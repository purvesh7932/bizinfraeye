Vtiger_List_Js("Inventory_List_Js", {

},
        {

            showQuickPreviewForId: function(recordId, appName, templateId) {
                var self = this;
                var vtigerInstance = Vtiger_Index_Js.getInstance();
                vtigerInstance.showQuickPreviewForId(recordId, self.getModuleName(), app.getAppName(), templateId);
            },
            
            registerEvents: function() {
                this._super();
            }

        });
