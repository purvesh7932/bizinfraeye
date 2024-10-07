{*********************************************************************************
* The content of this file is subject to the ITS4YouKanbanView license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************}

{strip}
    <br>
    <div class="row-fluid">
        <label class="fieldLabel span3"><strong>{vtranslate('LBL_SELECT_PICKLIST_IN',$QUALIFIED_MODULE)}&nbsp;{vtranslate($SELECTED_MODULE_NAME,$QUALIFIED_MODULE)}</strong></label>
        <div class="span6 fieldValue">
            <select class="chzn-select" id="modulePickList">
                <optgroup>
                    {foreach key=PICKLIST_FIELD item=FIELD_MODEL from=$PICKLIST_FIELDS}
                        <option value="{$FIELD_MODEL->getId()}" {if is_null($SAVED_FIELD) eq false && $SAVED_FIELD->getId() eq $FIELD_MODEL->getId()} selected {/if}>{vtranslate($FIELD_MODEL->get('label'),$SELECTED_MODULE_NAME)}</option>
                    {/foreach}
                </optgroup>
            </select>
        </div>
    </div>
    <br>
{/strip}
