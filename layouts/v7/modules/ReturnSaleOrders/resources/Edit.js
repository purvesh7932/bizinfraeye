Inventory_Edit_Js("ReturnSaleOrders_Edit_Js", {
	handleQtyValidations: function (event) {
		let lineItemsHolder = jQuery('#lineItemTab');
		let IS_SERV_MANAGER = jQuery('#IS_SERV_MANAGER').val();
		if (IS_SERV_MANAGER == true) {
			lineItemsHolder.find('tr.' + 'lineItemRow').each(function (index, domElement) {
				let lineItemRow = jQuery(domElement);
				let selectedId = lineItemRow.find('.selectedModuleId').val();
				let TotalQuantity = parseInt(jQuery('#RSO_CREATABLE_QTY').val());
				let CalQuantity = 0;
				$('.' + selectedId).each((index, element) => {
					element = jQuery(element);
					let eachLineQty = element.val();
					CalQuantity = parseInt(CalQuantity) + parseInt(eachLineQty);
				});
				if (isNaN(CalQuantity)) {
					CalQuantity = 0;
				}
				if (isNaN(TotalQuantity)) {
					TotalQuantity = 0;
				}
				if (CalQuantity != TotalQuantity) {
					app.helper.showErrorNotification({
						'message':
							`Quantities For Line Items Should Be Same as In SalesOrder`
					});
					event.preventDefault();
				}
			});
		}
	}
}, {

	lineItemDetectingClass: 'lineItemRow',

	dependencyStatusAndEvent: function () {
		let self = this;
		self.lineItemsHolder.find('tr.' + self.lineItemDetectingClass).each(function (index, domElement) {
			var lineItemRow = jQuery(domElement);
			let val = lineItemRow.find('select[data-extraname="action_taken_by_sm"]').val();
			if (val == 'Scrapped at Region') {
				$(this).closest('tr').find('.rso_part_status .select2-chosen').html('Scrapped at Region-Closed');
				$(this).closest('tr').find('.sto_no').addClass('hide');
				$(this).closest('tr').find('.div_or_ser_center').addClass('hide');
			} else if (val == 'Repaired at Region') {
				$(this).closest('tr').find('.rso_part_status .select2-chosen').html('Repaired at Region-Closed');
				$(this).closest('tr').find('.sto_no').addClass('hide');
				$(this).closest('tr').find('.div_or_ser_center').addClass('hide');
				$(this).closest('tr').find('#goods_consignment_noDivCla').removeClass('hide');
				$(this).closest('tr').find('#goods_rcived_dteDivCla').removeClass('hide');
			} else if ($(this).val() == 'Sent to division to Repair' || $(this).val() == 'Sent to division to Analysis' || $(this).val() == 'Sent to Service Centre for Repair') {
				$(this).closest('tr').find('.sto_no').removeClass('hide');
				$(this).closest('tr').find('.div_or_ser_center').removeClass('hide');
				$(this).closest('tr').find('#goods_consignment_noDivCla').removeClass('hide');
				$(this).closest('tr').find('#goods_rcived_dterDivCla').removeClass('hide');
			}

			let received_qty = lineItemRow.find('input[data-extraname="receivedvd_qty"]').val();
			let rowNum = lineItemRow.closest('tr').data('row-num');
			r_qty = parseInt(received_qty);

			//a
			let at_under_rep_atdiv = lineItemRow.find('input[data-extraname="at_under_rep_atdiv"]').val();
			let at_rep_a_sent_reg = lineItemRow.find('input[data-extraname="at_rep_a_sent_reg"]').val();
			let at_rep_a_kept_float = lineItemRow.find('input[data-extraname="at_rep_a_kept_float"]').val();
			let at_scraped_at_dev = lineItemRow.find('input[data-extraname="at_scraped_at_dev"]').val();

			at_under_rep_atdiv = parseInt(at_under_rep_atdiv);
			at_rep_a_sent_reg = parseInt(at_rep_a_sent_reg);
			at_rep_a_kept_float = parseInt(at_rep_a_kept_float);
			at_scraped_at_dev = parseInt(at_scraped_at_dev);

			let allsum = at_under_rep_atdiv + at_rep_a_sent_reg + at_rep_a_kept_float + at_scraped_at_dev;
			let a_all_sum = parseInt(allsum);

			jQuery.validator.addMethod("positive", function () {
				if (r_qty < a_all_sum) {
					return false;
				} else {
					$("#at_under_rep_atdiv" + rowNum).removeClass('input-error');
					$("#at_rep_a_sent_reg" + rowNum).removeClass('input-error');
					$("#at_rep_a_kept_float" + rowNum).removeClass('input-error');
					$("#at_scraped_at_dev" + rowNum).removeClass('input-error');
					return true;
				}

			}, jQuery.validator.format(app.vtranslate('DSM total qty should not be gather than received qty')));


			//b
			let ana_done_div_qty = lineItemRow.find('input[data-extraname="ana_done_div_qty"]').val();
			let und_fail_ana_div_qty = lineItemRow.find('input[data-extraname="und_fail_ana_div_qty"]').val();
			let asent_to_ven_qty = lineItemRow.find('input[data-extraname="asent_to_ven_qty"]').val();
			let scm_dismant_unprogre = lineItemRow.find('input[data-extraname="scm_dismant_unprogre"]').val();
			let scm_repaired_qty = lineItemRow.find('input[data-extraname="scm_repaired_qty"]').val();
			let scm_beyond_eco_rep_qty = lineItemRow.find('input[data-extraname="scm_beyond_eco_rep_qty"]').val();
			let scm_item_aw_for_rep = lineItemRow.find('input[data-extraname="scm_item_aw_for_rep"]').val();
			let scm_senttoreg_worep = lineItemRow.find('input[data-extraname="scm_senttoreg_worep"]').val();
			let scm_rep_and_sent_back_qty = lineItemRow.find('input[data-extraname="scm_rep_and_sent_back_qty"]').val();

			let ballsum = ana_done_div_qty + und_fail_ana_div_qty + asent_to_ven_qty + scm_dismant_unprogre + scm_repaired_qty + scm_beyond_eco_rep_qty + scm_beyond_eco_rep_qty + scm_senttoreg_worep + scm_rep_and_sent_back_qty;
			let b_all_sum = parseInt(ballsum);

			jQuery.validator.addMethod("positive", function () {
				if (r_qty < b_all_sum) {
					return false;
				} else {
					$("#ana_done_div_qty" + rowNum).removeClass('input-error');
					$("#und_fail_ana_div_qty" + rowNum).removeClass('input-error');
					$("#asent_to_ven_qty" + rowNum).removeClass('input-error');
					$("#scm_dismant_unprogre" + rowNum).removeClass('input-error');
					$("#scm_repaired_qty" + rowNum).removeClass('input-error');
					$("#scm_beyond_eco_rep_qty" + rowNum).removeClass('input-error');
					$("#scm_item_aw_for_rep" + rowNum).removeClass('input-error');
					$("#scm_senttoreg_worep" + rowNum).removeClass('input-error');
					$("#scm_rep_and_sent_back_qty" + rowNum).removeClass('input-error');
					return true;
				}

			}, jQuery.validator.format(app.vtranslate('Present total qty should not be gather than received qty')));
		});
	},
	updateRowNumberForRow: function (lineItemRow, expectedSequenceNumber, currentSequenceNumber) {
		if (typeof currentSequenceNumber == 'undefined') {
			//by default there will zero current sequence number
			currentSequenceNumber = 0;
		}

		let fildNamesOfCustFields = jQuery('#fildNamesOfCustFields').val();
		if (fildNamesOfCustFields == null || fildNamesOfCustFields == undefined) {
			fildNamesOfCustFields = '[]';
		}
		let fildNamesOfCustFieldsOther = jQuery('#fildNamesOfCustFieldsOther').val();
		if (fildNamesOfCustFieldsOther == null || fildNamesOfCustFieldsOther == undefined) {
			fildNamesOfCustFieldsOther = '[]';
		}

		let fildNamesOfCustFieldsOther1 = jQuery('#fildNamesOfCustFieldsSub1').val();
		if (fildNamesOfCustFieldsOther1 == null || fildNamesOfCustFieldsOther1 == undefined) {
			fildNamesOfCustFieldsOther1 = '[]';
		}

		let fildNamesOfCustFieldsOther2 = jQuery('#fildNamesOfCustFieldsSub2').val();
		if (fildNamesOfCustFieldsOther2 == null || fildNamesOfCustFieldsOther2 == undefined) {
			fildNamesOfCustFieldsOther2 = '[]';
		}

		fildNamesOfCustFields = JSON.parse(fildNamesOfCustFields);
		fildNamesOfCustFieldsOther = JSON.parse(fildNamesOfCustFieldsOther);

		fildNamesOfCustFieldsOther1 = JSON.parse(fildNamesOfCustFieldsOther1);
		fildNamesOfCustFieldsOther2 = JSON.parse(fildNamesOfCustFieldsOther2);

		var idFields = new Array('productName', 'subproduct_ids', 'hdnProductId', 'purchaseCost', 'margin', 'productName_other', 'qty_other',
			'comment', 'qty', 'listPrice', 'discount_div', 'discount_type', 'hdnProductId_other',
			'discount_amount', 'lineItemType', 'searchIcon', 'netPrice', 'subprod_names',
			'productTotal', 'discountTotal', 'totalAfterDiscount', 'taxTotal', 'sr_action_one', 'sr_action_two', 'sr_replace_action');
		if (fildNamesOfCustFields.length > 0) {
			idFields = idFields.concat(fildNamesOfCustFields);
		}
		if (fildNamesOfCustFieldsOther && fildNamesOfCustFieldsOther.length > 0) {
			idFields = idFields.concat(fildNamesOfCustFieldsOther);
		}

		if (fildNamesOfCustFieldsOther1 && fildNamesOfCustFieldsOther1.length > 0) {
			idFields = idFields.concat(fildNamesOfCustFieldsOther1);
		}
		if (fildNamesOfCustFieldsOther2 && fildNamesOfCustFieldsOther2.length > 0) {
			idFields = idFields.concat(fildNamesOfCustFieldsOther2);
		}

		var classFields = new Array('taxPercentage');
		//To handle variable tax ids
		for (var classIndex in classFields) {
			var className = classFields[classIndex];
			jQuery('.' + className, lineItemRow).each(function (index, domElement) {
				var idString = domElement.id
				//remove last character which will be the row number
				idFields.push(idString.slice(0, (idString.length - 1)));
			});
		}

		var expectedRowId = 'row' + expectedSequenceNumber;
		for (var idIndex in idFields) {
			var elementId = idFields[idIndex];
			var actualElementId = elementId + currentSequenceNumber;
			var expectedElementId = elementId + expectedSequenceNumber;
			lineItemRow.find('#' + actualElementId).attr('id', expectedElementId)
				.filter('[name="' + actualElementId + '"]').attr('name', expectedElementId);
		}

		var nameFields = new Array('discount', 'purchaseCost', 'margin');
		for (var nameIndex in nameFields) {
			var elementName = nameFields[nameIndex];
			var actualElementName = elementName + currentSequenceNumber;
			var expectedElementName = elementName + expectedSequenceNumber;
			lineItemRow.find('[name="' + actualElementName + '"]').attr('name', expectedElementName);
		}

		lineItemRow.attr('id', expectedRowId).attr('data-row-num', expectedSequenceNumber);
		lineItemRow.find('input.rowNumber').val(expectedSequenceNumber);

		return lineItemRow;
	},

	initializeLineItemRowCustomFields: function (lineItemRow, rowNum) {
		var lineItemType = lineItemRow.find('input.lineItemType').val();
		let fildNamesOfCustFields = jQuery('#fildNamesOfCustPickFieldsInfo').val();
		if (fildNamesOfCustFields == null || fildNamesOfCustFields == undefined) {
			fildNamesOfCustFields = '[]';
		}
		fildNamesOfCustFields = JSON.parse(fildNamesOfCustFields);
		let pickLength = fildNamesOfCustFields.length;
		for (let i = 0; i < pickLength; i++) {
			this.customLineItemFields[fildNamesOfCustFields[i]] = 'picklist';
		}
		for (var cfName in this.customLineItemFields) {
			var elementName = cfName + rowNum;
			var element = lineItemRow.find('[name="' + elementName + '"]');

			var cfDataType = this.customLineItemFields[cfName];
			if (cfDataType == 'picklist' || cfDataType == 'multipicklist') {

				(cfDataType == 'multipicklist') && (element = lineItemRow.find('[name="' + elementName + '[]"]'));

				var picklistValues = element.data('productPicklistValues');
				(lineItemType == 'Services') && (picklistValues = element.data('servicePicklistValues'));
				var options = '';
				(cfDataType == 'picklist') && (options = '<option value="">' + app.vtranslate('JS_SELECT_OPTION') + '</option>');

				for (var picklistName in picklistValues) {
					var pickListValue = picklistValues[picklistName];
					options += '<option value="' + picklistName + '">' + pickListValue + '</option>';
				}
				$("#" + cfName + "0 option").each(function () {
					if ($(this).val() != "") {
						options += '<option value="' + $(this).val() + '">' + $(this).val() + '</option>';
					}
				})
				element.html(options);
				element.addClass('select2');
			}

			var defaultValueInfo = this.customFieldsDefaultValues[cfName];
			if (defaultValueInfo) {
				var defaultValue = defaultValueInfo;
				if (typeof defaultValueInfo == 'object') {
					defaultValue = defaultValueInfo['productFieldDefaultValue'];
					(lineItemType == 'Services') && (defaultValue = defaultValueInfo['serviceFieldDefaultValue'])
				}

				if (cfDataType === 'multipicklist') {
					if (defaultValue.length > 0) {
						defaultValue = defaultValue.split(" |##| ");
						var setDefaultValue = function (picklistElement, values) {
							for (var index in values) {
								var picklistVal = values[index];
								picklistElement.find('option[value="' + picklistVal + '"]').prop('selected', true);
							}
						}(element, defaultValue)
					}
				} else {
					element.val(defaultValue);
				}
			} else {
				defaultValue = '';
				element.val(defaultValue);
			}
		}

		return lineItemRow;
	},

	handleDefaultDependencyINRSO: function () {
		let self = this;
		self.lineItemsHolder.find('tr.' + self.lineItemDetectingClass).each(function (index, domElement) {
			let parentRow = jQuery(domElement);
			let val = parentRow.find('select[data-extraname="action_taken_by_sm"]').val();
			if (val == 'Sent to Vendor') {
				parentRow.find('#vendor_responseDivCla').removeClass('hide');
				parentRow.find('#vendor_nameDivCla').removeClass('hide');
				parentRow.find('.sto_no').addClass('hide');
				parentRow.find('.div_or_ser_center').addClass('hide');
			} else {
				parentRow.find('#vendor_responseDivCla').addClass('hide');
				parentRow.find('#vendor_nameDivCla').addClass('hide');
			}

			if (val == 'Scrapped at Region') {
				jQuery('.rso_part_status option[value="Scrapped at Region-Closed"]', parentRow).attr("selected", "selected").trigger('change');
				jQuery('.rso_part_status option[value="Scrapped at Region-Closed"]', parentRow).attr("selected", "selected").trigger('change');
				parentRow.find('#div_or_ser_centerDivCla').addClass('hide');
				parentRow.find('#sto_noDivCla').addClass('hide');
			} else if (val == 'Repaired at Region') {
				jQuery('.rso_part_status option[value="Repaired at Region-Closed"]', parentRow).attr("selected", "selected").trigger('change');
				parentRow.find('#sto_noDivCla').addClass('hide');
				parentRow.find('#div_or_ser_centerDivCla').addClass('hide');
				parentRow.find('#goods_consignment_noDivCla').removeClass('hide');
				parentRow.find('#goods_rcived_dteDivCla').removeClass('hide');
			} else if (val == 'Sent to division to Repair' ||
				val == 'Sent to division to Analysis' ||
				val == 'Sent to Service Centre for Repair') {
				jQuery('.rso_part_status option[value="Return Sale Order Created"]', parentRow).attr("selected", "selected").trigger('change');
				parentRow.find('#sto_noDivCla').removeClass('hide');
				parentRow.find('.sto_no').removeClass('hide');
				parentRow.find('#div_or_ser_centerDivCla').removeClass('hide');
				parentRow.find('.div_or_ser_center').removeClass('hide');
				parentRow.find('#goods_consignment_noDivCla').removeClass('hide');
				parentRow.find('#goods_rcived_dterDivCla').removeClass('hide');
			}
		});
	},
	dependencyWarrantyApplicable: function () {
		$('select[data-extraname="action_taken_by_sm"]').change(function () {
			let val = $(this).val();
			let parentRow = $(this).closest('tr');
			if (val == 'Sent to Vendor') {
				parentRow.find('#vendor_responseDivCla').removeClass('hide');
				parentRow.find('#vendor_nameDivCla').removeClass('hide');
				parentRow.find('.sto_no').addClass('hide');
				parentRow.find('.div_or_ser_center').addClass('hide');
			} else {
				parentRow.find('#vendor_responseDivCla').addClass('hide');
				parentRow.find('#vendor_nameDivCla').addClass('hide');
			}

			if (val == 'Scrapped at Region') {
				jQuery('.rso_part_status option[value="Scrapped at Region-Closed"]', parentRow).attr("selected", "selected").trigger('change');
				jQuery('.rso_part_status option[value="Scrapped at Region-Closed"]', parentRow).attr("selected", "selected").trigger('change');
				parentRow.find('#div_or_ser_centerDivCla').addClass('hide');
				parentRow.find('#sto_noDivCla').addClass('hide');
			} else if (val == 'Repaired at Region') {
				jQuery('.rso_part_status option[value="Repaired at Region-Closed"]', parentRow).attr("selected", "selected").trigger('change');
				parentRow.find('#sto_noDivCla').addClass('hide');
				parentRow.find('#div_or_ser_centerDivCla').addClass('hide');
				parentRow.find('#goods_consignment_noDivCla').removeClass('hide');
				parentRow.find('#goods_rcived_dteDivCla').removeClass('hide');
			} else if (val == 'Sent to division to Repair' ||
				val == 'Sent to division to Analysis' ||
				val == 'Sent to Service Centre for Repair') {
				jQuery('.rso_part_status option[value="Return Sale Order Created"]', parentRow).attr("selected", "selected").trigger('change');
				parentRow.find('#sto_noDivCla').removeClass('hide');
				parentRow.find('.sto_no').removeClass('hide');
				parentRow.find('#div_or_ser_centerDivCla').removeClass('hide');
				parentRow.find('.div_or_ser_center').removeClass('hide');
				parentRow.find('#goods_consignment_noDivCla').removeClass('hide');
				parentRow.find('#goods_rcived_dterDivCla').removeClass('hide');
			}
		});

		$('td .a_qty').click(function () {
			let element = $(this);
			let parent = element.closest('table').closest('tr');
			let rowNum = parent.find('input.rowNumber').val();
			let received_qty = $("#receivedvd_qty" + rowNum).val();

			let r_qty = parseInt(received_qty);

			//a
			let at_under_rep_atdiv = $("#at_under_rep_atdiv" + rowNum).val();
			let at_rep_a_sent_reg = $("#at_rep_a_sent_reg" + rowNum).val();
			let at_rep_a_kept_float = $("#at_rep_a_kept_float" + rowNum).val();
			let at_scraped_at_dev = $("#at_scraped_at_dev" + rowNum).val();

			at_under_rep_atdiv = parseInt(at_under_rep_atdiv);
			at_rep_a_sent_reg = parseInt(at_rep_a_sent_reg);
			at_rep_a_kept_float = parseInt(at_rep_a_kept_float);
			at_scraped_at_dev = parseInt(at_scraped_at_dev);

			let allsum = at_under_rep_atdiv + at_rep_a_sent_reg + at_rep_a_kept_float + at_scraped_at_dev;
			let a_all_sum = parseInt(allsum);

			jQuery.validator.addMethod("positive", function () {
				if (r_qty < a_all_sum) {
					return false;
				}
				else {
					$("#at_under_rep_atdiv" + rowNum).removeClass('input-error');
					$("#at_rep_a_sent_reg" + rowNum).removeClass('input-error');
					$("#at_rep_a_kept_float" + rowNum).removeClass('input-error');
					$("#at_scraped_at_dev" + rowNum).removeClass('input-error');
					return true;
				}

			}, jQuery.validator.format(app.vtranslate('DSM total qty should not be gather than received qty')));
		});

		$('td .b_qty').click(function () {
			let element = $(this);
			let parent = element.closest('table').closest('tr');
			let rowNum = parent.find('input.rowNumber').val();
			let received_qty = $("#receivedvd_qty" + rowNum).val();

			let r_qty = parseInt(received_qty);

			//b
			let ana_done_div_qty = $("#ana_done_div_qty" + rowNum).val();
			let und_fail_ana_div_qty = $("#und_fail_ana_div_qty" + rowNum).val();
			let asent_to_ven_qty = $("#asent_to_ven_qty" + rowNum).val();
			let scm_dismant_unprogre = $("#scm_dismant_unprogre" + rowNum).val();
			let scm_repaired_qty = $("#scm_repaired_qty" + rowNum).val();
			let scm_beyond_eco_rep_qty = $("#scm_beyond_eco_rep_qty" + rowNum).val();
			let scm_item_aw_for_rep = $("#scm_item_aw_for_rep" + rowNum).val();
			let scm_senttoreg_worep = $("#scm_senttoreg_worep" + rowNum).val();
			let scm_rep_and_sent_back_qty = $("#scm_rep_and_sent_back_qty" + rowNum).val();

			ana_done_div_qty = parseInt(ana_done_div_qty);
			und_fail_ana_div_qty = parseInt(und_fail_ana_div_qty);
			asent_to_ven_qty = parseInt(asent_to_ven_qty);
			scm_dismant_unprogre = parseInt(scm_dismant_unprogre);
			scm_repaired_qty = parseInt(scm_repaired_qty);
			scm_beyond_eco_rep_qty = parseInt(scm_beyond_eco_rep_qty);
			scm_item_aw_for_rep = parseInt(scm_item_aw_for_rep);
			scm_senttoreg_worep = parseInt(scm_senttoreg_worep);
			scm_rep_and_sent_back_qty = parseInt(scm_rep_and_sent_back_qty);

			let ballsum = ana_done_div_qty + und_fail_ana_div_qty + asent_to_ven_qty + scm_dismant_unprogre + scm_repaired_qty + scm_beyond_eco_rep_qty + scm_item_aw_for_rep + scm_senttoreg_worep + scm_rep_and_sent_back_qty;
			let b_all_sum = parseInt(ballsum);

			jQuery.validator.addMethod("positive", function () {
				if (r_qty < b_all_sum) {
					return false;
				}
				else {
					$("#ana_done_div_qty" + rowNum).removeClass('input-error');
					$("#und_fail_ana_div_qty" + rowNum).removeClass('input-error');
					$("#asent_to_ven_qty" + rowNum).removeClass('input-error');
					$("#scm_dismant_unprogre" + rowNum).removeClass('input-error');
					$("#scm_repaired_qty" + rowNum).removeClass('input-error');
					$("#scm_beyond_eco_rep_qty" + rowNum).removeClass('input-error');
					$("#scm_item_aw_for_rep" + rowNum).removeClass('input-error');
					$("#scm_senttoreg_worep" + rowNum).removeClass('input-error');
					$("#scm_rep_and_sent_back_qty" + rowNum).removeClass('input-error');
					return true;
				}

			}, jQuery.validator.format(app.vtranslate('Present total qty should not be gather than received qty')));
		});
	},

	MakeNonEditFieldsDisabled: function (container) {
		let noneditableKeys = Array('ext_app_num_noti', 'project_name', 'parent_line_itemid');
		let noneditableKeysLength = noneditableKeys.length;
		for (let i = 0; i < noneditableKeysLength; i++) {
			$("input[name='" + noneditableKeys[i] + "']").attr('readonly', 'readonly').css('background-color', '#eeeeee !important');
		}
	},

	registerBasicEvents: function (container) {
		this._super(container);
		// this.dependencyStatusAndEvent();
		this.dependencyWarrantyApplicable();
		this.handleDefaultDependencyINRSO();
		jQuery(".duplicate").on('click', function (event) {
			let element = $(this);
			let parent = element.closest('tr');
			let rowNum = parent.find('input.rowNumber').val();
			let recordId = $("#hdnProductId" + rowNum).val();
			let dataUrl = "index.php?module=Inventory&action=GetTaxes&record=" + recordId + "&currency_id=" + jQuery('#currency_id option:selected').val() + "&sourceModule=" + app.getModuleName();
			app.request.get({ 'url': dataUrl }).then(
				function (error, data) {
					if (error == null) {
						let objKeys = Object.keys(data['0']);
						data['0'][objKeys['0']]['qty'] = $("#qty" + rowNum).val();
						data['0'][objKeys['0']]['validated_part_no'] = $("#validated_part_no" + rowNum).val();
						jQuery('#addProduct').trigger('click', data);
						app.helper.hideProgress();
					}
				},
				function (error, err) {
				}
			);
			event.preventDefault();
		});
		this.MakeNonEditFieldsDisabled();
		this.handleSTOValidation();
	},
	handleSTOValidation: function () {
		jQuery('input[data-extraname="sto_no"]').on('input', function (event) {
			let value = event.target.value;
			if (value.length == 10) {
				var self = this;
				let dataOf = {};
				dataOf['sto_no'] = value;
				dataOf['module'] = 'ReturnSaleOrders';
				dataOf['action'] = 'validateSTONumber';
				app.helper.showProgress();
				app.request.get({ data: dataOf }).then(function (err, response) {
					if (err == null) {
						if (response.validSTONumber == true) {
							$(self).data("stoValidated", true);
						} else {
							$(self).data("stoValidated", false);
						}
					}
					app.helper.hideProgress();
				});
			}
		});
	},

	mapResultsToFields: function (parentRow, responseData) {
		var lineItemNameElment = jQuery('input.productName', parentRow);
		var referenceModule = this.getLineItemSetype(parentRow);
		var lineItemRowNumber = parentRow.data('rowNum');
		for (var id in responseData) {
			$('#sto_qty' + lineItemRowNumber).addClass(id);
			var recordId = id;
			var recordData = responseData[id];
			var selectedName = recordData.name;
			var unitPrice = recordData.listprice;
			var listPriceValues = recordData.listpricevalues;
			var taxes = recordData.taxes;
			var purchaseCost = recordData.purchaseCost;
			this.setPurchaseCostValue(parentRow, purchaseCost);
			var imgSrc = recordData.imageSource;
			this.setImageTag(parentRow, imgSrc);
			if (referenceModule == 'Products') {
				parentRow.data('quantity-in-stock', recordData.quantityInStock);
			}
			var description = recordData.description;
			jQuery('input.selectedModuleId', parentRow).val(recordId);
			jQuery('input.lineItemType', parentRow).val(referenceModule);
			lineItemNameElment.val(selectedName);
			lineItemNameElment.attr('disabled', 'disabled');
			jQuery('input.listPrice', parentRow).val(unitPrice);
			var currencyId = this.currencyElement.val();
			var listPriceValuesJson = JSON.stringify(listPriceValues);
			if (listPriceValues && typeof listPriceValues[currencyId] != 'undefined') {
				this.formatListPrice(parentRow, listPriceValues[currencyId]);
				this.lineItemRowCalculations(parentRow);
			}
			jQuery('input.listPrice', parentRow).attr('list-info', listPriceValuesJson);
			jQuery('input.listPrice', parentRow).data('baseCurrencyId', recordData.baseCurrencyId);
			jQuery('textarea.lineItemCommentBox', parentRow).val(description);
			var taxUI = this.getTaxDiv(taxes, parentRow);
			jQuery('.taxDivContainer', parentRow).html(taxUI);

			//Take tax percentage according to tax-region, if region is selected.
			var selectedRegionId = this.regionElement.val();
			if (selectedRegionId != 0) {
				var taxPercentages = jQuery('.taxPercentage', parentRow);
				jQuery.each(taxPercentages, function (index1, taxDomElement) {
					var taxPercentage = jQuery(taxDomElement);
					var regionsList = taxPercentage.data('regionsList');
					var value = regionsList['default'];
					if (selectedRegionId && regionsList[selectedRegionId]) {
						value = regionsList[selectedRegionId];
					}
					taxPercentage.val(parseFloat(value));
				});
			}

			if (this.isIndividualTaxMode()) {
				parentRow.find('.productTaxTotal').removeClass('hide')
			} else {
				parentRow.find('.productTaxTotal').addClass('hide')
			}
		}
		if (referenceModule == 'Products') {
			this.loadSubProducts(parentRow);
		}

		jQuery('.qty', parentRow).trigger('focusout');
		if (recordData.qty != undefined && recordData.qty != null) {
			jQuery('input.qty', parentRow).val(recordData.qty);
			jQuery('textarea[name=validated_part_no' + lineItemRowNumber + ']', parentRow).val(recordData.validated_part_no);
			jQuery('textarea.lineItemCommentBox', parentRow).attr('readonly', 'readonly');
			jQuery('.rso_part_status option[value="Return Sale Order Created"]', parentRow).attr("selected", "selected").trigger('change');
		}
	},
});