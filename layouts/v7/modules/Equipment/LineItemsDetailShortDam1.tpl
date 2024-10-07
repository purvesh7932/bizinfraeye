<input type="hidden" class="isCustomFieldExists" value="false">
{assign var=FINAL_DETAILS value=$RELATED_PRODUCTS_OTHER1.1.final_details}
<div class="details block">
    <div class="lineItemTableDiv">
        <table class="table table-bordered lineItemsTable" style = "margin-top:15px">
            <thead>
            <th colspan="{$COL_SPAN1}" class="lineItemBlockHeader">
                {assign var=REGION_LABEL value=vtranslate('daadcp_lineblock', $MODULE_NAME)}
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
                    {foreach key=LINEITEM_CUSTOM_FIELDKEY item=LINEITEM_CUSTOM_FIELD from=$LINEITEM_CUSTOM_FIELDS_OTHER1}
                        <td data-td="{$LINEITEM_CUSTOM_FIELD['fieldname']}">
                            <strong>{vtranslate($LINEITEM_CUSTOM_FIELD['fieldlabel'],$MODULE)}</strong>
                        </td>
                    {/foreach}
                </tr>
                {foreach key=INDEX item=LINE_ITEM_DETAIL from=$RELATED_PRODUCTS_OTHER1}
                    <tr>
                        {foreach key=LINEITEM_CUSTOM_FIELDKEY item=LINEITEM_CUSTOM_FIELD from=$LINEITEM_CUSTOM_FIELDS_OTHER1}
		                    {assign var="fieldName" value=$LINEITEM_CUSTOM_FIELD['fieldname']}
                            <td data-td="{$fieldName}">
                                {if $LINEITEM_CUSTOM_FIELD['uitype'] eq '5'}
                                    {Vtiger_Functions::currentUserDisplayDate($LINE_ITEM_DETAIL["$fieldName$INDEX"])}
                                {elseif $LINEITEM_CUSTOM_FIELD['uitype'] eq '56'}
                                    {if $LINE_ITEM_DETAIL["$fieldName$INDEX"] eq 1} Yes {else} No {/if}
                                {else}
                                    {$LINE_ITEM_DETAIL["$fieldName$INDEX"]}
                                {/if}
                            </td>
                        {/foreach}
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>