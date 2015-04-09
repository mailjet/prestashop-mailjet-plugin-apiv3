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

class MailJetLog
{
	public static $file;

	public static $handle;

	/**
	 * Init default value
	 * @static
	 */
	public static function init()
	{
		MailJetLog::$file = dirname(__FILE__).'/../logs/ajax.log';
	}

	private static function lockWrite($handle, $msg)
	{
		if ($handle && flock($handle, LOCK_EX))
		{
			fwrite($handle, $msg."\r\n");
			flock($handle, LOCK_UN);
			return true;
		}
		return false;
	}

	public static function write($file, $message, $mode = 'a+', $close = false)
	{
		$date = date('d/m/Y G:i:s');
		$exist = false;
		if (file_exists($file))
			$exist = true;

		self::setHandle($file, $mode);

		if (!$exist)
			chmod($file, 0666);

		if (self::$handle && self::lockWrite(self::$handle, '['.$date.'] '.$message))
		{
			if ($close)
				fclose(self::$handle);
			return true;
		}
		return false;
	}

	private static function setHandle($file, $mode)
	{
		if ($file == self::$file && !self::$handle)
			self::$handle = fopen($file, $mode);
		else if ($file != self::$file)
		{
			if (self::$handle)
				fclose(self::$handle);
			self::$file = $file;
			self::$handle = fopen($file, $mode);
		}
	}
}