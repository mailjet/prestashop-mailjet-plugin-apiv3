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

include_once(_PS_MODULE_DIR_.'mailjet/classes/hooks/synchronization/SynchronizationAbstract.php');
include_once(_PS_MODULE_DIR_.'mailjet/classes/hooks/synchronization/Initial.php');
include_once(_PS_MODULE_DIR_.'mailjet/classes/hooks/synchronization/SingleUser.php');
include_once(_PS_MODULE_DIR_.'mailjet/classes/hooks/synchronization/Segment.php');
include_once(_PS_MODULE_DIR_.'mailjet/libraries/Mailjet.Overlay.class.php');
include_once(_PS_MODULE_DIR_.'mailjet/libraries/Mailjet.Api.class.php');
include_once(_PS_MODULE_DIR_.'mailjet/classes/MailJetTemplate.php');

class Segmentation
{
	public $page;
	public $trad;

	/**
	 * @author atanas
	 */
	protected $_contactListsMap = array();

	public function __construct()
	{
		$this->name = 'segmentation';
		$this->tab = 'administration';
		$this->_path = _PS_MODULE_DIR_.'mailjet';
        
		$this->displayName = $this->l('Segment Module');
		$this->description = $this->l('Module for Customer Segmentation');
		$this->page = 10;
	}
 
    
	public function initContent()
	{
		Configuration::updateValue('SEGMENT_CUSTOMER_TOKEN', Tools::getValue('token'));

		if (version_compare(_PS_VERSION_, '1.5', '>='))
			Context::getContext()->controller->addJqueryUI('ui.datepicker');

		$this->clearCacheLang();
		$this->initLang();

		Context::getContext()->smarty->assign(array(
			'mj__PS_BASE_URI__' => __PS_BASE_URI__,
			'mj_PS_JS_DIR_' => _PS_JS_DIR_,
			'mj_MODULE_DIR_' => _MODULE_DIR_,
            'mj_hint_fieldset' => array(
                $this->l('This module enables you to create segments of customers according to any criteria you think of. You can then either display and export the selected customers or associate them to an existing customer group.', 'mailjet'),
                $this->l('These segments are particularly useful to create special offers associated with customer groups (e.g., send a coupon to the customers interested in some products)', 'mailjet'),
                $this->l('Create an infinite number of filters corresponding to your needs!', 'mailjet')),
			'mj_datePickerJsFormat' => Context::getContext()->cookie->id_lang == Language::getIdByIso('fr') ? 'dd-mm-yy' : 'yy-mm-dd',
			'mj_datepickerPersonnalized' => version_compare(_PS_VERSION_, '1.5', '<') ? '<script type="text/javascript" src="'._PS_JS_DIR_.'jquery/datepicker/jquery-ui-personalized-1.6rc4.packed.js"></script>' : '',
			'mj_token' => Tools::getValue('token'),
			'mj_ajaxFile' => _MODULE_DIR_.'mailjet/ajax/ajax.php',
			'mj_ajaxSyncFile' => _MODULE_DIR_.'mailjet/ajax/sync.php',
			'mj_ajaxBundle' => _MODULE_DIR_.'mailjet/ajax/bundlejs_prestashop.php',
			'mj_id_employee' => (int)Context::getContext()->cookie->id_employee,
			'mj_lblMan' => stripReturn($this->ll(20)),
			'mj_lblWoman' => stripReturn($this->ll(21)),
			'mj_lblUnknown' => stripReturn($this->ll(43)),
			'mj_trads' => array_map('stripReturn', $this->trad),
			'mj_groups' => Group::getGroups((int)Context::getContext()->cookie->id_lang),
			'mj_filter_list' => Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'mj_filter`'),
			'mj_base_select' => Db::getInstance()->ExecuteS('SELECT id_basecondition, label FROM `'._DB_PREFIX_.'mj_basecondition`')
		));
		
		return '';
	}

	public static function l($string, $specific = false)
	{
		$module = new Mailjet();
		if ($string == $module->l($string, $specific))
		{
			$trad_file = _PS_MODULE_DIR_.'mailjet/translations/'.Context::getContext()->language->iso_code.'.php';
			if (file_exists($trad_file))
			{
				$_MODULE = array();
				@include($trad_file);

				$key = '<{mailjet}prestashop>segmentation_'.md5(str_replace('\'', '\\\'', $string));
				/*
				if (!isset($_MODULE[$key]) && Context::getContext()->language->iso_code!='en')
				{
					$f = fopen($trad_file,"a+");
						fwrite($f, '$_MODULE[\''.$key.'\'] = \''.$string.'\';'.PHP_EOL);
						fclose($f);
				}
				*/
			}

			return (isset($_MODULE[$key])?$_MODULE[$key]:($module->l($string, $specific)));
		}
		else return $module->l($string, $specific);
	}

	public function ll($i)
	{
		if (!isset($this->trad) || empty($this->trad[0]))
			$this->initLang();
		return $this->trad[$i];
	}

	public function getSourceSelect($ID, $inputID, $selected = null)
	{
		$res = Db::getInstance()->executeS('SELECT id_sourcecondition, label FROM `'._DB_PREFIX_.'mj_sourcecondition` WHERE `id_basecondition` = '.(int)$ID);
		$html = '<select id="sourceSelect'.Tools::safeOutput($inputID).'" name="sourceSelect[]" class="sourceSelect fixed">';
		$html .= '<option value="-1">--SELECT--</option>';
		foreach ($res as $r)
		{
			$html .= '<option value="'.Tools::safeOutput($r['id_sourcecondition']).'"';
			if ($selected == $r['id_sourcecondition'])
				$html .= 'selected=selected';
			$html .= ' >'.Tools::safeOutput($this->ll($r['label'])).'</option>';
		}
		$html .= '</select>';
		return $html;
	}

	public function getIndicSelect($ID, $inputID, $selected = null)
	{
		// ID = 4 when trying to segment by multi store customers
		$query = $ID == 4 ? 'SELECT id_shop AS id_fieldcondition, name AS label FROM `'._DB_PREFIX_.'shop` WHERE active = 1 ORDER BY name' :
			'SELECT id_fieldcondition, label FROM `'._DB_PREFIX_.'mj_fieldcondition` WHERE `id_sourcecondition` = '.(int)$ID;
		$res = Db::getInstance()->ExecuteS($query);
		$html = '<select name="fieldSelect[]" class="fieldSelect fixed" id="fieldSelect'.Tools::safeOutput($inputID).'">';
		$html .= '<option value="-1">--SELECT--</option>';
		foreach ($res as $r)
		{
			/* reserved cases 30 to 40 for names of shops for multi-store segmentation @see ajax/ajax.php */
			//$addId = $ID == 4 ? 29 : 0;
				$html .= '<option value="'.Tools::safeOutput($r['id_fieldcondition']).'"';
			if ($selected == $r['id_fieldcondition'])
				$html .= 'selected=selected';
			$html .= ' >'.Tools::safeOutput($ID == 4 ? $r['label'] : $this->ll($r['label'])).'</option>';
		}
		$html .= '</select>';
		return $html;
	}

	public function getBinder($ID)
	{
		return Db::getInstance()->getValue('SELECT binder FROM `'._DB_PREFIX_.'mj_fieldcondition` WHERE `id_fieldcondition` = '.(int)$ID);
	}

	public function formatDate($post)
	{
		switch ((int)Context::getContext()->cookie->id_lang)
		{
			case 2:
				$dataToFormat = array(33);
				if (isset($post['fieldSelect']))
					foreach ($post['fieldSelect'] as $key => $value) {
						if (in_array($value, array(12, 17, 18, 19, 20, 28, 35, 36))) {
							if (Tools::strlen($post['value1'][$key]) >= 10)
								$post['value1'][$key] = Tools::substr($post['value1'][$key], 6, 4).'-'.
									Tools::substr($post['value1'][$key], 3, 2).'-'.Tools::substr($post['value1'][$key], 0, 2);

							if (Tools::strlen($post['value2'][$key]) >= 10)
								$post['value2'][$key] = Tools::substr($post['value2'][$key], 6, 4).'-'.
									Tools::substr($post['value2'][$key], 3, 2).'-'.Tools::substr($post['value2'][$key], 0, 2);
						}
						if (in_array($value, $dataToFormat)) {
							if (Tools::strlen($post['data'][$key]) >= 10) {
								$post['data'][$key] = Tools::substr($post['data'][$key], 6, 4).'-'.
									Tools::substr($post['data'][$key], 3, 2).'-'.Tools::substr($post['data'][$key], 0, 2);
							}
						}
					}
				break;
			default:
		}
		return $post;
	}

	public function getQuery($post, $live, $limit = false, $speField = '')
	{
		if (empty($post))
            $post = $_GET;

		if ($live)
		{
			$tmp = array();
			$join = '';
			$field = '';
			$labels = array(
				'(SELECT COUNT(DISTINCT(wo0.id_order)) FROM '._DB_PREFIX_.'orders wo0 WHERE wo0.id_customer = c.id_customer) AS "'.$this->ll(4).'"',
				'(SELECT COUNT(DISTINCT(wo5.id_cart)) FROM '._DB_PREFIX_.'cart wo5 WHERE wo5.id_customer = c.id_customer AND wo5.id_cart NOT IN (SELECT DISTINCT(wo6.id_cart) FROM '._DB_PREFIX_.'orders wo6 WHERE wo6.id_customer = c.id_customer)) AS "'.$this->ll(9).'"'
			);
			$joins = array(
				'LEFT JOIN '._DB_PREFIX_.'orders o ON o.id_customer = c.id_customer',
				'LEFT JOIN '._DB_PREFIX_.'currency cu ON cu.id_currency = o.id_currency',
				'LEFT JOIN '._DB_PREFIX_.'address ad ON ad.id_customer = c.id_customer'
			);
			$havings = array();
			$from = str_replace('%1', _DB_PREFIX_, $this->getBase($post['baseSelect'][0]));
			foreach ($post['sourceSelect'] as $p)
				if (!in_array($p, $tmp) && $p > 0)
				{
					$join .= str_replace('%1', _DB_PREFIX_, $this->getSource($p));
					$tmp[] = $p;
				}
			$nb = count($post['baseSelect']);
			for ($i = 0; $i < $nb; $i++)
			{
				if ($post['baseSelect'][$i] == -1)
					$this->displayRuleError($i + 1, $this->trad[85]);

				if ($post['sourceSelect'][$i] == -1)
					$this->displayRuleError($i + 1, $this->trad[86]);

				$val1 = strtotime($post['value1'][$i]) === false ? $post['value1'][$i] : $this->_formatDate($post['value1'][$i]);
				$val2 = strtotime($post['value2'][$i]) === false ? $post['value2'][$i] : $this->_formatDate($post['value2'][$i]);
				$data = $post['data'][$i];
				/*$op1 = */$this->translateOp($val1);
				/*$op2 = */$this->translateOp($val2);

				$sub_where = '';
				$sub_join = '';
				$sub_groupby = '';
				$sub_orderby = '';
				$sub_having = '';
				$sub_limit = '';
				$sub_prefix = '';
				$sub_sufix = '';

				$sub_joins = array();

				switch ($post['fieldSelect'][$i])
				{
					case '1':
						/*$sub_join = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_where = 'o'.$i.'.valid = 1';
						if (strlen($val1) > 0 && strlen($val2) > 0)
							$sub_having = 'COUNT(DISTINCT(o'.$i.'.id_order)) BETWEEN '.(float)$val1.' AND '.(float)$val2;
						elseif (strlen($val1) > 0)
							$sub_having = 'COUNT(DISTINCT(o'.$i.'.id_order)) >= '.(float)$val1;
						elseif (strlen($val2) > 0)
							$sub_having = 'COUNT(DISTINCT(o'.$i.'.id_order)) <= '.(float)$val2;
						else
							$sub_having = 'COUNT(DISTINCT(o'.$i.'.id_order)) > 0';
						$post['data'][$i] = '';
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;*/
					case '2':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$data = '';
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_having = 'COUNT(DISTINCT(o'.$i.'.id_order)) BETWEEN '.(float)$val1.' AND '.(float)$val2;
						elseif (Tools::strlen($val1) > 0)
							$sub_having = 'COUNT(DISTINCT(o'.$i.'.id_order)) >= '.(float)$val1;
						elseif (Tools::strlen($val2) > 0)
							$sub_having = 'COUNT(DISTINCT(o'.$i.'.id_order)) <= '.(float)$val2;
						else
							$sub_having = 'COUNT(DISTINCT(o'.$i.'.id_order)) > 0';
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '3':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						if ($data > 0)
							$sub_where = '(SELECT oh'.$i.'.id_order_state FROM '._DB_PREFIX_.'order_history oh'.$i.' WHERE oh'.$i.'.id_order = o'.$i.'.id_order ORDER BY oh'.$i.'.date_add DESC LIMIT 0,1) = '.(int)$data;
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '4':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						if (Tools::strlen($data) > 0)
							$sub_where = 'o'.$i.'.payment = "'.pSQL($data).'"';
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '5':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'order_detail od'.$i.' ON od'.$i.'.id_order = o'.$i.'.id_order';
						$sub_where = 'od'.$i.'.product_id = '.(int)$data;
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_having = 'SUM(od'.$i.'.product_quantity) BETWEEN '.(float)$val1.' AND '.(float)$val2;
						elseif (Tools::strlen($val1) > 0)
							$sub_having = 'SUM(od'.$i.'.product_quantity) >= '.(float)$val1;
						elseif (Tools::strlen($val2) > 0)
							$sub_having = 'SUM(od'.$i.'.product_quantity) <= '.(float)$val2;
						break;
					case '6':
						$sub_where = 'cp'.$i.'.id_category = '.(int)$data;
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'order_detail od'.$i.' ON od'.$i.'.id_order = o'.$i.'.id_order';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'category_product cp'.$i.' ON cp'.$i.'.id_product = od'.$i.'.product_id';
						$sub_groupby = 'c'.$i.'.id_customer';
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_having = 'SUM(od'.$i.'.product_quantity) BETWEEN '.(float)$val1.' AND '.(float)$val2;
						elseif (Tools::strlen($val1) > 0)
							$sub_having = 'SUM(od'.$i.'.product_quantity) >= '.(float)$val1;
						elseif (Tools::strlen($val2) > 0)
							$sub_having = 'SUM(od'.$i.'.product_quantity) <= '.(float)$val2;
						break;
					case '7':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'order_detail od'.$i.' ON od'.$i.'.id_order = o'.$i.'.id_order';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'product p'.$i.' ON p'.$i.'.id_product = od'.$i.'.product_id';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'manufacturer m'.$i.' ON m'.$i.'.id_manufacturer = p'.$i.'.id_manufacturer';
						$sub_where = 'm'.$i.'.id_manufacturer = '.(int)$data;
						$sub_groupby = 'c'.$i.'.id_customer';
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_having = 'SUM(od'.$i.'.product_quantity) BETWEEN '.(float)$val1.' AND '.(float)$val2;
						elseif (Tools::strlen($val1) > 0)
							$sub_having = 'SUM(od'.$i.'.product_quantity) >= '.(float)$val1;
						elseif (Tools::strlen($val2) > 0)
							$sub_having = 'SUM(od'.$i.'.product_quantity) <= '.(float)$val2;
						break;
					case '8':
						$sub_where = '';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'currency cu'.$i.' ON cu'.$i.'.id_currency = o'.$i.'.id_currency';
						switch ($data)
						{
							case '1': // Taxes included
								$labels[] = '(SELECT FORMAT((SUM(wo1.total_paid_real)/cu.conversion_rate), 2)
								    FROM '._DB_PREFIX_.'orders wo1 WHERE wo1.valid = 1 AND wo1.id_customer = o.id_customer)
								    AS "'.pSQL($this->ll(55)).'"';
								$sub_having_amount = 'o'.$i.'.total_paid_real';
								break;
							case '2': // Taxes excluded
							default:
								$labels[] = '(SELECT FORMAT((SUM(wo2.total_products)/cu.conversion_rate), 2)
								    FROM '._DB_PREFIX_.'orders wo2 WHERE wo2.valid = 1 AND wo2.id_customer = o.id_customer)
								    AS "'.pSQL($this->ll(56)).'"';
								$sub_having_amount = 'o'.$i.'.total_products';
						}
						$sub_groupby = 'c'.$i.'.id_customer';
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_having = 'SUM('.$sub_having_amount.'/cu'.$i.'.conversion_rate) BETWEEN '.(float)$val1.' AND '.(float)$val2;
						elseif (Tools::strlen($val1) > 0)
							$sub_having = 'SUM('.$sub_having_amount.'/cu'.$i.'.conversion_rate) >= '.(float)$val1;
						elseif (Tools::strlen($val2) > 0)
							$sub_having = 'SUM('.$sub_having_amount.'/cu'.$i.'.conversion_rate) <= '.(float)$val2;
						break;
					case '9':
						$sub_where = '';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'currency cu'.$i.' ON cu'.$i.'.id_currency = o'.$i.'.id_currency';
						switch ($data)
						{
							case '1': // Taxes included
								$labels[] = '(SELECT FORMAT((AVG(wo3.total_paid_real)/cu.conversion_rate), 2)
								    FROM '._DB_PREFIX_.'orders wo3 WHERE wo3.valid = 1 AND wo3.id_customer = o.id_customer)
								    AS "'.pSQL($this->ll(57)).'"';
								$sub_having_amount = 'o'.$i.'.total_paid_real';
								break;
							case '2': // Taxes excluded
							default:
								$labels[] = '(SELECT FORMAT((AVG(wo4.total_products)/cu.conversion_rate), 2)
								    FROM '._DB_PREFIX_.'orders wo4 WHERE wo4.valid = 1 AND wo4.id_customer = o.id_customer)
								    AS "'.pSQL($this->ll(58)).'"';
								$sub_having_amount = 'o'.$i.'.total_products';
						}
						$sub_groupby = 'c'.$i.'.id_customer';
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_having = 'AVG('.$sub_having_amount.'/cu'.$i.'.conversion_rate) BETWEEN '.(float)$val1.' AND '.(float)$val2;
						elseif (Tools::strlen($val1) > 0)
							$sub_having = 'AVG('.$sub_having_amount.'/cu'.$i.'.conversion_rate) >= '.(float)$val1;
						elseif (Tools::strlen($val2) > 0)
							$sub_having = 'AVG('.$sub_having_amount.'/cu'.$i.'.conversion_rate) <= '.(float)$val2;
						break;
					case '10':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'cart ca'.$i.' ON ca'.$i.'.id_customer = c'.$i.'.id_customer AND ca'.$i.'.id_cart NOT IN (
										SELECT DISTINCT(so'.$i.'.id_cart) FROM '._DB_PREFIX_.'orders so'.$i.' WHERE so'.$i.'.id_customer = c'.$i.'.id_customer
									)';
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_having = 'COUNT(DISTINCT(ca'.$i.'.id_cart)) BETWEEN '.(float)$val1.' AND '.(float)$val2;
						elseif (Tools::strlen($val1) > 0)
							$sub_having = 'COUNT(DISTINCT(ca'.$i.'.id_cart)) >= '.(float)$val1;
						elseif (Tools::strlen($val2) > 0)
							$sub_having = 'COUNT(DISTINCT(ca'.$i.'.id_cart)) <= '.(float)$val2;
						else
							$sub_having = 'COUNT(DISTINCT(ca'.$i.'.id_cart)) > 0';
						break;
					case '11':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_where = 'c'.$i.'.id_gender = '.(int)$data;
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '12':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_where = 'UNIX_TIMESTAMP(c'.$i.'.date_add) BETWEEN UNIX_TIMESTAMP("'.pSQL($val1).' 00:00:00") AND UNIX_TIMESTAMP("'.pSQL($val2).' 23:59:59")';
						elseif (Tools::strlen($val1) > 0)
							$sub_where = 'UNIX_TIMESTAMP(c'.$i.'.date_add) >= UNIX_TIMESTAMP("'.pSQL($val1).' 00:00:00")';
						elseif (Tools::strlen($val2) > 0)
							$sub_where = 'UNIX_TIMESTAMP(c'.$i.'.date_add) <= UNIX_TIMESTAMP("'.pSQL($val2).' 23:59:59")';
						else
							$this->displayRuleError($i + 1, $this->trad[82]);
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '13':
						$sub_where = 'a'.$i.'.id_country = '.(int)$data;
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'address a'.$i.' ON a'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					/*case '14':
						//$labels[] = 'SUM(cap.quantity) AS "'.$this->ll(45).'"';
						//$joins[] = 'LEFT JOIN '._DB_PREFIX_.'cart ca ON ca.id_customer = c.id_customer';
						//$joins[] = 'LEFT JOIN '._DB_PREFIX_.'cart_product cap ON cap.id_cart = ca.id_cart';
						$sub_where = 'cap'.$i.'.id_product = '.(int)$data;
						$sub_join = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer
									LEFT JOIN '._DB_PREFIX_.'cart ca'.$i.' ON ca'.$i.'.id_customer = c'.$i.'.id_customer AND ca'.$i.'.id_cart NOT IN (
										SELECT DISTINCT(so'.$i.'.id_cart) FROM '._DB_PREFIX_.'orders so'.$i.' WHERE so'.$i.'.id_customer = c'.$i.'.id_customer
									)
									LEFT JOIN '._DB_PREFIX_.'cart_product cap'.$i.' ON cap'.$i.'.id_cart = ca'.$i.'.id_cart';
						$sub_groupby = 'c'.$i.'.id_customer AND ca'.$i.'.id_cart, c'.$i.'.id_customer';
						if (strlen($val1) > 0 && strlen($val2) > 0)
							$sub_having = 'SUM(cap'.$i.'.quantity) BETWEEN '.(float)$val1.' AND '.(float)$val2;
						elseif (strlen($val1) > 0)
							$sub_having = 'SUM(cap'.$i.'.quantity) >= '.(float)$val1;
						elseif (strlen($val2) > 0)
							$sub_having = 'SUM(cap'.$i.'.quantity) <= '.(float)$val2;
						else
							$sub_having = 'SUM(cap'.$i.'.quantity) > 0';
						break;*/
					case '15':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_where = 'o'.$i.'.gift = '.(int)$data;
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '16':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_where = 'o'.$i.'.recyclable = '.(int)$data;
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '17':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'guest g'.$i.' ON g'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'connections conn'.$i.' ON conn'.$i.'.id_guest = g'.$i.'.id_guest';
						$sub_where = 'conn'.$i.'.date_add = (SELECT sconn'.$i.'.date_add FROM '._DB_PREFIX_.'connections sconn'.$i.' WHERE sconn'.$i.'.id_guest = g'.$i.'.id_guest ORDER BY sconn'.$i.'.date_add DESC LIMIT 0,1)';
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_where .= ' AND UNIX_TIMESTAMP(conn'.$i.'.date_add) BETWEEN UNIX_TIMESTAMP("'.pSQL($val1).' 00:00:00") AND UNIX_TIMESTAMP("'.pSQL($val2).' 23:59:59")';
						elseif (Tools::strlen($val1) > 0)
							$sub_where .= ' AND UNIX_TIMESTAMP(conn'.$i.'.date_add) >= UNIX_TIMESTAMP("'.pSQL($val1).' 00:00:00")';
						elseif (Tools::strlen($val2) > 0)
							$sub_where .= ' AND UNIX_TIMESTAMP(conn'.$i.'.date_add) <= UNIX_TIMESTAMP("'.pSQL($val2).' 23:59:59")';
						else
							$this->displayRuleError($i + 1, $this->trad[83]);
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '18':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_where = 'UNIX_TIMESTAMP(c'.$i.'.birthday) BETWEEN UNIX_TIMESTAMP("'.pSQL($val1).'") AND UNIX_TIMESTAMP("'.pSQL($val2).'")';
						elseif (Tools::strlen($val1) > 0)
							$sub_where = 'UNIX_TIMESTAMP(c'.$i.'.birthday) >= UNIX_TIMESTAMP("'.pSQL($val1).'")';
						elseif (Tools::strlen($val2) > 0)
							$sub_where = 'UNIX_TIMESTAMP(c'.$i.'.birthday) <= UNIX_TIMESTAMP("'.pSQL($val2).'")';
						else
							$this->displayRuleError($i + 1, $this->trad[84]);
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '19':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_where = 'c'.$i.'.newsletter = '.(int)$data;
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_where .= ' AND UNIX_TIMESTAMP(c'.$i.'.newsletter_date_add) BETWEEN UNIX_TIMESTAMP("'.pSQL($val1).'") AND UNIX_TIMESTAMP("'.pSQL($val2).'")';
						elseif (Tools::strlen($val1) > 0)
							$sub_where .= ' AND UNIX_TIMESTAMP(c'.$i.'.newsletter_date_add) >= UNIX_TIMESTAMP("'.pSQL($val1).'")';
						elseif (Tools::strlen($val2) > 0)
							$sub_where .= ' AND UNIX_TIMESTAMP(c'.$i.'.newsletter_date_add) <= UNIX_TIMESTAMP("'.pSQL($val2).'")';
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '20':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_where = 'c'.$i.'.optin = '.(int)$data;
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '21':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'guest g'.$i.' ON g'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'connections conn'.$i.' ON conn'.$i.'.id_guest = g'.$i.'.id_guest';
						$sub_where = 'conn'.$i.'.http_referer LIKE "%'.pSQL($data).'%"';
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '22':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'discount d'.$i.' ON d'.$i.'.id_customer = c'.$i.'.id_customer';
						if ($data > 0)
							$sub_where = 'd'.$i.'.active = '.(int)$data;
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '23':
						$sub_joins[] = 'JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						if ($data > 0)
							$sub_where = 'os'.$i.'.id_customer IS NOT NULL';
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '24':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'order_return oret'.$i.' ON oret'.$i.'.id_customer = c'.$i.'.id_customer';
						if ($data > 0)
							$sub_where = 'oret'.$i.'.id_customer IS NOT NULL';
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '25':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'address ad'.$i.' ON ad'.$i.'.id_customer = c'.$i.'.id_customer';
						if (Tools::strlen($data) > 0)
							$sub_where = '(ad'.$i.'.address1 LIKE "%'.pSQL($data).'%" OR ad'.$i.'.address2 LIKE "%'.pSQL($data).'%")';
						$sub_groupby = 'c'.$i.'.id_customer';
						break;
					case '26':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'address ad'.$i.' ON ad'.$i.'.id_customer = c'.$i.'.id_customer';
						if (Tools::strlen($data) > 0)
							$sub_where = 'ad'.$i.'.postcode LIKE "%'.pSQL($data).'%"';
						$sub_groupby = 'c'.$i.'.id_customer';
						break;
					case '27':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'address ad'.$i.' ON ad'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_where = 'ad'.$i.'.active = 1 AND ad'.$i.'.deleted = 0';
						if (Tools::strlen($data) > 0)
							$sub_where .= ' AND ad'.$i.'.city LIKE "%'.pSQL($data).'%"';
						$sub_groupby = 'c'.$i.'.id_customer';
						break;
					case '28':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_where = 'UNIX_TIMESTAMP(o'.$i.'.date_add) BETWEEN UNIX_TIMESTAMP("'.pSQL($val1).'") AND UNIX_TIMESTAMP("'.pSQL($val2).'")';
						elseif (Tools::strlen($val1) > 0)
							$sub_where = 'UNIX_TIMESTAMP(o'.$i.'.date_add) >= UNIX_TIMESTAMP("'.pSQL($val1).'")';
						elseif (Tools::strlen($val2) > 0)
							$sub_where = 'UNIX_TIMESTAMP(o'.$i.'.date_add) <= UNIX_TIMESTAMP("'.pSQL($val2).'")';
						else
							$this->displayRuleError($i + 1, $this->trad[89]);
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '29':
						$sub_where = '';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'cart ca'.$i.' ON ca'.$i.'.id_customer = c'.$i.'.id_customer AND ca'.$i.'.id_cart NOT IN (
										SELECT DISTINCT(so'.$i.'.id_cart) FROM '._DB_PREFIX_.'orders so'.$i.' WHERE so'.$i.'.id_customer = c'.$i.'.id_customer
									)';
						$sub_groupby = 'ca'.$i.'.id_cart';
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_where = 'UNIX_TIMESTAMP(ca'.$i.'.date_upd) BETWEEN UNIX_TIMESTAMP("'.pSQL($val1).'") AND UNIX_TIMESTAMP("'.pSQL($val2).'")';
						elseif (Tools::strlen($val1) > 0)
							$sub_where = 'UNIX_TIMESTAMP(ca'.$i.'.date_upd) >= UNIX_TIMESTAMP("'.pSQL($val1).'")';
						elseif (Tools::strlen($val2) > 0)
							$sub_where = 'UNIX_TIMESTAMP(ca'.$i.'.date_upd) <= UNIX_TIMESTAMP("'.pSQL($val2).'")';
						else
							$this->displayRuleError($i + 1, $this->trad[103]);
						break;
					case '30':
						$sub_where = 'cap'.$i.'.id_product = '.(int)$data;
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'cart ca'.$i.' ON ca'.$i.'.id_customer = c'.$i.'.id_customer AND ca'.$i.'.id_cart NOT IN (
										SELECT DISTINCT(so'.$i.'.id_cart) FROM '._DB_PREFIX_.'orders so'.$i.' WHERE so'.$i.'.id_customer = c'.$i.'.id_customer
									)';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'cart_product cap'.$i.' ON cap'.$i.'.id_cart = ca'.$i.'.id_cart';
						$sub_groupby = 'cap'.$i.'.id_product';
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_having = 'SUM(cap'.$i.'.quantity) BETWEEN '.(float)$val1.' AND '.(float)$val2;
						elseif (Tools::strlen($val1) > 0)
							$sub_having = 'SUM(cap'.$i.'.quantity) >= '.(float)$val1;
						elseif (Tools::strlen($val2) > 0)
							$sub_having = 'SUM(cap'.$i.'.quantity) <= '.(float)$val2;
						else
							$sub_having = 'SUM(cap'.$i.'.quantity) > 0';
						break;
					case '31':
						$sub_where = 'cp'.$i.'.id_category = '.(int)$data;
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'cart ca'.$i.' ON ca'.$i.'.id_customer = c'.$i.'.id_customer AND ca'.$i.'.id_cart NOT IN (
										SELECT DISTINCT(so'.$i.'.id_cart) FROM '._DB_PREFIX_.'orders so'.$i.' WHERE so'.$i.'.id_customer = c'.$i.'.id_customer
									)';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'cart_product cap'.$i.' ON cap'.$i.'.id_cart = ca'.$i.'.id_cart';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'category_product cp'.$i.' ON cp'.$i.'.id_product = cap'.$i.'.id_product';
						$sub_groupby = 'cap'.$i.'.id_product';
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_having = 'SUM(cap'.$i.'.quantity) BETWEEN '.(float)$val1.' AND '.(float)$val2;
						elseif (Tools::strlen($val1) > 0)
							$sub_having = 'SUM(cap'.$i.'.quantity) >= '.(float)$val1;
						elseif (Tools::strlen($val2) > 0)
							$sub_having = 'SUM(cap'.$i.'.quantity) <= '.(float)$val2;
						else
							$sub_having = 'SUM(cap'.$i.'.quantity) > 0';
						break;
					case '32':
						$sub_where = 'm'.$i.'.id_manufacturer = '.(int)$data;
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'cart ca'.$i.' ON ca'.$i.'.id_customer = c'.$i.'.id_customer AND ca'.$i.'.id_cart NOT IN (
										SELECT DISTINCT(so'.$i.'.id_cart) FROM '._DB_PREFIX_.'orders so'.$i.' WHERE so'.$i.'.id_customer = c'.$i.'.id_customer
									)';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'cart_product cap'.$i.' ON cap'.$i.'.id_cart = ca'.$i.'.id_cart';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'product p'.$i.' ON p'.$i.'.id_product = cap'.$i.'.id_product';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'manufacturer m'.$i.' ON m'.$i.'.id_manufacturer = p'.$i.'.manufacturer_id';
						$sub_groupby = 'cap'.$i.'.id_product';
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_having = 'SUM(cap'.$i.'.quantity) BETWEEN '.(float)$val1.' AND '.(float)$val2;
						elseif (Tools::strlen($val1) > 0)
							$sub_having = 'SUM(cap'.$i.'.quantity) >= '.(float)$val1;
						elseif (Tools::strlen($val2) > 0)
							$sub_having = 'SUM(cap'.$i.'.quantity) <= '.(float)$val2;
						else
							$sub_having = 'SUM(cap'.$i.'.quantity) > 0';
						break;
					case '33':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_where = 'o'.$i.'.id_order = (SELECT so'.$i.'.id_order FROM '._DB_PREFIX_.'orders so'.$i.' WHERE so'.$i.'.id_customer = c'.$i.'.id_customer ORDER BY UNIX_TIMESTAMP(so'.$i.'.date_add) DESC LIMIT 0,1)';
						if (Tools::strlen($data) > 0)
							$sub_where .= ' AND UNIX_TIMESTAMP(o'.$i.'.date_add) < UNIX_TIMESTAMP("'.pSQL($data).'")';
						else
							$this->displayRuleError($i + 1, $this->trad[93]);
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '34':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_groupby = 'c'.$i.'.id_customer';
						if ((int)$data > 0)
							$sub_where = 'o'.$i.'.id_order IN (SELECT od'.$i.'.id_order FROM '._DB_PREFIX_.'order_discount od'.$i.' WHERE od'.$i.'.id_order = o'.$i.'.id_order)';
						else
							$sub_where = 'o'.$i.'.id_order NOT IN (SELECT od'.$i.'.id_order FROM '._DB_PREFIX_.'order_discount od'.$i.' WHERE od'.$i.'.id_order = o'.$i.'.id_order)';
						/*if (strlen($val1) > 0 && strlen($val2) > 0)
							$sub_having .= ' AND od'.$i.'.value BETWEEN '.(float)$val1.' AND '.(float)$val2;
						elseif (strlen($val1) > 0)
							$sub_having .= ' AND od'.$i.'.value >= '.(float)$val1;
						elseif (strlen($val2) > 0)
							$sub_having .= ' AND od'.$i.'.value <= '.(float)$val2;*/
						break;
					case '35':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						if ((int)$data == 0)
							$this->displayRuleError($i + 1, $this->trad[95]);
						$sub_having = 'COUNT(DISTINCT(o'.$i.'.id_order)) = '.(int)$data;
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_where = 'UNIX_TIMESTAMP(o'.$i.'.date_add) BETWEEN UNIX_TIMESTAMP("'.pSQL($val1).'") AND UNIX_TIMESTAMP("'.pSQL($val2).'")';
						elseif (Tools::strlen($val1) > 0)
							$sub_where = 'UNIX_TIMESTAMP(o'.$i.'.date_add) >= UNIX_TIMESTAMP("'.pSQL($val1).'")';
						elseif (Tools::strlen($val2) > 0)
							$sub_where = 'UNIX_TIMESTAMP(o'.$i.'.date_add) <= UNIX_TIMESTAMP("'.pSQL($val2).'")';
						else
							$this->displayRuleError($i + 1, $this->trad[89]);
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					case '36':
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'orders o'.$i.' ON o'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'guest g'.$i.' ON g'.$i.'.id_customer = c'.$i.'.id_customer';
						$sub_joins[] = 'LEFT JOIN '._DB_PREFIX_.'connections conn'.$i.' ON conn'.$i.'.id_guest = g'.$i.'.id_guest';
						if (Tools::strlen($val1) > 0 && Tools::strlen($val2) > 0)
							$sub_where = 'UNIX_TIMESTAMP(conn'.$i.'.date_add) BETWEEN UNIX_TIMESTAMP("'.pSQL($val1).' 00:00:00") AND UNIX_TIMESTAMP("'.pSQL($val2).' 23:59:59")';
						elseif (Tools::strlen($val1) > 0)
							$sub_where = 'UNIX_TIMESTAMP(conn'.$i.'.date_add) >= UNIX_TIMESTAMP("'.pSQL($val1).' 00:00:00")';
						elseif (Tools::strlen($val2) > 0)
							$sub_where = 'UNIX_TIMESTAMP(conn'.$i.'.date_add) <= UNIX_TIMESTAMP("'.pSQL($val2).' 23:59:59")';
						else
							$this->displayRuleError($i + 1, $this->trad[100]);
						$sub_groupby = 'c'.$i.'.id_customer AND o'.$i.'.id_order, c'.$i.'.id_customer';
						break;
					default:
						$this->displayRuleError($i + 1, $this->trad[87]);
						/*if (!$op1 AND !$op2)
							$tmp = str_replace('%1', ' BETWEEN "'.$val1.'" AND "'.$val2.'"' ,$this->getField($post['fieldSelect'][$i]));
						else if ($op1 AND !$op2)
							$tmp = str_replace('%1', ' '.$op1.' "'.$val2.'"' ,$this->getField($post['fieldSelect'][$i]));
						else if (!$op1 AND $op2)
							$tmp = str_replace('%1', ' '.$op2.' "'.$val1.'"' ,$this->getField($post['fieldSelect'][$i]));
						if ($data != '')
							$tmp = str_replace('%2', ' = '.$data, $tmp);
						if ($tmp)
							$field .= ' '.$post['rule_a'][$i].' '.str_replace('%0', _DB_PREFIX_, $tmp);
						$default = true;*/
				}

				$sub_field = 'c'.$i.'.id_customer';
				$sub_from = _DB_PREFIX_.'customer c'.$i;

				if ($sub_where)
					$sub_where = ' AND '.$sub_where;

				switch ($post['rule_a'][$i])
				{
					case 'AND':
					case 'OR':
						$sub_base = $i;
					case '+':
						$rule_a = $post['rule_a'][$i];
						break;
					default:
						$this->displayRuleError($i + 1, $this->trad[101]);
				}

				switch ($post['rule_action'][$i])
				{
					case 'IN':
					case 'NOT IN':
						$rule_action = $post['rule_action'][$i];
						break;
					default:
						$this->displayRuleError($i + 1, $this->trad[102]);
				}

				$customer_orders = array(2, 3, 4, 5, 6, 7, 8, 9, 15, 16, 28, 34, 35);
				$customer_lostcarts = array(10, 29, 30, 31, 32);

				$fieldSelect_count = count($post['fieldSelect']);
				for ($j = $sub_base; $j < $fieldSelect_count; $j++)
				{
					if (($j == $sub_base && isset($post['rule_a'][$j + 1]) && $post['rule_a'][$j + 1] != '+') || ($j > $sub_base && $post['rule_a'][$j] != '+'))
						break;

					if ($post['fieldSelect'][$j] == 3 && $post['fieldSelect'][$i] != $post['fieldSelect'][$j] && in_array($post['fieldSelect'][$i], $customer_orders)) // order state (implicit case)
					{
						if ((int)$post['data'][$j] > 0)
							$sub_where .= ' AND (SELECT soh'.$i.'.id_order_state FROM '._DB_PREFIX_.'order_history soh'.$i.' WHERE soh'.$i.'.id_order = o'.$i.'.id_order ORDER BY soh'.$i.'.date_add DESC LIMIT 0,1) = '.(int)$post['data'][$j];
					}

					if ($post['fieldSelect'][$j] == 4 && $post['fieldSelect'][$i] != $post['fieldSelect'][$j] && in_array($post['fieldSelect'][$i], $customer_orders)) // order payment method (implicit case)
					{
						if (Tools::strlen($post['data'][$j]) > 0)
							$sub_where .= ' AND o'.$i.'.payment = "'.pSQL($post['data'][$j]).'"';
					}

					if ($post['fieldSelect'][$j] == 15 && $post['fieldSelect'][$i] != $post['fieldSelect'][$j] && in_array($post['fieldSelect'][$i], $customer_orders)) // order gift (implicit case)
					{
						if ((int)$post['data'][$j] > 0)
							$sub_where .= ' AND o'.$i.'.gift = "'.pSQL($post['data'][$j]).'"';
					}

					if ($post['fieldSelect'][$j] == 16 && $post['fieldSelect'][$i] != $post['fieldSelect'][$j] && in_array($post['fieldSelect'][$i], $customer_orders)) // order recycled package (implicit case)
					{
						if ((int)$post['data'][$j] > 0)
							$sub_where .= ' AND o'.$i.'.recyclable = "'.pSQL($post['data'][$j]).'"';
					}

					if ($post['fieldSelect'][$j] == 34 && $post['fieldSelect'][$i] != $post['fieldSelect'][$j] && in_array($post['fieldSelect'][$i], $customer_orders)) // order voucher (implicit case)
					{
						if ((int)(int)$post['data'][$j] > 0)
							$sub_where = ' AND o'.$i.'.id_order IN (SELECT sod'.$i.'.id_order FROM '._DB_PREFIX_.'order_discount sod'.$i.' WHERE sod'.$i.'.id_order = o'.$i.'.id_order)';
						else
							$sub_where = ' AND o'.$i.'.id_order NOT IN (SELECT sod'.$i.'.id_order FROM '._DB_PREFIX_.'order_discount sod'.$i.' WHERE sod'.$i.'.id_order = o'.$i.'.id_order)';
					}

					if ($post['fieldSelect'][$j] == 28 && $post['fieldSelect'][$i] != $post['fieldSelect'][$j] && in_array($post['fieldSelect'][$i], $customer_orders)) // order date (implicit case)
					{
						if (Tools::strlen($post['value1'][$j]) > 0 && Tools::strlen($post['value2'][$j]) > 0)
							$sub_where_and = 'UNIX_TIMESTAMP(o'.$i.'.date_add) BETWEEN UNIX_TIMESTAMP("'.pSQL($post['value1'][$j]).'") AND UNIX_TIMESTAMP("'.pSQL($post['value2'][$j]).'")';
						elseif (Tools::strlen($post['value1'][$j]) > 0)
							$sub_where_and = 'UNIX_TIMESTAMP(o'.$i.'.date_add) >= UNIX_TIMESTAMP("'.pSQL($post['value1'][$j]).'")';
						elseif (Tools::strlen($post['value2'][$j]) > 0)
							$sub_where_and = 'UNIX_TIMESTAMP(o'.$i.'.date_add) <= UNIX_TIMESTAMP("'.pSQL($post['value2'][$j]).'")';
						else
							$this->displayRuleError($j + 1, $this->trad[89]);

						$sub_where .= ' AND '.$sub_where_and;
					}

					if ($post['fieldSelect'][$j] == 29 && $post['fieldSelect'][$i] != $post['fieldSelect'][$j] && in_array($post['fieldSelect'][$i], $customer_lostcarts)) // lost cart date (implicit case)
					{
						if (Tools::strlen($post['value1'][$j]) > 0 && Tools::strlen($post['value2'][$j]) > 0)
							$sub_where_and = 'UNIX_TIMESTAMP(ca'.$i.'.date_upd) BETWEEN UNIX_TIMESTAMP("'.pSQL($post['value1'][$j]).'") AND UNIX_TIMESTAMP("'.pSQL($post['value2'][$j]).'")';
						elseif (Tools::strlen($post['value1'][$j]) > 0)
							$sub_where_and = 'UNIX_TIMESTAMP(ca'.$i.'.date_upd) >= UNIX_TIMESTAMP("'.pSQL($post['value1'][$j]).'")';
						elseif (Tools::strlen($post['value2'][$j]) > 0)
							$sub_where_and = 'UNIX_TIMESTAMP(ca'.$i.'.date_upd) <= UNIX_TIMESTAMP("'.pSQL($post['value2'][$j]).'")';
						else
							$this->displayRuleError($j + 1, $this->trad[103]);

						$sub_where .= ' AND '.$sub_where_and;
					}
				}

				if ($sub_groupby)
					$sub_groupby = ' GROUP BY '.$sub_groupby;

				if ($sub_orderby)
					$sub_orderby = ' ORDER BY '.$sub_orderby;

				if ($sub_having)
					$sub_having = ' HAVING '.$sub_having;

				if ($sub_limit)
					$sub_limit = ' LIMIT '.$sub_limit;

				switch ($rule_a)
				{
					case '+':
						if (!isset($post['rule_a'][$i + 1]) || $post['rule_a'][$i + 1] != '+')
							$sub_sufix = ')';

						$rule_a = 'AND';
						break;

					case 'AND':

					case 'OR':
						if (isset($post['rule_a'][$i + 1]) && $post['rule_a'][$i + 1] == '+')
							$sub_prefix = '(';
						break;

					default:
						$this->displayRuleError($i + 1, $this->trad[101]);
				}

				if (!empty($sub_joins))
				{
					$sub_join = '';
					$sub_joins = array_unique($sub_joins);
					foreach ($sub_joins as $value)
						$sub_join .= ' '.$value;
				}

				$field .= ' '.$rule_a.' '.$sub_prefix.'c.id_customer '.$rule_action.' (SELECT '.$sub_field.' FROM '.
					$sub_from.' '.$sub_join.' WHERE c'.$i.'.deleted = 0'.
					$sub_where.$sub_groupby.$sub_orderby.$sub_having.$sub_limit.')'.$sub_sufix;
			}
		}

		if (!empty($labels))
		{
			$labels = array_unique($labels);
			$label = '';
			foreach ($labels as $value)
			{
				if (Tools::strlen($label) == 0)
					$label = $value;
				else
					$label .= ', '.$value;
			}
		}
		else
		{
			for ($i = 0; $i < $nb; $i++)
			{
				$data = pSQL($post['data'][$i]);
				$val1 = pSQL($post['value1'][$i]);
				$val2 = pSQL($post['value2'][$i]);
				$p = pSQL($post['fieldSelect'][$i]);

				if ($this->fieldIsPrintable($p))
				{
					$name = $this->getName($p, $data);
					$lab = ($name) ? $name : $this->getFieldLabel($p);

					$changeToQuantity = array(5, 6, 7);
					if (in_array($p, $changeToQuantity))
						$lab = $this->ll(45);

					if (!isset($label) && $data != '' && $val1 == '' && $val2 == '')
						$label = str_replace('%2', ' AS "'.$lab.'" ,', $this->getFieldLabelSQL($p));
					else
						$label = str_replace('%1', ' AS "'.$lab.'" ,', $this->getFieldLabelSQL($p));
				}
			}

			if (trim($field) == 'AND')
				return false;
			$label = str_replace('%0', _DB_PREFIX_, $label);
			$label = Tools::substr(trim($label), 0, -1);
		}

		if (!empty($joins))
		{
			$join = '';
			$joins = array_unique($joins);
			foreach ($joins as $value)
				$join .= ' '.$value;
		}

		$having = '';
		if (!empty($havings))
		{
			$havings = array_unique($havings);
			foreach ($havings as $value)
			{
				if (Tools::strlen($having) > 0)
					$having .= ' AND '.$value;
				else
					$having = $value;
			}
		}


			if($post['sourceSelect'][0] == 4){
				$multistoreWhere = ' AND c.id_shop = ' . (int)$post['fieldSelect'][0];
				$field = '';
			} else {
				$multistoreWhere = '';
			}
		$select = 'SELECT DISTINCT(c.id_customer) AS "'.$this->ll(47).'", CONCAT(UPPER(LEFT(c.firstname, 1)),
			LOWER(SUBSTRING(c.firstname FROM 2))) AS "'.$this->ll(48).'", UPPER(c.lastname) AS "'.$this->ll(49).'",
			LOWER(c.email) AS "'.$this->ll(75).'", ad.phone AS "'.$this->ll(73).'",
			ad.phone_mobile AS "'.$this->ll(74).'"'.$speField.' '.($label != '' ? ', '.$label : ' ').'
			FROM '.$from.' '.$join.'
			WHERE c.deleted = 0 AND (ad.active = 1 OR ad.active IS NULL)
			AND (ad.deleted = 0 OR ad.deleted IS NULL)'.$field.$multistoreWhere;

		$select .= ' GROUP BY c.id_customer AND o.id_order, c.id_customer';

		if ($having)
			$select .= ' HAVING '.$having;

		if ($limit)
			$select .= ' LIMIT '.(int)$limit['start'].', '.(int)$limit['length'];

		return $select;
	}

	// MySQL DB date format
	private function _formatDate($date){
        if(empty($date)) {
            return '';
            
        }
        if (@DateTime::createFromFormat('Y-m-d', $date) !== FALSE) {
            // it's a date
            return date('Y-m-d',strtotime($date));
        } else return $date;
		
	}

	public function getSubCategories($id_category)
	{
		$sql = 'SELECT id_category
				FROM '._DB_PREFIX_.'category 
				WHERE id_parent = '.(int)$id_category;

		$rows = (array)Db::getInstance()->executeS($sql);

		$categories = array();

		foreach ($rows as $row)
		{
			$categories[] = $row['id_category'];
			$categories = array_merge($categories, $this->getSubCategories($row['id_category']));
		}

		return array_unique($categories);
	}

	public function displayRuleError($id, $error) /* alias */
	{
		die('<p class="noResult">'.Tools::safeOutput($this->trad[81]).' '.Tools::safeOutput($id).' : '.Tools::safeOutput($error).'</p>');
	}

	public function getName($idfield, $id)
	{
		$bind = $this->getFieldBinder($idfield);
		$bind = explode(';', $bind);
		switch ($bind[0])
		{
			case 'product' :
				$p = new Product($id, false, Context::getContext()->cookie->id_lang);
				return $p->name;
			case 'category' :
				$c = new Category($id, Context::getContext()->cookie->id_lang);
				return $c->name;
			case 'brand' :
				$m = new manufacturer($id, Context::getContext()->cookie->id_lang);
				return $m->name;
		}
		return false;
	}

	public function saveFilter($post, $auto_assign = false, $replace_customer = false)
	{
		ini_set('display_errors', 'on');

		if ($post['idfilter'] != 0)
		{
			$id_filter = $post['idfilter'];
			$this->deleteCondition($id_filter);

			if ($post['idgroup'] == 0)
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'mj_filter` SET `name` = "'.pSQL($post['name']).'", `description` = "'.pSQL($post['description']).'" WHERE `id_filter`='.(int)$id_filter);
			else
			{
				$query = '
					UPDATE `'._DB_PREFIX_.'mj_filter`
					SET
						`name` = "'.pSQL($post['name']).'",
						`description` = "'.pSQL($post['description']).'",
						`id_group` = "'.(int)$post['idgroup'].'",
						`assignment_auto` = '.(int)(bool)$auto_assign.',
						`replace_customer` = '.(int)(bool)$replace_customer.'
					WHERE `id_filter`='.(int)$id_filter;

				Db::getInstance()->Execute($query);
			}

			/* try { */
				$segmentSynchronization = new HooksSynchronizationSegment(
						MailjetTemplate::getApi()
				);
				$mailjetFiterid = $this->_getMailjetContactListId($id_filter);
				$segmentSynchronization->updateName($mailjetFiterid, $id_filter, pSQL($post['name']));
			/* } catch (Exception $e) { } */
		}
		else
		{
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'mj_filter` (`name`, `description`, `date_start`, `date_end`, `id_group`, `assignment_auto`, `replace_customer`) 
						VALUES ("'.pSQL($post['name']).'", "'.pSQL($post['description']).'", NULL, NULL, "'.(int)$post['idgroup'].'", '.(int)(bool)$auto_assign.', '.(int)(bool)$replace_customer.')');
			$id_filter = Db::getInstance()->getValue('SELECT MAX(id_filter) FROM `'._DB_PREFIX_.'mj_filter`');
		}
		$nb = count($post['fieldSelect']);

		for ($i = 0; $i < $nb; $i++) {
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'mj_condition`(`id_filter`, `id_basecondition`, `id_sourcecondition`, `id_fieldcondition`, `rule_a`, `rule_action`, `data`, `value1`, `value2`)
					VALUES ('.(int)$id_filter.', '.pSQL($post['baseSelect'][$i]).', '.pSQL($post['sourceSelect'][$i]).', '.
                pSQL($post['fieldSelect'][$i]).', "'.pSQL($post['rule_a'][$i]).'", "'.pSQL($post['rule_action'][$i]).'", "'.
                pSQL($post['data'][$i]).'", "'.$this->_formatDate(pSQL($post['value1'][$i])).'", "'.
                $this->_formatDate(pSQL($post['value2'][$i])).'")');
        }

		if ($auto_assign)
		{
			$auto_assign_text = $this->ll(96);

			if ($replace_customer)
				$replace_customer_text = $this->ll(97);
			else
				$replace_customer_text = $this->ll(98);
		}
		else
		{
			$auto_assign_text = '--';
			$replace_customer_text = '--';
		}

		if (!($group_name = $this->getGroupName((int)$post['idgroup'])))
				$group_name = '--';

		/* ** ** */
		foreach ($post as &$p)
			$p = str_replace("\\'", "'", $p);
		$post['id'] = $id_filter;
		$post['replace_customer'] = $replace_customer_text;
		$post['auto_assign'] = $auto_assign_text;
		$post['group_name'] = $group_name;
		return Tools::jsonEncode($post);
		/* ** ** */
		
		// return '{"id" : '.$id_filter.',"name" : "'.pSQL($post['name']).'", "description" : "'.pSQL($post['description']).'", "replace_customer" : "'.$replace_customer_text.'", "auto_assign" : "'.$auto_assign_text.'", "group_name" : "'.$group_name.'"}';
	}

	public function deleteFilter($id)
	{
		$deleteFromDb = Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'mj_condition` WHERE `id_filter` ='.(int)$id) && Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'mj_filter` WHERE `id_filter` ='.(int)$id);

		if ($deleteFromDb)
		{
			/* try { */
				$segmentSynchronization = new HooksSynchronizationSegment( MailjetTemplate::getApi() );
				$mailjetListId = $this->_getMailjetContactListId($id);

				if ($mailjetListId)
					$segmentSynchronization->deleteList($mailjetListId);
			/* } catch (Exception $e) { } */
		}

		return (bool)$deleteFromDb;
	}

	public function deleteCondition($id)
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'mj_condition` WHERE `id_filter` ='.(int)$id);
	}

	public function loadFilter($id_filter)
	{
		if ($res = Db::getInstance()->ExecuteS('SELECT c.* FROM `'._DB_PREFIX_.'mj_condition` c  WHERE c.`id_filter`='.(int)$id_filter))
		{
			$i = 1;
			foreach ($res as &$r)
			{
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
		$res = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'mj_filter`  WHERE `id_filter`='.(int)$id_filter);
		$json = Tools::jsonEncode($res);
		return '{"return" : '.$json.'}';
	}

	public function translateOp($op)
	{
		switch (trim($op))
		{
			case '+' :
				return '>';
			case '+=' :
			case '=+':
				return '>=';
			case '-' :
				return '<';
			case '-=' :
			case '=-' :
				return '<=';
			case '=' :
				return '=';
			default :
				return false;
		}
	}

	private function getField($ID)
	{
		return Db::getInstance()->getValue('SELECT field FROM `'._DB_PREFIX_.'mj_fieldcondition` WHERE id_fieldcondition = '.(int)$ID);
	}

	public function getFieldLabel($ID)
	{
		return $this->trad[Db::getInstance()->getValue('SELECT label FROM `'._DB_PREFIX_.'mj_fieldcondition` WHERE id_fieldcondition = '.(int)$ID)];
	}

	private function getFieldLabelSQL($ID)
	{
		return Db::getInstance()->getValue('SELECT labelSQL FROM `'._DB_PREFIX_.'mj_fieldcondition` WHERE id_fieldcondition = '.(int)$ID);
	}

	public function fieldIsPrintable($ID)
	{
		return Db::getInstance()->getValue('SELECT printable FROM `'._DB_PREFIX_.'mj_fieldcondition` WHERE id_fieldcondition = '.(int)$ID);
	}

	private function getFieldBinder($ID)
	{
		return Db::getInstance()->getValue('SELECT binder FROM `'._DB_PREFIX_.'mj_fieldcondition` WHERE id_fieldcondition = '.(int)$ID);
	}

	private function getBase($ID)
	{
		return Db::getInstance()->getValue('SELECT tablename FROM `'._DB_PREFIX_.'mj_basecondition` WHERE id_basecondition = '.(int)$ID);
	}
	private function getSource($ID)
	{
		return Db::getInstance()->getValue('SELECT jointable FROM `'._DB_PREFIX_.'mj_sourcecondition` WHERE id_sourcecondition = '.(int)$ID);
	}

	public function getShopBirthdate()
	{
		return Db::getInstance()->executeS('SELECT date_add FROM '._DB_PREFIX_.'mj_configuration WHERE name = "PS_LANG_DEFAULT"');
	}

	public function getDomain($url)
	{
		$url = parse_url($url);

		if (!isset($url['host']))
			return '';

		if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $url['host'], $result))
			return $result['domain'];

		return $url['host'];
	}

	public function getDateByIdLang($date)/*, $id_lang) */
	{
		switch ((int)Context::getContext()->cookie->id_lang)
		{
			case 2: // fr
				$date = Tools::substr($date, 8, 2).'-'.Tools::substr($date, 5, 2).'-'.Tools::substr($date, 0, 4);
				break;
			default:
		}

		return $date;
	}

	public function getIdLangByIdEmployee($id_employee)
	{
		$sql = 'SELECT id_lang 
				FROM '._DB_PREFIX_.'employee 
				WHERE id_employee = '.(int)$id_employee;

		return (int)DB::getInstance()->getValue($sql);
	}

	public function initLang($id_lang = 0)
	{
		if (!$id_lang)
			$id_lang = $this->getCurrentIdLang();

		if (file_exists($this->_path.'/translations/translation_cache_'.(int)$id_lang.'.txt'))
			$this->trad = Tools::jsonDecode(Tools::file_get_contents($this->_path.'/translations/translation_cache_'.(int)$id_lang.'.txt'));
		else
		{
			$this->cacheLang();
			$tmp_create = $this->_path.'/translations/translation_create_'.(int)$id_lang.'.txt';
			if (file_exists($tmp_create))
			{
				$fp = fopen($tmp_create, 'r');
				$trad = array();
				while (($buffer = fgets($fp, 4096)) !== false)
					$trad[] = $buffer;
				fclose($fp);
				$this->trad = $trad;
			}
			else
			{
				$fp = fopen($tmp_create, 'w+');
				foreach ($this->trad as $trad)
					fwrite($fp, $trad."\r\n");
				fclose($fp);
			}
			file_put_contents($this->_path.'/translations/translation_cache_'.(int)$id_lang.'.txt', Tools::jsonEncode($this->trad));
		}
	}

	public function getCurrentIdLang()
	{
		if (($id_employee = (int)Tools::getValue('id_employee')) > 0)
			$id_lang = $this->getIdLangByIdEmployee($id_employee);
		else if (($id_employee = (int)Context::getContext()->cookie->id_employee) > 0)
			$id_lang = $this->getIdLangByIdEmployee($id_employee);
		else
			$id_lang = (int)Context::getContext()->cookie->id_lang;

		return (int)$id_lang;
	}

	private function clearCacheLang()
	{
		$langs = Language::getLanguages();
		foreach ($langs as $lang)
			if (file_exists($this->_path.'/translations/translation_cache_'.$lang['id_lang'].'.txt'))
				unlink($this->_path.'/translations/translation_cache_'.$lang['id_lang'].'.txt');
	}

	public function cacheLang() {
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
		);
	}

	public function belongsToGroup($id_group, $id_customer)
	{
		$sql = 'SELECT COUNT(*) 
				FROM '._DB_PREFIX_.'customer_group 
				WHERE id_group = '.(int)$id_group.' AND id_customer = '.(int)$id_customer;

		return (bool)DB::getInstance()->getValue($sql);
	}

	public function getGroupName($id_group, $id_lang = 0)
	{
		if (!$id_lang)
			$id_lang = (int)Context::getContext()->cookie->id_lang;

		$sql = 'SELECT name 
				FROM '._DB_PREFIX_.'group_lang 
				WHERE id_group = '.(int)$id_group.' AND id_lang = '.(int)$id_lang;

		return DB::getInstance()->getValue($sql);
	}

	/**
	 * 
	 * @author atanas
	 * @param int $filterId
	 * @return int
	 */
	public function _getMailjetContactListId($filterId)
	{
		if (array_key_exists($filterId, $this->_contactListsMap))
			return $this->_contactListsMap[$filterId];

		$api = MailjetTemplate::getApi();
			$lists = $api->getContactsLists();

		$id_list_contact = 0;
		if ($lists !== false)
		{
			foreach ($lists as $l)
			{
				$n = explode('idf', $l->Name);

				if ((string)$n[0] == (string)$filterId)
				{
					$id_list_contact = (int)$l->ID;
					$this->_contactListsMap[$filterId] = $id_list_contact;
					break;
				}
			}
		}

		return $id_list_contact;
	}
}

function stripReturn($txt)
{
	return preg_replace('/(\r|\n)/', '', $txt);
}
