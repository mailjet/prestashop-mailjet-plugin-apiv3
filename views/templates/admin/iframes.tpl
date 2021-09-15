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
{if $MJ_page_name == "PRICING"}
    {if $MJ_TOKEN_USER}
        {if $MJ_user_plan}
            <div class="center_page">
                <p class="error">{l s='warning_pricing' mod='mailjet'}</p>
            </div>
        {/if}
    {/if}
{/if}
{if $MJ_page_name == "CONTACTS"}
    {$MJ_contact_list_form}
{/if}
<iframe border="0" id="mj_iframe" width="100%" height="1200px" src="{$MJ_iframes.$MJ_page_name|escape:'htmlall':'UTF-8'}{if !empty($MJ_TOKEN_USER)}{/if}">
</iframe>