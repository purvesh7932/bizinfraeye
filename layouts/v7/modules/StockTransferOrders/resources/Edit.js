Inventory_Edit_Js("StockTransferOrders_Edit_Js", {}, {
    registerBasicEvents: function (container) {
        this._super(container);
        this.addProductOnSelect()
        let self = this;
        self.lineItemsHolder.find('tr.' + self.lineItemDetectingClass).each(function (index, domElement) {
            var lineItemRow = jQuery(domElement);
            let val = lineItemRow.find('input[name="collect_immidiately'+(index+1)+'"]').is(":checked");
            if (val == false) {
                lineItemRow.find('.lid_remarks').removeClass('hide');
                lineItemRow.find('.lid_remarks').attr('required', true);
            }
        });
    },
    addProductOnSelect: function () {
        let self = this;
        $('input[data-fieldnameig="add_for_cre_so"]').click(function () {
            let rowNum = $(this).data("rownum");
            if ($(this).is(':checked')) {
                app.helper.showProgress();
                let recordId = $("#hdnProductId_other" + rowNum).val();
                var dataUrl = "index.php?module=Inventory&action=GetTaxes&record=" + recordId + "&currency_id=" + jQuery('#currency_id option:selected').val() + "&sourceModule=" + app.getModuleName();
                app.request.get({ 'url': dataUrl }).then(
                    function (error, data) {
                        if (error == null) {
                            let objKeys = Object.keys(data['0']);
                            data['0'][objKeys['0']]['qty'] = $("#qty_other" + rowNum).val();
                            jQuery('#addProduct').trigger('click', data);
                            app.helper.hideProgress();
                        }
                    },
                    function (error, err) {
                    }
                );
            } else {
                let recordId = $("#hdnProductId_other" + rowNum).val();
                self.lineItemsHolder.find('tr.' + self.lineItemDetectingClass).each(function (index, domElement) {
                    var lineItemRow = jQuery(domElement);
                    let ele = lineItemRow.find('input[value='+recordId+']');
                    let parentRowLine = ele.closest('tr');
                    parentRowLine.find('.deleteRow').trigger('click');
                });
            }
        });
    },
    registerAddProductService : function() {
        var self = this;
        var addLineItemEventHandler = function(e, data){
            var currentTarget = jQuery(e.currentTarget);
            var params = {'currentTarget' : currentTarget}
            var newLineItem = self.getNewLineItem(params);
            newLineItem = newLineItem.appendTo(self.lineItemsHolder);
			newLineItem.find('input.productName').addClass('autoComplete');
            newLineItem.find('.ignore-ui-registration').removeClass('ignore-ui-registration');
            vtUtils.applyFieldElementsView(newLineItem);
            app.event.trigger('post.lineItem.New', newLineItem);
            self.checkLineItemRow();
            self.registerLineItemAutoComplete(newLineItem);
            if(typeof data != "undefined") {
                self.mapResultsToFields(newLineItem,data);
            }
			let rowNum = newLineItem.find('input.rowNumber').val();
			$("#lid_sto_del_date" + rowNum).datepicker({
			});
        }
        jQuery('#addProduct').on('click', addLineItemEventHandler);
        jQuery('#addService').on('click', addLineItemEventHandler);
        $('.fa-calendar').click(function (e) {
            let element = $(this);
            let parent = element.closest('tr');
            let rowNum = parent.find(".rowNumber").val();
            jQuery('#lid_sto_del_date' + rowNum).focus();
        });
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
            // jQuery('span.clearLineItem', parentRow).addClass('hide');
            // jQuery('span.lineItemPopup', parentRow).addClass('hide');
            jQuery('textarea.lineItemCommentBox', parentRow).attr('readonly', 'readonly');
            // jQuery('input.qty', parentRow).attr('readonly', 'readonly');
            // jQuery('input.qty', parentRow).attr('readonly', 'readonly');
            jQuery('input.collect_immidiately', parentRow).prop('checked', true);;
        } else {
            parentRow.find('.lid_remarks').removeClass('hide');
            parentRow.find('.lid_remarks').attr('required', true);
        }
    },
    checkLineItemRow: function () {
		var numRows = this.lineItemsHolder.find('.' + this.lineItemDetectingClass).length;
		if (numRows > 0) {
			this.showLineItemsDeleteIcon();
		} else {
			this.hideLineItemsDeleteIcon();
		}
	},
});