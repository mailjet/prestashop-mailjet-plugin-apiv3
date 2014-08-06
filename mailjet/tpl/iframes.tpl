{if $MJ_page_name == "PRICING"}
	{if $MJ_TOKEN_USER}
		{if $MJ_user_plan}
			<div class="center_page">
				<p class="error">{l s='warning_pricing' mod='mailjet'}</p>
			</div>
		{/if}
	{/if}
{/if}
<iframe border="0" style="border:0px;" width="100%" height="800px" src="{$MJ_iframes.$MJ_page_name}{if !empty($MJ_TOKEN_USER)}{/if}">
</iframe>