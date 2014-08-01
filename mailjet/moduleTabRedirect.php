<?php

if (version_compare(_PS_VERSION_,'1.5','<')) 
	include_once(realpath(PS_ADMIN_DIR.'/../').'/classes/AdminTab.php');
	
class moduleTabRedirect extends AdminTab
{
	public function __construct()
	{
		$token = Tools::getAdminTokenLite('AdminModules');
		$url = Dispatcher::getInstance()->createUrl('AdminModules', 1, array('token'=>$token), false);
		
		Tools::redirectAdmin($url.'&configure=mailjet');
	}

}