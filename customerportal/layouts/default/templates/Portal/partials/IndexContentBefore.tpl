{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.2
* ("License.txt"); You may not use this file except in compliance with the License
* The Original Code is: Vtiger CRM Open Source
* The Initial Developer of the Original Code is Vtiger.
* Portions created by Vtiger are Copyright (C) Vtiger.
* All Rights Reserved.
************************************************************************************}

{literal}
    <div class="navigation-controls-row">
        <div ng-if="checkRecordsVisibility(filterPermissions)" class="panel-title col-md-12 module-title">{{ptitle}}
        </div>
    </div>
    <div class="row portal-controls-row">
        <div class="col-lg-2 col-md-2 col-sm-8 col-xs-8 top_space">
            <div ng-if="!checkRecordsVisibility(filterPermissions)" class="panel-title col-md-12 module-title">{{ptitle}}</div>
            <div class="btn-group btn-group-justified" ng-if="checkRecordsVisibility(filterPermissions)">
                <div class="btn-group">
                    <button type="button" translate="Mine"
                            ng-class="{'btn btn-soft-secondary btn-soft-primary':searchQ.onlymine, 'btn btn-soft-secondary':!searchQ.onlymine}" ng-click="searchQ.onlymine=true"></button>
                </div>
                <div class="btn-group">
                    <button type="button" translate = "All"
                            ng-class="{'btn btn-soft-secondary btn-soft-primary':!searchQ.onlymine, 'btn btn-soft-secondary':searchQ.onlymine}" ng-click="searchQ.onlymine=false"></button>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 col-xs-4 top_space">
            <div class="addbtnContainer" ng-if="isCreatable">
                <button class="btn btn-soft-primary" ng-click="createRecord(module)">New {{ptitle}}</button>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-4 top_space">
            &nbsp;
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12 top_space">
            <button class="btn btn-soft-primary" ng-if="exportEnabled" ng-csv="exportRecords(module)" csv-header="csvHeaders" add-bom="true" filename="{{filename}}.csv">{{'Export'|translate}}&nbsp;{{ptitle}}</button>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 top_space pagination-holder">
            <div class="pull-right">
                <div class="text-center">
                    <pagination
                            total-items="totalPages" max-size="3" ng-model="currentPage" ng-change="pageChanged(currentPage)" boundary-links="true">
                    </pagination>
                </div>
            </div>
        </div>
    </div>
{/literal}
