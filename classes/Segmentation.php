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

class Segmentation
{

    public $page;
    public $trad;

    /**
     * @author atanas
     */
    protected $contactListsMap = array();

    public function __construct()
    {
        $this->name = 'segmentation';
        $this->tab = 'administration';
        $this->_path = _PS_MODULE_DIR_ . 'mailjet';

        $this->displayName = $this->l('Segment Module');
        $this->description = $this->l('Module for Customer Segmentation');
        $this->page = 25;
    }

    public function initContent()
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
            'mj_hint_fieldset' => array(
                $this->l(
                    'This module enables you to create segments of customers according to any criteria you think of. ' .
                    'You can then either display and export the selected customers or associate them to an existing ' .
                    'customer group.',
                    'mailjet'
                ),
                $this->l(
                    'These segments are particularly useful to create special offers associated with customer groups ' .
                    '(e.g., send a coupon to the customers interested in some products)',
                    'mailjet'
                ),
                $this->l(
                    'Create an infinite number of filters corresponding to your needs!',
                    'mailjet'
                )
            ),
//            'mj_datePickerJsFormat' => Context::getContext()->cookie->id_lang == Language::getIdByIso('fr') ? 'dd-mm-yy' : 'yy-mm-dd',
            'mj_datePickerJsFormat' => 'yy-mm-dd',
            'mj_datepickerPersonnalized' => version_compare(_PS_VERSION_, '1.5', '<') ? '<script type="text/javascript" src="' .
            _PS_JS_DIR_ . 'jquery/datepicker/jquery-ui-personalized-1.6rc4.packed.js"></script>' : '',
            'mj_token' => Tools::getValue('token'),
            'mj_ajaxFile' => _MODULE_DIR_ . 'mailjet/ajax/ajax.php',
            'mj_ajaxSyncFile' => _MODULE_DIR_ . 'mailjet/ajax/sync.php',
            'mj_ajaxBundle' => _MODULE_DIR_ . 'mailjet/ajax/bundlejs_prestashop.php',
            'mj_id_employee' => (int) Context::getContext()->cookie->id_employee,
            'mj_lblMan' => stripReturn($this->ll(20)),
            'mj_lblWoman' => stripReturn($this->ll(21)),
            'mj_lblUnknown' => stripReturn($this->ll(43)),
            'mj_trads' => array_map('stripReturn', $this->trad),
            'mj_groups' => Group::getGroups((int) Context::getContext()->cookie->id_lang),
            'mj_filter_list' => Db::getInstance()->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'mj_filter`'),
            'mj_base_select' => Db::getInstance()->ExecuteS('SELECT id_basecondition, label FROM `' . _DB_PREFIX_ . 'mj_basecondition`')
        ));

        return '';
    }

    public static function l($string, $specific = false)
    {
        $module = new Mailjet();
        if ($string == $module->l($string, $specific)) {
            $trad_file = _PS_MODULE_DIR_ . 'mailjet/translations/' . Context::getContext()->language->iso_code . '.php';
            if (file_exists($trad_file)) {
                $_MODULE = array();
                @include_once($trad_file);

                $key = '<{mailjet}prestashop>segmentation_' . md5(str_replace('\'', '\\\'', $string));
                /*
                  if (!isset($_MODULE[$key]) && Context::getContext()->language->iso_code!='en')
                  {
                  $f = fopen($trad_file,"a+");
                  fwrite($f, '$_MODULE[\''.$key.'\'] = \''.$string.'\';'.PHP_EOL);
                  fclose($f);
                  }
                 */
            }

            return (isset($_MODULE[$key]) ? $_MODULE[$key] : ($module->l($string, $specific)));
        } else {
            return $module->l($string, $specific);
        }
    }

    public function ll($i)
    {
        if (!isset($this->trad) || empty($this->trad[0])) {
            $this->initLang();
        }
        return $this->trad[$i];
    }

    public function getSourceSelect($ID, $inputID, $selected = null)
    {
        $res = Db::getInstance()->executeS(
            'SELECT id_sourcecondition, label FROM `' .
            _DB_PREFIX_ . 'mj_sourcecondition` WHERE `id_basecondition` = ' . (int) $ID
        );
        $html = '<select id="sourceSelect' .
            Tools::safeOutput($inputID) . '" name="sourceSelect[]" class="sourceSelect fixed">';
        $html .= '<option value="-1">--SELECT--</option>';
        foreach ($res as $r) {
            $html .= '<option value="' . Tools::safeOutput($r['id_sourcecondition']) . '"';
            if ($selected == $r['id_sourcecondition']) {
                $html .= 'selected=selected';
            }
            $html .= ' >' . Tools::safeOutput($this->ll($r['label'])) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function getIndicSelect($ID, $inputID, $selected = null)
    {
        // ID = 4 when trying to segment by multi store customers
        $query = $ID == 4 ? 'SELECT id_shop AS id_fieldcondition, name AS label FROM `' .
        _DB_PREFIX_ . 'shop` WHERE active = 1 ORDER BY name' : 'SELECT id_fieldcondition, label FROM `' .
        _DB_PREFIX_ . 'mj_fieldcondition` WHERE `id_sourcecondition` = ' . (int) $ID;
        $res = Db::getInstance()->ExecuteS($query);
        $html = '<select name="fieldSelect[]" class="fieldSelect fixed" id="fieldSelect' . Tools::safeOutput($inputID) . '">';
        $html .= '<option value="-1">--SELECT--</option>';
        foreach ($res as $r) {
            /* reserved cases 30 to 40 for names of shops for multi-store segmentation @see ajax/ajax.php */
            //$addId = $ID == 4 ? 29 : 0;
            $html .= '<option value="' . Tools::safeOutput($r['id_fieldcondition']) . '"';
            if ($selected == $r['id_fieldcondition']) {
                $html .= 'selected=selected';
            }
            $html .= ' >' . Tools::safeOutput($ID == 4 ? $r['label'] : $this->ll($r['label'])) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function getBinder($ID)
    {
        return Db::getInstance()->getValue('SELECT binder FROM `' . _DB_PREFIX_ . 'mj_fieldcondition` WHERE `id_fieldcondition` = ' . (int) $ID);
    }

    public function formatDate($post)
    {
        switch ((int) Context::getContext()->cookie->id_lang) {
            case 2:
                $dataToFormat = array(33);
                if (isset($post['fieldSelect'])) {
                    foreach ($post['fieldSelect'] as $key => $value) {
                        if (in_array($value, array(12, 17, 18, 19, 20, 28, 35, 36))) {
                            if (Tools::strlen($post['value1'][$key]) >= 10) {
                                $post['value1'][$key] = Tools::substr($post['value1'][$key], 6, 4) . '-' .
                                Tools::substr($post['value1'][$key], 3, 2) .
                                '-' .
                                Tools::substr($post['value1'][$key], 0, 2);
                            }

                            if (Tools::strlen($post['value2'][$key]) >= 10) {
                                $post['value2'][$key] = Tools::substr($post['value2'][$key], 6, 4) . '-' .
                                Tools::substr($post['value2'][$key], 3, 2) .
                                '-' .
                                Tools::substr($post['value2'][$key], 0, 2);
                            }
                        }
                        if (in_array($value, $dataToFormat)) {
                            if (Tools::strlen($post['data'][$key]) >= 10) {
                                $post['data'][$key] = Tools::substr($post['data'][$key], 6, 4) . '-' .
                                Tools::substr($post['data'][$key], 3, 2) .
                                '-' .
                                Tools::substr($post['data'][$key], 0, 2);
                            }
                        }
                    }
                }
                break;
            default:
        }
        return $post;
    }

    /**
     * Retrieves segments of specified type
     * @param int $segmentIndex
     * @param int[] $sourceSelect
     * @return int[]
     */
    public function getSegmentByType($segmentIndex, $sourceSelect)
    {
        $fieldSelectData = array();
        foreach ($sourceSelect as $sourceKey => $source) {
            if ($source == $segmentIndex) {
                $fieldSelectData[$sourceKey] = $this->fieldSelect[$sourceKey];
            }
        }
        return $fieldSelectData;
    }

    /**
     * @todo Prevent joins that already are added
     * @todo Group by can be doubled
     * @param array $post
     * @param bool $live
     * @param array $limit
     * @param type $speField
     * @return string
     */
    public function getQuery($post, $live, $limit = false, $having_id_customer = false)
    {
        $this->initContent();
        if (empty($post)) {
            $post = $_GET;
        }


        $from = str_replace('%1', _DB_PREFIX_, $this->getBase($post['baseSelect'][0]));
        $join = '';
        $joined_tables = array();
        $additional_select_column = '';
        $order_by = '';
        $where = '';
        $having = '';
        $havings = array();
        $ordersSegmentIndex = 1;
        $customerSegmentIndex = 2;
        $abandonedCartsIndex = 3;
        $shopSegmentIndex = 4;
        
        

        $this->fieldSelect = $post['fieldSelect'];
        $sourceSelect = $post['sourceSelect'];
        $sourceData = $post['data'];
        $ruleA = $post['rule_a'];
        $ruleAction = $post['rule_action'];
        $value1 = $post['value1'];
        $value2 = $post['value2'];
        $i = 0;
        if ($live) {
            $isCustomerSegment = in_array($customerSegmentIndex, $sourceSelect);
            // If there are any customer segments
            if ($isCustomerSegment) {
                $fieldSelectData = $this->getSegmentByType($customerSegmentIndex, $sourceSelect);

                $i = 0;
                foreach ($fieldSelectData as $fieldKey => $case) {
                    $logicalOperator = ' ' . $ruleA[$fieldKey] . ' ';
                    $operator = $ruleAction[$fieldKey] == 'IN' ? ' = ' : ' != ';
                    $action = $ruleAction[$fieldKey] == 'IN';
                    if ($ruleAction[$fieldKey] == 'IN') {
                        $minAction = ' >= ';
                        $maxAction = ' <= ';
                        $exclude = false;
                    } else {
                        $minAction = ' <= ';
                        $maxAction = ' >= ';
                        $exclude = true;
                    }
                    switch ($case) {
                        // Gender
                        case '11':
                            $i++;
                            // In db customer without gender is set to 0 insteat of 9
                            $gender = $sourceData[$fieldKey] == 9 ? 0 : $sourceData[$fieldKey];
                            $where .= $logicalOperator . ' c.id_gender ' . $operator . ' ' . $gender;
                            break;
                        // Subscription date
                        case '12':
                            $i++;
                            $data = false;
                            $where .= $logicalOperator . ' c.newsletter = 1 ';

                            if (Tools::strlen($value1[$fieldKey]) > 0) {
                                if (!validateDate($value1[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[82]);
                                }
                                $data = true;
                                $where .= ' AND UNIX_TIMESTAMP(c.newsletter_date_add) ' . $minAction
                                . 'UNIX_TIMESTAMP("' . pSQL($value1[$fieldKey]) . ' 00:00:00") ';
                            }
                            if (Tools::strlen($value2[$fieldKey]) > 0) {
                                if (!validateDate($value2[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[82]);
                                }
                                $data = true;
                                $where .= 'AND UNIX_TIMESTAMP(c.newsletter_date_add) ' . $maxAction
                                . 'UNIX_TIMESTAMP("' . pSQL($value2[$fieldKey]) . ' 23:59:59") ';
                            }
                            if ($exclude) {
                                $where .= ' OR c.newsletter_date_add="0000-00-00 00:00:00" ';
                            }
                            
                            if (!$data) {
                                $this->displayRuleError($i, $this->trad[82]);
                            }
                            break;
                        // Country
                        case '13':
                            $i++;
                            $where .= $logicalOperator . ' ad.id_country ' . $operator . ' ' . $sourceData[$fieldKey];
                            break;
                        // Last visit
                        case '17':
                            $i++;
                            break;
                        // Date of birth
                        case '18':
                            $i++;
                            $data = false;
                            if (Tools::strlen($value1[$fieldKey]) > 0) {
                                if (!validateDate($value1[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[85]);
                                }
                                $data = true;
                                $where .= $logicalOperator . ' UNIX_TIMESTAMP(c.birthday) ' . $minAction
                                . 'UNIX_TIMESTAMP("' . pSQL($value1[$fieldKey]) . ' 00:00:00") ';
                            }
                            if (Tools::strlen($value2[$fieldKey]) > 0) {
                                if (!validateDate($value1[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[85]);
                                }
                                $data = true;
                                $where .= $logicalOperator . ' UNIX_TIMESTAMP(c.birthday) ' . $maxAction
                                . 'UNIX_TIMESTAMP("' . pSQL($value2[$fieldKey]) . ' 23:59:59") ';
                            }
                            if ($exclude) {
                                $where .= ' OR c.birthday="0000-00-00" ';
                            }
                            if (!$data) {
                                $this->displayRuleError($i, $this->trad[85]);
                            }
                            break;
                        // Newsletter subscription and date
                        case '19':
                            $i++;
                            $where .= $logicalOperator . ' c.newsletter ' . $operator . ' ' . $sourceData[$fieldKey];
                            if (Tools::strlen($value1[$fieldKey]) > 0) {
                                if (!validateDate($value1[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[82]);
                                }
                                $where .= 'UNIX_TIMESTAMP(c.newsletter_date_add) ' . $minAction
                                . 'UNIX_TIMESTAMP("' . pSQL($value1[$fieldKey]) . ' 00:00:00") ';
                            }
                            if (Tools::strlen($value2[$fieldKey]) > 0) {
                                if (!validateDate($value1[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[82]);
                                }
                                $where .= 'UNIX_TIMESTAMP(c.newsletter_date_add) ' . $maxAction
                                . 'UNIX_TIMESTAMP("' . pSQL($value2[$fieldKey]) . ' 23:59:59") ';
                            }
                            if ($exclude) {
                                $where .= ' OR c.newsletter_date_add="0000-00-00 00:00:00" ';
                            }
                            break;
                        // Newsletter opt-in
                        case '20':
                            $i++;
                            $where .= $logicalOperator . ' c.optin ' . $operator . ' ' . $sourceData[$fieldKey];
                            break;
                        // Origin
                        case '21':
                            $i++;
                            // @todo Data is empty allways
                            if ($sourceData[$fieldKey]) {
                                if (!in_array('guest', $joined_tables)) {
                                    $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'guest AS g ON g.id_customer = c.id_customer ';
                                    $joined_tables[] = 'guest';
                                }
                                if (!in_array('connections', $joined_tables)) {
                                    $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'connections conn ON conn.id_guest = g.id_guest ';
                                    $joined_tables[] = 'connections';
                                }
                                $like = ' LIKE ';
                                if ($exclude) {
                                    $like = ' NOT LIKE ';
                                }
                                $where .= $logicalOperator . ' conn.http_referer ' . $like . ' "%' . pSQL($sourceData[$fieldKey]) . '%"';
                            }
                            break;
                        // Promo code
                        case '22':
                            $i++;
                            $discount_table = _PS_VERSION_ >= '1.5.0.1' ? 'cart_rule' : 'discount';
                            if (!in_array($discount_table, $joined_tables)) {
                                $join .= ' LEFT JOIN ' . _DB_PREFIX_ . $discount_table . ' AS d ON d.id_customer = c.id_customer ';
                                $joined_tables[] = $discount_table;
                            }
                            $where .= $logicalOperator . 'd.active' . $operator . (int) $sourceData[$fieldKey];
                            break;
                        // Assets
                        case '23':
                            $i++;
                            break;
                        // Product return
                        case '24':
                            $i++;
                            if (!in_array('order_return', $joined_tables)) {
                                $join .= 'LEFT JOIN ' . _DB_PREFIX_ . 'order_return AS oret ON oret.id_customer = c.id_customer';
                                $joined_tables[] = 'order_return';
                            }
                            $where .= $logicalOperator . ' oret.id_customer ' . $operator . ' ' . $sourceData[$fieldKey];
                            break;
                        // Address contains
                        case '25':
                            $i++;
                            if (Tools::strlen($sourceData[$fieldKey]) > 0) {
                                if ($action) {
                                    // Include
                                    $where .= $logicalOperator . ' ad.address1 LIKE "%' . pSQL($sourceData[$fieldKey]) . '%" '
                                    . ' OR ad.address2 LIKE "%' . $sourceData[$fieldKey] . '%" ';
                                } else {
                                    // Exclude
                                    $where .= $logicalOperator . ' ((ad.address1 IS NULL OR ad.address1 NOT LIKE "%' . pSQL($sourceData[$fieldKey]) . '%" ) AND '
                                    . ' (ad.address2 IS NULL OR ad.address2 NOT LIKE "%' . $sourceData[$fieldKey] . '%" ))';
                                }
                            }
                            break;
                        // Postcode starts with
                        case '26':
                            $i++;
                            if (Tools::strlen((int) $sourceData[$fieldKey]) > 0) {
                                if ($action) {
                                    // Include
                                    $where .= $logicalOperator . ' ad.postcode LIKE "' . pSQL($sourceData[$fieldKey]) . '%"';
                                } else {
                                    // EXclude
                                    $where .= $logicalOperator . ' ad.postcode IS NULL OR ad.postcode NOT LIKE "' . pSQL($sourceData[$fieldKey]) . '%"';
                                }
                            }
                            break;
                        // Date of visit
                        case '36':
                            $i++;
                            if (!in_array('guest', $joined_tables)) {
                                $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'guest AS g ON g.id_customer = c.id_customer ';
                                $joined_tables[] = 'guest';
                            }
                            if (!in_array('connections', $joined_tables)) {
                                $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'connections conn ON conn.id_guest = g.id_guest ';
                                $joined_tables[] = 'connections';
                            }
                            $operator = ' AND ';
                            if ($ruleAction[$fieldKey] == 'IN') {
                                $minValue1 = ' >= ';
                                $maxValue2 = ' <= ';
                            } else {
                                $minValue1 = ' <= ';
                                $maxValue2 = ' >= ';
                                $operator = ' OR ';
                            }
                            if (Tools::strlen($value1[$fieldKey]) > 0) {
                                if (!validateDate($value1[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[100]);
                                }
                                $where .= $logicalOperator . ' UNIX_TIMESTAMP(conn.date_add)' . $minValue1
                                . 'UNIX_TIMESTAMP("' . pSQL($value1[$fieldKey]) . ' 00:00:00") ';
                            }
                            if (Tools::strlen($value2[$fieldKey]) > 0) {
                                if (!validateDate($value2[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[100]);
                                }
                                $where .= $operator . 'UNIX_TIMESTAMP(conn.date_add)' . $maxValue2
                                . 'UNIX_TIMESTAMP("' . pSQL($value2[$fieldKey]) . ' 23:59:59") ';
                            }
                            break;
                    }
                }
            }
            $isOrderSegment = in_array($ordersSegmentIndex, $sourceSelect);
            // If there are any order segments
            if ($isOrderSegment) {
                // Table `orders` is used in each orderSegment so we need it anyway
                if (!in_array('orders', $joined_tables)) {
                    $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'orders o ON o.id_customer = c.id_customer';
                    $joined_tables[] = 'orders';
                }
                $fieldSelectData = $this->getSegmentByType($ordersSegmentIndex, $sourceSelect);
                foreach ($fieldSelectData as $fieldKey => $orderCase) {
                    $logicalOperator = ' ' . $ruleA[$fieldKey] . ' ';
                    // include - true , exclude - false
                    $exclude = $ruleAction[$fieldKey] != 'IN';
                    $action_oprator = $ruleAction[$fieldKey] == 'IN' ? '=' : '!=';
                    $minValue1 = (int) $value1[$fieldKey];
                    $maxValue2 = (int) $value2[$fieldKey];
                    switch ($orderCase) {
                        // Number of orders
                        case '2':
                            $i++;
                            if (strpos($additional_select_column, 'count(o.id_customer) AS "Number of orders"') === false) {
                                $additional_select_column .= ', count(o.id_customer) AS "Number of orders"';
                            }
                            $and = '';
                            if (!$exclude) {
                                // Include
                                $min_operator = '>=';
                                $max_operator = '<=';
                                $and = ' AND ';
                            } else {
                                // Exclude
                                // Not equal because we cannot exclude e.g. customer with 1 order
                                $min_operator = '<';
                                $max_operator = '>';
                                $and = ' OR ';
                            }
                            if ($minValue1 >= 0) {
                                $having .= ' count(o.id_customer) ' . $min_operator . $minValue1;
                            }
                            if ($maxValue2 > 0) {
                                if ($having != '') {
                                    $having .= $and;
                                }
                                $having .= ' count(o.id_customer) ' . $max_operator . $maxValue2;
                            }
                            $havings[] = $having;
                            $having = '';
                            break;
                        // Order status
                        case '3':
                            $i++;
                            if (!in_array('order_history', $joined_tables)) {
                                $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'order_history AS oh ON oh.id_order = o.id_order';
                                $joined_tables[] = 'order_history';
                            }
                            if ($sourceData[$fieldKey] > 0) {
                                $exclude = $exclude ? ' OR id_order_state is null' : '';
                                $where .= $logicalOperator . ' id_order_state ' . $action_oprator . $sourceData[$fieldKey] . $exclude;
                            }
                            break;
                        // Payment method
                        case '4':
                            $i++;
                            $exclude = $exclude ? ' OR o.payment is null' : '';
                            $where .= $logicalOperator . ' o.payment ' . $action_oprator . '"' . $sourceData[$fieldKey] . '" ' . $exclude;
                            break;
                        // Product name
                        case '5':
                            $i++;
                            if (!in_array('order_detail', $joined_tables)) {
                                $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'order_detail od ON od.id_order = o.id_order ';
                                $joined_tables[] = 'order_detail';
                            }
                            $exclude = $exclude ? ' OR od.product_id IS NULL ' : '';
                            $where .= $logicalOperator . ' od.product_id ' . $action_oprator . $sourceData[$fieldKey] . $exclude;
                            break;
                        // Category name
                        case '6':
                            $i++;
                            // Exclude problem - One product can be in many categories, so we don`t know from which category is
                            // and can not exclude it
                            $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'order_detail od ON od.id_order = o.id_order ';
                            $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'category_product cp ON cp.id_product = od.product_id ';
                            $exclude = $exclude ? ' OR cp.id_category IS NULL ' : '';
                            $where .= $logicalOperator . ' cp.id_category ' . $action_oprator . $sourceData[$fieldKey] . $exclude;
                            break;
                        // Brand name
                        case '7':
                            $i++;
                            if (!in_array('order_detail', $joined_tables)) {
                                $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'order_detail AS od ON od.id_order = o.id_order ';
                                $joined_tables[] = 'order_detail';
                            }
                            if (!in_array('product', $joined_tables)) {
                                $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'product AS p ON p.id_product = od.product_id ';
                                $joined_tables[] = 'product';
                            }
                            if (!in_array('manufacturer', $joined_tables)) {
                                $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer AS m ON m.id_manufacturer = p.id_manufacturer ';
                                $joined_tables[] = 'manufacturer';
                            }

                            $exclude = $exclude ? ' OR m.id_manufacturer IS NULL ' : '';
                            $where .= $logicalOperator . ' m.id_manufacturer ' . $action_oprator . $sourceData[$fieldKey] . $exclude;
                            break;
                        // Sales
                        case '8':
                            $i++;
                            # Feature
//                            if (!in_array('order_state', $joined_tables)) {
//                                $join .= ' JOIN ' . _DB_PREFIX_ . 'order_state AS os ON os.id_order_state = o.current_state AND os.paid = 1 ';
//                                $joined_tables[] = 'order_state';
//                            }
                            if (!in_array('currency', $joined_tables)) {
                                $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'currency AS cu ON cu.id_currency = o.id_currency ';
                                $joined_tables[] = 'currency';
                            }
                            if (strpos($additional_select_column, 'cu.conversion_rate') === false) {
                                    $additional_select_column .= ', cu.conversion_rate';
                            }
                            if (strpos($additional_select_column, 'o.total_paid_real') === false) {
                                    $additional_select_column .= ', o.total_paid_real';
                            }
                            if (!$exclude) {
                                // Include
                                $minAction = ' >= ';
                                $maxAction = ' <= ';
                                $exclude = '';
                                $operator = ' AND ';
                            } else {
                                // Exclude
                                $minAction = ' <= ';
                                $maxAction = ' >= ';
                                $exclude = ' OR o.total_paid_real IS NULL ';
                                $operator = ' OR ';
                            }
                            if ($sourceData[$fieldKey] == 1) {
                                $having .= ' FORMAT((SUM(o.total_paid_real)/cu.conversion_rate), 2) ' . $minAction . $minValue1;
                                if ($maxValue2 > 0) {
                                    $having .= $operator . ' FORMAT((SUM(o.total_paid_real)/cu.conversion_rate), 2) ' . $maxAction . $maxValue2;
                                }
                            }

                            if ($sourceData[$fieldKey] == 2) {
                                $having .= ' FORMAT((SUM(o.total_products)/cu.conversion_rate), 2) ' . $minAction . $minValue1;
                                if ($maxValue2 > 0) {
                                    $having .= $operator . ' FORMAT((SUM(o.total_products)/cu.conversion_rate), 2) ' . $maxAction . $maxValue2;
                                }
                            }
                            $having .= $exclude;
                            $havings[] = $having;
                            $having = '';
                            break;
                        // Average sales
                        case '9':
                            $i++;
                            // Get orders with only paid current status
                            if (!in_array('order_state', $joined_tables)) {
                                $join .= ' JOIN ' . _DB_PREFIX_ . 'order_state AS os ON os.id_order_state = o.current_state AND os.paid = 1 ';
                                $joined_tables[] = 'order_state';
                            }
                            if (!in_array('currency', $joined_tables)) {
                                $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'currency AS cu ON cu.id_currency = o.id_currency ';
                                $joined_tables[] = 'currency';
                            }
                            if (strpos($additional_select_column, 'cu.conversion_rate') === false) {
                                $additional_select_column .= ', cu.conversion_rate';
                            }
                            if (strpos($additional_select_column, 'FORMAT((AVG(o.total_paid_real)/cu.conversion_rate), 2) AS "Average sales"') === false) {
                                $additional_select_column .= ', FORMAT((AVG(o.total_paid_real)/cu.conversion_rate), 2) AS "Average sales"';
                            }
                            if ($ruleAction[$fieldKey] == 'IN') {
                                // Include
                                $minAction = ' >= ';
                                $maxAction = ' <= ';
                                $exclude = '';
                                $operator = ' AND ';
                            } else {
                                // Exclude
                                $minAction = ' <= ';
                                $maxAction = ' >= ';
                                $exclude = ' OR o.total_paid_real IS NULL ';
                                $operator = ' OR ';
                            }

                            if ($sourceData[$fieldKey] == 1) {
                                $having .= ' FORMAT((AVG(o.total_paid_real)/cu.conversion_rate), 2) ' . $minAction . $minValue1;
                                if ($maxValue2 > 0) {
                                    $having .= $operator . ' FORMAT((AVG(o.total_paid_real)/cu.conversion_rate), 2) ' . $maxAction . $maxValue2;
                                }
                            }

                            if ($sourceData[$fieldKey] == 2) {
                                $having .= ' FORMAT((AVG(o.total_products)/cu.conversion_rate), 2)' . $minAction . $minValue1;
                                if ($maxValue2 > 0) {
                                    $having .= $operator . 'FORMAT((AVG(o.total_products)/cu.conversion_rate), 2) ' . $maxAction . $maxValue2;
                                }
                            }
                            $having .= $exclude;
                            $havings[] = $having;
                            $having = '';
                            break;
                        // Gift package
                        case '15':
                            $i++;
                            $action = $ruleAction[$fieldKey] == 'IN' ? ' = ' : ' != ';
                            $exclude = $action == ' != ' ? ' OR o.gift IS NULL' : '';
                            $where .= $logicalOperator . ' o.gift ' . $action . (int) $sourceData[$fieldKey] . $exclude;
                            break;
                        // Recycled packaging
                        case '16':
                            $i++;
                            $action = $ruleAction[$fieldKey] == 'IN' ? ' = ' : ' != ';
                            $exclude = $action == ' != ' ? ' OR o.recyclable IS NULL' : '';
                            $where .= ' o.recyclable ' . $action . (int) $sourceData[$fieldKey] . $exclude;
                            break;

                        // Order date
                        case '28':
                            $i++;
                            if ($ruleAction[$fieldKey] == 'IN') {
                                // Include
                                $minAction = ' >= ';
                                $maxAction = ' <= ';
                                $exclude = '';
                                $is_negative = '';
                            } else {
                                // Exclude
                                $minAction = ' <= ';
                                $maxAction = ' >= ';
                                $exclude = ' OR o.date_add IS NULL ';
                                $is_negative = ' NOT ';
                            }
                            if (Tools::strlen($value1[$fieldKey]) > 0 && Tools::strlen($value2[$fieldKey]) > 0) {
                                if (!validateDate($value1[$fieldKey]) || !validateDate($value2[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[89]);
                                }
                                $where .= $logicalOperator . ' UNIX_TIMESTAMP(o.date_add) ' . $is_negative . ' BETWEEN UNIX_TIMESTAMP("' .
                                        pSQL($value1[$fieldKey]) . ' 00:00:00") AND UNIX_TIMESTAMP("' . pSQL($value2[$fieldKey]) . ' 23:59:59")';
                            } elseif (Tools::strlen($value1[$fieldKey]) > 0) {
                                if (!validateDate($value1[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[89]);
                                }
                                $where .= $logicalOperator . ' UNIX_TIMESTAMP(o.date_add) ' . $minAction
                                        . ' UNIX_TIMESTAMP("' . pSQL($value1[$fieldKey]) . ' 00:00:00") ';
                            } elseif (Tools::strlen($value2[$fieldKey]) > 0) {
                                if (!validateDate($value2[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[89]);
                                }
                                $where .= $logicalOperator . ' UNIX_TIMESTAMP(o.date_add) ' . $maxAction
                                        . ' UNIX_TIMESTAMP("' . pSQL($value2[$fieldKey]) . ' 23:59:59") ';
                            } else {
                                $this->displayRuleError($i, $this->trad[89]);
                            }
                            $where .= $exclude;
                            break;
                        // No order since
                        case '33':
                            $i++;
                            if ($ruleAction[$fieldKey] == 'IN') {
                                $include = ' OR o.date_add IS NULL ';
                                $sign = ' < ';
                            } else {
                                $include = '';
                                $sign = ' > ';
                            }
                            if (Tools::strlen($sourceData[$fieldKey]) > 0) {
                                if (!validateDate($sourceData[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[93]);
                                }
                                $where .= $logicalOperator . 'UNIX_TIMESTAMP(o.date_add) ' . $sign . ' UNIX_TIMESTAMP("' . pSQL($sourceData[$fieldKey]) . '")' . $include;
                            } else {
                                $this->displayRuleError($i, $this->trad[93]);
                            }
                            $order_by .= ' ORDER BY UNIX_TIMESTAMP(o.date_add) DESC';
                            break;
                        // Promo code
                        case '34':
                            $i++;
                            #  _DB_PREFIX_ . 'order_discount'; - does not exists
                            # ps17_order_cart_rule - may be should be used, but it doesnt work as expected
                            break;
                        // Order frequency
                        case '35':
                            $i++;
                            if ((int) $sourceData[$fieldKey] == 0) {
                                $this->displayRuleError($i, $this->trad[95]);
                            } else {
                                $having .= ' COUNT(c.id_customer) >= ' . (int) $sourceData[$fieldKey];
                                $havings[] = $having;
                                $having = '';
                            }
                            if ($ruleAction[$fieldKey] == 'IN') {
                                // Include
                                $minAction = ' >= ';
                                $maxAction = ' <= ';
                                $exclude = '';
                                $is_negative = '';
                            } else {
                                // Exclude
                                $minAction = ' <= ';
                                $maxAction = ' >= ';
                                $exclude = ' OR o.date_add IS NULL ';
                                $is_negative = ' NOT ';
                            }
                            if (Tools::strlen($value1[$fieldKey]) > 0 && Tools::strlen($value2[$fieldKey]) > 0) {
                                if (!validateDate($value1[$fieldKey]) || !validateDate($value2[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[89]);
                                }
                                $where .= $logicalOperator . 'UNIX_TIMESTAMP(o.date_add) ' . $is_negative . 'BETWEEN UNIX_TIMESTAMP("' .
                                        pSQL($value1[$fieldKey]) . ' 00:00:00") AND UNIX_TIMESTAMP("' . pSQL($value2[$fieldKey]) . ' 23:59:59")';
                            } elseif (Tools::strlen($value1[$fieldKey]) > 0) {
                                if (!validateDate($value1[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[89]);
                                }
                                $where .= $logicalOperator . 'UNIX_TIMESTAMP(o.date_add) ' . $minAction
                                        . ' UNIX_TIMESTAMP("' . pSQL($value1[$fieldKey]) . ' 00:00:00") ';
                            } elseif (Tools::strlen($value2[$fieldKey]) > 0) {
                                if (!validateDate($value2[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[89]);
                                }
                                $where .= $logicalOperator . 'UNIX_TIMESTAMP(o.date_add) ' . $maxAction
                                        . ' UNIX_TIMESTAMP("' . pSQL($value2[$fieldKey]) . ' 23:59:59") ';
                            } else {
                                $this->displayRuleError($i, $this->trad[89]);
                            }
                            $where .= $exclude;
                            break;
                    }
                }
            }

            $isAbandonedCartSegment = in_array($abandonedCartsIndex, $sourceSelect);
            // If there are any order segments
            if ($isAbandonedCartSegment) {
                $fieldSelectData = $this->getSegmentByType($abandonedCartsIndex, $sourceSelect);

                // Tables `cart` and `orders` are used in all abandonedCart segments
                if (!in_array('cart', $joined_tables)) {
                    $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'cart AS cart ON (cart.id_customer = c.id_customer) ';
                    $joined_tables[] = 'cart';
                }
                if (!in_array('orders', $joined_tables)) {
                    $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'orders AS o ON (o.id_customer = c.id_customer) ';
                    $joined_tables[] = 'orders';
                }
                foreach ($fieldSelectData as $fieldKey => $case) {
                    $include = $ruleAction[$fieldKey] == 'IN';
                    $logicalOperator = ' ' . $ruleA[$fieldKey] . ' ';

                    switch ($case) {
                        // Number of abandoned carts
                        case '10':
                            $i++;
                            $action = $include ? '>=' : '<=';
                            if (strpos($additional_select_column, 'o.id_order') === false) {
                                $additional_select_column .= ', o.id_order';
                            }
                            if ($ruleAction[$fieldKey] == 'IN') {
                                // Include
                                $having .= ' COUNT(cart.id_cart) >= ' . (int) $value1[$fieldKey];
                                if ($value2[$fieldKey] != '') {
                                    $having .= ' AND COUNT(cart.id_cart) <=' . (int) $value2[$fieldKey];
                                }
                            } else {
                                // Exclude
                                $having .= ' o.id_order IS NULL OR COUNT(cart.id_cart) <= ' . (int) $value1[$fieldKey];
                                if ($value2[$fieldKey] != '') {
                                    $having .= ' OR COUNT(cart.id_cart) >=' . (int) $value2[$fieldKey];
                                }
                            }
                            $havings[] = $having;
                            $having = '';
                            break;
                        // Date of abandoned cart
                        case '29':
                            $i++;
                            #$having .= ' o.id_order IS NULL ';
                            if ($ruleAction[$fieldKey] == 'IN') {
                                // Include
                                $minAction = ' >= ';
                                $maxAction = ' <= ';
                                $exclude = '';
                                $is_negative = '';
                            } else {
                                // Exclude
                                $minAction = ' <= ';
                                $maxAction = ' >= ';
                                $exclude = ' OR cart.date_upd IS NULL ';
                                $is_negative = ' NOT ';
                            }
                            if (Tools::strlen($value1[$fieldKey]) > 0 && Tools::strlen($value2[$fieldKey]) > 0) {
                                if (!validateDate($value1[$fieldKey]) || !validateDate($value2[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[103]);
                                }
                                $where .= $logicalOperator . 'UNIX_TIMESTAMP(cart.date_upd) ' . $is_negative . 'BETWEEN UNIX_TIMESTAMP("' .
                                    pSQL($value1[$fieldKey]) . ' 00:00:00") AND UNIX_TIMESTAMP("' . pSQL($value2[$fieldKey]) . ' 23:59:59")';
                            } elseif (Tools::strlen($value1[$fieldKey]) > 0) {
                                if (!validateDate($value1[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[103]);
                                }
                                $where .= $logicalOperator . 'UNIX_TIMESTAMP(cart.date_upd) ' . $minAction .
                                    ' UNIX_TIMESTAMP("' . pSQL($value1[$fieldKey]) . ' 00:00:00") ';
                            } elseif (Tools::strlen($value2[$fieldKey]) > 0) {
                                if (!validateDate($value2[$fieldKey])) {
                                    $this->displayRuleError($i, $this->trad[103]);
                                }
                                $where .= $logicalOperator . 'UNIX_TIMESTAMP(cart.date_upd) ' . $maxAction .
                                    ' UNIX_TIMESTAMP("' . pSQL($value2[$fieldKey]) . ' 23:59:59") ';
                            } else {
                                $this->displayRuleError($i, $this->trad[103]);
                            }
                            $where .= $exclude;
                            break;
                        // Product name
                        case '30':
                            $i++;
                            if (Tools::strlen((int) $sourceData[$fieldKey]) > 0) {
                                $action = $ruleAction[$fieldKey] == 'IN' ? ' = ' : ' != ';
                                if (!in_array('cart_product', $joined_tables)) {
                                    $join .= ' JOIN ' . _DB_PREFIX_ . 'cart_product AS cp ON cp.id_cart = cart.id_cart and cp.id_product' . $action . (int) $sourceData[$fieldKey];
                                    $joined_tables[] = 'cart_product';
                                }
                                $having .= ' o.id_order IS NULL ';
                                $havings[] = $having;
                                $having = '';
                            }
                            break;
                        // Category name
                        case '31':
                            $i++;
                            if (Tools::strlen($sourceData[$fieldKey]) > 0) {
                                if (!in_array('cart_product', $joined_tables)) {
                                    $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'cart_product as cp ON(cp.id_cart=cart.id_cart) ';
                                    $joined_tables[] = 'cart_product';
                                }
                                if (!in_array('product', $joined_tables)) {
                                    $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'product as p ON(cp.id_product=p.id_product) ';
                                    $joined_tables[] = 'product';
                                }
                                if (!in_array('category_lang', $joined_tables)) {
                                    $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'category_lang as cl ON(cl.id_category=p.id_category_default) ';
                                    $joined_tables[] = 'category_lang';
                                }
                                if ($ruleAction[$fieldKey] == 'IN') {
                                    $action = ' = ';
                                } else {
                                    $action = ' != ';
                                }
                                if (strpos($additional_select_column, 'o.id_order') === false) {
                                    $additional_select_column .= ', o.id_order';
                                }
                                $where .= ' AND cl.id_category' . $action . $sourceData[$fieldKey];
                                $having .= ' COUNT(cart.id_cart) >= 1 and o.id_order IS NULL';
                                $havings[] = $having;
                                $having = '';
                            }
                            break;
                        // Brand name
                        case '32':
                            $i++;
                            if (Tools::strlen($sourceData[$fieldKey]) > 0) {
                                if (!in_array('cart_product', $joined_tables)) {
                                    $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'cart_product as cp ON(cp.id_cart=cart.id_cart)';
                                    $joined_tables[] = 'cart_product';
                                }
                                if (!in_array('product', $joined_tables)) {
                                    $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'product as p ON(cp.id_product=p.id_product)';
                                    $joined_tables[] = 'product';
                                }
                                if (!in_array('manufacturer', $joined_tables)) {
                                    $join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer as m ON(m.id_manufacturer=p.id_manufacturer)';
                                    $joined_tables[] = 'manufacturer';
                                }
                                if ($ruleAction[$fieldKey] == 'IN') {
                                    $action = ' = ';
                                    $exclude = '';
                                } else {
                                    $action = ' != ';
                                    $exclude = ' OR m.id_manufacturer IS NULL';
                                }
                                if (strpos($additional_select_column, 'o.id_order') === false) {
                                    $additional_select_column .= ', o.id_order';
                                }
                                $where .= $logicalOperator . ' m.id_manufacturer ' . $action . $sourceData[$fieldKey] . $exclude;
                                $having .= ' COUNT(cart.id_cart) >= 1 AND o.id_order IS NULL ';
                                $havings[] = $having;
                                $having = '';
                            }
                            break;
                    }
                }
            }

            $isShopSegment = in_array($shopSegmentIndex, $sourceSelect);
            // If there are any customer segments
            if ($isShopSegment) {
                $fieldSelectData = $this->getSegmentByType($shopSegmentIndex, $sourceSelect);

                foreach ($fieldSelectData as $fieldKey => $case) {
                    $logicalOperator = ' ' . $ruleA[$fieldKey] . ' ';
                    if ($ruleAction[$fieldKey] == 'IN') {
                        $operator = ' = ';
                    } else {
                        $operator = ' != ';
                    }
                    $shopId = (int) $case;
                    if ($shopId > 0) {
                        $where .= $logicalOperator . 'c.id_shop ' . $operator . $shopId;
                    }
                }
            }
        }

        if ($having_id_customer) {
            $havings[] = $having_id_customer;
        }

        $having = '';
        if (!empty($havings)) {
            $having = ' HAVING ' . implode(' AND ', $havings);
        }

        $sql = 'SELECT '
                . '     c.id_customer AS "' . $this->ll(47) . '",  '
                . '     c.firstname AS "' . $this->ll(48) . '", '
                . '     c.lastname AS "' . $this->ll(49) . '", '
                . '     c.email AS "' . $this->ll(75) . '", '
                . '     ad.phone AS "' . $this->ll(73) . '", '
                . '     ad.phone_mobile AS "' . $this->ll(74) . '" ' . $additional_select_column
                . '     , c.newsletter '
                . ' FROM ' . $from
                . ' LEFT JOIN ' . _DB_PREFIX_ . 'address AS ad '
                . '     ON ad.id_customer = c.id_customer '
                . ' ' . $join
                . ' WHERE c.deleted = 0 AND c.active = 1 '
                . $where
                . ' GROUP BY email'
                . $order_by
                . $having;

        // Pagination
        if ($limit) {
            $sql .= ' LIMIT ' . (int) $limit['start'] . ', ' . (int) $limit['length'];
        }
        return $sql;
    }

    // MySQL DB date format
    private function formatDate2($date)
    {
        if (empty($date)) {
            return '';
        }
        if (@DateTime::createFromFormat('Y-m-d', $date) !== false) {
            // it's a date
            return date('Y-m-d', strtotime($date));
        } else {
            return $date;
        }
    }

    public function getSubCategories($id_category)
    {
        $sql = 'SELECT id_category
            FROM ' . _DB_PREFIX_ . 'category
            WHERE id_parent = ' . (int) $id_category;

        $rows = (array) Db::getInstance()->executeS($sql);

        $categories = array();

        foreach ($rows as $row) {
            $categories[] = $row['id_category'];
            $categories = array_merge($categories, $this->getSubCategories($row['id_category']));
        }

        return array_unique($categories);
    }

    public function displayRuleError($id, $error) /* alias */
    {
        die('<p class="noResult">' .
                Tools::safeOutput($this->trad[81]) . ' ' . Tools::safeOutput($id) . ' : ' . Tools::safeOutput($error) .
                '</p>');
    }

    public function getName($idfield, $id)
    {
        $bind = $this->getFieldBinder($idfield);
        $bind = explode(';', $bind);
        switch ($bind[0]) {
            case 'product':
                $p = new Product($id, false, Context::getContext()->cookie->id_lang);
                return $p->name;
            case 'category':
                $c = new Category($id, Context::getContext()->cookie->id_lang);
                return $c->name;
            case 'brand':
                $m = new manufacturer($id, Context::getContext()->cookie->id_lang);
                return $m->name;
        }
        return false;
    }

    public function saveFilter($post, $auto_assign = false, $replace_customer = false)
    {
        ini_set('display_errors', 'on');

        if ($post['idfilter'] != 0) {
            $id_filter = $post['idfilter'];
            $this->deleteCondition($id_filter);

            if ($post['idgroup'] == 0) {
                Db::getInstance()->Execute(
                    'UPDATE `' . _DB_PREFIX_ . 'mj_filter` SET `name` = "' .
                    pSQL($post['name']) . '", `description` = "' .
                    pSQL($post['description']) . '" WHERE `id_filter`=' . (int) $id_filter
                );
            } else {
                $query = '
                    UPDATE `' . _DB_PREFIX_ . 'mj_filter`
                    SET
                        `name` = "' . pSQL($post['name']) . '",
                        `description` = "' . pSQL($post['description']) . '",
                        `id_group` = "' . (int) $post['idgroup'] . '",
                        `assignment_auto` = ' . (int) (bool) $auto_assign . ',
                        `replace_customer` = ' . (int) (bool) $replace_customer . '
                    WHERE `id_filter`=' . (int) $id_filter;

                Db::getInstance()->Execute($query);
            }

            /* try { */
            $segmentSynchronization = new HooksSynchronizationSegment(MailjetTemplate::getApi());
            $mailjetFiterid = $this->getMailjetContactListId($id_filter);
            $segmentSynchronization->updateName($mailjetFiterid, $id_filter, pSQL($post['name']));
            /* } catch (Exception $e) { } */
        } else {
            Db::getInstance()->Execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'mj_filter` (`name`, `description`, `date_start`,
                `date_end`, `id_group`, `assignment_auto`, `replace_customer`)
                VALUES ("' . pSQL($post['name']) . '", "' . pSQL($post['description']) . '", '.
                'NULL, NULL, "' . (int) $post['idgroup'] . '", ' .
                (int) (bool) $auto_assign . ', ' . (int) (bool) $replace_customer . ')'
            );
            $id_filter = Db::getInstance()->getValue('SELECT MAX(id_filter) FROM `' . _DB_PREFIX_ . 'mj_filter`');
        }
        $nb = count($post['fieldSelect']);

        for ($i = 0; $i < $nb; $i++) {
            Db::getInstance()->Execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'mj_condition`(`id_filter`, `id_basecondition`, `id_sourcecondition`,
                `id_fieldcondition`, `rule_a`, `rule_action`, `data`, `value1`, `value2`)
                VALUES (' . (int) $id_filter . ', ' . pSQL($post['baseSelect'][$i]) . ', ' .
                pSQL($post['sourceSelect'][$i]) . ', ' .
                pSQL($post['fieldSelect'][$i]) . ', "' . pSQL($post['rule_a'][$i]) . '", "' .
                pSQL($post['rule_action'][$i]) . '", "' .
                pSQL($post['data'][$i]) . '", "' . $this->formatDate2(pSQL($post['value1'][$i])) . '", "' .
                $this->formatDate2(pSQL($post['value2'][$i])) . '")'
            );
        }

        if ($auto_assign) {
            $auto_assign_text = $this->ll(96);

            if ($replace_customer) {
                $replace_customer_text = $this->ll(97);
            } else {
                $replace_customer_text = $this->ll(98);
            }
        } else {
            $auto_assign_text = '--';
            $replace_customer_text = '--';
        }

        if (!($group_name = $this->getGroupName((int) $post['idgroup']))) {
            $group_name = '--';
        }

        foreach ($post as &$p) {
            $p = str_replace("\\'", "'", $p);
        }
        $post['id'] = $id_filter;
        $post['replace_customer'] = $replace_customer_text;
        $post['auto_assign'] = $auto_assign_text;
        $post['group_name'] = $group_name;
        return Tools::jsonEncode($post);
    }

    public function deleteFilter($id)
    {
        $deleteFromDb = Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'mj_condition` WHERE `id_filter` =' . (int) $id) &&
            Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'mj_filter` WHERE `id_filter` =' . (int) $id);

        if ($deleteFromDb) {
            $segmentSynchronization = new HooksSynchronizationSegment(MailjetTemplate::getApi());
            $mailjetListId = $this->getMailjetContactListId($id);

            if ($mailjetListId) {
                $segmentSynchronization->deleteList($mailjetListId);
            }
        }

        return (bool) $deleteFromDb;
    }

    public function deleteCondition($id)
    {
        return Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'mj_condition` WHERE `id_filter` =' . (int) $id);
    }

    public function loadFilter($id_filter)
    {
        if ($res = Db::getInstance()->ExecuteS('SELECT c.* FROM `' . _DB_PREFIX_ . 'mj_condition` c  WHERE c.`id_filter`=' . (int) $id_filter)) {
            $i = 1;
            foreach ($res as &$r) {
                $r['getSourceSelect'] = $this->getSourceSelect($r['id_basecondition'], $i, $r['id_sourcecondition']);
                $r['getIndicSelect'] = $this->getIndicSelect($r['id_sourcecondition'], $i, $r['id_fieldcondition']);
                $i++;
            }
            return Tools::jsonEncode($res);
        }
        return false;
    }

    public function loadFilterInfo($id_filter)
    {
        $res = Db::getInstance()->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'mj_filter`  WHERE `id_filter`=' . (int) $id_filter);
        $json = Tools::jsonEncode($res);
        return '{"return" : ' . $json . '}';
    }

    public function translateOp($op)
    {
        switch (trim($op)) {
            case '+':
                return '>';
            case '+=':
            case '=+':
                return '>=';
            case '-':
                return '<';
            case '-=':
            case '=-':
                return '<=';
            case '=':
                return '=';
            default:
                return false;
        }
    }

    private function getField($ID)
    {
        return Db::getInstance()->getValue(
            'SELECT field FROM `' . _DB_PREFIX_ . 'mj_fieldcondition` WHERE id_fieldcondition = ' . (int) $ID
        );
    }

    public function getFieldLabel($ID)
    {
        return $this->trad[Db::getInstance()->getValue(
            'SELECT label FROM `' . _DB_PREFIX_ . 'mj_fieldcondition` WHERE id_fieldcondition = ' . (int) $ID
        )];
    }

    private function getFieldLabelSQL($ID)
    {
        return Db::getInstance()->getValue(
            'SELECT labelSQL FROM `' . _DB_PREFIX_ . 'mj_fieldcondition` WHERE id_fieldcondition = ' . (int) $ID
        );
    }

    public function fieldIsPrintable($ID)
    {
        return Db::getInstance()->getValue(
            'SELECT printable FROM `' . _DB_PREFIX_ . 'mj_fieldcondition` WHERE id_fieldcondition = ' . (int) $ID
        );
    }

    private function getFieldBinder($ID)
    {
        return Db::getInstance()->getValue(
            'SELECT binder FROM `' . _DB_PREFIX_ . 'mj_fieldcondition` WHERE id_fieldcondition = ' . (int) $ID
        );
    }

    private function getBase($ID)
    {
        return Db::getInstance()->getValue(
            'SELECT tablename FROM `' . _DB_PREFIX_ . 'mj_basecondition` WHERE id_basecondition = ' . (int) $ID
        );
    }

    private function getSource($ID)
    {
        return Db::getInstance()->getValue(
            'SELECT jointable FROM `' . _DB_PREFIX_ . 'mj_sourcecondition` WHERE id_sourcecondition = ' . (int) $ID
        );
    }

    public function getShopBirthdate()
    {
        return Db::getInstance()->executeS(
            'SELECT date_add FROM ' . _DB_PREFIX_ . 'mj_configuration WHERE name = "PS_LANG_DEFAULT"'
        );
    }

    public function getDomain($url)
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

    public function getDateByIdLang($date)/* , $id_lang) */
    {
        switch ((int) Context::getContext()->cookie->id_lang) {
            case 2: // fr
                $date = Tools::substr($date, 8, 2) . '-' . Tools::substr($date, 5, 2) . '-' . Tools::substr($date, 0, 4);
                break;
            default:
        }

        return $date;
    }

    public function getIdLangByIdEmployee($id_employee)
    {
        $sql = 'SELECT id_lang FROM ' . _DB_PREFIX_ . 'employee WHERE id_employee = ' . (int) $id_employee;

        return (int) DB::getInstance()->getValue($sql);
    }

    public function initLang($id_lang = 0)
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

    public function getCurrentIdLang()
    {
        if (($id_employee = (int) Tools::getValue('id_employee')) > 0) {
            $id_lang = $this->getIdLangByIdEmployee($id_employee);
        } elseif (($id_employee = (int) Context::getContext()->cookie->id_employee) > 0) {
            $id_lang = $this->getIdLangByIdEmployee($id_employee);
        } else {
            $id_lang = (int) Context::getContext()->cookie->id_lang;
        }

        return (int) $id_lang;
    }

    private function clearCacheLang()
    {
        $langs = Language::getLanguages();
        foreach ($langs as $lang) {
            if (file_exists($this->_path . '/translations/translation_cache_' . $lang['id_lang'] . '.txt')) {
                unlink($this->_path . '/translations/translation_cache_' . $lang['id_lang'] . '.txt');
            }
        }
    }

    public function cacheLang()
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

    public function belongsToGroup($id_group, $id_customer)
    {
        $sql = 'SELECT COUNT(*)
            FROM ' . _DB_PREFIX_ . 'customer_group
            WHERE id_group = ' . (int) $id_group . ' AND id_customer = ' . (int) $id_customer;

        return (bool) DB::getInstance()->getValue($sql);
    }

    public function getGroupName($id_group, $id_lang = 0)
    {
        if (!$id_lang) {
            $id_lang = (int) Context::getContext()->cookie->id_lang;
        }

        $sql = 'SELECT name
            FROM ' . _DB_PREFIX_ . 'group_lang
            WHERE id_group = ' . (int) $id_group . ' AND id_lang = ' . (int) $id_lang;

        return DB::getInstance()->getValue($sql);
    }

    /**
     *
     * @author atanas
     * @param int $filterId
     * @return int
     */
    public function getMailjetContactListId($filterId)
    {
        if (array_key_exists($filterId, $this->contactListsMap)) {
            return $this->contactListsMap[$filterId];
        }

        $api = MailjetTemplate::getApi();
        $lists = $api->getContactsLists();

        $id_list_contact = 0;
        if ($lists !== false) {
            foreach ($lists as $l) {
                $n = explode('idf', $l->Name);

                if ((string) $n[0] == (string) $filterId) {
                    $id_list_contact = (int) $l->ID;
                    $this->contactListsMap[$filterId] = $id_list_contact;
                    break;
                }
            }
        }

        return $id_list_contact;
    }
}

function validateDate($date)
{
    // Use this after some improvement and tests
    //if (preg_match("/^[0-9]{4}-(0?[1-9]|1[0-2])-(0?[1-9]|[1-2][0-9]|3[0-1])$/", $date));

    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches)) {
        $year = (int) $matches[1];
        $month = (int) $matches[2];
        $day = (int) $matches[3];
        if ($month < 1 || $month > 12) {
            return false;
        }
        if ($year < 1900 || $year > (int) date('Y')) {
            return false;
        }
        if ($day < 1 || $day > 31) {
            return false;
        }
        return true;
    }
    return false;
}

function stripReturn($txt)
{
    return preg_replace('/(\r|\n)/', '', $txt);
}
