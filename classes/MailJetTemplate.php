<?php

/**
 * 2007-2017 PrestaShop
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
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

require_once __DIR__ . '/../libraries/Mailjet.Api.class.php';
require_once __DIR__ . '/../libraries/Mailjet.Overlay.class.php';

class MailjetTemplate
{
    private static $api = null;
    private static $data_api = null;
    public static $langCodesMap = array('en' => 'en_US', 'fr' => 'fr_FR', 'de' => 'de_DE', 'es' => 'es_ES');
    private $defaultLang = 'en';
    private $lang;
    private $locale;
    private $mjWebsite = "https://app.mailjet.com";

    public function getLang()
    {
        return $this->lang;
    }

    /* List of available template for webservice call */

    private $templates = [
        /* SETUP_LANDING */
        'setup_landing_message' => [
            'params' => ['name' => 'setup_landing_message'],
            'html' => ''
        ],
        'setup_landing_bt_more' => [
            'params' => ['name' => 'setup_landing_bt_more'],
            'html' => ''
        ],
        'setup_landing_bt_activate' => [
            'params' => ['name' => 'setup_landing_bt_activate'],
            'html' => ''
        ],
        /* SETUP_STEP_0 */
        'setup_hosting_error_bt_support' => [
            'params' => ['name' => 'setup_hosting_error_bt_support'],
            'html' => ''
        ],
        'setup_hosting_error_message' => [
            'params' => ['name' => 'setup_hosting_error_message'],
            'html' => ''
        ],
    ];

    public function __construct()
    {
        $context = Context::getContext();
        $this->lang = empty($context->language->iso_code) ? $this->defaultLang : $context->language->iso_code;
        if (!array_key_exists($this->lang, self::$langCodesMap)) {
            $this->lang = $this->defaultLang;
        }
        $this->locale = self::$langCodesMap[$this->lang];
        $this->initIframeLink();
    }

    private $iframes_url = array();

    /**
     * Get the api connection
     *
     * @static
     * @return Mailjet_ApiOverlay|Mailjet_Api|null
     */
    public static function getApi($with_overlay = true)
    {
        $obj = new Mailjet();
        if ($with_overlay) {
            self::$api = Mailjet_ApiOverlay::getInstance();
            self::$api->setKeys(
                $obj->getAccountSettingsKey('API_KEY'),
                $obj->getAccountSettingsKey('SECRET_KEY')
            );
            self::$api->secure(false);
        } else {
            self::$api =
                new Mailjet_Api($obj->getAccountSettingsKey('API_KEY'), $obj->getAccountSettingsKey('SECRET_KEY'));
        }

        unset($obj);

        return self::$api;
    }

    public static function getDataApi()
    {
        $obj = new Mailjet();

        if (self::$data_api === null) {
            self::$data_api = mailjetdata::getInstance();
            self::$data_api->setKeys($obj->getAccountSettingsKey('API_KEY'), $obj->getAccountSettingsKey('SECRET_KEY'));
        }

        return self::$data_api;
    }

    /**
     * Load the iframe links from xml
     *
     * @return bool
     */
    public function initIframeLink()
    {
        $file = __DIR__ . '/../xml/iframes.xml';
        if (file_exists($file) && ($xml = simplexml_load_string(file_get_contents($file)))) {
            foreach ($xml->iframe as $iframe) {
                $this->iframes_url[(string) $iframe['name']] = (string) str_replace('{lang}', $this->lang, $iframe);
            }
            return true;
        }
        return false;
    }

    /**
     * Fetch a specific template
     *
     * @param  $name
     * @return bool
     */
    public function fetchTemplate($name)
    {
        if (isset($this->templates[$name])) {
            $file = __DIR__ . '/../translations/templates/' . $this->lang . '/' . $name . '.txt';
            if (file_exists($file)) {
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
        $sign_up_call_back = urlencode($ps_shop_domain . '/modules/mailjet/callback_signup.php?internaltoken=' . $token);
        $url = $this->mjWebsite .
            '/reseller/signup?r=Prestashop-3.0&cb={' . $sign_up_call_back . '}&show_menu=none&sp=display&locale=' .
            $this->locale;

        $this->iframes_url[$name] = $url;
    }

    public function getCampaignURL($name = 'CAMPAIGN', $token = null)
    {
        // Let cb parameter in official version
        $url = $this->mjWebsite . '/campaigns?t=' . $token . '&r=Prestashop-3.0&show_menu=none&sp=display&f=am&locale=' . $this->locale;

        // Remove cb parameter while fix issue
        // $url = $this->mjWebsite . '/campaigns?t=' . $token . '&r=Prestashop-3.0&show_menu=none&sp=display&locale=' . $this->locale;
        $this->iframes_url[$name] = $url;
    }

    public function getPricingURL($name = 'PRICING', $token = null)
    {
        if ($token) {
            $url = $this->mjWebsite .
                '/reseller/pricing?t=' . $token . '&r=Prestashop-3.0&show_menu=none&sp=display&locale=' .
                $this->locale;
        } else {
            $url = $this->mjWebsite .
                '/reseller/pricing?r=Prestashop-3.0&show_menu=none&sp=display&locale=' .
                $this->locale;
        }
        $this->iframes_url[$name] = $url;
    }

    public function getStatsURL($name = 'STATS', $token = null)
    {
        $url = $this->mjWebsite .
            '/stats?t=' . $token . '&r=Prestashop-3.0&show_menu=none&f=am&locale=' .
            $this->locale;
        $this->iframes_url[$name] = $url;
    }

    public function getContactsURL($name = 'CONTACTS', $token = null)
    {
        $url = $this->mjWebsite .
            '/contacts?t=' . $token . '&r=Prestashop-3.0&show_menu=none&sp=display&f=am&locale=' .
            $this->locale;
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
     * @return array
     */
    public function getTemplates()
    {
        $tpl = array();
        foreach ($this->templates as $name => $template) {
            $tpl[$name] = $template['html'];
        }
        return $tpl;
    }
}
