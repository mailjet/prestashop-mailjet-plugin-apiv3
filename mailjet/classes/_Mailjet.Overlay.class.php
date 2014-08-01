<?php
/**
 * Mailjet Public API Overlay / The real-time Cloud Emailing platform
 *
 * Connect your Apps and Make our product yours with our powerful API
 *
 * @author		Mailjet Dev team
 * @copyright	Copyright (c) 2013, Mailjet SAS
 * @license		http://www.mailjet.com/Terms-of-use.htm
 * @link		http://www.mailjet.com/ Mailjet SAS Website
 * @filesource
 */

// ---------------------------------------------------------------------

namespace Mailjet;

use Mailjet\Api as Api;

/**
 * Mailjet Public API Overlay Class
 *
 * This class offers an abstract layer to use our powerful API.
 *
 * updated on 2013-06-14
 *
 * @package		Abstract Layer Mailjet Public API
 * @author		Mailjet Dev team
 * @link		http://www.mailjet.com/docs/api
 * @version		0.1
 */
class ApiOverlay
{

    /**
     * Mailjet API Instance
     *
     * @access	private
     * @var		resource
     */
    private $_api = NULL;

    /**
     * Singleton pattern : Current instance
     *
     * @access	private
     * @var		resource
     */
    private static $_instance = NULL;

    /**
     * Constructor
     *
     * Set $_apiKey and $_secretKey if provided
     *
     * @access	public
     * @param string $apiKey    Mailjet API Key
     * @param string $secretKey Mailjet API Secret Key
     */
    public function __construct($apiKey = FALSE, $secretKey = FALSE)
    {
        $this->_api = new Api($apiKey, $secretKey);
    }

    /**
     * Singleton pattern :
     * Get the instance of the object if it already exists
     * or create a new one.
     *
     * @access	public
     * @uses	Mailjet::Overlay::$_instance
     *
     * @return instance
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self))
            self::$_instance = new self();

        return self::$_instance;
    }

    /**
     * Destructor
     *
     * Unset the API object
     *
     * @access	public
     * @uses	Mailjet::Overlay::$_api
     */
    public function __destruct()
    {
        if(!is_null($this->_api))
            unset($this->_api);
    }

    /**
     * Update or set $_apiKey and $_secretKey
     *
     * @access	public
     * @param string $apiKey    Mailjet API Key
     * @param string $secretKey Mailjet API Secret Key
     */
    public function setKeys($apiKey, $secretKey)
    {
        $this->_api->setKeys($apiKey, $secretKey);
    }

    /**
     * Secure or not the transaction through https
     *
     * @access	public
     * @param boolean $secure TRUE to secure the transaction, FALSE otherwise
     */
    public function secure($secure = TRUE)
    {
        $this->_api->secure($secure);
    }

    /***************************** API *****************************/

    /**
     * API : Create a new Sub-Account with new API keys
     * url : api.mailjet.com/0.1/apiKeyadd
     *
     * @access	public
     * @param string $name          Custom name (Required)
     * @param string $custom_status custom status : 'up', 'suspend', 'down' (Optional)
     *
     * @return mixed Response from the API
     */
    public function createSubAccount($name, $custom_status = null)
    {
        $params = array(
            'method'	=> 'POST',
            'name'		=> $name
        );
        if (!is_null($custom_status))
            $params['custom_status'] = $custom_status;

        return $this->_api->apiKeyadd($params);
    }

    /**
     * API : Create a token for partial white-labeling of Mailjet
     * url : api.mailjet.com/0.1/apiKeyauthenticate
     *
     * @access	public
     * @param string $allowed_access 'campaigns', 'contacts', 'reports', 'stats', 'preferences' (Required)
     * @param string $type           default_url format : 'url', 'iframe', 'page' (Optional)
     * @param string $default_page   with a value within $allowed_access (Optional)
     * @param string $lang           Language : 'en', 'fr', 'de', 'es' (Optional)
     * @param string $timezone       Valid timezone (Optional)
     *
     * @return mixed Response from the API
     */
    public function createToken($allowed_access, $type = null, $default_page = null, $lang = null, $timezone = null)
    {
        $params = array(
            'method'			=> 'POST',
            'allowed_access'	=> $allowed_access,
            'apikey'			=> $this->_api->getAPIKey()
        );
        if (!is_null($type))
            $params['type'] = $type;
        if (!is_null($default_page))
            $params['default_page'] = $default_page;
        if (!is_null($lang))
            $params['lang'] = $lang;
        if (!is_null($timezone))
            $params['timezone'] = $timezone;

        return $this->_api->apiKeyauthenticate($params);
    }

    /**
     * API : Get a list of the Sub-Account's API keys
     * url : api.mailjet.com/0.1/apiKeylist
     *
     * @access	public
     * @param boolean $active        Mailjet's approval status : 0=inactive, 1=active (Optional)
     * @param string  $custom_status custom status : 'up', 'suspend', 'down' (Optional)
     * @param string  $name          Custom name. Use * as a joker for a research (Optional)
     * @param boolean $type          1=main, 0=subuser (Optional)
     * @param string  $api_key       Api public key (Optional)
     *
     * @return mixed Response from the API
     */
    public function getSubAccounts($active = null, $custom_status = null, $name = null, $type = null, $api_key = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($active))
            $params['active'] = $active;
        if (!is_null($custom_status))
            $params['custom_status'] = $custom_status;
        if (!is_null($name))
            $params['name'] = $name;
        if (!is_null($type))
            $params['type'] = $type;
        if (!is_null($api_key))
            $params['api_key'] = $api_key;

        return $this->_api->apiKeylist($params);
    }

    /**
     * API : Get the secret of one of your Sub-Account
     * url : api.mailjet.com/0.1/apiKeysecret
     *
     * @access	public
     * @param string $api_key Api public key (Required)
     *
     * @return mixed Response from the API
     */
    public function getSubAccountSecret($api_key)
    {
        $params = array(
            'method'	=> 'GET',
            'apikey'	=> $api_key
        );

        return $this->_api->apiKeysecret($params);
    }

    /**
     * API : Update the secret of one of your Sub-Account
     * url : api.mailjet.com/0.1/apiKeysecretchange
     *
     * @access	public
     * @param string $api_key Api public key (Required)
     *
     * @return mixed Response from the API
     */
    public function updateSubAccountSecret($api_key)
    {
        $params = array(
            'method'	=> 'GET',
            'apikey'	=> $api_key
        );

        return $this->_api->apiKeysecretchange($params);
    }

    /**
     * API : Update your Sub-Account informations
     * url : api.mailjet.com/0.1/apiKeyupdate
     *
     * @access	public
     * @param string $api_key       Api public key (Required)
     * @param string $custom_status custom status : 'up', 'suspend', 'down' (Optional)
     * @param string $name          Custom name (Optional)
     *
     * @return mixed Response from the API
     */
    public function updateSubAccount($api_key, $custom_status = null, $name = null)
    {
        $params = array(
            'method'	=> 'POST',
            'apikey'	=> $api_key
        );
        if (!is_null($custom_status))
            $params['custom_status'] = $custom_status;
        if (!is_null($name))
            $params['name'] = $name;

        return $this->_api->apiKeyupdate($params);
    }

    /***************************** USER *****************************/

    /**
     * USER : Add a new trust Domain to your sender addresses
     * url : api.mailjet.com/0.1/userDomainadd
     *
     * @access	public
     * @param string $domain Your domain name (Required)
     *
     * @return mixed Response from the API
     */
    public function createDomain($domain)
    {
        $params = array(
            'method'	=> 'POST',
            'domain'	=> $domain
        );

        return $this->_api->userDomainadd($params);
    }

    /**
     * USER : Get your trust Domains from your sender addresses
     * url : api.mailjet.com/0.1/userDomainList
     *
     * @access	public
     *
     * @return mixed Response from the API
     */
    public function getTrustDomains()
    {
        $params = array(
            'method'	=> 'GET'
        );

        return $this->_api->userDomainList($params);
    }

    /**
     * USER : Get the status of one of your trust Domains
     * url : api.mailjet.com/0.1/userDomainStatus
     *
     * @access	public
     * @param string  $domain      Your domain name (Required)
     * @param boolean $force_check Set to 1 to force a verification (Optional)
     *
     * @return mixed Response from the API
     */
    public function getDomainStatus($domain, $force_check = null)
    {
        $params = array(
            'method'	=> 'POST',
            'domain'	=> $domain
        );
        if (!is_null($force_check))
            $params['force_check'] = $force_check;

        return $this->_api->userDomainStatus($params);
    }

    /**
     * USER : Get Your account and profile informations
     * url : api.mailjet.com/0.1/userInfos
     *
     * @access	public
     *
     * @return mixed Response from the API
     */
    public function getUser()
    {
        $params = array(
            'method'	=> 'GET'
        );

        return $this->_api->userInfos($params);
    }

    /**
     * USER : Create a new Sender email
     * url : api.mailjet.com/0.1/userSenderAdd
     *
     * @access	public
     * @param string $email Sender email address (Required)
     *
     * @return mixed Response from the API
     */
    public function createSender($email)
    {
        $params = array(
            'method'	=> 'POST',
            'email'		=> $email
        );

        return $this->_api->userSenderAdd($params);
    }

    /**
     * USER : Get your sender email addresses
     * url : api.mailjet.com/0.1/userSenderList
     *
     * @access	public
     *
     * @return mixed Response from the API
     */
    public function getSenders()
    {
        $params = array(
            'method'	=> 'GET'
        );

        return $this->_api->userSenderList($params);
    }

    /**
     * USER : Get the status of one of your sender email addresses
     * url : api.mailjet.com/0.1/userSenderStatus
     *
     * @access	public
     * @param string $email Sender email address (Required)
     *
     * @return mixed Response from the API
     */
    public function getSenderStatus($email)
    {
        $params = array(
            'method'	=> 'POST',
            'email'		=> $email
        );

        return $this->_api->userSenderStatus($params);
    }

    /**
     * USER : Get your tracking preferences
     * url : api.mailjet.com/0.1/userTrackingCheck
     *
     * @access	public
     *
     * @return mixed Response from the API
     */
    public function getTracking()
    {
        $params = array(
            'method'	=> 'GET'
        );

        return $this->_api->userTrackingCheck($params);
    }

    /**
     * USER : Update your tracking preferences
     * url : api.mailjet.com/0.1/userTrackingUpdate
     *
     * @access	public
     * @param boolean $click Click tracking : 0=do not track, 1=track (Required)
     * @param boolean $open  Open tracking : 0=do not track, 1=track (Required)
     *
     * @return mixed Response from the API
     */
    public function updateTracking($click, $open)
    {
        $params = array(
            'method'	=> 'POST',
            'click'		=> $click,
            'open'		=> $open
        );

        return $this->_api->userTrackingUpdate($params);
    }

    /**
     * USER : Update Your account and profile informations
     * url : api.mailjet.com/0.1/userUpdate
     *
     * @access	public
     * @param string $address_city        Your address city (Optional)
     * @param string $address_country     Your address country (Optional)
     * @param string $address_postal_code Your address postal code (Optional)
     * @param string $address_street      Your street address (Optional)
     * @param string $company_name        Your company name (Optional)
     * @param string $contact_email       Your contact email (Optional)
     * @param string $firstname           Your firstname (Optional)
     * @param string $lastname            Your lastname (Optional)
     * @param string $locale              Your locale : fr_FR, en_US, de_DE, ... (Optional)
     *
     * @return mixed Response from the API
     */
    public function updateUser($address_city = null, $address_country = null, $address_postal_code = null,
                                $address_street = null, $company_name = null, $contact_email = null,
                                $firstname = null, $lastname = null, $locale = null)
    {
        $params = array(
            'method'	=> 'POST'
        );
        if (!is_null($address_city))
            $params['address_city'] = $address_city;
        if (!is_null($address_country))
            $params['address_country'] = $address_country;
        if (!is_null($address_postal_code))
            $params['address_postal_code'] = $address_postal_code;
        if (!is_null($address_street))
            $params['address_street'] = $address_street;
        if (!is_null($company_name))
            $params['company_name'] = $company_name;
        if (!is_null($contact_email))
            $params['contact_email'] = $contact_email;
        if (!is_null($firstname))
            $params['firstname'] = $firstname;
        if (!is_null($lastname))
            $params['lastname'] = $lastname;
        if (!is_null($locale))
            $params['locale'] = $locale;

        return $this->_api->userUpdate($params);
    }

    /**************************** MESSAGE ****************************/

    /**
     * MESSAGE : Get your campaigns
     * url : api.mailjet.com/0.1/messageCampaigns
     *
     * @access	public
     * @param integer $id      Mailjet's Campaign ID (Optional)
     * @param integer $start   Start offset (Optional)
     * @param integer $limit   Limit amount of results you want (Optional)
     * @param string  $status  Campaign status filter: "draft", "programmed", "sent", "archived". Filters can be combined, separated by a comma (Optional)
     * @param string  $orderby Order results by any returned parameter's name : default=id ASC (Optional)
     *
     * @return mixed Response from the API
     */
    public function getCampaigns($id = null, $start = null, $limit = null, $status = null, $orderby = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($id))
            $params['id'] = $id;
        if (!is_null($start))
            $params['start'] = $start;
        if (!is_null($limit))
            $params['limit'] = $limit;
        if (!is_null($status))
            $params['status'] = $status;
        if (!is_null($orderby))
            $params['orderby'] = $orderby;

        return $this->_api->messageCampaigns($params);
    }

    /**
     * MESSAGE : Get the complete list of subscribers to a specific message
     * url : api.mailjet.com/0.1/messageContacts
     *
     * @access	public
     * @param integer $id     Mailjet's Campaign ID (Required)
     * @param integer $start  Start offset (Optional)
     * @param integer $limit  Limit amount of results you want (Optional)
     * @param string  $status Message status filter: queued, sent, opened, clicked, bounce, blocked, spam or unsub (Optional)
     *
     * @return mixed Response from the API
     */
    public function getSubscribers($id, $start = null, $limit = null, $status = null)
    {
        $params = array(
            'method'	=> 'GET',
            'id'		=> $id
        );
        if (!is_null($start))
            $params['start'] = $start;
        if (!is_null($limit))
            $params['limit'] = $limit;
        if (!is_null($status))
            $params['status'] = $status;

        return $this->_api->messageContacts($params);
    }

    /**
     * MESSAGE : Create a new campaign available to be directly sent or programmed
     * url : api.mailjet.com/0.1/messageCreateCampaign
     *
     * @access	public
     * @param string  $lang         Language : en, fr, de, it, es, nl, sv, pt, ru, ja, lv, is, ro, el, ar, sk (Required)
     * @param string  $from         Sender email address (Required)
     * @param string  $from_name    Sender name (Optional)
     * @param string  $subject      Subject of the campaign (Required)
     * @param string  $edition_mode Edition mode : [tool]=WYSYWIG tool [html]=Raw HTML tool (Optional)
     * @param string  $edition_type Edition type : [full]=all steps [light]=step 2 and 3 [ulight]=step 2 only (Optional)
     * @param integer $list_id      Mailjet's contacts list ID. Required if edition_type = [light] (Optional)
     * @param string  $callback     Callback URL. Required if edition_type = [ulight] (Optional)
     * @param string  $footer       [default]=show [none]=hide . Required if edition_type = [html] (Optional)
     * @param string  $permalink    [default]=show [none]=hide (Optional)
     * @param integer $template_id  Mailjet's template ID (Optional)
     * @param string  $token        Unique token (Optional)
     * @param string  $reply_to     Replace the default 'reply-to' address (sender email) (Optional)
     * @param string  $title        Used in Mailjet's interface, to replace the subject (Optional)
     *
     * @return mixed Response from the API
     */
    public function createCampaign($lang, $from, $from_name = null, $subject,
                                    $edition_mode = null, $edition_type = null,
                                    $list_id = null, $callback = null, $footer = null,
                                    $permalink = null, $template_id = null, $token = null,
                                    $reply_to = null, $title = null)
    {
        $params = array(
            'method'	=> 'POST',
            'lang'		=> $lang,
            'from'		=> $from,
            'subject'	=> $subject
        );
        if (!is_null($from_name))
            $params['from_name'] = $from_name;
        if (!is_null($edition_mode))
            $params['edition_mode'] = $edition_mode;
        if (!is_null($edition_type))
            $params['edition_type'] = $edition_type;
        if (!is_null($list_id))
            $params['list_id'] = $list_id;
        if (!is_null($callback))
            $params['callback'] = $callback;
        if (!is_null($footer))
            $params['footer'] = $footer;
        if (!is_null($permalink))
            $params['permalink'] = $permalink;
        if (!is_null($template_id))
            $params['template_id'] = $template_id;
        if (!is_null($token))
            $params['token'] = $token;
        if (!is_null($reply_to))
            $params['reply_to'] = $reply_to;
        if (!is_null($title))
            $params['title'] = $title;

        return $this->_api->messageCreateCampaign($params);
    }

    /**
     * MESSAGE : Create a new campaign from an old one
     * url : api.mailjet.com/0.1/messageDuplicateCampaign
     *
     * @access	public
     * @param integer $id          Mailjet's Campaign ID (Required)
     * @param string  $lang        Language : en, fr, de, it, es, nl, sv, pt, ru, ja, lv, is, ro, el, ar, sk (Optional)
     * @param string  $from        Sender email address (Optional)
     * @param string  $from_name   Sender name (Optional)
     * @param string  $subject     Subject of the campaign (Optional)
     * @param integer $list_id     Mailjet's contacts list ID. Required if edition_type = [light] (Optional)
     * @param string  $callback    Callback URL. Required if edition_type = [ulight] (Optional)
     * @param string  $footer      [default]=show [none]=hide . Required if edition_type = [html] (Optional)
     * @param string  $permalink   [default]=show [none]=hide (Optional)
     * @param integer $template_id Mailjet's template ID (Optional)
     * @param string  $reply_to    Replace the default 'reply-to' address (sender email) (Optional)
     * @param string  $title       Used in Mailjet's interface, to replace the subject (Optional)
     *
     * @return mixed Response from the API
     */
    public function createCampaignFrom($id, $lang = null, $from = null, $from_name = null, $subject = null,
                                    $list_id = null, $callback = null, $footer = null, $permalink = null,
                                    $template_id = null, $reply_to = null, $title = null)
    {
        $params = array(
            'method'	=> 'POST',
            'id'		=> $id
        );
        if (!is_null($lang))
            $params['lang'] = $lang;
        if (!is_null($from))
            $params['from'] = $from;
        if (!is_null($from_name))
            $params['from_name'] = $from_name;
        if (!is_null($subject))
            $params['subject'] = $subject;
        if (!is_null($list_id))
            $params['list_id'] = $list_id;
        if (!is_null($callback))
            $params['callback'] = $callback;
        if (!is_null($footer))
            $params['footer'] = $footer;
        if (!is_null($permalink))
            $params['permalink'] = $permalink;
        if (!is_null($template_id))
            $params['template_id'] = $template_id;
        if (!is_null($reply_to))
            $params['reply_to'] = $reply_to;
        if (!is_null($title))
            $params['title'] = $title;

        return $this->_api->messageDuplicateCampaign($params);
    }

    /**
     * MESSAGE : Get the HTML source from one of your campaigns
     * url : api.mailjet.com/0.1/messageHtmlCampaign
     *
     * @access	public
     * @param integer $id Mailjet's Campaign ID (Required)
     *
     * @return mixed Response from the API
     */
    public function getCampaignHTML($id)
    {
        $params = array(
            'method'	=> 'GET',
            'id'		=> $id
        );

        return $this->_api->messageHtmlCampaign($params);
    }

    /**
     * MESSAGE : Get your messages with some filters
     * url : api.mailjet.com/0.1/messageList
     *
     * @access	public
     * @param string    $custom_campaign Your custom campaign name (Optional)
     * @param string    $from            Sender email address (Optional)
     * @param string    $from_name       Sender name (Optional)
     * @param string    $to_email        Recipient's email address (Optional)
     * @param integer   $mj_campaign_id  Mailjet's Campaign ID (Optional)
     * @param timestamp $sent_after      Minimum date of sending (Optional)
     * @param timestamp $sent_before     Maximum date of sending (Optional)
     * @param integer   $start           Start offset (Optional)
     * @param integer   $limit           Limit amount of results you want (Optional)
     *
     * @return mixed Response from the API
     */
    public function getMessages($custom_campaign = null, $from = null, $from_name = null,
                                $to_email = null, $mj_campaign_id = null,
                                $sent_after = null, $sent_before = null,
                                $start = null, $limit = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($custom_campaign))
            $params['custom_campaign'] = $custom_campaign;
        if (!is_null($from))
            $params['from_email'] = $from;
        if (!is_null($from_name))
            $params['from_name'] = $from_name;
        if (!is_null($to_email))
            $params['to_email'] = $to_email;
        if (!is_null($mj_campaign_id))
            $params['mj_campaign_id'] = $mj_campaign_id;
        if (!is_null($sent_after))
            $params['sent_after'] = $sent_after;
        if (!is_null($sent_before))
            $params['sent_before'] = $sent_before;
        if (!is_null($start))
            $params['start'] = $start;
        if (!is_null($limit))
            $params['limit'] = $limit;

        return $this->_api->messageList($params);
    }

    /**
     * MESSAGE : Send a campaign instantly
     * url : api.mailjet.com/0.1/messageSendCampaign
     *
     * @access	public
     * @param integer $id Mailjet's Campaign ID (Required)
     *
     * @return mixed Response from the API
     */
    public function sendCampaign($id)
    {
        $params = array(
            'method'	=> 'POST',
            'id'		=> $id
        );

        return $this->_api->messageSendCampaign($params);
    }

    /**
     * MESSAGE : Update the HTML and TXT source from one of your campaigns
     * url : api.mailjet.com/0.1/messageSetHtmlCampaign
     *
     * @access	public
     * @param integer $id   Mailjet's Campaign ID (Required)
     * @param string  $html Raw HTML code of your Email. It must contain the unsubscribe tag (in en: [[UNSUB_LINK_EN]]) (Required)
     * @param string  $text Text version of your Email. It must contain the unsubscribe tag (in en: [[UNSUB_LINK_EN]]) (Optional)
     *
     * @return mixed Response from the API
     */
    public function updateCampaignHTML($id, $html, $text = null)
    {
        $params = array(
            'method'	=> 'POST',
            'id'		=> $id,
            'html'		=> $html
        );
        if (!is_null($text))
            $params['text'] = $text;

        return $this->_api->messageSetHtmlCampaign($params);
    }

    /**
     * MESSAGE : Get light statistics on one of your campaigns
     * url : api.mailjet.com/0.1/messageStatistics
     *
     * @access	public
     * @param integer $id Mailjet's Campaign ID (Required)
     *
     * @return mixed Response from the API
     */
    public function getCampaignStatistics($id)
    {
        $params = array(
            'method'	=> 'GET',
            'id'		=> $id
        );

        return $this->_api->messageStatistics($params);
    }

    /**
     * MESSAGE : Test a campaign
     * url : api.mailjet.com/0.1/messageTestcampaign
     *
     * @access	public
     * @param integer $id    Mailjet's Campaign ID (Required)
     * @param string  $email Email which will receive the test (Required)
     *
     * @return mixed Response from the API
     */
    public function testCampaign($id, $email)
    {
        $params = array(
            'method'	=> 'POST',
            'id'		=> $id,
            'email'		=> $email
        );

        return $this->_api->messageTestcampaign($params);
    }

    /**
     * MESSAGE : Get Mailjet's template categories
     * url : api.mailjet.com/0.1/messageTplCategories
     *
     * @access	public
     *
     * @return mixed Response from the API
     */
    public function getTemplateCategories()
    {
        $params = array(
            'method'	=> 'GET'
        );

        return $this->_api->messageTplCategories($params);
    }

    /**
     * MESSAGE : Get Mailjet's template categories
     * url : api.mailjet.com/0.1/messageTplModels
     *
     * @access	public
     * @param integer $category Mailjet's template category ID (Optional)
     * @param boolean $custom   If true, returns the user's templates (Optional)
     * @param string  $locale   Language : fr_FR, en_US, de_DE, ... (Optional)
     *
     * @return mixed Response from the API
     */
    public function getTemplates($category = null, $custom = null, $locale = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($category))
            $params['category'] = $category;
        if (!is_null($custom))
            $params['custom'] = $custom;
        if (!is_null($locale))
            $params['locale'] = $locale;

        return $this->_api->messageTplModels($params);
    }

    /**
     * MESSAGE : Update a campaign
     * url : api.mailjet.com/0.1/messageUpdateCampaign
     *
     * @access	public
     * @param integer   $id           Mailjet's Campaign ID (Required)
     * @param string    $status       Status of the campaign : [draft] or [archived] (Optional)
     * @param string    $lang         Language : en, fr, de, it, es, nl, sv, pt, ru, ja, lv, is, ro, el, ar, sk (Optional)
     * @param string    $from         Sender email address (Optional)
     * @param string    $from_name    Sender name (Optional)
     * @param string    $subject      Subject of the campaign (Optional)
     * @param integer   $list_id      Mailjet's contacts list ID. Required if edition_type = [light] (Optional)
     * @param string    $callback     Callback URL. Required if edition_type = [ulight] (Optional)
     * @param string    $footer       [default]=show [none]=hide . Required if edition_type = [html] (Optional)
     * @param string    $permalink    [default]=show [none]=hide (Optional)
     * @param integer   $template_id  Mailjet's template ID (Optional)
     * @param string    $reply_to     Replace the default 'reply-to' address (sender email) (Optional)
     * @param string    $title        Used in Mailjet's interface, to replace the subject (Optional)
     * @param timestamp $sending_date If specified, the campaign will be sent at this date (Optional)
     *
     * @return mixed Response from the API
     */
    public function updateCampaign($id, $status = null, $lang = null, $from = null, $from_name = null, $subject = null,
                                    $list_id = null, $callback = null, $footer = null, $permalink = null,
                                    $template_id = null, $reply_to = null, $title = null,
                                    $sending_date = null)
    {
        $params = array(
            'method'	=> 'POST',
            'id'		=> $id
        );
        if (!is_null($status))
            $params['status'] = $status;
        if (!is_null($lang))
            $params['lang'] = $lang;
        if (!is_null($from))
            $params['from'] = $from;
        if (!is_null($from_name))
            $params['from_name'] = $from_name;
        if (!is_null($subject))
            $params['subject'] = $subject;
        if (!is_null($list_id))
            $params['list_id'] = $list_id;
        if (!is_null($callback))
            $params['callback'] = $callback;
        if (!is_null($footer))
            $params['footer'] = $footer;
        if (!is_null($permalink))
            $params['permalink'] = $permalink;
        if (!is_null($template_id))
            $params['template_id'] = $template_id;
        if (!is_null($reply_to))
            $params['reply_to'] = $reply_to;
        if (!is_null($title))
            $params['title'] = $title;
        if (!is_null($sending_date))
            $params['sending_date'] = $sending_date;

        return $this->_api->messageUpdateCampaign($params);
    }

    /**************************** CONTACTS ****************************/

    /**
     * CONTACTS : Get general informations about a specific contact
     * url : api.mailjet.com/0.1/contactInfos
     *
     * @access	public
     * @param string $contact Mailjet's contact ID or email (Required)
     *
     * @return mixed Response from the API
     */
    public function getContactInformations($contact)
    {
        $params = array(
            'method'	=> 'GET',
            'contact'	=> $contact
        );

        return $this->_api->contactInfos($params);
    }

    /**
     * CONTACTS : Get your contacts with some filters
     * url : api.mailjet.com/0.1/contactList
     *
     * @access	public
     * @param integer   $mj_contact_id Mailjet's Contact ID (Optional)
     * @param integer   $start         Start offset (Optional)
     * @param integer   $limit         Limit amount of results you want : default=100 (Optional)
     * @param string    $status        Contacts' status : opened, active, unactive or unsub (Optional)
     * @param boolean   $blocked       0=blocked, 1=active (Optional)
     * @param boolean   $unsub         0=subscriber, 1=unsubscribed (Optional)
     * @param timestamp $last_activity Minimum last activity timestamp (Optional)
     *
     * @return mixed Response from the API
     */
    public function getContacts($mj_contact_id = null, $start = null, $limit = null,
                                $status = null, $blocked = null, $unsub = null, $last_activity = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($mj_contact_id))
            $params['mj_contact_id'] = $mj_contact_id;
        if (!is_null($start))
            $params['start'] = $start;
        if (!is_null($limit))
            $params['limit'] = $limit;
        if (!is_null($status))
            $params['status'] = $status;
        if (!is_null($blocked))
            $params['blocked'] = $blocked;
        if (!is_null($unsub))
            $params['unsub'] = $unsub;
        if (!is_null($last_activity))
            $params['last_activity'] = $last_activity;

        if (in_array(array('start', 'limit', 'last_activity'), array_keys($params))
            && count($params) == 4)

            return $this->_api->contactOpeners($params);
        else
            return $this->_api->contactList($params);
    }

    /************************* CONTACTS LIST **************************/

    /**
     * LIST : Create a contact in a list
     * url : api.mailjet.com/0.1/listsAddContact
     *
     * @access	public
     * @param string  $contact Mailjet's contact ID or email (Required)
     * @param integer $list_id Mailjet's List ID (Required)
     * @param boolean $force   If the contact exists, reset unsub status (Optional)
     *
     * @return mixed Response from the API
     */
    public function createContact($contact, $list_id, $force = null)
    {
        $params = array(
            'method'	=> 'POST',
            'contact'	=> $contact,
            'id'		=> $list_id
        );
        if (!is_null($force))
            $params['force'] = $force;

        return $this->_api->listsAddContact($params);
    }

    /**
     * LIST : Create contacts in a list
     * url : api.mailjet.com/0.1/listsAddManyContacts
     *
     * @access	public
     * @param string  $contacts Serialized list of emails (Required)
     * @param integer $list_id  Mailjet's List ID (Required)
     * @param boolean $force    If the contact exists, reset unsub status (Optional)
     *
     * @return mixed Response from the API
     */
    public function createContacts($contacts, $list_id, $force = null)
    {
        $params = array(
            'method'	=> 'POST',
            'contacts'	=> $contacts,
            'id'		=> $list_id
        );
        if (!is_null($force))
            $params['force'] = $force;

        return $this->_api->listsAddManyContacts($params);
    }

    /**
     * LIST : Get your contacts lists with some filters
     * url : api.mailjet.com/0.1/listsAll
     *
     * @access	public
     * @param integer $start   Start offset (Optional)
     * @param integer $limit   Limit amount of results you want (Optional)
     * @param string  $orderby Order results by any returned parameter's name : default=id ASC (Optional)
     *
     * @return mixed Response from the API
     */
    public function getContactsLists($start = null, $limit = null, $orderby = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($start))
            $params['start'] = $start;
        if (!is_null($limit))
            $params['limit'] = $limit;
        if (!is_null($orderby))
            $params['order_by'] = $orderby;

        return $this->_api->listsAll($params);
    }

    /**
     * LIST : Get your contacts from a list with some filters
     * url : api.mailjet.com/0.1/listsContacts
     *
     * @access	public
     * @param integer   $id            Mailjet's List ID (Required)
     * @param integer   $start         Start offset (Optional)
     * @param integer   $limit         Limit amount of results you want (Optional)
     * @param string    $orderby       Order results by any returned parameter's name : default=id ASC (Optional)
     * @param string    $status        Contacts' status : opened, active, unactive or unsub (Optional)
     * @param boolean   $blocked       0=blocked, 1=active (Optional)
     * @param boolean   $unsub         0=subscriber, 1=unsubscribed (Optional)
     * @param timestamp $last_activity Minimum last activity timestamp (Optional)
     *
     * @return mixed Response from the API
     */
    public function getContactsFromList($id, $start = null, $limit = null, $orderby = null,
                                        $status = null, $blocked = null, $unsub = null, $last_activity = null)
    {
        $params = array(
            'method'	=> 'GET',
            'id'		=> $id
        );
        if (!is_null($start))
            $params['start'] = $start;
        if (!is_null($limit))
            $params['limit'] = $limit;
        if (!is_null($orderby))
            $params['orderby'] = $orderby;
        if (!is_null($status))
            $params['status'] = $status;
        if (!is_null($blocked))
            $params['blocked'] = $blocked;
        if (!is_null($unsub))
            $params['unsub'] = $unsub;
        if (!is_null($last_activity))
            $params['last_activity'] = $last_activity;

        return $this->_api->listsContacts($params);
    }

    /**
     * LIST : Create a new Contacts list
     * url : api.mailjet.com/0.1/listsCreate
     *
     * @access	public
     * @param string $label Title of your list (Required)
     * @param string $name  List name used as name@lists.mailjet.com (Required)
     *
     * @return mixed Response from the API
     */
    public function createContactsList($label, $name)
    {
        $params = array(
            'method'	=> 'POST',
            'label'		=> $label,
            'name'		=> $name
        );

        return $this->_api->listsCreate($params);
    }

    /**
     * LIST : Delete a Contacts list
     * url : api.mailjet.com/0.1/listsDelete
     *
     * @access	public
     * @param integer $id Mailjet's List ID (Required)
     *
     * @return mixed Response from the API
     */
    public function deleteContactsList($id)
    {
        $params = array(
            'method'	=> 'POST',
            'id'		=> $id
        );

        return $this->_api->listsDelete($params);
    }

    /**
     * LIST : Get a Contacts list Email : [name]@lists.mailjet.com
     * url : api.mailjet.com/0.1/listsEmail
     *
     * @access	public
     * @param integer $id Mailjet's List ID (Required)
     *
     * @return mixed Response from the API
     */
    public function getContactsListEmail($id)
    {
        $params = array(
            'method'	=> 'GET',
            'id'		=> $id
        );

        return $this->_api->listsEmail($params);
    }

    /**
     * LIST : Delete a Contact from a list
     * url : api.mailjet.com/0.1/listsRemoveContact
     *
     * @access	public
     * @param string  $contact Mailjet's contact ID or email (Required)
     * @param integer $id      Mailjet's List ID (Required)
     *
     * @return mixed Response from the API
     */
    public function deleteContact($contact, $id)
    {
        $params = array(
            'method'	=> 'POST',
            'id'		=> $id,
            'contact'	=> $contact
        );

        return $this->_api->listsRemoveContact($params);
    }

    /**
     * LIST : Delete a Contact from a list
     * url : api.mailjet.com/0.1/listsRemoveManyContacts
     *
     * @access	public
     * @param string  $contacts Serialized list of emails (Required)
     * @param integer $id       Mailjet's List ID (Required)
     *
     * @return mixed Response from the API
     */
    public function deleteContacts($contacts, $id)
    {
        $params = array(
            'method'	=> 'POST',
            'id'		=> $id,
            'contacts'	=> $contacts
        );

        return $this->_api->listsRemoveManyContacts($params);
    }

    /**
     * LIST : Get advanced statistics concerning one of your list of contacts
     * url : api.mailjet.com/0.1/listsStatistics
     *
     * @access	public
     * @param integer $id Mailjet's List ID (Required)
     *
     * @return mixed Response from the API
     */
    public function getContactsListStatistics($id)
    {
        $params = array(
            'method'	=> 'GET',
            'id'		=> $id
        );

        return $this->_api->listsStatistics($params);
    }

    /**
     * LIST : Unsubscribe a Contact from a list
     * url : api.mailjet.com/0.1/listsUnsubContact
     *
     * @access	public
     * @param string  $contact Mailjet's contact ID or email (Required)
     * @param integer $id      Mailjet's List ID (Required)
     *
     * @return mixed Response from the API
     */
    public function unsubscribeContact($contact, $id)
    {
        $params = array(
            'method'	=> 'POST',
            'id'		=> $id,
            'contact'	=> $contact
        );

        return $this->_api->listsUnsubContact($params);
    }

    /**
     * LIST : Update a Contacts list
     * url : api.mailjet.com/0.1/listsUpdate
     *
     * @access	public
     * @param integer $id    Mailjet's List ID (Required)
     * @param string  $label Title of your list (Optional)
     * @param string  $name  List name used as name@lists.mailjet.com (Optional)
     *
     * @return mixed Response from the API
     */
    public function updateContactsList($id, $label = null, $name = null)
    {
        $params = array(
            'method'	=> 'POST',
            'id'		=> $id
        );
        if (!is_null($label))
            $params['label'] = $label;
        if (!is_null($name))
            $params['name'] = $name;

        return $this->_api->listsUpdate($params);
    }

    /***************************** REPORT *****************************/

    /**
     * REPORT : Get your (tracked and clicked) links
     * url : api.mailjet.com/0.1/reportClick
     *
     * @access	public
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param string    $order       Order direction (Optional)
     * @param string    $order_by    Order by: [date], [link], [by_email], [click_delay], [user_agent] (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     *
     * @return mixed Response from the API
     */
    public function getClickedEmails($campaign_id = null, $from = null, $from_type = null,
                                    $start = null, $limit = null, $order = null, $order_by = null,
                                    $ts_from = null, $ts_to = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($campaign_id))
            $params['campaign_id'] = $campaign_id;
        if (!is_null($from))
            $params['from'] = $from;
        if (!is_null($from_type))
            $params['from_type'] = $from_type;
        if (!is_null($start))
            $params['start'] = $start;
        if (!is_null($limit))
            $params['limit'] = $limit;
        if (!is_null($order))
            $params['order'] = $order;
        if (!is_null($order_by))
            $params['order_by'] = $order_by;
        if (!is_null($ts_from))
            $params['ts_from'] = $ts_from;
        if (!is_null($ts_to))
            $params['ts_to'] = $ts_to;

        return $this->_api->reportClick($params);
    }

    /**
     * REPORT : Get a list of domains to which your emails are sent
     * url : api.mailjet.com/0.1/reportDomain
     *
     * @access	public
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param string    $from_domain Domain (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     *
     * @return mixed Response from the API
     */
    public function getDomains($campaign_id = null, $from = null, $from_domain = null, $from_type = null,
                                    $start = null, $limit = null, $ts_from = null, $ts_to = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($campaign_id))
            $params['campaign_id'] = $campaign_id;
        if (!is_null($from))
            $params['from'] = $from;
        if (!is_null($from_domain))
            $params['from_domain'] = $from_domain;
        if (!is_null($from_type))
            $params['from_type'] = $from_type;
        if (!is_null($start))
            $params['start'] = $start;
        if (!is_null($limit))
            $params['limit'] = $limit;
        if (!is_null($ts_from))
            $params['ts_from'] = $ts_from;
        if (!is_null($ts_to))
            $params['ts_to'] = $ts_to;

        return $this->_api->reportDomain($params);
    }

    /**
     * REPORT : Get a list of bounced emails
     * url : api.mailjet.com/0.1/reportEmailBounce
     *
     * @access	public
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param string    $from_domain Domain (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     *
     * @return mixed Response from the API
     */
    public function getBouncedEmails($campaign_id = null, $from = null, $from_domain = null, $from_type = null,
                                    $start = null, $limit = null, $ts_from = null, $ts_to = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($campaign_id))
            $params['campaign_id'] = $campaign_id;
        if (!is_null($from))
            $params['from'] = $from;
        if (!is_null($from_domain))
            $params['from_domain'] = $from_domain;
        if (!is_null($from_type))
            $params['from_type'] = $from_type;
        if (!is_null($start))
            $params['start'] = $start;
        if (!is_null($limit))
            $params['limit'] = $limit;
        if (!is_null($ts_from))
            $params['ts_from'] = $ts_from;
        if (!is_null($ts_to))
            $params['ts_to'] = $ts_to;

        return $this->_api->reportEmailBounce($params);
    }

    /**
     * REPORT : Get email clients used to open your emails when tracked
     * url : api.mailjet.com/0.1/reportEmailClients
     *
     * @access	public
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param string    $from_domain Domain (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     *
     * @return mixed Response from the API
     */
    public function getEmailClients($campaign_id = null, $from = null, $from_domain = null, $from_type = null,
                                    $start = null, $limit = null, $ts_from = null, $ts_to = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($campaign_id))
            $params['campaign_id'] = $campaign_id;
        if (!is_null($from))
            $params['from'] = $from;
        if (!is_null($from_domain))
            $params['from_domain'] = $from_domain;
        if (!is_null($from_type))
            $params['from_type'] = $from_type;
        if (!is_null($start))
            $params['start'] = $start;
        if (!is_null($limit))
            $params['limit'] = $limit;
        if (!is_null($ts_from))
            $params['ts_from'] = $ts_from;
        if (!is_null($ts_to))
            $params['ts_to'] = $ts_to;

        return $this->_api->reportEmailClients($params);
    }

    /**
     * REPORT : Get all your messages informations (sender, subject, dates, ...) for a campaign
     * url : api.mailjet.com/0.1/reportEmailInfos
     *
     * @access	public
     * @param integer $campaign_id Mailjet's Campaign ID (Required)
     *
     * @return mixed Response from the API
     */
    public function getEmailInformations($campaign_id)
    {
        $params = array(
            'method'		=> 'GET',
            'campaign_id'	=> $campaign_id
        );

        return $this->_api->reportEmailInfos($params);
    }

    /**
     * REPORT : Get all emails sent with numerous filters
     * url : api.mailjet.com/0.1/reportEmailSent
     *
     * @access	public
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param string    $from_domain Domain (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param string    $status      Email status : queued, sent, opened, clicked, bounce, blocked, spam or unsub (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     *
     * @return mixed Response from the API
     */
    public function getSentEmails($campaign_id = null, $from = null, $from_domain = null, $from_type = null,
                                    $start = null, $limit = null, $status = null, $ts_from = null, $ts_to = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($campaign_id))
            $params['campaign_id'] = $campaign_id;
        if (!is_null($from))
            $params['from'] = $from;
        if (!is_null($from_domain))
            $params['from_domain'] = $from_domain;
        if (!is_null($from_type))
            $params['from_type'] = $from_type;
        if (!is_null($start))
            $params['start'] = $start;
        if (!is_null($limit))
            $params['limit'] = $limit;
        if (!is_null($status))
            $params['status'] = $status;
        if (!is_null($ts_from))
            $params['ts_from'] = $ts_from;
        if (!is_null($ts_to))
            $params['ts_to'] = $ts_to;

        return $this->_api->reportEmailSent($params);
    }

    /**
     * REPORT : Get global and detailed statistics concerning your sendings
     * url : api.mailjet.com/0.1/reportEmailStatistics
     *
     * @access	public
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param string    $from_domain Domain (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param string    $to_email    Contact Email address (Optional)
     * @param integer   $to_id       Mailjet's contact ID (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     *
     * @return mixed Response from the API
     */
    public function getEmailStatitics($campaign_id = null, $from = null, $from_domain = null, $from_type = null,
                                    $to_email = null, $to_id = null, $start = null, $limit = null,
                                    $ts_from = null, $ts_to = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($campaign_id))
            $params['campaign_id'] = $campaign_id;
        if (!is_null($from))
            $params['from'] = $from;
        if (!is_null($from_domain))
            $params['from_domain'] = $from_domain;
        if (!is_null($from_type))
            $params['from_type'] = $from_type;
        if (!is_null($to_email))
            $params['to_email'] = $to_email;
        if (!is_null($to_id))
            $params['to_id'] = $to_id;
        if (!is_null($start))
            $params['start'] = $start;
        if (!is_null($limit))
            $params['limit'] = $limit;
        if (!is_null($ts_from))
            $params['ts_from'] = $ts_from;
        if (!is_null($ts_to))
            $params['ts_to'] = $ts_to;

        return $this->_api->reportEmailStatistics($params);
    }

    /**
     * REPORT : Get geographic datas on where around the world your emails are opened
     * url : api.mailjet.com/0.1/reportGeoIp
     *
     * @access	public
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param string    $from_domain Domain (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     *
     * @return mixed Response from the API
     */
    public function getGeographicDatas($campaign_id = null, $from = null, $from_domain = null, $from_type = null,
                                    $start = null, $limit = null, $ts_from = null, $ts_to = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($campaign_id))
            $params['campaign_id'] = $campaign_id;
        if (!is_null($from))
            $params['from'] = $from;
        if (!is_null($from_domain))
            $params['from_domain'] = $from_domain;
        if (!is_null($from_type))
            $params['from_type'] = $from_type;
        if (!is_null($start))
            $params['start'] = $start;
        if (!is_null($limit))
            $params['limit'] = $limit;
        if (!is_null($ts_from))
            $params['ts_from'] = $ts_from;
        if (!is_null($ts_to))
            $params['ts_to'] = $ts_to;

        return $this->_api->reportGeoIp($params);
    }

    /**
     * REPORT : Get a list of opened emails
     * url : api.mailjet.com/0.1/reportOpen
     *
     * @access	public
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param string    $from_domain Domain (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     *
     * @return mixed Response from the API
     */
    public function getOpenedEmails($campaign_id = null, $from = null, $from_domain = null, $from_type = null,
                                    $start = null, $limit = null, $ts_from = null, $ts_to = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($campaign_id))
            $params['campaign_id'] = $campaign_id;
        if (!is_null($from))
            $params['from'] = $from;
        if (!is_null($from_domain))
            $params['from_domain'] = $from_domain;
        if (!is_null($from_type))
            $params['from_type'] = $from_type;
        if (!is_null($start))
            $params['start'] = $start;
        if (!is_null($limit))
            $params['limit'] = $limit;
        if (!is_null($ts_from))
            $params['ts_from'] = $ts_from;
        if (!is_null($ts_to))
            $params['ts_to'] = $ts_to;

        return $this->_api->reportOpen($params);
    }

    /**
     * REPORT : Get Statistics of opened emails
     * url : api.mailjet.com/0.1/reportOpenedStatistics
     *
     * @access	public
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param string    $from_domain Domain (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     *
     * @return mixed Response from the API
     */
    public function getOpenedEmailsStatistics($campaign_id = null, $from = null, $from_domain = null, $from_type = null,
                                    $start = null, $limit = null, $ts_from = null, $ts_to = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($campaign_id))
            $params['campaign_id'] = $campaign_id;
        if (!is_null($from))
            $params['from'] = $from;
        if (!is_null($from_domain))
            $params['from_domain'] = $from_domain;
        if (!is_null($from_type))
            $params['from_type'] = $from_type;
        if (!is_null($start))
            $params['start'] = $start;
        if (!is_null($limit))
            $params['limit'] = $limit;
        if (!is_null($ts_from))
            $params['ts_from'] = $ts_from;
        if (!is_null($ts_to))
            $params['ts_to'] = $ts_to;

        return $this->_api->reportOpenedStatistics($params);
    }

    /**
     * REPORT : Get platform, browsers and versions used by your recipients
     * url : api.mailjet.com/0.1/reportUserAgents
     *
     * @access	public
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param string    $from_id     Mailjet's Sender ID (Optional)
     * @param string    $from_domain Domain (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param string    $status      Email status : queued, sent, opened, clicked, bounce, blocked, spam or unsub (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     *
     * @return mixed Response from the API
     */
    public function getUserAgents($campaign_id = null,
                                    $from = null, $from_id = null, $from_domain = null, $from_type = null,
                                    $start = null, $limit = null, $status = null, $ts_from = null, $ts_to = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($campaign_id))
            $params['campaign_id'] = $campaign_id;
        if (!is_null($from))
            $params['from'] = $from;
        if (!is_null($from_id))
            $params['from_id'] = $from_id;
        if (!is_null($from_domain))
            $params['from_domain'] = $from_domain;
        if (!is_null($from_type))
            $params['from_type'] = $from_type;
        if (!is_null($start))
            $params['start'] = $start;
        if (!is_null($limit))
            $params['limit'] = $limit;
        if (!is_null($status))
            $params['status'] = $status;
        if (!is_null($ts_from))
            $params['ts_from'] = $ts_from;
        if (!is_null($ts_to))
            $params['ts_to'] = $ts_to;

        return $this->_api->reportUserAgents($params);
    }

    /**************************** HELP *****************************/

    /**
     * HELP : Get all categories of methods available and documented in our API
     * url : api.mailjet.com/0.1/helpCategories
     *
     * @access	public
     *
     * @return mixed Response from the API
     */
    public function getAPICategories()
    {
        $params = array(
            'method'	=> 'GET'
        );

        return $this->_api->helpCategories($params);
    }

    /**
     * HELP : Get description of a category and embeded methods available and documented in our API
     * url : api.mailjet.com/0.1/helpCategory
     *
     * @access	public
     * @param string $name Category name (Required)
     *
     * @return mixed Response from the API
     */
    public function getAPICategory($name)
    {
        $params = array(
            'method'	=> 'GET',
            'name'		=> $name
        );

        return $this->_api->helpCategory($params);
    }

    /**
     * HELP : Get description of a specific method available and documented in our API
     * url : api.mailjet.com/0.1/helpMethod
     *
     * @access	public
     * @param string $name [categoryMethod] name (Required)
     *
     * @return mixed Response from the API
     */
    public function getAPIMethod($name)
    {
        $params = array(
            'method'	=> 'GET',
            'name'		=> $name
        );

        return $this->_api->helpMethod($params);
    }

    /**
     * HELP : Get all methods of a specific category available and documented in our API
     * url : api.mailjet.com/0.1/helpMethods
     *
     * @access	public
     * @param string $category Category name (Required)
     *
     * @return mixed Response from the API
     */
    public function getAPIMethods($category)
    {
        $params = array(
            'method'	=> 'GET',
            'category'	=> $category
        );

        return $this->_api->helpMethods($params);
    }

    /**
     * HELP : Get response status and status code you'll encounter when calling our API
     * url : api.mailjet.com/0.1/helpStatus
     *
     * @access	public
     * @param string $code Code response (Optional)
     *
     * @return mixed Response from the API
     */
    public function getAPIStatus($code = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($code))
            $params['code'] = $code;

        return $this->_api->helpStatus($params);
    }

}
