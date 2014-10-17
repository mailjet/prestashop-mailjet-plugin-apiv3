<div class="center_page">
<form action="{$MJ_adminmodules_link}" method="post">
<fieldset>
	<legend>{l s='Your Mailjet login information' mod='mailjet'}</legend>
	<div>
    	<p>{l s='Please enter below your Mailjet login information.' mod='mailjet'} (<a href="https://eu.mailjet.com/account/api_keys" target="_blank"><u>{l s='Mailjet account'}</u></a>)</p>
    	<p><label>Api Key</label><input type="text" name="mj_api_key" value="{$account.API_KEY}" size="60" /></p>
        <p><label>Secret Key :</label><input type="text" name="mj_secret_key" value="{$account.SECRET_KEY}" size="60" /></p>
        
        <!--<p><label>API Key :</label><input type="text" name="mj_api_key" value="{$account.API_KEY}" size="80" /></p>
        <p><label>Secret Key :</label><input type="text" name="mj_secret_key" value="{$account.SECRET_KEY}" size="80" /></p>-->
        
        <p><label>&nbsp;</label><input type="submit" name="MJ_set_connect" value="{l s='Save &amp; Login' mod='mailjet'}" class="button" /></p>
    </div>
</fieldset>
</form>
<p><a href="javascript:;" onclick="history.back()">&lt; {l s='Back to home'}</a></p>
</div>