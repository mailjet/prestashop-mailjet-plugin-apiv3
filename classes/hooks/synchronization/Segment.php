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
class HooksSynchronizationSegment extends HooksSynchronizationSynchronizationAbstract
{
	/**
	 * 
	 * @var array
	 */
	private $_mailjetContacts = array();

	/**
	 * 
	 * @var int
	 */
	private $_limitPerRequest = 2;

	/**
	 * 
	 * @param array $contacts
	 * @param string $filterId
	 * @param string $fiterName
	 */
	public function sychronize($contacts, $filterId, $fiterName)
	{
		$existingListId = $this->_getExistingMailjetListId($filterId);

		if ($existingListId)
			return $this->_update($contacts, $existingListId);

		return $this->_create($contacts, $filterId, $fiterName);
	}

	/**
	 * 
	 * @param int $filterId
	 * @param string $newName	 
	 * @return bool
	 */
	public function updateName($mailjetListId, $prestashopFilterId, $newName)
	{
		if ($mailjetListId)
		{

			$params = array(
				'ID'		=> $mailjetListId,
				'method' 	=> 'JSON',
				'Name' 		=> $prestashopFilterId.'idf'.preg_replace('`[^a-zA-Z0-9]`iUs', '', Tools::strtolower($newName))
			);

			/* # Api call */
			$oldList = $this->_getApiOverlay()->createContactsListP($params);

			if ($oldList)
			{
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
		if ($mailjetListId)
		{
			$params = array(
				'ID'		=> $mailjetListId,
				'method' 	=> 'DELETE'
			);

			/* # Api call */
			/* $oldList = */
				$this->_getApiOverlay()->createContactsListP($params);

			return true;
		}

		return false;
	}

	/**
	 * 
	 * @param array $contacts
	 * @param string $filterId
	 * @param string $fiterName
	 * @return mixed
	 */
	private function _create($res_contacts, $filterId, $fiterName)
	{        
        $segmentationObject = new Segmentation();
            
		// ** ** Détection du bon Index
		$mail_index = 'Email';
		if ($res_contacts)
		{
			$contact_ids = array_keys($res_contacts[0]);
			foreach ($contact_ids as $k) {
                if (preg_match('/(mail)/', $k))  {
                    $mail_index = $k;
                } else if ($k == $segmentationObject->ll(48)) {
                    $firstNameIndex = $k;
                } else if ($k == $segmentationObject->ll(49)) {
                    $lastNameIndex = $k;
                }  
            }

		}
		// ** **

		$newListId = $this->_createNewMailjetList($filterId, $fiterName);

		if (!$newListId)
			return false;

		$total_contacts = count($res_contacts);
		if ($total_contacts === 0)
			$response = 'No Result';

		$contacts_done = 0;

		// On va maintenant ajouter les contacts par 50 à la liste
		while (!empty($res_contacts))
		{
			$reste_contacts = count($res_contacts);

			$val = 50;
			if ($reste_contacts < $val) $val = $reste_contacts;

            $contactsToCsv = array();
			for ($ic = 1; $ic <= 50; $ic++)
			{
				$rc = array_pop($res_contacts);
                if (!empty($rc[$mail_index])) {
                    $contactsToCsv[] = array($rc[$mail_index], $rc[$firstNameIndex], $rc[$lastNameIndex]);
                }
               
            }
           
			# Call
			try {
                
                 
                $headers = array("email","firstname","lastname");
                $string_contacts = '';
                $string_contacts .= implode(",", $headers) ."\n";
                foreach ($contactsToCsv as $contact) {
                    $string_contacts .= implode(",", $contact) ."\n";
                }

                /*
                 * Sets related contact meta data like firstname, lastname, etc...
                 */
                $this->_getApiOverlay()->setContactMetaData(
                    array(
                        array('Datatype' => 'str', 'Name' => 'firstname', 'NameSpace' => 'static'), 
                        array('Datatype' => 'str', 'Name' => 'lastname', 'NameSpace' => 'static')
                    )
                );
                
				$res = $this->_getApiOverlay()->createContacts($string_contacts, $newListId);
                
				if (!isset($res->ID))
					throw new HooksSynchronizationException('Create contacts problem');

               
				$batchJobResponse = $this->_getApiOverlay()->batchJobContacts($newListId, $res->ID);

				if ($batchJobResponse == false)
					throw new HooksSynchronizationException('Batchjob problem');

				$contacts_done += $val;

				Configuration::updateValue('MJ_PERCENTAGE_SYNC', floor(($contacts_done * 100) / $total_contacts));
                
				$response = 'OK';
			} catch (Exception $e) {
				$response = 'Try again later';
			}
		}

		return $response;
	}

	/**
	 * 
	 * @param array $contacts
	 * @param int $existingListId
	 * @return string
	 */
	private function _update($contacts, $existingListId)
	{
        $segmentationObject = new Segmentation();
        
		// ** ** Détection du bon Index
		$mail_index = 'Email';
		if ($contacts)
		{
			$contact_ids = array_keys($contacts[0]); 
			foreach ($contact_ids as $k) {
                if (preg_match('/(mail)/', $k))  {
                    $mail_index = $k;
                } else if ($k == $segmentationObject->ll(48)) {
                    $firstNameIndex = $k;
                } else if ($k == $segmentationObject->ll(49)) {
                    $lastNameIndex = $k;
                }  
            }
		}
        
		$prestashopContacts = array();
        $contactsToCsv = array();
		foreach ($contacts as $contact) {
            $prestashopContacts[] = $contact[$mail_index];
            if (!empty($contact[$mail_index])) {
                $contactsToCsv[$contact[$mail_index]] = array($contact[$mail_index], $contact[$firstNameIndex], $contact[$lastNameIndex]);
            }
        }

		$this->_gatherCurrentContacts($existingListId);

		$contacstToAdd = array();
		$contacstToRemove = array();

		foreach ($prestashopContacts as $email)
		{
			if (!in_array($email, $this->_mailjetContacts))
				$contacstToAdd[] = $contactsToCsv[$email];
		}

		foreach ($this->_mailjetContacts as $email)
		{
			if (!in_array($email, $prestashopContacts))
				$contacstToRemove[] = $email;
		}

		$response = 'Pending';
 
		try {
			if (!empty($contacstToAdd))
			{
                /*
                 * Sets related contact meta data like firstname, lastname, etc...
                 */
                $this->_getApiOverlay()->setContactMetaData(
                    array(
                        array('Datatype' => 'str', 'Name' => 'firstname', 'NameSpace' => 'static'), 
                        array('Datatype' => 'str', 'Name' => 'lastname', 'NameSpace' => 'static')
                    )
                );
                
                $headers = array("email","firstname","lastname");
                $contstToAddCsv = '';
                $contstToAddCsv .= implode(",", $headers) ."\n";
                foreach ($contactsToCsv as $contact) {
                    $contstToAddCsv .= implode(",", $contact) ."\n";
                }

				$res = $this->_getApiOverlay()->createContacts($contstToAddCsv, $existingListId);
                
				if (!isset($res->ID))
					throw new HooksSynchronizationException('Create contacts problem');

                 
				$batchJobResponse = $this->_getApiOverlay()->batchJobContacts($existingListId, $res->ID, 'addforce');

				if ($batchJobResponse == false)
					throw new HooksSynchronizationException('Batchjob problem');
                  
			}

			if (!empty($contacstToRemove))
			{
				$contstToRemoveCsv = implode(' ', $contacstToRemove);

				$res = $this->_getApiOverlay()->createContacts($contstToRemoveCsv, $existingListId);

				if (!isset($res->ID))
					throw new HooksSynchronizationException('Create contacts problem');

				$batchJobResponse = $this->_getApiOverlay()->batchJobContacts($existingListId, $res->ID, 'remove');

				if ($batchJobResponse == false)
					throw new HooksSynchronizationException('Batchjob problem');
			}

			$response = 'OK';
		} catch (Exception $e) {
			$response = $e;
		}

		return $response;
	}

	/**
	 * 
	 * @param string $filterId
	 * @param string $fiterName
	 * @return int
	 */
	private function _getExistingMailjetListId($filterId)
	{
		$lists = $this->_getApiOverlay()->getContactsLists();

		$listId = 0;

		if ($lists !== false)
		{
			foreach ($lists as $l)
			{
				$n = explode('idf', $l->Name);

				if ((string)$n[0] == (string)$filterId)
				{
					$listId = (int)$l->ID;
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
	private function _createNewMailjetList($filterId, $fiterName)
	{
		$listId = 0;

		$params = array(
			'method' 	=> 'JSON',
			'Name' 		=> $filterId.'idf'.preg_replace('`[^a-zA-Z0-9]`iUs', '', Tools::strtolower($fiterName))
		);

		/* # Api call */
		$newList = $this->_getApiOverlay()->createContactsListP($params);

		if ($newList)
			$listId = $newList->ID;

		return $listId;
	}

	/**
	 * 
	 * @param int $mailjetListId
	 */
	private function _gatherCurrentContacts($mailjetListId, $offset = 0)
	{
		$params = array(
			'method'			=> 'GET',
			'ContactsList'		=> $mailjetListId,
			'style'				=> 'full',
			'CountRecords'		=> 1,
			'offset'			=> $offset,
			'limit'				=> $this->_limitPerRequest,
		);

		$this->_getApi()->resetRequest();
		$response = $this->_getApi()->listrecipient($params)->getResponse();

		$totalCount = $response->Total;
		$current 	= $response->Count;

		foreach ($response->Data as $contact)
			$this->_mailjetContacts[] = $contact->Contact->Email->Email;

		Configuration::updateValue('MJ_PERCENTAGE_SYNC', floor((($offset + $current) * 90) / $totalCount));

		if ($offset + $current < $totalCount)
			$this->_gatherCurrentContacts($mailjetListId, $offset + $this->_limitPerRequest);
	}

}
?>