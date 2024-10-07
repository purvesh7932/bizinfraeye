{*/* * *******************************************************************************
 * The content of this file is subject to the ITS4YouInstaller license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */*}
<tr class="" data-cfmid="1">
    <td style="border-left:none;border-right:none;">{vtranslate($LANGUAGE->get('label'), $QUALIFIED_MODULE)}</td>
    <td colspan="2" style="border-left:none;border-right:none;">{vtranslate($LANGUAGE->get('description'), $QUALIFIED_MODULE)}</td>
    <td style="border-left:none;border-right:none;">
        <span class="extension_container">
            <input type="hidden" name="extensionName" value="{$LANGUAGE->get('name')}"/>
            <input type="hidden" name="extensionUrl" value="{$LANGUAGE->get('downloadURL')}"/>

            <input type="hidden" name="extensionId" value="{$LANGUAGE->get('id')}"/>
            <span class="pull-left">
                {if $LANGUAGE->get('website') neq ""}
                    <button class="btn installExtension addButton" style="margin-right:5px;" data-url="{$LANGUAGE->get('website')}">{vtranslate('LBL_MORE_DETAILS', $QUALIFIED_MODULE)}</button>
                {/if}
                {assign var=LANG_KEY value=$LANGUAGE->get('name')}
                {if $ALL_LANGUAGES[$LANG_KEY] eq ""}
                    <input type="hidden" name="moduleAction" value="Install"/>
                    <button class="oneclickInstallFree btn btn-primary {if $IS_AUTH}authenticated {else} loginRequired{/if}">{vtranslate('LBL_INSTALL', $QUALIFIED_MODULE)}</button>
                {/if}
            </span>
        </span>
    </td>
</tr>