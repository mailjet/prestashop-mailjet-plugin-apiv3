
<div id="login_content">
	<div id="login_form">
		<fieldset>
			<legend>{l s='You already have an mailjet account' mod='mailjet'}</legend>
			<form id="MJ_auth_form" action="{$smarty.server.REQUEST_URI}" method="POST">
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