{* *********************************************************************************
 * The content of this file is subject to the ITS4YouInstaller license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ******************************************************************************* *}
{strip}
    <div class="tab-pane active" id="installedModules">
        {if $ERROR_MESSAGES}
            <div class="col-md-12">
                {foreach from=$ERROR_MESSAGES item=ERROR_MESSAGE}
                    <div>
                        <div class="displayInlineBlock alert alert-danger">{vtranslate($ERROR_MESSAGE, $QUALIFIED_MODULE)}</div>
                    </div>
                {/foreach}
            </div>
        {elseif !$IS_AUTH}
            <div style="text-align: center;">
                <br>
                <div class="alert alert-danger displayInlineBlock logintoInstaller extensionLoginAlert">{vtranslate('LBL_LOGIN_TO_INSTALLER', $QUALIFIED_MODULE)}</div>
                <br>
            </div>
        {else}
            <div class="col-md-12">
                <br>
                <h4>{vtranslate('LBL_ACTIVATED_LICENSES', $QUALIFIED_MODULE)}</h4>
                <hr>
            <div>
                <button class="btn btn-primary activateButton" type="button">
                    <i class="fa fa-plus icon-plus"></i>&nbsp;&nbsp;
                    <div class="displayInlineBlock">{vtranslate('LBL_ACTIVATE',$QUALIFIED_MODULE)} {vtranslate('LBL_LICENSE_KEY',$QUALIFIED_MODULE)}</div>
                </button>
                &nbsp;&nbsp;
                <button class="btn btn-secondary updateButton" type="button">
                    <i class="fa fa-refresh icon-refresh"></i>&nbsp;&nbsp;
                    <div class="displayInlineBlock">{vtranslate('LBL_UPDATE',$QUALIFIED_MODULE)}</div>
                </button>
            </div>
            <br>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>{vtranslate('LBL_LICENSE_KEY', $QUALIFIED_MODULE)}</th>
                    <th>{vtranslate('LBL_LICENSE_NAME', $QUALIFIED_MODULE)}</th>
                    <th>{vtranslate('LBL_LICENSE_DUE_DATE', $QUALIFIED_MODULE)}</th>
                    <th>{vtranslate('LBL_LICENSE_TYPE', $QUALIFIED_MODULE)}</th>
                    <th>{vtranslate('LBL_ACTION', $QUALIFIED_MODULE)}</th>
                </tr>
                </thead>
                <tbody>
                {foreach key=LICENSE_KEY item=LICENSE from=$LICENSES_LIST}
                    {if !$LICENSE}{continue}{/if}
                    {include file="rows/License.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
                {/foreach}
                {if !empty($EMPTY_LICENSES)}
                    <tr>
                        <td style="border-left:none;border-right:none;" colspan="4">
                            {foreach item=EMPTY_LICENSE from=$EMPTY_LICENSES}
                                <div>{$EMPTY_LICENSE}</div>
                                <br>
                            {/foreach}
                        </td>
                        <td style="border-left:none;border-right:none;" title="{vtranslate('LBL_EMPTY_LICENSES_MESSAGE', $QUALIFIED_MODULE)}">
                            <form action="index.php" method="post">
                                <input type="hidden" name="module" value="{$MODULE}">
                                <input type="hidden" name="parent" value="Settings">
                                <input type="hidden" name="action" value="Basic">
                                <input type="hidden" name="mode" value="clearEmptyLicenses">
                                <button class="btn btn-warning">{vtranslate('LBL_EMPTY_LICENSES_BUTTON', $QUALIFIED_MODULE)}</>
                            </form>
                        </td>
                    </tr>
                {/if}
                </tbody>
            </table>
        </div>
        {/if}
        <div class="col-md-12">
            <div>
                <br>
                <div class="marginBottom5px">
                <h4>{vtranslate('LBL_MODULES', $QUALIFIED_MODULE)}</h4>
                </div>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th style="width: 30%;">{vtranslate('Name', $QUALIFIED_MODULE)}</th>
                        <th style="width: 15%;">{vtranslate('LBL_VERSION', $QUALIFIED_MODULE)}</th>
                        <th style="width: 15%;">{vtranslate('LBL_NEWEST_VERSION', $QUALIFIED_MODULE)}</th>
                        <th>{vtranslate('LBL_ACTION', $QUALIFIED_MODULE)}</th>
                    </tr>
                    </thead>
                    <tbody>
                        {foreach item=EXTENSION key=EXTENSION_ID from=$EXTENSIONS_LIST}
                            {if !$EXTENSION->isVisible()}{continue}{/if}
                            {if $EXTENSION->isMultiPackage()}
                                {include file="rows/Package.tpl"|vtemplate_path:$QUALIFIED_MODULE}
                            {else}
                                {include file="rows/Extension.tpl"|vtemplate_path:$QUALIFIED_MODULE}
                            {/if}
                        {/foreach}
                    </tbody>
                </table>
            </div>
            <div>
                <br>
                <div class="marginBottom5px">
                <h4>{vtranslate('LBL_AVAILABLE_LANGUAGES', $QUALIFIED_MODULE)}</h4>
                </div>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th style="width: 30%;">{vtranslate('LBL_LANGUAGE_NAME', $QUALIFIED_MODULE)}</th>
                        <th style="width: 30%;">{vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}</th>
                        <th>{vtranslate('LBL_ACTION', $QUALIFIED_MODULE)}</th>
                    </tr>
                    </thead>
                    <tbody>
                        {foreach item=LANGUAGE from=$LANGUAGES_LIST name=languages}
                            {if $LANGUAGE->isVtigerCompatible() && !$LANGUAGE->isAlreadyExists() && ($LANGUAGE->get('price') eq 'Free' or $LANGUAGE->get('price') eq 0 or $LANGUAGE->get('available') eq 1)}
                                {include file="rows/Language.tpl"|vtemplate_path:$QUALIFIED_MODULE}
                            {/if}
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <br>
{/strip}