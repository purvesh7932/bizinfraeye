Inventory_Edit_Js("HelpDesk_Edit_Js", {}, {

	accountsReferenceField: false,
	contactsReferenceField: false,

	initializeVariables: function () {
		this._super();
		var form = this.getForm();
		this.accountsReferenceField = form.find('[name="account_id"]');
		this.contactsReferenceField = form.find('[name="contact_id"]');
	},

	/**
	 * Function to get popup params
	 */
	getPopUpParams: function (container) {
		var params = this._super(container);
		var sourceFieldElement = jQuery('input[class="sourceField"]', container);
		var referenceModule = jQuery('input[name=popupReferenceModule]', container).val();
		if (!sourceFieldElement.length) {
			sourceFieldElement = jQuery('input.sourceField', container);
		}

		if ((sourceFieldElement.attr('name') == 'contact_id' || sourceFieldElement.attr('name') == 'potential_id') && referenceModule != 'Leads') {
			var form = this.getForm();
			var parentIdElement = form.find('[name="account_id"]');
			if (parentIdElement.length > 0 && parentIdElement.val().length > 0 && parentIdElement.val() != 0) {
				var closestContainer = parentIdElement.closest('td');
				params['related_parent_id'] = parentIdElement.val();
				params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
			} else if (sourceFieldElement.attr('name') == 'potential_id') {
				parentIdElement = form.find('[name="contact_id"]');
				var relatedParentModule = parentIdElement.closest('td').find('input[name="popupReferenceModule"]').val()
				if (parentIdElement.length > 0 && parentIdElement.val().length > 0 && relatedParentModule != 'Leads') {
					closestContainer = parentIdElement.closest('td');
					params['related_parent_id'] = parentIdElement.val();
					params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
				}
			}
		}
		return params;
	},

	/**
	 * Function which will register event for Reference Fields Selection
	 */
	registerReferenceSelectionEvent: function (container) {
		this._super(container);
		var self = this;

		this.accountsReferenceField.on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, data) {
			self.referenceSelectionEventHandler(data, container);
		});
	},

	/**
	 * Function to search module names
	 */
	searchModuleNames: function (params) {
		var aDeferred = jQuery.Deferred();

		if (typeof params.module == 'undefined') {
			params.module = app.getModuleName();
		}
		if (typeof params.action == 'undefined') {
			params.action = 'BasicAjax';
		}

		if (typeof params.base_record == 'undefined') {
			var record = jQuery('[name="record"]');
			var recordId = app.getRecordId();
			if (record.length) {
				params.base_record = record.val();
			} else if (recordId) {
				params.base_record = recordId;
			} else if (app.view() == 'List') {
				var editRecordId = jQuery('#listview-table').find('tr.listViewEntries.edited').data('id');
				if (editRecordId) {
					params.base_record = editRecordId;
				}
			}
		}

		if (params.search_module == 'Contacts' || params.search_module == 'Potentials') {
			var form = this.getForm();
			if (this.accountsReferenceField.length > 0 && this.accountsReferenceField.val().length > 0) {
				var closestContainer = this.accountsReferenceField.closest('td');
				params.parent_id = this.accountsReferenceField.val();
				params.parent_module = closestContainer.find('[name="popupReferenceModule"]').val();
			} else if (params.search_module == 'Potentials') {

				if (this.contactsReferenceField.length > 0 && this.contactsReferenceField.val().length > 0) {
					closestContainer = this.contactsReferenceField.closest('td');
					params.parent_id = this.contactsReferenceField.val();
					params.parent_module = closestContainer.find('[name="popupReferenceModule"]').val();
				}
			}
		}

		// Added for overlay edit as the module is different
		if (params.search_module == 'Products' || params.search_module == 'Services') {
			params.module = 'Quotes';
		}

		app.request.get({ 'data': params }).then(
			function (error, data) {
				if (error == null) {
					aDeferred.resolve(data);
				}
			},
			function (error) {
				aDeferred.reject();
			}
		)
		return aDeferred.promise();
	},
	registerBasicEvents: function (container) {
		this._super(container);
		this.registerForTogglingBillingandShippingAddress();
		this.registerEventForCopyAddress();
		this.registerReferenceSelectionEvent1();
		this.initialBlocking();
	},
	registerReferenceSelectionEvent1: function (container) {
		var self = this;
		jQuery('input[name="parent_id"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, data) {
			self.autofill();
		});
	},
	initialBlocking: function () {
		$("textarea[name='address']").css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
		$("input[name='product_modal']").css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
		$("input[name='warrenty_period']").css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
		$("input[name='productname']").css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
		$('[data-fieldname="product_subcategory"]').attr("readonly", 'readonly');
		$('[data-fieldname="product_category"]').attr("readonly", 'readonly');
		$("input[name='customer_name']").css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
		$("input[name='mobile']").css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
		$("input[name='product_name']").css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
	},
	autofill: function () {
		let dataOf = {};
		dataOf['record'] = $("input[name=parent_id]").val();
		dataOf['source_module'] = 'Accounts';
		dataOf['module'] = 'HelpDesk';
		dataOf['action'] = 'GetAllInfoCust';
		app.helper.showProgress();
		app.request.get({ data: dataOf }).then(function (err, response) {
			if (err == null) {
				$("textarea[name='address']").val(response.data.address).css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
				$("input[name='product_modal']").val(response.data.model_number).css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
				$("input[name='warrenty_period']").val(response.data.warrenty_period).css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
				$("input[name='productname']").val(response.data.productname).css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
				let product_subcategory = response.data.product_subcategory;
				$('.product_subcategory  option[value="' + product_subcategory + '"]').attr("selected", "selected").trigger('change');
				$('[data-fieldname="product_subcategory"]').attr("readonly", 'readonly');

				let product_category = response.data.product_category;
				$('.product_category  option[value="' + product_category + '"]').attr("selected", "selected").trigger('change');
				$('[data-fieldname="product_category"]').attr("readonly", 'readonly');

				$("input[name='customer_name']").val(response.data.accountname).css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
				$("input[name='mobile']").val(response.data.phone).css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
				$("input[name='product_name']").val(response.data.productname).css('background-color', '#eeeeee !important').attr("readonly", 'readonly');
				app.helper.hideProgress();
			}
		});
	},
});