/*
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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright  2007-2015 PrestaShop SA
* @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

var rules = 1;
var nbrules = 0;
$(document).ready( function() {
    addLine();

    if(getParameterByName('createNewFilter') == '1') {
        resetFilter();
    }
    
    $(".filter-table-cond").hide();
    $("table#mainTable").find('td, th').css('text-align', 'center');
    $('input#data1, input#pdata1, input#value11, input#value21').hide();
    $('input#data1, input#value11, input#value21').parents('td').addClass('grey fixed');

    $('#baseSelect1').attr('value', 1);
    updateSource($('#baseSelect1').val(), 1);

    $("#1 select.cond:first").hide();
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
                   // $('select#groupUser').val('');
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
         // turn off events for multi store customer segmentation
         if($('.sourceSelect').val() != 4){
             updateBinder($(this).val() ,$(this).attr('id').replace('fieldSelect', ''));
         }
     });

    $('.trSelect').live('click', function() {
        loadFilterInfo($(this).attr('id').replace('list',''));
        loadFilter($(this).attr('id').replace('list',''));
        $('#newfilter').show();
        if($('.sourceSelect').val() == 4) {
            $('.fixed[name^="value"], .fixed[name^="data"]').hide().parent().addClass('grey fixed');
        }
    });

     $("#export").live("click", function(){
                    exportCSV();
    });
     //Action fixe
     $("#save").click( function() {
            $("#action").val('Save');
            saveFilter();
            /* location.reload(); */
            return false;
     });

    $("#newfilter").click( function() {
        if ( $("#idfilter").val() != '0') {
            window.location.href = window.location.href + '&createNewFilter=1';
        } else {
            resetFilter();
        }
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