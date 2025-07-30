<?php

global $wpdb,$ARMemberLite;

$profile_template    = isset( $_REQUEST['template'] ) ? sanitize_text_field(htmlspecialchars( $_REQUEST['template'] )) : 'profiletemplate3'; //phpcs:ignore
$profile_action = htmlspecialchars(sanitize_text_field($_REQUEST['action']));//phpcs:ignore
$default_cover_photo = 0;

if ( ! wp_script_is( 'arm_admin_file_upload_js', 'enqueued' ) ) {
	wp_enqueue_script( 'arm_admin_file_upload_js' );
}

wp_enqueue_style( 'arm_bootstrap_all_css' );

switch ( $profile_template ) {
	case 1:
		$temp_slug = 'profiletemplate1';
		break;

	case 2:
		$temp_slug = 'profiletemplate2';
		break;

	case 3:
		$temp_slug = 'profiletemplate3';
		break;

	case 4:
		$temp_slug = 'profiletemplate4';
		break;

	case 5:
		$temp_slug = 'profiletemplate5';
		break;

	default:
		$temp_slug = 'profiletemplate1';
		break;
}

global $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_members_directory, $arm_subscription_plans, $arm_member_forms;
$member_templates  = $arm_members_directory->arm_get_all_member_templates();
$defaultTemplates  = $arm_members_directory->arm_default_member_templates();
$tempColorSchemes  = $arm_members_directory->getTemplateColorSchemes();
$subs_data         = $arm_subscription_plans->arm_get_all_subscription_plans( 'arm_subscription_plan_id, arm_subscription_plan_name' );
$tempColorSchemes  = $arm_members_directory->getTemplateColorSchemes();
$tempColorSchemes1 = $arm_members_directory->getTemplateColorSchemes1();
$general_settings = $arm_global_settings->global_settings;
$enable_crop = isset($general_settings['enable_crop']) ? $general_settings['enable_crop'] : 0;

$profile_templates = array();
foreach ( $defaultTemplates as $key => $template ) {
	if ( $template['arm_type'] == 'profile' ) {
		array_push( $profile_templates, $template );
	}
}
?>
<?php
$arm_profile_before_content = $arm_profile_after_content = '';

$profile_fields_data                   = array();
$profile_fields_data['profile_fields'] = array(
	'user_login' => 'user_login',
	'user_email' => 'user_email',
	'first_name' => 'first_name',
	'last_name'  => 'last_name',
);

$profile_fields_data['label'] = array(
	'user_login' => 'Username',
	'user_email' => 'Email Address',
	'first_name' => 'First Name',
	'last_name'  => 'Last Name',
);

$profile_fields_data['default_values'] = $arm_members_directory->arm_get_profile_dummy_data();

echo "<script type='text/javascript'>";
echo 'function arm_profile_editor_default_data(){';
echo "var profile_default_values = '';";
echo "profile_default_values = '" . wp_json_encode( $profile_fields_data['default_values'] ) . "';";
echo 'return profile_default_values; ';
echo '}';
echo '</script>';

$options = array(
	'pagination'                => 'numeric',
	'show_badges'               => 1,
	'show_joining'              => 1,
	'hide_empty_profile_fields' => 1,
	'color_scheme'              => 'blue',
	'title_color'               => '#1A2538',
	'subtitle_color'            => '#2F3F5C',
	'border_color'              => '#005AEE',
	'button_color'              => '#005AEE',
	'button_font_color'         => '#FFFFFF',
	'tab_bg_color'              => '',
	'tab_link_color'            => '#1A2538',
	'tab_link_hover_color'      => '#005AEE',
	'tab_link_bg_color'         => '',
	'tab_link_hover_bg_color'   => '',
	'link_color'                => '',
	'link_hover_color'          => '',
	'content_font_color'        => '#3E4857',
	'box_bg_color'              => '',
	'title_font'                => array(
		'font_family'     => 'Poppins',
		'font_size'       => '18',
		'font_bold'       => 1,
		'font_italic'     => 0,
		'font_decoration' => '',
	),
	'subtitle_font'             => array(
		'font_family'     => 'Poppins',
		'font_size'       => '15',
		'font_bold'       => 0,
		'font_italic'     => 0,
		'font_decoration' => '',
	),
	'button_font'               => array(
		'font_family'     => 'Poppins',
		'font_size'       => '15',
		'font_bold'       => 0,
		'font_italic'     => 0,
		'font_decoration' => '',
	),
	'tab_link_font'             => array(
		'font_family'     => 'Poppins',
		'font_size'       => '15',
		'font_bold'       => 1,
		'font_italic'     => 0,
		'font_decoration' => '',
	),
	'content_font'              => array(
		'font_family'     => 'Poppins',
		'font_size'       => '15',
		'font_bold'       => 0,
		'font_italic'     => 0,
		'font_decoration' => '',
	),
	'default_cover'             => MEMBERSHIPLITE_IMAGES_URL . '/profile_default_cover.png',
	'custom_css'                => '',
);


$arm_template_title = "";
$display_joining_date = $options['show_joining'];
$display_member_badges = $options['show_badges'];
$display_admin_profile     = 0;
$subscription_plans        = array();
$template_id               = 0;
$is_default_template       = 0;
$hide_empty_profile_fields = 0;
$default_data              = array();
$get_action 			   = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'add_profile'; //phpcs:ignore
$is_rtl = is_rtl();
if ( ( isset( $get_action ) && $get_action == 'edit_profile') || (isset($_GET['action']) && $_GET['action'] == 'duplicate_profile') ){ //phpcs:ignore
	$template_id = !empty( $_GET['id'] ) ? intval( $_GET['id'] ) : ''; //phpcs:ignore
	$data        = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM `' . $ARMemberLite->tbl_arm_member_templates . '` WHERE arm_type = %s and arm_id = %d', 'profile', $template_id ) );//phpcs:ignore --Reason: $tbl_arm_member_templates is a table name. False Positive Alarm
	if ( $data == '' || empty( $data ) ) {
		wp_redirect( admin_url( 'admin.php?page=arm_profiles_directories' ) );
		exit;
	}
    	$arm_template_title = !empty($data->arm_title) ? $data->arm_title : '';
	$subscription_plans        = ( isset( $data->arm_subscription_plan ) && $data->arm_subscription_plan != '' ) ? explode( ',', $data->arm_subscription_plan ) : array();
	$default_data              = $data;
	$temp_slug                 = $data->arm_slug;
	$options                   = maybe_unserialize( $data->arm_options );
	$default_data->arm_options = maybe_unserialize( $options );

	$display_admin_profile = $data->arm_enable_admin_profile;
	$is_default_template   = $data->arm_default;
	if($ARMemberLite->is_arm_pro_active)
	{
		$display_member_badges = isset($options['show_badges']) && $options['show_badges'] != '' ? $options['show_badges'] : 0;
	}
	$display_joining_date                  = isset( $options['show_joining'] ) && $options['show_joining'] != '' ? $options['show_joining'] : 0;
	$default_cover_photo                   = isset( $options['default_cover_photo'] ) && $options['default_cover_photo'] != '' ? $options['default_cover_photo'] : 0;
	$arm_profile_before_content            = $data->arm_html_before_fields;
	$arm_profile_after_content             = $data->arm_html_after_fields;
	$profile_fields_data['profile_fields'] = isset( $options['profile_fields'] ) && $options['profile_fields'] != '' ? $options['profile_fields'] : array();
	$profile_fields_data['label']          = isset( $options['label'] ) && $options['label'] != '' ? $options['label'] : array();
	$hide_empty_profile_fields             = isset( $options['hide_empty_profile_fields'] ) ? $options['hide_empty_profile_fields'] : 1;
}


$_SESSION['arm_file_upload_arr']['profile_cover'] = isset( $options['default_cover'] ) && $options['default_cover'] != '' ? $options['default_cover'] : '';


$options['color_scheme'] = isset( $options['color_scheme'] ) && $options['color_scheme'] != '' ? $options['color_scheme'] : 'blue';

$options = apply_filters( 'arm_profile_default_options_outside', $options );

?>
<div class="wrap arm_page arm_profiles_main_wrapper armPageContainer">
	<div class="arm_toast_container" id="arm_toast_container"></div>
	<div class="content_wrapper arm_profiles_directories_container arm_min_height_500 arm_width_100_pct"  id="content_wrapper" style=" float:left;">
		<div class="page_title"><?php esc_html_e( 'Profiles & Directories', 'armember-membership' ); ?></div>
		<div class="armclear"></div>
		<?php
		$backToListingIcon = MEMBERSHIPLITE_IMAGES_URL . '/back_to_listing_arrow.png';
		if ( $is_rtl ) {
			$backToListingIcon    = MEMBERSHIPLITE_IMAGES_URL . '/back_to_listing_arrow_right.png';
			$arm_profile_form_rtl = 'arm_profile_form_rtl';
		}
		?>
		<input type="hidden" id="arm_default_profile_data" value='<?php echo esc_attr( wp_json_encode( $default_data ) ); ?>' />
		<form name="arm_add_profile_temp_form" class="arm_add_profile_temp_form" id="arm_add_profile_temp_form" onSubmit="return false;" method="POST" action="#">
			<input type="hidden" name="template_options[user_detail_width]" id="arm_user_meta_detail_div" value="">
			<input type="hidden" name="id" id="arm_profile_template_id" value="<?php echo esc_attr($profile_template); ?>">
			<input type="hidden" name="template_id" id="template_id" value="<?php echo intval($template_id); ?>" />
			<input type="hidden" name="arf_profile_action" id="arf_profile_action" value="<?php echo !empty( $get_action ) ? esc_attr($get_action) : 'add_profile'; ?>" />
			<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
			<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
			<div class="arm_sticky_top_belt" id="arm_sticky_top_belt">
				<div class="arm_belt_box arm_template_action_belt">
					<div class="arm_belt_block">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $arm_slugs->profiles_directories ) ); //phpcs:ignore ?>" class="armemailaddbtn"><img src="<?php echo esc_url($backToListingIcon); ?>"/><?php esc_html_e( 'Back to listing', 'armember-membership' ); ?></a>
					</div>
					<div class="arm_belt_block arm_temp_action_btns" align="<?php echo esc_attr( $is_rtl ) ? 'left' : 'right'; ?>">
						<button type="button" class="arm_save_btn arm_add_profile_template_submit" data-type="profile"><?php esc_html_e( 'Save', 'armember-membership' ); ?></button>
					</div>
					<div class="armclear"></div>
				</div>
			</div>
			<div class="arm_belt_box arm_template_action_belt" style="padding: 10px 15px; margin-bottom: 30px;">
				<div class="arm_belt_block arm_vertical_align_middle arm_font_size_20 arm_padding_left_20">
					<?php
					if ( $get_action == 'edit_profile' ) {
						esc_html_e( 'Edit Profile Template', 'armember-membership' );
                    			} else if($_GET['action'] == 'duplicate_profile') { //phpcs:ignore
                        			esc_html_e('Copy Profile Template', 'armember-membership');
					} else {
						esc_html_e( 'Add Profile Template', 'armember-membership' );
					}
					?>
				</div>
				<div class="arm_belt_block arm_temp_action_btns" align="<?php echo esc_attr( $is_rtl ) ? 'left' : 'right'; ?>">
					<button type="button" class="arm_save_btn arm_add_profile_template_submit" data-type="profile"><?php esc_html_e( 'Save', 'armember-membership' ); ?></button>
					<button type="button" class="arm_save_btn arm_add_profile_template_reset" id="arm_add_profile_template_reset" data-type="profile"><?php esc_html_e( 'Reset', 'armember-membership' ); ?></button>
				</div>
				<div class="armclear"></div>
			</div>
			<div class="arm_profile_template_name_div arm_form_fields_wrapper">
				<label class="arm_opt_title"><?php esc_html_e( 'Profile Template Name', 'armember-membership' ); ?></label>
				<input type="text" name="arm_profile_template_name" class="arm_form_input_box" value="<?php echo esc_attr($arm_template_title); ?>">
			</div>
			<div class="arm_profile_editor_left_div">
				<div class="arm_profile_belt">
					<div id="" class="arm_profile_belt_icon desktop selected" title="<?php esc_attr_e( 'Desktop View', 'armember-membership' ); ?>" data-type="desktop"></div>
					<div id="" class="arm_profile_belt_icon tab" title="<?php esc_attr_e( 'Tablet View', 'armember-membership' ); ?>" data-type="tab"></div>
					<div id="" class="arm_profile_belt_icon mobile" title="<?php esc_attr_e( 'Mobile View', 'armember-membership' ); ?>" data-type="mobile"></div>
					<?php
						$arm_additional_profile_settings='';
						echo apply_filters('arm_additional_profile_settings',$arm_additional_profile_settings); //phpcs:ignore
					?>
					<?php
					if(!$ARMemberLite->is_arm_pro_active)
					{
					?>
					<input type="hidden" name="arm_profile_template" value="<?php echo esc_attr($temp_slug); ?>" id="arm_profile_template" />
					<?php }else{
					?>
					<?php
						$arm_additional_form_belt_actions = '';
						echo apply_filters('arm_profile_directories_additional_popup_action',$arm_additional_form_belt_actions,$profile_templates,$temp_slug); //phpcs:ignore
					}
					?>
					
				   

					<div id="arm_profile_font_settings_popup" class="arm_profile_belt_right_icon" title="<?php esc_attr_e( 'Change Font Settings', 'armember-membership' ); ?>">
						<span class="arm_profile_template_belt_icon font_setting" ></span>
						<div class="arm_profile_settings_popup" id="arm_profile_font_settings_popup_div" style="display:none;">
							<div class="arm_profile_font_settings_popup_title">
								<?php esc_html_e( 'Font Settings', 'armember-membership' ); ?>
								<span class='arm_profile_settings_popup_close_button' data-id='arm_profile_font_settings_popup_div'></span>    
							</div>
							<div class="arm_profile_font_settings_popup_inner_div">
								<?php
								$fontOptions = array(
									'title_font'    => esc_html__( 'Title Font', 'armember-membership' ),
									'subtitle_font' => esc_html__( 'Sub Title Font', 'armember-membership' ),
									'content_font'  => esc_html__( 'Content Font', 'armember-membership' ),
								);
								?>
								<?php foreach ( $fontOptions as $key => $value ) : ?>
									<div class="arm_temp_font_opts_box">
										<div class="arm_opt_label"><?php echo esc_html($value); ?></div>
										<div class="arm_temp_font_opts">
											<input type="hidden" id="arm_template_font_family_<?php echo esc_attr($key); ?>" name="template_options[<?php echo esc_attr($key); ?>][font_family]" value="<?php echo ( $get_action == 'edit_profile' && $options[ $key ]['font_family'] != '' ) ? esc_attr($options[ $key ]['font_family']) : 'Helvetica'; ?> "/>
											<dl class="arm_selectbox column_level_dd arm_width_200">
												<dt><span><?php echo ( $get_action == 'edit_profile' ) ? esc_attr($options[ $key ]['font_family']) : 'Helvetica'; ?></span><input type="text" style="display:none;" value="" class="arm_autocomplete" readonly="readonly"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_template_font_family_<?php echo esc_attr($key); ?>"><?php echo $arm_member_forms->arm_fonts_list(); //phpcs:ignore ?></ul>
												</dd>
											</dl>
											<?php
												$fontSize = $options[ $key ]['font_size'];
											?>
											<input type="hidden" id="arm_template_font_size_<?php echo esc_attr($key); ?>" name="template_options[<?php echo esc_attr($key); ?>][font_size]" value="<?php echo esc_attr($fontSize); ?>"/>
											<dl class="arm_selectbox column_level_dd arm_width_90">
												<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete" readonly="readonly"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
												<dd>
													<ul data-id="arm_template_font_size_<?php echo esc_attr($key); ?>">
														<?php for ( $i = 8; $i < 41; $i++ ) : ?>
															<li data-label="<?php echo intval($i); ?> px" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?> px</li>
														<?php endfor; ?>
													</ul>
												</dd>
											</dl>
											<div class="arm_font_style_options arm_template_font_style_options">
												<?php
													$bold_cls      = isset( $options[ $key ]['font_bold'] ) && $options[ $key ]['font_bold'] == 1 ? 'arm_style_active' : '';
													$italic_cls    = isset( $options[ $key ]['font_italic'] ) && $options[ $key ]['font_italic'] == 1 ? 'arm_style_active' : '';
													$underline_cls = isset( $options[ $key ]['font_decoration'] ) && $options[ $key ]['font_decoration'] == 'underline' ? 'arm_style_active' : '';
													$strike_cls    = isset( $options[ $key ]['font_decoration'] ) && $options[ $key ]['font_decoration'] == 'line-through' ? 'arm_style_active' : '';
												?>
												<label class="arm_font_style_label <?php echo esc_attr($bold_cls); ?>" data-value="bold" data-field="arm_template_font_bold_<?php echo esc_attr($key); ?>"><i class="armfa armfa-bold"></i></label>
												<input type="hidden" name="template_options[<?php echo esc_attr($key); ?>][font_bold]" id="arm_template_font_bold_<?php echo esc_attr($key); ?>" class="arm_template_font_bold_<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($options[ $key ]['font_bold']); ?>" />
												<label class="arm_font_style_label <?php echo esc_attr($italic_cls); ?>" data-value="italic" data-field="arm_template_font_italic_<?php echo esc_attr($key); ?>"><i class="armfa armfa-italic"></i></label>
												<input type="hidden" name="template_options[<?php echo esc_attr($key); ?>][font_italic]" id="arm_template_font_italic_<?php echo esc_attr($key); ?>" class="arm_template_font_italic_<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($options[ $key ]['font_italic']); ?>" />
												<label class="arm_font_style_label arm_decoration_label <?php echo esc_attr($underline_cls); ?>" data-value="underline" data-field="arm_template_font_decoration_<?php echo esc_attr($key); ?>"><i class="armfa armfa-underline"></i></label>
												<label class="arm_font_style_label arm_decoration_label  <?php echo esc_attr($strike_cls); ?>" data-value="line-through" data-field="arm_template_font_decoration_<?php echo esc_attr($key); ?>"><i class="armfa armfa-strikethrough"></i></label>
												<input type="hidden" name="template_options[<?php echo esc_attr($key); ?>][font_decoration]" id="arm_template_font_decoration_<?php echo esc_attr($key); ?>" class="arm_template_font_decoration_<?php echo esc_attr($key); ?>" value="" />
											</div>
										</div>
									</div>
								<?php endforeach; ?>
                                				<?php do_action('arm_profile_font_settings_outside',$options); ?>
								<div class="arm_profile_font_settings_popup_footer">
									<button type="button" class="armemailaddbtn" id="arm_profile_font_settings_close"><?php esc_html_e( 'Apply', 'armember-membership' ); ?></button>
								</div>
							</div>
						</div>
					</div>

					<div id="arm_profile_settings_color_popup" class="arm_profile_belt_right_icon" title="<?php esc_html_e( 'Change Color Scheme', 'armember-membership' ); ?>">

						<span class="arm_profile_template_belt_icon color_settings" ></span>
						<div class="arm_profile_settings_popup" id="arm_profile_settings_color_popup_div">

							<div class="arm_profile_clor_scheme_div c_schemes">
								<span class="arm_profile_color_scheme_title">
									<?php esc_html_e( 'Color Scheme', 'armember-membership' ); ?>
									<span class='arm_profile_settings_popup_close_button' data-id='arm_profile_settings_color_popup_div'></span>
								</span>
								<?php foreach ( $tempColorSchemes as $color => $color_opt ) : ?>
									<?php
										$activeClass = isset( $options['color_scheme'] ) && $options['color_scheme'] == $color ? 'arm_color_box_active' : '';
									?>
									<label class="arm_profile_temp_color_scheme_block arm_temp_color_scheme_block_<?php echo esc_attr($color); ?> <?php echo esc_attr($activeClass); ?>">
										<span style="background-color:<?php echo esc_attr($color_opt['button_color']); ?>;"></span>
										<span style="background-color:<?php echo esc_attr($color_opt['tab_bg_color']); ?>;"></span>
										<input type="radio" id="arm_temp_color_radio_<?php echo esc_attr($color); ?>" name="template_options[color_scheme]" value="<?php echo esc_attr($color); ?>" <?php checked( $color, $options['color_scheme'] ); ?> class="arm_temp_color_radio" data-type="profile" />
									</label>
								<?php endforeach; ?>
								<label class="arm_temp_color_scheme_block arm_temp_color_scheme_block_custom <?php echo isset( $options['color_scheme'] ) && $options['color_scheme'] == 'custom' ? 'arm_color_box_active' : ''; ?>">
									<input type="radio" id="arm_temp_color_radio_custom_for_profile" name="template_options[color_scheme]" value="custom" class="arm_temp_color_radio" data-type="profile">
								</label>
								<div class="arm_temp_color_options" id="arm_temp_color_options" style="<?php echo isset( $options['color_scheme'] ) && $options['color_scheme'] == 'custom' ? 'display:block' : 'display:none'; ?>">
									<div class="arm_pdtemp_color_opts">
										<span class="arm_temp_form_label"><?php esc_html_e( 'Title Color', 'armember-membership' ); ?></span>
										<label class="arm_colorpicker_label arm_custom_colorpicker_label" style="background-color:<?php echo esc_attr($options['title_color']); ?>">
											<input type="text" name="template_options[title_color]" id="arm_profile_title_color" class="arm_colorpicker" value="<?php echo esc_attr($options['title_color']); ?>" />
										</label>
									</div>
									<div class="arm_pdtemp_color_opts">
										<span class="arm_temp_form_label"><?php esc_html_e( 'Sub Title Color', 'armember-membership' ); ?></span>
										<label class="arm_colorpicker_label arm_custom_colorpicker_label" style="background-color:<?php echo esc_attr($options['subtitle_color']); ?>">
											<input type="text" name="template_options[subtitle_color]" id="arm_profile_subtitle_color" class="arm_colorpicker" value="<?php echo esc_attr($options['subtitle_color']); ?>" />
										</label>
									</div>
									<div class="arm_pdtemp_color_opts">
										<span class="arm_temp_form_label"><?php esc_html_e( 'Border Color', 'armember-membership' ); ?></span>
										<label class="arm_colorpicker_label arm_custom_colorpicker_label" style="background-color:<?php echo esc_attr($options['border_color']); ?>">
											<input type="text" name="template_options[border_color]" id="arm_profile_border_color" class="arm_colorpicker" value="<?php echo esc_attr($options['border_color']); ?>" />
										</label>
									</div>
									<div class="arm_pdtemp_color_opts">
										<span class="arm_temp_form_label"><?php esc_html_e( 'Body Content Color', 'armember-membership' ); ?></span>
										<label class="arm_colorpicker_label arm_custom_colorpicker_label" style="background-color:<?php echo esc_attr($options['content_font_color']); ?>">
											<input type="text" name="template_options[content_font_color]" id="arm_profile_content_color" class="arm_colorpicker" value="<?php echo esc_attr($options['content_font_color']); ?>" />
										</label>
									</div>
                                    					<?php do_action('arm_profile_color_options_outside',$options); ?>
								</div>
								<div class="arm_temp_color_option_footer">
									<button type="button" class="armemailaddbtn" id="arm_temp_color_option_apply_button"><?php esc_html_e( 'Apply', 'armember-membership' ); ?></button>
								</div>
							</div>
						</div>
					</div></div>
				<?php
				$user_id           = get_current_user_id();
				$current_user_info = get_user_by( 'id', 1 );
				$content           = '';
				$content          .= '<div class="arm_admin_profile_container">
                    <div class="arm_template_container arm_profile_container" id="arm_template_container_wrapper">';
				$content          .= $arm_members_directory->arm_get_profile_editor_template( $temp_slug, $profile_fields_data, $options, $profile_template, false, $arm_profile_before_content, $arm_profile_after_content );
				echo $content     .= '</div></div>'; //phpcs:ignore
				?>
				 
			</div>
			<div class="arm_profile_editor_right_div connectedSortable" id="answers">

				<div id="arm_accordion">
					<ul>
						<?php $arm_active_class_lite = '';
						$class_css = "display:none;";
						if(!$ARMemberLite->is_arm_pro_active)
						{
							$arm_active_class_lite = 'arm_active_section';
							$class_css = '';
						}
						else{?>
						<li class="arm_active_section">
                            <a href="javascript:void(0)" class="arm_accordion_header"><?php esc_html_e('Add New Block', 'armember-membership'); ?>
                                <?php $gf_tooltip = esc_html__("You can add specific HTML before/after profile fields listing section", 'armember-membership'); ?>
                                <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo esc_attr($gf_tooltip); ?>"></i>
                                <i></i></a>
                            <div id="one" class="arm_accordion default">
                                <div class="arm_accordion_inner_title"><?php esc_html_e('Before profile fields', 'armember-membership'); ?></div>
                                <?php
                                $content = "";
                                $editor_id = "arm_before_profile_fields_content";
                                $arguments = array(
                                    'media_buttons' => false,
                                    'textarea_name' => 'arm_before_profile_fields_content',
                                    'textarea_rows' => 10,
                                    'editor_class' => 'arm_accordion_custom_block',
                                    'tinymce' => false,
                                );
                                wp_editor(stripslashes_deep($arm_profile_before_content), $editor_id, $arguments);
                                ?>
                                

                                <div class="arm_accordion_separator"></div>
                                <div class="arm_accordion_inner_title arm_margin_top_20" ><?php esc_html_e('After profile fields', 'armember-membership'); ?></div>
                                <?php
                                $content = "";
                                $editor_id = "arm_after_profile_fields_content";
                                $arguments = array(
                                    'media_buttons' => false,
                                    'textarea_name' => 'arm_after_profile_fields_content',
                                    'textarea_rows' => 10,
                                    'editor_class' => 'arm_accordion_custom_block',
                                    'tinymce' => false,
                                );
                                wp_editor(stripslashes_deep($arm_profile_after_content), $editor_id, $arguments);
                                ?>
                               
                            </div>
                        </li>
						<?php }

						?>
						<li class="<?php echo esc_attr($arm_active_class_lite);?>">
							<a href="javascript:void(0)" class="arm_accordion_header"><?php esc_html_e( 'Profile Fields', 'armember-membership' ); ?>
								<?php $pf_tooltip = esc_html__( 'Select fields that you want to display in profile fields listing section.', 'armember-membership' ); ?>
								<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo esc_attr($pf_tooltip); ?>"></i>
								<i></i></a>
							<div id="two" class="arm_accordion default" data-id="arm_profile_fields_wrapper" style="<?php echo esc_attr($class_css);?>">
								<div class="arm_profile_fields_dropdown">
									<input type="hidden" id="arm_profile_fields" value="" />
									<dl class="arm_selectbox column_level_dd" style="width:96%;">
										<dt><span><?php esc_html_e( 'Select Field', 'armember-membership' ); ?></span><input type="text" style="display:none;" class="arm_autocomplete" readonly="readonly" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
										<dd>
											<ul data-id="arm_profile_fields" style="display: none;">
												<li data-label="<?php esc_attr_e( 'Select Field', 'armember-membership' ); ?>" data-value=""><?php esc_attr_e( 'Select Field', 'armember-membership' ); ?></li>
												<?php
												$dbProfileFields = $arm_members_directory->arm_template_profile_fields();
												foreach ( $dbProfileFields as $fieldMetaKey => $fieldOpt ) {
													if ( empty( $fieldMetaKey ) || $fieldMetaKey == 'user_pass' || in_array( $fieldOpt['type'], array( 'hidden', 'html', 'section', 'rememberme', 'avatar' ) ) ) {
														continue;
													}
													$arm_is_deactive = '';
													if ( in_array( $fieldMetaKey, $profile_fields_data['profile_fields'] ) ) {
														$arm_is_deactive = ' class="arm_deactive" ';
													}
													?>
													<li data-code="<?php echo esc_attr($fieldMetaKey); ?>" data-label="<?php echo esc_attr( stripslashes_deep( $fieldOpt['label']) ); ?>" data-value="<?php echo esc_attr( stripslashes_deep( $fieldOpt['label'] ) ); //phpcs:ignore ?>" <?php echo esc_attr($arm_is_deactive); ?>><?php echo esc_html( stripslashes_deep( $fieldOpt['label'] ) ); //phpcs:ignore ?></li>
													<?php
												}
												$arm_additional_profile_fields_data = '';
												echo apply_filters('arm_additional_profile_fields_data',$arm_additional_profile_fields_data); //phpcs:ignore
												?>
											</ul>
										</dd>
									</dl>
								</div>
								<div class="arm_accordion_separator"></div>
								<div class="arm_accordion_separator"></div>
								<div class="arm_accordion_inner_container" id="arm_profile_fields_inner_container">
									<?php
									foreach ( $profile_fields_data['profile_fields'] as $k => $pf ) {
										?>
										<div class="arm_add_profile_shortcode_row arm_user_custom_meta" id="arm_add_profile_shortcode_<?php echo esc_attr($pf); ?>">
											<span class="arm_add_profile_variable_code arm_add_profile_user_meta" data-code="<?php echo esc_attr($pf); ?>">
                                                <?php 
						if($ARMemberLite->is_arm_pro_active)
						{
						
                                                if($pf=='arm_profile_membership_plan')
                                                {
                                                    $profile_fields_data['label'][$pf] = !isset($profile_fields_data['label'][$pf]) ? esc_html__("Membership Plan","armember-membership") : $profile_fields_data['label'][$pf];
                                                }
                                                else if($pf=='arm_profile_membership_plan_expiry_date')
                                                {
                                                    $profile_fields_data['label'][$pf] = !isset($profile_fields_data['label'][$pf]) ? esc_html__("Plan Expiry Date","armember-membership") : $profile_fields_data['label'][$pf];
                                                }
                                                else if($pf=='arm_profile_membership_plan_renew_date')
                                                {
                                                    $profile_fields_data['label'][$pf] = !isset($profile_fields_data['label'][$pf]) ? esc_html__("Plan Renewal Date","armember-membership") : $profile_fields_data['label'][$pf];
                                                }
						}
                                                ?>
												<input type="text" value="<?php echo esc_attr( stripslashes_deep( $profile_fields_data['label'][ $pf ]) ); //phpcs:ignore ?>" id="arm_profile_field_input_<?php echo esc_attr($pf); ?>" data-id="<?php echo esc_attr($pf); ?>" name="profile_fields[<?php echo esc_attr($pf); ?>]" class="arm_profile_field_input" />
											</span>
											<span class="arm_add_profile_field_icons">
												<span class="arm_profile_field_icon edit_field" id="arm_edit_field" data-code="<?php echo esc_attr($pf); ?>" title="<?php esc_attr_e( 'Edit Field Label', 'armember-membership' ); ?>"></span>
												<span class="arm_profile_field_icon delete_field" id="arm_delete_field" data-code="<?php echo esc_attr($pf); ?>" title="<?php esc_attr_e( 'Delete Field', 'armember-membership' ); ?>" onclick="showConfirmBoxCallback('<?php echo esc_attr($pf); ?>');"></span>
												<span class="arm_profile_field_icon sort_field" id="arm_sort_field" data-code="<?php echo esc_attr($pf); ?>" title="<?php esc_attr_e( 'Move', 'armember-membership' ); ?>"></span>
											</span>
										<?php echo $arm_global_settings->arm_get_confirm_box( $pf, esc_html__( 'Are you sure you want to delete this field?', 'armember-membership' ), 'arm_remove_profile_shortcode_row' ); //phpcs:ignore ?>
										</div>    
										<?php
									}
									?>
								</div>
							</div>
						</li>
						<li>
							<a href="javascript:void(0)" class="arm_accordion_header"><?php esc_html_e( 'Social Profile Fields', 'armember-membership' ); ?>
								<?php $gf_tooltip = esc_html__( 'Select social profile fields that you want to display in profile header.', 'armember-membership' ); ?>
								<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo esc_attr($gf_tooltip); ?>"></i>
								<i></i></a>
							<div id="three" class="arm_accordion"> 
								<?php
								$socialProfileFields = $arm_member_forms->arm_social_profile_field_types();

								foreach ( $socialProfileFields as $SPFKey => $SPFLabel ) {
									$checked = '';
									if ( isset( $options['arm_social_fields'] ) && in_array( $SPFKey, $options['arm_social_fields'] ) ) {
										$checked = "checked='checked'";
									}
									?>
									<div class='arm_social_profile_field_item'>
										<input type='checkbox' class='arm_icheckbox arm_spf_active_checkbox arm_shortcode_form_popup_opt' value='<?php echo esc_attr($SPFKey); ?>' name='template_options[arm_social_fields][]' id='arm_spf_<?php echo esc_attr($SPFKey); ?>_status' <?php echo $checked; //phpcs:ignore ?> />
										<label for='arm_spf_<?php echo esc_attr($SPFKey); ?>_status'><?php echo esc_attr($SPFLabel); ?></label>
									</div>
									<?php
								}
								?>
								</div>
						</li>
						<?php
						if ( $is_default_template < 1 ) {
							?>
						<li>
							<a href="javascript:void(0)" class="arm_accordion_header"><?php esc_html_e( 'Membership Plans', 'armember-membership' ); ?>
								<?php $gf_tooltip = esc_html__( 'Select membership plans, of which users, you want to display this profile template.', 'armember-membership' ); ?>
								<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo esc_attr($gf_tooltip); ?>"></i>
								<i></i></a>
							<div id="four" class="arm_accordion arm_admin_form">
								<div class="arm_profile_membership_plan">
									<?php esc_html_e( 'Select Membership Plans', 'armember-membership' ); ?><br/>
									<select id="arm_temp_plans" class="arm_chosen_selectbox arm_template_plans_select" name="template_options[plans][]" data-placeholder="<?php esc_html_e( 'Select Plan(s)..', 'armember-membership' ); ?>" multiple="multiple">
										<?php if ( ! empty( $subs_data ) ) : ?>
											<?php foreach ( $subs_data as $sd ) : ?>
												<option class="arm_message_selectbox_op" <?php echo ( in_array( $sd['arm_subscription_plan_id'], $subscription_plans ) ) ? 'selected="selected"' : ''; ?>  value="<?php echo esc_attr($sd['arm_subscription_plan_id']); ?>"><?php echo stripslashes( $sd['arm_subscription_plan_name'] ); //phpcs:ignore ?></option>
											<?php endforeach; ?>
										<?php endif; ?>
									</select>

								</div>
							</div>
							</a>
						</li>
						<?php } ?>
						<li>
							<a href="javascript:void(0)" class="arm_accordion_header">
								<?php esc_html_e( 'Other Settings', 'armember-membership' ); ?>
								<?php $gf_tooltip = esc_html__( 'Select Other Settings.', 'armember-membership' ); ?>
								
								<i></i>
							</a>
							<div id="five" class="arm_accordion">
								<div class="arm_profile_other_settings">
									<div class="arm_profile_setting_switch_div"><label for="arm_profile_display_admin_user"><?php esc_html_e( 'Display Administrator Users', 'armember-membership' ); ?></label>
										<div class="armswitch arm_profile_setting_switch">
											<input type="checkbox" id="arm_profile_display_admin_user" value="1" class="armswitch_input" name="show_admin_users" <?php checked( $display_admin_profile, 1 ); ?>/>
											<label for="arm_profile_display_admin_user" class="armswitch_label"></label>
										</div>
									</div>
									<div class="arm_profile_setting_switch_div"><label for="arm_hide_empty_profile_fields"><?php esc_html_e( 'Hide Empty Profile Fields', 'armember-membership' ); ?></label>
										<div class="armswitch arm_profile_setting_switch">
											<input type="checkbox" id="arm_hide_empty_profile_fields" value="1" class="armswitch_input" name="template_options[hide_empty_profile_fields]" <?php checked( $hide_empty_profile_fields, 1 ); ?>/>
											<label for="arm_hide_empty_profile_fields" class="armswitch_label"></label>
										</div>
									</div>
									<?php
									$arm_profile_additional_other_settings_options='';
									echo apply_filters('arm_profile_additional_other_settings_options',$arm_profile_additional_other_settings_options,$display_member_badges); //phpcs:ignore
									?>
									<div class="arm_profile_setting_switch_div"><label for="arm_profile_display_joining_date"><?php esc_html_e( 'Display Joining Date', 'armember-membership' ); ?></label>
										<div class="armswitch arm_profile_setting_switch">
											<input type="checkbox" id="arm_profile_display_joining_date" value="1" class="armswitch_input" name="template_options[show_joining]" <?php checked( $display_joining_date, 1 ); ?>/>
											<label for="arm_profile_display_joining_date" class="armswitch_label"></label>
										</div>
									</div>
									<div class="arm_profile_setting_switch_div"><label for="arm_profile_display_cover_image"><?php esc_html_e( 'Default Cover Image', 'armember-membership' ); ?></label>
										<div class="armswitch arm_profile_setting_switch">
											<input type="checkbox" id="arm_profile_display_cover_image" value="1" class="armswitch_input" name="template_options[default_cover_photo]" <?php checked( $default_cover_photo, 1 ); ?>/>
											<label for="arm_profile_display_cover_image" class="armswitch_label"></label>
										</div>
										<?php
										$default_cover_url       = isset( $options['default_cover'] ) && $options['default_cover'] != '' ? $options['default_cover'] : MEMBERSHIPLITE_IMAGES_URL . '/profile_default_cover.png';
										$show_remove_cover_photo = 0;
										if ( $default_cover_photo == 1 && $default_cover_url != MEMBERSHIPLITE_IMAGES_URL . '/profile_default_cover.png' ) {
											$show_remove_cover_photo = 1;
										}
										?>
										<div class="arm_profile_setting_switch_div" id="arm_profile_upload_buttons_div" style="<?php echo ( $default_cover_photo != 1 ) ? 'display:none;' : ''; ?>">
											<div class="arm_accordion_separator"></div>
											<div class="arm_accordion_separator"></div>
											<div class="arm_accordion_separator"></div>
											<span class="arm_profile_upload_buttons_label"><?php esc_html_e( 'Default Cover Photo', 'armember-membership' ); ?></span>
											<div class="arm_default_cover_photo_wrapper" style="<?php echo ( $show_remove_cover_photo ) ? 'display:none' : 'display:inline-block'; ?>">
												<span><?php esc_html_e( 'Upload', 'armember-membership' ); ?></span>
												<input type="file" data-update-meta='no' class="arm_accordion_file_upload_button armFileUpload" data-avatar-type="cover" id='armTempEditFileUpload' accept=".jpg,.jpeg,.png,.bmp,.ico" data-type="profile" />
											</div>
											<div class="arm_remove_default_cover_photo_wrapper" style="<?php echo ( $show_remove_cover_photo ) ? 'display:inline-block' : 'display:none'; ?>">
												<span><?Php esc_html_e( 'Remove', 'armember-membership' ); ?></span>
											</div>
											<input type='hidden' id='armTempEditFileUpload_hidden' class='armFileUpload_cover' name='template_options[default_cover]' value='<?php echo esc_url($default_cover_url); ?>' />
										</div>
									</div>
                                    <?php do_action('arm_profile_other_settings_outside',$options); ?>
								</div>
							</div>
						</li>
                        <?php do_action('arm_profile_setting_section_outside',$options); ?>
					</ul>
				</div>
			</div>
			<?php
			
			$arm_profile_editor_css_section = '';
			echo apply_filters('arm_profile_editor_css_section',$arm_profile_editor_css_section,$options); //phpcs:ignore
			?>	
		
		</form>

		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"><?php //phpcs:ignore ?>    </div>

	<style id="arm_profile_runtime_style">



	</style>

</div>
<?php
if($enable_crop){ ?>
	<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
	<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
<div id="arm_crop_cover_div_wrapper" class="arm_crop_cover_div_wrapper" style="display:none;">
    <div id="arm_crop_cover_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>
    <div id="arm_crop_cover_div">
        <img id="arm_crop_cover_image" class="arm_max_width_100_pct arm_max_height_100_pct" src=""  /> <?php //phpcs:ignore ?>
    </div>
    <div class="arm_skip_cvr_crop_button_wrapper_admn">
        <button class="arm_crop_cover_button arm_img_cover_setting armhelptip tipso_style" title="<?php echo esc_attr__('Crop', 'armember-membership'); ?>" data-method="crop"><span class="armfa armfa-crop"></span></button>
        <button class="arm_clear_cover_button arm_img_cover_setting armhelptip tipso_style" title="<?php echo esc_attr__('Clear', 'armember-membership'); ?>" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>
        <button class="arm_zoom_cover_button arm_zoom_plus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="0.1" title="<?php echo esc_attr__('Zoom In', 'armember-membership'); ?>"><span class="armfa armfa-search-plus"></span></button>
        <button class="arm_zoom_cover_button arm_zoom_minus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="-0.1" title="<?php echo esc_attr__('Zoom Out', 'armember-membership'); ?>"><span class="armfa armfa-search-minus"></span></button>
        <button class="arm_rotate_cover_button arm_img_cover_setting armhelptip tipso_style" data-method="rotate" data-option="90" title="<?php echo esc_attr__('Rotate', 'armember-membership'); ?>"><span class="armfa armfa-rotate-right"></span></button>
        <button class="arm_reset_cover_button arm_img_cover_setting armhelptip tipso_style" title="<?php echo esc_attr__('Reset', 'armember-membership'); ?>" data-method="reset"><span class="armfa armfa-refresh"></span></button>
		<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>">
        <button id="arm_skip_cvr_crop_nav_admn" class="arm_cvr_done_front"><?php echo esc_html__('Done', 'armember-membership'); ?></button>
    </div>

    <p class="arm_discription">(<?php echo esc_html__('Use Cropper to set image and use mouse scroller for zoom image','armember-membership' ); ?>.)</p>
</div>

<div id="arm_crop_div_wrapper" class="arm_crop_div_wrapper" style="display:none;">
    <div id="arm_crop_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>
    <div id="arm_crop_div">
        <img id="arm_crop_image" src="" class="arm_max_width_100_pct" /> <?php //phpcs:ignore ?>
    </div>
    <button class="arm_crop_button"><?php echo esc_html__('crop','armember-membership' ); ?></button>
    <p class="arm_discription">(<?php echo esc_html__('Use Cropper to set image and use mouse scroller for zoom image','armember-membership' ); ?>.)</p>
</div>
<?php 
}
?>
<script type="text/javascript">
	function armTempColorSchemes() {
		var tempColorSchemes = <?php echo wp_json_encode( $tempColorSchemes ); ?>;
		return tempColorSchemes;
	}
	function armTempColorSchemes1() {
		var tempColorSchemes = <?php echo wp_json_encode( $tempColorSchemes1 ); ?>;
		return tempColorSchemes;
	}

	var DEFAULT_COVER = '<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/profile_default_cover.png'; //phpcs:ignore ?>';
	var EDIT_FIELD_LABEL = '<?php esc_html_e( 'Edit Field Label', 'armember-membership' ); ?>';
	var DELETE_FIELD = '<?php esc_html_e( 'Delete Field', 'armember-membership' ); ?>';
	var MOVE = '<?php esc_html_e( 'Move', 'armember-membership' ); ?>';
	var ARM_REMOVE_PROFILE_ROW_MSG = '<?php esc_html_e( 'Are you sure you want to delete this field?', 'armember-membership' ); ?>';
	var ARM_DELETE = '<?php esc_html_e( 'Delete', 'armember-membership' ); ?>';
	var ARM_CANCEL = '<?php esc_html_e( 'Cancel', 'armember-membership' ); ?>';
</script>
<?php
    echo $ARMemberLite->arm_get_need_help_html_content('members-profile-template-add'); //phpcs:ignore
?>