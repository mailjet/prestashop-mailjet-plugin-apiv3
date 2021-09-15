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

/* Security */
if (!defined('_PS_VERSION_')) {
    exit;
}

/* include_once(_PS_MODULE_DIR_.'mailjet/classes/MailjetAPI.php'); */
require_once(_PS_MODULE_DIR_ . 'mailjet/classes/MailJetTranslate.php');
require_once(_PS_MODULE_DIR_ . 'mailjet/classes/MailJetTemplate.php');
require_once(_PS_MODULE_DIR_ . 'mailjet/classes/MailJetPages.php');
require_once(_PS_MODULE_DIR_ . 'mailjet/classes/MailJetEvents.php');
require_once(_PS_MODULE_DIR_ . 'mailjet/classes/MailJetLog.php');

require_once(_PS_MODULE_DIR_ . 'mailjet/classes/Segmentation.php');
if (version_compare(_PS_VERSION_, '1.7', '<')) {
    if (version_compare(_PS_VERSION_, '1.6.1.5', '>=')) {
        require_once(_PS_CORE_DIR_ . '/tools/swift/swift_required.php');
    } else {
        require_once(_PS_SWIFT_DIR_ . 'Swift.php');
        require_once(_PS_SWIFT_DIR_ . 'Swift/Connection/SMTP.php');
    }
}
/* include_once(_PS_SWIFT_DIR_.'Swift/Connection/NativeMail.php'); */
/* include_once(_PS_SWIFT_DIR_.'Swift/Plugin/Decorator.php'); */

require_once(_PS_MODULE_DIR_ . 'mailjet/classes/hooks/synchronization/SynchronizationAbstract.php');
require_once(_PS_MODULE_DIR_ . 'mailjet/classes/hooks/synchronization/Initial.php');
require_once(_PS_MODULE_DIR_ . 'mailjet/classes/hooks/synchronization/SingleUser.php');
require_once(_PS_MODULE_DIR_ . 'mailjet/classes/hooks/synchronization/Segment.php');
include_once(_PS_MODULE_DIR_ . 'mailjet/classes/hooks/Events.php');


class Mailjet extends Module
{
    private const DEFAULT_MAIL_OPTION = 1;
    public $errors_list = array();
    public $page_name;
    public $module_access = array();
    public $mj_template = null;
    public $mj_pages = null;
    public $segmentation = null;
    public $mj_mail_server = 'in-v3.mailjet.com';
    public $mj_mail_port = 587;

    public static function setMjMailServer()
    {
        $mj = new Mailjet();
        return $mj->mj_mail_server;
    }

    public static function setMjMailPort()
    {
        $mj = new Mailjet();
        return $mj->mj_mail_port;
    }

    /* Default account settings */

    public $account = array(
        'TOKEN' => '', /* Used for ajax security */
        /* 'TOKEN_{id_customer}' The one used to display iframe */
        'API_KEY' => '',
        'SECRET_KEY' => '',
        /* 'IP' => '', IP_{id_employee} */
        /*  LAST_TIMESTAMP => LAST_TIMESTAMP_{id_employee} */
        'EMAIL' => '',
        'PASSWD' => '',
        'ACTIVATION' => 0,
        'AUTHENTICATION' => 0,
        'MASTER_LIST_SYNCHRONIZED' => 0,
        'MASTER_LIST_ID' => 0,
    );

    /* Triggers parameters */
    public $triggers = array(
        'active' => 0,
        'trigger' => array(
            1 => array('active' => 0),
            2 => array('active' => 0),
            3 => array('active' => 0),
            4 => array('active' => 0),
            5 => array('active' => 0),
            6 => array('active' => 0),
            7 => array('active' => 0),
            8 => array('active' => 0),
            9 => array('active' => 0)
        )
    );

    /*
     * * Construct Method
     */

    public function __construct()
    {
        $this->account = json_decode(json_encode($this->account));
        //$this->getAdminFullUrl();
        // Default module variable
        $this->name = 'mailjet';
        $this->displayName = 'Mailjet';
        $this->description = $this->l('Create contact lists and client segment groups, drag-n-drop newsletters, define client re-engagement triggers, follow and analyze all email user interaction, minimize negative user engagement events(blocked, unsubs and spam) and optimise deliverability and revenue generation. Get started today with 6000 free emails per month.');
        $this->author = 'PrestaShop';
        $this->version = '3.4.20';
        $this->module_key = 'c81a68225f14a65239de29ee6b78d87b';
        $this->tab = 'advertising_marketing';

        // Parent constructor
        parent::__construct();
        // Backward compatibility
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            require(_PS_MODULE_DIR_ . $this->name . '/backward_compatibility/backward.php');
        }

        if ($this->active) {
            $this->module_access['uri'] = __PS_BASE_URI__ . 'modules/' . $this->name . '/';
            $this->module_access['dir'] = _PS_MODULE_DIR_ . $this->name . '/';

            $this->initAccountSettings();
            MailJetLog::init();

            $this->initTriggers();
        }
        $this->mj_pages = $this->getMjPages($this->account->AUTHENTICATION);
        $this->initContext();
    }

    private function initContext()
    {
        if (version_compare(_PS_VERSION_, '1.4', '>=') && class_exists('Context')) {
            $this->context = Context::getContext();
        } else {
            $this->context = new StdClass();
            $this->context->smarty = $GLOBALS['smarty'];
            $this->context->cookie = $GLOBALS['cookie'];
            // ###########################**
            $this->context->language = new Language($this->context->cookie->id_lang);
            $this->context->currency = new Currency($this->context->cookie->id_lang);
            $this->context->link = new Link();
            $this->context->shop = new Shop();
        }

        //var_dump($this->account); die;

        if (!isset($this->context->shop->domain)) {
            $this->context->shop->domain = Configuration::get('PS_SHOP_DOMAIN');
        }
    }

    /*
     * * Install / Uninstall Methods
     */

    public function install()
    {
        //$this->account = array(); // **
        $this->account = ($account = Tools::jsonDecode(Configuration::get('MAILJET'))) ? $account : $this->account;
        $this->account->TOKEN = Tools::getValue('token');
        $this->updateAccountSettings();

        // Install SQL
        $sql = array();
        include(_PS_MODULE_DIR_ . 'mailjet/sql/install.php');
        foreach ($sql as $s) {
            if (!Db::getInstance()->execute($s)) {
                return false;
            }
        }

        // Install Tab
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $tabMain = new Tab();
            $tabMain->name = array();
            foreach (Language::getLanguages() as $lang) {
                $tabMain->name[$lang['id_lang']] = $this->l('Mailjet');
            }
            if ($fr = Language::getIdByIso('fr')) {
                $tabMain->name[$fr] = $this->l('Mailjet');
            }
            $tabMain->class_name = 'AdminMailjetMain';
            $tabMain->id_parent = 0;
            $tabMain->module = $this->name;
            $tabMain->active = 1;
            $tabMain->add();

            $tab = new Tab();
            $tab->class_name = 'AdminMailjet';
            $tab->name = array();
            foreach (Language::getLanguages() as $lang) {
                $tab->name[$lang['id_lang']] = $this->l('Configure');
            }
            if ($fr = Language::getIdByIso('fr')) {
                $tab->name[$fr] = $this->l('Configure');
            }
            $tab->id_parent = $tabMain->id;
            $tab->module = $this->name;
            $tab->active = 1;
            $tab->add();
        } else {
            $tab = new Tab();
            $tab->class_name = 'AdminMailjet';
            $tab->name = array();
            foreach (Language::getLanguages() as $lang) {
                $tab->name[$lang['id_lang']] = $this->l('Mailjet');
            }
            if ($fr = Language::getIdByIso('fr')) {
                $tab->name[$fr] = $this->l('Mailjet');
            }
            $tab->id_parent = (int)Tab::getIdFromClassName('AdminTools');
            $tab->module = $this->name;
            $tab->active = 1;
            $tab->add();
        }

        $this->createTriggers();
        Configuration::updateValue('MJ_ALLEMAILS', 1);

        return (
            parent::install()
            && $this->loadConfiguration()
            && $this->registerHook('actionAdminCustomersControllerSaveBefore')
            && $this->registerHook('actionAdminCustomersControllerSaveAfter')
            && $this->registerHook('actionAdminCustomersControllerStatusAfter')
            && $this->registerHook('actionAdminCustomersControllerDeleteBefore')
            && $this->registerHook('actionExportGDPRData')
            && $this->registerHook('actionObjectCustomerDeleteBefore')
            && $this->registerHook('actionObjectCustomerUpdateAfter')
            && $this->registerHook('adminCustomers')
            && $this->registerHook('authentication')
            && $this->registerHook('BackOfficeHeader')
            && $this->registerHook('cancelProduct')
            && $this->registerHook('cart')
            && $this->registerHook('createAccount')
            && $this->registerHook('header')
            && $this->registerHook('invoice')
            && $this->registerHook('newOrder')
            && $this->registerHook('orderConfirmation')
            && $this->registerHook('orderReturn')
            && $this->registerHook('orderSlip')
            && $this->registerHook('registerGDPRConsent')
            && $this->registerHook('updateOrderStatus')
            && $this->registerHook('updateQuantity')
            && $this->registerHook('actionNewsletterRegistrationAfter')
            && $this->registerHook('actionNewsletterRegistrationBefore')
            && $this->registerHook('actionControllerInitBefore')
        );
    }

    // Export customer gdpr data
    public function hookActionExportGDPRData($customer)
    {
        $email = $customer['email'];
        if (empty($email)) {
            return json_encode($this->l('Invalid email format.'));
        }
        $api = MailjetTemplate::getApi();
        $contactData = $api->getContactData($email);

        try {
            $contactByEmail = $api->getContactByEmail($email);
            $creationDate = !empty($contactByEmail->Data[0] && $contactByEmail->Data[0]->CreatedAt) ? new DateTime($contactByEmail->Data[0]->CreatedAt) : '';
            $lastCampaignSent = !empty($contactByEmail->Data[0] && $contactByEmail->Data[0]->CreatedAt) ? new DateTime($contactByEmail->Data[0]->CreatedAt) : '';
        } catch (Mailjet_ApiException $e) {
            return json_encode($this->l('No data'));
        }

        $data = array();
        $firstname = '';
        $lastname = '';
        if (is_object($contactData) && $contactData->Count > 0 && isset($contactData->Data[0]) && isset($contactData->Data[0]->Data)) {
            $contactDataProperties = $contactData->Data[0]->Data;
            foreach ($contactDataProperties as $propertyData) {
                if ($propertyData->Name == 'firstname') {
                    $firstname = $propertyData->Value;
                    continue;
                }
                if ($propertyData->Name == 'lastname') {
                    $lastname = $propertyData->Value;
                    continue;
                }
            }
            $data[] = array(
                'Email' => $email,
                'Firstname' => $firstname,
                'Lastname' => $lastname,
                'Last campaign sent' => $lastCampaignSent->format('Y-m-d H:i:s'),
                'Creation date' => $creationDate->format('Y-m-d H:i:s')
            );
            return json_encode($data);
        }

        return json_encode($this->l('No data'));
    }

    public function uninstall()
    {
        $fileTranslationCache = $this->_path . '/translations/translation_cache.txt';
        if (file_exists($fileTranslationCache)) {
            unlink($fileTranslationCache);
        }

        // Uninstall SQL
        $sql = array();
        include(_PS_MODULE_DIR_ . 'mailjet/sql/uninstall.php');
        foreach ($sql as $s) {
            if (!Db::getInstance()->execute($s)) {
                return false;
            }
        }

        // Uninstall Tab
        $tab = new Tab((int)Tab::getIdFromClassName('AdminMailjet'));
        $tab->delete();
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $tabMain = new Tab((int)Tab::getIdFromClassName('AdminMailjetMain'));
            $tabMain->delete();
        }

        // Deletes and unsets all Mailjet module related settings
        Configuration::deleteByName('MAILJET');
        Configuration::deleteByName('MJ_TRIGGERS');
        Configuration::deleteByName('MJ_ALLEMAILS');
        $this->configToDefault();
        unset($this->account);
        unset($this->triggers);

        return parent::uninstall();
    }


    public function hookHeader()
    {
        if (Tools::getIsset('tokp')) {
            if (!$this->context->cart->id) {
                $this->context->cart->add();
                $this->context->cookie->id_cart = $this->context->cart->id;
            }

            Db::getInstance()->execute('REPLACE INTO `' . _DB_PREFIX_ . 'mj_roi_cart`(id_cart, token_presta)
			    VALUES(' . $this->context->cart->id . ', \'' . pSQL(Tools::getValue('tokp')) . '\')');
        }

        /*
         * RM: 29680 - add prestashop newsletter sunscribers to Mailjet master contact list
         */
        if (Tools::isSubmit('submitNewsletter')) {
            try {
                $initialSynchronization = new HooksSynchronizationSingleUser(MailjetTemplate::getApi());
                $masterListId = $initialSynchronization->getAlreadyCreatedMasterListId();

                // UnSubscribe
                if (Tools::getValue('action') == '1') {
                    // Remove customer from all lists(and segment lists)
                    $initialSynchronization->remove(Tools::getValue('email'), $masterListId);
                } elseif (Tools::getValue('action') == '0') { // Subscribe - to the Mailjet master list
                    $initialSynchronization->subscribe(Tools::getValue('email'), $masterListId);
                }
            } catch (Exception $e) {
                $this->errors_list[] = $this->l($e->getMessage());
            }
        }
    }


    public function hookNewOrder($params)
    {
        if (empty($params['customer']->id)) {
            return '';
        }
        $this->checkAutoAssignment((int)$params['customer']->id);
        if (empty($params['order']->id_cart)) {
            return '';
        }
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'mj_roi_cart`
            WHERE id_cart = ' . (int)$params['order']->id_cart;

        if ($tokp = Db::getInstance()->getRow($sql)) {
            // On enregistre le ROI
            $sql = 'SELECT campaign_id FROM ' . _DB_PREFIX_ . 'mj_campaign WHERE token_presta = \'' .
                pSQL($tokp['token_presta']) . "'";
            $campaign = Db::GetInstance()->GetRow($sql);

            if (!empty($campaign)) {
                $sql = 'REPLACE INTO ' . _DB_PREFIX_ . 'mj_roi (campaign_id, id_order, total_paid, date_add)
				VALUES (' . (int)$campaign['campaign_id'] . ', ' . (int)$params['order']->id . ', ' .
                    (float)$params['order']->total_paid . ', NOW())';
                Db::GetInstance()->Execute($sql);
            }
        }
    }

    public function hookActionControllerInitBefore()
    {
        if (Tools::isSubmit('subscribedmerged') && $this->isAccountSet()) {
            $id = Tools::getValue('id');
            if (preg_match('/(^N)/', $id)) {
                $id = (int) Tools::substr($id, 1);
                if (version_compare(_PS_VERSION_, '1.7', '<')) {
                    $sql = 'SELECT `email` FROM '._DB_PREFIX_.'newsletter WHERE `id` = \''. pSQL($id).'\'';
                } else {
                    $sql = 'SELECT `email` FROM '._DB_PREFIX_.'emailsubscription WHERE `id` = \''. pSQL($id).'\'';
                }
                if ($subscriber = Db::getInstance()->getRow($sql)) {
                    if (!empty($subscriber['email'])) {
                        $initialSynchronization = new HooksSynchronizationSingleUser(MailjetTemplate::getApi());
                        $masterListId = $initialSynchronization->getAlreadyCreatedMasterListId();
                        $initialSynchronization->unsubscribe($subscriber['email'], $masterListId);
                    }
                }
            }
        }

    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getIsset('sync_list')) {
            $sync = new HooksSynchronizationInitial(MailjetTemplate::getApi());
            $listId = $sync->getAlreadyCreatedMasterListId();
            $sync->synchronizeSubscribers($listId);
        }
        // Process subscribers changed by Admin through the BackOffice
        if (Tools::isSubmit('subscribedmerged') && $this->isAccountSet()) {
            $id = Tools::getValue('id');
            if (preg_match('/(^N)/', $id)) {
                $id = (int) Tools::substr($id, 1);
                if (version_compare(_PS_VERSION_, '1.7', '<')) {
                    $sql = 'SELECT `email` FROM '._DB_PREFIX_.'newsletter WHERE `id` = \''.pSQL($id).'\'';
                } else {
                    $sql = 'SELECT `email` FROM '._DB_PREFIX_.'emailsubscription WHERE `id` = \''.pSQL($id).'\'';
                }
                if ($subscriber = Db::getInstance()->getRow($sql)) {
                    if (!empty($subscriber['email'])) {
                        $initialSynchronization = new HooksSynchronizationSingleUser(MailjetTemplate::getApi());
                        $masterListId = $initialSynchronization->getAlreadyCreatedMasterListId();
                        $initialSynchronization->remove($subscriber['email'], $masterListId);
                    }
                }
            }
        } // End Subscribers processing


        $controller = Tools::getValue('tab');
        if (empty($controller)) {
            $controller = Tools::getValue('controller');
        }

        if (!in_array($controller, array('AdminModules', 'adminmodules', 'AdminCustomers'))) {
            return '';
        } elseif (Tools::getValue('configure') != $this->name) {
            return '';
        }

        $smarty_page = array();
        $nobug = array();
        foreach ($this->mj_pages->getPages() as $name => $value) {
            $smarty_page['MJ_' . $name] = $name;
            $nobug = $value;
        }

        $this->context->controller->addCss($this->_path . '/views/css/style.css');
        $this->context->controller->addCSS($this->_path . '/views/css/bo.css');
        $this->context->controller->addCSS($this->_path . '/views/css/bundlejs_prestashop.css');
        $this->context->controller->addJquery();
        $this->context->controller->addJs($this->_path . '/views/js/jquery.timer.js');
        $this->context->controller->addJs($this->_path . '/views/js/bo.js');
        $this->context->controller->addJs($this->_path . '/views/js/events.js');
        $this->context->controller->addJs($this->_path . '/views/js/functions.js');
        $this->context->controller->addJs($this->_path . '/views/js/main.js');
        $this->context->controller->addJs($this->_path . '/views/js/bundlejs_prestashop.js');

        $api = MailjetTemplate::getApi();
        $infos = $api->getUser();
        $mjSenders = $api->getSenders(null, $infos);
        $currentSender = Configuration::get('PS_SHOP_EMAIL');

        $this->context->smarty->assign(
            array(
                'MJ_base_dir' => $this->module_access['uri'],
                'MJ_local_path' => $this->module_access['dir'],
                'MJ_REQUEST_PAGE_TYPE' => MailJetPages::REQUEST_PAGE_TYPE,
                'MJ_ADMINMODULES_TOKEN' => Tools::getAdminTokenLite('AdminModules'),
                'MJ_available_pages' => $smarty_page,
                'MJ_tab_page' => $this->mj_pages->getPages(MailJetPages::REQUIRE_PAGE),
                'MJ_adminmodules_link' => $this->getAdminModuleLink(array()),
                'MJ_allemails_active' => Configuration::get('MJ_ALLEMAILS'),
                'MJ_TOKEN' => $this->account->TOKEN,
                'nobug' => $nobug,
                'mjSenders' => $this->getOnlyEmailSenders($mjSenders),
                'currentSender' => $currentSender
            )
        );

        if ($this->isAccountSet()) {
            $this->context->smarty->assign(
                array(
                    'MJ_tab_page' => $this->mj_pages->getPages(MailJetPages::REQUIRE_PAGE)
                )
            );
        }

        return $this->fetchTemplate('/views/templates/admin/', 'bo-header');
    }

    private function getOnlyEmailSenders($sendersFromApi)
    {
        $emailSenders = array();
        foreach ($sendersFromApi as $sender) {
            if (strpos($sender->Email->Email, '*') === false) {
                $emailSenders[] = $sender;
            }
        }
        return $emailSenders;
    }

    /**
     * New customer is created via Administration Panel
     * @author atanas
     * @param array $params
     */
    public function hookActionAdminCustomersControllerSaveBefore()
    {
        $customer = new Customer(Tools::getValue('id_customer'));
        Configuration::updateValue('PREVIOUS_MJ_USER_MAIL', $customer->email);
    }

    /**
     * Just after new customer is created via Administration Panel
     * @param array $params
     */
    public function hookActionAdminCustomersControllerSaveAfter($params)
    {
        $customer = $params['return'];
        $initialSynchronization = new HooksSynchronizationSingleUser(MailjetTemplate::getApi());

        try {
            if ($customer->active == 0) {
                // Remove customer from all lists(and segment lists)
                $initialSynchronization->removeFromAllLists($customer->email);
            } else {
                // Manage customer in his segment lists
                $filter_ids = $this->newCheckAutoAssignment($customer->id);
                foreach ($filter_ids as $filter_id => $result) {
                    $obj = new Segmentation();
                    $mailjetListID = $obj->getMailjetContactListId($filter_id);

                    if ($result) {
                        $initialSynchronization->addToList($customer, $mailjetListID);
                    } else {
                        // Remove customer from a list
                        $initialSynchronization->remove($customer->email, $mailjetListID);
                    }
                }
                // Add to the master list
                if ($customer->newsletter == 1) {
                    $masterListId = $initialSynchronization->getAlreadyCreatedMasterListId();
                    $initialSynchronization->subscribe($customer->email, $masterListId);
                }
            }
        } catch (Exception $e) {
            $this->errors_list[] = $this->l($e->getMessage());
        }
    }
    
    /**
     * Retrieve active segments
     * @return array
     */
    private function getAutoAssigmentSegments()
    {
        $sql = 'SELECT *
            FROM ' . _DB_PREFIX_ . 'mj_filter f
            LEFT JOIN ' . _DB_PREFIX_ . 'mj_condition c ON c.id_filter = f.id_filter
            WHERE f.assignment_auto = 1';

        $rows = DB::getInstance()->executeS($sql);
        if (!is_array($rows)) {
            return $this;
        }

        $formatRows = array();
        foreach ($rows as $row) {
            $id_filter = (int) $row['id_filter'];
            $formatRows[$id_filter]['mode'] = 0;
            $formatRows[$id_filter]['replace_customer'] = (bool) $row['replace_customer'];
            $formatRows[$id_filter]['name'] = $row['name'];
            $formatRows[$id_filter]['description'] = $row['description'];
            $formatRows[$id_filter]['idfilter'] = $id_filter;
            $formatRows[$id_filter]['idgroup'] = $row['id_group'];
            $formatRows[$id_filter]['rule_a'][] = $row['rule_a'];
            $formatRows[$id_filter]['rule_action'][] = $row['rule_action'];
            $formatRows[$id_filter]['baseSelect'][] = $row['id_basecondition'];
            $formatRows[$id_filter]['sourceSelect'][] = $row['id_sourcecondition'];
            $formatRows[$id_filter]['fieldSelect'][] = $row['id_fieldcondition'];
            $formatRows[$id_filter]['data'][] = $row['data'];
            $formatRows[$id_filter]['value1'][] = $row['value1'];
            $formatRows[$id_filter]['value2'][] = $row['value2'];
        }
        return $formatRows;
    }

    /**
     * Hook which is triggered right after customer account is changed - either by the customer himself via Frontend
     * or by admin via Customers listing - click on 'Newsletter' checkbox in the listing
     * (note that the Hook for customer profile edition by Admin is different -
     * it is hookActionAdminCustomersControllerSaveAfter)
     * @param type $params
     */
    public function hookActionAdminCustomersControllerStatusAfter($params)
    {
        $customer = $params['return'];
        $initialSynchronization = new HooksSynchronizationSingleUser(MailjetTemplate::getApi());
        try {
            $this->checkAutoAssignment($customer->id);
            if ($customer->active == 0) {
                $initialSynchronization->removeFromAllLists($customer->email);
            } elseif ($customer->active == 1 && $customer->newsletter == 0) {
                // Get all lists where customer is subscribed
                $subsSegmentListsIds = $initialSynchronization->getSubscribedSegmentLists($customer->email);
                
                // Unsubscribe user from all lists where he is subscribed
                foreach ($subsSegmentListsIds as $listId) {
                    $initialSynchronization->unsubscribe($customer->email, $listId);
                }
            } elseif ($customer->active == 1 && $customer->newsletter == 1) {
                $initialSynchronization->subscribe($customer);
            }
        } catch (Exception $e) {
            $this->errors_list[] = $this->l($e->getMessage());
        }
    }
    
    /**
     * User profile action
     * @param type $params
     */
    public function hookActionObjectCustomerUpdateAfter($params)
    {
        $customer = $params['object'];
        $initialSynchronization = new HooksSynchronizationSingleUser(MailjetTemplate::getApi());
        try {
            $this->checkAutoAssignment($customer->id);
            if ($customer->deleted == 1) {
                $initialSynchronization->removeFromAllLists($customer->email);
            } elseif ($customer->newsletter == 0) {
                $initialSynchronization->unsubscribe($customer->email);
            } elseif ($customer->active == 1 && $customer->newsletter == 1) {
                $initialSynchronization->subscribe($customer);
            }
        } catch (Exception $e) {
            $this->errors_list[] = $this->l($e->getMessage());
        }
    }

    public function hookActionObjectCustomerDeleteBefore($params)
    {
        $customer = $params['object'];

        if (!$customer->id) {
            return;
        }

        $singleUserSynchronization = new HooksSynchronizationSingleUser(MailjetTemplate::getApi());

        try {
            $singleUserSynchronization->remove($customer->email);
        } catch (Exception $e) {
            $this->errors_list[] = $this->l($e->getMessage());
        }
    }


    /**
     *
     * @author atanas
     * @param array $params
     */
    public function hookActionAdminCustomersControllerDeleteBefore()
    {
        $customer = new Customer(Tools::getValue('id_customer'));

        if (!$customer->id) {
            return;
        }

        $singleUserSynchronization = new HooksSynchronizationSingleUser(MailjetTemplate::getApi());

        try {
            $singleUserSynchronization->removeFromAllLists($customer->email);
        } catch (Exception $e) {
            $this->errors_list[] = $this->l($e->getMessage());
        }
    }

    public function hookAdminCustomers()
    {
        return;

        /*
          $api = MailjetTemplate::getApi();
          $customer = new Customer((int)Tools::getValue('id_customer'));

          $params = array(
          'from' => $customer->email
          );

          $stats = (($res = $api->reportEmailStatistics($params)) && isset($res->stats)) ? $res->stats : array();

          $translation = MailJetTranslate::getTranslationsByName('stats');
          $data = array();

          foreach ($stats as $key => $value)
          if (isset($translation[$key]))
          $data[$key] = array('title' => $translation[$key], 'value' => $value);

          // Split the array into 2 for the display
          $total = count($data);
          $this->context->smarty->assign(
            'MJ_stats', array(array_slice($data, 0, $total / 2), array_slice($data, $total / 2))
            );
          return $this->fetchTemplate('/views/templates/admin/', 'customer');
         */
    }

    /**
     *
     * @author atanas
     * @param unknown_type $params
     * @return boolean
     */
    public function hookCreateAccount($params)
    {
        $initialSynchronization = new HooksSynchronizationSingleUser(MailjetTemplate::getApi());

        try {
            if ($params['newCustomer']->active == 1 && $params['newCustomer']->newsletter == 1) {
                $initialSynchronization->subscribe($params['newCustomer']);
            }
            $this->checkAutoAssignment($params['newCustomer']->id);
        } catch (Exception $e) {
            $this->errors_list[] = $this->l($e->getMessage());
            return false;
        }
    }

    /**
     * @param array $params
     * @return void
     */
    public function hookActionNewsletterRegistrationAfter(array $params)
    {
        if (!$params['error'] && $params['action'] == '0') {
            try {
                $initialSynchronization = new HooksSynchronizationSingleUser(MailjetTemplate::getApi());
                $masterListId = $initialSynchronization->getAlreadyCreatedMasterListId();
                $initialSynchronization->subscribe($params['email'], $masterListId);
            } catch (Exception $e) {
                $this->errors_list[] = $this->l($e->getMessage());
            }
        }
    }

    /**
     * @param array $params
     * @return void
     */
    public function hookActionNewsletterRegistrationBefore(array $params)
    {
        if (!$params['error'] && $params['action'] == '1') {
            try {
                $initialSynchronization = new HooksSynchronizationSingleUser(MailjetTemplate::getApi());
                $masterListId = $initialSynchronization->getAlreadyCreatedMasterListId();
                $initialSynchronization->remove($params['email'], $masterListId);
            } catch (Exception $e) {
                $this->errors_list[] = $this->l($e->getMessage());
            }
        }
    }

    /**
     *
     * @author atanas
     * @param unknown_type $params
     * @return boolean
     */
    public function hookCustomerAccount($params)
    {
        $initialSynchronization = new HooksSynchronizationSingleUser(MailjetTemplate::getApi());

        try {
            if ($params['newCustomer']->active == 1 && $params['newCustomer']->newsletter == 1) {
                $initialSynchronization->subscribe($params['newCustomer']);
            }
        } catch (Exception $e) {
            $this->errors_list[] = $this->l($e->getMessage());
            return false;
        }
    }

    public function hookUpdateQuantity($params)
    {
        return $this->hookUpdateOrderStatus($params);
    }

    public function hookCart($params)
    {
        if (!empty($params['cart'])) {
            $this->checkAutoAssignment((int)$params['cart']->id_customer);
        }
        return '';
    }

    public function hookAuthentication($params)
    {
        return $this->hookNewOrder($params);
    }

    public function hookInvoice($params)
    {
        return $this->hookUpdateOrderStatus($params);
    }

    public function hookUpdateOrderStatus($params)
    {
        if (isset($params['id_order'])) {
            $sql = 'SELECT id_customer
                FROM ' . _DB_PREFIX_ . 'orders
                WHERE id_order = ' . (int)$params['id_order'];

            if (($id_customer = (int)Db::getInstance()->getValue($sql)) > 0) {
                $this->checkAutoAssignment($id_customer);
            }
        } elseif (isset($params['cart'])) {
            $cart = $params['cart'];
            if ($cart instanceof Cart && isset($cart->id_customer)) {
                $this->checkAutoAssignment((int)$cart->id_customer);
            }
        }

        return '';
    }

    public function hookOrderSlip($params)
    {
        return $this->hookUpdateOrderStatus($params);
    }

    public function hookOrderReturn($params)
    {
        return $this->hookUpdateOrderStatus($params);
    }

    public function hookCancelProduct($params)
    {
        return $this->hookUpdateOrderStatus($params);
    }

    public function newCheckAutoAssignment($id_customer)
    {
        $formatRows = $this->getAutoAssigmentSegments();
        $filterIds = array();
        foreach ($formatRows as $filterId => $formatRow) {
            $obj = new Segmentation();
            $sql = $obj->getQuery($formatRow, true, false, ' c.id_customer = ' . (int)$id_customer);

            $result = DB::getInstance()->executeS($sql);
            if ($result && !$obj->belongsToGroup($formatRow['idgroup'], $id_customer)) {
                if ($formatRow['replace_customer']) {
                    $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'customer_group WHERE id_customer = ' . (int)$id_customer;
                    Db::getInstance()->execute($sql);
                }
                Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'customer_group` (`id_customer`, `id_group`)
                    VALUES ("' . ((int) $id_customer) . '", "' .(int) $formatRow['idgroup'] . '")');
            } elseif (!$result && $obj->belongsToGroup($formatRow['idgroup'], $id_customer)) {
                $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'customer_group
                    WHERE id_group = ' . (int)$formatRow['idgroup'] . ' AND id_customer = ' . (int)$id_customer;
                DB::getInstance()->execute($sql);
            }
            
            $filterIds[$filterId] = $result ? $filterId : false;
        }
        return $filterIds;
    }

    public function checkAutoAssignment($id_customer = 0)
    {
        $formatRows = $this->getAutoAssigmentSegments();

        foreach ($formatRows as $filterId => $formatRow) {
            $obj = new Segmentation();
            $sql = $obj->getQuery($formatRow, true, false, ' c.id_customer = ' . (int)$id_customer);

            $result = DB::getInstance()->executeS($sql);

            if ($result && !$obj->belongsToGroup($formatRow['idgroup'], $id_customer)) {
                if ($formatRow['replace_customer']) {
                    $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'customer_group WHERE id_customer = ' . (int)$id_customer;
                    Db::getInstance()->execute($sql);
                }

                Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'customer_group` (`id_customer`, `id_group`)
                    VALUES ("' . ((int) $id_customer) . '", "' .(int) $formatRow['idgroup'] . '")');

                // Mailjet update
                $customer = new Customer($id_customer);
            } elseif (!$result && $obj->belongsToGroup($formatRow['idgroup'], $id_customer)) {
                $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'customer_group
                    WHERE id_group = ' . (int)$formatRow['idgroup'] . ' AND id_customer = ' . (int)$id_customer;

                DB::getInstance()->execute($sql);

                // Mailjet update
                $customer = new Customer($id_customer);
            }
            if (!isset($customer)) {
                $customer = new Customer($id_customer);
            }
            $initialSynchronization = new HooksSynchronizationSingleUser(MailjetTemplate::getApi());
            $mailjetListID = $obj->getMailjetContactListId($filterId);

            if ($result) {
                if ($customer->active == 1) {
                    $initialSynchronization->subscribe($customer, $mailjetListID);
                } else {
                    $initialSynchronization->unsubscribe($customer, $mailjetListID);
                }
            } else {
                $initialSynchronization->remove($customer->email, $mailjetListID);
            }
        }
        return $this;
    }

    public function loadConfiguration()
    {
        return Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . "mj_basecondition` VALUES 
            (1, 0, '`%1customer` c')")
            && Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . "mj_fieldcondition` VALUES
            (2, 1, 105, '', '', 1, NULL),
            (3, 1, 104, '', '', 1, NULL),
            (4, 1, 59, '', '', 1, NULL),
            (5, 1, 6, '', '', 1, 'product;null;null'),
            (6, 1, 7, '', '', 1, 'category;null;null'),
            (7, 1, 8, '', '', 1, 'brand;null;null'),
            (8, 1, 10, '', '', 1, NULL),
            (9, 1, 11, '', '', 1, NULL),
            (10, 3, 9, '', '', 1, NULL),
            (11, 2, 12, '', '', 0, 'gender;null;null;null'),
            (12, 2, 13, '', '', 1, 'null;date;date'),
            (13, 2, 14, '', '', 0, 'country;null;null'),
            (15, 1, 61, '', '', 0, NULL),
            (16, 1, 62, '', '', 0, NULL),
            (17, 2, 63, '', '', 0, 'null;date;date'),
            (18, 2, 64, '', '', 0, 'null;date;date'),
            (19, 2, 65, '', '', 0, 'null;date;date'),
            (20, 2, 66, '', '', 0, NULL),
            (21, 2, 69, '', '', 0, NULL),
            (22, 2, 70, '', '', 0, NULL),
            (23, 2, 71, '', '', 0, NULL),
            (24, 2, 72, '', '', 0, NULL),
            (25, 2, 76, '', '', 0, NULL),
            (26, 2, 77, '', '', 0, NULL),
            (33, 1, 92, '', '', 0, 'date;null;null'),
            (28, 1, 88, '', '', 0, 'null;date;date'),
            (29, 3, 91, '', '', 0, 'null;date;date'),
            (30, 3, 6, '', '', 0, 'product;null;null'),
            (31, 3, 7, '', '', 0, 'category;null;null'),
            (32, 3, 8, '', '', 0, 'brand;null;null'),
            (34, 1, 70, '', '', 0, NULL),
            (35, 1, 94, '', '', 0, 'null;date;date'),
            (36, 2, 99, '', '', 0, 'null;date;date')")
            &&
            Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . "mj_sourcecondition` VALUES
            (1, 1, 1, 'LEFT JOIN `%1orders` o ON c.`id_customer` = o.`id_customer`\r\nLEFT JOIN `%1order_detail` od
            ON o.`id_order` = od.`id_order`\r\nLEFT JOIN `%1currency` cu ON cu.`id_currency` = o.`id_currency`'),
            (2, 1, 0, 'LEFT JOIN `%1address` ad ON c.`id_customer` = ad.`id_customer` '),
            (3, 1, 90, NULL),
            (4, 1, 107, 'LEFT JOIN `%1shop` s ON s.`id_shop` = c.`id_shop`')");
    }

    public function fetchTemplate($path, $name)
    {
        if (_PS_VERSION_ < '1.4') {
            $this->context->smarty->currentTemplate = $name;
        }

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'mailjet' . $path . $name . '.tpl');
    }

    public function postProcess()
    {
        $modif = false;
        // Res is here to test page the time to wait the functional application
        if (($res = Tools::getValue('mj_check_hosting')) && !$this->isAccountSet()) {
            $this->page_name = $res ? 'SETUP_STEP_1' : 'SETUP_STEP_0';
        } elseif (Tools::isSubmit('MJ_set_connect')) {
            $this->checkAuthentication();
        } elseif (Tools::isSubmit('MJ_set_login')) {
            $this->checkAuthentication();
        } elseif (($events_list = Tools::getValue('events'))) {
            if (Tools::getValue('fixEvents') || Tools::getValue('deleteEvents')) {
                $mj_event = new MailJetEvents();
                foreach ($events_list as $key => $id_mf_events) {
                    $mj_event->id = $id_mf_events;
                    if (Tools::getValue('deleteEvents')) {
                        $eventData = $mj_event->getEventById($mj_event->id);
                        $hooks_events = new HooksEvents();
                        $hooks_events->unsubscribe($eventData[0]);
                    }
                    if ($mj_event->delete()) {
                        unset($events_list[$key]);
                    }
                }
            }
            $this->context->smarty->assign('MJ_events_form_success', $events_list);
        }
        // All emails sending by Mailjet ?
        if (Tools::isSubmit('MJ_set_allemails')) {
            $triggers =
                ($triggers = Tools::jsonDecode(Configuration::get('MJ_TRIGGERS'), true))
                    ? $triggers
                    : $this->triggers;
            Configuration::updateValue('MJ_TRIGGERS', Tools::jsonEncode($triggers));

            if (Tools::getValue('MJ_allemails_active') == '1') {
                // Set PS SHOP EMAIL to be selected Mailjet Sender address
                $account = Tools::jsonDecode(Configuration::get('MAILJET'), true);
                $account['EMAIL'] = Tools::getValue('MJ_senders');
                Configuration::updateValue('MAILJET', Tools::jsonEncode($account));
                Configuration::updateValue('PS_SHOP_EMAIL', Tools::getValue('MJ_senders'));

                $this->context->smarty->assign(array(
                    'currentSender' => Tools::getValue('MJ_senders')
                ));

                $this->activateAllEmailMailjet();
                $triggers['active'] = 1;
            } else {
                Configuration::updateValue('PS_MAIL_METHOD', 1);
                /*
                 * deactivate triggers if Mailjet emails are disabled
                 */
                $triggers['active'] = 0;
            }

            Configuration::updateValue('MJ_ALLEMAILS', Tools::getValue('MJ_allemails_active'));
            $this->context->smarty->assign(array(
                'MJ_allemails_active' => Configuration::get('MJ_ALLEMAILS'),
                'AllMailsActiveMessage' => Tools::getValue('MJ_allemails_active') ? 1 : 2
            ));
        }
        // Campaign
        if (Tools::isSubmit('MJ_submitCampaign')) {
            $this->page_name = 'NEWSLETTER';
        }
        if (Tools::isSubmit('MJ_submitCampaign2')) {
            $this->page_name = 'CAMPAIGN3';
        }
        // Activation root file Creation
        if (Tools::isSubmit('submitCreateRootFile')) {
            $api = MailjetTemplate::getApi();
            $infos = $api->getUser();
            $sendersFromApi = $api->getSenders(null, $infos);

            if ($sendersFromApi) {
                foreach ($sendersFromApi as $sender) {
                    if (isset($sender->DNS)) {
                        if ($sender->DNS->Domain == Configuration::get('PS_SHOP_DOMAIN')
                            || $sender->DNS->Domain == Configuration::get('PS_SHOP_DOMAIN_SSL')
                        ) {
                            $fp = fopen(_PS_ROOT_DIR_ . '/' . $sender->Filename, 'w');
                            fclose($fp);
                        }
                    }
                }
            }
        }
        // Account settings : details
        if (Tools::isSubmit('MJ_set_account_details')) {
            $api = MailjetTemplate::getApi();
            $api->updateUser(
                Tools::getValue('MJ_account_address_city'),
                Tools::getValue('MJ_account_address_country'),
                Tools::getValue('MJ_account_address_postal_code'),
                Tools::getValue('MJ_account_address_street'),
                Tools::getValue('MJ_account_company_name'),
                null,
                /* Tools::getValue('MJ_account_contact_email'), */
                Tools::getValue('MJ_account_firstname'),
                Tools::getValue('MJ_account_lastname'),
                null
            ); /* $locale = NULL */
            $modif = true;
        }
        // Account settings : tracking
        if (Tools::isSubmit('MJ_set_account_tracking')) {
            $api = MailjetTemplate::getApi();
            if (Tools::getValue('MJ_account_tracking_clicks') == '1') {
                $tracking_clicks = true;
            } else {
                $tracking_clicks = false;
            }
            if (Tools::getValue('MJ_account_tracking_openers') == '1') {
                $tracking_openers = true;
            } else {
                $tracking_openers = false;
            }
            $api->updateTracking($tracking_clicks, $tracking_openers);
            $modif = true;
        }
        // Account settings : senders
        if (Tools::isSubmit('MJ_set_account_senders')) {
            $api = MailjetTemplate::getApi();
            $address = Tools::getValue('MJ_account_senders_new');

            $api->createSender($address);
            $modif = true;
        }
        // Triggers
        if (Tools::isSubmit('MJ_set_triggers')) {
            $this->triggers['active'] = Tools::getValue('MJ_triggers_active');
            for ($sel = 1; $sel <= 9; $sel++) {
                $this->triggers['trigger'][$sel]['active'] =
                    Tools::getValue('MJ_triggers_trigger_' . $sel . '_active');
                if ($sel != 5 && $sel != 6) {
                    $this->triggers['trigger'][$sel]['period'] =
                        (float)Tools::getValue('MJ_triggers_trigger_' . $sel . '_period');
                    $this->triggers['trigger'][$sel]['periodType'] =
                        (int)Tools::getValue('MJ_triggers_trigger_' . $sel . '_periodType');
                } else {
                    $this->triggers['trigger'][$sel]['discount'] =
                        (float)Tools::getValue('MJ_triggers_trigger_' . $sel . '_discount');
                    $this->triggers['trigger'][$sel]['discountType'] =
                        (int)Tools::getValue('MJ_triggers_trigger_' . $sel . '_discountType');
                }
                $languages = Language::getLanguages();
                $shop_name = $this->context->shop->name;
                $shop_url = 'http://' . $this->context->shop->domain;
                $shop_logo =
                    $shop_url . _PS_IMG_ . Configuration::get('PS_LOGO') . '?' . Configuration::get('PS_IMG_UPDATE_TIME');

                foreach ($languages as $l) {
                    $this->triggers['trigger'][$sel]['subject'][$l['id_lang']] =
                        Tools::getValue('MJ_triggers_trigger_' . $sel . '_subject_' . $l['id_lang']);
                    $this->triggers['trigger'][$sel]['mail'][$l['id_lang']] =
                        Tools::getValue('MJ_triggers_trigger_' . $sel . '_mail_' . $l['id_lang']);

                    // replace {shop_name}, {shop_url}, {shop_logo}
                    $this->triggers['trigger'][$sel]['subject'][$l['id_lang']] =
                        str_replace(
                            '{shop_name}',
                            $shop_name,
                            $this->triggers['trigger'][$sel]['subject'][$l['id_lang']]
                        );
                    $this->triggers['trigger'][$sel]['subject'][$l['id_lang']] =
                        str_replace(
                            '{shop_url}',
                            $shop_url,
                            $this->triggers['trigger'][$sel]['subject'][$l['id_lang']]
                        );
                    $this->triggers['trigger'][$sel]['subject'][$l['id_lang']] =
                        str_replace(
                            '{shop_logo}',
                            $shop_logo,
                            $this->triggers['trigger'][$sel]['subject'][$l['id_lang']]
                        );
                    $this->triggers['trigger'][$sel]['mail'][$l['id_lang']] =
                        str_replace('{shop_name}', $shop_name, $this->triggers['trigger'][$sel]['mail'][$l['id_lang']]);
                    $this->triggers['trigger'][$sel]['mail'][$l['id_lang']] =
                        str_replace('{shop_url}', $shop_url, $this->triggers['trigger'][$sel]['mail'][$l['id_lang']]);
                    $this->triggers['trigger'][$sel]['mail'][$l['id_lang']] =
                        str_replace('{shop_logo}', $shop_logo, $this->triggers['trigger'][$sel]['mail'][$l['id_lang']]);
                }
            }
            $this->updateTriggers();
            $modif = true;

            if (Tools::getValue('MJ_triggers_active') == '1') {
                // Set PS SHOP EMAIL to be selected Mailjet Sender address
                $account = Tools::jsonDecode(Configuration::get('MAILJET'), true);
                $account['EMAIL'] = Tools::getValue('MJ_senders');
                Configuration::updateValue('MAILJET', Tools::jsonEncode($account));
                Configuration::updateValue('PS_SHOP_EMAIL', Tools::getValue('MJ_senders'));

                $this->context->smarty->assign(array(
                    'currentSender' => Tools::getValue('MJ_senders')
                ));
            }
        }

        if (Tools::isSubmit('MJ_triggers_import_submit')) {
            if (isset($_FILES['MJ_triggers_import_file']['tmp_name'])
                && !empty($_FILES['MJ_triggers_import_file']['tmp_name'])
            ) {
                $file = new SplFileObject($_FILES['MJ_triggers_import_file']['tmp_name']);
                while (!$file->eof()) {
                    $triggers .= $file->fgets();
                }

                Configuration::updateValue('MJ_TRIGGERS', $triggers);
                $modif = true;
            }
        }

        if (Tools::isSubmit('MJ_triggers_export_submit')) {
            $triggers =
                ($triggers = Configuration::get('MJ_TRIGGERS')) ? $triggers : Tools::jsonEncode($this->triggers);

            header("Content-Type: plain/text");
            header("Content-Disposition: Attachment; filename=Mailjet_Trigger_Templates.txt");
            header("Pragma: no-cache");

            echo "$triggers";
            die();
        }


        if ($modif) {
            $link = new Link();
            Tools::redirectAdmin(
                $link->getAdminLink('AdminModules') .
                '&configure=mailjet&module_name=mailjet&MJ_request_page=' .
                Tools::getValue('MJ_request_page') . '&conf=4'
            );
        }
    }

    public function disable($force_all = false)
    {
        $this->configToDefault();
        return parent::disable($force_all);

    }

    public function enable($force_all = false)
    {
        $this->activateAllEmailMailjet();
        return parent::enable($force_all);
    }

    public function configToDefault()
    {
        Configuration::updateValue('PS_MAIL_METHOD', self::DEFAULT_MAIL_OPTION);
    }
    
    public function activateAllEmailMailjet()
    {
        Configuration::updateValue('PS_MAIL_SERVER', $this->mj_mail_server);
        Configuration::updateValue('PS_MAIL_SMTP_PORT', $this->mj_mail_port);
        //Configuration::updateValue('PS_MAIL_SMTP_ENCRYPTION', 'tls');
        Configuration::updateValue('PS_MAIL_USER', $this->account->API_KEY);
        Configuration::updateValue('PS_MAIL_PASSWD', $this->account->SECRET_KEY);
        Configuration::updateValue('PS_MAIL_METHOD', 2);
        Configuration::updateValue('MJ_ALLEMAILS', 1);

        $account = Tools::jsonDecode(Configuration::get('MAILJET'), true);
        Configuration::updateValue('PS_SHOP_EMAIL', $account['EMAIL']);
        self::setSMTPconnectionParams();
    }

    public static function setSMTPconnectionParams()
    {

        $configs = array(
            array('ssl://', 465),
            array('tls://', 587),
            array('', 587),
            array('', 588),
            array('tls://', 25),
            array('', 25)
        );

        $host = Configuration::get('PS_MAIL_SERVER');

        $connected = false;

        for ($i = 0; $i < count($configs); ++$i) {
            $errno = null;
            $errstr = null;
            $soc = @fSockOpen($configs [$i] [0] . $host, $configs [$i] [1], $errno, $errstr, 5);

            if ($soc) {
                fClose($soc);
                $connected = true;
                break;
            }
        }

        if ($connected) {
            if ('ssl://' == $configs [$i] [0]) {
                Configuration::updateValue('PS_MAIL_SMTP_ENCRYPTION', 'ssl');
            } elseif ('tls://' == $configs [$i] [0]) {
                Configuration::updateValue('PS_MAIL_SMTP_ENCRYPTION', 'tls');
            } else {
                Configuration::updateValue('PS_MAIL_SMTP_ENCRYPTION', '');
            }
            Configuration::updateValue('PS_MAIL_SMTP_PORT', $configs[$i][1]);
        }
    }

    public function getContent()
    {
        if ($this->account->MASTER_LIST_SYNCHRONIZED == 0) {
            $this->initalSynchronize();
        }

        $this->mj_template = new MailjetTemplate();
        $this->page_name = $this->mj_pages->getCurrentPageName();
        $this->postProcess();

        $this->context->smarty->assign(array('is_landing' => false));
        $output = '';
        if (Tools::isSubmit('submit' . $this->name)) {
            $configValue = (string) Tools::getValue('contact_list');

            if (empty($configValue) || !Validate::isGenericName($configValue)) {
                $output = $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('contact_list', $configValue);
                $output = $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        switch ($this->page_name) {
            case 'SETUP_LANDING':
                $mt = new MailjetTemplate();
                $this->context->smarty->assign(array(
                    'is_landing' => true,
                    'lang' => $mt->getLang()
                ));
                $this->mj_template->fetchTemplate('setup_landing_message');
                $this->mj_template->fetchTemplate('setup_landing_bt_more');
                $this->mj_template->fetchTemplate('setup_landing_bt_activate');
                break;

            case 'SETUP_STEP_0':
                $this->mj_template->fetchTemplate('setup_hosting_error_message');
                $this->mj_template->fetchTemplate('setup_hosting_error_bt_support');
                break;

            case 'SETUP_STEP_1':
                $this->mj_template->getSignupURL('SETUP_STEP_1');
                break;

            case 'CONNECT_STEP_0':
                $this->context->smarty->assign(array('account' => $this->account));
                $this->mj_template->fetchTemplate('connect_step_0');
                break;

            case 'SEGMENTATION':
                $this->segmentation = new Segmentation();
                $this->mj_template->setContent('SEGMENTATION', $this->segmentation->initContent());
                break;

            case 'CAMPAIGN':
                $this->mj_template->getCampaignURL(
                    'CAMPAIGN',
                    $this->account->{'TOKEN_' . $this->context->employee->id}
                );
                break;

            case 'STATS':
                $this->mj_template->getStatsURL(
                    'STATS',
                    $this->account->{'TOKEN_' . $this->context->employee->id}
                );
                break;

            case 'CONTACTS':
                $this->mj_template->getContactsURL(
                    'CONTACTS',
                    $this->account->{'TOKEN_' . $this->context->employee->id}
                );
                break;

            case 'NEWSLETTER':
                $this->displayNewsletter();
                break;

            case 'CAMPAIGN1':
                $this->displayCampaign(1);
                break;

            case 'CAMPAIGN2':
                $this->displayCampaign(2);
                break;

            case 'CAMPAIGN3':
                $this->displayCampaign(3);
                break;

            case 'TRIGGERS':
                $this->displayTriggers();
                break;

            case 'PRICING':
                $this->mj_template->getPricingURL(
                    'PRICING',
                    $this->account->{'TOKEN_' . $this->context->employee->id}
                );
                break;

            case 'ROI':
                $this->displayROI();
                break;

            case 'EVENTS':
                $page = ($page = Tools::getValue('page')) ? $page : 1;
                $event = MailJetEvents::ALL_EVENTS_KEYS;
                if (Tools::getValue('event')) {
                    $event = Tools::getValue('event');
                }
                $mj_event = new MailJetEvents($event);
                $mj_event->setPage($page);

                $titles = $mj_event->getFieldsName();
                unset($titles['agent']);
                unset($titles['ip']);
                unset($titles['geo']);
                unset($titles['original_address']);
                unset($titles['new_address']);

                $part = $this->context->shop->domain.$this->context->shop->physical_uri;
                $url = 'http://' . $part . 'modules/mailjet/events.php?h=' . $this->getEventsHash();

                $this->context->smarty->assign(array(
                    'MJ_events_list' => $this->setUserLinkToEvents($mj_event->fetch()),
                    'MJ_title_list' => $titles,
                    'MJ_paging' => array(
                        'total_element' => $mj_event->getTotal(),
                        'current_page' => $page,
                        'next' => (($page * MailJetEvents::LIMIT_EVENT) < $mj_event->getTotal() ? true : false),
                        'prev' => ($page > 1) ? true : false,
                        'last' => ($mj_event->getTotalPages())
                    ),
                    'MJ_all_scheme_fields' => $mj_event->getScheme(MailJetEvents::ALL_EVENTS_KEYS),
                    'host' => $this->context->shop->domain,
                    'url' => $url,
                ));

                break;

            case 'ACCOUNT':
                $this->displayAccount();
                break;
        }

        if ($this->isAccountSet()) {
            $this->checkTokenValidity();
            $this->checkPlanValidity();
        }
        $tab = ['class_name' => 'AdminModules'];
        $link = $this->context->link->getTabLink($tab) . '&configure=' . $this->name . '&conf=4&token=' . Tools::getAdminTokenLite('AdminModules') . '&sync_list=true';
        $this->context->smarty->assign(array(
            'MJ_templates' => $this->mj_template->getTemplates(),
            'MJ_iframes' => $this->mj_template->getIframesURL(),
            'MJ_errors' => $this->errors_list,
            'MJ_page_name' => $this->page_name,
            'MJ_template_name' => $this->mj_pages->getTemplateName($this->page_name),
            'MJ_local_path' => $this->module_access['dir'],
            'MJ_template_tab_name' => $this->mj_pages->getTemplateTabName($this->page_name),
            'MJ_authentication' => $this->isAccountSet(),
            'MJ_TOKEN_USER' => isset($this->account->{'TOKEN_' . $this->context->employee->id}) ?
                $this->account->{'TOKEN_' . $this->context->employee->id} : null,
            'MJ_user_plan' => $this->getPlan(),
            'MJ_adminmodules_link' => $this->getAdminModuleLink(array()),
            'MJ_REQUEST_PAGE_TYPE' => MailJetPages::REQUEST_PAGE_TYPE,
            'MJ_sync_url' => $link,
        ));
        if ($this->page_name == 'CONTACTS') {
            $this->context->smarty->assign([
                'MJ_contact_list_form' => '<div class="center_page">' . $this->displayForm() . '</div>'
            ]);
        }
        return $output . $this->fetchTemplate('/views/templates/admin/', 'configuration');
    }

    public function displayForm()
    {
        $synchronization = new HooksSynchronizationSegment(MailjetTemplate::getApi());
        $lists = $synchronization->getAllLists();
        $formatedList = [];
        foreach ($lists as $l) {
            $formatedList[] = [
                'name' => explode('idf', $l->Name)[0],
                'list_id' => explode('idf', $l->ID)[0]
            ];
        }
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Settings', [], 'Admin.Global'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('Contact list:'),
                        'name' => 'contact_list',
                        'required' => true,
                        'options' => [
                          'query' => $formatedList,
                          'id' => 'list_id',
                          'name' => 'name'
                        ]
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-primary',
                ],
            ],
        ];

        $helper = new HelperForm();

        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;
        $helper->fields_value['contact_list'] = Configuration::get('contact_list');
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');

        return $helper->generateForm([$form]);
    }

    public function displayAccount()
    {
        $api = MailjetTemplate::getApi();

        // Traitements
        //$tracking = $api->getTracking();
        $infos = $api->getUser();

        $sendersFromApi = $api->getSenders(null, $infos);

        $is_senders = 0;
        $is_domains = 0;
        $domains = array();
        $domainsCurrent = array();
        $senders = array();
        if ($sendersFromApi) {
            foreach ($sendersFromApi as $sender) {
                if (strpos($sender->Email->Email, '*') !== false) {
                    $domains[] = $sender;
                } else {
                    $senders[] = $sender;
                }

                $is_senders = 1;
                if (isset($sender->DNS)) {
                    if (Configuration::get('PS_SHOP_DOMAIN') == $sender->DNS->Domain
                        || Configuration::get('PS_SHOP_DOMAIN_SSL') == $sender->DNS->Domain
                    ) {
                        $available_domain = 1;
                        $domainsCurrent[] = $sender;
                        if (!empty($sender->Filename) && file_exists(_PS_ROOT_DIR_ . '/' . $sender->Filename)) {
                            $root_file = 1;
                        }
                    }
                    if (isset($sender->DNS->Domain)) {
                        $is_domains = 1;
                    }
                }
            }
        }

        $iso = !empty($infos->AddressCountry) ? $infos->AddressCountry : 'fr';
        $country = Country::getNameById($this->context->language->id, Country::getByIso($iso));

        $language = explode('_', $infos->Locale);
        $language = Tools::strtoupper($language[0]);

        // countries list
        $countries = Country::getCountries($this->context->language->id);

        // Assign
        $this->context->smarty->assign(array(
            'countries' => $countries,
            'infos' => $infos,
            'country' => $country,
            'language' => $language,
            'domains' => $domains,
            /* 'tracking' => $tracking, */
            'sender' => $senders,
            'is_senders' => $is_senders,
            'is_domains' => $is_domains,
            'root_file' => $root_file,
            'available_domain' => $available_domain,
            'domainsCurrent' => $domainsCurrent,
        ));
    }

    public function displayROI()
    {
        $api = MailjetTemplate::getApi(false);

        // Traitements
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'mj_campaign ORDER BY date_add DESC';
        $campaigns = Db::getInstance()->ExecuteS($sql);

        foreach ($campaigns as $key => $c) {
            if (empty($c['stats_campaign_id']) || empty($c['delivered'])) {
                $api->resetRequest();
                $api->campaignstatistics(array('NewsLetter' => $c['campaign_id']));
                $mjc = $api->getResponse();

                if (isset($mjc->Data) && isset($mjc->Data[0])) {
                    $campaigns[$key]['delivered'] = (int)$mjc->Data[0]->ProcessedCount;
                    $campaigns[$key]['title'] = $mjc->Data[0]->CampaignSubject;

                    $sql = 'UPDATE ' . _DB_PREFIX_ . 'mj_campaign
					SET stats_campaign_id = 1,
					delivered = ' . (int)$mjc->Data[0]->ProcessedCount . ',
					title = \'' . pSQL($mjc->Data[0]->CampaignSubject) . '\'
					WHERE id_campaign_presta = ' . (int)$c['id_campaign_presta'];
                    Db::getInstance()->Execute($sql);
                }
            }

            $sql = 'SELECT COUNT(id_order) AS nb, SUM(total_paid) AS total
              FROM ' . _DB_PREFIX_ . 'mj_roi WHERE campaign_id = ' . (int)$c['campaign_id'];
            $totaux = Db::getInstance()->GetRow($sql);

            if (empty($totaux['total'])) {
                $campaigns[$key]['num_sales_roi'] = 0;
                $campaigns[$key]['perc_roi'] = 0;
                $campaigns[$key]['total_roi'] = 0;
            } else {
                $campaigns[$key]['num_sales_roi'] = $totaux['nb'];
                $campaigns[$key]['perc_roi'] =
                    round((int)$totaux['nb'] * 100 / (int)$campaigns[$key]['delivered'], 2);
                $campaigns[$key]['total_roi'] = $totaux['total'];
            }
        }

        $this->context->smarty->assign(array(
            'trad_title' => $this->l('Title'),
            'trad_sentemails' => $this->l('Sent emails'),
            'trad_roiamount' => $this->l('ROI Amount'),
            'trad_roipercent' => $this->l('ROI Percent'),
            'trad_roi_num_sales' => $this->l('Number of sales'),
            'campaigns' => $campaigns
        ));
    }

    public function displayTriggers()
    {
        // smarty vars
        $triggers = $this->triggers;
        $sign = $this->context->currency->getSign();
        $languages = Language::getLanguages();
        $sel_lang = $this->context->language->id;
        $cron = Tools::getShopDomainSsl(true) . _MODULE_DIR_ . $this->name . '/mailjet.cron.php?token=' .
            (Configuration::get('SEGMENT_CUSTOMER_TOKEN')
                ? Configuration::get('SEGMENT_CUSTOMER_TOKEN')
                : Tools::getValue('token'));
        $iso = $this->context->language->iso_code;

        $api = MailjetTemplate::getApi();
        $infos = $api->getUser();
        $mjSenders = $api->getSenders(null, $infos);
        $currentSender = Configuration::get('PS_SHOP_EMAIL');

        // Assign
        $this->context->smarty->assign(array(
            'tinymce_new' => version_compare(_PS_VERSION_, '1.4.0.0'),
            'tinymce_iso' => file_exists(_PS_ROOT_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en',
            'tinymce_pathCSS' => _THEME_CSS_DIR_,
            'tinymce_pathBase' => __PS_BASE_URI__,
            'tinymce_ad' => dirname($_SERVER['PHP_SELF']),
            'tinymce_id_language' => (int)$this->context->language->id,
            'tinymce_theme' => _THEME_NAME_,
            'sign' => $sign,
            'triggers' => $triggers,
            'languages' => $languages,
            'mjSenders' => $this->getOnlyEmailSenders($mjSenders),
            'currentSender' => $currentSender,
            'sel_lang' => $sel_lang,
            'cron' => $cron
        ));
    }

    private function updateTriggers()
    {
        $triggers = $this->triggers;

        $languages = Language::getLanguages();

        for ($i = 1; $i <= 9; $i++) {
            foreach ($languages as $l) {
                $triggers['trigger'][$i]['mail'][$l['id_lang']] =
                    rawurlencode($triggers['trigger'][$i]['mail'][$l['id_lang']]);
            }
        }

        return Configuration::updateValue('MJ_TRIGGERS', Tools::jsonEncode($triggers));
    }

    private function initTriggers()
    {
        $this->triggers =
            ($triggers = Tools::jsonDecode(Configuration::get('MJ_TRIGGERS'), true)) ? $triggers : $this->triggers;

        $languages = Language::getLanguages();

        for ($i = 1; $i <= 9; $i++) {
            foreach ($languages as $l) {
                if (!empty($this->triggers['trigger'][$i]['mail'][$l['id_lang']])) {
                    $this->triggers['trigger'][$i]['mail'][$l['id_lang']] =
                        rawurldecode($this->triggers['trigger'][$i]['mail'][$l['id_lang']]);
                }
            }
        }
    }

    public function getTriggers()
    {
        return $this->triggers;
    }

    public function createTriggers()
    {
        $subject = array();
        $mail = array();
        include(_PS_MODULE_DIR_ . 'mailjet/translations/triggers_messages.php');
        $languages = Language::getLanguages();

        $shop_name = $this->context->shop->name;
        $shop_url = 'http://' . $this->context->shop->domain;

        for ($i = 1; $i <= 9; $i++) {
            if ($i != 5 && $i != 6) {
                $this->triggers['trigger'][$i]['period'] = 0;
                $this->triggers['trigger'][$i]['periodType'] = 1;
            } else {
                $this->triggers['trigger'][$i]['discount'] = 0;
                $this->triggers['trigger'][$i]['discountType'] = 1;
            }
            foreach ($languages as $l) {
                $this->triggers['trigger'][$i]['subject'][$l['id_lang']] = 'New message to {firstname} {lastname} !';
                if (isset($subject[$i]['en'])) {
                    $this->triggers['trigger'][$i]['subject'][$l['id_lang']] = utf8_decode($subject[$i]['en']);
                }
                if (isset($subject[$i][$l['iso_code']])) {
                    $this->triggers['trigger'][$i]['subject'][$l['id_lang']] =
                        utf8_decode($subject[$i][$l['iso_code']]);
                }

                $this->triggers['trigger'][$i]['mail'][$l['id_lang']] = '';
                if (isset($mail[$i]['en'])) {
                    $this->triggers['trigger'][$i]['mail'][$l['id_lang']] = utf8_decode($mail[$i]['en']);
                }
                if (isset($mail[$i][$l['iso_code']])) {
                    $this->triggers['trigger'][$i]['mail'][$l['id_lang']] = utf8_decode($mail[$i][$l['iso_code']]);
                }

                // replace {shop_name}, {shop_url}
                $this->triggers['trigger'][$i]['subject'][$l['id_lang']] =
                    str_replace('{shop_name}', $shop_name, $this->triggers['trigger'][$i]['subject'][$l['id_lang']]);
                $this->triggers['trigger'][$i]['subject'][$l['id_lang']] =
                    str_replace('{shop_url}', $shop_url, $this->triggers['trigger'][$i]['subject'][$l['id_lang']]);
                $this->triggers['trigger'][$i]['mail'][$l['id_lang']] =
                    str_replace('{shop_name}', $shop_name, $this->triggers['trigger'][$i]['mail'][$l['id_lang']]);
                $this->triggers['trigger'][$i]['mail'][$l['id_lang']] =
                    str_replace('{shop_url}', $shop_url, $this->triggers['trigger'][$i]['mail'][$l['id_lang']]);
            }
        }
        $this->updateTriggers();
    }

    /**
     * Set Admin customer link for customer
     *
     * @param $events
     * @return mixed
     */
    private function setUserLinkToEvents($events)
    {
        foreach ($events as &$event) {
            if (!empty($event['email'])) {
                $customerClass = new Customer();
                $customer = $customerClass->getByEmail($event['email']);

                if (isset($customer->id) && !empty($customer->id)) {
                    $params = array(
                        'id_customer' => $customer->id,
                        'viewcustomer' => ''
                    );
                    unset($customer);
                    $event['email'] =
                        '<a href="' . $this->getAdminModuleLink($params, 'AdminCustomers') . '">' .
                        $event['email'] .
                        '</a>';
                }
            }
        }
        return $events;
    }

    /**
     * Check the token validity
     */
    public function checkTokenValidity()
    {
        if (!isset($this->account->{'TOKEN_' . $this->context->employee->id}) ||
            $this->account->{'IP_' . $this->context->employee->id} != $_SERVER['REMOTE_ADDR'] ||
            ($this->account->{'TIMESTAMP_' . $this->context->employee->id} <= strtotime('-1 day'))
        ) {
            $this->account->{'IP_' . $this->context->employee->id} = $_SERVER['REMOTE_ADDR'];
            $this->account->{'TIMESTAMP_' . $this->context->employee->id} = strtotime('now');
            $api = MailjetTemplate::getApi(false);
            $params = array(
                'AllowedAccess' => 'campaigns,contacts,stats,pricing,account,reports',
                'method' => 'JSON',
                'APIKeyALT' => $api->getAPIKey(),
                'TokenType' => 'iframe',
                'IsActive' => true,
                'SentData' => Tools::jsonEncode(array('plugin' => 'prestashop-3.0')),
            );
            $api->apitoken($params);
            $response = $api->getResponse();
            if (!empty($response->Count) && ($response->Count > 0)) {
                $this->account->{'TOKEN_' . $this->context->employee->id} = $response->Data[0]->Token;
                $this->updateAccountSettings();
            }
            if ($this->account->{'MASTER_LIST_SYNCHRONIZED'} == 0) {
                $this->initalSynchronize();
            }
        }
    }

    /**
     *
     * @author atanas
     * @return mixed
     */
    protected function getPlan()
    {
        if (!$this->isAccountSet()) {
            return null;
        }
    }

    public function checkPlanValidity()
    {
        /* $test = */
        new Mailjet_ApiOverlay($this->account->API_KEY, $this->account->SECRET_KEY);
        return;
        /*
          $plan = $test->getUserPlan();

          if (Tools::getValue('MJ_request_page') != "PRICING" && ($plan->uname == "free" || $plan->uname == "bronze"))
          {
          // On redirige vers le pricing
          //header("Location: index.php?tab=AdminModules&configure=mailjet&token=".
          Tools::getValue('token')."&module_name=mailjet&MJ_request_page=PRICING");
          //die();
          }
         */
    }

    public function auth($apiKey, $secretKey)
    {
        $test = new Mailjet_ApiOverlay($apiKey, $secretKey);
        $result = $test->getUser();

        if ($result !== false) {
            $this->account->API_KEY = $apiKey;
            $this->account->SECRET_KEY = $secretKey;
            $this->account->EMAIL = $result->Email;
            $this->account->AUTHENTICATION = 1;

            $this->updateAccountSettings();

            Configuration::updateValue('PS_MAIL_SERVER', $this->mj_mail_server);
            Configuration::updateValue('PS_MAIL_SMTP_PORT', $this->mj_mail_port);
            //Configuration::updateValue('PS_MAIL_SMTP_ENCRYPTION', 'tls');
            Configuration::updateValue('PS_MAIL_USER', $apiKey);
            Configuration::updateValue('PS_MAIL_PASSWD', $secretKey);
            Configuration::updateValue('PS_MAIL_METHOD', 2);

            //$account = Tools::jsonDecode(Configuration::get('MAILJET'), true);
            Configuration::updateValue('PS_SHOP_EMAIL', $result->Email);
            self::setSMTPconnectionParams();

            if ($this->account->MASTER_LIST_SYNCHRONIZED == 0) {
                return $this->initalSynchronize();
            }

            return true;
        } else {
            $this->errors_list[] = $this->l('Please verify that you have entered correct API and secret key.');
        }

        return false;
    }

    /**
     *
     * @throws Exception
     */
    public function initalSynchronize()
    {
        if (!$this->isAccountSet()) {
            return false;
        }
        $initialSynchronization = new HooksSynchronizationInitial(MailjetTemplate::getApi());
        try {
            $newlyCreatedListId = $initialSynchronization->synchronize();
            if ($newlyCreatedListId) {
                $this->account->MASTER_LIST_SYNCHRONIZED = 1;
                $this->account->MASTER_LIST_ID = $newlyCreatedListId;
                $this->updateAccountSettings();
            } else {
                throw new Exception('The master list is not created.');
            }
        } catch (Exception $e) {
            $this->errors_list[] = $this->l($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Check user authentication from submit form
     */
    public function checkAuthentication()
    {
        $API_KEY = Tools::getValue('mj_api_key');
        $SECRET_KEY = Tools::getValue('mj_secret_key');

        if ($this->auth($API_KEY, $SECRET_KEY) === true) {
            Tools::redirectAdmin($this->getAdminModuleLink(array(MailJetPages::REQUEST_PAGE_TYPE => 'HOME')));
        }
    }

    /**
     * Check if the account has been activated
     *
     * @return bool
     */
    public function isAccountSet()
    {
        return $this->account->AUTHENTICATION && !(empty($this->account->API_KEY)) && !empty($this->account->SECRET_KEY);
    }

    /**
     * Init the account settings
     */
    public function initAccountSettings()
    {
        $this->account = ($account = Tools::jsonDecode(Configuration::get('MAILJET'))) ? $account : $this->account;
    }

    /**
     * Return the account value of the key requested
     * @param $key
     * @return string
     */
    public function getAccountSettingsKey($key)
    {
        return isset($this->account->$key) ? $this->account->$key : '';
    }

    /**
     * Update the account settings
     *
     * @return mixed
     */
    public function updateAccountSettings()
    {
        return Configuration::updateValue('MAILJET', Tools::jsonEncode($this->account));
    }

    /**
     * Get Admin Module link
     *
     * @param $params allow to add or override default key/value
     * @return string
     */
    public function getAdminModuleLink($params, $tab = 'AdminModules', $token = null)
    {
        $initArray = array(
            'tab' => $tab,
            'configure' => $this->name,
            'module_name' => $this->name
        );

        if (!$token) {
            $initArray['token'] = Tools::getAdminTokenLite($tab);
        } else {
            $initArray['token'] = $token;
        }

        $params = array_merge($initArray, $params);
        return 'index.php?' . http_build_query($params);
    }

    /**
     * Ajax Method
     * Check if the merchant finish his setup process
     */
    public function checkMerchantSetupState()
    {
        // 1.4 ajax need the set back the token
        $params = array(
            MailJetPages::REQUEST_PAGE_TYPE => 'LOGIN',
            'token' => Tools::getValue('admin_token')
        );

        return array(
            'result' => 1, //(bool)$this->account['ACTIVATION'],
            'url' => $this->getAdminModuleLink($params)
        );
    }

    public function checkMjAuth()
    {
        $API_KEY = Tools::getValue('mj_api_key');
        $SECRET_KEY = Tools::getValue('mj_secret_key');
        return $this->auth($API_KEY, $SECRET_KEY);
    }

    /**
     * Ajax Method
     * Mailjet will call this method when user have done the subscription process
     */
    public function checkSubscription()
    {
        $this->account->ACTIVATION = 1;
        $this->updateAccountSettings();
    }

    /*
     * Ajax Method
     * Mailjet will call this method to get error detailed.
     */

    public function errorHandling()
    {
        $obj = new MailJetEvents(Tools::getValue('event'), Tools::getValue('time'));
        $obj->add();
        header('HTTP/1.1 200 OK');
        die();
    }

    /**
     * Get MailJet token security
     */
    public function getToken()
    {
        return $this->account->TOKEN;
    }

    public function getAdminFullUrl()
    {
        $adminDirName = null;
        $maindirs = scandir(_PS_ROOT_DIR_);
        foreach ($maindirs as $dirName) {
            if (strpos($dirName, 'admin') !== false) {
                $adminDirName = $dirName;
            }
        }

        return $adminDirName;
    }

    public static function sendMail($subject, $message, $to)
    {
        try {
            // PS 1.6.1.5+ uses a new version of SWIFT mailer
            if (version_compare(_PS_VERSION_, '1.6.1.5', '>=')) {
                return self::sendMail1615($subject, $message, $to);
            }

            $account = Tools::jsonDecode(Configuration::get('MAILJET'), true);
            $from = $account['EMAIL'];
            $from_name = Configuration::get('PS_SHOP_NAME');

            $mj_mail_server_port = Configuration::get('PS_MAIL_SMTP_PORT');

            switch (Configuration::get('PS_MAIL_SMTP_ENCRYPTION')) {
                case 'tls':
                    $mj_mail_server_encryption = \Swift_Connection_SMTP::ENC_TLS;
                    break;
                case 'ssl':
                    $mj_mail_server_encryption = \Swift_Connection_SMTP::ENC_SSL;
                    break;
                default:
                    $mj_mail_server_encryption = \Swift_Connection_SMTP::ENC_OFF;
            }

            $connection = new \Swift_Connection_SMTP(
                Configuration::get('PS_MAIL_SERVER'),
                $mj_mail_server_port,
                $mj_mail_server_encryption
            );
            $connection->setUsername($account['API_KEY']);
            $connection->setPassword($account['SECRET_KEY']);

            $swift = new \Swift($connection);

            $sMessage = new \Swift_Message('[' . $from_name . '] ' . $subject);
            //$sMessage->headers->setEncoding('Q');
            $sMessage->attach(new \Swift_Message_Part(strip_tags($message), 'text/plain', '8bit', 'utf-8'));
            $sMessage->attach(new \Swift_Message_Part($message, 'text/html', '8bit', 'utf-8'));

            /* Send mail */
            $send = $swift->send($sMessage, $to, new \Swift_Address($from, $from_name));
            $swift->disconnect();

            return $send;
        } catch (\Swift_Exception $e) {
            return false;
        }
    }

    public static function sendMail1615($subject, $message, $to)
    {
        try {
            $account = Tools::jsonDecode(Configuration::get('MAILJET'), true);
            $from = $account['EMAIL'];
            $from_name = Configuration::get('PS_SHOP_NAME');

            $transport = \Swift_SmtpTransport::newInstance(
                Configuration::get('PS_MAIL_SERVER'),
                Configuration::get('PS_MAIL_SMTP_PORT'),
                Configuration::get('PS_MAIL_SMTP_ENCRYPTION')
            )
                ->setUsername($account['API_KEY'])
                ->setPassword($account['SECRET_KEY']);

            /*
              You could alternatively use a different transport such as Sendmail or Mail:

              // Sendmail
              $transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');

              // Mail
              $transport = Swift_MailTransport::newInstance();
             */

            // Create the Mailer using your created Transport
            $mailer = \Swift_Mailer::newInstance($transport);

            // Create a message
            $message = \Swift_Message::newInstance('[' . $from_name . '] ' . $subject)
                ->setFrom(array($from => $from_name))
                ->setTo(array($to))
                ->setBody($message, 'text/html');

            // $message->addPart($message, 'text/plain', 'utf-8');
            //$message->addPart($message, 'text/html', 'utf-8');
            // Send the message
            if ($mailer->send($message)) {
                $result = true;
            }
        } catch (\Swift_SwiftException $e) {
            $result = $e->getMessage();
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getEventsHash()
    {
        return md5($this->account->TOKEN);
    }

    /**
     * @param $authenticationStatus
     * @return MailJetPages
     */
    private function getMjPages($authenticationStatus)
    {
        return new MailJetPages($authenticationStatus);
    }
}
