
Inventory_Detail_Js("FailedParts_Detail_Js", {
    handleREdirectionAndCReation: function () {
        var test = new Array();
        $("input[name='selectedIds']:checked").each(function () {
            test.push($(this).val());
        });
        let lengthOfSelectedIds = test.length;
        if (lengthOfSelectedIds <= 0) {
            // app.helper.showAlertNotification({
            //     'message': 'Please Select Atleast One Line Item'
            // });
            alert("Please Select Atleast One Line Item");
        } else {
            let selectedIds = JSON.stringify(test);
            let soCreateURL = jQuery('#SO_CREATE_URL').val() + '&selectedIds=' + selectedIds;
            soCreateURL = soCreateURL.replace(/[\\]/g,'');
            window.location = soCreateURL;
        }
    }
}, {

    postMailSentEvent: function () {
        window.location.reload();
    },

    Defaulstatusdependency: function () {
        let val = $('#fail_pa_pa_status').text();
        val = val.trim();
        if (val == "Closed") {
            $('#pending_days').text(0);
        }
    },

    registerEvents: function () {
        var self = this;
        this._super();
        this.Defaulstatusdependency();
        // this.handleREdirectionAndCReation();
        // this.handleCollapse();
    },
    handleCollapse: function () {
        let coll = document.getElementsByClassName("collapsible");
        let i;
        for (i = 0; i < coll.length; i++) {
            coll[i].addEventListener("click", function () {
                if (this.innerText == 'Show Details') {
                    this.innerText = 'Hide Details';
                } else {
                    this.innerText = 'Show Details';
                }
                this.classList.toggle("activeIgmenu");
                let content = this.nextElementSibling;
                if (content.style.display === "block") {
                    content.style.display = "none";
                } else {
                    content.style.display = "block";
                }
            });
        }
    },
    handleREdirectionAndCReation: function () {
        $("#createSalesOrderButton").on('click', function () {
            var test = new Array();
            $("input[name='selectedIds']:checked").each(function () {
                test.push($(this).val());
            });
            let lengthOfSelectedIds = test.length;
            if (lengthOfSelectedIds <= 0) {
                app.helper.showAlertNotification({
                    'message': 'Please Select Atleast One Line Item'
                });
            } else {
                let selectedIds = JSON.stringify(test);
                let soCreateURL = jQuery('#SO_CREATE_URL').val() + '&selectedIds=' + selectedIds;
                window.location = soCreateURL;
            }
        });
    },

});