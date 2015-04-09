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

require_once(realpath(dirname(__FILE__).'/../../config/config.inc.php'));
if (_PS_VERSION_ < '1.5' || !defined('_PS_ADMIN_DIR_'))
	require_once(realpath(dirname(__FILE__).'/../../init.php'));


if (Tools::getIsset('data'))
	$data = (object)Tools::getValue('data');
else if (Tools::getIsset('mailjet'))
{
	$mailjet = Tools::jsonDecode(Tools::getValue('mailjet'));
	$data = $mailjet->data;
}
else
	$data = new stdClass();

require_once(_PS_MODULE_DIR_.'mailjet/mailjet.php');
require_once(_PS_MODULE_DIR_.'mailjet/classes/MailJetLog.php');

$mj = new Mailjet();

MailJetLog::init();

if (Tools::getIsset('response'))
	$response = (object)Tools::getValue('response');

if ($data->next_step_url)
{
	if (!empty($response) && ($response->message == 'last change of campaigns parameters' || $response->message == 'send details saved successfully'))
	{
		$mj_data = new Mailjet_Api($mj->getAccountSettingsKey('API_KEY'), $mj->getAccountSettingsKey('SECRET_KEY'));
        $campaignId = (int)$data->campaign_id;
		$html = $mj_data->data('newsletter', $campaignId, 'HTML', 'text/html', null, 'GET', 'LAST')->getResponse();
		
		/* On enregistre la campagne en BDD et on génère un token */
		$sql = 'SELECT * FROM '._DB_PREFIX_.'mj_campaign WHERE campaign_id = '.$campaignId;
		$res_campaign = Db::getInstance()->GetRow($sql);

		if (empty($res_campaign))
		{
			$token_presta = md5(uniqid('mj', true));
			$sql = 'INSERT INTO '._DB_PREFIX_.'mj_campaign (campaign_id, token_presta, date_add)
			VALUES ('.$campaignId.', \''.$token_presta.'\', NOW())';
			Db::getInstance()->Execute($sql);

			$sql = 'SELECT * FROM '._DB_PREFIX_.'mj_campaign WHERE campaign_id = '.$campaignId;
			$res_campaign = Db::getInstance()->GetRow($sql);
		}

		$regexp = '<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>';
		preg_match_all("/$regexp/siU", $html, $liens);

		$changed_html = false;

		foreach ($liens[2] as $key => $l)
		{
			$url = str_replace('.', '\.', str_replace('-', '\-', Configuration::get('PS_SHOP_DOMAIN')));

			/* On cherche si on a un lien vers le site et sans le token */
			if (preg_match('`'.$url.'`iUs', $l))
			{
				$changed_html = true;

				$link_without_token = $liens[2][$key];
				if (preg_match('`tokp`iUs', $l))
					$link_without_token = preg_replace('`(\?tokp=.+$)`iUs', '', $link_without_token);

				if (!preg_match('`\?`iUs', $liens[2][$key]))
					$link_with_token = $link_without_token.'?tokp='.$res_campaign['token_presta'];
				else
					$link_with_token = $link_without_token.'&tokp='.$res_campaign['token_presta'];

				$html = str_replace($link_without_token, $link_with_token, $html);
			}
		}
				
		$res = $mj_data->data('newsletter', $campaignId, 'HTML', 'text/html', $html, 'PUT', 'LAST')->getResponse();
	}
	
	$response = array(
		'code'				=> 1,
		'continue'			=> true,
		'continue_address'	=> $data->next_step_url,
	);
}
else
{
	$response = array(
		'code'		=> 0,
		'continue'	=> false,
		'exit_url'	=> 'SOME URL ADDRESS',
	);
}

header('Content-Type: application/json');
echo Tools::jsonEncode($response);

?>
