{*
* 2007-2018 PrestaShop
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
* @copyright 2007-2018 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}
<script type="text/javascript">
    var datePickerJsFormat = "{$mj_datePickerJsFormat|escape:'javascript':'UTF-8'|default:'yy-mm-dd'}";
</script>


<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<style>
    .ui-autocomplete {
        max-height: 300px;
        overflow-y: auto;
        /* prevent horizontal scrollbar */
        overflow-x: hidden;
    }
    /* IE 6 doesn't support max-height
     * we use height instead, but this forces the menu to always be this tall
     */
    * html .ui-autocomplete {
        height: 300px;
    }
</style>
<script>
    $(function () {
        var senders = [];
    {foreach from=$mjSenders key=key item=value}
        {if $value->Status == 'Active'}
        senders[{$key|escape:'javascript':'UTF-8'}] = "{$value->Email->Email|escape:'javascript':'UTF-8'|default:' - '}";
        {/if}
    {/foreach}

        var currentSender = "{$currentSender|escape:'javascript':'UTF-8'}";
        var sendersClean = [];
        $.each(senders, function (key, sender) {
            if (typeof sender !== 'undefined') {
                sendersClean.push(sender);
            }
        });

        // check if current sender address is one of the API account validated senders
        if ($.inArray(currentSender, sendersClean) == -1) {
            currentSender = "";
        }

        $("#MJ_senders").autocomplete({
            source: sendersClean,
            minLength: 0,
            minChars: 0,
            max: 5,
            autoFill: true,
            mustMatch: true,
            matchContains: false,
            scrollHeight: 300,
        }).on('focus', function (event) {
            var self = this;
            $(self).autocomplete("search", "");
        });

        $('#MJ_set_allemails').on('click', function () {
            if ($("#MJ_senders").is(":visible")) {
                if ($.inArray($("#MJ_senders").val(), sendersClean) == -1) {
                    $("#MJ_senders").css('background-color', 'red');
                    alert("{l s='Could you please add a valid sender address?' mod='mailjet'}");
                    return false;
                }
                $("#MJ_senders").css('background-color', '#FFFFFF');
            }
        });

        $('#MJ_senders').on('focus', function () {
            $("#MJ_senders").css('background-color', '#FFFFFF');
        });

        if ($("#MJ_senders").val() == '' && currentSender == '' && sendersClean.length == 1) {
            $("#MJ_senders").val(sendersClean[0]);
        } else {
            $("#MJ_senders").val(currentSender);
        }

    });
</script>

<div class="center_page mj_home">
    <p class="hint">
        {l s='Mailjet sending all emails of your website, optimizes and automatically manages the statistical returns of errors.' mod='mailjet'}
    </p>

    <form action="{$MJ_adminmodules_link|escape:'htmlall':'UTF-8'|default:''}" id="home_form" method="POST">
        <div class="warn">
            {l s='Activate the sending of all email by Mailjet ?' mod='mailjet'} &nbsp; &nbsp;
            &nbsp; <input type="radio" name="MJ_allemails_active" id="MJ_allemails_active_1" value=1 onClick="jQuery('#mj_senders_list').slideDown()" {if $MJ_allemails_active}checked{/if} /> <label class="t" for="MJ_allemails_active_1">{l s='YES' mod='mailjet'}</label>
            &nbsp; <input type="radio" name="MJ_allemails_active" id="MJ_allemails_active_0" value=0 onClick="jQuery('#mj_senders_list').slideUp()" {if !$MJ_allemails_active}checked{/if} /> <label class="t" for="MJ_allemails_active_0">{l s='NO' mod='mailjet'}</label>
            &nbsp; &nbsp; <input type="submit" value=" {l s='Modify' mod='mailjet'} " name="MJ_set_allemails" id="MJ_set_allemails" class="button" />

            <div  id="mj_senders_list" style="color: #9e6014; width:300px; margin-bottom:10px;{if !$MJ_allemails_active} display:none;{/if} ">
                {l s='SENDER ADDRESS for transactional emails' mod='mailjet'} 
                <div class="ui-widget">
                    <input name="MJ_senders" id="MJ_senders" style="width:300px; border: 1px solid #9e6014 !important;">
                </div>
            </div>            
        </div>
    </form>
    {if isset($AllMailsActiveMessage)}
        {if $AllMailsActiveMessage == 1}
            {l s='Great!  Now all of your email traffic will send through Mailjet and benefit from awesome deliverability.  Welcome Aboard.' mod='mailjet'}
        {else}
            {l s='We are sad. This means your transactional and triggered emails will not benefit from Mailjet\'s advanced deliverability but don\'t worry, you can still use the Campaign Tool to send Newsletters.' mod='mailjet'}
        {/if}
    {/if}

    <h3>{l s='What you can do with the module' mod='mailjet'} :</h3>
    <div class="home_list_tab">
        <ul>
            <li>
                <a href="{$MJ_adminmodules_link|escape:'htmlall':'UTF-8'|default:''}&{$MJ_REQUEST_PAGE_TYPE|escape:'htmlall':'UTF-8'|default:''}=SEGMENTATION" class="btn_tab_home"><span></span><span>{l s='Segmentation' mod='mailjet'}</span></a>
                        {l s='With the help of pre-defined eCommerce filters and criteria, create different target client and prospect segments that you can then send targeted messages to.' mod='mailjet'}
            </li>
            <li>
                <a href="{$MJ_adminmodules_link|escape:'htmlall':'UTF-8'|default:''}&{$MJ_REQUEST_PAGE_TYPE|escape:'htmlall':'UTF-8'|default:''}=CAMPAIGN" class="btn_tab_home"><span></span><span>{l s='Campaigns' mod='mailjet'}</span></a>
                        {l s='Create and send a marketing newsletter blast to your client base via a drag-and-drop HTML designer and a gallery of pre-defined templates all within a few clicks.' mod='mailjet'}
            </li>
            <li>
                <a href="{$MJ_adminmodules_link|escape:'htmlall':'UTF-8'|default:''}&{$MJ_REQUEST_PAGE_TYPE|escape:'htmlall':'UTF-8'|default:''}=TRIGGERS" class="btn_tab_home"><span></span><span>{l s='Transactional emails' mod='mailjet'}</span></a>
                        {l s='... Description of Transactionnal emails on home page of the module ...' mod='mailjet'}
            </li>
            <li>
                <a href="{$MJ_adminmodules_link|escape:'htmlall':'UTF-8'|default:''}&{$MJ_REQUEST_PAGE_TYPE|escape:'htmlall':'UTF-8'|default:''}=CONTACTS" class="btn_tab_home"><span></span><span>{l s='Contact Lists' mod='mailjet'}</span></a>
                        {l s='This is where you synchronise and sync your master contact lists which can then be segmented and/or targeted with specific messages. You can also look up which contacts received previous emails and clicked where and when. This is also the space to manually add any new contacts.' mod='mailjet'}
            </li>
            <li>
                <a href="{$MJ_adminmodules_link|escape:'htmlall':'UTF-8'|default:''}&{$MJ_REQUEST_PAGE_TYPE|escape:'htmlall':'UTF-8'|default:''}=STATS" class="btn_tab_home"><span></span><span>{l s='Stats' mod='mailjet'}</span></a>
                        {l s='Analyse the flow, impact and client interaction of the different email streams that you send as a merchant. Access all the different statistics that you could ever dream of for your targeted messages, your marketing campaigns and transactional email.' mod='mailjet'}
            </li>
            <li>
                <a href="{$MJ_adminmodules_link|escape:'htmlall':'UTF-8'|default:''}&{$MJ_REQUEST_PAGE_TYPE|escape:'htmlall':'UTF-8'|default:''}=ROI" class="btn_tab_home"><span></span><span>{l s='R.O.I' mod='mailjet'}</span></a>
                        {l s='... Description of R.O.I on home page of the module ...' mod='mailjet'}
            </li>
            <li>
                <a href="{$MJ_adminmodules_link|escape:'htmlall':'UTF-8'|default:''}&{$MJ_REQUEST_PAGE_TYPE|escape:'htmlall':'UTF-8'|default:''}=EVENTS" class="btn_tab_home"><span></span><span>{l s='Contact Management' mod='mailjet'}</span></a>
                        {l s='Keep your contact lists up to date by updating and removing the different bounces and blocked email addresses from your clients and prospects, completely up to date. Need to correct an email address ? Update it here and that\'s one more client contact point saved for the future !' mod='mailjet'}
            </li>
            <li>
                <a href="{$MJ_adminmodules_link|escape:'htmlall':'UTF-8'|default:''}&{$MJ_REQUEST_PAGE_TYPE|escape:'htmlall':'UTF-8'|default:''}=ACCOUNT" class="btn_tab_home"><span></span><span>{l s='My account' mod='mailjet'}</span></a>
                        {l s='This menu allows you to modify your settings and update them in order to optimise your deliverability. You will also find all your profile and billing details here.' mod='mailjet'}
            </li>
            <li>
                <a href="http://www.mailjet.com/pricing_v3" target="_blank" class="btn_tab_home"><span></span><span>{l s='Upgrade' mod='mailjet'}</span></a>
                        {l s='Click here to change/upgrade your current plan.' mod='mailjet'}
            </li>

        </ul>
    </div>
</div>
