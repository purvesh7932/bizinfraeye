jQuery.Class("PDFMaker_CkEditor_Js", {}, {
    ckEditorInstance: false,

    getckEditorInstance: function() {
        if (this.ckEditorInstance == false) {
            this.ckEditorInstance = new Vtiger_CkEditor_Js();
        }
        return this.ckEditorInstance;
    },
    registerEvents: function() {
        var thisInstance = this;
        var ckEditorInstance = this.getckEditorInstance();
    }
});
jQuery(document).ready(function() {
    var PDFMakerCkEditorJsInstance = new PDFMaker_CkEditor_Js();
    PDFMakerCkEditorJsInstance.registerEvents();
});
