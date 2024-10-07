Users_Edit_Js("Users_PreferenceEdit_Js",{
	
	/**
	 * Function to register change event for currency separator
	 */
	registerChangeEventForCurrencySeparator : function(){
		var form = jQuery('form');
		jQuery('[name="currency_decimal_separator"]',form).on('change',function(e){
			var element = jQuery(e.currentTarget);
			var selectedValue = element.val();
			var groupingSeparatorValue = jQuery('[name="currency_grouping_separator"]',form).data('selectedValue');
			if(groupingSeparatorValue == selectedValue){
				var message = app.vtranslate('JS_DECIMAL_SEPARATOR_AND_GROUPING_SEPARATOR_CANT_BE_SAME');
				var params = {
					text: message,
					type: 'error'
				};
				Vtiger_Helper_Js.showMessage(params);
				var previousSelectedValue = element.data('selectedValue');
				element.find('option').removeAttr('selected');
				element.find('option[value="'+previousSelectedValue+'"]').attr('selected','selected');
				element.trigger("liszt:updated");
			}else{
				element.data('selectedValue',selectedValue);
			}
		})
		jQuery('[name="currency_grouping_separator"]',form).on('change',function(e){
			var element = jQuery(e.currentTarget);
			var selectedValue = element.val();
			var decimalSeparatorValue = jQuery('[name="currency_decimal_separator"]',form).data('selectedValue');
			if(decimalSeparatorValue == selectedValue){
				var message = app.vtranslate('JS_DECIMAL_SEPARATOR_AND_GROUPING_SEPARATOR_CANT_BE_SAME');
				var params = {
					text: message,
					type: 'error'
				};
				Vtiger_Helper_Js.showMessage(params);
				var previousSelectedValue = element.data('selectedValue');
				element.find('option').removeAttr('selected');
				element.find('option[value="'+previousSelectedValue+'"]').attr('selected','selected');
				element.trigger("liszt:updated");
			}else{
				element.data('selectedValue',selectedValue);
			}
		})
	}
},{
	
	/**
	 * register Events for my preference
	 */
	registerEvents : function(){
		this._super();
		Users_PreferenceEdit_Js.registerChangeEventForCurrencySeparator();
	}
});