<?php
if ( ! class_exists( 'ARM_growth_plugin_Lite' ) ) {

	class ARM_growth_plugin_Lite {

        function __construct(){
            add_action('wp_ajax_arm_get_bookingpress', array( $this, 'arm_get_bookingpress_plugin_func'));

			add_action('wp_ajax_arm_get_arforms', array( $this, 'arm_get_arforms_plugin_func'));
			
			add_action('wp_ajax_arm_get_arprice', array( $this, 'arm_get_arprice_plugin_func'));
        }

        function arm_get_bookingpress_plugin_func() {
			global $wpdb, $ARMemberLite, $arm_capabilities_global;
			$response = array(
				'type' => 'error',
				'msg'  => esc_html__( 'Sorry, Something went wrong. Please try again.', 'armember-membership' ),
			);

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_growth_plugins'], '1' ); //phpcs:ignore --Reason:Verifying nonce

			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_get_bookingpress' ) { //phpcs:ignore
				$arm_bookingpress_install_activate = 1; 
				if ( ! file_exists( WP_PLUGIN_DIR . '/bookingpress-appointment-booking/bookingpress-appointment-booking.php' ) ) {
        
					if ( ! function_exists( 'plugins_api' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
					}
					$response = plugins_api(
						'plugin_information',
						array(
							'slug'   => 'bookingpress-appointment-booking',
							'fields' => array(
								'sections' => false,
								'versions' => true,
							),
						)
					);
					if ( ! is_wp_error( $response ) && property_exists( $response, 'versions' ) ) {
						if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
							require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
						}
						$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
						$source   = ! empty( $response->download_link ) ? $response->download_link : '';
						
						if ( ! empty( $source ) ) {
							if ( $upgrader->install( $source ) === true ) {
								activate_plugin( 'bookingpress-appointment-booking/bookingpress-appointment-booking.php' );
								$arm_bookingpress_install_activate = 1; 
							}
						}
					} else {
						$package_data = $this->arm_lite_force_check_for_plugin_update( ['version', 'dwlurl'], false, 'bookingpress-appointment-booking' );

						$package_url = !empty( $package_data['dwlurl'] ) ? $package_data['dwlurl'] : '';
						
						if(!empty($package_url)) {	
							if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
								require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
							}
							$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
							update_option('arm_lite_download_automatic', 1);
                                                        
							if ( ! empty( $package_url ) ) {
								if ( $upgrader->install( $package_url ) === true ) {
									activate_plugin( 'bookingpress-appointment-booking/bookingpress-appointment-booking.php' );
									$arm_bookingpress_install_activate = 1;
								}
							}
						}
					}
				}
			
				if ( ! empty( $arm_bookingpress_install_activate ) && $arm_bookingpress_install_activate == 1 ) {
					$response = array(
						'type' => 'success',
						'msg'  => esc_html__('BookingPress Successfully installed.', 'armember-membership' ),
					);
				} else {
					$response = array(
						'type' => 'error',
						'msg'  => esc_html__('Something went wrong please try again later.', 'armember-membership' ),
					);
				}
			}
			
			echo wp_json_encode( $response );
			die();
			
		}

        function arm_get_arforms_plugin_func() {
			global $wpdb, $ARMemberLite, $arm_capabilities_global;
			$response = array(
				'type' => 'error',
				'msg'  => esc_html__( 'Sorry, Something went wrong. Please try again.', 'armember-membership' ),
			);

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_growth_plugins'], '1' ); //phpcs:ignore --Reason:Verifying nonce

			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_get_arforms' ) { //phpcs:ignore
				$arm_arforms_install_activate = 1; 
				if ( ! file_exists( WP_PLUGIN_DIR . '/arforms-form-builder/arforms-form-builder.php' ) ) {
        
					if ( ! function_exists( 'plugins_api' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
					}
					$response = plugins_api(
						'plugin_information',
						array(
							'slug'   => 'arforms-form-builder',
							'fields' => array(
								'sections' => false,
								'versions' => true,
							),
						)
					);
					if ( ! is_wp_error( $response ) && property_exists( $response, 'versions' ) ) {
						if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
							require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
						}
						$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
						$source   = ! empty( $response->download_link ) ? $response->download_link : '';
						
						if ( ! empty( $source ) ) {
							if ( $upgrader->install( $source ) === true ) {
								activate_plugin( 'arforms-form-builder/arforms-form-builder.php' );
								$arm_arforms_install_activate = 1; 
							}
						}
					} else {

						$package_data = $this->arm_lite_force_check_for_plugin_update( ['version', 'dwlurl'], false, 'arforms-form-builder' );

						$package_url = !empty( $package_data['dwlurl'] ) ? $package_data['dwlurl'] : '';
                        
						if(!empty($package_url)) {
							if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
								require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
							}
							$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
							update_option('arm_lite_download_automatic', 1);
                            
							if ( ! empty( $package_url ) ) {
								if ( $upgrader->install( $package_url ) === true ) {
									activate_plugin( 'arforms-form-builder/arforms-form-builder.php' );
									$arm_arforms_install_activate = 1;
								}
                            				}
							
						}
					}
				}
			
				if ( ! empty( $arm_arforms_install_activate ) && $arm_arforms_install_activate == 1 ) {
					$response = array(
						'type' => 'success',
						'msg'  => esc_html__('ARForms Successfully installed.', 'armember-membership' ),
					);
				}
				else
				{
					$response = array(
						'type' => 'error',
						'msg'  => esc_html__('Something went wrong please try again later.', 'armember-membership' ),
					);
				}
			}
			
			echo wp_json_encode( $response );
			die();
			
		}
		function arm_get_arprice_plugin_func() {
			global $wpdb, $ARMemberLite, $arm_capabilities_global;
			$response = array(
				'type' => 'error',
				'msg'  => esc_html__( 'Sorry, Something went wrong. Please try again.', 'armember-membership' ),
			);

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_growth_plugins'], '1' ); //phpcs:ignore --Reason:Verifying nonce

			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_get_arprice' ) { //phpcs:ignore
				$arm_arprice_install_activate = 1; 
				if ( ! file_exists( WP_PLUGIN_DIR . '/arprice-responsive-pricing-table/arprice-responsive-pricing-table.php' ) ) {
        
					if ( ! function_exists( 'plugins_api' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
					}
					$response = plugins_api(
						'plugin_information',
						array(
							'slug'   => 'arprice-responsive-pricing-table',
							'fields' => array(
								'sections' => false,
								'versions' => true,
							),
						)
					);
					if ( ! is_wp_error( $response ) && property_exists( $response, 'versions' ) ) {
						if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
							require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
						}
						$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
						$source   = ! empty( $response->download_link ) ? $response->download_link : '';
						
						if ( ! empty( $source ) ) {
							if ( $upgrader->install( $source ) === true ) {
								activate_plugin( 'arprice-responsive-pricing-table/arprice-responsive-pricing-table.php' );
								$arm_arprice_install_activate = 1; 
							}
						}
					} else {

						$package_data = $this->arm_lite_force_check_for_plugin_update( ['version', 'dwlurl'], false, 'arprice-responsive-pricing-table' );

						$package_url = !empty( $package_data['dwlurl'] ) ? $package_data['dwlurl'] : '';

						if(!empty($package_url)) {						
							if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
								require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
							}
							$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
							update_option('arm_lite_download_automatic', 1);
                            
							if ( ! empty( $package_url ) ) {
								if ( $upgrader->install( $package_url ) === true ) {
									activate_plugin( 'arprice-responsive-pricing-table/arprice-responsive-pricing-table.php' );
									$arm_arprice_install_activate = 1;
								}
							}
							
						}
					}
				}
				
				if ( ! empty( $arm_arprice_install_activate ) && $arm_arprice_install_activate == 1 ) {
					$response = array(
						'type' => 'success',
						'msg'  => esc_html__('ARPrice Successfully installed.', 'armember-membership' ),
					);
				}
				else
				{
					$response = array(
						'type' => 'success',
						'msg'  => esc_html__('Something went wrong please try again later.', 'armember-membership' ),
					);
				}
			}
			
			echo wp_json_encode( $response );
			die();
			
		}

        public function arm_lite_force_check_for_plugin_update( $param = [], $force_update = false,$slug = '' ){
			global $wp_version;
	
			$arm_lite_plugin_version = '';
			if( file_exists( WP_PLUGIN_DIR . '/armember-membership/armember-membership.php' ) ){
				$arm_lite_plugin_data         = get_plugin_data( WP_PLUGIN_DIR . '/armember-membership/armember-membership.php' );
				$arm_lite_plugin_version 	  = $arm_lite_plugin_data['Version'];
			}
	
			if( empty( $slug ) ){
				return false;
			}
			$arm_api_url = 'https://www.arpluginshop.com';
			$args = array(
				'slug' => $slug,
			);
		
			$request_string = array(
				'body' => array(
					'action' => 'lite_plugin_new_version_check',
					'request' => serialize( $args ),
					'api-key' => md5( ARMLITE_HOME_URL ),
					'is_update' => $force_update,
				),
				'sslverify' => false,
				'user-agent' => 'ARMLITE-WordPress/'.$wp_version.';'.ARMLITE_HOME_URL
			);
		
			//Start checking for an update
			$raw_response = wp_remote_post( $arm_api_url, $request_string );
		
			if( !is_wp_error( $raw_response ) && ( $raw_response['response']['code'] == 200 ) ){
				$response = @unserialize( $raw_response['body'] );
			}
			
			
			if( isset( $response['access_request'] ) && !empty( $response['access_request'] ) && 'success' == $response['access_request'] ){
				if( isset( $response['access_package'] ) && !empty( $response['access_package'] ) ){
					$update_package = @unserialize( $response['access_package'] );
					if( isset( $update_package ) && is_array( $update_package ) && !empty( $update_package ) ){
						$version = $update_package['version'];
						
						if( !empty( $param ) ){
							$response_arr = [];
							foreach( $param as  $post_key ){
								$response_arr[ $post_key ] = !empty( $update_package[ $post_key ] ) ? $update_package[ $post_key ] : '';
							}
		
							return $response_arr;
						}
	
						$current_version = $arm_lite_plugin_version;
						
						if( version_compare( $current_version, $version, '>=') ){
							delete_option( 'arm_show_lite_update_failed_notice' );
							return false;
						}
					}
				}
			}
			return true;
		}       

    }

    $arm_growth_plugin = new ARM_growth_plugin_Lite;

}