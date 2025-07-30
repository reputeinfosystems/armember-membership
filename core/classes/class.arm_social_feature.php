<?php

if ( ! class_exists( 'ARM_social_feature_Lite' ) ) {

	class ARM_social_feature_Lite {

		var $social_settings;
		var $isSocialFeature;


		function __construct() {
			global $wpdb, $ARMemberLite, $arm_slugs;
			$is_social_feature     = get_option( 'arm_is_social_feature', 0 );
			$this->isSocialFeature = ( $is_social_feature == '1' ) ? true : false;

			//add_action( 'wp_ajax_arm_update_social_settings', array( $this, 'arm_update_social_settings_func' ) );
			add_action( 'wp_ajax_arm_update_social_network_from_form', array( $this, 'arm_update_social_network_from_form_func' ) );

			add_filter( 'plugins_api_args', array( $this, 'arm_plugin_api_args' ), 100000, 2 );
			add_filter( 'plugins_api', array( $this, 'arm_plugin_api' ), 100000, 3 );
			add_filter( 'plugins_api_result', array( $this, 'arm_plugins_api_result' ), 100000, 3 );
			add_filter( 'upgrader_package_options', array( $this, 'arm_upgrader_package_options' ), 100000 );
		}

		function arm_upgrader_package_options( $options ) {
			$options['is_multi'] = false;
			return $options;
		}

		function arm_plugin_api_args( $args, $action ) {
			return $args;
		}

		function arm_plugin_api( $res, $action, $args ) {
			global $ARMemberLite;
			$ARMemberLite->arm_session_start();
			if ( isset( $_SESSION['arm_member_addon'] ) && ! empty( $_SESSION['arm_member_addon'] ) ) {
				
				$armember_addons = $_SESSION['arm_member_addon'] ; //phpcs:ignore

				$obj             = array();
				foreach ( $armember_addons as $slug => $armember_addon ) {
					if ( isset( $slug ) && isset( $args->slug ) ) {
						if ( $slug != $args->slug ) {
							continue;
						} else {
							$obj['name']          = $armember_addon['full_name'];
							$obj['slug']          = $slug;
							$obj['version']       = $armember_addon['plugin_version'];
							$obj['download_link'] = $armember_addon['install_url'];
							return (object) $obj;
						}
					} else {
						continue;
					}
				}
			}
			return $res;
		}

		function arm_plugins_api_result( $res, $action, $args ) {
			global $ARMemberLite;
			return $res;
		}

		function arm_get_social_settings() {
			global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms;
			$social_settings = get_option( 'arm_social_settings' );
			$social_settings = maybe_unserialize( $social_settings );
			if ( ! empty( $social_settings['options'] ) ) {
				$options                      = $social_settings['options'];
				$options['facebook']['label'] = esc_html__( 'Facebook', 'armember-membership' );
				$options['twitter']['label']  = esc_html__( 'Twitter', 'armember-membership' );
				$options['linkedin']['label'] = esc_html__( 'LinkedIn', 'armember-membership' );
				$options['vk']['label']       = esc_html__( 'VK', 'armember-membership' );
				$social_settings['options']   = $options;
			}
			$social_settings = apply_filters( 'arm_get_social_settings', $social_settings );
			return $social_settings;
		}

		function arm_get_active_social_options() {
			global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms;
			$social_options = isset( $this->social_settings['options'] ) ? $this->social_settings['options'] : array();
			$active_opts    = array();
			if ( ! empty( $social_options ) ) {
				foreach ( $social_options as $key => $opt ) {
					if ( isset( $opt['status'] ) && $opt['status'] == '1' ) {
						$active_opts[ $key ] = $opt;
					}
				}
			}
			$active_opts = apply_filters( 'arm_get_active_social_options', $active_opts );
			return $active_opts;
		}

		/* function arm_update_social_settings_func() {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings;
			$post_data = $_POST; //phpcs:ignore
			if ( isset( $post_data['s_action'] ) && $post_data['s_action'] == 'arm_update_social_settings' ) {
				$social_settings            = $post_data['arm_social_settings'];
				$social_settings            = arm_array_map( $social_settings );
				$new_social_settings_result = $social_settings;
				update_option( 'arm_social_settings', $new_social_settings_result );
				$response = array(
					'type' => 'success',
					'msg'  => esc_html__( 'Social Setting(s) has been Saved Successfully.', 'armember-membership' ),
				);
			} else {
				$response = array(
					'type' => 'error',
					'msg'  => esc_html__( 'There is a error while updating settings, please try again.', 'armember-membership' ),
				);
			}
			echo wp_json_encode( $response );
			die();
		} */

		function arm_update_social_network_from_form_func() {
			$response = array(
				'type'         => 'error',
				'msg'          => esc_html__( 'There is a error while updating settings, please try again.', 'armember-membership' ),
				'old_settings' => '',
			);
			global $wp, $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings;
			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_update_social_network_from_form' ) { //phpcs:ignore
				$socialOptions = isset( $_POST['arm_social_settings']['options'] ) ? array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST['arm_social_settings']['options'] ) : array(); //phpcs:ignore
				if ( ! empty( $socialOptions ) ) {
					foreach ( $socialOptions as $snk => $snv ) {
						if ( ! empty( $snv ) ) {
							$icons = get_option( 'arm_social_icons_' . $snk, array() );
							$icons = maybe_unserialize( $icons );
							if ( ! empty( $snv['custom_icon'] ) ) {
								foreach ( $snv['custom_icon'] as $custom_icon ) {
									$baseName = basename( $custom_icon );
									if ( isset( $snv['icon'] ) && $snv['icon'] == 'custom' ) {
										$snv['icon'] = $baseName;
									}
									$icons[ $baseName ] = $custom_icon;
									update_option( 'arm_social_icons_' . $snk, $icons );
								}
							}
						}
					}
				}
				$response = array(
					'type'         => 'success',
					'msg'          => esc_html__( 'Social Setting(s) has been Saved Successfully.', 'armember-membership' ),
					'old_settings' => maybe_serialize( $socialOptions ),
				);
			}
			echo wp_json_encode( $response );
			die();
		}





		function arm_get_user_id_by_meta( $meta_key = '', $meta_value = '' ) {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_member_forms;
			$user_id = 0;
			if ( ! empty( $meta_key ) && ! empty( $meta_value ) ) {
				$user_id = $wpdb->get_var( $wpdb->prepare("SELECT `user_id` FROM `$wpdb->usermeta` WHERE `meta_key`=%s AND `meta_value`=%s",$meta_key,$meta_value) );//phpcs:ignore --Reason $wpdb->usermeta is a table name
			}
			return $user_id;
		}



		function get_rand_alphanumeric( $length ) {
			if ( $length > 0 ) {
				$rand_id = '';
				for ( $i = 1; $i <= $length; $i++ ) {
					wp_rand( (float) microtime() * 1000000 );
					$num      = wp_rand( 1, 36 );
					$rand_id .= $this->assign_rand_value( $num );
				}
			}
			return $rand_id;
		}

		function assign_rand_value( $num ) {
			switch ( $num ) {
				case '1':
					$rand_value = 'a';
					break;
				case '2':
					$rand_value = 'b';
					break;
				case '3':
					$rand_value = 'c';
					break;
				case '4':
					$rand_value = 'd';
					break;
				case '5':
					$rand_value = 'e';
					break;
				case '6':
					$rand_value = 'f';
					break;
				case '7':
					$rand_value = 'g';
					break;
				case '8':
					$rand_value = 'h';
					break;
				case '9':
					$rand_value = 'i';
					break;
				case '10':
					$rand_value = 'j';
					break;
				case '11':
					$rand_value = 'k';
					break;
				case '12':
					$rand_value = 'l';
					break;
				case '13':
					$rand_value = 'm';
					break;
				case '14':
					$rand_value = 'n';
					break;
				case '15':
					$rand_value = 'o';
					break;
				case '16':
					$rand_value = 'p';
					break;
				case '17':
					$rand_value = 'q';
					break;
				case '18':
					$rand_value = 'r';
					break;
				case '19':
					$rand_value = 's';
					break;
				case '20':
					$rand_value = 't';
					break;
				case '21':
					$rand_value = 'u';
					break;
				case '22':
					$rand_value = 'v';
					break;
				case '23':
					$rand_value = 'w';
					break;
				case '24':
					$rand_value = 'x';
					break;
				case '25':
					$rand_value = 'y';
					break;
				case '26':
					$rand_value = 'z';
					break;
				case '27':
					$rand_value = '0';
					break;
				case '28':
					$rand_value = '1';
					break;
				case '29':
					$rand_value = '2';
					break;
				case '30':
					$rand_value = '3';
					break;
				case '31':
					$rand_value = '4';
					break;
				case '32':
					$rand_value = '5';
					break;
				case '33':
					$rand_value = '6';
					break;
				case '34':
					$rand_value = '7';
					break;
				case '35':
					$rand_value = '8';
					break;
				case '36':
					$rand_value = '9';
					break;
			}
			return $rand_value;
		}

		function CheckpluginStatus( $mypluginsarray, $pluginname, $attr, $purchase_addon, $plugin_type, $install_url, $compatible_version, $armember_version ) {
			foreach ( $mypluginsarray as $pluginarr ) {
				$response = '';
				if ( $pluginname == $pluginarr[ $attr ] ) {
					if ( $pluginarr['is_active'] == 1 ) {
						$response  = 'ACTIVE';
						$actionurl = $pluginarr['deactivation_url'];
						break;
					} else {
						$response  = 'NOT ACTIVE';
						$actionurl = $pluginarr['activation_url'];
						break;
					}
				} else {
					if ( $plugin_type == 'free' ) {
						$response  = 'NOT INSTALLED FREE';
						$actionurl = $install_url;
					} elseif ( $plugin_type == 'paid' ) {
						$response  = 'NOT INSTALLED PAID';
						$actionurl = $install_url;
					}
				}
			}
			$myicon          = '';
			$divclassname    = '';
			$arm_plugin_name = explode( '/', $pluginname );
			if ( $response == 'NOT INSTALLED FREE' ) {
				$myicon = '<div class="arm_feature_button_activate_container"><a id="arm_free_addon" href="javascript:void(0);"  class="arm_feature_activate_btn" data-name=' . esc_attr($purchase_addon) . ' data-plugin=' . esc_attr($arm_plugin_name[0]) . '  data-href="javascript:void(0);" data-version="' . esc_attr($compatible_version) . '" data-arm_version="' . esc_attr($armember_version) . '" data-type ="free_addon">Install</a></div>';
			} elseif ( $response == 'NOT INSTALLED PAID' ) {
				$myicon = '<div class="arm_feature_button_activate_container"><a class="arm_feature_activate_btn" href=javascript:void(0); data-version="' . esc_attr($compatible_version) . '" data-arm_version="' . esc_attr($armember_version) . '" data-type ="paid_addon" data-href="' . esc_attr($actionurl) . '"><img src="https://www.arformsplugin.com/arf/addons/images/buynow-icon.png"/> Get It</a></div>'; //phpcs:ignore
			} elseif ( $response == 'ACTIVE' ) {
				$myicon = '<div class="arm_feature_button_deactivate_container"><a id="arm_feature_deactivate_btn" class="arm_feature_activate_btn arm_deactive_addon" data-file="' . esc_attr($pluginname) . '" href="javascript:void(0);"  data-version="' . esc_attr($compatible_version) . '" data-arm_version="' . esc_attr($armember_version) . '" data-type ="deactivate_addon">Deactivate</a></div>';
			} elseif ( $response == 'NOT ACTIVE' ) {
				$myicon = '<div class="arm_feature_button_activate_container"><a class="arm_feature_activate_btn arm_active_addon" data-file="' . esc_attr($pluginname) . '" href="javascript:void(0);"  data-version="' . esc_attr($compatible_version) . '" data-arm_version="' . esc_attr($armember_version) . '" data-type ="activate_addon">Activate</a></div>';
			}
			return $myicon;
		}

		function addons_page( $force_check = 0 ) {
			$armember_addons_page = get_transient("arm_addons_listing_data_page");
			if(false === $armember_addons_page || $force_check == 1) {
				$plugins           = get_plugins();
				$installed_plugins = array();
				foreach ( $plugins as $key => $plugin ) {
					$is_active                            = is_plugin_active( $key );
					$installed_plugin                     = array(
						'plugin'    => $key,
						'name'      => $plugin['Name'],
						'is_active' => $is_active,
					);
					$installed_plugin['activation_url']   = $is_active ? '' : wp_nonce_url( "plugins.php?action=activate&plugin={$key}", "activate-plugin_{$key}" );
					$installed_plugin['deactivation_url'] = ! $is_active ? '' : wp_nonce_url( "plugins.php?action=deactivate&plugin={$key}", "deactivate-plugin_{$key}" );

					$installed_plugins[] = $installed_plugin;
				}

				global $arm_lite_version;
				$bloginformation = array();
				$str             = $this->get_rand_alphanumeric( 10 );

				if ( is_multisite() ) {
					$multisiteenv = 'Multi Site';
				} else {
					$multisiteenv = 'Single Site';
				}

				$addon_listing = 1;

				$bloginformation[] = get_bloginfo( 'name' );
				$bloginformation[] = get_bloginfo( 'description' );
				$bloginformation[] = ARMLITE_HOME_URL;
				$bloginformation[] = get_bloginfo( 'admin_email' );
				$bloginformation[] = get_bloginfo( 'version' );
				$bloginformation[] = get_bloginfo( 'language' );
				$bloginformation[] = $arm_lite_version;
				$bloginformation[] = sanitize_text_field($_SERVER['REMOTE_ADDR']); //phpcs:ignore
				$bloginformation[] = $str;
				$bloginformation[] = $multisiteenv;
				$bloginformation[] = $addon_listing;
				$bloginformation[] = 'armemberlite';

				$valstring  = implode( '||', $bloginformation );
				$encodedval = base64_encode( $valstring );

				$urltopost = 'https://www.armemberplugin.com/armember_addons/addon_list.php';

				$raw_response = wp_remote_post($urltopost, array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'body' => array('wpversion' => $encodedval),
                    'cookies' => array()
                        )
                );

				if ( is_wp_error( $raw_response ) || $raw_response['response']['code'] != 200 ) {
					return "0|^^|<div class='error_message' style='margin-top:100px; padding:20px;'>" . esc_html__( 'Add-On listing is currently unavailable. Please try again later.', 'armember-membership' ) . '</div>';
				} else {
					set_transient("arm_addons_listing_data_page", $raw_response['body'], DAY_IN_SECONDS);
					return '1|^^|' . $raw_response['body'];
				}
			} else {
                return "1|^^|" . $armember_addons_page;
            }
		}

		function upgrade_to_pro_content()
		{
			$armember_upgrade_to_pro_page = get_transient("arm_upgrade_to_pro_listing_data_page");
			if( false === $armember_upgrade_to_pro_page ) {
				$urltopost = 'https://www.armemberplugin.com/?action=get_upgrade_to_pro_content';
				$raw_response = wp_remote_post($urltopost, array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'body' => array(),
                    'cookies' => array()
                        )
                );

				if ( is_wp_error( $raw_response ) || $raw_response['response']['code'] != 200 ) {
					return 0;
				} else {
					set_transient("arm_upgrade_to_pro_listing_data_page", $raw_response['body'], WEEK_IN_SECONDS);
					return isset( $raw_response['body'] ) ? $raw_response['body'] : 0;
				}
			} else {
                return $armember_upgrade_to_pro_page;
            }
		}

		function growth_plugins_content()
		{
			$armember_upgrade_to_pro_page = get_transient("arm_growth_plugins_listing_data_page");
			if( false === $armember_upgrade_to_pro_page ) {
				$urltopost = 'https://www.armemberplugin.com/';
				$raw_response = wp_remote_post($urltopost, array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'body' => array('get_growth_plugins_content' => 1),
                    'cookies' => array()
                        )
                );

				if ( is_wp_error( $raw_response ) || $raw_response['response']['code'] != 200 ) {
					return 0;
				} else {
					set_transient("arm_growth_plugins_listing_data_page", $raw_response['body'], WEEK_IN_SECONDS);
					return isset( $raw_response['body'] ) ? $raw_response['body'] : 0;
				}
			} else {
                return $armember_upgrade_to_pro_page;
            }
		}

		function arm_install_plugin_install_status( $api, $loop = false ) {
			// This function is called recursively, $loop prevents further loops.
			if ( is_array( $api ) ) {
				$api = (object) $api;
			}

			// Default to a "new" plugin
			$status      = 'install';
			$url         = false;
			$update_file = false;

			/*
			 * Check to see if this plugin is known to be installed,
			 * and has an update awaiting it.
			 */
			$update_plugins = get_site_transient( 'update_plugins' );
			if ( isset( $update_plugins->response ) ) {
				foreach ( (array) $update_plugins->response as $file => $plugin ) {
					if ( $plugin->slug === $api->slug ) {
						$status      = 'update_available';
						$update_file = $file;
						$version     = $plugin->new_version;
						if ( current_user_can( 'update_plugins' ) ) {
							$url = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . $update_file ), 'upgrade-plugin_' . $update_file );
						}
						break;
					}
				}
			}

			if ( 'install' == $status ) {
				if ( is_dir( WP_PLUGIN_DIR . '/' . $api->slug ) ) {
					$installed_plugin = get_plugins( '/' . $api->slug );
					if ( empty( $installed_plugin ) ) {
						if ( current_user_can( 'install_plugins' ) ) {
							$url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $api->slug ), 'install-plugin_' . $api->slug );
						}
					} else {
						$key         = array_keys( $installed_plugin );
						$key         = reset( $key ); // Use the first plugin regardless of the name, Could have issues for multiple-plugins in one directory if they share different version numbers
						$update_file = $api->slug . '/' . $key;
						if ( version_compare( $api->version, $installed_plugin[ $key ]['Version'], '=' ) ) {
							$status = 'latest_installed';
						} elseif ( version_compare( $api->version, $installed_plugin[ $key ]['Version'], '<' ) ) {
							$status  = 'newer_installed';
							$version = $installed_plugin[ $key ]['Version'];
						} else {
							// If the above update check failed, Then that probably means that the update checker has out-of-date information, force a refresh
							if ( ! $loop ) {
								delete_site_transient( 'update_plugins' );
								wp_update_plugins();
								return arm_install_plugin_install_status( $api, true );
							}
						}
					}
				} else {
					// "install" & no directory with that slug
					if ( current_user_can( 'install_plugins' ) ) {
						$url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $api->slug ), 'install-plugin_' . $api->slug );
					}
				}
			}
			if ( isset( $_GET['from'] ) ) { //phpcs:ignore
				$url .= '&amp;from=' . urlencode( wp_unslash( $_GET['from'] ) ); //phpcs:ignore
			}

			$file = $update_file;
			return compact( 'status', 'url', 'version', 'file' );
		}

	}

}
global $arm_social_feature;
$arm_social_feature = new ARM_social_feature_Lite();

/*
  wp_unslash function to remove slashes. default in WordPress 4.6
 */

if ( ! function_exists( 'wp_unslash' ) ) {

	function wp_unslash( $value ) {
		return stripslashes_deep( $value );
	}
}

if ( ! class_exists( 'Automatic_Upgrader_Skin' ) ) {
	if ( version_compare( $GLOBALS['wp_version'], '4.6', '<' ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	} else {
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';
	}

	if ( version_compare( $GLOBALS['wp_version'], '3.8', '<' ) ) {

		class Automatic_Upgrader_Skin extends WP_Upgrader_Skin {

			protected $messages = array();

			/**
			 * Determines whether the upgrader needs FTP/SSH details in order to connect
			 * to the filesystem.
			 *
			 * @since 3.7.0
			 * @since 4.6.0 The `$context` parameter default changed from `false` to an empty string.
			 *
			 * @see request_filesystem_credentials()
			 *
			 * @param bool   $error                        Optional. Whether the current request has failed to connect.
			 *                                             Default false.
			 * @param string $context                      Optional. Full path to the directory that is tested
			 *                                             for being writable. Default empty.
			 * @param bool   $allow_relaxed_file_ownership Optional. Whether to allow Group/World writable. Default false.
			 * @return bool True on success, false on failure.
			 */
			public function request_filesystem_credentials( $error = false, $context = '', $allow_relaxed_file_ownership = false ) {
				if ( $context ) {
					$this->options['context'] = $context;
				}
				// TODO: fix up request_filesystem_credentials(), or split it, to allow us to request a no-output version
				// This will output a credentials form in event of failure, We don't want that, so just hide with a buffer
				$result = parent::request_filesystem_credentials( $error, $context, $allow_relaxed_file_ownership );
				ob_end_clean();
				return $result;
			}

			/**
			 * @access public
			 *
			 * @return array
			 */
			public function get_upgrade_messages() {
				return $this->messages;
			}

			/**
			 * @param string|array|WP_Error $data
			 */
			public function feedback( $data ) {
				if ( is_wp_error( $data ) ) {
					$string = $data->get_error_message();
				} elseif ( is_array( $data ) ) {
					return;
				} else {
					$string = $data;
				}
				if ( ! empty( $this->upgrader->strings[ $string ] ) ) {
					$string = $this->upgrader->strings[ $string ];
				}

				if ( strpos( $string, '%' ) !== false ) {
					$args = func_get_args();
					$args = array_splice( $args, 1 );
					if ( ! empty( $args ) ) {
						$string = vsprintf( $string, $args );
					}
				}

				$string = trim( $string );

				// Only allow basic HTML in the messages, as it'll be used in emails/logs rather than direct browser output.
				$string = wp_kses(
					$string,
					array(
						'a'      => array(
							'href' => true,
						),
						'br'     => true,
						'em'     => true,
						'strong' => true,
					)
				);

				if ( empty( $string ) ) {
					return;
				}

				$this->messages[] = $string;
			}

			/**
			 * @access public
			 */
			public function header() {
			}

			/**
			 * @access public
			 */
			public function footer() {
				$output = ob_get_clean();
				if ( ! empty( $output ) ) {
					$this->feedback( $output );
				}
			}

		}

	}
}

if ( ! class_exists( 'WP_Ajax_Upgrader_Skin' ) ) {
	if ( version_compare( $GLOBALS['wp_version'], '4.6', '<' ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	} else {
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';
	}
	if ( version_compare( $GLOBALS['wp_version'], '4.6', '<' ) ) {

		class WP_Ajax_Upgrader_Skin extends Automatic_Upgrader_Skin {

			/**
			 * Holds the WP_Error object.
			 *
			 * @since 4.6.0
			 * @access protected
			 * @var null|WP_Error
			 */
			protected $errors = null;

			/**
			 * Constructor.
			 *
			 * @since 4.6.0
			 * @access public
			 *
			 * @param array $args Options for the upgrader, see WP_Upgrader_Skin::__construct().
			 */
			public function __construct( $args = array() ) {
				parent::__construct( $args );

				$this->errors = new WP_Error();
			}

			/**
			 * Retrieves the list of errors.
			 *
			 * @since 4.6.0
			 * @access public
			 *
			 * @return WP_Error Errors during an upgrade.
			 */
			public function get_errors() {
				return $this->errors;
			}

			/**
			 * Retrieves a string for error messages.
			 *
			 * @since 4.6.0
			 * @access public
			 *
			 * @return string Error messages during an upgrade.
			 */
			public function get_error_messages() {
				$messages = array();

				foreach ( $this->errors->get_error_codes() as $error_code ) {
					if ( $this->errors->get_error_data( $error_code ) && is_string( $this->errors->get_error_data( $error_code ) ) ) {
						$messages[] = $this->errors->get_error_message( $error_code ) . ' ' . esc_html( wp_strip_all_tags( $this->errors->get_error_data( $error_code ) ) );
					} else {
						$messages[] = $this->errors->get_error_message( $error_code );
					}
				}

				return implode( ', ', $messages );
			}

			/**
			 * Stores a log entry for an error.
			 *
			 * @since 4.6.0
			 * @access public
			 *
			 * @param string|WP_Error $errors Errors.
			 */
			public function error( $errors ) {
				if ( is_string( $errors ) ) {
					$string = $errors;
					if ( ! empty( $this->upgrader->strings[ $string ] ) ) {
						$string = $this->upgrader->strings[ $string ];
					}

					if ( false !== strpos( $string, '%' ) ) {
						$args = func_get_args();
						$args = array_splice( $args, 1 );
						if ( ! empty( $args ) ) {
							$string = vsprintf( $string, $args );
						}
					}

					// Count existing errors to generate an unique error code.
					$errors_count          = count( $errors->get_error_codes() );
					$errors_count_plus_one = $errors_count + 1;
					$this->errors->add( 'unknown_upgrade_error_' . $errors_count_plus_one, $string );
				} elseif ( is_wp_error( $errors ) ) {
					foreach ( $errors->get_error_codes() as $error_code ) {
						$this->errors->add( $error_code, $errors->get_error_message( $error_code ), $errors->get_error_data( $error_code ) );
					}
				}

				$args = func_get_args();
				call_user_func_array( array( $this, 'parent::error' ), $args );
			}

			/**
			 * Stores a log entry.
			 *
			 * @since 4.6.0
			 * @access public
			 *
			 * @param string|array|WP_Error $data Log entry data.
			 */
			public function feedback( $data ) {
				if ( is_wp_error( $data ) ) {
					foreach ( $data->get_error_codes() as $error_code ) {
						$this->errors->add( $error_code, $data->get_error_message( $error_code ), $data->get_error_data( $error_code ) );
					}
				}

				$args = func_get_args();
				call_user_func_array( array( $this, 'parent::feedback' ), $args );
			}

		}

	}
}

if ( ! function_exists( 'wp_register_plugin_realpath' ) ) {

	function wp_register_plugin_realpath( $file ) {
		global $wp_plugin_paths;
		// Normalize, but store as static to avoid recalculation of a constant value
		static $wp_plugin_path = null, $wpmu_plugin_path = null;
		if ( ! isset( $wp_plugin_path ) ) {
			$wp_plugin_path   = wp_normalize_path( WP_PLUGIN_DIR );
			$wpmu_plugin_path = wp_normalize_path( WPMU_PLUGIN_DIR );
		}

		$plugin_path     = wp_normalize_path( dirname( $file ) );
		$plugin_realpath = wp_normalize_path( dirname( realpath( $file ) ) );

		if ( $plugin_path === $wp_plugin_path || $plugin_path === $wpmu_plugin_path ) {
			return false;
		}

		if ( $plugin_path !== $plugin_realpath ) {
			$wp_plugin_paths[ $plugin_path ] = $plugin_realpath;
		}
		return true;
	}
}

if ( ! function_exists( 'wp_normalize_path' ) ) {

	function wp_normalize_path( $path ) {
		$path = str_replace( '\\', '/', $path );
		$path = preg_replace( '|(?<=.)/+|', '/', $path );
		if ( ':' === substr( $path, 1, 1 ) ) {
			$path = ucfirst( $path );
		}
		return $path;
	}
}
