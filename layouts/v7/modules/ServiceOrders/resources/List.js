
Inventory_List_Js("ServiceOrders_List_Js", {
    getUpdatedRecods: function () {
        let self = this;
        var message = "Are You want To Sync To Sap Now For Getting Updated Records?";
        app.helper.showConfirmationBox({ 'message': message }).then(function (e) {
            app.helper.showProgress();
            self.getSAPAPICall().then(
                function (data) {
                    app.helper.hideProgress();
                    // location.reload();
                },
                function (error, err) {
                    app.helper.hideProgress();
                }
            );
        });
    },
    getSAPAPICall: function () {
        var aDeferred = jQuery.Deferred();
        var url = "module=ServiceOrders&action=SyncRecentlyUpdated";
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
}, {});