{*/* * *******************************************************************************
 * The content of this file is subject to the ITS4YouInstaller license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */*}
{assign var=MODULE_MODEL value=$EXTENSION->get('moduleModel')}
{assign var=MODULE_NAME value=$EXTENSION->get('name')}
{assign var=MODULE_ACTIVE value=false}
{assign var=MODULE_URL value=false}
{if $MODULE_MODEL}
    {assign var=MODULE_ACTIVE value=$MODULE_MODEL->isActive()}
    {assign var=MODULE_URL value=$MODULE_MODEL->getDefaultUrl()}
{/if}
<tr class="installedModuleRow" data-cfmid="1" id="{$MODULE_NAME}">
    <td style="border-left:none;border-right:none;">
        <a {if $MODULE_URL}href="{$MODULE_URL}" target="_blank"{else}href="#{$MODULE_NAME}"{/if}>
            {vtranslate($EXTENSION->get('label'), $QUALIFIED_MODULE)}
        </a>
    </td>
    <td style="border-left:none;border-right:none;">{$EXTENSION->getVersion()}</td>
    <td style="border-left:none;border-right:none;">{$EXTENSION->get('pkgVersion')}</td>
    <td style="border-left:none;border-right:none;">
        <span class="extension_container">
            {if $EXTENSION->isVtigerCompatible()}
                <input type="hidden" name="extensionName" value="{$EXTENSION->get('name')}"/>
                <input type="hidden" name="extensionUrl" value="{$EXTENSION->get('downloadURL')}"/>
                <input type="hidden" name="extensionId" value="{$EXTENSION->get('id')}"/>
                <input type="hidden" name="extensionType" value="Single"/>
                {if !$EXTENSION->isAlreadyExists()}
                <input type="hidden" name="moduleAction" value="Install"/>
                <button class="oneclickInstallFree btn btn-primary {if $IS_AUTH}authenticated {else} loginRequired{/if}">
                        {vtranslate('LBL_INSTALL', $QUALIFIED_MODULE)}
                    </button>
                {elseif $EXTENSION->isUpgradable()}
                    <input type="hidden" name="moduleAction" value="Upgrade"/>
                <button class="oneclickInstallFree isUpdateBtn btn btn-success margin0px {if $IS_AUTH}authenticated {else} loginRequired{/if}">
                        {vtranslate('LBL_UPDATE', $QUALIFIED_MODULE)}
                    </button>
            {/if}
            {/if}
        </span>
        {if $MODULE_MODEL}
            {assign var=SETTINGS_LINKS value=$MODULE_MODEL->getSettingLinks()}
            {if (count($SETTINGS_LINKS) > 0)}
                <span class="btn-group pull-right {if !$MODULE_ACTIVE}hide{/if}">
                    <button class="btn btn-default btn dropdown-toggle unpin hiden " data-toggle="dropdown">
                        {vtranslate('LBL_SETTINGS', $QUALIFIED_MODULE)}&nbsp;<i class="caret"></i>
                    </button>
                    <ul class="dropdown-menu pull-right dropdownfields">
                        {foreach item=SETTINGS_LINK from=$SETTINGS_LINKS}
                            <li>
                                <a target="_blank"
                                    {if stripos($SETTINGS_LINK['linkurl'], 'javascript:') === 0}
                                        onclick='{$SETTINGS_LINK['linkurl']|substr:strlen("javascript:")}'
                                    {else}
                                        href='{$SETTINGS_LINK['linkurl']}'
                                    {/if}>
                                    {vtranslate($SETTINGS_LINK['linklabel'], $MODULE_NAME, vtranslate("SINGLE_$MODULE_NAME", $MODULE_NAME))}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </span>
            {/if}
        {/if}
        <span class="pull-right">
            {if $EXTENSION->get('website') neq ""}
                <button class="btn btn-default installExtension addButton" style="margin-right:5px;" data-url="{$EXTENSION->get('website')}">{vtranslate('LBL_MORE_DETAILS', $QUALIFIED_MODULE)}</button>
            {/if}
        </span>
    </td>
</tr>