<?php
namespace ElementorARMELEMENT\Widgets;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Controls_Stack;

if(! defined('ABSPATH')) exit;

class arm_close_account_shortcode extends Widget_Base
{
	public function get_categories() {
		return [ 'armember' ];
	}

    public function get_name()
    {
        return 'arm-close-account-shortcode';
    }

    public function get_title()
    {
        return esc_html('ARMember Close Account','armember-membership').'<style>
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
		
        /**START Fetch all shortcode controls from DB */
        /*END*/
        $this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'ARMember Close Account', 'armember-membership' ),
			]
		);

        $this->add_control(
			'title',
			[
				'label' => esc_html__( 'Title', 'armember-membership' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default' => esc_html__('Close Account','armember-membership'),
			]
		);
		$forms = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `" . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_form_type`=%s ORDER BY `arm_form_id` ASC",'login'), ARRAY_A); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_forms is a table name
		$default = $cnt =0;
		if(!empty($forms)){
			foreach ($forms as $form) {
				$form_id = $form['arm_form_id'];
				if($cnt == 0)
				{
					$default = $form_id;
				}
				$cnt++;
				$form_slug = $form['arm_form_slug'];
				$form_shortcodes['forms'][$form_id] = array(
					'id' => $form['arm_form_id'],
					'slug' => $form['arm_form_slug'],
					'name' => $form['arm_form_label'] . " (" . esc_html__("ID:",'armember-membership') . " " . $form['arm_form_id'] . ")",
				);
				$arm_form[$form_id]=$form_shortcodes['forms'][$form_id]['name'];
			} 
		}

		$this->add_control(
			'arm_shortcode_select',
			[
				'label' => esc_html__( 'Select Forms', 'armember-membership'),
				'type' => Controls_Manager::SELECT,
				'default' => $default,
				'options' => $arm_form,
				'label_block' => true,
				
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
		echo '<div class="arm_select_setup">';
			/**Shotcode goes here */
            $arm_shortcode='';
			echo do_shortcode('[arm_close_account set_id="'.$settings['arm_shortcode_select'].'"]');
		echo '</div>';
	}
}
