
Vtiger_List_Js("Calendar_List_Js", {
	triggerMassEdit: function(massEditUrl) {
		Vtiger_List_Js.triggerMassAction(massEditUrl);
	}
}, {

	registerDeleteRecordClickEvent :function() {
		var thisInstance = this;

		jQuery('#page').on('click', '.deleteRecordButton', function(e){
			var elem = jQuery(e.currentTarget);
			var originalDropDownMenu = elem.closest('.dropdown-menu').data('original-menu');
			var parent = app.helper.getDropDownmenuParent(originalDropDownMenu);
			var row  = parent.closest('tr');
			var recordId = row.data('id');
			var isRecurringEnabled = row.data('recurringEnabled');
			if(isRecurringEnabled === 1) {
				app.helper.showConfirmationForRepeatEvents().then(function(postData) {
					thisInstance._deleteRecord(recordId,postData);
				});
			} else {
				thisInstance.deleteRecord(recordId);
			}
			e.stopPropagation();
		});
	},

	registerRowDoubleClickEvent: function () {
		return true;
	},
	registerEvents: function() {
		this._super();
	}
});