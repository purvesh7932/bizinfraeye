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
    {assign var="FINAL" value=$RELATED_PRODUCTS.1.final_details}
	{assign var="productDeleted" value="productDeleted"|cat:$row_no}
	{assign var="igLineItemId" value="igLineItemId"|cat:$row_no}
	{assign var="so_creatable_qty" value="so_creatable_qty"|cat:$row_no}
	{assign var="productId" value=$data[$hdnProductId]}
	{foreach item=LINEITEM_CUSTOM_FIELDNAME from=$LINEITEM_CUSTOM_FIELDNAMES}
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
		<button class="duplicate">Duplicate</button>
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
								   data-rule-required=true {if !empty($data.$productName)} disabled="disabled" {/if}>
							{* {if !$data.$productDeleted}
								<span class="input-group-addon cursorPointer clearLineItem" title="{vtranslate('LBL_CLEAR',$MODULE)}">
									<i class="fa fa-times-circle"></i>
								</span>
							{/if} *}
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
						<em> - {$SUB_PRODUCT_INFO.name} ({$SUB_PRODUCT_INFO.qty})
							{if $SUB_PRODUCT_INFO.qty > getProductQtyInStock($SUB_PRODUCT_ID)}
								&nbsp;-&nbsp;<span class="redColor">{vtranslate('LBL_STOCK_NOT_ENOUGH', $MODULE)}</span>
							{/if}
						</em><br>
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
			{else}
				{if $COMMENT_EDITABLE}
					<div><br><textarea readonly style="background-color:#eeeeee!important" id="{$comment}" name="{$comment}" class="lineItemCommentBox">{decode_html($data.$comment)}</textarea></div>
				{/if}
			{/if}
		</td>
	{/if}

	<td>
		{* <input hidden id="{$so_creatable_qty}" name="{$so_creatable_qty}" type="text" class="qty smallInputBox inputElement"
			readonly data-extraname="so_creatable_qty"
			value="{if !empty($data.$so_creatable_qty)}{$data.$so_creatable_qty}{else}1{/if}"
		/> *}

		<input id="{$qty}" name="{$qty}" type="text" class="qty smallInputBox inputElement"
			   data-rule-required=true data-extraname="qty" style="pointer-events: none;background-color:#eeeeee" readonly data-rule-positive=true data-rule-greater_than_zero=true value="{if !empty($data.$qty)}{$data.$qty}{else}1{/if}"
			   {if $QUANTITY_EDITABLE eq false} disabled=disabled {/if} />

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
	{assign var="dateFormat" value=$USER_MODEL->get('date_format')}
	{foreach key=LINEITEM_CUSTOM_FIELDKEY item=LINEITEM_CUSTOM_FIELD from=$LINEITEM_CUSTOM_FIELDS}
		<td  {if $LINEITEM_CUSTOM_FIELD["hiddenSpecial"] eq true} class="hide" {/if}>
			{assign var="fieldName" value=$LINEITEM_CUSTOM_FIELD['fieldname']}
			{if  $LINEITEM_CUSTOM_FIELD['uitype'] eq '5'}
				<div class="input-group inputElement" style="margin-bottom: 3px">
					<input  id="{${$fieldName}}" name="{${$fieldName}}" type="text" class="dateField form-control" data-fieldname="{${$fieldName}}" data-fieldtype="date" data-date-format="{$dateFormat}"
						value="{Vtiger_Functions::currentUserDisplayDate($data.${$fieldName})}"
						{if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
						{if $LINEITEM_CUSTOM_FIELD["isMandatory"] eq true} data-rule-required="true" {/if}
						{if count($LINEITEM_CUSTOM_FIELD['validator'])}
							data-specific-rules='{ZEND_JSON::encode($LINEITEM_CUSTOM_FIELD["validator"])}'
						{/if}  data-rule-date="true" />
					<span class="input-group-addon"><i class="fa fa-calendar "></i></span>
				</div>
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '21' or $LINEITEM_CUSTOM_FIELD['uitype'] eq '2'}
				{if $fieldName eq 'part_description'}
					<div>
						<textarea readonly style="background-color:#eeeeee!important" {if $LINEITEM_CUSTOM_FIELD['editable'] eq 0} readonly style="pointer-events: none;background-color:#eeeeee" {/if} {if $LINEITEM_CUSTOM_FIELD["isMandatory"] eq true} data-rule-required="true" {/if} id="{${$fieldName}}" name="{${$fieldName}}" class="{$fieldName}">{decode_html($data.${$fieldName})}</textarea>
					</div>
				{else}
					<div>
						<textarea {if $LINEITEM_CUSTOM_FIELD['editable'] eq 0} readonly style="pointer-events: none;background-color:#eeeeee" {/if} {if $LINEITEM_CUSTOM_FIELD["isMandatory"] eq true} data-rule-required="true" {/if} id="{${$fieldName}}" name="{${$fieldName}}" class="{$fieldName}">{decode_html($data.${$fieldName})}</textarea>
					</div>
				{/if}
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '7' }
				<div id="DivCla{$fieldName}">
					<input  {if $LINEITEM_CUSTOM_FIELD["hiddenSpecial"] eq true} type="hiden" {/if}
					 {if $LINEITEM_CUSTOM_FIELD["isMandatory"] eq true} data-rule-required="true" {/if}
					  id="{${$fieldName}}" name="{${$fieldName}}" value="{decode_html($data.${$fieldName})}"
					{if $fieldName eq 'final_qty'} 
						class="inputElement {$fieldName} {$data.$hdnProductId}" 
					{else}
						class="inputElement {$fieldName}" 
					{/if}
					{if $LINEITEM_CUSTOM_FIELD['editable_special'] eq true} readonly {/if}
					data-extraname="{$fieldName}" style="min-width: 100px;{if $LINEITEM_CUSTOM_FIELD['editable_special'] eq true}pointer-events: none;background-color:#eeeeee"{/if}">
				</div>
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '16'}
				<select {if $LINEITEM_CUSTOM_FIELD["isMandatory"] eq true} data-rule-required="true" {/if} 
				style="min-width: 150px;" id="{${$fieldName}}" data-fieldname="{$fieldName}"
				class="select2-container inputElement {if $row_no neq 0}select2{/if} inputElement picklistfield lineitempicklistfield"
				 name="{${$fieldName}}" class="inputElement picklistfield" data-fieldtype="picklist">
					<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
					{foreach  key=PICKLIST_FIELDKEY item=PICKLIST_FIELD_ITEM from=$LINEITEM_CUSTOM_FIELD['picklistValues']}
						<option {if trim(decode_html($data.${$fieldName})) eq $PICKLIST_FIELDKEY} selected {/if} value="{$PICKLIST_FIELDKEY}">{$PICKLIST_FIELDKEY}</option>
					{/foreach}
				</select>
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '56'}
				<input class="inputElement" id="{${$fieldName}}" name="{${$fieldName}}" style="width:15px;height:15px;" data-fieldname="{${$fieldName}}" data-fieldtype="checkbox" type="checkbox"
				{if $data.${$fieldName} eq true} checked {/if}
				{if !empty($SPECIAL_VALIDATOR)}data-validator="{Zend_Json::encode($SPECIAL_VALIDATOR)}"{/if}
				{if $LINEITEM_CUSTOM_FIELD["isMandatory"] eq true} data-rule-required = "true" {/if}
				{if count($LINEITEM_CUSTOM_FIELD['validator'])}
					data-specific-rules='{ZEND_JSON::encode($LINEITEM_CUSTOM_FIELD["validator"])}'
				{/if}/>
			{/if}
		</td>
	{/foreach}
{/strip}