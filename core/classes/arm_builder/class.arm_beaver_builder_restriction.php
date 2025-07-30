<?php
if (!class_exists('ARM_lite_beaver_builder_restiction')) {
	class ARM_lite_beaver_builder_restiction
	{
        var $isBeaverBuilderRestrictionFeature;
		function __construct()
		{
            $is_beaver_builder_restriction_feature = get_option('arm_is_beaver_builder_restriction_feature');
            $this->isBeaverBuilderRestrictionFeature = ($is_beaver_builder_restriction_feature == '1') ? true : false;
            if ($this->isBeaverBuilderRestrictionFeature) {
                add_filter( 'fl_builder_register_settings_form', array($this, 'armember_beaver_builder_settings_form'), 10, 2 );
                add_filter( 'fl_builder_is_node_visible', array($this, 'armember_beaver_builder_check_field_connections'), 200, 2 );
                add_filter( 'fl_builder_register_settings_form', array($this, 'armember_beaver_builder_add_custom_tab_all_modules'), 10, 2 );
            }
		}

        /**
         * Add ARMember to row settings.
         *
         * @param array  $form Row form settings.
         * @param string $id The node/row ID.
         *
         * @return array Updated form settings.
         */
        function armember_beaver_builder_settings_form( $form, $id ) {
            global $arm_subscription_plans;
            if (!$this->isBeaverBuilderRestrictionFeature) {
                return $form;
            }
            if ( 'row' !== $id ) {		
                return $form;
            }
            $arm_membership_plan = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
            $plans = array();
            $plans['any_plan'] = esc_html__( 'Any Plan', 'armember-membership' );
            $plans['unregistered'] = esc_html__( 'Non Loggedin Users', 'armember-membership' );
            $plans['registered'] = esc_html__( 'Loggedin Users', 'armember-membership' );
            if(!empty($arm_membership_plan)) {
                foreach ( $arm_membership_plan as $plan ) {
                    $plans[ $plan['arm_subscription_plan_id'] ] = $plan['arm_subscription_plan_name'];
                }
            }

            $row_settings_armember = array(
                'title'    => esc_html__( 'ARMember', 'armember-membership' ),
                'sections' => array(
                    'ARMember' => array(
                        'title'  => esc_html__( 'ARMember Restriction Settings', 'armember-membership' ),
                        'fields' => array(
                            'armember_enable'      => array(
                                'type'    => 'select',
                                'label'   => esc_html__( 'Enable this option to apply access or restriction.', 'armember-membership' ),
                                'options' => array(
                                    'yes' => esc_html__( 'Yes', 'armember-membership' ),
                                    'no'  => esc_html__( 'No', 'armember-membership' ),
                                ),
                                'default' => 'no',
                                'toggle'  => array(
                                    'yes' => array(
                                        'fields' => array(
                                            'armember_access_type',
                                            'armember_memberships',
                                        ),
                                    ),
                                ),
                            ),
                            'armember_access_type' => array(
                                'label'        => esc_html__( 'Select content access type', 'armember-membership' ),
                                'type'         => 'select',
                                'options'      => array(
                                    'show' => esc_html__( 'Show', 'armember-membership' ),
                                    'hide'  => esc_html__( 'Hide', 'armember-membership' ),
                                ),
                            ),
                            'armember_memberships' => array(
                                'label'        => esc_html__( 'Select a Membership Plan for content access or restriction', 'armember-membership' ),
                                'type'         => 'select',
                                'options'      => $plans,
                                'multi-select' => true,
                            ),
                        ),
                    ),
                ),
            );
            $form['tabs'] = array_merge(
                array_slice( $form['tabs'], 0, 2 ),
                array( 'ARMember' => $row_settings_armember ),
                array_slice( $form['tabs'], 2 )
            );
            return $form;
        }

        /**
         * Determine if the node (row/module) should be visible based on membership plan.
         *
         * @param bool   $is_visible Whether the module/row is visible.
         * @param object $node The node type.
         *
         * @return bool True if visible, false if not.
         */
        function armember_beaver_builder_check_field_connections( $is_visible, $node ) {
            global $arm_restriction;
            if ( 'row' === $node->type ) {
                if ( isset( $node->settings->armember_enable ) && 'yes' === $node->settings->armember_enable ) {
                    if ( $arm_restriction->arm_check_content_hasaccess( $node->settings->armember_memberships, $node->settings->armember_access_type )) {
                    	return $is_visible;
                    } else {
                    	return false;
                    }
                }
            }
            if ( isset( $node->settings->armember_enable ) && 'yes' === $node->settings->armember_enable ) {
                if ( $arm_restriction->arm_check_content_hasaccess( $node->settings->armember_memberships, $node->settings->armember_access_type )) {
                	return $is_visible;
                } else {
                	return false;
                }
            }
            return $is_visible;
        }

        /**
         * Add ARMember to all modules in Beaver Builder
         *
         * @param array  $form The form to add a custom tab for.
         * @param string $slug The module slug.
         *
         * @return array The updated form array.
         */
        function armember_beaver_builder_add_custom_tab_all_modules( $form, $slug ) {
            global $arm_subscription_plans;
            if (!$this->isBeaverBuilderRestrictionFeature) {
                return $form;
            }
            $modules = FLBuilderModel::get_enabled_modules(); // * getting all active modules slug
            if ( in_array( $slug, $modules, true ) ) {
                $arm_membership_plan = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
                $plans = array();
                $plans['any_plan'] = esc_html__( 'Any Plan', 'armember-membership' );
                $plans['unregistered'] = esc_html__( 'Non Loggedin Users', 'armember-membership' );
                $plans['registered'] = esc_html__( 'Loggedin Users', 'armember-membership' );
                if(!empty($arm_membership_plan)) {
                    foreach ( $arm_membership_plan as $plan ) {
                        $plans[ $plan['arm_subscription_plan_id'] ] = $plan['arm_subscription_plan_name'];
                    }
                }
                $form['armember-bb'] = array(
                    'title'    => esc_html__( 'ARMember', 'armember-membership' ),
                    'sections' => array(
                        'ARMember' => array(
                            'title'  => esc_html__( 'ARMember Restriction Settings', 'armember-membership' ),
                            'fields' => array(
                                'armember_enable'      => array(
                                    'type'    => 'select',
                                    'label'   => esc_html__( 'Select ARMember access', 'armember-membership' ),
                                    'options' => array(
                                        'yes' => esc_html__( 'Yes', 'armember-membership' ),
                                        'no'  => esc_html__( 'No', 'armember-membership' ),
                                    ),
                                    'default' => 'no',
                                    'toggle'  => array(
                                    	'yes' => array(
                                    		'fields' => array(
                                                'armember_access_type',
                                    			'armember_memberships',
                                    		),
                                    	),
                                    ),
                                ),
                                'armember_access_type' => array(
                                    'label'        => esc_html__( 'Select content access type', 'armember-membership' ),
                                    'type'         => 'select',
                                    'options'      => array(
                                        'show' => esc_html__( 'Show', 'armember-membership' ),
                                        'hide'  => esc_html__( 'Hide', 'armember-membership' ),
                                    ),
                                ),
                                'armember_memberships' => array(
                                    'label'        => esc_html__( 'Select a Membership Plan for content access', 'armember-membership' ),
                                    'type'         => 'select',
                                    'options'      => $plans,
                                    'multi-select' => true,
                                ),
                            ),
                        ),
                    ),
                );
            }
            return $form;
        }
	}
}
global $arm_lite_beaver_builder_restiction;
$arm_lite_beaver_builder_restiction = new ARM_lite_beaver_builder_restiction();
