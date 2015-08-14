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
<div class="center_page">
    <div class="logo_mailjet_center"></div>
   
    <ul id="landingMsgUl">
        <li id="landingMsg">    
        {$MJ_templates.setup_landing_message|default:''}
        </li>
        <li id="divYouTubePlayer">    
           <iframe width="400" height="300" src="https://www.youtube.com/embed/192pmEakul0?rel=0&amp;controls=0&amp;showinfo=0&autoplay=0" frameborder="0" allowfullscreen></iframe>
        </li>
    </ul>
     
    <br style="clear:left;" />
    
    <div id="setup_landing_bt">
        <a target="_blank" href="https://{$lang}.mailjet.com/signup?p=prestashop-3.0" id="setup_landing_bt_activate"
           class="default_button_style default_background_orange">
            {l s='Register' mod='mailjet'}
        </a>
        <a href="{$MJ_adminmodules_link|escape|default:''}&{$MJ_REQUEST_PAGE_TYPE|escape|default:''}=CONNECT_STEP_0"
           id="setup_landing_bt_connect" class="default_button_style">
            {l s='Connect' mod='mailjet'}
        </a>
        <a target="_blank" href="http://{$lang}.mailjet.com/pricing_v3 " id="setup_landing_bt_pricing" class="default_button_style">
            {l s='Pricing' mod='mailjet'}
        </a>
        <br clear="left"/>
    </div>
    
</div>