<div class="center_page">
	<div class="logo_mailjet_center"></div>
    {$MJ_templates.setup_landing_message}
    
    <div id="setup_landing_bt">
{*        <a href="{$smarty.server.REQUEST_URI}&mj_check_hosting=true" id="setup_landing_bt_activate" class="default_button_style default_background_orange">
            {l s='Register' mod='mailjet'}
        </a>*}
        <a target="_blank" href="https://www.mailjet.com/signup?p=prestashop-3.0" id="setup_landing_bt_activate" class="default_button_style default_background_orange">
            {l s='Register' mod='mailjet'}
        </a>
        <a href="{$MJ_adminmodules_link}&{$MJ_REQUEST_PAGE_TYPE}=CONNECT_STEP_0" id="setup_landing_bt_connect" class="default_button_style">
        	{l s='Connect' mod='mailjet'}
        </a>
{*        <a href="{$MJ_adminmodules_link}&{$MJ_REQUEST_PAGE_TYPE}=PRICING" id="setup_landing_bt_pricing" class="default_button_style">
        	{l s='Pricing' mod='mailjet'}
        </a>*}
        <a target="_blank" href="http://www.mailjet.com/pricing " id="setup_landing_bt_pricing" class="default_button_style">
        	{l s='Pricing' mod='mailjet'}
        </a>
        <br clear="left"/>
    </div>
</div>