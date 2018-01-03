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

class HooksSynchronizationInitial extends HooksSynchronizationSynchronizationAbstract
{

    /**
     *
     * @throws Exception
     * @return int
     */
    public function synchronize()
    {
        $masterListId = $this->getAlreadyCreatedMasterListId();
        if ($masterListId) {
            $segmentSynch = new HooksSynchronizationSegment($this->getApiOverlay());
            $is_master_deleted = $segmentSynch->deleteList($masterListId);
            if (!$is_master_deleted) {
                throw new HooksSynchronizationException('Master list is not deleted!');
            }
        }

        $apiOverlay = $this->getApiOverlay();

        $params = array(
            'method' => 'JSON',
            'Name' => self::LIST_NAME
        );

        $newMailjetList = $apiOverlay->createContactsListP($params);

        if (!$newMailjetList || !isset($newMailjetList->ID)) {
            throw new HooksSynchronizationException('There is a problem with the list\'s creation.');
        }

        $newlyCreatedListId = $newMailjetList->ID;

        if (!is_numeric($newlyCreatedListId)) {
            throw new HooksSynchronizationException('The API response is not correct.');
        }
        // increase the memory limit because the database could contain too many customers
        ini_set('memory_limit', '1028M');
        $allUsers = $this->getAllActiveCustomers();

        if (count($allUsers) === 0) {
            throw new HooksSynchronizationException('You don\'t have any users in the database.');
        }

        while (!empty($allUsers)) {
            $rest_contacts = count($allUsers);

            $chunck_size = 2500;
            if ($rest_contacts < $chunck_size) {
                $chunck_size = $rest_contacts;
            }

            $contacts = array();
            for ($i = 1; $i <= $chunck_size; $i++) {
                $userInfo = array_pop($allUsers);
                $contacts[] = array(
                    'Email' => $userInfo['email'],
                    'Properties' => array(
                        'firstname' => $userInfo['firstname'],
                        'lastname' => $userInfo['lastname']
                    )
                );
            }

            /*
             * Sets related contact meta data like firstname, lastname, etc...
             */
            $this->getApiOverlay()->setContactMetaData(array(
                array('Datatype' => 'str', 'Name' => 'firstname', 'NameSpace' => 'static'),
                array('Datatype' => 'str', 'Name' => 'lastname', 'NameSpace' => 'static')
            ));

            $asyncJobResponse = $apiOverlay->asyncManageContactsToList($contacts, $newlyCreatedListId);

            if (!isset($asyncJobResponse->Data[0]->JobID)) {
                $segmentSynch = new HooksSynchronizationSegment($this->getApiOverlay());
                $segmentSynch->deleteList($newlyCreatedListId);
                throw new HooksSynchronizationException('There is a problem with the creation of the contacts.');
            }
            $batchJobResponse = $apiOverlay->getAsyncJobStatus($newlyCreatedListId, $asyncJobResponse);

            if ($batchJobResponse == false) {
                throw new HooksSynchronizationException('Batchjob problem');
            }
        }

        return $newlyCreatedListId;
    }

    /**
     *
     * @return array
     */
    private function getAllActiveCustomers()
    {
        return $this->getDbInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'customer WHERE active = 1 AND newsletter = 1 AND deleted = 0');
    }
}
