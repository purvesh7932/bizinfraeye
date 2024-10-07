

{include file=vtemplate_path('OverlayEditView.tpl', 'Inventory')}
{foreach key=index item=jsModel from=$SCRIPTS}
    <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}