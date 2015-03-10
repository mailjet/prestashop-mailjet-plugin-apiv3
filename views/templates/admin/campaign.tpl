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
<div id="mj_campaigns_page" class="center_page">
	<form id="create" method="post" action="{$smarty.server.REQUEST_URI|escape|default:''}">
    <input type="hidden" name="MJ_submitCampaign" value=1 />
	<fieldset>
    	<legend>{l s='Campaigns' mod='mailjet'}</legend>
		<div align="right" class="fieldset_buttons"><a class="button" href="index.php?tab=AdminModules&configure=mailjet&module_name=mailjet&MJ_request_page=CAMPAIGN1&token={$token|escape|default:''}"><img src="../img/admin/add.gif" alt="{l s='Create a new Campign' mod='mailjet'}" title="{l s='Create a new Campign' mod='mailjet'}"> {l s='Create a new Campaign' mod='mailjet'}</a></div>
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
        	<td>{$campaign->id|escape|default:''}</td>
        	<td nowrap>{$campaign->title|escape|default:''}</td>
        	<td>{$campaign->status|escape|default:''}</td>
        	<td>{$campaign->edition_mode|escape|default:''}</td>
        	<td>{$campaign->updated_ts|escape|date_format:"%Y-%m-%d %H:%M:%S"}</td>
        	<td>{$campaign->created_ts|escape|date_format:"%Y-%m-%d %H:%M:%S"}</td>
        </tr>
        {/foreach}
        </table>
	</fieldset>
	</form>
</div>
<!-- /Mailjet : Campaigns -->