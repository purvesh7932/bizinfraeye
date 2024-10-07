Vtiger_List_Js("Products_List_Js", {
    getUpdatedRecods : function () {
        let self = this;
        var message = "Are You want To Sync To Sap Now For Getting Updated Records?";
        app.helper.showConfirmationBox({ 'message': message }).then(function (e) {
            app.helper.showProgress();
            self.getSAPAPICall().then(
                function (data) {
                    app.helper.hideProgress();
                    location.reload();
                },
                function (error, err) {
                    app.helper.hideProgress();
                }
            );
        });
    },
    getSAPAPICall: function () {
        var aDeferred = jQuery.Deferred();
        var url = "module=Products&action=SyncRecentlyUpdated";
        AppConnector.request(url).then(
            function (data) {
                if (data['success']) {
                    aDeferred.resolve(data);
                } else {
                    aDeferred.reject(data['message']);
                }
            },
            function (error) {
                aDeferred.reject();
            }
        )
        return aDeferred.promise();
    },
    syncToSAPMaterialMaster : function (index) {
        let self = this;
		app.helper.showProgress();
        if(index == 1){
            index = parseInt(localStorage.getItem('LastScaneedProductIndex'));
            if(index == undefined || index == null || isNaN(index)){
                index = 1;
            }
        }
		this.getSAPAPICallMaterialMaster(index).then(
				function (data) {
                    if(data.result.hasNext == true){
                        localStorage.setItem('LastScaneedProductIndex',index);
                        self.syncToSAPMaterialMaster(index + 1);
                    } else {
                        app.helper.hideProgress();
                    }
				},
				function (error, err) {
					app.helper.hideProgress();
				}
		);
	},
    getSAPAPICallMaterialMaster : function (index) {
        var aDeferred = jQuery.Deferred();
        var url = "module=Products&action=ProductsExternalAppSync&indexForLink="+index;
        AppConnector.request(url).then(
                function (data) {
                    if (data['success']) {
                        aDeferred.resolve(data);
                    } else {
                        aDeferred.reject(data['message']);
                    }
                },
                function (error) {
                    aDeferred.reject();
                }
        )
        return aDeferred.promise();
	},
}, {

});