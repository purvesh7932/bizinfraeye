Settings_Vtiger_Index_Js("Settings_ITS4YouInstaller_Uninstall_Js", {}, {
    uninstall: function () {
        var module = app.getModuleName(),
            message = app.vtranslate('JS_UNINSTALL_CONFIRM');

        app.helper.showConfirmationBox({message: message}).then(function () {
            var url = 'index.php?module=' + module + '&parent=Settings&action=Uninstall';

            app.request.post({url: url}).then( function (err, data) {
                if (err === null) {
                    if (data.success === true) {
                        window.location.href = "index.php";
                    }
                }
            });
        });
    },
    registerActions: function () {
        var thisInstance = this;

        jQuery('#ITS4YouUninstall_btn').click(function (e) {
            thisInstance.uninstall();
        });
    },
    init: function () {
        this._super();
        this.registerActions();
    }
});