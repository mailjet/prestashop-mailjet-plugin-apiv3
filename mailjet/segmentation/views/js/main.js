var rules = 1;
var nbrules = 0;
$(document).ready( function() {
	addLine();
	
	$(".filter-table-cond").hide();
	$("table#mainTable").find('td, th').css('text-align', 'center');
	$('input#data1, input#pdata1, input#value11, input#value21').hide();
	$('input#data1, input#value11, input#value21').parents('td').addClass('grey fixed');
	
	$('#baseSelect1').attr('value', 1);
	updateSource($('#baseSelect1').val(), 1);
	
	$("#1 select.cond").hide();
	$("#action1 a.delete").remove();
	
	$('select#groupUser').bind('change', function(){
		if ($(this).val() != '-1')
		{
			$('input#newgrp').val('');
			$('div#newgrpdiv').slideUp();
		}
		else
		{
			$('div#newgrpdiv').slideDown();
		}
	});
	
	$('input#newgrp').bind('keyup', function(){
		if ($(this).val())
		{
			$('select#groupUser').val('');
		}
	});
	
	$('.baseSelect').live ('change', function() {
		id = $(this).attr('id').replace('baseSelect', '');
		updateSource($(this).val(), id);
	 });
	 
	 //Action Globale
	 $('.sourceSelect').live ('change', function() {
		id = $(this).attr('id').replace('sourceSelect', '');
		updateIndic($(this).val(), id);
		
	 });
	 
	 $('.fieldSelect').live ('change', function() {
		updateBinder($(this).val() ,$(this).attr('id').replace('fieldSelect', ''));
	 });
	 
	$('.trSelect').live('click', function() {
		loadFilterInfo($(this).attr('id').replace('list',''));
		loadFilter($(this).attr('id').replace('list',''));
	});
	 
	 $("#export").live("click", function(){
			exportCSV();
	});
	 //Action fixe
	 $("#save").click( function() {
	 	$("#action").val('Save');
	 	saveFilter();
	 	return false;
	 });
	 
	 $("#newfilter").click( function() {
		 $(this).toggle(); // **
	 	$(".div_new_filter").toggle();
	});

	
	$("#view").click( function() {
		$("#action").val('getQuery');
		result();
	});
	
	$("#sync").click( function() {
		sync_contacts();
	});
	
	$("#periodType").live("change", function(){	
		fillSelect($(this).val());
	});
	
	$("#periodNumber").live("change", function(){	
		getDateAjax($("#periodType").val(), $(this).val());
	});
	
	$("#date_start").datepicker({
			prevText:"",
			nextText:"",
			dateFormat:datePickerJsFormat});
	$("#date_end").datepicker({
			prevText:"",
			nextText:"",
			dateFormat:datePickerJsFormat});
		
	$("#groupAttrib").live("click", function(){
		$("#action").val('addGroup');
		attribGroup();
	});
	
});