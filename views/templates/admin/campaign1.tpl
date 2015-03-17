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
*}<!-- Mailjet : Campaigns -->
<div id="mj_campaigns_page" class="center_page" style="width:initial;max-width:960px;">
	<form id="create" method="post" action="{$smarty.server.REQUEST_URI|escape|default:''}">
    <input type="hidden" name="MJ_submitCampaign" value=1 />
	<fieldset>
    	<legend>{l s='New Campaign' mod='mailjet'}</legend>
		<div class="button" style="padding:10px;">
	        <div style="display:inline-block;vertical-align:middle;">
				<label for="ctitle" class="t"><b style="color:#000;font-size:13px;">{l s='Title' mod='mailjet'}</b> <sup style="color:#900;">*</sup> : </label>
   	 	    <input id="ctitle" name="ctitle" type="text" style="width:400px;" /><br />
			</div>
			<div style="display:inline-block;vertical-align:middle;">
   	     	&nbsp; - &nbsp; {l s='Language' mod='mailjet'} <sup style="color:#900;">*</sup> : 
				<select id="lang" name="lang" rel="noresize" required>
					<option value="0" selected="selected">-- {l s='Choose the campaign language' mod='mailjet'} --</option>
					{foreach from=$langs key=key item=value}
						<option value="{$key|escape}"{if $key==$iso} selected{/if}>{$value|escape}</option>
					{/foreach}
				</select>
	        </div>
		</div>
        <br />
        <div style="float:right">
        	<table cellpadding=0 cellspacing=5 border=0>
			<tr>
            	<td nowrap><label for="sender" class="t required"><b style="color:#000;">{l s='Sender Name' mod='mailjet'}</b> <sup style="color:#900;">*</sup> : </label></td>
				<td nowrap><input id="sender" name="sender" type="text" required style="width:240px;" /></td>
			</tr>
			<tr>
            	<td nowrap><label for="from" class="t required"><b style="color:#000;">{l s='Sender Address' mod='mailjet'}</b> <sup style="color:#900;">*</sup> : </label></td>
				<td nowrap>
                	<select name="from" id="from" rel="noresize" style="width:250px;" required>
                    	<option value="0" selected="selected">-- {l s='Choose your sender address' mod='mailjet'} --</option>
                        {foreach from=$campaign.senders item=value}
							<option value="{$value|escape|default:''}">{$value|escape|default:''}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			</table>
        </div>
        	<table cellpadding=0 cellspacing=5 border=0>
			<tr>
            	<td nowrap><label for="subject" class="t required"><b>{l s='Subject' mod='mailjet'}</b> <sup style="color:#900;">*</sup> : </label></td>
				<td nowrap><input id="subject" name="subject" type="text" required  style="width:380px;" /></td>
			</tr>
			<tr>
            	<td nowrap><label for="replyto" class="t"><b>{l s='Reply To' mod='mailjet'}</b> : </label></td>
				<td nowrap><input id="replyto" name="replyto" type="text" style="width:240px;" /></td>
			</tr>
            </table>
        <br />
        <center>
        <p class="button" style="text-align:center;padding:10px;">
        	<br />
			<label for="template" class="t"><b style="font-size:13px;">{l s='Mailjet template' mod='mailjet'}</b></label><br />
			<select id="template" name="template" rel="noresize" style="width:500px;font-size:12px;padding:5px;font-weight:bold;margin:5px;">
				<option value="0" selected="selected">{l s='You can choose an available template : (Optional)' mod='mailjet'}</option>
				{foreach from=$campaign.templates key=key item=value}
					<option value="{$value|escape|default:''}">- {$key|escape|default:''}</option>
				{/foreach}
			</select><br />
            <br />
			<label for="contacts_list" class="t"><b style="font-size:13px;">{l s='Contacts list' mod='mailjet'}</b></label><br />
			<select id="contacts_list" name="list_id" rel="noresize" style="width:500px;font-size:12px;padding:5px;font-weight:bold;margin:5px;">
				<option value="0" selected="selected">{l s='Choose a contacts list :' mod='mailjet'}</option>
				{foreach from=$campaign.lists key=key item=label}
					<option value="{$key|escape|default:''}">- {$label|escape|default:''}</option>
				{/foreach}
			</select><br />
            <br />
			<!-- div class="box" -->
        		<a href="javascript:void(0)" name="submitCampaign" id="submitCampaign" onClick="submitform()" title="submit" class="button btn btn­success" style="font-weight:bold;font-size:13px;color:#000;">{l s='Save and continue' mod='mailjet'}</a><br />
			<!-- /div -->
            <br />
        </p>
        </center>
	</fieldset>
	</form>
	<script>
    function check_empty(inputName,value)
    {
    	if (typeof(value)=="undefined" || value=="")
        {
        	alert("{l s='Your' mod='mailjet'} "+inputName+" {l s='is invalid' mod='mailjet'}");
            return false;
		}
        return true;
	}
    function check_select(selectName,value)
	{
    	if (typeof(value)=="undefined" || value==0)
        {
        	alert("{l s='Your' mod='mailjet'} "+selectName+" {l s='is invalid' mod='mailjet'}");
            return false;
		}
        return true;
	}
    function submitform()
	{
    	var subject = document.getElementById('create')['subject'].value;
        var sender = document.getElementById('create')['sender'].value;
        var from = document.getElementById('create')['from'].value;
		var lang = document.getElementById('create')['lang'].value;
		var template = document.getElementById('create')['template'].value;
		var contact_list = document.getElementById('create')['contacts_list'].value;
        if ( check_empty('{l s='Title' mod='mailjet'}',ctitle) && check_empty('{l s='Subject' mod='mailjet'}',subject) && check_empty('{l s='Sender name' mod='mailjet'}',sender) && check_select('{l s='Sender address' mod='mailjet'}',from) && check_select('{l s='Language' mod='mailjet'}',lang) && check_select('{l s='Template' mod='mailjet'}',template) && check_select('{l s='Contact list' mod='mailjet'}',contact_list) )
			document.getElementById('create').submit();
	}
	</script>
</div>
<!-- /Mailjet : Campaigns -->