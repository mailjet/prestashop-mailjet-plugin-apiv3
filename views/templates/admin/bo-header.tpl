{**
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
*}
<script type="text/javascript">
	var _PS_MJ_MODULE_DIR_ = "{$MJ_base_dir|escape:'javascript'|default:''}";
	var MJ_TOKEN = "{$MJ_TOKEN|escape:'javascript'|default:''}";
	var MJ_ADMINMODULES_TOKEN = "{$MJ_ADMINMODULES_TOKEN|escape:'javascript'|default:''}";

	// Const from module to a better understanding if it used under the javascript file
	var MJ_REQUEST_PAGE_TYPE = "{$MJ_REQUEST_PAGE_TYPE|escape:'javascript'|default:''}";

	// Generate from the xml
	{foreach from=$MJ_available_pages key=MJ_page_key item=MJ_page_value}
		var {$MJ_page_key|escape:'javascript'} = "{$MJ_page_value|escape:'javascript'|default:''}";
	{/foreach}
</script>