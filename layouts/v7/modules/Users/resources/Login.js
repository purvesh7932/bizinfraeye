Vtiger.Class("Users_Login_Js", {}, {
	limitKeypress: function () {
		$("#fusername").on('input', function (event) {
			let value = event.target.value;
// 			if (value != undefined && value.toString().length >= 5) {
// 				$(this).val($(this).val().substr(0, 5));
// 				event.preventDefault();
// 			}
		});
		var form = jQuery('#forgotPasswordDivFormhol');
		var params = {
			submitHandler: function (form) {
				let realParams = {};
				let params = $('#forgotPasswordDivFormhol').serializeArray();
				let paramLength = params.length;
				for (let i = 0; i < paramLength; i++) {
					realParams[params[i]['name']] = params[i]['value'];
				}
				app.helper.showProgress();
				$.ajax({
					type: "POST",
					url: "forgotPassword.php",
					cache: false,
					data: realParams,
					success: function (response) {
						if (response.success == true) {
							$("#ShowSucess").modal();
						} else {
							app.helper.showErrorNotificationInSignUP({ message: app.vtranslate(response.error.message) });
						}
						app.helper.hideProgress();
					}
				});
				return false;
			}
		}
		form.vtValidate(params);
	},
	registerEvents: function () {
		jQuery('#Redidirect').click(function () {
			window.location.href = "index.php";
		});
		this.limitKeypress();
	}

});
