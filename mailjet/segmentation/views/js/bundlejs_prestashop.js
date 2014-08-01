var req = null;
/*(function($) {
    // définition du plugin
    $.fn.pos = function() {
    	obj = jQuery(this).get(0);
    	var curleft = obj.offsetLeft || 0;
 		var curtop = obj.offsetTop || 0;
    	while (obj = obj.offsetParent) {
        	curleft += obj.offsetLeft
        	curtop += obj.offsetTop
    	}
    	return {x:curleft,y:curtop};     
    };
})(jQuery);*/

(function($) {
    // définition du plugin
    $.fn.product = function(id,bind) {
		if (bind != false)
		{
			var obj = $(this);
			var tmpname = $(this).attr('name');
			obj.attr('autocomplete', 'off');

			$(this).after('<div class="plugproduct" id="complete' + obj.attr("id") + '"></div>');
			$(this).after('<input type="hidden" id="id'+ obj.attr("id") +'" value="0" name="' +  $(this).attr('name') +'">');
			$(this).after('<input type="text" class="fixed" id="p'+ obj.attr("id") +'" value="">');
			$(this).attr('name', 'old');
			$(this).hide();
			if (id)
			{
				$('#id'+ obj.attr("id")).val(id);
				$("#p"+ obj.attr("id")).val(getProductById(id));
			}
			
			$("#p" + obj.attr("id")).keyup(function() {
				if (req != null)
					req.abort();
				req = $.post(ajaxBundle, {'action' : 'product', 'name' : $(this).val(), 'id' : obj.attr("id"), 'token':tokenV}, function(data) {
					$("#complete" + obj.attr("id")).html(data);
				});
			});
			$("#p" + obj.attr("id")).change( function(){
				trid = $(this).parents('tr').attr('id');
				if ($(this).val() == '')
				{
					$("#id"+ obj.attr("id")).val('');					
					$('input#value1' + trid + ', input#value2' + trid).val('').hide();
					$('input#value1' + trid + ', input#value2' + trid).parents('td').addClass('grey fixed');
				}
				else 
				{
					setTimeout(function(){
						if ($('input#iddata' + trid).val() > 0)
						{
							$('input#value1' + trid + ', input#value2' + trid).show();
							$('input#value1' + trid + ', input#value2' + trid).parents('td').removeClass('grey fixed');
						}
					}, 200);					
				}
				setTimeout('$("#complete'+ obj.attr("id") + '").html("");',100000);
			});
			
			$('li', 'ul#plugproduct' + obj.attr('id')).live('click',function(){
				$("#id"+ obj.attr("id")).val($(this).attr('id'));
				$("#p"+ obj.attr("id")).val($(this).html());
				$("#complete" + obj.attr("id")).html('');
			});
		}
		else 
		{
			if ($("#complete" + $(this).attr("id")).length > 0)
			{
				$(this).attr('name', $("#id"+ $(this).attr("id")).attr('name'));
				$(this).val('');
				$(this).show();
				$("#complete" + $(this).attr("id")).remove();
				$("#p"+ $(this).attr("id")).remove();
				$("#id"+ $(this).attr("id")).remove();
				
			}
		}
		return $(this);    
   };
})(jQuery);

(function($) {
    // définition du plugin
    $.fn.category = function(idcat,bind) {
		if (bind != false)
		{
			var obj = $(this);
			var tmpname = $(this).attr('name');
			obj.attr('autocomplete', 'off');

			$(this).after('<div class="plugproduct" id="complete' + obj.attr("id") + '"></div>');
			$(this).after('<input type="hidden" id="id'+ obj.attr("id") +'" value="0" name="' +  $(this).attr('name') +'">');
			$(this).after('<input type="text" class="fixed" id="p'+ obj.attr("id") +'" value="">');
			$(this).attr('name', 'old');
			$(this).hide();
			if (idcat)
			{
				$('#id'+ obj.attr("id")).val(idcat);
				$("#p"+ obj.attr("id")).val(getCategoryById(idcat));
			}
			
			$("#p" + obj.attr("id")).keyup(function() {
				if (req != null)
					req.abort();
				
				req = $.post(ajaxBundle, {'action' : 'category', 'name' : $(this).val(), 'id' : obj.attr("id"), 'token':tokenV}, function(data) {
					$("#complete" + obj.attr("id")).html(data);
				});
			});
			
			$("#p" + obj.attr("id")).change( function(){
				trid = $(this).parents('tr').attr('id');
				if ($(this).val() == '')
				{
					$("#id"+ obj.attr("id")).val('');					
					$('input#value1' + trid + ', input#value2' + trid).val('').hide();
					$('input#value1' + trid + ', input#value2' + trid).parents('td').addClass('grey fixed');
				}
				else 
				{
					setTimeout(function(){
						if ($('input#iddata' + trid).val() > 0)
						{
							$('input#value1' + trid + ', input#value2' + trid).show();
							$('input#value1' + trid + ', input#value2' + trid).parents('td').removeClass('grey fixed');
						}
					}, 200);					
				}
				setTimeout('$("#complete'+ obj.attr("id") + '").html("");',100000);
			});
			
			$('li', 'ul#plugproduct' + obj.attr('id')).live('click',function(){
				$("#id"+ obj.attr("id")).val($(this).attr('id'));
				$("#p"+ obj.attr("id")).val($(this).html());
				$("#complete" + obj.attr("id")).html('');
			});
		}
		else
		{
			if ($("#complete" + $(this).attr("id")).length > 0)
			{
				$(this).attr('name', $("#id"+ $(this).attr("id")).attr('name'));
				$(this).val('');
				$(this).show();
				$("#complete" + $(this).attr("id")).remove();
				$("#p"+ $(this).attr("id")).remove();
				$("#id"+ $(this).attr("id")).remove();
				
			}
		}
		return $(this);    
	};
})(jQuery);

(function($) {
    // définition du plugin
    $.fn.brand = function(idcat,bind) {
		if (bind != false)
		{
			var obj = $(this);
			var tmpname = $(this).attr('name');
			obj.attr('autocomplete', 'off');

			$(this).after('<div class="plugproduct" id="complete' + obj.attr("id") + '"></div>');
			$(this).after('<input type="hidden" id="id'+ obj.attr("id") +'" value="0" name="' +  $(this).attr('name') +'">');
			$(this).after('<input type="text" class="fixed" id="p'+ obj.attr("id") +'" value="">');
			$(this).attr('name', 'old');
			$(this).hide();
			if (idcat)
			{
				$('#id'+ obj.attr("id")).val(idcat);
				$("#p"+ obj.attr("id")).val(getBrandById(idcat));
			}
			
			$("#p" + obj.attr("id")).keyup(function() {
				if (req != null)
					req.abort();
				req = $.post(ajaxBundle, {'action' : 'manufacturer', 'name' : $(this).val(), 'id' : obj.attr("id"), 'token':tokenV}, function(data) {
					$("#complete" + obj.attr("id")).html(data);
				});
			});
			$("#p" + obj.attr("id")).change( function(){
				trid = $(this).parents('tr').attr('id');
				if ($(this).val() == '')
				{
					$("#id"+ obj.attr("id")).val('');					
					$('input#value1' + trid + ', input#value2' + trid).val('').hide();
					$('input#value1' + trid + ', input#value2' + trid).parents('td').addClass('grey fixed');
				}
				else 
				{
					setTimeout(function(){
						if ($('input#iddata' + trid).val() > 0)
						{
							$('input#value1' + trid + ', input#value2' + trid).show();
							$('input#value1' + trid + ', input#value2' + trid).parents('td').removeClass('grey fixed');
						}
					}, 200);					
				}
				setTimeout('$("#complete'+ obj.attr("id") + '").html("");',100000);
			});
			
			$('li', 'ul#plugproduct' + obj.attr('id')).live('click',function(){
				$("#id"+ obj.attr("id")).val($(this).attr('id'));
				$("#p"+ obj.attr("id")).val($(this).html());
				$("#complete" + obj.attr("id")).html('');
			});
		}
		else
		{	
			if ($("#complete" + $(this).attr("id")).length > 0)
			{
				$(this).attr('name', $("#id"+ $(this).attr("id")).attr('name'));
				$(this).val('');
				$(this).show();
				$("#complete" + $(this).attr("id")).remove();
				$("#p"+ $(this).attr("id")).remove();
				$("#id"+ $(this).attr("id")).remove();
				
			}
		}
		return $(this);    
	};
})(jQuery);

(function($) {
    // définition du plugin
    $.fn.info = function(message, unbind) {
    	if (unbind)
    	{
    		$(this).unbind('mouseover');
        	$(this).unbind('mouseout');
    	}
    	else
    	{
    		var obj = $(this);
    		$(this).mouseover( function(){
    			$("#info").html(message);
    			$("#info").attr('style', 'left:' + (obj.pos().x + 4) + 'px;top:' + (obj.pos().y + 27) + 'px');
    			$("#info").show();
    		});
    		$(this).mouseout( function(){
    			$("#info").hide();
    		});
    		return $(this);     
    	}
    };
})(jQuery);

(function($) {
    // définition du plugin
    $.fn.inputToSelect = function(tab, selected, bind) {
    	
		if (bind != false)
		{
			var tmpname = $(this).attr('id');
			html = '<select id="idsel'+ $(this).attr('id') + '" class="fixed" name="' + $(this).attr('name') + '">' ;
			$.each(tab, function(index, value) {
				html += '<option value="' + index + '" ';
				if (index == selected)
					html += 'selected=slected ';
				html += '>'+value+'</option>';
			});
			html += '</select>';
			$(this).after(html);
			$(this).attr('name', 'old');
			$(this).hide();
		}
		else
		{
			if ($("#idsel" + $(this).attr('id')).length > 0)
			{
				$(this).attr('name', $("#idsel" + $(this).attr('id')).attr('name'));
				$(this).val('');
				$(this).show();
				$("#idsel" + $(this).attr('id')).remove();		
			}
		}
		return $(this);     
    };
})(jQuery);

function getProductById(id)
{
	retour = "",
	$.ajax({
 		url: ajaxBundle, 
 		type : "POST",
 		async: false,
 		data : "action=productname&id=" + id + "&token=" + tokenV,
  		success: function(data) 
  		{
  			retour = data;
  		}
  	});
	return retour;
}
function getCategoryById(id)
{
	retour = "",
	$.ajax({
 		url: ajaxBundle,
 		type : "POST",
 		async: false,
 		data : "action=categoryname&id=" + id + "&token=" + tokenV,
  		success: function(data) 
  		{
  			retour = data;
  		}
  	});
	return retour;
}

function getBrandById(id)
{
	retour = "",
	$.ajax({
 		url: ajaxBundle,
 		type : "POST",
 		async: false,
 		data : "action=brandname&id=" + id + "&token=" + tokenV,
  		success: function(data) 
  		{
  			retour = data;
  		}
  	});
	return retour;
}
