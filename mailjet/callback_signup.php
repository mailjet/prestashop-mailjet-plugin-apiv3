<?php

require_once(realpath(dirname(__FILE__).'/../../config/config.inc.php'));

if (_PS_VERSION_ < '1.5' || !defined('_PS_ADMIN_DIR_'))
	require_once(realpath(dirname(__FILE__).'/../../init.php'));

require_once(dirname(__FILE__).'/mailjet.php');

$mj = new Mailjet();
//mail("astoyanov@mailjet.com", "", print_r($_POST, true));
if (isset($_POST['data']))
{
	if (isset($_POST['data']['apikey']))
	{
		$mj->account['API_KEY'] = $_POST['data']['apikey'];
		$mj->account['SECRET_KEY'] = $_POST['data']['secretkey'];

		$mj->account['AUTHENTICATION'] = 1;
		$mj->auth($_POST['data']['apikey'], $_POST['data']['secretkey']);
		
		$mj->updateAccountSettings();
		$mj->activateAllEmailMailjet();
	}
	
	if ((isset($_POST['data']['previous_step_url'])) && $_POST['data']['previous_step_url'] == "prestashop/signup/activate")
	{
		$link = new Link();
		$moduleTabRedirectLink = @$link->getAdminLink('moduleTabRedirect', true);

		$response = array(
					"code"				=> 1,
					"continue"			=> false,
					"exit_url"			=> "http://".Configuration::get('PS_SHOP_DOMAIN')."/admin_dmu/".$moduleTabRedirectLink,
					//"exit_url"			=> "http://mailjet.dream-me-up.fr/admin_dmu/index.php?controller=moduleTabRedirect&token=3d9d49481e6ca3a14998cd44ddc0b878",
		);

	} else if (isset($_POST['data']['next_step_url']) && $_POST['data']['next_step_url'] == 'reseller/signup/welcome') {
		$response = array(
					"code"				=> 1,
					"continue"			=> true,
					"continue_address"	=> 'campaigns',
		);
	} else {
		$response = array(
				"code"				=> 1,
				"continue"			=> true,
				"continue_address"	=> $_POST['data']['next_step_url'],
		);
	}
	
	//mail("astoyanov@mailjet.com", "", print_r($_POST, true).print_r($response, true));

	echo Tools::jsonEncode($response);
}

?>