<?php
if (!class_exists('ARM_lite_siteorigin_builder_restriction')) {
	class ARM_lite_siteorigin_builder_restriction
	{ 
        var $isSiteOriginBuilderRestrictionFeature;

		function __construct()
		{
            $is_siteorigin_builder_restriction_feature = get_option('arm_is_siteorigin_builder_restriction_feature');
            $this->isSiteOriginBuilderRestrictionFeature = ($is_siteorigin_builder_restriction_feature == '1') ? true : false;
            if ($this->isSiteOriginBuilderRestrictionFeature) {
                add_filter( 'siteorigin_panels_row_style_groups', array( $this, 'arm_add_siteorigin_tabs_group' ), 10, 3 );
                add_filter( 'siteorigin_panels_cell_style_groups', array( $this, 'arm_add_siteorigin_tabs_group' ), 10, 3 );
                add_filter( 'siteorigin_panels_row_style_fields', array( $this, 'arm_add_siteorigin_fields' ), 10 );
                add_filter( 'siteorigin_panels_cell_style_fields', array( $this, 'arm_add_siteorigin_fields' ), 10 );
                add_filter( 'siteorigin_panels_layout_data', array( $this, 'arm_restrict_siteorigin_panels_layout_data' ), 10, 2 );
            }

		}

        public function arm_add_siteorigin_tabs_group( $groups, $post_id, $args ) {
            $arm_siteorigin_group = array(                
                'armember_restriction'     => array(
                    'name'     => esc_html__( 'ARMember Restriction', 'armember-membership' ),
                    'priority' => 20,
                ),
            );
            $groups = array_merge($groups, $arm_siteorigin_group);
            return $groups;
        }
        
        public function arm_add_siteorigin_fields( $fields ) {
            global $arm_subscription_plans;
            $arm_membership_plan = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
            $plan_options[] = array();
            $plan_options = array(
                'any_plan' => esc_html__( 'Any Plan', 'armember-membership' ),
                'unregistered' => esc_html__( 'Non Loggedin Users', 'armember-membership' ),
                'registered' => esc_html__( 'Loggedin Users', 'armember-membership' )
            );
            if(!empty($arm_membership_plan)) {

                foreach ( $arm_membership_plan as $plan ) {
                    $plan_options[ $plan['arm_subscription_plan_id'] ] = $plan['arm_subscription_plan_name'];
                }
            }

            $arm_field_priority = 2;
            $arm_membership_field = array();
            $arm_membership_field['type'] =  array(
                'name'        => esc_attr__( 'Content Restriction Type', 'armember-membership' ),
                'type'        => 'select',                
                'description' => esc_attr__( 'Select Content Restriction Type.', 'armember-membership' ),
                'group'       => 'armember_restriction',
                'options'     => array(
                    'show'    => esc_html__( 'Show', 'armember-membership' ),
                    'hide'   => esc_html__( 'Hide', 'armember-membership' ),
                ),
                'priority'    => 11,
            );
            foreach ($plan_options as $p_key => $p_value) {
                $arm_field_name = 'plans_'.$p_key;
                $arm_membership_field[$arm_field_name] = array(
                        'label'       => $p_value,
                        'type'        => 'checkbox',
                        'value'       => $p_value,
                        'priority'    => $arm_field_priority,
                    );

                    if ( array_key_first( $plan_options ) == $p_key ) {
                        $arm_membership_field[$arm_field_name]['name'] = esc_attr('Membership plans','armember-membership' );
                    }
                    if ( array_key_last( $plan_options ) == $p_key ) {
                        $arm_membership_field[$arm_field_name]['description'] = esc_attr__( 'If you select "Restriction Type" to "Show" then, the selected Membership Plan(s) will display the content if the condition is true, and select "Hide" then content will be restricted for the selected "Membership Plan(s)".', 'armember-membership' );
                    }
                $arm_field_priority++;
            }

            $arm_siteorigin_fields = array();
            $arm_siteorigin_fields['armember_restriction_access'] = array(
                'name'        => esc_attr__( 'Enable this option to apply access or restriction.', 'armember-membership' ),
                'type'        => 'toggle',
                'group'       => 'armember_restriction',
                'priority'    => 10,
                'fields' => $arm_membership_field,
            );
            $fields = array_merge($fields, $arm_siteorigin_fields);
            return $fields;
        }
        
        public function arm_restrict_siteorigin_panels_layout_data( $layout_data, $post_id ) {
            if (!$this->isSiteOriginBuilderRestrictionFeature) {
                return $layout_data;
            }

            if (current_user_can('administrator')) {
                return $layout_data;
            }
            
            if(isset($layout_data) && !empty($layout_data)) {
                foreach ($layout_data as $l_key => $l_value) {
                    $row_hasaccess = $this->arm_check_hasaccess($l_value['style']);
                    if($row_hasaccess) {
                        if(isset($l_value) && !empty($l_value)) {
                            foreach ($l_value['cells'] as $lc_key => $lc_value) {
                                if(!empty($lc_value['style'])) {
                                    $cells_hasaccess = $this->arm_check_hasaccess($lc_value['style']);
                                } else {
                                    $cells_hasaccess = true;
                                }
                                if (!$cells_hasaccess) {
                                    unset($layout_data[$l_key]['cells'][$lc_key]);
                                }
                            }
                        }
                    } else {
                        unset($layout_data[$l_key]);
                    }
                }
            }
            return $layout_data;
        }

        public function arm_check_hasaccess( $args ) {
            if(isset($args['armember_restriction_access']) && $args['armember_restriction_access'] != '1') {
                return true;
            }

            global $arm_subscription_plans;
            $arm_membership_plan = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
            $plan_options[] = array();
            $plan_options = array(
                'any_plan' => esc_html__( 'Any Plan', 'armember-membership' ),
                'unregistered' => esc_html__( 'Non Loggedin Users', 'armember-membership' ),
                'registered' => esc_html__( 'Loggedin', 'armember-membership' )
            );
            if(!empty($arm_membership_plan)) {
                foreach ( $arm_membership_plan as $plan ) {
                    $plan_options[ $plan['arm_subscription_plan_id'] ] = $plan['arm_subscription_plan_name'];
                }
            }

            foreach ($plan_options as $p_key => $p_value) {
                $plan_key = 'armember_restriction_access_plans_'.$p_key;
                if(isset($args[$plan_key]) && $args[$plan_key] != '1') {
                    unset($plan_options[$p_key]);
                }
            }
            $restricted_plans = array_keys($plan_options);
            
            $arm_membership_plans = isset($restricted_plans) && !empty($restricted_plans) ? $restricted_plans : array();
            $arm_restriction_type = isset($args['armember_restriction_access_type']) && !empty($args['armember_restriction_access_type']) ? $args['armember_restriction_access_type'] : '';

            global $arm_restriction;
            $hasaccess = $arm_restriction->arm_check_content_hasaccess( $arm_membership_plans, $arm_restriction_type );
            return $hasaccess;
        }
    }
        
}
global $arm_lite_siteorigin_builder_restriction;
$arm_lite_siteorigin_builder_restriction = new ARM_lite_siteorigin_builder_restriction();