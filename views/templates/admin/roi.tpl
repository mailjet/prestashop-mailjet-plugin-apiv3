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
*}<div class="center_page">
	<table id="vsTable">
    	<thead>
        	<tr>
                <th class="title">{$trad_title|default:''}</th>
                <th class="title">{$trad_sentemails|default:''}</th>
                <th class="title">{$trad_roiamount|default:''}</th>
                <th class="title">{$trad_roipercent|default:''}</th>
            </tr>
        </thead>
        <tbody>
        	{foreach from=$campaigns item=c}
        	<tr>
                <td>{$c.title|default:''}</td>
                <td>{$c.delivered|default:''}</td>
                <td>{convertPrice price=$c.total_roi|default:''}</td>
                <td>{$c.perc_roi|default:''}%</td>
            </tr>
            {/foreach}
        </tbody>
    </table>
</div>
        
<!-- div style="clear:both;width:960px;margin:0 auto;">
    <div align="center"><br>
    	Coming soon!
    </div>
</div -->