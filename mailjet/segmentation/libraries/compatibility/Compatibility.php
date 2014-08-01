<?php
/*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 7723 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Compatibility
{	
	public static function init()
	{		
		// controller Front
		self::initFrontController();
		
		// class AdminTab
		self::initAdminTab();
		
		// global currentIndex
		self::initCurrentIndex();
			
		// class Context
		self::initContext();
		
		return true;
	}
	
	public static function initFrontController()
	{		
		$pathFileFrom = realpath(dirname(__FILE__)) . '/FrontController.php';
		
		if ((float)_PS_VERSION_ >= 1.5)
			$pathFileTo = _PS_ROOT_DIR_ . '/override/classes/controller/FrontController.php';
		else
			$pathFileTo = _PS_ROOT_DIR_ . '/override/classes/FrontController.php';
			
		self::startOverride($pathFileFrom, $pathFileTo);
	}
	
	public static function initAdminTab()
	{
		$pathFileFrom = realpath(dirname(__FILE__)) . '/AdminTab.php';
		$pathFileTo = _PS_ROOT_DIR_ . '/override/classes/AdminTab.php';
		
		self::startOverride($pathFileFrom, $pathFileTo);
	}
	
	public static function initContext()
	{
		if (!class_exists('Context'))
			require_once(realpath(dirname(__FILE__))  . '/Context.php');
			
		return true;
	}
	
	public static function initCurrentIndex()
	{
		/*global $currentIndex;
		
		if (!$currentIndex)*/
			$currentIndex = $_SERVER['SCRIPT_NAME'].(($controller = Tools::getValue('controller')) ? '?controller='.$controller: '');
			
		if (!property_exists('AdminTab', 'currentIndex'))
			header('location: ' . $currentIndex . '&token=' . Tools::getValue('token'));
			
		AdminTab::$currentIndex = $currentIndex;
			
		return true;
	}
	
	public static function startOverride($pathFileFrom, $pathFileTo)
	{		
		if (!file_exists($pathFileFrom))
			throw new Exception("file to override doest not exist");
		
		if (file_exists($pathFileTo))
		{
			if (file_exists($pathFileTo . '.old') && !unlink($pathFileTo . '.old'))
	        	throw new Exception("can't delete old override class file");  				
        	
	        if (!rename($pathFileTo, $pathFileTo . '.old'))
				throw new Exception("renaming old file failed");
		}			
				
		if (!copy($pathFileFrom, $pathFileTo))
			throw new Exception("copying file failed");
				
		return true;
	}
	
	public static function resetOverride()
	{
		$pathOverride = _PS_ROOT_DIR_ . '/override/';
		$forbiddenReset = array(
			'.',
			'..',
			'FrontController.php.old',
			'AdminTab.php.old',
			'Compatibility.php.old'
		);		
		
		$pathOverrideClasses = $pathOverride . 'classes/';
		if (!($handle=opendir($pathOverrideClasses)))
		{
			while (($filename=readdir($handle)) !== false)
			{
				if (!in_array($filename, $forbiddenReset) && Tools::substr($filename, -4) == '.old')
        		{
        			if (!unlink($pathOverrideClasses . Tools::substr($filename, 0, Tools::strlen($filename) - 4)))
        				throw new Exception("can't delete override class file");
        			if (!rename($pathOverrideClasses . $filename, $pathOverrideClasses . Tools::substr($filename, 0, Tools::strlen($filename) - 4)))
        				throw new Exception("can't delete override class file");
        		}
			}
			closedir($handle);
		}			
        
        $pathOverrideControllers = $pathOverride . 'controllers/';
        if (!($handle=opendir($pathOverrideControllers)))
        {
        	while (($filename=readdir($handle)) !== false)
        	{
	        	if (!in_array($filename, $forbiddenReset) && Tools::substr($filename, -4) == '.old')
	        	{
	        		if (!unlink($pathOverrideControllers . Tools::substr($filename, 0, Tools::strlen($filename) - 4)))
	        			throw new Exception("can't delete override controller file");
	        		if (!rename($pathOverrideControllers . $filename, $pathOverrideControllers . Tools::substr($filename, 0, Tools::strlen($filename) - 4)))
	        			throw new Exception("can't delete override controller file");
	        	}
        	}        	
        	closedir($handle);
        }
        		
        return true;
	}
}