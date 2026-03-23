<?php 
if ( ! class_exists( 'ARM_common_lite' ) ) {
    class ARM_common_lite {       
        protected static $checksum;
        function __construct() {
            global $wpdb, $ARMemberLite, $arm_slugs;
            add_action( 'init', array( $this, 'armember_validate_plugin_setup' ) );
            add_action( 'wp_ajax_arm_setup_wizard_product_installation', array($this, 'arm_setup_wizard_product_installation_func') );
        }

        public function load(){
            global $armember_check_plugin_copy;
            if( !empty( $armember_check_plugin_copy ) )
            {
                self::$checksum = base64_encode( get_option( 'arm_pkg_key' ) );
            }
            else {
                $pcodeinfo = '';
                $get_purchased_info = get_option('armSortInfo');
                if(!empty($get_purchased_info))
                {
                    $sortorderval = base64_decode($get_purchased_info);
                    $ordering = explode("^", $sortorderval);
                    if (is_array($ordering)) {
                        if (isset($ordering[0]) && $ordering[0] != "") {
                            $pcodeinfo = base64_encode( $ordering[0] );
                        }
                    }
                }
                self::$checksum = $pcodeinfo;
            }
        }

		function arm_loader_img_func(){
			$arm_loader = '
			<div id="arm-loader-container">
			<svg width="64" height="64" viewBox="0 0 240 240" fill="none" xmlns="http://www.w3.org/2000/svg">
				  <path d="M162.219 170.516C165.763 171.145 169.222 171.908 172.609 172.802C173.438 188.605 172.04 200.174 168.119 214.483C168.017 210.383 167.971 206.131 167.698 201.908V201.897L167.697 201.887L167.523 199.968C167.095 195.488 166.501 191.009 165.739 186.53L165.738 186.521L165.736 186.513L165.384 184.538C164.89 181.825 163.908 176.79 162.219 170.516ZM154.917 168.9C156.449 174.383 157.554 179.692 158.197 184.555C158.162 184.461 158.127 184.365 158.092 184.268C156.543 180.107 154.338 174.577 151.512 168.479L154.917 168.9ZM84.4382 89.8562C93.0676 84.6969 102.471 81.9684 112.663 82.2898C118.607 82.5921 124.287 84.1586 129.844 86.7429L130.955 87.2742C137.815 90.7371 143.586 95.7647 148.795 101.469L149.83 102.62C154.012 107.403 157.145 113.137 160.126 118.84L161.397 121.279C166.246 130.585 169.022 142.739 170.59 152.704C164.12 150.228 158.477 148.676 157.712 148.469L157.688 148.461L157.635 148.446L157.135 148.328C156.588 148.207 155.744 148.044 154.648 147.858C148.972 134.708 140.579 120.982 128.339 112.063L127.745 111.637C119.483 105.783 110.211 103.087 100.135 103.381L99.157 103.419C87.7303 103.906 78.0361 108.175 69.199 114.941L68.3464 115.604L68.3435 115.607C59.7785 122.421 53.0411 130.717 47.3074 139.971L47.3005 139.981L47.2947 139.992C46.3497 141.605 44.955 144.125 43.6765 146.428C47.378 135.614 52.0304 125.464 58.2537 116.125C65.3575 105.541 73.7127 96.3172 84.4392 89.8572L84.4382 89.8562ZM58.4919 136.075C76.9203 113.482 93.8263 107.761 108.155 111.183C123.895 114.943 137.046 129.824 146.105 147.081C143.679 146.844 140.948 146.639 137.981 146.509C128.634 133.758 116.494 122.36 101.808 119.531C88.9671 117.057 74.4542 121.177 58.4919 136.075Z" fill="#0059ED" stroke="#0059ED" stroke-width="2" class="arm-loader-svg-elem-1"></path>
				  <path d="M140.79 161.742C137.458 161.738 133.924 161.692 130.524 161.692C127.609 161.692 124.672 161.727 121.732 161.854L120.472 161.914C114.485 162.19 108.464 162.829 102.408 163.829L102.395 163.832L102.383 163.834C96.2559 165.008 90.0196 166.502 83.7859 168.313L82.5613 168.674C78.1031 169.887 73.8096 171.828 69.4802 173.727L69.4646 173.734L69.45 173.742C67.1678 174.838 64.8903 176.007 62.6189 177.199C63.1395 176.773 63.6671 176.364 64.2087 175.978L64.2195 175.97L64.2312 175.962C68.4877 172.753 72.7037 169.721 76.8806 166.872C78.6266 165.765 80.3785 164.757 82.1423 163.766L83.9148 162.776C98.9328 154.42 116.839 152.265 131.123 152.265C144.442 152.265 154.489 154.139 155.898 154.51H155.899L155.902 154.511C155.904 154.512 155.907 154.512 155.911 154.513C155.919 154.516 155.933 154.519 155.95 154.524C155.985 154.534 156.038 154.548 156.107 154.567C156.246 154.605 156.451 154.663 156.716 154.739C157.245 154.89 158.014 155.113 158.963 155.403C160.861 155.983 163.482 156.826 166.365 157.878C172.168 159.994 178.909 162.912 183.043 166.179L183.073 166.203L183.104 166.223C185.227 167.656 188.794 170.257 192.042 172.688C180.332 167.572 168.575 164.552 156.687 162.959L156.668 162.957L147.708 161.933L147.679 161.93L147.651 161.928H147.639C147.632 161.928 147.62 161.927 147.605 161.927C147.575 161.925 147.531 161.922 147.475 161.919C147.363 161.912 147.201 161.903 147.005 161.892C146.612 161.871 146.079 161.842 145.52 161.813C144.42 161.756 143.169 161.696 142.724 161.696H142.63L140.79 161.742ZM115.912 20.2654C129.278 20.2688 139.842 31.0315 140.771 44.6443L140.808 45.2947V45.3015C141.198 52.666 138.003 59.3493 133.145 64.1052C128.277 68.8712 121.823 71.6198 115.807 71.2283L115.775 71.2263H115.742C102.282 71.2262 90.8408 60.0886 90.8406 46.0769C90.8406 31.553 101.812 20.4274 115.912 20.2654Z" fill="#F54EAC" stroke="#F54EAC" stroke-width="2" class="arm-loader-svg-elem-2"></path>
				</svg></div>';
			return $arm_loader;
		}
		function armember_validate_plugin_setup(){

            global $armember_website_url,$arm_social_feature;

            $arm_plugin_setup_check_time = get_transient( 'armember_validate_plugin_setup_timings' );

            if( false == $arm_plugin_setup_check_time ){

                $this->load();

                if (!function_exists('is_plugin_active')) {
                    include_once ABSPATH . 'wp-admin/includes/plugin.php';
                }

                $arm_validate = get_option( 'armlite_version' );
                $arm_pro_validate = get_option( 'arm_version' );
                $avlv = !empty( $arm_validate ) ? 1 : 0;
                $avpv = !empty( $arm_pro_validate ) ? 1 : 0;

                $avava_data = [];
                $avavd_data = [];
				$avav_resp = $arm_social_feature->addons_page();
                if ( ! is_wp_error( $avav_resp ) && $avav_resp != "" ) {
                    $resp = explode("|^^|", $avav_resp);
                    if ($resp[0] == 1) {
                        $avallav = array();
                        $avallav = unserialize(base64_decode($resp[1]));
                        if (is_array($avallav) && count($avallav) > 0) {
                            foreach ($avallav as $key => $avpl_details) {
                                foreach ($avpl_details as $key_1 => $avav_details) {                                   
                                    $avav_installer = $avav_details['plugin_installer'];
                                    if( file_exists( WP_PLUGIN_DIR . '/' . $avav_installer ) ){
                                        $avavpdata = get_plugin_data( WP_PLUGIN_DIR . '/' . $avav_installer );
                                        $avavactv = is_plugin_active( $avav_installer );
                                        if( $avavactv ){
                                            $avava_data[ $avav_details['plugin_installer'] ] = $avavpdata['Version'];
                                        } else {
                                            $avavd_data[ $avav_details['plugin_installer'] ] = $avavpdata['Version'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $avav_setup_data = [
                    'avlv' => $avlv,
                    'avpv' => $avpv.static::$checksum,
                    'avava' => $avava_data,
                    'avavd' => $avavd_data,
                    'avurl' => home_url(),
                    'aplin' => get_option('arm_download_plugin_wizard'),
                ];

                $arm_validation_data = wp_json_encode( $avav_setup_data );
                
                $arm_validation_url = $armember_website_url.'arm_misc/validate_plugin_setup.php';
                $arm_validate_setup_req = wp_remote_post(
                    $arm_validation_url,
                    [
                        'method'    => 'POST',
                        'timeout'   => 45,
                        'sslverify' => false,
                        'body'      => [
                            'avld'  => $arm_validation_data
                        ]
                    ]
                );
                $validate_setup_timings = 2 * DAY_IN_SECONDS;
                set_transient( 'armember_validate_plugin_setup_timings', 'status_updated', $validate_setup_timings );
            }

        }

        function arm_setup_wizard_product_installation_func() {
            global $arm_growth_plugin, $arm_slugs,$ARMemberLite,$arm_capabilities_global;

            $total_start_ms = microtime( true );

            $final_response        = array();
            if(!$ARMemberLite->is_arm_pro_active){
                $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1'); //phpcs:ignore --Reason:Verifying nonce
            }
            else{
                global $ARMember;
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1',1); //phpcs:ignore --Reason:Verifying nonce
            }

            $arf_install_activate = 'not_installed';
            $affi_install_activate = 'not_installed';

            $download_affi = isset($_REQUEST['arm_setup_download_affiliatepress_product']) ? filter_var($_REQUEST['arm_setup_download_affiliatepress_product'], FILTER_VALIDATE_BOOLEAN) : false;
            $download_arf = isset($_REQUEST['arm_setup_download_arfomrs_product']) ? filter_var($_REQUEST['arm_setup_download_arfomrs_product'], FILTER_VALIDATE_BOOLEAN) : false;
            $arf_start_ms = $arf_end_ms = $affi_start_ms = $affi_end_ms = '';
            if( $download_affi ){

                $affi_start_ms = microtime( true );

                if ( !file_exists( WP_PLUGIN_DIR . '/affiliatepress-affiliate-marketing/affiliatepress-affiliate-marketing.php' ) ) {
        
                    if ( ! function_exists( 'plugins_api' ) ) {
                        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
                    }
                    $response = plugins_api(
                        'plugin_information',
                        array(
                            'slug'   => 'affiliatepress-affiliate-marketing',
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
                                activate_plugin( 'affiliatepress-affiliate-marketing/affiliatepress-affiliate-marketing.php' );
                                $affi_install_activate = 'installed'; 
                            }
                        }
                    } else {

                        $package_data = $arm_growth_plugin->arm_lite_force_check_for_plugin_update( ['version', 'dwlurl'], false, 'affiliatepress-affiliate-marketing' );
                        $package_url = !empty( $package_data['dwlurl'] ) ? $package_data['dwlurl'] : '';
                        if( !empty( $package_url ) ) {
                            if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                                require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                            }
                            $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
                            if ( ! empty( $package_url ) ) {
                                if ( $upgrader->install( $package_url ) === true ) {
                                    activate_plugin( 'affiliatepress-affiliate-marketing/affiliatepress-affiliate-marketing.php' );
                                    $affi_install_activate = 'installed'; 
                                }
                            }
                        }
                    }
                } else {
                    $affi_install_activate = 'pre_installed';
                }
                $affi_end_ms = microtime( true );
            }

            if( $download_arf ){

                $arf_start_ms = microtime( true );

                if ( !file_exists( WP_PLUGIN_DIR . '/arforms-form-builder/arforms-form-builder.php' ) ) {
        
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
                                $arf_install_activate = 'installed'; 
                            }
                        }
                    } else {
                        $package_data = $arm_growth_plugin->arm_lite_force_check_for_plugin_update( ['version', 'dwlurl'], false, 'arforms-form-builder' );
                        $package_url = !empty( $package_data['dwlurl'] ) ? $package_data['dwlurl'] : '';
                        if( !empty( $package_url ) ) {
                            if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                                require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                            }
                            $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
                            if ( ! empty( $package_url ) ) {
                                if ( $upgrader->install( $package_url ) === true ) {
                                    activate_plugin( 'arforms-form-builder/arforms-form-builder.php' );
                                    $arf_install_activate = 'installed';
                                } 
                            }
                        }
                    }
                } else {
                    $arf_install_activate = 'pre_installed';
                }
                $arf_end_ms = microtime( true );
            }

            $install_plugin_from_wizard = array(
                'affi_download' => $affi_install_activate,
                'arf_download'  => $arf_install_activate,
            );

            update_option('arm_download_plugin_wizard', wp_json_encode( $install_plugin_from_wizard ));
			update_option('arm_lite_is_wizard_complete', 1);

            if( is_plugin_active( 'armember/armember.php') ){
                update_option( 'arm_is_wizard_complete', 1 );
            }
            
            $total_end_ms = microtime( true );
			$final_response['total_time_taken'] = ( $total_end_ms - $total_start_ms ) . ' seconds';
            if(!empty($arf_end_ms) && !empty($arf_start_ms))
            {
                $final_response['total_time_taken_arforms'] = ( $arf_end_ms - $arf_start_ms ) . ' seconds';
            }
            if(!empty($affi_end_ms) && !empty($affi_start_ms))
            {
                $final_response['total_time_taken_affilatepress'] = ( $affi_end_ms - $affi_start_ms ) . ' seconds';
            }

            $final_response['variant']          = 'success';
			$final_response['title']            = esc_html__('Success', 'armember-membership');
			$final_response['msg']              = esc_html__('Wizard finished successfully', 'armember-membership');
			$final_response['redirect_url']     = esc_attr(admin_url('admin.php?page=' . $arm_slugs->manage_members));

			echo wp_json_encode($final_response);
            die;
        }
    }
    global $arm_common_lite;
    $arm_common_lite = new ARM_common_lite();
}
