
<div id="mailjet">

	<h2>Mailjet</h2>

	{foreach from=$MJ_stats item=fields}

	<dl class="MJ_data">
		{foreach from=$fields item=field}
			<dt>{$field.title}&nbsp;</dt>
			<dd>{$field.value}&nbsp;</dd>
		{/foreach}
	</dl>
	{/foreach}
</div>