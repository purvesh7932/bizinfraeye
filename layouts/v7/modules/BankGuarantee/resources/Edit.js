Vtiger_Edit_Js("BankGuarantee_Edit_Js", {}, {

    registerBasicEvents: function (container) {
        this._super(container);
        this.reasonForExtension();
        this.availability();
    },

    reasonForExtension: function () {

        var label = $(".fieldLabel");
        var field = $(".fieldValue");
        for (let i = 0; i < label.length; i++) {
            $(label[i]).attr("id", "id_label" + i);
            $(field[i]).attr("id", "id_field" + i);
        }
        $("#id_label15").addClass("hide");
        $("#id_field15").addClass("hide");

        $('select[name="bnk_pre_status"]').on('change', function () {
            if ($('select[name="bnk_pre_status"]').val() == "Extended") {
                $("#id_label15").removeClass("hide");
                $("#id_field15").removeClass("hide");
            } else {
                $("#id_label15").addClass("hide");
                $("#id_field15").addClass("hide");
            }
        });
    },
    availability: function () {
        $("#id_label16").addClass("hide");
        $("#id_field16").addClass("hide");

        $('select[name="bnk_pre_status"]').on('change', function () {
            if ($('select[name="bnk_pre_status"]').val() == "Availability Signed") {
                $("#id_label16").removeClass("hide");
                $("#id_field16").removeClass("hide");
            } else {
                $("#id_label16").addClass("hide");
                $("#id_field16").addClass("hide");
            }
        });
    }
});
