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
*}<!-- Mailjet : Triggers -->

{* $tinymce|default:'' *}
{if $tinymce_new}
	<script type="text/javascript">
	var iso = '{$tinymce_iso|escape|default:'en'}';
	var pathCSS = '{$tinymce_pathCSS|escape|default:''}';
	var ad = '{$tinymce_ad|escape|default:''}';
	</script>
	<script type="text/javascript" src="{$tinymce_pathBase|escape|default:'/'}js/tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript" src="{$tinymce_pathBase|escape|default:'/'}js/tinymce.inc.js"></script>
	<script language="javascript" type="text/javascript">
	var id_language = Number({$tinymce_id_language|escape|default:'en'});
	</script>
{else}
	<script type="text/javascript" src="{$tinymce_pathBase|escape|default:'/'}js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
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
		content_css : "{$tinymce_pathBase|escape}themes/{$tinymce_theme|escape|default:'default-bootstrap'}/css/global.css",
		document_base_url : "{$tinymce_pathBase|escape|default:'/'}",
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
		language : "{$tinymce_iso|escape|default:'en'}"
	});
	id_language = Number({$tinymce_id_language|escape|default:'en'});
	</script>
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
        if($.inArray(ext, ['txt']) == -1) {
            alert('{l s='Add a valid file to import trigger templates from' mod='mailjet'}');
            return false;
        }
        return true;
    }





</script>
<form action="{$smarty.server.REQUEST_URI|escape|default:''}" method="POST">
<div id="mj_triggers_page" class="center_page">
	<div class="warn">&nbsp; {l s='To activate the triggers you need to set up this cron job' mod='mailjet'} :<br />
        <input type="text" readonly value="{$cron|escape}" size=135" />
    </div>
	<fieldset class="hint">
		<legend>{l s='Do you wish to activate eCommerce transactional email ?' mod='mailjet'}</legend>
        <div>
			<input type="radio" name="MJ_triggers_active" id="MJ_triggers_active_1" value=1 onClick="$('#triggers_options, #triggers_import_export').slideDown()" {if $MJ_allemails_active && $triggers.active}checked{/if} {if !$MJ_allemails_active}disabled{/if} /> <a href="javascript:;" onClick="$('#MJ_triggers_active_1').click();">{l s='YES' mod='mailjet'}</a> &nbsp;
			<input type="radio" name="MJ_triggers_active" id="MJ_triggers_active_0" value=0 onClick="$('#triggers_options, #triggers_import_export').slideUp()" {if !$MJ_allemails_active || !$triggers.active}checked{/if} {if !$MJ_allemails_active}disabled{/if} /> <a href="javascript:;" onClick="$('#MJ_triggers_active_0').click();">{l s='NO' mod='mailjet'}</a><br />
		</div>
        <input type="submit" name="MJ_set_triggers" value="{l s='Save Changes' mod='mailjet'}" onClick="this.value=' {l s='Wait please...' mod='mailjet'} ';" class="savebutton button"  {if !$MJ_allemails_active}disabled{/if} style="{if !$MJ_allemails_active}display:none;{/if}" />
	<br />
      
        {if !$MJ_allemails_active}
            <br />
            <p class="warn">
                {l s="Because you have selected to not send your transactional email via Mailjet on the plug-in Homepage, this means the triggered email module can't be activated either. To activate triggered emails, please go to the plug-in homepage and select 'Yes' to have Mailjet send all of your email. This will then allow you to select 'Yes' to activate the triggered emails module." mod='mailjet'}
            </p>
        {/if}

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
						<input {if !$MJ_allemails_active}disabled{/if} type="radio" name="MJ_triggers_trigger_{$sel|escape}_active" id="MJ_triggers_trigger_{$sel|escape}_active_1" value=1 {if $triggers.trigger.$sel.active}checked{/if} onClick="$('#MJ_triggers_trigger_{$sel|escape}_button').show();" /> <a href="javascript:;" onClick="{if !$MJ_allemails_active}return false;{/if}  $('#MJ_triggers_trigger_{$sel|escape}_active_1').click();">{l s='Yes' mod='mailjet'}</a> &nbsp;
						<input {if !$MJ_allemails_active}disabled{/if} type="radio" name="MJ_triggers_trigger_{$sel|escape}_active" id="MJ_triggers_trigger_{$sel|escape}_active_0" value=0 {if !$MJ_allemails_active || !$triggers.trigger.$sel.active}checked{/if} onClick="$('#MJ_triggers_trigger_{$sel|escape}_button').hide();$('#MJ_triggers_trigger_{$sel|escape}_parameters').hide();" /> <a href="javascript:;" onClick="{if !$MJ_allemails_active}return false;{/if} $('#MJ_triggers_trigger_{$sel|escape}_active_0').click();">{l s='No' mod='mailjet'}</a> &nbsp;
						<a href="javascript:;" onClick="{if !$MJ_allemails_active}return false;{/if} $('#MJ_triggers_trigger_{$sel|escape}_parameters').slideToggle();" id="MJ_triggers_trigger_{$sel|escape}_button" class="button MJ_triggers_trigger_buttons" style="{if !$MJ_allemails_active || !$triggers.trigger.$sel.active}display:none;{/if}" />{l s='parameters' mod='mailjet'}</a> &nbsp;
                        <br />
					</div>
					<span class="clearspan"></span>
					<div id="MJ_triggers_trigger_{$sel|escape|default:''}_parameters" class="warn mj_triggers_parameters">
						<b>{l s='Parameters' mod='mailjet'}</b><br />
                        {if $sel!=5 && $sel!=6}
	                        <label>{l s='Trigger sending after how long ?' mod='mailjet'}</label>
							<input type="text" name="MJ_triggers_trigger_{$sel|escape|default:''}_period" size=5 value="{$triggers.trigger.$sel.period|escape|default:''}" />
							<select name="MJ_triggers_trigger_{$sel|escape|default:''}_periodType">
								<option value=1 {if $triggers.trigger.$sel.periodType==1}selected{/if}>{l s='Month' mod='mailjet'}</option>
								<option value=2 {if $triggers.trigger.$sel.periodType==2}selected{/if}>{l s='Days' mod='mailjet'}</option>
								<option value=3 {if $triggers.trigger.$sel.periodType==3}selected{/if}>{l s='Hours' mod='mailjet'}</option>
								<option value=4 {if $triggers.trigger.$sel.periodType==4}selected{/if}>{l s='Minutes' mod='mailjet'}</option>
							</select>
                            <br />
						{else}
	                        <label>{l s='Reduction amount' mod='mailjet'} :</label>
							<input type="text" name="MJ_triggers_trigger_{$sel|escape|default:''}_discount" size=5 value="{$triggers.trigger.$sel.discount|escape|default:''}" />
							<select name="MJ_triggers_trigger_{$sel|escape|default:''}_discountType">
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
	                        	{assign var="id_lang" value=$language.id_lang|escape}
                            	<a href="javascript:;"
                                   onClick="$('.id_lang_close_{$sel|escape}').hide();$('#id_lang_{$sel|escape}_{$id_lang|escape}').show();$('.flags_{$sel|escape}').removeClass('selFlag');$(this).addClass('selFlag');"
                                   class="{if $id_lang==$sel_lang}selFlag{/if} flags_{$sel|escape}"
                                   title="{$language.name|escape}"><img src="../img/l/{$id_lang|escape}.jpg" alt="{$language.name|escape}" /></a>
                            {/foreach}
                            </div>
                            <div class="mj_decalage">
	                        {foreach $languages as $language}
	                        	{assign var="id_lang" value=$language.id_lang|escape}
								<div id="id_lang_{$sel|escape}_{$id_lang|escape}" class="id_lang_close_{$sel|escape}"
                                     style="{if $id_lang!=$sel_lang}display:none;{/if}">
   	 	                    		{l s='Subject' mod='mailjet'} : <input type="text"
                                        name="MJ_triggers_trigger_{$sel|escape|default:''}_subject_{$id_lang|escape|default:''}"
                                        class="mj_trigger_subjects"
                                        value="{$triggers.trigger.$sel.subject.$id_lang|escape|default:''}" /><br />
                                    <div class="mj_seps"></div>
									<textarea name="MJ_triggers_trigger_{$sel|escape|default:''}_mail_{$id_lang|escape|default:''}"
                                        class="mj_trigger_rtemails"
                                        class="rte">{$triggers.trigger.$sel.mail.$id_lang|escape|default:''}</textarea>
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
        <form id="MJ_triggers_import_form" name="MJ_triggers_import_form"  action="{$smarty.server.REQUEST_URI|escape|default:''}" method="POST" enctype="multipart/form-data" >
            <input type="hidden" name="MAX_FILE_SIZE" value="512000" />
            <ul>
                <li>
                    <input type="submit" name="MJ_triggers_export_submit" id="MJ_triggers_export_submit" value=" {l s='Export triggers' mod='mailjet'} " />
                    <label id="exportLabel">{l s='Export trigger templates' mod='mailjet'}</label>
                </li>
                <li>
                    <input type="file" name="MJ_triggers_import_file" id="MJ_triggers_import_file" />
                    <input onClick="if ($('#MJ_triggers_import_file').val() == '') { alert('{l s='Add a valid file to import trigger templates from' mod='mailjet'} '); return false;} else { return validateFile();}" type="submit" name="MJ_triggers_import_submit" id="MJ_triggers_import_submit" value=" {l s='Import triggers' mod='mailjet'} " />
                    <label id="importLabel">{l s='Import trigger templates' mod='mailjet'}</label>
                </li>
            </ul>
        </form>
    </fieldset>
</div>
<!-- /Mailjet : Triggers -->
