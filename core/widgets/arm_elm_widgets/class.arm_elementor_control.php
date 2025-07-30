<?php
/**
 * Settings for Elementor Widgets For ARMember.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Controls_Manager;

class ARM_Elementor {
    private static $_instance = null;

    public $locations = array(
        array(
            'element' => 'common',
            'action'  => '_section_style',
        ),
        array(
            'element' => 'section',
            'action'  => 'section_advanced',
		),
		array(
			'element' => 'container',
			'action'  => 'section_layout'
		)
    );
    public $section_name = 'arm_elementor_section';
    
	public function __construct() {
        if (file_exists(MEMBERSHIPLITE_WIDGET_DIR . '/arm_elm_widgets/class.arm_elementor_content_restriction.php')) {
        require_once( MEMBERSHIPLITE_WIDGET_DIR . '/arm_elm_widgets/class.arm_elementor_content_restriction.php' );
         }
        $this->register_sections();

        $this->content_restriction();
	}

    public static function instance() {
        if ( is_null( self::$_instance ) )
            self::$_instance = new self();

        return self::$_instance;
    }

    private function register_sections() {
        foreach( $this->locations as $where ) {
            add_action( 'elementor/element/'.$where['element'].'/'.$where['action'].'/after_section_end', array( $this, 'add_section' ), 10, 2 );
        }
    }

    public function add_section( $element, $args ) {
        $exists = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), $this->section_name );

        if( !is_wp_error( $exists ) )
            return false;

        $element->start_controls_section(
            $this->section_name, array(
                'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
                'label' => esc_html__( 'ARMember Settings', 'armember-membership' ),
                'classes' => 'arm_restrict_block_class',
            )
        );

        $element->end_controls_section();
    }

    protected function content_restriction(){}
}

ARM_Elementor::instance();