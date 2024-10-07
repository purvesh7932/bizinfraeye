{strip}
	{assign var="deleted" value="deleted"|cat:$row_no}
	{assign var="image" value="productImage"|cat:$row_no}
	{assign var="purchaseCost" value="purchaseCost"|cat:$row_no}
	{assign var="margin" value="margin"|cat:$row_no}
    {assign var="hdnProductId" value="hdnProductId"|cat:$row_no}
    {assign var="productName" value="productName"|cat:$row_no}
    {assign var="comment" value="comment"|cat:$row_no}
    {assign var="productDescription" value="productDescription"|cat:$row_no}
    {assign var="qtyInStock" value="qtyInStock"|cat:$row_no}
    {assign var="qty" value="qty"|cat:$row_no}
	{assign var="sr_action_one" value="sr_action_one"|cat:$row_no}
	{assign var="sr_action_two" value="sr_action_two"|cat:$row_no}
	{assign var="sr_replace_action" value="sr_replace_action"|cat:$row_no}
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
	{assign var="vendor_name_Label" value="vendor_name_Label"|cat:$row_no}
    {assign var="FINAL" value=$RELATED_PRODUCTS.1.final_details}
	{assign var="productDeleted" value="productDeleted"|cat:$row_no}
	{assign var="productId" value=$data[$hdnProductId]}
	{foreach item=LINEITEM_CUSTOM_FIELDNAME from=$LINEITEM_CUSTOM_FIELDNAMES}
		{assign var={$LINEITEM_CUSTOM_FIELDNAME} value=$LINEITEM_CUSTOM_FIELDNAME|cat:$row_no}
	{/foreach}
	{foreach item=LINEITEM_CUSTOM_FIELDNAME from=$SUB_LINEITEM_CUSTOM_FIELDNAMES}
		{assign var={$LINEITEM_CUSTOM_FIELDNAME} value=$LINEITEM_CUSTOM_FIELDNAME|cat:$row_no}
	{/foreach}
	{foreach item=LINEITEM_CUSTOM_FIELDNAME from=$SUB2_LINEITEM_CUSTOM_FIELDNAMES}
		{assign var={$LINEITEM_CUSTOM_FIELDNAME} value=$LINEITEM_CUSTOM_FIELDNAME|cat:$row_no}
	{/foreach}

	{assign var="listPriceValues" value=Products_Record_Model::getListPriceValues($productId)}
	{if $MODULE eq 'PurchaseOrder'}
		{assign var="listPriceValues" value=array()}
		{assign var="purchaseCost" value="{if $data.$purchaseCost}{((float)$data.$purchaseCost) / ((float)$data.$qty * {$RECORD_CURRENCY_RATE})}{else}0{/if}"}
		{foreach item=currency_details from=$CURRENCIES}
			{append var='listPriceValues' value=$currency_details.conversionrate * $purchaseCost index=$currency_details.currency_id}
		{/foreach}
	{/if}

	<td style="text-align:center;">
		<i class="fa fa-trash deleteRow cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
		&nbsp;<a><img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$MODULE)}"/></a>
		<input type="hidden" class="rowNumber" value="{$row_no}" />
		</br>
		</br>
		<input type="hidden" id="IS_SERV_MANAGER" class="IS_SERV_MANAGER" value="{$IS_SERV_MANAGER}" />
		{if $IS_SERV_MANAGER eq true}
			<button class="duplicate">Duplicate</button>
		{/if}
	</td>
	{if $IMAGE_EDITABLE}
		<td class='lineItemImage' style="text-align:center;">
			{if $data.$image }	<img src='{$data.$image}' height="42" width="42"> {/if}
		</td>
	{/if}

	{if $PRODUCT_EDITABLE}
		<td>
			<!-- Product Re-Ordering Feature Code Addition Starts -->
			<input type="hidden" name="hidtax_row_no{$row_no}" id="hidtax_row_no{$row_no}" value="{$tax_row_no}"/>
			<!-- Product Re-Ordering Feature Code Addition ends -->
			<div class="itemNameDiv form-inline">
				<div class="row">
					<div class="col-lg-10">
						<div class="input-group" style="width:100%">
							<input type="text" id="{$productName}" name="{$productName}" value="{$data.$productName}" class="productName form-control {if $row_no neq 0} autoComplete {/if} " placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}"
								   data-rule-required=true {if !empty($data.$productName)} readonly="readonly" {/if}>
							{if !$data.$productDeleted}
								{* <span class="input-group-addon cursorPointer clearLineItem" title="{vtranslate('LBL_CLEAR',$MODULE)}">
									<i class="fa fa-times-circle"></i>
								</span> *}
							{/if}
							<input type="hidden" id="{$hdnProductId}" name="{$hdnProductId}" value="{$data.$hdnProductId}" class="selectedModuleId"/>
							<input type="hidden" id="lineItemType{$row_no}" name="lineItemType{$row_no}" value="{$entityType}" class="lineItemType"/>
							{* <div class="col-lg-2">
								{if $row_no eq 0}
									<span class="lineItemPopup cursorPointer" data-popup="ServicesPopup" title="{vtranslate('Services',$MODULE)}" data-module-name="Services" data-field-name="serviceid">{Vtiger_Module_Model::getModuleIconPath('Services')}</span>
									<span class="lineItemPopup cursorPointer" data-popup="ProductsPopup" title="{vtranslate('Products',$MODULE)}" data-module-name="Products" data-field-name="productid">{Vtiger_Module_Model::getModuleIconPath('Products')}</span>
								{elseif $entityType eq '' and $PRODUCT_ACTIVE eq 'true'}
									<span class="lineItemPopup cursorPointer" data-popup="ProductsPopup" title="{vtranslate('Products',$MODULE)}" data-module-name="Products" data-field-name="productid">{Vtiger_Module_Model::getModuleIconPath('Products')}</span>
								{elseif $entityType eq '' and $SERVICE_ACTIVE eq 'true'}
									<span class="lineItemPopup cursorPointer" data-popup="ServicesPopup" title="{vtranslate('Services',$MODULE)}" data-module-name="Services" data-field-name="serviceid">{Vtiger_Module_Model::getModuleIconPath('Services')}</span>
								{else}
									{if ($entityType eq 'Services') and (!$data.$productDeleted)}
										<span class="lineItemPopup cursorPointer" data-popup="ServicesPopup" title="{vtranslate('Services',$MODULE)}" data-module-name="Services" data-field-name="serviceid">{Vtiger_Module_Model::getModuleIconPath('Services')}</span>
									{elseif (!$data.$productDeleted)}
										<span class="lineItemPopup cursorPointer" data-popup="ProductsPopup" title="{vtranslate('Products',$MODULE)}" data-module-name="Products" data-field-name="productid">{Vtiger_Module_Model::getModuleIconPath('Products')}</span>
									{/if}
								{/if}
							</div> *}
						</div>
					</div>
				</div>
			</div>
			<input type="hidden" value="{$data.$subproduct_ids}" id="{$subproduct_ids}" name="{$subproduct_ids}" class="subProductIds" />
			<div id="{$subprod_names}" name="{$subprod_names}" class="subInformation">
				<span class="subProductsContainer">
					{foreach key=SUB_PRODUCT_ID item=SUB_PRODUCT_INFO from=$data.$subprod_qty_list}
						{* <em> - {$SUB_PRODUCT_INFO.name} ({$SUB_PRODUCT_INFO.qty})
							{if $SUB_PRODUCT_INFO.qty > getProductQtyInStock($SUB_PRODUCT_ID)}
								&nbsp;-&nbsp;<span class="redColor">{vtranslate('LBL_STOCK_NOT_ENOUGH', $MODULE)}</span>
							{/if}
						</em><br> *}
					{/foreach}
				</span>
			</div>
			{if $data.$productDeleted}
				<div class="row-fluid deletedItem redColor">
					{if empty($data.$productName)}
						{vtranslate('LBL_THIS_LINE_ITEM_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_THIS_LINE_ITEM',$MODULE)}
					{else}
						{vtranslate('LBL_THIS',$MODULE)} {$entityType} {vtranslate('LBL_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_OR_REPLACE_THIS_ITEM',$MODULE)}
					{/if}
				</div>
			{/if}
		</td>
	{/if}

	<td>
		<input readonly style="background-color:#eeeeee!important" id="{$qty}" name="{$qty}" type="text" class="qty smallInputBox inputElement"
			   data-rule-required=true data-rule-positive=true data-rule-greater_than_zero=true value="{if !empty($data.$qty)}{$data.$qty}{else}1{/if}"
			   {if $QUANTITY_EDITABLE eq false} readonly=readonly {/if} />

		{if $PURCHASE_COST_EDITABLE eq false and $MODULE neq 'PurchaseOrder'}
			<input id="{$purchaseCost}" type="hidden" value="{if ((float)$data.$purchaseCost)}{((float)$data.$purchaseCost) / ((float)$data.$qty)}{else}0{/if}" />
            <span style="display:none" class="purchaseCost">0</span>
			<input name="{$purchaseCost}" type="hidden" value="{if $data.$purchaseCost}{$data.$purchaseCost}{else}0{/if}" />
		{/if}
		{if $MARGIN_EDITABLE eq false}
			<input type="hidden" name="{$margin}" value="{if $data.$margin}{$data.$margin}{else}0{/if}"></span>
			<span class="margin pull-right" style="display:none">{if $data.$margin}{$data.$margin}{else}0{/if}</span>
		{/if}
		{* {if $MODULE neq 'PurchaseOrder'}
			<br>
			<span class="stockAlert redColor {if $data.$qty <= $data.$qtyInStock}hide{/if}" >
				{vtranslate('LBL_STOCK_NOT_ENOUGH',$MODULE)}
				<br>
				{vtranslate('LBL_MAX_QTY_SELECT',$MODULE)}&nbsp;<span class="maxQuantity">{$data.$qtyInStock}</span>
			</span>
		{/if} *}
	</td>
	<td>
		{if $COMMENT_EDITABLE}
			<div><textarea readonly style="background-color:#eeeeee!important" id="{$comment}" name="{$comment}" class="lineItemCommentBox">{decode_html($data.$comment)}</textarea></div>
		{/if}
	</td>
	<input type="hidden" id="RSO_CREATABLE_QTY" value={$RSO_CREATABLE_QTY}>
	<input type="hidden" id="fildNamesOfCustFields" value={ZEND_JSON::encode($LINEITEM_CUSTOM_FIELDNAMES)}>
	<input type="hidden" id="fildNamesOfCustFieldsSub1" value={ZEND_JSON::encode($SUB_LINEITEM_CUSTOM_FIELDNAMES)}>
	<input type="hidden" id="fildNamesOfCustFieldsSub2" value={ZEND_JSON::encode($SUB2_LINEITEM_CUSTOM_FIELDNAMES)}>
	{assign var="dateFormat" value=$USER_MODEL->get('date_format')}
	{foreach key=LINEITEM_CUSTOM_FIELDKEY item=LINEITEM_CUSTOM_FIELD from=$LINEITEM_CUSTOM_FIELDS}
		<td a="{$LINEITEM_CUSTOM_FIELD['fieldlabel']}">
			{assign var="fieldName" value=$LINEITEM_CUSTOM_FIELD['fieldname']}
			{if $LINEITEM_CUSTOM_FIELD['fieldlabel'] eq 'Present Status'}
				{foreach key=SUB_LINEITEM_CUSTOM_FIELDKEY item=SUB_LINEITEM_CUSTOM_FIELD from=$SUB_LINEITEM_CUSTOM_FIELDS}
					{assign var="subfieldName" value=$SUB_LINEITEM_CUSTOM_FIELD['fieldname']}
					{assign var="subfieldLabel" value=$SUB_LINEITEM_CUSTOM_FIELD['fieldlabel']}
					<table>
						<tr>
							<td>
								<label style="white-space: nowrap;margin-top:10px;">{$subfieldLabel}</label>
							</td>
						</tr>
					</table>
				{/foreach}
			{/if}

			{if $LINEITEM_CUSTOM_FIELD['fieldlabel'] eq 'Action Taken by DSM'}
			{foreach key=SUB2_LINEITEM_CUSTOM_FIELDKEY item=SUB2_LINEITEM_CUSTOM_FIELD from=$SUB2_LINEITEM_CUSTOM_FIELDS}
			{assign var="sub2fieldName" value=$SUB2_LINEITEM_CUSTOM_FIELD['fieldname']}
			{assign var="sub2fieldLabel" value=$SUB2_LINEITEM_CUSTOM_FIELD['fieldlabel']}
			<table>
			<tr>
			<td>
			<label style="white-space: nowrap;margin-top:10px;">{$sub2fieldLabel}</label>
			</td>
			</tr>
			</table>
			{/foreach}
			{/if}
			{if  $LINEITEM_CUSTOM_FIELD['uitype'] eq '5'}
				<div {if $LINEITEM_CUSTOM_FIELD['hideInitialDisplay'] eq 'true'} class="hide" {/if} class="input-group inputElement" style="margin-bottom: 3px">
					<input {if $LINEITEM_CUSTOM_FIELD['editable'] eq 0} readonly {/if} id="{${$fieldName}}" name="{${$fieldName}}" type="text" class="dateField form-control" data-fieldname="{${$fieldName}}" data-fieldtype="date" data-date-format="{$dateFormat}"
						value="{Vtiger_Functions::IGcurrentUserDisplayDate($data.${$fieldName})}"
						{if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
						{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
						{if count($FIELD_INFO['validator'])}
							data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
						{/if}  data-rule-date="true" />
					<span class="input-group-addon"><i class="fa fa-calendar "></i></span>
				</div>
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '21' or $LINEITEM_CUSTOM_FIELD['uitype'] eq '2'}
				<div {if $LINEITEM_CUSTOM_FIELD['hideInitialDisplay'] eq 'true'} class="hide" {/if} >
					<textarea {if $LINEITEM_CUSTOM_FIELD['editable'] eq 0} readonly style="pointer-events: none;background-color:#eeeeee" {/if} id="{${$fieldName}}" name="{${$fieldName}}" class="">{decode_html($data.${$fieldName})}</textarea>
				</div>
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '7' }
				<div id="{$fieldName}DivCla" {if $LINEITEM_CUSTOM_FIELD['hideInitialDisplay'] eq 'true'} class="hide" {/if}>
				 	<input id="{${$fieldName}}" name="{${$fieldName}}"
					 data-extraname="{$fieldName}" value="{decode_html($data.${$fieldName})}" 
					 type="number"
					{if $LINEITEM_CUSTOM_FIELD['editable'] eq 0} readonly style="pointer-events: none;background-color:#eeeeee;min-width: 100px;" {else} style="min-width: 100px;"{/if}
					{if $fieldName eq 'sto_qty'} 
						class="inputElement {$fieldName} {$data.$hdnProductId}" 
					{else}
						class="inputElement {$fieldName}" 
					{/if}
					{if $fieldName eq 'sto_no'} data-specific-rules='{ZEND_JSON::encode($LINEITEM_CUSTOM_FIELD["validator"])}' {/if}
					  data-rule-positive=true />
				</div>
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '16'}
			 {if $LINEITEM_CUSTOM_FIELD['fieldlabel'] neq 'Present Status' AND $LINEITEM_CUSTOM_FIELD['fieldlabel'] neq 'Action Taken by DSM'}
				<div id="{$fieldName}DivCla" a="{$LINEITEM_CUSTOM_FIELD['fieldlabel']}" {if $LINEITEM_CUSTOM_FIELD['hideInitialDisplay'] eq 'true'} class="hide" {/if}>
					<select {if $LINEITEM_CUSTOM_FIELD['editable'] eq 0} disabled style="pointer-events: none;" {/if} 
					data-rule-required=true style="min-width: 150px;" id="{${$fieldName}}" 
					class="select2-container {if $row_no neq 0}select2{/if} inputElement picklistfield {$fieldName}" 
					name="{${$fieldName}}" data-extraname="{$fieldName}" data-fieldtype="picklist">
						<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
						{foreach  key=PICKLIST_FIELDKEY item=PICKLIST_FIELD_ITEM from=$LINEITEM_CUSTOM_FIELD['picklistValues']}
							<option {if trim(decode_html($data.${$fieldName})) eq $PICKLIST_FIELDKEY} selected {/if} value="{$PICKLIST_FIELDKEY}">{$PICKLIST_FIELDKEY}</option>
						{/foreach}
					</select>
				</div>
			 {/if}
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '56'}
				<input class="inputElement" id="{${$fieldName}}" name="{${$fieldName}}" style="width:15px;height:15px;" data-fieldname="{${$fieldName}}" data-fieldtype="checkbox" type="checkbox"
				{if $data.${$fieldName} eq true} checked {/if}
				{if !empty($SPECIAL_VALIDATOR)}data-validator="{Zend_Json::encode($SPECIAL_VALIDATOR)}"{/if}
				{if $FIELD_INFO["mandatory"] eq true} data-rule-required = "true" {/if}
				{if count($FIELD_INFO['validator'])}
					data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
				{/if}/>
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '10'}
				<div id="{$fieldName}DivCla" {if $LINEITEM_CUSTOM_FIELD['hideInitialDisplay'] eq 'true'} class="hide" {/if}>	
					<div class="referencefield-wrapper">
						<input name="popupReferenceModule" type="hidden" value="Vendors"/>
						<div class="input-group">
							<input name="{${$fieldName}}" type="hidden" value="{if !empty($data.$vendor_name)}{$data.$vendor_name}{/if}" class="sourceField" data-displayvalue='' />
							<input id="{${$fieldName}}_display" name="{${$fieldName}}_display" data-fieldname="{${$fieldName}}" data-fieldtype="reference" type="text" 
								class="marginLeftZero autoComplete2 inputElement" {if !empty($data.$vendor_name)} readonly value="{$data.$vendor_name_Label}" {/if}
								placeholder="Type to Search..."/>
								{assign var="FIELD_VALUE" value=$data.$vendor_name_Label}
							<a href="#" class="clearReferenceSelection {if $FIELD_VALUE eq 0}hide{/if}"> x </a>
								<span class="input-group-addon relatedPopup cursorPointer" title="{vtranslate('LBL_SELECT', $MODULE)}">
									<i id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_select" class="fa fa-search"></i>
								</span>
							{if (($smarty.request.view eq 'Edit') or ($MODULE_NAME eq 'Webforms')) && !in_array($REFERENCE_LIST[0],$QUICKCREATE_RESTRICTED_MODULES)}
								<span class="input-group-addon createReferenceRecord cursorPointer clearfix" title="{vtranslate('LBL_CREATE', $MODULE)}">
								<i id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_create" class="fa fa-plus"></i>
							</span>
							{/if}
						</div>
					</div>
				</div>
			{/if}
		</td>

			{if $LINEITEM_CUSTOM_FIELD['fieldlabel'] eq 'Present Status'}
				<td a="{$LINEITEM_CUSTOM_FIELD['fieldlabel']}">
				{foreach key=SUB_LINEITEM_CUSTOM_FIELDKEY item=SUB_LINEITEM_CUSTOM_FIELD from=$SUB_LINEITEM_CUSTOM_FIELDS}
					{assign var="subfieldName" value=$SUB_LINEITEM_CUSTOM_FIELD['fieldname']}
					{assign var="subfieldLabel" value=$SUB_LINEITEM_CUSTOM_FIELD['fieldlabel']}
							<table>
								<tr>
									<td>
										<div id="{$subfieldName}DivCla">
											<input id="{${$subfieldName}}" name="{${$subfieldName}}" {if {decode_html($data.${$subfieldName})} }value="{decode_html($data.${$subfieldName})}"{else}value=0{/if} data-extraname="{$subfieldName}" class="{$subfieldName} b_qty smallInputBox inputElement" style="margin-top:3px;" type="text" data-rule-positive="true">
										</div>
									</td>
								</tr>
							</table>
					{/foreach}
				</td>
			{/if}

			{if $LINEITEM_CUSTOM_FIELD['fieldlabel'] eq 'Action Taken by DSM'}
			  	<td a="{$LINEITEM_CUSTOM_FIELD['fieldlabel']}">
				{foreach key=SUB2_LINEITEM_CUSTOM_FIELDKEY item=SUB2_LINEITEM_CUSTOM_FIELD from=$SUB2_LINEITEM_CUSTOM_FIELDS}
					{assign var="sub2fieldName" value=$SUB2_LINEITEM_CUSTOM_FIELD['fieldname']}
					{assign var="sub2fieldLabel" value=$SUB2_LINEITEM_CUSTOM_FIELD['fieldlabel']}
							<table>
								<tr>
									<td>
										<div id="{$sub2fieldName}DivCla">
											<input id="{${$sub2fieldName}}" name="{${$sub2fieldName}}" {if {decode_html($data.${$sub2fieldName})} }value="{decode_html($data.${$sub2fieldName})}"{else}value=0{/if}  data-extraname="{$sub2fieldName}" class="{$sub2fieldName} a_qty smallInputBox inputElement" style="margin-top:3px;" type="text" data-rule-positive="true">
										</div>
									</td>
								</tr>
							</table>
					{/foreach}
				</td>
			{/if}
	{/foreach}
{/strip}