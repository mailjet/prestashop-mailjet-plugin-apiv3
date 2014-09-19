<div class="center_page">
	<p class="hint" style="display:block;position:relative;">
    	&nbsp; {l s='Mailjet sending all emails of your website, optimizes and automatically manages the statistical returns of errors.' mod='mailjet'}
	</p>
    <form action="{$MJ_adminmodules_link}" id="home_form" method="POST">
	<p class="warn" style="display:block;position:relative;margin-top:5px;">
		&nbsp; {l s='Activate the sending of all email by Mailjet ?' mod='mailjet'} &nbsp; &nbsp;
			&nbsp; <input type="radio" name="MJ_allemails_active" id="MJ_allemails_active_1" value=1 {if $MJ_allemails_active}checked{/if} /> <label class="t" for="MJ_allemails_active_1">{l s='YES' mod='mailjet'}</label>
			&nbsp; <input type="radio" name="MJ_allemails_active" id="MJ_allemails_active_0" value=0 {if !$MJ_allemails_active}checked{/if} /> <label class="t" for="MJ_allemails_active_0">{l s='NO' mod='mailjet'}</label>
            &nbsp; &nbsp; <input type="submit" value=" {l s='Modify' mod='mailjet'} " name="MJ_set_allemails" class="button" />
	</p>
    </form>
    {if $AllMailsActiveMessage}
    	{if $AllMailsActiveMessage == 1}
    		{l s='Great!  Now all of your email traffic will send through Mailjet and benefit from awesome deliverability.  Welcome Aboard.' mod='mailjet'}
    	{else}
    		{l s="Were sad. This means your transactional and triggered emails will not benefit from Mailjet's advanced deliverability but don't worry, you can still use the Campaign Tool to send Newsletters." mod='mailjet'}
    	{/if}
    {/if}
        
	<h3>{l s='What you can do with the module' mod='mailjet'} :</h3>
	<div class="home_list_tab">
    	<ul>
        	<li>
            	<a href="{$MJ_adminmodules_link}&{$MJ_REQUEST_PAGE_TYPE}=SEGMENTATION" class="btn_tab_home">{l s='Segmentation' mod='mailjet'}</a>
				{l s='With the help of pre-defined eCommerce filters and criteria, create different target client and prospect segments that you can then send targeted messages to.' mod='mailjet'}
            </li>
        	<li>
            	<a href="{$MJ_adminmodules_link}&{$MJ_REQUEST_PAGE_TYPE}=CAMPAIGN" class="btn_tab_home">{l s='Campaigns' mod='mailjet'}</a>
				{l s='Create and send a marketing newsletter blast to your client base via a drag-and-drop HTML designer and a gallery of pre-defined templates all within a few clicks.' mod='mailjet'}
            </li>
        	<li>
            	<a href="{$MJ_adminmodules_link}&{$MJ_REQUEST_PAGE_TYPE}=TRIGGERS" class="btn_tab_home">{l s='Transactional&nbsp;emails' mod='mailjet'}</a>
				{l s='... Description of Transactionnal emails on home page of the module ...' mod='mailjet'}
            </li>
        	<li>
            	<a href="{$MJ_adminmodules_link}&{$MJ_REQUEST_PAGE_TYPE}=CONTACTS" class="btn_tab_home">{l s='Contact Lists' mod='mailjet'}</a>
				{l s='This is where you synchronise and sync your master contact lists which can then be segmented and/or targeted with specific messages. You can also look up which contacts received previous emails and clicked where and when. This is also the space to manually add any new contacts.' mod='mailjet'}
            </li>
        	<li>
            	<a href="{$MJ_adminmodules_link}&{$MJ_REQUEST_PAGE_TYPE}=STATS" class="btn_tab_home">{l s='Stats' mod='mailjet'}</a>
                {l s='Analyse the flow, impact and client interaction of the different email streams that you send as a merchant. Access all the different statistics that you could ever dream of for your targeted messages, your marketing campaigns and transactional email.' mod='mailjet'}
            </li>
        	<li>
            	<a href="{$MJ_adminmodules_link}&{$MJ_REQUEST_PAGE_TYPE}=ROI" class="btn_tab_home">{l s='R.O.I' mod='mailjet'}</a>
				{l s='... Description of R.O.I on home page of the module ...' mod='mailjet'}
            </li>
        	<li>
            	<a href="{$MJ_adminmodules_link}&{$MJ_REQUEST_PAGE_TYPE}=EVENTS" class="btn_tab_home">{l s='Contact Management' mod='mailjet'}</a>
				{l s='Keep your contact lists up to date by updating and removing the different bounces and blocked email addresses from your clients and prospects, completely up to date. Need to correct an email address ? Update it here and that\'s one more client contact point saved for the future !' mod='mailjet'}
            </li>
        	<li>
            	<a href="{$MJ_adminmodules_link}&{$MJ_REQUEST_PAGE_TYPE}=ACCOUNT" class="btn_tab_home">{l s='My account' mod='mailjet'}</a>
                {l s='This menu allows you to modify your settings and update them in order to optimise your deliverability. You will also find all your profile and billing details here.' mod='mailjet'}
            </li>
        	<li>
            	<a href="http://www.preprod.mailjet.com/pricing" target="_blank" class="btn_tab_home">{l s='Upgrade' mod='mailjet'}</a>
                {l s='Click here to change/upgrade your current plan.' mod='mailjet'}
            </li>
            
        </ul>
    </div>
</div>