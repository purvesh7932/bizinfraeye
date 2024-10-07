Users_Detail_Js("Users_PreferenceDetail_Js",{},{
    
	/**
	 * register Events for my preference
	 */
	registerEvents : function(){
		this._super();
		Users_PreferenceEdit_Js.registerChangeEventForCurrencySeparator();
	}
});