{assign var=ITEM_DETAILS_BLOCK value=$BLOCK_LIST['LBL_ITEM_DETAILS']}
{assign var=LINEITEM_FIELDS value=$ITEM_DETAILS_BLOCK->getFields()}
{assign var=COL_SPAN1 value=0}
{assign var=COL_SPAN2 value=0}
{assign var=COL_SPAN3 value=2}
{assign var=IMAGE_VIEWABLE value=false}
{assign var=PRODUCT_VIEWABLE value=false}
{assign var=QUANTITY_VIEWABLE value=false}
{assign var=PURCHASE_COST_VIEWABLE value=false}
{assign var=LIST_PRICE_VIEWABLE value=false}
{assign var=MARGIN_VIEWABLE value=false}
{assign var=COMMENT_VIEWABLE value=false}
{assign var=ITEM_DISCOUNT_AMOUNT_VIEWABLE value=false}
{assign var=ITEM_DISCOUNT_PERCENT_VIEWABLE value=false}
{assign var=SH_PERCENT_VIEWABLE value=false}
{assign var=DISCOUNT_AMOUNT_VIEWABLE value=false}
{assign var=DISCOUNT_PERCENT_VIEWABLE value=false}
{assign var=LAST_COL_SPAN value=1}
{assign var=AVAIL_COL value=0}
{if $LINEITEM_FIELDS['image']}
    {assign var=IMAGE_VIEWABLE value=$LINEITEM_FIELDS['image']->isViewable()}
{if $IMAGE_VIEWABLE}{assign var=COL_SPAN1 value=($COL_SPAN1)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['productid']}
    {assign var=PRODUCT_VIEWABLE value=$LINEITEM_FIELDS['productid']->isViewable()}
{if $PRODUCT_VIEWABLE}{assign var=COL_SPAN1 value=($COL_SPAN1)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['quantity']}
    {assign var=QUANTITY_VIEWABLE value=$LINEITEM_FIELDS['quantity']->isViewable()}
{if $QUANTITY_VIEWABLE}{assign var=COL_SPAN1 value=($COL_SPAN1)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['purchase_cost']}
    {assign var=PURCHASE_COST_VIEWABLE value=$LINEITEM_FIELDS['purchase_cost']->isViewable()}
{if $PURCHASE_COST_VIEWABLE}{assign var=COL_SPAN2 value=($COL_SPAN2)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['listprice']}
    {assign var=LIST_PRICE_VIEWABLE value=$LINEITEM_FIELDS['listprice']->isViewable()}
{if $LIST_PRICE_VIEWABLE}{assign var=COL_SPAN2 value=($COL_SPAN2)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['margin']}
    {assign var=MARGIN_VIEWABLE value=$LINEITEM_FIELDS['margin']->isViewable()}
{if $MARGIN_VIEWABLE}{assign var=COL_SPAN3 value=($COL_SPAN3)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['comment']}
    {assign var=COMMENT_VIEWABLE value=$LINEITEM_FIELDS['comment']->isViewable()}
{/if}
{if $LINEITEM_FIELDS['discount_amount']}
    {assign var=ITEM_DISCOUNT_AMOUNT_VIEWABLE value=$LINEITEM_FIELDS['discount_amount']->isViewable()}
{/if}
{if $LINEITEM_FIELDS['discount_percent']}
    {assign var=ITEM_DISCOUNT_PERCENT_VIEWABLE value=$LINEITEM_FIELDS['discount_percent']->isViewable()}
{/if}
{if $LINEITEM_FIELDS['hdnS_H_Percent']}
    {assign var=SH_PERCENT_VIEWABLE value=$LINEITEM_FIELDS['hdnS_H_Percent']->isViewable()}
{/if}
{if $LINEITEM_FIELDS['hdnDiscountAmount']}
    {assign var=DISCOUNT_AMOUNT_VIEWABLE value=$LINEITEM_FIELDS['hdnDiscountAmount']->isViewable()}
{/if}
{if $LINEITEM_FIELDS['hdnDiscountPercent']}
    {assign var=DISCOUNT_PERCENT_VIEWABLE value=$LINEITEM_FIELDS['hdnDiscountPercent']->isViewable()}
{/if}

<input type="hidden" class="isCustomFieldExists" value="false">
<input type="hidden" id="SO_CREATE_URL" value={ZEND_JSON::encode($SO_CREATE_URL)}>
{assign var=FINAL_DETAILS value=$RELATED_PRODUCTS.1.final_details}
<div class="details block">
    <div class="lineItemTableDiv">
        <table class="table table-bordered lineItemsTable" style = "margin-top:15px">
            <thead>
            <th colspan="{$COL_SPAN1}" class="lineItemBlockHeader">
                {assign var=REGION_LABEL value=vtranslate('LBL_ITEM_DETAILS', $MODULE_NAME)}
                {if $RECORD->get('region_id') && $LINEITEM_FIELDS['region_id'] && $LINEITEM_FIELDS['region_id']->isViewable()}
                    {assign var=TAX_REGION_MODEL value=Inventory_TaxRegion_Model::getRegionModel($RECORD->get('region_id'))}
                    {if $TAX_REGION_MODEL}
                        {assign var=REGION_LABEL value="{vtranslate($LINEITEM_FIELDS['region_id']->get('label'), $MODULE_NAME)} : {$TAX_REGION_MODEL->getName()}"}
                    {/if}
                {/if}
                {$REGION_LABEL}
            </th>
            </thead>
            <tbody>
                <tr>
                    {if $IMAGE_VIEWABLE}
                        <td class="lineItemFieldName">
                            {assign var=LAST_COL_SPAN value=($LAST_COL_SPAN)+1}
                            <strong>{vtranslate({$LINEITEM_FIELDS['image']->get('label')},$MODULE)}</strong>
                        </td>
                    {/if}
                    {if $PRODUCT_VIEWABLE}
                        <td class="lineItemFieldName">
                            {assign var=LAST_COL_SPAN value=($LAST_COL_SPAN)+1}
                            <span class="redColor">*</span><strong>{vtranslate({$LINEITEM_FIELDS['productid']->get('label')},$MODULE_NAME)}</strong>
                        </td>
                    {/if}
                     {if $PRODUCT_VIEWABLE}
                        <td class="lineItemFieldName">
                            {assign var=LAST_COL_SPAN value=($LAST_COL_SPAN)+1}
                            <strong>{vtranslate('Description',$MODULE_NAME)}</strong>
                        </td>
                    {/if}
                    {if $QUANTITY_VIEWABLE}
                        <td class="lineItemFieldName">
                            {assign var=LAST_COL_SPAN value=($LAST_COL_SPAN)+1}
                            <strong>{vtranslate({$LINEITEM_FIELDS['quantity']->get('label')},$MODULE_NAME)}</strong>
                        </td>
                    {/if}
                    {foreach key=LINEITEM_CUSTOM_FIELDKEY item=LINEITEM_CUSTOM_FIELD from=$LINEITEM_CUSTOM_FIELDS}
                        <td>
                            {assign var=LAST_COL_SPAN value=($LAST_COL_SPAN)+1}
                            <strong>{vtranslate($LINEITEM_CUSTOM_FIELD['fieldlabel'],$MODULE)}</strong>
                        </td>
                    {/foreach}
                    <td>
                        <strong>Select For SalesOrder Creation</strong>
                    </td>
                </tr>
                {foreach key=INDEX item=LINE_ITEM_DETAIL from=$RELATED_PRODUCTS}
                    <tr>
                        {if $IMAGE_VIEWABLE}
                            <td style="text-align:center;">
                                <img src='{$LINE_ITEM_DETAIL["productImage$INDEX"]}' height="42" width="42">
                            </td>
                        {/if}

                        {if $PRODUCT_VIEWABLE}
                            <td>
                                <div>
                                    {if $LINE_ITEM_DETAIL["productDeleted$INDEX"]}
                                        {$LINE_ITEM_DETAIL["productName$INDEX"]}
                                    {else}
                                        <h5><a class="fieldValue" href="index.php?module={$LINE_ITEM_DETAIL["entityType$INDEX"]}&view=Detail&record={$LINE_ITEM_DETAIL["hdnProductId$INDEX"]}" target="_blank">{$LINE_ITEM_DETAIL["productName$INDEX"]}</a></h5>
                                        {/if}
                                </div>
                                {if $LINE_ITEM_DETAIL["productDeleted$INDEX"]}
                                    <div class="redColor deletedItem">
                                        {if empty($LINE_ITEM_DETAIL["productName$INDEX"])}
                                            {vtranslate('LBL_THIS_LINE_ITEM_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_THIS_LINE_ITEM',$MODULE)}
                                        {else}
                                            {vtranslate('LBL_THIS',$MODULE)} {$LINE_ITEM_DETAIL["entityType$INDEX"]} {vtranslate('LBL_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_OR_REPLACE_THIS_ITEM',$MODULE)}
                                        {/if}
                                    </div>
                                {/if}
                                <div>
                                    {$LINE_ITEM_DETAIL["subprod_names$INDEX"]}
                                </div>
                            </td>
                        {/if}
                        {if $COMMENT_VIEWABLE && !empty($LINE_ITEM_DETAIL["productName$INDEX"])}
                            <td>
                                {decode_html($LINE_ITEM_DETAIL["comment$INDEX"])|nl2br}
                            </td>
                        {/if}
                        {if $QUANTITY_VIEWABLE}
                            <td>
                                {$LINE_ITEM_DETAIL["qty$INDEX"]}
                            </td>
                        {/if}
                        {foreach key=LINEITEM_CUSTOM_FIELDKEY item=LINEITEM_CUSTOM_FIELD from=$LINEITEM_CUSTOM_FIELDS}
		                    {assign var="fieldName" value=$LINEITEM_CUSTOM_FIELD['fieldname']}
                            <td id="{$fieldName}">
                                {if  $LINEITEM_CUSTOM_FIELD['uitype'] eq '5'}
                                    {Vtiger_Functions::IGcurrentUserDisplayDate($LINE_ITEM_DETAIL["$fieldName$INDEX"])}
                                {elseif $LINEITEM_CUSTOM_FIELD['fieldname'] eq 'tot_excluded_qty_rem'}
                                    {if $LINE_ITEM_DETAIL["$fieldName$INDEX"]}
                                        {assign var=vals value=ZEND_JSON::decode($LINE_ITEM_DETAIL["$fieldName$INDEX"])}
                                        {assign var=valsArr value=$vals['val']}
                                        <button type="button" id="excludeRem{$INDEX}" onclick='handleHideAndShow("excludeRem{$INDEX}")' class="collapsible">Show Details</button>
                                        <table class="table table-bordered lineItemsTable" style="min-width: 200px;display : none">
                                            <thead>
                                                <th>Quantiy</th>
                                                <th>Remarks</th>
                                            </thead>
                                            {foreach key=VALKEY item=VALVALUE  from=$valsArr}
                                                <tr>
                                                    <td>{$VALVALUE['quantity']}</td>
                                                    <td>{$VALVALUE['remarks']}</td>
                                                </tr>
                                            {/foreach}
                                        </table>
                                    {/if}
                                {elseif $LINEITEM_CUSTOM_FIELD['fieldname'] eq 'submitted_qty_log'}
                                    {if $LINE_ITEM_DETAIL["$fieldName$INDEX"]}
                                        {assign var=vals value=ZEND_JSON::decode($LINE_ITEM_DETAIL["$fieldName$INDEX"])}
                                        {assign var=valsArr value=$vals['val']}
                                        <button type="button" class="collapsible" id="subMittedRem{$INDEX}" onclick='handleHideAndShow("subMittedRem{$INDEX}")' style="width:100px;">Show Details</button>
                                        <table class="table table-bordered lineItemsTable" style="min-width:200px;display:none">
                                            <thead>
                                                <th>Date Of Submission</th>
                                                <th>Quantiy</th>
                                            </thead>
                                            {foreach key=VALKEY item=VALVALUE  from=$valsArr}
                                                <tr>
                                                    <td>{$VALVALUE['date_of_submiss']}</td>
                                                    <td>{$VALVALUE['quantity']}</td>
                                                </tr>
                                            {/foreach}
                                        </table>
                                    {/if}
                                {elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '56'}
                                    {if $LINE_ITEM_DETAIL["$fieldName$INDEX"] eq 1} Yes {else} No {/if}
                                {else}
                                    {$LINE_ITEM_DETAIL["$fieldName$INDEX"]}
                                {/if}
                            </td>
                        {/foreach}
                        <td>
                            {assign var=VAL_OF_LINE_QTY_RCDV value=(int)$LINE_ITEM_DETAIL["so_creatable_qty$INDEX"]}
                            {if $VAL_OF_LINE_QTY_RCDV gt 0}
                                {assign var=AVAIL_COL value=($AVAIL_COL)+1}
                                <input type="checkbox" class="selectedIds" name="selectedIds" value="{$LINE_ITEM_DETAIL["igLineItemId$INDEX"]}">
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                {if $AVAIL_COL gt 0}
                    <tr>
                        <td colspan="{$LAST_COL_SPAN}" align="right">
                            <button style="width:135px !important" type="button" onclick="FailedParts_Detail_Js.handleREdirectionAndCReation()" id="createSalesOrderButton" 
                            class="collapsible">
                                Create SalesOrder
                            </button>
                        </td>
                    </tr>
                {/if}
            </tbody>
        </table>
    </div>
</div>