<?php

/**
 * Minimal PrestaShop environment for the unit tests (no shop, no database)
 */

define('_PS_VERSION_', '8.1.0');
define('_DB_PREFIX_', 'ps_');
define('_PS_MODULE_DIR_', dirname(__DIR__, 2) . '/');

require_once dirname(__DIR__) . '/vendor/autoload.php';

class Configuration
{
    public static $values = [];

    public static function get($key, $idLang = null)
    {
        return array_key_exists($key, self::$values) ? self::$values[$key] : false;
    }

    public static function updateValue($key, $value)
    {
        self::$values[$key] = $value;

        return true;
    }
}

class Tools
{
    public static function strtolower($str)
    {
        return mb_strtolower($str, 'UTF-8');
    }
}

require_once dirname(__DIR__) . '/classes/consent/MailjetTrackingConsent.php';
require_once __DIR__ . '/Support/InMemoryTrackingConsentStorage.php';
require_once __DIR__ . '/Support/FakeMessages.php';
require_once __DIR__ . '/Support/FakeMailjetTransport.php';
