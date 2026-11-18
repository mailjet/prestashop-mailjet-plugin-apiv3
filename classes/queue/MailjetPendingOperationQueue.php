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
require_once dirname(__FILE__) . '/MailjetPendingOperationDbStorage.php';

/**
 * Mailjet list operations which failed, kept until they succeed.
 *
 * Operations are idempotent (addforce, addnoforce, unsub, remove on one
 * list), so retrying one several times is safe. After MAX_ATTEMPTS failed
 * retries an operation is marked failed and shown in the back office.
 */
class MailjetPendingOperationQueue
{
    const STATUS_PENDING = 'pending';
    const STATUS_FAILED = 'failed';
    const MAX_ATTEMPTS = 10;

    /** @var MailjetPendingOperationStorageInterface */
    private $storage;

    /** @var int */
    private $maxAttempts;

    /**
     * @param MailjetPendingOperationStorageInterface $storage
     * @param int                                     $maxAttempts
     */
    public function __construct(MailjetPendingOperationStorageInterface $storage, $maxAttempts = self::MAX_ATTEMPTS)
    {
        $this->storage = $storage;
        $this->maxAttempts = max(1, (int) $maxAttempts);
    }

    /**
     * @return self
     */
    public static function create()
    {
        return new self(new MailjetPendingOperationDbStorage());
    }

    /**
     * Record an operation which could not be sent
     *
     * @param array  $contact
     * @param int    $listId
     * @param string $error
     * @return bool
     */
    public function enqueue(array $contact, $listId, $error)
    {
        if (empty($contact['Email']) || empty($listId)) {
            return false;
        }

        return $this->storage->save($contact['Email'], (int) $listId, $contact, (string) $error);
    }

    /**
     * An operation for this email and list succeeded: an older pending one is obsolete
     *
     * @param string $email
     * @param int    $listId
     * @return bool
     */
    public function resolve($email, $listId)
    {
        return $this->storage->delete($email, (int) $listId);
    }

    /**
     * Send the pending operations again
     *
     * @param callable $send  function (array $contact, int $listId): bool
     * @param int      $limit
     * @return array{retried: int, succeeded: int, failed: int}
     */
    public function retry(callable $send, $limit = 100)
    {
        $result = ['retried' => 0, 'succeeded' => 0, 'failed' => 0];

        foreach ($this->storage->findPending($limit) as $operation) {
            $result['retried']++;
            $error = '';
            try {
                $sent = (bool) call_user_func($send, $operation['contact'], $operation['list_id']);
            } catch (Exception $e) {
                $sent = false;
                $error = $e->getMessage();
            }

            if ($sent) {
                $this->storage->delete($operation['email'], $operation['list_id']);
                $result['succeeded']++;
                continue;
            }

            $attempts = $operation['attempts'] + 1;
            $status = $attempts >= $this->maxAttempts ? self::STATUS_FAILED : self::STATUS_PENDING;
            if ($status === self::STATUS_FAILED) {
                $result['failed']++;
            }
            $this->storage->updateAttempt(
                $operation['id'],
                $attempts,
                $error !== '' ? $error : 'Retry ' . $attempts . ' failed',
                $status
            );
        }

        return $result;
    }

    /**
     * @return int operations still waiting for a retry
     */
    public function countPending()
    {
        return $this->storage->count(self::STATUS_PENDING);
    }

    /**
     * @return int operations given up after MAX_ATTEMPTS retries
     */
    public function countFailed()
    {
        return $this->storage->count(self::STATUS_FAILED);
    }
}
