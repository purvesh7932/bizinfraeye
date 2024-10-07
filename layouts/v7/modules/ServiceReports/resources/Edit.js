Inventory_Edit_Js("ServiceReports_Edit_Js", {

	zeroDiscountType: 'zero',
	percentageDiscountType: 'percentage',
	directAmountDiscountType: 'amount',

	individualTaxType: 'individual',
	groupTaxType: 'group',
	// handleSubmitClickAndClose : function (e) {
	// 	if ($("select[name='eq_sta_aft_act_taken']").val() != 'On Road') {
	// 		app.helper.showAlertNotification({
	// 			'message': 'Equipment Status After Action Taken Should Be ' +
	// 			'On Road Before Submitting Report'
	// 		});
	// 		e.preventDefault();
	// 	} else {
	// 		document.getElementById('is_submitted').value = 1;
	// 	}
    // },
	handleMandatoryDependency: function (e) {
		let mandatoryKeys = $("input[name='MANDATORYFIELDS']").data('value');
		mandatoryKeys.forEach(element => {
			if (element != "") {
				$('#ServiceReports_editView_fieldName_' + element).attr('required', true);
				$('select[name="' + element + '"]').data('rule-required', true);
				$('#' + element + '_display').attr('required', true);
				$('[name="' + element + '"]').data('rule-required', true);
			}
		});
	},
	deMandatoryDependency: function (e) {
		let mandatoryKeys = $("input[name='MANDATORYFIELDS']").data('value');
		mandatoryKeys.forEach(element => {
			if (element != "") {
				$('#ServiceReports_editView_fieldName_' + element).attr('required', false);
				$('select[name="' + element + '"]').data('rule-required', false);
				$('#' + element + '_display').attr('required', false);
				$('[name="' + element + '"]').data('rule-required', false);
			}
		});
	},
	lineItemPopOverTemplate: '<div class="popover lineItemPopover" role="tooltip"><div class="arrow"></div>\n\
                                <h3 class="popover-title"></h3>\n\
								<div class="popover-content"></div>\n\
									<div class="modal-footer lineItemPopupModalFooter">\n\
										<center>\n\
										<button class="btn btn-success popoverButton" type="button"><strong>'+ app.vtranslate('JS_LBL_SAVE') + '</strong></button>\n\
										<a href="#" class="popoverCancel" type="reset">'+ app.vtranslate('JS_LBL_CANCEL') + '</a>\n\
										</center>\n\
									</div>\n\
                                </div>'

}, {

	//Will have the mapping of address fields based on the modules
	addressFieldsMapping: {
		'Contacts': {
			'bill_street': 'mailingstreet',
			'ship_street': 'otherstreet',
			'bill_pobox': 'mailingpobox',
			'ship_pobox': 'otherpobox',
			'bill_city': 'mailingcity',
			'ship_city': 'othercity',
			'bill_state': 'mailingstate',
			'ship_state': 'otherstate',
			'bill_code': 'mailingzip',
			'ship_code': 'otherzip',
			'bill_country': 'mailingcountry',
			'ship_country': 'othercountry'
		},

		'Accounts': {
			'bill_street': 'bill_street',
			'ship_street': 'ship_street',
			'bill_pobox': 'bill_pobox',
			'ship_pobox': 'ship_pobox',
			'bill_city': 'bill_city',
			'ship_city': 'ship_city',
			'bill_state': 'bill_state',
			'ship_state': 'ship_state',
			'bill_code': 'bill_code',
			'ship_code': 'ship_code',
			'bill_country': 'bill_country',
			'ship_country': 'ship_country'
		},

		'Vendors': {
			'bill_street': 'street',
			'ship_street': 'street',
			'bill_pobox': 'pobox',
			'ship_pobox': 'pobox',
			'bill_city': 'city',
			'ship_city': 'city',
			'bill_state': 'state',
			'ship_state': 'state',
			'bill_code': 'postalcode',
			'ship_code': 'postalcode',
			'bill_country': 'country',
			'ship_country': 'country'
		},
		'Leads': {
			'bill_street': 'lane',
			'ship_street': 'lane',
			'bill_pobox': 'pobox',
			'ship_pobox': 'pobox',
			'bill_city': 'city',
			'ship_city': 'city',
			'bill_state': 'state',
			'ship_state': 'state',
			'bill_code': 'code',
			'ship_code': 'code',
			'bill_country': 'country',
			'ship_country': 'country'
		}
	},

	//Address field mapping between modules specific for billing and shipping
	addressFieldsMappingBetweenModules: {
		'AccountsBillMap': {
			'bill_street': 'bill_street',
			'bill_pobox': 'bill_pobox',
			'bill_city': 'bill_city',
			'bill_state': 'bill_state',
			'bill_code': 'bill_code',
			'bill_country': 'bill_country'
		},
		'AccountsShipMap': {
			'ship_street': 'ship_street',
			'ship_pobox': 'ship_pobox',
			'ship_city': 'ship_city',
			'ship_state': 'ship_state',
			'ship_code': 'ship_code',
			'ship_country': 'ship_country'
		},
		'ContactsBillMap': {
			'bill_street': 'mailingstreet',
			'bill_pobox': 'mailingpobox',
			'bill_city': 'mailingcity',
			'bill_state': 'mailingstate',
			'bill_code': 'mailingzip',
			'bill_country': 'mailingcountry'
		},
		'ContactsShipMap': {
			'ship_street': 'otherstreet',
			'ship_pobox': 'otherpobox',
			'ship_city': 'othercity',
			'ship_state': 'otherstate',
			'ship_code': 'otherzip',
			'ship_country': 'othercountry'
		},
		'LeadsBillMap': {
			'bill_street': 'lane',
			'bill_pobox': 'pobox',
			'bill_city': 'city',
			'bill_state': 'state',
			'bill_code': 'code',
			'bill_country': 'country'
		},
		'LeadsShipMap': {
			'ship_street': 'lane',
			'ship_pobox': 'pobox',
			'ship_city': 'city',
			'ship_state': 'state',
			'ship_code': 'code',
			'ship_country': 'country'
		}

	},

	//Address field mapping within module
	addressFieldsMappingInModule: {
		'bill_street': 'ship_street',
		'bill_pobox': 'ship_pobox',
		'bill_city': 'ship_city',
		'bill_state': 'ship_state',
		'bill_code': 'ship_code',
		'bill_country': 'ship_country'
	},

	hasBlockedHMR: false,
	hasBlockedKM: false,
	dummyLineItemRow: false,
	dummyLineItemRow1: false,
	lineItemsHolder: false,
	lineItemsHolder1: false,
	lineItemsHolder2: false,
	numOfLineItems: false,
	numOfLineItems1: false,
	customLineItemFields: false,
	customFieldsDefaultValues: false,
	numOfCurrencyDecimals: false,
	taxTypeElement: false,
	regionElement: false,
	currencyElement: false,
	finalDiscountUIEle: false,
	conversionRateEle: false,
	overAllDiscountEle: false,
	preTaxTotalEle: false,

	//final calculation elements
	netTotalEle: false,
	finalDiscountTotalEle: false,
	finalTaxEle: false,
	finalDiscountEle: false,

	chargesTotalEle: false,
	chargesContainer: false,
	chargeTaxesContainer: false,
	chargesTotalDisplay: false,
	chargeTaxesTotal: false,
	deductTaxesTotal: false,
	adjustmentEle: false,
	adjustmentTypeEles: false,
	grandTotal: false,
	groupTaxContainer: false,
	dedutTaxesContainer: false,


	lineItemDetectingClass: 'lineItemRow',

	registerBasicEvents: function (container) {
		this._super(container);
		// Bydefault hide fields
		//1
		$('[data-td="vis_chk_ext_dam"]').addClass("hide");
		$('[data-td="vis_chk_ext_dam_img"]').addClass("hide");
		$('[data-td="vis_hyd_air_dam_img"]').addClass("hide");
		$('[data-td="vis_chk_hyd_air"]').addClass("hide");
		$('[data-td="vis_chk_lub_rem"]').addClass("hide");
		$('[data-td="vis_lub_los_img"]').addClass("hide");
		$('[data-td="vis_chk_oil_rem"]').addClass("hide");
		$('[data-td="vis_oil_lev_img"]').addClass("hide");
		$('[data-td="vis_hyd_wrk_los_img"]').addClass("hide");
		$('[data-td="vis_chk_wrk_los"]').addClass("hide");
		$('[data-td="vis_chk_painting_doc"]').addClass("hide");
		$('[data-td="vis_chk_painting_rem"]').addClass("hide");

		//2
		let reportType = $("select[name='sr_ticket_type']").val();
		let purpose = $('select[name="tck_det_purpose"]').val();
		if (reportType != "SERVICE FOR SPARES PURCHASED" && purpose != "WARRANTY CLAIM FOR SUB ASSEMBLY / OTHER SPARE PARTS") {
			$('[data-td="genchk_oil_pressure"]').addClass("hide");
			$('[data-td="genchk_oil_temperature"]').addClass("hide");
		}
		$('[data-td="genchk_coolant_temperature"]').addClass("hide");
		$('[data-td="genchk_oil_pre_tr"]').addClass("hide");
		$('[data-td="genchk_oil_tr_tem"]').addClass("hide");
		$('[data-td="genchk_brk_oil_tem"]').addClass("hide");
		$('[data-td="genchk_air_pressure"]').addClass("hide");
		$('[data-td="genchk_motor"]').addClass("hide");
		$('[data-td="genchk_battery_voltage"]').addClass("hide");
		$('[data-td="genchk_hi_volt_ele_system"]').addClass("hide");
		$('[data-td="genchk_auto_electrical_system"]').addClass("hide");
		$('[data-td="genchk_field_switch"]').addClass("hide");
		$('[data-td="genchk_transformer"]').addClass("hide");
		$('[data-td="genchk_suspension"]').addClass("hide");
		$('[data-td="genchk_cylinders"]').addClass("hide");
		$('[data-td="genchk_oil_cooler"]').addClass("hide");
		$('[data-td="breack_ticket_id"]').addClass("hide");
		$('[data-td="at_brkdn_sr_req"]').addClass("hide");
		$('[data-td="genchk_pumps"]').addClass("hide");

		//3
		$('[data-td="restoration_date"]').addClass("hide");
		$('[data-td="restoration_time"]').addClass("hide");
		$('[data-td="off_on_account_of"]').addClass("hide");
		$('[data-td="remarks_for_offroad"]').addClass("hide");

		// this.registerAddProductService();
		this.registerProductAndServiceSelector();
		this.registerLineItemEvents();
		this.checkLineItemRow();
		this.registerSubmitEvent();
		this.makeLineItemsSortable();
		this.registerLineItemAutoComplete();
		this.registerReferenceSelectionEvent(this.getForm());
		this.registerPopoverCancelEvent();
		// ---------
		this.phoneCountryCode();
		this.aggregate_warranty_applicalple();
		this.handleOtherDependecy();
		this.next();
		this.InitialBlocking();
		this.ECDT_HideorShow();
		this.uppercase();
		this.optionDamage1();
		this.optionPartsAffected();
		this.optionSystemAffected();
		this.comparePopupHandler();
		this.dependencyStatusAndEvent();
		this.editShowVendorsName();
		this.DefaultActionTakenDropdown();
		this.ActionTakenDropdown();
		this.DefaultEDdependency();
		this.External_damage_showAndHide();
		this.DefaultHAdependency();
		this.HydraulicAndAirLeakages();
		this.DefaultLdependency();
		this.Lubrication();
		this.DefaultOdependency();
		this.OilLevels();
		this.DefaulWLHdependency();
		this.WorklooseningofHardwares();
		this.DefaultPENdependency();
		this.CheckPainting();
		this.DefaultKMRdependency();
		this.KMRdependency();
		this.DefaultKMRdependencyIninstalationSubass();
		this.KMRdependencyIninstalationSubass();

		let type = $('select[name="sr_ticket_type"]').val();
		if (type != 'SERVICE FOR SPARES PURCHASED') {
			this.Defaultengine();
			this.engine();
			this.Defaulttransmission();
			this.transmission();
		}
		this.DefaultbreakdownYesorNo();
		this.breakdownYesorNo();
		this.dependencyVendorNameShowOrHide();
		this.dependencyWarrantyApplicable();
		this.disableSpecificFieldTypes();
		this.registerFileElementChangeEvent1(this.getForm());
		this.registerFileElementChangeEvent2(this.getForm());
		this.registerFileElementChangeEvent3(this.getForm());
		this.registerFileElementChangeEvent4(this.getForm());
		this.registerFileElementChangeEvent5(this.getForm());
	},

	init: function () {
		this._super();
		this.initializeVariables();
	},

	initializeVariables: function () {
		this.dummyLineItemRow = jQuery('#row0');
		this.dummyLineItemRow1 = jQuery('#anorow0');
		this.lineItemsHolder = jQuery('#lineItemTab');
		this.lineItemsHolder1 = jQuery('#lineItemTab1');
		this.lineItemsHolder2 = jQuery('#lineItemTab2');
		this.numOfLineItems = this.lineItemsHolder.find('.' + this.lineItemDetectingClass).length;
		this.numOfLineItems1 = this.lineItemsHolder1.find('.' + this.lineItemDetectingClass).length;
		if (typeof jQuery('#customFields').val() == 'undefined') {
			this.customLineItemFields = [];
		} else {
			this.customLineItemFields = JSON.parse(jQuery('#customFields').val());
		}

		if (typeof jQuery('#customFieldsDefaultValues').val() == 'undefined') {
			this.customFieldsDefaultValues = [];
		} else {
			this.customFieldsDefaultValues = JSON.parse(jQuery('#customFieldsDefaultValues').val());
		}


		this.numOfCurrencyDecimals = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		this.taxTypeElement = jQuery('#taxtype');
		this.regionElement = jQuery('#region_id');
		this.currencyElement = jQuery('#currency_id');

		this.netTotalEle = jQuery('#netTotal');
		this.finalDiscountTotalEle = jQuery('#discountTotal_final');
		this.finalTaxEle = jQuery('#tax_final');
		this.finalDiscountUIEle = jQuery('#finalDiscountUI');
		this.finalDiscountEle = jQuery('#finalDiscount');
		this.conversionRateEle = jQuery('#conversion_rate');
		this.overAllDiscountEle = jQuery('#overallDiscount');
		this.chargesTotalEle = jQuery('#chargesTotal');
		this.preTaxTotalEle = jQuery('#preTaxTotal');
		this.chargesContainer = jQuery('#chargesBlock')
		this.chargesTotalDisplay = jQuery('#chargesTotalDisplay');
		this.chargeTaxesContainer = jQuery('#chargeTaxesBlock');
		this.chargeTaxesTotal = jQuery('#chargeTaxTotalHidden');
		this.deductTaxesTotal = jQuery('#deductTaxesTotalAmount');
		this.adjustmentEle = jQuery('#adjustment');
		this.adjustmentTypeEles = jQuery('input[name="adjustmentType"]');
		this.grandTotal = jQuery('#grandTotal');
		this.groupTaxContainer = jQuery('#group_tax_div');
		this.dedutTaxesContainer = jQuery('#deductTaxesBlock');
		this.WarrantableHide();

		this.numOfCurrencyDecimals = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		this.taxTypeElement = jQuery('#taxtype');
		this.regionElement = jQuery('#region_id');
		this.currencyElement = jQuery('#currency_id');

		this.netTotalEle = jQuery('#netTotal');
		this.finalDiscountTotalEle = jQuery('#discountTotal_final');
		this.finalTaxEle = jQuery('#tax_final');
		this.finalDiscountUIEle = jQuery('#finalDiscountUI');
		this.finalDiscountEle = jQuery('#finalDiscount');
		this.conversionRateEle = jQuery('#conversion_rate');
		this.overAllDiscountEle = jQuery('#overallDiscount');
		this.chargesTotalEle = jQuery('#chargesTotal');
		this.preTaxTotalEle = jQuery('#preTaxTotal');
		this.chargesContainer = jQuery('#chargesBlock')
		this.chargesTotalDisplay = jQuery('#chargesTotalDisplay');
		this.chargeTaxesContainer = jQuery('#chargeTaxesBlock');
		this.chargeTaxesTotal = jQuery('#chargeTaxTotalHidden');
		this.deductTaxesTotal = jQuery('#deductTaxesTotalAmount');
		this.adjustmentEle = jQuery('#adjustment');
		this.adjustmentTypeEles = jQuery('input[name="adjustmentType"]');
		this.grandTotal = jQuery('#grandTotal');
		this.groupTaxContainer = jQuery('#group_tax_div');
		this.dedutTaxesContainer = jQuery('#deductTaxesBlock');
		this.registerDeleteLineItemEvent1();
		this.registerClearLineItemSelection1();
	},

	/**
	 * Function that is used to get the line item container
	 * @return : jQuery object
	 */
	getLineItemContentsContainer: function () {
		if (this.lineItemContentsContainer == false) {
			this.setLineItemContainer(jQuery('#lineItemTab'));
		}
		return this.lineItemContentsContainer;
	},

	/**
	 * Function which will copy the address details
	 */
	copyAddressDetails: function (data, container, addressMap) {
		var self = this;
		var sourceModule = data['source_module'];
		var noAddress = true;
		var errorMsg;

		this.getRecordDetails(data).then(
			function (data) {
				var response = data;
				if (typeof addressMap != "undefined") {
					var result = response['data'];
					for (var key in addressMap) {
						if (result[addressMap[key]] != "") {
							noAddress = false;
							break;
						}
					}
					if (noAddress) {
						if (sourceModule == "Accounts") {
							errorMsg = 'JS_SELECTED_ACCOUNT_DOES_NOT_HAVE_AN_ADDRESS';
						} else if (sourceModule == "Contacts") {
							errorMsg = 'JS_SELECTED_CONTACT_DOES_NOT_HAVE_AN_ADDRESS';
						} else if (sourceModule == "Leads") {
							errorMsg = 'JS_SELECTED_LEAD_DOES_NOT_HAVE_AN_ADDRESS';
						}
						app.helper.showErrorNotification({ 'message': app.vtranslate(errorMsg) });
					} else {
						self.mapAddressDetails(addressMap, result, container);
					}
				} else {
					self.mapAddressDetails(self.addressFieldsMapping[sourceModule], response['data'], container);
					if (sourceModule == "Accounts") {
						container.find('.accountAddress').attr('checked', 'checked');
					} else if (sourceModule == "Contacts") {
						container.find('.contactAddress').attr('checked', 'checked');
					}
				}
			},
			function (error, err) {

			});
	},

	/**
	 * Function which will copy the address details of the selected record
	 */
	mapAddressDetails: function (addressDetails, result, container) {
		for (var key in addressDetails) {
			container.find('[name="' + key + '"]').val(result[addressDetails[key]]);
			container.find('[name="' + key + '"]').trigger('change');
		}
	},

	/**
	 * Function to copy address between fields
	 * @param strings which accepts value as either odd or even
	 */
	copyAddress: function (swapMode) {
		var self = this;
		var formElement = this.getForm();
		var addressMapping = this.addressFieldsMappingInModule;
		if (swapMode == "false") {
			for (var key in addressMapping) {
				var fromElement = formElement.find('[name="' + key + '"]');
				var toElement = formElement.find('[name="' + addressMapping[key] + '"]');
				toElement.val(fromElement.val());
			}
		} else if (swapMode) {
			var swappedArray = self.swapObject(addressMapping);
			for (var key in swappedArray) {
				var fromElement = formElement.find('[name="' + key + '"]');
				var toElement = formElement.find('[name="' + swappedArray[key] + '"]');
				toElement.val(fromElement.val());
			}
			toElement.val(fromElement.val());
		}
	},

	/**
	 * Function to swap array
	 * @param Array that need to be swapped
	 */
	swapObject: function (objectToSwap) {
		var swappedArray = {};
		var newKey, newValue;
		for (var key in objectToSwap) {
			newKey = objectToSwap[key];
			newValue = key;
			swappedArray[newKey] = newValue;
		}
		return swappedArray;
	},

	getLineItemNextRowNumber: function () {
		return ++this.numOfLineItems;
	},
	getLineItemNextRowNumber1: function () {
		return ++this.numOfLineItems1;
	},

	formatListPrice: function (lineItemRow, listPriceValue) {
		var listPrice = parseFloat(listPriceValue).toFixed(this.numOfCurrencyDecimals);
		lineItemRow.find('.listPrice').val(listPrice);
		return this;
	},

	getLineItemRowNumber: function (itemRow) {
		return parseInt(itemRow.attr('data-row-num'));
	},

	/**
	 * Function which gives quantity value
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getQuantityValue: function (lineItemRow) {
		return parseFloat(lineItemRow.find('.qty').val());
	},

	/**
	 * Function which will get the value of cost price
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getPurchaseCostValue: function (lineItemRow) {
		var rowNum = this.getLineItemRowNumber(lineItemRow);
		return parseFloat(jQuery('#purchaseCost' + rowNum).val());
	},

	/**
	 * Function which will set the cost price
	 * @params : lineItemRow - row which represents the line item
	 * @params : cost price
	 * @return : current instance;
	 */
	setPurchaseCostValue: function (lineItemRow, purchaseCost) {
		if (isNaN(purchaseCost)) {
			purchaseCost = 0;
		}
		var rowNum = this.getLineItemRowNumber(lineItemRow);
		jQuery('#purchaseCost' + rowNum).val(purchaseCost);
		var quantity = this.getQuantityValue(lineItemRow);
		var updatedPurchaseCost = parseFloat(quantity) * parseFloat(purchaseCost);
		lineItemRow.find('[name="purchaseCost' + rowNum + '"]').val(updatedPurchaseCost);
		lineItemRow.find('.purchaseCost').text(updatedPurchaseCost);
		return this;
	},

	/**
	 * Function which will set the image
	 * @params : lineItemRow - row which represents the line item
	 * @params : image source
	 * @return : current instance;
	 */
	setImageTag: function (lineItemRow, imgSrc) {
		var imgTag = '<img src=' + imgSrc + ' height="42" width="42">';
		lineItemRow.find('.lineItemImage').html(imgTag);
		return this;
	},

	/**
	 * Function which will give me list price value
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getListPriceValue: function (lineItemRow) {
		return parseFloat(lineItemRow.find('.listPrice').val());
	},

	setListPriceValue: function (lineItemRow, listPriceValue) {
		var listPrice = parseFloat(listPriceValue).toFixed(this.numOfCurrencyDecimals);
		lineItemRow.find('.listPrice').val(listPrice);
		return this;
	},

	/**
	 * Function which will set the line item total value excluding tax and discount
	 * @params : lineItemRow - row which represents the line item
	 *			 lineItemTotalValue - value which has line item total  (qty*listprice)
	 * @return : current instance;
	 */
	setLineItemTotal: function (lineItemRow, lineItemTotalValue) {
		lineItemRow.find('.productTotal').text(lineItemTotalValue);
		return this;
	},

	/**
	 * Function which will get the value of line item total (qty*listprice)
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getLineItemTotal: function (lineItemRow) {
		var lineItemTotal = this.getLineItemTotalElement(lineItemRow).text();
		if (lineItemTotal)
			return parseFloat(lineItemTotal);
		return 0;
	},

	/**
	 * Function which will get the line item total element
	 * @params : lineItemRow - row which represents the line item
	 * @return : jQuery element
	 */
	getLineItemTotalElement: function (lineItemRow) {
		return lineItemRow.find('.productTotal');
	},

	/**
	 * Function which will set the discount total value for line item
	 * @params : lineItemRow - row which represents the line item
	 *			 discountValue - discount value
	 * @return : current instance;
	 */
	setDiscountTotal: function (lineItemRow, discountValue) {
		jQuery('.discountTotal', lineItemRow).text(discountValue);
		return this;
	},

	/**
	 * Function which will get the value of total discount
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getDiscountTotal: function (lineItemRow) {
		var element = jQuery('.discountTotal', lineItemRow);
		if (element.length > 0) {
			return parseFloat(element.text());
		}
		return 0;
	},

	/**
	 * Function which will set the total after discount value
	 * @params : lineItemRow - row which represents the line item
	 *			 totalAfterDiscountValue - total after discount value
	 * @return : current instance;
	 */
	setTotalAfterDiscount: function (lineItemRow, totalAfterDiscountValue) {
		lineItemRow.find('.totalAfterDiscount').text(totalAfterDiscountValue);
		return this;
	},

	/**
	 * Function which will get the value of total after discount
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getTotalAfterDiscount: function (lineItemRow) {
		var element = lineItemRow.find('.totalAfterDiscount');
		if (element.length > 0) {
			return parseFloat(element.text());
		}
		return this.getLineItemTotal(lineItemRow);
	},

	/**
	 * Function which will set the tax total
	 * @params : lineItemRow - row which represents the line item
	 *			 taxTotal -  tax total
	 * @return : current instance;
	 */
	setLineItemTaxTotal: function (lineItemRow, taxTotal) {
		jQuery('.productTaxTotal', lineItemRow).text(taxTotal);
		return this;
	},

	/**
	 * Function which will get the value of total tax
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getLineItemTaxTotal: function (lineItemRow) {
		var lineItemTax = jQuery('.productTaxTotal', lineItemRow).text();
		if (lineItemTax)
			return parseFloat(lineItemTax);
		return 0;
	},

	/**
	 * Function which will set the line item net price
	 * @params : lineItemRow - row which represents the line item
	 *			 lineItemNetPriceValue -  line item net price value
	 * @return : current instance;
	 */
	setLineItemNetPrice: function (lineItemRow, lineItemNetPriceValue) {
		lineItemRow.find('.netPrice').text(lineItemNetPriceValue);
		return this;
	},

	/**
	 * Function which will get the value of net price
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getLineItemNetPrice: function (lineItemRow) {
		return this.formatLineItemNetPrice(lineItemRow.find('.netPrice'));
	},

	formatLineItemNetPrice: function (netPriceEle) {
		var lineItemNetPrice = netPriceEle.text();
		if (lineItemNetPrice)
			return parseFloat(lineItemNetPrice);
		return 0;
	},

	setNetTotal: function (netTotalValue) {
		this.netTotalEle.text(netTotalValue);
		return this;
	},

	getNetTotal: function () {
		var netTotal = this.netTotalEle.text();
		if (netTotal)
			return parseFloat(netTotal);
		return 0;
	},

	/**
	 * Function to set the final discount total
	 */
	setFinalDiscountTotal: function (finalDiscountValue) {
		this.finalDiscountTotalEle.text(finalDiscountValue);
		return this;
	},

	getFinalDiscountTotal: function () {
		var discountTotal = this.finalDiscountTotalEle.text();
		if (discountTotal)
			return parseFloat(discountTotal);
		return 0;
	},

	setGroupTaxTotal: function (groupTaxTotalValue) {
		this.finalTaxEle.text(groupTaxTotalValue);
	},

	getGroupTaxTotal: function () {
		var groupTax = this.finalTaxEle.text();
		if (groupTax)
			return parseFloat(groupTax);
		return 0
	},

	getChargesTotal: function () {
		var chargesElement = this.chargesTotalEle;
		if (chargesElement.length <= 0) {
			return 0;
		}
		return parseFloat(chargesElement.val());
	},

	getChargeTaxesTotal: function () {
		var taxElement = this.chargeTaxesTotal;
		if (taxElement.length <= 0) {
			return 0;
		}
		return parseFloat(taxElement.val());
	},

	getDeductTaxesTotal: function () {
		var taxElement = this.deductTaxesTotal;
		if (taxElement.length <= 0) {
			return 0;
		}
		return parseFloat(taxElement.text());
	},

	/**
	 * Function to set the pre tax total
	 */
	setPreTaxTotal: function (preTaxTotalValue) {
		this.preTaxTotalEle.text(preTaxTotalValue);
		return this;
	},

	/**
	 * Function to get the pre tax total
	 */
	getPreTaxTotal: function () {
		if (this.preTaxTotalEle.length > 0) {
			return parseFloat(this.preTaxTotalEle.text())
		}
	},

	/**
	 * Function which will set the margin
	 * @params : lineItemRow - row which represents the line item
	 * @params : margin
	 * @return : current instance;
	 */
	setMarginValue: function (lineItemRow, margin) {
		var rowNum = this.getLineItemRowNumber(lineItemRow);
		lineItemRow.find('[name="margin' + rowNum + '"]').val(margin);
		lineItemRow.find('.margin').text(margin);
		return this;
	},

	getAdjustmentValue: function () {
		return parseFloat(this.adjustmentEle.val());
	},

	isAdjustMentAddType: function () {
		var adjustmentSelectElement = this.adjustmentTypeEles;
		var selectionOption;
		adjustmentSelectElement.each(function () {
			if (jQuery(this).is(':checked')) {
				selectionOption = jQuery(this);
			}
		})
		if (typeof selectionOption != "undefined") {
			if (selectionOption.val() == '+') {
				return true;
			}
		}
		return false;
	},

	isAdjustMentDeductType: function () {
		var adjustmentSelectElement = this.adjustmentTypeEles;
		var selectionOption;
		adjustmentSelectElement.each(function () {
			if (jQuery(this).is(':checked')) {
				selectionOption = jQuery(this);
			}
		})
		if (typeof selectionOption != "undefined") {
			if (selectionOption.val() == '-') {
				return true;
			}
		}
		return false;
	},

	setGrandTotal: function (grandTotalValue) {
		this.grandTotal.text(grandTotalValue);
		return this;
	},

	getGrandTotal: function () {
		var grandTotal = this.grandTotal.text();
		if (grandTotal)
			return parseFloat(grandTotal);
		return 0;
	},

	isIndividualTaxMode: function () {
		return (this.taxTypeElement.val() == Inventory_Edit_Js.individualTaxType) ? true : false;
	},

	isGroupTaxMode: function () {
		return (this.taxTypeElement.val() == Inventory_Edit_Js.groupTaxType) ? true : false;
	},

	/**
	 * Function which will give the closest line item row element
	 * @return : jQuery object
	 */
	getClosestLineItemRow: function (element) {
		return element.closest('tr.' + this.lineItemDetectingClass);
	},

	isProductSelected: function (element) {
		var parentRow = element.closest('tr');
		var productField = parentRow.find('.productName');
		var response = productField.valid();
		return response;
	},

	checkLineItemRow: function () {
		var numRows = this.lineItemsHolder.find('.' + this.lineItemDetectingClass).length;
		if (numRows > 0) {
			this.showLineItemsDeleteIcon();
		} else {
			this.hideLineItemsDeleteIcon();
		}
		let anotherNumRows = this.lineItemsHolder1.find('.' + this.lineItemDetectingClass).length;
		if (anotherNumRows > 0) {
			this.showLineItemsDeleteIconAnother();
		} else {
			this.hideLineItemsDeleteIconAnother();
		}
	},

	showLineItemsDeleteIcon: function () {
		this.lineItemsHolder.find('.deleteRow').show();
	},

	hideLineItemsDeleteIcon: function () {
		this.lineItemsHolder.find('.deleteRow').hide();
	},
	showLineItemsDeleteIconAnother: function () {
		this.lineItemsHolder1.find('.deleteRow').show();
	},

	hideLineItemsDeleteIconAnother: function () {
		this.lineItemsHolder1.find('.deleteRow').hide();
	},

	clearLineItemDetails: function (parentElem) {
		var lineItemRow = this.getClosestLineItemRow(parentElem);
		jQuery('[id*="purchaseCost"]', lineItemRow).val('0');
		jQuery('.lineItemImage', lineItemRow).html('');
		jQuery('input.selectedModuleId', lineItemRow).val('');
		jQuery('input.listPrice', lineItemRow).val('0');
		jQuery('.lineItemCommentBox', lineItemRow).val('');
		jQuery('.subProductIds', lineItemRow).val('');
		jQuery('.subProductsContainer', lineItemRow).html('');
		this.quantityChangeActions(lineItemRow);
	},

	saveProductCount: function () {
		jQuery('#totalProductCount').val(this.lineItemsHolder.find('tr.' + this.lineItemDetectingClass).length);
	},
	saveProductCount1: function () {
		jQuery('#totalProductCount1').val(this.lineItemsHolder1.find('tr.' + this.lineItemDetectingClass).length);
	},

	saveProductCount2: function () {
		jQuery('#totalProductCount2').val(this.lineItemsHolder2.find('tr.' + this.lineItemDetectingClass).length);
	},

	saveSubTotalValue: function () {
		jQuery('#subtotal').val(this.getNetTotal());
	},

	saveTotalValue: function () {
		jQuery('#total').val(this.getGrandTotal());
	},

	/**
	 * Function to save the pre tax total value
	 */
	savePreTaxTotalValue: function () {
		jQuery('#pre_tax_total').val(this.getPreTaxTotal());
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
		let fildNamesOfCustFieldsOther1 = jQuery('#fildNamesOfCustFieldsOther1').val();
		if (fildNamesOfCustFieldsOther1 == null || fildNamesOfCustFieldsOther1 == undefined) {
			fildNamesOfCustFieldsOther1 = '[]';
		}
		fildNamesOfCustFields = JSON.parse(fildNamesOfCustFields);
		fildNamesOfCustFieldsOther = JSON.parse(fildNamesOfCustFieldsOther);
		fildNamesOfCustFieldsOther1 = JSON.parse(fildNamesOfCustFieldsOther1);
		var idFields = new Array('productName', 'subproduct_ids', 'hdnProductId', 'purchaseCost', 'margin', 'productName_other', 'qty_other',
			'comment', 'qty', 'listPrice', 'discount_div', 'discount_type', 'hdnProductId_other',
			'discount_amount', 'lineItemType', 'searchIcon', 'netPrice', 'subprod_names',
			'productTotal', 'discountTotal', 'totalAfterDiscount', 'taxTotal', 'comment_other');
		if (fildNamesOfCustFields.length > 0) {
			idFields = idFields.concat(fildNamesOfCustFields);
		}
		if (fildNamesOfCustFieldsOther.length > 0) {
			idFields = idFields.concat(fildNamesOfCustFieldsOther);
		}
		if (fildNamesOfCustFieldsOther1.length > 0) {
			idFields = idFields.concat(fildNamesOfCustFieldsOther1);
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
		let type = $('select[name="sr_ticket_type"]').val();
		if (type == 'PERIODICAL MAINTENANCE') {
			lineItemRow.find('[data-fieldname="' + 'sad_dof0' + '"]').attr('data-fieldname', 'sad_dof'+expectedSequenceNumber);
		}
		lineItemRow.attr('id', expectedRowId).attr('data-row-num', expectedSequenceNumber);
		lineItemRow.find('input.rowNumber').val(expectedSequenceNumber);

		return lineItemRow;
	},

	updateLineItemElementByOrder: function () {
		var self = this;
		var checkedDiscountElements = {};
		var lineItems = this.lineItemsHolder.find('tr.' + this.lineItemDetectingClass);
		lineItems.each(function (index, domElement) {
			var lineItemRow = jQuery(domElement);
			var actualRowId = lineItemRow.attr('id');

			var discountContianer = lineItemRow.find('div.discountUI');
			var element = discountContianer.find('input.discounts').filter(':checked');
			checkedDiscountElements[actualRowId] = element.data('discountType');
		});

		lineItems.each(function (index, domElement) {
			var lineItemRow = jQuery(domElement);
			var expectedRowIndex = (index + 1);
			var expectedRowId = 'row' + expectedRowIndex;
			var actualRowId = lineItemRow.attr('id');
			if (expectedRowId != actualRowId) {
				var actualIdComponents = actualRowId.split('row');
				self.updateRowNumberForRow(lineItemRow, expectedRowIndex, actualIdComponents[1]);

				var discountContianer = lineItemRow.find('div.discountUI');
				discountContianer.find('input.discounts').each(function (index1, discountElement) {
					var discountElement = jQuery(discountElement);
					var discountType = discountElement.data('discountType');
					if (discountType == checkedDiscountElements[actualRowId]) {
						discountElement.attr('checked', true);
					}
				});
			}
		});
	},

	/**
	 * Function which will initialize line items custom fields with default values if exists 
	 */
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
		// this.customLineItemFields = {'sr_action_one' : 'picklist','sr_action_two' : 'picklist','sr_replace_action': 'picklist'};
		for (var cfName in this.customLineItemFields) {
			var elementName = cfName + rowNum;
			var element = lineItemRow.find('[name="' + elementName + '"]');

			var cfDataType = this.customLineItemFields[cfName];
			if (cfDataType == 'picklist' || cfDataType == 'multipicklist') {
				(cfDataType == 'multipicklist') && (element = lineItemRow.find('[name="' + elementName + '[]"]'));

				var picklistValues = element.data('productpicklistvalues');
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

	initializeLineItemRowCustomFieldsOther: function (lineItemRow, rowNum) {
		var lineItemType = lineItemRow.find('input.lineItemType').val();
		let fildNamesOfCustFields = jQuery('#fildNamesOfCustPickFieldsInfoOther').val();
		if (fildNamesOfCustFields == null || fildNamesOfCustFields == undefined) {
			fildNamesOfCustFields = '[]';
		}
		fildNamesOfCustFields = JSON.parse(fildNamesOfCustFields);
		let pickLength = fildNamesOfCustFields.length;
		for (let i = 0; i < pickLength; i++) {
			this.customLineItemFields[fildNamesOfCustFields[i]] = 'picklist';
		}
		// this.customLineItemFields = {'sr_action_one' : 'picklist','sr_action_two' : 'picklist','sr_replace_action': 'picklist'};
		for (var cfName in this.customLineItemFields) {
			var elementName = cfName + rowNum;
			var element = lineItemRow.find('[name="' + elementName + '"]');

			var cfDataType = this.customLineItemFields[cfName];
			if (cfDataType == 'picklist' || cfDataType == 'multipicklist') {
				(cfDataType == 'multipicklist') && (element = lineItemRow.find('[name="' + elementName + '[]"]'));

				var picklistValues = element.data('productpicklistvalues');
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

	getLineItemSetype: function (row) {
		return row.find('.lineItemType').val();
	},

	getNewLineItem: function (params) {
		var currentTarget = params.currentTarget;
		var itemType = currentTarget.data('moduleName');
		var newRow = this.dummyLineItemRow.clone(true).removeClass('hide').addClass(this.lineItemDetectingClass).removeClass('lineItemCloneCopy');
		var individualTax = this.isIndividualTaxMode();
		if (individualTax) {
			newRow.find('.individualTaxContainer').removeClass('hide');
		}
		newRow.find('.lineItemPopup').filter(':not([data-module-name="' + itemType + '"])').remove();
		newRow.find('.lineItemType').val(itemType);
		var newRowNum = this.getLineItemNextRowNumber();
		this.updateRowNumberForRow(newRow, newRowNum);
		this.initializeLineItemRowCustomFields(newRow, newRowNum);
		return newRow
	},

	getNewLineItem1: function (params) {
		var currentTarget = params.currentTarget;
		var itemType = currentTarget.data('moduleName');
		var newRow = this.dummyLineItemRow1.clone(true).removeClass('hide').addClass(this.lineItemDetectingClass).removeClass('lineItemCloneCopy');
		var individualTax = this.isIndividualTaxMode();
		if (individualTax) {
			newRow.find('.individualTaxContainer').removeClass('hide');
		}
		newRow.find('.lineItemPopup').filter(':not([data-module-name="' + itemType + '"])').remove();
		newRow.find('.lineItemType').val(itemType);
		var newRowNum = this.getLineItemNextRowNumber1();
		this.updateRowNumberForRow(newRow, newRowNum);
		this.initializeLineItemRowCustomFieldsOther(newRow, newRowNum);
		return newRow
	},

	/**
	 * Function which will calculate line item total excluding discount and tax
	 * @params : lineItemRow - element which will represent lineItemRow
	 */
	calculateLineItemTotal: function (lineItemRow) {
		var quantity = this.getQuantityValue(lineItemRow);
		var listPrice = this.getListPriceValue(lineItemRow);
		var lineItemTotal = parseFloat(quantity) * parseFloat(listPrice);
		this.setLineItemTotal(lineItemRow, lineItemTotal.toFixed(this.numOfCurrencyDecimals));
	},

	/**
	 * Function which will calculate discount for the line item
	 * @params : lineItemRow - element which will represent lineItemRow
	 */
	calculateDiscountForLineItem: function (lineItemRow) {
		var discountContianer = lineItemRow.find('div.discountUI');
		var element = discountContianer.find('input.discounts').filter(':checked');
		var discountType = element.data('discountType');
		var discountRow = element.closest('tr');

		jQuery('input.discount_type', discountContianer).val(discountType);
		var rowPercentageField = jQuery('input.discount_percentage', discountContianer);
		var rowAmountField = jQuery('input.discount_amount', discountContianer);

		//intially making percentage and amount discount fields as hidden
		rowPercentageField.addClass('hide');
		rowAmountField.addClass('hide');

		var discountValue = discountRow.find('.discountVal').val();
		if (discountValue == "") {
			discountValue = 0;
		}
		if (isNaN(discountValue) || discountValue < 0) {
			discountValue = 0;
		}
		var productTotal = this.getLineItemTotal(lineItemRow);
		var lineItemDiscount = '(' + discountValue + ')';
		if (discountType == Inventory_Edit_Js.percentageDiscountType) {
			lineItemDiscount = '(' + discountValue + '%)';
			rowPercentageField.removeClass('hide').focus();
			//since it is percentage

			discountValue = (productTotal * discountValue) / 100;
		} else if (discountType == Inventory_Edit_Js.directAmountDiscountType) {
			rowAmountField.removeClass('hide').focus();
		}
		jQuery('.itemDiscount', lineItemRow).text(lineItemDiscount);
		jQuery('.productTotalVal', lineItemRow).text(productTotal.toFixed(this.numOfCurrencyDecimals));
		this.setDiscountTotal(lineItemRow, parseFloat(discountValue).toFixed(this.numOfCurrencyDecimals))
			.calculateTotalAfterDiscount(lineItemRow);

	},
	WarrantableHide: function () {
		// $(document).ready(function(){
		// 	$('input:radio[value=No]').click(function(){
		// 	$("#addProduct").addClass("hide");
		// 	$("#PartsfieldBlockContainer").addClass("hide");
		// 	$("#VISUAL_CHECKShideOrShowId").addClass("hide");
		// 	$("#GENERAL_CHECKShideOrShowId").addClass("hide");
		// 	});
		// 	$('input:radio[value=Yes]').click(function(){
		// 		$("#addProduct").removeClass("hide");
		// 		$("#PartsfieldBlockContainer").removeClass("hide");
		// 		$("#VISUAL_CHECKShideOrShowId").removeClass("hide");
		// 		$("#GENERAL_CHECKShideOrShowId").removeClass("hide");
		// 	});
		// });
	},

	/**
	 * Function which will calculate line item total after discount
	 * @params : lineItemRow - element which will represent lineItemRow
	 */
	calculateTotalAfterDiscount: function (lineItemRow) {
		var productTotal = this.getLineItemTotal(lineItemRow);
		var discountTotal = this.getDiscountTotal(lineItemRow);
		var totalAfterDiscount = productTotal - discountTotal;
		totalAfterDiscount = totalAfterDiscount.toFixed(this.numOfCurrencyDecimals);
		this.setTotalAfterDiscount(lineItemRow, totalAfterDiscount);
		var purchaseCost = parseFloat(lineItemRow.find('.purchaseCost').text());
		var margin = totalAfterDiscount - purchaseCost;
		margin = parseFloat(margin.toFixed(2));
		this.setMarginValue(lineItemRow, margin);
	},

	/**
	 * Function which will calculate tax for the line item total after discount
	 */
	calculateTaxForLineItem: function (lineItemRow) {
		var self = this;
		var totalAfterDiscount = this.getTotalAfterDiscount(lineItemRow);
		var taxPercentages = jQuery('.taxPercentage', lineItemRow);
		//intially make the tax as zero
		var taxTotal = 0;
		jQuery.each(taxPercentages, function (index, domElement) {
			var taxPercentage = jQuery(domElement);
			var individualTaxRow = taxPercentage.closest('tr');
			var individualTaxPercentage = taxPercentage.val();
			if (individualTaxPercentage == "") {
				individualTaxPercentage = "0";
			}
			if (isNaN(individualTaxPercentage)) {
				var individualTaxTotal = "0";
			} else {
				var individualTaxPercentage = parseFloat(individualTaxPercentage);
				var individualTaxTotal = Math.abs(individualTaxPercentage * totalAfterDiscount) / 100;
				individualTaxTotal = individualTaxTotal.toFixed(self.numOfCurrencyDecimals);
			}
			individualTaxRow.find('.taxTotal').val(individualTaxTotal);
		});

		//Calculation compound taxes
		var taxTotal = 0;
		jQuery.each(taxPercentages, function (index, domElement) {
			var taxElement = jQuery(domElement);
			var taxRow = taxElement.closest('tr');
			var total = jQuery('.taxTotal', taxRow).val();
			var compoundOn = taxElement.data('compoundOn');
			if (compoundOn) {
				var amount = parseFloat(totalAfterDiscount);

				jQuery.each(compoundOn, function (index, id) {
					if (!isNaN(jQuery('.taxTotal' + id, lineItemRow).val())) {
						amount = parseFloat(amount) + parseFloat(jQuery('.taxTotal' + id, lineItemRow).val());
					}
				});

				if (isNaN(taxElement.val())) {
					var total = 0;
				} else {
					var total = Math.abs(amount * taxElement.val()) / 100;
				}

				taxRow.find('.taxTotal').val(total);
			}
			taxTotal += parseFloat(total);
		});
		taxTotal = parseFloat(taxTotal).toFixed(self.numOfCurrencyDecimals);
		this.setLineItemTaxTotal(lineItemRow, taxTotal);
	},

	/**
	 * Function which will calculate net price for the line item
	 */
	calculateLineItemNetPrice: function (lineItemRow) {
		var totalAfterDiscount = this.getTotalAfterDiscount(lineItemRow);
		var netPrice = parseFloat(totalAfterDiscount);
		if (this.isIndividualTaxMode()) {
			var productTaxTotal = this.getLineItemTaxTotal(lineItemRow);
			netPrice += parseFloat(productTaxTotal)
		}
		netPrice = netPrice.toFixed(this.numOfCurrencyDecimals);
		this.setLineItemNetPrice(lineItemRow, netPrice);
	},

	/**
	 * Function which will caliculate the total net price for all the line items
	 */
	calculateNetTotal: function () {
		var self = this
		var netTotalValue = 0;
		this.lineItemsHolder.find('tr.' + this.lineItemDetectingClass + ' .netPrice').each(function (index, domElement) {
			var lineItemNetPriceEle = jQuery(domElement);
			netTotalValue += self.formatLineItemNetPrice(lineItemNetPriceEle);
		});
		this.setNetTotal(netTotalValue.toFixed(this.numOfCurrencyDecimals));
		this.finalDiscountUIEle.find('.subTotalVal').text(netTotalValue);
	},
	uppercase: (function () {
		$(".inputElement ").keyup(function () {
			if (this.value != undefined) {
				this.value = this.value.toLocaleUpperCase();
			}
		});
	}),

	calculateFinalDiscount: function () {
		var discountContainer = this.finalDiscountUIEle;
		var element = discountContainer.find('input.finalDiscounts').filter(':checked');
		var discountType = element.data('discountType');
		var discountRow = element.closest('tr');
		var numberOfDecimal = this.numOfCurrencyDecimals;

		jQuery('#discount_type_final').val(discountType);
		var rowPercentageField = discountContainer.find('input.discount_percentage_final');
		var rowAmountField = discountContainer.find('input.discount_amount_final');

		//intially making percentage and amount discount fields as hidden
		rowPercentageField.addClass('hide');
		rowAmountField.addClass('hide');

		var discountValue = discountRow.find('.discountVal').val();
		if (discountValue == "") {
			discountValue = 0;
		}
		if (isNaN(discountValue) || discountValue < 0) {
			discountValue = 0;
		}

		var overallDiscount = '(' + discountValue + ')';
		if (discountType == Inventory_Edit_Js.percentageDiscountType) {
			overallDiscount = '(' + discountValue + '%)';
			rowPercentageField.removeClass('hide').focus();
			//since it is percentage
			var productTotal = this.getNetTotal();
			discountValue = (productTotal * discountValue) / 100;
		} else if (discountType == Inventory_Edit_Js.directAmountDiscountType) {
			if (this.prevSelectedCurrencyConversionRate) {
				var conversionRate = this.conversionRateEle.val();
				conversionRate = conversionRate / this.prevSelectedCurrencyConversionRate;
				discountValue = discountValue * conversionRate;
				discountRow.find('.discountVal').val(discountValue);
			}
			rowAmountField.removeClass('hide').focus();
		}
		discountValue = parseFloat(discountValue).toFixed(numberOfDecimal);
		this.overAllDiscountEle.text(overallDiscount);
		this.setFinalDiscountTotal(discountValue);
		this.calculatePreTaxTotal();
	},

	/**
	 * Function to calculate the preTaxTotal value
	 */
	calculatePreTaxTotal: function () {
		var numberOfDecimal = this.numOfCurrencyDecimals;
		if (this.isGroupTaxMode()) {
			var netTotal = this.getNetTotal();
		} else {
			var thisInstance = this;
			var netTotal = 0;
			var elementsList = this.lineItemsHolder.find('.' + this.lineItemDetectingClass);
			jQuery.each(elementsList, function (index, element) {
				var lineItemRow = jQuery(element);
				netTotal = netTotal + thisInstance.getTotalAfterDiscount(lineItemRow);
			});
		}
		var chargesTotal = this.getChargesTotal();
		var finalDiscountValue = this.getFinalDiscountTotal();
		var preTaxTotal = netTotal + chargesTotal - finalDiscountValue;
		var preTaxTotalValue = parseFloat(preTaxTotal).toFixed(numberOfDecimal);
		this.setPreTaxTotal(preTaxTotalValue);
	},

	calculateCharges: function () {
		var chargesBlockContainer = this.chargesContainer;
		var numberOfDecimal = this.numOfCurrencyDecimals;

		var netTotal = this.getNetTotal();
		var finalDiscountValue = this.getFinalDiscountTotal();
		var amount = parseFloat(netTotal - finalDiscountValue).toFixed(numberOfDecimal);

		chargesBlockContainer.find('.chargePercent').each(function (index, domElement) {
			var element = jQuery(domElement);

			if (isNaN(element.val())) {
				var value = 0;
			} else {
				var value = Math.abs(amount * element.val()) / 100;
			}

			element.closest('tr').find('.chargeValue').val(parseFloat(value).toFixed(numberOfDecimal));
		});

		var chargesTotal = 0;
		chargesBlockContainer.find('.chargeValue').each(function (index, domElement) {
			var chargeElementValue = jQuery(domElement).val();
			if (!chargeElementValue) {
				jQuery(domElement).val(0);
				chargeElementValue = 0;
			}
			chargesTotal = parseFloat(chargesTotal) + parseFloat(chargeElementValue);
		});

		this.chargesTotalEle.val(parseFloat(chargesTotal));
		this.chargesTotalDisplay.text(parseFloat(chargesTotal).toFixed(numberOfDecimal));
		jQuery('#SHChargeVal').text(chargesTotal.toFixed(numberOfDecimal));

		this.calculateChargeTaxes();
		//		this.calculatePreTaxTotal();
		//		this.calculateGrandTotal();
	},

	calculateChargeTaxes: function () {
		var self = this;
		var chargesBlockContainer = this.chargeTaxesContainer;

		chargesBlockContainer.find('.chargeTaxPercentage').each(function (index, domElement) {
			var element = jQuery(domElement);
			var chargeId = element.data('chargeId');
			var chargeAmount = self.chargesContainer.find('[name="charges[' + chargeId + '][value]"]').val();
			if (isNaN(element.val())) {
				var value = 0;
			} else {
				var value = Math.abs(chargeAmount * element.val()) / 100;
			}
			element.closest('tr').find('.chargeTaxValue').val(parseFloat(value).toFixed(self.numOfCurrencyDecimals));
		});

		chargesBlockContainer.find('.chargeTaxPercentage').each(function (index, domElement) {
			var element = jQuery(domElement);
			var compoundOn = element.data('compoundOn');
			if (compoundOn) {
				var chargeId = element.data('chargeId');
				var chargeAmount = parseFloat(self.chargesContainer.find('[name="charges[' + chargeId + '][value]"]').val()).toFixed(self.numOfCurrencyDecimals);

				jQuery.each(compoundOn, function (index, id) {
					var chargeTaxEle = chargesBlockContainer.find('.chargeTax' + chargeId + id);
					if (!isNaN(chargeTaxEle.val())) {
						chargeAmount = parseFloat(chargeAmount) + parseFloat(chargeTaxEle.val());
					}
				});

				if (isNaN(element.val())) {
					var value = 0;
				} else {
					var value = Math.abs(chargeAmount * element.val()) / 100;
				}

				element.closest('tr').find('.chargeTaxValue').val(parseFloat(value).toFixed(self.numOfCurrencyDecimals));
			}
		});

		var chargesTotal = 0;
		chargesBlockContainer.find('.chargeTaxValue').each(function (index, domElement) {
			var chargeElementValue = jQuery(domElement).val();
			chargesTotal = parseFloat(chargesTotal) + parseFloat(chargeElementValue);
		});
		jQuery('#chargeTaxTotal').text(parseFloat(chargesTotal).toFixed(this.numOfCurrencyDecimals));
		this.chargeTaxesTotal.val(parseFloat(chargesTotal).toFixed(this.numOfCurrencyDecimals));
		this.calculatePreTaxTotal();
		this.calculateGrandTotal();
	},

	calculateGrandTotal: function () {
		var netTotal = this.getNetTotal();
		var discountTotal = this.getFinalDiscountTotal();
		var shippingHandlingCharge = this.getChargesTotal();
		var shippingHandlingTax = this.getChargeTaxesTotal();
		var deductedTaxesAmount = this.getDeductTaxesTotal();
		var adjustment = this.getAdjustmentValue();
		var grandTotal = parseFloat(netTotal) - parseFloat(discountTotal) + parseFloat(shippingHandlingCharge) + parseFloat(shippingHandlingTax) - parseFloat(deductedTaxesAmount);

		if (this.isGroupTaxMode()) {
			grandTotal += this.getGroupTaxTotal();
		}

		if (this.isAdjustMentAddType()) {
			grandTotal += parseFloat(adjustment);
		} else if (this.isAdjustMentDeductType()) {
			grandTotal -= parseFloat(adjustment);
		}

		grandTotal = grandTotal.toFixed(this.numOfCurrencyDecimals);
		this.setGrandTotal(grandTotal);
	},

	calculateGroupTax: function () {
		var self = this;
		var netTotal = this.getNetTotal();
		var finalDiscountValue = this.getFinalDiscountTotal();
		var amount = netTotal - finalDiscountValue;
		amount = parseFloat(amount).toFixed(this.numOfCurrencyDecimals);
		var groupTaxTotal = 0;
		this.groupTaxContainer.find('.groupTaxPercentage').each(function (index, domElement) {
			var groupTaxPercentageElement = jQuery(domElement);
			var groupTaxRow = groupTaxPercentageElement.closest('tr');
			if (isNaN(groupTaxPercentageElement.val())) {
				var groupTaxValue = "0";
			} else {
				var groupTaxValue = Math.abs(amount * groupTaxPercentageElement.val()) / 100;
			}
			groupTaxValue = parseFloat(groupTaxValue).toFixed(self.numOfCurrencyDecimals);
			groupTaxRow.find('.groupTaxTotal').val(groupTaxValue);
		});

		//Calculating compound taxes
		groupTaxTotal = 0;
		this.groupTaxContainer.find('.groupTaxPercentage').each(function (index, domElement) {
			var groupTaxPercentageElement = jQuery(domElement);
			var compoundOn = groupTaxPercentageElement.data('compoundOn');
			var groupTaxRow = groupTaxPercentageElement.closest('tr');

			if (compoundOn) {
				var totalAmount = amount;
				jQuery.each(compoundOn, function (index, value) {
					var groupTaxAmountValue = self.groupTaxContainer.find('[name="tax' + value + '_group_amount"]').val();
					if (!isNaN(groupTaxAmountValue)) {
						totalAmount = parseFloat(totalAmount) + parseFloat(groupTaxAmountValue);
					}
				});

				if (isNaN(groupTaxPercentageElement.val())) {
					var groupTaxValue = 0;
				} else {
					var groupTaxValue = Math.abs(totalAmount * groupTaxPercentageElement.val()) / 100;
				}

				groupTaxValue = parseFloat(groupTaxValue).toFixed(self.numOfCurrencyDecimals);
				groupTaxRow.find('.groupTaxTotal').val(groupTaxValue);
			} else {
				var groupTaxValue = groupTaxRow.find('.groupTaxTotal').val();
			}
			if (isNaN(groupTaxValue)) {
				groupTaxValue = 0;
			}
			groupTaxTotal += parseFloat(groupTaxValue);
		});

		this.setGroupTaxTotal(groupTaxTotal.toFixed(this.numOfCurrencyDecimals));
	},

	calculateDeductTaxes: function () {
		var self = this;
		var netTotal = this.getNetTotal();
		var finalDiscountValue = this.getFinalDiscountTotal();
		var amount = parseFloat(netTotal - finalDiscountValue).toFixed(this.numOfCurrencyDecimals);

		var deductTaxesTotalAmount = 0;
		this.dedutTaxesContainer.find('.deductTaxPercentage').each(function (index, domElement) {
			var value = 0;
			var element = jQuery(domElement);
			if (!isNaN(element.val())) {
				value = Math.abs(amount * element.val()) / 100;
			}

			value = parseFloat(value).toFixed(self.numOfCurrencyDecimals);
			element.closest('tr').find('.deductTaxValue').val(value);
			deductTaxesTotalAmount = parseFloat(deductTaxesTotalAmount) + parseFloat(value);
		});

		this.deductTaxesTotal.text(parseFloat(deductTaxesTotalAmount).toFixed(this.numOfCurrencyDecimals));
		this.calculateGrandTotal();
	},

	lineItemDirectDiscountCal: function (conversionRate) {
		//LineItems Discount Calculations for direct Price reduction
		var self = this;

		self.lineItemsHolder.find('tr.' + self.lineItemDetectingClass).each(function (index, domElement) {
			var lineItemRow = jQuery(domElement);
			var discountContianer = lineItemRow.find('div.discountUI');
			var element = discountContianer.find('input.discounts').filter(':checked');
			var discountRow = element.closest('tr');
			var discountType = element.data('discountType');
			var discountValue = discountRow.find('.discountVal').val();
			if ((discountType == Inventory_Edit_Js.directAmountDiscountType)) {
				var newdiscountValue = conversionRate * discountValue;
				discountRow.find('.discountVal').val(newdiscountValue);
				jQuery(element).closest('tr').find('.discountVal').val(newdiscountValue);
				self.setDiscountTotal(lineItemRow, newdiscountValue.toFixed(self.numberOfCurrencyDecimals));
			}
		});
	},


	AdjustmentShippingResultCalculation: function (conversionRate) {
		//Adjustment
		var self = this;
		var adjustmentElement = this.adjustmentEle;
		var newAdjustment = jQuery(adjustmentElement).val() * conversionRate;
		jQuery(adjustmentElement).val(newAdjustment);

		//Shipping & handling
		var chargesBlockContainer = self.chargesContainer;
		chargesBlockContainer.find('.chargeValue').each(function (index, domElement) {
			var chargeElement = jQuery(domElement);
			jQuery(chargeElement).val(parseFloat(jQuery(domElement).val()) * conversionRate);
		});
		this.calculateCharges();
	},

	lineItemRowCalculations: function (lineItemRow) {
		this.calculateLineItemTotal(lineItemRow);
		this.calculateDiscountForLineItem(lineItemRow);
		this.calculateTaxForLineItem(lineItemRow);
		this.calculateLineItemNetPrice(lineItemRow);
	},

	lineItemToTalResultCalculations: function () {
		this.calculateNetTotal();
		this.calculateFinalDiscount();

		this.calculateCharges();
		if (this.isGroupTaxMode()) {
			this.calculateGroupTax();
		}
		this.calculateDeductTaxes();
		this.calculateGrandTotal();
	},

	/**
	 * Function which will handle the actions that need to be performed once the tax percentage is change for a line item
	 * @params : lineItemRow - element which will represent lineItemRow
	 */

	taxPercentageChangeActions: function (lineItemRow) {
		this.calculateLineItemNetPrice(lineItemRow);
		this.calculateNetTotal();
		this.calculateFinalDiscount();
		if (this.isGroupTaxMode()) {
			this.calculateGroupTax();
		}
		this.lineItemToTalResultCalculations();
		this.calculateGrandTotal();
	},

	lineItemDiscountChangeActions: function (lineItemRow) {
		this.calculateDiscountForLineItem(lineItemRow);
		this.calculateTaxForLineItem(lineItemRow);
		this.calculateLineItemNetPrice(lineItemRow);

		this.lineItemToTalResultCalculations();
	},

	finalDiscountChangeActions: function () {
		this.calculateChargeTaxes();
		this.calculateFinalDiscount();
		if (this.isGroupTaxMode()) {
			this.calculateGroupTax();
		}
		this.calculateCharges();
		this.calculateDeductTaxes();
		this.calculateGrandTotal();
	},

	lineItemDeleteActions: function () {
		this.lineItemToTalResultCalculations();
	},

	loadSubProducts: function (lineItemRow) {
		var recordId = jQuery('input.selectedModuleId', lineItemRow).val();
		var subProrductParams = {
			'module': "Products",
			'action': "SubProducts",
			'record': recordId
		}
		app.request.get({ 'data': subProrductParams }).then(
			function (error, data) {
				if (!data) {
					return;
				}
				var result = data;
				var isBundleViewable = result.isBundleViewable;
				var responseData = result.values;
				var subProductsContainer = jQuery('.subProductsContainer', lineItemRow);
				var subProductIdHolder = jQuery('.subProductIds', lineItemRow);

				var subProductIdsList = '';
				var subProductHtml = '';
				for (var id in responseData) {
					if (isBundleViewable == 1) {
						subProductHtml += '<em> - ' + responseData[id]['productName'] + ' (' + responseData[id]['quantity'] + ')';
						if (responseData[id]['stockMessage']) {
							subProductHtml += ' - <span class="redColor">' + responseData[id]['stockMessage'] + '</span>';
						}
						subProductHtml += '</em><br>';
					}
					subProductIdsList += id + ':' + responseData[id]['quantity'] + ',';
				}
				subProductIdHolder.val(subProductIdsList);
				subProductsContainer.html(subProductHtml);
			}
		);
	},

	/**
	 * Function which will handle the actions that need to be preformed once the qty is changed like below
	 *  - calculate line item total -> discount and tax -> net price of line item -> grand total
	 * @params : lineItemRow - element which will represent lineItemRow
	 */
	quantityChangeActions: function (lineItemRow) {
		var purchaseCost = this.getPurchaseCostValue(lineItemRow);
		this.setPurchaseCostValue(lineItemRow, purchaseCost);
		this.lineItemRowCalculations(lineItemRow);
		this.lineItemToTalResultCalculations();
	},

	getTaxDiv: function (taxObj, parentRow) {
		var rowNumber = jQuery('input.rowNumber', parentRow).val();
		var loopIterator = 1;
		var taxDiv =
			'<div class="taxUI hide" id="tax_div' + rowNumber + '">' +
			'<p class="popover_title hide"> Set Tax for : <span class="variable"></span></p>';
		if (!jQuery.isEmptyObject(taxObj)) {
			taxDiv +=
				'<div class="individualTaxDiv">' +
				'<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable" id="tax_table' + rowNumber + '">';

			for (var taxName in taxObj) {
				var taxInfo = taxObj[taxName];
				taxDiv +=
					'<tr>' +
					'<td>  ' + taxInfo.taxlabel + '</td>' +
					'<td style="text-align: right;">' +
					'<input type="text" name="' + taxName + '_percentage' + rowNumber + '" data-rule-positive=true data-rule-inventory_percentage=true  id="' + taxName + '_percentage' + rowNumber + '" value="' + taxInfo.taxpercentage + '" class="taxPercentage" data-compound-on=' + taxInfo.compoundOn + ' data-regions-list="' + taxInfo.regionsList + '">&nbsp;%' +
					'</td>' +
					'<td style="text-align: right; padding-right: 10px;">' +
					'<input type="text" name="popup_tax_row' + rowNumber + '" class="cursorPointer span1 taxTotal taxTotal' + taxInfo.taxid + '" value="0.0" readonly>' +
					'</td>' +
					'</tr>';
				loopIterator++;
			}
			taxDiv +=
				'</table>' +
				'</div>';
		} else {
			taxDiv +=
				'<div class="textAlignCenter">' +
				'<span>' + app.vtranslate('JS_NO_TAXES_EXISTS') + '</span>' +
				'</div>';
		}

		taxDiv += '</div>';
		return jQuery(taxDiv);
	},

	mapResultsToFields: function (parentRow, responseData) {
		var lineItemNameElment = jQuery('input.productName', parentRow);
		var referenceModule = this.getLineItemSetype(parentRow);
		var lineItemRowNumber = parentRow.data('rowNum');
		for (var id in responseData) {
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
			if (typeof listPriceValues[currencyId] != 'undefined') {
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
	},

	mapResultsToFieldsAnother: function (parentRow, responseData) {
		var lineItemNameElment = jQuery('input.productName', parentRow);
		var referenceModule = this.getLineItemSetype(parentRow);
		var lineItemRowNumber = parentRow.data('rowNum');
		for (var id in responseData) {
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
			if (typeof listPriceValues[currencyId] != 'undefined') {
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
		jQuery('#addProduct').trigger('click');
		let AnotherItem = jQuery('#lineItemTab');
		let child = AnotherItem.find("#row" + this.numOfLineItems1);
		this.mapResultsToFields1(child, responseData);
		jQuery('.qty', parentRow).trigger('focusout');
	},

	mapResultsToFields1: function (parentRow, responseData) {
		var lineItemNameElment = jQuery('input.productName', parentRow);
		var referenceModule = this.getLineItemSetype(parentRow);
		for (var id in responseData) {
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
			if (typeof listPriceValues[currencyId] != 'undefined') {
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
		jQuery('.qty', parentRow).trigger('focusout');
	},

	showLineItemPopup: function (callerParams) {
		var params = {
			'module': this.getModuleName(),
			'multi_select': true,
			'currency_id': this.currencyElement.val()
		};

		params = jQuery.extend(params, callerParams);
		var popupInstance = Vtiger_Popup_Js.getInstance();
		popupInstance.showPopup(params, 'post.LineItemPopupSelection.click');

	},

	postLineItemSelectionActions1: function (itemRow, selectedLineItemsData, lineItemSelectedModuleName) {
		for (var index in selectedLineItemsData) {
			if (index != 0) {
				if (lineItemSelectedModuleName == 'Products') {
					jQuery('#addProduct').trigger('click', selectedLineItemsData[index]);
				} else if (lineItemSelectedModuleName == 'Services') {
					jQuery('#addService').trigger('click', selectedLineItemsData[index]);
				}
			} else {
				itemRow.find('.lineItemType').val(lineItemSelectedModuleName);
				this.mapResultsToFieldsAnother(itemRow, selectedLineItemsData[index]);
			}
		}
	},

	postLineItemSelectionActions: function (itemRow, selectedLineItemsData, lineItemSelectedModuleName) {
		for (var index in selectedLineItemsData) {
			if (index != 0) {
				if (lineItemSelectedModuleName == 'Products') {
					jQuery('#addProduct').trigger('click', selectedLineItemsData[index]);
				} else if (lineItemSelectedModuleName == 'Services') {
					jQuery('#addService').trigger('click', selectedLineItemsData[index]);
				}
			} else {
				itemRow.find('.lineItemType').val(lineItemSelectedModuleName);
				this.mapResultsToFields(itemRow, selectedLineItemsData[index]);
			}
		}
	},

	/**
	 * Function which will be used to handle price book popup
	 * @params :  popupImageElement - popup image element
	 */
	pricebooksPopupHandler: function (popupImageElement) {
		var self = this;
		var lineItemRow = popupImageElement.closest('tr.' + this.lineItemDetectingClass);
		var lineItemProductOrServiceElement = lineItemRow.find('input.productName').closest('td');
		var params = {};
		params.module = 'PriceBooks';
		params.src_module = lineItemProductOrServiceElement.find('i.lineItemPopup').data('moduleName');
		params.src_field = lineItemProductOrServiceElement.find('i.lineItemPopup').data('fieldName');
		params.src_record = lineItemProductOrServiceElement.find('input.selectedModuleId').val();
		params.get_url = 'getProductListPriceURL';
		params.currency_id = jQuery('#currency_id option:selected').val();
		params.view = 'Popup';
		var popupInstance = Vtiger_Popup_Js.getInstance();
		popupInstance.showPopup(params, 'post.LineItemPriceBookSelect.click');
	},

	registerAddProductService: function () {
		var self = this;
		var addLineItemEventHandler = function (e, data) {
			var currentTarget = jQuery(e.currentTarget);
			var params = { 'currentTarget': currentTarget }
			var newLineItem = self.getNewLineItem(params);
			newLineItem = newLineItem.appendTo(self.lineItemsHolder);
			newLineItem.find('input.productName').addClass('autoComplete');
			newLineItem.find('.ignore-ui-registration').removeClass('ignore-ui-registration');
			vtUtils.applyFieldElementsView(newLineItem);
			app.event.trigger('post.lineItem.New', newLineItem);
			self.checkLineItemRow();
			self.registerLineItemAutoComplete(newLineItem);
			if (typeof data != "undefined") {
				self.mapResultsToFields(newLineItem, data);
			}
		}
		var addLineItemEventHandler1 = function (e, data) {
			var currentTarget = jQuery(e.currentTarget);
			var params = { 'currentTarget': currentTarget }
			var newLineItem = self.getNewLineItem1(params);
			newLineItem = newLineItem.appendTo(self.lineItemsHolder1);
			newLineItem.find('input.productName').addClass('autoComplete1');
			newLineItem.find('.ignore-ui-registration').removeClass('ignore-ui-registration');
			vtUtils.applyFieldElementsView(newLineItem);
			app.event.trigger('post.lineItem.New', newLineItem);
			self.checkLineItemRow();
			self.registerLineItemAutoComplete(newLineItem);
			if (typeof data != "undefined") {
				self.mapResultsToFields(newLineItem, data);
			}
		}
		jQuery('#addProduct1').on('click', addLineItemEventHandler1);
		jQuery('#addProduct').on('click', addLineItemEventHandler);
		jQuery('#addService').on('click', addLineItemEventHandler);
	},

	registerProductAndServiceSelector: function () {
		var self = this;

		this.lineItemsHolder.on('click', '.lineItemPopup', function (e) {
			var triggerer = jQuery(e.currentTarget);
			self.showLineItemPopup({ 'view': triggerer.data('popup') });
			var popupReferenceModule = triggerer.data('moduleName');
			var postPopupHandler = function (e, data) {
				data = JSON.parse(data);
				if (!$.isArray(data)) {
					data = [data];
				}
				self.postLineItemSelectionActions(triggerer.closest('tr'), data, popupReferenceModule);
			}
			app.event.off('post.LineItemPopupSelection.click');
			app.event.one('post.LineItemPopupSelection.click', postPopupHandler);
		});
		this.lineItemsHolder1.on('click', '.lineItemPopup1', function (e) {
			var triggerer = jQuery(e.currentTarget);
			self.showLineItemPopup({ 'view': triggerer.data('popup') });
			var popupReferenceModule = triggerer.data('moduleName');
			var postPopupHandler = function (e, data) {
				data = JSON.parse(data);
				if (!$.isArray(data)) {
					data = [data];
				}
				self.postLineItemSelectionActions1(triggerer.closest('tr'), data, popupReferenceModule);
			}
			app.event.off('post.LineItemPopupSelection.click');
			app.event.one('post.LineItemPopupSelection.click', postPopupHandler);
		});
	},

	registerQuantityChangeEvent: function () {
		var self = this;

		this.lineItemsHolder.on('focusout', '.qty', function (e) {
			var element = jQuery(e.currentTarget);
			var lineItemRow = element.closest('tr.' + self.lineItemDetectingClass);
			var quantityInStock = lineItemRow.data('quantityInStock');
			if (typeof quantityInStock != 'undefined') {
				if (parseFloat(element.val()) > parseFloat(quantityInStock)) {
					lineItemRow.find('.stockAlert').removeClass('hide').find('.maxQuantity').text(quantityInStock);
				} else {
					lineItemRow.find('.stockAlert').addClass('hide');
				}
			}
			if (self.formValidatorInstance == false) {
				self.quantityChangeActions(lineItemRow);
			}
			else {
				if (self.formValidatorInstance.element(element)) {
					self.quantityChangeActions(lineItemRow);
				}
			}

		});
	},

	/**
	  * Function which will register event for list price event change
	  */
	registerListPriceChangeEvent: function () {
		var self = this;

		this.lineItemsHolder.on('focusout', 'input.listPrice', function (e) {
			var element = jQuery(e.currentTarget);
			var lineItemRow = self.getClosestLineItemRow(element);
			var isPriceChanged = element.data('isPriceChanged');
			if (!self.formValidatorInstance.element(element)) {
				return;
			}

			if (isPriceChanged == false) {
				var listPriceValues = JSON.parse(element.attr('list-info'));
				var listPriceVal = self.getListPriceValue(lineItemRow);
				var currencyElement = self.currencyElement;
				var currencyId = currencyElement.val();
				var optionsSelected = currencyElement.find('option:selected');
				var prevSelectedCurrencyConversionRate = self.conversionRateEle.val();

				var conversionRate = optionsSelected.data('conversionRate');
				conversionRate = parseFloat(conversionRate) / parseFloat(prevSelectedCurrencyConversionRate);
				var convertedListPrice = listPriceValues[currencyId];
				if (typeof listPriceValues[currencyId] == 'undefined') {
					var baseCurrencyId = element.data('baseCurrencyId');
					var baseCurrencyElement = currencyElement.find("option[value='" + baseCurrencyId + "']");
					convertedListPrice = (listPriceValues[baseCurrencyId] * optionsSelected.data('conversionRate')) / baseCurrencyElement.data('conversionRate');
				}

				if (convertedListPrice != listPriceVal) {
					element.data('isPriceChanged', true);
				}
			}
			self.quantityChangeActions(lineItemRow);
		});
	},

	/**
	* Function which will regisrer price book popup
	*/
	registerPriceBookPopUp: function () {
		var self = this;

		this.lineItemsHolder.on('click', '.priceBookPopup', function (e) {
			var element = jQuery(e.currentTarget);
			var response = self.isProductSelected(element);
			if (response == false) {
				return;
			}
			var lineItemRow = element.closest('tr.' + self.lineItemDetectingClass);
			self.pricebooksPopupHandler(element);
			var postPriceBookPopupHandler = function (e, data) {
				var responseData = JSON.parse(data);
				for (var id in responseData) {
					self.setListPriceValue(lineItemRow, responseData[id]);
				}
				self.quantityChangeActions(lineItemRow);
			}
			app.event.off('post.LineItemPriceBookSelect.click');
			app.event.one('post.LineItemPriceBookSelect.click', postPriceBookPopupHandler);
		});
	},

	registerLineItemTaxShowEvent: function () {
		var self = this;

		this.lineItemsHolder.on('click', '.individualTax', function (e) {
			var element = jQuery(e.currentTarget);
			var response = self.isProductSelected(element);
			if (response == false) {
				return;
			}
			element.popover('destroy');
			var lineItemRow = self.getClosestLineItemRow(element);
			self.getForm().find('.popover.lineItemPopover').css('opacity', 0).css('z-index', '-1');

			var callBackFunction = function (element, data) {

				data.on('focusout', '.taxPercentage', function (e) {
					var currentTaxElement = jQuery(e.currentTarget);
					if (currentTaxElement.valid()) {
						var taxIdAttr = currentTaxElement.attr('id');
						var taxElement = lineItemRow.find('.taxUI').find('#' + taxIdAttr);
						taxElement.val(currentTaxElement.val());
						self.calculateTaxForLineItem(lineItemRow);
						var taxTotalValue = taxElement.closest('tr').find('.taxTotal').val();
						currentTaxElement.closest('tr').find('.taxTotal').val(taxTotalValue);
					}
				});

				data.find('.popoverButton').on('click', function (e) {
					var validate = data.find('input').valid();
					if (validate) {
						element.popover('destroy');
						self.taxPercentageChangeActions(lineItemRow);
					}
				});

				data.find('.popoverCancel').on('click', function (e) {
					self.getForm().find("div[id^=qtip-]").qtip('destroy');
					element.popover('destroy');
				});
			};

			var parentElem = jQuery(e.currentTarget).closest('td');

			var taxUI = parentElem.find('div.taxUI').clone(true, true).removeClass('hide').addClass('show');
			taxUI.find('div.individualTaxDiv').removeClass('hide').addClass('show');
			var popOverTitle = taxUI.find('.popover_title').find('.variable').text(self.getTotalAfterDiscount(lineItemRow)).closest('.popover_title').text();
			var template = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate);
			template.addClass('individualTaxForm');
			element.popover({
				'content': taxUI,
				'html': true,
				'placement': 'top',
				'animation': true,
				'title': popOverTitle,
				'trigger': 'manual',
				'template': template,
				'container': self.lineItemsHolder

			});
			element.one('shown.bs.popover', function (e) {
				callBackFunction(element, jQuery('.individualTaxForm'));
				if (element.next('.popover').find('.popover-content').height() > 300) {
					app.helper.showScroll(element.next('.popover').find('.popover-content'), { 'height': '300px' });
				}
			})
			element.popover('toggle');

		});
	},

	registerTaxTypeChange: function () {
		var self = this;

		this.taxTypeElement.on('change', function (e) {
			if (self.isIndividualTaxMode()) {
				jQuery('#group_tax_row').addClass('hide');
				self.lineItemsHolder.find('tr.' + self.lineItemDetectingClass).each(function (index, domElement) {
					var lineItemRow = jQuery(domElement);
					lineItemRow.find('.individualTaxContainer,.productTaxTotal').removeClass('hide');
					self.lineItemRowCalculations(lineItemRow);
				});
			} else {
				jQuery('#group_tax_row').removeClass('hide');
				self.lineItemsHolder.find('tr.' + self.lineItemDetectingClass).each(function (index, domElement) {
					var lineItemRow = jQuery(domElement);
					lineItemRow.find('.individualTaxContainer,.productTaxTotal').addClass('hide');
					self.calculateLineItemNetPrice(lineItemRow);
				});
			}
			self.lineItemToTalResultCalculations();
		});
	},

	registerCurrencyChangeEvent: function () {
		var self = this;
		this.currencyElement.change(function (e) {
			var element = jQuery(e.currentTarget);
			var currencyId = element.val();
			var conversionRateElem = jQuery('#conversion_rate');
			var prevSelectedCurrencyConversionRate = conversionRateElem.val();
			self.prevSelectedCurrencyConversionRate = prevSelectedCurrencyConversionRate;
			var optionsSelected = element.find('option:selected');
			var conversionRate = optionsSelected.data('conversionRate');
			conversionRateElem.val(conversionRate);
			conversionRate = parseFloat(conversionRate) / parseFloat(prevSelectedCurrencyConversionRate);
			self.lineItemDirectDiscountCal(conversionRate);
			self.lineItemsHolder.find('tr.' + self.lineItemDetectingClass).each(function (index, domElement) {
				var lineItemRow = jQuery(domElement);
				var isLineItemSelected = jQuery(lineItemRow).find('.selectedModuleId').val();
				if (!isLineItemSelected) {
					//continue == 'return' && break == 'return false' in JQuery.each();
					//Ref: http://stackoverflow.com/questions/17162334/how-to-use-continue-in-jquery-each-loop
					return;
				}

				var purchaseCostVal = self.getPurchaseCostValue(lineItemRow);
				var updatedPurchaseCost = parseFloat(purchaseCostVal) * parseFloat(conversionRate);
				self.setPurchaseCostValue(lineItemRow, updatedPurchaseCost);
				var listPriceElement = jQuery(lineItemRow).find('[name^=listPrice]');
				var listPriceValues = JSON.parse(listPriceElement.attr('list-info'));
				var isPriceChanged = listPriceElement.data('isPriceChanged');
				var listPriceVal = self.getListPriceValue(lineItemRow);
				var convertedListPrice = listPriceVal * conversionRate;
				if (isPriceChanged == false) {
					convertedListPrice = listPriceValues[currencyId];
					if (typeof listPriceValues[currencyId] == 'undefined') {
						var baseCurrencyId = listPriceElement.data('baseCurrencyId');
						var baseCurrencyElement = element.find("option[value='" + baseCurrencyId + "']");
						convertedListPrice = (listPriceValues[baseCurrencyId] * optionsSelected.data('conversionRate')) / baseCurrencyElement.data('conversionRate');
					}
				}
				self.setListPriceValue(lineItemRow, convertedListPrice);
				self.lineItemRowCalculations(lineItemRow);
			});
			self.AdjustmentShippingResultCalculation(conversionRate);
			self.lineItemToTalResultCalculations();
			jQuery('#prev_selected_currency_id').val(optionsSelected.val());
			self.prevSelectedCurrencyConversionRate = false;
		});
	},

	registerLineItemDiscountShowEvent: function () {
		var self = this;

		this.lineItemsHolder.on('click', '.individualDiscount', function (e) {
			var element = jQuery(e.currentTarget);
			var response = self.isProductSelected(element);
			if (response == false) {
				return;
			}
			element.popover('destroy');
			var lineItemRow = self.getClosestLineItemRow(element);
			self.getForm().find('.popover.lineItemPopover').css('opacity', 0).css('z-index', '-1');

			var callBackFunction = function (element, data) {
				var triggerDiscountChangeEvent = function (discountDiv) {
					var selectedDiscountType = discountDiv.find('input.discounts').filter(':checked');
					var discountType = selectedDiscountType.data('discountType');

					var rowAmountField = jQuery('input.discount_amount', discountDiv);
					var rowPercentageField = jQuery('input.discount_percentage', discountDiv);

					rowAmountField.hide();
					rowPercentageField.hide();
					if (discountType == Inventory_Edit_Js.percentageDiscountType) {
						rowPercentageField.show().removeClass('hide').focus();
					} else if (discountType == Inventory_Edit_Js.directAmountDiscountType) {
						rowAmountField.show().removeClass('hide').focus();
					}
				};

				var discountDiv = jQuery('div.discountUI', data);
				triggerDiscountChangeEvent(discountDiv);

				data.on('change', '.discounts', function (e) {
					var ele = jQuery(e.currentTarget);
					var discountDiv = ele.closest('div.discountUI');
					triggerDiscountChangeEvent(discountDiv);

				});

				data.find('.popoverButton').on('click', function (e) {
					var validate = data.find('input').valid();
					if (validate) {
						//if the element is not hidden then we need to handle the focus out
						//	if (!app.isHidden(saveButtonElement)) {
						//	var globalModal = saveButtonElement.closest('#globalmodal');
						//	var discountDiv = globalModal.find('div.discountUI');
						var selectedDiscountType = discountDiv.find('input.discounts').filter(':checked');
						var discountType = selectedDiscountType.data('discountType');
						var discountRow = selectedDiscountType.closest('tr');

						var discountValue = discountRow.find('.discountVal').val();
						if (discountValue == "" || isNaN(discountValue) || discountValue < 0) {
							discountValue = 0;
						}

						var discountDivId = discountDiv.attr('id');
						var oldDiscountDiv = jQuery('#' + discountDivId, lineItemRow);

						var discountTypes = oldDiscountDiv.find('input.discounts');
						jQuery.each(discountTypes, function (index, type) {
							var type = jQuery(type);
							type.prop('checked', false);
						});
						jQuery.each(discountTypes, function (index, type) {
							var type = jQuery(type);
							var discountTypeOfType = type.data('discountType');
							if (discountTypeOfType == discountType) {
								type.prop('checked', true);
							}
						});

						if (discountType == Inventory_Edit_Js.percentageDiscountType) {
							jQuery('input.discount_percentage', oldDiscountDiv).val(discountValue);
						} else if (discountType == Inventory_Edit_Js.directAmountDiscountType) {
							jQuery('input.discount_amount', oldDiscountDiv).val(discountValue);
						}
						element.popover('destroy');
						self.lineItemDiscountChangeActions(lineItemRow);
						//						}
					}
				});

				data.find('.popoverCancel').on('click', function (e) {
					self.getForm().find("div[id^=qtip-]").qtip('destroy');
					element.popover('destroy');
				});
			}

			var parentElem = jQuery(e.currentTarget).closest('td');

			var discountUI = parentElem.find('div.discountUI').clone(true, true).removeClass('hide').addClass('show');
			var template = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate);
			template.addClass('discountForm');
			var productTotal = self.getLineItemTotal(lineItemRow);
			var popOverTitle = discountUI.find('.popover_title').find('.variable').text(productTotal).closest('.popover_title').text();
			element.popover({
				'content': discountUI,
				'html': true,
				'placement': 'top',
				'animation': true,
				'title': popOverTitle,
				'trigger': 'manual',
				'template': template,
				'container': self.lineItemsHolder

			});
			element.one('shown.bs.popover', function (e) {
				callBackFunction(element, jQuery('.discountForm'));
				if (element.next('.popover').find('.popover-content').height() > 300) {
					app.helper.showScroll(element.next('.popover').find('.popover-content'), { 'height': '300px' });
				}
			})
			element.popover('toggle');
		});
	},

	registerFinalDiscountShowEvent: function () {
		var self = this;
		var finalDiscountUI = jQuery('#finalDiscountUI').clone(true, true).removeClass('hide');
		jQuery('#finalDiscountUI').remove();

		var popOverTemplate = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate).css('opacity', 0).css('z-index', '-1');
		this.finalDiscountEle.popover({
			'content': finalDiscountUI,
			'html': true,
			'placement': 'left',
			'animation': true,
			'title': 'Discount',
			'trigger': 'manual',
			'template': popOverTemplate

		});
		this.finalDiscountEle.on('shown.bs.popover', function () {
			if (jQuery(this.finalDiscountEle).next('.popover').find('.popover-content').height() > 300) {
				app.helper.showScroll(jQuery(this.finalDiscountEle).next('.popover').find('.popover-content'), { 'height': '300px' });
			}
			var finalDiscountUI = jQuery('#finalDiscountUI');
			var finalDiscountPopOver = finalDiscountUI.closest('.popover');
			finalDiscountPopOver.find('.popoverButton').on('click', function (e) {
				var validate = finalDiscountUI.find('input').valid();
				if (validate) {
					finalDiscountUI.closest('.popover').css('opacity', 0).css('z-index', '-1');
					self.finalDiscountChangeActions();
				}
			});
		});
		this.finalDiscountEle.popover('show');
		var popOverId = this.finalDiscountEle.attr('aria-describedby');
		var popOverEle = jQuery('#' + popOverId);

		//update local cache element
		this.finalDiscountUIEle = jQuery('#finalDiscountUI');

		this.finalDiscountEle.on('click', function (e) {
			self.getForm().find('.popover.lineItemPopover').css('opacity', 0).css('z-index', '-1');

			if (popOverEle.css('opacity') == '0') {
				self.finalDiscountEle.popover('show');
				popOverEle.find('.popover-title').text(popOverEle.find('.popover_title').text());
				popOverEle.css('opacity', 1).css('z-index', '');
			} else {
				popOverEle.css('opacity', 0).css('z-index', '-1');
			}
		});
	},

	registerFinalDiscountChangeEvent: function () {
		var self = this;
		this.finalDiscountUIEle.on('change', '.finalDiscounts', function (e) {
			var element = jQuery(e.currentTarget);
			var discountContainer = self.finalDiscountUIEle;
			var element = discountContainer.find('input.finalDiscounts').filter(':checked');
			var discountType = element.data('discountType');

			jQuery('#discount_type_final').val(discountType);
			var rowPercentageField = discountContainer.find('input.discount_percentage_final');
			var rowAmountField = discountContainer.find('input.discount_amount_final');

			//intially making percentage and amount discount fields as hidden
			rowPercentageField.addClass('hide');
			rowAmountField.addClass('hide');

			if (discountType == Inventory_Edit_Js.percentageDiscountType) {
				rowPercentageField.removeClass('hide').focus();
			} else if (discountType == Inventory_Edit_Js.directAmountDiscountType) {
				rowAmountField.removeClass('hide').focus();
			}
			if (element.closest('form').valid()) {
				self.finalDiscountChangeActions();
			}
		});
	},

	registerChargeBlockShowEvent: function () {
		var self = this;
		var chargesTrigger = jQuery('#charges');
		var chargesUI = this.chargesContainer.removeClass('hide');

		var popOverTemplate = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate).css('opacity', 0).css('z-index', '-1');
		chargesTrigger.popover({
			'content': chargesUI,
			'html': true,
			'placement': 'left',
			'animation': true,
			'title': chargesTrigger.text(),
			'trigger': 'manual',
			'template': popOverTemplate

		});

		chargesTrigger.on('shown.bs.popover', function () {
			if (chargesTrigger.next('.popover').find('.popover-content').height() > 300) {
				app.helper.showScroll(chargesTrigger.next('.popover').find('.popover-content'), { 'height': '300px' });
			}
			var chargesForm = jQuery('#chargesBlock').closest('.lineItemPopover');

			chargesForm.find('.popoverButton').on('click', function (e) {
				var validate = chargesForm.find('input').valid();
				if (validate) {
					chargesForm.closest('.popover').css('opacity', 0).css('z-index', '-1');
					self.calculateCharges();
				}
			});
		});

		chargesTrigger.popover('show');
		var popOverId = chargesTrigger.attr('aria-describedby');
		var popOverEle = jQuery('#' + popOverId);

		chargesTrigger.on('click', function (e) {
			self.getForm().find('.popover.lineItemPopover').css('opacity', 0).css('z-index', '-1');

			if (popOverEle.css('opacity') == '0') {
				chargesTrigger.popover('show');
				popOverEle.css('opacity', 1).css('z-index', '');
			} else {
				chargesTrigger.popover('hide');
				popOverEle.css('opacity', 0).css('z-index', '-1');
			}
		});

	},

	registerChargeBlockChangeEvent: function () {
		var self = this;
		var chargesBlockContainer = this.chargesContainer;

		chargesBlockContainer.on('focusout', '.chargePercent,.chargeValue', function (e) {
			var element = jQuery(e.currentTarget);
			if (element.closest('form').valid()) {
				self.calculateCharges();
			}
		});

		this.calculateCharges();
	},

	registerGroupTaxShowEvent: function () {
		var self = this;
		var finalTaxTriggerer = jQuery('#finalTax');
		var finalTaxUI = jQuery('#group_tax_row').find('.finalTaxUI').removeClass('hide');

		var popOverTemplate = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate).css('opacity', 0).css('z-index', '-1');
		finalTaxTriggerer.popover({
			'content': finalTaxUI,
			'html': true,
			'placement': 'left',
			'animation': true,
			'title': finalTaxUI.find('.popover_title').val(),
			'trigger': 'manual',
			'template': popOverTemplate

		});

		finalTaxTriggerer.on('shown.bs.popover', function () {
			var finalTaxForm = jQuery('#group_tax_row').find('.finalTaxUI').closest('.lineItemPopover');
			if (finalTaxTriggerer.next('.popover').find('.popover-content').height() > 300) {
				app.helper.showScroll(finalTaxTriggerer.next('.popover').find('.popover-content'), { 'height': '300px' });
			}

			finalTaxForm.find('.popoverButton').on('click', function (e) {
				var validate = finalTaxForm.find('input').valid();
				if (validate) {
					finalTaxForm.closest('.popover').css('opacity', 0).css('z-index', '-1');
					self.calculateGroupTax();
					self.calculateGrandTotal();
				}
			});
		});

		finalTaxTriggerer.popover('show');
		var popOverId = finalTaxTriggerer.attr('aria-describedby');
		var popOverEle = jQuery('#' + popOverId);

		finalTaxTriggerer.on('click', function (e) {
			self.getForm().find('.popover.lineItemPopover').css('opacity', 0).css('z-index', '-1');

			if (popOverEle.css('opacity') == '0') {
				finalTaxTriggerer.popover('show');
				popOverEle.css('opacity', 1).css('z-index', '');
			} else {
				finalTaxTriggerer.popover('hide');
				popOverEle.css('opacity', 0).css('z-index', '-1');
			}
		});
	},

	registerGroupTaxChangeEvent: function () {
		var self = this;
		var groupTaxContainer = jQuery('#group_tax_row');

		groupTaxContainer.on('focusout', '.groupTaxPercentage', function (e) {
			if (groupTaxContainer.find('.finalTaxUI').closest('form').valid()) {
				self.calculateGroupTax();
				self.calculateGrandTotal();
			}
		});

	},

	registerChargeTaxesShowEvent: function () {
		var self = this;
		var chargeTaxTriggerer = jQuery('#chargeTaxes');
		var chargeTaxesUI = this.chargeTaxesContainer.removeClass('hide');

		var popOverTemplate = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate).css('opacity', 0).css('z-index', '-1');
		chargeTaxTriggerer.popover({
			'content': chargeTaxesUI,
			'html': true,
			'placement': 'left',
			'animation': true,
			'title': 'Discount',
			'trigger': 'manual',
			'template': popOverTemplate

		});

		chargeTaxTriggerer.on('shown.bs.popover', function () {
			if (chargeTaxTriggerer.next('.popover').find('.popover-content').height() > 300) {
				app.helper.showScroll(chargeTaxTriggerer.next('.popover').find('.popover-content'), { 'height': '300px' });
			}
			var chargesTaxForm = self.chargeTaxesContainer.closest('.lineItemPopover');

			chargesTaxForm.find('.popoverButton').on('click', function (e) {
				var validate = chargesTaxForm.find('input').valid();
				if (validate) {
					chargesTaxForm.closest('.popover').css('opacity', 0).css('z-index', '-1');
					self.calculateChargeTaxes();
				}
			});
		});

		chargeTaxTriggerer.popover('show');
		var popOverId = chargeTaxTriggerer.attr('aria-describedby');
		var popOverEle = jQuery('#' + popOverId);

		chargeTaxTriggerer.on('click', function (e) {
			self.getForm().find('.popover.lineItemPopover').css('opacity', 0).css('z-index', '-1');

			if (popOverEle.css('opacity') == '0') {
				chargeTaxTriggerer.popover('show');
				popOverEle.find('.popover-title').text(popOverEle.find('.popover_title').text());
				popOverEle.css('opacity', 1).css('z-index', '');
			} else {
				chargeTaxTriggerer.popover('hide');
				popOverEle.css('opacity', 0).css('z-index', '-1');
			}
		});
	},

	registerChargeTaxesChangeEvent: function () {
		var self = this;

		this.chargeTaxesContainer.on('focusout', '.chargeTaxPercentage', function (e) {
			if (self.chargeTaxesContainer.closest('form').valid()) {
				self.calculateChargeTaxes();
			}
		});

		this.calculateChargeTaxes();
	},

	registerDeductTaxesShowEvent: function () {
		var self = this;
		var deductTaxesTriggerer = jQuery('#deductTaxes');
		var deductTaxForm = this.dedutTaxesContainer.removeClass('hide');

		var popOverTemplate = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate).css('opacity', 0).css('z-index', '-1');
		deductTaxesTriggerer.popover({
			'content': deductTaxForm,
			'html': true,
			'placement': 'left',
			'animation': true,
			'title': deductTaxesTriggerer.text(),
			'trigger': 'manual',
			'template': popOverTemplate

		});

		deductTaxesTriggerer.on('shown.bs.popover', function () {
			if (deductTaxesTriggerer.next('.popover').find('.popover-content').height() > 300) {
				app.helper.showScroll(deductTaxesTriggerer.next('.popover').find('.popover-content'), { 'height': '300px' });
			}
			var deductTaxForm = self.dedutTaxesContainer.closest('.lineItemPopover');

			deductTaxForm.find('.popoverButton').on('click', function (e) {
				var validate = deductTaxForm.find('input').valid();
				if (validate) {
					deductTaxForm.closest('.popover').css('opacity', 0).css('z-index', '-1');
					self.calculateDeductTaxes();
				}
			});
		});

		deductTaxesTriggerer.popover('show');
		var popOverId = deductTaxesTriggerer.attr('aria-describedby');
		var popOverEle = jQuery('#' + popOverId);

		deductTaxesTriggerer.on('click', function (e) {
			self.getForm().find('.popover.lineItemPopover').css('opacity', 0).css('z-index', '-1');

			if (popOverEle.css('opacity') == '0') {
				deductTaxesTriggerer.popover('show');
				popOverEle.css('opacity', 1).css('z-index', '');
			} else {
				deductTaxesTriggerer.popover('hide');
				popOverEle.css('opacity', 0).css('z-index', '-1');
			}
		});
	},

	registerDeductTaxesChangeEvent: function () {
		var self = this;

		this.dedutTaxesContainer.on('focusout', '.deductTaxPercentage', function (e) {
			if (self.dedutTaxesContainer.closest('form').valid()) {
				self.calculateDeductTaxes();
			}
		});

		this.calculateDeductTaxes();
	},

	registerAdjustmentTypeChange: function () {
		var self = this;
		this.adjustmentTypeEles.on('change', function (e) {
			self.adjustmentEle.trigger('focusout');
		});
	},

	registerAdjustmentValueChange: function () {
		var self = this;
		this.adjustmentEle.on('focusout', function (e) {
			var element = jQuery(e.currentTarget);
			if (self.getForm().data('validator').element(element)) {
				var value = element.val();
				if (value == "") {
					element.val("0");
				}
				self.calculateGrandTotal();
			}
		});
	},

	registerRegionChangeEvent: function () {
		var self = this;

		var chargeTaxesBlock = jQuery('.chargeTaxesBlock');

		this.regionElement.change(function (e) {
			var element = jQuery(e.currentTarget);
			var message = app.vtranslate('JS_CONFIRM_TAXES_AND_CHARGES_REPLACE');
			app.helper.showConfirmationBox({ 'message': message }).then(
				function (e) {
					var prevRegionId = jQuery('#prevRegionId').val();
					var selectedRegion = element.find('option:selected');
					var selectedRegionId = selectedRegion.val();
					var info = selectedRegion.data('info');
					var selectedCurrencyId = jQuery('#selectedCurrencyId').val();

					self.lineItemsHolder.find('tr.' + self.lineItemDetectingClass).each(function (index, domElement) {
						var lineItemRow = jQuery(domElement);
						var taxPercentages = jQuery('.taxPercentage', lineItemRow);
						jQuery.each(taxPercentages, function (index1, taxDomElement) {
							var taxPercentage = jQuery(taxDomElement);
							var regionsList = taxPercentage.data('regionsList');
							var value = regionsList['default'];
							if (selectedRegionId && regionsList[selectedRegionId]) {
								value = regionsList[selectedRegionId];
							}
							taxPercentage.val(parseFloat(value));
						});
						if (self.isIndividualTaxMode()) {
							self.calculateTaxForLineItem(lineItemRow);
						}
						self.calculateLineItemNetPrice(lineItemRow);
					});
					self.calculateNetTotal();
					self.calculateFinalDiscount();

					var taxes = info.taxes;
					for (var taxId in taxes) {
						element = self.groupTaxContainer.find('[name="tax' + taxId + '_group_percentage"]');
						element.val(parseFloat(taxes[taxId]['value']));
						element.data('compoundOn', taxes[taxId]['compoundOn']);
					}
					if (self.isGroupTaxMode()) {
						self.calculateGroupTax();
					}

					var charges = info.charges;
					for (var chargeId in charges) {
						var chargeInfo = charges[chargeId];
						var property = 'percent';
						var chargeValue = parseFloat(chargeInfo[property]);
						if (chargeInfo.hasOwnProperty('value')) {
							property = 'value';
							chargeValue = parseFloat(chargeInfo[property]) * parseFloat(jQuery('#conversion_rate').val());
						}
						self.chargesContainer.find('[name="charges[' + chargeId + '][' + property + ']"]').val(chargeValue);

						var chargeTaxes = chargeInfo['taxes'];
						for (var chargeTaxId in chargeTaxes) {
							element = self.chargeTaxesContainer.find('[name="charges[' + chargeId + '][taxes][' + chargeTaxId + ']"]');
							element.val(parseFloat(chargeTaxes[chargeTaxId]['value']));
							element.data('compoundOn', chargeTaxes[chargeTaxId]['compoundOn']);
						}
					}
					self.calculateCharges();
				},
				function (error, err) { });
		});
	},

	registerDeleteLineItemEvent: function () {
		var self = this;

		this.lineItemsHolder.on('click', '.deleteRow', function (e) {
			var element = jQuery(e.currentTarget);
			//removing the row
			self.getClosestLineItemRow(element).remove();
			self.checkLineItemRow();
			self.lineItemDeleteActions();
		});
	},

	registerClearLineItemSelection: function () {
		var self = this;

		this.lineItemsHolder.on('click', '.clearLineItem', function (e) {
			var elem = jQuery(e.currentTarget);
			var parentElem = elem.closest('td');
			self.clearLineItemDetails(parentElem);
			parentElem.find('input.productName').removeAttr('disabled').val('');
			e.preventDefault();
		});
	},
	registerDeleteLineItemEvent1: function () {
		var self = this;

		this.lineItemsHolder1.on('click', '.deleteRow', function (e) {
			var element = jQuery(e.currentTarget);
			//removing the row
			self.getClosestLineItemRow(element).remove();
			self.checkLineItemRow();
			self.lineItemDeleteActions();
		});
	},
	registerClearLineItemSelection1: function () {
		var self = this;

		this.lineItemsHolder1.on('click', '.clearLineItem', function (e) {
			var elem = jQuery(e.currentTarget);
			var parentElem = elem.closest('td');
			self.clearLineItemDetails(parentElem);
			parentElem.find('input.productName').removeAttr('disabled').val('');
			e.preventDefault();
		});
	},

	registerLineItemEvents: function () {
		this.registerQuantityChangeEvent();
		this.registerListPriceChangeEvent();
		this.registerPriceBookPopUp();
		this.registerLineItemTaxShowEvent();
		this.registerTaxTypeChange();
		this.registerCurrencyChangeEvent();
		this.registerLineItemDiscountShowEvent();

		this.registerFinalDiscountShowEvent();
		this.registerFinalDiscountChangeEvent();

		this.registerChargeBlockShowEvent();
		this.registerChargeBlockChangeEvent();

		this.registerGroupTaxShowEvent();
		this.registerGroupTaxChangeEvent();

		this.registerChargeTaxesShowEvent();
		this.registerChargeTaxesChangeEvent();

		this.registerDeductTaxesShowEvent();
		this.registerDeductTaxesChangeEvent();

		this.registerAdjustmentTypeChange();
		this.registerAdjustmentValueChange();

		this.registerRegionChangeEvent();
		this.registerDeleteLineItemEvent();

		this.registerClearLineItemSelection();
		var record = jQuery('[name="record"]').val();
		if (!record) {
			var container = this.lineItemsHolder;
			jQuery('.qty', container).trigger('focusout');
		}
	},

	registerSubmitEvent: function () {
		var self = this;
		var editViewForm = this.getForm();
		//this._super();
		editViewForm.submit(function (e) {
			var deletedItemInfo = jQuery('.deletedItem', editViewForm);
			if (deletedItemInfo.length > 0) {
				e.preventDefault();
				var msg = app.vtranslate('JS_PLEASE_REMOVE_LINE_ITEM_THAT_IS_DELETED');
				app.helper.showErrorNotification({ "message": msg });
				editViewForm.removeData('submit');
				return false;
			}
			// else if (jQuery('.lineItemRow').length <= 0) {
			// 	e.preventDefault();
			// 	msg = app.vtranslate('JS_NO_LINE_ITEM');
			// 	app.helper.showErrorNotification({ "message": msg });
			// 	editViewForm.removeData('submit');
			// 	return false;
			// }
			self.updateLineItemElementByOrder();
			var taxMode = self.isIndividualTaxMode();
			var elementsList = self.lineItemsHolder.find('.' + self.lineItemDetectingClass);
			//			jQuery.each(elementsList, function(index, element) {
			//				var lineItemRow = jQuery(element);
			//				thisInstance.calculateDiscountForLineItem(lineItemRow);
			//				if (taxMode) {
			//					thisInstance.calculateTaxForLineItem(lineItemRow);
			//				}
			//				thisInstance.calculateLineItemNetPrice(lineItemRow);
			//			});
			//thisInstance.lineItemToTalResultCalculations();
			self.saveProductCount();
			self.saveProductCount1();
			self.saveProductCount2();
			self.saveSubTotalValue();
			self.saveTotalValue();
			self.savePreTaxTotalValue();
			return true;
		})
	},

	makeLineItemsSortable: function () {
		var self = this;
		this.lineItemsHolder.sortable({
			'containment': this.lineItemsHolder,
			'items': 'tr.' + this.lineItemDetectingClass,
			'revert': true,
			'tolerance': 'pointer',
			'helper': function (e, ui) {
				//while dragging helper elements td element will take width as contents width
				//so we are explicity saying that it has to be same width so that element will not
				//look like distrubed
				ui.children().each(function (index, element) {
					element = jQuery(element);
					element.width(element.width());
				})
				return ui;
			}
		}).mousedown(function (event) {
			//TODO : work around for issue of mouse down even hijack in sortable plugin
			self.getClosestLineItemRow(jQuery(event.target)).find('input:focus').trigger('focusout');
		});
	},

	/**
	 * Function to register event for copying addresses
	 */
	registerEventForCopyAddress: function () {
		var self = this;
		jQuery('[name="copyAddressFromRight"],[name="copyAddressFromLeft"]').change(function () {
			var element = jQuery(this);
			var elementClass = element.attr('class');
			var targetCopyAddress = element.data('copyAddress');
			var objectToMapAddress;
			if (elementClass == "accountAddress") {
				var recordRelativeAccountId = jQuery('[name="account_id"]').val();
				if (typeof recordRelativeAccountId == 'undefined') {
					app.helper.showErrorNotification({ 'message': app.vtranslate('JS_RELATED_ACCOUNT_IS_NOT_AVAILABLE') });
					return;
				}
				if (recordRelativeAccountId == "" || recordRelativeAccountId == "0") {
					app.helper.showErrorNotification({ 'message': app.vtranslate('JS_PLEASE_SELECT_AN_ACCOUNT_TO_COPY_ADDRESS') });
				} else {
					var recordRelativeAccountName = jQuery('#account_id_display').val();
					var data = {
						'record': recordRelativeAccountId,
						'selectedName': recordRelativeAccountName,
						'source_module': "Accounts"
					}
					if (targetCopyAddress == "billing") {
						objectToMapAddress = self.addressFieldsMappingBetweenModules['AccountsBillMap'];
					} else if (targetCopyAddress == "shipping") {
						objectToMapAddress = self.addressFieldsMappingBetweenModules['AccountsShipMap'];
					}
					self.copyAddressDetails(data, element.closest('table'), objectToMapAddress);
					element.attr('checked', 'checked');
				}
			} else if (elementClass == "contactAddress") {
				var recordRelativeContactId = jQuery('[name="contact_id"]').val();
				if (typeof recordRelativeContactId == 'undefined') {
					app.helper.showErrorNotification({ 'message': app.vtranslate('JS_RELATED_CONTACT_IS_NOT_AVAILABLE') });
					return;
				}
				if (recordRelativeContactId == "" || recordRelativeContactId == "0") {
					app.helper.showErrorNotification({ 'message': app.vtranslate('JS_PLEASE_SELECT_AN_RELATED_TO_COPY_ADDRESS') });
				} else {
					var recordRelativeContactName = jQuery('#contact_id_display').val();
					var editViewLabel = jQuery('#contact_id_display').closest('td');
					var editViewSelection = jQuery(editViewLabel).find('input[name="popupReferenceModule"]').val();
					var data = {
						'record': recordRelativeContactId,
						'selectedName': recordRelativeContactName,
						source_module: editViewSelection
					}

					if (targetCopyAddress == "billing") {
						objectToMapAddress = self.addressFieldsMappingBetweenModules[editViewSelection + 'BillMap'];
					} else if (targetCopyAddress == "shipping") {
						objectToMapAddress = self.addressFieldsMappingBetweenModules[editViewSelection + 'ShipMap'];
					}
					self.copyAddressDetails(data, element.closest('table'), objectToMapAddress);
					element.attr('checked', 'checked');
				}
			} else if (elementClass == "shippingAddress") {
				var target = element.data('target');
				if (target == "shipping") {
					var swapMode = "true";
				}
				self.copyAddress(swapMode);
			} else if (elementClass == "billingAddress") {
				var target = element.data('target');
				if (target == "billing") {
					var swapMode = "false";
				}
				self.copyAddress(swapMode);
			}
		})
		jQuery('[name="copyAddress"]').on('click', function (e) {
			var element = jQuery(e.currentTarget);
			var swapMode;
			var target = element.data('target');
			if (target == "billing") {
				swapMode = "false";
			} else if (target == "shipping") {
				swapMode = "true";
			}
			self.copyAddress(swapMode);
		})
	},

	/**
	 * Function to toggle shipping and billing address according to layout
	 */
	registerForTogglingBillingandShippingAddress: function () {
		var billingAddressPosition = jQuery('[name="bill_street"]').closest('td').index();
		var copyAddress1Block = jQuery('[name="copyAddress1"]');
		var copyAddress2Block = jQuery('[name="copyAddress2"]');
		var copyHeader1 = jQuery('[name="copyHeader1"]');
		var copyHeader2 = jQuery('[name="copyHeader2"]');
		var copyAddress1toggleAddressLeftContainer = copyAddress1Block.find('[name="togglingAddressContainerLeft"]');
		var copyAddress1toggleAddressRightContainer = copyAddress1Block.find('[name="togglingAddressContainerRight"]');
		var copyAddress2toggleAddressLeftContainer = copyAddress2Block.find('[name="togglingAddressContainerLeft"]')
		var copyAddress2toggleAddressRightContainer = copyAddress2Block.find('[name="togglingAddressContainerRight"]');
		var headerText1 = copyHeader1.html();
		var headerText2 = copyHeader2.html();

		if (billingAddressPosition == 3) {
			if (copyAddress1toggleAddressLeftContainer.hasClass('hide')) {
				copyAddress1toggleAddressLeftContainer.removeClass('hide');
			}
			copyAddress1toggleAddressRightContainer.addClass('hide');
			if (copyAddress2toggleAddressRightContainer.hasClass('hide')) {
				copyAddress2toggleAddressRightContainer.removeClass('hide');
			}
			copyAddress2toggleAddressLeftContainer.addClass('hide');
			copyHeader1.html(headerText2);
			copyHeader2.html(headerText1);
			copyAddress1Block.find('[data-copy-address]').each(function () {
				jQuery(this).data('copyAddress', 'shipping');
			})
			copyAddress2Block.find('[data-copy-address]').each(function () {
				jQuery(this).data('copyAddress', 'billing');
			})
		}
	},

	registerLineItemAutoComplete: function (container) {
		var self = this;
		if (typeof container == 'undefined') {
			container = this.lineItemsHolder;
		}
		container.find('input.autoComplete2').autocomplete({
			'minLength': '3',
			'source': function (request, response) {
				//element will be array of dom elements
				//here this refers to auto complete instance
				var inputElement = jQuery(this.element[0]);
				var tdElement = inputElement.closest('td');
				var searchValue = request.term;
				var params = {};
				params.search_module = "Vendors";
				params.search_value = searchValue;
				self.searchModuleNames(params).then(function (data) {
					var reponseDataList = new Array();
					var serverDataFormat = data;
					if (serverDataFormat.length <= 0) {
						serverDataFormat = new Array({
							'label': app.vtranslate('JS_NO_RESULTS_FOUND'),
							'type': 'no results'
						});
					}
					for (var id in serverDataFormat) {
						var responseData = serverDataFormat[id];
						reponseDataList.push(responseData);
					}
					response(reponseDataList);
				});
			},
			'select': function (event, ui) {
				var selectedItemData = ui.item;
				//To stop selection if no results is selected
				if (typeof selectedItemData.type != 'undefined' && selectedItemData.type == "no results") {
					return false;
				}
				var element = jQuery(this);
				var parent = element.closest('td');
				if (parent.length == 0) {
					parent = element.closest('.fieldValue');
				}
				var sourceField = parent.find('.sourceField');
				selectedItemData.record = selectedItemData.id;
				selectedItemData.source_module = parent.find('input[name="popupReferenceModule"]').val();
				selectedItemData.selectedName = selectedItemData.label;
				var fieldName = sourceField.attr("name");
				parent.find('input[name="' + fieldName + '"]').val(selectedItemData.id);
				element.attr("value", selectedItemData.id);
				element.data("value", selectedItemData.id);
				parent.find('.clearReferenceSelection').removeClass('hide');
				parent.find('.referencefield-wrapper').addClass('selected');
				element.attr("disabled", "disabled");
				//trigger reference field selection event
				sourceField.trigger(Vtiger_Edit_Js.referenceSelectionEvent, selectedItemData);
				//trigger post reference selection
				sourceField.trigger(Vtiger_Edit_Js.postReferenceSelectionEvent, { 'data': selectedItemData });
			},
			'change': function (event, ui) {
				var element = jQuery(this);
				//if you dont have disabled attribute means the user didnt select the item
				if (element.attr('disabled') == undefined) {
					element.closest('td').find('.clearLineItem').trigger('click');
				}
			}
			//		}).each(function() {
			//			jQuery(this).data('autocomplete')._renderItem = function(ul, item) {
			//				var term = this.element.val();
			//				var regex = new RegExp('('+term+')', 'gi');
			//				var htmlContent = item.label.replace(regex, '<b>$&</b>');
			//				return jQuery('<li></li>').data('item.autocomplete', item).append(jQuery('<a></a>').html(htmlContent)).appendTo(ul);
			//			};
		});

		container.find('input.autoComplete').autocomplete({
			'minLength': '3',
			'source': function (request, response) {
				//element will be array of dom elements
				//here this refers to auto complete instance
				var inputElement = jQuery(this.element[0]);
				var tdElement = inputElement.closest('td');
				var searchValue = request.term;
				var params = {};
				var searchModule = tdElement.find('.lineItemPopup').data('moduleName');
				params.search_module = searchModule
				params.search_value = searchValue;
				self.searchModuleNames(params).then(function (data) {
					var reponseDataList = new Array();
					var serverDataFormat = data;
					if (serverDataFormat.length <= 0) {
						serverDataFormat = new Array({
							'label': app.vtranslate('JS_NO_RESULTS_FOUND'),
							'type': 'no results'
						});
					}
					for (var id in serverDataFormat) {
						var responseData = serverDataFormat[id];
						reponseDataList.push(responseData);
					}
					response(reponseDataList);
				});
			},
			'select': function (event, ui) {
				var selectedItemData = ui.item;
				//To stop selection if no results is selected
				if (typeof selectedItemData.type != 'undefined' && selectedItemData.type == "no results") {
					return false;
				}
				var element = jQuery(this);
				element.attr('disabled', 'disabled');
				var tdElement = element.closest('td');
				var selectedModule = tdElement.find('.lineItemPopup').data('moduleName');
				var popupElement = tdElement.find('.lineItemPopup');
				var dataUrl = "index.php?module=Inventory&action=GetTaxes&record=" + selectedItemData.id + "&currency_id=" + jQuery('#currency_id option:selected').val() + "&sourceModule=" + app.getModuleName();
				let value = $('.selectedModuleId');
				let self2 = this;
				for (let i = 0; i < value.length; i++) {
					let dub
					if ($(value[i]).val() == selectedItemData.id) {
						app.helper.showConfirmationBox({ 'message': "This value already Exits" }).then(
							function (e) {
								var element = jQuery(self2);
								element.closest('td').find('.clearLineItem').trigger('click');
								return false;
							},
							function (error, err) {
								var element = jQuery(self2);
								element.closest('td').find('.clearLineItem').trigger('click');
								return false;
							}
						);
						break;

					}
				}
				app.request.get({ 'url': dataUrl }).then(
					function (error, data) {
						if (error == null) {
							var itemRow = self.getClosestLineItemRow(element)
							itemRow.find('.lineItemType').val(selectedModule);
							self.mapResultsToFields(itemRow, data[0]);
						}
					},
					function (error, err) {

					}
				);

			},
			'change': function (event, ui) {
				var element = jQuery(this);
				//if you dont have disabled attribute means the user didnt select the item
				if (element.attr('disabled') == undefined) {
					element.closest('td').find('.clearLineItem').trigger('click');
				}
			}
			//		}).each(function() {
			//			jQuery(this).data('autocomplete')._renderItem = function(ul, item) {
			//				var term = this.element.val();
			//				var regex = new RegExp('('+term+')', 'gi');
			//				var htmlContent = item.label.replace(regex, '<b>$&</b>');
			//				return jQuery('<li></li>').data('item.autocomplete', item).append(jQuery('<a></a>').html(htmlContent)).appendTo(ul);
			//			};
		});

		container.find('input.autoComplete1').autocomplete({
			'minLength': '3',
			'source': function (request, response) {
				//element will be array of dom elements
				//here this refers to auto complete instance
				var inputElement = jQuery(this.element[0]);
				var tdElement = inputElement.closest('td');
				var searchValue = request.term;
				var params = {};
				var searchModule = tdElement.find('.lineItemPopup1').data('moduleName');
				params.search_module = searchModule
				params.search_value = searchValue;
				self.searchModuleNames(params).then(function (data) {
					var reponseDataList = new Array();
					var serverDataFormat = data;
					if (serverDataFormat.length <= 0) {
						serverDataFormat = new Array({
							'label': app.vtranslate('JS_NO_RESULTS_FOUND'),
							'type': 'no results'
						});
					}
					for (var id in serverDataFormat) {
						var responseData = serverDataFormat[id];
						reponseDataList.push(responseData);
					}
					response(reponseDataList);
				});
			},
			'select': function (event, ui) {
				var selectedItemData = ui.item;
				//To stop selection if no results is selected
				if (typeof selectedItemData.type != 'undefined' && selectedItemData.type == "no results") {
					return false;
				}
				var element = jQuery(this);
				element.attr('disabled', 'disabled');
				var tdElement = element.closest('td');
				var selectedModule = tdElement.find('.lineItemPopup1').data('moduleName');
				var popupElement = tdElement.find('.lineItemPopup1');
				let value = $('.selectedModuleId');
				let self2 = this;
				for (let i = 0; i < value.length; i++) {
					let dub
					if ($(value[i]).val() == selectedItemData.id) {
						app.helper.showConfirmationBox({ 'message': "This value already Exits" }).then(
							function (e) {
								var element = jQuery(self2);
								element.closest('td').find('.clearLineItem').trigger('click');
								return false;
							},
							function (error, err) {
								var element = jQuery(self2);
								element.closest('td').find('.clearLineItem').trigger('click');
								return false;
							}
						);
						break;

					}
				}
				var dataUrl = "index.php?module=Inventory&action=GetTaxes&record=" + selectedItemData.id + "&currency_id=" + jQuery('#currency_id option:selected').val() + "&sourceModule=" + app.getModuleName();
				app.request.get({ 'url': dataUrl }).then(
					function (error, data) {
						if (error == null) {
							var itemRow = self.getClosestLineItemRow(element)
							itemRow.find('.lineItemType').val(selectedModule);
							self.mapResultsToFields(itemRow, data[0]);
						}
					},
					function (error, err) {

					}
				);
			},
			'change': function (event, ui) {
				var element = jQuery(this);
				//if you dont have disabled attribute means the user didnt select the item
				if (element.attr('disabled') == undefined) {
					element.closest('td').find('.clearLineItem').trigger('click');
				}
			}
			//		}).each(function() {
			//			jQuery(this).data('autocomplete')._renderItem = function(ul, item) {
			//				var term = this.element.val();
			//				var regex = new RegExp('('+term+')', 'gi');
			//				var htmlContent = item.label.replace(regex, '<b>$&</b>');
			//				return jQuery('<li></li>').data('item.autocomplete', item).append(jQuery('<a></a>').html(htmlContent)).appendTo(ul);
			//			};
		});
	},

	/**
	 * Function which will register event for Reference Fields Selection
	 */
	registerReferenceSelectionEvent: function (container) {
		var self = this;

		jQuery('input[name="contact_id"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, data) {
			self.referenceSelectionEventHandler(data, container);
		});
	},

	/**
	 * Reference Fields Selection Event Handler
	 */
	referenceSelectionEventHandler: function (data, container) {
		var self = this;
		if (data['selectedName']) {
			var message = app.vtranslate('OVERWRITE_EXISTING_MSG1') + app.vtranslate('SINGLE_' + data['source_module']) + ' (' + data['selectedName'] + ') ' + app.vtranslate('OVERWRITE_EXISTING_MSG2');
			app.helper.showConfirmationBox({ 'message': message }).then(
				function (e) {
					self.copyAddressDetails(data, container);
				},
				function (error, err) {
				});
		}
	},

	registerPopoverCancelEvent: function () {
		this.getForm().on('click', '.popover .popoverCancel', function (e) {
			e.preventDefault();
			var element = jQuery(e.currentTarget);
			var popOverEle = element.closest('.popover');
			var validate = popOverEle.find('input').valid();
			if (!validate) {
				popOverEle.find('.input-error').val(0).valid();
			}
			popOverEle.css('opacity', 0).css('z-index', '-1');

		});
	},

	disableSpecificFieldTypes: function () {
		let reportType = $("select[name='sr_ticket_type']").val();
		if (reportType == "PERIODICAL MAINTENANCE") {
			$('[data-fieldname="fail_de_sap_noti_type"]').attr("readonly", 'readonly');
		}
	},
	warrentableyorn: function () {
		let warrantableORNOt = $('input[name="war_warable"]').val();
		if (warrantableORNOt == 'Not Warrantable') {
			$("#addProduct").addClass("hide");
			$("#PartsfieldBlockContainer").addClass("hide");
			$("#VISUAL_CHECKShideOrShowId").addClass("hide");
			$("#GENERAL_CHECKShideOrShowId").addClass("hide");
		} else if (warrantableORNOt == 'Warrantable') {
			$("#addProduct").removeClass("hide");
			$("#PartsfieldBlockContainer").removeClass("hide");
			$("#VISUAL_CHECKShideOrShowId").removeClass("hide");
			$("#GENERAL_CHECKShideOrShowId").removeClass("hide");
		}
	},
	dependencyVendorNameShowOrHide: function () {
		let self = this;
		self.lineItemsHolder.find('tr.' + self.lineItemDetectingClass).each(function (index, domElement) {
			var lineItemRow = jQuery(domElement);
			let val = lineItemRow.find('select[data-extraname="sr_replace_action"]').val();
			if (val == 'From Vendor Stock') {
				lineItemRow.find('#vendor_itemDivCla').removeClass('hide');
				lineItemRow.find('#line_vendor_idDivCla').removeClass('hide');
			} else {
				lineItemRow.find('#vendor_itemDivCla').addClass('hide');
				lineItemRow.find('#line_vendor_idDivCla').addClass('hide');
			}
		});
		$('select[data-extraname="sr_replace_action"]').change(function () {
			if ($(this).val() == 'From Vendor Stock') {
				$(this).closest('tr').find('#vendor_itemDivCla').removeClass('hide');
				$(this).closest('tr').find('#line_vendor_idDivCla').removeClass('hide');
			} else {
				$(this).closest('tr').find('#vendor_itemDivCla').addClass('hide');
				$(this).closest('tr').find('#line_vendor_idDivCla').addClass('hide');
			}
		});
	},

	dependencyWarrantyApplicable: function () {
		let self = this;
		let type = $('select[name="sr_ticket_type"]').val();
		let purpose = $('select[name="tck_det_purpose"]').val();
		if (type == 'INSTALLATION OF SUB ASSEMBLY FITMENT') {
			self.lineItemsHolder1.find('tr.' + self.lineItemDetectingClass).each(function (index, domElement) {
				var lineItemRow = jQuery(domElement);
				let val = lineItemRow.find('input:radio[data-extraname="sad_war_term_app"]:checked').val();
				if (val == 'YES') {
					lineItemRow.find('#sad_sub_ass_monDivCla').removeClass('hide');
					lineItemRow.find('#sad_sub_ass_hmrDivCla').removeClass('hide');
					lineItemRow.find('#sad_sub_ass_kmDivCla').removeClass('hide');
					lineItemRow.find('#sad_war_termDivCla').removeClass('hide');
					lineItemRow.find('#sad_war_start_conDivCla').removeClass('hide');
					lineItemRow.find('#sad_date_oracsDivCla').removeClass('hide');

				} else {
					lineItemRow.find('#sad_sub_ass_monDivCla').addClass('hide');
					lineItemRow.find('#sad_sub_ass_hmrDivCla').addClass('hide');
					lineItemRow.find('#sad_sub_ass_kmDivCla').addClass('hide');
					lineItemRow.find('#sad_war_termDivCla').addClass('hide');
					lineItemRow.find('#sad_war_start_conDivCla').addClass('hide');
					lineItemRow.find('#sad_date_oracsDivCla').addClass('hide');
				}
				let rowNum = lineItemRow.find(".rowNumber").val();
				$("#sad_podate" + rowNum).datepicker({
				});
			});
			$('input:radio[data-extraname="sad_war_term_app"]').change(function () {
				if ($(this).val() == 'YES') {
					$(this).closest('tr').find('#sad_sub_ass_monDivCla').removeClass('hide');
					$(this).closest('tr').find('#sad_sub_ass_hmrDivCla').removeClass('hide');
					$(this).closest('tr').find('#sad_sub_ass_kmDivCla').removeClass('hide');
					$(this).closest('tr').find('#sad_war_termDivCla').removeClass('hide');
					$(this).closest('tr').find('#sad_war_start_conDivCla').removeClass('hide');
					$(this).closest('tr').find('#sad_date_oracsDivCla').removeClass('hide');
				} else {
					$(this).closest('tr').find('#sad_sub_ass_monDivCla').addClass('hide');
					$(this).closest('tr').find('#sad_sub_ass_hmrDivCla').addClass('hide');
					$(this).closest('tr').find('#sad_sub_ass_kmDivCla').addClass('hide');
					$(this).closest('tr').find('#sad_war_termDivCla').addClass('hide');
					$(this).closest('tr').find('#sad_war_start_conDivCla').addClass('hide');
					$(this).closest('tr').find('#sad_date_oracsDivCla').addClass('hide');
				}
				self.lineItemsHolder1.find('tr.' + self.lineItemDetectingClass).each(function (index, domElement) {
					let element = $(this);
					let parent = element.closest('tr');
					let rowNum = parent.find(".rowNumber").val();
					$("#sad_date_oracs" + rowNum).datepicker({
					});
				});
			});
		}

		if (type == 'SERVICE FOR SPARES PURCHASED' && purpose == 'WARRANTY CLAIM FOR SUB ASSEMBLY / OTHER SPARE PARTS') {
			let preVal = $("input[name='sad_nl_war_term_app']:checked").val();
			if (preVal == 'Not Applicable') {
				$("#ShortagefieldBlockContainer").addClass("hide");
				$('#row1 #sad_sub_ass_monDivCla').addClass('hide');
				$('#row1 #sad_sub_ass_hmrDivCla').addClass('hide');
				$('#row1 #sad_sub_ass_kmDivCla').addClass('hide');
				$('#row1 #sad_war_termDivCla').addClass('hide');
				$('#row1 #sad_war_start_conDivCla').addClass('hide');
				$('#row1 #sad_date_oracsDivCla').addClass('hide');
			} else if (preVal == 'Applicable') {
				$("#ShortagefieldBlockContainer").removeClass("hide");
				$('#row1 #sad_sub_ass_monDivCla').removeClass('hide');
				$('#row1 #sad_sub_ass_hmrDivCla').removeClass('hide');
				$('#row1 #sad_sub_ass_kmDivCla').removeClass('hide');
				$('#row1 #sad_war_termDivCla').removeClass('hide');
				$('#row1 #sad_war_start_conDivCla').removeClass('hide');
				$('#row1 #sad_date_oracsDivCla').removeClass('hide');
			} else {
				$("#ShortagefieldBlockContainer").addClass("hide");
				$('#row1 #sad_sub_ass_monDivCla').addClass('hide');
				$('#row1 #sad_sub_ass_hmrDivCla').addClass('hide');
				$('#row1 #sad_sub_ass_kmDivCla').addClass('hide');
				$('#row1 #sad_war_termDivCla').addClass('hide');
				$('#row1 #sad_war_start_conDivCla').addClass('hide');
				$('#row1 #sad_date_oracsDivCla').addClass('hide');
			}
			$('input:radio[name="sad_nl_war_term_app"]').change(function () {
				let val = $("input[name='sad_nl_war_term_app']:checked").val();
				if (val == 'Not Applicable') {
					$("#ShortagefieldBlockContainer").addClass("hide");
					$('#row1 #sad_sub_ass_monDivCla').addClass('hide');
					$('#row1 #sad_sub_ass_hmrDivCla').addClass('hide');
					$('#row1 #sad_sub_ass_kmDivCla').addClass('hide');
					$('#row1 #sad_war_termDivCla').addClass('hide');
					$('#row1 #sad_war_start_conDivCla').addClass('hide');
					$('#row1 #sad_date_oracsDivCla').addClass('hide');
				} else if (val == 'Applicable') {
					$("#ShortagefieldBlockContainer").removeClass("hide");
					$('#row1 #sad_sub_ass_monDivCla').removeClass('hide');
					$('#row1 #sad_sub_ass_hmrDivCla').removeClass('hide');
					$('#row1 #sad_sub_ass_kmDivCla').removeClass('hide');
					$('#row1 #sad_war_termDivCla').removeClass('hide');
					$('#row1 #sad_war_start_conDivCla').removeClass('hide');
					$('#row1 #sad_date_oracsDivCla').removeClass('hide');

					self.lineItemsHolder1.find('tr.' + self.lineItemDetectingClass).each(function (index, domElement) {
						let element = $(this);
						let parent = element.closest('tr');
						let rowNum = parent.find(".rowNumber").val();
						if (rowNum == undefined || rowNum == null || rowNum == '') {
							rowNum = parent.attr("data-row-num");
						}
						$("#sad_date_oracs" + rowNum).datepicker({
						});
					});
				}
			});
			let warrantyVal = $("input[name='war_warable']:checked").val();
			if (warrantyVal == 'Warrantable') {
				$("#VISUAL_CHECKShideOrShowId").removeClass("hide");
				$("#GENERAL_CHECKShideOrShowId").removeClass("hide");
			} else if (warrantyVal == 'Not Warrantable') {
				$("#VISUAL_CHECKShideOrShowId").addClass("hide");
				$("#GENERAL_CHECKShideOrShowId").addClass("hide");
			} else {
				$("#VISUAL_CHECKShideOrShowId").addClass("hide");
				$("#GENERAL_CHECKShideOrShowId").addClass("hide");
			}
		}
		self.lineItemsHolder1.find('tr.' + self.lineItemDetectingClass).each(function (index, domElement) {
			var lineItemRow = jQuery(domElement);
			let val = lineItemRow.find('input:radio[data-extraname="sad_valid_sl_no"]:checked').val();
			if (val == 'No') {
				lineItemRow.find('#sad_ag_sl_noDivCla').removeClass('hide');
				lineItemRow.find('#sad_whoaDivCla').removeClass('hide');
				lineItemRow.find('#sad_dofDivCla').removeClass('hide');
				lineItemRow.find('#sad_manu_nameDivCla').removeClass('hide');
			} else {
				lineItemRow.find('#sad_ag_sl_noDivCla').addClass('hide');
				lineItemRow.find('#sad_whoaDivCla').addClass('hide');
				lineItemRow.find('#sad_dofDivCla').addClass('hide');
				lineItemRow.find('#sad_manu_nameDivCla').addClass('hide');
			}
		});
		$('input:radio[data-extraname="sad_valid_sl_no"]').change(function () {
			if ($(this).val() == 'No') {
				$(this).closest('tr').find('#sad_ag_sl_noDivCla').removeClass('hide');
				$(this).closest('tr').find('#sad_whoaDivCla').removeClass('hide');
				$(this).closest('tr').find('#sad_dofDivCla').removeClass('hide');
				$(this).closest('tr').find('#sad_manu_nameDivCla').removeClass('hide');
				let element = $(this);
				let parent = element.closest('tr');
				let rowNum = parent.find(".rowNumber").val();
				$("#sad_dof" + rowNum).datepicker({
				});
			} else {
				$(this).closest('tr').find('#sad_ag_sl_noDivCla').addClass('hide');
				$(this).closest('tr').find('#sad_whoaDivCla').addClass('hide');
				$(this).closest('tr').find('#sad_dofDivCla').addClass('hide');
				$(this).closest('tr').find('#sad_manu_nameDivCla').addClass('hide');
			}
		});
		$('.fa-calendar').click(function (e) {
			let element = $(this);
			let parent = element.closest('tr');
			let field = $(this).data("fieldname");
			if (parent) {
				if (field) {
					parent.find("#" + field).focus();
				}
			}
		});

		self.lineItemsHolder2.find('tr.' + self.lineItemDetectingClass).each(function (index, domElement) {
			var lineItemRow = jQuery(domElement);
			let val = lineItemRow.find('select[data-extraname="masn_manu"]').val();
			if (val == 'OTHERS') {
				lineItemRow.find('#masn_other_manuDivCla').removeClass('hide');
			} else {
				lineItemRow.find('#masn_other_manuDivCla').addClass('hide');
			}
		});
		$('select[data-extraname="masn_manu"]').change(function () {
			if ($(this).val() == 'OTHERS') {
				$(this).closest('tr').find('#masn_other_manuDivCla').removeClass('hide');
			} else {
				$(this).closest('tr').find('#masn_other_manuDivCla').addClass('hide');
			}
		});
	},

	InitialBlocking: function () {
		let noneditableKeys = $("input[name='NONEDITABLEKEYS']").data('value');
		let noneditableKeysLength = noneditableKeys.length;
		for (let i = 0; i < noneditableKeysLength; i++) {
			$("input[name='" + noneditableKeys[i] + "']").attr('readonly', 'readonly').css('background-color', '#eeeeee !important');
			$("input[name='" + noneditableKeys[i] + "']").css('pointer-events', 'none');
			$('[data-fieldname="' + noneditableKeys[i] + '"]').attr("readonly", 'readonly').css('background-color', '#eeeeee !important');
			$('[data-fieldname="' + noneditableKeys[i] + '[]"]').attr("readonly", 'readonly');
			let refFieldClass = 'refField' + noneditableKeys[i];
			$('.' + refFieldClass).addClass("hide");
			$("#" + noneditableKeys[i] + "_display").attr("placeholder", "");
			// $('.'+ noneditableKeys[i]).addClass("select2-container-disabled");
			// $('.'+ noneditableKeys[i]).addClass("select2-container-disabled");
			$('#ServiceReports_Edit_fieldName_' + noneditableKeys[i]).attr("readonly", 'readonly');
		}
		$("input[name='imagename[]']").attr('disabled', 'disabled');
		$('.disabledpicklistValue').attr("readonly", 'readonly');
	},

	next: function () {
		var block = $(".blockData");
		var next = $(".next");
		for (let i = 0; i < block.length; i++) {
			$(block[i]).attr('id', "block" + i);
			$(next[i]).attr('id', i)
			if (i == block.length - 1) {
				$(next[i]).addClass("hide");
			}
		}
		$(".next").click(function () {
			var idf = this.id;
			var block = $(".blockData");
			var next = $(".next");
			for (let i = 0; i < block.length - 1; i++) {
				$(block[i]).addClass("hide");
				if (i == parseInt(idf) + 1) {
					$(block[i]).removeClass("hide");
				}
				if (i == block.length - 2) {
					$(next[i + 1]).addClass("hide");
				}
			}
		});
	},
	handleOtherDependecy1: function (id, rowNum) {
		$(id).on('input', function (event) {
			let fieldName = $(this).data("fieldname");
			let value = event.target.value;
			if (value.length > 0) {
				if (fieldName == 'sad_sub_ass_hmr') {
					$("#sad_sub_ass_km" + rowNum).val('');
					$("#sad_sub_ass_km" + rowNum).prop("disabled", true);
					$("#ServiceReports_editView_fieldName_sacfd_km_run").val();
					$("#ServiceReports_editView_fieldName_sacfd_km_run").prop("disabled", true);
					$("#ServiceReports_editView_fieldName_sad_hmr").prop("disabled", false);
				} else {
					$("#sad_sub_ass_hmr" + rowNum).val('');
					$("#sad_sub_ass_hmr" + rowNum).prop("disabled", true);
					$("#ServiceReports_editView_fieldName_sad_hmr").val();
					$("#ServiceReports_editView_fieldName_sad_hmr").prop("disabled", true);
					$("#ServiceReports_editView_fieldName_sacfd_km_run").prop("disabled", false);
				}
			} else {
				$("#sad_sub_ass_hmr" + rowNum).prop("disabled", false);
				$("#sad_sub_ass_km" + rowNum).prop("disabled", false);
				$("#ServiceReports_editView_fieldName_sad_hmr").prop("disabled", false);
				$("#ServiceReports_editView_fieldName_sacfd_km_run").prop("disabled", false);
			}

		});
	},
	handleMonthOrKMOrHMR: function (id, rowNum) {
		$(id).off("input");
		$(id).on('input', function (event) {
			let fieldName = $(this).data("fieldname");
			let value = event.target.value;
			let warrantyTermsVal = $('select[name="sad_war_term'+ rowNum +'"]').val();

			if (value.length > 0) {
				if (fieldName == 'sad_sub_ass_hmr') {
					$("#sad_sub_ass_km" + rowNum).val('');
					$("#sad_sub_ass_km" + rowNum).prop("disabled", true);
					if (warrantyTermsVal != 'Month And HMR/KM') {
						$("#sad_sub_ass_mon" + rowNum).val('');
						$("#sad_sub_ass_mon" + rowNum).prop("disabled", true);
					}
					$("#ServiceReports_editView_fieldName_sacfd_km_run").val();
					$("#ServiceReports_editView_fieldName_sacfd_km_run").prop("disabled", true);

				} else if (fieldName == 'sad_sub_ass_mon') {
					if (warrantyTermsVal != 'Month And HMR/KM') {
						$("#sad_sub_ass_hmr" + rowNum).val('');
						$("#sad_sub_ass_hmr" + rowNum).prop("disabled", true);
						$("#sad_sub_ass_km" + rowNum).val('');
						$("#sad_sub_ass_km" + rowNum).prop("disabled", true);
						$("#ServiceReports_editView_fieldName_sad_hmr").val();
						$("#ServiceReports_editView_fieldName_sad_hmr").prop("disabled", true);
						$("#ServiceReports_editView_fieldName_sacfd_km_run").val();
						$("#ServiceReports_editView_fieldName_sacfd_km_run").prop("disabled", true);
					}
				} else if (fieldName == 'sad_sub_ass_km') {
					$("#sad_sub_ass_hmr" + rowNum).val('');
					$("#sad_sub_ass_hmr" + rowNum).prop("disabled", true);
					if (warrantyTermsVal != 'Month And HMR/KM') {
						$("#sad_sub_ass_mon" + rowNum).val('');
						$("#sad_sub_ass_mon" + rowNum).prop("disabled", true);
					}
					$("#ServiceReports_editView_fieldName_sad_hmr").val();
					$("#ServiceReports_editView_fieldName_sad_hmr").prop("disabled", true);
				}
			} else {
				$("#sad_sub_ass_hmr" + rowNum).prop("disabled", false);
				$("#sad_sub_ass_km" + rowNum).prop("disabled", false);
				$("#sad_sub_ass_mon" + rowNum).prop("disabled", false);
			}
		});
	},
	// warrantable or not
	checkWarntableOrNot: function () {
		let month = $("#sad_sub_ass_mon1").val();
		if (month == '' || month == null || month == undefined) {
			return 'm';
		}
		let d = new Date();
		d.setMonth(d.getMonth() - month);
		let dateOfFitemnet = $("#sad_date_oracs1").val();
		if (dateOfFitemnet == '' || dateOfFitemnet == null || dateOfFitemnet == undefined) {
			return 'm';
		}
		let dateArray = dateOfFitemnet.split('/');
		let day = dateArray[0];
		day = parseInt(day);
		let month1 = dateArray[1];
		month1 = parseInt(month1);
		let year = dateArray[2];
		let d1 = new Date(year, month1 - 1, day + 1);
		if (d1 > d) {
			return true;
		} else {
			return false;
		}
	},
	checkWarntableOrNotKM: function () {
		let warrantyKM = $("#sad_sub_ass_km1").val();
		if (warrantyKM == '' || warrantyKM == null || warrantyKM == undefined) {
			return 'm';
		}
		let fitMentKM = $("#ServiceReports_editView_fieldName_sad_km_run").val();
		if (fitMentKM == '' || fitMentKM == null || fitMentKM == undefined) {
			return 'm';
		}
		let presentKM = $("#ServiceReports_editView_fieldName_eq_present_km").val();
		if (presentKM == '' || presentKM == null || presentKM == undefined) {
			return 'm';
		}
		presentKM = parseInt(presentKM);
		warrantyKM = parseInt(warrantyKM);
		fitMentKM = parseInt(fitMentKM);
		let totalRunned = presentKM - fitMentKM;

		if (warrantyKM > totalRunned) {
			return true;
		} else {
			return false;
		}
	},
	checkWarntableOrNotHMR: function () {
		let warrantyHMR = $("#sad_sub_ass_hmr1").val();
		if (warrantyHMR == '' || warrantyHMR == null || warrantyHMR == undefined) {
			return 'm';
		}
		let fitMentHMR = $("#ServiceReports_editView_fieldName_sad_hmr").val();
		if (fitMentHMR == '' || fitMentHMR == null || fitMentHMR == undefined) {
			return 'm';
		}
		let presentHMR = $("#ServiceReports_editView_fieldName_eq_last_hmr").val();
		if (presentHMR == '' || presentHMR == null || presentHMR == undefined) {
			return 'm';
		}
		presentHMR = parseInt(presentHMR);
		warrantyHMR = parseInt(warrantyHMR);
		fitMentHMR = parseInt(fitMentHMR);
		let totalRunned = presentHMR - fitMentHMR;
		if (warrantyHMR > totalRunned) {
			return true;
		} else {
			return false;
		}
	},
	oredCondition: function () {
		let monthCheck = this.checkWarntableOrNot();
		let KMCheck = this.checkWarntableOrNotKM();
		let HMRCheck = this.checkWarntableOrNotHMR();
		if (monthCheck == 'm' && KMCheck == 'm' && HMRCheck == 'm') {
			$('input[name="war_warable"]').val('Not Warrantable');
			$('#war_warableNot_Warrantable').attr("checked", true);
			$('input[name="war_warable"]').attr('readonly', true);
		} else if (monthCheck == 'm' && KMCheck == 'm' && HMRCheck == true) {
			$('input[name="war_warable"]').val('Warrantable');
			$('#war_warableWarrantable').attr("checked", true);
			$('input[name="war_warable"]').attr('readonly', true);
		} else if (monthCheck == 'm' && KMCheck == true && HMRCheck == 'm') {
			$('input[name="war_warable"]').val('Warrantable');
			$('#war_warableWarrantable').attr("checked", true);
			$('input[name="war_warable"]').attr('readonly', true);
		} else if (monthCheck == true && KMCheck == 'm' && HMRCheck == 'm') {
			$('input[name="war_warable"]').val('Warrantable');
			$('#war_warableWarrantable').attr("checked", true);
			$('input[name="war_warable"]').attr('readonly', true);
		} else if (monthCheck == false || KMCheck == false || HMRCheck == false) {
			$('input[name="war_warable"]').val('Not Warrantable');
			$('#war_warableNot_Warrantable').attr("checked", true);
			$('input[name="war_warable"]').attr('readonly', true);
		} else if (monthCheck == true && KMCheck == true && HMRCheck == 'm') {
			$('input[name="war_warable"]').val('Warrantable');
			$('#war_warableWarrantable').attr("checked", true);
			$('input[name="war_warable"]').attr('readonly', true);
		} else if (monthCheck == 'm' && KMCheck == true && HMRCheck == true) {
			$('input[name="war_warable"]').val('Warrantable');
			$('#war_warableWarrantable').attr("checked", true);
			$('input[name="war_warable"]').attr('readonly', true);
		} else if (monthCheck == true && KMCheck == 'm' && HMRCheck == true) {
			$('input[name="war_warable"]').val('Warrantable');
			$('#war_warableWarrantable').attr("checked", true);
			$('input[name="war_warable"]').attr('readonly', true);
		} else if (monthCheck == true && KMCheck == true && HMRCheck == true) {
			$('input[name="war_warable"]').val('Warrantable');
			$('#war_warableWarrantable').attr("checked", true);
			$('input[name="war_warable"]').attr('readonly', true);
		}
		this.warrentableyorn();
	},
	registerAllWarrantableFunctions: function () {
		let self = this;
		jQuery("#sad_sub_ass_mon1").on('input', function (event) {
			self.oredCondition();
		});
		jQuery("#sad_date_oracs1").on('change', function (event) {
			self.oredCondition();
		});
		jQuery("#sad_sub_ass_km1").on('input', function (event) {
			self.oredCondition();
		});
		jQuery("#ServiceReports_editView_fieldName_eq_present_km").on('input', function (event) {
			self.oredCondition();
		});
		jQuery("#ServiceReports_editView_fieldName_sad_km_run").on('input', function (event) {
			self.oredCondition();
		});
		jQuery("#sad_sub_ass_hmr1").on('input', function (event) {
			self.oredCondition();
		});
		jQuery("#ServiceReports_editView_fieldName_eq_last_hmr").on('input', function (event) {
			self.oredCondition();
		});
		jQuery("#sad_hmr").on('input', function (event) {
			self.oredCondition();
		});
	},
	handleOtherDependecy: function () {
		let self = this;
		$('input[name="war_warable"]').click(function () {
			return false;
		});
		$('select[data-extraname="sad_sel_ag_name"]').change(function (event) {
			// let fieldName = $(this).data("fieldname");
			let value = event.target.value;
			let dependentFieldName = 'lastPeriodialTable';
			if (value == '') {
				$("#" + dependentFieldName).addClass("hide");
			} else {
				$("#" + dependentFieldName).removeClass("hide");
				let dataOf = {};
				dataOf['record'] = $("input[name=equipment_id]").val();
				dataOf['source_module'] = 'Equipment';
				dataOf['module'] = 'ServiceReports';
				dataOf['action'] = 'ArrgateDetails';
				dataOf['aggregate'] = value;
				app.helper.showProgress();
				app.request.get({ data: dataOf }).then(function (err, response) {
					if (err == null) {
						let trHeader = `<thead>
											<th colspan="6" class="lineItemBlockHeader">
												<h4 class="fieldBlockHeader">Last Aggregate Periodic Maintenance Details</h4>
											</th>
										</thead>
										<tr>
											<td> Sl. No.</td>
											<td> Selected Aggregates-Name </td>
											<td> Manufacturer name </td>
											<td> Working Hour of Aggregates </td>
											<td> Date of Fitment </td>
											<td> Last Periodical Maintenance Type </td>
										</tr>`;
						let trElemnet = "";
						for (let i = 0; i < response.data.length; i++) {
							let tdElemnet = '';
							if (response.data[i].sad_ag_sl_no == undefined || response.data[i].sad_ag_sl_no == null) {
								tdElemnet = "<td></td>";
							} else {
								tdElemnet = "<td>" + response.data[i].sad_ag_sl_no + "</td>";
							}

							if (response.data[i].sad_sel_ag_name == undefined || response.data[i].sad_sel_ag_name == null) {
								tdElemnet += "<td></td>";
							} else {
								tdElemnet += "<td>" + response.data[i].sad_sel_ag_name + "</td>";
							}
							if (response.data[i].sad_manu_name == undefined || response.data[i].sad_manu_name == null) {
								tdElemnet += "<td></td>";
							} else {
								tdElemnet += "<td>" + response.data[i].sad_manu_name + "</td>";
							}
							if (response.data[i].sad_whoa == undefined || response.data[i].sad_whoa == null) {
								tdElemnet += "<td></td>";
							} else {
								tdElemnet += "<td>" + response.data[i].sad_whoa + "</td>";
							}
							if (response.data[i].sad_dof == undefined || response.data[i].sad_dof == null) {
								tdElemnet += "<td></td>";
							} else {
								tdElemnet += "<td>" + response.data[i].sad_dof + "</td>";
							}
							if (response.data[i].apmd_peridic_maint_type == undefined || response.data[i].apmd_peridic_maint_type == null) {
								tdElemnet += "<td></td>";
							} else {
								tdElemnet += "<td>" + response.data[i].apmd_peridic_maint_type + "</td>";
							}
							trElemnet += '<tr>' + tdElemnet + '</tr>';
						}
						trElement = "<tbody>" + trHeader +
							trElemnet
							+ "</tbody>";
						$("#" + dependentFieldName).empty().append(trElement);
					}
					app.helper.hideProgress();
				});
			}
		});
		jQuery('.lineitempicklistfield').on('change', function (event) {
			let fieldName = $(this).data("fieldname");
			let rowNum = $(this).closest('tr').data('row-num');
			let value = event.target.value;
			if (fieldName == 'sad_war_term') {
				$('input[name="war_warable"]').val('');
				$('#war_warableWarrantable').attr("checked", false);
				$('#war_warableNot_Warrantable').attr("checked", false);
				if (value == 'Only Month') {
					$("#sad_sub_ass_hmr" + rowNum).val('');
					$("#sad_sub_ass_hmr" + rowNum).prop("disabled", true);
					$("#sad_sub_ass_km" + rowNum).val('');
					$("#sad_sub_ass_km" + rowNum).prop("disabled", true);
					$("#sad_sub_ass_mon" + rowNum).prop("disabled", false);
				} else if (value == 'Only HMR/KM') {
					$("#sad_sub_ass_mon" + rowNum).prop("disabled", true);
					$("#sad_sub_ass_mon" + rowNum).val('');
					$("#sad_sub_ass_hmr" + rowNum).prop("disabled", false);
					$("#sad_sub_ass_km" + rowNum).prop("disabled", false);
					self.handleOtherDependecy1("#sad_sub_ass_hmr" + rowNum, rowNum);
					self.handleOtherDependecy1("#sad_sub_ass_km" + rowNum, rowNum);
				} else if (value == 'Month Or HMR/KM') {
					$("#sad_sub_ass_hmr" + rowNum).prop("disabled", false);
					$("#sad_sub_ass_km" + rowNum).prop("disabled", false);
					$("#sad_sub_ass_mon" + rowNum).prop("disabled", false);
					self.handleMonthOrKMOrHMR("#sad_sub_ass_hmr" + rowNum, rowNum);
					self.handleMonthOrKMOrHMR("#sad_sub_ass_km" + rowNum, rowNum);
					self.handleMonthOrKMOrHMR("#sad_sub_ass_mon" + rowNum, rowNum);
				} else if (value == 'Month And HMR/KM') {
					$("#sad_sub_ass_hmr" + rowNum).prop("disabled", false);
					$("#sad_sub_ass_km" + rowNum).prop("disabled", false);
					self.handleOtherDependecy1("#sad_sub_ass_hmr" + rowNum, rowNum);
					self.handleOtherDependecy1("#sad_sub_ass_km" + rowNum, rowNum);
				}
			}
		});
		self.registerAllWarrantableFunctions();
		let initDependentFields = Array();
		let type = $('select[name="sr_ticket_type"]').val();
		if (type == 'BREAKDOWN') {
			initDependentFields = Array('fd_sub_div', 'vendor_id', 'restoration_date',
				'off_on_account_of', 'restoration_time', 'remarks_for_offroad', 'GENERAL_CHECKS',
				'Restoration_Date', 'Off_Road');
		} else {
			initDependentFields = Array('fd_sub_div', 'vendor_id', 'restoration_date',
				'off_on_account_of', 'restoration_time', 'remarks_for_offroad');
		}

		initDependentFields.forEach(element => {
			$("#" + element + 'hideOrShowId').addClass("hide");
			$("#" + element + 'hideOrShowInputId').addClass("hide");
		});
		jQuery('.fail_de_part_pertains_to').on('change', function (event) {
			let value = event.target.value;
			// Initial Hiding
			// let name = event.target.name;
			let dependentValues = Array('BEML');
			let dependentFieldName = 'fd_sub_div';
			if (dependentValues.includes(value)) {
				$("#" + dependentFieldName + 'hideOrShowId').removeClass("hide");
				$("#" + dependentFieldName + 'hideOrShowInputId').removeClass("hide");
			} else {
				$("#" + dependentFieldName + 'hideOrShowId').addClass("hide");
				$("#" + dependentFieldName + 'hideOrShowInputId').addClass("hide");
			}
			let dependentValuesAnother = Array('Vendor');
			let dependentFieldNameAnother = 'vendor_id';
			if (dependentValuesAnother.includes(value)) {
				$("#" + dependentFieldNameAnother + 'hideOrShowId').removeClass("hide");
				$("#" + dependentFieldNameAnother + 'hideOrShowInputId').removeClass("hide");
			} else {
				$("#" + dependentFieldNameAnother + 'hideOrShowId').addClass("hide");
				$("#" + dependentFieldNameAnother + 'hideOrShowInputId').addClass("hide");
			}
		})
		this.handleDefaultPartsPartainsTo();

		jQuery('.eq_sta_aft_act_taken').on('change', function (event) {
			let value = event.target.value;
			// Initial Hiding
			// let name = event.target.name;
			let dependentValues = Array('On Road', 'Running with Problem', 'operational', 'in limited operation');
			let dependentFieldName = 'restoration_date';
			if (dependentValues.includes(value)) {
				$("#" + dependentFieldName + 'hideOrShowId').removeClass("hide");
				$("#" + dependentFieldName + 'hideOrShowInputId').removeClass("hide");
			} else {
				$("#" + dependentFieldName + 'hideOrShowId').addClass("hide");
				$("#" + dependentFieldName + 'hideOrShowInputId').addClass("hide");
			}
			let dependentFieldNameAnother = 'restoration_time';
			if (dependentValues.includes(value)) {
				$("#" + dependentFieldNameAnother + 'hideOrShowId').removeClass("hide");
				$("#" + dependentFieldNameAnother + 'hideOrShowInputId').removeClass("hide");
			} else {
				$("#" + dependentFieldNameAnother + 'hideOrShowId').addClass("hide");
				$("#" + dependentFieldNameAnother + 'hideOrShowInputId').addClass("hide");
			}
			if (type == 'BREAKDOWN') {
				dependentFieldNameAnother = 'GENERAL_CHECKS';
				if (dependentValues.includes(value)) {
					$("#" + dependentFieldNameAnother + 'hideOrShowId').removeClass("hide");
				} else {
					$("#" + dependentFieldNameAnother + 'hideOrShowId').addClass("hide");
				}
				dependentFieldNameAnother = 'Restoration_Date';
				if (dependentValues.includes(value)) {
					$("#" + dependentFieldNameAnother + 'hideOrShowId').removeClass("hide");
				} else {
					$("#" + dependentFieldNameAnother + 'hideOrShowId').addClass("hide");
				}
			}

			dependentValues = Array('Off Road', 'out of order');
			if (type == 'BREAKDOWN') {
				dependentFieldName = 'Off_Road';
				if (dependentValues.includes(value)) {
					$("#" + dependentFieldName + 'hideOrShowId').removeClass("hide");
				} else {
					$("#" + dependentFieldName + 'hideOrShowId').addClass("hide");
				}
			}
			dependentFieldName = 'off_on_account_of';
			if (dependentValues.includes(value)) {
				$("#" + dependentFieldName + 'hideOrShowId').removeClass("hide");
				$("#" + dependentFieldName + 'hideOrShowInputId').removeClass("hide");
			} else {
				$("#" + dependentFieldName + 'hideOrShowId').addClass("hide");
				$("#" + dependentFieldName + 'hideOrShowInputId').addClass("hide");
			}
			dependentFieldNameAnother = 'remarks_for_offroad';
			if (dependentValues.includes(value)) {
				$("#" + dependentFieldNameAnother + 'hideOrShowId').removeClass("hide");
				$("#" + dependentFieldNameAnother + 'hideOrShowInputId').removeClass("hide");
			} else {
				$("#" + dependentFieldNameAnother + 'hideOrShowId').addClass("hide");
				$("#" + dependentFieldNameAnother + 'hideOrShowInputId').addClass("hide");
			}
		})

		if (type == 'SERVICE FOR SPARES PURCHASED') {
			let purposeValue = $('select[name="tck_det_purpose"]').val();
			if (purposeValue == "WARRANTY CLAIM FOR SUB ASSEMBLY / OTHER SPARE PARTS") {
				self.handlePresentWCKmHmrDependencyInitial();
				self.handlePresentWCKmHmrDependencyOnEdit();

				self.handleFitmentWCKmHmrDependencyInitial();
				self.handleFitmentWCKmHmrDependencyOnEdit();
			}
		}
	},

	handleDefaultPartsPartainsTo : function(){
		let value = $('select[name="fail_de_part_pertains_to"]').val();
		let dependentValues = Array('BEML');
		let dependentFieldName = 'fd_sub_div';
		let type = $('select[name="sr_ticket_type"]').val();
		if (dependentValues.includes(value)) {
			$("#" + dependentFieldName + 'hideOrShowId').removeClass("hide");
			$("#" + dependentFieldName + 'hideOrShowInputId').removeClass("hide");
		} else {
			$("#" + dependentFieldName + 'hideOrShowId').addClass("hide");
			$("#" + dependentFieldName + 'hideOrShowInputId').addClass("hide");
		}
		let dependentValuesAnother = Array('Vendor');
		let dependentFieldNameAnother = 'vendor_id';
		if (dependentValuesAnother.includes(value)) {
			$("#" + dependentFieldNameAnother + 'hideOrShowId').removeClass("hide");
			$("#" + dependentFieldNameAnother + 'hideOrShowInputId').removeClass("hide");
		} else {
			$("#" + dependentFieldNameAnother + 'hideOrShowId').addClass("hide");
			$("#" + dependentFieldNameAnother + 'hideOrShowInputId').addClass("hide");
		}

		value = $('select[name="eq_sta_aft_act_taken"]').val();
		dependentValues = Array('On Road', 'Running with Problem', 'operational', 'in limited operation');
		dependentFieldName = 'restoration_date';
		if (dependentValues.includes(value)) {
			$("#" + dependentFieldName + 'hideOrShowId').removeClass("hide");
			$("#" + dependentFieldName + 'hideOrShowInputId').removeClass("hide");
		} else {
			$("#" + dependentFieldName + 'hideOrShowId').addClass("hide");
			$("#" + dependentFieldName + 'hideOrShowInputId').addClass("hide");
		}
		dependentFieldNameAnother = 'restoration_time';
		if (dependentValues.includes(value)) {
			$("#" + dependentFieldNameAnother + 'hideOrShowId').removeClass("hide");
			$("#" + dependentFieldNameAnother + 'hideOrShowInputId').removeClass("hide");
		} else {
			$("#" + dependentFieldNameAnother + 'hideOrShowId').addClass("hide");
			$("#" + dependentFieldNameAnother + 'hideOrShowInputId').addClass("hide");
		}
		if (type == 'BREAKDOWN') {
			dependentFieldNameAnother = 'GENERAL_CHECKS';
			if (dependentValues.includes(value)) {
				$("#" + dependentFieldNameAnother + 'hideOrShowId').removeClass("hide");
			} else {
				$("#" + dependentFieldNameAnother + 'hideOrShowId').addClass("hide");
			}
			dependentFieldNameAnother = 'Restoration_Date';
			if (dependentValues.includes(value)) {
				$("#" + dependentFieldNameAnother + 'hideOrShowId').removeClass("hide");
			} else {
				$("#" + dependentFieldNameAnother + 'hideOrShowId').addClass("hide");
			}
		}
		dependentValues = Array('Off Road', 'out of order');
		if (type == 'BREAKDOWN') {
			dependentFieldName = 'Off_Road';
			if (dependentValues.includes(value)) {
				$("#" + dependentFieldName + 'hideOrShowId').removeClass("hide");
			} else {
				$("#" + dependentFieldName + 'hideOrShowId').addClass("hide");
			}
		}
		dependentFieldName = 'off_on_account_of';
		if (dependentValues.includes(value)) {
			$("#" + dependentFieldName + 'hideOrShowId').removeClass("hide");
			$("#" + dependentFieldName + 'hideOrShowInputId').removeClass("hide");
		} else {
			$("#" + dependentFieldName + 'hideOrShowId').addClass("hide");
			$("#" + dependentFieldName + 'hideOrShowInputId').addClass("hide");
		}
		dependentFieldNameAnother = 'remarks_for_offroad';
		if (dependentValues.includes(value)) {
			$("#" + dependentFieldNameAnother + 'hideOrShowId').removeClass("hide");
			$("#" + dependentFieldNameAnother + 'hideOrShowInputId').removeClass("hide");
		} else {
			$("#" + dependentFieldNameAnother + 'hideOrShowId').addClass("hide");
			$("#" + dependentFieldNameAnother + 'hideOrShowInputId').addClass("hide");
		}
	},

	handlePresentWCKmHmrDependencyInitial: function () {
		let val = $('input[name="eq_last_hmr"]').val();
		if (val > 0) {
			$('input[name="eq_present_km"]').attr('readonly', true).css('background-color', '#eeeeee !important');
		}
		let kval = $('input[name="eq_present_km"]').val();
		if (kval > 0) {
			$('input[name="eq_last_hmr"]').attr('readonly', true).css('background-color', '#eeeeee !important');
		}
	},
	handlePresentWCKmHmrDependencyOnEdit: function () {
		let self = this;
		$('input[name="eq_last_hmr"]').on('input', function (event) {
			let val = $('input[name="eq_last_hmr"]').val();
			if (val > 0) {
				$('input[name="eq_present_km"]').attr('readonly', true).css('background-color', '#eeeeee !important');
			} else {
				$('input[name="eq_present_km"]').removeAttr('readonly', true).css('background-color', '');
			}
		});
		$('input[name="eq_present_km"]').on('input', function (event) {
			let kval = parseFloat($('input[name="eq_present_km"]').val());
			if (kval > 0) {
				$('input[name="eq_last_hmr"]').attr('readonly', true).css('background-color', '#eeeeee !important');
			} else {
				$('input[name="eq_last_hmr"]').attr('readonly', false).css('background-color', '');
			}
		});
	},
	handleFitmentWCKmHmrDependencyInitial: function () {
		let val = $('input[name="sad_hmr"]').val();
		if (val > 0) {
			$('input[name="sad_km_run"]').attr('readonly', true).css('background-color', '#eeeeee !important');
		}
		let kval = $('input[name="sad_km_run"]').val();
		if (kval > 0) {
			$('input[name="sad_hmr"]').attr('readonly', true).css('background-color', '#eeeeee !important');
		}
	},
	handleFitmentWCKmHmrDependencyOnEdit: function () {
		let self = this;
		$('input[name="sad_hmr"]').on('input', function (event) {
			let val = $('input[name="sad_hmr"]').val();
			if (val > 0) {
				$('input[name="sad_km_run"]').attr('readonly', true).css('background-color', '#eeeeee !important');
			} else {
				$('input[name="sad_km_run"]').removeAttr('readonly', true).css('background-color', '');
			}
		});
		$('input[name="sad_km_run"]').on('input', function (event) {
			let kval = $('input[name="sad_km_run"]').val();
			if (kval > 0) {
				$('input[name="sad_hmr"]').attr('readonly', true).css('background-color', '#eeeeee !important');
			} else {
				$('input[name="sad_hmr"]').removeAttr('readonly', true).css('background-color', '');
			}
		});
	},
	phoneCountryCode: function () {
		var input = document.querySelector("#ServiceReports_editView_fieldName_phone"),
			errorMsg = document.querySelector("#phone_error-msg"),
			validMsg = document.querySelector("#phone_valid-msg");
		if (input == null) {
			return;
		}
		$(document).ready(function () {
			$('#ServiceReports_editView_fieldName_phone').attr('type', 'tel');
		});
		let isVaildMobilenumber = false;
		// Error messages based on the code returned from getValidationError
		var errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];

		// Initialise plugin
		var intl = window.intlTelInput(input, {
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
			let num = intl.getNumber(intlTelInputUtils.numberFormat.E164);
			if (input.value.trim()) {
				if (intl.isValidNumber()) {
					isVaildMobilenumber = true;
					$('#HelpDesk_editView_fieldName_phone').val(num);
					validMsg.classList.remove("hide");
				} else {
					input.classList.add("error");
					var errorCode = intl.getValidationError();
					errorMsg.innerHTML = errorMap[errorCode];
					errorMsg.classList.remove("hide");
					isVaildMobilenumber = false;
				}
			}
		});
	},

	aggregate_warranty_applicalple: function () {
		$('input:radio[name="fd_ag_war_avl"]').change(function () {
			let val = $("input[name='fd_ag_war_avl']:checked").val();
			let sapTicketVal = $("select[name='fail_de_sap_noti_type']").val();
			if (val == 'YES' && sapTicketVal != 'ZJ') {
				let message = "You have marked Aggregate warranty applicable as YES," +
					" you can select only ZJ notification type, if you still want to select " +
					"different notification type please mark Aggregate warranty applicable as 'NO'";
				let self = this;
				app.helper.showConfirmationBox({ 'message': message }).then(
					function (e) {
						$('.fail_de_sap_noti_type option[value="ZJ"]').attr("selected", "selected").trigger('change');
						$("select[name='fail_de_sap_noti_type']").attr('readonly', true);
					},
					function (error, err) {
						$(self).prop('checked', false);
					}
				);
			} else if (val == 'NO') {
				$('[data-fieldname="fail_de_sap_noti_type"]').removeAttr("readonly", false);
			}
		});
	},

	ECDT_HideorShow: function () {
		if ($("#Equipment_Commissioning_detailshideOrShowId").length > 0) {
			if ($("input[name='ecd_can_be_com']:checked").val() == 'Yes') {
				$('#FAILURE_DETAILShideOrShowId').addClass("hide");
				$('option[value="ZB"]').addClass("hide");
			} else if ($("input[name='ecd_can_be_com']:checked").val() == 'No') {
				$('#GENERAL_CHECKShideOrShowId').addClass("hide");
				$('option[value="ZE"]').addClass('hide');
			} else {
				// $('option[value="ZE"]').addClass('hide');
				// $('option[value="ZB"]').addClass("hide");
				$('#FAILURE_DETAILShideOrShowId').addClass("hide");
				$('#GENERAL_CHECKShideOrShowId').addClass("hide");
			}

			$('input:radio[name=ecd_can_be_com]').change(function () {
				if ($("input[name='ecd_can_be_com']:checked").val() == 'Yes') {
					let message = 'Notification Type should be ZE';
					let self = this;
					app.helper.showConfirmationBox({ 'message': message }).then(
						function (e) {
							$('option[value="ZE"]').removeClass('hide');
							$('option[value="ZB"]').addClass("hide");

							$('select[name="fail_de_sap_noti_type"]').val('ZE');
							$('select[name="fail_de_sap_noti_type"]').select2().trigger('change');
							$('[data-fieldname="fail_de_sap_noti_type"]').attr("readonly", 'readonly');
							$('#FAILURE_DETAILShideOrShowId').addClass("hide");
							$('#GENERAL_CHECKShideOrShowId').removeClass("hide");

						},
						function (error, err) {
							$(self).prop('checked', false);
						}
					);

				} else if ($("input[name='ecd_can_be_com']:checked").val() == 'No') {
					let message = 'Notification Type should be ZB';
					let self = this;
					app.helper.showConfirmationBox({ 'message': message }).then(
						function (e) {
							$('option[value="ZE"]').addClass('hide');
							$('option[value="ZB"]').removeClass("hide");
							$('#select2-result-label-124').trigger('click');

							$('select[name="fail_de_sap_noti_type"]').val('ZB');
							$('select[name="fail_de_sap_noti_type"]').select2().trigger('change');
							$('[data-fieldname="fail_de_sap_noti_type"]').attr("readonly", 'readonly');
							$('#FAILURE_DETAILShideOrShowId').removeClass("hide");
							$('#GENERAL_CHECKShideOrShowId').addClass("hide");
						},
						function (error, err) {
							$(self).prop('checked', false);
						}
					);
				}
			});
		}
		// Design Modification Status   hide show
		if ($("input[name='at_dm_status']:checked").val() != 'Not Completed') {
			$('#reason_for_not_completionhideOrShowId').addClass("hide");
			$('#reason_for_not_completionhideOrShowInputId').addClass("hide");
		}
		$('input:radio[name=at_dm_status]').change(function () {
			if ($("input[name='at_dm_status']:checked").val() == 'Not Completed') {
				$('#reason_for_not_completionhideOrShowId').removeClass("hide");
				$('#reason_for_not_completionhideOrShowInputId').removeClass("hide");
			} else {
				$('#reason_for_not_completionhideOrShowId').addClass("hide");
				$('#reason_for_not_completionhideOrShowInputId').addClass("hide");
			}
		});

		//Sub Assembly Installation Status  hide show
		if ($("#eq_sta_aft_act_t_sub").val() != 'Not Working' || $("#eq_sta_aft_act_t_sub").val() != 'Working with Problem') {
			$('#at_on_account_ofhideOrShowId').addClass("hide");
			$('#at_on_account_ofhideOrShowInputId').addClass("hide");
		}
		jQuery('.eq_sta_aft_act_t_sub').on('change', function (event) {
			let value = event.target.value;
			if (value == 'Not Working' || value == 'Working with Problem') {
				$('#at_on_account_ofhideOrShowId').removeClass("hide");
				$('#at_on_account_ofhideOrShowInputId').removeClass("hide");
			} else {
				$('#at_on_account_ofhideOrShowId').addClass("hide");
				$('#at_on_account_ofhideOrShowInputId').addClass("hide");
			}
		});

		//Sub Assembly Installation Status  hide show
		if ($("input[name='at_sais']:checked").val() != 'Not Completed') {
			$('#at_on_account_ofhideOrShowId').addClass("hide");
			$('#at_on_account_ofhideOrShowInputId').addClass("hide");
		}
		$('input:radio[name=at_sais]').change(function () {
			if ($("input[name='at_sais']:checked").val() == 'Not Completed') {
				$('#at_on_account_ofhideOrShowId').removeClass("hide");
				$('#at_on_account_ofhideOrShowInputId').removeClass("hide");
			} else {
				$('#at_on_account_ofhideOrShowId').addClass("hide");
				$('#at_on_account_ofhideOrShowInputId').addClass("hide");
			}
		});
		if ($("#sacfd_wasawbfisehideOrShowInputId").length > 0) {
			$('input:radio[name=sacfd_wasawbfise]').change(function () {
				if ($("input[name='sacfd_wasawbfise']:checked").val() == 'Yes') {
				} else if ($("input[name='sacfd_wasawbfise']:checked").val() == 'No') {
					let message = 'Please add the Sub assembly pertaing to one Equipment and for other sub assembly please Create new Service Reqest';
					let self = this;
					app.helper.showConfirmationBox({ 'message': message }).then(
						function (e) {
							$(self).prop('checked', false);
						},
						function (error, err) {
							$(self).prop('checked', false);
						}
					);
				}
			});
		}
	},
	optionDamage: function () {
		$("#ServiceReports_Edit_fieldName_fail_de_type_of_damage").removeClass("select2 select2-offscreen");

		$("#ServiceReports_Edit_fieldName_fail_de_type_of_damage").addClass("hide");
		var option = $("#ServiceReports_Edit_fieldName_fail_de_type_of_damage option");
		var data = [], temp = [];
		for (let i = 0; i < option.length; i++) {
			const myArray = option[i].value.split("_._");
			if (temp.length == 0) {
				temp = myArray;
				data.push(myArray[0]);
				data[myArray[0]] = [];
				data[myArray[0]].push(myArray[1]);
			}
			else if (myArray[0] == temp[0]) {
				data[myArray[0]].push(myArray[1]);
			} else {
				temp = myArray;
				data.push(myArray[0]);
				data[myArray[0]] = [];
				data[myArray[0]].push(myArray[1]);
			}
		}
		var damagedata = [];
		for (let i = 0; i < data.length; i++) {
			var child = [];
			if (data[data[i]].length == 1 && data[data[i][1]] == undefined) {
				child = [{
					id: data[i],
					text: data[i]
				}]
				damdata = {
					id: "",
					text: data[i],
					children: child
				}
				damagedata.push(damdata);
				continue;
			}
			for (let j = 0; j < data[data[i]].length; j++) {
				let tempdata = {
					id: data[i] + "_._" + data[data[i]][j],
					text: "" + data[data[i]][j]
				};
				child.push(tempdata);
			}
			damdata = {
				id: "",
				text: data[i],
				children: child
			};
			damagedata.push(damdata);
		};
		$('input[name="fail_de_type_of_damage"]').select2({
			multiple: true,
			placeholder: "Select Type of Damage",
			data: damagedata,
			width: '60%',
		}).on('select2-selecting', function (e) {
			var $select = $(this);
			if (e.val == '') {
				e.preventDefault();
			}
		}).on('change', function (e) {
			var select2Value = $(e.target).val();
			const myArray = select2Value.split(",");
			$('#ServiceReports_Edit_fieldName_fail_de_type_of_damage option:selected').removeAttr('selected');
			for (let i = 0; i < myArray.length; i++) {
				$("#ServiceReports_Edit_fieldName_fail_de_type_of_damage option[value='" + myArray[i] + "']").attr("selected", "selected").trigger('change');;
			}
		});
		var optionsSelected = $('#ServiceReports_Edit_fieldName_fail_de_type_of_damage').val();
		if (optionsSelected != null) {
			var damagedSelectedData = [];
			for (let i = 0; i < optionsSelected.length; i++) {
				const showText = optionsSelected[i].split('_._');
				damdata = {
					id: optionsSelected[i],
					text: showText[1],
				};
				damagedSelectedData.push(damdata);
			};
			$('input[name="fail_de_type_of_damage"]').data().select2.updateSelection(damagedSelectedData);
		}
	},
	optionPartsAffected: function () {
		$("#ServiceReports_Edit_fieldName_fail_de_parts_affected").removeClass("select2 select2-offscreen");

		$("#ServiceReports_Edit_fieldName_fail_de_parts_affected").addClass("hide");
		var option = $("#ServiceReports_Edit_fieldName_fail_de_parts_affected option");
		var data = [], temp = [];
		for (let i = 0; i < option.length; i++) {
			const myArray = option[i].value.split("_._");
			if (temp.length == 0) {
				temp = myArray;
				data.push(myArray[0]);
				data[myArray[0]] = [];
				data[myArray[0]].push(myArray[1]);
			}
			else if (myArray[0] == temp[0]) {
				data[myArray[0]].push(myArray[1]);
			} else {
				temp = myArray;
				data.push(myArray[0]);
				data[myArray[0]] = [];
				data[myArray[0]].push(myArray[1]);
			}
		}
		var damagedata = [];
		for (let i = 0; i < data.length; i++) {
			var child = [];
			if (data[data[i]].length == 1 && data[data[i][1]] == undefined) {
				child = [{
					id: data[i],
					text: data[i]
				}]
				damdata = {
					id: "",
					text: data[i],
					children: child
				}
				damagedata.push(damdata);
				continue;
			}
			for (let j = 0; j < data[data[i]].length; j++) {
				let tempdata = {
					id: data[i] + "_._" + data[data[i]][j],
					text: "" + data[data[i]][j]
				};
				child.push(tempdata);
			}
			damdata = {
				id: "",
				text: data[i],
				children: child
			};
			damagedata.push(damdata);
		};
		$('input[name="fail_de_parts_affected"]').select2({
			multiple: true,
			placeholder: "Select Parts Affected",
			data: damagedata,
			width: '60%',
		}).on('select2-selecting', function (e) {
			var $select = $(this);
			if (e.val == '') {
				e.preventDefault();
			}
		}).on('change', function (e) {
			var select2Value = $(e.target).val();
			const myArray = select2Value.split(",");
			$('#ServiceReports_Edit_fieldName_fail_de_parts_affected option:selected').removeAttr('selected');
			for (let i = 0; i < myArray.length; i++) {
				$("#ServiceReports_Edit_fieldName_fail_de_parts_affected option[value='" + myArray[i] + "']").attr("selected", "selected").trigger('change');;
			}
		});
		var optionsSelected = $('#ServiceReports_Edit_fieldName_fail_de_parts_affected').val();
		if (optionsSelected != null) {
			var damagedSelectedData = [];
			for (let i = 0; i < optionsSelected.length; i++) {
				const showText = optionsSelected[i].split('_._');
				damdata = {
					id: optionsSelected[i],
					text: showText[1],
				};
				damagedSelectedData.push(damdata);
			};
			$('input[name="fail_de_parts_affected"]').data().select2.updateSelection(damagedSelectedData);
		}
	},

	optionSystemAffected: function () {
		$("#ServiceReports_Edit_fieldName_fail_de_system_affected").removeClass("select2 select2-offscreen");

		$("#ServiceReports_Edit_fieldName_fail_de_system_affected").addClass("hide");
		var option = $("#ServiceReports_Edit_fieldName_fail_de_system_affected option");
		var data = [], temp = [];
		for (let i = 0; i < option.length; i++) {
			const myArray = option[i].value.split("_._");
			if (temp.length == 0) {
				temp = myArray;
				data.push(myArray[0]);
				data[myArray[0]] = [];
				data[myArray[0]].push(myArray[1]);
			}
			else if (myArray[0] == temp[0]) {
				data[myArray[0]].push(myArray[1]);
			} else {
				temp = myArray;
				data.push(myArray[0]);
				data[myArray[0]] = [];
				data[myArray[0]].push(myArray[1]);
			}
		}
		var damagedata = [];
		for (let i = 0; i < data.length; i++) {
			var child = [];
			if (data[data[i]].length == 1 && data[data[i][1]] == undefined) {
				child = [{
					id: data[i],
					text: data[i]
				}]
				damdata = {
					id: "",
					text: data[i],
					children: child
				}
				damagedata.push(damdata);
				continue;
			}
			for (let j = 0; j < data[data[i]].length; j++) {
				let tempdata = {
					id: data[i] + "_._" + data[data[i]][j],
					text: "" + data[data[i]][j]
				};
				child.push(tempdata);
			}
			damdata = {
				id: "",
				text: data[i],
				children: child
			};
			damagedata.push(damdata);
		};
		$('input[name="fail_de_system_affected"]').select2({
			multiple: true,
			placeholder: "Select System Affected",
			data: damagedata,
			width: '60%',
		}).on('select2-selecting', function (e) {
			var $select = $(this);
			if (e.val == '') {
				e.preventDefault();
			}
		}).on('change', function (e) {
			var select2Value = $(e.target).val();
			const myArray = select2Value.split(",");
			$('#ServiceReports_Edit_fieldName_fail_de_system_affected option:selected').removeAttr('selected');
			for (let i = 0; i < myArray.length; i++) {
				$("#ServiceReports_Edit_fieldName_fail_de_system_affected option[value='" + myArray[i] + "']").attr("selected", "selected").trigger('change');;
			}
		});
		var optionsSelected = $('#ServiceReports_Edit_fieldName_fail_de_system_affected').val();
		if (optionsSelected != null) {
			var damagedSelectedData = [];
			for (let i = 0; i < optionsSelected.length; i++) {
				const showText = optionsSelected[i].split('_._');
				damdata = {
					id: optionsSelected[i],
					text: showText[1],
				};
				damagedSelectedData.push(damdata);
			};
			$('input[name="fail_de_system_affected"]').data().select2.updateSelection(damagedSelectedData);
		}
	},
	dependencyStatusAndEvent: function () {
		$('select.sad_line_status').on('change', function () {
			if ($(this).val() == "Malfunctioning") {
				$(this).closest('tr').find('select.sad_line_event option').addClass('hide');
			} else if ($(this).val() == "Damage") {
				$(this).closest('tr').find('select.sad_line_event option').addClass('hide');
				$(this).closest('tr').find('select.sad_line_event option[value="During Transit"]').removeClass('hide');
				$(this).closest('tr').find('select.sad_line_event option[value="During Installation/Commissioning"]').removeClass('hide');
				$(this).closest('tr').find('select.sad_line_event option[value="Damage on Customer Account"]').removeClass('hide');
			} else if ($(this).val() == "Shortage") {
				$(this).closest('tr').find('select.sad_line_event option').addClass('hide');
				$(this).closest('tr').find('select.sad_line_event option[value="Missing"]').removeClass('hide');
				$(this).closest('tr').find('select.sad_line_event option[value="Short shipped from division"]').removeClass('hide');
				$(this).closest('tr').find('select.sad_line_event option[value="Theft under BEML custody"]').removeClass('hide');
				$(this).closest('tr').find('select.sad_line_event option[value="Theft under Customer custody"]').removeClass('hide');
			} else {
				$(this).closest('tr').find('select.sad_line_event option').addClass('hide');
			}
		});
	},
	optionDamage1: function () {
		$("#ServiceReports_Edit_fieldName_fail_de_type_of_damage").addClass("hide");
		var option = $("#ServiceReports_Edit_fieldName_fail_de_type_of_damage option");
		var data = [], temp = [];
		for (let i = 0; i < option.length; i++) {
			const myArray = option[i].value.split("_._");
			if (temp.length == 0) {
				temp = myArray;
				data.push(myArray[0]);
				data[myArray[0]] = [];
				data[myArray[0]].push(myArray[1]);
			}
			else if (myArray[0] == temp[0]) {
				data[myArray[0]].push(myArray[1]);
			} else {
				temp = myArray;
				data.push(myArray[0]);
				data[myArray[0]] = [];
				data[myArray[0]].push(myArray[1]);
			}
		}
		var damagedata = [];
		for (let i = 0; i < data.length; i++) {
			var child = [];
			if (data[data[i]].length == 1 && data[data[i][1]] == undefined) {
				child = [{
					id: data[i],
					text: data[i]
				}]
				damdata = {
					id: "",
					text: data[i],
					children: child
				}
				damagedata.push(damdata);
				continue;
			}
			for (let j = 0; j < data[data[i]].length; j++) {
				let tempdata = {
					id: data[i] + "_._" + data[data[i]][j],
					text: "" + data[data[i]][j]
				};
				child.push(tempdata);
			}
			damdata = {
				id: "",
				text: data[i],
				children: child
			};
			damagedata.push(damdata);
		};
		$('input[name="fail_de_type_of_damage"]').select2({
			multiple: true,
			placeholder: "Select Type of Damage",
			data: damagedata,
			tags: true,
			allowClear: true,
			closeOnSelect: true,
			width: '70%',
			query: function (options) {
				var selectedIds = options.element.select2('val');
				var selectableGroups = $.map(this.data, function (group) {
					var areChildrenAllSelected = true;
					$.each(group.children, function (i, child) {
						if (selectedIds.indexOf(child.id) < 0) {
							areChildrenAllSelected = false;
							return false; // Short-circuit $.each()
						}
					});
					return !areChildrenAllSelected ? group : null;
				});
				options.callback({ results: selectableGroups });
			}
		}).on('select2-selecting', function (e) {
			var $select = $(this);
			if (e.val == '') {
				e.preventDefault();
			}
		}).on('change', function (e) {
			var select2Value = $(e.target).val();
			const myArray = select2Value.split(",");
			$('#ServiceReports_Edit_fieldName_fail_de_type_of_damage option:selected').removeAttr('selected');
			for (let i = 0; i < myArray.length; i++) {
				$("#ServiceReports_Edit_fieldName_fail_de_type_of_damage option[value='" + myArray[i] + "']").attr("selected", "selected").trigger('change');;
			}
		});
		var optionsSelected = $('#ServiceReports_Edit_fieldName_fail_de_type_of_damage').val();
		if (optionsSelected != null) {
			var damagedSelectedData = [];
			for (let i = 0; i < optionsSelected.length; i++) {
				damdata = {
					id: optionsSelected[i],
					text: optionsSelected[i],
				};
				damagedSelectedData.push(damdata);
			};
			$('input[name="fail_de_type_of_damage"]').data().select2.updateSelection(damagedSelectedData);
		}
	},

	editShowVendorsName: function () {
		let autofileValue = $('input.autoComplete2').closest('td').find('input.sourceField');
		for (let i = 0; i < autofileValue.length; i++) {
			if (autofileValue[i].value != '') {
				var aDeferred = jQuery.Deferred();
				var url = 'record=' + autofileValue[i].value + '&source_module=Vendors&module=Vendors&action=GetData';
				AppConnector.request(url).then(
					function (data) {
						if (data['success']) {
							var element = $(autofileValue[i]);
							var parent = element.closest('td');
							parent.find('input.autoComplete2').val(data.result.data.label);
							parent.find('.clearReferenceSelection').removeClass('hide');
							parent.find('.referencefield-wrapper').addClass('selected');
							parent.find('input.autoComplete2').attr("disabled", "disabled");
						} else {
							aDeferred.reject(data['message']);
						}
					},
				)
			}
		}
		// 
	},

	Defaultengine: function () {
		if ($("input[name='genchk_engine']:checked").val() == 'Applicable') {
			$('[data-td="genchk_oil_pressure"]').removeClass("hide");
			$('[data-td="genchk_oil_temperature"]').removeClass("hide");
			$('[data-td="genchk_coolant_temperature"]').removeClass("hide");
		} else {
			$('[data-td="genchk_oil_pressure"]').addClass("hide");
			$('[data-td="genchk_oil_temperature"]').addClass("hide");
			$('[data-td="genchk_coolant_temperature"]').addClass("hide");
		}
	},

	engine: function () {
		$('input:radio[name="genchk_engine"]').change(function () {
			if ($("input[name='genchk_engine']:checked").val() == 'Applicable') {
				$('[data-td="genchk_oil_pressure"]').removeClass("hide");
				$('[data-td="genchk_oil_temperature"]').removeClass("hide");
				$('[data-td="genchk_coolant_temperature"]').removeClass("hide");
			} else {
				$('[data-td="genchk_oil_pressure"]').addClass("hide");
				$('[data-td="genchk_oil_temperature"]').addClass("hide");
				$('[data-td="genchk_coolant_temperature"]').addClass("hide");
			}
		});
	},

	Defaulttransmission: function () {
		if ($("input[name='genchk_transmission']:checked").val() == 'Applicable') {
			$('[data-td="genchk_oil_pre_tr"]').removeClass("hide");
			$('[data-td="genchk_oil_tr_tem"]').removeClass("hide");
		} else {
			$('[data-td="genchk_oil_pre_tr"]').addClass("hide");
			$('[data-td="genchk_oil_tr_tem"]').addClass("hide");
		}
	},

	transmission: function () {
		$('input:radio[name="genchk_transmission"]').change(function () {
			if ($("input[name='genchk_transmission']:checked").val() == 'Applicable') {
				$('[data-td="genchk_oil_pre_tr"]').removeClass("hide");
				$('[data-td="genchk_oil_tr_tem"]').removeClass("hide");
			} else {
				$('[data-td="genchk_oil_pre_tr"]').addClass("hide");
				$('[data-td="genchk_oil_tr_tem"]').addClass("hide");
			}
		});
		this.DefaultbrakeDependency();
		this.brakeDependency();
	},

	DefaultbrakeDependency: function () {
		if ($("input[name='genchk_brake']:checked").val() == 'Applicable') {
			$('[data-td="genchk_brk_oil_tem"]').removeClass("hide");
			$('[data-td="genchk_air_pressure"]').removeClass("hide");
		} else {
			$('[data-td="genchk_brk_oil_tem"]').addClass("hide");
			$('[data-td="genchk_air_pressure"]').addClass("hide");

		}
	},

	brakeDependency: function () {
		$('input:radio[name="genchk_brake"]').change(function () {
			if ($("input[name='genchk_brake']:checked").val() == 'Applicable') {
				$('[data-td="genchk_brk_oil_tem"]').removeClass("hide");
				$('[data-td="genchk_air_pressure"]').removeClass("hide");
			} else {
				$('[data-td="genchk_brk_oil_tem"]').addClass("hide");
				$('[data-td="genchk_air_pressure"]').addClass("hide");

			}
		});
		this.DefaultelectricalDependency();
		this.electricalDependency();
	},

	DefaultelectricalDependency: function () {
		if ($("input[name='genchk_electrical']:checked").val() == 'Applicable') {
			$('[data-td="genchk_motor"]').removeClass("hide");
			$('[data-td="genchk_battery_voltage"]').removeClass("hide");
			$('[data-td="genchk_hi_volt_ele_system"]').removeClass("hide");
			$('[data-td="genchk_auto_electrical_system"]').removeClass("hide");
			$('[data-td="genchk_field_switch"]').removeClass("hide");
			$('[data-td="genchk_transformer"]').removeClass("hide");
		} else {
			$('[data-td="genchk_motor"]').addClass("hide");
			$('[data-td="genchk_battery_voltage"]').addClass("hide");
			$('[data-td="genchk_hi_volt_ele_system"]').addClass("hide");
			$('[data-td="genchk_auto_electrical_system"]').addClass("hide");
			$('[data-td="genchk_field_switch"]').addClass("hide");
			$('[data-td="genchk_transformer"]').addClass("hide");
		}
	},

	electricalDependency: function () {
		$('input:radio[name="genchk_electrical"]').change(function () {
			if ($("input[name='genchk_electrical']:checked").val() == 'Applicable') {
				$('[data-td="genchk_motor"]').removeClass("hide");
				$('[data-td="genchk_battery_voltage"]').removeClass("hide");
				$('[data-td="genchk_hi_volt_ele_system"]').removeClass("hide");
				$('[data-td="genchk_auto_electrical_system"]').removeClass("hide");
				$('[data-td="genchk_field_switch"]').removeClass("hide");
				$('[data-td="genchk_transformer"]').removeClass("hide");
			} else {
				$('[data-td="genchk_motor"]').addClass("hide");
				$('[data-td="genchk_battery_voltage"]').addClass("hide");
				$('[data-td="genchk_hi_volt_ele_system"]').addClass("hide");
				$('[data-td="genchk_auto_electrical_system"]').addClass("hide");
				$('[data-td="genchk_field_switch"]').addClass("hide");
				$('[data-td="genchk_transformer"]').addClass("hide");
			}
		});
		this.DefaulthydralicDynamic();
		this.hydralicDynamic();
	},

	DefaulthydralicDynamic: function () {
		if ($("input[name='genchk_hydraulic']:checked").val() == 'Applicable') {
			$('[data-td="genchk_suspension"]').removeClass("hide");
			$('[data-td="genchk_cylinders"]').removeClass("hide");
			$('[data-td="genchk_oil_cooler"]').removeClass("hide");
			$('[data-td="genchk_pumps"]').removeClass("hide");
		} else {
			$('[data-td="genchk_suspension"]').addClass("hide");
			$('[data-td="genchk_cylinders"]').addClass("hide");
			$('[data-td="genchk_oil_cooler"]').addClass("hide");
			$('[data-td="genchk_pumps"]').addClass("hide");
		}
	},

	hydralicDynamic: function () {
		$('input:radio[name="genchk_hydraulic"]').change(function () {
			if ($("input[name='genchk_hydraulic']:checked").val() == 'Applicable') {
				$('[data-td="genchk_suspension"]').removeClass("hide");
				$('[data-td="genchk_cylinders"]').removeClass("hide");
				$('[data-td="genchk_oil_cooler"]').removeClass("hide");
				$('[data-td="genchk_pumps"]').removeClass("hide");
			} else {
				$('[data-td="genchk_suspension"]').addClass("hide");
				$('[data-td="genchk_cylinders"]').addClass("hide");
				$('[data-td="genchk_oil_cooler"]').addClass("hide");
				$('[data-td="genchk_pumps"]').addClass("hide");
			}
		});
	},

	DefaultbreakdownYesorNo: function () {
		if ($("input[name='at_brkdn_sr_req']:checked").val() == 'Yes') {
			$('[data-td="breack_ticket_id"]').removeClass("hide");
		} else {
			$('[data-td="breack_ticket_id"]').addClass("hide");
		}
	},

	breakdownYesorNo: function () {
		$('input:radio[name="at_brkdn_sr_req"]').change(function () {
			if ($("input[name='at_brkdn_sr_req']:checked").val() == 'Yes') {
				$('[data-td="breack_ticket_id"]').removeClass("hide");
			} else {
				$('[data-td="breack_ticket_id"]').addClass("hide");
			}
		});
	},

	comparePopupHandler: function () {
		let self = this;
		$("#SubAssemblyLookUP").on('click', function () {
			var params = {};
			params.module = 'ServiceReports';
			params.src_module = 'ServiceReports';
			params.src_field = '';
			params.src_record = '';
			params.view = 'Popup';
			let SearchParams = Array();
			let conditions = Array("sad_sub_ass_po_det",
				"c",
				$('[data-fieldname="sad_po_detail"]').val()
			);
			SearchParams.push(conditions);
			conditions = Array("sad_podate",
				"e",
				$('[data-fieldname="sad_po_date"]').val()
			);
			SearchParams.push(conditions);
			params.search_params = JSON.stringify(Array(SearchParams));

			var popupInstance = Vtiger_Popup_Js.getInstance();
			var postPopupHandlerAS = function (e, data) {
				data = JSON.parse(data);
				if (!$.isArray(data)) {
					data = [data];
				}
				self.handleSubAssemblyAutoFill(e, data);
			}
			app.event.off('post.LineItemPopupSelectionAS.click');
			app.event.one('post.LineItemPopupSelectionAS.click', postPopupHandlerAS);
			popupInstance.showPopup(params, "post.LineItemPopupSelectionAS.click");
		});
	},
	handleSubAssemblyAutoFill: function (e, data) {
		let ids = Object.keys(data[0]);
		let IdOfselected = ids[0];
		let module = data[0][IdOfselected].module;
		let dataOf = {};
		dataOf['record'] = IdOfselected;
		dataOf['module'] = 'ServiceReports';
		dataOf['action'] = 'GetDetailsForSubAssAutoFill';
		dataOf['sad_sub_ass_po_det'] = data[0][IdOfselected].info.sad_sub_ass_po_det
		app.helper.showProgress();
		app.request.get({ data: dataOf }).then(function (err, response) {
			if (err == null) {
				if (response && response.data) {
					if (response.data.sad_war_start_con &&
						response.data.sad_war_start_con != '') {
						$('select[name="sad_war_start_con1"]').val(response.data.sad_war_start_con);
						$('select[name="sad_war_start_con1"]').select2().trigger('change');
					}

					if (response.data.sad_date_oracs &&
						response.data.sad_date_oracs != '') {
						$('#sad_date_oracs1').val(response.data.sad_date_oracs);
					}
					if (response.data.sad_war_term &&
						response.data.sad_war_term != '') {
						$('select[name="sad_war_term1"]').val(response.data.sad_war_term);
						$('select[name="sad_war_term1"]').select2().trigger('change');
					}
					if (response.data.sad_sub_ass_km &&
						response.data.sad_sub_ass_km != '') {
						$('#sad_sub_ass_km1').val(response.data.sad_sub_ass_km);
					}
					if (response.data.sad_sub_ass_mon &&
						response.data.sad_sub_ass_mon != '') {
						$('#sad_sub_ass_mon1').val(response.data.sad_sub_ass_mon);
					}
					if (response.data.sad_sub_ass_hmr &&
						response.data.sad_sub_ass_hmr != '') {
						$('#sad_sub_ass_hmr1').val(response.data.sad_sub_ass_hmr);
					}
				}
			}
			app.helper.hideProgress();
		});
	},

	getMaxiumFileUploadingSize: function (container) {
		return (1024) * (1024) * 1;
	},

	registerFileElementChangeEvent1: function (container) {
		var thisInstance = this;
		container.on('change', 'input[name="vis_chk_ext_dam_img[]"]', function (e) {
			if ($(this)[0].MultiFile.total_size < thisInstance.getMaxiumFileUploadingSize(container)) {
				console.log("first");
				// if (e.target.type == "text") return false;
				var moduleName = jQuery('[name="module"]').val();
				if (moduleName == "Products") return false;
				Vtiger_Edit_Js.file = e.target.files[0];
				var element = container.find('[name="vis_chk_ext_dam_img[]"]');
				if (element.attr('type') != 'file') {
					return;
				}
				var uploadFileSizeHolder = element.closest('.fileUploadContainer').find('.uploadedFileSize');
				var fileSize = e.target.files[0].size;
				var fileName = e.target.files[0].name;
				var maxFileSize = thisInstance.getMaxiumFileUploadingSize(container);
				console.log(FileSize);
				console.log(maxFileSize);
				if (fileSize > maxFileSize) {
					app.helper.showAlertNotification({
						'message': app.vtranslate('JS_EXCEEDS_MAX_UPLOAD_SIZE')
					});
					var removeFileLinks = jQuery('.MultiFile-remove');
					jQuery(removeFileLinks[removeFileLinks.length - 1]).click();
					element.val('');
					uploadFileSizeHolder.text('');
				} else {
					if (container.length > 1) {
						jQuery('div.fieldsContainer').find('form#I_form').find('input[name="filename"]').css('width', '80px');
						jQuery('div.fieldsContainer').find('form#W_form').find('input[name="filename"]').css('width', '80px');
					} else {
						container.find('input[name="filename"]').css('width', '80px');
					}
					uploadFileSizeHolder.text(fileName + ' ' + thisInstance.convertFileSizeInToDisplayFormat(fileSize));
				}
				jQuery(e.currentTarget).addClass('ignore-validation');
			} else {
				console.log($(this).length);
				var totalSize = $(this)[0].MultiFile.total_size;
				totalSize = totalSize / 1024;
				totalSize = totalSize / 1024;
				totalSize = Math.round(totalSize)
				app.helper.showAlertNotification({
					'message': app.vtranslate(totalSize + 'mb total size more 40mb')
				});
				var removeFileLinks = jQuery('.MultiFile-remove');
				jQuery(removeFileLinks[removeFileLinks.length - 1]).click();
			}
		});
	},

	registerFileElementChangeEvent2: function (container) {
		var thisInstance = this;
		container.on('change', 'input[name="vis_hyd_air_dam_img[]"]', function (e) {
			if ($(this)[0].MultiFile.total_size < thisInstance.getMaxiumFileUploadingSize(container)) {
				if (e.target.type == "text") return false;
				var moduleName = jQuery('[name="module"]').val();
				if (moduleName == "Products") return false;
				Vtiger_Edit_Js.file = e.target.files[0];
				var element = container.find('[name="vis_hyd_air_dam_img[]"]');
				if (element.attr('type') != 'file') {
					return;
				}
				var uploadFileSizeHolder = element.closest('.fileUploadContainer').find('.uploadedFileSize');
				var fileSize = e.target.files[0].size;
				var fileName = e.target.files[0].name;
				var maxFileSize = thisInstance.getMaxiumFileUploadingSize(container);
				console.log(maxFileSize);
				if (fileSize > maxFileSize) {
					app.helper.showAlertNotification({
						'message': app.vtranslate('JS_EXCEEDS_MAX_UPLOAD_SIZE')
					});
					var removeFileLinks = jQuery('.MultiFile-remove');
					jQuery(removeFileLinks[removeFileLinks.length - 1]).click();
					element.val('');
					uploadFileSizeHolder.text('');
				} else {
					if (container.length > 1) {
						jQuery('div.fieldsContainer').find('form#I_form').find('input[name="filename"]').css('width', '80px');
						jQuery('div.fieldsContainer').find('form#W_form').find('input[name="filename"]').css('width', '80px');
					} else {
						container.find('input[name="filename"]').css('width', '80px');
					}
					uploadFileSizeHolder.text(fileName + ' ' + thisInstance.convertFileSizeInToDisplayFormat(fileSize));
				}
				jQuery(e.currentTarget).addClass('ignore-validation');
			} else {
				console.log($(this).length);
				var totalSize = $(this)[0].MultiFile.total_size;
				totalSize = totalSize / 1024;
				totalSize = totalSize / 1024;
				totalSize = Math.round(totalSize)
				app.helper.showAlertNotification({
					'message': app.vtranslate(totalSize + 'mb total size more 40mb')
				});
				var removeFileLinks = jQuery('.MultiFile-remove');
				jQuery(removeFileLinks[removeFileLinks.length - 1]).click();
			}
		});
	},

	registerFileElementChangeEvent3: function (container) {
		var thisInstance = this;
		container.on('change', 'input[name="vis_lub_los_img[]"]', function (e) {
			if ($(this)[0].MultiFile.total_size < thisInstance.getMaxiumFileUploadingSize(container)) {
				if (e.target.type == "text") return false;
				var moduleName = jQuery('[name="module"]').val();
				if (moduleName == "Products") return false;
				Vtiger_Edit_Js.file = e.target.files[0];
				var element = container.find('[name="vis_lub_los_img[]"]');
				if (element.attr('type') != 'file') {
					return;
				}
				var uploadFileSizeHolder = element.closest('.fileUploadContainer').find('.uploadedFileSize');
				var fileSize = e.target.files[0].size;
				var fileName = e.target.files[0].name;
				var maxFileSize = thisInstance.getMaxiumFileUploadingSize(container);
				console.log(maxFileSize);
				if (fileSize > maxFileSize) {
					app.helper.showAlertNotification({
						'message': app.vtranslate('JS_EXCEEDS_MAX_UPLOAD_SIZE')
					});
					var removeFileLinks = jQuery('.MultiFile-remove');
					jQuery(removeFileLinks[removeFileLinks.length - 1]).click();
					element.val('');
					uploadFileSizeHolder.text('');
				} else {
					if (container.length > 1) {
						jQuery('div.fieldsContainer').find('form#I_form').find('input[name="filename"]').css('width', '80px');
						jQuery('div.fieldsContainer').find('form#W_form').find('input[name="filename"]').css('width', '80px');
					} else {
						container.find('input[name="filename"]').css('width', '80px');
					}
					uploadFileSizeHolder.text(fileName + ' ' + thisInstance.convertFileSizeInToDisplayFormat(fileSize));
				}
				jQuery(e.currentTarget).addClass('ignore-validation');
			} else {
				console.log($(this).length);
				var totalSize = $(this)[0].MultiFile.total_size;
				totalSize = totalSize / 1024;
				totalSize = totalSize / 1024;
				totalSize = Math.round(totalSize)
				app.helper.showAlertNotification({
					'message': app.vtranslate(totalSize + 'mb total size more 40mb')
				});
				var removeFileLinks = jQuery('.MultiFile-remove');
				jQuery(removeFileLinks[removeFileLinks.length - 1]).click();
			}
		});
	},

	registerFileElementChangeEvent4: function (container) {
		var thisInstance = this;
		container.on('change', 'input[name="vis_oil_lev_img[]"]', function (e) {
			if ($(this)[0].MultiFile.total_size < thisInstance.getMaxiumFileUploadingSize(container)) {
				if (e.target.type == "text") return false;
				var moduleName = jQuery('[name="module"]').val();
				if (moduleName == "Products") return false;
				Vtiger_Edit_Js.file = e.target.files[0];
				var element = container.find('[name="vis_oil_lev_img[]"]');
				if (element.attr('type') != 'file') {
					return;
				}
				var uploadFileSizeHolder = element.closest('.fileUploadContainer').find('.uploadedFileSize');
				var fileSize = e.target.files[0].size;
				var fileName = e.target.files[0].name;
				var maxFileSize = thisInstance.getMaxiumFileUploadingSize(container);
				console.log(maxFileSize);
				if (fileSize > maxFileSize) {
					app.helper.showAlertNotification({
						'message': app.vtranslate('JS_EXCEEDS_MAX_UPLOAD_SIZE')
					});
					var removeFileLinks = jQuery('.MultiFile-remove');
					jQuery(removeFileLinks[removeFileLinks.length - 1]).click();
					element.val('');
					uploadFileSizeHolder.text('');
				} else {
					if (container.length > 1) {
						jQuery('div.fieldsContainer').find('form#I_form').find('input[name="filename"]').css('width', '80px');
						jQuery('div.fieldsContainer').find('form#W_form').find('input[name="filename"]').css('width', '80px');
					} else {
						container.find('input[name="filename"]').css('width', '80px');
					}
					uploadFileSizeHolder.text(fileName + ' ' + thisInstance.convertFileSizeInToDisplayFormat(fileSize));
				}
				jQuery(e.currentTarget).addClass('ignore-validation');
			} else {
				console.log($(this).length);
				var totalSize = $(this)[0].MultiFile.total_size;
				totalSize = totalSize / 1024;
				totalSize = totalSize / 1024;
				totalSize = Math.round(totalSize)
				app.helper.showAlertNotification({
					'message': app.vtranslate(totalSize + 'mb total size more 40mb')
				});
				var removeFileLinks = jQuery('.MultiFile-remove');
				jQuery(removeFileLinks[removeFileLinks.length - 1]).click();
			}
		});
	},

	registerFileElementChangeEvent5: function (container) {
		var thisInstance = this;
		container.on('change', 'input[name="vis_hyd_wrk_los_img[][]"]', function (e) {
			if ($(this)[0].MultiFile.total_size < thisInstance.getMaxiumFileUploadingSize(container)) {
				if (e.target.type == "text") return false;
				var moduleName = jQuery('[name="module"]').val();
				if (moduleName == "Products") return false;
				Vtiger_Edit_Js.file = e.target.files[0];
				var element = container.find('[name="vis_hyd_wrk_los_img[][]"]');
				if (element.attr('type') != 'file') {
					return;
				}
				var uploadFileSizeHolder = element.closest('.fileUploadContainer').find('.uploadedFileSize');
				var fileSize = e.target.files[0].size;
				var fileName = e.target.files[0].name;
				var maxFileSize = thisInstance.getMaxiumFileUploadingSize(container);
				console.log(maxFileSize);
				if (fileSize > maxFileSize) {
					app.helper.showAlertNotification({
						'message': app.vtranslate('JS_EXCEEDS_MAX_UPLOAD_SIZE')
					});
					var removeFileLinks = jQuery('.MultiFile-remove');
					jQuery(removeFileLinks[removeFileLinks.length - 1]).click();
					element.val('');
					uploadFileSizeHolder.text('');
				} else {
					if (container.length > 1) {
						jQuery('div.fieldsContainer').find('form#I_form').find('input[name="filename"]').css('width', '80px');
						jQuery('div.fieldsContainer').find('form#W_form').find('input[name="filename"]').css('width', '80px');
					} else {
						container.find('input[name="filename"]').css('width', '80px');
					}
					uploadFileSizeHolder.text(fileName + ' ' + thisInstance.convertFileSizeInToDisplayFormat(fileSize));
				}
				jQuery(e.currentTarget).addClass('ignore-validation');
			} else {
				console.log($(this).length);
				var totalSize = $(this)[0].MultiFile.total_size;
				totalSize = totalSize / 1024;
				totalSize = totalSize / 1024;
				totalSize = Math.round(totalSize)
				app.helper.showAlertNotification({
					'message': app.vtranslate(totalSize + 'mb total size more 40mb')
				});
				var removeFileLinks = jQuery('.MultiFile-remove');
				jQuery(removeFileLinks[removeFileLinks.length - 1]).click();
			}
		});
	},

	DefaultActionTakenDropdown: function () {
		if ($("select[name='eq_sta_aft_act_taken']").val() == 'On Road') {
			$('[data-block="Restoration_Date"]').removeClass("hide");
			$('[data-td="restoration_date"]').removeClass("hide");
			$('[data-td="restoration_time"]').removeClass("hide");
		} else if ($("select[name='eq_sta_aft_act_taken']").val() == 'Running with Problem') {
			$('[data-block="Restoration_Date"]').removeClass("hide");
			$('[data-td="restoration_date"]').removeClass("hide");
			$('[data-td="restoration_time"]').removeClass("hide");
		} else {
			$('[data-block="Off_Road"]').removeClass("hide");
			$('[data-td="restoration_date"]').addClass("hide");
			$('[data-td="restoration_time"]').addClass("hide");
		}

		if ($("select[name='eq_sta_aft_act_taken']").val() == 'Off Road') {
			$('[data-td="off_on_account_of"]').removeClass("hide");
			$('[data-td="remarks_for_offroad"]').removeClass("hide");
			$('[data-td="at_brkdn_sr_req"]').removeClass("hide");
		} else {
			$('[data-block="Off_Road"]').addClass("hide");
			$('[data-td="off_on_account_of"]').addClass("hide");
			$('[data-td="remarks_for_offroad"]').addClass("hide");
			$('[data-td="at_brkdn_sr_req"]').addClass("hide");
		}
	},

	ActionTakenDropdown: function () {
		$('select[data-fieldname="eq_sta_aft_act_taken"]').on('change', function () {
			if ($(this).val() == "On Road") {
				$('[data-td="restoration_date"]').removeClass("hide");
				$('[data-td="restoration_time"]').removeClass("hide");
			} else if ($(this).val() == "Running with Problem") {
				$('[data-td="restoration_date"]').removeClass("hide");
				$('[data-td="restoration_time"]').removeClass("hide");
			} else {
				$('[data-td="restoration_date"]').addClass("hide");
				$('[data-td="restoration_time"]').addClass("hide");
			}

			if ($(this).val() == "Off Road") {
				$('[data-td="off_on_account_of"]').removeClass("hide");
				$('[data-td="remarks_for_offroad"]').removeClass("hide");
				$('[data-td="at_brkdn_sr_req"]').removeClass("hide");
			} else {
				$('[data-td="off_on_account_of"]').addClass("hide");
				$('[data-td="remarks_for_offroad"]').addClass("hide");
				$('[data-td="at_brkdn_sr_req"]').addClass("hide");
			}
		});
	},

	DefaultEDdependency: function () {
		//by default show hide contaract
		let val = $('input:radio[name="vis_chk_external_damages"]:checked').val();
		if (val == 'NO') {
			$('[data-td="vis_chk_ext_dam"]').addClass("hide");
			$('[data-td="vis_chk_ext_dam_img"]').addClass("hide");
		} else if (val == 'YES') {
			$('[data-td="vis_chk_ext_dam"]').removeClass("hide");
			$('[data-td="vis_chk_ext_dam_img"]').removeClass("hide");
		}
	},

	External_damage_showAndHide: function () {
		$('input:radio[name="vis_chk_external_damages"]').change(function () {
			let val = $("input:radio[name='vis_chk_external_damages']:checked").val();
			if (val == 'NO') {
				$('[data-td="vis_chk_ext_dam"]').addClass("hide");
				$('[data-td="vis_chk_ext_dam_img"]').addClass("hide");
			} else if (val == 'YES') {
				$('[data-td="vis_chk_ext_dam"]').removeClass("hide");
				$('[data-td="vis_chk_ext_dam_img"]').removeClass("hide");
			}
		});
	},

	DefaultHAdependency: function () {
		//by default show hide contaract
		let val = $("input:radio[name='vis_chk_hydraulic_air_leakages']:checked").val();
		if (val == 'NO') {
			$('[data-td="vis_chk_hyd_air"]').addClass("hide");
			$('[data-td="vis_hyd_air_dam_img"]').addClass("hide");
		} else if (val == 'YES') {
			$('[data-td="vis_chk_hyd_air"]').removeClass("hide");
			$('[data-td="vis_hyd_air_dam_img"]').removeClass("hide");
		}
	},

	HydraulicAndAirLeakages: function () {
		$('input:radio[name="vis_chk_hydraulic_air_leakages"]').change(function () {
			let val = $("input:radio[name='vis_chk_hydraulic_air_leakages']:checked").val();
			if (val == 'NO') {
				$('[data-td="vis_chk_hyd_air"]').addClass("hide");
				$('[data-td="vis_hyd_air_dam_img"]').addClass("hide");
			} else if (val == 'YES') {
				$('[data-td="vis_chk_hyd_air"]').removeClass("hide");
				$('[data-td="vis_hyd_air_dam_img"]').removeClass("hide");
			}
		});
	},

	DefaultLdependency: function () {
		//by default show hide contaract
		let val = $("input:radio[name='vis_chk_lubrication']:checked").val();
		if (val == 'OK') {
			$('[data-td="vis_chk_lub_rem"]').addClass("hide");
			$('[data-td="vis_lub_los_img"]').addClass("hide");
		} else if (val == 'NOT OK') {
			$('[data-td="vis_chk_lub_rem"]').removeClass("hide");
			$('[data-td="vis_lub_los_img"]').removeClass("hide");
		}
	},

	Lubrication: function () {
		$('input:radio[name="vis_chk_lubrication"]').change(function () {
			let val = $("input:radio[name='vis_chk_lubrication']:checked").val();
			if (val == 'OK') {
				$('[data-td="vis_chk_lub_rem"]').addClass("hide");
				$('[data-td="vis_lub_los_img"]').addClass("hide");
			} else if (val == 'NOT OK') {
				$('[data-td="vis_chk_lub_rem"]').removeClass("hide");
				$('[data-td="vis_lub_los_img"]').removeClass("hide");
			}
		});
	},

	DefaultOdependency: function () {
		//by default show hide contaract
		let val = $("input:radio[name='vis_chk_oil_levels']:checked").val();
		if (val == 'OK') {
			$('[data-td="vis_oil_lev_img"]').addClass("hide");
			$('[data-td="vis_chk_oil_rem"]').addClass("hide");
		} else if (val == 'NOT OK') {
			$('[data-td="vis_oil_lev_img"]').removeClass("hide");
			$('[data-td="vis_chk_oil_rem"]').removeClass("hide");
		}
	},

	OilLevels: function () {
		$('input:radio[name="vis_chk_oil_levels"]').change(function () {
			let val = $("input:radio[name='vis_chk_oil_levels']:checked").val();
			if (val == 'OK') {
				$('[data-td="vis_oil_lev_img"]').addClass("hide");
				$('[data-td="vis_chk_oil_rem"]').addClass("hide");
			} else if (val == 'NOT OK') {
				$('[data-td="vis_oil_lev_img"]').removeClass("hide");
				$('[data-td="vis_chk_oil_rem"]').removeClass("hide");
			}
		});
	},

	DefaulWLHdependency: function () {
		//by default show hide contaract
		let val = $("input:radio[name='vis_chk_work_loseing_hders']:checked").val();
		if (val == 'NO') {
			$('[data-td="vis_hyd_wrk_los_img"]').addClass("hide");
			$('[data-td="vis_chk_wrk_los"]').addClass("hide");
		} else if (val == 'YES') {
			$('[data-td="vis_hyd_wrk_los_img"]').removeClass("hide");
			$('[data-td="vis_chk_wrk_los"]').removeClass("hide");
		}
	},

	WorklooseningofHardwares: function () {
		$('input:radio[name="vis_chk_work_loseing_hders"]').change(function () {
			let val = $("input:radio[name='vis_chk_work_loseing_hders']:checked").val();
			if (val == 'NO') {
				$('[data-td="vis_hyd_wrk_los_img"]').addClass("hide");
				$('[data-td="vis_chk_wrk_los"]').addClass("hide");
			} else if (val == 'YES') {
				$('[data-td="vis_hyd_wrk_los_img"]').removeClass("hide");
				$('[data-td="vis_chk_wrk_los"]').removeClass("hide");
			}
		});
	},

	DefaultPENdependency: function () {
		//by default show hide contaract
		let val = $("input:radio[name='vis_chk_painting']:checked").val();
		if (val == 'Ok') {
			$('[data-td="vis_chk_painting_doc"]').addClass("hide");
			$('[data-td="vis_chk_painting_rem"]').addClass("hide");
		} else if (val == 'Not Ok') {
			$('[data-td="vis_chk_painting_doc"]').removeClass("hide");
			$('[data-td="vis_chk_painting_rem"]').removeClass("hide");
		}
	},

	CheckPainting: function () {
		$('input:radio[name="vis_chk_painting"]').change(function () {
			let val = $("input:radio[name='vis_chk_painting']:checked").val();
			if (val == 'Ok') {
				$('[data-td="vis_chk_painting_doc"]').addClass("hide");
				$('[data-td="vis_chk_painting_rem"]').addClass("hide");
			} else if (val == 'Not Ok') {
				$('[data-td="vis_chk_painting_doc"]').removeClass("hide");
				$('[data-td="vis_chk_painting_rem"]').removeClass("hide");
			}
		});
	},

	DefaultKMRdependency: function () {
		let val = $('input[name="kilometer_reading"]').val();
		if (val > 0) {
			this.hasBlockedHMR = true;
			$('input[name="hmr"]').attr('readonly', true).css('background-color', '#eeeeee !important');
		}
		let kval = $('input[name="hmr"]').val();
		if (kval > 0) {
			this.hasBlockedKM = true;
			$('input[name="kilometer_reading"]').attr('readonly', true).css('background-color', '#eeeeee !important');
		}
	},
	KMRdependency: function () {
		let self = this;
		$('input[name="kilometer_reading"]').on('input', function (event) {
			let val = $('input[name="kilometer_reading"]').val();
			if (val > 0) {
				$('input[name="hmr"]').attr('readonly', true).css('background-color', '#eeeeee !important');
			} else if (self.hasBlockedHMR == false) {
				$('input[name="hmr"]').removeAttr('readonly', true).css('background-color', '');
			}
		});
		$('input[name="hmr"]').on('input', function (event) {
			let kval = $('input[name="hmr"]').val();
			if (kval > 0) {
				$('input[name="kilometer_reading"]').attr('readonly', true).css('background-color', '#eeeeee !important');
			} else if (self.hasBlockedKM == false) {
				$('input[name="kilometer_reading"]').removeAttr('readonly', true).css('background-color', '');
			}
		});
	},

	DefaultKMRdependencyIninstalationSubass: function () {
		let val = $('input[name="sacfd_km_run"]').val();
		if (val > 0) {
			$('input[name="sacfd_hmr_at_fitment"]').attr('readonly', true).css('background-color', '#eeeeee !important');
		}
		let kval = $('input[name="sacfd_hmr_at_fitment"]').val();
		if (kval > 0) {
			$('input[name="sacfd_km_run"]').attr('readonly', true).css('background-color', '#eeeeee !important');
		}
	},
	KMRdependencyIninstalationSubass: function () {
		let self = this;
		$('input[name="sacfd_km_run"]').on('input', function (event) {
			let val = $('input[name="sacfd_km_run"]').val();
			if (val > 0) {
				$('input[name="sacfd_hmr_at_fitment"]').attr('readonly', true).css('background-color', '#eeeeee !important');
			} else {
				$('input[name="sacfd_hmr_at_fitment"]').removeAttr('readonly', true).css('background-color', '');
			}
		});
		$('input[name="sacfd_hmr_at_fitment"]').on('input', function (event) {
			let kval = $('input[name="sacfd_hmr_at_fitment"]').val();
			if (kval > 0) {
				$('input[name="sacfd_km_run"]').attr('readonly', true).css('background-color', '#eeeeee !important');
			} else {
				$('input[name="sacfd_km_run"]').removeAttr('readonly', true).css('background-color', '');
			}
		});
	},
	registerQuickCreateEvent : function (){
		var thisInstance = this;
		jQuery("#quickCreateModules").on("click",".quickCreateModule",function(e,params){
			var quickCreateElem = jQuery(e.currentTarget);
			var quickCreateUrl = quickCreateElem.data('url');
			quickCreateUrl = quickCreateUrl + '&ticket_type=BREAKDOWN';
			let equipmentId = $('input[name="equipment_id"]').val();
			quickCreateUrl = quickCreateUrl + '&equipment_id=' + equipmentId;
			let model = $("select[name='eq_sr_equip_model']").val();
			quickCreateUrl = quickCreateUrl + '&sr_equip_model=' + model;
			equipmentId = $('input[name="func_loc_id"]').val();
			quickCreateUrl = quickCreateUrl + '&func_loc_id=' + equipmentId;
			equipmentId = $('input[name="account_id"]').val();
			quickCreateUrl = quickCreateUrl + '&parent_id=' + equipmentId;
			var quickCreateModuleName = quickCreateElem.data('name');
			if (typeof params === 'undefined') {
				params = {};
			}
			if (typeof params.callbackFunction === 'undefined') {
				params.callbackFunction = function(data, err) {
					var parentModule=app.getModuleName();
					var viewname=app.view();
					if(((quickCreateModuleName == parentModule) || (quickCreateModuleName == 'Events' && parentModule == 'Calendar')) && (viewname=="List")){
						var listinstance = app.controller();
						listinstance.loadListViewRecords();
					}
				};
			}
			app.helper.showProgress();
			thisInstance.getQuickCreateForm(quickCreateUrl,quickCreateModuleName,params).then(function(data){
				app.helper.hideProgress();
				var callbackparams = {
					'cb' : function (container){
						thisInstance.registerPostReferenceEvent(container);
						app.event.trigger('post.QuickCreateForm.show',form);
						app.helper.registerLeavePageWithoutSubmit(form);
						app.helper.registerModalDismissWithoutSubmit(form);
					},
					backdrop : 'static',
					keyboard : false
				}

				app.helper.showModal(data, callbackparams);
				var form = jQuery('form[name="QuickCreate"]');
				var moduleName = form.find('[name="module"]').val();
				var Options= {
					scrollInertia: 200,
					autoHideScrollbar: true,
					setHeight:(jQuery(window).height() - jQuery('form[name="QuickCreate"] .modal-body').find('.modal-header').height() - jQuery('form[name="QuickCreate"] .modal-body').find('.modal-footer').height()- 135),
				}
				app.helper.showVerticalScroll(jQuery('form[name="QuickCreate"] .modal-body'), Options);

				var targetInstance = thisInstance;
				var moduleInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName);
				if(typeof(moduleInstance.quickCreateSave) === 'function'){
					targetInstance = moduleInstance;
					targetInstance.registerBasicEvents(form);
				}

				vtUtils.applyFieldElementsView(form);
				targetInstance.quickCreateSave(form,params);
				$('.refFieldequipment_id').addClass('hide');
			});
		});
	},
	referenceCreateHandler : function(container) {
		var thisInstance = this;
		var postQuickCreateSave = function(data) {
			var module = thisInstance.getReferencedModuleName(container);
			var params = {};
			params.name = data._recordLabel;
			params.id = data._recordId;
			params.module = module;
			thisInstance.setReferenceFieldValue(container, params);

			var tdElement = thisInstance.getParentElement(container.find('[value="'+ module +'"]'));
			var sourceField = tdElement.find('input[class="sourceField"]').attr('name');
			var fieldElement = tdElement.find('input[name="'+sourceField+'"]');
			thisInstance.autoFillElement = fieldElement;
			thisInstance.postRefrenceSearch(params, container);

			tdElement.find('input[class="sourceField"]').trigger(Vtiger_Edit_Js.postReferenceQuickCreateSave, {'data' : data});
		}

		var referenceModuleName = this.getReferencedModuleName(container);
		var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="'+ referenceModuleName +'"]');
		if(quickCreateNode.length <= 0) {
			var notificationOptions = {
				'title' : app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED')
			}
			app.helper.showAlertNotification(notificationOptions);
		}
		quickCreateNode.trigger('click',[{'callbackFunction':postQuickCreateSave}]);
	},
});