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
     *
     * @param  string $email
     * @return boolean
     */
    public function subscribe($email, $list_id = null)
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
                "Action" => "addforce",
            );
            $response = $this->getApiOverlay()->addDetailedContactToList($contact, $update_list_id);
        } elseif (is_object($email)) {
            $action = $email->newsletter == 1 ? 'addforce' : 'unsub';
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
            $response = $this->getApiOverlay()->addDetailedContactToList($contact, $update_list_id);
        }
        return $response && $response->Count > 0 ? true : false;
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
            $action = $customer->newsletter == 1 ? 'addforce' : 'unsub';
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
            $response = $this->getApiOverlay()->addDetailedContactToList($contact, $mailjet_list_id);
            return $response && $response->Count > 0 ? true : false;
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
        $response = $this->getApiOverlay()->addDetailedContactToList($contact, $list_id);

        return $response && $response->Count > 0;
    }

    /**
     * Unsubscribe an email from every list it is currently subscribed to.
     *
     * @param  string $email
     * @return boolean false if at least one unsubscription failed
     */
    public function unsubscribeFromSubscribedLists($email)
    {
        $success = true;
        foreach ($this->getSubscribedSegmentLists($email) as $listId) {
            if (!$this->unsubscribe($email, $listId)) {
                $success = false;
            }
        }

        return $success;
    }

    public function unsubscribeListsExceptMaster($email)
    {
        $apiOverlay = $this->getApiOverlay();

        $lists = $apiOverlay->getContactsLists();

        $masterListId = $this->getAlreadyCreatedMasterListId();
        foreach ($lists as $list) {
            if ($list->ID == $masterListId) {
                continue;
            }
            $contact = array(
                "Email" => $email, // Mandatory field!
                "Action" => "unsub",
            );
            $response = $this->getApiOverlay()->addDetailedContactToList($contact, $list->ID);

            if (!$response || !($response->Count > 0)) {
                return false;
            }
        }
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
     * Remove email from a specific list or from all lists except masterList
     *
     * @param  string $email
     * @return boolean
     */
    public function remove($email, $list_id = null)
    {
        if ($list_id) {
            $contact = array(
                "Email" => $email,   // Mandatory field!
                "Action" => "remove",
            );
            $response = $this->getApiOverlay()->addDetailedContactToList($contact, $list_id);

            if (!$response || !($response->Count > 0)) {
                return false;
            }
        } else {
            $apiOverlay = $this->getApiOverlay();

            $lists = $apiOverlay->getContactsLists();

            $masterListId = $this->getAlreadyCreatedMasterListId();
            foreach ($lists as $list) {
                if ($list->ID == $masterListId) {
                    continue;
                }
                $contact = array(
                    "Email" => $email,   // Mandatory field!
                    "Action" => "remove",
                );
                $response = $apiOverlay->addDetailedContactToList($contact, $list->ID);

                if (!$response || !($response->Count > 0)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Remove email from all lists even in masterList
     *
     * @param  string $email
     * @return boolean
     */
    public function removeFromAllLists($email)
    {
        $apiOverlay = $this->getApiOverlay();

        $lists = $apiOverlay->getContactsLists();

        foreach ($lists as $list) {
            $contact = array(
                "Email" => $email,   // Mandatory field!
                "Action" => "remove",
            );
            $response = $apiOverlay->addDetailedContactToList($contact, $list->ID);

            if (!$response || !($response->Count > 0)) {
                return false;
            }
        }
        return true;
    }
}
