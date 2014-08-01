<?php
include_once(realpath(dirname(__FILE__).'/../../../../../../').'/config/config.inc.php');
include_once(_PS_ROOT_DIR_.'/init.php');
include_once(_PS_MODULE_DIR_.'mailjet/segmentation/segmentation.php');
$return = '';

if (Tools::getValue('token') != Configuration::get('SEGMENT_CUSTOMER_TOKEN'))
	exit();

if (Tools::getValue('action') == 'product')
{
	if (Tools::getValue('name') != "")
	{
		$products = Product::searchByName(Configuration::get('PS_LANG_DEFAULT'), Tools::getValue('name'));
    	if ($products)
    	{
    		$i = 0;
    		$return = '<ul id="plugproduct'.Tools::getValue('id').'">';
    		foreach ($products as $product)
    		{
    			$name = str_replace("'", "&#146;",$product['name']);
    			$name = str_replace('"', '\"',$name);
    			if (($i % 2) == 0)
        			$return .=  '<li id="'.$product['id_product'].'" class="pair">'.$name.'</li>';
        		else
        			$return .=  '<li id="'.$product['id_product'].'" class="impair">'.$name.'</li>';
        		$i++; 
    		}
    		$return .= '</ul>';
    	}
    }
    die ($return);
}
if (Tools::getValue('action') == 'productname')
{	
	$obj = new Segmentation();
	$prod = new product((int)Tools::getValue('id'), false, $obj->getCurrentIdLang());
	die ($prod->name);
}
if (Tools::getValue('action') == 'categoryname')
{	
	$obj = new Segmentation();
	$cat = new category((int)Tools::getValue('id'), $obj->getCurrentIdLang());
	die ($cat->name);
}
if (Tools::getValue('action') == 'brandname')
{	
	$obj = new Segmentation();
	$man = new manufacturer((int)Tools::getValue('id'), $obj->getCurrentIdLang());
	die ($man->name);
}
if (Tools::getValue('action') == 'category')
{
	if (Tools::getValue('name') != "")
	{
		$products = Category::searchByName(Configuration::get('PS_LANG_DEFAULT'), Tools::getValue('name'));
    	if ($products)
    	{
    		$i = 0;
    		$return = '<ul id="plugproduct'.Tools::getValue('id').'">';
    		foreach ($products as $product)
    		{
    			$name = str_replace("'", "&#146;",$product['name']);
    			$name = str_replace('"', '\"',$name);
    			if (($i % 2) == 0)
        			$return .=  '<li id="'.$product['id_category'].'" class="pair">'.$name.'</li>';
        		else
        			$return .=  '<li id="'.$product['id_category'].'" class="impair">'.$name.'</li>';
        		$i++; 
    		}
    		$return .= '</ul>';
    	}
    }
    die ($return);
}
if (Tools::getValue('action') == 'manufacturer')
{
	if (Tools::getValue('name') != "")
	{
		$manufacturers = Db::getInstance()->executeS('SELECT `id_manufacturer`, `name` FROM `'._DB_PREFIX_.'manufacturer` WHERE `name` LIKE "%'.pSQL(Tools::getValue('name')).'%" ');
		if ($manufacturers)
    	{
    		$i = 0;
    		$return = '<ul id="plugproduct'.Tools::getValue('id').'">';
    		foreach ($manufacturers as $manufacturer)
    		{
    			$name = str_replace("'", "&#146;",$manufacturer['name']);
    			$name = str_replace('"', '\"',$name);
    			if (($i % 2) == 0)
        			$return .=  '<li id="'.$manufacturer['id_manufacturer'].'" class="pair">'.$name.'</li>';
        		else
        			$return .=  '<li id="'.$manufacturer['id_manufacturer'].'" class="impair">'.$name.'</li>';
        		$i++; 
    		}
    		$return .= '</ul>';
    	}
    }
    die ($return);
}
?>