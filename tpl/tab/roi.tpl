{*<div class="center_page">
	<table id="vsTable">
    	<thead>
        	<tr>
                <th class="title">{$trad_title}</th>
                <th class="title">{$trad_sentemails}</th>
                <th class="title">{$trad_roiamount}</th>
                <th class="title">{$trad_roipercent}</th>
            </tr>
        </thead>
        <tbody>
        	{foreach from=$campaigns item=c}
        	<tr>
                <td>{$c.title}</td>
                <td>{$c.delivered}</td>
                <td>{convertPrice price=$c.total_roi}</td>
                <td>{$c.perc_roi}%</td>
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