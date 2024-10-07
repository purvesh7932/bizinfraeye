Vtiger_Edit_Js("Accounts_Edit_Js", {

}, {

	//This will store the editview form
	editViewForm: false,

	//Address field mapping within module
	addressFieldsMappingInModule: {
		'bill_street': 'ship_street',
		'bill_pobox': 'ship_pobox',
		'bill_city': 'ship_city',
		'bill_state': 'ship_state',
		'bill_code': 'ship_code',
		'bill_country': 'ship_country'
	},

	// mapping address fields of MemberOf field in the module              
	memberOfAddressFieldsMapping: {
		'bill_street': 'bill_street',
		'bill_pobox': 'bill_pobox',
		'bill_city': 'bill_city',
		'bill_state': 'bill_state',
		'bill_code': 'bill_code',
		'bill_country': 'bill_country',
		'ship_street': 'ship_street',
		'ship_pobox': 'ship_pobox',
		'ship_city': 'ship_city',
		'ship_state': 'ship_state',
		'ship_code': 'ship_code',
		'ship_country': 'ship_country'
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

	/**
	 * Function to copy address between fields
	 * @param strings which accepts value as either odd or even
	 */
	copyAddress: function (swapMode, container) {
		var thisInstance = this;
		var addressMapping = this.addressFieldsMappingInModule;
		if (swapMode == "false") {
			for (var key in addressMapping) {
				var fromElement = container.find('[name="' + key + '"]');
				var toElement = container.find('[name="' + addressMapping[key] + '"]');
				toElement.val(fromElement.val());
			}
		} else if (swapMode) {
			var swappedArray = thisInstance.swapObject(addressMapping);
			for (var key in swappedArray) {
				var fromElement = container.find('[name="' + key + '"]');
				var toElement = container.find('[name="' + swappedArray[key] + '"]');
				toElement.val(fromElement.val());
			}
		}
	},

	/**
	 * Function to register event for copying address between two fileds
	 */
	registerEventForCopyingAddress: function (container) {
		var thisInstance = this;
		var swapMode;
		jQuery('[name="copyAddress"]').on('click', function (e) {
			var element = jQuery(e.currentTarget);
			var target = element.data('target');
			if (target == "billing") {
				swapMode = "false";
			} else if (target == "shipping") {
				swapMode = "true";
			}
			thisInstance.copyAddress(swapMode, container);
		})
	},

	/**
	 * Function which will copy the address details - without Confirmation
	 */
	copyAddressDetails: function (data, container) {
		var thisInstance = this;
		thisInstance.getRecordDetails(data).then(
			function (data) {
				var response = data['result'];
				thisInstance.mapAddressDetails(thisInstance.memberOfAddressFieldsMapping, response['data'], container);
			},
			function (error, err) {

			});
	},

	/**
	 * Function which will map the address details of the selected record
	 */
	mapAddressDetails: function (addressDetails, result, container) {
		for (var key in addressDetails) {
			// While Quick Creat we don't have address fields, we should  add
			if (container.find('[name="' + key + '"]').length == 0) {
				container.append("<input type='hidden' name='" + key + "'>");
			}
			container.find('[name="' + key + '"]').val(result[addressDetails[key]]);
			container.find('[name="' + key + '"]').trigger('change');
			container.find('[name="' + addressDetails[key] + '"]').val(result[addressDetails[key]]);
			container.find('[name="' + addressDetails[key] + '"]').trigger('change');
		}
	},

	/**
	 * Function which will register basic events which will be used in quick create as well
	 *
	 */
	registerBasicEvents: function (container) {
		this._super(container);
		this.registerEventForCopyingAddress(container);
		this.registerReferenceSelectionEventInModule();
		this.initialBlocking();
	},
	registerReferenceSelectionEventInModule: function (container) {
		var self = this;
		jQuery('input[name="equipment_id"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, data) {
			self.autofill();
		});
	},
	initialBlocking: function () {
		$("textarea[name='address']").css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
		$("input[name='model_number']").css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
		$("input[name='warrenty_period']").css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
		$("input[name='productname']").css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
		$('[data-fieldname="product_subcategory"]').attr("readonly", 'readonly');
		$('[data-fieldname="product_category"]').attr("readonly", 'readonly');
		$('[data-fieldname="product_brand"]').attr("readonly", 'readonly');
		// $("input[name='serial_number']").css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
		$("input[name='customer_name']").css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
		$("input[name='mobile']").css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
		$("input[name='product_name']").css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
	},
	autofill: function () {
		let dataOf = {};
		dataOf['record'] = $("input[name=equipment_id]").val();
		dataOf['source_module'] = 'Equipment';
		dataOf['module'] = 'Accounts';
		dataOf['action'] = 'GetAllInfoEquip';
		app.helper.showProgress();
		app.request.get({ data: dataOf }).then(function (err, response) {
			if (err == null) {
				$("textarea[name='address']").val(response.data.address).css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
				$("input[name='model_number']").val(response.data.model_number).css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
				$("input[name='warrenty_period']").val(response.data.warrenty_period).css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
				$("input[name='productname']").val(response.data.productname).css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
				let product_subcategory = response.data.product_subcategory;
				$('.product_subcategory  option[value="' + product_subcategory + '"]').attr("selected", "selected").trigger('change');
				$('[data-fieldname="product_subcategory"]').attr("readonly", 'readonly');

				let product_category = response.data.product_category;
				$('.product_category  option[value="' + product_category + '"]').attr("selected", "selected").trigger('change');
				$('[data-fieldname="product_category"]').attr("readonly", 'readonly');

				let product_brand = response.data.product_brand;
				$('.product_brand  option[value="' + product_brand + '"]').attr("selected", "selected").trigger('change');
				$('[data-fieldname="product_brand"]').attr("readonly", 'readonly');
				// $("input[name='serial_number']").val(response.data.equipment_sl_no).css('background-color', '#eeeeee !important').attr("readonly", 'readonly');

				$("input[name='customer_name']").val(response.data.accountname).css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
				$("input[name='mobile']").val(response.data.phone).css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
				$("input[name='product_name']").val(response.data.phone).css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
				app.helper.hideProgress();
			}
		});
	},
});