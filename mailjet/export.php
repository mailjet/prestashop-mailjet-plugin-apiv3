<?php

include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once(dirname(__FILE__).'/mailjet.php');

$mailjet = new Mailjet();

$triggers = $mailjet->getTriggers();

if ($triggers['active']==1)
{
	if ($triggers['trigger'][1]['active']==1) // Abandon Cart Email
	{
	}
	if ($triggers['trigger'][2]['active']==1) // Payment failure recovery after canceled or blocked payment
	{
	}
	if ($triggers['trigger'][3]['active']==1) // Order pending payment
	{
	}
	if ($triggers['trigger'][4]['active']==1) // Shipment Delay Notification
	{
	}
	if ($triggers['trigger'][5]['active']==1) // Birthday promo
	{
	}
	if ($triggers['trigger'][6]['active']==1) // Purchase Anniversary promo
	{
	}
	if ($triggers['trigger'][7]['active']==1) // Customers who have not ordered since few time
	{
	}
	if ($triggers['trigger'][8]['active']==1) // Satisfaction survey
	{
	}
	if ($triggers['trigger'][9]['active']==1) // Loyalty points reminder
	{
	}
}

?>