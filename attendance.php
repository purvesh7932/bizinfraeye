<?php 

// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

$module = new Vtiger_Module();
$module->name = 'Attendance'; // (No space in module name)
$module->save();

$module->initTables();
$module->initWebservice();

$menu = Vtiger_Menu::getInstance('MARKETING');
$menu->addModule($module);

$block1 = new Vtiger_Block();
$block1->label = 'Attendance Information';
$module->addBlock($block1); // to create a new block

$field0 = new Vtiger_Field();
$field0->name = 'emp_name';
$field0->label = 'Name';
$field0->table = $module->basetable; 
$field0->column = 'emp_name';
$field0->columntype = 'VARCHAR(100)';
$field0->uitype = 2;
$field0->typeofdata = 'V~O';
$module->setEntityIdentifier($field0); // to insert values in entity folder
$block1->addField($field0); // to add field in block

$field4 = new Vtiger_Field();
$field4->name = 'atten_signindate';
$field4->label = 'Sign In Date';
$field4->table = $module->basetable; 
$field4->column = 'atten_signin';
$field4->columntype = 'DATE';
$field4->uitype = 5;
$field4->typeofdata = 'D~O';
$block1->addField($field4);

$field5 = new Vtiger_Field();
$field5->name = 'atten_signintime';
$field5->label = 'Sign in Time';
$field5->table = $module->basetable; 
$field5->column = 'atten_signintime';
$field5->columntype = 'TIME';
$field5->uitype = 14;
$field5->typeofdata = 'T~O';
$block1->addField($field5);

$field6 = new Vtiger_Field();
$field6->name = 'atten_breakintime';
$field6->label = 'Break In Time';
$field6->table = $module->basetable; 
$field6->column = 'atten_breakintime';
$field6->columntype = 'TIME';
$field6->uitype = 14;
$field6->typeofdata = 'T~O';
$block1->addField($field6); 

$field7 = new Vtiger_Field();
$field7->name = 'atten_breakouttime';
$field7->label= 'Break Out Time';
$field7->table = $module->basetable; 
$field7->column = 'atten_breakouttime';
$field7->columntype = 'TIME';
$field7->uitype = 14;
$field7->typeofdata = 'T~O';
$block1->addField($field7);

$field8 = new Vtiger_Field();
$field8->name = 'atten_signoutdate';
$field8->label= 'Sign Out Date';
$field8->table = $module->basetable; 
$field8->column = 'atten_signoutdate';
$field8->columntype = 'DATE';
$field8->uitype = 5;
$field8->typeofdata = 'D~O';
$block1->addField($field8);

$field9 = new Vtiger_Field();
$field9->name = 'atten_signouttime';
$field9->label = 'Sign Out Time';
$field9->table = $module->basetable; 
$field9->column = 'atten_signouttime';
$field9->columntype = 'TIME';
$field9->uitype = 14;
$field9->typeofdata = 'T~O';
$block1->addField($field9);

$field10 = new Vtiger_Field();
$field10->name = 'atten_workedhour';
$field10->label = 'Working Hours';
$field10->table = $module->basetable; 
$field10->column = 'atten_workedhour';
$field10->columntype = 'VARCHAR(100)';
$field10->uitype = 2;
$field10->typeofdata = 'V~O';
$block1->addField($field10);

$field1 = new Vtiger_Field();
$field1->name = 'atten_no';
$field1->label = 'Attendance No';
$field1->table = $module->basetable; 
$field1->column = 'atten_no';
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

// Do not change any value for field2.
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
$filter1->addField($field4, 2);
$filter1->addField($field5, 3);
$filter1->addField($field6, 4);
$filter1->addField($field7, 5);
$filter1->addField($field8, 6);
$filter1->addField($field9, 7);
$filter1->addField($field1, 8);
$filter1->addField($field2, 9);

/** Set sharing access of this module */
$module->setDefaultSharing('Private'); 
/** Enable and Disable available tools */
$module->enableTools(Array('Import', 'Export'));
$module->disableTools('Merge');

?>
