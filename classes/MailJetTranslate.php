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

class MailJetTranslate
{
	public static function getTranslationsByName($name, $iso = false)
	{
		$file = dirname(__FILE__).'/../xml/translate.xml';
		$translations = array();
		$default_translation = array();

		if (file_exists($file) && ($xml = simplexml_load_file($file)))
		{
			$iso = ($iso) ? $iso : Context::getContext()->language->iso_code;

			if (isset($xml->{$name}))
			{
				foreach ($xml->{$name}->iso as $data)
				{
					if ($data['code'] == $iso)
						$translations = (array)$data;
					if ($iso != 'en' && $data['code'] == 'en')
						$default_translation = (array)$data;
				}
			}
		}
		$translations += $default_translation;
		unset($translations['@attributes']);
		return $translations;
	}
}