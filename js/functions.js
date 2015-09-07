/**
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
*/
function addLine()
{
	$(".filter-table-cond").show();
	
	if ((rules - 1) > 0)
	{
		$("#action" + (rules -1)).html('<a href="javascript:addLine();" class="add"><img src="../modules/mailjet/img/add.png" /></a> <a class="delete" href="javascript:delLine(' + (rules - 1) + ');"><img src="../modules/mailjet/img/delete.png" /></a>');
	}
	newline = $("#newLine").html();
	if(typeof newline !== 'undefined'){
		newline = newline.replace(/#####/g,rules);
		newline = newline.replace('<tbody>','');
		$("#mainTable").append(newline);
	}
	//$('tr#' + rules).slideDown(500);
	$("table#mainTable").find('td').css('text-align', 'center');
	rules++;
	nbrules++;
	cleanAdd();
	
	newidrule = rules - 1;
	$('input#data' + newidrule + ', input#pdata' + newidrule + ', input#value1' + newidrule + ', input#value2' + newidrule).hide();
	$('input#data' + newidrule + ', input#value1' + newidrule + ', input#value2' + newidrule).parents('td').addClass('grey fixed');
	$('#baseSelect' + newidrule).attr('value', 1);
	updateSource($('#baseSelect' + newidrule).val(), newidrule);
}

function cleanAdd()
{
	$("#1 select.cond:first").hide();
	$("#action1 a.delete").remove();
	var items = ($("#mainTable a.add").length - 1);
	$("#mainTable a.add").each(function(index) {
		if (items != index)
			$(this).hide();
		else
			$(this).show();
	});
}

function updateSource(idbase, idrule)
{
	unbindAll('data' + idrule);
	unbindAll('value1' + idrule);
	unbindAll('value2' + idrule);
	$("#indicSelect" + idrule).html('');
	$("#indicSelect" + idrule).attr('class', 'grey');
	if(typeof ajaxFile !== 'undefined'){
		$.post(ajaxFile, {'action' : 'getSource' ,'token' : tokenV, 'baseID' : idbase, 'ID' : idrule,'id_employee': id_employee},
			function(data) {
				$("#sourceSelect" + idrule).html(data);
				$("#sourceSelect" + idrule).attr('class', '');
		});
	}

	$('input#data' + idrule + ', input#pdata' + idrule + ', input#value1' + idrule + ', input#value2' + idrule).hide();
	$('input#data' + idrule + ', input#value1' + idrule + ', input#value2' + idrule).parents('td').addClass('grey fixed');
}

function updateBinder(idrule, current, id, load)
{
	//$('input#value1' + current + ', input#value2' + current).val('');
	
	load = false;
	if (typeof(load) != 'undefined')
		load = true;
	$.ajax({
 		url: ajaxFile,
 		type : "POST",
 		async: false,
 		data : {'action' : 'getBinder' ,'token' : tokenV, 'ID' : idrule,'id_employee': id_employee},
  		success: function(data) {   			
  			if (loadingFilter !== true)
  			{
  				$('input#data' + current + ', input#pdata' + current + ', select#idseldata' + current).val('').hide();
  	  			$('input#value1' + current + ', input#value2' + current).val('').hide();
  			}  			
  			
  			switch ($('select#fieldSelect' + current).val())
			{
				case '1':
				case '2':
				case '10':
					$('input#value1' + current + ', input#value2' + current).show().parents('td').removeClass('grey fixed');	
				case '12':				
				case '17':
				case '18':		
				case '28':
				case '29':
				case '36':
					$('input#data' + current).parents('td').addClass('grey fixed');
					break;
				case '3':
				case '4':
				case '5':
				case '6':
				case '7':
				case '8':
				case '9':
				case '11':
				case '13':
				case '14':
				case '15':
				case '16':
				case '19':
				case '20':
				case '21':
				case '22':
				case '23':
				case '24':
				case '25':
				case '26':
				case '27':
				case '30':
				case '31':
				case '32':
				case '33':
				case '34':
				case '35':
					$('input#data' + current).parents('td').removeClass('grey fixed');
					break;
				default:
					$('input#data' + current).parents('td').addClass('grey fixed');
					$('input#value1' + current + ', input#value2' + current).parents('td').addClass('grey fixed');	
			}
  			
  			unbindAll('data' + current, load);
			unbindAll('value1' + current, load);
			unbindAll('value2' + current, load);
  			
			if (data)
			{
				info = jQuery.parseJSON(data);
				
				if (info.return[0] != 'null')
					bind(info.return[0], 'data' + current, id);
				if (info.return[1] != 'null')
					bind(info.return[1], 'value1' + current, id);
				if (info.return[2] != 'null')
					bind(info.return[2], 'value2' + current, id);
				
				dataOn = new Array('33', '35');	
				if ($.inArray($('select#fieldSelect' + current).val(), dataOn) !== -1)
					$('input#data' + current).show();
				else
					$('input#data' + current).hide();
				
				valuesOn = new Array('8', '9', '12', '17', '18', '19', '28', '29', '35', '36');
				if ($.inArray($('select#fieldSelect' + current).val(), valuesOn) !== -1)
				{
					$('input#value1' + current + ', input#value2' + current).show().parents('td').removeClass('grey fixed');
					
					bindZero = new Array('19');
					if ($.inArray($('select#fieldSelect' + current).val(), bindZero) !== -1)
						$('select#idseldata' + current).bind('change', function(){
							if ($(this).val() == 0)
								$('input#value1' + current + ', input#value2' + current).val('').hide().parents('td').addClass('grey fixed');
							else
								$('input#value1' + current + ', input#value2' + current).show().parents('td').removeClass('grey fixed');
						});
					else
						$('select#idseldata' + current).unbind('change');
				}
				else
					$('input#value1' + current + ', input#value2' + current).hide().parents('td').addClass('grey fixed');
			}
			else
			{
				$('input#data' + current + ', input#pdata' + current + ', select#idseldata' + current).val('').hide();
				dataOn = new Array('25', '26');	
				if ($.inArray($('select#fieldSelect' + current).val(), dataOn) !== -1)
					$('input#data' + current).show();
			}
  		}
  	});
}

function bind(type, field, id)
{
	switch (type)
	{
		case 'product' :
			$('#' + field).product(id);
			break;
		case 'category' :
			$('#' + field).category(id);
			break;
		case 'brand' :
			$('#' + field).brand(id);
			break;
		case 'gender' :
			$('#' + field).inputToSelect({1 : lblMan, 2 : lblWoman, 9 : lblUnknown}, id);
		case 'date' :
			$('#' + field).datepicker({
			prevText:"",
			nextText:"",
			dateFormat:datePickerJsFormat});
			break;
		case 'country' :
			$('#' + field).inputToSelect(getCountry(), id);
			break;
		case 'order':
		case 'ca':
		case 'payment-method':
		case 'yn':
		case 'origin':
		case 'city':
			$('#' + field).inputToSelect(info['values'], id);
			break;
		default:
	}
}

function getCountry()
{
   	retour = "",
	$.ajax({
 		url: ajaxFile,
 		type : "POST",
 		async: false,
 		data : "action=getCountry&token=" + tokenV+"&id_employee=" + id_employee + "&token=" + tokenV,
  		success: function(data) 
  		{
  			 retour = jQuery.parseJSON(data);
  		}
  	});
	return retour;
}

function unbindAll(field, load)
{
	load = false;
	if (typeof(load) != 'undefined')
		load = true;
		
	$('#' + field).product('',false);
	$('#' + field).category('',false);
	$('#' + field).brand('',false);
	$('#' + field).datepicker('destroy');
	$('#' + field).inputToSelect('','',false);
	if(!load)
		$('#' + field).val(' ');
}

function updateIndic(idsource, idrule)
{
	unbindAll('data' + idrule);
	unbindAll('value1' + idrule);
	unbindAll('value2' + idrule);
	$.post(ajaxFile, {'action' : 'getIndic' ,'token' : tokenV, 'sourceID' : idsource, 'ID' : idrule, 'id_employee': id_employee},
	   function(data) {
	   	$("#indicSelect" + idrule).html(data);
	   	$("#indicSelect" + idrule).attr('class', '');
	   });
	
	$('input#data' + idrule + ', input#pdata' + idrule + ', input#value1' + idrule + ', input#value2' + idrule).hide();
	$('input#data' + idrule + ', input#value1' + idrule + ', input#value2' + idrule).parents('td').addClass('grey fixed');
}

function delLine(ruleid)
{
	if ((nbrules - 1) > 0)
	{
		$("#" + ruleid).remove();
		nbrules--;
		rules--;
		clearRules(ruleid);
		cleanAdd();
	}
	
	if ($("table#mainTable").children('tr').length == 1)
	{
		$(".filter-table-cond").hide();
	}
}

function saveFilter()
{
	if (name)
		$("#name").val(name);
	if ($("#name").val() == '')
	{
		$("#name").attr('class', 'alertbox');
		return false;
	}
	
	$.post(ajaxFile, $("#mainForm").serialize() + "&token=" + tokenV + 
			"&assign-auto="+$('#assign-auto').val()
			+ "&idgroup=" + $('#groupUser').val()
			+ "&replace-customer=" + $('input[name=add]:checked').val()
			,
	   function(data) {
	   if ($("#idfilter").val() != 0)
	   {
	    	$("#list" + $("#idfilter").val()).remove();
	   }
	   data = jQuery.parseJSON(data);
	    $("#list").show();
		$(".no_filter_string").hide();
	   $("#list").append('<tr id="list'+data.id+'" class="trSelect"><td>'+data.id+'</td><td>'+data.name+'</td><td>'+data.description+'</td><td>'+data.replace_customer+'</td><td>'+data.auto_assign+'</td><td>'+data.group_name+'</td><td><a href="javascript:deleteFilter('+data.id+');"><img src="../modules/mailjet/img/delete.png" /></a></td></tr>');
	   $("#idfilter").val(data.id);
	   $(".div_new_filter").hide();
	   $("#action").val('getQuery');
	   displayListMessage(trad[23], "result");
	   $('#newfilter').show();
	});
}

function displayListMessage(msg, classname)
{
	$("#listMessage").fadeOut();
	$("#listMessage").attr('class', classname);
	$("#listMessage").html(msg);
	$("#listMessage").fadeIn();
	setTimeout("$(\"#listMessage\").fadeOut();",5000);
}

function displayActionMessage(msg, classname)
{
	$("#actionMessage").fadeOut();
	$("#actionMessage").attr('class', classname);
	$("#actionMessage").html(msg);
	$("#actionMessage").fadeIn();
	setTimeout("$(\"#listMessage\").fadeOut();",5000);
}

function loadFilter(idfilter)
{
	$.ajax({
 		url: ajaxFile,
 		type : "POST",
 		async: false,
 		data : {'idfilter' : idfilter, 'action' : 'loadFilter', 'token' : tokenV, 'id_employee': id_employee},
  		success: function(data) {
  			loadingFilter = true;

			html = '<tr id="mainTR">';
				html += '<th></th>';
				html += '<th>'+trad[36]+'</th>';
				html += '<th class="filter-table-cond">'+trad[79]+'</th>';
				html += '<th>'+trad[80]+'</th>';
				html += '<th>'+trad[38]+'</th>';
				html += '<th>'+trad[39]+'</th>';
				html += '<th>'+trad[40]+'</th>';
				html += '<th>'+trad[41]+'</th>';
				html += '<th>'+trad[42]+'</th>';
				html += '<th>'+trad[35]+'</th>';
			html += '</tr>';
			data= JSON.parse(data);
			for (i = 1; i <= data.length ; i++)
			{
				line = data[i-1];
				html += '<tr id="'+i+'">';
					html += '<td id="action'+i+'">';
						html += '<a class="add" href="javascript:addLine();"><img src="../modules/mailjet/img/add.png" /></a>';
						html += '<a class="delete" href="javascript:delLine('+i+');"><img src="../modules/mailjet/img/delete.png" /></a>';
					html += '</td>';
					html += '<td id="id'+i+'">'+i+'</td>';
					html += '<td><select name="rule_a[]" class="cond">';
						html += '<option value="AND" '+((line.rule_a == 'AND') ? ' selected="selected"' : '')+'>'+mj_trad_plus[0]+'</option>';
						html += '<option value="OR" '+((line.rule_a == 'OR') ? ' selected="selected"' : '')+'>'+mj_trad_plus[1]+'</option>';
						html += '<option value="+" '+((line.rule_a == '+') ? ' selected="selected"' : '')+'>+</option>';
					html += '</select></td>';
					html += '<td><select name="rule_action[]">';
						html += '<option value="IN" '+((line.rule_action == 'IN') ? ' selected="selected"' : '')+'>'+mj_trad_plus[2]+'</option>';
						html += '<option value="NOT IN" '+((line.rule_action == 'NOT IN') ? ' selected="selected"' : '')+'>'+mj_trad_plus[3]+'</option>';
					html += '</select></td>';
					html += '<td><select id="baseSelect'+i+'" name="baseSelect[]" class="baseSelect fixed">';
						html += '<option value="-1">--SELECT--</option>';
						for (var id_basecondition in mj_base_select)
						{
							html += '<option value="'+id_basecondition+'"'+(id_basecondition == line.id_basecondition ? ' selected=selected' : '')+' >'+mj_base_select[id_basecondition]+'</option>';
						}
					html += '</select></td>';
					html += '<td id="sourceSelect'+i+'">'+line.getSourceSelect+'</td>';
					html += '<td id="indicSelect'+i+'">'+line.getIndicSelect+'</td>';
/*					html += '<td id="sourceSelect'.$number.'">'.$this->getSourceSelect($idbase, $number, $idsource).'</td>';
					html += '<td id="indicSelect'.$number.'">'.$this->getIndicSelect($idsource, $number, $idfield).'</td>';*/
					html += '<td><input type="text" class="fixed" id="data'+i+'" name="data[]" value="'+line.data+'" /></td>';
					html += '<td><input type="text" class="fixed" id="value1'+i+'" name="value1[]" value="'+line.value1+'" /></td>';
					html += '<td><input type="text" class="fixed" id="value2'+i+'" name="value2[]" value="'+line.value2+'" /></td>';
				html += '</tr>';
			}
			$("#mainTable").html(html);

			$("#mainTable .fieldSelect").each( function(){
				current = $(this).attr('id').replace('fieldSelect', '');
				updateBinder($(this).val(), current , $("#data" + current).val(), true);
				uprules();
			}); 
			displayListMessage(trad[24], "result");
			cleanAdd();
			$("#result").html('');
			$('.div_new_filter').show(); // **
			loadingFilter = false;
		}
	});
}

function uprules()
{
	var nbtmp = 1;
	$("#mainTable tr").each( function() {
		nbtmp++;
	});
	rules = nbtmp - 1;
	nbrules = nbtmp - 1;
}

function loadFilterInfo(idfilter)
{
	
	$.post(ajaxFile, {'idfilter' : idfilter, 'action' : 'loadFilterInfo', 'token' : tokenV, 'id_employee': id_employee},
	   function(data) {
	   info = jQuery.parseJSON(data);

	   info = info.return[0];
	    $("#idfilter").val(idfilter);
	   	$("#name").val(info["name"]);
	   	$("#description").val(info["description"]);
		if(typeof info.date_start !== 'undefined' && info.date_start !== null){
			if (info.date_start.substr(0,10) != "0000-00-00"){
				$("#date_start").val(info.date_start.substr(0,10));
				$("#date_end").val(info.date_end.substr(0,10));
			}
		}

	   	/**
	   	 * Populate the group's value after  the ajax call
	   	 * 
	   	 * @author atanas
	   	 */
	   	$('#groupUser').val(info.id_group != "0" ? info.id_group : -1);
	   	if (info.id_group == "0") {
	   		$('#newgrpdiv').show();
	   	} else {
	   		$('#newgrpdiv').hide();
	   	}
	   	var addReplaceValue = info.replace_customer == "0" ? 'add' : 'rep';

	   	$('[name="add"]').removeAttr('checked');
	   	$('[name="add"]').filter('[value="'+addReplaceValue+'"]').attr('checked', 'checked');
	   	$('#assign-auto').val(info.assignment_auto);
	   	
//	   	$('#mainForm').find('#idgroup').val(info.id_group != "0" ? info.id_group : -1);
//	   	$('#mainForm').find('#mode').val(info.replace_customer == "0" ? '0' : addReplaceValue);
	   	
	   	/**
	   	 * End populate
	   	 */
	});
}

function deleteFilter(idfilter)
{
	$.post(ajaxFile, {'idfilter' : idfilter, 'action' : 'deleteFilter', token : tokenV, 'id_employee': id_employee},
		function(data) {
		if (data)
			$("#list" + idfilter).remove();
			if ($('table#list').find('tr.trSelect').length == 0)
				$('table#list').slideUp();
			displayListMessage(trad[28], "result");
	});
}

function result()
{
	$("#load").show();	
	$.post(ajaxFile, $("#mainForm").serialize() + "&token=" + tokenV,
	   function(data) {		
	   	$("#result").html(data);
	   	$("#load").hide();
	});
}

function attribGroup()
{
	if ($("#groupUser").val() == -1 && $("#newgrp").val().trim() == '')
	{
		alert(unescape("Error ! %0A[en] - You must specify the name of the new group !%0A[fr] - Vous devez renseign%E9 le nom du nouveau groupe !")); // message erreur
		$('#newgrp').css('border', '1px solid #900');
		$('#newgrpdiv').fadeIn();
		return false;
	}
	
	var id = 1;

	if ($("#groupUser").val() == -1)
	{
		$.ajax({
			url: ajaxFile, 
			type: "POST",
			async: false,
			data: {"token":tokenV, "name":$("#newgrp").val(), "action":"newGroup", "id_employee":id_employee},
			success: function(data){
				$("#groupUser").val(data);
				id = data;
			}
		});
		$("#groupUser").append("<option value=" + id +" selected=selected>" + $("#newgrp").val() +"</option>");	
	}
	else
		id = $("#groupUser").val();	 
    
	$("#idgroup").val(id);
	$("#mode").val($("input[type=radio][name=add]:checked").attr("value"));
	$("#groupAttrib").hide();
	$("#wait").show();
	
//	if ($("select#assign-auto").val() == 1)
//	{
		$("#action").val('Save');
		
		if ($("#name").val().length == 0)
		{
			if ($('select#groupUser').val() == -1)
				$("#name").val($('input#newgrp').val());
			else
				$("#name").val($('select#groupUser option:selected').text());
		}
		
		$.post(
			ajaxFile,
			$("#mainForm").serialize() + '&id_employee=' + id_employee + '&assign-auto=' + $("select#assign-auto").val() + '&replace-customer=' + $("#mode").val() + '&token=' + tokenV,
			function(data){
				if ($("#idfilter").val() != 0)
			   {
			    	$("#list" + $("#idfilter").val()).remove();
			   }
			   data = jQuery.parseJSON(data);
			   $("#list").show();
			   $("#list").append('<tr id="list'+data.id+'" class="trSelect"><td>'+data.id+'</td><td>'+data.name+'</td><td>'+data.description+'</td><td>'+data.replace_customer+'</td><td>'+data.auto_assign+'</td><td>'+data.group_name+'</td><td><a href="javascript:deleteFilter('+data.id+');"><img src="../modules/mailjet/img/delete.png" /></a></td></tr>');
			   $("#idfilter").val(data.id);
			   $("#action").val('getQuery');
			   displayListMessage(trad[23], "result");
		});
		
		$("#action").val('addGroup');
//	}	
	
	$.post(
		ajaxFile,
		$("#mainForm").serialize() + '&id_employee=' + id_employee + '&assign-auto=' + $("select#assign-auto").val() + '&token=' + tokenV,
		function(data){
			$("#wait").hide();
			$("#groupAttrib").show();
			$('input#newgrp').val('');
			$('div#newgrpdiv').slideUp();
			if (data)
				displayActionMessage(trad[29], "result");
	});
	
	return false;
}

function next(page, nb)
{
	if (page <= nb)
	{
		$("#page").val(page);
		$.post(ajaxFile, $("#mainForm").serialize() + '&token=' + tokenV,
			function(data){
				$("#result").html(data);
			});
	}
}

function exportCSV()
{
	$("#mainForm").attr("action",$("#module_path").val()+"export.php" );
	$("#mainForm").submit();
}

function clearRules(ruleid)
{
	for (i = (ruleid + 1); i <= nbrules; i++)
	{
		id = i - 1;
		$("#" + i).attr('id', id)
		$("#action" + i).attr('id', "action" + id);
		//$("#action" + id).html('<a class="add" href="javascript:addLine();"><img src="../modules/mailjet/img/add.png" /></a><a class="delete" href="javascript:delLine(' + id + ');"><img src="../modules/mailjet/img/delete.png" /></a>');
		$("#action" + id).html('<a class="add" href="javascript:addLine();"><img src="../modules/mailjet/img/add.png" /></a><a class="delete" href="javascript:delLine(' + id + ');"><img src="../modules/mailjet/img/delete.png" /></a>');
		$("#id" + i).attr('id', "id" + id);
		$("#id" + id).html(id);
		$("#sourceSelect" + i).attr('id', "sourceSelect" + id);
		$("#indicSelect" + i).attr('id', "indicSelect" + id);
	}
}

function fillSelect(type)
{
		
		choix = parseInt(type);
		switch(choix)
		{
			case 0 :
			$("#periodNumber").html(select(1 , 52));
			break;
			case 1 :
			$("#periodNumber").html(select(1, 12));
			break;
			case 2 :
			$("#periodNumber").html(select(1, 4));
			break;
			case 3 :
			$("#periodNumber").html(select(1990, 2020));
			break;
		}
}

function select(start, end)
{
	
	retour = "<option value=-1>Select</option>";
	for (i = start; i <= end; i++)	
		retour += "<option value=" + i + ">" + i +"</option>";
	
	return retour;
}
function getDateAjax(type, number)
{
	if (type.trim() != "")
	{
		date  = new Date();
		$.post(
			ajaxFile, 
			{action : "date", typedate : type, periode : number , years : date.getFullYear() , 'token':tokenV, 'id_employee': id_employee},
			function(data){
				date = data.split("/");
				$("#date_start").val(date[0]);
				$("#date_end").val(date[1]);
		});
	}
}

var interval_percentage = null;

function sync_contacts()
{
	$("#sync").hide();
	$('#syncMessage').slideUp();
	$.ajax({
 		url: ajaxSyncFile,
 		type : "POST",
 		async: true,
 		data: $("#mainForm").serialize(),
  		success: function(data) {   
  			if (data != 'OK') {
  				$("#sync").show();
  				$('#syncMessageError').html(data).slideDown().delay(1000).slideUp();
  			} else {
  				$("#perc_sync_value").html(100);
  				$(".perc_sync").hide(); 
  				$("#sync").show();
  				$('#syncMessage').slideDown().delay(1000).slideUp();
  			}
  			clearInterval(interval_percentage);
		}
	});
	
	// On lance le script de récupération de l'avancement
	$("#perc_sync_value").html(0); 
	$(".perc_sync").show(); 
	interval_percentage = setInterval("refreshSyncPercentage()", 1000);
}

function refreshSyncPercentage()
{
	$.ajax({
 		url: ajaxSyncFile,
 		type : "POST",
 		async: true,
 		data: "action=getPercentage&token="+tokenV,
  		success: function(data) { 
			$("#perc_sync_value").html(data);  
		}
	});
}


function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}


function resetFilter() 
{
    $('#mainForm').get(0).reset();
    $('#newfilter').hide();
    $(".div_new_filter").show();
}