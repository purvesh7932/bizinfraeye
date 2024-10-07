Vtiger_Edit_Js("Settings_Vtiger_Edit_Js",{},{
    
    registerEvents : function() {
        this._super();
        //Register events for settings side menu (Search and collapse open icon )
        var instance = new Settings_Vtiger_Index_Js(); 
        instance.registerBasicSettingsEvents();
    }
})