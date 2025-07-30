<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function arm_upgrade_to_premium_menu() {
	global $ARMemberLite, $arm_slugs, $arm_lite_bf_sale_start_time, $arm_lite_bf_sale_end_time; //phpcs:ignore

	$arm_current_date = current_time('timestamp', true );
	if( $arm_current_date >= $arm_lite_bf_sale_start_time && $arm_current_date <= $arm_lite_bf_sale_end_time ){
		$page_hook = add_submenu_page( $arm_slugs->main, '𖤓 Spring Sale 𖤓', '𖤓 Spring Sale 𖤓', 'arm_manage_members', 'arm_black_friday_sale', 'arm_black_friday_deal' ); //Black Friday Sale
	} else {
		$page_hook = add_submenu_page( $arm_slugs->main, esc_html__( 'Upgrade to Premium', 'armember-membership' ), esc_html__( 'Upgrade to Premium', 'armember-membership' ), 'arm_manage_members', 'arm_upgrade_to_premium', 'arm_upgrade_to_premium_url' );
	}
	add_action( 'load-' . $page_hook, 'arm_upgrade_ob_start' );
}
add_action( 'admin_menu', 'arm_upgrade_to_premium_menu', '51' );

function arm_upgrade_ob_start() {
	ob_start();
}

function arm_black_friday_deal(){
	wp_redirect(admin_url( 'admin.php?page=arm_manage_members&arm_upgrade_action=arm_upgrade_to_premium' ));
}

function arm_upgrade_to_premium_url() {
	global $arm_lite_version;
	wp_redirect('https://www.armemberplugin.com/pricing/?utm_source=lite_version&utm_medium=wordpress_org&utm_campaign=upgrade_to_pro');
	exit();
}

function arm_upgrade_to_premium_menu_js() {
	 global $arm_lite_version;

	wp_register_script( 'armlite_upgrade_js', MEMBERSHIPLITE_URL . '/js/armlite_upgrade_premium.js', array( 'jquery' ), $arm_lite_version ); //phpcs:ignore

	wp_enqueue_script( 'armlite_upgrade_js' );
}
add_action( 'admin_footer', 'arm_upgrade_to_premium_menu_js' );

