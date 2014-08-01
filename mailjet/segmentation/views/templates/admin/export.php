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

/*$period_start = $_POST['date_start'];
$period_end = $_POST['date_end'];

if (strlen($period_start) == 10 || strlen($period_end) == 10)
{
	$id_lang = $obj->getCurrentIdLang();
	
	if (strlen($period_start) != 10)
		$period_start = $obj->getDateByIdLang(substr($obj->getShopBirthdate(), 0, 10), $id_lang);
		
	if (strlen($period_end) != 10)
		$period_end = $obj->getDateByIdLang(date('Y-m-d'), $id_lang);
		
	if ($period_start == $period_end)
		$title = sprintf($obj->ll(51), $period_start);
	else
		$title = sprintf($obj->ll(50), $period_start, $period_end);
		
	switch ($id_lang)
	{
		case 2:
			$title = str_replace('-', '/', $title);
			break;
		default:
	}
}
else
	$title = $obj->ll(52);

$csv .= strtoupper(utf8_decode($title)) . "\n\n";*/



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