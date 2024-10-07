jQuery.Class("Migration_Index_Js",{
	
	startMigrationEvent : function(){
		
		var migrateUrl = 'index.php?module=Migration&view=Index&mode=applyDBChanges';
			AppConnector.request(migrateUrl).then(
			function(data) {
				jQuery("#running").hide();
				jQuery("#success").show();
				jQuery("#nextButton").show();
				jQuery("#showDetails").show().html(data);
			})
	},
	
	registerEvents : function(){
		this.startMigrationEvent();
	}
	
});
