<?php
require_once("modules/com_vtiger_workflow/include.inc");
require_once("modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc");
require_once("modules/com_vtiger_workflow/VTEntityMethodManager.inc");
require_once("include/database/PearDatabase.php");
$adb = PearDatabase::getInstance();
$emm = new VTEntityMethodManager($adb);
require_once 'vtlib/Vtiger/Module.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$moduleInstance = Vtiger_Module::getInstance('HelpDesk');
$blockInstance = Vtiger_Block::getInstance('LBL_ITEM_DETAILS', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('discount_amount', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'discount_amount';
        $fieldInstance->column = 'discount_amount';
        $fieldInstance->uitype = 71;
        $fieldInstance->table = 'vtiger_inventoryproductrel';
        $fieldInstance->label = 'Item Discount Amount';
        $fieldInstance->readonly = 0;
        $fieldInstance->presence = 2;
        $fieldInstance->typeofdata = 'N~O';
        $fieldInstance->columntype = 'decimal(25,8)';
        $fieldInstance->quickcreate = 1;
        $fieldInstance->displaytype = 5;
        $fieldInstance->masseditable = 0;
        $blockInstance->addField($fieldInstance);
    } else {
        echo "field is already Present --- discount_amount in HelpDesk Module --- <br>";
    }
} else {
    echo " block does not exits --- LBL_ITEM_DETAILS in HelpDesk -- <br>";
}

$moduleInstance = Vtiger_Module::getInstance('HelpDesk');
$blockInstance = Vtiger_Block::getInstance('LBL_ITEM_DETAILS', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('discount_percent', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'discount_percent';
        $fieldInstance->column = 'discount_percent';
        $fieldInstance->uitype = 71;
        $fieldInstance->table = 'vtiger_inventoryproductrel';
        $fieldInstance->label = 'Item Discount Percent';
        $fieldInstance->readonly = 0;
        $fieldInstance->presence = 2;
        $fieldInstance->typeofdata = 'N~O';
        $fieldInstance->columntype = 'decimal(25,8)';
        $fieldInstance->quickcreate = 1;
        $fieldInstance->displaytype = 5;
        $fieldInstance->masseditable = 0;
        $blockInstance->addField($fieldInstance);
    } else {
        echo "field is already Present --- discount_percent in HelpDesk Module --- <br>";
    }
} else {
    echo " block does not exits --- LBL_ITEM_DETAILS in HelpDesk -- <br>";
}

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('HelpDesk');
$blockInstance = Vtiger_Block::getInstance('CUSTOMER_DETAILS', $moduleInstance);
if ($blockInstance) {
    echo " block does not exits --- CUSTOMER_DETAILS   -- <br>";
} else {
    $blockInstance = new Vtiger_Block();
    $blockInstance->label = 'CUSTOMER_DETAILS';
    $moduleInstance->addBlock($blockInstance);
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;
$invoiceModule = Vtiger_Module::getInstance('HelpDesk');
$blockInstance = Vtiger_Block::getInstance('CUSTOMER_DETAILS', $invoiceModule);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('mobile', $invoiceModule);
    if (!$fieldInstance) {
        $field = new Vtiger_Field();
        $field->name = 'mobile';
        $field->column = 'mobile';
        $field->uitype = 11;
        $field->table = $invoiceModule->basetable;
        $field->label = 'Mobile Phone';
        $field->summaryfield = 1;
        $field->readonly = 1;
        $field->presence = 2;
        $field->typeofdata = 'V~O';
        $field->columntype = 'VARCHAR(30)';
        $field->quickcreate = 3;
        $field->displaytype = 1;
        $field->masseditable = 1;
        $blockInstance->addField($field);
    } else {
        echo "field is present -- mobile HelpDesk --- <br>";
    }
} else {
    echo "Block Does not exits -- CUSTOMER_DETAILS in HelpDesk -- <br>";
}
$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;

$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;
$invoiceModule = Vtiger_Module::getInstance('HelpDesk');
$blockInstance = Vtiger_Block::getInstance('CUSTOMER_DETAILS', $invoiceModule);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('customer_name', $invoiceModule);
    if (!$fieldInstance) {
        $field = new Vtiger_Field();
        $field->name = 'customer_name';
        $field->column = 'customer_name';
        $field->uitype = 2;
        $field->table = $invoiceModule->basetable;
        $field->label = 'Customer Name';
        $field->readonly = 1;
        $field->presence = 2;
        $field->typeofdata = 'V~O';
        $field->columntype = 'VARCHAR(250)';
        $field->quickcreate = 3;
        $field->displaytype = 1;
        $field->masseditable = 1;
        $blockInstance->addField($field);
    } else {
        echo "field is present -- customer_name in HelpDesk --- <br>";
    }
} else {
    echo "Block Does not exits -- CUSTOMER_DETAILS in HelpDesk -- <br>";
}
$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;

$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;
$invoiceModule = Vtiger_Module::getInstance('HelpDesk');
$blockInstance = Vtiger_Block::getInstance('CUSTOMER_DETAILS', $invoiceModule);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('address', $invoiceModule);
    if (!$fieldInstance) {
        $field = new Vtiger_Field();
        $field->name = 'address';
        $field->column = 'address';
        $field->uitype = 21;
        $field->table = $invoiceModule->basetable;
        $field->label = 'Address';
        $field->readonly = 1;
        $field->presence = 2;
        $field->typeofdata = 'V~O';
        $field->columntype = 'TEXT';
        $field->displaytype = 1;
        $field->masseditable = 1;
        $blockInstance->addField($field);
    } else {
        echo "field is present -- address In HelpDesk Module --- <br>";
    }
} else {
    echo "Block Does not exits -- CUSTOMER_DETAILS -- <br>";
}
$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('HelpDesk');
$blockInstance = Vtiger_Block::getInstance('PRODUCT_DETAILS', $moduleInstance);
if ($blockInstance) {
    echo " block does not exits --- PRODUCT_DETAILS   -- <br>";
} else {
    $blockInstance = new Vtiger_Block();
    $blockInstance->label = 'PRODUCT_DETAILS';
    $moduleInstance->addBlock($blockInstance);
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;
$invoiceModule = Vtiger_Module::getInstance('HelpDesk');
$blockInstance = Vtiger_Block::getInstance('PRODUCT_DETAILS', $invoiceModule);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('product_name', $invoiceModule);
    if (!$fieldInstance) {
        $field = new Vtiger_Field();
        $field->name = 'product_name';
        $field->column = 'product_name';
        $field->uitype = 2;
        $field->table = $invoiceModule->basetable;
        $field->label = 'Product Name';
        $field->readonly = 1;
        $field->presence = 2;
        $field->typeofdata = 'V~O';
        $field->columntype = 'VARCHAR(250)';
        $field->quickcreate = 3;
        $field->displaytype = 1;
        $field->masseditable = 1;
        $blockInstance->addField($field);
    } else {
        echo "field is present -- product_name in HelpDesk --- <br>";
    }
} else {
    echo "Block Does not exits -- PRODUCT_DETAILS in HelpDesk -- <br>";
}
$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;

$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;
$invoiceModule = Vtiger_Module::getInstance('HelpDesk');
$blockInstance = Vtiger_Block::getInstance('PRODUCT_DETAILS', $invoiceModule);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('product_modal', $invoiceModule);
    if (!$fieldInstance) {
        $field = new Vtiger_Field();
        $field->name = 'product_modal';
        $field->column = 'product_modal';
        $field->uitype = 2;
        $field->table = $invoiceModule->basetable;
        $field->label = 'Product Model';
        $field->readonly = 1;
        $field->presence = 2;
        $field->typeofdata = 'V~O';
        $field->columntype = 'VARCHAR(250)';
        $field->quickcreate = 3;
        $field->displaytype = 1;
        $field->masseditable = 1;
        $blockInstance->addField($field);
    } else {
        echo "field is present -- product_modal in HelpDesk --- <br>";
    }
} else {
    echo "Block Does not exits -- PRODUCT_DETAILS in HelpDesk -- <br>";
}
$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('HelpDesk');
$blockInstance = Vtiger_Block::getInstance('PRODUCT_DETAILS', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('product_category', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'product_category';
        $fieldInstance->label = 'Product Category';
        $fieldInstance->table = $moduleInstance->basetable;
        $fieldInstance->column = 'product_category';
        $fieldInstance->uitype = '16';
        $fieldInstance->presence = '0';
        $fieldInstance->typeofdata = 'V~O';
        $fieldInstance->columntype = 'VARCHAR(100)';
        $fieldInstance->defaultvalue = NULL;
        $blockInstance->addField($fieldInstance);
        $fieldInstance->setPicklistValues(array('test value'));
    } else {
        echo "field is already Present --- product_category in HelpDesk Module --- <br>";
    }
} else {
    echo " block does not exits --- PRODUCT_DETAILS -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('HelpDesk');
$blockInstance = Vtiger_Block::getInstance('PRODUCT_DETAILS', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('product_subcategory', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'product_subcategory';
        $fieldInstance->label = 'Product Subcategory';
        $fieldInstance->table = $moduleInstance->basetable;
        $fieldInstance->column = 'product_subcategory';
        $fieldInstance->uitype = '16';
        $fieldInstance->presence = '0';
        $fieldInstance->typeofdata = 'V~O';
        $fieldInstance->columntype = 'VARCHAR(100)';
        $fieldInstance->defaultvalue = NULL;
        $blockInstance->addField($fieldInstance);
        $fieldInstance->setPicklistValues(array('test value'));
    } else {
        echo "field is already Present --- product_subcategory in HelpDesk Module --- <br>";
    }
} else {
    echo " block does not exits --- PRODUCT_DETAILS -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('HelpDesk');
$blockInstance = Vtiger_Block::getInstance('PRODUCT_DETAILS', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('warrenty_period', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'warrenty_period';
        $fieldInstance->column = 'warrenty_period';
        $fieldInstance->uitype = 7;
        $fieldInstance->table = $moduleInstance->basetable;
        $fieldInstance->label = 'Warranty Period';
        $fieldInstance->readonly = 1;
        $fieldInstance->presence = 2;
        $fieldInstance->typeofdata = 'I~O';
        $fieldInstance->columntype = 'INT(5)';
        $fieldInstance->quickcreate = 3;
        $fieldInstance->displaytype = 1;
        $fieldInstance->masseditable = 1;
        $blockInstance->addField($fieldInstance);
    } else {
        echo "field is already Present --- warrenty_period in HelpDesk Module --- <br>";
    }
} else {
    echo " block does not exits --- PRODUCT_DETAILS in HelpDesk -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;
$invoiceModule = Vtiger_Module::getInstance('Equipment');
$blockInstance = Vtiger_Block::getInstance('LBL_BLOCK_GENERAL_INFORMATION', $invoiceModule);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('productname', $invoiceModule);
    if (!$fieldInstance) {
        $field = new Vtiger_Field();
        $field->name = 'productname';
        $field->column = 'productname';
        $field->uitype = 2;
        $field->table = $invoiceModule->basetable;
        $field->label = 'Product Name';
        $field->readonly = 1;
        $field->presence = 2;
        $field->typeofdata = 'V~O';
        $field->columntype = 'VARCHAR(250)';
        $field->quickcreate = 3;
        $field->displaytype = 1;
        $field->masseditable = 1;
        $blockInstance->addField($field);
    } else {
        echo "field is present -- productname in Equipment --- <br>";
    }
} else {
    echo "Block Does not exits -- LBL_BLOCK_GENERAL_INFORMATION in Equipment -- <br>";
}
$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('Accounts');
$blockInstance = Vtiger_Block::getInstance('Sale_Details', $moduleInstance);
if ($blockInstance) {
    echo " block does not exits --- Sale_Details   -- <br>";
} else {
    $blockInstance = new Vtiger_Block();
    $blockInstance->label = 'Sale_Details';
    $moduleInstance->addBlock($blockInstance);
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('Accounts');
$blockInstance = Vtiger_Block::getInstance('Sale_Details', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('saled_date', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'saled_date';
        $fieldInstance->label = 'Sold Date';
        $fieldInstance->table = $moduleInstance->basetable;
        $fieldInstance->column = 'saled_date';
        $fieldInstance->uitype = 5;
        $fieldInstance->presence = '0';
        $fieldInstance->typeofdata = 'D~O';
        $fieldInstance->columntype = 'DATE';
        $fieldInstance->defaultvalue = NULL;
        $blockInstance->addField($fieldInstance);
    } else {
        echo "field is already Present --- saled_date in Accounts Module --- <br>";
    }
} else {
    echo " block does not exits --- Sale_Details -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;
$invoiceModule = Vtiger_Module::getInstance('Accounts');
$blockInstance = Vtiger_Block::getInstance('Sale_Details', $invoiceModule);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('seller_name', $invoiceModule);
    if (!$fieldInstance) {
        $field = new Vtiger_Field();
        $field->name = 'seller_name';
        $field->column = 'seller_name';
        $field->uitype = 2;
        $field->table = $invoiceModule->basetable;
        $field->label = 'Seller Name';
        $field->readonly = 1;
        $field->presence = 2;
        $field->typeofdata = 'V~O';
        $field->columntype = 'VARCHAR(250)';
        $field->quickcreate = 3;
        $field->displaytype = 1;
        $field->masseditable = 1;
        $blockInstance->addField($field);
    } else {
        echo "field is present -- seller_name in Accounts --- <br>";
    }
} else {
    echo "Block Does not exits -- Sale_Details in Accounts -- <br>";
}
$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('Accounts');
$blockInstance = Vtiger_Block::getInstance('Sale_Details', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('final_amount', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'final_amount';
        $fieldInstance->column = 'final_amount';
        $fieldInstance->uitype = 7;
        $fieldInstance->table = $moduleInstance->basetable;
        $fieldInstance->label = 'Amount';
        $fieldInstance->readonly = 1;
        $fieldInstance->presence = 2;
        $fieldInstance->typeofdata = 'I~O';
        $fieldInstance->columntype = 'decimal(25,8)';
        $fieldInstance->quickcreate = 3;
        $fieldInstance->displaytype = 1;
        $fieldInstance->masseditable = 1;
        $blockInstance->addField($fieldInstance);
    } else {
        echo "field is already Present --- final_amount in Accounts Module --- <br>";
    }
} else {
    echo " block does not exits --- Sale_Details in Accounts -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;
$invoiceModule = Vtiger_Module::getInstance('Accounts');
$blockInstance = Vtiger_Block::getInstance('LBL_ACCOUNT_INFORMATION', $invoiceModule);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('address', $invoiceModule);
    if (!$fieldInstance) {
        $field = new Vtiger_Field();
        $field->name = 'address';
        $field->column = 'address';
        $field->uitype = 21;
        $field->table = $invoiceModule->basetable;
        $field->label = 'Address';
        $field->readonly = 1;
        $field->presence = 2;
        $field->typeofdata = 'V~O';
        $field->columntype = 'TEXT';
        $field->displaytype = 1;
        $field->masseditable = 1;
        $blockInstance->addField($field);
    } else {
        echo "field is present -- address In Accounts Module --- <br>";
    }
} else {
    echo "Block Does not exits -- LBL_ACCOUNT_INFORMATION -- <br>";
}
$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('Accounts');
$blockInstance = Vtiger_Block::getInstance('PRODUCT_DETAILS', $moduleInstance);
if ($blockInstance) {
    echo " block does not exits --- PRODUCT_DETAILS   -- <br>";
} else {
    $blockInstance = new Vtiger_Block();
    $blockInstance->label = 'PRODUCT_DETAILS';
    $moduleInstance->addBlock($blockInstance);
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('Accounts');
$blockInstance = Vtiger_Block::getInstance('PRODUCT_DETAILS', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('product_category', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'product_category';
        $fieldInstance->label = 'Product Category';
        $fieldInstance->table = $moduleInstance->basetable;
        $fieldInstance->column = 'product_category';
        $fieldInstance->uitype = '16';
        $fieldInstance->presence = '0';
        $fieldInstance->typeofdata = 'V~O';
        $fieldInstance->columntype = 'VARCHAR(100)';
        $fieldInstance->defaultvalue = NULL;
        $blockInstance->addField($fieldInstance);
        $fieldInstance->setPicklistValues(array('test value'));
    } else {
        echo "field is already Present --- product_category in Accounts Module --- <br>";
    }
} else {
    echo " block does not exits --- PRODUCT_DETAILS -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('Accounts');
$blockInstance = Vtiger_Block::getInstance('PRODUCT_DETAILS', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('product_subcategory', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'product_subcategory';
        $fieldInstance->label = 'Product Subcategory';
        $fieldInstance->table = $moduleInstance->basetable;
        $fieldInstance->column = 'product_subcategory';
        $fieldInstance->uitype = '16';
        $fieldInstance->presence = '0';
        $fieldInstance->typeofdata = 'V~O';
        $fieldInstance->columntype = 'VARCHAR(100)';
        $fieldInstance->defaultvalue = NULL;
        $blockInstance->addField($fieldInstance);
        $fieldInstance->setPicklistValues(array('test value'));
    } else {
        echo "field is already Present --- product_subcategory in Accounts Module --- <br>";
    }
} else {
    echo " block does not exits --- PRODUCT_DETAILS -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('Accounts');
$blockInstance = Vtiger_Block::getInstance('PRODUCT_DETAILS', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('warrenty_period', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'warrenty_period';
        $fieldInstance->column = 'warrenty_period';
        $fieldInstance->uitype = 7;
        $fieldInstance->table = $moduleInstance->basetable;
        $fieldInstance->label = 'Warranty Period';
        $fieldInstance->readonly = 1;
        $fieldInstance->presence = 2;
        $fieldInstance->typeofdata = 'I~O';
        $fieldInstance->columntype = 'INT(5)';
        $fieldInstance->quickcreate = 3;
        $fieldInstance->displaytype = 1;
        $fieldInstance->masseditable = 1;
        $blockInstance->addField($fieldInstance);
    } else {
        echo "field is already Present --- warrenty_period in Accounts Module --- <br>";
    }
} else {
    echo " block does not exits --- PRODUCT_DETAILS in Accounts -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;


$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('HelpDesk');
$blockInstance = Vtiger_Block::getInstance('LBL_TICKET_INFORMATION', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('ticket_date', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'ticket_date';
        $fieldInstance->label = 'Ticket Date ';
        $fieldInstance->table = $moduleInstance->basetable;
        $fieldInstance->column = 'ticket_date';
        $fieldInstance->uitype = 5;
        $fieldInstance->presence = '0';
        $fieldInstance->typeofdata = 'D~O';
        $fieldInstance->displaytype = 2;
        $fieldInstance->columntype = 'DATE';
        $fieldInstance->defaultvalue = NULL;
        $blockInstance->addField($fieldInstance);
    } else {
        echo "field is already Present --- ticket_date in HelpDesk Module --- <br>";
    }
} else {
    echo " block does not exits --- LBL_TICKET_INFORMATION -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('HelpDesk');
$blockInstance = Vtiger_Block::getInstance('LBL_TICKET_INFORMATION', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('ticket_type', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'ticket_type';
        $fieldInstance->label = 'Type';
        $fieldInstance->table = $moduleInstance->basetable;
        $fieldInstance->column = 'ticket_type';
        $fieldInstance->uitype = '16';
        $fieldInstance->presence = '0';
        $fieldInstance->typeofdata = 'V~O';
        $fieldInstance->columntype = 'VARCHAR(100)';
        $fieldInstance->defaultvalue = NULL;
        $blockInstance->addField($fieldInstance);
        $fieldInstance->setPicklistValues(array('Repair', 'Others', 'Service'));
    } else {
        echo "field is already Present --- ticket_type in HelpDesk Module --- <br>";
    }
} else {
    echo " block does not exits --- LBL_TICKET_INFORMATION -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;
$invoiceModule = Vtiger_Module::getInstance('Accounts');
$blockInstance = Vtiger_Block::getInstance('PRODUCT_DETAILS', $invoiceModule);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('model_number', $invoiceModule);
    if (!$fieldInstance) {
        $field = new Vtiger_Field();
        $field->name = 'model_number';
        $field->column = 'model_number';
        $field->uitype = 2;
        $field->table = $invoiceModule->basetable;
        $field->label = 'Model Number';
        $field->readonly = 1;
        $field->presence = 2;
        $field->typeofdata = 'V~O';
        $field->columntype = 'VARCHAR(250)';
        $field->quickcreate = 3;
        $field->displaytype = 1;
        $field->masseditable = 1;
        $blockInstance->addField($field);
    } else {
        echo "field is present -- model_number in Accounts --- <br>";
    }
} else {
    echo "Block Does not exits -- PRODUCT_DETAILS in Accounts -- <br>";
}
$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;

$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;
$invoiceModule = Vtiger_Module::getInstance('Accounts');
$blockInstance = Vtiger_Block::getInstance('PRODUCT_DETAILS', $invoiceModule);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('serial_number', $invoiceModule);
    if (!$fieldInstance) {
        $field = new Vtiger_Field();
        $field->name = 'serial_number';
        $field->column = 'serial_number';
        $field->uitype = 2;
        $field->table = $invoiceModule->basetable;
        $field->label = 'Serial Number';
        $field->readonly = 1;
        $field->presence = 2;
        $field->typeofdata = 'V~O';
        $field->columntype = 'VARCHAR(250)';
        $field->quickcreate = 3;
        $field->displaytype = 1;
        $field->masseditable = 1;
        $blockInstance->addField($field);
    } else {
        echo "field is present -- serial_number in Accounts --- <br>";
    }
} else {
    echo "Block Does not exits -- PRODUCT_DETAILS in Accounts -- <br>";
}
$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('Accounts');
$blockInstance = Vtiger_Block::getInstance('PRODUCT_DETAILS', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('product_brand', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'product_brand';
        $fieldInstance->label = 'Brand';
        $fieldInstance->table = $moduleInstance->basetable;
        $fieldInstance->column = 'product_brand';
        $fieldInstance->uitype = '16';
        $fieldInstance->presence = '0';
        $fieldInstance->typeofdata = 'V~O';
        $fieldInstance->columntype = 'VARCHAR(100)';
        $fieldInstance->defaultvalue = NULL;
        $blockInstance->addField($fieldInstance);
        $fieldInstance->setPicklistValues(array('test value'));
    } else {
        echo "field is already Present --- product_brand in Accounts Module --- <br>";
    }
} else {
    echo " block does not exits --- PRODUCT_DETAILS -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;
$invoiceModule = Vtiger_Module::getInstance('Accounts');
$blockInstance = Vtiger_Block::getInstance('LBL_BLOCK_GENERAL_INFORMATION', $invoiceModule);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('model_number', $invoiceModule);
    if (!$fieldInstance) {
        $field = new Vtiger_Field();
        $field->name = 'model_number';
        $field->column = 'model_number';
        $field->uitype = 2;
        $field->table = $invoiceModule->basetable;
        $field->label = 'Model Number';
        $field->readonly = 1;
        $field->presence = 2;
        $field->typeofdata = 'V~O';
        $field->columntype = 'VARCHAR(250)';
        $field->quickcreate = 3;
        $field->displaytype = 1;
        $field->masseditable = 1;
        $blockInstance->addField($field);
    } else {
        echo "field is present -- model_number in Accounts --- <br>";
    }
} else {
    echo "Block Does not exits -- PRODUCT_DETAILS in Accounts -- <br>";
}
$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;

$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;
$invoiceModule = Vtiger_Module::getInstance('Accounts');
$blockInstance = Vtiger_Block::getInstance('PRODUCT_DETAILS', $invoiceModule);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('serial_number', $invoiceModule);
    if (!$fieldInstance) {
        $field = new Vtiger_Field();
        $field->name = 'serial_number';
        $field->column = 'serial_number';
        $field->uitype = 2;
        $field->table = $invoiceModule->basetable;
        $field->label = 'Serial Number';
        $field->readonly = 1;
        $field->presence = 2;
        $field->typeofdata = 'V~O';
        $field->columntype = 'VARCHAR(250)';
        $field->quickcreate = 3;
        $field->displaytype = 1;
        $field->masseditable = 1;
        $blockInstance->addField($field);
    } else {
        echo "field is present -- serial_number in Accounts --- <br>";
    }
} else {
    echo "Block Does not exits -- PRODUCT_DETAILS in Accounts -- <br>";
}
$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;


$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('Equipment');
$blockInstance = Vtiger_Block::getInstance('LBL_BLOCK_GENERAL_INFORMATION', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('model_number', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'model_number';
        $fieldInstance->label = 'Model Number';
        $fieldInstance->table = $moduleInstance->basetable;
        $fieldInstance->column = 'model_number';
        $fieldInstance->uitype = '16';
        $fieldInstance->presence = '0';
        $fieldInstance->typeofdata = 'V~O';
        $fieldInstance->columntype = 'VARCHAR(100)';
        $fieldInstance->defaultvalue = NULL;
        $blockInstance->addField($fieldInstance);
        $fieldInstance->setPicklistValues(array('test value'));
    } else {
        echo "field is already Present --- model_number in Equipment Module --- <br>";
    }
} else {
    echo " block does not exits --- LBL_BLOCK_GENERAL_INFORMATION -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('Equipment');
$blockInstance = Vtiger_Block::getInstance('LBL_BLOCK_GENERAL_INFORMATION', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('product_category', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'product_category';
        $fieldInstance->label = 'Product Category';
        $fieldInstance->table = $moduleInstance->basetable;
        $fieldInstance->column = 'product_category';
        $fieldInstance->uitype = '16';
        $fieldInstance->presence = '0';
        $fieldInstance->typeofdata = 'V~O';
        $fieldInstance->columntype = 'VARCHAR(100)';
        $fieldInstance->defaultvalue = NULL;
        $blockInstance->addField($fieldInstance);
        $fieldInstance->setPicklistValues(array('test value'));
    } else {
        echo "field is already Present --- product_category in Equipment Module --- <br>";
    }
} else {
    echo " block does not exits --- LBL_BLOCK_GENERAL_INFORMATION -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('Equipment');
$blockInstance = Vtiger_Block::getInstance('LBL_BLOCK_GENERAL_INFORMATION', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('product_subcategory', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'product_subcategory';
        $fieldInstance->label = 'Product Subcategory';
        $fieldInstance->table = $moduleInstance->basetable;
        $fieldInstance->column = 'product_subcategory';
        $fieldInstance->uitype = '16';
        $fieldInstance->presence = '0';
        $fieldInstance->typeofdata = 'V~O';
        $fieldInstance->columntype = 'VARCHAR(100)';
        $fieldInstance->defaultvalue = NULL;
        $blockInstance->addField($fieldInstance);
        $fieldInstance->setPicklistValues(array('test value'));
    } else {
        echo "field is already Present --- product_subcategory in Equipment Module --- <br>";
    }
} else {
    echo " block does not exits --- LBL_BLOCK_GENERAL_INFORMATION -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('Equipment');
$blockInstance = Vtiger_Block::getInstance('LBL_BLOCK_GENERAL_INFORMATION', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('product_brand', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'product_brand';
        $fieldInstance->label = 'Manufacturer';
        $fieldInstance->table = $moduleInstance->basetable;
        $fieldInstance->column = 'product_brand';
        $fieldInstance->uitype = '16';
        $fieldInstance->presence = '0';
        $fieldInstance->typeofdata = 'V~O';
        $fieldInstance->columntype = 'VARCHAR(100)';
        $fieldInstance->defaultvalue = NULL;
        $blockInstance->addField($fieldInstance);
        $fieldInstance->setPicklistValues(array('test value'));
    } else {
        echo "field is already Present --- product_brand in Equipment Module --- <br>";
    }
} else {
    echo " block does not exits --- LBL_BLOCK_GENERAL_INFORMATION -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('Equipment');
$blockInstance = Vtiger_Block::getInstance('LBL_BLOCK_GENERAL_INFORMATION', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('final_amount', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'final_amount';
        $fieldInstance->column = 'final_amount';
        $fieldInstance->uitype = 7;
        $fieldInstance->table = $moduleInstance->basetable;
        $fieldInstance->label = 'Amount';
        $fieldInstance->readonly = 1;
        $fieldInstance->presence = 2;
        $fieldInstance->typeofdata = 'I~O';
        $fieldInstance->columntype = 'decimal(25,8)';
        $fieldInstance->quickcreate = 3;
        $fieldInstance->displaytype = 1;
        $fieldInstance->masseditable = 1;
        $blockInstance->addField($fieldInstance);
    } else {
        echo "field is already Present --- final_amount in Equipment Module --- <br>";
    }
} else {
    echo " block does not exits --- LBL_BLOCK_GENERAL_INFORMATION in Equipment -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;
$moduleInstance = Vtiger_Module::getInstance('Equipment');
$blockInstance = Vtiger_Block::getInstance('LBL_BLOCK_GENERAL_INFORMATION', $moduleInstance);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('warrenty_period', $moduleInstance);
    if (!$fieldInstance) {
        $fieldInstance = new Vtiger_Field();
        $fieldInstance->name = 'warrenty_period';
        $fieldInstance->column = 'warrenty_period';
        $fieldInstance->uitype = 7;
        $fieldInstance->table = $moduleInstance->basetable;
        $fieldInstance->label = 'Warranty Period';
        $fieldInstance->readonly = 1;
        $fieldInstance->presence = 2;
        $fieldInstance->typeofdata = 'I~O';
        $fieldInstance->columntype = 'INT(5)';
        $fieldInstance->quickcreate = 3;
        $fieldInstance->displaytype = 1;
        $fieldInstance->masseditable = 1;
        $blockInstance->addField($fieldInstance);
    } else {
        echo "field is already Present --- warrenty_period in Equipment Module --- <br>";
    }
} else {
    echo " block does not exits --- LBL_BLOCK_GENERAL_INFORMATION in Equipment -- <br>";
}
$moduleInstance = null;
$blockInstance = null;
$fieldInstance = null;

$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;
$invoiceModule = Vtiger_Module::getInstance('Accounts');
$blockInstance = Vtiger_Block::getInstance('LBL_CUSTOM_INFORMATION', $invoiceModule);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('equipment_id', $invoiceModule);
    if (!$fieldInstance) {
        $field = new Vtiger_Field();
        $field->name = 'equipment_id';
        $field->column = 'equipment_id';
        $field->uitype = 10;
        $field->table = $invoiceModule->basetable;
        $field->label = 'Equipment Serial No.';
        $field->readonly = 1;
        $field->presence = 2;
        $field->typeofdata = 'I~O';
        $field->columntype = 'INT(10)';
        $field->quickcreate = 3;
        $field->displaytype = 1;
        $field->masseditable = 1;
        $id = $blockInstance->addFieldIgnite($field);
        echo "created field --- $id ";
        $tom = "INSERT INTO `vtiger_fieldmodulerel` (`fieldid`, `module`, `relmodule`, `status`, `sequence`) VALUES ('$id', 'Accounts', 'Equipment', NULL, NULL)";
        $adb->pquery($tom);
    } else {
        echo "field is present -- equipment_id  in Accounts --- <br>";
    }
} else {
    echo "Block Does not exits -- LBL_CUSTOM_INFORMATION in Accounts -- <br>";
}
$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;

if (!Vtiger_Utils::CheckTable('vtiger_ignite_sqlmigrater')) {
    Vtiger_Utils::CreateTable(
        'vtiger_ignite_sqlmigrater',
        '(`id` int(25) NOT NULL AUTO_INCREMENT,
        `sqlquerynumber` int(19) NOT NULL,
        PRIMARY KEY (`id`))',
        true
    );
    echo '<br> vtiger_ignite_sqlmigrater table is created <br>';
} else {
    echo '<br> vtiger_ignite_sqlmigrater table is already exits <br>';
}

$moduleInstance = Vtiger_Module::getInstance('Companies');
$blockInstance = Vtiger_Block::getInstance('General Information', $moduleInstance);
if ($blockInstance) {
        echo " block exits ---    -- <br>" . 'General Information';
} else {
        $blockInstance = new Vtiger_Block();
        $blockInstance->label = 'General Information';
        $moduleInstance->addBlock($blockInstance);
}

$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;
$invoiceModule = Vtiger_Module::getInstance('Companies');
$blockInstance = Vtiger_Block::getInstance('General Information', $invoiceModule);
if ($blockInstance) {
    $fieldInstance = Vtiger_Field::getInstance('company_website', $invoiceModule);
    if (!$fieldInstance) {
        $field = new Vtiger_Field();
        $field->name = 'company_website';
        $field->column = 'company_website';
        $field->uitype = 17;
        $field->table = $invoiceModule->basetable;
        $field->label = 'Comapany Website';
        $field->presence = 2;
        $field->typeofdata = 'V~O';
        $field->columntype = 'VARCHAR(255)';
        $field->quickcreate = 3;
        $field->displaytype = 1;
        $blockInstance->addField($field);
    } else {
        echo "field is present -- company_website Companies --- <br>";
    }
} else {
    echo "Block Does not exits -- General Information in Companies -- <br>";
}
$invoiceModule = null;
$blockInstance = null;
$fieldInstance = null;