<?php
global $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_global_settings,  $arm_payment_gateways, $arm_subscription_plans, $arm_transaction,$arm_common_lite;
$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST); //phpcs:ignore
$payment_gateways             = $arm_payment_gateways->arm_get_all_payment_gateways();
$global_currency              = $arm_payment_gateways->arm_get_global_currency();
$general_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : array();
$arm_currency_decimal = isset($general_settings['arm_currency_decimal_digit']) ? $general_settings['arm_currency_decimal_digit'] : 2;
$nowDate                      = current_time( 'mysql' );
$filter_gateway = (!empty($posted_data['gateway'])) ? sanitize_text_field($posted_data['gateway']) : '0';
$filter_ptype = (!empty($posted_data['ptype'])) ? sanitize_text_field($posted_data['ptype']) : '0';
$filter_pmode = (!empty($posted_data['pmode'])) ? sanitize_text_field($posted_data['pmode']) : '0';
$filter_pstatus = (!empty($posted_data['pstatus'])) ? sanitize_text_field($posted_data['pstatus']) : '0';
$filter_search = (!empty($posted_data['search'])) ? sanitize_text_field($posted_data['search']) : '';
$default_hide                 = array(
	'arm_transaction_id'     => 'Transaction ID',
	'arm_user_id'            => 'User',
	'arm_plan_id'            => 'Membership',
	'arm_payment_gateway'    => 'Gateway',
	'arm_payment_type'       => 'Payment Type',
	'arm_transaction_status' => 'Transaction Status',
	'arm_created_date'       => 'Payment Date',
	'arm_amount'             => 'Amount',
);
if($ARMemberLite->is_arm_pro_active)
{
	$default_hide = array(
	    'arm_transaction_id' => 'Transaction ID',
	    'arm_invoice_id' => 'Invoice ID',
	    'arm_user_id' => 'User',    
	    'arm_plan_id' => 'Membership',
	    'arm_payment_gateway' => 'Gateway',
	    'arm_payment_type' => 'Payment Type',
	    'arm_transaction_status' => 'Transaction Status',
	    'arm_created_date' => 'Payment Date',
	    'arm_amount' => 'Amount',
	);
}
$user_id                      = get_current_user_id();
$transaction_show_hide_column = maybe_unserialize( get_user_meta( $user_id, 'arm_transaction_hide_show_columns', true ) );

$i           = 1;
/*$column_hide = '';
if ( ! empty( $transaction_show_hide_column ) ) {
	foreach ( $transaction_show_hide_column as $value ) {
		if ( $value != 1 ) {
			$column_hide = $column_hide . $i . ',';
		}
		$i++;
	}
}*/

if(isset($posted_data["arm_export_phistory"]) && $posted_data["arm_export_phistory"] == 1 && $ARMemberLite->is_arm_pro_active) {
    $filter_gateway = isset($_REQUEST['arm_filter_gateway']) ? sanitize_text_field($_REQUEST['arm_filter_gateway']) : ''; //phpcs:ignore
    $filter_ptype = isset($_REQUEST['arm_filter_ptype']) ? sanitize_text_field($_REQUEST['arm_filter_ptype']) : ''; //phpcs:ignore
    $filter_pmode = isset($_REQUEST['arm_filter_pmode']) ? sanitize_text_field($_REQUEST['arm_filter_pmode']) : ''; //phpcs:ignore
    $filter_pstatus = isset($_REQUEST['arm_filter_pstatus']) ? sanitize_text_field($_REQUEST['arm_filter_pstatus']) : ''; //phpcs:ignore
    $payment_start_date = isset($_REQUEST['arm_filter_pstart_date']) ? sanitize_text_field($_REQUEST['arm_filter_pstart_date']) : ''; //phpcs:ignore
    $payment_end_date = isset($_REQUEST['arm_filter_pend_date']) ? sanitize_text_field($_REQUEST['arm_filter_pend_date']) : ''; //phpcs:ignore
    $sSearch = isset($_REQUEST['armmanagesearch_new_transaction']) ? sanitize_text_field($_REQUEST['armmanagesearch_new_transaction']) : ''; //phpcs:ignore

    $date_time_format = $arm_global_settings->arm_get_wp_date_time_format();

    $where_plog = $wpdb->prepare("WHERE 1=1 AND arm_display_log = %d ",1);

    if (!empty($filter_gateway) && $filter_gateway != '0') {
        $where_plog .= $wpdb->prepare(" AND `arm_payment_gateway` = %s",$filter_gateway);
    }
    if (!empty($filter_ptype) && $filter_ptype != '0') {
        $where_plog .= $wpdb->prepare(" AND `arm_payment_type` = %s",$filter_ptype);
    }
    if (!empty($filter_pmode) && $filter_pmode != '0') {
        $where_plog .= $wpdb->prepare(" AND `arm_payment_mode` = %s",$filter_pmode);
    }
    
    if (!empty($filter_pstatus) && $filter_pstatus != '0') {
        $filter_pstatus = strtolower($filter_pstatus);
        if(!in_array($filter_pstatus,array('success','pending','canceled'))) {
			$status_query = $wpdb->prepare(" AND ( LOWER(`arm_transaction_status`)=%s",$filter_pstatus);
            $status_query .= ")";
        }
        switch ($filter_pstatus) {
            case 'success':
				$status_query = $wpdb->prepare(" AND `arm_transaction_status` IN (%s,%s)",$filter_pstatus,'1');
                break;
            case 'pending':
				$status_query = $wpdb->prepare(" AND `arm_transaction_status` IN (%s,%s)",$filter_pstatus,'0');
                break;
            case 'canceled':
                $status_query = $wpdb->prepare(" AND `arm_transaction_status` IN (%s,%s)",$filter_pstatus,'2');
                break;
        }
        $where_plog .= $status_query;
    }

    $pt_where = $bt_where = "";
    if(!empty($payment_start_date)) {
        $payment_start_date = date("Y-m-d", strtotime($payment_start_date)); //phpcs:ignore
        $pt_where .= $wpdb->prepare(" WHERE `pt`.`arm_created_date` >= %s",$payment_start_date);
        $bt_where .= $wpdb->prepare(" WHERE `bt`.`arm_created_date` >= %s",$payment_start_date);
    }

    if(!empty($payment_end_date)) {
        $payment_end_date = date("Y-m-d", strtotime("+1 day", strtotime($payment_end_date))); //phpcs:ignore
        if($pt_where != "") $pt_where .= " AND "; else $pt_where = " WHERE ";
        $pt_where .= $wpdb->prepare(" `pt`.`arm_created_date` < %s",$payment_end_date);

        if($bt_where != "") $bt_where .= " AND "; else $bt_where = " WHERE ";
        $bt_where .= $wpdb->prepare(" `bt`.`arm_created_date` < %s",$payment_end_date);
    }

    $search_ = "";
    if ($sSearch != '') {
        $search_ = $wpdb->prepare(" AND (`arm_payment_history_log`.`arm_transaction_id` LIKE %s OR `arm_payment_history_log`.`arm_payer_email` LIKE %s OR `arm_payment_history_log`.`arm_created_date` LIKE %s OR `arm_payment_history_log`.`arm_first_name` LIKE %s OR `arm_payment_history_log`.`arm_last_name` LIKE %s OR `arm_user_login` LIKE %s OR `arm_user_email` LIKE %s ) ",'%'.$sSearch.'%','%'.$sSearch.'%','%'.$sSearch.'%','%'.$sSearch.'%','%'.$sSearch.'%','%'.$sSearch.'%','%'.$sSearch.'%');
    }

    if(empty($pt_where))
    {
        $pt_where .= 'WHERE 1=1';
    }

    $orderby = "ORDER BY `arm_payment_history_log`.`arm_invoice_id` DESC";
    $ctquery = "SELECT pt.arm_log_id,pt.arm_invoice_id,pt.arm_user_id,pt.arm_first_name,pt.arm_last_name,pt.arm_plan_id,pt.arm_payer_email,pt.arm_transaction_id,pt.arm_amount,pt.arm_currency,pt.arm_is_trial,pt.arm_payment_gateway,pt.arm_payment_mode,pt.arm_transaction_status,pt.arm_created_date,pt.arm_payment_type,pt.arm_extra_vars,sp.arm_subscription_plan_name,wpu.user_login as arm_user_login, wpu.user_email as arm_user_email,pt.arm_display_log as arm_display_log FROM `" . $ARMemberLite->tbl_arm_payment_log . "` pt LEFT JOIN `" . $ARMemberLite->tbl_arm_subscription_plans . "` sp ON pt.arm_plan_id = sp.arm_subscription_plan_id LEFT JOIN `" . $wpdb->users . "` wpu ON pt.arm_user_id = wpu.ID " . $pt_where." AND arm_is_post_payment = 0 AND arm_paid_post_id = 0 AND arm_is_gift_payment = 0";
    $ptquery = "{$ctquery}";
        
    $payment_grid_query = "SELECT * FROM (" . $ptquery . ") AS arm_payment_history_log {$where_plog} {$search_} {$orderby}";

    $payment_log = $wpdb->get_results($payment_grid_query, ARRAY_A); //phpcs:ignore --Reason $payment_grid_query is a query

        $final_log = array();
        $tmp = array (
            "Transaction_Id" => '',
            "Invoice_Id" => '',
            "First_Name" => '',
            "Last_Name" => '',
            "User" => '',
            "User Email" => '',
            "Membership" => '',
            "Gateway" => '',
            "Payment_Type" => '',
            "Payer_Email" => '',
            "Transaction_Status" => '',
            "Payment_Date" => '',
            "Amount" => '',
            "Credit_Card_Number" => ''
        );
        $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
        $arm_all_plan_arr = array();
        foreach ($payment_log as $row) {
            $ccn = maybe_unserialize($row["arm_extra_vars"]);
            $arm_transaction_status = $row["arm_transaction_status"];
            switch ($arm_transaction_status) {
                case '0':
                    $arm_transaction_status = 'pending';
                    break;
                case '1':
                    $arm_transaction_status = 'success';
                    break;
                case '2':
                    $arm_transaction_status = 'canceled';
                    break;
                default:
                    $arm_transaction_status = $row["arm_transaction_status"];
                    break;
            }
            $tmp["Transaction_Id"] = $row["arm_transaction_id"];
            if($tmp["Transaction_Id"] == "-") {
                $tmp["Transaction_Id"] = "";
            }
            $tmp["Invoice_Id"] = $row["arm_invoice_id"];
            $tmp["First_Name"] = $row["arm_first_name"];
            $tmp["Last_Name"] = $row["arm_last_name"];
            $tmp["User"] = $row["arm_user_login"];
            $tmp["User Email"] = $row["arm_user_email"];
            $tmp["Membership"] = !empty($row["arm_subscription_plan_name"]) ? stripslashes_deep($row["arm_subscription_plan_name"]) : '';
            $tmp["Gateway"] = $row["arm_payment_gateway"] == "" ? esc_html__('Manual', 'armember-membership') : $arm_payment_gateways->arm_gateway_name_by_key($row["arm_payment_gateway"]);
            $tmp["Payment_Type"] = "";
            $tmp["Payer_Email"] = $row["arm_payer_email"];
            $tmp["Transaction_Status"] = $arm_transaction_status;
            $tmp["Payment_Date"] = date_i18n($date_time_format, strtotime($row["arm_created_date"]));
            $tmp["Amount"] = number_format($row["arm_amount"],$arm_currency_decimal) . " " . $row["arm_currency"];
            $tmp["Credit_Card_Number"] = isset($ccn["card_number"]) ? $ccn["card_number"] : '';
            if($tmp["Credit_Card_Number"] == "-") {
                $tmp["Credit_Card_Number"] = "";
            }

            $log_payment_mode = $row["arm_payment_mode"];
            $plan_id = $row["arm_plan_id"];
            $user_id = $row["arm_user_id"];
            $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
            $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
            $oldPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);
            $arm_old_plan_detail = $oldPlanData['arm_current_plan_detail'];
            if (!empty($arm_old_plan_detail)) {
                $plan_info = new ARM_Plan($plan_id);
                $plan_info->init((object) $arm_old_plan_detail);
            }
            else
            {
                if(!empty($arm_all_plan_arr[$plan_id]))
                {
                    $plan_info = $arm_all_plan_arr[$plan_id];
                }
                else
                {
                    $plan_info = new ARM_Plan($plan_id);
                    $arm_all_plan_arr[$plan_id] = $plan_info;
                }
            }
            //$plan_info = new ARM_Plan($plan_id);
            $payment_type_text = $user_payment_mode = "";

            $payment_type = $row['arm_payment_type'];

            if($plan_info->is_recurring()) {
                if($log_payment_mode != '') {
                    if($log_payment_mode == 'manual_subscription') {
                        $user_payment_mode .= "";
                    }
                    else {
                        $user_payment_mode .= "(" . esc_html__('Automatic','armember-membership') . ")";
                    }
                }
                //$payment_type = 'subscription';
                $payment_type = $plan_info->options['payment_type'];
            }

            if($payment_type =='one_time') {
                $payment_type_text = esc_html__('One Time', 'armember-membership');
            }
            else if($payment_type == 'subscription') {
                $payment_type_text = esc_html__('Subscription', 'armember-membership');
            }

            if($row["arm_is_trial"] == 1) {
                $arm_trial = "(" . esc_html__('Trial Transaction','armember-membership') . ")";
            }
            else {
                $arm_trial = '';
            }

            $tmp["Payment_Type"] = $payment_type_text . " " . $user_payment_mode . " " . $arm_trial;
            array_push($final_log, $tmp);
        }

        ob_clean();
        ob_start();
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename=ARMember-export-payment-history.csv");
        header("Content-Transfer-Encoding: binary");
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys($tmp));
        if(!empty($final_log)) {
            foreach ($final_log as $row) {
                fputcsv($df, $row);
            }
        }
        fclose($df); //phpcs:ignore
        exit;
}
?>
<script type="text/javascript" charset="utf-8">
// <![CDATA[

	jQuery(document).ready(function () {
		jQuery('#armember_datatable').dataTable().fnDestroy();
		arm_load_transaction_list_grid(false);
		var count_checkbox = jQuery('.chkstanard:checked').length;
		if(count_checkbox > 0)
		{
			jQuery('.arm_bulk_action_section').removeClass('hidden_section');
		}
		else{	
			jQuery('.arm_bulk_action_section').addClass('hidden_section');
		}	
	});

	jQuery(document).on('change','.chkstanard',function()
	{
		var count_checkbox = jQuery('.chkstanard:not(#cb-select-all-1):checked').length;
		var total_checkbox = jQuery('.chkstanard:not(#cb-select-all-1)').length;
		if(count_checkbox > 0)
		{
			jQuery('.arm_selected_chkcount').html(count_checkbox);
			jQuery('.arm_selected_chkcount_total').html(total_checkbox);		
			jQuery('.arm_bulk_action_section').removeClass('hidden_section').show();
		}
		else{		
			jQuery('.arm_bulk_action_section').addClass('hidden_section').hide();
		}
	});

	jQuery(document).on('click','.arm_reset_bulk_action',function(){
		jQuery('.chkstanard:checked').each(function(){
			jQuery(this).prop('checked',false).trigger('change');
		})
	});

	jQuery(document).on('click','#arm_transaction_grid_filter_btn',function(){	
		arm_transaction_grid_load_filter_data();	
		arm_load_trasaction_list_filtered_grid();
	});

	function arm_transaction_grid_load_filter_data() {
		var arm_selected_gateway = jQuery('.arm_filter_gateway_label').find('#arm_filter_gateway').val();
		if (arm_selected_gateway != '') {
			var gateway_label = jQuery('.arm_filter_gateway_label').find('li[data-value="'+arm_selected_gateway+'"]').attr('data-label');
			jQuery('.arm_gateway_filter_value').html(gateway_label);
		} else {
			var gateway_label = jQuery('.arm_filter_gateway_label').find('li[data-value="0"]').attr('data-label');
			jQuery('.arm_gateway_filter_value').html(gateway_label);
		}

		var arm_selected_ptype = jQuery('.arm_filter_ptype_label').find('#arm_filter_ptype').val();
		if (arm_selected_ptype != '') {
			var ptype_label = jQuery('.arm_filter_ptype_label').find('li[data-value="'+arm_selected_ptype+'"]').attr('data-label');
			jQuery('.arm_ptype_filter_value').html(ptype_label);
		} else {
			var ptype_label = jQuery('.arm_filter_ptype_label').find('li[data-value="0"]').attr('data-label');
			jQuery('.arm_ptype_filter_value').html(ptype_label);
		}

		var arm_selected_pmode = jQuery('.arm_filter_pmode_label').find('#arm_filter_pmode').val();
		if (arm_selected_pmode != '') {
			var pmode_label = jQuery('.arm_filter_pmode_label').find('li[data-value="'+arm_selected_pmode+'"]').attr('data-label');
			jQuery('.arm_pmode_filter_value').html(pmode_label);
		} else {
			var pmode_label = jQuery('.arm_filter_pmode_label').find('li[data-value="0"]').attr('data-label');
			jQuery('.arm_pmode_filter_value').html(pmode_label);
		}

		var arm_selected_pstatus = jQuery('.arm_filter_pstatus_label').find('#arm_filter_pstatus').val();
		if (arm_selected_pstatus != '') {
			var pstatus_label = jQuery('.arm_filter_pstatus_label').find('li[data-value="'+arm_selected_pstatus+'"]').attr('data-label');
			jQuery('.arm_pstatus_filter_value').html(pstatus_label);
		} else {
			var pstatus_label = jQuery('.arm_filter_pstatus_label').find('li[data-value="0"]').attr('data-label');
			jQuery('.arm_pstatus_filter_value').html(pstatus_label);
		}
	}

	function arm_load_trasaction_list_filtered_grid() {
		jQuery('#armember_datatable').dataTable().fnDestroy();
		arm_load_transaction_list_grid(true);
	}

	function arm_reset_transaction_grid_filter(){
		hideConfirmBoxCallback_filter('manage_transaction_filter');
		jQuery('#arm_filter_pstart_date_hidden').val('');
		jQuery('#arm_filter_pend_date_hidden').val('');
		jQuery('#arm_filter_pstart_date').val('').trigger('change');
		jQuery('#arm_filter_pend_date').val('').trigger('change');
		if (jQuery.isFunction( jQuery().datetimepicker )) {
			jQuery('#arm_filter_pstart_date,#arm_filter_pend_date').val('').datetimepicker();
		}	
		jQuery('#arm_filter_pstart_date').attr('value','');
		jQuery('#arm_filter_pend_date').attr('value','');
	}

	function arm_reset_transaction_grid(){
		arm_reset_transaction_grid_filter();
		if(!jQuery('.arm_filter_data_options').hasClass('hidden_section'))
		{
			jQuery('.arm_reset_bulk_action').trigger('click');
			jQuery('.arm_filter_data_options').addClass('hidden_section');
			jQuery('.manage_transaction_filter_btn').removeAttr('disabled', 'disabled');
			jQuery('.manage_transaction_filter_btn').removeClass('armopen');
			jQuery('#armember_datatable').dataTable().fnDestroy();
			arm_load_transaction_list_grid(false);
		}
	}

	jQuery(document).on('keyup','#armmanagesearch_new_transaction', function (e) {
		// e.stopPropagation();
		var arm_search_val = jQuery(this).val();
		jQuery('#armmanagesearch_new_transaction:last-child').val(arm_search_val);
		if (e.keyCode == 13 || 'Enter' == e.key) {
			jQuery('#armember_datatable').dataTable().fnDestroy();
			arm_transaction_grid_load_filter_data();
			arm_load_transaction_list_grid();
			return false;
		}
	});

	function show_grid_loader(){
		jQuery('.arm_bulk_action_section').hide();
		jQuery('#transactions_list_form .dataTables_scroll').hide();
		jQuery('#transactions_list_form .footer').hide();
		jQuery('#transactions_list_form .arm_loading_grid').show();
	}

	function arm_load_transaction_list_grid(is_filtered) {

	var __ARM_Showing = '<?php echo addslashes( esc_html__( 'Showing', 'armember-membership' ) ); //phpcs:ignore ?>';
	var __ARM_Showing_empty = '<?php echo addslashes( esc_html__( 'Showing','armember-membership').' <span class="arm-black-350 arm_font_size_15">0<span> '.esc_html__('of', 'armember-membership' ).' <span class="arm-black-350 arm_font_size_15">0</span> of 0 '.esc_html__('payment', 'armember-membership' ) ); //phpcs:ignore ?>';
	var __ARM_to = '-';
	var __ARM_of = '<?php echo addslashes( esc_html__( 'of', 'armember-membership' ) ); //phpcs:ignore ?>'; 
	var __ARM_transactions = '<?php esc_html_e( 'payment', 'armember-membership' ); //phpcs:ignore ?>'; 
	var __ARM_Show = '<?php echo addslashes( esc_html__( 'Show', 'armember-membership' ) ); //phpcs:ignore ?>';
	var __ARM_NO_FOUNT = '<?php echo addslashes( esc_html__( 'No any transaction found yet.', 'armember-membership' ) ); //phpcs:ignore ?>'; 
	var __ARM_NO_MATCHING = '<?php echo addslashes( esc_html__( 'No matching transactions found.', 'armember-membership' ) ); //phpcs:ignore ?>';

		var payment_gateway = jQuery("#arm_filter_gateway").val();
		var payment_type = jQuery("#arm_filter_ptype").val();
		var payment_mode = jQuery("#arm_filter_pmode").val();
		var payment_status = jQuery("#arm_filter_pstatus").val();
		var search_term = jQuery("#armmanagesearch_new_transaction").val();
		var payment_start_date = jQuery("#arm_filter_pstart_date_hidden").val();
		var payment_end_date = jQuery("#arm_filter_pend_date_hidden").val();
		var ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); //phpcs:ignore ?>";
		var filtered_data = (typeof is_filtered !== 'undefined' && is_filtered !== false) ? true : false;
		var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();


		var nColVisCols = [];
		var arm_cols_hide = '<?php echo count( $default_hide ); ?>';
		for( var cv = 1; cv <= arm_cols_hide ; cv++ ){
			nColVisCols.push( cv );
		}
		var __ARM_Coupon_List_Left = [1,2,3,7];
		var __ARM_max_List = [2,3];
		var __ARM_max_List_right = [9];
		var __ARM_Sortable_List = [0,1,9];
		var __ARM_Max_List_80 = [5];
		var __ARM_Max_List_100 =  [];
		var __ARM_Max_List_150 =  [4,6];
		var __ARM_Coupon_List_right = [];
		var __ARM_Max_List_120 = [7,8];
		<?php if($ARMemberLite->is_arm_pro_active)
		{?>
			__ARM_Coupon_List_right = [11];
			__ARM_max_List_right = [10];
			var __ARM_Sortable_List = [0,1,10];
			var __ARM_Max_List_100 = [3];
			var __ARM_Max_List_80 = [6,9];
			__ARM_Max_List_150 =  [2,4,5,9];
			var __ARM_max_List = [2];
			var __ARM_Max_List_120 = [7,8];
		<?php }?>	
		var __ARM_Coupon_List_Center = [];	
		var oTables = jQuery('#armember_datatable').dataTable({
			"bProcessing": false,
			"oLanguage": {
				"sProcessing": show_grid_loader(),
				"sInfo": __ARM_Showing + " <span class='arm-black-350 arm_font_size_15'>_START_</span> " + __ARM_to + " <span class='arm-black-350 arm_font_size_15'>_END_</span> " + __ARM_of + " <span class='arm-black-350 arm_font_size_15'>_TOTAL_</span> " + __ARM_transactions,
				"sInfoEmpty": __ARM_Showing_empty,
				"sLengthMenu": __ARM_Show + "_MENU_",
				"sEmptyTable": __ARM_NO_FOUNT,
				"sZeroRecords": __ARM_NO_MATCHING
			},
			"language":{
				"searchPlaceholder":"<?php esc_html_e( 'Search', 'armember-membership' ); ?>",
				"search":"",
			},
			"buttons":[],    
			"bServerSide": true,
			"sAjaxSource": __ARMAJAXURL,
			"sServerMethod": "POST",
			"fnServerParams": function (aoData) {
				aoData.push({"name": "action", "value": "arm_load_transactions"});
				aoData.push({"name": "gateway", "value": payment_gateway});
				aoData.push({"name": "payment_type", "value": payment_type});
				aoData.push({"name": "payment_status", "value": payment_status});
				aoData.push({"name": "payment_mode", "value": payment_mode});
				aoData.push({"name": "payment_start_date", "value": payment_start_date});
				aoData.push({"name": "payment_end_date", "value": payment_end_date});
				aoData.push({"name": "sSearch", "value": search_term});
				aoData.push({"name": "sColumns", "value": null});
				aoData.push({"name": "_wpnonce", "value": _wpnonce});
			},
			"bRetrieve": false,
			"sDom": '<"H"CBfr>t<"footer"ipl>',
			"sPaginationType": "four_button",
			"bJQueryUI": true,
			"bPaginate": true,
			"bAutoWidth": false,
			"sScrollX": "100%",
			"bScrollCollapse": true,
			"aoColumnDefs": [
				{"bSortable": false, "aTargets": __ARM_Sortable_List},			
				{"aTargets":[0],"sClass":"arm_padding_right_0 arm_min_width_40 center noVis"},
				{"aTargets":[1],"sClass":"arm_padding_left_0 arm_min_width_30 center noVis"},
				{"sClass":"arm_min_width_150","aTargets":__ARM_Max_List_150},
				{"sClass":"arm_min_width_120","aTargets":__ARM_Max_List_120},
				{"sClass":"arm_min_width_200","aTargets":__ARM_max_List},
				{"sClass":"arm_min_width_100","aTargets":__ARM_Max_List_100},			
				{"sClass":"arm_min_width_80","aTargets":__ARM_Max_List_80},			
				{"sClass": "dt-right", "aTargets": __ARM_Coupon_List_right},
				{"sClass": "arm_min_width_100 dt-right arm_padding_right_24", "aTargets": __ARM_max_List_right},
				{ "aTargets": -1, "responsivePriority": 1 }
			],
			"responsive":{
                details: {
					type: 'column',
					target: '' // This removes the dtr-control click event
				}
            },
			"bStateSave": true,
			"iCookieDuration": 60 * 60,
			"sCookiePrefix": "arm_datatable_",
			"aLengthMenu": [10, 25, 50, 100, 150, 200],
			"fnPreDrawCallback": function () {
				show_grid_loader()
			},
			"fnCreatedRow": function( nRow, aData, iDataIndex ) {
				jQuery(nRow).find('.arm_grid_action_btn_container').each(function () {
					jQuery(this).parent().addClass('armGridActionTD');
					jQuery(this).parent().attr('data-key', 'armGridActionTD');
				});
			},
			"fnDrawCallback": function () {
				arm_show_data();
				jQuery('#transactions_list_form .arm_loading_grid').hide();
				<?php if( !empty($_REQUEST['arm_log_id']) ) { ?>
                		    arm_preview_log_detail_by_url();
				<?php } ?>
				jQuery('#transactions_list_form #armember_datatable_wrapper').show();
				jQuery('#transactions_list_form .dataTables_scroll').show();
				jQuery('#transactions_list_form .footer').show();
				jQuery(".cb-select-all-th").removeClass('sorting_asc');
				jQuery("#cb-select-all-1").prop("checked", false);
				arm_selectbox_init();
				if (filtered_data == true) {
					var filter_box = jQuery('#arm_filter_wrapper_after_filter').html();
					jQuery('div#armember_datatable_filter').parent().append(filter_box);
					jQuery('div#armember_datatable_filter').hide();
				}
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
				jQuery('#arm_transaction_grid_filter_btn').removeAttr('disabled');
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
				var grid_data_length = jQuery('.arm_hide_datatable tbody .chkstanard').length;
				var grid_ids = [];
				jQuery('.arm_hide_datatable tbody .chkstanard').each(function(){
					var id = jQuery(this).closest('tr').find('.arm_show_user_transactions').attr('data-id');
					grid_ids.push(id);
				})
				if(grid_ids != '' && typeof grid_ids != 'undefined') {
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
					jQuery.ajax({
						type: "POST",
						url: __ARMAJAXURL,
						data: "action=get_transaction_all_details_for_grid_loads&inv_ids=" + grid_ids + "&exclude_headers="+headers+"&header_label="+headers_label+"&_wpnonce=" + _wpnonce,
						dataType: 'json',
						success: function (response) {
							
							jQuery.each(grid_ids, function(index, uid) {
								var arm_user_d = 'arm_log_id_'+uid;
								var response_data = response[arm_user_d];
								var tr = jQuery('.arm_hide_datatable tbody .chkstanard[value="'+uid+'"]').closest('tr');
								var row = jQuery('#armember_datatable').DataTable().row(tr);
								var class_name = jQuery('.arm_hide_datatable tbody .chkstanard[value="'+uid+'"]').closest('tr').attr('class');
								if (!row.child()) {
									row.child(transaction_grid_format(uid,response_data), class_name +" "+"arm_detail_expand_container").hide();							
									tr.removeClass('shown');
									tr.addClass('hide');
								}
							})
						}
					});
				}
			},
			"aaSorting": [[8, 'desc']],
			"fnStateSave": function (oSettings, oData) {
				oData.aaSorting = [];
				oData.abVisCols = [];
				oData.aoSearchCols = [];
				oData.iStart = 0;
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
			   // oData.oSearch.sSearch = search_term;
			},
		});
		var filter_box = jQuery('#arm_filter_wrapper').html();	
		jQuery('div#armember_datatable_filter').parent().append(filter_box);
		jQuery('div#armember_datatable_filter').hide();	

		if(search_term != ''){
			jQuery('.arm_datatable_searchbox').find('#armmanagesearch_new_transaction').val(search_term);
		}

		if (jQuery.isFunction( jQuery().datetimepicker )) {
			jQuery( '#arm_filter_pstart_date,#arm_filter_pend_date' ).datetimepicker({				
					useCurrent: false,
					format: 'MM/DD/YYYY',
					locale: '',
					maxDate: new Date()
			}).on("dp.change", function (e) {
				field_id = jQuery(this).attr('id');
				val = jQuery(this).val();
				jQuery('#'+field_id+"_hidden").val(val).trigger('change');
            });
		}
	
		if(payment_start_date != '')
		{
			jQuery('.arm_filters_fields').find('#arm_filter_pstart_date_hidden').val(payment_start_date);
			jQuery('#armember_datatable_wrapper').find('#arm_filter_pstart_date').val(payment_start_date);
		}
		if(payment_end_date != '')
		{
			jQuery('.arm_filters_fields').find('#arm_filter_pend_date_hidden').val(payment_end_date);
			jQuery('#armember_datatable_wrapper').find('#arm_filter_pend_date').val(payment_end_date);
		}
	}
	jQuery(document).ready(function(){
		jQuery(document).on('click', '.wrap #armember_datatable_wrapper tr.shown td:not([data-action="selectDay"],.armGridActionTD)', function (e) {
			if( ( jQuery(e.target) ).is( 'input.arm_transaction_bulk_check' ) )
			{
				return;
			}
			var tr = jQuery(this).closest('tr');
			var class_name = jQuery(this).closest('tr').attr('class');
			var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();
			var row = jQuery('#armember_datatable').DataTable().row(tr);	
			row.child.hide();
			tr.removeClass('shown');
			tr.addClass('hide');
		});
		jQuery(document).on('click', '.wrap #armember_datatable_wrapper tr:not(.arm_detail_expand_container,.shown,.arm_filter_child_row) td:not([data-action="selectDay"],.armGridActionTD)', function (e) {
			if( ( jQuery(e.target) ).is( 'input[type="checkbox"]' ) )
			{
				return;
			}
			jQuery('.arm_detail_expand_container').hide();
			jQuery('tr.shown .arm_show_user_transactions').trigger('click');
			var id = jQuery(this).closest('tr').find('.arm_show_user_transactions').attr('data-id');
			if(id != '' && typeof id !='undefined')
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
					if(typeof dataTableHeaderElements[i].dataset.key != 'undefined' && dataTableHeaderElements[i].dataset.key != 'armGridActionTD' && !jQuery(dataTableHeaderElements[i]).is(':visible'))
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
					row.child(user_format(id,headers,headers_label,_wpnonce), class_name +" "+"arm_detail_expand_container").show();
					tr.addClass('shown');
				}
			}
		});
	});
	function transaction_grid_format(d,response_data) {
		var response1 = '<div class="arm_child_row_div_'+d+'">'+response_data+'</div>';
		return response1;
	}
	function user_format(id,headers,headers_label,_wpnonce) {
    // `d` is the original data object for the row
		var arm_child_row_html = "<div class='arm_child_row_div_"+id+"'><div class='arm_child_row_div'><div class='arm_child_user_data_section'><div class='arm_view_member_left_box arm_no_border arm_margin_top_0' style='display: flex;align-items: center;'><img class='arm_load_subscription_plans' src='<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore?>/arm_loader.gif' alt='<?php esc_attr_e('Load More', 'armember-membership'); ?>' style='margin:30px auto;padding: 10px;width:24px; height:24px;display: flex;align-items: center;'></div></div></div></div>";
		setTimeout(function () { 
        jQuery.ajax({
            type: "POST",
            url: __ARMAJAXURL,
            data: "action=get_transaction_all_details_for_grid&trans_id=" + id + "&exclude_headers="+headers+"&header_label="+headers_label+"&_wpnonce=" + _wpnonce,
            dataType: 'html',
            success: function (response) {
                jQuery('.arm_child_row_div_'+id).html(response);
            }
        });
    },200);
    return arm_child_row_html;
		
	}
	function ChangeID(id, type)
	{
		document.getElementById('delete_id').value = id;
		document.getElementById('delete_type').value = type;
	}
	function ChangeStatus(id, status)
	{
		document.getElementById('log_id').value = id;
		document.getElementById('log_status').value = status;
	}
// ]]>
</script>
<div class="arm_transactions_list">
	<div class="arm_filter_wrapper" id="arm_filter_wrapper" style="display:none;">
		<div class="arm_datatable_filters_options arm_transaction_filter_options arm_bulk_action_section hidden_section">
			<span class="arm_reset_bulk_action"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M6.34313 17.6569L12 12M17.6568 6.34315L12 12M12 12L6.34313 6.34315M12 12L17.6568 17.6569" stroke="#617191" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span class="arm_selected_chkcount"></span>&nbsp;&nbsp;<span><?php esc_html_e('of','armember-membership');?></span>&nbsp;&nbsp;<span class="arm_selected_chkcount_total"></span>&nbsp;&nbsp;<span><?php esc_html_e('Selected','armember-membership');?></span><div class="arm_margin_right_10"></div><div class="arm_margin_left_10"></div>
			<div class='sltstandard'>
				<input type='hidden' id='arm_transaction_bulk_action1' name="action1" value="delete_transaction" />
				<dl class="arm_selectbox column_level_dd arm_width_160">
					<dt><span class="arm_no_auto_complete"></span><i class="armfa armfa-caret-down armfa-lg"></i></dt>
					<dd>
						<ul data-id="arm_transaction_bulk_action1">
							<li data-label="<?php esc_attr_e( 'Bulk Actions', 'armember-membership' ); ?>" data-value="-1"><?php esc_html_e( 'Bulk Actions', 'armember-membership' ); ?></li>
							<li data-label="<?php esc_attr_e( 'Delete', 'armember-membership' ); ?>" data-value="delete_transaction"><?php esc_html_e( 'Delete', 'armember-membership' ); ?></li>
						</ul>
					</dd>
				</dl>
			</div>
			<input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php esc_attr_e( 'Go', 'armember-membership' ); ?>"/>
		</div>
		<div class="arm_datatable_filters_options arm_transaction_filter_options arm_filters_fields">
			<div class="sltstandard">
				<div class="arm_confirm_box_btn_container arm_margin_0">
					<div class="arm_dt_filter_block arm_datatable_searchbox">
						<div class="arm_datatable_filter_item">
							<label><input type="text" placeholder="<?php esc_attr_e( 'Search', 'armember-membership' ); ?>" id="armmanagesearch_new_transaction" name="armmanagesearch_new_transaction" value="<?php echo esc_attr($filter_search); ?>" tabindex="0" ></label>
						</div>
					</div>
					<?php if ( ! empty( $payment_gateways ) ) : ?>
						<div class="arm_filter_child_row">
							<div>
								<div class="arm_datatable_filter_item arm_filter_gateway_label">
									<input type="text" id="arm_filter_gateway" class="arm_filter_gateway arm-selectpicker-input-control" value="<?php echo esc_attr($filter_gateway); ?>" />
									<dl class="arm_selectbox arm_width_220">
										<dt>
											<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_7528_8356)"><path d="M10 1.5C5.30576 1.5 1.5 5.30576 1.5 10C1.5 14.6942 5.30576 18.5 10 18.5C14.6942 18.5 18.5 14.6942 18.5 10C18.5 5.30576 14.6942 1.5 10 1.5ZM12.4416 13.351C12.0708 13.8023 11.5881 14.1066 11.0249 14.2605C10.78 14.327 10.6681 14.4564 10.6821 14.7117C10.6926 14.9636 10.6821 15.2119 10.6786 15.4638C10.6786 15.6877 10.5632 15.8066 10.3428 15.8136C10.1994 15.8171 10.056 15.8206 9.91255 15.8206C9.78663 15.8206 9.6607 15.8206 9.53477 15.8171C9.29691 15.8136 9.18498 15.6772 9.18498 15.4463C9.18148 15.2644 9.18148 15.079 9.18148 14.8971C9.17798 14.4914 9.16399 14.4774 8.77572 14.4144C8.27901 14.334 7.7893 14.222 7.33457 14.0016C6.97778 13.8267 6.9393 13.7393 7.04074 13.3615C7.1177 13.0817 7.19465 12.8019 7.2821 12.5255C7.34506 12.3226 7.40453 12.2317 7.51296 12.2317C7.57593 12.2317 7.65638 12.2632 7.76482 12.3191C8.26852 12.5815 8.8037 12.7284 9.36687 12.7984C9.46132 12.8088 9.55576 12.8158 9.65021 12.8158C9.91255 12.8158 10.1679 12.7669 10.4163 12.6584C11.0424 12.3856 11.1403 11.6615 10.6121 11.2278C10.4337 11.0809 10.2274 10.9724 10.014 10.878C9.46482 10.6366 8.89465 10.4547 8.37696 10.1434C7.53745 9.63971 7.00576 8.95062 7.06872 7.92922C7.13868 6.7749 7.7928 6.05432 8.85267 5.66955C9.28992 5.51214 9.29342 5.51564 9.29342 5.06091C9.29342 4.907 9.28992 4.75309 9.29691 4.59568C9.30741 4.25288 9.36338 4.19342 9.70617 4.18292C9.74465 4.18292 9.78663 4.18292 9.8251 4.18292C9.89156 4.18292 9.95802 4.18292 10.0245 4.18292C10.0525 4.18292 10.0805 4.18292 10.1049 4.18292C10.7556 4.18292 10.7556 4.21091 10.7591 4.91399C10.7626 5.43169 10.7626 5.43169 11.2767 5.51214C11.672 5.5751 12.0463 5.69054 12.4101 5.85144C12.6095 5.93889 12.6864 6.07881 12.6235 6.29218C12.5325 6.607 12.4451 6.92531 12.3471 7.23663C12.2842 7.42551 12.2247 7.51296 12.1128 7.51296C12.0498 7.51296 11.9728 7.48848 11.8749 7.43951C11.3712 7.19465 10.843 7.07572 10.2903 7.07572C10.2204 7.07572 10.1469 7.07922 10.077 7.08272C9.91255 7.09321 9.75165 7.1142 9.59774 7.18066C9.05206 7.41852 8.96461 8.02016 9.42984 8.39095C9.6642 8.57984 9.93354 8.71276 10.2099 8.82819C10.6926 9.02757 11.1753 9.21996 11.6335 9.47181C13.0747 10.2763 13.4665 12.1058 12.4416 13.351Z" fill="#9CA7BD"/></g><defs><clipPath id="clip0_7528_8356"><rect width="17" height="17" fill="white" transform="translate(1.5 1.5)"/></clipPath></defs></svg>
											<span class="arm_gateway_filter_value arm_no_auto_complete"></span><i class="armfa armfa-caret-down armfa-lg"></i>
										</dt>
										<dd>
											<ul data-id="arm_filter_gateway">
												<li data-label="<?php esc_attr_e( 'Gateway', 'armember-membership' ); ?>" data-value="0"><?php esc_html_e( 'Gateway', 'armember-membership' ); ?></li>
												<li data-label="<?php esc_attr_e( 'Manual', 'armember-membership' ); ?>" data-value="<?php esc_attr_e( 'manual', 'armember-membership' ); ?>"><?php esc_html_e( 'Manual', 'armember-membership' ); ?></li>
												<?php foreach ( $payment_gateways as $key => $pg ) : ?>
													<li data-label="<?php echo esc_attr($pg['gateway_name']); ?>" data-value="<?php echo esc_attr($key); ?>"><?php echo esc_attr($pg['gateway_name']); ?></li>                                    
												<?php endforeach; ?>
											</ul>
										</dd>
									</dl>
								</div>
							</div>
						</div>
					<?php endif; ?>
					<div class="arm_filter_child_row">
						<div>
							<div class="arm_datatable_filter_item arm_filter_ptype_label">
								<input type="text" id="arm_filter_ptype" class="arm_filter_ptype arm-selectpicker-input-control" value="<?php echo esc_html($filter_ptype); ?>" />
								<dl class="arm_selectbox arm_width_220 arm_min_width_60">
									<dt>
										<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.6666 13.333L17.7778 14.4441L18.8888 13.333" stroke="#9CA7BD" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M4.44446 4.35358C5.85986 3.03145 7.76024 2.22266 9.84672 2.22266C14.224 2.22266 17.7778 5.7824 17.7778 10.1671V14.4449" stroke="#9CA7BD" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M3.33331 6.66678L2.22218 5.55566L1.11108 6.66678" stroke="#9CA7BD" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M15.5555 15.647C14.1401 16.9691 12.2397 17.7779 10.1532 17.7779C5.77592 17.7779 2.22217 14.2181 2.22217 9.83342V5.55566" stroke="#9CA7BD" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 5C7.23868 5 5 7.23868 5 10C5 12.7613 7.23868 15 10 15C12.7613 15 15 12.7613 15 10C15 7.23868 12.7613 5 10 5ZM11.4362 11.9712C11.2181 12.2366 10.9342 12.4156 10.6029 12.5062C10.4588 12.5453 10.393 12.6214 10.4012 12.7716C10.4074 12.9198 10.4012 13.0658 10.3992 13.214C10.3992 13.3457 10.3313 13.4156 10.2016 13.4198C10.1173 13.4218 10.0329 13.4239 9.94856 13.4239C9.87449 13.4239 9.80041 13.4239 9.72634 13.4218C9.58642 13.4198 9.52058 13.3395 9.52058 13.2037C9.51852 13.0967 9.51852 12.9877 9.51852 12.8807C9.51646 12.642 9.50823 12.6337 9.27984 12.5967C8.98765 12.5494 8.69959 12.4835 8.4321 12.3539C8.22222 12.251 8.19959 12.1996 8.25926 11.9774C8.30453 11.8128 8.34979 11.6481 8.40123 11.4856C8.43827 11.3663 8.47325 11.3128 8.53704 11.3128C8.57407 11.3128 8.6214 11.3313 8.68519 11.3642C8.98148 11.5185 9.2963 11.6049 9.62757 11.6461C9.68313 11.6523 9.73868 11.6564 9.79424 11.6564C9.94856 11.6564 10.0988 11.6276 10.2449 11.5638C10.6132 11.4033 10.6708 10.9774 10.3601 10.7222C10.2551 10.6358 10.1337 10.572 10.0082 10.5165C9.68519 10.3745 9.34979 10.2675 9.04527 10.0844C8.55144 9.78807 8.23868 9.38272 8.27572 8.78189C8.31687 8.10288 8.70165 7.67901 9.3251 7.45267C9.5823 7.36008 9.58436 7.36214 9.58436 7.09465C9.58436 7.00412 9.5823 6.91358 9.58642 6.82099C9.59259 6.61934 9.62551 6.58436 9.82716 6.57819C9.84979 6.57819 9.87449 6.57819 9.89712 6.57819C9.93621 6.57819 9.97531 6.57819 10.0144 6.57819C10.0309 6.57819 10.0473 6.57819 10.0617 6.57819C10.4444 6.57819 10.4444 6.59465 10.4465 7.00823C10.4486 7.31276 10.4486 7.31276 10.751 7.36008C10.9835 7.39712 11.2037 7.46502 11.4177 7.55967C11.535 7.61111 11.5802 7.69342 11.5432 7.81893C11.4897 8.00412 11.4383 8.19136 11.3807 8.37449C11.3436 8.4856 11.3086 8.53704 11.2428 8.53704C11.2058 8.53704 11.1605 8.52263 11.1029 8.49383C10.8066 8.34979 10.4959 8.27984 10.1708 8.27984C10.1296 8.27984 10.0864 8.28189 10.0453 8.28395C9.94856 8.29012 9.85391 8.30247 9.76337 8.34156C9.44239 8.48148 9.39095 8.83539 9.66461 9.0535C9.80247 9.16461 9.96091 9.2428 10.1235 9.3107C10.4074 9.42798 10.6914 9.54115 10.9609 9.6893C11.8086 10.1626 12.0391 11.2387 11.4362 11.9712Z" fill="#9CA7BD"/></svg>
										<span class="arm_ptype_filter_value arm_no_auto_complete"></span><i class="armfa armfa-caret-down armfa-lg"></i>
									</dt>
									<dd>
										<ul data-id="arm_filter_ptype">
											<li data-label="<?php esc_attr_e( 'Payment Type', 'armember-membership' ); ?>" data-value="0"><?php esc_html_e( 'Payment Type', 'armember-membership' ); ?></li>
											<li data-label="<?php esc_attr_e( 'One Time', 'armember-membership' ); ?>" data-value="one_time"><?php esc_html_e( 'One Time', 'armember-membership' ); ?></li>
											<li data-label="<?php esc_attr_e( 'Recurring', 'armember-membership' ); ?>" data-value="subscription"><?php esc_html_e( 'Recurring', 'armember-membership' ); ?></li>
										</ul>
									</dd>
								</dl>
							</div>
						</div>
					</div>
					<div class="arm_filter_child_row">
						<div>
							<div class="arm_datatable_filter_item arm_filter_pmode_label">
								<input type="text" id="arm_filter_pmode" class="arm_filter_pmode arm-selectpicker-input-control" value="<?php echo esc_html($filter_pmode); ?>" />
								<dl class="arm_selectbox arm_width_220 arm_min_width_80">
									<dt>
										<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.6041 3H8.39587C5.3711 3 3.85872 3 2.91904 4.11578C2.24247 4.91915 2.05304 6.07507 2 8H18C17.947 6.07507 17.7575 4.91915 17.0809 4.11578C16.1413 3 14.6289 3 11.6041 3Z" fill="#9CA7BD"/><path fill-rule="evenodd" clip-rule="evenodd" d="M8.4 17H11.6C14.617 17 16.1255 17 17.0627 15.9867C18 14.9735 18 13.3418 18 10.0802C18 9.69836 18 9.33877 17.9985 9H2.00151C2 9.33903 2 9.6989 2 10.0811C2 13.3427 2 14.9735 2.93726 15.9867C3.87452 17 5.38301 17 8.4 17ZM12.4 12.8919C12.0686 12.8919 11.8 13.1823 11.8 13.5405C11.8 13.8988 12.0686 14.1892 12.4 14.1892H13.6C13.9314 14.1892 14.2 13.8988 14.2 13.5405C14.2 13.1823 13.9314 12.8919 13.6 12.8919H12.4ZM4.6 13.5405C4.6 13.1823 4.86863 12.8919 5.2 12.8919H8.4C8.73136 12.8919 9 13.1823 9 13.5405C9 13.8988 8.73136 14.1892 8.4 14.1892H5.2C4.86863 14.1892 4.6 13.8988 4.6 13.5405Z" fill="#9CA7BD"/></svg>
										<span class="arm_pmode_filter_value arm_no_auto_complete"></span><i class="armfa armfa-caret-down armfa-lg"></i>
									</dt>
									<dd>
										<ul data-id="arm_filter_pmode">
											<li data-label="<?php esc_attr_e( 'Subscription', 'armember-membership' ); ?>" data-value="0"><?php esc_html_e( 'Subscription', 'armember-membership' ); ?></li>
											<li data-label="<?php esc_attr_e( 'Automatic Subscription', 'armember-membership' ); ?>" data-value="auto_debit_subscription"><?php esc_html_e( 'Automatic Subscription', 'armember-membership' ); ?></li>
											<li data-label="<?php esc_attr_e( 'Semi Automatic Subscription', 'armember-membership' ); ?>" data-value="manual_subscription"><?php esc_html_e( 'Semi Automatic Subscription', 'armember-membership' ); ?></li>
										</ul>
									</dd>
								</dl>
							</div>
						</div>
					</div>
					<div class="arm_filter_child_row">						
						<div>
							<div class="arm_datatable_filter_item arm_filter_pstatus_label">
								<input type="text" id="arm_filter_pstatus" class="arm_filter_pstatus arm-selectpicker-input-control" value="<?php echo esc_html($filter_pstatus); ?>" />
								<dl class="arm_selectbox arm_min_width_60 arm_width_220">
									<dt>
										<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_7540_15552)"><path fill-rule="evenodd" clip-rule="evenodd" d="M16.0731 6.26028C16.5917 6.04852 17.1839 6.2973 17.3956 6.81595C19.0896 10.9651 17.0993 15.702 12.9502 17.396C10.5553 18.3738 7.96399 18.1232 5.87826 16.9415C5.39086 16.6653 5.21962 16.0462 5.49579 15.5588C5.77195 15.0714 6.39094 14.9002 6.87835 15.1764C8.44678 16.0651 10.388 16.2508 12.1834 15.5178C15.2952 14.2474 16.7879 10.6947 15.5174 7.58279C15.3056 7.06414 15.5544 6.47202 16.0731 6.26028ZM3.99514 11.4272C4.06205 11.6808 4.14623 11.9334 4.24841 12.1838C4.35059 12.434 4.46729 12.6734 4.59703 12.9014C4.8741 13.3883 4.70398 14.0076 4.21709 14.2847C3.73019 14.5617 3.11087 14.3916 2.83382 13.9047C2.6608 13.6006 2.50567 13.2823 2.37024 12.9505C2.2348 12.6188 2.12279 12.2829 2.03356 11.9446C1.89067 11.4029 2.21395 10.848 2.75562 10.7051C3.2973 10.5622 3.85225 10.8855 3.99514 11.4272ZM4.26263 5.42539C4.74656 5.70761 4.91007 6.3287 4.62786 6.81262C4.35976 7.27235 4.15248 7.76565 4.01177 8.2789C3.86366 8.81918 3.30561 9.13709 2.76534 8.98896C2.22507 8.84083 1.90717 8.28278 2.05529 7.74251C2.24262 7.05921 2.51848 6.40265 2.87541 5.79061C3.15763 5.30669 3.77871 5.14316 4.26263 5.42539ZM12.0236 2.05553C12.707 2.24287 13.3635 2.51873 13.9755 2.87567C14.4595 3.15788 14.623 3.77896 14.3408 4.26289C14.0586 4.74683 13.4375 4.91034 12.9536 4.62813C12.4939 4.36002 12.0005 4.15275 11.4873 4.01203C10.947 3.86392 10.6291 3.30586 10.7772 2.76558C10.9253 2.22531 11.4834 1.90741 12.0236 2.05553ZM9.06111 2.75587C9.204 3.29755 8.88072 3.8525 8.33904 3.9954C8.08538 4.06231 7.83274 4.14649 7.58247 4.24867C7.33219 4.35085 7.09283 4.46756 6.86482 4.5973C6.37793 4.87436 5.75861 4.70424 5.48155 4.21735C5.2045 3.73045 5.37461 3.11113 5.86151 2.83407C6.16558 2.66105 6.48393 2.50592 6.81565 2.37048C7.14736 2.23505 7.48332 2.12303 7.82159 2.0338C8.36327 1.89091 8.91822 2.21419 9.06111 2.75587Z" fill="#9CA7BD"/><circle cx="10" cy="10" r="3" fill="#9CA7BD"/></g><defs><clipPath id="clip0_7540_15552"><rect width="16" height="16" fill="white" transform="translate(2 2)"/></clipPath></defs></svg>
										<span class="arm_pstatus_filter_value arm_no_auto_complete"></span><i class="armfa armfa-caret-down armfa-lg"></i>
									</dt>
									<dd>
										<ul data-id="arm_filter_pstatus">
											<li data-label="<?php esc_attr_e( 'Status', 'armember-membership' ); ?>" data-value="0"><?php esc_html_e( 'Status', 'armember-membership' ); ?></li>
											<li data-label="<?php esc_attr_e( 'Success', 'armember-membership' ); ?>" data-value="success"><?php esc_html_e( 'Success', 'armember-membership' ); ?></li>
											<li data-label="<?php esc_attr_e( 'Pending', 'armember-membership' ); ?>" data-value="pending"><?php esc_html_e( 'Pending', 'armember-membership' ); ?></li>
											<li data-label="<?php esc_attr_e( 'Cancelled', 'armember-membership' ); ?>" data-value="canceled"><?php esc_html_e( 'Cancelled', 'armember-membership' ); ?></li>
											<li data-label="<?php esc_attr_e( 'Failed', 'armember-membership' ); ?>" data-value="failed"><?php esc_html_e( 'Failed', 'armember-membership' ); ?></li>
											<li data-label="<?php esc_attr_e( 'Expired', 'armember-membership' ); ?>" data-value="expired"><?php esc_html_e( 'Expired', 'armember-membership' ); ?></li>
										</ul>
									</dd>
								</dl>
							</div>
						</div>
					</div>
					<div class="arm_filter_child_row">						
						<div>
							<div class="arm_datatable_filter_item arm_filter_pstart_date arm_margin_left_0" >
								<input type="text" id="arm_filter_pstart_date" class="arm_min_width_60 arm_width_220" placeholder="<?php esc_attr_e( 'Start Date', 'armember-membership' ); ?>" data-date_format="m/d/Y" value=""/>
								<input type="hidden" id="arm_filter_pstart_date_hidden" name="arm_filter_pstart_date">
							</div>
						</div>
					</div>
					<div class="arm_filter_child_row">
						<div>
							<div class="arm_datatable_filter_item arm_filter_pend_date">
								<input type="text" id="arm_filter_pend_date" class="arm_min_width_60 arm_width_220" placeholder="<?php esc_attr_e( 'End Date', 'armember-membership' ); ?>" data-date_format="m/d/Y" value=""/>
								<input type="hidden" id="arm_filter_pend_date_hidden" name="arm_filter_pend_date">
							</div>
						</div>
					</div>
					<div class="arm_filter_child_row">
						<div>
							<input type="button" class="armemailaddbtn" id="arm_transaction_grid_filter_btn" value="<?php esc_html_e('Apply','armember-membership');?>">
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
	<form method="GET" id="transactions_list_form" class="data_grid_list" onsubmit="return arm_transactions_list_form_bulk_action();">
		<input type="hidden" name="page" value="<?php echo esc_attr($arm_slugs->transactions); //phpcs:ignore ?>" />
		<input type="button" id="arm_payment_grid_export_btn" class="hidden_section"/>
		<input type="hidden" name="armaction" value="list" />
		<div id="armmainformnewlist" class="arm_filter_grid_list_container">
			<div class="arm_loading_grid" style="display: none;"><?php $arm_loader = $arm_common_lite->arm_loader_img_func();
			echo $arm_loader; //phpcs:ignore ?></div>
			<div class="response_messages"></div>
			<table cellpadding="0" cellspacing="0" border="0" class="display arm_hide_datatable" id="armember_datatable">
				<thead>
					<tr>
						<th class="center"></th>
						<th class="center cb-select-all-th" ><input id="cb-select-all-1" type="checkbox" class="chkstanard"></th>
						<th data-key="transaction_id"><?php esc_html_e( 'Transaction ID', 'armember-membership' ); ?></th>
						<?php if($ARMemberLite->is_arm_pro_active){ ?> <th data-key="invoice_id"><?php esc_html_e('Invoice ID', 'armember-membership'); ?></th> <?php } ?>
						<th data-key="username"><?php esc_html_e( 'Username', 'armember-membership' ); ?></th>					
						<th class="arm_membership" data-key="arm_plan_id"><?php esc_html_e( 'Membership', 'armember-membership' ); ?></th>
						<th class="arm_membership_gatway"  data-key="arm_payment_gateway"><?php esc_html_e( 'Payment Gateway', 'armember-membership' ); ?></th>
						<th class="arm_payment_type" data-key="arm_payment_type"><?php esc_html_e( 'Payment Type', 'armember-membership' ); ?></th>
						<th class="arm_payment_status" data-key="arm_transaction_status"><?php esc_html_e( 'Transaction Status', 'armember-membership' ); ?></th>
						<th class="arm_payment_date" data-key="arm_created_date"><?php esc_html_e( 'Payment Date', 'armember-membership' ); ?></th>
						<th data-key="arm_amount"><?php esc_html_e( 'Amount', 'armember-membership' ); ?></th>
						<th data-key="armGridActionTD" class="armGridActionTD" style="display: none;"></th>
					</tr>
				</thead>
			</table>
			<div class="armclear"></div>
			<input type="hidden" name="show_hide_columns" id="show_hide_columns" value="<?php esc_attr_e( 'Show / Hide columns', 'armember-membership' ); ?>"/>
			<input type="hidden" name="search_grid" id="search_grid" value="<?php esc_attr_e( 'Search', 'armember-membership' ); ?>"/>
			<input type="hidden" name="entries_grid" id="entries_grid" value="<?php esc_attr_e( 'transactions', 'armember-membership' ); ?>"/>
			<input type="hidden" name="show_grid" id="show_grid" value="<?php esc_attr_e( 'Show', 'armember-membership' ); ?>"/>
			<input type="hidden" name="showing_grid" id="showing_grid" value="<?php esc_attr_e( 'Showing', 'armember-membership' ); ?>"/>
			<input type="hidden" name="to_grid" id="to_grid" value="<?php esc_attr_e( 'to', 'armember-membership' ); ?>"/>
			<input type="hidden" name="of_grid" id="of_grid" value="<?php esc_attr_e( 'of', 'armember-membership' ); ?>"/>
			<input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php esc_attr_e( 'No matching transactions found', 'armember-membership' ); ?>"/>
			<input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php esc_attr_e( 'No any transaction found yet.', 'armember-membership' ); ?>"/>
			<input type="hidden" name="filter_grid" id="filter_grid" value="<?php esc_attr_e( 'filtered from', 'armember-membership' ); ?>"/>
			<input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php esc_attr_e( 'total', 'armember-membership' ); ?>"/>
			<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
			<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
		</div>
		<div class="footer_grid"></div>
	</form>
</div>

<?php require_once(MEMBERSHIPLITE_VIEWS_DIR.'/arm_view_member_details.php')?>
