
jQuery.Class("Vtiger_RelatedList_Js",{
	loaded : true,
	
	getInstance: function(parentId, parentModule, selectedRelatedTabElement, relatedModuleName) {
		var moduleClassName = parentModule+"_RelatedList_Js";
		var fallbackClassName = Vtiger_RelatedList_Js;
		if(typeof window[moduleClassName] != 'undefined') {
			var instance = new window[moduleClassName](parentId, parentModule, selectedRelatedTabElement, relatedModuleName);
		} else {
			var instance = new fallbackClassName(parentId, parentModule, selectedRelatedTabElement, relatedModuleName);
		}

		return instance;
	}

},{
	
	selectedRelatedTabElement : false,
	parentRecordId : false,
	parentModuleName : false,
	relatedModulename : false,
	relatedTabsContainer : false,
	detailViewContainer : false,
	relatedContentContainer : false,
    parentId : false,
	
	setSelectedTabElement : function(tabElement) {
		this.selectedRelatedTabElement = tabElement;
	},
	
	getSelectedTabElement : function(){
		return this.selectedRelatedTabElement;
	},
	
	triggerDisplayTypeEvent : function() {
		var widthType = app.cacheGet('widthType', 'narrowWidthType');
		if(widthType) {
			var elements = jQuery('.listViewEntriesTable').find('td,th');
			elements.attr('class', widthType);
		}
	},
    
	updateRelatedRecordsCount: function (relationId) {
		var recordId = app.getRecordId();
		var moduleName = app.getModuleName();
		var detailInstance = new Vtiger_Detail_Js();
		detailInstance.getRelatedRecordsCount(recordId, moduleName, relationId).then(function (data) {
			let relatedRecordsCount = '';
			jQuery.each(data, function(key, value){
				relatedRecordsCount = value;
				var element = new Object(jQuery("a", "li[data-relation-id=" + key + "]"));
				// we should only show if there are any related records
				var numberEle = element.find('.numberCircle');
				numberEle.text(relatedRecordsCount);
				if (relatedRecordsCount > 0) {
					numberEle.removeClass('hide');
				} else {
					numberEle.addClass('hide');
				}
				element.attr("recordscount", relatedRecordsCount);
			});
		});
	},

	getCurrentPageNum : function() {
		return jQuery('input[name="currentPageNum"]',this.relatedContentContainer).val();
	},
	
	setCurrentPageNumber : function(pageNumber){
		jQuery('input[name="currentPageNum"]').val(pageNumber);
	},
	
	/**
	 * Function to get Order by
	 */
	getOrderBy : function(){
		return jQuery('#orderBy').val();
	},
	
	/**
	 * Function to get Sort Order
	 */
	getSortOrder : function(){
			return jQuery("#sortOrder").val();
	},
	
	getCompleteParams : function(){
		var params = {};
		params['view'] = "Detail";
		params['module'] = this.parentModuleName;
		params['record'] = this.getParentId(),
		params['relatedModule'] = this.relatedModulename,
		params['sortorder'] =  this.getSortOrder(),
		params['orderby'] =  this.getOrderBy(),
		params['page'] = this.getCurrentPageNum();
		params['mode'] = "showRelatedList";
		params['tab_label'] = this.selectedRelatedTabElement.data('label-key');
        var detailInstance = Vtiger_Detail_Js.getInstance();
        var searchParams = JSON.stringify(detailInstance.getRelatedListSearchParams());
        params['search_params'] = searchParams;
        params['nolistcache'] = (jQuery('#noFilterCache').val() == 1) ? 1 : 0;
		return params;
	},
    
    loadRelatedList : function(params){
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		if(typeof this.relatedModulename== "undefined" || this.relatedModulename.length <= 0 ) {
			return;
		}
        
		var completeParams = this.getCompleteParams();
		jQuery.extend(completeParams,params);
        app.helper.showProgress();
        
		app.event.trigger('pre.relatedListLoad.click');
		
        app.request.get({data:completeParams}).then(
			function(error,responseData){
                app.helper.hideProgress();
				thisInstance.relatedTabsContainer.find('li').removeClass('active');
				thisInstance.selectedRelatedTabElement.addClass('active');
				container = jQuery('div.details');
                container.html(responseData);
    				thisInstance.initializePaginationEvents();
                thisInstance.triggerRelationAdditionalActions();
				app.event.trigger('post.relatedListLoad.click', container);
                
				aDeferred.resolve(responseData);
			},
			
			function(textStatus, errorThrown){
                app.helper.hideProgress();
				aDeferred.reject(textStatus, errorThrown);
			}
		);
		return aDeferred.promise();
	},
    
    getParentId : function(){
		return this.parentRecordId;
	},
        setParentId : function(parentId){
            this.parentRecordId = parentId;
        },
       
    /**
	 * Function to select related record for the module
	 */
    showSelectRelationPopup : function(){
        var popupParams = this.getPopupParams(); 
        var popupjs = new Vtiger_Popup_Js();
        popupjs.showPopup(popupParams,"post.RecordList.click");
	},
    
    /**
	 * Function to fetch popup params
	 */
    getPopupParams : function(){
		var parameters = {};
		var parameters = {
			'module' : this.relatedModulename,
			'src_module' : this.parentModuleName,
			'src_record' : this.parentRecordId,
			'multi_select' : true,
            'view' : 'Popup',
            'relationId' : this.getSelectedTabElement().data('relationId')
		};
		return parameters;
	},

	/**
	 * Function to add related record for the module
	 */
	addRelatedRecord : function(element , callback){
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var	referenceModuleName = this.relatedModulename;
		var parentId = this.getParentId();
		var parentModule = this.parentModuleName;
		var quickCreateParams = {};
		var relatedParams = {};
		var relatedField = element.data('name');
		var fullFormUrl = element.data('url');
		relatedParams[relatedField] = parentId;
		var eliminatedKeys = new Array('view', 'module', 'mode', 'action');

        app.event.one('post.QuickCreateForm.show',function(event,data){
            var index,queryParam,queryParamComponents;
			
			//To handle switch to task tab when click on add task from related list of activities
			//As this is leading to events tab intially even clicked on add task
            /*
             * Not required as we are now showing only one button for adding activities
			if(typeof fullFormUrl != 'undefined' && fullFormUrl.indexOf('?')!== -1) {
				var urlSplit = fullFormUrl.split('?');
				var queryString = urlSplit[1];
				var queryParameters = queryString.split('&');
				for(index=0; index<queryParameters.length; index++) {
					queryParam = queryParameters[index];
					queryParamComponents = queryParam.split('=');
					if(queryParamComponents[0] == 'mode' && queryParamComponents[1] == 'Calendar'){
						data.find('a[data-tab-name="Task"]').trigger('click');
                        data.find('[name="calendarModule"]').val('Calendar');
					}
				}
			}
            */
			jQuery('<input type="hidden" name="sourceModule" value="'+parentModule+'" />').appendTo(data);
			jQuery('<input type="hidden" name="sourceRecord" value="'+parentId+'" />').appendTo(data);
			jQuery('<input type="hidden" name="relationOperation" value="true" />').appendTo(data);
			
			if(typeof relatedField != "undefined"){
				var field = data.find('[name="'+relatedField+'"]');
				//If their is no element with the relatedField name,we are adding hidden element with
				//name as relatedField name,for saving of record with relation to parent record
				if(field.length == 0){
					jQuery('<input type="hidden" name="'+relatedField+'" value="'+parentId+'" />').appendTo(data);
				}
			}
			for(index=0; index<queryParameters.length; index++) {
				queryParam = queryParameters[index];
				queryParamComponents = queryParam.split('=');
				if(jQuery.inArray(queryParamComponents[0], eliminatedKeys) == '-1' && data.find('[name="'+queryParamComponents[0]+'"]').length == 0) {
					jQuery('<input type="hidden" name="'+queryParamComponents[0]+'" value="'+queryParamComponents[1]+'" />').appendTo(data);
				}
			}
            if(typeof callback !== 'undefined') {
                callback();
            }
        });
        
        app.event.one('post.QuickCreateForm.save',function(event,data){
            //After adding Event to related list, reverting related module name back to Calendar from Events 
            if(thisInstance.relatedModulename === 'Events'){
                thisInstance.relatedModulename = 'Calendar';
			}
            thisInstance.loadRelatedList().then(function(data){
                var selectedTabElement = thisInstance.selectedRelatedTabElement;
                if(thisInstance.relatedModulename == 'Calendar'){
                    var params = thisInstance.getPageJumpParams();
                    app.request.post(params).then(function(error, data){
                        var numberOfRecords = data.numberOfRecords;
                        // we should only show if there are any related records
                        var numberEle = selectedTabElement.find('.numberCircle');
                        numberEle.text(numberOfRecords);
                        if(numberOfRecords > 0) {
                            numberEle.removeClass('hide');
                        }else{
                            numberEle.addClass('hide');
                        }
                    });
                } else {
                    thisInstance.updateRelatedRecordsCount(selectedTabElement.data('relation-id'),[1],true);
                }
                aDeferred.resolve(data);
            });
        });
		
		//If url contains params then seperate them and make them as relatedParams
		if(typeof fullFormUrl != 'undefined' && fullFormUrl.indexOf('?')!== -1) {
			var urlSplit = fullFormUrl.split('?');
			var queryString = urlSplit[1];
			var queryParameters = queryString.split('&');
			for(var index=0; index<queryParameters.length; index++) {
				var queryParam = queryParameters[index];
				var queryParamComponents = queryParam.split('=');
				if(jQuery.inArray(queryParamComponents[0], eliminatedKeys) == '-1') {
					relatedParams[queryParamComponents[0]] = queryParamComponents[1];
				}
			}
		}
		
		quickCreateParams['data'] = relatedParams;
		quickCreateParams['noCache'] = true;
		var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="'+ referenceModuleName +'"]');
		if(quickCreateNode.length <= 0) {
			Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED'))
		}
		quickCreateNode.trigger('click',quickCreateParams);
		return aDeferred.promise();
	},
    
    deleteRelation : function(relatedIdList, customParams) {
		var aDeferred = jQuery.Deferred();
        var thisInstance = this;
		var params = {};
		params['mode'] = "deleteRelation";
		params['module'] = this.parentModuleName;
		params['action'] = 'RelationAjax';

        var selectedTabElement = this.getSelectedTabElement();
        var relationId = selectedTabElement.data('relationId');
		params['related_module'] = this.relatedModulename;
        params['relationId'] = relationId;
        if(this.relatedModulename == 'Emails' && this.parentId != false) {
            params['src_record'] = this.parentId;
        } else {
            params['src_record'] = this.parentRecordId;
        }
		params['related_record_list'] = JSON.stringify(relatedIdList);
		
		if(typeof customParams != 'undefined') {
			params = jQuery.extend(params,customParams);
		}
		app.request.post({"data":params}).then(
			function(err,responseData){
                thisInstance.updateRelatedRecordsCount(relationId,relatedIdList,false);
				aDeferred.resolve(responseData);
			},

			function(textStatus, errorThrown){
				aDeferred.reject(textStatus, errorThrown);
			}
		);
		return aDeferred.promise();
	},
    
    addRelations : function(idList, searchParms){
        var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var sourceRecordId = this.parentRecordId;
		var sourceModuleName = this.parentModuleName;
		var relatedModuleName = this.relatedModulename;
        var selectedTabElement = this.getSelectedTabElement();
        if(selectedTabElement.length > 0){
            var relationId = selectedTabElement.data('relationId');
        }

		var params = {};
		params['mode'] = "addRelation";
		params['module'] = sourceModuleName;
		params['action'] = 'RelationAjax';
		params['relationId'] = relationId;
		params['related_module'] = relatedModuleName;
		params['src_record'] = sourceRecordId;
		params['related_record_list'] = JSON.stringify(idList);

        app.helper.showProgress();
        
		app.request.post({"data":params}).then(
			function(responseData){
                thisInstance.updateRelatedRecordsCount(relationId,idList,true);
                app.helper.hideProgress();
				aDeferred.resolve(responseData);
			},

			function(textStatus, errorThrown){
                app.helper.hideProgress();
				aDeferred.reject(textStatus, errorThrown);
			}
		);
		return aDeferred.promise();
	},
    
    addRelationsAnother : function(idList, searchParms, excludedIds){
        var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var sourceRecordId = this.parentRecordId;
		var sourceModuleName = this.parentModuleName;
		var relatedModuleName = this.relatedModulename;
        var selectedTabElement = this.getSelectedTabElement();
        if(selectedTabElement.length > 0){
            var relationId = selectedTabElement.data('relationId');
        }

		var params = {};
		params['mode'] = "addRelation";
		params['module'] = sourceModuleName;
		params['action'] = 'RelationAjax';
		params['relationId'] = relationId;
		params['related_module'] = relatedModuleName;
		params['src_record'] = sourceRecordId;
		params['related_record_list'] = idList;
        params['search_params'] = searchParms;
        params['excluded_ids'] = excludedIds;
        app.helper.showProgress();
        
		app.request.post({"data":params}).then(
			function(responseData){
                thisInstance.updateRelatedRecordsCount(relationId,idList,true);
                app.helper.hideProgress();
				aDeferred.resolve(responseData);
			},

			function(textStatus, errorThrown){
                app.helper.hideProgress();
				aDeferred.reject(textStatus, errorThrown);
			}
		);
		return aDeferred.promise();
	},
    
    
    triggerRelationAdditionalActions : function() {
	},
	
	registerScrollForRollupComments : function() {
        jQuery(document).scroll(function() {
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 30
                && jQuery('div.commentContainer').length > 0 
                && jQuery('.widgetContainer_comments').length === 0
                && jQuery('#rollupcomments').attr('rollup-status') > 0) {
				
                if(Vtiger_RelatedList_Js.loaded && jQuery('#rollupcomments').attr('hascomments') == 1) {
                    Vtiger_RelatedList_Js.loaded = false;
					app.helper.showProgress();
                    var currentTarget = jQuery('#rollupcomments');
                    var moduleName = currentTarget.attr('module');
                    var recordId = currentTarget.attr('record');
                    var rollupId = currentTarget.attr('rollupid');
                    var rollupstatus = currentTarget.attr('rollup-status');
                    var startindex = parseInt(currentTarget.attr('startindex'));

                    var url = 'index.php?module=Vtiger&view=ModCommentsDetailAjax&parent='+
                      moduleName+'&parentId='+recordId+'&rollupid='+rollupId+'&rollup_status='+rollupstatus
                      +'&startindex='+startindex+'&mode=getNextGroupOfRollupComments';

                    var params = {
						'type' : 'GET',
						'url' : url
					};
					
                    app.request.get(params).then(function(err, data){
						Vtiger_RelatedList_Js.loaded = true;
						app.helper.hideProgress();
						if(data) {
							jQuery('#rollupcomments').attr('startindex', startindex + 10);
							jQuery('.commentsBody ul.unstyled:first').append(jQuery(data).children());
						}else {
							jQuery('#rollupcomments').attr('hascomments', '0');
						}
                    });
                }
            }
        });
    },
    
    getPageJumpParams: function() {
        var thisInstance = this;
        var params = {
			'type' : 'POST',
			'data' : {
				'action' : "RelationAjax",
				'module' : thisInstance.parentModuleName,
				'record' : thisInstance.getParentId(),
				'relatedModule' : thisInstance.relatedModulename,
				'tab_label' : thisInstance.selectedRelatedTabElement.data('label-key'),
				'mode' : "getRelatedListPageCount"
			}
		};
        
        return params;
    },
	
	pageJump : function(){
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
        var params = this.getPageJumpParams();
		
		var element = jQuery('#totalPageCount');
		var totalCountElem = jQuery('.relatedContainer').find('#totalCount');
		var totalPageNumber = element.text();
		
		if(totalPageNumber === ""){
			app.request.post(params).then(
				function(err, data) {
					var response;
					if(typeof data !== "object"){
						response = JSON.parse(data);
					} else{
						response = data;
					}
					
					var pageCount = data.page;
					var numberOfRecords = data.numberOfRecords;
					totalCountElem.val(numberOfRecords);
					element.text(pageCount);
					aDeferred.resolve(response);
				}
			);
		}else{
			aDeferred.resolve();
		}
		return aDeferred.promise();
	},
	
	totalNumOfRecords : function (curEle) {
		var thisInstance = this;
		var element = jQuery('.relatedContainer').find('#totalCount');
		var totalPageNumber = element.text();
		var pageCount;
		if(curEle.attr('id') !== 'relatedViewPageJump') curEle.addClass('hide');

		if(totalPageNumber === ""){
			var totalCountElem = jQuery('.relatedContainer').find('#totalCount');
			var totalRecordCount = totalCountElem.val();

			if(totalRecordCount !== '') {
				var recordPerPage = jQuery('#pageLimit').val();
				if(recordPerPage === '0') recordPerPage = 1;
				pageCount = Math.ceil(totalRecordCount/recordPerPage);
				if(pageCount === 0){
					pageCount = 1;
				}
				element.text(pageCount);
				if(curEle.attr('id') !== 'PageJump') {
					thisInstance.showPagingInfo();
				}
				return;
			}

			thisInstance.pageJump().then(function(data){
				var pageCount = data.page;
				var numOfrecords = data.numberOfRecords;
				if(numOfrecords === 0) {
					numOfrecords = 1;
				}
				if(pageCount === 0){
					pageCount = 1;
				}
				element.text(pageCount);
				totalCountElem.val();
				if(curEle.attr('id') !== 'PageJump') {
					thisInstance.showPagingInfo();
				}
			});
		}
	},
	
	showPagingInfo : function(){
		var totalNumberOfRecords = jQuery('.relatedContainer').find('#totalCount').val();
		var pageNumberElement = jQuery('.pageNumbersText');
		var pageRange = pageNumberElement.text();
		var newPagingInfo = pageRange.trim()+" "+app.vtranslate('of')+" "+totalNumberOfRecords+"  ";
		var listViewEntriesCount = parseInt(jQuery('#noOfEntries').val());
		
		if(listViewEntriesCount !== 0){
			jQuery('.pageNumbersText').html(newPagingInfo);
		} else {
			jQuery('.pageNumbersText').html("");
		}
	},
	
	pageJumpOnSubmit : function(element) {
		var thisInstance = this;
		
		var currentPageElement = jQuery('.relatedContainer').find('#pageNumber');
		var currentPageNumber = parseInt(currentPageElement.val());
		var newPageNumber = parseInt(jQuery('#pageToJump').val());
		var totalPages = parseInt(jQuery('.relatedContainer').find('#totalPageCount').text());

		if(newPageNumber > totalPages){
			var message = app.vtranslate('JS_PAGE_NOT_EXIST');
			app.helper.showErrorNotification({'message':message})
			return;
		}

		if(newPageNumber === currentPageNumber){
			var message = app.vtranslate('JS_YOU_ARE_IN_PAGE_NUMBER')+" "+newPageNumber;
			app.helper.showAlertNotification({'message': message});
			return;
		}

		var urlParams = {
			"page" : newPageNumber
		};

		thisInstance.loadRelatedList(urlParams).then(
			function(data){
				element.closest('.btn-group ').removeClass('open');
			});
		return false;
		
	},
    
	initializePaginationEvents : function() {
		var thisInstance = this;
		var paginationObj = new Vtiger_Pagination_Js;
        var relatedViewContainer = jQuery('.relatedContainer');
		paginationObj.initialize(relatedViewContainer);
		
		app.event.on(paginationObj.nextPageButtonClickEventName, function(){
			var pageLimit = relatedViewContainer.find('#pageLimit').val();
            var noOfEntries = relatedViewContainer.find('#noOfEntries').val();
            var nextPageExist = relatedViewContainer.find('#nextPageExist').val();
			var pageNumber = relatedViewContainer.find('#pageNumber').val();
			var nextPageNumber = parseInt(parseFloat(pageNumber)) + 1;
			
            if(noOfEntries === pageLimit && nextPageExist){
				var urlParams = {};
                thisInstance.setCurrentPageNumber(nextPageNumber);
				relatedViewContainer.find("#pageNumber").val(nextPageNumber);
				thisInstance.loadRelatedList(urlParams);
			}
		});
		
		app.event.on(paginationObj.previousPageButtonClickEventName, function(){
			var pageNumber = relatedViewContainer.find('#pageNumber').val();
			var previousPageNumber = parseInt(parseFloat(pageNumber)) - 1;
			
			if(pageNumber > 1) {
				var urlParams = {};
                thisInstance.setCurrentPageNumber(previousPageNumber);
				relatedViewContainer.find('#pageNumber').val(previousPageNumber);
				thisInstance.loadRelatedList(urlParams);
			}
		});
		
		app.event.on(paginationObj.pageJumpButtonClickEventName, function(event, currentEle){
			thisInstance.pageJump();
		});
		
		app.event.on(paginationObj.totalNumOfRecordsButtonClickEventName, function(event, currentEle){
			thisInstance.totalNumOfRecords(currentEle);
		});
		
		app.event.on(paginationObj.pageJumpSubmitButtonClickEvent, function(event, currentEle){
			thisInstance.pageJumpOnSubmit(currentEle);
		});
        this.registerEvents();
	},
    
    registerEditLink : function() {
		var relatedContainer =  jQuery('.relatedContainer');;
		relatedContainer.on('click', 'a.relationEdit', function(e) {
			var element = jQuery(e.currentTarget);
			var url = element.attr('href');
			var detailInstance = Vtiger_Detail_Js.getInstance();
			var postData = detailInstance.getDefaultParams();
			for(var key in postData) {
				if(postData[key]) {
                    if(key == 'relatedModule') {
                        postData['returnrelatedModuleName'] = postData[key];
                    } else {
                        postData['return'+key] = postData[key];
                    }
					delete postData[key];
				} else {
					delete postData[key];
				}
			}
			e.preventDefault();
			e.stopPropagation();
			window.location.href = url +'&'+ $.param(postData);
		});
	},
        
    selectedRecordIds: false,
    excludedRecordIds: false,
    triggerSendEmail: function () {
        var detailInstance = Vtiger_Detail_Js.getInstance();
        var searchParams = JSON.stringify(detailInstance.getRelatedListSearchParams());
        var selectedIds = jQuery('#selectedIds').data('selected-ids');
        var excludedIds = jQuery('#excludedIds').data('excluded-ids');
        if (selectedIds == "") {
            app.helper.showAlertBox({message: app.vtranslate('JS_PLEASE_SELECT_ONE_RECORD')});
            return false;
        }
        var params = {
            'search_params': searchParams,
            'nolistcache': (jQuery('#noFilterCache').val() == 1) ? 1 : 0,
            'related_record_list': selectedIds,
            'excluded_ids': excludedIds,
            'sourceModule': app.getModuleName(),
            'src_record': jQuery('#recordId').val(),
            'related_module': this.relatedModulename,
            'mode': "deleteRelationEnhanced",
            'module': this.parentModuleName,
            'action': 'RelationAjax'
        };
        app.helper.showProgress();
        var selectedTabElement = this.getSelectedTabElement();
        var relationId = selectedTabElement.data('relationId');
        let self = this;
        app.request.post({"data": params}).then(function (error, responseData) {
            app.helper.hideProgress();
            self.loadRelatedList().then(function () {
				self.updateRelatedRecordsCount();
            });
        });
    },
    changeCustomFilterElementView: function () {
        var self = this;
        var filterSelectElement = self.relatedContentContainer.find('#recordsFilter');
        if (filterSelectElement.length > 0) {
            vtUtils.showSelect2ElementView(filterSelectElement);
        }
    },

    registerChangeCustomFilterEvent: function () {
        var self = this;
        var filterSelectElement = this.relatedContentContainer.find('#recordsFilter');
        filterSelectElement.change(function (e) {
            var element = jQuery(e.currentTarget);
            if (jQuery('.bootbox-confirm .in').length == 0) {
                var message = app.vtranslate('JS_APPENDED_TO_EXISTING_LIST', self.relatedModulename) + '<br><br>' + app.vtranslate('JS_WISH_TO_PROCEED');
                app.helper.showConfirmationBox({'message': message}).then(function (e) {
                    var cvId = element.find('option:selected').data('id');
                    var params = {
                        'sourceRecord': self.parentRecordId,
                        'relatedModule': self.relatedModulename,
                        'viewId': cvId,
                        'module': app.getModuleName(),
                        'action': "RelationAjax",
                        'mode': 'addRelationsFromRelatedModuleViewId'
                    };
                    app.helper.showProgress();
                    app.request.post({"data": params}).then(
                            function (responseData) {
                                app.helper.hideProgress();
                                if (responseData != null) {
                                    app.helper.showErrorNotification({"message": app.vtranslate('JS_NO_RECORDS_RELATED_TO_THIS_FILTER')});
                                } else {
                                    self.loadRelatedList().then(function () {
                                        self.triggerRelationAdditionalActions();
                                    });
                                }
                            },
                            function (textStatus, errorThrown) {}
                    );
                });
            }
        });
    },

    registerEventToEditRelatedStatus: function () {
        var self = this;
        var statusElement = self.relatedContentContainer.find('.currentStatus');
        statusElement.on('click', function (e) {
            e.stopImmediatePropagation();
            var element = jQuery(e.currentTarget);
            element.addClass('open');
        });
        var statusDropdown = statusElement.find('.dropdown-menu');
        statusDropdown.on('click', 'a', function (e) {
            e.stopImmediatePropagation();
            var element = jQuery(e.currentTarget);
            var liContainer = element.closest('li');
            var currentStatus = element.closest('.currentStatus');
            var selectedStatusId = liContainer.attr('id');
            var selectedStatusValue = liContainer.data('status');
            var relatedRecordId = element.closest('tr').data('id');
            var params = {
                'relatedModule': self.relatedModulename,
                'relatedRecord': relatedRecordId,
                'status': selectedStatusId,
                'module': app.getModuleName(),
                'action': 'RelationAjax',
                'sourceRecord': self.parentRecordId,
                'mode': 'updateStatus'
            }
            app.helper.showProgress();
            app.request.post({"data": params}).then(function (error, responseData) {
                if (responseData) {
                    app.helper.hideProgress();
                    currentStatus.find('.statusValue').text(selectedStatusValue);
                    currentStatus.removeClass('open');
                }
            },
                    function (textStatus, errorThrown) {}
            );
        });
    },

    writeSelectedIds: function (selectedIds) {
        var self = this;
        var element = self.relatedContentContainer.find('#selectedIds');
        element.data('selected-ids', selectedIds);
        self.selectedRecordIds = selectedIds;
    },

    writeExcludedIds: function (excludedIds) {
        var self = this;
        var element = self.relatedContentContainer.find('#excludedIds');
        element.data('excluded-ids', excludedIds);
        self.excludedRecordIds = excludedIds;
    },

    readSelectedIds: function (decode) {
        var self = this;
        var element = self.relatedContentContainer.find('#selectedIds');
        var selectedIds = element.data('selected-ids');
        if (selectedIds == "") {
            selectedIds = new Array();
            self.writeSelectedIds(selectedIds);
        }
        if (decode && typeof selectedIds == "object") {
            selectedIds = JSON.stringify(selectedIds);
        }
        return selectedIds;
    },

    reladExcludedIds: function (decode) {
        var self = this;
        var element = self.relatedContentContainer.find('#excludedIds');
        var excludedIds = element.data('excluded-ids');
        if (excludedIds == "") {
            excludedIds = new Array();
            self.writeExcludedIds(excludedIds);
        }
        if (decode && typeof excludedIds == "object") {
            excludedIds = JSON.stringify(excludedIds);
        }
        return excludedIds;
    },
    registerPostSelectionRelatedListActions: function () {
        var selectedIds = this.readSelectedIds(false);
        var sendEmailButton = this.relatedContentContainer.find('.relatedHeader').find('.unlinkbutton');
        if (selectedIds.length > 0) {
            sendEmailButton.removeAttr('disabled');
        } else if (selectedIds.length == 0) {
            sendEmailButton.attr('disabled', "disabled");
        }
    },

    markSelectedIdsCheckboxes: function (params) {
        var self = this;
        var selectedIds = params.selected;
        var excludedIds = params.excluded;
        var relatedListContainer = self.relatedContentContainer;
        relatedListContainer.find('#selectedIds').data('selected-ids', selectedIds);
        relatedListContainer.find('#excludedIds').data('excluded-ids', excludedIds);
        if ((selectedIds == '' && excludedIds == '')) {
            return;
        }
        relatedListContainer.find('.listViewEntriesCheckBox').each(function (i, ele) {
            var currentRow = jQuery(ele).closest('tr');
            var recordId = currentRow.data('id').toString();
            if (jQuery.inArray(recordId, excludedIds) == '-1' && (jQuery.inArray(recordId, selectedIds) != '-1' || selectedIds == 'all')) {
                jQuery(ele).prop('checked', true);
                currentRow.addClass('listviewhovercolor');
            }
        });
        self.selectMainCheck();
    },

    selectMainCheck: function () {
        var self = this;
        var relatedListContainer = self.relatedContentContainer;
        var mainCheckBox = relatedListContainer.find('#listViewEntriesMainCheckBox');
        if (relatedListContainer.find('.listViewEntriesCheckBox').not(":checked").length == 0) {
            mainCheckBox.prop("checked", true);
        } else {
            mainCheckBox.prop("checked", false);
        }
    },

    registerCheckboxClickEvent: function () {
        var self = this;
        self.relatedContentContainer.off('click', '.listViewEntriesCheckBox').on('click', '.listViewEntriesCheckBox', function (e) {
            var element = jQuery(e.currentTarget);
            var recordId = element.val();
            var selectedIds = self.readSelectedIds(false);
            var excludedIds = self.reladExcludedIds(false);
            if (element.is(":checked")) {
                if (selectedIds != "all") {
                    selectedIds.push(recordId);
                } else {
                    excludedIds.splice($.inArray(recordId, excludedIds), 1);
                }
                element.closest('tr').addClass('listviewhovercolor');
                self.registerPostSelectionRelatedListActions();
            } else {
                if (selectedIds != "all") {
                    selectedIds.splice($.inArray(recordId, selectedIds), 1);
                } else {
                    excludedIds.push(recordId);
                }
                element.closest('tr').removeClass('listviewhovercolor');
                self.registerPostSelectionRelatedListActions();
            }
            self.writeSelectedIds(selectedIds);
            self.writeExcludedIds(excludedIds);
            self.selectMainCheck();
        });
    },

    registerMainCheckboxClickEvent: function () {
        var self = this;
        self.relatedContentContainer.off('click', '#listViewEntriesMainCheckBox').on('click', '#listViewEntriesMainCheckBox', function (e) {
            var element = jQuery(e.currentTarget);
            if (element.is(":checked")) {
                var selectedIds = self.readSelectedIds(false);
                var excludedIds = self.reladExcludedIds(false);
                self.relatedContentContainer.find('.listViewEntriesCheckBox').each(function (i, ele) {
                    var recordId = jQuery(ele).val();
                    if (selectedIds != "all") {
                        selectedIds.push(recordId);
                    } else {
                        excludedIds.splice($.inArray(recordId, excludedIds), 1);
                    }
                    jQuery(ele).prop('checked', true).closest('tr').addClass('listviewhovercolor');
                    ;
                });
                self.writeSelectedIds(selectedIds);
                self.writeExcludedIds(excludedIds);
                self.getRecordsCount().then(function (count) {
                    self.relatedContentContainer.find('#totalRecordsCount').text(count);
                    self.relatedContentContainer.find('#selectAllMsgDiv').closest('div.messageContainer').removeClass('hide');
                });
                self.registerPostSelectionRelatedListActions();
            } else {
                var selectedIds = self.readSelectedIds(false);
                var excludedIds = self.reladExcludedIds(false);
                self.relatedContentContainer.find('.listViewEntriesCheckBox').each(function (i, ele) {
                    var recordId = jQuery(ele).val();
                    if (selectedIds != "all") {
                        selectedIds.splice($.inArray(recordId, selectedIds), 1);
                    } else {
                        excludedIds.push(recordId);
                    }
                    jQuery(ele).prop('checked', false).closest('tr').removeClass('listviewhovercolor');
                    ;
                });
                self.writeSelectedIds(selectedIds);
                self.writeExcludedIds(excludedIds);
                self.relatedContentContainer.find('#selectAllMsgDiv').closest('div.messageContainer').addClass('hide');
                self.registerPostSelectionRelatedListActions();
            }
        });
    },

    getRecordsCount: function () {
        var aDeferred = jQuery.Deferred();
        var self = this;
        var recordCountEle = self.relatedContentContainer.find('#recordsCount');
        var recordsCount = recordCountEle.val();
        if (recordsCount != "") {
            aDeferred.resolve(recordsCount);
        } else {
            let detailInstance = Vtiger_Detail_Js.getInstance();
            let searchParams = JSON.stringify(detailInstance.getRelatedListSearchParams());
            var params = {
                'module': app.getModuleName(),
                'action': 'DetailAjax',
                'mode': 'getRecordsCount',
                'relatedModule': self.relatedModulename,
                'record': self.parentRecordId,
                'search_params' : searchParams,
                'tab_label': self.relatedContentContainer.find('#tab_label').val(),
            };
            app.helper.showProgress();
            app.request.post({"data": params}).then(function (error, responseData) {
                app.helper.hideProgress();
                var count = responseData.count;
                recordCountEle.val(count);
                aDeferred.resolve(count);
            });
        }
        return aDeferred.promise();
    },

    registerSelectAllClickEvent: function () {
        var self = this;
        var selectAllContainer = self.relatedContentContainer.find('#selectAllMsgDiv');
        selectAllContainer.click(function () {
            self.relatedContentContainer.find('.listViewEntriesCheckBox').each(function (i, ele) {
                jQuery(ele).attr('checked', true);
            });
            self.relatedContentContainer.find('#listViewEntriesMainCheckBox').attr('checked', true);
            self.writeSelectedIds("all");
            selectAllContainer.closest('div.messageContainer').addClass('hide');
            self.relatedContentContainer.find('#deSelectAllMsgDiv').closest('div.messageContainer').removeClass('hide');
        });
    },

    registerDeselectAllClickEvent: function () {
        var self = this;
        var deselectAllContainer = self.relatedContentContainer.find('#deSelectAllMsgDiv');
        deselectAllContainer.click(function () {
            self.relatedContentContainer.find('.listViewEntriesCheckBox').each(function (i, ele) {
                jQuery(ele).attr('checked', false);
            });
            self.relatedContentContainer.find('#listViewEntriesMainCheckBox').attr('checked', false);
            self.writeSelectedIds('');
            self.writeExcludedIds('');
            deselectAllContainer.closest('div.messageContainer').addClass('hide');
        });
    },

    postLoadRelatedListViewRecords: function () {
        var thisInstance = this;
        app.event.off('Vtiger.RelatedList.PostLoad.Event');
        app.event.on('Vtiger.RelatedList.PostLoad.Event', function (e) {
            var listParams = {
                "selected": thisInstance.selectedRecordIds,
                "excluded": thisInstance.excludedRecordIds
            };
            thisInstance.markSelectedIdsCheckboxes(listParams);
        });
    },

    registerEvents: function () {
        let self = this;
       jQuery('.unlinkbutton').off('click').on('click',function(e) {
            self.triggerSendEmail();
        });
        this.changeCustomFilterElementView();
        this.registerEventToEditRelatedStatus();
        this.registerChangeCustomFilterEvent();
        this.registerCheckboxClickEvent();
        this.registerMainCheckboxClickEvent();
        this.registerSelectAllClickEvent();
        this.registerDeselectAllClickEvent();
        this.postLoadRelatedListViewRecords();
        if (typeof jQuery.fn.sadropdown === 'function') {
            jQuery('.currentStatus').find('.dropdown-toggle').sadropdown({
                relativeTo: '.listview-table'
            });
        }
    },
        
	init : function(parentId, parentModule, selectedRelatedTabElement, relatedModuleName) {
		this.selectedRelatedTabElement = selectedRelatedTabElement;
		this.parentRecordId = parentId;
		this.parentModuleName = parentModule;
		this.relatedModulename = relatedModuleName;
		this.relatedTabsContainer = jQuery(selectedRelatedTabElement).closest('div.related-tabs');
		this.detailViewContainer = this.relatedTabsContainer.closest('div.detailViewContainer');
		this.relatedContentContainer = jQuery('div.details', this.detailViewContainer);

		this.registerEditLink();
        this.registerEvents();
    }
    
})

jQuery(document).ready(function(){
	var recordId = app.getRecordId();
	var moduleName = app.getModuleName();
        var detailViewInstance = Vtiger_Detail_Js.getInstance();
        var selectedTabElement = detailViewInstance.getSelectedTab();
        var relatedModuleName = detailViewInstance.getRelatedModuleName();
            var instance = Vtiger_RelatedList_Js.getInstance(recordId, moduleName, selectedTabElement, relatedModuleName);
	
	instance.initializePaginationEvents();
});