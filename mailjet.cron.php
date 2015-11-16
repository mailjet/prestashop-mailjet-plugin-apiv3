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

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

require_once(_PS_MODULE_DIR_.'mailjet/mailjet.php');

$mailjet = new Mailjet();

if (Tools::getValue('token') !== Configuration::get('SEGMENT_CUSTOMER_TOKEN'))
	die('No hackers allowed here ! ;-)');

function utf8Outtags($text)
{
	$notags = strip_tags($text);
	$utf8 = htmlentities($notags);
	preg_match_all('/&([0-9A-Za-z]+);/', $utf8, $res);
	foreach ($res[0] as $preg)
		$text = str_replace($preg, html_entity_decode($preg), $text);
	return $text;
}

function findCode($code)
{
	if (version_compare(_PS_VERSION_, '1.5', '>='))
		return CartRule::getIdByCode($code);
	else
		return Discount::getIdByName($code);
}

if ($mailjet->triggers['active'])
{
	$template = array();

	$context = Context::getContext();
		if (!isset($context->shop->name)) $context->shop->name = Configuration::get('PS_SHOP_NAME');
		if (!isset($context->shop->domain)) $context->shop->domain = Configuration::get('PS_SHOP_DOMAIN');
	$id_shop = (int)$context->shop->id?$context->shop->id:Shop::getContextShopID();

	$trigger = $mailjet->triggers['trigger'];
	$period_type = array(0,'MONTH','DAY','HOUR','MINUTE');

	/* mail template file */
	$languages = Language::getLanguages();
	foreach ($languages as $l)
		if (file_exists(_PS_MODULE_DIR_.'mailjet/views/templates/admin/'.$l['id_lang'].'.tpl'))
			$template[$l['id_lang']] = Tools::file_get_contents(_PS_MODULE_DIR_.'mailjet/views/templates/admin/'.$l['id_lang'].'.tpl');
	/* default template (if no EN in languages) */
	$template['en'] = Tools::file_get_contents(_PS_MODULE_DIR_.'mailjet/views/templates/admin/en.tpl');

	/* infos from the shop */
	$shop_name = $context->shop->name;
	$shop_url = 'http://'.$context->shop->domain;
	$shop_logo = $shop_url._PS_IMG_.Configuration::get('PS_LOGO').'?'.Configuration::get('PS_IMG_UPDATE_TIME');

	/* IDs research for SQL requests */
	$ids_canceled_blocked_payment_sql = DB::getInstance()->executeS('
		SELECT * FROM `'._DB_PREFIX_.'order_state_lang` WHERE id_lang = 1 AND (template LIKE \'order_canceled\' OR template LIKE \'payment_error\')');
		$ids_canceled_blocked_payment = array();
		foreach ($ids_canceled_blocked_payment_sql as $id) $ids_canceled_blocked_payment[] = $id['id_order_state'];
	$ids_waiting_payment_sql = DB::getInstance()->executeS('
		SELECT * FROM `'._DB_PREFIX_.'order_state_lang` WHERE id_lang = 1 AND (template LIKE \'bankwire\' OR template LIKE \'cheque\')');
		$ids_waiting_payment = array();
		foreach ($ids_waiting_payment_sql as $id) $ids_waiting_payment[] = $id['id_order_state'];
	$ids_loyalty_state_available_points = array(2,5); /* "Disponible" & "Non disponbile sur produits remisés" */

	/* SQL requests for search */
	$sql = array();
	$sql_target = array();
	$sql[1] = '
			SELECT cu.id_customer, '.(version_compare(_PS_VERSION_, '1.5', '>=')?'`cu`.id_lang,':'').' cu.email, cu.firstname, cu.lastname,
				`tr`.type, `tr`.id_trigger, `ca`.id_cart, `ca`.id_shop, `ca_ldu`.last_date_upd
			FROM `'._DB_PREFIX_.'customer` `cu`
			LEFT JOIN (
				SELECT id_customer, MAX(date_upd) AS last_date_upd
				FROM `'._DB_PREFIX_.'cart`
				GROUP BY id_customer
				) `ca_ldu`
				ON `ca_ldu`.id_customer = `cu`.id_customer
			INNER JOIN `'._DB_PREFIX_.'cart` `ca`
				ON `ca`.id_customer = `cu`.id_customer AND `ca`.date_upd = `ca_ldu`.last_date_upd
			LEFT JOIN `'._DB_PREFIX_.'orders` `or`
				ON `or`.id_cart = `ca`.id_cart
			LEFT JOIN `'._DB_PREFIX_.'mj_trigger` `tr`
				ON `tr`.id_customer = `cu`.id_customer AND `tr`.type = 1 AND `tr`.id_target = `ca`.id_cart
			WHERE	`tr`.id_trigger IS NULL
				AND `ca`.id_shop = '.$id_shop.'
				AND `or`.id_order IS NULL
				AND `ca_ldu`.last_date_upd < DATE_SUB(NOW(), INTERVAL '.$trigger[1]['period'].' '.$period_type[$trigger[1]['periodType']].')
			';
			$sql_target[1] = 'id_cart';
	$sql[2] = '
			SELECT cu.id_customer, '.(version_compare(_PS_VERSION_, '1.5', '>=')?'`cu`.id_lang,':'').' cu.email, cu.firstname, cu.lastname,
				 `tr`.type, `tr`.id_trigger, `or`.id_order, `or_ldu`.last_date_upd
			FROM `'._DB_PREFIX_.'customer` `cu`
			LEFT JOIN (
				SELECT id_customer, MAX(date_upd) AS last_date_upd
				FROM `'._DB_PREFIX_.'orders`
				GROUP BY id_customer
				) `or_ldu`
				ON `or_ldu`.id_customer = `cu`.id_customer
			INNER JOIN `'._DB_PREFIX_.'orders` `or`
				ON `or`.id_customer = `cu`.id_customer AND `or`.date_upd = `or_ldu`.last_date_upd
			LEFT JOIN `'._DB_PREFIX_.'mj_trigger` `tr`
				ON `tr`.id_customer = `cu`.id_customer AND `tr`.type = 2 AND `tr`.id_target = `or`.id_order
			WHERE	`tr`.id_trigger IS NULL
				AND `or`.id_shop = '.$id_shop.'
				AND `or`.current_state IN ('.implode(',', $ids_canceled_blocked_payment).')
				AND `or_ldu`.last_date_upd < DATE_SUB(NOW(), INTERVAL '.$trigger[2]['period'].' '.$period_type[$trigger[2]['periodType']].')
			';
			$sql_target[2] = 'id_order';
	$sql[3] = '
			SELECT cu.id_customer, '.(version_compare(_PS_VERSION_, '1.5', '>=')?'`cu`.id_lang,':'').' cu.email, cu.firstname, cu.lastname,
				 `tr`.type, `tr`.id_trigger, `or`.id_order, `or_ldu`.last_date_upd
			FROM `'._DB_PREFIX_.'customer` `cu`
			LEFT JOIN (
				SELECT id_customer, MAX(date_upd) AS last_date_upd
				FROM `'._DB_PREFIX_.'orders`
				GROUP BY id_customer
				) `or_ldu`
				ON `or_ldu`.id_customer = `cu`.id_customer
			INNER JOIN `'._DB_PREFIX_.'orders` `or`
				ON `or`.id_customer = `cu`.id_customer AND `or`.date_upd = `or_ldu`.last_date_upd
			LEFT JOIN `'._DB_PREFIX_.'mj_trigger` `tr`
				ON `tr`.id_customer = `cu`.id_customer AND `tr`.type = 3 AND `tr`.id_target = `or`.id_order
			WHERE	`tr`.id_trigger IS NULL
				AND `or`.id_shop = '.$id_shop.'
				AND `or`.current_state IN ('.implode(',', $ids_waiting_payment).')
				AND `or_ldu`.last_date_upd < DATE_SUB(NOW(), INTERVAL '.$trigger[3]['period'].' '.$period_type[$trigger[3]['periodType']].')
			';
			$sql_target[3] = 'id_order';
	$sql[4] = '
			';
			$sql_target[4] = ''; /* <= TODO : en attente de réponse mailjet ******************************************** */
	$sql[5] = '
			SELECT cu.id_customer, '.(version_compare(_PS_VERSION_, '1.5', '>=')?'`cu`.id_lang,':'').' cu.email, cu.firstname, cu.lastname,
				 `tr`.type, `tr`.id_trigger
			FROM `'._DB_PREFIX_.'customer` `cu`
			LEFT JOIN `'._DB_PREFIX_.'mj_trigger` `tr`
				ON `tr`.id_customer = `cu`.id_customer AND `tr`.type = 5 AND `tr`.date > DATE_SUB(NOW(), INTERVAL 363 DAY)
			WHERE	`tr`.id_trigger IS NULL
				AND DATE_FORMAT(`cu`.birthday, \'%m-%d\') = DATE_FORMAT(NOW(), \'%m-%d\')
			';
			$sql_target[5] = 'id_customer';
	$sql[6] = '
			SELECT cu.id_customer, '.(version_compare(_PS_VERSION_, '1.5', '>=')?'`cu`.id_lang,':'').' cu.email, cu.firstname, cu.lastname,
				 `tr`.type, `tr`.id_trigger, `or`.id_order, `or_ldu`.last_date_upd
			FROM `'._DB_PREFIX_.'customer` `cu`
			LEFT JOIN (
				SELECT id_customer, MAX(date_upd) AS last_date_upd
				FROM `'._DB_PREFIX_.'orders`
				WHERE valid = 1
				GROUP BY id_customer
				) `or_ldu`
				ON `or_ldu`.id_customer = `cu`.id_customer
			INNER JOIN `'._DB_PREFIX_.'orders` `or`
				ON `or`.id_customer = `cu`.id_customer AND `or`.date_upd = `or_ldu`.last_date_upd
			LEFT JOIN `'._DB_PREFIX_.'mj_trigger` `tr`
				ON `tr`.id_customer = `cu`.id_customer AND `tr`.type = 6 AND `tr`.id_target = `or`.id_order
			WHERE	`tr`.id_trigger IS NULL
				AND `or`.id_shop = '.$id_shop.'
				AND `or`.valid = 1
				AND `or_ldu`.last_date_upd <= DATE_SUB(NOW(), INTERVAL 1 YEAR)
			';
			$sql_target[6] = 'id_order';
	$sql[7] = '
			SELECT cu.id_customer, '.(version_compare(_PS_VERSION_, '1.5', '>=')?'`cu`.id_lang,':'').' cu.email, cu.firstname, cu.lastname,
				 `tr`.type, `tr`.id_trigger, `or`.id_order, `or_ldu`.last_date_upd
			FROM `'._DB_PREFIX_.'customer` `cu`
			LEFT JOIN (
				SELECT id_customer, MAX(date_upd) AS last_date_upd
				FROM `'._DB_PREFIX_.'orders`
				WHERE valid = 1
				GROUP BY id_customer
				) `or_ldu`
				ON `or_ldu`.id_customer = `cu`.id_customer
			INNER JOIN `'._DB_PREFIX_.'orders` `or`
				ON `or`.id_customer = `cu`.id_customer AND `or`.date_upd = `or_ldu`.last_date_upd
			LEFT JOIN `'._DB_PREFIX_.'mj_trigger` `tr`
				ON `tr`.id_customer = `cu`.id_customer AND `tr`.type = 7 AND `tr`.id_target = `or`.id_order
			WHERE	`tr`.id_trigger IS NULL
				AND `or`.id_shop = '.$id_shop.'
				AND `or`.valid = 1
				AND `or_ldu`.last_date_upd < DATE_SUB(NOW(), INTERVAL '.$trigger[7]['period'].' '.$period_type[$trigger[7]['periodType']].')
			';
			$sql_target[7] = 'id_order';
	$sql[8] = '
			SELECT cu.id_customer, '.(version_compare(_PS_VERSION_, '1.5', '>=')?'`cu`.id_lang,':'').' cu.email, cu.firstname, cu.lastname,
				 `tr`.type, `tr`.id_trigger, `or`.id_order, `or_ldu`.last_date_upd
			FROM `'._DB_PREFIX_.'customer` `cu`
			LEFT JOIN (
				SELECT id_customer, MAX(date_upd) AS last_date_upd
				FROM `'._DB_PREFIX_.'orders`
				WHERE valid = 1
				GROUP BY id_customer
				) `or_ldu`
				ON `or_ldu`.id_customer = `cu`.id_customer
			INNER JOIN `'._DB_PREFIX_.'orders` `or`
				ON `or`.id_customer = `cu`.id_customer AND `or`.date_upd = `or_ldu`.last_date_upd
			LEFT JOIN `'._DB_PREFIX_.'mj_trigger` `tr`
				ON `tr`.id_customer = `cu`.id_customer AND `tr`.type = 8 AND `tr`.id_target = `or`.id_order
			WHERE	`tr`.id_trigger IS NULL
				AND `or`.id_shop = '.$id_shop.'
				AND `or`.valid = 1
				AND `or_ldu`.last_date_upd < DATE_SUB(NOW(), INTERVAL '.$trigger[8]['period'].' '.$period_type[$trigger[8]['periodType']].')
			';
			$sql_target[8] = 'id_order';
	$sql[9] = '
			SELECT cu.id_customer, '.(version_compare(_PS_VERSION_, '1.5', '>=')?'`cu`.id_lang,':'').' cu.email, cu.firstname, cu.lastname,
				 `tr`.type, `tr`.id_trigger
			FROM `'._DB_PREFIX_.'customer` `cu`
			INNER JOIN (
				SELECT id_customer, COUNT(points) AS total, MAX(date_upd) AS lastdate
				FROM `'._DB_PREFIX_.'loyalty`
				WHERE id_loyalty_state IN ('.implode(',', $ids_loyalty_state_available_points).')
				GROUP BY id_customer
				) `lo`
				ON `lo`.id_customer = `cu`.id_customer
			LEFT JOIN `'._DB_PREFIX_.'mj_trigger` `tr`
				ON `tr`.id_customer = `cu`.id_customer AND `tr`.type = 9
					AND `tr`.date > DATE_SUB(NOW(), INTERVAL '.$trigger[9]['period'].' '.$period_type[$trigger[9]['periodType']].')
			WHERE	`tr`.id_trigger IS NULL
				AND `lo`.lastdate < DATE_SUB(NOW(), INTERVAL '.$trigger[9]['period'].' '.$period_type[$trigger[9]['periodType']].')
			';
			$sql_target[9] = 'id_customer';

	/* don't use trigger 9 if loyalty table don't exists */
	$trigger_max = 9;
	if (!Configuration::get('PS_LOYALTY_POINT_VALUE')) $trigger_max = 8;

	/* Research & mail sending */
	for ($sel = 1; $sel <= $trigger_max; $sel++)
	{
		if ($trigger[$sel]['active'])
		{
			/* echo $sel.'"'.$sql[$sel].'"<br />'.PHP_EOL; */
			$customers = DB::getInstance()->executeS($sql[$sel]);

			foreach ($customers as $customer)
			{
				if (version_compare(_PS_VERSION_, '1.5', '>=')) $id_lang = $customer['id_lang'];
				else 											$id_lang = $context->language->id;
				$id_lang_default = $context->language->id;

				/* Mail template : header & footer */
				if (isset($template[$id_lang]))
					$seltemplate = $template[$id_lang];
				else
					$seltemplate = $template['en'];
				/* shop keywords research */
				$seltemplate = str_replace('{shop_name}', $shop_name, $seltemplate);
				$seltemplate = str_replace('{shop_url}', $shop_url, $seltemplate);
				$seltemplate = str_replace('{shop_logo}', $shop_logo, $seltemplate);

				/* mail subject */
				if (isset($trigger[$sel]['subject'][$id_lang]))
					$subject = $trigger[$sel]['subject'][$id_lang];
				else
					$subject = $trigger[$sel]['subject'][$id_lang_default];

				/* mail content */
				if (isset($trigger[$sel]['mail'][$id_lang]))
					$content = $trigger[$sel]['mail'][$id_lang];
				else
					$content = $trigger[$sel]['mail'][$id_lang_default];

				/* customer keywords research */
				foreach ($customer as $key => $value)
				{
					$subject = str_replace('{'.$key.'}', $value, $subject);
					$content = str_replace('{'.$key.'}', $value, $content);
				}

                $tags_search = array('{shop_name}', '{shop_url}', '{shop_logo}');
                $tags_replace = array($shop_name, $shop_url, $shop_logo);
                $content = str_replace($tags_search, $tags_replace, $content);

				$content = utf8Outtags($content);

				$temp = str_replace('{content}', $content, $seltemplate);

				/* vouchers */
				if ($sel == 5 || $sel == 6)
				{
					$code = '';
					$alphanum = '0123456789AZERTYUIOPMLKJHGFDSQWXCVBN';
					$code_length = Tools::strlen($code);
					while ($code_length < 8 || (int)findCode($code))
					{
						$code = '';
						for ($i = 0; $i < 8; $i++)
							$code .= Tools::substr($alphanum, mt_rand(1, Tools::strlen($alphanum)) - 1, 1);
					}

					$name = array();
					$name[5] = 'Birthday voucher !';
					$name[6] = 'Extra voucher !';

					if (version_compare(_PS_VERSION_, '1.5', '>='))
					{
						$voucher = new CartRule();
						foreach (Language::getLanguages() as $lang)
							$voucher->name[$lang['id_lang']] = $name[$sel];
						$voucher->code = $code;
						if ($trigger[$sel]['discountType'] == 'amount')
							$voucher->reduction_amount = $trigger[$sel]['discount'];
						else
							$voucher->reduction_percent = $trigger[$sel]['discount'];
					}
					else
					{
						$voucher = new Discount();
						foreach (Language::getLanguages() as $lang)
							$voucher->description[$lang['id_lang']] = $name[$sel];
						$voucher->name = $code;
						if ($trigger[$sel]['discountType'] == 'amount')
							$voucher->id_discount_type = 2;
						else
							$voucher->id_discount_type = 1;
						$voucher->value = $trigger[$sel]['discount'];
						$voucher->quantity = 1;
						$voucher->quantity_per_user = 1;
					}
					$voucher->id_customer = $customer['id_customer'];
					$voucher->date_from = date('Y-m-d');
					$voucher->date_to = date('Y-m-d', time() + (31 * 24 * 3600) );
					$voucher->active = 1;
					$voucher->add();
				}

				/* mail sending */
				if ($res = $mailjet->sendMail($subject, $temp, $customer['email']))
					DB::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'mj_trigger`(id_customer,id_target,type,date)
						VALUES('.$customer['id_customer'].','.$customer[$sql_target[$sel]].','.$sel.',\''.date('Y-m-d').'\')');
			}
		}
	}
	echo 'OK';
}
else
	echo 'KO';