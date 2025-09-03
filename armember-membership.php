<?php 
/*
  Plugin Name: ARMember Lite - Membership Plugin
  Description: The most powerful membership plugin to handle any complex membership WordPress sites with super ease.
  Version: 4.0.67
  Requires at least: 5.0
  Requires PHP: 5.6
  Plugin URI: https://www.armemberplugin.com
  Author: Membership & User Profile - Repute Infosystems
  Text Domain: armember-membership
  Domain Path: /languages
  Author URI: https://www.armemberplugin.com
  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'MEMBERSHIPLITE_DIR_NAME' ) ) {
	define( 'MEMBERSHIPLITE_DIR_NAME', 'armember-membership' );
	define( 'MEMBERSHIPLITE_DIR', WP_PLUGIN_DIR . '/' . MEMBERSHIPLITE_DIR_NAME );
}

require_once MEMBERSHIPLITE_DIR . '/autoload.php';