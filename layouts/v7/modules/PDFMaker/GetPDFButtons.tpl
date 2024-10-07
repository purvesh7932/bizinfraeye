{* ********************************************************************************
* The content of this file is subject to the PDF Maker Free license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
* ****************************************************************************** *}

{if $ENABLE_PDFMAKER eq 'true'}
    
     <div class="col-sm-4 pull-right" id="PDFMakerContentDiv">
        <div class="row clearfix">
            <div class=" col-lg-4 col-md-4 col-sm-4 pull-right">
                <select class="select2 inputElement" id="igtemp" name="pickListField">
                    <option></option>
                    {foreach key=index item=PICKLIST_FIELD from=$IG_TEMPS}
                        <option value="{$PICKLIST_FIELD[0]}" {if index eq 0} selected {/if}>
                           {$PICKLIST_FIELD[1]}
                        </option>
                    {/foreach}
                </select>
            </div>
                <div class="col-sm-6 padding0px pull-right">
                    <div class="btn-group pull-right">
                        <button class="btn btn-default selectPDFTemplates">{vtranslate('LBL_EXPORT_TO_PDF','PDFMaker')}</button>
                        <button type="button" class="btn btn-default dropdown-toggle dropdown-toggle-split PDFMoreAction" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {vtranslate('LBL_MORE','PDFMaker')}&nbsp;&nbsp;<span class="caret"></span></button>
                        </button>
                            <ul class="dropdown-menu">
                                {include file="GetPDFActions.tpl"|vtemplate_path:'PDFMaker'}
                            </ul>
                        </div>
                    </div>
                </div>
        </div>
    </div>
{/if}