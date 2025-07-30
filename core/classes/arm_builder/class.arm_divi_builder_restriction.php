<?php
if (!class_exists('ARM_lite_divi_builder_restriction')) {
	class ARM_lite_divi_builder_restriction{

		var $isDiviBuilderRestrictionFeature;
		function __construct(){
			$is_divi_builder_restriction_feature = get_option('arm_is_divi_builder_restriction_feature');
			$this->isDiviBuilderRestrictionFeature = ($is_divi_builder_restriction_feature == '1') ? true : false;
			if ( (empty( $_GET['page'] ) || 'et_divi_role_editor' !== $_GET['page']) && $this->isDiviBuilderRestrictionFeature ) { //phpcs:ignore --Reason:Verifying nonce
				add_filter( 'et_builder_get_parent_modules', array( $this, 'toggle' ) );
				add_filter( 'et_pb_module_content', array( $this, 'restrict_content' ), 10, 4 );
				add_filter( 'et_pb_all_fields_unprocessed_et_pb_row', array( $this, 'row_settings' ) );
				add_filter( 'et_pb_all_fields_unprocessed_et_pb_section', array( $this, 'row_settings' ) );
				add_action( 'admin_enqueue_scripts', array($this,'arm_enqueue_divi_assets'));			
			}
		}

		public static function toggle( $modules ) {

			if ( isset( $modules['et_pb_row'] ) && is_object( $modules['et_pb_row'] ) ) {
				$modules['et_pb_row']->settings_modal_toggles['custom_css']['toggles']['ARMember'] = esc_html__( 'ARMember', 'armember-membership' );
			}

			if ( isset( $modules['et_pb_section'] ) && is_object( $modules['et_pb_section'] ) ) {
				$modules['et_pb_section']->settings_modal_toggles['custom_css']['toggles']['ARMember'] = esc_html__( 'ARMember', 'armember-membership' );
			}

			return $modules;

		}

		public function row_settings( $settings ) {
			if (!$this->isDiviBuilderRestrictionFeature) {
				return $settings;
			}
			global $arm_subscription_plans;
			$plans = array();
			$arm_membership_plans = arm_membership_plans();
			foreach ( $arm_membership_plans as $p_id => $p_name ) {
				$plans[] = array(
					'label' => $p_name,
					'value' => $p_id,
				);
			}

			$settings['armember_restriction_access'] = array(
				'tab_slug' => 'custom_css',
				'label' => esc_html__( 'Enable Restriction access', 'armember-membership' ),
				'description' => esc_html__( 'Enable this option to apply access or restriction.', 'armember-membership' ),
				'type' => 'yes_no_button',
				'options' => array(
					'off' => esc_html__( 'No', 'armember-membership' ),
					'on' => esc_html__( 'Yes', 'armember-membership' ),
				),
				'default' => 'off',
				'option_category' => 'configuration',
				'toggle_slug' => 'ARMember',
			);

			$settings['armember_access_type'] = array(
				'tab_slug' => 'custom_css',
				'label' => esc_html__( 'Restriction Type', 'armember-membership' ),
				'description' => esc_html__( 'Select content restriction type.', 'armember-membership' ),
				'type' => 'select',
				'options' => array(
					'show' => esc_html__( 'Show', 'armember-membership' ),
					'hide' => esc_html__( 'Hide', 'armember-membership' ),
				),
				'default' => 'show',
				'option_category' => 'configuration',
				'toggle_slug' => 'ARMember',
				'show_if_not'         => array(
					'armember_restriction_access' => 'off',
				),            
			);

			$settings['armember_membership_plans'] = array(
				'tab_slug' => 'custom_css',
				'label' => esc_html__( 'Membership Plans', 'armember-membership' ),
				'description' => esc_html__( 'If "Restriction Type" set to "Show" then, the selected Membership Plan(s) will display the content if the condition is true, and if set "Hide" then content will be hidden for the selected "Membership Plan(s)" setting.', 'armember-membership' ),
				'type' => 'multiple_checkboxes',
				'options' => $arm_membership_plans,
				'default' => '',
				'option_category' => 'configuration',
				'toggle_slug' => 'ARMember',
				'show_if_not'         => array(
					'armember_restriction_access' => 'off',
				),
			);


			return $settings;

		}
		
		public function restrict_content( $output, $props, $attrs, $slug ) {
			global $arm_restriction;

			if (!$this->isDiviBuilderRestrictionFeature) {
				return $output;
			}

			if ( ( isset( $props['armember_restriction_access'] ) && $props['armember_restriction_access'] != 'on' ) /* || !isset( $props['armember_restriction_access'] ) */ ) {
				return $output;
			}

			$arm_membership_plans = arm_membership_plans(); 

			if ( et_fb_is_enabled() ) {
				return $output;
			}
			
			if( !isset( $props['armember_access_type'] ) && !isset( $props['armember_membership_plans'] ) ){
				return $output;
			}

			$restricted_plans = explode("|", $props['armember_membership_plans']);
			
			$restricted_plans_id = array();
			foreach ($restricted_plans as $key => $value) {
				if($value == 'on'){				
					$restricted_plans_id[] = array_keys($arm_membership_plans)[$key];
				}
			}

			$access_type = $props['armember_access_type'];

			$hasaccess = $arm_restriction->arm_check_content_hasaccess( $restricted_plans_id, $access_type );

			if( $hasaccess ){
				return $output;
			} else {
				return '';	    	
			}
		}	
			
		public function arm_enqueue_divi_assets(){
			global $arm_lite_version;
			$server_php_self = isset($_SERVER['PHP_SELF']) ? basename(sanitize_text_field($_SERVER['PHP_SELF'])) : ''; //phpcs:ignore

			if( !in_array( $server_php_self, array( 'site-editor.php' ) ) && !empty($_GET['et_fb']) ) { //phpcs:ignore --Reason:Verifying nonce
				wp_register_style('divi-block-editor-styles',MEMBERSHIPLITE_URL.'/css/arm_divi_style.css',array(), $arm_lite_version);
				wp_enqueue_style('divi-block-editor-styles');
			}

		}
	}
}
global $arm_lite_divi_builder_restriction;
$arm_lite_divi_builder_restriction = new ARM_lite_divi_builder_restriction();

if (!function_exists('arm_membership_plans')) {

    function arm_membership_plans() {
		global $arm_subscription_plans;

        $arm_membership_plan = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
	$arm_membership_plan = (is_array($arm_membership_plan) && !empty($arm_membership_plan)) ? $arm_membership_plan : array();
        $plans = array();
		if(!empty($arm_membership_plan)) {
			foreach ( array_reverse($arm_membership_plan) as $plan ) {
				$plans[ $plan['arm_subscription_plan_id'] ] = $plan['arm_subscription_plan_name'];
			}
		}
		$plans['any_plan'] = esc_html__( 'Any Plan', 'armember-membership' );
		$plans['unregistered'] = esc_html__( 'Non Loggedin Users', 'armember-membership' );
		$plans['registered'] = esc_html__( 'Loggedin Users', 'armember-membership' );
		return $plans;
	}
}