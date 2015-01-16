/*
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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright  2007-2015 PrestaShop SA
* @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

(function($, undifened) {

	/**
	 * Check if the customer finished his setup under the iframe
	 */
	function checkMerchantSetupState()
	{
		$.ajax({
			type : 'POST',
			url : _PS_MJ_MODULE_DIR_ + 'ajax.php',
			data :	{'method': 'checkMerchantSetupState', 'token': MJ_TOKEN, 'admin_token': MJ_ADMINMODULES_TOKEN},
			dataType: 'json',
			success: function(json)
			{
				if (json && json.result)
					window.location.href = json.url;
			},
			error: function(xhr, ajaxOptions, thrownError)
			{
				// console.log(xhr)
			}
		});
	}
	
	
	$(document).ready(function() {
	
		switch(MJ_page_name)
		{
			case MJ_SETUP_STEP_1:
				var timer = $.timer(checkMerchantSetupState);
				timer.set({time: 10000, autostart: true});
			break;
	
			case MJ_LOGIN:
				$('#MJ_auth_link').click(function() {
					$('#MJ_auth_form').submit();
					return false;
				});
			break;
		}
	});
})(jQuery);