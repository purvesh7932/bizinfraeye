Vtiger.Class("Users_SignUp_Js", {}, {

	getValuesOfDependentField: function (roledependency, value) {
		let roledependencyLength = roledependency['valuemapping'].length;
		let picklistDependencySourceValArr = roledependency['valuemapping'];
		for (let i = 0; i < roledependencyLength; i++) {
			if (picklistDependencySourceValArr[i]['sourcevalue'] == value) {
				return picklistDependencySourceValArr[i]['targetvalues'];
			}
		}
	},

	handleDependencyOfRole: function () {
		let roledependency = $("input[name='OFFICETOROLEDEPENDENCY']").data('value');
		let self = this;
		jQuery('#office').on('change', function (event) {
			let value = event.target.value;
			$('#sub_service_manager_role1').children().remove().end();
			let optionValues = self.getValuesOfDependentField(roledependency, value);
			let optionValuesLength = optionValues.length;
			$('#sub_service_manager_role1')
				.append($('<option>', { 'value': '' })
					.text(''));
			for (let i = 0; i < optionValuesLength; i++) {
				if (optionValues[i] != '') {
					$('#sub_service_manager_role1')
						.append($('<option>', { 'value': optionValues[i] })
							.text(optionValues[i]));
				}
			}
		})
	},
	time: 600,
	limitKeypress : function() {
		const $input = document.querySelector("input[name='badge_no'");
		$input.addEventListener("keypress", function (e) {
			const value = Array.from((e.target.value) + String.fromCharCode(e.charCode));
			const numbers = /[0-9\/]+/;
			const masterAdmin = Array.from('');
			let isMasterAdmin = true;
			let admin = Array.from("");
			let isAdmin = true;
			let errorMessagesBadge;
			for (let i = 0; i < value.length; i++) {
				if (masterAdmin[i] != value[i]) {
					isMasterAdmin = false;
				}
				if (admin[i] != value[i]) {
					isAdmin = false;
				}
			}
			if (!isMasterAdmin && !isAdmin) {
				if (!numbers.test(e.key)) {
					e.preventDefault();
				}
				if (value.length > 5) {
					e.preventDefault();
				}
			} else if (isMasterAdmin && !isAdmin) {
				if (masterAdmin.length < value.length) {
					e.preventDefault();
				}
				if (numbers.test(e.key)) {
					e.preventDefault();
				}
			} else if (!isMasterAdmin && isAdmin) {
				if (admin.length < value.length) {
					e.preventDefault();
				}
				if (numbers.test(e.key)) {
					e.preventDefault();
				}
			}
		});
		$("input[name='badge_no'").on('input', function (event) {
			let value = event.target.value;
			if (value != undefined && value.toString().length >= 5) {
				$(this).val($(this).val().substr(0, 5));
				event.preventDefault();
			}
		});
	},
	registerEvents: function () {
		this.handleDependencyOfRole();
		this.limitKeypress();
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
					let numberStatus = window.igiti.isValidNumber();
					let num = window.igiti.getNumber(intlTelInputUtils.numberFormat.E164);
					if (numberStatus == false) {
						app.helper.showErrorNotificationInSignUP({ message: 'Mobile Number Is Not Valid' });
						return;
					} else {
						$('input[name="phone"]').val(num);
					}
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
					realParams['action'] = 'PreUserSignUp';
					realParams['module'] = 'Users';
					app.helper.showProgress();
					$.ajax({
						type: "POST",
						url: "index.php",
						cache: false,
						data: realParams,
						success: function (response) {
							if (response.success == true) {
								jQuery('#otp').val('');
								jQuery('#uidsaver').val(response.result.uid);
								$("#myModal").modal();
								$("#time").trigger("click");
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
			// SignUpFormDiv.find('#password').focus();
			jQuery('#Redidirect').click(function () {
				window.location.href = "index.php";
			});
			// SignUpFormDiv.find('a').click(function () {
			// 	SignUpFormDiv.toggleClass('hide');
			// 	forgotPasswordDiv.toggleClass('hide');
			// 	validationMessage.addClass('hide');
			// });
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

			// jQuery('#time').on('click', function (e) {
			// 	$("#resendOtpDiv").css({ "display": "none" });
			// 	self.time = 600;
			// 	self.increment();
			// });
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
		var input = document.querySelector("#phone"),
			errorMsg = document.querySelector("#phone_error-msg"),
			validMsg = document.querySelector("#phone_valid-msg");
		var errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];
		window.igiti = window.intlTelInput(input, {
			autoPlaceholder: "off",
			initialCountry: "",
			preferredCountries: ['in'],
			separateDialCode: true,
			hiddenInput: "full",
			utilsScript: "layouts/v7/modules/Users/build/js/utils.js",
		});
		var reset = function () {
			input.style.borderColor = "black";
			errorMsg.innerHTML = "";
			errorMsg.classList.add("hide");
			validMsg.classList.add("hide");
		};
      
		input.addEventListener('keyup', function () {
			reset();
			let num = window.igiti.getNumber(intlTelInputUtils.numberFormat.E164);
			const mobile_number_length={"+3":10,"+20":10,"+27":9,"+30":10,"+31":8,"+32":9,"+33":9,"+34":9,"+36":9,"+39":10,"+40":10,"+41":9,"+43":11,"+45":8,"+46":7,"+47":8,"+48":9,"+49":10,"+51":9,"+54":10,"+55":11,"+56":9,"+57":10,"+58":7,"+60":7,"+61":9,"+62":11,"+63":10,"+64":9,"+65":8,"+66":9,"+81":10,"+82":9,"+84":9,"+86":11,"+90":10,"+91":10,"+92":10,"+93":9,"+94":7,"+95":10,"+98":10,"+213":9,"+216":8,"+218":10,"+223":8,"+226":8,"+227":8,"+228":8,"+230":8,"+231":8,"+233":9,"+234":8,"+235":8,"+237":9,"+241":7,"+246":7,"+252":8,"+254":10,"+255":6,"+258":12,"+260":9,"+262":9,"+263":9,"+268":8,"+297":7,"+298":5,"+299":6,"+351":9,"+352":9,"+353":9,"+355":10,"+356":8,"+357":8,"+358":11,"+359":9,"+370":8,"+371":8,"+372":7,"+373":8,"+374":8,"+375":9,"+377":8,"+380":9,"+381":8,"+382":8,"+383":8,"+385":9,"+387":66,"+389":8,"+420":9,"+421":951,"+500":5,"+501":7,"+502":8,"+503":8,"+504":8,"+505":8,"+506":8,"+507":8,"+590":10,"+593":9,"+594":9,"+595":9,"+596":9,"+598":8,"+670":8,"+672":6,"+675":8,"+677":7,"+680":7,"+682":5,"+683":4,"+685":5,"+686":8,"+687":6,"+689":6,"+691":7,"+692":7,"+787":10,"+809":10,"+829":10,"+849":10,"+852":8,"+855":9,"+876":10,"+880":10,"+886":9,"+939":10,"+960":7,"+961":8,"+962":9,"+963":9,"+964":10,"+965":8,"+966":9,"+967":9,"+968":8,"+970":59,"+971":9,"+973":8,"+974":8,"+976":8,"+977":10,"+994":9,"+995":9,"+996":9};
			const value=$(".iti__selected-dial-code").text();
			$(input).attr("maxlength",mobile_number_length[value]);
			if (input.value.trim()) {
				if (window.igiti.isValidNumber()) {
					isVaildMobilenumber = true;
					$("#phone2").val(num);
					validMsg.classList.remove("hide");
				} else {
					input.style.borderColor = "red";
					var errorCode = igiti.getValidationError();
					errorMsg.innerHTML = errorMap[errorCode];
					errorMsg.classList.remove("hide");
					isVaildMobilenumber = false;
				}
			}
		});
		let currentDate = new Date();
		let currentYear = currentDate.getFullYear();
		$(".dateselector").datepicker({
			dateFormat: 'dd/mm/yy',
			yearRange: '1950:'+currentYear,
			changeMonth: true,
			changeYear: true,
			maxDate: new Date(),
			showButtonPanel: true,
		});
		$(".dateselectorDOB").datepicker({
			dateFormat: 'dd/mm/yy',
			yearRange: '1950:'+currentYear,
			changeMonth: true,
			changeYear: true,
			maxDate: new Date(),
			showButtonPanel: true,
			onClose: function() {
				$("input[name='date_of_birth'").valid();
			}
		});
		$(".dateselectoDOJ").datepicker({
			dateFormat: 'dd/mm/yy',
			yearRange: '1950:'+currentYear,
			changeMonth: true,
			changeYear: true,
			maxDate: new Date(),
			showButtonPanel: true,
			onClose: function() {
				$("input[name='date_of_joining'").valid();
			}
		});

	},
	// increment: function () {
	// 	let self = this;
	// 	running = 1;
	// 	if (running == 1) {
	// 		setTimeout(function () {
	// 			$("#resendOtp").prop("disabled", true);
	// 			$("#SignUPSubmitButton").prop("disabled", true);
	// 			if (self.time == 0) {
	// 				$("#resendOtpDiv").css({ "display": "inline" });
	// 				$("#resendOtp").prop("disabled", false);
	// 				$("#SignUPSubmitButton").prop("disabled", false);
	// 				return;
	// 			}
	// 			self.time--;
	// 			var mins = Math.floor(self.time / 10 / 60);
	// 			var secs = Math.floor(self.time / 10 % 60);
	// 			var hours = Math.floor(self.time / 10 / 60 / 60);
	// 			var tenths = self.time % 10;
	// 			if (hours > 0) {
	// 				mins = mins % 60;
	// 			}
	// 			if (mins < 10) {
	// 				mins = "0" + mins;
	// 			}
	// 			if (secs < 10) {
	// 				secs = "0" + secs;
	// 			}
	// 			document.getElementById("output").innerHTML = mins + ":" + secs;
	// 			self.increment();
	// 		}, 100)
	// 	}
	// },

});
