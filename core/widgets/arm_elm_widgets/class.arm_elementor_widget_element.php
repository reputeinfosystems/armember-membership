<?php 
namespace ElementorARMELEMENT;
class elementor_arm_membership_elements
{
    private static $_instance = null;
    
    public function __construct()
    {
        // Register widgets
        add_action( 'elementor/frontend/after_register_scripts',array($this, 'arm_widget_scripts'));
       
        add_action( 'elementor/widgets/register',array($this, 'arm_register_new_widgets'));

        add_action( 'elementor/elements/categories_registered', array($this, 'arm_add_elementor_widget_categories'));
    }

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
           self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function arm_add_elementor_widget_categories( $elements_manager ) {

        $elements_manager->add_category(
            'armember',
            [
                'title' => esc_html__( 'ARMember', 'armember-membership' ),
                'icon' => 'arm_element_icon',
            ]
        );
    }

    public function arm_widget_scripts() {
        global $arm_lite_version, $ARMemberLite;
        
        if( ( !empty($_REQUEST['action']) && $_REQUEST['action']=='elementor_ajax' ) || ( !empty($_REQUEST['action']) && $_REQUEST['action']=='elementor' ) || !empty($_REQUEST['elementor-preview']) || (!empty($_REQUEST['data']) && !empty($_REQUEST['data']['elementor_post_lock']) ) ) //phpcs:ignore
        {
            wp_register_script('elementor-arm-element', MEMBERSHIPLITE_URL . '/js/arm_element.js', array('jquery'), $arm_lite_version, true);
            
            $isFrontSection = 2;

            $ARMemberLite->set_front_css($isFrontSection);
            $ARMemberLite->set_front_js($isFrontSection);
            $ARMemberLite->enqueue_angular_script();
        }
    }

    
    public function arm_register_new_widgets() {
        global $arm_pay_per_post_feature,$ARMemberLite;
    // Its is now safe to include Widgets files
        require_once( __DIR__ . '/class.arm_membership_form_element_add.php' );
        require_once( __DIR__ . '/class.arm_membership_login_element_add.php' );
        require_once( __DIR__ . '/class.arm_membership_forgot_password_element_add.php');
        require_once( __DIR__ . '/class.arm_membership_change_password_element_add.php');
        require_once( __DIR__ . '/class.arm_membership_edit_form_element_add.php' );
        require_once( __DIR__ . '/class.arm_setup_form_element_add.php' );
        require_once( __DIR__ . '/class.arm_actions_button_element_add.php' );
        require_once( __DIR__ . '/class.arm_close_account_add.php' );
        require_once( __DIR__ . '/class.arm_my_profile_element_add.php' );
        require_once( __DIR__ . '/class.arm_current_membership_element_add.php' );
        require_once( __DIR__ . '/class.arm_payment_transaction_element_add.php' );
        require_once( __DIR__ . '/class.arm_user_info_element_add.php' );
        require_once( __DIR__ . '/class.arm_user_plan_element_add.php' );
        require_once( __DIR__ . '/class.arm_latest_user_list_element_add.php' );
        require_once( __DIR__ .'/class.arm_condition_user_is_in_trial_add.php');
        require_once( __DIR__ .'/class.arm_partial_content_restriction_shortcode.php');

        // Register Widgets
        if(version_compare(ELEMENTOR_VERSION,'3.5.0','<'))
        {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_membership_register_element_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_membership_login_element_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_membership_forgot_password_element_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_membership_change_password_element_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_membership_edit_form_element_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_setup_element_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_action_button_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_close_account_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_my_profile_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_current_membership_shortcode());
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_payment_transaction_shortcode());
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_user_info_element_shortcode());
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_user_plan_info_element_shortcode());
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_latest_user_list_element_shortcode());
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_user_badge_element_shortcode());
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_user_private_content_element_shortcode());
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_conditional_redirect_roles_shortcode());
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_conditional_redirect_plans_shortcode());
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_check_user_is_in_trial_shortcode());
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_partial_content_restriction_shortcode());

            if($ARMemberLite->is_arm_pro_active && !empty($arm_pay_per_post_feature->isPayPerPostFeature))
            {
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_paid_post_list_shortcode());
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arm_paid_post_transaction_shortcode());
            }
        }
        else{

            \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_membership_register_element_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_membership_login_element_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_membership_forgot_password_element_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_membership_change_password_element_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_membership_edit_form_element_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_setup_element_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_action_button_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_close_account_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_my_profile_shortcode() );
            \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_current_membership_shortcode());
            \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_payment_transaction_shortcode());
            \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_user_info_element_shortcode());
            \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_user_plan_info_element_shortcode());
            \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_latest_user_list_element_shortcode());
            \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_check_user_is_in_trial_shortcode());
            \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_partial_content_restriction_shortcode());
            if($ARMemberLite->is_arm_pro_active && !empty($arm_pay_per_post_feature->isPayPerPostFeature))
            {
                \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_paid_post_list_shortcode());
                \Elementor\Plugin::instance()->widgets_manager->register( new Widgets\arm_paid_post_transaction_shortcode());
            }
        }
    }
}
elementor_arm_membership_elements::instance();
?>