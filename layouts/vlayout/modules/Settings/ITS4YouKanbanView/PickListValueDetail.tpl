{*********************************************************************************
* The content of this file is subject to the ITS4YouKanbanView license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************}

{strip}
    <ul class="nav nav-tabs massEditTabs" style="margin-bottom: 0;border-bottom: 0;">
        <li id="assignedToRoleTab"><a href="#AssignedToRoleLayout" data-toggle="tab"><strong>{vtranslate('LBL_VALUES_ASSIGNED_TO_PICKLIST',$QUALIFIED_MODULE)}</strong></a></li>
    </ul>
    <div class="tab-content layoutContent padding20 themeTableColor overflowVisible">
        <br>
        <div class="tab-pane active" id="AssignedToRoleLayout">
            <div class="row-fluid">
                <div class="span10 marginLeftZero textOverflowEllipsis">
                    <div class="row-fluid paddingTop20">
                        <select data-placeholder="{vtranslate('LBL_ADD_MENU_ITEM',$QUALIFIED_MODULE)}" id="menuListSelectElement" class="select2 span12" multiple="" name="role2picklist[]">
                            {if empty($ALL_PICKLIST_VALUES) } placeholder="{vtranslate("LS_NONE_PICKLIST_VALUES",$QUALIFIED_MODULE)}"{/if}>
                            {foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$ALL_PICKLIST_VALUES}
                                <option value="{$PICKLIST_VALUE}" data-id="{$PICKLIST_KEY}" {if in_array($PICKLIST_KEY,$SAVED_PICKLIST_VALUES)} selected {/if}>
                                    {vtranslate($PICKLIST_VALUE,$SELECTED_MODULE_NAME)}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
            <div class="row-fluid paddingTop20">
                <div class="span6">
                    <div class="pull-right">
                        <button class="btn btn-default" id="backLink" data-backurl="index.php?module=ITS4YouKanbanView&parent=Settings&view=List">
                            {vtranslate('LBL_BACK',$QUALIFIED_MODULE)}
                        </button>
                        &nbsp;&nbsp;
                        <button class="btn btn-success" id="saveOrder">
                            <strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}