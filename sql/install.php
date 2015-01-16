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

$sql = array();

	$sql[] = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mj_events` (
				`id_mj_events` int(11) NOT NULL AUTO_INCREMENT,
				`event` varchar(64) NOT NULL,
				`time` varchar(32) NOT NULL,
				`email` varchar(128) NOT NULL,
				`mj_campaign_id` int(11) NOT NULL,
				`mj_contact_id` int(11) NOT NULL,
				`customcampaign` varchar(64) NOT NULL,
				`ip` varchar(64) NOT NULL,
				`geo` varchar(2) NOT NULL,
				`agent` varchar(255) NOT NULL,
				`url` text NOT NULL,
				`blocked` varchar(8) NOT NULL,
				`hard_bounce` varchar(8) NOT NULL,
				`error_related_to` varchar(16) NOT NULL,
				`error` varchar(16) NOT NULL,
				`source` text NOT NULL,
				`original_address` text NOT NULL,
				`new_address` varchar(128) NOT NULL,
				PRIMARY KEY (`id_mj_events`)
			);';

	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mj_trigger` (
				`id_trigger` int(11) NOT NULL AUTO_INCREMENT,
				`id_customer` int(11) NOT NULL,
				`id_target` int(11) NOT NULL,
				`type` tinyint(2) NOT NULL,
				`date` date NOT NULL,
				PRIMARY KEY (`id_trigger`)
				);';

	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mj_campaign` (
			  `id_campaign_presta` int(8) NOT NULL AUTO_INCREMENT,
			  `campaign_id` int(10) NOT NULL,
			  `token_presta` varchar(64) NOT NULL,
			  `date_add` datetime NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `stats_campaign_id` int(12) NOT NULL,
			  `delivered` int(8) NOT NULL,
			  PRIMARY KEY (`id_campaign_presta`));';

	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mj_roi` (
			  `campaign_id` int(10) NOT NULL,
			  `id_order` int(10) NOT NULL,
			  `total_paid` double NOT NULL,
			  `date_add` datetime NOT NULL,
			  KEY `campaign_id` (`campaign_id`,`id_order`)
			);';

	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mj_roi_cart` (
			  `id_cart` int(11) NOT NULL,
			  `token_presta` varchar(64) NOT NULL,
			  PRIMARY KEY (`id_cart`)
			);';


	/* ** ** SEGMENTATION ** ** */
	
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'mj_filter`';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'mj_condition`';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'mj_basecondition`';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'mj_sourcecondition`';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'mj_fieldcondition`';
	
	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mj_filter` (
					id_filter INTEGER NOT NULL AUTO_INCREMENT,
					name VARCHAR(250) NOT NULL,
					description VARCHAR(250),
					id_group INT(10) NOT NULL,
					assignment_auto TINYINT(1) NOT NULL,
					replace_customer TINYINT(1) NOT NULL,
					date_start TIMESTAMP,
					date_end TIMESTAMP,
					PRIMARY KEY (id_filter)
					);';
	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mj_condition` (
					id_condition INTEGER NOT NULL AUTO_INCREMENT,
					id_filter INTEGER NOT NULL,
					id_basecondition INTEGER NOT NULL,
					id_sourcecondition INTEGER NOT NULL,
					id_fieldcondition INTEGER NOT NULL,
					rule_a ENUM(\'AND\', \'OR\') NOT NULL,
					rule_action ENUM(\'IN\', \'NOT IN\') NOT NULL,
					period ENUM(\'ALL\', \'MONTH\') NOT NULL,
					data VARCHAR(250),
					value1 VARCHAR(250),
					value2 VARCHAR(250),
					PRIMARY KEY (id_condition)
					);';
	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mj_basecondition` (
					id_basecondition INTEGER NOT NULL AUTO_INCREMENT,
					label INTEGER,
					tablename VARCHAR(250),
					PRIMARY KEY (id_basecondition)
					);';
	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mj_sourcecondition` (
					id_sourcecondition INTEGER NOT NULL AUTO_INCREMENT,
					id_basecondition INTEGER NOT NULL,
					label INTEGER,
					jointable VARCHAR(250),
					PRIMARY KEY (id_sourcecondition)
					);';
	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mj_fieldcondition` (
					id_fieldcondition INTEGER NOT NULL AUTO_INCREMENT,
					id_sourcecondition INTEGER NOT NULL,
					label INTEGER,
					field TEXT,
					labelSQL VARCHAR(250),
					printable TINYINT(1),
					binder VARCHAR(250),
					PRIMARY KEY (id_fieldcondition)
					);';

?>