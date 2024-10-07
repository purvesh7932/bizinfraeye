Vtiger_List_Js("EquipmentAvailability_List_Js", {
    calcAvail: function () {
        let self = this;
        var message = "Do You want To Calculate Availabilty?";
        app.helper.showConfirmationBox({ 'message': message }).then(function (e) {
            app.helper.showProgress();
            self.calcAvailApiCall().then(
                function (data) {
                    app.helper.hideProgress();
                },
                function (error, err) {
                    app.helper.hideProgress();
                }
            );
        });
    },
    calcAvailApiCall: function () {
        var aDeferred = jQuery.Deferred();
        var url = "module=Equipment&action=CalcAvail";
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