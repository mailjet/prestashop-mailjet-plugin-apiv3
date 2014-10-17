<?php

$post = trim(Tools::file_get_contents('php://input'));
//mail("guillaume@dream-me-up.fr", "callback ajax mailjet", $post.print_r($_POST, true).print_r($_GET, true));
//die();

if (in_array($method, $back_office_method))
	define('_PS_ADMIN_DIR_', true);

require_once(realpath(dirname(__FILE__).'/../../config/config.inc.php'));

if (_PS_VERSION_ < '1.5' || !defined('_PS_ADMIN_DIR_'))
	require_once(realpath(dirname(__FILE__).'/../../init.php'));

require_once(dirname(__FILE__).'/mailjet.php');

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

die(Tools::jsonEncode($result));