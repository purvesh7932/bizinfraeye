Vtiger_List_Js("BankGuarantee_List_Js", {
    syncToSAPFunction: function (moduleName) {
        app.helper.showProgress();
        this.syncToSAPFunctionCall(moduleName).then(
            function (data) {
                app.helper.hideProgress();
            },
            function (error, err) {
                app.helper.hideProgress();
            }
        );
    },
    syncToSAPFunctionCall : function (moduleName) {
        var aDeferred = jQuery.Deferred();
        var url = "module="+ moduleName +"&action=SyncAll"+moduleName;
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
    AutoUpdateBGStatusINPeriodic : function (moduleName) {
        app.helper.showProgress();
        this.AutoUpdateBGStatusINPeriodicAPICall(moduleName).then(
            function (data) {
                app.helper.hideProgress();
            },
            function (error, err) {
                app.helper.hideProgress();
            }
        );
    },
    AutoUpdateBGStatusINPeriodicAPICall : function (moduleName) {
        var aDeferred = jQuery.Deferred();
        var url = "module="+ moduleName +"&action=AutoUpdateBGStatusINPeriodic";
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