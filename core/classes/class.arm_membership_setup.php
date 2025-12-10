<?php 
if ( ! class_exists( 'ARM_membership_setup_Lite' ) ) {

	class ARM_membership_setup_Lite {

		function __construct() {
			global $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings;
			add_action( 'wp_ajax_arm_delete_single_setup', array( $this, 'arm_delete_single_setup' ) );
			add_action( 'wp_ajax_arm_refresh_setup_items', array( $this, 'arm_refresh_setup_items' ) );
			add_action( 'wp_ajax_arm_update_plan_form_gateway_selection', array( $this, 'arm_update_plan_form_gateway_selection' ) );
			/* Membership Setup Wizard Form Shortcode Ajax Action */
			add_action( 'wp_ajax_arm_membership_setup_form_ajax_action', array( $this, 'arm_membership_setup_form_ajax_action' ) );
			add_action( 'wp_ajax_nopriv_arm_membership_setup_form_ajax_action', array( $this, 'arm_membership_setup_form_ajax_action' ) );
			add_action( 'wp_ajax_arm_save_membership_setups', array( $this, 'arm_save_membership_setups_func' ) );
			add_shortcode( 'arm_setup', array( $this, 'arm_setup_shortcode_func' ) );
			add_shortcode( 'arm_setup_internal', array( $this, 'arm_setup_shortcode_func_internal' ) );

			add_action( 'arm_before_render_membership_setup_form', array( $this, 'arm_check_include_js_css' ), 10, 2 );
			add_action( 'wp_ajax_arm_renew_plan_action', array( $this, 'arm_renew_update_plan_action_func' ) );
			// add_action('wp_ajax_nopriv_arm_renew_plan_action', array($this, 'arm_renew_update_plan_action_func'));
			add_action( 'wp_ajax_arm_update_plan_action', array( $this, 'arm_renew_update_plan_action_func' ) );
			// add_action('wp_ajax_arm_save_configure_preview_data', array($this, 'arm_save_configure_signup_preview_data'));
			add_action( 'wp', array( $this, 'arm_membership_setup_preview_func' ) );
			add_action( 'arm_cancel_subscription_gateway_action', array( $this, 'arm_cancel_bank_transfer_subscription' ), 10, 2 );

			add_action('wp_ajax_arm_setup_edit_detail',array($this,'arm_setup_edit_detail_func'));

			add_action('wp_ajax_arm_get_configure_setup_details', array($this, 'arm_get_configure_setup_details_func'));
		}

		function arm_get_configure_setup_details_func(){
            global $wp,$wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_global_settings, $arm_email_settings,  $arm_subscription_plans, $arm_payment_gateways,$arm_pay_per_post_feature,$arm_common_lite,$arm_capabilities_global;

            $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_setups'], '1',1); //phpcs:ignore 

            $setup_result_sql = "SELECT * FROM `" . $ARMemberLite->tbl_arm_membership_setup . "` ORDER BY `arm_setup_id` DESC";//phpcs:ignore --Reason: $tbl_arm_membership_setup is a table name. False Positive Alarm.No need to prepare query without Where clause.
            

            // $setup_result = $wpdb->get_results($setup_result_sql,ARRAY_A);//phpcs:ignore --Reason: $tbl_arm_membership_setup is a table name. False Positive Alarm.No need to prepare query without Where clause.
        
            $results = $wpdb->get_results($setup_result_sql); //phpcs:ignore --Reason $sql is a prepareed query

            $before_filter = count($results);

            $sSearch = !empty($_REQUEST['sSearch']) ? trim(sanitize_text_field($_REQUEST['sSearch'])) : '';

            if(!empty($sSearch))
            {
                $where_bgs = $wpdb->prepare("`arm_setup_name` LIKE %s",'%'.$sSearch.'%');
                $setup_result_sql = "SELECT `arm_setup_id`, `arm_setup_name`, `arm_setup_modules`, `arm_created_date`,`arm_setup_type` FROM `" . $ARMemberLite->tbl_arm_membership_setup . "` WHERE ".$where_bgs." ORDER BY `arm_setup_id` DESC";
            }

            $after_filter_data = $wpdb->get_results($setup_result_sql);//phpcs:ignore --Reason 
            $after_filter = count($after_filter_data);

            $plan_offset = isset($_REQUEST['iDisplayStart']) ? intval($_REQUEST['iDisplayStart']) : 0;
            $plan_number = isset($_REQUEST['iDisplayLength']) ? intval($_REQUEST['iDisplayLength']) : 10;

            $phlimit = "LIMIT {$plan_offset},{$plan_number}";

            $setup_result = $wpdb->get_results($setup_result_sql.' '.$phlimit);

            $grid_data = array();
            $ai = 0;

            if (!empty($setup_result)) {

                foreach ( $setup_result as $val ) {
                    $setupID = $val->arm_setup_id;                   
                    $grid_data[$ai][0] = '<a href="javascript:void(0)" class="arm_get_form_link arm_edit_setup_form_link" data-form_id="'. intval($setupID).'">' . stripslashes( $val->arm_setup_name ) . '</a>';
                    $setup_type = esc_html__('Membership Plan','armember-membership');
                    $arm_setup_modules = maybe_unserialize( $val->arm_setup_modules );
                    $module_plans       = ( isset( $arm_setup_modules['modules']['plans'] ) ) ? $arm_setup_modules['modules']['plans'] : array();
                    $plan_title         = $arm_subscription_plans->arm_get_comma_plan_names_by_ids( $module_plans );

                    $grid_data[$ai][1] = ( ! empty( $plan_title ) ) ? stripslashes_deep( $plan_title ) : '--';
                    $shortCode = '[arm_setup id="' . $setupID . '"]';
                    $grid_data[$ai][2] = '<div class="arm_shortcode_text arm_form_shortcode_box">
                        <span class="armCopyText">'. esc_html($shortCode).'</span>
                        <span class="arm_click_to_copy_text" data-code="'. esc_attr( $shortCode ).'">'. esc_html__( 'Click to copy', 'armember-membership' ).'</span>
                        <span class="arm_copied_text"><img src="'. esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/copied_ok.png" alt="ok"/>'. esc_html__( 'Code Copied', 'armember-membership' ).'</span>
                    </div>';

                    $module_gateways = ( isset( $arm_setup_modules['modules']['gateways'] ) ) ? $arm_setup_modules['modules']['gateways'] : array();
                    $gateway_title   = '--';
                    if ( ! empty( $module_gateways ) ) {
                        $gateway_title = '';
                        foreach ( $module_gateways as $key => $gateway ) {
                            $gateway_title .= $arm_payment_gateways->arm_gateway_name_by_key( $gateway ) . ', ';
                        }
                    }

                    $grid_data[$ai][3] = rtrim( $gateway_title, ', ' ); //phpcs:ignore

                    $module_plans = ( isset( $arm_setup_modules['modules']['forms'] ) ) ? $arm_setup_modules['modules']['forms'] : 0;

                    $module_form = new ARM_Form_Lite( 'id', $module_plans );
                    if ( $module_form->exists() ) {
                        $grid_data[$ai][4] = $module_form->form_detail['arm_form_label']; //phpcs:ignore
                    } else {
                        $grid_data[$ai][4] = '--';
                    }

                    $grid_data_action_btn = '<div class="arm_grid_action_btn_container">
                        <a href="javascript:void(0)" class="arm_get_form_link arm_edit_setup_form_link" data-form_id="'. intval($setupID).'">
                            <img src="'. esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/grid_edit.svg" onmouseover="this.src=\''. esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/grid_edit_hover.svg\';" class="armhelptip" title="'. esc_attr__( 'Edit Setup', 'armember-membership' ).'" onmouseout="this.src=\''. esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/grid_edit.svg\';" />
                        </a>
                        <a href="javascript:void(0)" onclick="showConfirmBoxCallback('. intval($setupID).');" data-form_id="'.  intval($setupID).'">
                            <img src="'. esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/grid_delete.svg" class="armhelptip" title="'. esc_attr__( 'Delete Setup', 'armember-membership' ).'" onmouseover="this.src=\''. esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/grid_delete_hover.svg\';" onmouseout="this.src=\''. esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/grid_delete.svg\';" style="cursor:pointer"/>
                        </a>';

                        $grid_data_action_btn .= $arm_global_settings->arm_get_confirm_box( $setupID, esc_html__( 'Are you sure you want to delete this setup?', 'armember-membership' ), 'arm_setup_delete_btn','', esc_html__('Delete', 'armember-membership'), esc_attr__('Cancel', 'armember-membership'), esc_attr__('Delete', 'armember-membership') ); //phpcs:ignore                       
                    $grid_data_action_btn .= '</div>';

                    $grid_data[$ai][5] = $grid_data_action_btn;
                    $ai++;

                }
            }
            $sEcho = isset($_REQUEST['sEcho']) ? intval($_REQUEST['sEcho']) : intval(10);
            $columns = esc_html__('Setup Name', 'armember-membership') . ',' . esc_html__('Plans', 'armember-membership') . ',' . esc_html__('Gateways', 'armember-membership') . ',' . esc_html__('Member Form', 'armember-membership') . ','. esc_html__('Shortcode', 'armember-membership') . ',';
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

		function arm_save_configure_signup_preview_data() {
			global $ARMemberLite, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_setups'], '0' );
			
			$arm_transient_uniq_id   = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : ''; // phpcs:ignore --Reason WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$arm_transient_form_data = maybe_serialize( $_REQUEST ); //phpcs:ignore

			set_transient( 'arm_preview_transient_' . $arm_transient_uniq_id, $arm_transient_form_data, 86400 );
		}

		function arm_renew_update_plan_action_func() {
			global $ARMemberLite;
			$arm_capabilities = '';
			$ARMemberLite->arm_check_user_cap( $arm_capabilities, '0' );//phpcs:ignore --Reason:Verifying nonce

			$plan_id  = intval( $_POST['plan_id'] ); //phpcs:ignore
			$setup_id = intval( $_POST['setup_id'] ); //phpcs:ignore
			if ( is_user_logged_in() ) {
				echo do_shortcode( '[arm_setup_internal id="' . $setup_id . '" hide_plans="1" subscription_plan="' . $plan_id . '"]' );
			} else {
				global $arm_member_forms, $ARMemberLite;
				$default_login_form_id = $arm_member_forms->arm_get_default_form_id( 'login' );
				echo do_shortcode( "[arm_form id='$default_login_form_id' is_referer='1']" );
				$ARMemberLite->enqueue_angular_script( true );
			}
			die;
		}

		function arm_cancel_bank_transfer_subscription( $user_id, $plan_id ) {
			global $wpdb, $ARMemberLite, $arm_global_settings, $arm_subscription_plans, $arm_transaction, $arm_payment_gateways, $arm_manage_communication;
			if ( ! empty( $user_id ) && $user_id != 0 && ! empty( $plan_id ) && $plan_id != 0 ) {
				$defaultPlanData  = $arm_subscription_plans->arm_default_plan_array();
				$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
				$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
				$planData         = shortcode_atts( $defaultPlanData, $userPlanDatameta );

				$user_payment_gateway = $planData['arm_user_gateway'];

				$user_detail = get_userdata( $user_id );
				$payer_email = $user_detail->user_email;

				if ( in_array( strtolower( $user_payment_gateway ), array( 'bank_transfer', 'manual' ) ) ) {
					$arm_manage_communication->arm_user_plan_status_action_mail(
						array(
							'plan_id' => $plan_id,
							'user_id' => $user_id,
							'action'  => 'on_cancel_subscription',
						)
					);
				}
			}
		}

		function arm_membership_setup_preview_func() {
			if ( isset( $_REQUEST['arm_setup_preview'] ) && $_REQUEST['arm_setup_preview'] == '1' ) { //phpcs:ignore
				global $wpdb, $ARMemberLite, $arm_capabilities_global;
				
				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_setups'], '0' );

				if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_preview.php' ) ) {
					include MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_preview.php';
				}
				exit;
			}
		}

		function arm_membership_setup_form_ajax_action( $setup_id = 0, $post_data = array() ) {

			global $wp, $wpdb, $current_user, $arm_slugs, $arm_lite_errors, $ARMemberLite, $arm_member_forms, $arm_global_settings, $arm_payment_gateways,$arm_subscription_plans, $payment_done,$arm_manage_communication, $arm_transaction;
			$ARMemberLite->arm_session_start();
			$post_data = ( ! empty( $_POST ) ) ? $_POST : $post_data; //phpcs:ignore
			$setup_id      = ( ! empty( $post_data['setup_id'] ) && $post_data['setup_id'] != 0 ) ? intval( $post_data['setup_id'] ) : $setup_id;
			$err_msg       = $arm_global_settings->common_message['arm_general_msg'];
			$err_msg       = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'Sorry, Something went wrong. Please try again.', 'armember-membership' );
			$response      = array(
				'status'  => 'error',
				'type'    => 'message',
				'message' => $err_msg,
			);
			$validate      = true;
			$validate_msgs = array();
			if ( ! empty( $setup_id ) && $setup_id != 0 && ! empty( $post_data ) && $post_data['setup_action'] == 'membership_setup' ) {

				do_action( 'arm_before_setup_form_action', $setup_id, $post_data );
				$user_ID = 0;
				if ( is_user_logged_in() ) {
					$user_ID = get_current_user_id();
					do_action( 'arm_modify_content_on_plan_change', $post_data, $user_ID );
				}

				/* Unset unused variables. */
				unset( $post_data['ARMSETUPNEXT'] );
				unset( $post_data['ARMSETUPSUBMIT'] );
				//unset( $post_data['setup_action'] );

				if ( isset( $post_data['arm_user_plan'] ) ) {
					unset( $post_data['arm_user_plan'] );
				}

				if ( isset( $post_data['arm_primary_status'] ) ) {
					unset( $post_data['arm_primary_status'] );
				}

				if ( isset( $post_data['arm_user_future_plan'] ) ) {
					unset( $post_data['arm_user_future_plan'] );
				}
				// sesion handling
                $post_data = $arm_member_forms->verify_fileupload_form_fields($post_data);

				$setup_data = $this->arm_get_membership_setup( $setup_id );

				$setup_data = apply_filters( 'arm_setup_data_before_submit', $setup_data, $post_data );

				if ( !empty( $setup_data ) && !empty( $setup_data['setup_modules']['modules'] ) ) {

					//$form_slug = isset( $post_data['arm_action'] ) ? sanitize_text_field( $post_data['arm_action'] ) : '';
					$form_slug = empty($user_ID) ? 'please-signup' : '';

					$form      = new ARM_Form_Lite( 'slug', 'please-signup' );
					$form_id   = 0;

					$plan_id = isset( $post_data['subscription_plan'] ) ? intval( $post_data['subscription_plan'] ) : 0;
					if ( $plan_id == 0 ) {
						$plan_id = isset( $post_data['_subscription_plan'] ) ? intval( $post_data['_subscription_plan'] ) : 0;
					}

					$plan            = new ARM_Plan_Lite( $plan_id );
					$plan_type       = $plan->type;
					$payment_gateway = isset( $post_data['payment_gateway'] ) ? sanitize_text_field( $post_data['payment_gateway'] ) : '';
					if ( $payment_gateway == '' ) {
						$payment_gateway = isset( $post_data['_payment_gateway'] ) ? sanitize_text_field( $post_data['_payment_gateway'] ) : '';
					}

					if ( $plan->is_recurring() ) {
						$payment_mode_ = ! empty( $post_data['arm_selected_payment_mode'] ) ? sanitize_text_field( $post_data['arm_selected_payment_mode'] ) : 'manual_subscription';
						if ( isset( $post_data['arm_payment_mode'][ $payment_gateway ] ) ) {
							$payment_mode_ = ! empty( $post_data['arm_payment_mode'][ $payment_gateway ] ) ? sanitize_text_field( $post_data['arm_payment_mode'][ $payment_gateway ] ) : 'manual_subscription';
						} else {
							// $setup_data = $this->arm_get_membership_setup($setup_id);
							// if (!empty($setup_data) && !empty($setup_data['setup_modules']['modules'])) {
								$setup_modules = $setup_data['setup_modules'];
								$modules       = $setup_modules['modules'];
								$payment_mode_ = $modules['payment_mode'][ $payment_gateway ];
							// }
						}
						$payment_mode = 'manual_subscription';
						if ( $payment_mode_ == 'both' ) {
							$payment_mode = ! empty( $post_data['arm_selected_payment_mode'] ) ? sanitize_text_field( $post_data['arm_selected_payment_mode'] ) : 'manual_subscription';
						} else {
							$payment_mode = $payment_mode_;
						}
					} else {
						$payment_mode = '';
					}

					if ( $payment_gateway == 'bank_transfer' && $plan->is_recurring() ) {
						$payment_mode = 'manual_subscription';
					}
					$post_data['arm_selected_payment_mode'] = $payment_mode;
					if ( $payment_gateway == 'bank_transfer' ) {
						if(!empty($post_data['bank_transfer']))
						{
							if(!empty($post_data['bank_transfer']['transaction_id']))
							{
								$post_data['bank_transfer']['transaction_id'] = sanitize_text_field( stripslashes_deep( $post_data['bank_transfer']['transaction_id'] ) );
							}

							if(!empty($post_data['bank_transfer']['bank_name']))
							{
								$post_data['bank_transfer']['bank_name'] = sanitize_text_field( $post_data['bank_transfer']['bank_name'] );
							}

							if(!empty($post_data['bank_transfer']['account_name']))
							{
								$post_data['bank_transfer']['account_name'] = sanitize_text_field( $post_data['bank_transfer']['account_name'] );
							}

							if(!empty($post_data['bank_transfer']['additional_info']))
							{
								$post_data['bank_transfer']['additional_info'] = sanitize_textarea_field( $post_data['bank_transfer']['additional_info'] );
							}

							if(!empty($post_data['bank_transfer']['transfer_mode']))
							{
								$post_data['bank_transfer']['transfer_mode'] = sanitize_text_field( $post_data['bank_transfer']['transfer_mode'] );
							}
						}
					}					

					$payment_cycle = 0;
					if ( $plan->is_recurring() ) {
						$payment_cycle = isset( $post_data[ 'payment_cycle_' . $plan_id ] ) ? intval( $post_data[ 'payment_cycle_' . $plan_id ] ) : 0;
					}
					$post_data['arm_selected_payment_cycle'] = $payment_cycle;

					// To modify setup form data before submit it.
					do_action( 'arm_before_submit_form_data', $post_data );

					$user_info         = wp_get_current_user();
					$current_user_plan = array();
					$user_id           = $user_info->ID;
					if ( ! empty( $user_info->ID ) ) {
						$entry_email       = $user_info->user_email;
						$current_user_plan = get_user_meta( $user_id, 'arm_user_plan_ids', true );
						$current_user_plan = ! empty( $current_user_plan ) ? $current_user_plan : array();
					} else {
						$entry_email = sanitize_email( $post_data['user_email'] );
					}
					$setup_redirect = ARMLITE_HOME_URL;

					$redirection_settings  = get_option( 'arm_redirection_settings' );
					$redirection_settings  = maybe_unserialize( $redirection_settings );
					$arm_default_setup_url = ( isset( $redirection_settings['setup']['default'] ) && ! empty( $redirection_settings['setup']['default'] ) ) ? $redirection_settings['setup']['default'] : ARMLITE_HOME_URL;

					if ( is_user_logged_in() ) {
						$is_validate_form_field = 0;
						$all_errors = $arm_member_forms->arm_member_validate_meta_details( $form, $post_data, $is_validate_form_field );
						if ( $all_errors !== true ) {
							$validate       = false;
							$validate_msgs += $all_errors;
						}
													// IF same plan already exists in arm_user_plan_ids
						if ( in_array( $plan_id, $current_user_plan ) ) {

							// renew or recurring
							$PlanData = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
							if ( ! empty( $PlanData ) ) {
								$PlanDetail = isset( $PlanData['arm_current_plan_detail'] ) ? $PlanData['arm_current_plan_detail'] : array();
								if ( ! empty( $PlanData ) ) {
									$same_old_plan = new ARM_Plan_Lite( 0 );
									$same_old_plan->init( (object) $PlanDetail );
								} else {
									$same_old_plan = new ARM_Plan_Lite( $plan_id );
								}

								if ( $same_old_plan->is_recurring() ) {
									$oldPaymentMode = $PlanData['arm_payment_mode'];
									if ( $oldPaymentMode == 'manual_subscription' ) {

										$oldPaymentCycle      = $PlanData['arm_payment_cycle'];
										$completed_recurrence = $PlanData['arm_completed_recurring'];

										$same_plan_data        = $same_old_plan->prepare_recurring_data( $oldPaymentCycle );
										$oldPlanTotalRecurring = $same_plan_data['rec_time'];

										if ( $oldPlanTotalRecurring == 'infinite' || ( $completed_recurrence !== '' && $completed_recurrence < $oldPlanTotalRecurring ) ) {
											$payment_cycle                           = $oldPaymentCycle;
											$post_data['arm_selected_payment_cycle'] = $oldPaymentCycle;

											$payment_mode                           = $oldPaymentMode;
											$post_data['arm_selected_payment_mode'] = $oldPaymentMode;

											$plan = $same_old_plan;
										}
									}
								}
							}

							$arm_redirection_setup_change_type = ( isset( $redirection_settings['setup_renew']['type'] ) && ! empty( $redirection_settings['setup_renew']['type'] ) ) ? $redirection_settings['setup_renew']['type'] : 'page';
							if ( $arm_redirection_setup_change_type == 'page' ) {
								$arm_redirection_setup_signup_page_id = ( isset( $redirection_settings['setup_renew']['page_id'] ) && ! empty( $redirection_settings['setup_renew']['page_id'] ) ) ? $redirection_settings['setup_renew']['page_id'] : 0;
								if ( ! empty( $arm_redirection_setup_signup_page_id ) ) {
									   $setup_redirect = $arm_global_settings->arm_get_permalink( '', $arm_redirection_setup_signup_page_id );
								} else {
									$setup_redirect = $arm_default_setup_url;
								}
							} elseif ( $arm_redirection_setup_change_type == 'url' ) {
								$setup_redirect = ( isset( $redirection_settings['setup_renew']['url'] ) && ! empty( $redirection_settings['setup_renew']['url'] ) ) ? $redirection_settings['setup_renew']['url'] : $arm_default_setup_url;
							}
						} else {
							// change
							$arm_redirection_setup_change_type = ( isset( $redirection_settings['setup_change']['type'] ) && ! empty( $redirection_settings['setup_change']['type'] ) ) ? $redirection_settings['setup_change']['type'] : 'page';
							if ( $arm_redirection_setup_change_type == 'page' ) {
								$arm_redirection_setup_signup_page_id = ( isset( $redirection_settings['setup_change']['page_id'] ) && ! empty( $redirection_settings['setup_change']['page_id'] ) ) ? $redirection_settings['setup_change']['page_id'] : 0;
								if ( ! empty( $arm_redirection_setup_signup_page_id ) ) {
									   $setup_redirect = $arm_global_settings->arm_get_permalink( '', $arm_redirection_setup_signup_page_id );
								} else {
									$setup_redirect = $arm_default_setup_url;
								}
							} elseif ( $arm_redirection_setup_change_type == 'url' ) {
								$setup_redirect = ( isset( $redirection_settings['setup_change']['url'] ) && ! empty( $redirection_settings['setup_change']['url'] ) ) ? $redirection_settings['setup_change']['url'] : $arm_default_setup_url;
							}
						}
					} else {
						$arm_redirection_setup_signup_type = ( isset( $redirection_settings['setup_signup']['type'] ) && ! empty( $redirection_settings['setup_signup']['type'] ) ) ? $redirection_settings['setup_signup']['type'] : 'page';
						if ( $arm_redirection_setup_signup_type == 'page' ) {
							$arm_redirection_setup_signup_page_id = ( isset( $redirection_settings['setup_signup']['page_id'] ) && ! empty( $redirection_settings['setup_signup']['page_id'] ) ) ? $redirection_settings['setup_signup']['page_id'] : 0;
							if ( ! empty( $arm_redirection_setup_signup_page_id ) ) {
								$setup_redirect = $arm_global_settings->arm_get_permalink( '', $arm_redirection_setup_signup_page_id );
							} else {
								$setup_redirect = $arm_default_setup_url;
							}
						} elseif ( $arm_redirection_setup_signup_type == 'url' ) {
							$setup_redirect = ( isset( $redirection_settings['setup_signup']['url'] ) && ! empty( $redirection_settings['setup_signup']['url'] ) ) ? $redirection_settings['setup_signup']['url'] : $arm_default_setup_url;
						}
					}

					if ( $plan->is_recurring() ) {
						$planData = $plan->prepare_recurring_data( $payment_cycle );
						$amount   = ! empty( $planData['amount'] ) ? $planData['amount'] : 0;
					} else {
						$amount = ! empty( $plan->amount ) ? $plan->amount : 0;
					}
					$amount      = str_replace( ',', '', $amount );
					$planOptions = $plan->options;

					if ( $plan_type == 'paid_finite' ) {
						$plan_expiry_type = ( isset( $planOptions['expiry_type'] ) && $planOptions['expiry_type'] != '' ) ? $planOptions['expiry_type'] : 'joined_date_expiry';
						$plan_expiry_date = ( isset( $planOptions['expiry_date'] ) && $planOptions['expiry_date'] != '' ) ? $planOptions['expiry_date'] : date( 'Y-m-d 23:59:59' ); //phpcs:ignore
					}

					$now = current_time( 'timestamp' );

					$setup_name = $setup_data['setup_name'];
					$modules    = $setup_data['setup_modules']['modules'];

					$module_order = array(
						'plans'    => 1,
						'forms'    => 2,
						'gateways' => 3,

					);

					$all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

					/* ====================/.Begin Module section validation./==================== */
					foreach ( $module_order as $module => $order ) {
						if ( ! empty( $modules[ $module ] ) ) {
							if ( $module == 'forms' && ! empty( $form_slug ) ) {
								$form_id         = $form->ID;
								$arm_form_fields = $form->fields;
								$field_options   = array();

								foreach ( $arm_form_fields as $fields ) {
									if ( $fields['arm_form_field_slug'] == 'user_login' ) {
										$field_options = $fields['arm_form_field_option'];
										if ( isset( $field_options['hide_username'] ) && $field_options['hide_username'] == 1 ) {
											$post_data['user_login'] = sanitize_email( $post_data['user_email'] );
										}
									}
								}

								$all_errors = $arm_member_forms->arm_member_validate_meta_details( $form, $post_data );

								if ( $all_errors !== true ) {
									$validate       = false;
									$validate_msgs += $all_errors;
								}
							}

							if ( $module == 'plans' ) {
								if ( $plan->exists() && $plan->is_active() ) {
									if ( $plan->is_paid() && empty( $payment_gateway ) ) {

										if ( $plan->is_recurring() && $plan->has_trial_period() && $payment_mode == 'manual_subscription' && $planOptions['trial']['amount'] < 1 ) {

										} else {

											$validate                           = false;
											$err_msg                            = $arm_global_settings->common_message['arm_no_select_payment_geteway'];
											$validate_msgs['subscription_plan'] = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'Your selected plan is paid, please select payment method.', 'armember-membership' );
										}
									}

									if ( $plan_type == 'paid_finite' && $plan_expiry_type == 'fixed_date_expiry' ) {
										if ( strtotime( $plan_expiry_date ) <= $now ) {
											$validate                           = false;
											$err_msg                            = $arm_global_settings->common_message['arm_invalid_plan_select'];
											$validate_msgs['subscription_plan'] = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'Selected plan is not valid.', 'armember-membership' );
										}
									}
								} else {
									$validate                           = false;
									$err_msg                            = $arm_global_settings->common_message['arm_invalid_plan_select'];
									$validate_msgs['subscription_plan'] = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'Selected plan is not valid.', 'armember-membership' );
								}
							}

							if ( $module == 'gateways' && $plan->is_paid() && ! empty( $payment_gateway ) ) {
								$gateway_options = $all_payment_gateways[ $payment_gateway ];

								$payment_mode_bt = '';
								if ( $plan->is_recurring() ) {
									$payment_mode_bt = 'manual_subscription';
								} else {
									$payment_mode_bt = 'auto_debit_subscription';
								}
								if ( $payment_gateway == 'bank_transfer' && $payment_mode_bt == '' ) {
									$validate                       = false;
									$validate_msgs['bank_transfer'] = esc_html__( 'Selected plan is not valid for bank transfer.', 'armember-membership' );
								} else {

									$pg_errors = apply_filters( 'arm_validate_payment_gateway_fields', true, $post_data, $payment_gateway, $gateway_options );
									if ( $pg_errors !== true ) {
										$validate                          = false;
										$validate_msgs[ $payment_gateway ] = $pg_errors;
									}
								}
							}
						}
					}

					/* ====================/.End Module section validation./==================== */
					if ( $validate && empty( $validate_msgs ) ) {

						do_action( 'arm_after_setup_form_validate_action', $setup_id, $post_data );
						$entry_id    = 0;
						$ip_address  = $ARMemberLite->arm_get_ip_address();
						$description = maybe_serialize(
							array(
								'browser'       => !empty( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '', //phpcs:ignore
								'http_referrer' => !empty( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '', //phpcs:ignore
							)
						);

						$entry_post_data = $post_data;
						if ( is_user_logged_in() ) {
							$user_information = wp_get_current_user();
							$user_id_info     = $user_information->ID;
							$username_info    = $user_information->user_login;

							$setup_redirect = str_replace( '{ARMCURRENTUSERNAME}', $username_info, $setup_redirect );
							$setup_redirect = str_replace( '{ARMCURRENTUSERID}', $user_id_info, $setup_redirect );
						}

						$entry_post_data['setup_redirect'] = $setup_redirect;
						foreach ( $all_payment_gateways as $k => $data ) {
							if ( isset( $entry_post_data[ $k ] ) && isset( $entry_post_data[ $k ]['card_number'] ) ) {
								$cc_no = $entry_post_data[ $k ]['card_number'];
								unset( $entry_post_data[ $k ] );
								if ( ! empty( $cc_no ) ) {
									$entry_post_data[ $k ]['card_number'] = $arm_transaction->arm_mask_credit_card_number( $cc_no );
								}
							}
						}

						$entry_post_data = apply_filters( 'arm_add_arm_entries_value', $entry_post_data );
						$new_entry       = array(
							'arm_entry_email'  => $entry_email,
							'arm_name'         => $setup_name,
							'arm_description'  => $description,
							'arm_ip_address'   => $ip_address,
							'arm_browser_info' => !empty( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '', //phpcs:ignore
							'arm_entry_value'  => maybe_serialize( $entry_post_data ),
							'arm_form_id'      => $form_id,
							'arm_user_id'      => $user_id,
							'arm_plan_id'      => $plan_id,
							'arm_created_date' => current_time( 'mysql' ),
						);

						$new_entry_results = $wpdb->insert( $ARMemberLite->tbl_arm_entries, $new_entry ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						$entry_id          = $wpdb->insert_id;

						if ( ! empty( $entry_id ) && $entry_id != 0 ) {
							$post_data['arm_entry_id'] = $entry_id;
							$payment_gateway_options   = isset( $all_payment_gateways[ $payment_gateway ] ) ? $all_payment_gateways[ $payment_gateway ] : array();
							if ( is_user_logged_in() ) {

								if ( ! empty( $modules['plans'] ) ) {

									$defaultPlanData  = $arm_subscription_plans->arm_default_plan_array();
									$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
									$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
									$userPlanData     = shortcode_atts( $defaultPlanData, $userPlanDatameta );

									$post_data['old_plan_id'] = ( isset( $current_user_plan ) && ! empty( $current_user_plan ) ) ? implode( ',', $current_user_plan ) : 0;
									$old_plan_id              = isset( $current_user_plan[0] ) ? $current_user_plan[0] : 0;
									$oldPlanData              = get_user_meta( $user_id, 'arm_user_plan_' . $old_plan_id, true );
									$oldPlanData              = ! empty( $oldPlanData ) ? $oldPlanData : array();
									$oldPlanData              = shortcode_atts( $defaultPlanData, $oldPlanData );
									$oldPlanDetail            = isset( $oldPlanData['arm_current_plan_detail'] ) ? $oldPlanData['arm_current_plan_detail'] : array();
									if ( ! empty( $oldPlanDetail ) ) {
										$old_plan = new ARM_Plan_Lite( 0 );
										$old_plan->init( (object) $oldPlanDetail );
									} else {
										$old_plan = new ARM_Plan_Lite( $old_plan_id );
									}

									$is_update_plan = true;

									$now                     = current_time( 'mysql' );
									$arm_last_payment_status = $wpdb->get_var( $wpdb->prepare( 'SELECT `arm_transaction_status` FROM `' . $ARMemberLite->tbl_arm_payment_log . '` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1', $user_id, $plan_id, $now ) );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_payment_log is a table name. False Positive alarm
									/* If plan is being renewd */
									if ( in_array( $plan_id, $current_user_plan ) ) {

										/*
										 if plan is recurring and old payment mode is auto debit, then if payment is done using 2checkout, old plan need to be canceled and plan renew date will be today date
										 * In other payment gateway, plan renew date will be old ecpiry date
										 */

										if ( $old_plan->is_recurring() ) {
											if ( $payment_mode == 'auto_debit_subscription' ) {
												$need_to_cancel_payment_gateway_array = $arm_payment_gateways->arm_need_to_cancel_old_subscription_gateways();
												$need_to_cancel_payment_gateway_array = ! empty( $need_to_cancel_payment_gateway_array ) ? $need_to_cancel_payment_gateway_array : array();
												if ( in_array( $payment_gateway, $need_to_cancel_payment_gateway_array ) ) {

													do_action( 'arm_cancel_subscription_gateway_action', $user_id, $plan_id );
												}
											}
										}
									} else {

										/*
										 if plan is being changed. */
										/*
										 check if upgrade downgrade action is applied
										 * if it is immmediately then, cancel old subscription if plan is recurring immediately */

										if ( $old_plan->exists() ) {
											if ( $old_plan->is_lifetime() || $old_plan->is_free() || ( $old_plan->is_recurring() && $plan->is_recurring() ) ) {
												$is_update_plan = true;
											} else {
												$change_act = 'immediate';
												if ( $old_plan->enable_upgrade_downgrade_action == 1 ) {
													if ( ! empty( $old_plan->downgrade_plans ) && in_array( $plan->ID, $old_plan->downgrade_plans ) ) {
														$change_act = $old_plan->downgrade_action;
													}
													if ( ! empty( $old_plan->upgrade_plans ) && in_array( $plan->ID, $old_plan->upgrade_plans ) ) {
														$change_act = $old_plan->upgrade_action;
													}
												}
												$subscr_effective = ! empty( $oldPlanData['arm_expire_plan'] ) ? $oldPlanData['arm_expire_plan'] : '';
												if ( $change_act == 'on_expire' && ! empty( $subscr_effective ) ) {
													$is_update_plan                      = false;
													$oldPlanData['arm_subscr_effective'] = $subscr_effective;
													$oldPlanData['arm_change_plan_to']   = $plan_id;
													update_user_meta( $user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData );
												}
											}
											if ( $is_update_plan && $old_plan->is_recurring() ) {

												do_action( 'arm_cancel_subscription_gateway_action', $user_id, $old_plan_id );
											}
										}
									}

									if ( ! $plan->is_free() ) {
										if ( ! empty( $payment_gateway_options ) ) {

											if ( $payment_gateway == 'bank_transfer' ) {

												$payment_mode_bt = '';
												if ( $plan->is_recurring() ) {
													$payment_mode_bt = 'manual_subscription';
												}

												$arm_user_old_plan_details                              = ( isset( $userPlanData['arm_current_plan_detail'] ) && ! empty( $userPlanData['arm_current_plan_detail'] ) ) ? $userPlanData['arm_current_plan_detail'] : array();
												$arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];
												$userPlanData['arm_current_plan_detail']                = $arm_user_old_plan_details;

												update_user_meta( $user_id, 'arm_user_plan_' . $plan_id, $userPlanData );
												update_user_meta( $user_id, 'arm_entry_id', $entry_id );

												if ( ! $plan->is_recurring() || $payment_mode_bt == 'manual_subscription' ) {
													$arm_payment_gateways->arm_bank_transfer_payment_gateway_action( $payment_gateway, $payment_gateway_options, $post_data, $entry_id );
													global $payment_done;
													$response['status'] = 'success';
													$response['type']   = 'redirect';

													$response['message'] = '<script data-cfasync="false" type="text/javascript" language="javascript">window.location.href="' . $setup_redirect . '"</script>';
												} else {
													$validate_msgs['payment_failed'] = esc_html__( 'Selected plan is not valid for bank transfer.', 'armember-membership' );
												}
											} else {

												$post_data = apply_filters( 'arm_change_posted_data_before_payment_outside', $post_data, $payment_gateway, $payment_gateway_options, $entry_id );

												do_action( 'arm_payment_gateway_validation_from_setup', $payment_gateway, $payment_gateway_options, $post_data, $entry_id );

												global $payment_done;

												if ( isset( $payment_done['status'] ) && $payment_done['status'] === false ) {
													$validate_msgs['payment_failed'] = $payment_done['error'];
												} else {

													$pgs_arrays = apply_filters( 'arm_update_new_subscr_gateway_outside', array() );
													$log_id     = $payment_done['log_id'];
													$log_detail = $wpdb->get_row( $wpdb->prepare('SELECT `arm_log_id`, `arm_user_id`, `arm_token`, `arm_transaction_id`, `arm_extra_vars` FROM `' . $ARMemberLite->tbl_arm_payment_log . "` WHERE `arm_log_id`=%d",$log_id) );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_payment_log is a table name. False Positive Alarm
													update_user_meta( $user_id, 'arm_entry_id', $entry_id );

													$userPlanData['arm_user_gateway']                       = $payment_gateway;
													$arm_user_old_plan_details                              = ( isset( $userPlanData['arm_current_plan_detail'] ) && ! empty( $userPlanData['arm_current_plan_detail'] ) ) ? $userPlanData['arm_current_plan_detail'] : array();
													$arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];
													$userPlanData['arm_current_plan_detail']                = $arm_user_old_plan_details;

													if ( $plan->is_recurring() ) {
														$userPlanData['arm_payment_mode']  = $payment_mode;
														$userPlanData['arm_payment_cycle'] = $payment_cycle;
													} else {
														$userPlanData['arm_payment_mode']  = '';
														$userPlanData['arm_payment_cycle'] = '';
													}

													update_user_meta( $user_id, 'arm_user_plan_' . $plan_id, $userPlanData );
													do_action( 'arm_update_user_meta_after_renew_outside', $user_id, $log_detail, $plan_id, $payment_gateway );

													if ( $is_update_plan ) {

														$arm_subscription_plans->arm_update_user_subscription( $user_id, $plan_id, '', true, $arm_last_payment_status );
													} else {
														$arm_subscription_plans->arm_add_membership_history( $user_id, $plan_id, 'change_subscription' );
													}

													if ( $plan->is_recurring() ) {
														$userPlanData = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
													}
													$response['status']  = 'success';
													$response['type']    = 'redirect';
													$response['message'] = '<script data-cfasync="false" type="text/javascript" language="javascript">window.location.href="' . $setup_redirect . '"</script>';
												}
											}
										} else {

											$err_msg                          = $arm_global_settings->common_message['arm_inactive_payment_gateway'];
											$validate_msgs['payment_gateway'] = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'Payment gateway is not active, please contact site administrator.', 'armember-membership' );
											$payment_done                     = array( 'status' => false );
										}
									} else {

										if ( $is_update_plan ) {
											$arm_subscription_plans->arm_update_user_subscription( $user_id, $plan_id );
										} else {
											$arm_subscription_plans->arm_add_membership_history( $user_id, $plan_id, 'change_subscription' );
										}
										$response['status']  = 'success';
										$response['type']    = 'redirect';
										$response['message'] = '<script data-cfasync="false" type="text/javascript" language="javascript">window.location.href="' . $setup_redirect . '"</script>';
									}
								}
							} else {
								if ( ! empty( $modules['plans'] ) && $plan->is_paid() ) {
									if ( ! empty( $payment_gateway_options ) ) {
										if ( $payment_gateway == 'bank_transfer' ) {
											$payment_mode_bt = 'manual_subscription';
											if ( $plan->is_recurring() ) {
												$payment_mode_bt = 'manual_subscription';
											}
											if ( ! $plan->is_recurring() || $payment_mode == 'manual_subscription' ) {
												$arm_payment_gateways->arm_bank_transfer_payment_gateway_action( $payment_gateway, $payment_gateway_options, $post_data, $entry_id );
												global $payment_done;
												$payment_log_id = '';
												if ( $payment_done['status'] == 1 ) {
													$payment_log_id = $payment_done['log_id'];
												}
												$response['status']  = 'success';
												$response['type']    = 'redirect';
												$response['message'] = '<script data-cfasync="false" type="text/javascript" language="javascript">window.location.href="' . $setup_redirect . '"</script>';
											} else {
												$validate_msgs['payment_failed'] = esc_html__( 'Selected plan is not valid for bank transfer.', 'armember-membership' );
											}
										} else {
											$post_data = apply_filters( 'arm_change_posted_data_before_payment_outside', $post_data, $payment_gateway, $payment_gateway_options, $entry_id );

											do_action( 'arm_payment_gateway_validation_from_setup', $payment_gateway, $payment_gateway_options, $post_data, $entry_id );
											global $payment_done;
											if ( isset( $payment_done['status'] ) && $payment_done['status'] === false ) {
												$validate_msgs['payment_failed'] = $payment_done['error'];
											}
										}
									} else {
										if ( $plan->is_recurring() && $plan->has_trial_period() && $payment_mode == 'manual_subscription' && $planOptions['trial']['amount'] == 0 ) {
											$payment_data   = array(
												'arm_user_id' => '0',
												'arm_first_name' => ( isset( $post_data['first_name'] ) ) ? $post_data['first_name'] : '',
												'arm_last_name' => ( isset( $post_data['last_name'] ) ) ? $post_data['last_name'] : '',
												'arm_plan_id' => ( ! empty( $plan_id ) ? $plan_id : 0 ),
												'arm_payment_gateway' => 'paypal',
												'arm_payment_type' => $plan->payment_type,
												'arm_token' => '-',
												'arm_payer_email' => ( isset( $post_data['user_email'] ) ) ? sanitize_email( $post_data['user_email'] ) : '',
												'arm_receiver_email' => '',
												'arm_transaction_id' => '-',
												'arm_transaction_payment_type' => $plan->payment_type,
												'arm_transaction_status' => 'completed',
												'arm_payment_mode' => $payment_mode,
												'arm_payment_date' => current_time( 'mysql' ),
												'arm_amount' => 0,
												'arm_currency' => 'USD',

												'arm_extra_vars' => '',
												'arm_created_date' => current_time( 'mysql' ),
											);
											$payment_log_id = $arm_payment_gateways->arm_save_payment_log( $payment_data );
											$payment_done   = array(
												'status'   => true,
												'log_id'   => $payment_log_id,
												'entry_id' => $entry_id,
											);
										} else {
											$err_msg                          = $arm_global_settings->common_message['arm_inactive_payment_gateway'];
											$validate_msgs['payment_gateway'] = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'Payment gateway is not active, please contact site administrator.', 'armember-membership' );
											$payment_done                     = array( 'status' => false );
										}
									}
								} else {
									$payment_done = array( 'status' => true );
								}
								if ( ! empty( $modules['forms'] ) && $payment_done['status'] == true ) {
									if ( in_array( $form->type, array( 'registration' ) ) ) {
										$post_data['arm_update_user_from_profile'] = 0;
										$user_id                                   = $arm_member_forms->arm_register_new_member( $post_data, $form );
										if ( is_numeric( $user_id ) && ! is_array( $user_id ) ) {
											if ( ! empty( $payment_log_id ) ) {
												$armLogTable    = $ARMemberLite->tbl_arm_payment_log;
												$chk_log_detail = $wpdb->get_row( $wpdb->prepare("SELECT `arm_log_id`, `arm_amount` FROM `{$armLogTable}` WHERE `arm_log_id`=%d",$payment_log_id) );//phpcs:ignore --Reason: $armLogTable is a table name. False Positive Alarm
												if ( ! empty( $chk_log_detail ) ) {
													$user_register_verification = isset( $arm_global_settings->global_settings['user_register_verification'] ) ? $arm_global_settings->global_settings['user_register_verification'] : 'auto';
													if ( $chk_log_detail->arm_amount == 0 && $user_register_verification == 'auto' ) {
														$arm_transaction->arm_change_bank_transfer_status( $payment_log_id, '1',0 );
													}
												}
											}
											$response['status'] = 'success';
											$response['type']   = 'redirect';

											$user_info = get_userdata( $user_id );
											$username  = $user_info->user_login;

											$setup_redirect = str_replace( '{ARMCURRENTUSERNAME}', $username, $setup_redirect );
											$setup_redirect = str_replace( '{ARMCURRENTUSERID}', $user_id, $setup_redirect );

											$response['message'] = '<script data-cfasync="false" type="text/javascript" language="javascript">window.location.href="' . $setup_redirect . '"</script>';
										} else {
											$validate_msgs['register_error'] = $arm_lite_errors->get_error_messages( 'arm_reg_error' );
										}
									}
								}
							}
						} else {
							$err_msg                        = $arm_global_settings->common_message['arm_general_msg'];
							$validate_msgs['entry_message'] = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'Sorry, Something went wrong. Please contact to site administrator.', 'armember-membership' );
						}
					}

					if ( ! empty( $validate_msgs ) ) {
						$response['status']  = 'error';
						$response['type']    = 'message';
						$response['message'] = '<div class="arm_error_msg arm-df__fc--validation__wrap"><ul>';
						foreach ( $validate_msgs as $err ) {
							if ( is_array( $err ) ) {
								foreach ( $err as $key => $err_msg ) {
									$response['message'] .= '<li>' . esc_html($err_msg) . '</li>';
								}
							} else {
								$response['message'] .= '<li>' . esc_html($err) . '</li>';
							}
						}
						$response['message'] .= '</ul></div>';
					} else {

						$response['status'] = 'success';
						if ( isset( $response['type'] ) && $response['type'] == 'redirect' ) {
							$response['message'] = $response['message'];
						} else {
							$response['type']    = 'message';
							$response['message'] = '<div class="arm_success_msg"><ul><li>' . esc_html($response['message']) . '</li></ul></div>';
						}
					}
				}
				do_action( 'arm_after_setup_form_action', $setup_id, $post_data );
			}
			$arm_return_script  = '';
			$response['script'] = apply_filters( 'arm_after_setup_submit_sucess_outside', $arm_return_script );
			if ( $post_data['action'] == 'arm_membership_setup_form_ajax_action' ) {
				echo wp_json_encode( $response );
				exit;
			} else {
				return $response;
			}
		}

		function arm_setup_shortcode_func_internal( $atts, $content = '' ) {

			global $wp, $wpdb, $current_user, $ARMemberLite, $arm_member_forms, $arm_global_settings, $arm_payment_gateways, $arm_subscription_plans, $arm_lite_bpopup_loaded, $ARMSPAMFILEURL;
			$ARMemberLite->arm_session_start();
			/* ====================/.Begin Set Shortcode Attributes./==================== */
			$defaults = array(
				'id'                           => 0, /* Membership Setup Wizard ID */
				'hide_title'                   => false,
				'class'                        => '',
				'popup'                        => false, /* Form will be open in popup box when options is true */
				'link_type'                    => 'link',
				'link_class'                   => '', /* /* Possible Options:- `link`, `button` */
				'link_title'                   => esc_html__( 'Click here to open Set up form', 'armember-membership' ), /* Default to form name */
				'popup_height'                 => '',
				'popup_width'                  => '',
				'overlay'                      => '0.6',
				'modal_bgcolor'                => '#000000',
				'redirect_to'                  => '',
				'link_css'                     => '',
				'link_hover_css'               => '',
				'is_referer'                   => '0',
				'preview'                      => false,
				'setup_data'                   => '',
				'subscription_plan'            => 0,
				'hide_plans'                   => 0,
				'payment_duration'             => 0,
				'setup_form_id'                => '',
				'your_current_membership_text' => esc_html__( 'Your Current Membership', 'armember-membership' ),
			);
			/* Extract Shortcode Attributes */
			$args = shortcode_atts( $defaults, $atts, 'arm_setup' );
			$args = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $args ); //phpcs:ignore
			extract( $args );
			$args['hide_title'] = ( $args['hide_title'] === 'true' || $args['hide_title'] == '1' ) ? true : false;
			$args['popup']      = ( $args['popup'] === 'true' || $args['popup'] == '1' ) ? true : false;
			$isPreview          = ( $args['preview'] === 'true' || $args['preview'] == '1' ) ? true : false;
			if ( $args['popup'] ) {
				$arm_lite_bpopup_loaded = 1;
			}
			$completed_recurrence = '';
			$total_recurring      = '';
			/* ====================/.End Set Shortcode Attributes./==================== */
			if ( ( ! empty( $args['id'] ) && $args['id'] != 0 ) || ( $isPreview && ! empty( $args['setup_data'] ) ) ) {

				$setupID = $args['id'];
				if ( $isPreview && ! empty( $args['setup_data'] ) ) {
					$setup_data                     = maybe_unserialize( $args['setup_data'] );
					$setup_data['arm_setup_labels'] = $setup_data['setup_labels'];
				} else {
					$setup_data = $this->arm_get_membership_setup( $setupID );
				}
				$setup_data = apply_filters( 'arm_setup_data_before_setup_shortcode', $setup_data, $args );
				do_action( 'arm_before_render_membership_setup_form', $setup_data, $args );

				if ( ! empty( $setup_data ) && ! empty( $setup_data['setup_modules']['modules'] ) ) {

					$setupRandomID         = $setupID . '_' . arm_generate_random_code();
					$global_currency       = $arm_payment_gateways->arm_get_global_currency();
					$current_user_id       = get_current_user_id();
					$current_user_plan_ids = get_user_meta( $current_user_id, 'arm_user_plan_ids', true );
					$current_user_plan_ids = ! empty( $current_user_plan_ids ) ? $current_user_plan_ids : array();
					$current_user_plan     = '';
					$current_plan_data     = array();
					if ( ! empty( $current_user_plan_ids ) ) {
						$current_user_plan = current( $current_user_plan_ids );
						$current_plan_data = get_user_meta( $current_user_id, 'arm_user_plan_' . $current_user_plan, true );
					}
					$setup_name         = ( ! empty( $setup_data['setup_name'] ) ) ? stripslashes( $setup_data['setup_name'] ) : '';
					$button_labels      = $setup_data['setup_labels']['button_labels'];
					$submit_btn         = ( ! empty( $button_labels['submit'] ) ) ? $button_labels['submit'] : esc_html__( 'Submit', 'armember-membership' );
					$setup_modules      = $setup_data['setup_modules'];
					$user_selected_plan = isset( $setup_modules['selected_plan'] ) ? $setup_modules['selected_plan'] : '';
					$modules            = $setup_modules['modules'];
					$setup_style        = isset( $setup_modules['style'] ) ? $setup_modules['style'] : array();

					$formPosition        = ( ! empty( $setup_style['form_position'] ) ) ? $setup_style['form_position'] : 'center';
					$plan_skin           = ( ! empty( $setup_style['plan_skin'] ) ) ? $setup_style['plan_skin'] : 3;
					$plan_selection_area = ( ! empty( $setup_style['plan_area_position'] ) ) ? $setup_style['plan_area_position'] : 'before';

					$fieldPosition = 'left';

					$modules['step'] = ( ! empty( $modules['step'] ) ) ? $modules['step'] : array( -1 );

					if ( $plan_selection_area == 'before' ) {
						$module_order = array(
							'plans'         => 1,
							'payment_cycle' => 2,
							'note'          => 3,
							'forms'         => 4,
							'gateways'      => 5,
							'order_detail'  => 6,
						);
					} else {

						$module_order = array(
							'forms'         => 1,
							'plans'         => 2,
							'payment_cycle' => 3,
							'note'          => 4,
							'gateways'      => 5,
							'order_detail'  => 6,
						);
					}

					$modules['forms'] = ( ! empty( $modules['forms'] ) && $modules['forms'] != 0 ) ? $modules['forms'] : 0;
					$step_one_modules = $step_two_modules = '';
					/*
					 Check `GET` or `POST` Data */
					/* first check if user have selected any plan than select that plan otherwise set value from options of setup */
					if ( $current_user_plan != '' ) {
						$selected_plan_id = $current_user_plan;
					} else {
						$selected_plan_id = $user_selected_plan;
					}
					if ( ! empty( $_REQUEST['subscription_plan'] ) && $_REQUEST['subscription_plan'] != 0 ) { //phpcs:ignore
						$selected_plan_id = intval( $_REQUEST['subscription_plan'] ); //phpcs:ignore
					}

					$selected_payment_duration = 1;
					if ( ! empty( $_REQUEST['payment_duration'] ) && $_REQUEST['payment_duration'] != 0 ) { //phpcs:ignore
						$selected_payment_duration = intval($_REQUEST['payment_duration']); //phpcs:ignore
					}
					if ( ! empty( $args['subscription_plan'] ) && $args['subscription_plan'] != 0 ) {
						$selected_plan_id = $args['subscription_plan'];
						if ( ! empty( $args['payment_duration'] ) && $args['payment_duration'] != 0 ) {
							$selected_payment_duration = $args['payment_duration'];
						}
					}

					$isHidePlans = false;
					if ( ! empty( $selected_plan_id ) && $selected_plan_id != 0 ) {
						if ( ! empty( $_REQUEST['hide_plans'] ) && $_REQUEST['hide_plans'] == 1 ) { //phpcs:ignore
							$isHidePlans = true;
						}
						if ( ! empty( $args['hide_plans'] ) && $args['hide_plans'] == 1 ) {
							$isHidePlans = true;
						}
					}

					$is_hide_plan_selection_area = false;
					if ( isset( $setup_style['hide_plans'] ) && $setup_style['hide_plans'] == 1 ) {
						$is_hide_plan_selection_area = true;
					}

					if ( is_user_logged_in() ) {
						global $current_user;
						if ( ! empty( $current_user->data->arm_primary_status ) ) {
							$current_user_status = $current_user->data->arm_primary_status;
						} else {
							$current_user_status = arm_get_member_status( $current_user_id );
						}
					}

					$selected_plan_data = array();
					$module_html        = $formStyle = $setupGoogleFonts = '';
					$errPosCCField      = 'right';
					if ( is_rtl() ) {
						$is_form_class_rtl = 'arm_form_rtl';
					} else {
						$is_form_class_rtl = 'arm_form_ltr';
					}
					$form_style_class = ' arm_form_0 arm_form_layout_writer armf_label_placeholder armf_alignment_left armf_layout_block armf_button_position_left ' . $is_form_class_rtl;
					$btn_style_class  = ' --arm-is-flat-style ';
					if ( ! empty( $modules['forms'] ) ) {
						/* Query Monitor Change */
						if ( isset( $GLOBALS['arm_setup_form_settings'] ) && isset( $GLOBALS['arm_setup_form_settings'][ $modules['forms'] ] ) ) {
							$form_settings = $GLOBALS['arm_setup_form_settings'][ $modules['forms'] ];
						} else {
							$form_settings = $wpdb->get_var( $wpdb->prepare('SELECT `arm_form_settings` FROM `' . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_form_id`=%d",$modules['forms']) );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_forms is table name. False Positive Alarm
							if ( ! isset( $GLOBALS['arm_setup_form_settings'] ) ) {
								$GLOBALS['arm_setup_form_settings'] = array();
							}
							$GLOBALS['arm_setup_form_settings'][ $modules['forms'] ] = $form_settings;
						}
						$form_settings = ( ! empty( $form_settings ) ) ? maybe_unserialize( $form_settings ) : array();
					}
					$plan_payment_cycles = array();

					foreach ( $module_order as $module => $order ) {

						$module_content                  = '';
						$arm_user_id                     = 0;
						$arm_user_old_plan               = 0;
						$plan_id_array                   = array();
						$arm_user_selected_payment_mode  = 0;
						$arm_user_selected_payment_cycle = 0;
						$arm_last_payment_status         = 'success';
						switch ( $module ) {
							case 'plans':
								if ( is_user_logged_in() ) {
									global $current_user;
									$arm_user_id = $current_user->ID;

									$user_firstname = $current_user->user_firstname;
									$user_lastname  = $current_user->user_lastname;
									$user_email     = $current_user->user_email;
									if ( $user_firstname != '' && $user_lastname != '' ) {
										$arm_user_firstname_lastname = $user_firstname . ' ' . $user_lastname;
									} else {
										$arm_user_firstname_lastname = $user_email;
									}

									if ( ! empty( $current_user_plan_ids ) ) {
										$plan_name_array = array();
										foreach ( $current_user_plan_ids as $plan_id ) {
											$planData                       = get_user_meta( $arm_user_id, 'arm_user_plan_' . $plan_id, true );
											$arm_user_selected_payment_mode = $planData['arm_payment_mode'];
											$arm_user_current_plan_detail   = $planData['arm_current_plan_detail'];

											$plan_name_array[] = isset( $arm_user_current_plan_detail['arm_subscription_plan_name'] ) ? stripslashes( $arm_user_current_plan_detail['arm_subscription_plan_name'] ) : '';
											$plan_id_array[]   = $plan_id;

											$curPlanDetail        = $planData['arm_current_plan_detail'];
											$completed_recurrence = $planData['arm_completed_recurring'];
											if ( ! empty( $curPlanDetail ) ) {
												$arm_user_old_plan_info = new ARM_Plan_Lite( 0 );
												$arm_user_old_plan_info->init( (object) $curPlanDetail );
											} else {
												$arm_user_old_plan_info = new ARM_Plan_Lite( $arm_user_old_plan );
											}
											$total_recurring           = '';
											$arm_user_old_plan_options = $arm_user_old_plan_info->options;
											if ( $arm_user_old_plan_info->is_recurring() ) {
												$arm_user_selected_payment_cycle = $planData['arm_payment_cycle'];
												$arm_user_old_plan_data          = $arm_user_old_plan_info->prepare_recurring_data( $arm_user_selected_payment_cycle );
												$total_recurring                 = $arm_user_old_plan_data['rec_time'];

												$now = current_time( 'mysql' );

												$arm_last_payment_status = $wpdb->get_var( $wpdb->prepare( 'SELECT `arm_transaction_status` FROM `' . $ARMemberLite->tbl_arm_payment_log . '` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1', $arm_user_id, $plan_id, $now ) );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_payment_log is a table name. 

											}

											$module_content .= '<input type="hidden" data-id="arm_user_firstname_lastname" value="' . esc_attr($arm_user_firstname_lastname) . '">';
											$module_content .= '<input type="hidden" data-id="arm_user_last_payment_status_' . esc_attr($plan_id) . '" value="' . esc_attr($arm_last_payment_status) . '">';

											$module_content .= '<input type="hidden" data-id="arm_user_done_payment_' . esc_attr($plan_id) . '" value="' . esc_attr($completed_recurrence) . '">';
											$module_content .= '<input type="hidden" data-id="arm_user_old_plan_total_cycle_' . esc_attr($plan_id) . '" value="' . esc_attr($total_recurring) . '">';

											$module_content .= '<input type="hidden" data-id="arm_user_selected_payment_cycle_' . esc_attr($plan_id) . '" value="' . esc_attr($arm_user_selected_payment_cycle) . '">';
											$module_content .= '<input type="hidden" data-id="arm_user_selected_payment_mode_' . esc_attr($plan_id) . '" value="' . esc_attr($arm_user_selected_payment_mode) . '">';
										}
									}
									$arm_is_user_logged_in_flag = 1;
								} else {
									$arm_is_user_logged_in_flag = 0;
								}

								if ( ! empty( $plan_id_array ) ) {
									$arm_user_old_plan = implode( ',', $plan_id_array );
								}

									$module_content .= '<input type="hidden" data-id="arm_user_old_plan" name="arm_user_old_plan" value="' . esc_attr($arm_user_old_plan) . '">';
									$module_content .= '<input type="hidden" name="arm_is_user_logged_in_flag" data-id="arm_is_user_logged_in_flag" value="' . esc_attr($arm_is_user_logged_in_flag) . '">';

									$all_active_plans = $arm_subscription_plans->arm_get_all_active_subscription_plans();
									$plans            = array_keys( $all_active_plans );
								if ( ! empty( $plans ) ) {

									$is_hide_class = '';
									if ( $isHidePlans == true || $is_hide_plan_selection_area == true ) {
										$is_hide_class = 'style="display:none;"';
									}
									$form_no = '';

									$form_layout = '';
									if ( ! empty( $modules['forms'] ) && $modules['forms'] != 0 ) {
										if ( ! empty( $form_settings ) ) {
											$form_no      = 'arm_form_' . $modules['forms'];
											$form_layout .= ' arm_form_layout_' . $form_settings['style']['form_layout'] . ' arm-default-form';

											if ( $form_settings['style']['form_layout'] == 'writer' ) {
												$form_layout .= ' arm-material-style arm_materialize_form ';
											} elseif ( $form_settings['style']['form_layout'] == 'rounded' ) {
												$form_layout .= ' arm-rounded-style ';
											} elseif ( $form_settings['style']['form_layout'] == 'writer_border' ) {
												$form_layout .= ' arm--material-outline-style arm_materialize_form ';
											}
										}
									}
									if ( ! empty( $current_user_plan_ids ) ) {

										$module_content .= '<div class="arm_current_user_plan_info">' . $your_current_membership_text . ': <span>' . implode( ', ', $plan_name_array ) . '</span></div>';
									}
									$module_content .= '<div class="arm_module_plans_container arm_module_box ' . $form_no . ' ' . $form_layout . '" ' . $is_hide_class . '>';

									$payment_plan_cycle_title = ( isset( $setup_data['setup_labels']['payment_cycle_field_title'] ) && ! empty( $setup_data['setup_labels']['payment_cycle_field_title'] ) ) ? $setup_data['setup_labels']['payment_cycle_field_title'] : esc_html__( 'Select Your Payment Cycle', 'armember-membership' );
									$column_type              = ( ! empty( $setup_modules['plans_columns'] ) ) ? $setup_modules['plans_columns'] : '1';
									$module_content          .= '<input type="hidden" name="arm_front_plan_skin_type" data-id="arm_front_plan_skin_type" value="' . esc_attr($setup_style['plan_skin']) . '">';
									$allowed_payment_gateways = array();
									if ( $setup_style['plan_skin'] == 'skin5' ) {
										$dropdown_class                = 'arm-df__form-field-wrap_select';
											$arm_allow_notched_outline = 0;
										if ( $form_settings['style']['form_layout'] == 'writer_border' ) {
											$arm_allow_notched_outline = 1;
											$inputPlaceholder          = '';
										}

											$arm_field_wrap_active_class = $ffield_label_html = $ffield_label = '';
										if ( ! empty( $arm_allow_notched_outline ) ) {
											$arm_field_wrap_active_class = ' arm-df__form-material-field-wrap';

											$ffield_label_html  = '<div class="arm-notched-outline">';
											$ffield_label_html .= '<div class="arm-notched-outline__leading"></div>';
											$ffield_label_html .= '<div class="arm-notched-outline__notch">';

											$ffield_label_html .= '<label class="arm-df__label-text active arm_material_label">' . esc_html($payment_plan_cycle_title) . '</label>';

											$ffield_label_html .= '</div>';
											$ffield_label_html .= '<div class="arm-notched-outline__trailing"></div>';
											$ffield_label_html .= '</div>';

											$ffield_label = $ffield_label_html;
										} else {
											$class_label = '';
											if ( $form_settings['style']['form_layout'] == 'writer' ) {
												$class_label = 'arm-df__label-text';

												$ffield_label = '<label class="' . esc_attr($class_label) . ' active">' . esc_html($payment_plan_cycle_title) . '</label>';
											}
										}
											$planSkinFloat = 'float:none;';
										switch ( $formPosition ) {
											case 'left':
												$planSkinFloat = '';
												break;
											case 'right':
												$planSkinFloat = 'float:right;';
												break;
										}
											$module_content     .= '<div class="arm-control-group arm-df__form-group arm-df__form-group_select">';
												$module_content .= '<div class="arm_label_input_separator"></div><div class="arm-df__form-field">';
											$module_content     .= '<div class="arm-df__form-field-wrap arm_container payment_plan_dropdown_skin1 ' . esc_attr($arm_field_wrap_active_class) . ' ' . esc_attr($dropdown_class) . '" style="' . esc_attr($planSkinFloat) . '">';
											$module_content     .= '<dl class="arm-df__dropdown-control column_level_dd">';

											// $module_content .= '<select name="subscription_plan" class="arm_module_plan_input select_skin"  aria-label="plan" onchange="armPlanChange(\'arm_setup_form' . $setupRandomID . '\')">';
											$i = 0;

										foreach ( $plans as $plan_id ) {
											if ( isset( $all_active_plans[ $plan_id ] ) ) {

												$plan_data = $all_active_plans[ $plan_id ];
												$planObj   = new ARM_Plan_Lite( 0 );
												$planObj->init( (object) $plan_data );
												$plan_type = $planObj->type;
												$planText  = $planObj->setup_plan_text();
												if ( $planObj->exists() ) {
													/* Checked Plan Radio According Settings. */
													$plan_checked = $plan_checked_class = '';
													if ( ! empty( $selected_plan_id ) && $selected_plan_id != 0 && in_array( $selected_plan_id, $plans ) ) {
														if ( $selected_plan_id == $plan_id ) {
															$plan_checked_class = 'arm_active';
															$plan_checked       = 'selected="selected"';
															$selected_plan_data = $plan_data;
														}
													} else {
														if ( $i == 0 ) {
															$plan_checked_class = 'arm_active';
															$plan_checked       = 'selected="selected"';
															$selected_plan_data = $plan_data;
														}
													}
													/* Check Recurring Details */
													$plan_options = $planObj->options;

													if ( is_user_logged_in() ) {
														if ( $arm_user_old_plan == $plan_id ) {
															$arm_user_payment_cycles = ( isset( $arm_user_old_plan_options['payment_cycles'] ) && ! empty( $arm_user_old_plan_options['payment_cycles'] ) ) ? $arm_user_old_plan_options['payment_cycles'] : array();
															if ( empty( $arm_user_payment_cycles ) ) {
																$plan_amount    = $planObj->amount;
																$recurring_time = isset( $arm_user_old_plan_options['recurring']['time'] ) ? $arm_user_old_plan_options['recurring']['time'] : 'infinite';
																$recurring_type = isset( $arm_user_old_plan_options['recurring']['type'] ) ? $arm_user_old_plan_options['recurring']['type'] : 'D';
																switch ( $recurring_type ) {
																	case 'D':
																		$billing_cycle = isset( $arm_user_old_plan_options['recurring']['days'] ) ? $arm_user_old_plan_options['recurring']['days'] : '1';
																		break;
																	case 'M':
																		$billing_cycle = isset( $arm_user_old_plan_options['recurring']['months'] ) ? $arm_user_old_plan_options['recurring']['months'] : '1';
																		break;
																	case 'Y':
																		$billing_cycle = isset( $arm_user_old_plan_options['recurring']['years'] ) ? $arm_user_old_plan_options['recurring']['years'] : '1';
																		break;
																	default:
																		$billing_cycle = '1';
																		break;
																}
																$payment_cycles                  = array(
																	array(
																		'cycle_label' => $planObj->plan_text( false, false ),
																		'cycle_amount' => $plan_amount,
																		'billing_cycle' => $billing_cycle,
																		'billing_type' => $recurring_type,
																		'recurring_time' => $recurring_time,
																		'payment_cycle_order' => 1,
																	),
																);
																$plan_payment_cycles[ $plan_id ] = $payment_cycles;
															} else {
																if ( ( $completed_recurrence == $total_recurring && $total_recurring != 'infinite' ) || ( $completed_recurrence == '' && $arm_user_selected_payment_mode == 'auto_debit_subscription' ) ) {
																	$arm_user_new_payment_cycles     = ( isset( $plan_options['payment_cycles'] ) && ! empty( $plan_options['payment_cycles'] ) ) ? $plan_options['payment_cycles'] : array();
																	$plan_payment_cycles[ $plan_id ] = $arm_user_new_payment_cycles;
																} else {
																	$plan_payment_cycles[ $plan_id ] = $arm_user_payment_cycles;
																}
															}
														} else {
															if ( $planObj->is_recurring() ) {
																if ( ! empty( $plan_options['payment_cycles'] ) ) {
																	$plan_payment_cycles[ $plan_id ] = $plan_options['payment_cycles'];
																} else {

																	$plan_amount    = $planObj->amount;
																	$recurring_time = isset( $plan_options['recurring']['time'] ) ? $plan_options['recurring']['time'] : 'infinite';
																	$recurring_type = isset( $plan_options['recurring']['type'] ) ? $plan_options['recurring']['type'] : 'D';
																	switch ( $recurring_type ) {
																		case 'D':
																			$billing_cycle = isset( $plan_options['recurring']['days'] ) ? $plan_options['recurring']['days'] : '1';
																			break;
																		case 'M':
																			$billing_cycle = isset( $plan_options['recurring']['months'] ) ? $plan_options['recurring']['months'] : '1';
																			break;
																		case 'Y':
																			$billing_cycle = isset( $plan_options['recurring']['years'] ) ? $plan_options['recurring']['years'] : '1';
																			break;
																		default:
																			$billing_cycle = '1';
																			break;
																	}
																	$payment_cycles                  = array(
																		array(
																			'cycle_label' => $planObj->plan_text( false, false ),
																			'cycle_amount' => $plan_amount,
																			'billing_cycle' => $billing_cycle,
																			'billing_type' => $recurring_type,
																			'recurring_time' => $recurring_time,
																			'payment_cycle_order' => 1,
																		),
																	);
																	$plan_payment_cycles[ $plan_id ] = $payment_cycles;
																}
															}
														}
													} else {
														if ( $planObj->is_recurring() ) {
															if ( ! empty( $plan_options['payment_cycles'] ) ) {
																$plan_payment_cycles[ $plan_id ] = $plan_options['payment_cycles'];
															} else {
																$plan_amount    = $planObj->amount;
																$recurring_time = isset( $plan_options['recurring']['time'] ) ? $plan_options['recurring']['time'] : 'infinite';
																$recurring_type = isset( $plan_options['recurring']['type'] ) ? $plan_options['recurring']['type'] : 'D';
																switch ( $recurring_type ) {
																	case 'D':
																		$billing_cycle = isset( $plan_options['recurring']['days'] ) ? $plan_options['recurring']['days'] : '1';
																		break;
																	case 'M':
																		$billing_cycle = isset( $plan_options['recurring']['months'] ) ? $plan_options['recurring']['months'] : '1';
																		break;
																	case 'Y':
																		$billing_cycle = isset( $plan_options['recurring']['years'] ) ? $plan_options['recurring']['years'] : '1';
																		break;
																	default:
																		$billing_cycle = '1';
																		break;
																}
																$payment_cycles                  = array(
																	array(
																		'cycle_label' => $planObj->plan_text( false, false ),
																		'cycle_amount' => $plan_amount,
																		'billing_cycle' => $billing_cycle,
																		'billing_type' => $recurring_type,
																		'recurring_time' => $recurring_time,
																		'payment_cycle_order' => 1,
																	),
																);
																$plan_payment_cycles[ $plan_id ] = $payment_cycles;
															}
														}
													}

													$payment_type = $planObj->payment_type;
													$is_trial     = '0';
													$trial_amount = $arm_payment_gateways->arm_amount_set_separator( $global_currency, 0 );
													if ( $planObj->is_recurring() ) {
														$stripePlans = ( isset( $modules['stripe_plans'] ) && ! empty( $modules['stripe_plans'] ) ) ? $modules['stripe_plans'] : array();

														if ( $planObj->has_trial_period() ) {
															$is_trial     = '1';
															$trial_amount = ! empty( $plan_options['trial']['amount'] ) ?
																	$arm_payment_gateways->arm_amount_set_separator( $global_currency, $plan_options['trial']['amount'] ) : $trial_amount;
															if ( is_user_logged_in() ) {
																if ( ! empty( $current_user_plan_ids ) ) {
																	if ( in_array( $planObj->ID, $current_user_plan_ids ) ) {
																		$is_trial = '0';
																	}
																}
															}
														}
													}

													$allowed_payment_gateways_['paypal'] = '1';

													$allowed_payment_gateways_['bank_transfer'] = '1';

													$allowed_payment_gateways_     = apply_filters( 'arm_allowed_payment_gateways', $allowed_payment_gateways_, $planObj, $plan_options );
													$data_allowed_payment_gateways = wp_json_encode( $allowed_payment_gateways_ );
													$arm_plan_amount               = $arm_payment_gateways->arm_amount_set_separator( $global_currency, $planObj->amount );
													$arm_plan_amount               = $planObj->amount = apply_filters( 'arm_modify_secondary_amount_outside', $arm_plan_amount, $plan_data );
													$planInputAttr                 = ' data-type="' . $plan_type . '" data-plan_name="' . esc_attr($planObj->name) . '" data-amt="' . $arm_plan_amount . '" data-recurring="' . $payment_type . '" data-is_trial="' . $is_trial . '" data-trial_amt="' . $trial_amount . '" data-allowed_gateways=\'' . $data_allowed_payment_gateways . '\' data-plan_text="' . htmlentities( $planText ) . '"';

													$count_total_cycle = 0;
													if ( $planObj->is_recurring() ) {

														$count_total_cycle = count( $plan_payment_cycles[ $plan_id ] );
														$planInputAttr    .= '  " data-cycle="' . esc_attr($count_total_cycle) . '" data-cycle_label="' . esc_attr($plan_payment_cycles[ $plan_id ][0]['cycle_label']) . '"';
													} else {
														$planInputAttr .= " data-cycle='0' data-cycle_label=''";
													}

													// $module_content .='<option value="' . $plan_id . '" class="armMDOption armSelectOption' . $setup_modules['modules']['forms'] . '" ' . $planInputAttr . ' ' . $plan_checked . '>' . $planObj->name . ' (' . $planObj->plan_price(false) . ')</option>';
													$plan_option_label = $planObj->name . '(' . wp_strip_all_tags( $planObj->plan_price( false ) ) . ')';
													if ( ! empty( $plan_checked ) ) {
														$plan_option_label_selected = $plan_option_label;
													}
													$module_content_options .= '<li class="arm__dc--item armMDOption armSelectOption' . esc_attr($setup_modules['modules']['forms']) . '" arm_plan_option_check_' . esc_attr($plan_id) . '" ' . $planInputAttr . ' data-label="' . esc_attr($plan_option_label) . '" data-value="' . esc_attr($plan_id) . '">' . $plan_option_label . '</li>';
													$i++;
												}
											}
										}
											$selected_plan_data_selected = isset( $selected_plan_data['arm_subscription_plan_id'] ) ? $selected_plan_data['arm_subscription_plan_id'] : 0;

											$module_content .= '<dt class="arm__dc--head">
                                                                    <span class="arm__dc--head__title">' . esc_html($plan_option_label_selected) . '</span><input type="text" style="display:none;" value="" class="arm-df__dc--head__autocomplete arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>';
											$module_content .= '</dt>';
											$module_content .= '<dd class="arm__dc--items-wrap">';
											$module_content .= '<ul class="arm__dc--items" data-id="subscription_plan_' . esc_attr($setupRandomID) . '" style="display:none;">';

											$module_content .= $module_content_options;
											$module_content .= '</ul>';
											$module_content .= '</dd>';
											$module_content .= '<input type="hidden" name="subscription_plan" id="subscription_plan_' . esc_attr($setupRandomID) . '" class="arm_module_plan_input select_skin"  aria-label="plan" onchange="armPlanChange(\'arm_setup_form' . esc_attr($setupRandomID) . '\')" value="' . esc_attr($selected_plan_data_selected) . '" />';
											$module_content .= '</dl>';
											$module_content .= $ffield_label;
											// $module_content .= '</select>';
											$module_content .= '</div></div></div>';
									} else {
										$plan_skin_align = '';
										$module_content .= '<ul class="arm_module_plans_ul arm_column_' . esc_attr($column_type) . '"' . $plan_skin_align . '>';
										$i               = 0;
										foreach ( $plans as $plan_id ) {
											if ( isset( $all_active_plans[ $plan_id ] ) ) {
												$plan_data = $all_active_plans[ $plan_id ];
												$planObj   = new ARM_Plan_Lite( 0 );
												$planObj->init( (object) $plan_data );
												$plan_type = $planObj->type;
												$planText  = $planObj->setup_plan_text();
												if ( $planObj->exists() ) {
													/* Checked Plan Radio According Settings. */
													$plan_checked = $plan_checked_class = '';
													if ( ! empty( $selected_plan_id ) && $selected_plan_id != 0 && in_array( $selected_plan_id, $plans ) ) {
														if ( $selected_plan_id == $plan_id ) {
															$plan_checked_class = 'arm_active';
															$plan_checked       = 'checked="checked"';
															$selected_plan_data = $plan_data;
														}
													} else {
														if ( $i == 0 ) {
															$plan_checked_class = 'arm_active';
															$plan_checked       = 'checked="checked"';
															$selected_plan_data = $plan_data;
														}
													}
													/* Check Recurring Details */
													$plan_options = $planObj->options;

													if ( is_user_logged_in() ) {
														if ( $arm_user_old_plan == $plan_id ) {
															$arm_user_payment_cycles = ( isset( $arm_user_old_plan_options['payment_cycles'] ) && ! empty( $arm_user_old_plan_options['payment_cycles'] ) ) ? $arm_user_old_plan_options['payment_cycles'] : array();
															if ( empty( $arm_user_payment_cycles ) ) {
																$plan_amount    = $planObj->amount;
																$recurring_time = isset( $arm_user_old_plan_options['recurring']['time'] ) ? $arm_user_old_plan_options['recurring']['time'] : 'infinite';
																$recurring_type = isset( $arm_user_old_plan_options['recurring']['type'] ) ? $arm_user_old_plan_options['recurring']['type'] : 'D';
																switch ( $recurring_type ) {
																	case 'D':
																		$billing_cycle = isset( $arm_user_old_plan_options['recurring']['days'] ) ? $arm_user_old_plan_options['recurring']['days'] : '1';
																		break;
																	case 'M':
																		$billing_cycle = isset( $arm_user_old_plan_options['recurring']['months'] ) ? $arm_user_old_plan_options['recurring']['months'] : '1';
																		break;
																	case 'Y':
																		$billing_cycle = isset( $arm_user_old_plan_options['recurring']['years'] ) ? $arm_user_old_plan_options['recurring']['years'] : '1';
																		break;
																	default:
																		$billing_cycle = '1';
																		break;
																}
																$payment_cycles                  = array(
																	array(
																		'cycle_label' => $planObj->plan_text( false, false ),
																		'cycle_amount' => $plan_amount,
																		'billing_cycle' => $billing_cycle,
																		'billing_type' => $recurring_type,
																		'recurring_time' => $recurring_time,
																		'payment_cycle_order' => 1,
																	),
																);
																$plan_payment_cycles[ $plan_id ] = $payment_cycles;
															} else {

																if ( ( $completed_recurrence == $total_recurring && $total_recurring != 'infinite' ) || ( $completed_recurrence == '' && $arm_user_selected_payment_mode == 'auto_debit_subscription' ) ) {

																	$arm_user_new_payment_cycles     = ( isset( $plan_options['payment_cycles'] ) && ! empty( $plan_options['payment_cycles'] ) ) ? $plan_options['payment_cycles'] : array();
																	$plan_payment_cycles[ $plan_id ] = $arm_user_new_payment_cycles;
																} else {
																	$plan_payment_cycles[ $plan_id ] = $arm_user_payment_cycles;
																}
															}
														} else {
															if ( $planObj->is_recurring() ) {
																if ( ! empty( $plan_options['payment_cycles'] ) ) {
																	$plan_payment_cycles[ $plan_id ] = $plan_options['payment_cycles'];
																} else {

																	$plan_amount    = $planObj->amount;
																	$recurring_time = isset( $plan_options['recurring']['time'] ) ? $plan_options['recurring']['time'] : 'infinite';
																	$recurring_type = isset( $plan_options['recurring']['type'] ) ? $plan_options['recurring']['type'] : 'D';
																	switch ( $recurring_type ) {
																		case 'D':
																			$billing_cycle = isset( $plan_options['recurring']['days'] ) ? $plan_options['recurring']['days'] : '1';
																			break;
																		case 'M':
																			$billing_cycle = isset( $plan_options['recurring']['months'] ) ? $plan_options['recurring']['months'] : '1';
																			break;
																		case 'Y':
																			$billing_cycle = isset( $plan_options['recurring']['years'] ) ? $plan_options['recurring']['years'] : '1';
																			break;
																		default:
																			$billing_cycle = '1';
																			break;
																	}
																	$payment_cycles                  = array(
																		array(
																			'cycle_label' => $planObj->plan_text( false, false ),
																			'cycle_amount' => $plan_amount,
																			'billing_cycle' => $billing_cycle,
																			'billing_type' => $recurring_type,
																			'recurring_time' => $recurring_time,
																			'payment_cycle_order' => 1,
																		),
																	);
																	$plan_payment_cycles[ $plan_id ] = $payment_cycles;
																}
															}
														}
													} else {
														if ( $planObj->is_recurring() ) {
															if ( ! empty( $plan_options['payment_cycles'] ) ) {
																$plan_payment_cycles[ $plan_id ] = $plan_options['payment_cycles'];
															} else {
																$plan_amount    = $planObj->amount;
																$recurring_time = isset( $plan_options['recurring']['time'] ) ? $plan_options['recurring']['time'] : 'infinite';
																$recurring_type = isset( $plan_options['recurring']['type'] ) ? $plan_options['recurring']['type'] : 'D';
																switch ( $recurring_type ) {
																	case 'D':
																		$billing_cycle = isset( $plan_options['recurring']['days'] ) ? $plan_options['recurring']['days'] : '1';
																		break;
																	case 'M':
																		$billing_cycle = isset( $plan_options['recurring']['months'] ) ? $plan_options['recurring']['months'] : '1';
																		break;
																	case 'Y':
																		$billing_cycle = isset( $plan_options['recurring']['years'] ) ? $plan_options['recurring']['years'] : '1';
																		break;
																	default:
																		$billing_cycle = '1';
																		break;
																}
																$payment_cycles                  = array(
																	array(
																		'cycle_label' => $planObj->plan_text( false, false ),
																		'cycle_amount' => $plan_amount,
																		'billing_cycle' => $billing_cycle,
																		'billing_type' => $recurring_type,
																		'recurring_time' => $recurring_time,
																		'payment_cycle_order' => 1,
																	),
																);
																$plan_payment_cycles[ $plan_id ] = $payment_cycles;
															}
														}
													}

													$payment_type = $planObj->payment_type;

													$is_trial     = '0';
													$trial_amount = $arm_payment_gateways->arm_amount_set_separator( $global_currency, 0 );

													if ( $planObj->is_recurring() ) {
														$stripePlans = ( isset( $modules['stripe_plans'] ) && ! empty( $modules['stripe_plans'] ) ) ? $modules['stripe_plans'] : array();

														if ( $planObj->has_trial_period() ) {
															$is_trial     = '1';
															$trial_amount = ! empty( $plan_options['trial']['amount'] ) ? $arm_payment_gateways->arm_amount_set_separator( $global_currency, $plan_options['trial']['amount'] ) : $trial_amount;
															if ( is_user_logged_in() ) {
																if ( ! empty( $current_user_plan_ids ) ) {
																	if ( in_array( $planObj->ID, $current_user_plan_ids ) ) {
																		$is_trial = '0';
																	}
																}
															}
														}
													}

													$allowed_payment_gateways_['paypal']        = '1';
													$allowed_payment_gateways_['stripe']        = '1';
													$allowed_payment_gateways_['bank_transfer'] = '1';
													$allowed_payment_gateways_['2checkout']     = '1';
													$allowed_payment_gateways_['authorize_net'] = '1';
													$allowed_payment_gateways_                  = apply_filters( 'arm_allowed_payment_gateways', $allowed_payment_gateways_, $planObj, $plan_options );
													$data_allowed_payment_gateways              = wp_json_encode( $allowed_payment_gateways_ );
													$arm_plan_amount                            = $arm_payment_gateways->arm_amount_set_separator( $global_currency, $planObj->amount );
													$arm_plan_amount                            = $planObj->amount = apply_filters( 'arm_modify_secondary_amount_outside', $arm_plan_amount, $plan_data );
													$planInputAttr                              = ' data-type="' . esc_attr($plan_type) . '" data-plan_name="' . esc_attr($planObj->name) . '" data-amt="' . esc_attr($arm_plan_amount) . '" data-recurring="' . esc_attr($payment_type) . '" data-is_trial="' . esc_attr($is_trial) . '" data-trial_amt="' . esc_attr($trial_amount) . '"  data-allowed_gateways=\'' . esc_attr($data_allowed_payment_gateways) . '\' data-plan_text="' . htmlentities( $planText ) . '"';

													$count_total_cycle = 0;
													if ( $planObj->is_recurring() ) {

														$count_total_cycle = count( $plan_payment_cycles[ $plan_id ] );
														$planInputAttr    .= '  " data-cycle="' . esc_attr($count_total_cycle) . '"';
													} else {
														$planInputAttr .= " data-cycle='0'";
													}

													if ( $setup_style['plan_skin'] == '' ) {
														$module_content .= '<li class="arm_plan_default_skin arm_setup_column_item ' . esc_attr($plan_checked_class) . '">';
														$module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . esc_attr($plan_id) . '">';
														$module_content .= '<span class="arm_setup_check_circle"><i class="armfa armfa-check"></i></span>';
														$module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . esc_attr($plan_id) . '" class="arm_module_plan_input" value="' . esc_attr($plan_id) . '" ' . esc_attr($planInputAttr) . ' ' . $plan_checked . ' required>';
														$module_content .= '<span class="arm_module_plan_name">' . esc_html($planObj->name) . '</span>';
														$module_content .= '<div class="arm_module_plan_price_type"><span class="arm_module_plan_price">' . $planObj->plan_price( false ) . '</span></div>';
														$module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';
														/* $module_content .= $setup_info; */
														$module_content .= '</label>';
														$module_content .= '</li>';
													} elseif ( $setup_style['plan_skin'] == 'skin1' ) {
														$module_content .= '<li class="arm_plan_skin1 arm_setup_column_item ' . esc_attr($plan_checked_class) . '">';
														$module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . esc_attr($plan_id) . '">';

														$module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . esc_attr($plan_id) . '" class="arm_module_plan_input" value="' . esc_attr($plan_id) . '" ' . $planInputAttr . ' ' . $plan_checked . ' required>';
														$module_content .= '<span class="arm_module_plan_name">' . esc_html($planObj->name) . '</span>';
														$module_content .= '<div class="arm_module_plan_price_type"><span class="arm_module_plan_price">' . $planObj->plan_price( false ). '</span></div>';
														$module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';//phpcs:ignore
														/* $module_content .= $setup_info; */
														$module_content .= '</label>';
														$module_content .= '</li>';
													} elseif ( $setup_style['plan_skin'] == 'skin3' ) {
														$module_content .= '<li class="arm_plan_skin3 arm_setup_column_item ' . esc_attr($plan_checked_class) . '">';
														$module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . esc_attr($plan_id) . '">';
														$module_content .= '<div class="arm_plan_name_box"><span class="arm_setup_check_circle"><i class="armfa armfa-check"></i></span>';
														$module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . esc_attr($plan_id) . '" class="arm_module_plan_input" value="' . esc_attr($plan_id) . '" ' . $planInputAttr . ' ' . $plan_checked . ' required>';
														$module_content .= '<span class="arm_module_plan_name">' . esc_html($planObj->name) . '</span></div>';
														$module_content .= '<div class="arm_module_plan_price_type"><span class="arm_module_plan_price">' . $planObj->plan_price( false ). '</span></div>';
														$module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';
														/* $module_content .= $setup_info; */
														$module_content .= '</label>';
														$module_content .= '</li>';
													} else {
														$module_content .= '<li class="arm_plan_skin2 arm_setup_column_item ' . esc_attr($plan_checked_class) . '">';
														$module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . esc_attr($plan_id) . '">';

														$module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . esc_attr($plan_id) . '" class="arm_module_plan_input" value="' . esc_attr($plan_id) . '" ' . $planInputAttr . ' ' . $plan_checked . ' required>';
														$module_content .= '<span class="arm_module_plan_name">' . esc_html($planObj->name) . '</span>';
														$module_content .= '<div class="arm_module_plan_price_type"><span class="arm_module_plan_price">' . $planObj->plan_price( false ). '</span></div>';
														$module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';
														/* $module_content .= $setup_info; */
														$module_content .= '</label>';
														$module_content .= '</li>';
													}
													$i++;
												}
											}
										}
										$module_content .= '</ul>';
									}
									$module_content .= '</div>';
									$module_content  = apply_filters( 'arm_after_setup_plan_section', $module_content, $setupID, $setup_data );
									$module_content .= '<div class="armclear"></div>';
									$module_content .= '<input type="hidden" data-id="arm_form_plan_type" name="arm_plan_type" value="' . esc_attr( ( ! empty( $selected_plan_data['arm_subscription_plan_type'] ) && $selected_plan_data['arm_subscription_plan_type'] == 'free' ) ? 'free' : 'paid' ) . '">';
								}

								break;
							case 'forms':
								if ( ! empty( $modules['forms'] ) && $modules['forms'] != 0 ) {
									$form_id = $modules['forms'];
									if ( ! empty( $form_settings ) ) {
										$form_style_class  = 'arm_form_' . $modules['forms'];
										$form_style_class .= ' arm_form_layout_' . $form_settings['style']['form_layout'] . ' arm-default-form';

										if ( $form_settings['style']['form_layout'] == 'writer' ) {
											$form_style_class .= ' arm-material-style arm_materialize_form ';
										} elseif ( $form_settings['style']['form_layout'] == 'rounded' ) {
											$form_style_class .= ' arm-rounded-style ';
										} elseif ( $form_settings['style']['form_layout'] == 'writer_border' ) {
											$form_style_class .= ' arm--material-outline-style arm_materialize_form ';
										}

										$form_style_class .= ( $form_settings['style']['label_hide'] == '1' ) ? ' armf_label_placeholder' : '';
										$form_style_class .= ' armf_alignment_' . $form_settings['style']['label_align'];
										$form_style_class .= ' armf_layout_' . $form_settings['style']['label_position'];
										$form_style_class .= ' armf_button_position_' . $form_settings['style']['button_position'];
										$form_style_class .= ( $form_settings['style']['rtl'] == '1' ) ? ' arm_form_rtl' : ' arm_form_ltr';
										$errPosCCField     = ( ! empty( $form_settings['style']['validation_position'] ) ) ? $form_settings['style']['validation_position'] : 'bottom';
										$buttonStyle       = ( isset( $form_settings['style']['button_style'] ) && ! empty( $form_settings['style']['button_style'] ) ) ? $form_settings['style']['button_style'] : 'flat';
										$btn_style_class   = ' --arm-is-' . $buttonStyle . '-style';

										$fieldPosition = ! empty( $form_settings['style']['field_position'] ) ? $form_settings['style']['field_position'] : 'left';
									}
									if ( is_user_logged_in() && ! $isPreview ) {

										$form             = new ARM_Form_Lite( 'id', $modules['forms'] );
										$ref_template     = $form->form_detail['arm_ref_template'];
										$form_css         = $arm_member_forms->arm_ajax_generate_form_styles( $modules['forms'], $form_settings, array(), $ref_template );
										$formStyle       .= $form_css['arm_css'];
										$modules['forms'] = 0;
									} else {
										$formAttr = '';
										if ( $isPreview ) {
											$formAttr = 'preview="true"';
										}
										$module_content .= '<div class="arm_module_forms_container arm_module_box">';
										$module_content .= do_shortcode( '[arm_form id="' . $modules['forms'] . '" setup="true" form_position="' . $formPosition . '" ' . $formAttr . ']' );
										$module_content .= '</div>';
										$module_content  = apply_filters( 'arm_after_setup_reg_form_section', $module_content, $setupID, $setup_data );
										$module_content .= '<div class="armclear"></div>';
									}
								} else {
									if ( ! $isPreview ) {
										/* Hide Setup Form for non-logged in users when there is no form configured */
										return '';
									}
								}
								break;
							case 'note':
								if ( isset( $setup_modules['note'] ) && ! empty( $setup_modules['note'] ) ) {
									$module_content .= '<div class="arm_module_note_container arm_module_box">';
									$module_content .= apply_filters( 'the_content', stripslashes( $setup_modules['note'] ) );
									$module_content .= '</div>';
								}
								break;

							case 'payment_cycle':
								$form_layout = '';
								if ( ! empty( $form_settings ) ) {
									$form_layout .= ' arm_form_layout_' . $form_settings['style']['form_layout'] . ' arm-default-form';
									if ( $form_settings['style']['form_layout'] == 'writer' ) {
										$form_layout .= ' arm-material-style arm_materialize_form ';
									} elseif ( $form_settings['style']['form_layout'] == 'rounded' ) {
										$form_layout .= ' arm-rounded-style ';
									} elseif ( $form_settings['style']['form_layout'] == 'writer_border' ) {
										$form_layout .= ' arm--material-outline-style arm_materialize_form ';
									}
									if ( ! empty( $form_settings['style']['validation_type'] ) && $form_settings['style']['validation_type'] == 'standard' ) {
										$form_layout .= ' arm_standard_validation_type ';
									}
								}
								$payment_mode = 'both';

								$module_content .= '<div class="arm_setup_paymentcyclebox_wrapper arm_hide">';

								if ( ! empty( $plan_payment_cycles ) ) {

									foreach ( $plan_payment_cycles as $payment_cycle_plan_id => $plan_payment_cycle_data ) {

										$arm_user_selected_payment_cycle = 0;
										if ( ! empty( $current_plan_data ) ) {
											$arm_user_selected_payment_cycle = $current_plan_data['arm_payment_cycle'];
										}
										$plan_cycle_field_title = ( isset( $setup_data['setup_labels']['payment_cycle_field_title'] ) ) ? $setup_data['setup_labels']['payment_cycle_field_title'] : esc_html__( 'Select Your payment Cycle', 'armember-membership' );
										if ( ! empty( $plan_payment_cycle_data ) ) {
											$module_content .= '<div class="arm_module_payment_cycle_container arm_module_box arm_payment_cycle_box_' . esc_attr($payment_cycle_plan_id) . ' arm_form_' . esc_attr($setup_modules['modules']['forms']) . ' ' . esc_attr($form_layout) . ' arm_hide">';
											if ( isset( $setup_data['setup_labels']['payment_cycle_section_title'] ) && ! empty( $setup_data['setup_labels']['payment_cycle_section_title'] ) ) {
												$module_content .= '<div class="arm_setup_section_title_wrapper arm_setup_payment_cycle_title_wrapper arm_hide" style="text-align:' . $formPosition . ';">' . esc_html(stripslashes_deep( $setup_data['setup_labels']['payment_cycle_section_title']) ) . '</div>';
											} else {
												$module_content .= '<div class="arm_setup_section_title_wrapper arm_setup_payment_cycle_title_wrapper arm_hide" style="text-align:' . $formPosition . ';">' . esc_html__( 'Select Payment Cycle', 'armember-membership' ) . '</div>';
											}
											$column_type = ( ! empty( $setup_modules['cycle_columns'] ) ) ? $setup_modules['cycle_columns'] : '1';

											if ( is_array( $plan_payment_cycle_data ) ) {
												if ( count( $plan_payment_cycle_data ) <= $arm_user_selected_payment_cycle ) {
													$arm_user_selected_payment_cycle_no = 0;
												} else {
													$arm_user_selected_payment_cycle_no = $arm_user_selected_payment_cycle;
												}

												$module_content .= '<input type="hidden" name="arm_payment_cycle_plan_' . esc_attr($payment_cycle_plan_id) . '" data-id="arm_payment_cycle_plan_' . esc_attr($payment_cycle_plan_id) . '" value="' . esc_attr($arm_user_selected_payment_cycle_no) . '" >';
											}

											if ( $setup_style['plan_skin'] == 'skin5' ) {
												if ( is_array( $plan_payment_cycle_data ) ) {
													$dropdown_class            = 'arm-df__form-field-wrap_plan_cycles';
													$arm_allow_notched_outline = 0;
													if ( $form_settings['style']['form_layout'] == 'writer_border' ) {
														$arm_allow_notched_outline = 1;
														$inputPlaceholder          = '';
													}

													$arm_field_wrap_active_class = $ffield_label_html = $ffield_label = '';
													if ( ! empty( $arm_allow_notched_outline ) ) {
														$arm_field_wrap_active_class = ' arm-df__form-material-field-wrap';

														$ffield_label_html  = '<div class="arm-notched-outline">';
														$ffield_label_html .= '<div class="arm-notched-outline__leading"></div>';
														$ffield_label_html .= '<div class="arm-notched-outline__notch">';

														$ffield_label_html .= '<label class="arm-df__label-text active arm_material_label">' . esc_attr($plan_cycle_field_title) . '</label>';

														$ffield_label_html .= '</div>';
														$ffield_label_html .= '<div class="arm-notched-outline__trailing"></div>';
														$ffield_label_html .= '</div>';

														$ffield_label = $ffield_label_html;
													} else {
														$class_label = '';
														if ( $form_settings['style']['form_layout'] == 'writer' ) {
															$class_label = 'arm-df__label-text';

															$ffield_label = '<label class="' . esc_attr($class_label) . ' active">' . esc_html($plan_cycle_field_title) . '</label>';
														}
													}

													$paymentSkinFloat = 'float:none;';
													switch ( $formPosition ) {
														case 'left':
															$paymentSkinFloat = '';
															break;
														case 'right':
															$paymentSkinFloat = 'float:right;';
															break;
													}
													$module_content .= '<div class="arm-control-group arm-df__form-group arm-df__form-group_plan_cycles">';
													$module_content .= '<div class="arm_label_input_separator"></div><div class="arm-df__form-field">';

													$module_content .= '<div class="arm-df__form-field-wrap ' . $dropdown_class . ' payment_cycle_dropdown_skin1 ' . esc_attr($arm_field_wrap_active_class) . ' " style="' . $paymentSkinFloat . '">';
													$module_content .= '<dl class="arm-df__dropdown-control column_level_dd">';

													// $module_content .= '<select name="payment_cycle_' . $payment_cycle_plan_id . '" class="arm_module_cycle_input select_skin" onchange="armPaymentCycleChange('.$payment_cycle_plan_id.', \'arm_setup_form' . $setupRandomID . '\')">';

													$i = 0;

													$module_content_options = $pc_checked_label = $pc_checked_cycle_val = '';
													foreach ( $plan_payment_cycle_data as $arm_cycle_data_key => $arm_cycle_data ) {

														$pc_checked = $pc_checked_class = '';

														$arm_paymentg_cycle_label = ( isset( $arm_cycle_data['cycle_label'] ) ) ? $arm_cycle_data['cycle_label'] : '';

														if ( $i == $arm_user_selected_payment_cycle_no ) {
															$pc_checked           = 'selected="selected""';
															$pc_checked_class     = 'arm_active';
															$pc_checked_label     = $arm_paymentg_cycle_label;
															$pc_checked_cycle_val = $arm_user_selected_payment_cycle_no;
														}

														$arm_paymentg_cycle_amount = ( isset( $arm_cycle_data['cycle_amount'] ) ) ? $arm_payment_gateways->arm_amount_set_separator( $global_currency, $arm_cycle_data['cycle_amount'] ) : 0;
														$arm_paymentg_cycle_amount = apply_filters( 'arm_modify_secondary_amount_outside', $arm_paymentg_cycle_amount, $arm_cycle_data );

														// $module_content .='<option value="' . $arm_cycle_data_key  . '" class="armMDOption armSelectOption' . $setup_modules['modules']['forms'] . '" ' . $pc_checked . '  data-cycle_type="recurring" data-plan_id="' . $payment_cycle_plan_id . '" data-plan_amount = "' . $arm_paymentg_cycle_amount . '" >' . $arm_paymentg_cycle_label . '</option>';

														$module_content_options .= '<li class="arm__dc--item armMDOption armSelectOption' . esc_attr($setup_modules['modules']['forms']) . '" data-label="' . esc_attr($arm_paymentg_cycle_label) . '" data-value="' . esc_attr($arm_cycle_data_key) . '" data-cycle_type="recurring" data-plan_id="' . esc_attr($payment_cycle_plan_id) . '" data-plan_amount = "' . esc_attr($arm_paymentg_cycle_amount) . '">' . esc_html($arm_paymentg_cycle_label) . '</li>';

														$i++;
													}
													$module_content     .= '<dt class="arm__dc--head">
                                                                            <span class="arm__dc--head__title">' . $pc_checked_label . '</span><input type="text" style="display:none;" value="" class="arm-df__dc--head__autocomplete arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>';
													$module_content     .= '</dt>';
													$module_content     .= '<dd class="arm__dc--items-wrap">';
														$module_content .= '<ul class="arm__dc--items" data-id="arm_payment_cycle_' . esc_attr($payment_cycle_plan_id) . '_' . esc_attr($setupRandomID) . '" style="display:none;">';

													$module_content .= $module_content_options;
													$module_content .= '</ul>';
													$module_content .= '</dd>';
													$module_content .= '</dl>';
													$module_content .= '<input type="hidden" id="arm_payment_cycle_' . $payment_cycle_plan_id . '_' .esc_attr( $setupRandomID) . '" name="payment_cycle_' . esc_attr($payment_cycle_plan_id) . '" class="arm_module_cycle_input select_skin" onchange="armPaymentCycleChange(' . esc_attr($payment_cycle_plan_id) . ', \'arm_setup_form' . esc_attr($setupRandomID) . '\')" value="' . esc_attr($pc_checked_cycle_val) . '" />';

													// $module_content .= '</select>';
													$module_content .= '</div></div></div>';

												}
											} else {
												$module_content .= '<ul class="arm_module_payment_cycle_ul arm_column_' . esc_attr($column_type) . '" style="text-align:' . $formPosition . ';">';
												$i               = 0;

												if ( is_array( $plan_payment_cycle_data ) ) {
													foreach ( $plan_payment_cycle_data as $arm_cycle_data_key => $arm_cycle_data ) {

														$pc_checked = $pc_checked_class = '';
														if ( $i == $arm_user_selected_payment_cycle_no ) {
															$pc_checked       = 'checked="checked"';
															$pc_checked_class = 'arm_active';
														}

														$arm_paymentg_cycle_amount = ( isset( $arm_cycle_data['cycle_amount'] ) ) ? $arm_payment_gateways->arm_amount_set_separator( $global_currency, $arm_cycle_data['cycle_amount'] ) : 0;
														$arm_paymentg_cycle_amount = apply_filters( 'arm_modify_secondary_amount_outside', $arm_paymentg_cycle_amount, $arm_cycle_data );

														$arm_paymentg_cycle_label = ( isset( $arm_cycle_data['cycle_label'] ) ) ? stripslashes($arm_cycle_data['cycle_label']) : '';

														$pc_content  = '<label class="arm_module_payment_cycle_option">';
														$pc_content .= '<span class="arm_setup_check_circle"><i class="armfa armfa-check"></i></span>';
														$pc_content .= '<input type="radio" name="payment_cycle_' . esc_attr($payment_cycle_plan_id) . '" class="arm_module_cycle_input" value="' . esc_attr( $arm_cycle_data_key ) . '" ' . $pc_checked . '  data-cycle_type="recurring" data-plan_id="' . esc_attr($payment_cycle_plan_id) . '" data-plan_amount = "' . esc_attr($arm_paymentg_cycle_amount) . '">';
														$pc_content .= '<div class="arm_module_payment_cycle_name"><span class="arm_module_payment_cycle_span">' . esc_html($arm_paymentg_cycle_label) . '</span></div>';
														$pc_content .= '</label>';

														$module_content .= '<li class="arm_setup_column_item arm_payment_cycle_' . esc_attr( $arm_cycle_data_key ) . ' ' . esc_attr($pc_checked_class) . '"  data-plan_id="' . esc_attr($payment_cycle_plan_id) . '">';
														$module_content .= $pc_content;
														$module_content .= '</li>';
														$i++;
													}
												}
												$module_content .= '</ul>';
											}
											$module_content .= '</div>';
										}
									}
								}
								$module_content  = apply_filters( 'arm_after_setup_payment_cycle_section', $module_content, $setupID, $setup_data );
								$module_content .= '</div>';
								break;
							case 'gateways':
								$form_layout = '';

								if ( ! empty( $form_settings ) ) {

										$form_layout = ' arm_form_layout_' . $form_settings['style']['form_layout'] . ' arm-default-form';
									if ( $form_settings['style']['form_layout'] == 'writer' ) {
										$form_layout .= ' arm-material-style arm_materialize_form ';
									} elseif ( $form_settings['style']['form_layout'] == 'rounded' ) {
										$form_layout .= ' arm-rounded-style ';
									} elseif ( $form_settings['style']['form_layout'] == 'writer_border' ) {
										$form_layout .= ' arm--material-outline-style arm_materialize_form ';
									}
									if ( ! empty( $form_settings['style']['validation_type'] ) && $form_settings['style']['validation_type'] == 'standard' ) {
										$form_layout .= ' arm_standard_validation_type ';
									}
								}
									$payment_mode = 'both';
								if ( ! empty( $modules['gateways'] ) ) {
									$payment_gateway_skin = ( isset( $setup_style['gateway_skin'] ) && $setup_style['gateway_skin'] != '' ) ? $setup_style['gateway_skin'] : 'radio';
									$gatewayOrders        = array();
									$gatewayOrders        = ( isset( $modules['gateways_order'] ) && ! empty( $modules['gateways_order'] ) ) ? $modules['gateways_order'] : array();
									if ( ! empty( $gatewayOrders ) ) {
										asort( $gatewayOrders );
									}
									$form_position = ( ! empty( $setup_style['form_position'] ) ) ? $setup_style['form_position'] : 'left';

									$payment_gateway_title = ( isset( $setup_data['setup_labels']['payment_gateway_field_title'] ) && ! empty( $setup_data['setup_labels']['payment_gateway_field_title'] ) ) ? $setup_data['setup_labels']['payment_gateway_field_title'] : esc_html__( 'Select Your Payment Gateway', 'armember-membership' );

									$gateways = $this->armSortModuleOrders( $modules['gateways'], $gatewayOrders );
									if ( ! empty( $gateways ) ) {
										$active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
										$is_display_pg   = ( ! empty( $selected_plan_data['arm_subscription_plan_type'] ) && $selected_plan_data['arm_subscription_plan_type'] == 'free' ) ? 'display:none;' : '';
										$module_content .= '<div class="arm_setup_gatewaybox_main_wrapper"><div class="arm_setup_gatewaybox_wrapper" style="' . $is_display_pg . '">';
										if ( isset( $setup_data['setup_labels']['payment_section_title'] ) && ! empty( $setup_data['setup_labels']['payment_section_title'] ) ) {
											$module_content .= '<div class="arm_setup_section_title_wrapper" style="text-align:' . $formPosition . ';">' . stripslashes_deep( $setup_data['setup_labels']['payment_section_title'] ) . '</div>';
										}
										$module_content .= '<input type="hidden" name="arm_front_gateway_skin_type" data-id="arm_front_gateway_skin_type" value="' . esc_attr($payment_gateway_skin) . '">';
										$module_content .= '<div class="arm_module_gateways_container arm_module_box arm_form_' . esc_attr($setup_modules['modules']['forms']) . ' ' . esc_attr($form_layout) . '">';

										$column_type = ( ! empty( $setup_modules['gateways_columns'] ) ) ? $setup_modules['gateways_columns'] : '1';

										$doNotDisplayPaymentMode = array( 'bank_transfer' );
										$doNotDisplayPaymentMode = apply_filters( 'arm_not_display_payment_mode_setup', $doNotDisplayPaymentMode );

										$pglabels = isset( $setup_data['arm_setup_labels']['payment_gateway_labels'] ) ? $setup_data['arm_setup_labels']['payment_gateway_labels'] : array();

										if ( $payment_gateway_skin == 'radio' ) {

											$module_content .= '<ul class="arm_module_gateways_ul arm_column_' . $column_type . '" style="text-align:' . $formPosition . ';">';
											$i               = 0;
											$pg_fields       = $selectedKey = '';

											foreach ( $gateways as $pg ) {
												if ( in_array( $pg, array_keys( $active_gateways ) ) ) {
													if ( isset( $selected_plan_data['arm_subscription_plan_options']['trial']['is_trial_period'] ) && $pg == 'stripe' && $selected_plan_data['arm_subscription_plan_options']['payment_type'] == 'subscription' ) {
														if ( $selected_plan_data['arm_subscription_plan_options']['trial']['amount'] > 0 ) {
															// continue;
														}
													}
													if ( ! in_array( $pg, $doNotDisplayPaymentMode ) ) {
														$payment_mode = $modules['payment_mode'][ $pg ];
													} else {
														$payment_mode = 'manual_subscription';
													}

													$pg_options    = $active_gateways[ $pg ];
													$pg_checked    = $pg_checked_class = '';
													$display_block = 'arm_hide';
													if ( $i == 0 ) {
														$pg_checked       = 'checked="checked"';
														$pg_checked_class = 'arm_active';
														$display_block    = '';
														$selectedKey      = $pg;
													}
													$pg_content      = '<label class="arm_module_gateway_option">';
													$pg_content     .= '<span class="arm_setup_check_circle"><i class="armfa armfa-check"></i></span>';
														$pg_content .= '<input type="radio" name="payment_gateway" class="arm_module_gateway_input" value="' . esc_attr($pg) . '" ' . $pg_checked . ' data-payment_mode="' . esc_attr($payment_mode) . '" >';
													if ( ! empty( $pglabels ) ) {
														if ( isset( $pglabels[ $pg ] ) ) {
															$pg_options['gateway_name'] = $pglabels[ $pg ];
														}
													}
														$pg_content .= '<div class="arm_module_gateway_name"><span class="arm_module_gateway_span">' . stripslashes_deep( esc_html($pg_options['gateway_name']) ) . '</span></div>';
													$pg_content     .= '</label>';
													switch ( $pg ) {
														case 'paypal':
															break;
														case 'bank_transfer':
															if ( isset( $pg_options['note'] ) && ! empty( $pg_options['note'] ) ) {
																$pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_bank_transfer ' . esc_attr($display_block) . ' arm-form-container"><div class="arm_bank_transfer_note_container">' . stripslashes( nl2br( $pg_options['note'] ) ) . '</div></div>';
															}
															$bt_fields = isset( $pg_options['fields'] ) ? $pg_options['fields'] : array();
															if ( isset( $bt_fields['transaction_id'] ) || isset( $bt_fields['bank_name'] ) || isset( $bt_fields['account_name'] ) || isset( $bt_fields['additional_info'] ) || isset( $bt_fields['transfer_mode'] ) ) {
																$pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_bank_transfer ' . esc_attr($display_block) . ' arm-form-container">';
																	$pg_fields .= '<div class="' . esc_attr($form_style_class) . '">';
																	$pg_fields .= $arm_payment_gateways->arm_get_bank_transfer_form( $pg_options, $fieldPosition, $errPosCCField, $setup_modules['modules']['forms'], $form_settings );
																	$pg_fields .= '</div>';
																$pg_fields .= '</div>';
															}
															break;
														default:
															$gateway_fields = apply_filters( 'arm_membership_setup_gateway_option', '', $pg, $pg_options );
															
															if ( ! empty( $gateway_fields ) ) {
																$pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_' . esc_attr($pg) . ' ' . $display_block . ' arm-form-container">';
																$pg_fields .= '<div class="' . esc_attr($form_style_class) . '">';
																$pg_fields .= $gateway_fields;
																$pg_fields .= '</div>';
																$pg_fields .= '</div>';
															}
															break;
													}
													$module_content .= '<li class="arm_setup_column_item arm_gateway_' . esc_attr($pg) . ' ' . $pg_checked_class . '">';
													$module_content .= $pg_content;
													$module_content .= '</li>';
													$i++;
													$module_content .= "<input type='hidden' name='arm_payment_mode[". esc_attr($pg)."]' value='".esc_attr($payment_mode)."' />";
												}
											}
											$module_content .= '</ul>';
										} else {
												$arm_allow_notched_outline = 0;
											if ( $form_settings['style']['form_layout'] == 'writer_border' ) {
												$arm_allow_notched_outline = 1;
												$inputPlaceholder          = '';
											}

													$arm_field_wrap_active_class = $ffield_label_html = $ffield_label = '';
											if ( ! empty( $arm_allow_notched_outline ) ) {
												$arm_field_wrap_active_class = ' arm-df__form-material-field-wrap';

												$ffield_label_html  = '<div class="arm-notched-outline">';
												$ffield_label_html .= '<div class="arm-notched-outline__leading"></div>';
												$ffield_label_html .= '<div class="arm-notched-outline__notch">';

												$ffield_label_html .= '<label class="arm-df__label-text active arm_material_label">' . esc_html($payment_gateway_title) . '</label>';

												$ffield_label_html .= '</div>';
												$ffield_label_html .= '<div class="arm-notched-outline__trailing"></div>';
												$ffield_label_html .= '</div>';

												$ffield_label = $ffield_label_html;
											} else {
												$class_label = '';
												if ( $form_settings['style']['form_layout'] == 'writer' ) {
													$class_label = 'arm-df__label-text';

													$ffield_label = '<label class="' . esc_attr($class_label) . ' active">' . esc_html($payment_gateway_title) . '</label>';
												}
											}
											$paymentSkinFloat = 'float:none;';
											switch ( $formPosition ) {
												case 'left':
													$paymentSkinFloat = '';
													break;
												case 'right':
													$paymentSkinFloat = 'float:right;';
													break;
											}
												$module_content         .= '<div class="arm-control-group arm-df__form-group arm-df__form-group_select">';
														$module_content .= '<div class="arm_label_input_separator"></div><div class="arm-df__form-field">';
												$module_content         .= '<div class="arm-df__form-field-wrap arm-controls arm_container payment_gateway_dropdown_skin1 ' . esc_attr($arm_field_wrap_active_class) . '" style="' . $paymentSkinFloat . '">';

												$module_content .= '<dl class="arm-df__dropdown-control column_level_dd">';

												// $module_content .= '<select name="payment_gateway" class="arm_module_gateway_input select_skin"  aria-label="gateway" onchange="armPaymentGatewayChange(\'arm_setup_form' . $setupRandomID . '\')">';
												$i                      = 0;
												$module_content_options = $pg_fields = $selectedKey = $pg_options_gateway_name = '';
											foreach ( $gateways as $pg ) {
												if ( in_array( $pg, array_keys( $active_gateways ) ) ) {
													$payment_gateway_name = $pg;
													if ( isset( $selected_plan_data['arm_subscription_plan_options']['trial']['is_trial_period'] ) && $pg == 'stripe' && $selected_plan_data['arm_subscription_plan_options']['payment_type'] == 'subscription' ) {
														if ( $selected_plan_data['arm_subscription_plan_options']['trial']['amount'] > 0 ) {
															// continue;
														}
													}

													if ( ! in_array( $pg, $doNotDisplayPaymentMode ) ) {
														$payment_mode = $modules['payment_mode'][ $pg ];
													} else {
														$payment_mode = 'manual_subscription';
													}

													$pg_options    = $active_gateways[ $pg ];
													$pg_checked    = $pg_checked_class = '';
													$display_block = 'arm_hide';
													if ( $i == 0 ) {
														$pg_checked                  = 'selected="selected"';
														$pg_checked_class            = 'arm_active';
														$display_block               = '';
														$selectedKey                 = $pg;
															$pg_options_gateway_name = stripslashes_deep( $pglabels[ $pg ] );
													}

													switch ( $pg ) {
														case 'paypal':
															break;
														case 'bank_transfer':
															if ( isset( $pg_options['note'] ) && ! empty( $pg_options['note'] ) ) {
																$pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_bank_transfer ' . esc_attr($display_block) . ' arm-form-container"><div class="arm_bank_transfer_note_container">' . stripslashes( nl2br( $pg_options['note'] ) ) . '</div></div>';
															}
															$bt_fields = isset( $pg_options['fields'] ) ? $pg_options['fields'] : array();
															if ( isset( $bt_fields['transaction_id'] ) || isset( $bt_fields['bank_name'] ) || isset( $bt_fields['account_name'] ) || isset( $bt_fields['additional_info'] ) || isset( $bt_fields['transfer_mode'] ) ) {
																$pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_bank_transfer ' . esc_attr($display_block) . ' arm-form-container">';
																if ( isset( $bt_fields['transaction_id'] ) || isset( $bt_fields['bank_name'] ) || isset( $bt_fields['account_name'] ) || isset( $bt_fields['additional_info'] ) || isset( $bt_fields['transfer_mode'] ) ) {
																	$pg_fields .= '<div class="' . esc_attr($form_style_class) . '">';
																	$pg_fields .= $arm_payment_gateways->arm_get_bank_transfer_form( $pg_options, $fieldPosition, $errPosCCField, $setup_modules['modules']['forms'], $form_settings );
																	$pg_fields .= '</div>';
																}
																$pg_fields .= '</div>';
															}
															break;
														default:
															$gateway_fields = apply_filters( 'arm_membership_setup_gateway_option', '', $pg, $pg_options );
															
															if ( ! empty( $gateway_fields ) ) {
																$pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_' . esc_attr($pg) . ' ' . esc_attr($display_block) . ' arm-form-container">';
																$pg_fields .= '<div class="' . esc_attr($form_style_class) . '">';
																$pg_fields .= $gateway_fields;
																$pg_fields .= '</div>';
																$pg_fields .= '</div>';
															}
															break;
													}

													if ( ! empty( $pglabels ) && isset( $pglabels[ $pg ] ) ) {
														$pg_options['gateway_name'] = stripslashes_deep( $pglabels[ $pg ] );
													}

														// $module_content .='<option value="' . $payment_gateway_name . '" class="armMDOption armSelectOption' . $setup_modules['modules']['forms'] . ' arm_gateway_' . $payment_gateway_name . '" ' . $pg_checked . ' data-payment_mode="' . $payment_mode . '">' . $pg_options['gateway_name'] . '</option>';

														$module_content_options .= '<li data-value="' . $payment_gateway_name . '" class="arm__dc--item armMDOption armSelectOption' . esc_attr($setup_modules['modules']['forms']) . ' arm_gateway_' . esc_attr($payment_gateway_name) . '" data-payment_mode="' . esc_attr($payment_mode) . '">' . esc_html($pg_options['gateway_name']) . '</li>';

														$i++;
														$module_content .= "<input type='hidden' name='arm_payment_mode[".esc_attr($pg)."]'  value='".esc_attr($payment_mode)."' />";
												}
											}

												$module_content         .= '<dt class="arm__dc--head">
                                                                            <span class="arm__dc--head__title">' . esc_attr($pg_options_gateway_name) . '</span><input type="text" style="display:none;" value="" class="arm-df__dc--head__autocomplete arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>';
													$module_content     .= '</dt>';
													$module_content     .= '<dd class="arm__dc--items-wrap">';
														$module_content .= '<ul class="arm__dc--items" data-id="arm_payment_gateway_' . esc_attr($setupRandomID) . '" style="display:none;">';

													$module_content .= $module_content_options;
													$module_content .= '</ul>';
													$module_content .= '</dd>';
													$module_content .= '<input type="hidden" id="arm_payment_gateway_' . esc_attr($setupRandomID) . '" name="payment_gateway" class="arm_module_gateway_input select_skin" aria-label="gateway" onchange="armPaymentGatewayChange(\'arm_setup_form' . esc_attr($setupRandomID) . '\')" value="' . esc_attr($selectedKey) . '" />';
													$module_content .= '</dl>';
													$module_content .= $ffield_label;
												// $module_content .= '</select>';
												$module_content .= '</div></div></div>';

										}

										$module_content .= '<div class="armclear"></div>';
										$module_content .= $pg_fields;
										$module_content .= '<div class="armclear"></div>';
										$module_content .= '</div>';
										$module_content  = apply_filters( 'arm_after_setup_gateway_section', $module_content, $setupID, $setup_data );
										$module_content .= '<div class="armclear"></div>';
										// $module_content .= '<script type="text/javascript" data-cfasync="false">armSetDefaultPaymentGateway(\'' . $selectedKey . '\');</script>';
										$module_content .= '</div></div>';
										/* Payment Mode Module */

										$arm_automatic_sub_label                              = ( isset( $setup_data['setup_labels']['automatic_subscription'] ) && ! empty( $setup_data['setup_labels']['automatic_subscription'] ) ) ? stripslashes_deep( $setup_data['setup_labels']['automatic_subscription'] ) : esc_html__( 'Auto Debit Payment', 'armember-membership' );
										$arm_semi_automatic_sub_label                         = ( isset( $setup_data['setup_labels']['semi_automatic_subscription'] ) && ! empty( $setup_data['setup_labels']['semi_automatic_subscription'] ) ) ? stripslashes_deep( $setup_data['setup_labels']['semi_automatic_subscription'] ) : esc_html__( 'Manual Payment', 'armember-membership' );
										$module_content                                      .= "<div class='arm_payment_mode_main_wrapper'><div class='arm_payment_mode_wrapper' id='arm_payment_mode_wrapper' style='text-align:{$formPosition};'>";
										$setup_data['setup_labels']['payment_mode_selection'] = ( isset( $setup_data['setup_labels']['payment_mode_selection'] ) && ! empty( $setup_data['setup_labels']['payment_mode_selection'] ) ) ? $setup_data['setup_labels']['payment_mode_selection'] : esc_html__( 'How you want to pay?', 'armember-membership' );
										$module_content                                      .= "<div class='arm_setup_section_title_wrapper arm_payment_mode_selection_wrapper' >" . stripslashes_deep( $setup_data['setup_labels']['payment_mode_selection'] ) . '</div>';
										$module_content                                      .= "<div class='arm-df__form-field'>";
										$module_content                                      .= "<div class='arm-df__form-field-wrap_radio arm-df__form-field-wrap arm-d-flex arm-justify-content-" . esc_attr($form_position) . "'><div class='arm-df__radio arm-d-flex arm-align-items-" . esc_attr($form_position) . "'><input type='radio' checked='checked' name='arm_selected_payment_mode' value='auto_debit_subscription' class='arm_selected_payment_mode arm-df__form-control--is-radio' id='arm_selected_payment_mode_auto_".esc_attr($setupRandomID)."'/><label for='arm_selected_payment_mode_auto_".esc_attr($setupRandomID)."' class='arm_payment_mode_label arm-df__fc-radio--label'>" . esc_html($arm_automatic_sub_label) . '</label></div>';
										$module_content                                      .= "<div class='arm-df__radio arm-d-flex arm-align-items-" . esc_attr($form_position) . "'><input type='radio'  name='arm_selected_payment_mode' value='manual_subscription' class='arm_selected_payment_mode arm-df__form-control--is-radio' id='arm_selected_payment_mode_semi_auto_".esc_attr($setupRandomID)."'/><label for='arm_selected_payment_mode_semi_auto_".esc_attr($setupRandomID)."' class='arm_payment_mode_label arm-df__fc-radio--label'>" . esc_attr($arm_semi_automatic_sub_label) . '</label></div></div>';
										$module_content                                      .= '</div>';
										$module_content                                      .= '</div></div>';
									}
								}
								break;
							case 'order_detail':
								if ( ! empty( $modules['plans'] ) ) {
									/* $module_content .= '<div class="arm_order_description arm_module_box"></div>'; */
									$module_content = apply_filters( 'arm_after_setup_order_detail', $module_content, $setupID, $setup_data );
									if ( isset( $setup_data['setup_labels']['summary_text'] ) && ! empty( $setup_data['setup_labels']['summary_text'] ) ) {
										$setupSummaryText = stripslashes( $setup_data['setup_labels']['summary_text'] );
										$setupSummaryText = str_replace( '[PLAN_NAME]', '<span class="arm_plan_name_text"></span>', $setupSummaryText );
										$setupSummaryText = str_replace( '[PLAN_AMOUNT]', '<span class="arm_plan_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText );
										$setupSummaryText = str_replace( '[DISCOUNT_AMOUNT]', '<span class="arm_discount_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText );
										$setupSummaryText = str_replace( '[PAYABLE_AMOUNT]', '<span class="arm_payable_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText );
										$setupSummaryText = str_replace( '[TRIAL_AMOUNT]', '<span class="arm_trial_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText );
										$module_content  .= "<div class='arm_setup_summary_text_container arm_module_box' style='text-align:{$formPosition};'>";
										$module_content  .= '<input type="hidden" name="arm_total_payable_amount" data-id="arm_total_payable_amount" value=""/>';
										$module_content  .= '<input type="hidden" name="arm_zero_amount_discount" data-id="arm_zero_amount_discount" value="' . esc_attr($arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator( $global_currency ) ). '"/>';
										$module_content  .= '<div class="arm_setup_summary_text">' . nl2br( $setupSummaryText ) . '</div>';
										$module_content  .= '</div>';
									}
								}
								break;
							default:
								break;
						}
						$module_html .= $module_content;
					}

					$content  = apply_filters( 'arm_before_setup_form_content', $content, $setupID, $setup_data );
					$content .= '<div class="arm_setup_form_container">';
					$content .= '<style type="text/css" id="arm_setup_style_' . esc_attr($args['id']) . '">';
					if ( ! empty( $setup_style ) ) {
						$sfontFamily = isset( $setup_style['font_family'] ) ? $setup_style['font_family'] : '';
						$gFontUrl    = $arm_member_forms->arm_get_google_fonts_url( array( $sfontFamily ) );
						if ( ! empty( $gFontUrl ) ) {
							// $setupGoogleFonts .= '<link id="google-font-' . $setupID . '" rel="stylesheet" type="text/css" href="' . $gFontUrl . '" />';
							wp_enqueue_style( 'google-font-' . $setupID, $gFontUrl, array(), MEMBERSHIPLITE_VERSION );
						}
						$content .= $this->arm_generate_setup_style( $setupID, $setup_style );
					}
					if ( ! empty( $formStyle ) ) {
						$content .= $formStyle;
					}
					if ( ! empty( $custom_css ) ) {
						$content .= $custom_css;
					}
					$content .= '</style>';
					$content .= $setupGoogleFonts;
					$content .= '<div class="arm_setup_messages arm_form_message_container"></div>';

					$is_form_class_rtl = '';
					if ( is_rtl() ) {
						$is_form_class_rtl = 'is_form_class_rtl';
					}
					$form_attr        = '';
					$general_settings = isset( $arm_global_settings->global_settings ) ? $arm_global_settings->global_settings : array();
					$spam_protection  = isset( $general_settings['spam_protection'] ) ? $general_settings['spam_protection'] : '';
					if ( ! empty( $spam_protection ) ) {
						$captcha_code = arm_generate_captcha_code();
						if ( ! isset( $_SESSION['ARM_FILTER_INPUT'] ) ) {
							$_SESSION['ARM_FILTER_INPUT'] = array();
						}
						if ( isset( $_SESSION['ARM_FILTER_INPUT'][ $setupRandomID ] ) ) {
							unset( $_SESSION['ARM_FILTER_INPUT'][ $setupRandomID ] );
						}
						$_SESSION['ARM_FILTER_INPUT'][ $setupRandomID ] = $captcha_code;
						$_SESSION['ARM_VALIDATE_SCRIPT']                = true;
						$form_attr                                     .= ' data-submission-key="' . esc_attr($captcha_code) . '" ';
					}

					$form_layout = ' arm_form_layout_' . esc_attr($form_settings['style']['form_layout']) . ' arm-default-form';
					if ( $form_settings['style']['form_layout'] == 'writer' ) {
						$form_layout .= ' arm-material-style arm_materialize_form ';
					} elseif ( $form_settings['style']['form_layout'] == 'rounded' ) {
						$form_layout .= ' arm-rounded-style ';
					} elseif ( $form_settings['style']['form_layout'] == 'writer_border' ) {
						$form_layout .= ' arm--material-outline-style arm_materialize_form ';
					}
					if ( ! empty( $form_settings['style']['validation_type'] ) && $form_settings['style']['validation_type'] == 'standard' ) {
						$form_layout .= ' arm_standard_validation_type ';
					}
					$content .= '<form method="post" name="arm_form" id="arm_setup_form' . esc_attr($setupRandomID) . '" class="arm_setup_form_' . $setupID . ' arm_membership_setup_form arm_form_' . esc_attr($modules['forms']) . esc_attr($form_layout) . ' ' . esc_attr($is_form_class_rtl) . '" enctype="multipart/form-data" data-random-id="' . esc_attr($setupRandomID) . '" novalidate ' . esc_attr($form_attr) . '>';
					if ( $args['hide_title'] == false && $args['popup'] == false ) {
						$content .= '<h3 class="arm_setup_form_title">' . esc_html($setup_name) . '</h3>';
					}
					$content .= '<input type="hidden" name="setup_id" value="' . esc_attr($setupID) . '" data-id="arm_setup_id"/>';
					$content .= '<input type="hidden" name="setup_action" value="membership_setup"/>';
					$content .= "<input type='text' name='arm_filter_input' data-random-key='".esc_attr($setupRandomID)."' value='' style='opacity:0 !important;display:none !important;visibility:hidden !important;' />";
					$content .= '<div class="arm_setup_form_inner_container">';
					$content .= '<input type="hidden" class="arm_global_currency" value="' . esc_attr($global_currency) . '"/>';
					// $currency_separators = $arm_payment_gateways->get_currency_separators_standard();
					// $currency_separators = wp_json_encode($currency_separators);
					$currency_separators = $arm_payment_gateways->get_currency_wise_separator( $global_currency );
					$currency_separators = ( ! empty( $currency_separators ) ) ? wp_json_encode( $currency_separators ) : '';
					$content            .= "<input type='hidden' class='arm_global_currency_separators' value='" . esc_attr($currency_separators) . "'/>";
					$content            .= '<input type="hidden" class="arm_pay_thgough_mpayment" name="arm_pay_thgough_mpayment" value="1"/>';

					$content .= $module_html;
					$content .= '<div class="armclear"></div>';
					$content .= '<div class="arm_setup_submit_btn_wrapper ' . esc_attr($form_style_class) . '">';
					$content .= '<div class="arm-df__form-group arm-df__form-group_submit">';
					// $content .= '<div class="arm_label_input_separator"></div>';
					// $content .= '<div class="arm_form_label_wrapper arm-df__field-label arm_form_member_field_submit"></div>';
					$content .= '<div class="arm-df__form-field">';
					$content .= '<div class="arm-df__form-field-wrap_submit arm-df__form-field-wrap" id="arm_setup_form_input_container' . esc_attr($setupID) . '">';
					$ngClick  = 'onclick="armSubmitBtnClick(event)"';
					if ( current_user_can( 'administrator' ) ) {
						$ngClick = 'onclick="return false;"';
					}

					if(file_exists(ABSPATH . 'wp-admin/includes/file.php')){
						require_once(ABSPATH . 'wp-admin/includes/file.php');
					}

					WP_Filesystem();
					global $wp_filesystem;
					$arm_loader_url = MEMBERSHIPLITE_IMAGES_URL . "/loader.svg";
					$arm_loader_img = $wp_filesystem->get_contents($arm_loader_url);

					$content .= '<button type="submit" name="ARMSETUPSUBMIT" class="arm_setup_submit_btn arm-df__form-control-submit-btn arm-df__form-group_button arm_material_input ' . esc_attr($btn_style_class) . '" ' . $ngClick . '><span class="arm_spinner">' . $arm_loader_img . '</span>' . html_entity_decode( stripslashes( $submit_btn ) ) . '</button>';
					if ( is_user_logged_in() ) {
						$content .= do_shortcode( '[armember_spam_filters]' );
					}
					$content .= '</div>';
					$content .= '</div>';
					$content .= '</div>';
					$content .= '</div>';
					$content .= '</div>';
					$content .= '</form></div>';

					if ( $args['popup'] !== false ) {
						$popup_content  = '<div class="arm_setup_form_popup_container">';
						$link_title     = ( ! empty( $args['link_title'] ) ) ? $args['link_title'] : $setup_name;
						$link_style     = $link_hover_style = '';
						$popup_content .= '<style type="text/css">';
						if ( ! empty( $args['link_css'] ) ) {
							$link_style     = esc_html( $args['link_css'] );
							$popup_content .= '.arm_setup_form_popup_link_' . $setupID . '{' . $link_style . '}';
						}
						if ( ! empty( $args['link_hover_css'] ) ) {
							$link_hover_style = esc_html( $args['link_hover_css'] );
							$popup_content   .= '.arm_setup_form_popup_link_' . $setupID . ':hover{' . $link_hover_style . '}';
						}
						$popup_content .= '</style>';
						$pformRandomID  = $setupID . '_popup_' . arm_generate_random_code();
						$popupLinkID    = 'arm_setup_form_popup_link_' . esc_attr($setupID);
						$popupLinkClass = 'arm_setup_form_popup_link arm_setup_form_popup_link_' . esc_attr($setupID);
						if ( ! empty( $args['link_class'] ) ) {
							$popupLinkClass .= ' ' . esc_html( $args['link_class'] );
						}
						$popupLinkAttr = 'data-form_id="' . esc_attr($pformRandomID) . '" data-toggle="armmodal"  data-modal_bg="' . esc_attr($args['modal_bgcolor']) . '" data-overlay="' . esc_attr($args['overlay']) . '"';
						if ( ! empty( $args['link_type'] ) && strtolower( $args['link_type'] ) == 'button' ) {
							$popup_content .= '<button type="button" id="' . esc_attr($popupLinkID) . '" class="' . esc_attr($popupLinkClass) . ' arm_setup_form_popup_button" ' . esc_attr($popupLinkAttr) . '>' . esc_html($link_title) . '</button>';
						} else {
							$popup_content .= '<a href="javascript:void(0)" id="' . esc_attr($popupLinkID) . '" class="' . esc_attr($popupLinkClass) . ' arm_setup_form_popup_ahref" ' . esc_attr($popupLinkAttr) . '>' . esc_html($link_title) . '</a>';
						}
						$popup_style = $popup_content_height = '';
						$popupHeight = 'auto';
						$popupWidth  = '500';
						if ( ! empty( $args['popup_height'] ) ) {
							if ( $args['popup_height'] == 'auto' ) {
								$popup_style .= 'height: auto;';
							} else {
								$popup_style         .= 'overflow: hidden;height: ' . $args['popup_height'] . 'px;';
								$popupHeight          = ( $args['popup_height'] - 70 ) . 'px';
								$popup_content_height = 'overflow-x: hidden;overflow-y: auto;height: ' . ( $args['popup_height'] - 70 ) . 'px;';
							}
						}
						if ( ! empty( $args['popup_width'] ) ) {
							if ( $args['popup_width'] == 'auto' ) {
								$popup_style .= '';
							} else {
								$popupWidth   = $args['popup_width'];
								$popup_style .= 'width: ' . $args['popup_width'] . 'px;';
							}
						}
						$popup_content .= '<div class="popup_wrapper arm_popup_wrapper arm_popup_member_setup_form arm_popup_member_setup_form_' . esc_attr($setupID) . ' arm_popup_member_setup_form_' . $pformRandomID . '" style="' . $popup_style . '" data-width="' . esc_attr($popupWidth) . '"><div class="popup_setup_inner_container popup_wrapper_inner">';
						$popup_content .= '<div class="popup_header">';
						$popup_content .= '<span class="popup_close_btn arm_popup_close_btn"></span>';
						$popup_content .= '<div class="popup_header_text arm_setup_form_heading_container">';
						if ( $args['hide_title'] == false ) {
							$popup_content .= '<span class="arm_setup_form_field_label_wrapper_text">' . esc_attr($setup_name) . '</span>';
						}
						$popup_content .= '</div>';
						$popup_content .= '</div>';
						$popup_content .= '<div class="popup_content_text" style="' . $popup_content_height . '" data-height="' . esc_attr($popupHeight) . '">';
						$popup_content .= $content;
						$popup_content .= '</div><div class="armclear"></div>';
						$popup_content .= '</div></div>';
						$popup_content .= '</div>';
						$content        = $popup_content;
						$content       .= '<div class="armclear">&nbsp;</div>';
					}
					$content = apply_filters( 'arm_after_setup_form_content', $content, $setupID, $setup_data );
				}
			}
			$ARMemberLite->arm_check_font_awesome_icons( $content );
			$ARMemberLite->enqueue_angular_script();
			return do_shortcode( $content );
		}

		function arm_setup_shortcode_func( $atts, $content = '' ) {
			global $wp, $wpdb, $current_user, $ARMemberLite, $arm_member_forms, $arm_global_settings, $arm_payment_gateways, $arm_manage_coupons, $arm_subscription_plans, $arm_lite_bpopup_loaded, $ARMSPAMFILEURL;
			$ARMemberLite->arm_session_start();
			/* ====================/.Begin Set Shortcode Attributes./==================== */
			$defaults = array(
				'id'                           => 0, /* Membership Setup Wizard ID */
				'hide_title'                   => false,
				'class'                        => '',
				'popup'                        => false, /* Form will be open in popup box when options is true */
				'link_type'                    => 'link',
				'link_class'                   => '', /* /* Possible Options:- `link`, `button` */
				'link_title'                   => esc_html__( 'Click here to open Set up form', 'armember-membership' ), /* Default to form name */
				'popup_height'                 => '',
				'popup_width'                  => '',
				'overlay'                      => '0.6',
				'modal_bgcolor'                => '#000000',
				'redirect_to'                  => '',
				'link_css'                     => '',
				'link_hover_css'               => '',
				'is_referer'                   => '0',
				'preview'                      => false,
				'setup_data'                   => '',
				'subscription_plan'            => 0,
				'hide_plans'                   => 0,
				'payment_duration'             => 0,
				'setup_form_id'                => '',
				'your_current_membership_text' => esc_html__( 'Your Current Membership', 'armember-membership' ),
			);
			/* Extract Shortcode Attributes */
			$args = shortcode_atts( $defaults, $atts, 'arm_setup' );

			$args = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $args ); //phpcs:ignore

			extract( $args );
			$args['hide_title'] = ( $args['hide_title'] === 'true' || $args['hide_title'] == '1' ) ? true : false;
			$args['popup']      = ( $args['popup'] === 'true' || $args['popup'] == '1' ) ? true : false;
			$isPreview          = ( $args['preview'] === 'true' || $args['preview'] == '1' ) ? true : false;
			if ( $args['popup'] ) {
				$arm_lite_bpopup_loaded = 1;
			}
			$completed_recurrence = '';
			$total_recurring      = '';
			/* ====================/.End Set Shortcode Attributes./==================== */
			if ( ( ! empty( $args['id'] ) && $args['id'] != 0 ) || ( $isPreview && ! empty( $args['setup_data'] ) ) ) {

				$setupID = $args['id'];
				if ( $isPreview && ! empty( $args['setup_data'] ) ) {
					$setup_data                     = maybe_unserialize( $args['setup_data'] );
					$setup_data['arm_setup_labels'] = $setup_data['setup_labels'];
				} else {
					$setup_data = $this->arm_get_membership_setup( $setupID );
				}
				$setup_data = apply_filters( 'arm_setup_data_before_setup_shortcode', $setup_data, $args );
				do_action( 'arm_before_render_membership_setup_form', $setup_data, $args );

				if ( ! empty( $setup_data ) && ! empty( $setup_data['setup_modules']['modules'] ) ) {

					$all_global_settings   = $arm_global_settings->arm_get_all_global_settings();
					$general_settings      = $all_global_settings['general_settings'];
					$setupRandomID         = $setupID . '_' . arm_generate_random_code();
					$global_currency       = $arm_payment_gateways->arm_get_global_currency();
					$current_user_id       = get_current_user_id();
					$current_user_plan_ids = get_user_meta( $current_user_id, 'arm_user_plan_ids', true );
					$current_user_plan_ids = ! empty( $current_user_plan_ids ) ? $current_user_plan_ids : array();

					$user_posts = get_user_meta( $current_user_id, 'arm_user_post_ids', true );
					$user_posts = ! empty( $user_posts ) ? $user_posts : array();

					if ( ! empty( $current_user_plan_ids ) && ! empty( $user_posts ) ) {
						foreach ( $current_user_plan_ids as $user_plans_key => $user_plans_val ) {
							if ( ! empty( $user_posts ) ) {
								foreach ( $user_posts as $user_post_key => $user_post_val ) {
									if ( $user_post_key == $user_plans_val ) {
										unset( $current_user_plan_ids[ $user_plans_key ] );
									}
								}
							}
						}
					}

					$current_user_plan_ids = apply_filters( 'arm_modify_plan_ids_externally', $current_user_plan_ids, $current_user_id );

					$current_user_plan = '';
					$current_plan_data = array();
					if ( ! empty( $current_user_plan_ids ) ) {
						$current_user_plan = current( $current_user_plan_ids );
						$current_plan_data = get_user_meta( $current_user_id, 'arm_user_plan_' . $current_user_plan, true );
					}
					$setup_name         = ( ! empty( $setup_data['setup_name'] ) ) ? stripslashes( $setup_data['setup_name'] ) : '';
					$button_labels      = $setup_data['setup_labels']['button_labels'];
					$submit_btn         = ( ! empty( $button_labels['submit'] ) ) ? $button_labels['submit'] : esc_html__( 'Submit', 'armember-membership' );
					$setup_modules      = $setup_data['setup_modules'];
					$user_selected_plan = isset( $setup_modules['selected_plan'] ) ? $setup_modules['selected_plan'] : '';
					$modules            = $setup_modules['modules'];
					$setup_style        = isset( $setup_modules['style'] ) ? $setup_modules['style'] : array();
					$setup_type         = ( ! empty( $setup_data['setup_type'] ) ) ? stripslashes( $setup_data['setup_type'] ) : 0;

					$tax_percentage = 0;
					$enable_tax     = isset( $general_settings['enable_tax'] ) ? $general_settings['enable_tax'] : 0;
					if ( $enable_tax == 1 ) {
						$tax_values     = $this->arm_get_sales_tax( $general_settings, '', $current_user_id, $modules['forms'] );
						$tax_percentage = ! empty( $tax_values['tax_percentage'] ) ? $tax_values['tax_percentage'] : '0';
					}

					$formPosition        = ( isset( $setup_style['form_position'] ) && ! empty( $setup_style['form_position'] ) ) ? $setup_style['form_position'] : 'left';
					$plan_selection_area = ( isset( $setup_style['plan_area_position'] ) && ! empty( $setup_style['plan_area_position'] ) ) ? $setup_style['plan_area_position'] : 'before';

					$hide_current_plans    = isset( $setup_style['hide_current_plans'] ) ? $setup_style['hide_current_plans'] : 0;
					$previuos_button_label = ( isset( $button_labels['previous'] ) && ! empty( $button_labels['previous'] ) ) ? stripslashes_deep( $button_labels['previous'] ) : esc_html__( 'Previous', 'armember-membership' );
					$next_button_label     = ( isset( $button_labels['next'] ) && ! empty( $button_labels['next'] ) ) ? stripslashes_deep( $button_labels['next'] ) : esc_html__( 'Next', 'armember-membership' );

					$two_step = ( isset( $setup_style['two_step'] ) ) ? $setup_style['two_step'] : 0;

					$fieldPosition   = 'left';
					$custom_css      = isset( $setup_modules['custom_css'] ) ? $setup_modules['custom_css'] : '';
					$modules['step'] = ( ! empty( $modules['step'] ) ) ? $modules['step'] : array( -1 );

					if ( $plan_selection_area == 'before' || $two_step == 1 ) {
						$module_order = array(
							'plans'         => 1,
							'payment_cycle' => 2,
							'note'          => 3,
							'forms'         => 4,
							'gateways'      => 5,
							'order_detail'  => 6,
						);
					} else {

						$module_order = array(
							'forms'         => 1,
							'plans'         => 2,
							'payment_cycle' => 3,
							'note'          => 4,
							'gateways'      => 5,
							'order_detail'  => 6,
						);
					}

					$modules['forms'] = ( ! empty( $modules['forms'] ) && $modules['forms'] != 0 ) ? $modules['forms'] : 0;
					$step_one_modules = $step_two_modules = '';
					/*
					 Check `GET` or `POST` Data */
					/* first check if user have selected any plan than select that plan otherwise set value from options of setup */
					if ( $current_user_plan != '' ) {
						$selected_plan_id = $current_user_plan;
					} else {
						$selected_plan_id = $user_selected_plan;
					}
					if ( ! empty( $_REQUEST['subscription_plan'] ) && $_REQUEST['subscription_plan'] != 0 ) { //phpcs:ignore
						$selected_plan_id = intval( $_REQUEST['subscription_plan'] ); //phpcs:ignore
					}

					$selected_payment_duration = 1;
					if ( ! empty( $_REQUEST['payment_duration'] ) && $_REQUEST['payment_duration'] != 0 ) { //phpcs:ignore
						$selected_payment_duration = intval( $_REQUEST['payment_duration'] ); //phpcs:ignore
					}
					if ( ! empty( $args['subscription_plan'] ) && $args['subscription_plan'] != 0 ) {
						$selected_plan_id = $args['subscription_plan'];
						if ( ! empty( $args['payment_duration'] ) && $args['payment_duration'] != 0 ) {
							$selected_payment_duration = $args['payment_duration'];
						}
					}

					$isHidePlans = false;
					if ( ! empty( $selected_plan_id ) && $selected_plan_id != 0 ) {
						if ( ! empty( $_REQUEST['hide_plans'] ) && $_REQUEST['hide_plans'] == 1 ) { //phpcs:ignore
							$isHidePlans = true;
						}
						if ( ! empty( $args['hide_plans'] ) && $args['hide_plans'] == 1 ) {
							$isHidePlans = true;
						}
					}

					$is_hide_plan_selection_area = false;
					if ( isset( $setup_style['hide_plans'] ) && $setup_style['hide_plans'] == 1 ) {
						$is_hide_plan_selection_area = true;
					}

					$arm_two_step_class = '';
					if ( $two_step ) {
						if ( $isHidePlans == true || $is_hide_plan_selection_area == true ) {

						} else {
							$arm_two_step_class = ' arm_hide';
						}
					}

					if ( is_user_logged_in() ) {
						global $current_user;
						if ( ! empty( $current_user->data->arm_primary_status ) ) {
							$current_user_status = $current_user->data->arm_primary_status;
						} else {
							$current_user_status = arm_get_member_status( $current_user_id );
						}
					}

					$selected_plan_data = array();
					$module_html        = $formStyle = $setupGoogleFonts = '';
					$errPosCCField      = 'right';
					if ( is_rtl() ) {
						$is_form_class_rtl = 'arm_form_rtl';
					} else {
						$is_form_class_rtl = 'arm_form_ltr';
					}
					$form_style_class = ' arm_form_0 arm_form_layout_writer armf_label_placeholder armf_alignment_left armf_layout_block armf_button_position_left ' . $is_form_class_rtl;
					$btn_style_class  = ' --arm-is-flat-style ';
					if ( ! empty( $modules['forms'] ) ) {
						/* Query Monitor Change */
						if ( isset( $GLOBALS['arm_setup_form_settings'] ) && isset( $GLOBALS['arm_setup_form_settings'][ $modules['forms'] ] ) ) {
							$form_settings = $GLOBALS['arm_setup_form_settings'][ $modules['forms'] ];
						} else {
							$form_settings = $wpdb->get_var( $wpdb->prepare('SELECT `arm_form_settings` FROM `' . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_form_id`=%d",$modules['forms']) );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_payment_log is a table name.
							if ( ! isset( $GLOBALS['arm_setup_form_settings'] ) ) {
								$GLOBALS['arm_setup_form_settings'] = array();
							}
							$GLOBALS['arm_setup_form_settings'][ $modules['forms'] ] = $form_settings;
						}
						$form_settings = ( ! empty( $form_settings ) ) ? maybe_unserialize( $form_settings ) : array();
					}
					$plan_payment_cycles = array();

					foreach ( $module_order as $module => $order ) {
						$module_content                  = '';
						$arm_user_id                     = 0;
						$arm_user_old_plan               = 0;
						$plan_id_array                   = array();
						$arm_user_selected_payment_mode  = 0;
						$arm_user_selected_payment_cycle = 0;
						$arm_last_payment_status         = 'success';

						switch ( $module ) {
							case 'plans':
								if ( ! empty( $modules['plans'] ) ) {
									if ( is_user_logged_in() ) {
										global $current_user;
										$arm_user_id = $current_user->ID;

										$user_firstname = $current_user->user_firstname;
										$user_lastname  = $current_user->user_lastname;
										$user_email     = $current_user->user_email;
										if ( $user_firstname != '' && $user_lastname != '' ) {
											$arm_user_firstname_lastname = $user_firstname . ' ' . $user_lastname;
										} else {
											$arm_user_firstname_lastname = $user_email;
										}

										if ( ! empty( $current_user_plan_ids ) ) {
											$plan_name_array = array();
											foreach ( $current_user_plan_ids as $plan_id ) {
												$planData                       = get_user_meta( $arm_user_id, 'arm_user_plan_' . $plan_id, true );
												$arm_user_selected_payment_mode = $planData['arm_payment_mode'];
												$arm_user_current_plan_detail   = $planData['arm_current_plan_detail'];

												$plan_name_array[] = isset( $arm_user_current_plan_detail['arm_subscription_plan_name'] ) ? stripslashes( $arm_user_current_plan_detail['arm_subscription_plan_name'] ) : '';
												$plan_id_array[]   = $plan_id;

												$curPlanDetail        = $planData['arm_current_plan_detail'];
												$completed_recurrence = $planData['arm_completed_recurring'];
												if ( ! empty( $curPlanDetail ) ) {
													$arm_user_old_plan_info = new ARM_Plan_Lite( 0 );
													$arm_user_old_plan_info->init( (object) $curPlanDetail );
												} else {
													$arm_user_old_plan_info = new ARM_Plan_Lite( $arm_user_old_plan );
												}
												$total_recurring           = '';
												$arm_user_old_plan_options = $arm_user_old_plan_info->options;
												if ( $arm_user_old_plan_info->is_recurring() ) {
													$arm_user_selected_payment_cycle = $planData['arm_payment_cycle'];
													$arm_user_old_plan_data          = $arm_user_old_plan_info->prepare_recurring_data( $arm_user_selected_payment_cycle );
													$total_recurring                 = $arm_user_old_plan_data['rec_time'];

													$now = current_time( 'mysql' );

													$arm_last_payment_status = $wpdb->get_var( $wpdb->prepare( 'SELECT `arm_transaction_status` FROM `' . $ARMemberLite->tbl_arm_payment_log . '` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1', $arm_user_id, $plan_id, $now ) ); //phpcs:ignore --Reason: $ARMemberLite->tbl_arm_payment_log is a table name.

												}

												$module_content .= '<input type="hidden" data-id="arm_user_firstname_lastname" value="' . esc_attr($arm_user_firstname_lastname) . '">';

												$module_content .= '<input type="hidden" data-id="arm_user_last_payment_status_' . esc_attr($plan_id) . '" value="' . esc_attr($arm_last_payment_status) . '">';

												$module_content .= '<input type="hidden" data-id="arm_user_done_payment_' . esc_attr($plan_id) . '" value="' . esc_attr($completed_recurrence) . '">';
												$module_content .= '<input type="hidden" data-id="arm_user_old_plan_total_cycle_' . esc_attr($plan_id) . '" value="' . esc_attr($total_recurring) . '">';

												$module_content .= '<input type="hidden" data-id="arm_user_selected_payment_cycle_' . esc_attr($plan_id) . '" value="' . esc_attr($arm_user_selected_payment_cycle) . '">';
												$module_content .= '<input type="hidden" data-id="arm_user_selected_payment_mode_' . esc_attr($plan_id) . '" value="' . esc_attr($arm_user_selected_payment_mode) . '">';
											}
										}
										$arm_is_user_logged_in_flag = 1;
									} else {
										$arm_is_user_logged_in_flag = 0;
									}

									if ( ! empty( $plan_id_array ) ) {
										$arm_user_old_plan = implode( ',', $plan_id_array );
									}

									$module_content .= '<input type="hidden" data-id="arm_user_old_plan" name="arm_user_old_plan" value="' . esc_attr($arm_user_old_plan) . '">';
									$module_content .= '<input type="hidden" name="arm_is_user_logged_in_flag" data-id="arm_is_user_logged_in_flag" value="' . esc_attr($arm_is_user_logged_in_flag) . '">';
									$planOrders      = ( isset( $modules['plans_order'] ) && ! empty( $modules['plans_order'] ) ) ? $modules['plans_order'] : array();
									if ( ! empty( $planOrders ) ) {
										asort( $planOrders );
									}
									$plans = $this->armSortModuleOrders( $modules['plans'], $planOrders );
									if ( ! empty( $plans ) ) {
										$all_active_plans = $arm_subscription_plans->arm_get_all_active_subscription_plans();
										$all_active_plans = apply_filters( 'arm_filter_active_plans_for_setup', $all_active_plans, $setup_type );

										$is_hide_class = '';
										if ( $isHidePlans == true || $is_hide_plan_selection_area == true ) {
											$is_hide_class = 'style="display:none;"';
										}
										$form_no = '';

										$form_layout = '';
										if ( ! empty( $modules['forms'] ) && $modules['forms'] != 0 ) {

											if ( ! empty( $form_settings ) ) {
												$form_no     = 'arm_form_' . $modules['forms'];
												$form_layout = ' arm_form_layout_' . $form_settings['style']['form_layout'] . ' arm-default-form';
												if ( $form_settings['style']['form_layout'] == 'writer' ) {
													$form_layout .= ' arm-material-style arm_materialize_form ';
												} elseif ( $form_settings['style']['form_layout'] == 'rounded' ) {
													$form_layout .= ' arm-rounded-style ';
												} elseif ( $form_settings['style']['form_layout'] == 'writer_border' ) {
													$form_layout .= ' arm--material-outline-style arm_materialize_form ';
												}
												if ( ! empty( $form_settings['style']['validation_type'] ) && $form_settings['style']['validation_type'] == 'standard' ) {
													$form_layout .= ' arm_standard_validation_type ';
												}
											}
										}

										if ( ! empty( $current_user_plan_ids ) ) {

											$module_content .= '<div class="arm_current_user_plan_info">' . $your_current_membership_text . ': <span>' . implode( ', ', $plan_name_array ) . '</span></div>';
										}

										$module_content .= '<div class="arm_module_plans_main_container"><div class="arm_module_plans_container arm_module_box ' . esc_attr($form_no) . ' ' . esc_attr($form_layout) . ' " ' . $is_hide_class . '>';

										$column_type              = ( ! empty( $setup_modules['plans_columns'] ) ) ? $setup_modules['plans_columns'] : '1';
										$module_content          .= '<input type="hidden" name="arm_front_plan_skin_type" data-id="arm_front_plan_skin_type" value="' . $setup_style['plan_skin'] . '">';
										$allowed_payment_gateways = array();

										if ( $hide_current_plans == 1 ) {
											if ( ! empty( $current_user_plan_ids ) ) {
												$plans = array_diff( $plans, $current_user_plan_ids );
											}
										}
										if ( $setup_style['plan_skin'] == 'skin5' ) {
											$membership_plan_label     = ( ! empty( $setup_data['setup_labels']['member_plan_field_title'] ) ) ? $setup_data['setup_labels']['member_plan_field_title'] : esc_html__( 'Select Membership Plan', 'armember-membership' );
											$dropdown_class            = 'arm-df__form-field-wrap_select';
											$arm_allow_notched_outline = 0;
											if ( $form_settings['style']['form_layout'] == 'writer_border' ) {
												$arm_allow_notched_outline = 1;
												$inputPlaceholder          = '';
											}

											$arm_field_wrap_active_class = $ffield_label_html = $ffield_label = '';
											if ( ! empty( $arm_allow_notched_outline ) ) {
												$arm_field_wrap_active_class = ' arm-df__form-material-field-wrap';

												$ffield_label_html  = '<div class="arm-notched-outline">';
												$ffield_label_html .= '<div class="arm-notched-outline__leading"></div>';
												$ffield_label_html .= '<div class="arm-notched-outline__notch">';

												$ffield_label_html .= '<label class="arm-df__label-text active arm_material_label">' . esc_html($membership_plan_label) . '</label>';

												$ffield_label_html .= '</div>';
												$ffield_label_html .= '<div class="arm-notched-outline__trailing"></div>';
												$ffield_label_html .= '</div>';

												$ffield_label = $ffield_label_html;
											} else {
												$class_label = '';
												if ( $form_settings['style']['form_layout'] == 'writer' ) {
													$class_label  = 'arm-df__label-text';
													$ffield_label = '<label class="' . esc_attr($class_label) . ' active">' . esc_html($membership_plan_label) . '</label>';
												}
											}
											$planSkinFloat = 'float:none;';
											switch ( $formPosition ) {
												case 'left':
													$planSkinFloat = '';
													break;
												case 'right':
													$planSkinFloat = 'float:right;';
													break;
											}
											$module_content         .= '<div class="arm-control-group arm-df__form-group arm-df__form-group_select">';
													$module_content .= '<div class="arm_label_input_separator"></div><div class="arm-df__form-field">';
											$module_content         .= '<div class="arm-df__form-field-wrap arm-controls arm-controls  payment_plan_dropdown_skin1 ' . esc_attr($arm_field_wrap_active_class) . ' ' . esc_attr($dropdown_class) . '" style="' . $planSkinFloat . '">';

											$module_content .= '<dl class="arm-df__dropdown-control column_level_dd">';
													// $module_content .= '<li class="arm__dc--item" data-label="Select Option" data-value="Select Option">Select Option</li>';
													// $module_content .= '<li class="arm__dc--item" data-label="Option1" data-value="Option1">Option1</li>';

											// $module_content .= '<select name="subscription_plan" class="arm_module_plan_input select_skin"  aria-label="plan" onchange="armPlanChange(\'arm_setup_form' . $setupRandomID . '\')">';

											$i = 0;

											if ( empty( $plans ) ) {
												return;
											}
											$module_content_options = $plan_option_label_selected = '';
											foreach ( $plans as $plan_id ) {
												if ( isset( $all_active_plans[ $plan_id ] ) ) {

													$plan_data = $all_active_plans[ $plan_id ];
													$planObj   = new ARM_Plan_Lite( 0 );
													$planObj->init( (object) $plan_data );
													$plan_type = $planObj->type;
													$planText  = $planObj->setup_plan_text();
													if ( $planObj->exists() ) {
														/* Checked Plan Radio According Settings. */
														$plan_checked = $plan_checked_class = '';
														if ( ! empty( $selected_plan_id ) && $selected_plan_id != 0 && in_array( $selected_plan_id, $plans ) ) {
															if ( $selected_plan_id == $plan_id ) {
																$plan_checked_class = 'arm_active';
																$plan_checked       = 'selected="selected"';
																$selected_plan_data = $plan_data;
															}
														} else {
															if ( $i == 0 ) {
																$plan_checked_class = 'arm_active';
																$plan_checked       = 'selected="selected"';
																$selected_plan_data = $plan_data;
															}
														}

														/* Check Recurring Details */
														$plan_options = $planObj->options;

														if ( is_user_logged_in() ) {
															if ( $arm_user_old_plan == $plan_id ) {
																$arm_user_payment_cycles = ( isset( $arm_user_old_plan_options['payment_cycles'] ) && ! empty( $arm_user_old_plan_options['payment_cycles'] ) ) ? $arm_user_old_plan_options['payment_cycles'] : array();
																if ( empty( $arm_user_payment_cycles ) ) {
																	$plan_amount    = $planObj->amount;
																	$recurring_time = isset( $arm_user_old_plan_options['recurring']['time'] ) ? $arm_user_old_plan_options['recurring']['time'] : 'infinite';
																	$recurring_type = isset( $arm_user_old_plan_options['recurring']['type'] ) ? $arm_user_old_plan_options['recurring']['type'] : 'D';
																	switch ( $recurring_type ) {
																		case 'D':
																			$billing_cycle = isset( $arm_user_old_plan_options['recurring']['days'] ) ? $arm_user_old_plan_options['recurring']['days'] : '1';
																			break;
																		case 'M':
																			$billing_cycle = isset( $arm_user_old_plan_options['recurring']['months'] ) ? $arm_user_old_plan_options['recurring']['months'] : '1';
																			break;
																		case 'Y':
																			$billing_cycle = isset( $arm_user_old_plan_options['recurring']['years'] ) ? $arm_user_old_plan_options['recurring']['years'] : '1';
																			break;
																		default:
																			$billing_cycle = '1';
																			break;
																	}
																	$payment_cycles                  = array(
																		array(
																			'cycle_label' => $planObj->plan_text( false, false ),
																			'cycle_amount' => $plan_amount,
																			'billing_cycle' => $billing_cycle,
																			'billing_type' => $recurring_type,
																			'recurring_time' => $recurring_time,
																			'payment_cycle_order' => 1,
																		),
																	);
																	$plan_payment_cycles[ $plan_id ] = $payment_cycles;
																} else {
																	if ( ( $completed_recurrence == $total_recurring && $total_recurring != 'infinite' ) || ( $completed_recurrence == '' && $arm_user_selected_payment_mode == 'auto_debit_subscription' ) ) {
																		$arm_user_new_payment_cycles     = ( isset( $plan_options['payment_cycles'] ) && ! empty( $plan_options['payment_cycles'] ) ) ? $plan_options['payment_cycles'] : array();
																		$plan_payment_cycles[ $plan_id ] = $arm_user_new_payment_cycles;
																	} else {
																		$plan_payment_cycles[ $plan_id ] = $arm_user_payment_cycles;
																	}
																}
															} else {
																if ( $planObj->is_recurring() ) {
																	if ( ! empty( $plan_options['payment_cycles'] ) ) {
																		$plan_payment_cycles[ $plan_id ] = $plan_options['payment_cycles'];
																	} else {

																		$plan_amount    = $planObj->amount;
																		$recurring_time = isset( $plan_options['recurring']['time'] ) ? $plan_options['recurring']['time'] : 'infinite';
																		$recurring_type = isset( $plan_options['recurring']['type'] ) ? $plan_options['recurring']['type'] : 'D';
																		switch ( $recurring_type ) {
																			case 'D':
																				$billing_cycle = isset( $plan_options['recurring']['days'] ) ? $plan_options['recurring']['days'] : '1';
																				break;
																			case 'M':
																				$billing_cycle = isset( $plan_options['recurring']['months'] ) ? $plan_options['recurring']['months'] : '1';
																				break;
																			case 'Y':
																				$billing_cycle = isset( $plan_options['recurring']['years'] ) ? $plan_options['recurring']['years'] : '1';
																				break;
																			default:
																				$billing_cycle = '1';
																				break;
																		}
																		$payment_cycles                  = array(
																			array(
																				'cycle_label' => $planObj->plan_text( false, false ),
																				'cycle_amount' => $plan_amount,
																				'billing_cycle' => $billing_cycle,
																				'billing_type' => $recurring_type,
																				'recurring_time' => $recurring_time,
																				'payment_cycle_order' => 1,
																			),
																		);
																		$plan_payment_cycles[ $plan_id ] = $payment_cycles;
																	}
																}
															}
														} else {
															if ( $planObj->is_recurring() ) {
																if ( ! empty( $plan_options['payment_cycles'] ) ) {
																	$plan_payment_cycles[ $plan_id ] = $plan_options['payment_cycles'];
																} else {
																	$plan_amount    = $planObj->amount;
																	$recurring_time = isset( $plan_options['recurring']['time'] ) ? $plan_options['recurring']['time'] : 'infinite';
																	$recurring_type = isset( $plan_options['recurring']['type'] ) ? $plan_options['recurring']['type'] : 'D';
																	switch ( $recurring_type ) {
																		case 'D':
																			$billing_cycle = isset( $plan_options['recurring']['days'] ) ? $plan_options['recurring']['days'] : '1';
																			break;
																		case 'M':
																			$billing_cycle = isset( $plan_options['recurring']['months'] ) ? $plan_options['recurring']['months'] : '1';
																			break;
																		case 'Y':
																			$billing_cycle = isset( $plan_options['recurring']['years'] ) ? $plan_options['recurring']['years'] : '1';
																			break;
																		default:
																			$billing_cycle = '1';
																			break;
																	}
																	$payment_cycles                  = array(
																		array(
																			'cycle_label' => $planObj->plan_text( false, false ),
																			'cycle_amount' => $plan_amount,
																			'billing_cycle' => $billing_cycle,
																			'billing_type' => $recurring_type,
																			'recurring_time' => $recurring_time,
																			'payment_cycle_order' => 1,
																		),
																	);
																	$plan_payment_cycles[ $plan_id ] = $payment_cycles;
																}
															}
														}

														$payment_type = $planObj->payment_type;
														$is_trial     = '0';
														$trial_amount = $arm_payment_gateways->arm_amount_set_separator( $global_currency, 0 );
														if ( $planObj->is_recurring() ) {
															$stripePlans = ( isset( $modules['stripe_plans'] ) && ! empty( $modules['stripe_plans'] ) ) ? $modules['stripe_plans'] : array();

															if ( $planObj->has_trial_period() ) {
																$is_trial     = '1';
																$trial_amount = ! empty( $plan_options['trial']['amount'] ) ?
																		$arm_payment_gateways->arm_amount_set_separator( $global_currency, $plan_options['trial']['amount'] ) : $trial_amount;
																if ( is_user_logged_in() ) {
																	if ( ! empty( $current_user_plan_ids ) ) {
																		if ( in_array( $planObj->ID, $current_user_plan_ids ) ) {
																			$is_trial = '0';
																		}
																	}
																}
															}
														}

														if ( ! $planObj->is_free() ) {
															$trial_amount = ! empty( $plan_options['trial']['amount'] ) ? $arm_payment_gateways->arm_amount_set_separator( $global_currency, $plan_options['trial']['amount'] ) : $trial_amount;
														}

														$allowed_payment_gateways_['paypal']        = '1';
														$allowed_payment_gateways_['stripe']        = '1';
														$allowed_payment_gateways_['bank_transfer'] = '1';
														$allowed_payment_gateways_['2checkout']     = '1';
														$allowed_payment_gateways_['authorize_net'] = '1';
														$allowed_payment_gateways_                  = apply_filters( 'arm_allowed_payment_gateways', $allowed_payment_gateways_, $planObj, $plan_options );

														$data_allowed_payment_gateways = wp_json_encode( $allowed_payment_gateways_ );

														$arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator( $global_currency, $planObj->amount );
														$arm_plan_amount = $planObj->amount = apply_filters( 'arm_modify_secondary_amount_outside', $arm_plan_amount, $plan_data );
														$planInputAttr   = ' data-type="' . esc_attr($plan_type) . '" data-plan_name="' . esc_attr($planObj->name) . '" data-amt="' . esc_attr($arm_plan_amount) . '" data-recurring="' . esc_attr($payment_type) . '" data-is_trial="' . esc_attr($is_trial) . '" data-trial_amt="' . esc_attr($trial_amount) . '" data-allowed_gateways=\'' . esc_attr($data_allowed_payment_gateways) . '\' data-plan_text="' . htmlentities( $planText ) . '"';

														$count_total_cycle = 0;
														if ( $planObj->is_recurring() ) {

															$count_total_cycle = count( $plan_payment_cycles[ $plan_id ] );
															$planInputAttr    .= '  " data-cycle="' . $count_total_cycle . '" data-cycle_label="' . esc_attr($plan_payment_cycles[ $plan_id ][0]['cycle_label']) . '"';
														} else {
															$planInputAttr .= " data-cycle='0' data-cycle_label=''";
														}

														$planInputAttr .= " data-tax='" . esc_attr($tax_percentage) . "'";

														// $module_content .='<option value="' . $plan_id . '" class="armMDOption armSelectOption' . $setup_modules['modules']['forms'] . '" ' . $planInputAttr . ' ' . $plan_checked . '>' . $planObj->name . ' (' . $planObj->plan_price(false) . ')</option>';
														$plan_option_label = $planObj->name . '(' . wp_strip_all_tags( $planObj->plan_price( false ) ) . ')';
														if ( ! empty( $plan_checked ) ) {
															$plan_option_label_selected = $plan_option_label;
														}
														$module_content_options .= '<li class="arm__dc--item armMDOption armSelectOption' . esc_attr($setup_modules['modules']['forms']) . ' arm_plan_option_check_' . esc_attr($plan_id) . '" ' . $planInputAttr . ' data-label="' . esc_attr($plan_option_label) . '" data-value="' . esc_attr($plan_id) . '">' . $plan_option_label . '</li>';
														$i++;
													}
												}
											}
											$selected_plan_data_selected = isset( $selected_plan_data['arm_subscription_plan_id'] ) ? $selected_plan_data['arm_subscription_plan_id'] : 0;

											$module_content     .= '<dt class="arm__dc--head">
                                                                    <span class="arm__dc--head__title">' . esc_attr($plan_option_label_selected) . '</span><input type="text" style="display:none;" value="" class="arm-df__dc--head__autocomplete arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>';
											$module_content     .= '</dt>';
											$module_content     .= '<dd class="arm__dc--items-wrap">';
												$module_content .= '<ul class="arm__dc--items" data-id="subscription_plan_' . esc_attr($setupRandomID) . '" style="display:none;">';

											$module_content .= $module_content_options;
											$module_content .= '</ul>';
											$module_content .= '</dd>';
											$module_content .= '<input type="hidden" name="subscription_plan" id="subscription_plan_' . esc_attr($setupRandomID) . '" class="arm_module_plan_input select_skin"  aria-label="plan" onchange="armPlanChange(\'arm_setup_form' . esc_attr($setupRandomID) . '\')" value="' . esc_attr($selected_plan_data_selected) . '" />';
											$module_content .= '</dl>';
											$module_content .= $ffield_label;
											// $module_content .= '</select>';
											$module_content .= '</div></div></div>';
										} else {

											if ( $setup_style['plan_skin'] != 'skin6' ) {
												$module_content .= '<ul class="arm_module_plans_ul arm_column_' . $column_type . '" style="text-align:' . $formPosition . ';">';
											} else {
												$module_content .= '<ul class="arm_module_plans_ul arm_column_1">';
											}

											if ( empty( $plans ) ) {
												return;
											}
											$i = 0;
											foreach ( $plans as $plan_id ) {
												if ( isset( $all_active_plans[ $plan_id ] ) ) {
													$plan_data = $all_active_plans[ $plan_id ];
													$planObj   = new ARM_Plan_Lite( 0 );
													$planObj->init( (object) $plan_data );
													$plan_type = $planObj->type;
													$planText  = $planObj->setup_plan_text();
													if ( $planObj->exists() ) {
														/* Checked Plan Radio According Settings. */
														$plan_checked = $plan_checked_class = '';
														if ( ! empty( $selected_plan_id ) && $selected_plan_id != 0 && in_array( $selected_plan_id, $plans ) ) {
															if ( $selected_plan_id == $plan_id ) {
																$plan_checked_class = 'arm_active';
																$plan_checked       = 'checked="checked"';
																$selected_plan_data = $plan_data;
															}
														} else {
															if ( $i == 0 ) {
																$plan_checked_class = 'arm_active';
																$plan_checked       = 'checked="checked"';
																$selected_plan_data = $plan_data;
															}
														}
														/* Check Recurring Details */
														$plan_options = $planObj->options;

														if ( is_user_logged_in() ) {
															if ( $arm_user_old_plan == $plan_id ) {
																$arm_user_payment_cycles = ( isset( $arm_user_old_plan_options['payment_cycles'] ) && ! empty( $arm_user_old_plan_options['payment_cycles'] ) ) ? $arm_user_old_plan_options['payment_cycles'] : array();
																if ( empty( $arm_user_payment_cycles ) ) {
																	$plan_amount    = $planObj->amount;
																	$recurring_time = isset( $arm_user_old_plan_options['recurring']['time'] ) ? $arm_user_old_plan_options['recurring']['time'] : 'infinite';
																	$recurring_type = isset( $arm_user_old_plan_options['recurring']['type'] ) ? $arm_user_old_plan_options['recurring']['type'] : 'D';
																	switch ( $recurring_type ) {
																		case 'D':
																			$billing_cycle = isset( $arm_user_old_plan_options['recurring']['days'] ) ? $arm_user_old_plan_options['recurring']['days'] : '1';
																			break;
																		case 'M':
																			$billing_cycle = isset( $arm_user_old_plan_options['recurring']['months'] ) ? $arm_user_old_plan_options['recurring']['months'] : '1';
																			break;
																		case 'Y':
																			$billing_cycle = isset( $arm_user_old_plan_options['recurring']['years'] ) ? $arm_user_old_plan_options['recurring']['years'] : '1';
																			break;
																		default:
																			$billing_cycle = '1';
																			break;
																	}
																	$payment_cycles                  = array(
																		array(
																			'cycle_label' => $planObj->plan_text( false, false ),
																			'cycle_amount' => $plan_amount,
																			'billing_cycle' => $billing_cycle,
																			'billing_type' => $recurring_type,
																			'recurring_time' => $recurring_time,
																			'payment_cycle_order' => 1,
																		),
																	);
																	$plan_payment_cycles[ $plan_id ] = $payment_cycles;
																} else {

																	if ( ( $completed_recurrence == $total_recurring && $total_recurring != 'infinite' ) || ( $completed_recurrence == '' && $arm_user_selected_payment_mode == 'auto_debit_subscription' ) ) {

																		$arm_user_new_payment_cycles     = ( isset( $plan_options['payment_cycles'] ) && ! empty( $plan_options['payment_cycles'] ) ) ? $plan_options['payment_cycles'] : array();
																		$plan_payment_cycles[ $plan_id ] = $arm_user_new_payment_cycles;
																	} else {
																		$plan_payment_cycles[ $plan_id ] = $arm_user_payment_cycles;
																	}
																}
															} else {
																if ( $planObj->is_recurring() ) {
																	if ( ! empty( $plan_options['payment_cycles'] ) ) {
																		$plan_payment_cycles[ $plan_id ] = $plan_options['payment_cycles'];
																	} else {

																		$plan_amount    = $planObj->amount;
																		$recurring_time = isset( $plan_options['recurring']['time'] ) ? $plan_options['recurring']['time'] : 'infinite';
																		$recurring_type = isset( $plan_options['recurring']['type'] ) ? $plan_options['recurring']['type'] : 'D';
																		switch ( $recurring_type ) {
																			case 'D':
																				$billing_cycle = isset( $plan_options['recurring']['days'] ) ? $plan_options['recurring']['days'] : '1';
																				break;
																			case 'M':
																				$billing_cycle = isset( $plan_options['recurring']['months'] ) ? $plan_options['recurring']['months'] : '1';
																				break;
																			case 'Y':
																				$billing_cycle = isset( $plan_options['recurring']['years'] ) ? $plan_options['recurring']['years'] : '1';
																				break;
																			default:
																				$billing_cycle = '1';
																				break;
																		}
																		$payment_cycles                  = array(
																			array(
																				'cycle_label' => $planObj->plan_text( false, false ),
																				'cycle_amount' => $plan_amount,
																				'billing_cycle' => $billing_cycle,
																				'billing_type' => $recurring_type,
																				'recurring_time' => $recurring_time,
																				'payment_cycle_order' => 1,
																			),
																		);
																		$plan_payment_cycles[ $plan_id ] = $payment_cycles;
																	}
																}
															}
														} else {
															if ( $planObj->is_recurring() ) {
																if ( ! empty( $plan_options['payment_cycles'] ) ) {
																	$plan_payment_cycles[ $plan_id ] = $plan_options['payment_cycles'];
																} else {
																	$plan_amount    = $planObj->amount;
																	$recurring_time = isset( $plan_options['recurring']['time'] ) ? $plan_options['recurring']['time'] : 'infinite';
																	$recurring_type = isset( $plan_options['recurring']['type'] ) ? $plan_options['recurring']['type'] : 'D';
																	switch ( $recurring_type ) {
																		case 'D':
																			$billing_cycle = isset( $plan_options['recurring']['days'] ) ? $plan_options['recurring']['days'] : '1';
																			break;
																		case 'M':
																			$billing_cycle = isset( $plan_options['recurring']['months'] ) ? $plan_options['recurring']['months'] : '1';
																			break;
																		case 'Y':
																			$billing_cycle = isset( $plan_options['recurring']['years'] ) ? $plan_options['recurring']['years'] : '1';
																			break;
																		default:
																			$billing_cycle = '1';
																			break;
																	}
																	$payment_cycles                  = array(
																		array(
																			'cycle_label' => $planObj->plan_text( false, false ),
																			'cycle_amount' => $plan_amount,
																			'billing_cycle' => $billing_cycle,
																			'billing_type' => $recurring_type,
																			'recurring_time' => $recurring_time,
																			'payment_cycle_order' => 1,
																		),
																	);
																	$plan_payment_cycles[ $plan_id ] = $payment_cycles;
																}
															}
														}

														$payment_type = $planObj->payment_type;

														$is_trial     = '0';
														$trial_amount = $arm_payment_gateways->arm_amount_set_separator( $global_currency, 0 );

														if ( $planObj->is_recurring() ) {
															$stripePlans = ( isset( $modules['stripe_plans'] ) && ! empty( $modules['stripe_plans'] ) ) ? $modules['stripe_plans'] : array();

															if ( $planObj->has_trial_period() ) {
																$is_trial     = '1';
																$trial_amount = ! empty( $plan_options['trial']['amount'] ) ? $arm_payment_gateways->arm_amount_set_separator( $global_currency, $plan_options['trial']['amount'] ) : $trial_amount;
																if ( is_user_logged_in() ) {
																	if ( ! empty( $current_user_plan_ids ) ) {
																		if ( in_array( $planObj->ID, $current_user_plan_ids ) ) {
																			$is_trial = '0';
																		}
																	}
																}
															}
														}

														$allowed_payment_gateways_['paypal']        = '1';
														$allowed_payment_gateways_['stripe']        = '1';
														$allowed_payment_gateways_['bank_transfer'] = '1';
														$allowed_payment_gateways_['2checkout']     = '1';
														$allowed_payment_gateways_['authorize_net'] = '1';
														$allowed_payment_gateways_                  = apply_filters( 'arm_allowed_payment_gateways', $allowed_payment_gateways_, $planObj, $plan_options );
														$data_allowed_payment_gateways              = wp_json_encode( $allowed_payment_gateways_ );
														$arm_plan_amount                            = $arm_payment_gateways->arm_amount_set_separator( $global_currency, $planObj->amount );
														$arm_plan_amount                            = $planObj->amount = apply_filters( 'arm_modify_secondary_amount_outside', $arm_plan_amount, $plan_data );

														$planInputAttr = ' data-type="' . esc_attr($plan_type) . '" data-plan_name="' . esc_attr($planObj->name) . '" data-amt="' . esc_attr($arm_plan_amount) . '" data-recurring="' . esc_attr($payment_type) . '" data-is_trial="' . esc_attr($is_trial) . '" data-trial_amt="' . esc_attr($trial_amount) . '"  data-allowed_gateways=\'' . esc_attr($data_allowed_payment_gateways) . '\' data-plan_text="' . htmlentities( $planText ) . '"';

														$count_total_cycle = 0;
														if ( $planObj->is_recurring() ) {

															$count_total_cycle = count( $plan_payment_cycles[ $plan_id ] );
															$planInputAttr    .= '  " data-cycle="' . $count_total_cycle . '"';
														} else {
															$planInputAttr .= " data-cycle='0'";
														}

														$planInputAttr .= " data-tax='" . esc_attr($tax_percentage) . "'";

														if ( $setup_style['plan_skin'] == '' ) {
															$module_content .= '<li class="arm_plan_default_skin arm_setup_column_item ' . esc_attr($plan_checked_class) . '">';
															$module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . esc_attr($plan_id) . '">';
															$module_content .= '<span class="arm_setup_check_circle"><i class="armfa armfa-check"></i></span>';
															$module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . esc_attr($plan_id) . '" class="arm_module_plan_input" value="' . esc_attr($plan_id) . '" ' . $planInputAttr . ' ' . $plan_checked . ' required>';
															$module_content .= '<span class="arm_module_plan_name">' . esc_attr($planObj->name) . '</span>';
															$module_content .= '<div class="arm_module_plan_price_type"><span class="arm_module_plan_price">' . $planObj->plan_price( false ) . '</span></div>';
															$module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';
															$module_content .= '</label>';
															$module_content .= '</li>';
														} elseif ( $setup_style['plan_skin'] == 'skin6' ) {
															$module_content .= '<li class="arm_plan_skin6 arm_setup_column_item ' . esc_attr($plan_checked_class) . '">';
															$module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . esc_attr($plan_id) . '">';
															$module_content .= '<div class="arm_plan_skin6_left_box"><div class="arm_plan_name_box">';
															$module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . esc_attr($plan_id) . '" class="arm_module_plan_input" value="' . esc_attr($plan_id) . '" ' . $planInputAttr . ' ' . $plan_checked . ' required>';
															$module_content .= '<span class="arm_module_plan_name">' . esc_attr($planObj->name) . '</span></div>';
															if ( ! empty( $planObj->description ) ) {
																$module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';//phpcs:ignore
															}
															 $module_content .= '</div>';

															 $module_content .= '<div class="arm_plan_skin6_right_box"><span class="arm_module_plan_price">' . $planObj->plan_price( false ) . '</span></div>';
															$module_content  .= '</label>';
															$module_content  .= '</li>';
														} elseif ( $setup_style['plan_skin'] == 'skin1' ) {
															$module_content .= '<li class="arm_plan_skin1 arm_setup_column_item ' . esc_attr($plan_checked_class) . '">';
															$module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . esc_attr($plan_id) . '">';
															$module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . esc_attr($plan_id) . '" class="arm_module_plan_input" value="' . esc_attr($plan_id) . '" ' . $planInputAttr . ' ' . $plan_checked . ' required>';
															$module_content .= '<span class="arm_module_plan_name">' . $planObj->name . '</span>';
															$module_content .= '<div class="arm_module_plan_price_type"><span class="arm_module_plan_price">' . $planObj->plan_price( false ) . '</span></div>';
															$module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';
															/* $module_content .= $setup_info; */
															$module_content .= '</label>';
															$module_content .= '</li>';
														} elseif ( $setup_style['plan_skin'] == 'skin3' ) {
															$module_content .= '<li class="arm_plan_skin3 arm_setup_column_item ' . esc_attr($plan_checked_class) . '">';
															$module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . esc_attr($plan_id) . '">';
															$module_content .= '<div class="arm_plan_name_box"><span class="arm_setup_check_circle"><i class="armfa armfa-check"></i></span>';
															$module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . esc_attr($plan_id) . '" class="arm_module_plan_input" value="' . esc_attr($plan_id) . '" ' . $planInputAttr . ' ' . $plan_checked . ' required>';
															$module_content .= '<span class="arm_module_plan_name">' . $planObj->name . '</span></div>';
															$module_content .= '<div class="arm_module_plan_price_type"><span class="arm_module_plan_price">' . $planObj->plan_price( false ). '</span></div>';
															$module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';//phpcs:ignore
															/* $module_content .= $setup_info; */
															$module_content .= '</label>';
															$module_content .= '</li>';
														} else {
															$module_content .= '<li class="arm_plan_skin2 arm_setup_column_item ' . esc_attr($plan_checked_class) . '">';
															$module_content .= '<label class="arm_module_plan_option" id="arm_subscription_plan_option_' . esc_attr($plan_id) . '">';

															$module_content .= '<input type="radio" name="subscription_plan" data-id="subscription_plan_' . esc_attr($plan_id) . '" class="arm_module_plan_input" value="' . esc_attr($plan_id) . '" ' . $planInputAttr . ' ' . $plan_checked . ' required>';
															$module_content .= '<span class="arm_module_plan_name">' . esc_html($planObj->name) . '</span>';
															$module_content .= '<div class="arm_module_plan_price_type"><span class="arm_module_plan_price">' . $planObj->plan_price( false ) . '</span></div>';
															$module_content .= '<div class="arm_module_plan_description">' . $planObj->description . '</div>';//phpcs:ignore
															/* $module_content .= $setup_info; */
															$module_content .= '</label>';
															$module_content .= '</li>';
														}
														$i++;
													}
												}
											}
											$module_content .= '</ul>';
										}
										$module_content .= '</div></div>';
										$module_content  = apply_filters( 'arm_after_setup_plan_section', $module_content, $setupID, $setup_data );
										$module_content .= '<div class="armclear"></div>';
										$module_content .= '<input type="hidden" data-id="arm_form_plan_type" name="arm_plan_type" value="' . ( ( ! empty( $selected_plan_data['arm_subscription_plan_type'] ) && $selected_plan_data['arm_subscription_plan_type'] == 'free' ) ? 'free' : 'paid' ) . '">';
									}
								}

								break;
							case 'forms':
								if ( ! empty( $modules['forms'] ) && $modules['forms'] != 0 ) {
									$form_id = $modules['forms'];
									if ( ! empty( $form_settings ) ) {
										$form_style_class  = 'arm_form_' . $modules['forms'];
										$form_style_class .= ' arm_form_layout_' . $form_settings['style']['form_layout'] . ' arm-default-form';

										if ( $form_settings['style']['form_layout'] == 'writer' ) {
											$form_style_class .= ' arm-material-style arm_materialize_form ';
										} elseif ( $form_settings['style']['form_layout'] == 'rounded' ) {
											$form_style_class .= ' arm-rounded-style ';
										} elseif ( $form_settings['style']['form_layout'] == 'writer_border' ) {
											$form_style_class .= ' arm--material-outline-style arm_materialize_form ';
										}
										if ( ! empty( $form_settings['style']['validation_type'] ) && $form_settings['style']['validation_type'] == 'standard' ) {
											$form_style_class .= ' arm_standard_validation_type ';
										}
										$form_style_class .= ( $form_settings['style']['label_hide'] == '1' ) ? ' armf_label_placeholder' : '';
										$form_style_class .= ' armf_alignment_' . $form_settings['style']['label_align'];
										$form_style_class .= ' armf_layout_' . $form_settings['style']['label_position'];
										$form_style_class .= ' armf_button_position_' . $form_settings['style']['button_position'];
										$form_style_class .= ( $form_settings['style']['rtl'] == '1' ) ? ' arm_form_rtl' : ' arm_form_ltr';
										$errPosCCField     = ( ! empty( $form_settings['style']['validation_position'] ) && isset( $form_settings['style']['validation_type'] ) && $form_settings['style']['validation_type'] != 'standard' ) ? $form_settings['style']['validation_position'] : 'bottom';
										$buttonStyle       = ( isset( $form_settings['style']['button_style'] ) && ! empty( $form_settings['style']['button_style'] ) ) ? $form_settings['style']['button_style'] : 'flat';
										$btn_style_class   = ' --arm-is-' . $buttonStyle . '-style';

										$fieldPosition = ! empty( $form_settings['style']['field_position'] ) ? $form_settings['style']['field_position'] : 'left';
									}

									if ( $two_step ) {
										$module_content .= '<div class="arm_setup_submit_btn_wrapper ' . esc_attr($form_style_class) . ' arm_setup_two_step_next_wrapper" ' . $is_hide_class . '>';
										$module_content .= '<div class="arm-df__form-group arm-df__form-group_submit">';

										$module_content .= '<div class="arm-df__form-field">';
										$module_content .= '<div class="arm-df__form-field-wrap_submit arm-df__form-field-wrap" id="arm_setup_form_input_container' . esc_attr($setupID) . '">';

										$module_content .= '<button type="button" class="arm-df__form-control-submit-btn arm_material_input ' . esc_attr($btn_style_class) . '" data-id="arm_setup_two_step_next">' . html_entity_decode( stripslashes( $next_button_label ) ) . '</button>';
										$module_content .= '</div>';
										$module_content .= '</div>';
										$module_content .= '</div>';
										$module_content .= '</div>';

										$module_content .= '<div class="arm_setup_submit_btn_wrapper ' . esc_attr($form_style_class) . ' arm_setup_two_step_previous_wrapper arm_hide" ' . $is_hide_class . '>';
										$module_content .= '<div class="arm-df__form-group arm-df__form-group_submit">';

										$module_content .= '<div class="arm-df__form-field">';
										$module_content .= '<div class="arm-df__form-field-wrap_submit arm-df__form-field-wrap" id="arm_setup_form_input_container' . esc_attr($setupID) . '">';

										$module_content .= '<button type="button" class="arm-df__form-control-submit-btn arm_material_input ' . $btn_style_class . '" data-id="arm_setup_two_step_previous">' . html_entity_decode( stripslashes( $previuos_button_label ) ) . '</button>';
										$module_content .= '</div>';
										$module_content .= '</div>';
										$module_content .= '</div>';
										$module_content .= '</div>';
									}

									if ( is_user_logged_in() && ! $isPreview ) {
										$form              = new ARM_Form_Lite( 'id', $modules['forms'] );
										$ref_template      = $form->form_detail['arm_ref_template'];
										$form_css          = $arm_member_forms->arm_ajax_generate_form_styles( $modules['forms'], $form_settings, array(), $ref_template );
										$formStyle        .= $form_css['arm_css'];
										$modules['forms']  = 0;
										$setupGoogleFonts .= $form_css['arm_link'];

										$module_content = apply_filters( 'arm_before_setup_reg_form_section', $module_content, $setupID, $setup_data, $setupRandomID );

									} else {
										$formAttr = '';
										if ( $isPreview ) {
											$formAttr = 'preview="true"';
										}

										$module_content  = apply_filters( 'arm_before_setup_reg_form_section', $module_content, $setupID, $setup_data, $setupRandomID );
										$module_content .= '<div class="arm_module_forms_main_container' . esc_attr($arm_two_step_class) . '"><div class="arm_module_forms_container arm_module_box">';
										$module_content .= do_shortcode( '[arm_form id="' . $modules['forms'] . '" setup="true" form_position="' . $formPosition . '" ' . $formAttr . ' setup_form_id="' . $setupRandomID . '"]' );
										$module_content .= '</div>';
										$module_content  = apply_filters( 'arm_after_setup_reg_form_section', $module_content, $setupID, $setup_data );
										$module_content .= '<div class="armclear"></div></div>';
									}
								} else {
									if ( ! $isPreview ) {
										/* Hide Setup Form for non-logged in users when there is no form configured */
										return '';
									}
								}
								break;
							case 'note':
								if ( isset( $setup_modules['note'] ) && ! empty( $setup_modules['note'] ) ) {
									$module_content .= '<div class="arm_module_note_main_container' . esc_attr($arm_two_step_class) . '"><div class="arm_module_note_container arm_module_box">';
									$module_content .= apply_filters( 'the_content', stripslashes( $setup_modules['note'] ) );
									$module_content .= '</div></div>';
								}
								break;
							case 'payment_cycle':
								$form_layout = '';
								if ( ! empty( $form_settings ) ) {
									$form_layout = ' arm_form_layout_' . $form_settings['style']['form_layout'] . ' arm-default-form';
									if ( $form_settings['style']['form_layout'] == 'writer' ) {
										$form_layout .= ' arm-material-style arm_materialize_form ';
									} elseif ( $form_settings['style']['form_layout'] == 'rounded' ) {
										$form_layout .= ' arm-rounded-style ';
									} elseif ( $form_settings['style']['form_layout'] == 'writer_border' ) {
										$form_layout .= ' arm--material-outline-style arm_materialize_form ';
									}
									if ( ! empty( $form_settings['style']['validation_type'] ) && $form_settings['style']['validation_type'] == 'standard' ) {
										$form_layout .= ' arm_standard_validation_type ';
									}
								}
								$payment_mode = 'both';

								$is_hide_class = '';
								if ( $isHidePlans == true || $is_hide_plan_selection_area == true ) {
									$is_hide_class = 'style="display:none;"';
								}
								$module_content .= '<div class="arm_setup_paymentcyclebox_main_wrapper" ' . $is_hide_class . '><div class="arm_setup_paymentcyclebox_wrapper arm_hide">';

								if ( ! empty( $plan_payment_cycles ) ) {

									foreach ( $plan_payment_cycles as $payment_cycle_plan_id => $plan_payment_cycle_data ) {

										$arm_user_selected_payment_cycle = 0;

										if ( $selected_plan_id == $payment_cycle_plan_id ) {
											$arm_user_selected_payment_cycle = $selected_payment_duration - 1;
										}

										if ( in_array( $payment_cycle_plan_id, $current_user_plan_ids ) ) {
											$current_plan_data = get_user_meta( $current_user_id, 'arm_user_plan_' . $payment_cycle_plan_id, true );

											$arm_user_selected_payment_cycle = ( isset( $current_plan_data['arm_payment_cycle'] ) && ! empty( $current_plan_data['arm_payment_cycle'] ) ) ? $current_plan_data['arm_payment_cycle'] : 0;
										}

										$payment_plan_cycle_title = ( isset( $setup_data['setup_labels']['payment_cycle_field_title'] ) && ! empty( $setup_data['setup_labels']['payment_cycle_section_title'] ) ) ? $setup_data['setup_labels']['payment_cycle_field_title'] : esc_html__( 'Select Your Payment Cycle', 'armember-membership' );
										if ( ! empty( $plan_payment_cycle_data ) ) {
											$module_content .= '<div class="arm_module_payment_cycle_container arm_module_box arm_payment_cycle_box_' . esc_attr($payment_cycle_plan_id) . ' arm_form_' . esc_attr($setup_modules['modules']['forms']) . ' ' . esc_attr($form_layout) . ' arm_hide">';
											if ( isset( $setup_data['setup_labels']['payment_cycle_section_title'] ) && ! empty( $setup_data['setup_labels']['payment_cycle_section_title'] ) ) {
												$module_content .= '<div class="arm_setup_section_title_wrapper arm_setup_payment_cycle_title_wrapper arm_hide" style="text-align:' . $formPosition . ';">' . stripslashes_deep( $setup_data['setup_labels']['payment_cycle_section_title'] ) . '</div>';
											} else {
												$module_content .= '<div class="arm_setup_section_title_wrapper arm_setup_payment_cycle_title_wrapper arm_hide" style="text-align:' . $formPosition . ';">' . esc_html__( 'Select Payment Cycle', 'armember-membership' ) . '</div>';
											}
											$column_type = ( ! empty( $setup_modules['cycle_columns'] ) ) ? $setup_modules['cycle_columns'] : '1';

											if ( is_array( $plan_payment_cycle_data ) ) {
												if ( count( $plan_payment_cycle_data ) <= $arm_user_selected_payment_cycle ) {
													$arm_user_selected_payment_cycle_no = 0;
												} else {
													$arm_user_selected_payment_cycle_no = $arm_user_selected_payment_cycle;
												}

												$module_content .= '<input type="hidden" name="arm_payment_cycle_plan_' . esc_attr($payment_cycle_plan_id) . '" data-id="arm_payment_cycle_plan_' . esc_attr($payment_cycle_plan_id) . '" value="' . esc_attr($arm_user_selected_payment_cycle_no) . '">';
											}

											if ( $setup_style['plan_skin'] == 'skin5' ) {
												if ( is_array( $plan_payment_cycle_data ) ) {
													$dropdown_class            = 'arm-df__form-field-wrap_plan_cycles';
													$arm_allow_notched_outline = 0;
													if ( $form_settings['style']['form_layout'] == 'writer_border' ) {
														$arm_allow_notched_outline = 1;
														$inputPlaceholder          = '';
													}

													$arm_field_wrap_active_class = $ffield_label_html = $ffield_label = '';
													if ( ! empty( $arm_allow_notched_outline ) ) {
														$arm_field_wrap_active_class = ' arm-df__form-material-field-wrap';

														$ffield_label_html  = '<div class="arm-notched-outline">';
														$ffield_label_html .= '<div class="arm-notched-outline__leading"></div>';
														$ffield_label_html .= '<div class="arm-notched-outline__notch">';

														$ffield_label_html .= '<label class="arm-df__label-text active arm_material_label">' . esc_html($payment_plan_cycle_title) . '</label>';

														$ffield_label_html .= '</div>';
														$ffield_label_html .= '<div class="arm-notched-outline__trailing"></div>';
														$ffield_label_html .= '</div>';

														$ffield_label = $ffield_label_html;
													} else {
														$class_label = '';
														if ( $form_settings['style']['form_layout'] == 'writer' ) {
															$class_label = 'arm-df__label-text';

															$ffield_label = '<label class="' . esc_attr($class_label) . ' active">' . esc_html($payment_plan_cycle_title) . '</label>';
														}
													}
													$paymentSkinFloat = 'float:none;';
													switch ( $formPosition ) {
														case 'left':
															$paymentSkinFloat = '';
															break;
														case 'right':
															$paymentSkinFloat = 'float:right;';
															break;
													}
													$module_content .= '<div class="arm-control-group arm-df__form-group arm-df__form-group_plan_cycle">';
													$module_content .= '<div class="arm_label_input_separator"></div><div class="arm-df__form-field">';
													$module_content .= '<div class="arm-df__form-field-wrap ' . esc_attr($dropdown_class) . ' arm-controls arm_container payment_gateway_dropdown_skin1 ' . esc_attr($arm_field_wrap_active_class) . '" style="' . $paymentSkinFloat . '">';
													$module_content .= '<dl class="arm-df__dropdown-control column_level_dd">';

													// $module_content .= '<select name="payment_cycle_' . $payment_cycle_plan_id . '" class="arm_module_cycle_input select_skin"  onchange="armPaymentCycleChange('.$payment_cycle_plan_id.', \'arm_setup_form' . $setupRandomID . '\')">';

													$i                      = 0;
													$module_content_options = $pc_checked_label = $pc_checked_cycle_val = '';
													foreach ( $plan_payment_cycle_data as $arm_cycle_data_key => $arm_cycle_data ) {

														$pc_checked = $pc_checked_class = '';

														$arm_paymentg_cycle_label = ( isset( $arm_cycle_data['cycle_label'] ) ) ? $arm_cycle_data['cycle_label'] : '';

														if ( $i == $arm_user_selected_payment_cycle_no ) {
															$pc_checked           = 'selected="selected""';
															$pc_checked_class     = 'arm_active';
															$pc_checked_label     = $arm_paymentg_cycle_label;
															$pc_checked_cycle_val = $arm_user_selected_payment_cycle_no;
														}

														$arm_paymentg_cycle_amount = ( isset( $arm_cycle_data['cycle_amount'] ) ) ? $arm_payment_gateways->arm_amount_set_separator( $global_currency, $arm_cycle_data['cycle_amount'] ) : 0;
														$arm_paymentg_cycle_amount = apply_filters( 'arm_modify_secondary_amount_outside', $arm_paymentg_cycle_amount, $arm_cycle_data );

														// $module_content .='<option value="' . $arm_cycle_data_key  . '" class="armMDOption armSelectOption' . $setup_modules['modules']['forms'] . '" ' . $pc_checked . ' data-cycle_type="recurring" data-plan_id="' . $payment_cycle_plan_id . '" data-plan_amount = "' . $arm_paymentg_cycle_amount . '" '.$planCycleInputAttr.' '.$planCycleInputAttr.' data-cycle_label = "'. $arm_paymentg_cycle_label .'">' . $arm_paymentg_cycle_label . '</option>';

														$planCycleInputAttr      = " data-tax='" . esc_attr($tax_percentage) . "'";
														$module_content_options .= '<li class="arm__dc--item armMDOption armSelectOption' . esc_attr($setup_modules['modules']['forms']) . '" data-label="' . esc_attr($arm_paymentg_cycle_label) . '" data-value="' . esc_attr($arm_cycle_data_key) . '" data-cycle_type="recurring" data-plan_id="' . esc_attr($payment_cycle_plan_id) . '" data-plan_amount = "' . esc_attr($arm_paymentg_cycle_amount) . '" ' . $planCycleInputAttr . ' data-cycle_label = "' . esc_attr($arm_paymentg_cycle_label) . '">' . esc_html($arm_paymentg_cycle_label) . '</li>';

														$i++;
													}

													$module_content .= '<dt class="arm__dc--head">
                                                                        <span class="arm__dc--head__title">' . esc_html($pc_checked_label) . '</span><input type="text" style="display:none;" value="" class="arm-df__dc--head__autocomplete arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>';
													$module_content .= '</dt>';
													$module_content .= '<dd class="arm__dc--items-wrap">';
													$module_content .= '<ul class="arm__dc--items" data-id="arm_payment_cycle_' . esc_attr($payment_cycle_plan_id) . '_' . esc_attr($setupRandomID) . '" style="display:none;">';

													$module_content .= $module_content_options;
													$module_content .= '</ul>';
													$module_content .= '</dd>';
													$module_content .= '</dl>';
													$module_content .= $ffield_label;
													$module_content .= '<input type="hidden" id="arm_payment_cycle_' . esc_attr($payment_cycle_plan_id) . '_' . esc_attr($setupRandomID) . '" name="payment_cycle_' . esc_attr($payment_cycle_plan_id) . '" class="arm_module_cycle_input select_skin" onchange="armPaymentCycleChange(' . esc_attr($payment_cycle_plan_id) . ', \'arm_setup_form' . esc_attr($setupRandomID) . '\')" value="' . esc_attr($pc_checked_cycle_val) . '" />';

													// $module_content .= '</select>';
													$module_content .= '</div></div></div>';

												}
											} else {
												$module_content .= '<ul class="arm_module_payment_cycle_ul arm_column_' . esc_attr($column_type) . '" style="text-align:' . $formPosition . ';">';
												$i               = 0;

												if ( is_array( $plan_payment_cycle_data ) ) {

													foreach ( $plan_payment_cycle_data as $arm_cycle_data_key => $arm_cycle_data ) {

														$pc_checked = $pc_checked_class = '';
														if ( $i == $arm_user_selected_payment_cycle_no ) {
															$pc_checked       = 'checked="checked"';
															$pc_checked_class = 'arm_active';
														}

														$arm_paymentg_cycle_amount = ( isset( $arm_cycle_data['cycle_amount'] ) ) ? $arm_payment_gateways->arm_amount_set_separator( $global_currency, $arm_cycle_data['cycle_amount'] ) : 0;

														$arm_paymentg_cycle_amount = apply_filters( 'arm_modify_secondary_amount_outside', $arm_paymentg_cycle_amount, $arm_cycle_data );

														$arm_paymentg_cycle_label = ( isset( $arm_cycle_data['cycle_label'] ) ) ? stripslashes($arm_cycle_data['cycle_label']) : '';

														$planCycleInputAttr = " data-tax='" . esc_attr($tax_percentage) . "'";

														$pc_content  = '<label class="arm_module_payment_cycle_option">';
														$pc_content .= '<span class="arm_setup_check_circle"><i class="armfa armfa-check"></i></span>';
														$pc_content .= '<input type="radio" name="payment_cycle_' . esc_attr($payment_cycle_plan_id) . '" class="arm_module_cycle_input" value="' . esc_attr( $arm_cycle_data_key ) . '" ' . $pc_checked . '  data-cycle_type="recurring" data-plan_id="' . esc_attr($payment_cycle_plan_id) . '" data-plan_amount = "' . esc_attr($arm_paymentg_cycle_amount) . '" ' . $planCycleInputAttr . '>';

														$pc_content .= '<div class="arm_module_payment_cycle_name"><span class="arm_module_payment_cycle_span">' . esc_attr($arm_paymentg_cycle_label) . '</span></div>';
														$pc_content .= '</label>';

														$module_content .= '<li class="arm_setup_column_item arm_payment_cycle_' . esc_attr( $arm_cycle_data_key ) . ' ' . $pc_checked_class . '"  data-plan_id="' . esc_attr($payment_cycle_plan_id) . '">';
														$module_content .= $pc_content;
														$module_content .= '</li>';
														$i++;
													}
												}
												$module_content .= '</ul>';
											}
											$module_content .= '</div>';
										}
									}
								}
								$module_content .= '</div></div>';
								$module_content  = apply_filters( 'arm_after_setup_payment_cycle_section', $module_content, $setupID, $setup_data );

								break;
							case 'gateways':
								$form_layout = '';

								if ( ! empty( $form_settings ) ) {

									$form_layout = ' arm_form_layout_' . $form_settings['style']['form_layout'] . ' arm-default-form';
									if ( $form_settings['style']['form_layout'] == 'writer' ) {
										$form_layout .= ' arm-material-style arm_materialize_form ';
									} elseif ( $form_settings['style']['form_layout'] == 'rounded' ) {
										$form_layout .= ' arm-rounded-style ';
									} elseif ( $form_settings['style']['form_layout'] == 'writer_border' ) {
										$form_layout .= ' arm--material-outline-style arm_materialize_form ';
									}
									if ( ! empty( $form_settings['style']['validation_type'] ) && $form_settings['style']['validation_type'] == 'standard' ) {
										$form_layout .= ' arm_standard_validation_type ';
									}
								}

								$payment_mode = 'both';
								if ( ! empty( $modules['gateways'] ) ) {
									$payment_gateway_skin = ( isset( $setup_style['gateway_skin'] ) && $setup_style['gateway_skin'] != '' ) ? $setup_style['gateway_skin'] : 'radio';
									$gatewayOrders        = array();
									$gatewayOrders        = ( isset( $modules['gateways_order'] ) && ! empty( $modules['gateways_order'] ) ) ? $modules['gateways_order'] : array();
									if ( ! empty( $gatewayOrders ) ) {
										asort( $gatewayOrders );
									}
									$form_position = ( ! empty( $setup_style['form_position'] ) ) ? $setup_style['form_position'] : 'left';

									$payment_gateway_title = ( isset( $setup_data['setup_labels']['payment_gateway_field_title'] ) && ! empty( $setup_data['setup_labels']['payment_gateway_field_title'] ) ) ? $setup_data['setup_labels']['payment_gateway_field_title'] : esc_html__( 'Select Your Payment Gateway', 'armember-membership' );

									$gateways = $this->armSortModuleOrders( $modules['gateways'], $gatewayOrders );
									if ( ! empty( $gateways ) ) {
										$active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
										$is_display_pg   = ( ! empty( $selected_plan_data['arm_subscription_plan_type'] ) && $selected_plan_data['arm_subscription_plan_type'] == 'free' ) ? 'display:none;' : '';
										$module_content .= '<div class="arm_setup_gatewaybox_main_wrapper' . esc_attr($arm_two_step_class) . '"><div class="arm_setup_gatewaybox_wrapper" style="' . $is_display_pg . '">';
										if ( isset( $setup_data['setup_labels']['payment_section_title'] ) && ! empty( $setup_data['setup_labels']['payment_section_title'] ) ) {
											$module_content .= '<div class="arm_setup_section_title_wrapper" style="text-align:' . $formPosition . ';">' . stripslashes_deep( $setup_data['setup_labels']['payment_section_title'] ) . '</div>';
										}
										$module_content .= '<input type="hidden" name="arm_front_gateway_skin_type" data-id="arm_front_gateway_skin_type" value="' . esc_attr($payment_gateway_skin) . '">';
										$module_content .= '<div class="arm_module_gateways_container arm_module_box arm_form_' . esc_attr($setup_modules['modules']['forms']) . ' ' . esc_attr($form_layout) . '">';

										$column_type = ( ! empty( $setup_modules['gateways_columns'] ) ) ? $setup_modules['gateways_columns'] : '1';

										$doNotDisplayPaymentMode = array( 'bank_transfer' );
										$doNotDisplayPaymentMode = apply_filters( 'arm_not_display_payment_mode_setup', $doNotDisplayPaymentMode );

										$pglabels = isset( $setup_data['arm_setup_labels']['payment_gateway_labels'] ) ? $setup_data['arm_setup_labels']['payment_gateway_labels'] : array();

										if ( $payment_gateway_skin == 'radio' ) {

											$module_content .= '<ul class="arm_module_gateways_ul arm_column_' . esc_attr($column_type) . '" style="text-align:' . $formPosition . ';">';
											$i               = 0;
											$pg_fields       = $selectedKey = '';

											foreach ( $gateways as $pg ) {
												if ( in_array( $pg, array_keys( $active_gateways ) ) ) {
													if ( isset( $selected_plan_data['arm_subscription_plan_options']['trial']['is_trial_period'] ) && $pg == 'stripe' && $selected_plan_data['arm_subscription_plan_options']['payment_type'] == 'subscription' ) {

														if ( $selected_plan_data['arm_subscription_plan_options']['trial']['amount'] > 0 ) {
															// continue;
														}
													}
													if ( ! in_array( $pg, $doNotDisplayPaymentMode ) ) {
														$payment_mode = $modules['payment_mode'][ $pg ];
													} else {
														$payment_mode = 'manual_subscription';
													}

													$pg_options    = $active_gateways[ $pg ];
													$pg_checked    = $pg_checked_class = '';
													$display_block = 'arm_hide';
													if ( $i == 0 ) {
														$pg_checked       = 'checked="checked"';
														$pg_checked_class = 'arm_active';
														$display_block    = '';
														$selectedKey      = $pg;
													}
													$pg_content  = '<label class="arm_module_gateway_option">';
													$pg_content .= '<span class="arm_setup_check_circle"><i class="armfa armfa-check"></i></span>';
													$pg_content .= '<input type="radio" name="payment_gateway" class="arm_module_gateway_input" value="' . esc_attr($pg) . '" ' . $pg_checked . ' data-payment_mode="' . esc_attr($payment_mode) . '" >';
													if ( ! empty( $pglabels ) ) {
														if ( isset( $pglabels[ $pg ] ) ) {
															$pg_options['gateway_name'] = $pglabels[ $pg ];
														}
													}

													$pg_content .= '<div class="arm_module_gateway_name"><span class="arm_module_gateway_span">' . stripslashes_deep( $pg_options['gateway_name'] ) . '</span></div>';
													$pg_content .= '</label>';
													switch ( $pg ) {
														case 'paypal':
															break;
														case 'bank_transfer':
															if ( isset( $pg_options['note'] ) && ! empty( $pg_options['note'] ) ) {
																$pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_bank_transfer ' . esc_attr($display_block) . ' arm-form-container"><div class="arm_bank_transfer_note_container">' . stripslashes( nl2br( $pg_options['note'] ) ) . '</div></div>';
															}
															$bt_fields = isset( $pg_options['fields'] ) ? $pg_options['fields'] : array();
															if ( isset( $bt_fields['transaction_id'] ) || isset( $bt_fields['bank_name'] ) || isset( $bt_fields['account_name'] ) || isset( $bt_fields['additional_info'] ) || isset( $bt_fields['transfer_mode'] ) || isset($pg_options['note']) ) {
																$pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_bank_transfer ' . esc_attr($display_block) . ' arm-form-container">';
																if ( isset( $bt_fields['transaction_id'] ) || isset( $bt_fields['bank_name'] ) || isset( $bt_fields['account_name'] ) || isset( $bt_fields['additional_info'] ) || isset( $bt_fields['transfer_mode'] ) ) {
																	$pg_fields .= '<div class="' . esc_attr($form_style_class) . '">';
																	$pg_fields .= $arm_payment_gateways->arm_get_bank_transfer_form( $pg_options, $fieldPosition, $errPosCCField, $setup_modules['modules']['forms'], $form_settings );
																	$pg_fields .= '</div>';
																}
																$pg_fields .= '</div>';
															}
															break;
														default:
															$gateway_fields = apply_filters( 'arm_membership_setup_gateway_option', '', $pg, $pg_options );
															
															if ( ! empty( $gateway_fields ) ) {
																$pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_' . esc_attr($pg) . ' ' . esc_attr($display_block) . ' arm-form-container">';
																$pg_fields .= '<div class="' . esc_attr($form_style_class) . '">';
																$pg_fields .= $gateway_fields;
																$pg_fields .= '</div>';
																$pg_fields .= '</div>';
															}
															break;
													}
													$module_content .= '<li class="arm_setup_column_item arm_gateway_' . esc_attr($pg) . ' ' . $pg_checked_class . '">';
													$module_content .= $pg_content;
													$module_content .= '</li>';
													$i++;
													$module_content .= "<input type='hidden' name='arm_payment_mode[".esc_attr($pg)."]'  value='".esc_attr($payment_mode)."' />";
												}
											}
											$module_content .= '</ul>';
										} else {
											$arm_allow_notched_outline = 0;
											if ( $form_settings['style']['form_layout'] == 'writer_border' ) {
												$arm_allow_notched_outline = 1;
												$inputPlaceholder          = '';
											}

												$arm_field_wrap_active_class = $ffield_label_html = $ffield_label = '';
											if ( ! empty( $arm_allow_notched_outline ) ) {
												$arm_field_wrap_active_class = ' arm-df__form-material-field-wrap';

												$ffield_label_html  = '<div class="arm-notched-outline">';
												$ffield_label_html .= '<div class="arm-notched-outline__leading"></div>';
												$ffield_label_html .= '<div class="arm-notched-outline__notch">';

												$ffield_label_html .= '<label class="arm-df__label-text active arm_material_label">' . esc_attr($payment_gateway_title) . '</label>';

												$ffield_label_html .= '</div>';
												$ffield_label_html .= '<div class="arm-notched-outline__trailing"></div>';
												$ffield_label_html .= '</div>';

												$ffield_label = $ffield_label_html;
											} else {
												$class_label = '';
												if ( $form_settings['style']['form_layout'] == 'writer' ) {
													$class_label = 'arm-df__label-text';

													$ffield_label = '<label class="' . esc_attr($class_label) . ' active">' . esc_attr($payment_gateway_title) . '</label>';
												}
											}
											$paymentSkinFloat = 'float:none;';
											switch ( $formPosition ) {
												case 'left':
													$paymentSkinFloat = '';
													break;
												case 'right':
													$paymentSkinFloat = 'float:right;';
													break;
											}
											$module_content         .= '<div class="arm-control-group arm-df__form-group arm-df__form-group_select">';
													$module_content .= '<div class="arm_label_input_separator"></div><div class="arm-df__form-field">';
											$module_content         .= '<div class="arm-df__form-field-wrap arm-controls arm_container payment_gateway_dropdown_skin1 ' . esc_attr($arm_field_wrap_active_class) . '" style="' . $paymentSkinFloat . '">';

											$module_content .= '<dl class="arm-df__dropdown-control column_level_dd">';

											// $module_content .= '<select name="payment_gateway" class="arm_module_gateway_input select_skin"  aria-label="gateway" onchange="armPaymentGatewayChange(\'arm_setup_form' . $setupRandomID . '\')">';
											$i                      = 0;
											$module_content_options = $pg_fields = $selectedKey = $pg_options_gateway_name = '';
											foreach ( $gateways as $pg ) {
												if ( in_array( $pg, array_keys( $active_gateways ) ) ) {
													$payment_gateway_name = $pg;
													if ( isset( $selected_plan_data['arm_subscription_plan_options']['trial']['is_trial_period'] ) && $pg == 'stripe' && $selected_plan_data['arm_subscription_plan_options']['payment_type'] == 'subscription' ) {
														if ( $selected_plan_data['arm_subscription_plan_options']['trial']['amount'] > 0 ) {
															// continue;
														}
													}

													if ( ! in_array( $pg, $doNotDisplayPaymentMode ) ) {
														$payment_mode = $modules['payment_mode'][ $pg ];
													} else {
														$payment_mode = 'manual_subscription';
													}

													$pg_options    = $active_gateways[ $pg ];
													$pg_checked    = $pg_checked_class = '';
													$display_block = 'arm_hide';
													if ( $i == 0 ) {
														$pg_checked              = 'selected="selected"';
														$pg_checked_class        = 'arm_active';
														$display_block           = '';
														$selectedKey             = $pg;
														$pg_options_gateway_name = stripslashes_deep( $pglabels[ $pg ] );
													}

													switch ( $pg ) {
														case 'paypal':
															break;
														case 'stripe':
															$hide_cc_fields = apply_filters( 'arm_hide_cc_fields', false, $pg, $pg_options );
															if ( false == $hide_cc_fields ) {
																$pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_stripe ' . esc_attr($display_block) . ' arm-form-container">';
																$pg_fields .= '<div class="' . $form_style_class . '">';
																$pg_fields .= $arm_payment_gateways->arm_get_credit_card_box( 'stripe', $column_type, $fieldPosition, $errPosCCField, $form_settings['style']['form_layout'] );
																$pg_fields .= '</div>';
																$pg_fields .= '</div>';
															}
															break;
														case 'authorize_net':
															$pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_authorize_net ' . esc_attr($display_block) . ' arm-form-container">';
															$pg_fields .= '<div class="' . esc_attr($form_style_class) . '">';
															$pg_fields .= $arm_payment_gateways->arm_get_credit_card_box( 'authorize_net', $column_type, $fieldPosition, $errPosCCField, $form_settings['style']['form_layout'] );
															$pg_fields .= '</div>';
															$pg_fields .= '</div>';
															break;
														case '2checkout':
															break;
														case 'bank_transfer':
															if ( isset( $pg_options['note'] ) && ! empty( $pg_options['note'] ) ) {
																$pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_bank_transfer ' . esc_attr($display_block) . ' arm-form-container"><div class="arm_bank_transfer_note_container">' . stripslashes( nl2br( $pg_options['note'] ) ) . '</div></div>';
															}
															$bt_fields = isset( $pg_options['fields'] ) ? $pg_options['fields'] : array();
															if ( isset( $bt_fields['transaction_id'] ) || isset( $bt_fields['bank_name'] ) || isset( $bt_fields['account_name'] ) || isset( $bt_fields['additional_info'] ) || isset( $bt_fields['transfer_mode'] ) || isset($pg_options['note']) ) {
																$pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_bank_transfer ' . esc_attr($display_block) . ' arm-form-container">';
																if ( isset( $bt_fields['transaction_id'] ) || isset( $bt_fields['bank_name'] ) || isset( $bt_fields['account_name'] ) || isset( $bt_fields['additional_info'] ) || isset( $bt_fields['transfer_mode'] ) ) {
																	$pg_fields .= '<div class="' . esc_attr($form_style_class) . '">';
																	$pg_fields .= $arm_payment_gateways->arm_get_bank_transfer_form( $pg_options, $fieldPosition, $errPosCCField, $setup_modules['modules']['forms'], $form_settings );
																	$pg_fields .= '</div>';
																}
																$pg_fields .= '</div>';
															}
															break;
														default:
															$gateway_fields = apply_filters( 'arm_membership_setup_gateway_option', '', $pg, $pg_options );
															if ( ! empty( $gateway_fields ) ) {
																$pg_fields .= '<div class="arm_module_gateway_fields arm_module_gateway_fields_' . esc_attr($pg) . ' ' . esc_attr($display_block) . ' arm-form-container">';
																$pg_fields .= '<div class="' . esc_attr($form_style_class) . '">';
																$pg_fields .= $gateway_fields;
																$pg_fields .= '</div>';
																$pg_fields .= '</div>';
															}
															break;
													}

													if ( ! empty( $pglabels ) && isset( $pglabels[ $pg ] ) ) {
														$pg_options['gateway_name'] = stripslashes_deep( $pglabels[ $pg ] );
													}

													// $module_content .='<option value="' . $payment_gateway_name . '" class="armMDOption armSelectOption' . $setup_modules['modules']['forms'] . ' arm_gateway_' . $payment_gateway_name . '" ' . $pg_checked . ' data-payment_mode="' . $payment_mode . '">' . $pg_options['gateway_name'] . '</option>';

													$module_content_options .= '<li data-value="' . esc_attr($payment_gateway_name) . '" class="arm__dc--item armMDOption armSelectOption' . esc_attr($setup_modules['modules']['forms']) . ' arm_gateway_' . esc_attr($payment_gateway_name) . '" data-payment_mode="' . esc_attr($payment_mode) . '">' . esc_html($pg_options['gateway_name']) . '</li>';

													$i++;
													$module_content .= "<input type='hidden' name='arm_payment_mode[".esc_attr($pg)."]'  value='".esc_attr($payment_mode)."' />";
												}
											}

											$module_content         .= '<dt class="arm__dc--head">
                                                                        <span class="arm__dc--head__title">' . esc_html($pg_options_gateway_name) . '</span><input type="text" style="display:none;" value="" class="arm-df__dc--head__autocomplete arm_autocomplete"><i class="armfa armfa-caret-down armfa-lg"></i>';
												$module_content     .= '</dt>';
												$module_content     .= '<dd class="arm__dc--items-wrap">';
													$module_content .= '<ul class="arm__dc--items" data-id="arm_payment_gateway_' . esc_attr($setupRandomID) . '" style="display:none;">';

												$module_content .= $module_content_options;
												$module_content .= '</ul>';
												$module_content .= '</dd>';
												$module_content .= '<input type="hidden" id="arm_payment_gateway_' . $setupRandomID . '" name="payment_gateway" class="arm_module_gateway_input select_skin" aria-label="gateway" onchange="armPaymentGatewayChange(\'arm_setup_form' . esc_attr($setupRandomID) . '\')" value="' . esc_attr($selectedKey) . '" />';
												$module_content .= '</dl>';
												$module_content .= $ffield_label;
											// $module_content .= '</select>';
											$module_content .= '</div></div></div>';

										}

										$module_content .= '<div class="armclear"></div>';
										$module_content .= $pg_fields;
										$module_content .= '<div class="armclear"></div>';
										$module_content .= '</div>';
										$module_content  = apply_filters( 'arm_after_setup_gateway_section', $module_content, $setupID, $setup_data );
										$module_content .= '<div class="armclear"></div>';
										// $module_content .= '<script type="text/javascript" data-cfasync="false">armSetDefaultPaymentGateway(\'' . $selectedKey . '\');</script>';
										$module_content .= '</div></div>';
										/* Payment Mode Module */

										$arm_automatic_sub_label                              = ( isset( $setup_data['setup_labels']['automatic_subscription'] ) && ! empty( $setup_data['setup_labels']['automatic_subscription'] ) ) ? stripslashes_deep( $setup_data['setup_labels']['automatic_subscription'] ) : esc_html__( 'Auto Debit Payment', 'armember-membership' );
										$arm_semi_automatic_sub_label                         = ( isset( $setup_data['setup_labels']['semi_automatic_subscription'] ) && ! empty( $setup_data['setup_labels']['semi_automatic_subscription'] ) ) ? stripslashes_deep( $setup_data['setup_labels']['semi_automatic_subscription'] ) : esc_html__( 'Manual Payment', 'armember-membership' );
										$module_content                                      .= "<div class='arm_payment_mode_main_wrapper" . esc_attr($arm_two_step_class) . "'><div class='arm_payment_mode_wrapper' id='arm_payment_mode_wrapper' style='text-align:{$formPosition};'>";
										$setup_data['setup_labels']['payment_mode_selection'] = ( isset( $setup_data['setup_labels']['payment_mode_selection'] ) && ! empty( $setup_data['setup_labels']['payment_mode_selection'] ) ) ? $setup_data['setup_labels']['payment_mode_selection'] : esc_html__( 'How you want to pay?', 'armember-membership' );
										$module_content                                      .= "<div class='arm_setup_section_title_wrapper arm_payment_mode_selection_wrapper' >" . stripslashes_deep( $setup_data['setup_labels']['payment_mode_selection'] ) . '</div>';
										$module_content                                      .= "<div class='arm-df__form-field'>";
										$module_content                                      .= "<div class='arm-df__form-field-wrap_radio arm-df__form-field-wrap arm-d-flex arm-justify-content-" . esc_attr($form_position) . "'><div class='arm-df__radio arm-d-flex arm-align-items-" . esc_attr($form_position) . "'><input type='radio' checked='checked' name='arm_selected_payment_mode' value='auto_debit_subscription' class='arm_selected_payment_mode arm-df__form-control--is-radio' id='arm_selected_payment_mode_auto_".esc_attr($setupRandomID)."'/><label for='arm_selected_payment_mode_auto_".esc_attr($setupRandomID)."' class='arm_payment_mode_label arm-df__fc-radio--label'>" . esc_html($arm_automatic_sub_label) . '</label></div>';
										$module_content                                      .= "<div class='arm-df__radio arm-d-flex arm-align-items-" . esc_attr($form_position) . "'><input type='radio'  name='arm_selected_payment_mode' value='manual_subscription' class='arm_selected_payment_mode arm-df__form-control--is-radio' id='arm_selected_payment_mode_semi_auto_".esc_attr($setupRandomID)."'/><label for='arm_selected_payment_mode_semi_auto_".esc_attr($setupRandomID)."' class='arm_payment_mode_label arm-df__fc-radio--label'>" . esc_attr($arm_semi_automatic_sub_label) . '</label></div></div>';
										$module_content                                      .= '</div>';
										$module_content                                      .= '</div></div>';
									}
								}
								break;
							case 'order_detail':
								if ( ! empty( $modules['plans'] ) ) {
									$module_content = apply_filters( 'arm_after_setup_order_detail', $module_content, $setupID, $setup_data );
									if ( isset( $setup_data['setup_labels']['summary_text'] ) && ! empty( $setup_data['setup_labels']['summary_text'] ) ) {
										$setupSummaryText = stripslashes( $setup_data['setup_labels']['summary_text'] );
										$setupSummaryText = str_replace( '[PLAN_NAME]', '<span class="arm_plan_name_text"></span>', $setupSummaryText );
										$setupSummaryText = str_replace( '[PLAN_CYCLE_NAME]', '<span class="arm_plan_cycle_name_text"></span>', $setupSummaryText );
										$setupSummaryText = str_replace( '[PLAN_AMOUNT]', '<span class="arm_plan_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText );
										$setupSummaryText = str_replace( '[DISCOUNT_AMOUNT]', '<span class="arm_discount_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText );
										$setupSummaryText = str_replace( '[PAYABLE_AMOUNT]', '<span class="arm_payable_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText );
										$setupSummaryText = str_replace( '[TAX_AMOUNT]', '<span class="arm_tax_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText );
										$setupSummaryText = str_replace( '[TAX_PERCENTAGE]', '<span class="arm_tax_percentage_text">' . $tax_percentage . '</span>%', $setupSummaryText );
										$setupSummaryText = str_replace( '[TRIAL_AMOUNT]', '<span class="arm_trial_amount_text"></span> <span class="arm_order_currency"></span>', $setupSummaryText );
										$module_content  .= "<div class='arm_setup_summary_text_main_container" . esc_attr($arm_two_step_class) . "'><div class='arm_setup_summary_text_container arm_module_box' style='text-align:{$formPosition};'>";
										$module_content  .= '<input type="hidden" name="arm_total_payable_amount" data-id="arm_total_payable_amount" value=""/>';
										$module_content  .= '<input type="hidden" name="arm_zero_amount_discount" data-id="arm_zero_amount_discount" value="' . esc_attr($arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator( $global_currency ) ) . '"/>';

										$setupSummaryText = apply_filters( 'arm_summary_text_filter', $setupSummaryText );

										$module_content .= '<div class="arm_setup_summary_text">' . nl2br( $setupSummaryText ) . '</div>';
										$module_content .= '</div></div>';
									}
								}
								break;
							default:
								break;
						}
						$module_html .= $module_content;
					}

					$content  = apply_filters( 'arm_before_setup_form_content', $content, $setupID, $setup_data );
					$content .= '<div class="arm_setup_form_container">';
					$content .= '<style type="text/css" id="arm_setup_style_' . esc_attr($args['id']) . '">';
					if ( ! empty( $setup_style ) ) {
						$sfontFamily = isset( $setup_style['font_family'] ) ? $setup_style['font_family'] : '';
						$gFontUrl    = $arm_member_forms->arm_get_google_fonts_url( array( $sfontFamily ) );
						if ( ! empty( $gFontUrl ) ) {
							// $setupGoogleFonts .= '<link id="google-font-' . $setupID . '" rel="stylesheet" type="text/css" href="' . $gFontUrl . '" />';
							if ( ! empty( $args['preview'] ) && $args['preview'] == true ) {
								wp_register_style( 'google-font-' . $setupID, $gFontUrl, array(), MEMBERSHIPLITE_VERSION );
								wp_print_styles( 'google-font-' . $setupID );
							} else {
								wp_enqueue_style( 'google-font-' . $setupID, $gFontUrl, array(), MEMBERSHIPLITE_VERSION );
							}
						}
						$content .= $this->arm_generate_setup_style( $setupID, $setup_style );
					}
					if ( ! empty( $formStyle ) ) {
						$content .= $formStyle;
					}
					if ( ! empty( $custom_css ) ) {
						$content .= $custom_css;
					}
					$content .= '</style>';
					$content .= $setupGoogleFonts;
					$content .= '<div class="arm_setup_messages arm_form_message_container"></div>';

					$is_form_class_rtl = '';
					if ( is_rtl() ) {
						$is_form_class_rtl = 'is_form_class_rtl';
					}

					$form_attr        = '';
					$general_settings = isset( $arm_global_settings->global_settings ) ? $arm_global_settings->global_settings : array();
					$spam_protection  = isset( $general_settings['spam_protection'] ) ? $general_settings['spam_protection'] : '';
					if ( ! empty( $spam_protection ) ) {
						$captcha_code = arm_generate_captcha_code();
						if ( ! isset( $_SESSION['ARM_FILTER_INPUT'] ) ) {
							$_SESSION['ARM_FILTER_INPUT'] = array();
						}
						if ( isset( $_SESSION['ARM_FILTER_INPUT'][ $setupRandomID ] ) ) {
							unset( $_SESSION['ARM_FILTER_INPUT'][ $setupRandomID ] );
						}
						$_SESSION['ARM_FILTER_INPUT'][ $setupRandomID ] = $captcha_code;
						$_SESSION['ARM_VALIDATE_SCRIPT']                = true;
						$form_attr                                      = ' data-submission-key="' . $captcha_code . '" ';
					}
					if ( ! empty( $form_settings ) ) {
						$form_layout = ' arm_form_layout_' . $form_settings['style']['form_layout'] . ' arm-default-form';
						if ( $form_settings['style']['form_layout'] == 'writer' ) {
							$form_layout .= ' arm-material-style arm_materialize_form ';
						} elseif ( $form_settings['style']['form_layout'] == 'rounded' ) {
							$form_layout .= ' arm-rounded-style ';
						} elseif ( $form_settings['style']['form_layout'] == 'writer_border' ) {
							$form_layout .= ' arm--material-outline-style arm_materialize_form ';
						}
						if ( ! empty( $form_settings['style']['validation_type'] ) && $form_settings['style']['validation_type'] == 'standard' ) {
							$form_layout .= ' arm_standard_validation_type ';
						}
					}
					$content .= '<form method="post" name="arm_form" id="arm_setup_form' . esc_attr($setupRandomID) . '" class="arm_setup_form_' . esc_attr($setupID) . ' arm_membership_setup_form arm_form_' . esc_attr($form_id) . esc_attr($form_layout) . ' ' . esc_attr($is_form_class_rtl) . '" enctype="multipart/form-data" data-random-id="' . esc_attr($setupRandomID) . '" novalidate ' . $form_attr . '>';
					if ( $args['hide_title'] == false && $args['popup'] == false ) {
						$content .= '<h3 class="arm_setup_form_title">' . $setup_name . '</h3>';
					}
					$content .= '<input type="hidden" name="setup_id" value="' . esc_attr($setupID) . '" data-id="arm_setup_id"/>';
					$content .= '<input type="hidden" name="setup_action" value="membership_setup"/>';
					$content .= "<input type='text' name='arm_filter_input' data-random-key='".esc_attr($setupRandomID)."' value='' style='opacity:0 !important;display:none !important;visibility:hidden !important;' />";
					$content .= '<div class="arm_setup_form_inner_container">';
					$content .= '<input type="hidden" class="arm_global_currency" value="' . esc_attr($global_currency) . '"/>';
					// $currency_separators = $arm_payment_gateways->get_currency_separators_standard();
					$currency_separators = $arm_payment_gateways->get_currency_wise_separator( $global_currency );
					$currency_separators = ( ! empty( $currency_separators ) ) ? wp_json_encode( $currency_separators ) : '';

					$content .= "<input type='hidden' class='arm_global_currency_separators' value='" . esc_attr($currency_separators) . "'/>";

					/* tax values */
					if ( $enable_tax == 1 && ! empty( $tax_values ) ) {
						$content .= "<input type='hidden' name='arm_tax_type' value='" . esc_attr($tax_values['tax_type']) . "'/>";
						if ( $tax_values['tax_type'] == 'country_tax' ) {
							$content .= "<input type='hidden' name='arm_country_tax_field' value='" . esc_attr($tax_values['country_tax_field']) . "'/>";
							$content .= "<input type='hidden' name='arm_country_tax_field_opts' value='" . esc_attr($tax_values['country_tax_field_opts_json']) . "'/>";
							$content .= "<input type='hidden' name='arm_country_tax_amount' value='" . esc_attr($tax_values['country_tax_amount_json']) . "'/>";
							$content .= "<input type='hidden' name='arm_country_tax_default_val' value='" . esc_attr($tax_values['tax_percentage']) . "'/>";
						} else {
							$content .= "<input type='hidden' name='arm_common_tax_amount' value='" . esc_attr($tax_values['tax_percentage']) . "'/>";
						}
					}
					/* tax values over */

					$content .= $module_html;

					$content .= '<div class="arm_setup_submit_btn_main_wrapper' . esc_attr($arm_two_step_class) . '"><div class="arm_setup_submit_btn_wrapper ' . esc_attr($form_style_class) . '">';
					$content .= '<div class="arm-df__form-group arm-df__form-group_submit">';
					// $content .= '<div class="arm_label_input_separator"></div>';
					// $content .= '<div class="arm_form_label_wrapper arm-df__field-label arm_form_member_field_submit"></div>';
					$content .= '<div class="arm-df__form-field">';
					$content .= '<div class="arm-df__form-field-wrap_submit arm-df__form-field-wrap" id="arm_setup_form_input_container' . esc_attr($setupID) . '">';
					$ngClick  = 'onclick="armSubmitBtnClick(event)"';
					if ( current_user_can( 'administrator' ) ) {
						$ngClick = 'onclick="return false;"';
					}

					if(file_exists(ABSPATH . 'wp-admin/includes/file.php')){
						require_once(ABSPATH . 'wp-admin/includes/file.php');
					}

					WP_Filesystem();
					global $wp_filesystem;
					$arm_loader_url = MEMBERSHIPLITE_IMAGES_URL . "/loader.svg";
					$arm_loader_img = $wp_filesystem->get_contents($arm_loader_url);

					$content .= '<button type="submit" name="ARMSETUPSUBMIT" class="arm_setup_submit_btn arm-df__form-control-submit-btn arm-df__form-group_button arm_material_input ' . esc_attr($btn_style_class) . '" ' . $ngClick . '><span class="arm_spinner">' . $arm_loader_img . '</span>' . html_entity_decode( stripslashes( $submit_btn ) ) . '</button>';
					if ( is_user_logged_in() ) {
						$content .= do_shortcode( '[armember_spam_filters]' );
					}
					if ( current_user_can( 'administrator' ) ) {
						$arm_default_common_messages = $arm_global_settings->arm_default_common_messages();
						$content                    .= '<div class="arm_disabled_submission_container">';
							$content                .= '<div class="arm_setup_messages arm_form_message_container">
                                            <div class="arm_error_msg arm-df__fc--validation__wrap">
                                                <ul><li>' . esc_html($arm_default_common_messages['arm_disabled_submission']) . '</li></ul>
                                            </div>
                                        </div>';
						$content                    .= '</div>';
					}

					$content .= '</div>';
					$content .= '</div>';

					/*Add login link in signup form*/
					$login_link_label = ( isset( $form_settings['login_link_label'] ) ) ? stripslashes( $form_settings['login_link_label'] ) : esc_html__( 'Login', 'armember-membership' );

					$show_login_link = ( isset( $form_settings['show_login_link'] ) ) ? $form_settings['show_login_link'] : 0;

					if ( $show_login_link == '1' && ! is_user_logged_in() ) {
						$content .= '<div class="arm_reg_links_wrapper arm_reg_options arm_reg_login_links">';
						global $arm_login_form_popup_ids_arr, $arm_member_forms;

						if ( isset( $form_settings['login_link_type'] ) && $form_settings['login_link_type'] == 'modal' ) {
							$default_lf_id = $arm_member_forms->arm_get_default_form_id( 'login' );
							$lf_id         = ( isset( $form_settings['login_link_type_modal'] ) ) ? $form_settings['login_link_type_modal'] : $default_lf_id;

							if ( array_key_exists( $lf_id, $arm_login_form_popup_ids_arr ) ) {
								$setupRandomID = $arm_login_form_popup_ids_arr[ $lf_id ];
							}

							$loginIdClass = 'arm_reg_form_login_link_' . $setupRandomID;
							$content     .= '<input type="hidden" name="arm_signup_login_form" value="' . $loginIdClass . '">';

							if ( ! array_key_exists( $lf_id, $arm_login_form_popup_ids_arr ) ) {
								$arm_login_form_popup_ids_arr[ $lf_id ] = $setupRandomID;
								$content                               .= do_shortcode( "[arm_form id='" . $lf_id . "' popup='true' link_title=' ' link_class='arm_reg_form_other_links " . $loginIdClass . "']" );
							} else {
								$content .= "[arm_form id='" . $lf_id . "' popup='true' link_title=' ' link_class='arm_reg_form_other_links " . $loginIdClass . "']";
							}

							$login_link_label = $arm_member_forms->arm_parse_login_links( $login_link_label, 'javascript:void(0)', 'arm_reg_popup_form_links arm_form_popup_ahref', 'data-form_id="' . $loginIdClass . '" data-toggle="armmodal"' );
							$content         .= '<center><span class="arm_login_link">' . $login_link_label . '</span></center>';
						} else {
							$loginLinkPageID  = ( isset( $form_settings['login_link_type_page'] ) ) ? $form_settings['login_link_type_page'] : $arm_global_settings->arm_get_single_global_settings( 'login_page_id', 0 );
							$loginLinkHref    = $arm_global_settings->arm_get_permalink( '', $loginLinkPageID );
							$login_link_label = $arm_member_forms->arm_parse_login_links( $login_link_label, $loginLinkHref );
							$content         .= '<center><span class="arm_login_link">' . $login_link_label . '</span></center>';
						}
						$content .= '<div class="armclear"></div>';
						$content .= '</div>';
						$content .= '<div class="armclear"></div>';
					}
					$nonce = wp_create_nonce('arm_wp_nonce');
					$content .= '<input type="hidden" name="arm_wp_nonce" value="'.esc_attr($nonce).'">';

					$content .= '</div>';
					$content .= '</div>';
					$content .= '</div></div>';
					$content .= '</form></div>';

					if ( $args['popup'] !== false ) {
						$popup_content  = '<div class="arm_setup_form_popup_container">';
						$link_title     = ( ! empty( $args['link_title'] ) ) ? $args['link_title'] : $setup_name;
						$link_style     = $link_hover_style = '';
						$popup_content .= '<style type="text/css">';
						if ( ! empty( $args['link_css'] ) ) {
							$link_style     = esc_html( $args['link_css'] );
							$popup_content .= '.arm_setup_form_popup_link_' . $setupID . '{' . $link_style . '}';
						}
						if ( ! empty( $args['link_hover_css'] ) ) {
							$link_hover_style = esc_html( $args['link_hover_css'] );
							$popup_content   .= '.arm_setup_form_popup_link_' . $setupID . ':hover{' . $link_hover_style . '}';
						}
						$popup_content .= '</style>';
						$pformRandomID  = $setupID . '_popup_' . arm_generate_random_code();
						$popupLinkID    = 'arm_setup_form_popup_link_' . $setupID;
						$popupLinkClass = 'arm_setup_form_popup_link arm_setup_form_popup_link_' . $setupID;
						if ( ! empty( $args['link_class'] ) ) {
							$popupLinkClass .= ' ' . esc_html( $args['link_class'] );
						}
						$popupLinkAttr = 'data-form_id="' . esc_attr($pformRandomID) . '" data-toggle="armmodal"  data-modal_bg="' . esc_attr($args['modal_bgcolor']) . '" data-overlay="' . esc_attr($args['overlay']) . '"';
						if ( ! empty( $args['link_type'] ) && strtolower( $args['link_type'] ) == 'button' ) {
							$popup_content .= '<button type="button" id="' . esc_attr($popupLinkID) . '" class="' . esc_attr($popupLinkClass) . ' arm_setup_form_popup_button" ' . $popupLinkAttr . '>' . esc_html($link_title) . '</button>';
						} else {
							$popup_content .= '<a href="javascript:void(0)" id="' .esc_attr($popupLinkID) . '" class="' . esc_attr($popupLinkClass) . ' arm_setup_form_popup_ahref" ' . $popupLinkAttr . '>' . esc_html($link_title) . '</a>';
						}
						$popup_style = $popup_content_height = '';
						$popupHeight = 'auto';
						$popupWidth  = '500';
						if ( ! empty( $args['popup_height'] ) ) {
							if ( $args['popup_height'] == 'auto' ) {
								$popup_style .= 'height: auto;';
							} else {
								$popup_style         .= 'overflow: hidden;height: ' . $args['popup_height'] . 'px;';
								$popupHeight          = ( $args['popup_height'] - 70 ) . 'px';
								$popup_content_height = 'overflow-x: hidden;overflow-y: auto;height: ' . ( $args['popup_height'] - 70 ) . 'px;';
							}
						}
						if ( ! empty( $args['popup_width'] ) ) {
							if ( $args['popup_width'] == 'auto' ) {
								$popup_style .= '';
							} else {
								$popupWidth   = $args['popup_width'];
								$popup_style .= 'width: ' . $args['popup_width'] . 'px;';
							}
						}
						$popup_content .= '<div class="popup_wrapper arm_popup_wrapper arm_popup_member_setup_form arm_popup_member_setup_form_' . esc_attr($setupID) . ' arm_popup_member_setup_form_' . esc_attr($pformRandomID) . '" style="' . $popup_style . '" data-width="' . esc_attr($popupWidth) . '"><div class="popup_setup_inner_container popup_wrapper_inner">';
						$popup_content .= '<div class="popup_header">';
						$popup_content .= '<span class="popup_close_btn arm_popup_close_btn"></span>';
						$popup_content .= '<div class="popup_header_text arm_setup_form_heading_container">';
						if ( $args['hide_title'] == false ) {
							$popup_content .= '<span class="arm_setup_form_field_label_wrapper_text">' . esc_html($setup_name) . '</span>';
						}
						$popup_content .= '</div>';
						$popup_content .= '</div>';
						$popup_content .= '<div class="popup_content_text" style="' . $popup_content_height . '" data-height="' . esc_attr($popupHeight) . '">';
						$popup_content .= $content;
						$popup_content .= '</div><div class="armclear"></div>';
						$popup_content .= '</div></div>';
						$popup_content .= '</div>';
						$content        = $popup_content;
						$content       .= '<div class="armclear">&nbsp;</div>';
					}
					$content = apply_filters( 'arm_after_setup_form_content', $content, $setupID, $setup_data );
				}
			}
			$ARMemberLite->arm_check_font_awesome_icons( $content );
			$ARMemberLite->enqueue_angular_script( true );

			$isEnqueueAll = $arm_global_settings->arm_get_single_global_settings( 'enqueue_all_js_css', 0 );
			if ( $isEnqueueAll == '1' ) {
				if ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) {
					$plan_skin = '';
					if ( $setup_style['plan_skin'] != 'skin5' ) {
						$plan_skin = ':checked';
					}
					$content .= '<script type="text/javascript" data-cfasync="false">
                                    jQuery(document).ready(function (){
                                        setTimeout(function () {
                                            jQuery(".arm_setup_form_container").show();
                                        }, 100);
                                        setTimeout(function () {
                                            arm_current_membership_init();
                                            arm_transaction_init();
                                            arm_tooltip_init();
                                            arm_set_plan_width();
                                            arm_do_bootstrap_angular();
                                            ARMFormInitValidation("arm_setup_form' . $setupRandomID . '");
                                            arm_equal_hight_setup_plan();
                                            jQuery("input.arm_module_plan_input' . $plan_skin . '").trigger("change");
                                        }, 500);                        
                                    }); ';

					$content .= '</script>';
				}
			}

			$inbuild     = '';
			$hiddenvalue = '';
			global $arm_lite_members_activity, $arm_lite_version;
			$arm_request_version = get_bloginfo( 'version' );
			$setact              = 0;
			global $check_version;
			$setact = $arm_lite_members_activity->$check_version();

			if ( $setact != 1 ) {
				$inbuild = ' (U)';
			}

			$hiddenvalue = '  
            <!--Plugin Name: ARMember    
                Plugin Version: ' . get_option( 'armlite_version' ) . ' ' . $inbuild . '
                Developed By: Repute Infosystems
                Developer URL: http://www.reputeinfosystems.com/
            -->';

			return do_shortcode( $content . $hiddenvalue );
		}

		function arm_get_sales_tax( $general_settings, $post_data = '', $user_id = 0, $form_id = 0 ) {

			$return_arr = array(
				'tax_type'                    => 'common_tax',
				'country_tax_field'           => '',
				'country_tax_field_opts_json' => '',
				'country_tax_amount_json'     => '',
				'tax_percentage'              => '',
			);

			$tax_type          = isset( $general_settings['tax_type'] ) ? $general_settings['tax_type'] : 'common_tax';
			$country_tax_field = isset( $general_settings['country_tax_field'] ) ? $general_settings['country_tax_field'] : '';

			if ( $tax_type == 'country_tax' ) {

				$tax_percentage = ! empty( $general_settings['arm_country_tax_default_val'] ) ? $general_settings['arm_country_tax_default_val'] : 0;

				if ( ! empty( $general_settings['arm_tax_country_name'] ) && $country_tax_field != '' ) {

					$country_tax_field_opts = isset( $general_settings['arm_tax_country_name'] ) ? $general_settings['arm_tax_country_name'] : '';

					if ( ! empty( $country_tax_field_opts ) ) {
						$country_tax_amount = isset( $general_settings['arm_country_tax_val'] ) ? $general_settings['arm_country_tax_val'] : '';
						if ( ! empty( $country_tax_amount ) ) {
							$country_tax_amount                        = maybe_unserialize( $country_tax_amount );
							$country_tax_field_opts                    = maybe_unserialize( $country_tax_field_opts );
							$return_arr['tax_type']                    = $tax_type;
							$return_arr['country_tax_field']           = $country_tax_field;
							$return_arr['country_tax_field_opts_json'] = wp_json_encode( $country_tax_field_opts );
							$return_arr['country_tax_amount_json']     = wp_json_encode( $country_tax_amount );

							if ( is_user_logged_in() && ! empty( $user_id ) ) {
								global $wpdb;
								$user_country = $wpdb->get_var( $wpdb->prepare("SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = '%s'",$user_id,$country_tax_field) ); //phpcs:ignore --Reason: $wpdb->usermeta is a table name. 
								if ( ! empty( $user_country ) && in_array( $user_country, $country_tax_field_opts ) ) {
									$opt_index      = array_search( $user_country, $country_tax_field_opts );
									$tax_percentage = $country_tax_amount[ $opt_index ];
								}
							} elseif ( ! empty( $post_data ) && isset( $post_data[ $country_tax_field ] ) && in_array( $post_data[ $country_tax_field ], $country_tax_field_opts ) ) {
								$opt_index      = array_search( $post_data[ $country_tax_field ], $country_tax_field_opts );
								$tax_percentage = $country_tax_amount[ $opt_index ];
							} elseif ( ! empty( $form_id ) && ctype_digit( $form_id ) ) {
								global $arm_member_forms;
								$form_field_opt = $arm_member_forms->arm_get_field_option_by_meta( $country_tax_field, $form_id );
								if ( ! empty( $form_field_opt ) && ! empty( $form_field_opt['default_val'] ) ) {
									$default_opt = $form_field_opt['default_val'];
									if ( in_array( $default_opt, $country_tax_field_opts ) ) {
										$opt_index      = array_search( $user_country, $country_tax_field_opts );
										$tax_percentage = $country_tax_amount[ $opt_index ];
									}
								}
							}
						}
					}
				}
			} else {
				$tax_percentage = isset( $general_settings['tax_amount'] ) ? $general_settings['tax_amount'] : 0;
			}

			$return_arr['tax_percentage'] = $tax_percentage;

			return $return_arr;
		}

		function arm_generate_setup_style( $setupid = 0, $setup_style = array() ) {
			$defaultStyle        = array(
				'content_width'                  => '800',
				'plan_skin'                      => '',
				'font_family'                    => 'Poppins',
				'title_font_size'                => 20,
				'title_font_bold'                => 1,
				'title_font_italic'              => '',
				'title_font_decoration'          => '',
				'description_font_size'          => 15,
				'description_font_bold'          => 0,
				'description_font_italic'        => '',
				'description_font_decoration'    => '',
				'price_font_size'                => 28,
				'price_font_bold'                => 1,
				'price_font_italic'              => '',
				'price_font_decoration'          => '',
				'form_position'                  => 'center',
				'summary_font_size'              => 16,
				'summary_font_bold'              => 0,
				'summary_font_italic'            => '',
				'summary_font_decoration'        => '',
				'plan_title_font_color'          => '#2C2D42',
				'plan_desc_font_color'           => '#2C2D42',
				'price_font_color'               => '#2C2D42',
				'summary_font_color'             => '#2C2D42',
				'bg_active_color'                => '#005AEE',
				'selected_plan_title_font_color' => '#005AEE',
				'selected_plan_desc_font_color'  => '#000000',
				'selected_price_font_color'      => '#005AEE',
			);
			$setup_style         = shortcode_atts( $defaultStyle, $setup_style );
			$form_position       = ( isset( $setup_style['form_position'] ) ) ? 'float: ' . $setup_style['form_position'] : 'float:none';
			$summary_font_style  = ( isset( $setup_style['summary_font_bold'] ) && $setup_style['summary_font_bold'] == '1' ) ? 'font-weight: bold;' : 'font-weight: normal;';
			$summary_font_style .= ( isset( $setup_style['summary_font_italic'] ) && $setup_style['summary_font_italic'] == '1' ) ? 'font-style: italic;' : '';
			$summary_font_style .= ( isset( $setup_style['summary_font_decoration'] ) && ! empty( $setup_style['summary_font_decoration'] ) ) ? 'text-decoration: ' . $setup_style['summary_font_decoration'] . ';' : '';

			$title_font_style  = ( isset( $setup_style['title_font_bold'] ) && $setup_style['title_font_bold'] == '1' ) ? 'font-weight: bold;' : 'font-weight: normal;';
			$title_font_style .= ( isset( $setup_style['title_font_italic'] ) && $setup_style['title_font_italic'] == '1' ) ? 'font-style: italic;' : '';
			$title_font_style .= ( isset( $setup_style['title_font_decoration'] ) && ! empty( $setup_style['title_font_decoration'] ) ) ? 'text-decoration: ' . $setup_style['title_font_decoration'] . ';' : '';

			$description_font_style  = ( isset( $setup_style['description_font_bold'] ) && $setup_style['description_font_bold'] == '1' ) ? 'font-weight: bold;' : 'font-weight: normal;';
			$description_font_style .= ( isset( $setup_style['description_font_italic'] ) && $setup_style['description_font_italic'] == '1' ) ? 'font-style: italic;' : '';
			$description_font_style .= ( isset( $setup_style['description_font_decoration'] ) && ! empty( $setup_style['description_font_decoration'] ) ) ? 'text-decoration: ' . $setup_style['description_font_decoration'] . ';' : '';
			$price_font_style        = ( isset( $setup_style['price_font_bold'] ) && $setup_style['price_font_bold'] == '1' ) ? 'font-weight: bold;' : 'font-weight: normal;';
			$price_font_style       .= ( isset( $setup_style['price_font_italic'] ) && $setup_style['price_font_italic'] == '1' ) ? 'font-style: italic;' : '';
			$price_font_style       .= ( isset( $setup_style['price_font_decoration'] ) && ! empty( $setup_style['price_font_decoration'] ) ) ? 'text-decoration: ' . $setup_style['price_font_decoration'] . ';' : '';
			$setup_content_width     = ( $setup_style['content_width'] == 0 && $setup_style['content_width'] != '' ) ? '800' : $setup_style['content_width'];
			$setup_content_width     = ( $setup_content_width == '' ) ? 'auto' : $setup_content_width . 'px';
			$setup_font_family       = ( $setup_style['font_family'] != 'inherit' ) ? 'font-family: ' . $setup_style['font_family'] . ', sans-serif, \'Trebuchet MS\' !important;' : '';
			$setup_css               = '
                    .arm_setup_form_' . $setupid . '.arm-default-form:not(.arm_admin_member_form){
                        width: ' . $setup_content_width . ';
                        margin: 0 auto;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_form_title,
                    .arm_setup_form_' . $setupid . ' .arm_setup_section_title_wrapper{
                        ' . $setup_font_family . '
                        font-size: 20px !important;
                        font-size: ' . ( $setup_style['title_font_size'] + 2 ) . 'px !important;
                        color: ' . $setup_style['plan_title_font_color'] . ';
                        font-weight: normal;
                    }
                    
                    .arm_setup_form_' . $setupid . ' .arm_payment_mode_label{
                        ' . $setup_font_family . '
                        font-size: ' . $setup_style['description_font_size'] . 'px !important;
                        color: ' . $setup_style['plan_desc_font_color'] . ';
                        font-weight : normal;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item .arm_module_plan_name,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item .arm_module_gateway_name,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item .arm_module_payment_cycle_name{
                        ' . $setup_font_family . '
                        font-size: ' . $setup_style['title_font_size'] . 'px !important;
                        color: ' . $setup_style['plan_title_font_color'] . ' !important;
                        ' . $title_font_style . '
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_name,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_plan_name{
                        color: ' . $setup_style['selected_plan_title_font_color'] . ' !important;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item .arm_module_plan_price{
                        ' . $setup_font_family . '
                        font-size: ' . $setup_style['price_font_size'] . 'px !important;
                        color: ' . $setup_style['price_font_color'] . ' !important;
                        ' . $price_font_style . '
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_price_type .arm_module_plan_price,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_plan_price{
                        color: ' . $setup_style['selected_price_font_color'] . ' !important;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item .arm_module_plan_description{
                        ' . $setup_font_family . '
                        font-size: ' . $setup_style['description_font_size'] . 'px !important;
                        color: ' . $setup_style['plan_desc_font_color'] . ';
                        ' . $description_font_style . '
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_description,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_plan_description{
                        color: ' . $setup_style['selected_plan_desc_font_color'] . ';
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_summary_text_container .arm_setup_summary_text{
                        ' . $setup_font_family . '
                        font-size: ' . $setup_style['summary_font_size'] . 'px !important;
                        color: ' . $setup_style['summary_font_color'] . ';
                        ' . $summary_font_style . '
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item:hover .arm_module_plan_option,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_plan_option,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item:hover .arm_module_gateway_option,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_gateway_option,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item:hover .arm_module_payment_cycle_option,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item.arm_active .arm_module_payment_cycle_option{
                        border: 1px solid ' . $setup_style['bg_active_color'] . ';
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item .arm_module_plan_name,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item .arm_module_gateway_name,
                    .arm_setup_form_' . $setupid . ' .arm_setup_column_item .arm_module_payment_cycle_name{
                        font-size: ' . $setup_style['title_font_size'] . 'px !important;
                        color: ' . $setup_style['plan_title_font_color'] . ';
                        ' . $title_font_style . '
                    }
                    .arm_setup_form_' . $setupid . ' .arm_plan_default_skin.arm_setup_column_item.arm_active .arm_module_plan_option{
                        background-color: ' . $setup_style['bg_active_color'] . ';
                        border: 1px solid ' . $setup_style['bg_active_color'] . ';
                    }
                    .arm_setup_form_' . $setupid . ' .arm_plan_default_skin.arm_setup_column_item.arm_active .arm_module_plan_option{
                        background-color: ' . $setup_style['bg_active_color'] . ';
                        border: 1px solid ' . $setup_style['bg_active_color'] . ';
                    }
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin1.arm_setup_column_item:hover .arm_module_plan_option .arm_module_plan_price_type,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin1.arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_price_type {
                        transition: all 0.7s ease 0s;
                        -webkit-transition: all 0.7s ease 0s;
                        -moz-transiton: all 0.7s ease 0s;
                        -o-transition: all 0.7s ease 0s;
                        background-color: ' . $setup_style['bg_active_color'] . ';
                        border: 1px solid ' . $setup_style['bg_active_color'] . ';
                    }
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin1.arm_setup_column_item:hover .arm_module_plan_option .arm_module_plan_price_type .arm_module_plan_price,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin1.arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_price_type .arm_module_plan_price{
                        color: ' . $setup_style['selected_price_font_color'] . ' !important;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin2.arm_setup_column_item:hover .arm_module_plan_option .arm_module_plan_name,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin2.arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_name{
                         transition: all 0.7s ease 0s;
                        -webkit-transition: all 0.7s ease 0s;
                        -moz-transiton: all 0.7s ease 0s;
                        -o-transition: all 0.7s ease 0s;
                        background-color: ' . $setup_style['bg_active_color'] . ';
                        border: 1px solid ' . $setup_style['bg_active_color'] . ';
                    }

                    .arm_setup_form_' . $setupid . ' .arm_plan_skin6.arm_setup_column_item:hover .arm_module_plan_option,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin6.arm_setup_column_item.arm_active .arm_module_plan_option{
                         transition: all 0.7s ease 0s;
                        -webkit-transition: all 0.7s ease 0s;
                        -moz-transiton: all 0.7s ease 0s;
                        -o-transition: all 0.7s ease 0s;
                        background-color: ' . $setup_style['bg_active_color'] . ';
                        border: 1px solid ' . $setup_style['bg_active_color'] . ';
                        color: ' . $setup_style['selected_plan_title_font_color'] . ' !important;
                    }

                    .arm_setup_form_' . $setupid . ' .arm_plan_skin6.arm_setup_column_item:hover .arm_module_plan_option .arm_module_plan_name,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin6.arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_name,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin6.arm_setup_column_item:hover .arm_module_plan_option .arm_module_plan_price,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin6.arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_price,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin6.arm_setup_column_item:hover .arm_module_plan_option .arm_module_plan_description,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin6.arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_description{
                         transition: all 0.7s ease 0s;
                        -webkit-transition: all 0.7s ease 0s;
                        -moz-transiton: all 0.7s ease 0s;
                        -o-transition: all 0.7s ease 0s;
                       
                        color: ' . $setup_style['selected_plan_title_font_color'] . ' !important;
                    }


                    .arm_setup_form_' . $setupid . ' .arm_plan_skin2.arm_setup_column_item:hover .arm_module_plan_option .arm_module_plan_name,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin2.arm_setup_column_item.arm_active .arm_module_plan_option .arm_module_plan_name{
                        color: ' . $setup_style['selected_plan_title_font_color'] . ' !important;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_setup_check_circle{
                        border-color: ' . $setup_style['bg_active_color'] . ' !important;
                        color: ' . $setup_style['bg_active_color'] . ' !important;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin3 .arm_module_plan_option .arm_setup_check_circle i,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin3 .arm_module_plan_option:hover .arm_setup_check_circle i{
                        color:  ' . $setup_style['bg_active_color'] . ' !important;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin3 .arm_module_plan_option .arm_setup_check_circle,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin3 .arm_module_plan_option .arm_setup_check_circle,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin3 .arm_module_plan_option .arm_setup_check_circle,
                    .arm_setup_form_' . $setupid . ' .arm_plan_skin5 .arm_module_plan_option .arm_setup_check_circle{
                        border-color: ' . $setup_style['bg_active_color'] . ' !important;
                    }
                    .arm_setup_form_' . $setupid . ' .arm_module_gateways_container .arm_module_gateway_fields{
                        ' . $setup_font_family . '
                    }
                    .arm_setup_form_' . $setupid . ' .arm-form-container .arm-default-form:not(.arm_admin_member_form)
                    {
                        ' . $form_position . '   
                    }
                ';
			return $setup_css;
		}

		function armSortModuleOrders( array $array, array $orderArray ) {
			$ordered = array();
			if ( ! empty( $array ) && ! empty( $orderArray ) ) {
				foreach ( $array as $key => $val ) {
					if ( array_key_exists( $val, $orderArray ) ) {
						$ordered[ $orderArray[ $val ] ] = $val;
						unset( $array[ $key ] );
					}
				}
			} else {
				$ordered = $array;
			}
			if ( ! empty( $ordered ) ) {
				ksort( $ordered );
			}
			return $ordered;
		}

		function arm_sort_module_by_order( $items = array(), $item_order = array() ) {
			$new_items = array();
			if ( ! empty( $items ) ) {
				if ( ! empty( $item_order ) ) {
					asort( $item_order );
					foreach ( $item_order as $key => $order ) {
						if ( ! empty( $items[ $key ] ) ) {
							$new_items[ $key ] = $items[ $key ];
							unset( $items[ $key ] );
						}
					}
					$new_items = $new_items + $items;
				} else {
					$new_items = $items;
				}
			}
			return $new_items;
		}

		/*function arm_refresh_setup_items() {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways,$arm_subscription_plans, $arm_capabilities_global;
			$module_items = '';

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_setups'], '1' );

			if ( ! empty( $_POST['module'] ) ) { //phpcs:ignore
				$module_items = $this->arm_get_module_items_box( sanitize_text_field($_POST['module']) ); //phpcs:ignore
			}
			echo $module_items; //phpcs:ignore
			exit;
		}*/
		/*
		function arm_get_module_items_box( $module_type = 'plans', $options = array() ) {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_payment_gateways, $arm_subscription_plans;
			$module_box          = '';
			$alertMessages       = $ARMemberLite->arm_alert_messages();
			$add_plan_link       = admin_url( 'admin.php?page=' . $arm_slugs->manage_plans . '&action=new' );
			$manage_gateway_link = admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&&action=payment_options' );
			$selected_items      = ( ! empty( $options['selected'] ) ) ? $options['selected'] : array();
			$items_order         = ( ! empty( $options['items_order'] ) ) ? $options['items_order'] : array();
			$column_type         = ( ! empty( $options['column'] ) ) ? $options['column'] : 1;

			$input_prefix = 'setup_data[setup_modules]';
			if ( $module_type == 'plans' ) {
				$active_plans   = $arm_subscription_plans->arm_get_all_active_subscription_plans();
				$filtered_items = $this->arm_sort_module_by_order( $active_plans, $items_order );
				$module_box    .= '<div class="arm_setup_items_box_plans">';
				if ( ! empty( $filtered_items ) ) {
					$module_box   .= '<div class="arm_setup_module_column_layout_types arm_setup_plans_column_layout_types">';
					$module_box   .= '<div class="arm_column_layout_types_container">';
					$column1_class = ( $column_type == 1 ) ? 'arm_active_label' : '';
					$module_box   .= '<label class="' . $column1_class . '"><span class="single_column_img"></span><input type="radio" name="setup_data[setup_modules][plans_columns]" value="1" class="arm_column_layout_type_radio" ' . checked( $column_type, '1', false ) . ' data-module="plans"><span>' . esc_html__( 'Single Column', 'armember-membership' ) . '</span></label>';
					$column2_class = ( $column_type == 2 ) ? 'arm_active_label' : '';
					$module_box   .= '<label class="' . $column2_class . '"><span class="two_column_img"></span><input type="radio" name="setup_data[setup_modules][plans_columns]" value="2" class="arm_column_layout_type_radio" ' . checked( $column_type, '2', false ) . ' data-module="plans"><span>' . esc_html__( 'Two Column', 'armember-membership' ) . '</span></label>';
					$column3_class = ( $column_type == 3 ) ? 'arm_active_label' : '';
					$module_box   .= '<label class="' . $column3_class . '"><span class="three_column_img"></span><input type="radio" name="setup_data[setup_modules][plans_columns]" value="3" class="arm_column_layout_type_radio" ' . checked( $column_type, '3', false ) . ' data-module="plans"><span>' . esc_html__( 'Three Column', 'armember-membership' ) . '</span></label>';
					$module_box   .= '<div class="armclear"></div></div>';
					$module_box   .= '</div>';
					$module_box   .= '<ul class="arm_setup_plans_ul arm_membership_setup_sub_ul arm_column_' . $column_type . '">';
					$pi            = 1;
					foreach ( $filtered_items as $plan ) {
						$planObj = new ARM_Plan_Lite( 0 );
						$planObj->init( (object) $plan );
						$plan_id      = $planObj->ID;
						$plan_options = $planObj->options;
						/* Check Recurring Details *//*
						$bank_allow = ( $planObj->is_recurring() ) ? '0' : '1';

						$planInputAttr = ' data-plan_name="' . $planObj->name . '" data-plan_type="' . $planObj->type . '" data-payment_type="' . $planObj->payment_type . '"  data-bank_allow="' . $bank_allow . '" ';
						$plan_checked  = ( ! empty( $selected_items ) && in_array( $plan_id, $selected_items ) ) ? 'checked="checked"' : '';
						$module_box   .= '<li class="arm_membership_setup_plans_li arm_membership_setup_sub_li">';
						$module_box   .= '<div class="arm_membership_setup_sortable_icon"></div>';
						$module_box   .= '<label id="label_plan_chk_' . $plan_id . '">';
						$module_box   .= '<input type="checkbox" name="' . $input_prefix . '[modules][plans][]" value="' . $plan_id . '" id="plan_chk_' . $plan_id . '" class="arm_icheckbox plans_chk_inputs plans_chk_inputs_' . $planObj->type . '" ' . $planInputAttr . ' ' . $plan_checked . ' data-msg-required="' . esc_html__( 'Please select at least one plan.', 'armember-membership' ) . '"/>';
						$module_box   .= '<span>' . $planObj->name . '</span>';
						$module_box   .= '</label>';
						$module_box   .= '<input type="hidden" name="' . $input_prefix . '[modules][plans_order][' . $plan_id . ']" value="' . $pi . '" class="arm_module_options_order">';
						$module_box   .= '</li>';
						$pi++;
					}
					$module_box .= '</ul>';
				} else {
					$module_box .= '<span class="arm_setup_plan_error_msg error" style="display: none;">' . esc_html__( 'Please select at least one plan.', 'armember-membership' ) . '</span>';
					$module_box .= '<a href="javascript:void(0)" class="arm_setup_module_refresh" data-module="plans" title="' . esc_html__( 'Reload Plan List', 'armember-membership' ) . '"><i class="armfa armfa-refresh"></i></a>';
					$module_box .= '<div class="arm_setup_items_empty_msg">' . esc_html__( 'There is no any plan configured yet.', 'armember-membership' );
					$module_box .= ' <a href="' . $add_plan_link . '" target="_blank">' . esc_html__( 'Please click here to add plan.', 'armember-membership' ) . '</a>';
					$module_box .= ' ' . esc_html__( 'After adding plan, click on refresh button', 'armember-membership' );
					$module_box .= ' (<a style="float: none;padding: 3px;" href="javascript:void(0)" class="arm_setup_module_refresh" data-module="plans"><i class="armfa armfa-refresh"></i></a>) ' . esc_html__( 'to get added plans.', 'armember-membership' );
					$module_box .= '</div>';
				}
				$module_box .= '</div>';
			} elseif ( $module_type == 'gateways' ) {
				$active_gateways  = $arm_payment_gateways->arm_get_active_payment_gateways();
				$filtered_gatways = $this->arm_sort_module_by_order( $active_gateways, $items_order );
				$module_box      .= '<div class="arm_setup_items_box_gateways">';
				if ( ! empty( $filtered_gatways ) ) {
					$module_box   .= '<div class="arm_setup_module_column_layout_types arm_setup_gatways_column_layout_types">';
					$module_box   .= '<div class="arm_column_layout_types_container">';
					$column1_class = ( $column_type == 1 ) ? 'arm_active_label' : '';
					$module_box   .= '<label class="' . $column1_class . '"><span class="single_column_img"></span><input type="radio" name="setup_data[setup_modules][gateways_columns]" value="1" class="arm_column_layout_type_radio" ' . checked( $column_type, '1', false ) . ' data-module="gateways"><span>' . esc_html__( 'Single Column', 'armember-membership' ) . '</span></label>';
					$column2_class = ( $column_type == 2 ) ? 'arm_active_label' : '';
					$module_box   .= '<label class="' . $column2_class . '"><span class="two_column_img"></span><input type="radio" name="setup_data[setup_modules][gateways_columns]" value="2" class="arm_column_layout_type_radio" ' . checked( $column_type, '2', false ) . ' data-module="gateways"><span>' . esc_html__( 'Two Column', 'armember-membership' ) . '</span></label>';
					$column3_class = ( $column_type == 3 ) ? 'arm_active_label' : '';
					$module_box   .= '<label class="' . $column3_class . '"><span class="three_column_img"></span><input type="radio" name="setup_data[setup_modules][gateways_columns]" value="3" class="arm_column_layout_type_radio" ' . checked( $column_type, '3', false ) . ' data-module="gateways"><span>' . esc_html__( 'Three Column', 'armember-membership' ) . '</span></label>';
					$module_box   .= '<div class="armclear"></div></div>';
					$module_box   .= '</div>';
					$module_box   .= '<ul class="arm_setup_gateways_ul arm_membership_setup_sub_ul arm_column_' . $column_type . '">';
					$gi            = 1;
					/* --------------------Strip Plan Box------------------------------------ */
					/*
					$selected_plan = isset( $options['selected_plan'] ) ? $options['selected_plan'] : array();

					$isBTWarning = false;
					if ( ! empty( $selected_plan ) ) {
						foreach ( $selected_plan as $pID ) {
							$pddata       = $arm_subscription_plans->arm_get_subscription_plan( $pID, 'arm_subscription_plan_name, arm_subscription_plan_type, arm_subscription_plan_options' );
							$s_plan_name  = $pddata['arm_subscription_plan_name'];
							$plan_type    = $pddata['arm_subscription_plan_type'];
							$plan_options = maybe_unserialize( $pddata['arm_subscription_plan_options'] );
							$payment_type = isset( $plan_options['payment_type'] ) ? $plan_options['payment_type'] : '';
							if ( $plan_type == 'paid' && $payment_type == 'subscription' ) {
								if ( in_array( 'bank_transfer', $selected_items ) ) {
									$isBTWarning          = true;
									$bankNotPlans[ $pID ] = $s_plan_name;
								}

								$trialOptions = isset( $plan_options['trial'] ) ? $plan_options['trial'] : array();

							}
						}
					}
					/* -------------------------------------------------------- *//*
					foreach ( $filtered_gatways as $key => $pg ) {
						$pgname          = $pg['gateway_name'];
						$gateway_checked = ( ! empty( $selected_items ) && in_array( $key, $selected_items ) ) ? 'checked="checked"' : '';
						$module_box     .= '<li class="arm_membership_setup_gateways_li arm_membership_setup_sub_li">';
						$module_box     .= '<div class="arm_membership_setup_sortable_icon"></div>';
						$module_box     .= '<label>';
						$module_box     .= '<input type="checkbox" name="' . $input_prefix . '[modules][gateways][]" value="' . $key . '" id="gateway_chk_' . $key . '" class="arm_icheckbox gateways_chk_inputs" ' . $gateway_checked . ' data-pg_name="' . $pgname . '" data-msg-required="' . esc_html__( 'Please select at least one payment gateway.', 'armember-membership' ) . '"/>';
						$module_box     .= '<span>' . $pgname . '</span>';
						$module_box     .= '</label>';
						$module_box     .= '<input type="hidden" name="' . $input_prefix . '[modules][gateways_order][' . $key . ']" value="' . $gi . '" class="arm_module_options_order">';

						$module_box .= '</li>';
						$gi++;
					}
					$module_box .= '</ul>';
					$module_box .= '<div class="armclear"></div>';
					$module_box .= '<div class="arm_payment_gateway_warnings">';

					$module_box .= '<span class="arm_invalid" id="arm_bank_transfer_warning" style="' . ( $isBTWarning ? '' : 'display:none;' ) . '"><span class="arm_bank_transfer_not_support_plans">' . ( implode( ',', $bankNotPlans ) ) . '</span> ' . esc_html__( "plan's configuration is not supported by Bank Transfer. So it will be hide as a payment option when user will select this plan(s).", 'armember-membership' ) . '</span>';
					$module_box .= '</div>';
					$module_box .= '<div class="armclear"></div>';
				} else {
					$module_box .= '<span class="arm_setup_gateway_error_msg error" style="display: none;">' . esc_html__( 'Payment gateway is required for paid plans.', 'armember-membership' ) . '</span>';
					$module_box .= '<a href="javascript:void(0)" class="arm_setup_module_refresh" data-module="gateways" title="' . esc_html__( 'Reload Payment Gateway List', 'armember-membership' ) . '"><i class="armfa armfa-refresh"></i></a>';
					$module_box .= '<div class="arm_setup_items_empty_msg">' . esc_html__( 'There is no any payment gateway configured yet.', 'armember-membership' );
					$module_box .= ' <a href="' . $manage_gateway_link . '" target="_blank">' . esc_html__( 'Please click here to add payment method.', 'armember-membership' ) . '</a>';
					$module_box .= ' ' . esc_html__( 'After setup payment gateway, click on refresh button', 'armember-membership' );
					$module_box .= ' (<a style="float: none;padding: 3px;" href="javascript:void(0)" class="arm_setup_module_refresh" data-module="gateways"><i class="armfa armfa-refresh"></i></a>) ' . esc_html__( 'to get added payment gateways.', 'armember-membership' );
					$module_box .= '</div>';
				}
				$module_box .= '</div>';
			}
			return $module_box;
		}*/

		function arm_update_plan_form_gateway_selection() {
			global $wp, $wpdb, $ARMemberLite, $arm_payment_gateways, $arm_subscription_plans, $arm_capabilities_global;
			$returnArr = array(
				'plans'            => '',
				'plan_layout_list' => '',
				'forms'            => $this->arm_setup_form_list_options(),
				'gateways'         => '',
			);
			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_setups'], '1' ); 
			$totalPlans         = isset( $_POST['total_plans'] ) ? intval( $_POST['total_plans'] ) : 0; //phpcs:ignore
			$totalGateways      = isset( $_POST['total_gateways'] ) ? intval( $_POST['total_gateways'] ) : 0; //phpcs:ignore
			$selectedPlans      = ( isset( $_POST['selected_plans'] ) && ! empty( $_POST['selected_plans'] ) ) ? explode( ',', sanitize_text_field($_POST['selected_plans']) ) : array(); //phpcs:ignore
			$plansOrder         = ( isset( $_POST['setup_data']['setup_modules']['modules']['plans_order'] ) ) ? array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST['setup_data']['setup_modules']['modules']['plans_order'] ) : array(); //phpcs:ignore
			$selectedGateways   = ( isset( $_POST['selected_gateways'] ) && ! empty( $_POST['selected_gateways'] ) ) ? explode( ',', sanitize_text_field($_POST['selected_gateways']) ) : array(); //phpcs:ignore
			$user_selected_plan = ( isset( $_POST['default_selected_plan'] ) ) ? intval( $_POST['default_selected_plan'] ) : ''; //phpcs:ignore
			$activePlanCounts   = $arm_subscription_plans->arm_get_total_active_plan_counts();
			if ( $activePlanCounts == 0 ) {
				$returnArr['plans'] = "<span style='display:none;'></span>";
			} elseif ( $activePlanCounts != $totalPlans ) {
				$allPlans                      = $arm_subscription_plans->arm_get_all_active_subscription_plans();
				$returnArr['plans']            = $this->arm_setup_plan_list_options( $selectedPlans, $allPlans );
				$returnArr['plan_layout_list'] = $this->arm_setup_plan_layout_list_options( $plansOrder, $selectedPlans, $user_selected_plan );
			}
			$activeGateways = $arm_payment_gateways->arm_get_active_payment_gateways();
			if ( count( $activeGateways ) == 0 ) {
				$returnArr['gateways'] = "<span style='display:none;'></span>";
			} elseif ( count( $activeGateways ) != $totalGateways ) {
				$activeGateways        = $arm_payment_gateways->arm_get_active_payment_gateways();
				$returnArr['gateways'] = $this->arm_setup_gateway_list_options( $selectedGateways, $activeGateways );
			}

			$returnArr = apply_filters( 'arm_modify_update_plan_form_gateway_selection', $returnArr, $_POST ); //phpcs:ignore

			echo wp_json_encode( $returnArr );
			exit;
		}

		function arm_setup_plan_list_options( $selectedPlans = array(), $allPlans = array() ) {
			global $wp, $wpdb, $ARMemberLite, $arm_subscription_plans;
			$planList = '';
				$planList     .= '<div class="arm_setup_forms_container">
					<select id="arm_membership_setup_plan" class="arm_chosen_selectbox"
						data-msg-required="'.esc_attr__( 'Select Membership Plans.', 'armember-membership' ).'"
						name="setup_data[setup_modules][modules][plans][]"
						data-placeholder="'. esc_attr__( 'Select Membership Plans', 'armember-membership' ).'"
						multiple="multiple">';					
						if ( ! empty( $allPlans ) ) {
							foreach ( $allPlans as $plan ) { 
								$planObj = new ARM_Plan_Lite( 0 );
								$planObj->init( (object) $plan );
								$plan_id = $planObj->ID;
								$plan_options = $planObj->options;
								$is_plan_selected = ( in_array( $plan_id, $selectedPlans ) ) ?  "selected='selected'" : '';
								$plan_name = $planObj->name;
                               					$arm_plan_cls = ($planObj->type == 'recurring') ? "plans_chk_inputs_recurring" : '';
								$arm_show_plan_payment_cycles = ( isset( $plan_options['show_payment_cycle'] ) && $plan_options['show_payment_cycle'] == '1' ) ? 1 : 0;
								$planInputAttr = ' data-plan_name="' . esc_attr($planObj->name) . '" data-plan_type="' . esc_attr($planObj->type) . '" data-payment_type="' . esc_attr($planObj->payment_type) . '" data-show_payment_cycle="' . esc_attr($arm_show_plan_payment_cycles) . '" ';
								$planList     .= '<option class="arm_message_selectbox_op '.$arm_plan_cls.'" value="'.esc_attr($plan_id).'" '.$is_plan_selected.' '.$planInputAttr.'>'. esc_html($plan_name).'</option>';
							}
						} else {
							$planList     .= '<option value="">'. esc_html__( 'No plans Available', 'armember-membership' ).' </option>';
						}
					$planList     .= '</select>
				</div>';		
			return $planList;
		}

		function arm_setup_plan_layout_list_options( $planOrders = array(), $selectedPlans = array(), $user_selected_plan = '' ) {
			global $wp, $wpdb, $ARMemberLite, $arm_subscription_plans;
			$planOrderList = '';
			$allPlans      = $arm_subscription_plans->arm_get_all_subscription_plans();

			$allPlans = apply_filters( 'arm_modify_all_plans_arr_for_setup', $allPlans );

			$orderPlans         = $this->arm_sort_module_by_order( $allPlans, $planOrders );
			$user_selected_plan = ( isset( $user_selected_plan ) ) ? $user_selected_plan : '';

			if ( ! empty( $orderPlans ) ) {
				$pi = 1;
				foreach ( $orderPlans as $plan ) {
					$plan_id   = $plan['arm_subscription_plan_id'];
					$add_class = 'arm_setup_subscription_plan_li';

					$add_class = apply_filters( 'arm_add_class_filter_for_setup', $add_class, $plan_id );

					/* if no plan selected than set first plan default selected */
					if ( $pi == 1 && $user_selected_plan == '' ) {
						$user_selected_plan = $plan_id;
					}

					$planClass      = 'arm_membership_setup_plans_li_' . esc_attr($plan_id);
					$planClass     .= ( ! in_array( $plan_id, $selectedPlans ) ? ' hidden_section ' : '' );
					$planOrderList .= '<li class="arm_membership_setup_sub_li arm_membership_setup_plans_li ' . $planClass . ' ' . $add_class . '">';
					$planOrderList .= '<div class="arm_membership_setup_sortable_icon"></div>';
					$planOrderList .= '<input type="radio" class="arm_iradio arm_default_user_selected_plan" name="setup_data[setup_modules][selected_plan]" value="' . esc_attr($plan_id) . '" ' . checked( $user_selected_plan, $plan_id, false ) . ' id="arm_setup_plan_' . esc_attr($plan_id) . '">';
					$planOrderList .= '<label for="arm_setup_plan_' . esc_attr($plan_id) . '" class="arm_setup_plan_label">' . $plan['arm_subscription_plan_name'] . '</label>';
					$planOrderList .= '<input type="hidden" name="setup_data[setup_modules][modules][plans_order][' . esc_attr($plan_id) . ']" value="' . esc_attr($pi) . '" class="arm_module_options_order arm_plan_order_inputs" data-plan_id="' . esc_attr($plan_id) . '">';
					$planOrderList .= '</li>';
					$pi++;
				}
			}
			return $planOrderList;
		}

		function arm_setup_form_list_options() {
			global $wp, $wpdb, $ARMemberLite, $arm_member_forms;
			$registerForms = $arm_member_forms->arm_get_member_forms_by_type( 'registration', false );
			$formList      = '<li data-label="' . esc_html__( 'Select Form', 'armember-membership' ) . '" data-value="">' . esc_html__( 'Select Form', 'armember-membership' ) . '</li>';
			if ( ! empty( $registerForms ) ) {
				foreach ( $registerForms as $form ) {
					$formList .= '<li data-label="' . wp_strip_all_tags( stripslashes( $form['arm_form_label'] ) ) . '" data-value="' . esc_attr($form['arm_form_id']) . '">' . wp_strip_all_tags( stripslashes( $form['arm_form_label'] ) ) . '</li>';
				}
			}
			return $formList;
		}

		function arm_setup_gateway_list_options( $selectedGateways = array(), $activeGateways = array(), $selectedPaymentModes = array(), $selectedPlans = array(), $plan_object_array = array() ) {
			global $wp, $wpdb, $ARMemberLite, $arm_payment_gateways, $arm_subscription_plans;
			$gatewayList = '';

			$arm_display_payment_mode_box = 'display: none;';

			if ( ! empty( $selectedPlans ) && count( $selectedPlans ) > 0 ) {
				foreach ( $selectedPlans as $plan ) {
					$planObj = isset( $plan_object_array[ $plan ] ) ? $plan_object_array[ $plan ] : '';

					if ( is_object( $planObj ) ) {
						$plan_type                    = $planObj->type;
						$plan_options                 = $planObj->options;
						$arm_show_plan_payment_cycles = ( isset( $plan_options['show_payment_cycle'] ) && $plan_options['show_payment_cycle'] == '1' ) ? 1 : 0;
						if ( $planObj->is_recurring() || ( $plan_type == 'paid_finite' && $arm_show_plan_payment_cycles == 1 ) ) {
							$arm_display_payment_mode_box = 'display: block;';
						}
					}
				}
			}

			if ( ! empty( $activeGateways ) ) {
				$doNotDisplayPaymentMode = array( 'bank_transfer' );
				$doNotDisplayPaymentMode = apply_filters( 'arm_not_display_payment_mode_setup', $doNotDisplayPaymentMode );
				foreach ( $activeGateways as $key => $pg ) {

					$selectedPaymentModes[ $key ] = isset( $selectedPaymentModes[ $key ] ) ? $selectedPaymentModes[ $key ] : 'both';
					$checked_auto                 = ( $selectedPaymentModes[ $key ] == 'auto_debit_subscription' ) ? 'checked="checked"' : '';
					$checked_manual               = ( $selectedPaymentModes[ $key ] == 'manual_subscription' ) ? 'checked="checked"' : '';
					$checked_both                 = ( $selectedPaymentModes[ $key ] == 'both' ) ? 'checked="checked"' : '';

					$gatewayChecked = in_array( $key, $selectedGateways ) ? 'checked="checked"' : '';
					if ( in_array( $key, $selectedGateways ) ) {
						$display_payment_mode = 'display: block;';
					} else {
						$display_payment_mode = 'display: none;';
					}
					$gatewayList .= '<div class="arm_setup_gateway_opt_wrapper" id="arm_setup_gateway_opt_wrapper_id">';
					$gatewayList .= '<input type="checkbox" name="setup_data[setup_modules][modules][gateways][]" value="' . esc_attr($key) . '" id="gateway_chk_' . esc_attr($key) . '" class="arm_icheckbox gateways_chk_inputs" data-pg_name="' . esc_attr($pg['gateway_name']) . '" ' . $gatewayChecked . ' data-msg-required="' . esc_html__( 'Please select at least one payment gateway.', 'armember-membership' ) . '"/>';
					$gatewayList .= '<label for="gateway_chk_' . esc_attr($key) . '">' . esc_attr($pg['gateway_name']) . '</label>';

					if ( ! in_array( $key, $doNotDisplayPaymentMode ) ) {
						$gateway_note = '';
						$gateway_note = apply_filters( 'arm_setup_show_payment_gateway_notice', $gateway_note, $key );
						$gatewayList .= '<div class="arm_gateway_payment_mode_box" style="' . $arm_display_payment_mode_box . '"><div class="' . esc_attr($key) . '_gateway_payment_mode_class" id="arm_gateway_payment_mode_box" style="' . $display_payment_mode . '">
                           <label class="arm_padding_left_0">' . esc_html__( 'In case of subscription plan selected', 'armember-membership' ) . '</label>
                               <br/>
									<div class="arm_autodebit_only">
                                        <input name="setup_data[setup_modules][modules][payment_mode][' . esc_attr($key) . ']" value="auto_debit_subscription" type="radio" class="arm_iradio arm_' . esc_attr($key) . '_gateway_payment_mode_input" ' . $checked_auto . ' id="arm_' . esc_attr($key) . '_auto_mode"><label for="arm_' . esc_attr($key) . '_auto_mode">' . esc_html__( 'Allow Auto debit method only', 'armember-membership' ) . '</label>
									</div>
									<div class="arm_semi_autodebit_only">
                                        <input name="setup_data[setup_modules][modules][payment_mode][' . esc_attr($key) . ']" value="manual_subscription" type="radio" class="arm_iradio arm_' . esc_attr($key) . '_gateway_payment_mode_input" ' . $checked_manual . ' id= "arm_' . esc_attr($key) . '_manual_mode"><label for="arm_' . esc_attr($key) . '_manual_mode">' . esc_html__( 'Allow Semi Automatic(manual) method only', 'armember-membership' ) . '</label>
									</div>
									<div class="arm_both">
                                        <input name="setup_data[setup_modules][modules][payment_mode][' . esc_attr($key) . ']" value="both" type="radio" class="arm_iradio arm_' . esc_attr($key) . '_gateway_payment_mode_input" ' . $checked_both . ' id="arm_' . esc_attr($key) . '_both_mode">
                                        <label for="arm_' . esc_attr($key) . '_both_mode">' . esc_html__( 'Both (allow user to select payment method)', 'armember-membership' ) . '</label>
									</div>' . $gateway_note . '
                                    </div></div>';
					}
					if ( in_array( $key, $doNotDisplayPaymentMode ) ) {
						$gatewayList .= '<input name="setup_data[setup_modules][modules][payment_mode][' . esc_attr($key) . ']" value="manual_subscription" type="hidden" class="arm_iradio arm_' . esc_attr($key) . '_gateway_payment_mode_input" id= "arm_' . esc_attr($key) . '_manual_mode">';
					}
					$gatewayList .= '</div>';
				}
			}
			return $gatewayList;
		}

		function arm_total_setups() {
			global $wpdb,$ARMemberLite;
			$setup_count = $wpdb->get_var( 'SELECT COUNT(`arm_setup_id`) FROM ' . $ARMemberLite->tbl_arm_membership_setup); //phpcs:ignore --Reason: $ARMemberLite->tbl_arm_membership_setup is a table name and it counts total result no need to prepare. False Positive Alarm
			return $setup_count;
		}

		function arm_get_membership_setup( $setup_id = 0 ) {
			global $wp, $wpdb, $current_user, $ARMemberLite, $arm_global_settings;
			if ( is_numeric( $setup_id ) && $setup_id != 0 ) {
				/* Query Monitor Change */
				if ( isset( $GLOBALS['arm_setup_data'] ) && isset( $GLOBALS['arm_setup_data'][ $setup_id ] ) ) {
					$setup_data = $GLOBALS['arm_setup_data'][ $setup_id ];
				} else {
					$setup_data = $wpdb->get_row( $wpdb->prepare('SELECT * FROM `' . $ARMemberLite->tbl_arm_membership_setup . "` WHERE `arm_setup_id`=%d",$setup_id), ARRAY_A );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_membership_setup is a table name
					if ( ! isset( $GLOBALS['arm_setup_data'] ) ) {
						$GLOBALS['arm_setup_data'] = array();
					}
					$GLOBALS['arm_setup_data'][ $setup_id ] = $setup_data;
				}
				if ( ! empty( $setup_data ) ) {
					$setup_data['arm_setup_name']    = ( ! empty( $setup_data['arm_setup_name'] ) ) ? stripslashes( $setup_data['arm_setup_name'] ) : '';
					$setup_data['arm_setup_modules'] = maybe_unserialize( $setup_data['arm_setup_modules'] );
					$setup_data['arm_setup_labels']  = maybe_unserialize( $setup_data['arm_setup_labels'] );
					if(!empty($setup_data['arm_setup_labels'])){
						$setup_data['arm_setup_labels'] = stripslashes_deep($setup_data['arm_setup_labels']);
					}
					$setup_data['setup_name']        = $setup_data['arm_setup_name'];
					$setup_data['setup_modules']     = $setup_data['arm_setup_modules'];
					$setup_data['setup_labels']      = $setup_data['arm_setup_labels'];
					$setup_data['setup_type']        = $setup_data['arm_setup_type'];
				}
				return $setup_data;
			} else {
				return false;
			}
		}

		function arm_save_membership_setups_func( $posted_data = array() ) {

			global $wp, $wpdb, $current_user, $arm_slugs, $ARMemberLite, $arm_global_settings, $arm_payment_gateways, $ARMemberLiteAllowedHTMLTagsArray,$arm_capabilities_global;
			//$redirect_to = admin_url( 'admin.php?page=' . $arm_slugs->membership_setup );
			$response = array('status' => 'error','msg' => esc_html__('Something went wrong! Please try again','armember-membership'));

			$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST ); //phpcs:ignore

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_setups'], '1', 1 ); //phpcs:ignore --Reason:Verifying nonce
			if ( isset( $posted_data ) && ! empty( $posted_data ) && in_array( $posted_data['form_action'], array( 'add', 'update' ) ) ) {
				$setup_data = $posted_data['setup_data'];
				if ( ! empty( $setup_data ) ) {
					
					$setup_modules    = ( ! empty( $setup_data['setup_modules'] ) ) ? $setup_data['setup_modules'] : array();
					$setup_labels     = ( ! empty( $setup_data['setup_labels'] ) ) ? $setup_data['setup_labels'] : array();
					$setup_name       = ( ! empty( $setup_data['setup_name'] ) ) ? wp_kses($setup_data['setup_name'], $ARMemberLiteAllowedHTMLTagsArray ) : esc_html__( 'Untitled Setup', 'armember-membership' );

					$setup_labels['button_labels']['submit'] = wp_kses($setup_labels['button_labels']['submit'], $ARMemberLiteAllowedHTMLTagsArray);
					$setup_labels['payment_section_title'] = wp_kses($setup_labels['payment_section_title'], $ARMemberLiteAllowedHTMLTagsArray);
					$setup_labels['payment_gateway_labels']['paypal'] = wp_kses($setup_labels['payment_gateway_labels']['paypal'], $ARMemberLiteAllowedHTMLTagsArray);
					$setup_labels['payment_gateway_labels']['bank_transfer'] = wp_kses($setup_labels['payment_gateway_labels']['bank_transfer'], $ARMemberLiteAllowedHTMLTagsArray);
					$setup_labels['payment_mode_selection'] = wp_kses($setup_labels['payment_mode_selection'], $ARMemberLiteAllowedHTMLTagsArray);
					$setup_labels['automatic_subscription'] = wp_kses($setup_labels['automatic_subscription'], $ARMemberLiteAllowedHTMLTagsArray);
					$setup_labels['semi_automatic_subscription'] = wp_kses($setup_labels['semi_automatic_subscription'], $ARMemberLiteAllowedHTMLTagsArray);
					$setup_labels['summary_text'] = wp_kses($setup_labels['summary_text'], $ARMemberLiteAllowedHTMLTagsArray);


					$payment_gateways = $arm_payment_gateways->arm_get_all_payment_gateways_for_setup();
					foreach ( $payment_gateways as $pgkey => $gateway ) {
						if ( $setup_labels['payment_gateway_labels'][ $pgkey ] == '' ) {
							$setup_labels['payment_gateway_labels'][ $pgkey ] = $gateway['gateway_name'];
						}
					}
					if ( ! empty( $setup_modules['modules']['module_order'] ) ) {
						asort( $setup_modules['modules']['module_order'] );
					}

					$db_data = array(
						'arm_setup_name'    => $setup_name,
						'arm_setup_modules' => maybe_serialize( $setup_modules ),
						'arm_setup_labels'  => maybe_serialize( $setup_labels ),
					);
					if ( $posted_data['form_action'] == 'add' ) {
						$db_data['arm_status']       = 1;
						$db_data['arm_created_date'] = current_time( 'mysql' );
						/* Insert Form Fields. */
						$wpdb->insert( $ARMemberLite->tbl_arm_membership_setup, $db_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						$setup_id = $wpdb->insert_id;
						/* Action After Adding Setup Details */
						do_action( 'arm_saved_membership_setup', $setup_id, $db_data );
						$response = array('status' => 'success','msg' => esc_html__( 'Membership setup wizard has been added successfully.', 'armember-membership' ));
					} elseif ( $posted_data['form_action'] == 'update' && ! empty( $posted_data['id'] ) && $posted_data['id'] != 0 ) {
						$setup_id     = $posted_data['id'];
						$field_update = $wpdb->update( $ARMemberLite->tbl_arm_membership_setup, $db_data, array( 'arm_setup_id' => $setup_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						/* Action After Updating Setup Details */
						do_action( 'arm_saved_membership_setup', $setup_id, $db_data );
						$response = array('status' => 'success','msg' => esc_html__( 'Membership setup wizard has been updated successfully.', 'armember-membership' ));
					}
				}
				do_action( 'arm_save_membership_setups', $posted_data ); //phpcs:ignore
			}
			echo arm_pattern_json_encode( $response );
			die;
		}

		function arm_delete_single_setup() {
			global $wp, $wpdb, $current_user, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_capabilities_global;
			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_setups'], '1' ); //phpcs:ignore --Reason:Verifying nonce
			$action = sanitize_text_field( $_POST['act'] ); //phpcs:ignore
			$id     = intval( $_POST['id'] ); //phpcs:ignore
			if ( $action == 'delete' ) {
				if ( empty( $id ) ) {
					$errors[] = esc_html__( 'Invalid action.', 'armember-membership' );
				} else {
					if ( ! current_user_can( 'arm_manage_setups' ) ) {
						$errors[] = esc_html__( 'Sorry, You do not have permission to perform this action', 'armember-membership' );
					} else {
						$res_var = $wpdb->delete( $ARMemberLite->tbl_arm_membership_setup, array( 'arm_setup_id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						if ( $res_var ) {
							$message = esc_html__( 'Setup has been deleted successfully.', 'armember-membership' );
						}
					}
				}
			}
			$return_array = $arm_global_settings->handle_return_messages( @$errors, @$message );
			echo arm_pattern_json_encode( $return_array );
			exit;
		}

		function arm_generate_setup_shortcode_preview( $setupData = array(), $setupID = 0 ) {
			$setupForm = '';
			if ( ! empty( $args['setup_data'] ) ) {

				$setupForm .= '';
				$setupForm .= '';
				$setupForm .= '';
			}
			return $setupForm;
		}

		function arm_check_include_js_css( $setup_data, $atts ) {
			global $ARMemberLite;
			$form_style = '';
			if ( isset( $setup_data['setup_modules']['modules']['forms'] ) ) {
				$form_id = $setup_data['setup_modules']['modules']['forms'];
				$form = new ARM_Form_Lite( 'id', $form_id );
				$form_settings     = $form->settings;
				$form_style        = $form_settings['style']['form_layout'];
			}
			$ARMemberLite->set_front_css( false, $form_style );
			$ARMemberLite->set_front_js( true );
		}

		function arm_setup_skin_default_color_array() {
			$font_colors = array(
				'skin1' => array(
					'arm_setup_plan_title_font_color'     => '#2C2D42',
					'arm_setup_plan_desc_font_color'      => '#555F70',
					'arm_setup_price_font_color'          => '#2C2D42',
					'arm_setup_summary_font_color'        => '#555F70',
					'arm_setup_selected_plan_title_font_color' => '#005AEE',
					'arm_setup_selected_plan_desc_font_color' => '#2C2D42',
					'arm_setup_selected_price_font_color' => '#FFFFFF',
					'arm_setup_bg_active_color'           => '#005AEE',
				),
				'skin2' => array(
					'arm_setup_plan_title_font_color'     => '#2C2D42',
					'arm_setup_plan_desc_font_color'      => '#555F70',
					'arm_setup_price_font_color'          => '#2C2D42',
					'arm_setup_summary_font_color'        => '#555F70',
					'arm_setup_selected_plan_title_font_color' => '#FFFFFF',
					'arm_setup_selected_plan_desc_font_color' => '#2C2D42',
					'arm_setup_selected_price_font_color' => '#005AEE',
					'arm_setup_bg_active_color'           => '#005AEE',
				),
				'skin3' => array(
					'arm_setup_plan_title_font_color'     => '#2C2D42',
					'arm_setup_plan_desc_font_color'      => '#555F70',
					'arm_setup_price_font_color'          => '#2C2D42',
					'arm_setup_summary_font_color'        => '#555F70',
					'arm_setup_selected_plan_title_font_color' => '#005AEE',
					'arm_setup_selected_plan_desc_font_color' => '#2C2D42',
					'arm_setup_selected_price_font_color' => '#005AEE',
					'arm_setup_bg_active_color'           => '#005AEE',
				),
				'skin4' => array(
					'arm_setup_plan_title_font_color'     => '#2C2D42',
					'arm_setup_plan_desc_font_color'      => '#555F70',
					'arm_setup_price_font_color'          => '#2C2D42',
					'arm_setup_summary_font_color'        => '#555F70',
					'arm_setup_selected_plan_title_font_color' => '#FFFFFF',
					'arm_setup_selected_plan_desc_font_color' => '#FFFFFF',
					'arm_setup_selected_price_font_color' => '#FFFFFF',
					'arm_setup_bg_active_color'           => '#005AEE',
				),
				'skin5' => array(
					'arm_setup_plan_title_font_color'     => '#2C2D42',
					'arm_setup_plan_desc_font_color'      => '#555F70',
					'arm_setup_price_font_color'          => '#2C2D42',
					'arm_setup_summary_font_color'        => '#555F70',
					'arm_setup_selected_plan_title_font_color' => '#005AEE',
					'arm_setup_selected_plan_desc_font_color' => '#2C2D42',
					'arm_setup_selected_price_font_color' => '#FFFFFF',
					'arm_setup_bg_active_color'           => '#005AEE',
				),
				'skin6' => array(
					'arm_setup_plan_title_font_color'     => '#2C2D42',
					'arm_setup_plan_desc_font_color'      => '#555F70',
					'arm_setup_price_font_color'          => '#2C2D42',
					'arm_setup_summary_font_color'        => '#555F70',
					'arm_setup_selected_plan_title_font_color' => '#FFFFFF',
					'arm_setup_selected_plan_desc_font_color' => '#FFFFFF',
					'arm_setup_selected_price_font_color' => '#FFFFFF',
					'arm_setup_bg_active_color'           => '#005AEE',
				),
			);

			return apply_filters( 'arm_membership_setup_skin_colors', $font_colors );
		}

		function arm_setup_edit_detail_func()
		{
			global $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_manage_coupons, $arm_subscription_plans, $arm_membership_setup, $arm_member_forms, $arm_payment_gateways,$arm_capabilities_global;

            $ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_setups'], '1',1); //phpcs:ignore 

            $setup_id = intval($_REQUEST['id']);
            $setup_data = $arm_membership_setup->arm_get_membership_setup($setup_id);
            $button_labels = array(
                'submit' => esc_html__('Submit', 'armember-membership'),
                'next' => esc_html__('Next', 'armember-membership'),
                'previous' => esc_html__('Previous', 'armember-membership'),
            );
            $response = array();
            if ($setup_data !== FALSE && !empty($setup_data)) {
                $response['arm_setup_id'] = $setup_id;
                $response['setup_title'] = esc_html__("Edit Plan + Signup Page", 'armember-membership');
                $response['setup_action'] = 'update';
                $response['setup_name'] = $setup_data['setup_name'];
                $response['arm_setup_type'] = $setup_data['arm_setup_type'];
                $response['setup_modules'] = !empty($setup_data['setup_modules']) ? $setup_data['setup_modules'] : array();
                $response['two_step'] = !empty($setup_data['setup_modules']['style']['two_step']) ? $setup_data['setup_modules']['style']['two_step'] : 0;
                $response['hide_current_plans'] = (!empty($setup_data['setup_modules']['style']['hide_current_plans'])) ? $setup_data['setup_modules']['style']['hide_current_plans'] : 0;
                $response['hide_plan'] = (!empty($setup_data['setup_modules']['style']['hide_plans'])) ? $setup_data['setup_modules']['style']['hide_plans'] : 0; 
                $response['setup_labels'] = isset($setup_data['setup_labels']) ? stripslashes_deep($setup_data['setup_labels']): array();
				
                $response['button_labels'] = isset($setup_data['setup_labels']['button_labels']) ? stripslashes_deep($setup_data['setup_labels']['button_labels']) : $button_labels;
                $shortCode = '[arm_setup id="'.$setup_id.'"]';
                $response['shortcode_btn'] = '
                    <span class="armCopyText"> '.$shortCode.'</span>
                    <span class="arm_click_to_copy_text" data-code=\''.$shortCode.'\'>'. esc_html__('Click to copy', 'armember-membership').'</span>
                    <span class="arm_copied_text"><img src="'. MEMBERSHIPLITE_IMAGES_URL.'/copied_ok.png" alt="ok"/>'. esc_html__('Code Copied', 'armember-membership').'</span>';

                $response = apply_filters( 'arm_setup_form_custom_response', $response );
            }
            echo arm_pattern_json_encode( $response );
            die();
		}

	}

}

global $arm_membership_setup;
$arm_membership_setup = new ARM_membership_setup_Lite();
