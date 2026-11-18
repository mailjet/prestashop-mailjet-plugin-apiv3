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

class HooksSynchronizationSingleUser extends HooksSynchronizationSynchronizationAbstract
{
    const SEGMENT_SUBSCRIBE = 'subscribe';
    const SEGMENT_UNSUBSCRIBE = 'unsubscribe';
    const SEGMENT_REMOVE = 'remove';

    /** Explicit, recorded opt-in: may resubscribe a contact who unsubscribed */
    const ACTION_FORCE = 'addforce';

    /** Routine update: never changes an unsubscribed contact back to subscribed */
    const ACTION_NOFORCE = 'addnoforce';

    /**
     * Lists unsubscribed by the last unsubscribeFromSubscribedLists() call
     *
     * @var int[]
     */
    private $lastUnsubscribedListIds = array();

    /** @var MailjetPendingOperationQueue|null */
    private $pendingQueue;

    /** @var bool */
    private $pendingQueueResolved = false;

    /**
     * True when the newsletter flag goes from 0 to 1 in this update, i.e. the
     * contact (or the merchant on his behalf) opted in just now.
     *
     * @param  int|string|null $previousNewsletter newsletter value before the update, null if unknown
     * @param  object          $customer
     * @return bool
     */
    public static function isExplicitOptIn($previousNewsletter, $customer)
    {
        return $previousNewsletter !== null
            && (int) $previousNewsletter === 0
            && (int) $customer->newsletter === 1;
    }

    /**
     * Previous address when this update changed the customer email, otherwise null
     *
     * @param  string|null $previousEmail email stored before the update, null if unknown
     * @param  object      $customer
     * @return string|null
     */
    public static function getReplacedEmail($previousEmail, $customer)
    {
        if (empty($previousEmail) || empty($customer->email)) {
            return null;
        }

        return strcasecmp(trim($previousEmail), trim($customer->email)) === 0 ? null : $previousEmail;
    }

    /**
     * @param  bool $explicitOptIn
     * @return string
     */
    public static function getSubscribeAction($explicitOptIn)
    {
        return $explicitOptIn ? self::ACTION_FORCE : self::ACTION_NOFORCE;
    }

    /**
     * What to do with a customer in an auto-assigned segment list.
     *
     * Matching a segment is not consent: only an active customer with
     * newsletter = 1 is subscribed. A matching customer without consent is
     * kept with the unsubscribed status, so the segment never mails him.
     *
     * @param  object $customer         Customer (active, newsletter)
     * @param  bool   $matchesSegment
     * @return string one of the SEGMENT_* constants
     */
    public static function getSegmentListAction($customer, $matchesSegment)
    {
        if (!$matchesSegment) {
            return self::SEGMENT_REMOVE;
        }

        if ((int) $customer->active === 1 && (int) $customer->newsletter === 1) {
            return self::SEGMENT_SUBSCRIBE;
        }

        return self::SEGMENT_UNSUBSCRIBE;
    }

    /**
     * Routine calls use addnoforce, so a contact who unsubscribed in Mailjet
     * stays unsubscribed. Pass $explicitOptIn = true only for an opt-in made
     * just now by the contact (see docs/scope-rules.md).
     *
     * @param  string|Customer $email
     * @param  int|null        $list_id
     * @param  bool            $explicitOptIn
     * @return boolean
     */
    public function subscribe($email, $list_id = null, $explicitOptIn = false)
    {
        $api = $this->getApi();
        $update_list_id = $list_id ?: $this->getAlreadyCreatedMasterListId();
        $api->resetRequest();

        if (!$update_list_id || empty($update_list_id)) {
            $params = array(
                'method' => 'JSON',
                'Name' => self::LIST_NAME
            );

            $response = $this->getApiOverlay()->createContactsListP($params);
            if (!$response || empty($response->ID)) {
                throw new HooksSynchronizationException('There is a problem with the list\'s creation.');
            }

            $update_list_id = $response->ID;
        }

        if (is_string($email)) {
            $contact = array(
                "Email" => $email,   // Mandatory field!
                "Action" => self::getSubscribeAction($explicitOptIn),
            );
        } elseif (is_object($email)) {
            $action = $email->newsletter == 1 ? self::getSubscribeAction($explicitOptIn) : 'unsub';
            $contact = array(
                "Action" => $action,
                'Email' => $email->email,
                'Name' => $email->firstname,
                'Properties' => array_merge(
                    array(
                        'firstname' => $email->firstname,
                        'lastname' => $email->lastname
                    ),
                    MailjetTrackingConsent::create()->getContactProperties((int) $email->id)
                )
            );
        } else {
            return false;
        }

        return $this->sendContact($contact, $update_list_id);
    }

    /**
     * Add to a list(same status as in PS)
     *
     * @param  Customer $customer
     * @param  int      $mailjet_list_id
     * @return boolean
     */
    public function addToList($customer, $mailjet_list_id)
    {
        if (is_object($customer)) {
            $action = $customer->newsletter == 1 ? self::ACTION_NOFORCE : 'unsub';
            $contact = array(
                "Action" => $action,
                'Email' => $customer->email,
                'Name' => $customer->firstname,
                'Properties' => array_merge(
                    array(
                        'firstname' => $customer->firstname,
                        'lastname' => $customer->lastname
                    ),
                    MailjetTrackingConsent::create()->getContactProperties((int) $customer->id)
                )
            );
            return $this->sendContact($contact, $mailjet_list_id);
        }
        return false;
    }

    /**
     * Unsubscribe an email from one contact list.
     *
     * A list id is mandatory: without it nothing is sent, so a contact is never
     * unsubscribed from every list of the Mailjet account (including lists which
     * are not managed by PrestaShop).
     *
     * @param  string $email
     * @param  int    $list_id
     * @return boolean
     */
    public function unsubscribe($email, $list_id = null)
    {
        if (empty($list_id)) {
            $this->log('unsubscribe() called without a list id for ' . $email . ', nothing unsubscribed');

            return false;
        }

        $contact = array(
            "Email" => $email,   // Mandatory field!
            "Action" => "unsub",
        );
        return $this->sendContact($contact, $list_id);
    }

    /**
     * Newsletter consent withdrawn: the single entry point for every withdrawal
     * (newsletter form, back office, customer saved with newsletter = 0).
     *
     * The contact is unsubscribed (not removed) from every managed list it is
     * subscribed to: master list and segment lists. The master list is always
     * included, even when the list lookup returns nothing, so a guest who only
     * registered through the newsletter form is covered too.
     *
     * @param  string $email
     * @return bool false if at least one list could not be updated
     */
    public function withdraw($email)
    {
        if (empty($email)) {
            return false;
        }

        $success = $this->unsubscribeFromSubscribedLists($email);

        $masterListId = (int) $this->getAlreadyCreatedMasterListId();
        if ($masterListId && !in_array($masterListId, $this->lastUnsubscribedListIds, true)) {
            if (!$this->unsubscribe($email, $masterListId)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Unsubscribe an email from every managed list it is currently subscribed to.
     * Lists not managed by the shop are left untouched.
     *
     * @param  string $email
     * @return boolean false if at least one unsubscription failed
     */
    public function unsubscribeFromSubscribedLists($email)
    {
        $success = true;
        $this->lastUnsubscribedListIds = array();
        $subscribed = $this->getSubscribedSegmentLists($email);
        $managed = $subscribed ? $this->getManagedListIds() : array();
        foreach ($subscribed as $listId) {
            if (!in_array((int) $listId, $managed, true)) {
                continue;
            }
            $this->lastUnsubscribedListIds[] = (int) $listId;
            if (!$this->unsubscribe($email, $listId)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Unsubscribe an email from the managed segment lists (master list excluded)
     *
     * @param  string $email
     * @return boolean
     */
    public function unsubscribeListsExceptMaster($email)
    {
        $success = true;
        foreach ($this->getManagedListIds(false) as $listId) {
            $contact = array(
                "Email" => $email, // Mandatory field!
                "Action" => "unsub",
            );
            // A failure is queued for retry; the other lists are still processed
            if (!$this->sendContact($contact, $listId)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Get segment lists in which a customer is subscribed
     *
     * The contact/{email}/getcontactslists endpoint returns ListID and IsUnsub
     * (not the ID field of the contactslist resource).
     *
     * @param  string $email
     * @return int[]
     */
    public function getSubscribedSegmentLists($email)
    {
        $lists = $this->getApiOverlay()->getCustomerLists($email);
        if (!$lists || empty($lists->Data)) {
            return array();
        }

        $subscribedListsIds = array();
        foreach ($lists->Data as $list) {
            $listId = isset($list->ListID) ? $list->ListID : (isset($list->ID) ? $list->ID : null);
            if (empty($listId)) {
                $this->log('getSubscribedSegmentLists() skipped an entry without a list id: ' . json_encode($list));
                continue;
            }

            $isUnsub = isset($list->IsUnsub) ? $list->IsUnsub
                : (isset($list->IsUnsubscribed) ? $list->IsUnsubscribed : false);
            if (filter_var($isUnsub, FILTER_VALIDATE_BOOLEAN)) {
                continue;
            }

            $subscribedListsIds[] = (int) $listId;
        }

        return $subscribedListsIds;
    }

    /**
     * @param string $message
     */
    private function log($message)
    {
        if (class_exists('MailJetLog') && MailJetLog::$file) {
            MailJetLog::write(MailJetLog::$file, 'HooksSynchronizationSingleUser::' . $message);
        }
    }

    /**
     * Remove email from a specific list or from all managed lists except masterList
     *
     * @param  string $email
     * @return boolean
     */
    public function remove($email, $list_id = null)
    {
        $listIds = $list_id ? array($list_id) : $this->getManagedListIds(false);

        $success = true;
        foreach ($listIds as $listId) {
            $contact = array(
                "Email" => $email,   // Mandatory field!
                "Action" => "remove",
            );
            if (!$this->sendContact($contact, $listId)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Erasure of a deleted customer: removed (not unsubscribed) from every managed
     * list, master list included. The single entry point for every deletion workflow.
     *
     * @param  string $email
     * @return bool
     */
    public function erase($email)
    {
        if (empty($email)) {
            return false;
        }

        return $this->removeFromAllLists($email);
    }

    /**
     * Remove email from all managed lists, master list included.
     * Lists not managed by the shop are left untouched.
     *
     * @param  string $email
     * @return boolean
     */
    public function removeFromAllLists($email)
    {
        $success = true;
        foreach ($this->getManagedListIds() as $listId) {
            $contact = array(
                "Email" => $email,   // Mandatory field!
                "Action" => "remove",
            );
            if (!$this->sendContact($contact, $listId)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Send one contact operation to one list and check the result.
     *
     * A failed or rejected request is recorded in the pending operation queue
     * and retried by the cron; a successful one drops any older pending
     * operation for the same address and list, so a retry never overrides a
     * newer change.
     *
     * @param  array $contact        Email, Action and optional Name/Properties
     * @param  int   $listId
     * @param  bool  $queueOnFailure false when called by the retry itself
     * @return bool
     */
    protected function sendContact(array $contact, $listId, $queueOnFailure = true)
    {
        $error = null;
        try {
            $response = $this->getApiOverlay()->addDetailedContactToList($contact, $listId);
            $success = $response && isset($response->Count) && $response->Count > 0;
            if (!$success) {
                $error = 'Mailjet rejected the request or did not answer';
            }
        } catch (Exception $e) {
            $success = false;
            $error = $e->getMessage();
        }

        $queue = $this->getPendingQueue();
        if ($success) {
            if ($queue) {
                $queue->resolve($contact['Email'], $listId);
            }

            return true;
        }

        $this->log('sendContact() ' . $contact['Action'] . ' ' . $contact['Email'] . ' on list ' . $listId . ' failed: ' . $error);
        if ($queue && $queueOnFailure) {
            $queue->enqueue($contact, $listId, $error);
        }

        return false;
    }

    /**
     * Retry the operations which failed earlier (called by the cron)
     *
     * @param  int $limit
     * @return array{retried: int, succeeded: int, failed: int}
     */
    public function retryPendingOperations($limit = 100)
    {
        $queue = $this->getPendingQueue();
        if (!$queue) {
            return array('retried' => 0, 'succeeded' => 0, 'failed' => 0);
        }

        $self = $this;

        return $queue->retry(function (array $contact, $listId) use ($self) {
            return $self->sendQueuedContact($contact, $listId);
        }, $limit);
    }

    /**
     * @internal used by retryPendingOperations()
     *
     * @param  array $contact
     * @param  int   $listId
     * @return bool
     */
    public function sendQueuedContact(array $contact, $listId)
    {
        return $this->sendContact($contact, $listId, false);
    }

    /**
     * @param MailjetPendingOperationQueue|null $queue
     */
    public function setPendingQueue($queue)
    {
        $this->pendingQueue = $queue;
        $this->pendingQueueResolved = true;
    }

    /**
     * @return MailjetPendingOperationQueue|null
     */
    protected function getPendingQueue()
    {
        if (!$this->pendingQueueResolved) {
            $this->pendingQueueResolved = true;
            if (class_exists('Db') && class_exists('MailjetPendingOperationQueue')) {
                $this->pendingQueue = MailjetPendingOperationQueue::create();
            }
        }

        return $this->pendingQueue;
    }
}
