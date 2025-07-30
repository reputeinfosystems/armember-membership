<?php
if (!class_exists('ARM_lite_bricks_builder_restriction')) {
	class ARM_lite_bricks_builder_restriction
	{ 
        var $isBricksBuilderRestrictionFeature;

		function __construct()
		{
            $is_bricks_builder_restriction_feature = get_option('arm_is_bricks_builder_restriction_feature');
            $this->isBricksBuilderRestrictionFeature = ($is_bricks_builder_restriction_feature == '1') ? true : false;
            if ($this->isBricksBuilderRestrictionFeature) {
                add_filter( 'bricks/builder/elements', array($this, 'arm_add_bricks_builder_control_group'), 10);
                add_filter( 'bricks/element/render', array($this, 'arm_render_bricks_element' ),20 , 2 );
            }
		}

        public function arm_add_bricks_builder_control_group( $elements ) {

            foreach ($elements as $e_value) {
                add_filter( 'bricks/elements/'. $e_value .'/control_groups', array($this, 'arm_add_control_group') );
                add_filter( 'bricks/elements/'. $e_value .'/controls', array($this, 'arm_add_controls') );
            }

            return $elements;
            
        }
        
        public function arm_add_control_group( $control_groups ) {
            $control_groups['ARMember_content'] = [
                'tab'      => 'content', // or 'style'
                'title'    => '<img src="'.MEMBERSHIPLITE_IMAGES_URL . '/armember_menu_icon.png'.'" alt="">'.'&nbsp&nbsp'.esc_html__( 'ARMember Restriction', 'armember-membership' ), //phpcs:ignore
            ];

            return $control_groups;
        }

        public function arm_add_controls( $controls ) {
            global $arm_subscription_plans;

            $content_restriction_type = array(
                'show' => esc_html__( 'Show', 'armember-membership' ),
                'hide' => esc_html__( 'Hide', 'armember-membership' )
            );
            
            $arm_membership_plan = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
            $plan_options = array();
            $plan_options = array(
                'any_plan' => esc_html__( 'Any Plan', 'armember-membership' ),
                'unregistered' => esc_html__( 'Non Loggedin Users', 'armember-membership' ),
                'registered' => esc_html__( 'Loggedin Users', 'armember-membership' )
            );
            foreach ( $arm_membership_plan as $plan ) {
                $plan_options[ $plan['arm_subscription_plan_id'] ] = $plan['arm_subscription_plan_name'];
            }

            $controls['arm_restriction_access'] = array(
                'tab'           => 'content',
                'group'         => 'ARMember_content',
                'label'         => esc_html__( 'Enable Content Restriction', 'armember-membership' ),
                'type'          => 'checkbox',
                'inline'        => true,
                'small'         => true,
                'default'       => false, // Default: false
                'description'   => esc_html__( 'Enable this option to apply access or restriction.', 'armember-membership' ),
            );
            $controls['arm_restriction_type'] = array(
                'tab'           => 'content',
                'group'         => 'ARMember_content',
                'label'         => esc_html__( 'Restriction Type', 'armember-membership' ),
                'type'          => 'select',
                'options'       => $content_restriction_type,
                'pasteStyles'   => true,
                'default'       => 'show',
                'description'   => esc_html__( 'Select Content Restriction Type.', 'armember-membership' ),
                'required'      => array('arm_restriction_access', '=', true ),
            );

            $controls['arm_membership_plans'] = array(
                'tab'           => 'content',
                'group'         => 'ARMember_content',
                'label'         => esc_html__( 'Membership Plans', 'armember-membership' ),
                'type'          => 'select',
                'options'       => $plan_options,
                'clearable'     => true,
                'searchable'    => true,
                'multiple'      => true, 
                'pasteStyles'   => true,
                'placeholder'   => esc_html__( 'None', 'armember-membership' ),
                'description'   => esc_html__( 'If "Restriction Type" set to "Show" then, the selected Membership Plan(s) will display the content if the condition is true, and if set "Hide" then content will be hidden for the selected "Membership Plan(s)" setting.', 'armember-membership' ),
                'required'      => array('arm_restriction_access', '=', true ),
            );
        
            return $controls;
        }

        public function arm_render_bricks_element( $render, $element ) {
            $arm_settings = $element->settings;
            if (!$this->isBricksBuilderRestrictionFeature) {
                return $render;
            }
                
            if (current_user_can('administrator')) {
                return $render;
            }

            if(!isset($arm_settings['arm_restriction_access']) || (isset($arm_settings['arm_restriction_access']) && $arm_settings['arm_restriction_access'] != '1')) {
                return $render;
            }

            $arm_restriction_type = (isset($arm_settings['arm_restriction_type']) && !empty($arm_settings['arm_restriction_type'])) ? $arm_settings['arm_restriction_type'] : 'show'; 

            $arm_membership_plans = (isset($arm_settings['arm_membership_plans']) && !empty($arm_settings['arm_membership_plans'])) ? $arm_settings['arm_membership_plans'] : array() ;

            global $arm_restriction;
            $hasaccess = $arm_restriction->arm_check_content_hasaccess( $arm_membership_plans, $arm_restriction_type );

            return $hasaccess;
        }
    }        
}
global $arm_lite_bricks_builder_restriction;
$arm_lite_bricks_builder_restriction = new ARM_lite_bricks_builder_restriction();