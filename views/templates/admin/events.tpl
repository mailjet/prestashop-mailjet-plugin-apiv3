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
*}<div class="center_page">
<div id="events">
	{if isset($MJ_events_form_success)}
		{if !count($MJ_events_form_success)}
			<div class="conf confirm">
				{l s='Elements has been properly set has fixed' mod='mailjet'}
			</div>
		{else}
			<div class="error">
				{l s='Some elements canot be set has fixed' mod='mailjet'}
				<ul>
					{foreach from=$MJ_events_form_success item=id_mj_events}
						<li>{$id_mj_events|escape|default:''}</li>
					{/foreach}
				</ul>
			</div>
		{/if}
	{/if}

	{if !count($MJ_events_list)}

    	<div class="warn">
        	&nbsp; {l s='To activate Events, yous must go to your Mailjet account : ' mod='mailjet'} <a href="https://mailjet.com/account/triggers" target="_blank"><u>https://mailjet.com/account/triggers</u></a><br />
            <br />
            <b >
            {l s='Specify the' mod='mailjet'} <span>Endpoint Url</span> : <input type="text" value="{$url|escape}"/><br />
            {l s='and click the Events you want to activate' mod='mailjet'}...<br />
            </b>
            <br />
        </div>
		{l s='No elements exist with this event type' mod='mailjet'}

	{else}

	<form action="{$smarty.server.REQUEST_URI|escape|default:''}" method="POST">
		<div id="tableWrapper">
			<table cellpadding="1" cellspacing="1" id="vsTable">
				<thead>
					<tr>
						<th class="title">&nbsp;</th>
						<th class="title">ID</th>
						{foreach from=$MJ_title_list item=title}
							<th class="title">{$title|escape|default:''}</th>
						{/foreach}
					</tr>
				</thead>
				<tbody>
					{foreach from=$MJ_events_list item=fields}
						<tr class="cat">
							<td>
								<input type="checkbox" value="{$fields.id_mj_events|escape|default:''}" name="events[]" />
							</td>
							{foreach from=$fields item=field}
								<td>{$field|escape|default:''}</td>
							{/foreach}
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
		<div class="MJ_event_link">
			<input type="submit" value="{l s='Fix the selected errors' mod='mailjet'}" />
			{if $MJ_paging.prev}
            	{assign var=calc value=$MJ_paging.current_page - 1}
				<a href="{$MJ_adminmodules_link|escape|default:''}&{$MJ_REQUEST_PAGE_TYPE|escape|default:''}={$MJ_page_name|escape|default:''}&page=1"><<</a>
				<a href="{$MJ_adminmodules_link|escape|default:''}&{$MJ_REQUEST_PAGE_TYPE|escape|default:''}={$MJ_page_name|escape|default:''}&page={$calc|escape|default:''}"><</a>
			{/if}
			<a href="javascript:void(0)">{$MJ_paging.current_page|escape|default:''}</a>
			{if $MJ_paging.next}
            	{assign var=calc value=$MJ_paging.current_page + 1}
				<a href="{$MJ_adminmodules_link|escape|default:''}&{$MJ_REQUEST_PAGE_TYPE|escape|default:''}={$MJ_page_name|escape|default:''}&page={$calc|escape|default:''}">></a>
				<a href="{$MJ_adminmodules_link|escape|default:''}&{$MJ_REQUEST_PAGE_TYPE|escape|default:''}={$MJ_page_name|escape|default:''}&page={$MJ_paging.last|escape}">>></a>
			{/if}
		</div>
	</form>

	{/if}

</div>
</a>