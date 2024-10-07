{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

<footer class="app-footer">
    <div style="display: flex; height: 25px">
		{if $MODULE != 'Home' }
			<div style="width: 42px;background-color:#2C3B49"></div>
		{else}
			<div></div>
		{/if}
	</div>
</footer>
</div>
<div id='overlayPage'>
	<div class='arrow'></div>
	<div class='data'>
	</div>
</div>
<div id='helpPageOverlay'></div>
<div id="js_strings" class="hide noprint">{Zend_Json::encode($LANGUAGE_STRINGS)}</div>
<div class="modal myModal fade"></div>
{include file='JSResources.tpl'|@vtemplate_path}
    <script src="{vresource_url('layouts/v7/feather.min.js')}"></script>
	<script>
        feather.replace()
    </script>
</body>

</html>