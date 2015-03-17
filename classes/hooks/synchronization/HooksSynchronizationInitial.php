<?php
/**
 * 2007-2015 PrestaShop
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
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
*/

/**
 * 
 * @author atanas
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
		if ($masterListId = $this->_getAlreadyCteatedMasterListId())
		{
			$segmentSynch = new HooksSynchronizationSegment($this->_getApiOverlay());
			$segmentSynch->deleteList($masterListId);
		}

		$apiOverlay = $this->_getApiOverlay();

		$params = array(
			'method' 	=> 'JSON',
			'Name' 		=> self::LIST_NAME
		);

		$newMailjetList = $apiOverlay->createContactsListP($params);

		if (!$newMailjetList || !isset($newMailjetList->ID))
			throw new HooksSynchronizationException('There is a problem with the list\'s creation.');

		$newlyCreatedListId = $newMailjetList->ID;

		if (!is_numeric($newlyCreatedListId)) {
            throw new HooksSynchronizationException('The API response is not correct.');
        }

		$allUsers = $this->_getAllActiveCustomers();

		if (count($allUsers) === 0) {
            throw new HooksSynchronizationException('You don\'t have any users in the database.');
        }

        $segmentationObject = new Segmentation();
        /*
        * Sets related contact meta data like firstname, lastname, etc...
        */
        $this->_getApiOverlay()->setContactMetaData(array(
            array('Datatype' => 'str', 'Name' => $segmentationObject->ll(48), 'NameSpace' => 'static'),
            array('Datatype' => 'str', 'Name' => $segmentationObject->ll(49), 'NameSpace' => 'static')
        ));

        $csvStr = 'email,'.$segmentationObject->ll(48).','.$segmentationObject->ll(49)."\n";
        foreach ($allUsers as $contact) {
            $csvStr .= implode(',',array($contact['email'],$contact['firstname'],$contact['lastname']))."\n";
        }

		$apiResponse = $apiOverlay->createContacts($csvStr, $newlyCreatedListId);

		if (!isset($apiResponse->ID)) {
			$segmentSynch = new HooksSynchronizationSegment($this->_getApiOverlay());
			$segmentSynch->deleteList($newlyCreatedListId);
			throw new HooksSynchronizationException('There is a problem with the creation of the contacts.');
		}

		$batchJobResponse = $apiOverlay->batchJobContacts($newlyCreatedListId, $apiResponse->ID);

		if ($batchJobResponse == false) {
            throw new HooksSynchronizationException('Batchjob problem');
        }

		return $newlyCreatedListId;
	}

	/**
	 * 
	 * @return array
	 */
	private function _getAllActiveCustomers()
	{
		return $this->getDbInstance()->executeS('
			SELECT * 
			FROM '._DB_PREFIX_.'customer 
			WHERE active = 1 
			AND deleted = 0
		');
	}

}

?>