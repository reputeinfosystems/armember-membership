<?php
if (!class_exists('ARM_lite_oxygen_builder_restriction')) {
	class ARM_lite_oxygen_builder_restriction
	{ 
        var $isOxygenBuilderRestrictionFeature;

        function __construct()
        {
            $is_oxygen_builder_restriction_feature = get_option('arm_is_oxygen_builder_restriction_feature');
            $this->isOxygenBuilderRestrictionFeature = ($is_oxygen_builder_restriction_feature == '1') ? true : false;
            if ($this->isOxygenBuilderRestrictionFeature) {
                if (is_plugin_active('oxygen/plugin.php')) {
                    add_action('breakdance_register_template_types_and_conditions',array($this, 'arm_breakdance_builder_restrictions'));
                } else if (is_plugin_active('oxygen/functions.php')) {
                    add_action('init',array($this, 'arm_oxygen_builder_restrictions'));
                }
            }

            if (is_plugin_active('oxygen/plugin.php')) {
                add_filter('breakdance_singular_content', array( $this,'arm_oxygen_builder_restriction_content_after'));
            }
        }

        function arm_oxygen_builder_restriction_content_after($content) {
            global $arm_pay_per_post_feature,$ARMember;
            $ARMember->set_front_css(true);
            $arm_is_allowed_content = 1;
            $arm_is_allowed_content = apply_filters('arm_paid_post_check_content_access_external', $arm_is_allowed_content);
            if($arm_pay_per_post_feature->isPayPerPostFeature) {
                if (!current_user_can('administrator') && is_singular() && $arm_is_allowed_content)
                {
                    $current_post_id = get_the_ID();
                    if(!empty($current_post_id))
                    {
                        $arm_is_paid_post = get_post_meta($current_post_id, 'arm_is_paid_post', true);
                        if(!empty($arm_is_paid_post))
                        {
                            $plan_id = $arm_pay_per_post_feature->arm_get_plan_from_post_id( $current_post_id );
                            if( !empty( $plan_id ) )
                            {
                                $hasaccess = false;
                                $isLoggedIn = is_user_logged_in();
                                if($isLoggedIn)
                                {
                                    $current_user_id = get_current_user_id();
                                    $arm_user_plan = arm_get_user_meta($current_user_id, 'arm_user_plan_ids', true);
                                    $arm_user_plan = !empty($arm_user_plan) ? $arm_user_plan : array();
                                    if(!empty($arm_user_plan)){
                                        $suspended_plan_ids = arm_get_user_meta($current_user_id, 'arm_user_suspended_plan_ids', true);
                                        if( ! empty($suspended_plan_ids)) {
                                            foreach ($suspended_plan_ids as $suspended_plan_id) {
                                                if(in_array($suspended_plan_id, $arm_user_plan)) {
                                                    unset($arm_user_plan[array_search($suspended_plan_id, $arm_user_plan)]);
                                                }
                                            }
                                        }

                                        if(in_array($plan_id, $arm_user_plan))
                                        {
                                            $hasaccess = true;
                                        }
                                    }
                                }

                                if($hasaccess==false)
                                {

                                    $arm_enable_paid_post_alternate_content = get_post_meta($current_post_id, 'arm_enable_paid_post_alternate_content', true);
                                    if(!empty($arm_enable_paid_post_alternate_content))
                                    {
                                        $arm_paid_post_alternative_content = get_post_meta($current_post_id, 'arm_paid_post_alternative_content', true);
                                        
                                        $arm_paid_post_alternative_content = apply_filters('arm_modified_paid_post_alternative_content_externally', $arm_paid_post_alternative_content,$current_post_id);
                                        
                                        $content = do_shortcode( $arm_paid_post_alternative_content );
                                        
                                    }
                                    else {
                                        global $arm_global_settings;
                                        $arm_global_settings_general_settings = !empty($arm_global_settings->global_settings['arm_pay_per_post_default_content']) ? stripslashes($arm_global_settings->global_settings['arm_pay_per_post_default_content']) : esc_html__('Content is Restricted. Buy this post to get access to full content.', 'ARMember');
                                        
                                        $arm_global_settings_general_settings = apply_filters('arm_modified_paid_post_settings_alternative_content_externally', $arm_global_settings_general_settings);

                                        $content = do_shortcode( $arm_global_settings_general_settings );                                       
                                        
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $content;
        }

        public function arm_breakdance_builder_restrictions() {
            \Breakdance\Themeless\registerCondition(
                [
                    'supports' => ['element_display'],
                    'availableForType' => ['ALL'],
                    'slug' => 'armember-restriction',
                    'label' => __('ARMember Restriction', 'armember-membership'),
                    'category' => __('ARMember', 'armember-membership'),
                    'operands' => [OPERAND_ONE_OF, OPERAND_NONE_OF],
                    'values' => function () {
                        global $arm_subscription_plans;
                        $arm_membership_plan = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
                        
                        $items = [
                            ['text' => __('Any Plan', 'armember-membership'), 'value' => 'any_plan'],
                            ['text' => __('Non Logged-In Users', 'armember-membership'), 'value' => 'unregistered'],
                            ['text' => __('Logged-In Users', 'armember-membership'), 'value' => 'registered'],
                        ];

                        if (!empty($arm_membership_plan)) {
                            foreach ($arm_membership_plan as $plan) {
                                $items[] = [
                                    'text' => $plan['arm_subscription_plan_name'],
                                    'value' => (string)$plan['arm_subscription_plan_id']
                                ];
                            }
                        }

                        return [
                            [
                                'label' => __('ARMember Restrictions', 'armember-membership'),
                                'items' => $items
                            ]
                        ];
                    },
                    'callback' => function ($operand, $values): bool {
                        if (!$values) {
                            return false;
                        }

                        if (current_user_can('administrator')) {
                            return true;
                        }

                        $arm_restriction_type = ($operand === OPERAND_ONE_OF) ? 'show' : 'hide';

                        global $arm_restriction;
                        return $arm_restriction->arm_check_content_hasaccess($values, $arm_restriction_type);
                    },
                    'templatePreviewableItems' => false,
                ]
            );
        }  
        
        public function arm_oxygen_builder_restrictions() {
            if( function_exists( 'oxygen_vsb_register_condition' ) ) {
                global $arm_subscription_plans;
                $arm_membership_plan = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
                $plan_options[] = array();
                $plan_options = array(
                    'any_plan' => '[any_plan] '.esc_html__( 'Any Plan', 'armember-membership' ),
                    'unregistered' => '[unregistered] '.esc_html__( 'Non Logged-In Users', 'armember-membership' ),
                    'registered' => '[registered] '.esc_html__( 'Logged-In Users', 'armember-membership' )
                );
                foreach ( $arm_membership_plan as $plan ) {
                    $plan_options[ $plan['arm_subscription_plan_id'] ] = '['.$plan['arm_subscription_plan_id'].'] '.$plan['arm_subscription_plan_name'];
                }
                oxygen_vsb_register_condition( 
                    esc_html__( 'ARMember Restriction', 'armember-membership' ), 
                    array( 'options' => $plan_options ), 
                    array('show', 'hide' ),
                    'arm_lite_oxygen_builder_condition_callback', 
                    'ARMember'
                );
            }
        }    
    }
}
global $arm_lite_oxygen_builder_restriction;
$arm_lite_oxygen_builder_restriction = new ARM_lite_oxygen_builder_restriction();

function arm_lite_oxygen_builder_condition_callback( $value, $operator ) {

    preg_match_all("/([^[]+(?=]))/", $value, $matches); 

    if (current_user_can('administrator')) {
        return true;
    }
    
    $arm_membership_plans = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : array();
    $arm_restriction_type = isset($operator) && !empty($operator) ? $operator : '';

    global $arm_restriction;
    $hasaccess = $arm_restriction->arm_check_content_hasaccess( $arm_membership_plans, $arm_restriction_type );

    return $hasaccess;
}