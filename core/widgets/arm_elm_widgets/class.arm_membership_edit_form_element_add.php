<?php
namespace ElementorARMELEMENT\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Controls_Stack;

if(! defined('ABSPATH')) exit;

class arm_membership_edit_form_element_shortcode extends Widget_Base
{
	public function get_categories() {
		return [ 'armember' ];
	}

    public function get_name()
    {
        return 'arm-edit-form-element-shortcode';
    }

    public function get_title()
    {
        return esc_html('ARMember Edit Form','armember-membership').'<style>
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
        /**START Fetch all shortcode controls from DB */
        /*END*/
        $this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'ARMember Edit Member', 'armember-membership' ),
			]
		);
        $this->add_control(
			'title',
			[
				'label' => esc_html__( 'Title', 'armember-membership' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Edit Profile', 'armember-membership' ),
				'label_block' => true,
			]
		);
		if($ARMemberLite->is_arm_pro_active)
		{
			$forms = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `" . $ARMemberLite->tbl_arm_forms . "` WHERE arm_form_type=%s ORDER BY `arm_form_id` ASC",'edit_profile'), ARRAY_A);//phpcs:ignore --Reason $ARMemberLite->tbl_arm_forms is a table name
			$default = $cnt = 0;
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
						'name' =>  $form['arm_form_label'] . " (" . esc_html__( "ID:",'armember-membership') . " " . $form['arm_form_id'].")",
					);
					$arm_form[$form_id]=$form_shortcodes['forms'][$form_id]['name'];
				} 
			}
		}
		else
		{
			$default = 101;
			$forms = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `" . $ARMemberLite->tbl_arm_forms . "` WHERE arm_form_id=%d",101), ARRAY_A);//phpcs:ignore --Reason $ARMemberLite->tbl_arm_forms is a table name
			$form_id = $forms['arm_form_id'];
			$form_shortcodes['forms'][$form_id] = array(
				'id' => $forms['arm_form_id'],
				'slug' => $forms['arm_form_slug'],
				'name' =>  $forms['arm_form_label'] . " (" . esc_html__( "ID:",'armember-membership') . " " . $forms['arm_form_id'].")",
			);
			$arm_form[$form_id]=$form_shortcodes['forms'][$form_id]['name'];
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
		
		$this->add_control(
			'arm_frm_position',
			[
				'label' => esc_html__( 'Form Position', 'armember-membership'),
				'type' => Controls_Manager::SELECT,
				'default' => 'center',
				'options' => [
					'left' => esc_html__( 'Left', 'armember-membership' ),
					'center' =>esc_html__( 'Center', 'armember-membership' ),
					'right' =>esc_html__( 'Right', 'armember-membership' )
				],
				'label_block' => true,
				'classes'=>'',
				
			]
		);
		if(!$ARMemberLite->is_arm_pro_active)
		{
		$this->add_control(
			'message',
			[
				'label' => esc_html__( 'Message', 'armember-membership' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'Your profile has been updated successfully.', 'armember-membership' ),
			]
		);

		$this->add_control(
			'view_profile',
			[
				'label' => esc_html__( 'View Profile', 'armember-membership' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'armember-membership' ),
				'label_off' => esc_html__( 'Hide', 'armember-membership' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'view_profile_link',
			[
				'label' => esc_html__( 'View Profile Link Label', 'armember-membership' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'View Profile', 'armember-membership' ),
			]
		);
		}

		$this->end_controls_section();
    }

	protected function render()
	{
		global $ARMemberLite;
		$settings = $this->get_settings_for_display();

		echo '<h5 class="title">';
		echo $settings['title']; //phpcs:ignore
		echo '</h5>';
		echo '<div class="arm_select">';
			$arm_shortcode='';
			if($ARMemberLite->is_arm_pro_active)
			{
				echo  do_shortcode('[arm_profile_detail form_id="'.$settings['arm_shortcode_select'].'" form_position="'.$settings['arm_frm_position'].'"]');
			}
			else
			{
				echo do_shortcode( '[arm_edit_profile title="'.$settings['title'].'" form_id="'.$settings['arm_shortcode_select'].'" form_position="'.$settings['arm_frm_position'].'" social_fields="facebook,twitter,linkedin" submit_text="Update Profile" message="'.$settings['message'].'" view_profile="true" view_profile_link="View Profile"]');
			}
		echo '</div>';
	}
}
