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
*}

<script type="text/javascript">
	var tokenV = "{$mj_token|escape:'javascript'|default:'0'}";
	var ajaxFile =  "{$mj_ajaxFile|escape:'javascript'|default:''}";
	var ajaxSyncFile =  "{$mj_ajaxSyncFile|escape:'javascript'|default:''}";
	var ajaxBundle =  "{$mj_ajaxBundle|escape:'javascript'|default:''}";
	var id_employee = "{$mj_id_employee|escape:'javascript'|default:'0'}";
	var trad = new Array();
	var datePickerJsFormat = "{$mj_datePickerJsFormat|escape:'javascript'|default:'yy-mm-dd'}";
	{foreach from=$mj_trads key=key item=value}
		trad[{$key}] = "{$value|escape:'javascript'|default:'?'}";
	{/foreach}
	var lblMan = "{$mj_lblMan|escape:'javascript'|default:'Man'}";
	var lblWoman = "{$mj_lblWoman|escape:'javascript'|default:'Woman'}";
	var lblUnknown = "{$mj_lblUnknown|escape:'javascript'|default:'Unknow'}";
	var loadingFilter = false;
	var mj_trad_plus = ['{l s='And' mod='mailjet'}', '{l s='Or' mod='mailjet'}', '{l s='Include' mod='mailjet'}', '{l s='Exclude' mod='mailjet'}'];
	var mj_base_select = new Array();
	{foreach from=$mj_base_select item=base_select}
		mj_base_select[{$base_select.id_basecondition|escape}] = "{$mj_trads[$base_select.label]|escape:'javascript'|default:'?'}";
	{/foreach}
</script>
{$mj_datepickerPersonnalized|escape|default:''}
{$MJ_templates.SEGMENTATION|escape|default:''}



<div class="center_page">

    {if !empty($mj_hint_fieldset.0) || !empty($mj_hint_fieldset.1) || !empty($mj_hint_fieldset.2) }
        <fieldset class="width6 hint seg_fieldset">&nbsp;
            {$mj_hint_fieldset.0|escape|default:''}<br /><br />
            {$mj_hint_fieldset.1|escape|default:''}<br /><br />
            {$mj_hint_fieldset.2|escape|default:''}
        </fieldset>
    {/if}

	<div class="clear"> &nbsp; </div>
	<fieldset id="mainFieldset">
		<legend>{l s='Segment Module' mod='mailjet'}</legend>
		<div class="newFilter custo">
			<p class="result" id="listMessage" style="display:none;"></p>
			{if !$mj_filter_list}
				<div class="no_filter_string warn">{l s='You have no segment for now' mod='mailjet'}</div>
			{/if}
			<table class="table space" id="list" style="{if !$mj_filter_list}display:none;{/if}">
				<tr>
					<th>{l s='ID' mod='mailjet'}</th>
					<th>{l s='Name' mod='mailjet'}</th>
					<th>{l s='Description' mod='mailjet'}</th>
					<th>{l s='Mode' mod='mailjet'}</th>
					<th>{l s='Association' mod='mailjet'}</th>
					<th>{l s='Group' mod='mailjet'}</th>
					<th>{l s='Action' mod='mailjet'}</th>
				</tr>
			{foreach from=$mj_filter_list item=filter}
				<tr class="trSelect" id="list{$filter.id_filter|escape|default:'0'}">
					<td>{$filter.id_filter|escape|default:'0'}</td>
					<td><b>{$filter.name|escape|ucfirst}</b></td>
					<td>{$filter.description|escape|default:''}</td>
					<td>{if ($filter.assignment_auto)}{if ($filter.replace_customer)}{$mj_trads[97]|escape|default:'Replace'}{else}{$mj_trads[98]|escape|default:'Add'}{/if}{else}--{/if}</td>
					<td>{if ($filter.assignment_auto)}{$mj_trads[96]|escape|default:'in real time'}{else}--{/if}</td>
					<td>
                                            {if $mj_groups}
                                            {foreach from=$mj_groups item=group}
                                            {if ($filter.id_group == $group.id_group)}
                                                {$group.name|escape|default:'?'}
                                            {/if}
                                            {/foreach}{/if}
                                        </td>
					<td><a href="javascript:deleteFilter({$filter.id_filter|escape|default:'0'});"><img src="../modules/mailjet/img/delete.png" /></a></td>
				</tr>
			{/foreach}
			</table>
			<br />
			<button id="newfilter" class="my_button right"><img src="../modules/mailjet/img/page_excel.png" />{l s='Create a New Segment' mod='mailjet'}</button>
			<br />
			<div class="div_new_filter">
				<h2>{l s='Add a Segment' mod='mailjet'}</h2>
				<div class="nameFilter">
				<form method="post" id="mainForm" action="../modules/mailjet/views/templates/admin/export.php">
					<input type="hidden" id="module_path" value="../modules/mailjet/views/templates/admin/" />
					<table>
						<tr>
							<td class="segmentNameLabel">{l s='Segment name' mod='mailjet'} <sup>*</sup></td>
							<td class="padding2"><input id="name" type="text" value="" name="name" size="43"></td>
						</tr>
						<tr>
							<td class="segmentNameLabel">{l s='Description' mod='mailjet'}</td>
							<td class="padding2"><textarea class="description" name="description" id="description"></textarea></td>
						</tr>
					</table>
					<br />
					<input type="hidden" value="{$mj_id_employee|escape|default:'0'}" name="id_employee" />
					<input type="hidden" value="{$mj_token|escape|default:'0'}" name="token" />
					<input type="hidden" value="getQuery" name="action" id="action" />
					<input type="hidden" value="0" name="page" id="page" />
					<input type="hidden" value="0" name="idfilter" id="idfilter" />
					<input type="hidden" value="0" name="idgroup" id="idgroup" />
					<input type="hidden" value="0" name="mode" id="mode" />
					<dl id="filter-help">
						<dt>{l s='Base' mod='mailjet'}</dt>
						<dd>{l s='for example customers' mod='mailjet'}</dd>
						<dt>{l s='Source' mod='mailjet'}</dt>
						<dd>{l s='for example your customers\' orders or your customers\' profiles' mod='mailjet'}</dd>
						<dt>{l s='Indic' mod='mailjet'}</dt>
						<dd>{l s='select attributes you\'re looking for' mod='mailjet'}</dd>
						<dt>{l s='Data' mod='mailjet'}</dt>
						<dd>{l s='a quantity, a product\'s name, a category\'s name, a brand\'s name, a price, or another value' mod='mailjet'}</dd>
						<dt>{l s='Value1' mod='mailjet'}, {l s='Value2' mod='mailjet'}</dt>
						<dd>{l s='a quantity, a price, or another value, but you can leave this/these field(s) empty' mod='mailjet'}</dd>
						<dt>{l s='+/-, A, Action' mod='mailjet'}</dt>
						<dd>{l s='combine with others attributes to refine your search' mod='mailjet'}</dd>
					</dl>
					<table id="mainTable" class="table">
						<tr id="mainTR">
							<th></th>
							<th>{l s='Rules' mod='mailjet'}</th>
							<th class="filter-table-cond">{l s='A' mod='mailjet'}</th>
							<th>{l s='Action' mod='mailjet'}</th>
							<th>{l s='Base' mod='mailjet'}</th>
							<th>{l s='Source' mod='mailjet'}</th>
							<th>{l s='Indic' mod='mailjet'}</th>
							<th>{l s='Data' mod='mailjet'}</th>
							<th>{l s='Value1' mod='mailjet'}</th>
							<th>{l s='Value2' mod='mailjet'}</th>
						</tr>
					</table>
				</form>
				<br />
				<p class="result" id="syncMessage" style="display: none;">Mailjet list - Update successfully</p>
				<p class="noResult" id="syncMessageError" style="display: none;">Mailjet list - Error occured</p>
				<button id="save" class="my_button right"><img src="../modules/mailjet/img/save.png" /> {l s='Save' mod='mailjet'}</button>
				<button id="view" class="my_button right"><img src="../modules/mailjet/img/table.png" /> {l s='View' mod='mailjet'}</button>
				<button id="export" class="my_button right"><img src="../modules/mailjet/img/page_excel.png" />{l s='Export' mod='mailjet'}</button>
				<button id="sync" class="my_button right"><img src="../modules/mailjet/img/sync.png" />{l s='Create / Update Mailjet list' mod='mailjet'}</button>
				<div class="perc_sync">Synchronisation : <span id="perc_sync_value">0</span>%</div>

				<table id="newLine" style="display:none;">
					<tr id="#####">
						<td id="action#####">
							<a href="javascript:addLine();" class="add"><img src="../modules/mailjet/img/add.png" /></a>
							<a href="javascript:delLine(#####);" class="delete"><img src="../modules/mailjet/img/delete.png" /></a>
						</td>
						<td id="id#####">#####</td>
						<td class="filter-table-cond">
							<select name="rule_a[]" class="cond">
								<option value="AND">{l s='And' mod='mailjet'}</option>
								<option value="OR">{l s='Or' mod='mailjet'}</option>
								<option value="+">{l s='+' mod='mailjet'}</option>
							</select>
						</td>
						<td>
							<select name="rule_action[]" class="cond">
								<option value="IN">{l s='Include' mod='mailjet'}</option>
								<option value="NOT IN">{l s='Exclude' mod='mailjet'}</option>
							</select>
						</td>
						<td>
							<select id="baseSelect#####" name="baseSelect[]" class="baseSelect fixed">
								<option value="-1">--SELECT--</option>
								{foreach from=$mj_base_select item=base}
									<option value="{$base.id_basecondition|escape|default:'-1'}">{$mj_trads[$base.label]|escape|default:''}</option>
								{/foreach}
							</select>
						</td>
						<td id="sourceSelect#####" class="grey"></td>
						<td id="indicSelect#####" class="grey"></td>
						<td><input type="text" class="fixed" id="data#####" name="data[]" value="" /></td>
						<td><input type="text" class="fixed" id="value1#####" name="value1[]" value="" /></td>
						<td><input type="text" class="fixed" id="value2#####" name="value2[]" value="" /></td>
					</tr>
				</table>
			</div>
			<div id="load" style="display:none;"><center><img src="../modules/mailjet/img/load.gif" ></center></div>
			<div id="result"></div>

			<div class="blocAction">
				<h2>{l s='Group association' mod='mailjet'}</h2>
				<fieldset class="custo">
				<p class="result" id="actionMessage" style="display:none;"></p>
				<div class="rowAction">
					<label>{l s='Customer Group' mod='mailjet'} :</label>
					<span class="displayInlineBlock width200 verticalAlignTop">
						<select id="groupUser">
							<option value="-1">{l s='New' mod='mailjet'}</option>
							{if $mj_groups}
								{foreach from=$mj_groups item=group}
									<option value="{$group.id_group|escape|default:'0'}">{$group.name|escape|default:'?'}</option>
								{/foreach}
							{/if}
						</select>
					</span>
					<span class="help">{l s='Select the customer group in which the selected customers will be affected.'
						mod='mailjet'}</span>
				</div>
				<div class="rowAction" id="newgrpdiv">
					<label>{l s='New customer group' mod='mailjet'} : </label>
					<span class="displayInlineBlock width200 verticalAlignTop">
						<input type="text" name="newgrp" id="newgrp">
					</span>
					<span class="help">{l s='Fill in the name of the customer group that will be automatically created'
						mod='mailjet'}.</span>
				</div>
				<div class="rowAction" id="type">
					<label>{l s='Replace or add' mod='mailjet'} : </label>
					<span class="displayInlineBlock width200 verticalAlignTop">
						<input type="radio" id="rep" value="add" name="add" checked="checked" class="displayInlineBlock floatLeft">
						<label for="rep" class="label">{l s='Add' mod='mailjet'}</label>
						<br/>
						<input type="radio" id="add" value="rep" name="add" class="displayInlineBlock floatLeft">
						<label for="add" class="label">{l s='Replace' mod='mailjet'}</label>
					</span>
					<span class="help">{l s='Add: If the client belongs to the selected group without losing its other groups'
						mod='mailjet'}.<br/>
						{l s='Replace: If the client belongs to the selected group, losing all other groups' mod='mailjet'}.
					</span>
				</div>
				<div class="rowAction" id="auto-assignment">
					<label>{l s='Associate in real time' mod='mailjet'} :</label>
					<span class="displayInlineBlock width200 verticalAlignTop">
						<select id="assign-auto" name="assign-auto">
							<option value="0">{l s='No' mod='mailjet'}</option>
							<option value="1">{l s='Yes' mod='mailjet'}</option>
						</select>
					</span>
					<span class="help">{l s='Assign customers to this group automatically. It will create a new filter
						which associate customers in real time in your shop' mod='mailjet'}.</span>
				</div>
				<div class="rowAction" id="attrib">
					<label>{l s='Assign group selection' mod='mailjet'}</label>
					<span class="displayInlineBlock width200 verticalAlignTop">
						<button class="my_button" id="groupAttrib" ><img src="../modules/mailjet/img/table.png" />
							{l s='Assign now' mod='mailjet'}</button>
						<img src="{$mj__PS_BASE_URI__|default:'/'}modules/mailjet/img/load.gif" id="wait" style="display:none;" />
					</span>
					<span class="help">{l s='Customers will be assigned to the group after the click on the button'
						mod='mailjet'}.</span>
					<br><span id="resultText"></span>
				</div>
			</div>
		</div>
	</fieldset>

</div>