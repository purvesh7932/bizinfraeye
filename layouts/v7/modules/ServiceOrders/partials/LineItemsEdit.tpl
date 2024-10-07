{strip}
{foreach item=LINEITEM_CUSTOM_FIELD from=$LINEITEM_CUSTOM_FIELDS}
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

{assign var="FINAL" value=$RELATED_PRODUCTS.1.final_details}
{assign var="IS_INDIVIDUAL_TAX_TYPE" value=true}
{assign var="IS_GROUP_TAX_TYPE" value=false}

{if $TAX_TYPE eq 'individual'}
	{assign var="IS_GROUP_TAX_TYPE" value=false}
	{assign var="IS_INDIVIDUAL_TAX_TYPE" value=true}
{/if}

<input type="hidden" class="numberOfCurrencyDecimal" value="{$USER_MODEL->get('no_of_currency_decimals')}" />
<input type="hidden" name="totalProductCount" id="totalProductCount" value="{$row_no}" />
<input type="hidden" name="subtotal" id="subtotal" value="" />
<input type="hidden" name="total" id="total" value="" />

<div name='editContent' style="padding-top: 30px;">
	{assign var=LINE_ITEM_BLOCK_LABEL value="LBL_ITEM_DETAILS"}
	{assign var=BLOCK_FIELDS value=$RECORD_STRUCTURE.$LINE_ITEM_BLOCK_LABEL}
	{assign var=BLOCK_LABEL value=$LINE_ITEM_BLOCK_LABEL}
	{if $BLOCK_FIELDS|@count gt 0}
		<div class='fieldBlockContainer'>
			<div class="row">
				<div class="col-sm-3">
					<h4 class='fieldBlockHeader' style="margin-top:5px;">{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
				</div>
				<div style="display:none" class="col-sm-9 well">
					<div class="row">
						<div class="col-sm-4">
							{if $LINEITEM_FIELDS['region_id'] && $LINEITEM_FIELDS['region_id']->isEditable()}
								<span class="pull-right">
									<i class="fa fa-info-circle"></i>&nbsp;
									<label>{vtranslate($LINEITEM_FIELDS['region_id']->get('label'), $MODULE)}</label>&nbsp;
									<select class="select2" id="region_id" name="region_id" style="width: 164px;">
										<option value="0" data-info="{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($DEFAULT_TAX_REGION_INFO))}">{vtranslate('LBL_SELECT_OPTION', $MODULE)}</option>
										{foreach key=TAX_REGION_ID item=TAX_REGION_INFO from=$TAX_REGIONS}
											<option value="{$TAX_REGION_ID}" data-info='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($TAX_REGION_INFO))}' {if $TAX_REGION_ID eq $RECORD->get('region_id')}selected{/if}>{$TAX_REGION_INFO['name']}</option>
										{/foreach}
									</select>
									<input type="hidden" id="prevRegionId" value="{$RECORD->get('region_id')}" />
									<a class="fa fa-wrench hidden-xs" href="index.php?module=Vtiger&parent=Settings&view=TaxIndex" target="_blank" style="vertical-align:middle;"></a>
										</span>
							{/if}
						</div>
						<div class="col-sm-4">
							<div class="pull-right">
								<i class="fa fa-info-circle"></i>&nbsp;
								<label>{vtranslate('LBL_CURRENCY',$MODULE)}</label>&nbsp;
								{assign var=SELECTED_CURRENCY value=$CURRENCINFO}
								{* Lookup the currency information if not yet set - create mode *}
								{if $SELECTED_CURRENCY eq ''}
									{assign var=USER_CURRENCY_ID value=$USER_MODEL->get('currency_id')}
									{foreach item=currency_details from=$CURRENCIES}
										{if $currency_details.curid eq $USER_CURRENCY_ID}
											{assign var=SELECTED_CURRENCY value=$currency_details}
										{/if}
									{/foreach}
								{/if}

								<select class="select2" id="currency_id" name="currency_id" style="width: 150px;">
									{foreach item=currency_details key=count from=$CURRENCIES}
										<option value="{$currency_details.curid}" class="textShadowNone" data-conversion-rate="{$currency_details.conversionrate}" {if $SELECTED_CURRENCY.currency_id eq $currency_details.curid} selected {/if}>
											{$currency_details.currencylabel|@getTranslatedCurrencyString} ({$currency_details.currencysymbol})
										</option>
									{/foreach}
								</select>

								{assign var="RECORD_CURRENCY_RATE" value=$RECORD_STRUCTURE_MODEL->getRecord()->get('conversion_rate')}
								{if $RECORD_CURRENCY_RATE eq ''}
									{assign var="RECORD_CURRENCY_RATE" value=$SELECTED_CURRENCY.conversionrate}
								{/if}
								<input type="hidden" name="conversion_rate" id="conversion_rate" value="{$RECORD_CURRENCY_RATE}" />
								<input type="hidden" value="{$SELECTED_CURRENCY.currency_id}" id="prev_selected_currency_id" />
								<!-- TODO : To get default currency in even better way than depending on first element -->
								<input type="hidden" id="default_currency_id" value="{$CURRENCIES.0.curid}" />
								<input type="hidden" value="{$SELECTED_CURRENCY.currency_id}" id="selectedCurrencyId" />
							</div>
						</div>
						<div class="col-sm-4">
							<div class="pull-right">
								<i class="fa fa-info-circle"></i>&nbsp;
								<label>{vtranslate('LBL_TAX_MODE',$MODULE)}</label>&nbsp;
								<select class="select2 lineItemTax" id="taxtype" name="taxtype" style="width: 150px;">
									<option value="individual" {if $IS_INDIVIDUAL_TAX_TYPE}selected{/if}>{vtranslate('LBL_INDIVIDUAL', $MODULE)}</option>
									<option value="group" {if $IS_GROUP_TAX_TYPE}selected{/if}>{vtranslate('LBL_GROUP', $MODULE)}</option>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="lineitemTableContainer">
				<table class="table table-bordered" id="lineItemTab">
					<tr>
						<td><strong>{vtranslate('LBL_TOOLS',$MODULE)}</strong></td>
						{if $IMAGE_EDITABLE}
							<td>
								<strong>{vtranslate({$LINEITEM_FIELDS['image']->get('label')},$MODULE)}</strong>
							</td>
						{/if}
						{if $PRODUCT_EDITABLE}
							<td>
								<span class="redColor">*</span><strong>{vtranslate({$LINEITEM_FIELDS['productid']->get('label')},$MODULE)}</strong>
							</td>
						{/if}
						{if $PRODUCT_EDITABLE}
							<td>
								<strong>{vtranslate('Description',$MODULE)}</strong>
							</td>
						{/if}
						<td>
							<strong>{vtranslate('LBL_QTY',$MODULE)}</strong>
						</td>
						{foreach key=LINEITEM_CUSTOM_FIELDKEY item=LINEITEM_CUSTOM_FIELD from=$LINEITEM_CUSTOM_FIELDS}
							{if $LINEITEM_CUSTOM_FIELD['fieldlabel'] neq 'Mark as Important to Collect immediately'}
								<td>
									<strong>{vtranslate($LINEITEM_CUSTOM_FIELD['fieldlabel'],$MODULE)}</strong>
								</td>
							{/if}
						{/foreach}
					</tr>
					<tr id="row0" class="hide lineItemCloneCopy" data-row-num="0">
						{include file="partials/LineItemsContent.tpl"|@vtemplate_path:'ServiceOrders' row_no=0 data=[] IGNORE_UI_REGISTRATION=true}
					</tr>
					{if count($RELATED_PRODUCTS) gt 0}
						{foreach key=row_no item=data from=$RELATED_PRODUCTS}
							<tr id="row{$row_no}" data-row-num="{$row_no}" class="lineItemRow" {if $data["entityType$row_no"] eq 'Products'}data-quantity-in-stock={$data["qtyInStock$row_no"]}{/if}>
								{include file="partials/LineItemsContent.tpl"|@vtemplate_path:'ServiceOrders' row_no=$row_no data=$data}
							</tr>
						{/foreach}
					{/if}
					{* {if count($RELATED_PRODUCTS) eq 0 and ($PRODUCT_ACTIVE eq 'true' || $SERVICE_ACTIVE eq 'true')}
						<tr id="row1" class="lineItemRow" data-row-num="1">
							{include file="partials/LineItemsContent.tpl"|@vtemplate_path:'ServiceOrders' row_no=1 data=[] IGNORE_UI_REGISTRATION=false}
						</tr>
					{/if} *}
				</table>
			</div>
		</div>
	{/if}
</div>
<br>
<div>
	<div>
		{if $PRODUCT_ACTIVE eq 'true' && $SERVICE_ACTIVE eq 'true'}
			<div class="btn-toolbar">
				<span class="btn-group">
					<button type="button" class="btn btn-default" id="addProduct" data-module-name="Products" >
						<i class="fa fa-plus"></i>&nbsp;&nbsp;<strong>{vtranslate('LBL_ADD_PRODUCT',$MODULE)}</strong>
					</button>
				</span>
				<span class="btn-group">
					<button type="button" class="btn btn-default" id="addService" data-module-name="Services" >
						<i class="fa fa-plus"></i>&nbsp;&nbsp;<strong>{vtranslate('LBL_ADD_SERVICE',$MODULE)}</strong>
					</button>
				</span>
			</div>
		{elseif $PRODUCT_ACTIVE eq 'true'}
			<div class="btn-group">
				<button type="button" class="btn btn-default" id="addProduct" data-module-name="Products">
					<i class="fa fa-plus"></i><strong>&nbsp;&nbsp;{vtranslate('LBL_ADD_PRODUCT',$MODULE)}</strong>
				</button>
			</div>
		{elseif $SERVICE_ACTIVE eq 'true'}
			<div class="btn-group">
				<button type="button" class="btn btn-default" id="addService" data-module-name="Services">
					<i class="fa fa-plus"></i><strong>&nbsp;&nbsp;{vtranslate('LBL_ADD_SERVICE',$MODULE)}</strong>
				</button>
			</div>
		{/if}
	</div>
</div>
<br>
