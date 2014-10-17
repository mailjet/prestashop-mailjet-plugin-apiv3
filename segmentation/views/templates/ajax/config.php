<?php
include_once(realpath(dirname(__FILE__).'/../../../../../../').'/config/config.inc.php');
include_once(_PS_ROOT_DIR_.'/init.php');

if (Tools::getValue('token') != Configuration::get('SEGMENT_CUSTOMER_TOKEN'))
	exit();

function getTable($header, $data, $id)
{
	$html = '<table border="1">
				<tr>';
	foreach ( $header as $h)
		$html .= '<td>'.$h.'</td>';
	$html .= '</tr>';
	foreach ( $data as $d)
	{
		$html .= '<tr>';
		foreach ($d as $dd)
			$html .= '<td>'.$dd.'</td>';
		$html .= '<td><a href="config.php?type=addto'.$id.'&id'.$id.'='.$d['id_'.$id.'condition'].'">Add</a></td>';
		$html .= '</tr>';
	}
	return $html;
}

function getAdd($input, $type, $hidden = false)
{
	$html = '<fieldset>
		<legend>'.$this->l('Add One').'</legend>
		<form action=# method="post">
		<input type="hidden" name="type" value="'.$type.'" />';
		if ($hidden)
			foreach ($hidden as $key => $value)
				$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
	$html .='<table>';
	
	foreach ($input as $i)
		$html .= '<tr><td>'.$i.'</td><td><textarea name="'.$i.'" ></textarea></td></tr>';
		
	$html .= '	</table>
				<input type="submit" value="ok" />
			</form>
		</fieldset>';
	return $html;
}

if (Tools::getValue('type') == 'baseadd')
{
	Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'basecondition(label, tablename) values ("'.pSQL($_POST['label']).'", "'.pSQL($_POST['tablename']).'")');
	Tools::redirect('modules/segmentmodule/config.php');
}
elseif (Tools::getValue('type') == 'sourceadd')
{
	Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'sourcecondition(id_basecondition,label, jointable) values ('.pSQL($_POST['idbase']).',"'.pSQL($_POST['label']).'", "'.pSQL($_POST['jointable']).'")');
	Tools::redirect('modules/segmentmodule/config.php?type=addtobase&idbase='.(int)$_POST['idbase']);
}
elseif (Tools::getValue('type') == 'fieldadd')
{
	Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'fieldcondition(id_sourcecondition,label, field) values ('.pSQL($_POST['idsource']).',"'.pSQL($_POST['label']).'", "'.pSQL($_POST['field']).'")');
	Tools::redirect('modules/segmentmodule/config.php?type=addtosource&idsource='.(int)$_POST['idsource']);
}
elseif (Tools::getValue('type') == 'addtosource')
{
	echo "add to :".$_GET['idsource'].' <br />';
	echo getAdd(array('label', 'field'), "fieldadd", array("idsource" => (int)$_GET['idsource']));
	echo getTable(array('id','idsource', 'label', 'field'), Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'fieldcondition'), 'field');
}
elseif (Tools::getValue('type') == 'addtobase')
{
	echo "add to :".$_GET['idbase'].' <br />';
	echo getAdd(array('label', 'jointable'), "sourceadd", array("idbase" => (int)$_GET['idbase']));
	echo getTable(array('id','idbase', 'label', 'jointable'), Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'sourcecondition'), 'source');
}
else
{
	echo getAdd(array('label', 'tablename'), "baseadd");
	echo getTable(array('id', 'label', 'tablename'), Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'basecondition'), 'base');
}

?>