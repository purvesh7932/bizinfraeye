{strip}
	{assign var="deleted" value="deleted"|cat:$row_no}
	{assign var="image" value="productImage"|cat:$row_no}
	{assign var="purchaseCost" value="purchaseCost"|cat:$row_no}
	{assign var="margin" value="margin"|cat:$row_no}
    {assign var="hdnProductId_other" value="hdnProductId_other"|cat:$row_no}
    {assign var="productName_other" value="productName_other"|cat:$row_no}
    {assign var="comment" value="comment"|cat:$row_no}
    {assign var="productDescription" value="productDescription"|cat:$row_no}
    {assign var="qtyInStock" value="qtyInStock"|cat:$row_no}
    {assign var="qty_other" value="qty_other"|cat:$row_no}
    {assign var="listPrice" value="listPrice"|cat:$row_no}
    {assign var="productTotal" value="productTotal"|cat:$row_no}
    {assign var="subproduct_ids" value="subproduct_ids"|cat:$row_no}
    {assign var="subprod_names" value="subprod_names"|cat:$row_no}
	{assign var="subprod_qty_list" value="subprod_qty_list"|cat:$row_no}
    {assign var="entityIdentifier" value="entityType"|cat:$row_no}
    {assign var="entityType" value=$data.$entityIdentifier}

    {assign var="discount_type" value="discount_type"|cat:$row_no}
    {assign var="discount_percent" value="discount_percent"|cat:$row_no}
    {assign var="checked_discount_percent" value="checked_discount_percent"|cat:$row_no}
    {assign var="style_discount_percent" value="style_discount_percent"|cat:$row_no}
    {assign var="discount_amount" value="discount_amount"|cat:$row_no}
    {assign var="checked_discount_amount" value="checked_discount_amount"|cat:$row_no}
    {assign var="style_discount_amount" value="style_discount_amount"|cat:$row_no}
    {assign var="checked_discount_zero" value="checked_discount_zero"|cat:$row_no}

    {assign var="discountTotal" value="discountTotal"|cat:$row_no}
    {assign var="totalAfterDiscount" value="totalAfterDiscount"|cat:$row_no}
    {assign var="taxTotal" value="taxTotal"|cat:$row_no}
    {assign var="netPrice" value="netPrice"|cat:$row_no}
	{assign var="picklistValuesConfigured" value="picklistValuesConfigured"|cat:$row_no}
    {assign var="FINAL" value=$RELATED_PRODUCTS_OTHER1.1.final_details}

	{assign var="productDeleted" value="productDeleted"|cat:$row_no}
	{assign var="productId" value=$data[$hdnProductId]}
	{assign var="listPriceValues" value=Products_Record_Model::getListPriceValues($productId)}
	{foreach item=LINEITEM_CUSTOM_FIELDNAME from=$LINEITEM_CUSTOM_FIELDNAMES_OTHER1}
		{assign var={$LINEITEM_CUSTOM_FIELDNAME} value=$LINEITEM_CUSTOM_FIELDNAME|cat:$row_no}
	{/foreach}
	{if $MODULE eq 'PurchaseOrder'}
		{assign var="listPriceValues" value=array()}
		{assign var="purchaseCost" value="{if $data.$purchaseCost}{((float)$data.$purchaseCost) / ((float)$data.$qty * {$RECORD_CURRENCY_RATE})}{else}0{/if}"}
		{foreach item=currency_details from=$CURRENCIES}
			{append var='listPriceValues' value=$currency_details.conversionrate * $purchaseCost index=$currency_details.currency_id}
		{/foreach}
	{/if}

	{* <td style="text-align:center;">
		<i class="fa fa-trash deleteRow cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
		&nbsp;<a><img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$MODULE)}"/></a>
		<input type="hidden" class="rowNumber" value="{$row_no}" />
	</td> *}
	<input type="hidden" id="fildNamesOfCustFieldsOther1" value={ZEND_JSON::encode($LINEITEM_CUSTOM_FIELDNAMES_OTHER1)}>
	{assign var="dateFormat" value=$USER_MODEL->get('date_format')}
	{foreach key=LINEITEM_CUSTOM_FIELDKEY item=LINEITEM_CUSTOM_FIELD from=$LINEITEM_CUSTOM_FIELDS_OTHER1}
		<td {if $tabletdhiderVal eq true} class="tabletdhider" {/if} data-td="{$LINEITEM_CUSTOM_FIELD['fieldname']}">
			{assign var="fieldName" value=$LINEITEM_CUSTOM_FIELD['fieldname']}
			{if  $LINEITEM_CUSTOM_FIELD['uitype'] eq '5'}
				<div class="input-group inputElement" style="margin-bottom: 3px">
					<input  id="{${$fieldName}}" name="{${$fieldName}}" type="text" class="dateField form-control" data-fieldname="{${$fieldName}}" data-fieldtype="date" data-date-format="{$dateFormat}"
						value="{Vtiger_Functions::currentUserDisplayDate($data.${$fieldName})}"
						{if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
						{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
						{if count($FIELD_INFO['validator'])}
							data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
						{/if}  data-rule-date="true" />
					<span class="input-group-addon"><i class="fa fa-calendar "></i></span>
				</div>
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '21' or $LINEITEM_CUSTOM_FIELD['uitype'] eq '2'}
				<div id="{$fieldName}DivCla" {if $LINEITEM_CUSTOM_FIELD['hideInitialDisplay'] eq 'true'} class="hide" {/if}>
					<textarea readonly style="pointer-events: none;background-color:#eeeeee" id="{${$fieldName}}" name="{${$fieldName}}" class="">{decode_html($data.${$fieldName})}
					</textarea>
				</div>
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '9' }
				<div id="{$fieldName}DivCla" {if $LINEITEM_CUSTOM_FIELD['hideInitialDisplay'] eq 'true'} class="hide" {/if}>
					<div class="input-group inputElement">
						<input type="text" data-rule-required="true" data-rule-positive=true class="form-control" id="{${$fieldName}}" name="{${$fieldName}}" 
							 step="any" value="{decode_html($data.${$fieldName})}"
							max="100"/>
						<span class="input-group-addon">%</span>
					</div>
				</div>
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '7' }
				<div id="{$fieldName}DivCla" {if $LINEITEM_CUSTOM_FIELD['hideInitialDisplay'] eq 'true'} class="hide" {/if}>
					<input readonly style="pointer-events: none;background-color:#eeeeee" id="{${$fieldName}}" name="{${$fieldName}}" value="{decode_html($data.${$fieldName})}" class="qty inputElement" data-fieldname="{$fieldName}" style="min-width: 100px;"></input>
				</div>
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '16'}
				<div id="{$fieldName}DivCla" {if $LINEITEM_CUSTOM_FIELD['hideInitialDisplay'] eq 'true'} class="hide" {/if}>
					{if $fieldName eq 'masn_manu'}
						<select data-rule-required=true style="min-width: 150px;" id="{${$fieldName}}" class="select2-container {if $row_no neq 0}select2{/if} inputElement picklistfield {if $fieldName eq 'masn_aggrregate'} disabledpicklistValue {/if} lineitempicklistfield" data-fieldname="{$fieldName}" data-rownum="{$row_no}" name="{${$fieldName}}" data-extraname="{$fieldName}" data-fieldtype="picklist">
							<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
							{foreach  key=PICKLIST_FIELDKEY item=PICKLIST_FIELD_ITEM from=$data.$picklistValuesConfigured}
								<option {if trim(decode_html($data.${$fieldName})) eq $PICKLIST_FIELD_ITEM} selected {/if} value="{$PICKLIST_FIELD_ITEM}">{$PICKLIST_FIELD_ITEM}</option>
							{/foreach}
						</select>
					{else}
						<select data-rule-required=true style="min-width: 150px;" id="{${$fieldName}}" class="select2-container {if $row_no neq 0}select2{/if} inputElement picklistfield {if $fieldName eq 'masn_aggrregate'} disabledpicklistValue {/if} lineitempicklistfield" data-fieldname="{$fieldName}" data-rownum="{$row_no}" name="{${$fieldName}}" data-extraname="{$fieldName}" data-fieldtype="picklist">
							<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
							{foreach  key=PICKLIST_FIELDKEY item=PICKLIST_FIELD_ITEM from=$LINEITEM_CUSTOM_FIELD['picklistValues']}
								<option {if trim(decode_html($data.${$fieldName})) eq $PICKLIST_FIELDKEY} selected {/if} value="{$PICKLIST_FIELDKEY}">{$PICKLIST_FIELDKEY}</option>
							{/foreach}
						</select>
					{/if}
				</div>
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '999'}
				<div id="paymentContainer" style="margin : 0px;min-width: 100px" name="paymentContainer" class="paymentOptions">
					{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$LINEITEM_CUSTOM_FIELD['picklistValues']}
						<div id="payCC" class="floatBlock">
						<label> <input data-extraname="{$fieldName}" id="{${$fieldName}}" data-rule-required="true" data-fieldname="{$fieldName}" name="{${$fieldName}}"  type="radio" value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}"  {if trim(decode_html($data.${$fieldName})) eq $PICKLIST_NAME} checked="checked" {/if}>
						&nbsp {$PICKLIST_VALUE}</label>
						</div>
					{/foreach}
				</div>
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '56'}
				<input class="inputElement" id="{${$fieldName}}" name="{${$fieldName}}" style="width:15px;height:15px;" data-fieldname="{${$fieldName}}" data-fieldtype="checkbox" type="checkbox"
				{if $data.${$fieldName} eq true} checked {/if}
				{if !empty($SPECIAL_VALIDATOR)}data-validator="{Zend_Json::encode($SPECIAL_VALIDATOR)}"{/if}
				{if $FIELD_INFO["mandatory"] eq true} data-rule-required = "true" {/if}
				{if count($FIELD_INFO['validator'])}
					data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
				{/if}/>
			{/if}
		</td>
	{/foreach}
{/strip}