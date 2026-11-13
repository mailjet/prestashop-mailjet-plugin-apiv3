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

require_once dirname(__FILE__) . '/MailjetPendingOperationStorageInterface.php';

class MailjetPendingOperationDbStorage implements MailjetPendingOperationStorageInterface
{
    const TABLE = 'mj_pending_operation';

    /**
     * SQL used by both the install script and the upgrade script
     *
     * @return string[]
     */
    public static function getInstallSql()
    {
        return [
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . self::TABLE . '` (
                `id_mj_pending_operation` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `email` varchar(255) NOT NULL,
                `list_id` int(10) unsigned NOT NULL,
                `contact` text NOT NULL,
                `attempts` int(10) unsigned NOT NULL DEFAULT 0,
                `last_error` varchar(255) NOT NULL DEFAULT \'\',
                `status` varchar(16) NOT NULL DEFAULT \'pending\',
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_mj_pending_operation`),
                UNIQUE KEY `email_list` (`email`, `list_id`),
                KEY `status` (`status`)
            );',
        ];
    }

    /**
     * @return string[]
     */
    public static function getUninstallSql()
    {
        return [
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . self::TABLE . '`',
        ];
    }

    public function save($email, $listId, array $contact, $error)
    {
        $now = date('Y-m-d H:i:s');

        return (bool) Db::getInstance()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . self::TABLE . '`
            (`email`, `list_id`, `contact`, `attempts`, `last_error`, `status`, `date_add`, `date_upd`)
            VALUES (\'' . pSQL($email) . '\', ' . (int) $listId . ', \'' . pSQL(json_encode($contact)) . '\', 0, \''
            . pSQL(Tools::substr((string) $error, 0, 255)) . '\', \'' . MailjetPendingOperationQueue::STATUS_PENDING
            . '\', \'' . $now . '\', \'' . $now . '\')
            ON DUPLICATE KEY UPDATE `contact` = VALUES(`contact`), `attempts` = 0,
            `last_error` = VALUES(`last_error`), `status` = VALUES(`status`), `date_upd` = VALUES(`date_upd`)'
        );
    }

    public function delete($email, $listId)
    {
        return (bool) Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . self::TABLE . '`
            WHERE `email` = \'' . pSQL($email) . '\' AND `list_id` = ' . (int) $listId
        );
    }

    public function findPending($limit)
    {
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . self::TABLE . '`
            WHERE `status` = \'' . MailjetPendingOperationQueue::STATUS_PENDING . '\'
            ORDER BY `date_upd` ASC LIMIT ' . (int) $limit
        );
        if (!is_array($rows)) {
            return [];
        }

        $operations = [];
        foreach ($rows as $row) {
            $contact = json_decode($row['contact'], true);
            if (!is_array($contact)) {
                continue;
            }
            $operations[] = [
                'id' => (int) $row['id_mj_pending_operation'],
                'email' => $row['email'],
                'list_id' => (int) $row['list_id'],
                'contact' => $contact,
                'attempts' => (int) $row['attempts'],
                'last_error' => $row['last_error'],
                'status' => $row['status'],
            ];
        }

        return $operations;
    }

    public function updateAttempt($id, $attempts, $error, $status)
    {
        return (bool) Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . self::TABLE . '`
            SET `attempts` = ' . (int) $attempts . ', `last_error` = \'' . pSQL(Tools::substr((string) $error, 0, 255)) . '\',
            `status` = \'' . pSQL($status) . '\', `date_upd` = \'' . date('Y-m-d H:i:s') . '\'
            WHERE `id_mj_pending_operation` = ' . (int) $id
        );
    }

    public function count($status)
    {
        return (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . self::TABLE . '` WHERE `status` = \'' . pSQL($status) . '\''
        );
    }
}
