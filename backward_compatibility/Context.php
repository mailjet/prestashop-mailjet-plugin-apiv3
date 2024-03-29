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

// Retro 1.3, 'class_exists' cause problem with autoload...
if (version_compare(_PS_VERSION_, '1.4', '<')) {
    // Not exist for 1.3
    class Shop extends ObjectModel
    {
        public function __construct()
        {
        }

        public static function getShops()
        {
            return array(
                array('id_shop' => 1, 'name' => 'Default shop')
            );
        }

        public static function getCurrentShop()
        {
            return 1;
        }
    }

}

// Not exist for 1.3 and 1.4
class Context
{
    /**
     * @var Context
     */
    protected static $instance;

    /**
     * @var Cart
     */
    public $cart;

    /**
     * @var Customer
     */
    public $customer;

    /**
     * @var Cookie
     */
    public $cookie;

    /**
     * @var Link
     */
    public $link;

    /**
     * @var Country
     */
    public $country;

    /**
     * @var Employee
     */
    public $employee;

    /**
     * @var Controller
     */
    public $controller;

    /**
     * @var Language
     */
    public $language;

    /**
     * @var Currency
     */
    public $currency;

    /**
     * @var AdminTab
     */
    public $tab;

    /**
     * @var Shop
     */
    public $shop;

    /**
     * @var Smarty
     */
    public $smarty;

    /**
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    public function __construct()
    {
        global $cookie, $cart, $smarty, $link;

        $this->tab = null;

        $this->cookie = $cookie;
        $this->cart = $cart;
        $this->smarty = $smarty;
        $this->link = $link;

        $this->controller = new ControllerBackwardModule();
        $this->currency = new Currency(isset($cookie->id_currency) ? (int)$cookie->id_currency : null);
        $this->language = new Language(isset($cookie->id_lang) ? (int)$cookie->id_lang : null);
        $this->country = new Country(isset($cookie->id_country) ? (int)$cookie->id_country : null);
        $this->shop = new ShopBackwardModule();
        $this->customer = new Customer(isset($cookie->id_customer) ? (int)$cookie->id_customer : null);
        $this->employee = new Employee(isset($cookie->id_employee) ? (int)$cookie->id_employee : null);
    }

    /**
     * Get a singleton context
     *
     * @return Context
     */
    public static function getContext()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Context();
        }
        return self::$instance;
    }

    /**
     * Clone current context
     *
     * @return Context
     */
    public function cloneContext()
    {
        return clone($this);
    }

    /**
     * @return int Shop context type (Shop::CONTEXT_ALL, etc.)
     */
    public static function shop()
    {
        if (!self::$instance->shop->getContextType()) {
            return ShopBackwardModule::CONTEXT_ALL;
        }
        return self::$instance->shop->getContextType();
    }
}

/**
 * Class Shop for Backward compatibility
 */
class ShopBackwardModule extends Shop
{
    const CONTEXT_ALL = 1;

    public $id = 1;

    public function getContextType()
    {
        return ShopBackwardModule::CONTEXT_ALL;
    }
}

/**
 * Class Controller for a Backward compatibility
 * Allow to use method declared in 1.5
 */
class ControllerBackwardModule
{
    /**
     * @param  $js_uri
     * @return void
     */
    public function addJS($js_uri)
    {
        Tools::addJS($js_uri);
    }

    /**
     * @param  $css_uri
     * @param  string $css_media_type
     * @return void
     */
    public function addCSS($css_uri, $css_media_type = 'all')
    {
        Tools::addCSS($css_uri, $css_media_type);
    }
}
