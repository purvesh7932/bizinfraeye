Vtiger_Edit_Js("Settings_Vtiger_Edit_Js",{},{
	
	/**
	 * Function to register form for validation
	 */
	registerFormForValidation : function(){
		var editViewForm = this.getForm();
		editViewForm.validationEngine(app.validationEngineOptions);
	},
	
	/**
	 * Function which will handle the registrations for the elements 
	 */
	registerEvents : function() {
		this.registerFormForValidation();
	}
})