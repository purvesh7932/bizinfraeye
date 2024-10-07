Settings_Vtiger_Index_Js('Settings_ITS4YouQuickReminder_Uninstall_Js', {
    uninstall: function () {
        let module = app.getModuleName(),
            message = app.vtranslate('JS_UNINSTALL_CONFIRM');

        Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(function() {
            let url = 'index.php?module=' + module + '&action=Uninstall&parent=Settings';

            AppConnector.request(url).then(function (data) {
                window.location.href = "index.php";
            });
        });
    },
    actions: function () {
        var thisInstance = this;

        jQuery('#ITS4YouUninstall_btn').click(function() {
            thisInstance.uninstall();
        });
    },
    init: function() {
        this.actions();
    }
});