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
*}<!-- Mailjet : Campaigns -->
<div id="mj_campaigns_page" class="center_page" style="width:initial;max-width:960px;">
	<form id="create" method="post" action="{$smarty.server.REQUEST_URI|default:''}">
    <input type="hidden" name="MJ_submitCampaign2" value=1 />
	<fieldset>
    	<legend>{l s='New Campaign' mod='mailjet'}</legend>
        <div class="button" style="padding:10px;"><b style="font-size:15px;">{l s='Campaign' mod='mailjet'} : &#171; {$campaign.title|escape|default:''} &#187;</b></div>
        <br />
        <b>When do you want to send this campaign :</b>
        <div class="margin-form" style="padding-left:300px;">
            <div class="button" style="text-align:left;">
			    <input id="sendnow" name="radiosend" type="radio" class="t" checked="checked" value="sendnow" />
			    <label style="display:inline" for="sendnow" class="t required">Send Now</label><br />
            </div>
            <br />
            <div class="button" style="text-align:left;">
				<input id="sendlater" name="radiosend" type="radio" class="t" value="sendlater" />
				<label style="display:inline" for="sendlater" class="t">Send Later</label><br />
        	    <div style="width:100%;height:1px;background:#ccc;margin:8px 0 1px 0;"></div>
        	    <div style="width:100%;height:1px;background:#fff;margin-bottom:5px;"></div>
                <input type="hidden" name="programmed" id="programmed" />
                {if $lang=="en"}
            	<select id="month" name="month" style="text-align:right;">
	                {for $var=1 to 12}<option value="{$var|escape}"{if $var==$month} selected{/if}>{$months.$var|escape}</option>{/for}
                </select> 
            	<select id="day" name="day" style="text-align:center;">
	                {for $var=1 to 31}<option value="{$var|escape}"{if $var==$day} selected{/if}>{$var|escape}</option>{/for}
                </select> 
            	<select id="year" name="year">
	                {for $var=0 to 9}<option value="{$var+$year|escape}">{$var+$year|escape}</option>{/for}
                </select>
                {else}
            	<select id="day" name="day" style="text-align:right;">
	                {for $var=1 to 31}<option value="{$var|escape}"{if $var==$day} selected{/if}>{$var|escape}</option>{/for}
                </select> 
            	<select id="month" name="month" style="text-align:center;">
	                {for $var=1 to 12}<option value="{$var|escape}"{if $var==$month} selected{/if}>{$months.$var|escape}</option>{/for}
                </select> 
            	<select id="year" name="year">
	                {for $var=0 to 9}<option value="{$var+$year|escape}">{$var+$year|escape}</option>{/for}
                </select>
                {/if}
                &nbsp;&nbsp; {l s='to' mod='mailjet'} &nbsp;&nbsp;
            	<select id="hour" name="hour" style="text-align:right;">
	                {for $var=0 to 23}<option value="{$var|escape}"{if $var==$hour} selected{/if}>{$var|escape}</option>{/for}
                </select>{l s='H' mod='mailjet'} 
            	<select id="minute" name="minute">
	                {for $var=0 to 59}<option value="{$var|escape}"{if $var==$min} selected{/if}>{$var|escape}</option>{/for}
                </select>{l s='min' mod='mailjet'} 
            </div>
            <br /><br />
			<div class="box">
		    	<a href="javascript:void(0)" onClick="submitform()" class="button" style="font-weight:bold;padding:5px 20px;" title="submit" class="button btn btn­success">{l s='Send the Campaign' mod='mailjet'}</a>
			</div>
        </div>
	</fieldset>
    </form>
	<script>
	// date : yyyy-mm-ddThh:ii
	function create_date_object(dateTime)
    {
    	dateTimeArray = dateTime.split('T');
        dateArray = dateTimeArray[0].split('­');
        dateTime = dateTimeArray[1].split(':');
        dateObject = new Date(parseInt(dateArray[0]), parseInt(dateArray[1]), parseInt(dateArray[2]), parseInt(dateTime[0]), parseInt(dateTime[1]));
        return dateObject;
	}
    function check_program(sendlater)
	{
    	if (sendlater) {
        	var today = create_date_object("{$today|escape|default:''}")
			// *****
			{literal}
				year = parseInt($('#year').val());
				month = parseInt($('#month').val()); if (month<10) month = '0'+month;
				day = parseInt($('#day').val()); if (day<10) day = '0'+day;
				hour = parseInt($('#hour').val()); if (hour<10) hour = '0'+hour;
				minute = parseInt($('#minute').val()); if (minute<10) minute = '0'+minute;
				programmed = year+'-'+month+'-'+day+'T'+hour+':'+minute;
				document.getElementById('create')['sendlater'] = programmed;
			{/literal}
			// *****
            var send = create_date_object(programmed);
            if (today > send) {
            	alert("{l s='The date you programmed' mod='mailjet'} "+send.toLocaleString()+" {l s='must be after the current date' mod='mailjet'} "+today.toLocaleString());
                return false;
			}
		}
        return true;
	}
	function submitform()
    {
    	var sendlater = document.getElementById('create')['sendlater'].checked;
        if (check_program(sendlater)) { document.getElementById('create').submit();	}
	}
	</script>
<!-- /Mailjet : Campaigns -->