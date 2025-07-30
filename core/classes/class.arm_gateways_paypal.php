<?php 
if ( ! class_exists( 'ARM_Paypal_Lite' ) ) {

	class ARM_Paypal_Lite {

		function __construct() {
			add_action( 'arm_payment_gateway_validation_from_setup', array( $this, 'arm_payment_gateway_form_submit_action' ), 10, 4 );
			add_action( 'wp', array( $this, 'arm_paypal_api_handle_response' ), 5 );
			add_action( 'arm_cancel_subscription_gateway_action', array( $this, 'arm_cancel_paypal_subscription' ), 10, 2 );
			add_filter( 'arm_update_new_subscr_gateway_outside', array( $this, 'arm_update_new_subscr_gateway_outside_func' ), 10 );
			add_filter( 'arm_change_pending_gateway_outside', array( $this, 'arm_change_pending_gateway_outside' ), 100, 3 );

			add_action( 'arm_after_cancel_subscription', array( $this, 'arm_cancel_subscription_instant' ), 100, 4 );
		}


		function arm_cancel_subscription_instant( $user_id, $plan, $cancel_plan_action, $planData ) {
			global $wpdb, $ARMemberLite, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication;

			$plan_id = $plan->ID;

			if ( empty( $planData ) ) {
				$planData = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
			}

			$payment_mode = ! empty( $planData['arm_payment_mode'] ) ? $planData['arm_payment_mode'] : '';
			$subscr_id    = ! empty( $planData['arm_subscr_id'] ) ? $planData['arm_subscr_id'] : '';

			$plan_cycle      = isset( $planData['arm_payment_cycle'] ) ? $planData['arm_payment_cycle'] : '';
			$paly_cycle_data = $plan->prepare_recurring_data( $plan_cycle );

			$user_payment_gateway = ! empty( $planData['arm_user_gateway'] ) ? $planData['arm_user_gateway'] : '';

			if ( ! empty( $subscr_id ) && strtolower( $user_payment_gateway ) == 'paypal' && ( $cancel_plan_action == 'on_expire' || $paly_cycle_data['rec_time'] == 'infinite' ) ) {
				$this->arm_immediate_cancel_paypal_payment( $subscr_id, $user_id, $plan_id, $planData );
			}
		}

		function arm_immediate_cancel_paypal_payment( $subscr_id, $user_id, $plan_id, $planData ) {
			 global $wpdb, $ARMemberLite, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication,$arm_lite_debug_payment_log_id;

			 do_action('arm_payment_log_entry', 'paypal', 'immediate cancel subscription ID', 'armember', $subscr_id, $arm_lite_debug_payment_log_id);

			try {
				$PayPal = self::arm_init_paypal();

				$PayPalCancelRequestData = array(
					'MRPPSFields' => array(
						'profileid' => $subscr_id,
						'action'    => urlencode( 'Cancel' ),
						'note'      => esc_html__( "Cancel User's Subscription.", 'armember-membership' ),
					),
				);
				$PayPalResult            = $PayPal->ManageRecurringPaymentsProfileStatus( $PayPalCancelRequestData );
				do_action('arm_payment_log_entry', 'paypal', 'immediate cancel subscription response', 'armember', $PayPalResult, $arm_lite_debug_payment_log_id);
				// $ARMemberLite->arm_write_response( 'Error in Paypal Result => ' . wp_json_encode( $PayPalResult ) );
				if ( ! is_wp_error( $PayPalResult ) && isset( $PayPalResult['ACK'] ) && strtolower( $PayPalResult['ACK'] ) == 'success' ) {
					$planData['arm_subscr_id'] = '';
					update_user_meta( $user_id, 'arm_user_plan_' . $plan_id, $planData );
				}
			} catch ( Exception $e ) {
				do_action('arm_payment_log_entry', 'paypal', 'cancel subscription error', 'armember', $e->getMessage(), $arm_lite_debug_payment_log_id);
				// $ARMemberLite->arm_write_response( 'Error in Paypal Plan Cancel => ' . wp_json_encode( $e ) );
			}
		}

		function arm_init_paypal() {
			global $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways;
			if ( file_exists( MEMBERSHIPLITE_DIR . '/lib/paypal/paypal.class.php' ) ) {
				require_once MEMBERSHIPLITE_DIR . '/lib/paypal/paypal.class.php';
			}
			/* ---------------------------------------------------------------------------- */
			$all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
			if ( isset( $all_payment_gateways['paypal'] ) && ! empty( $all_payment_gateways['paypal'] ) ) {
				$paypal_options = $all_payment_gateways['paypal'];
				// Set Paypal Currency
				$currency = $arm_payment_gateways->arm_get_global_currency();
				$sandbox  = ( isset( $paypal_options['paypal_payment_mode'] ) && $paypal_options['paypal_payment_mode'] == 'sandbox' ) ? true : false;
				/** Set API Credentials */
				$developer_account_email = $paypal_options['paypal_merchant_email'];
				$api_username            = $sandbox ? $paypal_options['sandbox_api_username'] : $paypal_options['live_api_username'];
				$api_password            = $sandbox ? $paypal_options['sandbox_api_password'] : $paypal_options['live_api_password'];
				$api_signature           = $sandbox ? $paypal_options['sandbox_api_signature'] : $paypal_options['live_api_signature'];
				/* ---------------------------------------------------------------------------- */
				$PayPalConfig        = array(
					'Sandbox'      => $sandbox,
					'APIUsername'  => $api_username,
					'APIPassword'  => $api_password,
					'APISignature' => $api_signature,
				);
				$PayPal              = new PayPal( $PayPalConfig );
				$PayPal->ARMcurrency = $currency;
				$PayPal->ARMsandbox  = $sandbox;
			} else {
				$PayPal = false;
			}
			return $PayPal;
		}

		function arm_generate_paypal_form( $plan_action = 'new_subscription', $plan_id = 0, $entry_id = 0, $coupon_code = '', $form_type = 'new', $setup_id = 0, $payment_mode = 'manual_subscription' ) {
			global $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_subscription_plans,  $arm_payment_gateways, $arm_membership_setup, $is_free_manual;
			$paypal_form    = '';
			$is_free_manual = false;
			if ( ! empty( $plan_id ) && $plan_id != 0 && ! empty( $entry_id ) && $entry_id != 0 ) {

				$all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
				if ( isset( $all_payment_gateways['paypal'] ) && ! empty( $all_payment_gateways['paypal'] ) ) {
					$paypal_options = $all_payment_gateways['paypal'];
					// Set Paypal Callback URLs

					$arf_pyapal_home_url = ARMLITE_HOME_URL . '/';

					if ( strstr( $arf_pyapal_home_url, '?' ) ) {
						$notify_url = $arf_pyapal_home_url . '&arm-listener=arm_paypal_api';

					} else {
						$notify_url = $arf_pyapal_home_url . '?arm-listener=arm_paypal_api';
					}

					$globalSettings = $arm_global_settings->global_settings;
					$cp_page_id     = isset( $globalSettings['cancel_payment_page_id'] ) ? $globalSettings['cancel_payment_page_id'] : 0;

					$default_cancel_url = $arm_global_settings->arm_get_permalink( '', $cp_page_id );

					$cancel_url = ( ! empty( $paypal_options['cancel_url'] ) ) ? $paypal_options['cancel_url'] : $default_cancel_url;
					if ( $cancel_url == '' || empty( $cancel_url ) ) {
						$cancel_url = ARMLITE_HOME_URL;
					}
					// Get Entry Detail
					$entry_data = $arm_payment_gateways->arm_get_entry_data_by_id( $entry_id );

					if ( ! empty( $entry_data ) ) {
						$user_email     = $entry_data['arm_entry_email'];
						$form_id        = $entry_data['arm_form_id'];
						$user_id        = $entry_data['arm_user_id'];
						$entry_values   = maybe_unserialize( $entry_data['arm_entry_value'] );
						$return_url     = $entry_values['setup_redirect'];
						$user_detail    = get_userdata( $user_id );
						$arm_first_name = isset($user_detail->first_name) ? $user_detail->first_name : '';
						$arm_last_name  = isset($user_detail->last_name) ? $user_detail->last_name : '';
						if ( empty( $return_url ) ) {
							$return_url = ARMLITE_HOME_URL;
						}

						$arm_user_selected_payment_cycle = $entry_values['arm_selected_payment_cycle'];
						$arm_user_old_plan               = ( isset( $entry_values['arm_user_old_plan'] ) && ! empty( $entry_values['arm_user_old_plan'] ) ) ? explode( ',', $entry_values['arm_user_old_plan'] ) : array();
						$arm_is_trial                    = '0';

						$sandbox = ( isset( $paypal_options['paypal_payment_mode'] ) && $paypal_options['paypal_payment_mode'] == 'sandbox' ) ? 'sandbox.' : '';
						// Set Paypal Currency
						$currency = $arm_payment_gateways->arm_get_global_currency();
						$plan     = new ARM_Plan_Lite( $plan_id );

						$defaultPlanData  = $arm_subscription_plans->arm_default_plan_array();
						$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
						$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
						$planData         = shortcode_atts( $defaultPlanData, $userPlanDatameta );

						if ( $plan_action == 'renew_subscription' && $plan->is_recurring() ) {
							$is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user( $user_id, $plan_id, $payment_mode );
							if ( $is_recurring_payment ) {
								$plan_action   = 'recurring_payment';
								$oldPlanDetail = $planData['arm_current_plan_detail'];
								if ( ! empty( $oldPlanDetail ) ) {
									$plan = new ARM_Plan_Lite( 0 );
									$plan->init( (object) $oldPlanDetail );
								}
							}
						}

						$plan_payment_type = $plan->payment_type;
						// Set Custom Variable.
						$custom_var = $entry_id . '|' . $user_email . '|' . $plan_payment_type;
						// Set Amount to be paid

						if ( $plan->is_recurring() ) {
							$plan_data = $plan->prepare_recurring_data( $arm_user_selected_payment_cycle );
							$amount    = $plan_data['amount'];
						} else {
							$amount = $plan->amount;
						}

						$amount = str_replace( ',', '', $amount );

						if ( $currency == 'HUF' || $currency == 'JPY' || $currency == 'TWD' ) {
							$amount = number_format( (float) $amount, 0, '', '' );
						} else {
							$amount = number_format( (float) $amount, 2, '.', '' );
						}

						$discount_amt = 0;

						$plan_form_data = '';

						if ( $plan->is_recurring() && $payment_mode == 'auto_debit_subscription' ) {

							$cmd = '_xclick-subscriptions';
							// Recurring Options
							$recurring_data = $plan->prepare_recurring_data( $arm_user_selected_payment_cycle );
							$recur_period   = $recurring_data['period'];
							$recur_interval = $recurring_data['interval'];
							$recur_cycles   = $recurring_data['cycles'];

							// Trial Period Options
							$is_trial = false;
							if ( ! empty( $recurring_data['trial'] ) && $plan_action == 'new_subscription' ) {
								$is_trial       = true;
								$arm_is_trial   = '1';
								$trial_amount   = $recurring_data['trial']['amount'];
								$trial_period   = $recurring_data['trial']['period'];
								$trial_interval = $recurring_data['trial']['interval'];
							}

							$remained_days = 0;
							if ( $plan_action == 'renew_subscription' ) {
								$user_plan_data   = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
								$plan_expiry_date = $user_plan_data['arm_expire_plan'];
								$now              = strtotime( current_time( 'mysql' ) );

								$remained_days = ceil( abs( $plan_expiry_date - $now ) / 86400 );

								if ( $remained_days > 0 ) {
									$trial_amount   = 0;
									$trial_interval = $remained_days;
									$trial_period   = 'D';
								}
							}

							$trial_amount = isset( $trial_amount ) ? str_replace( ',', '', $trial_amount ) : 0;
							if ( $currency == 'HUF' || $currency == 'JPY' || $currency == 'TWD' ) {
								$trial_amount = number_format( (float) $trial_amount, 0, '', '' );
							} else {
								$trial_amount = number_format( (float) $trial_amount, 2, '.', '' );
							}

							$plan_form_data .= '<input type="hidden" name="a3" value="' . esc_attr( $amount ) . '" />';
							$plan_form_data .= '<input type="hidden" name="p3" value="' . esc_attr( $recur_interval ) . '" />';
							$plan_form_data .= '<input type="hidden" name="t3" value="' . esc_attr( $recur_period ) . '" />';
							// PayPal re-attempts failed recurring payments
							$plan_form_data .= '<input type="hidden" name="sra" value="1" />';
							// Set recurring payments until cancelled.
							$plan_form_data .= '<input type="hidden" name="src" value="1" />';
							$plan_form_data .= '<input type="hidden" name="no_note" value="1" />';
							$modify_val      = ( $form_type == 'modify' ) ? '1' : '0';
							$plan_form_data .= '<input type="hidden" name="modify" value="' . esc_attr( $modify_val ) . '" />';
							if ( $recur_cycles > 1 ) {
								// Set recurring payments to stop after X billing cycles
								$plan_form_data .= '<input type="hidden" name="srt" value="' . esc_attr( $recur_cycles ) . '" />';
							}
							if ( $is_trial && $plan_action == 'new_subscription' || $remained_days > 0 ) {
								$plan_form_data .= '<input type="hidden" name="a1" value="' . esc_attr( $trial_amount ) . '" />';
								$plan_form_data .= '<input type="hidden" name="p1" value="' . esc_attr( $trial_interval ) . '" />';
								$plan_form_data .= '<input type="hidden" name="t1" value="' . esc_attr( $trial_period ) . '" />';
							}
						} elseif ( $plan->is_recurring() && $payment_mode == 'manual_subscription' ) {

							$cmd            = '_xclick';
							$recurring_data = $plan->prepare_recurring_data( $arm_user_selected_payment_cycle );
							$recur_period   = $recurring_data['period'];
							$recur_interval = $recurring_data['interval'];
							$recur_cycles   = $recurring_data['cycles'];

							// Trial Period Options
							$is_trial    = false;
							$allow_trial = true;
							if ( is_user_logged_in() ) {
								$user_id   = get_current_user_id();
								$user_plan = get_user_meta( $user_id, 'arm_user_plan_ids', true );
								if ( ! empty( $user_plan ) ) {
									$allow_trial = false;
								}
							}
							if ( $plan->has_trial_period() && $allow_trial ) {

								$is_trial       = true;
								$arm_is_trial   = '1';
								$trial_amount   = $recurring_data['trial']['amount'];
								$trial_period   = $recurring_data['trial']['period'];
								$trial_interval = $recurring_data['trial']['interval'];
							} else {

								$trial_amount = $amount;
							}

							if ( $trial_amount == 0 || $trial_amount == '0.00' ) {
								$return_array = $extraParam = array();
								if ( is_user_logged_in() ) {
									$current_user_id             = get_current_user_id();
									$return_array['arm_user_id'] = $current_user_id;
								}
								$return_array['arm_first_name']               = $arm_first_name;
								$return_array['arm_last_name']                = $arm_last_name;
								$return_array['arm_plan_id']                  = $plan->ID;
								$return_array['arm_payment_gateway']          = 'paypal';
								$return_array['arm_payment_type']             = $plan->payment_type;
								$return_array['arm_token']                    = '-';
								$return_array['arm_payer_email']              = $user_email;
								$return_array['arm_receiver_email']           = '';
								$return_array['arm_transaction_id']           = '-';
								$return_array['arm_transaction_payment_type'] = $plan->payment_type;
								$return_array['arm_transaction_status']       = 'completed';
								$return_array['arm_payment_mode']             = 'manual_subscription';
								$return_array['arm_payment_date']             = current_time( 'mysql' );
								$return_array['arm_amount']                   = 0;
								$return_array['arm_currency']                 = $currency;

								$return_array['arm_extra_vars']    = '';
								$return_array['arm_is_trial']      = $arm_is_trial;
								$return_array['arm_created_date']  = current_time( 'mysql' );
								$payment_log_id                    = $arm_payment_gateways->arm_save_payment_log( $return_array );
								$is_free_manual                    = true;
								do_action( 'arm_after_paypal_free_manual_payment', $plan, $payment_log_id, $arm_is_trial, '', $extraParam );
								return array(
									'status'   => true,
									'log_id'   => $payment_log_id,
									'entry_id' => $entry_id,
								);
							}
							$plan_form_data .= "<input type='hidden' name='amount' value='" . esc_attr( $trial_amount ) . "' />";
						} else {

							$cmd = '_xclick';

							if ( $amount == 0 || $amount == '0.00' ) {
								$return_array = array();
								if ( is_user_logged_in() ) {
									$current_user_id             = get_current_user_id();
									$return_array['arm_user_id'] = $current_user_id;
								}
								$return_array['arm_first_name']               = $arm_first_name;
								$return_array['arm_last_name']                = $arm_last_name;
								$return_array['arm_plan_id']                  = $plan->ID;
								$return_array['arm_payment_gateway']          = 'paypal';
								$return_array['arm_payment_type']             = $plan->payment_type;
								$return_array['arm_token']                    = '-';
								$return_array['arm_payer_email']              = $user_email;
								$return_array['arm_receiver_email']           = '';
								$return_array['arm_transaction_id']           = '-';
								$return_array['arm_transaction_payment_type'] = $plan->payment_type;
								$return_array['arm_transaction_status']       = 'completed';
								$return_array['arm_payment_mode']             = '';
								$return_array['arm_payment_date']             = current_time( 'mysql' );
								$return_array['arm_amount']                   = 0;
								$return_array['arm_currency']                 = $currency;

								$return_array['arm_extra_vars']    = '';
								$return_array['arm_is_trial']      = $arm_is_trial;
								$return_array['arm_created_date']  = current_time( 'mysql' );
								$payment_log_id                    = $arm_payment_gateways->arm_save_payment_log( $return_array );
								$is_free_manual                    = true;
								do_action( 'arm_after_paypal_free_payment', $plan, $payment_log_id, $arm_is_trial, '', $extraParam );
								return array(
									'status'   => true,
									'log_id'   => $payment_log_id,
									'entry_id' => $entry_id,
								);
							}
							$plan_form_data .= '<input type="hidden" name="amount" value="' . esc_attr( $amount ) . '" />';
						}
						$arm_paypal_language = isset( $paypal_options['language'] ) ? $paypal_options['language'] : 'en_US';
						$paypal_form         = '<form name="_xclick" id="arm_paypal_form" action="https://www.' . $sandbox . 'paypal.com/cgi-bin/webscr" method="post">';
						$paypal_form        .= '<input type="hidden" name="cmd" value="' . esc_attr( $cmd ) . '" />';
						$paypal_form        .= '<input type="hidden" name="business" value="' . esc_attr( $paypal_options['paypal_merchant_email'] ) . '" />';
						$paypal_form        .= '<input type="hidden" name="notify_url" value="' . esc_url( $notify_url ) . '" />';
						$paypal_form        .= '<input type="hidden" name="cancel_return" value="' . esc_url( $cancel_url ) . '" />';
						$paypal_form        .= '<input type="hidden" name="return" value="' . esc_url( $return_url ) . '" />';
						$paypal_form        .= '<input type="hidden" name="rm" value="2" />';
						$paypal_form        .= '<input type="hidden" name="lc" value="' . esc_attr( $arm_paypal_language ) . '" />';
						$paypal_form        .= '<input type="hidden" name="no_shipping" value="1" />';
						$paypal_form        .= '<input type="hidden" name="custom" value="' . esc_attr( $custom_var ) . '" />';
						$paypal_form        .= '<input type="hidden" name="on0" value="user_email" />';
						$paypal_form        .= '<input type="hidden" name="os0" value="' . esc_attr( $user_email ) . '" />';
						// $paypal_form .= '<input type="hidden" name="on1" value="user_plan">';
						// $paypal_form .= '<input type="hidden" name="os1" value="' . esc_attr( $plan_id ) . '">';
						$paypal_form .= '<input type="hidden" name="currency_code" value="' . esc_attr( $currency ) . '" />';
						$paypal_form .= '<input type="hidden" name="page_style" value="primary" />';
						$paypal_form .= '<input type="hidden" name="charset" value="UTF-8" />';
						$paypal_form .= '<input type="hidden" name="item_name" value="' . esc_attr( $plan->name ) . '" />';
						$paypal_form .= '<input type="hidden" name="item_number" value="1" />';
						$paypal_form .= '<input type="submit" style="display:none;" name="cbt" value="' . esc_html__( 'Click here to continue', 'armember-membership' ) . '" />';
						$paypal_form .= $plan_form_data;
						$paypal_form .= '<input type="submit" value="Pay with PayPal!" style="display:none;" />';
						$paypal_form .= '</form>';
						$paypal_form .= '<script data-cfasync="false" type="text/javascript" language="javascript">document.getElementById("arm_paypal_form").submit();</script>';
					}
				}
			}
			return $paypal_form;
		}

		function arm_payment_gateway_form_submit_action( $payment_gateway, $payment_gateway_options, $posted_data, $entry_id = 0 ) {
			global $wpdb, $ARMemberLite, $arm_global_settings, $arm_subscription_plans, $arm_member_forms,  $payment_done, $arm_payment_gateways,$arm_lite_debug_payment_log_id;

			if ( $payment_gateway == 'paypal' ) {
				$plan_id = ( ! empty( $posted_data['subscription_plan'] ) ) ? $posted_data['subscription_plan'] : 0;
				if ( $plan_id == 0 ) {
					$plan_id = ( ! empty( $posted_data['_subscription_plan'] ) ) ? $posted_data['_subscription_plan'] : 0;
				}

				$arm_posted_data = $posted_data;
				if(!empty($arm_posted_data) && isset($arm_posted_data['user_pass'])){
					unset($arm_posted_data['user_pass']);
				}
				if(!empty($arm_posted_data) && isset($arm_posted_data['repeat_pass'])){
					unset($arm_posted_data['repeat_pass']);
				}

				do_action('arm_payment_log_entry', $payment_gateway, 'payment form submit', 'armember', $arm_posted_data, $arm_lite_debug_payment_log_id);

				$plan = new ARM_Plan_Lite( $plan_id );

				$plan_action = 'new_subscription';

				$oldPlanIdArray = ( isset( $posted_data['old_plan_id'] ) && ! empty( $posted_data['old_plan_id'] ) ) ? explode( ',', $posted_data['old_plan_id'] ) : 0;
				if ( ! empty( $oldPlanIdArray ) ) {
					if ( in_array( $plan_id, $oldPlanIdArray ) ) {
						$plan_action = 'renew_subscription';
					} else {
						$plan_action = 'change_subscription';
					}
				}

				if ( $plan->is_recurring() ) {
					$setup_id      = $posted_data['setup_id'];
					$payment_mode_ = ! empty( $posted_data['arm_selected_payment_mode'] ) ? $posted_data['arm_selected_payment_mode'] : 'manual_subscription';
					if ( isset( $posted_data['arm_payment_mode']['paypal'] ) ) {
						$payment_mode_ = ! empty( $posted_data['arm_payment_mode']['paypal'] ) ? $posted_data['arm_payment_mode']['paypal'] : 'manual_subscription';
					} else {
						$setup_data = $arm_membership_setup->arm_get_membership_setup( $setup_id );
						if ( ! empty( $setup_data ) && ! empty( $setup_data['setup_modules']['modules'] ) ) {
							$setup_modules = $setup_data['setup_modules'];
							$modules       = $setup_modules['modules'];
							$payment_mode_ = $modules['payment_mode']['paypal'];
						}
					}
					$payment_mode = 'manual_subscription';
					if ( $payment_mode_ == 'both' ) {
						$payment_mode = ! empty( $posted_data['arm_selected_payment_mode'] ) ? $posted_data['arm_selected_payment_mode'] : 'manual_subscription';
					} else {
						$payment_mode = $payment_mode_;
					}
				} else {
					$payment_mode = '';
				}

				$setup_id = $posted_data['setup_id'];

				$paypal_form = self::arm_generate_paypal_form( $plan_action, $plan_id, $entry_id, '', 'new', $setup_id, $payment_mode );

				do_action('arm_payment_log_entry', $payment_gateway, 'payment form redirected data', 'armember', $paypal_form, $arm_lite_debug_payment_log_id);

				if ( is_array( $paypal_form ) ) {

					global $payment_done;
					$payment_done                     = $paypal_form;
					$payment_done['zero_amount_paid'] = true;
					return $payment_done;
				} elseif ( isset( $posted_data['action'] ) && in_array( $posted_data['action'], array( 'arm_shortcode_form_ajax_action', 'arm_membership_setup_form_ajax_action' ) ) ) {

					$return = array(
						'status'  => 'success',
						'type'    => 'redirect',
						'message' => $paypal_form,
					);
					echo wp_json_encode( $return );
					exit;
				} else {

					echo $paypal_form; //phpcs:ignore
					exit;
				}
			}
		}

		function arm_paypal_api_handle_response() {
			global $wpdb, $ARMemberLite, $arm_global_settings, $arm_members_class, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication, $payment_done,$arm_lite_debug_payment_log_id;

			if ( isset( $_REQUEST['arm-listener'] ) && in_array( $_REQUEST['arm-listener'], array( 'arm_paypal_api', 'arm_paypal_notify' ) ) ) { //phpcs:ignore

				$payment_gateway = 'paypal';
                do_action('arm_payment_log_entry', $payment_gateway, 'webhook data', 'payment_gateway', $_REQUEST, $arm_lite_debug_payment_log_id); //phpcs:ignore

				if ( ! empty( $_POST['txn_id'] ) || ! empty( $_POST['subscr_id'] ) ) { //phpcs:ignore
					$req = 'cmd=_notify-validate';
					foreach ( $_POST as $key => $value ) { //phpcs:ignore
						$value = urlencode( stripslashes( $value ) );
						$req  .= "&$key=$value";
					}
					$all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
					if ( isset( $all_payment_gateways['paypal'] ) && ! empty( $all_payment_gateways['paypal'] ) ) {
						$options = $all_payment_gateways['paypal'];
						$request = new WP_Http();
						/* For HTTP1.0 Request */
						$requestArr = array(
							'sslverify' => false,
							'ssl'       => true,
							'body'      => $req,
							'timeout'   => 20,
						);
						/* For HTTP1.1 Request */
						$requestArr_1_1 = array(
							'httpversion' => '1.1',
							'sslverify'   => false,
							'ssl'         => true,
							'body'        => $req,
							'timeout'     => 20,
						);
						$response       = array();
						if ( isset( $options['paypal_payment_mode'] ) && $options['paypal_payment_mode'] == 'sandbox' ) {
							$url          = 'https://www.sandbox.paypal.com/cgi-bin/webscr/';
							$response_1_1 = $request->post( $url, $requestArr_1_1 );
							if ( ! is_wp_error( $response_1_1 ) && $response_1_1['body'] == 'VERIFIED' ) {
								$response = $response_1_1;
							} else {
								$response = $request->post( $url, $requestArr );
							}
						} else {
							$url          = 'https://www.paypal.com/cgi-bin/webscr/';
							$response_1_0 = $request->post( $url, $requestArr );
							if ( ! is_wp_error( $response_1_0 ) && $response_1_0['body'] == 'VERIFIED' ) {
								$response = $response_1_0;
							} else {
								$response = $request->post( $url, $requestArr_1_1 );
							}
						}
						do_action('arm_payment_log_entry', $payment_gateway, 'verified webhook response', 'payment_gateway', $response, $arm_lite_debug_payment_log_id);

						if ( ! is_wp_error( $response ) && $response['body'] == 'VERIFIED' ) {
							$paypalLog        = $_POST; //phpcs:ignore
							$customs          = explode( '|', $_POST['custom'] ); //phpcs:ignore
							$entry_id         = $customs[0];
							$entry_email      = $customs[1];
							$arm_payment_type = $customs[2];
							$txn_id           = isset( $_POST['txn_id'] ) ? $_POST['txn_id'] : ''; //phpcs:ignore
							$arm_token        = isset( $_POST['subscr_id'] ) ? $_POST['subscr_id'] : ''; //phpcs:ignore
							$txn_type         = isset( $_POST['txn_type'] ) ? $_POST['txn_type'] : ''; //phpcs:ignore
							/**                             * ***********************************************
							 * Do Member Form Action After Successfull Payment
							 * ************************************************ */
							$user_id = 0;

							$entry_data = $wpdb->get_row( $wpdb->prepare('SELECT `arm_entry_id`, `arm_entry_email`, `arm_entry_value`, `arm_form_id`, `arm_user_id`, `arm_plan_id` FROM `' . $ARMemberLite->tbl_arm_entries . "` WHERE `arm_entry_id`=%d AND `arm_entry_email`=%s",$entry_id,$entry_email), ARRAY_A );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_entries is a table name

							if ( ! empty( $entry_data ) ) {
								$is_log            = false;
								$extraParam        = array(
									'plan_amount' => $_POST['mc_gross'],	 //phpcs:ignore
									'paid_amount' => $_POST['mc_gross'],	 //phpcs:ignore
								);
								$entry_values      = maybe_unserialize( $entry_data['arm_entry_value'] );
								$payment_mode      = $entry_values['arm_selected_payment_mode'];
								$payment_cycle     = $entry_values['arm_selected_payment_cycle'];
								$arm_user_old_plan = ( isset( $entry_values['arm_user_old_plan'] ) && ! empty( $entry_values['arm_user_old_plan'] ) ) ? explode( ',', $entry_values['arm_user_old_plan'] ) : array();
								$setup_id          = $entry_values['setup_id'];
								$entry_plan        = $entry_data['arm_plan_id'];

								$paypalLog['arm_payment_type'] = $arm_payment_type;
								$extraParam['arm_is_trial']    = '0';
								$extraParam['subs_id']         = $arm_token;
								$extraParam['trans_id']        = isset( $_POST['txn_id'] ) ? $_POST['txn_id'] : '';		 //phpcs:ignore
								$extraParam['error']           = isset( $_POST['txn_type'] ) ? $_POST['txn_type'] : '';	 //phpcs:ignore
								$extraParam['date']            = current_time( 'mysql' );
								$extraParam['message_type']    = isset( $_POST['txn_type'] ) ? $_POST['txn_type'] : '';	 //phpcs:ignore

								$user_info          = get_user_by( 'email', $entry_email );
								$do_not_update_user = true;
								if ( $user_info ) {
									$user_id = $user_info->ID;

									$log_id = $wpdb->get_var( $wpdb->prepare('SELECT `arm_log_id` FROM `' . $ARMemberLite->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_transaction_id`=%s AND `arm_transaction_status` = %s AND `arm_payment_gateway` = %s",$user_id,$txn_id,'pending','paypal') );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name

									if ( $log_id != '' ) {
										$payment_history_data                           = array();
										$payment_history_data['arm_transaction_status'] = 'success';
										$field_update                                   = $wpdb->update( $ARMemberLite->tbl_arm_payment_log, $payment_history_data, array( 'arm_log_id' => $log_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
										$do_not_update_user                             = false;
									}
								}

								if ( $do_not_update_user ) {

									switch ( $_POST['txn_type'] ) { //phpcs:ignore
										case 'subscr_signup':
											/*
											 * Only Create user or update membership when trial period option is enable
											 */
											if ( isset( $_POST['mc_amount1'] ) && $_POST['mc_amount1'] == 0 ) { //phpcs:ignore

												$extraParam = array(
													'plan_amount' => $_POST['mc_amount3'],	 //phpcs:ignore
													'paid_amount' => $_POST['mc_amount1'],	 //phpcs:ignore
												);
												$form_id    = $entry_data['arm_form_id'];

												$armform   = new ARM_Form_Lite( 'id', $form_id );
												$user_info = get_user_by( 'email', $entry_email );
												$new_plan  = new ARM_Plan_Lite( $entry_plan );

												if ( $new_plan->is_recurring() ) {
													if ( in_array( $entry_plan, $arm_user_old_plan ) ) {
														$is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user( $user_id, $entry_plan, $payment_mode );
														if ( $is_recurring_payment ) {
															$planData      = get_user_meta( $user_id, 'arm_user_plan_' . $entry_plan, true );
															$oldPlanDetail = $planData['arm_current_plan_detail'];
															if ( ! empty( $oldPlanDetail ) ) {
																$plan = new ARM_Plan_Lite( 0 );
																$plan->init( (object) $oldPlanDetail );
																$plan_data                 = $plan->prepare_recurring_data( $payment_cycle );
																$extraParam['plan_amount'] = $plan_data['amount'];
															}
														} else {
															$plan_data                 = $new_plan->prepare_recurring_data( $payment_cycle );
															$extraParam['plan_amount'] = $plan_data['amount'];
														}
													} else {
														$plan_data                 = $new_plan->prepare_recurring_data( $payment_cycle );
														$extraParam['plan_amount'] = $plan_data['amount'];
													}
												} else {
													$extraParam['plan_amount'] = $new_plan->amount;
												}

												$recurring_data = $new_plan->prepare_recurring_data( $payment_cycle );
												if ( ! empty( $recurring_data['trial'] ) && empty( $arm_user_old_plan ) ) {
													$extraParam['trial']        = array(
														'amount' => $recurring_data['trial']['amount'],
														'period' => $recurring_data['trial']['period'],
														'interval' => $recurring_data['trial']['interval'],
													);
													$extraParam['arm_is_trial'] = '1';
												}

												$defaultPlanData  = $arm_subscription_plans->arm_default_plan_array();
												$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $entry_plan, true );
												$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
												$userPlanData     = shortcode_atts( $defaultPlanData, $userPlanDatameta );

												if ( ! $user_info && in_array( $armform->type, array( 'registration' ) ) ) {

													$payment_log_id = self::arm_store_paypal_log( $paypalLog, 0, $entry_plan, $extraParam, $payment_mode );
													$payment_done   = array();
													if ( $payment_log_id ) {
														$payment_done = array(
															'status' => true,
															'log_id' => $payment_log_id,
															'entry_id' => $entry_id,
														);
													}
													$entry_values['payment_done']                 = '1';
													$entry_values['arm_entry_id']                 = $entry_id;
													$entry_values['arm_update_user_from_profile'] = 0;
													$user_id                                      = $arm_member_forms->arm_register_new_member( $entry_values, $armform );
													if ( is_numeric( $user_id ) && ! is_array( $user_id ) ) {
														if ( $arm_payment_type == 'subscription' ) {

															$defaultPlanData  = $arm_subscription_plans->arm_default_plan_array();
															$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $entry_plan, true );
															$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
															$userPlanData     = shortcode_atts( $defaultPlanData, $userPlanDatameta );

															$userPlanData['arm_subscr_id'] = $arm_token;
															update_user_meta( $user_id, 'arm_user_plan_' . $entry_plan, $userPlanData );
														}
														update_user_meta( $user_id, 'arm_entry_id', $entry_id );

													}
												} else {

													$user_id = $user_info->ID;
													if ( ! empty( $user_id ) ) {

														$userPlanData['arm_payment_mode']  = $entry_values['arm_selected_payment_mode'];
														$userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];
														$is_update_plan                    = true;

															$now                     = current_time( 'mysql' );
															$arm_last_payment_status = $wpdb->get_var( $wpdb->prepare( 'SELECT `arm_transaction_status` FROM `' . $ARMemberLite->tbl_arm_payment_log . '` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1', $user_id, $entry_plan, $now ) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name

															$old_plan_ids = get_user_meta( $user_id, 'arm_user_plan_ids', true );
															$old_plan_ids = ! empty( $old_plan_ids ) ? $old_plan_ids : array();
														if ( ! empty( $old_plan_ids ) ) {
															$old_plan_id   = isset( $old_plan_ids[0] ) ? $old_plan_id[0] : 0;
															$oldPlanDetail = array();
															if ( ! empty( $old_plan_id ) ) {
																$oldPlanData      = get_user_meta( $user_id, 'arm_user_plan_' . $old_plan_id, true );
																$oldPlanData      = ! empty( $oldPlanData ) ? $oldPlanData : array();
																$oldPlanData      = shortcode_atts( $defaultPlanData, $oldPlanData );
																$oldPlanDetail    = $oldPlanData['arm_current_plan_detail'];
																$subscr_effective = $oldPlanData['arm_expire_plan'];
															}

															if ( ! empty( $oldPlanDetail ) ) {
																$old_plan = new ARM_Plan_Lite( 0 );
																$old_plan->init( (object) $oldPlanDetail );
															} else {
																$old_plan = new ARM_Plan_Lite( $old_plan_id );
															}

															if ( $old_plan->exists() ) {
																if ( $old_plan->is_lifetime() || $old_plan->is_free() || ( $old_plan->is_recurring() && $new_plan->is_recurring() ) ) {
																	$is_update_plan = true;
																} else {
																	$change_act = 'immediate';
																	if ( $old_plan->enable_upgrade_downgrade_action == 1 ) {
																		if ( ! empty( $old_plan->downgrade_plans ) && in_array( $new_plan->ID, $old_plan->downgrade_plans ) ) {
																			$change_act = $old_plan->downgrade_action;
																		}
																		if ( ! empty( $old_plan->upgrade_plans ) && in_array( $new_plan->ID, $old_plan->upgrade_plans ) ) {
																			$change_act = $old_plan->upgrade_action;
																		}
																	}

																	if ( $change_act == 'on_expire' && ! empty( $subscr_effective ) ) {
																		$is_update_plan                      = false;
																		$oldPlanData['arm_subscr_effective'] = $subscr_effective;
																		$oldPlanData['arm_change_plan_to']   = $entry_plan;
																		update_user_meta( $user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData );
																	}
																}
															}
														}

														update_user_meta( $user_id, 'arm_entry_id', $entry_id );
														$userPlanData['arm_user_gateway'] = 'paypal';

														if ( ! empty( $arm_token ) ) {
															$userPlanData['arm_subscr_id'] = $arm_token;
														}

														update_user_meta( $user_id, 'arm_user_plan_' . $entry_plan, $userPlanData );

														if ( $is_update_plan ) {
															$arm_subscription_plans->arm_update_user_subscription( $user_id, $entry_plan, '', true, $arm_last_payment_status );
														} else {

																$arm_subscription_plans->arm_add_membership_history( $user_id, $entry_plan, 'change_subscription' );

														}
														$is_log = true;
													}
												}
												$paypalLog['txn_id']         = '-';
												$paypalLog['payment_status'] = 'success';
												$paypalLog['payment_type']   = 'subscr_signup';
												$paypalLog['mc_gross']       = $_POST['mc_amount1'];	 //phpcs:ignore
												$paypalLog['payment_date']   = $_POST['subscr_date'];	 //phpcs:ignore
											}
											break;
										case 'subscr_payment':
										case 'recurring_payment':
										case 'web_accept':
											$form_id   = $entry_data['arm_form_id'];
											$armform   = new ARM_Form_Lite( 'id', $form_id );
											$user_info = get_user_by( 'email', $entry_email );
											$new_plan  = new ARM_Plan_Lite( $entry_plan );
											if ( $new_plan->is_recurring() ) {

												if ( in_array( $entry_plan, $arm_user_old_plan ) ) {
													$is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user( $user_id, $entry_plan, $payment_mode );
													if ( $is_recurring_payment ) {
														$planData      = get_user_meta( $user_id, 'arm_user_plan_' . $entry_plan, true );
														$oldPlanDetail = $planData['arm_current_plan_detail'];
														if ( ! empty( $oldPlanDetail ) ) {
															$plan = new ARM_Plan_Lite( 0 );
															$plan->init( (object) $oldPlanDetail );
															$plan_data                 = $plan->prepare_recurring_data( $payment_cycle );
															$extraParam['plan_amount'] = $plan_data['amount'];
														}
													} else {
														$plan_data                 = $new_plan->prepare_recurring_data( $payment_cycle );
														$extraParam['plan_amount'] = $plan_data['amount'];
													}
												} else {
													$plan_data                 = $new_plan->prepare_recurring_data( $payment_cycle );
													$extraParam['plan_amount'] = $plan_data['amount'];
												}
											} else {

												$extraParam['plan_amount'] = $new_plan->amount;
											}

											$defaultPlanData  = $arm_subscription_plans->arm_default_plan_array();
											$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $entry_plan, true );
											$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
											$userPlanData     = shortcode_atts( $defaultPlanData, $userPlanDatameta );

											if ( ! $user_info && in_array( $armform->type, array( 'registration' ) ) ) {

												$recurring_data = $new_plan->prepare_recurring_data( $payment_cycle );
												if ( ! empty( $recurring_data['trial'] ) ) {
													$extraParam['trial']        = array(
														'amount' => $recurring_data['trial']['amount'],
														'period' => $recurring_data['trial']['period'],
														'interval' => $recurring_data['trial']['interval'],
													);
													$extraParam['arm_is_trial'] = '1';
												}
												$payment_log_id = self::arm_store_paypal_log( $paypalLog, 0, $entry_plan, $extraParam, $payment_mode );
												$payment_done   = array();
												if ( $payment_log_id ) {
													$payment_done = array(
														'status' => true,
														'log_id' => $payment_log_id,
														'entry_id' => $entry_id,
													);
												}
												$entry_values['payment_done']                 = '1';
												$entry_values['arm_entry_id']                 = $entry_id;
												$entry_values['arm_update_user_from_profile'] = 0;
												$user_id                                      = $arm_member_forms->arm_register_new_member( $entry_values, $armform );

												if ( is_numeric( $user_id ) && ! is_array( $user_id ) ) {
													if ( $arm_payment_type == 'subscription' ) {

														$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $entry_plan, true );
														$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
														$userPlanData     = shortcode_atts( $defaultPlanData, $userPlanDatameta );

														$userPlanData['arm_subscr_id'] = $arm_token;
														update_user_meta( $user_id, 'arm_user_plan_' . $entry_plan, $userPlanData );
													}
													update_user_meta( $user_id, 'arm_entry_id', $entry_id );

												}
											} else {

												$user_id = $user_info->ID;
												if ( ! empty( $user_id ) ) {

														$old_plan_ids        = get_user_meta( $user_id, 'arm_user_plan_ids', true );
														$old_plan_id         = isset( $old_plan_ids[0] ) ? $old_plan_ids[0] : 0;
														$oldPlanDetail       = array();
														$old_subscription_id = '';
													if ( ! empty( $old_plan_id ) ) {
														$oldPlanData         = get_user_meta( $user_id, 'arm_user_plan_' . $old_plan_id, true );
														$oldPlanData         = ! empty( $oldPlanData ) ? $oldPlanData : array();
														$oldPlanData         = shortcode_atts( $defaultPlanData, $oldPlanData );
														$oldPlanDetail       = $oldPlanData['arm_current_plan_detail'];
														$subscr_effective    = $oldPlanData['arm_expire_plan'];
														$old_subscription_id = $oldPlanData['arm_subscr_id'];
													}

														$arm_user_old_plan_details                              = ( isset( $userPlanData['arm_current_plan_detail'] ) && ! empty( $userPlanData['arm_current_plan_detail'] ) ) ? $userPlanData['arm_current_plan_detail'] : array();
														$arm_user_old_plan_details['arm_user_old_payment_mode'] = $userPlanData['arm_payment_mode'];

													if ( ! empty( $old_subscription_id ) && $entry_values['arm_selected_payment_mode'] == 'auto_debit_subscription' && $arm_token == $old_subscription_id ) {

														$arm_next_due_payment_date = $userPlanData['arm_next_due_payment'];
														if ( ! empty( $arm_next_due_payment_date ) ) {
															if ( strtotime( current_time( 'mysql' ) ) >= $arm_next_due_payment_date ) {
																$arm_user_completed_recurrence = $userPlanData['arm_completed_recurring'];
																$arm_user_completed_recurrence++;
																$userPlanData['arm_completed_recurring'] = $arm_user_completed_recurrence;
																update_user_meta( $user_id, 'arm_user_plan_' . $entry_plan, $userPlanData );
																$arm_next_payment_date = $arm_members_class->arm_get_next_due_date( $user_id, $entry_plan, false, $payment_cycle );
																if ( $arm_next_payment_date != '' ) {
																	$userPlanData['arm_next_due_payment'] = $arm_next_payment_date;
																	update_user_meta( $user_id, 'arm_user_plan_' . $entry_plan, $userPlanData );
																}
															} else {
																	$now                     = current_time( 'mysql' );
																	$arm_last_payment_status = $wpdb->get_var( $wpdb->prepare( 'SELECT `arm_transaction_status` FROM `' . $ARMemberLite->tbl_arm_payment_log . '` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1', $user_id, $entry_plan, $now ) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name
																if ( in_array( $arm_last_payment_status, array( 'success', 'pending' ) ) ) {
																	$arm_user_completed_recurrence = $userPlanData['arm_completed_recurring'];
																	$arm_user_completed_recurrence++;
																	$userPlanData['arm_completed_recurring'] = $arm_user_completed_recurrence;
																	update_user_meta( $user_id, 'arm_user_plan_' . $entry_plan, $userPlanData );
																	$arm_next_payment_date = $arm_members_class->arm_get_next_due_date( $user_id, $entry_plan, false, $payment_cycle );
																	if ( $arm_next_payment_date != '' ) {
																			   $userPlanData['arm_next_due_payment'] = $arm_next_payment_date;
																			   update_user_meta( $user_id, 'arm_user_plan_' . $entry_plan, $userPlanData );
																	}
																}
															}
														}

														$suspended_plan_ids = get_user_meta( $user_id, 'arm_user_suspended_plan_ids', true );
														$suspended_plan_id  = ( isset( $suspended_plan_ids ) && ! empty( $suspended_plan_ids ) ) ? $suspended_plan_ids : array();

														if ( in_array( $entry_plan, $suspended_plan_id ) ) {
															unset( $suspended_plan_id[ array_search( $entry_plan, $suspended_plan_id ) ] );
															update_user_meta( $user_id, 'arm_user_suspended_plan_ids', array_values( $suspended_plan_id ) );
														}
													} else {

														$now                     = current_time( 'mysql' );
														$arm_last_payment_status = $wpdb->get_var( $wpdb->prepare( 'SELECT `arm_transaction_status` FROM `' . $ARMemberLite->tbl_arm_payment_log . '` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1', $user_id, $entry_plan, $now ) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name

														$userPlanData['arm_current_plan_detail'] = $arm_user_old_plan_details;

														$userPlanData['arm_payment_mode']  = $entry_values['arm_selected_payment_mode'];
														$userPlanData['arm_payment_cycle'] = $entry_values['arm_selected_payment_cycle'];

														if ( ! empty( $oldPlanDetail ) ) {
															$old_plan = new ARM_Plan_Lite( 0 );
															$old_plan->init( (object) $oldPlanDetail );
														} else {
															$old_plan = new ARM_Plan_Lite( $old_plan_id );
														}
														$is_update_plan = true;

														$recurring_data = $new_plan->prepare_recurring_data( $payment_cycle );
														if ( ! empty( $recurring_data['trial'] ) && empty( $arm_user_old_plan ) ) {
															$extraParam['trial'] = array(
																'amount' => $recurring_data['trial']['amount'],
																'period' => $recurring_data['trial']['period'],
																'interval' => $recurring_data['trial']['interval'],
															);
														}
														if ( $old_plan->exists() ) {
															if ( $old_plan->is_lifetime() || $old_plan->is_free() || ( $old_plan->is_recurring() && $new_plan->is_recurring() ) ) {
																$is_update_plan = true;
															} else {
																$change_act = 'immediate';
																if ( $old_plan->enable_upgrade_downgrade_action == 1 ) {
																	if ( ! empty( $old_plan->downgrade_plans ) && in_array( $new_plan->ID, $old_plan->downgrade_plans ) ) {
																		$change_act = $old_plan->downgrade_action;
																	}
																	if ( ! empty( $old_plan->upgrade_plans ) && in_array( $new_plan->ID, $old_plan->upgrade_plans ) ) {
																		$change_act = $old_plan->upgrade_action;
																	}
																}
																if ( $change_act == 'on_expire' && ! empty( $subscr_effective ) ) {
																	$is_update_plan                      = false;
																	$oldPlanData['arm_subscr_effective'] = $subscr_effective;
																	$oldPlanData['arm_change_plan_to']   = $entry_plan;
																	update_user_meta( $user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData );
																}
															}
														}

														update_user_meta( $user_id, 'arm_entry_id', $entry_id );
														$userPlanData['arm_user_gateway'] = 'paypal';

														if ( ! empty( $arm_token ) ) {
															$userPlanData['arm_subscr_id'] = $arm_token;
														}
														update_user_meta( $user_id, 'arm_user_plan_' . $entry_plan, $userPlanData );
														if ( $is_update_plan ) {

															$arm_subscription_plans->arm_update_user_subscription( $user_id, $entry_plan, '', true, $arm_last_payment_status );
														} else {

															$arm_subscription_plans->arm_add_membership_history( $user_id, $entry_plan, 'change_subscription' );
														}
													}

													$is_log = true;
												}
											}
											do_action( 'arm_after_recurring_payment_success_outside', $user_id, $entry_plan, 'paypal', $entry_values['arm_selected_payment_mode'] );
											break;
										case 'subscr_cancel':
										case 'recurring_payment_profile_cancel':
											$user_info = get_user_by( 'email', $entry_email );
											$user_id   = $user_info->ID;

											$paly_cycle_data = $plan->prepare_recurring_data($plan_cycle);
											$is_log                = true;
											$paypalLog['mc_gross'] = ( isset( $_POST['amount3'] ) && ! empty( $_POST['amount3'] ) ) ? $_POST['amount3'] : 0;  //phpcs:ignore
											$arm_transaction_id    = $wpdb->get_var( $wpdb->prepare('SELECT `arm_transaction_id` FROM `' . $ARMemberLite->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_token`=%s AND `arm_payment_gateway` = %s",$user_id,$arm_token,'paypal') );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name
											$paypalLog['txn_id']   = $arm_transaction_id;

											$paypalLog['payment_status'] = 'cancelled';
											$paypalLog['payment_type']   = 'subscr_cancel';

											$defaultPlanData  = $arm_subscription_plans->arm_default_plan_array();
											$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $entry_plan, true );
											$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
											$planData         = shortcode_atts( $defaultPlanData, $userPlanDatameta );

											$subscr_cancel_log_data = array(
												'cancel_rec_time' => $paly_cycle_data['rec_time'],
												'cancel_plan_action' => $plan->options['cancel_plan_action'],
												'user_id' => $user_id,
												'entry_plan' => $entry_plan,
												'arm_is_canceled' => $planData['arm_cencelled_plan'],
											);
	
											do_action('arm_payment_log_entry', $payment_gateway, 'webhook cancel subscription data', 'payment_gateway', $subscr_cancel_log_data, $arm_lite_debug_payment_log_id);

											$planDetail = $planData['arm_current_plan_detail'];
											if ( ! empty( $planDetail ) ) {
												$plan = new ARM_Plan_Lite( 0 );
												$plan->init( (object) $planDetail );
											} else {
												$plan = new ARM_Plan_Lite( $entry_plan );
											}

											$plan_cycle      = isset( $planData['arm_payment_cycle'] ) ? $planData['arm_payment_cycle'] : '';
											$paly_cycle_data = $plan->prepare_recurring_data( $plan_cycle );

											if ( $paly_cycle_data['rec_time'] != 'infinite' || $plan->options['cancel_plan_action'] != 'on_expire' ) {
												if ( ! empty( $planData['arm_subscr_id'] ) ) {

													$arm_subscription_plans->arm_add_membership_history( $user_id, $entry_plan, 'cancel_subscription' );

													do_action( 'arm_cancel_subscription', $user_id, $entry_plan );
													$arm_subscription_plans->arm_clear_user_plan_detail( $user_id, $entry_plan );

													$cancel_plan_act = isset( $plan->options['cancel_action'] ) ? $plan->options['cancel_action'] : 'block';
													if ( $arm_subscription_plans->isPlanExist( $cancel_plan_act ) ) {
															$arm_members_class->arm_new_plan_assigned_by_system( $cancel_plan_act, $entry_plan, $user_id );
													} else {
													}
												}

												do_action( 'arm_after_recurring_payment_cancelled_outside', $user_id, $entry_plan, 'paypal' );
											}
											break;
										case 'subscr_eot':
										case 'recurring_payment_expired':
										case 'subscr_failed':
										case 'recurring_payment_failed':
										case 'recurring_payment_suspended':
										case 'recurring_payment_suspended_due_to_max_failed_payment':
											$user_info = get_user_by( 'email', $entry_email );
											$user_id   = $user_info->ID;
											$plan_ids  = get_user_meta( $user_id, 'arm_user_plan_ids', true );
											if ( ! empty( $plan_ids ) && is_array( $plan_ids ) ) {
												foreach ( $plan_ids as $plan_id ) {
													$planData = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
													if ( ! empty( $planData ) ) {
														$subscr_id = $planData['arm_subscr_id'];
														if ( $plan_id == $entry_plan && $subscr_id == $arm_token ) {
															if ( in_array( $_POST['txn_type'], array( 'subscr_eot', 'recurring_payment_expired' ) ) ) { //phpcs:ignore
																/*
																$action = "eot";
																$is_log = true;
																$paypalLog['txn_id'] = '-';
																$paypalLog['payment_status'] = 'expired';
																$paypalLog['payment_type'] = 'subscr_eot';
																$paypalLog['payment_date'] = current_time('mysql');
																$arm_subscription_plans->arm_user_plan_status_action(array('plan_id' => $entry_plan, 'user_id' => $user_id, 'action' => "eot"));
																do_action('arm_after_recurring_payment_completed_outside', $user_id, $plan_id, 'paypal'); */
															} else {

																$action                      = 'failed_payment';
																$is_log                      = true;
																$extraParam['error']         = isset( $_POST['txn_type'] ) ? $_POST['txn_type'] : ''; //phpcs:ignore
																$paypalLog['mc_gross']       = 0;
																$paypalLog['txn_id']         = '-';
																$paypalLog['payment_status'] = 'failed';
																$paypalLog['payment_type']   = 'subscr_failed';
																$paypalLog['payment_date']   = current_time( 'mysql' );

																$arm_subscription_plans->arm_user_plan_status_action(
																	array(
																		'plan_id' => $entry_plan,
																		'user_id' => $user_id,
																		'action' => 'failed_payment',
																	)
																);
																do_action( 'arm_after_recurring_payment_stopped_outside', $user_id, $plan_id, 'paypal' );
															}
														}
													}
												}
											}
											break;
										default:
											do_action( 'arm_handle_paypal_unknown_error_from_outside', $entry_data['arm_user_id'], $entry_data['arm_plan_id'], sanitize_text_field( $_POST['txn_type'] ) ); //phpcs:ignore
											break;
									}
									if ( $is_log && ! empty( $user_id ) && $user_id != 0 ) {

										$payment_log_id = self::arm_store_paypal_log( $paypalLog, $user_id, $entry_plan, $extraParam, $payment_mode );

									} //-->End `($is_log && !empty($user_id) && $user_id != 0)`
								}//For Writing Response

							}//-->End `(!empty($entry_data))`
						}//-->End `(!is_wp_error($response) and $response['body'] == 'VERIFIED')`
					}
				}//-->End `(!empty($_POST['txn_id']) || !empty($_POST['subscr_id']))`
			}
			return;
		}

		function arm_cancel_paypal_subscription( $user_id, $plan_id ) {
			global $wpdb, $ARMemberLite, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication,$arm_lite_debug_payment_log_id;
			if ( ! empty( $user_id ) && $user_id != 0 && ! empty( $plan_id ) && $plan_id != 0 ) {
				$user_detail = get_userdata( $user_id );
				$payer_email = $user_detail->user_email;

				$defaultPlanData  = $arm_subscription_plans->arm_default_plan_array();
				$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
				$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
				$planData         = shortcode_atts( $defaultPlanData, $userPlanDatameta );

				do_action('arm_payment_log_entry', 'paypal', 'cancel subscription plan data', 'armember', $planData, $arm_lite_debug_payment_log_id);

				$subscr_id            = '';
				$user_payment_gateway = '';
				if ( ! empty( $planData ) ) {
					$user_payment_gateway = $planData['arm_user_gateway'];
					$subscr_id            = $planData['arm_subscr_id'];
				}
				$payment_mode = $planData['arm_payment_mode'];
				if ( ! empty( $subscr_id ) && strtolower( $user_payment_gateway ) == 'paypal' ) {

					$planDetail = $planData['arm_current_plan_detail'];

					if ( ! empty( $planDetail ) ) {
						$plan = new ARM_Plan_Lite( 0 );
						$plan->init( (object) $planDetail );
					} else {
						$plan = new ARM_Plan_Lite( $plan_id );
					}

						$arm_payment_cycle = $planData['arm_payment_cycle'];
						$recurring_data    = $plan->prepare_recurring_data( $arm_payment_cycle );
						$amount            = $recurring_data['amount'];

					if ( $payment_mode == 'auto_debit_subscription' ) {
						$this->arm_immediate_cancel_paypal_payment( $subscr_id, $user_id, $plan_id, $planData );
						/*
						$PayPal = self::arm_init_paypal();
						if ($PayPal !== false) {
							$PayPalCancelRequestData = array(
								'MRPPSFields' => array(
									'profileid' => $subscr_id,
									'action' => urlencode('Cancel'),
									'note' => esc_html__("Cancel User's Subscription.", 'armember-membership')
								)
							);
							$PayPalResult = $PayPal->ManageRecurringPaymentsProfileStatus($PayPalCancelRequestData);
							if (!is_wp_error($PayPalResult) && isset($PayPalResult['ACK']) && strtolower($PayPalResult['ACK']) == 'success') {
								$planData['arm_subscr_id'] = '';
								update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $planData);

							}
						}*/
					} else {
						$payment_data   = array(
							'arm_user_id'                  => $user_id,
							'arm_first_name'               => $user_detail->first_name,
							'arm_last_name'                => $user_detail->last_name,
							'arm_plan_id'                  => ( ! empty( $plan_id ) ? $plan_id : 0 ),
							'arm_payment_gateway'          => 'paypal',
							'arm_payment_type'             => 'subscription',
							'arm_token'                    => $subscr_id,
							'arm_payer_email'              => $payer_email,
							'arm_receiver_email'           => '',
							'arm_transaction_id'           => $subscr_id,
							'arm_transaction_payment_type' => 'subscription',
							'arm_transaction_status'       => 'canceled',
							'arm_payment_mode'             => $payment_mode,
							'arm_payment_date'             => current_time( 'mysql' ),
							'arm_amount'                   => $amount,
							'arm_is_trial'                 => '0',
							'arm_created_date'             => current_time( 'mysql' ),
						);
						$payment_log_id = $arm_payment_gateways->arm_save_payment_log( $payment_data );
						return;
					}
				}//End `(!empty($subscr_id) && strtolower($user_payment_gateway)=='paypal')`
			}//End `(!empty($user_id) && $user_id != 0 && !empty($plan_id) && $plan_id != 0)`
		}

		function arm_store_paypal_log( $paypal_response = '', $user_id = 0, $plan_id = 0, $extraVars = array(), $payment_mode = 'manual_subscription' ) {
			global $wpdb, $ARMemberLite, $arm_global_settings, $arm_member_forms, $arm_payment_gateways;

			if ( ! empty( $paypal_response ) ) {
				$arm_first_name = ( isset( $paypal_response['first_name'] ) ) ? $paypal_response['first_name'] : '';
				$arm_last_name  = ( isset( $paypal_response['last_name'] ) ) ? $paypal_response['last_name'] : '';
				if ( $user_id ) {
					$user_detail    = get_userdata( $user_id );
					$arm_first_name = $user_detail->first_name;
					$arm_last_name  = $user_detail->last_name;
				}
				$payment_data = array(
					'arm_user_id'                  => $user_id,
					'arm_first_name'               => $arm_first_name,
					'arm_last_name'                => $arm_last_name,
					'arm_plan_id'                  => ( ! empty( $plan_id ) ? $plan_id : 0 ),
					'arm_payment_gateway'          => 'paypal',
					'arm_payment_type'             => $paypal_response['arm_payment_type'],
					'arm_token'                    => $paypal_response['subscr_id'],
					'arm_payer_email'              => $paypal_response['payer_email'],
					'arm_receiver_email'           => $paypal_response['receiver_email'],
					'arm_transaction_id'           => $paypal_response['txn_id'],
					'arm_transaction_payment_type' => $paypal_response['payment_type'],
					'arm_transaction_status'       => $paypal_response['payment_status'],
					'arm_payment_mode'             => $payment_mode,
					'arm_payment_date'             => date( 'Y-m-d H:i:s', strtotime( $paypal_response['payment_date'] ) ), //phpcs:ignore
					'arm_amount'                   => $paypal_response['mc_gross'],
					'arm_currency'                 => $paypal_response['mc_currency'],

					'arm_extra_vars'               => maybe_serialize( $extraVars ),
					'arm_is_trial'                 => $extraVars['arm_is_trial'],
					'arm_created_date'             => current_time( 'mysql' ),
				);

				$payment_log_id = $arm_payment_gateways->arm_save_payment_log( $payment_data );
				return $payment_log_id;
			}
			return false;
		}

		function arm_update_new_subscr_gateway_outside_func( $payment_gateways = array() ) {
			global $payment_done;
			if ( isset( $payment_done['zero_amount_paid'] ) && $payment_done['zero_amount_paid'] == true ) {
				array_push( $payment_gateways, 'paypal' );
			}
			return $payment_gateways;
		}

		function arm_update_user_meta_after_renew_outside_func( $user_id, $log_detail, $plan_id, $payment_gateway ) {
			global $payment_done;
			if ( isset( $payment_don['zero_amount_paid'] ) && $payment_done['zero_amount_paid'] == true ) {

			}
		}

		function arm_change_pending_gateway_outside( $user_pending_pgway, $plan_ID, $user_id ) {
			global $is_free_manual, $ARMemberLite;
			if ( $is_free_manual ) {
				$key = array_search( 'paypal', $user_pending_pgway );
				unset( $user_pending_pgway[ $key ] );
			}
			return $user_pending_pgway;
		}

	}

}
global $arm_paypal;
$arm_paypal = new ARM_Paypal_Lite();
