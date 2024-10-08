{strip}
{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
    <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
{/if}
<input type="hidden" id="maxUploadSize" value="{$MAX_UPLOAD_SIZE}"/>
<input type="hidden" name="NONEDITABLEKEYS" data-value='{ZEND_JSON::encode($NONEDITABLEKEYS)|escape}' />
<div name='editContent' style="padding-top: 30px;">
	{if $DUPLICATE_RECORDS}
		<div class="fieldBlockContainer duplicationMessageContainer">
			<div class="duplicationMessageHeader"><b>{vtranslate('LBL_DUPLICATES_DETECTED', $MODULE)}</b></div>
			<div>{getDuplicatesPreventionMessage($MODULE, $DUPLICATE_RECORDS)}</div>
		</div>
	{/if}
    {if $EXTERNALERRORISTHERE}
		<div class="fieldBlockContainer duplicationMessageContainer">
			<div class="duplicationMessageHeader"><b>Service Report Is Created/Updated In CRM, But Sync To SAP , Has Failed , Please Fix Following Errors</b></div>
			<div>{$EXTERNALERROR}</div>
		</div>
	{/if}
    {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
        {if $BLOCK_LABEL eq 'Shortages_And_Damages'}
            {if $SERVICEREPORTTYPE|in_array:$EXTRALINEITEMREQUIREDTYPES}
                {include file="partials/LineItemsEdit1.tpl"|@vtemplate_path:'RecommissioningReports'}
            {/if}
            {if $REPORTTYPE|in_array:$EXTRALINEITEMREQUIREDSUBTYPES}
                {include file="partials/LineItemsEdit1.tpl"|@vtemplate_path:'RecommissioningReports'}
            {/if}
        {elseif $BLOCK_LABEL eq 'Major_Aggregates_Sl_No'}
            {if $SERVICEREPORTTYPE eq 'PRE-DELIVERY' or $SERVICEREPORTTYPE eq 'ERECTION AND COMMISSIONING'}
                {include file="partials/LineItemsEdit2.tpl"|@vtemplate_path:'RecommissioningReports'}
            {/if}
        {elseif $BLOCK_LABEL eq 'LBL_ITEM_DETAILS'}
                {include file="partials/LineItemsEdit.tpl"|@vtemplate_path:'RecommissioningReports'}
        {else}
            {if $BLOCK_FIELDS|@count gt 0}
                <div class='fieldBlockContainer' id="{$BLOCK_LABEL}hideOrShowId" data-block="{$BLOCK_LABEL}">
                        {if $BLOCK_LABEL eq 'Equipment Details' and $REPORTTYPE eq 'WARRANTY CLAIM FOR SUB ASSEMBLY / OTHER SPARE PARTS'}
                            <h4 class='fieldBlockHeader'>{vtranslate('Equipment details where Sub Assembly /Spares Parts fitted', $MODULE)}</h4>
                        {else}
                            <h4 class='fieldBlockHeader'>{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
                        {/if}
                    <hr>
                    <table class="table table-borderless blockData {if $BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION'} addressBlock{/if}">
                        {if ($BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION') and ($MODULE neq 'PurchaseOrder')}
                            <tr>
                                <td class="fieldLabel " name="copyHeader1">
                                    <label  name="togglingHeader">{vtranslate('LBL_BILLING_ADDRESS_FROM', $MODULE)}</label>
                                </td>
                                <td class="fieldValue" name="copyAddress1">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="copyAddressFromRight" class="accountAddress" data-copy-address="billing" checked="checked">
                                            &nbsp;{vtranslate('SINGLE_Accounts', $MODULE)}
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label> 
                                            {if $MODULE eq 'Quotes'}
                                                <input type="radio" name="copyAddressFromRight" class="contactAddress" data-copy-address="billing" checked="checked">
                                                &nbsp;{vtranslate('Related To', $MODULE)}
                                            {else}
                                                <input type="radio" name="copyAddressFromRight" class="contactAddress" data-copy-address="billing" checked="checked">
                                                &nbsp;{vtranslate('SINGLE_Contacts', $MODULE)}
                                            {/if}
                                        </label>
                                    </div>
                                    <div class="radio" name="togglingAddressContainerRight">
                                        <label>
                                            <input type="radio" name="copyAddressFromRight" class="shippingAddress" data-target="shipping" checked="checked">
                                            &nbsp;{vtranslate('Shipping Address', $MODULE)}
                                        </label>
                                    </div>
                                    <div class="radio hide" name="togglingAddressContainerLeft">
                                        <label>
                                            <input type="radio" name="copyAddressFromRight"  class="billingAddress" data-target="billing" checked="checked">
                                            &nbsp;{vtranslate('Billing Address', $MODULE)}
                                        </label>
                                    </div>
                                </td>
                                <td class="fieldLabel" name="copyHeader2">
                                    <label  name="togglingHeader">{vtranslate('LBL_SHIPPING_ADDRESS_FROM', $MODULE)}</label>
                                </td>
                                <td class="fieldValue" name="copyAddress2">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="copyAddressFromLeft" class="accountAddress" data-copy-address="shipping" checked="checked">
                                            &nbsp;{vtranslate('SINGLE_Accounts', $MODULE)}
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                            {if $MODULE eq 'Quotes'}
                                                <input type="radio" name="copyAddressFromLeft" class="contactAddress" data-copy-address="shipping" checked="checked">
                                                &nbsp;{vtranslate('Related To', $MODULE)}
                                            {else}
                                                <input type="radio" name="copyAddressFromLeft" class="contactAddress" data-copy-address="shipping" checked="checked">
                                                &nbsp;{vtranslate('SINGLE_Contacts', $MODULE)}	
                                            {/if}
                                        </label>
                                    </div>
                                    <div class="radio" name="togglingAddressContainerLeft">
                                        <label>
                                            <input type="radio" name="copyAddressFromLeft" class="billingAddress" data-target="billing" checked="checked">
                                            &nbsp;{vtranslate('Billing Address', $MODULE)}
                                        </label>
                                    </div>
                                    <div class="radio hide" name="togglingAddressContainerRight">
                                        <label>
                                            <input type="radio" name="copyAddressFromLeft" class="shippingAddress" data-target="shipping" checked="checked">
                                            &nbsp;{vtranslate('Shipping Address', $MODULE)}
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        {/if}
                        <tr>
                        {assign var=COUNTER value=0}
                        {foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
                            {assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
                            {assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
                            {assign var="refrenceListCount" value=count($refrenceList)}
                            {if $FIELD_MODEL->isEditable() eq true}
                                {if $FIELD_MODEL->get('uitype') eq "19"}
                                    {if $COUNTER eq '1'}
                                        <td></td><td></td></tr><tr>
                                        {assign var=COUNTER value=0}
                                    {/if}
                                {/if}
                                {if $COUNTER eq 2}
                                    </tr><tr>
                                    {assign var=COUNTER value=1}
                                {else}
                                    {assign var=COUNTER value=$COUNTER+1}
                                {/if}
                                <td id="{$FIELD_MODEL->getFieldName()}hideOrShowId" class="fieldLabel alignMiddle" data-td="{$FIELD_MODEL->getFieldName()}">
                                    {if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
                                    {if $isReferenceField eq "reference"}
                                        {if $refrenceListCount > 1}
                                            <span>{vtranslate($FIELD_MODEL->get('label'), $MODULE)}</span>
                                            {assign var="REFERENCED_MODULE_ID" value=$FIELD_MODEL->get('fieldvalue')}
                                            {assign var="REFERENCED_MODULE_STRUCTURE" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($REFERENCED_MODULE_ID)}
                                            {if !empty($REFERENCED_MODULE_STRUCTURE)}
                                                {assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCTURE->get('name')}
                                            {/if}
                                            <select disabled style="width: 140px;" class="select2 referenceModulesList">
                                                {foreach key=index item=value from=$refrenceList}
                                                    <option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if}>{vtranslate($value, $value)}</option>
                                                {/foreach}
                                            </select>
                                        {else}
                                            {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                        {/if}
                                    {else}
                                        {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                    {/if}
                                    &nbsp;&nbsp;
                                </td>
                                <td id="{$FIELD_MODEL->getFieldName()}hideOrShowInputId" {if in_array($FIELD_MODEL->get('uitype'),array('19','69')) || $FIELD_NAME eq 'description' || $BLOCK_LABEL eq 'GENERAL_CHECKS'} class="fieldValue fieldValueWidth80" colspan="3" {assign var=COUNTER value=$COUNTER+1} {else} class="fieldValue" {/if} data-td="{$FIELD_MODEL->getFieldName()}">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                    {if $FIELD_MODEL->getFieldDataType() eq 'image' || $FIELD_MODEL->getFieldDataType() eq 'file'}
                                        <hr style="height:1px;border-width:0;color:gray;background-color:black">
                                    {/if}
                                </td>
                            {/if}
                        {/foreach}
                        {*If their are odd number of fields in edit then border top is missing so adding the check*}
                        {if $COUNTER is odd}
                            <td></td>
                            <td></td>
                        {/if}
                        </tr>
                    </table>
                </div>
            {/if}
        {/if}
     {/foreach}
     	<script type="text/javascript" src="{vresource_url('layouts/v7/modules/Users/resources/intlTelInput.js')}"></script>
</div>