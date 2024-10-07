{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
	<br>
	<div class="detailViewContainer" id="WooCommerceDetails">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="clearfix">
				<h3 style="margin-top: 0px;">{vtranslate('LabelManager', $QUALIFIED_MODULE)} - <a href="index.php?module={$sourceModule}&view=List" target="_blank">{vtranslate($sourceModule, $sourceModule)}</a></h3>
				<hr>
			</div>
			<div class="fieldBlockContainer">
				<form class="form-horizontal recordEditView" id="EditView" name="edit" method="post" action="index.php" enctype="multipart/form-data">
					<input type="hidden" name="module" value="LabelManager" />
					<input type="hidden" name="parent" value="Settings" />
					<input type="hidden" name="action" value="SaveLanguage" />
					<input type="hidden" name="mode" value="saveLanguageLabel" />
					<input type="hidden" id="language" name="language" value="{$language}">
					<input type="hidden" id="sourceModule" name="sourceModule" value="{$sourceModule}">
					
					<table class="table table-borderless">
						{foreach item=languageStrings_value from=$languageStrings key=languageStrings_key}
							<tr>
								<td>
									<span><b>{$languageStrings_key}</b></span>
								</td>
								<td>
									<input type="text" class="inputElement" name="{$languageStrings_key}" value="{$languageStrings_value}">
								</td>
							</tr>
						{/foreach}
					</table><br><br>
					<div class='modal-overlay-footer clearfix'>
						<div class="row clearfix">
							<div class='textAlignCenter col-lg-12 col-md-12 col-sm-12 '>
								<button type='button' class='btn btn-success saveButton' >{vtranslate('LBL_SAVE', $MODULE)}</button>&nbsp;&nbsp;
								<a class='cancelLink' href="javascript:history.{if $DUPLICATE_RECORDS}go(-2){else}back(){/if}" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
							</div>
						</div>
					</div>
				</form>	
			</div>
		</div>
	</div>
{/strip}
