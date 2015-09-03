<?php
/**
 * Mailjet Public API Overlay / The real-time Cloud Emailing platform
 *
 * Connect your Apps and Make our product yours with our powerful API
 * http://www.mailjet.com/ Mailjet SAS Website
 *
 * @author		David Coullet at Mailjet Dev team
 * @copyright	Copyright (c) 2013, Mailjet SAS, http://www.mailjet.com/Terms-of-use.htm
 * @file
 *
 * @mainpage PHP5 Mailjet API lib Code Source Documentation
 *
 * @section INTRO Introduction
 * @subsection WHAT Mailjet Api ?
 * <h3>Mailjet is a real-time Cloud Emailing platform: scalable, agile and flexible !</h3>
 * - Our unique algorithm boosts your deliverability and our platform provides in-depth insight so you can optimize more than ever.
 * - Because 20% of emails never reach the inbox Mailjet is a logical choice ! But you already know that.
 *
 * <h3>Our API is powerful and accessible to everyone : enjoy the possibilities that the openness of our platform provides !</h3>
 * - Each account includes full API access so you can easily develop your own scripts and applications on top of our system.
 * <p>This documentation and the Package provided is for the <strong>PHP5 environment</strong>.
 * You can check for examples in the <em>examples/</em> directory of the package.</p>
 *
 * @subsection API Api classes :
 * - Mailjet Public API Main Class
 * 	- Mailjet::Api
 *
 * - Mailjet Public API Overlay Class
 * 	- Mailjet::ApiOverlay
 *
 * - Mailjet Public API Exception Class
 * 	- Mailjet::Mailjet_ApiException
 *
 * - Mailjet Public API Mailjet_Parameters class
 * 	- Mailjet::Mailjet_Parameters
 *
 * - Mailjet Public Event Trigger API Main Class
 * 	- Mailjet::Event
 *
 * - Mailjet Public API Orderby Class
 * 	- Mailjet::OrderBy
 *
 * @subsection REQUIRED Required :
 * - PHP 5.3+
 * - Account on http://www.mailjet.com and your Api & Secret keys
 *
 * @section COPYRIGHT Copyright
 * Copyright (c) 2013, Mailjet SAS
 * http://www.mailjet.com/Terms-of-use.htm
 *
 */

// ---------------------------------------------------------------------

require_once 'Mailjet.Api.class.php';
require_once 'MailJet.Data.Api.class.php';
require_once 'Mailjet.Exception.class.php';
require_once 'Mailjet.Parameters.class.php';


/**
 * Mailjet Public API Overlay Class
 *
 * This class offers an abstract layer to use our powerful API.
 * http://www.mailjet.com/docs/api
 *
 * @code{.php}
 * $mj = Mailjet\ApiOverlay::getInstance();
 * $mj->setDebugFlag(Mailjet\ApiOverlay::PRODUCTION);
 * $mj->setOutput(Mailjet\ApiOverlay::JSON);
 * $mj->setKeys('', '');
 * $mj->secure(true);
 *
 * // Get global statistics concerning your sendings in the last 24 hours
 * // Active a cache of 5 minutes
 *
 * $parameters = new Mailjet\Mailjet_Parameters();
 * $parameters->ts_from = time() - (24 * 60 * 60);
 * $parameters->ts_to = time();
 * $parameters->cache = 300;
 *
 * try {
 * 	$response = $mj->getEmailStatisticsP($parameters);
 * } catch (Mailjet\Mailjet_ApiException $e) {
 * 	echo $e->getMessage();
 * 	if (!is_null($mj->getResponse()))
 * 		var_dump($mj->getResponse());
 * }
 * var_dump($response);
 * @endcode
 *
 * updated on 2013-08-11
 *
 * @class		ApiOverlay
 * @author		David Coullet at Mailjet Dev team
 * @version		0.2
 */
class Mailjet_ApiOverlay
{

    /**
     * Mailjet API Instance
     *
     * @access	private
     * @var		resource $_api
     */
    private $_api = NULL;

    /**
     * Singleton pattern : Current instance
     *
     * @access	private
     * @var		resource $_instance
     */
    private static $_instance = NULL;

    /**
     * Mailjet Output format
     *
     * @access	public
     * @var		const PHP
     */
    const PHP = 'php';

    /**
     * Mailjet Output format
     *
     * @access	public
     * @var		const JSON
     */
    const JSON = 'json';

    /**
     * Mailjet Output format
     *
     * @access	public
     * @var		const XML
     */
    const XML = 'xml';

    /**
     * Mailjet Output format
     *
     * @access	public
     * @var		const SERIALIZE
     */
    const SERIALIZE = 'serialize';

    /**
     * Mailjet Output format
     *
     * @access	public
     * @var		const HTML
     */
    const HTML = 'html';

    /**
     * Mailjet Output format
     *
     * @access	public
     * @var		const CSV
     */
    const CSV = 'csv';

    /**
     * Mailjet Debug Flag
     * - No debug
     *
     * @access	public
     * @var		const PRODUCTION
     */
    const PRODUCTION = 0;

    /**
     * Mailjet Debug Flag
     * - Errors only
     *
     * @access	public
     * @var		const TESTING
     */
    const TESTING = 1;

    /**
     * Mailjet Debug Flag
     * - Always
     *
     * @access	public
     * @var		const DEVELOPMENT
     */
    const DEVELOPMENT = 2;

    /**
     * Mailjet API Errors status
     *
     * @access	private
     * @var		array $_errors
     */
    private $_errors = NULL;
    
    /**
     * 
     * @var array
     */
    protected $_contactLists = array();

    /**
     * Constructor
     *
     * Set $_apiKey and $_secretKey if provided, change the error handler
     * and fill the errors array with Mailjet errors status and description
     *
     * @access	public
     * @uses	Mailjet::ApiOverlay::$_api
     * @uses	Mailjet::ApiOverlay::$_errors
     * @param string $apiKey    Mailjet API Key
     * @param string $secretKey Mailjet API Secret Key
     */
    public function __construct($apiKey = FALSE, $secretKey = FALSE)
    {
        $this->_api = new Mailjet_Api($apiKey, $secretKey);
        
        set_error_handler('Mailjet_ApiOverlay::phpErrorHandler');
        if ($apiKey && $secretKey)
            $this->createErrorsArray();
    }

    /**
     * Singleton pattern :
     * Get the instance of the object if it already exists
     * or create a new one.
     *
     * @access	public
     * @uses	Mailjet::ApiOverlay::$_instance
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
     * 
     * @return Api
     */
    public function getApi()
    {
    	return $this->_api;
    }

    /**
     * Destructor
     *
     * Unset the API object
     *
     * @access	public
     * @uses	Mailjet::ApiOverlay::$_api
     */
    public function __destruct()
    {
        if(!is_null($this->_api))
            unset($this->_api);
    }

    /**
     * Fill the errors array with Mailjet errors status and description
     *
     * @access	private
     * @uses	Mailjet::ApiOverlay::$_api
     * @uses	Mailjet::ApiOverlay::$_errors
     */
    private function createErrorsArray()
    {
    	return;
        $response = $this->getAPIStatus();
        $this->_errors = array();
        foreach ($response->status as $error)
            $this->_errors[$error->code] = '['.$error->code.'] '.$error->status.' : '.$error->description;
    }

    /**
     * Update or set $_apiKey and $_secretKey
     * and fill the errors array with Mailjet errors status and description
     *
     * @access	public
     * @param string $apiKey    Mailjet API Key
     * @param string $secretKey Mailjet API Secret Key
     */
    public function setKeys($apiKey, $secretKey)
    {
        $this->_api->setKeys($apiKey, $secretKey);
        $this->createErrorsArray();
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

    /**
     * Get the last Response HTTP Code
     *
     * @access	public
     * @uses	Mailjet::Api::$_response_code
     * @return integer last Response HTTP Code
     */
    public function getHTTPCode()
    {
        return ($this->_api->getHTTPCode());
    }

    /**
     * Get the response from the last call
     *
     * @access	public
     * @uses	Mailjet::Api::$_response
     * @return mixed Response from the last call
     */
    public function getResponse()
    {
        return ($this->_api->getResponse());
    }

    /**
     * Get the last debug Error Html
     *
     * @access	public
     * @uses	Mailjet::Api::$_debugErrorHtml
     * @return string last Error as a HTML table
     */
    public function getErrorHtml()
    {
        return ($this->_api->getErrorHtml());
    }

    /**
     * Set the current API output format
     *
     * @access	public
     * @param const $output API output format
     *
     * @return boolean TRUE on success, FALSE otherwise
     */
    public function setOutput($output)
    {
        if (in_array($output, array(self::PHP, self::JSON, self::XML,
                            self::SERIALIZE, self::HTML, self::CSV))) {
            $this->_api->setOutput($output);

            return (TRUE);
        }

        return (FALSE);
    }

    /**
     * Get the current API output format
     *
     * @access	public
     *
     * @return string API output format
     */
    public function getOutput()
    {
        return ($this->_api->getOutput());
    }

    /**
     * Set the debug flag :
     * PRODUCTION = none / TESTING = errors only / DEVELOPMENT = always
     *
     * @access	public
     * @param integer $debug Debug flag
     */
    public function setDebugFlag($debug)
    {
        if (in_array($debug, array(self::PRODUCTION, self::TESTING, self::DEVELOPMENT))) {
            $this->_api->setDebugFlag($debug);

            return (TRUE);
        }

        return (FALSE);
    }

    /**
     * Get the debug flag :
     * PRODUCTION = none / TESTING = errors only / DEVELOPMENT = always
     *
     * @access	public
     *
     * @return integer Debug flag
     */
    public function getDebugFlag()
    {
        return ($this->_api->getDebugFlag());
    }

    /**
     * Set the default nb of seconds before updating the cached object
     * If set to 0, Object caching will be disabled
     *
     * @access	public
     * @param integer $cache Cache to set in seconds
     */
    public function setCachePeriod($cache)
    {
        $this->_api->setCachePeriod($cache);
    }

    /**
     * Get the default nb of seconds before updating the cached object
     * If set to 0, Object caching will be disabled
     *
     * @access	public
     *
     * @return integer Cache in seconds
     */
    public function getCachePeriod()
    {
        return ($this->_api->getCachePeriod());
    }

    /**
     * Set the Cache path
     *
     * @access	public
     * @param string $cache_path path to the cached objects
     *
     * @return boolean TRUE if the path is successfully set, FALSE otherwise
     */
    public function setCachePath($cache_path)
    {
        return ($this->_api->setCachePath($cache_path));
    }

    /**
     * Get the cache path
     *
     * @access	public
     *
     * @return string path to the cached objects
     */
    public function getCachePath()
    {
        return ($this->_api->getCachePath());
    }

    /**
     * PHP Error Handler / Scalar Type-Hinting
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string $ErrLevel   Error Level (Required)
     * @param string $ErrMessage Error Message (Required)
     *
     * @return mixed FALSE if no Scalar type error, TRUE and throw an exception otherwise
     */
    public static function phpErrorHandler($ErrLevel, $ErrMessage)
    {
        if ($ErrLevel == E_RECOVERABLE_ERROR) {
            if (strpos($ErrMessage, 'must be an instance of Mailjet\string, string')
                || strpos($ErrMessage, 'must be an instance of Mailjet\integer, integer')
                || strpos($ErrMessage, 'must be an instance of Mailjet\float, double')
                || strpos($ErrMessage, 'must be an instance of Mailjet\boolean, boolean')
                || strpos($ErrMessage, 'must be an instance of Mailjet\resource, resource')) {
                    return (TRUE);
                }
            throw new Mailjet_ApiException(0, '[DataType] '.str_replace('Mailjet\\', '', $ErrMessage));

            return (FALSE);
        }
    }

    /***************************** API *****************************/

    /**
     * API : Create a new Sub-Account with new API keys
     * - url : api.mailjet.com/0.1/apiKeyadd
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string $name          Custom name (Required)
     * @param string $custom_status Custom status : 'up', 'suspend', 'down' (Optional)
     *
     * @return mixed Response from the API
     */
    public function createSubAccount(string $name, string $custom_status = null)
    {
        $params = array(
            'method'	=> 'POST',
            'name'		=> $name
        );
        if (!is_null($custom_status))
            $params['custom_status'] = $custom_status;

        $response = $this->_api->apiKeyadd($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * API : Create a new Sub-Account with new API keys
     * - url : api.mailjet.com/0.1/apiKeyadd
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function createSubAccountP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->createSubAccount($parameters->name, $parameters->custom_status);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * API : Create a token for partial white-labeling of Mailjet
     * - url : api.mailjet.com/0.1/apiKeyauthenticate
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param array  $allowed_access 'campaigns', 'contacts', 'reports', 'stats', 'preferences' (Required)
     * @param string $type           default_url format : 'url', 'iframe', 'page' (Optional)
     * @param string $default_page   with a value within $allowed_access (Optional)
     * @param string $lang           Language : 'en', 'fr', 'de', 'es' (Optional)
     * @param string $timezone       Valid timezone (Optional)
     *
     * @return mixed Response from the API
     */
    public function createToken(array $allowed_access, string $type = null, string $default_page = null, string $lang = null, string $timezone = null)
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

        $response = $this->_api->apiKeyauthenticate($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * API : Create a token for partial white-labeling of Mailjet
     * - url : api.mailjet.com/0.1/apiKeyauthenticate
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function createTokenP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->createToken($parameters->allowed_access, $parameters->type, $parameters->default_page, $parameters->lang, $parameters->timezone);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * API : Get a list of the Sub-Account's API keys
     * - url : api.mailjet.com/0.1/apiKeylist
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param boolean $active        Mailjet's approval status : 0=inactive, 1=active (Optional)
     * @param string  $custom_status custom status : 'up', 'suspend', 'down' (Optional)
     * @param string  $name          Custom name. Use * as a joker for a research (Optional)
     * @param boolean $type          1=main, 0=subuser (Optional)
     * @param string  $api_key       Api public key (Optional)
     * @param integer $cache         Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getSubAccounts(boolean $active = null, string $custom_status = null, string $name = null, boolean $type = null, string $api_key = null, integer $cache = null)
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
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->apiKeylist($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * API : Get a list of the Sub-Account's API keys
     * - url : api.mailjet.com/0.1/apiKeylist
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getSubAccountsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getSubAccounts($parameters->active, $parameters->custom_status, $parameters->name,$parameters->type, $parameters->api_key, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * API : Get the secret of one of your Sub-Account
     * - url : api.mailjet.com/0.1/apiKeysecret
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string  $api_key Api public key (Required)
     * @param integer $cache   Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getSubAccountSecret(string $api_key, integer $cache = null)
    {
        $params = array(
            'method'	=> 'GET',
            'apikey'	=> $api_key
        );
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->apiKeysecret($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * API : Get the secret of one of your Sub-Account
     * - url : api.mailjet.com/0.1/apiKeysecret
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getSubAccountSecretP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getSubAccountSecret($parameters->api_key, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * API : Update the secret of one of your Sub-Account
     * - url : api.mailjet.com/0.1/apiKeysecretchange
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string $api_key Api public key (Required)
     *
     * @return mixed Response from the API
     */
    public function updateSubAccountSecret(string $api_key)
    {
        $params = array(
            'method'	=> 'GET',
            'apikey'	=> $api_key

        );

        $response = $this->_api->apiKeysecretchange($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * API : Update the secret of one of your Sub-Account
     * - url : api.mailjet.com/0.1/apiKeysecretchange
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function updateSubAccountSecretP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->updateSubAccountSecret($parameters->api_key);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * API : Update your Sub-Account informations
     * - url : api.mailjet.com/0.1/apiKeyupdate
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string $api_key       Api public key (Required)
     * @param string $custom_status custom status : 'up', 'suspend', 'down' (Optional)
     * @param string $name          Custom name (Optional)
     *
     * @return mixed Response from the API
     */
    public function updateSubAccount(string $api_key, string $custom_status = null, string $name = null)
    {
        $params = array(
            'method'	=> 'POST',
            'apikey'	=> $api_key
        );
        if (!is_null($custom_status))
            $params['custom_status'] = $custom_status;
        if (!is_null($name))
            $params['name'] = $name;

        $response = $this->_api->apiKeyupdate($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * API : Update your Sub-Account informations
     * - url : api.mailjet.com/0.1/apiKeyupdate
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function updateSubAccountP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->updateSubAccount($parameters->api_key, $parameters->custom_status, $parameters->name);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /***************************** USER *****************************/

    /**
     * USER : Add a new trust Domain to your sender addresses
     * - url : api.mailjet.com/0.1/userDomainadd
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string $domain Your domain name (Required)
     *
     * @return mixed Response from the API
     */
    public function createDomain(string $domain)
    {
        $params = array(
            'method'	=> 'POST',
            'domain'	=> $domain
        );

        $response = $this->_api->userDomainadd($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * USER : Add a new trust Domain to your sender addresses
     * - url : api.mailjet.com/0.1/userDomainadd
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function createDomainP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->createDomain($parameters->domain);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * USER : Get your trust Domains from your sender addresses
     * - url : api.mailjet.com/0.1/userDomainList
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException

     * @param integer $cache Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getTrustDomains(integer $cache = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->userDomainList($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * USER : Get your trust Domains from your sender addresses
     * - url : api.mailjet.com/0.1/userDomainList
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getTrustDomainsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getTrustDomains($parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * USER : Get the status of one of your trust Domains
     * - url : api.mailjet.com/0.1/userDomainStatus
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string  $domain      Your domain name (Required)
     * @param boolean $force_check Set to 1 to force a verification (Optional)
     * @param integer $cache       Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getDomainStatus(string $domain, boolean $force_check = null, integer $cache = null)
    {
        $params = array(
            'method'	=> 'POST',
            'domain'	=> $domain
        );
        if (!is_null($force_check))
            $params['force_check'] = $force_check;
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->userDomainStatus($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * USER : Get the status of one of your trust Domains
     * - url : api.mailjet.com/0.1/userDomainStatus
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getDomainStatusP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getDomainStatus($parameters->domain, $parameters->force_check, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * USER : Get Your account and profile informations
     * - url : api.mailjet.com/0.1/userInfos
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $cache Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getUser($cache = null)
    {
//         $params = array(
//             'method'	=> 'GET'
//         );
//         if (!is_null($cache))
//             $params['cache'] = $cache;

//         $response = $this->_api->userInfos($params);
//         if ($response !== FALSE)
//             return ($response);
//         else
//             throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
        
        

        	$paramsProfile = array(
        		'method'	 	=> 'GET',
        	);
        	
        	$this->_api->resetRequest();
        	$this->_api->user($paramsProfile);
        	
        	$responesProfile = $this->_api->getResponse();
        
        	if ($responesProfile->Count > 0) {

        		$this->_api->resetRequest();
        		$this->_api->myprofile($paramsProfile);
        		 
        		$responesMyProfile = $this->_api->getResponse();
        	
        		if ($responesMyProfile->Count > 0) {
        			$responesProfileArray = (array) $responesProfile->Data[0];
        			unset($responesProfileArray['ID']);
        			return (object) array_merge($responesProfileArray, (array) $responesMyProfile->Data[0]);
        		}
        	}
      
        
        return false;
    }

    /**
     * USER : Get Your account and profile informations
     * - url : api.mailjet.com/0.1/userInfos
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getUserP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getUser($parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * USER : Get the Plan you are currently subscribed
     * - url :
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $cache Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getUserPlan($cache = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->userAccountInfos($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * USER : Get the Plan you are currently subscribed
     * - url :
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getUserPlanP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getUserPlan($parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * USER : Create a new Sender email
     * - url : api.mailjet.com/0.1/userSenderAdd
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string $email Sender email address (Required)
     *
     * @return mixed Response from the API
     */
    public function createSender($email)
    {
  
    	if (strpos($email,'@') === false) {
    		$email = '*@'.$email;
    	}
    	
        $params = array(
            'method'	=> 'JSON',
            'Email'		=> $email
        );
        
        $this->_api->resetRequest();
        $this->_api->sender($params);
         
        $responesProfile = $this->_api->getResponse();

        if ($responesProfile->Count > 0) {
        	return $responesProfile->Data[0];
        }
        
        return false;

        $response = $this->_api->sender($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * USER : Create a new Sender email
     * - url : api.mailjet.com/0.1/userSenderAdd
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function createSenderP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->createSender($params->email);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * USER : Get your sender email addresses
     * - url : api.mailjet.com/0.1/userSenderList
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $cache Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getSenders($cache = null, $user = null)
    {

    	$params = array(
    		'method' => 'GET',
    		'style'	=> 'full',
            'limit' => 0
    	);
    	$this->_api->resetRequest();
    	$this->_api->sender($params);
    	$responesSender = $this->_api->getResponse();
    	if ($responesSender->Count > 0) {
    		return $responesSender->Data;
    	}
    	return false;
    }

    /**
     * USER : Get your sender email addresses
     * - url : api.mailjet.com/0.1/userSenderList
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getSendersP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getSenders($parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }
        return $response;
    }

    /**
     * USER : Get the status of one of your sender email addresses
     * - url : api.mailjet.com/0.1/userSenderStatus
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string  $email Sender email address (Required)
     * @param integer $cache Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getSenderStatus($email, $cache = null)
    {
        $params = array(
            'method'	=> 'POST',
            'email'		=> $email
        );
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->userSenderStatus($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * USER : Get the status of one of your sender email addresses
     * - url : api.mailjet.com/0.1/userSenderStatus
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getSenderStatusP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getSenderStatus($parameters->email, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * USER : Get your tracking preferences
     * - url : api.mailjet.com/0.1/userTrackingCheck
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $cache Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getTracking($cache = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->userTrackingCheck($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * USER : Get your tracking preferences
     * - url : api.mailjet.com/0.1/userTrackingCheck
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getTrackingP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getTracking($parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * USER : Update your tracking preferences
     * - url : api.mailjet.com/0.1/userTrackingUpdate
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
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

        $response = $this->_api->userTrackingUpdate($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * USER : Update your tracking preferences
     * - url : api.mailjet.com/0.1/userTrackingUpdate
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function updateTrackingP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->updateTracking($parameters->click, $parameters->open);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * USER : Update Your account and profile informations
     * - url : api.mailjet.com/0.1/userUpdate
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
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
    	$user = $this->getUser();
        $params = array(
            'method'	=> 'PUT',
        	'ID'		=> $user->ID,
        );
        
        if (!is_null($address_city))
            $params['AddressStreet'] = $address_city;
        if (!is_null($address_country))
            $params['AddressCountry'] = $address_country;
        if (!is_null($address_postal_code))
            $params['AddressPostalCode'] = $address_postal_code;
        if (!is_null($address_street))
            $params['AddressStreet'] = $address_street;
        if (!is_null($company_name))
            $params['CompanyName'] = $company_name;
//         if (!is_null($contact_email))
//             $params['contact_email'] = $contact_email;
        if (!is_null($firstname))
            $params['Firstname'] = $firstname;
        if (!is_null($lastname))
            $params['Lastname'] = $lastname;
//         if (!is_null($locale))
//             $params['locale'] = $locale;

        $this->_api->resetRequest();
        $this->_api->myprofile($params);
        $response = $this->_api->getResponse();

        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * USER : Update Your account and profile informations
     * - url : api.mailjet.com/0.1/userUpdate
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function updateUserP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->updateUser($parameters->address_city, $parameters->address_country, $parameters->address_postal_code,
                                $parameters->address_street, $parameters->company_name, $parameters->contact_email,
                                $parameters->firstname, $parameters->lastname, $parameters->locale);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**************************** MESSAGE ****************************/

    /**
     * MESSAGE : Get your campaigns
     * - url : api.mailjet.com/0.1/messageCampaigns
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $id      Mailjet's Campaign ID (Optional)
     * @param integer $start   Start offset (Optional)
     * @param integer $limit   Limit amount of results you want (Optional)
     * @param string  $status  Campaign status filter: "draft", "programmed", "sent", "archived". Filters can be combined, separated by a comma (Optional)
     * @param string  $orderby Order results by any returned parameter's name : default=id ASC (Optional)
     * @param integer $cache   Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getCampaigns($id = null, $start = null, $limit = null,
                                    $status = null, $orderby = null, $cache = null)
    {

	    	$paramsProfile = array(
	    			'method'	 	=> 'GET',
	    			'limit'			=> 0,
	    			'style'			=> 'full',
	    	);	

	    	 
	    	$this->_api->resetRequest();
	    	$this->_api->newsletter($paramsProfile);
	    	 
	    	$responesProfile = $this->_api->getResponse();

	    	$camp = false;
	    	
	    	if (!id) {
	    		if ($responesProfile->Count > 0) {
	    			$camp = $responesProfile->Data;
	    		}
	    	} else {
	    		if ($responesProfile->Count > 0) {
	    			foreach ($responesProfile->Data as $campaign) {
	    		
	    				if ($campaign->ID == $id) {
	    					$camp = $campaign;
	    				}
	    			}
	    		}
	    	}

	    	
	    	return $camp;
    	
    	
    	
    	
//         $params = array(
//             'method'	=> 'GET'
//         );
//         if (!is_null($id))
//             $params['id'] = $id;
//         if (!is_null($start))
//             $params['start'] = $start;
//         if (!is_null($limit))
//             $params['limit'] = $limit;
//         if (!is_null($status))
//             $params['status'] = $status;
//         if (!is_null($orderby))
//             $params['orderby'] = $orderby;
//         if (!is_null($cache))
//             $params['cache'] = $cache;

//         $response = $this->_api->messageCampaigns($params);
//         if ($response !== FALSE)
//             return ($response);
//         else
//             throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * MESSAGE : Get your campaigns
     * - url : api.mailjet.com/0.1/messageCampaigns
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getCampaignsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getCampaigns($parameters->id, $parameters->start, $parameters->limit,
                                   $parameters->status, $parameters->orderby, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * MESSAGE : Get the complete list of subscribers to a specific message
     * - url : api.mailjet.com/0.1/messageContacts
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $id     Mailjet's Campaign ID (Required)
     * @param integer $start  Start offset (Optional)
     * @param integer $limit  Limit amount of results you want (Optional)
     * @param string  $status Message status filter: queued, sent, opened, clicked, bounce, blocked, spam or unsub (Optional)
     * @param integer $cache  Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getSubscribers($id, $start = null, $limit = null,
                                    $status = null, $cache = null)
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
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->messageContacts($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * MESSAGE : Get the complete list of subscribers to a specific message
     * - url : api.mailjet.com/0.1/messageContacts
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getSubscribersP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getSubscribers($parameters->id, $parameters->start, $parameters->limit,
                                   $parameters->status, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * MESSAGE : Create a new campaign available to be directly sent or programmed
     * - url : api.mailjet.com/0.1/messageCreateCampaign
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string  $lang         Language : en, fr, de, it, es, nl, sv, pt, ru, ja, lv, is, ro, el, ar, sk (Required)
     * @param string  $from         Sender email address (Required)
     * @param string  $from_name    Sender name (Optional)
     * @param string  $subject      Subject of the campaign (Required)
     * @param string  $edition_mode Edition mode : [tool]=WYSYWIG tool [html]=Raw HTML tool (Optional)
     * @param string  $edition_type Edition type : [full]=all steps [light]=step 2 and 3 [ulight]=step 2 only (Optional)
     * @param integer $list_id      Mailjet's contacts list ID. Required if edition_type = [light] (Optional)
     * @param string  $callback     Callback URL. Required if edition_type = [ulight] (Optional)
     * @param string  $footer       [default]=show [none]=hide . Required if edition_mode = [tool] (Optional)
     * @param string  $permalink    [default]=show [none]=hide (Optional)
     * @param integer $template_id  Mailjet's template ID (Optional)
     * @param string  $token        Unique token (Optional)
     * @param string  $reply_to     Replace the default 'reply-to' address (sender email) (Optional)
     * @param string  $title        Used in Mailjet's interface, to replace the subject (Optional)
     *
     * @return mixed Response from the API
     */
    public function createCampaign(string $lang, string $from, string $from_name = null, string $subject,
                                    string $edition_mode = null, string $edition_type = null,
                                    integer $list_id = null, string $callback = null, string $footer = null,
                                    string $permalink = null, integer $template_id = null, string $token = null,
                                    string $reply_to = null, string $title = null)
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

        $response = $this->_api->messageCreateCampaign($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * MESSAGE : Create a new campaign available to be directly sent or programmed
     * - url : api.mailjet.com/0.1/messageCreateCampaign
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function createCampaignP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->createCampaign($parameters->lang, $parameters->from, $parameters->from_name, $parameters->subject,
                                    $parameters->edition_mode, $parameters->edition_type,
                                    $parameters->list_id, $parameters->callback, $parameters->footer,
                                    $parameters->permalink, $parameters->template_id, $parameters->token,
                                    $parameters->reply_to, $parameters->title);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * MESSAGE : Create a new campaign from an old one
     * - url : api.mailjet.com/0.1/messageDuplicateCampaign
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
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
    public function createCampaignFrom(integer $id, string $lang = null, string $from = null, string $from_name = null, string $subject = null,
                                    integer $list_id = null, string $callback = null, string $footer = null, string $permalink = null,
                                    integer $template_id = null, string $reply_to = null, string $title = null)
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

        $response = $this->_api->messageDuplicateCampaign($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * MESSAGE : Create a new campaign from an old one
     * - url : api.mailjet.com/0.1/messageDuplicateCampaign
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function createCampaignFromP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->createCampaignFrom($parameters->id, $parameters->lang, $parameters->from, $parameters->from_name, $parameters->subject,
                                    $parameters->list_id, $parameters->callback, $parameters->footer, $parameters->permalink,
                                    $parameters->template_id, $parameters->reply_to, $parameters->title);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * MESSAGE : Get the HTML source from one of your campaigns
     * - url : api.mailjet.com/0.1/messageHtmlCampaign
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $id    Mailjet's Campaign ID (Required)
     * @param integer $cache Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getCampaignHTML($id, $cache = null)
    {
    	$campaign = $this->getCampaigns($id);

    	$this->_api->resetRequest();
    	$this->_api->data('newsletter', $id, 'HTML', 'text/html', null, 'GET', 'LAST');
	
    	$respones = $this->_api->getResponse();

    	return $respones;
    	
    	
        $call_newsletter_html = $this->_dataApi->DATA('GET', 'NewsLetter', $campaign->NewsLetterID, 'HTML', 'text/html', 'LAST', NULL, null);
		
		if(!isset($call_newsletter_html->ErrorInfo))
			$contents = $call_newsletter_html;
		
		
    	$this->_api->setVersion('DATA')->newsletter($params);
		$appId = NULL;//get_app_id();
    	$call_newsletter_html = $this->mailjetdata->DATA('GET', 'NewsLetter', $newsletterID, 'HTML', 'text/html', 'LAST', NULL, $appId);
    	 
    	 
    	$respones = $this->_api->getResponse();
    	
    	echo '<pre>';
    	print_r($respones); die;
    	
    	
        $params = array(
            'method'	=> 'GET',
            'id'		=> $id
        );
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->messageHtmlCampaign($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * MESSAGE : Get the HTML source from one of your campaigns
     * - url : api.mailjet.com/0.1/messageHtmlCampaign
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getCampaignHTMLP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getCampaignHTML($parameters->id, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * MESSAGE : Get your messages with some filters
     * - url : api.mailjet.com/0.1/messageList
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string    $custom_campaign Your custom campaign name (Optional)
     * @param string    $from            Sender email address (Optional)
     * @param string    $from_name       Sender name (Optional)
     * @param string    $to_email        Recipient's email address (Optional)
     * @param integer   $mj_campaign_id  Mailjet's Campaign ID (Optional)
     * @param timestamp $sent_after      Minimum date of sending (Optional)
     * @param timestamp $sent_before     Maximum date of sending (Optional)
     * @param integer   $start           Start offset (Optional)
     * @param integer   $limit           Limit amount of results you want (Optional)
     * @param integer   $cache           Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getMessages(string $custom_campaign = null, string $from = null, string $from_name = null,
                                string $to_email = null, integer $mj_campaign_id = null,
                                $sent_after = null, $sent_before = null,
                                integer $start = null, integer $limit = null, integer $cache = null)
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
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->messageList($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * MESSAGE : Get your messages with some filters
     * - url : api.mailjet.com/0.1/messageList
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getMessagesP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getMessages($parameters->custom_campaign, $parameters->from, $parameters->from_name,
                                $parameters->to_email, $parameters->mj_campaign_id,
                                $parameters->sent_after, $parameters->sent_before,
                                $parameters->start, $parameters->limit, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * MESSAGE : Send a campaign instantly
     * - url : api.mailjet.com/0.1/messageSendCampaign
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $id Mailjet's Campaign ID (Required)
     *
     * @return mixed Response from the API
     */
    public function sendCampaign(integer $id)
    {
        $params = array(
            'method'	=> 'POST',
            'id'		=> $id
        );

        $response = $this->_api->messageSendCampaign($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * MESSAGE : Send a campaign instantly
     * - url : api.mailjet.com/0.1/messageSendCampaign
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function sendCampaignP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->sendCampaign($parameters->id);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * MESSAGE : Update the HTML and TXT source from one of your campaigns
     * - url : api.mailjet.com/0.1/messageSetHtmlCampaign
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $id   Mailjet's Campaign ID (Required)
     * @param string  $html Raw HTML code of your Email. It must contain the unsubscribe tag (in en: [[UNSUB_LINK_EN]]) (Required)
     * @param string  $text Text version of your Email. It must contain the unsubscribe tag (in en: [[UNSUB_LINK_EN]]) (Optional)
     *
     * @return mixed Response from the API
     */
    public function updateCampaignHTML($id, $html, $text = null)
    {
    	$this->_api->resetRequest();
    	$this->_api->data('newsletter', $id, 'HTML', 'text/html', $html, 'POST', null);
	
    	$respones = $this->_api->getResponse();
        
    	return $respones;
        
        
        if (!is_null($text))
            $params['text'] = $text;

        $response = $this->_api->messageSetHtmlCampaign($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * MESSAGE : Update the HTML and TXT source from one of your campaigns
     * - url : api.mailjet.com/0.1/messageSetHtmlCampaign
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function updateCampaignHTMLP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->updateCampaignHTML($parameters->id, $parameters->html, $parameters->text);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * MESSAGE : Get light statistics on one of your campaigns
     * - url : api.mailjet.com/0.1/messageStatistics
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $id    Mailjet's Campaign ID (Required)
     * @param integer $cache Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getCampaignStatistics($id = null, $cache = null)
    {
    	
    	$paramsProfile = array(
    			'method'	 	=> 'GET',
    			'ID'			=> $id,
    			'style'			=> 'full',
    	);
    	
    	 
    	$this->_api->resetRequest();
    	$this->_api->campaignstatistics($paramsProfile);
    	 
    	$responesProfile = $this->_api->getResponse();

    	if ($responesProfile->Count === 1) {
    		return $responesProfile->Data[0];
    	}
    	
    	return isset($responesProfile->Data) ? $responesProfile->Data : false;
    	
        $params = array(
            'method'	=> 'GET',
            'id'		=> $id
        );
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->messageStatistics($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * MESSAGE : Get light statistics on one of your campaigns
     * - url : api.mailjet.com/0.1/messageStatistics
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getCampaignStatisticsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getCampaignStatistics($parameters->id, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * MESSAGE : Test a campaign
     * - url : api.mailjet.com/0.1/messageTestcampaign
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
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

        $response = $this->_api->messageTestcampaign($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * MESSAGE : Test a campaign
     * - url : api.mailjet.com/0.1/messageTestcampaign
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function testCampaignP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->testCampaign($parameters->id, $parameters->email);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * MESSAGE : Get Mailjet's template categories
     * - url : api.mailjet.com/0.1/messageTplCategories
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $cache Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getTemplateCategories($cache = null)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->messageTplCategories($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * MESSAGE : Get Mailjet's template categories
     * - url : api.mailjet.com/0.1/messageTplCategories
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getTemplateCategoriesP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getTemplateCategories($parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * MESSAGE : Get Mailjet's template models
     * - url : api.mailjet.com/0.1/messageTplModels
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $category Mailjet's template category ID (Optional)
     * @param boolean $custom   If true, returns the user's templates (Optional)
     * @param string  $locale   Language : fr_FR, en_US, de_DE, ... (Optional)
     * @param integer $cache    Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getTemplates($category = null, $custom = null, $locale = null, $cache = null)
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
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->messageTplModels($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * MESSAGE : Get Mailjet's template models
     * - url : api.mailjet.com/0.1/messageTplModels
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getTemplatesP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getTemplates($parameters->category, $parameters->custom, $parameters->locale, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * MESSAGE : Update a campaign
     * - url : api.mailjet.com/0.1/messageUpdateCampaign
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
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
    public function updateCampaign(integer $id, string $status = null, string $lang = null, string $from = null, string $from_name = null, string $subject = null,
                                    integer $list_id = null, string $callback = null, string $footer = null, string $permalink = null,
                                    integer $template_id = null, string $reply_to = null, string $title = null,
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

        $response = $this->_api->messageUpdateCampaign($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * MESSAGE : Update a campaign
     * - url : api.mailjet.com/0.1/messageUpdateCampaign
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function updateCampaignP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->updateCampaign($parameters->id, $parameters->status, $parameters->lang, $parameters->from, $parameters->from_name, $parameters->subject,
                                    $parameters->list_id, $parameters->callback, $parameters->footer, $parameters->permalink,
                                    $parameters->template_id, $parameters->reply_to, $title,
                                    $parameters->sending_date);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**************************** CONTACTS ****************************/

    /**
     * CONTACTS : Get general informations about a specific contact
     * - url : api.mailjet.com/0.1/contactInfos
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string  $contact Mailjet's contact ID or email (Required)
     * @param integer $cache   Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getContactInformations($contact, $cache = null)
    {
        $params = array(
            'method'	=> 'GET',
            'contact'	=> $contact
        );
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->contactInfos($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * CONTACTS : Get general informations about a specific contact
     * - url : api.mailjet.com/0.1/contactInfos
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getContactInformationsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getContactInformations($parameters->contact, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * CONTACTS : Get your contacts with some filters
     * - url : api.mailjet.com/0.1/contactList
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer   $mj_contact_id Mailjet's Contact ID (Optional)
     * @param integer   $start         Start offset (Optional)
     * @param integer   $limit         Limit amount of results you want : default=100 (Optional)
     * @param string    $status        Contacts' status : opened, active, unactive or unsub (Optional)
     * @param boolean   $blocked       0=blocked, 1=active (Optional)
     * @param boolean   $unsub         0=subscriber, 1=unsubscribed (Optional)
     * @param timestamp $last_activity Minimum last activity timestamp (Optional)
     * @param integer   $cache         Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getContacts(integer $mj_contact_id = null, integer $start = null, integer $limit = null,
                                string $status = null, boolean $blocked = null, boolean $unsub = null, $last_activity = null,
                                integer $cache = null)
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
        if (!is_null($cache))
            $params['cache'] = $cache;

        if (in_array(array('start', 'limit', 'last_activity'), array_keys($params))
            && count($params) == 4) {
            $response = $this->_api->contactOpeners($params);
            if ($response !== FALSE)
                return ($response);
            else
                throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
        } else {
            $response = $this->_api->contactList($params);
            if ($response !== FALSE)
                return ($response);
            else
                throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
        }
    }

    /**
     * CONTACTS : Get your contacts with some filters
     * - url : api.mailjet.com/0.1/contactList
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getContactsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getContacts($parameters->mj_contact_id, $parameters->start, $parameters->limit,
                                $parameters->status, $parameters->blocked, $parameters->unsub, $parameters->last_activity,
                                $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /************************* CONTACTS LIST **************************/

    /**
     * LIST : Create a contact in a list
     * - url : api.mailjet.com/0.1/listsAddContact
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string  $contact Mailjet's contact ID or email (Required)
     * @param integer $list_id Mailjet's List ID (Required)
     * @param boolean $force   If the contact exists, reset unsub status (Optional)
     *
     * @return mixed Response from the API
     */
    public function createContact(string $contact, integer $list_id, boolean $force = null)
    {
        $params = array(
            'method'	=> 'POST',
            'contact'	=> $contact,
            'id'		=> $list_id
        );
        if (!is_null($force))
            $params['force'] = $force;

        $response = $this->_api->listsAddContact($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * LIST : Create a contact in a list
     * - url : api.mailjet.com/0.1/listsAddContact
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function createContactP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->createContact($parameters->contact, $parameters->list_id, $parameters->force);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * LIST : Create contacts in a list
     * - url : api.mailjet.com/0.1/listsAddManyContacts
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string  $contacts Serialized list of emails (Required)
     * @param integer $list_id  Mailjet's List ID (Required)
     * @param boolean $force    If the contact exists, reset unsub status (Optional)
     *
     * @return mixed Response from the API
     */
    public function createContacts($contacts, $list_id, $force = null)
    {
        $this->_api->resetRequest();
        $this->_api->data('contactslist', $list_id, 'CSVData', 'text/plain', $contacts, 'POST', null);
        //$call_data = $this->mailjetdata->DATA('POST', 'ContactsList', $this->list_id, 'CSVData', 'text/csv', NULL, $contacts, get_app_id());
  
        $responesProfile = $this->_api->getResponse();
        
        return Tools::jsonDecode($responesProfile);
        
        if (!is_null($force))
            $params['force'] = $force;

        $response = $this->_api->listsAddManyContacts($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }
    
    
    
    /**
     * setContactMetaData()
     * First checks if certain contact meta data field is already created, 
     * and if not - creates it in order to be populated by the followed createContacts call
     * @return boolean
     */
    public function setContactMetaData($params = array())
    {
            
        try {
            /*
            * Add contact properties like First Name and Last Name
            */
            $resContactMetaData = $this->getContactMetaData();

           
            if(isset($resContactMetaData->Data) && count($resContactMetaData->Data) > 0) {
                foreach($resContactMetaData->Data as $metaData) {
                    foreach ($params as $paramsMetaData) {
                        if(isset($metaData->Name) && $metaData->Name = $paramsMetaData['Name']) {
                            $flagName = $paramsMetaData['Name']."NameExists";
                            ${$flagName} = true;
                        }
                    }
                }
            }
            
            foreach ($params as $paramsMetaData) {
                $flagName = $paramsMetaData['Name']."NameExists";
                if(!${$flagName}) {
                    $resContactMetaData = $this->createContactMetaData($paramsMetaData); 
                }           
            }

            return true;

        } catch (Exception $ex) {
            MailJetLog::write(MailJetLog::$file, 'Exception : '.$ex->getMessage());
            throw new Mailjet_ApiException($ex->getMessage());
        }
        
        return false;
    }
    
    /**
     * 
     * @param type $contactEmail
     * @param type $params
     * @return type
     * @throws Mailjet_ApiException
     */
    public function createContactData($contactEmail, $params = array())
    {
        $contactData = $this->getContactByEmail($contactEmail);
      
        $paramsRequest = array(
            'method' => 'JSON',  // JSON
            'ID' => $contactData->Data[0]->ID,
            'Data' => $params,
    	);
         
        $this->_api->resetRequest();        
        $this->_api->contactdata($paramsRequest);
            
        $response = $this->_api->getResponse();
        if ($response !== FALSE) {
            return ($response);
        
        
        }
        else { 
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
        }
    }
    
    public function deleteContactData($contactEmail)
    {
        $paramsRequest = array(
            'method' => 'DELETE',
            'ID' => $contactEmail
    	);
            
        $this->_api->resetRequest();        
        $this->_api->contactdata($paramsRequest);
            
        $response = $this->_api->getResponse();
        if ($response !== FALSE) { 
            return ($response);
        }
        else { 
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
        }
    }
    
    public function getContactByEmail($contactEmail)
    {
        $paramsRequest = array(
            'method' => 'GET',
            'ID' => $contactEmail,
            'Email' => $contactEmail,
        );
        
        $this->_api->resetRequest();    
        
        $this->_api->contact($paramsRequest);
        
        $response = $this->_api->getResponse(); 
        
        if ($response !== FALSE) { 
            return ($response);
        }
        else {
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
        }
    }
    
    public function getContactMetaData()
    {
        $paramsRequest = array(
            'method' => 'GET',
        );
            
        $this->_api->resetRequest();        
        $this->_api->contactmetadata($paramsRequest);
           
        $response = $this->_api->getResponse();
            
        if ($response !== FALSE) {
            return ($response);
        }
        else { 
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
        }
    }
    
    
    public function createContactMetaData($params = array())
    {
        $paramsRequest = array(
            'method' => 'JSON',  // JSON
            'Datatype' => $params['Datatype'],
            'Name' => $params['Name'],
            'NameSpace' => $params['NameSpace'],
    	);
           
        $this->_api->resetRequest();        
        $this->_api->contactmetadata($paramsRequest);
            
        $response = $this->_api->getResponse();
        if ($response !== FALSE) {
            return ($response);
        }
        else { 
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
        }
    }
    
    
    public function batchJobContacts($listID, $dataID, $status = 'addforce')
    {

    	$paramsProfile = array(
    			'method' => 'JSON',  // JSON
    			'JobType' => 'Contact list import csv',
    			'DataID' => $dataID,
    			'Status' => 'Upload',
    			'RefId' => $listID,
    			'Method' => $status, // = 'addforce,remove,addnoforse'
    			'APIKeyALT'	=> $this->_api->getAPIKey()
    	);
    	
    	
    	$this->_api->resetRequest();
    	$this->_api->batchjob($paramsProfile);
    	
    	$responesProfile = $this->_api->getResponse();
    	 
    	if ($responesProfile->Count > 0) {
    		 
    		return $responesProfile->Data;
    	}
    	 
    	return false;
    }

    /**
     * LIST : Create contacts in a list
     * - url : api.mailjet.com/0.1/listsAddManyContacts
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function createContactsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->createContacts($parameters->contacts, $parameters->list_id, $parameters->force);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * LIST : Get your contacts lists with some filters
     * - url : api.mailjet.com/0.1/listsAll
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $start   Start offset (Optional)
     * @param integer $limit   Limit amount of results you want (Optional)
     * @param string  $orderby Order results by any returned parameter's name : default=id ASC (Optional)
     * @param integer $cache   Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getContactsLists($start = null, $limit = null, $orderby = null, $cache = null)
    {
    	if (!empty($this->_contactLists)) {
    		return $this->_contactLists;
    	}
    	
    	$paramsProfile = array(
    		'method'	 	=> 'GET',
    		'limit'			=> 0
    	);
    	 
    	$this->_api->resetRequest();
    	$this->_api->contactslist($paramsProfile);
    	 
    	$responesProfile = $this->_api->getResponse();
    	
    	if ($responesProfile->Count > 0) {
    		$this->_contactLists = $responesProfile->Data;
    		return $responesProfile->Data;
    	}
    	
    	return false;
    	
    	
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($start))
            $params['start'] = $start;
        if (!is_null($limit))
            $params['limit'] = $limit;
        if (!is_null($orderby))
            $params['order_by'] = $orderby;
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->listsAll($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * LIST : Get your contacts lists with some filters
     * - url : api.mailjet.com/0.1/listsAll
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getContactsListsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getContactsLists($parameters->start, $parameters->limit, $parameters->orderby, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * LIST : Get your contacts from a list with some filters
     * - url : api.mailjet.com/0.1/listsContacts
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer   $id            Mailjet's List ID (Required)
     * @param integer   $start         Start offset (Optional)
     * @param integer   $limit         Limit amount of results you want (Optional)
     * @param string    $orderby       Order results by any returned parameter's name : default=id ASC (Optional)
     * @param string    $status        Contacts' status : opened, active, unactive or unsub (Optional)
     * @param boolean   $blocked       0=blocked, 1=active (Optional)
     * @param boolean   $unsub         0=subscriber, 1=unsubscribed (Optional)
     * @param timestamp $last_activity Minimum last activity timestamp (Optional)
     * @param integer   $cache         Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getContactsFromList($id, $start = null, $limit = null, $orderby = null,
                                        $status = null, $blocked = null, $unsub = null,
                                        $last_activity = null, $cache = null)
    {

    	$params = array(
    			'method' => 'GET',
    			'ContactsList'    => $id,
    			'style' => 'full',
    			'limit'   => 0,
    			'offset' => 0,
    			'Status'  => ($status && $status !== 'all') ? $status : 'all',
    			'Unsub' => 'false',
    			'countrecords' => 1,
    			'recurse' => 1
    	);
    	 

    	$this->_api->resetRequest();

    	$se = $this->_api->listrecipient($params);

    	$response = $this->_api->getResponse();

    	if ($response && $response->Count > 0) {
    		return $response;
    	}
    	else {
    		return FALSE;
    	}
    	
    	
    	
    	$this->_api->data('contactslist', $id, 'CSVData', 'text/plain', null, 'GET', null);
    	//$this->_api->data('contacts', $id, 'HTML', 'text/html', null, 'GET', 'LAST');
    	
    	$respones = $this->_api->getResponse();
    	
    	return $respones;
    	
    	
    	
    	
    	
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
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->listsContacts($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * LIST : Get your contacts from a list with some filters
     * - url : api.mailjet.com/0.1/listsContacts
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getContactsFromListP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getContactsFromList($parameters->id, $parameters->start, $parameters->limit, $parameters->orderby,
                                        $parameters->status, $parameters->blocked, $parameters->unsub, $parameters->last_activity,
                                        $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * LIST : Create a new Contacts list
     * - url : api.mailjet.com/0.1/listsCreate
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string $label Title of your list (Required)
     * @param string $name  List name used as name@lists.mailjet.com (Required)
     *
     * @return mixed Response from the API
     */
    public function createContactsList(string $label, string $name)
    {
        $params = array(
            'method'	=> 'POST',
            'label'		=> $label,
            'name'		=> $name
        );

        $response = $this->_api->listsCreate($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }
    

    /**
     * LIST : Create a new Contacts list
     * - url : api.mailjet.com/0.1/listsCreate
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function createContactsListP($parameters)
    {

    	$this->_api->resetRequest();
    	$this->_api->contactslist($parameters);
    	
    	$responesProfile = $this->_api->getResponse();

    	if ($responesProfile->Count > 0) {
    		 
    		return $responesProfile->Data[0];
    	}
    	 
    	return false;
    	
    	
        try {
            $response = $this->createContactsList($parameters->label, $parameters->name);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * LIST : Delete a Contacts list
     * - url : api.mailjet.com/0.1/listsDelete
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
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

        $response = $this->_api->listsDelete($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * LIST : Delete a Contacts list
     * - url : api.mailjet.com/0.1/listsDelete
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function deleteContactsListP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->deleteContactsList($parameters->id);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * LIST : Get a Contacts list Email : [name] @ lists.mailjet.com
     * - url : api.mailjet.com/0.1/listsEmail
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $id    Mailjet's List ID (Required)
     * @param integer $cache Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getContactsListEmail(integer $id, integer $cache = null)
    {
        $params = array(
            'method'	=> 'GET',
            'id'		=> $id
        );
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->listsEmail($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * LIST : Get a Contacts list Email : [name] @ lists.mailjet.com
     * - url : api.mailjet.com/0.1/listsEmail
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getContactsListEmailP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getContactsListEmail($parameters->id, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * LIST : Delete a Contact from a list
     * - url : api.mailjet.com/0.1/listsRemoveContact
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
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

        $response = $this->_api->listsRemoveContact($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * LIST : Delete a Contact from a list
     * - url : api.mailjet.com/0.1/listsRemoveContact
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function deleteContactP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->deleteContact($parameters->contact, $parameters->id);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * LIST : Delete many Contacts from a list
     * - url : api.mailjet.com/0.1/listsRemoveManyContacts
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
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

        $response = $this->_api->listsRemoveManyContacts($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * LIST : Delete many Contacts from a list
     * - url : api.mailjet.com/0.1/listsRemoveManyContacts
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function deleteContactsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->deleteContacts($parameters->contacts, $parameters->id);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * LIST : Get advanced statistics concerning one of your list of contacts
     * - url : api.mailjet.com/0.1/listsStatistics
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $id    Mailjet's List ID (Required)
     * @param integer $cache Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getContactsListStatistics($id, $cache = null)
    {
        $params = array(
            'method'	=> 'GET',
            'id'		=> $id
        );
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->listsStatistics($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * LIST : Get advanced statistics concerning one of your list of contacts
     * - url : api.mailjet.com/0.1/listsStatistics
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getContactsListStatisticsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getContactsListStatistics($parameters->id, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * LIST : Unsubscribe a Contact from a list
     * - url : api.mailjet.com/0.1/listsUnsubContact
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
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

        $response = $this->_api->listsUnsubContact($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * LIST : Unsubscribe a Contact from a list
     * - url : api.mailjet.com/0.1/listsUnsubContact
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function unsubscribeContactP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->unsubscribeContact($parameters->contact, $parameters->id);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * LIST : Update a Contacts list
     * - url : api.mailjet.com/0.1/listsUpdate
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
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

        $response = $this->_api->listsUpdate($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * LIST : Update a Contacts list
     * - url : api.mailjet.com/0.1/listsUpdate
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function updateContactsListP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->updateContactsList($parameters->id, $parameters->label, $parameters->name);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /***************************** REPORT *****************************/

    /**
     * REPORT : Get your (tracked and clicked) links
     * - url : api.mailjet.com/0.1/reportClick
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param string    $order       Order direction (Optional)
     * @param string    $order_by    Order by: [date], [link], [by_email], [click_delay], [user_agent] (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     * @param integer   $cache       Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getClickedEmails(integer $campaign_id = null, string $from = null, integer $from_type = null,
                                    integer $start = null, integer $limit = null, string $order = null, string $order_by = null,
                                    $ts_from = null, $ts_to = null, integer $cache = null)
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
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->reportClick($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * REPORT : Get your (tracked and clicked) links
     * - url : api.mailjet.com/0.1/reportClick
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getClickedEmailsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getClickedEmails($parameters->campaign_id, $parameters->from, $parameters->from_type,
                                    $parameters->start, $parameters->limit, $parameters->order, $parameters->order_by,
                                    $parameters->ts_from, $parameters->ts_to, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * REPORT : Get a list of domains to which your emails are sent
     * - url : api.mailjet.com/0.1/reportDomain
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param string    $from_domain Domain (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     * @param integer   $cache       Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getDomains(integer $campaign_id = null, string $from = null, string $from_domain = null, integer $from_type = null,
                                    integer $start = null, integer $limit = null, $ts_from = null, $ts_to = null, integer $cache = null)
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
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->reportDomain($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * REPORT : Get a list of domains to which your emails are sent
     * - url : api.mailjet.com/0.1/reportDomain
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getDomainsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getDomains($parameters->campaign_id, $parameters->from, $parameters->from_domain, $parameters->from_type,
                                    $parameters->start, $parameters->limit, $parameters->ts_from, $parameters->ts_to, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * REPORT : Get a list of bounced emails
     * - url : api.mailjet.com/0.1/reportEmailBounce
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param string    $from_domain Domain (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     * @param integer   $cache       Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getBouncedEmails(integer $campaign_id = null, string $from = null, string $from_domain = null, integer $from_type = null,
                                    integer $start = null, integer $limit = null, $ts_from = null, $ts_to = null, integer $cache = null)
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
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->reportEmailBounce($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * REPORT : Get a list of bounced emails
     * - url : api.mailjet.com/0.1/reportEmailBounce
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getBouncedEmailsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getBouncedEmails($parameters->campaign_id, $parameters->from, $parameters->from_domain, $parameters->from_type,
                      $parameters->start, $parameters->limit, $parameters->ts_from, $parameters->ts_to, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * REPORT : Get email clients used to open your emails when tracked
     * - url : api.mailjet.com/0.1/reportEmailClients
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param string    $from_domain Domain (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     * @param integer   $cache       Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getEmailClients(integer $campaign_id = null, string $from = null, string $from_domain = null, integer $from_type = null,
                                    integer $start = null, integer $limit = null, $ts_from = null, $ts_to = null, integer $cache = null)
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
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->reportEmailClients($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * REPORT : Get email clients used to open your emails when tracked
     * - url : api.mailjet.com/0.1/reportEmailClients
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getEmailClientsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getEmailClients($parameters->campaign_id, $parameters->from, $parameters->from_domain, $parameters->from_type,
                                    $parameters->start, $parameters->limit, $parameters->ts_from, $parameters->ts_to, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * REPORT : Get all your messages informations (sender, subject, dates, ...) for a campaign
     * - url : api.mailjet.com/0.1/reportEmailInfos
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $campaign_id Mailjet's Campaign ID (Required)
     * @param integer $cache       Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getEmailInformations(integer $campaign_id, integer $cache = null)
    {
        $params = array(
            'method'		=> 'GET',
            'campaign_id'	=> $campaign_id
        );
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->reportEmailInfos($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * REPORT : Get all your messages informations (sender, subject, dates, ...) for a campaign
     * - url : api.mailjet.com/0.1/reportEmailInfos
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getEmailInformationsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getEmailInformations($parameters->campaign_id, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * REPORT : Get all emails sent with numerous filters
     * - url : api.mailjet.com/0.1/reportEmailSent
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param string    $from_domain Domain (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param string    $status      Email status : queued, sent, opened, clicked, bounce, blocked, spam or unsub (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     * @param integer   $cache       Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getSentEmails(integer $campaign_id = null, string $from = null, string $from_domain = null, integer $from_type = null,
                                    integer $start = null, integer $limit = null, string $status = null, $ts_from = null, $ts_to = null, integer $cache = null)
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
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->reportEmailSent($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * REPORT : Get all emails sent with numerous filters
     * - url : api.mailjet.com/0.1/reportEmailSent
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getSentEmailsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getSentEmails($parameters->campaign_id, $parameters->from, $parameters->from_domain, $parameters->from_type,
                                    $parameters->start, $parameters->limit, $parameters->status, $parameters->ts_from, $parameters->ts_to, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * REPORT : Get global and detailed statistics concerning your sendings
     * - url : api.mailjet.com/0.1/reportEmailStatistics
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
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
     * @param integer   $cache       Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getEmailStatistics(integer $campaign_id = null, string $from = null, string $from_domain = null, integer $from_type = null,
                                    string $to_email = null, integer $to_id = null, integer $start = null, integer $limit = null,
                                    $ts_from = null, $ts_to = null, integer $cache = null)
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
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->reportEmailStatistics($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * REPORT : Get global and detailed statistics concerning your sendings
     * - url : api.mailjet.com/0.1/reportEmailStatistics
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getEmailStatisticsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getEmailStatistics($parameters->campaign_id, $parameters->from, $parameters->from_domain, $parameters->from_type,
                                    $parameters->to_email, $parameters->to_id, $parameters->start, $parameters->limit,
                                    $parameters->ts_from, $parameters->ts_to, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * REPORT : Get geographic datas on where around the world your emails are opened
     * - url : api.mailjet.com/0.1/reportGeoIp
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param string    $from_domain Domain (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     * @param integer   $cache       Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getGeographicDatas(integer $campaign_id = null, string $from = null, string $from_domain = null, integer $from_type = null,
                                    integer $start = null, integer $limit = null, $ts_from = null, $ts_to = null, integer $cache = null)
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
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->reportGeoIp($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * REPORT : Get geographic datas on where around the world your emails are opened
     * - url : api.mailjet.com/0.1/reportGeoIp
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getGeographicDatasP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getGeographicDatas($parameters->campaign_id, $parameters->from, $parameters->from_domain, $parameters->from_type,
                                    $parameters->start, $parameters->limit, $parameters->ts_from, $parameters->ts_to, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * REPORT : Get a list of opened emails
     * - url : api.mailjet.com/0.1/reportOpen
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param string    $from_domain Domain (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     * @param integer   $cache       Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getOpenedEmails(integer $campaign_id = null, string $from = null, string $from_domain = null, integer $from_type = null,
                                    integer $start = null, integer $limit = null, $ts_from = null, $ts_to = null, integer $cache = null)
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
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->reportOpen($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * REPORT : Get a list of opened emails
     * - url : api.mailjet.com/0.1/reportOpen
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getOpenedEmailsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getOpenedEmails($parameters->campaign_id, $parameters->from, $parameters->from_domain, $parameters->from_type,
                                    $parameters->start, $parameters->limit, $parameters->ts_from, $parameters->ts_to, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * REPORT : Get Statistics of opened emails
     * - url : api.mailjet.com/0.1/reportOpenedStatistics
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param string    $from_domain Domain (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     * @param integer   $cache       Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getOpenedEmailsStatistics(integer $campaign_id = null, string $from = null, string $from_domain = null, integer $from_type = null,
                                    integer $start = null, integer $limit = null, $ts_from = null, $ts_to = null, integer $cache = null)
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
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->reportOpenedStatistics($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * REPORT : Get Statistics of opened emails
     * - url : api.mailjet.com/0.1/reportOpenedStatistics
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getOpenedEmailsStatisticsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getOpenedEmailsStatistics($parameters->campaign_id, $parameters->from, $parameters->from_domain, $parameters->from_type,
                                    $parameters->start, $parameters->limit, $parameters->ts_from, $parameters->ts_to, $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * REPORT : Get platform, browsers and versions used by your recipients
     * - url : api.mailjet.com/0.1/reportUserAgents
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer   $campaign_id Mailjet's Campaign ID (Optional)
     * @param string    $from        Sender email address (Optional)
     * @param integer   $from_id     Mailjet's Sender ID (Optional)
     * @param string    $from_domain Domain (Optional)
     * @param integer   $from_type   Email type : [0]=all [1]=transactional only [2]=campaigns only (Optional)
     * @param integer   $start       Start offset (Optional)
     * @param integer   $limit       Limit amount of results you want (Optional)
     * @param string    $status      Email status : queued, sent, opened, clicked, bounce, blocked, spam or unsub (Optional)
     * @param timestamp $ts_from     Beginning of the period (Optional)
     * @param timestamp $ts_to       End of the period (Optional)
     * @param integer   $cache       Cache period for the object (Optional)
     *
     * @return mixed Response from the API
     */
    public function getUserAgents(integer $campaign_id = null,
                                    string $from = null, integer $from_id = null, string $from_domain = null, integer $from_type = null,
                                    integer $start = null, integer $limit = null, string $status = null, $ts_from = null, $ts_to = null, integer $cache = null)
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
        if (!is_null($cache))
            $params['cache'] = $cache;

        $response = $this->_api->reportUserAgents($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * REPORT : Get platform, browsers and versions used by your recipients
     * - url : api.mailjet.com/0.1/reportUserAgents
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param Mailjet_Parameters $parameters (Required)
     *
     * @return mixed Response from the API
     */
    public function getUserAgentsP(Mailjet_Parameters $parameters)
    {
        try {
            $response = $this->getUserAgents($parameters->campaign_id,
                                    $parameters->from, $parameters->from_id, $parameters->from_domain, $parameters->from_type,
                                    $parameters->start, $parameters->limit, $parameters->status, $parameters->ts_from, $parameters->ts_to,
                                    $parameters->cache);
        } catch (Mailjet_ApiException $e) {
            throw $e;
        }

        return $response;
    }

    /**************************** HELP *****************************/

    /**
     * HELP : Get all categories of methods available and documented in our API
     * - url : api.mailjet.com/0.1/helpCategories
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $cache Cache period for the object - default to 600s = 10m (Optional)
     *
     * @return mixed Response from the API
     */
    public function getAPICategories($cache = 600)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($cache))
            $params['cache'] = intval($cache);

        $response = $this->_api->helpCategories($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * HELP : Get description of a category and embeded methods available and documented in our API
     * - url : api.mailjet.com/0.1/helpCategory
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string  $name  Category name (Required)
     * @param integer $cache Cache period for the object - default to 600s = 10m (Optional)
     *
     * @return mixed Response from the API
     */
    public function getAPICategory(string $name, $cache = 600)
    {
        $params = array(
            'method'	=> 'GET',
            'name'		=> $name
        );
        if (!is_null($cache))
            $params['cache'] = intval($cache);

        $response = $this->_api->helpCategory($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * HELP : Get description of a specific method available and documented in our API
     * - url : api.mailjet.com/0.1/helpMethod
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string  $name  [categoryMethod] name (Required)
     * @param integer $cache Cache period for the object - default to 600s = 10m (Optional)
     *
     * @return mixed Response from the API
     */
    public function getAPIMethod(string $name, $cache = 600)
    {
        $params = array(
            'method'	=> 'GET',
            'name'		=> $name
        );
        if (!is_null($cache))
            $params['cache'] = intval($cache);

        $response = $this->_api->helpMethod($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * HELP : Get all methods of a specific category available and documented in our API
     * - url : api.mailjet.com/0.1/helpMethods
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param string  $category Category name (Required)
     * @param integer $cache    Cache period for the object - default to 600s = 10m (Optional)
     *
     * @return mixed Response from the API
     */
    public function getAPIMethods(string $category, $cache = 600)
    {
        $params = array(
            'method'	=> 'GET',
            'category'	=> $category
        );
        if (!is_null($cache))
            $params['cache'] = intval($cache);

        $response = $this->_api->helpMethods($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

    /**
     * HELP : Get response status and status code you'll encounter when calling our API
     * - url : api.mailjet.com/0.1/helpStatus
     *
     * @access	public
     * @throw	Mailjet::Mailjet_ApiException
     * @param integer $code  Code response (Optional)
     * @param integer $cache Cache period for the object - default to 600s = 10m (Optional)
     *
     * @return mixed Response from the API
     */
    public function getAPIStatus(integer $code = null, $cache = 600)
    {
        $params = array(
            'method'	=> 'GET'
        );
        if (!is_null($code))
            $params['code'] = $code;
        if (!is_null($cache))
            $params['cache'] = intval($cache);

        $response = $this->_api->helpStatus($params);
        if ($response !== FALSE)
            return ($response);
        else
            throw new Mailjet_ApiException($this->_api->getHTTPCode(), $this->_errors[$this->_api->getHTTPCode()]);
    }

}
