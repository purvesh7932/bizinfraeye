jQuery.Class("Settings_Vtiger_Detail_Js",{},{
	detailViewForm : false,

	/**
	 * Function which will give the detail view form
	 * @return : jQuery element
	 */
	getForm : function() {
		if(this.detailViewForm == false) {
			this.detailViewForm = jQuery('#detailView');
		}
		return this.detailViewForm;
	},

	/**
	 * Function to register form for validation
	 */
	registerFormForValidation : function(){
		var detailViewForm = this.getForm();
		detailViewForm.validationEngine(app.validationEngineOptions);
	},

	/**
	 * Function which will handle the registrations for the elements
	 */
	registerEvents : function() {
		this.registerFormForValidation();
	}
});