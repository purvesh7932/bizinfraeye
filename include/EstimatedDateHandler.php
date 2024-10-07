<?php
function handleSetEstimatedDate($entityData)
{
    /* CONTENT OF CUSTOM FUNCTION ... */

    $projectData = $entityData->{'data'};

    $focus = CRMEntity::getInstance('Project');
    foreach($projectData as $key => $value){
        if($key == 'id'){
            continue;
        }
        if($key ==  'assigned_user_id' || $key ==  'modifiedby' || $key ==  'projectype' || $key=="linktoaccountscontacts" ){
            $purevalue = explode('x', $value)[1];
            $focus->column_fields[$key] = $purevalue;
        } else if( $key ==  'startdate' || $key ==  'targetenddate'  ){
            $focus->column_fields[$key] = date('Y-m-d',strtotime($value." +1 day"));
        } else {
            $focus->column_fields[$key] = $value;
        }
    }
    $focus->save("Project");
   
}
