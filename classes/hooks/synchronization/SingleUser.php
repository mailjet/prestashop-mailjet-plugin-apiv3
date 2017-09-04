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
    /**
     *
     * @param string $email
     * @return boolean
     */
    public function subscribe($email, $list_id = null)
    {
        $api = $this->getApi();
        $update_list_id = $list_id ? $list_id : $this->getAlreadyCreatedMasterListId();
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
                'Properties' => array(
                    'firstname' => $email->firstname,
                    'lastname' => $email->lastname
                )
            );
            $response = $this->getApiOverlay()->addDetailedContactToList($contact, $update_list_id);
        }
        return $response && $response->Count > 0 ? true : false;
    }

    /**
     * Add to a list(same status as in PS)
     * @param Customer $customer
     * @param int $mailjet_list_id
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
                'Properties' => array(
                    'firstname' => $customer->firstname,
                    'lastname' => $customer->lastname
                )
            );
            $response = $this->getApiOverlay()->addDetailedContactToList($contact, $mailjet_list_id);
            return $response && $response->Count > 0 ? true : false;
        }
        return false;
    }

    /**
     *
     * @param string $email
     * @return boolean
     */
    public function unsubscribe($email, $list_id = null)
    {
        if ($list_id) {
            $contact = array(
                "Email" => $email,   // Mandatory field!
                "Action" => "unsub",
            );
            $response = $this->getApiOverlay()->addDetailedContactToList($contact, $list_id);

            if (!$response || !($response->Count > 0)) {
                return false;
            }
        } else {
            $apiOverlay = $this->getApiOverlay();

            $lists = $apiOverlay->getContactsLists();

            foreach ($lists as $list) {
                $contact = array(
                    "Email" => $email,   // Mandatory field!
                    "Action" => "unsub",
                );
                $response = $this->getApiOverlay()->addDetailedContactToList($contact, $list->ID);

                if (!$response || !($response->Count > 0)) {
                    return false;
                }
            }
        }

        return true;
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
     * @param string $email
     * @return [int] $subscribedListsIds
     */
    public function getSubscribedSegmentLists($email)
    {
        $apiOverlay = $this->getApiOverlay();
        $lists = $apiOverlay->getCustomerLists($email);
        if (!$lists) {
            return false;
        }
        $subscribedListsIds = array();
        foreach ($lists->Data as $list) {
            if ($list->IsUnsub == false) {
                $subscribedListsIds[] = $list->ID;
            }
        }
        return $subscribedListsIds;
    }

    /**
     * Remove email from a specific list or from all lists except masterList
     * @param string $email
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
     * @param string $email
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
