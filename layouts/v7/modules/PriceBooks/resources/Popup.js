Vtiger_Popup_Js("PriceBooks_Popup_Js",{},{
	
	/**
	 * Function to pass params for request
	 */
	getCompleteParams : function(){
		var params = this._super();
		params['currency_id'] = jQuery('#currencyId').val();
		return params;
	},
	
	registerEvents: function(){
		this._super();
	}
});

