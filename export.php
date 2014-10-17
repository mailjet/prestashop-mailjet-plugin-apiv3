<?php
/*
* 2007-2014 PrestaShop
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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright  2007-2014 PrestaShop SA
* @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once(dirname(__FILE__).'/mailjet.php');

$mailjet = new Mailjet();

$triggers = $mailjet->getTriggers();

if ($triggers['active']==1)
{
	if ($triggers['trigger'][1]['active']==1) // Abandon Cart Email
	{
	}
	if ($triggers['trigger'][2]['active']==1) // Payment failure recovery after canceled or blocked payment
	{
	}
	if ($triggers['trigger'][3]['active']==1) // Order pending payment
	{
	}
	if ($triggers['trigger'][4]['active']==1) // Shipment Delay Notification
	{
	}
	if ($triggers['trigger'][5]['active']==1) // Birthday promo
	{
	}
	if ($triggers['trigger'][6]['active']==1) // Purchase Anniversary promo
	{
	}
	if ($triggers['trigger'][7]['active']==1) // Customers who have not ordered since few time
	{
	}
	if ($triggers['trigger'][8]['active']==1) // Satisfaction survey
	{
	}
	if ($triggers['trigger'][9]['active']==1) // Loyalty points reminder
	{
	}
}

?>