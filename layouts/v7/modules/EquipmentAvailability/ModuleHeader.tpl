{strip}
	<div class="col-sm-11 col-xs-10 padding0 module-action-bar clearfix coloredBorderTop">
		<div class="module-action-content clearfix {$MODULE}-module-action-content">
			<div class="col-lg-7 col-md-6 col-sm-5 col-xs-11 padding0 module-breadcrumb module-breadcrumb-{$smarty.request.view} transitionsAllHalfSecond">
				{assign var=MODULE_MODEL value=Vtiger_Module_Model::getInstance($MODULE)}
				{if $MODULE_MODEL->getDefaultViewName() neq 'List'}
					{assign var=DEFAULT_FILTER_URL value=$MODULE_MODEL->getDefaultUrl()}
				{else}
					{assign var=DEFAULT_FILTER_ID value=$MODULE_MODEL->getDefaultCustomFilter()}
					{if $DEFAULT_FILTER_ID}
						{assign var=CVURL value="&viewname="|cat:$DEFAULT_FILTER_ID}
						{assign var=DEFAULT_FILTER_URL value=$MODULE_MODEL->getListViewUrl()|cat:$CVURL}
					{else}
						{assign var=DEFAULT_FILTER_URL value=$MODULE_MODEL->getListViewUrlWithAllFilter()}
					{/if}
				{/if}
				<a title="{vtranslate($MODULE, $MODULE)}" href='{$DEFAULT_FILTER_URL}&app={$SELECTED_MENU_CATEGORY}'><h4 class="module-title pull-left"> {vtranslate($MODULE, $MODULE)} </h4>&nbsp;&nbsp;</a>
                                {if $smarty.session.lvs.$MODULE.viewname}
					{assign var=VIEWID value=$smarty.session.lvs.$MODULE.viewname}
				{/if}
				{if $VIEWID}
					{foreach item=FILTER_TYPES from=$CUSTOM_VIEWS}
						{foreach item=FILTERS from=$FILTER_TYPES}
							{if $FILTERS->get('cvid') eq $VIEWID}
								{assign var=CVNAME value=$FILTERS->get('viewname')}
								{break}
							{/if}
						{/foreach}
					{/foreach}
					<p class="current-filter-name filter-name pull-left cursorPointer" title="{$CVNAME}"><span class="fa fa-angle-right pull-left" aria-hidden="true"></span><a href='{$MODULE_MODEL->getListViewUrl()}&viewname={$VIEWID}&app={$SELECTED_MENU_CATEGORY}'>&nbsp;&nbsp;{$CVNAME}&nbsp;&nbsp;</a> </p>
				{/if}
				{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
				{if $RECORD and $smarty.request.view eq 'Edit'}
					<p class="current-filter-name filter-name pull-left "><span class="fa fa-angle-right pull-left" aria-hidden="true"></span><a title="{$RECORD->get('label')}">&nbsp;&nbsp;{vtranslate('LBL_EDITING', $MODULE)} : {$RECORD->get('label')} &nbsp;&nbsp;</a></p>
				{else if $smarty.request.view eq 'Edit'}
					<p class="current-filter-name filter-name pull-left "><span class="fa fa-angle-right pull-left" aria-hidden="true"></span><a>&nbsp;&nbsp;{vtranslate('LBL_ADDING_NEW', $MODULE)}&nbsp;&nbsp;</a></p>
				{/if}
				{if $smarty.request.view eq 'Detail'}
					<p class="current-filter-name filter-name pull-left"><span class="fa fa-angle-right pull-left" aria-hidden="true"></span><a title="{$RECORD->get('label')}">&nbsp;&nbsp;{$RECORD->get('label')} &nbsp;&nbsp;</a></p>
				{/if}
			</div>
			<div class="col-lg-5 col-md-6 col-sm-7 col-xs-1 padding0 pull-right">
				<div id="appnav" class="navbar-right">
					<nav class="navbar navbar-inverse border0 margin0">
						<div class="container-fluid">
							<div class="navbar-header marginTop5px">
								<button type="button" class="navbar-toggle collapsed margin0" data-toggle="collapse" data-target="#appnavcontent" aria-expanded="false">
									<i class="fa fa-ellipsis-v"></i>
								</button>
							</div>

							<div class="navbar-collapse collapse" id="appnavcontent" aria-expanded="false" style="height: 1px;">
								<ul class="nav navbar-nav">
									{foreach item=BASIC_ACTION from=$MODULE_BASIC_ACTIONS}
										{if $BASIC_ACTION->getLabel() == 'LBL_IMPORT'}
											<li>
												<button id="{$MODULE}_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($BASIC_ACTION->getLabel())}" type="button" class="btn addButton btn-soft-info module-btn"
														{if stripos($BASIC_ACTION->getUrl(), 'javascript:')===0}
													onclick='{$BASIC_ACTION->getUrl()|substr:strlen("javascript:")};'
														{else}
													onclick="Vtiger_Import_Js.triggerImportAction('{$BASIC_ACTION->getUrl()}')"
														{/if}>
													<div class="fa {$BASIC_ACTION->getIcon()}" aria-hidden="true"></div>&nbsp;&nbsp;
													{vtranslate($BASIC_ACTION->getLabel(), $MODULE)}
												</button>
											</li>
										{else}
											<li>
												<button id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($BASIC_ACTION->getLabel())}" type="button" class="btn addButton btn-soft-blue module-btn"
														{if stripos($BASIC_ACTION->getUrl(), 'javascript:')===0}
													onclick='{$BASIC_ACTION->getUrl()|substr:strlen("javascript:")};'
														{else}
													onclick='window.location.href = "{$BASIC_ACTION->getUrl()}&app={$SELECTED_MENU_CATEGORY}"'
														{/if}>
													<div class="fa {$BASIC_ACTION->getIcon()}" aria-hidden="true"></div>&nbsp;&nbsp;
													{vtranslate($BASIC_ACTION->getLabel(), $MODULE)}
												</button>
											</li>
										{/if}
									{/foreach}
									{if $MODULE_SETTING_ACTIONS|@count gt 0}
										<li>
											<div class="settingsIcon">
												<button type="button" class="btn btn-soft-dark dropdown-toggle module-btn" data-toggle="dropdown" aria-expanded="false" title="{vtranslate('LBL_SETTINGS', $MODULE)}">
													<span class="fa fa-wrench" aria-hidden="true"></span>&nbsp;{vtranslate('LBL_CUSTOMIZE', 'Reports')}&nbsp; <span class="caret"></span>
												</button>
												<ul class="detailViewSetting dropdown-menu">
													{foreach item=SETTING from=$MODULE_SETTING_ACTIONS}
														<li id="{$MODULE_NAME}_listview_advancedAction_{$SETTING->getLabel()}"><a href={$SETTING->getUrl()}>{vtranslate($SETTING->getLabel(), $MODULE_NAME ,vtranslate($MODULE_NAME, $MODULE_NAME))}</a></li>
													{/foreach}
												</ul>
											</div>
										</li>
									{/if}
									{if $USER_MODEL->isAdminUser()}
										<li>
											<button id="{$MODULE}_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores('Sync Recently Updated')}" type="button" class="btn addButton btn-soft-info module-btn"
												onclick="EquipmentAvailability_List_Js.calcAvail('')">
												<div class="fa fa-plus" aria-hidden="true"></div>&nbsp;&nbsp;
												Calculate Availability
											</button>
										</li>
									{/if}
								</ul>

							</div><!-- /.navbar-collapse -->
						</div><!-- /.container-fluid -->
						
					</nav>
				</div>
			</div>
		</div>
		{if $FIELDS_INFO neq null}
			<script type="text/javascript">
				var uimeta = (function () {
					var fieldInfo = {$FIELDS_INFO};
					return {
						field: {
							get: function (name, property) {
								if (name && property === undefined) {
									return fieldInfo[name];
								}
								if (name && property) {
									return fieldInfo[name][property]
								}
							},
							isMandatory: function (name) {
								if (fieldInfo[name]) {
									return fieldInfo[name].mandatory;
								}
								return false;
							},
							getType: function (name) {
								if (fieldInfo[name]) {
									return fieldInfo[name].type
								}
								return false;
							}
						},
					};
				})();
			</script>
		{/if}
	</div>     
{/strip}
