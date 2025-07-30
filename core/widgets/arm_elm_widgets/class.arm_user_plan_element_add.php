<?php
namespace ElementorARMELEMENT\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Controls_Stack;

if(! defined('ABSPATH')) exit;

class arm_user_plan_info_element_shortcode extends Widget_Base
{
	public function get_categories() {
		return [ 'armember' ];
	}

    public function get_name()
    {
        return 'arm-user-plan-info-shortcode';
    }

    public function get_title()
    {
        return esc_html('ARMember Plan Info','armember-membership').'<style>
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
		$arm_form =array();
        $all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
		
        /**START Fetch all shortcode controls from DB */
        /*END*/
        $this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'ARMember Membership Shortcode', 'armember-membership' ),
			]
		);
		$this->add_control(
			'title',
			[
				'label' => esc_html__( 'Title', 'armember-membership' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default'=>'ARMember Plan Info'
			]
		);
		$plans = array();
		$cnt = $default = 0;
		if( !empty( $all_plans ) && is_array( $all_plans ) )
		{
			foreach($all_plans as $plan) {
				if($cnt == 0)
				{
					$default = $plan['arm_subscription_plan_id'];
				}
				$key = $plan['arm_subscription_plan_id'];
				$val=$plan['arm_subscription_plan_name'];
				$plans[$key]=$val;
				$cnt++;
			}
		}
		$this->add_control(
			'arm_show_plans',
			[
				'label' => esc_html__( 'Select User Plans', 'armember-membership' ),
				'type' => Controls_Manager::SELECT,
				'multiple' => true,
				'options' => $plans,	
				'label_block' => true,
				'default' => $default
			]
		);

		$this->add_control(
			'arm_select_plan_info',
			[
				'label' => esc_html__( 'Select Plan Information', 'armember-membership' ),
				'type' => Controls_Manager::SELECT,
				'label_block' => true,
				'options'=>[
					'arm_start_plan'=>esc_html__('Start Date','armember-membership'),
					'arm_expire_plan'=>esc_html__('Expire Date','armember-membership'),
					'arm_amount_plan'=>esc_html__('Plan Amount','armember-membership'),
					'arm_trial_start'=>esc_html__('Trial Start Date','armember-membership'),
					'arm_trial_end'=>esc_html__('Trial Start Date','armember-membership'),
					'arm_grace_period_end'=>esc_html__('Grace End Date','armember-membership'),
					'arm_user_gateway'=>esc_html__('Paid By','armember-membership'),
					'arm_completed_recurring'=>esc_html__('Completed Recurrence','armember-membership'),
					'arm_next_due_payment' => esc_html__('Next Due Date','armember-membership'),
					'arm_payment_mode' => esc_html__('Payment Mode','armember-membership'),
					'arm_payment_cycle' => esc_html__('Payment Cycle','armember-membership'),
				],
				'default'=>'arm_start_plan'
			]
		);
	
		$this->end_controls_section();
	}

	protected function render()
	{
		
		$settings = $this->get_settings_for_display();
			echo '<h5 class="title">';
			echo $settings['title']; //phpcs:ignore
			echo '</h5>';
			echo '<div class="arm_select">';
			echo do_shortcode('[arm_user_planinfo plan_id="'.$settings['arm_show_plans'].'" plan_info="'.$settings['arm_select_plan_info'].'"]'); //phpcs:ignore
			echo '</div>';
		
	}
}
