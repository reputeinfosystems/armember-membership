<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core logic for auto-generating sequential membership usernames.
 *
 * Username format: {PREFIX}{GLOBAL_SEQUENCE_NUMBER}
 * e.g. LM1036, AM1042, FNM1043
 *
 * A single global counter is shared across all membership types so the
 * number part is always strictly ascending regardless of which plan is
 * being registered.
 */
class ARM_Username_Generator {

	/** WordPress option keys */
	const OPT_LAST_NUMBER  = 'arm_ugen_last_number';
	const OPT_PLAN_PREFIXES = 'arm_ugen_plan_prefixes';

	public function __construct() {
		/* Admin */
		add_action( 'admin_menu',            array( $this, 'add_admin_menu' ), 35 );
		add_action( 'admin_post_arm_ugen_save_settings', array( $this, 'save_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		/* After ARMember creates a new user via a registration form */
		add_action( 'arm_after_add_new_user', array( $this, 'assign_username' ), 5, 2 );

		/* Frontend: enqueue preview script on public pages */
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

		/* AJAX: return the next preview username for a given plan */
		add_action( 'wp_ajax_arm_ugen_preview',        array( $this, 'ajax_preview' ) );
		add_action( 'wp_ajax_nopriv_arm_ugen_preview', array( $this, 'ajax_preview' ) );
	}

	/* =========================================================================
	 * Admin Menu
	 * ======================================================================= */

	public function add_admin_menu() {
		add_submenu_page(
			'arm_manage_members',
			__( 'Username Generator', 'arm-username-generator' ),
			__( 'Username Generator', 'arm-username-generator' ),
			'manage_options',
			'arm_username_generator',
			array( $this, 'render_settings_page' )
		);
	}

	public function render_settings_page() {
		require ARM_UGEN_DIR . 'admin/views/page-settings.php';
	}

	/* =========================================================================
	 * Settings: Save
	 * ======================================================================= */

	public function save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'arm-username-generator' ) );
		}
		check_admin_referer( 'arm_ugen_save_settings' );

		/* Last allotted number */
		$last_number = isset( $_POST['arm_ugen_last_number'] )
			? absint( $_POST['arm_ugen_last_number'] )
			: 0;
		update_option( self::OPT_LAST_NUMBER, $last_number );

		/* Plan → prefix map */
		$raw_map = isset( $_POST['arm_ugen_prefixes'] ) && is_array( $_POST['arm_ugen_prefixes'] )
			? wp_unslash( $_POST['arm_ugen_prefixes'] )
			: array();

		$clean_map = array();
		foreach ( $raw_map as $plan_id => $prefix ) {
			$plan_id = absint( $plan_id );
			$prefix  = strtoupper( sanitize_text_field( trim( $prefix ) ) );
			if ( $plan_id > 0 && $prefix !== '' ) {
				$clean_map[ $plan_id ] = $prefix;
			}
		}
		update_option( self::OPT_PLAN_PREFIXES, $clean_map );

		wp_redirect(
			add_query_arg(
				array( 'page' => 'arm_username_generator', 'updated' => '1' ),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/* =========================================================================
	 * Core: Assign username after registration
	 * ======================================================================= */

	/**
	 * Hooked into arm_after_add_new_user.
	 * Generates the next sequential username for the plan the user signed up for
	 * and writes it directly to the database (atomic).
	 *
	 * @param int   $user_id     Newly created user ID.
	 * @param array $posted_data Form POST data including subscription_plan.
	 */
	public function assign_username( $user_id, $posted_data ) {
		$user_id = absint( $user_id );
		if ( $user_id <= 0 ) {
			return;
		}

		/* Determine the plan ID from posted data */
		$plan_id = 0;
		if ( ! empty( $posted_data['subscription_plan'] ) ) {
			$plan_id = absint( $posted_data['subscription_plan'] );
		} elseif ( ! empty( $posted_data['_subscription_plan'] ) ) {
			$plan_id = absint( $posted_data['_subscription_plan'] );
		}

		if ( $plan_id <= 0 ) {
			return;
		}

		$prefix = $this->get_prefix_for_plan( $plan_id );
		if ( $prefix === '' ) {
			return; // Plan not configured for auto-username — leave as-is.
		}

		$new_username = $this->generate_next_username( $prefix );
		$this->update_user_login( $user_id, $new_username );
	}

	/* =========================================================================
	 * Core: Username generation (atomic counter)
	 * ======================================================================= */

	/**
	 * Atomically increment the global counter and return the new username.
	 * Uses a single UPDATE statement so concurrent registrations never collide.
	 *
	 * @param  string $prefix e.g. 'LM', 'AM', 'FNM'
	 * @return string         e.g. 'LM1042'
	 */
	private function generate_next_username( $prefix ) {
		global $wpdb;

		/*
		 * Single atomic SQL increment — no read-modify-write race condition.
		 * CAST ensures the stored string is treated as an integer before +1.
		 */
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->options}
				 SET    option_value = CAST(option_value AS UNSIGNED) + 1
				 WHERE  option_name  = %s",
				self::OPT_LAST_NUMBER
			)
		);

		/* Bust the object cache so get_option reads the fresh DB value */
		wp_cache_delete( self::OPT_LAST_NUMBER, 'options' );
		$number = (int) get_option( self::OPT_LAST_NUMBER, 1 );

		return $prefix . $number;
	}

	/**
	 * Preview the next username for a plan WITHOUT advancing the counter.
	 *
	 * @param  int    $plan_id
	 * @return string|false  Username string, or false if plan not configured.
	 */
	public function peek_next_username( $plan_id ) {
		$prefix = $this->get_prefix_for_plan( (int) $plan_id );
		if ( $prefix === '' ) {
			return false;
		}
		$number = (int) get_option( self::OPT_LAST_NUMBER, 0 );
		return $prefix . ( $number + 1 );
	}

	/* =========================================================================
	 * Core: DB helpers
	 * ======================================================================= */

	/**
	 * Return the configured prefix for a plan, or '' if not configured.
	 */
	public function get_prefix_for_plan( $plan_id ) {
		$map = get_option( self::OPT_PLAN_PREFIXES, array() );
		return isset( $map[ $plan_id ] ) ? (string) $map[ $plan_id ] : '';
	}

	/**
	 * Write a new user_login directly to wp_users and clear all related caches.
	 * wp_update_user() intentionally prevents login changes in some WP versions,
	 * so we bypass it with a direct query.
	 *
	 * @param int    $user_id
	 * @param string $new_login
	 */
	private function update_user_login( $user_id, $new_login ) {
		global $wpdb;

		$wpdb->update(
			$wpdb->users,
			array( 'user_login' => $new_login ),
			array( 'ID'         => $user_id ),
			array( '%s' ),
			array( '%d' )
		);

		/* Clear all user caches so subsequent get_userdata() calls are fresh */
		clean_user_cache( $user_id );
	}

	/* =========================================================================
	 * Admin Assets
	 * ======================================================================= */

	public function enqueue_admin_assets( $hook ) {
		if ( strpos( $hook, 'arm_username_generator' ) === false ) {
			return;
		}
		wp_enqueue_style(
			'arm-ugen-admin',
			ARM_UGEN_URL . 'admin/css/admin.css',
			array(),
			ARM_UGEN_VERSION
		);
		wp_enqueue_script(
			'arm-ugen-admin',
			ARM_UGEN_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			ARM_UGEN_VERSION,
			true
		);
	}

	/* =========================================================================
	 * Frontend Assets
	 * ======================================================================= */

	public function enqueue_frontend_assets() {
		/* Only load when the page likely contains an ARMember form */
		if ( ! $this->page_has_arm_form() ) {
			return;
		}

		wp_enqueue_style(
			'arm-ugen-frontend',
			ARM_UGEN_URL . 'admin/css/frontend.css',
			array(),
			ARM_UGEN_VERSION
		);

		wp_enqueue_script(
			'arm-ugen-frontend',
			ARM_UGEN_URL . 'admin/js/frontend.js',
			array( 'jquery' ),
			ARM_UGEN_VERSION,
			true
		);

		/* Pass plan→prefix map + previews to JS */
		$plan_map  = get_option( self::OPT_PLAN_PREFIXES, array() );
		$previews  = array();
		foreach ( $plan_map as $pid => $prefix ) {
			$previews[ $pid ] = $this->peek_next_username( (int) $pid );
		}

		wp_localize_script(
			'arm-ugen-frontend',
			'armUgen',
			array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( 'arm_ugen_preview' ),
				'planPreviews'  => $previews,   // plan_id => "LM1042"
				'planPrefixes'  => $plan_map,   // plan_id => "LM"
				'readonlyMsg'   => __( 'This username is auto-generated and cannot be changed.', 'arm-username-generator' ),
			)
		);
	}

	/**
	 * Detect if the current page's post content contains an ARMember shortcode.
	 */
	private function page_has_arm_form() {
		global $post;
		if ( empty( $post->post_content ) ) {
			return false;
		}
		return (
			has_shortcode( $post->post_content, 'arm_form' )          ||
			has_shortcode( $post->post_content, 'arm_member_panel' )  ||
			strpos( $post->post_content, '[arm_' ) !== false
		);
	}

	/* =========================================================================
	 * AJAX: Preview username
	 * ======================================================================= */

	public function ajax_preview() {
		check_ajax_referer( 'arm_ugen_preview', 'nonce' );

		$plan_id  = isset( $_POST['plan_id'] ) ? absint( $_POST['plan_id'] ) : 0;
		$username = $plan_id ? $this->peek_next_username( $plan_id ) : false;

		if ( $username === false ) {
			wp_send_json_error( array( 'message' => 'No prefix configured for this plan.' ) );
		}

		wp_send_json_success( array( 'username' => $username ) );
	}

	/* =========================================================================
	 * Static helpers used by the settings view
	 * ======================================================================= */

	/**
	 * Return all ARMember subscription plans as id => name array.
	 *
	 * @return array
	 */
	public static function get_all_plans() {
		global $wpdb, $ARMemberLite;
		$tbl = isset( $ARMemberLite->tbl_arm_subscription_plans )
			? $ARMemberLite->tbl_arm_subscription_plans
			: $wpdb->prefix . 'arm_subscription_plans';

		$rows = $wpdb->get_results(
			"SELECT arm_subscription_plan_id, arm_subscription_plan_name FROM `{$tbl}` ORDER BY arm_subscription_plan_name ASC" // phpcs:ignore
		);

		$plans = array();
		if ( $rows ) {
			foreach ( $rows as $row ) {
				$plans[ (int) $row->arm_subscription_plan_id ] = $row->arm_subscription_plan_name;
			}
		}
		return $plans;
	}
}
