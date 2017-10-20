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

class HooksSynchronizationSegment extends HooksSynchronizationSynchronizationAbstract
{

    /**
     *
     * @var array
     */
    private $mailjetContacts = array();

    /**
     *
     * @var int
     */
    private $limitPerRequest = 2500;

    /**
     *
     * @param array $contacts
     * @param string $filterId
     * @param string $fiterName
     */
    public function sychronize($contacts, $filterId, $fiterName)
    {
        $existingListId = $this->getExistingMailjetListId($filterId);

        /*
         * Sets related contact meta data like firstname, lastname, etc...
         */
        $this->getApiOverlay()->setContactMetaData(array(
            array('Datatype' => 'str', 'Name' => 'firstname', 'NameSpace' => 'static'),
            array('Datatype' => 'str', 'Name' => 'lastname', 'NameSpace' => 'static')
        ));

        if ($existingListId) {
            return $this->update($contacts, $existingListId);
        }

        return $this->create($contacts, $filterId, $fiterName);
    }

    /**
     *
     * @param int $filterId
     * @param string $newName
     * @return bool
     */
    public function updateName($mailjetListId, $prestashopFilterId, $newName)
    {
        if ($mailjetListId) {
            $params = array(
                'ID' => $mailjetListId,
                'method' => 'JSON',
                'Name' => $prestashopFilterId . 'idf' .
                preg_replace('`[^a-zA-Z0-9]`iUs', '', Tools::strtolower($newName))
            );

            /* # Api call */
            $oldList = $this->getApiOverlay()->createContactsListP($params);

            if ($oldList) {
                /* $listId = $oldList->ID; */
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @param int $mailjetListId
     */
    public function deleteList($mailjetListId)
    {
        if (!$mailjetListId) {
            return false;
        }
        return $this->getApiOverlay()->deleteContactsList($mailjetListId);
    }

    /**
     *
     * @param array $contacts
     * @param string $filterId
     * @param string $fiterName
     * @return mixed
     */
    private function create($res_contacts, $filterId, $fiterName)
    {
        $segmentationObject = new Segmentation();

        // ** ** Détection du bon Index
        $mail_index = 'Email';
        if ($res_contacts) {
            $contact_ids = array_keys($res_contacts[0]);
            foreach ($contact_ids as $k) {
                if (preg_match('/(mail)/', $k)) {
                    $mail_index = $k;
                } elseif ($k == $segmentationObject->ll(48)) {
                    $firstNameIndex = $k;
                } elseif ($k == $segmentationObject->ll(49)) {
                    $lastNameIndex = $k;
                }
            }
        }
        // ** **

        $newListId = $this->createNewMailjetList($filterId, $fiterName);

        if (!$newListId) {
            return false;
        }

        $total_contacts = count($res_contacts);
        if ($total_contacts === 0) {
            $responseMsg = 'No Result';
        }

        $contacts_done = 0;

        $contactsToAddSubscrubed = array();
        $contactsToAddUnsubscribed = array();
        // On va maintenant ajouter les contacts par 50 à la liste
        while (!empty($res_contacts)) {
            $reste_contacts = count($res_contacts);

            $val = 2500;
            if ($reste_contacts < $val) {
                $val = $reste_contacts;
            }

            for ($ic = 1; $ic <= $val; $ic++) {
                $rc = array_pop($res_contacts);
                if ((int) $rc['newsletter'] != 0) {
                    $sub = $rc;
                } else {
                    $unsub = $rc;
                }

                if (!empty($sub)) {
                    $contactsToAddSubscrubed[] = array(
                        'Email' => $sub[$mail_index],
                        'Properties' => array(
                            'firstname' => $sub[$firstNameIndex],
                            'lastname' => $sub[$lastNameIndex]
                        )
                    );
                }

                if (!empty($unsub)) {
                    $contactsToAddUnsubscribed[] = array(
                        'Email' => $unsub[$mail_index],
                        'Properties' => array(
                            'firstname' => $unsub[$firstNameIndex],
                            'lastname' => $unsub[$lastNameIndex]
                        )
                    );
                }
            }

            # Call
            try {
                if (!empty($sub)) {
                    $response = $this->getApiOverlay()->getApi()->{'contactslist/' . $newListId . '/managemanycontacts'}(
                            array(
                                'method' => 'JSON',
                                'Action' => 'addforce',
                                'Contacts' => $contactsToAddSubscrubed
                            )
                    );
                }

                if (!empty($unsub)) {
                    $response = $this->getApiOverlay()->getApi()->{'contactslist/' . $newListId . '/managemanycontacts'}(
                            array(
                                'method' => 'JSON',
                                'Action' => 'unsub',
                                'Contacts' => $contactsToAddUnsubscribed
                            )
                    );
                }

                $contacts_done += $val;

                Configuration::updateValue('MJ_PERCENTAGE_SYNC', floor(($contacts_done * 100) / $total_contacts));

                $responseMsg = $response->getResponse() && $response->getResponse()->Count > 0 ? 'OK' : '';
            } catch (Exception $e) {
                $responseMsg = 'Try again later';
            }
        }

        return $responseMsg;
    }

    /**
     *
     * @param array $contacts
     * @param int $existingListId
     * @return string
     */
    private function update($contacts, $existingListId)
    {
        $segmentationObject = new Segmentation();

        $mail_index = 'Email';
        if ($contacts) {
            $contact_ids = array_keys($contacts[0]);
            foreach ($contact_ids as $k) {
                if (preg_match('/(mail)/', $k)) {
                    $mail_index = $k;
                } elseif ($k == $segmentationObject->ll(48)) {
                    $firstNameIndex = $k;
                } elseif ($k == $segmentationObject->ll(49)) {
                    $lastNameIndex = $k;
                }
            }
        }

        $prestashopContacts = array();
        $prestashopUsers = array();
        $contactsToCsv = array();
        foreach ($contacts as $contact) {
            $contact[$mail_index] = Tools::strtolower($contact[$mail_index]);
            $prestashopContacts[] = $contact[$mail_index];
            if (!empty($contact[$mail_index])) {
                $contactsToCsv[$contact[$mail_index]]['firstname'] = $contact[$firstNameIndex];
                $contactsToCsv[$contact[$mail_index]]['lastname'] = $contact[$lastNameIndex];
                $prestashopUsers[$contact[$mail_index]]['newsletter'] = $contact['newsletter'];
            }
        }

        $this->gatherCurrentContacts($existingListId);

        $contactsToAdd = array();
        $contactsToAddUnsub = array();
        $contactsToRemove = array();

        foreach ($prestashopContacts as $email) {
            $email = Tools::strtolower($email);
            if (!in_array($email, $this->mailjetContacts)) {
                $contactData = array(
                    'Email' => $email,
                    'Properties' => $contactsToCsv[$email]
                );
                if ((int) $prestashopUsers[$email]['newsletter'] == 0) {
                    $contactsToAddUnsub[] = $contactData;
                } else {
                    $contactsToAdd[] = $contactData;
                }
            }
        }

        foreach ($this->mailjetContacts as $email) {
            if (!in_array($email, $prestashopContacts)) {
                $contactsToRemove[] = array(
                    'Email' => $email
                );
                //$contactsToRemove[] =  $email;
            }
        }

        $responseMsg = 'Pending';

        try {
            $response = false;
            if (!empty($contactsToRemove)) {
                $response = $this->getApiOverlay()->getApi()->{'contactslist/' . $existingListId . '/managemanycontacts'}(
                    array(
                        'method' => 'JSON',
                        'Action' => 'remove',
                        'Contacts' => $contactsToRemove
                    )
                );
            }

            if (!empty($contactsToAddUnsub)) {
                $response = $this->getApiOverlay()->getApi()->{'contactslist/' . $existingListId . '/managemanycontacts'}(
                    array(
                        'method' => 'JSON',
                        'Action' => 'unsub',
                        'Contacts' => $contactsToAddUnsub
                    )
                );
            }

            if (!empty($contactsToAdd)) {
                $response = $this->getApiOverlay()->getApi()->{'contactslist/' . $existingListId . '/managemanycontacts'}(
                    array(
                        'method' => 'JSON',
                        'Action' => 'addforce',
                        'Contacts' => $contactsToAdd
                    )
                );
            }

            if ($response) {
                $responseMsg = $response->getResponse() && $response->getResponse()->Count > 0 ? 'OK' : false;
            } else {
                $responseMsg = 'OK';
            }
        } catch (Exception $e) {
            $responseMsg = $e;
        }

        return $responseMsg;
    }

    /**
     *
     * @param string $filterId
     * @param string $fiterName
     * @return int
     */
    private function getExistingMailjetListId($filterId)
    {
        $lists = $this->getApiOverlay()->getContactsLists();

        $listId = 0;

        if ($lists !== false) {
            foreach ($lists as $l) {
                $n = explode('idf', $l->Name);

                if ((string) $n[0] == (string) $filterId) {
                    $listId = (int) $l->ID;
                    break;
                }
            }
        }

        return $listId;
    }

    /**
     *
     * @param string $filterId
     * @param string $fiterName
     * @return number
     */
    private function createNewMailjetList($filterId, $fiterName)
    {
        $listId = 0;

        $params = array(
            'method' => 'JSON',
            'Name' => $filterId . 'idf' . preg_replace('`[^a-zA-Z0-9]`iUs', '', Tools::strtolower($fiterName))
        );

        /* # Api call */
        $newList = $this->getApiOverlay()->createContactsListP($params);

        if ($newList) {
            $listId = $newList->ID;
        }

        return $listId;
    }

    /**
     *
     * @param int $mailjetListId
     */
    private function gatherCurrentContacts($mailjetListId, $offset = 0)
    {
        $params = array(
            'method' => 'GET',
            'ContactsList' => $mailjetListId,
            'style' => 'full',
            'CountRecords' => 1,
            'offset' => $offset,
            'limit' => $this->limitPerRequest,
        );

        $this->getApi()->resetRequest();
        $response = $this->getApi()->listrecipient($params)->getResponse();

        $totalCount = $response->Total;
        $current = $response->Count;

        foreach ($response->Data as $contact) {
            $this->mailjetContacts[] = $contact->Contact->Email->Email;
        }

        Configuration::updateValue('MJ_PERCENTAGE_SYNC', floor((($offset + $current) * 90) / $totalCount));

        if ($offset + $current < $totalCount) {
            $this->gatherCurrentContacts($mailjetListId, $offset + $this->limitPerRequest);
        }
    }
}
