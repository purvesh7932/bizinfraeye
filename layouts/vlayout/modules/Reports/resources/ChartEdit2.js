

Reports_Edit3_Js("Reports_ChartEdit2_Js",{},{

	calculateValues : function(){
		//handled advanced filters saved values.
		var advfilterlist = this.advanceFilterInstance.getValues();
		jQuery('#advanced_filter').val(JSON.stringify(advfilterlist));
	},

	initialize : function(container) {
		if(typeof container == 'undefined') {
			container = jQuery('#chart_report_step2');
		}

		if(container.is('#chart_report_step2')) {
			this.setContainer(container);
		}else{
			this.setContainer(jQuery('#chart_report_step2'));
		}
	},

	submit : function(){
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		thisInstance.calculateValues();
		var form = this.getContainer();
		var formData = form.serializeFormData();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});
		AppConnector.request(formData).then(
			function(data) {
				form.hide();
				progressIndicatorElement.progressIndicator({
					'mode' : 'hide'
				})
				aDeferred.resolve(data);
			},
			function(error,err){

			}
		);
		return aDeferred.promise();
	}
});