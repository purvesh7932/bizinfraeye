/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

$( document ).ready(function() {
	jQuery('.app-menu').removeClass('hide');
	var toggleAppMenu = function(type) {
		var appMenu = jQuery('.app-menu');
		var appNav = jQuery('.app-nav');
		appMenu.appendTo('#page');
		appMenu.css({
			'top' : appNav.offset().top + appNav.height(),
			'left' : 0
		});
		if(typeof type === 'undefined') {
			type = appMenu.is(':hidden') ? 'show' : 'hide';
		}
		if(type == 'show') {
			appMenu.show(200, function() {});
		} else {
			appMenu.hide(200, function() {});
		}
	};

	jQuery('.app-trigger, .app-icon, .app-navigator').on('click',function(e){
		e.stopPropagation();
		toggleAppMenu();
	});

	jQuery('html').on('click', function() {
		toggleAppMenu('hide');
	});

	jQuery(document).keyup(function (e) {
		if (e.keyCode == 27) {
			if(!jQuery('.app-menu').is(':hidden')) {
				toggleAppMenu('hide');
			}
		}
	});

	jQuery('.app-modules-dropdown-container').hover(function(e) {
		var dropdownContainer = jQuery(e.currentTarget);
		jQuery('.dropdown').removeClass('open');
		if(dropdownContainer.length) {
			if(dropdownContainer.hasClass('dropdown-compact')) {
				dropdownContainer.find('.app-modules-dropdown').css('top', dropdownContainer.position().top - 8);
			} else {
				dropdownContainer.find('.app-modules-dropdown').css('top', '');
			}
			dropdownContainer.addClass('open').find('.app-item').addClass('active-app-item');
		}
	}, function(e) {
		var dropdownContainer = jQuery(e.currentTarget);
		dropdownContainer.find('.app-item').removeClass('active-app-item');
		setTimeout(function() {
			if(dropdownContainer.find('.app-modules-dropdown').length && !dropdownContainer.find('.app-modules-dropdown').is(':hover') && !dropdownContainer.is(':hover')) {
				dropdownContainer.removeClass('open');
			}
		}, 500);

	});

	jQuery('.app-item').on('click', function() {
		var url = jQuery(this).data('defaultUrl');
		if(url) {
			window.location.href = url;
		}
	});

	jQuery(window).resize(function() {
		jQuery(".app-modules-dropdown").mCustomScrollbar("destroy");
		app.helper.showVerticalScroll(jQuery(".app-modules-dropdown").not('.dropdown-modules-compact'), {
			setHeight: $(window).height(),
			autoExpandScrollbar: true
		});
		jQuery('.dropdown-modules-compact').each(function() {
			var element = jQuery(this);
			var heightPer = parseFloat(element.data('height'));
			app.helper.showVerticalScroll(element, {
				setHeight: $(window).height()*heightPer - 3,
				autoExpandScrollbar: true,
				scrollbarPosition: 'outside'
			});
		});
	});
	app.helper.showVerticalScroll(jQuery(".app-modules-dropdown").not('.dropdown-modules-compact'), {
		setHeight: $(window).height(),
		autoExpandScrollbar: true,
		scrollbarPosition: 'outside'
	});
	jQuery('.dropdown-modules-compact').each(function() {
		var element = jQuery(this);
		var heightPer = parseFloat(element.data('height'));
		app.helper.showVerticalScroll(element, {
			setHeight: $(window).height()*heightPer - 3,
			autoExpandScrollbar: true,
			scrollbarPosition: 'outside'
		});
	});
	
	jQuery('.nextButton').on('click', function() {
		var sourceModule = jQuery('#addModules').val();
		var language = jQuery('#language').val();
		if(sourceModule == ''){
			var err = app.vtranslate('JS_PLEASE_SELECT_ANY_MODULE');
			app.helper.showErrorNotification({"message": err});
			return false;
		}
		if(language == ''){
			var err = app.vtranslate('JS_PLEASE_SELECT_ANY_LANGUAGE');
			app.helper.showErrorNotification({"message": err});
			return false;
		}
		var url = 'index.php?module=LabelManager&parent=Settings&view=LabelManager&mode=languageManage&sourceModule='+sourceModule+'&language='+language;
		window.location.href = url;
	});
	
	jQuery('.saveButton').on('click', function() {
		var sourceModule = jQuery('#sourceModule').val();
		var language = jQuery('#language').val();
		var serializeData = $('#EditView').serialize();
		
		var params = { 
			'module' : 'LabelManager',
			'parent' : 'Settings',
			'action' : 'SaveLanguage',
			'mode' : 'saveLanguageLabel',
			'serializeData' : serializeData,
			'sourceModule' : sourceModule,
			'language' : language
		};
		app.helper.showProgress();

		app.request.post({'data' : params}).then(
		function(err, data) {
			app.helper.hideProgress();
			var url = 'index.php?module=LabelManager&parent=Settings&view=LabelManager&mode=languageSettings';
			window.location.href = url;
		});
	});
});