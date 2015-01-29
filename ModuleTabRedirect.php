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

if (version_compare(_PS_VERSION_, '1.5', '<'))
	include_once(_PS_ROOT_DIR_.'/classes/AdminTab.php');

class ModuleTabRedirect extends AdminTab
{
	public function __construct()
	{
		$token = Tools::getAdminTokenLite('AdminModules');

		//$url = Dispatcher::getInstance()->createUrl('AdminModules', 1, array('token'=>$token), false);
		$url = 'index.php?controller=AdminModules&tab=AdminModules&token='.$token;

		Tools::redirectAdmin($url.'&configure=mailjet');
	}
}
