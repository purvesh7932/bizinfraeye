{* *********************************************************************************
 * The content of this file is subject to the ITS4YouInstaller license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ******************************************************************************* *}
{strip}
	<div>
		<div class="clearfix padding10 marginBottom10px">
			<div class="pull-right">
				<button class="btn btn-{$REQUIREMENTS->getButtonType()}" type="button" onclick="window.open('{$MODULE_MODEL->getRequirementsUrl()}', '_blank');">
					{vtranslate('LBL_SYSTEM_REQUIREMENTS', $MODULE)}
				</button>
				&nbsp;
				{if !($PASSWORD_STATUS)}
					<button class="btn btn-default module-buttons" type="button" id="logintoInstaller">
						<div class="fa fa-sign-in" aria-hidden="true"></div>&nbsp;
						{vtranslate('LBL_LOGIN', $QUALIFIED_MODULE)}
					</button>
				{else}
					<button class="btn btn-default module-buttons" type="button" style="text-transform:none">
						{$USER_NAME}
					</button>
					&nbsp;
					<button class="btn btn-danger" type="button" id="logoutInstaller">
						<span class="fa fa-power-off"></span>&nbsp;
						{vtranslate('LBL_LOGOUT', $QUALIFIED_MODULE)}
					</button>
				{/if}
			</div>
			<br>
		</div>
	</div>
{/strip}