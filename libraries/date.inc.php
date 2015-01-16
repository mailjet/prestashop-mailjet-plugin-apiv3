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

function get_week($week,$year)
{
	$week_dates = array();
   	// Get timestamp of first week of the year
   $first_day = mktime(12,0,0,1,1,$year);
   $first_week = date("W",$first_day);
   if ($first_week > 1) {
       $first_day = strtotime("+1 week",$first_day); // skip to next if year does not begin with week 1
   }
   // Get timestamp of the week
   $timestamp = strtotime("+$week week",$first_day);
   // Adjust to Monday of that week
   $what_day = date("w",$timestamp); // I wanted to do "N" but only version 4.3.9 is installed :-(
   if ($what_day==0) {
       // actually Sunday, last day of the week. FIX;
       $timestamp = strtotime("-6 days",$timestamp);
   } elseif ($what_day > 1) {
       $what_day--;
       $timestamp = strtotime("-$what_day days",$timestamp);
   }
   $week_dates["start"] = date("Y-m-d",$timestamp); // Monday
   $week_dates["end"] = date("Y-m-d",strtotime("+6 day",$timestamp)); // Sunday
   return($week_dates);
}

function get_month($month, $year)
{
	$return = array();
	$month--;
	$week_dates = array();
   	// Get timestamp of first week of the year
    $first_day = mktime(12,0,0,1,1,$year);
    $timestamp = strtotime("+$month month",$first_day);
  	$return["start"] = date("Y-m-d",$timestamp);
  	$return["end"] = date("Y-m-d",strtotime("last day of",$timestamp));
  	return $return;
}
function get_trimester($trimester, $year)
{
    $return = array();
    $trimester--;
	$month = ($trimester * 3);
	$week_dates = array();
   	// Get timestamp of first week of the year
    $first_day = mktime(12,0,0,1,1,$year);
    $timestamp = strtotime("+$month month",$first_day);
  	$return["start"] = date("Y-m-d",$timestamp);
  	$timestamp = strtotime("+2 month",$timestamp);
  	$return["end"] = date("Y-m-d",strtotime("last day of",$timestamp));
  	return $return;
}
// type = 0 week; 1 month;2 trimester; 3 year
function get_date_rel($type, $nb)
{
	$date = new DateTime();
	$today = $date->getTimestamp();
	switch ($type)
	{
		case 0 :
			$nb = $nb * 7;
			$rel = strtotime("-$nb day",$today);
			break;
		case 1 :
			$rel = strtotime("-$nb month",$today);
			break;
		case 2 :
			$nb = $nb * 3;
			$rel = strtotime("-$nb month",$today);
			break;
		case 3 :
			$rel = strtotime("-$nb year",$today);
			break;
	}
	
	return date("Y-m-d",$rel);
}
?>