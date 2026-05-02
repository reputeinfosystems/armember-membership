<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles exporting ARMember members to CSV.
 */
class ARM_CSV_Exporter {

	/**
	 * Return all user-created (custom) ARMember form fields as meta_key => label.
	 * Skips structural/non-data field types.
	 *
	 * @return array
	 */
	public static function get_arm_custom_fields() {
		$skip_types = array(
			'section', 'html', 'hidden', 'submit', 'social_fields',
			'avatar', 'profile_cover', 'file', 'rememberme',
			'repeat_pass', 'repeat_email', 'info',
		);

		$preset = maybe_unserialize( get_option( 'arm_preset_form_fields', '' ) );
		if ( ! is_array( $preset ) ) {
			return array();
		}

		$custom = array();
		$other  = isset( $preset['other'] ) ? $preset['other'] : array();
		foreach ( $other as $meta_key => $field ) {
			$type = isset( $field['type'] ) ? $field['type'] : '';
			if ( in_array( $type, $skip_types, true ) ) {
				continue;
			}
			$label            = ( isset( $field['label'] ) && $field['label'] !== '' ) ? $field['label'] : $meta_key;
			$custom[ $meta_key ] = $label;
		}

		return $custom;
	}

	/**
	 * Run export: query members, apply filters, stream CSV to browser.
	 *
	 * @param array $filters {
	 *   @type int[]   $plan_ids     Filter by plan IDs (empty = all plans).
	 *   @type string  $status       Filter by primary status (empty = all).
	 *   @type string  $date_from    Registration date lower bound (Y-m-d).
	 *   @type string  $date_to      Registration date upper bound (Y-m-d).
	 *   @type string[] $extra_fields Additional user-meta keys to include.
	 * }
	 */
	public function export( array $filters = array() ) {
		global $wpdb, $ARMemberLite;

		$users = $this->get_users( $filters );

		if ( empty( $users ) ) {
			/* Return an empty CSV rather than crashing. */
			$this->stream_csv( array(), 'arm-members-export.csv' );
			return;
		}

		/*
		 * Merge explicitly requested extra_fields with every custom ARMember
		 * form field so they are always present in the export.
		 */
		$extra_fields  = isset( $filters['extra_fields'] ) ? (array) $filters['extra_fields'] : array();
		$custom_fields = array_keys( self::get_arm_custom_fields() );
		$extra_fields  = array_unique( array_merge( $extra_fields, $custom_fields ) );

		$rows = array();
		foreach ( $users as $user_obj ) {
			$user_id  = (int) $user_obj->ID;
			$userdata = get_userdata( $user_id );
			if ( ! $userdata ) {
				continue;
			}

			$plan_ids  = get_user_meta( $user_id, 'arm_user_plan_ids', true );
			$plan_names = array();
			if ( ! empty( $plan_ids ) && is_array( $plan_ids ) ) {
				foreach ( $plan_ids as $pid ) {
					$plan_names[] = $this->get_plan_name( (int) $pid );
				}
			}

			$row = array(
				'ID'               => $user_id,
				'username'         => $userdata->user_login,
				'email'            => $userdata->user_email,
				'first_name'       => $userdata->first_name,
				'last_name'        => $userdata->last_name,
				'display_name'     => $userdata->display_name,
				'role'             => implode( '|', (array) $userdata->roles ),
				'registered'       => $userdata->user_registered,
				'status'           => $this->get_status_label( $user_id ),
				'subscription_plan'=> implode( '|', $plan_names ),
			);

			foreach ( $extra_fields as $meta_key ) {
				if ( array_key_exists( $meta_key, $row ) ) {
					continue;
				}
				$value = get_user_meta( $user_id, $meta_key, true );
				if ( is_array( $value ) ) {
					$value = implode( '|', $value );
				}
				$row[ $meta_key ] = $value;
			}

			$rows[] = $row;
		}

		$filename = 'arm-members-export-' . gmdate( 'Y-m-d' ) . '.csv';
		$this->stream_csv( $rows, $filename );
	}

	/** -----------------------------------------------------------------------
	 * Query helpers
	 * ---------------------------------------------------------------------- */

	private function get_users( array $filters ) {
		global $wpdb, $ARMemberLite;

		$where = 'WHERE 1=1';

		/* Date range */
		$date_from = isset( $filters['date_from'] ) ? $filters['date_from'] : '';
		$date_to   = isset( $filters['date_to'] ) ? $filters['date_to'] : '';

		if ( $date_from ) {
			$where .= $wpdb->prepare( ' AND u.user_registered >= %s', $date_from . ' 00:00:00' );
		}
		if ( $date_to ) {
			$where .= $wpdb->prepare( ' AND u.user_registered <= %s', $date_to . ' 23:59:59' );
		}

		/* Primary status */
		$status = isset( $filters['status'] ) ? $filters['status'] : '';
		if ( $status !== '' ) {
			$tbl_members = isset( $ARMemberLite ) ? $ARMemberLite->tbl_arm_members : $wpdb->prefix . 'arm_members';
			$where .= $wpdb->prepare(
				' AND u.ID IN (SELECT arm_user_id FROM `' . $tbl_members . '` WHERE arm_primary_status = %s)', // phpcs:ignore
				$status
			);
		}

		/* Exclude admins */
		$admin_ids = $this->get_admin_ids();
		if ( ! empty( $admin_ids ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $admin_ids ), '%d' ) );
			$where       .= $wpdb->prepare( ' AND u.ID NOT IN (' . $placeholders . ')', $admin_ids ); // phpcs:ignore
		}

		$users = $wpdb->get_results( "SELECT u.ID FROM {$wpdb->users} u {$where} ORDER BY u.ID ASC" ); // phpcs:ignore

		/* Filter by plan IDs (post-query, uses user-meta) */
		$plan_ids = isset( $filters['plan_ids'] ) ? array_filter( (array) $filters['plan_ids'] ) : array();
		if ( ! empty( $plan_ids ) && ! empty( $users ) ) {
			foreach ( $users as $k => $u ) {
				$user_plans = get_user_meta( (int) $u->ID, 'arm_user_plan_ids', true );
				if ( empty( $user_plans ) || ! is_array( $user_plans )
					|| empty( array_intersect( array_map( 'intval', $user_plans ), $plan_ids ) )
				) {
					unset( $users[ $k ] );
				}
			}
		}

		return array_values( $users );
	}

	private function get_admin_ids() {
		global $wpdb;

		$capability_key = $wpdb->get_blog_prefix() . 'capabilities';
		$results        = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value LIKE %s",
				$capability_key,
				'%administrator%'
			)
		);

		return $results ? wp_list_pluck( $results, 'user_id' ) : array();
	}

	private function get_plan_name( $plan_id ) {
		global $wpdb, $ARMemberLite;

		if ( isset( $ARMemberLite ) ) {
			$tbl = $ARMemberLite->tbl_arm_subscription_plans;
		} else {
			$tbl = $wpdb->prefix . 'arm_subscription_plans';
		}

		$name = $wpdb->get_var(
			$wpdb->prepare( "SELECT arm_subscription_plan_name FROM `{$tbl}` WHERE arm_subscription_plan_id = %d", $plan_id ) // phpcs:ignore
		);

		return $name ? $name : 'Plan #' . $plan_id;
	}

	private function get_status_label( $user_id ) {
		$labels = array(
			'0' => 'Inactive',
			'1' => 'Active',
			'2' => 'Pending',
			'3' => 'Expired',
			'4' => 'Banned',
		);

		$status = get_user_meta( $user_id, 'arm_user_primary_status', true );
		if ( $status === '' || $status === false ) {
			global $wpdb, $ARMemberLite;
			$tbl = isset( $ARMemberLite ) ? $ARMemberLite->tbl_arm_members : $wpdb->prefix . 'arm_members';
			$status = $wpdb->get_var(
				$wpdb->prepare( "SELECT arm_primary_status FROM `{$tbl}` WHERE arm_user_id = %d", $user_id ) // phpcs:ignore
			);
		}

		return isset( $labels[ (string) $status ] ) ? $labels[ (string) $status ] : 'Inactive';
	}

	/** -----------------------------------------------------------------------
	 * CSV streaming
	 * ---------------------------------------------------------------------- */

	private function stream_csv( array $rows, $filename ) {
		if ( ob_get_length() ) {
			ob_clean();
		}

		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$df = fopen( 'php://output', 'w' );

		if ( ! empty( $rows ) ) {
			fputcsv( $df, array_keys( $rows[0] ) );
			foreach ( $rows as $row ) {
				fputcsv( $df, $row );
			}
		} else {
			fputcsv( $df, array( 'ID', 'username', 'email', 'first_name', 'last_name', 'display_name', 'role', 'registered', 'status', 'subscription_plan' ) );
		}

		fclose( $df ); // phpcs:ignore
	}
}
