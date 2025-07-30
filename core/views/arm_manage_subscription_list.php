<?php
global $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_member_forms ,$arm_subscription_class, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways,$armPrimaryStatus;
$date_format = $arm_global_settings->arm_get_wp_date_format();
$user_roles = get_editable_roles();
$nowDate = current_time('mysql');
$all_plans = $arm_subscription_plans->arm_get_all_subscription_plans();
$payment_gateways = $arm_payment_gateways->arm_get_all_payment_gateways();
$filter_search = (!empty($_POST['search'])) ? sanitize_text_field($_POST['search']) : ''; //phpcs:ignore
$filter_plan_status = isset($_REQUEST['arm_filter_status']) ? sanitize_text_field($_REQUEST['arm_filter_status']) : ''; //phpcs:ignore
$filter_gateway = isset($_REQUEST['arm_filter_gateway']) ? sanitize_text_field($_REQUEST['arm_filter_gateway']) : ''; //phpcs:ignore
$filter_plan_id = (!empty($_REQUEST['arm_subs_plan_filter']) && $_REQUEST['arm_subs_plan_filter'] != '0') ? sanitize_text_field($_REQUEST['arm_subs_plan_filter']) : ''; //phpcs:ignore
$filter_ptype = isset($_REQUEST['arm_filter_ptype']) ? sanitize_text_field($_REQUEST['arm_filter_ptype']) : ''; //phpcs:ignore
$selected_filtered_tab = isset($_REQUEST['selected_tab']) ? sanitize_text_field($_REQUEST['selected_tab']) : 'activity'; //phpcs:ignore

?>

<style type="text/css" title="currentStyle">
	.paginate_page a{display:none;}
	#poststuff #post-body {margin-top: 32px;}
	.ColVis_Button{display:none;}
</style>

<script type="text/javascript" charset="utf-8">
// <![CDATA[

jQuery(document).ready(function () {


    jQuery('#armsubscriptionsearch_new').on('keyup', function (e) {
        e.stopPropagation();
        if (e.keyCode == 13) {
            arm_load_subscription_grid_after_filtered();
            return false;
        }
    });

    
    jQuery('#armember_datatable').dataTable().fnDestroy(); 
    jQuery('#armember_datatable_1').dataTable().fnDestroy(); 
    jQuery('#armember_datatable_1_div #armember_datatable_1').dataTable().fnDestroy();
    jQuery('#armember_datatable_2').dataTable().fnDestroy();
    arm_load_subscription_list_grid();

    jQuery('.arm_subscription_tabs .arm_all_subscription_tab').on('click',function(e){
        e.stopPropagation();
        jQuery('#arm_selected_sub_tab').val('subscriptions');
        jQuery('.arm_subscription_tabs .arm_all_subscription_tab').addClass('arm_selected_sub_tab');
        if(jQuery('.arm_all_activities_tab').hasClass('arm_selected_sub_tab'))
        {
            jQuery('.arm_all_activities_tab').removeClass('arm_selected_sub_tab');
        }
        if(jQuery('.arm_upcoming_subscription_tab').hasClass('arm_selected_sub_tab'))
        {
            jQuery('.arm_upcoming_subscription_tab').removeClass('arm_selected_sub_tab')
        }
       
        jQuery('.arm_filter_status_activity_box').addClass('arm_hide');
        jQuery('.arm_filter_status_subscription_box').removeClass('arm_hide');
        jQuery('.armember_activity_datatable_div').addClass('arm_hide');
        jQuery('.armember_subscription_datatable_div').removeClass('arm_hide');
        jQuery('.armember_upcoming_datatable_div').addClass('arm_hide');
        arm_load_subscription_list_grid();
    });

    jQuery('.arm_subscription_tabs .arm_all_activities_tab').on('click',function(e){
        e.stopPropagation();
        jQuery('#arm_selected_sub_tab').val('activity');
        jQuery('.arm_subscription_tabs .arm_all_activities_tab').addClass('arm_selected_sub_tab');
        if(jQuery('.arm_all_subscription_tab').hasClass('arm_selected_sub_tab'))
        {
            jQuery('.arm_all_subscription_tab').removeClass('arm_selected_sub_tab')
        }
        if(jQuery('.arm_upcoming_subscription_tab').hasClass('arm_selected_sub_tab'))
        {
            jQuery('.arm_upcoming_subscription_tab').removeClass('arm_selected_sub_tab')
        }
        jQuery('.arm_filter_status_activity_box').removeClass('arm_hide');
        jQuery('.arm_filter_status_subscription_box').addClass('arm_hide');
        jQuery('.armember_activity_datatable_div').removeClass('arm_hide');
        jQuery('.armember_subscription_datatable_div').addClass('arm_hide');
        jQuery('.armember_upcoming_datatable_div').addClass('arm_hide');
        arm_load_subscription_list_grid();
    });
    jQuery('.arm_subscription_tabs .arm_upcoming_subscription_tab').on('click',function(e){
        e.stopPropagation();
        jQuery('#arm_selected_sub_tab').val('upcoming');
        jQuery('.arm_subscription_tabs .arm_upcoming_subscription_tab').addClass('arm_selected_sub_tab');
        if(jQuery('.arm_all_activities_tab').hasClass('arm_selected_sub_tab'))
        {
            jQuery('.arm_all_activities_tab').removeClass('arm_selected_sub_tab');
        }
        if(jQuery('.arm_all_subscription_tab').hasClass('arm_selected_sub_tab'))
        {
            jQuery('.arm_all_subscription_tab').removeClass('arm_selected_sub_tab');
        }
        jQuery('.arm_filter_status_activity_box').addClass('arm_hide');
        jQuery('.arm_filter_status_subscription_box').addClass('arm_hide');
        jQuery('.armember_activity_datatable_div').addClass('arm_hide');
        jQuery('.armember_subscription_datatable_div').addClass('arm_hide');
        jQuery('.armember_upcoming_datatable_div').removeClass('arm_hide');
        arm_load_subscription_list_grid();
    });

    
});

function arm_load_subscription_grid_after_filtered() {
    jQuery('#arm_subscription_grid_filter_btn').attr('disabled', 'disabled');
    jQuery('#armember_datatable').dataTable().fnDestroy();
    jQuery('#armember_datatable_1').dataTable().fnDestroy();
    jQuery('#armember_datatable_2').dataTable().fnDestroy();
    arm_load_subscription_list_grid();
}

function show_grid_loader() {
    jQuery(".arm_hide_datatable").css('visibility', 'hidden');
    jQuery('.arm_loading_grid').show();
}

function arm_load_subscription_list_grid(is_filtered){
	var __ARM_Showing = '<?php echo addslashes(esc_html__('Showing','armember-membership')); //phpcs:ignore?>';
    var __ARM_Showing_empty = '<?php echo addslashes(esc_html__('Showing 0 to 0 of 0 Subscriptions','armember-membership')); //phpcs:ignore?>';
    var __ARM_to = '<?php echo addslashes(esc_html__('to','armember-membership')); //phpcs:ignore?>';
    var __ARM_of = '<?php echo addslashes(esc_html__('of','armember-membership')); //phpcs:ignore?>';
    var __ARM_Entries = ' <?php echo addslashes(esc_html__('Subscriptions','armember-membership')); //phpcs:ignore?>';
    var __ARM_Show = '<?php echo addslashes(esc_html__('Show','armember-membership')); //phpcs:ignore?> ';
    var __ARM_NO_FOUND = '<?php echo addslashes(esc_html__('No Subscriptions found.','armember-membership')); //phpcs:ignore?>';
    var __ARM_NO_MATCHING = '<?php echo addslashes(esc_html__('No matching records found.','armember-membership')); //phpcs:ignore?>';
    var __ARM_subscription_List_right = [7];
    var __ARM_Activity_List_right = [4];
    
	var ajax_url = '<?php echo admin_url("admin-ajax.php"); //phpcs:ignore?>';

    var filtered_data = (typeof is_filtered !== 'undefined' && is_filtered !== false) ? true : false;
    var arm_subs_filter = jQuery('#arm_subs_plan_filter').val();
    var pstatus = jQuery('#arm_status_filter').val();
    var pstatus_sub = jQuery('#arm_status_subscription_filter').val();
    var gateway = jQuery('#arm_filter_gateway').val();
    var ptype = jQuery('#arm_filter_ptype').val();
    var selected_tab = jQuery('#arm_selected_sub_tab').val();
    var search = jQuery('#armsubscriptionsearch_new').val();
    var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();
	
    if(selected_tab == 'activity')
    {
        <?php
            $sortables = '0,1,2,4,5, 6, 7';
            $order = 3;
            $center = 5;
            if($ARMemberLite->is_arm_pro_active){
                $sortables = '2,3,4,5, 6, 7';
                $order = 0;
                $center = 6;
            }
        ?>
        var oTables = jQuery('#armember_datatable').dataTable({
            "oLanguage": {
                "sProcessing": show_grid_loader(),
                "sInfo": __ARM_Showing + " _START_ " + __ARM_to + " _END_ " + __ARM_of + " _TOTAL_ " + __ARM_Entries,
                "sInfoEmpty": __ARM_Showing_empty,
                
                "sLengthMenu": __ARM_Show + "_MENU_" + __ARM_Entries,
                "sEmptyTable": __ARM_NO_FOUND,
                "sZeroRecords": __ARM_NO_MATCHING,
            },
            "bDestroy": true,
            "language":{
                "searchPlaceholder":"<?php esc_html_e( 'Search', 'armember-membership' ); ?>",
                "search":"",
            },
            "bFilter": false,
            "bProcessing": false,
            "bServerSide": true,
            "sAjaxSource": ajax_url,
            "sServerMethod": "POST",
            "fnServerParams": function (aoData) {
                aoData.push({'name': 'action', 'value': 'get_activity_data'});
                aoData.push({"name": "payment_type", "value": ptype});
                aoData.push({"name": "plan_status", "value": pstatus});
                aoData.push({"name": "arm_subs_plan_filter", "value": arm_subs_filter});
                aoData.push({"name": "payment_gateway", "value": gateway});
                aoData.push({"name": "sSearch", "value": search});
                aoData.push({"name": "selected_tab", "value": selected_tab});
                aoData.push({"name": "sColumns", "value": null});
                aoData.push({"name": "_wpnonce", "value": _wpnonce});
            },
            "order": [[<?php echo esc_html($order);?>, 'desc']],
            "bRetrieve": false,
            "sDom": '<"H"Cfr>t<"footer"ipl>',
            "sPaginationType": "four_button",
            "bJQueryUI": true,
            "bPaginate": true,
            "bAutoWidth": false,
            "sScrollX": "100%",
            "bScrollCollapse": true,
            "aoColumnDefs": [
                {"bSortable": false, "aTargets": [<?php echo esc_html($sortables);?>]},
                {"sClass": "dt-right", "aTargets": __ARM_Activity_List_right},
                {"sClass": "center", "aTargets": [<?php echo esc_html($center);?>]},
            ],
            "fixedColumns": false,
            "bStateSave": true,
            "iCookieDuration": 60 * 60,
            "sCookiePrefix": "arm_datatable_",
            "aLengthMenu": [10, 25, 50, 100, 150, 200],
            "fnStateSave": function (oSettings, oData) {
                oData.aaSorting = [];
                oData.abVisCols = [];
                this.oApi._fnCreateCookie(
                    oSettings.sCookiePrefix + oSettings.sInstance,
                    this.oApi._fnJsonString(oData),
                    oSettings.iCookieDuration,
                    oSettings.sCookiePrefix,
                    oSettings.fnCookieCallback
                );
            },
            "stateSaveParams":function(oSettings,oData){
                oData.start=0;
            },
            "fnStateLoadParams": function (oSettings, oData) {
                oData.iLength = 10;
                oData.iStart = 0;
                //oData.oSearch.sSearch = db_search_term;
            },
            "fnPreDrawCallback": function () {
                show_grid_loader();
            },
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                jQuery(nRow).find('.arm_grid_action_btn_container').each(function () {
                    jQuery(this).parent().addClass('armGridActionTD');
                    jQuery(this).parent().attr('data-key', 'armGridActionTD');
                });
            },
            
            "fnDrawCallback": function (oSettings) {
                jQuery('.arm_loading_grid').hide();
                arm_show_data();
                jQuery("#cb-select-all-1").prop("checked", false);
                arm_selectbox_init();
                jQuery('#arm_filter_wrapper').hide();
                filtered_data = false;
                if (jQuery.isFunction(jQuery().tipso)) {
                    jQuery('.armhelptip').each(function () {
                        jQuery(this).tipso({
                            position: 'top',
                            size: 'small',
                            background: '#939393',
                            color: '#ffffff',
                            width: false,
                            maxWidth: 400,
                            useTitle: true
                        });
                    });
                }
                oTables.dataTable().fnAdjustColumnSizing(false);
                jQuery('#arm_subscription_grid_filter_btn').removeAttr('disabled');
            }
        });
    }
    if(selected_tab == 'subscriptions')
    {
        var oTables = jQuery('#armember_datatable_1').dataTable({
            "oLanguage": {
                "sProcessing": show_grid_loader(),
                "sInfo": __ARM_Showing + " _START_ " + __ARM_to + " _END_ " + __ARM_of + " _TOTAL_ " + __ARM_Entries,
                "sInfoEmpty": __ARM_Showing_empty,
                
                "sLengthMenu": __ARM_Show + "_MENU_" + __ARM_Entries,
                "sEmptyTable": __ARM_NO_FOUND,
                "sZeroRecords": __ARM_NO_MATCHING,
            },
            "bDestroy": true,
            "language":{
                "searchPlaceholder": "<?php esc_html_e( 'Search', 'armember-membership' ); ?>",
                "search":"",
            },
            "bFilter": false,
            "bProcessing": false,
            "bServerSide": true,
            "sAjaxSource": ajax_url,
            "sServerMethod": "POST",
            "fnServerParams": function (aoData) {
                aoData.push({'name': 'action', 'value': 'get_subscription_data'});
                aoData.push({"name": "payment_type", "value": ptype});
                aoData.push({"name": "plan_status", "value": pstatus_sub});
                aoData.push({"name": "arm_subs_filter", "value": arm_subs_filter});
                aoData.push({"name": "payment_gateway", "value": gateway});
                aoData.push({"name": "sSearch", "value": search});
                aoData.push({"name": "selected_tab", "value": selected_tab});
                aoData.push({"name": "sColumns", "value": null});
                aoData.push({"name": "_wpnonce", "value": _wpnonce});
            },
            "bRetrieve": false,
            "sDom": '<"H"Cfr>t<"footer"ipl>',
            "sPaginationType": "four_button",
            "bJQueryUI": true,
            "bPaginate": true,
            "bAutoWidth": false,
            "sScrollX": "100%",
            "bScrollCollapse": true,
            "oColVis": {
                "aiExclude": [0]
            },
            "aoColumnDefs": [                
                {"sClass": "center", "aTargets": [0,8,9]},
                {"bSortable": false, "aTargets": [0,2,3,4,5, 6, 7, 8,9,10] },
                {"aTargets":[0],"sClass":"noVis"},
                {"sClass": "dt-right", "aTargets": __ARM_subscription_List_right},
            ],
            "order": [[1, 'desc']],
            "fixedColumns": false,
            "bStateSave": true,
            "iCookieDuration": 60 * 60,
            "sCookiePrefix": "arm_datatable_",
            "aLengthMenu": [10, 25, 50, 100, 150, 200],
            "fnStateSave": function (oSettings, oData) {
                oData.aaSorting = [];
                oData.abVisCols = [];
                oData.aoSearchCols = [];
                this.oApi._fnCreateCookie(
                    oSettings.sCookiePrefix + oSettings.sInstance,
                    this.oApi._fnJsonString(oData),
                    oSettings.iCookieDuration,
                    oSettings.sCookiePrefix,
                    oSettings.fnCookieCallback
                );
            },
            "stateSaveParams":function(oSettings,oData){
                oData.start=0;
            },
            "aaSorting": [[1, 'desc']],
            "fnStateLoadParams": function (oSettings, oData) {
                oData.iLength = 10;
                oData.iStart = 1;
                //oData.oSearch.sSearch = db_search_term;
            },
            "fnPreDrawCallback": function () {
                show_grid_loader();
            },
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                jQuery(nRow).find('.arm_grid_action_btn_container').each(function () {
                    jQuery(this).parent().addClass('armGridActionTD');
                    jQuery(this).parent().attr('data-key', 'armGridActionTD');
                    if(jQuery(this).html()==""){
                        jQuery(this).hide(0); 
                    }
                });
            },
            
            "fnDrawCallback": function (oSettings) {
                jQuery('.arm_loading_grid').hide();
                arm_show_data();
                jQuery("#cb-select-all-1").prop("checked", false);
                arm_selectbox_init();
                jQuery('#arm_filter_wrapper').hide();
                filtered_data = false;
                if (jQuery.isFunction(jQuery().tipso)) {
                    jQuery('.armhelptip').each(function () {
                        jQuery(this).tipso({
                            position: 'top',
                            size: 'small',
                            background: '#939393',
                            color: '#ffffff',
                            width: false,
                            maxWidth: 400,
                            useTitle: true
                        });
                    });
                }
                oTables.dataTable().fnAdjustColumnSizing(false);
                jQuery('#arm_subscription_grid_filter_btn').removeAttr('disabled');
            }
        });
    }
    if(selected_tab == 'upcoming')
    {
        var oTables = jQuery('#armember_datatable_2').dataTable({
            "oLanguage": {
                "sProcessing": show_grid_loader(),
                "sInfo": __ARM_Showing + " _START_ " + __ARM_to + " _END_ " + __ARM_of + " _TOTAL_ " + __ARM_Entries,
                "sInfoEmpty": __ARM_Showing_empty,
                
                "sLengthMenu": __ARM_Show + "_MENU_" + __ARM_Entries,
                "sEmptyTable": __ARM_NO_FOUND,
                "sZeroRecords": __ARM_NO_MATCHING,
            },
            "bDestroy": true,
            "language":{
                "searchPlaceholder": "Search",
                "search":"",
            },
            "bProcessing": false,
            "bServerSide": true,
            "sAjaxSource": ajax_url,
            "sServerMethod": "POST",
            "fnServerParams": function (aoData) {
                aoData.push({'name': 'action', 'value': 'get_upcoming_subscription_data'});
                aoData.push({"name": "payment_type", "value": ptype});
                aoData.push({"name": "plan_status", "value": pstatus_sub});
                aoData.push({"name": "arm_subs_filter", "value": arm_subs_filter});
                aoData.push({"name": "payment_gateway", "value": gateway});
                aoData.push({"name": "sSearch", "value": search});
                aoData.push({"name": "selected_tab", "value": selected_tab});
                aoData.push({"name": "sColumns", "value": null});
                aoData.push({"name": "_wpnonce", "value": _wpnonce});
            },
            "bRetrieve": false,
            "sDom": '<"H"Cfr>t<"footer"ipl>',
            "sPaginationType": "four_button",
            "bJQueryUI": true,
            "bFilter": false,
            "bPaginate": true,
            "bAutoWidth": false,
            "sScrollX": "100%",
            "bScrollCollapse": true,
            "oColVis": {
                "aiExclude": [0]
            },
            "aoColumnDefs": [                
                {"sClass": "center", "aTargets": [0]},
                {"bSortable": false, "aTargets": [0,1,2,3,4,5, 6, 7] },
                {"sClass": "dt-right", "aTargets": [6]},
            ],
            "order": [],
            "fixedColumns": false,
            "bStateSave": true,
            "iCookieDuration": 60 * 60,
            "sCookiePrefix": "arm_datatable_",
            "aLengthMenu": [10, 25, 50, 100, 150, 200],
            "fnStateSave": function (oSettings, oData) {
                oData.abVisCols = [];
                oData.aoSearchCols = [];
                this.oApi._fnCreateCookie(
                    oSettings.sCookiePrefix + oSettings.sInstance,
                    this.oApi._fnJsonString(oData),
                    oSettings.iCookieDuration,
                    oSettings.sCookiePrefix,
                    oSettings.fnCookieCallback
                );
            },
            "stateSaveParams":function(oSettings,oData){
                oData.start=0;
            },
            "fnStateLoadParams": function (oSettings, oData) {
                oData.iLength = 10;
                oData.iStart = 1;
                //oData.oSearch.sSearch = db_search_term;
            },
            "fnPreDrawCallback": function () {
                show_grid_loader();
            },
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                jQuery(nRow).find('.arm_grid_action_btn_container').each(function () {
                    jQuery(this).parent().addClass('armGridActionTD');
                    jQuery(this).parent().attr('data-key', 'armGridActionTD');
                    if(jQuery(this).html()==""){
                        jQuery(this).hide(0); 
                    }
                });
            },
            
            "fnDrawCallback": function (oSettings) {
                jQuery('.arm_loading_grid').hide();
                arm_show_data();
                jQuery("#cb-select-all-1").prop("checked", false);
                arm_selectbox_init();
                jQuery('#arm_filter_wrapper').hide();
                filtered_data = false;
                if (jQuery.isFunction(jQuery().tipso)) {
                    jQuery('.armhelptip').each(function () {
                        jQuery(this).tipso({
                            position: 'top',
                            size: 'small',
                            background: '#939393',
                            color: '#ffffff',
                            width: false,
                            maxWidth: 400,
                            useTitle: true
                        });
                    });
                }
                oTables.dataTable().fnAdjustColumnSizing(false);
                jQuery('#arm_subscription_grid_filter_btn').removeAttr('disabled');
            }
        });
    }
}

function ChangeID(id) {
	document.getElementById('delete_id').value = id;
}

jQuery(document).on('click', '.arm_show_user_more_transactions', function () {
    var id = jQuery(this).attr('data-id');
    var plan_id = jQuery(this).attr('data-planid');
    var tr = jQuery(this).closest('tr');
    var class_name = jQuery(this).closest('tr').attr('class');
    var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();
    var row = jQuery('#armember_datatable_1').DataTable().row(tr);
        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            tr.addClass('hide');
        }
        else {
            // Open this row
            row.child.show();
            tr.removeClass('hide');
            row.child(format(id,_wpnonce), class_name +" "+"arm_child_transaction_row").show();
            tr.addClass('shown');
        }
});
function format(d,_wpnonce) {
    
    var response1 = '</div><div class="arm_child_row_div_'+d+'"><img class="arm_load_subscription_plans" src="<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore?>/arm_loader.gif" alt="<?php esc_attr_e('Load More', 'armember-membership'); ?>" style="  margin-left: 530px; padding: 10px;"></div>';

    setTimeout(function () { 
        jQuery.ajax({
            type: "POST",
            url: __ARMAJAXURL,
            data: "action=get_user_all_transaction_details_for_grid&activity_id=" + d + "&_wpnonce=" + _wpnonce,
            dataType: 'html',
            success: function (response) {
                jQuery('.arm_child_row_div_'+d).html('<div class="arm_member_grid_arrow"></div>'+response);
            }
        });
    },200);
    return response1;
}

// ]]>
</script>

<?php

$get_msg = !empty($_GET['msg']) ? esc_html( sanitize_text_field($_GET['msg']) ) : ''; //phpcs:ignore
if( isset( $_GET['status'] ) && 'success' == $_GET['status'] ){ //phpcs:ignore
	echo "<script type='text/javascript'>";
		echo "jQuery(document).ready(function(){";
			echo "armToast('" . esc_attr($get_msg) . "','success');";
			echo "var pageurl = ArmRemoveVariableFromURL( document.URL, 'status' );";  
			echo "pageurl = ArmRemoveVariableFromURL( pageurl, 'msg' );";  
			echo "window.history.pushState( { path: pageurl }, '', pageurl );";
		echo "});";
	echo "</script>";
}

$filter_search = (!empty($_POST['search'])) ? sanitize_text_field($_POST['search']) : '';//phpcs:ignore

global $wpdb, $ARMember, $arm_global_settings;
?>
<div class="wrap arm_page arm_subscription_main_wrapper">
	<div class="content_wrapper arm_subscription_wrapper arm_position_relative" id="content_wrapper" >
		<div class="arm_loading_grid" style="display: none;"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore?>/loader.gif" alt="Loading.."></div>
		<div class="page_title">
			<?php esc_html_e('Manage Subscriptions','armember-membership');?>
			<div class="arm_add_new_item_box">
				<a class="greensavebtn arm_add_subscriptions_link" href="javascript:void(0);"><img align="absmiddle" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore?>/add_new_icon.png"><span><?php esc_html_e('Add Subscription', 'armember-membership') ?></span></a>
			</div>	
			<div class="armclear"></div>
		</div>

		<div class="armclear"></div>

		<div class="arm_subscriptions_list arm_main_wrapper_seperator" >
			<form method="GET" id="subscription_plans_list_form" class="data_grid_list">
				<input type="hidden" name="page" value="<?php echo isset( $arm_slugs->arm_manage_subscriptions ) ? esc_attr($arm_slugs->arm_manage_subscriptions) : '';?>" />
				<input type="hidden" name="armaction" value="list" />

                <div class="arm_datatable_filters">
                    <div class="arm_dt_filter_block arm_datatable_searchbox">
                        <label><input type="text" placeholder="<?php esc_html_e('Search by Username', 'armember-membership'); ?>" id="armsubscriptionsearch_new" value="<?php echo esc_attr($filter_search); ?>" class="arm_mng_sbscr_srch_inpt" tabindex="-1"></label>
                        <!--./====================Begin Filter By Plan Box====================/.-->
                        <?php if (!empty($all_plans)){ ?>
                            <div class="arm_filter_plans_box arm_datatable_filter_item">                        
                                <input type="hidden" id="arm_subs_plan_filter" class="arm_subs_filter" value="<?php echo esc_attr($filter_plan_id); ?>" />
                                <dl class="arm_multiple_selectbox arm_width_190">
                                    <dt><span><?php esc_html_e('Select Memberships', 'armember-membership'); ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd>
                                        <ul data-id="arm_subs_plan_filter" data-placeholder="<?php esc_html_e('Select Memberships', 'armember-membership'); ?>">
                                            <?php foreach ($all_plans as $plan): ?>
                                                <li data-label="<?php echo stripslashes(esc_attr($plan['arm_subscription_plan_name'])); ?>" data-value="<?php echo esc_attr($plan['arm_subscription_plan_id']); ?>"><input type="checkbox" class="arm_icheckbox" value="<?php echo esc_attr($plan['arm_subscription_plan_id']); ?>"/><?php echo stripslashes( esc_html($plan['arm_subscription_plan_name']) ); //phpcs:ignore?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </dd>
                                </dl>
                            </div>
                            <?php
                            }
                            ?>
                            <div class="arm_filter_status_activity_box arm_datatable_filter_item arm_hide">                        
                                <input type="hidden" id="arm_status_filter" class="arm_status_filter" value="<?php echo esc_attr($filter_plan_status); ?>" />
                                <dl class="arm_selectbox arm_width_190">
                                    <dt><span><?php esc_html_e('Select Status', 'armember-membership'); ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd>
                                        <ul data-id="arm_status_filter" data-placeholder="<?php esc_attr_e('Select Status', 'armember-membership'); ?>">
                                            <li data-label="<?php esc_attr_e('Select Status', 'armember-membership'); ?>" data-value="0" ><?php esc_html_e('Select Status', 'armember-membership'); ?></li>
                                            <li data-label="<?php esc_attr_e('Approved', 'armember-membership'); ?>" data-value="success" class="arm_status_activity arm_hide"><?php esc_html_e('Approved', 'armember-membership'); ?></li>
                                            <li data-label="<?php esc_attr_e('Pending', 'armember-membership'); ?>" data-value="pending" class="arm_status_activity arm_hide"><?php esc_html_e('Pending', 'armember-membership'); ?></li>
                                            <li data-label="<?php esc_attr_e('Failed', 'armember-membership'); ?>" data-value="failed" class="arm_status_activity arm_hide"><?php esc_html_e('Failed', 'armember-membership'); ?></li>
                                            <li data-label="<?php esc_attr_e('Canceled', 'armember-membership'); ?>" data-value="canceled" class="arm_status_activity arm_hide"><?php esc_html_e('Canceled', 'armember-membership'); ?></li>
                                        </ul>
                                    </dd>
                                </dl>
                            </div>
                            <div class="arm_filter_status_subscription_box arm_datatable_filter_item">                        
                                <input type="hidden" id="arm_status_subscription_filter" class="arm_status_filter" value="<?php echo esc_attr($filter_plan_status); ?>" />
                                <dl class="arm_selectbox arm_width_190">
                                    <dt><span><?php esc_html_e('Select Status', 'armember-membership'); ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd>
                                        <ul data-id="arm_status_subscription_filter" data-placeholder="<?php esc_attr_e('Select Status', 'armember-membership'); ?>">
                                            <li data-label="<?php esc_attr_e('Select Status', 'armember-membership'); ?>" data-value="0" ><?php esc_html_e('Select Status', 'armember-membership'); ?></li>
                                            <li data-label="<?php esc_attr_e('Active', 'armember-membership'); ?>" data-value="1" class="arm_status_subscription "><?php esc_html_e('Active', 'armember-membership'); ?></li>
                                            <li data-label="<?php esc_attr_e('Expired', 'armember-membership'); ?>" data-value="2" class="arm_status_subscription "><?php esc_html_e('Expired', 'armember-membership'); ?></li>
                                            <li data-label="<?php esc_attr_e('Suspended', 'armember-membership'); ?>" data-value="3" class="arm_status_subscription "><?php esc_html_e('Suspended', 'armember-membership'); ?></li>
                                            <li data-label="<?php esc_attr_e('Canceled', 'armember-membership'); ?>" data-value="4" class="arm_status_subscription"><?php esc_html_e('Canceled', 'armember-membership'); ?></li>
                                            <?php if($ARMemberLite->is_arm_pro_active){
                                                $additional_filter = '';
                                                $additional_filter = apply_filters('arm_add_option_in_membership_filters_status', $additional_filter);
                                                echo $additional_filter; //phpcs:ignore
                                            } ?>
                                        </ul>
                                    </dd>
                                </dl>
                            </div>
                            <?php if (!empty($payment_gateways)) { ?>
                            <!--./====================Begin Filter By Payment Gateway Box====================/.-->
                            <div class="arm_datatable_filter_item arm_filter_gateway_label">
                                <input type="hidden" id="arm_filter_gateway" class="arm_filter_gateway" value="<?php echo esc_attr($filter_gateway); ?>" />
                                <dl class="arm_selectbox arm_width_160">
                                    <dt><span><?php esc_html_e('Gateway', 'armember-membership'); ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd>
                                        <ul data-id="arm_filter_gateway">
                                            <li data-label="<?php esc_attr_e('Gateway', 'armember-membership'); ?>" data-value="0"><?php esc_html_e('Gateway', 'armember-membership'); ?></li>
                                            <li data-label="<?php esc_attr_e('Manual', 'armember-membership'); ?>" data-value="<?php esc_attr_e('manual', 'armember-membership'); ?>"><?php esc_html_e('Manual', 'armember-membership'); ?></li>
                                            <?php foreach ($payment_gateways as $key => $pg): ?>
                                                <li data-label="<?php echo esc_attr($pg['gateway_name']); ?>" data-value="<?php echo esc_attr($key); ?>"><?php echo esc_html($pg['gateway_name']); ?></li>                                                                                
                                            <?php endforeach; ?>
                                        </ul>
                                    </dd>
                                </dl>
                            </div>
                            <!--./====================End Filter By Payment Gateway Box====================/.-->
                            <?php } ?>

                        <div class="arm_datatable_filter_item arm_filter_ptype_label">
                            <input type="hidden" id="arm_filter_ptype" class="arm_filter_ptype" value="<?php echo esc_attr($filter_ptype); ?>" />
                            <dl class="arm_selectbox arm_width_160 arm_min_width_60">
                                <dt><span><?php esc_html_e('Plan Type', 'armember-membership'); ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                <dd>
                                    <ul data-id="arm_filter_ptype">
                                        <li data-label="<?php esc_attr_e('Plan Type', 'armember-membership'); ?>" data-value="0"><?php esc_html_e('Plan Type', 'armember-membership'); ?></li>
                                        <li data-label="<?php esc_attr_e('One Time', 'armember-membership'); ?>" data-value="one_time"><?php esc_html_e('One Time', 'armember-membership'); ?></li>
                                        <li data-label="<?php esc_attr_e('Recurring', 'armember-membership'); ?>" data-value="subscription"><?php esc_html_e('Recurring', 'armember-membership'); ?></li>
                                    </ul>
                                </dd>
                            </dl>
                        </div>
                    
                        <!--./====================End Filter By Plan Box====================/.-->
                    </div>
                    <div class="arm_dt_filter_block arm_dt_filter_submit">
                        <input type="button" class="armemailaddbtn" id="arm_subscription_grid_filter_btn" onClick="arm_load_subscription_grid_after_filtered();" value="<?php esc_attr_e('Apply', 'armember-membership'); ?>"/>
                    </div>
                    <div class="armclear"></div>
                </div>

                <div class="arm_subscription_tabs">
                    <input type="hidden" id="arm_selected_sub_tab" value="subscriptions"/>
                    <div class="arm_all_subscription_tab arm_selected_sub_tab">
                        <?php esc_html_e('Subscriptions','armember-membership');?>
                    </div>
                    <div class="arm_all_activities_tab">
                        <?php esc_html_e('All Activities','armember-membership');?>
                    </div>
                    <div class="arm_upcoming_subscription_tab">
                        <?php esc_html_e('Upcoming Subscriptions','armember-membership');?>
                    </div>
                    
                </div>

				<div id="armmainformnewlist" class="armember_activity_datatable_div arm_hide">
                    <table cellpadding="0" cellspacing="0" border="0" class="display arm_on_display arm_hide_datatable" id="armember_datatable">
                        <thead>
                                <?php If($ARMemberLite->is_arm_pro_active){?>
                                <th class="arm_width_150"><?php esc_html_e('Invoice ID','armember-membership');?></th>
                                <?php }?>
                                <th class="arm_min_width_250"><?php esc_html_e('Membership','armember-membership');?></th>
                                <th class="arm_width_250"><?php esc_html_e('Username','armember-membership');?></th>
                                <th class="arm_width_260"><?php esc_html_e('Name','armember-membership');?></th>
                                <th class="arm_width_180"><?php esc_html_e('Payment Date','armember-membership');?></th>
                                <th class="arm_width_100"><?php esc_html_e('Amount','armember-membership');?></th>
                                <th class="center arm_width_150"><?php esc_html_e('Payment Type','armember-membership');?></th>
                                <th class="arm_width_120"><?php esc_html_e('Status','armember-membership');?></th>
                                <th class="armGridActionTD"></th>
                            </tr>
                        </thead>
                    </table>
                    
                    
					<div class="armclear"></div>
					<input type="hidden" name="show_hide_columns" id="show_hide_columns" value="<?php esc_attr_e('Show / Hide columns','armember-membership');?>"/>
					<input type="hidden" name="search_grid" id="search_grid" value="<?php esc_attr_e('Search','armember-membership');?>"/>
					<input type="hidden" name="entries_grid" id="entries_grid" value="<?php esc_attr_e('subscriptions','armember-membership');?>"/>
					<input type="hidden" name="show_grid" id="show_grid" value="<?php esc_attr_e('Show','armember-membership');?>"/>
					<input type="hidden" name="showing_grid" id="showing_grid" value="<?php esc_attr_e('Showing','armember-membership');?>"/>
					<input type="hidden" name="to_grid" id="to_grid" value="<?php esc_attr_e('to','armember-membership');?>"/>
					<input type="hidden" name="of_grid" id="of_grid" value="<?php esc_attr_e('of','armember-membership');?>"/>
					<input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php esc_attr_e('No matching plans found','armember-membership');?>"/>
					<input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php esc_attr_e('No any subscriptions found.','armember-membership');?>"/>
					<input type="hidden" name="filter_grid" id="filter_grid" value="<?php esc_attr_e('filtered from','armember-membership');?>"/>
					<input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php esc_attr_e('total','armember-membership');?>"/>
					<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
					<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
				</div>
                <div id="armmainformnewlist" class="armember_subscription_datatable_div">
                    <table cellpadding="0" cellspacing="0" border="0" class="display arm_on_display arm_hide_datatable arm_datatable_div" id="armember_datatable_1">
                        <thead>
                            <tr>
                                <th class="arm_min_width_50"></th>
                                <th class="arm_width_50"><?php esc_html_e('ID','armember-membership');?></th>
                                <th class="arm_min_width_200"><?php esc_html_e('Membership','armember-membership');?></th>
                                <th class="arm_min_width_150"><?php esc_html_e('Username','armember-membership');?></th>
                                <th class="arm_min_width_150"><?php esc_html_e('Name','armember-membership');?></th>
                                <th class="arm_min_width_80"><?php esc_html_e('Start Date','armember-membership');?></th>
                                <th class="arm_min_width_90"><?php esc_html_e('Expire/Next Renewal','armember-membership');?></th>
                                <th class="arm_min_width_120"><?php esc_html_e('Amount','armember-membership');?></th>
                                <th class="center arm_min_width_120"><?php esc_html_e('Payment Type','armember-membership');?></th>
                                <th class="arm_min_width_80"><?php esc_html_e('Transaction','armember-membership');?></th>
                                <th class="arm_width_100"><?php esc_html_e('Status','armember-membership');?></th>
                                <th class="armGridActionTD"></th>
                            </tr>
                        </thead>
                    </table>
                    
					<div class="armclear"></div>
					<input type="hidden" name="show_hide_columns" id="show_hide_columns" value="<?php esc_attr_e('Show / Hide columns','armember-membership');?>"/>
					<input type="hidden" name="search_grid" id="search_grid" value="<?php esc_attr_e('Search','armember-membership');?>"/>
					<input type="hidden" name="entries_grid" id="entries_grid" value="<?php esc_attr_e('subscriptions','armember-membership');?>"/>
					<input type="hidden" name="show_grid" id="show_grid" value="<?php esc_attr_e('Show','armember-membership');?>"/>
					<input type="hidden" name="showing_grid" id="showing_grid" value="<?php esc_attr_e('Showing','armember-membership');?>"/>
					<input type="hidden" name="to_grid" id="to_grid" value="<?php esc_attr_e('to','armember-membership');?>"/>
					<input type="hidden" name="of_grid" id="of_grid" value="<?php esc_attr_e('of','armember-membership');?>"/>
					<input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php esc_attr_e('No matching plans found','armember-membership');?>"/>
					<input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php esc_attr_e('No any subscriptions found.','armember-membership');?>"/>
					<input type="hidden" name="filter_grid" id="filter_grid" value="<?php esc_attr_e('filtered from','armember-membership');?>"/>
					<input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php esc_attr_e('total','armember-membership');?>"/>
					<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
                    <input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
                </div>
                <div id="armmainformnewlist" class="armember_upcoming_datatable_div arm_hide">
                    <table cellpadding="0" cellspacing="0" border="0" class="display arm_on_display arm_hide_datatable arm_datatable_div" id="armember_datatable_2">
                        <thead>
                            <tr>
                                <th class="arm_min_width_50"><?php esc_html_e('ID','armember-membership');?></th>
                                <th class="arm_min_width_200"><?php esc_html_e('Membership','armember-membership');?></th>
                                <th class="arm_min_width_150"><?php esc_html_e('Username','armember-membership');?></th>
                                <th class="arm_min_width_150"><?php esc_html_e('Name','armember-membership');?></th>
                                <th class="arm_min_width_120"><?php esc_html_e('Start Date','armember-membership');?></th>
                                <th class="arm_min_width_150"><?php esc_html_e('Expire/Next Renewal','armember-membership');?></th>
                                <th class="arm_min_width_120"><?php esc_html_e('Amount','armember-membership');?></th>
                                <th class="center arm_min_width_120"><?php esc_html_e('Payment Type','armember-membership');?></th>
                            </tr>
                        </thead>
                    </table>
                    
					<div class="armclear"></div>
					<input type="hidden" name="show_hide_columns" id="show_hide_columns" value="<?php esc_html_e('Show / Hide columns','armember-membership');?>"/>
					<input type="hidden" name="search_grid" id="search_grid" value="<?php esc_html_e('Search','armember-membership');?>"/>
					<input type="hidden" name="entries_grid" id="entries_grid" value="<?php esc_html_e('subscriptions','armember-membership');?>"/>
					<input type="hidden" name="show_grid" id="show_grid" value="<?php esc_html_e('Show','armember-membership');?>"/>
					<input type="hidden" name="showing_grid" id="showing_grid" value="<?php esc_html_e('Showing','armember-membership');?>"/>
					<input type="hidden" name="to_grid" id="to_grid" value="<?php esc_html_e('to','armember-membership');?>"/>
					<input type="hidden" name="of_grid" id="of_grid" value="<?php esc_html_e('of','armember-membership');?>"/>
					<input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php esc_html_e('No matching plans found','armember-membership');?>"/>
					<input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php esc_html_e('No any subscriptions found.','armember-membership');?>"/>
					<input type="hidden" name="filter_grid" id="filter_grid" value="<?php esc_html_e('filtered from','armember-membership');?>"/>
					<input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php esc_html_e('total','armember-membership');?>"/>
					<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
					<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
				</div>
				<div class="footer_grid"></div>
			</form>
		</div>
		<div class="armclear"></div>
		<br>
		<?php 
		/* **********./Begin Change Transaction Status Popup/.********** */
		$change_transaction_status_popup_content = '<span class="arm_confirm_text">'.esc_html__("Are you sure you want to change transaction status?",'armember-membership' ).'</span>';
		$change_transaction_status_popup_content .= '<input type="hidden" value="" id="log_id"/>';
		$change_transaction_status_popup_content .= '<input type="hidden" value="" id="log_status"/>';
        
		$change_transaction_status_popup_arg = array(
			'id' => 'change_transaction_status_message',
			'class' => 'change_transaction_status_message',
            'title' => esc_html__('Change Transaction Status', 'armember-membership'),
			'content' => $change_transaction_status_popup_content,
			'button_id' => 'arm_change_transaction_status_ok_btn',
			'button_onclick' => "arm_change_bank_transfer_status_func();",
		);
        echo $arm_global_settings->arm_get_bpopup_html($change_transaction_status_popup_arg); //phpcs:ignore

		/* **********./End Change Transaction Status Popup/.********** */
		/* **********./Begin Bulk Delete Transaction Popup/.********** */
		$bulk_delete_transaction_popup_content = '<span class="arm_confirm_text">'.esc_html__("Are you sure you want to delete this transaction(s)?",'armember-membership' ).'</span>';
		$bulk_delete_transaction_popup_content .= '<input type="hidden" value="false" id="bulk_delete_flag"/>';
		$bulk_delete_transaction_popup_arg = array(
			'id' => 'delete_bulk_transactions_message',
			'class' => 'delete_bulk_transactions_message',
            'title' => esc_html__('Delete Transaction(s)', 'armember-membership'),
			'content' => $bulk_delete_transaction_popup_content,
			'button_id' => 'arm_bulk_delete_transactions_ok_btn',
			'button_onclick' => "apply_transactions_bulk_action('bulk_delete_flag');",
		);
		echo $arm_global_settings->arm_get_bpopup_html($bulk_delete_transaction_popup_arg); //phpcs:ignore

		/* **********./End Bulk Delete Transaction Popup/.********** */
		?>
        <div class="arm_invoice_detail_container"></div>
		<div class="arm_preview_log_detail_container"></div>
		<div class="arm_preview_failed_log_detail_container"></div>
	</div>
</div>

<div class="arm_member_view_detail_container"></div>

<div class="arm_add_new_subscription_wrapper popup_wrapper">
	<form method="post" action="#" id="arm_add_new_subscription_wrapper_frm" class="arm_admin_form arm_add_new_subscription_wrapper_frm">
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">	
				<td class="add_new_subscription_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php esc_html_e('Add New Subscription','armember-membership');?></td>
				<td class="popup_content_text">
					<div class="arm_table_label_on_top">	
                        <div class="form-field form-required">
                            <span class="arm_edit_plan_lbl"><label for="arm_user_id"><?php esc_html_e('Member','armember-membership'); ?></label></span>
                            <div class="arm_auto_user_field">
                                <input id="arm_user_auto_selection" type="text" name="arm_user_ids" value="" placeholder="<?php esc_attr_e('Search by username or email...', 'armember-membership');?>" data-msg-required="<?php esc_attr_e('Please select user.', 'armember-membership');?>" required>
                                <input type="hidden" name="arm_display_admin_user" id="arm_display_admin_user" value="0">
                                <div class="arm_users_items arm_required_wrapper" id="arm_users_items" style="display: none;"></div>
                                <span class="arm_plan_users_error"></span>
                            </div>
                        </div>
                        <div class="form-field form-required arm_transaction_membership_plan_wrapper">
                            <span class="arm_edit_plan_lbl"><?php esc_html_e('Select Membership Plan','armember-membership'); ?></span>
                            <div>
                                <input type="hidden" class="arm_user_plan_change_input_get_cycle" id="arm_plan_id" name="membership_plan" value="" data-manage-plan-grid="1" data-msg-required="<?php esc_attr_e('Please select atleast one membership', 'armember-membership');?>"/>
                                <dl class="arm_selectbox column_level_dd">
                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd>
                                        <ul data-id="arm_plan_id">
                                            <li data-label="<?php esc_attr_e('Select Plan', 'armember-membership'); ?>" data-value=""><?php esc_html_e('Select Plan', 'armember-membership'); ?></li>
                                            <?php 
                                            if (!empty($all_plans)) {
                                                foreach ($all_plans as $p) {
                                                    $p_id = $p['arm_subscription_plan_id'];
                                                    if ($p['arm_subscription_plan_status'] == '1' && $p['arm_subscription_plan_type'] != 'free') {
                                                        ?><li data-label="<?php echo stripslashes( esc_attr($p['arm_subscription_plan_name']) ); //phpcs:ignore?>" data-value="<?php echo esc_attr($p_id) ?>"><?php echo esc_html(stripslashes($p['arm_subscription_plan_name']));?></li><?php
                                                    }
                                                }
                                            }
                                            ?>
                                        </ul>
                                    </dd>
                                </dl>
                                <span class="arm_plan_error"></span>
                            </div>
                        </div>
                        <div class="form-field form-required arm_selected_plan_cycle"></div>
                    </div>
					<div class="armclear"></div>
				</td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
					<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr( wp_create_nonce( 'arm_wp_nonce' ) ); //phpcs:ignore?>" class="valid arm_valid" aria-invalid="false">
						<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm_loader.gif'; //phpcs:ignore?>" id="arm_loader_img_add_subscription" class="arm_loader_img arm_submit_btn_loader"  style="top: 15px;float: <?php echo (is_rtl()) ? 'right' : 'left';?>;display: none;" width="20" height="20" />
						<button class="arm_save_btn arm_new_subscription_button" type="submit" data-type="add"><?php esc_html_e('Save', 'armember-membership') ?></button>
						<button class="arm_cancel_btn add_new_subscription_close_btn" type="button"><?php esc_html_e('Cancel','armember-membership');?></button>
					</div>
				</td>
			</tr>
		</table>
		<div class="armclear"></div>
	</form>
    <?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
    <input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
</div>
<?php
	echo $ARMemberLite->arm_get_need_help_html_content('manage-subscription'); //phpcs:ignore
?>