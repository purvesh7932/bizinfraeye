{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
<script type="text/javascript">
function openInNewTab(url) {
 window.open(url, '_blank').focus();
}
</script>
{strip}
	{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
		<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
	{/if}

	{foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
		{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
		{if $BLOCK eq null or $FIELD_MODEL_LIST|@count lte 0 or $BLOCK_LABEL_KEY eq 'LBL_PRICING_INFORMATION'}{continue}{/if}
		{if $BLOCK_LABEL_KEY eq 'Major_Aggregates_Sl_No' or $BLOCK_LABEL_KEY eq 'Shortages_And_Damages' or $BLOCK_LABEL_KEY eq 'LBL_ITEM_DETAILS'}
			{if $BLOCK_LABEL_KEY eq 'Shortages_And_Damages'}
				{if $SERVICEREPORTTYPE|in_array:$EXTRALINEITEMREQUIREDTYPES}
					{include file="LineItemsDetailShortDam.tpl"|@vtemplate_path:'ServiceReports'}
					{continue}
				{/if}
				{if $REPORTTYPE|in_array:$EXTRALINEITEMREQUIREDSUBTYPES}
					{include file="LineItemsDetailShortDam.tpl"|@vtemplate_path:'ServiceReports'}
					{continue}
				{/if}
			{elseif $BLOCK_LABEL_KEY eq 'Major_Aggregates_Sl_No'}
				{if $SERVICEREPORTTYPE eq 'PRE-DELIVERY' or $SERVICEREPORTTYPE eq 'ERECTION AND COMMISSIONING'}
					{include file="LineItemsDetailShortDam1.tpl"|@vtemplate_path:'ServiceReports'}
					{continue}
				{/if}
			{elseif $BLOCK_LABEL_KEY eq 'LBL_ITEM_DETAILS'}
					{include file="LineItemsDetail.tpl"|@vtemplate_path:'ServiceReports'}
					{continue}
			{/if}
		{else}
			<div class="block block_{$BLOCK_LABEL_KEY}" data-block="{$BLOCK_LABEL_KEY}" data-blockid="{$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}">
				{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
				{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
				<input type=hidden name="timeFormatOptions" data-value='{$DAY_STARTS}' />
				<div>
					<h4 class="textOverflowEllipsis maxWidth50">
						<img class="cursorPointer alignMiddle blockToggle {if !($IS_HIDDEN)} hide {/if}" src="{vimage_path('arrowRight.png')}" data-mode="hide" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}>
						<img class="cursorPointer alignMiddle blockToggle {if ($IS_HIDDEN)} hide {/if}" src="{vimage_path('arrowdown.png')}" data-mode="show" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}>&nbsp;
						{vtranslate({$BLOCK_LABEL_KEY},{$MODULE_NAME})}
					</h4>
				</div>
				<hr>
				<div class="blockData">
					<table class="table detailview-table no-border">
						<tbody {if $IS_HIDDEN} class="hide" {/if}>
							{assign var=COUNTER value=0}
							<tr>
								{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELD_MODEL_LIST}
									{assign var=fieldDataType value=$FIELD_MODEL->getFieldDataType()}
									{if !$FIELD_MODEL->isViewableInDetailView()}
										{continue}
									{/if}
									{if $FIELD_MODEL->get('uitype') eq "83"}
										{foreach item=tax key=count from=$TAXCLASS_DETAILS}
											{if $COUNTER eq 2}
												</tr><tr>
												{assign var="COUNTER" value=1}
											{else}
												{assign var="COUNTER" value=$COUNTER+1}
											{/if}
											<td class="fieldLabel {$WIDTHTYPE}">
												<span class='muted'>{vtranslate($tax.taxlabel, $MODULE)}(%)</span>
											</td>
											<td class="fieldValue {$WIDTHTYPE}">
												<span class="value textOverflowEllipsis" data-field-type="{$FIELD_MODEL->getFieldDataType()}" >
													{if $tax.check_value eq 1}
														{$tax.percentage}
													{else}
														0
													{/if} 
												</span>
											</td>
										{/foreach}
									{else if $FIELD_MODEL->get('uitype') eq "69" || $FIELD_MODEL->get('uitype') eq "105"}
										{if $COUNTER neq 0}
											{if $COUNTER eq 2}
												</tr><tr>
												{assign var=COUNTER value=0}
											{/if}
										{/if}
										<td class="fieldLabel {$WIDTHTYPE}"><span class="muted">{vtranslate({$FIELD_MODEL->get('label')},{$MODULE_NAME})}</span></td>
										<td class="fieldValue {$WIDTHTYPE}">
											<ul id="imageContainer" class="imageSlider">
												{* {if empty($IMAGE_DETAILS)}
													<div class="name"><span><strong>{$MODULE_MODEL->getModuleIcon()}</strong></span></div>
												{/if} *}
												{if $MODULE_NAME eq 'HelpDesk' ||  $MODULE_NAME eq 'ServiceReports'}
													{foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
														{if $FIELD_MODEL->getName() eq $IMAGE_INFO.fieldNameFromDB}
															{if ( strpos($IMAGE_INFO.name , '.png') >= 1 || strpos($IMAGE_INFO.name , '.jpeg') >= 1 || strpos($IMAGE_INFO.name , '.jpg') >= 1)  }
																<li>
																	<span>
																		<a onclick="Vtiger_Header_Js.fileView({$RECORDID},{$IMAGE_INFO.id});">{$IMAGE_INFO.name}</a>
																	</span>
																</li>
															{else}									
																<li>
																	<img style="display:none" data-image-id="{$IMAGE_INFO.id}"  title="{$IMAGE_INFO.name}"/>
																	<span>
																		<a onclick="Vtiger_Header_Js.fileView({$RECORDID},{$IMAGE_INFO.id});">
																			{$IMAGE_INFO.name}
																		</a>
																	</span>
																</li>
															{/if}
														{/if}
													{/foreach}
												{else}
													{foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
														{if !empty($IMAGE_INFO.url) && !empty({$IMAGE_INFO.orgname}) }
															{if ( strpos($IMAGE_INFO.name , '.png') >= 1 || strpos($IMAGE_INFO.name , '.jpeg') >= 1 || strpos($IMAGE_INFO.name , '.jpg') >= 1)  }
																<li><img src="{$IMAGE_INFO.url}" title="{$IMAGE_INFO.orgname}" width="400" height="300" /><span><a href="{$IMAGE_INFO.url}">{$IMAGE_INFO.name}</a></span></li>
															{else}
																<li><img style="display:none" data-image-id="{$IMAGE_INFO.id}" target="_blank" title="{$IMAGE_INFO.name}"/><span><a href="{$IMAGE_INFO.url}">{$IMAGE_INFO.name}</a></span></li>
															{/if}
														{/if}
													{/foreach}
												{/if}
											</ul>
										</td>
										{assign var=COUNTER value=$COUNTER+1}
									{else}
										{if $FIELD_MODEL->get('uitype') eq "20" or $FIELD_MODEL->get('uitype') eq "19" or $fieldDataType eq 'reminder' or $fieldDataType eq 'recurrence'}
											{if $COUNTER eq '1'}
												<td class="fieldLabel {$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td></tr><tr>
												{assign var=COUNTER value=0}
											{/if}
										{/if}
										{if $COUNTER eq 2}
											</tr><tr>
											{assign var=COUNTER value=1}
										{else}
											{assign var=COUNTER value=$COUNTER+1}
										{/if}
										<td class="fieldLabel textOverflowEllipsis {$WIDTHTYPE}" id="{$MODULE_NAME}_detailView_fieldLabel_{$FIELD_MODEL->getName()}" {if $FIELD_MODEL->getName() eq 'description' or $FIELD_MODEL->get('uitype') eq '69'} style='width:8%'{/if}>
											<span class="muted">
												{if $MODULE_NAME eq 'Documents' && $FIELD_MODEL->get('label') eq "File Name" && $RECORD->get('filelocationtype') eq 'E'}
													{vtranslate("LBL_FILE_URL",{$MODULE_NAME})}
												{else}
													{vtranslate({$FIELD_MODEL->get('label')},{$MODULE_NAME})}
												{/if}
												{if ($FIELD_MODEL->get('uitype') eq '72') && ($FIELD_MODEL->getName() eq 'unit_price')}
													({$BASE_CURRENCY_SYMBOL})
												{/if}
											</span>
										</td>
										<td class="fieldValue {$WIDTHTYPE}" id="{$MODULE_NAME}_detailView_fieldValue_{$FIELD_MODEL->getName()}" {if $FIELD_MODEL->get('uitype') eq '19' or $fieldDataType eq 'reminder' or $fieldDataType eq 'recurrence'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
											{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
											{if $fieldDataType eq 'multipicklist'}
												{assign var=FIELD_DISPLAY_VALUE value=$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}
											{else}
												{assign var=FIELD_DISPLAY_VALUE value=Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue')))}
											{/if}

											<span class="value" data-field-type="{$FIELD_MODEL->getFieldDataType()}" {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '21'} style="white-space:normal;" {/if}>
												{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
											</span>
											{if $IS_AJAX_ENABLED && $FIELD_MODEL->isEditable() eq 'true' && $FIELD_MODEL->isAjaxEditable() eq 'true'}
												<span class="hide edit pull-left">
													{if $fieldDataType eq 'multipicklist'}
														<input type="hidden" class="fieldBasicData" data-name='{$FIELD_MODEL->get('name')}[]' data-type="{$fieldDataType}" data-displayvalue='{$FIELD_DISPLAY_VALUE}' data-value="{$FIELD_VALUE}" />
													{else}
														<input type="hidden" class="fieldBasicData" data-name='{$FIELD_MODEL->get('name')}' data-type="{$fieldDataType}" data-displayvalue='{$FIELD_DISPLAY_VALUE}' data-value="{$FIELD_VALUE}" />
													{/if}
												</span>
												<span class="action pull-right"><a href="#" onclick="return false;" class="editAction fa fa-pencil"></a></span>
											{/if}
										</td>
									{/if}

									{if $FIELD_MODEL_LIST|@count eq 1 and $FIELD_MODEL->get('uitype') neq "19" and $FIELD_MODEL->get('uitype') neq "20" and $FIELD_MODEL->get('uitype') neq "30" and $FIELD_MODEL->get('name') neq "recurringtype" and $FIELD_MODEL->get('uitype') neq "69" and $FIELD_MODEL->get('uitype') neq "105"}
										<td class="fieldLabel {$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td>
									{/if}
								{/foreach}
								{* adding additional column for odd number of fields in a block *}
								{if $FIELD_MODEL_LIST|@end eq true and $FIELD_MODEL_LIST|@count neq 1 and $COUNTER eq 1}
									<td class="fieldLabel {$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td>
								{/if}
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		{/if}
		<br>
	{/foreach}
{/strip}