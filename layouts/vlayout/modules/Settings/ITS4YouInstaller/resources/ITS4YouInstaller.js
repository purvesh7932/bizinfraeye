jQuery.Class("Settings_ITS4YouInstaller_Extensions_Js", {
    showPopover : function(e) {
        let ele = jQuery(e);
        let options = {
            placement : ele.data('position'),
            trigger   : 'hover'
        };
        ele.popover(options);
    }
}, {
    showAlert: function(message, success = true) {

        let type = success ? 'success' : 'error',
            params = {
                type: type,
                text: app.vtranslate(message),
            };

        Vtiger_Helper_Js.showPnotify(params);
    },
    getImportModuleIndexParams: function() {
        return {
            'module': app.getModuleName(),
            'parent': app.getParentModuleName(),
            'view': 'Extensions'
        };
    },

    /*getImportModuleStepView: function(params) {
        let aDeferred = jQuery.Deferred();

        AppConnector.request(params).then( function(data) {
            aDeferred.resolve(data);
        }, function(error) {
            aDeferred.reject(error);
        });

        return aDeferred.promise();
    },*/

    registerEventForIndexView: function() {
        let detailContentsHolder = jQuery('.contentsDiv');

        //this.registerEventsForInstaller(detailContentsHolder);
    },
    
    getContainer : function() {
        return jQuery('.contentsDiv');
    },

    registerEventsForInstaller: function(container) {
        let thisInstance = this;

        jQuery(container).find('.installExtension, .installPaidExtension').on('click', function(e) {
            thisInstance.installExtension(e);
        });

        jQuery(container).find('.renewButton, .buyButton').on('click',function(e){
                let element = jQuery(e.currentTarget);
                let url = element.data('url');
                window.open(url);
        });

        jQuery('#logoutInstaller').off('click').on('click', function() {

            Vtiger_Helper_Js.showConfirmationBox({message:'<b>'+app.vtranslate('JS_ARE_YOU_SURE_LOGOUT')+'?</b>'}).then(function(){
                let params = {
                    'module': app.getModuleName(),
                    'parent': app.getParentModuleName(),
                    'action': 'Basic',
                    'mode': 'logoutITS4YouInstaller'
                };

                AppConnector.request(params).then(function() {
                    location.reload();
                });
            });
        });

        jQuery('#logintoInstaller, .logintoInstaller').off('click').on('click', function(e) {
            let loginAccountModal = jQuery(container).find('.loginAccount').clone(true, true);
                loginAccountModal.removeClass('hide');

            let callBackFunction = function() {
                let loginAccount = loginAccountModal,
                    form = loginAccount.find('form');

                form.submit(function(e) {
                   e.preventDefault();
                });
                form.validationEngine(app.validationEngineOptions);

                thisInstance.registerLoginModalTabEvents(loginAccount);

                loginAccount.find('#saveButton').on('click', function() {
                    let activeForm = loginAccount.find('.tab-pane:visible form'),
                        formData = activeForm.serializeFormData();

                    AppConnector.request(formData).then(function(data) {
                        app.hideModalWindow();
                        location.reload();

                    }, function(error) {
                        Vtiger_Helper_Js.showPnotify({type: 'error', "title": error});
                    });
                });
            };

            app.showModalWindow(loginAccountModal, {cb:callBackFunction});
        });

        jQuery(container).off('click').on('click', '.oneclickInstallFree', function(e) {
            const element = jQuery(e.currentTarget),
                extensionContainer = element.closest('.extension_container'),
                extensionId = extensionContainer.find('[name="extensionId"]').val(),
                extensionType = extensionContainer.find('[name="extensionType"]').val(),
                extensionName = extensionContainer.find('[name="extensionName"]').val(),
                moduleAction = extensionContainer.find('[name="moduleAction"]').val();

            if(element.hasClass('loginRequired')){
                let loginError = app.vtranslate('JS_PLEASE_LOGIN_TO_MARKETPLACE_FOR_INSTALLING_EXTENSION');
                thisInstance.showAlert(loginError, false);
                return false;
            }

            let question = 'JS_ARE_YOU_SURE_INSTALL';
            if(element.hasClass('isUpdateBtn')){
                question = 'JS_ARE_YOU_SURE_UPGRADE';
            }

            let params = {
                'module': app.getModuleName(),
                'parent': app.getParentModuleName(),
                'view': 'Extensions',
                'mode': 'oneClickInstall',
                'extensionId': extensionId,
                'moduleAction': moduleAction,
                'extensionName': extensionName,
                'extensionType': extensionType,
            };

            Vtiger_Helper_Js.showConfirmationBox({message: app.vtranslate(question)+'?'}).then(function(){

                AppConnector.request(params).then(function(installationLogData) {
                    let callBackFunction = function(data) {
                        data = installationLogData;

                        let installationStatus = jQuery(data).find('[name="installationStatus"]').val();

                        if (installationStatus === "success") {
                            let installed = app.vtranslate('JS_INSTALLED');

                            element.closest('span').html('<span class="alert alert-info">' + installed + '</span>');
                            extensionContainer.find('[name="moduleAction"]').val(installed);
                        }
                    };

                    app.showModalWindow(installationLogData, { cb: callBackFunction });
                });
            });
        });

        jQuery(container).on('click', '#installLoader', function(e) {
            let extensionLoaderModal = jQuery(container).find('.extensionLoader').clone(true, true);
            extensionLoaderModal.removeClass('hide');

            app.showModalWindow(extensionLoaderModal);
        });
    },

    /**
     * Function to register quick create tab events
     */
    registerLoginModalTabEvents : function(form) {
        let modalContent = form.find('.modal-content');
        let tabElements = modalContent.find('.nav.nav-pills , .nav.nav-tabs').find('a');

        let loginModalTabOnHide = function(tabElement) {
            let container = form.find('#' + tabElement.attr('data-target'));
            container.addClass('hide');
        };

        let loginModalTabOnShow = function(tabElement) {
            let tabName = tabElement.attr('data-target');
            let container = form.find('#' + tabName);
            container.removeClass('hide');
            modalContent.data('actual-tab', tabName);
        };

        tabElements.on('shown.bs.tab', function(e) {

            let previousTab = jQuery(e.relatedTarget);
            let currentTab = jQuery(e.currentTarget);

            loginModalTabOnHide(previousTab);
            loginModalTabOnShow(currentTab);
        });

        loginModalTabOnHide(tabElements.closest('li').filter(':not(.active)').find('a'));
    },

    installExtension: function(e) {
        let element = jQuery(e.currentTarget);
        let url = element.data('url');

        if(url){
            window.open(url);
        }
    },
    /*registerEventsForInstallerDetail: function(container) {
        let thisInstance = this;

        container = jQuery(container);
        jQuery('.carousel').carousel({
            interval: 3000
        });

        container.find('#installExtension').on('click', function(e) {
            let element = jQuery(e.currentTarget);
            if(element.hasClass('loginRequired')){
                let loginError = app.vtranslate('JS_PLEASE_LOGIN_TO_MARKETPLACE_FOR_INSTALLING_EXTENSION');
                Vtiger_Helper_Js.showErrorNotification({"message": loginError});
                return false;
            }

            Vtiger_Helper_Js.showConfirmationBox({message:'<b>'+app.vtranslate('JS_ARE_YOU_SURE_INSTALL')+'?</b>'}).then(function(){
				let extensionId = jQuery('[name="extensionId"]').val();
				let targetModule = jQuery('[name="targetModule"]').val();
				let moduleType = jQuery('[name="moduleType"]').val();
				let moduleAction = jQuery('[name="moduleAction"]').val();
				let fileName = jQuery('[name="fileName"]').val();

				let params = {
					'module': app.getModuleName(),
					'parent': app.getParentModuleName(),
					'view': 'Extensions',
					'mode': 'installationLog',
					'extensionId': extensionId,
					'moduleAction': moduleAction,
					'targetModule': targetModule,
					'moduleType': moduleType,
					'fileName': fileName
				}

				thisInstance.getImportModuleStepView(params).then(function(installationLogData) {
					let callBackFunction = function(data) {
						let installationStatus = jQuery(data).find('[name="installationStatus"]').val();
						if (installationStatus == "success") {
							jQuery('#installExtension').remove();
							jQuery('#launchExtension').removeClass('hide');
							jQuery('.writeReview').removeClass('hide');
						}
                        Vtiger_Helper_Js.showScroll(jQuery('#installationLog'), {'height': '150px'});
					};
					let modalData = {
						cb: callBackFunction
					};
                    app.showModalWindow(installationLogData, modalData);
				});
            });
            
        });

        container.find('#uninstallModule').on('click', function(e) {
            let element = jQuery(e.currentTarget);
            let extensionName = container.find('[name="targetModule"]').val();
            if(element.hasClass('loginRequired')){
                let loginError = app.vtranslate('JS_PLEASE_LOGIN_TO_MARKETPLACE_FOR_UNINSTALLING_EXTENSION');
                Vtiger_Helper_Js.showErrorNotification({"message": loginError});
                return false;
            }

            Vtiger_Helper_Js.showConfirmationBox({message:'<b>'+app.vtranslate('JS_ARE_YOU_SURE_UNINSTALL')+'?</b>'}).then(function(){
                let params = {
                'module': app.getModuleName(),
                'parent': app.getParentModuleName(),
                'action': 'Basic',
                'mode': 'uninstallExtension',
                'extensionName': extensionName
                };

                AppConnector.request({data: params}).then(function(error, data) {
                    if (!error) {
                        container.find('#declineExtension').trigger('click');
                    }
                });
            });
        });

        container.find('#declineExtension').on('click', function() {
            let params = thisInstance.getImportModuleIndexParams();
            thisInstance.getImportModuleStepView(params).then(function(data) {
                let detailContentsHolder = jQuery('.contentsDiv');
                detailContentsHolder.html(data);
                //thisInstance.registerEventForIndexView();
            });
        });
    },*/

    registerExtensionTabs : function(container) {
        let thisInstance = this;
        container.on('click', '.extensionTab', function(e){
            let element = jQuery(e.currentTarget),
                params = {
                'module': app.getModuleName(),
                'parent': app.getParentModuleName(),
                'view': 'Extensions',
                'mode': 'getExtensionByType',
                'type': element.data('type')
            };

            AppConnector.request(params).then(
                function(error, data) {
                    jQuery('.extensionTab').removeClass('active').removeClass('btn-primary');
                    element.addClass('active').addClass('btn-primary');

                    jQuery('#extensionContainer').html(data);
                    //thisInstance.registerEventForIndexView();
                }
            );
        });    
    },

    registerIframeTabs : function(container) {
        let thisInstance = this;
        container.on('click', '.customerPortalBtn', function(e){
            let heightContainer = jQuery('#extensionContainer').height();
            jQuery('#customerPortalIframe').height(heightContainer - 50);
        });
    },

    getTrial: function(trial, button) {
        let self = this,
            params = {
            module: app.getModuleName(),
            parent: app.getParentModuleName(),
            action: 'Trial',
            mode: 'getTrial',
            trial: trial,
        };

        AppConnector.request(params).then(function(data) {
            if(data.success) {
                data = data.result;

                if(data.success && data.license) {

                    let params = {
                        module: app.getModuleName(),
                        parent: 'Settings',
                        view: 'License',
                        mode: 'activate',
                        license: data.license,
                    };

                    self.showActivateLicense(params);
                } else {
                    self.showAlert(data.message, data.success);
                }
            }
        });
    },

    trialButtonActions: function() {
        let self = this;

        jQuery('.trialButton').on('click', function() {
            let button = jQuery(this),
                data = button.data(),
                trial = parseInt(data['trial']);

            if(trial > 0) {
                self.getTrial(trial, button);
            }
        });
    },

    registerEventsForTrial() {
        this.trialButtonActions();
    },
    showActivateLicense(params) {
        app.hideModalWindow();
        AppConnector.request(params).then(function(data) {
            if(data) {
                app.showModalWindow(data);
            }
        });
    },
    activateLicense() {
        let self = this;

        jQuery('#activateLicense').live('submit', function(e) {
            e.preventDefault();
            let form = jQuery(this),
                formData = form.serializeFormData(),
                license = formData['licensekey'],
                module = formData['module'],
                params = {
                    module: app.getModuleName(),
                    license: license,
                    action: 'License',
                    parent: 'Settings',
                    mode: 'activate',
                };

            if('' !== license) {
                AppConnector.request(params).then(function(data) {
                    if(data.result) {
                        let result = data.result;

                        if(result.success) {
                            self.showAlert(result.message);

                            location.reload();
                        } else {
                            self.showAlert(result.message, false);
                        }
                    } else {
                        self.showAlert(data, false);
                    }
                    app.hideModalWindow();
                });
            } else {
                jQuery('#licensekey').addClass('input-error');
            }
        });

    },
    activateButton() {
        let self = this;

        jQuery('.activateButton').live('click', function(e) {
            let params = {
                    module: app.getModuleName(),
                    parent: 'Settings',
                    view: 'License',
                    mode: 'activate',
                };

            self.showActivateLicense(params);
        });
    },
    registerActivateLicenses() {
        let self = this;

        this.activateButton();
        this.activateLicense();
    },
    registerLicensesActions() {
        let self = this;

        jQuery('.actionLicenses').on('click', function() {
            let button = jQuery(this),
                btnData = button.data(),
                message = 'JS_DEACTIVATE_LICENSES_QUESTION';

            if(btnData.mode === 'update') {
                message = 'JS_UPDATE_LICENSES_QUESTION';
            }

            if(btnData.license) {
                Vtiger_Helper_Js.showConfirmationBox({message: app.vtranslate(message)}).then(function() {
                    let mode = btnData.mode,
	                    params = {
                            module: app.getModuleName(),
                            parent: 'Settings',
                            action: 'License',
                            mode: mode,
                            license: btnData.license
                        };

                    button.attr('disable', 'disabled');
                    AppConnector.request(params).then(function(data) {
                        if(data.success) {
                            let message = '';
                            data = data.result;

                            if(data.message) {
                                message = data.message;
                            }

                            if(data.success) {
                                self.showAlert(message);

                                if(mode === 'deactivate') {
                                    button.parents('tr').hide();
                                }
                            } else {
                                self.showAlert(message, false);
                            }
                        }
                    });
                });
            }
        });
    },
    registerUpdateLicenses: function() {
        let self = this;

        jQuery('.updateButton').on('click', function() {
            let button = jQuery(this),
                message = app.vtranslate('JS_LICENSE_UPDATE_QUESTION'),
                params = {
                    module: app.getModuleName(),
                    parent: 'Settings',
                    action: 'License',
                    mode: 'update',
                };

            Vtiger_Helper_Js.showConfirmationBox({message: message}).then(function() {
                button.attr('disabled', 'disabled');

                AppConnector.request(params).then(function(data) {
                    if(data.success) {
                        if(data.result.message) {
                            self.showAlert(data.result.message);
                        }
                        button.removeAttr('disabled');
                        location.reload();
                    }
                });
            });
        });
    },
    registerLicensesColors: function() {
        jQuery('.licenseColors').on('click', function() {
            let id = jQuery(this).attr('href');

            jQuery(id)
                .animate({backgroundColor: '#FFEEEE'}, 1000)
                .animate({backgroundColor: '#ffffff'}, 10000);
        });
    },
    registerEvents: function() {
        let container = jQuery('.contentsDiv');
        //this.registerEventForIndexView();
        this.registerEventsForInstaller(container);
        this.registerExtensionTabs(container);
        this.registerIframeTabs(container);
        this.registerEventsForTrial();
        this.registerActivateLicenses();
        this.registerUpdateLicenses();
        this.registerLicensesActions();
        this.registerLicensesColors();
    }
});

/*jQuery(document).ready(function() {
    let settingExtensionStoreInstance = new Settings_ITS4YouInstaller_Extensions_Js();
    let mode = jQuery('[name="mode"]').val();
    if(mode == 'detail'){
        settingExtensionStoreInstance.registerEventsForInstallerDetail(jQuery('.contentsDiv'));
    }
});*/
