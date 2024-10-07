{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.2
* ("License.txt"); You may not use this file except in compliance with the License
* The Original Code is: Vtiger CRM Open Source
* The Initial Developer of the Original Code is Vtiger.
* Portions created by Vtiger are Copyright (C) Vtiger.
* All Rights Reserved.
************************************************************************************}

{literal}
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 ticket-detail-header-row ">
  <div class="col-lg-6 col-md-5 col-sm-4 col-xs-12">
    <h3 class="fsmall">
      <detail-navigator>
      <span>
        <a ng-click="navigateBack(module)" style="font-size:small;">{{ptitle}}</a>
      </span>
      </detail-navigator>

      {{record[header]}}
    </h3>
  </div>
  <div class="col-lg-6 col-md-7 col-sm-8 col-xs-12 top_space_helpdesk">
    <button ng-if="(closeButtonDisabled && HelpDeskIsStatusEditable && isEditable)" translate="Mark as closed" class="btn btn-soft-success close-ticket" ng-click="close();"></button>
    <button ng-if="closeButtonDisabled && documentsEnabled" translate="Attach document to this ticket" class="btn btn-soft-primary attach-files-ticket" ng-click="attachDocument('Documents','LBL_ADD_DOCUMENT')"></button>
    <button translate="Edit Ticket" class="btn btn-soft-primary attach-files-ticket" ng-if="isEditable" ng-click="edit(module,id)"></button>

  </div>


</div>
</div>
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
  {/literal}
  <script type="text/javascript" src="{portal_componentjs_file('Documents')}"></script>
  {include file=portal_template_resolve('Documents', "partials/IndexContentAfter.tpl")}
