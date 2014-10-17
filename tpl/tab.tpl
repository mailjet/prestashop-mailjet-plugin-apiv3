
{if isset($MJ_iframes.$MJ_page_name)}
	{include file="$MJ_local_path/tpl/iframes.tpl"}
{else}
	{include file="$MJ_local_path/tpl/tab/{$MJ_template_tab_name}.tpl"}
{/if}