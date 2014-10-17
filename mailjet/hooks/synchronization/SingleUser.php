<?php 

/**
 * 
 * @author atanas
 */
class Hooks_Synchronization_SingleUser extends Hooks_Synchronization_SynchronizationAbstract
{
	/**
	 * 
	 * @param string $email
	 * @return boolean
	 */
	public function subscribe($email, $listId = null)
	{
		$api = $this->_getApi();
		$updateListId = $listId ? $listId : $this->_getAlreadyCteatedMasterListId();

		$addParams = array(
			'method'  	=> 'JSON',
			'Action'  	=> 'Add',
			'Force'  	=> true,
			'Addresses' => array($email),
			'ListID'  	=> $updateListId
		);
		
		$api->resetRequest();
		$response = $api->manycontacts($addParams);
		
		if ($response && $response->Count > 0) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * 
	 * @param string $email
	 * @return boolean
	 */
	public function unsubscribe($email, $listId = null)
	{

		$api = $this->_getApi();
		
		if ($listId) {
			$addParams = array(
				'method'  	=> 'JSON',
				'Action'  	=> 'Unsubscribe',
				'Force'  	=> true,
				'Addresses' => array($email),
				'ListID'  	=> $listId
			);
			
			$api->resetRequest();
			$response = $api->manycontacts($addParams);
		} else {
			$apiOverlay = $this->_getApiOverlay();
			
			$lists = $apiOverlay->getContactsLists();
			
			foreach ($lists as $list) {
				$addParams = array(
					'method'  	=> 'JSON',
					'Action'  	=> 'Unsubscribe',
					'Force'  	=> true,
					'Addresses' => array($email),
					'ListID'  	=> $list->ID
				);
				
				$api->resetRequest();
				$response = $api->manycontacts($addParams);
			}
		}

		if ($response && $response->Count > 0) {
			return true;
		}
	
		return false;
	}

	
	/**
	 *
	 * @param string $email
	 * @return boolean
	 */
	public function remove($email, $listId = null)
	{

		$api = $this->_getApi();
		
		if ($listId) {
			$addParams = array(
				'method'  	=> 'JSON',
				'Action'  	=> 'Remove',
				'Force'  	=> true,
				'Addresses' => array($email),
				'ListID'  	=> $listId
			);
			
			$api->resetRequest();
			$response = $api->manycontacts($addParams);
		} else {
			$apiOverlay = $this->_getApiOverlay();
			
			$lists = $apiOverlay->getContactsLists();
			
			foreach ($lists as $list) {
				$addParams = array(
					'method'  	=> 'JSON',
					'Action'  	=> 'Remove',
					'Force'  	=> true,
					'Addresses' => array($email),
					'ListID'  	=> $list->ID
				);
				
				$api->resetRequest();
				$response = $api->manycontacts($addParams);
			}
		}

		if ($response && $response->Count > 0) {
			return true;
		}
	
		return false;
	}
}
?>