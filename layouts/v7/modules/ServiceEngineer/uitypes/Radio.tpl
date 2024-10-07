{strip}
    {assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
    {assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
    {assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
    {assign var=PICKLIST_COLORS value=$FIELD_INFO['picklistColors']}
    <div id="paymentContainer" style="margin : 0px" name="paymentContainer" class="paymentOptions">
        {foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
            <div id="payCC" class="floatBlock">
            <label> <input id="{$FIELD_MODEL->getFieldName()}{Vtiger_Util_Helper::replaceSpaceWithUnderScores(Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME))}" {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if} data-fieldname="{$FIELD_MODEL->getFieldName()}" name="{$FIELD_MODEL->getFieldName()}" type="radio" value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if trim(decode_html($FIELD_MODEL->get('fieldvalue'))) eq trim($PICKLIST_NAME)} checked="checked" {/if}>
            &nbsp {$PICKLIST_VALUE}</label>
            </div>
        {/foreach}
    </div>
    <style type="text/css">
        label {
            display: block;
            color: #7d7d7d;
        }
        .floatBlock {
            margin: 0 1.81em 0 0;
        }
        .labelish {
            color:#7d7d7d;
            margin: 0;
        }
        .paymentOptions {
            border: none;
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            break-before: always;
            margin: 0 0 3em 0;
        }
        #purchaseOrder {
            margin: 0 0 2em 0;
        }
	</style>
{/strip}