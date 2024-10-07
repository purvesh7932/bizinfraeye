{*<!--
/*********************************************************************************
 ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
********************************************************************************/
-->*}

{strip}
	{if !is_array($IMAGE_DETAILS)}
		{assign var=IMAGE_DETAILS value=$RECORD_STRUCTURE_MODEL->getRecord()->getImageDetails()}
	{/if}
	{if $MODULE_NAME eq 'Webforms'}
		<input type="text" readonly="" />
	{else}
		{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
		{assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
		<div class="fileUploadContainer text-left">
		{if $FIELD_MODEL->getName() neq 'imagename'}
			<div class="fileUploadBtn btn btn-primary">
				<span><i class="fa fa-laptop"></i> {vtranslate('LBL_UPLOAD', $MODULE)}</span>
				<input type="file" class="inputElement {if $MODULE eq 'Products' or $MODULE eq 'HelpDesk' or $MODULE eq 'ServiceReports'}multi max-6{/if} {if $FIELD_MODEL->get('fieldvalue') and $FIELD_INFO["mandatory"] eq true} ignore-validation {/if}" name="{$FIELD_MODEL->getFieldName()}[]" value="{$FIELD_MODEL->get('fieldvalue')}"
					{if !empty($SPECIAL_VALIDATOR)}data-validator="{Zend_Json::encode($SPECIAL_VALIDATOR)}"{/if} 
					{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
					{if count($FIELD_INFO['validator'])} 
						data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
					{/if} />
			</div>
		{/if}
			<div class="uploadedFileDetails {if $IS_EXTERNAL_LOCATION_TYPE}hide{/if}">
				<div class="uploadedFileSize"></div>
				<div class="uploadedFileName">
					{if !empty($FIELD_VALUE) && !$smarty.request['isDuplicate']}
						[{$FIELD_MODEL->getDisplayValue($FIELD_VALUE)}]
					{/if}
				</div>
			</div>
		</div>
		{if $FIELD_MODEL->getFieldDataType() eq 'image' || $FIELD_MODEL->getFieldDataType() eq 'file'}
			{if $MODULE neq 'Products' and $MODULE neq 'HelpDesk' and $MODULE neq 'ServiceReports' }
                            <div class='redColor'>
				{vtranslate('LBL_NOTE_EXISTING_ATTACHMENTS_WILL_BE_REPLACED', $MODULE)}
                            </div>
                        {/if}
		{/if}
		{if $MODULE eq 'Products' or $MODULE eq 'HelpDesk'}<div id="MultiFile1_wrap_list" class="MultiFile-list"></div>{/if}

		{foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
			<div class="row" style="margin-top:5px;">
				{if $FIELD_MODEL->getName() eq $IMAGE_INFO.fieldNameFromDB}
					{if !empty($IMAGE_INFO.url)}
						{if ( strpos($IMAGE_INFO.name , '.png') >= 1 || strpos($IMAGE_INFO.name , '.jpeg') >= 1 || strpos($IMAGE_INFO.name , '.jpg') >= 1 || strpos($IMAGE_INFO.name , '.JPEG') >= 1 )  }
							<span class="col-lg-6" name="existingImages"><img src="{$IMAGE_INFO.url}" data-image-id="{$IMAGE_INFO.id}" width="400" height="250" ></span>
							<span class="col-lg-3">
								<span class="row">
									<span class="col-lg-1">{if $FIELD_MODEL->getName() neq 'imagename'}<input type="button" id="file_{$ITER}" value="{vtranslate('LBL_DELETE','Vtiger')}" class="imageDelete">{/if}</span>
								</span>
							</span>
						{else}
							<span class="col-lg-6" name="existingImages"> <img style="display:none" data-image-id="{$IMAGE_INFO.id}" target="_blank" title="{$IMAGE_INFO.name}"/><span><a href="{$IMAGE_INFO.url}">{$IMAGE_INFO.name}</a></span></span>
							<span class="col-lg-3">
								<span class="row">
									<span class="col-lg-1"> {if $FIELD_MODEL->getName() neq 'imagename'}<input type="button" id="file_{$ITER}" value="{vtranslate('LBL_DELETE','Vtiger')}" class="imageDelete">{/if}</span>
								</span>
							</span>
						{/if}
					{/if}
				{/if}
			</div>
		{/foreach}
	{/if}
{/strip}
