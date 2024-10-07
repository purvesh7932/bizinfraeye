{*********************************************************************************
* The content of this file is subject to the ITS4YouKanbanView license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************}

{strip}
    <div class="listViewEntriesDiv" style='overflow-x:auto;'>
        <table class="table table-bordered table-condensed listViewEntriesTable">
            <thead>
            <tr class="listViewHeaders">
                <th style="width:60px">
                    {vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}
                </th>
                <th>
                    {vtranslate('LBL_SOURCE_MODULE', $QUALIFIED_MODULE)}
                </th>
                <th>
                    {vtranslate('LBL_SOURCE_FIELD', $QUALIFIED_MODULE)}
                </th>
            </tr>
            </thead>
            <tbody>
            {foreach item=PICKLIST_MODULE_MODEL from=$PICKLIST_MODULES}
                {assign var=MODULE_ID value=getTabId($PICKLIST_MODULE_MODEL->getName())}
                {if array_key_exists($MODULE_ID, $SAVED_SETTINGS)}
                    {assign var=FIELD_MODEL value=$SAVED_SETTINGS[getTabId($PICKLIST_MODULE_MODEL->getName())]}
                    <tr class="listViewEntries" data-tabid="{$MODULE_ID}" data-fieldid="{$FIELD_MODEL->get('id')}"
                        data-editurl="index.php?module=ITS4YouKanbanView&parent=Settings&view=Edit&sourceModule={$PICKLIST_MODULE_MODEL->getName()}">
                        <td style="width:60px">
                            <div class="actions">
									<span class="actionImages" style="opacity: 1">
                                        <a class="deleteKanbanViewSettings">
                                            <i class="icon-trash alignMiddle" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"></i>
                                        </a>&nbsp;&nbsp;
                                        <a class="editKanbanViewSettings">
                                            <i class="icon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></i>
                                        </a>
									</span>
                            </div>
                        </td>
                        <td>
                            {vtranslate($PICKLIST_MODULE_MODEL->getName(), $PICKLIST_MODULE_MODEL->getName())}
                        </td>
                        <td>
                            {vtranslate($FIELD_MODEL->get('label'), $PICKLIST_MODULE_MODEL->getName())}
                        </td>
                    </tr>
                {/if}
            {/foreach}
            </tbody>
        </table>
        {if $COUNT_OF_TABLE_ELEMENTS eq 0}
            <table class="emptyRecordsDiv">
                <tbody>
                <tr>
                    <td>
                        {vtranslate('LBL_NO_SETTINGS', $QUALIFIED_MODULE)}
                    </td>
                </tr>
                </tbody>
            </table>
        {/if}
    </div>
    </div>
    </div>
{/strip}