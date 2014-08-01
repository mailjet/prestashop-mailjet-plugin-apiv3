<?php 

namespace Hooks\Synchronization;


/**
 * 
 * @author atanas
 */
class SingleUser extends SynchronizationAbstract
{
	
	
	/**
	 * 
	 * @param string $email
	 * @return boolean
	 */
	public function subscribe($email)
	{
		$api = $this->_getApi();
		$masterListId = $this->_getAlreadyCteatedMasterListId();

		$addParams = array(
			'method'  	=> 'JSON',
			'Action'  	=> 'Add',
			'Force'  	=> true,
			'Addresses' => array($email),
			'ListID'  	=> $masterListId
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
	public function unsubscribe($email)
	{
		$api = $this->_getApi();
		$masterListId = $this->_getAlreadyCteatedMasterListId();
		
		$addParams = array(
			'method'  	=> 'JSON',
			'Action'  	=> 'Unsubscribe',
			'Force'  	=> true,
			'Addresses' => array($email),
			'ListID'  	=> $masterListId
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
	public function remove($email)
	{
		$api = $this->_getApi();
		$masterListId = $this->_getAlreadyCteatedMasterListId();
	
		$addParams = array(
			'method'  	=> 'JSON',
			'Action'  	=> 'Remove',
			'Force'  	=> true,
			'Addresses' => array($email),
			'ListID'  	=> $masterListId
		);
		
		$api->resetRequest();
		$response = $api->manycontacts($addParams);

		if ($response && $response->Count > 0) {
			return true;
		}
	
		return false;
	}

}


?>