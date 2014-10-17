<!-- Mailjet : Account -->
<form action="{$smarty.server.REQUEST_URI|default:''}" method="POST">
<div id="mj_account_page" class="center_page">
	<div style="width:455px;float:left;">
    
		<fieldset>
			<legend>{l s='My details' mod='mailjet'}</legend>
            <div id="mj_account_details">
			    <div style="float:right;width:47%;">
				    {if $infos->Firstname || $infos->Lastname}
	                	<p>
				    	{if $infos->Firstname}{$infos->Firstname}&nbsp;{/if}
				    	{if $infos->Lastname}{$infos->Lastname}{/if}
				        </p>
					{/if}
				    {if $infos->Email}<p>{$infos->Email}</p>{/if}
	                <br />
				    {* if $language}{l s='Display' mod='mailjet'} : {$language}<br />{/if *}
			    </div>
			    {if $infos->CompanyName}<p>{$infos->CompanyName}</p>{/if}
	            <p>
			    {if $infos->AddressStreet}{$infos->AddressStreet|nl2br}<br />{/if}
			    {if $infos->AddressPostalCode || $infos->AddressPostalCode}
			    	{if $infos->AddressPostalCode}{$infos->AddressPostalCode}&nbsp;{/if}
			    	{if $infos->AddressCity}{$infos->AddressCity}{/if}
			        <br />
				{/if}
			    {if $country}{$country}<br />{/if}
	            </p>
			    <a href="javascript:;" onClick="$('#mj_account_details_mod').show();$('#mj_account_details').hide();" class="savebutton button">{l s='Modify' mod='mailjet'}</a>
			</div>
            <div id="mj_account_details_mod" style="display:none;">
            	<div style="width:40%;display:inline-block;">
	            	{l s='Firstname'}<br />
					<input type="text" name="MJ_account_firstname" value="{$infos->Firstname|default:''}" style="width:90%;" /><br />
				</div>
                <div style="width:50%;display:inline-block;">
					{l s='Lastname'}<br />
					<input type="text" name="MJ_account_lastname" value="{$infos->Lastname|default:''}" style="width:90%;" /><br />
				</div>
                <div style="display:block;">
                    <div style="height:10px;"></div>
	            	{l s='e-mail'}<br />
					<input type="text" name="MJ_account_contact_email" readonly value="{$infos->Email|default:''}" style="width:90%;background:#f0f0f0;" /><br />
                    <div style="height:10px;"></div>
	            	{l s='Company'}<br />
					<input type="text" name="MJ_account_company_name" value="{$infos->CompanyName|default:''}" style="width:90%;" /><br />
	            	{l s='Address'}<br />
    	            <textarea name="MJ_account_address_street" style="width:90%;height:60px;">{$infos->AddressStreet|default:''}</textarea><br />
                    <div style="height:5px;"></div>
	                <input type="text" name="MJ_account_address_postal_code" value="{$infos->AddressPostalCode|default:''}" style="width:20%;" />&nbsp;
                  		<input type="text"name="MJ_account_address_city" value="{$infos->AddressCity|default:''}" style="width:40%;" /><br />
                    <div style="height:5px;"></div>
                    <select name="MJ_account_address_country">
                    	<option>---- {l s='Country'} ----</option>
                    	{foreach $countries as $pays}
                        	<option value="{$pays.iso_code|default:''}"{if $pays.iso_code==$infos->AddressCountry} selected{/if}>{$pays.name|default:''}</option>
                        {/foreach}
                    	</select><br />
				</div>
			    <input type="submit" name="MJ_set_account_details" value="{l s='Save' mod='mailjet'}" class="savebutton button" />
                <a href="javascript:;" onClick="$('#mj_account_details').show();$('#mj_account_details_mod').hide();" class="closebutton">X</a>
            </div>
		</fieldset>

		<!--<br />
		<fieldset>
			<legend>{l s='Current plan' mod='mailjet'}</legend>
		</fieldset>-->

		<br />
		<fieldset>
			<legend>{l s='DKIM/SPF for better deliverability' mod='mailjet'}</legend>
            {foreach $domains as $domain}
            	<div>
	            	<b>Domain @{$domain->DNS->Domain}</b> <i>( {if $domain->Status=='Active'}{l s='enabled' mod='mailjet'}{else}{l s='pending' mod='mailjet'}{/if} )</i><br />
                    <p style="margin-left:10px;">
	        	        {l s='Root file' mod='mailjet'} : <a href="/modules/mailjet/emptyfile.php?name={$domain->Filename|default:''}"><u>{$domain->Filename|default:''}</u></a><br />
    	                <i style="font-size:11px;">{l s='File to put at your root folder to activate your domain' mod='mailjet'}</i>
					</p>
				</div>
            {/foreach}
            <br />
			<center>
	            {if $available_domain}
		            {if $root_file}
                    	<b style="color:#008000;">{l s='The activation file is present to the root folder !' mod='mailjet'}</b>
					{else}
    		        	<input type="submit" name="submitCreateRootFile" value="{l s='Create and place the activation file in the root folder' mod='mailjet'}" class="button" />
	                {/if}
				{else}
                   	<b style="color:#990000;">{l s='The current domain is not present in the available domains list !' mod='mailjet'}</b>
                {/if}
			</center>
		</fieldset>

	</div>
	<div style="width:455px;float:right;">
<!--
		<fieldset>
			<legend>{l s='Tracking' mod='mailjet'}</legend>
            <div id="mj_account_tracking">
		    	<p>{l s='Tracking of openers' mod='mailjet'} : {if $tracking->tracking_openers}{l s='yes'}{else}{l s='no'}{/if}</p>
		        <p>{l s='Tracking of links' mod='mailjet'} : {if $tracking->tracking_clicks}{l s='yes'}{else}{l s='no'}{/if}</p>
			    <a href="javascript:;" onClick="$('#mj_account_tracking_mod').show();$('#mj_account_tracking').hide();" class="savebutton button">{l s='Modify' mod='mailjet'}</a>
		    </div>
            <div id="mj_account_tracking_mod" style="display:none;">
            	<p>
                	<label>{l s='Tracking of openers' mod='mailjet'}</label>
					<input type="radio" name="MJ_account_tracking_openers" id="MJ_account_tracking_openers_1" value=1 {if $tracking->tracking_openers}checked{/if} /> <a href="javascript:;" onClick="$('#MJ_account_tracking_openers_1').click()">{l s='yes'}</a>
                     &nbsp; <input type="radio" name="MJ_account_tracking_openers" id="MJ_account_tracking_openers_0" value=0 {if !$tracking->tracking_openers}checked{/if} /> <a href="javascript:;" onClick="$('#MJ_account_tracking_openers_0').click()">{l s='no'}</a><br />
				</p>
            	<p>
                	<label>{l s='Tracking of links' mod='mailjet'}</label>
					<input type="radio" name="MJ_account_tracking_clicks" id="MJ_account_tracking_clicks_1" value=1 {if $tracking->tracking_clicks}checked{/if} /> <a href="javascript:;" onClick="$('#MJ_account_tracking_clicks_1').click()">{l s='yes'}</a>
                     &nbsp; <input type="radio" name="MJ_account_tracking_clicks" id="MJ_account_tracking_clicks_0" value=0 {if !$tracking->tracking_clicks}checked{/if} /> <a href="javascript:;" onClick="$('#MJ_account_tracking_clicks_0').click()">{l s='no'}</a><br />
				</p>
				<div style="height:40px;"></div>
			    <input type="submit" name="MJ_set_account_tracking" value="{l s='Save' mod='mailjet'}" class="savebutton button" />
                <a href="javascript:;" onClick="$('#mj_account_tracking').show();$('#mj_account_tracking_mod').hide();" class="closebutton">X</a>
            </div>
		</fieldset>
		
        <br />
		--!>

		<fieldset>
			<legend>{l s='Sender addresses' mod='mailjet'}</legend>
            <div id="mj_account_senders">
	            <p><b>{l s='Individual addresses' mod='mailjet'}</b></p>
	            {if $is_senders}
	            	<ul>
		            {foreach $sender as $address}
                   
		            		<li style="{if $address->Status=='Active'}color:#000;font-weight:bold;{else}color:#808080;font-style:italic;{/if}">- {$address->Email->Email} {if $address->Status!='Active'}({l s='pending' mod='mailjet'}){/if}</li>
						
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
	            			<li style="{if $address->Status=='Active'}color:#000;font-weight:bold;{else}color:#808080;font-style:italic;{/if}">- {$address->DNS->Domain} {if $address->Status!='Active'}({l s='pending' mod='mailjet'}){/if}</li>
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

        <!--<br />
		<fieldset>
			<legend>{l s='Invoicing and payment' mod='mailjet'}</legend>
		    <a href="javascript:;" class="savebutton button">{l s='Modify' mod='mailjet'}</a>
		</fieldset>-->

        <br />
        <a href="https://www.mailjet.com/account" class="paramsbutton button" target="_blank">{l s='Advanced parameters' mod='mailjet'}</a>

	</div>
</div>
</form>
<!-- /Mailjet : Account -->