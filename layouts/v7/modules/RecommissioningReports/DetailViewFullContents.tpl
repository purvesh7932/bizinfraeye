<form id="detailView" method="POST">
    {include file='DetailViewBlockView.tpl'|@vtemplate_path:$MODULE_NAME RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
    {* {if $SERVICEREPORTTYPE|in_array:$EXTRALINEITEMREQUIREDTYPES}
        {include file="LineItemsDetailShortDam.tpl"|@vtemplate_path:'RecommissioningReports'}
    {/if}
    {if $REPORTTYPE|in_array:$EXTRALINEITEMREQUIREDSUBTYPES}
        {include file="LineItemsDetailShortDam.tpl"|@vtemplate_path:'RecommissioningReports'}
    {/if}
    {include file='LineItemsDetail.tpl'|@vtemplate_path:'RecommissioningReports'} *}
</form>
