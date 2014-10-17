<?php
include_once(realpath(dirname(__FILE__).'/../../../../../../').'/config/config.inc.php');
include_once(_PS_ROOT_DIR_.'/init.php');
header("Content-Type: application/octet-stream");
header('Content-Disposition: attachment; filename="customersegmentation'.time().'.csv');
//$tokenOK = Tools::getAdminTokenLite('AdminModules');
$tokenOK = Tools::getAdminToken('AdminModules'); // **

if (!Tools::getValue('token') && Tools::getValue('token') != $tokenOK)
	die("hack attempt");

include_once(realpath(dirname(__FILE__).'/../../..').'/segmentation.php');
$obj = new Segmentation();

$sql = Db::getInstance()->executeS($obj->getQuery($_POST, true, false));
//$sql = Db::getInstance()->executeS($obj->getQuery($_POST, true, false, ', c.`email`, c.`active`, c.`date_add` AS "'.$obj->trad[13].'", IF(c.`id_gender` =  1, "'.$obj->trad[20].'", "'.$obj->trad[21].'") AS "'.$obj->trad[12].'"'));

if (empty($sql))
	die(utf8_decode($obj->trad[22]));

$header = array_keys($sql[0]);
$csv = '';

foreach ($header as $h)
	$csv .= '"'.utf8_decode($h).'";';

$csv .= "\n";

foreach ($sql as $s)
{
	foreach ($s as $field)
		$csv .= '"'.utf8_decode($field).'";';
	$csv .= "\n";
}

echo $csv;

?>