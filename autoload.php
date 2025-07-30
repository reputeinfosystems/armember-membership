<?php 
if ( ! defined( 'MEMBERSHIPLITE_CORE_DIR' ) ) {

	if ( is_ssl() ) {
		define( 'MEMBERSHIPLITE_URL', str_replace( 'http://', 'https://', WP_PLUGIN_URL . '/' . MEMBERSHIPLITE_DIR_NAME ) );
		define( 'ARMLITE_HOME_URL', home_url( '', 'https' ) );
	} else {
		define( 'MEMBERSHIPLITE_URL', WP_PLUGIN_URL . '/' . MEMBERSHIPLITE_DIR_NAME );
		define( 'ARMLITE_HOME_URL', home_url() );
	}

	define( 'MEMBERSHIPLITE_CORE_DIR', MEMBERSHIPLITE_DIR . '/core' );
	define( 'MEMBERSHIPLITE_CLASSES_DIR', MEMBERSHIPLITE_DIR . '/core/classes' );
	define( 'MEMBERSHIPLITE_CLASSES_URL', MEMBERSHIPLITE_URL . '/core/classes' );
	define( 'MEMBERSHIPLITE_WIDGET_DIR', MEMBERSHIPLITE_DIR . '/core/widgets' );
	define( 'MEMBERSHIPLITE_WIDGET_URL', MEMBERSHIPLITE_URL . '/core/widgets' );
	define( 'MEMBERSHIPLITE_IMAGES_DIR', MEMBERSHIPLITE_DIR . '/images' );
	define( 'MEMBERSHIPLITE_IMAGES_URL', MEMBERSHIPLITE_URL . '/images' );
	define( 'MEMBERSHIPLITE_LIBRARY_DIR', MEMBERSHIPLITE_DIR . '/lib' );
	define( 'MEMBERSHIPLITE_LIBRARY_URL', MEMBERSHIPLITE_URL . '/lib' );
	define( 'MEMBERSHIPLITE_INC_DIR', MEMBERSHIPLITE_DIR . '/inc' );
	define( 'MEMBERSHIPLITE_VIEWS_DIR', MEMBERSHIPLITE_DIR . '/core/views' );
	define( 'MEMBERSHIPLITE_VIEWS_URL', MEMBERSHIPLITE_URL . '/core/views' );
	define( 'MEMBERSHIPLITE_VIDEO_URL', 'https://www.youtube.com/embed/8COXGo-NetQ' );
	define( 'MEMBERSHIPLITE_DOCUMENTATION_URL', 'https://www.armemberplugin.com/documentation' );

}

if ( ! defined( 'FS_METHOD' ) ) {
	define( 'FS_METHOD', 'direct' );
}

/* Cornerstone */



/* DEBUG LOG CONSTANTS */
define( 'MEMBERSHIPLITE_DEBUG_LOG', false ); /* true - enable debug log (Default) & false - disable debug log */
define( 'MEMBERSHIPLITE_DEBUG_LOG_TYPE', 'ARM_ALL' );
/*
 Possible Values
  ARM_ALL - Enable Debug Log for All types for restriction & redirection rules (Default).
  ARM_ADMIN_PANEL - Enable Debug Log for WordPress admin panel restriction & redirection rules.
  ARM_POSTS - Enable Debug Log for WordPress default posts for restriction & redirection rules.
  ARM_PAGES - Enable Debug Log for WordPress default pages for restriction & redirection rules.
  ARM_TAXONOMY - Enable Debug Log for all taxonomies for restriction & redirection rules.
  ARM_MENU - Enable Debug Log for WordPress Menu for restriction & redirection rules.
  ARM_CUSTOM - Enable Debug Log for all types of custom posts for restriction & redirection rules.
  ARM_SPECIAL_PAGE - Enable Debug Log for all types of special pages like Archive Page, Author Page, Category Page, etc.
  ARM_SHORTCODE - Enable Debug Log for all types of restriction & redirection rules applied using shortcodes
  ARM_MAIL - Enable Debug Log for all content before mail sent.
 */


global $arm_lite_datepicker_loaded, $arm_lite_avatar_loaded, $arm_lite_file_upload_field, $arm_lite_bpopup_loaded, $arm_lite_load_tipso, $arm_lite_popup_modal_elements, $arm_lite_is_access_rule_applied, $arm_lite_load_icheck, $arm_lite_font_awesome_loaded, $arm_lite_inner_form_modal,$arm_lite_forms_page_arr, $ARMemberLiteAllowedHTMLTagsArray;

$arm_lite_is_access_rule_applied = 0;
$arm_lite_datepicker_loaded      = $arm_lite_avatar_loaded = $arm_lite_file_upload_field = $arm_lite_bpopup_loaded = $arm_lite_load_tipso = $arm_lite_font_awesome_loaded = 0;
$arm_lite_popup_modal_elements   = array();
$arm_lite_inner_form_modal       = array();
$arm_lite_forms_page_arr         = array();
global $arm_case_types;
$arm_case_types = array(
	'admin_panel' => array(
		'protected' => false,
		'type'      => 'redirect',
	),
	'page'        => array(
		'protected' => false,
		'type'      => 'redirect',
	),
	'post'        => array(
		'protected' => false,
		'type'      => 'redirect',
	),
	'taxonomy'    => array(
		'protected' => false,
		'type'      => 'redirect',
	),
	'menu'        => array(
		'protected' => false,
		'type'      => 'redirect',
	),
	'custom'      => array(
		'protected' => false,
		'type'      => 'redirect',
	),
	'special'     => array(
		'protected' => false,
		'type'      => 'redirect',
	),
	'shortcode'   => array(
		'protected' => false,
		'type'      => 'redirect',
	),
	'mail'        => array(
		'protected' => false,
		'type'      => 'redirect',
	),
);

$arm_lite_wpupload_dir = wp_upload_dir();
$arm_lite_upload_dir   = $arm_lite_wpupload_dir['basedir'] . '/armember';
$arm_lite_upload_url   = $arm_lite_wpupload_dir['baseurl'] . '/armember';
if ( ! is_dir( $arm_lite_upload_dir ) ) {
	wp_mkdir_p( $arm_lite_upload_dir );
}
define( 'MEMBERSHIPLITE_UPLOAD_DIR', $arm_lite_upload_dir );
define( 'MEMBERSHIPLITE_UPLOAD_URL', $arm_lite_upload_url );

/* Defining Membership Plugin Version */
global $arm_lite_version;
$arm_lite_version = '4.0.66';
define( 'MEMBERSHIPLITE_VERSION', $arm_lite_version );

global $arm_lite_ajaxurl;
$arm_lite_ajaxurl = admin_url( 'admin-ajax.php' );

global $arm_lite_errors;
$arm_lite_errors = new WP_Error();

global $arm_lite_widget_effects;

global $armlite_default_user_details_text;

/**
 * Plugin Main Class
 */
global $ARMemberLite, $arm_lite_debug_payment_log_id, $arm_lite_debug_general_log_id, $arm_lite_bf_sale_start_time, $arm_lite_bf_sale_end_time;
$ARMemberLite = new ARMemberlite();
$arm_lite_debug_payment_log_id =0;
$arm_lite_debug_general_log_id = 0;

$arm_lite_bf_sale_start_time = "1745838000"; //black friday sale start time
$arm_lite_bf_sale_end_time = "1746444600"; //black friday sale end time

if(!$ARMemberLite->is_arm_pro_active){
	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_members.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_members.php';
	}

	if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_setup_wizard.php")){
		require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_setup_wizard.php");
	}

	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_modal_view_in_menu.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_modal_view_in_menu.php';
	}

	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_restriction.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_restriction.php';
	}

	if (file_exists(MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_manage_subscription.php')) {
		include( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_manage_subscription.php');
	}

	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_payment_gateways.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_payment_gateways.php';
	}

	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_shortcodes.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_shortcodes.php';
	}

	if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_updates_cron.php")){
		require_once( MEMBERSHIPLITE_CLASSES_DIR . "/class.arm_updates_cron.php");
	}

	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_gateways_paypal.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_gateways_paypal.php';
	}

	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_global_settings.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_global_settings.php';
	}
	
	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_membership_setup.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_membership_setup.php';
	}

	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_member_forms.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_member_forms.php';
	}


	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_members_directory.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_members_directory.php';
	}

	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_subscription_plans.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_subscription_plans.php';
	}


	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_transaction.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_transaction.php';
	}

	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_crons.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_crons.php';
	}

	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_manage_communication.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_manage_communication.php';
	}

	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_members_activity.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_members_activity.php';
	}

	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_social_feature.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_social_feature.php';
	}

	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_access_rules.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_access_rules.php';
	}

	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_email_settings.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_email_settings.php';
	}

	if ( file_exists( MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_spam_filter.php' ) ) {
		require_once MEMBERSHIPLITE_CLASSES_DIR . '/class.arm_spam_filter.php';
	}

	if ( file_exists( MEMBERSHIPLITE_WIDGET_DIR . '/class.arm_dashboard_widgets.php' ) ) {
		require_once MEMBERSHIPLITE_WIDGET_DIR . '/class.arm_dashboard_widgets.php';
	}

	if ( file_exists( MEMBERSHIPLITE_WIDGET_DIR . '/class.arm_widgetForm.php' ) ) {
		require_once MEMBERSHIPLITE_WIDGET_DIR . '/class.arm_widgetForm.php';
	}

	if ( file_exists( MEMBERSHIPLITE_WIDGET_DIR . '/class.arm_widgetlatestMembers.php' ) ) {
		require_once MEMBERSHIPLITE_WIDGET_DIR . '/class.arm_widgetlatestMembers.php';
	}

	if ( file_exists( MEMBERSHIPLITE_WIDGET_DIR . '/class.arm_widgetloginwidget.php' ) ) {
		require_once MEMBERSHIPLITE_WIDGET_DIR . '/class.arm_widgetloginwidget.php';
	}

	if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/arm_builder/class.arm_gutenberg_restriction.php")){
		require_once( MEMBERSHIPLITE_CLASSES_DIR . "/arm_builder/class.arm_gutenberg_restriction.php");
	}

	if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/arm_builder/class.arm_beaver_builder_restriction.php")){
		require_once( MEMBERSHIPLITE_CLASSES_DIR . "/arm_builder/class.arm_beaver_builder_restriction.php");
	}

	if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/arm_builder/class.arm_divi_builder_restriction.php")){
		require_once( MEMBERSHIPLITE_CLASSES_DIR . "/arm_builder/class.arm_divi_builder_restriction.php");
	}

	if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/arm_builder/class.arm_wpbakery_builder_restriction.php")){
		require_once( MEMBERSHIPLITE_CLASSES_DIR . "/arm_builder/class.arm_wpbakery_builder_restriction.php");
	}

	if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/arm_builder/class.arm_fusion_builder_restriction.php")){
		require_once( MEMBERSHIPLITE_CLASSES_DIR . "/arm_builder/class.arm_fusion_builder_restriction.php");
	}

	if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/arm_builder/class.arm_oxygen_builder_restriction.php")){
		require_once( MEMBERSHIPLITE_CLASSES_DIR . "/arm_builder/class.arm_oxygen_builder_restriction.php");
	}

	if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/arm_builder/class.arm_siteorigin_builder_restriction.php")){
		require_once( MEMBERSHIPLITE_CLASSES_DIR . "/arm_builder/class.arm_siteorigin_builder_restriction.php");
	}
	
	if(file_exists(MEMBERSHIPLITE_CLASSES_DIR . "/arm_builder/class.arm_bricks_builder_restriction.php")){
		require_once( MEMBERSHIPLITE_CLASSES_DIR . "/arm_builder/class.arm_bricks_builder_restriction.php");
	}

	if(is_plugin_active('elementor/elementor.php'))
	{
		require_once(MEMBERSHIPLITE_CORE_DIR . '/classes/arm_builder/class.arm_elementor_membership_shortcode.php');
		global $ARMelementor;
		$ARMelementor = new arm_lite_membership_elementcontroller();
	}
	
}
else {
	if( file_exists(MEMBERSHIPLITE_CLASSES_DIR.'/class.armemberlite.php')){
		require_once MEMBERSHIPLITE_CLASSES_DIR.'/class.armemberlite.php';
	}
}
if( file_exists(MEMBERSHIPLITE_CLASSES_DIR.'/class.arm_growth_plugin.php')){
	require_once MEMBERSHIPLITE_CLASSES_DIR.'/class.arm_growth_plugin.php';
}

global $arm_lite_api_url, $arm_lite_plugin_slug, $wp_version;

// Query monitor
register_uninstall_hook( MEMBERSHIPLITE_DIR . '/armember-membership.php', array( 'ARMemberlite', 'uninstall' ) );

class ARMemberlite {

	var $arm_slugs;
	var $tbl_arm_activity;
	var $tbl_arm_auto_message;

	var $tbl_arm_email_templates;
	var $tbl_arm_entries;
	var $tbl_arm_fail_attempts;
	var $tbl_arm_forms;
	var $tbl_arm_form_field;
	var $tbl_arm_lockdown;
	var $tbl_arm_members;
	var $tbl_arm_membership_setup;
	var $tbl_arm_payment_log;
	var $tbl_arm_bank_transfer_log;
	var $tbl_arm_subscription_plans;
	var $tbl_arm_termmeta;
	var $tbl_arm_member_templates;
	var $is_arm_pro_active;
	var $tbl_arm_debug_payment_log;
	var $tbl_arm_debug_general_log;


	var $tbl_arm_login_history;

	function __construct() {
		global $wp, $wpdb, $arm_db_tables, $arm_access_rules, $arm_capabilities_global, $ARMemberLiteAllowedHTMLTagsArray;
		$this->is_arm_pro_active = $this->arm_is_pro_active();
		$arm_db_tables = array(
			'tbl_arm_activity'           => $wpdb->prefix . 'arm_activity',
			'tbl_arm_auto_message'       => $wpdb->prefix . 'arm_auto_message',

			'tbl_arm_email_templates'    => $wpdb->prefix . 'arm_email_templates',
			'tbl_arm_entries'            => $wpdb->prefix . 'arm_entries',
			'tbl_arm_fail_attempts'      => $wpdb->prefix . 'arm_fail_attempts',
			'tbl_arm_forms'              => $wpdb->prefix . 'arm_forms',
			'tbl_arm_form_field'         => $wpdb->prefix . 'arm_form_field',
			'tbl_arm_lockdown'           => $wpdb->prefix . 'arm_lockdown',
			'tbl_arm_members'            => $wpdb->prefix . 'arm_members',
			'tbl_arm_membership_setup'   => $wpdb->prefix . 'arm_membership_setup',
			'tbl_arm_payment_log'        => $wpdb->prefix . 'arm_payment_log',
			'tbl_arm_bank_transfer_log'  => $wpdb->prefix . 'arm_bank_transfer_log',
			'tbl_arm_subscription_plans' => $wpdb->prefix . 'arm_subscription_plans',
			'tbl_arm_termmeta'           => $wpdb->prefix . 'arm_termmeta',
			'tbl_arm_member_templates'   => $wpdb->prefix . 'arm_member_templates',

			'tbl_arm_login_history'      => $wpdb->prefix . 'arm_login_history',
			'tbl_arm_debug_payment_log' => $wpdb->prefix . 'arm_debug_payment_log',
			'tbl_arm_debug_general_log' => $wpdb->prefix . 'arm_debug_general_log'
		);
		/* Set Database Table Variables. */
		foreach ( $arm_db_tables as $key => $table ) {
			$this->$key = $table;
		}

		/* Set Page Slugs Global */
		$this->arm_slugs = $this->arm_page_slugs();

		/* Set Page Capabilities Global */
		$arm_capabilities_global = array(
			'arm_manage_members'             => 'arm_manage_members',
			'arm_manage_plans'               => 'arm_manage_plans',
			'arm_manage_setups'              => 'arm_manage_setups',
			'arm_manage_forms'               => 'arm_manage_forms',
			'arm_manage_access_rules'        => 'arm_manage_access_rules',
			'arm_manage_subscriptions'	 => 'arm_manage_subscriptions',
			'arm_manage_transactions'        => 'arm_manage_transactions',
			'arm_manage_email_notifications' => 'arm_manage_email_notifications',
			'arm_manage_communication'       => 'arm_manage_communication',
			'arm_manage_member_templates'    => 'arm_manage_member_templates',
			'arm_manage_general_settings'    => 'arm_manage_general_settings',
			'arm_manage_feature_settings'    => 'arm_manage_feature_settings',
			'arm_manage_block_settings'      => 'arm_manage_block_settings',

			'arm_manage_payment_gateways'    => 'arm_manage_payment_gateways',
			'arm_import_export'              => 'arm_import_export',
			'arm_growth_plugins'              => 'arm_growth_plugins',

		);

		register_activation_hook( MEMBERSHIPLITE_DIR . '/armember-membership.php', array( 'ARMemberlite', 'install' ) );
		register_activation_hook( MEMBERSHIPLITE_DIR . '/armember-membership.php', array( 'ARMemberlite', 'armember_check_network_activation' ) );
		register_deactivation_hook(MEMBERSHIPLITE_DIR . '/armember-membership.php', array( 'ARMemberlite', 'deactivate__armember_lite_version' ));

		add_action( 'admin_notices', array( $this, 'arm_display_news_notices' ) );
		add_action( 'wp_ajax_arm_dismiss_news', array( $this, 'arm_dismiss_news_notice' ) );

		/* Load Language TextDomain */
		add_action( 'init', array( $this, 'arm_load_textdomain' ) );
		/* Add 'Addon' link in plugin list */
		add_filter( 'plugin_action_links', array( $this, 'armPluginActionLinks' ), 10, 2 );
		/* Hide Update Notification */
		/* Init Hook */
		add_action( 'init', array( $this, 'arm_init_action' ) );
		add_action( 'init', array( $this, 'wpdbfix' ) );
		add_action( 'switch_blog', array( $this, 'wpdbfix' ) );
		// Query monitor
		add_action( 'admin_init', array( $this, 'arm_install_plugin_data' ), 1000 );
		
		add_action( 'admin_body_class', array( $this, 'arm_admin_body_class' ) );
		if (!is_plugin_active( 'armember/armember.php' ) ) {
			add_action( 'admin_menu', array( $this, 'arm_menu' ), 27 );
			add_action( 'admin_menu', array( $this, 'arm_set_last_menu' ), 50 );
			add_action( 'admin_init', array( $this, 'arm_hide_update_notice' ), 1 );
			add_action( 'admin_enqueue_scripts', array( $this, 'set_css' ), 11 );
			add_action( 'admin_enqueue_scripts', array( $this, 'set_js' ), 11 );
			// add_action( 'admin_enqueue_scripts', array( $this, 'set_global_javascript_variables' ), 10 );
			add_action( 'wp_head', array( $this, 'set_front_css' ), 1 );
			add_action( 'wp_head', array( $this, 'set_front_js' ), 1 );
			// add_action( 'wp_head', array( $this, 'set_global_javascript_variables' ) );
			add_action( 'admin_footer', array( $this, 'arm_add_document_video' ), 1 );
			add_action( 'admin_footer', array( $this, 'arm_add_new_version_release_note' ), 1 );
			add_action( 'arm_admin_messages', array( $this, 'arm_admin_messages_init' ) );
			
			add_action( 'admin_bar_menu', array( $this, 'arm_add_debug_bar_menu' ), 999 );
			
			/* Add Document Video For First Time */
			add_action( 'wp_ajax_arm_do_not_show_video', array( $this, 'arm_do_not_show_video' ), 1 );
			
			/* Add what's new popup */
		
			add_action( 'wp_ajax_arm_dont_show_upgrade_notice', array( $this, 'arm_dont_show_upgrade_notice' ), 1 );

			/* For Admin Menus. */
			add_action( 'adminmenu', array( $this, 'arm_set_adminmenu' ) );
			add_action( 'wp_logout', array( $this, 'ARM_EndSession' ) );
			add_action( 'wp_login', array( $this, 'ARM_EndSession' ) );

		
			add_action('wp_ajax_arm_get_need_help_content', array( $this, 'arm_get_need_help_content_func' ), 10, 1);
			
			/* Include All Class Files. */

			// Query Monitor
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require ABSPATH . '/wp-admin/includes/plugin.php';
			}
			if ( is_plugin_active( 'js_composer/js_composer.php' ) && file_exists( MEMBERSHIPLITE_CORE_DIR . '/vc/class_vc_extend.php' ) ) {
				require_once MEMBERSHIPLITE_CORE_DIR . '/vc/class_vc_extend.php';
				global $armlite_vcextend;
				$armlite_vcextend = new ARMLITE_VCExtend();
			}

			if ( is_plugin_active( 'wp-rocket/wp-rocket.php' ) && ! is_admin() ) {
				add_filter( 'script_loader_tag', array( $this, 'arm_prevent_rocket_loader_script' ), 10, 2 );
			}
			
			/*
			Register Element for Cornerstone */
			/*
			add_action('wp_enqueue_scripts',array($this,'armember_cs_enqueue'));
			add_action('cornerstone_register_elements',array($this,'armember_cs_register_element'));
			add_filter('cornerstone_icon_map',array($this,'armember_cs_icon_map')); */
			/* Register Element for Cornerstone */
			add_action( 'wp_footer', array( $this, 'arm_set_js_css_conditionally' ), 11 );

			if ( ! empty( $GLOBALS['wp_version'] ) && version_compare( $GLOBALS['wp_version'], '5.7.2', '>' ) ) {
				add_filter( 'block_categories_all', array( $this, 'arm_gutenberg_category' ), 10, 2 );
			} else {
				add_filter( 'block_categories', array( $this, 'arm_gutenberg_category' ), 10, 2 );
			}
			
			add_action( 'enqueue_block_editor_assets', array( $this, 'arm_enqueue_gutenberg_assets' ) );

			add_action('arm_payment_log_entry', array($this, 'arm_write_payment_log'), 10, 6);
	
			add_action('arm_general_log_entry', array( $this, 'arm_write_general_log'), 10, 4);
		}
		
		add_action( 'admin_enqueue_scripts', array( $this, 'armlite_enqueue_notice_assets' ), 10 );
		add_action( 'admin_notices', array( $this, 'armlite_display_notice_for_rating' ) );
		add_action( 'wp_ajax_armlite_dismiss_rate_notice', array( $this, 'armlite_reset_ratenow_notice' ) );
		add_action( 'wp_ajax_armlite_dismiss_rate_notice_no_display', array( $this, 'armlite_reset_ratenow_notice_never' ) );

		add_action('wp_ajax_arm_reinit_nonce_var',array($this,'arm_reinit_nonce_var_func'));
		add_action( 'wp_ajax_nopriv_arm_reinit_nonce_var', array($this,'arm_reinit_nonce_var_func'));

		$ARMemberLiteAllowedHTMLTagsArray = $this->armember_allowed_html_tags();
	}

	function arm_reinit_nonce_var_func(){
        global $ARMemberLite,$arm_capabilities_global;
		$ARMemberLite->arm_session_start();
		
		$form_key    = sanitize_text_field( $_POST['form_key'] ); //phpcs:ignore
		if ( ! empty( $form_key ) ) {
			if( !empty($_SESSION['ARM_FILTER_INPUT']) && !empty($_SESSION['ARM_FILTER_INPUT'][ $form_key ]) )
			{
				if(isset($_POST['action']) && $_POST['action'] == 'arm_reinit_nonce_var'){//phpcs:ignore
					echo wp_json_encode(array( 'nonce' => wp_create_nonce('arm_wp_nonce')));
				}
			}
		}
        die();
    }
    function arm_write_payment_log($arm_log_payment_gateway, $arm_log_event, $arm_log_event_from = 'armember-membership', $arm_payment_log_raw_data = '', $arm_ref_id = 0, $arm_log_status = 1)
		{
			global $wpdb, $ARMemberLite, $arm_payment_gateways, $arm_lite_debug_payment_log_id, $arm_capabilities_global, $arm_payment_gateways_data_logs, $arm_payment_gateways_data_logs_flag;

			if(empty($arm_payment_gateways_data_logs) && empty($arm_payment_gateways_data_logs_flag) )
			{
				$arm_payment_gateways_data_logs = $arm_payment_gateways->arm_get_all_payment_gateways_for_setup();
				$arm_payment_gateways_data_logs_flag = 1;
			}
			$inserted_id = 0;
			if( !empty($arm_payment_gateways_data_logs[$arm_log_payment_gateway]['payment_debug_logs']) )
			{
				$tbl_arm_debug_payment_log = $ARMemberLite->tbl_arm_debug_payment_log;

				if($arm_ref_id==NULL) { $arm_ref_id = 0; }
				$arm_database_log_data = array(
					'arm_payment_log_ref_id' => $arm_ref_id,
					'arm_payment_log_gateway' => $arm_log_payment_gateway,
					'arm_payment_log_event' => $arm_log_event,
					'arm_payment_log_event_from' => $arm_log_event_from,
					'arm_payment_log_status' => $arm_log_status,
					'arm_payment_log_raw_data' => maybe_serialize(stripslashes_deep($arm_payment_log_raw_data)),
					'arm_payment_log_added_date' => current_time('mysql'),
				);
				
				//If reference id empty then insert log.
				$wpdb->insert($tbl_arm_debug_payment_log, $arm_database_log_data); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$inserted_id = $wpdb->insert_id;
				if(empty($arm_ref_id))
				{
					$arm_ref_id = $inserted_id;
				}
			}
			$arm_lite_debug_payment_log_id = $arm_ref_id;

			return $inserted_id;
		}



		function arm_write_general_log($arm_log_event, $arm_log_event_name, $arm_log_event_from = 'armember', $arm_payment_log_raw_data = '')
		{
			global $wpdb, $ARMemberLite, $arm_lite_debug_general_log_id, $arm_capabilities_global, $arm_email_settings, $arm_is_cron_log_enabled,$arm_is_email_log_enabled ,$arm_is_cron_log_check_flag, $arm_is_opt_ins_log_enabled, $arm_is_opt_ins_log_check_flag,$arm_is_email_log_check_flag;

			if ($arm_log_event == 'cron') 
			{
				if ( empty($arm_is_cron_log_enabled) && empty($arm_is_cron_log_check_flag) ) 
				{
					$arm_is_cron_log_enabled = get_option('arm_cron_debug_log');
					$arm_is_cron_log_check_flag = 1;
				}
				$arm_is_log_enabled = $arm_is_cron_log_enabled;
			} 
			else if ($arm_log_event == 'email') 
			{
				if ( empty($arm_is_email_log_enabled) && empty($arm_is_email_log_check_flag) ) 
				{
					$arm_is_email_log_enabled = get_option('arm_email_debug_log');
					$arm_is_email_log_check_flag = 1;
				}
				$arm_is_log_enabled = $arm_is_email_log_enabled;
			} 
			else {
				if ($arm_email_settings->isOptInsFeature && empty($arm_is_opt_ins_log_enabled) && empty($arm_is_opt_ins_log_check_flag)) 
				{
					$arm_is_opt_ins_log_enabled = get_option('arm_optins_debug_log');
					$arm_is_opt_ins_log_check_flag = 1;
				}
				$arm_is_log_enabled = $arm_is_opt_ins_log_enabled;
			}
			
			$inserted_id = 0;
			if($arm_log_event != 'email') {
				$arm_payment_log_raw_data = maybe_serialize(stripslashes_deep($arm_payment_log_raw_data));
			}
			if ( !empty($arm_is_log_enabled) ) 
			{
				$tbl_arm_debug_general_log = $ARMemberLite->tbl_arm_debug_general_log;
				$arm_database_log_data = array(
					'arm_general_log_event' => $arm_log_event,
					'arm_general_log_event_name' => $arm_log_event_name,
					'arm_general_log_event_from' => $arm_log_event_from,
					'arm_general_log_raw_data' => $arm_payment_log_raw_data,
					'arm_general_log_added_date' => current_time('mysql'),  
				);
				
				$wpdb->insert($tbl_arm_debug_general_log, $arm_database_log_data); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$inserted_id = $wpdb->insert_id;
			}
			
			return $inserted_id;
		}

	function armlite_enqueue_notice_assets() {
		global $arm_lite_version, $ARMemberLite;

		wp_register_script( 'armlite-admin-notice-script-js', MEMBERSHIPLITE_URL . '/js/armlite-admin-notice.js', array(), $arm_lite_version ); //phpcs:ignore

		wp_enqueue_script( 'armlite-admin-notice-script-js' );
		if($ARMemberLite->is_arm_pro_active){
			global $ARMember;
			$ARMember->set_global_javascript_variables();
		}
		else
		{
			$this->set_global_javascript_variables();
		}
	}

	function armlite_reset_ratenow_notice_never() {
		global $ARMemberLite;
		$ARMemberLite->arm_check_user_cap('',1); //phpcs:ignore --Reason:Verifying nonce
		update_option( 'armlite_display_rating_notice', 'no' );
		update_option( 'armlite_never_display_rating_notice', 'true' );
		die;
	}

	function armlite_reset_ratenow_notice() {
		global $ARMemberLite;
		$ARMemberLite->arm_check_user_cap('',1); //phpcs:ignore --Reason:Verifying nonce
		$nextEvent = strtotime( '+60 days' );

		wp_schedule_single_event( $nextEvent, 'armlite_display_ratenow_popup' );

		update_option( 'armlite_display_rating_notice', 'no' );

		die;
	}

	function armlite_display_notice_for_rating() {
		$display_notice       = get_option( 'armlite_display_rating_notice' );
		$display_notice_never = get_option( 'armlite_never_display_rating_notice' );
		// echo "<br>Reputelog : display_notice : ".$display_notice." || display_notice_never : ".$display_notice_never;die;

		if ( '' != $display_notice && 'yes' == $display_notice && ( '' == $display_notice_never || 'yes' != $display_notice_never ) ) {
			$wpnonce = wp_create_nonce( 'arm_wp_nonce' );
			$nonce = '<input type="hidden" name="arm_wp_nonce" value="'.esc_attr($wpnonce).'"/>';
			$class           = 'notice notice-warning armlite-rate-notice is-dismissible';
			$message         = sprintf( addslashes( esc_html__( "Hey, you've been using %1\$sARMember Lite%2\$s for a long time. %3\$sCould you please do us a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation. %4\$sYour help is much appreciated. Thank you very much - %5\$sRepute InfoSystems%6\$s", 'armember-membership' ) ), '<strong>', '</strong>', '<br/>', '<br/><br/>', '<strong>', '</strong>' );
			$rate_link       = 'https://wordpress.org/support/plugin/armember-membership/reviews/';
			$rate_link_text  = esc_html__( 'OK, you deserve it', 'armember-membership' );
			$close_btn_text  = esc_html__( 'No, Maybe later', 'armember-membership' );
			$rated_link_text = esc_html__( 'I already did', 'armember-membership' );

			printf( '<div class="%1$s"><p>%2$s</p><br/><br/><a href="%3$s" class="armlite_rate_link" target="_blank">%4$s</a><br/><a class="armlite_maybe_later_link" href="javascript:void(0);">%5$s</a><br/><a class="armlite_already_rated_link" href="javascript:void(0)">%6$s</a><br/>&nbsp;</div>', esc_attr( $class ), $message, esc_url( $rate_link ), esc_html( $rate_link_text ), esc_attr( $close_btn_text ), esc_html( $rated_link_text ), $nonce ); //phpcs:ignore
		}
	}

	function arm_gutenberg_category( $category, $post ) {
		$new_category = array(
			array(
				'slug'  => 'armember',
				'title' => 'ARMember Blocks',
			),
		);

		$final_categories = array_merge( $category, $new_category );

		return $final_categories;
	}

	function arm_enqueue_gutenberg_assets(){
		global $arm_lite_version,$arm_subscription_plans, $arm_gutenberg_block_restriction;
		$server_php_self = isset($_SERVER['PHP_SELF']) ? basename(sanitize_text_field($_SERVER['PHP_SELF'])) : ''; //phpcs:ignore
		if( !in_array( $server_php_self, array( 'site-editor.php', 'widgets.php' ) ) ) {
			$all_membership_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');

			$all_plan_values_and_labels = array(
				array('value' => 'any_plan' , 'label' => esc_html__('Any Plan', 'armember-membership')),
				array('value' => 'unregistered' , 'label' => esc_html__('Non Loggedin Users', 'armember-membership')),
				array('value' => 'registered' , 'label' => esc_html__('Loggedin Users', 'armember-membership'))
			);
			foreach( $all_membership_plans as $plan ) {        
				$all_plan_values_and_labels[] = array( 'value' => $plan['arm_subscription_plan_id'], 'label' => $plan['arm_subscription_plan_name'] );
			}

			wp_register_script( 'armlite_gutenberg_script', MEMBERSHIPLITE_URL . '/js/arm_gutenberg_script.js', array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-components','wp-editor','wp-block-editor' ), $arm_lite_version ); //phpcs:ignore
			wp_enqueue_script( 'armlite_gutenberg_script' );

			$is_gutenberg_block_restriction_feature = $arm_gutenberg_block_restriction->isGutenbergBlockRestrictionFeature;
			$arm_block_esc_html = array();
			$arm_block_esc_html['membership_shortcodes'] = array(
				'block_title' => esc_html__('Membership Shortcodes', 'armember-membership'),
				'keywords' => array(esc_html__('Membership', 'armember-membership'),esc_html__('ARMember', 'armember-membership'),esc_html__('Shortcode', 'armember-membership')),
			);
			$arm_block_esc_html['restrict_content_shortcode'] = array(
				'block_title' => esc_html__('Restrict Content Shortcode', 'armember-membership'),
				'keywords' => array(esc_html__('Membership', 'armember-membership'),esc_html__('ARMember', 'armember-membership'),esc_html__('Restriction', 'armember-membership')),
			);
			$arm_block_esc_html['armember_block_restriction'] = array(
				'block_title' => esc_html__('ARMember Block Restriction', 'armember-membership'),
				'keywords' => array(esc_html__('Membership', 'armember-membership'),esc_html__('ARMember', 'armember-membership'),esc_html__('Block', 'armember-membership'),esc_html__('Restriction', 'armember-membership')),
				'description' => esc_html__('Nest blocks within this wrapper to control the inner block visibility by membership plans or for free membership only.', 'armember-membership'),
				'restriction_type' => array( 
					'type' =>  esc_html__( "Restriction Type", 'armember-membership' ),
					'show' => esc_html__("Show content only for", 'armember-membership' ),
					'hide' => esc_html__("Hide content only for", 'armember-membership' ),
				),
				'membership_plan' => esc_html__( "Membership Plans", 'armember-membership' ),
				'membership_plan_help' => esc_html__("If any of the following conditions will true then restrictions will apply on content", 'armember-membership' ),
			);


			wp_localize_script( 'armlite_gutenberg_script', 'armember_block_admin', array(
				'all_membership_plans' => $all_plan_values_and_labels,
				'arm_gutenberg_block_restriction_feature' => $is_gutenberg_block_restriction_feature,
				'arm_block_esc_html' => $arm_block_esc_html,
			));

			wp_register_style( 'armlite_gutenberg_style', MEMBERSHIPLITE_URL . '/css/arm_gutenberg_style.css', array(), $arm_lite_version );
			wp_enqueue_style( 'armlite_gutenberg_style' );
		}

	}

	function arm_sample_admin_notice__success() {
		$is_arm_admin_notice_shown = 'block !important';
		global $ARMemberLite;
		$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
		if ( $arm_check_is_gutenberg_page ) {
			return true;
		}

		if ( ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'arm_manage_forms' && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit_form' ) || (isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'arm_growth_plugins') ) { //phpcs:ignore
			$is_arm_admin_notice_shown = 'none !important';
		}

		?>
		<div class="notice arm_admin_notice_shown" style="display: <?php echo esc_html($is_arm_admin_notice_shown); ?>;background-color: #faa800;color: #fff; padding: 0; border: none; margin-bottom: 0 !important">

			<p class="arm_admin_notice_shown_icn" style="padding: 13px 2px 13px 0;display: table-cell;width: 60px;text-align: center;vertical-align: middle;background-color: #ffb215;line-height: 24px;margin: 0 15px 0 0;">
				<span class="dashicons dashicons-warning" style="font-size: 25px;"></span>
			</p>
			<p class="arm_admin_notice_shown_msg" style="display: table-cell; padding: 10px 0 0 15px; font-weight: 600; font-size: 16px;">Upgrade to <a href="https://www.armemberplugin.com/product.php?rdt=t11" style="color: #fff;font-size: 18px;text-decoration: none;border-bottom: 1px solid;" target="_blank">ARMember Premium</a> to get access of all premium features and frequent updates.</p>
		</div>
		<?php
	}

	function arm_display_news_notices() {
		$arm_news = get_transient( 'arm_news' );
		if ( false == $arm_news ) {
			$url          = 'https://www.armemberplugin.com/armember_addons/armemberlite_notices.php';
			$raw_response = wp_remote_post(
				$url,
				array(
					'timeout' => 5000,
				)
			);

			if ( ! is_wp_error( $raw_response ) && 200 == $raw_response['response']['code'] ) {

				$news = json_decode( $raw_response['body'], true );

			} else {
				$news = array();
			}

			set_transient( 'arm_news', wp_json_encode( $news ), DAY_IN_SECONDS );
		} else {
			$news = json_decode( $arm_news, true );
		}
		$current_time_date = current_time('timestamp');
		$current_date = date( 'Y-m-d',$current_time_date); //phpcs:ignore

		foreach ( $news as $news_id => $news_data ) {
			$isAlreadyDismissed = get_option( 'arm_' . $news_id . '_is_dismissed' );

			if ( '' == $isAlreadyDismissed ) {
				$class      = 'notice notice-warning arm-news-notice is-dismissible';
				$message    = $news_data['description'];
				$start_date = strtotime( $news_data['start_date'] );
				$end_date   = strtotime( $news_data['end_date'] );
				$wpnonce = wp_create_nonce( 'arm_wp_nonce' );
				$nonce='<input type="hidden" name="arm_wp_nonce" value="'.esc_attr($wpnonce).'"/>';

				$current_timestamp = strtotime( $current_date );

				if ( $current_timestamp >= $start_date && $current_timestamp <= $end_date ) {
					$background_color = ( isset( $news_data['background'] ) && '' != $news_data['background'] ) ? 'background:' . $news_data['background'] . ';' : '';
					$font_color       = ( isset( $news_data['color'] ) && '' != $news_data['color'] ) ? 'color:' . $news_data['color'] . ';' : '';
					$border_color     = ( isset( $news_data['border'] ) && '' != $news_data['border'] ) ? 'border-left-color:' . $news_data['border'] . ';' : '';

					printf(
						'<div class="%1$s" style="%2$s%3$s%4$s" id="%5$s"><p>%6$s</p></div>',
						esc_attr( $class ),
						esc_attr( $background_color ),
						esc_attr( $font_color ),
						esc_attr( $border_color ),
						esc_attr( $news_id ),
						wp_kses( $message, $this->armember_allowed_html_tags() ),
						esc_attr( $nonce )
					);
				}
			}
		}
	}

	function arm_dismiss_news_notice() {
		global $ARMemberLite;
		if( current_user_can( 'administrator') )
		{
			$ARMemberLite->arm_check_user_cap('',1); //phpcs:ignore --Reason:Verifying nonce
			$noticeId = isset( $_POST['notice_id'] ) ? sanitize_text_field($_POST['notice_id']) : ''; //phpcs:ignore
			if ( '' != $noticeId ) {
				update_option( 'arm_' . $noticeId . '_is_dismissed', true );
			}
		}
	}

	function arm_is_gutenberg_active() {
		// Check Gutenberg plugin is installed and activated.
		$gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

		// Version Check Block editor since 5.0.
		$block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

		if ( ! $gutenberg && ! $block_editor ) {
			return false;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
			return true;
		}

		$use_block_editor = get_option( 'classic-editor-replace' ) === 'no-replace';

		return $use_block_editor;
	}

	/**
	 * Check is gutenberg active or not function end
	 */

	function arm_check_is_gutenberg_page()
	{
		$return = false;
		global $ARMemberLite, $arm_doing_save_flag;
		if( $ARMemberLite->arm_check_is_elementor_page() )
		{
			$return = false;
		}
		if( function_exists('is_gutenberg_page') )
		{
			if( is_gutenberg_page() )
			{
				$return =  true;
			}
		}
		else 
		{
			if ( function_exists( 'get_current_screen' ) ) {
				$arm_get_current_screen = get_current_screen();
				if( is_object( $arm_get_current_screen ) )
				{
					if ( isset($arm_get_current_screen->base) && $arm_get_current_screen->base==='post' && $this->arm_is_gutenberg_active() ) {
						$return =  true;
					}
				}
			}
			if( doing_action( 'save_post' ) || ( defined("DOING_AUTOSAVE") && DOING_AUTOSAVE ) || !empty($arm_doing_save_flag) )
			{
				$arm_doing_save_flag = 1;
				$return =  true;
			}
		}
		return $return;
	}

	function arm_check_is_elementor_page()
	{
		/* if(is_admin() && ( ( !empty($_REQUEST['action']) && $_REQUEST['action']!='elementor_ajax' ) || !empty($_REQUEST['elementor-preview']) ) )
		{
			return true;
		} */
		if( (!empty($_REQUEST['action']) && $_REQUEST['action']!='elementor_ajax' ) || !empty($_REQUEST['elementor-preview']) ) //phpcs:ignore
		{
			return true;
		}
		return false;
	}



	function wpdbfix() {
		global $wpdb, $arm_db_tables, $ARMemberLite;
		$wpdb->arm_termmeta = $ARMemberLite->tbl_arm_termmeta;
	}

	function arm_init_action() {
		global $wp, $wpdb, $arm_db_tables;
		$this->arm_slugs = $this->arm_page_slugs();
		/**
		 * Start Session
		 */
		ob_start();
		/**
		 * Plugin Hook for `Init` Actions
		 */
		do_action( 'arm_init', $this );
	}

	/**
	 * Hide WordPress Update Notifications In Plugin's Pages
	 */
	function arm_hide_update_notice() {
		global $wp, $wpdb, $arm_lite_errors, $current_user, $ARMemberLite, $pagenow, $arm_slugs;
		if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], (array) $arm_slugs ) && !in_array( $_REQUEST['page'], array( $arm_slugs->arm_setup_wizard ) )) { //phpcs:ignore
			remove_action( 'admin_notices', 'update_nag', 3 );
			remove_action( 'network_admin_notices', 'update_nag', 3 );
			remove_action( 'admin_notices', 'maintenance_nag' );
			remove_action( 'network_admin_notices', 'maintenance_nag' );
			remove_action( 'admin_notices', 'site_admin_notice' );
			remove_action( 'network_admin_notices', 'site_admin_notice' );
			remove_action( 'load-update-core.php', 'wp_update_plugins' );
			add_filter( 'pre_site_transient_update_core', array( $this, 'arm_remove_core_updates' ) );
			add_filter( 'pre_site_transient_update_plugins', array( $this, 'arm_remove_core_updates' ) );
			add_filter( 'pre_site_transient_update_themes', array( $this, 'arm_remove_core_updates' ) );

			add_action( 'admin_notices', array( $this, 'arm_sample_admin_notice__success' ), 1 );

			/* Remove BuddyPress Admin Notices */
			remove_action( 'bp_admin_init', 'bp_core_activation_notice', 1010 );
			if ( ! in_array( $_REQUEST['page'], array( $arm_slugs->manage_forms ) ) ) { //phpcs:ignore
				add_action( 'admin_notices', array( $this, 'arm_admin_notices' ) );
			}
			global  $arm_social_feature;
			if ( in_array( $_REQUEST['page'], array( $arm_slugs->profiles_directories ) ) && ! $arm_social_feature->isSocialFeature ) { //phpcs:ignore
				$armAddonsLink = admin_url( 'admin.php?page=' . $arm_slugs->feature_settings . '&arm_activate_social_feature=1' );
				wp_safe_redirect( $armAddonsLink );
				exit;
			}
		}
	}

	function arm_admin_notices() {
		global $wp, $wpdb, $arm_lite_errors, $ARMemberLite, $pagenow, $arm_global_settings;
		$notice_html = '';
		$notices     = array();
		$notices     = apply_filters( 'arm_display_admin_notices', $notices );

		if ( ! empty( $notices ) ) {
			$notice_html .= '<div class="arm_admin_notices_container">';
			$notice_html .= '<ul class="arm_admin_notices">';
			foreach ( $notices as $notice ) {
				$notice_html .= '<li class="arm_notice arm_notice_' . esc_attr($notice['type']) . '">' . $notice['message'] . '</li>';
			}
			$notice_html .= '</ul>';
			$notice_html .= '<div class="armclear"></div></div>';
		}

		$arm_get_php_version = ( function_exists( 'phpversion' ) ) ? phpversion() : 0;
		if ( version_compare( $arm_get_php_version, '5.6', '<' ) ) {
			$notice_html .= '<div class="notice notice-warning" style="display:block;">';
			$notice_html .= '<p>' . esc_html__( 'ARMember Lite recommend to use Minimum PHP version 5.6 or greater.', 'armember-membership' ) . '</p>';
			$notice_html .= '</div>';
		}
		if ( ! empty( $arm_global_settings->global_settings['enable_crop'] ) ) {
			if ( ! function_exists( 'gd_info' ) ) {
				$notice_html .= '<div class="notice notice-error" style="display:block;">';
				$notice_html .= '<p>' . esc_html__( "ARMember Lite requires PHP GD Extension module at the server. And it seems that it's not installed or activated. Please contact your hosting provider for the same.", 'armember-membership' ) . '</p>';
				$notice_html .= '</div>';
			}
		}
		echo $notice_html; //phpcs:ignore
	}

	function arm_set_message( $type = 'error', $message = '' ) {
		global $wp, $wpdb, $arm_lite_errors, $ARMemberLite, $pagenow;
		if ( ! empty( $message ) ) {
			$ARMemberLite->arm_session_start();
			$_SESSION['arm_message'][] = array(
				'type'    => $type,
				'message' => $message,
			);
		}
		return;
	}

	function arm_remove_core_updates() {
		global $wp_version;
		return (object) array(
			'last_checked'    => time(),
			'version_checked' => $wp_version,
		);
	}

	function arm_set_adminmenu() {
		global $menu, $submenu, $parent_file, $ARMemberLite;
		$ARMemberLite->arm_session_start();
		if ( isset( $_SESSION['arm_admin_menus'] ) ) {
			unset( $_SESSION['arm_admin_menus'] );
		}
		$_SESSION['arm_admin_menus'] = array(
			'main_menu' => $menu,
			'submenu'   => $submenu,
		);
		if ( isset( $submenu['arm_manage_members'] ) && ! empty( $submenu['arm_manage_members'] ) ) {
			$armAdminMenuScript  = '<script type="text/javascript">';
			$armAdminMenuScript .= 'jQuery(document).ready(function ($) {';
			$armAdminMenuScript .= 'jQuery("#toplevel_page_arm_manage_members").find("ul li").each(function(){
                
					var thisLI = jQuery(this);
					thisLI.addClass("arm-submenu-item");
					var thisLinkHref = thisLI.find("a").attr("href");
					if(thisLinkHref != "" && thisLinkHref != undefined){
						var thisLinkClass = thisLinkHref.replace("admin.php?page=","");
						thisLI.addClass(thisLinkClass);
					}
				});
				jQuery(".arm_documentation a, .arm-submenu-item a[href=\"admin.php?page=arm_documentation\"]").attr("target", "_blank");';

			$docLink             = MEMBERSHIPLITE_DOCUMENTATION_URL;
			$armAdminMenuScript .= 'jQuery(".arm_documentation a, .arm-submenu-item a[href=\"admin.php?page=arm_documentation\"]").attr("href", "' . $docLink . '");';

			$armAdminMenuScript .= '});';

			$armAdminMenuScript .= '</script>';
			$armAdminMenuScript .= '<style type="text/css">';
			global  $arm_social_feature;

			if ( ! $arm_social_feature->isSocialFeature ) {
				$armAdminMenuScript .= '.arm-submenu-item.arm_profiles_directories{display:none;}';
			}

			$armAdminMenuScript .= '.arm-submenu-item.arm_feature_settings a{color:#ffff00 !important;}';
			$armAdminMenuScript .= '</style>';
			echo $armAdminMenuScript; //phpcs:ignore
		}
	}

	function ARM_EndSession() {
		//@session_destroy();
		$arm_action = isset($_POST['arm_action']) ? sanitize_text_field($_POST['arm_action']) : ''; //phpcs:ignore
        
		if(isset($_SESSION['arm_site_permalink_is_changed'])) { unset($_SESSION['arm_site_permalink_is_changed']); }
        if(isset($_SESSION['arm_restricted_page_url'])) { unset($_SESSION['arm_restricted_page_url']); }
        if(isset($_SESSION['imported_users'])) { unset($_SESSION['imported_users']); }
        if(isset($_SESSION['arm_member_addon'])) { unset($_SESSION['arm_member_addon']); }
        if(isset($_SESSION['arm_message'])) { unset($_SESSION['arm_message']); }
        if(isset($_SESSION['arm_admin_menus'])) { unset($_SESSION['arm_admin_menus']); }
		
		if($arm_action!="change-password")
		{
			if(isset($_SESSION['ARM_FILTER_INPUT'])) { unset($_SESSION['ARM_FILTER_INPUT']); };
			if(isset($_SESSION['ARM_VALIDATE_SCRIPT'])) { unset($_SESSION['ARM_VALIDATE_SCRIPT']); }
		}
	}

	/**
	 * Loading plugin text domain
	 */
	function arm_load_textdomain() {
		load_plugin_textdomain( 'armember-membership', false, dirname( plugin_basename( MEMBERSHIPLITE_DIR_NAME.'/armember-membership.php' ) ) . '/languages/' );
		global $armPrimaryStatus, $armSecondaryStatus, $arm_lite_widget_effects, $armlite_default_user_details_text;
		$armPrimaryStatus   = array(
			'1' => esc_html__( 'Active', 'armember-membership' ),
			'2' => esc_html__( 'Inactive', 'armember-membership' ),
			'3' => esc_html__( 'Pending', 'armember-membership' ),
			'4' => esc_html__( 'Terminated', 'armember-membership' ),
		);
		$armSecondaryStatus = array(
			'0' => esc_html__( 'by admin', 'armember-membership' ),
			'1' => esc_html__( 'Account Closed', 'armember-membership' ),
			'2' => esc_html__( 'Suspended', 'armember-membership' ),
			'3' => esc_html__( 'Expired', 'armember-membership' ),
			'4' => esc_html__( 'User Cancelled', 'armember-membership' ),
			'5' => esc_html__( 'Payment Failed', 'armember-membership' ),
			'6' => esc_html__( 'Cancelled', 'armember-membership' ),
		);

		$arm_lite_widget_effects = array(
			'slide'        => esc_html__( 'Slide', 'armember-membership' ),
			'crossfade'    => esc_html__( 'Fade', 'armember-membership' ),
			'directscroll' => esc_html__( 'Direct Scroll', 'armember-membership' ),
			'cover'        => esc_html__( 'Cover', 'armember-membership' ),
			'uncover'      => esc_html__( 'Uncover', 'armember-membership' ),
		);

		$armlite_default_user_details_text = esc_html__( 'Unknown', 'armember-membership' );
	}

	/* Setting Capabilities for user */

	function arm_capabilities() {
		$cap = array(
			'arm_manage_subscriptions'	 =>	esc_html__('Manage Subscriptions', 'armember-membership'),
			'arm_manage_members'             => esc_html__( 'Manage Members', 'armember-membership' ),
			'arm_manage_plans'               => esc_html__( 'Manage Plans', 'armember-membership' ),
			'arm_manage_setups'              => esc_html__( 'Manage Setups', 'armember-membership' ),
			'arm_manage_forms'               => esc_html__( 'Manage Form Settings', 'armember-membership' ),
			'arm_manage_access_rules'        => esc_html__( 'Manage Access Rules', 'armember-membership' ),

			'arm_manage_transactions'        => esc_html__( 'Manage Transactions', 'armember-membership' ),
			'arm_manage_email_notifications' => esc_html__( 'Manage Email Notifications', 'armember-membership' ),
			'arm_manage_communication'       => esc_html__( 'Manage Communication', 'armember-membership' ),
			'arm_manage_member_templates'    => esc_html__( 'Manage Member Templates', 'armember-membership' ),
			'arm_manage_general_settings'    => esc_html__( 'Manage General Settings', 'armember-membership' ),
			'arm_manage_feature_settings'    => esc_html__( 'Manage Feature Settings', 'armember-membership' ),
			'arm_manage_block_settings'      => esc_html__( 'Manage Block Settings', 'armember-membership' ),

			'arm_manage_payment_gateways'    => esc_html__( 'Manage Payment Gateways', 'armember-membership' ),
			'arm_import_export'              => esc_html__( 'Manage Import/Export', 'armember-membership' ),
			'arm_growth_plugins'             => esc_html__( 'Growth Plugins', 'armember-membership' ),

		);
		return $cap;
	}

	function arm_page_slugs() {
		global $ARMemberLite, $arm_slugs;
		$arm_slugs = new stdClass();
		$arm_current_date_for_bf_popup = current_time('timestamp',true); //GMT-0 Timezone
		$arm_bf_start_time = "1700483400";
		$arm_bf_end_time = "1701541800";
		$arm_black_friday_slug = 'arm_upgrade_to_premium';
		/* Admin-Pages-Slug */
		$arm_slugs->main             = 'arm_manage_members';
		$arm_slugs->manage_members   = 'arm_manage_members';
		$arm_slugs->manage_plans     = 'arm_manage_plans';
		$arm_slugs->membership_setup = 'arm_membership_setup';
		$arm_slugs->manage_forms     = 'arm_manage_forms';
		$arm_slugs->access_rules     = 'arm_access_rules';
		$arm_slugs->manage_subscriptions = 'arm_manage_subscriptions';
		$arm_slugs->transactions        = 'arm_transactions';
		$arm_slugs->email_notifications = 'arm_email_notifications';

		$arm_slugs->general_settings     = 'arm_general_settings';
		$arm_slugs->feature_settings     = 'arm_feature_settings';
		$arm_slugs->documentation        = 'arm_documentation';
		$arm_slugs->arm_upgrade_to_premium = $arm_black_friday_slug;
		$arm_slugs->profiles_directories = 'arm_profiles_directories';
		$arm_slugs->arm_setup_wizard = 'arm_setup_wizard';
		$arm_slugs->arm_growth_plugins = 'arm_growth_plugins';

		return $arm_slugs;
	}

	/**
	 * Setting Menu Position
	 */
	function get_free_menu_position( $start, $increment = 0.1 ) {
		foreach ( $GLOBALS['menu'] as $key => $menu ) {
			$menus_positions[] = floatval( $key );
		}
		if ( ! in_array( $start, $menus_positions ) ) {
			$start = strval( $start );
			return $start;
		} else {
			$start += $increment;
		}
		/* the position is already reserved find the closet one */
		while ( in_array( $start, $menus_positions ) ) {
			$start += $increment;
		}
		$start = strval( $start );
		return $start;
	}

	public static function arm_is_pro_active(){
		if( !function_exists('is_plugin_active') ){
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}
		$plugin_slug = 'armember/armember.php';
		return is_plugin_active( $plugin_slug );
	}

	function armPluginActionLinks( $links, $file ) {
		global $wp, $wpdb, $ARMemberLite, $arm_slugs;
		if ( $file == plugin_basename( MEMBERSHIPLITE_DIR_NAME.'/armember-membership.php' ) ) {
			if ( isset( $links['deactivate'] ) ) {
				$deactivation_link = $links['deactivate'];
				// Insert an onClick action to allow form before deactivating
				$deactivation_link   = str_replace(
					'<a ',
					'<div class="armlite-deactivate-form-wrapper">
                         <span class="armlite-deactivate-form " id="armlite-deactivate-form-armember-membership"></span>
                     </div><a id="armlite-deactivate-link-armember-membership" ',
					$deactivation_link
				);
				$links['deactivate'] = $deactivation_link;
			}
			if(!$this->is_arm_pro_active) {
				$armAddonsLink = admin_url( 'admin.php?page=' . $arm_slugs->feature_settings );
				$link          = '<a title="' . esc_attr__( 'Add-ons', 'armember-membership' ) . '" href="' . esc_url( $armAddonsLink ) . '">' . esc_html__( 'Add-ons', 'armember-membership' ) . '</a>';
				$link          = '<a title="' . esc_attr__( 'Upgrade To Pro', 'armember-membership' ) . '" href="https://www.armemberplugin.com/pricing/" style="font-weight:bold;">' . esc_html__( 'Upgrade To Pro', 'armember-membership' ) . '</a>';
				array_unshift( $links, $link ); /* Add Link To First Position */
			}
		}
		return $links;
	}

	function arm_admin_body_class( $classes ) {
		global $pagenow, $arm_slugs;
		if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], (array) $arm_slugs ) ) { //phpcs:ignore
			$classes .= ' arm_wpadmin_page ';
		}
		return $classes;
	}

	/**
	 * Adding Membership Admin Menu(s)
	 */
	function arm_menu() {
		global $wp, $wpdb, $current_user, $arm_lite_errors, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_social_feature, $arm_membership_setup;

		$armlite_is_wizard_complete = get_option('arm_lite_is_wizard_complete');

		$place = $this->get_free_menu_position( 26.1, 0.3 );
		if ( version_compare( $GLOBALS['wp_version'], '3.8', '<' ) ) {
			echo "<style type='text/css'>.toplevel_page_arm_manage_members .wp-menu-image img{margin-top:-4px !important;}.toplevel_page_arm_manage_members .wp-menu-image .wp-menu-name{padding-left:30px !important;;}</style>";
		}
		if(empty($armlite_is_wizard_complete) || $armlite_is_wizard_complete == 0)
        {
            $arm_menu_hook = add_menu_page('ARMember', esc_html__('ARMember Lite', 'armember-membership'), 'arm_manage_members', $arm_slugs->arm_setup_wizard, array($this, 'route'), MEMBERSHIPLITE_IMAGES_URL . '/armember_menu_icon.png', $place);
        }
        else{
            $arm_menu_hook    = add_menu_page( 'ARMember Lite', esc_html__( 'ARMember Lite', 'armember-membership' ), 'arm_manage_members', $arm_slugs->main, array( $this, 'route' ), MEMBERSHIPLITE_IMAGES_URL . '/armember_menu_icon.png', $place );
        }
		
		$admin_menu_items = array(
			$arm_slugs->manage_members       => array(
				'name'       => esc_html__( 'Manage Members', 'armember-membership' ),
				'title'      => esc_html__( 'Manage Members', 'armember-membership' ),
				'capability' => 'arm_manage_members',
			),
			$arm_slugs->manage_plans         => array(
				'name'       => esc_html__( 'Manage Plans', 'armember-membership' ),
				'title'      => esc_html__( 'Manage Plans', 'armember-membership' ),
				'capability' => 'arm_manage_plans',
			),
			$arm_slugs->membership_setup     => array(
				'name'       => esc_html__( 'Configure Plan + Signup Page', 'armember-membership' ),
				'title'      => esc_html__( 'Configure Plan + Signup Page', 'armember-membership' ),
				'capability' => 'arm_manage_setups',
			),
			$arm_slugs->manage_forms         => array(
				'name'       => esc_html__( 'Manage Forms', 'armember-membership' ),
				'title'      => esc_html__( 'Manage Forms', 'armember-membership' ),
				'capability' => 'arm_manage_forms',
			),
			$arm_slugs->access_rules         => array(
				'name'       => esc_html__( 'Content Access Rules', 'armember-membership' ),
				'title'      => esc_html__( 'Content Access Rules', 'armember-membership' ),
				'capability' => 'arm_manage_access_rules',
			),
			$arm_slugs->manage_subscriptions => array(
	                'name' => esc_html__('Manage Subscriptions', 'armember-membership'),
	                'title' => esc_html__('Manage Subscriptions', 'armember-membership'),
	                'capability' => 'arm_manage_subscriptions'
	            ),
			$arm_slugs->transactions         => array(
				'name'       => esc_html__( 'Payment History', 'armember-membership' ),
				'title'      => esc_html__( 'Payment History', 'armember-membership' ),
				'capability' => 'arm_manage_transactions',
			),
			$arm_slugs->email_notifications  => array(
				'name'       => esc_html__( 'Email Notifications', 'armember-membership' ),
				'title'      => esc_html__( 'Email Notifications', 'armember-membership' ),
				'capability' => 'arm_manage_email_notifications',
			),

			$arm_slugs->profiles_directories => array(
				'name'       => esc_html__( 'Profiles & Directories', 'armember-membership' ),
				'title'      => esc_html__( 'Profiles & Directories', 'armember-membership' ),
				'capability' => 'arm_manage_member_templates',
			),
			$arm_slugs->general_settings     => array(
				'name'       => esc_html__( 'General Settings', 'armember-membership' ),
				'title'      => esc_html__( 'General Settings', 'armember-membership' ),
				'capability' => 'arm_manage_general_settings',
			),

		);
		foreach ( $admin_menu_items as $slug => $menu ) {

			if ( $slug == $arm_slugs->membership_setup ) {
				$total_setups = $arm_membership_setup->arm_total_setups();
				if ( $total_setups < 1 ) {
					$menu['title'] = '<span style="color: #53E2F3">' . $menu['title'] . '</span>';
				}
			}
			$armSubMenuHook = add_submenu_page( $arm_slugs->main, $menu['name'], $menu['title'], $menu['capability'], $slug, array( $this, 'route' ) );
		}
		do_action( 'arm_before_last_menu' );
	}

	function arm_set_last_menu() {
		global $wp, $wpdb, $ARMemberLite, $arm_slugs, $arm_membership_setup;
		$admin_menu_items = array(
			$arm_slugs->feature_settings => array(
				'name'       => esc_html__( 'Add-ons', 'armember-membership' ),
				'title'      => esc_html__( 'Add-ons', 'armember-membership' ),
				'capability' => 'arm_manage_feature_settings',
			),
			$arm_slugs->documentation    => array(
				'name'       => esc_html__( 'Documentation', 'armember-membership' ),
				'title'      => esc_html__( 'Documentation', 'armember-membership' ),
				'capability' => 'arm_manage_members',
			),
			$arm_slugs->arm_growth_plugins    => array(
				'name'       => esc_html__( 'Growth Plugins', 'armember-membership' ),
				'title'      => esc_html__( 'Growth Plugins', 'armember-membership' ),
				'capability' => 'arm_growth_plugins',
			),
		);
		foreach ( $admin_menu_items as $slug => $menu ) {
			if ( $slug == $arm_slugs->membership_setup ) {
				$total_setups = $arm_membership_setup->arm_total_setups();
				if ( $total_setups < 1 ) {
					$menu['title'] = '<span style="color: #53E2F3">' . $menu['title'] . '</span>';
				}
			}
			$armSubMenuHook = add_submenu_page( $arm_slugs->main, $menu['name'], $menu['title'], $menu['capability'], $slug, array( $this, 'route' ) );
		}
		$this->arm_set_premium_link();
	}
	function arm_set_premium_link(){
		if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_upgrade_to_premium.php' ) ) {
			include MEMBERSHIPLITE_VIEWS_DIR . '/arm_upgrade_to_premium.php';
		}
	}


	function arm_add_debug_bar_menu( $wp_admin_bar ) {
		/* Admin Bar Menu */
		if ( ! current_user_can( 'administrator' ) || MEMBERSHIPLITE_DEBUG_LOG == false ) {
			return;
		}
		$args = array(
			'id'     => 'arm_debug_menu',
			'title'  => esc_html__( 'ARMember Debug', 'armember-membership' ),
			'parent' => 'top-secondary',
			'href'   => '#',
			'meta'   => array(
				'class' => 'armember_admin_bar_debug_menu',
			),
		);
		echo "<style type='text/css'>";
		echo '.armember_admin_bar_debug_menu{
				background:#ff9a8d !Important;
			}';
		echo '</style>';
		$wp_admin_bar->add_menu( $args );
	}

	/**
	 * Display Admin Page View
	 */
	function route() {
		global $wp, $wpdb, $arm_lite_errors, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings;
		if ( isset( $_REQUEST['page'] ) ) { //phpcs:ignore
			$pageWrapperClass = '';
			if ( is_rtl() ) {
				$pageWrapperClass = 'arm_page_rtl';
			}
			echo '<div class="arm_page_wrapper ' . esc_html($pageWrapperClass) . '" id="arm_page_wrapper">';
			$requested_page = sanitize_text_field( $_REQUEST['page'] ); //phpcs:ignore
			do_action( 'arm_admin_messages', $requested_page );
			$GET_ACTION = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : ''; //phpcs:ignore
			$GET_id     = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : ''; //phpcs:ignore
			switch ( $requested_page ) {
				case $arm_slugs->main:
				case $arm_slugs->manage_members:
					if ( isset( $GET_ACTION ) && in_array( $GET_ACTION, array( 'new', 'edit_member', 'view_member' ) ) ) {
						if ( $GET_ACTION == 'view_member' && ! empty( $GET_id ) && file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_view_member.php' ) ) {
							include MEMBERSHIPLITE_VIEWS_DIR . '/arm_view_member.php';
						} elseif ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_member_add.php' ) ) {
							include MEMBERSHIPLITE_VIEWS_DIR . '/arm_member_add.php';
						}
					} else {
						if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_members_list.php' ) ) {
							include MEMBERSHIPLITE_VIEWS_DIR . '/arm_members_list.php';
						}
					}
					break;
				case $arm_slugs->arm_setup_wizard:
					if(file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_setup_wizard.php'))
					{
						include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_setup_wizard.php');
					}
					break;
				case $arm_slugs->manage_plans:
					if ( isset( $GET_ACTION ) && in_array( $GET_ACTION, array( 'new', 'edit_plan' ) ) ) {
						if ( $GET_ACTION == 'edit_plan' && ! isset( $GET_id ) && file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_list.php' ) ) {
							include MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_list.php';
						} elseif ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_add.php' ) ) {
							include MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_add.php';
						}
					} else {
						if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_list.php' ) ) {
							include MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_list.php';
						}
					}
					break;
				case $arm_slugs->membership_setup:
					if ( isset( $GET_ACTION ) && in_array( $GET_ACTION, array( 'new_setup', 'edit_setup', 'new_setup_old' ) ) ) {
						if ( $GET_ACTION == 'new_setup_old' ) {
							if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add_old.php' ) ) {
								include MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add_old.php';
							}
						} elseif ( $GET_ACTION == 'edit_setup' && isset( $GET_id ) && ! empty( $GET_id ) && $GET_id != 0 ) {
							if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php' ) ) {
								include MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php';
							}
						} else {
							if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php' ) ) {
								include MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php';
							}
						}
					} else {
						global $arm_membership_setup;
						$total_setups = $arm_membership_setup->arm_total_setups();
						if ( $total_setups < 1 && file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php' ) ) {
							include MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php';
						} elseif ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_list.php' ) ) {
							include MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_list.php';
						}
					}
					break;
				case $arm_slugs->manage_forms:
					if ( isset( $GET_ACTION ) && ( $GET_ACTION == 'edit_form' ) && !empty( $_GET['form_id'] ) && is_numeric( $_GET['form_id'] ) && file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_form_editor.php' ) ) { //phpcs:ignore
						include MEMBERSHIPLITE_VIEWS_DIR . '/arm_form_editor.php'; 
					} else {
						if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_manage_forms.php' ) ) {
							include MEMBERSHIPLITE_VIEWS_DIR . '/arm_manage_forms.php';
						}
					}
					break;
				case $arm_slugs->access_rules:
					if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_access_rules.php' ) ) {
						include MEMBERSHIPLITE_VIEWS_DIR . '/arm_access_rules.php';
					}
					break;
				case $arm_slugs->manage_subscriptions:
					if (file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_manage_subscription_list.php')) {
						include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_manage_subscription_list.php');
					}
				break;
				case $arm_slugs->transactions:
					if ( isset( $GET_ACTION ) && in_array( $GET_ACTION, array( 'new', 'edit_payment' ) ) ) {
						if ( $GET_ACTION == 'edit_payment' && ! isset( $GET_id ) && file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions.php' ) ) {
							include MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions.php';
						} elseif ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions_add.php' ) ) {
							include MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions_add.php';
						}
					} else {
						if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions.php' ) ) {
							include MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions.php';
						}
					}
					break;
				case $arm_slugs->email_notifications:
					if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_email_notification.php' ) ) {
						include MEMBERSHIPLITE_VIEWS_DIR . '/arm_email_notification.php';
					}
					break;

				case $arm_slugs->general_settings:
					if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_general_settings.php' ) ) {
						include MEMBERSHIPLITE_VIEWS_DIR . '/arm_general_settings.php';
					}
					break;
				case $arm_slugs->feature_settings:
					if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_feature_settings.php' ) ) {
						include MEMBERSHIPLITE_VIEWS_DIR . '/arm_feature_settings.php';
					}
					break;
				case $arm_slugs->documentation:
					wp_redirect( MEMBERSHIPLITE_DOCUMENTATION_URL );
					die();
					break;
				case $arm_slugs->arm_growth_plugins:
					if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_growth_plugins.php' ) ) {
						include MEMBERSHIPLITE_VIEWS_DIR . '/arm_growth_plugins.php';
					}
					break;
					
				case $arm_slugs->profiles_directories:
					if ( isset( $GET_ACTION ) && ( $GET_ACTION == 'add_profile' || $GET_ACTION == 'edit_profile' ) && file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_profile_editor.php' ) ) {
						include MEMBERSHIPLITE_VIEWS_DIR . '/arm_profile_editor.php';
					} else {
						if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_profiles_directories.php' ) ) {
							include MEMBERSHIPLITE_VIEWS_DIR . '/arm_profiles_directories.php';
						}
					}
					break;
				case $arm_slugs->arm_upgrade_to_premium:
					if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_upgrade_to_premium.php' ) ) {
						include MEMBERSHIPLITE_VIEWS_DIR . '/arm_upgrade_to_premium.php';
					}
					break;

				default:
					break;
			}
			echo '</div>';
		} else {
			/* No Action */
		}
	}

	/* Setting Admin CSS  */

	function set_css() {
		global $arm_slugs,$arm_lite_version;
		/* Plugin Style */
		wp_register_style( 'arm_admin_wp_css', MEMBERSHIPLITE_URL . '/css/arm_admin_wp_css.css', array(), MEMBERSHIPLITE_VERSION );
		wp_register_style( 'arm_admin_css', MEMBERSHIPLITE_URL . '/css/arm_admin.css', array(), MEMBERSHIPLITE_VERSION );
		wp_register_style( 'arm_form_style_css', MEMBERSHIPLITE_URL . '/css/arm_form_style.css', array(), MEMBERSHIPLITE_VERSION );
		wp_register_style('arm_admin_setup_css', MEMBERSHIPLITE_URL . '/css/arm_lite_admin_setup_wizard.css', array(), MEMBERSHIPLITE_VERSION);
		wp_register_style( 'arm-font-awesome-css', MEMBERSHIPLITE_URL . '/css/arm-font-awesome.css', array(), MEMBERSHIPLITE_VERSION );
		wp_register_style( 'arm-font-awesome-mini-css', MEMBERSHIPLITE_URL . '/css/arm-font-awesome-mini.css', array(), MEMBERSHIPLITE_VERSION );

		/* For chosen select box */
		wp_register_style( 'arm_chosen_selectbox', MEMBERSHIPLITE_URL . '/css/chosen.css', array(), MEMBERSHIPLITE_VERSION );

		/* For bootstrap datetime picker */

		wp_register_style( 'arm_admin_growth_plugins_css', MEMBERSHIPLITE_URL . '/css/arm_admin_growth_plugins.css', array(), MEMBERSHIPLITE_VERSION );

		wp_register_style( 'arm_bootstrap_all_css', MEMBERSHIPLITE_URL . '/bootstrap/css/bootstrap_all.css', array(), MEMBERSHIPLITE_VERSION );
		// version compare need to insert
		/*Admin view Template Popup*/
		wp_register_style( 'arm_directory_popup', MEMBERSHIPLITE_VIEWS_URL . '/templates/arm_directory_popup.css', array(), MEMBERSHIPLITE_VERSION );

		wp_register_style( 'arm_front_components_base-controls', MEMBERSHIPLITE_URL . '/assets/css/front/components/_base-controls.css', array(), MEMBERSHIPLITE_VERSION );

		wp_register_style( 'arm_front_components_form-style_base', MEMBERSHIPLITE_URL . '/assets/css/front/components/form-style/_base.css', array(), MEMBERSHIPLITE_VERSION );

		wp_register_style( 'arm_front_components_form-style__arm-style-default', MEMBERSHIPLITE_URL . '/assets/css/front/components/form-style/_arm-style-default.css', array(), MEMBERSHIPLITE_VERSION );

		wp_register_style( 'arm_front_components_form-style__arm-style-material', MEMBERSHIPLITE_URL . '/assets/css/front/components/form-style/_arm-style-material.css', array(), MEMBERSHIPLITE_VERSION );

		wp_register_style( 'arm_front_components_form-style__arm-style-outline-material', MEMBERSHIPLITE_URL . '/assets/css/front/components/form-style/_arm-style-outline-material.css', array(), MEMBERSHIPLITE_VERSION );

		wp_register_style( 'arm_front_components_form-style__arm-style-rounded', MEMBERSHIPLITE_URL . '/assets/css/front/components/form-style/_arm-style-rounded.css', array(), MEMBERSHIPLITE_VERSION );

		wp_register_style( 'arm_front_component_css', MEMBERSHIPLITE_URL . '/assets/css/front/arm_front.css', array(), MEMBERSHIPLITE_VERSION );
		$arm_admin_page_name = ! empty( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : ''; //phpcs:ignore

		if ( ! empty( $arm_admin_page_name ) && ( preg_match( '/arm_*/', $arm_admin_page_name ) || $arm_admin_page_name == 'badges_achievements' ) ) {
			wp_deregister_style( 'datatables' );
			wp_dequeue_style( 'datatables' );

			wp_register_style( 'datatables', MEMBERSHIPLITE_URL . '/datatables/media/css/datatables.css', array(), MEMBERSHIPLITE_VERSION );
		}

		/* Add Style for menu icon image. */
		//echo '<style type="text/css"> .toplevel_page_armember .wp-menu-image img,.toplevel_page_arm_setup_wizard .wp-menu-image img, .toplevel_page_arm_manage_members .wp-menu-image img{padding: 5px !important;} .arm_vc_icon{background-image:url(' . MEMBERSHIPLITE_IMAGES_URL . '/armember_menu_icon.png) !important;}</style>'; //phpcs:ignore
		wp_enqueue_style( 'arm_admin_wp_css');
		/* Add CSS file only for plugin pages. */
		if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], (array) $arm_slugs ) ) { //phpcs:ignore
			wp_enqueue_style( 'arm_admin_css' );
			wp_enqueue_style( 'arm_form_style_css' );

			if ( in_array( $_REQUEST['page'], array( $arm_slugs->manage_members, $arm_slugs->manage_forms ) ) ) { //phpcs:ignore
				wp_enqueue_style( 'arm-font-awesome-css' );

				if ( $_REQUEST['page'] == $arm_slugs->manage_forms ) { //phpcs:ignore
					wp_enqueue_style( 'arm_front_components_base-controls' );
					wp_enqueue_style( 'arm_front_components_form-style_base' );
					wp_enqueue_style( 'arm_front_components_form-style__arm-style-default' );

					// wp_enqueue_style('arm-font-awesome');

					wp_enqueue_style( 'arm_front_components_form-style__arm-style-material' );
					wp_enqueue_style( 'arm_front_components_form-style__arm-style-outline-material' );
					wp_enqueue_style( 'arm_front_components_form-style__arm-style-rounded' );

					wp_enqueue_style( 'arm_front_component_css' );
					wp_enqueue_style( 'arm_custom_component_css' );
				}
			} else {
				wp_enqueue_style( 'arm-font-awesome-mini-css' );
			}
			if ( in_array( $_REQUEST['page'], array( $arm_slugs->general_settings, $arm_slugs->manage_members,  $arm_slugs->manage_plans, $arm_slugs->arm_setup_wizard,$arm_slugs->email_notifications, $arm_slugs->manage_subscriptions,$arm_slugs->profiles_directories, $arm_slugs->access_rules, $arm_slugs->transactions ) ) ) { //phpcs:ignore
				wp_enqueue_style( 'arm_chosen_selectbox' );
				wp_enqueue_style( 'datatables' );
			}
			if(in_array($_REQUEST['page'],array($arm_slugs->arm_setup_wizard))) //phpcs:ignore
            {
                wp_enqueue_style('arm_admin_setup_css');
            }
			if ( in_array( $_REQUEST['page'], array( $arm_slugs->general_settings, $arm_slugs->manage_plans, $arm_slugs->manage_subscriptions,$arm_slugs->manage_members, $arm_slugs->transactions,$arm_slugs->arm_setup_wizard ) ) ) { //phpcs:ignore
				wp_enqueue_style( 'arm_bootstrap_all_css' );
			}
			if ( $_REQUEST['page'] == $arm_slugs->manage_members && ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'view_member' ) && ( isset( $_REQUEST['view_type'] ) && $_REQUEST['view_type'] == 'popup' ) ) { //phpcs:ignore
				$inline_style = 'html.wp-toolbar { padding-top: 0px !important; }
                #wpcontent{ margin-left: 0 !important; }
                #wpadminbar { display: none !important; }
                #adminmenumain { display: none !important; }
                .arm_view_member_wrapper { max-width: inherit !important; }';
				wp_add_inline_style( 'arm_admin_css', $inline_style );
			}
			if(in_array($_REQUEST['page'],array($arm_slugs->arm_growth_plugins))) //phpcs:ignore
			{
				wp_enqueue_style( 'arm_admin_growth_plugins_css' );
			}
		}
		if ( is_rtl() ) {
			wp_register_style( 'arm_admin_css-rtl', MEMBERSHIPLITE_URL . '/css/arm_admin_rtl.css', array(), MEMBERSHIPLITE_VERSION );
			wp_enqueue_style( 'arm_admin_css-rtl' );
		}
	}

	/* Setting Admin JavaScript */
	function set_js() {
		global $wp, $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_lite_ajaxurl;

		/* Plugin JS */
		wp_register_script('arm_admin_setup_js', MEMBERSHIPLITE_URL . '/js/arm_lite_admin_setup.js', array(), MEMBERSHIPLITE_VERSION); //phpcs:ignore
		wp_register_script( 'arm_admin_js', MEMBERSHIPLITE_URL . '/js/arm_admin.js', array(), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
		wp_register_script( 'arm_common_js', MEMBERSHIPLITE_URL . '/js/arm_common.js', array(), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
		wp_register_script( 'arm_bpopup', MEMBERSHIPLITE_URL . '/js/jquery.bpopup.min.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
		wp_register_script( 'arm_jeditable', MEMBERSHIPLITE_URL . '/js/jquery.jeditable.mini.js', array(), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
		// wp_register_script('arm_icheck-js', MEMBERSHIPLITE_URL . '/js/icheck.js', array('jquery'), MEMBERSHIPLITE_VERSION); //phpcs:ignore
		wp_register_script( 'arm_colpick-js', MEMBERSHIPLITE_URL . '/js/colpick.min.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
		wp_register_script( 'arm_codemirror-js', MEMBERSHIPLITE_URL . '/js/arm_codemirror.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
		/* Tooltip JS */
		wp_register_script( 'arm_tipso', MEMBERSHIPLITE_URL . '/js/tipso.min.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
		/* Form Validation */
		wp_register_script( 'arm_validate', MEMBERSHIPLITE_URL . '/js/jquery.validate.min.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
		wp_register_script( 'arm_tojson', MEMBERSHIPLITE_URL . '/js/jquery.json.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
		/* For chosen select box */
		wp_register_script( 'arm_chosen_jq_min', MEMBERSHIPLITE_URL . '/js/chosen.jquery.min.js', array(), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
		/* File Upload JS */
		wp_register_script( 'arm_filedrag_import_user_js', MEMBERSHIPLITE_URL . '/js/filedrag/filedrag_import_user.js', array(), MEMBERSHIPLITE_VERSION ); //phpcs:ignore

		wp_register_script( 'arm_file_upload_js', MEMBERSHIPLITE_URL . '/js/arm_file_upload_js.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
		wp_register_script( 'arm_admin_file_upload_js', MEMBERSHIPLITE_URL . '/js/arm_admin_file_upload_js.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION ); //phpcs:ignore

		/* For bootstrap datetime picker js */
		wp_register_script( 'arm_bootstrap_js', MEMBERSHIPLITE_URL . '/bootstrap/js/bootstrap.min.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION ); //phpcs:ignore

		wp_register_script( 'arm_bootstrap_datepicker_with_locale', MEMBERSHIPLITE_URL . '/bootstrap/js/bootstrap-datetimepicker-with-locale.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
		
		wp_register_script( 'arm_admin_chart', MEMBERSHIPLITE_URL . '/js/arm_admin_chart.js', array(), MEMBERSHIPLITE_VERSION ); //phpcs:ignore

		$arm_admin_page_name = ! empty( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : ''; //phpcs:ignore
		if ( ! empty( $arm_admin_page_name ) && ( preg_match( '/arm_*/', $arm_admin_page_name ) || $arm_admin_page_name == 'badges_achievements' ) ) {
			wp_deregister_script( 'datatables' );
			wp_dequeue_script( 'datatables' );

			wp_deregister_script( 'buttons-colvis' );
			wp_dequeue_script( 'buttons-colvis' );

			wp_deregister_script( 'fixedcolumns' );
			wp_dequeue_script( 'fixedcolumns' );

			wp_deregister_script( 'fourbutton' );
			wp_dequeue_script( 'fourbutton' );

			wp_register_script( 'datatables', MEMBERSHIPLITE_URL . '/datatables/media/js/datatables.js', array(), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
			wp_register_script( 'buttons-colvis', MEMBERSHIPLITE_URL . '/datatables/media/js/buttons.colVis.js', array(), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
			wp_register_script( 'fixedcolumns', MEMBERSHIPLITE_URL . '/datatables/media/js/FixedColumns.js', array(), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
			wp_register_script( 'fourbutton', MEMBERSHIPLITE_URL . '/datatables/media/js/four_button.js', array(), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
		}
		if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], (array) $arm_slugs ) ) { //phpcs:ignore
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'arm_tojson' );
			wp_enqueue_script( 'arm_icheck-js' );
			wp_enqueue_script( 'arm_validate' );
			/* Main Plugin Back-End JS */
			wp_enqueue_script( 'arm_bpopup' );
			wp_enqueue_script( 'arm_tipso' );
			wp_enqueue_script( 'arm_admin_js' );
			wp_enqueue_script( 'arm_common_js' );
			wp_enqueue_script( 'wp-hooks' );

			/* For the Datatable Design. */
			$dataTablePages = array(
				$arm_slugs->main,
				$arm_slugs->manage_members,
				$arm_slugs->manage_plans,
				$arm_slugs->membership_setup,
				$arm_slugs->access_rules,
				$arm_slugs->manage_subscriptions,
				$arm_slugs->transactions,
				$arm_slugs->email_notifications,
			);
			if ( in_array( $_REQUEST['page'], $dataTablePages ) ) { //phpcs:ignore
				wp_enqueue_script( 'datatables' );
				wp_enqueue_script( 'buttons-colvis' );
				wp_enqueue_script( 'fixedcolumns' );
				wp_enqueue_script( 'fourbutton' );
			}
			if(in_array($_REQUEST['page'],array($arm_slugs->arm_setup_wizard))){ //phpcs:ignore
                wp_enqueue_script('arm_admin_setup_js');
				wp_enqueue_script('jquery-ui-autocomplete');
            }
			if ( in_array( $_REQUEST['page'], array( $arm_slugs->general_settings, $arm_slugs->manage_plans, $arm_slugs->manage_subscriptions,$arm_slugs->membership_setup, $arm_slugs->manage_forms, $arm_slugs->profiles_directories ) ) ) { //phpcs:ignore
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'jquery-ui-draggable' );
			}
			if ( in_array( $_REQUEST['page'], array( $arm_slugs->manage_forms, $arm_slugs->profiles_directories ) ) ) { //phpcs:ignore
				wp_enqueue_script( 'arm_jeditable' );
				wp_enqueue_script( 'arm_colpick-js' );
				wp_enqueue_style( 'arm_colpick-css', MEMBERSHIPLITE_URL . '/css/colpick.css', array(), MEMBERSHIPLITE_VERSION );
			}
			if ( in_array( $_REQUEST['page'], array( $arm_slugs->general_settings, $arm_slugs->membership_setup, $arm_slugs->profiles_directories ) ) ) { //phpcs:ignore
				wp_enqueue_script( 'arm_colpick-js' );
				wp_enqueue_style( 'arm_colpick-css', MEMBERSHIPLITE_URL . '/css/colpick.css', array(), MEMBERSHIPLITE_VERSION );
			}
			if ( in_array( $_REQUEST['page'], array( $arm_slugs->general_settings, $arm_slugs->manage_members, $arm_slugs->manage_forms, $arm_slugs->profiles_directories,$arm_slugs->arm_setup_wizard ) ) ) { //phpcs:ignore
				wp_enqueue_script( 'arm_admin_file_upload_js' );
			}
			if ( in_array( $_REQUEST['page'], array( $arm_slugs->general_settings, $arm_slugs->manage_members, $arm_slugs->manage_plans, $arm_slugs->email_notifications, $arm_slugs->profiles_directories,$arm_slugs->manage_subscriptions,$arm_slugs->arm_setup_wizard ) ) ) { //phpcs:ignore
				wp_enqueue_script( 'arm_chosen_jq_min' );
			}
			if ( in_array( $_REQUEST['page'], array( $arm_slugs->general_settings, $arm_slugs->manage_plans, $arm_slugs->manage_subscriptions,$arm_slugs->manage_members, $arm_slugs->transactions,$arm_slugs->arm_setup_wizard ) ) ) { //phpcs:ignore
				wp_enqueue_script( 'arm_bootstrap_js' );
				wp_enqueue_script( 'arm_bootstrap_datepicker_with_locale' );
			}
			if ( in_array( $_REQUEST['page'], array( $arm_slugs->general_settings ) ) ) { //phpcs:ignore
				wp_enqueue_script( 'arm_filedrag_import_user_js' );
				wp_enqueue_script( 'sack' );
			}
			if ( in_array( $_REQUEST['page'], array( $arm_slugs->manage_members ) ) ) { //phpcs:ignore
				wp_enqueue_script( 'arm_admin_file_upload_js' );
			}
			if (in_array($_REQUEST['page'], array($arm_slugs->transactions,$arm_slugs->manage_subscriptions))) { //phpcs:ignore
				wp_enqueue_script('jquery-ui-autocomplete');
            }
		}
	}


	/* Setting global javascript variables */
	function set_global_javascript_variables() {

		global $arm_lite_ajaxurl;
		$arm_global_css = '__ARMAJAXURL = "' . esc_html($arm_lite_ajaxurl) . '";';
		$arm_global_css .= '__ARMURL = "' . MEMBERSHIPLITE_URL . '";'; //phpcs:ignore
		$arm_global_css .= '__ARMVIEWURL = "' . MEMBERSHIPLITE_VIEWS_URL . '";'; //phpcs:ignore
		$arm_global_css .= '__ARMLITEIMAGEURL = "' . MEMBERSHIPLITE_IMAGES_URL . '";'; //phpcs:ignore
		$arm_global_css .= '__ARMISADMIN = [' . is_admin() . '];'; //phpcs:ignore
		$arm_global_css .= 'loadActivityError = "' . esc_html__( 'There is an error while loading activities, please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'pinterestPermissionError = "' . esc_html__( 'The user chose not to grant permissions or closed the pop-up', 'armember-membership' ) . '";';
		$arm_global_css .= 'pinterestError = "' . esc_html__( 'Oops, there was a problem getting your information', 'armember-membership' ) . '";';
		$arm_global_css .= 'clickToCopyError = "' . esc_html__( 'There is a error while copying, please try again', 'armember-membership' ) . '";';
		$arm_global_css .= 'fbUserLoginError = "' . esc_html__( 'User cancelled login or did not fully authorize.', 'armember-membership' ) . '";';
		$arm_global_css .= 'closeAccountError = "' . esc_html__( 'There is a error while closing account, please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'invalidFileTypeError = "' . esc_html__( 'Sorry, this file type is not permitted for security reasons.', 'armember-membership' ) . '";';
		$arm_global_css .= 'fileSizeError = "' . esc_html__( 'File is not allowed bigger than {SIZE}.', 'armember-membership' ) . '";';
		$arm_global_css .= 'fileUploadError = "' . esc_html__( 'There is an error in uploading file, Please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'coverRemoveConfirm = "' . esc_html__( 'Are you sure you want to remove cover photo?', 'armember-membership' ) . '";';
		$arm_global_css .= 'profileRemoveConfirm = "' . esc_html__( 'Are you sure you want to remove profile photo?', 'armember-membership' ) . '";';
		$arm_global_css .= 'errorPerformingAction = "' . esc_html__( 'There is an error while performing this action, please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'userSubscriptionCancel = "' . esc_html__( "User's subscription has been canceled", 'armember-membership' ) . '";';

		$arm_global_css .= 'ARM_Loding = "' . esc_html__( 'Loading..', 'armember-membership' ) . '";';
		$arm_global_css .= 'Post_Publish ="' . esc_html__( 'After certain time of post is published', 'armember-membership' ) . '";';
		$arm_global_css .= 'Post_Modify ="' . esc_html__( 'After certain time of post is modified', 'armember-membership' ) . '";';

		$arm_global_css .= 'wentwrong ="' . esc_html__( 'Sorry, Something went wrong. Please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'bulkActionError = "' . esc_html__( 'Please select valid action.', 'armember-membership' ) . '";';
		$arm_global_css .= 'bulkRecordsError ="' . esc_html__( 'Please select one or more records.', 'armember-membership' ) . '";';
		$arm_global_css .= 'clearLoginAttempts ="' . esc_html__( 'Login attempts cleared successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'clearLoginHistory ="' . esc_html__( 'Login History cleared successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'nopasswordforimport ="' . esc_html__( 'Password can not be left blank.', 'armember-membership' ) . '";';

		$arm_global_css .= 'delPlansSuccess ="' . esc_html__( 'Plan(s) has been deleted successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delPlansError ="' . esc_html__( 'There is a error while deleting Plan(s), please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delPlanError ="' . esc_html__( 'There is a error while deleting Plan, please try again.', 'armember-membership' ) . '";';

		$arm_global_css .= 'delSetupsSuccess ="' . esc_html__( 'Setup(s) has been deleted successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delSetupsError ="' . esc_html__( 'There is a error while deleting Setup(s), please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delSetupSuccess ="' . esc_html__( 'Setup has been deleted successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delSetupError ="' . esc_html__( 'There is a error while deleting Setup, please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delFormSetSuccess ="' . esc_html__( 'Form Set Deleted Successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delFormSetError ="' . esc_html__( 'There is a error while deleting form set, please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delFormSuccess ="' . esc_html__( 'Form deleted successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delFormError ="' . esc_html__( 'There is a error while deleting form, please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delRuleSuccess ="' . esc_html__( 'Rule has been deleted successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delRuleError ="' . esc_html__( 'There is a error while deleting Rule, please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delRulesSuccess ="' . esc_html__( 'Rule(s) has been deleted successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delRulesError ="' . esc_html__( 'There is a error while deleting Rule(s), please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'prevTransactionError ="' . esc_html__( 'There is a error while generating preview of transaction detail, Please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'invoiceTransactionError ="' . esc_html__( 'There is a error while generating invoice of transaction detail, Please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'prevMemberDetailError ="' . esc_html__( 'There is a error while generating preview of members detail, Please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'prevMemberActivityError ="' . esc_html__( 'There is a error while displaying members activities detail, Please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'prevCustomCssError ="' . esc_html__( 'There is a error while displaying ARMember CSS Class Information, Please Try Again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'prevImportMemberDetailError ="' . esc_html__( 'Please upload appropriate file to import users.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delTransactionSuccess ="' . esc_html__( 'Transaction has been deleted successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delTransactionsSuccess ="' . esc_html__( 'Transaction(s) has been deleted successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delAutoMessageSuccess ="' . esc_html__( 'Message has been deleted successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delAutoMessageError ="' . esc_html__( 'There is a error while deleting Message, please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delAutoMessagesSuccess ="' . esc_html__( 'Message(s) has been deleted successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delAutoMessagesError ="' . esc_html__( 'There is a error while deleting Message(s), please try again.', 'armember-membership' ) . '";';

		$arm_global_css .= 'saveSettingsSuccess ="' . esc_html__( 'Settings has been saved successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'saveSettingsError ="' . esc_html__( 'There is a error while updating settings, please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'saveDefaultRuleSuccess ="' . esc_html__( 'Default Rules Saved Successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'saveDefaultRuleError ="' . esc_html__( 'There is a error while updating rules, please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'saveOptInsSuccess ="' . esc_html__( 'Opt-ins Settings Saved Successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'saveOptInsError ="' . esc_html__( 'There is a error while updating opt-ins settings, please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delOptInsConfirm ="' . esc_html__( 'Are you sure to delete configuration?', 'armember-membership' ) . '";';
		$arm_global_css .= 'delMemberActivityError ="' . esc_html__( 'There is a error while deleting member activities, please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'noTemplateError ="' . esc_html__( 'Template not found.', 'armember-membership' ) . '";';
		$arm_global_css .= 'saveTemplateSuccess ="' . esc_html__( 'Template options has been saved successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'saveTemplateError ="' . esc_html__( 'There is a error while updating template options, please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'prevTemplateError ="' . esc_html__( 'There is a error while generating preview of template, Please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'addTemplateSuccess ="' . esc_html__( 'Template has been added successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'addTemplateError ="' . esc_html__( 'There is a error while adding template, please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delTemplateSuccess ="' . esc_html__( 'Template has been deleted successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'delTemplateError ="' . esc_html__( 'There is a error while deleting template, please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'saveEmailTemplateSuccess ="' . esc_html__( 'Email Template Updated Successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'saveAutoMessageSuccess ="' . esc_html__( 'Message Updated Successfully.', 'armember-membership' ) . '";';

		$arm_global_css .= 'pastDateError ="' . esc_html__( 'Cannot Set Past Dates.', 'armember-membership' ) . '";';
		$arm_global_css .= 'pastStartDateError ="' . esc_html__( 'Start date can not be earlier than current date.', 'armember-membership' ) . '";';
		$arm_global_css .= 'pastExpireDateError ="' . esc_html__( 'Expire date can not be earlier than current date.', 'armember-membership' ) . '";';

		$arm_global_css .= 'uniqueformsetname ="' . esc_html__( 'This Set Name is already exist.', 'armember-membership' ) . '";';
		$arm_global_css .= 'uniquesignupformname ="' . esc_html__( 'This Form Name is already exist.', 'armember-membership' ) . '";';
		$arm_global_css .= 'installAddonError ="' . esc_html__( 'There is an error while installing addon, Please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'installAddonSuccess ="' . esc_html__( 'Addon installed successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'activeAddonError ="' . esc_html__( 'There is an error while activating addon, Please try agina.', 'armember-membership' ) . '";';
		$arm_global_css .= 'activeAddonSuccess ="' . esc_html__( 'Addon activated successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'deactiveAddonSuccess ="' . esc_html__( 'Addon deactivated successfully.', 'armember-membership' ) . '";';
		$arm_global_css .= 'confirmCancelSubscription ="' . esc_html__( 'Are you sure you want to cancel subscription?', 'armember-membership' ) . '";';
		$arm_global_css .= 'errorPerformingAction ="' . esc_html__( 'There is an error while performing this action, please try again.', 'armember-membership' ) . '";';
		$arm_global_css .= 'arm_nothing_found ="' . esc_html__( 'Oops, nothing found.', 'armember-membership' ) . '";';
		$arm_global_css .= 'armEditCurrency ="' . esc_html__( 'Edit', 'armember-membership' ) . '";';
		
		wp_add_inline_script( 'armlite-admin-notice-script-js', $arm_global_css);
		if(!$this->is_arm_pro_active) {
			wp_add_inline_script( 'arm_common_js', $arm_global_css);
		}
		
	}


	/* Setting Frond CSS */

	function set_front_css( $isFrontSection = false, $form_style = '' ) {
		global $wp, $wpdb, $wp_query, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_members_directory,$arm_global_load_js_css_forms;
		/* Main Plugin CSS */
		wp_register_style( 'arm_front_css', MEMBERSHIPLITE_URL . '/css/arm_front.css', array(), MEMBERSHIPLITE_VERSION );
		wp_register_style( 'arm_form_style_css', MEMBERSHIPLITE_URL . '/css/arm_form_style.css', array(), MEMBERSHIPLITE_VERSION );
		/* Font Awesome CSS */
		wp_register_style( 'arm_fontawesome_css', MEMBERSHIPLITE_URL . '/css/arm-font-awesome.css', array(), MEMBERSHIPLITE_VERSION );
		/* For bootstrap datetime picker */
		wp_register_style( 'arm_bootstrap_all_css', MEMBERSHIPLITE_URL . '/bootstrap/css/bootstrap_all.css', array(), MEMBERSHIPLITE_VERSION );
		// version compare need to insert
		wp_register_style( 'arm_front_components_base-controls', MEMBERSHIPLITE_URL . '/assets/css/front/components/_base-controls.css', array(), MEMBERSHIPLITE_VERSION );

		wp_register_style( 'arm_front_components_form-style_base', MEMBERSHIPLITE_URL . '/assets/css/front/components/form-style/_base.css', array(), MEMBERSHIPLITE_VERSION );

		wp_register_style( 'arm_front_components_form-style__arm-style-default', MEMBERSHIPLITE_URL . '/assets/css/front/components/form-style/_arm-style-default.css', array(), MEMBERSHIPLITE_VERSION );

		wp_register_style( 'arm_front_components_form-style__arm-style-material', MEMBERSHIPLITE_URL . '/assets/css/front/components/form-style/_arm-style-material.css', array(), MEMBERSHIPLITE_VERSION );

		wp_register_style( 'arm_front_components_form-style__arm-style-outline-material', MEMBERSHIPLITE_URL . '/assets/css/front/components/form-style/_arm-style-outline-material.css', array(), MEMBERSHIPLITE_VERSION );

		wp_register_style( 'arm_front_components_form-style__arm-style-rounded', MEMBERSHIPLITE_URL . '/assets/css/front/components/form-style/_arm-style-rounded.css', array(), MEMBERSHIPLITE_VERSION );

		// wp_register_style('arm-font-awesome', MEMBERSHIPLITE_URL . '/assets/css/front/libs/fontawesome/arm-font-awesome.css', array(), MEMBERSHIPLITE_VERSION);
		wp_register_style( 'arm_front_component_css', MEMBERSHIPLITE_URL . '/assets/css/front/arm_front.css', array(), MEMBERSHIPLITE_VERSION );
		/* Check Current Front-Page is Membership Page. */
		$is_arm_front_page   = $this->is_arm_front_page();
		$isEnqueueAll        = $arm_global_settings->arm_get_single_global_settings( 'enqueue_all_js_css', 0 );
		$is_arm_form_in_page = $this->is_arm_form_page();
		if ( ( $is_arm_front_page === true || $isEnqueueAll == '1' || $isFrontSection || $form_style != '' ) && ! is_admin() ) {
			wp_enqueue_style( 'arm_front_css' );
			if ( $is_arm_form_in_page || $isFrontSection || $isEnqueueAll == '1' || $form_style != '' ) {
				wp_enqueue_style( 'arm_form_style_css' );
				wp_enqueue_style( 'arm_fontawesome_css' );

				wp_enqueue_style( 'arm_front_components_base-controls' );
				wp_enqueue_style( 'arm_front_components_form-style_base' );
				// wp_enqueue_style('arm-font-awesome');
				$include_materia_outline_style = $include_material_style = $include_rounded_style = $include_standard_style = '';
				if ( $isEnqueueAll != '1' ) {
					if ( ! empty( $is_arm_form_in_page ) && is_array( $is_arm_form_in_page ) ) {
						$is_arm_form_in_page_0_0_arr = isset( $is_arm_form_in_page[0][0] ) ? $is_arm_form_in_page[0][0] : array();
						if ( ! empty( $is_arm_form_in_page_0_0_arr ) && is_array( $is_arm_form_in_page_0_0_arr ) ) {

							foreach ( $is_arm_form_in_page_0_0_arr as $is_arm_form_in_page_0_0_shortcode ) {
								$is_arm_form_in_page_0_0_shortcode = strtolower( $is_arm_form_in_page_0_0_shortcode );

								$array_check_parameter_arr = array( 'id', 'set_id' );
								foreach ( $array_check_parameter_arr as $array_check_parameter ) {
									$form_id_pattern = '/' . $array_check_parameter . '\=(\'|\")(\d+)(\'|\")/';
									preg_match_all( $form_id_pattern, $is_arm_form_in_page_0_0_shortcode, $found_form_id_arr );

									$check_is_setup_form = strpos( $is_arm_form_in_page_0_0_shortcode, 'arm_setup' );
									if ( is_array( $found_form_id_arr ) && isset( $found_form_id_arr[2] ) ) {
										$form_id_arr = $found_form_id_arr[2];
										foreach ( $form_id_arr as $form_id ) {
											$get_form_style_layout = '';
											if ( ! isset( $arm_global_load_js_css_forms[ $form_id ] ) ) {
												$setup_form_id = 0;
												if ( $check_is_setup_form ) {
													$setup_form_id               = $form_id;
													$get_arm_setup_form_settings = $wpdb->get_var( $wpdb->prepare('SELECT `arm_setup_modules` FROM `' . $ARMemberLite->tbl_arm_membership_setup . "` WHERE `arm_setup_id`= %d", $setup_form_id) );// phpcs:ignore --Reason: $ARMemberLite->tbl_arm_membership_setup is table name defined globally. False Positive alarm
													$arm_setup_form_settings     = maybe_unserialize( $get_arm_setup_form_settings );
													$form_id                     = isset( $arm_setup_form_settings['modules']['forms'] ) ? $arm_setup_form_settings['modules']['forms'] : 101;
												}
												$get_arm_form_settings = $wpdb->get_var( $wpdb->prepare("SELECT `arm_form_settings` FROM `". $ARMemberLite->tbl_arm_forms."` WHERE `arm_form_id`= %d",$form_id ) ); //phpcs:ignore
												$arm_form_settings     = maybe_unserialize( $get_arm_form_settings );
												if ( ! empty( $arm_form_settings['style'] ) ) {
													$get_form_style_layout = ! empty( $arm_form_settings['style']['form_layout'] ) ? $arm_form_settings['style']['form_layout'] : 'writer_border';
												}

												$arm_global_load_js_css_forms             = ! empty( $arm_global_load_js_css_forms ) ? $arm_global_load_js_css_forms : array();
												$arm_global_load_js_css_forms[ $form_id ] = $get_form_style_layout;
												if ( ! empty( $setup_form_id ) ) {
													$arm_global_load_js_css_forms[ $setup_form_id ] = $get_form_style_layout;
												}
											} else {

												$get_form_style_layout = $arm_global_load_js_css_forms[ $form_id ];
											}

											if ( $get_form_style_layout == 'writer_border' ) {
												$include_materia_outline_style = '1';
											} elseif ( $get_form_style_layout == 'writer' ) {
												$include_material_style = '1';
											} elseif ( $get_form_style_layout == 'rounded' ) {
												$include_rounded_style = '1';
											}
											if ( $get_form_style_layout == 'iconic' ) {
												$include_standard_style = '1';
											}
										}
									}
								}
							}
						}
					}
				}
				wp_enqueue_style( 'arm_front_components_form-style__arm-style-default' );
				if ( ! empty( $include_material_style ) || $form_style == 'writer' || ( $isFrontSection == true && $form_style == '' ) ) {
					wp_enqueue_style( 'arm_front_components_form-style__arm-style-material' );
				}

				if ( ! empty( $include_materia_outline_style ) || $form_style == 'writer_border' || ( $isFrontSection == true && $form_style == '' ) ) {
					wp_enqueue_style( 'arm_front_components_form-style__arm-style-outline-material' );
				}
				if ( ! empty( $include_rounded_style ) || $form_style == 'rounded' || ( $isFrontSection == true && $form_style == '' ) ) {
					wp_enqueue_style( 'arm_front_components_form-style__arm-style-rounded' );
				}

				wp_enqueue_style( 'arm_front_component_css' );
				// wp_enqueue_style('arm_custom_component_css');
			}
			wp_enqueue_style( 'arm_bootstrap_all_css' );

			/* Print Custom CSS in Front-End Pages (Required `arm_front_css` handle to add inline css) */
			$arm_add_custom_css_flag = '';
			if ( isset( $_GET['_locale'] ) && $_GET['_locale'] == 'user' && $this->arm_is_gutenberg_active() ) { //phpcs:ignore
				$arm_add_custom_css_flag = '1';
			}

			/**
			 * Directory & Profile Templates Style
			 */
			if ( $isEnqueueAll == '1' || $isFrontSection === 2 ) {
				wp_enqueue_style( 'arm_form_style_css' );
				wp_enqueue_style( 'arm_front_components_base-controls' );
				wp_enqueue_style( 'arm_front_components_form-style_base' );
				wp_enqueue_style( 'arm_front_components_form-style__arm-style-default' );
				// wp_enqueue_style('arm-font-awesome');

				wp_enqueue_style( 'arm_front_components_form-style__arm-style-material' );
				wp_enqueue_style( 'arm_front_components_form-style__arm-style-outline-material' );
				wp_enqueue_style( 'arm_front_components_form-style__arm-style-rounded' );

				wp_enqueue_style( 'arm_front_component_css' );
				wp_enqueue_style( 'arm_custom_component_css' );

				$templates = $arm_members_directory->arm_default_member_templates();
				if ( ! empty( $templates ) ) {
					foreach ( $templates as $tmp ) {
						if ( is_file( MEMBERSHIPLITE_VIEWS_DIR . '/templates/' . $tmp['arm_slug'] . '.css' ) ) {
							wp_enqueue_style( 'arm_template_style_' . $tmp['arm_slug'], MEMBERSHIPLITE_VIEWS_URL . '/templates/' . $tmp['arm_slug'] . '.css', array(), MEMBERSHIPLITE_VERSION );
						}
					}
				}
			} else {
				$found_matches = array();
				$pattern       = '\[(\[?)(arm_template)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
				$posts         = $wp_query->posts;
				if ( is_array( $posts ) ) {
					foreach ( $posts as $post ) {
						if ( preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches ) > 0 ) {
							$found_matches[] = $matches;
						}
					}
					$tempids = array();
					if ( is_array( $found_matches ) && count( $found_matches ) > 0 ) {
						foreach ( $found_matches as $mat ) {
							if ( is_array( $mat ) and count( $mat ) > 0 ) {
								foreach ( $mat as $k => $v ) {
									foreach ( $v as $key => $val ) {
										$parts = explode( 'id=', $val );
										if ( $parts > 0 && isset( $parts[1] ) ) {
											if ( stripos( @$parts[1], ']' ) !== false ) {
												$partsnew  = explode( ']', $parts[1] );
												$tempids[] = str_replace( "'", '', str_replace( '"', '', $partsnew[0] ) );
											} elseif ( stripos( @$parts[1], ' ' ) !== false ) {
												$partsnew  = explode( ' ', $parts[1] );
												$tempids[] = str_replace( "'", '', str_replace( '"', '', $partsnew[0] ) );
											}
										}
									}
								}
							}
						}
					}
				}
				if ( ! empty( $tempids ) && count( $tempids ) > 0 ) {
					$tempids = $this->arm_array_unique( $tempids );
					foreach ( $tempids as $tid ) {
						$tid = trim( $tid );
						/* Query Monitor Change */

						if ( isset( $GLOBALS['arm_profile_template'] ) && isset( $GLOBALS['arm_profile_template'][ $tid ] ) ) {
							$tempSlug = $GLOBALS['arm_profile_template'][ $tid ];
						} else {
							$tempSlug = $wpdb->get_var($wpdb->prepare( "SELECT `arm_slug` FROM ".$this->tbl_arm_member_templates." WHERE `arm_id`= %d AND `arm_type` != %s", $tid, 'profile' ));//phpcs:ignore --Reason $tbl_arm_member_template is table name
							if ( ! isset( $GLOBALS['arm_profile_template'] ) ) {
								$GLOBALS['arm_profile_template'] = array();
							}
							$GLOBALS['arm_profile_template'][ $tid ] = $tempSlug;
						}

						if ( is_file( MEMBERSHIPLITE_VIEWS_DIR . '/templates/' . $tempSlug . '.css' ) ) {
							wp_enqueue_style( 'arm_template_style_' . $tempSlug, MEMBERSHIPLITE_VIEWS_URL . '/templates/' . $tempSlug . '.css', array(), MEMBERSHIPLITE_VERSION );
						}
					}
				}
			}
		}
	}

	/* Setting Front Side JavaScript */

	function set_front_js( $isFrontSection = false ) {
		global $wp, $wpdb, $post, $wp_scripts, $ARMemberLite, $arm_lite_ajaxurl, $arm_slugs, $arm_global_settings;
		/* Check Current Front-Page is Membership Page. */

		$is_arm_front_page = $this->is_arm_front_page();
		$isEnqueueAll      = $arm_global_settings->arm_get_single_global_settings( 'enqueue_all_js_css', 0 );
		if ( ( $is_arm_front_page === true || $isEnqueueAll == '1' || $isFrontSection ) && ! is_admin() ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'wp-hooks' );
			/* Main Plugin Front-End JS */
			wp_register_script( 'arm_common_js', MEMBERSHIPLITE_URL . '/js/arm_common.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
			wp_register_script( 'arm_bpopup', MEMBERSHIPLITE_URL . '/js/jquery.bpopup.min.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
			/* Tooltip JS */
			wp_register_script( 'arm_tipso_front', MEMBERSHIPLITE_URL . '/js/tipso.min.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION ); //phpcs:ignore
			/* File Upload JS */
			wp_register_script( 'arm_file_upload_js', MEMBERSHIPLITE_URL . '/js/arm_file_upload_js.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION ); //phpcs:ignore

			/* For bootstrap datetime picker js */
			wp_register_script( 'arm_bootstrap_js', MEMBERSHIPLITE_URL . '/bootstrap/js/bootstrap.min.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION ); //phpcs:ignore

			wp_register_script( 'arm_bootstrap_datepicker_with_locale_js', MEMBERSHIPLITE_URL . '/bootstrap/js/bootstrap-datetimepicker-with-locale.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION ); //phpcs:ignore

			/* Enqueue Javascripts */
			wp_enqueue_script( 'jquery-ui-core' );
			if ( ! wp_script_is( 'arm_bpopup', 'enqueued' ) ) {
				wp_enqueue_script( 'arm_bpopup' );
			}

			if ( ! wp_script_is( 'arm_bootstrap_js', 'enqueued' ) ) {
				wp_enqueue_script( 'arm_bootstrap_js' );
			}

			if ( $isEnqueueAll == '1' ) {
				if ( ! wp_script_is( 'arm_bootstrap_datepicker_with_locale_js', 'enqueued' ) ) {
					wp_enqueue_script( 'arm_bootstrap_datepicker_with_locale_js' );
				}
				if ( ! wp_script_is( 'arm_bpopup', 'enqueued' ) ) {
					wp_enqueue_script( 'arm_bpopup' );
				}
				if ( ! wp_script_is( 'arm_file_upload_js', 'enqueued' ) ) {
					wp_enqueue_script( 'arm_file_upload_js' );
				}
				if ( ! wp_script_is( 'arm_tipso_front', 'enqueued' ) ) {
					wp_enqueue_script( 'arm_tipso_front' );
				}
			}

			if ( !wp_script_is( 'arm_common_js', 'enqueued' ) ) {
				wp_enqueue_script( 'arm_common_js' );
			}
			/* Load Angular Assets */
			if ( $isEnqueueAll == '1' ) {
				$this->enqueue_angular_script();
			}
		}
		if(!$this->is_arm_pro_active) {
			$this->set_global_javascript_variables();
		}
	}

	function enqueue_angular_script( $include_card_validation = false ) {
		global $wp, $wpdb, $post, $arm_lite_errors, $ARMemberLite, $arm_lite_ajaxurl,$arm_lite_version;
		/* Design CSS */
			wp_register_style( 'arm_angular_material_css', MEMBERSHIPLITE_URL . '/materialize/arm_materialize.css', array(), MEMBERSHIPLITE_VERSION );
			wp_enqueue_style( 'arm_angular_material_css' );
			$angularJSFiles = array(
				'arm_angular_with_material' => MEMBERSHIPLITE_URL . '/materialize/arm_materialize.js',
				'arm_jquery_validation'     => MEMBERSHIPLITE_URL . '/bootstrap/js/jqBootstrapValidation.js',
				'arm_form_validation'       => MEMBERSHIPLITE_URL . '/bootstrap/js/arm_form_validation.js',
			);

			foreach ( $angularJSFiles as $handle => $src ) {
				if ( ! wp_script_is( $handle, 'registered' ) ) {
					wp_register_script( $handle, $src, array(), MEMBERSHIPLITE_VERSION, true );
				}
				if ( ! wp_script_is( $handle, 'enqueued' ) ) {
					wp_enqueue_script( $handle );
				}
			}

	}

	/**
	 * Check front page has plugin content.
	 */
	function is_arm_front_page() {
		global $wp, $wpdb, $wp_query, $post, $arm_lite_errors, $ARMemberLite, $arm_global_settings;
		if ( ! is_admin() ) {
			$found_matches = array();
			$pattern       = '\[(\[?)(arm.*)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
			$posts         = $wp_query->posts;
			if ( is_array( $posts ) ) {
				foreach ( $posts as $post ) {
					if ( preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches ) > 0 ) {
						$found_matches[] = $matches;
					}
				}
			}
			/* Remove empty array values. */
			$found_matches = $this->arm_array_trim( $found_matches );
			if ( ! empty( $found_matches ) && count( $found_matches ) > 0 ) {
				return true;
			}
		}
		return false;
	}

	function is_arm_setup_page() {
		global $wp, $wpdb, $wp_query, $post, $arm_lite_errors, $ARMemberLite, $arm_global_settings;
		if ( ! is_admin() ) {
			$found_matches = array();
			$pattern       = '\[(\[?)(arm_setup)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
			$posts         = $wp_query->posts;
			if ( is_array( $posts ) ) {
				foreach ( $posts as $post ) {
					if ( preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches ) > 0 ) {
						$found_matches[] = $matches;
					}
				}
			}
			/* Remove empty array values. */
			$found_matches = $this->arm_array_trim( $found_matches );
			if ( ! empty( $found_matches ) && count( $found_matches ) > 0 ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if front page content has plugin shortcode and has form.
	 */
	function is_arm_form_page() {
		global $wp, $wpdb, $wp_query, $post, $ARMemberLite, $arm_global_settings;
		if ( ! is_admin() ) {
			$found_matches = array();
			$pattern       = '\[(\[?)(arm_form|arm_edit_profile|arm_close_account|arm_setup|arm_template)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
			$posts         = $wp_query->posts;
			if ( is_array( $posts ) && ! empty( $posts ) ) {
				foreach ( $posts as $key => $post ) {
					if ( preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches ) > 0 ) {
						$found_matches[] = $matches;
					}
				}
			}

			$found_matches = $this->arm_array_trim( $found_matches );
			if ( ! empty( $found_matches ) && count( $found_matches ) > 0 ) {
				return $found_matches;
			}
		}
		return false;
	}

	/*
	 * Trim Array Values.
	 */

	function arm_array_trim( $array ) {
		if ( is_array( $array ) ) {
			foreach ( $array as $key => $value ) {
				if ( is_array( $value ) ) {
					$array[ $key ] = $this->arm_array_trim( $value );
				} else {
					$array[ $key ] = trim( $value );
				}
				if ( empty( $array[ $key ] ) ) {
					unset( $array[ $key ] );
				}
			}
		} else {
			$array = trim( $array );
		}
		return $array;
	}

	/**
	 * Removes duplicate values from multidimensional array
	 */
	function arm_array_unique( $array ) {
		$result = array_map( 'unserialize', array_unique( array_map( 'serialize', $array ) ) );
		if ( is_array( $result ) ) {
			foreach ( $result as $key => $value ) {
				if ( is_array( $value ) ) {
					$result[ $key ] = $this->arm_array_unique( $value );
				}
			}
		}
		return $result;
	}

	/**
	 * Restrict Network Activation
	 */
	public static function armember_check_network_activation( $network_wide ) {
		if ( ! $network_wide ) {
			return;
		}

		deactivate_plugins( plugin_basename( MEMBERSHIPLITE_DIR_NAME.'/armember-membership.php' ), true, true );

		header( 'Location: ' . network_admin_url( 'plugins.php?deactivate=true' ) );
		exit;
	}

	public static function deactivate__armember_lite_version(){
		$dependent = 'armember/armember.php';
		if (is_plugin_active($dependent) ) {
			add_action('update_option_active_plugins', array( 'ARMemberLite', 'deactivate_armember_pro_version' ));
		}
	}

	public static function deactivate_armember_pro_version()
	{
		$dependent = 'armember/armember.php';
		deactivate_plugins($dependent);
	}

	public static function install() {

		global $ARMemberLite, $arm_lite_version;

		$armember_exists = 0;
		if ( file_exists( WP_PLUGIN_DIR . '/armember/armember.php' ) ) {
			$armember_exists = 1;
		}
		$armember_version = get_option( 'arm_version', '' );

		if ( $armember_version != '' && $armember_exists == 1 ) {
			$_version = get_option( 'armlite_version' );

			if ( empty( $_version ) || $_version == '' ) {
				update_option( 'armlite_version', $arm_lite_version );
			} else {
				$ARMemberLite->wpdbfix();
				do_action( 'arm_reactivate_plugin' );
			}
		} else {
			$_version = get_option( 'armlite_version' );

			if ( empty( $_version ) || $_version == '' ) {

				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				@set_time_limit( 0 ); //phpcs:ignore
				global $wpdb, $arm_lite_version, $arm_global_settings;
				$arm_global_settings->arm_set_ini_for_access_rules();
				$charset_collate = '';
				if ( $wpdb->has_cap( 'collation' ) ) {
					if ( ! empty( $wpdb->charset ) ) {
						$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
					}
					if ( ! empty( $wpdb->collate ) ) {
						$charset_collate .= " COLLATE $wpdb->collate";
					}
				}

				update_option( 'armlite_version', $arm_lite_version );
				update_option( 'arm_plugin_activated', 1 );
				update_option( 'arm_show_document_video', 1 );
				update_option( 'arm_is_social_feature', 0 );
				update_option( 'arm_is_gutenberg_block_restriction_feature', 0 );
				update_option( 'arm_is_beaver_builder_restriction_feature', 0 );
				update_option( 'arm_is_divi_builder_restriction_feature', 0 );
				update_option( 'arm_is_wpbakery_page_builder_restriction_feature', 0 );
				update_option( 'arm_is_fusion_builder_restriction_feature', 0 );
				update_option( 'arm_is_oxygen_builder_restriction_feature', 0 );
				update_option( 'arm_is_siteorigin_builder_restriction_feature', 0 );
				update_option( 'arm_is_bricks_builder_restriction_feature', 0 );

				$arm_dbtbl_create = array();
				/* Table structure for `Members activity` */
				$tbl_arm_members_activity                      = $wpdb->prefix . 'arm_activity';
				$sql_table                                     = "DROP TABLE IF EXISTS `{$tbl_arm_members_activity}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_members_activity}`(
                    `arm_activity_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_user_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_type` VARCHAR(50) NOT NULL,
                    `arm_action` VARCHAR(50) NOT NULL,
                    `arm_content` LONGTEXT NOT NULL,
                    `arm_item_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_paid_post_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_gift_plan_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_link` VARCHAR(255) DEFAULT NULL,
		    `arm_activity_plan_name` VARCHAR(255) NOT NULL,
                    `arm_activity_plan_type` VARCHAR(255) NOT NULL,
                    `arm_activity_payment_gateway` VARCHAR(255) NOT NULL,
                    `arm_activity_plan_amount` DOUBLE NOT NULL DEFAULT '0',
                    `arm_activity_plan_start_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    `arm_activity_plan_end_date` datetime NULL DEFAULT '1970-01-01 00:00:00',
                    `arm_activity_plan_next_cycle_date` datetime NULL DEFAULT '1970-01-01 00:00:00',
                    `arm_ip_address` VARCHAR(50) NOT NULL,
                    `arm_date_recorded` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_activity_id`)
                ) {$charset_collate};";
				$arm_dbtbl_create[ $tbl_arm_members_activity ] = dbDelta( $sql_table );

				/* Table structure for `email settings` */
				$tbl_arm_email_settings                      = $wpdb->prefix . 'arm_email_templates';
				$sql_table                                   = "DROP TABLE IF EXISTS `{$tbl_arm_email_settings}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_email_settings}`(
                    `arm_template_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_template_name` VARCHAR(255) NOT NULL,
                    `arm_template_slug` VARCHAR(255) NOT NULL ,
                    `arm_template_subject` VARCHAR(255) NOT NULL,
                    `arm_template_content` longtext NOT NULL,
                    `arm_template_status` INT(1) NOT NULL DEFAULT '1',
                    PRIMARY KEY (`arm_template_id`)
                ) {$charset_collate};";
				$arm_dbtbl_create[ $tbl_arm_email_settings ] = dbDelta( $sql_table );

				/* Table structure for `Entries` */
				$tbl_arm_entries                      = $wpdb->prefix . 'arm_entries';
				$sql_table                            = "DROP TABLE IF EXISTS `{$tbl_arm_entries}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_entries}` (
                    `arm_entry_id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `arm_entry_email` varchar(255) DEFAULT NULL,
                    `arm_name` varchar(255) DEFAULT NULL,
                    `arm_description` LONGTEXT,
                    `arm_ip_address` text,
                    `arm_browser_info` text,
                    `arm_entry_value` LONGTEXT,
                    `arm_form_id` int(11) DEFAULT NULL,
                    `arm_user_id` bigint(20) DEFAULT NULL,
                    `arm_plan_id` int(11) DEFAULT NULL,
                    `arm_is_post_entry` TINYINT(1) NOT NULL DEFAULT '0',
                    `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0',
                    `arm_is_gift_entry` TINYINT(1) NOT NULL DEFAULT '0',
                    `arm_created_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_entry_id`)
                ) {$charset_collate};";
				$arm_dbtbl_create[ $tbl_arm_entries ] = dbDelta( $sql_table );

				/* Table structure for `failed login` */
				$tbl_arm_fail_attempts                      = $wpdb->prefix . 'arm_fail_attempts';
				$sql_table                                  = "DROP TABLE IF EXISTS `{$tbl_arm_fail_attempts}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_fail_attempts}`(
                    `arm_fail_attempts_id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `arm_user_id` bigint(20) NOT NULL,
                    `arm_fail_attempts_detail` text,
                    `arm_fail_attempts_ip` varchar(200) DEFAULT NULL,
                    `arm_is_block` int(1) NOT NULL DEFAULT '0',
                    `arm_fail_attempts_datetime` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    `arm_fail_attempts_release_datetime` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_fail_attempts_id`)
                ) {$charset_collate};";
				$arm_dbtbl_create[ $tbl_arm_fail_attempts ] = dbDelta( $sql_table );

				/* Table structure for `arm_forms` */
				$tbl_arm_forms                      = $wpdb->prefix . 'arm_forms';
				$sql_table                          = "DROP TABLE IF EXISTS `{$tbl_arm_forms}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_forms}` (
                    `arm_form_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_form_label` VARCHAR(255) DEFAULT NULL,
                    `arm_form_title` VARCHAR(255) DEFAULT NULL,
                    `arm_form_type` VARCHAR(100) DEFAULT NULL,
                    `arm_form_slug` VARCHAR(255) DEFAULT NULL,
                    `arm_is_default` INT(1) NOT NULL DEFAULT '0',
                    `arm_set_name` VARCHAR(255) DEFAULT NULL,
                    `arm_set_id` INT(11) NOT NULL DEFAULT '0',
                    `arm_is_template` INT(11) NOT NULL DEFAULT '0',
                    `arm_ref_template` INT(11) NOT NULL DEFAULT '0',
                    `arm_form_settings` LONGTEXT,
                    `arm_form_updated_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    `arm_form_created_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_form_id`)
                ) {$charset_collate};";
				$arm_dbtbl_create[ $tbl_arm_forms ] = dbDelta( $sql_table );

				/* Table structure for `arm_form_field` */
				$tbl_arm_form_field                      = $wpdb->prefix . 'arm_form_field';
				$sql_table                               = "DROP TABLE IF EXISTS `{$tbl_arm_form_field}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_form_field}`(
                    `arm_form_field_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_form_field_form_id` INT(11) NOT NULL,
                    `arm_form_field_order` INT(11) NOT NULL DEFAULT '0',
                    `arm_form_field_slug` VARCHAR(255) DEFAULT NULL,
                    `arm_form_field_option` LONGTEXT,
                                    `arm_form_field_bp_field_id` INT(11) NOT NULL DEFAULT '0',
                    `arm_form_field_status` INT(1) NOT NULL DEFAULT '1',
                    `arm_form_field_created_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_form_field_id`)
                ) {$charset_collate};";
				$arm_dbtbl_create[ $tbl_arm_form_field ] = dbDelta( $sql_table );

				/* Table structure for `lockdown` */
				$tbl_arm_lockdown                      = $wpdb->prefix . 'arm_lockdown';
				$sql_table                             = "DROP TABLE IF EXISTS `{$tbl_arm_lockdown}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_lockdown}`(
                    `arm_lockdown_ID` bigint(20) NOT NULL AUTO_INCREMENT,
                    `arm_user_id` bigint(20) NOT NULL,
                    `arm_lockdown_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    `arm_release_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    `arm_lockdown_IP` VARCHAR(255) DEFAULT NULL,
                    PRIMARY KEY  (`arm_lockdown_ID`)
                ) {$charset_collate};";
				$arm_dbtbl_create[ $tbl_arm_lockdown ] = dbDelta( $sql_table );

				/* Table structure for `arm_members` */
				$tbl_arm_members                      = $wpdb->prefix . 'arm_members';
				$sql_table                            = "DROP TABLE IF EXISTS `{$tbl_arm_members}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_members}` (
                  `arm_member_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                  `arm_user_id` bigint(20) unsigned NOT NULL,
                  `arm_user_login` VARCHAR(60) NOT NULL DEFAULT '',
                  `arm_user_nicename` VARCHAR(50) NOT NULL DEFAULT '',
                  `arm_user_email` VARCHAR(100) NOT NULL DEFAULT '',
                  `arm_user_url` VARCHAR(100) NOT NULL DEFAULT '',
                  `arm_user_registered` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                  `arm_user_activation_key` VARCHAR(60) NOT NULL DEFAULT '',
                  `arm_user_status` INT(11) NOT NULL DEFAULT '0',
                  `arm_display_name` VARCHAR(250) NOT NULL DEFAULT '',
                  `arm_user_type` int(1) NOT NULL DEFAULT '0',
                  `arm_primary_status` int(1) NOT NULL DEFAULT '1',
                  `arm_secondary_status` int(1) NOT NULL DEFAULT '0',
                  `arm_user_plan_ids` TEXT NULL,
                  `arm_user_suspended_plan_ids` TEXT NULL,
                  PRIMARY KEY (`arm_member_id`),
                  KEY `arm_user_login_key` (`arm_user_login`),
                  KEY `arm_user_nicename` (`arm_user_nicename`)
                ) {$charset_collate};";
				$arm_dbtbl_create[ $tbl_arm_members ] = dbDelta( $sql_table );

				/* Table structure for `Membership Setup Wizard` */
				$tbl_arm_membership_setup                      = $wpdb->prefix . 'arm_membership_setup';
				$sql_table                                     = "DROP TABLE IF EXISTS `{$tbl_arm_membership_setup}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_membership_setup}`(
                    `arm_setup_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_setup_name` VARCHAR(255) NOT NULL,
                    `arm_setup_type` TINYINT(1) NOT NULL DEFAULT '0',
                    `arm_setup_modules` LONGTEXT,
                    `arm_setup_labels` LONGTEXT,
                    `arm_status` INT(1) NOT NULL DEFAULT '1',
                    `arm_created_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_setup_id`)
                ) {$charset_collate};";
				$arm_dbtbl_create[ $tbl_arm_membership_setup ] = dbDelta( $sql_table );

				/* Table structure for `Payment Log` */
				$tbl_arm_payment_log                      = $wpdb->prefix . 'arm_payment_log';
				$sql_table                                = "DROP TABLE IF EXISTS `{$tbl_arm_payment_log}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_payment_log}`(
                    `arm_log_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_invoice_id` INT(11) NOT NULL DEFAULT '0',
                    `arm_user_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_first_name` VARCHAR(255) DEFAULT NULL,
                    `arm_last_name` VARCHAR(255) DEFAULT NULL,
                    `arm_plan_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_old_plan_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_payment_gateway` VARCHAR(50) NOT NULL,
                    `arm_payment_type` VARCHAR(50) NOT NULL,
                    `arm_token` TEXT,
                    `arm_payer_email` VARCHAR(255) DEFAULT NULL,
                    `arm_receiver_email` VARCHAR(255) DEFAULT NULL,
                    `arm_transaction_id` TEXT,
                    `arm_transaction_payment_type` VARCHAR(100) DEFAULT NULL,
                    `arm_transaction_status` TEXT,
                    `arm_payment_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    `arm_payment_mode` VARCHAR(255),
                    `arm_payment_cycle` INT(11) NOT NULL DEFAULT '0',
                    `arm_bank_name` VARCHAR(255) DEFAULT NULL,
                    `arm_account_name` VARCHAR(255) DEFAULT NULL,
                    `arm_additional_info` LONGTEXT,
                    `arm_payment_transfer_mode` VARCHAR(255) DEFAULT NULL,
                    `arm_amount` double NOT NULL DEFAULT '0',
                    `arm_currency` VARCHAR(50) DEFAULT NULL,
                    `arm_extra_vars` LONGTEXT,
                    `arm_coupon_code` VARCHAR(255) DEFAULT NULL,
                    `arm_coupon_discount` double NOT NULL DEFAULT '0',
                    `arm_coupon_discount_type` VARCHAR(50) DEFAULT NULL,
                    `arm_coupon_on_each_subscriptions` TINYINT(1) NULL DEFAULT '0',
                    `arm_is_post_payment` TINYINT(1) NOT NULL DEFAULT '0',
                    `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0',
                    `arm_is_gift_payment` TINYINT(1) NOT NULL DEFAULT '0',
                    `arm_is_trial` INT(1) NOT NULL DEFAULT '0',
                    `arm_display_log` INT(1) NOT NULL DEFAULT '1',
                    `arm_created_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_log_id`)
                ) {$charset_collate};";
				$arm_dbtbl_create[ $tbl_arm_payment_log ] = dbDelta( $sql_table );

				/* Table structure for `arm_subscription_plans` */
				$tbl_arm_subscription_plans                      = $wpdb->prefix . 'arm_subscription_plans';
				$sql_table                                       = "DROP TABLE IF EXISTS `{$tbl_arm_subscription_plans}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_subscription_plans}`(
                    `arm_subscription_plan_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_subscription_plan_name` VARCHAR(255) NOT NULL,
                    `arm_subscription_plan_description` TEXT,
                    `arm_subscription_plan_type` VARCHAR(50) NOT NULL,
                    `arm_subscription_plan_options` LONGTEXT,
                    `arm_subscription_plan_amount` double NOT NULL DEFAULT '0',
                    `arm_subscription_plan_status` INT(1) NOT NULL DEFAULT '1',
                    `arm_subscription_plan_role` VARCHAR(100) DEFAULT NULL,
                    `arm_subscription_plan_post_id` BIGINT(20) NOT NULL DEFAULT '0',
                    `arm_subscription_plan_gift_status` INT(1) NOT NULL DEFAULT '0',
                    `arm_subscription_plan_is_delete` INT(1) NOT NULL DEFAULT '0',
                    `arm_subscription_plan_created_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_subscription_plan_id`)
                ) {$charset_collate};";
				$arm_dbtbl_create[ $tbl_arm_subscription_plans ] = dbDelta( $sql_table );

				/* Table structure for `Taxonomy Term Meta` */
				$tbl_arm_termmeta                      = $wpdb->prefix . 'arm_termmeta';
				$sql_table                             = "DROP TABLE IF EXISTS `{$tbl_arm_termmeta}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_termmeta}`(
                    `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `arm_term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                    `meta_key` VARCHAR(255) DEFAULT NULL,
                    `meta_value` longtext,
                    PRIMARY KEY (`meta_id`),
                    KEY `arm_term_id` (`arm_term_id`),
                    KEY `meta_key` (`meta_key`)
                ) {$charset_collate};";
				$arm_dbtbl_create[ $tbl_arm_termmeta ] = dbDelta( $sql_table );

				/* Table structure for `Member Templates` */
				$tbl_arm_member_templates                      = $wpdb->prefix . 'arm_member_templates';
				$sql_table                                     = "DROP TABLE IF EXISTS `{$tbl_arm_member_templates}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_member_templates}`(
                    `arm_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_title` text,
                    `arm_slug` varchar(255) DEFAULT NULL,
                    `arm_type` varchar(50) DEFAULT NULL,
                    `arm_default` int(1) NOT NULL DEFAULT '0',
                    `arm_subscription_plan` text NULL,
                    `arm_core` int(1) NOT NULL DEFAULT '0',
                    `arm_template_html` longtext,
                    `arm_ref_template` int(11) NOT NULL DEFAULT '0',
                    `arm_options` longtext,
                    `arm_html_before_fields` longtext,
                    `arm_html_after_fields` longtext,
                    `arm_enable_admin_profile` int(1) NOT NULL DEFAULT '0',
                    `arm_created_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_id`)
                ) {$charset_collate};";
				$arm_dbtbl_create[ $tbl_arm_member_templates ] = dbDelta( $sql_table );

				$tbl_arm_login_history                      = $wpdb->prefix . 'arm_login_history';
				$sql_table                                  = "DROP TABLE IF EXISTS `{$tbl_arm_login_history}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_login_history}`(
                    `arm_history_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_user_id` int(11) NOT NULL,
                    `arm_logged_in_ip` varchar(255) NOT NULL,
                    `arm_logged_in_date` DATETIME NOT NULL,
                    `arm_logout_date` DATETIME NOT NULL,
                    `arm_login_duration` TIME NOT NULL,
                    `arm_history_browser` VARCHAR(255) NOT NULL,
                    `arm_history_session` VARCHAR(255) NOT NULL,
                    `arm_login_country` VARCHAR(255) NOT NULL,
                    `arm_user_current_status` int(1) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`arm_history_id`)
                ){$charset_collate};";
				$arm_dbtbl_create[ $tbl_arm_login_history ] = dbDelta( $sql_table );

				$tbl_arm_debug_payment_log = $wpdb->prefix . 'arm_debug_payment_log';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_debug_payment_log}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_debug_payment_log}`(
                    `arm_payment_log_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_payment_log_ref_id` int(11) NOT NULL DEFAULT '0',
                    `arm_payment_log_gateway` varchar(255) DEFAULT NULL,
                    `arm_payment_log_event` varchar(255) DEFAULT NULL,
                    `arm_payment_log_event_from` varchar(255) DEFAULT NULL,
                    `arm_payment_log_status` TINYINT(1) DEFAULT '1',
                    `arm_payment_log_raw_data` TEXT,
                    `arm_payment_log_added_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_payment_log_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_debug_payment_log] = dbDelta($sql_table);

				$tbl_arm_debug_general_log = $wpdb->prefix . 'arm_debug_general_log';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_debug_general_log}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_debug_general_log}`(
                    `arm_general_log_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_general_log_event` varchar(255) DEFAULT NULL,
                    `arm_general_log_event_name` varchar(255) DEFAULT NULL,
                    `arm_general_log_event_from` varchar(255) DEFAULT NULL,
                    `arm_general_log_raw_data` TEXT,
                    `arm_general_log_added_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_general_log_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_debug_general_log] = dbDelta($sql_table);

				/* Install Default Template Forms & Fields */
				$ARMemberLite->install_default_templates();
				$wpdb->query( "ALTER TABLE `".$tbl_arm_forms."` AUTO_INCREMENT=101" ); // phpcs:ignore  --Reason: $tbl_arm_forms is a table name. 
				/* Install Default Member Forms & Fields. */
				$ARMemberLite->install_member_form_fields();
				/* Install Default Pages. */
				$ARMemberLite->install_default_pages();
				/* Update Page in default template */
				$ARMemberLite->update_default_pages_for_templates();
				/* Create Custom User Role & Capabilities. */
				$ARMemberLite->add_user_role_and_capabilities();

				$armember_check_db_permission = $ARMemberLite->armember_check_db_permission();
				if(!empty($armember_check_db_permission))
				{
					$arm_members_table = $ARMemberLite->tbl_arm_members;
					$arm_tbl_arm_payment_log = $ARMemberLite->tbl_arm_payment_log;
					
					//Add the arm-user-id INDEX for the Members table
					$arm_members_add_index_arm_user_id = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_members_table." where Key_name=%s",'arm-user-id')); //phpcs:ignore --Reason: $arm_members_table is a table name
					if(empty($arm_members_add_index_arm_user_id))
					{
						$wpdb->query("ALTER TABLE `{$arm_members_table}` ADD INDEX `arm-user-id` (`arm_user_id`)"); //phpcs:ignore --Reason $arm_members_table is a table name
					}

					//Add the arm-user-id INDEX for the Payment table
					$arm_payment_log_add_index_arm_user_id = $wpdb->get_results($wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_payment_log." where Key_name=%s",'arm-user-id')); //phpcs:ignore --Reason: $arm_tbl_arm_payment_log is a table name
					if(empty($arm_payment_log_add_index_arm_user_id))
					{
						$wpdb->query("ALTER TABLE `{$arm_tbl_arm_payment_log}` ADD INDEX `arm-user-id` (`arm_user_id`)"); //phpcs:ignore --Reason: $arm_tbl_arm_payment_log is a table name
					}

					//Add the arm-plan-id INDEX for the Payment table
					$arm_payment_log_add_index_arm_plan_id = $wpdb->get_results($wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_payment_log." where Key_name=%s",'arm-plan-id')); //phpcs:ignore --Reason: $arm_tbl_arm_payment_log is a table name
					if(empty($arm_payment_log_add_index_arm_plan_id))
					{
						$wpdb->query("ALTER TABLE `{$arm_tbl_arm_payment_log}` ADD INDEX `arm-plan-id` (`arm_plan_id`)"); //phpcs:ignore --Reason $arm_tbl_arm_payment_log
					}

					//Add the arm-display-log INDEX for the Payment table
					$arm_payment_log_add_index_arm_display_log = $wpdb->get_results($wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_payment_log." where Key_name=%s ",'arm-display-log')); //phpcs:ignore --Reason: $arm_tbl_arm_payment_log is a table name
					if(empty($arm_payment_log_add_index_arm_display_log))
					{
						$wpdb->query("ALTER TABLE `{$arm_tbl_arm_payment_log}` ADD INDEX `arm-display-log` (`arm_display_log`)"); //phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
					}

					$arm_tbl_arm_payment_log = $ARMemberLite->tbl_arm_payment_log;
					$arm_tbl_arm_debug_payment_log = $ARMemberLite->tbl_arm_debug_payment_log;
					$arm_tbl_arm_debug_general_log = $ARMemberLite->tbl_arm_debug_general_log;

					//Add the arm-debug-payment-log-gateway INDEX for the Payment table
					$arm_debug_payment_log_add_index_arm_gateway = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_debug_payment_log." where Key_name=%s ",'arm-debug-payment-log-gateway') );//phpcs:ignore --Reason $arm_tbl_arm_debug_payment_log is a table name
					if(empty($arm_debug_payment_log_add_index_arm_gateway))
					{
						$wpdb->query("ALTER TABLE `{$arm_tbl_arm_debug_payment_log}` ADD INDEX `arm-debug-payment-log-gateway` (`arm_payment_log_gateway`)");//phpcs:ignore --Reason $arm_tbl_arm_debug_payment_log is a table name
					}

					//Add the arm-debug-payment-log-status INDEX for the Payment table
					$arm_debug_payment_log_add_index_arm_status = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_debug_payment_log." where Key_name=%s ",'arm-debug-payment-log-status') ); //phpcs:ignore --Reason $arm_tbl_arm_debug_payment_log is a table name
					if(empty($arm_debug_payment_log_add_index_arm_status))
					{
						$wpdb->query("ALTER TABLE `{$arm_tbl_arm_debug_payment_log}` ADD INDEX `arm-debug-payment-log-status` (`arm_payment_log_status`)");//phpcs:ignore --Reason $arm_tbl_arm_debug_payment_log is a table name
					}

					//Add the arm-debug-general-log-event INDEX for the general table
					$arm_debug_general_log_add_index_arm_event = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_debug_general_log." where Key_name=%s ",'arm-debug-general-log-event') );//phpcs:ignore --Reason $arm_tbl_arm_debug_general_log is a table name
					if(empty($arm_debug_general_log_add_index_arm_event))
					{
						$wpdb->query("ALTER TABLE `{$arm_tbl_arm_debug_general_log}` ADD INDEX `arm-debug-general-log-event` (`arm_general_log_event`)");//phpcs:ignore --Reason $arm_tbl_arm_debug_general_log is a table name
					}
				}

				/* Plugin Action Hook After Install Process */
				do_action( 'arm_after_activation_hook' );
				do_action( 'arm_after_install' );

				add_option('armember_lite_install_date', current_time('mysql') );

			} else {

				$ARMemberLite->wpdbfix();
				do_action( 'arm_reactivate_plugin' );
			}
		}

		$args  = array(
			'role'   => 'administrator',
			'fields' => 'id',
		);
		$users = get_users( $args );
		if ( count( $users ) > 0 ) {
			foreach ( $users as $key => $user_id ) {
				$armroles = $ARMemberLite->arm_capabilities();
				$userObj  = new WP_User( $user_id );
				foreach ( $armroles as $armrole => $armroledescription ) {
					$userObj->add_cap( $armrole );
				}
				unset( $armrole );
				unset( $armroles );
				unset( $armroledescription );
			}
		}
	}

	function armember_check_db_permission()
    {
        global $wpdb;
        $results = $wpdb->get_results("SHOW GRANTS FOR CURRENT_USER;"); //phpcs:ignore --Reason $wpdb is a global variable.
        $allowed_index = 0;
        foreach($results as $result)
        {
            if(is_object($result))
            {
                foreach($result as $res)
                {
                    $result_data = stripslashes_deep($res);
                }
            }
            else {
                $result_data = stripslashes_deep($result);
            }
            if( (strpos($result_data, "ALL PRIVILEGES") !== false || strpos($result_data, "INDEX") !== false) && (strpos($result_data, "ON *.*") || strpos($result_data, "`".DB_NAME."`") ) )
            {
                $allowed_index = 1;
                break;
            }
        }
        return $allowed_index;
    }

	function install_default_templates() {
		include MEMBERSHIPLITE_CLASSES_DIR . '/templates.arm_member_forms_templates.php';
	}

	function update_default_pages_for_templates() {
		global $wpdb, $ARMemberLite;
		$global_settings      = get_option( 'arm_global_settings' );
		$arm_settings         = maybe_unserialize( $global_settings );
		$page_settings        = $arm_settings['page_settings'];
		$forms                = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".$ARMemberLite->tbl_arm_forms." WHERE (`arm_form_slug` LIKE %s OR `arm_form_slug` LIKE %s OR `arm_form_slug` LIKE %s OR `arm_form_slug` LIKE %s) AND arm_is_template = %d",'template-login%', 'template-registration%', 'template-forgot%','template-change%',1) ); //phpcs:ignore --Reason $tbl_arm_forms is table name
		if ( count( $forms ) > 0 ) {
			foreach ( $forms as $key => $value ) {
				$form_id                                      = $value->arm_form_id;
				$form_settings                                = maybe_unserialize( $value->arm_form_settings );
				$form_settings['redirect_page']               = $page_settings['edit_profile_page_id'];
				$form_settings['registration_link_type_page'] = $page_settings['register_page_id'];
				$form_settings['forgot_password_link_type_page'] = $page_settings['forgot_password_page_id'];
				$form_settings                                   = maybe_serialize( $form_settings );
				$formData                                        = array( 'arm_form_settings' => $form_settings );
				$form_update                                     = $wpdb->update( $ARMemberLite->tbl_arm_forms, $formData, array( 'arm_form_id' => $form_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			}
		}
	}

	function arm_install_plugin_data() {
		global $wp, $wpdb, $arm_members_directory, $arm_access_rules, $arm_email_settings, $arm_subscription_plans;
		$is_activate = get_option( 'arm_plugin_activated', 0 );
		if ( $is_activate == '1' ) {
			delete_option( 'arm_plugin_activated' );
			/**
			 * Install Plugin Default Data For The First Time.
			 */
			/* Create Free Plan. */
			$arm_subscription_plans->arm_insert_sample_subscription_plan();
			/* Install default templates */
			$arm_email_settings->arm_insert_default_email_templates();
			/* Install Default Profile Template */
			$arm_members_directory->arm_insert_default_member_templates();

			/* Install Default Rules */
			$arm_access_rules->install_rule_data();

			$arm_access_rules->install_redirection_settings();
		}
	}

	/**
	 * Add Custom User Role & Capabilities
	 */
	function add_user_role_and_capabilities() {
		global $wp, $wpdb, $wp_roles, $ARMemberLite, $arm_members_class, $arm_global_settings;
		$role_name  = 'ARMember';
		$role_slug  = sanitize_title( $role_name );
		$basic_caps = array(
			$role_slug => true,
			'read'     => true,
			'level_0'  => true,
		);

		$wp_roles->add_role( $role_slug, $role_name, $basic_caps );
		$arm_user_role = $wp_roles->get_role( $role_slug );

		$wpdb->query( "DELETE FROM ".$ARMemberLite->tbl_arm_members ); //phpcs:ignore --Reason: $tbl_arm_members is table name

		$user_table     = $wpdb->users;
		$usermeta_table = $wpdb->usermeta;
		if ( is_multisite() ) {
			$capability_column           = $wpdb->get_blog_prefix( $GLOBALS['blog_id'] ) . 'capabilities';
			$allMembers = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".$user_table." u INNER JOIN ".$usermeta_table." um ON u.ID = um.user_id WHERE 1=1 AND um.meta_key = %s",$capability_column) );//phpcs:ignore --Reason $user_table and $usermeta_table is a table name
		} else {
			$allMembers =  $wpdb->get_results("SELECT * FROM ".$wpdb->users);//phpcs:ignore --Reason: $user_table and $wpdb->users is a table name. False Positive Alarm
		}
		$chunk_size = 100;
		if ( ! empty( $allMembers ) ) {

			$arm_total_users = count( $allMembers );

			if ( $arm_total_users <= 15000 ) {
				$chunk_size = 100;
			} elseif ( $arm_total_users > 15000 && $arm_total_users <= 25000 ) {
				$chunk_size = 200;
			} elseif ( $arm_total_users > 25000 && $arm_total_users <= 50000 ) {
				$chunk_size = 300;
			} elseif ( $arm_total_users > 50000 && $arm_total_users <= 100000 ) {
				$chunk_size = 400;
			} else {
				$chunk_size = 500;
			}

			$i              = 0;
			$chunked_values = '';
			foreach ( $allMembers as $member ) {
				$i++;
				$user_id                 = $member->ID;
				$arm_user_id             = $user_id;
				$arm_user_login          = $member->user_login;
				$arm_user_nicename       = $member->user_nicename;
				$arm_user_email          = $member->user_email;
				$arm_user_url            = $member->user_url;
				$arm_user_registered     = $member->user_registered;
				$arm_user_activation_key = $member->user_activation_key;
				$arm_user_status         = $member->user_status;
				$arm_display_name        = $member->display_name;
				$arm_user_type           = 0;
				$arm_primary_status      = 1;
				$arm_secondary_status    = 0;
				if ( $i == 1 ) {
					$chunked_values .= '(' . $arm_user_id . ',"' . $arm_user_login . '","' . $arm_user_nicename . '","' . $arm_user_email . '","","' . $arm_user_registered . '","' . $arm_user_activation_key . '",' . $arm_user_status . ',"' . $arm_display_name . '",0,1,0)';
				} else {
					$chunked_values .= ',(' . $arm_user_id . ',"' . $arm_user_login . '","' . $arm_user_nicename . '","' . $arm_user_email . '","","' . $arm_user_registered . '","' . $arm_user_activation_key . '",' . $arm_user_status . ',"' . $arm_display_name . '",0,1,0)';
				}
				if ( $i == $chunk_size && ( ! empty( $chunked_values ) || $chunked_values != '' ) ) {
					$wpdb->query( 'INSERT INTO `' . $ARMemberLite->tbl_arm_members . '` (arm_user_id, arm_user_login, arm_user_nicename, arm_user_email, arm_user_url,arm_user_registered, arm_user_activation_key, arm_user_status,arm_display_name, arm_user_type, arm_primary_status,arm_secondary_status) VALUES ' . $chunked_values );//phpcs:ignore -- Reason $ARMemberLite->tbl_arm_members is a table name
					$i              = 0;
					$chunked_values = '';
				}
			}
			if ( ! empty( $chunked_values ) || $chunked_values != '' ) {
				$wpdb->query( 'INSERT INTO `' . $ARMemberLite->tbl_arm_members . '` (arm_user_id, arm_user_login, arm_user_nicename, arm_user_email, arm_user_url,arm_user_registered, arm_user_activation_key, arm_user_status,arm_display_name, arm_user_type, arm_primary_status,arm_secondary_status) VALUES ' . $chunked_values );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_members is a table name
			}
		}
	}

	/**
	 * Check and Add Custom User Role & Capabilities for new users - after plugin reactivation
	 */

	 function check_new_users_after_plugin_reactivation() {

		global $wpdb, $ARMemberLite;
		$user_table     = $wpdb->users;
		$usermeta_table = $wpdb->usermeta;

		$get_all_armembers = $wpdb->get_results( "select * from $ARMemberLite->tbl_arm_members", ARRAY_A );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_members is a table name
		$push_user_ids     = array();
		$where             = "WHERE 1=1";
		$where1            = '';
		foreach ( $get_all_armembers as $new_user_id ) {
			$push_user_ids[] = $new_user_id['arm_user_id'];
		}
		if (!empty($push_user_ids)) {
			if (is_multisite()) {
				$where1 = " AND u.ID NOT IN (" . implode(", ", $push_user_ids) . ") "; //phpcs:ignore
			} else {
				$where .= " AND `ID` NOT IN (" . implode(", ", $push_user_ids) . ") "; //phpcs:ignore
			}
		}
		$list_to_include_new_users=array();
		if ( is_multisite() ) {
			$capability_column           = $wpdb->get_blog_prefix( $GLOBALS['blog_id'] ) . 'capabilities';
			$where1 .= $wpdb->prepare(" AND um.meta_key = %s ",$capability_column);
			$list_to_include_new_users = $wpdb->get_results("SELECT * FROM `".$user_table."` u INNER JOIN `".$usermeta_table."` um  ON u.ID = um.user_id WHERE 1=1 ".$where1, ARRAY_A);//phpcs:ignore --Reason $user and $wpdb->usermeta_table is table name
		} else {
			$list_to_include_new_users = $wpdb->get_results("SELECT * FROM $wpdb->users ".$where, ARRAY_A);//phpcs:ignore --Reason: $wpdb->users is a table name. False Positive alarm
		}

		if ( ! empty( $list_to_include_new_users ) ) {

			$arm_total_users = count( $list_to_include_new_users );

			if ( $arm_total_users <= 15000 ) {
				$chunk_size = 100;
			} elseif ( $arm_total_users > 15000 && $arm_total_users <= 25000 ) {
				$chunk_size = 200;
			} elseif ( $arm_total_users > 25000 && $arm_total_users <= 50000 ) {
				$chunk_size = 300;
			} elseif ( $arm_total_users > 50000 && $arm_total_users <= 100000 ) {
				$chunk_size = 400;
			} else {
				$chunk_size = 500;
			}

			$chunked_values = '';
			$i              = 0;
			foreach ( $list_to_include_new_users as $key => $new_users_data ) {
				$i++;
				$arm_user_id             = $new_users_data['ID'];
				$arm_user_login          = $new_users_data['user_login'];
				$arm_user_nicename       = $new_users_data['user_nicename'];
				$arm_user_email          = $new_users_data['user_email'];
				$arm_user_url            = $new_users_data['user_url'];
				$arm_user_registered     = $new_users_data['user_registered'];
				$arm_user_activation_key = $new_users_data['user_activation_key'];
				$arm_user_status         = $new_users_data['user_status'];
				$arm_display_name        = $new_users_data['display_name'];
				$arm_user_type           = 0;
				$arm_primary_status      = 1;
				$arm_secondary_status    = 0;
				if ( $i == 1 ) {
					$chunked_values .= "(" . $arm_user_id . ",\"" . $arm_user_login . "\",\"" . $arm_user_nicename . "\",\"" . $arm_user_email . "\",\"\",\"" . $arm_user_registered . "\",\"" . $arm_user_activation_key . "\"," . $arm_user_status . ",\"" . $arm_display_name . "\",0,1,0)";
				} else {
					$chunked_values .= ",(" . $arm_user_id . ",\"" . $arm_user_login . "\",\"" . $arm_user_nicename . "\",\"" . $arm_user_email . "\",\"\",\"" . $arm_user_registered . "\",\"" . $arm_user_activation_key . "\"," . $arm_user_status . ",\"" . $arm_display_name . "\",0,1,0)";
				}
				if ( $i == $chunk_size && $chunked_values != '' ) {
					$wpdb->query( 'INSERT INTO `' . $ARMemberLite->tbl_arm_members . '` (arm_user_id, arm_user_login, arm_user_nicename, arm_user_email, arm_user_url,arm_user_registered, arm_user_activation_key, arm_user_status,arm_display_name, arm_user_type, arm_primary_status,arm_secondary_status) VALUES ' . $chunked_values);//phpcs:ignore --Reason $ARMemberLite->tbl_arm_members is a table name
					$i              = 0;
					$chunked_values = '';
				}
			}

			if ( ! empty( $chunked_values ) || $chunked_values != '' ) {
				$wpdb->query( 'INSERT INTO `' . $ARMemberLite->tbl_arm_members . '` (arm_user_id, arm_user_login, arm_user_nicename, arm_user_email, arm_user_url,arm_user_registered, arm_user_activation_key, arm_user_status,arm_display_name, arm_user_type, arm_primary_status,arm_secondary_status) VALUES ' . $chunked_values);//phpcs:ignore --Reason $ARMemberLite->tbl_arm_members is a table name
			}
		}
	}

	/**
	 * Install Default Member Forms & thier fields into Database
	 */
	function install_member_form_fields() {
		global $wp, $wpdb, $arm_lite_errors, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings;
		/* Add Default Preset Fields */
		$defaultFields = $arm_member_forms->arm_default_preset_user_fields();
		unset( $defaultFields['social_fields'] );
		$defaultPresetFields = array( 'default' => $defaultFields );
		update_option( 'arm_preset_form_fields', $defaultPresetFields );
		/* Add Default Forms */
		$tbl_arm_forms      = $wpdb->prefix . 'arm_forms';
		$tbl_arm_form_field = $wpdb->prefix . 'arm_form_field';

		$default_member_forms_data = $arm_member_forms->arm_default_member_forms_data();
		$insertedFields            = array();
		foreach ( $default_member_forms_data as $key => $val ) {
			$arm_set_id   = 0;
			$arm_set_name = '';
			if ( in_array( $key, array( 'login', 'forgot_password', 'change_password' ) ) ) {
				$arm_set_name = esc_html__( 'Default Set', 'armember-membership' );
				$arm_set_id   = 1;
			}
			$form_data = array(
				'arm_form_label'        => $val['name'],
				'arm_form_title'        => $val['name'],
				'arm_form_type'         => $key,
				'arm_form_slug'         => $val['form_slug'],
				'arm_is_default'        => '1',
				'arm_set_name'          => $arm_set_name,
				'arm_set_id'            => $arm_set_id,
				'arm_ref_template'      => '1',
				'arm_form_updated_date' => current_time( 'mysql' ),
				'arm_form_created_date' => current_time( 'mysql' ),
				'arm_form_settings'     => maybe_serialize( $val['settings'] ),
			);
			/* Insert Form Data */
			$wpdb->insert( $tbl_arm_forms, $form_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$form_id = $wpdb->insert_id;
			if ( ! empty( $val['fields'] ) ) {
				$i = 1;
				foreach ( $val['fields'] as $field ) {
					$fid = isset( $field['id'] ) ? $field['id'] : $field['meta_key'];
					if ( $fid == 'repeat_pass' ) {
						$field['ref_field_id'] = $insertedFields[ $key ]['user_pass'];
					}
					$form_field_data = array(
						'arm_form_field_form_id'      => $form_id,
						'arm_form_field_order'        => $i,
						'arm_form_field_slug'         => isset( $field['meta_key'] ) ? $field['meta_key'] : '',
						'arm_form_field_created_date' => current_time( 'mysql' ),
						'arm_form_field_option'       => maybe_serialize( $field ),
					);
					/* Insert Form Fields. */
					$wpdb->insert( $tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$insert_field_id                = $wpdb->insert_id;
					$insertedFields[ $key ][ $fid ] = $insert_field_id;
					$i++;
				}
			}
		}
	}

	/**
	 * Install Default Plugin Pages into Database
	 */
	function install_default_pages() {
		global $wp, $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings;
		/* Default Global Settings */
		$arm_settings = $arm_global_settings->arm_default_global_settings();
		/* Default Pages */
		$arm_pages = $arm_global_settings->arm_default_pages_content();
		if ( ! empty( $arm_pages ) ) {
			foreach ( $arm_pages as $pageIDKey => $page ) {
				$page_id = wp_insert_post( $page );
				if ( $page_id != 0 ) {
					$arm_settings['page_settings'][ $pageIDKey ] = $page_id;
				}
			}
		}
		/* Store Global Setting into DB */
		if ( ! empty( $arm_settings ) ) {
			$new_global_settings = $arm_settings;
			update_option( 'arm_global_settings', $new_global_settings );
			/**
			 * Update Redirection pages in member forms
			 */
			$allForms = $arm_member_forms->arm_get_all_member_forms( '`arm_form_id`, `arm_form_type`, `arm_form_settings`' );
			if ( ! empty( $allForms ) ) {
				foreach ( $allForms as $form ) {
					$form_id       = $form['arm_form_id'];
					$form_settings = $form['arm_form_settings'];
					$isFormUpdate  = false;
					switch ( $form['arm_form_type'] ) {
						case 'registration':
							$isFormUpdate                   = true;
							$form_settings['redirect_type'] = 'page';
							$form_settings['redirect_page'] = $arm_settings['page_settings']['edit_profile_page_id'];
							break;
						case 'login':
							$isFormUpdate                                    = true;
							$form_settings['redirect_type']                  = 'page';
							$form_settings['redirect_page']                  = $arm_settings['page_settings']['edit_profile_page_id'];
							$form_settings['registration_link_type']         = 'page';
							$form_settings['registration_link_type_page']    = $arm_settings['page_settings']['register_page_id'];
							$form_settings['forgot_password_link_type_page'] = $arm_settings['page_settings']['forgot_password_page_id'];
							break;
					}
					if ( $isFormUpdate ) {
						$formData    = array( 'arm_form_settings' => maybe_serialize( $form_settings ) );
						$form_update = $wpdb->update( $ARMemberLite->tbl_arm_forms, $formData, array( 'arm_form_id' => $form_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					}
				}
			}
		}
		/* Update Security Settings */
		$securitySettings = $arm_global_settings->arm_get_all_block_settings();
		update_option( 'arm_block_settings', $securitySettings );
	}

	public static function uninstall() {
		global $wpdb;
		$arm_uninstall = false;
		if ( !is_plugin_active( 'armember/armember.php' ) && !file_exists( WP_PLUGIN_DIR . '/armember/armember.php' ) ) {
			   $arm_uninstall = true;
		}
		if ( is_multisite() ) {
			$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			if ( $blogs ) {
				foreach ( $blogs as $blog ) {
					switch_to_blog( $blog['blog_id'] );
					delete_option( 'armlite_version' );
					if ( $arm_uninstall ) {
						self::arm_uninstall();
					}
				}
				restore_current_blog();
			}
		} else {
			if ( $arm_uninstall ) {
						self::arm_uninstall();
			}
		}
		/* Plugin Action Hook After Uninstall Process */
		do_action( 'arm_after_uninstall' );
	}

	public static function arm_uninstall() {
		global $wpdb, $arm_members_class;
		/**
		 * To Cancel User's Recurring Subscription from Payment Gateway
		 */


		$query_member_users  = $wpdb->get_results( 'SELECT arm_user_id FROM '.$wpdb->prefix . 'arm_members' );//phpcs:ignore --Reason: $wpdb->prefix.arm_members is a table name. False Positive Alarm
		if ( ! empty( $query_member_users ) ) {
			foreach ( $query_member_users as $query_member_user ) {
				$chk_subscription_arm_user_id = $query_member_user->arm_user_id;
				$arm_members_class->arm_before_delete_user_action( $chk_subscription_arm_user_id );
			}
		}

		/**
		 * Delete Meta Values
		 */
		$wpdb->query( $wpdb->prepare('DELETE FROM `' . $wpdb->options . "` WHERE  `option_name` LIKE %s",'%arm\_%') ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( $wpdb->prepare('DELETE FROM `' . $wpdb->postmeta . "` WHERE  `meta_key` LIKE %s",'%arm\_%')); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( $wpdb->prepare('DELETE FROM `' . $wpdb->usermeta . "` WHERE  `meta_key` LIKE %s",'%arm\_%') ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		delete_option( 'armlite_version' );
		delete_option( 'armIsSorted' );
		delete_option( 'armSortOrder' );
		delete_option( 'armSortId' );
		delete_option( 'armSortInfo' );
		delete_option( 'arm_lite_new_version_installed' );

		delete_site_option( 'armIsSorted' );
		delete_site_option( 'armSortOrder' );
		delete_site_option( 'armSortId' );
		delete_site_option( 'armSortInfo' );
		delete_site_option( 'arm_version_1_7_installed' );

		/**
		 * Delete Plugin DB Tables
		 */
		$blog_tables = array(
			$wpdb->prefix . 'arm_activity',
			$wpdb->prefix . 'arm_auto_message',
			$wpdb->prefix . 'arm_coupons',
			$wpdb->prefix . 'arm_email_templates',
			$wpdb->prefix . 'arm_entries',
			$wpdb->prefix . 'arm_fail_attempts',
			$wpdb->prefix . 'arm_forms',
			$wpdb->prefix . 'arm_form_field',
			$wpdb->prefix . 'arm_lockdown',
			$wpdb->prefix . 'arm_members',
			$wpdb->prefix . 'arm_membership_setup',
			$wpdb->prefix . 'arm_payment_log',
			$wpdb->prefix . 'arm_payment_log_temp',
			$wpdb->prefix . 'arm_bank_transfer_log',
			$wpdb->prefix . 'arm_subscription_plans',
			$wpdb->prefix . 'arm_termmeta',
			$wpdb->prefix . 'arm_member_templates',
			$wpdb->prefix . 'arm_drip_rules',
			$wpdb->prefix . 'arm_badges_achievements',
			$wpdb->prefix . 'arm_login_history',
			$wpdb->prefix . 'arm_debug_general_log',
			$wpdb->prefix . 'arm_debug_payment_log',
			$wpdb->prefix . 'arm_dripped_contents'
		);
		foreach ( $blog_tables as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS ".$table );//phpcs:ignore --Reason: $table is a table name. False Positive Alarm
		}
		return true;
	}

	/**
	 * Get Current Browser Info
	 */
	function getBrowser( $user_agent ) {
		$u_agent  = $user_agent;
		$bname    = 'Unknown';
		$platform = 'Unknown';
		$version  = '';
		$ub       = '';

		/* First get the platform? */
		if ( @preg_match( '/linux/i', $u_agent ) ) {
			$platform = 'linux';
		} elseif ( @preg_match( '/macintosh|mac os x/i', $u_agent ) ) {
			$platform = 'mac';
		} elseif ( @preg_match( '/windows|win32/i', $u_agent ) ) {
			$platform = 'windows';
		}

		/* Next get the name of the useragent yes seperately and for good reason */
		if ( @preg_match( '/MSIE/i', $u_agent ) && ! @preg_match( '/Opera/i', $u_agent ) ) {
			$bname = 'Internet Explorer';
			$ub    = 'MSIE';
		} elseif ( @preg_match( '/Firefox/i', $u_agent ) ) {
			$bname = 'Mozilla Firefox';
			$ub    = 'Firefox';
		} elseif ( @preg_match( '/OPR/i', $u_agent ) ) {
			$bname = 'Opera';
			$ub    = 'OPR';
		} elseif ( @preg_match( '/Edge/i', $u_agent ) ) {
			$bname = 'Edge';
			$ub    = 'Edge';
		} elseif ( @preg_match( '/Chrome/i', $u_agent ) ) {
			$bname = 'Google Chrome';
			$ub    = 'Chrome';
		} elseif ( @preg_match( '/Safari/i', $u_agent ) ) {
			$bname = 'Apple Safari';
			$ub    = 'Safari';
		} elseif ( @preg_match( '/Opera/i', $u_agent ) ) {
			$bname = 'Opera';
			$ub    = 'Opera';
		} elseif ( @preg_match( '/Netscape/i', $u_agent ) ) {
			$bname = 'Netscape';
			$ub    = 'Netscape';
		} elseif ( @preg_match( '/Trident/', $u_agent ) ) {
			$bname = 'Internet Explorer';
			$ub    = 'rv';
		}
		/* finally get the correct version number */
		$known   = array( 'Version', $ub, 'other' );
		$pattern = '#(?<browser>' . join( '|', $known ) . ')[/ |:]+(?<version>[0-9.|a-zA-Z.]*)#';

		if ( ! @preg_match_all( $pattern, $u_agent, $matches ) ) {
			/* we have no matching number just continue */
		}

		/* see how many we have */
		$i = count( $matches['browser'] );
		if ( $i != 1 ) {
			/*
			 we will have two since we are not using 'other' argument yet */
			/* see if version is before or after the name */
			if ( strripos( $u_agent, 'Version' ) < strripos( $u_agent, $ub ) ) {
				$version = $matches['version'][0];
			} else {
				$version = $matches['version'][1];
			}
		} else {
			$version = $matches['version'][0];
		}

		/* check if we have a number */
		if ( $version == null || $version == '' ) {
			$version = '?';
		}

		return array(
			'userAgent' => $u_agent,
			'name'      => $bname,
			'version'   => $version,
			'platform'  => $platform,
			'pattern'   => $pattern,
		);
	}

	/**
	 * Get Current IP Address of User/Guest
	 */
	function arm_get_ip_address() {
		$ipaddress = '';
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ipaddress = sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] ); //phpcs:ignore
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ipaddress = sanitize_text_field( $_SERVER['HTTP_X_FORWARDED_FOR'] ); //phpcs:ignore
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED'] ) && ! empty( $_SERVER['HTTP_X_FORWARDED'] ) ) {
			$ipaddress = sanitize_text_field( $_SERVER['HTTP_X_FORWARDED'] ); //phpcs:ignore
		} elseif ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) && ! empty( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
			$ipaddress = sanitize_text_field( $_SERVER['HTTP_FORWARDED_FOR'] ); //phpcs:ignore
		} elseif ( isset( $_SERVER['HTTP_FORWARDED'] ) && ! empty( $_SERVER['HTTP_FORWARDED'] ) ) {
			$ipaddress = sanitize_text_field( $_SERVER['HTTP_FORWARDED'] ); //phpcs:ignore
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) && ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ipaddress = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ); //phpcs:ignore
		} else {
			$ipaddress = 'UNKNOWN';
		}
		/*
		 For Public IP Address. */
		/* $publicIP = trim(shell_exec("dig +short myip.opendns.com @resolver1.opendns.com")); */
		return $ipaddress;
	}

	function arm_write_response( $response_data, $file_name = '' ) {
		global $wp, $wpdb, $wp_filesystem;
		if ( ! empty( $file_name ) ) {
			$file_path = MEMBERSHIPLITE_DIR . '/log/' . $file_name;
		} else {
			$file_path = MEMBERSHIPLITE_DIR . '/log/response.txt';
		}
		if ( file_exists( ABSPATH . 'wp-admin/includes/file.php' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			if ( false === ( $creds = request_filesystem_credentials( $file_path, '', false, false ) ) ) {
				/**
				 * if we get here, then we don't have credentials yet,
				 * but have just produced a form for the user to fill in,
				 * so stop processing for now
				 */
				return true; /* stop the normal page form from displaying */
			}
			/* now we have some credentials, try to get the wp_filesystem running */
			if ( ! WP_Filesystem( $creds ) ) {
				/* our credentials were no good, ask the user for them again */
				request_filesystem_credentials( $file_path, $method, true, false );
				return true;
			}
			@$file_data = $wp_filesystem->get_contents( $file_path );
			$file_data .= $response_data;
			$file_data .= "\r\n===========================================================================\r\n";
			$breaks     = array( '<br />', '<br>', '<br/>' );
			$file_data  = str_ireplace( $breaks, "\r\n", $file_data );

			@$write_file = $wp_filesystem->put_contents( $file_path, $file_data, 0755 );
			if ( ! $write_file ) {
				/* esc_html_e('Error Saving Log.', 'armember-membership'); */
			}
		}
		return;
	}

	/**
	 * Function for Write Degug Log
	 */
	function arm_debug_response_log( $callback = '', $arm_restricted_cases = array(), $query_obj = array(), $executed_query = '', $is_mail_log = false ) {
		global $wp, $wpdb, $wp_filesystem;
		if ( ! defined( 'MEMBERSHIPLITE_DEBUG_LOG' ) || MEMBERSHIPLITE_DEBUG_LOG == false ) {
			return;
		}
		$arm_restricted_cases_filtered = '';
		if ( $executed_query == '' ) {
			$executed_query = $wpdb->last_query;
		}
		$arm_restriction_type = 'redirect';
		if ( ! empty( $arm_restricted_cases ) ) {
			foreach ( $arm_restricted_cases as $key => $restricted_case ) {
				if ( $restricted_case['protected'] == true ) {
					$arm_restricted_cases_filtered = $arm_restricted_cases[ $key ]['message'];
					$arm_restriction_type          = $arm_restricted_cases[ $key ]['type'];
				}
			}
		}
		$arm_debug_file_path = MEMBERSHIPLITE_DIR . '/log/restriction_response.txt';
		$date                = '[ ' . date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) . ' ]'; //phpcs:ignore
		if ( file_exists( ABSPATH . 'wp-admin/includes/file.php' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			if ( false === ( $creds = request_filesystem_credentials( $arm_debug_file_path, '', false, false ) ) ) {
				return true;
			}
			if ( ! WP_Filesystem( $creds ) ) {
				request_filesystem_credentials( $arm_debug_file_path, $method, true, false );
				return true;
			}
			$debug_log_type = MEMBERSHIPLITE_DEBUG_LOG_TYPE;
			$content        = ' Date: ' . $date . "\r\n";
			$content       .= "\r\n Function :" . $callback . "\r\n";
			if ( $is_mail_log == true ) {
				$content .= "\r\n Log Type : Mail Notification Log \r\n";
				$content .= "\r\n Mail Content : " . $arm_restricted_cases_filtered . " \r\n";
			} else {
				$content .= "\r\n Log Type : " . $debug_log_type . "\r\n";
				$content .= "\r\n Content : " . $arm_restricted_cases_filtered . "\r\n";

			}
			$content             .= "\r\n Last Executed Query:" . $executed_query . "\r\n";
			$arm_debug_file_data  = $wp_filesystem->get_contents( $arm_debug_file_path );
			$arm_debug_file_data .= $content;
			$arm_debug_file_data .= "\r\n===========================================================================\r\n";
			$breaks               = array( '<br />', '<br>', '<br/>' );
			$arm_debug_file_data  = str_ireplace( $breaks, "\r\n", $arm_debug_file_data );

			@$write_file = $wp_filesystem->put_contents( $arm_debug_file_path, $arm_debug_file_data, 0755 );
			if ( ! $write_file ) {
				/* esc_html_e('Error Saving Log.', 'armember-membership'); */
			}
		}
	}

	function arm_admin_messages_init( $page = '' ) {
		global $wp, $wpdb, $arm_lite_errors, $ARMemberLite, $pagenow, $arm_slugs;
		$success_msgs = '';
		$error_msgs   = '';
		$ARMemberLite->arm_session_start();
		if ( isset( $_SESSION['arm_message'] ) && ! empty( $_SESSION['arm_message'] ) ) {
			$arm_admin_message = $_SESSION['arm_message']; //phpcs:ignore
			foreach ( $arm_admin_message as $snotice ) {
				if ( $snotice['type'] == 'success' ) {
					$success_msgs .= $snotice['message'];
				} else {
					$error_msgs .= $snotice['message'];
				}
			}
			if ( ! empty( $success_msgs ) ) {
				?>
				<script type="text/javascript">jQuery(window).on("load", function () {
						armToast('<?php echo esc_html($snotice['message']); ?>', 'success');
					});</script>
				<?php
			} elseif ( ! empty( $error_msgs ) ) {
				?>
				<script type="text/javascript">jQuery(window).on("load", function () {
						armToast('<?php echo esc_html($snotice['message']); ?>', 'error');
					});</script>
				<?php
			}
			unset( $_SESSION['arm_message'] );
		}
		?>
		<div class="armclear"></div>
		<div class="arm_message arm_success_message" id="arm_success_message">
			<div class="arm_message_text"><?php echo esc_html($success_msgs); ?></div>
		</div>
		<div class="arm_message arm_error_message" id="arm_error_message">
			<div class="arm_message_text"><?php echo esc_html($error_msgs); ?></div>
		</div>
		<div class="armclear"></div>
		<div class="arm_toast_container" id="arm_toast_container"></div>
		<div class="arm_loading" style="display: none;"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/loader.gif" alt="Loading.."></div>
		<?php
	}

	function arm_do_not_show_video() {
		global $wp, $wpdb, $ARMemberLite, $pagenow, $arm_capabilities_global;

		$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce

		$isShow = ( isset( $_POST['isShow'] ) && $_POST['isShow'] == '0' ) ? 0 : 1; //phpcs:ignore
		$now    = strtotime( current_time( 'mysql' ) );
		$time   = strtotime( '+10 day', $now );
		update_option( 'arm_show_document_video', $isShow );
		update_option( 'arm_show_document_video_on', $time );
		exit;
	}

	function arm_add_document_video() {
		global $wp, $wpdb, $ARMemberLite, $pagenow, $arm_slugs;
		$popupData = '';
		$arm_slugs_arm_setup_wizard = isset( $arm_slugs->arm_setup_wizard ) ? $arm_slugs->arm_setup_wizard : '';
		if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], (array) $arm_slugs ) && $arm_slugs_arm_setup_wizard != $_REQUEST['page']  ) { //phpcs:ignore
			$now                    = strtotime( current_time( 'mysql' ) );
			$show_document_video    = get_option( 'arm_show_document_video', 0 );
			$show_document_video_on = get_option( 'arm_show_document_video_on', strtotime( current_time( 'mysql' ) ) );
			if ( $show_document_video == '0' ) {
				return;
			}
			if ( $show_document_video_on > $now ) {
				return;
			}
			/* Document Video Popup */
			$popupData  = '<div id="arm_document_video_popup" class="popup_wrapper arm_document_video_popup"><div class="popup_wrapper_inner">';
			$popupData .= '<div class="popup_header">';
			$popupData .= '<span class="popup_close_btn arm_popup_close_btn" onclick="armHideDocumentVideo();"></span>';
			$popupData .= '<span class="popup_header_text">' . esc_html__( 'Help Tutorial', 'armember-membership' ) . '</span>';
			$popupData .= '</div>';
			$popupData .= '<div class="popup_content_text">';
			$popupData .= '<iframe src="' . esc_attr(MEMBERSHIPLITE_VIDEO_URL) . '" allowfullscreen="" frameborder="0"> </iframe> ';
			$popupData .= '</div>';
			$popupData .= '<div class="armclear"></div>';
			$popupData .= '<div class="popup_content_btn popup_footer">';
			$popupData .= '<label><input type="checkbox" id="arm_do_not_show_video" class="arm_do_not_show_video arm_icheckbox"><span>' . esc_html__( 'Do not show again.', 'armember-membership' ) . '</span></label>';
			$popupData .= '<div class="popup_content_btn_wrapper">';
			$popupData .= '<button class="arm_cancel_btn popup_close_btn" onclick="armHideDocumentVideo();" type="button">' . esc_html__( 'Close', 'armember-membership' ) . '</button>';
			$popupData .= '</div>';
			$popupData .= '<div class="armclear"></div>';
			$popupData .= '</div>';
			$popupData .= '<div class="armclear"></div>';
			$popupData .= '</div></div>';
			$wpnonce = wp_create_nonce( 'arm_wp_nonce' );
			$popupData .= '<input type="hidden" name="arm_wp_nonce" value="'.esc_attr($wpnonce).'"/>';
			$popupData .= '<script type="text/javascript">jQuery(window).on("load", function(){
				var v_width = jQuery( window ).width();
				if(v_width <= "1350")
		        {
		          var poup_width = "720";
		          var poup_height = "400";
		          jQuery("#arm_document_video_popup").css("width","760");
		          jQuery(".popup_content_text iframe").css("width",poup_width);
		          jQuery(".popup_content_text iframe").css("height",poup_height);
		          
		        }
		        if(v_width > "1350" && v_width <= "1600")
		        {
		          var poup_width = "750";
		          var poup_height = "430";

		          jQuery("#arm_document_video_popup").css("width","790");
		          jQuery(".popup_content_text iframe").css("width",poup_width);
		          jQuery(".popup_content_text iframe").css("height",poup_height);
		        }
		        if(v_width > "1600")
		        {
		          var poup_width = "800";
		          var poup_height = "450";
		          jQuery("#arm_document_video_popup").css("width","840");
		          jQuery(".popup_content_text iframe").css("width",poup_width);
		          jQuery(".popup_content_text iframe").css("height",poup_height);
		        }
				jQuery("#arm_document_video_popup").bPopup({
					modalClose: false,
					closeClass: "popup_close_btn",
					onClose: function(){
               			 jQuery(this).find(".popup_wrapper_inner .popup_content_text").html("");
         			},
				});
			});</script>';
			echo $popupData; //phpcs:ignore
		}
	}

	function arm_add_new_version_release_note() {
		global $wp, $wpdb, $ARMemberLite, $pagenow, $arm_slugs, $arm_lite_version;
		$popupData = '';
		if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], (array) $arm_slugs ) ) { //phpcs:ignore

			$show_document_video = get_option( 'arm_lite_new_version_installed', 0 );

			if ( $show_document_video == '0' ) {
				return;
			}

			$urltopost = 'https://www.armemberplugin.com/armember_addons/addon_whatsnew_list.php?arm_version=' . $arm_lite_version . '&arm_list_type=whatsnew_list';

			$raw_response = wp_remote_post(
				$urltopost,
				array(
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array(),
					// 'body' => array('plugins' => urlencode(serialize($installed_plugins)), 'wpversion' => $encodedval),
					'cookies'     => array(),
				)
			);

			$addon_list_html = '';
			if ( is_wp_error( $raw_response ) || $raw_response['response']['code'] != 200 ) {
				$addon_list_html .= "<div class='error_message' style='margin-top:100px; padding:20px;'>" . esc_html__( 'Add-On listing is currently unavailable. Please try again later.', 'armember-membership' ) . '</div>';
			} else {
				$addon_list                = json_decode( $raw_response['body'] );
				$addon_count               = count( $addon_list );
				$arm_whtsnew_wrapper_width = $addon_count * 141;
				foreach ( $addon_list as $list ) {

					$addon_list_html .= '<div class="arm_add_on">';
					$addon_list_html .= '<a href="' . esc_url($list->addon_url) . '" target="_blank"><img src="' . esc_attr($list->addon_icon_url) . '" /></a>';//phpcs:ignore
					$addon_list_html .= '<div class="arm_add_on_text">';
					$addon_list_html .= '<a href="' . esc_url($list->addon_url) . '" target="_blank">' . $list->addon_name . '</a>';
					$addon_list_html .= '</div>';
					$addon_list_html .= '</div>';
				}
			}

			$popupData     = '<div id="arm_update_note" class="popup_wrapper arm_update_note">'
					. '<div class="popup_wrapper_inner">';
			$popupData    .= '<div class="popup_header">';
			$popupData    .= '<img src="' . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/logo_addon.png" />'; //phpcs:ignore
			$popupData    .= '</div>';
			$popupData    .= '<div class="popup_content_text">';
			$i             = 1;
			$major_changes = false;
			$change_log    = $this->arm_new_version_changelog();

			if ( isset( $change_log ) && ! empty( $change_log ) ) {

				$arm_show_critical_change_title = isset( $change_log['show_critical_title'] ) ? $change_log['show_critical_title'] : 0;
				$arm_critical_title             = isset( $change_log['critical_title'] ) ? $change_log['critical_title'] : '';
				$arm_critical_changes           = ( isset( $change_log['critical'] ) && ! empty( $change_log['critical'] ) ) ? $change_log['critical'] : array();

				$arm_show_major_change_title = isset( $change_log['show_major_title'] ) ? $change_log['show_major_title'] : 0;
				$arm_major_title             = isset( $change_log['major_title'] ) ? $change_log['major_title'] : '';
				$arm_major_changes           = ( isset( $change_log['major'] ) && ! empty( $change_log['major'] ) ) ? $change_log['major'] : array();

				$arm_show_other_change_title = isset( $change_log['show_other_title'] ) ? $change_log['show_other_title'] : 0;
				$arm_other_title             = isset( $change_log['other_title'] ) ? $change_log['other_title'] : '';
				$arm_other_changes           = ( isset( $change_log['other'] ) && ! empty( $change_log['other'] ) ) ? $change_log['other'] : array();

				if ( ! empty( $arm_critical_changes ) ) {
					if ( $arm_show_critical_change_title == 1 ) {
						$popupData .= '<div class="arm_critical_change_title">' . esc_html( $arm_critical_title ) . '</div>';
					}
					$popupData .= '<div class="arm_critical_change_list"><ul>';
					foreach ( $arm_critical_changes as $value ) {
						$popupData .= '<li>' . esc_html( $value ) . '</li>';
					}
					$popupData .= '</ul></div>';
				}

				if ( ! empty( $arm_major_changes ) ) {
					if ( $arm_show_major_change_title == 1 ) {
						$popupData .= '<div class="arm_major_change_title">' . esc_html( $arm_major_title ) . '</div>';
					}
					$popupData .= '<div class="arm_major_change_list"><ul>';
					foreach ( $arm_major_changes as $value ) {
						$popupData .= '<li>' . esc_html( $value ) . '</li>';
					}
					$popupData .= '</ul></div>';
				}

				if ( ! empty( $arm_other_changes ) ) {
					if ( $arm_show_other_change_title == 1 ) {
						$popupData .= '<div class="arm_other_change_title">' . esc_html( $arm_other_title ) . '</div>';
					}
					$popupData .= '<div class="arm_other_change_list"><ul>';
					foreach ( $arm_other_changes as $value ) {
						$popupData .= '<li>' . esc_html( $value ) . '</li>';
					}
					$popupData .= '</ul></div>';
				}
			}

			$popupData .= '</div>';
			$popupData .= '<div class="arm_addons_list_title">' . esc_html__( 'Available Modules', 'armember-membership' ) . '</div>';

			$popupData .= '<div class="arm_addons_list_div">';
			$popupData .= '<div class="arm_addons_list" style="width:' . $arm_whtsnew_wrapper_width . 'px;">';

			$popupData .= $addon_list_html;
			$popupData .= '</div>';
			$popupData .= '</div>';

			$popupData .= '<div class="armclear"></div>';
			$popupData .= '<div class="popup_content_btn popup_footer">';
			if ( ! empty( $arm_critical_changes ) ) {
				$popupData .= '<label><input type="checkbox" id="arm_hide_update_notice" class="arm_icheckbox"><span>' . esc_html__( 'I agree', 'armember-membership' ) . '</span></label>';
				$popupData .= '<div class="popup_content_btn_wrapper">';
				$popupData .= '<button class="arm_cancel_btn popup_close_btn" onclick="arm_hide_update_notice();" type="button">' . esc_html__( 'Close', 'armember-membership' ) . '</button>';
				$popupData .= '</div>';
				$popupData .= '<div class="armclear"></div>';
			} else {
				$popupData .= '<div style="display: none;"><input type="checkbox" id="arm_hide_update_notice" class="arm_icheckbox" value="1" checked="checked"></div>';
			}
			$popupData .= '</div>';
			$popupData .= '<div class="armclear"></div>';
			$popupData .= '</div></div>';
			$popupData .= '<script type="text/javascript">jQuery(window).on("load", function(){
				
				jQuery("#arm_update_note").bPopup({
					modalClose: false,  
escClose : false                                        
				});

			});
                                                        function arm_hide_update_notice()
{
    var ishide = 0;
    if (jQuery("#arm_hide_update_notice").is(":checked")) {
	var ishide = 1;                   
	    jQuery("#arm_update_note").bPopup().close(); 
    }else{
        return;
    }
	var _arm_wpnonce   = jQuery( \'input[name="arm_wp_nonce"]\' ).val();
    jQuery.ajax({
	type: "POST",
	url: __ARMAJAXURL,
	data: "action=arm_dont_show_upgrade_notice&is_hide=" + ishide + "&_wpnonce=" + _arm_wpnonce,
	success: function (res) {

            return false;
            
	}
    });
    return false;
}
</script>';
			echo $popupData; //phpcs:ignore
		}
	}

	/*
	 * for red color note `|^|Use coupon for invitation link`
	 * Add important note to `major`
	 * Add normal changelog to `other`
	 */

	function arm_new_version_changelog() {
		$arm_change_log = array();
		global $arm_payment_gateways, $arm_global_settings, $arm_slugs;
		$active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

		$arm_change_log = array(
			'show_critical_title' => 1,
			'critical_title'      => 'Version 4.0.66 Changes',
			'critical'            => array(
				'Accessibility Improvements',
				'Other Minor bug fixes.',
			),
			'show_major_title'    => 0,
			'major_title'         => 'Major Changes',
			'major'               => array(),
			'show_other_title'    => 0,
			'other_title'         => 'Other Changes',
			'other'               => array(),
		);

		return $arm_change_log;
	}

	function arm_get_need_help_html_content($page_name) {
        $return_html = '';
        if(!empty($page_name)) {
            $return_html .= '<div class="arm_need_help_main_wrapper arm_need_help_main_wrapper_active">';
                $return_html .= '<span class="arm_need_help_wrapper arm_need_help_icon arm_need_help_btn arm_help_question_icon armhelptip" data-param="'.esc_attr($page_name).'" title="' . esc_attr__('Documentation', 'armember-membership') . '"><svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M24.9129 12C25.6432 12 26.3735 12 27.0964 12C27.111 12.0584 27.1621 12.0511 27.2059 12.0511C27.7171 12.0877 28.221 12.1607 28.7176 12.263C34.5523 13.4319 38.9849 18.0927 39.8539 23.9517C39.8978 24.2658 39.8832 24.5945 40 24.9014C40 25.6465 40 26.399 40 27.1441C39.9124 27.1734 39.9489 27.2537 39.9416 27.3049C39.8905 27.8235 39.8174 28.3349 39.7079 28.8463C38.43 34.866 33.4861 39.3296 27.3958 39.9286C24.1681 40.2428 21.1667 39.5195 18.421 37.7662C13.5648 34.6541 11.1112 28.7879 12.2942 23.1554C13.5064 17.3914 18.1143 13.0447 23.9563 12.1534C24.2849 12.1023 24.6135 12.1242 24.9129 12ZM24.577 27.1295C24.577 27.2683 24.5697 27.3998 24.577 27.5386C24.6135 28.1596 24.8472 28.3934 25.4679 28.4445C25.6797 28.4591 25.8842 28.4372 26.0887 28.3861C26.4757 28.2838 26.6437 28.0573 26.6583 27.6555C26.6656 27.4802 26.6583 27.3122 26.6656 27.1368C26.6875 26.5816 26.8919 26.1141 27.3009 25.7342C27.4615 25.5881 27.6295 25.442 27.7975 25.3105C28.4182 24.8064 28.9659 24.2439 29.3456 23.5353C30.1781 21.9792 29.7034 20.3063 28.1407 19.5173C26.6802 18.7867 25.1612 18.7648 23.6715 19.4661C22.9924 19.7876 22.4812 20.299 22.2621 21.0514C22.0869 21.6432 22.2986 22.1618 22.8025 22.3883C23.3429 22.6367 23.708 22.5125 24.0951 21.9573C24.7012 21.0879 25.8185 20.8103 26.7751 21.2852C27.3009 21.5482 27.5346 22.0523 27.4031 22.6294C27.3228 22.9874 27.1183 23.2796 26.8627 23.528C26.6144 23.7836 26.3296 23.9955 26.0448 24.2074C25.059 24.9306 24.5551 25.9022 24.577 27.1295ZM27.0088 31.1475C27.0161 30.3731 26.3881 29.7303 25.6213 29.7157C24.8472 29.7083 24.2119 30.3293 24.1973 31.1037C24.19 31.9 24.818 32.5502 25.5921 32.5502C26.3589 32.5575 27.0015 31.9146 27.0088 31.1475Z" fill="white"/>
                </svg></span>';
                $return_html .= '<a href="https://ideas.armemberplugin.com" target="_blank" class="arm_need_help_icon arm_help_ideas_icon armhelptip" title="' . esc_attr__('Feature Request', 'armember-membership') . '"><svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M26.8205 12C27.2077 12.073 27.5949 12.1388 27.982 12.2191C31.503 12.9496 34.4104 15.9958 34.9729 19.5533C35.4258 22.4461 34.7026 25.0028 32.7595 27.2016C31.6857 28.4215 30.9552 29.7948 30.736 31.4165C30.6922 31.7233 30.663 32.0301 30.663 32.3442C30.663 32.5269 30.6045 32.5707 30.4292 32.5634C28.9901 32.5561 27.551 32.5561 26.112 32.5561C24.6071 32.5561 23.1023 32.5488 21.6048 32.5634C21.4075 32.5634 21.3491 32.5196 21.3418 32.315C21.2907 30.4669 20.677 28.8233 19.4352 27.4426C18.2518 26.1277 17.4336 24.6448 17.0757 22.9063C16.3379 19.3122 17.9231 15.5064 21.0058 13.5121C22.2257 12.7232 23.5479 12.2411 24.9943 12.0657C25.0454 12.0584 25.1112 12.0877 25.1331 12.0073C25.6883 12 26.2508 12 26.8205 12Z" fill="white"/>
                <path d="M26.0024 36.6103C24.7459 36.6103 23.4895 36.6103 22.233 36.6103C21.634 36.6103 21.3491 36.3181 21.3418 35.7264C21.3418 35.215 21.3491 34.7037 21.3418 34.1997C21.3418 34.0463 21.3783 33.9951 21.539 33.9951C24.5049 34.0024 27.478 34.0024 30.4438 33.9951C30.6191 33.9951 30.6557 34.0463 30.6557 34.2143C30.6484 34.7402 30.6557 35.2735 30.6483 35.7994C30.641 36.3035 30.3342 36.6176 29.8302 36.6176C28.5518 36.6176 27.2808 36.6103 26.0024 36.6103Z" fill="white"/>
                <path d="M23.3287 38.0781C25.1038 38.0781 26.879 38.0781 28.6541 38.0781C28.4641 39.0789 27.2369 39.9847 26.0535 39.9993C24.819 40.0139 23.5552 39.13 23.3287 38.0781Z" fill="white"/>
                </svg></a>';
		$return_html .= '<a href="https://www.facebook.com/groups/arplugins" target="_blank" class="arm_need_help_icon arm_help_join_icon armhelptip" title="' . esc_attr__('Join Community', 'armember-membership') . '"><svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M34.5741 31.3248C34.3082 29.5176 33.4033 27.8656 32.0236 26.6685C30.6439 25.4714 28.8808 24.8085 27.0541 24.8H25.3517C23.5251 24.8085 21.762 25.4714 20.3823 26.6685C19.0026 27.8656 18.0977 29.5176 17.8317 31.3248L17.0157 37.0304C16.9897 37.2148 17.0069 37.4028 17.066 37.5794C17.1252 37.7561 17.2246 37.9165 17.3565 38.048C17.6765 38.368 19.6397 40 26.2045 40C32.7693 40 34.7277 38.3744 35.0525 38.048C35.1844 37.9165 35.2839 37.7561 35.343 37.5794C35.4022 37.4028 35.4194 37.2148 35.3933 37.0304L34.5741 31.3248ZM19.0973 25.68C17.551 27.1055 16.5448 29.0217 16.2493 31.104L15.6573 35.2C10.9053 35.168 9.46533 33.44 9.22533 33.088C9.13256 32.9601 9.06635 32.815 9.03063 32.6611C8.9949 32.5072 8.99038 32.3477 9.01733 32.192L9.36933 30.208C9.55272 29.1711 9.98344 28.1938 10.625 27.3588C11.2665 26.5238 12.0999 25.8558 13.0545 25.4115C14.0092 24.9671 15.0569 24.7595 16.1088 24.8063C17.1607 24.853 18.1859 25.1527 19.0973 25.68ZM43.3853 32.192C43.4123 32.3477 43.4078 32.5072 43.372 32.6611C43.3363 32.815 43.2701 32.9601 43.1773 33.088C42.9373 33.44 41.4973 35.168 36.7453 35.2L36.1533 31.104C35.8578 29.0217 34.8517 27.1055 33.3053 25.68C34.2168 25.1527 35.2419 24.853 36.2939 24.8063C37.3458 24.7595 38.3935 24.9671 39.3481 25.4115C40.3028 25.8558 41.1362 26.5238 41.7777 27.3588C42.4192 28.1938 42.8499 29.1711 43.0333 30.208L43.3853 32.192ZM19.3693 22.16C18.9666 22.7312 18.4319 23.1967 17.8107 23.5171C17.1895 23.8374 16.5002 24.0031 15.8013 24C15.1041 24 14.4169 23.8343 13.7963 23.5166C13.1757 23.1989 12.6395 22.7383 12.2319 22.1727C11.8242 21.6072 11.5568 20.9528 11.4516 20.2636C11.3465 19.5744 11.4067 18.87 11.6271 18.2086C11.8476 17.5472 12.2221 16.9476 12.7197 16.4594C13.2174 15.9711 13.8239 15.608 14.4894 15.4002C15.1549 15.1923 15.8602 15.1455 16.5473 15.2637C17.2344 15.3819 17.8836 15.6617 18.4413 16.08C18.2809 16.7074 18.2003 17.3525 18.2013 18C18.2025 19.4675 18.6066 20.9064 19.3693 22.16ZM41.0013 19.6C41.0017 20.1779 40.8882 20.7503 40.6673 21.2843C40.4463 21.8183 40.1222 22.3036 39.7135 22.7122C39.3049 23.1209 38.8197 23.445 38.2856 23.6659C37.7516 23.8869 37.1793 24.0004 36.6013 24C35.9024 24.0031 35.2131 23.8374 34.5919 23.5171C33.9708 23.1967 33.4361 22.7312 33.0333 22.16C33.7961 20.9064 34.2001 19.4675 34.2013 18C34.2024 17.3525 34.1218 16.7074 33.9613 16.08C34.615 15.5897 35.3924 15.2912 36.2062 15.2178C37.02 15.1444 37.8382 15.2991 38.5691 15.6645C39.2999 16.03 39.9146 16.5917 40.3442 17.2868C40.7738 17.9819 41.0013 18.7829 41.0013 19.6Z" fill="white"/><path d="M26.2013 24C29.515 24 32.2013 21.3137 32.2013 18C32.2013 14.6863 29.515 12 26.2013 12C22.8876 12 20.2013 14.6863 20.2013 18C20.2013 21.3137 22.8876 24 26.2013 24Z" fill="white"/></svg></a>';

                $return_html .= '<a href="https://www.youtube.com/@armember/videos?sub_confirmation=1" target="_blank" class="arm_need_help_icon arm_need_help_btn arm_help_video_icon armhelptip" title="' . esc_attr__('Video Tutorials', 'armember-membership') . '"><svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M40.4606 24.8142C40.3578 24.66 40.4178 24.4887 40.4178 24.3174C40.4349 24.3174 40.452 24.3174 40.4606 24.3174C40.4606 24.4887 40.4606 24.6514 40.4606 24.8142Z" fill="#F5B11D"/><path d="M40.4606 24.3258C40.4434 24.3258 40.4263 24.3258 40.4178 24.3258C40.4178 24.2659 40.3749 24.1973 40.4606 24.1631C40.4606 24.2145 40.4606 24.2659 40.4606 24.3258Z" fill="#F5B11D"/><path d="M40 24.703C40 25.8508 40 26.9985 40 28.1463C39.9229 28.172 39.9486 28.2319 39.9486 28.2833C39.8972 29.688 39.7687 31.0842 39.6231 32.4803C39.4947 33.7737 38.287 35.0071 37.0107 35.1527C34.7409 35.4011 32.4626 35.6152 30.1757 35.718C27.0237 35.8636 23.8716 35.8465 20.7196 35.6666C18.8353 35.5553 16.9595 35.3668 15.0837 35.1613C13.7561 35.0156 12.5398 33.8765 12.3856 32.5488C12.1801 30.8101 12.0602 29.0628 12.0173 27.3154C11.9488 24.6345 12.0859 21.9536 12.3856 19.2898C12.5313 18.005 13.7389 16.7973 15.0066 16.6602C17.285 16.4118 19.5548 16.1977 21.8417 16.0949C24.9766 15.9493 28.1114 15.975 31.2463 16.1463C33.1478 16.2491 35.0407 16.4461 36.9336 16.6516C38.2613 16.7973 39.4861 17.9793 39.6231 19.3069C39.7687 20.7287 39.8972 22.1591 39.9486 23.5895C39.9486 23.6409 39.9229 23.7009 40 23.7266C40 23.8379 40 23.9493 40 24.0521C39.9058 24.0863 39.9572 24.1548 39.9572 24.2148C39.9572 24.3775 39.9058 24.5489 40 24.703ZM30.6639 25.9107C28.3084 24.3433 25.9958 22.8015 23.6661 21.2426C23.6661 24.369 23.6661 27.4525 23.6661 30.5703C26.013 29.0114 28.317 27.4782 30.6639 25.9107Z" fill="white"/></svg></a>';

                $return_html .= '<span class="arm_need_help_icon arm_need_help_btn arm_help_close_icon armhelptip" title="' . esc_attr__('Close', 'armember-membership') . '"><svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M40.4606 24.8142C40.3578 24.66 40.4178 24.4887 40.4178 24.3174C40.4349 24.3174 40.452 24.3174 40.4606 24.3174C40.4606 24.4887 40.4606 24.6514 40.4606 24.8142Z" fill="#F5B11D"/><path d="M40.4606 24.3258C40.4434 24.3258 40.4263 24.3258 40.4178 24.3258C40.4178 24.2659 40.3749 24.1973 40.4606 24.1631C40.4606 24.2145 40.4606 24.2659 40.4606 24.3258Z" fill="#F5B11D"/><path d="M17.8776 34.9777C17.8392 34.923 17.779 34.9284 17.7242 34.9065C16.9902 34.6053 16.7602 33.6687 17.286 33.0662C17.3353 33.0114 17.3901 32.9566 17.4448 32.9018C19.7015 30.6452 21.9582 28.3885 24.2204 26.1319C24.3354 26.0169 24.33 25.9676 24.2204 25.858C21.9418 23.5904 19.6687 21.3119 17.401 19.0388C16.8752 18.513 16.8752 17.7626 17.3955 17.297C17.8337 16.9026 18.4856 16.8972 18.9347 17.2806C19.0059 17.3408 19.0716 17.412 19.1374 17.4778C21.3776 19.718 23.6179 21.9582 25.8527 24.2039C25.9842 24.3353 26.0389 24.3134 26.1594 24.1984C28.4271 21.9253 30.6947 19.6577 32.9624 17.3901C33.4554 16.8972 34.151 16.8753 34.633 17.3189C34.8192 17.4887 34.9014 17.7133 35 17.9379C35 18.1022 35 18.2665 35 18.4308C34.8247 18.9183 34.4249 19.225 34.0798 19.5701C31.9819 21.6734 29.8786 23.7767 27.7698 25.8745C27.6657 25.9785 27.6712 26.0223 27.7753 26.1264C29.9662 28.3064 32.1463 30.4973 34.3318 32.6773C34.6056 32.9511 34.8905 33.214 35 33.6084C35 33.7618 35 33.9151 35 34.063C34.9069 34.2438 34.8466 34.441 34.7042 34.5998C34.5399 34.7806 34.3318 34.8791 34.1181 34.9777C33.9209 34.9777 33.7183 34.9777 33.5211 34.9777C33.0665 34.7915 32.7707 34.4136 32.442 34.0849C30.3387 31.9816 28.2354 29.8838 26.1375 27.7751C26.028 27.6655 25.9787 27.6655 25.8691 27.7751C23.6672 29.9824 21.4598 32.1898 19.2524 34.3971C19.0114 34.6381 18.7759 34.8737 18.4472 34.9777C18.2555 34.9777 18.0638 34.9777 17.8776 34.9777Z" fill="white"/></svg></span>';
            $return_html .= '</div>';
            $return_html .= '<div class="arm_need_help_main_wrapper_inactive armhelptip" title="' . esc_attr__('Need Help?', 'armember-membership') . '">';
                $return_html .= '<a class="arm_need_help_icon arm_need_help_btn"><svg width="52" height="52" viewBox="0 0 70 70" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="35" cy="35" r="35" fill="#F5B11D"/><path d="M21.5694 48.4327C22.0846 48.9479 22.6131 49.4232 23.1726 49.8647C23.2914 49.9584 23.464 49.9468 23.571 49.8398L29.9484 43.4625C30.0822 43.3287 30.0549 43.1126 29.8954 43.0107C29.3236 42.6455 28.7837 42.214 28.286 41.7163C27.8041 41.2345 27.3881 40.7107 27.0278 40.1654C26.9244 40.009 26.7097 39.9847 26.5772 40.1173L20.2078 46.4866C20.0993 46.595 20.0883 46.7707 20.1844 46.8903C20.6145 47.4252 21.0723 47.9356 21.5694 48.4327Z" fill="white"/><path d="M44.2454 51.601C44.4184 51.5045 44.4502 51.2634 44.3101 51.1233L37.4925 44.3058C37.4215 44.2348 37.3196 44.2074 37.222 44.2306C35.7444 44.5819 34.2125 44.5818 32.7422 44.2233C32.6441 44.1994 32.5417 44.2267 32.4703 44.298L25.6612 51.107C25.5213 51.247 25.5528 51.4878 25.7255 51.5845C31.461 54.7971 38.5021 54.8051 44.2454 51.601Z" fill="white"/><path d="M46.8073 49.8893C47.3753 49.4399 47.9121 48.9563 48.4356 48.4328C48.9245 47.9439 49.3741 47.4421 49.796 46.9155C49.8918 46.796 49.8808 46.6205 49.7724 46.5122L43.4053 40.1451C43.2719 40.0117 43.0567 40.0384 42.9541 40.1967C42.6051 40.7355 42.1917 41.2437 41.7191 41.7164C41.2134 42.222 40.6654 42.6612 40.0802 43.0289C39.9192 43.13 39.8905 43.3469 40.025 43.4813L46.4086 49.8648C46.5157 49.9719 46.6884 49.9834 46.8073 49.8893Z" fill="white"/><path d="M18.4499 44.3439C18.5469 44.5159 18.7872 44.5471 18.9269 44.4074L25.7274 37.6069C25.7994 37.535 25.8265 37.4318 25.8017 37.3331C25.4272 35.845 25.4122 34.2812 25.7706 32.7785C25.7939 32.6808 25.7665 32.5792 25.6956 32.5082L18.8777 25.6904C18.7377 25.5504 18.4966 25.582 18.4 25.7549C15.1798 31.5161 15.2038 38.5907 18.4499 44.3439Z" fill="white"/><path d="M32.6259 25.8058C34.1715 25.4088 35.7996 25.4085 37.3381 25.7982C37.4366 25.8232 37.5397 25.7961 37.6116 25.7242L44.4121 18.9238C44.5518 18.7841 44.5207 18.5437 44.3486 18.4466C38.552 15.1763 31.4106 15.1845 25.6297 18.4713C25.4582 18.5688 25.4276 18.8086 25.567 18.948L32.3512 25.7321C32.4233 25.8043 32.527 25.8312 32.6259 25.8058Z" fill="white"/><path d="M43.0135 29.8927C43.1154 30.0523 43.3316 30.0795 43.4653 29.9457L49.8427 23.5684C49.9497 23.4614 49.9613 23.2888 49.8676 23.17C49.0453 22.1277 48.0073 21.0775 46.8932 20.1818C46.7736 20.0857 46.5979 20.0967 46.4894 20.2051L40.12 26.5745C39.9875 26.7071 40.0118 26.9217 40.1682 27.0251C41.3158 27.7833 42.2703 28.7288 43.0135 29.8927Z" fill="white"/><path d="M51.5886 25.7225C51.4919 25.5498 51.251 25.5182 51.111 25.6582L44.302 32.467C44.2306 32.5384 44.2035 32.6408 44.2276 32.7388C44.6011 34.2593 44.5861 35.8554 44.1962 37.376C44.1708 37.4749 44.1977 37.5788 44.27 37.651L51.0538 44.4348C51.1933 44.5743 51.4332 44.5435 51.5306 44.372C54.8012 38.609 54.8255 31.5012 51.5886 25.7225Z" fill="white"/><path d="M23.0868 20.2069C21.9806 21.0934 20.9396 22.1512 20.1131 23.1956C20.019 23.3144 20.0304 23.4872 20.1376 23.5944L26.5212 29.9779C26.6556 30.1123 26.8724 30.0836 26.9735 29.9227C27.6883 28.7851 28.6935 27.7695 29.8057 27.0487C29.964 26.9461 29.9907 26.731 29.8573 26.5976L23.4903 20.2306C23.3819 20.1222 23.2064 20.111 23.0868 20.2069Z" fill="white"/></svg></a>';
            $return_html .= '</div>';

            $return_html .= '<div class="arm_sidebar_drawer_main_wrapper">';
                $return_html .= '<div class="arm_sidebar_drawer_inner_wrapper">';
                    $return_html .= '<div class="arm_sidebar_drawer_content">';
                        $return_html .= '<div class="arm_sidebar_drawer_close_container">';
                            $return_html .= '<div class="arm_sidebar_drawer_close_btn"></div>';
                        $return_html .= '</div>';
                        $return_html .= '<div class="arm_sidebar_drawer_body">';
                            $return_html .= '<div class="arm_sidebar_content_wrapper">';
                                $return_html .= '<div class="arm_sidebar_content_header">';
                                    $return_html .= '<h1 class="arm_sidebar_content_heading"></h1>';                                    
                                $return_html .= '</div>';
                                $return_html .= '<div class="arm_sidebar_content_body">';
                                $return_html .= '</div>';
                                $return_html .= '<div class="arm_sidebar_content_footer"><a href="https://www.armemberplugin.com/documentation/" target="_blank" class="arm_readmore_link">Read More</a></div>';
                            $return_html .= '</div>';
                        $return_html .= '</div>';

                        $return_html .= '<div class="arm_loading"><img src="'.esc_attr(MEMBERSHIPLITE_IMAGES_URL).'/loader.gif" alt="Loading.."></div>';//phpcs:ignore

                    $return_html .= '</div>';
                $return_html .= '</div>';
            $return_html .= '</div>';
        }

        return $return_html;
    }

    function arm_get_need_help_content_func($param) {
        
        $wpnonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : ''; //phpcs:ignore
        $arm_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'arm_wp_nonce' );//phpcs:ignore --Reason:Verifying nonce
        if ( ! $arm_verify_nonce_flag ) {
            $response['status'] = 'error';
            $response['title'] = esc_html__( 'Error', 'armember-membership' );
            $response['msg'] = esc_html__( 'Sorry, Your request can not process due to security reason.', 'armember-membership' );
            wp_send_json( $response );
            die();
        }
        $arm_doc_content = "";
        if ( !empty($_POST['action']) && $_POST['action'] == 'arm_get_need_help_content' && !empty($_POST['page']) ) { //phpcs:ignore
            $help_page = sanitize_text_field( $_POST['page'] ); //phpcs:ignore
            $arm_get_data_url = 'https://www.armemberplugin.com/';
                $arm_get_data_params = array(
                    'method' => 'POST',
                    'body' => array(
                        'action' => 'get_documentation',
                        'page' => $help_page,
                    ),
                    'timeout' => 45,
                );
                $arm_doc_res = wp_remote_post( $arm_get_data_url, $arm_get_data_params );
                if(!is_wp_error($arm_doc_res)){
                    $arm_doc_content = ! empty( $arm_doc_res['body'] ) ? $arm_doc_res['body'] : esc_html__('No data found', 'armember-membership');


                    $arm_json_paresed_data = json_decode($arm_doc_content);
                    $arm_doc_url = !empty($arm_json_paresed_data->data->url) ? $arm_json_paresed_data->data->url : ARMLITE_HOME_URL;
                    $arm_json_paresed_data = !empty($arm_json_paresed_data->data->content) ? urldecode($arm_json_paresed_data->data->content) : esc_html__('No data found', 'armember-membership');

                    //Replace the anchor tag if anchor tag has any image url
                    $arm_json_paresed_data = preg_replace(array('"<a href=(.*(png|jpg|gif|jpeg|webp))(.*?)>"', '"</a>"'), array('',''), $arm_json_paresed_data);

                    //Add target='_blank' to anchor tag.
                    if(preg_match('/<a.*?target=[^>]*?>/', $arm_json_paresed_data)){
                        preg_replace('/<a.*?target="([^"]?)"[^>]*?>/', 'blank', $arm_json_paresed_data);
                    }else{
                        $arm_json_paresed_data = str_replace('<a', '<a target="_blank"', $arm_json_paresed_data);
                    }

                    //Replace the URL if it not strats with 'https' or 'http'.
                    if(extension_loaded('xml')){
                        $arm_xml_obj = new DOMDocument();
                        $arm_xml_obj->loadHTML($arm_json_paresed_data);
                        foreach($arm_xml_obj->getElementsByTagName('a') as $arm_anchor_tag_data){
                            $arm_anchor_href = $arm_anchor_tag_data->getAttribute('href');
                            if( false === strpos($arm_anchor_href, 'https://') && false === strpos($arm_anchor_href, 'http://') ){
                                $arm_anchor_tag_data->setAttribute('href', $arm_doc_url.$arm_anchor_href);
                            }
                        }

                        $arm_json_paresed_data = $arm_xml_obj->saveHTML();
                    }

                    $arm_doc_content = json_decode($arm_doc_content);
                    if(!empty($arm_doc_content) && is_object($arm_doc_content))
                    {
                        $arm_doc_content->data->content = rawurlencode($arm_json_paresed_data);
                        $arm_doc_content = wp_json_encode($arm_doc_content);
                    }
                } else{
                    $arm_doc_content = $arm_doc_res->get_error_message();
                }

            echo $arm_doc_content; //phpcs:ignore
            exit;
        }
    }

	function arm_dont_show_upgrade_notice() {
		global $wp, $wpdb, $ARMemberLite, $pagenow, $arm_capabilities_global;

		$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce

		$is_hide = ( isset( $_POST['is_hide'] ) && $_POST['is_hide'] == '1' ) ? 1 : 0; //phpcs:ignore
		if ( $is_hide == 1 ) {
			delete_option( 'arm_lite_new_version_installed' );
		}
		die();
	}

	/* Cornerstone Methods */

	function arm_front_alert_messages() {
		$alertMessages = array(
			'loadActivityError'        => esc_html__( 'There is an error while loading activities, please try again.', 'armember-membership' ),
			'pinterestPermissionError' => esc_html__( 'The user chose not to grant permissions or closed the pop-up', 'armember-membership' ),
			'pinterestError'           => esc_html__( 'Oops, there was a problem getting your information', 'armember-membership' ),
			'clickToCopyError'         => esc_html__( 'There is a error while copying, please try again', 'armember-membership' ),
			'fbUserLoginError'         => esc_html__( 'User cancelled login or did not fully authorize.', 'armember-membership' ),
			'closeAccountError'        => esc_html__( 'There is a error while closing account, please try again.', 'armember-membership' ),
			'invalidFileTypeError'     => esc_html__( 'Sorry, this file type is not permitted for security reasons.', 'armember-membership' ),
			'fileSizeError'            => esc_html__( 'File is not allowed bigger than {SIZE}.', 'armember-membership' ),
			'fileUploadError'          => esc_html__( 'There is an error in uploading file, Please try again.', 'armember-membership' ),
			'coverRemoveConfirm'       => esc_html__( 'Are you sure you want to remove cover photo?', 'armember-membership' ),
			'profileRemoveConfirm'     => esc_html__( 'Are you sure you want to remove profile photo?', 'armember-membership' ),
			'errorPerformingAction'    => esc_html__( 'There is an error while performing this action, please try again.', 'armember-membership' ),
			'userSubscriptionCancel'   => esc_html__( "User's subscription has been canceled", 'armember-membership' ),
			'cancelSubscriptionAlert'  => esc_html__( 'Are you sure you want to cancel subscription?', 'armember-membership' ),
			'ARM_Loding'               => esc_html__( 'Loading..', 'armember-membership' ),
		);
		return $alertMessages;
	}

	function arm_alert_messages() {
		$alertMessages = array(
			'wentwrong'                   => esc_html__( 'Sorry, Something went wrong. Please try again.', 'armember-membership' ),
			'bulkActionError'             => esc_html__( 'Please select valid action.', 'armember-membership' ),
			'bulkRecordsError'            => esc_html__( 'Please select one or more records.', 'armember-membership' ),
			'clearLoginAttempts'          => esc_html__( 'Login attempts cleared successfully.', 'armember-membership' ),
			'clearLoginHistory'           => esc_html__( 'Login History cleared successfully.', 'armember-membership' ),

			'delPlansSuccess'             => esc_html__( 'Plan(s) has been deleted successfully.', 'armember-membership' ),
			'delPlansError'               => esc_html__( 'There is a error while deleting Plan(s), please try again.', 'armember-membership' ),
			'delPlanSuccess'              => esc_html__( 'Plan has been deleted successfully.', 'armember-membership' ),
			'delPlanError'                => esc_html__( 'There is a error while deleting Plan, please try again.', 'armember-membership' ),

			'delSetupsSuccess'            => esc_html__( 'Setup(s) has been deleted successfully.', 'armember-membership' ),
			'delSetupsError'              => esc_html__( 'There is a error while deleting Setup(s), please try again.', 'armember-membership' ),
			'delSetupSuccess'             => esc_html__( 'Setup has been deleted successfully.', 'armember-membership' ),
			'delSetupError'               => esc_html__( 'There is a error while deleting Setup, please try again.', 'armember-membership' ),
			'delFormSetSuccess'           => esc_html__( 'Form Set Deleted Successfully.', 'armember-membership' ),
			'delFormSetError'             => esc_html__( 'There is a error while deleting form set, please try again.', 'armember-membership' ),
			'delFormSuccess'              => esc_html__( 'Form deleted successfully.', 'armember-membership' ),
			'delFormError'                => esc_html__( 'There is a error while deleting form, please try again.', 'armember-membership' ),
			'delRuleSuccess'              => esc_html__( 'Rule has been deleted successfully.', 'armember-membership' ),
			'delRuleError'                => esc_html__( 'There is a error while deleting Rule, please try again.', 'armember-membership' ),
			'delRulesSuccess'             => esc_html__( 'Rule(s) has been deleted successfully.', 'armember-membership' ),
			'delRulesError'               => esc_html__( 'There is a error while deleting Rule(s), please try again.', 'armember-membership' ),
			'prevTransactionError'        => esc_html__( 'There is a error while generating preview of transaction detail, Please try again.', 'armember-membership' ),
			'invoiceTransactionError'     => esc_html__( 'There is a error while generating invoice of transaction detail, Please try again.', 'armember-membership' ),
			'prevMemberDetailError'       => esc_html__( 'There is a error while generating preview of members detail, Please try again.', 'armember-membership' ),
			'prevMemberActivityError'     => esc_html__( 'There is a error while displaying members activities detail, Please try again.', 'armember-membership' ),
			'prevCustomCssError'          => esc_html__( 'There is a error while displaying ARMember CSS Class Information, Please Try Again.', 'armember-membership' ),
			'prevImportMemberDetailError' => esc_html__( 'Please upload appropriate file to import users.', 'armember-membership' ),
			'delTransactionSuccess'       => esc_html__( 'Transaction has been deleted successfully.', 'armember-membership' ),
			'delTransactionsSuccess'      => esc_html__( 'Transaction(s) has been deleted successfully.', 'armember-membership' ),
			'delAutoMessageSuccess'       => esc_html__( 'Message has been deleted successfully.', 'armember-membership' ),
			'delAutoMessageError'         => esc_html__( 'There is a error while deleting Message, please try again.', 'armember-membership' ),
			'delAutoMessagesSuccess'      => esc_html__( 'Message(s) has been deleted successfully.', 'armember-membership' ),
			'delAutoMessagesError'        => esc_html__( 'There is a error while deleting Message(s), please try again.', 'armember-membership' ),

			'saveSettingsSuccess'         => esc_html__( 'Settings has been saved successfully.', 'armember-membership' ),
			'saveSettingsError'           => esc_html__( 'There is a error while updating settings, please try again.', 'armember-membership' ),
			'saveDefaultRuleSuccess'      => esc_html__( 'Default Rules Saved Successfully.', 'armember-membership' ),
			'saveDefaultRuleError'        => esc_html__( 'There is a error while updating rules, please try again.', 'armember-membership' ),

			'delMemberActivityError'      => esc_html__( 'There is a error while deleting member activities, please try again.', 'armember-membership' ),
			'noTemplateError'             => esc_html__( 'Template not found.', 'armember-membership' ),
			'saveTemplateSuccess'         => esc_html__( 'Template options has been saved successfully.', 'armember-membership' ),
			'saveTemplateError'           => esc_html__( 'There is a error while updating template options, please try again.', 'armember-membership' ),
			'prevTemplateError'           => esc_html__( 'There is a error while generating preview of template, Please try again.', 'armember-membership' ),
			'addTemplateSuccess'          => esc_html__( 'Template has been added successfully.', 'armember-membership' ),
			'addTemplateError'            => esc_html__( 'There is a error while adding template, please try again.', 'armember-membership' ),
			'delTemplateSuccess'          => esc_html__( 'Template has been deleted successfully.', 'armember-membership' ),
			'delTemplateError'            => esc_html__( 'There is a error while deleting template, please try again.', 'armember-membership' ),
			'saveEmailTemplateSuccess'    => esc_html__( 'Email Template Updated Successfully.', 'armember-membership' ),
			'saveAutoMessageSuccess'      => esc_html__( 'Message Updated Successfully.', 'armember-membership' ),

			'addAchievementSuccess'       => esc_html__( 'Achievements Added Successfully.', 'armember-membership' ),
			'saveAchievementSuccess'      => esc_html__( 'Achievements Updated Successfully.', 'armember-membership' ),

			'pastDateError'               => esc_html__( 'Cannot Set Past Dates.', 'armember-membership' ),
			'pastStartDateError'          => esc_html__( 'Start date can not be earlier than current date.', 'armember-membership' ),
			'pastExpireDateError'         => esc_html__( 'Expire date can not be earlier than current date.', 'armember-membership' ),

			'uniqueformsetname'           => esc_html__( 'This Set Name is already exist.', 'armember-membership' ),
			'uniquesignupformname'        => esc_html__( 'This Form Name is already exist.', 'armember-membership' ),
			'installAddonError'           => esc_html__( 'There is an error while installing addon, Please try again.', 'armember-membership' ),
			'installAddonSuccess'         => esc_html__( 'Addon installed successfully.', 'armember-membership' ),
			'activeAddonError'            => esc_html__( 'There is an error while activating addon, Please try agina.', 'armember-membership' ),
			'activeAddonSuccess'          => esc_html__( 'Addon activated successfully.', 'armember-membership' ),
			'deactiveAddonSuccess'        => esc_html__( 'Addon deactivated successfully.', 'armember-membership' ),
			'confirmCancelSubscription'   => esc_html__( 'Are you sure you want to cancel subscription?', 'armember-membership' ),
			'errorPerformingAction'       => esc_html__( 'There is an error while performing this action, please try again.', 'armember-membership' ),
			'userSubscriptionCancel'      => esc_html__( "User's subscription has been canceled", 'armember-membership' ),
			'cancelSubscriptionAlert'     => esc_html__( 'Are you sure you want to cancel subscription?', 'armember-membership' ),
			'ARM_Loding'                  => esc_html__( 'Loading..', 'armember-membership' ),
			'arm_nothing_found'           => esc_html__( 'Oops, nothing found.', 'armember-membership' ),
		);
		$frontMessages = $this->arm_front_alert_messages();
		$alertMessages = array_merge( $alertMessages, $frontMessages );
		return $alertMessages;
	}

	function arm_prevent_rocket_loader_script( $tag, $handle ) {
		$script   = htmlspecialchars( $tag );
		$pattern2 = '/\/(wp\-content\/plugins\/armember-membership)/';
		preg_match( $pattern2, $script, $match_script );

		/* Check if current script is loaded from ARMember only */
		if ( ! isset( $match_script[0] ) || $match_script[0] == '' ) {
			return $tag;
		}

		$pattern = '/(.*?)(data\-cfasync\=)(.*?)/';
		preg_match_all( $pattern, $tag, $matches );
		if ( ! is_array( $matches ) ) {
			return str_replace( ' src', ' data-cfasync="false" src', $tag );
		} elseif ( ! empty( $matches ) && ! empty( $matches[2] ) && ! empty( $matches[2][0] ) && strtolower( trim( $matches[2][0] ) ) != 'data-cfasync=' ) {
			return str_replace( ' src', ' data-cfasync="false" src', $tag );
		} elseif ( ! empty( $matches ) && empty( $matches[2] ) ) {
			return str_replace( ' src', ' data-cfasync="false" src', $tag );
		} else {
			return $tag;
		}
	}

	function arm_set_js_css_conditionally() {
		global $arm_lite_datepicker_loaded, $arm_lite_avatar_loaded, $arm_lite_file_upload_field, $arm_lite_bpopup_loaded, $arm_lite_load_tipso, $arm_lite_load_icheck, $arm_lite_font_awesome_loaded;
		if ( ! is_admin() ) {
			if ( $arm_lite_datepicker_loaded == 1 ) {
				if ( ! wp_script_is( 'arm_bootstrap_datepicker_with_locale_js', 'enqueued' ) ) {
					wp_enqueue_script( 'arm_bootstrap_datepicker_with_locale_js' );
				}
			}
			if ( $arm_lite_avatar_loaded == 1 || $arm_lite_file_upload_field == 1 ) {
				if ( ! wp_script_is( 'arm_file_upload_js', 'enqueued' ) ) {
					wp_enqueue_script( 'arm_file_upload_js' );
				}
			}
			if ( $arm_lite_bpopup_loaded == 1 ) {
				if ( ! wp_script_is( 'arm_bpopup', 'enqueued' ) ) {
					wp_enqueue_script( 'arm_bpopup' );
				}
			}
			if ( $arm_lite_load_tipso == 1 ) {
				if ( ! wp_script_is( 'arm_tipso_front', 'enqueued' ) ) {
					wp_enqueue_script( 'arm_tipso_front' );
				}
			}
			if ( $arm_lite_font_awesome_loaded == 1 ) {
				wp_enqueue_style( 'arm_fontawesome_css' );
			}
		}
	}

	function arm_check_font_awesome_icons( $content ) {
		global $arm_lite_font_awesome_loaded;

		$fa_class = '/armfa|arm_user_social_icons|arm_user_social_fields/';
		$matches  = array();
		preg_match_all( $fa_class, $content, $matches );

		if ( count( $matches ) > 0 && count( $matches[0] ) > 0 ) {
			$arm_lite_font_awesome_loaded = 1;
		}

		return $content;
	}

	function arm_check_user_cap( $arm_capabilities = '', $is_ajax_call = '' ) {
		global $arm_global_settings;

		$errors  = array();
		$message = '';
		if (!empty($arm_capabilities) &&  !current_user_can( $arm_capabilities ) ) {
			$errors[]                = esc_html__( 'Sorry, You do not have permission to perform this action.', 'armember-membership' );
			$return_array            = $arm_global_settings->handle_return_messages( $errors, $message );
			$return_array['message'] = $return_array['msg'];

			echo wp_json_encode( $return_array );
			exit;
		}

		$wpnonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field($_REQUEST['_wpnonce']) : ''; //phpcs:ignore
        if(empty($wpnonce))
        {
            $wpnonce = isset($_REQUEST['arm_wp_nonce']) ? sanitize_text_field($_REQUEST['arm_wp_nonce']) : ''; //phpcs:ignore
        }
		$arm_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'arm_wp_nonce' );
		if(empty( $wpnonce) )
		{
			$errors[]                = esc_html__( 'Sorry, Your request can not process due to nonce not found.', 'armember-membership' );
			$return_array            = $arm_global_settings->handle_return_messages( $errors, $message );
			$return_array['message'] = $return_array['msg'];
			echo wp_json_encode( $return_array );
			exit;
		}
		else if( !$arm_verify_nonce_flag ) {
			$errors[]                = esc_html__( 'Sorry, Your request can not process due to security reason.', 'armember-membership' );
			$return_array            = $arm_global_settings->handle_return_messages( $errors, $message );
			$return_array['message'] = $return_array['msg'];
			echo wp_json_encode( $return_array );
			exit;
		}
	}

	function arm_session_start( $force = false ) {
		/**
		 * Start Session
		 */
		$arm_session_id = session_id();
		if ( empty( $arm_session_id ) || $force == true ) {
			@session_start();
		}
	}

	function armember_allowed_html_tags(){

        $arm_allowed_html = array(
            'a' => array_merge(
                $this->armember_global_attributes(),
                array(
                    'href' => array(),
                    'rel' => array(),
                    'target' => array(),
                )
            ),
            'b' => $this->armember_global_attributes(),
            'br' => $this->armember_global_attributes(),
            'button' => array_merge(
                $this->armember_global_attributes(),
                array(
                    'name' => array(),
                    'type' => array(),
                    'value' => array()
                )
            ),
            'code' => $this->armember_global_attributes(),
            'div' => $this->armember_global_attributes(),
            /* 'embed' => array_merge(
                $this->armember_global_attributes(),
                array(
                    'height' => array(),
                    'src' => array(),
                    'type' => array(),
                    'width' => array(),
                )
            ), */
            'font' => array_merge(
                $this->armember_global_attributes(),
                array(
                    'color' => array(),
                    'face' => array(),
                    'size' => array()
                )
            ),
            'h1' => $this->armember_global_attributes(),
            'h2' => $this->armember_global_attributes(),
            'h3' => $this->armember_global_attributes(),
            'h4' => $this->armember_global_attributes(),
            'h5' => $this->armember_global_attributes(),
            'h6' => $this->armember_global_attributes(),
            'hr' => $this->armember_global_attributes(),
            'i' => $this->armember_global_attributes(),
            'img' => array_merge(
                $this->armember_global_attributes(),
                array(
                    'alt' => array(),
                    'height' => array(),
                    'src' => array(),
                    'width' => array()
                )
            ),
            'input' => array_merge(
                $this->armember_global_attributes(),
                $this->armember_visible_tag_attributes(),
                array(
                    'accept' => array(),
                    'alt' => array(),
                    'autocomplete' => array(),
                    //'autofocus' => array(),
                    'checked' => array(),
                    //'dirname' => array(),
                    'disabled' => array(),
                    //'height' => array(),
                    //'list' => array(),
                    'max' => array(),
                    'maxlength' => array(),
                    'min' => array(),
                    //'multiple' => array(),
                    'name' => array(),
                    'onsearch' => array(),
                    //'pattern' => array(),
                    'placeholder' => array(),
                    'readonly' => array(),
                    'required' => array(),
                    'size' => array(),
                    'src' => array(),
                    'step' => array(),
                    'type' => array(),
                    'value' => array(),
                    'width' => array()
                )
            ),
            'ins' => $this->armember_global_attributes(),
            'label' => array_merge(
                $this->armember_global_attributes(),
                array(
                    'for' => array(),
                )
            ),
            'li' => $this->armember_global_attributes(),
            'ol' => $this->armember_global_attributes(),
            'optgroup' => $this->armember_global_attributes(),
            'p' => $this->armember_global_attributes(),
            'section' => $this->armember_global_attributes(),
            'span' => $this->armember_global_attributes(),
            'strong' => $this->armember_global_attributes(),
            'sub' => $this->armember_global_attributes(),
            'sup' => $this->armember_global_attributes(),
            'table' => $this->armember_global_attributes(),
            'tbody' => $this->armember_global_attributes(),
            'thead' => $this->armember_global_attributes(),
            'tfooter' => $this->armember_global_attributes(),
            'th' => array_merge(
                $this->armember_global_attributes(),
                array(
                    'colspan' => array(),
                    'headers' => array(),
                    'rowspan' => array(),
                    'scope' => array()
                )
            ),
            'td' => array_merge(
                $this->armember_global_attributes(),
                array(
                    'colspan' => array(),
                    'headers' => array(),
                    'rowspan' => array()
                )
            ),
            'tr' => $this->armember_global_attributes(),
            'textarea' => array_merge(
                $this->armember_global_attributes(),
                $this->armember_visible_tag_attributes(),
                array(
                    'cols' => array(),
                    'maxlength' => array(),
                    'name' => array(),
                    'placeholder' => array(),
                    'readonly' => array(),
                    'required' => array(),
                    'rows' => array(),
                )
            ),
            'u' => $this->armember_global_attributes(),
            'ul' => $this->armember_global_attributes(),
        );

        return $arm_allowed_html;
    }

	function arm_recursive_sanitize_data( $posted_data ) {
		global $ARMemberLite;

		if( empty( $posted_data ) ) {
            return $posted_data;
        }

		if ( is_array( $posted_data ) ) {
			return array_map( array( $ARMemberLite, __FUNCTION__ ), json_decode( wp_json_encode( $posted_data ), true ) );
		} elseif ( is_object( $posted_data ) ) {
			return array_map( array( $ARMemberLite, __FUNCTION__ ), json_decode( wp_json_encode( $posted_data ), true ) );
		}
		
		/*
		if ( preg_match( '/^(\d+)$/', $posted_data ) ) {
			return intval( $posted_data );
		} elseif ( preg_match( '/^(\d+(|\.\d+))$/', $posted_data ) ) {
			return floatval( $posted_data );
		} else
		*/
		if ( preg_match( '/<[^<]+>/', $posted_data ) ) {
			$armlite_allowed_html = $ARMemberLite->armember_allowed_html_tags();
			return wp_kses( $posted_data, $armlite_allowed_html );
		} elseif ( filter_var( $posted_data, FILTER_VALIDATE_URL ) ) {
			return esc_url_raw( $posted_data );
		} else {
			return sanitize_text_field( $posted_data );
		}
		return $posted_data;
	}

	function arm_recursive_sanitize_data_extend( $posted_data ) {
		global $ARMemberLite;

		if( empty( $posted_data ) ) {
            return $posted_data;
        }

		if ( is_array( $posted_data ) ) {
			return array_map( array( $ARMemberLite, __FUNCTION__ ), json_decode( wp_json_encode( $posted_data ), true ) );
		} elseif ( is_object( $posted_data ) ) {
			return array_map( array( $ARMemberLite, __FUNCTION__ ), json_decode( wp_json_encode( $posted_data ), true ) );
		}
		
		/*
		if ( preg_match( '/^(\d+)$/', $posted_data ) ) {
			return intval( $posted_data );
		} elseif ( preg_match( '/^(\d+(|\.\d+))$/', $posted_data ) ) {
			return floatval( $posted_data );
		} else*/ 
		if ( preg_match( '/<[^<]+>/', $posted_data ) ) {
			$armlite_allowed_html = $ARMemberLite->armember_allowed_html_tags();
			return wp_kses( $posted_data, $armlite_allowed_html );
		} elseif ( filter_var( $posted_data, FILTER_VALIDATE_URL ) ) {
			return esc_url_raw( $posted_data );
		} else {
			return sanitize_textarea_field( $posted_data );
		}
		return $posted_data;
	}

	function arm_recursive_sanitize_data_extend_only_kses( $posted_data ) {
		global $ARMemberLite;

		if( empty( $posted_data ) ) {
            return $posted_data;
        }
		
		if ( is_array( $posted_data ) ) {
			return array_map( array( $ARMemberLite, __FUNCTION__ ), json_decode( wp_json_encode( $posted_data ), true ) );
		} elseif ( is_object( $posted_data ) ) {
			return array_map( array( $ARMemberLite, __FUNCTION__ ), json_decode( wp_json_encode( $posted_data ), true ) );
		}
		
		$armlite_allowed_html = $ARMemberLite->armember_allowed_html_tags();
		return wp_kses( $posted_data, $armlite_allowed_html );
	
	}

    function armember_global_attributes(){
        return array(
            'class' => array(),
            'id' => array(),
            'title' => array(),
            'tabindex' => array(),
            'lang' => array(),
            'style' => array(),
        );
    }

    function armember_visible_tag_attributes(){
        return array(
            /* 'onblur' => array(),
            'onchange' => array(),
            'onclick' => array(),
            'oncontextmenu' => array(),
            'oncopy' => array(),
            'oncut' => array(),
            'ondblclick' => array(),
            'ondrag' => array(),
            'ondragend' => array(),
            'ondragenter' => array(),
            'ondragleave' => array(),
            'ondragover' => array(),
            'ondragstart' => array(),
            'ondrop' => array(),
            'onfocus' => array(),
            'oninput' => array(),
            'oninvalid' => array(),
            'onkeydown' => array(),
            'onkeypress' => array(),
            'onkeyup' => array(),
            'onmousedown' => array(),
            'onmousemove' => array(),
            'onmouseout' => array(),
            'onmouseover' => array(),
            'onmouseup' => array(),
            'onmousewheel' => array(),
            'onpaste' => array(),
            'onscroll' => array(),
            'onselect' => array(),
            'onwheel' => array() */
        );
    }
	
	function arm_get_basename($filename){
        return preg_replace('/^.+[\\\\\\/]/', '', $filename);
    }

	function armgetapiurl() {
		$api_url = 'https://arpluginshop.com/';
		return $api_url;
	}

	function arm_get_remote_post_params( $plugin_info = '' ) {
		global $wpdb;

		$action = '';
		$action = $plugin_info;

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_list = get_plugins();
		$site_url    = ARMLITE_HOME_URL;
		$plugins     = array();

		$active_plugins = get_option( 'active_plugins' );

		foreach ( $plugin_list as $key => $plugin ) {
			$is_active = in_array( $key, $active_plugins );

			// filter for only armember ones, may get some others if using our naming convention
			if ( strpos( strtolower( $plugin['Title'] ), 'armember-membership' ) !== false ) {
				$name      = substr( $key, 0, strpos( $key, '/' ) );
				$plugins[] = array(
					'name'      => $name,
					'version'   => $plugin['Version'],
					'is_active' => $is_active,
				);
			}
		}
		$plugins = wp_json_encode( $plugins );

		// get theme info
		$theme            = wp_get_theme();
		$theme_name       = $theme->get( 'Name' );
		$theme_uri        = $theme->get( 'ThemeURI' );
		$theme_version    = $theme->get( 'Version' );
		$theme_author     = $theme->get( 'Author' );
		$theme_author_uri = $theme->get( 'AuthorURI' );

		$im        = is_multisite();
		$sortorder = get_option( 'armSortOrder' );

		$post = array(
			'wp'        => get_bloginfo( 'version' ),
			'php'       => phpversion(),
			'mysql'     => $wpdb->db_version(),
			'plugins'   => $plugins,
			'tn'        => $theme_name,
			'tu'        => $theme_uri,
			'tv'        => $theme_version,
			'ta'        => $theme_author,
			'tau'       => $theme_author_uri,
			'im'        => $im,
			'sortorder' => $sortorder,
		);

		return $post;
	}
}

global $ARMemberLite;

$arm_lite_api_url = $ARMemberLite->armgetapiurl();
$arm_lite_plugin_slug = basename(dirname(__FILE__));

add_filter('pre_set_site_transient_update_plugins', 'arm_check_for_lite_plugin_update');

function arm_check_for_lite_plugin_update($checked_data) {

	$arm_lite_force_update_check = get_transient('arm_lite_force_update_check');
	if( !empty( $arm_lite_force_update_check ) )
	{
		global $arm_lite_api_url, $arm_lite_plugin_slug, $wp_version, $ARMemberLite, $arm_lite_version;

		//Comment out these two lines during testing.
		if (empty($checked_data->checked))
			return $checked_data;

		$args = array(
			'slug' => $arm_lite_plugin_slug,
			'version' => $arm_lite_version,
			'other_variables' => $ARMemberLite->arm_get_remote_post_params(),
		);

		$request_string = array(
			'body' => array(
				'action' => 'basic_check',
				'request' => serialize($args),
				'api-key' => md5(ARMLITE_HOME_URL)
			),
			'sslverify' => false,
			'user-agent' => 'ARMLITE-WordPress/' . $wp_version . '; ' . ARMLITE_HOME_URL
		);

		// Start checking for an update
		$raw_response = wp_remote_post($arm_lite_api_url, $request_string);

		if( !is_wp_error( $raw_response ) && ( $raw_response['response']['code'] == 200 ) )
			$response = @unserialize( $raw_response['body'] );
			
		if(isset($response['access_request']) && !empty($response['access_request']) && 'success' == $response['access_request'] )
		{
			if(isset($response['access_package']) && !empty($response['access_package']))
			{
				$update_package = @unserialize($response['access_package']);
				if (isset($update_package) && is_object($update_package) && is_object($checked_data) && !empty($update_package))
				{
					$checked_data->response[$arm_lite_plugin_slug . '/' . $arm_lite_plugin_slug . '.php'] = $update_package;
				}
			}
		}
	}
	return $checked_data;
}

add_filter('plugins_api', 'arm_lite_plugin_api_call', 11, 3);

function arm_lite_plugin_api_call( $res, $action, $args ) {

	$arm_lite_force_update_check = get_transient('arm_lite_force_update_check');
	if( !empty( $arm_lite_force_update_check ) )
	{
		global $arm_lite_plugin_slug, $arm_lite_api_url, $wp_version, $arm_lite_version;

		if (!isset($args->slug) || ($args->slug != $arm_lite_plugin_slug))
			return $res;
		
		$args->version = $arm_lite_version;

		$request_string = array(
			'body' => array(
				'action' => $action,
				'request' => serialize($args),
				'api-key' => md5(ARMLITE_HOME_URL)
			),
			'sslverify' => false,
			'user-agent' => 'ARMLITE-WordPress/' . $wp_version . '; ' . ARMLITE_HOME_URL
		);

		$request = wp_remote_post($arm_lite_api_url, $request_string);

		if (is_wp_error($request)) {
			$res = new WP_Error('plugins_api_failed', '<p>'.esc_html__('An Unexpected HTTP Error occurred during the API request.', 'armember-membership') . "</p> <p><a href='?' onclick='document.location.reload(); return false;>".esc_html__('Try again','armember-membership')."</a></p>", $request->get_error_message());
		} else {
			$res = unserialize($request['body']);

			if ($res === false)
				$res = new WP_Error('plugins_api_failed', esc_html__('An unknown error occurred', 'armember-membership'), $request['body']);
		}
	}

	return $res;
}

add_action('init', 'arm_lite_validate_plugin_update');

function arm_lite_validate_plugin_update()
{
	$arm_lite_force_update_check = get_transient('arm_lite_force_update_check');
	if( empty( $arm_lite_force_update_check ) )
	{
		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}
		$validate_active_plugin  = plugins_api(
			'plugin_information',
			array(
				'slug'   => 'armember-membership',
				'fields' => array(
					'sections' => false,
					'versions' => true,
				),
			)
		);
		if ( is_wp_error( $validate_active_plugin ) ) {
			$expiration_sec = 60 * 60 * 24; // 24 hours
			set_transient('arm_lite_force_update_check', 1, $expiration_sec);
		}
	}
}
