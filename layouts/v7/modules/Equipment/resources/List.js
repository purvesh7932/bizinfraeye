Vtiger_List_Js("Equipment_List_Js", {
    syncToSAPEquipment: function (index) {
        let self = this;
        var message = "Do You want To Sync To Sap Now For Getting All Equipments?";
        app.helper.showProgress();
        if(index == 1){
            index = parseInt(localStorage.getItem('LastScaneedEquipmentIndex'));
            if(index == undefined || index == null || isNaN(index)){
                index = 1;
            }
        }
        // app.helper.showConfirmationBox({ 'message': message }).then(function (e) {
            self.getSAPAPICallEquipment(index).then(
                function (data) {
                    if (data.result.hasNext == true) {
                        localStorage.setItem('LastScaneedEquipmentIndex',index);
                        self.syncToSAPEquipment(index + 1);
                    } else {
                        app.helper.hideProgress();
                        location.reload();
                    }
                },
                function (error, err) {
                    app.helper.hideProgress();
                }
            );
        // });
    },
    getSAPAPICallEquipment: function (index) {
        var aDeferred = jQuery.Deferred();
        var url = "module=Equipment&action=getAllEquipment&indexForLink=" + index;
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
    getUpdatedRecods: function () {
        let self = this;
        var message = "Do You want To Sync To Sap Now For Getting Updated Records?";
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
    getUpdateWarrentyStatus: function () {
        let self = this;
        var message = "Do You want To Update Warrenty Status Now For Getting Updated Records?";
        app.helper.showConfirmationBox({ 'message': message }).then(function (e) {
            app.helper.showProgress();
            self.getUWSCall().then(
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
    getUWSCall: function () {
        var aDeferred = jQuery.Deferred();
        var url = "module=Equipment&action=WarrentyStatusUpdate";
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
    getUpdateRunningContractYear: function () {
        let self = this;
        var message = "Do You want To Update Running Contract Year Now For Getting Updated Records?";
        app.helper.showConfirmationBox({ 'message': message }).then(function (e) {
            app.helper.showProgress();
            self.getURCYCall().then(
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
    getURCYCall: function () {
        var aDeferred = jQuery.Deferred();
        var url = "module=Equipment&action=GetContractYear";
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
    calcAvail: function () {
        let self = this;
        var message = "Do You want To Calculate Availabilty?";
        app.helper.showConfirmationBox({ 'message': message }).then(function (e) {
            app.helper.showProgress();
            self.calcAvailApiCall().then(
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
    },
    getSAPAPICall: function () {
        var aDeferred = jQuery.Deferred();
        var url = "module=Equipment&action=SyncRecentlyUpdated";
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
    getAllAgregates: function (index) {
        let self = this;
        var message = "Do You want To Sync To Sap Now For Getting Aggregates?";
        // app.helper.showConfirmationBox({ 'message': message }).then(function (e) {
            app.helper.showProgress();
            if(index == 1){
                index = parseInt(localStorage.getItem('LastScaneedEquipmentAgIndex'));
                if(index == undefined || index == null || isNaN(index)){
                    index = 1;
                }
            }
            self.getAllAgregatesAPICall(index).then(
                function (data) {
                    if (data.result.hasNext == true) {
                        localStorage.setItem('LastScaneedEquipmentAgIndex',index);
                        self.getAllAgregates(index + 1);
                    } else {
                        app.helper.hideProgress();
                        location.reload();
                    }
                },
                function (error, err) {
                    app.helper.hideProgress();
                }
            );
        // });
    },
    getAllAgregatesAPICall: function (index) {
        var aDeferred = jQuery.Deferred();
        var url = "module=Equipment&action=GetAllAggregates&indexForLink=" + index;
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