{*********************************************************************************
* The content of this file is subject to the ITS4YouKanbanView license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************}

{strip}
    <div class="container-fluid">
    <div class="widget_header row-fluid">
        <h3>{vtranslate('LBL_MODULE_NAME', $QUALIFIED_MODULE)}</h3>
    </div>
    <hr>
    <div class="row-fluid">
		<span class="span8 btn-toolbar">
			<button class="btn addButton addKanbanSettings" data-url="index.php?module=ITS4YouKanbanView&parent=Settings&view=Edit">
				<i class="icon-plus"></i>&nbsp;
				<strong>{vtranslate('LBL_ADD_NEW_KANBAN_SETTINGS', $QUALIFIED_MODULE)}</strong>
			</button>
		</span>
    </div>
    <div class="clearfix"></div>
    <div class="listViewContentDiv" id="indexViewContent">
{/strip}