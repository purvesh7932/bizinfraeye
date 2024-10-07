{strip}
{foreach item=LINEITEM_CUSTOM_FIELD from=$LINEITEM_CUSTOM_FIELDS_OTHER1}
	{assign var="fieldNameStyle" value=$LINEITEM_CUSTOM_FIELD['fieldname']}
	{if $LINEITEM_CUSTOM_FIELD['uitype'] eq '16'}
		<style>
			#s2id_{$fieldNameStyle}0 {
				display : none;
			}
		</style>
	{/if}
{/foreach}
{assign var=LINEITEM_FIELDS value=$RECORD_STRUCTURE['LBL_ITEM_DETAILS']}
{if $LINEITEM_FIELDS['image']}
	{assign var=IMAGE_EDITABLE value=$LINEITEM_FIELDS['image']->isEditable()}
{if $IMAGE_EDITABLE}{assign var=COL_SPAN1 value=($COL_SPAN1)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['productid']}
	{assign var=PRODUCT_EDITABLE value=$LINEITEM_FIELDS['productid']->isEditable()}
{if $PRODUCT_EDITABLE}{assign var=COL_SPAN1 value=($COL_SPAN1)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['quantity']}
	{assign var=QUANTITY_EDITABLE value=$LINEITEM_FIELDS['quantity']->isEditable()}
{if $QUANTITY_EDITABLE}{assign var=COL_SPAN1 value=($COL_SPAN1)+1}{/if}
{/if}
{assign var=QUANTITY_VIEWABLE value=true}
{if $SERVICEREPORTTYPE eq 'PRE-DELIVERY' or $SERVICEREPORTTYPE eq 'ERECTION AND COMMISSIONING'}
	{assign var=PRODUCT_EDITABLE value=false}
	{assign var=COL_SPAN1 value=($COL_SPAN1)-1}
	{assign var=QUANTITY_VIEWABLE value=false}
	{assign var=COL_SPAN1 value=($COL_SPAN1)-1}
{/if}
{if $REPORTTYPE eq 'WARRANTY CLAIM FOR SUB ASSEMBLY / OTHER SPARE PARTS'}
	{assign var=QUANTITY_VIEWABLE value=false}
	{assign var=COL_SPAN1 value=($COL_SPAN1)-1}
{/if}
{if $LINEITEM_FIELDS['purchase_cost']}
	{assign var=PURCHASE_COST_EDITABLE value=$LINEITEM_FIELDS['purchase_cost']->isEditable()}
{if $PURCHASE_COST_EDITABLE}{assign var=COL_SPAN2 value=($COL_SPAN2)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['listprice']}
	{assign var=LIST_PRICE_EDITABLE value=$LINEITEM_FIELDS['listprice']->isEditable()}
{if $LIST_PRICE_EDITABLE}{assign var=COL_SPAN2 value=($COL_SPAN2)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['margin']}
	{assign var=MARGIN_EDITABLE value=$LINEITEM_FIELDS['margin']->isEditable()}
{if $MARGIN_EDITABLE}{assign var=COL_SPAN3 value=($COL_SPAN3)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['comment']}
	{assign var=COMMENT_EDITABLE value=$LINEITEM_FIELDS['comment']->isEditable()}
{/if}
{if $LINEITEM_FIELDS['discount_amount']}
	{assign var=ITEM_DISCOUNT_AMOUNT_EDITABLE value=$LINEITEM_FIELDS['discount_amount']->isEditable()}
{/if}
{if $LINEITEM_FIELDS['discount_percent']}
	{assign var=ITEM_DISCOUNT_PERCENT_EDITABLE value=$LINEITEM_FIELDS['discount_percent']->isEditable()}
{/if}
{if $LINEITEM_FIELDS['hdnS_H_Percent']}
	{assign var=SH_PERCENT_EDITABLE value=$LINEITEM_FIELDS['hdnS_H_Percent']->isEditable()}
{/if}
{if $LINEITEM_FIELDS['hdnDiscountAmount']}
	{assign var=DISCOUNT_AMOUNT_EDITABLE value=$LINEITEM_FIELDS['hdnDiscountAmount']->isEditable()}
{/if}
{if $LINEITEM_FIELDS['hdnDiscountPercent']}
	{assign var=DISCOUNT_PERCENT_EDITABLE value=$LINEITEM_FIELDS['hdnDiscountPercent']->isEditable()}
{/if}

{assign var="FINAL" value=$RELATED_PRODUCTS_OTHER1.1.final_details}
{assign var="IS_INDIVIDUAL_TAX_TYPE" value=true}
{assign var="IS_GROUP_TAX_TYPE" value=false}

{if $TAX_TYPE eq 'individual'}
	{assign var="IS_GROUP_TAX_TYPE" value=false}
	{assign var="IS_INDIVIDUAL_TAX_TYPE" value=true}
{/if}

<input type="hidden" class="numberOfCurrencyDecimal" value="{$USER_MODEL->get('no_of_currency_decimals')}" />
<input type="hidden" name="totalProductCount2" id="totalProductCount2" value="{$row_no}" />
<input type="hidden" name="subtotal" id="subtotal" value="" />
<input type="hidden" name="total" id="total" value="" />
<input type="hidden" disabled="disabled" id="fildNamesOfCustPickFieldsInfoOther" value={ZEND_JSON::encode($LINEITEM_CUSTOM_OTHER_PICK_FIELDS)}>
<table id="lastPeriodialTable" class="table table-bordered fieldBlockContainer hide">
	<tbody></tbody>
</table>
<div name='editContent'>
	{assign var=LINE_ITEM_BLOCK_LABEL value="LBL_ITEM_DETAILS"}
	{assign var=BLOCK_FIELDS value=$RECORD_STRUCTURE.$LINE_ITEM_BLOCK_LABEL}
	{assign var=ITEMBLOCKNAMEANOTHER value='Contracts Avalibilty Values'}
	{if $BLOCK_FIELDS|@count gt 0 or 1 == 1}
		<div class='fieldBlockContainer' id="ShortagefieldBlockContainer" >
			<div class="row">
				<div class="col-sm-6">
					<h4 class='fieldBlockHeader' style="margin-top:5px;">{vtranslate($ITEMBLOCKNAMEANOTHER, $MODULE)}</h4>
				</div>
			</div>
			<div class="lineitemTableContainerValuesHolder">
				<table class="table table-bordered hide" id="lineItemTab2">
					<thead>
						{foreach key=LINEITEM_CUSTOM_FIELDKEY item=LINEITEM_CUSTOM_FIELD from=$LINEITEM_CUSTOM_FIELDS_OTHER1}
							<td data-td="{$LINEITEM_CUSTOM_FIELD['fieldname']}">
								<strong>{vtranslate($LINEITEM_CUSTOM_FIELD['fieldlabel'],$MODULE)}</strong>
							</td>
						{/foreach}
					</thead>
					<tbody class="appendRows">
						<tr id="row" class="hide lineItemCloneCopy" data-row-num="0">
							{include file="partials/LineItemsContent2.tpl"|@vtemplate_path:'Equipment' row_no=0 data=[] IGNORE_UI_REGISTRATION=true}
						</tr>
						{if count($RELATED_PRODUCTS_OTHER1) gt 0}
							{foreach key=row_no item=data from=$RELATED_PRODUCTS_OTHER1}
								<tr id="row{$row_no}" data-row-num="{$row_no}" class="lineItemRow" {if $data["entityType$row_no"] eq 'Products'}data-quantity-in-stock={$data["qtyInStock$row_no"]}{/if}>
									{include file="partials/LineItemsContent2.tpl"|@vtemplate_path:'Equipment' row_no=$row_no data=$data}
								</tr>
							{/foreach}
						{/if}
					</tbody>
				</table>
			</div>
			<div class="lineitemTableContainer">
				<table class="table table-bordered" id="lineItemTab2">
					<thead>
						{foreach key=LINEITEM_CUSTOM_FIELDKEY item=LINEITEM_CUSTOM_FIELD from=$LINEITEM_CUSTOM_FIELDS_OTHER1}
							<td data-td="{$LINEITEM_CUSTOM_FIELD['fieldname']}">
								<strong>{vtranslate($LINEITEM_CUSTOM_FIELD['fieldlabel'],$MODULE)}</strong>
							</td>
						{/foreach}
					</thead>
					<tbody class="appendRows">
						<tr id="row" class="lineItemCloneCopy" data-row-num="0">
							{include file="partials/LineItemsContent2.tpl"|@vtemplate_path:'Equipment' tabletdhiderVal= true row_no=0 data=[] IGNORE_UI_REGISTRATION=true}
						</tr>
						{if count($RELATED_PRODUCTS_OTHER1) gt 0}
							{foreach key=row_no item=data from=$RELATED_PRODUCTS_OTHER1}
								<tr id="row{$row_no}" data-row-num="{$row_no}" class="lineItemRow" {if $data["entityType$row_no"] eq 'Products'}data-quantity-in-stock={$data["qtyInStock$row_no"]}{/if}>
									{include file="partials/LineItemsContent2.tpl"|@vtemplate_path:'Equipment' row_no=$row_no data=$data}
								</tr>
							{/foreach}
						{/if}
					</tbody>
				</table>
			</div>
		</div>
	{/if}
</div>