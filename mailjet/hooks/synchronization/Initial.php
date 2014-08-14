<?php 

namespace Hooks\Synchronization;


/**
 * 
 * @author atanas
 */
class Initial extends SynchronizationAbstract
{
	

	/**
	 * 
	 * @throws Exception
	 * @return int
	 */
	public function synchronize()
	{
		if ($masterListId = $this->_getAlreadyCteatedMasterListId()) {
			$segmentSynch = new Segment($this->_getApiOverlay());
			$segmentSynch->deleteList($masterListId);
		}
		
		$apiOverlay = $this->_getApiOverlay();
		
		$params = array(
			'method' 	=> 'JSON',
			'Name' 		=> self::LIST_NAME
		);
		
		$newMailjetList = $apiOverlay->createContactsListP($params);
		
		if (!$newMailjetList || !isset($newMailjetList->ID)) {
			throw new Exception("There is a problem with the list's creation.");
		}
		
		$newlyCreatedListId = $newMailjetList->ID;
		
		if (!is_numeric($newlyCreatedListId)) {
			throw new Exception("The API response is not correct.");
		}
		
		
		$allUsers = $this->_getAllActiveCustomers();
		
		if (count($allUsers) === 0) {
			throw new Exception("You don't have any users in the database.");
		}
		
		$contacts = array();
		
		foreach ($allUsers as $user) {
			$contacts[] = $user['email'];
		}
		
		$stringContacts = implode(" ", $contacts);
		
		$apiResponse = $apiOverlay->createContacts(
			$stringContacts, $newlyCreatedListId
		);
		
		if (!isset($apiResponse->ID)) {
			$segmentSynch = new Segment($this->_getApiOverlay());
			$segmentSynch->deleteList($newlyCreatedListId);
			
			throw new Exception("There is a problem with the creation of the contacts.");
		}
			
		$batchJobResponse = $apiOverlay->batchJobContacts(
			$newlyCreatedListId, $apiResponse->ID
		);
		
		if ($batchJobResponse == false) {
			throw new Exception("Batchjob problem");
		}
		
		return $newlyCreatedListId;
	}
	
	
	/**
	 * 
	 * @return array
	 */
	private function _getAllActiveCustomers()
	{
		return $this->getDbInstance()->executeS("
			SELECT email 
			FROM "._DB_PREFIX_."customer 
			WHERE active = 1 
			AND deleted = 0
		");
	}

}


?>