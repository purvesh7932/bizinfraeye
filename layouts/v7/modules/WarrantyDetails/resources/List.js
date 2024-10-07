Vtiger_List_Js("WarrantyDetails_List_Js", {
    getUpdatedRecods : function () {
        let self = this;
        var message = "Are You want To Sync To Sap Now For Getting Records?";
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
        var url = "module=WarrantyDetails&action=getAllRecords";
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
    }
}, {

});