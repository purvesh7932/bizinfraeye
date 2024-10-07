<?php

include_once 'vtlib/Vtiger/Module.php';
include_once 'vtlib/Vtiger/Package.php';
include_once 'includes/main/WebUI.php';

include_once 'include/Webservices/Utils.php';

$Vtiger_Utils_Log = true;

$MODULENAME = 'Owner';

$moduleInstance = new Vtiger_Module();
$moduleInstance->name = $MODULENAME;
$moduleInstance->parent = "Marketing";
$moduleInstance->save();

// Schema Setup
$moduleInstance->initTables();

// Webservice Setup
$moduleInstance->initWebservice();

// Field Setup
$block1 = new Vtiger_Block();
$block1->label = 'Owner And Tenant Information';
$moduleInstance->addBlock($block1);

// Add fields to the module

$field1 = new Vtiger_Field();
$field1->name = 'owntenant_name';
$field1->table = $moduleInstance->basetable;
$field1->label = 'Name';
$field1->column = $field1->name;
$field1->columntype = 'VARCHAR(255)';
$field1->uitype = 1;
$field1->displaytype = 1;
$field1->presence = 2;
$field1->typeofdata = 'V~M';
$block1->addField($field1);

$field2 = new Vtiger_Field();
$field2->name = 'owntenant_mobile';
$field2->table = $moduleInstance->basetable;
$field2->label = 'Mobile Number';
$field2->column = $field2->name;
$field2->columntype = 'VARCHAR(255)';
$field2->uitype = 11;
$field2->displaytype = 1;
$field2->presence = 2;
$field2->typeofdata = 'V~O';
$block1->addField($field2);

$field4 = new Vtiger_Field();
$field4->name = 'owntenant_email';
$field4->table = $moduleInstance->basetable;
$field4->label = 'Email';
$field4->column = $field4->name;
$field4->columntype = 'VARCHAR(255)';
$field4->uitype = 13;
$field4->displaytype = 1;
$field4->presence = 2;
$field4->typeofdata = 'V~O';
$block1->addField($field4);

$field7 = new Vtiger_Field();
$field7->name = 'owntenant_password';
$field7->table = $moduleInstance->basetable;
$field7->label = 'Password';
$field7->column = $field7->name;
$field7->columntype = 'VARCHAR(255)';
$field7->uitype = 99; // Date uitype
$field7->displaytype = 1;
$field7->presence = 2;
$field7->typeofdata = 'P~M';
$block1->addField($field7);


$field9 = new Vtiger_Field();
$field9->name = 'society_name'; 
$field9->table = $moduleInstance->basetable;
$field9->label = 'Society Name';
$field9->column = $field9->name;
$field9->columntype = 'VARCHAR(255)';
$field9->uitype = 15; 
$field9->displaytype = 1; 
$field9->presence = 2; 
$field9->typeofdata = 'V~O'; 

$picklistValues = ['Krupa', 'Laxmi', 'Brigad']; 
$field9->setPicklistValues($picklistValues);
$block1->addField($field9);

$field3 = new Vtiger_Field();
$field3->name = 'select_block'; 
$field3->table = $moduleInstance->basetable;
$field3->label = 'Select Block';
$field3->column = $field3->name;
$field3->columntype = 'VARCHAR(255)';
$field3->uitype = 15; 
$field3->displaytype = 1; 
$field3->presence = 2; 
$field3->typeofdata = 'V~O'; 

$picklistValues = ['A', 'B', 'C']; 
$field3->setPicklistValues($picklistValues);
$block1->addField($field3);



$field5 = new Vtiger_Field();
$field5->name = 'select_society_no'; 
$field5->table = $moduleInstance->basetable;
$field5->label = 'Select Society No';
$field5->column = $field5->name;
$field5->columntype = 'VARCHAR(255)';
$field5->uitype = 15; 
$field5->displaytype = 1; 
$field5->presence = 2; 
$field5->typeofdata = 'V~O'; 

$picklistValues = ['A', 'B', 'C']; 
$field5->setPicklistValues($picklistValues);
$block1->addField($field5);


$field6 = new Vtiger_Field();
$field6->name = 'owntenant_role'; 
$field6->table = $moduleInstance->basetable;
$field6->label = 'Role';
$field6->column = $field6->name;
$field6->columntype = 'VARCHAR(255)';
$field6->uitype = 15; 
$field6->displaytype = 1; 
$field6->presence = 2; 
$field6->typeofdata = 'V~O'; 

$picklistValues = ['Owner', 'Tenant']; 
$field6->setPicklistValues($picklistValues);
$block1->addField($field6);


$field17 = new Vtiger_Field();
$field17->name = 'approval_status'; 
$field17->table = $moduleInstance->basetable;
$field17->label = 'Approval Status';
$field17->column = $field17->name;
$field17->columntype = 'VARCHAR(255)';
$field17->uitype = 15; 
$field17->displaytype = 3; 
$field17->presence = 2; 
$field17->typeofdata = 'V~O'; 

$picklistValues = ['Accepted', 'Pending']; 
$field17->setPicklistValues($picklistValues);
$block1->addField($field17);


// $fieldInstance = new Vtiger_Field();
// $fieldInstance->name = 'candidate_work_choice';
// $fieldInstance->label = 'Work Choice';
// $fieldInstance->table = $moduleInstance->basetable;
// $fieldInstance->column = $fieldInstance->name;
// $fieldInstance->uitype = '15';
// $fieldInstance->presence = '2';
// $fieldInstance->typeofdata = 'V~O';
// $fieldInstance->columntype = 'VARCHAR(100)';
// $fieldInstance->displaytype = 1;
// $blockInstance->addField($fieldInstance);
// $fieldInstance->setPicklistValues(array('Remote', 'Onsite', 'Hybrid'));

// $fieldInstance = new Vtiger_Field();
// $fieldInstance->name = 'candidate_on_weekends';
// $fieldInstance->label = 'Are you Avaialble to work in Weekends';
// $fieldInstance->table = $moduleInstance->basetable;
// $fieldInstance->column = $fieldInstance->name;
// $fieldInstance->uitype = '15';
// $fieldInstance->presence = '2';
// $fieldInstance->typeofdata = 'V~O';
// $fieldInstance->columntype = 'VARCHAR(100)';
// $fieldInstance->displaytype = 1;
// $blockInstance->addField($fieldInstance);
// $fieldInstance->setPicklistValues(array('Yes', 'No'));

// $field10 = new Vtiger_Field();
// $field10->name = 'experience';
// $field10->table = $moduleInstance->basetable;
// $field10->label = 'Experience';
// $field10->column = $field10->name;
// $field2->columntype = 'VARCHAR(255)';
// $field2->uitype = 1;
// $field10->displaytype = 1;
// $field10->presence = 2;
// $field10->typeofdata = 'V~O';
// $block1->addField($field10);

// $field11 = new Vtiger_Field();
// $field11->name = 'vacancies';
// $field11->table = $moduleInstance->basetable;
// $field11->label = 'Vacancies';
// $field11->column = $field11->name;
// $field11->columntype = 'INT';
// $field11->uitype = 7; // Integer uitype
// $field11->displaytype = 1;
// $field11->presence = 2;
// $field11->typeofdata = 'N~O';
// $block1->addField($field11);

$field = new Vtiger_Field();
$field->name = 'assigned_user_id';
$field->label = 'Assigned To';
$field->table = 'vtiger_crmentity';
$field->column = 'smownerid';
$field->uitype = 53;
$field->displaytype = 1;
$field->presence = 2;
$field->typeofdata = 'V~M';
$block1->addField($field);

$field = new Vtiger_Field();
$field->name = 'createdtime';
$field->label = 'Created Time';
$field->table = 'vtiger_crmentity';
$field->column = 'createdtime';
$field->displaytype = 2;
$field->uitype = 70;
$field->typeofdata = 'D~O';
$block1->addField($field);

$field = new Vtiger_Field();
$field->name = 'modifiedtime';
$field->label = 'Modified Time';
$field->table = 'vtiger_crmentity';
$field->column = 'modifiedtime';
$field->displaytype = 2;
$field->uitype = 70;
$field->typeofdata = 'D~O';
$block1->addField($field);

// Filter Setup
$filter1 = new Vtiger_Filter();
$filter1->name = 'All';
$filter1->isdefault = true;
$moduleInstance->addFilter($filter1);

// Sharing Access Setup
$moduleInstance->setDefaultSharing('Public');

$targetpath = 'modules/' . $moduleInstance->name;

if (!is_file($targetpath)) {
    mkdir($targetpath);

    $templatepath = 'vtlib/ModuleDir/6.0.0';

    $moduleFileContents = file_get_contents($templatepath . '/ModuleName.php');
    $replacevars = array(
        'ModuleName' => $moduleInstance->name,
        '<modulename>' => strtolower($moduleInstance->name),
        '<entityfieldlabel>' => $field1->label,
        '<entitycolumn>' => $field1->column,
        '<entityfieldname>' => $field1->name
    );

    foreach ($replacevars as $key => $value) {
        $moduleFileContents = str_replace($key, $value, $moduleFileContents);
    }
    file_put_contents($targetpath . '/' . $moduleInstance->name . '.php', $moduleFileContents);
}

if (!file_exists('languages/en_us/ModuleName.php')) {
    $ModuleLanguageContents = file_get_contents($templatepath . '/languages/en_us/ModuleName.php');

    $replaceparams = array(
        'Module Name' => $moduleInstance->name,
        'Custom' => $moduleInstance->name,
        'ModuleBlock' => $moduleInstance->name,
        'ModuleFieldLabel Text' => $field1->label
    );

    foreach ($replaceparams as $key => $value) {
        $ModuleLanguageContents = str_replace($key, $value, $ModuleLanguageContents);
    }

    $languagePath = 'languages/en_us';
    file_put_contents($languagePath . '/' . $moduleInstance->name . '.php', $ModuleLanguageContents);
}

Settings_MenuEditor_Module_Model::addModuleToApp($moduleInstance->name, $moduleInstance->parent);

echo $moduleInstance->name . " is Created";

?>
