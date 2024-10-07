
Vtiger_Edit_Js("Equipment_Edit_Js", {}, {

	registerBasicEvents: function (container) {
		//Availavility for warranty
		$('[data-block="Availability For Warranty Period"]').addClass('hide');
		$('[data-block="afbwcpas"]').addClass('hide');
		$('[data-block="saatocp"]').addClass('hide');
		$('[data-block="daadcp"]').addClass('hide');
		$('[data-td="awp_commited_avl_m"]').addClass('hide');
		$('[data-td="afbwcpas_commited_avl_m"]').addClass('hide');
		$('[data-td="saatocp_commited_avl_m_w"]').addClass('hide');
		$('[data-td="saatocp_commited_avl_m_c"]').addClass('hide');

		//contract
		$('[data-td="total_year_cont"]').addClass('hide');
		$('[data-td="cont_start_date"]').addClass('hide');
		$('[data-td="cont_end_date"]').addClass('hide');
		$('[data-td="run_year_cont"]').addClass('hide');
		$('[data-td="eq_type_of_conrt"]').addClass('hide');

		//equipements
		$('[data-td="eq_available_for"]').addClass('hide');
		$('[data-td="maint_h_app_for_ac"]').addClass('hide');
		$('[data-td="eq_mon_available"]').addClass('hide');
		$('[data-td="eq_war_app_cp"]').addClass('hide');
		$('[data-td="eq_war_app_wp"]').addClass('hide');
		$('[data-td="eq_commited_avl"]').addClass('hide');

		this._super(container);
		this.DefaultAvailabilitydependency();
		this.Availabilitydependency();
		this.DefaulMonthlyAvailabilitydependency();
		this.MonthlyAvailabilitydependency();
		this.DefaultContractAvailabilitydependency();
		this.ContractAvailabilitydependency();
		this.DefaultEquipmentAvailabilitydependency();
		this.EquipmentAvailabilitydependency();
		this.AddContractLine();
	},

	Availabilitydependency: function () {
		$('select[data-fieldname="eq_commited_avl"]').change(function () {
			let val = $(this).val();
			if (val == 'Availability For Warranty Period') {
				$('[data-block="afbwcpas"]').addClass('hide');
				$('[data-block="saatocp"]').addClass('hide');
				$('[data-block="daadcp"]').addClass('hide');
				$('[data-block="Availability For Warranty Period"]').removeClass('hide');
			} else if (val == 'Availability for both Warranty & Contract Period are Same') {
				$('[data-block="afbwcpas"]').removeClass('hide');
				$('[data-block="Availability For Warranty Period"]').addClass('hide');
				$('[data-block="saatocp"]').addClass('hide');
				$('[data-block="daadcp"]').addClass('hide');
			} else if (val == 'Same availability applicable through out contract period') {
				$('[data-block="saatocp"]').removeClass('hide');
				$('[data-block="Availability For Warranty Period"]').addClass('hide');
				$('[data-block="afbwcpas"]').addClass('hide');
				$('[data-block="daadcp"]').addClass('hide');
			} else if (val == 'Different availability applicable during contract period') {
				$('[data-block="daadcp"]').removeClass('hide');
				$('[data-block="Availability For Warranty Period"]').addClass('hide');
				$('[data-block="afbwcpas"]').addClass('hide');
				$('[data-block="saatocp"]').addClass('hide');
			}
		});
	},

	MonthlyAvailabilitydependency: function () {
		$('input:radio[data-fieldname="eq_mon_available"]').change(function () {
			if ($(this).val() == 'Aplicable') {
				$('[data-td="awp_commited_avl_m"]').removeClass('hide');
				$('[data-td="afbwcpas_commited_avl_m"]').removeClass('hide');
				$('[data-td="saatocp_commited_avl_m_w"]').removeClass('hide');
				$('[data-td="saatocp_commited_avl_m_c"]').removeClass('hide');
				$('[data-td="daadcp_avail_mon_percent"]').removeClass('hide');
				$('[data-td="daadcp_commited_avl_m_w"]').removeClass('hide');
			} else if ($(this).val() == 'Not Applicable') {
				$('[data-td="awp_commited_avl_m"]').addClass('hide');
				$('[data-td="afbwcpas_commited_avl_m"]').addClass('hide');
				$('[data-td="saatocp_commited_avl_m_w"]').addClass('hide');
				$('[data-td="saatocp_commited_avl_m_c"]').addClass('hide');
				$('[data-td="daadcp_commited_avl_m_w"]').addClass('hide');
				$('[data-td="daadcp_avail_mon_percent"]').addClass('hide');

			}
		});
	},

	ContractAvailabilitydependency: function () {
		$('input:radio[data-fieldname="eq_contra_app"]').change(function (event) {
			if ($(this).val() == 'No') {
				let eqVal = $('input:radio[data-fieldname="eq_available_for"]:checked').val();
				if(eqVal == 'Both Warranty and Contract Period'){
					app.helper.showAlertNotification({
						'message': app.vtranslate('Please Change The Availability Applicable For Value')
					});
					event.preventDefault();
					$("#eq_contra_appYes").prop("checked", true);
					return;
				}
			}
			if ($(this).val() == 'Yes') {
				$('[data-td="total_year_cont"]').removeClass('hide');
				$('[data-td="cont_start_date"]').removeClass('hide');
				$('[data-td="cont_end_date"]').removeClass('hide');
				$('[data-td="run_year_cont"]').removeClass('hide');
				$('[data-td="eq_type_of_conrt"]').removeClass('hide');
				$('#eq_available_forBoth_Warranty_and_Contract_Period').parent().css('pointer-events', '');
				$('#eq_available_forBoth_Warranty_and_Contract_Period').css('background-color', '#FEFEFE');
				$("select option[value*='Availability for both Warranty & Contract Period are Same']").prop('disabled', false);
				$("select option[value*='Same availability applicable through out contract period']").prop('disabled', false);
				$("select option[value*='Different availability applicable during contract period']").prop('disabled', false);
			} else if ($(this).val() == 'No') {
				$('[data-td="total_year_cont"]').addClass('hide');
				$('[data-td="cont_start_date"]').addClass('hide');
				$('[data-td="cont_end_date"]').addClass('hide');
				$('[data-td="run_year_cont"]').addClass('hide');
				$('[data-td="eq_type_of_conrt"]').addClass('hide');
				$('#eq_available_forBoth_Warranty_and_Contract_Period').parent().css('pointer-events', 'none');
				$('#eq_available_forBoth_Warranty_and_Contract_Period').css('background-color', '#DEDEDE');
				$('.eq_commited_avl .select2-chosen').text('Availability for Warranty Period');
				$("select option[value*='Availability for both Warranty & Contract Period are Same']").prop('disabled', true);
				$("select option[value*='Same availability applicable through out contract period']").prop('disabled', true);
				$("select option[value*='Different availability applicable during contract period']").prop('disabled', true);
			} else {
				$("select option[value*='Availability for both Warranty & Contract Period are Same']").prop('disabled', true);
				$("select option[value*='Same availability applicable through out contract period']").prop('disabled', true);
				$("select option[value*='Different availability applicable during contract period']").prop('disabled', true);
			}
		});

		$('input:radio[data-fieldname="eq_available_for"]').change(function () {
			if ($(this).val() == 'Both Warranty and Contract Period') {
				$('[data-td="total_year_cont"]').removeClass('hide');
				$('[data-td="cont_start_date"]').removeClass('hide');
				$('[data-td="cont_end_date"]').removeClass('hide');
				$('[data-td="run_year_cont"]').removeClass('hide');
				$('[data-td="eq_type_of_conrt"]').removeClass('hide');
				$('#eq_available_forBoth_Warranty_and_Contract_Period').parent().css('pointer-events', '');
				$('#eq_available_forBoth_Warranty_and_Contract_Period').css('background-color', '#FEFEFE');
				$("select option[value*='Availability for both Warranty & Contract Period are Same']").prop('disabled', false);
				$("select option[value*='Same availability applicable through out contract period']").prop('disabled', false);
				$("select option[value*='Different availability applicable during contract period']").prop('disabled', false);
			} else if ($(this).val() == 'Only Warranty Period') {
				$('[data-td="total_year_cont"]').addClass('hide');
				$('[data-td="cont_start_date"]').addClass('hide');
				$('[data-td="cont_end_date"]').addClass('hide');
				$('[data-td="run_year_cont"]').addClass('hide');
				$('[data-td="eq_type_of_conrt"]').addClass('hide');
				$('#eq_available_forBoth_Warranty_and_Contract_Period').parent().css('pointer-events', 'none');
				$('#eq_available_forBoth_Warranty_and_Contract_Period').css('background-color', '#DEDEDE');
				$('.eq_commited_avl .select2-chosen').text('Availability for Warranty Period');
				$("select option[value*='Availability for both Warranty & Contract Period are Same']").prop('disabled', true);
				$("select option[value*='Same availability applicable through out contract period']").prop('disabled', true);
				$("select option[value*='Different availability applicable during contract period']").prop('disabled', true);
			} else {
				$("select option[value*='Availability for both Warranty & Contract Period are Same']").prop('disabled', true);
				$("select option[value*='Same availability applicable through out contract period']").prop('disabled', true);
				$("select option[value*='Different availability applicable during contract period']").prop('disabled', true);
			}
		});
	},

	EquipmentAvailabilitydependency: function () {
		let self = this;
		$('input:radio[data-fieldname="eq_available"]').change(function () {
			if ($(this).val() == 'Aplicable') {
				$('[data-td="eq_available_for"]').removeClass('hide');
				self.DefaultAvailabilitydependencyForAvail();
				$('[data-name="shift_hours"]').attr('required', true);
			} else if ($(this).val() == 'Not Applicable') {
				$('[data-td="eq_available_for"]').addClass('hide');
				$('[data-td="maint_h_app_for_ac"]').addClass('hide');
				$('[data-td="eq_mon_available"]').addClass('hide');
				$('[data-td="eq_war_app_cp"]').addClass('hide');
				$('[data-td="shift_hours"]').addClass('hide');
				$('[data-td="start_day_of_avail_calc"]').addClass('hide');
				$('[data-td="eq_war_app_wp"]').addClass('hide');
				$('[data-td="eq_commited_avl"]').addClass('hide');

				$('[data-block="Availability For Warranty Period"]').addClass('hide');
				$('[data-block="afbwcpas"]').addClass('hide');
				$('[data-block="saatocp"]').addClass('hide');
				$('[data-block="daadcp"]').addClass('hide');
			}
		});
		$('input:radio[data-fieldname="eq_available_for"]').change(function () {
			if ($(this).val() == 'Both Warranty and Contract Period') {
				$('[data-td="eq_war_app_cp"]').removeClass('hide');

				$('[data-td="maint_h_app_for_ac"]').removeClass('hide');
				$('[data-td="eq_mon_available"]').removeClass('hide');
				$('[data-td="eq_war_app_wp"]').removeClass('hide');
				$('[data-td="eq_commited_avl"]').removeClass('hide');

				$('[data-td="shift_hours"]').removeClass('hide');
				$('[data-fieldname="shift_hours"]').attr('required', true);
				$('[data-td="start_day_of_avail_calc"]').removeClass('hide');
			} else if ($(this).val() == 'Only Warranty Period') {
				$('[data-td="eq_war_app_cp"]').addClass('hide');

				$('[data-td="maint_h_app_for_ac"]').removeClass('hide');
				$('[data-td="eq_mon_available"]').removeClass('hide');
				$('[data-td="eq_war_app_wp"]').removeClass('hide');
				$('[data-td="eq_commited_avl"]').removeClass('hide');

				$('[data-td="shift_hours"]').removeClass('hide');
				$('[data-fieldname="shift_hours"]').attr('required', true);
				$('[data-td="start_day_of_avail_calc"]').removeClass('hide');
			}
		});
		this.DefaultAvailabilitydependencyForAvail();
	},

	DefaultAvailabilitydependencyForAvail: function () {
		let e_val = $('input:radio[data-fieldname="eq_available_for"]:checked').val();
		let availVal = $('input:radio[data-fieldname="eq_available"]:checked').val();
		if (availVal == 'Aplicable') {
			if (e_val == 'Both Warranty and Contract Period') {
				$('[data-td="eq_war_app_cp"]').removeClass('hide');
				$('[data-td="maint_h_app_for_ac"]').removeClass('hide');
				$('[data-td="eq_mon_available"]').removeClass('hide');
				$('[data-td="eq_war_app_wp"]').removeClass('hide');
				$('[data-td="eq_commited_avl"]').removeClass('hide');
				$('[data-td="shift_hours"]').removeClass('hide');
				$('[data-td="start_day_of_avail_calc"]').removeClass('hide');
				$('[data-fieldname="shift_hours"]').attr('required', true);
			} else if (e_val == 'Only Warranty Period') {
				$('[data-td="eq_war_app_cp"]').addClass('hide');
				$('[data-td="maint_h_app_for_ac"]').removeClass('hide');
				$('[data-td="eq_mon_available"]').removeClass('hide');
				$('[data-td="eq_war_app_wp"]').removeClass('hide');
				$('[data-td="eq_commited_avl"]').removeClass('hide');
				$('[data-td="shift_hours"]').removeClass('hide');
				$('[data-td="start_day_of_avail_calc"]').removeClass('hide');
				$('[data-name="shift_hours"]').attr('required', true);
			}
		}
	},

	DefaultAvailabilitydependency: function () {
		//by default show hide block based on dropdown values
		let val = $('select[data-fieldname="eq_commited_avl"]').val();
		if (val == 'Availability For Warranty Period') {
			$('[data-block="afbwcpas"]').addClass('hide');
			$('[data-block="saatocp"]').addClass('hide');
			$('[data-block="daadcp"]').addClass('hide');
			$('[data-block="Availability For Warranty Period"]').removeClass('hide');
		} else if (val == 'Availability for both Warranty & Contract Period are Same') {
			$('[data-block="afbwcpas"]').removeClass('hide');
			$('[data-block="Availability For Warranty Period"]').addClass('hide');
			$('[data-block="saatocp"]').addClass('hide');
			$('[data-block="daadcp"]').addClass('hide');
		} else if (val == 'Same availability applicable through out contract period') {
			$('[data-block="saatocp"]').removeClass('hide');
			$('[data-block="Availability For Warranty Period"]').addClass('hide');
			$('[data-block="afbwcpas"]').addClass('hide');
			$('[data-block="daadcp"]').addClass('hide');
		} else if (val == 'Different availability applicable during contract period') {
			$('[data-block="daadcp"]').removeClass('hide');
			$('[data-block="Availability For Warranty Period"]').addClass('hide');
			$('[data-block="afbwcpas"]').addClass('hide');
			$('[data-block="saatocp"]').addClass('hide');
		}
	},

	DefaulMonthlyAvailabilitydependency: function () {
		//by default show hide montly percentage fields
		let rval = $('input:radio[data-fieldname="eq_mon_available"]:checked').val();
		if (rval == 'Aplicable') {
			$('[data-td="awp_commited_avl_m"]').removeClass('hide');
			$('[data-td="afbwcpas_commited_avl_m"]').removeClass('hide');
			$('[data-td="saatocp_commited_avl_m_w"]').removeClass('hide');
			$('[data-td="saatocp_commited_avl_m_c"]').removeClass('hide');
			$('[data-td="daadcp_avail_mon_percent"]').removeClass('hide');
		}
		else if (rval == 'Not Applicable') {
			$('[data-td="awp_commited_avl_m"]').addClass('hide');
			$('[data-td="afbwcpas_commited_avl_m"]').addClass('hide');
			$('[data-td="saatocp_commited_avl_m_w"]').addClass('hide');
			$('[data-td="saatocp_commited_avl_m_c"]').addClass('hide');
			$('[data-td="daadcp_avail_mon_percent"]').addClass('hide');
		}
	},

	DefaultContractAvailabilitydependency: function () {
		//by default show hide contaract
		let c_val = $('input:radio[data-fieldname="eq_contra_app"]:checked').val();
		if (c_val == 'Yes') {
			$('[data-td="total_year_cont"]').removeClass('hide');
			$('[data-td="cont_start_date"]').removeClass('hide');
			$('[data-td="cont_end_date"]').removeClass('hide');
			$('[data-td="run_year_cont"]').removeClass('hide');
			$('[data-td="eq_type_of_conrt"]').removeClass('hide');
		} else if (c_val == 'No') {
			$('[data-td="total_year_cont"]').addClass('hide');
			$('[data-td="cont_start_date"]').addClass('hide');
			$('[data-td="cont_end_date"]').addClass('hide');
			$('[data-td="run_year_cont"]').addClass('hide');
			$('[data-td="eq_type_of_conrt"]').addClass('hide');
			$('#eq_available_forBoth_Warranty_and_Contract_Period').parent().css('pointer-events', 'none');
			$('#eq_available_forBoth_Warranty_and_Contract_Period').css('background-color', '#DEDEDE');
		} else {
			$('#eq_available_forBoth_Warranty_and_Contract_Period').parent().css('pointer-events', 'none');
			$('#eq_available_forBoth_Warranty_and_Contract_Period').css('background-color', '#DEDEDE');
		}
	},

	DefaultEquipmentAvailabilitydependency: function () {
		let e_val = $('input:radio[data-fieldname="eq_available"]:checked').val();
		if (e_val == 'Aplicable') {
			$('[data-td="eq_available_for"]').removeClass('hide');
			$('[data-td="maint_h_app_for_ac"]').removeClass('hide');
			$('[data-td="eq_mon_available"]').removeClass('hide');
			$('[data-td="eq_war_app_cp"]').removeClass('hide');
			$('[data-td="eq_war_app_wp"]').removeClass('hide');
			$('[data-td="eq_commited_avl"]').removeClass('hide');
			$('[data-fieldname="shift_hours"]').attr('required', true);
		} else if (e_val == 'Not Applicable') {
			$('[data-td="eq_available_for"]').addClass('hide');
			$('[data-td="maint_h_app_for_ac"]').addClass('hide');
			$('[data-td="eq_mon_available"]').addClass('hide');
			$('[data-td="eq_war_app_cp"]').addClass('hide');
			$('[data-td="eq_war_app_wp"]').addClass('hide');
			$('[data-td="eq_commited_avl"]').addClass('hide');
			$('[data-td="shift_hours"]').addClass('hide');
			$('[data-td="start_day_of_avail_calc"]').addClass('hide');
		} else {
			$('[data-td="eq_available_for"]').addClass('hide');
			$('[data-td="maint_h_app_for_ac"]').addClass('hide');
			$('[data-td="eq_mon_available"]').addClass('hide');
			$('[data-td="eq_war_app_cp"]').addClass('hide');
			$('[data-td="eq_war_app_wp"]').addClass('hide');
			$('[data-td="eq_commited_avl"]').addClass('hide');
			$('[data-td="shift_hours"]').addClass('hide');
			$('[data-td="start_day_of_avail_calc"]').addClass('hide');
		}
	},

	/**
   * Function to register AddContractLine
   */
	AddContractLine: function () {
		jQuery('#Equipment_editView_fieldName_cont_end_date').on('change', function () {

			var d1 = $('#Equipment_editView_fieldName_cont_start_date').datepicker('getDate');
			var d2 = $('#Equipment_editView_fieldName_cont_end_date').datepicker('getDate');

			if (d1 && d2) {
				var diff = Math.floor(d2.getTime() - d1.getTime());
				var day = 1000 * 60 * 60 * 24;
				var days = Math.floor(diff / day);
				var months = Math.floor(days / 31);
				var years = Math.floor(months / 12);

				if (months > 0) {
					years = years + 1;
				}
			}
			if (years <= 0) {
				return;
			}
			//alert if selected date grather than today date
			var varDate = new Date(d1); //dd-mm-YYYY
			var EDate = new Date(d2);
			var today = new Date();

			// if (varDate >= today) {
			// 	//Do something..
			// 	//alert("Startdate is grater than today date..!!!");
			// 	app.helper.showAlertNotification({ message: app.vtranslate('Startdate is grater than today date..!!!') });
			// 	window.onbeforeunload = null;
			// 	window.location.reload();
			// }

			if (varDate > EDate) {
				//Do something..
				//alert("Enddate is less than Start date..!!!");
				app.helper.showAlertNotification({ message: app.vtranslate('Enddate is less than Start date') });
				//$('#Timesheet_editView_fieldName_timesheet_tks_enddate').val(null);
				// window.onbeforeunload = null;
				// window.location.reload();
			}

			// Clear rows
			let otherLineItemTable = $(".lineitemTableContainer #lineItemTab2").clone();
			$('.lineitemTableContainerValuesHolder').html(otherLineItemTable);
			$('.lineitemTableContainerValuesHolder').addClass('hide');

			var trLength = $('body .lineitemTableContainer .appendRows tr').length;
			for (var i = 1; i < trLength; i++) {
				$('.lineitemTableContainer .appendRows tr:nth-child(2)').remove();
			}

			var startDate = moment(d1);
			var endDate = moment(d2);

			for (var i = 0; i <= years; ++i) {
				var date = new Date();

				if (i > 0) {
					var newdate = startDate.add(1, 'days');
				}
				else {
					var newdate = startDate.add(0, 'days');
				}

				var totalrow = years + 1;
				var contractyear = i;
				var j = i + 1;
				var html = $(".lineitemTableContainer .appendRows tr:first-child").clone();
				html.find('input[name^="Totalrow"]').val(totalrow);

				let yperVal = $(".lineitemTableContainerValuesHolder #daadcp_avail_percent" + i).val();
				let mperVal = $(".lineitemTableContainerValuesHolder #daadcp_avail_mon_percent" + i).val();
				html.find('input[name^="daadcp_avail_percent"]').val(yperVal);
				html.find('input[name^="daadcp_avail_mon_percent"]').val(mperVal);
				html.find('input[name^="daadcp_avail_sl_no"]').val(i);
				html.find('textarea[name^="daadcp_contra_lable"]').text(i + ' Year Contract');
				html.find('#row').removeClass('hide');
				html.find('#row').data('row-num', i);

				var idFields = new Array('daadcp_avail_percent', 'daadcp_avail_mon_percent',
					'daadcp_contra_lable', 'daadcp_avail_sl_no');
				for (var idIndex in idFields) {
					var elementId = idFields[idIndex];
					let expectedElementId = elementId + i;
					html.find('#' + elementId + '0').attr('id', expectedElementId)
						.filter('[name="' + elementId + '0' + '"]').attr('name', expectedElementId);

					if (i != 0) {
						html.find('[data-td="' + elementId + '"]').removeClass("tabletdhider");
					}
				}

				$('.lineitemTableContainer .appendRows').append(html);
				$('#daadcp_avail_sl_no0').attr('name', 'daadcp_avail_sl_no' + j);
				$('#daadcp_contra_lable0').attr('name', 'daadcp_contra_lable' + j);
				$('#daadcp_avail_percent0').attr('name', 'daadcp_avail_percent' + j);
				$('#daadcp_avail_mon_percent0').attr('name', 'daadcp_avail_mon_percent' + j);
			}
			$(".lineitemTableContainer .appendRows tr:first-child").remove();
		});
	},

});