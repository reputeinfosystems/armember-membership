<?php
global $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings,  $arm_subscription_plans, $arm_membership_setup, $arm_member_forms, $arm_payment_gateways,$arm_common_lite;
$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST ); //phpcs:ignore
/*if ( isset( $posted_data['form_action'] ) && in_array( $posted_data['form_action'], array( 'add', 'update' ) ) ) {
	do_action( 'arm_save_membership_setups', $posted_data ); //phpcs:ignore
}*/
$manage_gateway_link = admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=payment_options' );
$alertMessages       = $ARMemberLite->arm_alert_messages();
$alertMessages = apply_filters('arm_alert_message_pro',$alertMessages);
$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
$general_settings    = $all_global_settings['general_settings'];
//$currencies          = array_merge( $arm_payment_gateways->currency['paypal'], $arm_payment_gateways->currency['bank_transfer'] );
if($ARMemberLite->is_arm_pro_active)
{
	$currencies = array_merge($arm_payment_gateways->currency['paypal'], $arm_payment_gateways->currency['stripe'], $arm_payment_gateways->currency['authorize_net'], $arm_payment_gateways->currency['2checkout'], $arm_payment_gateways->currency['bank_transfer']);
}
else
{
	$currencies          = array_merge( $arm_payment_gateways->currency['paypal'], $arm_payment_gateways->currency['bank_transfer'] );
}
$currency_symbol     = $currencies[ $general_settings['paymentcurrency'] ];
$allGateways         = $arm_payment_gateways->arm_get_all_payment_gateways_for_setup();

        $arm_setup_preview_split_string = '<input type="hidden" name="arm_setupform_split" value="">';
$page_mode     = esc_html__( 'Add New Plan + Signup Page', 'armember-membership' );
$action        = 'add';
$setup_id      = 0;
$button_labels = array(
	'submit'   => esc_html__( 'Submit', 'armember-membership' ),

	'next'     => esc_html__( 'Next', 'armember-membership' ),
	'previous' => esc_html__( 'Previous', 'armember-membership' ),

);
$setup_modules = array( 'style' => array() );
$default_setup_style = array(
	'content_width'                  => '800',
	'plan_skin'                      => 'skin1',
	'hide_current_plans'             => 0,
	'hide_plans'					 => 0,
	'plan_area_position'			 => 'before',
	'plan_selection_area'            => 'before',
	'form_position'			         => 'left',
	'font_family'                    => 'Poppins',
	'title_font_size'                => 20,
	'title_font_bold'                => 1,
	'title_font_italic'              => '',
	'title_font_decoration'          => '',
	'description_font_size'          => 15,
	'description_font_bold'          => 0,
	'description_font_italic'        => '',
	'description_font_decoration'    => '',
	'price_font_size'                => 28,
	'price_font_bold'                => 0,
	'price_font_italic'              => '',
	'price_font_decoration'          => '',
	'summary_font_size'              => 16,
	'summary_font_bold'              => 0,
	'summary_font_italic'            => '',
	'summary_font_decoration'        => '',
	'plan_title_font_color'          => '#2C2D42',
	'plan_desc_font_color'           => '#555F70',
	'price_font_color'               => '#2C2D42',
	'summary_font_color'             => '#555F70',
	'bg_active_color'                => '#005AEE',
	'selected_plan_title_font_color' => '#005AEE',
	'selected_plan_desc_font_color'  => '#2C2D42',
	'selected_price_font_color'      => '#FFFFFF',
);
$default_setup_style = apply_filters('arm_default_setup_style',$default_setup_style);
$setup_data = array();
if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit_setup' && isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) { //phpcs:ignore
	$setup_id   = intval( $_GET['id'] ); //phpcs:ignore
	$setup_data = $arm_membership_setup->arm_get_membership_setup( $setup_id );

	if ( $setup_data !== false && ! empty( $setup_data ) ) {
		$page_mode     = esc_html__( 'Edit Plan + Signup Page', 'armember-membership' );
		$action        = 'update';
		$setup_name    = $setup_data['setup_name'];
		$arm_setup_type = 0;
		$arm_setup_type = $setup_data['arm_setup_type'];
		$setup_modules = !empty($setup_data['setup_modules']) ? $setup_data['setup_modules'] : array();
		$button_labels = isset( $setup_data['setup_labels']['button_labels'] ) ? $setup_data['setup_labels']['button_labels'] : $button_labels;
	}
}

if(!empty($setup_data['setup_labels']['credit_card_logos']))
{
	$arm_card_logos= explode(',',$setup_data['setup_labels']['credit_card_logos']);
	if(count($arm_card_logos) > 1)
	{
		$setup_data['setup_labels']['credit_card_logos'] = end($arm_card_logos);
	}
}

$_SESSION['arm_file_upload_arr']['credit_card_logos'] = empty($setup_data['setup_labels']['credit_card_logos']) ?  "-" : $ARMemberLite->arm_get_basename($setup_data['setup_labels']['credit_card_logos']);
$user_selected_plan       = isset( $setup_modules['selected_plan'] ) ? $setup_modules['selected_plan'] : '';
$user_selected_plan_cycle = isset( $setup_modules['selected_plan_cycle'] ) ? $setup_modules['selected_plan_cycle'] : '';
$selectedForm             = ( empty( $setup_modules['modules']['forms'] ) || $setup_modules['modules']['forms'] == 0 ) ? '' : $setup_modules['modules']['forms'];
$selectedPlans            = isset( $setup_modules['modules']['plans'] ) ? $setup_modules['modules']['plans'] : array();
$planOrders               = ( ! empty( $setup_modules['modules']['plans_order'] ) ) ? $setup_modules['modules']['plans_order'] : array();
$planCycleOrders          = ( ! empty( $setup_modules['modules']['plan_cycle_order'] ) ) ? $setup_modules['modules']['plan_cycle_order'] : array();
$gatewayOrders            = ( ! empty( $setup_modules['modules']['gateways_order'] ) ) ? $setup_modules['modules']['gateways_order'] : array();

$selectedGateways     = isset( $setup_modules['modules']['gateways'] ) ? $setup_modules['modules']['gateways'] : array();
$selectedPaymentModes = isset( $setup_modules['modules']['payment_mode'] ) ? $setup_modules['modules']['payment_mode'] : array();
$setup_modules = apply_filters('arm_additional_setup_modules_data',$setup_modules);

$setup_modules['style'] = shortcode_atts( $default_setup_style, $setup_modules['style']);
$setup_name             = ! empty( $setup_name ) ? esc_html( stripslashes( $setup_name ) ) : '';
$arm_setup_type = !empty($arm_setup_type) ? $arm_setup_type : 0;
$all_payment_gateways   = $arm_payment_gateways->arm_get_active_payment_gateways();

?>
<?php
 $allPlans = $arm_subscription_plans->arm_get_all_active_subscription_plans();
?>
<div class="wrap arm_page arm_membership_setup_form_main_wrapper arm_membership_setup_main_wrapper popup_wrapper">
	<div class="content_wrapper arm_membership_setup_container" id="content_wrapper">
		<div class="arm_membership_setup_content">
			<form  method="post" id="arm_membership_setup_admin_form" class="arm_membership_setup_admin_form arm_admin_form" >
				<input type="hidden" name="id" value="<?php echo intval($setup_id); ?>">
				<input type="hidden" name="form_action" value="<?php echo esc_attr($action); ?>">
				<div class="page_title">
					<span class="arm_add_plan_signup_label">
                    <?php 
		    	        echo esc_html($page_mode);
					?>
					</span>
					<span class="arm_edit_plan_signup_label hidden_section"><?php echo esc_html__("Edit Plan + Signup Page", 'armember-membership');?></span>
					<?php
                    	
                        	$after_title_content = "";
                        	$after_title_content = apply_filters('arm_after_membership_setup_page_title', $after_title_content);
                        	
						echo $after_title_content; //phpcs:ignore
					?>
					<span class="popup_close_btn arm_popup_close_btn arm_setup_cancel_btn"></span>
                </div>
				<div class="armclear"></div>
				<div class="arm_setup_admin_form_container arm_admin_form_content">
					<span class="arm_setup_main_error_msg error" style="display: none;"><?php esc_html_e( 'This membership setup can not be saved because in some cases, payment gateway will not be available. So setup cannot be processed.', 'armember-membership' ); ?></span>
					
					<div class="arm_setup_modules_container">
						<div class="arm_right_border"></div>
						<div class="arm_setup_section_title"><span class="arm_title_round">1</span><?php esc_html_e( 'Let\'s Start', 'armember-membership' ); ?></div>
						
						<div class="arm_setup_section_body">

							<span class="arm_info_text arm_margin_bottom_24" ><?php esc_html_e( 'This wizard will help you to configure membership registration page. It will generate only single shortcode for processes like plan selection', 'armember-membership' ); ?> &rarr; <?php esc_html_e( 'signup', 'armember-membership' ); ?> &rarr; <?php esc_html_e( 'payment process.', 'armember-membership' ); ?></span>
							
							<div class="arm_setup_option_field">
								<div class="arm_setup_option_label"><?php esc_html_e( 'Setup Name', 'armember-membership' ); ?></div>
								<div class="arm_setup_option_input arm_setup_forms_container arm_width_100_pct">
									<div class="arm_setup_module_box">
										<input name="setup_data[setup_name]" id="setup_name"  class="arm_width_100_pct" type="text"  title="Setup name" value="<?php echo esc_attr($setup_name); ?>" data-msg-required="<?php esc_attr_e( 'Setup name can not be left blank.', 'armember-membership' ); ?>" placeholder="<?php esc_attr_e( 'Setup name', 'armember-membership' ); ?>" required />
										<span class="arm_setup_error_msg"></span>
									</div>
									<div class="armclear"></div>
								</div>
							</div>

							<div class="arm_solid_divider"></div>
							
							<div class="arm_setup_section_title arm_margin_top_20 arm_margin_bottom_18"><?php esc_html_e( 'Setup', 'armember-membership' ); ?></div>

							<?php if($ARMemberLite->is_arm_pro_active)
							{
								$arm_pro_setup_style_section = '';
								echo apply_filters('arm_pro_setup_form_style_section',$arm_pro_setup_style_section,$setup_data,$arm_setup_type); //phpcs:ignore
							}?>
							<div class="arm_setup_option_field arm_setup_plans_main_container arm_width_50_pct <?php echo ($arm_setup_type == 0) ? '' : 'hidden_section';?>">
								<div class="arm_setup_option_label arm_padding_top_12" ><?php esc_html_e( 'Select Plans', 'armember-membership' ); ?></div>

								<div class="arm_setup_option_input arm_setup_plans_container">
									<div class="arm_setup_module_box">
										<div class="arm_setup_plan_options_list">
										<?php echo $arm_membership_setup->arm_setup_plan_list_options( $selectedPlans, $allPlans ); //phpcs:ignore ?>
										</div>
										<span class="arm_setup_error_msg"></span>
									</div>
									<div class="armclear"></div>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $arm_slugs->manage_plans . '&action=new' ) ); //phpcs:ignore ?>" target="_blank" class="arm_setup_conf_links arm_ref_info_links"><?php esc_html_e( 'Add New Plan', 'armember-membership' ); ?></a>
								</div>
							</div>
                            <?php 
                                $arm_setup_content_after_plans = "";
                                $arm_setup_content_after_plans = apply_filters('arm_add_setup_container_after_plans', $arm_setup_content_after_plans, $selectedPlans, $arm_setup_type);
                                echo $arm_setup_content_after_plans; //phpcs:ignore
                            ?>
							<span class="arm_setup_plan_error_msg"></span>
							<div class="arm_solid_divider"></div>
						</div>
						<div class="arm_setup_section_title"><span class="arm_title_round">2</span><?php esc_html_e( 'Payment Gateways', 'armember-membership' ); ?></div>
						<div class="arm_setup_section_body arm_setup_gateways_container_body">							

							<div class="arm_setup_option_field arm_setup_gateways_container">
								<div class="arm_setup_option_label arm_padding_top_10"><?php esc_html_e( 'Select Payment Gateways', 'armember-membership' ); ?></div>
								<div class="arm_setup_option_input arm_setup_items_box_gateways">
									<?php

									$plan_options = array();
									$plan_detail  = array();



										$plan_object_array = array();
									foreach ( $allPlans as $pID => $pdata ) {
										$pddata                     = isset( $allPlans[ $pID ] ) ? $allPlans[ $pID ] : array();
										if($ARMemberLite->is_arm_pro_active)
										{
											$plan_object                = new ARM_Plan( $pID );
										}
										else
										{
											$plan_object                = new ARM_Plan_Lite( $pID );
										}
										 $plan_object_array[ $pID ] = $plan_object;
										if ( ! empty( $pddata ) ) {
											array_push( $plan_detail, $pddata );
											$s_plan_name         = $pddata['arm_subscription_plan_name'];
											$plan_type           = $pddata['arm_subscription_plan_type'];
											$plan_options        = maybe_unserialize( $pddata['arm_subscription_plan_options'] );
											$plan_payment_cycles = ( isset( $plan_options['payment_cycles'] ) && ! empty( $plan_options['payment_cycles'] ) ) ? $plan_options['payment_cycles'] : array();
											if ( empty( $plan_payment_cycles ) ) {
												$plan_payment_cycles = array(
													array(
														'cycle_key' => 'arm0',
														'cycle_label' => $plan_object->plan_text( false, false ),
													),
												);
											}
											$payment_type = isset( $plan_options['payment_type'] ) ? $plan_options['payment_type'] : '';


										}
									}




									?>
									<div class="arm_setup_module_box">
										<div class="arm_setup_gateway_options_list">
											<?php

											echo $arm_membership_setup->arm_setup_gateway_list_options( $selectedGateways, $all_payment_gateways, $selectedPaymentModes, $selectedPlans, $plan_object_array ); //phpcs:ignore
											?>
										</div>
										<span class="arm_setup_error_msg"></span>
									</div>
									<div class="armclear"></div>
									<a href="<?php echo esc_attr( admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=payment_options' ) ); //phpcs:ignore ?>" target="_blank" class="arm_setup_conf_links arm_ref_info_links"><?php esc_html_e( 'Configure More Gateways', 'armember-membership' ); ?></a>
									<span class="arm_setup_gateway_error_msg error" style="display: none;"><?php esc_html_e( 'At least one payment gateway configuration is required for paid plan(s) selection.', 'armember-membership' ); ?></span>
									<div class="armclear"></div>
								   
									<?php
									$arm_additional_plans_fields_section = '';
									echo apply_filters('arm_additional_plans_fields_section',$arm_additional_plans_fields_section,$allPlans,$selectedPaymentModes,$selectedPlans,$setup_modules,$alertMessages); //phpcs:ignore
                                        $paymentgateway_plan_options = "";
                                        echo apply_filters('arm_payment_gateway_has_plan_field_outside', $paymentgateway_plan_options, $selectedPlans, $allPlans, $alertMessages, $setup_modules, $selectedGateways); //phpcs:ignore
                                    ?>
									<div class="arm_payment_gateway_warnings">
									   <?php do_action( 'arm_show_payment_gateway_recurring_notice', $plan_detail ); ?>
									</div>
								</div>
							</div>
                            <?php echo $arm_setup_preview_split_string; //phpcs:ignore ?>
							<?php 
							$arm_setup_additional_basic_options = '';
							echo apply_filters('arm_setup_additional_basic_option_section',$arm_setup_additional_basic_options,$setup_modules); //phpcs:ignore
							?>
							<div class="arm_solid_divider"></div>
						</div>
						<div class="arm_setup_section_title"><span class="arm_title_round">3</span><?php esc_html_e( 'Forms', 'armember-membership' ); ?></div>
						<div class="arm_setup_section_body">
							<span class="arm_info_text arm_margin_bottom_24 arm_width_100_pct" ><?php esc_html_e( 'If user is not logged in than selected signup form will be displayed at frontend in subscription page.', 'armember-membership' ); ?></span>
							<div class="arm_setup_option_field arm_setup_forms_container">
								<div class="arm_setup_option_label" ><?php esc_html_e( 'Select Signup / Registration Form', 'armember-membership' ); ?></div>
								<div class="arm_setup_option_input arm_setup_forms_container">
									<div class="arm_setup_module_box">
										<input type="hidden" id="arm_form_select_box" name="setup_data[setup_modules][modules][forms]" value="<?php echo esc_attr($selectedForm); ?>" data-msg-required="<?php esc_attr_e( 'Please select signup / registration form.', 'armember-membership' ); ?>" /> <?php //phpcs:ignore ?>
										<dl class="arm_selectbox">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_form_select_box" class="arm_setup_form_options_list">
													<?php echo $arm_membership_setup->arm_setup_form_list_options(); //phpcs:ignore ?>
												</ul>
											</dd>
										</dl>
										<?php 
										$arm_add_new_form_btn='';
										echo apply_filters('arm_pro_add_new_register_form_btn_field',$arm_add_new_form_btn); //phpcs:ignore
										?>
										
										<span class="arm_setup_error_msg"></span>
									</div>
									
								</div>
							</div>
							<span class="arm_info_text arm_notice_text arm_width_100_pct"><?php esc_html_e( 'Form will be skipped automatically when user is logged in.', 'armember-membership' ); ?></span>
							<div class="arm_solid_divider"></div>
							<div class="arm_setup_section_title arm_margin_top_20 arm_margin_bottom_24"><?php esc_html_e( 'Form inputs', 'armember-membership' ); ?></div>
							<div class="arm_form_input_section_field_grid">

							
								<div class="arm_setup_option_field arm_form_input_section_fields">
									<div class="arm_setup_option_label"><?php esc_html_e( 'Submit Button Label', 'armember-membership' ); ?></div>
									<div class="arm_setup_option_input">
										<div class="arm_setup_module_box">
											<input type="text" name="setup_data[setup_labels][button_labels][submit]" value="<?php echo ( isset( $button_labels['submit'] ) ) ? esc_attr( stripslashes( $button_labels['submit'] ) ) : ''; ?>">
											<span class="arm_setup_error_msg"></span>
										</div>
									</div>
								</div>
								<?php
								if($ARMemberLite->is_arm_pro_active)
								{
									$arm_pro_additional_fields_for_other_options = '';
									echo apply_filters('arm_pro_additional_fields_for_other_options',$arm_pro_additional_fields_for_other_options,$setup_data,$setup_modules,$button_labels); //phpcs:ignore
								}
								else
								{?>
							
								<div class="arm_setup_option_field arm_form_input_section_fields">
									<div class="arm_setup_option_label"><?php esc_html_e( 'Payment Section Title', 'armember-membership' ); ?></div>
									<div class="arm_setup_option_input">
										<div class="arm_setup_module_box">
											<input type="text" name="setup_data[setup_labels][payment_section_title]" value="<?php echo isset( $setup_data['setup_labels']['payment_section_title'] ) ? esc_attr(stripslashes_deep( $setup_data['setup_labels']['payment_section_title'] )) : esc_html__( 'Select Your Payment Gateway', 'armember-membership' ); ?>">
											<span class="arm_setup_error_msg"></span>
										</div>
									</div>
								</div>
								<?php
								}
								$payment_gateways = $arm_payment_gateways->arm_get_all_payment_gateways_for_setup();
								if ( ! empty( $payment_gateways ) && is_array( $payment_gateways ) ) {
									foreach ( $payment_gateways as $pgkey => $gateway ) {
										$default_label       = $gateway['gateway_name'];
										$gateway_field_label = ( isset( $setup_data['setup_labels']['payment_gateway_labels'][ $pgkey ] ) && $setup_data['setup_labels']['payment_gateway_labels'][ $pgkey ] != '' ) ? $setup_data['setup_labels']['payment_gateway_labels'][ $pgkey ] : $default_label;
										?>
								<div class="arm_setup_option_field arm_form_input_section_fields">
									<div class="arm_setup_option_label"><?php echo esc_html($default_label) . ' ' . esc_html__( 'Label', 'armember-membership' ); ?></div>
									<div class="arm_setup_option_input">
										<div class="arm_setup_module_box">
											<input type="text" name="setup_data[setup_labels][payment_gateway_labels][<?php echo esc_attr($pgkey); ?>]" value="<?php echo esc_attr(stripslashes_deep( $gateway_field_label ) ); //phpcs:ignore ?>" />
											<span class="arm_setup_error_msg"></span>
										</div>
									</div>
								</div>
										<?php
									}
								}
								?>
								<div class="arm_setup_option_field arm_form_input_section_fields">
									<div class="arm_setup_option_label"><?php esc_html_e( 'Payment Mode Selection Title', 'armember-membership' ); ?></div>
									<div class="arm_setup_option_input">
										<div class="arm_setup_module_box">
											<input type="text" name="setup_data[setup_labels][payment_mode_selection]" value="<?php echo isset( $setup_data['setup_labels']['payment_mode_selection'] ) ? esc_attr(stripslashes_deep( $setup_data['setup_labels']['payment_mode_selection'] )) : esc_html__( 'How you want to pay?', 'armember-membership' ); //phpcs:ignore ?>">
											<span class="arm_setup_error_msg"></span>
										</div>
									</div>
								</div>
								<div class="arm_setup_option_field arm_form_input_section_fields">
									<div class="arm_setup_option_label"><?php esc_html_e( 'Automatic Subscription Label', 'armember-membership' ); ?></div>
									<div class="arm_setup_option_input">
										<div class="arm_setup_module_box">
											<input type="text" name="setup_data[setup_labels][automatic_subscription]" value="<?php echo isset( $setup_data['setup_labels']['automatic_subscription'] ) ? esc_attr(stripslashes_deep( $setup_data['setup_labels']['automatic_subscription'] )) : esc_html__( 'Auto Debit Payment', 'armember-membership' ); //phpcs:ignore ?>">
											<span class="arm_setup_error_msg"></span>
										</div>
									</div>
								</div>
								<div class="arm_setup_option_field arm_form_input_section_fields">
									<div class="arm_setup_option_label"><?php esc_html_e( 'Semi Automatic Subscription Label', 'armember-membership' ); ?></div>
									<div class="arm_setup_option_input">
										<div class="arm_setup_module_box">
											<input type="text" name="setup_data[setup_labels][semi_automatic_subscription]" value="<?php echo isset( $setup_data['setup_labels']['semi_automatic_subscription'] ) ? esc_attr(stripslashes_deep( $setup_data['setup_labels']['semi_automatic_subscription'] )) : esc_html__( 'Manual Payment', 'armember-membership' ); //phpcs:ignore ?>">
											<span class="arm_setup_error_msg"></span>
										</div>
									</div>
								</div>
								<?php
									$arm_pro_other_form_setup_form = '';
									echo apply_filters('arm_credit_card_image_section',$arm_pro_other_form_setup_form,$setup_data); //phpcs:ignore
									do_action( 'arm_add_configuration_option', $button_labels );
								?>
							</div>
							<div class="arm_solid_divider"></div>
							<div class="arm_setup_section_title arm_margin_top_20 arm_margin_bottom_24"><?php esc_html_e( 'Other', 'armember-membership' ); ?></div>
                            
							<div class="arm_setup_option_field arm_setup_summary_container">
								<div class="arm_setup_option_label">
									<?php esc_html_e( 'Summary Text', 'armember-membership' ); ?>
									<div class="arm_position_float_right">
										<input type="button" class="arm_get_shortcode_model" value="<?php esc_html_e('View Shortcode','armember-membership')?>">
									</div>
								</div>
								<div class="arm_setup_option_input">
									<div class="arm_setup_module_box">
										<?php
										$payment_summery = '<div>Payment Summary</div><br/><div>Your currently selected plan : <strong>[PLAN_NAME]</strong>,  Plan Amount : <strong>[PLAN_AMOUNT]</strong> </div><div>Coupon Discount Amount : <strong>[DISCOUNT_AMOUNT]</strong>, Final Payable Amount: <strong>[PAYABLE_AMOUNT]</strong> </div>';

										$payment_summery = apply_filters('arm_payment_setup_summary_filter',$payment_summery);
										
										$summary_text_content = isset($setup_data['setup_labels']['summary_text']) ? stripslashes($setup_data['setup_labels']['summary_text']) : $payment_summery;
                                            $arm_message_editor = array(
                                                'textarea_name' => 'setup_data[setup_labels][summary_text]',
                                                'editor_class' => 'arm_setup_summary_text',
                                                'media_buttons' => false,
                                                'textarea_rows' => 5,
                                                'tinymce' => false,
                                            );
                                        ?>
										<div class="arm_setup_summary_text_container">
                                        <?php wp_editor($summary_text_content, 'arm_setup_summary_text', $arm_message_editor); ?>
                                        </div>
										<span class="arm_setup_error_msg"></span>
									</div>
								</div>
							</div>
							<div class="arm_solid_divider"></div>
						</div>
						<?php if($ARMemberLite->is_arm_pro_active)
						{
							$arm_twp_setup_section = '';
							echo apply_filters( 'arm_pro_two_step_feature_section', $arm_twp_setup_section, $setup_data,$setup_modules ); //phpcs:ignore
						}?>
						<div class="armclear"></div>
						<div class="arm_setup_section_title"><span class="arm_title_round"><?php echo ($ARMemberLite->is_arm_pro_active) ? 5: 4; ?></span><?php esc_html_e( 'Styling & Formatting', 'armember-membership' ); ?></div>
						
						<div class="arm_setup_section_body arm_basic_setup_body">
							<div class="arm_setup_section_title arm_margin_top_20 arm_margin_bottom_24 arm_font_size_16"><?php esc_html_e( 'Basic', 'armember-membership' ); ?></div>
							<div class="arm_form_input_section_field_grid arm_width_100_pct">
                            <?php echo $arm_setup_preview_split_string; //phpcs:ignore 
							if($ARMemberLite->is_arm_pro_active)
							{
								$arm_styling_plan_skin_section = '';
								echo apply_filters( 'arm_styling_plan_skin_section', $arm_styling_plan_skin_section, $setup_data,$setup_modules ); //phpcs:ignore
							}
							else
							{
							?>
								
								<input type='hidden' id="arm_setup_plan_skin" name="setup_data[setup_modules][style][plan_skin]" class="arm_setup_plan_skin" value="skin3" />
							<?php 
							}
							?>

							<?php
							$arm_is_pro_two_step_enabled_style  = '';
							if($ARMemberLite->is_arm_pro_active)
							{
								$arm_is_pro_two_step_enabled_style = apply_filters('arm_two_step_feature_enabled',$arm_is_pro_two_step_enabled_style,$setup_modules); //phpcs:ignore
							}
							?>
							<div class="arm_setup_option_field plan_area_position" <?php echo $arm_is_pro_two_step_enabled_style; //phpcs:ignore?>>
								 <div class="arm_setup_option_label"><?php esc_html_e( 'Plan Selection Area Position', 'armember-membership' ); ?></div>
								 <div class="arm_setup_option_input">
									 <input type='hidden' id="arm_setup_plan_area_position" name="setup_data[setup_modules][style][plan_area_position]" class="arm_setup_plan_area_position" value="<?php echo ( isset( $setup_modules['style']['plan_area_position'] ) ) ? esc_attr($setup_modules['style']['plan_area_position']) : 'before'; ?>" />

									 <dl class="arm_selectbox column_level_dd">
										 <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
										 <dd>
											 <ul data-id="arm_setup_plan_area_position" class="arm_setup_plan_area_position">
												 <li data-label="<?php esc_attr_e( 'Before Registration Form', 'armember-membership' ); ?>" data-value="before"><span class="arm_selectbox_option_list"><?php esc_html_e( 'Before Registration Form', 'armember-membership' ); ?></span></li>
												 <li data-label="<?php esc_attr_e( 'After Registration Form', 'armember-membership' ); ?>" data-value="after"><span class="arm_selectbox_option_list"><?php esc_html_e( 'After Registration Form', 'armember-membership' ); ?></span></li>
											 </ul>
										
										 </dd>
									 </dl>
								 </div>
							 </div> 
							 
							<?php
							if($ARMemberLite->is_arm_pro_active){
								$arm_other_option_payment_skins = '';
								echo apply_filters('arm_other_option_payment_gateway_skins',$arm_other_option_payment_skins,$setup_data,$setup_modules); //phpcs:ignore
							}
							else{?>
								<input type='hidden' id="arm_setup_gateway_skin" name="setup_data[setup_modules][style][gateway_skin]" class="arm_setup_gateway_skin" value="radio" />
							<?php }
							?>
							</div>
							<div class="arm_setup_option_field arm_hide_current_plan_section arm_width_100_pct">
								<div class="arm_setup_option_label"></div>
								<div class="arm_setup_option_input">
									<div class="armswitch arm_global_setting_switch">
										
										<?php $is_hide_current_plans = ( isset( $setup_modules['style']['hide_current_plans'] ) ) ? $setup_modules['style']['hide_current_plans'] : 0; ?>
										<input id="arm_setup_hide_current_plans" class="armswitch_input" <?php checked( $is_hide_current_plans, '1' ); ?> value="1" name="setup_data[setup_modules][style][hide_current_plans]" type="checkbox">
										<label class="armswitch_label" for="arm_setup_hide_current_plans"></label>
									</div>
									<label class="arm_global_setting_switch_label" for="arm_setup_hide_current_plans"><?php esc_html_e( 'Hide Current Plans', 'armember-membership' ); ?></label>
									<label class="arm_global_setting_switch_label_hint"><?php esc_html_e( 'Hide plans which are already owned by user', 'armember-membership' ); ?></label>
								</div>
							</div>

							
							<div class="arm_setup_option_field hide_plan_selection arm_width_100_pct">
								<div class="arm_setup_option_label"></div>
								<div class="arm_setup_option_input">
									<div class="armswitch arm_global_setting_switch">
										<?php 
										$is_hide_plans = ( isset( $setup_modules['style']['hide_plans'] ) ) ? $setup_modules['style']['hide_plans'] : 0; ?>
										<input id="arm_setup_hide_plans" class="armswitch_input" <?php checked( $is_hide_plans, '1' ); ?> value="1" name="setup_data[setup_modules][style][hide_plans]" type="checkbox">
										<label class="armswitch_label" for="arm_setup_hide_plans"></label>
									</div>
									<label class="arm_global_setting_switch_label" for="arm_setup_hide_plans"><?php esc_html_e( 'Hide Plan Selection Area', 'armember-membership' ); ?></label>
									
								</div>
							</div>
							<div class="arm_solid_divider"></div>
							<div class="arm_setup_section_title arm_margin_top_20 arm_margin_bottom_24 arm_font_size_16"><?php esc_html_e( 'Layouts', 'armember-membership' ); ?></div>
							<div class="arm_form_input_section_field_grid arm_width_100_pct">
								<div class="arm_setup_option_field">
									<div class="arm_setup_option_label"><?php esc_html_e( 'Content Width', 'armember-membership' ); ?><i class="arm_helptip_icon armfa armfa-question-circle" title='<?php esc_html_e( 'Leave blank for auto width', 'armember-membership' ); ?>'></i></div>
									<div class="arm_setup_option_input">
										<?php
										$setup_content_width = ( $setup_modules['style']['content_width'] == 0 && $setup_modules['style']['content_width'] != '' ) ? 800 : $setup_modules['style']['content_width'];
										?>
										<input type="text" name="setup_data[setup_modules][style][content_width]" value="<?php echo intval($setup_content_width); ?>" class="arm_setup_shortcode_form_width">
										<span class="arm_plan_currency_symbol arm_plan_currency_symbol_suffix">px</span>
									</div>
								</div>
								<div class="arm_setup_option_field">
									<div class="arm_setup_option_label"><?php esc_html_e( 'Form Position', 'armember-membership' ); ?></div>
									<div class="arm_setup_option_input arm_padding_top_15">
										<?php
										$formPosition = ( isset( $setup_modules['style']['form_position'] ) && ! empty( $setup_modules['style']['form_position'] ) ) ? $setup_modules['style']['form_position'] : 'left';
										?>
										<div class="arm_column_layout_types_container arm_form_align">
											<label class="arm_flex arm_active_label">
												<img class="arm_inactive_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL?>/arm_align_left.svg" alt="">
												<img class="arm_active_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL?>/arm_align_left_hover.svg" alt="">
												<input type="radio" class="arm_iradio arm_setup_form_position_radio" name="setup_data[setup_modules][style][form_position]" value="left" <?php checked( $formPosition, 'left', true ); ?> id="arm_setup_form_position_left"><span><?php esc_html_e('Left','armember-membership');?></span>
											</label>
											<label class="arm_flex arm_active_label">
												<img class="arm_inactive_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL?>/arm_align_center.svg" alt="">
												<img class="arm_active_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL?>/arm_align_center_hover.svg" alt="">
												<input type="radio" class="arm_iradio arm_setup_form_position_radio" name="setup_data[setup_modules][style][form_position]" value="center" <?php checked( $formPosition, 'center', true ); ?> id="arm_setup_form_position_center"><span><?php esc_html_e('Center','armember-membership');?></span>
											</label>
	
											<label class="arm_flex arm_active_label">
												<img class="arm_inactive_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL?>/arm_align_right.svg" alt="">
												<img class="arm_active_img" src="<?php echo MEMBERSHIPLITE_IMAGES_URL?>/arm_align_right_hover.svg" alt="">
												<input type="radio" class="arm_iradio arm_setup_form_position_radio" name="setup_data[setup_modules][style][form_position]" value="right" <?php checked( $formPosition, 'right', true ); ?> id="arm_setup_form_position_right"><span><?php esc_html_e('Right','armember-membership');?></span>
											</label>
										</div>
										
									</div>
								</div>
							</div>
							<div class="arm_solid_divider"></div>
							<div class="arm_form_input_section_field_grid arm_width_100_pct">
								<?php echo $arm_setup_preview_split_string; //phpcs:ignore
								 $arm_class = "";
								 if($ARMemberLite->is_arm_pro_active)
								 {
									$setup_modules['style']['plan_skin'] = 'skin3';
								 }
								 if($setup_modules['style']['plan_skin'] == 'skin6')
								 {
									 $arm_class = 'arm_hide';
									 $setup_modules['plans_columns'] = $setup_modules['cycle_columns'] = $setup_modules['gateways_columns'] = 1;
								 }
								 ?>
								<div class="arm_setup_option_field">
									<div class="arm_setup_option_label arm_padding_top_12">
										<?php esc_html_e( 'Select Plan Layout', 'armember-membership' ); ?>
									</div>
									<div class="arm_setup_option_input">
										<?php $planColumnType = ( ! empty( $setup_modules['plans_columns'] ) ) ? $setup_modules['plans_columns'] : '3'; ?>
										<div class="arm_column_layout_types_container <?php echo esc_attr($arm_class);?>">
											<label class="<?php echo ( $planColumnType == 1 ) ? 'arm_active_label' : ''; ?>">
												<img class="arm_inactive_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/single_column.png" alt=""/>
												<img class="arm_active_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/single_column_hover.png" alt=""/>
												<input type="radio" name="setup_data[setup_modules][plans_columns]" value="1" class="arm_column_layout_type_radio" data-module="plans" <?php checked( $planColumnType, 1, true ); ?>>
											</label>
											<label class="<?php echo ( $planColumnType == 2 ) ? 'arm_active_label' : ''; ?>">
												<img class="arm_inactive_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/two_column.png" alt=""/>
												<img class="arm_active_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/two_column_hover.png" alt=""/>
												<input type="radio" name="setup_data[setup_modules][plans_columns]" value="2" class="arm_column_layout_type_radio" data-module="plans" <?php checked( $planColumnType, 2, true ); ?>>
											</label>
											<label class="<?php echo ( $planColumnType == 3 ) ? 'arm_active_label' : ''; ?>">
												<img class="arm_inactive_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/three_column.png" alt=""/>
												<img class="arm_active_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/three_column_hover.png" alt=""/>
												<input type="radio" name="setup_data[setup_modules][plans_columns]" value="3" class="arm_column_layout_type_radio" data-module="plans" <?php checked( $planColumnType, 3, true ); ?>>
											</label>
											<label class="<?php echo ( $planColumnType == 4 ) ? 'arm_active_label' : ''; ?>">
												<img class="arm_inactive_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/four_column.png" alt=""/>
												<img class="arm_active_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/four_column_hover.png" alt=""/>
												<input type="radio" name="setup_data[setup_modules][plans_columns]" value="4" class="arm_column_layout_type_radio" data-module="plans" <?php checked( $planColumnType, 4, true ); ?>>
											</label>
											<div class="armclear"></div>
										</div>
										<ul class="arm_membership_setup_sub_ul arm_setup_plans_ul arm_setup_plan_layout_list arm_max_width_785 arm_column_<?php echo esc_attr($planColumnType); //phpcs:ignore ?>" style="<?php echo ( empty( $selectedPlans ) ) ? 'display:none;' : ''; ?>">
											<?php echo $arm_membership_setup->arm_setup_plan_layout_list_options( $planOrders, $selectedPlans, $user_selected_plan ); //phpcs:ignore ?>
										</ul>
									</div>
								</div>
								<?php 
								$arm_plan_cycle_skin = '';
								echo apply_filters( 'arm_plan_cycle_skin_types', $arm_plan_cycle_skin, $setup_data,$setup_modules,$arm_class); //phpcs:ignore
								?>
														
								<div class="arm_setup_option_field">
									<div class="arm_setup_option_label arm_padding_top_12"><?php esc_html_e( 'Select Payment Gateway Layout', 'armember-membership' ); ?></div>
									<div class="arm_setup_option_input">
										<?php
										$gatewayColumnType = ( ! empty( $setup_modules['gateways_columns'] ) ) ? $setup_modules['gateways_columns'] : '1';
										$orderGateways     = $arm_membership_setup->arm_sort_module_by_order( $allGateways, $gatewayOrders );
										?>
										<div class="arm_column_layout_types_container <?php echo esc_attr($arm_class);?>">
											<label class="<?php echo ( $gatewayColumnType == 1 ) ? 'arm_active_label' : ''; ?>">
												<img class="arm_inactive_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/single_column.png" alt=""/>
												<img class="arm_active_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/single_column_hover.png" alt=""/>
												<input type="radio" name="setup_data[setup_modules][gateways_columns]" value="1" class="arm_column_layout_type_radio" data-module="gateways" <?php checked( $gatewayColumnType, 1, true ); ?>>
											</label>
											<label class="<?php echo ( $gatewayColumnType == 2 ) ? 'arm_active_label' : ''; ?>">
												<img class="arm_inactive_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/two_column.png" alt=""/>
												<img class="arm_active_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/two_column_hover.png" alt=""/>
												<input type="radio" name="setup_data[setup_modules][gateways_columns]" value="2" class="arm_column_layout_type_radio" data-module="gateways" <?php checked( $gatewayColumnType, 2, true ); ?>>
											</label>
											<label class="<?php echo ( $gatewayColumnType == 3 ) ? 'arm_active_label' : ''; ?>">
												<img class="arm_inactive_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/three_column.png" alt=""/>
												<img class="arm_active_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/three_column_hover.png" alt=""/>
												<input type="radio" name="setup_data[setup_modules][gateways_columns]" value="3" class="arm_column_layout_type_radio" data-module="gateways" <?php checked( $gatewayColumnType, 3, true ); ?>>
											</label>
											<label class="<?php echo ( $gatewayColumnType == 4 ) ? 'arm_active_label' : ''; ?>">
												<img class="arm_inactive_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/four_column.png" alt=""/>
												<img class="arm_active_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore	 ?>/four_column_hover.png" alt=""/>
												<input type="radio" name="setup_data[setup_modules][gateways_columns]" value="4" class="arm_column_layout_type_radio" data-module="gateways" <?php checked( $gatewayColumnType, 4, true ); ?>>
											</label>
											<div class="armclear"></div>
										</div>
										<ul class="arm_membership_setup_sub_ul arm_setup_gateways_ul arm_column_<?php echo intval($gatewayColumnType); ?>" style="<?php echo ( empty( $selectedPlans ) && empty( $arm_setup_type ) ) ? 'display:none;' : ''; ?>">
										<?php if ( ! empty( $orderGateways ) ) : ?>
											<?php
											$gi = 1;
											foreach ( $orderGateways as $key => $pg ) :
												?>
												<?php
												$gateweyClass  = 'arm_membership_setup_gateways_li_' . $key;
												$gateweyClass .= ( ( in_array( $key, $selectedGateways ) && ( isset( $pg['status'] ) && $pg['status'] == '1' ) ) ? '' : ' hidden_section ' );
												?>
												<li class="arm_membership_setup_sub_li arm_membership_setup_gateways_li <?php echo esc_attr($gateweyClass); ?>">
													<div class="arm_membership_setup_sortable_icon"></div>
													<span><?php echo esc_html($pg['gateway_name']); ?></span>
													<input type="hidden" name="setup_data[setup_modules][modules][gateways_order][<?php echo esc_attr($key); ?>]" value="<?php echo intval($gi); ?>" class="arm_module_options_order">
												</li>
												<?php
												$gi++;
											endforeach;
											?>
										<?php endif; ?>
										</ul>
									</div>
								</div>
							</div>
                            <?php echo $arm_setup_preview_split_string; //phpcs:ignore 
								$arm_setup_additional_form_fields = '';
								echo apply_filters('arm_setup_form_styling_additional_fields',$arm_setup_additional_form_fields,$setup_data,$setup_modules); //phpcs:ignore
			    
							?>
						</div>
					</div>
					<div class="armclear"></div>
					<!--<div class="arm_divider"></div>-->
					<div class="arm_submit_btn_container">
						<div class="arm_membership_setup_shortcode_box">
							<span class="arm_font_size_15 arm_margin_right_10 arm-black-350"><?php esc_html_e('Shortcode','armember-membership');?></span>
							<span class="arm_shortcode_text">
								<span style="display: block;font-size: 12px;line-height: normal;text-align: left;"><?php esc_html_e('Shortcode will be display here once you save current setup.','armember-membership');?></span>
							</span>
						</div>
						<div class="arm_membership_setup_action_box">
							<?php
								$arm_is_pro_btn_class = '';
								if($ARMemberLite->is_arm_pro_active)
								{
									$arm_is_pro_btn_class = 'pro';	
								}
	
							?>
							<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL.'/arm_loader.gif' //phpcs:ignore?>" id="arm_loader_img_add_setup_loader" class="arm_loader_img arm_submit_btn_loader" style="display: none;" width="20" height="20" />
							<a href="javascript:void(0)" class="arm_setup_preview_btn armemailaddbtn <?php echo esc_attr($arm_is_pro_btn_class); ?>"><?php esc_html_e( 'Preview', 'armember-membership' ); ?></a>
							<a class="arm_cancel_btn arm_setup_cancel_btn" href="javascript:void(0)"><?php esc_html_e( 'Close', 'armember-membership' ); ?></a>
							<button class="arm_save_btn arm_setup_save_btn" name="SetupSubmit" type="submit"><?php esc_html_e( 'Save', 'armember-membership' ); ?></button>
						</div>
					</div>
				</div>
                <?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr( $wpnonce );?>"/>
                <?php do_action("arm_after_membership_setup_page_form"); ?>
			</form>
		</div>
	</div>
	<div class="arm_custom_css_detail_container"></div>
</div>
<div class="popup_wrapper arm_preview_setup_shortcode_popup_wrapper">
	<div class="popup_wrapper_inner">
		<div class="popup_header page_title">
			<span class="popup_close_btn arm_popup_close_btn arm_preview_setup_shortcode_close_btn"></span>
			<span class="add_rule_content"><?php esc_html_e( 'Preview', 'armember-membership' ); ?></span>
		</div>
		<div class="popup_content_text arm_setup_shortcode_html_wrapper">
			<div class="arm_setup_shortcode_html"></div>
			<div class="arm_loading_grid arm_setup_preview_loader" style="display: none;"><?php $arm_loader = $arm_common_lite->arm_loader_img_func();
				echo $arm_loader; //phpcs:ignore ?></div>
		</div>
		<div class="armclear"></div>
	</div>
</div>
<?php
		/* **********./Begin Bulk Delete Plan Popup/.********** */
		$plan_skin_change_content   = '<span class="arm_confirm_text">' . esc_html__( 'Please confirm that while changing skin, All colors will be reset to default.', 'armember-membership' ) . '</span>';
		$plan_skin_change_content  .= '<input type="hidden" value="false" id="bulk_delete_flag"/>';
		$plan_skin_change_popup_arg = array(
			'id'             => 'plan_skin_change_message',
			'class'          => 'plan_skin_change_message arm_delete_bulk_action_message ',
			'title'          => esc_html__( 'Change Plan Skin', 'armember-membership' ),
			'content'        => $plan_skin_change_content,
			'button_id'      => 'plan_skin_change_ok_btn',
			'ok_btn_class'      => 'arm_bulk_change_member_ok_btn',
			'button_onclick' => 'plan_skin_change();'
		);
		echo $arm_global_settings->arm_get_bpopup_html( $plan_skin_change_popup_arg ); //phpcs:ignore
		/* **********./End Bulk Delete Plan Popup/.********** */

		$armHomeUrl = ARMLITE_HOME_URL;
		$armHomeUrl = $arm_global_settings->add_query_arg( 'arm_setup_preview', '1', $armHomeUrl );
		?>
<script type="text/javascript">
var setupPreviewUrl = '<?php echo $armHomeUrl; //phpcs:ignore ?>';
jQuery(window).on("load", function(){
	arm_MembershipSetup_init();
});
function arm_setup_skin_default_color_array(){
	var arm_setup_skin_array;
	arm_setup_skin_array = '<?php echo wp_json_encode( $arm_membership_setup->arm_setup_skin_default_color_array() ); ?>';
	return arm_setup_skin_array;
}
var ARM_REMOVE_IMAGE_ICON = '<?php echo MEMBERSHIPLITE_IMAGES_URL?>/delete.svg';
var ARM_REMOVE_IMAGE_ICON_HOVER = '<?php echo MEMBERSHIPLITE_IMAGES_URL?>/delete_hover.svg';
</script>

<?php
$plan_skin_change_content = '<div class="arm_setup_option_input">
	<div class="arm_setup_module_box">
		<div class="arm_setup_summary_tags">
			<ul>';			
				if($ARMemberLite->is_arm_pro_active)
				{
					$arm_summary_shortcode = '';
					$plan_skin_change_content .= apply_filters('arm_setup_summary_content_shortcode',$arm_summary_shortcode); //phpcs:ignore
				}
				else
				{
				
					$plan_skin_change_content .= '<li>
					<div class="arm_shortcode_text arm_form_shortcode_box">
						<span class="armCopyText">[PLAN_NAME]</span>
						<span class="arm_click_to_copy_text" data-code="[PLAN_NAME]">'.esc_html__('Click to copy','armember-membership').'</span>
						<span class="arm_copied_text"><img src="'.MEMBERSHIPLITE_IMAGES_URL.'/copied_ok.png" alt="ok">'.esc_html__('Code Copied','armember-membership').'</span>
					</div>
					'. esc_html__( "This will be replaced with selected plan's title.", 'armember-membership' ).'</li>
					<li>
					<div class="arm_shortcode_text arm_form_shortcode_box">
						<span class="armCopyText">[PLAN_AMOUNT]</span>
						<span class="arm_click_to_copy_text" data-code="[PLAN_AMOUNT]">'.esc_html__('Click to copy','armember-membership').'</span>
						<span class="arm_copied_text"><img src="'.MEMBERSHIPLITE_IMAGES_URL.'/copied_ok.png" alt="ok">'.esc_html__('Code Copied','armember-membership').'</span>
					</div>
					 - '. esc_html__( "This will be replaced with selected plan's amount.", 'armember-membership' ).'
					</li>
					<li>
					<div class="arm_shortcode_text arm_form_shortcode_box">
						<span class="armCopyText">[PAYABLE_AMOUNT]</span>
						<span class="arm_click_to_copy_text" data-code="[PAYABLE_AMOUNT]">'.esc_html__('Click to copy','armember-membership').'</span>
						<span class="arm_copied_text"><img src="'.MEMBERSHIPLITE_IMAGES_URL.'/copied_ok.png" alt="ok">'.esc_html__('Code Copied','armember-membership').'</span>
					</div>
					'. esc_html__( 'This will be replaced with final payable amount.', 'armember-membership' ).'</li>
					<li>
					<div class="arm_shortcode_text arm_form_shortcode_box">
						<span class="armCopyText">[TRIAL_AMOUNT]</span>
						<span class="arm_click_to_copy_text" data-code="[TRIAL_AMOUNT]">'.esc_html__('Click to copy','armember-membership').'</span>
						<span class="arm_copied_text"><img src="'.MEMBERSHIPLITE_IMAGES_URL.'/copied_ok.png" alt="ok">'.esc_html__('Code Copied','armember-membership').'</span>
					</div>
					'. esc_html__( "This will be replaced with plan's trial period amount.", 'armember-membership' ).'</li>';
				}
			$plan_skin_change_content .='</ul>
		</div>	
	</div>
</div>';
$plan_skin_change_popup_arg = array(
	'id'             => 'arm_summary_shortcode_popup',
	'class'          => 'arm_summary_shortcode_popup',
	'title'          => esc_html__( 'Shortcode', 'armember-membership' ),
	'content'        => $plan_skin_change_content,
	'close_icon' 	 => true
);
echo $arm_global_settings->arm_get_bpopup_html( $plan_skin_change_popup_arg ); //phpcs:ignore
?>