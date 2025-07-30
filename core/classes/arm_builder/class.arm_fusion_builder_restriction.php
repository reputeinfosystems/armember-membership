<?php
if (!class_exists('ARM_lite_fusion_builder_restriction')) {
	class ARM_lite_fusion_builder_restriction
	{ 
        var $isFusionBuilderRestrictionFeature;

		function __construct()
		{
            $is_fusion_builder_restriction_feature = get_option('arm_is_fusion_builder_restriction_feature');
            $this->isFusionBuilderRestrictionFeature = ($is_fusion_builder_restriction_feature == '1') ? true : false;
            if ($this->isFusionBuilderRestrictionFeature) {                
                add_action( 'init' , array( $this, 'arm_filter_element' ) );
                add_action( 'wp_enqueue_scripts' , array( $this, 'arm_enqueue_style' ), 999 );
                add_action( 'fusion_builder_before_init' , array( $this, 'arm_filter_element' ) );
                add_filter( 'fusion_builder_element_params', array( $this, 'filter_available_element' ), 10, 4 );
                add_filter( 'fusion_builder_icon_map', array( $this, 'arm_fusiona_armember') );
            }
		}

        public function arm_fusiona_armember( $args ) {
            $args = array_merge( $args, array( 'armember-restriction' => 'fusiona-armember-restriction',) );
            return $args;
        }

        public function arm_enqueue_style() {
            global $arm_lite_version;
            wp_enqueue_style( 'arm_fusion_builder_style', MEMBERSHIPLITE_URL.'/css/arm_fusion_style.css',array(), $arm_lite_version, 'all');
        }

        public function arm_filter_element() {
            $fusion_builder_elements_list = array('container','row','row_inner','column','column_inner','builder_blank_page','builder_inline','builder_next_page','builder_row_inner','builder_row');
            foreach ( $fusion_builder_elements_list as $value ) {
                add_filter( 'fusion_element_'.$value.'_content', array( $this, 'arm_render_restricted_content' ),11, 2 );
            }
        }

        /**
        * Filter already set maps, add in a new option to container, column and elements.
        */
        public function filter_available_element( $params, $shortcode  ) {
            
            $restricted_element = array('fusion_builder_container','fusion_builder_column');

            if (in_array($shortcode, $restricted_element)) {
                global $arm_subscription_plans;
                $arm_membership_plan = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
                $plan_options[] = array();
                $plan_options = array(
                    'any_plan' => esc_attr__( 'Any Plan', 'armember-membership' ),
                    'unregistered' => esc_attr__( 'Non Loggedin Users', 'armember-membership' ),
                    'registered' => esc_attr__( 'Loggedin Users', 'armember-membership' )
                );
                foreach ( $arm_membership_plan as $plan ) {
                    $plan_options[ $plan['arm_subscription_plan_id'] ] = $plan['arm_subscription_plan_name'];
                }

                $arm_fusion_builder_elements_perams = array(
                    array(
                        'type'        => 'radio_button_set',
                        'heading'     => esc_attr__( 'Enable Content Restriction', 'armember-membership' ),
                        'description' => esc_attr__( 'Enable this option to apply access or restriction..', 'armember-membership' ),
                        'param_name'  => 'armember_restriction_access',
                        'value'       => array(
                            'yes' => esc_attr__( 'Yes', 'armember-membership' ),
                            'no'  => esc_attr__( 'No', 'armember-membership' ),
                        ),
                        'default'     => 'no',
                        'group'       => esc_attr__( 'ARMember-Restriction', 'armember-membership' ),
                    ),
                    array(
                        'type'        => 'radio_button_set',
                        'heading'     => esc_attr__( 'Content Restriction Type', 'armember-membership' ),
                        'description' => esc_attr__( 'Select Content Restriction Type.', 'armember-membership' ),
                        'param_name'  => 'armember_access_type',
                        'default'     => 'show',
                        'group'       => esc_attr__( 'ARMember-Restriction', 'armember-membership' ),
                        'dependency'  => array(
                            array(
                                'element'   => 'armember_restriction_access',
                                'value'     => 'yes',
                                'operator'  => '==',
                            ),
                        ),
                        'value'       => [
                            'show' => esc_attr__( 'Show', 'armember-membership' ),
                            'hide'  => esc_attr__( 'Hide', 'armember-membership' ),
                        ],
                    ),
                    array(
                        'type'        => 'multiple_select',
                        'heading'     => esc_attr__( 'Membership Plans', 'armember-membership' ),
                        'description' => esc_attr__( 'If "Restriction Type" set to "Show" then, the selected Membership Plan(s) will display the content if the condition is true, and if set "Hide" then content will be hidden for the selected "Membership Plan(s)" setting.', 'armember-membership' ),
                        'param_name'  => 'armember_membership_plans',
                        'group'       => esc_attr__( 'ARMember-Restriction', 'armember-membership' ),
                        'dependency'  => array(
                            array(
                                'element'   => 'armember_restriction_access',
                                'value'     => 'yes',
                                'operator'  => '==',
                            ),
                        ),
                        'value'       => $plan_options,
                    )
                );
                $params = array_merge($params, $arm_fusion_builder_elements_perams);
                return $params;
            }
            return $params;
        }

        public function arm_render_restricted_content( $html, $args ) {

            if (!$this->isFusionBuilderRestrictionFeature) {
                return $html;
            }

            if (current_user_can('administrator')) {
                return $html;
            }

            if(isset($args['armember_restriction_access']) && $args['armember_restriction_access'] == 'no') {
                return $html;
            }
            
            $arm_membership_plans = isset($args['armember_membership_plans']) && !empty($args['armember_membership_plans']) ? explode(",", $args['armember_membership_plans']) : array();
            $arm_restriction_type = isset($args['armember_access_type']) && !empty($args['armember_access_type']) ? $args['armember_access_type'] : '';

            global $arm_restriction;
            $hasaccess = $arm_restriction->arm_check_content_hasaccess( $arm_membership_plans, $arm_restriction_type );

            if($hasaccess){
                return $html;
            } else {
                return '';
            }
        }
	}
}
global $arm_lite_fusion_builder_restriction;
$arm_lite_fusion_builder_restriction = new ARM_lite_fusion_builder_restriction();
