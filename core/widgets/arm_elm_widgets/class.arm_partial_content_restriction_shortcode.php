<?php
namespace ElementorARMELEMENT\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Controls_Stack;

if(! defined('ABSPATH')) exit;

class arm_partial_content_restriction_shortcode extends Widget_Base
{
	public function get_categories() {
		return [ 'armember' ];
	}

    public function get_name()
    {
        return 'arm-partial-content-restriction-shortcode';
    }

    public function get_title()
    {
        return esc_html('ARMember Content Restriction','armember-membership').'<style>
        .arm_element_icon{
			display: inline-block;
		    width: 28px;
		    height: 28px;
		    background-image: url('.MEMBERSHIPLITE_IMAGES_URL.'/armember_icon.png);
		    background-repeat: no-repeat;
		    background-position: bottom;
			border-radius: 5px;
		}
        </style>';
    }
    public function get_icon() {
		return 'arm_element_icon';
	}

    public function get_script_depends() {
		return [ 'elementor-arm-element' ];
	}
    protected function register_controls()
    {
        global $ARMemberLite,$wp,$wpdb,$armainhelper,$arm_member_forms,$arm_subscription_plans;

		$all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
		
        /**START Fetch all shortcode controls from DB */
        /*END*/
        $this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'ARMember Partial Content Restriction', 'armember-membership' ),
			]
		);

		$this->add_control(
			'arm_shortcode_select',
			[
				'label' => esc_html__( 'Restriction Type', 'armember-membership'),
				'type' => Controls_Manager::SELECT,
				'default' => 'hide',
				'options' =>[
					'hide'=>esc_html__('Hide content only for','armember-membership'),
					'show'=>esc_html__('Show content only for','armember-membership')
				],
				'label_block' => true,
				
			]
		);

		$plans = array();
		$plans['registered'] = esc_html__('LoggedIn Users','armember-membership');
		$plans['unregistered'] = esc_html__('Non LoggedIn Users','armember-membership');
		if( !empty( $all_plans ) && is_array( $all_plans ) )
		{
			foreach($all_plans as $plan) {
				$key = $plan['arm_subscription_plan_id'];
				$val=$plan['arm_subscription_plan_name'];
				$plans[$key]=$val;
			}
		}
		$plans['any_plan'] =esc_html__('Any Plan','armember-membership');
		

		$this->add_control(
            'arm_show_plans',
            [
                'label' => esc_html__( 'Select User Roles', 'armember-membership' ),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $plans,	
                'label_block' => true,
            ]
        );

		$this->add_control(
			'arm_display_textarea',
			[
				'label' => esc_html__( 'Content to display on true condition', 'armember-membership'),
				'type' => Controls_Manager::TEXTAREA,
				'default' => 'Content Goes Here if condition is true',
				'label_block' => true,
				'classes'=>'',
			]
		);
		$this->add_control(
			'arm_display_textarea_else',
			[
				'label' => esc_html__( 'Content to display on false condition', 'armember-membership'),
				'type' => Controls_Manager::TEXTAREA,
				'default' => 'Content Goes Here if Condition is false',
				'label_block' => true,
				'classes'=>'',

			]
		);
		
		

		$this->end_controls_section();
    }

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		echo '<div class="arm_select">';
			$arm_shortcode='';
			$str='';
			if(!empty($settings['arm_show_plans']))
			{
				foreach($settings['arm_show_plans'] as $sk)
				{
					$str .= $sk.',';
				}
			}
			echo  do_shortcode('[arm_restrict_content plan="'.$str.'" type="'.$settings['arm_shortcode_select'].'"]'.$settings['arm_display_textarea'].' [armelse]
			'.$settings['arm_display_textarea_else'].'
			[/arm_restrict_content]');
		echo '</div>';
	}
}
