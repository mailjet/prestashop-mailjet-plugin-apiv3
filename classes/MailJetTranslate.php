<?php

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