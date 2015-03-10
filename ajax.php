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

require_once(realpath(dirname(__FILE__).'/../../config/config.inc.php'));
if (_PS_VERSION_ < '1.5' || !defined('_PS_ADMIN_DIR_'))
	require_once(realpath(dirname(__FILE__).'/../../init.php'));


if (Tools::getIsset('emptyfile'))
{
	header('Content-Type: application/force-download; name="'.Tools::getValue('name').'"');
	header('Content-Transfer-Encoding: binary');
	header('Content-Length: 0');
	header('Content-Disposition: attachment; filename="'.Tools::getValue('name').'"');
	header('Expires: 0');
	header('Cache-Control: no-cache, must-revalidate');
	header('Pragma: no-cache');
	exit();
}

$post = trim(Tools::file_get_contents('php://input'));
/* mail("guillaume@dream-me-up.fr", "callback ajax mailjet", $post.print_r($_POST, true).print_r($_GET, true)); */
/* die(); */

require_once(_PS_ROOT_DIR_.'/config/config.inc.php');

$method = Tools::getValue('method');
$back_office_method = Tools::getValue('back_office_method');

if (in_array($method, $back_office_method))
	define('_PS_ADMIN_DIR_', true);

require_once(_PS_MODULE_DIR_.'mailjet/mailjet.php');

$method = Tools::isSubmit('method') ? Tools::getValue('method') : '';
$token = Tools::isSubmit('token') ? Tools::getValue('token') : '';

$mj = new Mailjet();
$result = array();

MailJetLog::write(MailJetLog::$file, 'New request sent');

if ($mj->getToken() != Tools::getValue('token'))
	$result['error'] = $mj->l('Bad token sent');
else if (!method_exists($mj, $method))
	$result['error'] = $mj->l('Method requested doesn\'t exist:').' '.$method;
else
	$result = $mj->{$method}();

$message = isset($result['error']) ? $result['error'] : 'Success with method: '.$method;
MailJetLog::write(MailJetLog::$file, $message);

header('Content-Type: application/json');
die(Tools::jsonEncode($result));