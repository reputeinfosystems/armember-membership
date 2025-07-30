<?php

if (!class_exists('ARM_subsctriptions_Lite')) {

    class ARM_subsctriptions_Lite{
        
        function __construct(){
            global $wpdb, $ARMemberLite, $arm_slugs;

            add_action('wp_ajax_get_activity_data',array($this, 'arm_fetch_activity_data'));
            add_action('wp_ajax_get_subscription_data',array($this, 'arm_fetch_subscription_data'));
            add_action('wp_ajax_get_upcoming_subscription_data',array($this, 'arm_fetch_upcoming_subscription_data'));
            add_action('wp_ajax_transaction_activity_ajax_action',array($this, 'arm_delete_transaction_data'));
            add_action('wp_ajax_arm_change_bank_transfer_status', array($this, 'arm_change_bank_transfer_status'));
            add_action('wp_ajax_arm_invoice_detail', array($this, 'arm_invoice_detail'));
            add_action('wp_ajax_arm_cancel_subscription_ajax_action',array($this, 'arm_cancel_subscription_data'));
            add_action('wp_ajax_arm_add_new_subscriptions',array($this,'arm_add_new_subscriptions'));
            add_action('wp_ajax_get_user_all_transaction_details_for_grid',array($this,'get_user_all_transaction_details_for_grid'));      
            add_action('wp_ajax_arm_activation_subscription_plan',array($this,'arm_activation_subscription_plan'),10,2);     
        }
        function arm_activation_subscription_plan()
        {
            global $wp,$wpdb,$ARMemberLite,$arm_global_settings, $arm_subscription_plans, $arm_payment_gateways, $global_currency_sym ,$arm_capabilities_global,$arm_members_class;

            $response = array('type'=>'error','msg'=>esc_html__('Something went wrong','armember-membership'));
            $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_subscriptions'], '1'); //phpcs:ignore --Reason:Verifying nonce

            $activity_id = $_POST['arm_activity'];//phpcs:ignore
            $sql_act = $wpdb->prepare('SELECT `arm_user_id`,`arm_item_id` FROM '.$ARMemberLite->tbl_arm_activity.' WHERE arm_activity_id=%d',$activity_id); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_activity is a table name
            
            $get_result_sql = $wpdb->get_row($sql_act,ARRAY_A); //phpcs:ignore --Reason $sql_act is a query name
            $user_id = $get_result_sql['arm_user_id'];
            $plan_id = $get_result_sql['arm_item_id'];

            $post_data=array('arm_action'=>'status','user_id'=>$user_id,'plan_id'=>$plan_id);
            
            $user    = get_userdata( $user_id );
            $plan_id = intval( $plan_id );

            $user_suspended_plans = get_user_meta( $user_id, 'arm_user_suspended_plan_ids', true );
            $user_suspended_plans = ! empty( $user_suspended_plans ) ? $user_suspended_plans : array();

            if ( ! empty( $user_suspended_plans ) ) {
                if ( in_array( $plan_id, $user_suspended_plans ) ) {
                    unset( $user_suspended_plans[ array_search( $plan_id, $user_suspended_plans ) ] );
                    update_user_meta( $user_id, 'arm_user_suspended_plan_ids', array_values( $user_suspended_plans ) );
                    $is_activated['type'] = 'success';
                }
            }

            if($is_activated['type'] == 'success')
            {
                $response   = array(
                    'type'    => 'success',
                    'msg'     => esc_html__( 'Plan activated successfully.', 'armember-membership' ),
                );
            }
            else
            {
                $response = array('type'=>'error','msg'=>esc_html__( 'Plan activation failed.', 'armember-membership' ));
            }
            echo wp_json_encode($response);
            die;
        }
        function get_user_all_transaction_details_for_grid(){
            global $wp,$wpdb,$ARMemberLite,$arm_global_settings, $arm_subscription_plans, $arm_payment_gateways, $arm_transaction,$global_currency_sym ,$arm_capabilities_global;

            $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_subscriptions'], '1'); //phpcs:ignore --Reason:Verifying nonce

            $arm_invoice_tax_feature = get_option('arm_is_invoice_tax_feature', 0);

            $arm_activity_id = intval( $_POST['activity_id'] );//phpcs:ignore

            $get_result_sql = $wpdb->prepare('SELECT act.arm_activity_id,act.arm_user_id,am.arm_user_login,act.arm_content,act.arm_item_id,act.arm_date_recorded FROM '.$ARMemberLite->tbl_arm_activity.' act LEFT JOIN '.$ARMemberLite->tbl_arm_members.' am ON act.arm_user_id = am.arm_user_id WHERE act.arm_activity_id =%d',$arm_activity_id); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_activity is a table name
            $response_result = $wpdb->get_row($get_result_sql); //phpcs:ignore --Reason $get_result_sql is a sql query
            
            $return='';
            
            if(!empty($response_result))
            {
                $rc = (object) $response_result;
               
                $get_activity_data = maybe_unserialize($rc->arm_content);
                $grace_period_data = $plan_detail = $membership_start = '';
                $user_id = $rc->arm_user_id;
                $date_format = $arm_global_settings->arm_get_wp_date_format();
                $user_plan_detail = get_user_meta($user_id, 'arm_user_plan_'.$rc->arm_item_id, true);
                $start_plan_date = !empty($rc->arm_activity_plan_start_date) ? $rc->arm_activity_plan_start_date : current_time( 'mysql' );
                $plan_status = $this->get_return_status_data($user_id,$rc->arm_item_id,$user_plan_detail,strtotime($start_plan_date));
                $canceled_date = !empty($plan_status['canceled_date']) ? $plan_status['canceled_date'] : '';
                $transaction_started_date = date('Y-m-d 00:00:00', strtotime($start_plan_date)); //phpcs:ignore
                
                if($get_activity_data['gateway'] !='manual')
                {
                    $transaction_started_date = date('Y-m-d H:i:s', ( $start_plan_date - 120)); //phpcs:ignore
                }
                if(!empty($canceled_date))
                {
                    $get_last_transaction_sql = "SELECT * FROM ".$ARMemberLite->tbl_arm_payment_log." WHERE arm_user_id=".$user_id." AND arm_plan_id=".$rc->arm_item_id." AND arm_created_date BETWEEN '".$transaction_started_date."' AND '".$canceled_date."'  ORDER BY arm_log_id DESC";
                }
                else
                {
                    if(!empty($user_plan_detail['arm_trial_start']))
                    {
                        $transaction_started_date = date('Y-m-d H:i:s', ( $user_plan_detail['arm_trial_start'] - 120)); //phpcs:ignore
                    }
                    $get_last_transaction_sql = $wpdb->prepare("SELECT * FROM ".$ARMemberLite->tbl_arm_payment_log." WHERE arm_user_id=%d AND arm_plan_id=%d AND arm_created_date >= %s ORDER BY arm_log_id DESC",$user_id,$rc->arm_item_id,$transaction_started_date); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name
                }
                $return .= '<div class="arm_child_row_div"><table class="arm_user_child__transaction_table " cellspacing="1" >';
                $return .= '<tr class="arm_child_transaction_row">';
                $return .= '<th>' . esc_html__('Transaction ID', 'armember-membership') . '</th>';
                $return .= '<th>' . esc_html__('Subscription ID', 'armember-membership') . '</th>';
                $return .= '<th>' . esc_html__('Payment Gateway', 'armember-membership') . '</th>';
                $return .= '<th class="dt-right">' . esc_html__('Amount', 'armember-membership') . '</th>';
                $return .= '<th class="dt-right arm_padding_right_20">' . esc_html__('Status', 'armember-membership') . '</th>';
                $return .= '<th class="dt-right arm_padding_right_20">' . esc_html__('Transaction Date', 'armember-membership') . '</th>';
                $return .= '</tr>';
                $response_transaction_result = $wpdb->get_results($get_last_transaction_sql); //phpcs:ignore --Reason $get_last_transaction_sql is a predefined query
                foreach($response_transaction_result as $transactions)
                {
                    $transactionID = !empty($transactions->arm_transaction_id) ? $transactions->arm_transaction_id : 'manual';
                    $subscription_id = !empty($transactions->arm_token) ? $transactions->arm_token : '-';
                    $arm_transaction_status = $transactions->arm_transaction_status;
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
                            $arm_transaction_status = !empty($rc->arm_transaction_status) ? $rc->arm_transaction_status : 'success';
                            break;
                    }
                    $return .= '<tr class="arm_child_transaction_row">';
                    $return .= '<td>' . $transactionID . '</td>';
                    $return .= '<td>' . $subscription_id . '</td>';
                    $return .= '<td>' . $arm_payment_gateways->arm_gateway_name_by_key($transactions->arm_payment_gateway) . '</td>';
                    $return .= '<td class="dt-right">' . number_format(floatval($transactions->arm_amount),2,'.',',') .' '. $transactions->arm_currency . '</td>';
                    $return .= '<td class="dt-right">' . $arm_transaction->arm_get_transaction_status_text($arm_transaction_status) . '</td>';
                    $return .= '<td class="dt-right arm_padding_right_20">' . date_i18n($date_format, strtotime($transactions->arm_payment_date)) . '</td>';
                    $return .= '</tr>';
                }
                $return .= '</table></div>';
            }
            echo $return; //phpcs:ignore
            die;
        }
        function arm_add_new_subscriptions()
        {
            global $wp, $wpdb, $current_user, $arm_errors, $ARMemberLite, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways, $global_currency_sym,$arm_capabilities_global,$arm_members_class;

            $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_subscriptions'], '1'); //phpcs:ignore --Reason:Verifying nonce
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
            $post_data = array();
            $posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend'), $_POST ); //phpcs:ignore
            if(!empty($posted_data))
            {
                $post_data['arm_action'] = 'add';
                $post_data['user_id'] = isset($posted_data['arm_user_id_hidden']) ? intval($posted_data['arm_user_id_hidden']) : 0;
                $post_data['arm_user_plan'] = isset($posted_data['membership_plan']) ? intval($posted_data['membership_plan']) : 0;
		$post_data['arm_selected_payment_cycle'] = isset($posted_data['arm_selected_payment_cycle']) ? intval($posted_data['arm_selected_payment_cycle']) : 0;
                $membership_type = isset($posted_data['plan_type']) ? intval($posted_data['plan_type']) : 0;
                $post_data['arm_subscription_start_date'] = date_i18n($date_format, strtotime(current_time('mysql')));
                $post_data['user_id'] = isset($posted_data['arm_user_id_hidden']) ? intval($posted_data['arm_user_id_hidden']) : 0;
                $old_plan_ids = get_user_meta($posted_data['arm_user_id_hidden'], 'arm_user_plan_ids', true);
                $old_plan_ids = !empty($old_plan_ids) ? $old_plan_ids : array();
                if(!in_array($post_data['arm_user_plan'],$old_plan_ids))
                {
                    $response = $this->add_plan_action($post_data);
                }
                else
                {
                    $response = array('type' => 'error', 'msg' => esc_html__("Membership plan is already exist for selected member.", 'armember-membership'));
                }
                echo wp_json_encode($response);
                die;
            }
            
        }
        function add_plan_action($post_data=array()) {
            global $wpdb, $ARMemberLite, $arm_member_forms, $arm_manage_communication, $is_multiple_membership_feature, $arm_subscription_plans, $arm_members_class, $arm_global_settings, $arm_capabilities_global, $arm_pay_per_post_feature, $arm_subscription_cancel_msg;
            
            $response = array('type' => 'error', 'msg' => esc_html__("Sorry, Something went wrong. Please try again.", 'armember-membership'));

            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
            if ($post_data['arm_action'] == 'add') {
                $user_ID = !empty($post_data['user_id']) ? intval($post_data['user_id']) : 0;

                do_action('arm_modify_content_on_plan_change', $post_data, $user_ID);

                if (!empty($user_ID)) {
                    if (!isset($post_data['arm_user_plan'])) {
                        $post_data['arm_user_plan'] = 0;
                    } else {
                        if (is_array($post_data['arm_user_plan'])) {
                            foreach ($post_data['arm_user_plan'] as $key => $mpid) {
                                if (empty($mpid)) {
                                    unset($post_data['arm_user_plan'][$key]);
                                } else {
                                    $post_data['arm_subscription_start_' . $mpid] = isset($post_data['arm_subscription_start_date'][$key]) ? $post_data['arm_subscription_start_date'][$key] : '';
                                }
                            }
                            unset($post_data['arm_subscription_start_date']);
                            $post_data['arm_user_plan'] = array_values($post_data['arm_user_plan']);
                        }
                    }
                    unset($post_data['arm_action']);
                    $post_data['action'] = 'update_member';

                    $old_plan_ids = get_user_meta($user_ID, 'arm_user_plan_ids', true);
                    $old_plan_ids = !empty($old_plan_ids) ? $old_plan_ids : array();
                    $old_plan_id = isset($old_plan_ids[0]) ? $old_plan_ids[0] : 0;
                    if (!empty($old_plan_ids)) {
                        foreach ($old_plan_ids as $plan_id) {
                            $field_name = "arm_subscription_expiry_date_" . $plan_id . "_" . $user_ID;
                            if (isset($post_data[$field_name])) {
                                unset($post_data[$field_name]);
                            }
                        }
                    }
                    unset($post_data['user_id']);

                    $arm_old_suscribed_plans = "";

                    $admin_save_flag = 1;
                    do_action('arm_member_update_meta', $user_ID, $post_data, $admin_save_flag);

                    if (isset($post_data['arm_user_plan']) && !empty($post_data['arm_user_plan'])) {

                        do_action('arm_after_user_plan_change_by_admin', $user_ID, $post_data['arm_user_plan']);
                    }
                    
                    $popup_plan_content = "";
                    
                    $response = array('type' => 'success', 'msg' => esc_html__("Plan added successfully.", 'armember-membership'), 'content' => $popup_plan_content);

                    $response = apply_filters('arm_modify_admin_plan_add_response', $response, $user_ID, $popup_plan_content, $post_data);
                }
            }

            if (isset($response['type']) && $response['type'] == 'success' && $user_ID > 0) 
            {
                $userPlanIDs = get_user_meta($user_ID, 'arm_user_plan_ids', true);

        		if(!empty($userPlanIDs))
        		{
        			$userPostIDs = get_user_meta($user_ID, 'arm_user_post_ids', true);
                    foreach($userPlanIDs as $arm_plan_key => $arm_plan_val)
                    {
                        if(isset($userPostIDs[$arm_plan_val]) && in_array($userPostIDs[$arm_plan_val], $userPostIDs))
                        {
                            unset($userPlanIDs[$arm_plan_key]);
                        }
                    }
                    $userPlanIDs = apply_filters('arm_modify_plan_ids_externally',$userPlanIDs,$user_ID);
        		}
                $arm_all_user_plans = $userPlanIDs;
                $arm_future_user_plans = get_user_meta($user_ID, 'arm_user_future_plan_ids', true);
                
                if (!empty($arm_future_user_plans)) {
                    $arm_all_user_plans = array_merge($userPlanIDs, $arm_future_user_plans);
                }
                $arm_user_plans = '';
                $plan_names = array();
                $subscription_effective_from = array();
                if (!empty($arm_all_user_plans) && is_array($arm_all_user_plans)) {
                    foreach ($arm_all_user_plans as $userPlanID) {
                        $plan_data = get_user_meta($user_ID, 'arm_user_plan_' . $userPlanID, true);

                        $userPlanDatameta = !empty($plan_data) ? $plan_data : array();
                        $plan_data = shortcode_atts($defaultPlanData, $userPlanDatameta);
                        $subscription_effective_from_date = $plan_data['arm_subscr_effective'];
                        $change_plan_to = $plan_data['arm_change_plan_to'];

                        $plan_names[$userPlanID] = $arm_subscription_plans->arm_get_plan_name_by_id($userPlanID);
                        $subscription_effective_from[] = array('arm_subscr_effective' => $subscription_effective_from_date, 'arm_change_plan_to' => $change_plan_to);
                    }
                }
                   
                $auser = new WP_User($user_ID);
                $u_role = array_shift($auser->roles);
                $user_roles = get_editable_roles();
                if (!empty($user_roles[$u_role]['name'])) {
                    $arm_user_role = $user_roles[$u_role]['name'];
                } else {
                    $arm_user_role = '-';
                }
                $response['user_role'] = $arm_user_role;

                $memberTypeText = $arm_members_class->arm_get_member_type_text($user_ID);
                $response['membership_type'] = $memberTypeText;

                $plan_name = (!empty($plan_names)) ? implode(',', $plan_names) : '-';
                $response['membership_plan'] = '<span class="arm_user_plan_' . esc_attr($user_ID) . '">' . esc_html(stripslashes_deep($plan_name)) . '</span>';

                if (!empty($subscription_effective_from)) {
                    foreach ($subscription_effective_from as $subscription_effective) {
                        $subscr_effective = $subscription_effective['arm_subscr_effective'];
                        $change_plan = $subscription_effective['arm_change_plan_to'];
                        $change_plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($change_plan);
                        if (!empty($change_plan) && $subscr_effective > strtotime($nowDate)) {
                            $response['membership_plan'] .= '<div>' . esc_html($change_plan_name) . '<br/> (' . esc_html__('Effective from', 'armember-membership') . ' ' . esc_html(date_i18n($date_format, $subscr_effective)) . ')</div>';
                        }
                    }
                }
            }
            return $response;
            exit;
        }
        function arm_invoice_detail()
		{
			global $wp, $wpdb, $current_user, $arm_errors, $ARMemberLite, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways, $global_currency_sym;
			
			$log_id = intval($_POST['log_id']);//phpcs:ignore
			$log_type = sanitize_text_field($_POST['log_type']);//phpcs:ignore

            $ARMemberLite->arm_check_user_cap('',0); //phpcs:ignore --Reason:Verifying nonce
			/* Get Edit Rule Form HTML */
			if (!empty($log_id) && $log_id != 0) {
			?>
				<script type="text/javascript">
					jQuery('#arm_invoice_iframe').on('load', function() {
						var iframeDoc = document.getElementById('arm_invoice_iframe');
					});
					function arm_print_invoice() {
						var iframeDoc = document.getElementById('arm_invoice_iframe');
						iframeDoc.contentWindow.arm_print_invoice_content();
					}
				</script>
				<div class="arm_invoice_detail_popup popup_wrapper arm_invoice_detail_popup_wrapper">
					<div class="popup_wrapper_inner" style="overflow: hidden;">
						<div class="popup_header arm_text_align_center" >
							<span class="popup_close_btn arm_popup_close_btn arm_invoice_detail_close_btn"></span>
							<span class="add_rule_content"><?php esc_html_e('Invoice Detail','armember-membership' );?></span>
						</div>
						<div class="popup_content_text arm_invoice_detail_popup_text arm_padding_0" id="arm_invoice_detail_popup_text" >
							
							<iframe src="<?php echo esc_attr(ARM_HOME_URL)."/?log_id=".esc_attr($log_id)."&log_type=".esc_attr($log_type)."&is_display_invoice=1" ; ?>" id="arm_invoice_iframe" class="arm_width_100_pct" style="height:665px;"></iframe> <?php //phpcs:ignore ?>
						</div>
						<div class="popup_footer arm_text_align_center" style=" padding: 0 0 35px;">
							<button type="button" name="print" onclick="arm_print_invoice();" value="Print" class="armemailaddbtn"><?php esc_html_e('Print', 'armember-membership'); ?></button>
							<?php 
							$invoice_pdf_icon_html='';
							$invoice_pdf_icon_html=apply_filters('arm_membership_invoice_details_outside',$invoice_pdf_icon_html,$log_id);
							echo $invoice_pdf_icon_html; //phpcs:ignore
							?>
						</div>
					</div>
				</div>
			<?php
			}
			exit;
		}
        function arm_change_bank_transfer_status()
		{
			global $wp, $wpdb, $current_user, $arm_errors, $ARMemberLite, $arm_global_settings, $arm_subscription_plans,$arm_manage_coupons, $arm_lite_debug_payment_log_id, $arm_capabilities_global;
				
            $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_subscriptions'], '1'); //phpcs:ignore --Reason:Verifying nonce
			
            $log_id = intval($_POST['log_id']);//phpcs:ignore
            $logid_exit_flag = '';
            $new_status = sanitize_text_field($_POST['log_status']);//phpcs:ignore

			$response = array('status' => 'error', 'message' => esc_html__('Sorry, Something went wrong. Please try again.', 'armember-membership'));
			if (!empty($log_id) && $log_id != 0) {
				$log_data = $wpdb->get_row( $wpdb->prepare("SELECT `arm_log_id`, `arm_user_id`, `arm_plan_id`, `arm_payment_cycle` FROM `" . $ARMemberLite->tbl_arm_payment_log . "` WHERE `arm_log_id`=%d" , $log_id) ); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name

                

				do_action('arm_payment_log_entry', 'bank_transfer', 'Change status log data', 'armember-membership', $log_data, $arm_lite_debug_payment_log_id);

				if(!empty($log_data))
				{
					$user_id = $log_data->arm_user_id;
					$plan_id = $log_data->arm_plan_id;
                    $payment_cycle = $log_data->arm_payment_cycle;

                    if ($new_status == '1') {

                    	$plan_payment_mode = 'manual_subscription';
                    	$is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user($user_id, $plan_id, $plan_payment_mode);
					
						$nowDate = current_time('mysql');
                        $arm_last_payment_status = $wpdb->get_var( $wpdb->prepare("SELECT `arm_transaction_status` FROM `" . $ARMemberLite->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1",$user_id,$plan_id,$nowDate) ); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name
					 	$arm_subscription_plans->arm_update_user_subscription_for_bank_transfer($user_id, $plan_id, 'bank_transfer', $payment_cycle, $arm_last_payment_status);
						$wpdb->update($ARMemberLite->tbl_arm_payment_log, array('arm_transaction_status' => 1), array('arm_log_id' => $log_id)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						
						$userPlanData = get_user_meta($user_id, 'arm_user_plan_'.$plan_id, true);						
						if($is_recurring_payment)
						{
							do_action('arm_after_recurring_payment_success_outside', $user_id, $plan_id, 'bank_transfer', $plan_payment_mode);
						}
						
                        do_action('arm_after_accept_bank_transfer_payment', $user_id, $plan_id, $log_id);
						$response = array('status' => 'success', 'message' => esc_html__('Bank transfer request has been approved.', 'armember-membership'));
					} else {
						delete_user_meta($user_id, 'arm_change_plan_to');
						$wpdb->update($ARMemberLite->tbl_arm_payment_log, array('arm_transaction_status' => 2), array('arm_log_id' => $log_id)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                                                do_action('arm_after_decline_bank_transfer_payment',$user_id,$plan_id);
						$response = array('status' => 'success', 'message' => esc_html__('Bank transfer request has been cancelled.', 'armember-membership'));
					}
				}
			}

			do_action('arm_payment_log_entry', 'bank_transfer', 'Change bank transfer response', 'armember-membership', $response, $arm_lite_debug_payment_log_id);

			if(empty($logid_exit_flag))
			{
				echo wp_json_encode($response);
				exit;
			}
		}
        function arm_delete_transaction_data(){
			global $wpdb, $ARMemberLite, $arm_members_class, $arm_global_settings, $arm_capabilities_global;
			$ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_subscriptions'], '1'); //phpcs:ignore --Reason:Verifying nonce
			if (!isset($_POST))//phpcs:ignore
			{
				return;
			}
			
			$action = sanitize_text_field($_POST['act']);//phpcs:ignore
			$id = intval($_POST['id']);//phpcs:ignore
			if ($action == 'delete')
			{
				if (empty($id))
				{
					$errors[] = esc_html__('Invalid action.', 'armember-membership');
				}
				else
				{
					if (!current_user_can('arm_manage_subscriptions'))
					{
						$errors[] = esc_html__('Sorry, You do not have permission to perform this action.', 'armember-membership');
					}
					else {
                        $res_var = $wpdb->delete($ARMemberLite->tbl_arm_payment_log, array('arm_log_id' => $id)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

						if ($res_var)
						{
							$message = esc_html__('Record deleted successfully.', 'armember-membership');
						}
						else
						{
							$errors[] = esc_html__('Sorry, Something went wrong. Please try again.', 'armember-membership');
						}
					}
				}
			}
			$return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
			echo wp_json_encode($return_array);
			exit;
		}
        function arm_fetch_activity_data() {
            global $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways, $is_multiple_membership_feature, $arm_capabilities_global,$arm_transaction;
            
            $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_subscriptions'], '1');
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $user_roles = get_editable_roles();
            $nowDate = current_time('mysql');
            $arm_invoice_tax_feature = get_option('arm_is_invoice_tax_feature', 0);

            $global_currency = $arm_payment_gateways->arm_get_global_currency();
			$general_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : array();

            $response_data = array();
            $posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_REQUEST );//phpcs:ignore
            $filter_ptype = isset($posted_data['payment_type']) ? $posted_data['payment_type'] : '';
            $filter_search = isset($posted_data['sSearch']) ? $posted_data['sSearch'] : '';
            $filter_status = isset($posted_data['plan_status']) ? $posted_data['plan_status'] : '';

            $all_plans = $arm_subscription_plans->arm_get_all_subscription_plans();
            if(!empty($posted_data['data']))
            {
                $posted_data = json_decode(stripslashes_deep($posted_data['data']),true);
            }
            $sql = '';
            $filter = '';
            $total_results = 0;
            $response_result = array();
            

            
            $grid_columns = array(
                'username' => esc_html__('Username', 'armember-membership'),
                'name' => esc_html__('Name', 'armember-membership'),
                'date' => esc_html__('Start Date', 'armember-membership'),
                'arm_payment_cycle' => esc_html__('Expire/Next Renewal', 'armember-membership'),
                'amount' => esc_html__('Amount', 'armember-membership'),
                'arm_payment_type' => esc_html__('Payment Type', 'armember-membership'),
                'transaction' => esc_html__('Transaction', 'armember-membership'),
                'status' => esc_html__('Status', 'armember-membership'),
            );

            $displayed_grid_columns = $grid_columns;
            $filter_plans = (!empty($posted_data['arm_subs_plan_filter']) && $posted_data['arm_subs_plan_filter'] != '') ? $posted_data['arm_subs_plan_filter'] : '';
            $filter_status_id = (!empty($posted_data['filter_status_id']) && $posted_data['filter_status_id'] != 0) ? $posted_data['filter_status_id'] : '';
            $filter_gateway = (!empty($posted_data['payment_gateway']) && $posted_data['payment_gateway'] != '0') ? $posted_data['payment_gateway'] : '';
            $filter_plan_type = (!empty($posted_data['filter_plan_type']) && $posted_data['filter_plan_type'] != '') ? $posted_data['filter_plan_type'] : '';
            $filter_tab = (!empty($posted_data['selected_tab']) && $posted_data['selected_tab'] != '') ? $posted_data['selected_tab'] : 'activity';
            
            $grid_columns['action_btn'] = '';            
            $sorting_ord = isset($_REQUEST['sSortDir_0']) ? sanitize_text_field($_REQUEST['sSortDir_0']) : 'desc'; //phpcs:ignore
            $sorting_ord = strtolower($sorting_ord);
            $sorting_col = (isset($_REQUEST['iSortCol_0']) && $_REQUEST['iSortCol_0'] > 0) ? intval($_REQUEST['iSortCol_0']) : 0; //phpcs:ignore
            if ( empty($sorting_col) && ( 'asc'!=$sorting_ord && 'desc'!=$sorting_ord ) ) {
                $sorting_ord = 'desc';
            }
            $offset = isset($posted_data['iDisplayStart']) ? $posted_data['iDisplayStart'] : 0;
            $limit = isset($posted_data['iDisplayLength']) ? $posted_data['iDisplayLength'] : 10;
            $phlimit = " LIMIT {$offset},{$limit}";
            
            $response_data = array();
            $grid_columns = array(
                'arm_plan_id' => esc_html__('Membership', 'armember-membership'),
                'arm_username' => esc_html__('Username', 'armember-membership'),
                'arm_display_name' => esc_html__('Name', 'armember-membership'),
                'arm_payment_date' => esc_html__('Payment Date', 'armember-membership'),
                'arm_amount' => esc_html__('Amount', 'armember-membership'),
                'arm_payment_type' => esc_html__('Payment type', 'armember-membership'),
                'arm_transaction_status' => esc_html__('Payment type', 'armember-membership'),
            );
            $data_columns = array();
            $n = 0;
            foreach ($grid_columns as $key => $value) {
                $data_columns[$n]['data'] = $key;
                $n++;
            }
            unset($n);

            $sOrder = "";
            $orderby = $data_columns[(intval($sorting_col))]['data'];
            if(empty($orderby) || $sorting_col == 0){
                $order_by_qry = "ORDER BY pl.arm_log_id DESC";
            }
            else{
                $order_by_qry = "ORDER BY " . $orderby . " " . $sorting_ord ;
            }
            
            $sql = $wpdb->prepare("SELECT pl.arm_log_id,pl.arm_invoice_id,am.arm_user_id,am.arm_user_login,pl.arm_plan_id,pl.arm_payment_gateway,pl.arm_payment_type,pl.arm_transaction_status,pl.arm_payment_date,pl.arm_is_post_payment,pl.arm_paid_post_id,pl.arm_is_gift_payment,pl.arm_payment_mode,pl.arm_amount,pl.arm_currency FROM ".$ARMemberLite->tbl_arm_payment_log." pl LEFT JOIN ".$ARMemberLite->tbl_arm_members." am ON pl.arm_user_id = am.arm_user_id WHERE 1=1 AND pl.arm_user_id !=%d ",0); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log and $ARMemberLite->tbl_arm_members are a table names
            $filter ='';
            if (!empty($filter_gateway) && $filter_gateway != '0') {
                $filter .= $wpdb->prepare(" AND pl.arm_payment_gateway=%s",$filter_gateway);
            }
            if (!empty($filter_ptype) && $filter_ptype != '') {
                $filter .= $wpdb->prepare(" AND pl.arm_payment_type=%s",$filter_ptype);
            }
            if (!empty($filter_plans) && $filter_plans != '0') {
				$filter_act_plans = explode(',',$filter_plans);
                $page_placeholders = 'AND pl.arm_plan_id IN (';
                $page_placeholders .= rtrim( str_repeat( '%s,', count( $filter_act_plans ) ), ',' );
                $page_placeholders .= ')';
                array_unshift( $filter_act_plans, $page_placeholders );
                $filter .= call_user_func_array(array( $wpdb, 'prepare' ), $filter_act_plans );
                // $filter .= " AND pl.arm_plan_id IN ($filter_plans)";
            }
            if (!empty($filter_search) && $filter_search != '') {
                $filter .= $wpdb->prepare(" AND (pl.arm_plan_id LIKE %s OR pl.arm_payment_gateway LIKE %s OR pl.arm_payment_type LIKE %s OR pl.arm_transaction_status LIKE %s OR am.arm_user_login LIKE %s)",'%'.$filter_search.'%','%'.$filter_search.'%','%'.$filter_search.'%','%'.$filter_search.'%','%'.$filter_search.'%');
            }
            if (!empty($filter_status) && $filter_status != '') {
                $filter_pstatus = strtolower($filter_status);
                $status_query = $wpdb->prepare(" AND (pl.arm_transaction_status=%s",$filter_pstatus);
                if( !in_array($filter_pstatus,array('success','pending','canceled')) ){
                    $status_query .= ")";
                }
                switch ($filter_pstatus) {
                    case 'success':
                        $status_query .= $wpdb->prepare(" OR pl.arm_transaction_status=%s)",1);                        break;
                    case 'pending':
                        $status_query .= $wpdb->prepare(" OR pl.arm_transaction_status=%s)",0);
                        break;
                    case 'canceled':
                        $status_query .= $wpdb->prepare(" OR pl.arm_transaction_status=%s)",2);
                        break;
                }
                $filter .= $status_query;
            }
            $get_result_sql = $sql .' '. $filter . ' ' . $order_by_qry . ' '. $phlimit; //phpcs:ignore
            $response_result = $wpdb->get_results($get_result_sql,ARRAY_A); //phpcs:ignore --Reason $get_result_sql is a predefined query
            $before_filter_sql = $wpdb->get_results($sql);//phpcs:ignore --Reason $sql is a predefined query

            $before_filter = count($before_filter_sql);

            $total_results = $wpdb->get_results($sql .' '. $filter . ' ' . $order_by_qry);//phpcs:ignore --Reason $sql is a predefined query

            $after_filter = count($total_results);
            if(!empty($response_result))
            {
                $ai = 0;
                foreach($response_result as $rc)
                {
                    
                    $plan_detail = '';
                    $rc = (Object) $rc;
                    
                    $user_first_name = get_user_meta( $rc->arm_user_id,'first_name',true);
                    $user_last_name = get_user_meta( $rc->arm_user_id,'last_name',true);
                    
                    $plan_ID = $rc->arm_plan_id;                       
                    foreach($all_plans as $planData)
                    {
                        $planObj = new ARM_Plan_Lite();
                        $planObj->init((object) $planData);
                        $planID = $planData['arm_subscription_plan_id'];
                        if($plan_ID == $planID)
                        {
                            $plan_detail = $planObj->name;
                            break;
                        }
                    }
                    $response_data[$ai][0] = (!empty($plan_detail)) ? $plan_detail : '-';                    
                    
                    $response_data[$ai][1] = '<a class="arm_openpreview_popup" href="javascript:void(0)" data-id="'.esc_attr($rc->arm_user_id).'">'.esc_html($rc->arm_user_login).'</a>';

                    $response_data[$ai][2] = trim($user_first_name.' '.$user_last_name);
                    
                    $log_type = ($rc->arm_payment_gateway == 'bank_transfer') ? 'bt_log' : 'other';
                    $response_data[$ai][3] = date_i18n($date_format, strtotime($rc->arm_payment_date));
                    $currency_sym = (!empty($rc->arm_currency)) ? strtoupper($rc->arm_currency) : strtoupper($global_currency);
                    $response_data[$ai][4] = number_format(floatval($rc->arm_amount),2,'.',',') .' '. $currency_sym;
                    $payment_mode = (!empty($rc->arm_payment_mode)) ? $rc->arm_payment_mode : esc_html__('Semi Automatic','armember-membership');
                    if($payment_mode == 'auto_debit_subscription')
                    {
                        $payment_mode = '<span>'.esc_html__('Auto Debit','armember-membership').'</span>';
                    }
                    else
                    {
                        $payment_mode = '<span>'.esc_html__('Semi Automatic','armember-membership') .'</span>';
                    }
                    $payment_gateway = $arm_payment_gateways->arm_gateway_name_by_key($rc->arm_payment_gateway);
                    $payment_type = !empty($rc->arm_payment_mode) ? $rc->arm_payment_mode : 'manual';
                    if($payment_gateway != 'manual')
                    {
                        $payment_types = ($payment_type != 'auto_debit_subscription') ? esc_html__('Semi Automatic','armember-membership') : esc_html__('Auto Debit','armember-membership')  ;
                        $class = ($payment_type != 'auto_debit_subscription') ? 'arm_semi_auto' : 'arm_auto';
                        $response_data[$ai][5] = $payment_gateway." <br/><span class='arm_payment_types ".esc_attr($class)."'>".esc_html($payment_types)."</span>";                    
                    }
                    else
                    {
                        $response_data[$ai][5] = $payment_gateway;    
                    }
                    $arm_transaction_status = $rc->arm_transaction_status;
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
                            $arm_transaction_status = $rc->arm_transaction_status;
                            break;
                    }
                    $response_data[$ai][6] =  $arm_transaction->arm_get_transaction_status_text($arm_transaction_status);
                    $transactionID = $rc->arm_log_id;   
                    $gridAction = "<div class='arm_grid_action_btn_container'>";
                    if ($rc->arm_payment_gateway == 'bank_transfer' && $arm_transaction_status == 'pending') {
                    	$changeStatusFun = 'ChangeStatus(' . $transactionID .',1);';
                    	$chagneStatusFun2 = 'ChangeStatus(' . $transactionID . ',2);';
                    	$armbPopupArg = 'change_transaction_status_message';

                        $gridAction .= "<a class='armhelptip arm_change_btlog_status' href='javascript:void(0)' onclick=\"{$changeStatusFun}armBpopup('".$armbPopupArg."');\" data-status='1' data-log_id='" . esc_attr($transactionID) . "' title='" . esc_attr__('Approve', 'armember-membership') . "'><img src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_approved.png' onmouseover=\"this.src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_approved_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_approved.png';\" /></a>"; //phpcs:ignore
                        $gridAction .= "<a class='armhelptip arm_change_btlog_status' href='javascript:void(0)' onclick=\"{$chagneStatusFun2}armBpopup('".esc_attr($armbPopupArg)."');\" data-status='2' data-log_id='" . esc_attr($transactionID) . "' title='" . esc_attr__('Reject', 'armember-membership') . "'><img src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_denied.png' onmouseover=\"this.src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_denied_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_denied.png';\" /></a>"; //phpcs:ignore
                    } 
                    
                    $gridAction .= "<a class='armhelptip arm_preview_log_detail' href='javascript:void(0)' data-log_type='" . esc_attr($log_type) . "' data-log_id='" . esc_attr($transactionID) . "' data-trxn_status='".esc_attr($arm_transaction_status)."' title='" . esc_attr__('View Detail', 'armember-membership') . "'><img src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_preview.png' onmouseover=\"this.src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_preview_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_preview.png';\" /></a>"; //phpcs:ignore
                    $gridAction .= "<a href='javascript:void(0)' data-log_type='" . esc_attr($log_type) . "' data-delete_log_id='" . esc_attr($transactionID) . "' data-trxn_status='".esc_attr($arm_transaction_status)."' onclick='showConfirmBoxCallback(".esc_attr($transactionID).");'><img src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_delete.png' class='armhelptip' title='" . esc_attr__('Delete', 'armember-membership') . "' onmouseover=\"this.src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_delete_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_delete.png';\" /></a>"; //phpcs:ignore
                    $arm_transaction_del_cls = 'arm_transaction_delete_btn';
                    $gridAction .= $arm_global_settings->arm_get_confirm_box($transactionID, esc_html__("Are you sure you want to delete this transaction?", 'armember-membership'), $arm_transaction_del_cls, $log_type);
                    $gridAction .= "</div>";
                    $response_data[$ai][7] = $gridAction;
                    $ai++;
                }
            }
            $sEcho = isset($_REQUEST['sEcho']) ? intval($_REQUEST['sEcho']) : intval(10); //phpcs:ignore
            $response = array(
                'sColumns' => implode(',', $grid_columns),
                'sEcho' => $sEcho,
                'iTotalRecords' => $before_filter, // Before Filtered Records
                'iTotalDisplayRecords' => $after_filter, // After Filter Records
                'aaData' => $response_data,
            );
            echo wp_json_encode($response);
            die();
        }
        function arm_fetch_subscription_data() {
            global $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways, $is_multiple_membership_feature, $arm_capabilities_global,$arm_transaction;

            $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_subscriptions'], '1');//phpcs:ignore --Reason:Verifying nonce
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $user_roles = get_editable_roles();
            $nowDate = current_time('mysql');

            $global_currency = $arm_payment_gateways->arm_get_global_currency();
			$general_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : array();

            $response_data = array();
            $posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_REQUEST );//phpcs:ignore
            $filter_ptype = isset($posted_data['payment_type']) ? sanitize_text_field( $posted_data['payment_type'] ) : '';
            $filter_search = isset($posted_data['sSearch']) ? sanitize_text_field( $posted_data['sSearch'] ) : '';

            $all_plans = $arm_subscription_plans->arm_get_all_subscription_plans();
            if(!empty($posted_data['data']))
            {
                $posted_data = json_decode(stripslashes_deep($posted_data['data']),true);
            }
            $sql = '';
            $filter = '';
            $total_results = 0;
            $response_result = array();
            
            $filter_plans = (!empty($posted_data['arm_subs_filter']) && $posted_data['arm_subs_filter'] != '') ? $posted_data['arm_subs_filter'] : '';
            $filter_status_id = (!empty($posted_data['plan_status']) && $posted_data['plan_status'] != 0) ? intval( $posted_data['plan_status'] ) : '';
            $filter_gateway = (!empty($posted_data['payment_gateway']) && $posted_data['payment_gateway'] != '0') ? sanitize_text_field( $posted_data['payment_gateway'] ) : '';
            $filter_plan_type = (!empty($posted_data['filter_plan_type']) && $posted_data['filter_plan_type'] != '') ? sanitize_text_field( $posted_data['filter_plan_type'] ) : '';
            if($filter_plan_type!='one_time' && $filter_plan_type!='subscription')
            {
                $filter_plan_type = 0;
            }
            $filter_tab = (!empty($posted_data['selected_tab']) && $posted_data['selected_tab'] != '') ? esc_attr( $posted_data['selected_tab'] ) : 'activity';
            if($filter_tab!='activity')
            {
                $filter_tab = 'subscriptions';
            }
            
            $sorting_ord = !empty($posted_data['sSortDir_0']) ? strtoupper($posted_data['sSortDir_0']) : 'DESC';
            if($sorting_ord!='ASC')
            {
                $sorting_ord = 'DESC';
            }
            $sorting_col = (isset($posted_data['iSortCol_0']) && $posted_data['iSortCol_0'] > 0) ? intval($posted_data['iSortCol_0']) : 1;
            if(empty($sorting_col)) { $sorting_col = 1; }

            $offset = isset($posted_data['iDisplayStart']) ? intval( $posted_data['iDisplayStart'] ) : 0;
            $limit = isset($posted_data['iDisplayLength']) ? intval( $posted_data['iDisplayLength'] ) : 10;
            $phlimit = " LIMIT {$offset},{$limit}";
            
            $response_data = array();
            $grid_columns = array(
                'arm_activity_id' => esc_html__('ID', 'armember-membership'),
                'arm_item_id' => esc_html__('Membership', 'armember-membership'),
                'arm_user_login' => esc_html__('Username', 'armember-membership'),
                'name' => esc_html__('Name', 'armember-membership'),
                'arm_date_recorded' => esc_html__('Start Date', 'armember-membership'),
                'arm_next_cycle_date' => esc_html__('Expire/Next Renewal', 'armember-membership'),
                'arm_amount' => esc_html__('Amount Type', 'armember-membership'),
                'arm_payment_type' => esc_html__('Payment Type', 'armember-membership'),
                'arm_transactions' => esc_html__('Transaction', 'armember-membership'),
                'arm_plan_status' => esc_html__('Status', 'armember-membership'),
            );
            $grid_columns['action_btn'] = '';    
            $data_columns = array();
            $n = 1;
            foreach ($grid_columns as $key => $value) {
                $data_columns[$n]['data'] = $key;
                $n++;
            }
            unset($n);
            $sql = $wpdb->prepare('SELECT act.*,am.arm_user_login FROM '.$ARMemberLite->tbl_arm_activity.' act LEFT JOIN '.$ARMemberLite->tbl_arm_members.' am ON act.arm_user_id = am.arm_user_id WHERE act.arm_user_id !=%d AND act.arm_action = %s',0,"new_subscription"); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_members is a table name
            
            $orderby = $data_columns[(intval($sorting_col))]['data'];

            $order_by_qry = "ORDER BY " . $orderby . " " . $sorting_ord ;
            if(!empty($filter_gateway))
            {
                $filter_gateway = '"'.$filter_gateway.'"';
                $filter .= $wpdb->prepare("AND act.arm_content LIKE %s ",'%'.$filter_gateway.'%');
            }
            if(!empty($filter_ptype))
            {
                $filter_data = '%s:17:"plan_payment_type";s:8:"'.$filter_ptype.'"%';
                if($filter_ptype == 'subscription')
                {
                    $filter_data = '%s:17:"plan_payment_type";s:12:"'.$filter_ptype.'"%';
                }
                $filter .= $wpdb->prepare("AND act.arm_content LIKE %s",$filter_data);
            }
            if(!empty($filter_search))
            {
                $filter .= $wpdb->prepare('AND (am.arm_user_login LIKE %s) ','%'.$filter_search.'%');
            }
            if (!empty($filter_plans) && $filter_plans != '0') {
                $filter_sub_plans = explode(',', $filter_plans);
                $admin_placeholders = ' AND act.arm_item_id IN (';
				$admin_placeholders .= rtrim( str_repeat( '%d,', count( $filter_sub_plans ) ), ',' );
				$admin_placeholders .= ')';
				array_unshift( $filter_sub_plans, $admin_placeholders );
				
				$filter .= call_user_func_array(array( $wpdb, 'prepare' ), $filter_sub_plans );               
            }
            if(!empty($filter_status_id))
            {
                $user_ids = array();
                $plan_ids = array();
                $filter_sql = $sql;
                $filter_response_result = $wpdb->get_results($filter_sql); //phpcs:ignore --Reason $filter_sql is a query
                if(!empty($filter_response_result))
                {
                    foreach($filter_response_result as $rc)
                    {
                        $rc = (object) $rc;
                        $user_plan_detail = get_user_meta($rc->arm_user_id, 'arm_user_plan_'.$rc->arm_item_id, true);
                        $get_activity_data = maybe_unserialize($rc->arm_content);
                        $start_plan_date = !empty($rc->arm_activity_plan_start_date) ? $rc->arm_activity_plan_start_date : '';
                        $plan_status = $this->get_return_status_data($rc->arm_user_id,$rc->arm_item_id,$user_plan_detail,strtotime($start_plan_date));
                        $suspended_plan_detail = get_user_meta($rc->arm_user_id, 'arm_user_suspended_plan_ids', true);
                        
                        if(!empty($plan_status['status']) && $plan_status['status'] == 'suspended' && $filter_status_id == '3')
                        {
                            array_push($user_ids,$rc->arm_activity_id);
                        }
                        else if( !empty($plan_status['status']) && $plan_status['status'] == 'canceled' && $filter_status_id == '4')
                        {
                            array_push($user_ids,$rc->arm_activity_id);
                        }
                        else if( !empty($plan_status['status']) && $plan_status['status'] == 'expired' && $filter_status_id == '2')
                        {
                            array_push($user_ids,$rc->arm_activity_id);
                        }
                        else if(!empty($plan_status['status']) && $plan_status['status'] == 'active' &&  $filter_status_id == '1' && (empty($suspended_plan_detail) || !in_array($rc->arm_item_id,$suspended_plan_detail)))
                        {
                            array_push($user_ids,$rc->arm_activity_id);
                        }
                    }
                }
                if(!empty($user_ids))
                {
                    $admin_placeholders = ' AND act.arm_activity_id IN (';
                    $admin_placeholders .= rtrim( str_repeat( '%s,', count( $user_ids ) ), ',' );
                    $admin_placeholders .= ')';
                    array_unshift( $user_ids, $admin_placeholders );
                    
                    $filter .= call_user_func_array(array( $wpdb, 'prepare' ), $user_ids );   
                }
            }
            
            $before_filter_total_results = $wpdb->get_results($sql); //phpcs:ignore --Reason $sql is a Predefined query
            
            $before_filter = count($before_filter_total_results);

            $get_result_sql = $sql .' '. $filter . ' '.$order_by_qry.' '. $phlimit;

            $response_result = $wpdb->get_results($get_result_sql); //phpcs:ignore --Reason $get_result_sql is a predefined query

            $total_results = $wpdb->get_results($sql .' '. $filter . ' '.$order_by_qry);//phpcs:ignore --Reason $sql is a predefined query

           
            $after_filter = count($total_results);
            
            if(!empty($response_result))
            {
                $ai = 0;
                foreach($response_result as $rc)
                {
                    $rc = (object) $rc;
                    $activity_id = $rc->arm_activity_id;
                    $user_id = $rc->arm_user_id;
                    $plan_id = $rc->arm_item_id;
                    $user_first_name = get_user_meta( $user_id,'first_name',true);
                    $user_last_name = get_user_meta( $user_id,'last_name',true);
                    $plan_name = '';
                    $response_data[$ai][1] = $rc->arm_activity_id;
                    $get_activity_data = maybe_unserialize($rc->arm_content);
                    $arm_currency = !empty($get_activity_data['arm_currency']) ? $get_activity_data['arm_currency'] : $global_currency;
                    $start_plan_date = !empty($rc->arm_activity_plan_start_date) ? strtotime($rc->arm_activity_plan_start_date) : '';
                    $user_future_plan_ids = get_user_meta($user_id, 'arm_user_future_plan_ids', true);
                    if(!empty($get_activity_data))
                    {
                        $grace_period_data = $plan_detail = $membership_start = '';
                        $plan_text = htmlentities($get_activity_data['plan_text']);
                        $plan_details = explode('&lt;br/&gt;',$plan_text);
                        
                        $plan_detail = (!empty($plan_details[1])) ? wp_strip_all_tags(html_entity_decode($plan_details[1])) : '';
                        $user_plan_detail = get_user_meta($user_id, 'arm_user_plan_'.$rc->arm_item_id, true);
                        $membership_start = (!empty($user_plan_detail['arm_start_plan'])) ? $user_plan_detail['arm_start_plan'] : 0;
                        if(!empty($user_plan_detail['arm_is_user_in_grace']) && $user_plan_detail['arm_is_user_in_grace'] == 1)
                        {
                            $grace_period_data = "<span class='arm_item_status_plan grace'>".esc_html__('Grace Expiration','armember-membership').": ". esc_html(date_i18n($date_format, $user_plan_detail['arm_grace_period_end']))."</span>";
                        }
                        if(!empty($user_future_plan_ids) && in_array($plan_id,$user_future_plan_ids)){
                            $grace_period_data .= " <span class='arm_item_status_plan plan_future'>".esc_html__('Future Membership','armember-membership')."</span>";
                        }
                        if(!empty($user_plan_detail['arm_current_plan_detail']) && !empty($user_plan_detail['arm_current_plan_detail']['arm_subscription_plan_type']) && $user_plan_detail['arm_current_plan_detail']['arm_subscription_plan_type'] == 'recurring')
                        {
                            $arm_subscription_plans_expire = date_i18n($date_format, $user_plan_detail['arm_next_due_payment']);
                        }
                        else
                        {
                            $arm_subscription_plans_expire = !empty($user_plan_detail['arm_expire_plan']) ? date_i18n($date_format, $user_plan_detail['arm_expire_plan']) : '-';
                        }
                        $suspended_plan_detail = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                        $plan_status = $this->get_return_status_data($user_id,$rc->arm_item_id,$user_plan_detail,$start_plan_date);
                        $status = !empty($plan_status['status']) ? $plan_status['status'] : '';
                        $canceled_date = !empty($plan_status['canceled_date']) ? $plan_status['canceled_date'] : '';
                        if(!empty($plan_status['status']) && $plan_status['status'] == 'suspended')
                        {
                            $status = 'suspended';
                            $response_data[$ai][10] = '<span class="arm_item_status_plan cancelled">'.esc_html__('Suspended','armember-membership').'</span>';
                        }
                        else if(!empty($plan_status['status']) &&  $plan_status['status'] == 'canceled')
                        {
                            $status = 'canceled';
                            $arm_subscription_plans_expire = '-';
                            $response_data[$ai][10] = '<span class="arm_item_status_plan cancelled">'.esc_html__('Canceled','armember-membership').'</span>';
                        }
                        else if( !empty($plan_status['status']) && $plan_status['status'] == 'expired')
                        {
                            $status = 'expired';
                            $arm_subscription_plans_expire = '-';
                            $response_data[$ai][10] = '<span class="arm_item_status_plan expired">'.esc_html__('Expired','armember-membership').'</span>';
                        }
                        else if( !empty($plan_status['status']) && $plan_status['status'] == 'active')
                        {
                            $status = 'active';
                            $response_data[$ai][10] ='<span class="arm_item_status_plan active">'.esc_html__('Active','armember-membership').'</span>';
                        }
                        else{
                            $arm_subscription_plans_expire = '-';
                            $status ='';
                            $response_data[$ai][10] ='';
                        }
                        $plan_name = $get_activity_data['plan_name'];
                        
                        $response_data[$ai][2] = $get_activity_data['plan_name'] . "<br/><span class='arm_plan_style'>".$plan_detail."</span><br/>". $grace_period_data;
                        $response_data[$ai][6] = $arm_subscription_plans_expire;
                        $response_data[$ai][7] = number_format(floatval($get_activity_data['plan_amount']),2,'.',',') . ' '. $arm_currency;

                        $payment_type = !empty($user_plan_detail['arm_payment_mode']) ? $user_plan_detail['arm_payment_mode'] : 'manual';
                        
                    }

                    $response_data[$ai][3] = '<a class="arm_openpreview_popup" href="javascript:void(0)" data-id="'.$user_id.'">'.$rc->arm_user_login.'</a>';
                    $response_data[$ai][4] = $user_first_name . ' ' .$user_last_name;
                    $start_plan_date = !empty($rc->arm_activity_plan_start_date) ? strtotime($rc->arm_activity_plan_start_date) : '';
                    $response_data[$ai][5] = !empty($start_plan_date) ? date_i18n($date_format, $start_plan_date) : '-';
                    $transaction_started_date = date('Y-m-d H:i:s', ($start_plan_date - 120)); //phpcs:ignore
                    $payment_gateway = $get_activity_data['gateway'];
                    if($payment_gateway == 'manual')
                    {
                        $transaction_started_date = date('Y-m-d 00:00:00', $start_plan_date); //phpcs:ignore
                    }
                    
                    if(!empty($canceled_date))
                    {
                        $get_last_transaction_sql = $wpdb->prepare("SELECT * FROM ".$ARMemberLite->tbl_arm_payment_log." WHERE arm_user_id=%d AND arm_plan_id=%d AND arm_created_date BETWEEN %s AND %s ORDER BY arm_log_id DESC",$user_id,$rc->arm_item_id,$transaction_started_date,$canceled_date); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name
                    }
                    else
                    {
                        if(!empty($user_plan_detail['arm_trial_start']))
                        {
                            $transaction_started_date = date('Y-m-d H:i:s', ($user_plan_detail['arm_trial_start'] - 120)); //phpcs:ignore
                        }
                        $get_last_transaction_sql = $wpdb->prepare("SELECT * FROM ".$ARMemberLite->tbl_arm_payment_log." WHERE arm_user_id=%d AND arm_plan_id=%d AND arm_created_date >= %s ORDER BY arm_log_id DESC",$user_id,$rc->arm_item_id,$transaction_started_date); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name
                    }
                    
                    $get_transaction_sql = $wpdb->get_results($get_last_transaction_sql,ARRAY_A); //phpcs:ignore --Reason get_last_transaction_sql is a query
                    $transaction_count = 0;
                    $payment_row = $payment_gateway_text = $arm_payment_gateways->arm_gateway_name_by_key($payment_gateway);
                    $payment_types = '';
                    $class = '';                   
                    if($payment_gateway != 'manual')
                    {
                        $payment_types = ($payment_type != 'auto_debit_subscription') ? esc_html__('Semi Automatic','armember-membership') : esc_html__('Auto Debit','armember-membership')  ;
                        $class = ($payment_type != 'auto_debit_subscription') ? 'arm_semi_auto' : 'arm_auto';
                        $payment_row = $payment_gateway_text." <br/><span class='arm_payment_types ".$class."'>".$payment_types."</span>";
                    }
                    if(!empty($get_transaction_sql))
                    {
                        $total_trans = count($get_transaction_sql);
                        
                        if($payment_gateway != 'manual')
                        {
                            $response_data[$ai][8] = $payment_row;                    
                        }
                        else
                        {
                            $response_data[$ai][8] = $payment_gateway_text;  
                        }
                        $response_data[$ai][9] = $total_trans;
                        $transaction_count = $total_trans;
                    }
                    else
                    {
                        $response_data[$ai][8] = esc_html__('Manual','armember-membership');
                        $response_data[$ai][9] ='0';
                        $transaction_count = 0;
                    }
                    $activityID = $rc->arm_activity_id;   
                    if($transaction_count > 0)
                    {
                        $response_data[$ai][0] = "<div class='arm_show_user_more_transactions' id='arm_show_user_more_transaction_" . esc_attr($activityID) . "' data-id='" . esc_attr($activityID) . "'></div>";
                    }
                    else
                    {
                        $response_data[$ai][0] = "";
                    }
                    $gridAction ='';
                    $gridAction .= "<div class='arm_grid_action_btn_container'>";
                    if($status == 'active')
                    {

                        $gridAction .= "<a href='javascript:void(0)' data-cancel_activity_type='" . esc_attr($status) . "'  data-cancel_activity_id='" . esc_attr($activityID) . "' onclick='showConfirmBoxCallback(".esc_attr($activityID).");'><img src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_denied.png' class='armhelptip' title='" . esc_attr__('Cancel', 'armember-membership') . "' onmouseover=\"this.src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_denied_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_denied.png';\" /></a>"; //phpcs:ignore
                        $arm_transaction_del_cls = 'arm_activity_delete_btn';
                        $gridAction .= $arm_global_settings->arm_get_confirm_box($activityID, esc_html__("Are you sure you want to cancel this subscription  ?", 'armember-membership'), $arm_transaction_del_cls);

                    }
                    if($status == 'suspended')
                    {
                        $gridAction .= "<a href='javascript:void(0)' data-activation_id='" . esc_attr($activityID) . "' data-plan_id='" . esc_attr($plan_id) . "' onclick='showConfirmBoxCallback_activation(".esc_attr($activityID).");'><img src='" . MEMBERSHIPLITE_IMAGES_URL . "/arm-active-plan.png' class='armhelptip' title='" . esc_attr__('Activate Plan', 'armember-membership') . "' onmouseover=\"this.src='" . MEMBERSHIPLITE_IMAGES_URL . "/arm-active-plan-hover.png';\" onmouseout=\"this.src='" . MEMBERSHIPLITE_IMAGES_URL . "/arm-active-plan.png';\" style='vertical-align:middle' /></a>"; //phpcs:ignore

                        $arm_plan_is_suspended = "<div class='arm_confirm_box arm_confirm_box_activate_".esc_attr($activityID)."' id='arm_confirm_box_activate_".esc_attr($activityID)."' style='right: -5px;'>";
                        $arm_plan_is_suspended .= "<div class='arm_confirm_box_body'>";
                        $arm_plan_is_suspended .= "<div class='arm_confirm_box_arrow'></div>";
                        $arm_plan_is_suspended .= "<div class='arm_confirm_box_text'>" . esc_html__("Are you sure you want to activate", 'armember-membership') . " " . esc_html($plan_name) . esc_html__(" plan for this user?", 'armember-membership') . "</div>";
                        $arm_plan_is_suspended .= "<div class='arm_confirm_box_btn_container'>";//phpcs:ignore
                        $arm_plan_is_suspended .= "<button type='button' class='arm_confirm_box_btn armok arm_plan_activation_change' data-item_id='".esc_attr($activityID)."'>" . esc_html__('Activate', 'armember-membership') . "</button>";
                        $arm_plan_is_suspended .= "<button type='button' class='arm_confirm_box_btn armcancel' onclick='hideConfirmBoxCallback();'>" . esc_html__('Cancel', 'armember-membership') . "</button>";
                        $arm_plan_is_suspended .= "</div>";
                        $arm_plan_is_suspended .= "</div>";
                        $arm_plan_is_suspended .= "</div></div>";

                        $gridAction .= $arm_plan_is_suspended;
                    }
                    $gridAction .= "</div>";
                    $response_data[$ai][11] = $gridAction;
                    $ai++;
                }
                // exit;
            }
            $sEcho = isset($_REQUEST['sEcho']) ? intval($_REQUEST['sEcho']) : intval(10); //phpcs:ignore
            $response = array(
                'sColumns' => implode(',', $grid_columns),
                'sEcho' => $sEcho,
                'iTotalRecords' => $before_filter, // Before Filtered Records
                'iTotalDisplayRecords' => $after_filter, // After Filter Records
                'aaData' => $response_data,
            );
            echo wp_json_encode($response);
            die();
        
        }

        function arm_fetch_upcoming_subscription_data() {
            global $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways, $arm_capabilities_global,$arm_transaction;
        
            $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_subscriptions'], '1');//phpcs:ignore --Reason:Verifying nonce
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $user_roles = get_editable_roles();
            $nowDate = current_time('mysql');
        
            $global_currency = $arm_payment_gateways->arm_get_global_currency();
            $general_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : array();
            $arm_currency_decimal = isset($general_settings['arm_currency_decimal_digit']) ? $general_settings['arm_currency_decimal_digit'] : 2;
        
            $response_data = array();
            $posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_REQUEST );//phpcs:ignore
            $filter_ptype = isset($posted_data['payment_type']) ? sanitize_text_field( $posted_data['payment_type'] ) : '';
            $filter_search = isset($posted_data['sSearch']) ? sanitize_text_field( $posted_data['sSearch'] ) : '';
        
            $all_plans = $arm_subscription_plans->arm_get_all_subscription_plans();
            if(!empty($posted_data['data']))
            {
                $posted_data = json_decode(stripslashes_deep($posted_data['data']),true);
            }
            $sql = '';
            $filter = '';
            $total_results = 0;
            $response_result = array();
            
            $filter_plans = (!empty($posted_data['arm_subs_filter']) && $posted_data['arm_subs_filter'] != '') ? $posted_data['arm_subs_filter'] : '';
            $filter_status_id = (!empty($posted_data['plan_status']) && $posted_data['plan_status'] != 0) ? intval( $posted_data['plan_status'] ) : '';
            $filter_gateway = (!empty($posted_data['payment_gateway']) && $posted_data['payment_gateway'] != '0') ? sanitize_text_field( $posted_data['payment_gateway'] ) : '';
            $filter_plan_type = (!empty($posted_data['filter_plan_type']) && $posted_data['filter_plan_type'] != '') ? sanitize_text_field( $posted_data['filter_plan_type'] ) : '';
            $filter_tab = (!empty($posted_data['selected_tab']) && $posted_data['selected_tab'] != '') ? esc_attr( $posted_data['selected_tab'] ) : 'activity';
                       
            $sorting_col = (isset($posted_data['iSortCol_0']) && $posted_data['iSortCol_0'] > 0) ? intval( $posted_data['iSortCol_0'] ) : 1;

            $sorting_ord = !empty($posted_data['sSortDir_0']) ? strtoupper($posted_data['sSortDir_0']) : 'DESC';
            if($sorting_ord!='ASC')
            {
                $sorting_ord = 'DESC';
            }
        
            $offset = isset($posted_data['iDisplayStart']) ? intval( $posted_data['iDisplayStart'] ) : 0;
            $limit = isset($posted_data['iDisplayLength']) ? intval( $posted_data['iDisplayLength'] ) : 10;
            $phlimit = " LIMIT {$offset},{$limit}";
            
            $response_data = array();
            $grid_columns = array(
                'arm_activity_id' => esc_html__('ID', 'armember-membership'),
                'arm_item_id' => esc_html__('Membership', 'armember-membership'),
                'arm_user_login' => esc_html__('Username', 'armember-membership'),
                'name' => esc_html__('Name', 'armember-membership'),
                'arm_date_recorded' => esc_html__('Start Date', 'armember-membership'),
                'arm_next_cycle_date' => esc_html__('Expire/Next Renewal', 'armember-membership'),
                'arm_amount' => esc_html__('Amount Type', 'armember-membership'),
                'arm_payment_type' => esc_html__('Payment Type', 'armember-membership'),
                'arm_plan_status' => esc_html__('Status', 'armember-membership'),
            );
            $grid_columns['action_btn'] = '';    
            $data_columns = array();
            $n = 0;
            foreach ($grid_columns as $key => $value) {
                $data_columns[$n]['data'] = $key;
                $n++;
            }
            unset($n);
            $sql = $wpdb->prepare('SELECT act.*,am.arm_user_login FROM '.$ARMemberLite->tbl_arm_activity.' act LEFT JOIN '.$ARMemberLite->tbl_arm_members.' am ON act.arm_user_id = am.arm_user_id WHERE act.arm_user_id !=%d AND act.arm_action != "eot"',0); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_members is a table name
            
            $orderby = 'act.arm_activity_id';
        
            $order_by_qry = "ORDER BY " . $orderby . " " . $sorting_ord ;
            if(!empty($filter_gateway))
            {
                $filter_gateway = '"'.$filter_gateway.'"';
                $filter .= $wpdb->prepare("AND act.arm_content LIKE %s ",'%'.$filter_gateway.'%');
            }
            if(!empty($filter_ptype))
            {
                $filter_data = '%s:17:"plan_payment_type";s:8:"'.$filter_ptype.'"%';
                if($filter_ptype == 'subscription')
                {
                    $filter_data = '%s:17:"plan_payment_type";s:12:"'.$filter_ptype.'"%';
                }
                $filter .= $wpdb->prepare("AND act.arm_content LIKE %s",$filter_data);
            }
            if(!empty($filter_search))
            {
                $filter .= $wpdb->prepare('AND (am.arm_user_login LIKE %s) ','%'.$filter_search.'%');
            }
            if (!empty($filter_plans) && $filter_plans != '0') {
                $filter_sub_plans = explode(',', $filter_plans);
                $admin_placeholders = ' AND act.arm_item_id IN (';
                $admin_placeholders .= rtrim( str_repeat( '%d,', count( $filter_sub_plans ) ), ',' );
                $admin_placeholders .= ')';
                array_unshift( $filter_sub_plans, $admin_placeholders );
                
                $filter .= call_user_func_array(array( $wpdb, 'prepare' ), $filter_sub_plans );               
            }
            
            //fetch all active plan only
            $user_ids = array();
            $plan_ids = array();
            $filter_sql = $sql;
            $filter_response_result = $wpdb->get_results($filter_sql); //phpcs:ignore --Reason $filter_sql is a query
            
            if(!empty($filter_response_result))
            {
                foreach($filter_response_result as $rc)
                {
                    $rc = (object) $rc;
                    $user_plan_detail = get_user_meta($rc->arm_user_id, 'arm_user_plan_'.$rc->arm_item_id, true);
                    $suspended_plan_detail = get_user_meta($rc->arm_user_id, 'arm_user_suspended_plan_ids', true);
                    $suspended_plan_ids = maybe_unserialize( $suspended_plan_detail );
                    if(empty($suspended_plan_ids) || (!empty($suspended_plan_ids) && !in_array($rc->arm_item_id,$suspended_plan_ids)))
                    {
                        $get_activity_data = maybe_unserialize($rc->arm_content);
                        $start_plan_type = !empty($user_plan_detail['arm_current_plan_detail']['arm_subscription_plan_type']) ? $user_plan_detail['arm_current_plan_detail']['arm_subscription_plan_type'] : 'recurring';
                        $arm_subscription_plans_expire = '';
                        if(!empty($user_plan_detail['arm_current_plan_detail']) && $start_plan_type == 'recurring')
                        {
                            $arm_subscription_plans_expire = !empty($user_plan_detail['arm_next_due_payment']) ? $user_plan_detail['arm_next_due_payment']: '';
                        }
                        if(!empty($user_plan_detail['arm_current_plan_detail']) && $start_plan_type == 'paid_finite')
                        {
                            $arm_subscription_plans_expire = !empty($user_plan_detail['arm_expire_plan']) ? $user_plan_detail['arm_expire_plan'] : '';
                        }              
                        if(in_array($start_plan_type,array('recurring','paid_finite')) && !empty($arm_subscription_plans_expire) && $arm_subscription_plans_expire >= current_time('timestamp'))
                        {
                            array_push($user_ids,$rc->arm_activity_id);
                        }
                    }
                }
            }
            $before_filter = 1;
            $after_filter = 1;
            if(!empty($user_ids))
            {
                $admin_placeholders = ' AND act.arm_activity_id IN (';
                $admin_placeholders .= rtrim( str_repeat( '%s,', count( $user_ids ) ), ',' );
                $admin_placeholders .= ')';
                array_unshift( $user_ids, $admin_placeholders );
                
                $sql .= call_user_func_array(array( $wpdb, 'prepare' ), $user_ids );   
            
            
                //Get upcoming subscriptions
                $fetch_all_activities = $wpdb->get_results($sql); //phpcs:ignore --Reason $sql is a Predefined query

            if(!empty($fetch_all_activities))
            {
                $arm_eot_activity = array();
                foreach($fetch_all_activities as $rct)
                {
                    $rct = (object) $rct;
                    $activity_id = $rct->arm_activity_id;
                    $user_id = $rct->arm_user_id;
                    $plan_id = $rct->arm_item_id;
                    $suspended_plan_detail = get_user_meta( $user_id, 'arm_user_suspended_plan_ids', true );
                    $check_if_plan_is_canceled = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $ARMemberLite->tbl_arm_activity WHERE arm_user_id = %d AND arm_item_id =%d AND arm_activity_id > %d AND (arm_action = %s OR arm_action = %s)",$user_id,$plan_id,$activity_id,'cancel_subscription','eot'),ARRAY_A); //phpcs:ignore
    
                    if(!empty($check_if_plan_is_canceled) && count($check_if_plan_is_canceled) > 0)
                    {
                        array_push($arm_eot_activity,$activity_id);
                    }

                    if(!empty($suspended_plan_detail) && in_array($plan_id,$suspended_plan_detail))
                    {
                        if(!in_array($activity_id,$arm_eot_activity))
                        {
                            array_push($arm_eot_activity,$activity_id);
                        }
                    }
                }
                if(!empty($arm_eot_activity)){
                    $sql = $sql.' AND act.arm_activity_id NOT IN ('.implode(',',$arm_eot_activity).')';
                }
            }

            $before_filter_total_results = $wpdb->get_results($sql); //phpcs:ignore
                
                $before_filter_total_results = $wpdb->get_results($sql); //phpcs:ignore --Reason $sql is a Predefined query
                
                $before_filter = count($before_filter_total_results);
            
                $get_result_sql = $sql .' '. $filter . ' '.$order_by_qry.' '. $phlimit;
            
                $response_result = $wpdb->get_results($get_result_sql); //phpcs:ignore --Reason $get_result_sql is a predefined query
            
                $total_results = $wpdb->get_results($sql .' '. $filter . ' '.$order_by_qry);//phpcs:ignore --Reason $sql is a predefined query
            
            
                $after_filter = count($total_results);
                
                if(!empty($response_result))
                {
                    $ai = 0;
                    foreach($response_result as $rc)
                    {
                        $rc = (object) $rc;
                        $activity_id =$activityID = $rc->arm_activity_id;
                        $user_id = $rc->arm_user_id;
                        $plan_id = $rc->arm_item_id;
                        $user_first_name = get_user_meta( $user_id,'first_name',true);
                        $user_last_name = get_user_meta( $user_id,'last_name',true);
                        $plan_name = '';
                        $response_data[$ai][0] = $rc->arm_activity_id;
                        $get_activity_data = maybe_unserialize($rc->arm_content);
                        $arm_currency = !empty($get_activity_data['arm_currency']) ? $get_activity_data['arm_currency'] : $global_currency;
                        $start_plan_date = !empty($rc->arm_activity_plan_start_date) ? $rc->arm_activity_plan_start_date : '';
                        $user_future_plan_ids = get_user_meta($user_id, 'arm_user_future_plan_ids', true);
                        $membership_data = "<span class='arm_item_status_plan paid_post'>".esc_html__('Plan','armember-membership')."</span>";
                        if(!empty($get_activity_data))
                        {
                            $grace_period_data = $plan_detail = $membership_start = '';
                            $plan_text = htmlentities($get_activity_data['plan_text']);
                            $plan_details = explode('&lt;br/&gt;',$plan_text);
                            
                            $plan_detail = (!empty($plan_details[1])) ? wp_strip_all_tags(html_entity_decode($plan_details[1])) : '';
                            $user_plan_detail = get_user_meta($user_id, 'arm_user_plan_'.$rc->arm_item_id, true);
                            $membership_start = (!empty($user_plan_detail['arm_start_plan'])) ? $user_plan_detail['arm_start_plan'] : 0;
                            if(!empty($user_plan_detail['arm_is_user_in_grace']) && $user_plan_detail['arm_is_user_in_grace'] == 1)
                            {
                                $grace_period_data = "<span class='arm_item_status_plan grace'>".esc_html__('Grace Expiration','armember-membership').": ". esc_html(date_i18n($date_format, $user_plan_detail['arm_grace_period_end']))."</span>";
                            }
                            if(!empty($user_future_plan_ids) && in_array($plan_id,$user_future_plan_ids)){
                                $grace_period_data .= " <span class='arm_item_status_plan plan_future'>".esc_html__('Future Membership','armember-membership')."</span>";
                            }
                            if(!empty($user_plan_detail['arm_current_plan_detail']) && !empty($user_plan_detail['arm_current_plan_detail']['arm_subscription_plan_type']) && $user_plan_detail['arm_current_plan_detail']['arm_subscription_plan_type'] == 'recurring')
                            {
                                $arm_subscription_plans_expire = date_i18n($date_format, $user_plan_detail['arm_next_due_payment']);
                            }
                            else
                            {
                                $arm_subscription_plans_expire = !empty($user_plan_detail['arm_expire_plan']) ? date_i18n($date_format, $user_plan_detail['arm_expire_plan']) : '-';
                            }
                            $suspended_plan_detail = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                            $plan_name = $get_activity_data['plan_name'];
                            
                            $response_data[$ai][1] = $get_activity_data['plan_name'] . "<br/><span class='arm_plan_style'>".$plan_detail."</span><br/>".$grace_period_data;

                            $response_data[$ai][5] = $arm_subscription_plans_expire;
                            
                            $response_data[$ai][6] = number_format(floatval($get_activity_data['plan_amount']),$arm_currency_decimal,'.',',') . ' '. $arm_currency;
            
                            $payment_type = !empty($user_plan_detail['arm_payment_mode']) ? $user_plan_detail['arm_payment_mode'] : 'manual';
                            
                        }
            
                        $response_data[$ai][2] = '<a class="arm_openpreview_popup" href="javascript:void(0)" data-id="'.esc_attr($user_id).'">'.$rc->arm_user_login.'</a>';
                        $response_data[$ai][3] = $user_first_name . ' ' .$user_last_name;
                        $arm_start_plan_date = !empty($start_plan_date) ? strtotime($start_plan_date) : '';
                        $response_data[$ai][4] = !empty($arm_start_plan_date) ? date_i18n($date_format, $arm_start_plan_date) : '-';
                        $transaction_started_date = !empty($arm_start_plan_date) ? date('Y-m-d H:i:s', ($arm_start_plan_date - 120)) : '-'; //phpcs:ignore
                        $payment_gateway = !empty($rc->arm_activity_payment_gateway) ? $rc->arm_activity_payment_gateway : 'manual';
                        if($payment_gateway == 'manual')
                        {
                            $transaction_started_date = !empty($arm_start_plan_date) ? date('Y-m-d 00:00:00', $arm_start_plan_date): current_time( 'mysql'); //phpcs:ignore
                        }
                        
                        if(!empty($canceled_date))
                        {
                            $get_last_transaction_sql = $wpdb->prepare("SELECT * FROM ".$ARMemberLite->tbl_arm_payment_log." WHERE arm_user_id=%d AND arm_plan_id=%d AND arm_created_date BETWEEN %s AND %s ORDER BY arm_log_id DESC",$user_id,$rc->arm_item_id,$transaction_started_date,$canceled_date); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name
                        }
                        else
                        {
                            if(!empty($user_plan_detail['arm_trial_start']))
                            {
                                $transaction_started_date = date('Y-m-d H:i:s', ($user_plan_detail['arm_trial_start'] - 120)); //phpcs:ignore
                            }
                            $get_last_transaction_sql = $wpdb->prepare("SELECT * FROM ".$ARMemberLite->tbl_arm_payment_log." WHERE arm_user_id=%d AND arm_plan_id=%d AND arm_created_date >= %s ORDER BY arm_log_id DESC",$user_id,$rc->arm_item_id,$transaction_started_date); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name
                        }
                        
                        $get_transaction_sql = $wpdb->get_results($get_last_transaction_sql,ARRAY_A); //phpcs:ignore --Reason get_last_transaction_sql is a query
                        $transaction_count = 0;
                        $payment_row = $payment_gateway_text = $arm_payment_gateways->arm_gateway_name_by_key($payment_gateway);
                        $payment_types = '';
                        $class = '';                   
                        if($payment_gateway != 'manual')
                        {
                            $payment_types = ($payment_type != 'auto_debit_subscription') ? esc_html__('Semi Automatic','armember-membership') : esc_html__('Auto Debit','armember-membership')  ;
                            $class = ($payment_type != 'auto_debit_subscription') ? 'arm_semi_auto' : 'arm_auto';
                            $payment_row = $payment_gateway_text." <br/><span class='arm_payment_types ".esc_attr($class)."'>".$payment_types."</span>";
                        }
                        if(!empty($get_transaction_sql))
                        {
                            $total_trans = count($get_transaction_sql);
                            
                            if($payment_gateway != 'manual')
                            {
                                $response_data[$ai][7] = $payment_row;                    
                            }
                            else
                            {
                                $response_data[$ai][7] = $payment_gateway_text;  
                            }
                        
                        }
                        else
                        {
                            $response_data[$ai][7] = esc_html__('Manual','armember-membership');
                        }
                        $gridAction ='';
                        $gridAction .= "<div class='arm_grid_action_btn_container'>";
            
                            $gridAction .= "<a href='javascript:void(0)' data-cancel_activity_type='active'  data-cancel_activity_id='" . esc_attr($activityID) . "' onclick='showConfirmBoxCallbackupcomming(".esc_attr($activityID).");'><img src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_denied.png' class='armhelptip' title='" . esc_attr__('Cancel', 'armember-membership') . "' onmouseover=\"this.src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_denied_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIPLITE_IMAGES_URL . "/grid_denied.png';\" /></a>";//phpcs:ignore
                            $arm_transaction_del_cls = 'arm_activity_delete_btn';

                            $confirmBox  = "<div class='arm_confirm_box arm_confirm_box_".esc_attr($activityID)."' id='arm_upcoming_activity_".esc_attr($activityID)."'>";
                                $confirmBox .= "<div class='arm_confirm_box_body'>";
                                    $confirmBox .= "<div class='arm_confirm_box_arrow'></div>";
                                        $confirmBox .= "<div class='arm_confirm_box_text'>".esc_html__("Are you sure you want to cancel this subscription?", 'armember-membership')."</div>";
                                            $confirmBox .= "<div class='arm_confirm_box_btn_container'>";
                                                $confirmBox .= "<button type='button' class='arm_confirm_box_btn armok ".esc_attr($arm_transaction_del_cls).
                                                "' data-item_id='".esc_attr($activityID)."' data-type=''>" . esc_html('Delete','armember-membership') . '</button>';
                                                $confirmBox .= "<button type='button' class='arm_confirm_box_btn armcancel' onclick='hideConfirmBoxCallback();'>" . esc_html__('Cancel','armember-membership') . '</button>';
                                        $confirmBox .= '</div>';
                                    $confirmBox .= '</div>';
                                $confirmBox .= '</div>';
                            $confirmBox .= '</div>';

                            $gridAction .= $confirmBox;

                        $gridAction .= "</div>";
                        $response_data[$ai][8] = $gridAction;
                        $ai++;
                    }
                    // exit;
                }
            }
            $sEcho = isset($_REQUEST['sEcho']) ? intval($_REQUEST['sEcho']) : intval(10); //phpcs:ignore
            $response = array(
                'sColumns' => implode(',', $grid_columns),
                'sEcho' => $sEcho,
                'iTotalRecords' => $before_filter, // Before Filtered Records
                'iTotalDisplayRecords' => $after_filter, // After Filter Records
                'aaData' => $response_data,
            );
            echo wp_json_encode($response);
            die();
        
        }
        function get_return_status_data($user_id,$plan_id,$user_plan_detail,$start_plan_date)
        {
            global $wp,$wpdb,$ARMemberLite;
            $end_date = '';
            
            $suspended_plan_detail = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
            $active_plan_detail = get_user_meta($user_id, 'arm_user_plan_ids', true);
            if(!empty($user_plan_detail['arm_next_due_payment']))
            {
                $end_date = $user_plan_detail['arm_next_due_payment'];
            }
            else
            {
                $end_date = !empty($user_plan_detail['arm_expire_plan']) ? $user_plan_detail['arm_expire_plan'] : '';
            }
            $sql_act = $wpdb->prepare('SELECT arm_action,arm_content,arm_date_recorded FROM '.$ARMemberLite->tbl_arm_activity.' WHERE arm_user_id=%d AND arm_item_id = %d AND (arm_action=%s OR arm_action=%s)',$user_id,$plan_id,"cancel_subscription","eot"); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_activity is a table name
            $get_activity_status = $wpdb->get_results($sql_act); //phpcs:ignore --Reason $sql_act is a query 
            $retun_data = array();
            if(!empty($get_activity_status))
            {
                
                foreach($get_activity_status as $ract)
                {
                    $get_cancel_eot_activity_data = maybe_unserialize($ract->arm_content);
                    $plan_started_date = $get_cancel_eot_activity_data['start'];
                    
                    if( $start_plan_date == $plan_started_date)
                    {
                        if($ract->arm_action == 'cancel_subscription')
                        {
                            
                            $retun_data = array('status'=>'canceled','canceled_date'=>$ract->arm_date_recorded);
                            break;
                        }
                        else if($ract->arm_action == 'eot')
                        {
                            $retun_data = array('status'=>'expired','canceled_date'=>$ract->arm_date_recorded);
                            break;
                        }
                    }
                    else
                    {
                        if(!empty($active_plan_detail) && in_array($plan_id,$active_plan_detail))
                        {
                            $retun_data = array('status'=>'active','canceled_date'=>'');
                            break;
                        }
                        else {
                            $retun_data = array('status'=>'','canceled_date'=>'');
                        }
                    }
                    
                }
            }
            else
            {
                if(!empty($suspended_plan_detail) && in_array($plan_id,$suspended_plan_detail))
                {
                    $retun_data = array('status'=>'suspended','canceled_date'=>'');
                }
                else
                {
                    if(!empty($active_plan_detail) && in_array($plan_id,$active_plan_detail))
                    {
                        $retun_data = array('status'=>'active','canceled_date'=>'');
                    }
                    else {
                        $retun_data = array('status'=>'','canceled_date'=>'');
                    }

                }

            }
            return $retun_data;
        }
        function arm_cancel_subscription_data()
        {
            global $wp,$wpdb,$ARMemberLite,$arm_subscription_plans,$arm_capabilities_global;
            
            $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_subscriptions'], '1'); //phpcs:ignore --Reason:Verifying nonce

            $activity_id = intval( $_POST['activity_id'] ); //phpcs:ignore

            $sql_act = $wpdb->prepare('SELECT * FROM '.$ARMemberLite->tbl_arm_activity.' WHERE arm_activity_id=%d',$activity_id); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_activity is a table name
            $get_activity_status = $wpdb->get_row( $sql_act, ARRAY_A ); //phpcs:ignore --Reason $sql_act is a query

            
            $response ='';
            if($get_activity_status['arm_action'] == 'new_subscription')
            {
                //check membership plan has selected "DO NOT CANCEL UNTIL PLAN EXPIRES" option
                unset( $get_activity_status['arm_activity_id'] );
                $get_activity_status['arm_action'] = 'cancel_subscription';
                $get_activity_status['arm_date_recorded'] = current_time('mysql');
                $user_id = $get_activity_status['arm_user_id'];

                $plan_id = $get_activity_status['arm_item_id'];

                $update = $arm_subscription_plans->arm_ajax_stop_user_subscription($user_id,$plan_id);
                if($update['type']=='success')
                {
                    $response = array('type' => 'success', 'message' => esc_html__('Subscription plan has been canceled successfully', 'armember-membership'));
                }
                else
                {
                    $response = array('type' => 'error', 'message' => esc_html__('Something went wrong please try again', 'armember-membership'));
                }
            }
            echo wp_json_encode($response);
            die;
        }
    }
}
global $arm_subscription_class;
$arm_subscription_class = new ARM_subsctriptions_Lite();