{* Has to be initiated here cause it's defined after the postProcess *}
<script type="text/javascript">
	var MJ_page_name = "{$MJ_page_name}";
</script>

{if !$is_landing}
<div class="logo_mailjet_tiny"></div>
<div id="MJ_tab_menu">
	<ul id="MJ_tab">
    {if $MJ_authentication}
            {foreach from=$MJ_tab_page key=MJ_key item=MJ_title}
                <li {if $MJ_page_name == $MJ_key}class="active"{/if}>
                    <a href="{$MJ_adminmodules_link}&{$MJ_REQUEST_PAGE_TYPE}={$MJ_key}">{$MJ_title}</a>
                </li>
            {/foreach}
    {else}

    {/if}
	</ul>
</div>
<div class="bandeau_noir"></div>
{/if}
{if isset($MJ_errors) && count($MJ_errors)}
	<div class="alert error">
		{l s='Errors list:' mod='socolissimo'}
		<ul style="margin-top: 10px;">
			{foreach from=$MJ_errors item=current_error}
				<li>{$current_error}</li>
			{/foreach}
		</ul>
	</div>
{/if}

{include file="$MJ_local_path/tpl/$MJ_template_name.tpl"}

{if !$is_landing}
<div style="clear:both;width:960px;margin:0 auto;">
    <div align="center"><br />
    <a href="http://fr.mailjet.com/support" target="_blank">{l s='If you have a question or if you have a problem, click here to contact support.' mod='mailjet'}</a>
    </div>
</div>
{/if}