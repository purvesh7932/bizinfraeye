Inventory_Edit_Js("FailedParts_Edit_Js", {}, {

	registerBasicEvents: function (container) {
		this._super(container);
		this.editShowVendorsName();
		this.registerExcludedQuantityChangeEvent();
		this.StatusDependency();
		this.StatusDependancyChangeEvent();
	},

	editShowVendorsName: function () {
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
	},

	registerExcludedQuantityChangeEvent1: function () {
		var self = this;
		this.lineItemsHolder.on('focusout', '.excluded_qty', function (e) {
			var element = jQuery(e.currentTarget);
			var lineItemRow = element.closest('tr.' + self.lineItemDetectingClass);
			let failedqty = lineItemRow.find('input[data-extraname="pending_qty_for_validation"]').val();
			failedqty = parseInt(failedqty);
			var quantity = element.val();
			if (typeof quantity != 'undefined') {
				if (parseFloat(quantity) > 0) {
					lineItemRow.find('input[data-extraname="excluded_qty"]').attr('max', failedqty);
					lineItemRow.find('.excluded_qty_rem').removeClass('hide');
					lineItemRow.find('.excluded_qty_rem').attr('required', true);
				} else {
					lineItemRow.find('.excluded_qty_rem').addClass('hide');
					lineItemRow.find('.excluded_qty_rem').attr('required', false);
				}
			}
		});
	},

	registerExcludedQuantityChangeEvent: function () {
		let self = this;
		self.lineItemsHolder.find('tr.' + self.lineItemDetectingClass).each(function (index, domElement) {
			var lineItemRow = jQuery(domElement);
			let rowNum = lineItemRow.closest('tr').data('row-num');
			let failedqty = lineItemRow.find('input[data-extraname="pending_qty_for_validation"]').val();
			failedqty = parseFloat(failedqty);
			if (failedqty == 0 || failedqty < 0 || failedqty == null || failedqty == "") {
				let recqty = lineItemRow.find('input[data-extraname="pending_qty_to_sub"]').val();
				recqty = parseFloat(recqty);
				if (recqty > 0) {
					lineItemRow.find('input[data-extraname="excluded_qty"]').attr('max', recqty);
				}
			} else {
				let recqty = lineItemRow.find('input[data-extraname="rcvd_qty_tr_validate"]').val();
				recqty = parseInt(recqty);
				if (recqty > 0) {
					lineItemRow.find('input[data-extraname="rcvd_qty_tr_validate"]').attr('max', failedqty);
				}
			}
			let exqty = lineItemRow.find('input[data-extraname="excluded_qty"]').val();
			exqty = parseInt(exqty);
			if (exqty > 0) {
				lineItemRow.find('input[data-extraname="excluded_qty"]').attr('max', failedqty);
				lineItemRow.find('#excluded_qty_remDivCla').removeClass('hide');
				lineItemRow.find('#excluded_qty_rem' + rowNum).attr('required', true);
			} else {
				lineItemRow.find('#excluded_qty_remDivCla').addClass('hide');
			}
		});

		//for excluded qty
		$('input[data-extraname="excluded_qty"]').on('input', function (event) {
			let rowNum = $(this).closest('tr').data('row-num');
			let val = $(this).val();
			val = parseFloat(val);
			let failedqty = $(this).closest('tr').find('input[data-extraname="pending_qty_for_validation"]').val();
			failedqty = parseFloat(failedqty);
			if (failedqty == 0 || failedqty < 0 || failedqty == null || failedqty == ""
				|| isNaN(failedqty)) {
				let recqty = $(this).closest('tr').find('input[data-extraname="pending_qty_to_sub"]').val();
				recqty = parseFloat(recqty);
				if (recqty > 0) {
					$(this).closest('tr').find('input[data-extraname="excluded_qty"]').attr('max', recqty);
				}
				if (val > 0) {
					$(this).closest('tr').find('#excluded_qty_remDivCla').removeClass('hide');
					$(this).closest('tr').find('#excluded_qty_rem' + rowNum).attr('required', true);
				} else {
					$(this).closest('tr').find('#excluded_qty_remDivCla').addClass('hide');
				}
			} else {
				let recqty = $(this).closest('tr').find('input[data-extraname="rcvd_qty_tr_validate"]').val();
				recqty = parseFloat(recqty);
				if (isNaN(recqty)) {
					recqty = 0;
				}
				let pendingQtyToBeSubmitted = $(this).closest('tr').find('input[data-extraname="pending_qty_to_sub"]').val();
				let maxExcludeqty = parseFloat(pendingQtyToBeSubmitted) - recqty;
				$(this).closest('tr').find('.excluded_qty').attr('max', maxExcludeqty);
				// if (failedqty == recqty) {
				// 	$('.excluded_qty').attr('max', 0);
				// 	return;
				// }
				// if (failedqty < total) {
				// 	let bal = failedqty - recqty;
				// 	console.log(bal);
				// 	$('.excluded_qty').attr('max', bal);
				// 	return;
				// }
				if (val > 0) {
					$(this).closest('tr').find('#excluded_qty_remDivCla').removeClass('hide');
					$(this).closest('tr').find('#excluded_qty_rem' + rowNum).attr('required', true);
				} else {
					$(this).closest('tr').find('#excluded_qty_remDivCla').addClass('hide');
				}
			}
		});

		//for received qty
		$('input[data-extraname="rcvd_qty_tr_validate"]').on('input', function (event) {
			let recqty = $(this).closest('tr').find('input[data-extraname="rcvd_qty_tr_validate"]').val();
			recqty = parseFloat(recqty);
			if (isNaN(recqty)) {
				recqty = 0;
			}

			let pendingQtyToBeSubmitted = $(this).closest('tr').find('input[data-extraname="pending_qty_to_sub"]').val();
			let maxExcludeqty = parseFloat(pendingQtyToBeSubmitted) - recqty;
			$(this).closest('tr').find('.excluded_qty').attr('max', maxExcludeqty);

			let failedqty = $(this).closest('tr').find('input[data-extraname="pending_qty_for_validation"]').val();
			failedqty = parseFloat(failedqty);
			$(this).closest('tr').find('.rcvd_qty_tr_validate').attr('max', failedqty);

			// let rowNum = $(this).closest('tr').data('row-num');
			// let failedqty = $(this).closest('tr').find('input[data-extraname="fail_pa_sb_qty"]').val();
			// failedqty = parseInt(failedqty);
			// let val = $(this).val();
			// val = parseInt(val);
			// let exqty = $(this).closest('tr').find('input[data-extraname="excluded_qty"]').val();
			// exqty = parseInt(exqty);
			// let total = exqty + val;
			// $('.rcvd_qty_tr_validate').attr('max', failedqty);

			// if (failedqty == exqty) {
			// 	$('.rcvd_qty_tr_validate').attr('max', 0);
			// 	return;
			// }

			// if (failedqty < total) {
			// 	let bal = failedqty - exqty;
			// 	console.log(bal);
			// 	$('.rcvd_qty_tr_validate').attr('max', bal);
			// 	return;
			// }

			// if (val < failedqty) {
			// 	$('.excluded_qty').attr('max',balance);
			// 	return;
			// }

			// if(balance < val)
			// {
			// 	$('.rcvd_qty_tr_validate').attr('max',balance);
			// 	return;
			// }
		});

		$('input[data-extraname="fail_pa_sb_qty"]').on('input', function (event) {
			let recqty = $(this).closest('tr').find('input[data-extraname="pending_qty_to_sub"]').val();
			recqty = parseFloat(recqty);
			if (isNaN(recqty)) {
				recqty = 0;
			}
			$(this).closest('tr').find('.fail_pa_sb_qty').attr('max', recqty);
		});
	},

	StatusDependency: function () {
		var self = this;
		this.lineItemsHolder.on('change', '.fail_pa_pa_status', function (e) {
			var element = jQuery(e.currentTarget);
			var lineItemRow = element.closest('tr.' + self.lineItemDetectingClass);
			var status = element.val();
			if (status == 'Closed') {
				lineItemRow.find('.pending_days').addClass('hide');
			} else {
				lineItemRow.find('.pending_days').remooveClass('hide');
			}
		});
	},

	StatusDependancyChangeEvent: function () {
		let self = this;
		self.lineItemsHolder.find('tr.' + self.lineItemDetectingClass).each(function (index, domElement) {
			var lineItemRow = jQuery(domElement);
			let status = lineItemRow.find('select[data-extraname="fail_pa_pa_status"]').val();
			let rowNum = lineItemRow.closest('tr').data('row-num');
			if (status == 'Closed') {
				lineItemRow.find('.pending_days').addClass('hide');
			} else {
				lineItemRow.find('.pending_days').removeClass('hide');
			}
			let subittedValue = parseFloat(lineItemRow.find('input[data-extraname="pending_qty_for_validation"]').val());
			if (subittedValue == 0 || subittedValue < 0 || subittedValue == null ||
				subittedValue == "" || isNaN(subittedValue)) {
				lineItemRow.find('.rcvd_qty_tr_validate').attr('readonly', true).css('background-color', '#eeeeee !important');
			}
			let totalExcludedQuantity = parseFloat(jQuery('#total_excluded_qty' + rowNum).val());
			let totalValidatedRecivedQty = parseFloat(lineItemRow.find('input[data-extraname="rcvd_qty_validated"]').val());

			let lineQty = parseFloat(lineItemRow.find('input[data-extraname="qty"]').val());
			let remainingQty =  lineQty - (totalExcludedQuantity + totalValidatedRecivedQty);
			if(remainingQty == 0 || remainingQty < 0 ){
				lineItemRow.find('.excluded_qty').attr('readonly', true).css('background-color', '#eeeeee !important');
			}
		});
		$('select[data-extraname="fail_pa_pa_status"]').on('change', function (event) {
			let status = $(this).val();
			let rowNum = $(this).closest('tr').data('row-num');
			if (status == 'Closed') {
				$(this).closest('tr').find('.pending_days').addClass('hide');
			} else {
				$(this).closest('tr').find('.pending_days').removeClass('hide');
			}
		});
	},
});