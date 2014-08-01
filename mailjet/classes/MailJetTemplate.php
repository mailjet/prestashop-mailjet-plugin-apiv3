<?php

include_once(dirname(__FILE__).'/../mailjet.php'); // **

include_once(dirname(__FILE__).'/Mailjet.Api.class.php');
include_once(dirname(__FILE__).'/Mailjet.Overlay.class.php');

class MailjetTemplate
{
	static private $api = null;
	static private $dataApi = null;

	// List of available template for webservice call
	private $templates = array(
		// SETUP_LANDING
		'setup_landing_message' => array(
			'params' => array('name' => 'setup_landing_message'),
			'html' => ''
		),
		'setup_landing_bt_more' => array(
			'params' => array('name' => 'setup_landing_bt_more'),
			'html' => ''
		),
		'setup_landing_bt_activate' => array(
			'params' => array('name' => 'setup_landing_bt_activate'),
			'html' => ''
		),

		// SETUP_STEP_0
		'setup_hosting_error_bt_support' => array(
			'params' => array('name' => 'setup_hosting_error_bt_support'),
			'html' => ''
		),
		'setup_hosting_error_message' => array(
			'params' => array('name' => 'setup_hosting_error_message'),
			'html' => ''
		),
	);

	public function __construct()
	{
		$this->initIframeLink();
	}

	private $iframes_url = array();

	/**
	 * Get the api connection
	 * @static
	 * @return null
	 */
	static public function getApi($with_overlay = true)
	{

		$obj = new Mailjet();
		if ($with_overlay)
		{
			MailjetTemplate::$api = Mailjet\ApiOverlay::getInstance();
			MailjetTemplate::$api->setKeys($obj->getAccountSettingsKey('API_KEY'), $obj->getAccountSettingsKey('SECRET_KEY'));
			MailjetTemplate::$api->secure(FALSE);

		} else {
			MailjetTemplate::$api = new Mailjet\Api($obj->getAccountSettingsKey('API_KEY'), $obj->getAccountSettingsKey('SECRET_KEY'));
		}
		unset($obj);
	
		return MailjetTemplate::$api;
	}
	
	static public function getDataApi()
	{
		if (self::$dataApi === null) {
			self::$dataApi = mailjetdata::getInstance();
			self::$dataApi->setKeys($obj->getAccountSettingsKey('API_KEY'), $obj->getAccountSettingsKey('SECRET_KEY'));
		}
		
		return self::$dataApi;
	}

	/**
	 * Load the iframe links from xml
	 * @return bool
	 */
	public function initIframeLink()
	{
		$context = Context::getContext();
		$lang = $context->language->iso_code; // language_code
		$lang = 'preprod'; // <== pout les Tests : TODO
		
		$file = dirname(__FILE__).'/../xml/iframes.xml';
		if (file_exists($file) && ($xml = simplexml_load_file($file)))
		{
			foreach($xml->iframe as $iframe)
				$this->iframes_url[(string)$iframe['name']] = (string)(str_replace("{lang}",$lang,$iframe));
			return true;
		}

		return false;
	}

	/**
	 * Fetch a specific template
	 *
	 * @param $name
	 * @return bool
	 */
	public function fetchTemplate($name)
	{
		if (isset($this->templates[$name]))
		{
			$context = Context::getContext();
			$lang = $context->language->iso_code; 
			
			$file = dirname(__FILE__).'/../translations/templates/'.$lang.'/'.$name. '.txt';
			
			if (file_exists($file)) {
				$template = file_get_contents($file);
				$this->templates[$name]['html'] = $template;
				return true;
			}
		}
		
		return false;
		
		echo $file; die;
		if (isset($this->templates[$name]))
		{
			return false;
			$this->templates[$name]['html'] = $this->l($name);
			return true;
			$api = MailjetTemplate::getApi(false);
			$res = $api->resellerTemplate($this->templates[$name]['params']);
			
			if (isset($res->tpl->{$name}))
			{
				$this->templates[$name]['html'] = $res->tpl->{$name};
				return true;
			}
		}
		return false;
	}

	/**
	 * Get the iframe url for signup
	 *
	 * @param int $name
	 */
	public function getSignupURL($name = 'SETUP_STEP_1')
	{
		$context = Context::getContext();
		$lang = $context->language->iso_code; // language_code
		$lang = 'app.preprod'; // <== pout les Tests : TODO

		$url = "https://".$lang.".mailjet.com/reseller/signup?r=prestashop&cb=http://".Configuration::get('PS_SHOP_DOMAIN')."/modules/mailjet/callback_signup.php&show_menu=none";

		$this->iframes_url[$name] = $url;
	}
	
	public function getCampaignURL($name = 'CAMPAIGN', $token)
	{
		$context = Context::getContext();
		$lang = $context->language->iso_code; // language_code
		$lang = 'app.preprod'; // <== pout les Tests : TODO

		$url = "https://".$lang.".mailjet.com/campaigns?t=".$token."&r=prestashop&cb=http://".Configuration::get('PS_SHOP_DOMAIN')."/modules/mailjet/callback_campaign.php&show_menu=none";
		//$url = "https://jdf.www.mailjet.com/campaigns?t=".$token."&r=prestashop&cb=http://mailjet.dream-me-up.fr/modules/mailjet/callback_signup.php";
		$this->iframes_url[$name] = $url;
	}
	
	public function getPricingURL($name = 'PRICING', $token = null)
	{
		$context = Context::getContext();
		$lang = $context->language->iso_code; // language_code
		$lang = 'app.preprod'; // <== pout les Tests : TODO
	
		if ($token) {
			$url = "https://".$lang.".mailjet.com/reseller/pricing?t=".$token."&r=prestashop&cb=http://".Configuration::get('PS_SHOP_DOMAIN')."/modules/mailjet/callback_campaign.php&show_menu=none";
		} else {
			$url = "https://".$lang.".mailjet.com/reseller/pricing?r=prestashop&show_menu=none";
		}
		
		//$url = "https://jdf.www.mailjet.com/campaigns?t=".$token."&r=prestashop&cb=http://mailjet.dream-me-up.fr/modules/mailjet/callback_signup.php";
		$this->iframes_url[$name] = $url;
	}
	
	
	public function getStatsURL($name = 'STATS', $token)
	{
		$context = Context::getContext();
		$lang = $context->language->iso_code; // language_code
		$lang = 'app.preprod'; // <== pout les Tests : TODO
	
		$url = "https://".$lang.".mailjet.com/stats?t=".$token."&r=prestashop&cb=http://".Configuration::get('PS_SHOP_DOMAIN')."/modules/mailjet/callback_campaign.php&show_menu=none";
		//$url = "https://jdf.www.mailjet.com/campaigns?t=".$token."&r=prestashop&cb=http://mailjet.dream-me-up.fr/modules/mailjet/callback_signup.php";
		$this->iframes_url[$name] = $url;
	}
	
	public function getContactsURL($name = 'CONTACTS', $token)
	{
		$context = Context::getContext();
		$lang = $context->language->iso_code; // language_code
		$lang = 'app.preprod'; // <== pout les Tests : TODO
	
		$url = "https://".$lang.".mailjet.com/contacts?t=".$token."&r=prestashop&cb=http://".Configuration::get('PS_SHOP_DOMAIN')."/modules/mailjet/callback_campaign.php&show_menu=none";
		//$url = "https://jdf.www.mailjet.com/campaigns?t=".$token."&r=prestashop&cb=http://mailjet.dream-me-up.fr/modules/mailjet/callback_signup.php";
		$this->iframes_url[$name] = $url;
	}

	/**
	 * Set html content to a specific page for specific work
	 *
	 * @param $name
	 * @param $content
	 */
	public function setContent($name, $content)
	{
		$this->templates[$name]['html'] = $content;
	}

	/**
	 * Get the iframes url list
	 *
	 * @return array
	 */
	public function getIframesURL()
	{
		return $this->iframes_url;
	}

	/**
	 * Get all fetched template
	 *
	 * @return string
	 */
	public function getTemplates()
	{
		$tpl = array();
		foreach($this->templates as $name => $template)
			$tpl[$name] = $template['html'];
		return $tpl;
	}
}