{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Vtiger/views/Popup.php *}

{strip}
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE={vtranslate($MODULE,$MODULE)}}
        <div class="modal-body">
            <div id="popupPageContainer" class="contentsDiv col-sm-12">
                <input type="hidden" id="parentModule" value="{$SOURCE_MODULE}"/>
                <input type="hidden" id="module" value="{$MODULE}"/>
                <input type="hidden" id="parent" value="{$PARENT_MODULE}"/>
                <input type="hidden" id="sourceRecord" value="{$SOURCE_RECORD}"/>
                <input type="hidden" id="sourceField" value="{$SOURCE_FIELD}"/>
                <input type="hidden" id="url" value="{$GETURL}" />
                <input type="hidden" id="multi_select" value="{$MULTI_SELECT}" />
                <input type="hidden" id="currencyId" value="{$CURRENCY_ID}" />
                <input type="hidden" id="relatedParentModule" value="{$RELATED_PARENT_MODULE}"/>
                <input type="hidden" id="relatedParentId" value="{$RELATED_PARENT_ID}"/>
                <input type="hidden" id="view" name="view" value="{$VIEW}"/>
                <input type="hidden" id="relationId" value="{$RELATION_ID}" />
                <input type="hidden" id="selectedIds" name="selectedIds" data-selected-ids="" />
                <input type="hidden" id="excludedIds" name="excludedIds" data-excluded-ids="" />
                <input type="hidden" id="recordsCount" name="recordsCount" />
                {if !empty($POPUP_CLASS_NAME)}
                    <input type="hidden" id="popUpClassName" value="{$POPUP_CLASS_NAME}"/>
                {/if}
                <div class='col-lg-12 col-md-12 col-sm-12'>
                    {assign var=RELATED_MODULE_NAME value=$RELATED_PARENT_MODULE}
                    <div class="hide messageContainer" style = "height:30px;">
                            <center><a id="selectAllMsgDiv" href="#">{vtranslate('LBL_SELECT_ALL',$MODULE)}&nbsp;{vtranslate($RELATED_MODULE_NAME ,$RELATED_MODULE_NAME)}&nbsp;(<span id="totalRecordsCount" value=""></span>)</a></center>
                    </div>
                    <div class="hide messageContainer" style = "height:30px;">
                            <center><a id="deSelectAllMsgDiv" href="#">{vtranslate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></center>
                    </div>
                </div>
                <div id="popupContents" class="">
                    {include file='PopupContents.tpl'|vtemplate_path:$MODULE_NAME}
                </div>
            </div>
        </div>
    </div>
</div>
{/strip}
