<?php 
if ( ! defined( 'ABSPATH' ) ) {	exit; }

if ( ! class_exists( 'ARM_ai_abilities_base_Lite' ) ) {

	class ARM_ai_abilities_base_Lite {

		protected static function arm_default_pagination_input_schema( $item = 'items' ) {
			return array(
				'type'                 => 'object',
				'default'              => array(
					'page'     => 1,
					'per_page' => 10,
				),
				'properties'           => array(
					'page'     => array(
						'type'        => 'integer',
						'minimum'     => 1,
						'default'     => 1,
						'description' => __( 'Page number for pagination', 'armember-membership' ),
					),
					'per_page' => array(
						'type'        => 'integer',
						'minimum'     => 1,
						'maximum'     => 100,
						'default'     => 10,
						'description' => sprintf( __( 'Number of %s per page (max 100)', 'armember-membership' ), $item ),
					),
				),
				'additionalProperties' => false,
			);
		}

		protected static function arm_build_pagination_data( $total_items, $page, $per_page ) {
			$page       = max( 1, absint( $page ) );
			$per_page   = max( 1, absint( $per_page ) );
			$total_items = max( 0, absint( $total_items ) );

			return array(
				'total'        => $total_items,
				'per_page'     => $per_page,
				'current_page' => $page,
				'total_pages'  => ( $total_items > 0 ) ? (int) ceil( $total_items / $per_page ) : 0,
				'has_more'     => ( ( $page * $per_page ) < $total_items ),
			);
		}

		protected static function arm_pagination_output_schema_properties() {
			return array(
				'total'        => array(
					'type'        => 'integer',
					'description' => __( 'Total number of items available.', 'armember-membership' ),
				),
				'per_page'     => array(
					'type'        => 'integer',
					'description' => __( 'Number of items returned per page.', 'armember-membership' ),
				),
				'current_page' => array(
					'type'        => 'integer',
					'description' => __( 'Current page number.', 'armember-membership' ),
				),
				'total_pages'  => array(
					'type'        => 'integer',
					'description' => __( 'Total number of pages.', 'armember-membership' ),
				),
				'has_more'     => array(
					'type'        => 'boolean',
					'description' => __( 'True if there are additional pages available.', 'armember-membership' ),
				),
			);
		}

		protected static function arm_format_datetime( $datetime = '' ) {
			global $arm_global_settings;
			if ( empty( $datetime ) || '0000-00-00 00:00:00' === $datetime ) {
				return '';
			}
			$date_format = $arm_global_settings->arm_get_wp_date_format();
			$timestamp = is_numeric( $datetime ) ? (int) $datetime : strtotime( $datetime );
			if ( ! $timestamp ) {
				return (string) $datetime;
			}
			return date_i18n( $date_format, $timestamp );
		}

		protected static function arm_get_member_status_label( $status ) {
			global $armPrimaryStatus;
			return ( is_array( $armPrimaryStatus ) && isset( $armPrimaryStatus[ $status ] ) ) ? (string) $armPrimaryStatus[ $status ] : '';
		}
		/**
		 * Plan names by ID, unslashed. ARMember stores plan names from slashed $_POST,
		 * and arm_get_plan_name_by_id_from_array() returns them raw from the DB.
		 */
		protected static function arm_get_plan_names() {
			global $arm_subscription_plans;
			if ( ! is_object( $arm_subscription_plans ) || ! method_exists( $arm_subscription_plans, 'arm_get_plan_name_by_id_from_array' ) ) {
				return array();
			}
			$arm_plan_names = $arm_subscription_plans->arm_get_plan_name_by_id_from_array();
			return is_array( $arm_plan_names ) ? wp_unslash( $arm_plan_names ) : array();
		}
		
		/**
		 * Payment log stores both legacy numeric (0/1/2) and text statuses.
		 * Map the numeric ones to text before getting the label, and return plain text (no HTML).
		 */
		protected static function arm_normalize_transaction_status( $status ) {
			$arm_status_map = array(
				'0'         => 'pending',
				'1'         => 'success',
				'2'         => 'canceled',
				'cancelled' => 'canceled',
			);
			$status = (string) $status;
			return isset( $arm_status_map[ $status ] ) ? $arm_status_map[ $status ] : $status;
		}

		protected static function arm_get_transaction_status_label( $status ) {
			global $arm_transaction;
			$status = self::arm_normalize_transaction_status( $status );
			if ( ! is_object( $arm_transaction ) || ! method_exists( $arm_transaction, 'arm_get_transaction_status_text' ) ) {
				return ucfirst( $status );
			}
			$arm_transaction_status = $arm_transaction->arm_get_transaction_status_text( $status );
			return trim( wp_strip_all_tags( $arm_transaction_status ) );
		}

		/**
		 * All arm_transaction_status DB values that mean the given status.
		 */
		protected static function arm_get_transaction_status_db_values( $status ) {
			$arm_status_values = array(
				'success'  => array( 'success', '1' ),
				'pending'  => array( 'pending', '0' ),
				'canceled' => array( 'canceled', 'cancelled', '2' ),
				'failed'   => array( 'failed' ),
				'expired'  => array( 'expired' ),
			);
			return isset( $arm_status_values[ $status ] ) ? $arm_status_values[ $status ] : array();
		}

		/**
		 * Prepared " AND arm_transaction_status IN (...)" clause, or '' for unknown/all.
		 */
		protected static function arm_get_transaction_status_where( $status ) {
			global $wpdb;
			$arm_values = self::arm_get_transaction_status_db_values( $status );
			if ( empty( $arm_values ) ) {
				return '';
			}
			$arm_placeholders = implode( ',', array_fill( 0, count( $arm_values ), '%s' ) );
			return $wpdb->prepare( " AND arm_transaction_status IN ({$arm_placeholders})", $arm_values ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		/**
		 * A non-administrator manager must not read or change an administrator account,
		 * and nobody changes their own membership status through an ability.
		 *
		 * @return true|WP_Error
		 */
		protected static function arm_check_target_member( $user_id, $is_status_change = false ) {
			$user_id = absint( $user_id );
			if ( $is_status_change && get_current_user_id() === $user_id ) {
				return new WP_Error( 'arm_forbidden_target', esc_html__( 'You cannot change your own membership status.', 'armember-membership' ), array( 'status' => 403 ) );
			}
			if ( user_can( $user_id, 'administrator' ) ) {
				if ( $is_status_change || ! self::arm_verify_admin_permission() ) {
					return new WP_Error( 'arm_forbidden_target', esc_html__( 'This action is not allowed for administrator accounts.', 'armember-membership' ), array( 'status' => 403 ) );
				}
			}
			return true;
		}

		public static function arm_verify_read_permission() {
			if ( ! is_user_logged_in() ) {
				return false;
			}
			if ( current_user_can( 'administrator' ) ) {
				return true;
			}
			global $arm_capabilities_global;
			$allowed_caps = array(
				$arm_capabilities_global['arm_manage_members'] ?? 'arm_manage_members',
				$arm_capabilities_global['arm_manage_plans'] ?? 'arm_manage_plans',
				$arm_capabilities_global['arm_manage_transactions'] ?? 'arm_manage_transactions',
				$arm_capabilities_global['arm_manage_subscriptions'] ?? 'arm_manage_subscriptions',
			);
			foreach ( $allowed_caps as $cap ) {
				if ( current_user_can( $cap ) ) {
					return true;
				}
			}
			return false;
		}

		public static function arm_verify_manage_members_permission() {
			if ( ! is_user_logged_in() ) {
				return false;
			}
			if ( current_user_can( 'administrator' ) ) {
				return true;
			}
			global $arm_capabilities_global;
			$cap = $arm_capabilities_global['arm_manage_members'] ?? 'arm_manage_members';
			return current_user_can( $cap );
		}

		public static function arm_verify_manage_transactions_permission() {
			if ( ! is_user_logged_in() ) {
				return false;
			}
			if ( current_user_can( 'administrator' ) ) {
				return true;
			}
			global $arm_capabilities_global;
			$cap = $arm_capabilities_global['arm_manage_transactions'] ?? 'arm_manage_transactions';
			return current_user_can( $cap );
		}

		/**
		 * Same access as the ARMember admin pages: administrator, or any of the given
		 * ARMember page capabilities (keys of $arm_capabilities_global).
		 */
		protected static function arm_verify_page_capability( $arm_cap_keys = array() ) {
			if ( ! is_user_logged_in() ) {
				return false;
			}
			if ( current_user_can( 'administrator' ) ) {
				return true;
			}
			global $arm_capabilities_global;
			foreach ( (array) $arm_cap_keys as $arm_cap_key ) {
				$cap = $arm_capabilities_global[ $arm_cap_key ] ?? $arm_cap_key;
				if ( current_user_can( $cap ) ) {
					return true;
				}
			}
			return false;
		}

		public static function arm_verify_manage_plans_permission() {
			return self::arm_verify_page_capability( array( 'arm_manage_plans' ) );
		}

		public static function arm_verify_member_stats_permission() {
			return self::arm_verify_page_capability( array( 'arm_manage_members', 'arm_manage_transactions' ) );
		}

		public static function arm_verify_manage_coupons_permission() {
			return self::arm_verify_page_capability( array( 'arm_manage_coupons' ) );
		}

		public static function arm_verify_paid_post_transactions_permission() {
			return self::arm_verify_page_capability( array( 'arm_manage_transactions', 'arm_manage_pay_per_post' ) );
		}

		public static function arm_verify_admin_permission() {
			if ( ! is_user_logged_in() ) {
				return false;
			}
			return current_user_can( 'administrator' );
		}

		public static function arm_verify_logged_in_permission() {
			return is_user_logged_in();
		}

	}
}
