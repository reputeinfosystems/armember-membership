<?php 
if ( ! class_exists( 'ARM_common_lite' ) ) {
    class ARM_common_lite {
        protected static $checksum;
        function __construct() {
            global $wpdb, $ARMemberLite, $arm_slugs;
            add_action( 'init', array( $this, 'armember_validate_plugin_setup' ) );
            add_action( 'wp_ajax_arm_setup_wizard_product_installation', array($this, 'arm_setup_wizard_product_installation_func') );

            add_shortcode( 'arm_member_panel', array($this,'arm_member_panel_func') );

            add_filter('arm_change_content_before_display_form_member_panel',array($this,'arm_change_content_before_display_form_member_panel_func'),10,5);

            add_action('wp_ajax_arm_panel_change_tab',array($this,'arm_panel_change_tab_func'));

            add_action('wp_head', array($this, 'arm_head_func'));

            add_action('wp_ajax_arm_get_membership_expand_info',array($this,'arm_get_membership_expand_info_func'));

            add_action('wp_ajax_arm_get_transaction_expand_info',array($this,'arm_get_transaction_expand_info_func'));
            
        }       

        function arm_change_content_before_display_form_member_panel_func($content,$arm_org_forms,$arm_ingored_fields,$atts, $formRandomID){
            //cerate avatar and profile forms fields
            $arm_ignored_fields_arr = array();
            global $arm_member_forms,$arm_members_class,$ARMemberLite;           
            $dbFormFields = $arm_member_forms->arm_get_db_form_fields( true );
            $arm_existing_form_fields = $arm_org_forms->fields;
            foreach($arm_existing_form_fields as $field_key => $field_data){
                if(in_array($field_data['arm_form_field_slug'],$arm_ingored_fields)){
                    array_push($arm_ignored_fields_arr,$field_data['arm_form_field_slug']);
                }               
            }
            $user_id     = intval(abs( get_current_user_id() )); //phpcs:ignore
            $user        = $arm_members_class->arm_get_member_detail( $user_id );
            $default_form_id = $arm_member_forms->arm_get_default_form_id('registration');
            $arm_form_id = isset( $user->arm_form_id ) ? $user->arm_form_id : $default_form_id;
            if($ARMemberLite->is_arm_pro_active)
            {
                $armform ='';
                $armform = apply_filters('arm_get_member_forms_filter',$armform);
            }
            else
            {
                $armform = new ARM_Form_Lite();
            }
            if ( ! empty( $arm_form_id ) && $arm_form_id != 0 ) {
                $userRegForm     = $arm_member_forms->arm_get_single_member_forms( $arm_form_id );
                $arm_exists_form = $armform->arm_is_form_exists( $arm_form_id );
                if ( $arm_exists_form ) {
                    $armform->init( (object) $userRegForm );
                }
            }
            $counter = 0;
            $after_profile_field = '';
            if((isset($_SESSION['arm_file_upload_arr']) && empty($arm_reset_file_upload_data_flag) ) || !isset($_SESSION['arm_file_upload_arr'])){
                $arm_file_upload_arr_avatar = isset($_SESSION['arm_file_upload_arr']['avatar']) ? 1 : 0;
                $arm_file_upload_arr_cover = isset($_SESSION['arm_file_upload_arr']['profile_cover']) ? 1 : 0;
			
                if(isset($_SESSION['arm_file_upload_arr'])){
                    unset($_SESSION['arm_file_upload_arr']);
                }
                $_SESSION['arm_file_upload_arr'] = array();
                if(!empty($arm_file_upload_arr_avatar))
                {
                    $_SESSION['arm_file_upload_arr']['avatar'] = array();
                }
                if(!empty($arm_file_upload_arr_cover))
                {
                    $_SESSION['arm_file_upload_arr']['profile_cover'] = array();
                }
            }
            foreach ( $dbFormFields as $meta_key => $field ) {
                $field_options = maybe_unserialize( $field );
                $field_options = apply_filters( 'arm_change_field_options', $field_options );
                $meta_key      = isset( $field_options['meta_key'] ) ? $field_options['meta_key'] : $field_options['id'];
                $field_id      = $meta_key . arm_generate_random_code();
                if ( in_array($meta_key,$arm_ignored_fields_arr)){
                    if ( $meta_key == 'profile_cover') {
                        
                        $field_options['required'] = 0;
                        $content.='<div class="arm_form_fields_wrapper">';
                            if ( ! empty( $user ) ) {
                                $field_options['value'] = $user->$meta_key;
                            }
                            $content .= $arm_member_forms->arm_member_form_get_fields_by_type( $field_options, $field_id, $arm_form_id, 'active', $armform ); //phpcs:ignore
                            $content .= '<div class="armclear"></div>
                        </div>';
                    }
                    else if( $meta_key == 'avatar') {
                        $field_options['required'] = 0;
                        $after_profile_field.='<div class="arm_form_field_avatar_section">';
                        $after_profile_field.='<div class="arm_form_field_avatar">';
                        $after_profile_field.='<div class="arm_form_fields_wrapper arm_avatar_prev">'.do_shortcode('[arm_avatar]').'</div>';
                        $after_profile_field.='<a href="javascript:void(0)" class="arm_edit_member_avatar">'.esc_html__('Edit','armember-membership').'</a>';
                        $after_profile_field.='<div class="arm_form_fields_wrapper arm_avatar_form_field_wrapper arm_hidden_section">';
                            if ( ! empty( $user ) ) {
                                $field_options['value'] = $user->$meta_key;
                            }
                            $after_profile_field .= $arm_member_forms->arm_member_form_get_fields_by_type( $field_options, $field_id, $arm_form_id, 'active', $armform ); //phpcs:ignore
                        $after_profile_field .= '<div class="armclear"></div></div></div>';
                        $after_profile_field .= '<div class="arm-panel-profile_info-heading">'. esc_html__("Profile info","armember-membership") .'</div> </div>';                       
                    }
                    $counter++;
                }
            }
            $content .= '<input type="hidden" name="arm_member_panel" value="1"/>';
            $content .= $after_profile_field;
            return $content;
        }

        public function arm_head_func(){
            //load css and js file
            global $ARMemberLite,$wp_query;
            if($ARMemberLite->is_arm_pro_active){
                global $ARMember;
                $ARMember->set_front_css(2);
                $ARMember->set_front_js(true);
            }
            else{
                $ARMemberLite->set_front_css(2);
                $ARMemberLite->set_front_js(true);
            }

            $found_matches = array();
            $pattern       = '\[(\[?)(arm_member_panel)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            $posts         = $wp_query->posts;
            if ( is_array( $posts ) ) {
                foreach ( $posts as $post ) {
                    if ( preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches ) > 0 ) {
                        $found_matches[] = $matches;
                    }
                }
                $tempids = array();
                if ( is_array( $found_matches ) && count( $found_matches ) > 0 ) {
                    
                    wp_register_style('armlite_front_user_dashboard_css', MEMBERSHIPLITE_URL . '/css/arm_front_dashboard.css', array(), MEMBERSHIPLITE_VERSION);
                    if(!wp_style_is('armlite_front_user_dashboard_css', 'enqueued')){
                        wp_enqueue_style('armlite_front_user_dashboard_css');
                    }
                    wp_register_script('armlite_front_dashboard_js', MEMBERSHIPLITE_URL . '/js/arm_front_dashboard.js', array('jquery'), MEMBERSHIPLITE_URL,false);
                    if(!wp_script_is('armlite_front_dashboard_js', 'enqueued')){
                        wp_enqueue_script('armlite_front_dashboard_js');
                    }
                }
            }
            
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

            $download_affi = isset($_REQUEST['arm_setup_download_affiliatepress_product']) ? filter_var($_REQUEST['arm_setup_download_affiliatepress_product'], FILTER_VALIDATE_BOOLEAN) : false; //phpcs:ignore
            $download_arf = isset($_REQUEST['arm_setup_download_arfomrs_product']) ? filter_var($_REQUEST['arm_setup_download_arfomrs_product'], FILTER_VALIDATE_BOOLEAN) : false; //phpcs:ignore
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

        function arm_member_panel_func($atts, $content = null, $tag = ''){
            global $ARMemberLite;
            $arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}
            
            ob_start();
            
            $view_file = MEMBERSHIPLITE_VIEWS_DIR . '/arm_front_dashboard.php';
    
            if(file_exists($view_file)){
                include $view_file;
            }
        
            return ob_get_clean();
        }

        function arm_get_membership_expand_info_func()
        {
            global $ARMemberLite,$arm_global_settings;
            
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $posted_data  = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend'), $_POST ); //phpcs:ignore

            $membership_id = isset( $posted_data['membership_id'] ) ? absint( $posted_data['membership_id'] ) : 0;
            $user_id = isset( $posted_data['user_id'] ) ? absint( $posted_data['user_id'] ) : 0;
            $expand_visible_columns = isset($posted_data['visible_columns']) ? $posted_data['visible_columns'] : array();

            $arm_expand_col_response = array('type'=>'error','msg'=>esc_html__('Something went wrong','armember-membership'));

            $arm_current_member_columns = array('current_membership_is','current_membership_started_on','current_membership_expired_on','current_membership_recurring_profile','current_membership_remaining_occurence','current_membership_next_billing_date','trial_period');

            $arm_expand_cols = array_diff($arm_current_member_columns, $expand_visible_columns);

            $planData      = get_user_meta( $user_id, 'arm_user_plan_' . $membership_id, true );
            
            $curPlanDetail = !empty($planData['arm_current_plan_detail']) ? $planData['arm_current_plan_detail'] : array();
            $start_plan    = !empty( $planData['arm_start_plan'] ) ? $planData['arm_start_plan'] : '';
            if ( ! empty( $planData['arm_started_plan_date'] ) && $planData['arm_started_plan_date'] <= $start_plan ) {
                $start_plan = !empty( $planData['arm_started_plan_date'] ) ? $planData['arm_started_plan_date'] : $start_plan;
            }
            $expire_plan    = !empty( $planData['arm_expire_plan'] ) ? $planData['arm_expire_plan'] : '';
            $change_plan    = !empty( $planData['arm_change_plan_to'] ) ? $planData['arm_change_plan_to'] : '';
            $effective_from = !empty( $planData['arm_subscr_effective'] ) ? $planData['arm_subscr_effective'] : '';

            if ( $change_plan != '' && $effective_from != '' && ! empty( $effective_from ) && ! empty( $change_plan ) ) {
                $change_plan_to_array[ $change_plan ] = $effective_from;

            }

            $payment_mode      = '';
            $payment_cycle     = '';
            $is_plan_cancelled = '';
            $completed         = '';
            $recurring_time    = '';
            $recurring_profile = '';
            $next_due_date     = '-';
            $user_payment_mode = '';
            if(!$ARMemberLite->is_arm_pro_active){
                if ( ! empty( $curPlanDetail ) ) {
                    $plan_info = new ARM_Plan_Lite( 0 );
                    $plan_info->init( (object) $curPlanDetail );
                } else {
                    $plan_info = new ARM_Plan_Lite( $membership_id );
                }
            }
            else{
                if ( ! empty( $curPlanDetail ) ) {
                    $plan_info = new ARM_Plan( 0 );
                    $plan_info->init( (object) $curPlanDetail );
                } else {
                    $plan_info = new ARM_Plan( $membership_id );
                }
            }

            if ( $plan_info->exists() ) {
                $plan_options = $plan_info->options;

                if ( $plan_info->is_recurring() ) {
                    $completed              = $planData['arm_completed_recurring'];
                    $is_plan_cancelled      = $planData['arm_cencelled_plan'];
                    $payment_mode           = $planData['arm_payment_mode'];
                    $payment_cycle          = $planData['arm_payment_cycle'];
                    $recurring_plan_options = $plan_info->prepare_recurring_data( $payment_cycle );
                    $recurring_time         = $recurring_plan_options['rec_time'];
                    $next_due_date          = $planData['arm_next_due_payment'];

                    if ( $payment_mode == 'auto_debit_subscription' ) {
                        $user_payment_mode = '<br/>( ' . esc_html__( 'Auto Debit', 'armember-membership' ) . ' )';
                    } else {
                        $user_payment_mode = '';
                    }
                    $arm_trial_start_date = $planData['arm_trial_start'];
                    $arm_is_user_in_trial = $planData['arm_is_trial_plan'];

                    if ( $recurring_time == 'infinite' || empty( $expire_plan ) ) {
                        $remaining_occurence = esc_html__( 'Infinite', 'armember-membership' );
                    } else {
                        $remaining_occurence = $recurring_time - $completed;
                    }

                    if ( $remaining_occurence > 0 || $recurring_time == 'infinite' ) {
                        if ( ! empty( $next_due_date ) ) {
                            $next_due_date = date_i18n( $date_format, $next_due_date );
                        }
                    } else {
                        $next_due_date = '';
                    }

                    $arm_is_user_in_grace = $planData['arm_is_user_in_grace'];

                    $arm_grace_period_end = $planData['arm_grace_period_end'];
                } else {
                    $recurring_profile    = '-';
                    $arm_trial_start_date = '';
                    $remaining_occurence  = '-';
                    $arm_is_user_in_grace = 0;
                    $arm_grace_period_end = '';
                    $arm_is_user_in_trial = 0;

                }

                $recurring_profile = $plan_info->new_user_plan_text( false, $payment_cycle );
            }
            $arm_expand_content = '<div class="arm_expand_grid_section">';
            foreach($arm_expand_cols as $expand_col_key){
                switch($expand_col_key){
                    case 'current_membership_is':
                        $plan_name = !empty($plan_info->name) ? $plan_info->name : '-';
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Membership Plan','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($plan_name).'</div></div>';
                    break;
                    case 'current_membership_started_on':
                        $start_plan_date = !empty($start_plan) ? date_i18n( $date_format, $start_plan ) : '-';
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Start Date','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($start_plan_date).'</div></div>';
                    break;
                    case 'current_membership_expired_on':
                        $expire_plan_date = !empty($expire_plan) ? date_i18n( $date_format, $expire_plan ) : esc_html__('Never Expires','armember-membership');
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Expiry Date','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($expire_plan_date).'</div></div>';
                    break;
                    case 'current_membership_recurring_profile':
                        $plan_recurring_profile = !empty($recurring_profile) ? $recurring_profile : '-';
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Plan Type','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($plan_recurring_profile).'</div></div>';
                    break;
                    case 'current_membership_remaining_occurence':
                        $plan_remaining_occurence = !empty($remaining_occurence) ? $remaining_occurence : '-';
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Remaining Occurrence','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($plan_remaining_occurence).'</div></div>';
                    break;
                    case 'current_membership_next_billing_date':
                        $plan_next_due_date = !empty($next_due_date) ? $next_due_date : '-';
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Next Billing Date','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($plan_next_due_date).'</div></div>';
                    break;
                    case 'trial_period':
                        if( $arm_is_user_in_trial ){
                            $plan_trial_status = esc_html__('Trial Active','armember-membership') . (!empty($arm_trial_start_date) ? ' ('.esc_html__('Started on','armember-membership').' '.date_i18n( $date_format, $arm_trial_start_date ).')' : '');
                        }
                        else{
                            $plan_trial_status = esc_html__('No','armember-membership');
                        }
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Trial Period','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($plan_trial_status).'</div></div>';
                    break;
                }
            }
            $arm_expand_content .= '</div>';
            if(!empty($arm_expand_content))
            {
                $arm_expand_col_response = array('type'=>'success','data'=>$arm_expand_content);
            }

            echo wp_json_encode( $arm_expand_col_response );
            die;
            
        }

        function arm_get_transaction_expand_info_func()
        {
            global $ARMemberLite,$arm_global_settings,$wp,$wpdb;
            
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $posted_data  = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend'), $_POST ); //phpcs:ignore

            $log_id = isset( $posted_data['log_id'] ) ? absint( $posted_data['log_id'] ) : 0;
            $expand_visible_columns = isset($posted_data['visible_columns']) ? $posted_data['visible_columns'] : array();

            $arm_expand_col_response = array('type'=>'error','msg'=>esc_html__('Something went wrong','armember-membership'));

            $arm_current_member_columns = array('transaction_id','invoice_id','plan','payment_gateway','payment_type','transaction_status','amount','used_coupon_code','used_coupon_discount','payment_date','tax_percentage','tax_amount');

            $arm_expand_cols = array_diff($arm_current_member_columns, $expand_visible_columns);

            //get transaction Data via log id

            $transactionDetail = $wpdb->get_row($wpdb->prepare("SELECT * FROM $ARMemberLite->tbl_arm_payment_log WHERE arm_log_id=%d",$log_id));
            
            $transaction_id      = '';
            $invoice_id     = '';
            $plan = '';
            $payment_gateway         = '';
            $payment_type    = '';
            $transaction_status = '';
            $amount     = '-';
            $used_coupon_code = '';
            $used_coupon_discount = '';
            $payment_date = '';
            $tax_percentage = '';
            $tax_amount = '';

            $arm_expand_content = '<div class="arm_expand_grid_section">';
            foreach($arm_expand_cols as $expand_col_key){
                switch($expand_col_key){
                    case 'transaction_id':
                        $arm_expand_cols[$expand_col_key] = (!empty($transactionDetail->arm_transaction_id) && $transactionDetail->arm_transaction_id != '-' )  ? $transactionDetail->arm_transaction_id : esc_html__('Manual','armember-membership');
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Transaction ID','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($arm_expand_cols[$expand_col_key]).'</div></div>';
                    break;
                    case 'invoice_id':
                        $arm_expand_cols[$expand_col_key] = !empty($transactionDetail->arm_invoice_id) ? $transactionDetail->arm_invoice_id : '-';
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Invoice ID','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($arm_expand_cols[$expand_col_key]).'</div></div>';
                    break;
                    case 'plan':
                        $arm_expand_cols[$expand_col_key] = !empty($transactionDetail->arm_plan_id) ? $transactionDetail->arm_plan_id : '-';
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Membership plan','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($arm_expand_cols[$expand_col_key]).'</div></div>';
                    break;
                    case 'payment_gateway':
                        $arm_expand_cols[$expand_col_key] = !empty($transactionDetail->arm_payment_gateway) ? $transactionDetail->arm_payment_gateway : esc_html__('Manual','armember-membership');
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Payment Gateway','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($arm_expand_cols[$expand_col_key]).'</div></div>';
                    break;
                    case 'payment_type':
                        $arm_expand_cols[$expand_col_key] = !empty($transactionDetail->arm_payment_type) ? $transactionDetail->arm_payment_type : '-';
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Payment Type','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($arm_expand_cols[$expand_col_key]).'</div></div>';
                    break;
                    case 'transaction_status':
                        global $arm_transaction;
                        $arm_expand_cols[$expand_col_key] = !empty($transactionDetail->arm_transaction_status) ? $transactionDetail->arm_transaction_status : '-';
                        switch ( $transactionDetail->arm_transaction_status ) {
                            case '0':
                                $arm_txn_status = 'pending';
                                break;
                            case '1':
                                $arm_txn_status = 'success';
                                break;
                            case '2':
                                $arm_txn_status = 'canceled';
                                break;
                            default:
                                $arm_txn_status = $transactionDetail->arm_transaction_status;
                                break;
                        }
                        $arm_transaction_status = $arm_transaction->arm_get_transaction_status_text( $arm_txn_status );
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Transaction Status','armember-membership').'</div><div class="arm_expand_grid_col_val">'.$arm_transaction_status.'</div></div>';
                    break;
                    case 'amount':
                        $arm_expand_cols[$expand_col_key] = !empty($transactionDetail->arm_amount) ? $transactionDetail->arm_amount : '-';
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Amount','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($arm_expand_cols[$expand_col_key]).'</div></div>';
                    break;
                    case 'used_coupon_code':
                        $arm_expand_cols[$expand_col_key] = !empty($transactionDetail->arm_coupon_code) ? $transactionDetail->arm_coupon_code : '-';
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Used Coupon code','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($arm_expand_cols[$expand_col_key]).'</div></div>';
                    break;
                    case 'used_coupon_discount':
                        $arm_expand_cols[$expand_col_key] = !empty($transactionDetail->arm_coupon_discount) ? $transactionDetail->arm_coupon_discount : '-';
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Used Coupon discount','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($arm_expand_cols[$expand_col_key]).'</div></div>';
                    break;
                    case 'payment_date':
                        $arm_expand_cols[$expand_col_key] = !empty($transactionDetail->arm_payment_date) ? $transactionDetail->arm_payment_date : '-';
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Payment Date','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($arm_expand_cols[$expand_col_key]).'</div></div>';
                    break;
                    case 'tax_percentage':
                        $arm_expand_cols[$expand_col_key] = !empty($transactionDetail->tax_percentage) ? $transactionDetail->tax_percentage : '-';
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Tax Percentage','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($arm_expand_cols[$expand_col_key]).'</div></div>';
                    break;
                    case 'tax_amount':
                        $arm_expand_cols[$expand_col_key] = !empty($transactionDetail->tax_amount) ? $transactionDetail->tax_amount : '-';
                        $arm_expand_content .= '<div class="arm_expand_grid_col"><div class="arm_expand_grid_col_label">'.esc_html__('Tax Amount','armember-membership').'</div><div class="arm_expand_grid_col_val">'.esc_html($arm_expand_cols[$expand_col_key]).'</div></div>';
                    break;

                }
            }
            $arm_expand_content .= '</div>';
            if(!empty($arm_expand_content))
            {
                $arm_expand_col_response = array('type'=>'success','data'=>$arm_expand_content);
            }

            echo wp_json_encode( $arm_expand_col_response );
            die;
            
        }

        function arm_no_result_svg($arm_no_result_msg = ''){
            global $arm_global_settings;
            $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
            $general_settings   = $all_global_settings['general_settings'];
            $all_default_global_setting = $arm_global_settings->arm_default_global_settings();
            $arm_default_front_settings  = $all_default_global_setting['general_settings']['front_settings'];
            $arm_all_member_panel_settings = $arm_global_settings->arm_get_member_panel_settings();

            $arm_appearance_panel_settings = isset($arm_all_member_panel_settings['appearance_settings']) ? $arm_all_member_panel_settings['appearance_settings'] : array();
                        
            $arm_default_front_settings['color'] = isset($arm_default_front_settings['color']) ? $arm_default_front_settings['color'] : array();
            $arm_appearance_panel_settings['color'] = isset($arm_appearance_panel_settings['color']) ? $arm_appearance_panel_settings['color'] : array();
            
            $frontOptions   = isset($arm_appearance_panel_settings['color']) ? $arm_appearance_panel_settings['color'] : $arm_default_front_settings['color'];
            $arm_primary_color = !empty($frontOptions['primary_color']) ? $frontOptions['primary_color'] : $arm_default_front_settings['color']['primary_color'];
            $panel_background_color = !empty($frontOptions['panel_background_color']) ? $frontOptions['panel_background_color'] : $arm_default_front_settings['color']['panel_background_color'];

            $arm_no_ressult_svg = "<div class='arm_no_result_div' style='background:".$panel_background_color."'>
                <svg width='161' height='160' viewBox='0 0 221 220' fill='none' xmlns='http://www.w3.org/2000/svg'><rect class='arm-empty-data-svg-icon-background-color' width='160' height='160' transform='translate(0.5)' fill=''></rect><path class='arm-empty-data-svg-icon-color' d='M162.056 82C172.012 82 176.99 82.0004 180.793 83.9424C184.138 85.6506 186.858 88.3758 188.562 91.7285C190.5 95.54 190.5 100.53 190.5 110.509V151.491C190.5 161.47 190.5 166.46 188.562 170.271C186.859 173.624 184.138 176.35 180.793 178.058C176.99 180 172.012 180 162.056 180H58.9443C48.988 180 44.0099 180 40.207 178.058C36.8619 176.35 34.1419 173.624 32.4375 170.271C30.4998 166.46 30.5 161.47 30.5 151.491V110.509C30.5 100.53 30.4998 95.54 32.4375 91.7285C34.1419 88.3759 36.862 85.6506 40.207 83.9424C44.0099 82.0003 48.988 82 58.9443 82H92.5V97.5869C92.5001 108.227 101.348 117 112.172 117C118.478 117 124.407 113.92 128.172 108.88C130.619 105.52 131.843 101.32 131.843 97.2129C131.843 95.2532 130.243 93.6671 128.267 93.667C126.29 93.667 124.69 95.2531 124.689 97.2129C124.689 99.8262 123.843 102.627 122.337 104.773C119.984 108.04 116.125 110 112.172 110C105.301 110 99.6535 104.4 99.6533 97.5869V82H162.056Z' fill='".$arm_primary_color."' fill-opacity='0.2'></path><path class='arm-empty-data-svg-icon-color' d='M78.0537 56C85.3291 56 88.9673 56.0001 92.2383 57.3447C92.761 57.5596 93.2662 57.8048 93.7666 58.0869C92.9453 60.6203 92.5 63.3243 92.5 66.1338V82H58.9443C48.988 82 44.0099 82.0003 40.207 83.9424C36.862 85.6506 34.1419 88.3759 32.4375 91.7285C30.6 95.3428 30.5069 100.017 30.502 109H30.5V82.5C30.5 74.2688 30.4997 70.1529 31.8545 66.9062C33.6611 62.5775 37.1268 59.1378 41.4883 57.3447C44.7595 56 48.9065 56 57.2002 56H78.0537Z' fill='".$arm_primary_color."' fill-opacity='0.3'></path><path class='arm-empty-data-svg-icon-color' d='M162.055 82H144.266C137.678 82 132.69 75.9333 134.007 69.5867L136.831 48.2133C136.925 47.4667 137.584 47 138.149 47C138.525 47 138.807 47.0933 139.09 47.4667L146.243 55.4H159.985L166.95 47.3733C167.232 47.0933 167.608 46.9067 167.891 46.9067C168.456 46.9067 169.114 47.28 169.208 48.0267L172.314 69.7733C173.538 76.12 168.55 82 162.055 82ZM149.161 74.16C146.431 73.5067 142.102 73.5067 139.278 74.16C138.807 74.2533 138.525 74.72 138.619 75.1867C138.713 75.6533 139.09 75.9333 139.56 75.9333C139.655 75.9333 139.749 75.9333 139.749 75.9333C142.196 75.3733 146.243 75.3733 148.69 75.9333C149.161 76.0267 149.725 75.7467 149.82 75.28C149.914 74.8133 149.631 74.2533 149.161 74.16ZM165.632 74.16C162.902 73.5067 158.573 73.5067 155.749 74.16C155.279 74.2533 154.996 74.72 155.09 75.1867C155.184 75.6533 155.561 75.9333 156.032 75.9333C156.126 75.9333 156.22 75.9333 156.22 75.9333C158.667 75.3733 162.714 75.3733 165.161 75.9333C165.632 76.0267 166.197 75.7467 166.291 75.28C166.385 74.8133 166.103 74.2533 165.632 74.16ZM92.5 66.1333V78.5467V82V97.5867C92.5 108.227 101.347 117 112.171 117C118.477 117 124.407 113.92 128.172 108.88C130.619 105.52 131.843 101.32 131.843 97.2133C131.843 95.2533 130.242 93.6667 128.266 93.6667C126.289 93.6667 124.689 95.2533 124.689 97.2133C124.689 99.8267 123.842 102.627 122.336 104.773C119.983 108.04 116.124 110 112.171 110C105.3 110 99.6532 104.4 99.6532 97.5867V82H137.678C136.549 81.3467 135.513 80.4133 134.666 79.3867C132.313 76.4933 131.372 72.8533 132.125 69.2133L134.948 47.84C135.043 47.1867 135.419 46.6267 135.796 46.16C131.184 42.3333 125.254 40 118.76 40C104.265 40 92.5 51.6667 92.5 66.1333Z' fill='".$arm_primary_color."' fill-opacity='0.8'></path><path class='arm-empty-data-svg-icon-color' d='M187.999 31.0315V29.1704L195.057 18.9477V18.8598H188.636V16H199.389V17.9973L192.485 28.0835V28.1717H199.5V31.0315H187.999ZM178.956 36.0161V34.8119L183.523 28.1972V28.1403H179.368V26.2898H186.326V27.5822L181.858 34.1085V34.1657H186.398V36.0161H178.956ZM171.5 40V39.1242L174.822 34.3135V34.2721H171.8V32.9263H176.86V33.8663L173.611 38.6127V38.6542H176.912V40H171.5Z' fill='".$arm_primary_color."' fill-opacity='0.8'></path></svg>" .$arm_no_result_msg . "</div>";
            
            return $arm_no_ressult_svg;
        }

        function hexToRgba($hex, $opacity = 1) {
            // Remove the '#' if present
            $hex = ltrim($hex, '#');
        
            // Handle shorthand hex codes (e.g., #fff)
            if (strlen($hex) == 3) {
                $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
                $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
                $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
            } 
            // Handle full hex codes (e.g., #ffffff)
            elseif (strlen($hex) == 6) {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
            } 
            // Return false for invalid hex codes
            else {
                return false;
            }
        
            // Ensure opacity is between 0 and 1
            $opacity = max(0, min(1, $opacity));
        
            // Return the RGBA color string
            return "rgba($r, $g, $b, $opacity)";
        }

    }
    global $arm_common_lite;
    $arm_common_lite = new ARM_common_lite();
}
