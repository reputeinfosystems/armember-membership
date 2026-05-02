<?php
/**
 * Plugin Name: ARM CSV Import & Export
 * Plugin URI:  https://www.armemberplugin.com/
 * Description: Adds CSV import and export functionality for members to ARMember Lite.
 * Version:     1.0.0
 * Requires at least: 5.0
 * Requires PHP: 5.6
 * Author:      Repute Infosystems
 * License:     GPL-2.0-or-later
 * Text Domain: arm-csv-import-export
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ARM_CSV_IE_VERSION', '1.0.0' );
define( 'ARM_CSV_IE_DIR', plugin_dir_path( __FILE__ ) );
define( 'ARM_CSV_IE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Boot the plugin after all plugins are loaded so we can
 * confirm ARMember Lite is active.
 */
add_action( 'plugins_loaded', 'arm_csv_ie_init', 20 );

function arm_csv_ie_init() {
	if ( ! defined( 'MEMBERSHIPLITE_DIR' ) ) {
		add_action( 'admin_notices', 'arm_csv_ie_missing_notice' );
		return;
	}

	require_once ARM_CSV_IE_DIR . 'includes/class-arm-csv-exporter.php';
	require_once ARM_CSV_IE_DIR . 'includes/class-arm-csv-importer.php';

	new ARM_CSV_Import_Export();
}

function arm_csv_ie_missing_notice() {
	echo '<div class="notice notice-error"><p>'
		. esc_html__( 'ARM CSV Import & Export requires ARMember Lite to be installed and active.', 'arm-csv-import-export' )
		. '</p></div>';
}

/**
 * Main controller class.
 */
class ARM_CSV_Import_Export {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 30 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// AJAX handlers
		add_action( 'wp_ajax_arm_csv_export', array( $this, 'handle_export' ) );
		add_action( 'wp_ajax_arm_csv_import_preview', array( $this, 'handle_import_preview' ) );
		add_action( 'wp_ajax_arm_csv_import_process', array( $this, 'handle_import_process' ) );
		add_action( 'wp_ajax_arm_csv_download_sample', array( $this, 'handle_sample_download' ) );
	}

	/** -----------------------------------------------------------------------
	 * Admin menu
	 * ---------------------------------------------------------------------- */

	public function add_admin_menu() {
		add_submenu_page(
			'arm_manage_members',
			__( 'CSV Import & Export', 'arm-csv-import-export' ),
			__( 'CSV Import & Export', 'arm-csv-import-export' ),
			'manage_options',
			'arm_csv_import_export',
			array( $this, 'render_page' )
		);
	}

	/** -----------------------------------------------------------------------
	 * Enqueue assets (only on our page)
	 * ---------------------------------------------------------------------- */

	public function enqueue_assets( $hook ) {
		if ( strpos( $hook, 'arm_csv_import_export' ) === false ) {
			return;
		}
		wp_enqueue_style(
			'arm-csv-ie-admin',
			ARM_CSV_IE_URL . 'admin/css/admin.css',
			array(),
			ARM_CSV_IE_VERSION
		);
		wp_enqueue_script(
			'arm-csv-ie-admin',
			ARM_CSV_IE_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			ARM_CSV_IE_VERSION,
			true
		);
		wp_localize_script(
			'arm-csv-ie-admin',
			'armCsvIE',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'arm_csv_ie_nonce' ),
				'importing' => __( 'Importing…', 'arm-csv-import-export' ),
				'exporting' => __( 'Exporting…', 'arm-csv-import-export' ),
				'confirm'   => __( 'Are you sure you want to import these members?', 'arm-csv-import-export' ),
			)
		);
	}

	/** -----------------------------------------------------------------------
	 * Render admin page
	 * ---------------------------------------------------------------------- */

	public function render_page() {
		require ARM_CSV_IE_DIR . 'admin/views/page-import-export.php';
	}

	/** -----------------------------------------------------------------------
	 * AJAX: Export CSV
	 * ---------------------------------------------------------------------- */

	public function handle_export() {
		check_ajax_referer( 'arm_csv_ie_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'arm-csv-import-export' ) );
		}

		$exporter = new ARM_CSV_Exporter();
		$filters  = array(
			'plan_ids'       => isset( $_POST['plan_ids'] ) ? array_map( 'intval', (array) $_POST['plan_ids'] ) : array(),
			'status'         => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '',
			'date_from'      => isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '',
			'date_to'        => isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '',
			'extra_fields'   => isset( $_POST['extra_fields'] ) ? array_map( 'sanitize_key', (array) $_POST['extra_fields'] ) : array(),
		);

		$exporter->export( $filters );
		exit;
	}

	/** -----------------------------------------------------------------------
	 * AJAX: Preview uploaded CSV
	 * ---------------------------------------------------------------------- */

	public function handle_import_preview() {
		check_ajax_referer( 'arm_csv_ie_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'arm-csv-import-export' ) ) );
		}

		if ( empty( $_FILES['csv_file'] ) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK ) {
			wp_send_json_error( array( 'message' => __( 'Please upload a valid CSV file.', 'arm-csv-import-export' ) ) );
		}

		$importer = new ARM_CSV_Importer();
		$result   = $importer->preview( $_FILES['csv_file'] );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}

	/** -----------------------------------------------------------------------
	 * AJAX: Process import
	 * ---------------------------------------------------------------------- */

	public function handle_import_process() {
		check_ajax_referer( 'arm_csv_ie_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'arm-csv-import-export' ) ) );
		}

		$rows        = isset( $_POST['rows'] ) ? wp_unslash( $_POST['rows'] ) : '';
		$column_map  = isset( $_POST['column_map'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['column_map'] ) ) : array();
		$plan_id     = isset( $_POST['plan_id'] ) ? intval( $_POST['plan_id'] ) : 0;
		$send_notify = ! empty( $_POST['send_notify'] );
		$update_existing = ! empty( $_POST['update_existing'] );

		if ( empty( $rows ) || empty( $column_map ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing import data.', 'arm-csv-import-export' ) ) );
		}

		$decoded = json_decode( stripslashes( $rows ), true );
		if ( ! is_array( $decoded ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid import data.', 'arm-csv-import-export' ) ) );
		}

		$importer = new ARM_CSV_Importer();
		$result   = $importer->process( $decoded, $column_map, $plan_id, $send_notify, $update_existing );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}

	/** -----------------------------------------------------------------------
	 * AJAX / direct: Download sample CSV
	 * ---------------------------------------------------------------------- */

	public function handle_sample_download() {
		check_ajax_referer( 'arm_csv_ie_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'arm-csv-import-export' ) );
		}

		$sample = array(
			array(
				'username'     => 'jdoe',
				'email'        => 'jdoe@example.com',
				'first_name'   => 'John',
				'last_name'    => 'Doe',
				'display_name' => 'John Doe',
				'password'     => 'StrongPass1!',
				'role'         => 'subscriber',
			),
			array(
				'username'     => 'jsmith',
				'email'        => 'jsmith@example.com',
				'first_name'   => 'Jane',
				'last_name'    => 'Smith',
				'display_name' => 'Jane Smith',
				'password'     => 'StrongPass2!',
				'role'         => 'subscriber',
			),
		);

		$filename = 'arm-members-sample.csv';
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		ob_clean();
		$df = fopen( 'php://output', 'w' );
		fputcsv( $df, array_keys( $sample[0] ) );
		foreach ( $sample as $row ) {
			fputcsv( $df, $row );
		}
		fclose( $df ); // phpcs:ignore
		exit;
	}
}
