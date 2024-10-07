Vtiger_Detail_Js("ServiceEngineer_Detail_Js", {
	approveOrReject: function (url, acpStatus) {

		if (acpStatus == 'Rejected') {
			let confMsg = "Do You Really Want To Reject this User ? ";
			app.helper.showConfirmationBox({ 'message': confMsg }).then(function (e) {
				var data = {
					'module': 'ServiceEngineer',
					'view': 'RejectionReason',
					'mode': 'showRejectionReasonForm'
				};
				app.request.post({ "data": data }).then(function (err, res) {
					if (err === null) {
						var cb = function (data) {
							var form = jQuery(data).find('#AddRejectionReason');
							var params = {
								submitHandler: function (form) {
									var params = jQuery(form).serializeFormData();
									url = url + '&RejectionReason=' + params.rejectionReason;
									url = url + '&apStatus=' + acpStatus;
									app.request.post({ url: url }).then(
										function (error, data) {
											if (!error) {
												app.helper.hideModal();
												app.helper.showSuccessNotification({ message: data.message });
												location.reload();
											} else {
												app.helper.showErrorNotification({ message: error.message });
											}
										},
										function (error, err) {
										}
									);
								}
							}
							form.vtValidate(params);
						}
						app.helper.showModal(res, { "cb": cb });
					}
				})
			});
		} else {
			var data = {
				'module': 'ServiceEngineer',
				'action': 'HasLinkedFnAndMob',
				'record': this.getRecordId()
			};
			app.request.post({ "data": data }).then(
				function (error, data) {
					if (!error) {
						let confMsg = "Do You Really Want To Accept this User ? </br>" +
							" * Please Make Sure User Has Correct Login PlatForms </br>" ;
						app.helper.showConfirmationBox({ 'message': confMsg }).then(function (e) {
							url = url + '&apStatus=' + acpStatus;
							app.helper.showProgress();
							app.request.post({ url: url }).then(
								function (error, data) {
									if (!error) {
										app.helper.showSuccessNotification({ message: data.message });
										location.reload();
									} else {
										app.helper.showErrorNotification({ message: error.message });
									}
									app.helper.hideProgress();
								},
								function (error, err) {
								}
							);
						});
					} else {
						app.helper.showErrorNotification({ message: error.message });
					}
				},
				function (error, err) {
				}
			);
		}
	},
	getRecordId: function () {
		return app.getRecordId();
	},
	triggerChangePassword: function (url, module) {
		var message = "Do You Really Want To Reset This User Password ?";
		app.helper.showConfirmationBox({ 'message': message }).then(function (e) {
			app.helper.showProgress();
			app.request.get({ 'url': url }).then(
				function (err, data) {
					app.helper.hideProgress();
					if (err == null) {
						app.helper.hideModal();
						var successMessage = app.vtranslate(data.message);
						app.helper.showSuccessNotification({ "message": successMessage });
					} else {
						app.helper.showErrorNotification({ "message": err });
						return false;
					}
				}
			);
		});
	},
}, {});
