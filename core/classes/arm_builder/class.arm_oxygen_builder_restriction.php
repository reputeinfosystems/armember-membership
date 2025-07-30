<?php
if (!class_exists('ARM_lite_oxygen_builder_restriction')) {
	class ARM_lite_oxygen_builder_restriction
	{ 
        var $isOxygenBuilderRestrictionFeature;

		function __construct()
		{
            $is_oxygen_builder_restriction_feature = get_option('arm_is_oxygen_builder_restriction_feature');
            $this->isOxygenBuilderRestrictionFeature = ($is_oxygen_builder_restriction_feature == '1') ? true : false;
            if ($this->isOxygenBuilderRestrictionFeature) {
                add_action( 'plugins_loaded' , array( $this, 'arm_oxygen_builder_restrictions' ) );
            }
		}

        public function arm_oxygen_builder_restrictions() {
            if( function_exists( 'oxygen_vsb_register_condition' ) ) {
                global $arm_subscription_plans;
                $arm_membership_plan = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
                $plan_options[] = array();
                $plan_options = array(
                    'any_plan' => '[any_plan] '.esc_html__( 'Any Plan', 'armember-membership' ),
                    'unregistered' => '[unregistered] '.esc_html__( 'Non Loggedin Users', 'armember-membership' ),
                    'registered' => '[registered] '.esc_html__( 'Loggedin Users', 'armember-membership' )
                );
                foreach ( $arm_membership_plan as $plan ) {
                    $plan_options[ $plan['arm_subscription_plan_id'] ] = '['.$plan['arm_subscription_plan_id'].'] '.$plan['arm_subscription_plan_name'];
                }
                oxygen_vsb_register_condition( 
                    esc_html__( 'ARMember Restriction', 'armember-membership' ), 
                    array( 'options' => $plan_options ), 
                    array('show', 'hide' ),
                    'arm_lite_oxygen_builder_condition_callback', 
                    'armember-membership'
                );
            }
        }        
    }
        
}
global $arm_lite_oxygen_builder_restriction;
$arm_lite_oxygen_builder_restriction = new ARM_lite_oxygen_builder_restriction();

function arm_lite_oxygen_builder_condition_callback( $value, $operator ) {

    preg_match_all("/([^[]+(?=]))/", $value, $matches); 

    if (current_user_can('administrator')) {
        return true;
    }
    
    $arm_membership_plans = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : array();
    $arm_restriction_type = isset($operator) && !empty($operator) ? $operator : '';

    global $arm_restriction;
    $hasaccess = $arm_restriction->arm_check_content_hasaccess( $arm_membership_plans, $arm_restriction_type );

    return $hasaccess;
}