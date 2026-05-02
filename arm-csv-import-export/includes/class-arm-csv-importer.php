<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles importing members from a CSV file into ARMember Lite.
 */
class ARM_CSV_Importer {

	/** Maximum file size: 5 MB */
	const MAX_FILE_SIZE = 5242880;

	/** Columns that map directly to WP_User fields */
	private $core_fields = array(
		'username', 'user_login',
		'email', 'user_email',
		'first_name',
		'last_name',
		'display_name',
		'password', 'user_pass',
		'role',
		'website', 'user_url',
		'description',
		'nickname',
	);

	/**
	 * Return all user-created (custom) ARMember form fields as meta_key => label.
	 * Mirrors ARM_CSV_Exporter::get_arm_custom_fields().
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

	/** -----------------------------------------------------------------------
	 * Step 1 – Parse uploaded file and return preview data.
	 *
	 * @param  array $file  $_FILES element.
	 * @return array|WP_Error
	 * ---------------------------------------------------------------------- */
	public function preview( array $file ) {
		$error = $this->validate_upload( $file );
		if ( is_wp_error( $error ) ) {
			return $error;
		}

		$rows = $this->parse_csv( $file['tmp_name'] );
		if ( is_wp_error( $rows ) ) {
			return $rows;
		}

		if ( count( $rows ) < 2 ) {
			return new WP_Error( 'empty_csv', __( 'The CSV file has no data rows.', 'arm-csv-import-export' ) );
		}

		$headers = array_shift( $rows );
		$headers = array_map( 'trim', $headers );

		$preview_rows = array_slice( $rows, 0, 5 );

		/*
		 * Merge core WP fields with all user-created ARMember custom fields
		 * so the JS column-mapping dropdown offers them as mapping targets.
		 */
		$custom_fields      = self::get_arm_custom_fields(); // meta_key => label
		$all_mappable_fields = array_merge( $this->core_fields, array_keys( $custom_fields ) );

		return array(
			'headers'       => $headers,
			'preview_rows'  => $preview_rows,
			'total'         => count( $rows ),
			'all_rows'      => $rows,
			'core_fields'   => $all_mappable_fields,
			'custom_fields' => $custom_fields, // meta_key => label, for JS labelling
		);
	}

	/** -----------------------------------------------------------------------
	 * Step 2 – Create / update users from parsed rows.
	 *
	 * @param  array  $rows            All data rows (no header).
	 * @param  array  $column_map      Maps column index (string) → field name.
	 * @param  int    $plan_id         Optional plan to assign imported users.
	 * @param  bool   $send_notify     Whether to send WordPress new-user email.
	 * @param  bool   $update_existing Update existing users instead of skipping.
	 * @return array|WP_Error
	 * ---------------------------------------------------------------------- */
	public function process( array $rows, array $column_map, $plan_id = 0, $send_notify = false, $update_existing = false ) {
		$created  = 0;
		$updated  = 0;
		$skipped  = 0;
		$errors   = array();

		foreach ( $rows as $index => $row ) {
			$row_num  = $index + 2; // 1-based, +1 for header
			$userdata = $this->map_row( $row, $column_map );

			if ( empty( $userdata['user_login'] ) && empty( $userdata['user_email'] ) ) {
				$errors[] = sprintf(
					/* translators: %d row number */
					__( 'Row %d: missing username and email — skipped.', 'arm-csv-import-export' ),
					$row_num
				);
				$skipped++;
				continue;
			}

			/* Derive missing login / email */
			if ( empty( $userdata['user_login'] ) ) {
				$userdata['user_login'] = sanitize_user( strtolower( explode( '@', $userdata['user_email'] )[0] ), true );
			}
			if ( empty( $userdata['user_email'] ) ) {
				/* No email supplied; skip */
				$errors[] = sprintf(
					__( 'Row %d: missing email — skipped.', 'arm-csv-import-export' ),
					$row_num
				);
				$skipped++;
				continue;
			}

			$existing_id = $this->find_existing_user( $userdata );

			if ( $existing_id ) {
				if ( ! $update_existing ) {
					$skipped++;
					continue;
				}
				$userdata['ID'] = $existing_id;
				$result         = wp_update_user( $userdata );
				if ( is_wp_error( $result ) ) {
					$errors[] = sprintf(
						__( 'Row %d (%s): %s', 'arm-csv-import-export' ),
						$row_num,
						esc_html( $userdata['user_email'] ),
						$result->get_error_message()
					);
					$skipped++;
					continue;
				}
				$user_id = $result;
				$updated++;
			} else {
				if ( empty( $userdata['user_pass'] ) ) {
					$userdata['user_pass'] = wp_generate_password( 12, true, true );
				}

				if ( ! isset( $userdata['role'] ) || empty( $userdata['role'] ) ) {
					$userdata['role'] = get_option( 'default_role', 'subscriber' );
				}

				$userdata['user_registered'] = current_time( 'mysql' );

				if ( $send_notify ) {
					add_filter( 'send_password_change_email', '__return_false' );
				}

				$result = wp_insert_user( $userdata );

				if ( is_wp_error( $result ) ) {
					$errors[] = sprintf(
						__( 'Row %d (%s): %s', 'arm-csv-import-export' ),
						$row_num,
						esc_html( $userdata['user_email'] ),
						$result->get_error_message()
					);
					$skipped++;
					continue;
				}

				$user_id = $result;

				if ( $send_notify ) {
					wp_send_new_user_notifications( $user_id, 'user' );
				}

				$created++;
			}

			/* Write extra meta (non-core fields) */
			$this->save_extra_meta( $user_id, $row, $column_map );

			/* Assign ARMember plan */
			if ( $plan_id > 0 ) {
				$this->assign_plan( $user_id, $plan_id );
			}

			/* Fire ARMember hook so other add-ons know about the import */
			do_action( 'arm_after_user_import', $user_id );
		}

		return array(
			'created' => $created,
			'updated' => $updated,
			'skipped' => $skipped,
			'errors'  => $errors,
		);
	}

	/** -----------------------------------------------------------------------
	 * Helpers
	 * ---------------------------------------------------------------------- */

	/**
	 * Map a CSV row array to a WP insert/update userdata array.
	 */
	private function map_row( array $row, array $column_map ) {
		$field_aliases = array(
			'username'     => 'user_login',
			'login'        => 'user_login',
			'email'        => 'user_email',
			'password'     => 'user_pass',
			'pass'         => 'user_pass',
			'website'      => 'user_url',
			'url'          => 'user_url',
			'bio'          => 'description',
			'display'      => 'display_name',
		);

		$userdata = array();
		foreach ( $column_map as $col_index => $field_name ) {
			$field_name = trim( $field_name );
			if ( $field_name === '' || $field_name === 'skip' ) {
				continue;
			}
			if ( isset( $field_aliases[ $field_name ] ) ) {
				$field_name = $field_aliases[ $field_name ];
			}
			$value = isset( $row[ (int) $col_index ] ) ? trim( $row[ (int) $col_index ] ) : '';

			/* Sanitize based on field */
			switch ( $field_name ) {
				case 'user_login':
					$value = sanitize_user( $value, true );
					break;
				case 'user_email':
					$value = sanitize_email( $value );
					break;
				case 'user_url':
					$value = esc_url_raw( $value );
					break;
				case 'user_pass':
					/* Keep as-is; wp_insert_user will hash it */
					break;
				default:
					$value = sanitize_text_field( $value );
			}

			$userdata[ $field_name ] = $value;
		}

		return $userdata;
	}

	/**
	 * Save non-core columns as user meta.
	 */
	private function save_extra_meta( $user_id, array $row, array $column_map ) {
		$skip = array(
			'user_login', 'username', 'login',
			'user_email', 'email',
			'user_pass', 'password', 'pass',
			'user_url', 'website', 'url',
			'display_name', 'display',
			'description', 'bio',
			'first_name', 'last_name', 'nickname',
			'role',
		);

		foreach ( $column_map as $col_index => $field_name ) {
			$field_name = trim( $field_name );
			if ( $field_name === '' || $field_name === 'skip' || in_array( $field_name, $skip, true ) ) {
				continue;
			}
			$value = isset( $row[ (int) $col_index ] ) ? sanitize_text_field( trim( $row[ (int) $col_index ] ) ) : '';
			update_user_meta( $user_id, $field_name, $value );
		}
	}

	/**
	 * Look up an existing user by login or email.
	 */
	private function find_existing_user( array $userdata ) {
		if ( ! empty( $userdata['user_email'] ) ) {
			$user = get_user_by( 'email', $userdata['user_email'] );
			if ( $user ) {
				return $user->ID;
			}
		}
		if ( ! empty( $userdata['user_login'] ) ) {
			$user = get_user_by( 'login', $userdata['user_login'] );
			if ( $user ) {
				return $user->ID;
			}
		}
		return false;
	}

	/**
	 * Assign an ARMember subscription plan to a user.
	 */
	private function assign_plan( $user_id, $plan_id ) {
		global $wpdb, $ARMemberLite;

		$current_plans = get_user_meta( $user_id, 'arm_user_plan_ids', true );
		if ( ! is_array( $current_plans ) ) {
			$current_plans = array();
		}

		if ( ! in_array( $plan_id, $current_plans, true ) ) {
			$current_plans[] = $plan_id;
			update_user_meta( $user_id, 'arm_user_plan_ids', $current_plans );
		}

		/* Create default plan data if it does not already exist */
		$plan_meta_key  = 'arm_user_plan_' . $plan_id;
		$existing_data  = get_user_meta( $user_id, $plan_meta_key, true );
		if ( empty( $existing_data ) ) {
			$plan_data = array(
				'arm_current_plan_is_paid' => 0,
				'arm_payment_mode'         => 'free',
				'arm_subscription_status'  => 'active',
				'arm_is_recurring'         => 0,
				'arm_start_date'           => current_time( 'mysql' ),
				'arm_expire_date'          => '0000-00-00 00:00:00',
			);
			update_user_meta( $user_id, $plan_meta_key, $plan_data );
		}

		/* Sync to arm_members table if available */
		if ( isset( $ARMemberLite ) && isset( $ARMemberLite->tbl_arm_members ) ) {
			$tbl = $ARMemberLite->tbl_arm_members;
			$existing_member = $wpdb->get_var(
				$wpdb->prepare( "SELECT arm_user_id FROM `{$tbl}` WHERE arm_user_id = %d", $user_id ) // phpcs:ignore
			);
			$wp_user = get_userdata( $user_id );
			if ( ! $wp_user ) {
				return;
			}

			if ( $existing_member ) {
				$wpdb->update(
					$tbl,
					array( 'arm_user_plan_ids' => maybe_serialize( $current_plans ) ),
					array( 'arm_user_id' => $user_id ),
					array( '%s' ),
					array( '%d' )
				);
			} else {
				$wpdb->insert(
					$tbl,
					array(
						'arm_user_id'        => $user_id,
						'arm_user_login'     => $wp_user->user_login,
						'arm_user_email'     => $wp_user->user_email,
						'arm_user_registered'=> $wp_user->user_registered,
						'arm_display_name'   => $wp_user->display_name,
						'arm_primary_status' => 1,
						'arm_user_plan_ids'  => maybe_serialize( $current_plans ),
					),
					array( '%d', '%s', '%s', '%s', '%s', '%d', '%s' )
				);
			}
		}

		do_action( 'arm_after_user_plan_change_by_admin', $user_id, $plan_id );
	}

	/** -----------------------------------------------------------------------
	 * File helpers
	 * ---------------------------------------------------------------------- */

	private function validate_upload( array $file ) {
		if ( $file['size'] > self::MAX_FILE_SIZE ) {
			return new WP_Error( 'file_too_large', __( 'File exceeds the 5 MB limit.', 'arm-csv-import-export' ) );
		}

		$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		if ( $ext !== 'csv' ) {
			return new WP_Error( 'invalid_type', __( 'Only CSV files are accepted.', 'arm-csv-import-export' ) );
		}

		return true;
	}

	private function parse_csv( $filepath ) {
		if ( ! file_exists( $filepath ) ) {
			return new WP_Error( 'file_missing', __( 'Uploaded file could not be found.', 'arm-csv-import-export' ) );
		}

		$rows = array();
		$handle = fopen( $filepath, 'r' ); // phpcs:ignore
		if ( ! $handle ) {
			return new WP_Error( 'file_open', __( 'Could not open CSV file.', 'arm-csv-import-export' ) );
		}

		/* Strip UTF-8 BOM if present */
		$bom  = fread( $handle, 3 );
		if ( $bom !== "\xef\xbb\xbf" ) {
			rewind( $handle );
		}

		while ( ( $row = fgetcsv( $handle, 0, ',' ) ) !== false ) { // phpcs:ignore
			$rows[] = $row;
		}
		fclose( $handle ); // phpcs:ignore

		return $rows;
	}
}
