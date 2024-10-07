Settings_Vtiger_List_Js("Settings_ITS4YouKanbanView_List_Js", {}, {
    getProgressIndicatorElement: function () {
        return jQuery.progressIndicator({
            'position': 'html',
            'blockInfo': {
                'enabled': true
            }
        });
    },
    removeKanbanSettingsForModule: function () {
        var thisInstance = this;
        jQuery('.deleteKanbanViewSettings').on('click', function (e) {
            e.stopPropagation();
            var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
            Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
                function () {
                    var tabId = jQuery(e.currentTarget).closest('tr').data('tabid');
                    var fieldId = jQuery(e.currentTarget).closest('tr').data('fieldid');

                    var params = {
                        module: app.getModuleName(),
                        parent: app.getParentModuleName(),
                        action: "DeleteAjax",
                        mode: "deleteKanbanSettings",
                        tabid: tabId,
                        fieldid: fieldId
                    };

                    var progressIndicatorElement = thisInstance.getProgressIndicatorElement();

                    AppConnector.request(params).then(
                        function (data) {
                            progressIndicatorElement.progressIndicator({'mode': 'hide'});
                            if (data.success) {
                                Settings_Vtiger_Index_Js.showMessage({
                                    text: app.vtranslate('JS_KANBAN_SETTINGS_REMOVED_SUCCESSFULLY'),
                                    type: 'success'
                                });
                                thisInstance.updateIndexContentAfterSettingsRemoval();
                            } else {
                                Settings_Vtiger_Index_Js.showMessage({
                                    text: app.vtranslate(error.message),
                                    type: 'error'
                                });
                            }
                        }
                    );
                }
            );
        });
    },
    editKanbanViewSettingsClickAction: function () {
        jQuery('.editKanbanViewSettings').on('click', function (e) {
            var editUrl = jQuery(e.currentTarget).closest('tr').data('editurl');

            window.location.href = editUrl;
        });
    },
    rowClickEvent: function () {
        jQuery('.listViewEntries').on('click', function (e) {
            var editUrl = jQuery(e.currentTarget).data('editurl');

            window.location.href = editUrl;
        });
    },
    updateIndexContentAfterSettingsRemoval: function () {
        var aDeferred = jQuery.Deferred();

        var thisInstance = this;
        var viewParams = {
            module: app.getModuleName(),
            parent: app.getParentModuleName(),
            view: "List",
        };

        var progressIndicatorElement = thisInstance.getProgressIndicatorElement();

        AppConnector.request(viewParams).then(
            function (response) {
                progressIndicatorElement.progressIndicator({'mode': 'hide'});
                jQuery('#indexViewContent').empty().html(response);
                thisInstance.addKanbanViewSettingsClickAction();
                thisInstance.editKanbanViewSettingsClickAction();
                thisInstance.rowClickEvent();
                aDeferred.resolve(response);
            }
        );

        return aDeferred.promise();
    },
    addKanbanViewSettingsClickAction: function () {
        jQuery('.addKanbanSettings').on('click', function (element) {
            var listViewUrl = jQuery(element.currentTarget).data('url');
            window.location.href = listViewUrl;
        });
    },
    registerEvents: function () {
        this.removeKanbanSettingsForModule();
        this.addKanbanViewSettingsClickAction();
        this.editKanbanViewSettingsClickAction();
        this.rowClickEvent();
    }
});