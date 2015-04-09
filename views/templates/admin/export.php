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
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="customersegmentation'.time().'.csv');
$token_ok = Tools::getAdminToken('AdminModules');

if (!Tools::getValue('token') && Tools::getValue('token') != $token_ok)
	die('hack attempt');

include_once(_PS_MODULE_DIR_.'mailjet/mailjet.php');

$obj = new Segmentation();

$sql = Db::getInstance()->executeS($obj->getQuery($_POST, true, false));

if (empty($sql))
	die(utf8_decode($obj->trad[22]));

$header = array_keys($sql[0]);
$csv = '';

foreach ($header as $h)
	$csv .= '"'.preg_replace('/(\r|\n)/', '', utf8_decode($h)).'";';

$csv .= "\n";

foreach ($sql as $s)
{
	foreach ($s as $field)
		$csv .= '"'.utf8_decode($field).'";';
	$csv .= "\n";
}

echo $csv;

?>