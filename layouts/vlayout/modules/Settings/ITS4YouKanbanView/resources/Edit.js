Settings_Vtiger_Edit_Js("Settings_ITS4YouKanbanView_Edit_Js", {}, {
    registerModuleChangeEvent: function () {
        var thisInstance = this;
        jQuery('#pickListModules').on('change', function (e) {
            var selectedModule = jQuery(e.currentTarget).val();
            if (selectedModule.length <= 0) {
                Settings_Vtiger_Index_Js.showMessage({'type': 'error', 'text': app.vtranslate('JS_PLEASE_SELECT_MODULE')});
                return;
            }
            var params = {
                module: app.getModuleName(),
                parent: app.getParentModuleName(),
                source_module: selectedModule,
                view: 'IndexAjax',
                mode: 'getPickListDetailsForModule'
            };
            var progressIndicatorElement = jQuery.progressIndicator({
                'position': 'html',
                'blockInfo': {
                    'enabled': true
                }
            });
            AppConnector.request(params).then(
                function (data) {
                    progressIndicatorElement.progressIndicator({'mode': 'hide'});
                    jQuery('#modulePickListContainer').html(data);
                    app.changeSelectElementView(jQuery('#modulePickListContainer'));
                    thisInstance.registerModulePickListChangeEvent();
                    jQuery('#modulePickList').trigger('change');
                }
            )
        });
    },
    registerModulePickListChangeEvent: function () {
        var thisInstance = this;
        jQuery('#modulePickList').on('change', function (e) {
            var params = {
                module: app.getModuleName(),
                parent: app.getParentModuleName(),
                source_module: jQuery('#pickListModules').val(),
                view: 'IndexAjax',
                mode: 'getPickListValueForField',
                pickListFieldId: jQuery(e.currentTarget).val()
            };
            var progressIndicatorElement = jQuery.progressIndicator({
                'position': 'html',
                'blockInfo': {
                    'enabled': true
                }
            });

            AppConnector.request(params).then(
                function (data) {
                    progressIndicatorElement.progressIndicator({'mode': 'hide'});
                    jQuery('#modulePickListValuesContainer').html(data);
                    app.showSelect2ElementView(jQuery('#modulePickListValuesContainer').find('select.select2'), {_maximumSelectionSize: 7, dropdownCss: {'z-index': 0}});

                    thisInstance.saveEnabledPicklistValues();
                    thisInstance.registerBackClickEvent();
                }
            );
        });
    },
    saveEnabledPicklistValues: function () {
        jQuery('#saveOrder').on('click', function (e) {
            var pickListValues = jQuery('#menuListSelectElement option');
            console.log(pickListValues);
            var selectedValues = jQuery('#menuListSelectElement').val();

            var enabledValues = [];
            jQuery.each(pickListValues, function () {
                var currentValue = jQuery(this);
                if (selectedValues && jQuery.inArray(currentValue.val(), selectedValues) > -1) {
                    enabledValues.push(currentValue.data('id'));
                }
            });

            var params = {
                module: app.getModuleName(),
                parent: app.getParentModuleName(),
                action: 'SaveAjax',
                mode: 'savePicklistValues',
                enabled_values: enabledValues,
                picklistName: jQuery('#modulePickList').val(),
                selectedModule: jQuery('#pickListModules').val()
            };

            var progressIndicatorElement = jQuery.progressIndicator({
                'position': 'html',
                'blockInfo': {
                    'enabled': true
                }
            });

            AppConnector.request(params).then(
                function (data) {
                    progressIndicatorElement.progressIndicator({'mode': 'hide'});
                    if (data.success) {
                        Settings_Vtiger_Index_Js.showMessage({
                            text: app.vtranslate('JS_KANBAN_SETTINGS_SAVED_SUCCESSFULLY'),
                            type: 'success'
                        });
                    } else {
                        Settings_Vtiger_Index_Js.showMessage({
                            text: app.vtranslate('JS_KANBAN_SETTINGS_NOT_SAVED'),
                            type: 'error'
                        });
                    }
                }
            );
        });
    },
    registerBackClickEvent: function () {
        jQuery('#backLink').on('click', function (e) {
            var url = jQuery(e.currentTarget).data('backurl');
            window.location.href = url;
        });
    },
    registerEvents: function () {
        this.registerModuleChangeEvent();

        jQuery('#pickListModules').trigger('change');
    }
})