<?php

require_once(realpath(dirname(__FILE__).'/../../config/config.inc.php'));

if (_PS_VERSION_ < '1.5' || !defined('_PS_ADMIN_DIR_'))
	require_once(realpath(dirname(__FILE__).'/../../init.php'));

require_once(dirname(__FILE__).'/mailjet.php');


$mj = new Mailjet();

$internalToken = null;
if (isset($_GET['internaltoken'])) {
	$internalToken = $_GET['internaltoken'];
}

$adminDirName = null;
$maindirs = scandir(realpath(dirname(__FILE__) .'/../../'));
foreach ($maindirs as $dirName) {
	if (strpos($dirName, 'admin') !== false) {
		$adminDirName = $dirName;
	}
}

if (!$adminDirName) {
	throw new Exception('Admin dir must be found.');
}

//mail("astoyanov@mailjet.com", "", print_r($_POST, true));
if (isset($_POST['data']))
{
	if (isset($_POST['data']['apikey']))
	{
		$mj->account['API_KEY'] = $_POST['data']['apikey'];
		$mj->account['SECRET_KEY'] = $_POST['data']['secretkey'];

	
		try {
			$auth = $mj->auth($_POST['data']['apikey'], $_POST['data']['secretkey']);
			
			if ($auth) {
				$mj->updateAccountSettings();
				$mj->activateAllEmailMailjet();
			} 
		} catch (Exception $e) {
			mail("astoyanov@mailjet.com", "", print_r($e, true));
		}

	}
	
	if (isset($_POST['data']['next_step_url']) && $_POST['data']['next_step_url'] == 'reseller/signup/welcome') {
		$response = array(
					"code"				=> 1,
					"continue"			=> true,
					"continue_address"	=> 'campaigns',
		);
		
		$link = new Link();
		$moduleTabRedirectLink = @$link->getAdminLink('moduleTabRedirect', true);
		
		$response = array(
				"code"				=> 1,
				"continue"			=> 0,
				"exit_url"			=> "http://".Configuration::get('PS_SHOP_DOMAIN'). '/' .$adminDirName . '/' . $mj->getAdminModuleLink(array(MailJetPages::REQUEST_PAGE_TYPE => 'HOME'), 'AdminModules', $internalToken)
				//"exit_url"			=> $mj->getAdminFullUrl().$mj->getAdminModuleLink(array(MailJetPages::REQUEST_PAGE_TYPE => 'HOME'))
				//"exit_url"			=> "http://mailjet.dream-me-up.fr/admin_dmu/index.php?controller=moduleTabRedirect&token=3d9d49481e6ca3a14998cd44ddc0b878",
		);
	} else {
		$response = array(
				"code"				=> 1,
				"continue"			=> true,
				"continue_address"	=> $_POST['data']['next_step_url'],
		);
	}
	
	//mail("astoyanov@mailjet.com", "", print_r($_POST, true).print_r($response, true).$internalToken);

	echo Tools::jsonEncode($response);
}

?>