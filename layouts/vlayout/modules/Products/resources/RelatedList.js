PriceBooks_RelatedList_Js("Products_RelatedList_Js",{},{
	
	/**
	 * Function to get params for show event invocation
	 */
	getPopupParams : function(){
		var parameters = {
			'module' : this.relatedModulename,
			'src_module' :this.parentModuleName ,
			'src_record' : this.parentRecordId,
			'view' : "ProductPriceBookPopup",
			'src_field' : 'productsRelatedList',
			'multi_select' : true
		}
		return parameters;
	}
})