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

include_once(realpath(dirname(__FILE__).'/../../../').'/config/config.inc.php');
include_once(_PS_ROOT_DIR_.'/init.php');

//header('Content-Type: application/json');

if (Tools::getValue('token') != Configuration::get('SEGMENT_CUSTOMER_TOKEN'))
	exit();

$token_ok = Tools::getAdminToken('AdminModules'.(int)Tab::getIdFromClassName('AdminModules').(int)Tools::getValue('id_employee'));

if (Tools::getValue('token') != $token_ok)
	die('hack attempt');


include_once(_PS_MODULE_DIR_.'mailjet/mailjet.php');

$obj = new segmentation();

if (Tools::getValue('action') == 'getSource')
	die($obj->getSourceSelect(Tools::getValue('baseID'), Tools::getValue('ID')));

if (Tools::getValue('action') == 'getIndic')
	die($obj->getIndicSelect(Tools::getValue('sourceID'), Tools::getValue('ID')));

if (Tools::getValue('action') == 'getBinder')
{
	switch (Tools::getValue('ID'))
	{
		case 3: /* order states */
			$orderStates = OrderState::getOrderStates($obj->getCurrentIdLang());

			$bindOrderStates = array();
			foreach ($orderStates as $orderState)
				$bindOrderStates[$orderState['id_order_state']] = $orderState['name'];

			$bind = array(
				'return'	=>	array('order'),
				'values'	=>	$bindOrderStates
			);

			die(Tools::jsonEncode($bind));
			/*break;*/
		case 4: /* payment method used */
			$res = Db::getInstance()->executeS('SELECT DISTINCT(payment) FROM '._DB_PREFIX_.'orders');

			$values = array();
			foreach ($res as $key => $value)
				$values[$value['payment']] = $value['payment'];

			$bind = array(
				'return'	=>	array('payment-method'),
				'values'	=>	$values
			);

			die(Tools::jsonEncode($bind));
			/*break;*/
		case 8: /* ca */
		case 9: /* avg ca */
			$values = array(
				1	=>	$obj->ll(53),
				2	=>	$obj->ll(54)
			);

			$bind = array(
				'return'	=>	array('ca'),
				'values'	=>	$values
			);

			die(Tools::jsonEncode($bind));
			/*break;*/
		case 19: /* newsletter subscription */
			$values = array(
				1	=>	$obj->ll(67),
				0	=>	$obj->ll(68)
			);

			$bind = array(
				'return'	=>	array('yn', 'date', 'date'),
				'values'	=>	$values
			);

			die(Tools::jsonEncode($bind));
			/*break;*/
		case 15: /* gift package */
		case 16: /* recycled packaging */
		case 20: /* newsletter optin */
		case 22: /* voucher */
		case 23: /* assets */
		case 24: /* product return */
		case 34: /* voucher */
			$values = array(
				1	=>	$obj->ll(67),
				0	=>	$obj->ll(68)
			);

			$bind = array(
				'return'	=>	array('yn'),
				'values'	=>	$values
			);

			die(Tools::jsonEncode($bind));
			/*break;*/
		case 21: /* origin	*/
			$sql = 'SELECT DISTINCT(conn.http_referer) AS url FROM '._DB_PREFIX_.'connections conn
					LEFT JOIN '._DB_PREFIX_.'guest g on g.id_guest = conn.id_guest
					LEFT JOIN '._DB_PREFIX_.'customer c ON c.id_customer = g.id_customer
					WHERE LENGTH(conn.http_referer) > 0 AND c.id_customer IS NOT NULL';
			$res = Db::getInstance()->executeS($sql);

			$values = array();
			foreach ($res as $key => $value)
			{
				$url = $obj->getDomain($value['url']);
				$values[$url] = $url;
			}
			asort($values, SORT_STRING);

			$bind = array(
				'return'	=>	array('origin'),
				'values'	=>	$values
			);

			die(Tools::jsonEncode($bind));
			/*break;*/
		case 27: /* city */
			$sql = 'SELECT DISTINCT(TRIM(a.city)) AS city FROM '._DB_PREFIX_.'address a
					LEFT JOIN '._DB_PREFIX_.'customer c ON c.id_customer = a.id_customer
					WHERE c.deleted = 0 AND a.active = 1 AND a.deleted = 0 AND a.city IS NOT NULL';
			$res = Db::getInstance()->executeS($sql);

			$values = array();
			foreach ($res as $key => $value)
				$values[$value['city']] = ucwords(mb_strtolower($value['city']));
			asort($values, SORT_STRING);

			$bind = array(
				'return'	=>	array('city'),
				'values'	=>	$values
			);

			die(Tools::jsonEncode($bind));
			/*break;*/
		default:
			$bind = $obj->getBinder(Tools::getValue('ID'));
			if ($bind != '')
			{
				$json = Tools::jsonEncode(explode(';', $bind));
				die('{"return" : '.$json.'}');
			}
	}

	die(''); /* default */
}

if (Tools::getValue('action') == 'getCountry')
{
	$context = Context::getContext();
	$rep = Country::getCountries((int)$context->language->id);
	$html = '{';
	foreach ($rep as $r)
		$html .= '"'.$r['id_country'].'" : "'.$r['name'].'",';
	$html = Tools::substr($html, 0, -1);
	$html .= '}';
	die($html);
}

if (Tools::getValue('action') == 'date')
{
	include_once(realpath(dirname(__FILE__).'/../../..').'/libraries/date.inc.php');
	$ret = array();
	switch (Tools::getValue('typedate'))
	{
		case 0 :
			$ret = get_week(Tools::getValue('periode') - 1, Tools::getValue('years'));
			break;
		case 1 :
			$ret = get_month(Tools::getValue('periode'), Tools::getValue('years'));
			break;
		case 2 :
			$ret = get_trimester(Tools::getValue('periode'), Tools::getValue('years'));
			break;
		case 3 :
			$ret['start'] = Tools::getValue('periode').'-01-01';
			$ret['end'] = Tools::getValue('periode').'-12-31';
		break;
	}
	die($ret['start'].'/'.$ret['end']);
}

if (Tools::getValue('action') == 'Save')
{
	$assignAuto = false;
	if (Tools::getIsset('assign-auto'))
		$assignAuto = (bool)Tools::getValue('assign-auto');

	$replace_customer = false;
	if (Tools::getIsset('replace-customer'))
	{
		switch (Tools::getValue('replace-customer'))
		{
			case 'rep':
				$replace_customer = true;
				break;
			case 'add':
			default:
				$replace_customer = false;
		}
	}

	die($obj->saveFilter($_POST, $assignAuto, $replace_customer));
}

if (Tools::getValue('action') == 'loadFilter')
	die($obj->loadFilter(Tools::getValue('idfilter')));

if (Tools::getValue('action') == 'loadFilterInfo')
	die($obj->loadFilterInfo(Tools::getValue('idfilter')));

if (Tools::getValue('action') == 'deleteFilter')
	die($obj->deleteFilter(Tools::getValue('idfilter')));

if (Tools::getValue('action') == 'newGroup')
{
	$grp = new Group();

	$name = array();
	$languages = Language::getLanguages(false);
	foreach ($languages as $language)
		$name[$language['id_lang']] = Tools::getValue('name');
	$grp->name = $name;
	$grp->price_display_method = 0;
	$grp->add();
	echo (int)$grp->id;
	exit();
}

if (Tools::getValue('action') == 'addGroup')
{
	$sql = $obj->getQuery($_POST, true);

	if (!$sql)
		die(false);

	$rows = Db::getInstance()->ExecuteS($sql);

	if (!is_array($rows))
		die(false);

	if (Tools::getValue('mode') == 'rep')
		foreach ($rows as $row)
			Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'customer_group WHERE id_customer = '.(int)$row[$obj->ll(47)]);

	foreach ($rows as $row)
	{
        Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'customer_group` (`id_customer`, `id_group`)
            VALUES ("'.pSQL($row[$obj->ll(47)]).'", "'.((int)Tools::getValue('idgroup')).'")');
	}

	die(true);
}

if (Tools::getValue('action') == 'getQuery')
{
	/* Determine la page en cours */
	$page = 0;
	if (Tools::getValue('page'))
		$page = (int)Tools::getValue('page') - 1;

	$reqsql = $obj->getQuery($_POST, true);

	if (!$reqsql)
		die('<p class="noResult">'.Tools::safeOutput($obj->trad[22]).'</p>');
	$req = Db::getInstance()->executeS($reqsql);
	$nb1 = count($req);
	$nb = round($nb1 / $obj->page);
	$nb = ($nb == 0) ? 1 : $nb;

	if ($nb <= $page)
		$page = 0;

		$req = Db::getInstance()->executeS($obj->getQuery($_POST, true, array('start' => ($page * $obj->page), 'length' => $obj->page)));
		$content = '<h2>'.Tools::safeOutput($obj->trad[25]).'</h2>';
		if ($req)
		{
			$header = array_keys($req[0]);

			$content .= '
					<button id="export" class="my_button right"><img src="../modules/mailjet/img/page_excel.png" />'.Tools::safeOutput($obj->trad[26]).'</button>
					<div class="right" id="seg_pagination">
						<a href="javascript:next(1, '.$nb.')"><<</a>
						<a href="javascript:next('.(Tools::getvalue('page') ? Tools::getvalue('page') - 1 : 1).', '.$nb.')"><</a>
						'.Tools::safeOutput($obj->trad[27]).' <b>'.(Tools::getvalue('page') ? Tools::getvalue('page') : 1).'</b> / '.$nb.'
						<a href="javascript:next('.(Tools::getvalue('page') ? Tools::getvalue('page') + 1 : 2).', '.$nb.')">></a>
						<a href="javascript:next('.$nb.','.$nb.')">>></a>
						<font size="3">'.$nb1.' '.Tools::safeOutput($obj->trad[25]).'</font>
					</div>
					<br />';

			$content .=	'<table cellspacing="0" cellpadding="0" class="table space">
					<tr>';
				foreach ($header as $h)
					$content .= '<th>'.Tools::safeOutput($h).'</th>';
				$content .= '
					</tr>';
			foreach ($req as $s)
			{
				$content .= '
					<tr>';
				foreach ($s as $key => $field)
				{
					if ($field == '')
						$field = '--';
					$content .= '
						<td>'.Tools::safeOutput($field).'</td>';
				}
						$content .= '
					</tr>';
			}

			$content .= '
					</table>
					<button id="export" class="my_button right"><img src="../modules/mailjet/img/page_excel.png" />'.Tools::safeOutput($obj->trad[26]).'</button>
					<div class="right" id="seg_pagination">
						<a href="javascript:next(1, '.$nb.')"><<</a>
						<a href="javascript:next('.(Tools::getvalue('page') ? Tools::getvalue('page') - 1 : 1).', '.$nb.')"><</a>
						'.Tools::safeOutput($obj->trad[27]).' <b>'.(Tools::getvalue('page') ? Tools::getvalue('page') : 1).'</b> / '.$nb.'
						<a href="javascript:next('.(Tools::getvalue('page') ? Tools::getvalue('page') + 1 : 2).', '.$nb.')">></a>
						<a href="javascript:next('.$nb.','.$nb.')">>></a>
						<font size="3">'.$nb1.' '.Tools::safeOutput($obj->trad[25]).'</font>
					</div>
					<div class="clear">&nbsp;</div>';

			if (checkTable(Tools::getValue('fieldSelect')) && ($fs = Tools::getValue('fieldSelect')) && $obj->fieldIsPrintable($fs[0]))
			{
				$stat = array();
				$nbcustomer = Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'customer`');
				$req = Db::getInstance()->executeS($reqsql);
				if (Tools::getIsset($obj->getFieldLabel($fs[0])) && Tools::getIsset($req[0][$obj->getFieldLabel($fs[0])]))
				{
					$fieldSelect_nb = count(Tools::getValue('fieldSelect'));
					for ($i = 0; $i < $fieldSelect_nb; $i++)
					{
						$val1 = $_POST['value1'][$i];
						$val2 = $_POST['value2'][$i];
						$range = true;
						$op = false;
						if ($obj->translateOp($val1) && !$obj->translateOp($val2))
						{
							$range = false;
							$op = $val1;
							$val1 = $val2;
						}
						else if (!$obj->translateOp($val1) && $obj->translateOp($val2))
							$op = $val2;

						if ($op)
							$stat[$op.' '.$val1] = getNumberCustomer($req, $val1, $val2, $op, $obj->getFieldLabel($_POST['fieldSelect'][$i]), $range);
						else
							$stat[$val1.' - '.$val2] = getNumberCustomer($req, $val1, $val2, $op, $obj->getFieldLabel($_POST['fieldSelect'][$i]), $range);
					}
				}
			}
		}
		else
			$content .= '<p class="noResult">'.Tools::safeOutput($obj->trad[22]).'</p>';

	die ($content);
}

function checkTable($table)
{
	$ref = $table[0];
	foreach ($table as $r)
		if ($r != $ref)
			return false;

	return true;
}

function getNumberCustomer($table, $val1, $val2, $op, $field, $range)
{
	$nb = 0;
	foreach ($table as $t)
	{
		if (!$range)
		{
			if (getNumber($op, $val1, $t[$field]))
				$nb++;
		}
		elseif ($t[$field] >= $val1 && $t[$field] <= $val2)
			$nb++;
	}
	return $nb;
}

function getNumber($op, $val, $field)
{
	switch (trim($op))
	{
		case '+' :
			if ($field > $val)
				return true;
		break;
		case '+=' :
		case '=+' :
			if ($field >= $val)
				return true;
		break;
		case '-' :
			if ($field < $val)
				return true;
		break;
		case '-=' :
		case '=-' :
			if ($field <= $val)
				return true;
		break;
		case '=' :
			if ($field == $val)
				return true;
		break;
		default :
			return false;
		/* break; */
	}
}

?>