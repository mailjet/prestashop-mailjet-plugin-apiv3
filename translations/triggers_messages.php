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

/* Default Mails contents for triggers */
/* KEYWORDS : {shop_url} {shop_name} {firstname} {lastname} {email} */

/* *** english ************************ */

$subject = array();
$mail = array();

for ($i = 1; $i <= 9; $i++)
	$subject[$i]['en'] = 'Message to {firstname} {lastname} !';

$mail[1]['en'] = '
	Dear {firstname} {lastname},<br />
	You seem to have filled your shopping cart <br />
        but did not go through with your order... <br />
	<a href="{shop_url}">Click here</a> to complete your order!
	';
$mail[2]['en'] = '
	Dear {firstname} {lastname},<br />
	It seems there was an issue with your payment on your last order on our website and your purchase did not complete ...<br />
	Please <a href="{shop_url}">click here</a> to order again!
	';
$mail[3]['en'] = '
	Dear {firstname} {lastname},<br />
	You have placed an order in our store but we are still awaiting payment...
	';
$mail[4]['en'] = '
	Dear {firstname} {lastname},<br />
	???
	';
$mail[5]['en'] = '
	Dear {firstname} {lastname},<br />
	HAPPY BIRTHDAY! To thank you for your interest in our store we are offering you a voucher valid for 1 month!<br />
	To enjoy this birthday gift, go to your account : <a href="{shop_url}">click here</a> !
	';
$mail[6]['en'] = '
	Dear {firstname} {lastname},<br />
	SPECIAL OFFER! We are offering you a discount voucher valid for 1 month.<br />
	Take advantage of it, <a href="{shop_url}">click here</a> ! :)
	';
$mail[7]['en'] = '
	Dear {firstname} {lastname},<br />
    It`s been awhile since you came to {shop_name}!<br />
    Come check out what is new - <a href="{shop_url}">click here</a>!
    ';
$mail[8]['en'] = '
	Dear {firstname} {lastname},<br />
	You recently made a purchase with us!<br />
	Were you satisfied? Please leave us a comment by <a href="{shop_url}">clicking here</a> !
	';
$mail[9]['en'] = '
	Dear {firstname} {lastname},<br />
	You still have loyalty points, transform them in purchase and enjoy!<br />
	<a href="{shop_url}">click here</a> !
	';

/* *** français ************************ */

for ($i = 1; $i <= 9; $i++)
	$subject[$i]['fr'] = 'Message pour {firstname} {lastname} !';

$mail[1]['fr'] = '
	Cher {firstname} {lastname},<br />
	Il y a quelques temps, vous avez rempli un caddie avec un produit mais n\'&ecirc;tes pas all&eacute; jusqu\'au bout de
	votre commande...<br />
	<a href="{shop_url}">Cliquez ici</a> pour terminer votre commande !
	';
$mail[2]['fr'] = '
	Cher {firstname} {lastname},<br />
	Vous avez eu un souci de paiement lors de votre derni&egrave;re commande sur notre site...<br />
	Le probl&egrave;me est s&ucirc;rement r&eacute;gl&eacute; depuis.  <a href="{shop_url}">Cliquez ici</a> pour repasser commande !
	';
$mail[3]['fr'] = '
	Cher {firstname} {lastname},<br />
	Vous avez pass&eacute; commande dans notre magasin mais nous somme toujours en attente de votre paiement...
	';
$mail[4]['fr'] = '
	Cher {firstname} {lastname},<br />
	???
	';
$mail[5]['fr'] = '
	Cher {firstname} {lastname},<br />
	JOYEUX ANNIVERSAIRE ! Pour vous remercier de votre int&eacute;r&ecirc;t pour notre magasin<br />
	nous vous offrons un bon de r&eacute;duction valable 1 mois !<br />
	Pour en profiter, rendez-vous tout de suite dans votre compte : <a href="{shop_url}">Cliquez ici</a> !
	';
$mail[6]['fr'] = '
	Cher {firstname} {lastname},<br />
	OFFRE EXCEPTIONNELLE ! Nous vous offrons un bon de r&eacute;duction valable pendant 1 mois.<br />
	Profitez-en ! <a href="{shop_url}">Cliquez ici</a> ! :)
	';
$mail[7]['fr'] = '
	Cher {firstname} {lastname},<br />
	Cela fait longtemps que vous n’&ecirc;tes pas venu sur Mailjet !<br />
	Venez voir nos nouveaut&eacute;s.  <a href="{shop_url}">Cliquez ici</a> !
	';
$mail[8]['fr'] = '
	Cher {firstname} {lastname},<br />
	Il y a peu de temps vous avez fait un achat sur Mailjet !<br />
	En &egrave;tes-vous satisfait ? Laissez-nous un commentaire en <a href="{shop_url}">cliquant ici</a> !
	';
$mail[9]['fr'] = '
	Cher {firstname} {lastname},<br />
	Il vous reste encore des points de fid&eacute;lit&eacute;. Transformez-les en achats et faites-vous plaisir !<br />
	A bient&ocirc;t sur Mailjet : <a href="{shop_url}">Cliquez ici</a> !
	';
