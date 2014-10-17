<?php

	$sql = array();
	
	$sql[] = 'DROP TABLE `'._DB_PREFIX_.'mj_trigger`';
	$sql[] = 'DROP TABLE `'._DB_PREFIX_.'mj_events`';
	$sql[] = 'DROP TABLE `'._DB_PREFIX_.'mj_campaign`';
	$sql[] = 'DROP TABLE `'._DB_PREFIX_.'mj_roi`';
	
?>