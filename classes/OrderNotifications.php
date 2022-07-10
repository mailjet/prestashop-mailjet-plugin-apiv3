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

include_once(_PS_MODULE_DIR_ . 'mailjet/classes/hooks/synchronization/SynchronizationAbstract.php');
include_once(_PS_MODULE_DIR_ . 'mailjet/classes/hooks/synchronization/Initial.php');
include_once(_PS_MODULE_DIR_ . 'mailjet/classes/hooks/synchronization/SingleUser.php');
include_once(_PS_MODULE_DIR_ . 'mailjet/classes/hooks/synchronization/Segment.php');
include_once(_PS_MODULE_DIR_ . 'mailjet/libraries/Mailjet.Overlay.class.php');
include_once(_PS_MODULE_DIR_ . 'mailjet/libraries/Mailjet.Api.class.php');
include_once(_PS_MODULE_DIR_ . 'mailjet/classes/MailJetTemplate.php');

class OrderNotifications
{
    /**
     * @var int $page
     */
    public $page;

    /**
     * @var array $trad
     */
    public $trad;

    /**
     * @var string
     */
    private $_path;

    /**
     * @var string $displayName
     */
    private $displayName;

    /**
     * @var string $description
     */
    private $description;

    public function __construct()
    {
        $this->name = 'order_notifications';
        $this->tab = 'administration';
        $this->_path = _PS_MODULE_DIR_ . 'mailjet';

        $this->displayName = $this->l('Order Notification Module');
        $this->description = $this->l('Module for setup order notifications');
        $this->page = 25;
    }

    /**
     * @return int
     */
    public function setActivationSettings(): int
    {
        $useOrderNotification = 0;
        if (Tools::isSubmit('MJ_use_order_notifications')) {
            $useOrderNotification = (int)Tools::getValue('MJ_order_notifications');
            Configuration::updateValue('MJ_USE_ORDER_NOTIFICATIONS', $useOrderNotification);
        }

        return $useOrderNotification;
    }

    /**
     * @return string
     */
    public function initContent(): string
    {
        Configuration::updateValue('SEGMENT_CUSTOMER_TOKEN', Tools::getValue('token'));

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            Context::getContext()->controller->addJqueryUI('ui.datepicker');
        }

        $this->clearCacheLang();
        $this->initLang();

        Context::getContext()->smarty->assign(array(
            'mj__PS_BASE_URI__' => __PS_BASE_URI__,
            'mj_PS_JS_DIR_' => _PS_JS_DIR_,
            'mj_MODULE_DIR_' => _MODULE_DIR_,
            'mj_datePickerJsFormat' => 'yy-mm-dd',
            'mj_datepickerPersonnalized' => version_compare(_PS_VERSION_, '1.5', '<') ? '<script type="text/javascript" src="' .
            _PS_JS_DIR_ . 'jquery/datepicker/jquery-ui-personalized-1.6rc4.packed.js"></script>' : '',
            'mj_token' => Tools::getValue('token'),
            'mj_ajaxFile' => _MODULE_DIR_ . 'mailjet/ajax/ajax.php',
            'mj_ajaxSyncFile' => _MODULE_DIR_ . 'mailjet/ajax/sync.php',
            'mj_ajaxBundle' => _MODULE_DIR_ . 'mailjet/ajax/bundlejs_prestashop.php',
            'mj_id_employee' => (int) Context::getContext()->cookie->id_employee,
            'mj_trads' => array_map('stripReturn', $this->trad),
            'mj_groups' => Group::getGroups((int) Context::getContext()->cookie->id_lang),
            'mj_use_order_notification' => Configuration::get('MJ_USE_ORDER_NOTIFICATIONS'),
        ));

        return '';
    }

    /**
     * @param string $string
     * @param bool   $specific
     * @return mixed|string
     */
    public static function l(string $string, $specific = false)
    {
        $module = new Mailjet();
        if ($string == $module->l($string, $specific)) {
            $trad_file = _PS_MODULE_DIR_ . 'mailjet/translations/' . Context::getContext()->language->iso_code . '.php';
            if (file_exists($trad_file)) {
                $_MODULE = array();
                @include_once($trad_file);

                $key = '<{mailjet}prestashop>order_notificationss_' . md5(str_replace('\'', '\\\'', $string));
            }

            return (isset($_MODULE[$key]) ? $_MODULE[$key] : ($module->l($string, $specific)));
        } else {
            return $module->l($string, $specific);
        }
    }

    /**
     * @param $i
     * @return mixed
     */
    public function ll($i)
    {
        if (!isset($this->trad) || empty($this->trad[0])) {
            $this->initLang();
        }
        return $this->trad[$i];
    }

    /**
     * @param string $url
     * @return string
     */
    private function getDomain(string $url): string
    {
        $url = parse_url($url);

        if (!isset($url['host'])) {
            return '';
        }

        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $url['host'], $result)) {
            return $result['domain'];
        }

        return $url['host'];
    }

    /**
     * @param int $idEmployee
     * @return int
     */
    private function getIdLangByIdEmployee(int $idEmployee): int
    {
        $sql = 'SELECT id_lang FROM ' . _DB_PREFIX_ . 'employee WHERE id_employee = ' . (int)$idEmployee;

        return (int)DB::getInstance()->getValue($sql);
    }

    /**
     * @param int $id_lang
     * @return void
     */
    public function initLang(int $id_lang = 0)
    {
        if (!$id_lang) {
            $id_lang = $this->getCurrentIdLang();
        }

        if (file_exists($this->_path . '/translations/translation_cache_' . (int) $id_lang . '.txt')) {
            $this->trad = Tools::jsonDecode(
                Tools::file_get_contents($this->_path . '/translations/translation_cache_' . (int) $id_lang . '.txt')
            );
        } else {
            $this->cacheLang();
            $tmp_create = $this->_path . '/translations/translation_create_' . (int) $id_lang . '.txt';
            if (file_exists($tmp_create)) {
                $fp = fopen($tmp_create, 'r');
                $trad = array();
                while (($buffer = fgets($fp, 4096)) !== false) {
                    $trad[] = $buffer;
                }
                fclose($fp);
                $this->trad = $trad;
            } else {
                $fp = fopen($tmp_create, 'w+');
                foreach ($this->trad as $trad) {
                    fwrite($fp, $trad . "\r\n");
                }
                fclose($fp);
            }
            file_put_contents(
                $this->_path . '/translations/translation_cache_' . (int) $id_lang . '.txt',
                Tools::jsonEncode($this->trad)
            );
        }
    }

    /**
     * @return int
     */
    private function getCurrentIdLang(): int
    {
        if (($id_employee = (int)Tools::getValue('id_employee')) > 0) {
            $id_lang = $this->getIdLangByIdEmployee($id_employee);
        } elseif (($id_employee = (int) Context::getContext()->cookie->id_employee) > 0) {
            $id_lang = $this->getIdLangByIdEmployee($id_employee);
        } else {
            $id_lang = (int)Context::getContext()->cookie->id_lang;
        }

        return (int)$id_lang;
    }

    /**
     * @return void
     */
    private function clearCacheLang()
    {
        $langs = Language::getLanguages();
        foreach ($langs as $lang) {
            if (file_exists($this->_path . '/translations/translation_cache_' . $lang['id_lang'] . '.txt')) {
                unlink($this->_path . '/translations/translation_cache_' . $lang['id_lang'] . '.txt');
            }
        }
    }

    /**
     * @return void
     */
    private function cacheLang()
    {
        $this->trad = array(
            0 => $this->l('Customers'),
            1 => $this->l('Orders'),
            2 => $this->l('Number of valid orders'),
            3 => $this->l('Number of invalid orders'),
            4 => $this->l('Number of orders (all)'),
            5 => $this->l('Sales'),
            6 => $this->l('Product\'s name'),
            7 => $this->l('Category\'s name'),
            8 => $this->l('Brand\'s name'),
            9 => $this->l('Lost cart number'),
            10 => $this->l('Sales'),
            11 => $this->l('Average sales'),
            12 => $this->l('Gender'),
            13 => $this->l('Subscribe Date'),
            14 => $this->l('Country'),
            15 => $this->l('-- Select --'),
            16 => $this->l('Week'),
            17 => $this->l('Month'),
            18 => $this->l('Trimester'),
            19 => $this->l('Year'),
            20 => $this->l('Man'),
            21 => $this->l('Woman'),
            22 => $this->l('No Result'),
            23 => $this->l('Save successfully'),
            24 => $this->l('Load successfully'),
            25 => $this->l('Customer(s)'),
            26 => $this->l('Export'),
            27 => $this->l('Page'),
            28 => $this->l('Filter removed successfully'),
            29 => $this->l('Group successfully fill'),
            30 => $this->l('unknown'),
            31 => $this->l('Stat Table'),
            32 => $this->l('Range'),
            33 => $this->l('Number of customer'),
            34 => $this->l('Purcent of customer'),
            35 => $this->l('Value2'),
            36 => $this->l('Rules'),
            37 => $this->l('Cond'),
            38 => $this->l('Base'),
            39 => $this->l('Source'),
            40 => $this->l('Indic'),
            41 => $this->l('Data'),
            42 => $this->l('Value1'),
            43 => $this->l('Unknown'),
            44 => $this->l('All'),
            45 => $this->l('Quantity'),
            46 => $this->l('No brand found'),
            47 => $this->l('Customer ID'),
            48 => $this->l('Firstname'),
            49 => $this->l('Lastname'),
            50 => $this->l('Period from %s to %s'),
            51 => $this->l('Day of %s'),
            52 => $this->l('Undefined period'),
            53 => $this->l('Amount taxes included'),
            54 => $this->l('Amount taxes excluded'),
            55 => $this->l('Sales taxes included'),
            56 => $this->l('Sales taxes excluded'),
            57 => $this->l('Average sales taxes included'),
            58 => $this->l('Average sales taxes excluded'),
            59 => $this->l('Payment method used'),
            60 => $this->l('Lost cart contains'),
            61 => $this->l('Gift package'),
            62 => $this->l('Recycled packaging'),
            63 => $this->l('Last visit'),
            64 => $this->l('Date of birth'),
            65 => $this->l('Newsletter subscription'),
            66 => $this->l('Newsletter optin'),
            67 => $this->l('Yes'),
            68 => $this->l('No'),
            69 => $this->l('Origin'),
            70 => $this->l('Voucher'),
            71 => $this->l('Assets'),
            72 => $this->l('Return product'),
            73 => $this->l('Phone number'),
            74 => $this->l('Phone mobile'),
            75 => $this->l('Email'),
            76 => $this->l('Address contains'),
            77 => $this->l('Zipcode starts by'),
            78 => $this->l('City'),
            79 => $this->l('A'),
            80 => $this->l('Action'),
            81 => $this->l('Rule'),
            82 => $this->l('You must specify at least one date of subscription'),
            83 => $this->l('You must specify at least one date of last visit'),
            84 => $this->l('You must specify at least one date of birth'),
            85 => $this->l('You must specify a base'),
            86 => $this->l('You must specify a source'),
            87 => $this->l('You must specify an indicator'),
            88 => $this->l('Order date'),
            89 => $this->l('You must specify at least one order date'),
            90 => $this->l('Lost carts'),
            91 => $this->l('Date of abandoned cart'),
            92 => $this->l('No order since'),
            93 => $this->l('You must specify a date'),
            94 => $this->l('Frequency orders'),
            95 => $this->l('You must specify a number of orders'),
            96 => $this->l('in real time'),
            97 => $this->l('replace'),
            98 => $this->l('add'),
            99 => $this->l('Date of visit'),
            100 => $this->l('You must specify at least one date of visit'),
            101 => $this->l('Unknown rule A'),
            102 => $this->l('Unknown rule Action'),
            103 => $this->l('You must specify at least one date of abandoned cart'),
            104 => $this->l('Order state'),
            105 => $this->l('Number of orders'),
            106 => $this->l('Products'),
            107 => $this->l('Shop'),
            108 => $this->l('Date format must be yyyy-mm-dd'),
        );
    }

}
