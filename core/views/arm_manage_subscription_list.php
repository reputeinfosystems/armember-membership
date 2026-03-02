<?php
global $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_member_forms ,$arm_subscription_class, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways,$armPrimaryStatus,$arm_common_lite;
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
    var ARM_IMAGE_URL = '<?php echo MEMBERSHIPLITE_IMAGES_URL;?>';
// <![CDATA[

jQuery(document).ready(function () {

    jQuery(document).on('keyup','#armsubscriptionsearch_new', function (e) {
        e.stopPropagation();
        var arm_search_val = jQuery(this).val();
        jQuery('#armsubscriptionsearch_new').val(arm_search_val);
        if (e.keyCode == 13 || 'Enter' == e.key) {
            arm_subscription_grid_load_filter_data();
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
        jQuery('.armember_subscription_datatable_div').removeClass('arm_hide');
        jQuery('.armember_activity_datatable_div').addClass('arm_hide');       
        jQuery('.armember_upcoming_datatable_div').addClass('arm_hide');
        jQuery('.arm_membership_plan_status_filters').removeClass('arm_hide');
        jQuery('.arm_selectbox').trigger('click');
        arm_reset_manage_subscription_grid_filter_tab();
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
        jQuery('.arm_membership_plan_status_filters').removeClass('arm_hide');
        jQuery('.arm_selectbox').trigger('click');
        arm_reset_manage_subscription_grid_filter_tab();
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
        jQuery('.arm_membership_plan_status_filters').addClass('arm_hide');
        arm_reset_manage_subscription_grid_filter_tab();
    });    
});

jQuery(document).on('click','#arm_subscription_grid_filter_btn',function(){
    arm_subscription_grid_load_filter_data();
    arm_load_subscription_grid_after_filtered();
    
});

function arm_subscription_grid_load_filter_data() {
    var chk_count = 0;
    var arm_selected_plan = jQuery('.arm_filter_plans_box').find('#arm_subs_plan_filter').val();
    if(arm_selected_plan != '')
    {
        var arm_plans = arm_selected_plan.split(',');
        chk_count = arm_plans.length;
    }		

    jQuery('.arm_plan_filter_value').html('');
    if(chk_count > 0)
    {
        var first_selected_plan_lbl = '';
        var first_selected_plan_id = arm_plans[0];
        
        var first_selected_plan_lbl = jQuery('.arm_filter_plans_box').find('li[data-value="'+first_selected_plan_id+'"]').attr('data-label');	
        if(chk_count > 1)
        {
            first_selected_plan_lbl += '...';
        }		
	
        first_selected_plan_lbl = first_selected_plan_lbl != '' ? first_selected_plan_lbl : jQuery('.arm_filter_plans_box').find('ul[data-id="arm_subs_plan_filter"]').attr('data-placeholder');
        jQuery('.arm_plan_filter_value').html(first_selected_plan_lbl);
    }
    else
    {
        var first_selected_plan_lbl = jQuery('.arm_filter_plans_box').find('ul[data-id="arm_subs_plan_filter"]').attr('data-placeholder');
        jQuery('.arm_plan_filter_value').html(first_selected_plan_lbl);
    }

    var arm_selected_subscription_status = jQuery('.arm_filter_status_subscription_box').find('#arm_status_subscription_filter').val();
    if (arm_selected_subscription_status != '') {
        var subscription_status_label = jQuery('.arm_filter_status_subscription_box').find('li[data-value="'+arm_selected_subscription_status+'"]').attr('data-label');
        jQuery('.arm_subscription_status_filter_value').html(subscription_status_label);
    } else {
        var subscription_status_label = jQuery('.arm_filter_status_subscription_box').find('ul[data-id="arm_status_subscription_filter"]').attr('data-placeholder');
        jQuery('.arm_subscription_status_filter_value').html(subscription_status_label);
    }

    var arm_selected_gateway = jQuery('.arm_filter_gateway_box').find('#arm_filter_gateway').val();
    if (arm_selected_gateway != '') {
        var gateway_label = jQuery('.arm_filter_gateway_box').find('li[data-value="'+arm_selected_gateway+'"]').attr('data-label');
        jQuery('.arm_gateway_filter_value').html(gateway_label);
    } else {
        var gateway_label = jQuery('.arm_filter_gateway_box').find('ul[data-id="arm_filter_gateway"]').attr('data-placeholder');
        jQuery('.arm_gateway_filter_value').html(gateway_label);
    }

    var arm_selected_ptype = jQuery('.arm_filter_ptype_box').find('#arm_filter_ptype').val();
    if (arm_selected_ptype != '') {
        var ptype_label = jQuery('.arm_filter_ptype_box').find('li[data-value="'+arm_selected_ptype+'"]').attr('data-label');
        jQuery('.arm_ptype_filter_value').html(ptype_label);
    } else {
        var ptype_label = jQuery('.arm_filter_ptype_box').find('ul[data-id="arm_filter_ptype"]').attr('data-placeholder');
        jQuery('.arm_ptype_filter_value').html(ptype_label);
    }

    var arm_selected_gateway = jQuery('.arm_filter_status_activity_box').find('#arm_status_filter').val();
    if (arm_selected_gateway != '') {
        var gateway_label = jQuery('.arm_filter_status_activity_box').find('li[data-value="'+arm_selected_gateway+'"]').attr('data-label');
        jQuery('.arm_activity_status_filter_value').html(gateway_label);
    } else {
        var subscription_status_label = jQuery('.arm_filter_status_activity_box').find('ul[data-id="arm_status_filter"]').attr('data-placeholder');
        jQuery('.arm_activity_status_filter_value').html(subscription_status_label);
    }
}

function arm_reset_manage_subscription_grid_filter_tab()
{
    hideConfirmBoxCallback_filter('manage_subscription_filter');
    jQuery('.arm_selectbox ul').hide();
    jQuery('.arm_filter_child_row .arm_cancel_btn').addClass('hidden_section');
    jQuery('.arm_filter_data_options').addClass('hidden_section');
    arm_load_subscription_grid_after_filtered();
}

function arm_reset_manage_subscription_grid_filter(){
    jQuery('#arm_confirm_box_manage_subscription_filter').find('input:not([type="button"],[type="checkbox"],#arm_subs_plan_filter)').val('');
    jQuery('.arm_filter_plans_box input[type="text"]').attr('value','');
    jQuery('.arm_filter_plans_box .arm_icheckbox').each(function(){

        	jQuery(this).prop('checked',false).trigger('click');
    });
    jQuery('#arm_confirm_box_manage_subscription_filter').find('input#arm_subs_plan_filter').val('');
       
}

function arm_reset_manage_subscription_grid(){
    hideConfirmBoxCallback_filter('manage_subscription_filter');   
    jQuery('.arm_filter_status_activity_box').find('#arm_status_filter').val('0');
    arm_selectbox_init();
}

function arm_load_subscription_grid_after_filtered() {
    jQuery('#armember_datatable').dataTable().fnDestroy();
    jQuery('#armember_datatable_1').dataTable().fnDestroy();
    jQuery('#armember_datatable_2').dataTable().fnDestroy();
    arm_load_subscription_list_grid();
}

function show_grid_loader() {
    jQuery(".dataTables_scroll").hide();
    jQuery(".footer").hide();
    jQuery('.arm_loading_grid').show();
}

function arm_load_subscription_list_grid(is_filtered){
	var __ARM_Showing = '<?php echo addslashes(esc_html__('Showing','armember-membership')); //phpcs:ignore?>';
    var __ARM_Showing_empty = '<?php echo addslashes(esc_html__('Showing','armember-membership').' <span class="arm-black-350 arm_font_size_15">0</span> - <span class="arm-black-350 arm_font_size_15">0</span> of <span class="arm-black-350 arm_font_size_15">0</span> '.esc_html__('Subscriptions','armember-membership')); //phpcs:ignore?>';
    var __ARM_to = '-';
    var __ARM_of = '<?php echo addslashes(esc_html__('of','armember-membership')); //phpcs:ignore?>';
    var __ARM_Entries = ' <?php echo addslashes(esc_html__('Subscriptions','armember-membership')); //phpcs:ignore?>';
    var __ARM_Show = '<?php echo addslashes(esc_html__('Show','armember-membership')); //phpcs:ignore?> ';
    var __ARM_NO_FOUND = '<?php echo addslashes(esc_html__('No Subscriptions found.','armember-membership')); //phpcs:ignore?>';
    var __ARM_NO_MATCHING = '<?php echo addslashes(esc_html__('No matching records found.','armember-membership')); //phpcs:ignore?>';
    var __ARM_subscription_List_right = [7];
    
    
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
            $sortables = '0,2,3,4, 6, 7,8';
            $order = 1;
            $ARM_Activity_width_80 = '1';
            $center = '7';
            $ARM_Activity_width_120 = '3,4,5';
            $ARM_Activity_List_right ='6';
        ?>
        var oTables = jQuery('#armember_datatable').dataTable({
            "oLanguage": {
                "sProcessing": show_grid_loader(),
                "sInfo": __ARM_Showing + " <span class='arm-black-350 arm_font_size_15'>_START_</span> " + __ARM_to + " <span class='arm-black-350 arm_font_size_15'>_END_</span> " + __ARM_of + " <span class='arm-black-350 arm_font_size_15'>_TOTAL_</span> " + __ARM_Entries,
                "sInfoEmpty": __ARM_Showing_empty,
                
                "sLengthMenu": __ARM_Show + "_MENU_",
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
                {"sClass": "dt-right", "aTargets": [<?php echo esc_html($ARM_Activity_List_right)?>]},
                {"sClass": "arm_min_width_190", "aTargets": [<?php echo esc_html($ARM_Activity_width_120)?>]},
                {"sClass": "control arm_padding_right_0", "aTargets": [0]},
                {"sClass": "center", "aTargets": [<?php echo esc_html($center);?>]},
                { "aTargets": -1, "responsivePriority": 1 }
            ],
            "responsive": {
				details: {
					type: 'column',
					target: '' // This removes the dtr-control click event
				}
			},
            "aaSorting": [[<?php echo $order; //phpcs:ignore ?>, 'desc']],
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
                jQuery(".dataTables_scroll").show();
                jQuery(".footer").show();
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
                var datatable = jQuery('#armember_datatable').DataTable();
				var dataTableHeaderElements = datatable.columns().header();	
				for (var i = 0; i< dataTableHeaderElements.length; i++) {
					if(typeof dataTableHeaderElements[i].dataset.key != 'undefined')
					{
						if(!jQuery(dataTableHeaderElements[i]).is(':visible')){
							var i = i - 1;
							jQuery(dataTableHeaderElements[i]).addClass('arm_last_dt_col');                           
							break;
						}
					}
				}
            }
        });
        var filter_box = jQuery('#arm_filter_wrapper').html();
        jQuery('div#armember_datatable_wrapper > div:first-child').html(filter_box);               
    }
    if(selected_tab == 'subscriptions')
    {
        var oTables = jQuery('#armember_datatable_1').dataTable({
            "oLanguage": {
                "sProcessing": show_grid_loader(),
                "sInfo": __ARM_Showing + " <span class='arm-black-350 arm_font_size_15'>_START_</span> " + __ARM_to + " <span class='arm-black-350 arm_font_size_15'>_END_</span> " + __ARM_of + " <span class='arm-black-350 arm_font_size_15'>_TOTAL_</span> " + __ARM_Entries,
                "sInfoEmpty": __ARM_Showing_empty,
                
                "sLengthMenu": __ARM_Show+ "_MENU_",
                "sEmptyTable": __ARM_NO_FOUND,
                "sZeroRecords": __ARM_NO_MATCHING,
            },
            "bDestroy": true,
            "language":{
                "searchPlaceholder": "<?php esc_html_e( 'Search', 'armember-membership' ); ?>",
                "search":"",
            },
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
                {"sClass": "arm_padding_right_0 center control", "aTargets": [0]},
                {"bSortable": false, "aTargets": [0,2,3,4,5, 6, 7, 8,9,10] },
                {"aTargets":[0],"sClass":"noVis"},                
                {"sClass": "dt-right arm_min_width_100 arm_max_width_100", "aTargets": __ARM_subscription_List_right},
                {"sClass": "arm_min_width_120", "aTargets": [2,3]},
                {"sClass": "arm_min_width_150", "aTargets": [4,5,6]},
                {"sClass": "dt-left arm_max_width_100", "aTargets": [8,9,10]},
                { "aTargets": -1, "responsivePriority": 1 }
            ],
            "responsive": {
				details: {
					type: 'column',
					target: '' // This removes the dtr-control click event
				}
			},
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
                        jQuery(this).parent().hide(0);
                        jQuery(this).parent().css('visibility','hidden');
                    }
                    if(jQuery(this).hasClass('arm_no_expand')){
                        jQuery(this).closest('tr').addClass('arm_no_expand');
                    }
                });
            },
            
            "fnDrawCallback": function (oSettings) {
                jQuery('.arm_loading_grid').hide();
                arm_show_data();
                jQuery(".dataTables_scroll").show();
                jQuery(".footer").show();
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
                var datatable = jQuery('#armember_datatable_1').DataTable();
				var dataTableHeaderElements = datatable.columns().header();	
				for (var i = 0; i< dataTableHeaderElements.length; i++) {
					if(typeof dataTableHeaderElements[i].dataset.key != 'undefined')
					{
						if(!jQuery(dataTableHeaderElements[i]).is(':visible')){
							var i = i - 1;
							jQuery(dataTableHeaderElements[i]).addClass('arm_last_dt_col');                           
							break;
						}
					}
				}
            }
        });
        var filter_box = jQuery('#arm_filter_wrapper').html();
        jQuery('.arm_filter_grid_list_container').find('.arm_datatable_filters_options').remove();
        jQuery('div#armember_datatable_1_filter').parent().append(filter_box);
        jQuery('div#armember_datatable_1_filter').hide();
    }
    if(selected_tab == 'upcoming')
    {
        var oTables = jQuery('#armember_datatable_2').dataTable({
            "oLanguage": {
                "sProcessing": show_grid_loader(),
                "sInfo": __ARM_Showing + " <span class='arm-black-350 arm_font_size_15'>_START_</span> " + __ARM_to + " <span class='arm-black-350 arm_font_size_15'>_END_</span> " + __ARM_of + " <span class='arm-black-350 arm_font_size_15'>_TOTAL_</span> " + __ARM_Entries,
                "sInfoEmpty": __ARM_Showing_empty,
                "sLengthMenu": __ARM_Show + "_MENU_",
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
                {"sClass": "arm_padding_right_0 center control", "aTargets": [0]},
                {"sClass": "center", "aTargets": [8]},
                {"bSortable": false, "aTargets": [0,1,2,3,4,5, 6, 7,8] },
                {"sClass": "dt-right", "aTargets": [7]},
                { "aTargets": -1, "responsivePriority": 1 }
            ],
            "responsive": {
				details: {
					type: 'column',
					target: '' // This removes the dtr-control click event
				}
			},
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
                jQuery(".dataTables_scroll").show();
                jQuery(".footer").show();
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
                var datatable = jQuery('#armember_datatable_2').DataTable();
				var dataTableHeaderElements = datatable.columns().header();	
				for (var i = 0; i< dataTableHeaderElements.length; i++) {
					if(typeof dataTableHeaderElements[i].dataset.key != 'undefined')
					{
						if(!jQuery(dataTableHeaderElements[i]).is(':visible')){
							var i = i - 1;
							jQuery(dataTableHeaderElements[i]).addClass('arm_last_dt_col');                           
							break;
						}
					}
				}
            }
        });
        var filter_box = jQuery('#arm_filter_wrapper').html();
        jQuery('div#armember_datatable_2_wrapper > div:first-child').html(filter_box); 
    }
    if(search != ''){
        jQuery('.arm_datatable_searchbox').find('#armsubscriptionsearch_new').val(search);
    }
}

function ChangeID(id) {
	document.getElementById('delete_id').value = id;
}

jQuery(document).ready(function(){
	jQuery(document).on('click', '.wrap #armember_datatable_1.collapsed tr.shown td:not([data-action="selectDay"],.armGridActionTD)', function (e) {
		
		var tr = jQuery(this).closest('tr');
		var class_name = jQuery(this).closest('tr').attr('class');
		var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();
		var row = jQuery('#armember_datatable_1').DataTable().row(tr);	
		row.child.hide();
		tr.removeClass('shown');
		tr.addClass('hide');
	});
	jQuery(document).on('click', '.wrap #armember_datatable_1.collapsed tr:not(.arm_child_transaction_row,.shown,.arm_filter_child_row,.arm_detail_expand_container,.arm_detail_expand_container_child_row) td:not([data-action="selectDay"],.armGridActionTD)', function (e) {
		
		jQuery('.arm_child_transaction_row').hide();
		jQuery('tr.shown .arm_show_user_more_transactions').trigger('click');
		var id = jQuery(this).closest('tr').find('.arm_show_user_more_transactions').attr('data-id');
        if(typeof id != 'undefined' && id != '')
        {
            var tr = jQuery(this).closest('tr');
            var class_name = jQuery(this).closest('tr').attr('class');
            var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();
            var row = jQuery('#armember_datatable_1').DataTable().row(tr);
            var datatable = jQuery('#armember_datatable_1').DataTable();
            var dataTableHeaderElements = datatable.columns().header();		
            var headers = [];
            var headers_label = [];
            for (var i = 0; i< dataTableHeaderElements.length; i++) {
                if(typeof dataTableHeaderElements[i].dataset.key != 'undefined' && !jQuery(dataTableHeaderElements[i]).is(':visible'))
                {
                    key = dataTableHeaderElements[i].dataset.key;
                    label = jQuery(dataTableHeaderElements[i]).text();
                    headers.push(key);
                    headers_label.push(label);
                }
            }
            // Open this row
            if (row.child()) {
                row.child.show();
                tr.removeClass('hide');
                jQuery('.arm_detail_expand_container').removeAttr('style');
                tr.addClass('shown');
            }
            else{
                row.child.show();
                tr.removeClass('hide');
                row.child(sub_child_format(id,headers,headers_label,_wpnonce), class_name +" "+"arm_detail_expand_container").show();
                tr.addClass('shown');
            }
        }
	});

    jQuery(document).on('click', '.wrap #armember_datatable.collapsed tr.shown td:not([data-action="selectDay"],.armGridActionTD)', function (e) {
		
		var tr = jQuery(this).closest('tr');
		var class_name = jQuery(this).closest('tr').attr('class');
		var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();
		var row = jQuery('#armember_datatable').DataTable().row(tr);	
		row.child.hide();
		tr.removeClass('shown');
		tr.addClass('hide');
	});

    jQuery(document).on('click', '.wrap #armember_datatable.collapsed tr:not(.arm_child_transaction_row,.shown,.arm_filter_child_row,.arm_detail_expand_container,.arm_detail_expand_container_child_row) td:not([data-action="selectDay"],.armGridActionTD)', function (e) {
		
		jQuery('.arm_child_transaction_row').hide();
		jQuery('tr.shown .arm_show_user_more_transactions').trigger('click');
		var id = jQuery(this).closest('tr').find('.arm_show_user_more_transactions').attr('data-id');
        if(typeof id != 'undefined' && id != '')
        {
            var tr = jQuery(this).closest('tr');
            var class_name = jQuery(this).closest('tr').attr('class');
            var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();
            var row = jQuery('#armember_datatable').DataTable().row(tr);
            var datatable = jQuery('#armember_datatable').DataTable();
            var dataTableHeaderElements = datatable.columns().header();		
            var headers = [];
            var headers_label = [];
            for (var i = 0; i< dataTableHeaderElements.length; i++) {
                if(typeof dataTableHeaderElements[i].dataset.key != 'undefined' && !jQuery(dataTableHeaderElements[i]).is(':visible'))
                {
                    key = dataTableHeaderElements[i].dataset.key;
                    label = jQuery(dataTableHeaderElements[i]).text();
                    headers.push(key);
                    headers_label.push(label);
                }
            }
            // Open this row
            if (row.child()) {
                row.child.show();
                tr.removeClass('hide');
                jQuery('.arm_detail_expand_container').removeAttr('style');
                tr.addClass('shown');
            }
            else{
                row.child.show();
                tr.removeClass('hide');
                row.child(activity_child_format(id,headers,headers_label,_wpnonce), class_name +" "+"arm_detail_expand_container").show();
                tr.addClass('shown');
            }
        }
	});

    jQuery(document).on('click', '.wrap #armember_datatable_2.collapsed tr.shown td:not([data-action="selectDay"],.armGridActionTD)', function (e) {
		
		var tr = jQuery(this).closest('tr');
		var class_name = jQuery(this).closest('tr').attr('class');
		var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();
		var row = jQuery('#armember_datatable_2').DataTable().row(tr);	
		row.child.hide();
		tr.removeClass('shown');
		tr.addClass('hide');
	});

    jQuery(document).on('click', '.wrap #armember_datatable_2.collapsed tr:not(.arm_child_transaction_row,.shown,.arm_filter_child_row,.arm_detail_expand_container,.arm_detail_expand_container_child_row) td:not([data-action="selectDay"],.armGridActionTD)', function (e) {
		
		jQuery('.arm_child_transaction_row').hide();
		jQuery('tr.shown .arm_show_user_more_transactions').trigger('click');
		var id = jQuery(this).closest('tr').find('.arm_show_user_more_transactions').attr('data-id');
        if(typeof id != 'undefined' && id != '')
        {
            var tr = jQuery(this).closest('tr');
            var class_name = jQuery(this).closest('tr').attr('class');
            var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();
            var row = jQuery('#armember_datatable_2').DataTable().row(tr);
            var datatable = jQuery('#armember_datatable_2').DataTable();
            var dataTableHeaderElements = datatable.columns().header();		
            var headers = [];
            var headers_label = [];
            for (var i = 0; i< dataTableHeaderElements.length; i++) {
                if(typeof dataTableHeaderElements[i].dataset.key != 'undefined' && !jQuery(dataTableHeaderElements[i]).is(':visible'))
                {
                    key = dataTableHeaderElements[i].dataset.key;
                    label = jQuery(dataTableHeaderElements[i]).text();
                    headers.push(key);
                    headers_label.push(label);
                }
            }
            // Open this row
            if (row.child()) {
                row.child.show();
                tr.removeClass('hide');
                jQuery('.arm_detail_expand_container').removeAttr('style');
                tr.addClass('shown');
            }
            else{
                row.child.show();
                tr.removeClass('hide');
                row.child(upcycle_child_format(id,headers,headers_label,_wpnonce), class_name +" "+"arm_detail_expand_container").show();
                tr.addClass('shown');
            }
        }
	});

});
function sub_child_format(d,headers,headers_label,_wpnonce) {
    
    var response1 = '</div><div class="arm_child_row_div_'+d+'" style="justify-self:center;text-align:center"></><img class="arm_load_subscription_plans" src="<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore?>/arm_loader.gif" alt="<?php esc_attr_e('Load More', 'armember-membership'); ?>"div>';
    jQuery.ajax({
        type: "POST",
        url: __ARMAJAXURL,
        data: "action=get_user_subscription_details_for_grid&activity_id=" + d + "&exclude_headers="+headers+"&header_label="+headers_label+"&_wpnonce=" + _wpnonce,
        dataType: 'html',
        success: function (response) {
            jQuery('.arm_child_row_div_'+d).html(response);
            jQuery('.arm_child_row_div_'+d).removeAttr('style');
        }
    });
    return response1;
}

function upcycle_child_format(d,headers,headers_label,_wpnonce) {
    
    var response1 = '</div><div class="arm_child_row_div_'+d+'" style="justify-self:center;text-align:center"></><img class="arm_load_subscription_plans" src="<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore?>/arm_loader.gif" alt="<?php esc_attr_e('Load More', 'armember-membership'); ?>"div>';
    jQuery.ajax({
        type: "POST",
        url: __ARMAJAXURL,
        data: "action=get_upcomming_sub_details_for_grid&activity_id=" + d + "&exclude_headers="+headers+"&header_label="+headers_label+"&_wpnonce=" + _wpnonce,
        dataType: 'html',
        success: function (response) {
            jQuery('.arm_child_row_div_'+d).html(response);
            jQuery('.arm_child_row_div_'+d).removeAttr('style');
        }
    });
    return response1;
}

function activity_child_format(d,headers,headers_label,_wpnonce) {
    
    var response1 = '</div><div class="arm_child_row_div_'+d+'" style="justify-self:center;text-align:center"></><img class="arm_load_subscription_plans" src="<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore?>/arm_loader.gif" alt="<?php esc_attr_e('Load More', 'armember-membership'); ?>"div>';
    jQuery.ajax({
        type: "POST",
        url: __ARMAJAXURL,
        data: "action=get_user_activity_details_for_grid&log_id=" + d + "&exclude_headers="+headers+"&header_label="+headers_label+"&_wpnonce=" + _wpnonce,
        dataType: 'html',
        success: function (response) {
            jQuery('.arm_child_row_div_'+d).html(response);
            jQuery('.arm_child_row_div_'+d).removeAttr('style');
        }
    });
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
<div class="arm_filter_wrapper" id="arm_filter_wrapper" style="display:none;">
    <div class="arm_datatable_filters_options arm_filters_fields">
        <div class="sltstandard">
            <div class="arm_confirm_box_btn_container arm_margin_0">
                <div class="arm_dt_filter_block arm_datatable_searchbox">
                    <div class="arm_datatable_filter_item">
                        <label><input type="text" placeholder="<?php esc_html_e('Search by Username', 'armember-membership'); ?>" id="armsubscriptionsearch_new" value="<?php echo esc_attr($filter_search); ?>" class="arm_mng_sbscr_srch_inpt" tabindex="0"></label>
                    </div>
                </div>
                <div class="arm_filter_child_row">
                    <div>
                        <?php if (!empty($all_plans)){ ?>
                        <div class="arm_filter_plans_box arm_datatable_filter_item arm_width_100_pct">                        
                            <input type="text" id="arm_subs_plan_filter" class="arm_subs_filter arm-selectpicker-input-control" value="<?php echo esc_attr($filter_plan_id); ?>" />
                            <dl class="arm_multiple_selectbox arm_width_230">
                                <dt>
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_7522_5770)"><rect x="6" y="2" width="8" height="16" rx="2" fill="#9CA7BD"/><path opacity="0.4" d="M11 4H16C17.1046 4 18 4.89543 18 6V14C18 15.1046 17.1046 16 16 16H11V4Z" fill="#9CA7BD"/><path opacity="0.4" d="M2 6C2 4.89543 2.89543 4 4 4H9V16H4C2.89543 16 2 15.1046 2 14V6Z" fill="#9CA7BD"/></g><defs><clipPath id="clip0_7522_5770"><rect width="16" height="16" fill="white" transform="translate(2 2)"/></clipPath></defs></svg>
                                    <span class="arm_plan_filter_value"><?php esc_html_e('Select Memberships', 'armember-membership'); ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i>
                                </dt>
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
                    </div>
                </div>
                <div class="arm_filter_child_row arm_filter_status_activity_box arm_hide">
                    <div>
                        <div class="">                        
                            <input type="text" id="arm_status_filter" class="arm_status_filter arm-selectpicker-input-control" value="<?php echo esc_attr($filter_plan_status); ?>" />
                            <dl class="arm_selectbox arm_width_230">
                                <dt>
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_7540_15552)"><path fill-rule="evenodd" clip-rule="evenodd" d="M16.0731 6.26028C16.5917 6.04852 17.1839 6.2973 17.3956 6.81595C19.0896 10.9651 17.0993 15.702 12.9502 17.396C10.5553 18.3738 7.96399 18.1232 5.87826 16.9415C5.39086 16.6653 5.21962 16.0462 5.49579 15.5588C5.77195 15.0714 6.39094 14.9002 6.87835 15.1764C8.44678 16.0651 10.388 16.2508 12.1834 15.5178C15.2952 14.2474 16.7879 10.6947 15.5174 7.58279C15.3056 7.06414 15.5544 6.47202 16.0731 6.26028ZM3.99514 11.4272C4.06205 11.6808 4.14623 11.9334 4.24841 12.1838C4.35059 12.434 4.46729 12.6734 4.59703 12.9014C4.8741 13.3883 4.70398 14.0076 4.21709 14.2847C3.73019 14.5617 3.11087 14.3916 2.83382 13.9047C2.6608 13.6006 2.50567 13.2823 2.37024 12.9505C2.2348 12.6188 2.12279 12.2829 2.03356 11.9446C1.89067 11.4029 2.21395 10.848 2.75562 10.7051C3.2973 10.5622 3.85225 10.8855 3.99514 11.4272ZM4.26263 5.42539C4.74656 5.70761 4.91007 6.3287 4.62786 6.81262C4.35976 7.27235 4.15248 7.76565 4.01177 8.2789C3.86366 8.81918 3.30561 9.13709 2.76534 8.98896C2.22507 8.84083 1.90717 8.28278 2.05529 7.74251C2.24262 7.05921 2.51848 6.40265 2.87541 5.79061C3.15763 5.30669 3.77871 5.14316 4.26263 5.42539ZM12.0236 2.05553C12.707 2.24287 13.3635 2.51873 13.9755 2.87567C14.4595 3.15788 14.623 3.77896 14.3408 4.26289C14.0586 4.74683 13.4375 4.91034 12.9536 4.62813C12.4939 4.36002 12.0005 4.15275 11.4873 4.01203C10.947 3.86392 10.6291 3.30586 10.7772 2.76558C10.9253 2.22531 11.4834 1.90741 12.0236 2.05553ZM9.06111 2.75587C9.204 3.29755 8.88072 3.8525 8.33904 3.9954C8.08538 4.06231 7.83274 4.14649 7.58247 4.24867C7.33219 4.35085 7.09283 4.46756 6.86482 4.5973C6.37793 4.87436 5.75861 4.70424 5.48155 4.21735C5.2045 3.73045 5.37461 3.11113 5.86151 2.83407C6.16558 2.66105 6.48393 2.50592 6.81565 2.37048C7.14736 2.23505 7.48332 2.12303 7.82159 2.0338C8.36327 1.89091 8.91822 2.21419 9.06111 2.75587Z" fill="#9CA7BD"/><circle cx="10" cy="10" r="3" fill="#9CA7BD"/></g><defs><clipPath id="clip0_7540_15552"><rect width="16" height="16" fill="white" transform="translate(2 2)"/></clipPath></defs></svg>
                                    <span class="arm_activity_status_filter_value arm_no_auto_complete"><?php esc_html_e('Select Status', 'armember-membership'); ?></span><i class="armfa armfa-caret-down armfa-lg"></i>
                                </dt>
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
                    </div>
                </div>
                <div class="arm_filter_child_row arm_filter_status_subscription_box">
                    <div>
                        <div class="arm_datatable_filter_item arm_filter_plans_box arm_width_100_pct">                        
                            <input type="text" id="arm_status_subscription_filter" class="arm_status_filter arm-selectpicker-input-control" value="<?php echo esc_attr($filter_plan_status); ?>" />
                            <dl class="arm_selectbox arm_width_230">
                                <dt>
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_7540_15552)"><path fill-rule="evenodd" clip-rule="evenodd" d="M16.0731 6.26028C16.5917 6.04852 17.1839 6.2973 17.3956 6.81595C19.0896 10.9651 17.0993 15.702 12.9502 17.396C10.5553 18.3738 7.96399 18.1232 5.87826 16.9415C5.39086 16.6653 5.21962 16.0462 5.49579 15.5588C5.77195 15.0714 6.39094 14.9002 6.87835 15.1764C8.44678 16.0651 10.388 16.2508 12.1834 15.5178C15.2952 14.2474 16.7879 10.6947 15.5174 7.58279C15.3056 7.06414 15.5544 6.47202 16.0731 6.26028ZM3.99514 11.4272C4.06205 11.6808 4.14623 11.9334 4.24841 12.1838C4.35059 12.434 4.46729 12.6734 4.59703 12.9014C4.8741 13.3883 4.70398 14.0076 4.21709 14.2847C3.73019 14.5617 3.11087 14.3916 2.83382 13.9047C2.6608 13.6006 2.50567 13.2823 2.37024 12.9505C2.2348 12.6188 2.12279 12.2829 2.03356 11.9446C1.89067 11.4029 2.21395 10.848 2.75562 10.7051C3.2973 10.5622 3.85225 10.8855 3.99514 11.4272ZM4.26263 5.42539C4.74656 5.70761 4.91007 6.3287 4.62786 6.81262C4.35976 7.27235 4.15248 7.76565 4.01177 8.2789C3.86366 8.81918 3.30561 9.13709 2.76534 8.98896C2.22507 8.84083 1.90717 8.28278 2.05529 7.74251C2.24262 7.05921 2.51848 6.40265 2.87541 5.79061C3.15763 5.30669 3.77871 5.14316 4.26263 5.42539ZM12.0236 2.05553C12.707 2.24287 13.3635 2.51873 13.9755 2.87567C14.4595 3.15788 14.623 3.77896 14.3408 4.26289C14.0586 4.74683 13.4375 4.91034 12.9536 4.62813C12.4939 4.36002 12.0005 4.15275 11.4873 4.01203C10.947 3.86392 10.6291 3.30586 10.7772 2.76558C10.9253 2.22531 11.4834 1.90741 12.0236 2.05553ZM9.06111 2.75587C9.204 3.29755 8.88072 3.8525 8.33904 3.9954C8.08538 4.06231 7.83274 4.14649 7.58247 4.24867C7.33219 4.35085 7.09283 4.46756 6.86482 4.5973C6.37793 4.87436 5.75861 4.70424 5.48155 4.21735C5.2045 3.73045 5.37461 3.11113 5.86151 2.83407C6.16558 2.66105 6.48393 2.50592 6.81565 2.37048C7.14736 2.23505 7.48332 2.12303 7.82159 2.0338C8.36327 1.89091 8.91822 2.21419 9.06111 2.75587Z" fill="#9CA7BD"/><circle cx="10" cy="10" r="3" fill="#9CA7BD"/></g><defs><clipPath id="clip0_7540_15552"><rect width="16" height="16" fill="white" transform="translate(2 2)"/></clipPath></defs></svg>
                                    <span class="arm_subscription_status_filter_value arm_no_auto_complete"><?php esc_html_e('Select Status', 'armember-membership'); ?></span><i class="armfa armfa-caret-down armfa-lg"></i>
                                </dt>
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
                    </div>
                </div>
                <div class="arm_filter_child_row arm_filter_gateway_box">
                    <div>
                        <?php if (!empty($payment_gateways)) { ?>
                        <!--./====================Begin Filter By Payment Gateway Box====================/.-->
                            <div class="arm_datatable_filter_item arm_filter_plans_box arm_filter_gateway_label">
                                <input type="text" id="arm_filter_gateway" class="arm_filter_gateway arm-selectpicker-input-control" value="<?php echo esc_attr($filter_gateway); ?>" />
                                <dl class="arm_selectbox arm_width_230">
                                    <dt>
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_7528_8356)"><path d="M10 1.5C5.30576 1.5 1.5 5.30576 1.5 10C1.5 14.6942 5.30576 18.5 10 18.5C14.6942 18.5 18.5 14.6942 18.5 10C18.5 5.30576 14.6942 1.5 10 1.5ZM12.4416 13.351C12.0708 13.8023 11.5881 14.1066 11.0249 14.2605C10.78 14.327 10.6681 14.4564 10.6821 14.7117C10.6926 14.9636 10.6821 15.2119 10.6786 15.4638C10.6786 15.6877 10.5632 15.8066 10.3428 15.8136C10.1994 15.8171 10.056 15.8206 9.91255 15.8206C9.78663 15.8206 9.6607 15.8206 9.53477 15.8171C9.29691 15.8136 9.18498 15.6772 9.18498 15.4463C9.18148 15.2644 9.18148 15.079 9.18148 14.8971C9.17798 14.4914 9.16399 14.4774 8.77572 14.4144C8.27901 14.334 7.7893 14.222 7.33457 14.0016C6.97778 13.8267 6.9393 13.7393 7.04074 13.3615C7.1177 13.0817 7.19465 12.8019 7.2821 12.5255C7.34506 12.3226 7.40453 12.2317 7.51296 12.2317C7.57593 12.2317 7.65638 12.2632 7.76482 12.3191C8.26852 12.5815 8.8037 12.7284 9.36687 12.7984C9.46132 12.8088 9.55576 12.8158 9.65021 12.8158C9.91255 12.8158 10.1679 12.7669 10.4163 12.6584C11.0424 12.3856 11.1403 11.6615 10.6121 11.2278C10.4337 11.0809 10.2274 10.9724 10.014 10.878C9.46482 10.6366 8.89465 10.4547 8.37696 10.1434C7.53745 9.63971 7.00576 8.95062 7.06872 7.92922C7.13868 6.7749 7.7928 6.05432 8.85267 5.66955C9.28992 5.51214 9.29342 5.51564 9.29342 5.06091C9.29342 4.907 9.28992 4.75309 9.29691 4.59568C9.30741 4.25288 9.36338 4.19342 9.70617 4.18292C9.74465 4.18292 9.78663 4.18292 9.8251 4.18292C9.89156 4.18292 9.95802 4.18292 10.0245 4.18292C10.0525 4.18292 10.0805 4.18292 10.1049 4.18292C10.7556 4.18292 10.7556 4.21091 10.7591 4.91399C10.7626 5.43169 10.7626 5.43169 11.2767 5.51214C11.672 5.5751 12.0463 5.69054 12.4101 5.85144C12.6095 5.93889 12.6864 6.07881 12.6235 6.29218C12.5325 6.607 12.4451 6.92531 12.3471 7.23663C12.2842 7.42551 12.2247 7.51296 12.1128 7.51296C12.0498 7.51296 11.9728 7.48848 11.8749 7.43951C11.3712 7.19465 10.843 7.07572 10.2903 7.07572C10.2204 7.07572 10.1469 7.07922 10.077 7.08272C9.91255 7.09321 9.75165 7.1142 9.59774 7.18066C9.05206 7.41852 8.96461 8.02016 9.42984 8.39095C9.6642 8.57984 9.93354 8.71276 10.2099 8.82819C10.6926 9.02757 11.1753 9.21996 11.6335 9.47181C13.0747 10.2763 13.4665 12.1058 12.4416 13.351Z" fill="#9CA7BD"/></g><defs><clipPath id="clip0_7528_8356"><rect width="17" height="17" fill="white" transform="translate(1.5 1.5)"/></clipPath></defs></svg>
                                        <span class="arm_gateway_filter_value arm_no_auto_complete"><?php esc_html_e('Gateway', 'armember-membership'); ?></span><i class="armfa armfa-caret-down armfa-lg"></i>
                                    </dt>
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
                    </div>
                </div>
                <div class="arm_filter_child_row arm_filter_ptype_box">
                    <div>
                        <div class="arm_datatable_filter_item arm_filter_plans_box arm_filter_ptype_label arm_width_100_pct">
                            <input type="text" id="arm_filter_ptype" class="arm_filter_ptype arm-selectpicker-input-control" value="<?php echo esc_attr($filter_ptype); ?>" />
                            <dl class="arm_selectbox arm_width_230">
                                <dt>
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_7527_8244)"><path d="M16.6666 13.333L17.7778 14.4441L18.8888 13.333" stroke="#9CA7BD" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M4.44446 4.35358C5.85986 3.03145 7.76024 2.22266 9.84672 2.22266C14.224 2.22266 17.7778 5.7824 17.7778 10.1671V14.4449" stroke="#9CA7BD" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M3.33331 6.66678L2.22218 5.55566L1.11108 6.66678" stroke="#9CA7BD" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M15.5555 15.647C14.1401 16.9691 12.2397 17.7779 10.1532 17.7779C5.77592 17.7779 2.22217 14.2181 2.22217 9.83342V5.55566" stroke="#9CA7BD" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 5C7.23868 5 5 7.23868 5 10C5 12.7613 7.23868 15 10 15C12.7613 15 15 12.7613 15 10C15 7.23868 12.7613 5 10 5ZM11.4362 11.9712C11.2181 12.2366 10.9342 12.4156 10.6029 12.5062C10.4588 12.5453 10.393 12.6214 10.4012 12.7716C10.4074 12.9198 10.4012 13.0658 10.3992 13.214C10.3992 13.3457 10.3313 13.4156 10.2016 13.4198C10.1173 13.4218 10.0329 13.4239 9.94856 13.4239C9.87449 13.4239 9.80041 13.4239 9.72634 13.4218C9.58642 13.4198 9.52058 13.3395 9.52058 13.2037C9.51852 13.0967 9.51852 12.9877 9.51852 12.8807C9.51646 12.642 9.50823 12.6337 9.27984 12.5967C8.98765 12.5494 8.69959 12.4835 8.4321 12.3539C8.22222 12.251 8.19959 12.1996 8.25926 11.9774C8.30453 11.8128 8.34979 11.6481 8.40123 11.4856C8.43827 11.3663 8.47325 11.3128 8.53704 11.3128C8.57407 11.3128 8.6214 11.3313 8.68519 11.3642C8.98148 11.5185 9.2963 11.6049 9.62757 11.6461C9.68313 11.6523 9.73868 11.6564 9.79424 11.6564C9.94856 11.6564 10.0988 11.6276 10.2449 11.5638C10.6132 11.4033 10.6708 10.9774 10.3601 10.7222C10.2551 10.6358 10.1337 10.572 10.0082 10.5165C9.68519 10.3745 9.34979 10.2675 9.04527 10.0844C8.55144 9.78807 8.23868 9.38272 8.27572 8.78189C8.31687 8.10288 8.70165 7.67901 9.3251 7.45267C9.5823 7.36008 9.58436 7.36214 9.58436 7.09465C9.58436 7.00412 9.5823 6.91358 9.58642 6.82099C9.59259 6.61934 9.62551 6.58436 9.82716 6.57819C9.84979 6.57819 9.87449 6.57819 9.89712 6.57819C9.93621 6.57819 9.97531 6.57819 10.0144 6.57819C10.0309 6.57819 10.0473 6.57819 10.0617 6.57819C10.4444 6.57819 10.4444 6.59465 10.4465 7.00823C10.4486 7.31276 10.4486 7.31276 10.751 7.36008C10.9835 7.39712 11.2037 7.46502 11.4177 7.55967C11.535 7.61111 11.5802 7.69342 11.5432 7.81893C11.4897 8.00412 11.4383 8.19136 11.3807 8.37449C11.3436 8.4856 11.3086 8.53704 11.2428 8.53704C11.2058 8.53704 11.1605 8.52263 11.1029 8.49383C10.8066 8.34979 10.4959 8.27984 10.1708 8.27984C10.1296 8.27984 10.0864 8.28189 10.0453 8.28395C9.94856 8.29012 9.85391 8.30247 9.76337 8.34156C9.44239 8.48148 9.39095 8.83539 9.66461 9.0535C9.80247 9.16461 9.96091 9.2428 10.1235 9.3107C10.4074 9.42798 10.6914 9.54115 10.9609 9.6893C11.8086 10.1626 12.0391 11.2387 11.4362 11.9712Z" fill="#9CA7BD"/></g><defs><clipPath id="clip0_7527_8244"><rect width="20" height="20" rx="6" fill="white"/></clipPath></defs></svg>
                                    <span class="arm_ptype_filter_value arm_no_auto_complete"><?php esc_html_e('Plan Type', 'armember-membership'); ?></span><i class="armfa armfa-caret-down armfa-lg"></i>
                                </dt>
                                <dd>
                                    <ul data-id="arm_filter_ptype">
                                        <li data-label="<?php esc_attr_e('Plan Type', 'armember-membership'); ?>" data-value="0"><?php esc_html_e('Plan Type', 'armember-membership'); ?></li>
                                        <li data-label="<?php esc_attr_e('One Time', 'armember-membership'); ?>" data-value="one_time"><?php esc_html_e('One Time', 'armember-membership'); ?></li>
                                        <li data-label="<?php esc_attr_e('Recurring', 'armember-membership'); ?>" data-value="subscription"><?php esc_html_e('Recurring', 'armember-membership'); ?></li>
                                    </ul>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="arm_filter_child_row arm_datatable_filter_submit">
                    <div>
                        <input type="button" class="armemailaddbtn" id="arm_subscription_grid_filter_btn" value="<?php esc_attr_e('Apply', 'armember-membership'); ?>"/>
                        <input type="button" class="arm_cancel_btn arm_margin_left_12 hidden_section" value="<?php esc_html_e('Clear','armember-membership');?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="arm_filter_hide_show_btn_section arm_hide">
            <button type="button" class="arm_filter_hide_show_btn" id="arm_filter_hide_show_btn" data-status="0">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_7619_15796)"><g clip-path="url(#clip1_7619_15796)"><path d="M17 1H3C1.89543 1 1 1.89557 1 3.00031V4.17207C1 4.70259 1.21071 5.21137 1.58579 5.58651L7.41421 11.4158C7.78929 11.791 8 12.2998 8 12.8302V18.0027V18.2884C8 18.9211 8.7649 19.2379 9.2122 18.7906L10 18.0027L11.4142 16.5882C11.7893 16.2131 12 15.7043 12 15.1738V12.8302C12 12.2998 12.2107 11.791 12.5858 11.4158L18.4142 5.58651C18.7893 5.21137 19 4.70259 19 4.17207V3.00031C19 1.89557 18.1046 1 17 1Z" stroke="#617191" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></g></g><defs><clipPath id="clip0_7619_15796"><rect width="20" height="20" fill="white"/></clipPath><clipPath id="clip1_7619_15796"><rect width="20" height="20" fill="white"/></clipPath></defs></svg>
            </button>
        </div>
    </div>
</div>
<div class="wrap arm_page arm_subscription_main_wrapper">
	<div class="content_wrapper arm_subscription_wrapper arm_position_relative" id="content_wrapper" >
		<div class="arm_loading_grid" style="display: none;"><?php $arm_loader = $arm_common_lite->arm_loader_img_func();
				echo $arm_loader; //phpcs:ignore ?></div>
		<div class="page_title">
			<?php esc_html_e('Manage Subscriptions','armember-membership');?>
			<div class="arm_add_new_item_box">
				<a class="greensavebtn arm_add_subscriptions_link" href="javascript:void(0);"><img align="absmiddle" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore?>/add_new_icon.svg"><span><?php esc_html_e('Add Subscription', 'armember-membership') ?></span></a>
			</div>	
			<div class="armclear"></div>
		</div>

		<div class="armclear"></div>

        <div class="arm_subscription_tabs">
            <input type="hidden" id="arm_selected_sub_tab" value="subscriptions"/>
            <div class="arm_all_subscription_tab arm_selected_sub_tab">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13.4611 5.96152C12.6708 5.17115 11.2788 4.6214 9.99961 4.58697M6.53809 13.4615C7.28171 14.453 8.66435 15.0185 9.99961 15.0665M9.99961 4.58697C8.47755 4.54602 7.11501 5.23458 7.11501 7.11537C7.11501 10.5769 13.4611 8.84613 13.4611 12.3076C13.4611 14.2819 11.7721 15.1302 9.99961 15.0665M9.99961 4.58697V2.5M9.99961 15.0665V17.4999" stroke="#4D5973" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg><?php esc_html_e('Subscriptions','armember-membership');?>
            </div>
            <div class="arm_all_activities_tab">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.5 10H5L7.5 2.5L12.5 17.5L15 10H17.5" stroke="#4D5973" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg><?php esc_html_e('All Activities','armember-membership');?>
            </div>
            <div class="arm_upcoming_subscription_tab">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.5 1.66669L12.5 1.66669" stroke="#4D5973" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 8.33331L10 11.6666" stroke="#4D5973" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.99998 18.3333C13.6819 18.3333 16.6666 15.3486 16.6666 11.6667C16.6666 7.98477 13.6819 5 9.99998 5C6.31808 5 3.33331 7.98477 3.33331 11.6667C3.33331 15.3486 6.31808 18.3333 9.99998 18.3333Z" stroke="#4D5973" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg><?php esc_html_e('Upcoming Subscriptions','armember-membership');?>
            </div>
            
        </div>

		<div class="arm_subscriptions_list arm_main_wrapper_seperator">
			<form method="GET" id="subscription_plans_list_form" class="data_grid_list">
				<input type="hidden" name="page" value="<?php echo isset( $arm_slugs->arm_manage_subscriptions ) ? esc_attr($arm_slugs->arm_manage_subscriptions) : '';?>" />
				<input type="hidden" name="armaction" value="list" />

				<div id="armmainformnewlist" class="armember_activity_datatable_div arm_hide">
                    <table cellpadding="0" cellspacing="0" border="0" class="display arm_on_display arm_hide_datatable" id="armember_datatable">
                        <thead>
                                <th class="arm_max_width_50 arm_min_width_40"></th>
                                <th class="arm_min_width_100" data-key="arm_log_id"><?php esc_html_e('Invoice ID','armember-membership');?></th>
                                <th data-key="arm_membership_id"><?php esc_html_e('Membership','armember-membership');?></th>
                                <th data-key="arm_username"><?php esc_html_e('Username','armember-membership');?></th>
                                <th data-key="arm_display_name"><?php esc_html_e('Name','armember-membership');?></th>
                                <th data-key="arm_payment_date"><?php esc_html_e('Payment Date','armember-membership');?></th>
                                <th data-key="arm_amount"><?php esc_html_e('Amount','armember-membership');?></th>
                                <th class="center arm_min_width_150" data-key="arm_payment_type"><?php esc_html_e('Payment Type','armember-membership');?></th>
                                <th class="arm_min_width_120" data-key="arm_payment_status"><?php esc_html_e('Status','armember-membership');?></th>
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
                    <table cellpadding="0" cellspacing="0" border="0" class="display arm_on_display arm_hide_datatable arm_no_expand_row arm_datatable_div" id="armember_datatable_1">
                        <thead>
                            <tr>
                                <th class="arm_max_width_50 arm_min_width_40"></th>
                                <th class="arm_min_width_50" data-key="arm_activity_id"><?php esc_html_e('ID','armember-membership');?></th>
                                <th class="arm_min_width_180" data-key="arm_membership_plan_id"><?php esc_html_e('Membership','armember-membership');?></th>
                                <th class="arm_min_width_100" data-key="arm_user_id"><?php esc_html_e('Username','armember-membership');?></th>
                                <th data-key="arm_user_full_name"><?php esc_html_e('Name','armember-membership');?></th>
                                <th data-key="arm_plan_start_date"><?php esc_html_e('Start Date','armember-membership');?></th>
                                <th data-key="arm_plan_end_due_date"><?php esc_html_e('Expire/Next Renewal','armember-membership');?></th>
                                <th class="arm_min_width_100" data-key="arm_plan_amount"><?php esc_html_e('Amount','armember-membership');?></th>
                                <th class="center arm_min_width_100"  data-key="arm_plan_payment_type"><?php esc_html_e('Payment Type','armember-membership');?></th>
                                <th class="arm_min_width_80" data-key="arm_plan_transaction_count"><?php esc_html_e('Transaction','armember-membership');?></th>
                                <th class="arm_width_100 center" data-key="arm_plan_status"><?php esc_html_e('Status','armember-membership');?></th>
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
                                <th class="arm_max_width_50 arm_min_width_40"></th>
                                <th class="arm_min_width_50" data-key="arm_activity_id"><?php esc_html_e('ID','armember-membership');?></th>
                                <th class="arm_min_width_200" data-key="arm_item_id"><?php esc_html_e('Membership','armember-membership');?></th>
                                <th class="arm_min_width_150" data-key="arm_user_login"><?php esc_html_e('Username','armember-membership');?></th>
                                <th class="arm_min_width_150" data-key="name"><?php esc_html_e('Name','armember-membership');?></th>
                                <th class="arm_min_width_120" data-key="arm_date_recorded"><?php esc_html_e('Start Date','armember-membership');?></th>
                                <th class="arm_min_width_150" data-key="arm_next_cycle_date"><?php esc_html_e('Expire/Next Renewal','armember-membership');?></th>
                                <th class="arm_min_width_120" data-key="arm_amount"><?php esc_html_e('Amount','armember-membership');?></th>
                                <th class="center arm_min_width_120" data-key="arm_payment_type"><?php esc_html_e('Payment Type','armember-membership');?></th>
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
			'class' => 'arm_delete_bulk_action_message change_transaction_status_message',
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
        <div class="arm_invoice_detail_container">
            <div class="arm_invoice_detail_popup popup_wrapper arm_invoice_detail_popup_wrapper">
                <div class="popup_wrapper_inner" style="overflow: hidden;">
                    <div class="popup_header arm_text_align_center" >
                        <span class="popup_close_btn arm_popup_close_btn arm_invoice_detail_close_btn"></span>
                        <span class="add_rule_content"><?php esc_html_e('Invoice Detail','armember-membership' );?></span>
                    </div>
                    <div class="popup_content_text arm_invoice_detail_popup_text arm_padding_24" id="arm_invoice_detail_popup_text" ></div>
                </div>
            </div>
        </div>
		<div class="arm_preview_log_detail_container">
            <div class="arm_preview_log_detail_popup popup_wrapper arm_preview_log_detail_popup_wrapper" style="width:600px;">
                <div class="popup_wrapper_inner" style="overflow: hidden;">
                    <div class="popup_header">
                        <span class="popup_close_btn arm_popup_close_btn arm_preview_log_detail_close_btn"></span>
                        <span class="add_rule_content"><?php esc_html_e( 'Transaction Details', 'armember-membership' ); ?></span>
                    </div>
                    <div class="popup_content_text arm_transactions_detail_popup_text"></div>
                    <div class="armclear"></div>
                </div>
            </div>
        </div>
		<div class="arm_preview_failed_log_detail_container"></div>
	</div>
</div>

<?php require_once(MEMBERSHIPLITE_VIEWS_DIR.'/arm_view_member_details.php')?>

<div class="arm_add_new_subscription_wrapper popup_wrapper">
	<form method="post" action="#" id="arm_add_new_subscription_wrapper_frm" class="arm_admin_form arm_add_new_subscription_wrapper_frm">
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">	
				<td class="add_new_subscription_close_btn arm_popup_close_btn"></td>
				<td class="popup_header arm_font_size_20 arm_font_weight_500"><?php esc_html_e('Add New Subscription','armember-membership');?></td>
				<td class="popup_content_text">
					<div class="arm_table_label_on_top arm_padding_0">	
                        <div class="form-field form-required arm_padding_0">
                            <span class="arm_edit_plan_lbl arm_margin_bottom_12"><label for="arm_user_id"><?php esc_html_e('Member','armember-membership'); ?></label></span>
                            <div class="arm_auto_user_field">
                                <input id="arm_user_auto_selection" type="text" name="arm_user_ids" value="" placeholder="<?php esc_attr_e('Search by username or email...', 'armember-membership');?>" data-msg-required="<?php esc_attr_e('Please select user.', 'armember-membership');?>" required>
                                <input type="hidden" name="arm_display_admin_user" id="arm_display_admin_user" value="0">
                                <div class="arm_users_items arm_required_wrapper" id="arm_users_items" style="display: none;"></div>
                                <span class="arm_plan_users_error"></span>
                            </div>
                        </div>
                        <div class="form-field form-required arm_transaction_membership_plan_wrapper arm_padding_0 arm_margin_top_28">
                            <span class="arm_edit_plan_lbl arm_margin_bottom_12"><?php esc_html_e('Select Membership Plan','armember-membership'); ?></span>
                            <div class="arm_display_flex">
                                <input type="text" class="arm-selectpicker-input-control arm_user_plan_change_input_get_cycle" id="arm_plan_id" name="membership_plan" value="" data-manage-plan-grid="1" data-msg-required="<?php esc_attr_e('Please select atleast one membership', 'armember-membership');?>"/>
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
                            </div>
                            <span class="arm_plan_error"></span>
                        </div>
                        <div class="form-field form-required arm_selected_plan_cycle arm_padding_0 arm_margin_top_28"></div>
                    </div>
					<div class="armclear"></div>
				</td>
				<td class="popup_content_btn popup_footer arm_padding_top_0 arm_padding_bottom_33" style="border-top:none">
					<div class="popup_content_btn_wrapper arm_subscription_btn_wrapper arm_margin_top_0">
                        <div class="arm_soild_divider arm_margin_0"></div>
                        <input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr( wp_create_nonce( 'arm_wp_nonce' ) ); //phpcs:ignore?>" class="valid arm_valid" aria-invalid="false">
						<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/arm_loader.gif'; //phpcs:ignore?>" id="arm_loader_img_add_subscription" class="arm_loader_img arm_submit_btn_loader"  style="top: 15px;float: <?php echo (is_rtl()) ? 'right' : 'left';?>;display: none;" width="20" height="20" />
						<button class="arm_cancel_btn add_new_subscription_close_btn arm_margin_0 arm_margin_right_10" type="button"><?php esc_html_e('Cancel','armember-membership');?></button>
						<button class="arm_save_btn arm_new_subscription_button arm_margin_right_0" type="submit" data-type="add"><?php esc_html_e('Save', 'armember-membership') ?></button>
					</div>
				</td>
			</tr>
		</table>
		<div class="armclear"></div>
	</form>
    <?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
    <input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
</div>
<!-- Popup Contnet for transaction list under subscriptions -->
<div class="arm_members_list_detail_popup popup_wrapper arm_members_list_detail_popup_wrapper" >
	<div class="arm_loading_grid" id="arm_loading_grid_members" style="display: none;"><?php echo $arm_common_lite->arm_loader_img_func();?></div>
    <div class="popup_wrapper_inner" style="overflow: hidden;">
        <div class="popup_header page_title">
            <span class="popup_close_btn arm_popup_close_btn arm_transction_list_detail_close_btn"></span>
            <span class="add_rule_content"><?php esc_html_e('Subscription Transactions', 'armember-membership'); ?> (<span class="arm_user_name_txt"></span>)</span>
        </div>
        <div class="popup_content_text arm_members_list_detail_popup_text arm_transaction_list_detail_popup_text">
            <table cellspacing="0" class="display dataTable arm_transaction_table arm_no_margin arm_width_100_pct" id="example">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Invoice ID', 'armember-membership');?></th>
                        <th><?php esc_html_e('Transaction ID', 'armember-membership');?></th>
                        <th><?php esc_html_e('Subscription ID', 'armember-membership');?></th>
                        <th><?php esc_html_e('Payment Gateway', 'armember-membership');?></th>
                        <th class="dt-right"><?php esc_html_e('Amount', 'armember-membership');?></th>
                        <th class="center"><?php esc_html_e('Status', 'armember-membership')?></th>
                        <th><?php esc_html_e('Transaction Date', 'armember-membership');?></th>
                    </tr>
                </thead>
            </table>
            <input type="hidden" name="search_grid" id="search_grid" value="<?php esc_html_e('Search','armember-membership');?>"/>
            <input type="hidden" name="entries_grid" id="entries_grid" value="<?php esc_html_e('members','armember-membership');?>"/>
            <input type="hidden" name="show_grid" id="show_grid" value="<?php esc_html_e('Show','armember-membership');?>"/>
            <input type="hidden" name="showing_grid" id="showing_grid" value="<?php esc_html_e('Showing','armember-membership');?>"/>
            <input type="hidden" name="to_grid" id="to_grid" value="<?php esc_html_e('to','armember-membership');?>"/>
            <input type="hidden" name="of_grid" id="of_grid" value="<?php esc_html_e('of','armember-membership');?>"/>
            <input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php esc_html_e('No matching members found','armember-membership');?>"/>
            <input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php esc_html_e('There is no any member found.','armember-membership');?>"/>
            <input type="hidden" name="filter_grid" id="filter_grid" value="<?php esc_html_e('filtered from','armember-membership');?>"/>
            <input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php esc_html_e('total','armember-membership');?>"/>
            <?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
            <input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
        </div>
        <div class="armclear"></div>
    </div>
</div>
<script type="text/javascript">
    __ARM_Showing = '<?php esc_html_e('Showing','armember-membership'); ?>';
    __ARM_Showing_empty = '<?php esc_html_e('Showing 0 - 0 of 0 transactions','armember-membership'); ?>';
    __ARM_to = '-';
    __ARM_of = '<?php esc_html_e('of','armember-membership'); ?>';
    __ARM_members = '<?php esc_html_e('transactions','armember-membership'); ?>';
    __ARM_Show = '<?php esc_html_e('Show','armember-membership'); ?>';
    __ARM_NO_FOUNT = '<?php esc_html_e('No any transactions found.','armember-membership'); ?>';
    __ARM_NO_MATCHING = '<?php esc_html_e('No matching transactions found.','armember-membership'); ?>';
</script>
<?php
	echo $ARMemberLite->arm_get_need_help_html_content('manage-subscription'); //phpcs:ignore
?>