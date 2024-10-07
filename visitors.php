<?php 

// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

$module = new Vtiger_Module();
$module->name = 'Visitors';//(No space in module name)
$module->save();

$module->initTables();
$module->initWebservice();

$menu = Vtiger_Menu::getInstance('MARKETING');
$menu->addModule($module);

$block1 = new Vtiger_Block();
$block1->label = 'Visitors Information';
$module->addBlock($block1); //to create a new block

$field0 = new Vtiger_Field();
$field0->name = 'visitor_name';
$field0->label = 'Name';
$field0->table = $module->basetable; 
$field0->column = 'visitor_name';
$field0->columntype = 'VARCHAR(100)';
$field0->uitype = 2;
$field0->typeofdata = 'V~O';
$module->setEntityIdentifier($field0); //to insert values in entity folder
$block1->addField($field0); //to add field in block

$field8 = new Vtiger_Field();
$field8->name = 'visitor_mobile';
$field8->label = 'Mobile No';
$field8->table = $module->basetable; 
$field8->column = 'visitor_mobile';
$field8->columntype = 'VARCHAR(100)';
$field8->uitype = 2;
$field8->typeofdata = 'V~O';
$block1->addField($field8); 

$field9 = new Vtiger_Field();
$field9->name = 'todays_date';
$field9->label= 'Todays Date';
$field9->table = $module->basetable; 
$field9->column = 'todays_date';
$field9->uitype = 5;
$field9->typeofdata = 'D~O';
$block1->addField($field9);

$field1 = new Vtiger_Field();
$field1->name = 'visitor_no';
$field1->label = 'visitor No';
$field1->table = $module->basetable; 
$field1->column = 'visitor_no';
$field1->columntype = 'VARCHAR(100)';
$field1->uitype = 4;
$field1->typeofdata = 'V~O';
$block1->addField($field1);

$mfield2 = new Vtiger_Field();
$mfield2->name = 'createdtime';
$mfield2->label= 'Created Time';
$mfield2->table = 'vtiger_crmentity';
$mfield2->column = 'createdtime';
$mfield2->uitype = 70;
$mfield2->typeofdata = 'DT~O';
$mfield2->displaytype= 2;
$block1->addField($mfield2);

$mfield3 = new Vtiger_Field();
$mfield3->name = 'modifiedtime';
$mfield3->label= 'Modified Time';
$mfield3->table = 'vtiger_crmentity';
$mfield3->column = 'modifiedtime';
$mfield3->uitype = 70;
$mfield3->typeofdata = 'DT~O';
$mfield3->displaytype= 2;
$block1->addField($mfield3);

//Do not change any value for filed2.
$field2 = new Vtiger_Field();
$field2->name = 'assigned_user_id';
$field2->label = 'Assigned To';
$field2->table = 'vtiger_crmentity'; 
$field2->column = 'smownerid';
$field2->columntype = 'int(19)';
$field2->uitype = 53;
$field2->typeofdata = 'V~M';
$block1->addField($field2);

$filter1 = new Vtiger_Filter();
$filter1->name = 'All';
$filter1->isdefault = true;
$module->addFilter($filter1);
// Add fields to the filter created
$filter1->addField($field0, 1);
$filter1->addField($field1, 2);
$filter1->addField($field2, 3);


/** Set sharing access of this module */
$module->setDefaultSharing('Private'); 
/** Enable and Disable available tools */
$module->enableTools(Array('Import', 'Export'));
$module->disableTools('Merge');