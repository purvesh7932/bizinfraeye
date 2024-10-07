Vtiger_Detail_Js("PDFMaker_DetailFree_Js",{

    setPreviewContent : function(type){
        var previewcontent =  jQuery('#previewcontent_'+type).html();
        var previewFrame = document.getElementById('preview_'+type);
        var preview =  previewFrame.contentDocument ||  previewFrame.contentWindow.document;
        preview.open();
        preview.write(previewcontent);
        preview.close();
        jQuery('#previewcontent_'+type).html('');
    }

    },{
});