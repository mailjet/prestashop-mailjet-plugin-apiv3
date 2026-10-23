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

require_once dirname(__FILE__) . '/MailjetTrackingConsentStorageInterface.php';

class MailjetTrackingConsentDbStorage implements MailjetTrackingConsentStorageInterface
{
    const TABLE = 'mj_tracking_consent';
    const HISTORY_TABLE = 'mj_tracking_consent_history';

    /**
     * SQL used by both the install script and the upgrade script
     *
     * @return string[]
     */
    public static function getInstallSql()
    {
        return [
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . self::TABLE . '` (
                `id_customer` int(10) unsigned NOT NULL,
                `email` varchar(255) NOT NULL,
                `consent` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `source` varchar(16) NOT NULL,
                `synced` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_customer`),
                KEY `email` (`email`)
            );',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . self::HISTORY_TABLE . '` (
                `id_mj_tracking_consent_history` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_customer` int(10) unsigned NOT NULL,
                `email` varchar(255) NOT NULL,
                `consent` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `source` varchar(16) NOT NULL,
                `date_add` datetime NOT NULL,
                PRIMARY KEY (`id_mj_tracking_consent_history`),
                KEY `id_customer` (`id_customer`)
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
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . self::HISTORY_TABLE . '`',
        ];
    }

    public function find($idCustomer)
    {
        $row = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . self::TABLE . '` WHERE `id_customer` = ' . (int) $idCustomer
        );

        return $row ? $this->normalize($row) : null;
    }

    public function findByEmail($email)
    {
        $row = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . self::TABLE . '`
            WHERE `email` = \'' . pSQL($email) . '\'
            ORDER BY `date_upd` DESC'
        );

        return $row ? $this->normalize($row) : null;
    }

    public function findCustomerIdsByEmail($email)
    {
        $rows = Db::getInstance()->executeS(
            'SELECT `id_customer` FROM `' . _DB_PREFIX_ . 'customer`
            WHERE `email` = \'' . pSQL($email) . '\' AND `deleted` = 0'
        );

        return is_array($rows) ? array_map('intval', array_column($rows, 'id_customer')) : [];
    }

    public function save(array $row)
    {
        $db = Db::getInstance();
        $values = [
            'id_customer' => (int) $row['id_customer'],
            'email' => pSQL($row['email']),
            'consent' => (int) (bool) $row['consent'],
            'source' => pSQL($row['source']),
            'synced' => (int) (bool) $row['synced'],
            'date_upd' => pSQL($row['date_upd']),
        ];

        $saved = $db->execute(
            'REPLACE INTO `' . _DB_PREFIX_ . self::TABLE . '`
            (`id_customer`, `email`, `consent`, `source`, `synced`, `date_upd`)
            VALUES (' . $values['id_customer'] . ', \'' . $values['email'] . '\', ' . $values['consent'] . ', \''
            . $values['source'] . '\', ' . $values['synced'] . ', \'' . $values['date_upd'] . '\')'
        );

        return $saved && $db->execute(
            'INSERT INTO `' . _DB_PREFIX_ . self::HISTORY_TABLE . '`
            (`id_customer`, `email`, `consent`, `source`, `date_add`)
            VALUES (' . $values['id_customer'] . ', \'' . $values['email'] . '\', ' . $values['consent'] . ', \''
            . $values['source'] . '\', \'' . $values['date_upd'] . '\')'
        );
    }

    public function markSynced($idCustomer, $synced)
    {
        return Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . self::TABLE . '` SET `synced` = ' . (int) (bool) $synced . '
            WHERE `id_customer` = ' . (int) $idCustomer
        );
    }

    public function findUnsynced($limit)
    {
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . self::TABLE . '`
            WHERE `synced` = 0 ORDER BY `date_upd` ASC LIMIT ' . (int) $limit
        );

        return is_array($rows) ? array_map([$this, 'normalize'], $rows) : [];
    }

    public function getHistory($idCustomer, $limit)
    {
        $rows = Db::getInstance()->executeS(
            'SELECT `consent`, `source`, `email`, `date_add` FROM `' . _DB_PREFIX_ . self::HISTORY_TABLE . '`
            WHERE `id_customer` = ' . (int) $idCustomer . '
            ORDER BY `date_add` DESC, `id_mj_tracking_consent_history` DESC LIMIT ' . (int) $limit
        );

        if (!is_array($rows)) {
            return [];
        }

        foreach ($rows as &$row) {
            $row['consent'] = (bool) $row['consent'];
        }

        return $rows;
    }

    /**
     * @param array $row
     * @return array
     */
    private function normalize(array $row)
    {
        $row['id_customer'] = (int) $row['id_customer'];
        $row['consent'] = (bool) $row['consent'];
        $row['synced'] = (bool) $row['synced'];

        return $row;
    }
}
