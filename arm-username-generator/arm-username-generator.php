<?php
/**
 * Plugin Name: ARM Username Generator
 * Plugin URI:  https://www.armemberplugin.com/
 * Description: Auto-generates sequential, plan-based usernames (LM####, AM####, FNM####) for ARMember Lite registrations.
 * Version:     1.0.0
 * Requires at least: 5.0
 * Requires PHP: 5.6
 * Author:      Repute Infosystems
 * License:     GPL-2.0-or-later
 * Text Domain: arm-username-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ARM_UGEN_VERSION', '1.0.0' );
define( 'ARM_UGEN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ARM_UGEN_URL', plugin_dir_url( __FILE__ ) );

add_action( 'plugins_loaded', 'arm_ugen_init', 25 );

function arm_ugen_init() {
	if ( ! defined( 'MEMBERSHIPLITE_DIR' ) ) {
		add_action( 'admin_notices', 'arm_ugen_missing_notice' );
		return;
	}
	require_once ARM_UGEN_DIR . 'includes/class-arm-username-generator.php';
	new ARM_Username_Generator();
}

function arm_ugen_missing_notice() {
	echo '<div class="notice notice-error"><p>'
		. esc_html__( 'ARM Username Generator requires ARMember Lite to be installed and active.', 'arm-username-generator' )
		. '</p></div>';
}
