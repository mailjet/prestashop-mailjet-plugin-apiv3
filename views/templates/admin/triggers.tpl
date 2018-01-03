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
*}<!-- Mailjet : Triggers -->

{* $tinymce|default:'' *}
{if $tinymce_new}
    <script type="text/javascript">
        var iso = '{$tinymce_iso|escape:'htmlall':'UTF-8'|default:'en'}';
        var pathCSS = '{$tinymce_pathCSS|escape:'htmlall':'UTF-8'|default:''}';
        var ad = '{$tinymce_ad|escape:'htmlall':'UTF-8'|default:''}';    
    </script>
    <script type="text/javascript" src="{$tinymce_pathBase|escape:'htmlall':'UTF-8'|default:'/'}js/tiny_mce/tiny_mce.js"></script>
    <script type="text/javascript" src="{$tinymce_pathBase|escape:'htmlall':'UTF-8'|default:'/'}js/tinymce.inc.js"></script>
    <script language="javascript" type="text/javascript">
                var id_language = Number({$tinymce_id_language|escape:'htmlall':'UTF-8'|default:'en'});    </script>
{else}
    <script type="text/javascript" src="{$tinymce_pathBase|escape:'htmlall':'UTF-8'|default:'/'}js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
    <script type="text/javascript">
        tinyMCE.init({
            mode : "textareas",
            theme : "advanced",
            plugins : "safari,pagebreak,style,layer,table,advimage,advlink,inlinepopups,media,searchreplace,contextmenu,paste,directionality,fullscreen",
            theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
            theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,,|,forecolor,backcolor",
            theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,fullscreen",
            theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,pagebreak",
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_statusbar_location : "bottom",
            theme_advanced_resizing : false,
            content_css : "{$tinymce_pathBase|escape:'htmlall':'UTF-8'}themes/{$tinymce_theme|escape:'htmlall':'UTF-8'|default:'default-bootstrap'}/css/global.css",
            document_base_url : "{$tinymce_pathBase|escape:'htmlall':'UTF-8'|default:'/'}",
            width: "600",
            height: "auto",
            font_size_style_values : "8pt, 10pt, 12pt, 14pt, 18pt, 24pt, 36pt",
            template_external_list_url : "lists/template_list.js",
            external_link_list_url : "lists/link_list.js",
            external_image_list_url : "lists/image_list.js",
            media_external_list_url : "lists/media_list.js",
            elements : "nourlconvert",
            entity_encoding: "raw",
            convert_urls : false,
            language : "{$tinymce_iso|escape:'htmlall':'UTF-8'|default:'en'}"
        });
        id_language = Number({$tinymce_id_language|escape:'htmlall':'UTF-8'|default:'en'});    </script>
{/if}
<script type="text/javascript">
    $(document).ready(function () {
        $('#exportLabel, #importLabel').css('cursor', 'pointer');
        var initialExportLabelText = $('#exportLabel').text();
        var initialImportLabelText = $('#importLabel').text();
        $('#exportLabel, #MJ_triggers_export_submit').mouseover(function() {
            $('#exportLabel').text("{l s='(Useful when upgrading Mailjet add-on)' mod='mailjet'}");
            $('#exportLabel').fadeIn(500);
        });
        $('#exportLabel, #MJ_triggers_export_submit').mouseout(function() {
            $('#exportLabel').text(initialExportLabelText);
            $('#exportLabel').fadeIn(500);
        });
        $('#importLabel, #MJ_triggers_import_submit').mouseover(function() {
            $('#importLabel').text("{l s='(WARNING - this overwrites your current trigger templates)' mod='mailjet'}");
            $('#importLabel').fadeIn(500);
        });
        $('#importLabel, #MJ_triggers_import_submit').mouseout(function() {
            $('#importLabel').text(initialImportLabelText);
            $('#importLabel').fadeIn(500);
        });
    });
    function validateFile() {
        var ext = $('#MJ_triggers_import_file').val().split('.').pop().toLowerCase();
        if ($.inArray(ext, ['txt']) == - 1) {
            alert("{l s='Add a valid file to import trigger templates from' mod='mailjet'}");
            return false;
        }
        return true;
    }


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
    $(function() {
        var senders = [];
        {foreach from=$mjSenders key=key item=value}
            {if $value->Status == 'Active'}
                senders[{$key|escape:'javascript':'UTF-8'}] = "{$value->Email->Email|escape:'javascript':'UTF-8'|default:' - '}";
            {/if}
        {/foreach}

        var currentSender = "{$currentSender|escape:'javascript':'UTF-8'}";
        var sendersClean = [];
        $.each(senders, function(key, sender) {
            if (typeof sender !== 'undefined') {
                sendersClean.push(sender);
            }
        });
        // check if current sender address is one of the API account validated senders
        if ($.inArray(currentSender, sendersClean) == - 1) {
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
        }).on('focus', function(event) {
            var self = this;
            $(self).autocomplete("search", "");
        });
        $('#MJ_set_triggers').on('click', function() {
            if ($("#MJ_senders").is(":visible")) {
                if ($.inArray($("#MJ_senders").val(), sendersClean) == - 1) {
                    $("#MJ_senders").css('background-color', 'red');
                    alert("{l s='Could you please add a valid sender address?' mod='mailjet'}");
                    return false;
                }
                $("#MJ_senders").css('background-color', '#FFFFFF');
            }
        });
        $('#MJ_senders').on('focus', function() {
            $("#MJ_senders").css('background-color', '#FFFFFF');
        });
        if ($("#MJ_senders").val() == '' && currentSender == '' && sendersClean.length == 1) {
            $("#MJ_senders").val(sendersClean[0]);
        } else {
            $("#MJ_senders").val(currentSender);
        }
    });
</script>


<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'|default:''}" method="POST">
    <div id="mj_triggers_page" class="center_page">
        <div class="warn">{l s='To activate the triggers you need to set up this cron job' mod='mailjet'} :<br />
            <input type="text" readonly value="{$cron|escape:'htmlall':'UTF-8'}" size=135" />
        </div>
        <fieldset class="hint">
            <legend>{l s='Do you wish to activate eCommerce transactional email ?' mod='mailjet'}</legend>
            <div>
                <input type="radio" name="MJ_triggers_active" id="MJ_triggers_active_1" value=1 onClick="$('#triggers_options, #triggers_import_export, #mj_senders_list').slideDown()" {if $MJ_allemails_active && $triggers.active}checked{/if} {if !$MJ_allemails_active}disabled{/if} /> <a href="javascript:;" onClick="$('#MJ_triggers_active_1').click();">{l s='YES' mod='mailjet'}</a> &nbsp;
                <input type="radio" name="MJ_triggers_active" id="MJ_triggers_active_0" value=0 onClick="$('#triggers_options, #triggers_import_export, #mj_senders_list').slideUp()" {if !$MJ_allemails_active || !$triggers.active}checked{/if} {if !$MJ_allemails_active}disabled{/if} /> <a href="javascript:;" onClick="$('#MJ_triggers_active_0').click();">{l s='NO' mod='mailjet'}</a><br />
            </div> 

            <br />
            <div  id="mj_senders_list" style="color: #9e6014; width:300px; {if !$MJ_allemails_active || !$triggers.active} display:none;{/if} ">
                {l s='SENDER ADDRESS for transactional emails' mod='mailjet'}                
                <div class="ui-widget" style="padding-left:0px;">
                    <input name="MJ_senders" id="MJ_senders" style="width:300px; border: 1px solid #9e6014 !important;">
                </div>
            </div>
            <br />   
            <input type="submit" name="MJ_set_triggers" id="MJ_set_triggers" value="{l s='Save Changes' mod='mailjet'}" onClick="this.value = ' {l s='Wait please...' mod='mailjet'} ';" class="savebutton button"  {if !$MJ_allemails_active}disabled{/if} style="{if !$MJ_allemails_active}display:none;{/if}" />
            <br />

            {if !$MJ_allemails_active}
                <br />
                <p class="warn">
                    {l s='Because you have selected to not send your transactional email via Mailjet on the plug-in Homepage, this means the triggered email module can\'t be activated either. To activate triggered emails, please go to the plug-in homepage and select "Yes" to have Mailjet send all of your email. This will then allow you to select "Yes" to activate the triggered emails module.' mod='mailjet'}
                </p>
            {/if}
            <br />
        </fieldset>
        <br />
        <fieldset id="triggers_options" {if $MJ_allemails_active && !$triggers.active}style="display:none;"{/if}>
            <legend>{l s='Triggers' mod='mailjet'}</legend>
            <ul>
                {for $sel=1 to 9}
                    <li style="{if $sel==4}display:none;{/if}">
                        <label>
                        {if $sel==1}{l s='Abandoned Cart Email' mod='mailjet'}{/if}
                    {if $sel==2}{l s='Payment failure recovery after canceled or blocked payment' mod='mailjet'}{/if}
                {if $sel==3}{l s='Order pending payment' mod='mailjet'}{/if}
            {if $sel==4}{l s='Shipment Delay Notification' mod='mailjet'}{/if}
        {if $sel==5}{l s='Birthday promo' mod='mailjet'}{/if}
    {if $sel==6}{l s='Purchase Anniversary promo' mod='mailjet'}{/if}
{if $sel==7}{l s='Customers who have not ordered since few time' mod='mailjet'}{/if}
{if $sel==8}{l s='Satisfaction survey' mod='mailjet'}{/if}
{if $sel==9}{l s='Loyalty points reminder' mod='mailjet'}{/if} : 
</label>
<div class="mj_radios">
    <input {if !$MJ_allemails_active}disabled{/if} type="radio" name="MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'}_active" id="MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'}_active_1" value=1 {if $triggers.trigger.$sel.active}checked{/if} onClick="$('#MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'}_button').show();" /> <a href="javascript:;" onClick="{if !$MJ_allemails_active}return false;{/if}  $('#MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'}_active_1').click();">{l s='Yes' mod='mailjet'}</a> &nbsp;
    <input {if !$MJ_allemails_active}disabled{/if} type="radio" name="MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'}_active" id="MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'}_active_0" value=0 {if !$MJ_allemails_active || !$triggers.trigger.$sel.active}checked{/if} onClick="$('#MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'}_button').hide(); $('#MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'}_parameters').hide();" /> <a href="javascript:;" onClick="{if !$MJ_allemails_active}return false;{/if} $('#MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'}_active_0').click();">{l s='No' mod='mailjet'}</a> &nbsp;
    <a href="javascript:;" onClick="{if !$MJ_allemails_active}return false;{/if} $('#MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'}_parameters').slideToggle();" id="MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'}_button" class="button MJ_triggers_trigger_buttons" style="{if !$MJ_allemails_active || !$triggers.trigger.$sel.active}display:none;{/if}" />{l s='parameters' mod='mailjet'}</a> &nbsp;
    <br />
</div>
<span class="clearspan"></span>
<div id="MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'|default:''}_parameters" class="warn mj_triggers_parameters">
    <b>{l s='Parameters' mod='mailjet'}</b><br />
    {if $sel!=5 && $sel!=6}
        <label>{l s='Trigger sending after how long ?' mod='mailjet'}</label>
        <input type="text" name="MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'|default:''}_period" size=5 value="{$triggers.trigger.$sel.period|escape:'htmlall':'UTF-8'|default:''}" />
        <select name="MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'|default:''}_periodType">
            <option value=1 {if $triggers.trigger.$sel.periodType==1}selected{/if}>{l s='Month' mod='mailjet'}</option>
            <option value=2 {if $triggers.trigger.$sel.periodType==2}selected{/if}>{l s='Days' mod='mailjet'}</option>
            <option value=3 {if $triggers.trigger.$sel.periodType==3}selected{/if}>{l s='Hours' mod='mailjet'}</option>
            <option value=4 {if $triggers.trigger.$sel.periodType==4}selected{/if}>{l s='Minutes' mod='mailjet'}</option>
        </select>
        <br />
    {else}
        <label>{l s='Reduction amount' mod='mailjet'} :</label>
        <input type="text" name="MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'|default:''}_discount" size=5 value="{$triggers.trigger.$sel.discount|escape:'htmlall':'UTF-8'|default:''}" />
        <select name="MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'|default:''}_discountType">
            <option value=1 {if $triggers.trigger.$sel.discountType==1}selected{/if}>(%) {l s='Percentage' mod='mailjet'}</option>
            <option value=2 {if $triggers.trigger.$sel.discountType==2}selected{/if}>($,€,£) {l s='Amount' mod='mailjet'}</option>
        </select>
        <br />
    {/if}
    <br />
    <center>
        <div class="sel_lang">
            <b class="b_black">{l s='Message' mod='mailjet'}</b> - {l s='Language select' mod='mailjet'} : &nbsp;
            {foreach $languages as $language}
                {assign var="id_lang" value=$language.id_lang|escape:'htmlall':'UTF-8'}
                <a href="javascript:;"
                   onClick="$('.id_lang_close_{$sel|escape:'htmlall':'UTF-8'}').hide(); $('#id_lang_{$sel|escape:'htmlall':'UTF-8'}_{$id_lang|escape:'htmlall':'UTF-8'}').show(); $('.flags_{$sel|escape}').removeClass('selFlag'); $(this).addClass('selFlag');"
                   class="{if $id_lang==$sel_lang}selFlag{/if} flags_{$sel|escape:'htmlall':'UTF-8'}"
                   title="{$language.name|escape:'htmlall':'UTF-8'}"><img src="../img/l/{$id_lang|escape:'htmlall':'UTF-8'}.jpg" alt="{$language.name|escape:'htmlall':'UTF-8'}" /></a>
                {/foreach}
        </div>
        <div class="mj_decalage">
            {foreach $languages as $language}
                {assign var="id_lang" value=$language.id_lang|escape:'htmlall':'UTF-8'}
                <div id="id_lang_{$sel|escape:'htmlall':'UTF-8'}_{$id_lang|escape:'htmlall':'UTF-8'}" class="id_lang_close_{$sel|escape:'htmlall':'UTF-8'}"
                     style="{if $id_lang!=$sel_lang}display:none;{/if}">
                    {l s='Subject' mod='mailjet'} : <input type="text"
                             name="MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'|default:''}_subject_{$id_lang|escape:'htmlall':'UTF-8'|default:''}"
                             class="mj_trigger_subjects"
                             value="{$triggers.trigger.$sel.subject.$id_lang|escape:'htmlall':'UTF-8'|default:''}" /><br />
                    <div class="mj_seps"></div>
                    <textarea name="MJ_triggers_trigger_{$sel|escape:'htmlall':'UTF-8'|default:''}_mail_{$id_lang|escape:'htmlall':'UTF-8'|default:''}"
                              class="mj_trigger_rtemails"
                              class="rte">{$triggers.trigger.$sel.mail.$id_lang|escape:'htmlall':'UTF-8'|default:''}</textarea>
                </div>
            {/foreach}
        </div>
    </center>
    <br />
    <br />
</div>
<span class="clearspan"></span>
</li>
{/for}


</ul>
</fieldset>
</div>
</form>
<div id="mj_triggers_page" class="center_page">
    <fieldset id="triggers_import_export" {if $MJ_allemails_active && !$triggers.active}style="display:none;"{/if}>
        <legend>{l s='Triggers Import/Export' mod='mailjet'}</legend>
        <form id="MJ_triggers_import_form" name="MJ_triggers_import_form"  action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'|default:''}" method="POST" enctype="multipart/form-data" >
            <input type="hidden" name="MAX_FILE_SIZE" value="512000" />
            <ul>
                <li>
                    <input type="submit" name="MJ_triggers_export_submit" id="MJ_triggers_export_submit" value=" {l s='Export triggers' mod='mailjet'} " />
                    <label id="exportLabel">{l s='Export trigger templates' mod='mailjet'}</label>
                </li>
                <li>
                    <input type="file" name="MJ_triggers_import_file" id="MJ_triggers_import_file" />
                    <input onClick="if ($('#MJ_triggers_import_file').val() == '') { alert('{l s='Add a valid file to import trigger templates from' mod='mailjet'} '); return false; } else { return validateFile(); }" type="submit" name="MJ_triggers_import_submit" id="MJ_triggers_import_submit" value=" {l s='Import triggers' mod='mailjet'} " />
                    <label id="importLabel">{l s='Import trigger templates' mod='mailjet'}</label>
                </li>
            </ul>
        </form>
    </fieldset>
</div>
<!-- /Mailjet : Triggers -->
