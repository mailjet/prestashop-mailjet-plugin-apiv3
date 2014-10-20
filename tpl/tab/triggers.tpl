{**
 * 2007-2014 PrestaShop
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
 * @copyright 2007-2014 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
*}<!-- Mailjet : Triggers -->
{$tinymce|default:''}
<form action="{$smarty.server.REQUEST_URI|default:''}" method="POST">
<div id="mj_triggers_page" class="center_page" style="width:initial;max-width:960px;">
	<div class="warn">&nbsp; {l s='To activate the triggers you need to set up this cron job' mod='mailjet'} :<br />&nbsp; <a href="{$cron}" target="_blank"><b>{$cron|default:''}</b></a></div>
	<fieldset class="hint" style="position:relative;display:block !important;">
		<legend style="text-transform:none;">{l s='Do you wish to activate eCommerce transactional email ?' mod='mailjet'}</legend>
        <div style="padding-left:40px;">
			<input type="radio" name="MJ_triggers_active" id="MJ_triggers_active_1" value=1 onClick="$('#triggers_options').slideDown()" {if $triggers.active}checked{/if} /> <a href="javascript:;" onClick="$('#MJ_triggers_active_1').click();">{l s='YES' mod='mailjet'}</a> &nbsp; 
			<input type="radio" name="MJ_triggers_active" id="MJ_triggers_active_0" value=0 onClick="$('#triggers_options').slideUp()" {if !$triggers.active}checked{/if} /> <a href="javascript:;" onClick="$('#MJ_triggers_active_0').click();">{l s='NO' mod='mailjet'}</a><br />
		</div>
        <input type="submit" name="MJ_set_triggers" value="{l s='Save Changes' mod='mailjet'}" onClick="this.value=' {l s='Wait please...' mod='mailjet'} ';" class="savebutton button" />
	</fieldset>
    <br />
    <fieldset id="triggers_options" {if !$triggers.active}style="display:none;"{/if}>
    	<legend>{l s='Triggers' mod='mailjet'}</legend>
        <ul>
        	{for $sel=1 to 9}
            	<li style="{if $sel==4}display:none;{/if}">
	            	<label>
	                	{if $sel==1}{l s='Abandon Cart Email' mod='mailjet'}{/if}
	                	{if $sel==2}{l s='Payment failure recovery after canceled or blocked payment' mod='mailjet'}{/if}
	                	{if $sel==3}{l s='Order pending payment' mod='mailjet'}{/if}
	                	{if $sel==4}{l s='Shipment Delay Notification' mod='mailjet'}{/if}
	                	{if $sel==5}{l s='Birthday promo' mod='mailjet'}{/if}
	                	{if $sel==6}{l s='Purchase Anniversary promo' mod='mailjet'}{/if}
	                	{if $sel==7}{l s='Customers who have not ordered since few time' mod='mailjet'}{/if}
	                	{if $sel==8}{l s='Satisfaction survey' mod='mailjet'}{/if}
	                	{if $sel==9}{l s='Loyalty points reminder' mod='mailjet'}{/if} : 
					</label>
					<div style="padding-left:20px;">
						<input type="radio" name="MJ_triggers_trigger_{$sel}_active" id="MJ_triggers_trigger_{$sel}_active_1" value=1 {if $triggers.trigger.$sel.active}checked{/if} onClick="$('#MJ_triggers_trigger_{$sel}_button').show();" /> <a href="javascript:;" onClick="$('#MJ_triggers_trigger_{$sel}_active_1').click();">{l s='Yes' mod='mailjet'}</a> &nbsp; 
						<input type="radio" name="MJ_triggers_trigger_{$sel}_active" id="MJ_triggers_trigger_{$sel}_active_0" value=0 {if !$triggers.trigger.$sel.active}checked{/if} onClick="$('#MJ_triggers_trigger_{$sel}_button').hide();$('#MJ_triggers_trigger_{$sel}_parameters').hide();" /> <a href="javascript:;" onClick="$('#MJ_triggers_trigger_{$sel}_active_0').click();">{l s='No' mod='mailjet'}</a> &nbsp; 
						<a href="javascript:;" onClick="$('#MJ_triggers_trigger_{$sel}_parameters').slideToggle();" id="MJ_triggers_trigger_{$sel}_button" class="button" style="display:inline-block;min-width:90px;{if !$triggers.trigger.$sel.active}display:none;{/if}" />{l s='parameters' mod='mailjet'}</a> &nbsp; 
                        <br />
					</div>
					<span class="clearspan"></span>
					<div id="MJ_triggers_trigger_{$sel|default:''}_parameters" class="warn mj_triggers_parameters">
						<b>{l s='Parameters' mod='mailjet'}</b><br />
                        {if $sel!=5 && $sel!=6}
	                        <label>{l s='Trigger sending after how long ?' mod='mailjet'}</label>
							<input type="text" name="MJ_triggers_trigger_{$sel|default:''}_period" size=5 style="text-align:right;" value="{$triggers.trigger.$sel.period|default:''}" />
							<select name="MJ_triggers_trigger_{$sel|default:''}_periodType">
								<option value=1 {if $triggers.trigger.{$sel}.periodType==1}selected{/if}>{l s='Month' mod='mailjet'}</option>
								<option value=2 {if $triggers.trigger.{$sel}.periodType==2}selected{/if}>{l s='Days' mod='mailjet'}</option>
								<option value=3 {if $triggers.trigger.{$sel}.periodType==3}selected{/if}>{l s='Hours' mod='mailjet'}</option>
								<option value=4 {if $triggers.trigger.{$sel}.periodType==4}selected{/if}>{l s='Minutes' mod='mailjet'}</option>
							</select>
                            <br />
						{else}
	                        <label>{l s='Reduction amount' mod='mailjet'} :</label>
							<input type="text" name="MJ_triggers_trigger_{$sel|default:''}_discount" size=5 style="text-align:right;" value="{$triggers.trigger.$sel.discount|default:''}" />
							<select name="MJ_triggers_trigger_{$sel|default:''}_discountType">
								<option value=1 {if $triggers.trigger.{$sel}.discountType==1}selected{/if}>(%) {l s='Percentage' mod='mailjet'}</option>
{*								<option value=2 {if $triggers.trigger.{$sel}.discountType==2}selected{/if}>({$sign}) {l s='Amount' mod='mailjet'}</option>*}
								<option value=2 {if $triggers.trigger.{$sel}.discountType==2}selected{/if}>($,€,£) {l s='Amount' mod='mailjet'}</option>
							</select>
                            <br />
						{/if}
						<br />
                        <center>
                        	<div class="sel_lang">
                            <b style="color:#000;">{l s='Message' mod='mailjet'}</b> - {l s='Language select' mod='mailjet'} : &nbsp;
	                        {foreach $languages as $language}
	                        	{assign var="id_lang" value=$language.id_lang}
                            	<a href="javascript:;" onClick="$('.id_lang_close_{$sel}').hide();$('#id_lang_{$sel}_{$id_lang}').show();$('.flags_{$sel}').removeClass('selFlag');$(this).addClass('selFlag');" class="{if $id_lang==$sel_lang}selFlag{/if} flags_{$sel}" title="{$language.name}"><img src="../img/l/{$id_lang}.jpg" alt="{$language.name}" /></a>
                            {/foreach}
                            </div>
                            <div style="padding-left:60px;">
	                        {foreach $languages as $language}
	                        	{assign var="id_lang" value=$language.id_lang}
								<div id="id_lang_{$sel}_{$id_lang}" class="id_lang_close_{$sel}" style="{if $id_lang!=$sel_lang}display:none;{/if}">
   	 	                    		{l s='Subject' mod='mailjet'} : <input type="text" name="MJ_triggers_trigger_{$sel|default:''}_subject_{$id_lang|default:''}" value="{$triggers.trigger.$sel.subject.$id_lang|default:''}" style="width:500px;" /><br />
                                    <div style="width:5px;height:5px;display:block;"></div>
									<textarea name="MJ_triggers_trigger_{$sel|default:''}_mail_{$id_lang|default:''}" class="rte" style="width:100%;height:400px;">{$triggers.trigger.$sel.mail.$id_lang|default:''}</textarea>
								</div>
							{/foreach}
                            </div>
						</center>
                        <br />
                        <br />
					</div>
					<span class="clearspan"></span>
				</li>
            {/for}
        </ul>
    </fieldset>
</div>
</form>
<!-- /Mailjet : Triggers -->