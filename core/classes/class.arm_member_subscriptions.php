<?php
if(! defined('ABSPATH')) exit;

if (!class_exists( 'ARM_member_subscriptions_Lite')) 
{
	class ARM_member_subscriptions_Lite 
	{
		const ARM_STATUS_PENDING        = 0;
		const ARM_STATUS_ACTIVE         = 1;
		const ARM_STATUS_SUSPENDED      = 2;
		const ARM_STATUS_FUTURE         = 3;
		const ARM_STATUS_EXPIRED        = 4;
		const ARM_STATUS_CANCELLED      = 5;
		const ARM_STATUS_CHANGED        = 6;
		const ARM_STATUS_REMOVED        = 7;
		const ARM_STATUS_FAILED_PAYMENT = 8;

		const ARM_TYPE_PLAN      = 0;
		const ARM_TYPE_PAID_POST = 1;
		const ARM_TYPE_GIFT      = 2;

		private $arm_closed_in_request   = array();
		private $arm_inserted_in_request = array();
		private $arm_is_syncing          = false;
		private $arm_deleted_meta_key    = '';
		
		private $arm_request_entries     = array();

		private $arm_migration_filled_keys = array();

		private $arm_context             = array();
		private $arm_tables_ready        = null;

		private $arm_read_cache          = array();
		private $arm_read_skip_users     = null;

		function __construct() 
		{
			add_action( 'arm_after_add_transaction', array( $this, 'arm_link_transaction_to_subscription' ), 10, 1 );
			add_action( 'deleted_user', array( $this, 'arm_subscription_after_deleted_user' ), 10, 1 );

			add_action( 'added_user_meta', array( $this, 'arm_sync_on_user_meta_change' ), 10, 3 );
			add_action( 'updated_user_meta', array( $this, 'arm_sync_on_user_meta_change' ), 10, 3 );
			add_action( 'deleted_user_meta', array( $this, 'arm_sync_on_user_meta_change' ), 10, 3 );

			add_action( 'arm_user_plan_status_action_failed_payment', array( $this, 'arm_set_failed_payment_close_status' ), 10, 2 );
			add_action( 'arm_user_plan_status_action_eot', array( $this, 'arm_set_eot_close_status' ), 10, 2 );

			add_action( 'arm_after_decline_bank_transfer_payment', array( $this, 'arm_decline_pending_subscription' ), 10, 2 );

			add_filter( 'arm_change_membership_activity_before_save', array( $this, 'arm_add_subscription_to_activity' ), 20, 1 );

			add_action( 'arm_after_get_entry_data_by_id', array( $this, 'arm_remember_request_entry' ), 10, 1 );
		}

		function arm_set_subscription_context( $user_id, $plan_id, $arm_context = array() ) 
		{
			$user_id = (int) $user_id;
			$plan_id = (int) $plan_id;
			if ( $user_id <= 0 || $plan_id <= 0 || ! is_array( $arm_context ) ) 
			{
				return;
			}
			$arm_old = isset( $this->arm_context[ $user_id ][ $plan_id ] ) ? $this->arm_context[ $user_id ][ $plan_id ] : array();
			$this->arm_context[ $user_id ][ $plan_id ] = array_merge( $arm_old, $arm_context );

			$this->arm_apply_context_to_request_rows( $user_id, $plan_id );
		}

		function arm_apply_context_to_request_rows( $user_id, $plan_id ) 
		{
			global $wpdb, $ARMemberLite;
			if ( empty( $this->arm_inserted_in_request[ $user_id ] ) || ! $this->arm_is_tables_ready() ) 
			{
				return;
			}
			$arm_update = array();
			$arm_source = $this->arm_get_context( $user_id, $plan_id, 'source', null );
			if ( null !== $arm_source ) 
			{
				$arm_update['arm_subscription_source'] = sanitize_key( $arm_source );
			}
			foreach ( array( 'action_by' => 'arm_action_by', 'gifted_by' => 'arm_gifted_by' ) as $arm_key => $arm_column ) 
			{
				$arm_value = $this->arm_get_context( $user_id, $plan_id, $arm_key, null );
				if ( null !== $arm_value ) 
				{
					$arm_update[ $arm_column ] = (int) $arm_value;
				}
			}
			if ( empty( $arm_update ) ) 
			{
				return;
			}
			foreach ( $this->arm_inserted_in_request[ $user_id ] as $arm_subscription_id => $arm_row ) 
			{
				if ( (int) $arm_row['plan_id'] === (int) $plan_id ) 
				{
					$wpdb->update( $ARMemberLite->tbl_arm_subscriptions, $arm_update, array( 'arm_subscription_id' => (int) $arm_subscription_id ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
				}
			}
		}

		function arm_get_context( $user_id, $plan_id, $arm_key, $arm_default = null ) 
		{
			return isset( $this->arm_context[ (int) $user_id ][ (int) $plan_id ][ $arm_key ] ) ? $this->arm_context[ (int) $user_id ][ (int) $plan_id ][ $arm_key ] : $arm_default;
		}

		function arm_set_failed_payment_close_status( $args = array(), $arm_plan = null ) 
		{
			if ( ! empty( $args['user_id'] ) && ! empty( $args['plan_id'] ) ) 
			{
				$this->arm_set_subscription_context( $args['user_id'], $args['plan_id'], array( 'close_status' => self::ARM_STATUS_FAILED_PAYMENT ) );
			}
		}

		function arm_set_eot_close_status( $args = array(), $arm_plan = null ) 
		{
			if ( ! empty( $args['user_id'] ) && ! empty( $args['plan_id'] ) ) 
			{
				$this->arm_set_subscription_context( $args['user_id'], $args['plan_id'], array( 'close_status' => self::ARM_STATUS_EXPIRED ) );
			}
		}

		function arm_create_subscription_tables() 
		{
			global $wpdb, $ARMemberLite;

			$charset_collate = '';
			if ( $wpdb->has_cap( 'collation' ) ) 
			{
				if ( ! empty( $wpdb->charset ) ) 
				{
					$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
				}
				if ( ! empty( $wpdb->collate ) ) 
				{
					$charset_collate .= " COLLATE $wpdb->collate";
				}
			}

			$tbl_plan_detail = $ARMemberLite->tbl_arm_members_plan_detail;
			$tbl_subs        = $ARMemberLite->tbl_arm_subscriptions;
			$tbl_subs_meta   = $ARMemberLite->tbl_arm_subscriptions_meta;

			$wpdb->query( //phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"CREATE TABLE IF NOT EXISTS `{$tbl_plan_detail}` (
					`arm_member_plan_det_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					`arm_member_plan_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
					`arm_member_user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
					`arm_subscription_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
					`arm_member_plan_status` TINYINT(1) NOT NULL DEFAULT '0',
					`arm_member_plan_start_date` DATETIME NULL DEFAULT NULL,
					`arm_member_plan_end_date` DATETIME NULL DEFAULT NULL,
					`arm_member_plan_next_due_date` DATETIME NULL DEFAULT NULL,
					`arm_member_plan_added_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
					`arm_member_plan_updated_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
					PRIMARY KEY (`arm_member_plan_det_id`)
				) {$charset_collate};"
			); //phpcs:ignore --Reason $tbl_plan_detail is a table name

			$wpdb->query( //phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"CREATE TABLE IF NOT EXISTS `{$tbl_subs}` (
					`arm_subscription_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					`arm_user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
					`arm_plan_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
					`arm_previous_subscription_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
					`arm_subscription_type` TINYINT(1) NOT NULL DEFAULT '0',
					`arm_subscription_source` VARCHAR(50) NOT NULL DEFAULT '',
					`arm_action_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
					`arm_gifted_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
					`arm_plan_type` VARCHAR(50) NOT NULL DEFAULT '',
					`arm_plan_status` TINYINT(1) NOT NULL DEFAULT '0',
					`arm_plan_payment_cycle` INT(11) NOT NULL DEFAULT '0',
					`arm_plan_billing_cycle` INT(11) NOT NULL DEFAULT '0',
					`arm_plan_billing_type` VARCHAR(10) NOT NULL DEFAULT '',
					`arm_plan_amount` DOUBLE NOT NULL DEFAULT '0',
					`arm_plan_currency` VARCHAR(50) NOT NULL DEFAULT '',
					`arm_coupon_code` VARCHAR(255) NOT NULL DEFAULT '',
					`arm_coupon_on_each_subscriptions` TINYINT(1) NOT NULL DEFAULT '0',
					`arm_plan_start_date` DATETIME NULL DEFAULT NULL,
					`arm_plan_original_start_date` DATETIME NULL DEFAULT NULL,
					`arm_plan_end_date` DATETIME NULL DEFAULT NULL,
					`arm_plan_cycle_next_date` DATETIME NULL DEFAULT NULL,
					`arm_plan_total_cycle` INT(11) NOT NULL DEFAULT '0',
					`arm_plan_completed_cycle` INT(11) NOT NULL DEFAULT '0',
					`arm_plan_trial_plan` TINYINT(1) NOT NULL DEFAULT '0',
					`arm_plan_trial_start_date` DATETIME NULL DEFAULT NULL,
					`arm_plan_trial_end_date` DATETIME NULL DEFAULT NULL,
					`arm_plan_payment_mode` VARCHAR(50) NOT NULL DEFAULT '',
					`arm_plan_payment_gateway` VARCHAR(50) NOT NULL DEFAULT '',
					`arm_plan_gateway_subscription_id` VARCHAR(255) NOT NULL DEFAULT '',
					`arm_plan_gateway_customer_id` VARCHAR(255) NOT NULL DEFAULT '',
					`arm_plan_gateway_data` TEXT NULL,
					`arm_plan_grace_period` TINYINT(1) NOT NULL DEFAULT '0',
					`arm_plan_grace_period_type` VARCHAR(50) NOT NULL DEFAULT '',
					`arm_plan_grace_period_end_date` DATETIME NULL DEFAULT NULL,
					`arm_plan_grace_period_end_plan` INT(11) NOT NULL DEFAULT '0',
					`arm_subscription_effective_date` DATETIME NULL DEFAULT NULL,
					`arm_subscription_eot_plan` INT(11) NOT NULL DEFAULT '0',
					`arm_subscription_cancel_plan_next_cycle` TINYINT(1) NOT NULL DEFAULT '0',
					`arm_cancelled_date` DATETIME NULL DEFAULT NULL,
					`arm_ended_date` DATETIME NULL DEFAULT NULL,
					`arm_cancel_reason_id` VARCHAR(64) NOT NULL DEFAULT '',
					`arm_cancel_reason` VARCHAR(255) NOT NULL DEFAULT '',
					`arm_created_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
					`arm_updated_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
					PRIMARY KEY (`arm_subscription_id`)
				) {$charset_collate};"
			); //phpcs:ignore --Reason $tbl_subs is a table name

			$wpdb->query( //phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"CREATE TABLE IF NOT EXISTS `{$tbl_subs_meta}` (
					`arm_subscription_meta_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					`arm_subscription_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
					`arm_subscription_meta_key` VARCHAR(255) NOT NULL DEFAULT '',
					`arm_subscription_meta_value` LONGTEXT NULL,
					`arm_subscription_meta_added_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
					`arm_subscription_meta_updated_date` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
					PRIMARY KEY (`arm_subscription_meta_id`)
				) {$charset_collate};"
			); //phpcs:ignore --Reason $tbl_subs_meta is a table name

			$this->arm_set_plan_detail_unique_key_option( $tbl_plan_detail );

			/* Link columns in existing tables. */
			$this->arm_add_column_if_not_exists( $ARMemberLite->tbl_arm_payment_log, 'arm_subscription_id', "INT(11) UNSIGNED NOT NULL DEFAULT '0'", 'arm_plan_id' );
			$this->arm_add_column_if_not_exists( $ARMemberLite->tbl_arm_activity, 'arm_subscription_id', "INT(11) UNSIGNED NOT NULL DEFAULT '0'", 'arm_item_id' );
			$this->arm_add_column_if_not_exists( $ARMemberLite->tbl_arm_activity, 'arm_activity_plan_currency', "VARCHAR(50) NOT NULL DEFAULT ''", 'arm_activity_plan_amount' );
			$this->arm_add_column_if_not_exists( $ARMemberLite->tbl_arm_entries, 'arm_subscription_id', "INT(11) UNSIGNED NOT NULL DEFAULT '0'", 'arm_plan_id' );

			$arm_is_ready = $this->arm_table_exists( $tbl_plan_detail ) && $this->arm_table_exists( $tbl_subs ) && $this->arm_table_exists( $tbl_subs_meta )
				&& $this->arm_column_exists( $ARMemberLite->tbl_arm_payment_log, 'arm_subscription_id' )
				&& $this->arm_column_exists( $ARMemberLite->tbl_arm_activity, 'arm_subscription_id' )
				&& $this->arm_column_exists( $ARMemberLite->tbl_arm_activity, 'arm_activity_plan_currency' )
				&& $this->arm_column_exists( $ARMemberLite->tbl_arm_entries, 'arm_subscription_id' );

			if ( $arm_is_ready ) 
			{
				if ( ! get_option( 'arm_subscription_tables_installed' ) ) 
				{
					update_option( 'arm_subscription_tables_installed', current_time( 'mysql' ) );
				}
				if ( false === get_option( 'arm_subscription_tables_activity_from_id', false ) ) 
				{
					update_option( 'arm_subscription_tables_activity_from_id', (int) $wpdb->get_var( "SELECT MAX(arm_activity_id) FROM `{$ARMemberLite->tbl_arm_activity}`" ) ); //phpcs:ignore --Reason table name
				}
				$this->arm_tables_ready = null;
			}

			return $arm_is_ready;
		}

		function arm_add_subscription_tables_index() 
		{
			global $wpdb, $ARMemberLite;

			$tbl_arm_members_plan_detail = $ARMemberLite->tbl_arm_members_plan_detail;
			$tbl_arm_subscriptions       = $ARMemberLite->tbl_arm_subscriptions;
			$tbl_arm_subscriptions_meta  = $ARMemberLite->tbl_arm_subscriptions_meta;
			$tbl_arm_payment_log         = $ARMemberLite->tbl_arm_payment_log;
			$tbl_arm_activity            = $ARMemberLite->tbl_arm_activity;
			$tbl_arm_entries             = $ARMemberLite->tbl_arm_entries;

			$this->arm_add_plan_detail_unique_key( $tbl_arm_members_plan_detail );

			//Add the arm-user-plan-id INDEX for the Subscriptions table
			$arm_subscriptions_add_index_arm_user_plan_id = $wpdb->get_results( $wpdb->prepare( "SHOW INDEX FROM `{$tbl_arm_subscriptions}` WHERE Key_name=%s", 'arm-user-plan-id' ) ); //phpcs:ignore --Reason: $tbl_arm_subscriptions is a table name
			if ( empty( $arm_subscriptions_add_index_arm_user_plan_id ) ) 
			{
				$wpdb->query( "ALTER TABLE `{$tbl_arm_subscriptions}` ADD INDEX `arm-user-plan-id` (`arm_user_id`,`arm_plan_id`)" ); //phpcs:ignore --Reason: $tbl_arm_subscriptions is a table name
			}

			//Add the arm-plan-id INDEX for the Subscriptions table
			$arm_subscriptions_add_index_arm_plan_id = $wpdb->get_results( $wpdb->prepare( "SHOW INDEX FROM `{$tbl_arm_subscriptions}` WHERE Key_name=%s", 'arm-plan-id' ) ); //phpcs:ignore --Reason: $tbl_arm_subscriptions is a table name
			if ( empty( $arm_subscriptions_add_index_arm_plan_id ) ) 
			{
				$wpdb->query( "ALTER TABLE `{$tbl_arm_subscriptions}` ADD INDEX `arm-plan-id` (`arm_plan_id`)" ); //phpcs:ignore --Reason: $tbl_arm_subscriptions is a table name
			}

			//Add the arm-plan-status INDEX for the Subscriptions table
			$arm_subscriptions_add_index_arm_plan_status = $wpdb->get_results( $wpdb->prepare( "SHOW INDEX FROM `{$tbl_arm_subscriptions}` WHERE Key_name=%s", 'arm-plan-status' ) ); //phpcs:ignore --Reason: $tbl_arm_subscriptions is a table name
			if ( empty( $arm_subscriptions_add_index_arm_plan_status ) ) 
			{
				$wpdb->query( "ALTER TABLE `{$tbl_arm_subscriptions}` ADD INDEX `arm-plan-status` (`arm_plan_status`)" ); //phpcs:ignore --Reason: $tbl_arm_subscriptions is a table name
			}

			//Add the arm-subscription-id INDEX for the Subscriptions Meta table
			$arm_subscriptions_meta_add_index_arm_subscription_id = $wpdb->get_results( $wpdb->prepare( "SHOW INDEX FROM `{$tbl_arm_subscriptions_meta}` WHERE Key_name=%s", 'arm-subscription-id' ) ); //phpcs:ignore --Reason: $tbl_arm_subscriptions_meta is a table name
			if ( empty( $arm_subscriptions_meta_add_index_arm_subscription_id ) ) 
			{
				$wpdb->query( "ALTER TABLE `{$tbl_arm_subscriptions_meta}` ADD INDEX `arm-subscription-id` (`arm_subscription_id`)" ); //phpcs:ignore --Reason: $tbl_arm_subscriptions_meta is a table name
			}

			//Add the arm-meta-key INDEX for the Subscriptions Meta table (191 characters prefix, same as WordPress meta tables)
			$arm_subscriptions_meta_add_index_arm_meta_key = $wpdb->get_results( $wpdb->prepare( "SHOW INDEX FROM `{$tbl_arm_subscriptions_meta}` WHERE Key_name=%s", 'arm-meta-key' ) ); //phpcs:ignore --Reason: $tbl_arm_subscriptions_meta is a table name
			if ( empty( $arm_subscriptions_meta_add_index_arm_meta_key ) ) 
			{
				$wpdb->query( "ALTER TABLE `{$tbl_arm_subscriptions_meta}` ADD INDEX `arm-meta-key` (`arm_subscription_meta_key`(191))" ); //phpcs:ignore --Reason: $tbl_arm_subscriptions_meta is a table name
			}

			//Add the arm-subscription-id INDEX for the Payment table
			$arm_payment_log_add_index_arm_subscription_id = $wpdb->get_results( $wpdb->prepare( "SHOW INDEX FROM `{$tbl_arm_payment_log}` WHERE Key_name=%s", 'arm-subscription-id' ) ); //phpcs:ignore --Reason: $tbl_arm_payment_log is a table name
			if ( empty( $arm_payment_log_add_index_arm_subscription_id ) ) 
			{
				$wpdb->query( "ALTER TABLE `{$tbl_arm_payment_log}` ADD INDEX `arm-subscription-id` (`arm_subscription_id`)" ); //phpcs:ignore --Reason: $tbl_arm_payment_log is a table name
			}

			//Add the arm-subscription-id INDEX for the Activity table
			$arm_activity_add_index_arm_subscription_id = $wpdb->get_results( $wpdb->prepare( "SHOW INDEX FROM `{$tbl_arm_activity}` WHERE Key_name=%s", 'arm-subscription-id' ) ); //phpcs:ignore --Reason: $tbl_arm_activity is a table name
			if ( empty( $arm_activity_add_index_arm_subscription_id ) ) 
			{
				$wpdb->query( "ALTER TABLE `{$tbl_arm_activity}` ADD INDEX `arm-subscription-id` (`arm_subscription_id`)" ); //phpcs:ignore --Reason: $tbl_arm_activity is a table name
			}

			//Add the arm-subscription-id INDEX for the Entries table
			$arm_entries_add_index_arm_subscription_id = $wpdb->get_results( $wpdb->prepare( "SHOW INDEX FROM `{$tbl_arm_entries}` WHERE Key_name=%s", 'arm-subscription-id' ) ); //phpcs:ignore --Reason: $tbl_arm_entries is a table name
			if ( empty( $arm_entries_add_index_arm_subscription_id ) ) 
			{
				$wpdb->query( "ALTER TABLE `{$tbl_arm_entries}` ADD INDEX `arm-subscription-id` (`arm_subscription_id`)" ); //phpcs:ignore --Reason: $tbl_arm_entries is a table name
			}
		}

		function arm_add_plan_detail_unique_key( $tbl_plan_detail ) 
		{
			global $wpdb;
			if ( ! $this->arm_table_exists( $tbl_plan_detail ) ) 
			{
				return false;
			}
			if ( ! $this->arm_plan_detail_unique_key_exists( $tbl_plan_detail ) ) 
			{
				$wpdb->query( "DELETE arm_dup FROM `{$tbl_plan_detail}` arm_dup INNER JOIN `{$tbl_plan_detail}` arm_keep ON arm_dup.arm_member_user_id = arm_keep.arm_member_user_id AND arm_dup.arm_member_plan_id = arm_keep.arm_member_plan_id AND arm_dup.arm_member_plan_det_id > arm_keep.arm_member_plan_det_id" ); //phpcs:ignore --Reason table name
				$wpdb->query( "ALTER TABLE `{$tbl_plan_detail}` ADD UNIQUE KEY `arm_member_plan_user` (`arm_member_user_id`,`arm_member_plan_id`)" ); //phpcs:ignore --Reason table name
			}
			return $this->arm_set_plan_detail_unique_key_option( $tbl_plan_detail );
		}

		function arm_set_plan_detail_unique_key_option( $tbl_plan_detail ) 
		{
			$arm_has_key = $this->arm_table_exists( $tbl_plan_detail ) && $this->arm_plan_detail_unique_key_exists( $tbl_plan_detail );
			if ( $arm_has_key ) 
			{
				update_option( 'arm_members_plan_detail_unique_key', 1 );
			} 
			else 
			{
				delete_option( 'arm_members_plan_detail_unique_key' );
			}
			return $arm_has_key;
		}

		function arm_plan_detail_unique_key_exists( $tbl_plan_detail ) 
		{
			global $wpdb;
			$arm_index = $wpdb->get_results( $wpdb->prepare( "SHOW INDEX FROM `{$tbl_plan_detail}` WHERE Key_name = %s", 'arm_member_plan_user' ) ); //phpcs:ignore --Reason table name
			return ! empty( $arm_index );
		}

		function arm_table_exists( $table_name ) 
		{
			global $wpdb;
			$arm_found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
			return ( $arm_found === $table_name );
		}

		function arm_column_exists( $table_name, $column_name ) 
		{
			global $wpdb;
			$arm_column = $wpdb->get_results( $wpdb->prepare( 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s', DB_NAME, $table_name, $column_name ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
			return ! empty( $arm_column );
		}

		function arm_add_column_if_not_exists( $table_name, $column_name, $column_definition, $after_column = '' ) 
		{
			global $wpdb;
			if ( ! $this->arm_table_exists( $table_name ) || $this->arm_column_exists( $table_name, $column_name ) ) 
			{
				return;
			}
			$arm_after = '';
			if ( ! empty( $after_column ) && $this->arm_column_exists( $table_name, $after_column ) ) 
			{
				$arm_after = ' AFTER `' . esc_sql( $after_column ) . '`';
			}
			$wpdb->query( 'ALTER TABLE `' . esc_sql( $table_name ) . '` ADD `' . esc_sql( $column_name ) . '` ' . $column_definition . $arm_after ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared --Reason table/column names are internal constants
		}

		function arm_is_tables_ready() 
		{
			if ( null === $this->arm_tables_ready ) 
			{
				$this->arm_tables_ready = (bool) get_option( 'arm_subscription_tables_installed' );
			}
			return $this->arm_tables_ready;
		}

		function arm_get_meta_key_type( $meta_key, &$plan_id = 0 ) 
		{
			if ( in_array( $meta_key, array( 'arm_user_plan_ids', 'arm_user_suspended_plan_ids', 'arm_user_future_plan_ids' ), true ) ) 
			{
				return 'list';
			}
			if ( 'arm_user_post_ids' === $meta_key ) 
			{
				return 'post'; /* Pay per post: plan id => post id */
			}
			if ( is_string( $meta_key ) && preg_match( '/^arm_user_plan_(\d+)$/', $meta_key, $arm_match ) ) 
			{
				$plan_id = (int) $arm_match[1];
				return ( $plan_id > 0 ) ? 'plan' : '';
			}
			return '';
		}

		function arm_sync_from_user_meta( $user_id, $meta_key ) 
		{
			$user_id = (int) $user_id;
			$this->arm_clear_read_cache( $user_id );
			if ( $user_id <= 0 || $this->arm_is_syncing || ! $this->arm_is_tables_ready() ) 
			{
				return;
			}
			$plan_id  = 0;
			$key_type = $this->arm_get_meta_key_type( $meta_key, $plan_id );
			if ( '' === $key_type ) 
			{
				return;
			}

			$this->arm_is_syncing = true;
			try 
			{
				if ( 'list' === $key_type ) 
				{
					$this->arm_sync_user_all_plans( $user_id );
				} 
				elseif ( 'post' === $key_type ) 
				{
					$this->arm_sync_user_post_ids( $user_id );
				} 
				else 
				{
					$this->arm_sync_member_plan( $user_id, $plan_id );
				}
			} 
			catch ( \Throwable $arm_error ) //phpcs:ignore
			{
				/* Never break the membership flow because of the mirror tables. */
				do_action( 'arm_general_log_entry', 'subscription_sync', 'Subscription table sync error', 'armember', $arm_error->getMessage() );
			}
			$this->arm_is_syncing = false;
		}

		function arm_sync_on_user_meta_change( $meta_id, $user_id, $meta_key ) 
		{
			if ( ! is_string( $meta_key ) || 0 !== strpos( $meta_key, 'arm_user_' ) ) 
			{
				return;
			}
			$this->arm_deleted_meta_key = ( 'deleted_user_meta' === current_action() ) ? $meta_key : '';
			$this->arm_sync_from_user_meta( $user_id, $meta_key );
			$this->arm_deleted_meta_key = '';
		}

		function arm_sync_user_all_plans( $user_id ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_lists  = $this->arm_get_raw_plan_lists( $user_id );

			$arm_list_plans = array_unique( array_merge( $arm_lists['plan_ids'], $arm_lists['future_ids'] ) );

			$arm_table_plans = $wpdb->get_col( $wpdb->prepare( "SELECT arm_member_plan_id FROM `{$ARMemberLite->tbl_arm_members_plan_detail}` WHERE arm_member_user_id = %d", $user_id ) ); //phpcs:ignore --Reason table name
			$arm_open_plans  = $wpdb->get_col( $wpdb->prepare( "SELECT arm_plan_id FROM `{$ARMemberLite->tbl_arm_subscriptions}` WHERE arm_user_id = %d AND arm_plan_status IN (%d,%d,%d)", $user_id, self::ARM_STATUS_ACTIVE, self::ARM_STATUS_SUSPENDED, self::ARM_STATUS_FUTURE ) ); //phpcs:ignore --Reason table name

			$arm_known_plans = array_unique( array_map( 'intval', array_merge( $arm_table_plans, $arm_open_plans ) ) );

			$arm_removed_plans = array_diff( $arm_known_plans, $arm_list_plans );
			foreach ( $arm_removed_plans as $arm_plan_id ) 
			{
				$this->arm_sync_member_plan( $user_id, $arm_plan_id, $arm_lists );
			}
			foreach ( $arm_list_plans as $arm_plan_id ) 
			{
				$this->arm_sync_member_plan( $user_id, $arm_plan_id, $arm_lists );
			}
		}

		function arm_sync_member_plan( $user_id, $plan_id, $arm_lists = null ) 
		{
			global $wpdb, $ARMemberLite;
			$user_id = (int) $user_id;
			$plan_id = (int) $plan_id;
			if ( $user_id <= 0 || $plan_id <= 0 ) 
			{
				return;
			}

			if ( null === $arm_lists ) 
			{
				$arm_lists = $this->arm_get_raw_plan_lists( $user_id );
			}
			$arm_state     = $this->arm_get_plan_state( $plan_id, $arm_lists );
			$arm_plan_data = ( $arm_state > 0 ) ? $this->arm_get_raw_user_meta( $user_id, 'arm_user_plan_' . $plan_id ) : get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
			if ( is_string( $arm_plan_data ) ) 
			{
				$arm_plan_data = arm_maybe_unserialize( $arm_plan_data );
			}
			$arm_plan_data = ( ! empty( $arm_plan_data ) && is_array( $arm_plan_data ) ) ? $arm_plan_data : array();
			$arm_open_row  = $this->arm_get_open_subscription( $user_id, $plan_id );
			$arm_now       = current_time( 'mysql' );

			if ( $arm_state > 0 ) 
			{

				$arm_columns = ! empty( $arm_plan_data ) ? $this->arm_prepare_subscription_columns( $user_id, $plan_id, $arm_plan_data, $arm_open_row ) : array();
				$arm_columns['arm_plan_status']  = $arm_state;
				$arm_columns['arm_updated_date'] = $arm_now;

				if ( empty( $arm_open_row ) ) 
				{
					/* Same plan was closed earlier in this request (e.g. future list cleared before the plan list is updated): continue that row. */
					$arm_open_row = $this->arm_reopen_closed_in_request( $user_id, $plan_id );
					if ( ! empty( $arm_open_row ) ) 
					{
						$arm_columns['arm_ended_date'] = null;
					}
				}

				if ( empty( $arm_open_row ) ) 
				{
					$arm_subscription_id = $this->arm_insert_subscription( $user_id, $plan_id, $arm_columns, $arm_plan_data );
				} 
				else 
				{
					$arm_subscription_id = (int) $arm_open_row['arm_subscription_id'];
					$wpdb->update( $ARMemberLite->tbl_arm_subscriptions, $arm_columns, array( 'arm_subscription_id' => $arm_subscription_id ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery

					if ( self::ARM_STATUS_PENDING === (int) $arm_open_row['arm_plan_status'] ) 
					{
						$this->arm_activate_pending_subscription( $arm_open_row );
					}
				}

				if ( $arm_subscription_id > 0 ) 
				{
					if ( ! empty( $arm_plan_data ) ) 
					{
						$this->arm_sync_subscription_meta( $arm_subscription_id, $arm_plan_data );
					}
					$this->arm_upsert_plan_detail( $user_id, $plan_id, $arm_subscription_id, $arm_state );
					$this->arm_sync_post_id_meta( $user_id, $plan_id, $arm_subscription_id );
				}
			} 
			else 
			{

				$wpdb->delete( $ARMemberLite->tbl_arm_members_plan_detail, array( 'arm_member_user_id' => $user_id, 'arm_member_plan_id' => $plan_id ), array( '%d', '%d' ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery

				if ( ! empty( $arm_open_row ) && in_array( (int) $arm_open_row['arm_plan_status'], array( self::ARM_STATUS_ACTIVE, self::ARM_STATUS_SUSPENDED, self::ARM_STATUS_FUTURE ), true ) ) 
				{
					$this->arm_close_subscription( $arm_open_row, $arm_plan_data );
				}
			}

			do_action( 'arm_after_sync_member_plan_table', $user_id, $plan_id, $arm_state );
		}

		function arm_get_raw_plan_lists( $user_id ) 
		{
			$arm_lists = array(
				'plan_ids'      => $this->arm_get_raw_user_meta( $user_id, 'arm_user_plan_ids' ),
				'suspended_ids' => $this->arm_get_raw_user_meta( $user_id, 'arm_user_suspended_plan_ids' ),
				'future_ids'    => $this->arm_get_raw_user_meta( $user_id, 'arm_user_future_plan_ids' ),
			);
			foreach ( $arm_lists as $arm_key => $arm_ids ) 
			{
				$arm_ids             = ( ! empty( $arm_ids ) && is_array( $arm_ids ) ) ? $arm_ids : array();
				$arm_lists[ $arm_key ] = array_values( array_unique( array_filter( array_map( 'intval', $arm_ids ) ) ) );
			}
			return $arm_lists;
		}

		function arm_get_raw_user_meta( $user_id, $meta_key ) 
		{
			if ( $meta_key === $this->arm_deleted_meta_key || metadata_exists( 'user', $user_id, $meta_key ) || ! $this->arm_is_table_read_allowed( $user_id ) ) 
			{
				return get_user_meta( $user_id, $meta_key, true );
			}
			$arm_value = $this->arm_get_plan_meta_from_tables( $user_id, $meta_key, true );
			if ( ! empty( $arm_value ) ) 
			{
				$arm_is_syncing       = $this->arm_is_syncing;
				$this->arm_is_syncing = true;
				update_user_meta( $user_id, $meta_key, $arm_value );
				$this->arm_is_syncing = $arm_is_syncing;
				do_action( 'arm_general_log_entry', 'subscription_sync', 'Usermeta restored from the subscription tables', 'armember', 'user_id: ' . $user_id . ', meta_key: ' . $meta_key );
			}
			return $arm_value;
		}

		/**
		 * 1 = active, 2 = suspended, 3 = future, 0 = not in user account.
		 */
		function arm_get_plan_state( $plan_id, $arm_lists ) 
		{
			if ( in_array( $plan_id, $arm_lists['plan_ids'], true ) ) 
			{
				return in_array( $plan_id, $arm_lists['suspended_ids'], true ) ? self::ARM_STATUS_SUSPENDED : self::ARM_STATUS_ACTIVE;
			}
			if ( in_array( $plan_id, $arm_lists['future_ids'], true ) ) 
			{
				return self::ARM_STATUS_FUTURE;
			}
			return 0;
		}

		function arm_get_open_subscription( $user_id, $plan_id, $arm_statuses = array( self::ARM_STATUS_PENDING, self::ARM_STATUS_ACTIVE, self::ARM_STATUS_SUSPENDED, self::ARM_STATUS_FUTURE ) ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_statuses = implode( ',', array_map( 'intval', (array) $arm_statuses ) );
			$arm_row      = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$ARMemberLite->tbl_arm_subscriptions}` WHERE arm_user_id = %d AND arm_plan_id = %d AND arm_plan_status IN ({$arm_statuses}) ORDER BY arm_subscription_id DESC LIMIT 1", $user_id, $plan_id ), ARRAY_A ); //phpcs:ignore --Reason table name and int list
			return ! empty( $arm_row ) ? $arm_row : array();
		}

		/**
		 * Map the arm_user_plan_{plan_id} array to arm_subscriptions columns.
		 */
		function arm_prepare_subscription_columns( $user_id, $plan_id, $arm_plan_data, $arm_open_row = array() ) 
		{

			$arm_plan_detail = ( ! empty( $arm_plan_data['arm_current_plan_detail'] ) && is_array( $arm_plan_data['arm_current_plan_detail'] ) ) ? $arm_plan_data['arm_current_plan_detail'] : array();
			$arm_options     = ( isset( $arm_plan_detail['arm_subscription_plan_options'] ) ) ? arm_maybe_unserialize( $arm_plan_detail['arm_subscription_plan_options'] ) : array();
			$arm_options     = is_array( $arm_options ) ? $arm_options : array();
			$arm_plan_type   = isset( $arm_plan_detail['arm_subscription_plan_type'] ) ? sanitize_text_field( $arm_plan_detail['arm_subscription_plan_type'] ) : '';
			$arm_cycle       = ( isset( $arm_plan_data['arm_payment_cycle'] ) && is_numeric( $arm_plan_data['arm_payment_cycle'] ) ) ? (int) $arm_plan_data['arm_payment_cycle'] : 0;

			/* Amount per cycle */
			$arm_amount = isset( $arm_plan_detail['arm_subscription_plan_amount'] ) ? (float) str_replace( ',', '', $arm_plan_detail['arm_subscription_plan_amount'] ) : 0;
			if ( 'recurring' === $arm_plan_type && isset( $arm_options['payment_cycles'][ $arm_cycle ]['cycle_amount'] ) ) 
			{
				$arm_amount = (float) str_replace( ',', '', $arm_options['payment_cycles'][ $arm_cycle ]['cycle_amount'] );
			}

			/* Total cycles: 0 = infinite / not recurring */
			$arm_total_cycle = 0;
			if ( 'recurring' === $arm_plan_type ) 
			{
				if ( isset( $arm_options['payment_cycles'][ $arm_cycle ]['recurring_time'] ) ) 
				{
					$arm_total_cycle = $arm_options['payment_cycles'][ $arm_cycle ]['recurring_time'];
				} 
				elseif ( isset( $arm_options['recurring']['time'] ) ) 
				{
					$arm_total_cycle = $arm_options['recurring']['time'];
				}
				$arm_total_cycle = ( 'infinite' === $arm_total_cycle ) ? 0 : (int) $arm_total_cycle;
			}

			$arm_billing_cycle = 0;
			$arm_billing_type  = '';
			if ( 'recurring' === $arm_plan_type ) 
			{
				if ( ! empty( $arm_options['payment_cycles'] ) && isset( $arm_options['payment_cycles'][ $arm_cycle ] ) ) 
				{
					$arm_billing_type  = ! empty( $arm_options['payment_cycles'][ $arm_cycle ]['billing_type'] ) ? $arm_options['payment_cycles'][ $arm_cycle ]['billing_type'] : 'M';
					$arm_billing_cycle = ! empty( $arm_options['payment_cycles'][ $arm_cycle ]['billing_cycle'] ) ? $arm_options['payment_cycles'][ $arm_cycle ]['billing_cycle'] : 1;
				} 
				elseif ( ! empty( $arm_options['recurring']['type'] ) ) 
				{
					$arm_billing_type  = $arm_options['recurring']['type'];
					$arm_billing_keys  = array( 'D' => 'days', 'W' => 'weeks', 'M' => 'months', 'Y' => 'years' );
					$arm_billing_key   = isset( $arm_billing_keys[ $arm_billing_type ] ) ? $arm_billing_keys[ $arm_billing_type ] : '';
					$arm_billing_cycle = ( '' !== $arm_billing_key && ! empty( $arm_options['recurring'][ $arm_billing_key ] ) ) ? $arm_options['recurring'][ $arm_billing_key ] : 1;
				}
			}

			// Grace period Check
			$arm_grace_type     = ! empty( $arm_plan_data['arm_grace_period_action'] ) ? sanitize_text_field( $arm_plan_data['arm_grace_period_action'] ) : '';
			$arm_grace_end_plan = 0;
			if ( '' !== $arm_grace_type ) 
			{
				$arm_grace_option   = ( 'eot' === $arm_grace_type ) ? 'eot' : 'payment_failed_action';
				$arm_grace_end_plan = ( isset( $arm_options[ $arm_grace_option ] ) && is_numeric( $arm_options[ $arm_grace_option ] ) ) ? (int) $arm_options[ $arm_grace_option ] : 0;
			}

			$arm_is_cancelled = ( ! empty( $arm_plan_data['arm_cencelled_plan'] ) && 'yes' === $arm_plan_data['arm_cencelled_plan'] ) ? 1 : 0;
			$arm_start_date   = $this->arm_timestamp_to_datetime( isset( $arm_plan_data['arm_start_plan'] ) ? $arm_plan_data['arm_start_plan'] : '' );
			$arm_orig_start   = $this->arm_timestamp_to_datetime( isset( $arm_plan_data['arm_started_plan_date'] ) ? $arm_plan_data['arm_started_plan_date'] : '' );

			$arm_columns = array(
				'arm_subscription_type'                   => $this->arm_get_subscription_type( $plan_id, $arm_plan_detail ),
				'arm_plan_type'                           => $arm_plan_type,
				'arm_plan_payment_cycle'                  => $arm_cycle,
				'arm_plan_billing_cycle'                  => (int) $arm_billing_cycle,
				'arm_plan_billing_type'                   => strtoupper( sanitize_text_field( (string) $arm_billing_type ) ),
				'arm_plan_amount'                         => $arm_amount,
				'arm_plan_currency'                       => $this->arm_get_currency(),
				'arm_plan_start_date'                     => $arm_start_date,
				'arm_plan_original_start_date'            => $arm_orig_start,
				'arm_plan_end_date'                       => $this->arm_timestamp_to_datetime( isset( $arm_plan_data['arm_expire_plan'] ) ? $arm_plan_data['arm_expire_plan'] : '' ),
				'arm_plan_cycle_next_date'                => $this->arm_timestamp_to_datetime( isset( $arm_plan_data['arm_next_due_payment'] ) ? $arm_plan_data['arm_next_due_payment'] : '' ),
				'arm_plan_total_cycle'                    => $arm_total_cycle,
				'arm_plan_completed_cycle'                => isset( $arm_plan_data['arm_completed_recurring'] ) ? (int) $arm_plan_data['arm_completed_recurring'] : 0,
				'arm_plan_trial_plan'                     => ! empty( $arm_plan_data['arm_is_trial_plan'] ) ? 1 : 0,
				'arm_plan_trial_start_date'               => $this->arm_timestamp_to_datetime( isset( $arm_plan_data['arm_trial_start'] ) ? $arm_plan_data['arm_trial_start'] : '' ),
				'arm_plan_trial_end_date'                 => $this->arm_timestamp_to_datetime( isset( $arm_plan_data['arm_trial_end'] ) ? $arm_plan_data['arm_trial_end'] : '' ),
				/* As saved in usermeta ('' for non recurring plans). */
				'arm_plan_payment_mode'                   => ! empty( $arm_plan_data['arm_payment_mode'] ) ? sanitize_text_field( $arm_plan_data['arm_payment_mode'] ) : '',
				'arm_plan_payment_gateway'                => ! empty( $arm_plan_data['arm_user_gateway'] ) ? sanitize_text_field( $arm_plan_data['arm_user_gateway'] ) : '',
				'arm_plan_gateway_subscription_id'        => $this->arm_get_gateway_subscription_id( $arm_plan_data ),
				'arm_plan_gateway_customer_id'            => $this->arm_get_gateway_customer_id( $arm_plan_data ),
				'arm_plan_grace_period'                   => ! empty( $arm_plan_data['arm_is_user_in_grace'] ) ? 1 : 0,
				'arm_plan_grace_period_type'              => $arm_grace_type,
				'arm_plan_grace_period_end_date'          => $this->arm_timestamp_to_datetime( isset( $arm_plan_data['arm_grace_period_end'] ) ? $arm_plan_data['arm_grace_period_end'] : '' ),
				'arm_plan_grace_period_end_plan'          => $arm_grace_end_plan,
				'arm_subscription_effective_date'         => $this->arm_timestamp_to_datetime( isset( $arm_plan_data['arm_subscr_effective'] ) ? $arm_plan_data['arm_subscr_effective'] : '' ),
				'arm_subscription_eot_plan'               => ! empty( $arm_plan_data['arm_change_plan_to'] ) ? (int) $arm_plan_data['arm_change_plan_to'] : 0,
				'arm_subscription_cancel_plan_next_cycle' => $arm_is_cancelled,
			);

			$arm_gateway_key = $this->arm_get_gateway_data_key( $arm_plan_data );
			if ( '' !== $arm_gateway_key && array_key_exists( $arm_gateway_key, $arm_plan_data ) ) 
			{
				$arm_columns['arm_plan_gateway_data'] = maybe_serialize( $arm_plan_data[ $arm_gateway_key ] );
			}

			/* Do not empty a value which was set by the payment flow. */
			if ( '' === $arm_columns['arm_plan_gateway_customer_id'] && ! empty( $arm_open_row['arm_plan_gateway_customer_id'] ) ) 
			{
				unset( $arm_columns['arm_plan_gateway_customer_id'] );
			}

			/* Currency is kept as it was at purchase time. */
			if ( ! empty( $arm_open_row['arm_plan_currency'] ) ) 
			{
				unset( $arm_columns['arm_plan_currency'] );
			}

			if ( $arm_is_cancelled && ( empty( $arm_open_row ) || empty( $arm_open_row['arm_cancelled_date'] ) ) ) 
			{
				$arm_columns['arm_cancelled_date'] = current_time( 'mysql' );
			}

			return apply_filters( 'arm_prepare_subscription_table_columns', $arm_columns, $user_id, $plan_id, $arm_plan_data );
		}

		function arm_get_subscription_type( $plan_id, $arm_plan_detail ) 
		{
			if ( ! empty( $arm_plan_detail['arm_subscription_plan_post_id'] ) ) 
			{
				return self::ARM_TYPE_PAID_POST;
			}
			if ( ! empty( $arm_plan_detail['arm_subscription_plan_gift_status'] ) ) 
			{
				return self::ARM_TYPE_GIFT;
			}
			return self::ARM_TYPE_PLAN;
		}

		function arm_get_gateway_data_key( $arm_plan_data ) 
		{
			$arm_gateway = ! empty( $arm_plan_data['arm_user_gateway'] ) ? sanitize_key( $arm_plan_data['arm_user_gateway'] ) : '';
			return ( '' !== $arm_gateway ) ? 'arm_' . $arm_gateway : '';
		}

		function arm_get_gateway_subscription_id( $arm_plan_data ) 
		{
			$arm_subscription_id = ! empty( $arm_plan_data['arm_subscr_id'] ) && is_scalar( $arm_plan_data['arm_subscr_id'] ) ? (string) $arm_plan_data['arm_subscr_id'] : '';
			$arm_gateway_key     = $this->arm_get_gateway_data_key( $arm_plan_data );
			if ( '' === $arm_subscription_id && '' !== $arm_gateway_key && ! empty( $arm_plan_data[ $arm_gateway_key ] ) && is_array( $arm_plan_data[ $arm_gateway_key ] ) ) 
			{
				foreach ( array( 'subscription_id', 'arm_subscr_id', 'subscr_id', 'subscription' ) as $arm_key ) 
				{
					if ( ! empty( $arm_plan_data[ $arm_gateway_key ][ $arm_key ] ) && is_scalar( $arm_plan_data[ $arm_gateway_key ][ $arm_key ] ) ) 
					{
						$arm_subscription_id = (string) $arm_plan_data[ $arm_gateway_key ][ $arm_key ];
						break;
					}
				}
			}
			$arm_subscription_id = apply_filters( 'arm_subscription_table_gateway_subscription_id', $arm_subscription_id, $arm_plan_data );
			return sanitize_text_field( (string) $arm_subscription_id );
		}

		function arm_get_gateway_customer_id( $arm_plan_data ) 
		{
			$arm_gateway     = ! empty( $arm_plan_data['arm_user_gateway'] ) ? sanitize_key( $arm_plan_data['arm_user_gateway'] ) : '';
			$arm_customer_id = '';
			if ( '' !== $arm_gateway && ! empty( $arm_plan_data[ 'arm_' . $arm_gateway ] ) && is_array( $arm_plan_data[ 'arm_' . $arm_gateway ] ) ) 
			{
				$arm_gateway_data = $arm_plan_data[ 'arm_' . $arm_gateway ];
				foreach ( array( 'customer_id', 'customer', 'customer_profile_id' ) as $arm_key ) 
				{
					if ( ! empty( $arm_gateway_data[ $arm_key ] ) && is_scalar( $arm_gateway_data[ $arm_key ] ) ) 
					{
						$arm_customer_id = (string) $arm_gateway_data[ $arm_key ];
						break;
					}
				}
			}
			$arm_customer_id = apply_filters( 'arm_subscription_table_gateway_customer_id', $arm_customer_id, $arm_gateway, $arm_plan_data );
			return sanitize_text_field( (string) $arm_customer_id );
		}

		function arm_get_currency() 
		{
			global $arm_payment_gateways;
			$arm_currency = '';
			if ( is_object( $arm_payment_gateways ) && method_exists( $arm_payment_gateways, 'arm_get_global_currency' ) ) 
			{
				$arm_currency = $arm_payment_gateways->arm_get_global_currency();
			}
			return is_string( $arm_currency ) ? strtoupper( sanitize_text_field( $arm_currency ) ) : '';
		}

		function arm_insert_subscription( $user_id, $plan_id, $arm_columns, $arm_plan_data ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_now    = current_time( 'mysql' );

			$arm_current_user = get_current_user_id();
			$arm_source       = 'purchase';
			$arm_action_by    = 0;
			$arm_ajax_action  = ( wp_doing_ajax() && ! empty( $_REQUEST['action'] ) ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification -- only used to label the row, the request is verified by its own handler

			if ( ! empty( $arm_plan_data['arm_user_gateway'] ) && 'woocommerce' === $arm_plan_data['arm_user_gateway'] ) 
			{
				$arm_source = 'woocommerce';
			} 
			elseif ( in_array( $arm_ajax_action, apply_filters( 'arm_subscription_table_import_actions', array( 'arm_add_import_user' ) ), true ) || 0 === strpos( $arm_ajax_action, 'arm_migrate_subscription_from_' ) ) 
			{
				/* Member import (Lite / Pro) and Migration Tool add-on (MemberPress, PMPro, UMP, RCP, WooCommerce). */
				$arm_source    = 'import';
				$arm_action_by = $arm_current_user;
			} 
			elseif ( $arm_current_user > 0 && $arm_current_user !== (int) $user_id && current_user_can( 'arm_manage_members' ) ) 
			{
				$arm_source    = 'admin_assign';
				$arm_action_by = $arm_current_user;
			} 
			elseif ( wp_doing_cron() ) 
			{
				$arm_source = 'system';
			}

			/* Values given by the flow win. */
			$arm_source    = (string) $this->arm_get_context( $user_id, $plan_id, 'source', $arm_source );
			$arm_action_by = (int) $this->arm_get_context( $user_id, $plan_id, 'action_by', $arm_action_by );
			$arm_gifted_by = (int) $this->arm_get_context( $user_id, $plan_id, 'gifted_by', 0 );

			$arm_previous_id = ( self::ARM_STATUS_PENDING === (int) $arm_columns['arm_plan_status'] ) ? 0 : $this->arm_find_previous_subscription( $user_id, $plan_id );

			$arm_insert = array_merge(
				array(
					'arm_user_id'                  => (int) $user_id,
					'arm_plan_id'                  => (int) $plan_id,
					'arm_previous_subscription_id' => $arm_previous_id,
					'arm_subscription_source'      => sanitize_key( apply_filters( 'arm_subscription_table_source', $arm_source, $user_id, $plan_id, $arm_plan_data ) ),
					'arm_action_by'                => $arm_action_by,
					'arm_gifted_by'                => $arm_gifted_by,
					'arm_created_date'             => $arm_now,
				),
				$arm_columns
			);

			$wpdb->insert( $ARMemberLite->tbl_arm_subscriptions, $arm_insert ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$arm_subscription_id = (int) $wpdb->insert_id;

			if ( $arm_subscription_id > 0 && self::ARM_STATUS_PENDING !== (int) $arm_columns['arm_plan_status'] ) 
			{
				$this->arm_inserted_in_request[ $user_id ][ $arm_subscription_id ] = array(
					'previous_id' => $arm_previous_id,
					'plan_id'     => (int) $plan_id,
				);
			}
			if ( $arm_subscription_id > 0 ) 
			{
				$this->arm_link_unlinked_transactions( $arm_subscription_id, $user_id, $plan_id, $arm_previous_id );
				$this->arm_link_unlinked_activities( $arm_subscription_id, $user_id, $plan_id );
				$this->arm_link_entry_to_subscription( $arm_subscription_id, $user_id, $plan_id, in_array( $arm_insert['arm_subscription_source'], array( 'purchase', 'gift_redeem' ), true ) );
				do_action( 'arm_after_insert_subscription_table_row', $arm_subscription_id, $user_id, $plan_id );
			}
			return $arm_subscription_id;
		}

		function arm_find_previous_subscription( $user_id, $plan_id ) 
		{
			global $wpdb, $ARMemberLite;
			/* Single plan mode: the plan closed in this request was replaced by this one. Multiple membership: plans are independent. */
			if ( ! $this->arm_is_multiple_membership() && ! empty( $this->arm_closed_in_request[ $user_id ] ) ) 
			{
				$arm_previous_id = (int) $this->arm_get_last_closed_in_request( $user_id );
				$this->arm_mark_closed_as_changed( $user_id, $plan_id );
				return $arm_previous_id;
			}
			return (int) $wpdb->get_var( $wpdb->prepare( "SELECT arm_subscription_id FROM `{$ARMemberLite->tbl_arm_subscriptions}` WHERE arm_user_id = %d AND arm_plan_id = %d AND arm_plan_status > %d ORDER BY arm_subscription_id DESC LIMIT 1", $user_id, $plan_id, self::ARM_STATUS_FUTURE ) ); //phpcs:ignore --Reason table name
		}

		function arm_activate_pending_subscription( $arm_row ) 
		{
			global $wpdb, $ARMemberLite;
			$user_id             = (int) $arm_row['arm_user_id'];
			$plan_id             = (int) $arm_row['arm_plan_id'];
			$arm_subscription_id = (int) $arm_row['arm_subscription_id'];
			$arm_previous_id     = (int) $arm_row['arm_previous_subscription_id'];

			if ( empty( $arm_previous_id ) ) 
			{
				$arm_previous_id = $this->arm_find_previous_subscription( $user_id, $plan_id );
				if ( $arm_previous_id > 0 ) 
				{
					$wpdb->update( $ARMemberLite->tbl_arm_subscriptions, array( 'arm_previous_subscription_id' => $arm_previous_id ), array( 'arm_subscription_id' => $arm_subscription_id ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
				}
			}
			$this->arm_inserted_in_request[ $user_id ][ $arm_subscription_id ] = array(
				'previous_id' => $arm_previous_id,
				'plan_id'     => $plan_id,
			);
		}

		function arm_insert_pending_subscription( $log_data ) 
		{
			global $wpdb, $ARMemberLite;
			$user_id = (int) $log_data['arm_user_id'];
			$plan_id = (int) $log_data['arm_plan_id'];

			$arm_plans_table = $ARMemberLite->tbl_arm_subscription_plans;
			$arm_plan_row    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$arm_plans_table}` WHERE arm_subscription_plan_id = %d", $plan_id ), ARRAY_A ); //phpcs:ignore --Reason table name
			if ( empty( $arm_plan_row ) ) 
			{
				return 0;
			}

			/* Same shape as usermeta plan data, so the same column mapping is used. */
			$arm_plan_data = array(
				'arm_current_plan_detail' => $arm_plan_row,
				'arm_payment_cycle'       => isset( $log_data['arm_payment_cycle'] ) ? (int) $log_data['arm_payment_cycle'] : 0,
				'arm_payment_mode'        => isset( $log_data['arm_payment_mode'] ) ? $log_data['arm_payment_mode'] : '',
				'arm_user_gateway'        => isset( $log_data['arm_payment_gateway'] ) ? $log_data['arm_payment_gateway'] : '',
			);
			$arm_columns = $this->arm_prepare_subscription_columns( $user_id, $plan_id, $arm_plan_data, array() );
			if ( ! empty( $log_data['arm_currency'] ) ) 
			{
				$arm_columns['arm_plan_currency'] = strtoupper( sanitize_text_field( $log_data['arm_currency'] ) );
			}
			$arm_columns['arm_plan_status']  = self::ARM_STATUS_PENDING;
			$arm_columns['arm_updated_date'] = current_time( 'mysql' );

			$arm_subscription_id = $this->arm_insert_subscription( $user_id, $plan_id, $arm_columns, $arm_plan_data );
			if ( $arm_subscription_id > 0 ) 
			{
				$this->arm_sync_subscription_meta( $arm_subscription_id, array( 'arm_current_plan_detail' => $arm_plan_row ) );
			}
			return $arm_subscription_id;
		}

		function arm_decline_pending_subscription( $user_id, $plan_id ) 
		{
			global $wpdb, $ARMemberLite;
			if ( ! $this->arm_is_tables_ready() ) 
			{
				return;
			}
			$arm_row = $this->arm_get_open_subscription( (int) $user_id, (int) $plan_id, array( self::ARM_STATUS_PENDING ) );
			if ( ! empty( $arm_row ) ) 
			{
				$arm_now    = current_time( 'mysql' );
				$wpdb->update( //phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$ARMemberLite->tbl_arm_subscriptions,
					array(
						'arm_plan_status'    => self::ARM_STATUS_CANCELLED,
						'arm_cancelled_date' => $arm_now,
						'arm_ended_date'     => $arm_now,
						'arm_updated_date'   => $arm_now,
					),
					array( 'arm_subscription_id' => (int) $arm_row['arm_subscription_id'] )
				);
			}
		}

		function arm_close_subscription( $arm_open_row, $arm_plan_data = array() ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_subscription_id = (int) $arm_open_row['arm_subscription_id'];
			$user_id             = (int) $arm_open_row['arm_user_id'];

			$arm_is_cancelled = ! empty( $arm_plan_data ) ? ( ! empty( $arm_plan_data['arm_cencelled_plan'] ) && 'yes' === $arm_plan_data['arm_cencelled_plan'] ) : ! empty( $arm_open_row['arm_subscription_cancel_plan_next_cycle'] );
			$arm_end_date     = ! empty( $arm_plan_data['arm_expire_plan'] ) ? $this->arm_timestamp_to_datetime( $arm_plan_data['arm_expire_plan'] ) : $arm_open_row['arm_plan_end_date'];
			$arm_now          = current_time( 'mysql' );

			$arm_context_status = (int) $this->arm_get_context( $user_id, $arm_open_row['arm_plan_id'], 'close_status', 0 );

			if ( self::ARM_STATUS_FAILED_PAYMENT === $arm_context_status ) 
			{
				$arm_status = self::ARM_STATUS_FAILED_PAYMENT;
			} 
			elseif ( $arm_is_cancelled ) 
			{
				$arm_status = self::ARM_STATUS_CANCELLED;
			} 
			elseif ( $arm_context_status >= self::ARM_STATUS_EXPIRED && $arm_context_status <= self::ARM_STATUS_FAILED_PAYMENT ) 
			{
				$arm_status = $arm_context_status;
			} 
			elseif ( self::ARM_STATUS_SUSPENDED === (int) $arm_open_row['arm_plan_status'] ) 
			{
				/* Suspended plans are suspended because of a failed payment. */
				$arm_status = self::ARM_STATUS_FAILED_PAYMENT;
			} 
			elseif ( ! empty( $arm_end_date ) && strtotime( $arm_end_date ) <= strtotime( $arm_now ) ) 
			{
				$arm_status = self::ARM_STATUS_EXPIRED;
			} 
			else 
			{
				$arm_status = self::ARM_STATUS_REMOVED;
			}
			$arm_status = (int) apply_filters( 'arm_subscription_table_close_status', $arm_status, $arm_open_row, $arm_plan_data );

			$arm_update = array(
				'arm_plan_status'  => $arm_status,
				'arm_ended_date'   => $arm_now,
				'arm_updated_date' => $arm_now,
			);
			if ( self::ARM_STATUS_CANCELLED === $arm_status && empty( $arm_open_row['arm_cancelled_date'] ) ) 
			{
				$arm_update['arm_cancelled_date'] = $arm_now;
			}
			$wpdb->update( $ARMemberLite->tbl_arm_subscriptions, $arm_update, array( 'arm_subscription_id' => $arm_subscription_id ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery

			$this->arm_closed_in_request[ $user_id ][ $arm_subscription_id ] = array(
				'status'  => $arm_status,
				'plan_id' => (int) $arm_open_row['arm_plan_id'],
			);

			/* Single plan mode: new row was created before this one was closed (same request) -> it is a plan change. */
			if ( ! $this->arm_is_multiple_membership() && ! empty( $this->arm_inserted_in_request[ $user_id ] ) ) 
			{
				foreach ( $this->arm_inserted_in_request[ $user_id ] as $arm_new_id => $arm_new_row ) 
				{
					if ( empty( $arm_new_row['previous_id'] ) && (int) $arm_new_row['plan_id'] !== (int) $arm_open_row['arm_plan_id'] ) 
					{
						$wpdb->update( $ARMemberLite->tbl_arm_subscriptions, array( 'arm_previous_subscription_id' => $arm_subscription_id ), array( 'arm_subscription_id' => $arm_new_id ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
						$this->arm_inserted_in_request[ $user_id ][ $arm_new_id ]['previous_id'] = $arm_subscription_id;
						$this->arm_mark_closed_as_changed( $user_id, $arm_new_row['plan_id'] );
						break;
					}
				}
			}

			do_action( 'arm_after_close_subscription_table_row', $arm_subscription_id, $arm_status, $user_id );
		}

		function arm_reopen_closed_in_request( $user_id, $plan_id ) 
		{
			global $wpdb, $ARMemberLite;
			if ( empty( $this->arm_closed_in_request[ $user_id ] ) ) 
			{
				return array();
			}
			foreach ( $this->arm_closed_in_request[ $user_id ] as $arm_id => $arm_closed ) 
			{
				if ( (int) $arm_closed['plan_id'] === (int) $plan_id ) 
				{
					unset( $this->arm_closed_in_request[ $user_id ][ $arm_id ] );
					$arm_row    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$ARMemberLite->tbl_arm_subscriptions}` WHERE arm_subscription_id = %d", $arm_id ), ARRAY_A ); //phpcs:ignore --Reason table name
					return ! empty( $arm_row ) ? $arm_row : array();
				}
			}
			return array();
		}

		function arm_is_multiple_membership() 
		{
			$arm_is_multiple = ( '1' === (string) get_option( 'arm_is_multiple_membership_feature' ) );
			return (bool) apply_filters( 'arm_subscription_table_is_multiple_membership', $arm_is_multiple );
		}

		function arm_get_last_closed_in_request( $user_id ) 
		{
			$arm_ids = array_keys( $this->arm_closed_in_request[ $user_id ] );
			return end( $arm_ids );
		}

		function arm_mark_closed_as_changed( $user_id, $new_plan_id ) 
		{
			global $wpdb, $ARMemberLite;
			foreach ( $this->arm_closed_in_request[ $user_id ] as $arm_id => $arm_closed ) 
			{
				if ( self::ARM_STATUS_REMOVED === (int) $arm_closed['status'] && (int) $arm_closed['plan_id'] !== (int) $new_plan_id ) 
				{
					$wpdb->update( $ARMemberLite->tbl_arm_subscriptions, array( 'arm_plan_status' => self::ARM_STATUS_CHANGED ), array( 'arm_subscription_id' => $arm_id ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$this->arm_closed_in_request[ $user_id ][ $arm_id ]['status'] = self::ARM_STATUS_CHANGED;
				}
			}
		}

		/**
		 * Keys of arm_user_plan_{plan_id} which have their own column. All other keys go to arm_subscriptions_meta.
		 */
		function arm_get_column_mapped_keys() 
		{
			return array(
				'arm_start_plan',
				'arm_expire_plan',
				'arm_started_plan_date',
				'arm_is_trial_plan',
				'arm_trial_start',
				'arm_trial_end',
				'arm_payment_mode',
				'arm_payment_cycle',
				'arm_is_user_in_grace',
				'arm_grace_period_end',
				'arm_grace_period_action',
				'arm_subscr_effective',
				'arm_change_plan_to',
				'arm_user_gateway',
				'arm_subscr_id',
				'arm_next_due_payment',
				'arm_completed_recurring',
				'arm_cencelled_plan',
			);
		}

		/**
		 * Write meta keys (arm_sent_msgs, gateway arrays, payment_detail, add-on keys). Only changed values are written.
		 */
		function arm_sync_subscription_meta( $arm_subscription_id, $arm_plan_data ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_now      = current_time( 'mysql' );
			$arm_meta_new = array_diff_key( $arm_plan_data, array_flip( $this->arm_get_column_mapped_keys() ) );

			/* Gateway array of this subscription is in arm_plan_gateway_data column. */
			$arm_gateway_key = $this->arm_get_gateway_data_key( $arm_plan_data );
			if ( '' !== $arm_gateway_key ) 
			{
				unset( $arm_meta_new[ $arm_gateway_key ] );
			}

			/* Empty default keys (e.g. other gateways' empty arrays, empty arm_sent_msgs) are not stored: the read adds the defaults back. */
			$arm_default_keys = $this->arm_get_default_plan_keys();

			$arm_existing = $wpdb->get_results( $wpdb->prepare( "SELECT arm_subscription_meta_id, arm_subscription_meta_key, arm_subscription_meta_value FROM `{$ARMemberLite->tbl_arm_subscriptions_meta}` WHERE arm_subscription_id = %d", $arm_subscription_id ), ARRAY_A ); //phpcs:ignore --Reason table name
			$arm_existing_by_key = array();
			foreach ( (array) $arm_existing as $arm_meta_row ) 
			{
				$arm_existing_by_key[ $arm_meta_row['arm_subscription_meta_key'] ] = $arm_meta_row;
			}

			foreach ( $arm_meta_new as $arm_meta_key => $arm_meta_value ) 
			{
				$arm_meta_key   = preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $arm_meta_key );
				if ( '' === $arm_meta_key ) 
				{
					continue;
				}
				if ( isset( $arm_default_keys[ $arm_meta_key ] ) && ( '' === $arm_meta_value || null === $arm_meta_value || array() === $arm_meta_value ) ) 
				{
					if ( isset( $arm_existing_by_key[ $arm_meta_key ] ) ) 
					{
						$wpdb->delete( $ARMemberLite->tbl_arm_subscriptions_meta, array( 'arm_subscription_meta_id' => (int) $arm_existing_by_key[ $arm_meta_key ]['arm_subscription_meta_id'] ), array( '%d' ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
					}
					continue;
				}
				if ( 'arm_current_plan_detail' === $arm_meta_key && is_array( $arm_meta_value ) ) 
				{
					if ( isset( $arm_meta_value['arm_subscription_plan_description'] ) ) 
					{
						$arm_meta_value['arm_subscription_plan_description'] = '';
					}
				}
				$arm_meta_value = maybe_serialize( $arm_meta_value );
				if ( isset( $arm_existing_by_key[ $arm_meta_key ] ) ) 
				{
					if ( $arm_existing_by_key[ $arm_meta_key ]['arm_subscription_meta_value'] !== $arm_meta_value ) 
					{
						$wpdb->update( //phpcs:ignore WordPress.DB.DirectDatabaseQuery
							$ARMemberLite->tbl_arm_subscriptions_meta,
							array(
								'arm_subscription_meta_value'        => $arm_meta_value,
								'arm_subscription_meta_updated_date' => $arm_now,
							),
							array( 'arm_subscription_meta_id' => (int) $arm_existing_by_key[ $arm_meta_key ]['arm_subscription_meta_id'] )
						);
					}
				} 
				else 
				{
					$wpdb->insert( //phpcs:ignore WordPress.DB.DirectDatabaseQuery
						$ARMemberLite->tbl_arm_subscriptions_meta,
						array(
							'arm_subscription_id'                => (int) $arm_subscription_id,
							'arm_subscription_meta_key'          => $arm_meta_key,
							'arm_subscription_meta_value'        => $arm_meta_value,
							'arm_subscription_meta_added_date'   => $arm_now,
							'arm_subscription_meta_updated_date' => $arm_now,
						)
					);
				}
			}
		}

		function arm_get_default_plan_keys() 
		{
			global $arm_subscription_plans;
			if ( is_object( $arm_subscription_plans ) && method_exists( $arm_subscription_plans, 'arm_default_plan_array' ) ) 
			{
				return $arm_subscription_plans->arm_default_plan_array();
			}
			return array();
		}

		function arm_upsert_plan_detail( $user_id, $plan_id, $arm_subscription_id, $arm_state ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_now    = current_time( 'mysql' );

			$arm_sub = $wpdb->get_row( $wpdb->prepare( "SELECT arm_plan_start_date, arm_plan_end_date, arm_plan_cycle_next_date FROM `{$ARMemberLite->tbl_arm_subscriptions}` WHERE arm_subscription_id = %d", $arm_subscription_id ), ARRAY_A ); //phpcs:ignore --Reason table name

			$arm_data = array(
				'arm_subscription_id'           => (int) $arm_subscription_id,
				'arm_member_plan_status'        => (int) $arm_state,
				'arm_member_plan_start_date'    => ! empty( $arm_sub['arm_plan_start_date'] ) ? $arm_sub['arm_plan_start_date'] : null,
				'arm_member_plan_end_date'      => ! empty( $arm_sub['arm_plan_end_date'] ) ? $arm_sub['arm_plan_end_date'] : null,
				'arm_member_plan_next_due_date' => ! empty( $arm_sub['arm_plan_cycle_next_date'] ) ? $arm_sub['arm_plan_cycle_next_date'] : null,
				'arm_member_plan_updated_date'  => $arm_now,
			);

			if ( get_option( 'arm_members_plan_detail_unique_key' ) ) 
			{
				$arm_date_sql = array();
				foreach ( array( 'arm_member_plan_start_date', 'arm_member_plan_end_date', 'arm_member_plan_next_due_date' ) as $arm_date_key ) 
				{
					$arm_date_sql[ $arm_date_key ] = ( null === $arm_data[ $arm_date_key ] ) ? 'NULL' : $wpdb->prepare( '%s', $arm_data[ $arm_date_key ] );
				}
				$wpdb->query( //phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->prepare(
						"INSERT INTO `{$ARMemberLite->tbl_arm_members_plan_detail}` (arm_member_plan_id, arm_member_user_id, arm_subscription_id, arm_member_plan_status, arm_member_plan_start_date, arm_member_plan_end_date, arm_member_plan_next_due_date, arm_member_plan_added_date, arm_member_plan_updated_date)
						VALUES (%d, %d, %d, %d, {$arm_date_sql['arm_member_plan_start_date']}, {$arm_date_sql['arm_member_plan_end_date']}, {$arm_date_sql['arm_member_plan_next_due_date']}, %s, %s)
						ON DUPLICATE KEY UPDATE arm_subscription_id = VALUES(arm_subscription_id), arm_member_plan_status = VALUES(arm_member_plan_status), arm_member_plan_start_date = VALUES(arm_member_plan_start_date), arm_member_plan_end_date = VALUES(arm_member_plan_end_date), arm_member_plan_next_due_date = VALUES(arm_member_plan_next_due_date), arm_member_plan_updated_date = VALUES(arm_member_plan_updated_date)", //phpcs:ignore --Reason table name, date values are prepared above
						$plan_id,
						$user_id,
						$arm_subscription_id,
						$arm_state,
						$arm_now,
						$arm_now
					)
				);
				return;
			}

			$arm_detail_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT arm_member_plan_det_id FROM `{$ARMemberLite->tbl_arm_members_plan_detail}` WHERE arm_member_user_id = %d AND arm_member_plan_id = %d ORDER BY arm_member_plan_det_id ASC LIMIT 1", $user_id, $plan_id ) ); //phpcs:ignore --Reason table name

			if ( $arm_detail_id > 0 ) 
			{
				$wpdb->update( $ARMemberLite->tbl_arm_members_plan_detail, $arm_data, array( 'arm_member_plan_det_id' => $arm_detail_id ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
			} 
			else 
			{
				$arm_data['arm_member_user_id']         = (int) $user_id;
				$arm_data['arm_member_plan_id']         = (int) $plan_id;
				$arm_data['arm_member_plan_added_date'] = $arm_now;
				$wpdb->insert( $ARMemberLite->tbl_arm_members_plan_detail, $arm_data ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
			}
		}

		function arm_get_raw_post_ids( $user_id ) 
		{
			$arm_post_ids = get_user_meta( $user_id, 'arm_user_post_ids', true );
			if ( is_string( $arm_post_ids ) ) 
			{
				$arm_post_ids = arm_maybe_unserialize( $arm_post_ids );
			}
			return ( ! empty( $arm_post_ids ) && is_array( $arm_post_ids ) ) ? $arm_post_ids : array();
		}

		function arm_sync_post_id_meta( $user_id, $plan_id, $arm_subscription_id, $arm_post_ids = null ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_post_ids = ( null === $arm_post_ids ) ? $this->arm_get_raw_post_ids( $user_id ) : $arm_post_ids;
			if ( isset( $arm_post_ids[ $plan_id ] ) && '' !== (string) $arm_post_ids[ $plan_id ] ) 
			{
				$this->arm_sync_subscription_meta( $arm_subscription_id, array( '_arm_paid_post_id' => (string) $arm_post_ids[ $plan_id ] ) );
			} 
			else 
			{
				$wpdb->delete( $ARMemberLite->tbl_arm_subscriptions_meta, array( 'arm_subscription_id' => (int) $arm_subscription_id, 'arm_subscription_meta_key' => '_arm_paid_post_id' ), array( '%d', '%s' ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
			}
		}

		function arm_sync_user_post_ids( $user_id ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_post_ids = $this->arm_get_raw_post_ids( $user_id );
			$arm_rows     = $wpdb->get_results( $wpdb->prepare( "SELECT arm_subscription_id, arm_plan_id FROM `{$ARMemberLite->tbl_arm_subscriptions}` WHERE arm_user_id = %d AND arm_plan_status IN (%d,%d,%d)", $user_id, self::ARM_STATUS_ACTIVE, self::ARM_STATUS_SUSPENDED, self::ARM_STATUS_FUTURE ), ARRAY_A ); //phpcs:ignore --Reason table name
			foreach ( (array) $arm_rows as $arm_row ) 
			{
				$this->arm_sync_post_id_meta( $user_id, (int) $arm_row['arm_plan_id'], (int) $arm_row['arm_subscription_id'], $arm_post_ids );
			}
		}

		function arm_remember_request_entry( $arm_entry ) 
		{
			if ( is_array( $arm_entry ) && ! empty( $arm_entry['arm_entry_id'] ) ) 
			{
				$this->arm_request_entries[ (int) $arm_entry['arm_entry_id'] ] = array(
					'arm_entry_id'    => (int) $arm_entry['arm_entry_id'],
					'arm_user_id'     => isset( $arm_entry['arm_user_id'] ) ? (int) $arm_entry['arm_user_id'] : 0,
					'arm_plan_id'     => isset( $arm_entry['arm_plan_id'] ) ? (int) $arm_entry['arm_plan_id'] : 0,
					'arm_entry_email' => isset( $arm_entry['arm_entry_email'] ) ? (string) $arm_entry['arm_entry_email'] : '',
				);
			}
		}

		function arm_link_entry_by_id( $arm_entry_id, $arm_subscription_id ) 
		{
			global $wpdb, $ARMemberLite;
			if ( ! $this->arm_is_tables_ready() || empty( $arm_entry_id ) || empty( $arm_subscription_id ) ) 
			{
				return false;
			}
			return (bool) $wpdb->update( $ARMemberLite->tbl_arm_entries, array( 'arm_subscription_id' => (int) $arm_subscription_id ), array( 'arm_entry_id' => (int) $arm_entry_id ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		function arm_link_entry_to_subscription( $arm_subscription_id, $user_id, $plan_id, $arm_allow_guess = false ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_user   = get_userdata( (int) $user_id );
			$arm_email  = ( $arm_user && ! empty( $arm_user->user_email ) ) ? strtolower( $arm_user->user_email ) : '';

			$arm_entry_from = $this->arm_get_entry_link_from_date( $user_id, $plan_id );

			foreach ( array_reverse( $this->arm_request_entries, true ) as $arm_entry_id => $arm_entry ) 
			{
				$arm_same_user = ( $arm_entry['arm_user_id'] === (int) $user_id ) || ( empty( $arm_entry['arm_user_id'] ) && '' !== $arm_email && strtolower( $arm_entry['arm_entry_email'] ) === $arm_email );
				if ( $arm_same_user && ( empty( $arm_entry['arm_plan_id'] ) || $arm_entry['arm_plan_id'] === (int) $plan_id ) ) 
				{
					$arm_linked = $wpdb->query( $wpdb->prepare( "UPDATE `{$ARMemberLite->tbl_arm_entries}` SET arm_subscription_id = %d WHERE arm_entry_id = %d AND arm_subscription_id = 0 AND arm_created_date >= %s", $arm_subscription_id, $arm_entry_id, $arm_entry_from ) ); //phpcs:ignore --Reason table name
					unset( $this->arm_request_entries[ $arm_entry_id ] );
					if ( $arm_linked ) 
					{
						return true;
					}
				}
			}
			if ( ! $arm_allow_guess ) 
			{
				return false;
			}

			$arm_from = gmdate( 'Y-m-d H:i:s', strtotime( current_time( 'mysql' ) ) - 7 * DAY_IN_SECONDS );
			if ( strtotime( $arm_entry_from ) > strtotime( $arm_from ) ) 
			{
				$arm_from = $arm_entry_from;
			}
			$arm_entry_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT arm_entry_id FROM `{$ARMemberLite->tbl_arm_entries}` WHERE arm_plan_id = %d AND arm_subscription_id = 0 AND arm_created_date >= %s AND ( arm_user_id = %d OR ( ( arm_user_id IS NULL OR arm_user_id = 0 ) AND arm_entry_email = %s ) ) ORDER BY arm_entry_id DESC LIMIT 1", $plan_id, $arm_from, $user_id, $arm_email ) ); //phpcs:ignore --Reason table name
			if ( $arm_entry_id > 0 ) 
			{
				return $this->arm_link_entry_by_id( $arm_entry_id, $arm_subscription_id );
			}
			return false;
		}

		function arm_get_entry_link_from_date( $user_id, $plan_id ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_from      = '1970-01-01 00:00:00';
			$arm_installed = get_option( 'arm_subscription_tables_installed' );
			if ( ! empty( $arm_installed ) ) 
			{
				$arm_from = $arm_installed;
			}
			$arm_last_end = $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(arm_ended_date, arm_updated_date) FROM `{$ARMemberLite->tbl_arm_subscriptions}` WHERE arm_user_id = %d AND arm_plan_id = %d AND arm_plan_status > %d ORDER BY arm_subscription_id DESC LIMIT 1", $user_id, $plan_id, self::ARM_STATUS_FUTURE ) ); //phpcs:ignore --Reason table name
			if ( ! empty( $arm_last_end ) && strtotime( $arm_last_end ) > strtotime( $arm_from ) ) 
			{
				$arm_from = $arm_last_end;
			}
			return $arm_from;
		}

		function arm_link_transaction_to_subscription( $log_data ) 
		{
			global $wpdb, $ARMemberLite;
			if ( ! $this->arm_is_tables_ready() || empty( $log_data['arm_log_id'] ) || empty( $log_data['arm_user_id'] ) || empty( $log_data['arm_plan_id'] ) ) 
			{
				return;
			}
			try 
			{
				$arm_log_id   = (int) $log_data['arm_log_id'];
				$arm_open_row = $this->arm_get_open_subscription( (int) $log_data['arm_user_id'], (int) $log_data['arm_plan_id'] );

				if ( ! empty( $arm_open_row ) ) 
				{
					$wpdb->update( $ARMemberLite->tbl_arm_payment_log, array( 'arm_subscription_id' => (int) $arm_open_row['arm_subscription_id'] ), array( 'arm_log_id' => $arm_log_id ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$this->arm_apply_transaction_coupon( (int) $arm_open_row['arm_subscription_id'], $arm_log_id );
					$this->arm_link_entry_to_subscription( (int) $arm_open_row['arm_subscription_id'], (int) $log_data['arm_user_id'], (int) $log_data['arm_plan_id'], false );
				} 
				elseif ( $this->arm_is_pending_offline_transaction( $log_data ) ) 
				{
					/* Bank transfer request: plan is given only after admin approval, so keep it as a pending subscription. The insert links this log. */
					$this->arm_insert_pending_subscription( $log_data );
				}
				/* Else the plan is assigned after the payment: the row insert links it (arm_link_unlinked_transactions). */
			} 
			catch ( \Throwable $arm_error ) //phpcs:ignore
			{
				do_action( 'arm_general_log_entry', 'subscription_sync', 'Subscription table transaction link error', 'armember', $arm_error->getMessage() );
			}
		}

		function arm_is_pending_offline_transaction( $log_data ) 
		{
			$arm_gateway = isset( $log_data['arm_payment_gateway'] ) ? $log_data['arm_payment_gateway'] : '';
			$arm_status  = isset( $log_data['arm_transaction_status'] ) ? (string) $log_data['arm_transaction_status'] : '';
			$arm_pending = ( 'bank_transfer' === $arm_gateway && in_array( $arm_status, array( '0', 'pending' ), true ) );
			return (bool) apply_filters( 'arm_subscription_table_is_pending_transaction', $arm_pending, $log_data );
		}

		function arm_apply_transaction_coupon( $arm_subscription_id, $arm_log_id ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_sub    = $wpdb->get_row( $wpdb->prepare( "SELECT arm_coupon_code, arm_plan_amount FROM `{$ARMemberLite->tbl_arm_subscriptions}` WHERE arm_subscription_id = %d", $arm_subscription_id ), ARRAY_A ); //phpcs:ignore --Reason table name
			if ( empty( $arm_sub ) || '' !== (string) $arm_sub['arm_coupon_code'] ) 
			{
				return;
			}
			$arm_log = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$ARMemberLite->tbl_arm_payment_log}` WHERE arm_log_id = %d", $arm_log_id ), ARRAY_A ); //phpcs:ignore --Reason table name
			if ( empty( $arm_log ) || empty( $arm_log['arm_coupon_code'] ) ) 
			{
				return;
			}
			$arm_on_each = ! empty( $arm_log['arm_coupon_on_each_subscriptions'] ) ? 1 : 0;
			$arm_update  = array(
				'arm_coupon_code'                  => sanitize_text_field( $arm_log['arm_coupon_code'] ),
				'arm_coupon_on_each_subscriptions' => $arm_on_each,
				'arm_updated_date'                 => current_time( 'mysql' ),
			);

			$arm_gifted_by = $this->arm_get_gift_buyer_by_coupon( $arm_log['arm_coupon_code'] );
			if ( $arm_gifted_by > 0 && $arm_gifted_by !== (int) $arm_log['arm_user_id'] ) 
			{
				$arm_update['arm_subscription_source'] = 'gift_redeem';
				$arm_update['arm_gifted_by']           = $arm_gifted_by;
			}

			$this->arm_sync_subscription_meta(
				$arm_subscription_id,
				array(
					'_arm_coupon_discount'      => isset( $arm_log['arm_coupon_discount'] ) ? (float) $arm_log['arm_coupon_discount'] : 0,
					'_arm_coupon_discount_type' => isset( $arm_log['arm_coupon_discount_type'] ) ? sanitize_text_field( $arm_log['arm_coupon_discount_type'] ) : '',
				)
			);
			$wpdb->update( $ARMemberLite->tbl_arm_subscriptions, $arm_update, array( 'arm_subscription_id' => $arm_subscription_id ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		function arm_get_gift_buyer_by_coupon( $arm_coupon_code ) 
		{
			global $wpdb, $ARMember;
			static $arm_has_gift_column = null;
			if ( '' === (string) $arm_coupon_code || ! is_object( $ARMember ) || empty( $ARMember->tbl_arm_coupons ) ) 
			{
				return 0;
			}
			if ( null === $arm_has_gift_column ) 
			{
				$arm_has_gift_column = $this->arm_column_exists( $ARMember->tbl_arm_coupons, 'arm_gift_coupon_user_id' );
			}
			if ( ! $arm_has_gift_column ) 
			{
				return 0;
			}
			return (int) $wpdb->get_var( $wpdb->prepare( "SELECT arm_gift_coupon_user_id FROM `{$ARMember->tbl_arm_coupons}` WHERE arm_coupon_code = %s AND arm_gift_coupon_user_id IS NOT NULL LIMIT 1", $arm_coupon_code ) ); //phpcs:ignore --Reason table name
		}

		function arm_add_subscription_to_activity( $arm_activity ) 
		{
			if ( ! is_array( $arm_activity ) || ! $this->arm_is_tables_ready() || empty( $arm_activity['arm_user_id'] ) || empty( $arm_activity['arm_item_id'] ) ) 
			{
				return $arm_activity;
			}
			try 
			{
				$user_id = (int) $arm_activity['arm_user_id'];
				$plan_id = (int) $arm_activity['arm_item_id'];

				$arm_content = isset( $arm_activity['arm_content'] ) ? arm_maybe_unserialize( $arm_activity['arm_content'] ) : array();
				if ( is_array( $arm_content ) && ! empty( $arm_content['arm_currency'] ) && empty( $arm_activity['arm_activity_plan_currency'] ) ) 
				{
					$arm_activity['arm_activity_plan_currency'] = strtoupper( sanitize_text_field( $arm_content['arm_currency'] ) );
				}

				if ( empty( $arm_activity['arm_subscription_id'] ) ) 
				{
					$arm_row = $this->arm_get_open_subscription( $user_id, $plan_id );
					if ( empty( $arm_row ) && ! empty( $this->arm_closed_in_request[ $user_id ] ) ) 
					{
						/* eot / cancel history written right after the row was closed in this request. */
						foreach ( array_reverse( $this->arm_closed_in_request[ $user_id ], true ) as $arm_closed_id => $arm_closed ) 
						{
							if ( (int) $arm_closed['plan_id'] === $plan_id ) 
							{
								$arm_row = array( 'arm_subscription_id' => $arm_closed_id );
								break;
							}
						}
					}
					if ( ! empty( $arm_row['arm_subscription_id'] ) ) 
					{
						$arm_activity['arm_subscription_id'] = (int) $arm_row['arm_subscription_id'];
					}
				}
			} 
			catch ( \Throwable $arm_error ) //phpcs:ignore
			{
				do_action( 'arm_general_log_entry', 'subscription_sync', 'Subscription table activity link error', 'armember', $arm_error->getMessage() );
			}
			return $arm_activity;
		}

		function arm_link_unlinked_activities( $arm_subscription_id, $user_id, $plan_id ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_from_id = (int) get_option( 'arm_subscription_tables_activity_from_id', 0 );
			$arm_last_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT MAX(arm_activity_id) FROM `{$ARMemberLite->tbl_arm_activity}` WHERE arm_user_id = %d AND arm_item_id = %d AND arm_subscription_id > 0 AND arm_subscription_id <> %d", $user_id, $plan_id, $arm_subscription_id ) ); //phpcs:ignore --Reason table name
			$arm_from_id = max( $arm_from_id, $arm_last_id );
			$wpdb->query( $wpdb->prepare( "UPDATE `{$ARMemberLite->tbl_arm_activity}` SET arm_subscription_id = %d WHERE arm_user_id = %d AND arm_item_id = %d AND arm_subscription_id = 0 AND arm_activity_id > %d", $arm_subscription_id, $user_id, $plan_id, $arm_from_id ) ); //phpcs:ignore --Reason table name
		}

		function arm_link_unlinked_transactions( $arm_subscription_id, $user_id, $plan_id, $arm_previous_id = 0 ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_from_date  = get_option( 'arm_subscription_tables_installed' );
			if ( empty( $arm_from_date ) ) 
			{
				return;
			}
			$arm_last_end = $wpdb->get_var( $wpdb->prepare( "SELECT arm_updated_date FROM `{$ARMemberLite->tbl_arm_subscriptions}` WHERE arm_user_id = %d AND arm_plan_id = %d AND arm_plan_status > %d ORDER BY arm_subscription_id DESC LIMIT 1", $user_id, $plan_id, self::ARM_STATUS_FUTURE ) ); //phpcs:ignore --Reason table name
			if ( ! empty( $arm_last_end ) && strtotime( $arm_last_end ) > strtotime( $arm_from_date ) ) 
			{
				$arm_from_date = $arm_last_end;
			}
			$wpdb->query( $wpdb->prepare( "UPDATE `{$ARMemberLite->tbl_arm_payment_log}` SET arm_subscription_id = %d WHERE arm_user_id = %d AND arm_plan_id = %d AND arm_subscription_id = 0 AND arm_created_date >= %s", $arm_subscription_id, $user_id, $plan_id, $arm_from_date ) ); //phpcs:ignore --Reason table name

			$arm_first_log = (int) $wpdb->get_var( $wpdb->prepare( "SELECT arm_log_id FROM `{$ARMemberLite->tbl_arm_payment_log}` WHERE arm_subscription_id = %d ORDER BY arm_log_id ASC LIMIT 1", $arm_subscription_id ) ); //phpcs:ignore --Reason table name
			if ( $arm_first_log > 0 ) 
			{
				$this->arm_apply_transaction_coupon( $arm_subscription_id, $arm_first_log );
			}
		}

		function arm_subscription_after_deleted_user( $user_id ) 
		{
			global $wpdb, $ARMemberLite;
			$this->arm_clear_read_cache( $user_id );
			if ( ! $this->arm_is_tables_ready() || empty( $user_id ) ) 
			{
				return;
			}
			$wpdb->delete( $ARMemberLite->tbl_arm_members_plan_detail, array( 'arm_member_user_id' => (int) $user_id ), array( '%d' ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->query( $wpdb->prepare( "UPDATE `{$ARMemberLite->tbl_arm_subscriptions}` SET arm_plan_status = %d, arm_ended_date = %s, arm_updated_date = %s WHERE arm_user_id = %d AND arm_plan_status IN (%d,%d,%d,%d)", self::ARM_STATUS_REMOVED, current_time( 'mysql' ), current_time( 'mysql' ), $user_id, self::ARM_STATUS_PENDING, self::ARM_STATUS_ACTIVE, self::ARM_STATUS_SUSPENDED, self::ARM_STATUS_FUTURE ) ); //phpcs:ignore --Reason table name
		}

		function arm_get_user_meta_from_tables( $user_id, $meta_key, $single = true ) 
		{
			$user_id = (int) $user_id;
			if ( $user_id <= 0 || ! is_string( $meta_key ) || '' === $meta_key || ! $this->arm_is_table_read_allowed( $user_id ) ) 
			{
				return null;
			}
			if ( ! array_key_exists( $user_id, $this->arm_read_cache ) || ! array_key_exists( $meta_key, $this->arm_read_cache[ $user_id ] ) ) 
			{
				$arm_value   = $this->arm_get_plan_meta_from_tables( $user_id, $meta_key, true );
				$arm_plan_id = 0;
				if ( '' === $arm_value && 'plan' === $this->arm_get_meta_key_type( $meta_key, $arm_plan_id ) ) 
				{
					$arm_value = null;
				}
				$this->arm_read_cache[ $user_id ][ $meta_key ] = $arm_value;
			}
			$arm_value = $this->arm_read_cache[ $user_id ][ $meta_key ];
			if ( null === $arm_value || $single ) 
			{
				return $arm_value;
			}
			return ( '' === $arm_value ) ? array() : array( $arm_value );
		}

		function arm_is_table_read_allowed( $user_id ) 
		{
			if ( ! $this->arm_is_tables_ready() || 2 !== (int) get_option( 'arm_updates_cron_db_subscription_notice', 2 ) ) 
			{
				return false;
			}
			if ( null === $this->arm_read_skip_users ) 
			{
				$this->arm_read_skip_users = array_flip( array_merge( array_keys( (array) get_option( 'arm_subscription_migration_failed_users', array() ) ), array_keys( (array) get_option( 'arm_subscription_migration_mismatch_users', array() ) ) ) );
			}
			$arm_allowed = ! isset( $this->arm_read_skip_users[ (int) $user_id ] );
			return (bool) apply_filters( 'arm_subscription_table_read_allowed', $arm_allowed, $user_id );
		}

		function arm_clear_read_cache( $user_id ) 
		{
			unset( $this->arm_read_cache[ (int) $user_id ] );
		}

		function arm_get_plan_meta_from_tables( $user_id, $meta_key, $single = true ) 
		{
			global $wpdb, $ARMemberLite;
			$user_id = (int) $user_id;
			$plan_id = 0;
			$key_type = $this->arm_get_meta_key_type( $meta_key, $plan_id );
			if ( $user_id <= 0 || '' === $key_type || ! $this->arm_is_tables_ready() ) 
			{
				return null;
			}
			$arm_value  = '';

			if ( 'post' === $key_type ) 
			{
				$arm_rows = $wpdb->get_results( $wpdb->prepare( "SELECT s.arm_plan_id, m.arm_subscription_meta_value FROM `{$ARMemberLite->tbl_arm_subscriptions}` s INNER JOIN `{$ARMemberLite->tbl_arm_subscriptions_meta}` m ON m.arm_subscription_id = s.arm_subscription_id AND m.arm_subscription_meta_key = %s WHERE s.arm_user_id = %d AND s.arm_plan_status IN (%d,%d,%d) ORDER BY s.arm_subscription_id ASC", '_arm_paid_post_id', $user_id, self::ARM_STATUS_ACTIVE, self::ARM_STATUS_SUSPENDED, self::ARM_STATUS_FUTURE ), ARRAY_A ); //phpcs:ignore --Reason table name
				foreach ( (array) $arm_rows as $arm_row ) 
				{
					$arm_value = is_array( $arm_value ) ? $arm_value : array();
					$arm_value[ (int) $arm_row['arm_plan_id'] ] = $arm_row['arm_subscription_meta_value'];
				}
			} 
			elseif ( 'list' === $key_type ) 
			{
				if ( 'arm_user_plan_ids' === $meta_key ) 
				{
					$arm_statuses = array( self::ARM_STATUS_ACTIVE, self::ARM_STATUS_SUSPENDED );
				} 
				elseif ( 'arm_user_suspended_plan_ids' === $meta_key ) 
				{
					$arm_statuses = array( self::ARM_STATUS_SUSPENDED );
				} 
				else 
				{
					$arm_statuses = array( self::ARM_STATUS_FUTURE );
				}
				$arm_ids = $wpdb->get_col( $wpdb->prepare( "SELECT arm_member_plan_id FROM `{$ARMemberLite->tbl_arm_members_plan_detail}` WHERE arm_member_user_id = %d AND arm_member_plan_status IN (" . implode( ',', array_map( 'intval', $arm_statuses ) ) . ") ORDER BY arm_member_plan_det_id ASC", $user_id ) ); //phpcs:ignore --Reason table name and int list
				if ( ! empty( $arm_ids ) ) 
				{
					$arm_value = array_values( array_map( 'intval', $arm_ids ) );
				}
			} 
			else 
			{
				/* Pending (0) rows have no usermeta plan data, so only active / suspended / future rows are returned. */
				$arm_row = $this->arm_get_open_subscription( $user_id, $plan_id, array( self::ARM_STATUS_ACTIVE, self::ARM_STATUS_SUSPENDED, self::ARM_STATUS_FUTURE ) );
				if ( ! empty( $arm_row ) ) 
				{
					$arm_value = $this->arm_build_plan_data_from_row( $arm_row );
				}
			}

			if ( $single ) 
			{
				return $arm_value;
			}
			return ( '' === $arm_value ) ? array() : array( $arm_value );
		}

		function arm_build_plan_data_from_row( $arm_row ) 
		{
			global $wpdb, $ARMemberLite, $arm_subscription_plans;

			$arm_plan_data = array(
				'arm_current_plan_detail' => array(), /* From arm_subscriptions_meta below. */
				'arm_start_plan'          => $this->arm_datetime_to_timestamp( $arm_row['arm_plan_start_date'] ),
				'arm_expire_plan'         => $this->arm_datetime_to_timestamp( $arm_row['arm_plan_end_date'] ),
				'arm_is_trial_plan'       => (int) $arm_row['arm_plan_trial_plan'],
				'arm_trial_start'         => $this->arm_datetime_to_timestamp( $arm_row['arm_plan_trial_start_date'] ),
				'arm_trial_end'           => $this->arm_datetime_to_timestamp( $arm_row['arm_plan_trial_end_date'] ),
				'arm_payment_mode'        => $arm_row['arm_plan_payment_mode'],
				'arm_payment_cycle'       => (int) $arm_row['arm_plan_payment_cycle'],
				'arm_is_user_in_grace'    => (int) $arm_row['arm_plan_grace_period'],
				'arm_grace_period_end'    => $this->arm_datetime_to_timestamp( $arm_row['arm_plan_grace_period_end_date'] ),
				'arm_grace_period_action' => $arm_row['arm_plan_grace_period_type'],
				'arm_subscr_effective'    => $this->arm_datetime_to_timestamp( $arm_row['arm_subscription_effective_date'] ),
				'arm_change_plan_to'      => ! empty( $arm_row['arm_subscription_eot_plan'] ) ? (int) $arm_row['arm_subscription_eot_plan'] : '',
				'arm_user_gateway'        => $arm_row['arm_plan_payment_gateway'],
				'arm_subscr_id'           => $arm_row['arm_plan_gateway_subscription_id'],
				'arm_next_due_payment'    => $this->arm_datetime_to_timestamp( $arm_row['arm_plan_cycle_next_date'] ),
				'arm_completed_recurring' => (int) $arm_row['arm_plan_completed_cycle'],
				'arm_cencelled_plan'      => ! empty( $arm_row['arm_subscription_cancel_plan_next_cycle'] ) ? 'yes' : '',
				'arm_started_plan_date'   => $this->arm_datetime_to_timestamp( $arm_row['arm_plan_original_start_date'] ),
			);

			$arm_meta_rows = $wpdb->get_results( $wpdb->prepare( "SELECT arm_subscription_meta_key, arm_subscription_meta_value FROM `{$ARMemberLite->tbl_arm_subscriptions_meta}` WHERE arm_subscription_id = %d", (int) $arm_row['arm_subscription_id'] ), ARRAY_A ); //phpcs:ignore --Reason table name
			foreach ( (array) $arm_meta_rows as $arm_meta_row ) 
			{
				/* Keys starting with "_arm_" are internal (e.g. coupon discount) and are not part of usermeta. */
				if ( 0 === strpos( $arm_meta_row['arm_subscription_meta_key'], '_arm_' ) ) 
				{
					continue;
				}
				$arm_plan_data[ $arm_meta_row['arm_subscription_meta_key'] ] = arm_maybe_unserialize( $arm_meta_row['arm_subscription_meta_value'] );
			}

			if ( isset( $arm_plan_data['arm_current_plan_detail']['arm_subscription_plan_description'] ) && is_array( $arm_plan_data['arm_current_plan_detail'] ) && '' === $arm_plan_data['arm_current_plan_detail']['arm_subscription_plan_description'] ) 
			{
				$arm_plan_data['arm_current_plan_detail']['arm_subscription_plan_description'] = $this->arm_get_plan_description( (int) $arm_row['arm_plan_id'] );
			}

			if ( ! empty( $arm_row['arm_plan_payment_gateway'] ) && null !== $arm_row['arm_plan_gateway_data'] ) 
			{
				$arm_plan_data[ 'arm_' . sanitize_key( $arm_row['arm_plan_payment_gateway'] ) ] = arm_maybe_unserialize( $arm_row['arm_plan_gateway_data'] );
			}

			if ( is_object( $arm_subscription_plans ) && method_exists( $arm_subscription_plans, 'arm_default_plan_array' ) ) 
			{
				$arm_plan_data = array_merge( $arm_subscription_plans->arm_default_plan_array(), $arm_plan_data );
			}

			return apply_filters( 'arm_build_plan_data_from_subscription_table', $arm_plan_data, $arm_row );
		}

		function arm_get_plan_description( $plan_id ) 
		{
			global $wpdb, $ARMemberLite;
			static $arm_plan_descriptions = array();
			if ( ! isset( $arm_plan_descriptions[ $plan_id ] ) ) 
			{
				$arm_plan_descriptions[ $plan_id ] = (string) $wpdb->get_var( $wpdb->prepare( "SELECT arm_subscription_plan_description FROM `{$ARMemberLite->tbl_arm_subscription_plans}` WHERE arm_subscription_plan_id = %d", $plan_id ) ); //phpcs:ignore --Reason table name
			}
			return $arm_plan_descriptions[ $plan_id ];
		}

		function arm_init_subscription_migration() 
		{
			if ( false !== get_option( 'arm_updates_cron_db_subscription_notice', false ) ) 
			{
				return; /* Already started or done. */
			}
			update_option( 'arm_updates_cron_db_subscription_notice', 0 );
			update_option( 'arm_updates_cron_db_subscription_last_user_id', 0 );
			update_option( 'arm_updates_cron_db_subscription_totals', array(), false );
			foreach ( array( 'arm_updates_cron_db_notice', 'arm_updates_cron_db_activity_notice' ) as $arm_job_option ) 
			{
				if ( false === get_option( $arm_job_option, false ) ) 
				{
					update_option( $arm_job_option, 2 );
				}
			}
			update_option( 'arm_updates_cron_db_initialize', 1 );
		}

		function arm_skip_subscription_migration() 
		{
			if ( false === get_option( 'arm_updates_cron_db_subscription_notice', false ) ) 
			{
				update_option( 'arm_updates_cron_db_subscription_notice', 2 );
			}
		}

		function arm_migrate_user( $user_id ) 
		{
			global $wpdb, $ARMemberLite;
			$user_id = (int) $user_id;
			if ( $user_id <= 0 || ! $this->arm_is_tables_ready() ) 
			{
				return false;
			}
			$arm_stats  = array( 'created' => 0, 'skipped' => 0, 'history' => 0, 'logs' => 0, 'activities' => 0, 'entries' => 0, 'mismatch' => 0 );
			$this->arm_clear_read_cache( $user_id );

			$this->arm_is_syncing = true;
			unset( $this->arm_migration_filled_keys[ $user_id ] );
			try 
			{
				$this->arm_migration_cleanup_user( $user_id );

				$arm_lists         = $this->arm_get_raw_plan_lists( $user_id );
				$arm_current_plans = array_values( array_unique( array_merge( $arm_lists['plan_ids'], $arm_lists['future_ids'] ) ) );

				/* 1. Periods from the membership history not linked yet (rows before the tables were installed). */
				$arm_activities = $wpdb->get_results( $wpdb->prepare( "SELECT arm_activity_id, arm_action, arm_content, arm_item_id, arm_date_recorded FROM `{$ARMemberLite->tbl_arm_activity}` WHERE arm_user_id = %d AND arm_type = %s AND arm_subscription_id = 0 ORDER BY arm_activity_id ASC", $user_id, 'membership' ), ARRAY_A ); //phpcs:ignore --Reason table name
				$arm_periods    = $this->arm_migration_build_periods( (array) $arm_activities, $arm_current_plans );

				/* 2. Current plans. */
				$arm_period_row = array(); /* period index => subscription id */
				foreach ( $arm_current_plans as $arm_plan_id ) 
				{
					$arm_period_idx = null;
					foreach ( $arm_periods as $arm_idx => $arm_period ) 
					{
						if ( ! empty( $arm_period['current'] ) && (int) $arm_period['plan_id'] === (int) $arm_plan_id ) 
						{
							$arm_period_idx = $arm_idx;
						}
					}
					$arm_open_row = $this->arm_get_open_subscription( $user_id, $arm_plan_id );
					if ( ! empty( $arm_open_row ) ) 
					{
						/* Dual-write already made it after the update: keep it as it is. */
						$arm_subscription_id = (int) $arm_open_row['arm_subscription_id'];
						$arm_stats['skipped']++;
					} 
					else 
					{
						$arm_subscription_id = $this->arm_migration_insert_current( $user_id, $arm_plan_id, $arm_lists, ( null !== $arm_period_idx ) ? $arm_periods[ $arm_period_idx ] : array() );
						if ( $arm_subscription_id > 0 ) 
						{
							$arm_stats['created']++;
						}
					}
					if ( null !== $arm_period_idx && $arm_subscription_id > 0 ) 
					{
						$arm_period_row[ $arm_period_idx ] = $arm_subscription_id;
					}
				}

				foreach ( $arm_periods as $arm_idx => $arm_period ) 
				{
					if ( ! empty( $arm_period['current'] ) ) 
					{
						continue;
					}
					$arm_previous_id = ( null !== $arm_period['previous'] && isset( $arm_period_row[ $arm_period['previous'] ] ) ) ? $arm_period_row[ $arm_period['previous'] ] : 0;
					$arm_subscription_id = $this->arm_migration_insert_period( $user_id, $arm_period, $arm_previous_id );
					if ( $arm_subscription_id > 0 ) 
					{
						$arm_period_row[ $arm_idx ] = $arm_subscription_id;
						$arm_stats['history']++;
					}
				}

				/* Current row gets its previous period (plan change before the update) when it has none. */
				foreach ( $arm_periods as $arm_idx => $arm_period ) 
				{
					if ( ! empty( $arm_period['current'] ) && isset( $arm_period_row[ $arm_idx ] ) && null !== $arm_period['previous'] && isset( $arm_period_row[ $arm_period['previous'] ] ) ) 
					{
						$wpdb->query( $wpdb->prepare( "UPDATE `{$ARMemberLite->tbl_arm_subscriptions}` SET arm_previous_subscription_id = %d WHERE arm_subscription_id = %d AND arm_previous_subscription_id = 0", $arm_period_row[ $arm_period['previous'] ], $arm_period_row[ $arm_idx ] ) ); //phpcs:ignore --Reason table name
					}
				}

				foreach ( $arm_current_plans as $arm_plan_id ) 
				{
					$arm_has_current = false;
					$arm_last_idx    = null;
					foreach ( $arm_periods as $arm_idx => $arm_period ) 
					{
						if ( (int) $arm_period['plan_id'] === (int) $arm_plan_id ) 
						{
							$arm_last_idx = $arm_idx;
							if ( ! empty( $arm_period['current'] ) ) 
							{
								$arm_has_current = true;
							}
						}
					}
					if ( ! $arm_has_current && null !== $arm_last_idx && isset( $arm_period_row[ $arm_last_idx ] ) ) 
					{
						$arm_wpdb_row = $this->arm_get_open_subscription( $user_id, $arm_plan_id );
						if ( ! empty( $arm_wpdb_row ) ) 
						{
							$wpdb->query( $wpdb->prepare( "UPDATE `{$ARMemberLite->tbl_arm_subscriptions}` SET arm_previous_subscription_id = %d WHERE arm_subscription_id = %d AND arm_previous_subscription_id = 0", $arm_period_row[ $arm_last_idx ], $arm_wpdb_row['arm_subscription_id'] ) ); //phpcs:ignore --Reason table name
						}
					}
				}

				foreach ( $arm_periods as $arm_idx => $arm_period ) 
				{
					if ( empty( $arm_period_row[ $arm_idx ] ) || empty( $arm_period['activities'] ) ) 
					{
						continue;
					}
					$arm_ids = implode( ',', array_map( 'intval', $arm_period['activities'] ) );
					$arm_stats['activities'] += (int) $wpdb->query( $wpdb->prepare( "UPDATE `{$ARMemberLite->tbl_arm_activity}` SET arm_subscription_id = %d WHERE arm_activity_id IN ({$arm_ids}) AND arm_subscription_id = 0", $arm_period_row[ $arm_idx ] ) ); //phpcs:ignore --Reason table name and int list
				}

				$arm_stats['logs'] = $this->arm_migration_link_payment_logs( $user_id );

				$arm_stats['entries'] = $this->arm_migration_link_entries( $user_id );

				$this->arm_sync_user_post_ids( $user_id );

				$arm_stats['mismatch'] = $this->arm_migration_verify_user( $user_id, $arm_current_plans, $arm_lists );

			} 
			catch ( \Throwable $arm_error ) //phpcs:ignore
			{
				$this->arm_is_syncing = false;
				$this->arm_migration_add_user_to_option( 'arm_subscription_migration_failed_users', $user_id, $arm_error->getMessage() );
				do_action( 'arm_general_log_entry', 'subscription_migration', 'Subscription migration error user ' . $user_id, 'armember', $arm_error->getMessage() );
				return false;
			}
			$this->arm_is_syncing = false;

			do_action( 'arm_after_migrate_user_subscriptions', $user_id, $arm_stats );
			return $arm_stats;
		}

		function arm_migration_cleanup_user( $user_id ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_ids    = $wpdb->get_col( $wpdb->prepare( "SELECT arm_subscription_id FROM `{$ARMemberLite->tbl_arm_subscriptions}` WHERE arm_user_id = %d AND arm_subscription_source = %s", $user_id, 'migration' ) ); //phpcs:ignore --Reason table name
			if ( empty( $arm_ids ) ) 
			{
				return;
			}
			$arm_in = implode( ',', array_map( 'intval', $arm_ids ) );
			$wpdb->query( "UPDATE `{$ARMemberLite->tbl_arm_payment_log}` SET arm_subscription_id = 0 WHERE arm_subscription_id IN ({$arm_in})" ); //phpcs:ignore --Reason table name and int list
			$wpdb->query( "UPDATE `{$ARMemberLite->tbl_arm_activity}` SET arm_subscription_id = 0 WHERE arm_subscription_id IN ({$arm_in})" ); //phpcs:ignore --Reason table name and int list
			$wpdb->query( "UPDATE `{$ARMemberLite->tbl_arm_entries}` SET arm_subscription_id = 0 WHERE arm_subscription_id IN ({$arm_in})" ); //phpcs:ignore --Reason table name and int list
			$wpdb->query( "UPDATE `{$ARMemberLite->tbl_arm_subscriptions}` SET arm_previous_subscription_id = 0 WHERE arm_previous_subscription_id IN ({$arm_in})" ); //phpcs:ignore --Reason table name and int list
			$wpdb->query( "DELETE FROM `{$ARMemberLite->tbl_arm_subscriptions_meta}` WHERE arm_subscription_id IN ({$arm_in})" ); //phpcs:ignore --Reason table name and int list
			$wpdb->query( "DELETE FROM `{$ARMemberLite->tbl_arm_members_plan_detail}` WHERE arm_subscription_id IN ({$arm_in})" ); //phpcs:ignore --Reason table name and int list
			$wpdb->query( "DELETE FROM `{$ARMemberLite->tbl_arm_subscriptions}` WHERE arm_subscription_id IN ({$arm_in})" ); //phpcs:ignore --Reason table name and int list
		}

		function arm_migration_build_periods( $arm_activities, $arm_current_plans ) 
		{
			$arm_periods  = array();
			$arm_open     = array(); /* plan id => period index */
			$arm_multiple = $this->arm_is_multiple_membership();
			$arm_now      = current_time( 'mysql' );

			foreach ( $arm_activities as $arm_activity ) 
			{
				$arm_plan_id = (int) $arm_activity['arm_item_id'];
				if ( $arm_plan_id <= 0 ) 
				{
					continue;
				}
				$arm_action  = (string) $arm_activity['arm_action'];
				$arm_content = arm_maybe_unserialize( $arm_activity['arm_content'] );
				$arm_content = is_array( $arm_content ) ? $arm_content : array();
				$arm_by      = isset( $arm_content['action_by'] ) ? (string) $arm_content['action_by'] : '';
				$arm_date    = ! empty( $arm_content['start'] ) && in_array( $arm_action, array( 'new_subscription', 'change_subscription', 'renew_subscription' ), true ) ? $this->arm_timestamp_to_datetime( $arm_content['start'] ) : $arm_activity['arm_date_recorded'];
				$arm_date    = ! empty( $arm_date ) ? $arm_date : $arm_now;

				$arm_is_start = in_array( $arm_action, array( 'new_subscription', 'change_subscription' ), true );

				if ( ! isset( $arm_open[ $arm_plan_id ] ) ) 
				{
					$arm_previous = null;
					/* Re-purchase of a plan which ended before: previous = last period of the same plan. */
					for ( $arm_i = count( $arm_periods ) - 1; $arm_i >= 0; $arm_i-- ) 
					{
						if ( (int) $arm_periods[ $arm_i ]['plan_id'] === $arm_plan_id ) 
						{
							$arm_previous = $arm_i;
							break;
						}
					}
					if ( $arm_is_start && ! $arm_multiple ) 
					{
						foreach ( $arm_open as $arm_other_plan => $arm_other_idx ) 
						{
							$arm_status = ! empty( $arm_periods[ $arm_other_idx ]['cancelled'] ) ? self::ARM_STATUS_CANCELLED : self::ARM_STATUS_CHANGED;
							$this->arm_migration_close_period( $arm_periods, $arm_open, $arm_other_idx, $arm_status, $arm_date );
							$arm_previous = $arm_other_idx;
						}
					}
					$arm_periods[] = array(
						'plan_id'        => $arm_plan_id,
						'activities'     => array(),
						'first_content'  => $arm_content,
						'last_content'   => $arm_content,
						'start'          => $arm_date,
						'closed_at'      => null,
						'status'         => null,
						'cancelled'      => false,
						'cancelled_date' => null,
						'failed'         => false,
						'previous'       => $arm_previous,
						'current'        => false,
					);
					$arm_open[ $arm_plan_id ] = count( $arm_periods ) - 1;
				}

				$arm_idx                                   = $arm_open[ $arm_plan_id ];
				$arm_periods[ $arm_idx ]['activities'][]   = (int) $arm_activity['arm_activity_id'];
				if ( ! empty( $arm_content ) ) 
				{
					$arm_periods[ $arm_idx ]['last_content'] = $arm_content;
				}

				switch ( $arm_action ) 
				{
					case 'cancel_subscription':
						$arm_periods[ $arm_idx ]['cancelled']      = true;
						$arm_periods[ $arm_idx ]['cancelled_date'] = $arm_activity['arm_date_recorded'];
						/* Terminated by admin / account closed: the plan is removed at once. Else it runs till end of term. */
						if ( in_array( $arm_by, array( 'terminate', 'close_account' ), true ) ) 
						{
							$this->arm_migration_close_period( $arm_periods, $arm_open, $arm_idx, self::ARM_STATUS_CANCELLED, $arm_activity['arm_date_recorded'] );
						}
						break;
					case 'eot':
						$arm_status = ! empty( $arm_periods[ $arm_idx ]['cancelled'] ) ? self::ARM_STATUS_CANCELLED : self::ARM_STATUS_EXPIRED;
						$arm_end    = ! empty( $arm_content['expire'] ) ? $this->arm_timestamp_to_datetime( $arm_content['expire'] ) : $arm_activity['arm_date_recorded'];
						$this->arm_migration_close_period( $arm_periods, $arm_open, $arm_idx, $arm_status, $arm_end );
						break;
					case 'failed_payment':
						$arm_periods[ $arm_idx ]['failed'] = true;
						break;
				}
			}

			foreach ( $arm_open as $arm_plan_id => $arm_idx ) 
			{
				if ( in_array( (int) $arm_plan_id, array_map( 'intval', $arm_current_plans ), true ) ) 
				{
					$arm_periods[ $arm_idx ]['current'] = true;
					continue;
				}
				$arm_last   = $arm_periods[ $arm_idx ]['last_content'];
				$arm_end    = ! empty( $arm_last['expire'] ) ? $this->arm_timestamp_to_datetime( $arm_last['expire'] ) : null;
				if ( ! empty( $arm_periods[ $arm_idx ]['cancelled'] ) ) 
				{
					$arm_status = self::ARM_STATUS_CANCELLED;
				} 
				elseif ( ! empty( $arm_periods[ $arm_idx ]['failed'] ) ) 
				{
					$arm_status = self::ARM_STATUS_FAILED_PAYMENT;
				} 
				else 
				{
					$arm_status = self::ARM_STATUS_EXPIRED;
				}
				$this->arm_migration_close_period( $arm_periods, $arm_open, $arm_idx, $arm_status, ! empty( $arm_end ) ? $arm_end : $arm_periods[ $arm_idx ]['start'] );
			}

			foreach ( array_map( 'intval', $arm_current_plans ) as $arm_plan_id ) 
			{
				$arm_last_idx = null;
				foreach ( $arm_periods as $arm_idx => $arm_period ) 
				{
					if ( (int) $arm_period['plan_id'] === $arm_plan_id ) 
					{
						$arm_last_idx = $arm_idx;
					}
				}
				if ( null !== $arm_last_idx && empty( $arm_periods[ $arm_last_idx ]['current'] ) && in_array( (int) $arm_periods[ $arm_last_idx ]['status'], array( self::ARM_STATUS_EXPIRED, self::ARM_STATUS_FAILED_PAYMENT ), true ) ) 
				{
					$arm_periods[ $arm_last_idx ]['current']   = true;
					$arm_periods[ $arm_last_idx ]['status']    = null;
					$arm_periods[ $arm_last_idx ]['closed_at'] = null;
				}
			}

			return $arm_periods;
		}

		function arm_migration_close_period( &$arm_periods, &$arm_open, $arm_idx, $arm_status, $arm_date ) 
		{
			$arm_periods[ $arm_idx ]['status']    = (int) $arm_status;
			$arm_periods[ $arm_idx ]['closed_at'] = $arm_date;
			unset( $arm_open[ $arm_periods[ $arm_idx ]['plan_id'] ] );
		}

		function arm_migration_plan_data_from_content( $arm_first, $arm_last ) 
		{
			$arm_detail = ( ! empty( $arm_last['plan_detail'] ) && is_array( $arm_last['plan_detail'] ) ) ? $arm_last['plan_detail'] : array();
			return array(
				'arm_current_plan_detail' => $arm_detail,
				'arm_start_plan'          => ! empty( $arm_first['start'] ) ? $arm_first['start'] : '',
				'arm_expire_plan'         => ! empty( $arm_last['expire'] ) ? $arm_last['expire'] : '',
				'arm_user_gateway'        => ! empty( $arm_last['gateway'] ) ? $arm_last['gateway'] : '',
				'arm_payment_cycle'       => isset( $arm_detail['arm_user_selected_payment_cycle'] ) ? $arm_detail['arm_user_selected_payment_cycle'] : 0,
				'arm_payment_mode'        => isset( $arm_detail['arm_user_old_payment_mode'] ) ? $arm_detail['arm_user_old_payment_mode'] : '',
				'arm_subscr_effective'    => isset( $arm_last['arm_subscr_effective'] ) ? $arm_last['arm_subscr_effective'] : '',
				'arm_change_plan_to'      => isset( $arm_last['arm_change_plan_to'] ) ? $arm_last['arm_change_plan_to'] : '',
			);
		}

		function arm_migration_insert_current( $user_id, $plan_id, $arm_lists, $arm_period ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_state     = $this->arm_get_plan_state( (int) $plan_id, $arm_lists );
			$arm_plan_data = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
			if ( is_string( $arm_plan_data ) ) 
			{
				$arm_plan_data = arm_maybe_unserialize( $arm_plan_data );
			}
			$arm_plan_data = ( ! empty( $arm_plan_data ) && is_array( $arm_plan_data ) ) ? $arm_plan_data : array();

			$arm_columns = ! empty( $arm_plan_data ) ? $this->arm_prepare_subscription_columns( $user_id, $plan_id, $arm_plan_data, array() ) : array();

			$arm_history_plan_data = array();
			if ( ! empty( $arm_period['last_content'] ) ) 
			{
				$arm_history_plan_data = $this->arm_migration_plan_data_from_content( $arm_period['first_content'], $arm_period['last_content'] );
				$arm_history_columns   = $this->arm_prepare_subscription_columns( $user_id, $plan_id, $arm_history_plan_data, array() );
				if ( empty( $arm_history_columns['arm_plan_start_date'] ) ) 
				{
					$arm_history_columns['arm_plan_start_date'] = $arm_period['start'];
				}
				if ( ! empty( $arm_period['last_content']['payment_data'] ) ) 
				{
					$arm_history_columns['arm_plan_gateway_data'] = maybe_serialize( $arm_period['last_content']['payment_data'] );
				}

				/* column => usermeta key it comes from */
				$arm_fill_map = array(
					'arm_subscription_type'            => 'arm_current_plan_detail',
					'arm_plan_type'                    => 'arm_current_plan_detail',
					'arm_plan_amount'                  => 'arm_current_plan_detail',
					'arm_plan_total_cycle'             => 'arm_current_plan_detail',
					'arm_plan_billing_cycle'           => 'arm_current_plan_detail',
					'arm_plan_billing_type'            => 'arm_current_plan_detail',
					'arm_plan_payment_cycle'           => 'arm_payment_cycle',
					'arm_plan_start_date'              => 'arm_start_plan',
					'arm_plan_end_date'                => 'arm_expire_plan',
					'arm_plan_payment_mode'            => 'arm_payment_mode',
					'arm_plan_payment_gateway'         => 'arm_user_gateway',
					'arm_plan_gateway_subscription_id' => 'arm_subscr_id',
					'arm_plan_gateway_data'            => 'arm_user_gateway',
					'arm_subscription_effective_date'  => 'arm_subscr_effective',
					'arm_subscription_eot_plan'        => 'arm_change_plan_to',
				);
				$arm_must_have = array( 'arm_current_plan_detail', 'arm_start_plan', 'arm_user_gateway' );
				foreach ( $arm_fill_map as $arm_column => $arm_meta_key ) 
				{
					$arm_meta_missing = empty( $arm_plan_data ) || ! array_key_exists( $arm_meta_key, $arm_plan_data ) || ( in_array( $arm_meta_key, $arm_must_have, true ) && empty( $arm_plan_data[ $arm_meta_key ] ) );
					if ( $arm_meta_missing && isset( $arm_history_columns[ $arm_column ] ) && '' !== (string) $arm_history_columns[ $arm_column ] ) 
					{
						$arm_columns[ $arm_column ] = $arm_history_columns[ $arm_column ];
						$this->arm_migration_filled_keys[ (int) $user_id ][ (int) $plan_id ][ $arm_meta_key ] = true;
					}
				}
			}

			$arm_columns['arm_user_id']     = (int) $user_id;
			$arm_columns['arm_plan_id']     = (int) $plan_id;
			$arm_columns['arm_plan_status'] = $arm_state;
			$arm_columns['arm_subscription_source'] = 'migration';
			$arm_columns['arm_created_date']        = ! empty( $arm_period['start'] ) ? $arm_period['start'] : ( ! empty( $arm_columns['arm_plan_start_date'] ) ? $arm_columns['arm_plan_start_date'] : current_time( 'mysql' ) );
			$arm_columns['arm_updated_date']        = current_time( 'mysql' );
			if ( empty( $arm_columns['arm_plan_currency'] ) && ! empty( $arm_period['last_content']['arm_currency'] ) ) 
			{
				$arm_columns['arm_plan_currency'] = strtoupper( sanitize_text_field( $arm_period['last_content']['arm_currency'] ) );
			}
			if ( ! empty( $arm_columns['arm_subscription_cancel_plan_next_cycle'] ) && ! empty( $arm_period['cancelled_date'] ) ) 
			{
				$arm_columns['arm_cancelled_date'] = $arm_period['cancelled_date'];
			}

			$wpdb->insert( $ARMemberLite->tbl_arm_subscriptions, $arm_columns ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$arm_subscription_id = (int) $wpdb->insert_id;
			if ( $arm_subscription_id > 0 ) 
			{
				if ( ! empty( $arm_plan_data ) ) 
				{
					$this->arm_sync_subscription_meta( $arm_subscription_id, $arm_plan_data );
				}
				/* Plan detail missing in usermeta: the one from the history. */
				if ( ! empty( $this->arm_migration_filled_keys[ (int) $user_id ][ (int) $plan_id ]['arm_current_plan_detail'] ) && ! empty( $arm_history_plan_data['arm_current_plan_detail'] ) ) 
				{
					$this->arm_sync_subscription_meta( $arm_subscription_id, array( 'arm_current_plan_detail' => $arm_history_plan_data['arm_current_plan_detail'] ) );
				}
				$this->arm_upsert_plan_detail( $user_id, $plan_id, $arm_subscription_id, $arm_state );
			}
			return $arm_subscription_id;
		}

		function arm_migration_insert_period( $user_id, $arm_period, $arm_previous_id ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_plan_data = $this->arm_migration_plan_data_from_content( $arm_period['first_content'], $arm_period['last_content'] );

			$arm_columns = $this->arm_prepare_subscription_columns( $user_id, $arm_period['plan_id'], $arm_plan_data, array() );
			unset( $arm_columns['arm_cancelled_date'] );
			$arm_columns['arm_user_id']                  = (int) $user_id;
			$arm_columns['arm_plan_id']                  = (int) $arm_period['plan_id'];
			$arm_columns['arm_previous_subscription_id'] = (int) $arm_previous_id;
			$arm_columns['arm_plan_status']              = (int) $arm_period['status'];
			$arm_columns['arm_subscription_source']      = 'migration';
			$arm_columns['arm_subscription_cancel_plan_next_cycle'] = ! empty( $arm_period['cancelled'] ) ? 1 : 0;
			$arm_columns['arm_cancelled_date']           = ! empty( $arm_period['cancelled'] ) ? ( ! empty( $arm_period['cancelled_date'] ) ? $arm_period['cancelled_date'] : $arm_period['closed_at'] ) : null;
			$arm_columns['arm_created_date']             = ! empty( $arm_period['start'] ) ? $arm_period['start'] : current_time( 'mysql' );
			$arm_columns['arm_updated_date']             = ! empty( $arm_period['closed_at'] ) ? $arm_period['closed_at'] : $arm_columns['arm_created_date'];
			$arm_columns['arm_ended_date']               = $arm_columns['arm_updated_date'];
			if ( empty( $arm_columns['arm_plan_start_date'] ) ) 
			{
				$arm_columns['arm_plan_start_date'] = $arm_period['start'];
			}
			if ( ! empty( $arm_period['last_content']['arm_currency'] ) ) 
			{
				$arm_columns['arm_plan_currency'] = strtoupper( sanitize_text_field( $arm_period['last_content']['arm_currency'] ) );
			}
			if ( ! empty( $arm_period['last_content']['payment_data'] ) && empty( $arm_columns['arm_plan_gateway_data'] ) ) 
			{
				$arm_columns['arm_plan_gateway_data'] = maybe_serialize( $arm_period['last_content']['payment_data'] );
				if ( empty( $arm_columns['arm_plan_gateway_subscription_id'] ) && is_array( $arm_period['last_content']['payment_data'] ) ) 
				{
					foreach ( array( 'subscription_id', 'arm_subscr_id', 'subscr_id', 'subscription' ) as $arm_key ) 
					{
						if ( ! empty( $arm_period['last_content']['payment_data'][ $arm_key ] ) && is_scalar( $arm_period['last_content']['payment_data'][ $arm_key ] ) ) 
						{
							$arm_columns['arm_plan_gateway_subscription_id'] = sanitize_text_field( (string) $arm_period['last_content']['payment_data'][ $arm_key ] );
							break;
						}
					}
				}
			}
			$wpdb->insert( $ARMemberLite->tbl_arm_subscriptions, $arm_columns ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$arm_subscription_id = (int) $wpdb->insert_id;
			if ( $arm_subscription_id > 0 && ! empty( $arm_plan_data['arm_current_plan_detail'] ) ) 
			{
				$this->arm_sync_subscription_meta( $arm_subscription_id, array( 'arm_current_plan_detail' => $arm_plan_data['arm_current_plan_detail'] ) );
			}
			return $arm_subscription_id;
		}

		function arm_migration_link_payment_logs( $user_id ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_logs   = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$ARMemberLite->tbl_arm_payment_log}` WHERE arm_user_id = %d AND arm_subscription_id = 0 AND arm_plan_id > 0 ORDER BY arm_log_id ASC", $user_id ), ARRAY_A ); //phpcs:ignore --Reason table name
			if ( empty( $arm_logs ) ) 
			{
				return 0;
			}
			$arm_rows = $wpdb->get_results( $wpdb->prepare( "SELECT arm_subscription_id, arm_plan_id, arm_plan_start_date, arm_created_date, arm_subscription_source, arm_coupon_code FROM `{$ARMemberLite->tbl_arm_subscriptions}` WHERE arm_user_id = %d ORDER BY arm_subscription_id ASC", $user_id ), ARRAY_A ); //phpcs:ignore --Reason table name
			$arm_by_plan = array();
			foreach ( (array) $arm_rows as $arm_row ) 
			{
				$arm_row['arm_from'] = ! empty( $arm_row['arm_plan_start_date'] ) ? $arm_row['arm_plan_start_date'] : $arm_row['arm_created_date'];
				$arm_by_plan[ (int) $arm_row['arm_plan_id'] ][] = $arm_row;
			}
			foreach ( $arm_by_plan as $arm_plan_id => $arm_plan_rows ) 
			{
				usort( $arm_plan_rows, function ( $a, $b ) { return strcmp( (string) $a['arm_from'], (string) $b['arm_from'] ); } );
				$arm_by_plan[ $arm_plan_id ] = $arm_plan_rows;
			}

			$arm_link = array(); /* subscription id => log ids */
			foreach ( $arm_logs as $arm_log ) 
			{
				$arm_plan_id = (int) $arm_log['arm_plan_id'];
				if ( empty( $arm_by_plan[ $arm_plan_id ] ) ) 
				{
					$arm_new_id = $this->arm_migration_insert_from_log( $user_id, $arm_log );
					if ( $arm_new_id <= 0 ) 
					{
						continue;
					}
					$arm_by_plan[ $arm_plan_id ][] = array( 'arm_subscription_id' => $arm_new_id, 'arm_from' => $arm_log['arm_created_date'], 'arm_subscription_source' => 'migration', 'arm_coupon_code' => '' );
				}
				$arm_log_time = strtotime( $arm_log['arm_created_date'] ) + HOUR_IN_SECONDS;
				$arm_target   = $arm_by_plan[ $arm_plan_id ][0];
				foreach ( $arm_by_plan[ $arm_plan_id ] as $arm_row ) 
				{
					if ( strtotime( (string) $arm_row['arm_from'] ) <= $arm_log_time ) 
					{
						$arm_target = $arm_row;
					}
				}
				$arm_link[ (int) $arm_target['arm_subscription_id'] ][] = (int) $arm_log['arm_log_id'];
			}

			$arm_linked = 0;
			foreach ( $arm_link as $arm_subscription_id => $arm_log_ids ) 
			{
				$arm_in      = implode( ',', array_map( 'intval', $arm_log_ids ) );
				$arm_linked += (int) $wpdb->query( $wpdb->prepare( "UPDATE `{$ARMemberLite->tbl_arm_payment_log}` SET arm_subscription_id = %d WHERE arm_log_id IN ({$arm_in}) AND arm_subscription_id = 0", $arm_subscription_id ) ); //phpcs:ignore --Reason table name and int list
				/* Coupon of the first payment for rows made by the migration. */
				$this->arm_apply_transaction_coupon( $arm_subscription_id, min( $arm_log_ids ) );
			}
			return $arm_linked;
		}

		function arm_migration_link_entries( $user_id ) 
		{
			global $wpdb, $ARMemberLite;
			$arm_user   = get_userdata( (int) $user_id );
			$arm_email  = ( $arm_user && ! empty( $arm_user->user_email ) ) ? $arm_user->user_email : '';
			$arm_entries = $wpdb->get_results( $wpdb->prepare( "SELECT arm_entry_id, arm_plan_id, arm_created_date FROM `{$ARMemberLite->tbl_arm_entries}` WHERE arm_subscription_id = 0 AND arm_plan_id > 0 AND ( arm_user_id = %d OR ( ( arm_user_id IS NULL OR arm_user_id = 0 ) AND arm_entry_email = %s ) ) ORDER BY arm_entry_id ASC", $user_id, $arm_email ), ARRAY_A ); //phpcs:ignore --Reason table name
			if ( empty( $arm_entries ) ) 
			{
				return 0;
			}
			$arm_rows = $wpdb->get_results( $wpdb->prepare( "SELECT arm_subscription_id, arm_plan_id, arm_plan_start_date, arm_plan_end_date, arm_created_date, arm_updated_date, arm_plan_status FROM `{$ARMemberLite->tbl_arm_subscriptions}` WHERE arm_user_id = %d", $user_id ), ARRAY_A ); //phpcs:ignore --Reason table name
			$arm_linked = 0;
			foreach ( $arm_entries as $arm_entry ) 
			{
				$arm_time   = strtotime( $arm_entry['arm_created_date'] );
				$arm_target = 0;
				$arm_best   = null;
				foreach ( (array) $arm_rows as $arm_row ) 
				{
					if ( (int) $arm_row['arm_plan_id'] !== (int) $arm_entry['arm_plan_id'] ) 
					{
						continue;
					}
					$arm_start = strtotime( ! empty( $arm_row['arm_plan_start_date'] ) ? $arm_row['arm_plan_start_date'] : $arm_row['arm_created_date'] );
					if ( $arm_start >= $arm_time - HOUR_IN_SECONDS && $arm_start <= $arm_time + 7 * DAY_IN_SECONDS && ( null === $arm_best || $arm_start < $arm_best ) ) 
					{
						$arm_best   = $arm_start;
						$arm_target = (int) $arm_row['arm_subscription_id'];
					}
				}
				if ( ! $arm_target ) 
				{
					foreach ( (array) $arm_rows as $arm_row ) 
					{
						if ( (int) $arm_row['arm_plan_id'] !== (int) $arm_entry['arm_plan_id'] ) 
						{
							continue;
						}
						$arm_start = strtotime( ! empty( $arm_row['arm_plan_start_date'] ) ? $arm_row['arm_plan_start_date'] : $arm_row['arm_created_date'] );
						$arm_end   = ( (int) $arm_row['arm_plan_status'] > self::ARM_STATUS_FUTURE ) ? strtotime( ! empty( $arm_row['arm_plan_end_date'] ) ? $arm_row['arm_plan_end_date'] : $arm_row['arm_updated_date'] ) : PHP_INT_MAX;
						if ( $arm_start <= $arm_time && $arm_end >= $arm_time ) 
						{
							$arm_paid = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$ARMemberLite->tbl_arm_payment_log}` WHERE arm_subscription_id = %d AND arm_created_date >= %s AND arm_created_date <= %s", $arm_row['arm_subscription_id'], gmdate( 'Y-m-d H:i:s', $arm_time - HOUR_IN_SECONDS ), gmdate( 'Y-m-d H:i:s', $arm_time + DAY_IN_SECONDS ) ) ); //phpcs:ignore --Reason table name
							if ( $arm_paid > 0 ) 
							{
								$arm_target = (int) $arm_row['arm_subscription_id'];
								break;
							}
						}
					}
				}
				if ( $arm_target && $this->arm_link_entry_by_id( (int) $arm_entry['arm_entry_id'], $arm_target ) ) 
				{
					$arm_linked++;
				}
			}
			return $arm_linked;
		}

		function arm_migration_insert_from_log( $user_id, $arm_log ) 
		{
			$arm_status = (string) $arm_log['arm_transaction_status'];
			if ( 'bank_transfer' === $arm_log['arm_payment_gateway'] && in_array( $arm_status, array( '0', 'pending' ), true ) ) 
			{
				$arm_new_status = self::ARM_STATUS_PENDING;
			} 
			elseif ( in_array( $arm_status, array( '2', 'canceled', 'cancelled', 'failed' ), true ) ) 
			{
				$arm_new_status = self::ARM_STATUS_CANCELLED;
			} 
			else 
			{
				$arm_new_status = self::ARM_STATUS_EXPIRED;
			}
			global $wpdb, $ARMemberLite;
			$arm_plans_table = $ARMemberLite->tbl_arm_subscription_plans;
			$arm_plan_row    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$arm_plans_table}` WHERE arm_subscription_plan_id = %d", (int) $arm_log['arm_plan_id'] ), ARRAY_A ); //phpcs:ignore --Reason table name
			$arm_plan_data   = array(
				'arm_current_plan_detail' => ! empty( $arm_plan_row ) ? $arm_plan_row : array(),
				'arm_payment_cycle'       => isset( $arm_log['arm_payment_cycle'] ) ? (int) $arm_log['arm_payment_cycle'] : 0,
				'arm_payment_mode'        => isset( $arm_log['arm_payment_mode'] ) ? $arm_log['arm_payment_mode'] : '',
				'arm_user_gateway'        => isset( $arm_log['arm_payment_gateway'] ) ? $arm_log['arm_payment_gateway'] : '',
			);
			$arm_columns = $this->arm_prepare_subscription_columns( $user_id, (int) $arm_log['arm_plan_id'], $arm_plan_data, array() );
			$arm_columns['arm_user_id']             = (int) $user_id;
			$arm_columns['arm_plan_id']             = (int) $arm_log['arm_plan_id'];
			$arm_columns['arm_plan_status']         = $arm_new_status;
			$arm_columns['arm_subscription_source'] = 'migration';
			$arm_columns['arm_plan_start_date']     = $arm_log['arm_created_date'];
			$arm_columns['arm_created_date']        = $arm_log['arm_created_date'];
			$arm_columns['arm_updated_date']        = $arm_log['arm_created_date'];
			$arm_columns['arm_cancelled_date']      = ( self::ARM_STATUS_CANCELLED === $arm_new_status ) ? $arm_log['arm_created_date'] : null;
			$arm_columns['arm_ended_date']          = ( self::ARM_STATUS_PENDING === $arm_new_status ) ? null : $arm_log['arm_created_date'];
			if ( ! empty( $arm_log['arm_currency'] ) ) 
			{
				$arm_columns['arm_plan_currency'] = strtoupper( sanitize_text_field( $arm_log['arm_currency'] ) );
			}
			$wpdb->insert( $ARMemberLite->tbl_arm_subscriptions, $arm_columns ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$arm_subscription_id = (int) $wpdb->insert_id;
			if ( $arm_subscription_id > 0 && ! empty( $arm_plan_data['arm_current_plan_detail'] ) ) 
			{
				$this->arm_sync_subscription_meta( $arm_subscription_id, array( 'arm_current_plan_detail' => $arm_plan_data['arm_current_plan_detail'] ) );
			}
			return $arm_subscription_id;
		}

		function arm_migration_verify_user( $user_id, $arm_current_plans, $arm_lists ) 
		{
			$arm_mismatch = array();

			foreach ( array( 'arm_user_plan_ids' => 'plan_ids', 'arm_user_suspended_plan_ids' => 'suspended_ids', 'arm_user_future_plan_ids' => 'future_ids' ) as $arm_key => $arm_list_key ) 
			{
				$arm_table_ids = $this->arm_get_plan_meta_from_tables( $user_id, $arm_key, true );
				$arm_table_ids = is_array( $arm_table_ids ) ? array_map( 'intval', $arm_table_ids ) : array();
				$arm_meta_ids  = $arm_lists[ $arm_list_key ];
				sort( $arm_table_ids );
				sort( $arm_meta_ids );
				if ( $arm_table_ids !== $arm_meta_ids ) 
				{
					$arm_mismatch[] = $arm_key;
				}
			}

			/* Pay per post ids of current plans. */
			$arm_post_meta  = array();
			foreach ( $this->arm_get_raw_post_ids( $user_id ) as $arm_post_plan => $arm_post_id ) 
			{
				if ( in_array( (int) $arm_post_plan, array_map( 'intval', $arm_current_plans ), true ) && '' !== (string) $arm_post_id ) 
				{
					$arm_post_meta[ (int) $arm_post_plan ] = (string) $arm_post_id;
				}
			}
			$arm_post_table = $this->arm_get_plan_meta_from_tables( $user_id, 'arm_user_post_ids', true );
			$arm_post_table = is_array( $arm_post_table ) ? array_map( 'strval', $arm_post_table ) : array();
			ksort( $arm_post_meta );
			ksort( $arm_post_table );
			if ( $arm_post_meta !== $arm_post_table ) 
			{
				$arm_mismatch[] = 'arm_user_post_ids';
			}

			foreach ( $arm_current_plans as $arm_plan_id ) 
			{
				$arm_meta = get_user_meta( $user_id, 'arm_user_plan_' . $arm_plan_id, true );
				if ( is_string( $arm_meta ) ) 
				{
					$arm_meta = arm_maybe_unserialize( $arm_meta );
				}
				if ( empty( $arm_meta ) || ! is_array( $arm_meta ) ) 
				{
					continue;
				}
				$arm_table = $this->arm_get_plan_meta_from_tables( $user_id, 'arm_user_plan_' . $arm_plan_id, true );
				$arm_table = is_array( $arm_table ) ? $arm_table : array();
				if ( isset( $arm_meta['arm_current_plan_detail'] ) && is_array( $arm_meta['arm_current_plan_detail'] ) ) 
				{
					unset( $arm_meta['arm_current_plan_detail']['arm_subscription_plan_description'] );
				}
				if ( isset( $arm_table['arm_current_plan_detail'] ) && is_array( $arm_table['arm_current_plan_detail'] ) ) 
				{
					unset( $arm_table['arm_current_plan_detail']['arm_subscription_plan_description'] );
				}
				$arm_filled = isset( $this->arm_migration_filled_keys[ (int) $user_id ][ (int) $arm_plan_id ] ) ? $this->arm_migration_filled_keys[ (int) $user_id ][ (int) $arm_plan_id ] : array();
				if ( ! empty( $arm_filled['arm_user_gateway'] ) && ! empty( $arm_table['arm_user_gateway'] ) ) 
				{
					$arm_filled[ 'arm_' . sanitize_key( $arm_table['arm_user_gateway'] ) ] = true;
				}
				foreach ( array_unique( array_merge( array_keys( $arm_meta ), array_keys( $arm_table ) ) ) as $arm_key ) 
				{
					if ( ! empty( $arm_filled[ $arm_key ] ) ) 
					{
						continue; /* Empty in usermeta, filled from the history on purpose. */
					}
					$arm_a = isset( $arm_meta[ $arm_key ] ) ? $arm_meta[ $arm_key ] : null;
					$arm_b = isset( $arm_table[ $arm_key ] ) ? $arm_table[ $arm_key ] : null;
					if ( empty( $arm_a ) && empty( $arm_b ) ) 
					{
						continue;
					}
					if ( wp_json_encode( $this->arm_migration_normalize( $arm_a ) ) !== wp_json_encode( $this->arm_migration_normalize( $arm_b ) ) ) 
					{
						$arm_mismatch[] = 'arm_user_plan_' . $arm_plan_id . ':' . $arm_key;
					}
				}
			}

			if ( ! empty( $arm_mismatch ) ) 
			{
				$this->arm_migration_add_user_to_option( 'arm_subscription_migration_mismatch_users', $user_id, implode( ', ', array_slice( $arm_mismatch, 0, 10 ) ) );
				do_action( 'arm_general_log_entry', 'subscription_migration', 'Subscription migration mismatch user ' . $user_id, 'armember', $arm_mismatch );
			}
			return count( $arm_mismatch );
		}

		/* Same value written in different types (1 / '1', array order kept). */
		function arm_migration_normalize( $arm_value ) 
		{
			if ( is_array( $arm_value ) ) 
			{
				$arm_out = array();
				foreach ( $arm_value as $arm_k => $arm_v ) 
				{
					$arm_out[ $arm_k ] = $this->arm_migration_normalize( $arm_v );
				}
				return $arm_out;
			}
			if ( is_bool( $arm_value ) ) 
			{
				return $arm_value ? '1' : '';
			}
			return ( null === $arm_value ) ? '' : (string) $arm_value;
		}

		function arm_migration_add_user_to_option( $arm_option, $user_id, $arm_reason ) 
		{
			$arm_users = get_option( $arm_option, array() );
			$arm_users = is_array( $arm_users ) ? $arm_users : array();
			if ( count( $arm_users ) < 1000 || isset( $arm_users[ $user_id ] ) ) 
			{
				$arm_users[ (int) $user_id ] = substr( (string) $arm_reason, 0, 500 );
				update_option( $arm_option, $arm_users, false );
			}
		}

		function arm_remigrate_users( $arm_user_ids = array() ) 
		{
			if ( empty( $arm_user_ids ) ) 
			{
				$arm_user_ids = array_merge( array_keys( (array) get_option( 'arm_subscription_migration_failed_users', array() ) ), array_keys( (array) get_option( 'arm_subscription_migration_mismatch_users', array() ) ) );
				delete_option( 'arm_subscription_migration_failed_users' );
				delete_option( 'arm_subscription_migration_mismatch_users' );
			}
			$arm_done = array();
			foreach ( array_unique( array_map( 'intval', $arm_user_ids ) ) as $user_id ) 
			{
				$arm_done[ $user_id ] = $this->arm_migrate_user( $user_id );
			}
			return $arm_done;
		}

		function arm_timestamp_to_datetime( $arm_timestamp ) 
		{
			if ( '' === $arm_timestamp || null === $arm_timestamp || false === $arm_timestamp ) 
			{
				return null;
			}
			if ( ! is_numeric( $arm_timestamp ) ) 
			{
				$arm_timestamp = strtotime( $arm_timestamp . ' UTC' );
			}
			$arm_timestamp = (int) $arm_timestamp;
			return ( $arm_timestamp > 0 ) ? gmdate( 'Y-m-d H:i:s', $arm_timestamp ) : null;
		}

		function arm_datetime_to_timestamp( $arm_datetime ) 
		{
			if ( empty( $arm_datetime ) || '1970-01-01 00:00:00' === $arm_datetime ) 
			{
				return '';
			}
			$arm_timestamp = strtotime( $arm_datetime . ' UTC' );
			return ( false === $arm_timestamp ) ? '' : $arm_timestamp;
		}
	}
}

global $arm_member_subscriptions;
$arm_member_subscriptions = new ARM_member_subscriptions_Lite();
