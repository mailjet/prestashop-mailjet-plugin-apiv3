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
<div id="login_content">
	<div id="login_form">
		<fieldset>
			<legend>{l s='You already have an mailjet account' mod='mailjet'}</legend>
			<form id="MJ_auth_form" action="{$smarty.server.REQUEST_URI|escape|default:''}" method="POST">
				<ul>
					<li>
						<label for="MJ_email_address">{l s='Email address' mod='mailjet'}</label>
						<input id="MJ_email_address" type="text" name="MJ_email_address" value="{$MJ_email_address|escape:all}" />
					</li>
					<li>
						<label for="MJ_passwd">
						{l s='Password' mod='mailjet'}
						</label>
						<input id="MJ_passwd" class="MJ_passwd" type="password" name="MJ_passwd" value="{$MJ_passwd|escape:all}" />
					</li>
				</ul>
				<input name="MJ_set_login" type="hidden" />
			</form>
			<div id="login_bt_activate" class="default_button_style default_background_orange">
				<a id="MJ_auth_link" href="#">{l s='Waiting template name' mod='mailjet'}</a>
			</div>
			<br clear="left"/>
		</fieldset>
	</div>
	<div id="login_warning_detail">
		{l s='Waiting template name' mod='mailjet'}
	</div>
	<br clear="left"/>
</div>
<div id="login_error" class="default_button_style">
	{l s='Waiting template name' mod='mailjet'}
</div>
<div id="login_ask_question">
	<div id="login_bt_question" class="default_button_style default_background_orange">
		{l s='Waiting template name' mod='mailjet'}
	</div>
</div>
<br clear="left"/>