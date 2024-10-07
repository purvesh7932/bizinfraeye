Vtiger.Class("Users_UserSetupAnother_Js", {}, {
	time: 600,
	registerEvents: function () {
		var self = this;
		jQuery(document).ready(function () {
			var form = jQuery('#SignUpFormDiv');
			const togglePassword1 = document.querySelector('#togglePassword1');
			const togglePassword2 = document.querySelector('#togglePassword2');
			const password_1 = document.querySelector('#password_1');
			const password_2 = document.querySelector('#password_2');
			togglePassword1.addEventListener('click', function (e) {
				const type = password_1.getAttribute('type') === 'password' ? 'text' : 'password';
				password_1.setAttribute('type', type);
				this.classList.toggle('glyphicon-eye-close');
			});
			togglePassword2.addEventListener('click', function (e) {
				const type = password_2.getAttribute('type') === 'password' ? 'text' : 'password';
				password_2.setAttribute('type', type);
				this.classList.toggle('glyphicon-eye-close');
			});
			var params = {
				submitHandler: function (form) {
					var user_password = jQuery('input[name=user_password]').val();
					var confirm_password = jQuery('input[name=confirm_password]').val();

					if (user_password != confirm_password) {
						app.helper.showErrorNotificationInSignUP({ message: 'Set Password And Re-Type Password Is Not Matching' });
						var result = false;
						return result;
					}

					let realParams = {};
					let params = $('#SignUpFormDiv').serializeArray();
					let paramLength = params.length;
					for (let i = 0; i < paramLength; i++) {
						realParams[params[i]['name']] = params[i]['value'];
					}
					realParams['action'] = 'UserSetupSaveFromSE';
					realParams['module'] = 'Users';
					app.helper.showProgress();
					$.ajax({
						type: "POST",
						url: "index.php",
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
					var result = false;
					return result;
				}
			}
			form.vtValidate(params);

			var validationMessage = jQuery('#validationMessage');
			var forgotPasswordDiv = jQuery('#forgotPasswordDiv');

			var SignUpFormDiv = jQuery('#SignUpFormDiv');
			jQuery('#Redidirect').click(function () {
				window.location.href = "index.php";
			});
			
			var otpHandler = jQuery('#SignUpFormDiv1');
			otpHandler.find('#OTPSubmit').on('click', function () {
				let otp = $('#otp').val();
				let uid = $('#uidsaver').val();
				callAnother(otp, uid);
				return false;
			});
			otpHandler.find('#resendOtpDiv').on('click', function () {
				SignUpFormDiv.find('button').trigger('click');
				return false;
			});
			var showPasswordDiv = jQuery('#forgotPasswordDiv1');
			showPasswordDiv.find('#togglepassText').click(function () {
				let type = document.getElementById('password').type;
				if (type == 'password') {
					document.getElementById('password').type = 'text';
				} else {
					document.getElementById('password').type = 'password';
				}
			});

			forgotPasswordDiv.find('a').click(function () {
				SignUpFormDiv.toggleClass('hide');
				forgotPasswordDiv.toggleClass('hide');
				validationMessage.addClass('hide');
			});

			jQuery('#time').on('click', function (e) {
				$("#resendOtpDiv").css({ "display": "none" });
				let initialTime = Date.now();
				let limit = 60;
				self.time = 600;
				console.log(limit);
				increment(initialTime, limit);
			});
			function increment(initialTime, limit) {
				let self = this;
				running = 1;
				if (running == 1) {
					setTimeout(function () {
						let time = (Date.now() - initialTime),
							hours = Math.floor((time / (60000)) % 60),
							minss = Math.floor((time / 1000) % 60),
							secss = Math.floor((time / 10) % 100);
						self.time--;
						document.getElementById("output").innerHTML = "00" + ":" + (limit - minss);
						if ((limit - minss) <= 1) {
							$("#resendOtpDiv").css({ "display": "inline" });
							$("#resendOtp").prop("disabled", false);
							$("#SignUPSubmitButton").prop("disabled", false);
							document.getElementById("output").innerHTML = "00" + ":" + "00";
							return;
						}
						increment(initialTime, limit);
					}, 100)
				}
			}
			function callAnother(OTP, UID) {
				let realParams = {};
				realParams['action'] = 'UserSignUp';
				realParams['otp'] = OTP;
				realParams['uid'] = UID;
				realParams['module'] = 'Users';
				$.ajax({
					type: "POST",
					url: "index.php",
					cache: false,
					data: realParams,
					success: function (response) {
						if (response.success == true) {
							$("#ShowSucess").modal();
						} else {
							$("#errorModalContent").text(response.error.message);
							$("#ShowError").modal();
						}
					}
				});
			}

			forgotPasswordDiv.find('button').on('click', function () {
				var username = jQuery('#forgotPasswordDiv #fusername').val();
				var email = jQuery('#email').val();

				var email1 = email.replace(/^\s+/, '').replace(/\s+$/, '');
				var emailFilter = /^[^@]+@[^@.]+\.[^@]*\w\w$/;
				var illegalChars = /[\(\)\<\>\,\;\:\\\"\[\]]/;

				var result = true;
				var errorMessage = '';
				if (username === '') {
					errorMessage = 'Please enter valid username';
					result = false;
				} else if (!emailFilter.test(email1) || email == '') {
					errorMessage = 'Please enter valid email address';
					result = false;
				} else if (email.match(illegalChars)) {
					errorMessage = 'The email address contains illegal characters.';
					result = false;
				}
				if (errorMessage) {
					validationMessage.removeClass('hide').text(errorMessage);
				}
				return result;
			});
			jQuery('input').blur(function (e) {
				var currentElement = jQuery(e.currentTarget);
				if (currentElement.val()) {
					currentElement.addClass('used');
				} else {
					currentElement.removeClass('used');
				}
			});

			var ripples = jQuery('.ripples');
			ripples.on('click.Ripples', function (e) {
				jQuery(e.currentTarget).addClass('is-active');
			});

			ripples.on('animationend webkitAnimationEnd mozAnimationEnd oanimationend MSAnimationEnd', function (e) {
				jQuery(e.currentTarget).removeClass('is-active');
			});
			SignUpFormDiv.find('#username').focus();

			var slider = jQuery('.bxslider').bxSlider({
				auto: true,
				pause: 4000,
				nextText: "",
				prevText: "",
				autoHover: true
			});
			jQuery('.bx-prev, .bx-next, .bx-pager-item').live('click', function () {
				slider.startAuto();
			});
			jQuery('.bx-wrapper .bx-viewport').css('background-color', 'transparent');
			jQuery('.bx-wrapper .bxslider li').css('text-align', 'left');
			jQuery('.bx-wrapper .bx-pager').css('bottom', '-15px');

			var params = {
				theme: 'dark-thick',
				setHeight: '100%',
				advanced: {
					autoExpandHorizontalScroll: true,
					setTop: 0
				}
			};
			jQuery('.scrollContainer').mCustomScrollbar(params);
		});
	},
});
