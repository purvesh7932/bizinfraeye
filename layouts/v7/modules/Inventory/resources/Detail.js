Vtiger_Detail_Js("Inventory_Detail_Js", {
    triggerRecordPreview: function(recordId){
        var thisInstance = app.controller();
        thisInstance.showRecordPreview(recordId);
    },
    
    sendEmailPDFClickHandler: function(url) {
        var params = app.convertUrlToDataParams(url);
        
        app.helper.showProgress();
                app.request.post({data:params}).then(function(err,response){
                    var callback = function() {
                        var emailEditInstance = new Emails_MassEdit_Js();
                        emailEditInstance.registerEvents();                      
                    };
                    var data = {};
                    data['cb'] = callback;
                    app.helper.hideProgress();
                    app.helper.showModal(response,data);
                });
    },
},
{
    showRecordPreview: function(recordId, templateId) {
        var thisInstance = this;
        var params = {};
        var moduleName = app.getModuleName();
        params['module'] = moduleName;
        params['record'] = recordId;
        params['view'] = 'InventoryQuickPreview';
        params['navigation'] = 'false';
        params['mode']='Detail';

        if (templateId) {
            params['templateid'] = templateId;
        }
        app.helper.showProgress();
        app.request.get({data: params}).then(function(err, response) {
            app.helper.hideProgress();

            if (templateId) {
                jQuery('#pdfViewer').html(response);
                return;
            }
            app.helper.showModal(response, {'cb': function(modal) {
                    jQuery('.modal-dialog').css({"width": "870px"});
                    thisInstance.registerChangeTemplateEvent(modal, recordId);
                }
            });
        });
    },
     registerChangeTemplateEvent: function(container, recordId) {
         var thisInstance = this;
        var select = container.find('#fieldList');
        select.on("change", function() {
            var templateId = select.val();
            thisInstance.showRecordPreview(recordId, templateId);
        });

    },
    
    
    
    registerEvents: function() {
		var self = this;
        this._super();
        this.getDetailViewContainer().find('.inventoryLineItemDetails').popover({html: true});
		app.event.on("post.relatedListLoad.click", function() {
			self.getDetailViewContainer().find('.inventoryLineItemDetails').popover({html: true});
		});
    },

});