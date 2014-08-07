function addLine()
{
	$(".filter-table-cond").show();
	
	if ((rules - 1) > 0)
	{
		$("#action" + (rules -1)).html('<a href="javascript:addLine();" class="add"><img src="../modules/mailjet/segmentation/views/img/add.png" /></a> <a class="delete" href="javascript:delLine(' + (rules - 1) + ');"><img src="../modules/mailjet/segmentation/views/img/delete.png" /></a>');
	}
	newline = $("#newLine").html();
	newline = newline.replace(/#####/g,rules);
	newline = newline.replace('<tbody>','');
	$("#mainTable").append(newline);
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
	$("#1 select.cond").hide();
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
	$.post(ajaxFile, {'action' : 'getSource' ,'token' : tokenV, 'baseID' : idbase, 'ID' : idrule,'id_employee': id_employee},
	   function(data) {
	   	$("#sourceSelect" + idrule).html(data);
	   	$("#sourceSelect" + idrule).attr('class', '');
	   });
	
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
	$.post(ajaxFile, $("#mainForm").serialize() + "&token=" + tokenV,
	   function(data) {
	   if ($("#idfilter").val() != 0)
	   {
	    	$("#list" + $("#idfilter").val()).remove();
	   }
	   data = jQuery.parseJSON(data);
	    $("#list").show();
		$(".no_filter_string").hide();
	   $("#list").append('<tr id="list'+data.id+'" class="trSelect"><td>'+data.id+'</td><td>'+data.name+'</td><td>'+data.description+'</td><td>'+data.replace_customer+'</td><td>'+data.auto_assign+'</td><td>'+data.group_name+'</td><td><a href="javascript:deleteFilter('+data.id+');"><img src="../modules/mailjet/segmentation/views/img/delete.png" /></a></td></tr>');
	   $("#idfilter").val(data.id);
	   $(".div_new_filter").hide();
	   $("#action").val('getQuery');
	   displayListMessage(trad[23], "result");
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
	console.log('loadFilter('+idfilter+')'); // **
	$.ajax({
 		url: ajaxFile,
 		type : "POST",
 		async: false,
 		data : {'idfilter' : idfilter, 'action' : 'loadFilter', 'token' : tokenV, 'id_employee': id_employee},
  		success: function(data) {
			console.log('- return Ajax'); // **
  			loadingFilter = true;
			$("#mainTable").html(data);
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
	   console.log(info);
	   info = info.return[0];
	    $("#idfilter").val(idfilter);
	   	$("#name").val(info["name"]);
	   	$("#description").val(info["description"]);
	   	if (info.date_start.substr(0,10) != "0000-00-00")
	   	{
			$("#date_start").val(info.date_start.substr(0,10));
			$("#date_end").val(info.date_end.substr(0,10));
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
	
	if ($("select#assign-auto").val() == 1)
	{
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
			   $("#list").append('<tr id="list'+data.id+'" class="trSelect"><td>'+data.id+'</td><td>'+data.name+'</td><td>'+data.description+'</td><td>'+data.replace_customer+'</td><td>'+data.auto_assign+'</td><td>'+data.group_name+'</td><td><a href="javascript:deleteFilter('+data.id+');"><img src="../modules/mailjet/segmentation/img/delete.png" /></a></td></tr>');
			   $("#idfilter").val(data.id);
			   $("#action").val('getQuery');
			   displayListMessage(trad[23], "result");
		});
		
		$("#action").val('addGroup');
	}	
	
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
		//$("#action" + id).html('<a class="add" href="javascript:addLine();"><img src="../modules/'+modname+'/views/img/add.png" /></a><a class="delete" href="javascript:delLine(' + id + ');"><img src="../modules/'+modname+'/views/img/delete.png" /></a>');
		$("#action" + id).html('<a class="add" href="javascript:addLine();"><img src="../modules/mailjet/segmentation/views/img/add.png" /></a><a class="delete" href="javascript:delLine(' + id + ');"><img src="../modules/mailjet/segmentation/views/img/delete.png" /></a>');
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
  				console.log(data);
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