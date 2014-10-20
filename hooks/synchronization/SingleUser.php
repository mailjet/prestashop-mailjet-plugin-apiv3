<?php
/**
 * 2007-2014 PrestaShop
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
 * @copyright 2007-2014 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
*/

/**
 * 
 * @author atanas
 */
class HooksSynchronizationSingleUser extends HooksSynchronizationSynchronizationAbstract
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
		
		if ($response && $response->Count > 0)
			return true;
		
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
		
		if ($listId)
		{
			$addParams = array(
				'method'  	=> 'JSON',
				'Action'  	=> 'Unsubscribe',
				'Force'  	=> true,
				'Addresses' => array($email),
				'ListID'  	=> $listId
			);
			
			$api->resetRequest();
			$response = $api->manycontacts($addParams);
		}
		else
		{
			$apiOverlay = $this->_getApiOverlay();
			
			$lists = $apiOverlay->getContactsLists();
			
			foreach ($lists as $list)
			{
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

		if ($response && $response->Count > 0)
			return true;
	
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
		
		if ($listId)
		{
			$addParams = array(
				'method'  	=> 'JSON',
				'Action'  	=> 'Remove',
				'Force'  	=> true,
				'Addresses' => array($email),
				'ListID'  	=> $listId
			);
			
			$api->resetRequest();
			$response = $api->manycontacts($addParams);
		}
		else
		{
			$apiOverlay = $this->_getApiOverlay();
			
			$lists = $apiOverlay->getContactsLists();
			
			foreach ($lists as $list)
			{
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

		if ($response && $response->Count > 0)
			return true;
	
		return false;
	}
}
?>