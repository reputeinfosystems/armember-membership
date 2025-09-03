<?php 
if ( ! class_exists( 'ARM_transaction_Lite' ) ) {
	class ARM_transaction_Lite {

		function __construct() {
			global $wpdb, $ARMemberLite, $arm_slugs;
			add_action( 'wp_ajax_arm_transaction_ajax_action', array( $this, 'arm_transaction_ajax_action' ) );
			add_action( 'wp_ajax_arm_bulk_delete_transactions', array( $this, 'arm_bulk_delete_transactions' ) );
			add_action( 'wp_ajax_arm_filter_transactions_list', array( $this, 'arm_filter_transactions_list' ) );
			add_action( 'wp_ajax_arm_transaction_hide_show_columns', array( $this, 'arm_transaction_hide_show_columns' ) );
			// add_action( 'wp_ajax_arm_change_bank_transfer_status', array( $this, 'arm_change_bank_transfer_status' ) );
			add_action( 'wp_ajax_arm_preview_log_detail', array( $this, 'arm_preview_log_detail' ) );
		
			add_action( 'arm_save_manual_payment', array( $this, 'arm_add_manual_payment' ) );
			add_action( 'wp_ajax_arm_load_transactions', array( $this, 'arm_load_transaction_grid' ) );
			add_action( 'wp_ajax_arm_get_user_transactions_paging_action', array( $this, 'arm_get_user_transactions_paging_action' ) );
			
			add_action('wp_ajax_arm_preview_log_detail_by_url', array($this, 'arm_preview_log_detail_by_url'));
		}
		function arm_transaction_hide_show_columns() {
			global $ARMemberLite, $arm_capabilities_global;
			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_transactions'], '1' ); //phpcs:ignore --Reason:Verifying nonce
			$column_list = isset( $_POST['column_list'] ) ? sanitize_text_field($_POST['column_list']) : ''; //phpcs:ignore
			if ( $column_list != '' ) {
				$user_id             = get_current_user_id();
				$column_list         = explode( ',', $column_list );
				$transaction_columns = maybe_serialize( $column_list );
				update_user_meta( $user_id, 'arm_transaction_hide_show_columns', $transaction_columns );
			}
			die();
		}
		function arm_get_transaction( $field = '', $value = '', $output_type = ARRAY_A ) {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_global_settings, $arm_subscription_plans;
			$log_data = array();
			if ( ! empty( $field ) && ! empty( $value ) && $value != 0 ) {
				$log_data = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `".$ARMemberLite->tbl_arm_payment_log."` WHERE ".$field."=%s",$value), $output_type );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name
			}
			return $log_data;
		}
		function arm_get_single_transaction( $log_id = 0, $output_type = ARRAY_A ) {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_global_settings, $arm_subscription_plans;
			$log_data = array();
			if ( ! empty( $log_id ) && $log_id != 0 ) {
				$log_data = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$ARMemberLite->tbl_arm_payment_log."` WHERE `arm_log_id`=%d",$log_id), $output_type );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name
			}
			return $log_data;
		}


		function arm_preview_log_detail() {
			 global $wp, $wpdb, $current_user, $arm_lite_errors, $ARMemberLite, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_transactions'], '1' ); //phpcs:ignore --Reason:Verifying nonce	
			$gateways        = $arm_payment_gateways->arm_get_all_payment_gateways();
			$bank_transfer_gateways_opts = $gateways['bank_transfer'];
			$log_id                      = intval( $_POST['log_id'] ); //phpcs:ignore
			$log_type                    = sanitize_text_field( $_POST['log_type'] ); //phpcs:ignore
			$trxn_status                 = sanitize_text_field( $_POST['trxn_status'] ); //phpcs:ignore
			$date_time_format            = $arm_global_settings->arm_get_wp_date_time_format();
			/* Get Edit Rule Form HTML */
			if ( ! empty( $log_id ) && $log_id != 0 ) {
				if ( $log_type == 'bt_log' && $trxn_status != 'failed' ) {
					$log_data = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$ARMemberLite->tbl_arm_payment_log."` WHERE `arm_log_id`=%d",$log_id) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name
					if ( empty( $log_data ) ) {
						$log_detail = $this->arm_get_single_transaction( $log_id );
					}
					$plan_id          = $log_data->arm_plan_id;
					$defaultPlanData  = $arm_subscription_plans->arm_default_plan_array();
					$userPlanDatameta = get_user_meta( $log_data->arm_user_id, 'arm_user_plan_' . $plan_id, true );
					$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
					$planData         = shortcode_atts( $defaultPlanData, $userPlanDatameta );
					$planDetail       = $planData['arm_current_plan_detail'];

					if ( ! empty( $planDetail ) ) {
						$arm_user_plan_details = new ARM_Plan_Lite( 0 );
						$arm_user_plan_details->init( (object) $planDetail );
					} else {
						$arm_user_plan_details = new ARM_Plan_Lite( $plan_id );
					}
					$payment_type = ! empty( $arm_user_plan_details->options['payment_type'] ) ? $arm_user_plan_details->options['payment_type'] : '';
					if ( ! empty( $log_data ) ) {
						$lStatus = 'pending';
						if ( $log_data->arm_transaction_status == '1' ) {
							$lStatus = 'success';
						}
						if ( $log_data->arm_transaction_status == '2' ) {
							$lStatus = 'canceled';
						}
						$log_detail = array(
							'arm_log_id'                   => $log_data->arm_log_id,
							'arm_invoice_id'               => $log_data->arm_invoice_id,
							'arm_user_id'                  => $log_data->arm_user_id,
							'arm_plan_id'                  => $log_data->arm_plan_id,
							'arm_payment_gateway'          => 'bank_transfer',
							'arm_payment_type'             => $payment_type,
							'arm_token'                    => '',
							'arm_payer_email'              => $log_data->arm_payer_email,
							'arm_receiver_email'           => '',
							'arm_transaction_id'           => $log_data->arm_transaction_id,
							'arm_transaction_payment_type' => '-',
							'arm_transaction_status'       => $lStatus,
							'arm_payment_date'             => $log_data->arm_created_date,
							'arm_amount'                   => $log_data->arm_amount,
							'arm_currency'                 => $log_data->arm_currency,
							'arm_extra_vars'               => $log_data->arm_extra_vars,
							'arm_bank_name'                => $log_data->arm_bank_name,
							'arm_account_name'             => $log_data->arm_account_name,
							'arm_additional_info'          => $log_data->arm_additional_info,
							'arm_payment_transfer_mode'    => $log_data->arm_payment_transfer_mode,
							'arm_created_date'             => $log_data->arm_created_date,
						);
					}
				} else {
					$log_detail = $this->arm_get_single_transaction( $log_id );
				}
				if ( ! empty( $log_detail ) ) {
					$extra_vars = ( isset( $log_detail['arm_extra_vars'] ) ) ? maybe_unserialize( $log_detail['arm_extra_vars'] ) : array();
					?>
					<div class="arm_preview_log_detail_popup popup_wrapper arm_preview_log_detail_popup_wrapper" style="width:800px;">
						<div class="popup_wrapper_inner" style="overflow: hidden;">
							<div class="popup_header">
								<span class="popup_close_btn arm_popup_close_btn arm_preview_log_detail_close_btn"></span>
								<span class="add_rule_content"><?php esc_html_e( 'Transaction Details', 'armember-membership' ); ?></span>
							</div>
							<div class="popup_content_text arm_transactions_detail_popup_text">
								<table width="100%" cellspacing="0">
									<tr>
										<th><?php esc_html_e( 'User', 'armember-membership' ); ?></th>
										<td>
										<?php
										if ( ! empty( $log_detail['arm_user_id'] ) ) {
											$data = get_userdata( $log_detail['arm_user_id'] );
											echo ( ! empty( $data->user_login ) ) ? esc_html($data->user_login) : '--';
										} else {
											echo '--';
										}
										?>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Plan', 'armember-membership' ); ?></th>
										<td><?php echo ( ! empty( $log_detail['arm_plan_id'] ) ) ? $arm_subscription_plans->arm_get_plan_name_by_id( intval($log_detail['arm_plan_id']) ) : '--'; //phpcs:ignore ?></td>
									</tr>
									<tr>
										<?php
										if ( $log_detail['arm_payment_gateway'] == 'bank_transfer' ) :
											$transaction_id_field_label = ! empty( $bank_transfer_gateways_opts['transaction_id_label'] ) ? stripslashes( $bank_transfer_gateways_opts['transaction_id_label'] ) : esc_html__( 'Transaction ID', 'armember-membership' );
										?>
											<th><?php echo esc_html($transaction_id_field_label); ?></th>
										<?php else : ?>
											<th><?php esc_html_e( 'Transaction ID', 'armember-membership' ); ?></th>
										<?php endif; ?>
										<td><?php echo ( !empty( $log_detail['arm_transaction_id'] ) ) ? esc_html($log_detail['arm_transaction_id']) : esc_html__( 'Manual', 'armember-membership' ); //phpcs:ignore ?></td>
									</tr>
									<?php if ( ! empty( $log_detail['arm_token'] ) ) : ?>
									<tr>
										<th>
										<?php
										if ( $log_detail['arm_payment_type'] == 'subscription' ) {
											esc_html_e( 'Subscription ID', 'armember-membership' );
										} else {
											esc_html_e( 'Token', 'armember-membership' );
										}
										?>
										</th>
										<td><?php echo ( ! empty( $log_detail['arm_token'] ) ) ? esc_html($log_detail['arm_token']) : '--'; ?></td>
									</tr>
									<?php endif; ?>
									<tr>
										<th><?php esc_html_e( 'Payment Gateway', 'armember-membership' ); ?></th>
										<td>
										<?php
										echo ( ! empty( $log_detail['arm_payment_gateway'] ) ) ? $arm_payment_gateways->arm_gateway_name_by_key( $log_detail['arm_payment_gateway'] ) : '--'; //phpcs:ignore
										?>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Payment Type', 'armember-membership' ); ?></th>
										<td><?php echo ( esc_html($log_detail['arm_payment_type']) == 'subscription' ) ? esc_html__( 'Subscription', 'armember-membership' ) : esc_html__( 'One Time', 'armember-membership' ); //phpcs:ignore ?></td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Payer Email', 'armember-membership' ); ?></th>
										<td><?php echo esc_html($log_detail['arm_payer_email']); ?></td>
									</tr>
									<?php if ( ! empty( $log_detail['arm_receiver_email'] ) ) : ?>
									<tr>
										<th><?php esc_html_e( 'Receiver Email', 'armember-membership' ); ?></th>
										<td><?php echo esc_html($log_detail['arm_receiver_email']); ?></td>
									</tr>
									<?php endif; ?>
									<tr>
										<th><?php esc_html_e( 'Transaction Status', 'armember-membership' ); ?></th>
										<td><?php echo ucfirst( esc_html($log_detail['arm_transaction_status']) ); //phpcs:ignore ?></td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Payment Amount', 'armember-membership' ); ?></th>
										<td>
										<?php
										echo $arm_payment_gateways->arm_amount_set_separator( $log_detail['arm_currency'], floatval($log_detail['arm_amount'] )) . ' ' . strtoupper( $log_detail['arm_currency'] ); //phpcs:ignore
										// echo (!empty($log_detail['arm_amount']) && $log_detail['arm_amount'] > 0) ? number_format((float) $log_detail['arm_amount'], 2) . ' ' . strtoupper($log_detail['arm_currency']) : '0.00';
										?>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Credit Card Number', 'armember-membership' ); ?></th>
										<td>
										<?php
										$cc_num = ( isset( $extra_vars['card_number'] ) && ! empty( $extra_vars['card_number'] ) ) ? $extra_vars['card_number'] : '-';
										echo $cc_num; //phpcs:ignore
										?>
										</td>
									</tr>
									<?php if ( isset( $extra_vars['trial'] ) && ! empty( $extra_vars['trial'] ) ) : ?>
									<tr>
										<th><?php esc_html_e( 'Trial Amount', 'armember-membership' ); ?></th>
										<td><?php echo number_format( (float) $extra_vars['trial']['amount'], 2 ) . ' ' . strtoupper( $log_detail['arm_currency'] ); //phpcs:ignore ?></td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Trial Period', 'armember-membership' ); ?></th>
										<td>
										<?php
										$trialInterval = $extra_vars['trial']['interval'];
										$trialData     = $trialInterval . ' ';
										if ( $extra_vars['trial']['period'] == 'Y' ) {
											$trialData .= ( $trialInterval > 1 ) ? esc_html__( 'Years', 'armember-membership' ) : esc_html__( 'Year', 'armember-membership' );
										} elseif ( $extra_vars['trial']['period'] == 'M' ) {
											$trialData .= ( $trialInterval > 1 ) ? esc_html__( 'Months', 'armember-membership' ) : esc_html__( 'Month', 'armember-membership' );
										} elseif ( $extra_vars['trial']['period'] == 'W' ) {
											$trialData .= ( $trialInterval > 1 ) ? esc_html__( 'Weeks', 'armember-membership' ) : esc_html__( 'Week', 'armember-membership' );
										} else {
											$trialData .= ( $trialInterval > 1 ) ? esc_html__( 'Days', 'armember-membership' ) : esc_html__( 'Day', 'armember-membership' );
										}
										echo esc_html($trialData);
										?>
										</td>
									</tr>
									<?php endif; ?>
									
									<?php
									if ( $log_detail['arm_payment_gateway'] == 'bank_transfer' ) :
										$bank_name_field_label       = ! empty( $bank_transfer_gateways_opts['bank_name_label'] ) ? stripslashes( $bank_transfer_gateways_opts['bank_name_label'] ) : esc_html__( 'Bank Name', 'armember-membership' );
										$account_name_field_label    = ! empty( $bank_transfer_gateways_opts['account_name_label'] ) ? stripslashes( $bank_transfer_gateways_opts['account_name_label'] ) : esc_html__( 'Account Holder Name', 'armember-membership' );
										$additional_info_field_label = ! empty( $bank_transfer_gateways_opts['additional_info_label'] ) ? stripslashes( $bank_transfer_gateways_opts['additional_info_label'] ) : esc_html__( 'Additional Note', 'armember-membership' );
										$transfer_mode_field_label   = ! empty( $bank_transfer_gateways_opts['transfer_mode_label'] ) ? stripslashes( $bank_transfer_gateways_opts['transfer_mode_label'] ) : esc_html__( 'Payment Mode', 'armember-membership' );
										?>
										<?php if ( isset( $log_detail['arm_bank_name'] ) && ! empty( $log_detail['arm_bank_name'] ) ) : ?>
											<tr>
												<th><?php echo esc_html($bank_name_field_label); ?></th>
												<td><?php echo esc_html($log_detail['arm_bank_name']); ?></td>
											</tr>
										<?php endif; ?>
										<?php if ( isset( $log_detail['arm_account_name'] ) && ! empty( $log_detail['arm_account_name'] ) ) : ?>
											<tr>
												<th><?php echo esc_html($account_name_field_label); ?></th>
												<td><?php echo esc_html($log_detail['arm_account_name']); ?></td>
											</tr>
										<?php endif; ?>
										<?php if ( isset( $log_detail['arm_additional_info'] ) && ! empty( $log_detail['arm_additional_info'] ) ) : ?>
											<tr>
												<th><?php echo esc_html($additional_info_field_label); ?></th>
												<td><?php echo nl2br( esc_html($log_detail['arm_additional_info']) ); ?></td>
											</tr>
										<?php endif; ?>
										<?php if ( isset( $log_detail['arm_payment_transfer_mode'] ) && ! empty( $log_detail['arm_payment_transfer_mode'] ) ) : ?>
											<tr>
												<th><?php echo esc_html($transfer_mode_field_label); ?></th>
												<td><?php echo nl2br( esc_html($log_detail['arm_payment_transfer_mode']) ); ?></td>
											</tr>
										<?php endif; ?>
									<?php endif; ?>
									<?php if ( $log_detail['arm_payment_gateway'] == 'manual' && ! empty( $extra_vars['note'] ) ) : ?>
									<tr>
										<th><?php esc_html_e( 'Note', 'armember-membership' ); ?></th>
										<td><?php echo nl2br( stripslashes( esc_html($extra_vars['note'] )) ); //phpcs:ignore ?></td>
									</tr>
									<?php endif; ?>
									<tr>
										<th><?php esc_html_e( 'Payment Date', 'armember-membership' ); ?></th>
										<td><?php echo date_i18n( $date_time_format, strtotime( $log_detail['arm_created_date'] ) ); //phpcs:ignore ?></td>
									</tr>
								</table>
							</div>
							<div class="armclear"></div>
						</div>
					</div>
					<?php
				}
			}
			exit;
		}
		function arm_preview_log_detail_by_url()
		{
			global $wp, $wpdb, $current_user, $arm_errors, $ARMemberLite, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways, $arm_capabilities_global, $arm_ajax_pattern_start, $arm_ajax_pattern_end;

			$ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_transactions'], '1',1); //phpcs:ignore --Reason:Verifying nonce
			$arm_currency_decimal = 2;
			$date_time_format =  $arm_global_settings->arm_get_wp_date_time_format();
			$gateways        = $arm_payment_gateways->arm_get_all_payment_gateways();
			$bank_transfer_gateways_opts = $gateways['bank_transfer'];
			$log_id = $_POST['arm_log_id'];
			$log_detail = $this->arm_get_single_transaction($log_id);

			if(!empty($log_detail))
			{
				echo $arm_ajax_pattern_start;
				$extra_vars = (isset($log_detail['arm_extra_vars'])) ? maybe_unserialize($log_detail['arm_extra_vars']) : array();
				$arm_is_post_payment = (!empty($log_detail['arm_is_post_payment'])) ? maybe_unserialize($log_detail['arm_is_post_payment']) : 0;
				$arm_is_gift_payment = (!empty($log_detail['arm_is_gift_payment'])) ? maybe_unserialize($log_detail['arm_is_gift_payment']) : 0;		

				$log_detail = apply_filters('arm_filter_preview_log_details', $log_detail, $log_id, $_POST);//phpcs:ignore

				$transaction_id = (!empty($log_detail['arm_transaction_id'])) ? $log_detail['arm_transaction_id'] : esc_html__('Manual', 'armember-membership'); 

				$plan_id = (!empty($log_detail['arm_plan_id'])) ? $arm_subscription_plans->arm_get_plan_name_by_id($log_detail['arm_plan_id']): '--';

				$arm_token = (!empty($log_detail['arm_token'])) ? $log_detail['arm_token'] : '--';

				$payment_gateway = (!empty($log_detail['arm_payment_gateway'])) ? $arm_payment_gateways->arm_gateway_name_by_key($log_detail['arm_payment_gateway']) : '--';

				$transaction_status = ucfirst($log_detail['arm_transaction_status']);

				$payment_amount = $arm_payment_gateways->arm_amount_set_separator($log_detail['arm_currency'], $log_detail['arm_amount']) . ' ' . strtoupper($log_detail['arm_currency']);

				$trial_amount = !empty($extra_vars['trial']['amount']) ? number_format((float) $extra_vars['trial']['amount'], $arm_currency_decimal).' '.strtoupper($log_detail['arm_currency']) : '-';
				?>
				<div class="arm_preview_log_detail_popup popup_wrapper arm_preview_log_detail_popup_wrapper" >
					<div class="popup_wrapper_inner" style="overflow: hidden;">
						<div class="popup_header">
							<span class="popup_close_btn arm_popup_close_btn arm_preview_log_detail_close_btn"></span>
							<span class="add_rule_content"><?php esc_html_e('Transaction Details','armember-membership');?></span>
						</div>
						<div class="popup_content_text arm_transactions_detail_popup_text">
							<table width="100%" cellspacing="0">
								<tr>
									<th><?php esc_html_e('User','armember-membership' );?></th>
									<td><?php 
									if(!empty($log_detail['arm_user_id']))
									{
										$data = get_userdata($log_detail['arm_user_id']);
										echo (!empty($data->user_login)) ? esc_html($data->user_login) : '--';
									}
									else {
										echo '--';
									}
									?></td>
								</tr>
								<tr>
									<th>
										<?php 
											if(!empty($arm_is_post_payment)) 
											{ 
												esc_html_e('Post','armember-membership' ); 
											} 
											else if(!empty($arm_is_gift_payment)) 
											{ 
												esc_html_e('Gift','armember-membership' ); 
											} 
											else 
											{ 
												esc_html_e('Plan','armember-membership' ); 
											} 
										?>
									</th>
									<td><?php echo esc_html($plan_id);?></td>
								</tr>
								<tr>
									<?php
										if ($log_detail['arm_payment_gateway'] == "bank_transfer")
										{
											$transaction_id_field_label = !empty($bank_transfer_gateways_opts['transaction_id_label']) ? stripslashes($bank_transfer_gateways_opts['transaction_id_label']) : esc_html__('Transaction ID', 'armember-membership');
									?>
											<th><?php echo esc_html($transaction_id_field_label); ?></th>
									<?php
										}
										else
										{
									?>
											<th><?php esc_html_e('Transaction ID','armember-membership' );?></th>
									<?php } ?>

									<td><?php echo esc_html($transaction_id); ?></td>
								</tr>
								<?php if(!empty($log_detail['arm_token'])):?>
								<tr>
									<th><?php 
									if($log_detail['arm_payment_type'] == 'subscription')
									{
										esc_html_e('Subscription ID','armember-membership' );
									}
									else {
										esc_html_e('Token','armember-membership' );
									}
									?></th>
									<td><?php echo esc_html($arm_token);?></td>
								</tr>
								<?php endif;?>
								<tr>
									<th><?php esc_html_e('Payment Gateway','armember-membership' );?></th>
									<td><?php echo esc_html($payment_gateway);?></td>
								</tr>
								<tr>
									<th><?php esc_html_e('Payment Type','armember-membership' );?></th>
									<td><?php ($log_detail['arm_payment_type'] == 'subscription') ? esc_html_e('Subscription', 'armember-membership') : esc_html_e('One Time', 'armember-membership');?></td>
								</tr>
								<tr>
									<th><?php esc_html_e('Payer Email','armember-membership' );?></th>
									<td><?php echo esc_html($log_detail['arm_payer_email']);?></td>
								</tr>
								<?php if(!empty($log_detail['arm_receiver_email'])): ?>
								<tr>
									<th><?php esc_html_e('Receiver Email','armember-membership' );?></th>
									<td><?php echo esc_html($log_detail['arm_receiver_email']);?></td>
								</tr>
								<?php endif;?>
								<tr>
									<th><?php esc_html_e('Transaction Status','armember-membership' );?></th>
									<td><?php echo esc_html($transaction_status);?></td>
								</tr>
								<tr>
									<th><?php esc_html_e('Payment Amount','armember-membership' );?></th>
									<td><?php echo esc_html($payment_amount)?></td>
								</tr>
								<tr>
									<th><?php esc_html_e('Credit Card Number','armember-membership' );?></th>
									<td><?php 
									$cc_num = (isset($extra_vars['card_number']) && !empty($extra_vars['card_number'])) ? $extra_vars['card_number'] : '-';
									echo $cc_num; //phpcs:ignore
									?></td>
								</tr>
								<?php if(isset($extra_vars['trial']) && !empty($extra_vars['trial'])): ?>
								<tr>
									<th><?php esc_html_e('Trial Amount','armember-membership' );?></th>
									<td><?php echo esc_html($trial_amount)?></td>
								</tr>
								<tr>
									<th><?php esc_html_e('Trial Period','armember-membership' );?></th>
									<td><?php 
									$trialInterval = $extra_vars['trial']['interval'];
									$trialData = $trialInterval.' ';
									if ($extra_vars['trial']['period'] == 'Y')
									{
										$trialData .= ($trialInterval > 1) ? esc_html__('Years', 'armember-membership') : esc_html__('Year', 'armember-membership');
									}
									elseif ($extra_vars['trial']['period'] == 'M')
									{
										$trialData .= ($trialInterval > 1) ? esc_html__('Months', 'armember-membership') : esc_html__('Month', 'armember-membership');
									}
									elseif ($extra_vars['trial']['period'] == 'W')
									{
										$trialData .= ($trialInterval > 1) ? esc_html__('Weeks', 'armember-membership') : esc_html__('Week', 'armember-membership');
									}
									else
									{
										$trialData .= ($trialInterval > 1) ? esc_html__('Days', 'armember-membership') : esc_html__('Day', 'armember-membership');
									}
									echo esc_html($trialData);
									?></td>
								</tr>
								<?php endif;?>
								<?php if(!empty($log_detail['arm_coupon_code'])): ?>
								<tr>
									<th><?php esc_html_e('Used Coupon Code','armember-membership' );?></th>
									<td><?php echo esc_html($log_detail['arm_coupon_code']);?></td>
								</tr>
								<tr>
									<th><?php esc_html_e('Used Coupon Discount','armember-membership' );?></th>
									<td><?php
										if(!empty($log_detail['arm_coupon_discount']) && $log_detail['arm_coupon_discount'] > 0)
										{ 
											$discount_type = ($log_detail['arm_coupon_discount_type'] != 'percentage') ? " " .$log_detail['arm_coupon_discount_type'] : "%";
											$discount_decimal = number_format((float) $log_detail['arm_coupon_discount'], $arm_currency_decimal);
											echo esc_html($discount_decimal);
											echo esc_html($discount_type);
										}
										else
										{
											echo 0;
										}
									?></td>
								</tr>
								
								<?php endif;?>
								<?php if ($log_detail['arm_payment_gateway'] == "bank_transfer"):
									$bank_name_field_label = !empty($bank_transfer_gateways_opts['bank_name_label']) ? stripslashes($bank_transfer_gateways_opts['bank_name_label']) : esc_html__('Bank Name', 'armember-membership');
									$account_name_field_label = !empty($bank_transfer_gateways_opts['account_name_label']) ? stripslashes($bank_transfer_gateways_opts['account_name_label']) : esc_html__('Account Holder Name', 'armember-membership');
									$additional_info_field_label = !empty($bank_transfer_gateways_opts['additional_info_label']) ? stripslashes($bank_transfer_gateways_opts['additional_info_label']) : esc_html__('Additional Note', 'armember-membership');
									$transfer_mode_field_label = !empty($bank_transfer_gateways_opts['transfer_mode_label']) ? stripslashes($bank_transfer_gateways_opts['transfer_mode_label']) : esc_html__('Payment Mode', 'armember-membership');
									?>
									<?php if (isset($log_detail['arm_bank_name']) && !empty($log_detail['arm_bank_name'])): ?>
										<tr>
											<th><?php echo esc_html($bank_name_field_label);?></th>
											<td><?php echo esc_html($log_detail['arm_bank_name']);?></td>
										</tr>
									<?php endif;?>
									<?php if (isset($log_detail['arm_account_name']) && !empty($log_detail['arm_account_name'])): ?>
										<tr>
											<th><?php echo esc_html($account_name_field_label);?></th>
											<td><?php echo esc_html($log_detail['arm_account_name']);?></td>
										</tr>
									<?php endif;?>
									<?php if (isset($log_detail['arm_additional_info']) && !empty($log_detail['arm_additional_info'])): ?>
										<tr>
											<th><?php echo esc_html($additional_info_field_label);?></th>
											<td><?php $additional_info = nl2br($log_detail['arm_additional_info']); echo esc_html($additional_info);?></td>
										</tr>
									<?php endif;?>
									<?php if (isset($log_detail['arm_payment_transfer_mode']) && !empty($log_detail['arm_payment_transfer_mode'])): ?>
										<tr>
											<th><?php echo esc_html($transfer_mode_field_label);?></th>
											<td><?php $transfer_mode = nl2br($log_detail['arm_payment_transfer_mode']); echo esc_html($transfer_mode);?></td>
										</tr>
									<?php endif;?>
								<?php endif;?>
								<?php if ($log_detail['arm_payment_gateway'] == "manual" && !empty($extra_vars['note'])): ?>
								<tr>
									<th><?php esc_html_e('Note','armember-membership' );?></th>
									<td><?php $transaction_note= nl2br(stripslashes($extra_vars['note'])); echo esc_html($transaction_note);?></td>
								</tr>
								<?php endif;?>
								<tr>
									<th><?php esc_html_e('Payment Date','armember-membership' );?></th>
									<td><?php $payment_date = date_i18n($date_time_format, strtotime($log_detail['arm_created_date'])); echo esc_html($payment_date);?></td>
								</tr>
							</table>
						</div>

						<?php
							do_action('arm_add_preview_log_data_action', $log_detail, $log_id, $_POST);//phpcs:ignore
						?>
						<div class="armclear"></div>
					</div>
				</div>
				<?php
				echo $arm_ajax_pattern_end;
			}
			exit;
		}
		function arm_transaction_ajax_action() {
			global $wpdb, $ARMemberLite, $arm_members_class, $arm_global_settings, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_transactions'], '1' ); //phpcs:ignore --Reason:Verifying nonce

			$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data' ), $_POST ); //phpcs:ignore
			if ( ! isset( $posted_data ) ) {
				return;
			}
			$errors = array();
			$message = '';
			$action      = sanitize_text_field( $posted_data['act'] );
			$id          = intval( $posted_data['id'] );
			$type        = sanitize_text_field( $posted_data['type'] );
			$trxn_status = sanitize_text_field( $posted_data['trxn_status'] );
			if ( $action == 'delete' ) {
				if ( empty( $id ) ) {
					$errors[] = esc_html__( 'Invalid action.', 'armember-membership' );
				} else {
					if ( ! current_user_can( 'arm_manage_transactions' ) ) {
						$errors[] = esc_html__( 'Sorry, You do not have permission to perform this action.', 'armember-membership' );
					} else {
						if ( $type == 'bt_log' && $trxn_status != 'failed' ) {
							$res_var = $wpdb->delete( $ARMemberLite->tbl_arm_payment_log, array( 'arm_log_id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						} else {
							$res_var = $wpdb->delete( $ARMemberLite->tbl_arm_payment_log, array( 'arm_log_id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						}
						if ( $res_var ) {
							$message = esc_html__( 'Record is deleted successfully.', 'armember-membership' );
						} else {
							$errors[] = esc_html__( 'Sorry, Something went wrong. Please try again.', 'armember-membership' );
						}
					}
				}
			}
			$return_array = $arm_global_settings->handle_return_messages( $errors, $message );
			echo wp_json_encode( $return_array );
			exit;
		}
		function arm_bulk_delete_transactions() {
			global $wpdb, $ARMemberLite, $arm_members_class, $arm_global_settings, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_transactions'], '1' ); //phpcs:ignore --Reason:Verifying nonce
			$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data' ), $_POST ); //phpcs:ignore
			if ( ! isset( $posted_data ) ) {
				return;
			}
			$errors = array();
			$message = '';
			$bulkaction = $arm_global_settings->get_param( 'action1' );
			if ( $bulkaction == -1 ) {
				$bulkaction = $arm_global_settings->get_param( 'action2' );
			}
			$btids = $arm_global_settings->get_param( 'bt-item-action', '' );
			$ids   = $arm_global_settings->get_param( 'item-action', '' );
			if ( empty( $ids ) && empty( $btids ) ) {
				$errors[] = esc_html__( 'Please select one or more records.', 'armember-membership' );
			} else {
				if ( ! current_user_can( 'arm_manage_transactions' ) ) {
					$errors[] = esc_html__( 'Sorry, You do not have permission to perform this action.', 'armember-membership' );
				} else {
					if ( $bulkaction == 'delete_transaction' ) {
						$btids = ( ! is_array( $btids ) ) ? explode( ',', $btids ) : $btids;
						$ids   = ( ! is_array( $ids ) ) ? explode( ',', $ids ) : $ids;
						if ( is_array( $btids ) ) {
							foreach ( $btids as $id ) {
								$res_var = $wpdb->delete( $ARMemberLite->tbl_arm_payment_log, array( 'arm_log_id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
							}
						}
						if ( is_array( $ids ) ) {
							foreach ( $ids as $id ) {
								$res_var = $wpdb->delete( $ARMemberLite->tbl_arm_payment_log, array( 'arm_log_id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
							}
						}
						$message = esc_html__( 'Transaction(s) has been deleted successfully.', 'armember-membership' );
					} else {
						$errors[] = esc_html__( 'Please select valid action.', 'armember-membership' );
					}
				}
			}
			$return_array = $arm_global_settings->handle_return_messages( $errors, $message );
						$ARMemberLite->arm_set_message( 'success', esc_html__( 'Transaction(s) has been deleted successfully.', 'armember-membership' ) );
			echo wp_json_encode( $return_array );
			exit;
		}
		function arm_filter_transactions_list() {
			global $ARMemberLite, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_transactions'], '1' );

			if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions_list_records.php' ) ) {
				include MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions_list_records.php';
			}
			die();
		}
		function arm_add_manual_payment( $data = array() ) {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_subscription_plans,$arm_capabilities_global;
			$ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_transactions'], '1'); //phpcs:ignore --Reason:Verifying Nonce
			$redirect_to = admin_url( 'admin.php?page=' . $arm_slugs->transactions );
			if ( ! empty( $data ) ) {
				$manual_data = $data['manual_payment'];
				$user_id     = intval( $data['arm_user_id_hidden'] );
	
				$plan_id     = intval( $manual_data['plan_id'] );
				$user_info   = get_user_by( 'id', $user_id );
				$plan        = new ARM_Plan_Lite( $plan_id );
				/* Add transaction payment log */
				$manual_log = array(
					'arm_user_id'                  => $user_id,
					'arm_first_name'               => $user_info->first_name,
					'arm_last_name'                => $user_info->last_name,
					'arm_plan_id'                  => $plan_id,
					'arm_payment_gateway'          => 'manual',
					'arm_payer_email'              => $user_info->user_email,
					'arm_payment_type'             => $plan->payment_type,
					'arm_transaction_payment_type' => 'manual',
					'arm_transaction_status'       => sanitize_text_field( $manual_data['transaction_status'] ),
					'arm_amount'                   => sanitize_text_field( $manual_data['amount'] ),
					'arm_currency'                 => sanitize_text_field( $manual_data['currency'] ),
				);
				$log_id     = $this->arm_add_transaction( $manual_log );
				if ( $log_id ) {
					/* Action After Adding Plan */
					do_action( 'arm_saved_manual_payment', $data );
					$ARMemberLite->arm_set_message( 'success', esc_html__( 'Manual payment has been added successfully.', 'armember-membership' ) );
					wp_redirect( $redirect_to );
					exit;
				} else {
					$ARMemberLite->arm_set_message( 'error', esc_html__( 'Sorry, Something went wrong. please try again.', 'armember-membership' ) );
					$redirect_to = $arm_global_settings->add_query_arg( 'action', 'new', $redirect_to );
					wp_redirect( $redirect_to );
					exit;
				}
			}
			return;
		}
		function arm_add_transaction( $log_data = array() ) {

			global $wp, $wpdb, $ARMemberLite, $arm_subscription_plans,  $arm_payment_gateways;
			$currency         = $arm_payment_gateways->arm_get_global_currency();
			$default_log_data = array(
				'arm_invoice_id'               => 0,
				'arm_user_id'                  => 0,
				'arm_first_name'               => '',
				'arm_last_name'                => '',
				'arm_plan_id'                  => 0,
				'arm_payment_gateway'          => '',
				'arm_payment_type'             => '',
				'arm_token'                    => '',
				'arm_payer_email'              => '',
				'arm_receiver_email'           => '',
				'arm_transaction_id'           => '',
				'arm_transaction_payment_type' => '',
				'arm_transaction_status'       => '',
				'arm_payment_mode'             => '',
				'arm_payment_date'             => current_time( 'mysql' ),
				'arm_amount'                   => 0,
				'arm_currency'                 => $currency,
				'arm_extra_vars'               => '',
				'arm_coupon_code'              => '',
				'arm_coupon_discount'          => 0,
				'arm_coupon_discount_type'     => '',
				'arm_is_trial'                 => '0',
				'arm_created_date'             => current_time( 'mysql' ),
				'arm_display_log'              => '1',
			);
			$log_data         = shortcode_atts( $default_log_data, $log_data ); /* Merge Default Values */
			switch ( strtolower( $log_data['arm_transaction_status'] ) ) {
				case 'completed':
				case 'paid':
				case 'active':
				case 'trialing':
				case 'succeeded':
				case 'success':
					$log_data['arm_transaction_status'] = 'success';
					break;
				case 'pending':
				case 'past_due':
					$log_data['arm_transaction_status'] = 'pending';
					break;
				case 'canceled':
				case 'unpaid':
					$log_data['arm_transaction_status'] = 'canceled';
					$log_data['arm_coupon_code']        = $_REQUEST['arm_coupon_code'] = '';
					break;
				case 'failed':
					$log_data['arm_transaction_status'] = 'failed';
					$log_data['arm_coupon_code']        = $_REQUEST['arm_coupon_code'] = '';
					break;
				case 'expired':
					$log_data['arm_transaction_status'] = 'expired';
					$log_data['arm_coupon_code']        = $_REQUEST['arm_coupon_code'] = '';
					break;
				default:
					break;
			}
			$coupon_code = ! empty( $log_data['arm_coupon_code'] ) ? $log_data['arm_coupon_code'] : '';
			if ( ! empty( $coupon_code ) && $arm_manage_coupons->isCouponFeature ) {

							$log_data['arm_coupon_code']          = $coupon_code;
							$log_data['arm_coupon_discount']      = ! empty( $log_data['arm_coupon_discount'] ) ? $log_data['arm_coupon_discount'] : 0;
							$log_data['arm_coupon_discount_type'] = ! empty( $log_data['arm_coupon_discount_type'] ) ? $log_data['arm_coupon_discount_type'] : '';
				if ( $coupon_code != '' ) {
					$arm_manage_coupons->arm_update_coupon_used_count( $coupon_code );
				}
			} else {
				$log_data['arm_coupon_code'] = '';
			}
			/* Insert Payment Log Data. */

						$arm_last_invoice_id = get_option( 'arm_last_invoice_id', 0 );
						$arm_last_invoice_id++;
						$log_data['arm_invoice_id'] = $arm_last_invoice_id;

			do_action( 'arm_before_add_transaction', $log_data );

			$payment_log    = $wpdb->insert( $ARMemberLite->tbl_arm_payment_log, $log_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$payment_log_id = $wpdb->insert_id;

			if ( ! empty( $payment_log_id ) && $payment_log_id != 0 ) {
				update_option( 'arm_last_invoice_id', $arm_last_invoice_id );
				do_action( 'arm_after_add_transaction', $log_data );
				return $payment_log_id;
			} else {
				return false;
			}
		}
		function arm_mask_credit_card_number( $cc_number = '' ) {
			$masked = 'xxxx-xxxx-xxxx-' . substr( $cc_number, -4 );
			return $masked;
		}

		function arm_get_total_transaction( $user_id = 0 ) {
			global $ARMemberLite, $wp, $wpdb;
			$where_plog = ' WHERE arm_display_log = 1 ';
			if ( isset( $user_id ) && $user_id != '' && $user_id != 0 ) {
				$where_plog .= $wpdb->prepare(" AND `arm_user_id`=%d ",$user_id);
			}
			$total_payment_log_rows = 'SELECT COUNT(*) as count_plog FROM `' . $ARMemberLite->tbl_arm_payment_log . "` {$where_plog}";
			$count_payment_rows     = $wpdb->get_results( $total_payment_log_rows ); //phpcs:ignore --Reason: $ARMemberLite->tbl_arm_payment_log is a table name

			$totalRecord = intval( $count_payment_rows[0]->count_plog );
			return $totalRecord;
		}

		function arm_get_all_transaction( $user_id = 0, $offset = 0, $perPage = 5 ) {
			global $ARMemberLite, $wp, $wpdb;

			$ctquery = 'SELECT pt.arm_log_id,pt.arm_invoice_id,pt.arm_user_id,pt.arm_first_name,pt.arm_last_name,pt.arm_plan_id,pt.arm_transaction_id,pt.arm_amount,pt.arm_currency,pt.arm_payment_gateway,pt.arm_transaction_status,pt.arm_payment_type,pt.arm_extra_vars,wpu.user_login as arm_user_login,pt.arm_display_log as arm_display_log, pt.arm_payment_date, pt.arm_coupon_code, pt.arm_coupon_discount_type, pt.arm_coupon_discount, pt.arm_created_date FROM `' . $ARMemberLite->tbl_arm_payment_log . '` pt LEFT JOIN `' . $ARMemberLite->tbl_arm_subscription_plans . '` sp ON pt.arm_plan_id = sp.arm_subscription_plan_id LEFT JOIN `' . $wpdb->users . '` wpu ON pt.arm_user_id = wpu.ID ';
			$ptquery = "{$ctquery}";

			$where_plog = ' WHERE arm_display_log = 1 ';
			if ( isset( $user_id ) && $user_id != '' && $user_id != 0 ) {
				$where_plog .= $wpdb->prepare(" AND `arm_user_id`=%d ",$user_id);
			}
			$orderby = ' order by arm_payment_date desc, arm_invoice_id desc ';
			$phlimit = " LIMIT {$offset},{$perPage}";

			$user_plogs         = $wpdb->get_results( "SELECT * FROM (" . $ptquery . ") AS arm_payment_history_log ".$where_plog." ". $orderby." ".$phlimit, ARRAY_A );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_subscription_plans is a table name

			return $user_plogs;
		}

		function arm_get_user_transactions_with_pagging( $user_id, $current_page = 1, $perPage = 2, $plan_id_name_array = array() ) {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_global_settings, $arm_subscription_plans,$arm_payment_gateways, $global_currency_sym;
			$log_data            = $temp_logs = array();
			$date_format         = $arm_global_settings->arm_get_wp_date_time_format();
				$global_currency = $arm_payment_gateways->arm_get_global_currency();
			if ( ! empty( $user_id ) && $user_id != 0 ) {

						$perPage = ( ! empty( $perPage ) && is_numeric( $perPage ) ) ? $perPage : 5;
						$offset  = 0;
				if ( ! empty( $current_page ) && $current_page > 1 ) {
					$offset = ( $current_page - 1 ) * $perPage;
				}

						$totalRecord = $this->arm_get_total_transaction( $user_id );
						$user_logs   = $this->arm_get_all_transaction( $user_id, $offset, $perPage );

						$trans_records  = '';
						$trans_records .= '<div class="arm_user_transaction_wrapper" data-user_id="' . esc_attr($user_id) . '">';
						$trans_records .= '<table class="form-table arm_member_last_subscriptions_table" width="100%">';
					$trans_records     .= '<tr>';
					$trans_records     .= '<td>#</td>';
					$trans_records     .= '<td>' . esc_html__( 'Membership', 'armember-membership' ) . '</td>';
						$trans_records .= '<td>' . esc_html__( 'Payment Type', 'armember-membership' ) . '</td>';
						$trans_records .= '<td>' . esc_html__( 'Transaction Status', 'armember-membership' ) . '</td>';
						$trans_records .= '<td>' . esc_html__( 'Gateway', 'armember-membership' ) . '</td>';
						$trans_records .= '<td>' . esc_html__( 'Amount', 'armember-membership' ) . '</td>';

						$trans_records .= '<td>' . esc_html__( 'Payment Date', 'armember-membership' ) . '</td>';
						$trans_records .= '</tr>';

						$i                   = 1;
						$plan_ids_array      = array();
						$plan_ids_name_array = array();

				foreach ( $user_logs as $user_log ) {
					$rc = (object) $user_log;

					if ( in_array( $rc->arm_plan_id, $plan_ids_array ) ) {
						$subs_plan = $plan_ids_name_array[ $rc->arm_plan_id ];
					} else {
						$subs_plan = $plan_id_name_array[ $rc->arm_plan_id ];
					}
					$plan_ids_name_array[ $rc->arm_plan_id ] = $subs_plan;
					$plan_ids_array[]                        = $rc->arm_plan_id;
					$membership                              = ( ! empty( $subs_plan ) ) ? $subs_plan : '-';
					$payment_type                            = ( $rc->arm_payment_type == 'subscription' ) ? esc_html__( 'Subscription', 'armember-membership' ) : esc_html__( 'One Time', 'armember-membership' );

					$extraVars = ( ! empty( $rc->arm_extra_vars ) ) ? maybe_unserialize( $rc->arm_extra_vars ) : array();
					if ( ! empty( $extraVars ) ) {
						if ( isset( $extraVars['manual_by'] ) ) {
							$payment_type .= '<div style="font-size: 12px;"><em>(' . esc_html( $extraVars['manual_by'] ) . ')</em></div>';
						}
					}

					$arm_transaction_status = $rc->arm_transaction_status;
					switch ( $arm_transaction_status ) {
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

					$arm_transaction_status = $this->arm_get_transaction_status_text( $arm_transaction_status );

					$arm_gateway = ( $rc->arm_payment_gateway != '' ) ? $arm_payment_gateways->arm_gateway_name_by_key( $rc->arm_payment_gateway ) : esc_html__( 'Manual', 'armember-membership' );

					$t_currency  = ( isset( $rc->arm_currency ) && ! empty( $rc->arm_currency ) ) ? strtoupper( $rc->arm_currency ) : strtoupper( $global_currency );
					$currency    = ( isset( $all_currencies[ $t_currency ] ) ) ? $all_currencies[ $t_currency ] : $global_currency_sym;
					$transAmount = '';

					if ( ! empty( $extraVars ) && ! empty( $extraVars['plan_amount'] ) && $extraVars['plan_amount'] != 0 ) {
						$arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator( $t_currency, $extraVars['plan_amount'] );
						if ( $arm_plan_amount != $rc->arm_amount ) {
							$transAmount .= '<span class="arm_transaction_list_plan_amount">' . $arm_payment_gateways->arm_prepare_amount( $t_currency, $extraVars['plan_amount'] ) . '</span>';
						}
					}
					$transAmount .= '<span class="arm_transaction_list_paid_amount">';
					if ( ! empty( $rc->arm_amount ) && $rc->arm_amount > 0 ) {
							$transAmount .= $arm_payment_gateways->arm_prepare_amount( $t_currency, $rc->arm_amount );
						if ( $global_currency_sym == $currency && strtoupper( $global_currency ) != $t_currency ) {
										$transAmount .= ' (' . $t_currency . ')';
						}
					} else {
							$transAmount .= $arm_payment_gateways->arm_prepare_amount( $t_currency, $rc->arm_amount );
					}
					$transAmount .= '</span>';
					if ( ! empty( $extraVars ) && isset( $extraVars['trial'] ) ) {
							$trialInterval = $extraVars['trial']['interval'];
							$transAmount  .= '<span class="arm_transaction_list_trial_text">';
							$transAmount  .= esc_html__( 'Trial Period', 'armember-membership' ) . ": {$trialInterval} ";
						if ( $extraVars['trial']['period'] == 'Y' ) {
										$transAmount .= ( $trialInterval > 1 ) ? esc_html__( 'Years', 'armember-membership' ) : esc_html__( 'Year', 'armember-membership' );
						} elseif ( $extraVars['trial']['period'] == 'M' ) {
												$transAmount .= ( $trialInterval > 1 ) ? esc_html__( 'Months', 'armember-membership' ) : esc_html__( 'Month', 'armember-membership' );
						} elseif ( $extraVars['trial']['period'] == 'W' ) {
							$transAmount .= ( $trialInterval > 1 ) ? esc_html__( 'Weeks', 'armember-membership' ) : esc_html__( 'Week', 'armember-membership' );
						} elseif ( $extraVars['trial']['period'] == 'D' ) {
							$transAmount .= ( $trialInterval > 1 ) ? esc_html__( 'Days', 'armember-membership' ) : esc_html__( 'Day', 'armember-membership' );
						}
										$transAmount .= '</span>';
					}

					$trans_records .= '<tr class="arm_member_last_subscriptions_data">';
					$trans_records .= '<td>' . $i . '</td>';
					$trans_records .= '<td class="rec_center">' . $membership . '</td>';
					$trans_records .= '<td class="rec_center">' . $payment_type . '</td>';
					$trans_records .= '<td>' . $arm_transaction_status . '</td>';
					$trans_records .= '<td>' . $arm_gateway . '</td>';
					$trans_records .= '<td>' . $transAmount . '</td>';

					$trans_records .= '<td>' . date_i18n( $date_format, strtotime( $rc->arm_payment_date ) ) . '</td>';
					$trans_records .= '</tr>';
					$i++;
				}

				if ( $totalRecord <= 0 ) {
					$trans_records .= '<tr>';
					$trans_records .= '<td colspan="9" style="text-align: center;">' . esc_html__( 'No Payment History Found.', 'armember-membership' ) . '</td>';
					$trans_records .= '</tr>';
				}

						$trans_records .= '</table>';
						$trans_records .= '<div class="arm_membership_history_pagination_block">';
						$transPaging    = $arm_global_settings->arm_get_paging_links( $current_page, $totalRecord, $perPage, '' );
						$trans_records .= '<div class="arm_membership_history_paging_container">' . $transPaging . '</div>';
						$trans_records .= '</div>';
						$trans_records .= '</div>';

			}
				return $trans_records;
		}

		function arm_get_user_transactions_paging_action() {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings, $arm_payment_gateways, $arm_subscription_plans, $arm_capabilities_global;
			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_get_user_transactions_paging_action' ) { //phpcs:ignore
				$user_id            = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0; //phpcs:ignore
				$current_page       = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1; //phpcs:ignore
				$per_page           = isset( $_POST['per_page'] ) ? intval( $_POST['per_page'] ) : 5; //phpcs:ignore
				$plan_id_name_array = $arm_subscription_plans->arm_get_plan_name_by_id_from_array();

				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_members'], '1' ); //phpcs:ignore --Reason:Verifying nonce
				echo $this->arm_get_user_transactions_with_pagging( $user_id, $current_page, $per_page, $plan_id_name_array ); //phpcs:ignore
			}
			exit;
		}

		function arm_get_bank_transfer_logs( $filter_gateway = 0, $filter_ptype = 0, $filter_pstatus = 0, $limit = 0 ) {
			global $wp, $wpdb, $current_user, $arm_lite_errors, $ARMemberLite, $arm_global_settings, $arm_subscription_plans;
			$bt_logs = array();
			if ( empty( $filter_gateway ) || $filter_gateway == 'bank_transfer' || $filter_gateway == '0' ) {
				if ( empty( $filter_ptype ) || $filter_ptype == 'one_time' || $filter_ptype == '0' ) {
					$where_btlog = 'WHERE arm_payment_gateway="bank_transfer"';
					if ( ! empty( $filter_pstatus ) && $filter_pstatus != '0' ) {
						if ( $filter_pstatus == 'success' ) {
							$where_btlog .= $wpdb->prepare(" AND `arm_transaction_status`=%s",'1');
						}
						if ( $filter_pstatus == 'pending' ) {
							$where_btlog .= $wpdb->prepare(" AND `arm_transaction_status`=%s",'0');
						}
						if ( $filter_pstatus == 'canceled' ) {
							$where_btlog .= $wpdb->prepare(" AND `arm_transaction_status`=%s",'2');
						}
					}
					$sqlLimit = '';
					if ( ! empty( $limit ) && $limit != 0 ) {
						$sqlLimit = "LIMIT {$limit}";
					}
					$logs = $wpdb->get_results( "SELECT * FROM `".$ARMemberLite->tbl_arm_payment_log."` ".$where_btlog." ORDER BY `arm_created_date` DESC ".$sqlLimit );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name
					if ( ! empty( $logs ) ) {
						foreach ( $logs as $l ) {
							$bt_logs[] = $this->arm_convert_bt_to_main_log( $l );
						}
					}
				}
			}
			return $bt_logs;
		}
		function arm_convert_bt_to_main_log( $data ) {
			$main_logs = array();
			if ( ! empty( $data ) ) {
				$lStatus = 'pending';
				if ( $data->arm_transaction_status == '1' ) {
					$lStatus = 'success';
				}
				if ( $data->arm_transaction_status == '2' ) {
					$lStatus = 'canceled';
				}
				$main_log = array(
					'arm_log_id'                   => $data->arm_log_id,
					'arm_invoice_id'               => $data->arm_invoice_id,
					'arm_user_id'                  => $data->arm_user_id,
					'arm_first_name'               => $data->arm_first_name,
					'arm_last_name'                => $data->arm_last_name,
					'arm_plan_id'                  => $data->arm_plan_id,
					'arm_payment_gateway'          => 'bank_transfer',
					'arm_payment_type'             => $data->arm_payment_type,
					'arm_token'                    => '',
					'arm_payer_email'              => $data->arm_payer_email,
					'arm_receiver_email'           => '',
					'arm_transaction_id'           => $data->arm_transaction_id,
					'arm_transaction_payment_type' => $data->arm_transaction_payment_type,
					'arm_transaction_status'       => $lStatus,
					'arm_payment_date'             => $data->arm_created_date,
					'arm_amount'                   => $data->arm_amount,
					'arm_currency'                 => $data->arm_currency,
					'arm_extra_vars'               => maybe_unserialize( $data->arm_extra_vars ),

					'arm_created_date'             => $data->arm_created_date,
				);
			}
			return $main_log;
		}
		function arm_change_bank_transfer_status( $log_id = 0, $new_status = 0, $check_permission = 1 ) {
			global $wp, $wpdb, $current_user, $arm_lite_errors, $ARMemberLite, $arm_global_settings, $arm_subscription_plans, $arm_capabilities_global;
			if($check_permission)
			{
				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_transactions'], '1' ); //phpcs:ignore --Reason:Verifying nonce
			}

			$logid_exit_flag = '1';
			if ( empty( $log_id ) ) {
				$log_id          = isset( $_POST['log_id'] ) ? intval( $_POST['log_id'] ) : 0; //phpcs:ignore
				$logid_exit_flag = '';
			}
			if ( empty( $new_status ) ) {
				$new_status = isset( $_POST['log_status'] ) ? sanitize_text_field( $_POST['log_status'] ) : 0;  //phpcs:ignore
			}
			$response = array(
				'status'  => 'error',
				'message' => esc_html__( 'Sorry, Something went wrong. Please try again.', 'armember-membership' ),
			);
			if ( ! empty( $log_id ) && $log_id != 0 ) {
				$log_data = $wpdb->get_row( $wpdb->prepare("SELECT `arm_log_id`, `arm_user_id`, `arm_plan_id`, `arm_payment_cycle` FROM `".$ARMemberLite->tbl_arm_payment_log."` WHERE `arm_log_id`=%d",$log_id) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name
				if ( ! empty( $log_data ) ) {
					$user_id                           = $log_data->arm_user_id;
					$plan_id                           = $log_data->arm_plan_id;
										$payment_cycle = $log_data->arm_payment_cycle;
					if ( $new_status == '1' ) {

						$nowDate = current_time( 'mysql' );

						$arm_last_payment_status = $wpdb->get_var( $wpdb->prepare( 'SELECT `arm_transaction_status` FROM `'.$ARMemberLite->tbl_arm_payment_log.'` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1', $user_id, $plan_id, $nowDate ) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name
						$arm_subscription_plans->arm_update_user_subscription_for_bank_transfer( $user_id, $plan_id, 'bank_transfer', $payment_cycle, $arm_last_payment_status );
						$wpdb->update( $ARMemberLite->tbl_arm_payment_log, array( 'arm_transaction_status' => 1 ), array( 'arm_log_id' => $log_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
												do_action( 'arm_after_accept_bank_transfer_payment', $user_id, $plan_id );
						$response = array(
							'status'  => 'success',
							'message' => esc_html__( 'Bank transfer request has been approved.', 'armember-membership' ),
						);
					} else {
						delete_user_meta( $user_id, 'arm_change_plan_to' );
						$wpdb->update( $ARMemberLite->tbl_arm_payment_log, array( 'arm_transaction_status' => 2 ), array( 'arm_log_id' => $log_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
												do_action( 'arm_after_decline_bank_transfer_payment', $user_id, $plan_id );
						$response = array(
							'status'  => 'success',
							'message' => esc_html__( 'Bank transfer request has been cancelled.', 'armember-membership' ),
						);
					}
				}
			}
			if ( empty( $logid_exit_flag ) ) {
				echo wp_json_encode( $response );
				exit;
			}
		}
		function arm_get_transaction_status_text( $statuses = '' ) {
			$statusClass = 'active';
			$lStatus     = 'success';
			switch ( $statuses ) {
				case 'success':
					$statusClass = 'active';
					$lStatus     = esc_html__( 'success', 'armember-membership' );
					break;
				case 'pending':
					$statusClass = 'pending';
					$lStatus     = esc_html__( 'pending', 'armember-membership' );
					break;
				case 'canceled':
				case 'cancelled':
					$statusClass = 'canceled';
					$lStatus     = esc_html__( 'cancelled', 'armember-membership' );
					break;
				case 'failed':
					$statusClass = 'failed';
					$lStatus     = esc_html__( 'failed', 'armember-membership' );
					break;
				case 'expired':
					$statusClass = 'expired';
					$lStatus     = esc_html__( 'expired', 'armember-membership' );
					break;
				default:
					break;
			}
			return '<span class="arm_item_status_text_transaction ' . esc_attr($statusClass) . '">' . ucfirst( $lStatus ) . '</span>';
		}

		function arm_load_transaction_grid() {

			global $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_global_settings,  $arm_payment_gateways, $arm_subscription_plans, $armlite_default_user_details_text, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_transactions'], '1' );  //phpcs:ignore --Reason:Verifying nonce

			$date_format        = $arm_global_settings->arm_get_wp_date_format();
			$date_time_format   = $arm_global_settings->arm_get_wp_date_time_format();
			$global_currency    = $arm_payment_gateways->arm_get_global_currency();
			$filter_gateway     = isset( $_REQUEST['gateway'] ) ? sanitize_text_field($_REQUEST['gateway']) : '';//phpcs:ignore
			$filter_ptype       = isset( $_REQUEST['payment_type'] ) ? sanitize_text_field($_REQUEST['payment_type']) : '';//phpcs:ignore
			$filter_pmode       = isset( $_REQUEST['payment_mode'] ) ? sanitize_text_field($_REQUEST['payment_mode']) : '';//phpcs:ignore
			$filter_pstatus     = isset( $_REQUEST['payment_status'] ) ? sanitize_text_field($_REQUEST['payment_status']) : '';//phpcs:ignore
			$payment_start_date = isset( $_REQUEST['payment_start_date'] ) ? sanitize_text_field($_REQUEST['payment_start_date']) : '';//phpcs:ignore
			$payment_end_date   = isset( $_REQUEST['payment_end_date'] ) ? sanitize_text_field($_REQUEST['payment_end_date']) : '';//phpcs:ignore
			$response_data      = array();
			$nowDate            = current_time( 'mysql' );
			$where_plog         = 'WHERE 1=1 AND arm_display_log = 1 ';
			if ( ! empty( $filter_gateway ) && $filter_gateway != '0' ) {
				$where_plog .= $wpdb->prepare(" AND `arm_payment_gateway`=%s",$filter_gateway);
			}
			if ( ! empty( $filter_ptype ) && $filter_ptype != '0' ) {
				$where_plog .=  $wpdb->prepare(" AND `arm_payment_type`=%s",$filter_ptype);
			}
			if ( ! empty( $filter_pmode ) && $filter_pmode != '0' ) {
				$where_plog .=  $wpdb->prepare(" AND `arm_payment_mode`=%s",$filter_pmode);
			}

			if ( ! empty( $filter_pstatus ) && $filter_pstatus != '0' ) {
				$filter_pstatus = strtolower( $filter_pstatus );
				$status_query   = $wpdb->prepare(" AND ( LOWER(`arm_transaction_status`)=%s",$filter_pstatus);
				if ( ! in_array( $filter_pstatus, array( 'success', 'pending', 'canceled' ) ) ) {
					$status_query .= ')';
				}
				switch ( $filter_pstatus ) {
					case 'success':
						$status_query .= $wpdb->prepare(" OR `arm_transaction_status`=%s)",'1');
						break;
					case 'pending':
						$status_query .= $wpdb->prepare(" OR `arm_transaction_status`=%s)",'0');
						break;
					case 'canceled':
						$status_query .= $wpdb->prepare(" OR `arm_transaction_status`=%s)",'2');
						break;
				}
				$where_plog .= $status_query;
			}
			$total_count = $wpdb->get_results( 'SELECT COUNT(*) as total_logs FROM `' . $ARMemberLite->tbl_arm_payment_log . '`' );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name.

			$total_fpaylog = $total_count[0]->total_logs;

			$total_counter = $total_fpaylog;

			$sorting_ord = isset( $_REQUEST['sSortDir_0'] ) ? strtoupper( sanitize_text_field($_REQUEST['sSortDir_0']) ) : 'DESC'; //phpcs:ignore
			$sorting_ord = strtolower( $sorting_ord );
			$sorting_col = ( isset( $_REQUEST['iSortCol_0'] ) && $_REQUEST['iSortCol_0'] > 0 ) ? intval($_REQUEST['iSortCol_0']) : ''; //phpcs:ignore
			if ( ( isset( $_REQUEST['iSortCol_0'] ) && $_REQUEST['iSortCol_0'] == 0 ) || ( 'asc' != $sorting_ord && 'desc' != $sorting_ord ) ) { //phpcs:ignore
				$sorting_ord = 'DESC';
			}
			$offset = isset( $_REQUEST['iDisplayStart'] ) ? intval($_REQUEST['iDisplayStart']) : 0; //phpcs:ignore
			$limit  = isset( $_REQUEST['iDisplayLength'] ) ? intval($_REQUEST['iDisplayLength']) : 10; //phpcs:ignore

			$phlimit = " LIMIT {$offset},{$limit}";

			switch ( $sorting_col ) {
				case 1:
					$column_name = '`arm_transaction_id`';
					break;
				case 2:
					$column_name = '`arm_first_name`';
					break;
				case 3:
					$column_name = '`arm_last_name`';
					break;
				case 5:
					$column_name = '`arm_subscription_plan_name`';
					break;
				case 6:
					$column_name = '`arm_payment_gateway`';
					break;
				case 7:
					$column_name = '`arm_payment_type`';
					break;
				case 8:
					$column_name = '`arm_payer_email`';
					break;
				case 9:
					$column_name = '`arm_transaction_status`';
					break;
				case 10:
					$column_name = '`arm_created_date`';
					break;
				case 11:
					$column_name = '`arm_amount`';
					break;
				default:
					$column_name = '`arm_created_date`';
					break;
			}
			$orderby = "ORDER BY `arm_payment_history_log`.{$column_name} {$sorting_ord}";

			$sSearch = isset( $_REQUEST['sSearch'] ) ? sanitize_text_field($_REQUEST['sSearch']) : ''; //phpcs:ignore
			$search_ = '';
			if ( $sSearch != '' ) {
				$search_ = $wpdb->prepare(" AND (`arm_payment_history_log`.`arm_transaction_id` LIKE %s OR `arm_payment_history_log`.`arm_payer_email` LIKE %s OR `arm_payment_history_log`.`arm_created_date` LIKE %s OR `arm_payment_history_log`.`arm_first_name` LIKE %s OR `arm_payment_history_log`.`arm_last_name` LIKE %s ) ",'%'.$sSearch.'%','%'.$sSearch.'%','%'.$sSearch.'%','%'.$sSearch.'%','%'.$sSearch.'%');
			}

			$pt_where = $bt_where = '';
			if ( ! empty( $payment_start_date ) ) {
				$payment_start_date = date( 'Y-m-d', strtotime( $payment_start_date ) ); //phpcs:ignore
				$pt_where          .= " WHERE `pt`.`arm_created_date` >= '$payment_start_date' ";
				$bt_where          .= " WHERE `bt`.`arm_created_date` >= '$payment_start_date' ";
			}

			if ( ! empty( $payment_end_date ) ) {
				$payment_end_date = date( 'Y-m-d', strtotime( '+1 day', strtotime( $payment_end_date ) ) ); //phpcs:ignore
				if ( $pt_where != '' ) {
					$pt_where .= ' AND ';
				} else {
					$pt_where = ' WHERE ';
				}
				$pt_where .= " `pt`.`arm_created_date` < '$payment_end_date' ";

				if ( $bt_where != '' ) {
					$bt_where .= ' AND ';
				} else {
					$bt_where = ' WHERE ';
				}
				$bt_where .= " `bt`.`arm_created_date` < '$payment_end_date' ";
			}

			$ctquery = 'SELECT pt.arm_log_id,pt.arm_invoice_id,pt.arm_user_id,pt.arm_first_name,pt.arm_last_name,pt.arm_plan_id,pt.arm_payer_email,pt.arm_transaction_id,pt.arm_amount,pt.arm_currency,pt.arm_is_trial,pt.arm_payment_gateway,pt.arm_payment_mode,pt.arm_transaction_status,pt.arm_created_date,pt.arm_payment_type,pt.arm_extra_vars,sp.arm_subscription_plan_name,wpu.user_login as arm_user_login,pt.arm_display_log as arm_display_log FROM `' . $ARMemberLite->tbl_arm_payment_log . '` pt LEFT JOIN `' . $ARMemberLite->tbl_arm_subscription_plans . '` sp ON pt.arm_plan_id = sp.arm_subscription_plan_id LEFT JOIN `' . $wpdb->users . '` wpu ON pt.arm_user_id = wpu.ID ' . $pt_where;
			$ptquery = "{$ctquery}";


			$payment_rows  = $wpdb->get_results( $wpdb->prepare('SELECT (SELECT COUNT(*) FROM `' . $ARMemberLite->tbl_arm_payment_log . '` WHERE `arm_display_log` = %d) as total_payment_log',1) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name
			$before_filter = intval( $payment_rows[0]->total_payment_log );

			$ex_query                  = $wpdb->get_results( 'SELECT COUNT(*) AS total_payments FROM (' . $ptquery . ") AS arm_payment_history_log ".$where_plog." ".$search_." ".$orderby);//phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name

			$after_filter = intval( $ex_query[0]->total_payments );		

			$phquery = $wpdb->get_results('SELECT * FROM (' . $ptquery . ") AS arm_payment_history_log ".$where_plog." ".$search_." ".$orderby." ".$phlimit, ARRAY_A ); //phpcs:ignore --Reason $ptquery is a joined table

			$payment_log = $phquery;
			if ( ! empty( $payment_log ) ) {
				$effectiveData = array();
				$ai            = 0;
				foreach ( $payment_log as $rc ) {
					$rc                     = (object) $rc;
					$transactionID          = intval( $rc->arm_log_id );
					$arm_transaction_status = $rc->arm_transaction_status;
					switch ( $arm_transaction_status ) {
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
					$log_type  = ( $rc->arm_payment_gateway == 'bank_transfer' ) ? 'bt_log' : 'other';
					$extraVars = ( isset( $rc->arm_extra_vars ) ) ? maybe_unserialize( $rc->arm_extra_vars ) : array();
					if ( $rc->arm_payment_gateway == 'bank_transfer' ) :
						$response_data[ $ai ][0] = '<input id="cb-item-action-' . esc_attr( $rc->arm_log_id ) . '" class="chkstanard arm_bt_transaction_bulk_check" type="checkbox" value="' . esc_attr( $rc->arm_log_id ) . '" name="item-action[]">';
					else :
						$response_data[ $ai ][0] = '<input id="cb-item-action-' . esc_attr( $rc->arm_log_id ) . '" class="chkstanard arm_transaction_bulk_check" type="checkbox" value="' . esc_attr( $rc->arm_log_id ) . '" name="item-action[]">';
					endif;
					$response_data[ $ai ][1] = ( ! empty( $rc->arm_transaction_id ) ) ? $rc->arm_transaction_id : esc_html__( 'Manual', 'armember-membership' );

					$data = get_userdata( $rc->arm_user_id );
					if ( ! empty( $data ) ) {
						$response_data[ $ai ][2] = $rc->arm_first_name;
						$response_data[ $ai ][3] = $rc->arm_last_name;
						$response_data[ $ai ][4] = $data->user_login;
					} else {
						$response_data[ $ai ][2] = $armlite_default_user_details_text;
						$response_data[ $ai ][3] = $armlite_default_user_details_text;
						$response_data[ $ai ][4] = $armlite_default_user_details_text;
					}
					$response_data[ $ai ][5] = $arm_subscription_plans->arm_get_plan_name_by_id( $rc->arm_plan_id );

					$userPlanData = get_user_meta( $rc->arm_user_id, 'arm_user_plan_' . $rc->arm_plan_id, true );

					$change_plan = $subscr_effective = '';
					if ( ! empty( $userPlanData ) ) {
						$change_plan      = $userPlanData['arm_change_plan_to'];
						$subscr_effective = $userPlanData['arm_subscr_effective'];
					}

					if ( ! isset( $effectiveData[ $rc->arm_user_id ] ) && ! empty( $change_plan ) && $change_plan == $rc->arm_plan_id && $subscr_effective > strtotime( $nowDate ) ) {
						$response_data[ $ai ][5]            .= '<div>' . esc_html__( 'Effective from', 'armember-membership' ) . ' ' . date_i18n( $date_format, $subscr_effective ) . '</div>';
						$effectiveData[ $rc->arm_user_id ][] = $change_plan;
					}
					if ( $rc->arm_payment_gateway == '' ) {
						$payment_gateway = esc_html__( 'Manual', 'armember-membership' );
					} else {
						$payment_gateway = $arm_payment_gateways->arm_gateway_name_by_key( $rc->arm_payment_gateway );
					}
					$response_data[ $ai ][6] = $payment_gateway;
					$payment_type            = $rc->arm_payment_type;
					$payment_type_text       = '';

					$plan_id           = $rc->arm_plan_id;
					$plan_info         = new ARM_Plan_Lite( $plan_id );
					$user_payment_mode = '';

					$log_payment_mode = isset( $rc->arm_payment_mode ) ? $rc->arm_payment_mode : '';

					if ( $plan_info->is_recurring() ) {
						if ( $log_payment_mode != '' ) {
							if ( $log_payment_mode == 'manual_subscription' ) {
								$user_payment_mode .= '';
							} else {
								$user_payment_mode .= '<span>(' . esc_html__( 'Automatic', 'armember-membership' ) . ')</span>';
							}
						}

						$payment_type = 'subscription';
					}

					if ( $payment_type == 'one_time' ) {
							$payment_type_text = esc_html__( 'One Time', 'armember-membership' );
					} elseif ( $payment_type == 'subscription' ) {

							$payment_type_text = esc_html__( 'Subscription', 'armember-membership' );
					}

					$arm_trial_tran = ( $rc->arm_is_trial == 1 ) ? ' (Trial Transaction)' : '';

					$response_data[ $ai ][7] = $payment_type_text . ' ' . $user_payment_mode . $arm_trial_tran;
					$payer_email             = '';
					if ( $rc->arm_payer_email == '' ) {
						$extra = maybe_unserialize( $rc->arm_extra_vars );
						if ( $extra != '' ) {
							if ( array_key_exists( 'manual_by', $extra ) ) {

								$payer_email = '<em>' . esc_html( $extra['manual_by'] ) . '</em>';
							}
						}
					} else {
						$payer_email = $rc->arm_payer_email;
					}

					if ( $payer_email == '' ) {
						$payer_email = $armlite_default_user_details_text;
					}

					$response_data[ $ai ][8] = $payer_email;

					$transStatus   = $this->arm_get_transaction_status_text( $arm_transaction_status );
					$failed_reason = ( isset( $extraVars['error'] ) && ! empty( $extraVars['error'] ) ) ? $extraVars['error'] : '';
					if ( $rc->arm_transaction_status == 'failed' && ! empty( $failed_reason ) ) {
						$transStatus = '<span class="armhelptip" title="' . esc_attr($failed_reason) . '">' . $transStatus . '</span>';
					}
					$response_data[ $ai ][9]  = $transStatus;
					$response_data[ $ai ][10] = date_i18n( $date_time_format, strtotime( $rc->arm_created_date ) );
					$rc->arm_currency         = ( isset( $rc->arm_currency ) && ! empty( $rc->arm_currency ) ) ? strtoupper( $rc->arm_currency ) : strtoupper( $global_currency );
					$response_data[ $ai ][11] = $arm_payment_gateways->arm_amount_set_separator( $rc->arm_currency, $rc->arm_amount ) . ' ' . strtoupper( $rc->arm_currency );

					$response_data[ $ai ][12] = ( isset( $extraVars['card_number'] ) && ! empty( $extraVars['card_number'] ) ) ? $extraVars['card_number'] : '-';
					$gridAction               = "<div class='arm_grid_action_btn_container'>";

					$gridAction              .= "<a class='armhelptip arm_preview_log_detail' href='javascript:void(0)' data-log_type='" . esc_attr($log_type) . "' data-log_id='" . esc_attr($transactionID) . "' data-trxn_status='" . esc_attr($arm_transaction_status) . "' title='" . esc_html__( 'View Detail', 'armember-membership' ) . "'><img src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_preview.png' onmouseover=\"this.src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_preview_hover.png';\" onmouseout=\"this.src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_preview.png';\" /></a>"; //phpcs:ignore 
					$gridAction              .= "<a href='javascript:void(0)' data-log_type='" . esc_attr($log_type) . "' data-delete_log_id='" . esc_attr($transactionID) . "' data-trxn_status='" . esc_attr($arm_transaction_status) . "' onclick='showConfirmBoxCallback(".esc_attr($transactionID).");'><img src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_delete.png' class='armhelptip' title='" . esc_html__( 'Delete', 'armember-membership' ) . "' onmouseover=\"this.src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_delete_hover.png';\" onmouseout=\"this.src='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/grid_delete.png';\" /></a>"; //phpcs:ignore 
					$gridAction              .= $arm_global_settings->arm_get_confirm_box( $transactionID, esc_html__( 'Are you sure you want to delete this transaction?', 'armember-membership' ), 'arm_transaction_delete_btn', $log_type );
					$gridAction              .= '</div>';
					$response_data[ $ai ][13] = $gridAction;
					$ai++;
				}
			}

			$columns = ',' . esc_html__( 'Transaction ID', 'armember-membership' ) . ',' . esc_html__( 'User', 'armember-membership' ) . ',' . esc_html__( 'Membership', 'armember-membership' ) . ',' . esc_html__( 'Gateway', 'armember-membership' ) . ',' . esc_html__( 'Payment Type', 'armember-membership' ) . ',' . esc_html__( 'Payer Email', 'armember-membership' ) . ',' . esc_html__( 'Transaction Status', 'armember-membership' ) . ',' . esc_html__( 'Payment Date', 'armember-membership' ) . ',' . esc_html__( 'Amount', 'armember-membership' ) . ',' . esc_html__( 'Credit Card Number', 'armember-membership' ) . ',';
			$sEcho   = isset( $_REQUEST['sEcho'] ) ? intval( $_REQUEST['sEcho'] ) : ''; //phpcs:ignore
			$output  = array(
				'sColumn'              => $columns,
				'sEcho'                => $sEcho,
				'iTotalRecords'        => $before_filter, // Before Filtered Records
				'iTotalDisplayRecords' => $after_filter, // After filter records,
				'aaData'               => $response_data,
			);
			echo wp_json_encode( $output );
			die();
		}

	}

}
global $arm_transaction;
$arm_transaction = new ARM_transaction_Lite();
