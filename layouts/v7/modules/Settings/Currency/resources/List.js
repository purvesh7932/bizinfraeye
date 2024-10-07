Settings_Currency_Js('Settings_Currency_List_Js', {}, {
	
	init : function() {
            this._super();
		this.addComponents();
	},
	
	addComponents : function() {
		this.addModuleSpecificComponent('Index','Vtiger',app.getParentModuleName());
	}
});
