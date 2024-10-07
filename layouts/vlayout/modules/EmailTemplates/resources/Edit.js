Vtiger_Edit_Js("EmailTemplates_Edit_Js",{},{
	
	/**
	 * Function to register event for ckeditor for description field
	 */
	registerEventForCkEditor : function(){
		var templateContentElement = jQuery("#templatecontent");
		if(templateContentElement.length > 0) {
			var ckEditorInstance = new Vtiger_CkEditor_Js();
			ckEditorInstance.loadCkEditor(templateContentElement);
		}
		this.registerFillTemplateContentEvent();
	},
	
	/**
	 * Function which will register module change event
	 */
	registerChangeEventForModule : function(){
		var thisInstance = this;
		var advaceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance();
		var filterContainer = advaceFilterInstance.getFilterContainer();
		filterContainer.on('change','select[name="modulename"]',function(e){
			thisInstance.loadFields();
		});
	},
	
	/**
	 * Function to load condition list for the selected field
	 * @params : fieldSelect - select element which will represents field list
	 * @return : select element which will represent the condition element
	 */
	loadFields : function() {
		var moduleName = jQuery('select[name="modulename"]').val();
		var allFields = jQuery('[name="moduleFields"]').data('value');
		var fieldSelectElement = jQuery('select[name="templateFields"]');
		var options = '';
		for(var key in allFields) {
			//IE Browser consider the prototype properties also, it should consider has own properties only.
			if(allFields.hasOwnProperty(key) && key == moduleName) {
				var moduleSpecificFields = allFields[key];
				var len = moduleSpecificFields.length;
				for (var i = 0; i < len; i++) {
					var fieldName = moduleSpecificFields[i][0].split(':');
					options += '<option value="'+moduleSpecificFields[i][1]+'"';
					if(fieldName[0] == moduleName) {
						options += '>'+fieldName[1]+'</option>';
					} else {
						options += '>'+moduleSpecificFields[i][0]+'</option>';
					}
				}
			}
		}
		
		if(options == '')
			options = '<option value="">NONE</option>';
		
		fieldSelectElement.empty().html(options).trigger("liszt:updated");
		return fieldSelectElement;
		
	},
	
	registerFillTemplateContentEvent : function() {
		jQuery('#templateFields').change(function(e){
			var textarea = CKEDITOR.instances.templatecontent;
			var value = jQuery(e.currentTarget).val();
			textarea.insertHtml(value);
		});
	},

/**
	 * Registered the events for this page
	 */
	registerEvents : function() {
		this.registerEventForCkEditor();
		this.registerChangeEventForModule();
		//jQuery('#EditView').validationEngine();
		this._super();
	}
});

