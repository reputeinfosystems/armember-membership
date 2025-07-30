<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Controls_Manager;

class ARM_Elementor_Content_Restriction extends ARM_Elementor {
	protected function content_restriction() {
		// Setup controls
		$this->register_controls();

		// Filter elementor render_content hook
		add_action( 'elementor/widget/render_content', array( $this, 'arm_elementor_render_content' ), 10, 2 );
		add_action( 'elementor/frontend/section/should_render', array( $this, 'arm_elementor_should_render' ), 10, 2 );
		add_action( 'elementor/frontend/container/should_render', array( $this, 'arm_elementor_should_render' ), 10, 2 );
		
	}
    
	// Register controls to sections and widgets
	protected function register_controls() {
		foreach( $this->locations as $where ) {
				add_action('elementor/element/'.$where['element'].'/'.$this->section_name.'/before_section_end', array( $this, 'add_controls' ), 10, 2 );
		}
	}

	// Define controls
	public function add_controls( $element, $args ) {
        global $ARMemberLite,$wp,$wpdb,$arm_subscription_plans;
        $plan_list = array();
        $all_active_plans = $arm_subscription_plans->arm_get_all_active_subscription_plans();
		
	$plan_list = array(
		'any_plan' => esc_html__( 'Any Plan', 'armember-membership' ),
		'-2' => esc_html__( 'User Having No Plan', 'armember-membership' ),
		'unregistered' => esc_html__( 'Non Loggedin Users', 'armember-membership' ),
		'registered' => esc_html__( 'Loggedin Users', 'armember-membership' )
	);
		if( !empty( $all_active_plans ) && is_array( $all_active_plans ) )
		{
			foreach($all_active_plans as $plans)
			{
				$plan_id = $plans['arm_subscription_plan_id'];
				$plan_name = $plans['arm_subscription_plan_name'];
				$plan_list[$plan_id] = $plan_name;
			}
		}
		$element->add_control(
			'arm_require_membership_heading', array(
				'label'     => esc_html__( 'Show content to', 'armember-membership' ),
				'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
			)
		);

		$element->add_control(
            'arm_require_membership', array(
                'type'        => Controls_Manager::SELECT2,
                'options'     => $plan_list,
                'multiple'    => 'true',
				'label_block' => 'true',
				'description' => esc_html__( 'Require membership Plan to access this content.', 'armember-membership' ),
            )
        );

	}

	/**
	 * Filter sections to render content or not.
	 * If user doesn't have access, hide the section.
	 * @return boolean whether to show or hide section.
	 * @since 2.3
	 */

	public function arm_elementor_should_render( $should_render, $element ) {
		// Don't hide content in editor mode.
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			return $should_render;
		}

		// Bypass if it's already hidden.
		if ( $should_render === false ) {
			return $should_render;
		}

		// Checks if the element is restricted and then if the user has access.
		$should_render = $this->arm_elementor_has_access($element);

		return apply_filters( 'arm_elementor_section_access', $should_render, $element );
	}

	/**
	 * Filter individual content for members.
	 * @return string Returns the content set from Elementor.
	 * @since 2.0
	 */
	public function arm_elementor_render_content( $content, $widget ){

        // Don't hide content in editor mode.
        if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            return $content;
        }

		$show = $this->arm_elementor_has_access( $widget );
		
		if ( ! $show ) {
			$content = '';
		}
        
        return $content;
	}

	/**
	 * Figure out if the user has access to restricted content.
	 * @return bool True or false based if the user has access to the content or not.
	 */
	public function arm_elementor_has_access( $element ) {		

		$access = false;

		$element_settings = $element->get_active_settings();

		$restricted_plans = (!empty($element_settings['arm_require_membership']) ) ?  $element_settings['arm_require_membership'] : '';
		
		if(!$restricted_plans){
			$access=true;
		}

		$user_id = get_current_user_id();
		if( user_can($user_id, 'administrator'))
		{
			$access = true;
		}

		global $arm_restriction;
		$arm_restricted_type = 'show';
		$access = $arm_restriction->arm_check_content_hasaccess( $restricted_plans, $arm_restricted_type );

		return apply_filters( 'arm_elementor_has_access', $access, $element, $restricted_plans );
	}
}

new ARM_Elementor_Content_Restriction;
