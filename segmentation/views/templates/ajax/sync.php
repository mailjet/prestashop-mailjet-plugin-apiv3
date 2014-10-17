<?php
/*
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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright  2007-2014 PrestaShop SA
* @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

include_once(realpath(dirname(__FILE__).'/../../../../../../').'/config/config.inc.php');
include_once(_PS_ROOT_DIR_.'/init.php');

$response = false;
//$tokenOK = Tools::getAdminTokenLite('AdminModules');
$tokenOK = Tools::getAdminToken('AdminModules'); // **

if (!Tools::getValue('token') && Tools::getValue('token') != $tokenOK)
	die("hack attempt");

if (Tools::getValue('idfilter') == 0 && Tools::getValue('action') == "getQuery") {
	die('You have to save the list first.');
}

include_once(realpath(dirname(__FILE__).'/../../..').'/segmentation.php');
//include_once(realpath(dirname(__FILE__).'/../../../..').'/classes/MailjetAPI.php');
include_once(realpath(dirname(__FILE__).'/../../../..').'/classes/MailJetTemplate.php'); // **
include_once(realpath(dirname(__FILE__).'/../../../..').'/hooks/synchronization/SynchronizationAbstract.php'); // **
include_once(realpath(dirname(__FILE__).'/../../../..').'/hooks/synchronization/Segment.php'); // **

if (Tools::getValue('action') == "getQuery")
{
	Configuration::updateValue("MJ_PERCENTAGE_SYNC", 0);
	$obj = new Segmentation();

	$res_contacts = Db::getInstance()->executeS($obj->getQuery($_POST, true, false));
	
	

// 	echo '<pre>';
// 	print_r($res_contacts);
// 	echo 'users';
	
	
	//$account = unserialize(Configuration::get('MAILJET'));

	//$api = new MailjetAPI($account['API_KEY'], $account['SECRET_KEY']);
	$api = MailjetTemplate::getApi(); // **
	
	$synchronization = new Hooks_Synchronization_Segment(
		MailjetTemplate::getApi()
	);
	
	$response = $synchronization->sychronize($res_contacts, Tools::getValue('idfilter'), Tools::getValue('name'));

// 	//$api->data('contactslist', 2, 'CSVData', 'text/plain', null, 'GET', 2);
// 	//$call_data = $this->mailjetdata->DATA('POST', 'ContactsList', $this->list_id, 'CSVData', 'text/csv', NULL, $contacts, get_app_id());
	
// 	//$responesProfile = $api->getResponse();
	
// 	//var_dump($responesProfile); die;
// 	// On regarde si la liste n'existe pas déjà
// 	//$response = $api->listsAll();

// 	$lists = $api->getContactsLists();

// 	$id_list_contact = 0;
	
// 	if ($lists !== false) 
// 	{
// 		foreach ($lists as $l)
// 		{
// 			$n = explode("idf", $l->Name);
	
// 			if ((string)$n[0] == (string)$_POST['idfilter'])
// 			{
// 				$id_list_contact = (int)$l->ID;
// 				break;
// 			}
// 		}
// 	}

// 	$params = array(
// 		'method' => 'JSON',
// 		'Name' => $_POST['idfilter']."idf".preg_replace("`[^a-zA-Z0-9]`iUs", "", Tools::strtolower($_POST['name']))
// 	);
	
// 	if ($id_list_contact != 0) {
// 		$params['ID'] = $id_list_contact;
// 	} 

// 	# Call
// 	//$response = $api->listsCreate($params);
// 	$newList = $api->createContactsListP($params); // **
// 	// 		echo '<pre>';
// 	// 		print_r($newList);
// 	// 		echo 'list added';
// 	if ($newList) {
// 		# Result
// 		$id_list_contact = $newList->ID;
// 	}


// //	$contacts = $api->getContactsFromList($id_list_contact);

// 	//echo 'contactlistID:' . $id_list_contact;
// 	if (!$id_list_contact) {
// 		echo 'An error occured';
// 		exit;
// 	}

// 	$total_contacts = count($res_contacts);
// 	if ($total_contacts === 0) {
// 		$response = 'No Result';
// 	}
// 	$contacts_done = 0;
	
// 	// On va maintenant ajouter les contacts par 50 à la liste
// 	while (!empty($res_contacts))
// 	{
// 		$reste_contacts = count($res_contacts);
		
// 		$val = 50;
// 		if ($reste_contacts < $val) $val = $reste_contacts;
		
// 		$selected_contacts = array();
// 		for ($ic = 1; $ic <= 50 ; $ic++)
// 		{
// 			$rc = array_pop($res_contacts);
// 			$selected_contacts[] = $rc['Email'];
// 		}
	
// 		$string_contacts = implode(" ", $selected_contacts);

// 		$params = array(
// 			'method' 	=> 'JSON',
// 			'contacts' 	=> $string_contacts,
// 			'id' 		=> $id_list_contact
// 		);
// 		# Call
// 		// $response = $api->listsAddManyContacts($params);
		
// 		try {
// 			$res = $api->createContacts($params['contacts'], $params['id']); // **

// 			if (!isset($res->ID)) {
// 				throw new Exception("Create contacts problem");
// 			}
			
// 			$batchJobResponse = $api->batchJobContacts($id_list_contact, $res->ID);
		
// 			if ($batchJobResponse == false) {
// 				throw new Exception("Batchjob problem");
// 			}
			
// 			$contacts_done += $val;
			
// 			Configuration::updateValue("MJ_PERCENTAGE_SYNC", floor(($contacts_done*100)/$total_contacts));
// 			$response = 'OK';
// 		} catch (Exception $e) {
// 			$response = 'Try again later';
// 		}

// 	}
} else if (Tools::getValue('action') == "getPercentage")
{
	$response = Configuration::get("MJ_PERCENTAGE_SYNC");
}

if ($response === false) {
	$response = 'Error';
}

echo $response; 
?>