<!-- Mailjet : Campaigns -->
<div id="mj_campaigns_page" class="center_page" style="width:initial;max-width:960px;">
	<form id="create" method="post" action="{$smarty.server.REQUEST_URI}">
    <input type="hidden" name="MJ_submitCampaign" value=1 />
	<fieldset>
    	<legend>{l s='Campaigns' mod='mailjet'}</legend>
		<div align="right" style="position:relative;top:-10px;"><a class="button" href="index.php?tab=AdminModules&configure=mailjet&module_name=mailjet&MJ_request_page=CAMPAIGN1&token={$token}"><img src="../img/admin/add.gif" alt="{l s='Create a new Campign' mod='mailjet'}" title="{l s='Create a new Campign' mod='mailjet'}"> {l s='Create a new Campaign' mod='mailjet'}</a></div>
        <table class="table" width="100%">
        <tr>
        	<th>{l s='ID' mod='mailjet'}</th>
        	<th>{l s='Title' mod='mailjet'}</th>
        	<th>{l s='status' mod='mailjet'}</th>
        	<th>{l s='edition mode' mod='mailjet'}</th>
        	<th>{l s='update' mod='mailjet'}</th>
        	<th>{l s='creation' mod='mailjet'}</th>
       	</tr>
        {foreach from=$campaigns item=campaign name=campaigns}
        <tr  {if $smarty.foreach.campaigns.index % 2 == 0}class="alt_row"{/if}>
        	<td>{$campaign->id}</td>
        	<td nowrap>{$campaign->title}</td>
        	<td>{$campaign->status}</td>
        	<td>{$campaign->edition_mode}</td>
        	<td>{$campaign->updated_ts|date_format:"%Y-%m-%d %H:%M:%S"}</td>
        	<td>{$campaign->created_ts|date_format:"%Y-%m-%d %H:%M:%S"}</td>
        </tr>
        {/foreach}
        </table>
	</fieldset>
	</form>
</div>
<!-- /Mailjet : Campaigns -->