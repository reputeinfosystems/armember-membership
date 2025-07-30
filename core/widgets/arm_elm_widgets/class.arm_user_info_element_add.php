<?php
namespace ElementorARMELEMENT\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Controls_Stack;

if(! defined('ABSPATH')) exit;

class arm_user_info_element_shortcode extends Widget_Base
{
	public function get_categories() {
		return [ 'armember' ];
	}

    public function get_name()
    {
        return 'arm-user-info-shortcode';
    }

    public function get_title()
    {
        return esc_html('ARMember User Info','armember-membership').'<style>
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
        $arm_form['Please select a valid form']='Select Form type';
		
        /**START Fetch all shortcode controls from DB */
        /*END*/
        $this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'ARMember Membership Shortcode', 'armember-membership' ),
			]
		);

		$this->add_control(
			'arm_shortcode_select',
			[
				'label' => esc_html__( 'Select Option', 'armember-membership'),
				'type' => Controls_Manager::SELECT,
				'default' => 'arm_userid',
				'options' =>[
					'arm_userid'=>esc_html__('User ID','armember-membership'),
					'arm_username'=>esc_html__('Username','armember-membership'),
					'arm_displayname'=>esc_html__('Display Name','armember-membership'),
					'arm_firstname_lastname'=>esc_html__('Firstname Lastname', 'armember-membership'),
					'arm_user_plan'=>esc_html__('User Plan','armember-membership'),
					'arm_avatar'=>esc_html__('Avatar','armember-membership'),
					'arm_usermeta'=>esc_html__('Custom Meta','armember-membership')
				],
				'label_block' => true,
				
			]
		);
		$this->add_control(
			'arm_custom_meta',
			[
				'label'=>esc_html__('Enter User Meta Name','armember-membership'),
				'type'=> Controls_Manager::TEXT,
				'default'=>'',
				'label_block' => true,
				'condition'=>['arm_shortcode_select'=>'arm_usermeta']
			]
			);
		

		$this->end_controls_section();
    }

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		echo '<div class="arm_select">';
			$arm_shortcode='';			
			if($settings['arm_shortcode_select'] != 'arm_usermeta')
			{
				echo  do_shortcode('['.$settings['arm_shortcode_select'].']');
			}
			else
			{
				echo  do_shortcode('['.$settings['arm_shortcode_select'].' meta="'.$settings['arm_custom_meta'].'"]');
			}
		echo '</div>';
	}
}
