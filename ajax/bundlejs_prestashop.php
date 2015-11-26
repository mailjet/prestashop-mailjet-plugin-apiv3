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
include_once(_PS_MODULE_DIR_.'mailjet/mailjet.php');
$return = '';

if (Tools::getValue('token') != Configuration::get('SEGMENT_CUSTOMER_TOKEN'))
	exit();

if (Tools::getValue('action') == 'product')
{
	if (Tools::getValue('name') != '')
	{
		$products = Product::searchByName(Configuration::get('PS_LANG_DEFAULT'), Tools::getValue('name'));
		if ($products)
		{
			$i = 0;
			$return = '<ul id="plugproduct'.Tools::safeOutput(Tools::getValue('id')).'">';
			foreach ($products as $product)
			{
				$name = str_replace("'", '&#146;', $product['name']);
				$name = str_replace('"', '\"', $name);
				if (($i % 2) == 0)
					$return .= '<li id="'.Tools::safeOutput($product['id_product']).'" class="pair">'.Tools::safeOutput($name).'</li>';
				else
					$return .= '<li id="'.Tools::safeOutput($product['id_product']).'" class="impair">'.Tools::safeOutput($name).'</li>';
				$i++;
			}
			$return .= '</ul>';
		}
	}
	die ($return);
}
if (Tools::getValue('action') == 'productname')
{
	$obj = new Segmentation();
	$prod = new Product((int)Tools::getValue('id'), false, $obj->getCurrentIdLang());
	die ($prod->name);
}
if (Tools::getValue('action') == 'categoryname')
{
	$obj = new Segmentation();
	$cat = new Category((int)Tools::getValue('id'), $obj->getCurrentIdLang());
	die ($cat->name);
}
if (Tools::getValue('action') == 'brandname')
{
	$obj = new Segmentation();
	$man = new Manufacturer((int)Tools::getValue('id'), $obj->getCurrentIdLang());
	die ($man->name);
}
if (Tools::getValue('action') == 'category')
{
	if (Tools::getValue('name') != '')
	{
		$products = Category::searchByName(Configuration::get('PS_LANG_DEFAULT'), Tools::getValue('name'));
		if ($products)
		{
			$i = 0;
			$return = '<ul id="plugproduct'.Tools::safeOutput(Tools::getValue('id')).'">';
			foreach ($products as $product)
			{
				$name = str_replace("'", '&#146;', $product['name']);
				$name = str_replace('"', '\"', $name);
				if (($i % 2) == 0)
					$return .= '<li id="'.Tools::safeOutput($product['id_category']).'" class="pair">'.Tools::safeOutput($name).'</li>';
				else
					$return .= '<li id="'.Tools::safeOutput($product['id_category']).'" class="impair">'.Tools::safeOutput($name).'</li>';
				$i++;
			}
			$return .= '</ul>';
		}
	}
	die ($return);
}
if (Tools::getValue('action') == 'manufacturer')
{
	if (Tools::getValue('name') != '')
	{
		$manufacturers = Db::getInstance()->executeS('SELECT `id_manufacturer`, `name` FROM `'._DB_PREFIX_.'manufacturer`
														 WHERE `name` LIKE "%'.pSQL(Tools::getValue('name')).'%" ');
		if ($manufacturers)
		{
			$i = 0;
			$return = '<ul id="plugproduct'.Tools::safeOutput(Tools::getValue('id')).'">';
			foreach ($manufacturers as $manufacturer)
			{
				$name = str_replace("'", '&#146;', $manufacturer['name']);
				$name = str_replace('"', '\"', $name);
				if (($i % 2) == 0)
					$return .= '<li id="'.Tools::safeOutput($manufacturer['id_manufacturer']).'" class="pair">'.Tools::safeOutput($name).'</li>';
				else
					$return .= '<li id="'.Tools::safeOutput($manufacturer['id_manufacturer']).'" class="impair">'.Tools::safeOutput($name).'</li>';
				$i++;
			}
			$return .= '</ul>';
		}
	}
	die ($return);
}
?>