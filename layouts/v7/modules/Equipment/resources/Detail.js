
Vtiger_Detail_Js("Equipment_Detail_Js", {}, {

    DefaulAvailabilitydependency: function () {
        return;
        //by default show hide block based on dropdown values
        let val = $('#eq_commited_avl span').text();
        val = val.trim();
        if (val == 'Availability for Warranty Period') {
            $('[data-block="afbwcpas"]').addClass('hide');
            $('[data-block="saatocp"]').addClass('hide');
            $('[data-block="daadcp"]').addClass('hide');
            $('[data-block="Availability For Warranty Period"]').removeClass('hide');
        } else if (val == 'Availability for both Warranty & Contract Period are Same') {
            $('[data-block="afbwcpas"]').removeClass('hide');
            $('[data-block="Availability For Warranty Period"]').addClass('hide');
            $('[data-block="saatocp"]').addClass('hide');
            $('[data-block="daadcp"]').addClass('hide');
        } else if (val == 'Same availability applicable through out contract period') {
            $('[data-block="saatocp"]').removeClass('hide');
            $('[data-block="Availability For Warranty Period"]').addClass('hide');
            $('[data-block="afbwcpas"]').addClass('hide');
            $('[data-block="daadcp"]').addClass('hide');
        } else if (val == 'Different availability applicable during contract period') {
            $('[data-block="daadcp"]').removeClass('hide');
            $('[data-block="Availability For Warranty Period"]').addClass('hide');
            $('[data-block="afbwcpas"]').addClass('hide');
            $('[data-block="saatocp"]').addClass('hide');
        }
    },

    DefaulMonthlyAvailabilitydependency: function () {
        //by default show hide montly percentage fields
        let rval = $('#eq_mon_available').text();
        rval = rval.trim();
        if (rval == 'Aplicable') {
            $('#Equipment_detailView_fieldLabel_awp_commited_avl_m span').removeClass('hide');
            $('#Equipment_detailView_fieldLabel_afbwcpas_commited_avl_m span').removeClass('hide');
            $('#Equipment_detailView_fieldLabel_saatocp_commited_avl_m_w span').removeClass('hide');
            $('#Equipment_detailView_fieldLabel_saatocp_commited_avl_m_c span').removeClass('hide');

            $('#Equipment_detailView_fieldValue_awp_commited_avl_m span').removeClass('hide');
            $('#Equipment_detailView_fieldValue_afbwcpas_commited_avl_m span').removeClass('hide');
            $('#Equipment_detailView_fieldValue_saatocp_commited_avl_m_w span').removeClass('hide');
            $('#Equipment_detailView_fieldValue_saatocp_commited_avl_m_c span').removeClass('hide');

            $('[data-td="daadcp_avail_mon_percent"]').removeClass('hide');
        }
        else if (rval == 'Not Applicable') {
            $('#Equipment_detailView_fieldLabel_awp_commited_avl_m span').addClass('hide');
            $('#Equipment_detailView_fieldLabel_afbwcpas_commited_avl_m span').addClass('hide');
            $('#Equipment_detailView_fieldLabel_saatocp_commited_avl_m_w span').addClass('hide');
            $('#Equipment_detailView_fieldLabel_saatocp_commited_avl_m_c span').addClass('hide');

            $('#Equipment_detailView_fieldValue_awp_commited_avl_m span').addClass('hide');
            $('#Equipment_detailView_fieldValue_afbwcpas_commited_avl_m span').addClass('hide');
            $('#Equipment_detailView_fieldValue_saatocp_commited_avl_m_w span').addClass('hide');
            $('#Equipment_detailView_fieldValue_saatocp_commited_avl_m_c span').addClass('hide');

            $('[data-td="daadcp_avail_mon_percent"]').addClass('hide');
        }
    },

    DefaulContractAvailabilitydependency: function () {
        //by default show hide contract fields
        let rval = $('#eq_contra_app').text();
        rval = rval.trim();
        if (rval == 'Yes') {
            $('#Equipment_detailView_fieldLabel_total_year_cont span').removeClass('hide');
            $('#Equipment_detailView_fieldLabel_cont_start_date span').removeClass('hide');
            $('#Equipment_detailView_fieldLabel_cont_end_date span').removeClass('hide');
            $('#Equipment_detailView_fieldLabel_run_year_cont span').removeClass('hide');
            $('#Equipment_detailView_fieldLabel_eq_type_of_conrt span').removeClass('hide');

            $('#Equipment_detailView_fieldValue_total_year_cont span').removeClass('hide');
            $('#Equipment_detailView_fieldValue_cont_start_date span').removeClass('hide');
            $('#Equipment_detailView_fieldValue_cont_end_date span').removeClass('hide');
            $('#Equipment_detailView_fieldValue_run_year_cont span').removeClass('hide');
            $('#Equipment_detailView_fieldValue_eq_type_of_conrt span').removeClass('hide');
        }
        else if (rval == 'No') {
            $('#Equipment_detailView_fieldLabel_total_year_cont span').addClass('hide');
            $('#Equipment_detailView_fieldLabel_cont_start_date span').addClass('hide');
            $('#Equipment_detailView_fieldLabel_cont_end_date span').addClass('hide');
            $('#Equipment_detailView_fieldLabel_run_year_cont span').addClass('hide');
            $('#Equipment_detailView_fieldLabel_eq_type_of_conrt span').addClass('hide');

            $('#Equipment_detailView_fieldValue_total_year_cont span').addClass('hide');
            $('#Equipment_detailView_fieldValue_cont_start_date span').addClass('hide');
            $('#Equipment_detailView_fieldValue_cont_end_date span').addClass('hide');
            $('#Equipment_detailView_fieldValue_run_year_cont span').addClass('hide');
            $('#Equipment_detailView_fieldValue_eq_type_of_conrt span').addClass('hide');
            $('#Equipment_detailView_fieldValue_eq_available_for').css('pointer-events', 'none');
        }
    },

    DefaulEquipmentAvailabilitydependency: function () {
        //by default show hide contract fields
        let rval = $('#eq_available').text();
        rval = rval.trim();
        if (rval == 'Aplicable') {
            $('#Equipment_detailView_fieldLabel_eq_available_for span').removeClass('hide');
            $('#Equipment_detailView_fieldLabel_maint_h_app_for_ac span').removeClass('hide');
            $('#Equipment_detailView_fieldLabel_eq_mon_available span').removeClass('hide');
            $('#Equipment_detailView_fieldLabel_eq_war_app_cp span').removeClass('hide');
            $('#Equipment_detailView_fieldLabel_eq_war_app_wp span').removeClass('hide');
            $('#Equipment_detailView_fieldLabel_eq_commited_avl span').removeClass('hide');

            $('#Equipment_detailView_fieldValue_eq_available_for span').removeClass('hide');
            $('#Equipment_detailView_fieldValue_maint_h_app_for_ac span').removeClass('hide');
            $('#Equipment_detailView_fieldValue_eq_mon_available span').removeClass('hide');
            $('#Equipment_detailView_fieldValue_eq_war_app_cp span').removeClass('hide');
            $('#Equipment_detailView_fieldValue_eq_war_app_wp span').removeClass('hide');
            $('#Equipment_detailView_fieldValue_eq_commited_avl span').removeClass('hide');
        }
        else if (rval == 'Not Applicable') {
            $('#Equipment_detailView_fieldLabel_eq_available_for span').addClass('hide');
            $('#Equipment_detailView_fieldLabel_maint_h_app_for_ac span').addClass('hide');
            $('#Equipment_detailView_fieldLabel_eq_mon_available span').addClass('hide');
            $('#Equipment_detailView_fieldLabel_eq_war_app_cp span').addClass('hide');
            $('#Equipment_detailView_fieldLabel_eq_war_app_wp span').addClass('hide');
            $('#Equipment_detailView_fieldLabel_eq_commited_avl span').addClass('hide');

            $('#Equipment_detailView_fieldValue_eq_available_for span').addClass('hide');
            $('#Equipment_detailView_fieldValue_maint_h_app_for_ac span').addClass('hide');
            $('#Equipment_detailView_fieldValue_eq_mon_available span').addClass('hide');
            $('#Equipment_detailView_fieldValue_eq_war_app_cp span').addClass('hide');
            $('#Equipment_detailView_fieldValue_eq_war_app_wp span').addClass('hide');
            $('#Equipment_detailView_fieldValue_eq_commited_avl span').addClass('hide');
        }
    },

    registerEvents: function () {
        var self = this;
        this._super();
        // $('[data-block="Availability For Warranty Period"]').addClass('hide');
        // $('[data-block="afbwcpas"]').addClass('hide');
        // $('[data-block="saatocp"]').addClass('hide');
        // $('[data-block="daadcp"]').addClass('hide');
        $('[data-td="awp_commited_avl_m"]').addClass('hide');
        $('[data-td="afbwcpas_commited_avl_m"]').addClass('hide');
        $('[data-td="saatocp_commited_avl_m_w"]').addClass('hide');
        $('[data-td="saatocp_commited_avl_m_c"]').addClass('hide');

        //contract
        $('[data-td="total_year_cont"]').addClass('hide');
        $('[data-td="cont_start_date"]').addClass('hide');
        $('[data-td="cont_end_date"]').addClass('hide');
        $('[data-td="run_year_cont"]').addClass('hide');
        $('[data-td="eq_type_of_conrt"]').addClass('hide');

        //equipements
        $('[data-td="eq_available_for"]').addClass('hide');
        $('[data-td="maint_h_app_for_ac"]').addClass('hide');
        $('[data-td="eq_mon_available"]').addClass('hide');
        $('[data-td="eq_war_app_cp"]').addClass('hide');
        $('[data-td="eq_war_app_wp"]').addClass('hide');
        $('[data-td="eq_commited_avl"]').addClass('hide');

        this.DefaulAvailabilitydependency();
        this.DefaulContractAvailabilitydependency();
        this.DefaulEquipmentAvailabilitydependency();
        this.DefaulMonthlyAvailabilitydependency();
    },

});