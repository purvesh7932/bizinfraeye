{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
	<div class="detailViewContainer" id="WooCommerceDetails">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="clearfix">
				<h3 style="margin-top: 0px;">{vtranslate('LabelManager', $QUALIFIED_MODULE)}</h3>
				<hr>
			</div>
			<div class="fieldBlockContainer">
				
				<table class="table table-borderless">
					<tr>
						<td>
							<span><b>{vtranslate('Select Modules', $QUALIFIED_MODULE)}</b></span>
						</td>
						<td>
							<select class="select2" name="addModules" id="addModules" style="width:500px;">
								<option value="">{vtranslate('Select an Option', $QUALIFIED_MODULE)}</option>
								{foreach item=supportModules_value from=$supportModules key=supportModules_key}
									<option value="{$supportModules_value}">{vtranslate($supportModules_value, $supportModules_value)}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<span><b>{vtranslate('Select Language', $QUALIFIED_MODULE)}</b></span>
						</td>
						<td>
							<select class="select2" name="language" id="language" style="width:500px;">
								<option value="">{vtranslate('Select an Option', $QUALIFIED_MODULE)}</option>
								{foreach item=LANGUAGES_VALUE from=$LANGUAGES key=LANGUAGES_KEY}
									<option value="{$LANGUAGES_KEY}">{$LANGUAGES_VALUE}</option>
								{/foreach}
							</select>
						</td>
					</tr>
				</table>
				<div class='modal-overlay-footer clearfix'>
					<div class="row clearfix">
						<div class='textAlignCenter col-lg-12 col-md-12 col-sm-12 '>
							<button type='button' class='btn btn-success nextButton' >{vtranslate('LBL_NEXT', $MODULE)}</button>&nbsp;&nbsp;
							<a class='cancelLink' href="javascript:history.{if $DUPLICATE_RECORDS}go(-2){else}back(){/if}" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
