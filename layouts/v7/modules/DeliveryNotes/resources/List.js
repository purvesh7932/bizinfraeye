Vtiger_List_Js("DeliveryNotes_List_Js", {
    syncToSAPDeliveryNotes : function (index) {
        let self = this;
        var message = "Do You want To Sync To Sap Now For Getting All DeliveryNotess?";
        app.helper.showProgress();
        if(index == 1){
            index = parseInt(localStorage.getItem('LastScaneedDeliveryNotesIndex'));
            if(index == undefined || index == null || isNaN(index)){
                index = 1;
            }
        }
        // app.helper.showConfirmationBox({ 'message': message }).then(function (e) {
            self.getSAPAPICallDeliveryNotes(index).then(
                function (data) {
                    if (data.result.hasNext == true) {
                        localStorage.setItem('LastScaneedDeliveryNotesIndex',index);
                        self.syncToSAPDeliveryNotes(index + 1);
                    } else {
                        app.helper.hideProgress();
                        // location.reload();
                    }
                },
                function (error, err) {
                    app.helper.hideProgress();
                }
            );
        // });
    },
    getSAPAPICallDeliveryNotes: function (index) {
        var aDeferred = jQuery.Deferred();
        var url = "module=DeliveryNotes&action=getAllDeliveryNotes&indexForLink=" + index;
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