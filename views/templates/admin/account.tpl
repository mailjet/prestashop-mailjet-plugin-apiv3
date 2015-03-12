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
*}<!-- Mailjet : Account -->
<form action="{$smarty.server.REQUEST_URI|escape|default:''}" method="POST">
<div id="mj_account_page" class="center_page">
	<div id="mj_account_page_left">
    
		<fieldset>
			<legend>{l s='My details' mod='mailjet'}</legend>
            <div id="mj_account_details">
			    <div>
				    {if $infos->Firstname || $infos->Lastname}
	                	<p>
				    	{if $infos->Firstname}{$infos->Firstname|escape}&nbsp;{/if}
				    	{if $infos->Lastname}{$infos->Lastname|escape}{/if}
				        </p>
					{/if}
				    {if $infos->Email}<p>{$infos->Email|escape}</p>{/if}
	                <br />
				    {* if $language}{l s='Display' mod='mailjet'} : {$language}<br />{/if *}
			    </div>
			    {if $infos->CompanyName}<p>{$infos->CompanyName|escape}</p>{/if}
	            <p>
			    {if $infos->AddressStreet}{$infos->AddressStreet|escape|nl2br}<br />{/if}
			    {if $infos->AddressPostalCode || $infos->AddressPostalCode}
			    	{if $infos->AddressPostalCode}{$infos->AddressPostalCode|escape}&nbsp;{/if}
			    	{if $infos->AddressCity}{$infos->AddressCity|escape}{/if}
			        <br />
				{/if}
			    {if $country}{$country|escape}<br />{/if}
	            </p>
			    <a href="javascript:;" onClick="$('#mj_account_details_mod').show();$('#mj_account_details').hide();" class="savebutton button">{l s='Modify' mod='mailjet'}</a>
			</div>
            <div id="mj_account_details_mod" style="display:none;">
            	<div>
	            	{l s='Firstname' mod='mailjet'}<br />
					<input type="text" name="MJ_account_firstname" value="{$infos->Firstname|escape|default:''}" /><br />
				</div>
                <div>
                    {l s='Lastname' mod='mailjet'}<br />
                    <input type="text" name="MJ_account_lastname" value="{$infos->Lastname|escape|default:''}" /><br />
                </div>
                <div style="display:block;">
                    <div class="mj_account_sep"></div>
	            	{l s='e-mail' mod='mailjet'}<br />
					<input type="text" name="MJ_account_contact_email" readonly value="{$infos->Email|escape|default:''}" /><br />
                    <div class="mj_account_sep"></div>
	            	{l s='Company' mod='mailjet'}<br />
					<input type="text" name="MJ_account_company_name" value="{$infos->CompanyName|escape|default:''}" /><br />
	            	{l s='Address' mod='mailjet'}<br />
    	            <textarea name="MJ_account_address_street">{$infos->AddressStreet|escape|default:''}</textarea><br />
                    <div class="mj_account_sep2"></div>
	                <input type="text" name="MJ_account_address_postal_code" value="{$infos->AddressPostalCode|escape|default:''}" />&nbsp;
                  		<input type="text"name="MJ_account_address_city" value="{$infos->AddressCity|escape|default:''}" /><br />
                    <div class="mj_account_sep2"></div>
                    <select name="MJ_account_address_country">
                    	<option>---- {l s='Country' mod='mailjet'} ----</option>
                    	{foreach $countries as $pays}
                        	<option value="{$pays.iso_code|escape|default:''}"{if $pays.iso_code==$infos->AddressCountry} selected{/if}>{$pays.name|escape|default:''}</option>
                        {/foreach}
                    	</select><br />
				</div>
			    <input type="submit" name="MJ_set_account_details" value="{l s='Save' mod='mailjet'}" class="savebutton button" />
                <a href="javascript:;" onClick="$('#mj_account_details').show();$('#mj_account_details_mod').hide();" class="closebutton">X</a>
            </div>
		</fieldset>
		<br />
		<fieldset id="mj_account_deliverability">
			<legend>{l s='DKIM/SPF for better deliverability' mod='mailjet'}</legend>
            {foreach $domains as $domain}
            	<div>
	            	<b>Domain @{$domain->DNS->Domain|escape}</b> <i>( {if $domain->Status=='Active'}{l s='enabled' mod='mailjet'}{else}{l s='pending' mod='mailjet'}{/if} )</i><br />
                    <p>
	        	        {l s='Root file' mod='mailjet'} : <a href="/modules/mailjet/ajax.php?emptyfile&name={$domain->Filename|escape|default:''}"><u>{$domain->Filename|escape|default:''}</u></a><br />
    	                <i>{l s='File to put at your root folder to activate your domain' mod='mailjet'}</i>
					</p>
				</div>
            {/foreach}
            <br />
			<center>
	            {if $available_domain}
		            {if $root_file}
                    	<b class="vert">{l s='The activation file is present to the root folder !' mod='mailjet'}</b>
					{else}
    		        	<input type="submit" name="submitCreateRootFile" value="{l s='Create and place the activation file in the root folder' mod='mailjet'}" class="button" />
	                {/if}
				{else}
                   	<b class="rouge">{l s='The current domain is not present in the available domains list !' mod='mailjet'}</b>
                {/if}
			</center>
		</fieldset>

	</div>
	<div id="mj_account_page_right">
        <fieldset>
            <legend>{l s='Sender addresses' mod='mailjet'}</legend>
            <div id="mj_account_senders">
	            <p><b>{l s='Individual addresses' mod='mailjet'}</b></p>
	            {if $is_senders}
	            	<ul>
		            {foreach $sender as $address}
                   
		            		<li style="{if $address->Status=='Active'}color:#000;font-weight:bold;{else}color:#808080;font-style:italic;{/if}">- {$address->Email->Email|escape} {if $address->Status!='Active'}({l s='pending' mod='mailjet'}){/if}</li>
						
	    	        {/foreach}
	                </ul>
				{else}
	            	- {l s='No inidivdual address...' mod='mailjet'}<br />
	            {/if}
	            <br />
				<p><b>{l s='Activated domains' mod='mailjet'}</b></p>
	            {if $domains || $is_domains}
	            	<ul>
		            {foreach $domains as $address}
		            	{if $address->Status=='Active'}
	            			<li style="{if $address->Status=='Active'}color:#000;font-weight:bold;{else}color:#808080;font-style:italic;{/if}">- {$address->DNS->Domain|escape} {if $address->Status!='Active'}({l s='pending' mod='mailjet'}){/if}</li>
	    	        	{/if}
	    	        {/foreach}
	
	                </ul>
				{else}
	            	- {l s='No domain activated...' mod='mailjet'}<br />
	            {/if}
			    <a href="javascript:;" onClick="$('#mj_account_senders_mod').show();$('#mj_account_senders').hide();" class="savebutton button">{l s='Modify' mod='mailjet'}</a>
			</div>
            <div id="mj_account_senders_mod" style="display:none;">
                    <center>
                	<br />
            		<b>{l s='Add a new email or domain' mod='mailjet'}</b><br />
        	        <br />
                        <input type="text" name="MJ_account_senders_new" style="width:250px;" /><br />
                    </center>
                    <div style="height:40px;"></div>
                    <input type="submit" name="MJ_set_account_senders" value="{l s='Add' mod='mailjet'}" class="savebutton button" />
                <a href="javascript:;" onClick="$('#mj_account_senders').show();$('#mj_account_senders_mod').hide();" class="closebutton">X</a>
            </div>
        </fieldset>

        <br />
        <a href="https://www.mailjet.com/account" class="paramsbutton button" target="_blank">{l s='Advanced parameters' mod='mailjet'}</a>

	</div>
</div>
</form>
<!-- /Mailjet : Account -->