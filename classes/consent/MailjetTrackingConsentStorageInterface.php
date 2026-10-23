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

/**
 * Persistence of the GDPR open/click tracking consent, kept separate from the
 * native Customer "newsletter" and "optin" fields.
 *
 * A consent row is an array with the keys:
 * id_customer, email, consent (bool), source, date_upd (Y-m-d H:i:s), synced (bool)
 */
interface MailjetTrackingConsentStorageInterface
{
    /**
     * @param int $idCustomer
     * @return array|null
     */
    public function find($idCustomer);

    /**
     * Most recently updated consent row for an email address
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail($email);

    /**
     * Ids of the (not deleted) customers using this email address
     *
     * @param string $email
     * @return int[]
     */
    public function findCustomerIdsByEmail($email);

    /**
     * Insert or update the consent row and append an entry to the audit history
     *
     * @param array $row
     * @return bool
     */
    public function save(array $row);

    /**
     * @param int $idCustomer
     * @param bool $synced
     * @return bool
     */
    public function markSynced($idCustomer, $synced);

    /**
     * Rows changed locally which have not been pushed to Mailjet yet
     *
     * @param int $limit
     * @return array[]
     */
    public function findUnsynced($limit);

    /**
     * Audit trail of a customer, newest first
     *
     * @param int $idCustomer
     * @param int $limit
     * @return array[]
     */
    public function getHistory($idCustomer, $limit);
}
