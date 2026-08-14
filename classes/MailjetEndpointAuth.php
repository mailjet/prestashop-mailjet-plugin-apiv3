<?php

/**
 * 2007-2017 PrestaShop
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
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class MailjetEndpointAuth
{
    /**
     * @return void
     */
    public static function validateAdminModuleToken()
    {
        $idEmployee = (int) Tools::getValue('id_employee');
        if (!$idEmployee && isset(Context::getContext()->employee->id)) {
            $idEmployee = (int) Context::getContext()->employee->id;
        }

        $tokenOk = Tools::getAdminToken(
            'AdminModules' . (int) Tab::getIdFromClassName('AdminModules') . $idEmployee
        );
        $submittedToken = (string) Tools::getValue('token');

        if ($submittedToken === ''
            || ($submittedToken !== $tokenOk && $submittedToken !== Tools::getAdminTokenLite('AdminModules'))
        ) {
            die('hack attempt');
        }
    }

    /**
     * @return void
     */
    public static function ensureCronSecret()
    {
        if (!Configuration::get('MAILJET_CRON_SECRET')) {
            Configuration::updateValue('MAILJET_CRON_SECRET', bin2hex(random_bytes(32)));
        }
    }

    /**
     * @return void
     */
    public static function validateCronSecret()
    {
        self::ensureCronSecret();
        $submittedToken = (string) Tools::getValue('token');
        $cronSecret = (string) Configuration::get('MAILJET_CRON_SECRET');

        if ($submittedToken === '' || !hash_equals($cronSecret, $submittedToken)) {
            die('No hackers allowed here ! ;-)');
        }
    }

    /**
     * @return void
     */
    public static function ensureWebhookSecret()
    {
        if (!Configuration::get('MAILJET_WEBHOOK_SECRET')) {
            Configuration::updateValue('MAILJET_WEBHOOK_SECRET', bin2hex(random_bytes(32)));
        }
    }

    /**
     * @return string
     */
    public static function getWebhookSecret()
    {
        self::ensureWebhookSecret();

        return (string) Configuration::get('MAILJET_WEBHOOK_SECRET');
    }

    /**
     * @return string
     */
    public static function readWebhookRequestBody()
    {
        return trim(Tools::file_get_contents('php://input'));
    }

    /**
     * @param string $body
     * @return void
     */
    public static function validateWebhookRequest($body)
    {
        self::ensureWebhookSecret();
        $secret = (string) Configuration::get('MAILJET_WEBHOOK_SECRET');
        $urlToken = (string) Tools::getValue('h');
        $providedSignature = isset($_SERVER['HTTP_X_MAILJET_SIGNATURE'])
            ? (string) $_SERVER['HTTP_X_MAILJET_SIGNATURE']
            : '';

        if ($urlToken === '' || !hash_equals($secret, $urlToken)) {
            header('HTTP/1.1 401 Unauthorized');
            exit;
        }

        if ($providedSignature !== '' && !hash_equals(hash_hmac('sha256', $body, $secret), $providedSignature)) {
            header('HTTP/1.1 401 Unauthorized');
            exit;
        }
    }
}
