<?php 

if ( ! class_exists( 'ARM_subscription_plans_Lite' ) ) {

	class ARM_subscription_plans_Lite {

		function __construct() {
			global $wpdb, $ARMemberLite, $arm_global_settings;
			add_action( 'wp_ajax_arm_delete_single_plan', array( $this, 'arm_delete_single_plan' ) );
			add_action( 'wp_ajax_arm_stop_user_subscription', array( $this, 'arm_ajax_stop_user_subscription' ) );
			add_action( 'wp_ajax_arm_cancel_membership', array( $this, 'arm_ajax_stop_user_subscription' ) );
			add_action( 'wp_ajax_arm_save_subscription_plans', array( $this, 'arm_save_subscription_plans_func' ) );
			/* Hook for update user's last subscriptions */
			add_action( 'arm_before_update_user_subscription', array( $this, 'arm_before_update_user_subscription_action' ), 10, 2 );
			add_action( 'wp_ajax_arm_update_plans_status', array( $this, 'arm_update_plans_status' ) );

			add_action( 'wp_ajax_arm_membership_history_paging_action', array( $this, 'arm_membership_history_paging_action' ) );
			/* Post Meta Box Functions */
			add_action( 'add_meta_boxes', array( $this, 'arm_add_meta_boxes_func' ) );

			add_action( 'arm_apply_plan_to_member', array( $this, 'arm_apply_plan_to_member_function' ), 10, 2 );

			// Subscription Plan Interations
			add_filter( 'update_user_metadata', array( $this, 'arm_update_subscription_plan_data' ), 10, 4 );
			add_filter( 'delete_user_metadata', array( $this, 'arm_delete_subscription_plan_data' ), 10, 5 );

			add_action("wp_ajax_arm_member_edit_membership_plan_detail",array($this,'arm_member_edit_membership_plan_detail_func'));

            add_action("wp_ajax_arm_get_subscription_plan_details",array($this,"arm_get_membership_plan_details_func"));

		}

		function arm_get_membership_plan_details_func(){
            global $wp, $wpdb, $arm_slugs, $ARMemberLite, $arm_global_settings, $arm_access_rules, $arm_stripe, $arm_capabilities_global, $ARMemberLiteAllowedHTMLTagsArray,$arm_pay_per_post_feature;           

            $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_plans'], '1',1); //phpcs:ignore 

            // $form_result = $arm_subscription_plans->arm_get_all_subscription_plans();

            $arm_plan_sql = $wpdb->prepare("SELECT * FROM `" . $ARMemberLite->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_post_id`=%d AND `arm_subscription_plan_gift_status`=%d",0,0,0); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_subscription_plans is a table name

            $results = $wpdb->get_results($arm_plan_sql); //phpcs:ignore --Reason $sql is a prepareed query

            $before_filter = count($results);

            $sSearch = isset($_REQUEST['sSearch']) ? trim(sanitize_text_field($_REQUEST['sSearch'])) : '';

            if(!empty($sSearch))
            {
                $where_bgs = $wpdb->prepare("`arm_subscription_plan_name` LIKE %s AND",'%'.$sSearch.'%');
                $arm_plan_sql = $wpdb->prepare("SELECT * FROM `" . $ARMemberLite->tbl_arm_subscription_plans . "` WHERE ".$where_bgs." `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_post_id`=%d AND `arm_subscription_plan_gift_status`=%d",0,0,0);
            }

            $after_filter_data = $wpdb->get_results($arm_plan_sql);//phpcs:ignore --Reason 
            $after_filter = count($after_filter_data);

            $sorting_ord = isset($_REQUEST['sSortDir_0']) ? sanitize_text_field($_REQUEST['sSortDir_0']) : 'DESC';
            $sorting_ord = strtolower($sorting_ord);
            $sorting_col = (isset($_REQUEST['iSortCol_0']) && $_REQUEST['iSortCol_0'] > 0) ? intval($_REQUEST['iSortCol_0']) : 0;
            if ( ( 'asc'!=$sorting_ord && 'desc'!=$sorting_ord ) ) {
                $sorting_ord = 'DESC';
            }

            if(intval($sorting_col) == 1) {
                $orderby = "arm_subscription_plan_name";
            }
            else if(intval($sorting_col) == 2){
                $orderby = "arm_subscription_plan_type";
            }
            else if(intval($sorting_col) == 4){
                $orderby = "arm_subscription_plan_role";
            }
            else
            {
                $orderby = "arm_subscription_plan_id";
            }

            $order_by_qry = " ORDER BY " . $orderby . " " . $sorting_ord;

            $plan_offset = isset($_REQUEST['iDisplayStart']) ? intval($_REQUEST['iDisplayStart']) : 0;
            $plan_number = isset($_REQUEST['iDisplayLength']) ? intval($_REQUEST['iDisplayLength']) : 10;

            $phlimit = "LIMIT {$plan_offset},{$plan_number}";

            $form_result = $wpdb->get_results($arm_plan_sql.' '.$order_by_qry.' '.$phlimit,ARRAY_A);
            $grid_data = array();
            $ai = 0;
            if (!empty($form_result)) {
                $arm_is_multisite = is_multisite();
                $arm_current_blog_id = !empty($arm_is_multisite) ? get_current_blog_id() : 0;
                            
                $arm_user_query = $wpdb->get_results($wpdb->prepare("SELECT um.user_id, um.meta_value FROM $wpdb->users  u LEFT JOIN $wpdb->usermeta um ON um.user_id = u.ID WHERE um.meta_key = %s",'arm_user_plan_ids'));
                $arm_user_array = array(); 
                if(!empty($arm_user_query)){
                    foreach($arm_user_query as $arm_user){
                        $user_meta=get_userdata($arm_user->user_id);
                        $user_roles= !empty($user_meta->roles) ? $user_meta->roles : array();
                        if(!in_array('administrator', $user_roles)) {

                            if ($arm_is_multisite) {
                                if(is_user_member_of_blog($arm_user->user_id, $arm_current_blog_id))
                                {
                                    $arm_user_array[$arm_user->user_id] = maybe_unserialize($arm_user->meta_value);
                                }
                                else
                                {
                                    continue;
                                }
                            }
                            else {
                                $arm_user_array[$arm_user->user_id] = maybe_unserialize($arm_user->meta_value);
                            }
                        }
                    }
                }
                                        
                foreach($form_result as $planData) {
                    $planObj = new ARM_Plan_Lite();
                    $planObj->init((object) $planData);
                    $planID = $planData['arm_subscription_plan_id'];
                    $total_users = 0;
                    if(!empty($arm_user_array)){
                        foreach($arm_user_array as $arm_user_id => $arm_user_plans){
                            if(!empty($arm_user_plans) && in_array($planID, $arm_user_plans)){
                                $total_users++;
                            }
                        }
                    }
                    
                    $grid_data[$ai][0] = esc_attr($planID);
                    $grid_data[$ai][1] = (stripslashes($planObj->name)); //phpcs:ignore

                    if( $planObj->is_recurring() && isset($planObj->options['payment_cycles']) && count($planObj->options['payment_cycles']) > 1 ) {
                        $grid_data[$ai][2] = '<span class="arm_item_status_text active">' . esc_html__('Paid', 'armember-membership') . '</span>
                        <a href="javascript:void(0);" class="arm_margin_left_12" onclick="arm_plan_cycle('. esc_attr($planID) .')">' . esc_html__('Multiple Cycle', 'armember-membership') . '</a>';
                    } else {
                        $grid_data[$ai][2] = $planObj->plan_text(true); //phpcs:ignore
                    }
                            
                    $planMembers = $total_users;
                    if ($planMembers > 0) {
                        $membersLink = admin_url('admin.php?page=' . $arm_slugs->manage_members . '&plan_id=' . $planID);
                        $grid_data[$ai][3] = "<a href='".esc_url($membersLink)."'>".esc_html($planMembers)."</a>";
                    } else {
                        $grid_data[$ai][3] = esc_html($planMembers);
                    }
                    $planRole = $planObj->plan_role;
                    if (!empty($planRole)) {
                        $grid_data[$ai][4] = $planRole;
                    } else {
                        $grid_data[$ai][4] = '-';
                    }		                       
                    $gridAction = "<div class='arm_grid_action_btn_container'>";
                    if (current_user_can('arm_manage_plans')) {
                        $gridAction .= "<a href='javascript:void(0)'  class='arm_edit_plan_data' data-plan_id='".$planID."'><img src='".MEMBERSHIPLITE_IMAGES_URL."/grid_edit.svg' onmouseover=\"this.src='".MEMBERSHIPLITE_IMAGES_URL."/grid_edit_hover.svg';\" class='armhelptip' title='".esc_html__('Edit Plan','armember-membership')."' onmouseout=\"this.src='".MEMBERSHIPLITE_IMAGES_URL."/grid_edit.svg';\" /></a>";
                        $gridAction .= "<a href='javascript:void(0)' onclick='showConfirmBoxCallback({$planID});'><img src='".MEMBERSHIPLITE_IMAGES_URL."/grid_delete.svg' class='armhelptip' title='".esc_html__('Delete','armember-membership')."' onmouseover=\"this.src='".MEMBERSHIPLITE_IMAGES_URL."/grid_delete_hover.svg';\" onmouseout=\"this.src='".MEMBERSHIPLITE_IMAGES_URL."/grid_delete.svg';\" /></a>";
                        if (empty($planMembers) || $planMembers == 0) {
                            $gridAction .= $arm_global_settings->arm_get_confirm_box($planID, esc_html__("Are you sure you want to delete this plan?", 'armember-membership'), 'arm_plan_delete_btn','', esc_html__('Delete', 'armember-membership'), esc_attr__('Cancel', 'armember-membership'), esc_attr__('Delete', 'armember-membership'));
                        } else {
                            $gridAction .= $arm_global_settings->arm_get_confirm_box($planID, esc_html__("This plan has one or more subscribers. So this plan can not be deleted.", 'armember-membership'), 'arm_plan_delete_btn_not arm_hide','','',esc_html__("Close",'armember-membership'),esc_attr__('Delete', 'armember-membership'));
                        }
                    }
                    $gridAction .= "</div>";
                    $grid_data[$ai][5] = $gridAction; //phpcs:ignore
                    $ai++;
                }//End Foreach
            }
            $sEcho = isset($_REQUEST['sEcho']) ? intval($_REQUEST['sEcho']) : intval(10);
            $columns = esc_html__('Plan ID', 'armember-membership') . ',' . esc_html__('Plan Name', 'armember-membership') . ',' . esc_html__('Plan Type', 'armember-membership') . ',' . esc_html__('Members', 'armember-membership') . ',' . esc_html__('Wp Role', 'armember-membership') . ',';
            $response = array(
                'sColumns' => $columns,
                'sEcho' => $sEcho,
                'iTotalRecords' => $before_filter, // Before Filtered Records
                'iTotalDisplayRecords' => $after_filter, // After Filter Records
                'aaData' => $grid_data,
            );
            echo json_encode($response);
            die();		
        }

        function arm_member_edit_membership_plan_detail_func()
        {
            
            global $wp, $wpdb, $arm_slugs, $ARMemberLite, $arm_global_settings, $arm_access_rules, $arm_stripe, $arm_capabilities_global, $ARMemberLiteAllowedHTMLTagsArray,$arm_pay_per_post_feature,$arm_ajax_pattern_start,$arm_ajax_pattern_end;

            $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_plans'], '1',1); //phpcs:ignore 

            $arm_plan_id = $_REQUEST['id'];

            $response = array("status"=>"error","response"=>esc_html__("Something Went Wrong! Please try again","armember-membership"));

            if(!empty($arm_plan_id) && (!empty($_REQUEST['arm_action']) && $_REQUEST['arm_action'] == 'edit_plan'))
            {
                $arm_plan_sql = $wpdb->prepare("SELECT * FROM `" . $ARMemberLite->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_id`=%d AND `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_post_id`=%d AND `arm_subscription_plan_gift_status`=%d",$arm_plan_id,0,0,0); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_subscription_plans is a table name

                $arm_is_multisite = is_multisite();
                $arm_current_blog_id = !empty($arm_is_multisite) ? get_current_blog_id() : 0;
                            
                $arm_user_query = $wpdb->get_results($wpdb->prepare("SELECT um.user_id, um.meta_value FROM $wpdb->users  u LEFT JOIN $wpdb->usermeta um ON um.user_id = u.ID WHERE um.meta_key = %s",'arm_user_plan_ids'));
                $arm_user_array = array(); 
                if(!empty($arm_user_query)){
                    foreach($arm_user_query as $arm_user){
                        $user_meta=get_userdata($arm_user->user_id);
                        $user_roles= !empty($user_meta->roles) ? $user_meta->roles : array();
                        if(!in_array('administrator', $user_roles)) {

                            if ($arm_is_multisite) {
                                if(is_user_member_of_blog($arm_user->user_id, $arm_current_blog_id))
                                {
                                    $arm_user_array[$arm_user->user_id] = maybe_unserialize($arm_user->meta_value);
                                }
                                else
                                {
                                    continue;
                                }
                            }
                            else {
                                $arm_user_array[$arm_user->user_id] = maybe_unserialize($arm_user->meta_value);
                            }
                        }
                    }
                }
				
				$total_users = 0;
				if(!empty($arm_user_array)){
					foreach($arm_user_array as $arm_user_id => $arm_user_plans){
						if(!empty($arm_user_plans) && in_array($arm_plan_id, $arm_user_plans)){
							$total_users++;
						}
					}
				}

                $arm_plan_result_data = $wpdb->get_row($arm_plan_sql,ARRAY_A);
                $response_data['arm_plan_id'] = $arm_plan_id;
                $response_data['arm_plan_title'] = esc_html__('Edit Membership plan',"armember-membership");
                $response_data['arm_plan_name'] = !empty($arm_plan_result_data['arm_subscription_plan_name']) ?  stripslashes($arm_plan_result_data['arm_subscription_plan_name']) : '';
                $response_data['arm_plan_desc'] = !empty($arm_plan_result_data['arm_subscription_plan_description']) ? stripslashes($arm_plan_result_data['arm_subscription_plan_description']) : '';
                $response_data['arm_plan_type'] = $arm_plan_result_data['arm_subscription_plan_type'];
                $response_data['arm_plan_options'] = maybe_unserialize($arm_plan_result_data['arm_subscription_plan_options']);
                $plan_expire_date = !empty($response_data['arm_plan_options']['expiry_date']) ? $response_data['arm_plan_options']['expiry_date'] : '';

                $arm_common_date_format = 'm/d/Y';
                
                $plan_expire_date = date($arm_common_date_format, strtotime($plan_expire_date));
                $response_data['arm_plan_options']['expiry_date'] = $plan_expire_date;

                $response_data['arm_plan_amount'] = $arm_plan_result_data['arm_subscription_plan_amount'];
                $response_data['arm_plan_status'] = $arm_plan_result_data['arm_subscription_plan_status'];
                $roles = get_editable_roles();
                $arm_plan_selected_role = '';
                if (!empty($roles)) {
                    unset($roles['administrator']);
                    foreach ($roles as $key => $role) {
                        if($key == $arm_plan_result_data['arm_subscription_plan_role'])
                        {
                            $arm_plan_selected_role = $role['name'];
                        }
                    }
                }
                $response_data['arm_plan_role'] = $arm_plan_selected_role;
                $response_data['is_already_used'] = 0;
                /* $plan = new ARM_Plan($arm_plan_id);
                $plan_options = $plan;
                print_r($plan_options);
                exit; */
                $response_data['limit'] = (isset($response_data['arm_plan_options']["limit"])) ? $response_data['arm_plan_options']["limit"] : 0; 
                if($total_users > 0)
                {
                    $response_data['is_already_used'] = 1;
                }

                $response_data = apply_filters('arm_membership_plan_field_section',$response_data);

                $response = array("status"=>"success","response"=>$response_data);
            }

            echo arm_pattern_json_encode($response);
            die();

        }



		function arm_save_subscription_plans_func() {
			global $wp, $wpdb, $arm_slugs, $ARMemberLite, $arm_global_settings, $arm_access_rules, $arm_capabilities_global, $ARMemberLiteAllowedHTMLTagsArray,$arm_ajax_pattern_start,$arm_ajax_pattern_end;
			$redirect_to = admin_url( 'admin.php?page=' . $arm_slugs->manage_plans );

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_plans'], '1' ); //phpcs:ignore --Reason:Verifying nonce

			$response = array("status"=>"error","message"=>esc_html__("Something went wrong!Please try again",'armember-membership'));
			//$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend' ), $_POST );//phpcs:ignore
			$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST ); //phpcs:ignore

			$posted_data['plan_description'] = ( ! empty( $_POST['plan_description'] ) ) ?  wp_kses($_POST['plan_description'], $ARMemberLiteAllowedHTMLTagsArray) : ''; //phpcs:ignore

			if ( isset( $posted_data ) && ! empty( $posted_data ) && in_array( $posted_data['arm_action'], array( 'add', 'update' ) ) ) {

				$plan_name        = ( ! empty( $posted_data['plan_name'] ) ) ? sanitize_text_field( $posted_data['plan_name'] ) : esc_html__( 'Untitled Plan', 'armember-membership' );
				$plan_description = ( ! empty( $posted_data['plan_description'] ) ) ?  wp_kses($posted_data['plan_description'], $ARMemberLiteAllowedHTMLTagsArray) : '';
				$plan_status      = ( ! empty( $posted_data['plan_status'] ) && $posted_data['plan_status'] != 0 ) ? 1 : 0;
				$plan_role        = ( ! empty( $posted_data['plan_role'] ) ) ? sanitize_text_field( $posted_data['plan_role'] ) : get_option( 'default_role' );
				$plan_type        = ( ! empty( $posted_data['arm_subscription_plan_type'] ) ) ? sanitize_text_field( $posted_data['arm_subscription_plan_type'] ) : 'free';

				$payment_type = $plan_amount = '';
				$plan_options = $plan_payment_gateways = array();
				$plan_options = ( ! empty( $posted_data['arm_subscription_plan_options'] ) ) ? $posted_data['arm_subscription_plan_options'] : array();
				if ( $plan_type != 'free' ) {
					$plan_options['access_type']  = ( ! empty( $plan_options['access_type'] ) ) ? $plan_options['access_type'] : 'lifetime';
					$plan_options['payment_type'] = ( ! empty( $plan_options['payment_type'] ) ) ? $plan_options['payment_type'] : 'one_time';

					if ( $plan_type == 'paid_finite' ) {
						$plan_options['expiry_type'] = ( isset( $plan_options['expiry_type'] ) && ! empty( $plan_options['expiry_type'] ) ) ? $plan_options['expiry_type'] : 'joined_date_expiry';
						$expiry_date                 = ! empty( $plan_options['expiry_date'] ) ? $plan_options['expiry_date'] : '';
						$plan_options['expiry_date'] = ( $expiry_date != '' ) ? date( 'Y-m-d 23:59:59', strtotime( $expiry_date ) ) : ''; //phpcs:ignore
					} else {
						unset( $plan_options['expiry_type'] );
						unset( $plan_options['expiry_date'] );
						unset( $plan_options['eopa'] );
					}

					if ( $plan_type == 'paid_infinite' ) {
						unset( $plan_options['grace_period'] );
						unset( $plan_options['eot'] );
					}

					if ( $plan_options['payment_type'] == 'one_time' ) {
						$plan_options['trial'] = array();
					}
					$plan_amount = ( ! empty( $posted_data['arm_subscription_plan_amount'] ) && $posted_data['arm_subscription_plan_amount'] != 0 ) ? $posted_data['arm_subscription_plan_amount'] : 0;

					if ( $plan_type == 'recurring' ) {
						$manual_billing_start = ( ! empty( $plan_options['recurring'] ) ) ? $plan_options['recurring']['manual_billing_start'] : 'transaction_day';

						if ( isset( $plan_options['trial'] ) && isset( $plan_options['trial']['is_trial_period'] ) && $plan_options['trial']['is_trial_period'] == '1' ) {
							$plan_options['trial'] = ( ! empty( $plan_options['trial'] ) ) ? $plan_options['trial'] : array();
						}
						$plan_options['payment_cycles'] = ( isset( $plan_options['payment_cycles'] ) && ! empty( $plan_options['payment_cycles'] ) ) ? array_values( $plan_options['payment_cycles'] ) : array();

						$plan_amount         = ( ! empty( $plan_options['payment_cycles'] ) ) ? $plan_options['payment_cycles'][0]['cycle_amount'] : 0;
						$first_payment_cycle = $plan_options['payment_cycles'][0];

						if( isset($plan_options['payment_cycles'][0]['cycle_label']) )
						{
							$plan_options['payment_cycles'][0]['cycle_label'] = wp_kses($plan_options['payment_cycles'][0]['cycle_label'], $ARMemberLiteAllowedHTMLTagsArray);

						}

						$arm_billing_type   = $first_payment_cycle['billing_type'];
						$arm_recurring_time = $first_payment_cycle['recurring_time'];
						$arm_billing_cycle  = $first_payment_cycle['billing_cycle'];
						$arm_months         = 1;
						$arm_days           = 1;
						$arm_years          = 1;

						if ( $arm_billing_type == 'D' ) {
							$arm_days = $arm_billing_cycle;
						} elseif ( $arm_billing_type == 'M' ) {
							$arm_months = $arm_billing_cycle;
						} else {
							$arm_years = $arm_billing_cycle;
						}
						$plan_options['recurring'] = array(
							'days'                 => $arm_days,
							'months'               => $arm_months,
							'years'                => $arm_years,
							'type'                 => $arm_billing_type,
							'time'                 => $arm_recurring_time,
							'manual_billing_start' => $manual_billing_start,
						);
					} else {
						unset( $plan_options['payment_cycles'] );
						unset( $plan_options['recurring'] );
						unset( $plan_options['trial'] );
						unset( $plan_options['cancel_action'] );
						unset( $plan_options['cancel_plan_action'] );
						unset( $plan_options['payment_failed_action'] );
					}
				}
				$plan_options['pricetext'] = isset( $posted_data['arm_subscription_plan_options']['pricetext'] ) ? $posted_data['arm_subscription_plan_options']['pricetext'] : esc_html__( 'Free Membership', 'armember-membership' );
				$plan_options              = apply_filters( 'arm_befor_save_field_membership_plan', $plan_options, $posted_data );
				$subscription_plans_data   = array(
					'arm_subscription_plan_name'        => $plan_name,
					'arm_subscription_plan_description' => $plan_description,
					'arm_subscription_plan_status'      => $plan_status,
					'arm_subscription_plan_type'        => $plan_type,
					'arm_subscription_plan_options'     => maybe_serialize( $plan_options ),
					'arm_subscription_plan_amount'      => $plan_amount,
					'arm_subscription_plan_role'        => $plan_role,
				);
				if ( $posted_data['arm_action'] == 'add' ) {
					$subscription_plans_data['arm_subscription_plan_created_date'] = current_time( 'mysql' );
					// Insert Form Fields.
					$wpdb->insert( $ARMemberLite->tbl_arm_subscription_plans, $subscription_plans_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$plan_id = $wpdb->insert_id;
					// Action After Adding Plan
					do_action( 'arm_saved_subscription_plan', $plan_id, $subscription_plans_data );
					$inherit_plan_id = isset( $posted_data['arm_inherit_plan_rules'] ) ? intval( $posted_data['arm_inherit_plan_rules'] ) : 0;
					if ( ! empty( $plan_id ) && $plan_id != 0 && ! empty( $inherit_plan_id ) && $inherit_plan_id != 0 ) {
						$arm_access_rules->arm_inherit_plan_rules( $plan_id, $inherit_plan_id );
					}
					$is_recurring = 0;
					$arm_plan_upd_drg_li = '';
					if($plan_amount > 0)
					{

						if($plan_type == 'recurring')
						{
							$is_recurring = 1;
						}
						$arm_plan_upd_drg_li = "<option value='".$plan_id."' data-recurring='".$is_recurring."'>".$plan_name."</option>";
					}
					$response = array("status"=>"success","message"=>esc_html__("Plan has been added successfully.",'armember-membership'),'arm_up_dg_plans_opt' => $arm_plan_upd_drg_li);

				} elseif ( $posted_data['arm_action'] == 'update' && ! empty( $posted_data['id'] ) && $posted_data['id'] != 0 ) {
					$update_plan_id = intval( $posted_data['id'] );
					$field_update   = $wpdb->update( $ARMemberLite->tbl_arm_subscription_plans, $subscription_plans_data, array( 'arm_subscription_plan_id' => $update_plan_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					// Action After Updating Plan
					$arm_plan_upd_drg_li = "";
					do_action( 'arm_saved_subscription_plan', $update_plan_id, $subscription_plans_data );
					$response = array("status"=>"success","message"=>esc_html__("Plan has been updated successfully.",'armember-membership'),'arm_up_dg_plans_opt' => '');
				}
				do_action( 'arm_save_subscription_plans', $posted_data ); //phpcs:ignore
			}
			echo arm_pattern_json_encode($response);
			die;
		}

		function arm_update_plans_status( $posted_data = array() ) {
			global $wpdb, $ARMemberLite, $arm_global_settings, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_plans'], '1' );//phpcs:ignore --Reason:Verifying nonce

			$response = array(
				'type' => 'error',
				'msg'  => esc_html__( 'Sorry, Something went wrong. Please try again.', 'armember-membership' ),
			);
			if ( ! empty( $_POST['plan_id'] ) && $_POST['plan_id'] != 0 ) { //phpcs:ignore
				$plan_id         = intval($_POST['plan_id']); //phpcs:ignore
				$arm_plan_status = ( ! empty( $_POST['plan_status'] ) ) ? intval($_POST['plan_status']) : 0; //phpcs:ignore
				$update_temp     = $wpdb->update( $ARMemberLite->tbl_arm_subscription_plans, array( 'arm_subscription_plan_status' => $arm_plan_status ), array( 'arm_subscription_plan_id' => $plan_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$response        = array(
					'type' => 'success',
					'msg'  => esc_html__( 'Plan has been updated successfully.', 'armember-membership' ),
				);
			}
			echo wp_json_encode( $response );
			die();
		}

		function arm_get_subscription_plan( $plan_id = 0, $columns = 'all' ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings;
			$selectColumns = '*';
			if ( ! empty( $columns ) ) {
				if ( $columns != 'all' && $columns != '*' ) {
					$selectColumns = $columns;
				}
			}
			if ( is_numeric( $plan_id ) && $plan_id != 0 ) {
				$plan_data = $wpdb->get_row( $wpdb->prepare("SELECT {$selectColumns}, `arm_subscription_plan_id` FROM `" . $ARMemberLite->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_id`=%d AND `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_gift_status`=%d LIMIT 1",$plan_id,0,0), ARRAY_A );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_subscription_plans is a table name
				if ( ! empty( $plan_data ) ) {
					if ( isset( $plan_data['arm_subscription_plan_name'] ) ) {
						$plan_data['arm_subscription_plan_name'] = stripslashes( $plan_data['arm_subscription_plan_name'] );
					}
					if ( isset( $plan_data['arm_subscription_plan_description'] ) ) {
						$plan_data['arm_subscription_plan_description'] = stripslashes( $plan_data['arm_subscription_plan_description'] );
					}
					if ( isset( $plan_data['arm_subscription_plan_options'] ) ) {
						$plan_data['arm_subscription_plan_options'] = maybe_unserialize( $plan_data['arm_subscription_plan_options'] );
					}
				}
				return $plan_data;
			} else {
				return false;
			}
		}

		function arm_get_plan_id_by_name( $name = '' ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings;
			$plan_id = 0;
			if ( ! empty( $name ) ) {
				$plan_id = $wpdb->get_var( $wpdb->prepare('SELECT `arm_subscription_plan_id` FROM `' . $ARMemberLite->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_name` LIKE %s AND `arm_subscription_plan_gift_status`=%d",'%' . $wpdb->esc_like( $name ) . '%',0) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_subscription_plans is a table name
				if ( empty( $plan_id ) ) {
					$plan_id = 0;
				}
			}
			return $plan_id;
		}

		function arm_get_plan_role_by_id( $plan_ids = array() ) {
			global $wp, $wpdb, $ARMemberLite;
			$plan_role = array();
			if ( ! empty( $plan_ids ) ) {
				$where ='WHERE ';
				$admin_placeholders = ' arm_subscription_plan_id IN (';
				$admin_placeholders .= rtrim( str_repeat( '%s,', count( $plan_ids ) ), ',' );
				$admin_placeholders .= ')';
				array_unshift( $plan_ids, $admin_placeholders );
				
				$where .= call_user_func_array(array( $wpdb, 'prepare' ), $plan_ids );

				$plan_role = $wpdb->get_results( 'SELECT `arm_subscription_plan_role`, `arm_subscription_plan_id` FROM `' . $ARMemberLite->tbl_arm_subscription_plans . '` '.$where , ARRAY_A );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_member_templates is a table name
			}
			return $plan_role;
		}

		function arm_get_plan_name_by_id_from_array( $skipDeleted = false ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings;
			$plan_name = '';
			$whereSql  = '';
			if ( $skipDeleted ) {
				$whereSql = $wpdb->prepare(" WHERE `arm_subscription_plan_is_delete`=%d",0);
			}
			$plan_array         = $wpdb->get_results("SELECT `arm_subscription_plan_id`, `arm_subscription_plan_name` FROM `".$ARMemberLite->tbl_arm_subscription_plans."` ".$whereSql );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_subscription_plans is a table name
			$plan_id_name_array = array();
			if ( ! empty( $plan_array ) ) {

				foreach ( $plan_array as $plan_arr ) {
					$plan_id_name_array[ $plan_arr->arm_subscription_plan_id ] = $plan_arr->arm_subscription_plan_name;
				}
			}

			return $plan_id_name_array;
		}

		function arm_get_plan_name_by_id( $id = 0, $skipDeleted = false ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings;
			$plan_name = '';
			if ( ! empty( $id ) && $id != 0 ) {
				$whereSql = $wpdb->prepare("WHERE `arm_subscription_plan_id` = %d",$id);
				if ( $skipDeleted ) {
					$whereSql .= $wpdb->prepare(" AND `arm_subscription_plan_is_delete`=%d",0);
				}

				$plan_name = $wpdb->get_var( 'SELECT `arm_subscription_plan_name` FROM `' . $ARMemberLite->tbl_arm_subscription_plans . "` ".$whereSql);//phpcs:ignore --Reason $ARMemberLite->tbl_arm_subscription_plans is a table name
				if ( empty( $plan_name ) ) {
					$plan_name = '';
				}
			}
			return stripslashes( $plan_name );
		}

		function arm_get_comma_plan_names_by_ids( $ids = array() ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings;
			$plan_names = '';
			if ( ! empty( $ids ) ) {
				// from here call function arm_get_plan_name_by_id and query for each plan so, make it change during query monitor
				$plan_ids = $ids ;
				$admin_placeholders = ' arm_subscription_plan_id IN (';
				$admin_placeholders .= rtrim( str_repeat( '%s,', count( $plan_ids ) ), ',' );
				$admin_placeholders .= ')';
				// $admin_users       = implode( ',', $admin_users );

				array_unshift( $plan_ids, $admin_placeholders );

					
				$admin_user_where = call_user_func_array(array( $wpdb, 'prepare' ), $plan_ids );
				$plans    = $wpdb->get_col( 'SELECT `arm_subscription_plan_name` FROM `' . $ARMemberLite->tbl_arm_subscription_plans . "` WHERE ".$admin_user_where." ORDER BY `arm_subscription_plan_id` DESC" );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_subscription_plans is a table name

				$plan_names = @implode( ', ', $plans );
			}
			return $plan_names;
		}

		/**
		 * Get all subscritpion plans
		 *
		 * @return array of plans, False if there is no plan(s).
		 */
		function arm_get_plans_data( $fields = 'all' ) {
			global $wp, $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings;
			$selectFields = '*';
			if ( ! empty( $fields ) ) {
				if ( $fields != 'all' && $fields != '*' ) {
					$selectFields = $fields;
				}
			}
			$results = $wpdb->get_results( $wpdb->prepare("SELECT {$selectFields}, `arm_subscription_plan_id` FROM `" . $ARMemberLite->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_is_delete`=%d ORDER BY `arm_subscription_plan_id` DESC",0), ARRAY_A );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_subscription_plans is a table name
			if ( ! empty( $results ) ) {
				$plans_data = array();
				foreach ( $results as $sp ) {
					$plnID = $sp['arm_subscription_plan_id'];
					if ( isset( $sp['arm_subscription_plan_name'] ) ) {
						$sp['arm_subscription_plan_name'] = stripslashes( $sp['arm_subscription_plan_name'] );
					}
					if ( isset( $sp['arm_subscription_plan_description'] ) ) {
						$sp['arm_subscription_plan_description'] = stripslashes( $sp['arm_subscription_plan_description'] );
					}
					if ( isset( $sp['arm_subscription_plan_options'] ) ) {
						$sp['arm_subscription_plan_options'] = maybe_unserialize( $sp['arm_subscription_plan_options'] );
					}
					$plans_data[ $plnID ] = $sp;
				}
				return $plans_data;
			} else {
				return false;
			}
		}

		function arm_get_all_free_plans( $fields = 'all', $object_type = ARRAY_A ) {
			global $wp, $wpdb, $ARMemberLite;
			$selectFields = '*';
			if ( ! empty( $fields ) ) {
				if ( $fields != 'all' && $fields != '*' ) {
					$selectFields = $fields;
				}
			}
			$object_type = ! empty( $object_type ) ? $object_type : ARRAY_A;

			$results = $wpdb->get_results( $wpdb->prepare("SELECT {$selectFields}, `arm_subscription_plan_id` FROM `" . $ARMemberLite->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_type` = %s AND `arm_subscription_plan_gift_status`=%d ORDER BY `arm_subscription_plan_id` DESC",0,'free',0), $object_type );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_member_templates is a table name
			if ( ! empty( $results ) ) {
				$plans_data = array();
				if ( ! empty( $results ) ) {
					foreach ( $results as $sp ) {
						if ( $object_type == OBJECT || $object_type == OBJECT_K ) {
							$plnID = $sp->arm_subscription_plan_id;
							if ( isset( $sp->arm_subscription_plan_name ) ) {
								$sp->arm_subscription_plan_name = stripslashes( $sp->arm_subscription_plan_name );
							}
							if ( isset( $sp->arm_subscription_plan_description ) ) {
								$sp->arm_subscription_plan_description = stripslashes( $sp->arm_subscription_plan_description );
							}
							if ( isset( $sp->arm_subscription_plan_options ) ) {
								$sp->arm_subscription_plan_options = maybe_unserialize( $sp->arm_subscription_plan_options );
							}
						} else {
							$plnID = $sp['arm_subscription_plan_id'];
							if ( isset( $sp['arm_subscription_plan_name'] ) ) {
								$sp['arm_subscription_plan_name'] = stripslashes( $sp['arm_subscription_plan_name'] );
							}
							if ( isset( $sp['arm_subscription_plan_description'] ) ) {
								$sp['arm_subscription_plan_description'] = stripslashes( $sp['arm_subscription_plan_description'] );
							}
							if ( isset( $sp['arm_subscription_plan_options'] ) ) {
								$sp['arm_subscription_plan_options'] = maybe_unserialize( $sp['arm_subscription_plan_options'] );
							}
						}
						$plans_data[ $plnID ] = $sp;
					}
				}
				return $plans_data;
			} else {
				return false;
			}
		}

		/**
		 * Get all subscritpion plans
		 *
		 * @return array of plans, False if there is no plan(s).
		 */
		function arm_get_all_subscription_plans( $fields = 'all', $object_type = ARRAY_A, $allow_user_no_plan = false ) {
			global $wp, $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings;
			$selectFields = '*';
			if ( ! empty( $fields ) ) {
				if ( $fields != 'all' && $fields != '*' ) {
					$selectFields = $fields;
				}
			}
			$object_type = ! empty( $object_type ) ? $object_type : ARRAY_A;

			$results = $wpdb->get_results( $wpdb->prepare("SELECT ".$selectFields.", `arm_subscription_plan_id` FROM `" . $ARMemberLite->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_post_id`=%d AND `arm_subscription_plan_gift_status`=%d ORDER BY `arm_subscription_plan_id` DESC",0,0,0), $object_type );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_subscription_plans is a table name
			if ( ! empty( $results ) || $allow_user_no_plan ) {
				$plans_data = array();
				if ( $allow_user_no_plan ) {
					$plnID   = -2;
					$plnName = esc_html__( 'Users Having No Plan', 'armember-membership' );
					if ( $object_type == OBJECT || $object_type == OBJECT_K ) {
						$sp->arm_subscription_plan_id          = $plnID;
						$sp->arm_subscription_plan_name        = $plnName;
						$sp->arm_subscription_plan_description = '';
						$sp->arm_subscription_plan_options     = array();
					} else {
						$sp['arm_subscription_plan_id']          = $plnID;
						$sp['arm_subscription_plan_name']        = $plnName;
						$sp['arm_subscription_plan_description'] = '';
						$sp['arm_subscription_plan_options']     = array();
					}
					$plans_data[ $plnID ] = $sp;
				}
				if ( ! empty( $results ) ) {
					foreach ( $results as $sp ) {
						if ( $object_type == OBJECT || $object_type == OBJECT_K ) {
							$plnID = $sp->arm_subscription_plan_id;
							if ( isset( $sp->arm_subscription_plan_name ) ) {
								$sp->arm_subscription_plan_name = stripslashes( $sp->arm_subscription_plan_name );
							}
							if ( isset( $sp->arm_subscription_plan_description ) ) {
								$sp->arm_subscription_plan_description = stripslashes( $sp->arm_subscription_plan_description );
							}
							if ( isset( $sp->arm_subscription_plan_options ) ) {
								$sp->arm_subscription_plan_options = maybe_unserialize( $sp->arm_subscription_plan_options );
							}
						} else {
							$plnID = $sp['arm_subscription_plan_id'];
							if ( isset( $sp['arm_subscription_plan_name'] ) ) {
								$sp['arm_subscription_plan_name'] = stripslashes( $sp['arm_subscription_plan_name'] );
							}
							if ( isset( $sp['arm_subscription_plan_description'] ) ) {
								$sp['arm_subscription_plan_description'] = stripslashes( $sp['arm_subscription_plan_description'] );
							}
							if ( isset( $sp['arm_subscription_plan_options'] ) ) {
								$sp['arm_subscription_plan_options'] = maybe_unserialize( $sp['arm_subscription_plan_options'] );
							}
						}
						$plans_data[ $plnID ] = $sp;
					}
				}
				return $plans_data;
			} else {
				return false;
			}
		}

		function arm_get_all_active_subscription_plans( $orderby = '', $order = '', $allow_user_no_plan = false ) {
			global $wp, $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings;
			$orderby = ( ! empty( $orderby ) ) ? $orderby : 'arm_subscription_plan_id';
			$order   = ( ! empty( $order ) && $order == 'ASC' ) ? 'ASC' : 'DESC';
			/* Query Monitor Settings */
			if ( isset( $GLOBALS['arm_active_subscription_plan_data'] ) ) {
				$results = $GLOBALS['arm_active_subscription_plan_data'];
			} else {
				$results                                      = $wpdb->get_results( $wpdb->prepare('SELECT * FROM `' . $ARMemberLite->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_status`=%d AND `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_post_id`=%d AND `arm_subscription_plan_gift_status`=%d ORDER BY `" . $orderby . '` ' . $order . '',1,0,0,0), ARRAY_A );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_member_templates is a table name
				$GLOBALS['arm_active_subscription_plan_data'] = $results;
			}
			if ( ! empty( $results ) || $allow_user_no_plan ) {
				$plans_data = array();
				if ( $allow_user_no_plan ) {
					$sp['arm_subscription_plan_id']                = -2;
					$sp['arm_subscription_plan_name']              = esc_html__( 'Users Having No Plan', 'armember-membership' );
					$sp['arm_subscription_plan_description']       = '';
					$sp['arm_subscription_plan_options']           = array();
					$plans_data[ $sp['arm_subscription_plan_id'] ] = $sp;
				}
				if ( ! empty( $results ) ) {
					foreach ( $results as $sp ) {
						$sp['arm_subscription_plan_name']              = stripslashes( $sp['arm_subscription_plan_name'] );
						$sp['arm_subscription_plan_description']       = stripslashes( $sp['arm_subscription_plan_description'] );
						$sp['arm_subscription_plan_options']           = maybe_unserialize( $sp['arm_subscription_plan_options'] );
						$plans_data[ $sp['arm_subscription_plan_id'] ] = $sp;
					}
				}

				$plans_data = apply_filters( 'arm_all_active_subscription_plans', $plans_data );

				return $plans_data;
			} else {
				return false;
			}
		}

		function arm_get_total_active_plan_counts() {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings;

			$plan_counts = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(`arm_subscription_plan_id`) FROM `".$ARMemberLite->tbl_arm_subscription_plans."` WHERE `arm_subscription_plan_status`=%d AND `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_post_id`=%d AND `arm_subscription_plan_gift_status`=%d",1,0,0,0) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_member_templates is a table name
			return $plan_counts;
		}

		function arm_get_total_plan_counts() {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings;

			$plan_counts = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(`arm_subscription_plan_id`) FROM `".$ARMemberLite->tbl_arm_subscription_plans."` WHERE `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_post_id`=%d AND `arm_subscription_plan_gift_status`=%d",0,0,0) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_member_templates is a table name
			return $plan_counts;
		}

		function arm_delete_subscription_plan( $plan_id ) {
			global $wp, $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings;
			$res_var = false;
			if ( ! empty( $plan_id ) && $plan_id != 0 ) {
				$plan_detail = new ARM_Plan_Lite( $plan_id );
				$res_var     = $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$ARMemberLite->tbl_arm_subscription_plans,
					array(
						'arm_subscription_plan_is_delete' => '1',
						'arm_subscription_plan_status'    => '0',
					),
					array( 'arm_subscription_plan_id' => $plan_id )
				);
				if ( $res_var ) {
					do_action( 'arm_deleted_subscription_plan', $plan_id, $plan_detail );
				}
			}
			return $res_var;
		}

		function arm_delete_single_plan() {
			global $wp, $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_plans'], '1' ); //phpcs:ignore --Reason:Verifying nonce
			$action = sanitize_text_field( $_POST['act'] ); //phpcs:ignore
			$id     = intval( $_POST['id'] ); //phpcs:ignore
			if ( $action == 'delete' ) {
				if ( empty( $id ) ) {
					$errors[] = esc_html__( 'Invalid action.', 'armember-membership' );
				} else {
					if ( ! current_user_can( 'arm_manage_plans' ) ) {
						$errors[] = esc_html__( 'Sorry, You do not have permission to perform this action', 'armember-membership' );
					} else {
						$res_var = self::arm_delete_subscription_plan( $id );
						if ( $res_var ) {
							$message = esc_html__( 'Plan has been deleted successfully.', 'armember-membership' );
						}
					}
				}
			}
			$return_array = $arm_global_settings->handle_return_messages( @$errors, @$message );
			echo arm_pattern_json_encode( $return_array );
			exit;
		}


		function arm_insert_sample_subscription_plan() {
			global $wp, $wpdb, $wp_roles, $ARMemberLite, $arm_global_settings;
			$totalPlans = $this->arm_get_total_plan_counts();
			if ( $totalPlans == 0 ) {
				$defaultRole               = ( $wp_roles->is_role( 'armember' ) ) ? 'armember' : get_option( 'default_role' );
				$plan_options['pricetext'] = esc_html__( 'Free Membership', 'armember-membership' );
				$sample_plan_data          = array(
					'arm_subscription_plan_name'         => esc_html__( 'Free Membership', 'armember-membership' ),
					'arm_subscription_plan_description'  => esc_html__( 'This is Free Membership Plan.', 'armember-membership' ),
					'arm_subscription_plan_type'         => 'free',
					'arm_subscription_plan_options'      => maybe_serialize( $plan_options ),
					'arm_subscription_plan_amount'       => 0,
					'arm_subscription_plan_status'       => 1,
					'arm_subscription_plan_role'         => $defaultRole,
					'arm_subscription_plan_created_date' => current_time( 'mysql' ),
				);
				// Insert First(Sample) Subscription Plan.
				$wpdb->insert( $ARMemberLite->tbl_arm_subscription_plans, $sample_plan_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$plan_id = $wpdb->insert_id;
			}
			return true;
		}

		function arm_user_plan_status_action( $atts, $failed_by_system = false ) {

			global $wp, $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways, $arm_email_settings, $arm_members_class;
			$atts     = ( ! empty( $atts ) ) ? $atts : array();
			$defaults = array(
				'plan_id' => '0', // Plan ID, Pass `all` to get all plans options.
				'user_id' => '0', // User ID.
				'action'  => '',
			);
			// Extract Shortcode Attributes
			$args = shortcode_atts( $defaults, $atts );

			extract( $args );
			if ( $plan_id != 0 && $user_id != 0 && ! empty( $action ) ) {
				$user_detail = get_userdata( $user_id );
				$user_email  = $user_detail->user_email;
				$user_login  = stripslashes( $user_detail->user_login );
				$nowDate     = current_time( 'mysql' );

				$action_opt       = '';
				$secondary_status = 5;
				$user_plans       = get_user_meta( $user_id, 'arm_user_plan_ids', true );
				$user_plans       = ! empty( $user_plans ) ? $user_plans : array();

				if ( in_array( $plan_id, $user_plans ) ) {

					$defaultPlanData  = $this->arm_default_plan_array();
					$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
					$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
					$planData         = shortcode_atts( $defaultPlanData, $userPlanDatameta );

					$plan_detail = $planData['arm_current_plan_detail'];
					$curPlan     = new ARM_Plan_Lite( 0 );

					if ( is_array( $plan_detail ) ) {
						$plan_detail = (object) $plan_detail;
					}
					$curPlan->init( $plan_detail );

					$planGracePeriod = 0;
					if ( $curPlan->exists() ) {
						$plan_options = $curPlan->options;
						if ( $curPlan->is_paid() && ! $curPlan->is_lifetime() ) {
							if ( ! empty( $plan_options['grace_period'] ) ) {
								switch ( $action ) {
									case 'eot':
										$planGracePeriod = isset( $plan_options['grace_period']['end_of_term'] ) ? $plan_options['grace_period']['end_of_term'] : 0;
										break;
									case 'failed_payment':
										$planGracePeriod = isset( $plan_options['grace_period']['failed_payment'] ) ? $plan_options['grace_period']['failed_payment'] : 0;
										break;
									default:
										break;
								}
							}
						}
						$user_in_grace = $planData['arm_is_user_in_grace'];

						if ( ! empty( $user_in_grace ) && $user_in_grace == '1' ) {
							$graceEnd        = $planData['arm_grace_period_end'];
							$planGracePeriod = 0;

							if ( $graceEnd > strtotime( $nowDate ) ) {
								return;
							}
						}

						switch ( $action ) {
							case 'eot':
								$u_gateway     = $planData['arm_user_gateway'];
								$plan_end_date = ! empty( $planData['arm_expire_plan'] ) ? $planData['arm_expire_plan'] : $nowDate;

								$action_opt     = $plan_options['eot'];
								$change_plan_to = $planData['arm_change_plan_to'];
								if ( ! empty( $change_plan_to ) && $change_plan_to != 0 ) {
									$action_opt = $change_plan_to;
								}
								$secondary_status = 3;
								$temp_detail_user = $arm_email_settings->arm_get_email_template( $arm_email_settings->templates->grace_eot );
								break;

							case 'failed_payment':
								$action_opt        = $plan_options['payment_failed_action'];
								$plan_end_date     = ! empty( $planData['arm_next_due_payment'] ) ? $planData['arm_next_due_payment'] : $nowDate;
								$secondary_status  = 5;
								$temp_detail_admin = $arm_email_settings->arm_get_email_template( $arm_email_settings->templates->failed_payment_admin );
								if ( $temp_detail_admin->arm_template_status == '1' ) {
									$all_email_settings = $arm_email_settings->arm_get_all_email_settings();
									$subject_admin      = $arm_global_settings->arm_filter_email_with_user_detail( $temp_detail_admin->arm_template_subject, $user_id, $plan_id );
									$message_admin      = $arm_global_settings->arm_filter_email_with_user_detail( $temp_detail_admin->arm_template_content, $user_id, $plan_id );
									$admin_send_mail    = $arm_global_settings->arm_send_message_to_armember_admin_users( '', $subject_admin, $message_admin );
								}
								$temp_detail_user = $arm_email_settings->arm_get_email_template( $arm_email_settings->templates->grace_failed_payment );
								break;
							default:
								break;
						}
						/* Do Action Before Change User's Subscription Status */
						if ( $planGracePeriod > 0 ) {
							$graceEndDate = strtotime( date( 'Y-m-d', $plan_end_date ) . " +$planGracePeriod day" ); //phpcs:ignore

							$planData['arm_is_user_in_grace']    = '1';
							$planData['arm_grace_period_end']    = $graceEndDate;
							$planData['arm_grace_period_action'] = $action;

							update_user_meta( $user_id, 'arm_user_plan_' . $plan_id, $planData );
							if ( isset( $temp_detail_user ) && $temp_detail_user->arm_template_status == '1' ) {
								$subject        = $arm_global_settings->arm_filter_email_with_user_detail( $temp_detail_user->arm_template_subject, $user_id, $plan_id );
								$message        = $arm_global_settings->arm_filter_email_with_user_detail( $temp_detail_user->arm_template_content, $user_id, $plan_id );
								$user_send_mail = $arm_global_settings->arm_wp_mail( '', $user_email, $subject, $message );
							}
						} else {
							do_action( 'arm_user_plan_status_action_' . $action, $args, $curPlan );
							if ( ! empty( $action_opt ) && ! empty( $action ) ) {

								if ( ! $failed_by_system ) {
									$this->arm_add_membership_history( $user_id, $plan_id, $action, array(), 'system' );
								}

								if ( $this->isPlanExist( $action_opt ) ) {

									$this->arm_clear_user_plan_detail( $user_id, $plan_id );
									$arm_members_class->arm_new_plan_assigned_by_system( $action_opt, $plan_id, $user_id );
								} else {

									if ( $action == 'eot' ) {
										$this->arm_clear_user_plan_detail( $user_id, $plan_id );
									} else {
										$payment_mode             = $planData['arm_payment_mode'];
										$arm_user_payment_gateway = $planData['arm_user_gateway'];
										$old_next_due_date        = $planData['arm_next_due_payment'];
										$payment_cycle            = $planData['arm_payment_cycle'];
										$recurring_data           = $curPlan->prepare_recurring_data( $payment_cycle );
										$amount                   = $recurring_data['amount'];
										if ( 'failed_payment' != $action || $payment_mode == 'manual_subscription' ) {
											$completed_recurrence = $planData['arm_completed_recurring'];
											$completed_recurrence++;
											$planData['arm_completed_recurring'] = $completed_recurrence;
											// update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $planData);
											$arm_next_payment_date            = $arm_members_class->arm_get_next_due_date( $user_id, $plan_id, false, $payment_cycle );
											$planData['arm_next_due_payment'] = $arm_next_payment_date;

											update_user_meta( $user_id, 'arm_user_plan_' . $plan_id, $planData );
											update_user_meta( $user_id, 'arm_user_failed_payment_plan_status_' . $plan_id, 'yes' );
										}

										// if ($payment_mode == 'manual_subscription') {

											$extraParam              = array();
											$extraParam['manual_by'] = 'Paid By system';
											$payment_data            = array(
												'arm_user_id' => $user_id,
												'arm_first_name' => $user_detail->first_name,
												'arm_last_name' => $user_detail->last_name,
												'arm_plan_id' => $plan_id,
												'arm_payment_gateway' => $arm_user_payment_gateway,
												'arm_payment_type' => 'subscription',
												'arm_token' => '-',
												'arm_payer_email' => $user_email,
												'arm_transaction_payment_type' => 'subscription',
												'arm_transaction_status' => 'failed',
												'arm_payment_mode' => $payment_mode,
												'arm_payment_date' => date( 'Y-m-d H:i:s', $old_next_due_date ), //phpcs:ignore
												'arm_extra_vars' => maybe_serialize( $extraParam ),

												'arm_amount' => $amount,
											);
											$payment_log_id          = $arm_payment_gateways->arm_save_payment_log( $payment_data );
											// }

											$total_user_plans = get_user_meta( $user_id, 'arm_user_plan_ids', true );
											if ( ! empty( $total_user_plans ) ) {
												$suspended_plan_ids = get_user_meta( $user_id, 'arm_user_suspended_plan_ids', true );
												$suspended_plan_id  = ( isset( $suspended_plan_ids ) && ! empty( $suspended_plan_ids ) ) ? $suspended_plan_ids : array();

												if ( ! in_array( $plan_id, $suspended_plan_id ) ) {
													$suspended_plan_id[] = $plan_id;
													update_user_meta( $user_id, 'arm_user_suspended_plan_ids', array_values( $suspended_plan_id ) );
												}
											}
									}
								}
							}
							$planData['arm_is_user_in_grace']    = '0';
							$planData['arm_grace_period_end']    = '';
							$planData['arm_grace_period_action'] = '';
							update_user_meta( $user_id, 'arm_user_plan_' . $plan_id, $planData );
						}
					} /* End `($curPlan->exists() && $user_plan == $plan_id)` */
				}
			}
		}

		function isFreePlanExist( $planID = 0 ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways;
			$isPlan = false;
			if ( ! empty( $planID ) && is_numeric( $planID ) && $planID != 0 ) {
				$plan   = new ARM_Plan_Lite( $planID );
				$isPlan = ( $plan->exists() && $plan->is_active() && $plan->is_free() );
			}
			return $isPlan;
		}

		function isPlanExist( $planID = 0 ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways;
			$isPlan = false;
			if ( ! empty( $planID ) && is_numeric( $planID ) && $planID != 0 ) {
				$plan   = new ARM_Plan_Lite( $planID );
				$isPlan = ( $plan->exists() && $plan->is_active() );
			}
			return $isPlan;
		}

		function arm_ajax_stop_user_subscription( $user_id = 0, $plan_id = 0 ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways, $arm_members_class, $arm_capabilities_global;

			$date_format = $arm_global_settings->arm_get_wp_date_format();
			$err_msg     = $arm_global_settings->common_message['arm_general_msg'];
			$err_msg     = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'Sorry, Something went wrong. Please try again.', 'armember-membership' );
			$success_msg = ( isset( $_POST['cancel_message'] ) && ! empty( $_POST['cancel_message'] ) ) ? sanitize_text_field( $_POST['cancel_message'] ) : esc_html__( 'Your subscription has been cancelled.', 'armember-membership' ); //phpcs:ignore
			$return      = array(
				'type' => 'error',
				'msg'  => $err_msg,
			);
			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_cancel_membership' && isset( $_POST['type'] ) && $_POST['type'] == 'front' ) { //phpcs:ignore
				$ARMemberLite->arm_check_user_cap('',1); //phpcs:ignore --Reason:Verifying nonce
				$user_id = get_current_user_id();
				$plan_id = !empty( $_REQUEST['plan_id'] ) ? intval( $_REQUEST['plan_id'] ) : ''; //phpcs:ignore
			} elseif ( empty( $user_id ) && empty( $plan_id ) ) {
				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_members'], '1' ); //phpcs:ignore --Reason:Verifying nonce
				$user_id = !empty( $_REQUEST['user_id'] ) ? intval( $_REQUEST['user_id'] ) : ''; //phpcs:ignore
				$plan_id = !empty( $_REQUEST['plan_id'] ) ? intval( $_REQUEST['plan_id'] ) : ''; //phpcs:ignore
			}

			$defaultPlanData  = $this->arm_default_plan_array();
			$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
			$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();

			$planDataDefault = shortcode_atts( $defaultPlanData, $userPlanDatameta );
			$planData        = ! empty( $userPlanDatameta ) ? $userPlanDatameta : $planDataDefault;

			$planDetail = $planData['arm_current_plan_detail'];
			if ( ! empty( $planDetail ) ) {
				$plan = new ARM_Plan_Lite( 0 );
				$plan->init( (object) $planDetail );
			} else {
				$plan = new ARM_Plan_Lite( $plan_id );
			}

			if ( $plan->exists() ) {
				$cancel_plan_action = isset( $plan->options['cancel_plan_action'] ) ? $plan->options['cancel_plan_action'] : 'immediate';

				if ( ( $plan->is_paid() && ! $plan->is_lifetime() && $plan->is_recurring() ) || ( $plan->is_paid() || $plan->is_lifetime() || $plan->is_free() ) ) {
					$planData['arm_cencelled_plan'] = 'yes';
					update_user_meta( $user_id, 'arm_user_plan_' . $plan_id, $planData );
					$cancel_plan_action = apply_filters( 'arm_before_cancel_subscription', $cancel_plan_action, $plan, $user_id );
					if ( $cancel_plan_action == 'immediate' ) {
						if ( $plan->is_paid() && ! $plan->is_lifetime() && $plan->is_recurring() ) {
							do_action( 'arm_cancel_subscription_gateway_action', $user_id, $plan_id );
						}
						$this->arm_add_membership_history( $user_id, $plan_id, 'cancel_subscription' );
						do_action( 'arm_cancel_subscription', $user_id, $plan_id );
						$this->arm_clear_user_plan_detail( $user_id, $plan_id );
						$cancel_plan_act = isset( $plan->options['cancel_action'] ) ? $plan->options['cancel_action'] : 'block';
						if ( $this->isPlanExist( $cancel_plan_act ) ) {
							$arm_members_class->arm_new_plan_assigned_by_system( $cancel_plan_act, $plan_id, $user_id );
						} else {
						}

						$return = array(
							'type' => 'success',
							'msg'  => $success_msg,
						);
					} elseif ( $cancel_plan_action == 'on_expire' ) {
						$plan_cycle      = isset( $planData['arm_payment_cycle'] ) ? $planData['arm_payment_cycle'] : '';
						$paly_cycle_data = $plan->prepare_recurring_data( $plan_cycle );
						$expire_strtime  = '';
						if ( $paly_cycle_data['rec_time'] == 'infinite' ) {
							$expire_strtime = $planData['arm_next_due_payment'];
						} else {
							$expire_strtime = $planData['arm_expire_plan'];
						}
						$expire_time = date_i18n( $date_format, $expire_strtime );
						$success_msg = esc_html__( 'Your Subscription will be canceled on', 'armember-membership' ) . ' ' . $expire_time;
						$return      = array(
							'type' => 'success',
							'msg'  => $success_msg,
						);
					}
					do_action( 'arm_after_cancel_subscription', $user_id, $plan, $cancel_plan_action, $planData );
				}
			}
			if (isset($_POST['action']) && $_POST['action'] == 'arm_cancel_membership' && isset($_POST['type']) && $_POST['type'] == 'front') {//phpcs:ignore
                echo wp_json_encode($return);
                exit;
            } else {
                return $return;
            }
		}

		function arm_clear_user_plan_detail( $user_id = 0, $plan_id = 0 ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways,  $arm_subscription_plans;
			if ( ! empty( $user_id ) && $user_id != 0 ) {

				$user             = get_userdata( $user_id );
				$defaultPlanData  = $this->arm_default_plan_array();
				$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
				$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
				$planData         = shortcode_atts( $defaultPlanData, $userPlanDatameta );

				$planDetail = $planData['arm_current_plan_detail'];
				if ( ! empty( $planDetail ) ) {
					$plan = new ARM_Plan_Lite( 0 );
					$plan->init( (object) $planDetail );
				} else {
					$plan = new ARM_Plan_Lite( $plan_id );
				}

				$is_cancelled_by_user = $planData['arm_cencelled_plan'];
				$payment_mode         = $planData['arm_payment_mode'];
				$completed_recurrence = isset( $planData['arm_completed_recurring'] ) ? $planData['arm_completed_recurring'] : 0;
				$payment_cycle        = $planData['arm_payment_cycle'];
				$total_recurrence     = 0;
				if ( $plan->is_recurring() ) {
					if ( $payment_cycle === '' ) {
						$total_recurrence = $plan->options['recurring']['time'];
					} else {
						$total_recurrence = $plan->options['payment_cycles'][ $payment_cycle ]['recurring_time'];
					}
				}

				if ( $plan->is_recurring() && $payment_mode == 'manual_subscription' && $total_recurrence > $completed_recurrence && empty( $is_cancelled_by_user ) ) {

				} else {

					$arm_changed_expiry_date_plan = get_user_meta( $user_id, 'arm_changed_expiry_date_plans', true );
					$arm_changed_expiry_date_plan = ! empty( $arm_changed_expiry_date_plan ) ? $arm_changed_expiry_date_plan : array();
					if ( ! empty( $arm_changed_expiry_date_plan ) ) {
						if ( in_array( $plan_id, $arm_changed_expiry_date_plan ) ) {
							unset( $arm_changed_expiry_date_plan[ array_search( $plan_id, $arm_changed_expiry_date_plan ) ] );
						}
					}

					delete_user_meta( $user_id, 'arm_user_plan_' . $plan_id );

					if ( $user->has_cap( "armember_access_plan_{$plan_id}" ) ) {
						$user->remove_cap( "armember_access_plan_{$plan_id}" );
					}

					$plan_id_role_array = $arm_subscription_plans->arm_get_plan_role_by_id( array( $plan_id ) );
					if ( ! empty( $plan_id_role_array ) && is_array( $plan_id_role_array ) ) {
						foreach ( $plan_id_role_array as $key => $value ) {
							$plan_role = $value['arm_subscription_plan_role'];
							if ( ! empty( $plan_role ) ) {
								$user->remove_role( $plan_role );
							}
						}
					}

					$user_plan_ids = get_user_meta( $user_id, 'arm_user_plan_ids', true );
					$user_plan_ids = ! empty( $user_plan_ids ) ? $user_plan_ids : array();

					$user_future_plan_ids = get_user_meta( $user_id, 'arm_user_future_plan_ids', true );
					$user_future_plan_ids = ! empty( $user_future_plan_ids ) ? $user_future_plan_ids : array();

					$user_suspended_plan_ids = get_user_meta( $user_id, 'arm_user_suspended_plan_ids', true );
					$user_suspended_plan_ids = ! empty( $user_suspended_plan_ids ) ? $user_suspended_plan_ids : array();

					if ( in_array( $plan_id, $user_plan_ids ) ) {
						unset( $user_plan_ids[ array_search( $plan_id, $user_plan_ids ) ] );
					}

					if ( in_array( $plan_id, $user_future_plan_ids ) ) {
						unset( $user_future_plan_ids[ array_search( $plan_id, $user_future_plan_ids ) ] );
					}

					if ( in_array( $plan_id, $user_suspended_plan_ids ) ) {
						unset( $user_suspended_plan_ids[ array_search( $plan_id, $user_suspended_plan_ids ) ] );
						update_user_meta( $user_id, 'arm_user_suspended_plan_ids', array_values( $user_suspended_plan_ids ) );
					}

					if ( empty( $user_future_plan_ids ) ) {
						delete_user_meta( $user_id, 'arm_user_future_plan_ids' );
					} else {
						update_user_meta( $user_id, 'arm_user_future_plan_ids', array_values( $user_future_plan_ids ) );
					}

					if ( empty( $user_plan_ids ) ) {
						$arm_default_wordpress_role = get_option( 'default_role', 'subscriber' );
						$user->add_role( $arm_default_wordpress_role );
						delete_user_meta( $user_id, 'arm_user_plan_ids' );
						delete_user_meta( $user_id, 'arm_user_last_plan' );
						delete_user_meta( $user_id, 'arm_user_suspended_plan_ids' );
						delete_user_meta( $user_id, 'arm_changed_expiry_date_plans', true );
					} else {
						update_user_meta( $user_id, 'arm_user_plan_ids', array_values( $user_plan_ids ) );
					}
				}
			}
			return;
		}

		/**
		 * Update User's Last Subscriptions
		 */
		function arm_before_update_user_subscription_action( $user_id = 0, $new_plan_id = 0 ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways;
			if ( ! empty( $user_id ) && $user_id != 0 ) {
				$old_plan_ids = get_user_meta( $user_id, 'arm_user_plan_ids', true );
				$old_plan_ids = ! empty( $old_plan_ids ) ? $old_plan_ids : array();
				if ( ! empty( $old_plan_ids ) && ! in_array( $new_plan_id, $old_plan_ids ) ) {
					// Cancel User's Last Subscription
					foreach ( $old_plan_ids as $old_plan_id ) {
						do_action( 'arm_cancel_subscription_gateway_action', $user_id, $old_plan_id );
					}
				}
			}
		}

		function arm_update_user_subscription( $user_id = 0, $new_plan_id = 0, $action_by = '', $allow_trial = true, $arm_last_payment_status = 'success' ) {

			global $wp, $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways, $arm_manage_communication,$arm_members_class, $arm_subscription_plans;

			if ( ! empty( $user_id ) && $user_id != 0 ) {
				arm_set_member_status( $user_id, 1 );
				/* Only update plan if Member is active */

				$user               = new WP_User( $user_id );
				$old_plan_ids       = get_user_meta( $user_id, 'arm_user_plan_ids', true );
				$suspended_plan_ids = get_user_meta( $user_id, 'arm_user_suspended_plan_ids', true );
				$suspended_plan_ids = ! empty( $suspended_plan_ids ) ? $suspended_plan_ids : array();
				$old_plan           = ( isset( $old_plan_ids ) && ! empty( $old_plan_ids ) ) ? $old_plan_ids : array();
				$old_plans          = $old_plan;
				if ( in_array( $new_plan_id, $suspended_plan_ids ) ) {
					unset( $suspended_plan_ids[ array_search( $new_plan_id, $suspended_plan_ids ) ] );
					update_user_meta( $user_id, 'arm_user_suspended_plan_ids', $suspended_plan_ids );
				}

				if ( ! in_array( $new_plan_id, $old_plan ) ) {

					$new_plan = new ARM_Plan_Lite( $new_plan_id );
					if ( $new_plan->exists() && $new_plan->is_active() ) {

						$new_plan = apply_filters( 'arm_change_plan_before_user_change_plan', $new_plan, $user_id, $old_plan, $new_plan_id );
						do_action( 'arm_before_change_user_plans', $user_id, $old_plan, $new_plan_id, $new_plan );

						if ( $action_by == 'admin' ) {
							$arm_change_subscription_mail_type = 'on_change_subscription_by_admin';
						} else {
							$arm_change_subscription_mail_type = 'change_subscription';
						}
							$mail_type = ( empty( $old_plan ) ) ? 'new_subscription' : $arm_change_subscription_mail_type;

							update_user_meta( $user_id, 'arm_user_plan_ids', array( $new_plan_id ) );

						if ( ! empty( $old_plan ) ) {
							foreach ( $old_plan as $old_plan_id ) {
								$user->remove_cap( 'armember_access_plan_' . $old_plan_id );
								delete_user_meta( $user_id, 'arm_user_plan_' . $old_plan_id );
							}

							$plan_id_role_array = $arm_subscription_plans->arm_get_plan_role_by_id( $old_plan );
							if ( ! empty( $plan_id_role_array ) && is_array( $plan_id_role_array ) ) {
								foreach ( $plan_id_role_array as $key => $value ) {
									$plan_role = $value['arm_subscription_plan_role'];
									if ( ! empty( $plan_role ) ) {
										$user->remove_role( $plan_role );
										$arm_default_wordpress_role = get_option( 'default_role', 'subscriber' );
										$user->add_role( $arm_default_wordpress_role );
									}
								}
							}
						}
						if ( ! empty( $new_plan->plan_role ) ) {
							$user->set_role( $new_plan->plan_role );
						}

						update_user_meta( $user_id, 'arm_user_last_plan', $new_plan_id );

						$user->add_cap( 'armember_access_plan_' . $new_plan_id );
						$defaultPlanData  = $this->arm_default_plan_array();
						$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, true );
						$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
						$newPlanData      = shortcode_atts( $defaultPlanData, $userPlanDatameta );

						$payment_mode  = ( isset( $newPlanData['arm_payment_mode'] ) && ! empty( $newPlanData['arm_payment_mode'] ) ) ? $newPlanData['arm_payment_mode'] : 'manual_subscription';
						$payment_cycle = ( isset( $newPlanData['arm_payment_cycle'] ) && ! empty( $newPlanData['arm_payment_cycle'] ) ) ? $newPlanData['arm_payment_cycle'] : 0;

						// Start Plan
						$start_time = strtotime( current_time( 'mysql' ) );

						if ( $new_plan->is_recurring() ) {
							if ( $new_plan->has_trial_period() && $action_by != 'system' ) {
								if ( isset( $old_plan ) && ! empty( $old_plan ) ) {
									$newPlanData['arm_completed_recurring'] = 1;
								} else {
									$trial_and_sub_start_date = $new_plan->arm_trial_and_plan_start_date( '', $payment_mode, $allow_trial, $payment_cycle );
									$start_time               = isset( $trial_and_sub_start_date['subscription_start_date'] ) ? $trial_and_sub_start_date['subscription_start_date'] : '';
									if ( isset( $trial_and_sub_start_date['arm_expire_plan_trial'] ) && $trial_and_sub_start_date['arm_expire_plan_trial'] != '' ) {

										$newPlanData['arm_trial_end']           = $trial_and_sub_start_date['arm_expire_plan_trial'];
										$newPlanData['arm_trial_start']         = $trial_and_sub_start_date['arm_trial_start_date'];
										$newPlanData['arm_is_trial_plan']       = 1;
										$newPlanData['arm_completed_recurring'] = 0;
									}
								}
							} else {
								$newPlanData['arm_completed_recurring'] = 1;
							}
						}
						$newPlanData['arm_start_plan'] = $start_time;

						 $newPlanData['arm_payment_mode']   = $payment_mode;
						  $newPlanData['arm_payment_cycle'] = $payment_cycle;

						// Expire Plan
						$expire_time = $new_plan->arm_plan_expire_time( $start_time, $payment_mode, $payment_cycle );
						if ( $expire_time != false ) {
							$newPlanData['arm_expire_plan'] = $expire_time;
						}

						/* Set Current Plan Detail */
						$curPlanDetail                                    = (array) $new_plan->plan_detail;
						$curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;

						$newPlanData['arm_current_plan_detail'] = $curPlanDetail;
						update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $newPlanData );

						if ( $new_plan->is_recurring() ) {
							$arm_next_payment_date = $arm_members_class->arm_get_next_due_date( $user_id, $new_plan_id, $allow_trial, $payment_cycle );
							if ( $arm_next_payment_date != '' ) {
								$newPlanData['arm_next_due_payment'] = $arm_next_payment_date;
							}
						}

						update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $newPlanData );

						$this->arm_add_membership_history( $user_id, $new_plan_id, 'new_subscription', array(), $action_by );
						/**
						 * Send Email Notification for Successful Payment
						 */
						$arm_manage_communication->arm_user_plan_status_action_mail(
							array(
								'plan_id' => $new_plan_id,
								'user_id' => $user_id,
								'action'  => $mail_type,
							)
						);
						/**
						 * Update User's Achievements.
						 */

						do_action( 'arm_after_user_plan_change', $user_id, $new_plan_id );
					}
				} else {

					$mail_type = ( empty( $old_plan ) ) ? 'new_subscription' : 'renew_subscription';
					$user      = new WP_User( $user_id );
					$new_plan  = new ARM_Plan_Lite( $new_plan_id );
					if ( $new_plan->exists() && $new_plan->is_active() ) {

						$new_plan = apply_filters( 'arm_change_plan_before_user_renew_subscription', $new_plan, $user_id, $old_plan, $new_plan_id );
						if ( ! empty( $new_plan->plan_role ) ) {
							$user->set_role( $new_plan->plan_role );
						}

						$defaultPlanData  = $this->arm_default_plan_array();
						$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, true );
						$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
						$oldPlanData      = shortcode_atts( $defaultPlanData, $userPlanDatameta );

						$payment_mode               = isset( $oldPlanData['arm_payment_mode'] ) ? $oldPlanData['arm_payment_mode'] : 'manual_subscription';
						$payment_cycle              = isset( $oldPlanData['arm_payment_cycle'] ) ? $oldPlanData['arm_payment_cycle'] : '';
						$arm_old_plan_detail        = $oldPlanData['arm_current_plan_detail'];
						$arm_user_old_payment_cycle = '';
						$arm_user_old_payment_mode  = 'manual_subscription';
						if ( ! empty( $arm_old_plan_detail ) ) {
							$arm_user_old_plan_info = new ARM_Plan_Lite( 0 );
							$arm_user_old_plan_info->init( (object) $arm_old_plan_detail );
							$arm_user_old_payment_cycle = isset( $arm_old_plan_detail['arm_user_selected_payment_cycle'] ) ? $arm_old_plan_detail['arm_user_selected_payment_cycle'] : '';
							$arm_user_old_payment_mode  = isset( $arm_old_plan_detail['arm_user_old_payment_mode'] ) ? $arm_old_plan_detail['arm_user_old_payment_mode'] : '';
						} else {
							$arm_user_old_plan_info = new ARM_Plan_Lite( $new_plan_id );
						}

						$arm_user_old_plan_data = $arm_user_old_plan_info->prepare_recurring_data( $arm_user_old_payment_cycle );

						$planObj = new ARM_Plan_Lite( $new_plan_id );

						if ( $planObj->is_recurring() && $payment_mode == 'manual_subscription' ) {

							$total_recurrence = $arm_user_old_plan_data['rec_time'];
							$completed_rec    = $oldPlanData['arm_completed_recurring'];
							$expiry_time      = $oldPlanData['arm_expire_plan'];

							if ( $arm_user_old_payment_mode != 'manual_subscription' ) {
								$plan_action = 'renew_subscription';
							} else {
								$plan_action = 'renew_or_recurring';
							}

							// if ((($completed_rec == $total_recurrence || $completed_rec === '') && $total_recurrence != 'infinite' ) || $plan_action == 'renew_subscription')
							if ( $total_recurrence != 'infinite' && $completed_rec >= $total_recurrence ) {

								// Code for keep started plan date show at Current Membership Shortcode which was showing future date.
								$arm_check_current_time = strtotime( current_time( 'mysql' ) );
								if ( $arm_check_current_time < $oldPlanData['arm_expire_plan'] ) {
									if ( empty( $oldPlanData['arm_started_plan_date'] ) ) {
										$oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
									} elseif ( $oldPlanData['arm_start_plan'] < $oldPlanData['arm_started_plan_date'] ) {
										$oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
									}
								}

								$now = strtotime( current_time( 'mysql' ) );
								if ( $expiry_time != '' ) {
									$start_time                    = $expiry_time;
									$oldPlanData['arm_start_plan'] = $start_time;
								} else {
									$start_time                    = $now;
									$oldPlanData['arm_start_plan'] = $start_time;
								}
								do_action( 'arm_before_renew_user_plans', $user_id, $old_plan, $new_plan_id, $new_plan );

								$expire_time = $new_plan->arm_plan_expire_time( $start_time, $payment_mode, $payment_cycle );
								if ( $expire_time != false ) {
									$oldPlanData['arm_expire_plan'] = $expire_time;
								}

								$oldPlanData['arm_completed_recurring'] = 1;

								$curPlanDetail                                    = (array) $new_plan->plan_detail;
								$curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;
								$oldPlanData['arm_current_plan_detail']           = $curPlanDetail;
								update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData );

								$arm_next_payment_date = $arm_members_class->arm_get_next_due_date( $user_id, $new_plan_id, false, $payment_cycle );
								if ( $arm_next_payment_date != '' ) {
									$oldPlanData['arm_next_due_payment'] = $arm_next_payment_date;
								}

								$oldPlanData['arm_sent_msgs']           = array();
								$oldPlanData['arm_trial_end']           = '';
								$oldPlanData['arm_trial_start']         = '';
								$oldPlanData['arm_is_trial_plan']       = 0;
								$oldPlanData['arm_is_user_in_grace']    = 0;
								$oldPlanData['arm_grace_period_end']    = '';
								$oldPlanData['arm_grace_period_action'] = '';
								$oldPlanData['arm_cencelled_plan']      = '';
								$oldPlanData['arm_subscr_effective']    = '';
								$oldPlanData['arm_change_plan_to']      = '';

								update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData );

								$this->arm_add_membership_history( $user_id, $new_plan_id, 'renew_subscription' );
								$arm_manage_communication->arm_user_plan_status_action_mail(
									array(
										'plan_id' => $new_plan_id,
										'user_id' => $user_id,
										'action'  => $mail_type,
									)
								);
								do_action( 'arm_after_user_plan_renew', $user_id, $new_plan_id );
							} else {

								$completed_rec        = $oldPlanData['arm_completed_recurring'];
								$old_next_due_payment = $oldPlanData['arm_next_due_payment'];

								$now = strtotime( current_time( 'mysql' ) );
								if ( $now < $old_next_due_payment ) {

									if ( $arm_last_payment_status != 'failed' ) {
										$completed_rec                          = ! empty( $completed_rec ) ? $completed_rec : 0;
										$oldPlanData['arm_completed_recurring'] = ( $completed_rec + 1 );
										update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData );
										$arm_next_payment_date = $arm_members_class->arm_get_next_due_date( $user_id, $new_plan_id, false, $payment_cycle );
										if ( $arm_next_payment_date != '' ) {
											$oldPlanData['arm_next_due_payment'] = $arm_next_payment_date;
										}
									}
								} else {

									$completed_rec                          = ! empty( $completed_rec ) ? $completed_rec : 0;
									$oldPlanData['arm_completed_recurring'] = ( $completed_rec + 1 );
									update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData );
									$arm_next_payment_date = $arm_members_class->arm_get_next_due_date( $user_id, $new_plan_id, false, $payment_cycle );
									if ( $arm_next_payment_date != '' ) {
										$oldPlanData['arm_next_due_payment'] = $arm_next_payment_date;
									}
								}

								$oldPlanData['arm_is_user_in_grace']    = 0;
								$oldPlanData['arm_grace_period_end']    = '';
								$oldPlanData['arm_grace_period_action'] = '';

								update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData );
								do_action( 'arm_after_user_recurring_payment_done', $user_id, $new_plan_id );
							}
						} else {

							do_action( 'arm_before_renew_user_plans', $user_id, $old_plan, $new_plan_id, $new_plan );

							// Code for keep started plan date show at Current Membership Shortcode which was showing future date.
							$arm_check_current_time = strtotime( current_time( 'mysql' ) );
							if ( $arm_check_current_time < $oldPlanData['arm_expire_plan'] ) {
								if ( empty( $oldPlanData['arm_started_plan_date'] ) ) {
									$oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
								} elseif ( $oldPlanData['arm_start_plan'] < $oldPlanData['arm_started_plan_date'] ) {
									$oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
								}
							}
							// Start Plan
							$start_time = strtotime( current_time( 'mysql' ) );
							if ( $planObj->is_paid() && ! $planObj->is_lifetime() && ! $planObj->is_recurring() ) {
								$payment_type = $arm_user_old_plan_info->options['payment_type'];
								if ( $payment_type == 'one_time' ) {
									$start_time = ! empty( $oldPlanData['arm_expire_plan'] ) ? $oldPlanData['arm_expire_plan'] : $start_time;
								}
							} elseif ( $planObj->is_recurring() ) {
								$arm_user_gateway                     = ! empty( $oldPlanData['arm_user_gateway'] ) ? $oldPlanData['arm_user_gateway'] : '';
								$need_to_cancel_payment_gateway_array = $arm_payment_gateways->arm_need_to_cancel_old_subscription_gateways();
								$need_to_cancel_payment_gateway_array = ! empty( $need_to_cancel_payment_gateway_array ) ? $need_to_cancel_payment_gateway_array : array();
								if ( ! in_array( $arm_user_gateway, $need_to_cancel_payment_gateway_array ) ) {
									$start_time = ! empty( $oldPlanData['arm_expire_plan'] ) ? $oldPlanData['arm_expire_plan'] : $start_time;
								}
							}
							$oldPlanData['arm_start_plan'] = $start_time;
							// Expire Plan
							$expire_time = $new_plan->arm_plan_expire_time( $start_time, $payment_mode, $payment_cycle );
							if ( $expire_time != false ) {
								$oldPlanData['arm_expire_plan'] = $expire_time;
							}

							$curPlanDetail                                    = (array) $new_plan->plan_detail;
							$curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;
							$oldPlanData['arm_current_plan_detail']           = $curPlanDetail;

							$oldPlanData['arm_sent_msgs']           = array();
							$oldPlanData['arm_trial_end']           = '';
							$oldPlanData['arm_trial_start']         = '';
							$oldPlanData['arm_is_trial_plan']       = 0;
							$oldPlanData['arm_is_user_in_grace']    = 0;
							$oldPlanData['arm_grace_period_end']    = '';
							$oldPlanData['arm_grace_period_action'] = '';
							if ( $planObj->is_recurring() ) {
								$oldPlanData['arm_completed_recurring'] = 1;
								update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData );
								$arm_next_payment_date = $arm_members_class->arm_get_next_due_date( $user_id, $new_plan_id, false, $payment_cycle );
								if ( $arm_next_payment_date != '' ) {
									$oldPlanData['arm_next_due_payment'] = $arm_next_payment_date;
								}
							} else {
								$oldPlanData['arm_completed_recurring'] = '';
								$oldPlanData['arm_next_due_payment']    = '';
							}
							$oldPlanData['arm_cencelled_plan']   = '';
							$oldPlanData['arm_subscr_effective'] = '';
							$oldPlanData['arm_change_plan_to']   = '';

							update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData );

							$this->arm_add_membership_history( $user_id, $new_plan_id, 'renew_subscription' );
							$arm_manage_communication->arm_user_plan_status_action_mail(
								array(
									'plan_id' => $new_plan_id,
									'user_id' => $user_id,
									'action'  => $mail_type,
								)
							);
							do_action( 'arm_after_user_plan_renew', $user_id, $new_plan_id );
						}
						// Update User's Last Subscriptions

					}
				}
			}
		}

		function arm_update_user_subscription_for_bank_transfer( $user_id = 0, $new_plan_id = 0, $payment_gateway = 'bank_transfer', $payment_cycle = 0, $arm_last_payment_status = 'success' ) {

			global $wp, $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways, $arm_manage_communication,  $arm_members_class;
			if ( ! empty( $user_id ) && $user_id != 0 ) {
				arm_set_member_status( $user_id, 1 );
				/* Only update plan if Member is active */

				$old_plan_ids       = get_user_meta( $user_id, 'arm_user_plan_ids', true );
				$suspended_plan_ids = get_user_meta( $user_id, 'arm_user_suspended_plan_ids', true );
				$suspended_plan_ids = ! empty( $suspended_plan_ids ) ? $suspended_plan_ids : array();
				$old_plan           = ( isset( $old_plan_ids ) && ! empty( $old_plan_ids ) ) ? $old_plan_ids : array();
				$old_plans          = $old_plan;
				$payment_mode       = 'manual_subscription';

				if ( ! empty( $suspended_plan_ids ) && in_array( $new_plan_id, $suspended_plan_ids ) ) {
					unset( $suspended_plan_ids[ array_search( $new_plan_id, $suspended_plan_ids ) ] );
					update_user_meta( $user_id, 'arm_user_suspended_plan_ids', $suspended_plan_ids );
				}

				if ( ! in_array( $new_plan_id, $old_plan ) ) {
					$new_plan = new ARM_Plan_Lite( $new_plan_id );
					if ( $new_plan->exists() && $new_plan->is_active() ) {
						$user = new WP_User( $user_id );

						$new_plan = apply_filters( 'arm_change_plan_before_user_change_plan', $new_plan, $user_id, $old_plan, $new_plan_id );
						do_action( 'arm_before_change_user_plans', $user_id, $old_plan, $new_plan_id, $new_plan );
						$is_update_plan = true;

							$mail_type = ( empty( $old_plan ) ) ? 'new_subscription' : 'change_subscription';

						if ( ! empty( $old_plan ) ) {
							$defaultPlanData = $this->arm_default_plan_array();
							$old_plan_id     = isset( $old_plans[0] ) ? $old_plans[0] : 0;
							$oldPlanData     = get_user_meta( $user_id, 'arm_user_plan_' . $old_plan_id, true );
							$oldPlanData     = ! empty( $oldPlanData ) ? $oldPlanData : array();
							$oldPlanData     = shortcode_atts( $defaultPlanData, $oldPlanData );
							$oldPlanDetail   = isset( $oldPlanData['arm_current_plan_detail'] ) ? $oldPlanData['arm_current_plan_detail'] : array();
							if ( ! empty( $oldPlanDetail ) ) {
								$old_plan1 = new ARM_Plan_Lite( 0 );
								$old_plan1->init( (object) $oldPlanDetail );
							} else {
								$old_plan1 = new ARM_Plan_Lite( $old_plan_id );
							}

							if ( $old_plan1->exists() ) {
								if ( $old_plan1->is_lifetime() || $old_plan1->is_free() || ( $old_plan1->is_recurring() && $new_plan->is_recurring() ) ) {
									$is_update_plan = true;
								} else {
									$change_act = 'immediate';
									if ( $old_plan1->enable_upgrade_downgrade_action == 1 ) {
										if ( ! empty( $old_plan1->downgrade_plans ) && in_array( $new_plan->ID, $old_plan1->downgrade_plans ) ) {
											$change_act = $old_plan1->downgrade_action;
										}
										if ( ! empty( $old_plan1->upgrade_plans ) && in_array( $new_plan->ID, $old_plan1->upgrade_plans ) ) {
											$change_act = $old_plan1->upgrade_action;
										}
									}
									$subscr_effective = $oldPlanData['arm_expire_plan'];
									if ( $change_act == 'on_expire' && ! empty( $subscr_effective ) ) {
										$is_update_plan                      = false;
										$oldPlanData['arm_subscr_effective'] = $subscr_effective;
										$oldPlanData['arm_change_plan_to']   = $new_plan_id;
										update_user_meta( $user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData );
									}
								}
							}
						}

						if ( $is_update_plan ) {
							update_user_meta( $user_id, 'arm_user_plan_ids', array( $new_plan_id ) );

							if ( ! empty( $old_plan ) ) {
								foreach ( $old_plan as $old_plan_id ) {
									$user->remove_cap( 'armember_access_plan_' . $old_plan_id );
									delete_user_meta( $user_id, 'arm_user_plan_' . $old_plan_id );
								}

								$plan_id_role_array = $this->arm_get_plan_role_by_id( $old_plan );
								if ( ! empty( $plan_id_role_array ) && is_array( $plan_id_role_array ) ) {
									foreach ( $plan_id_role_array as $key => $value ) {
										$plan_role = $value['arm_subscription_plan_role'];
										if ( ! empty( $plan_role ) ) {
											$user->remove_role( $plan_role );
											$arm_default_wordpress_role = get_option( 'default_role', 'subscriber' );
											$user->set_role( $arm_default_wordpress_role );
										}
									}
								}
							}

							if ( ! empty( $new_plan->plan_role ) ) {
								$user->set_role( $new_plan->plan_role );
							}
						}

						if ( $is_update_plan ) {
							$defaultPlanData     = $this->arm_default_plan_array();
							$userPlanDatameta    = get_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, true );
							$userPlanDatameta    = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
							$newPlanData         = shortcode_atts( $defaultPlanData, $userPlanDatameta );
							$arm_old_plan_detail = $newPlanData['arm_current_plan_detail'];

							update_user_meta( $user_id, 'arm_user_last_plan', $new_plan_id );

							$user->add_cap( 'armember_access_plan_' . $new_plan_id );

							// Start Plan
							$start_time = strtotime( current_time( 'mysql' ) );
							if ( $new_plan->is_recurring() ) {

								if ( $new_plan->has_trial_period() ) {
									if ( isset( $old_plan ) && ! empty( $old_plan ) ) {
										$newPlanData['arm_completed_recurring'] = 1;
									} else {
										$trial_and_sub_start_date = $new_plan->arm_trial_and_plan_start_date( '', $payment_mode, true, $payment_cycle );
										$start_time               = isset( $trial_and_sub_start_date['subscription_start_date'] ) ? $trial_and_sub_start_date['subscription_start_date'] : '';
										if ( isset( $trial_and_sub_start_date['arm_expire_plan_trial'] ) && $trial_and_sub_start_date['arm_expire_plan_trial'] != '' ) {

											$newPlanData['arm_trial_end']           = $trial_and_sub_start_date['arm_expire_plan_trial'];
											$newPlanData['arm_trial_start']         = $trial_and_sub_start_date['arm_trial_start_date'];
											$newPlanData['arm_is_trial_plan']       = 1;
											$newPlanData['arm_completed_recurring'] = 0;
										}
									}
								} else {
									$newPlanData['arm_completed_recurring'] = 1;
								}
							} else {
								$payment_mode = '';
							}

							$newPlanData['arm_start_plan'] = $start_time;

							// Expire Plan
							$expire_time = $new_plan->arm_plan_expire_time( $start_time, $payment_mode, $payment_cycle );
							if ( $expire_time != false ) {
								$newPlanData['arm_expire_plan'] = $expire_time;
							}

							/* Set Current Plan Detail */
							$curPlanDetail                                    = (array) $new_plan->plan_detail;
							$curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;
							$newPlanData['arm_current_plan_detail']           = $curPlanDetail;
							$newPlanData['arm_payment_mode']                  = $payment_mode;
							$newPlanData['arm_payment_cycle']                 = $payment_cycle;
							update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $newPlanData );

							$arm_next_payment_date = $arm_members_class->arm_get_next_due_date( $user_id, $new_plan_id, true, $payment_cycle );
							if ( $arm_next_payment_date != '' ) {
								$newPlanData['arm_next_due_payment'] = $arm_next_payment_date;
							}
							$newPlanData['arm_user_gateway'] = 'bank_transfer';
							update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $newPlanData );

							/**
							 * Update User's Achievements.
							 */

							do_action( 'arm_after_user_plan_change', $user_id, $new_plan_id );
						}

						// Update User's Last Subscriptions
						$this->arm_add_membership_history( $user_id, $new_plan_id, 'new_subscription' );
						/**
						 * Send Email Notification for Successful Payment
						 */
						$arm_manage_communication->arm_user_plan_status_action_mail(
							array(
								'plan_id' => $new_plan_id,
								'user_id' => $user_id,
								'action'  => $mail_type,
							)
						);
					}
				} else {
					$mail_type = ( empty( $old_plan ) ) ? 'new_subscription' : 'renew_subscription';
					$user      = new WP_User( $user_id );
					$new_plan  = new ARM_Plan_Lite( $new_plan_id );
					if ( $new_plan->exists() && $new_plan->is_active() ) {

						$new_plan = apply_filters( 'arm_change_plan_before_user_renew_subscription', $new_plan, $user_id, $old_plan, $new_plan_id );
						if ( ! empty( $new_plan->plan_role ) ) {
							$user->set_role( $new_plan->plan_role );
						}

						$defaultPlanData  = $this->arm_default_plan_array();
						$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, true );
						$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
						$oldPlanData      = shortcode_atts( $defaultPlanData, $userPlanDatameta );

						$arm_old_plan_detail        = $oldPlanData['arm_current_plan_detail'];
						$arm_user_old_payment_cycle = $arm_user_old_payment_mode = '';
						if ( ! empty( $arm_old_plan_detail ) ) {
							$arm_user_old_plan_info = new ARM_Plan_Lite( 0 );
							$arm_user_old_plan_info->init( (object) $arm_old_plan_detail );
							$arm_user_old_payment_cycle = isset( $arm_old_plan_detail['arm_user_selected_payment_cycle'] ) ? $arm_old_plan_detail['arm_user_selected_payment_cycle'] : '';
							$arm_user_old_payment_mode  = isset( $arm_old_plan_detail['arm_user_old_payment_mode'] ) ? $arm_old_plan_detail['arm_user_old_payment_mode'] : '';
						} else {
							$arm_user_old_plan_info = new ARM_Plan_Lite( $new_plan_id );
						}

						$arm_user_old_plan_data = $arm_user_old_plan_info->prepare_recurring_data( $arm_user_old_payment_cycle );

						$planObj = new ARM_Plan_Lite( $new_plan_id );

						if ( $planObj->is_recurring() ) {

							if ( $arm_user_old_payment_mode != 'manual_subscription' ) {
								$plan_action = 'renew_subscription';
							} else {
								$plan_action = 'renew_or_recurring';
							}

							$total_recurrence                 = $arm_user_old_plan_data['rec_time'];
							$completed_rec                    = $oldPlanData['arm_completed_recurring'];
							$expiry_time                      = $oldPlanData['arm_expire_plan'];
							$oldPlanData['arm_payment_mode']  = 'manual_subscription';
							$oldPlanData['arm_payment_cycle'] = $payment_cycle;

							update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData );
							if ( ( ( $completed_rec == $total_recurrence || $completed_rec === '' ) && $total_recurrence != 'infinite' ) || $plan_action == 'renew_subscription' ) {
								do_action( 'arm_before_renew_user_plans', $user_id, $old_plan, $new_plan_id, $new_plan );
								$start_time = $expiry_time;

								// Code for keep started plan date show at Current Membership Shortcode which was showing future date.
								$arm_check_current_time = strtotime( current_time( 'mysql' ) );
								if ( $arm_check_current_time < $oldPlanData['arm_expire_plan'] ) {
									if ( empty( $oldPlanData['arm_started_plan_date'] ) ) {
										$oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
									} elseif ( $oldPlanData['arm_start_plan'] < $oldPlanData['arm_started_plan_date'] ) {
										$oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
									}
								}

								$oldPlanData['arm_start_plan'] = $start_time;
								$expire_time                   = $new_plan->arm_plan_expire_time( $start_time, $payment_mode, $payment_cycle );
								if ( $expire_time != false ) {
									$oldPlanData['arm_expire_plan'] = $expire_time;
								}
								$oldPlanData['arm_completed_recurring']           = 1;
								$curPlanDetail                                    = (array) $new_plan->plan_detail;
								$curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;
								$oldPlanData['arm_current_plan_detail']           = $curPlanDetail;
								update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData );
								$arm_next_payment_date = $arm_members_class->arm_get_next_due_date( $user_id, $new_plan_id, true, $payment_cycle );
								if ( $arm_next_payment_date != '' ) {
									$oldPlanData['arm_next_due_payment'] = $arm_next_payment_date;
								}
								$oldPlanData['arm_sent_msgs']           = array();
								$oldPlanData['arm_trial_end']           = '';
								$oldPlanData['arm_trial_start']         = '';
								$oldPlanData['arm_is_trial_plan']       = 0;
								$oldPlanData['arm_is_user_in_grace']    = 0;
								$oldPlanData['arm_grace_period_end']    = '';
								$oldPlanData['arm_grace_period_action'] = '';
								$oldPlanData['arm_cencelled_plan']      = '';
								$oldPlanData['arm_subscr_effective']    = '';
								$oldPlanData['arm_change_plan_to']      = '';
								$oldPlanData['arm_user_gateway']        = 'bank_transfer';
								update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData );
								$this->arm_add_membership_history( $user_id, $new_plan_id, 'renew_subscription' );
								$arm_manage_communication->arm_user_plan_status_action_mail(
									array(
										'plan_id' => $new_plan_id,
										'user_id' => $user_id,
										'action'  => $mail_type,
									)
								);
								do_action( 'arm_after_user_plan_renew', $user_id, $new_plan_id );
							} else {

								$completed_rec        = $oldPlanData['arm_completed_recurring'];
								$old_next_due_payment = $oldPlanData['arm_next_due_payment'];

								$now = strtotime( current_time( 'mysql' ) );
								if ( $now < $old_next_due_payment ) {

									if ( $arm_last_payment_status != 'failed' ) {
										$completed_rec                          = ! empty( $completed_rec ) ? $completed_rec : 0;
										$oldPlanData['arm_completed_recurring'] = ( $completed_rec + 1 );
										update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData );
										$arm_next_payment_date = $arm_members_class->arm_get_next_due_date( $user_id, $new_plan_id, false, $payment_cycle );
										if ( $arm_next_payment_date != '' ) {
											$oldPlanData['arm_next_due_payment'] = $arm_next_payment_date;
										}
									}
								} else {
									$completed_rec                          = ! empty( $completed_rec ) ? $completed_rec : 0;
									$oldPlanData['arm_completed_recurring'] = ( $completed_rec + 1 );
									update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData );
									$arm_next_payment_date = $arm_members_class->arm_get_next_due_date( $user_id, $new_plan_id, false, $payment_cycle );
									if ( $arm_next_payment_date != '' ) {
										$oldPlanData['arm_next_due_payment'] = $arm_next_payment_date;
									}
								}
								$oldPlanData['arm_user_gateway']        = 'bank_transfer';
								$oldPlanData['arm_is_user_in_grace']    = 0;
								$oldPlanData['arm_grace_period_end']    = '';
								$oldPlanData['arm_grace_period_action'] = '';
								update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData );
								do_action( 'arm_after_user_recurring_payment_done', $user_id, $new_plan_id );
							}
						} else {

							do_action( 'arm_before_renew_user_plans', $user_id, $old_plan, $new_plan_id, $new_plan );

							// Code for keep started plan date show at Current Membership Shortcode which was showing future date.
							$arm_check_current_time = strtotime( current_time( 'mysql' ) );
							if ( $arm_check_current_time < $oldPlanData['arm_expire_plan'] ) {
								if ( empty( $oldPlanData['arm_started_plan_date'] ) ) {
									$oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
								} elseif ( $oldPlanData['arm_start_plan'] < $oldPlanData['arm_started_plan_date'] ) {
									$oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
								}
							}

							// Start Plan
							$start_time = strtotime( current_time( 'mysql' ) );
							if ( $planObj->is_paid() && ! $planObj->is_lifetime() && ! $planObj->is_recurring() ) {
								$payment_type = $arm_user_old_plan_info->options['payment_type'];
								if ( $payment_type == 'one_time' ) {
									$start_time = ! empty( $oldPlanData['arm_expire_plan'] ) ? $oldPlanData['arm_expire_plan'] : $start_time;
								}
							}
							$oldPlanData['arm_start_plan'] = $start_time;

							// Expire Plan
							$expire_time = $new_plan->arm_plan_expire_time( $start_time, $payment_mode, $payment_cycle );

							if ( $expire_time != false ) {
								$oldPlanData['arm_expire_plan'] = $expire_time;
							}

							$curPlanDetail                                    = (array) $new_plan->plan_detail;
							$curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;
							$oldPlanData['arm_current_plan_detail']           = $curPlanDetail;

							$oldPlanData['arm_payment_mode']        = '';
							$oldPlanData['arm_payment_cycle']       = '';
							$oldPlanData['arm_sent_msgs']           = array();
							$oldPlanData['arm_trial_end']           = 0;
							$oldPlanData['arm_trial_start']         = '';
							$oldPlanData['arm_is_trial_plan']       = '';
							$oldPlanData['arm_is_user_in_grace']    = 0;
							$oldPlanData['arm_grace_period_end']    = '';
							$oldPlanData['arm_grace_period_action'] = '';
							$oldPlanData['arm_completed_recurring'] = '';
							$oldPlanData['arm_next_due_payment']    = '';
							$oldPlanData['arm_cencelled_plan']      = '';
							$oldPlanData['arm_subscr_effective']    = '';
							$oldPlanData['arm_change_plan_to']      = '';
							$oldPlanData['arm_user_gateway']        = 'bank_transfer';

							update_user_meta( $user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData );
							$this->arm_add_membership_history( $user_id, $new_plan_id, 'renew_subscription' );
							$arm_manage_communication->arm_user_plan_status_action_mail(
								array(
									'plan_id' => $new_plan_id,
									'user_id' => $user_id,
									'action'  => $mail_type,
								)
							);
							do_action( 'arm_after_user_plan_renew', $user_id, $new_plan_id );
						}
						// Update User's Last Subscriptions

					}
				}
			}
		}

		function arm_get_user_membership_detail( $user_id = 0, $plan_id = 0, $action = 'new_subscription', $action_by = '' ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways, $arm_manage_communication;
			$membershipData = array();
			if ( ! empty( $user_id ) && $user_id != 0 ) {
				$current_user_user_id = $user_id;
				if(is_admin())
				{
					$current_user_user_id = (is_user_logged_in()) ? get_current_user_id() : $user_id;
				}
				$membershipData['current_user'] = $current_user_user_id;
				$membershipData['plan_id']      = $plan_id;
				$membershipData['action_by']    = $action_by;
				$defaultPlanData                = $this->arm_default_plan_array();
				$userPlanDatameta               = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
				$userPlanDatameta               = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
				$planData                       = shortcode_atts( $defaultPlanData, $userPlanDatameta );
				$planDetail                     = !empty($userPlanDatameta['arm_current_plan_detail']) ? $userPlanDatameta['arm_current_plan_detail'] : $planData['arm_current_plan_detail'];
				$payment_cycle                  = !empty($userPlanDatameta['arm_payment_cycle']) ? $userPlanDatameta['arm_payment_cycle'] : $planData['arm_payment_cycle'];
				if ( ! empty( $planDetail ) ) {
					$plan = new ARM_Plan_Lite( 0 );
					$plan->init( (object) $planDetail );
				} else {
					$plan = new ARM_Plan_Lite( $plan_id );
				}

				if ( $plan->is_recurring() ) {
					$recurring_data = $plan->prepare_recurring_data( $payment_cycle );
					$amount         = $recurring_data['amount'];
				} else {
					$amount = ! empty( $plan->amount ) ? $plan->amount : 0;
				}

				if ( $plan->exists() ) {
					$membershipData['plan_name']         = $plan->name;
					$membershipData['plan_amount']       = $amount;
					$membershipData['plan_type']         = $plan->type;
					$membershipData['plan_payment_type'] = $plan->payment_type;
					$membershipData['plan_text']         = $plan->user_plan_text( false, $payment_cycle );
					$membershipData['plan_detail']       = (array) $plan->plan_detail;
				}
				$changePlanTo = $planData['arm_change_plan_to'];

				$membershipData['arm_subscr_effective'] = $planData['arm_subscr_effective'];
				$membershipData['arm_change_plan_to']   = $changePlanTo;

				if ( ! empty( $changePlanTo ) && $changePlanTo == $plan_id ) {
					$membershipData['start'] = $planData['arm_subscr_effective'];
				} else {
					/*rpt_log changes for trial recurring start date if payment done by admin*/
					if(isset($plan->recurring_data['trial']) && !empty($plan->recurring_data['trial'])) {
					$plan_start_date = empty($planData['arm_start_plan']) ? current_time('mysql') : date('Y-m-d H:i:s', $planData['arm_start_plan']); //phpcs:ignore
					$start_date = "";    
									
					if ( "D" == $plan->recurring_data['trial']['period'] ) {
						//$day += $plan->recurring_data['trial']['interval'];
						$start_date = date('Y-m-d H:i:s', strtotime($plan_start_date . " + ".$plan->recurring_data['trial']['interval']." days")); //phpcs:ignore
					} else if ( "M" == $plan->recurring_data['trial']['period'] ) {
						//$month += $plan->recurring_data['trial']['interval'];
						$start_date = date('Y-m-d H:i:s', strtotime($plan_start_date . " + ".$plan->recurring_data['trial']['interval']." months")); //phpcs:ignore
					} else if ( "Y" == $plan->recurring_data['trial']['period'] ) {
						//$year += $plan->recurring_data['trial']['interval'];
						$start_date = date('Y-m-d H:i:s', strtotime($plan_start_date . " + ".$plan->recurring_data['trial']['interval']." years")); //phpcs:ignore
					}

					$membershipData['start'] = strtotime($start_date);
			
					} else {
						if(empty($planData['arm_start_plan'])) {
							$membershipData['start'] = strtotime(current_time('mysql'));    
						} else {
							$membershipData['start'] = $planData['arm_start_plan'];
						}
					}    
					$membershipData['expire'] = $planData['arm_expire_plan'];
					$membershipData['arm_next_due_payment'] = $planData['arm_next_due_payment'];
				}
				$using_gateway             = $planData['arm_user_gateway'];
				$membershipData['gateway'] = ( ! empty( $using_gateway ) ) ? $using_gateway : 'manual';
				$payment_data              = array();

				$subscr_id = $planData['arm_subscr_id'];
				if ( ! empty( $subscr_id ) ) {
					$payment_data['arm_subscr_id'] = $subscr_id;
				}
				$membershipData['payment_data'] = $payment_data;
			}
			return $membershipData;
		}

		function arm_membership_history_paging_action() {
			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_membership_history_paging_action' ) { //phpcs:ignore
				global $wp, $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways, $arm_subscription_plans, $arm_capabilities_global;

				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_members'], '1' );//phpcs:ignore --Reason:Verifying nonce

				$user_id            = isset( $_POST['user_id'] ) ? intval($_POST['user_id']) : 0; //phpcs:ignore
				$current_page       = isset( $_POST['page'] ) ? intval($_POST['page']) : 1; //phpcs:ignore
				$per_page           = isset( $_POST['per_page'] ) ? intval($_POST['per_page']) : 5; //phpcs:ignore
				$plan_id_name_array = $arm_subscription_plans->arm_get_plan_name_by_id_from_array();
				echo $this->arm_get_user_membership_history( $user_id, $current_page, $per_page, $plan_id_name_array ); //phpcs:ignore
			}
			exit;
		}

		function arm_get_user_membership_history( $user_id = 0, $current_page = 1, $perPage = 2, $plan_id_name_array = array() ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways;
			$date_format = $arm_global_settings->arm_get_wp_date_format();
			$historyHtml = '';
			if ( ! empty( $user_id ) && $user_id != 0 ) {

				$nowDate = current_time( 'mysql' );

				$perPage = ( ! empty( $perPage ) && is_numeric( $perPage ) ) ? $perPage : 5;
				$offset  = 0;
				if ( ! empty( $current_page ) && $current_page > 1 ) {
					$offset = ( $current_page - 1 ) * $perPage;
				}
				$historyLimit   = ( ! empty( $perPage ) ) ? " LIMIT $offset, $perPage " : '';
				$totalRecord    = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(`arm_activity_id`) FROM `".$ARMemberLite->tbl_arm_activity."` WHERE `arm_type`=%s AND `arm_user_id`=%d",'membership',$user_id) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_activity is a table name
				$historyRecords = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `".$ARMemberLite->tbl_arm_activity."` WHERE `arm_type`=%s AND `arm_user_id`=%d AND `arm_action` != %s ORDER BY `arm_activity_id` DESC ".$historyLimit,'membership',$user_id,'recurring_subscription'), ARRAY_A );//phpcs:ignore --Reason $tbl_arm_activity is a table name
				if ( ! empty( $historyRecords ) ) {

					$user_plans = get_user_meta( $user_id, 'arm_user_plan_ids', true );

					$user_plans = ! empty( $user_plans ) ? $user_plans : array();
					$user_plan  = isset( $user_plans[0] ) ? $user_plans[0] : 0;

					$user_suspended_plans = get_user_meta( $user_id, 'arm_user_suspended_plan_ids', true );
					$user_suspended_plans = ( isset( $user_suspended_plans ) && ! empty( $user_suspended_plans ) ) ? $user_suspended_plans : array();

					$curPlanName = isset( $plan_id_name_array[ $user_plan ] ) ? $plan_id_name_array[ $user_plan ] : '';

					$historyHtml           .= '<div class="arm_membership_history_wrapper" data-user_id="' . $user_id . '">';
					$historyHtml           .= '<table class="form-table arm_member_last_subscriptions_table" width="100%">';
					$historyHtml           .= '<tr>';
					$historyHtml           .= '<td>' . esc_html__( 'Plan', 'armember-membership' ) . '</td>';
					$historyHtml           .= '<td>' . esc_html__( 'Type', 'armember-membership' ) . '</td>';
					$historyHtml           .= '<td>' . esc_html__( 'Start Date', 'armember-membership' ) . '</td>';
					$historyHtml           .= '<td>' . esc_html__( 'Expire Date', 'armember-membership' ) . '</td>';
					$historyHtml           .= '<td>' . esc_html__( 'Amount', 'armember-membership' ) . '</td>';
					$historyHtml           .= '<td>' . esc_html__( 'Payment Gateway', 'armember-membership' ) . '</td>';
					$historyHtml           .= '</tr>';
					$isCurrent              = false;
					$item_id_arrray         = array();
					$defaultPlanData        = $this->arm_default_plan_array();
					$change_plan_array      = array();
					$subscr_effective_array = array();
					$change_plan            = '';
					$subscr_effective       = '';

					foreach ( $historyRecords as $mh ) {

						$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $mh['arm_item_id'], true );
						$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
						$planData         = shortcode_atts( $defaultPlanData, $userPlanDatameta );
						if ( ! empty( $planData['arm_change_plan_to'] ) ) {
							if ( ! in_array( $planData['arm_change_plan_to'], $change_plan_array ) ) {
								$change_plan_array[] = isset( $planData['arm_change_plan_to'] ) ? $planData['arm_change_plan_to'] : '';
							}
							$subscr_effective_array[ $planData['arm_change_plan_to'] ] = isset( $planData['arm_subscr_effective'] ) ? $planData['arm_subscr_effective'] : '';
						}
					}

					foreach ( $historyRecords as $mh ) {
						$mh_content = maybe_unserialize( $mh['arm_content'] );
						if ( $user_plan == $mh['arm_item_id'] ) {
							$default_plan_name = $curPlanName;
						} else {
							$default_plan_name = $this->arm_get_plan_name_by_id( $mh['arm_item_id'] );
						}

						$plan_name = ( isset( $mh['arm_activity_plan_name'] ) ) ? $mh['arm_activity_plan_name'] : $default_plan_name;
						if ( in_array( $mh['arm_item_id'], $user_plans ) && ! in_array( $mh['arm_item_id'], $item_id_arrray ) ) {

							if ( strtotime($mh['arm_activity_plan_start_date']) <= strtotime( $nowDate ) ) {
								if ( in_array( $mh['arm_item_id'], $user_suspended_plans ) ) {
									$plan_name .= ' <span style="color: red;">(' . esc_html__( 'Suspended', 'armember-membership' ) . ')</span>';
								} else {

									$plan_name .= ' <span class="arm_item_status_text active">(' . esc_html__( 'Current', 'armember-membership' ) . ')</span>';
								}

								$item_id_arrray[] = $mh['arm_item_id'];
							}
						}

						if ( in_array( $mh['arm_item_id'], $change_plan_array ) ) {
							$change_plan      = $mh['arm_item_id'];
							$subscr_effective = isset( $subscr_effective_array[ $mh['arm_item_id'] ] ) ? $subscr_effective_array[ $mh['arm_item_id'] ] : '';
							$newStartDate     = date_i18n( $date_format, $subscr_effective );
						} else {
							$newStartDate = date_i18n( $date_format, strtotime($mh['arm_activity_plan_start_date'] ));
						}

						$historyHtml .= '<tr class="arm_member_last_subscriptions_data">';
						$historyHtml .= '<td>' . $plan_name . '</td>';
						$historyHtml .= '<td>';
						switch ( $mh['arm_action'] ) {
							case 'new_subscription':
								$historyHtml .= esc_html__( 'New Subscription', 'armember-membership' );
								break;
							case 'failed_payment':
								$historyHtml         .= esc_html__( 'Failed Payment', 'armember-membership' );
								$mh['arm_activity_plan_end_date'] = strtotime( $mh['arm_date_recorded'] );
								break;
							case 'cancel_payment':
							case 'cancel_subscription':
								$historyHtml         .= esc_html__( 'Cancel Subscription', 'armember-membership' );
								$mh['arm_activity_plan_end_date'] = strtotime( $mh['arm_date_recorded'] );
								break;
							case 'eot':
								$historyHtml .= esc_html__( 'Expire Subscription', 'armember-membership' );
								/* manual subscription if user expired */
								$mh['arm_activity_plan_end_date'] = ( $mh['arm_activity_plan_end_date'] );
								break;
							case 'change_subscription':
								$historyHtml .= esc_html__( 'Change Subscription', 'armember-membership' );
								break;
							case 'renew_subscription':
								$historyHtml .= esc_html__( 'Renew Subscription', 'armember-membership' );
								break;
							case 'recurring_subscription':
								$historyHtml .= esc_html__( 'Recurring Payment', 'armember-membership' );
								break;
							default:
								break;
						}
						if ( isset( $mh_content['current_user'] ) && $mh_content['current_user'] != '0' && $mh_content['current_user'] != $mh['arm_user_id'] ) {
							if ( isset( $mh_content['action_by'] ) && $mh_content['action_by'] == 'terminate' ) {
								$historyHtml .= '<div style="font-size: 12px;"><em>(' . esc_html__( 'Admin Terminated Account', 'armember-membership' ) . ')</em></div>';
							} else {
								$historyHtml .= '<div style="font-size: 12px;"><em>(' . esc_html__( 'Action By Admin', 'armember-membership' ) . ')</em></div>';
							}
						} elseif ( isset( $mh_content['action_by'] ) && $mh_content['action_by'] == 'system' ) {
							$historyHtml .= '<div style="font-size: 12px;"><em>(' . esc_html__( 'Action by system', 'armember-membership' ) . ')</em></div>';
						} elseif ( isset( $mh_content['action_by'] ) && $mh_content['action_by'] == 'close_account' ) {
							$historyHtml .= '<div style="font-size: 12px;"><em>(' . esc_html__( 'User Closed Account', 'armember-membership' ) . ')</em></div>';
						}
						$historyHtml .= '</td>';
						$startDetail  = '-';
						if ( isset( $mh['arm_activity_plan_start_date'] ) && ! empty( $mh['arm_activity_plan_start_date'] ) ) {
							$startDetail = '';
							$start_time = strtotime($mh['arm_activity_plan_start_date']);
							if ( ! in_array( $mh['arm_item_id'], $user_plans ) && ! empty( $change_plan ) && $subscr_effective > strtotime( $nowDate ) ) {
								$change_plan_name = $this->arm_get_plan_name_by_id( $change_plan );
								$startDetail     .= "<div class='arm_member_detail_confirm_wrapper armGridActionTD'>";
								$startDetail     .= '<div>' . esc_html__( 'Effective from', 'armember-membership' ) . '</div>';
								$startDetail     .= "<a href='javascript:void(0)' onclick='showConfirmBoxCallback({$start_time});'>{$newStartDate}</a>";
								$startDetail     .= "<div class='arm_confirm_box arm_confirm_box_".esc_attr($start_time)."' id='arm_confirm_box_".esc_attr($start_time)."'>";
								$startDetail     .= "<div class='arm_confirm_box_body'>";
								$startDetail     .= "<div class='arm_confirm_box_arrow'></div>";
								$startDetail     .= "<div class='arm_confirm_box_text'>";
								$startDetail     .= "<div class='arm_effective_detail_rows'>";
								$startDetail     .= "<div class='arm_effective_detail_label'>" . esc_html__( 'Current plan', 'armember-membership' ) . ':</div>';
								$startDetail     .= "<div class='arm_effective_detail_value'>{$curPlanName}</div>";
								$startDetail     .= '</div>';
								$startDetail     .= "<div class='arm_effective_detail_rows'>";
								$startDetail     .= "<div class='arm_effective_detail_label'>" . esc_html__( 'Plan expiration date', 'armember-membership' ) . ':</div>';
								$startDetail     .= "<div class='arm_effective_detail_value'>{$newStartDate}</div>";
								$startDetail     .= '</div>';
								$startDetail     .= "<div class='arm_effective_detail_rows'>";
								$startDetail     .= "<div class='arm_effective_detail_label'>" . esc_html__( 'New plan', 'armember-membership' ) . " ({$change_plan_name}) " . esc_html__( 'will be effective from', 'armember-membership' ) . ':</div>';
								$startDetail     .= "<div class='arm_effective_detail_value'>{$newStartDate}</div>";
								$startDetail     .= '</div>';
								$startDetail     .= '</div>';
								$startDetail     .= "<div class='arm_confirm_box_btn_container'>";
								$startDetail     .= "<button type='button' class='arm_confirm_box_btn armemailaddbtn' onclick='hideConfirmBoxCallback();'>" . esc_html__( 'Ok', 'armember-membership' ) . '</button>';
								$startDetail     .= '</div>';
								$startDetail     .= '</div>';
								$startDetail     .= '</div>';
								$startDetail     .= '</div>';
							} else {
								$startDetail .= $newStartDate;
							}
						}
						$historyHtml .= '<td>' . $startDetail . '</td>';
						$historyHtml .= '<td>';
						if ( isset( $mh['arm_activity_plan_end_date'] ) && ! empty( $mh['arm_activity_plan_end_date'] )  && $mh['arm_activity_plan_end_date'] != "0000-00-00 00:00:00" ) {
							$historyHtml .= date_i18n( $date_format, strtotime($mh['arm_activity_plan_end_date'] ));
						} else {
							$historyHtml .= '-';
						}
						$historyHtml .= '</td>';
						$historyHtml .= '<td>';
						if ( in_array( $mh['arm_action'], array( 'new_subscription', 'change_subscription', 'renew_subscription', 'recurring_subscription' ) ) && isset( $mh_content['plan_text'] ) && ! empty( $mh_content['plan_text'] ) ) {
							$arm_paid_amount = $mh_content['plan_text'];
							$historyHtml    .= apply_filters( 'arm_change_membership_history_paid_amount', $arm_paid_amount, $mh );
						} else {
							$historyHtml .= '-';
						}
						$historyHtml .= '</td>';
						$historyHtml .= '<td>';
						if ( isset( $mh['arm_activity_payment_gateway'] ) && ! empty( $mh['arm_activity_payment_gateway'] ) ) {
							$historyHtml .= $arm_payment_gateways->arm_gateway_name_by_key( $mh['arm_activity_payment_gateway'] );
						} else {
							$historyHtml .= '-';
						}
						$historyHtml .= '</td>';
						$historyHtml .= '</tr>';
					}
					$historyHtml  .= '</table>';
					$historyHtml  .= '<div class="arm_membership_history_pagination_block">';
					$historyPaging = $arm_global_settings->arm_get_paging_links( $current_page, $totalRecord, $perPage, 'membership_history' );
					$wpnonce = wp_create_nonce( 'arm_wp_nonce' );
					$historyHtml .= '<input type="hidden" name="arm_wp_nonce" value="'.esc_attr($wpnonce).'"/>';
					$historyHtml  .= '<div class="arm_membership_history_paging_container">' . $historyPaging . '</div>';
					$historyHtml  .= '</div>';
					$historyHtml  .= '</div>';
					
				}
			}
			return $historyHtml;
		}

		function arm_get_membership_history( $user_id = 0, $limit = '' ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings;
			$history = array();
			if ( ! empty( $user_id ) && $user_id != 0 ) {
				$limit          = ( ! empty( $limit ) ) ? ' LIMIT ' . $limit : '';
				$actType        = 'membership';
				$result_history = $wpdb->get_results( $wpdb->prepare("SELECT `arm_activity_id`, `arm_action`, `arm_content`, `arm_item_id`, `arm_ip_address`, `arm_date_recorded` FROM `".$ARMemberLite->tbl_arm_activity."` WHERE `arm_type`=%s AND `arm_user_id`=%d ORDER BY `arm_activity_id` DESC ".$limit,$actType,$user_id,$limit), ARRAY_A );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_activity is a table name
				if ( ! empty( $result_history ) ) {
					foreach ( $result_history as $mh ) {
						$activity_id             = $mh['arm_activity_id'];
						$mh['arm_type']          = $actType;
						$mh['arm_user_id']       = $user_id;
						$mh['arm_content']       = maybe_unserialize( $mh['arm_content'] );
						$history[ $activity_id ] = $mh;
					}
				}
			}
			return $history;
		}

		function arm_add_membership_history( $user_id = 0, $plan_id = 0, $action = 'new_subscription', $extraVars = array(), $action_by = '' ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways, $arm_manage_communication;
			if ( ! empty( $user_id ) && $user_id != 0 ) {
				$ip_address         = $ARMemberLite->arm_get_ip_address();
				$content            = $this->arm_get_user_membership_detail( $user_id, $plan_id, $action, $action_by );
				$content['arm_currency'] = $arm_payment_gateways->arm_get_global_currency();
				$arm_plan_name = $content['plan_name'];
                $arm_plan_type = $content['plan_type'];
                $arm_plan_payment_gateway = $content['gateway'];
                $arm_plan_amount = $content['plan_amount'];
                $arm_plan_start_date = !empty($content['start']) ? date('Y-m-d H:i:s',$content['start']) :''; //phpcs:ignore
                $arm_plan_end_date = !empty($content['expire']) ? date('Y-m-d H:i:s',$content['expire']): ''; //phpcs:ignore
                $arm_plan_next_cycle_date = !empty($content['arm_next_due_payment']) ? date('Y-m-d H:i:s',$content['arm_next_due_payment']) : ''; //phpcs:ignore
				$membershipActivity = array(
					'arm_user_id'       => $user_id,
					'arm_type'          => 'membership',
					'arm_action'        => $action,
					'arm_content'       => maybe_serialize( $content ),
					'arm_item_id'       => $plan_id,
					'arm_link'          => '',
					'arm_activity_plan_name'=>$arm_plan_name,
                    'arm_activity_plan_type'=>$arm_plan_type,
                    'arm_activity_payment_gateway'=>$arm_plan_payment_gateway,
                    'arm_activity_plan_amount'=>$arm_plan_amount,
                    'arm_activity_plan_start_date'=>$arm_plan_start_date,
                    'arm_activity_plan_end_date'=>$arm_plan_end_date,
                    'arm_activity_plan_next_cycle_date'=>$arm_plan_next_cycle_date,
					'arm_ip_address'    => $ip_address,
					'arm_date_recorded' => date( 'Y-m-d H:i:s' ), //phpcs:ignore
				);
				$membershipActivity = apply_filters( 'arm_change_membership_activity_before_save', $membershipActivity );
				$_activity          = $wpdb->insert( $ARMemberLite->tbl_arm_activity, $membershipActivity ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				if ( $_activity ) {
					return $wpdb->insert_id;
				}
			}
			return;
		}

		function arm_get_total_members_in_plan( $plan_id = 0 ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways;
			$res = 0;
			if ( ! empty( $plan_id ) && $plan_id != 0 ) {
				$user_arg = array(
					'meta_key'     => 'arm_user_plan_ids', // phpcs:ignore
					'meta_value'   => $plan_id, // phpcs:ignore
					'meta_compare' => 'like',
					'role__not_in' => 'administrator',
				);
				$users    = get_users( $user_arg );
				$res      = 0;
				foreach ( $users as $user ) {
					$plan_ids = get_user_meta( $user->ID, 'arm_user_plan_ids', true );
					if ( ! empty( $plan_ids ) && is_array( $plan_ids ) ) {
						if ( in_array( $plan_id, $plan_ids ) ) {
							$res++;
						}
					}
				}
			}
			return $res;
		}

		function arm_get_payment_detail_by_plan( $plan_id = 0 ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways;
			$array_return = array();
			if ( ! empty( $plan_id ) && $plan_id != 0 ) {
				$res         = $wpdb->get_row( $wpdb->prepare("SELECT `arm_subscription_plan_type`, `arm_subscription_plan_options`, `arm_subscription_plan_amount` FROM ".$ARMemberLite->tbl_arm_subscription_plans." WHERE `arm_subscription_plan_id`=%d",$plan_id) );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_subscription_plans is a table name. False Positive alarm
				$plan_type   = $res->arm_subscription_plan_type;
				$plan_option = maybe_unserialize( $res->arm_subscription_plan_options );
				$plan_amount = $res->arm_subscription_plan_amount;
				if ( isset( $plan_option['access_type'] ) && $plan_option['access_type'] == 'lifetime' ) {
					$array_return = array(
						'access_type' => 'lifetime',
						'plan_type'   => $plan_type,
						'plan_amount' => $plan_amount,
					);
				} elseif ( isset( $plan_option['access_type'] ) && $plan_option['access_type'] == 'finite' ) {
					if ( $plan_option['payment_type'] == 'subscription' ) {
						$rec_time            = $plan_option['recurring']['time'];
						$rec_type            = $plan_option['recurring']['type'];
						$rec_display_type    = '';
						$rec_display_type_ly = '';
						$rec_per             = '';
						if ( $rec_type == 'D' ) {
							$rec_display_type    = esc_html__( 'Day(s)', 'armember-membership' );
							$rec_display_type_ly = esc_html__( 'Daily', 'armember-membership' );
							$rec_per             = $plan_option['recurring']['days'];
						} elseif ( $rec_type == 'M' ) {
							$rec_display_type    = esc_html__( 'Months(s)', 'armember-membership' );
							$rec_display_type_ly = esc_html__( 'Monthly', 'armember-membership' );
							$rec_per             = $plan_option['recurring']['months'];
						} elseif ( $rec_type == 'W' ) {
							$rec_display_type    = esc_html__( 'Week(s)', 'armember-membership' );
							$rec_display_type_ly = esc_html__( 'Weekly', 'armember-membership' );
							$rec_per             = $plan_option['recurring']['weeks'];
						} elseif ( $rec_type == 'Y' ) {
							$rec_display_type    = esc_html__( 'Year(s)', 'armember-membership' );
							$rec_display_type_ly = esc_html__( 'Yearly', 'armember-membership' );
							$rec_per             = $plan_option['recurring']['years'];
						}

						$array_return = array(
							'access_type'     => 'finite',
							'plan_type'       => $plan_type,
							'plan_amount'     => $plan_amount,
							'type'            => $rec_type,
							'display_type'    => $rec_display_type,
							'plan_period'     => $rec_per,
							'rec_time'        => $rec_time,
							'payment_type'    => 'subscription',
							'display_type_ly' => $rec_display_type_ly,
						);
					} elseif ( $plan_option['payment_type'] == 'one_time' ) {
						$rec_type            = $plan_option['eopa']['type'];
						$rec_display_type    = '';
						$rec_display_type_ly = '';
						$rec_per             = '';
						if ( $rec_type == 'D' ) {
							$rec_display_type    = esc_html__( 'Day(s)', 'armember-membership' );
							$rec_display_type_ly = esc_html__( 'Daily', 'armember-membership' );
							$rec_per             = $plan_option['eopa']['days'];
						} elseif ( $rec_type == 'M' ) {
							$rec_display_type    = esc_html__( 'Months(s)', 'armember-membership' );
							$rec_display_type_ly = esc_html__( 'Monthly', 'armember-membership' );
							$rec_per             = $plan_option['eopa']['months'];
						} elseif ( $rec_type == 'W' ) {
							$rec_display_type    = esc_html__( 'Week(s)', 'armember-membership' );
							$rec_display_type_ly = esc_html__( 'Weekly', 'armember-membership' );
							$rec_per             = $plan_option['eopa']['weeks'];
						} elseif ( $rec_type == 'Y' ) {
							$rec_display_type    = esc_html__( 'Year(s)', 'armember-membership' );
							$rec_display_type_ly = esc_html__( 'Yearly', 'armember-membership' );
							$rec_per             = $plan_option['eopa']['years'];
						}
						$array_return = array(
							'access_type'     => 'finite',
							'plan_type'       => $plan_type,
							'plan_amount'     => $plan_amount,
							'type'            => $rec_type,
							'display_type'    => $rec_display_type,
							'plan_period'     => $rec_per,
							'payment_type'    => 'one_time',
							'display_type_ly' => $rec_display_type_ly,
						);
					}
				} elseif ( $plan_type == 'free' ) {
					$array_return = array( 'plan_type' => 'free' );
				}
			}
			return $array_return;
		}

		function arm_convert_to_format( $type, $count = 0 ) {
			$string_format = '';
			if ( ! empty( $type ) && $count != 0 ) {
				switch ( $type ) {
					case 'D':
						$datetime      = new DateTime();
						$diff          = $datetime->diff(
							new DateTime( date( 'Y-m-d H:i:s', strtotime( "$count Days" ) ) ) //phpcs:ignore
						);
						$year          = $diff->y;
						$month         = $diff->m;
						$days          = $diff->d;
						$year_s        = ( $year != 0 ) ? $year . ' ' . esc_html__( 'Year(s)', 'armember-membership' ) : '';
						$month_s       = ( $month != 0 ) ? $month . ' ' . esc_html__( 'Month(s)', 'armember-membership' ) : '';
						$day_s         = ( $days != 0 ) ? $days . ' ' . esc_html__( 'Day(s)', 'armember-membership' ) : '';
						$string_format = "$year_s $month_s $day_s";
						break;
					case 'M':
						$datetime      = new DateTime();
						$diff          = $datetime->diff(
							new DateTime( date( 'Y-m-d H:i:s', strtotime( "$count Months" ) ) ) //phpcs:ignore
						);
						$year          = $diff->y;
						$month         = $diff->m;
						$days          = $diff->d;
						$year_s        = ( $year != 0 ) ? $year . ' ' . esc_html__( 'Year(s)', 'armember-membership' ) : '';
						$month_s       = ( $month != 0 ) ? $month . ' ' . esc_html__( 'Month(s)', 'armember-membership' ) : '';
						$day_s         = ( $days != 0 ) ? $days . ' ' . esc_html__( 'Day(s)', 'armember-membership' ) : '';
						$string_format = "$year_s $month_s $day_s";
						break;
					case 'Y':
						$string_format = $count . ' ' . esc_html__( 'Year(s)', 'armember-membership' );
						break;
				}
			}
			return $string_format;
		}

		/**
		 * Add Custom Metaboxes in page/post/custom-post-type screen
		 */
		function arm_add_meta_boxes_func() {
			global $wpdb, $post, $pagenow, $ARMemberLite, $arm_global_settings, $arm_access_rules;
			if ( current_user_can( 'administrator' ) || current_user_can( 'arm_content_access_rules_metabox' ) ) {
				$totalPlans = $this->arm_get_total_plan_counts();
				if ( $totalPlans > 0 ) {
					$arm_screens       = array(
						'post' => 'post',
						'page' => 'page',
					);
					$custom_post_types = get_post_types(
						array(
							'public'   => true,
							'_builtin' => false,
							'show_ui'  => true,
						),
						'objects'
					);
					if ( ! empty( $custom_post_types ) ) {
						foreach ( $custom_post_types as $cpt ) {
							$arm_screens[ $cpt->name ] = $cpt->name;
						}
					}
					/* For remove meta box from plugin pages */
					$arm_current_screen = get_current_screen();
					if ( $arm_current_screen->post_type == 'page' && ! empty( $post->ID ) ) {
						$page_settings = $arm_global_settings->arm_get_single_global_settings( 'page_settings' );

						$arm_default_redirection_settings = get_option( 'arm_redirection_settings' );
						$arm_default_redirection_settings = maybe_unserialize( $arm_default_redirection_settings );
						$default_access_rules             = $arm_default_redirection_settings['default_access_rules'];

						unset( $page_settings['member_profile_page_id'] );
						unset( $page_settings['thank_you_page_id'] );
						unset( $page_settings['cancel_payment_page_id'] );
						$page_settings = array_filter( $page_settings );
						if ( ! empty( $default_access_rules['non_logged_in'] ) ) {
							if ( $default_access_rules['non_logged_in']['type'] == 'specific' && ! empty( $default_access_rules['non_logged_in']['redirect_to'] ) ) {
								$page_settings[] = $default_access_rules['non_logged_in']['redirect_to'];
							}
						}
						if ( ! empty( $page_settings ) && in_array( $post->ID, array_values( $page_settings ) ) ) {
							unset( $arm_screens['page'] );
						}
					}
					/* Create meta box for membership access */
					$arm_context  = 'side';
					$arm_priority = 'high';
					foreach ( $arm_screens as $screen ) {
						do_action( 'arm_add_meta_boxes', $screen, $arm_context, $arm_priority );
					}
					/* Add CSS for Metaboxes */
					wp_enqueue_style( 'arm_post_metaboxes_css', MEMBERSHIPLITE_URL . '/css/arm_post_metaboxes.css', array(), MEMBERSHIPLITE_VERSION );
				}
			}
		}

		function arm_apply_plan_to_member_function( $plan_id = 0, $user_id = 0 ) {
			global $wpdb, $ARMemberLite, $arm_members_class;
			if ( $plan_id == 0 || $user_id == 0 ) {
				return false;
			}
			$plan = new ARM_Plan_Lite( $plan_id );
			if ( empty( $plan->ID ) ) {
				return false;
			}

			$user = get_user_by( 'id', $user_id );
			if ( empty( $user ) || user_can( $user, 'administrator' ) ) {
				return false;
			}
			$old_plan_ids = get_user_meta( $user_id, 'arm_user_plan_ids', true );
			$old_plan_ids = ! empty( $old_plan_ids ) ? $old_plan_ids : array();
			$old_plan_id  = isset( $old_plan_ids[0] ) ? $old_plan_ids[0] : 0;
			$arm_members_class->arm_new_plan_assigned_by_system( $plan_id, $old_plan_id, $user_id );
			return true;
		}

		function arm_default_plan_array() {
			$default_plan_array = array(
				'arm_current_plan_detail' => array(),
				'arm_start_plan'          => '',
				'arm_expire_plan'         => '',
				'arm_is_trial_plan'       => 0,
				'arm_trial_start'         => '',
				'arm_trial_end'           => '',
				'arm_payment_mode'        => '',
				'arm_payment_cycle'       => '',
				'arm_is_user_in_grace'    => 0,
				'arm_grace_period_end'    => '',
				'arm_grace_period_action' => '',
				'arm_subscr_effective'    => '',
				'arm_change_plan_to'      => '',
				'arm_user_gateway'        => '',
				'arm_subscr_id'           => '',
				'arm_next_due_payment'    => '',
				'arm_completed_recurring' => '',
				'arm_sent_msgs'           => array(),
				'arm_cencelled_plan'      => '',
				'arm_authorize_net'       => array(),
				'arm_2checkout'           => array(),
				'arm_stripe'              => array(),
				'arm_paypal'              => array(),
				'payment_detail'          => array(),
				'arm_started_plan_date'   => '',
			);

			return apply_filters( 'arm_default_plan_array_filter', $default_plan_array );
		}

		function arm_is_recurring_payment_of_user( $user_id = 0, $plan_id = 0, $payment_mode = '' ) {
			global $arm_subscription_plans;
			$arm_user_plan = $plan_id;

			$defaultPlanData  = $arm_subscription_plans->arm_default_plan_array();
			$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $arm_user_plan, true );
			$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
			$planData         = shortcode_atts( $defaultPlanData, $userPlanDatameta );

			$return = false;
			if ( ! empty( $arm_user_plan ) ) {
				$arm_current_plan_detail = $planData['arm_current_plan_detail'];
				if ( ! empty( $arm_current_plan_detail ) ) {
					$plan = new ARM_Plan_Lite( 0 );
					$plan->init( (object) $arm_current_plan_detail );

					if ( $plan->is_recurring() ) {
						$arm_payment_mode = $planData['arm_payment_mode'];
						if ( $arm_payment_mode == 'manual_subscription' && $payment_mode == 'manual_subscription' ) {
							$arm_completed_recurrence = $planData['arm_completed_recurring'];
							$arm_user_payment_cycle   = $planData['arm_payment_cycle'];

							$recurring_data   = $plan->prepare_recurring_data( $arm_user_payment_cycle );
							$total_recurrence = isset( $recurring_data['rec_time'] ) && ! empty( $recurring_data['rec_time'] ) ? $recurring_data['rec_time'] : 0;

							// if ($arm_completed_recurrence < $total_recurrence) {
							if ( $total_recurrence == 'infinite' ) {
								$return = true;
							} elseif ( $total_recurrence != 'infinite' && $arm_completed_recurrence < $total_recurrence ) {
								$return = true;
							}
						}
					}
				}
			}
			return $return;
		}

		function arm_get_plan_payment_cycle( $plan_id = 0 ) {
			global $wp, $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings;
			$where_condition ='';
			if ( $plan_id > 0 ) {$where_condition = $wpdb->prepare(' AND `arm_subscription_plan_id`=%d' , $plan_id); }
			$results         = $wpdb->get_results( $wpdb->prepare('SELECT * FROM `' . $ARMemberLite->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_is_delete`=%d " . $where_condition . ' ORDER BY `arm_subscription_plan_id` DESC',0), ARRAY_A );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_subscription_plans is a Table name.
			if ( ! empty( $results ) ) {
				$plans_data = array();
				foreach ( $results as $sp ) {
					$plnID                          = $sp['arm_subscription_plan_id'];
					$plnName                        = stripslashes( $sp['arm_subscription_plan_name'] );
					$plan_options                   = maybe_unserialize( $sp['arm_subscription_plan_options'] );
					$plan_options['payment_cycles'] = ( isset( $plan_options['payment_cycles'] ) && ! empty( $plan_options['payment_cycles'] ) ) ? $plan_options['payment_cycles'] : array();
					if ( ! empty( $plan_options['payment_cycles'] ) ) {
						$plans_data[ $plnID ] = $plan_options['payment_cycles'];
					}
				}
				return $plans_data;
			} else {
				return false;
			}
		}

		function arm_update_subscription_plan_data( $check, $user_id, $user_meta_key, $user_meta_value ) {
			if ( 'arm_user_plan_ids' == $user_meta_key && ! empty( $user_id ) ) {
				global $ARMemberLite, $wpdb;

				$user_meta_value_array = array();
				if ( ! empty( $user_meta_value ) ) {
					$user_meta_value_arr = maybe_unserialize( $user_meta_value );
					if ( ! empty( $user_meta_value_arr ) && is_array( $user_meta_value_arr ) ) {
						foreach ( $user_meta_value_arr as $user_meta_value ) {
							$user_meta_value_array[] = (int) $user_meta_value;
						}
					}
				}
				$user_meta_value_array = maybe_serialize( $user_meta_value_array );
				$wpdb->update( $ARMemberLite->tbl_arm_members, array( 'arm_user_plan_ids' => $user_meta_value_array ), array( 'arm_user_id' => $user_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			} elseif ( 'arm_user_suspended_plan_ids' == $user_meta_key && ! empty( $user_id ) ) {
				global $ARMemberLite, $wpdb;

				$user_meta_value_array = array();
				if ( ! empty( $user_meta_value ) ) {
					$user_meta_value_arr = maybe_unserialize( $user_meta_value );
					if ( ! empty( $user_meta_value_arr ) && is_array( $user_meta_value_arr ) ) {
						foreach ( $user_meta_value_arr as $user_meta_value ) {
							$user_meta_value_array[] = (int) $user_meta_value;
						}
					}
				}
				$user_meta_value_array = maybe_serialize( $user_meta_value_array );
				$wpdb->update( $ARMemberLite->tbl_arm_members, array( 'arm_user_suspended_plan_ids' => $user_meta_value_array ), array( 'arm_user_id' => $user_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery	
			}
			return $check;
		}

		function arm_delete_subscription_plan_data( $check, $user_id, $user_meta_key, $meta_value, $delete_all ) {
			if ( 'arm_user_plan_ids' == $user_meta_key && ! empty( $user_id ) ) {
				global $ARMemberLite, $wpdb;
				$wpdb->update( $ARMemberLite->tbl_arm_members, array( 'arm_user_plan_ids' => '' ), array( 'arm_user_id' => $user_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			} elseif ( 'arm_user_suspended_plan_ids' == $user_meta_key && ! empty( $user_id ) ) {
				global $ARMemberLite, $wpdb;
				$wpdb->update( $ARMemberLite->tbl_arm_members, array( 'arm_user_suspended_plan_ids' => '' ), array( 'arm_user_id' => $user_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			}
			return $check;
		}

	}

}
global $arm_subscription_plans;
$arm_subscription_plans = new ARM_subscription_plans_Lite();

if ( ! class_exists( 'ARM_Plan_Lite' ) ) {

	class ARM_Plan_Lite {

		var $ID;
		var $name;
		var $type;
		var $status;
		var $amount;
		var $level;
		var $options;
		var $arm_subscription_plan_options;
		var $payment_type;
		var $plan_role;
		var $recurring_data;
		var $description;
		var $plan_text;
		var $plan_price;
		var $plan_price_text;
		var $enable_upgrade_downgrade_action;
		var $upgrade_action;
		var $upgrade_plans;
		var $downgrade_action;
		var $downgrade_plans;
		var $is_delete;
		var $plan_detail;

		public function __construct( $id = 0 ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings;
			if ( is_numeric( $id ) && $id != 0 ) {
				$data = self::arm_get_plan_detail( $id );
				if ( $data ) {
					$this->init( $data );
				}
			}
		}

		public function arm_get_plan_detail( $plan_id = 0 ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings;
			if ( is_numeric( $plan_id ) && $plan_id != 0 ) {
				$plan = $wpdb->get_row( $wpdb->prepare('SELECT * FROM `' . $ARMemberLite->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_id`=%d LIMIT 1",$plan_id) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_subscription_plans is a table name
				if ( ! empty( $plan ) ) {
					return $plan;
				}
			}
			return false;
		}

		public function init( $data ) {

			$this->ID                              = ( isset( $data->arm_subscription_plan_id ) ) ? $data->arm_subscription_plan_id : 0;
			$this->name                            = ( isset( $data->arm_subscription_plan_name ) ) ? stripslashes( $data->arm_subscription_plan_name ) : '';
			$this->type                            = ( isset( $data->arm_subscription_plan_type ) ) ? $data->arm_subscription_plan_type : 'free';
			$this->status                          = ( isset( $data->arm_subscription_plan_status ) ) ? $data->arm_subscription_plan_status : 1;
			$this->amount                          = ( isset( $data->arm_subscription_plan_amount ) ) ? number_format( (float) $data->arm_subscription_plan_amount, 2 ) : 0;
			$this->options                         = ( isset( $data->arm_subscription_plan_options ) ) ? maybe_unserialize( $data->arm_subscription_plan_options ) : array();
			$this->arm_subscription_plan_options   = ( isset( $data->arm_subscription_plan_options ) ) ? maybe_unserialize( $data->arm_subscription_plan_options ) : array();
			$this->payment_type                    = ( isset( $this->options['payment_type'] ) ) ? $this->options['payment_type'] : '';
			$this->plan_role                       = ( isset( $data->arm_subscription_plan_role ) ) ? $data->arm_subscription_plan_role : '';
			$this->recurring_data                  = $this->prepare_recurring_data();
			$this->description                     = ( isset( $data->arm_subscription_plan_description ) ) ? stripslashes( $data->arm_subscription_plan_description ) : '';
			$this->plan_text                       = $this->plan_text();
			$this->plan_price                      = $this->plan_price();
			$this->plan_price_text                 = $this->plan_price_text();
			$this->enable_upgrade_downgrade_action = ( isset( $this->options['enable_upgrade_downgrade_action'] ) && $this->options['enable_upgrade_downgrade_action'] == 1 ) ? 1 : 0;
			$this->upgrade_action                  = ( isset( $this->options['upgrade_action'] ) ) ? $this->options['upgrade_action'] : 'immediate';
			$this->upgrade_plans                   = ( isset( $this->options['upgrade_plans'] ) ) ? $this->options['upgrade_plans'] : array();
			$this->downgrade_action                = ( isset( $this->options['downgrade_action'] ) ) ? $this->options['downgrade_action'] : 'immediate';
			$this->downgrade_plans                 = ( isset( $this->options['downgrade_plans'] ) ) ? $this->options['downgrade_plans'] : array();
			$this->is_delete                       = ( isset( $this->arm_subscription_plan_is_delete ) ) ? $this->arm_subscription_plan_is_delete : 0;
			$this->plan_detail                     = $data;
		}

		/**
		 * Check whether plan exist or not.
		 */
		public function exists() {
			return ! empty( $this->ID );
		}

		/**
		 * Check whether plan exist or not.
		 */
		public function is_active() {
			return ( isset( $this->status ) && $this->status == '1' && isset( $this->is_delete ) && $this->is_delete == '0' );
		}

		/**
		 * Check whether plan exist or not.
		 */
		public function is_deleted() {
			return ( isset( $this->is_delete ) && $this->is_delete == '1' );
		}

		/**
		 * Check whether plan exist or not.
		 */
		public function is_lifetime() {
			return ( isset( $this->options['access_type'] ) && $this->options['access_type'] == 'lifetime' );
		}

		/**
		 * Check plan is recurring or single time payment plan
		 */
		public function is_recurring() {
			return ( ! $this->is_lifetime() && $this->payment_type == 'subscription' );
		}

		/**
		 * Check plan has trial period or not.
		 */
		public function has_trial_period() {
			$trialOptions = isset( $this->options['trial'] ) ? $this->options['trial'] : array();
			if ( $this->is_recurring() && isset( $trialOptions['is_trial_period'] ) && $trialOptions['is_trial_period'] == 1 ) {
				return true;
			}
			return false;
		}

		/**
		 * Check plan is free or not
		 */
		public function is_free() {
			return ( $this->type == 'free' );
		}

		/**
		 * Check plan is paid or not
		 */
		public function is_paid() {
			return ( $this->type == 'paid_infinite' || $this->type == 'paid_finite' || $this->type == 'recurring' );
		}



		/**
		 * Prepare Reccuring Data Array
		 */
		public function prepare_recurring_data( $arm_user_selected_payment_cycle = 0 ) {
			global $ARMemberLite;
			$dataArray = array();
			if ( $this->is_recurring() ) {

				if ( $arm_user_selected_payment_cycle === '' ) {
					$dataArray['amount'] = ! empty( $this->amount ) ? $this->amount : 0;
					$opt_recurring       = $this->options['recurring'];
					$dataArray['period'] = ! empty( $opt_recurring['type'] ) ? $opt_recurring['type'] : 'M';
					switch ( $dataArray['period'] ) {
						case 'D':
							$dataArray['interval'] = ! empty( $opt_recurring['days'] ) ? $opt_recurring['days'] : '1';
							break;
						case 'W':
							$dataArray['interval'] = ! empty( $opt_recurring['weeks'] ) ? $opt_recurring['weeks'] : '1';
							break;
						case 'M':
							$dataArray['interval'] = ! empty( $opt_recurring['months'] ) ? $opt_recurring['months'] : '1';
							break;
						case 'Y':
							$dataArray['interval'] = ! empty( $opt_recurring['years'] ) ? $opt_recurring['years'] : '1';
							break;
						default:
							$dataArray['interval'] = 1;
							break;
					}
					$dataArray['cycles']   = ( ! empty( $opt_recurring['time'] ) && $opt_recurring['time'] != 'infinite' ) ? $opt_recurring['time'] : '';
					$dataArray['rec_time'] = $opt_recurring['time'];
				} else {
					if ( isset( $this->options['payment_cycles'] ) && ! empty( $this->options['payment_cycles'] ) ) {
						$opt_recurring            = $this->options['payment_cycles'][ $arm_user_selected_payment_cycle ];
						$dataArray['cycle_label'] = ! empty( $opt_recurring['cycle_label'] ) ? $opt_recurring['cycle_label'] : 0;
						$dataArray['amount']      = ! empty( $opt_recurring['cycle_amount'] ) ? $opt_recurring['cycle_amount'] : 0;
						$dataArray['period']      = ! empty( $opt_recurring['billing_type'] ) ? $opt_recurring['billing_type'] : 'M';
						$dataArray['interval']    = ! empty( $opt_recurring['billing_cycle'] ) ? $opt_recurring['billing_cycle'] : '1';
						$dataArray['cycles']      = ( ! empty( $opt_recurring['recurring_time'] ) && $opt_recurring['recurring_time'] != 'infinite' ) ? $opt_recurring['recurring_time'] : '';
						$dataArray['rec_time']    = $opt_recurring['recurring_time'];
					} else {
						$dataArray['amount'] = ! empty( $this->amount ) ? $this->amount : 0;
						$opt_recurring       = $this->options['recurring'];
						$dataArray['period'] = ! empty( $opt_recurring['type'] ) ? $opt_recurring['type'] : 'M';
						switch ( $dataArray['period'] ) {
							case 'D':
								$dataArray['interval'] = ! empty( $opt_recurring['days'] ) ? $opt_recurring['days'] : '1';
								break;
							case 'W':
								$dataArray['interval'] = ! empty( $opt_recurring['weeks'] ) ? $opt_recurring['weeks'] : '1';
								break;
							case 'M':
								$dataArray['interval'] = ! empty( $opt_recurring['months'] ) ? $opt_recurring['months'] : '1';
								break;
							case 'Y':
								$dataArray['interval'] = ! empty( $opt_recurring['years'] ) ? $opt_recurring['years'] : '1';
								break;
							default:
								$dataArray['interval'] = 1;
								break;
						}
						$dataArray['cycles']   = ( ! empty( $opt_recurring['time'] ) && $opt_recurring['time'] != 'infinite' ) ? $opt_recurring['time'] : '';
						$dataArray['rec_time'] = $opt_recurring['time'];
					}
				}

				$dataArray['manual_billing_start'] = $this->options['recurring']['manual_billing_start'];
				// Trial Period Options
				$opt_trial = $this->options['trial'];
				if ( isset( $opt_trial['is_trial_period'] ) && $opt_trial['is_trial_period'] == 1 ) {
					$dataArray['trial']['amount'] = ! empty( $opt_trial['amount'] ) ? $opt_trial['amount'] : 0;
					$dataArray['trial']['period'] = ! empty( $opt_trial['type'] ) ? $opt_trial['type'] : 'M';
					switch ( $opt_trial['type'] ) {
						case 'D':
							$dataArray['trial']['interval'] = ! empty( $opt_trial['days'] ) ? $opt_trial['days'] : '1';
							$dataArray['trial']['type']     = 'Day';
							break;
						case 'W':
							$dataArray['trial']['interval'] = ! empty( $opt_trial['weeks'] ) ? $opt_trial['weeks'] : '1';
							$dataArray['trial']['type']     = 'Week';
							break;
						case 'M':
							$dataArray['trial']['interval'] = ! empty( $opt_trial['months'] ) ? $opt_trial['months'] : '1';
							$dataArray['trial']['type']     = 'Month';
							break;
						case 'Y':
							$dataArray['trial']['interval'] = ! empty( $opt_trial['years'] ) ? $opt_trial['years'] : '1';
							$dataArray['trial']['type']     = 'Year';
							break;
						default:
							$dataArray['trial']['interval'] = 1;
							$dataArray['trial']['type']     = 'Month';
							break;
					}
				}
			}
			return $dataArray;
		}

		/**
		 * Get subscription plan expire time
		 *
		 * @param type $start_time
		 * @return expire time
		 */
		function arm_plan_expire_time( $start_time = '', $payment_mode = 'manual_subscription', $payment_cycle = 0 ) {

			global $wp, $wpdb, $ARMemberLite, $arm_global_settings;
			$start_time  = ( ! empty( $start_time ) ) ? $start_time : strtotime( current_time( 'mysql' ) );
			$expire_time = false;
			if ( $this->exists() ) {
				$plan_options = $this->options;
				if ( ! empty( $plan_options ) ) {

					if ( $this->is_recurring() ) {
						if ( isset( $plan_options['payment_cycles'] ) && ! empty( $plan_options['payment_cycles'] ) ) {

							if ( $payment_cycle === '' ) {
								$payment_cycle = 0;
							}
							$opt_recurring          = $plan_options['payment_cycles'][ $payment_cycle ];
							$period_options         = array();
							$period_options['type'] = ! empty( $opt_recurring['billing_type'] ) ? $opt_recurring['billing_type'] : 'M';
							$billing_cycle          = ! empty( $opt_recurring['billing_cycle'] ) ? $opt_recurring['billing_cycle'] : '1';
							switch ( $period_options['type'] ) {
								case 'D':
									$period_options['days'] = $billing_cycle;
									break;
								case 'M':
									$period_options['months'] = $billing_cycle;
									break;
								case 'Y':
									$period_options['years'] = $billing_cycle;
									break;
								default:
									$period_options['days'] = $billing_cycle;
									break;
							}
							$period_options['time'] = ( ! empty( $opt_recurring['recurring_time'] ) ) ? $opt_recurring['recurring_time'] : 'infinite';
						} else {
							$period_options = $plan_options['recurring'];
						}
					}

					if ( $this->is_paid() && ! $this->is_lifetime() && ! ( $this->is_recurring() && $period_options['time'] == 'infinite' ) ) {
						$payment_type     = $plan_options['payment_type'];
						$num_of_recurring = 1;
						$trial_option     = array();

						$intervalDate = '';
						if ( $payment_type == 'one_time' ) {
							$period_options = $plan_options['eopa'];
						} elseif ( $payment_type == 'subscription' ) {

							$trial_option = $plan_options['trial'];
							// No Expiry date for infinite options.
							if ( isset( $period_options['time'] ) && ( $period_options['time'] == 'infinite' || $period_options['time'] < 2 ) && $payment_mode == 'auto_debit_subscription' ) {
								return false;
							}
							// Add recurring time for number of recurring subscription
							if ( isset( $period_options['time'] ) && ( $period_options['time'] != 'infinite' || $period_options['time'] > 1 ) ) {
								$num_of_recurring = $period_options['time'];
							}
						} else {
							$period_options = array(
								'type'   => 'D',
								'months' => '0',
							);
						}
						if ( ( $this->is_recurring() && $payment_mode == 'auto_debit_subscription' ) || ( $this->options['access_type'] == 'finite' && $payment_type == 'one_time' ) ) {

							$arm_subscription_plan_type = $this->type;
							$expiry_type                = ( isset( $this->options['expiry_type'] ) && $this->options['expiry_type'] != '' ) ? $this->options['expiry_type'] : 'joined_date_expiry';
							if ( $arm_subscription_plan_type == 'recurring' || ( $arm_subscription_plan_type == 'paid_finite' && $expiry_type == 'joined_date_expiry' ) ) {
								switch ( $period_options['type'] ) {
									case 'D':
										$num          = ( isset( $period_options['days'] ) ) ? ( $period_options['days'] * $num_of_recurring ) : $num_of_recurring;
										$intervalDate = "+$num day";
										break;
									case 'W':
										$num          = ( isset( $period_options['weeks'] ) ) ? ( $period_options['weeks'] * $num_of_recurring ) : ( $num_of_recurring );
										$intervalDate = "+$num week";
										break;
									case 'M':
										$num          = ( isset( $period_options['months'] ) ) ? ( $period_options['months'] * $num_of_recurring ) : ( $num_of_recurring );
										$intervalDate = "+$num month";
										break;
									case 'Y':
										$num          = ( isset( $period_options['years'] ) ) ? ( $period_options['years'] * $num_of_recurring ) : ( $num_of_recurring );
										$intervalDate = "+$num year";
										break;
									default:
										$num          = ( isset( $period_options['days'] ) ) ? ( $period_options['days'] * $num_of_recurring ) : $num_of_recurring;
										$intervalDate = "+$num day";
										break;
								}
							} else {
								return $expire_time = strtotime( $this->options['expiry_date'] );
							}
						} elseif ( $this->is_recurring() && $payment_mode == 'manual_subscription' ) {
							$billing_start_day = $this->options['recurring']['manual_billing_start'];
							$current_day       = date( 'Y-m-d', $start_time ); //phpcs:ignore
							if ( $billing_start_day == 'transaction_day' ) {
								$billing_type = $period_options['type'];
								if ( $billing_type == 'D' ) {
									$days         = $period_options['days'] * $num_of_recurring;
									$intervalDate = date( 'Y-m-d', strtotime( "$current_day+$days day" ) ); //phpcs:ignore
								} elseif ( $billing_type == 'M' ) {
									$months       = $period_options['months'] * $num_of_recurring;
									$intervalDate = date( 'Y-m-d', strtotime( "$current_day+$months month" ) ); //phpcs:ignore
								} elseif ( $billing_type == 'Y' ) {
									$years        = $period_options['years'] * $num_of_recurring;
									$intervalDate = date( 'Y-m-d', strtotime( "$current_day+$years year" ) ); //phpcs:ignore
								}
							} else {

								$billing_type = $period_options['type'];
								$days         = isset( $period_options['days'] ) ? $period_options['days'] : 0;
								$months       = isset( $period_options['months'] ) ? $period_options['months'] : 0;
								$years        = isset( $period_options['years'] ) ? $period_options['years'] : 0;
								if ( $billing_type == 'D' ) {
									$tdays        = ( $days > 0 ) ? ( $days * $num_of_recurring ) : $days;
									$intervalDate = date( 'Y-m-d', strtotime( date( 'Y-m-d', strtotime( "$current_day+$tdays day" ) ) ) ); //phpcs:ignore
								}

								if ( date( 'd', strtotime( $current_day ) ) < $billing_start_day ) { //phpcs:ignore

									if ( $billing_type == 'M' ) {

										$tmonths = ( $months > 0 ) ? ( $months * $num_of_recurring ) : $months;

										$intervalDate = date( 'Y-m-' . $billing_start_day, strtotime( "$current_day+$tmonths month" ) ); //phpcs:ignore
									} elseif ( $billing_type == 'Y' ) {
										$tyears = ( $years > 0 ) ? ( $years * $num_of_recurring ) : $years;

										$intervalDate = date( 'Y-m-' . $billing_start_day, strtotime( "$current_day+$tyears year" ) ); //phpcs:ignore
									}
								} elseif ( date( 'd', strtotime( $current_day ) ) >= $billing_start_day ) { //phpcs:ignore

									$tdays   = ( $days > 0 ) ? ( $days * $num_of_recurring ) : $days;
									$tmonths = ( $months > 0 ) ? ( $months * $num_of_recurring ) : $months;
									$tyears  = ( $years > 0 ) ? ( $years * $num_of_recurring ) : $years;

									if ( $billing_type == 'M' ) {
										$intervalDate = date( 'Y-m-d', strtotime( date( 'Y-m-' . $billing_start_day, strtotime( "$current_day+$tmonths month" ) ) ) ); //phpcs:ignore
									} elseif ( $billing_type == 'Y' ) {
										$intervalDate = date( 'Y-m-d', strtotime( date( 'Y-m-' . $billing_start_day, strtotime( "$current_day+$tyears year" ) ) ) ); //phpcs:ignore
									}
								}
							}
						}
						$expire_time = strtotime( $intervalDate, $start_time );
					}
				}
			}
			return $expire_time;
		}

		function arm_plan_expire_time_for_renew_action( $start_time = '', $mail_type = 'renew_subscription', $payment_cycle = 0 ) {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings;
			$start_time  = ( ! empty( $start_time ) ) ? $start_time : strtotime( current_time( 'mysql' ) );
			$expire_time = false;
			if ( $this->exists() ) {
				$plan_options = $this->options;
				if ( $this->is_paid() && ! $this->is_lifetime() ) {
					$num_of_recurring = 1;
					$trial_option     = array();
					$payment_type     = $plan_options['payment_type'];
					$intervalDate     = '';
					if ( $payment_type == 'one_time' ) {
						$period_options = $plan_options['eopa'];
					} elseif ( $payment_type == 'subscription' ) {

						if ( isset( $plan_options['payment_cycles'] ) && ! empty( $plan_options['payment_cycles'] ) ) {

							if ( $payment_cycle === '' ) {
								$payment_cycle = 0;
							}
							$opt_recurring          = $plan_options['payment_cycles'][ $payment_cycle ];
							$period_options         = array();
							$period_options['type'] = ! empty( $opt_recurring['billing_type'] ) ? $opt_recurring['billing_type'] : 'M';
							$billing_cycle          = ! empty( $opt_recurring['billing_cycle'] ) ? $opt_recurring['billing_cycle'] : '1';
							switch ( $period_options['type'] ) {
								case 'D':
									$period_options['days'] = $billing_cycle;
									break;
								case 'M':
									$period_options['months'] = $billing_cycle;
									break;
								case 'Y':
									$period_options['years'] = $billing_cycle;
									break;
								default:
									$period_options['days'] = $billing_cycle;
									break;
							}
							$period_options['time'] = ( ! empty( $opt_recurring['recurring_time'] ) ) ? $opt_recurring['recurring_time'] : 'infinite';
						} else {
							$period_options = $plan_options['recurring'];
						}

						$trial_option = $plan_options['trial'];
						// No Expiry date for infinite options.
						if ( isset( $period_options['time'] ) && ( $period_options['time'] == 'infinite' || $period_options['time'] < 2 ) ) {
							return false;
						}
						// Add recurring time for number of recurring subscription
						if ( isset( $period_options['time'] ) && ( $period_options['time'] != 'infinite' || $period_options['time'] > 1 ) ) {
							$num_of_recurring = $period_options['time'];
						}
					} else {
						$period_options = array(
							'type'   => 'D',
							'months' => '0',
						);
					}
					switch ( $period_options['type'] ) {
						case 'D':
							$num          = ( isset( $period_options['days'] ) ) ? ( $period_options['days'] * $num_of_recurring ) : $num_of_recurring;
							$intervalDate = "+$num day";
							break;
						case 'W':
							$num          = ( isset( $period_options['weeks'] ) ) ? ( $period_options['weeks'] * $num_of_recurring ) : ( $num_of_recurring );
							$intervalDate = "+$num week";
							break;
						case 'M':
							$num          = ( isset( $period_options['months'] ) ) ? ( $period_options['months'] * $num_of_recurring ) : ( $num_of_recurring );
							$intervalDate = "+$num month";
							break;
						case 'Y':
							$num          = ( isset( $period_options['years'] ) ) ? ( $period_options['years'] * $num_of_recurring ) : ( $num_of_recurring );
							$intervalDate = "+$num year";
							break;
						default:
							$num          = ( isset( $period_options['days'] ) ) ? ( $period_options['days'] * $num_of_recurring ) : $num_of_recurring;
							$intervalDate = "+$num day";
							break;
					}
					$user    = wp_get_current_user();
					$user_id = $user->ID;

					$expire_time = strtotime( $intervalDate, $start_time );
					if ( isset( $trial_option['is_trial_period'] ) && $trial_option['is_trial_period'] != 0 && $mail_type != 'renew_subscription' ) {
						if ( $trial_option['type'] == 'W' ) {
							$trial_num  = ( isset( $trial_option['weeks'] ) ) ? ( $trial_option['weeks'] ) : 7;
							$trial_days = "+$trial_num week";
						} elseif ( $trial_option['type'] == 'M' ) {
							$trial_num  = ( isset( $trial_option['months'] ) ) ? ( $trial_option['months'] ) : 30;
							$trial_days = "+$trial_num month";
						} elseif ( $trial_option['type'] == 'Y' ) {
							$trial_num  = ( isset( $trial_option['years'] ) ) ? ( $trial_option['years'] ) : 365;
							$trial_days = "+$trial_num year";
						} else {
							$trial_num  = ( isset( $trial_option['days'] ) ) ? $trial_option['days'] : 1;
							$trial_days = "+$trial_num day";
						}
						$expire_time = strtotime( $trial_days, $expire_time );
					}
				}
			}
			return $expire_time;
		}

		function arm_plan_next_renew_date( $start_time, $payment_mode = 'manual_subscription' ) {
			$current_day = date( 'Y-m-d', $start_time ); //phpcs:ignore

			$billing_start_day = $this->options['recurring']['manual_billing_start'];

			if ( $billing_start_day == 'transaction_day' || $payment_mode == 'auto_debit_subscription' ) {
				$billing_type = $this->options['recurring']['type'];
				if ( $billing_type == 'D' ) {
					$days         = $this->options['recurring']['days'];
					$intervalDate = date( 'Y-m-d', strtotime( "$current_day +$days day" ) ); //phpcs:ignore
				} elseif ( $billing_type == 'M' ) {
					$months       = $this->options['recurring']['months'];
					$intervalDate = date( 'Y-m-d', strtotime( "$current_day +$months month" ) ); //phpcs:ignore
				} elseif ( $billing_type == 'Y' ) {
					$years        = $this->options['recurring']['years'];
					$intervalDate = date( 'Y-m-d', strtotime( "$current_day +$years year" ) ); //phpcs:ignore
				}
			} else {

				$billing_type = $this->options['recurring']['type'];
				$days         = $this->options['recurring']['days'];
				$months       = $this->options['recurring']['months'];
				$years        = $this->options['recurring']['years'];

				if ( date( 'd', strtotime( $current_day ) ) < $billing_start_day ) { //phpcs:ignore

					if ( $billing_type == 'D' ) {
						$tdays        = ( $days > 0 ) ? $days - 1 : $days;
						$intervalDate = date( 'Y-m-' . $billing_start_day, strtotime( "$current_day + $tdays day" ) ); //phpcs:ignore
					} elseif ( $billing_type == 'M' ) {
						$tmonths      = ( $months > 0 ) ? $months - 1 : $months;
						$intervalDate = date( 'Y-m-' . $billing_start_day, strtotime( "$current_day + $tmonths month" ) ); //phpcs:ignore
					} elseif ( $billing_type == 'Y' ) {
						$tyears       = ( $years > 1 ) ? $years - 1 : $years;
						$intervalDate = date( 'Y-m-' . $billing_start_day, strtotime( "$current_day + $tyears year" ) ); //phpcs:ignore
					}
				} elseif ( date( 'd', strtotime( $current_day ) ) >= $billing_start_day ) { //phpcs:ignore

					if ( $billing_type == 'D' ) {
						$intervalDate = date( 'Y-m-d', strtotime( date( 'Y-m-' . $billing_start_day, strtotime( "$current_day + $days day" ) ) ) ); //phpcs:ignore
					} elseif ( $billing_type == 'M' ) {
						$intervalDate = date( 'Y-m-d', strtotime( date( 'Y-m-' . $billing_start_day, strtotime( "$current_day + $months month" ) ) ) ); //phpcs:ignore
					} elseif ( $billing_type == 'Y' ) {
						$intervalDate = date( 'Y-m-d', strtotime( date( 'Y-m-' . $billing_start_day, strtotime( "$current_day + $years year" ) ) ) ); //phpcs:ignore
					}
				}
			}

			$expire_time = strtotime( $intervalDate, $start_time );
			return $expire_time;
		}

		public function plan_text( $showTrialInfo = false, $showPlanType = true ) {
			global $arm_subscription_plans, $arm_payment_gateways, $arm_global_settings;
			$date_format     = $arm_global_settings->arm_get_wp_date_format();
			$currency        = $arm_payment_gateways->arm_get_global_currency();
			$planText        = '';
			$arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator( $currency, $this->amount );
			if ( $this->is_paid() ) {
				if ( $showPlanType ) {
					$planText .= '<span class="arm_item_status_text active arm_margin_right_12">' . esc_html__( 'Paid', 'armember-membership' ) . '</span>';
				}
				if ( $this->is_lifetime() ) {
					$planText .= $arm_plan_amount . ' ' . $currency . ' ' . esc_html__( 'For Lifetime', 'armember-membership' );
				} else {
					if ( $this->payment_type == 'subscription' ) {
						$planText .= '<span>';
						if ( $showTrialInfo ) {
							if ( ! empty( $this->recurring_data['trial'] ) ) {
								if ( $this->recurring_data['trial']['amount'] > 0 ) {
									$arm_plan_trial_amount = $arm_payment_gateways->arm_amount_set_separator( $currency, $this->recurring_data['trial']['amount'] );
									$planText             .= "{$arm_plan_trial_amount} {$currency}";
								} else {
									$planText .= esc_html__( 'Free', 'armember-membership' );
								}
								$planText     .= ' ' . esc_html__( 'for the first', 'armember-membership' ) . ' ';
								$trialInterval = $this->recurring_data['trial']['interval'];
								if ( $this->recurring_data['trial']['period'] == 'Y' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'years', 'armember-membership' ) : esc_html__( 'year', 'armember-membership' );
								} elseif ( $this->recurring_data['trial']['period'] == 'M' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'months', 'armember-membership' ) : esc_html__( 'month', 'armember-membership' );
								} elseif ( $this->recurring_data['trial']['period'] == 'W' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'weeks', 'armember-membership' ) : esc_html__( 'week', 'armember-membership' );
								} elseif ( $this->recurring_data['trial']['period'] == 'D' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'days', 'armember-membership' ) : esc_html__( 'day', 'armember-membership' );
								}
								$planText .= ',<br/>' . esc_html__( 'Then', 'armember-membership' ) . ' ';
							}
						}
						$typeArrayMany = array(
							'D' => esc_html__( 'days', 'armember-membership' ),
							'W' => esc_html__( 'weeks', 'armember-membership' ),
							'M' => esc_html__( 'months', 'armember-membership' ),
							'Y' => esc_html__( 'years', 'armember-membership' ),
						);
						$typeArray     = array(
							'D' => esc_html__( 'day', 'armember-membership' ),
							'W' => esc_html__( 'week', 'armember-membership' ),
							'M' => esc_html__( 'month', 'armember-membership' ),
							'Y' => esc_html__( 'year', 'armember-membership' ),
						);
						$period        = $this->recurring_data['period'];
						$interval      = $this->recurring_data['interval'];
						$cycles        = $this->recurring_data['rec_time'];
						$recText       = ( $interval > 1 ) ? "{$interval} {$typeArrayMany[$period]}" : "{$typeArray[$period]}";
						$planText     .= "{$arm_plan_amount} {$currency} " . esc_html__( 'for each', 'armember-membership' ) . " {$recText}";
						if ( ! empty( $cycles ) && $cycles != '0' && is_numeric( $cycles ) ) {
							$planText .= ', ' . esc_html__( 'for', 'armember-membership' ) . " {$cycles} " . esc_html__( 'installments', 'armember-membership' );
						}
						$planText .= '</span>';
					} elseif ( $this->payment_type == 'one_time' ) {
						$planText .= '<span>';
						$expiry_type = ( isset( $this->options['expiry_type'] ) && $this->options['expiry_type'] != '' ) ? $this->options['expiry_type'] : 'joined_date_expiry';
						if ( $expiry_type == 'joined_date_expiry' ) {
							$period_options = $this->options['eopa'];
							$eopaType       = $period_options['type'];
							$eopaTime       = '';
							switch ( $eopaType ) {
								case 'D':
									$num      = ( isset( $period_options['days'] ) ) ? $period_options['days'] : 1;
									$eopaTime = " $num day(s)";
									break;
								case 'W':
									$num      = ( isset( $period_options['weeks'] ) ) ? $period_options['weeks'] : 1;
									$eopaTime = " $num week(s)";
									break;
								case 'M':
									$num      = ( isset( $period_options['months'] ) ) ? $period_options['months'] : 1;
									$eopaTime = " $num month(s)";
									break;
								case 'Y':
									$num      = ( isset( $period_options['years'] ) ) ? $period_options['years'] : 1;
									$eopaTime = " $num year(s)";
									break;
								default:
									$num      = ( isset( $period_options['days'] ) ) ? $period_options['days'] : 1;
									$eopaTime = " $num day(s)";
									break;
							}
							$planText .= "{$arm_plan_amount} {$currency} " . esc_html__( 'as One Time payment for', 'armember-membership' ) . " {$eopaTime}";
						} else {
							$expiry_time = date_i18n( $date_format, strtotime( $this->options['expiry_date'] ) );
							$planText   .= "{$arm_plan_amount} {$currency} " . esc_html__( 'as One Time payment till', 'armember-membership' ) . " {$expiry_time}";
						}
						$planText .= '</span>';
					}
				}
			} else {
				
				$planText = '<span class="arm_item_status_text default">' . esc_html__( 'Free', 'armember-membership' ) . '</span>';
			}
			
			return $planText;
		}

		public function user_plan_text( $showTrialInfo = false, $payment_cycle = 0 ) {
			global $arm_subscription_plans, $arm_payment_gateways, $ARMemberLite, $arm_global_settings;
			$date_format = $arm_global_settings->arm_get_wp_date_format();
			$currency    = $arm_payment_gateways->arm_get_global_currency();
			$planText    = '';
			if ( $this->is_paid() ) {
				$planText .= '<span class="arm_item_status_text active">' . esc_html__( 'Paid', 'armember-membership' ) . '</span><br/>';
				if ( $this->is_lifetime() ) {
					$arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator( $currency, $this->amount );
					$planText       .= $arm_plan_amount . ' ' . $currency . ' ' . esc_html__( 'For Lifetime', 'armember-membership' );
				} else {
					if ( $this->payment_type == 'subscription' ) {
						if ( $showTrialInfo ) {
							if ( ! empty( $this->recurring_data['trial'] ) ) {
								if ( $this->recurring_data['trial']['amount'] > 0 ) {
									$arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator( $currency, $this->recurring_data['trial']['amount'] );
									$planText       .= "{$arm_plan_amount} {$currency}";
								} else {
									$planText .= esc_html__( 'Free', 'armember-membership' );
								}
								$planText     .= ' ' . esc_html__( 'for the first', 'armember-membership' ) . ' ';
								$trialInterval = $this->recurring_data['trial']['interval'];
								if ( $this->recurring_data['trial']['period'] == 'Y' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'years', 'armember-membership' ) : esc_html__( 'year', 'armember-membership' );
								} elseif ( $this->recurring_data['trial']['period'] == 'M' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'months', 'armember-membership' ) : esc_html__( 'month', 'armember-membership' );
								} elseif ( $this->recurring_data['trial']['period'] == 'W' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'weeks', 'armember-membership' ) : esc_html__( 'week', 'armember-membership' );
								} elseif ( $this->recurring_data['trial']['period'] == 'D' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'days', 'armember-membership' ) : esc_html__( 'day', 'armember-membership' );
								}
								$planText .= ',<br/>' . esc_html__( 'Then', 'armember-membership' ) . ' ';
							}
						}
						$typeArrayMany = array(
							'D' => esc_html__( 'days', 'armember-membership' ),
							'W' => esc_html__( 'weeks', 'armember-membership' ),
							'M' => esc_html__( 'months', 'armember-membership' ),
							'Y' => esc_html__( 'years', 'armember-membership' ),
						);
						$typeArray     = array(
							'D' => esc_html__( 'day', 'armember-membership' ),
							'W' => esc_html__( 'week', 'armember-membership' ),
							'M' => esc_html__( 'month', 'armember-membership' ),
							'Y' => esc_html__( 'year', 'armember-membership' ),
						);

						$recurring_data = $this->prepare_recurring_data( $payment_cycle );

						$period          = $recurring_data['period'];
						$interval        = $recurring_data['interval'];
						$cycles          = $recurring_data['rec_time'];
						$recText         = ( $interval > 1 ) ? "{$interval} {$typeArrayMany[$period]}" : "{$typeArray[$period]}";
						$arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator( $currency, $recurring_data['amount'] );
						$planText       .= "{$arm_plan_amount} {$currency} " . esc_html__( 'for each', 'armember-membership' ) . " {$recText}";
						if ( ! empty( $cycles ) && $cycles != '0' && is_numeric( $cycles ) ) {
							$planText .= ', ' . esc_html__( 'for', 'armember-membership' ) . " {$cycles} " . esc_html__( 'installments', 'armember-membership' );
						}
					} elseif ( $this->payment_type == 'one_time' ) {
						$expiry_type = ( isset( $this->options['expiry_type'] ) && $this->options['expiry_type'] != '' ) ? $this->options['expiry_type'] : 'joined_date_expiry';
						if ( $expiry_type == 'joined_date_expiry' ) {
							$period_options = $this->options['eopa'];
							$eopaType       = $period_options['type'];
							$eopaTime       = '';
							switch ( $eopaType ) {
								case 'D':
									$num      = ( isset( $period_options['days'] ) ) ? $period_options['days'] : 1;
									$eopaTime = " $num day(s)";
									break;
								case 'W':
									$num      = ( isset( $period_options['weeks'] ) ) ? $period_options['weeks'] : 1;
									$eopaTime = " $num week(s)";
									break;
								case 'M':
									$num      = ( isset( $period_options['months'] ) ) ? $period_options['months'] : 1;
									$eopaTime = " $num month(s)";
									break;
								case 'Y':
									$num      = ( isset( $period_options['years'] ) ) ? $period_options['years'] : 1;
									$eopaTime = " $num year(s)";
									break;
								default:
									$num      = ( isset( $period_options['days'] ) ) ? $period_options['days'] : 1;
									$eopaTime = " $num day(s)";
									break;
							}
							$arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator( $currency, $this->amount );
							$planText       .= "{$arm_plan_amount} {$currency} " . esc_html__( 'as One Time payment for', 'armember-membership' ) . " {$eopaTime}";
						} else {
							$expiry_time     = date_i18n( $date_format, strtotime( $this->options['expiry_date'] ) );
							$arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator( $currency, $this->amount );
							$planText       .= "{$arm_plan_amount} {$currency} " . esc_html__( 'as One Time payment till', 'armember-membership' ) . " {$expiry_time}";
						}
					}
				}
			} else {
				$planText = esc_html__( 'Free', 'armember-membership' );
			}
			return $planText;
		}



		public function new_user_plan_text( $showTrialInfo = false, $payment_cycle = 0, $show_title = true ) {
			global $arm_subscription_plans, $arm_payment_gateways, $ARMemberLite, $arm_global_settings;
			$date_format = $arm_global_settings->arm_get_wp_date_format();
			$currency    = $arm_payment_gateways->arm_get_global_currency();

			$planText = '';
			if ( $this->is_paid() ) {

				if ( $this->is_lifetime() ) {

					$planText .= $arm_payment_gateways->arm_prepare_amount( $currency, $this->amount ) . ' - ' . esc_html__( 'Onetime', 'armember-membership' );
				} else {
					if ( $this->payment_type == 'subscription' ) {

						if ( $show_title ) {
							$planText .= esc_html__( 'Subscription', 'armember-membership' ) . '<br/>';
						}
						if ( $showTrialInfo ) {
							if ( ! empty( $this->recurring_data['trial'] ) ) {
								if ( $this->recurring_data['trial']['amount'] > 0 ) {
									$arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator( $currency, $this->recurring_data['trial']['amount'] );
									$planText       .= "{$arm_plan_amount} {$currency}";
								} else {
									$planText .= esc_html__( 'Free', 'armember-membership' );
								}
								$planText     .= ' ' . esc_html__( 'for the first', 'armember-membership' ) . ' ';
								$trialInterval = $this->recurring_data['trial']['interval'];
								if ( $this->recurring_data['trial']['period'] == 'Y' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'years', 'armember-membership' ) : esc_html__( 'year', 'armember-membership' );
								} elseif ( $this->recurring_data['trial']['period'] == 'M' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'months', 'armember-membership' ) : esc_html__( 'month', 'armember-membership' );
								} elseif ( $this->recurring_data['trial']['period'] == 'W' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'weeks', 'armember-membership' ) : esc_html__( 'week', 'armember-membership' );
								} elseif ( $this->recurring_data['trial']['period'] == 'D' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'days', 'armember-membership' ) : esc_html__( 'day', 'armember-membership' );
								}
								$planText .= ',<br/>' . esc_html__( 'Then', 'armember-membership' ) . ' ';
							}
						}
						$typeArrayMany = array(
							'D' => esc_html__( 'days', 'armember-membership' ),
							'W' => esc_html__( 'weeks', 'armember-membership' ),
							'M' => esc_html__( 'months', 'armember-membership' ),
							'Y' => esc_html__( 'years', 'armember-membership' ),
						);
						$typeArray     = array(
							'D' => esc_html__( 'Daily', 'armember-membership' ),
							'W' => esc_html__( 'Weekly', 'armember-membership' ),
							'M' => esc_html__( 'Monthly', 'armember-membership' ),
							'Y' => esc_html__( 'Yearly', 'armember-membership' ),
						);

						$recurring_data = $this->prepare_recurring_data( $payment_cycle );

						$period    = $recurring_data['period'];
						$interval  = $recurring_data['interval'];
						$cycles    = $recurring_data['rec_time'];
						$recText   = ( $interval > 1 ) ? esc_html__( 'every', 'armember-membership' ) . ' ' . $interval . ' ' . $typeArrayMany[ $period ] : "{$typeArray[$period]}";
						$planText .= $arm_payment_gateways->arm_prepare_amount( $currency, $recurring_data['amount'] ) . ' - ' . $recText;
					} elseif ( $this->payment_type == 'one_time' ) {

						$planText .= $arm_payment_gateways->arm_prepare_amount( $currency, $this->amount ) . ' - ' . esc_html__( 'Onetime', 'armember-membership' );

					}
				}
			} else {
				$planText = esc_html__( 'Free', 'armember-membership' );
			}
			return $planText;
		}

		public function plan_price_text( $showTrialInfo = false ) {
			global $arm_subscription_plans, $arm_payment_gateways;
			$currency = $arm_payment_gateways->arm_get_global_currency();
			$planText = '';
			if ( $this->is_paid() ) {
				if ( $this->is_lifetime() ) {
					$planText .= esc_html__( 'For Lifetime', 'armember-membership' );
				} else {
					if ( $this->payment_type == 'subscription' ) {
						if ( $showTrialInfo ) {
							if ( ! empty( $this->recurring_data['trial'] ) ) {
								$planText     .= ' ' . esc_html__( 'for the first', 'armember-membership' ) . ' ';
								$trialInterval = $this->recurring_data['trial']['interval'];
								if ( $this->recurring_data['trial']['period'] == 'Y' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'years', 'armember-membership' ) : esc_html__( 'year', 'armember-membership' );
								} elseif ( $this->recurring_data['trial']['period'] == 'M' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'months', 'armember-membership' ) : esc_html__( 'month', 'armember-membership' );
								} elseif ( $this->recurring_data['trial']['period'] == 'W' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'weeks', 'armember-membership' ) : esc_html__( 'week', 'armember-membership' );
								} elseif ( $this->recurring_data['trial']['period'] == 'D' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'days', 'armember-membership' ) : esc_html__( 'day', 'armember-membership' );
								}
								$planText .= ',<br/>' . esc_html__( 'Then', 'armember-membership' ) . ' ';
							}
						}
						$typeArrayMany = array(
							'D' => esc_html__( 'days', 'armember-membership' ),
							'W' => esc_html__( 'weeks', 'armember-membership' ),
							'M' => esc_html__( 'months', 'armember-membership' ),
							'Y' => esc_html__( 'years', 'armember-membership' ),
						);
						$typeArray     = array(
							'D' => esc_html__( 'day', 'armember-membership' ),
							'W' => esc_html__( 'week', 'armember-membership' ),
							'M' => esc_html__( 'month', 'armember-membership' ),
							'Y' => esc_html__( 'year', 'armember-membership' ),
						);
						$period        = $this->recurring_data['period'];
						$interval      = $this->recurring_data['interval'];
						$cycles        = $this->recurring_data['rec_time'];
						$recText       = ( $interval > 1 ) ? "{$interval} {$typeArrayMany[$period]}" : "{$typeArray[$period]}";
						$planText     .= esc_html__( 'for each', 'armember-membership' ) . " {$recText}";
						if ( ! empty( $cycles ) && $cycles != '0' && is_numeric( $cycles ) ) {
							$planText .= ', ' . esc_html__( 'for', 'armember-membership' ) . " {$cycles} " . esc_html__( 'installments', 'armember-membership' );
						}
					} elseif ( $this->payment_type == 'one_time' ) {
						$period_options = $this->options['eopa'];
						$eopaType       = $period_options['type'];
						$eopaTime       = '';
						switch ( $eopaType ) {
							case 'D':
								$num      = ( isset( $period_options['days'] ) ) ? $period_options['days'] : 1;
								$eopaTime = " $num day(s)";
								break;
							case 'W':
								$num      = ( isset( $period_options['weeks'] ) ) ? $period_options['weeks'] : 1;
								$eopaTime = " $num week(s)";
								break;
							case 'M':
								$num      = ( isset( $period_options['months'] ) ) ? $period_options['months'] : 1;
								$eopaTime = " $num month(s)";
								break;
							case 'Y':
								$num      = ( isset( $period_options['years'] ) ) ? $period_options['years'] : 1;
								$eopaTime = " $num year(s)";
								break;
							default:
								$num      = ( isset( $period_options['days'] ) ) ? $period_options['days'] : 1;
								$eopaTime = " $num day(s)";
								break;
						}
						$planText .= esc_html__( 'as One Time payment for', 'armember-membership' ) . " {$eopaTime}";
					}
				}
			}
			return $planText;
		}

		public function plan_price( $showTrialInfo = false ) {
			global $arm_subscription_plans, $arm_payment_gateways;
			$currency                    = $arm_payment_gateways->arm_get_global_currency();
			$currency_position           = $arm_payment_gateways->arm_currency_symbol_position( $currency );
			$currencies                  = array_merge( $arm_payment_gateways->currency['paypal'], $arm_payment_gateways->currency['bank_transfer'] );
			$get_currency_wise_seperator = true;

			$arm_plan_amount = '<span class="arm_module_plan_cycle_price">' . $arm_payment_gateways->arm_amount_set_separator( $currency, $this->amount, false, $get_currency_wise_seperator ) . '</span>';

			if ( isset( $currencies[ $currency ] ) ) {
				$currency = $currencies[ $currency ];
			} else {
				$currencies_all = $arm_payment_gateways->arm_get_all_currencies();
				$currency       = isset( $currencies_all[ strtoupper( $currency ) ] ) ? $currencies_all[ strtoupper( $currency ) ] : '';
			}
			$planText = '';
			if ( $this->is_paid() ) {
				if ( $this->is_lifetime() ) {
					if ( $currency_position == 'prefix' ) {
						$planText .= $currency . $arm_plan_amount;
					} else {
						$planText .= $arm_plan_amount . $currency;
					}
				} else {
					if ( $this->payment_type == 'subscription' ) {
						if ( $showTrialInfo ) {
							if ( ! empty( $this->recurring_data['trial'] ) ) {
								if ( $this->recurring_data['trial']['amount'] > 0 ) {
									if ( $currency_position == 'prefix' ) {
										$planText .= "{$currency}{$this->recurring_data['trial']['amount']}";
									} else {
										$planText .= "{$this->recurring_data['trial']['amount']}{$currency}";
									}
								} else {
									$planText .= esc_html__( 'Free', 'armember-membership' );
								}
							}
						}
						if ( $currency_position == 'prefix' ) {
							$planText .= "{$currency}{$arm_plan_amount}";
						} else {
							$planText .= "{$arm_plan_amount}{$currency} ";
						}
					} elseif ( $this->payment_type == 'one_time' ) {
						if ( $currency_position == 'prefix' ) {
							$planText .= "{$currency}{$arm_plan_amount}";
						} else {

							$planText .= "{$arm_plan_amount}{$currency}";
						}
					}
				}
			} else {
				if ( $currency_position == 'prefix' ) {
					$planText = "{$currency}{$arm_plan_amount}";
				} else {
					$planText = "{$arm_plan_amount}{$currency}";
				}
			}

			return $planText;
		}

		public function setup_plan_text( $showTrialInfo = true ) {
			global $arm_subscription_plans, $arm_payment_gateways;
			$currency = $arm_payment_gateways->arm_get_global_currency();
			$planText = '';
			if ( $this->is_paid() ) {
				if ( $this->is_lifetime() ) {
					$planText .= $this->amount . ' ' . $currency . ' ' . esc_html__( 'For Lifetime', 'armember-membership' );
				} else {
					if ( $this->payment_type == 'subscription' ) {
						if ( $showTrialInfo ) {
							if ( ! empty( $this->recurring_data['trial'] ) ) {
								if ( $this->recurring_data['trial']['amount'] > 0 ) {
									$planText .= "{$this->recurring_data['trial']['amount']} {$currency}";
								} else {
									$planText .= esc_html__( 'Free', 'armember-membership' );
								}
								$planText     .= ' ' . esc_html__( 'for the first', 'armember-membership' ) . ' ';
								$trialInterval = $this->recurring_data['trial']['interval'];
								if ( $this->recurring_data['trial']['period'] == 'Y' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'years', 'armember-membership' ) : esc_html__( 'year', 'armember-membership' );
								} elseif ( $this->recurring_data['trial']['period'] == 'M' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'months', 'armember-membership' ) : esc_html__( 'month', 'armember-membership' );
								} elseif ( $this->recurring_data['trial']['period'] == 'W' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'weeks', 'armember-membership' ) : esc_html__( 'week', 'armember-membership' );
								} elseif ( $this->recurring_data['trial']['period'] == 'D' ) {
									$planText .= ( $trialInterval > 1 ) ? "{$trialInterval} " . esc_html__( 'days', 'armember-membership' ) : esc_html__( 'day', 'armember-membership' );
								}
								$planText .= ', ' . esc_html__( 'Then', 'armember-membership' ) . ' ';
							}
						}
						$typeArrayMany = array(
							'D' => esc_html__( 'days', 'armember-membership' ),
							'W' => esc_html__( 'weeks', 'armember-membership' ),
							'M' => esc_html__( 'months', 'armember-membership' ),
							'Y' => esc_html__( 'years', 'armember-membership' ),
						);
						$typeArray     = array(
							'D' => esc_html__( 'day', 'armember-membership' ),
							'W' => esc_html__( 'week', 'armember-membership' ),
							'M' => esc_html__( 'month', 'armember-membership' ),
							'Y' => esc_html__( 'year', 'armember-membership' ),
						);
						$period        = $this->recurring_data['period'];
						$interval      = $this->recurring_data['interval'];
						$cycles        = $this->recurring_data['rec_time'];
						$recText       = ( $interval > 1 ) ? "{$interval} {$typeArrayMany[$period]}" : "{$typeArray[$period]}";
						$planText     .= "{$this->amount} {$currency} " . esc_html__( 'for each', 'armember-membership' ) . " {$recText}";
						if ( ! empty( $cycles ) && $cycles != '0' && is_numeric( $cycles ) ) {
							$planText .= ', ' . esc_html__( 'for', 'armember-membership' ) . " {$cycles} " . esc_html__( 'installments', 'armember-membership' );
						}
					} elseif ( $this->payment_type == 'one_time' ) {
						$period_options = $this->options['eopa'];
						$eopaType       = $period_options['type'];
						$eopaTime       = '';
						switch ( $eopaType ) {
							case 'D':
								$num      = ( isset( $period_options['days'] ) ) ? $period_options['days'] : 1;
								$eopaTime = " $num day(s)";
								break;
							case 'W':
								$num      = ( isset( $period_options['weeks'] ) ) ? $period_options['weeks'] : 1;
								$eopaTime = " $num week(s)";
								break;
							case 'M':
								$num      = ( isset( $period_options['months'] ) ) ? $period_options['months'] : 1;
								$eopaTime = " $num month(s)";
								break;
							case 'Y':
								$num      = ( isset( $period_options['years'] ) ) ? $period_options['years'] : 1;
								$eopaTime = " $num year(s)";
								break;
							default:
								$num      = ( isset( $period_options['days'] ) ) ? $period_options['days'] : 1;
								$eopaTime = " $num day(s)";
								break;
						}
						$planText .= "{$this->amount} {$currency} " . esc_html__( 'as One Time payment for', 'armember-membership' ) . " {$eopaTime}";
					}
				}
			}
			return $planText;
		}

		/* return plan start date and trial start date */

		function arm_trial_and_plan_start_date( $nowMysql = '', $payment_mode = '', $allow_trial = true, $payment_cycle = 0 ) {
			$return_array['arm_trial_start_date']    = '';
			$return_array['arm_expire_plan_trial']   = '';
			$return_array['subscription_start_date'] = '';
			if ( $nowMysql === '' ) {
				$nowMysql = strtotime( current_time( 'mysql' ) );
			}
			$return_array['subscription_start_date'] = $nowMysql;
			$current_day                             = date( 'Y-m-d', $nowMysql ); //phpcs:ignore
			if ( $this->has_trial_period() && $this->is_recurring() && $allow_trial ) {
				$plan_options = $this->options;
				if ( isset( $plan_options['payment_cycles'] ) && ! empty( $plan_options['payment_cycles'] ) ) {
					if ( $payment_cycle === '' ) {
						$payment_cycle = 0;
					}
					$opt_recurring          = $plan_options['payment_cycles'][ $payment_cycle ];
					$period_options['type'] = ! empty( $opt_recurring['billing_type'] ) ? $opt_recurring['billing_type'] : 'M';
				} else {
					$period_options = $plan_options['recurring'];
				}

				$billing_start_day                    = $this->options['recurring']['manual_billing_start'];
				$return_array['arm_trial_start_date'] = $nowMysql;
				$trial_type                           = $this->options['trial']['type'];

				if ( $payment_mode != 'manual_subscription' ) {
					switch ( $trial_type ) {
						case 'D':
							$days                                    = $this->options['trial']['days'];
							$return_array['subscription_start_date'] = strtotime( date( 'Y-m-d', strtotime( "$current_day + $days day" ) ) ); //phpcs:ignore
							break;
						case 'W':
							$weeks                                   = $this->options['trial']['weeks'];
							$return_array['subscription_start_date'] = strtotime( date( 'Y-m-d', strtotime( "$current_day + $weeks week" ) ) ); //phpcs:ignore
							break;
						case 'M':
							$months                                  = $this->options['trial']['months'];
							$return_array['subscription_start_date'] = strtotime( date( 'Y-m-d', strtotime( "$current_day + $months month" ) ) ); //phpcs:ignore
							break;
						case 'Y':
							$years                                   = $this->options['trial']['years'];
							$return_array['subscription_start_date'] = strtotime( date( 'Y-m-d', strtotime( "$current_day + $years year" ) ) ); //phpcs:ignore
							break;
						default:
							break;
					}
					$expire_date = $return_array['subscription_start_date'];

					$return_array['arm_expire_plan_trial'] = $expire_date;
				} else {
					if ( $billing_start_day == 'transaction_day' ) {
						switch ( $trial_type ) {
							case 'D':
								$days                                    = $this->options['trial']['days'];
								$return_array['subscription_start_date'] = strtotime( date( 'Y-m-d', strtotime( "$current_day+$days day" ) ) ); //phpcs:ignore
								break;
							case 'W':
								$weeks                                   = $this->options['trial']['weeks'];
								$return_array['subscription_start_date'] = strtotime( date( 'Y-m-d', strtotime( "$current_day+$weeks week" ) ) ); //phpcs:ignore
								break;
							case 'M':
								$months                                  = $this->options['trial']['months'];
								$return_array['subscription_start_date'] = strtotime( date( 'Y-m-d', strtotime( "$current_day+$months month" ) ) ); //phpcs:ignore
								break;
							case 'Y':
								$years                                   = $this->options['trial']['years'];
								$return_array['subscription_start_date'] = strtotime( date( 'Y-m-d', strtotime( "$current_day+$years year" ) ) ); //phpcs:ignore
								break;
							default:
								break;
						}
					} else {
						switch ( $trial_type ) {
							case 'D':
								$trial_days     = $this->options['trial']['days'];
								$trial_end_date = date( 'Y-m-d', strtotime( "$current_day+$trial_days day" ) ); //phpcs:ignore
								$trial_end_day  = date( 'd', strtotime( $trial_end_date ) ); //phpcs:ignore

								/* If recurring type daily( Recurring Using Days ) than we will simply add trial days to current day */
								if ( $trial_end_day < $billing_start_day || $period_options['type'] == 'D' ) {
									$return_array['subscription_start_date'] = strtotime( $trial_end_date );
								} else {
									$return_array['subscription_start_date'] = strtotime( date( 'Y-m-' . $billing_start_day, strtotime( "$current_day+$trial_days day" ) ) ); //phpcs:ignore
								}

								if ( $return_array['subscription_start_date'] < $nowMysql ) {
									$return_array['subscription_start_date'] = strtotime( $trial_end_date );
								}

								break;
							case 'W':
								$trial_weeks    = $this->options['trial']['weeks'];
								$trial_end_date = date( 'Y-m-d', strtotime( "$current_day+$trial_weeks week" ) ); //phpcs:ignore
								$trial_end_day  = date( 'd', strtotime( $trial_end_date ) ); //phpcs:ignore
								/* If recurring type daily( Recurring Using Days ) than we will simply add trial days to current day */
								if ( $trial_end_day < $billing_start_day || $period_options['type'] == 'D' ) {
									$return_array['subscription_start_date'] = strtotime( $trial_end_date );
								} else {
									$return_array['subscription_start_date'] = strtotime( date( 'Y-m-' . $billing_start_day, strtotime( "$current_day+$trial_weeks week" ) ) ); //phpcs:ignore
								}

								if ( $return_array['subscription_start_date'] < $nowMysql ) {
									$return_array['subscription_start_date'] = strtotime( $trial_end_date );
								}

								break;
							case 'M':
								$trial_months   = $this->options['trial']['months'];
								$trial_end_date = date( 'Y-m-d', strtotime( "$current_day+$trial_months month" ) ); //phpcs:ignore
								$trial_end_day  = date( 'd', strtotime( $trial_end_date ) ); //phpcs:ignore
								/* If recurring type daily( Recurring Using Days ) than we will simply add trial days to current day */
								if ( $trial_end_day < $billing_start_day || $period_options['type'] == 'D' ) {
									$return_array['subscription_start_date'] = strtotime( $trial_end_date );
								} else {
									$return_array['subscription_start_date'] = strtotime( date( 'Y-m-' . $billing_start_day, strtotime( "$current_day+$trial_months month" ) ) ); //phpcs:ignore
								}

								if ( $return_array['subscription_start_date'] < $nowMysql ) {
									$return_array['subscription_start_date'] = strtotime( $trial_end_date );
								}
								break;
							case 'Y':
								$trial_years    = $this->options['trial']['years'];
								$trial_end_date = date( 'Y-m-d', strtotime( "$current_day+$trial_years year" ) ); //phpcs:ignore
								$trial_end_day  = date( 'd', strtotime( $trial_end_date ) ); //phpcs:ignore
								/* If recurring type daily( Recurring Using Days ) than we will simply add trial days to current day */
								if ( $trial_end_day < $billing_start_day || $period_options['type'] == 'D' ) {
									$return_array['subscription_start_date'] = strtotime( $trial_end_date );
								} else {
									$return_array['subscription_start_date'] = strtotime( date( 'Y-m-' . $billing_start_day, strtotime( "$current_day+$trial_years year" ) ) ); //phpcs:ignore
								}

								if ( $return_array['subscription_start_date'] < $nowMysql ) {
									$return_array['subscription_start_date'] = strtotime( $trial_end_date );
								}
								break;
							default:
								break;
						}
					}
					$expire_date = $return_array['subscription_start_date'];

					$return_array['arm_expire_plan_trial'] = $expire_date;
				}
			} else {
				$return_array['arm_trial_start_date']    = '';
				$return_array['arm_expire_plan_trial']   = '';
				$return_array['subscription_start_date'] = $nowMysql;
			}
			return $return_array;
		}

	}

}
