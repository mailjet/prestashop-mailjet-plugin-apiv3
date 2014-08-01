(function($, undifened) {

	/**
	 * Check if the customer finished his setup under the iframe
	 */
	function checkMerchantSetupState()
	{
		$.ajax({
			type : 'POST',
			url : _PS_MJ_MODULE_DIR_ + 'ajax.php',
			data :	{'method': 'checkMerchantSetupState', 'token': MJ_TOKEN, 'admin_token': MJ_ADMINMODULES_TOKEN},
			dataType: 'json',
			success: function(json)
			{
				if (json && json.result)
					window.location.href = json.url;
			},
			error: function(xhr, ajaxOptions, thrownError)
			{
				// console.log(xhr)
			}
		});
	}
	
	
	$(document).ready(function() {
	
		switch(MJ_page_name)
		{
			case MJ_SETUP_STEP_1:
				var timer = $.timer(checkMerchantSetupState);
				timer.set({time: 10000, autostart: true});
			break;
	
			case MJ_LOGIN:
				$('#MJ_auth_link').click(function() {
					$('#MJ_auth_form').submit();
					return false;
				});
			break;
		}
	});
})(jQuery);