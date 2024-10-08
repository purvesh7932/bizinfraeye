<?php

class BankGuarantee_List_View extends Vtiger_List_View {

    public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer) {

        $moduleName = $request->getModule();
        $cvId = $this->viewName;
        $pageNumber = $request->get('page');
        $orderBy = $request->get('orderby');
        $sortOrder = $request->get('sortorder');
        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
        $operator = $request->get('operator');
        $searchParams = $request->get('search_params');
        $tagParams = $request->get('tag_params');
        $starFilterMode = $request->get('starFilterMode');
        $listHeaders = $request->get('list_headers', array());
        $tag = $request->get('tag');
        $requestViewName = $request->get('viewname');
        $tagSessionKey = $moduleName . '_TAG';

        if (!$this->listViewModel) {
            $listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $cvId, $listHeaders);
        } else {
            $listViewModel = $this->listViewModel;
        }

        if (!empty($requestViewName) && empty($tag)) {
            unset($_SESSION[$tagSessionKey]);
        }

        if (empty($tag)) {
            $tagSessionVal = $listViewModel->getSortParamsSession($tagSessionKey);
            if (!empty($tagSessionVal)) {
                $tag = $tagSessionVal;
            }
        } else {
            $listViewModel->setSortParamsSession($tagSessionKey, $tag);
        }

        if (empty($cvId)) {
            $customView = new CustomView();
            $cvId = $customView->getViewId($moduleName);
        }

        $listViewSessionKey = $moduleName . '_' . $cvId;
        if (!empty($tag)) {
            $listViewSessionKey .= '_' . $tag;
        }

        $orderParams = $listViewModel->getSortParamsSession($listViewSessionKey);
        if ($request->get('mode') == 'removeAlphabetSearch') {
            $listViewModel->deleteParamsSession($listViewSessionKey, array('search_key', 'search_value', 'operator'));
            $searchKey = '';
            $searchValue = '';
            $operator = '';
        }
        if ($request->get('mode') == 'removeSorting') {
            $listViewModel->deleteParamsSession($listViewSessionKey, array('orderby', 'sortorder'));
            $orderBy = '';
            $sortOrder = '';
        }
        if (empty($listHeaders)) {
            $listHeaders = $orderParams['list_headers'];
        }


        if (!empty($tag) && empty($tagParams)) {
            $tagParams = $orderParams['tag_params'];
        }

        if (empty($orderBy) && empty($searchValue) && empty($pageNumber)) {
            if ($orderParams) {
                $pageNumber = $orderParams['page'];
                $orderBy = $orderParams['orderby'];
                $sortOrder = $orderParams['sortorder'];
                $searchKey = $orderParams['search_key'];
                $searchValue = $orderParams['search_value'];
                $operator = $orderParams['operator'];
                if (empty($tagParams)) {
                    $tagParams = $orderParams['tag_params'];
                }
                if (empty($searchParams)) {
                    $searchParams = $orderParams['search_params'];
                }

                if (empty($starFilterMode)) {
                    $starFilterMode = $orderParams['star_filter_mode'];
                }
            }
        } else if ($request->get('nolistcache') != 1) {
            $params = array('page' => $pageNumber, 'orderby' => $orderBy, 'sortorder' => $sortOrder, 'search_key' => $searchKey,
                'search_value' => $searchValue, 'operator' => $operator, 'tag_params' => $tagParams, 'star_filter_mode' => $starFilterMode, 'search_params' => $searchParams);

            if (!empty($listHeaders)) {
                $params['list_headers'] = $listHeaders;
            }
            $listViewModel->setSortParamsSession($listViewSessionKey, $params);
        }
        if ($sortOrder == "ASC") {
            $nextSortOrder = "DESC";
            $sortImage = "icon-chevron-down";
            $faSortImage = "fa-sort-desc";
        } else {
            $nextSortOrder = "ASC";
            $sortImage = "icon-chevron-up";
            $faSortImage = "fa-sort-asc";
        }

        if (empty($pageNumber)) {
            $pageNumber = '1';
        }

        $currentUser = Users_Record_Model::getCurrentUserModel();

        $linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'), 'CVID' => $cvId);
        $linkModels = $listViewModel->getListViewMassActions($linkParams);

        // preProcess is already loading this, we can reuse
        if (!$this->pagingModel) {
            $pagingModel = new Vtiger_Paging_Model();
            $pagingModel->set('page', $pageNumber);
            $pagingModel->set('viewid', $request->get('viewname'));
        } else {
            $pagingModel = $this->pagingModel;
        }

        if (!empty($orderBy)) {
            $listViewModel->set('orderby', $orderBy);
            $listViewModel->set('sortorder', $sortOrder);
        }

        if (!empty($operator)) {
            $listViewModel->set('operator', $operator);
            $viewer->assign('OPERATOR', $operator);
            $viewer->assign('ALPHABET_VALUE', $searchValue);
        }
        if (!empty($searchKey) && !empty($searchValue)) {
            $listViewModel->set('search_key', $searchKey);
            $listViewModel->set('search_value', $searchValue);
        }

        if (empty($searchParams)) {
            $searchParams = array();
        }
        if (count($searchParams) == 2 && empty($searchParams[1])) {
            unset($searchParams[1]);
        }

        if (empty($tagParams)) {
            $tagParams = array();
        }

        $searchAndTagParams = array_merge($searchParams, $tagParams);

        $transformedSearchParams = $this->transferListSearchParamsToFilterCondition($searchAndTagParams, $listViewModel->getModule());
        $listViewModel->set('search_params', $transformedSearchParams);


        //To make smarty to get the details easily accesible
        foreach ($searchParams as $fieldListGroup) {
            foreach ($fieldListGroup as $fieldSearchInfo) {
                $fieldSearchInfo['searchValue'] = $fieldSearchInfo[2];
                $fieldSearchInfo['fieldName'] = $fieldName = $fieldSearchInfo[0];
                $fieldSearchInfo['comparator'] = $fieldSearchInfo[1];
                $searchParams[$fieldName] = $fieldSearchInfo;
            }
        }

        foreach ($tagParams as $fieldListGroup) {
            foreach ($fieldListGroup as $fieldSearchInfo) {
                $fieldSearchInfo['searchValue'] = $fieldSearchInfo[2];
                $fieldSearchInfo['fieldName'] = $fieldName = $fieldSearchInfo[0];
                $fieldSearchInfo['comparator'] = $fieldSearchInfo[1];
                $tagParams[$fieldName] = $fieldSearchInfo;
            }
        }

        if (!$this->listViewHeaders) {
            $this->listViewHeaders = $listViewModel->getListViewHeaders();
        }

        if (!$this->listViewEntries) {
            $this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
        }
        //if list view entries restricted to show, paging should not fail
        if (!$this->noOfEntries) {
            $this->noOfEntries = $pagingModel->get('_listcount');
        }
        if (!$this->noOfEntries) {
            $noOfEntries = count($this->listViewEntries);
        } else {
            $noOfEntries = $this->noOfEntries;
        }
        $viewer->assign('MODULE', $moduleName);

        if (!$this->listViewLinks) {
            $this->listViewLinks = $listViewModel->getListViewLinks($linkParams);
        }
        $viewer->assign('LISTVIEW_LINKS', $this->listViewLinks);

        $viewer->assign('LISTVIEW_MASSACTIONS', $linkModels['LISTVIEWMASSACTION']);

        $viewer->assign('PAGING_MODEL', $pagingModel);
        if (!$this->pagingModel) {
            $this->pagingModel = $pagingModel;
        }
        $viewer->assign('PAGE_NUMBER', $pageNumber);

        if (!$this->moduleFieldStructure) {
            $recordStructure = Vtiger_RecordStructure_Model::getInstanceForModule($listViewModel->getModule(), Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_FILTER);
            $this->moduleFieldStructure = $recordStructure->getStructure();
        }

        if (!$this->tags) {
            $this->tags = Vtiger_Tag_Model::getAllAccessible($currentUser->id, $moduleName);
        }
        if (!$this->allUserTags) {
            $this->allUserTags = Vtiger_Tag_Model::getAllUserTags($currentUser->getId());
        }

        $listViewController = $listViewModel->get('listview_controller');
        $selectedHeaderFields = $listViewController->getListViewHeaderFields();

        $viewer->assign('ORDER_BY', $orderBy);
        $viewer->assign('SORT_ORDER', $sortOrder);
        $viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
        $viewer->assign('SORT_IMAGE', $sortImage);
        $viewer->assign('FASORT_IMAGE', $faSortImage);
        $viewer->assign('COLUMN_NAME', $orderBy);
        $viewer->assign('VIEWNAME', $this->viewName);

        $viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
        $viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
        $viewer->assign('LIST_HEADER_FIELDS', json_encode(array_keys($this->listViewHeaders)));
        $viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
        $viewer->assign('MODULE_FIELD_STRUCTURE', $this->moduleFieldStructure);
        $viewer->assign('SELECTED_HEADER_FIELDS', $selectedHeaderFields);
        $viewer->assign('TAGS', $this->tags);
        $viewer->assign('ALL_USER_TAGS', $this->allUserTags);
        $viewer->assign('ALL_CUSTOMVIEW_MODEL', CustomView_Record_Model::getAllFilterByModule($moduleName));
        $viewer->assign('CURRENT_TAG', $tag);
        $appName = $request->get('app');
        if (!empty($appName)) {
            $viewer->assign('SELECTED_MENU_CATEGORY', $appName);
        }
        if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false)) {
            if (!$this->listViewCount) {
                $this->listViewCount = $listViewModel->getListViewCount();
            }
            $totalCount = $this->listViewCount;
            $pageLimit = $pagingModel->getPageLimit();
            $pageCount = ceil((int) $totalCount / (int) $pageLimit);

            if ($pageCount == 0) {
                $pageCount = 1;
            }
            $viewer->assign('PAGE_COUNT', $pageCount);
            $viewer->assign('LISTVIEW_COUNT', $totalCount);
        }
        global $current_user;
        $bgValue = (float)$listViewModel->getSumOfEnqAmount();
        $bgValue = $bgValue / 10000000;
        $viewer->assign('LISTVIEW_AMOUNT_SUM', number_format($bgValue, getCurrencyDecimalPlaces($current_user), '.', ''));
        $viewer->assign('LIST_VIEW_MODEL', $listViewModel);
        $viewer->assign('GROUPS_IDS', Vtiger_Util_Helper::getGroupsIdsForUsers($currentUser->getId()));
        $viewer->assign('IS_CREATE_PERMITTED', $listViewModel->getModule()->isPermitted('CreateView'));
        $viewer->assign('IS_MODULE_EDITABLE', $listViewModel->getModule()->isPermitted('EditView'));
        $viewer->assign('IS_MODULE_DELETABLE', $listViewModel->getModule()->isPermitted('Delete'));
        $viewer->assign('SEARCH_DETAILS', $searchParams);
        $viewer->assign('TAG_DETAILS', $tagParams);
        $viewer->assign('NO_SEARCH_PARAMS_CACHE', $request->get('nolistcache'));
        $viewer->assign('STAR_FILTER_MODE', $starFilterMode);
        $viewer->assign('VIEWID', $cvId);
        $viewer->assign('REQUEST_INSTANCE', $request);
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        if ($moduleModel->isQuickPreviewEnabled()) {
            $viewer->assign('QUICK_PREVIEW_ENABLED', 'true');
        }

        $picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);
        $viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Zend_Json::encode($picklistDependencyDatasource));
    }

}
