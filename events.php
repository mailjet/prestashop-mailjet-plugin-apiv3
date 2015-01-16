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

include_once(realpath(dirname(__FILE__).'/../../').'/config/config.inc.php');

include_once(_PS_MODULE_DIR_.'mailjet/classes/MailJetLog.php');
include_once(_PS_MODULE_DIR_.'mailjet/classes/MailJetTranslate.php');
include_once(_PS_MODULE_DIR_.'mailjet/classes/MailJetEvents.php');
include_once(_PS_MODULE_DIR_.'mailjet/classes/hooks/Events.php');
include_once(_PS_ROOT_DIR_.'/init.php');
require_once(_PS_MODULE_DIR_.'mailjet/mailjet.php');

$mj = new Mailjet();

if ($mj->getEventsHash() !== Tools::getValue('h'))
{
	header('HTTP/1.1 401 Unauthorized');
	return;
}

# Catch Event
$post = trim(Tools::file_get_contents('php://input'));

# No Event sent
if (empty($post))
{
	header('HTTP/1.1 421 No event');
	/* => do action */
	return;
}

# Decode Trigger Informations
$t = Tools::jsonDecode($post, true);

# No Informations sent with the Event
if (!is_array($t) || !isset($t['event']))
{
	header('HTTP/1.1 422 Not ok');
	/* => do action */
	return;
}

$events = new MailJetEvents($t['event'], $t);

/*
 *	Event handler
 *	- please check https://www.mailjet.com/docs/event_tracking for further informations.
 */
switch ($t['event'])
{
	case 'open':
		/* => do action */
		/* If an error occurs, tell Mailjet to retry later: header('HTTP/1.1 400 Error'); */
		/* If it works, tell Mailjet it's OK */
		header('HTTP/1.1 200 Ok');
		break;

	case 'click':
		/* => do action */
		break;

	case 'bounce':
		/* => do action */
		$events->add();
		break;

	case 'spam':
		/* => do action */
		$events->add();
		break;

	case 'blocked':
		/* => do action */
		$events->add();
		break;

	case 'unsub':
		/* => do action */
		$hooks_events = new HooksEvents();
		$hooks_events->unsubscribe($t);
		break;

	case 'typofix':
		/* => do action */
		$events->add();
		break;

	/* # No handler */
	default:
		header('HTTP/1.1 423 No handler');
		/* => do action */
		break;
}


?>
