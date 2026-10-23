{*
* 2007-2019 PrestaShop
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
* @copyright 2007-2019 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}
<div class="card panel" id="mailjet-tracking-consent">
    <h3 class="card-header panel-heading">
        <i class="icon-shield material-icons">security</i>
        {l s='Mailjet - Email tracking consent' mod='mailjet'}
    </h3>
    <div class="card-body">
        {if !$MJ_tracking_consent_enabled}
            <p class="text-muted">{l s='Tracking consent is disabled in the Mailjet module settings.' mod='mailjet'}</p>
        {/if}
        <p>
            <strong>{l s='Open/click tracking consent:' mod='mailjet'}</strong>
            {if $MJ_tracking_consent_status && $MJ_tracking_consent_status.consent}
                <span class="badge badge-success label label-success">{l s='Given' mod='mailjet'}</span>
            {elseif $MJ_tracking_consent_status}
                <span class="badge badge-danger label label-danger">{l s='Refused / withdrawn' mod='mailjet'}</span>
            {else}
                <span class="badge badge-secondary label label-default">{l s='No answer (treated as no consent)' mod='mailjet'}</span>
            {/if}
        </p>
        {if $MJ_tracking_consent_status}
            <p>
                {l s='Last change:' mod='mailjet'} {$MJ_tracking_consent_status.date_upd|escape:'htmlall':'UTF-8'}
                ({$MJ_tracking_consent_status.source|escape:'htmlall':'UTF-8'})
                {if !$MJ_tracking_consent_status.synced}
                    - <em>{l s='waiting for synchronization with Mailjet' mod='mailjet'}</em>
                {/if}
            </p>
        {/if}
        {if $MJ_tracking_consent_history}
            <table class="table">
                <thead>
                    <tr>
                        <th>{l s='Date' mod='mailjet'}</th>
                        <th>{l s='Consent' mod='mailjet'}</th>
                        <th>{l s='Source' mod='mailjet'}</th>
                        <th>{l s='Email' mod='mailjet'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$MJ_tracking_consent_history item=entry}
                        <tr>
                            <td>{$entry.date_add|escape:'htmlall':'UTF-8'}</td>
                            <td>{if $entry.consent}{l s='Given' mod='mailjet'}{else}{l s='Refused / withdrawn' mod='mailjet'}{/if}</td>
                            <td>{$entry.source|escape:'htmlall':'UTF-8'}</td>
                            <td>{$entry.email|escape:'htmlall':'UTF-8'}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        {/if}
    </div>
</div>
