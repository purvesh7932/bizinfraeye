Inventory_List_Js("ServiceReports_List_Js", {
    syncToSAPForSettingsMaster: function () {
        let self = this;
        var message = "Are You want To Sync To Sap Now?";
        app.helper.showConfirmationBox({ 'message': message }).then(function (e) {
            app.helper.showProgress();
            self.getSAPAPICall().then(
                function (data) {
                    app.helper.hideProgress();
                },
                function (error, err) {
                    app.helper.hideProgress();
                }
            );
        });
    },
    getSAPAPICall: function () {
        var aDeferred = jQuery.Deferred();
        var url = "module=ServiceReports&action=SyncMasterData";
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