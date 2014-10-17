{*<div class="center_page">
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
</div>*}
        
<div style="clear:both;width:960px;margin:0 auto;">
    <div align="center"><br>
    	Coming soon!
    </div>
</div>