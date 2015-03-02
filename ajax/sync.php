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

$response = false;
/* $token_ok = Tools::getAdminTokenLite('AdminModules'); */
$token_ok = Tools::getAdminToken('AdminModules');

if (!Tools::getValue('token') && Tools::getValue('token') != $token_ok)
	die('hack attempt');

if (Tools::getValue('idfilter') == 0 && Tools::getValue('action') == 'getQuery')
	die('You have to save the list first.');

include_once(_PS_MODULE_DIR_.'mailjet/mailjet.php');
/* include_once(_PS_MODULE_DIR_.'mailjet/classes/MailjetAPI.php'); */
include_once(_PS_MODULE_DIR_.'mailjet/classes/MailJetTemplate.php');
include_once(_PS_MODULE_DIR_.'mailjet/classes/hooks/synchronization/SynchronizationAbstract.php');
include_once(_PS_MODULE_DIR_.'mailjet/classes/hooks/synchronization/Segment.php');

if (Tools::getValue('action') == 'getQuery')
{
	Configuration::updateValue('MJ_PERCENTAGE_SYNC', 0);
	$obj = new Segmentation();

	$res_contacts = Db::getInstance()->executeS($obj->getQuery($_POST, true, false));

	$api = MailjetTemplate::getApi();

	$synchronization = new HooksSynchronizationSegment(
		MailjetTemplate::getApi()
	);

	$response = $synchronization->sychronize($res_contacts, Tools::getValue('idfilter'), Tools::getValue('name'));
}
else if (Tools::getValue('action') == 'getPercentage')
	$response = Configuration::get('MJ_PERCENTAGE_SYNC');

if ($response === false)
	$response = 'Error';

echo $response;
?>
