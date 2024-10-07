{strip}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
{if (!$FIELD_NAME)}
  {assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}
{/if}
<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" class="inputElement" name="{$FIELD_NAME}"
value="{$FIELD_MODEL->get('fieldvalue')}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
{if count($FIELD_INFO['validator'])}
    data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
{/if}
 />
<div class="row">
  <div class="col-sm-12 col-md-12 col-lg-12">
    <span id="{$FIELD_MODEL->getFieldName()}_valid-msg" class="hide text-success">Phone Number is valid</span>
    <span id="{$FIELD_MODEL->getFieldName()}_error-msg" class="hide text-danger">Invalid number</span>
  </div>
</div>
{/strip}
