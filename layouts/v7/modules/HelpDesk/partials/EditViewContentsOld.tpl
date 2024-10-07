{strip}
	{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
		<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
	{/if}

	<div name='editContent' class="details" style="padding-top: 30px;">
		<input type="hidden" disabled name="MANDATORYFIELDS" data-value='{ZEND_JSON::encode($MANDATORYFIELDS)|escape}' />
		{if $DUPLICATE_RECORDS}
			<div class="fieldBlockContainer duplicationMessageContainer">
				<div class="duplicationMessageHeader"><b>{vtranslate('LBL_DUPLICATES_DETECTED', $MODULE)}</b></div>
				<div>{getDuplicatesPreventionMessage($MODULE, $DUPLICATE_RECORDS)}</div>
			</div>
		{/if}
		{if $SPECIAL_ERROR}
			<div class="fieldBlockContainer duplicationMessageContainer">
				<div class="duplicationMessageHeader"><b>Error</b></div>
				<div>{$SPECIAL_ERROR}</div>
			</div>
		{/if}
		{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
			{if $BLOCK_FIELDS|@count gt 0}
				<div class='fieldBlockContainer fieldBlockHeader block' data-block="{$BLOCK_LABEL}">
					<h4 class="textOverflowEllipsis maxWidth50">
						<img class="cursorPointer alignMiddle blockToggle hide" src="{vimage_path('arrowRight.png')}" data-mode="hide">
						<img class="cursorPointer alignMiddle blockToggle" src="{vimage_path('arrowdown.png')}" data-mode="show">&nbsp;
						{vtranslate($BLOCK_LABEL, $MODULE)}
					</h4>
					<hr>
					<div  class="container blockData">
						<div class="row">
							{assign var=COUNTER value=0}
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
								{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
                                                                {assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
								{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
								{assign var="refrenceListCount" value=count($refrenceList)}
								{if $FIELD_MODEL->isEditable() eq true}
									{if $FIELD_MODEL->getFieldName() eq 'pincode' }
										<h4 class="hide" id="currentEqLocationHolder">
											Current Equipment Location
										</h4>
									{/if}
									<div style="padding:10px;" id="{$FIELD_MODEL->getFieldName()}hideOrShowId" class="col-sm-6 col-md-6 col-lg-6 parentTRDiv {if $FIELD_MODEL->getFieldName() neq 'ticket_type'} hide {/if}">
									<div class="row">
									<div class="col-sm-5 col-md-5 col-lg-5" style="padding:10px;color: #2c3b49;opacity: 0.8;font-size: 14px;">
										{if $MASS_EDITION_MODE}
											<input class="inputElement" id="include_in_mass_edit_{$FIELD_MODEL->getFieldName()}" data-update-field="{$FIELD_MODEL->getFieldName()}" type="checkbox">&nbsp;
										{/if}
										{if $isReferenceField eq "reference"}
											{if $refrenceListCount > 1}
												{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
												{assign var="REFERENCED_MODULE_STRUCTURE" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
												{if !empty($REFERENCED_MODULE_STRUCTURE)}
													{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCTURE->get('name')}
												{/if}
												<select style="width: 140px;" class="select2 referenceModulesList">
													{foreach key=index item=value from=$refrenceList}
														<option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if}>{vtranslate($value, $value)}</option>
													{/foreach}
												</select>
											{else}
												{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
											{/if}
										{else if $FIELD_MODEL->get('uitype') eq "83"}
											{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) COUNTER=$COUNTER MODULE=$MODULE}
											{if $TAXCLASS_DETAILS}
												{assign 'taxCount' count($TAXCLASS_DETAILS)%2}
												{if $taxCount eq 0}
													{if $COUNTER eq 2}
														{assign var=COUNTER value=1}
													{else}
														{assign var=COUNTER value=2}
													{/if}
												{/if}
											{/if}
										{else}
											{if $MODULE eq 'Documents' && $FIELD_MODEL->get('label') eq 'File Name'}
												{assign var=FILE_LOCATION_TYPE_FIELD value=$RECORD_STRUCTURE['LBL_FILE_INFORMATION']['filelocationtype']}
												{if $FILE_LOCATION_TYPE_FIELD}
													{if $FILE_LOCATION_TYPE_FIELD->get('fieldvalue') eq 'E'}
														{vtranslate("LBL_FILE_URL", $MODULE)}&nbsp;<span class="redColor">*</span>
													{else}
														{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
													{/if}
												{else}
													{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
												{/if}
											{else}
												{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
											{/if}
										{/if}
										&nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
									</div>
									{if $FIELD_MODEL->get('uitype') neq '83'}
										<div class="col-sm-7 col-md-7 col-lg-7">
											<div  id="{$FIELD_MODEL->getFieldName()}hideOrShowInputId" {if in_array($FIELD_MODEL->get('uitype'),array('19','69')) || $FIELD_NAME eq 'description' ||  (($FIELD_NAME eq 'recurringtype' or $FIELD_NAME eq 'reminder_time')  && in_array({$MODULE},array('Events','Calendar')))} class="fieldValueWidth80 {if $FIELD_MODEL->getFieldName() neq 'ticket_type'} hide {/if}"  colspan="3" {assign var=COUNTER value=$COUNTER+1} {elseif $FIELD_MODEL->get('uitype') eq '56'} class="checkBoxType {if $FIELD_MODEL->getFieldName() neq 'ticket_type'} hide {/if}" {elseif $isReferenceField eq 'reference' or $isReferenceField eq 'multireference' } class="p-t-8 {if $FIELD_MODEL->getFieldName() neq 'ticket_type'} hide {/if}" {else}class="{if $FIELD_MODEL->getFieldName() neq 'ticket_type'} hide {/if}" {/if}>
												{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
											</div>
										</div>
									{/if}
								{/if}
								</hr>
								</div>
								</div>
							{/foreach}
							{*If their are odd number of fields in edit then border top is missing so adding the check*}
							{if $COUNTER is odd}
								<div class="col-sm-6 col-md-6 col-lg-6"></div>
								<div class="col-sm-6 col-md-6 col-lg-6"></div>
							{/if}
						</div>
					</div>
				</div>
			{/if}
		{/foreach}
	</div>
	<script type="text/javascript" src="{vresource_url('layouts/v7/modules/Users/resources/intlTelInput.js')}"></script>
{/strip}
