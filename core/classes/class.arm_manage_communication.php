<?php 
if ( ! class_exists( 'ARM_manage_communication_Lite' ) ) {

	class ARM_manage_communication_Lite {

		function __construct() {
			global $wpdb, $ARMemberLite, $arm_slugs;

			add_action( 'arm_user_plan_status_action_failed_payment', array( $this, 'arm_user_plan_status_action_mail' ), 10, 2 );
			add_action( 'arm_user_plan_status_action_cancel_payment', array( $this, 'arm_user_plan_status_action_mail' ), 10, 2 );
			add_action( 'arm_user_plan_status_action_eot', array( $this, 'arm_user_plan_status_action_mail' ), 10, 2 );

		}

		function arm_delete_single_communication() {
			global $wpdb, $ARMemberLite, $arm_slugs, $arm_subscription_plans, $arm_global_settings, $arm_capabilities_global;
			$ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_email_notifications'], '1');
			$action = sanitize_text_field( $_POST['act'] ); //phpcs:ignore
			$id     = intval( $_POST['id'] ); //phpcs:ignore
			if ( $action == 'delete' ) {
				if ( empty( $id ) ) {
					$errors[] = esc_html__( 'Invalid action.', 'armember-membership' );
				} else {
					if ( ! current_user_can( 'arm_manage_communication' ) ) {
						$errors[] = esc_html__( 'Sorry, You do not have permission to perform this action.', 'armember-membership' );
					} else {
						$res_var = $wpdb->delete( $ARMemberLite->tbl_arm_auto_message, array( 'arm_message_id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						if ( $res_var ) {
							$message = esc_html__( 'Message has been deleted successfully.', 'armember-membership' );
						}
					}
				}
			}
			$return_array = $arm_global_settings->handle_return_messages( @$errors, @$message );
			echo wp_json_encode( $return_array );
			exit;
		}

		function arm_delete_bulk_communication() {
			if ( ! isset( $_POST ) ) { //phpcs:ignore
				return;
			}
			global $wp, $wpdb, $current_user, $arm_lite_errors, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_email_notifications'], '1');

			$bulkaction = $arm_global_settings->get_param( 'action1' );
			if ( $bulkaction == -1 ) {
				$bulkaction = $arm_global_settings->get_param( 'action2' );
			}
			$ids = $arm_global_settings->get_param( 'item-action', '' );
			if ( empty( $ids ) ) {
				$errors[] = esc_html__( 'Please select one or more records.', 'armember-membership' );
			} else {
				if ( ! current_user_can( 'arm_manage_communication' ) ) {
					$errors[] = esc_html__( 'Sorry, You do not have permission to perform this action.', 'armember-membership' );
				} else {
					if ( ! is_array( $ids ) ) {
						$ids = explode( ',', $ids );
					}
					if ( is_array( $ids ) ) {
						if ( $bulkaction == 'delete_communication' ) {
							foreach ( $ids as $msg_id ) {
								$res_var = $wpdb->delete( $ARMemberLite->tbl_arm_auto_message, array( 'arm_message_id' => $msg_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
							}
							if ( $res_var ) {
								$message = esc_html__( 'Message(s) has been deleted successfully.', 'armember-membership' );
							}
						} else {
							$errors[] = esc_html__( 'Please select valid action.', 'armember-membership' );
						}
					}
				}
			}
			$return_array = $arm_global_settings->handle_return_messages( @$errors, @$message );
			echo wp_json_encode( $return_array );
			exit;
		}

		function arm_user_plan_status_action_mail( $args = array(), $plan_obj = array() ) {
			global $wpdb, $ARMemberLite, $arm_slugs, $arm_subscription_plans, $arm_global_settings;
			if ( ! empty( $args['action'] ) ) {
				$now             = current_time( 'timestamp' );
				$user_id         = $args['user_id'];
				$plan_id         = $args['plan_id'];
				$alreadysentmsgs = array();

				$defaultPlanData  = $arm_subscription_plans->arm_default_plan_array();
				$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
				$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
				$planData         = shortcode_atts( $defaultPlanData, $userPlanDatameta );

				if ( ! empty( $planData ) ) {
					if ( isset( $planData['arm_sent_msgs'] ) && ! empty( $planData['arm_sent_msgs'] ) ) {
						$alreadysentmsgs = $planData['arm_sent_msgs'];
					}
				}

				$notification_type = '';
				switch ( $args['action'] ) {
					case 'on_failed':
					case 'failed_payment':
						$notification_type = 'on_failed';
						break;
					case 'on_next_payment_failed':
						$notification_type = 'on_next_payment_failed';
						break;
					case 'on_cancel_subscription':
					case 'on_cancel':
					case 'cancel_payment':
					case 'cancel_subscription':
						$notification_type = 'on_cancel_subscription';
						break;
					case 'on_expire':
					case 'eot':
						$notification_type = 'on_expire';
						break;
					case 'on_new_subscription':
					case 'new_subscription':
						$notification_type = 'on_new_subscription';
						break;
					case 'on_change_subscription':
					case 'change_subscription':
						$notification_type = 'on_change_subscription';
						break;
					case 'on_renew_subscription':
					case 'renew_subscription':
						$notification_type = 'on_renew_subscription';
						break;
					case 'on_success_payment':
					case 'success_payment':
						$notification_type = 'on_success_payment';
						break;
					case 'on_change_subscription_by_admin':
						$notification_type = 'on_change_subscription_by_admin';
						break;

					default:
						break;
				}
			}
		}



		function arm_filter_communication_content( $content = '', $user_id = 0, $user_plan = 0, $key = '' ) {
			global $wpdb, $ARMemberLite, $arm_slugs, $arm_subscription_plans, $arm_global_settings, $arm_payment_gateways, $wp_hasher;
			if ( ! empty( $content ) && ! empty( $user_id ) ) {
				$date_format = $arm_global_settings->arm_get_wp_date_format();
				$currency    = $arm_payment_gateways->arm_get_global_currency();
				$user_plan   = ( ! empty( $user_plan ) && $user_plan != 0 ) ? $user_plan : 0;
				$plan_name   = $arm_subscription_plans->arm_get_plan_name_by_id( $user_plan );

				$defaultPlanData  = $arm_subscription_plans->arm_default_plan_array();
				$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $user_plan, true );
				$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
				$planData         = shortcode_atts( $defaultPlanData, $userPlanDatameta );

				$arm_plan_detail      = $planData['arm_current_plan_detail'];
				$using_gateway        = $planData['arm_user_gateway'];
				$arm_plan_description = $arm_plan_detail['arm_subscription_plan_description'];
				if ( isset( $arm_plan_detail['arm_subscription_plan_type'] ) && $arm_plan_detail['arm_subscription_plan_type'] == 'recurring' ) {
					$arm_user_plan_info = new ARM_Plan_Lite( 0 );
					$arm_user_plan_info->init( (object) $arm_plan_detail );
					$arm_user_payment_cycle = isset( $arm_plan_detail['arm_user_selected_payment_cycle'] ) ? $arm_plan_detail['arm_user_selected_payment_cycle'] : '';
					$arm_user_plan_data     = $arm_user_plan_info->prepare_recurring_data( $arm_user_payment_cycle );
					$plan_amount            = isset( $arm_user_plan_data['amount'] ) ? $arm_user_plan_data['amount'] : 0;
				} else {
					$plan_amount = isset( $arm_plan_detail['arm_subscription_plan_amount'] ) ? $arm_plan_detail['arm_subscription_plan_amount'] : 0;
				}

				$u_payable_amount = 0;

				$plan_expire = esc_html__( 'Never Expires', 'armember-membership' );
				$expire_time = $planData['arm_expire_plan'];
				if ( ! empty( $expire_time ) ) {
					$plan_expire = date_i18n( $date_format, $expire_time );
				}

				$plan_next_due_date = '-';
				$next_due_date      = $planData['arm_next_due_payment'];
				if ( ! empty( $next_due_date ) ) {
					$plan_next_due_date = date_i18n( $date_format, $next_due_date );
				}

				$user_info     = get_userdata( $user_id );
				$blog_name     = get_bloginfo( 'name' );
				$blog_url      = ARMLITE_HOME_URL;
				$u_email       = $user_info->user_email;
				$u_displayname = $user_info->display_name;
				$u_username    = $user_info->user_login;
				$u_fname       = $user_info->first_name;
				$u_lname       = $user_info->last_name;
				$u_nicename    = $user_info->user_nicename;
				$networ_name   = get_site_option( 'site_name' );
				$networ_url    = get_site_option( 'siteurl' );

				if ( $key != '' && ! empty( $key ) ) {

					$change_password_page_id = isset( $arm_global_settings->global_settings['change_password_page_id'] ) ? $arm_global_settings->global_settings['change_password_page_id'] : 0;
					if ( $change_password_page_id == 0 ) {
						$arm_reset_password_link = network_site_url( 'wp-login.php?action=rp&key=' . rawurlencode( $key ) . '&login=' . rawurlencode( $u_username ), 'login' );
					} else {
						$arm_change_password_page_url = $arm_global_settings->arm_get_permalink( '', $change_password_page_id );
						$arm_change_password_page_url = $arm_global_settings->add_query_arg( 'action', 'armrp', $arm_change_password_page_url );
						$arm_change_password_page_url = $arm_global_settings->add_query_arg( 'key', rawurlencode( $key ), $arm_change_password_page_url );
						$arm_change_password_page_url = $arm_global_settings->add_query_arg( 'login', rawurlencode( $u_username ), $arm_change_password_page_url );
						$arm_reset_password_link      = $arm_change_password_page_url;
					}
					$content = str_replace( '{ARM_MESSAGE_RESET_PASSWORD_LINK}', $arm_reset_password_link, $content );
				} else {

					$content = str_replace( '{ARM_MESSAGE_RESET_PASSWORD_LINK}', '', $content );
				}

				 $selectColumns = '`arm_log_id`, `arm_user_id`, `arm_transaction_id`, `arm_amount`, `arm_is_trial`, `arm_extra_vars`';
				$where_bt       = '';
				if ( $using_gateway == 'bank_transfer' ) {
					/* Change Log Table For Bank Transfer Method */
					$armLogTable = $ARMemberLite->tbl_arm_payment_log;
					$where_bt    = $wpdb->prepare(" AND arm_payment_gateway=%s",'bank_transfer');
				} else {
					$armLogTable    = $ARMemberLite->tbl_arm_payment_log;
					$selectColumns .= ', `arm_token`';

				}

				$log_detail      = $wpdb->get_row( $wpdb->prepare("SELECT {$selectColumns} FROM `{$armLogTable}` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d {$where_bt} ORDER BY `arm_log_id` DESC",$user_id,$user_plan) );//phpcs:ignore --Reason: $armLogTable is a table name False Positive Alarm.
				$u_plan_discount = 0;
				$u_trial_amount  = 0;
				if ( ! empty( $log_detail ) ) {
					$u_transaction_id = $log_detail->arm_transaction_id;
					$u_payable_amount = $log_detail->arm_amount;

					$extravars = maybe_unserialize( $log_detail->arm_extra_vars );

					if ( ! empty( $log_detail->arm_is_trial ) && $log_detail->arm_is_trial == 1 ) {
						$u_trial_amount = isset( $extravars['trial']['amount'] ) ? $extravars['trial']['amount'] : 0;

					}
				}

				$profile_link = $arm_global_settings->arm_get_user_profile_url( $user_id );
				$content      = str_replace( '{ARM_MESSAGE_BLOGNAME}', $blog_name, $content );
				$content      = str_replace( '{ARM_MESSAGE_BLOGURL}', $blog_url, $content );
				$content      = str_replace( '{ARM_MESSAGE_NETWORKNAME}', $networ_name, $content );
				$content      = str_replace( '{ARM_MESSAGE_NETWORKURL}', $networ_url, $content );
				$content      = str_replace( '{ARM_MESSAGE_USERNAME}', $u_username, $content );
				$content      = str_replace( '{ARM_MESSAGE_USER_ID}', $user_id, $content );
				$content      = str_replace( '{ARM_MESSAGE_EMAIL}', $u_email, $content );
				$content      = str_replace( '{ARM_MESSAGE_USERNICENAME}', $u_nicename, $content );
				$content      = str_replace( '{ARM_MESSAGE_USERDISPLAYNAME}', $u_displayname, $content );
				$content      = str_replace( '{ARM_MESSAGE_USERFIRSTNAME}', $u_fname, $content );
				$content      = str_replace( '{ARM_MESSAGE_USERLASTNAME}', $u_lname, $content );
				$content      = str_replace( '{ARM_MESSAGE_SUBSCRIPTIONNAME}', $plan_name, $content );
				$content      = str_replace( '{ARM_MESSAGE_SUBSCRIPTIONDESCRIPTION}', $arm_plan_description, $content );
				$content      = str_replace( '{ARM_MESSAGE_SUBSCRIPTION_AMOUNT}', $plan_amount, $content );

				$content = str_replace( '{ARM_MESSAGE_TRIAL_AMOUNT}', $u_trial_amount, $content );
				$content = str_replace( '{ARM_MESSAGE_PAYABLE_AMOUNT}', $u_payable_amount, $content );
				$content = str_replace( '{ARM_MESSAGE_CURRENCY}', $currency, $content );
				$content = str_replace( '{ARM_MESSAGE_SUBSCRIPTION_EXPIRE}', $plan_expire, $content );
				$content = str_replace( '{ARM_MESSAGE_SUBSCRIPTION_NEXT_DUE}', $plan_next_due_date, $content );
				$content = str_replace( '{ARM_PROFILE_LINK}', $profile_link, $content );
				/* Content replace for user meta */
				$matches = array();
				preg_match_all( "/\b(\w*ARM_USERMETA_\w*)\b/", $content, $matches, PREG_PATTERN_ORDER );
				$matches = $matches[0];
				if ( ! empty( $matches ) ) {
					foreach ( $matches as $mat_var ) {
						$key      = str_replace( 'ARM_USERMETA_', '', $mat_var );
						$meta_val = '';
						if ( ! empty( $key ) ) {
							$meta_val = do_shortcode('[arm_usermeta id='.$user_id.' meta="'.$key.'"]',true);
							/* $meta_val = get_user_meta( $user_id, $key, true );
							if ( is_array( $meta_val ) ) {
								$meta_val = implode( ',', $meta_val );
							} */
						}
						$content = str_replace( '{' . $mat_var . '}', $meta_val, $content );
					}
				}
			}
			$content = nl2br( $content );
			$content = apply_filters( 'arm_change_advanced_email_communication_email_notification', $content, $user_id, $user_plan );
			return $content;
		}

		function arm_get_communication_messages_by( $field = '', $value = '' ) {
			global $wpdb, $ARMemberLite, $arm_slugs, $arm_subscription_plans, $arm_global_settings;
			$messages = array();
			if ( ! empty( $field ) && ! empty( $value ) ) {
				$field_key = $field;
				switch ( $field ) {
					case 'id':
					case 'message_id':
					case 'arm_message_id':
						$field_key = 'arm_message_id';
						break;
					case 'type':
					case 'message_type':
					case 'arm_message_type':
						$field_key = 'arm_message_type';
						break;
					case 'status':
					case 'message_status':
					case 'arm_message_status':
						$field_key = 'arm_message_status';
						break;
					default:
						break;
				}
				$results = $wpdb->get_results( $wpdb->prepare('SELECT * FROM `' . $ARMemberLite->tbl_arm_auto_message . "` WHERE `arm_message_status`=%d AND `$field_key`=%s",1	,$value) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_auto_message is a table name
				if ( ! empty( $results ) ) {
					$messages = $results;
				}
			}
			return $messages;
		}

	}

}
global $arm_manage_communication;
$arm_manage_communication = new ARM_manage_communication_Lite();
