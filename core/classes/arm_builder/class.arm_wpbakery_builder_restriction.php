<?php
if (!class_exists('ARM_lite_wpbakery_builder_restriction')) {
	class ARM_lite_wpbakery_builder_restriction
	{
        var $isWPBakryBuilderRestrictionFeature;
		function __construct()
		{
            $is_wpbakery_builder_restriction_feature = get_option('arm_is_wpbakery_page_builder_restriction_feature');
            $this->isWPBakryBuilderRestrictionFeature = ($is_wpbakery_builder_restriction_feature == '1') ? true : false;
            if ($this->isWPBakryBuilderRestrictionFeature) {
                add_filter( 'vc_add_element_categories', array( $this, 'arm_restriction_tabs' ) );
                add_action( 'init', array( $this, 'arm_restriction_settings' ), 101 );
                add_filter( 'vc_shortcode_output', array( $this, 'arm_restriction_shortcode' ), 20, 4 );
            }
		}

        /**
         * Adds a new tab to the Visual Composer editor
         *
         * @param  array  $tabs   Tabs
         *
         * @return array
         */
        public function arm_restriction_tabs( $tabs ) {
            $tabs[] = array(
                'name' => 'ARMember Restriction',
                'filter' => 'ARMember_Restriction',
                'active' => false
            );
            return $tabs;
        }
        
        public function ARM_init_all_shortcode() {
            if ( function_exists( 'vc_add_shortcode_param' ) ) {
                vc_add_shortcode_param( 'dropdown_multi', 'dropdown_multi_settings_field' );
            }
        }
        
        /**
         * Add new settings to VC row
         *
         * @return void
         */
        public function arm_restriction_settings() {
            global $arm_subscription_plans;
            
            if (!$this->isWPBakryBuilderRestrictionFeature) {
                return;
            }

            // Make sure WPBakery is active            
            if(!class_exists('WPBMap')){
                return;
            }
            
            if ( ! function_exists( 'vc_add_param' ) ) {
                return;
            }
            
            $elements = WPBMap::getAllShortCodes();
            $shortcodes_to_add_options = array();
            $shortcodes_to_add_options = array_keys($elements);
            $shortcodes_to_add_options_ = array(
                'vc_row' => 'vc_row',
                'vc_column' => 'vc_column',
            );
            $shortcodes_to_add_options = array_merge($shortcodes_to_add_options, $shortcodes_to_add_options_);

            $shortcodes_to_add_options = apply_filters('armember_shortcodes_to_add_options', $shortcodes_to_add_options);

            $restriction_content_access = array(
                esc_html__( 'No', 'armember-membership' ) => 'no',
                esc_html__( 'Yes', 'armember-membership' ) => 'yes'
            );

            $content_access_type = array(
                esc_html__( 'Show', 'armember-membership' ) => 'show',
                esc_html__( 'Hide', 'armember-membership' ) => 'hide'
            );
            
            $arm_membership_plan = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
            $plan_options = array();
            $plan_options = array(
                '&nbsp;'.esc_html__( 'Any Plan', 'armember-membership' ) => 'any_plan',
                '&nbsp;'.esc_html__( 'Non Loggedin Users', 'armember-membership' ) => 'unregistered',
                '&nbsp;'.esc_html__( 'Loggedin Users', 'armember-membership' ) => 'registered'
            );
            foreach ( $arm_membership_plan as $plan ) {
                $plan_options[ '&nbsp;'.$plan['arm_subscription_plan_name'] ] = $plan['arm_subscription_plan_id'];
            }

            foreach ($shortcodes_to_add_options as $shortcode){
                $non_restricted_shortcode = array('woocommerce_cart', 'woocommerce_checkout', 'woocommerce_order_tracking');
                
                if(!in_array($shortcode, $non_restricted_shortcode)) {
                    $arm_restriction_attributes = array(
                        array(
                            'type' => 'dropdown',
                            'class' => 'arm_element_dropdown',
                            'heading'  => esc_html__( 'Enable Content Restriction', 'armember-membership' ),
                            'description' => esc_html__( 'Enable this option to apply access or restriction..', 'armember-membership' ),
                            'param_name' => 'armember_restriction_access',
                            'default' => 'no',
                            'value' => $restriction_content_access,
                            'admin_enqueue_css' => array(MEMBERSHIPLITE_URL . '/core/vc/arm_vc.css'),
                            'front_enqueue_css' => MEMBERSHIPLITE_URL . '/core/vc/arm_vc.css',
                            'group' => 'ARMember Restriction',
                        ),
                        array(
                            'type' => 'dropdown',
                            'class' => 'arm_element_dropdown',
                            'heading'  => esc_html__( 'Content Restriction Type', 'armember-membership' ),
                            'description' => esc_html__( 'Select Content Restriction Type.', 'armember-membership' ),
                            'param_name' => 'armember_access_type',
                            'default' => 'show',
                            'value' => $content_access_type,
                            'admin_enqueue_css' => array(MEMBERSHIPLITE_URL . '/core/vc/arm_vc.css'),
                            'front_enqueue_css' => MEMBERSHIPLITE_URL . '/core/vc/arm_vc.css',
                            'group' => 'ARMember Restriction',
                            'dependency' => array(
                                'element' => 'armember_restriction_access',
                                'value' => 'yes',
                            ),
                        ),
                        array(
                            'type' => 'checkbox',
                            'class' => 'arm_membership_plans_checkbox',
                            'heading'  => esc_html__( 'Membership Plans', 'armember-membership' ),
                            'description' => esc_html__( 'Select Membership Plan(s).', 'armember-membership' ),
                            'param_name' => 'armember_membership_plans',
                            'default' => '',
                            'value' => $plan_options,
                            'admin_enqueue_css' => array(MEMBERSHIPLITE_URL . '/core/vc/arm_vc.css'),
                            'front_enqueue_css' => MEMBERSHIPLITE_URL . '/core/vc/arm_vc.css',
                            'group' => 'ARMember Restriction',
                            'dependency' => array(
                                'element' => 'armember_restriction_access',
                                'value' => 'yes',
                            ),
                        ),
                    );

                    vc_add_params($shortcode, $arm_restriction_attributes);
                }
                
            }
        }

        /**
         * Modify the content of the shortcode
         *
         * @param  string   $output       Shortcode content
         * @param  object   $shortcode    Module shortcode
         * @param  array    $attrs        Shortcode attributes
         * @param  string   $tag          Shortcode tag/ID
         *
         * @return string
         */
        public function arm_restriction_shortcode( $output, $shortcode, $prepared_atts, $tag ) {

            if (!$this->isWPBakryBuilderRestrictionFeature) {
                return $output;
            }
            
            if (current_user_can('administrator')) {
                return $output;
            }

            if(isset($prepared_atts['armember_restriction_access']) && $prepared_atts['armember_restriction_access'] == 'no') {
                return $output;
            }
            
            $arm_membership_plans = isset($prepared_atts['armember_membership_plans']) && !empty($prepared_atts['armember_membership_plans']) ? explode(",", $prepared_atts['armember_membership_plans']) : array();
            $arm_restriction_type = isset($prepared_atts['armember_access_type']) && !empty($prepared_atts['armember_access_type']) ? $prepared_atts['armember_access_type'] : '';

            global $arm_restriction;
            $hasaccess = $arm_restriction->arm_check_content_hasaccess( $arm_membership_plans, $arm_restriction_type );

            if($hasaccess){
                return $output;
            } else {
                return '';
            }
        }
	}
}
global $arm_lite_wpbakery_builder_restriction;
$arm_lite_wpbakery_builder_restriction = new ARM_lite_wpbakery_builder_restriction();
