<?php 
if(! defined('ABSPATH')) exit;

if ( ! class_exists( 'ARM_Reset_Data_Lite' ) ) {

	class ARM_Reset_Data_Lite {

		const arm_rd_last_reset_option   = 'arm_last_data_reset';
		const arm_rd_member_batch_keep   = 100;
		const arm_rd_member_batch_delete = 10;
		const arm_rd_table_batch         = 1000;
		const arm_rd_table_loops         = 5;

		var $arm_rd_process_order = array( 'members', 'transactions', 'activity', 'logs' );
		var $arm_rd_table_exists  = array();
		var $arm_rd_modules       = null;
		var $arm_rd_suppress_mail = false;
		var $arm_rd_protected_ids = null;
		var $arm_rd_blocked_tables = null;

		var $arm_rd_protected_caps = array( 'manage_options', 'manage_network', 'delete_users', 'edit_users', 'promote_users', 'create_users' );

		function __construct() {
			add_action( 'wp_ajax_arm_reset_data_summary', array( $this, 'arm_reset_data_summary' ) );
			add_action( 'wp_ajax_arm_reset_data_process', array( $this, 'arm_reset_data_process' ) );
			add_action( 'wp_ajax_arm_reset_data_complete', array( $this, 'arm_reset_data_complete' ) );
		}

		function arm_reset_data_get_modules() {
			global $ARMemberLite;

			if ( null !== $this->arm_rd_modules ) {
				return $this->arm_rd_modules;
			}

			$arm_rd_modules = array(
				'members'      => array(
					'label'       => __( 'Members & membership plans', 'armember-membership' ),
					'description' => __( 'Members, their plans, subscriptions and profile data.', 'armember-membership' ),
					'tables'      => array(),
					'notes'       => array(),
					'capability'  => 'arm_manage_members',
				),
				'transactions' => array(
					'label'       => __( 'Transactions & payment history', 'armember-membership' ),
					'description' => __( 'Payment log, bank transfer requests and invoices.', 'armember-membership' ),
					'tables'      => array( $ARMemberLite->tbl_arm_payment_log ),
					'notes'       => array(),
					'capability'  => 'arm_manage_transactions',
				),
				'activity'     => array(
					'label'       => __( 'Login history', 'armember-membership' ),
					'description' => __( 'Member login and logout history.', 'armember-membership' ),
					'tables'      => array( $ARMemberLite->tbl_arm_login_history ),
					'notes'       => array(),
					'capability'  => 'arm_manage_general_settings',
				),
				'logs'         => array(
					'label'       => __( 'Security & debug logs', 'armember-membership' ),
					'description' => __( 'Failed logins, lockouts, payment and general debug logs.', 'armember-membership' ),
					'tables'      => array( $ARMemberLite->tbl_arm_fail_attempts, $ARMemberLite->tbl_arm_lockdown, $ARMemberLite->tbl_arm_debug_payment_log, $ARMemberLite->tbl_arm_debug_general_log ),
					'notes'       => array(),
					'capability'  => 'arm_manage_general_settings',
				),
			);

			if ( $ARMemberLite->is_arm_pro_active ) {
				$arm_rd_addon_modules = apply_filters( 'arm_reset_data_modules', array() );
				if ( ! empty( $arm_rd_addon_modules ) && is_array( $arm_rd_addon_modules ) ) {
					foreach ( $arm_rd_addon_modules as $arm_rd_key => $arm_rd_module ) {
						$arm_rd_key = sanitize_key( $arm_rd_key );
						if ( empty( $arm_rd_key ) || isset( $arm_rd_modules[ $arm_rd_key ] ) || empty( $arm_rd_module['label'] ) ) {
							continue;
						}
						$arm_rd_modules[ $arm_rd_key ] = array(
							'label'       => $arm_rd_module['label'],
							'description' => ! empty( $arm_rd_module['description'] ) ? $arm_rd_module['description'] : '',
							'tables'      => ! empty( $arm_rd_module['tables'] ) ? array_values( array_filter( (array) $arm_rd_module['tables'], array( $this, 'arm_reset_data_is_allowed_table' ) ) ) : array(),
							'notes'       => array(),
							/* An add-on can require its own capability for its row. */
							'capability'  => ! empty( $arm_rd_module['capability'] ) ? sanitize_key( $arm_rd_module['capability'] ) : 'arm_manage_general_settings',
						);
					}
				}

				foreach ( $arm_rd_modules as $arm_rd_key => $arm_rd_module ) {
					$arm_rd_notes = apply_filters( 'arm_reset_data_module_notes', array(), $arm_rd_key );
					$arm_rd_modules[ $arm_rd_key ]['notes'] = is_array( $arm_rd_notes ) ? array_values( array_filter( $arm_rd_notes ) ) : array();

					/* Extra tables that are fully cleared with this group. */
					$arm_rd_tables = apply_filters( 'arm_reset_data_module_tables', $arm_rd_module['tables'], $arm_rd_key );
					$arm_rd_modules[ $arm_rd_key ]['tables'] = is_array( $arm_rd_tables ) ? array_values( array_unique( array_filter( $arm_rd_tables, array( $this, 'arm_reset_data_is_allowed_table' ) ) ) ) : $arm_rd_module['tables'];
				}
			}

			$this->arm_rd_modules = $arm_rd_modules;
			return $this->arm_rd_modules;
		}

		function arm_reset_data_get_process_order() {
			$arm_rd_process_order = array( 'members', 'transactions' );
			foreach ( array_keys( $this->arm_reset_data_get_modules() ) as $arm_rd_key ) {
				if ( ! in_array( $arm_rd_key, $this->arm_rd_process_order, true ) ) {
					$arm_rd_process_order[] = $arm_rd_key;
				}
			}
			$arm_rd_process_order[] = 'activity';
			$arm_rd_process_order[] = 'logs';
			return $arm_rd_process_order;
		}

		function arm_reset_data_count_module( $arm_rd_key ) {
			global $ARMemberLite;

			if ( 'members' === $arm_rd_key ) {
				return $this->arm_reset_data_count_members();
			}

			$arm_rd_count = null;
			if ( $ARMemberLite->is_arm_pro_active ) {
				$arm_rd_count = apply_filters( 'arm_reset_data_module_count', null, $arm_rd_key );
			}
			if ( ! is_numeric( $arm_rd_count ) ) {
				$arm_rd_modules = $this->arm_reset_data_get_modules();
				$arm_rd_count   = $this->arm_reset_data_count_tables( $arm_rd_modules[ $arm_rd_key ]['tables'] );
			}
			return intval( $arm_rd_count );
		}

		function arm_reset_data_summary() {
			$arm_rd_request = $this->arm_reset_data_verify_request();
			$arm_rd_modules = $this->arm_reset_data_get_modules();

			$arm_rd_rows  = array();
			$arm_rd_total = 0;
			foreach ( $this->arm_reset_data_get_process_order() as $arm_rd_key ) {
				if ( ! in_array( $arm_rd_key, $arm_rd_request['modules'], true ) ) {
					continue;
				}
				$arm_rd_count  = $this->arm_reset_data_count_module( $arm_rd_key );
				$arm_rd_total += $arm_rd_count;

				$arm_rd_sub_strong = '';
				if ( 'members' === $arm_rd_key ) {
					$arm_rd_sub_text = ( 'delete' === $arm_rd_request['member_action'] ) ? __( 'Only ARMember members are deleted.', 'armember-membership' ) : __( 'Only ARMember plans are removed.', 'armember-membership' );

					$arm_rd_other_count = $this->arm_reset_data_count_other_users( $arm_rd_count );
					if ( $arm_rd_other_count > 0 ) {
						$arm_rd_sub_strong = sprintf( _n( '%s other member', '%s other members', $arm_rd_other_count, 'armember-membership' ), number_format_i18n( $arm_rd_other_count ) );
						if ( 'delete' === $arm_rd_request['member_action'] ) {
							$arm_rd_sub_text .= ' ' . sprintf( __( '%s and administrators are kept.', 'armember-membership' ), $arm_rd_sub_strong );
						} else {
							$arm_rd_sub_text .= ' ' . sprintf( __( '%s are not changed.', 'armember-membership' ), $arm_rd_sub_strong );
						}
					}
					$arm_rd_count_text = sprintf( _n( '%s member', '%s members', $arm_rd_count, 'armember-membership' ), number_format_i18n( $arm_rd_count ) );
				} else {
					$arm_rd_sub_text = implode( ' ', $arm_rd_modules[ $arm_rd_key ]['notes'] );
					$arm_rd_count_text = sprintf( _n( '%s record', '%s records', $arm_rd_count, 'armember-membership' ), number_format_i18n( $arm_rd_count ) );
				}

				$arm_rd_rows[] = array(
					'key'        => $arm_rd_key,
					'label'      => $arm_rd_modules[ $arm_rd_key ]['label'],
					'sub_text'   => $arm_rd_sub_text,
					'sub_strong' => $arm_rd_sub_strong,
					'count'      => $arm_rd_count,
					'count_text' => $arm_rd_count_text,
				);
			}

			$arm_rd_display_rows = array();
			foreach ( array_keys( $arm_rd_modules ) as $arm_rd_key ) {
				foreach ( $arm_rd_rows as $arm_rd_row ) {
					if ( $arm_rd_row['key'] === $arm_rd_key ) {
						$arm_rd_display_rows[] = $arm_rd_row;
					}
				}
			}

			if ( in_array( 'members', $arm_rd_request['modules'], true ) ) {
				$arm_rd_recurring = $this->arm_reset_data_count_recurring_subscriptions();
				if ( $arm_rd_recurring > 0 ) {
					$arm_rd_display_rows[] = array(
						'key'        => 'recurring',
						'label'      => __( 'Active recurring subscriptions', 'armember-membership' ),
						'sub_text'   => __( 'Cancelled at the payment gateway without sending ARMember emails.', 'armember-membership' ),
						'count'      => $arm_rd_recurring,
						/* translators: %s: number of subscriptions */
						'count_text' => sprintf( _n( '%s subscription', '%s subscriptions', $arm_rd_recurring, 'armember-membership' ), number_format_i18n( $arm_rd_recurring ) ),
					);
				}
			}

			if ( in_array( 'transactions', $arm_rd_request['modules'], true ) ) {
				$arm_rd_display_rows[] = array(
					'key'        => 'reset_invoice',
					'label'      => __( 'Reset Invoice ID and start from 1', 'armember-membership' ),
					'sub_text'   => '',
					'count'      => 0,
					'count_text' => ! empty( $arm_rd_request['reset_invoice'] ) ? __( 'Yes', 'armember-membership' ) : __( 'No', 'armember-membership' ),
				);
			}

			$this->arm_reset_data_send_response(
				array(
					'type'          => 'success',
					'rows'          => $arm_rd_display_rows,
					'process_order' => array_values( array_intersect( $this->arm_reset_data_get_process_order(), $arm_rd_request['modules'] ) ),
					'total'         => $arm_rd_total,
				)
			);
		}

		function arm_reset_data_process() {
			global $ARMemberLite;

			$arm_rd_request = $this->arm_reset_data_verify_request();
			$arm_rd_modules = $this->arm_reset_data_get_modules();
			$arm_rd_module  = isset( $_POST['arm_rd_module'] ) ? sanitize_key( wp_unslash( $_POST['arm_rd_module'] ) ) : ''; //phpcs:ignore --Reason:Nonce verified in arm_reset_data_verify_request

			if ( empty( $arm_rd_module ) || ! isset( $arm_rd_modules[ $arm_rd_module ] ) || ! in_array( $arm_rd_module, $arm_rd_request['modules'], true ) ) {
				$this->arm_reset_data_send_error( esc_html__( 'Invalid data type.', 'armember-membership' ) );
			}

			if ( 'members' === $arm_rd_module ) {
				$this->arm_rd_suppress_mail = true;
				add_filter( 'pre_wp_mail', array( $this, 'arm_reset_data_block_wp_mail' ), 999 );

				$arm_rd_last_user_id = isset( $_POST['arm_rd_last_user_id'] ) ? intval( $_POST['arm_rd_last_user_id'] ) : 0; //phpcs:ignore --Reason:Nonce verified in arm_reset_data_verify_request
				$arm_rd_result       = $this->arm_reset_data_process_members( $arm_rd_request['member_action'], $arm_rd_last_user_id );
			} else {
				$arm_rd_result = null;
				if ( $ARMemberLite->is_arm_pro_active ) {
					$arm_rd_result = apply_filters( 'arm_reset_data_process_module', null, $arm_rd_module, $arm_rd_request );
				}
				if ( ! is_array( $arm_rd_result ) ) {
					$arm_rd_result = $this->arm_reset_data_process_tables( $arm_rd_modules[ $arm_rd_module ]['tables'] );
				}
			}

			if ( ! empty( $arm_rd_result['module_done'] ) ) {
				if ( 'transactions' === $arm_rd_module && ! empty( $arm_rd_request['reset_invoice'] ) ) {
					update_option( 'arm_last_invoice_id', 0 );
				}
				if ( $ARMemberLite->is_arm_pro_active ) {
					do_action( 'arm_reset_data_after_module', $arm_rd_module, $arm_rd_request );
				}
			}

			$arm_rd_result['type']   = 'success';
			$arm_rd_result['module'] = $arm_rd_module;
			$this->arm_reset_data_send_response( $arm_rd_result );
		}

		function arm_reset_data_complete() {
			global $ARMemberLite;

			$arm_rd_request = $this->arm_reset_data_verify_request();

			update_option(
				self::arm_rd_last_reset_option,
				array(
					'time'    => current_time( 'timestamp' ),
					'user_id' => get_current_user_id(),
				)
			);

			if ( $ARMemberLite->is_arm_pro_active ) {
				do_action( 'arm_reset_data_completed', $arm_rd_request );
			}

			$this->arm_reset_data_send_response( array( 'type' => 'success' ) );
		}

		function arm_reset_data_process_tables( $arm_rd_tables ) {
			global $wpdb;

			$arm_rd_removed = 0;
			$arm_rd_loops   = 0;
			$arm_rd_tables  = array_values( array_filter( (array) $arm_rd_tables, array( $this, 'arm_reset_data_is_allowed_table' ) ) );
			foreach ( $arm_rd_tables as $arm_rd_table ) {
				if ( ! $this->arm_reset_data_table_exists( $arm_rd_table ) ) {
					continue;
				}

				$arm_rd_table_count = intval( $wpdb->get_var( "SELECT COUNT(*) FROM `{$arm_rd_table}`" ) ); //phpcs:ignore --Reason $arm_rd_table is a table name
				if ( $this->arm_reset_data_truncate_table( $arm_rd_table ) ) {
					$arm_rd_removed += $arm_rd_table_count;
					continue;
				}

				while ( $arm_rd_loops < self::arm_rd_table_loops ) {
					$arm_rd_deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM `{$arm_rd_table}` LIMIT %d", self::arm_rd_table_batch ) ); //phpcs:ignore --Reason $arm_rd_table is a table name
					$arm_rd_loops++;
					if ( empty( $arm_rd_deleted ) ) {
						break;
					}
					$arm_rd_removed += intval( $arm_rd_deleted );
					if ( $arm_rd_deleted < self::arm_rd_table_batch ) {
						break;
					}
				}
				if ( 0 === intval( $wpdb->get_var( "SELECT COUNT(*) FROM `{$arm_rd_table}`" ) ) ) { //phpcs:ignore --Reason $arm_rd_table is a table name
					$this->arm_reset_data_reset_auto_increment( $arm_rd_table );
				}
				if ( $arm_rd_loops >= self::arm_rd_table_loops ) {
					break;
				}
			}

			$arm_rd_remaining = $this->arm_reset_data_count_tables( $arm_rd_tables );
			return array(
				'removed'     => $arm_rd_removed,
				'remaining'   => $arm_rd_remaining,
				'module_done' => ( $arm_rd_remaining <= 0 ) ? 1 : 0,
			);
		}

		function arm_reset_data_process_members( $arm_rd_member_action, $arm_rd_last_user_id ) {
			global $ARMemberLite;

			$arm_rd_batch    = ( 'delete' === $arm_rd_member_action ) ? self::arm_rd_member_batch_delete : self::arm_rd_member_batch_keep;
			$arm_rd_user_ids = $this->arm_reset_data_get_member_ids( $arm_rd_last_user_id, $arm_rd_batch );

			if ( empty( $arm_rd_user_ids ) ) {
				$arm_rd_entries = $this->arm_reset_data_process_tables( array( $ARMemberLite->tbl_arm_entries ) );
				if ( ! empty( $arm_rd_entries['module_done'] ) ) {
					$this->arm_reset_data_reset_empty_member_tables();
				}
				return array(
					'removed'      => 0,
					'remaining'    => $arm_rd_entries['remaining'],
					'last_user_id' => $arm_rd_last_user_id,
					'module_done'  => $arm_rd_entries['module_done'],
				);
			}

			$arm_rd_fetched_ids = $arm_rd_user_ids;
			$arm_rd_user_ids    = array_values( array_filter( $arm_rd_user_ids, array( $this, 'arm_reset_data_is_member_user' ) ) );
			if ( empty( $arm_rd_user_ids ) ) {
				$arm_rd_last_user_id = max( $arm_rd_fetched_ids );
				return array(
					'removed'      => 0,
					'remaining'    => $this->arm_reset_data_count_members( $arm_rd_last_user_id ),
					'last_user_id' => $arm_rd_last_user_id,
					'module_done'  => 0,
				);
			}

			if ( $ARMemberLite->is_arm_pro_active ) {
				do_action( 'arm_reset_data_before_members_batch', $arm_rd_user_ids, $arm_rd_member_action );
			}

			if ( 'delete' === $arm_rd_member_action ) {
				$this->arm_reset_data_delete_users( $arm_rd_user_ids );
			} else {
				$this->arm_reset_data_cancel_gateway_subscriptions( $arm_rd_user_ids );
				$this->arm_reset_data_clear_member_plans( $arm_rd_user_ids );
			}
			$this->arm_reset_data_clear_member_tables( $arm_rd_user_ids );

			if ( $ARMemberLite->is_arm_pro_active ) {
				do_action( 'arm_reset_data_after_members_batch', $arm_rd_user_ids, $arm_rd_member_action );
			}

			$arm_rd_last_user_id = max( $arm_rd_fetched_ids );
			return array(
				'removed'      => count( $arm_rd_user_ids ),
				'remaining'    => $this->arm_reset_data_count_members( $arm_rd_last_user_id ),
				'last_user_id' => $arm_rd_last_user_id,
				'module_done'  => 0,
			);
		}

		function arm_reset_data_delete_users( $arm_rd_user_ids ) {
			global $wpdb, $ARMemberLite;

			if ( file_exists( ABSPATH . 'wp-admin/includes/user.php' ) ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
			}

			$arm_rd_reassign = get_current_user_id();

			if ( is_multisite() ) {
				$arm_rd_blog_id = get_current_blog_id();
				foreach ( $arm_rd_user_ids as $arm_rd_user_id ) {
					remove_user_from_blog( $arm_rd_user_id, $arm_rd_blog_id, $arm_rd_reassign );
					arm_update_user_meta( $arm_rd_user_id, 'arm_site_' . $arm_rd_blog_id . '_deleted', true );
				}
				$this->arm_reset_data_clear_member_plans( $arm_rd_user_ids );
				$arm_rd_placeholders = implode( ',', array_fill( 0, count( $arm_rd_user_ids ), '%d' ) );
				$wpdb->query( $wpdb->prepare( "DELETE FROM `{$ARMemberLite->tbl_arm_members}` WHERE arm_user_id IN ({$arm_rd_placeholders})", $arm_rd_user_ids ) ); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_members is a table name
				return;
			}
			else {
				$this->arm_reset_data_clear_member_plans( $arm_rd_user_ids );
			}
			foreach ( $arm_rd_user_ids as $arm_rd_user_id ) {
				wp_delete_user( $arm_rd_user_id, $arm_rd_reassign );
			}
		}

		function arm_reset_data_cancel_gateway_subscriptions( $arm_rd_user_ids ) {
			global $ARMemberLite, $arm_subscription_plans;

			$arm_rd_plan_class = ( $ARMemberLite->is_arm_pro_active && class_exists( 'ARM_Plan' ) ) ? 'ARM_Plan' : 'ARM_Plan_Lite';
			if ( ! class_exists( $arm_rd_plan_class ) || ! is_object( $arm_subscription_plans ) ) {
				return;
			}

			foreach ( $arm_rd_user_ids as $arm_rd_user_id ) {
				$arm_rd_plan_ids = arm_get_user_meta( $arm_rd_user_id, 'arm_user_plan_ids', true );
				if ( empty( $arm_rd_plan_ids ) || ! is_array( $arm_rd_plan_ids ) ) {
					continue;
				}
				foreach ( $arm_rd_plan_ids as $arm_rd_plan_id ) {
					if ( empty( $arm_rd_plan_id ) ) {
						continue;
					}
					$arm_rd_plan_meta = arm_get_user_meta( $arm_rd_user_id, 'arm_user_plan_' . $arm_rd_plan_id, true );
					$arm_rd_plan_data = shortcode_atts( $arm_subscription_plans->arm_default_plan_array(), ! empty( $arm_rd_plan_meta ) ? $arm_rd_plan_meta : array() );

					if ( ! empty( $arm_rd_plan_data['arm_current_plan_detail'] ) ) {
						$arm_rd_plan = new $arm_rd_plan_class( 0 );
						$arm_rd_plan->init( (object) $arm_rd_plan_data['arm_current_plan_detail'] );
					} else {
						$arm_rd_plan = new $arm_rd_plan_class( $arm_rd_plan_id );
					}

					if ( $arm_rd_plan->exists() && $arm_rd_plan->is_recurring() ) {
						do_action( 'arm_cancel_subscription_gateway_action', $arm_rd_user_id, $arm_rd_plan_id );
					} elseif ( $ARMemberLite->is_arm_pro_active ) {
						do_action( 'arm_cancel_except_recurring_subscription_action', $arm_rd_user_id, $arm_rd_plan_id );
					}
				}
			}
		}

		function arm_reset_data_block_wp_mail( $arm_rd_return ) {
			return $this->arm_rd_suppress_mail ? false : $arm_rd_return;
		}

		function arm_reset_data_count_recurring_subscriptions() {
			global $wpdb, $ARMemberLite;

			if ( ! get_option( 'arm_subscription_tables_installed' ) ) {
				return 0;
			}
			list( $arm_rd_sql, $arm_rd_args ) = $this->arm_reset_data_member_query_parts( 0 );
			$arm_rd_args = array_merge( array( 1, 2, 'auto_debit_subscription' ), $arm_rd_args );

			return intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$ARMemberLite->tbl_arm_subscriptions}` s WHERE s.arm_plan_status IN (%d,%d) AND s.arm_plan_payment_mode = %s AND s.arm_plan_gateway_subscription_id != '' AND s.arm_user_id IN (SELECT DISTINCT m.arm_user_id {$arm_rd_sql})", $arm_rd_args ) ) ); //phpcs:ignore --Reason query is prepared, table names come from $ARMemberLite
		}

		function arm_reset_data_clear_member_plans( $arm_rd_user_ids ) {
			global $wpdb, $ARMemberLite,$arm_subscription_plans;

			$arm_rd_meta_keys = array(
				'exact' => array( 'arm_user_plan_ids', 'arm_user_future_plan_ids', 'arm_user_suspended_plan_ids', 'arm_user_last_plan', 'arm_changed_expiry_date_plans' ),
				'like'  => array( 'arm_user_plan_', 'arm_completed_recurring_', 'arm_user_failed_payment_plan_status_','arm_subscription_start_date','arm_user_plan'),
			);
			if ( $ARMemberLite->is_arm_pro_active ) {
				$arm_rd_meta_keys = apply_filters( 'arm_reset_data_member_meta_keys', $arm_rd_meta_keys );
			}

			foreach ( $arm_rd_user_ids as $arm_rd_user_id ) {
				wp_cache_delete( $arm_rd_user_id, 'user_meta' );
				$arm_rd_user = get_userdata( $arm_rd_user_id );
				$arm_rd_user_plans = arm_get_user_meta( $arm_rd_user_id, 'arm_user_plan_ids', true );
				$arm_rd_user_plans2 = arm_get_user_meta( $arm_rd_user_id, 'arm_user_plan2', true );
				if(!empty($arm_rd_user_plans2)){
					$arm_rd_user_plans = $arm_rd_user_plans2;
				}
				$user_capabilities = array_keys( $arm_rd_user->caps );
				if ( $arm_rd_user && ! empty( $user_capabilities ) ) {
					foreach ( $user_capabilities as $cap) {
						if ( strpos( $cap, 'armember_access_plan_' ) === 0 ) {
							$arm_rd_user->remove_cap( $cap );
						}
					}
				}

				if(!empty($arm_rd_user_plans))
				{
					$arm_rd_plan_id_role_array = $arm_subscription_plans->arm_get_plan_role_by_id( $arm_rd_user_plans );
					if ( ! empty( $arm_rd_plan_id_role_array ) && is_array( $arm_rd_plan_id_role_array ) ) {
						foreach ( $arm_rd_plan_id_role_array as $key => $value ) {
							$plan_role = $value['arm_subscription_plan_role'];
							$arm_rd_default_role = get_option( 'default_role' );
							if ( ! empty( $plan_role ) && $arm_rd_default_role != $plan_role ) {
								$arm_rd_user->remove_role( $plan_role );
							}
						}
					}
				}
			}

			$arm_rd_exact = ! empty( $arm_rd_meta_keys['exact'] ) ? array_values( array_unique( array_map( 'sanitize_key', (array) $arm_rd_meta_keys['exact'] ) ) ) : array();
			$arm_rd_like  = ! empty( $arm_rd_meta_keys['like'] ) ? array_values( array_unique( array_map( 'sanitize_key', (array) $arm_rd_meta_keys['like'] ) ) ) : array();

			$arm_rd_key_where = array();
			$arm_rd_key_args  = array();
			if ( ! empty( $arm_rd_exact ) ) {
				$arm_rd_key_where[] = 'meta_key IN (' . implode( ',', array_fill( 0, count( $arm_rd_exact ), '%s' ) ) . ')';
				$arm_rd_key_args    = array_merge( $arm_rd_key_args, $arm_rd_exact );
			}
			foreach ( $arm_rd_like as $arm_rd_prefix ) {
				$arm_rd_key_where[] = 'meta_key LIKE %s';
				$arm_rd_key_args[]  = $wpdb->esc_like( $arm_rd_prefix ) . '%';
			}

			$arm_rd_user_placeholders = implode( ',', array_fill( 0, count( $arm_rd_user_ids ), '%d' ) );
			if ( ! empty( $arm_rd_key_where ) ) {
				$arm_rd_query = "DELETE FROM `{$wpdb->usermeta}` WHERE user_id IN ({$arm_rd_user_placeholders}) AND (" . implode( ' OR ', $arm_rd_key_where ) . ')';
				$wpdb->query( $wpdb->prepare( $arm_rd_query, array_merge( $arm_rd_user_ids, $arm_rd_key_args ) ) ); //phpcs:ignore --Reason query is prepared with placeholders built above
			}

			$wpdb->query( $wpdb->prepare( "UPDATE `{$ARMemberLite->tbl_arm_members}` SET arm_user_plan_ids = '', arm_user_suspended_plan_ids = '' WHERE arm_user_id IN ({$arm_rd_user_placeholders})", $arm_rd_user_ids ) ); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_members is a table name

		}

		function arm_reset_data_clear_member_tables( $arm_rd_user_ids ) {
			global $wpdb, $ARMemberLite, $arm_member_subscriptions;

			$arm_rd_user_placeholders = implode( ',', array_fill( 0, count( $arm_rd_user_ids ), '%d' ) );

			$wpdb->query( $wpdb->prepare( "DELETE FROM `{$ARMemberLite->tbl_arm_activity}` WHERE arm_user_id IN ({$arm_rd_user_placeholders})", $arm_rd_user_ids ) ); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_activity is a table name

			if ( ! get_option( 'arm_subscription_tables_installed' ) ) {
				return;
			}

			$arm_rd_subscription_ids  = $wpdb->get_col( $wpdb->prepare( "SELECT arm_subscription_id FROM `{$ARMemberLite->tbl_arm_subscriptions}` WHERE arm_user_id IN ({$arm_rd_user_placeholders})", $arm_rd_user_ids ) ); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_subscriptions is a table name

			if ( ! empty( $arm_rd_subscription_ids ) ) {
				$arm_rd_subscription_ids = array_map( 'intval', $arm_rd_subscription_ids );
				$arm_rd_sub_placeholders = implode( ',', array_fill( 0, count( $arm_rd_subscription_ids ), '%d' ) );
				$wpdb->query( $wpdb->prepare( "DELETE FROM `{$ARMemberLite->tbl_arm_subscriptions_meta}` WHERE arm_subscription_id IN ({$arm_rd_sub_placeholders})", $arm_rd_subscription_ids ) ); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_subscriptions_meta is a table name
				$wpdb->query( $wpdb->prepare( "DELETE FROM `{$ARMemberLite->tbl_arm_subscriptions}` WHERE arm_subscription_id IN ({$arm_rd_sub_placeholders})", $arm_rd_subscription_ids ) ); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_subscriptions is a table name
			}

			$wpdb->query( $wpdb->prepare( "DELETE FROM `{$ARMemberLite->tbl_arm_members_plan_detail}` WHERE arm_member_user_id IN ({$arm_rd_user_placeholders})", $arm_rd_user_ids ) ); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_members_plan_detail is a table name

			if ( is_object( $arm_member_subscriptions ) && method_exists( $arm_member_subscriptions, 'arm_clear_read_cache' ) ) {
				foreach ( $arm_rd_user_ids as $arm_rd_user_id ) {
					$arm_member_subscriptions->arm_clear_read_cache( $arm_rd_user_id );
				}
			}
		}

		function arm_reset_data_reset_empty_member_tables() {
			global $wpdb, $ARMemberLite;

			$arm_rd_tables = array(
				$ARMemberLite->tbl_arm_activity,
				$ARMemberLite->tbl_arm_subscriptions_meta,
				$ARMemberLite->tbl_arm_subscriptions,
				$ARMemberLite->tbl_arm_members_plan_detail,
			);
			foreach ( $arm_rd_tables as $arm_rd_table ) {
				if ( empty( $arm_rd_table ) || ! $this->arm_reset_data_table_exists( $arm_rd_table ) ) {
					continue;
				}
				if ( 0 !== intval( $wpdb->get_var( "SELECT COUNT(*) FROM `{$arm_rd_table}`" ) ) ) { //phpcs:ignore --Reason $arm_rd_table is a table name
					continue;
				}
				if ( ! $this->arm_reset_data_truncate_table( $arm_rd_table ) ) {
					$this->arm_reset_data_reset_auto_increment( $arm_rd_table );
				}
			}
		}

		function arm_reset_data_truncate_table( $arm_rd_table ) {
			global $wpdb;

			$arm_rd_suppress = $wpdb->suppress_errors( true );
			$arm_rd_result   = $wpdb->query( "TRUNCATE TABLE `{$arm_rd_table}`" ); //phpcs:ignore --Reason $arm_rd_table is a table name
			$wpdb->suppress_errors( $arm_rd_suppress );
			return ( false !== $arm_rd_result );
		}

		function arm_reset_data_reset_auto_increment( $arm_rd_table ) {
			global $wpdb;

			$arm_rd_suppress = $wpdb->suppress_errors( true );
			$wpdb->query( "ALTER TABLE `{$arm_rd_table}` AUTO_INCREMENT = 1" ); //phpcs:ignore --Reason $arm_rd_table is a table name
			$wpdb->suppress_errors( $arm_rd_suppress );
		}

		function arm_reset_data_get_protected_user_ids() {
			if ( null !== $this->arm_rd_protected_ids ) {
				return $this->arm_rd_protected_ids;
			}

			$arm_rd_roles = array( 'administrator' );
			foreach ( wp_roles()->roles as $arm_rd_role_key => $arm_rd_role ) {
				$arm_rd_role_caps = ! empty( $arm_rd_role['capabilities'] ) ? array_keys( array_filter( (array) $arm_rd_role['capabilities'] ) ) : array();
				if ( array_intersect( $this->arm_rd_protected_caps, $arm_rd_role_caps ) ) {
					$arm_rd_roles[] = $arm_rd_role_key;
				}
			}

			$arm_rd_protected   = get_users(
				array(
					'role__in' => array_values( array_unique( $arm_rd_roles ) ),
					'fields'   => 'ID',
				)
			);
			$arm_rd_protected[] = get_current_user_id();
			if ( is_multisite() ) {
				foreach ( (array) get_super_admins() as $arm_rd_login ) {
					$arm_rd_admin = get_user_by( 'login', $arm_rd_login );
					if ( ! empty( $arm_rd_admin ) ) {
						$arm_rd_protected[] = $arm_rd_admin->ID;
					}
				}
			}
			$this->arm_rd_protected_ids = array_values( array_unique( array_filter( array_map( 'intval', $arm_rd_protected ) ) ) );
			return $this->arm_rd_protected_ids;
		}

		function arm_reset_data_is_member_user( $arm_rd_user_id ) {
			$arm_rd_user_id = intval( $arm_rd_user_id );
			if ( $arm_rd_user_id <= 0 || in_array( $arm_rd_user_id, $this->arm_reset_data_get_protected_user_ids(), true ) || is_super_admin( $arm_rd_user_id ) ) {
				return false;
			}
			foreach ( $this->arm_rd_protected_caps as $arm_rd_cap ) {
				if ( user_can( $arm_rd_user_id, $arm_rd_cap ) ) {
					return false;
				}
			}
			return true;
		}

		function arm_reset_data_is_allowed_table( $arm_rd_table ) {
			global $wpdb, $ARMemberLite, $ARMember;

			if ( ! is_string( $arm_rd_table ) || '' === $arm_rd_table || ! preg_match( '/^[A-Za-z0-9_]+$/', $arm_rd_table ) || 0 !== strpos( $arm_rd_table, $wpdb->prefix ) ) {
				return false;
			}

			if ( null === $this->arm_rd_blocked_tables ) {
				$arm_rd_allowed = array(
					$ARMemberLite->tbl_arm_payment_log,
					$ARMemberLite->tbl_arm_bank_transfer_log,
					$ARMemberLite->tbl_arm_login_history,
					$ARMemberLite->tbl_arm_fail_attempts,
					$ARMemberLite->tbl_arm_lockdown,
					$ARMemberLite->tbl_arm_debug_payment_log,
					$ARMemberLite->tbl_arm_debug_general_log,
					$ARMemberLite->tbl_arm_entries,
				);
				$arm_rd_blocked = array_values( $wpdb->tables( 'all', true ) );
				foreach ( array( $ARMemberLite, $ARMember ) as $arm_rd_object ) {
					if ( ! is_object( $arm_rd_object ) ) {
						continue;
					}
					foreach ( get_object_vars( $arm_rd_object ) as $arm_rd_prop => $arm_rd_value ) {
						if ( 0 === strpos( $arm_rd_prop, 'tbl_arm_' ) && is_string( $arm_rd_value ) && ! in_array( $arm_rd_value, $arm_rd_allowed, true ) ) {
							$arm_rd_blocked[] = $arm_rd_value;
						}
					}
				}
				$this->arm_rd_blocked_tables = array_values( array_unique( $arm_rd_blocked ) );
			}

			return ! in_array( $arm_rd_table, $this->arm_rd_blocked_tables, true );
		}

		function arm_reset_data_member_query_parts( $arm_rd_after_user_id ) {
			global $wpdb, $ARMemberLite;

			$arm_rd_protected    = $this->arm_reset_data_get_protected_user_ids();
			$arm_rd_placeholders = implode( ',', array_fill( 0, count( $arm_rd_protected ), '%d' ) );

			$arm_rd_plan_where = "m.arm_user_type = 1 OR IFNULL(m.arm_user_plan_ids, '') != '' OR IFNULL(m.arm_user_suspended_plan_ids, '') != '' OR EXISTS (SELECT 1 FROM `{$wpdb->usermeta}` um WHERE um.user_id = m.arm_user_id AND um.meta_key LIKE %s)";
			$arm_rd_args       = array_merge( array( intval( $arm_rd_after_user_id ) ), $arm_rd_protected, array( $wpdb->esc_like( 'arm_user_plan_' ) . '%' ) );
			if ( get_option( 'arm_subscription_tables_installed' ) ) {
				$arm_rd_plan_where .= " OR EXISTS (SELECT 1 FROM `{$ARMemberLite->tbl_arm_subscriptions}` s WHERE s.arm_user_id = m.arm_user_id)";
			}

			$arm_rd_sql = "FROM `{$ARMemberLite->tbl_arm_members}` m INNER JOIN `{$wpdb->users}` u ON u.ID = m.arm_user_id WHERE m.arm_user_id > %d AND m.arm_user_id NOT IN ({$arm_rd_placeholders}) AND ({$arm_rd_plan_where})";
			return array( $arm_rd_sql, $arm_rd_args );
		}

		function arm_reset_data_count_other_users( $arm_rd_member_count ) {
			$arm_rd_user_query = new WP_User_Query(
				array(
					'exclude'     => $this->arm_reset_data_get_protected_user_ids(),
					'fields'      => 'ID',
					'number'      => 1,
					'count_total' => true,
				)
			);
			return max( 0, intval( $arm_rd_user_query->get_total() ) - intval( $arm_rd_member_count ) );
		}

		function arm_reset_data_count_members( $arm_rd_after_user_id = 0 ) {
			global $wpdb;

			list( $arm_rd_sql, $arm_rd_args ) = $this->arm_reset_data_member_query_parts( $arm_rd_after_user_id );
			return intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT m.arm_user_id) {$arm_rd_sql}", $arm_rd_args ) ) ); //phpcs:ignore --Reason query is prepared, table names come from $ARMemberLite
		}

		function arm_reset_data_get_member_ids( $arm_rd_after_user_id, $arm_rd_limit ) {
			global $wpdb;

			list( $arm_rd_sql, $arm_rd_args ) = $this->arm_reset_data_member_query_parts( $arm_rd_after_user_id );
			$arm_rd_args[] = intval( $arm_rd_limit );

			$arm_rd_ids = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT m.arm_user_id {$arm_rd_sql} ORDER BY m.arm_user_id ASC LIMIT %d", $arm_rd_args ) ); //phpcs:ignore --Reason query is prepared, table names come from $ARMemberLite
			return array_map( 'intval', (array) $arm_rd_ids );
		}

		function arm_reset_data_count_tables( $arm_rd_tables ) {
			global $wpdb;

			$arm_rd_count = 0;
			foreach ( (array) $arm_rd_tables as $arm_rd_table ) {
				if ( $this->arm_reset_data_is_allowed_table( $arm_rd_table ) && $this->arm_reset_data_table_exists( $arm_rd_table ) ) {
					$arm_rd_count += intval( $wpdb->get_var( "SELECT COUNT(*) FROM `{$arm_rd_table}`" ) ); //phpcs:ignore --Reason $arm_rd_table is a table name
				}
			}
			return $arm_rd_count;
		}

		function arm_reset_data_table_exists( $arm_rd_table ) {
			global $wpdb;

			if ( ! isset( $this->arm_rd_table_exists[ $arm_rd_table ] ) ) {
				$arm_rd_found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $arm_rd_table ) ) );
				$this->arm_rd_table_exists[ $arm_rd_table ] = ( $arm_rd_found === $arm_rd_table );
			}
			return $this->arm_rd_table_exists[ $arm_rd_table ];
		}

		function arm_reset_data_verify_request() {
			global $ARMemberLite, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce
			if ( ! $this->arm_reset_data_can_access() ) {
				$this->arm_reset_data_send_error( esc_html__( 'Sorry, You do not have permission to perform this action.', 'armember-membership' ) );
			}

			if ( ! check_ajax_referer( 'arm_reset_data', 'arm_rd_nonce', false ) ) {
				$this->arm_reset_data_send_error( esc_html__( 'Sorry, Your request cannot process due to security reason.', 'armember-membership' ) );
			}

			$arm_rd_modules_raw = isset( $_POST['arm_rd_modules'] ) ? (array) wp_unslash( $_POST['arm_rd_modules'] ) : array(); //phpcs:ignore --Reason:Nonce verified above, values sanitized below
			$arm_rd_modules_raw = array_map( 'sanitize_key', $arm_rd_modules_raw );
			$arm_rd_modules     = array_values( array_intersect( array_keys( $this->arm_reset_data_get_modules() ), $arm_rd_modules_raw ) );

			if ( empty( $arm_rd_modules ) ) {
				$this->arm_reset_data_send_error( esc_html__( 'Please select at least one data type to remove.', 'armember-membership' ) );
			}

			foreach ( $arm_rd_modules as $arm_rd_key ) {
				if ( ! $this->arm_reset_data_can_use_module( $arm_rd_key ) ) {
					$this->arm_reset_data_send_error( $this->arm_reset_data_get_module_denied_message( $arm_rd_key ) );
				}
			}

			$arm_rd_member_action = isset( $_POST['arm_rd_member_action'] ) ? sanitize_key( wp_unslash( $_POST['arm_rd_member_action'] ) ) : 'keep'; //phpcs:ignore --Reason:Nonce verified above
			$arm_rd_member_action = ( 'delete' === $arm_rd_member_action ) ? 'delete' : 'keep';

			return array(
				'modules'       => $arm_rd_modules,
				'member_action' => $arm_rd_member_action,
				'reset_invoice' => ( ! empty( $_POST['arm_rd_reset_invoice'] ) && in_array( 'transactions', $arm_rd_modules, true ) ) ? 1 : 0, //phpcs:ignore --Reason:Nonce verified above
			);
		}

		function arm_reset_data_can_access() {
			global $arm_capabilities_global;

			return current_user_can( $arm_capabilities_global['arm_manage_general_settings'] ) && current_user_can( $arm_capabilities_global['arm_manage_members'] );
		}

		function arm_reset_data_can_use_module( $arm_rd_key ) {
			$arm_rd_modules = $this->arm_reset_data_get_modules();
			if ( ! isset( $arm_rd_modules[ $arm_rd_key ] ) ) {
				return false;
			}
			$arm_rd_capability = ! empty( $arm_rd_modules[ $arm_rd_key ]['capability'] ) ? $arm_rd_modules[ $arm_rd_key ]['capability'] : 'arm_manage_general_settings';
			return current_user_can( $arm_rd_capability );
		}

		function arm_reset_data_get_module_denied_message( $arm_rd_key ) {
			if ( 'members' === $arm_rd_key ) {
				return __( 'Your role can\'t manage members.', 'armember-membership' );
			}
			if ( 'transactions' === $arm_rd_key ) {
				return __( 'Your role can\'t manage transactions.', 'armember-membership' );
			}
			$arm_rd_modules = $this->arm_reset_data_get_modules();
			$arm_rd_label   = isset( $arm_rd_modules[ $arm_rd_key ]['label'] ) ? $arm_rd_modules[ $arm_rd_key ]['label'] : $arm_rd_key;
			return sprintf( __( 'Your role can\'t reset %s.', 'armember-membership' ), $arm_rd_label );
		}

		function arm_reset_data_send_error( $arm_rd_message ) {
			$this->arm_reset_data_send_response(
				array(
					'type' => 'error',
					'msg'  => $arm_rd_message,
				)
			);
		}

		function arm_reset_data_send_response( $arm_rd_response ) {
			if ( function_exists( 'arm_pattern_json_encode' ) ) {
				echo arm_pattern_json_encode( $arm_rd_response ); //phpcs:ignore
			} else {
				echo wp_json_encode( $arm_rd_response );
			}
			exit;
		}
	}

	global $arm_reset_data;
	$arm_reset_data = new ARM_Reset_Data_Lite();
}
