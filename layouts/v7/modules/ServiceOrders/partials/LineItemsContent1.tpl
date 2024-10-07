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
    {assign var="FINAL" value=$RELATED_PRODUCTS_OTHER.1.final_details}

	{assign var="productDeleted" value="productDeleted"|cat:$row_no}
	{assign var="productId" value=$data[$hdnProductId]}
	{assign var="listPriceValues" value=Products_Record_Model::getListPriceValues($productId)}
	{foreach item=LINEITEM_CUSTOM_FIELDNAME from=$LINEITEM_CUSTOM_FIELDNAMES_OTHER}
		{assign var={$LINEITEM_CUSTOM_FIELDNAME} value=$LINEITEM_CUSTOM_FIELDNAME|cat:$row_no}
	{/foreach}
	{if $MODULE eq 'PurchaseOrder'}
		{assign var="listPriceValues" value=array()}
		{assign var="purchaseCost" value="{if $data.$purchaseCost}{((float)$data.$purchaseCost) / ((float)$data.$qty * {$RECORD_CURRENCY_RATE})}{else}0{/if}"}
		{foreach item=currency_details from=$CURRENCIES}
			{append var='listPriceValues' value=$currency_details.conversionrate * $purchaseCost index=$currency_details.currency_id}
		{/foreach}
	{/if}
	<td style="text-align:center;">
		{* <i class="fa fa-trash deleteRow cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}"></i> *}
		&nbsp;<a><img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$MODULE)}"/></a>
		<input type="hidden" class="rowNumber" value="{$row_no}" />
	</td>
	{if $IMAGE_EDITABLE}
		<td class='lineItemImage' style="text-align:center;">
			{if $data.$image }	<img src='{$data.$image}' height="42" width="42"> {/if}
		</td>
	{/if}

	{if $PRODUCT_EDITABLE}
		<td>
			<input type="hidden" name="hidtax_row_no{$row_no}" id="hidtax_row_no{$row_no}" value="{$tax_row_no}"/>
			<div class="itemNameDiv form-inline">
				<div class="row">
					<div class="col-lg-10">
						<div class="input-group" style="width:100%">
							<input disabled type="text" id="{$productName_other}" name="{$productName_other}" value="{$data.$productName_other}" class="productName form-control autoComplete1" placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}"
							 {if !empty($data.$productName_other)} disabled="disabled" {/if}>
							{if !$data.$productDeleted}
								<span class="input-group-addon cursorPointer clearLineItem" title="{vtranslate('LBL_CLEAR',$MODULE)}">
									<i class="fa fa-times-circle"></i>
								</span>
							{/if}
							<input type="hidden" id="{$hdnProductId_other}" name="{$hdnProductId_other}" value="{$data.$hdnProductId_other}" class="selectedModuleId"/>
							<input type="hidden" id="lineItemType{$row_no}" name="lineItemType{$row_no}" value="{$entityType}" class="lineItemType"/>
							<div class="col-lg-2">
								{if $row_no eq 0}
									<span class="lokesh lineItemPopup1 cursorPointer" data-popup="ServicesPopup" title="{vtranslate('Services',$MODULE)}" data-module-name="Services" data-field-name="serviceid">{Vtiger_Module_Model::getModuleIconPath('Services')}</span>
									<span class="lineItemPopup1 cursorPointer" data-popup="ProductsPopup" title="{vtranslate('Products',$MODULE)}" data-module-name="Products" data-field-name="productid">{Vtiger_Module_Model::getModuleIconPath('Products')}</span>
								{elseif $entityType eq '' and $PRODUCT_ACTIVE eq 'true'}
									<span class="lineItemPopup1 cursorPointer" data-popup="ProductsPopup" title="{vtranslate('Products',$MODULE)}" data-module-name="Products" data-field-name="productid">{Vtiger_Module_Model::getModuleIconPath('Products')}</span>
								{elseif $entityType eq '' and $SERVICE_ACTIVE eq 'true'}
									<span class="lineItemPopup1 cursorPointer" data-popup="ServicesPopup" title="{vtranslate('Services',$MODULE)}" data-module-name="Services" data-field-name="serviceid">{Vtiger_Module_Model::getModuleIconPath('Services')}</span>
								{else}
									{if ($entityType eq 'Services') and (!$data.$productDeleted)}
										<span class="lineItemPopup1 cursorPointer" data-popup="ServicesPopup" title="{vtranslate('Services',$MODULE)}" data-module-name="Services" data-field-name="serviceid">{Vtiger_Module_Model::getModuleIconPath('Services')}</span>
									{elseif (!$data.$productDeleted)}
										<span class="lineItemPopup1 cursorPointer" data-popup="ProductsPopup" title="{vtranslate('Products',$MODULE)}" data-module-name="Products" data-field-name="productid">{Vtiger_Module_Model::getModuleIconPath('Products')}</span>
									{/if}
								{/if}
							</div>
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
					{if empty($data.$productName_other)}
						{vtranslate('LBL_THIS_LINE_ITEM_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_THIS_LINE_ITEM',$MODULE)}
					{else}
						{vtranslate('LBL_THIS',$MODULE)} {$entityType} {vtranslate('LBL_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_OR_REPLACE_THIS_ITEM',$MODULE)}
					{/if}
				</div>
			{/if}
		</td>
		<td>
			{if $COMMENT_EDITABLE}
				<div><textarea disabled id="{$comment}" name="{$comment}" class="lineItemCommentBox">{decode_html($data.$comment)}</textarea></div>
			{/if}
		</td>
	{/if}

	{if $QUANTITY_VIEWABLE}
		<td>
			<input id="{$qty_other}" name="{$qty_other}" type="text" class="qty smallInputBox inputElement"
				data-rule-required=true data-rule-positive=true data-rule-greater_than_zero=true value="{if !empty($data.$qty_other)}{$data.$qty_other}{else}1{/if}"
				disabled {if $QUANTITY_EDITABLE eq false} disabled=disabled {/if} />

			{if $PURCHASE_COST_EDITABLE eq false and $MODULE neq 'PurchaseOrder'}
				<input disabled id="{$purchaseCost}" type="hidden" value="{if ((float)$data.$purchaseCost)}{((float)$data.$purchaseCost) / ((float)$data.$qty)}{else}0{/if}" />
				<span style="display:none" class="purchaseCost">0</span>
				<input name="{$purchaseCost}" type="hidden" value="{if $data.$purchaseCost}{$data.$purchaseCost}{else}0{/if}" />
			{/if}
			{if $MARGIN_EDITABLE eq false}
				<input disabled type="hidden" name="{$margin}" value="{if $data.$margin}{$data.$margin}{else}0{/if}"></span>
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
	{/if}
	{assign var="dateFormat" value=$USER_MODEL->get('date_format')}
	{foreach key=LINEITEM_CUSTOM_FIELDKEY item=LINEITEM_CUSTOM_FIELD from=$LINEITEM_CUSTOM_FIELDS_OTHER}
		<td>
			{assign var="fieldName" value=$LINEITEM_CUSTOM_FIELD['fieldname']}
			{if  $LINEITEM_CUSTOM_FIELD['uitype'] eq '5'}
				<div class="input-group inputElement" style="margin-bottom: 3px">
					<input disabled id="{${$fieldName}}" name="{${$fieldName}}" type="text" class="dateField form-control" data-fieldname="{${$fieldName}}" data-fieldtype="date" data-date-format="{$dateFormat}"
						value="{Vtiger_Functions::currentUserDisplayDate($data.${$fieldName})}"
						{if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
						{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
						{if count($FIELD_INFO['validator'])}
							data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
						{/if}  data-rule-date="true" />
					<span class="input-group-addon"><i class="fa fa-calendar "></i></span>
				</div>
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '21' or $LINEITEM_CUSTOM_FIELD['uitype'] eq '2'}
				<div><textarea disabled id="{${$fieldName}}" name="{${$fieldName}}" class="">{decode_html($data.${$fieldName})}</textarea></div>
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '7' }
				<div><input disabled id="{${$fieldName}}" name="{${$fieldName}}" value="{decode_html($data.${$fieldName})}" class="qty inputElement" data-fieldname="{$fieldName}" style="min-width: 100px;"></input></div>
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '16'}
				<select disabled style="min-width: 150px;" id="{${$fieldName}}" class="select2-container select2 inputElement picklistfield lineitempicklistfield {${$fieldName}}" name="{${$fieldName}}" data-fieldname="{$fieldName}" data-rownum="{$row_no}" data-fieldtype="picklist">
					<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
					{foreach  key=PICKLIST_FIELDKEY item=PICKLIST_FIELD_ITEM from=$LINEITEM_CUSTOM_FIELD['picklistValues']}
						<option {if trim(decode_html($data.${$fieldName})) eq $PICKLIST_FIELDKEY} selected {/if} value="{$PICKLIST_FIELDKEY}">{$PICKLIST_FIELDKEY}</option>
					{/foreach}
				</select>
			{elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '56'}
				<input class="{$fieldName} inputElement" data-rownum="{$row_no}" id="{${$fieldName}}" name="{${$fieldName}}" style="width:15px;height:15px;" data-fieldnameig="{$fieldName}" data-fieldtype="checkbox" type="checkbox"
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