<?php 
if (!class_exists('arm_lite_membership_elementcontroller')) {
   class arm_lite_membership_elementcontroller{
   
      function __construct() {
            add_action( 'plugins_loaded', array( $this, 'arm_element_widget' ) );
            add_action('elementor/editor/before_enqueue_scripts',function(){
               wp_register_style('arm_admin_elementor', MEMBERSHIPLITE_URL . '/css/arm_elementor_section.css', array(), MEMBERSHIPLITE_VERSION);
               wp_enqueue_style('arm_admin_elementor');
            });
      } 
      function arm_element_widget(){
         if ( ! did_action( 'elementor/loaded' ) ) {
            return;
         }
         
         require_once(MEMBERSHIPLITE_WIDGET_DIR . '/arm_elm_widgets/class.arm_elementor_widget_element.php');

         if (file_exists(MEMBERSHIPLITE_WIDGET_DIR . '/arm_elm_widgets/class.arm_elementor_control.php')) {
            require_once( MEMBERSHIPLITE_WIDGET_DIR . '/arm_elm_widgets/class.arm_elementor_control.php');
         }   
      }
   }
}
?>