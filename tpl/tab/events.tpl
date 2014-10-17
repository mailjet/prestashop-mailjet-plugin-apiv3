<div class="center_page">
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
						<li>{$id_mj_events}</li>
					{/foreach}
				</ul>
			</div>
		{/if}
	{/if}

	{if !count($MJ_events_list)}

    	<div class="warn">
        	&nbsp; {l s='To activate Events, yous must go to your Mailjet account : ' mod='mailjet'} <a href="https://eu.mailjet.com/account/triggers" target="_blank"><u>https://eu.mailjet.com/account/triggers</u></a><br />
            <br />
            <b style="color:#000;">
            {l s='Specify the' mod='mailjet'} <span style="color:#900;">Endpoint Url</span> : <input type="text" value="{$url}" style="width:700px;padding:3px;"/><br />
            {l s='and click the Events you want to activate' mod='mailjet'}...<br />
            </b>
            <br />
        </div>
		{l s='No elements exist with this event type' mod='mailjet'}

	{else}

	<form action="{$smarty.server.REQUEST_URI}" method="POST">
		<div id="tableWrapper" style="width: 100%;">
			<table cellpadding="1" cellspacing="1" id="vsTable">
				<thead>
					<tr>
						<th class="title">&nbsp;</th>
						<th class="title" style="text-align:center;">ID</th>
						{foreach from=$MJ_title_list item=title}
							<th class="title" style="text-align:center;">{$title}</th>
						{/foreach}
					</tr>
				</thead>
				<tbody>
					{foreach from=$MJ_events_list item=fields}
						<tr class="cat">
							<td>
								<input type="checkbox" value="{$fields.id_mj_events}" name="events[]" />
							</td>
							{foreach from=$fields item=field}
								<td style="text-align:center;">{$field}</td>
							{/foreach}
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
		<div class="MJ_event_link">
			<input type="submit" value="{l s='Fix the selected errors' mod='mailjet'}" />
			{if $MJ_paging.prev}
				<a href="{$MJ_adminmodules_link}&{$MJ_REQUEST_PAGE_TYPE}={$MJ_page_name}&page=1"><<</a>
				<a href="{$MJ_adminmodules_link}&{$MJ_REQUEST_PAGE_TYPE}={$MJ_page_name}&page={$MJ_paging.current_page - 1}"><</a>
			{/if}
			<a href="javascript:void(0)">{$MJ_paging.current_page}</a>
			{if $MJ_paging.next}
				<a href="{$MJ_adminmodules_link}&{$MJ_REQUEST_PAGE_TYPE}={$MJ_page_name}&page={$MJ_paging.current_page + 1}">></a>
				<a href="{$MJ_adminmodules_link}&{$MJ_REQUEST_PAGE_TYPE}={$MJ_page_name}&page={$MJ_paging.last}">>></a>
			{/if}
		</div>
	</form>

	{/if}

</div>
</a>