Vtiger_Edit_Js("ServiceEngineer_Edit_Js", {}, {

	registerBasicEvents: function (container) {
		this._super(container);
		this.hide();
		this.phoneCountryCode();
		this.myFunction();
	},
// 	hide: function () {
// 		$("#ServiceEngineer_editView_fieldName_rejection_reason").parent().parent().parent().parent().addClass("hide");

// 		$('select').on('change', function () {
// 			var name = this.value;
// 			if (name == "Rejected") {
// 				$("#ServiceEngineer_editView_fieldName_rejection_reason").parent().parent().parent().parent().removeClass("hide");

// 			}
// 			if (name == "Accepted") {
// 				$("#ServiceEngineer_editView_fieldName_rejection_reason").parent().parent().parent().parent().addClass("hide");

// 			}
// 			if (name == "Pending") {
// 				$("#ServiceEngineer_editView_fieldName_rejection_reason").parent().parent().parent().parent().addClass("hide");

// 			}
// 		})
// 	},
	myFunction: function () {
		$("#district_officehideOrShowId").addClass("hide");
		$("#service_centrehideOrShowId").addClass("hide");
		$("#activity_centrehideOrShowId").addClass("hide");
		$("#production_divisionhideOrShowId").addClass("hide");
		$("#regional_officehideOrShowId").addClass("hide");

		let officeVal = $('select[name="office"]').val();
		if (officeVal == 'District Office') {
			$("#district_officehideOrShowId").removeClass("hide");
		} else if (officeVal == "Service Centre") {
			$("#service_centrehideOrShowId").removeClass("hide");
		} else if (officeVal == "Activity Centre") {
			$("#activity_centrehideOrShowId").removeClass("hide");
		} else if (officeVal == "Production Division") {
			$("#production_divisionhideOrShowId").removeClass("hide");
		} else if (officeVal == "Regional Office") {
			$("#regional_officehideOrShowId").removeClass("hide");
		}

		jQuery('.office').on('change', function (event) {
			$("#district_officehideOrShowId").addClass("hide");
			$("#service_centrehideOrShowId").addClass("hide");
			$("#activity_centrehideOrShowId").addClass("hide");
			$("#production_divisionhideOrShowId").addClass("hide");
			$("#regional_officehideOrShowId").addClass("hide");
			let name = $(this).val();
			if (name == 'District Office') {
				$("#district_officehideOrShowId").removeClass("hide");
			} else if (name == "Service Centre") {
				$("#service_centrehideOrShowId").removeClass("hide");
			} else if (name == "Activity Centre") {
				$("#activity_centrehideOrShowId").removeClass("hide");
			} else if (name == "Production Division") {
				$("#production_divisionhideOrShowId").removeClass("hide");
			} else if (name == "Regional Office") {
				$("#regional_officehideOrShowId").removeClass("hide");
			}
		});
	},
	phoneCountryCode: function () {
		var input = document.querySelector("#ServiceEngineer_editView_fieldName_phone"),
		errorMsg = document.querySelector("#phone_error-msg"),
		validMsg = document.querySelector("#phone_valid-msg");
		if(input){
			$(document).ready(function () {
				$('#ServiceEngineer_editView_fieldName_phone').attr('type', 'tel');
			});
			let isVaildMobilenumber = false;
			// Error messages based on the code returned from getValidationError
			var errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];

			// Initialise plugin
			window.igiti = window.intlTelInput(input, {
				autoPlaceholder: "off",
				initialCountry: "",
				preferredCountries: ['in'],
				hiddenInput: "phone",
				separateDialCode: true,
				utilsScript: "layouts/v7/modules/Users/build/js/utils.js",
			});
			var reset = function () {
				input.classList.remove("error");
				errorMsg.innerHTML = "";
				errorMsg.classList.add("hide");
				validMsg.classList.add("hide");
			};

			// Validate on blur event
			input.addEventListener('blur', function () {
				reset();
				let num = window.igiti.getNumber(intlTelInputUtils.numberFormat.E164);
				if (input.value.trim()) {
					if (window.igiti.isValidNumber()) {
						isVaildMobilenumber = true;
						$('#HelpDesk_editView_fieldName_phone').val(num);
						validMsg.classList.remove("hide");
					} else {
						input.classList.add("error");
						var errorCode = window.igiti.getValidationError();
						errorMsg.innerHTML = errorMap[errorCode];
						errorMsg.classList.remove("hide");
						isVaildMobilenumber = false;
					}
				}
			});
		}
	},
});

