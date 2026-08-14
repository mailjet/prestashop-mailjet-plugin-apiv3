<?php

/**
 * Bootstrap and dispatch Mailjet module HTTP endpoints.
 */
class MailjetEndpointRunner
{
    /**
     * @param string $endpoint
     * @return void
     */
    public static function run($endpoint)
    {
        if (!defined('_PS_VERSION_')) {
            require_once dirname(__DIR__, 3) . '/config/config.inc.php';
            if (version_compare(_PS_VERSION_, '1.5', '<') || !defined('_PS_ADMIN_DIR_')) {
                require_once _PS_ROOT_DIR_ . '/init.php';
            }
        }

        $file = __DIR__ . '/' . $endpoint . '.inc.php';
        if (!file_exists($file)) {
            header('HTTP/1.1 404 Not Found');
            exit;
        }

        require $file;
    }
}
