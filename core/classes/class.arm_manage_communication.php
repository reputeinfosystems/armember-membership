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
			echo arm_pattern_json_encode( $return_array ); //phpcs:ignore
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

	}

}
global $arm_manage_communication;
$arm_manage_communication = new ARM_manage_communication_Lite();
