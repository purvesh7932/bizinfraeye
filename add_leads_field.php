<?php

// Turn on debugging level
$Vtiger_Utils_Log = true;

// Include necessary classes
include_once('vtlib/Vtiger/Module.php');

// Define instances
$users = Vtiger_Module::getInstance('ServiceEngineer');

// Nouvelle instance pour le nouveau bloc
$block = Vtiger_Block::getInstance('LBL_USERLOGIN_ROLE', $users);

$field21 = new Vtiger_Field();
$field21->name = 'emp_imagefile';
$field21->label = 'Upload Image';
$field21->table = $users->basetable; 
$field21->column = 'emp_imagefile';
$field21->columntype = 'VARCHAR(255)';
$field21->uitype = 69;
$field21->typeofdata = 'V~O';
$block->addField($field21);

?> 