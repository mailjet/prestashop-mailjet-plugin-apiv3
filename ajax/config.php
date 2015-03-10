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

include_once(realpath(dirname(__FILE__).'/../../../../../').'/config/config.inc.php');
include_once(_PS_ROOT_DIR_.'/init.php');

if (Tools::getValue('token') != Configuration::get('SEGMENT_CUSTOMER_TOKEN'))
	exit();

function getTable($header, $data, $id)
{
	$html = '<table border="1">
				<tr>';
	foreach ($header as $h)
		$html .= '<td>'.Tools::safeOutput($h).'</td>';
	$html .= '</tr>';
	foreach ($data as $d)
	{
		$html .= '<tr>';
		foreach ($d as $dd)
			$html .= '<td>'.Tools::safeOutput($dd).'</td>';
		$html .= '<td><a href="config.php?type=addto'.Tools::safeOutput($id).'&id'.Tools::safeOutput($id).'='.Tools::safeOutput($d['id_'.$id.'condition']).'">Add</a></td>';
		$html .= '</tr>';
	}
	return $html;
}

function getAdd($input, $type, $hidden = false)
{
	$html = '<fieldset>
		<legend>Add One</legend>
		<form action=# method="post">
		<input type="hidden" name="type" value="'.Tools::safeOutput($type).'" />';
		if ($hidden)
			foreach ($hidden as $key => $value)
				$html .= '<input type="hidden" name="'.Tools::safeOutput($key).'" value="'.Tools::safeOutput($value).'" />';
	$html .= '<table>';

	foreach ($input as $i)
		$html .= '<tr><td>'.Tools::safeOutput($i).'</td><td><textarea name="'.Tools::safeOutput($i).'" ></textarea></td></tr>';

	$html .= '	</table>
				<input type="submit" value="ok" />
			</form>
		</fieldset>';
	return $html;
}

if (Tools::getValue('type') == 'baseadd')
{
	Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'basecondition(label, tablename)
		values ("'.pSQL(Tools::getValue('label')).'", "'.pSQL(Tools::getValue('tablename')).'")');
	Tools::redirect('modules/segmentmodule/config.php');
}
elseif (Tools::getValue('type') == 'sourceadd')
{
	Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'sourcecondition(id_basecondition,label, jointable)
		values ("'.pSQL(Tools::getValue('idbase')).'","'.pSQL(Tools::getValue('label')).'", "'.pSQL(Tools::getValue('jointable')).'")');
	Tools::redirect('modules/segmentmodule/config.php?type=addtobase&idbase='.(int)Tools::getValue('idbase'));
}
elseif (Tools::getValue('type') == 'fieldadd')
{
	Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'fieldcondition(id_sourcecondition,label, field)
		values ("'.pSQL(Tools::getValue('idsource')).'","'.pSQL(Tools::getValue('label')).'", "'.pSQL(Tools::getValue('field')).'")');
	Tools::redirect('modules/segmentmodule/config.php?type=addtosource&idsource='.(int)Tools::getValue('idsource'));
}
elseif (Tools::getValue('type') == 'addtosource')
{
	echo 'add to :'.Tools::getValue('idsource').' <br />';
	echo getAdd(array('label', 'field'), 'fieldadd', array('idsource' => (int)Tools::getValue('idsource')));
	echo getTable(array('id','idsource', 'label', 'field'), Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'fieldcondition'), 'field');
}
elseif (Tools::getValue('type') == 'addtobase')
{
	echo 'add to :'.Tools::getValue('idbase').' <br />';
	echo getAdd(array('label', 'jointable'), 'sourceadd', array('idbase' => (int)Tools::getValue('idbase')));
	echo getTable(array('id','idbase', 'label', 'jointable'), Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'sourcecondition'), 'source');
}
else
{
	echo getAdd(array('label', 'tablename'), 'baseadd');
	echo getTable(array('id', 'label', 'tablename'), Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'basecondition'), 'base');
}

?>