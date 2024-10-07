
jQuery.Class("Vtiger_Header_Js", {
   
    previewFile : function(e,recordId) {
        e.stopPropagation();
        var currentTarget = e.currentTarget;
        var currentTargetObject = jQuery(currentTarget);
        if(typeof recordId == 'undefined') {
            if(currentTargetObject.closest('tr').length) {
                recordId = currentTargetObject.closest('tr').data('id');
            } else {
                recordId = currentTargetObject.data('id');
            }
        }
        var fileLocationType = currentTargetObject.data('filelocationtype');
        var fileName = currentTargetObject.data('filename'); 
        if(fileLocationType == 'I'){
            var params = {
                module : 'Documents',
                view : 'FilePreview',
                record : recordId
            };
            app.request.post({"data":params}).then(function(err,data){
                app.helper.showModal(data);
            });
        } else {
            var win = window.open(fileName, '_blank');
            win.focus();
        }
    },
    fileView:function(recordId,id){
        var params = {
            module : 'HelpDesk',
            view : 'FilePreview',
            record : recordId,
            atid  :id,
        };
        app.request.post({"data":params}).then(function(err,data){
            app.helper.showModal(data);
            console.log(data)
        });
    },
},{
});