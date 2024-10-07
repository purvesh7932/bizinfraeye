{*********************************************************************************
* The content of this file is subject to the ITS4YouKanbanView license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************}

{strip}
    <div class="listViewPageDiv">
        <div class="listViewTopMenuDiv">
            <h3>{vtranslate('LBL_MODULE_NAME',$QUALIFIED_MODULE)}</h3>
            <hr>
            <div class="clearfix"></div>
        </div>
        <div class="listViewContentDiv" id="listViewContents" style="padding: 1%;">
            <br>
            <div class="row-fluid">
                <label class="fieldLabel span3"><strong>{vtranslate('LBL_SELECT_MODULE',$QUALIFIED_MODULE)} </strong></label>
                <div class="span6 fieldValue">
                    <select class="chzn-select" id="pickListModules">
                        <optgroup>
                            <option value="">{vtranslate('LBL_SELECT_OPTION',$QUALIFIED_MODULE)}</option>
                            {foreach item=PICKLIST_MODULE from=$PICKLIST_MODULES}
                                <option {if $SELECTED_MODULE_NAME eq $PICKLIST_MODULE->get('name')} selected="" {/if} value="{$PICKLIST_MODULE->get('name')}">{vtranslate($PICKLIST_MODULE->get('name'),$PICKLIST_MODULE->get('name'))}</option>
                            {/foreach}
                        </optgroup>
                    </select>
                </div>
            </div>
            <div id="modulePickListContainer">
                {include file="ModulePickListDetail.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
            </div>
            <br>
            <div id="modulePickListValuesContainer">
            </div>
        </div>
    </div>
{/strip}