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
 * Persistence of the Mailjet list operations which could not be sent.
 *
 * A row is an array with the keys: id, email, list_id, contact (array),
 * attempts, last_error, status ('pending'|'failed'), date_add, date_upd.
 * There is at most one row per email and list: a newer operation replaces
 * the older one.
 */
interface MailjetPendingOperationStorageInterface
{
    /**
     * Insert or replace the operation for this email and list, as pending with 0 attempts
     *
     * @param string $email
     * @param int    $listId
     * @param array  $contact
     * @param string $error
     * @return bool
     */
    public function save($email, $listId, array $contact, $error);

    /**
     * @param string $email
     * @param int    $listId
     * @return bool
     */
    public function delete($email, $listId);

    /**
     * Oldest pending operations first
     *
     * @param int $limit
     * @return array[]
     */
    public function findPending($limit);

    /**
     * @param int    $id
     * @param int    $attempts
     * @param string $error
     * @param string $status
     * @return bool
     */
    public function updateAttempt($id, $attempts, $error, $status);

    /**
     * @param string $status
     * @return int
     */
    public function count($status);
}
