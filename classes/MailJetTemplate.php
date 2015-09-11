<?php
/**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
*/

include_once(dirname(__FILE__).'/../libraries/Mailjet.Api.class.php');
include_once(dirname(__FILE__).'/../libraries/Mailjet.Overlay.class.php');

class MailjetTemplate
{
	static private $api = null;
	static private $data_api = null;

    public static $_langCodesMap = array('en' => 'en_US', 'fr' => 'fr_FR', 'de' => 'de_DE', 'es' => 'es_ES');
    private $_defaultLang = 'en';
    private $_lang;
    private $_locale;

	public function getLang(){
		return $this->_lang;
	}

	/* List of available template for webservice call */
	private $templates = array(
		/* SETUP_LANDING */
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

		/* SETUP_STEP_0 */
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
        $context = Context::getContext();
        $this->_lang = empty($context->language->iso_code) ? $this->_defaultLang : $context->language->iso_code; // language_code
        if(!array_key_exists($this->_lang, self::$_langCodesMap)){
            $this->_lang = $this->_defaultLang;
        }
        $this->_locale = self::$_langCodesMap[$this->_lang];
		$this->initIframeLink();
	}

	private $iframes_url = array();

	/**
	 * Get the api connection
	 * @static
	 * @return null
	 */
	public static function getApi($with_overlay = true)
	{
		$obj = new Mailjet();
		if ($with_overlay)
		{
			MailjetTemplate::$api = Mailjet_ApiOverlay::getInstance();
			MailjetTemplate::$api->setKeys($obj->getAccountSettingsKey('API_KEY'), $obj->getAccountSettingsKey('SECRET_KEY'));
			MailjetTemplate::$api->secure(false);
		}
		else
			MailjetTemplate::$api = new Mailjet_Api($obj->getAccountSettingsKey('API_KEY'), $obj->getAccountSettingsKey('SECRET_KEY'));

		unset($obj);

		return MailjetTemplate::$api;
	}

	public static function getDataApi()
	{
		$obj = new Mailjet();

		if (self::$data_api === null)
		{
			self::$data_api = mailjetdata::getInstance();
			self::$data_api->setKeys($obj->getAccountSettingsKey('API_KEY'), $obj->getAccountSettingsKey('SECRET_KEY'));
		}

		return self::$data_api;
	}

	/**
	 * Load the iframe links from xml
	 * @return bool
	 */
	public function initIframeLink()
	{
		$file = dirname(__FILE__).'/../xml/iframes.xml';
		if (file_exists($file) && ($xml = simplexml_load_file($file))){
			foreach ($xml->iframe as $iframe)
				$this->iframes_url[(string)$iframe['name']] = (string)str_replace('{lang}', $this->_lang, $iframe);
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
		if (isset($this->templates[$name])){
			$file = dirname(__FILE__).'/../translations/templates/'.$this->_lang.'/'.$name.'.txt';
			if (file_exists($file)){
				$template = Tools::file_get_contents($file);
				$this->templates[$name]['html'] = $template;
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
		$ps_shop_domain = Context::getContext()->shop->getBaseUrl(true, true);
		$token = Tools::getAdminTokenLite('AdminModules');
		$sign_up_call_back = urlencode($ps_shop_domain.'/modules/mailjet/callback_signup.php?internaltoken='.$token);
		$url = 'https://app.mailjet.com/reseller/signup?r=prestashop&cb={'.$sign_up_call_back.'}&show_menu=none&sp=display&locale='.$this->_locale;

		$this->iframes_url[$name] = $url;
	}

	public function getCampaignURL($name = 'CAMPAIGN', $token)
	{
		$ps_shop_domain = Context::getContext()->shop->getBaseUrl(true, true);
		$cb = $ps_shop_domain.'/modules/mailjet/callback_campaign.php';
		$url = 'https://app.mailjet.com/campaigns?t='.$token.'&r=Prestashop-3.0&cb='.$cb.'&show_menu=none&sp=display&f=am&locale='.$this->_locale;
		$this->iframes_url[$name] = $url;
	}

	public function getPricingURL($name = 'PRICING', $token = null)
	{
		$ps_shop_domain = Context::getContext()->shop->getBaseUrl(true, true);
		$cb = $ps_shop_domain.'/modules/mailjet/callback_campaign.php';
		if ($token)
			$url = 'https://app.mailjet.com/reseller/pricing?t='.$token.'&r=prestashop&cb='.$cb.'&show_menu=none&sp=display&locale='.$this->_locale;
		else
			$url = 'https://app.mailjet.com/reseller/pricing?r=prestashop&show_menu=none&sp=display&locale='.$this->_locale;
		$this->iframes_url[$name] = $url;
	}

	public function getStatsURL($name = 'STATS', $token)
	{
		$ps_shop_domain = Context::getContext()->shop->getBaseUrl(true, true);
		$cb = $ps_shop_domain.'/modules/mailjet/callback_campaign.php';
		$url = 'https://app.mailjet.com/stats?t='.$token.'&r=Prestashop-3.0&cb='.$cb.'&show_menu=none&f=am&locale='.$this->_locale;
		$this->iframes_url[$name] = $url;
	}

	public function getContactsURL($name = 'CONTACTS', $token)
	{
		$ps_shop_domain = Context::getContext()->shop->getBaseUrl(true, true);
		$cb = $ps_shop_domain.'/modules/mailjet/callback_campaign.php';
		$url = 'https://app.mailjet.com/contacts?t='.$token.'&r=Prestashop-3.0&cb='.$cb.'&show_menu=none&sp=display&f=am&locale='.$this->_locale;
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
		foreach ($this->templates as $name => $template)
			$tpl[$name] = $template['html'];
		return $tpl;
	}
}
