<?php
chdir(dirname(__FILE__) . '/../');
include_once 'vtlib/Vtiger/Module.php';

$Vtiger_Utils_Log = true;

$MODULENAME = 'Companies';

$lcasemodname = strtolower($MODULENAME);
$basetable = "vtiger_$lcasemodname";
$basetableid = $lcasemodname . "id";
$customtable = $basetable . "cf";

global $adb;
$moduleInstance = Vtiger_Module::getInstance($MODULENAME);
if ($moduleInstance) {
        echo "Module already present - choose a different name.";
} else {
        $moduleInstance = new Vtiger_Module();
        $moduleInstance->name = $MODULENAME;
        $moduleInstance->parent = 'TOOLS';
        $moduleInstance->save();
}
$moduleInstance = Vtiger_Module::getInstance($MODULENAME);
$moduleInstance->initTables($basetable, $basetableid);
$blockInstance = Vtiger_Block::getInstance('LBL_' . strtoupper($moduleInstance->name) . '_INFORMATION', $moduleInstance);
if ($blockInstance) {
        echo " block exits ---    -- <br>" . 'LBL_' . strtoupper($moduleInstance->name) . '_INFORMATION';
} else {
        $blockInstance = new Vtiger_Block();
        $blockInstance->label = 'LBL_' . strtoupper($moduleInstance->name) . '_INFORMATION';
        $moduleInstance->addBlock($blockInstance);
}
$blockInstance = Vtiger_Block::getInstance('LBL_CUSTOM_INFORMATION', $moduleInstance);
if ($blockInstance) {
        echo " block exits --- LBL_CUSTOM_INFORMATION   -- <br>";
} else {
        $blockInstance = new Vtiger_Block();
        $blockInstance->label = 'LBL_CUSTOM_INFORMATION';
        $moduleInstance->addBlock($blockInstance);
}

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance($MODULENAME);
$blockInstance = Vtiger_Block::getInstance('LBL_CUSTOM_INFORMATION', $moduleInstance);
if ($blockInstance) {
        $fieldInstance = Vtiger_Field::getInstance('company_name', $moduleInstance);
        if (!$fieldInstance) {
                $field = new Vtiger_Field();
                $field->name = 'company_name';
                $field->column = 'company_name';
                $field->uitype = 19;
                $field->table = $basetable;
                $field->label = 'Company Name';
                $field->presence = 2;
                $field->typeofdata = 'V~O';
                $field->columntype = 'VARCHAR(1000)';
                $field->displaytype = 1;
                $blockInstance->addField($field);
                $moduleInstance->setEntityIdentifier($field);
        } else {
                echo "field is already Present --- company_name in $MODULENAME Module --- <br>";
        }
} else {
        echo " block does not exits --- LBL_CUSTOM_INFORMATION in $MODULENAME -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance($MODULENAME);
$blockInstance = Vtiger_Block::getInstance('LBL_CUSTOM_INFORMATION', $moduleInstance);
if ($blockInstance) {
        $fieldInstance = Vtiger_Field::getInstance('description', $moduleInstance);
        if (!$fieldInstance) {
                $field3  = new Vtiger_Field();
                $field3->name = 'description';
                $field3->label = 'Description';
                $field3->uitype = 19;
                $field3->column = 'description';
                $field3->table = 'vtiger_crmentity';
                $blockInstance->addField($field3);
        } else {
                echo "field is already Present --- description in $MODULENAME Module --- <br>";
        }
} else {
        echo " block does not exits --- LBL_CUSTOM_INFORMATION in $MODULENAME -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;


$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance($MODULENAME);
$blockInstance = Vtiger_Block::getInstance('LBL_CUSTOM_INFORMATION', $moduleInstance);
if ($blockInstance) {
        $fieldInstance = Vtiger_Field::getInstance('assigned_user_id', $moduleInstance);
        if (!$fieldInstance) {
                $mfield1 = new Vtiger_Field();
                $mfield1->name = 'assigned_user_id';
                $mfield1->label = 'Assigned To';
                $mfield1->table = 'vtiger_crmentity';
                $mfield1->column = 'smownerid';
                $mfield1->uitype = 53;
                $mfield1->typeofdata = 'V~M';
                $blockInstance->addField($mfield1);
        } else {
                echo "field is already Present --- assigned_user_id in $MODULENAME Module --- <br>";
        }
} else {
        echo " block does not exits --- LBL_CUSTOM_INFORMATION in $MODULENAME -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance($MODULENAME);
$blockInstance = Vtiger_Block::getInstance('LBL_CUSTOM_INFORMATION', $moduleInstance);
if ($blockInstance) {
        $fieldInstance = Vtiger_Field::getInstance('createdtime', $moduleInstance);
        if (!$fieldInstance) {
                $mfield2 = new Vtiger_Field();
                $mfield2->name = 'createdtime';
                $mfield2->label = 'Created Time';
                $mfield2->table = 'vtiger_crmentity';
                $mfield2->column = 'createdtime';
                $mfield2->uitype = 70;
                $mfield2->typeofdata = 'DT~O';
                $mfield2->displaytype = 2;
                $blockInstance->addField($mfield2);
        } else {
                echo "field is already Present --- createdtime in $MODULENAME Module --- <br>";
        }
} else {
        echo " block does not exits --- LBL_CUSTOM_INFORMATION in $MODULENAME -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;


$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance($MODULENAME);
$blockInstance = Vtiger_Block::getInstance('LBL_CUSTOM_INFORMATION', $moduleInstance);
if ($blockInstance) {
        $fieldInstance = Vtiger_Field::getInstance('modifiedtime', $moduleInstance);
        if (!$fieldInstance) {
                $mfield3 = new Vtiger_Field();
                $mfield3->name = 'modifiedtime';
                $mfield3->label = 'Modified Time';
                $mfield3->table = 'vtiger_crmentity';
                $mfield3->column = 'modifiedtime';
                $mfield3->uitype = 70;
                $mfield3->typeofdata = 'DT~O';
                $mfield3->displaytype = 2;
                $blockInstance->addField($mfield3);
        } else {
                echo "field is already Present --- modifiedtime in $MODULENAME Module --- <br>";
        }
} else {
        echo " block does not exits --- LBL_CUSTOM_INFORMATION in $MODULENAME -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance($MODULENAME);
$blockInstance = Vtiger_Block::getInstance('LBL_CUSTOM_INFORMATION', $moduleInstance);
if ($blockInstance) {
        $fieldInstance = Vtiger_Field::getInstance('source', $moduleInstance);
        if (!$fieldInstance) {
                /* NOTE: Vtiger 7.1.0 onwards */
                $mfield4 = new Vtiger_Field();
                $mfield4->name = 'source';
                $mfield4->label = 'Source';
                $mfield4->table = 'vtiger_crmentity';
                $mfield4->displaytype = 2; // to disable field in Edit View
                $mfield4->quickcreate = 3;
                $mfield4->masseditable = 0;
                $blockInstance->addField($mfield4);
        } else {
                echo "field is already Present --- source in $MODULENAME Module --- <br>";
        }
} else {
        echo " block does not exits --- LBL_CUSTOM_INFORMATION in $MODULENAME -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = null;

$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance($MODULENAME);
$blockInstance = Vtiger_Block::getInstance('LBL_CUSTOM_INFORMATION', $moduleInstance);
if ($blockInstance) {
        $fieldInstance = Vtiger_Field::getInstance('starred', $moduleInstance);
        if (!$fieldInstance) {
                $mfield5 = new Vtiger_Field();
                $mfield5->name = 'starred';
                $mfield5->label = 'starred';
                $mfield5->table = 'vtiger_crmentity_user_field';
                $mfield5->displaytype = 6;
                $mfield5->uitype = 56;
                $mfield5->typeofdata = 'C~O';
                $mfield5->quickcreate = 3;
                $mfield5->masseditable = 0;
                $blockInstance->addField($mfield5);
        } else {
                echo "field is already Present --- starred in $MODULENAME Module --- <br>";
        }
} else {
        echo " block does not exits --- LBL_CUSTOM_INFORMATION in $MODULENAME -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;


$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance($MODULENAME);
$blockInstance = Vtiger_Block::getInstance('LBL_CUSTOM_INFORMATION', $moduleInstance);
if ($blockInstance) {
        $fieldInstance = Vtiger_Field::getInstance('tags', $moduleInstance);
        if (!$fieldInstance) {
                $mfield6 = new Vtiger_Field();
                $mfield6->name = 'tags';
                $mfield6->label = 'tags';
                $mfield6->displaytype = 6;
                $mfield6->columntype = 'VARCHAR(1)';
                $mfield6->quickcreate = 3;
                $mfield6->masseditable = 0;
                $blockInstance->addField($mfield6);
        } else {
                echo "field is already Present --- tags in $MODULENAME Module --- <br>";
        }
} else {
        echo " block does not exits --- LBL_CUSTOM_INFORMATION in $MODULENAME -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

// Filter Setup

$moduleInstance = Vtiger_Module::getInstance($MODULENAME);
$vendorAllFilter = Vtiger_Filter::getInstance('All', $moduleInstance);
if (!$vendorAllFilter) {
        $filter1 = new Vtiger_Filter();
        $filter1->name = 'All';
        $filter1->isdefault = true;
        $moduleInstance->addFilter($filter1);
        $filter1->addField($field1)->addField($field2, 1)->addField($field3, 2)->addField($mfield1, 3);
}
$moduleInstance->setDefaultSharing();
$moduleInstance->initWebservice();
mkdir('modules/' . $MODULENAME);
echo "OK\n";

$result = $adb->pquery("SELECT id FROM vtiger_ws_entity WHERE name=? and handler_path=? and handler_class=?", array('Companies', 'include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation'));
if ($adb->num_rows($result) == 0) {
        $sql = "INSERT INTO `vtiger_ws_entity` (`id`, `name`, `handler_path`, `handler_class`, `ismodule`) VALUES (NULL, 'Companies', 'include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation', '1')";
        $adb->pquery($sql, array());
} else {
        print_r("<br> Query Already Executred ------ vtiger_ws_entity tuuuuuuuuuuuuuuuuuuuuuus");
}
$emm = null;

$tabid = getTabId($MODULENAME);
$result = $adb->pquery("SELECT * FROM vtiger_app2tab WHERE tabid=? and appname=?", array($tabid, 'INVENTORY'));
if ($adb->num_rows($result) == 0) {
        $sql = "INSERT INTO `vtiger_app2tab` (`tabid`, `appname`, `sequence`, `visible`) VALUES ($tabid, 'INVENTORY', '100', '1');";
        $adb->pquery($sql, array());
} else {
        print_r("<br> Query Already Executred ------> $sqlQuryNumber");
}
$emm = null;

$sqlQuryNumber = 143;
$result = $adb->pquery("SELECT sqlquerynumber FROM vtiger_ignite_sqlmigrater WHERE sqlquerynumber=?", array($sqlQuryNumber));
if ($adb->num_rows($result) == 0) {
        $adb->pquery($sql, array());
        $sql = "ALTER TABLE `$basetable` 
                ADD CONSTRAINT ig_$basetable
                FOREIGN KEY (`$basetableid`) REFERENCES `vtiger_crmentity`(`crmid`) 
                ON DELETE CASCADE ON UPDATE NO ACTION";

        $adb->pquery($sql, array());
        $sql = 'insert into `vtiger_ignite_sqlmigrater`(sqlquerynumber) values(?)';
        $adb->pquery($sql, array($sqlQuryNumber));
} else {
        print_r("<br> Query Already Executred ------> $sqlQuryNumber");
}
$emm = null;

$sqlQuryNumber = 144;
$result = $adb->pquery("SELECT sqlquerynumber FROM vtiger_ignite_sqlmigrater WHERE sqlquerynumber=?", array($sqlQuryNumber));
if ($adb->num_rows($result) == 0) {
        $adb->pquery($sql, array());
        $sql = "ALTER TABLE `$customtable` 
                    ADD CONSTRAINT `ig_$customtable` 
                    FOREIGN KEY (`$basetableid`) REFERENCES `vtiger_crmentity`(`crmid`) 
                    ON DELETE CASCADE ON UPDATE NO ACTION";
        $adb->pquery($sql, array());
        $sql = 'insert into `vtiger_ignite_sqlmigrater`(sqlquerynumber) values(?)';
        $adb->pquery($sql, array($sqlQuryNumber));
} else {
        print_r("<br> Query Already Executred ------> $sqlQuryNumber");
}
$emm = null;