{*
* 2007-2019 PrestaShop
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
* @copyright 2007-2019 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

<script type="text/javascript">
    var tokenV = "{$mj_token|escape:'javascript':'UTF-8'|default:'0'}";
    var ajaxFile = "{$mj_ajaxFile|escape:'javascript':'UTF-8'|default:''}";
    var ajaxSyncFile = "{$mj_ajaxSyncFile|escape:'javascript':'UTF-8'|default:''}";
    var ajaxBundle = "{$mj_ajaxBundle|escape:'javascript':'UTF-8'|default:''}";
    var id_employee = "{$mj_id_employee|escape:'javascript':'UTF-8'|default:'0'}";
    var trad = new Array();
    var datePickerJsFormat = "{$mj_datePickerJsFormat|escape:'javascript':'UTF-8'|default:'yy-mm-dd'}";
    {foreach from=$mj_trads key=key item=value}
    trad[{$key|escape:'javascript':'UTF-8'}] = "{$value|escape:'javascript':'UTF-8'|default:'?'}";
    {/foreach}
    var lblMan = "{$mj_lblMan|escape:'javascript':'UTF-8'|default:'Man'}";
    var lblWoman = "{$mj_lblWoman|escape:'javascript':'UTF-8'|default:'Woman'}";
    var lblUnknown = "{$mj_lblUnknown|escape:'javascript':'UTF-8'|default:'Unknow'}";
    var loadingFilter = false;
    var mj_trad_plus = ['{l s='And' mod='mailjet'}', '{l s='Or' mod='mailjet'}', '{l s='Include' mod='mailjet'}', '{l s='Exclude' mod='mailjet'}'];
    var mj_base_select = new Array();
    {foreach from=$mj_base_select item=base_select}
    mj_base_select[{$base_select.id_basecondition|escape:'javascript':'UTF-8'}] = "{$mj_trads[$base_select.label]|escape:'javascript':'UTF-8'|default:'?'}";
    {/foreach}
</script>
{$mj_datepickerPersonnalized|escape:'htmlall':'UTF-8'|default:''}
{$MJ_templates.ORDER_NOTIFICATIONS|escape:'htmlall':'UTF-8'|default:''}



<div class="center_page">

    <div class="clear"> &nbsp; </div>
    <fieldset id="mainFieldset">
        <legend>{l s='Order Notifications Module' mod='mailjet'}</legend>
    </fieldset>
    <form action="{$MJ_adminmodules_link|escape:'htmlall':'UTF-8'|default:''}&MJ_request_page=ORDER_NOTIFICATIONS" method="POST">
        <div class="warn">
            {l s='Do you want to use custom order notifications templates ?' mod='mailjet'} &nbsp; &nbsp;
            <input type="radio" name="MJ_order_notifications" id="activate_yes" value=1 {if $mj_use_order_notification}checked{/if} /> <label class="t" for="activate_yes">{l s='YES' mod='mailjet'}</label>
            <input type="radio" name="MJ_order_notifications" id="activate_no" value=0 {if !$mj_use_order_notification}checked{/if} /> <label class="t" for="activate_no">{l s='NO' mod='mailjet'}</label>
            <input type="submit" value=" {l s='Modify' mod='mailjet'} " name="MJ_use_order_notifications"  class="button" />


        </div>
    </form>
    <div class="warn">

    </div>

</div>
