<?php
global $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings, $arm_social_feature, $arm_subscription_plans,$arm_common_lite;
$form_color_schemes   = $arm_member_forms->arm_form_color_schemes();
$form_gradient_scheme = $arm_member_forms->arm_default_button_gradient_color();
$formColorSchemes     = isset( $form_color_schemes ) ? $form_color_schemes : array();
$formButtonSchemes    = isset( $form_gradient_scheme ) ? $form_gradient_scheme : array();

$activeSocialNetworks = $arm_social_feature->arm_get_active_social_options();
$thank_you_page_id    = $arm_global_settings->arm_get_single_global_settings( 'thank_you_page_id', 0 );
$all_global_settings  = $arm_global_settings->global_settings;
$form_id              = $show_registration_link = $show_forgot_password_link = 0;
$prefix_name          = $form_styles = '';
$form_detail          = $socialFieldsOptions = $submitBtnOptions = array();
$default_form_style   = $arm_member_forms->arm_default_form_style();
$sectionPlaceholder   = esc_html__( 'Drop Fields Here.', 'armember-membership' );

$form_settings = array(
	'message'                     => esc_html__( 'Form has been successfully submitted.', 'armember-membership' ),
	'redirect_type'               => 'page',
	'redirect_page'               => '',
	'redirect_url'                => ARMLITE_HOME_URL,
	'auto_login'                  => 0,
	'show_rememberme'             => 0,
	'show_registration_link'      => 0,
	'show_forgot_password_link'   => 0,
	'registration_link_margin'    => array(),
	'forgot_password_link_margin' => array(),
	'enable_social_login'         => 0,
	'social_networks'             => array(),
	'social_networks_order'       => array(),
	'social_networks_settings'    => array(),
	'style'                       => $default_form_style,
	'date_format'                 => 'd/m/Y',
	'show_time'                   => 0,
	'is_hidden_fields'            => 0,


);
$social_networks = $social_networks_order = $formSocialNetworksSettings = array();
foreach ( $activeSocialNetworks as $sk => $so ) {
	if ( $so['status'] == 1 ) {
		$social_networks[] = $sk;
	}
}
if ( ! empty( $_GET['form_id'] ) && $_GET['form_id'] != 0 ) { //phpcs:ignore
	$form_id = !empty( $_REQUEST['form_id']) ? intval( $_REQUEST['form_id'] ) : ''; //phpcs:ignore
	// Remove fields for non-saved forms
	$delete_field_status = $wpdb->delete( $ARMemberLite->tbl_arm_form_field, array( 'arm_form_field_status' => 2 ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	// Update field status for non-saved forms
	$update_field_status = $wpdb->update( $ARMemberLite->tbl_arm_form_field, array( 'arm_form_field_status' => '1' ), array( 'arm_form_field_form_id' => $form_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

	$form_detail            = $arm_member_forms->arm_get_single_member_forms( $form_id );
	$form_settings          = ( ! empty( $form_detail['arm_form_settings'] ) ) ? maybe_unserialize( $form_detail['arm_form_settings'] ) : array();
	$form_settings['style'] = ( isset( $form_settings['style'] ) ) ? $form_settings['style'] : array();
	$form_settings['style'] = shortcode_atts( $default_form_style, $form_settings['style'] );
	$login_regex            = '/template-login(.*?)/';
	$register_regex         = '/template-registration(.*?)/';
	preg_match( $login_regex, $form_detail['arm_form_slug'], $match_login );
	preg_match( $register_regex, $form_detail['arm_form_slug'], $match_register );
	$reference_template = $form_detail['arm_ref_template'];
	if ( isset( $match_login[0] ) && ! empty( $match_login[0] ) ) {
		$form_detail['arm_form_type'] = 'login';
	} elseif ( isset( $match_register[0] ) && ! empty( $match_register[0] ) ) {
		$form_detail['arm_form_type'] = 'registration';
	}
}
$get_action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action']) : ''; //phpcs:ignore
$get_set_name = isset( $_GET['set_name'] ) ? sanitize_text_field( $_GET['set_name']) : ''; //phpcs:ignore
$get_form_id = isset( $_REQUEST['form_id'] ) ? intval( $_GET['form_id']) : ''; //phpcs:ignore
if($ARMemberLite->is_arm_pro_active)
{
	if( 'new_form' == sanitize_text_field( $get_action ) && isset( $_GET['arm_form_type'] ) && 'edit_profile' == $_GET['arm_form_type'] ){ //phpcs:ignore
	    $form_detail['arm_form_type'] = 'edit_profile';
	    if( isset( $_GET['form_meta_fields'] ) && '' != $_GET['form_meta_fields'] ) { //phpcs:ignore
	        $form_meta_fields = explode( ',', sanitize_text_field($_GET['form_meta_fields']) ); //phpcs:ignore
	    }
	    if( empty($form_meta_fields) ){
	        foreach( $form_detail['fields'] as $fkey => $fvalue ){
	            if( 'submit' != $fvalue['arm_form_field_option']['type'] ){
	                unset( $form_detail['fields'][$fkey] );
	            }
	        }
	        $form_detail['fields'] = array_values( $form_detail['fields'] );
	    } else {
	        foreach( $form_detail['fields'] as $fkey => $fvalue ){
	            if( $fvalue['arm_form_field_option']['type'] != 'submit' && !in_array( $fvalue['arm_form_field_option']['meta_key'], $form_meta_fields) ){
	                unset( $form_detail['fields'][$fkey]);
	            } else {
	                if( $fvalue['arm_form_field_option']['type'] != 'submit' ){
	                    $form_detail['fields'][$fkey]['arm_form_field_option']['default_field'] = 0;
	                }
	            }
	        }
	        $form_detail['fields'] = array_values( $form_detail['fields'] );
	    }

	    $form_detail_fields = array();

	    foreach( $form_detail['fields'] as $ofk => $ofv ){
	        if( $ofv['arm_form_field_option']['type'] != 'submit' ){
	            $form_detail_fields[] = $ofv['arm_form_field_option']['meta_key'];
	        }
	    }
	}
}

$isRegister = ($form_detail['arm_form_type'] == 'registration') ? true : false;
$isEditProfile = ( 'edit_profile' == $form_detail['arm_form_type'] ) ? true : false;
$formDateFormat = !empty($form_settings['date_format']) ? $form_settings['date_format'] : 'd/m/Y';
$showTimePicker = !empty($form_settings['show_time']) ? $form_settings['show_time'] : 0;
$setID = $form_detail['arm_set_id'];
$is_rtl = (isset($form_settings['style']['rtl']) && $form_settings['style']['rtl'] == '1') ? $form_settings['style']['rtl'] : '0';
//Form Classes
$form_class = '';
$formLayout = ! empty( $form_settings['style']['form_layout'] ) ? $form_settings['style']['form_layout'] : 'writer';

$form_class .= ' arm_form_' . $form_id;
$form_class .= ' arm_form_layout_' . $formLayout;
$form_class .= ' armf_layout_' . $form_settings['style']['label_position'];
$form_class .= ' armf_button_position_' . $form_settings['style']['button_position'];
$form_class .= ( $form_settings['style']['label_hide'] == '1' ) ? ' armf_label_placeholder' : '';
$form_class .= ' armf_alignment_' . $form_settings['style']['label_align'];
$form_class .= ( $is_rtl == '1' ) ? ' arm_form_rtl' : ' arm_form_ltr';
if ( is_rtl() ) {
	$form_class .= ' arm_rtl_site';
}
if ( $formLayout == 'writer' || $formLayout == 'writer_border' ) {
	$form_class .= ' arm_materialize_form';
}

if ( $formLayout == 'writer' ) {
	$form_class .= ' arm-default-form arm-material-style ';
} elseif ( $formLayout == 'rounded' ) {
	$form_class .= ' arm-default-form arm-rounded-style ';
} elseif ( $formLayout == 'writer_border' ) {
	$form_class .= ' arm-default-form arm--material-outline-style ';
} else {
	$form_class .= ' arm-default-form ';
}
$arm_form_fields_for_cl = array();

$arm_form_fields_cl_omited_fields = array( 'password', 'html', 'file', 'section', 'avatar', 'arm_captcha' );

$arm_max_field_id = 0;
if ( ! empty( $_REQUEST['is_clone'] ) && $_REQUEST['is_clone'] == 1 ) { //phpcs:ignore
	$max_field_id = $wpdb->get_row( $wpdb->prepare("SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s",DB_NAME,$ARMemberLite->tbl_arm_form_field) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	if ( ! empty( $max_field_id ) ) {
		$arm_max_field_id = $max_field_id->AUTO_INCREMENT;
	}
}
$_SESSION['arm_file_upload_arr']['form_bg_file'] = "-";

?>
<div class="arm_loading_grid" style="display: none;"><?php $arm_loader = $arm_common_lite->arm_loader_img_func();
echo $arm_loader; //phpcs:ignore ?></div>
<div class="wrap arm_page arm_manage_form_main_wrapper">
	<div id="content_wrapper" class="arm_manage_form_content_wrapper">
		<form id="arm_manage_form_settings_form" class="arm_admin_member_form <?php echo esc_attr($form_class); ?>">
			<input type="hidden" name="form_set_id" value="<?php echo intval($setID); ?>" id="form_set_id" class="form_set_id">
			<?php $arm_form_action = !empty( $get_action ) ? esc_attr( $get_action ) : 'new_form'; ?>
			<?php $arm_new_set_name = isset( $get_set_name ) ? stripslashes_deep( esc_attr( $get_set_name ) ) : ''; ?>
			<input type="hidden" name="arm_action" id="arm_action" value="<?php echo esc_attr($arm_form_action); ?>" />
			<input type="hidden" name="arm_form_id" id="arm_form_id" value="<?php echo esc_attr( $get_form_id ); ?>" />
			<input type="hidden" name="arm_ref_template" id="arm_ref_template" value="<?php echo esc_attr($reference_template); ?>" />
			<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
			<?php
			if ( $isRegister || $isEditProfile) {
				$arm_new_set_name = isset( $_REQUEST['arm_set_name'] ) ? $_REQUEST['arm_set_name'] : ''; //phpcs:ignore
				if(!empty($arm_new_set_name)){
					$arm_new_set_name = wp_unslash($arm_new_set_name);      // remove \ slashes
					$arm_new_set_name = htmlspecialchars_decode($arm_new_set_name, ENT_QUOTES); // convert &quot; → "
					$arm_new_set_name = sanitize_text_field($arm_new_set_name);            // sanitize safely
				}	
				?>
				<input type="hidden" name="arm_new_set_name" id="arm_new_set_name" value="<?php echo esc_html($arm_new_set_name); ?>" />
				<?php
			} else {
				$arm_new_set_name = isset( $_GET['arm_set_name'] ) ? $_GET['arm_set_name'] : ''; //phpcs:ignore
				if(!empty($arm_new_set_name)){
					$arm_new_set_name = wp_unslash($arm_new_set_name);      // remove \ slashes
					$arm_new_set_name = htmlspecialchars_decode($arm_new_set_name, ENT_QUOTES); // convert &quot; → "
					$arm_new_set_name = sanitize_text_field($arm_new_set_name);            // sanitize safely
				}
				?>
				<input type="hidden" name="arm_new_set_name" id="arm_new_set_name" value="<?php echo esc_html($arm_new_set_name); ?>" />
				<?php
			}
			?>
			<div class="arm_editor_heading">
				<div class="page_title">
					<?php if ( $isRegister || $isEditProfile) { ?>

						<div class="arm_header_registration_form_title">
							<?php
							$form_detail['arm_form_label'] = !empty($form_detail['arm_form_label']) ? sanitize_text_field($form_detail['arm_form_label']) : sanitize_text_field($_GET['arm_set_name']); //phpcs:ignore
							 echo stripslashes_deep( $form_label = ( sanitize_text_field( $get_action) !== 'new_form' ) ? stripslashes_deep( wp_unslash($form_detail['arm_form_label'] )) : stripslashes_deep(wp_unslash( $arm_new_set_name ) ) ); ?> <?php //phpcs:ignore ?>
							<input type="hidden" name="arm_forms[<?php echo esc_attr( $get_form_id ); ?>][arm_form_label]"  class="arm-df__field-label_value" value="<?php echo stripslashes_deep( esc_attr($form_label) ); //phpcs:ignore ?>"/>
						</div>
					<?php } else { ?>
						<?php esc_html_e( 'Form Set (Login / Forgot Password / Change Password)', 'armember-membership' ); ?>
					<?php } ?>
					<div class="arm_editor_heading_action_btns">
						<a href="javascript:void(0)" id="arm_save_member_form" class="arm_save_btn arm_save_edior_btn"><?php esc_html_e( 'Save', 'armember-membership' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $arm_slugs->manage_forms )); //phpcs:ignore ?>" id="arm_close_member_form" class="arm_cancel_btn arm_close_edior_btn"><?php esc_html_e( 'Close', 'armember-membership' ); ?></a>
						<a href="javascript:void(0)" id="arm_reset_member_form" class="arm_cancel_btn arm_form_reset_btn"><i class="armfa armfa-rotate-left"></i></a>
					</div>
                    <?php 

                    if(sanitize_text_field( $get_action) == 'edit_form'){
                        $after_title_content = "";
                        $after_title_content = apply_filters('arm_action_for_edit_manage_form_page_heading', $after_title_content); //phpcs:ignore
                        echo $after_title_content; //phpcs:ignore
                    }
                    ?>
					<?php if ( $isRegister || $isEditProfile) { ?>
						<div class="arm_form_shortcode_container" style="<?php echo ( esc_attr( $get_action ) == 'new_form' ) ? 'display:none;' : ''; ?>" >
							<span><?php esc_html_e( 'Shortcode', 'armember-membership' ); ?>:</span>
							<span class="arm_form_shortcode arm_shortcode_text arm_form_shortcode_box">
                                <?php
                                    if( $isEditProfile ){
                                ?>
                                    <input type="text" value="[arm_profile_detail id='<?php echo esc_attr($form_detail['arm_form_id']); ?>']" readonly="readonly" class="armCopyText arm_font_size_13"/>
                                    <span class="arm_click_to_copy_text arm_font_size_13" data-code="[arm_profile_detail id='<?php echo esc_attr($form_detail['arm_form_id']); ?>']" ><?php esc_html_e('Click to Copy', 'armember-membership') ?></span>
                                    <span class="arm_copied_text arm_font_size_13">
                                        <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL . '/copied_ok.png' //phpcs:ignore?>" />
                                        <?php esc_html_e('Code Copied', 'armember-membership') ?>
                                    </span>    
                                <?php
                                    } else {
                                ?>
								<input type="text" class="armCopyText arm_font_size_13" value="[arm_form id='<?php echo intval($form_detail['arm_form_id']); ?>']" readonly="readonly"/>
								<span class="arm_click_to_copy_text arm_font_size_13" data-code="[arm_form id='<?php echo intval($form_detail['arm_form_id']); ?>']" ><?php esc_html_e( 'Click to Copy', 'armember-membership' ); ?></span>
								<span class="arm_copied_text arm_font_size_13">
									<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/copied_ok.png'; //phpcs:ignore ?>" />
									<?php esc_html_e( 'Code Copied', 'armember-membership' ); ?>
								</span>
                                <?php
                                    }
                                ?>
							</span>
						</div>
					<?php } ?>
					<div class="armclear"></div>
				</div>
			</div>
			<div class="arm_editor_wrapper">
				<?php if ( $isRegister || $isEditProfile) { ?>
					<div class="arm_editor_left">
						<div id="tabs-container" class="arm_user_form_fields_container tabs-container">
							<ul class="tabs-menu arm_tab_menu">
								<li class="current"><a href="#tab-1"><?php esc_html_e( 'Preset Fields', 'armember-membership' ); ?></a></li>
								<li><a href="#tab-2"><?php esc_html_e( 'Form Fields', 'armember-membership' ); ?></a></li>
							</ul>
							<div class="tab arm_form_fields_container_tab">
								<div id="tab-1" class="arm-tab-content">
									<div class="arm_form_addnew_fields_section arm_form_addnew_user_fields">
										<?php
										$user_meta_keys = $arm_member_forms->arm_get_db_form_fields( true );
										unset( $user_meta_keys['roles'] );
										unset( $user_meta_keys['avatar'] );
										unset( $user_meta_keys['plans'] );
										unset( $user_meta_keys['subscription_plan'] );
										unset( $user_meta_keys['social_login'] );
										unset( $user_meta_keys['social_fields'] );
										unset( $user_meta_keys['rememberme'] );
                                        					unset($user_meta_keys['arm_captcha']);
										if ( ! empty( $user_meta_keys ) ) {
											?>
											<div class="arm_form_addnew_title"></div>
											<div class="arm_form_addnew_fields_container arm_form_addnew_user_element">
												<ul class="arm_field_type_list">
												<?php
												foreach ( $user_meta_keys as $meta_key => $opts ) {
													if ( strpos( $meta_key, '_select_' ) == false ) {
														$fieldMetaClass = '';
														if( !$isEditProfile ){
															if ( in_array( $meta_key, array( 'first_name', 'last_name', 'user_login', 'user_email' ) ) ) {
																$fieldMetaClass = 'arm_disabled';
															}
						                                                            	} else {
							                                                                if( isset( $form_meta_fields ) && is_array($form_meta_fields) && in_array( $meta_key, $form_meta_fields ) ){
							                                                                    $fieldMetaClass = 'arm_disabled';
							                                                                }
						                                                            	}
														?><li class="frmfieldtypebutton arm_form_preset_fields <?php echo esc_html($fieldMetaClass); ?>" data-field_key="<?php echo esc_attr($meta_key); ?>"><div class="arm_new_field"><a href="javascript:void(0);" id="<?php echo esc_attr($meta_key); ?>"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/general_icon.png" alt="<?php echo esc_html(stripslashes($opts['label'])); ?>" /><img class="arm_disabled_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/general_icon_disabled.png" alt="<?php echo esc_html(stripslashes($opts['label'])); ?>" /><?php echo esc_html(stripslashes($opts['label'])); ?></a></div></li>
																															<?php
													}
												}
												?>
													</ul>
											</div>
										<?php } ?>
										<div class="armclear"></div>
										<?php if ( $arm_social_feature->isSocialFeature ) { ?>
											<div class="arm_form_addnew_fields_container arm_form_addnew_social_fields_container">
												<div class="arm_form_addnew_title"><?php esc_html_e( 'Social Profile Fields', 'armember-membership' ); ?></div>
												<a href="javascript:void(0)" class="arm_enable_social_profile_fields_link armemailaddbtn" data-field_key="social_fields"><?php esc_html_e( 'Social Profile Fields', 'armember-membership' ); ?></a>
											</div>
										<?php } ?>
									</div>
								</div>
								<div id="tab-2" class="arm-tab-content">
									<div class="arm_form_addnew_fields_section arm_form_addnew_other_fields">
										<div class="arm_form_addnew_title"><?php esc_html_e( 'Basic Fields', 'armember-membership' ); ?></div>
										<div class="arm_form_addnew_fields_container">
											<ul class="arm_field_type_list">
												<li class="frmfieldtypebutton">
													<div class="arm_new_field">
														<a href="javascript:void(0);" id="text"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/textbox_icon.png" alt="<?php esc_html_e( 'Textbox', 'armember-membership' ); ?>" /><?php esc_html_e( 'Textbox', 'armember-membership' ); ?></a>
													</div>
												</li>
												<li class="frmfieldtypebutton">
													<div class="arm_new_field">
														<a href="javascript:void(0);" id="password"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/password_icon.png" alt="<?php esc_html_e( 'Password', 'armember-membership' ); ?>"><?php esc_html_e( 'Password', 'armember-membership' ); ?></a>
													</div>
												</li>
												<li class="frmfieldtypebutton">
													<div class="arm_new_field">
														<a href="javascript:void(0);" id="textarea"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/textarea_icon.png" alt="<?php esc_html_e( 'Textarea', 'armember-membership' ); ?>" /><?php esc_html_e( 'Textarea', 'armember-membership' ); ?></a>
													</div>
												</li>
												<li class="frmfieldtypebutton">
													<div class="arm_new_field">
														<a href="javascript:void(0);" id="checkbox"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/checkbox_icon.png" alt="<?php esc_html_e( 'Checkbox', 'armember-membership' ); ?>" /><?php esc_html_e( 'Checkbox', 'armember-membership' ); ?></a>
													</div>
												</li>
												<li class="frmfieldtypebutton">
													<div class="arm_new_field">
														<a href="javascript:void(0);" id="radio"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/radio_icon.png" alt="<?php esc_html_e( 'Radio Buttons', 'armember-membership' ); ?>" /><?php esc_html_e( 'Radio Buttons', 'armember-membership' ); ?></a>
													</div>
												</li>
												<li class="frmfieldtypebutton">
													<div class="arm_new_field">
														<a href="javascript:void(0);" id="select"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/dropdown_icon.png" alt="<?php esc_html_e( 'Dropdown', 'armember-membership' ); ?>" /><?php esc_html_e( 'Dropdown', 'armember-membership' ); ?></a>
													</div>
												</li>
												<li class="frmfieldtypebutton">
													<div class="arm_new_field">
														<a href="javascript:void(0);" id="date"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/date_icon.svg" alt="<?php esc_html_e( 'Date', 'armember-membership' ); ?>" /><?php esc_html_e( 'Date', 'armember-membership' ); ?></a>
													</div>
												</li>
												<li class="frmfieldtypebutton">
													<div class="arm_new_field">
														<a href="javascript:void(0);" id="html"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/html_text_icon.png" alt="<?php esc_html_e( 'Html Text', 'armember-membership' ); ?>" /><?php esc_html_e( 'Html Text', 'armember-membership' ); ?></a>
													</div>
												</li>

												<li class="frmfieldtypebutton">
													<div class="arm_new_field">
														<a href="javascript:void(0);" id="file"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/file_upload_icon.png" alt="<?php esc_html_e( 'File Upload', 'armember-membership' ); ?>" /><?php esc_html_e( 'File Upload', 'armember-membership' ); ?></a>
													</div>
												</li>
												<li class="frmfieldtypebutton">
													<div class="arm_new_field">
														<a href="javascript:void(0);" id="section"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/roles_icon.png" alt="<?php esc_html_e( 'Divider', 'armember-membership' ); ?>" /><?php esc_html_e( 'Divider', 'armember-membership' ); ?></a>
													</div>
												</li>
											</ul>
										</div>
										<div class="armclear"></div>
										<div class="arm_form_addnew_title"><?php esc_html_e( 'Advanced Fields', 'armember-membership' ); ?></div>
										<div class="arm_form_addnew_fields_container">
											<ul class="arm_field_type_list">
												<li class="frmfieldtypebutton arm_form_preset_fields" data-field_key="roles">
													<div class="arm_new_field">
														<a href="javascript:void(0);" id="roles"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/roles_icon.png" alt="<?php esc_html_e( 'Roles', 'armember-membership' ); ?>" /><img class="arm_disabled_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/roles_icon_disabled.png" alt="<?php esc_html_e( 'Roles', 'armember-membership' ); ?>" /><?php esc_html_e( 'Roles', 'armember-membership' ); ?></a>
													</div>
												</li>
												<li class="frmfieldtypebutton arm_form_preset_fields" data-field_key="avatar">
													<div class="arm_new_field">
														<a href="javascript:void(0);" id="avatar"><img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/avatar_icon.png" alt="<?php esc_html_e( 'Avatar', 'armember-membership' ); ?>" /><img class="arm_disabled_img" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/avatar_icon_disabled.png" alt="<?php esc_html_e( 'Avatar', 'armember-membership' ); ?>" /><?php esc_html_e( 'Avatar', 'armember-membership' ); ?></a>
													</div>
												</li>
												<?php $arm_profile_preset_form_fields = '';
												echo apply_filters('arm_profile_preset_form_fields',$arm_profile_preset_form_fields); //phpcs:ignore
												?>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div><!--./ END `.arm_editor_left`-->
				<?php } ?>
				<div class="arm_editor_center">
					<div class="arm_message arm_success_message" id="arm_success_message"><div class="arm_message_text"></div></div>
					<div class="arm_message arm_error_message" id="arm_error_message"><div class="arm_message_text"></div></div>
					<div class="armclear"></div>
					<div class="arm_editor_form_fileds_container" style="display: none;">
						<?php
						$socialLoginBtns   = '';
						$otherForms        = $otherFormIDs = array();
						$mainSortableClass = 'arm_main_sortable';
						if ( $isRegister || $isEditProfile) {
							$otherForms[] = $form_detail;
						} else {
							$mainSortableClass = 'arm_no_sortable arm_set_editor_ul';
							$otherForms        = $arm_member_forms->arm_get_other_member_forms( $setID );
						}
						$otherFormsValues                     = array_values( $otherForms );
						$firstForm                            = array_shift( $otherFormsValues );
						$form_settings                        = ( ! empty( $firstForm['arm_form_settings'] ) ) ? maybe_unserialize( $firstForm['arm_form_settings'] ) : array();
						$form_settings['style']               = ( isset( $form_settings['style'] ) ) ? $form_settings['style'] : array();
						$form_settings['style']               = shortcode_atts( $default_form_style, $form_settings['style'] );
						$form_settings['style']['form_width'] = ( ! empty( $form_settings['style']['form_width'] ) ) ? $form_settings['style']['form_width'] : '600';
						$form_settings['hide_title']          = ( isset( $form_settings['hide_title'] ) ) ? $form_settings['hide_title'] : '0';
						$form_settings['is_hidden_fields']    = ( isset( $form_settings['is_hidden_fields'] ) ) ? $form_settings['is_hidden_fields'] : '0';
						$formFieldPosition                    = ( ! empty( $form_settings['style']['field_position'] ) ) ? $form_settings['style']['field_position'] : 'left';
						$mainSortableClass                   .= ' arm_field_position_' . $formFieldPosition . ' ';
						$enable_social_login                  = ( isset( $form_settings['enable_social_login'] ) ) ? $form_settings['enable_social_login'] : 0;
						$social_btn_type                      = ( ! empty( $form_settings['style']['social_btn_type'] ) ) ? $form_settings['style']['social_btn_type'] : 'horizontal';
						$social_btn_align                     = ( ! empty( $form_settings['style']['social_btn_align'] ) ) ? $form_settings['style']['social_btn_align'] : 'left';
						$enable_social_btn_separator          = ( isset( $form_settings['style']['enable_social_btn_separator'] ) ) ? $form_settings['style']['enable_social_btn_separator'] : 0;
						$social_btn_separator                 = ( isset( $form_settings['style']['social_btn_separator'] ) ) ? $form_settings['style']['social_btn_separator'] : '';
						$social_btn_position                  = ( isset( $form_settings['style']['social_btn_position'] ) ) ? $form_settings['style']['social_btn_position'] : 'bottom';
						if ( $enable_social_login == '1' ) {
							$social_networks                           = ( isset( $form_settings['social_networks'] ) && $form_settings['social_networks'] != '' ) ? explode( ',', $form_settings['social_networks'] ) : array();
							$social_networks_order                     = ( isset( $form_settings['social_networks_order'] ) && $form_settings['social_networks_order'] != '' ) ? explode( ',', $form_settings['social_networks_order'] ) : array();
							$form_settings['social_networks_settings'] = ( isset( $form_settings['social_networks_settings'] ) ) ? stripslashes_deep( $form_settings['social_networks_settings'] ) : '';
							$formSocialNetworksSettings                = maybe_unserialize( $form_settings['social_networks_settings'] );
						} else {
							$enable_social_btn_separator = 0;
						}

						$show_reg_link              = ( isset( $form_settings['show_registration_link'] ) ) ? $form_settings['show_registration_link'] : 0;
						$show_fp_link               = ( isset( $form_settings['show_forgot_password_link'] ) ) ? $form_settings['show_forgot_password_link'] : 0;
						$registration_link_label    = ( isset( $form_settings['registration_link_label'] ) ) ? stripslashes( $form_settings['registration_link_label'] ) : esc_html__( 'Register', 'armember-membership' );
						$forgot_password_link_label = ( isset( $form_settings['forgot_password_link_label'] ) ) ? stripslashes( $form_settings['forgot_password_link_label'] ) : esc_html__( 'Forgot Password', 'armember-membership' );
						$registration_link_label    = $arm_member_forms->arm_parse_login_links( $registration_link_label, '#' );
						$forgot_password_link_label = $arm_member_forms->arm_parse_login_links( $forgot_password_link_label, '#' );
						reset( $otherForms );
						?>
						<?php if ( ! empty( $otherForms ) ) { ?>
							<div class="arm_form_width_belt">
								<div class="arm_form_width_text"><?php echo esc_html($form_settings['style']['form_width']) . esc_html($form_settings['style']['form_width_type']); ?></div>
							</div>
							<?php $arm_form_ids = array(); ?>
							<?php foreach ( $otherForms as $oform ) { ?>
								<div class="arm_editor_form_fileds_wrapper armPageContainer">
									<?php
									$oformid = $oform['arm_form_id'];
									array_push( $arm_form_ids, $oformid );
									$oformarmtype                            = $oform['arm_form_type'];
									$otherFormIDs[ $oform['arm_form_type'] ] = $oform;
									$aboveLinks                              = $belowLinks = '';
									$form_title_position                     = ( ! empty( $form_settings['style']['form_title_position'] ) ) ? $form_settings['style']['form_title_position'] : 'left';

									if ( isset( $_GET['action'] ) && $_GET['action'] == 'new_form' ) { //phpcs:ignore
										if ( isset( $_GET['arm_set_name'] ) && $_GET['arm_set_name'] != '') { //phpcs:ignore
											
											$oform['arm_form_title'] = esc_html(wp_unslash( $_GET['arm_set_name'] )); //phpcs:ignore
											
											$oform['arm_form_label'] = esc_html(wp_unslash($_GET['arm_set_name'])); //phpcs:ignore
										} elseif ( ! empty( $oform['arm_form_label'] ) ) {
											$oform['arm_form_label'] = stripslashes( $oform['arm_form_label'] );
										} else {
											$oform['label'] = '';
										}
									} else {
										$oform['arm_form_title'] = ! empty( $oform['arm_form_title'] ) ? stripslashes(wp_unslash($oform['arm_form_title'] )) : '';
										$oform['arm_form_label'] = ! empty( $oform['arm_form_label'] ) ? stripslashes(wp_unslash($oform['arm_form_label'] )) : '';
									}
				    $arm_additional_social_fields = '';
				    echo apply_filters('arm_additional_social_fields_sec',$arm_additional_social_fields,$firstForm,$social_networks,$activeSocialNetworks,$oformarmtype,$form_settings,$formSocialNetworksSettings,$oformid,'top'); //phpcs:ignore
									?>
								
									<div class="arm-df__heading arm_form_editor_form_heading <?php echo 'armalign' . esc_attr($form_title_position); ?>" style="<?php echo ( $form_settings['hide_title'] == '1' ) ? 'display:none;' : ''; ?>">
										<?php
										$formTitleClass = '';
										if ( $isRegister || $isEditProfile) {
											$formTitleClass = 'arm_registration_form_title';
										}
										?>
										<div class="arm_form_member_main_field_label arm_member_form_label">
											<span class="arm-df__field-label_text arm-df__heading-text <?php echo esc_attr($formTitleClass); ?>" data-type="heading"><?php echo stripslashes( $oform['arm_form_title'] ); //phpcs:ignore ?></span>
											<?php if ( ! $isRegister || $isEditProfile) { ?>
												<input type="hidden" name="arm_forms[<?php echo intval($oformid); ?>][arm_form_label]" class="arm-df__field-label_value" value="<?php echo esc_attr($oform['arm_form_title']); ?>"/>
											<?php } ?>

											<input type="hidden" name="arm_forms[<?php echo intval($oformid); ?>][arm_form_title]" id="arm_form_label_input_hidden_<?php echo intval($oformid); ?>" class="arm-df__field-label_value" value="<?php echo esc_attr($oform['arm_form_title']); ?>"/>
										</div>
										<div class="armclear"></div>
										<input type="hidden" name="arm_forms[<?php echo intval($oformid); ?>][arm_form_type]" value="<?php echo esc_attr($oform['arm_form_type']); ?>"/>
									</div>
									<ul class="arm-df__fields-wrapper <?php echo esc_attr($mainSortableClass); ?> arm-df__fields-wrapper_<?php echo intval($oformid); ?> arm_form_editor_middle_part 
																				 <?php
																					if ( $oformarmtype == 'forgot_password' ) {
																						echo 'arm_form_editor_forgot_password_form';
																					}
																					?>
									<?php
									if ( $oformarmtype == 'change_password' ) {
										echo 'arm_form_editor_change_password_form';
									}
									?>
									" data-form_id="<?php echo intval($oformid); ?>">
											<?php
											if ( isset( $_REQUEST['form_meta_fields'] ) && $_REQUEST['form_meta_fields'] !== '' ) { //phpcs:ignore
												//$form_meta_fields = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST['form_meta_fields'] ); //phpcs:ignore
                                                						$form_meta_fields = $_REQUEST['form_meta_fields']; //phpcs:ignore
												$meta_fields = explode( ',', $form_meta_fields );

												$metaFields      = $arm_member_forms->arm_get_db_form_fields( true );
												$new_meta_fields = array();
												$n               = 1;
                                                						if( !$isEditProfile ){
													foreach ( $metaFields as $key => $value ) {
														if ( in_array( $key, $meta_fields ) ) {
															$new_meta_fields['arm_form_field_id']      = ( ( $form_id * 10 ) + $n );
															$new_meta_fields['arm_form_field_form_id'] = count( $oform['fields'] ) + 1;
															$new_meta_fields['arm_form_field_order']   = '0';

															$new_meta_fields['arm_form_field_option']      = maybe_serialize( $metaFields[ $key ] );
															$new_meta_fields['arm_form_field_status']      = '2';
															$new_meta_fields['arm_form_field_create_date'] = current_time( 'mysql' );
															array_push( $oform['fields'], $new_meta_fields );
															unset( $new_meta_fields );
															$n++;
														}
													}
						                                                } else {
						                                                    foreach( $metaFields as $key => $value ) {
						                                                        if( in_array($key, $meta_fields) && !in_array($key,$form_detail_fields) ){
						                                                            $new_meta_fields['arm_form_field_id'] = (($form_id * 10) + $n);
						                                                            $new_meta_fields['arm_form_field_form_id'] = count($oform['fields']) + 1;
						                                                            $new_meta_fields['arm_form_field_order'] = '0';

						                                                            $new_meta_fields['arm_form_field_option'] = maybe_serialize($metaFields[$key]);
						                                                            $new_meta_fields['arm_form_field_status'] = '2';
						                                                            $new_meta_fields['arm_form_field_create_date'] = current_time( 'mysql' );
						                                                            array_push($oform['fields'], $new_meta_fields);
						                                                            unset($new_meta_fields);
						                                                            $n++;
						                                                        }
						                                                    }
						                                                }
						                                            }
											?>
											<?php if ( ! empty( $oform['fields'] ) ) { ?>
												<?php
												if($ARMemberLite->is_arm_pro_active){
													$armForm = new ARM_Form( 'id', $oformid );
												}
												else
												{
													$armForm = new ARM_Form_Lite( 'id', $oformid );
												}
												
												if ( $oformarmtype == 'forgot_password' ) {
													?>
												<li class="arm_margin_0">
													<div class="arm_forgot_password_description" style="<?php echo ( empty( $oform['arm_form_settings']['description'] ) ) ? 'display:none;' : ''; ?>"><?php echo stripslashes( $oform['arm_form_settings']['description'] ); //phpcs:ignore ?></div>
												</li>
													<?php
												}
												foreach ( $oform['fields'] as $ffID => $field ) {
                                                							$form_field_id = !empty($arm_max_field_id) ? $arm_max_field_id : $field['arm_form_field_id'];
													$field_options = maybe_unserialize( $field['arm_form_field_option'] );
													if ( ($isRegister || $isEditProfile) && $field_options['type'] == 'submit' ) {
														$submitBtnOptions = $field;
													} elseif ( ( $isRegister || $isEditProfile ) && $field_options['type'] == 'social_fields' ) {
														$socialFieldsOptions = $field;
													} else {
														$liStyle         = '';
														$show_rememberme = ( isset( $armForm->settings['show_rememberme'] ) ) ? $armForm->settings['show_rememberme'] : 0;
														if ( $field_options['type'] == 'rememberme' && $show_rememberme != 1 ) {
															$liStyle = 'display:none;';
														}
														$sortable_class = '';
														if ( $field_options['type'] == 'section' ) {
															$sortable_class  .= ' arm_section_fields_wrapper';
															$margin           = isset( $field_options['margin'] ) ? $field_options['margin'] : array();
															$margin['top']    = ( isset( $margin['top'] ) && is_numeric( $margin['top'] ) ) ? $margin['top'] : 20;
															$margin['bottom'] = ( isset( $margin['bottom'] ) && is_numeric( $margin['bottom'] ) ) ? $margin['bottom'] : 20;
															$liStyle         .= 'margin-top:' . $margin['top'] . 'px !important;';
															$liStyle         .= 'margin-bottom:' . $margin['bottom'] . 'px !important;';
														}
														$ref_field_id = isset( $field_options['ref_field_id'] ) ? $field_options['ref_field_id'] : 0;
														if ( isset( $field_options['hide_username'] ) && $field_options['hide_username'] == 1 ) {
															$hide_username_class = 'hide_username_class';
														} else {
															$hide_username_class = '';
														}
														?>
													<li class="arm-df__form-group arm_form_field_sortable arm-df__form-group_<?php echo esc_attr($field_options['type']); ?> <?php echo esc_attr($sortable_class); ?> <?php echo esc_attr($hide_username_class); ?>" id="arm-df__form-group_<?php echo intval($form_field_id); ?>" data-field_id="<?php echo intval($form_field_id); ?>" data-type="<?php echo esc_attr($field_options['type']); ?>" data-meta_key="<?php echo isset( $field_options['meta_key'] ) ? esc_attr($field_options['meta_key']) : ''; ?>" data-ref_field="<?php echo intval($ref_field_id); ?>" style="<?php echo $liStyle; //phpcs:ignore ?>">
																																		<?php
																																		if ( ! in_array( $field_options['type'], $arm_form_fields_cl_omited_fields ) && isset( $field_options['meta_key'] ) ) {
																																			$arm_form_fields_for_cl[ $field_options['meta_key'] ] = $field_options['label'];
																																		}
																																		// Generate Field HTML

																																		$arm_member_forms->arm_member_form_get_field_html( $oformid, $form_field_id, $field_options, 'inactive', $armForm, $isEditProfile );
																																		?>
													</li><!--/.End `arm-df__form-group`./-->
														<?php
														if ( $oformarmtype == 'login' && $field_options['type'] == 'submit' ) {
															?>
														<li class="arm-df__form-group arm_form_field_sortable arm-df__form-group_forgot_link arm_forgot_password_below_link arm_forgotpassword_link arm-df__form-group_armforgotpassword <?php echo ( $show_fp_link != '1' ) ? 'hidden_section' : ''; ?>" id="arm-df__form-group_0_0" data-field_id="0" data-type="forgot_link" data-meta_key="forgot_link"><?php echo $forgot_password_link_label; //phpcs:ignore ?></li><!--/.End `arm-df__form-group`./-->
																																																													<?php
														}
													}
													if(!empty($arm_max_field_id))
													{
							                                                	$arm_max_field_id++;
													}
												}
												?>
											<?php } else { ?>
											<li></li>
										<?php } ?>
									</ul>
									<?php if ( ! empty( $submitBtnOptions ) ) { ?>
										<ul class="arm-df__fields-wrapper arm_no_sortable arm-df__fields-wrapper_<?php echo intval($oformid); ?> arm_form_editor_submit_part"  data-form_id="<?php echo intval($oformid); ?>">
											<?php if ( ! empty( $socialFieldsOptions ) && $arm_social_feature->isSocialFeature ) { ?>
												<?php
												$socialFieldID = $socialFieldsOptions['arm_form_field_id'];
												$field_options = maybe_unserialize( $socialFieldsOptions['arm_form_field_option'] );
												?>
												<li class="arm-df__form-group arm-df__form-group_social_fields" id="arm-df__form-group_<?php echo $socialFieldID; ?>" data-type="social_fields" data-field_id="<?php echo intval($socialFieldID); ?>"><?php $arm_member_forms->arm_member_form_get_field_html( $oformid, $socialFieldID, $field_options, 'inactive', $armForm ); //phpcs:ignore ?></li>
											<?php } ?>
											<?php
											$form_field_id = $submitBtnOptions['arm_form_field_id'];
											$field_options = maybe_unserialize( $submitBtnOptions['arm_form_field_option'] );
											?>
											<li class="arm-df__form-group arm-df__form-group_submit" id="arm-df__form-group_<?php echo intval($form_field_id); ?>" data-field_id="<?php echo intval($form_field_id); ?>" data-type="submit">
																																	   <?php
																																		$arm_member_forms->arm_member_form_get_field_html( $oformid, $form_field_id, $field_options, 'inactive', $armForm, $isEditProfile );
																																		?>
												</li>
										</ul>
									<?php } ?>
									<?php if ( $oformarmtype == 'login' ) { ?>

										<ul class="arm-df__fields-wrapper arm_no_sortable arm_set_editor_ul arm-df__fields-wrapper_<?php echo intval($oformid); ?> arm_login_links_wrapper" data-form_id="<?php echo intval($oformid); ?>" id="arm_form_editor_all_login_options">
											<?php
											    $arm_additional_social_fields = '';
											    echo apply_filters('arm_additional_social_fields_sec',$arm_additional_social_fields,$firstForm,$social_networks,$activeSocialNetworks,$oformarmtype,$form_settings,$formSocialNetworksSettings,$oformid,'bottom'); //phpcs:ignore
											?>
											<li class="arm-df__form-group_armbothlink arm_width_100_pct" style="<?php echo ( $show_reg_link != '1' && $show_fp_link != '1' ) ? 'display:none;' : ''; ?>">
												<span class="arm_registration_link arm_reg_login_links arm-df__form-group_armregister <?php echo ( $show_reg_link != '1' ) ? 'hidden_section' : ''; ?>" id="arm-df__form-group_armregister"><?php echo $registration_link_label; //phpcs:ignore ?></span>
											</li>
										</ul>
									<?php } ?>
									<div class="armclear"></div>
								</div>
								<div class="arm_editor_form_divider"></div>
							<?php } ?>
						<?php } ?>
						<input type="hidden" name="arm_login_form_ids" id="arm_login_form_ids" value="<?php echo isset( $arm_form_ids ) ? implode( ',', $arm_form_ids ) : ''; //phpcs:ignore ?>" />
						<div class="armclear"></div>
					</div>
				</div><!--./ END `.arm_editor_center`-->
				<a href="javascript:void(0)" class="arm_slider_arrow arm_slider_arrow_left arm_editor_right_arrow_left armhelptip hidden_section" title="<?php esc_html_e( 'Open Settings & Styles', 'armember-membership' ); ?>" data-id="arm_editor_right"></a>
				<div class="arm_editor_right">
					<a href="javascript:void(0)" class="arm_slider_arrow arm_slider_arrow_right armhelptip" title="<?php esc_html_e( 'Hide Settings & Styles', 'armember-membership' ); ?>" data-id="arm_editor_right"></a>
					<div class="arm_editor_right_wrapper tabs-container" id="tabs-container1">
						<ul class="tabs-menu arm_tab_menu">
							<li class="current"><a href="#tabsetting-1"><?php esc_html_e( 'Basic Options', 'armember-membership' ); ?></a></li>
							<?php $arm_pro_advanced_tab_option = '';
							echo apply_filters('arm_pro_advance_option_tab',$arm_pro_advanced_tab_option); //phpcs:ignore?>
						</ul>
						<div class="tab arm_form_settings_styles_container arm_width_100_pct" id="arm_form_settings_styles_container" >
							<div id="tabsetting-1" class="arm-tab-content">
								<div class="arm_right_section_heading style_setting_main_heading"><?php esc_html_e( 'Styling & Formatting', 'armember-membership' ); ?></div>
								<div class="arm_right_section_body">
									<table class="arm_form_settings_style_block arm_tbl_label_left_input_right">
										<input type="hidden" class="arm_switch_radio" name="arm_form_settings[display_direction]" value="vertical">

										<tr class="arm_form_style_options">
											<td class="arm_width_100"><label class="arm_form_opt_label"><?php esc_html_e( 'Form Width', 'armember-membership' ); ?></label></td>
											<td>
												<div class="arm_right">
													<input type="text" id="arm_form_width1" class="arm_form_width arm_form_setting_input armMappedTextbox arm_width_130" data-id="arm_form_width" value="<?php echo ! empty( $form_settings['style']['form_width'] ) ? esc_attr($form_settings['style']['form_width']) : '600'; ?>"  onkeydown="javascript:return checkNumber(event)"/><span>&nbsp;(px)</span>
												</div>
											</td>
										</tr>
										<tr class="arm_form_style_options">
											<td><label class="arm_form_opt_label"><?php esc_html_e( 'Input Style', 'armember-membership' ); ?></label></td>
											<td>
												<div class="arm_right">
													<input type='hidden' id="arm_manage_form_layout" class="arm_manage_form_layout armMappedTextbox" data-id="arm_manage_form_layout1" value="<?php echo esc_attr($formLayout); ?>" data-old_value="<?php echo esc_attr($formLayout); ?>"/>
													<dl class="arm_selectbox column_level_dd arm_width_160">
														<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
														<dd>
															<ul data-id="arm_manage_form_layout">
																<li data-label="<?php esc_html_e( 'Material Outline', 'armember-membership' ); ?>" data-value="writer_border"><?php esc_html_e( 'Material Outline', 'armember-membership' ); ?></li>
																<li data-label="<?php esc_html_e( 'Material Style', 'armember-membership' ); ?>" data-value="writer"><?php esc_html_e( 'Material Style', 'armember-membership' ); ?></li>
																<li data-label="<?php esc_html_e( 'Standard Style', 'armember-membership' ); ?>" data-value="iconic"><?php esc_html_e( 'Standard Style', 'armember-membership' ); ?></li>
																<li data-label="<?php esc_html_e( 'Rounded Style', 'armember-membership' ); ?>" data-value="rounded"><?php esc_html_e( 'Rounded Style', 'armember-membership' ); ?></li>
															</ul>
														</dd>
													</dl>
												</div>
											</td>
										</tr>
										<tr class="arm_form_style_color_schemes">
											<td colspan="2"><label class="arm_form_opt_label"><?php esc_html_e( 'Color Scheme', 'armember-membership' ); ?></label></td>
										</tr>
										<tr class="arm_form_style_color_schemes">
											<td colspan="2">
											   
													
													
													<div id="arm_color_scheme_container" class="arm_form_style_color_schemes">
											
												
																<div class="c_schemes">
																	<?php foreach ( $formColorSchemes as $color => $color_opt ) { ?>
																		
																			<label class="arm_color_scheme_block arm_color_scheme_block_<?php echo esc_attr($color); ?> <?php echo ( $form_settings['style']['color_scheme'] == $color ) ? 'arm_color_box_active' : ''; ?>" style="<?php echo ($color == 'custom') ? 'display:none;' : '' ?>background-color:<?php echo isset( $color_opt['main_color'] ) ? esc_attr($color_opt['main_color']) : ''; ?>">
																				<input id="arm_color_block_radio_<?php echo esc_attr($color); ?>" type="radio" name="arm_form_settings[style][color_scheme]" value="<?php echo esc_attr($color); ?>" class="arm_color_block_radio armMappedRadio" data-id="arm_color_block_radio_<?php echo esc_attr($color); ?>1" <?php checked( $form_settings['style']['color_scheme'], $color ); ?>/>
																			</label>
																		<?php } ?>
																		<div class="armclear"></div>
																	<?php
																	if($ARMemberLite->is_arm_pro_active){?>
																	<a href="javascript:void(0);" class="arm_color_scheme_nav_link"><?php esc_html_e('Advanced Options', 'armember-membership'); ?></a>
																	<?php } else {?>
																	<label class="arm_color_scheme_block arm_color_scheme_block_custom">
																		<span><?php esc_html_e( 'Custom Color', 'armember-membership' ); ?></span>
																	</label>
																	<?php }?>
																</div>
													<?php if(!$ARMemberLite->is_arm_pro_active){?>	
													<div class="arm_form_custom_style_opts arm_slider_box arm_custom_scheme_box">
														<div class="arm_form_field_settings_menu arm_slider_box_container arm_custom_scheme_container">
															<div class="arm_slider_box_arrow arm_custom_scheme_arrow"></div>
															<div class="arm_slider_box_heading" style="display: none;"><?php esc_html_e( 'Custom Setting', 'armember-membership' ); ?></div>
															<div class="arm_slider_box_body arm_custom_scheme_block">
																<?php
																$formColorScheme = isset( $form_settings['style']['color_scheme'] ) ? $form_settings['style']['color_scheme'] : 'blue';
																$formColors      = isset( $formColorSchemes[ $formColorScheme ] ) ? $formColorSchemes[ $formColorScheme ] : array();
																?>
																<table class="arm_form_settings_style_block">
																	<tr>
																		<td class="arm_custom_scheme_main_label" colspan="4"><?php esc_html_e( 'Form', 'armember-membership' ); ?></td>
																	</tr>
																	<tr>
																		<td class="arm_custom_scheme_sub_label">
																			<input id="arm_form_title_font_color" type="text" name="arm_form_settings[style][form_title_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['form_title_font_color']); ?>"/>
																			<span><?php esc_html_e( 'Form Title', 'armember-membership' ); ?></span>
																		</td>
																		<td class="arm_custom_scheme_sub_label">
																			<input id="arm_form_bg_color" type="text" name="arm_form_settings[style][form_bg_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['form_bg_color']); ?>"/>
																			<span><?php esc_html_e( 'Form Background', 'armember-membership' ); ?></span>
																		</td>
																		<td class="arm_custom_scheme_sub_label">
																			<input id="arm_form_border_color" type="text" name="arm_form_settings[style][form_border_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['form_border_color']); ?>"/>
																			<span><?php esc_html_e( 'Form Border', 'armember-membership' ); ?></span>
																		</td>
																		<?php if ( ! $isRegister ) { ?>
																			<td class="arm_custom_scheme_sub_label">
																				<input id="arm_login_link_font_color" type="text" name="arm_form_settings[style][login_link_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['login_link_font_color']); ?>"/>
																				<span><?php esc_html_e( 'Forgot / Register Link', 'armember-membership' ); ?></span>
																			</td>
																		<?php } ?>


																		<?php
																		if ( $isRegister ) {
																			?>
																				<td class="arm_custom_scheme_sub_label">
																					<input id="arm_register_link_font_color" type="text" name="arm_form_settings[style][register_link_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr( $form_settings['style']['register_link_font_color'] ); ?>"/>
																					<span><?php esc_html_e( 'Register Link', 'armember-membership' ); ?></span>
																				</td>
																			<?php
																		}
																		?>
																	</tr>
																	<tr>
																		<td class="arm_custom_scheme_divider" colspan="4"></td>
																	</tr>
																	<tr>
																		<td class="arm_custom_scheme_main_label" colspan="4"><?php esc_html_e( 'Label & Input Fields', 'armember-membership' ); ?></td>
																	</tr>
																	<tr>
																		<td class="arm_custom_scheme_sub_label">
																			<input id="arm_field_font_color" type="text" name="arm_form_settings[style][field_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['field_font_color']); ?>"/>
																			<span><?php esc_html_e( 'Field Font', 'armember-membership' ); ?></span>
																		</td>
																		<td class="arm_custom_scheme_sub_label">
																			<input id="arm_field_border_color" type="text" name="arm_form_settings[style][field_border_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['field_border_color']); ?>"/>
																			<span><?php esc_html_e( 'Field Border', 'armember-membership' ); ?></span>
																		</td>
																		<td class="arm_custom_scheme_sub_label">
																			<input id="arm_field_focus_color" type="text" name="arm_form_settings[style][field_focus_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['field_focus_color']); ?>" data-form_id="<?php echo intval($form_id); ?>"/>
																			<span><?php esc_html_e( 'Field Focus', 'armember-membership' ); ?></span>
																		</td>
																		<td class="arm_custom_scheme_sub_label arm_custom_scheme_sub_label_no_writer <?php echo ( $formLayout == 'writer' ) ? 'hidden_section' : ''; ?>">
																			<input id="arm_field_bg_color" type="text" name="arm_form_settings[style][field_bg_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['field_bg_color']); ?>" data-form_id="<?php echo intval($form_id); ?>"/>
																			<span><?php esc_html_e( 'Field Background', 'armember-membership' ); ?></span>
																		</td>
																	</tr>
																	<tr>
																		<td class="arm_custom_scheme_sub_label">
																			<input id="arm_lable_font_color" type="text" name="arm_form_settings[style][lable_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['lable_font_color']); ?>"/>
																			<span><?php esc_html_e( 'Label Font', 'armember-membership' ); ?></span>
																		</td>
																		<td></td>
																		<td></td>
																		<td></td>
																	</tr>
																	<tr>
																		<td class="arm_custom_scheme_divider" colspan="4"></td>
																	</tr>
																	<tr>
																		<td class="arm_custom_scheme_main_label" colspan="4"><?php esc_html_e( 'Submit Button', 'armember-membership' ); ?></td>
																	</tr>
																	<tr>
																		<td class="arm_custom_scheme_sub_label">
																			<input id="arm_button_back_color" type="text" name="arm_form_settings[style][button_back_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['button_back_color']); ?>"/>
																			<span><?php esc_html_e( 'Button Background', 'armember-membership' ); ?></span>
																		</td>
																		<?php
																		if ( in_array( $reference_template, array( 3 ) ) ) {
																			?>
																			<td class="arm_custom_scheme_sub_label" id="arm_button_gradient_color" colspan="2">
																				<input id="arm_button_back_color_gradient" type="text" name="arm_form_settings[style][button_back_color_gradient]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['button_back_color_gradient']); ?>"/>
																				<span><?php esc_html_e( 'Button Background 2', 'armember-membership' ); ?></span>
																			</td>
																		<?php } ?>
																		<td class="arm_custom_scheme_sub_label arm_button_font_color_wrapper <?php echo ( ! empty( $form_settings['style']['button_style'] ) && $form_settings['style']['button_style'] == 'border' ) ? 'hidden_section' : ''; ?>">
																			<input id="arm_button_font_color" type="text" name="arm_form_settings[style][button_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['button_font_color']); ?>"/>
																			<span><?php esc_html_e( 'Button Font', 'armember-membership' ); ?></span>
																		</td>
																		<?php
																		if ( ! in_array( $reference_template, array( 3 ) ) ) {
																			?>
																			<td class="arm_custom_scheme_sub_label">
																				<input id="arm_button_hover_color" type="text" name="arm_form_settings[style][button_hover_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['button_hover_color']); ?>"/>
																				<span><?php esc_html_e( 'Hover Background', 'armember-membership' ); ?></span>
																			</td>
																			<?php
																		}
																		?>
																		<?php
																		if ( ! in_array( $reference_template, array( 3 ) ) ) {
																			?>
																			<td class="arm_custom_scheme_sub_label arm_button_hover_font_color_wrapper <?php echo ( ! empty( $form_settings['style']['button_style'] ) && $form_settings['style']['button_style'] == 'reverse_border' ) ? 'hidden_section' : ''; ?>">
																				<input id="arm_button_hover_font_color" type="text" name="arm_form_settings[style][button_hover_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['button_hover_font_color']); ?>"/>
																				<span><?php esc_html_e( 'Hover Font', 'armember-membership' ); ?></span>
																			</td>
																		<?php } ?>
																	</tr>
																	<tr>
																		<?php
																		if ( in_array( $reference_template, array( 3 ) ) ) {
																			?>
																			<td class="arm_custom_scheme_sub_label">
																				<input id="arm_button_hover_color" type="text" name="arm_form_settings[style][button_hover_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['button_hover_color']); ?>"/>
																				<span><?php esc_html_e( 'Hover Background', 'armember-membership' ); ?></span>
																			</td>
																			<?php
																		}
																		?>
																		<?php
																		if ( in_array( $reference_template, array( 3 ) ) ) {
																			?>
																			<td class="arm_custom_scheme_sub_label" id="arm_button_hover_gradient_color" colspan="2">
																				<input id="arm_button_hover_color_gradient" type="text" name="arm_form_settings[style][button_hover_color_gradient]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['button_hover_color_gradient']); ?>" />
																				<span><?php esc_html_e( 'Hover Background 2', 'armember-membership' ); ?></span>
																			</td>
																		<?php } ?>
																		<?php
																		if ( in_array( $reference_template, array( 3 ) ) ) {
																			?>
																			<td class="arm_custom_scheme_sub_label arm_button_hover_font_color_wrapper <?php echo ( ! empty( $form_settings['style']['button_style'] ) && $form_settings['style']['button_style'] == 'reverse_border' ) ? 'hidden_section' : ''; ?>">
																				<input id="arm_button_hover_font_color" type="text" name="arm_form_settings[style][button_hover_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['button_hover_font_color']); ?>"/>
																				<span><?php esc_html_e( 'Hover Font', 'armember-membership' ); ?></span>
																			</td>
																		<?php } ?>
																	</tr>
																	<tr>
																		<td class="arm_custom_scheme_divider" colspan="4"></td>
																	</tr>
																	<tr class=" arm_custom_scheme_sub_label_no_writer <?php echo ( $formLayout == 'writer' ) ? 'hidden_section' : ''; ?>">
																		<td class="arm_custom_scheme_main_label" colspan="4"><?php esc_html_e( 'Prefix / Suffix Icon Color', 'armember-membership' ); ?></td>
																	</tr>
																	<tr class=" arm_custom_scheme_sub_label_no_writer <?php echo ( $formLayout == 'writer' ) ? 'hidden_section' : ''; ?>">
																		<td class="arm_custom_scheme_sub_label">
																			<input id="arm_prefix_suffix_color" type="text" name="arm_form_settings[style][prefix_suffix_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['prefix_suffix_color']); ?>"/>
																			<span><?php esc_html_e( 'Icon Color', 'armember-membership' ); ?></span>
																		</td>
																		<td></td>
																		<td></td>
																		<td></td>
																	</tr>
																	<tr>
																		<td class="arm_custom_scheme_divider arm_custom_scheme_sub_label_no_writer <?php echo ( $formLayout == 'writer' ) ? 'hidden_section' : ''; ?>" colspan="4"></td>
																	</tr>
																	<tr>
																		<td class="arm_custom_scheme_main_label" colspan="4"><?php esc_html_e( 'Validation Color', 'armember-membership' ); ?></td>
																	</tr>
																	<tr>
																		<?php
																		$d_error_font_color     = $form_settings['style']['error_font_color'];
																		$d_error_field_bg_color = $form_settings['style']['error_field_bg_color'];
																		if ( $formLayout == 'writer' ) {
																			$d_error_font_color     = $form_settings['style']['error_field_bg_color'];
																			$d_error_field_bg_color = $form_settings['style']['error_font_color'];
																		}
																		?>
																		<td class="arm_custom_scheme_sub_label">
																			<input id="arm_error_font_color" type="text" name="arm_form_settings[style][error_font_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['error_font_color']); ?>" data-old_color="<?php echo esc_attr($d_error_font_color); ?>"/>
																			<span><?php esc_html_e( 'Validation Message Font', 'armember-membership' ); ?></span>
																		</td>
																		<td class="arm_custom_scheme_sub_label">
																			<input id="arm_error_field_border_color" type="text" name="arm_form_settings[style][error_field_border_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['error_field_border_color']); ?>"/>
																			<span><?php esc_html_e( 'Error Field Border', 'armember-membership' ); ?></span>
																		</td>
																		<td class="arm_custom_scheme_sub_label arm_custom_scheme_sub_label_no_writer <?php echo ( $formLayout == 'writer' ) ? 'hidden_section' : ''; ?>">
																			<input id="arm_error_field_bg_color" type="text" name="arm_form_settings[style][error_field_bg_color]" class="arm_colorpicker arm_custom_scheme_colorpicker" value="<?php echo esc_attr($form_settings['style']['error_field_bg_color']); ?>" data-old_color="<?php echo esc_attr($d_error_field_bg_color); ?>"/>
																			<span><?php esc_html_e( 'Validation Message Background', 'armember-membership' ); ?></span>
																		</td>
																	</tr>
																</table>
															</div>
														</div>
													</div>
													<?php }?>
											  
											</div> 
												
											</td>
										</tr>
									</table>
									<div class="armclear"></div>
									
								</div>
								<?php
								$arm_recaptcha_v3_section = '';
										echo apply_filters('arm_google_recaptcha_form_section',$arm_recaptcha_v3_section,$all_global_settings,$form_settings,$isRegister,$isEditProfile); //phpcs:ignore
								if ( $isRegister ) {
									?>
										<div class="arm_right_section_heading"><?php esc_html_e( 'Register Form Options', 'armember-membership' ); ?></div>
										<div class="arm_right_section_body arm_form_redirection_options arm_padding_bottom_15">
											<table class="arm_form_settings_style_block">
											<?php $show_login_link = ( isset( $form_settings['show_login_link'] ) ) ? $form_settings['show_login_link'] : 0; ?>
												<tr>
													<td colspan="2">
														<label class="arm_form_opt_label" for="show_login_link"><?php esc_html_e( 'Display Login Link?', 'armember-membership' ); ?></label>
														<div class="armswitch arm_global_setting_switch arm_vertical_align_middle">
															<input type="checkbox" id="show_login_link" <?php checked( $show_login_link, '1' ); ?> value="1" class="armswitch_input" name="arm_form_settings[show_login_link]"/>
															<label for="show_login_link" class="armswitch_label"></label>
														</div>
													</td>
												</tr>

												<?php
													$arm_default_login_link  = esc_html__( 'Already have an account?', 'armember-membership' );
													$arm_default_login_link .= ' ';
													$arm_default_login_link .= '[ARMLINK]';
													$arm_default_login_link .= esc_html__( 'Login', 'armember-membership' );
													$arm_default_login_link .= '[/ARMLINK]';
												?>

												<tr class="arm_login_link_options <?php echo ( $show_login_link != '1' ) ? 'hidden_section' : ''; ?>">
													<td colspan="2">
														<label class="arm_form_opt_label"><?php esc_html_e( 'Login Link Label', 'armember-membership' ); ?>:</label>
														<div class="arm_form_opt_input">
															<input type="text" name="arm_form_settings[login_link_label]" value="<?php echo ( ! empty( $form_settings['login_link_label'] ) ) ? esc_attr(stripslashes( $form_settings['login_link_label'] )) : $arm_default_login_link; //phpcs:ignore ?>" class="login_link_label_input">
															<span class="arm_info_text"><?php esc_html_e( 'To make partial part of sentence clickable, please use this pattern', 'armember-membership' ); ?> <strong>[ARMLINK]</strong>xx<strong>[/ARMLINK]</strong></span>
														</div>
														<div class="armclear"></div>
														<div class="arm_form_opt_input">
															<?php
															$login_link_type = ( isset( $form_settings['login_link_type'] ) ) ? $form_settings['login_link_type'] : 'page';
															$arm_login_page_redirection_field= '';
															echo apply_filters('arm_login_page_redirection_field',$arm_login_page_redirection_field,$login_link_type); //phpcs:ignore
															$arm_hidden_class  = '';
															if(!$ARMemberLite->is_arm_pro_active){
																$arm_hidden_class  = 'hidden_section';
															}
															?>
															<label>
																<input type="radio" id="arm_login_link_type_page" name="arm_form_settings[login_link_type]" value="page" class="arm_login_link_type arm_iradio <?php echo esc_attr($arm_hidden_class); ?>" <?php checked( $login_link_type, 'page' ); ?>>
																<span><?php esc_html_e( 'Redirect to Page', 'armember-membership' ); ?></span>
															</label>
															<div class="armclear"></div>
															<div class="arm_login_link_type_option arm_margin_top_5 arm_login_link_type_option_modal <?php echo ( $login_link_type != 'modal' ) ? 'hidden_section' : ''; ?>">
																<?php
																$defaultLoginForm                = $arm_member_forms->arm_get_default_form_id( 'Login' );
																$loginFormsList                  = $arm_member_forms->arm_get_member_forms_by_type( 'Login' );
																$login_link_type_modal           = ( isset( $form_settings['login_link_type_modal'] ) ) ? $form_settings['login_link_type_modal'] : $defaultLoginForm;
																$login_link_type_modal_form_type = ( isset( $form_settings['login_link_type_modal_form_type'] ) ) ? $form_settings['login_link_type_modal_form_type'] : 'arm_form';
																?>
																<input type="hidden" id="login_link_type_modal_form_type" name="arm_form_settings[login_link_type_modal_form_type]" value="<?php echo esc_attr($login_link_type_modal_form_type); //phpcs:ignore ?>"/>

																<input type="hidden" id="login_link_type_modal_form" name="arm_form_settings[login_link_type_modal]" value="<?php echo esc_attr($login_link_type_modal); //phpcs:ignore ?>"/>
																<dl class="arm_selectbox column_level_dd arm_width_250 arm_margin_top_12">
																	<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																	<dd>
																		<ul data-id="login_link_type_modal_form">
																			<?php if ( ! empty( $loginFormsList ) ) { ?>
																				<?php foreach ( $loginFormsList as $mrform ) { ?>
																					<li data-label="<?php echo esc_attr($mrform['arm_form_label']); ?>" data-value="<?php echo esc_attr($mrform['arm_form_id']); ?>" data-form_type='arm_form'><?php echo esc_attr($mrform['arm_form_label']); ?></li>
																				<?php } ?>
																			<?php } ?>
																		</ul>
																	</dd>
																</dl>
															</div>
															<div class="arm_login_link_type_option arm_login_link_type_option_page <?php echo ( $login_link_type != 'page' ) ? 'hidden_section' : ''; ?>">
																<?php
																	$login_link_type_page = ( isset( $form_settings['login_link_type_page'] ) ) ? $form_settings['login_link_type_page'] : $arm_global_settings->arm_get_single_global_settings( 'login_page_id', 0 );
																	$arm_global_settings->arm_wp_dropdown_pages(
																		array(
																			'selected' => $login_link_type_page,
																			'name' => 'arm_form_settings[login_link_type_page]',
																			'id' => 'login_link_type_page',
																			'show_option_none' => 'Select Page',
																			'option_none_value' => '',
																			'class' => 'login_link_type_page',
																		)
																	);
																?>
															</div>
														</div>
													</td>
												</tr>
											</table>
										</div>
									<?php
								}

								?>



								<?php
								$form_submit_action_type = ! empty( $form_settings['redirect_type'] ) ? $form_settings['redirect_type'] : 'page';
								$f_redirect_page         = ( ! empty( $form_settings['redirect_page'] ) ) ? $form_settings['redirect_page'] : $thank_you_page_id;
								?>
								<?php if ( $isRegister || $isEditProfile) { ?>
									<div class="arm_right_section_heading"><?php esc_html_e( 'Submit Action', 'armember-membership' ); ?></div>
								<?php } else { ?>
									<div class="arm_right_section_heading"><?php esc_html_e( 'Login Form Options', 'armember-membership' ); ?></div>
								<?php } ?>
								<div class="arm_right_section_body arm_form_redirection_options arm_padding_bottom_15">
									<table class="arm_form_settings_style_block">
										<?php if ( ! $isRegister && !$isEditProfile) { ?>
											<?php $show_rememberme = ( isset( $form_settings['show_rememberme'] ) ) ? $form_settings['show_rememberme'] : 0; ?>
											<tr>
												<td colspan="2">
													<label class="arm_form_opt_label" for="show_rememberme"><?php esc_html_e( 'Remember Me Checkbox', 'armember-membership' ); ?></label>
													<div class="armswitch arm_global_setting_switch arm_vertical_align_middle">
														<input type="checkbox" id="show_rememberme" <?php checked( $show_rememberme, '1' ); ?> value="1" class="armswitch_input arm_show_rememberme_chk" name="arm_form_settings[show_rememberme]"/>
														<label for="show_rememberme" class="armswitch_label"></label>
													</div>
												</td>
											</tr>
											<?php $show_registration_link = ( isset( $form_settings['show_registration_link'] ) ) ? $form_settings['show_registration_link'] : 0; ?>
											<tr>
												<td colspan="2">
													<label class="arm_form_opt_label" for="show_registration_link"><?php esc_html_e( 'Display Registration Link?', 'armember-membership' ); ?></label>
													<div class="armswitch arm_global_setting_switch arm_vertical_align_middle">
														<input type="checkbox" id="show_registration_link" <?php checked( $show_registration_link, '1' ); ?> value="1" class="armswitch_input" name="arm_form_settings[show_registration_link]"/>
														<label for="show_registration_link" class="armswitch_label"></label>
													</div>
												</td>
											</tr>
											<tr class="arm_registration_link_options <?php echo ( $show_registration_link != '1' ) ? 'hidden_section' : ''; ?>">
												<td colspan="2">
													<label class="arm_form_opt_label"><?php esc_html_e( 'Registration Link Label', 'armember-membership' ); ?>:</label>
													<div class="arm_form_opt_input">
														<input type="text" name="arm_form_settings[registration_link_label]" value="<?php echo ( isset( $form_settings['registration_link_label'] ) ) ? esc_attr(stripslashes( $form_settings['registration_link_label'] )) : esc_html__( 'Register', 'armember-membership' ); //phpcs:ignore ?>" class="registration_link_label_input">
														<span class="arm_info_text"><?php esc_html_e( 'To make partial part of sentence clickable, please use this pattern', 'armember-membership' ); ?> <strong>[ARMLINK]</strong>xx<strong>[/ARMLINK]</strong></span>
													</div>
													<div class="armclear"></div>
													<div class="arm_form_opt_input">
														<?php
														$registration_link_type = ( isset( $form_settings['registration_link_type'] ) ) ? $form_settings['registration_link_type'] : 'page';

														$arm_register_page_redirection_field= '';
														echo apply_filters('arm_register_page_redirection_field',$arm_register_page_redirection_field,$registration_link_type); //phpcs:ignore
														$arm_hidden_class  = '';
														if(!$ARMemberLite->is_arm_pro_active){
															$arm_hidden_class  = 'hidden_section';
														}
														?>
														
														<label>
															<input type="radio" id="arm_registration_link_type_page" name="arm_form_settings[registration_link_type]" value="page" class="arm_registration_link_type arm_iradio <?php echo esc_attr($arm_hidden_class);?>" <?php checked( $registration_link_type, 'page' ); ?>>
															<span><?php esc_html_e( 'Redirect to Page', 'armember-membership' ); ?></span>
														</label>
														<div class="armclear"></div>
														<div class="arm_registration_link_type_option arm_registration_link_type_option_modal <?php echo ( $registration_link_type != 'modal' ) ? 'hidden_section' : ''; ?>">
															<?php
															$defaultRegForm                         = $arm_member_forms->arm_get_default_form_id( 'registration' );
															$regFormsList                           = $arm_member_forms->arm_get_member_forms_by_type( 'registration' );
															$registration_link_type_modal           = ( isset( $form_settings['registration_link_type_modal'] ) ) ? $form_settings['registration_link_type_modal'] : $defaultRegForm;
															$setup_data                             = $wpdb->get_results( 'SELECT `arm_setup_id`, `arm_setup_name` FROM `' . $ARMemberLite->tbl_arm_membership_setup . '`', ARRAY_A );//phpcs:ignore --Reason: $tbl_arm_membership_setup is a table name. False Positive Alarm. No need to prepare query without Where clause.
															$registration_link_type_modal_form_type = ( isset( $form_settings['registration_link_type_modal_form_type'] ) ) ? $form_settings['registration_link_type_modal_form_type'] : 'arm_form';
															?>
															<input type="hidden" id="registration_link_type_modal_form_type" name="arm_form_settings[registration_link_type_modal_form_type]" value="<?php echo esc_html($registration_link_type_modal_form_type); ?>"/>

															<input type="hidden" id="registration_link_type_modal_form" name="arm_form_settings[registration_link_type_modal]" value="<?php echo $registration_link_type_modal; //phpcs:ignore ?>"/>
															<dl class="arm_selectbox arm_width_100_pct arm_margin_top_12">
																<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																<dd>
																	<ul data-id="registration_link_type_modal_form">
																		<?php if ( ! empty( $regFormsList ) ) { ?>
																			<?php foreach ( $regFormsList as $mrform ) { ?>
																				<li data-label="<?php echo esc_attr($mrform['arm_form_label']); ?>" data-value="<?php echo esc_attr($mrform['arm_form_id']); ?>" data-form_type='arm_form'><?php echo esc_attr($mrform['arm_form_label']); ?></li>
																			<?php } ?>
																		<?php } ?>


																		<?php if ( ! empty( $setup_data ) ) { ?>
																			<?php foreach ( $setup_data as $arm_setup ) { ?>
																				<li data-label="<?php echo esc_attr($arm_setup['arm_setup_name']); ?>" data-value="<?php echo esc_attr($arm_setup['arm_setup_id']); ?>" data-form_type='arm_setup'><?php echo esc_attr($arm_setup['arm_setup_name']); ?></li>
																			<?php } ?>
																		<?php } ?>
																	</ul>
																</dd>
															</dl>
														</div>
														<div class="arm_registration_link_type_option arm_registration_link_type_option_page <?php echo ( $registration_link_type != 'page' ) ? 'hidden_section' : ''; ?>">
															<?php
															$registration_link_type_page = ( isset( $form_settings['registration_link_type_page'] ) ) ? $form_settings['registration_link_type_page'] : $arm_global_settings->arm_get_single_global_settings( 'register_page_id', 0 );
															$arm_global_settings->arm_wp_dropdown_pages(
																array(
																	'selected' => $registration_link_type_page,
																	'name' => 'arm_form_settings[registration_link_type_page]',
																	'id' => 'registration_link_type_page',
																	'show_option_none' => 'Select Page',
																	'option_none_value' => '',
																	'class' => 'registration_link_type_page',
																)
															);
															?>
														</div>
													</div>
												</td>
											</tr>
											<?php $show_forgot_password_link = ( isset( $form_settings['show_forgot_password_link'] ) ) ? $form_settings['show_forgot_password_link'] : 0; ?>
											<tr>
												<td colspan="2">
													<label class="arm_form_opt_label" for="show_forgot_password_link"><?php esc_html_e( 'Display Forgot Password Link?', 'armember-membership' ); ?></label>
													<div class="armswitch arm_global_setting_switch arm_vertical_align_middle">
														<input type="checkbox" id="show_forgot_password_link" <?php checked( $show_forgot_password_link, '1' ); ?> value="1" class="armswitch_input" name="arm_form_settings[show_forgot_password_link]"/>
														<label for="show_forgot_password_link" class="armswitch_label"></label>
													</div>
												</td>
											</tr>
											<tr class="arm_forgot_password_link_options <?php echo ( $show_forgot_password_link != '1' ) ? 'hidden_section' : ''; ?>">
												<td colspan="2">
													<label class="arm_form_opt_label"><?php esc_html_e( 'Forgot Password Link Label', 'armember-membership' ); ?>:</label>
													<div class="arm_form_opt_input">
														<input type="text" name="arm_form_settings[forgot_password_link_label]" value="<?php echo ( isset( $form_settings['forgot_password_link_label'] ) ) ? esc_attr( stripslashes( $form_settings['forgot_password_link_label'] )) : esc_html__( 'Forgot Password', 'armember-membership' ); //phpcs:ignore ?>" class="forgot_password_link_label_input">
														<span class="arm_info_text"><?php esc_html_e( 'To make partial part of sentence clickable, please use this pattern', 'armember-membership' ); ?> <strong>[ARMLINK]</strong>xx<strong>[/ARMLINK]</strong></span>
													</div>
													<div class="armclear"></div>
													<div class="arm_form_opt_input">
														<?php
														$forgot_password_link_type = ( isset( $form_settings['forgot_password_link_type'] ) ) ? $form_settings['forgot_password_link_type'] : 'modal';
														$arm_forgot_password_page_redirection_field= '';
														echo apply_filters('arm_forgot_password_page_redirection_field',$arm_forgot_password_page_redirection_field,$forgot_password_link_type); //phpcs:ignore
														$arm_hidden_class  = '';
														if(!$ARMemberLite->is_arm_pro_active){
															$arm_hidden_class  = 'hidden_section';
														}
														?>
														
														<label>
															<input type="radio" id="arm_forgot_password_link_type_page" name="arm_form_settings[forgot_password_link_type]" value="page" class="arm_forgot_password_link_type arm_iradio <?php echo esc_attr($arm_hidden_class);?>" <?php checked( $forgot_password_link_type, 'page' ); ?>>
															<span><?php esc_html_e( 'Redirect to Page', 'armember-membership' ); ?></span>
														</label>
														<div class="armclear"></div>
														<div class="arm_forgot_password_link_type_option arm_forgot_password_link_type_option_page <?php echo ( $forgot_password_link_type != 'page' ) ? 'hidden_section' : ''; ?>">
															<?php
															$forgot_password_link_type_page = ( isset( $form_settings['forgot_password_link_type_page'] ) ) ? $form_settings['forgot_password_link_type_page'] : $arm_global_settings->arm_get_single_global_settings( 'forgot_password_page_id', 0 );
															$arm_global_settings->arm_wp_dropdown_pages(
																array(
																	'selected' => $forgot_password_link_type_page,
																	'name' => 'arm_form_settings[forgot_password_link_type_page]',
																	'id' => 'forgot_password_link_type_page',
																	'show_option_none' => 'Select Page',
																	'option_none_value' => '',
																	'class' => 'forgot_password_link_type_page',
																)
															);
															?>
														</div>
													</div>
												</td>
											</tr>
											<?php if(!$ARMemberLite->is_arm_pro_active){?>
											<tr class="arm_registration_link_options <?php echo ( $show_registration_link != '1' ) ? 'hidden_section' : ''; ?>">
												<td colspan="2" class="font_settings_label"><?php esc_html_e( 'Link Position Settings', 'armember-membership' ); ?></td>
												<td></td>
											</tr>    
													   <tr class="arm_registration_link_options <?php echo ( $show_registration_link != '1' ) ? 'hidden_section' : ''; ?>">
															<td colspan="2"><?php esc_html_e( 'Registration Link Margin', 'armember-membership' ); ?>
															<?php
																$registration_link_margin           = ( isset( $form_settings['registration_link_margin'] ) ) ? $form_settings['registration_link_margin'] : array();
																$registration_link_margin['left']   = ( isset( $registration_link_margin['left'] ) && is_numeric( $registration_link_margin['left'] ) ) ? $registration_link_margin['left'] : 0;
																$registration_link_margin['top']    = ( isset( $registration_link_margin['top'] ) && is_numeric( $registration_link_margin['top'] ) ) ? $registration_link_margin['top'] : 0;
																$registration_link_margin['right']  = ( isset( $registration_link_margin['right'] ) && is_numeric( $registration_link_margin['right'] ) ) ? $registration_link_margin['right'] : 0;
																$registration_link_margin['bottom'] = ( isset( $registration_link_margin['bottom'] ) && is_numeric( $registration_link_margin['bottom'] ) ) ? $registration_link_margin['bottom'] : 0;
															?>
																<div class="arm_registration_link_margin_inputs_container">
																	<div class="arm_registration_link_margin_inputs">
																		<input type="text" name="arm_form_settings[registration_link_margin][left]" id="arm_registration_link_margin_left" class="arm_registration_link_margin_left" value="<?php echo floatval($registration_link_margin['left']); ?>"/>
																		<br /><?php esc_html_e( 'Left', 'armember-membership' ); ?>
																	</div>
																	<div class="arm_registration_link_margin_inputs">
																		<input type="text" name="arm_form_settings[registration_link_margin][top]" id="arm_registration_link_margin_top" class="arm_registration_link_margin_top" value="<?php echo floatval($registration_link_margin['top']); ?>"/>
																		<br /><?php esc_html_e( 'Top', 'armember-membership' ); ?>
																	</div>
																	<div class="arm_registration_link_margin_inputs">
																		<input type="text" name="arm_form_settings[registration_link_margin][right]" id="arm_registration_link_margin_right" class="arm_registration_link_margin_right" value="<?php echo floatval($registration_link_margin['right']); ?>"/>
																		<br /><?php esc_html_e( 'Right', 'armember-membership' ); ?>
																	</div>
																	<div class="arm_registration_link_margin_inputs">
																		<input type="text" name="arm_form_settings[registration_link_margin][bottom]" id="arm_registration_link_margin_bottom" class="arm_registration_link_margin_bottom" value="<?php echo floatval($registration_link_margin['bottom']); ?>"/>
																		<br /><?php esc_html_e( 'Bottom', 'armember-membership' ); ?>
																	</div>
																</div>
																<div class="armclear"></div>

															</td>
															
														</tr>
														<tr class="arm_forgot_password_link_options <?php echo ( $show_forgot_password_link != '1' ) ? 'hidden_section' : ''; ?>">
															<td colspan="2"><?php esc_html_e( 'Forgot Password Link Margin', 'armember-membership' ); ?>
																
																	 <?php
																		$forgot_password_link_margin           = ( isset( $form_settings['forgot_password_link_margin'] ) ) ? $form_settings['forgot_password_link_margin'] : array();
																		$forgot_password_link_margin['left']   = ( isset( $forgot_password_link_margin['left'] ) && is_numeric( $forgot_password_link_margin['left'] ) ) ? $forgot_password_link_margin['left'] : 0;
																		$forgot_password_link_margin['top']    = ( isset( $forgot_password_link_margin['top'] ) && is_numeric( $forgot_password_link_margin['top'] ) ) ? $forgot_password_link_margin['top'] : 0;
																		$forgot_password_link_margin['right']  = ( isset( $forgot_password_link_margin['right'] ) && is_numeric( $forgot_password_link_margin['right'] ) ) ? $forgot_password_link_margin['right'] : 0;
																		$forgot_password_link_margin['bottom'] = ( isset( $forgot_password_link_margin['bottom'] ) && is_numeric( $forgot_password_link_margin['bottom'] ) ) ? $forgot_password_link_margin['bottom'] : 0;
																		?>
																<div class="arm_forgot_password_link_margin_inputs_container">
																	<div class="arm_forgot_password_link_margin_inputs">
																		<input type="text" name="arm_form_settings[forgot_password_link_margin][left]" id="arm_forgot_password_link_margin_left" class="arm_forgot_password_link_margin_left" value="<?php echo floatval($forgot_password_link_margin['left']); ?>"/>
																		<br /><?php esc_html_e( 'Left', 'armember-membership' ); ?>
																	</div>
																	<div class="arm_forgot_password_link_margin_inputs">
																		<input type="text" name="arm_form_settings[forgot_password_link_margin][top]" id="arm_forgot_password_link_margin_top" class="arm_forgot_password_link_margin_top" value="<?php echo floatval($forgot_password_link_margin['top']); ?>"/>
																		<br /><?php esc_html_e( 'Top', 'armember-membership' ); ?>
																	</div>
																	<div class="arm_forgot_password_link_margin_inputs">
																		<input type="text" name="arm_form_settings[forgot_password_link_margin][right]" id="arm_forgot_password_link_margin_right" class="arm_forgot_password_link_margin_right" value="<?php echo floatval($forgot_password_link_margin['right']); ?>"/>
																		<br /><?php esc_html_e( 'Right', 'armember-membership' ); ?>
																	</div>
																	<div class="arm_forgot_password_link_margin_inputs">
																		<input type="text" name="arm_form_settings[forgot_password_link_margin][bottom]" id="arm_forgot_password_link_margin_bottom" class="arm_forgot_password_link_margin_bottom" value="<?php echo floatval($forgot_password_link_margin['bottom']); ?>"/>
																		<br /><?php esc_html_e( 'Bottom', 'armember-membership' ); ?>
																	</div>
																</div>
																<div class="armclear"></div>

															</td>
															
														</tr>
										<?php }
										}
										 ?>
										<?php if ( $isRegister ) { ?>
											<?php $auto_login = ( isset( $form_settings['auto_login'] ) ) ? $form_settings['auto_login'] : 0; ?>
											<tr>
												<td colspan="2">
													<label class="arm_form_opt_label" for="arm_auto_login_btn"><?php esc_html_e( 'Automatic login on signup', 'armember-membership' ); ?></label>
													<div class="armswitch arm_global_setting_switch arm_vertical_align_middle">
														<input type="checkbox" id="arm_auto_login_btn" <?php checked( $auto_login, '1' ); ?> value="1" class="armswitch_input" name="arm_form_settings[auto_login]"/>
														<label for="arm_auto_login_btn" class="armswitch_label"></label>
													</div>
												</td>
											</tr>
											<tr>
												<td colspan="2">
													<a href="<?php echo admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=redirection_options' ); //phpcs:ignore ?>" class="arm_configure_submission_redirection_link armemailaddbtn" target="_balnk" ><?php esc_html_e( 'Configure Submission Redirection', 'armember-membership' ); ?></a>
												</td>
											</tr>
										<?php } 
										
										$arm_edit_profile_link_option_section = '';
										echo apply_filters('arm_edit_profile_section',$arm_edit_profile_link_option_section,$isEditProfile,$form_settings); //phpcs:ignore
										?>
									</table>
									<div class="armclear"></div>
								</div>
								<?php
								$arm_right_section_handling_additional_fields = '';
								echo apply_filters('arm_right_section_handling_additional_fields',$arm_right_section_handling_additional_fields,$form_settings,$isRegister,$isEditProfile,$activeSocialNetworks); //phpcs:ignore
								
								if ( ! $isRegister && !$isEditProfile) { ?>
								
									<?php
									$changePassMsg    = isset( $otherFormIDs['change_password']['arm_form_settings']['message'] ) ? stripslashes( $otherFormIDs['change_password']['arm_form_settings']['message'] ) : esc_html__( 'Your password has been changed successfully.', 'armember-membership' );
									$forgotgePassMsg  = isset( $otherFormIDs['forgot_password']['arm_form_settings']['message'] ) ? stripslashes( $otherFormIDs['forgot_password']['arm_form_settings']['message'] ) : esc_html__( 'We have sent you password reset link, Please check your mail.', 'armember-membership' );
									$forgotgePassDesc = isset( $otherFormIDs['forgot_password']['arm_form_settings']['description'] ) ? stripslashes( $otherFormIDs['forgot_password']['arm_form_settings']['description'] ) : '';
									$arm_google_recaptcha_v3_section = '';
									echo apply_filters('arm_google_recaptcha_v3_section',$arm_google_recaptcha_v3_section,$all_global_settings,$form_settings); //phpcs:ignore
									?>

									

									<div class="arm_right_section_heading"><?php esc_html_e( 'Messages', 'armember-membership' ); ?></div>
									
									<div class="arm_right_section_body arm_form_redirection_options arm_padding_bottom_15">
										<table class="arm_form_settings_style_block">
											<tr>
												<td colspan="2">
													<span class="arm_form_opt_label"><?php esc_html_e( 'Forgot Password Messages', 'armember-membership' ); ?>:</span>
												</td>
											</tr>
											<tr>
												<td colspan="2">
													<div class="arm_form_opt_input">
														<label class="arm_form_opt_label"><?php esc_html_e( 'Form Description', 'armember-membership' ); ?>:</label>

														<?php /*<input type="text" name="arm_form_settings[forgot_password][description]" style="max-width:auto" value="<?php echo addslashes($forgotgePassDesc); ?>" id="arm_forgot_password_description_input" class="arm_forgot_password_description_input form_submit_action_input">*/ ?>
														<textarea name="arm_form_settings[forgot_password][description]" id="arm_forgot_password_description_input" class="arm_forgot_password_description_input form_submit_action_input" rows="2" cols="35" style="min-height:100px"><?php echo esc_html($forgotgePassDesc); ?></textarea>
													</div>
												</td>
											</tr>
											<tr>
												<td colspan="2">
													<div class="arm_form_opt_input">
														<label class="arm_form_opt_label"><?php esc_html_e( 'Display message after form submit', 'armember-membership' ); ?>:</label>
														<input type="text" name="arm_form_settings[forgot_password][message]" value="<?php echo esc_attr($forgotgePassMsg); ?>" id="form_submit_action_message_fp" class="form_submit_action_input">
													</div>
												</td>
											</tr>
											<tr>
												<td colspan="2">
													<span class="arm_form_opt_label"><?php esc_html_e( 'Change Password Messages', 'armember-membership' ); ?>:</span>
												</td>
											</tr>
											<tr>
												<td colspan="2">
													<div class="arm_form_opt_input">
														<label class="arm_form_opt_label"><?php esc_html_e( 'Display message after form submit', 'armember-membership' ); ?>:</label>
														<input type="text" name="arm_form_settings[change_password][message]" value="<?php echo esc_attr($changePassMsg); ?>" id="form_submit_action_message_cp" class="form_submit_action_input">
													</div>
												</td>
											</tr>
										</table>
									</div>
								<?php } if(!$ARMemberLite->is_arm_pro_active){?>
								<div class="arm_right_section_heading"><?php esc_html_e( 'Input Field Options', 'armember-membership' ); ?></div>
									<div class="arm_right_section_body arm_form_redirection_options">
									<table class="arm_form_settings_style_block">
									<tr>
															<td><?php esc_html_e( 'Text Direction', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_right">
																	<div class="arm_switch arm_form_rtl_switch">
																		<label data-value="0" class="arm_switch_label <?php echo ( $is_rtl == '0' ) ? 'active' : ''; ?>"><?php esc_html_e( 'LTR', 'armember-membership' ); ?></label>
																		<label data-value="1" class="arm_switch_label <?php echo ( $is_rtl == '1' ) ? 'active' : ''; ?>"><?php esc_html_e( 'RTL', 'armember-membership' ); ?></label>
																		<input type="hidden" class="arm_switch_radio arm_form_rtl_support_chk" name="arm_form_settings[style][rtl]" value="<?php echo esc_attr($is_rtl); ?>">
																	</div>
																</div>
															</td>
														</tr>
														</table>
									</div>


									<div class="arm_right_section_heading"><?php esc_html_e( 'Label Options', 'armember-membership' ); ?></div>
									<div class="arm_right_section_body arm_form_redirection_options">
									<table class="arm_form_settings_style_block">
														<tr class="arm_field_label_position_container">
															<td><?php esc_html_e( 'Position', 'armember-membership' ); ?></td>
															<td>
																<div class="arm_right">
																	<?php
																	$form_settings['style']['label_position'] = ( ! empty( $form_settings['style']['label_position'] ) ) ? $form_settings['style']['label_position'] : 'inline';
																	?>
																	<div class="arm_switch arm_switch3 arm_label_position_switch">
																		<label data-value="block" class="arm_switch_label <?php echo ( $form_settings['style']['label_position'] == 'block' ) ? 'active' : ''; ?> <?php echo ( $formLayout == 'writer_border' ) ? 'disable_section' : ''; ?>"><?php esc_html_e( 'Top', 'armember-membership' ); ?></label>
																		<label data-value="inline" class="arm_switch_label <?php echo ( $form_settings['style']['label_position'] == 'inline' ) ? 'active' : ''; ?> <?php echo ( $formLayout == 'writer_border' ) ? 'disable_section' : ''; ?>"><?php esc_html_e( 'Left', 'armember-membership' ); ?></label>
																		<label data-value="inline_right" class="arm_switch_label <?php echo ( $form_settings['style']['label_position'] == 'inline_right' ) ? 'active' : ''; ?> <?php echo ( $formLayout == 'writer_border' ) ? 'disable_section' : ''; ?>"><?php esc_html_e( 'Right', 'armember-membership' ); ?></label>
																		<input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][label_position]" value="<?php echo esc_attr($form_settings['style']['label_position']); ?>">
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Align', 'armember-membership' ); ?></td>
															<td>
																<div class="arm_right">

																	<div class="arm_switch arm_label_align_switch ">
																		<label data-value="left" class="arm_switch_label <?php echo ( $form_settings['style']['label_align'] == 'left' ) ? 'active' : ''; ?> <?php echo ( $formLayout == 'writer_border' ) ? 'disable_section' : ''; ?>"><?php esc_html_e( 'Left', 'armember-membership' ); ?></label>
																		<label data-value="right" class="arm_switch_label <?php echo ( $form_settings['style']['label_align'] == 'right' ) ? 'active' : ''; ?> <?php echo ( $formLayout == 'writer_border' ) ? 'disable_section' : ''; ?>"><?php esc_html_e( 'Right', 'armember-membership' ); ?></label>
																		<input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][label_align]" value="<?php echo esc_attr($form_settings['style']['label_align']); ?>">
																	</div>
																</div>
															</td>
														</tr>
														</table>
									</div>
								<?php }?>
								<div class="armclear"></div>
								<?php $displayMapFields = false; 
								$arm_member_forms_editor_basic_option_additional_fields = '';
								echo apply_filters('arm_member_forms_editor_basic_option_additional_fields',$arm_member_forms_editor_basic_option_additional_fields,$form_settings,$isRegister,$arm_form_fields_for_cl); //phpcs:ignore
								?>
							  
							</div>
							
							<?php
							if($ARMemberLite->is_arm_pro_active)
							{
								$arm_member_forms_editor_other_tab_options_settings = '';
								echo apply_filters('arm_member_forms_editor_other_tab_options_settings',$arm_member_forms_editor_other_tab_options_settings); //phpcs:ignore
							}
							else
							{?>
								<div id="tabsetting-2" class="arm-tab-content">
								<div class="arm_form_setting_options_head style_setting_main_heading"><?php esc_html_e( 'Style Settings', 'armember-membership' ); ?></div>
								<div id="arm_form_styles_fields_container" class="arm_form_styles_fields_container" data-form_id="<?php echo intval($form_id); ?>">
									<div id="arm_accordion">
										<ul>
											<li class="arm_active_section">
												<a href="javascript:void(0)" class="arm_accordion_header"><?php esc_html_e( 'Form Options', 'armember-membership' ); ?>:<i></i></a>
												<div id="one" class="arm_accordion default">
													<table class="arm_form_settings_style_block arm_tbl_label_left_input_right">
														<tr>
															<td><?php esc_html_e( 'Form Style', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_right">
																	<input type="hidden" id="arm_manage_form_layout1" name="arm_form_settings[style][form_layout]" class="arm_manage_form_layout armMappedTextbox" data-id="arm_manage_form_layout" value="<?php echo esc_attr($formLayout); ?>" data-old_value="<?php echo esc_attr($formLayout); ?>"/>
																	<dl class="arm_selectbox column_level_dd arm_width_160">
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_manage_form_layout1">
																				<li data-label="<?php esc_attr_e( 'Material Outline', 'armember-membership' ); ?>" data-value="writer_border"><?php esc_html_e( 'Material Outline', 'armember-membership' ); ?></li>
																				<li data-label="<?php esc_attr_e( 'Material Style', 'armember-membership' ); ?>" data-value="writer"><?php esc_html_e( 'Material Style', 'armember-membership' ); ?></li>
																				<li data-label="<?php esc_attr_e( 'Standard Style', 'armember-membership' ); ?>" data-value="iconic"><?php esc_html_e( 'Standard Style', 'armember-membership' ); ?></li>
																				<li data-label="<?php esc_attr_e( 'Rounded Style', 'armember-membership' ); ?>" data-value="rounded"><?php esc_html_e( 'Rounded Style', 'armember-membership' ); ?></li>
																			</ul>
																		</dd>
																	</dl>
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Form Width', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_right">
																	<input type="text" id="arm_form_width" name="arm_form_settings[style][form_width]" class="arm_form_width arm_form_setting_input armMappedTextbox arm_width_130" data-id="arm_form_width1" value="<?php echo ! empty( $form_settings['style']['form_width'] ) ? esc_attr($form_settings['style']['form_width']) : '600'; ?>" onkeydown="javascript:return checkNumber(event)"/><span>&nbsp;(px)</span>
																	<input type='hidden' id="arm_form_width_type" name="arm_form_settings[style][form_width_type]" class="arm_form_width_type" value="px" />
																</div>
															</td>
														</tr>
														<tr>
															<td class="arm_form_editor_field_label"><?php esc_html_e( 'Border', 'armember-membership' ); ?></td>
															<td>
																<div class="arm_right">
																	<input type='text' id="arm_form_border_width" name="arm_form_settings[style][form_border_width]" class="arm_form_width arm_form_setting_input arm_width_80" value="<?php echo isset( $form_settings['style']['form_border_width'] ) ? esc_attr($form_settings['style']['form_border_width']) : '0'; ?>" onkeydown="javascript:return checkNumber(event)" />

																	<br />Width (px)
																</div>
															</td>
															<td>
																<div class="arm_right">
																	<input type='text' id="arm_form_border_radius" name="arm_form_settings[style][form_border_radius]" class="arm_form_width arm_form_setting_input arm_width_80" value="<?php echo isset( $form_settings['style']['form_border_radius'] ) ? esc_attr($form_settings['style']['form_border_radius']) : '8'; ?>" onkeydown="javascript:return checkNumber(event)" />

																	<br />Radius (px)
																</div>
															</td>
														</tr>
														<tr>
															<td></td>
															<td colspan="2">
																<div class="arm_right">
																	<input type='hidden' id="arm_form_border_style" name="arm_form_settings[style][form_border_style]" class="arm_form_border_style" value="<?php echo ! empty( $form_settings['style']['form_border_style'] ) ? esc_attr($form_settings['style']['form_border_style']) : 'solid'; ?>" />
																	<dl class="arm_selectbox column_level_dd arm_width_150">
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_form_border_style">
																				<li data-label="Solid" data-value="solid">Solid</li>
																				<li data-label="Dashed" data-value="dashed">Dashed</li>
																				<li data-label="Dotted" data-value="dotted">Dotted</li>
																			</ul>
																		</dd>
																	</dl>
																	<br />Style
																</div>
															</td>                                            
														</tr>
														<tr>
															<td class="arm_form_editor_field_label"><?php esc_html_e( 'Form Padding', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_button_margin_inputs_container arm_right">
																	<?php
																	$form_settings['style']['form_padding_left']   = ( is_numeric( $form_settings['style']['form_padding_left'] ) ) ? $form_settings['style']['form_padding_left'] : 20;
																	$form_settings['style']['form_padding_top']    = ( is_numeric( $form_settings['style']['form_padding_top'] ) ) ? $form_settings['style']['form_padding_top'] : 20;
																	$form_settings['style']['form_padding_right']  = ( is_numeric( $form_settings['style']['form_padding_right'] ) ) ? $form_settings['style']['form_padding_right'] : 20;
																	$form_settings['style']['form_padding_bottom'] = ( is_numeric( $form_settings['style']['form_padding_bottom'] ) ) ? $form_settings['style']['form_padding_bottom'] : 20;
																	?>
																	<div class="arm_button_margin_inputs">
																		<input type="text" name="arm_form_settings[style][form_padding_left]" id="arm_form_padding_left" class="arm_form_padding_left" value="<?php echo esc_attr($form_settings['style']['form_padding_left']); ?>"/>
																		<br /><?php esc_html_e( 'Left', 'armember-membership' ); ?>
																	</div>
																	<div class="arm_button_margin_inputs">
																		<input type="text" name="arm_form_settings[style][form_padding_top]" id="arm_form_padding_top" class="arm_form_padding_top" value="<?php echo esc_attr($form_settings['style']['form_padding_top']); ?>"/>
																		<br /><?php esc_html_e( 'Top', 'armember-membership' ); ?>
																	</div>
																	<div class="arm_button_margin_inputs">
																		<input type="text" name="arm_form_settings[style][form_padding_right]" id="arm_form_padding_right" class="arm_form_padding_right" value="<?php echo esc_attr($form_settings['style']['form_padding_right']); ?>"/>
																		<br /><?php esc_html_e( 'Right', 'armember-membership' ); ?>
																	</div>
																	<div class="arm_button_margin_inputs">
																		<input type="text" name="arm_form_settings[style][form_padding_bottom]" id="arm_form_padding_bottom" class="arm_form_padding_bottom" value="<?php echo esc_attr($form_settings['style']['form_padding_bottom']); ?>"/>
																		<br /><?php esc_html_e( 'Bottom', 'armember-membership' ); ?>
																	</div>
																</div>
															</td>
														</tr>

														<tr>
															<td class="arm_vertical_align_top"><?php esc_html_e( 'Background', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_right">
																	<?php
																	$isFormBGImg = ! empty( $form_settings['style']['form_bg'] ) && file_exists( MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename( $form_settings['style']['form_bg'] ) ) ? true : false;
																	$form_settings['style']['form_bg'] = ( $isFormBGImg ) ? $form_settings['style']['form_bg'] : '';
																	?>
																	<div class="arm_form_bg_upload_wrapper">
																		<div class="armFileUploadWrapper">
																			<div class="armFileUploadContainer" style="<?php echo ( $isFormBGImg ) ? 'display: none;' : ''; ?>">
																				<div class="armFileUpload-icon"></div><?php esc_html_e( 'Upload', 'armember-membership' ); ?>
																				<input id="armFormBGFileUpload" class="armFileUpload armFormBGFileUpload armIgnore" name="arm_form_settings[style][form_bg_file]" type="file" value="" accept=".jpg,.jpeg,.png,.gif,.bmp" data-file_size="5"/>
																			</div>
																			<div class="armFileRemoveContainer" style="<?php echo ( $isFormBGImg ) ? 'display: inline-block;' : ''; ?>"><div class="armFileRemove-icon"></div><?php esc_html_e( 'Remove', 'armember-membership' ); ?></div>
																			<div class="armUploadedFileName" id="armFormBGUploadedFileName"></div>
																			<div class="armFileMessages" id="armFileUploadMsg"></div>
																			<input class="arm_file_url" type="hidden" name="arm_form_settings[style][form_bg]" value="<?php echo esc_attr($form_settings['style']['form_bg']); ?>">
																			<div class="arm_image_file_preview">
																			<?php
																			if ( $isFormBGImg ) {
																				echo '<img alt="" src="' . esc_attr($form_settings['style']['form_bg']) . '"/>'; //phpcs:ignore
																			}
																			?>
																				</div>
																		</div>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Opacity', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_right">
																	<input type='hidden' id="arm_form_opacity" name="arm_form_settings[style][form_opacity]" class="arm_form_opacity" value="<?php echo ! empty( $form_settings['style']['form_opacity'] ) ? esc_attr($form_settings['style']['form_opacity']) : '1'; ?>" />
																	<dl class="arm_selectbox column_level_dd arm_width_80 arm_min_width_50">
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_form_opacity">
																				<li data-label="1.0" data-value="1">1.0</li>
																				<li data-label="0.9" data-value="0.9">0.9</li>
																				<li data-label="0.8" data-value="0.8">0.8</li>
																				<li data-label="0.7" data-value="0.7">0.7</li>
																				<li data-label="0.6" data-value="0.6">0.6</li>
																				<li data-label="0.5" data-value="0.5">0.5</li>
																				<li data-label="0.4" data-value="0.4">0.4</li>
																				<li data-label="0.3" data-value="0.3">0.3</li>
																				<li data-label="0.2" data-value="0.2">0.2</li>
																				<li data-label="0.1" data-value="0.1">0.1</li>
																			</ul>
																		</dd>
																	</dl>
																</div>
															</td>
														</tr>
														<tr>
															<td colspan="2" class="font_settings_label"><?php esc_html_e( 'Form Title Settings', 'armember-membership' ); ?></td>
															<td></td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Hide Title', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_right">
																	<div class="armswitch arm_global_setting_switch arm_vertical_align_middle">
																		<input type="checkbox" id="arm_hide_form_title" <?php checked( $form_settings['hide_title'], '1' ); ?> value="1" class="armswitch_input armIgnore" name="arm_form_settings[hide_title]"/>
																		<label for="arm_hide_form_title" class="armswitch_label"></label>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Font Family', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_right">
																	<input type='hidden' id="arm_form_title_font_family" name="arm_form_settings[style][form_title_font_family]" class="arm_form_title_font_family" value="<?php echo ! empty( $form_settings['style']['form_title_font_family'] ) ? esc_attr($form_settings['style']['form_title_font_family']) : 'Helvetica'; ?>" />
																	<dl class="arm_selectbox column_level_dd arm_width_150">
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_form_title_font_family">
																				<?php echo $arm_member_forms->arm_fonts_list(); //phpcs:ignore ?>
																			</ul>
																		</dd>
																	</dl>
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Font Size', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_right">
																	<input type='hidden' id="arm_form_title_font_size" name="arm_form_settings[style][form_title_font_size]" class="arm_form_title_font_size" value="<?php echo isset( $form_settings['style']['form_title_font_size'] ) ? intval($form_settings['style']['form_title_font_size']) : '26'; ?>" />
																	<dl class="arm_selectbox column_level_dd arm_width_120">
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_form_title_font_size">
																				<?php
																				for ( $i = 8; $i < 41; $i++ ) {
																					?>
																					<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																											   <?php
																				}
																				?>
																			</ul>
																		</dd>
																	</dl>
																	<span>(px)</span>
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Font Style', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_right">
																	<div class="arm_font_style_options">
																		<!--/. Font Bold Option ./-->
																		<label class="arm_font_style_label <?php echo ( $form_settings['style']['form_title_font_bold'] == '1' ) ? 'arm_style_active' : ''; ?>" data-value="bold" data-field="arm_form_title_font_bold"><i class="armfa armfa-bold"></i></label>
																		<input type="hidden" name="arm_form_settings[style][form_title_font_bold]" id="arm_form_title_font_bold" class="arm_form_title_font_bold" value="<?php echo esc_attr($form_settings['style']['form_title_font_bold']); ?>" />
																		<!--/. Font Italic Option ./-->
																		<label class="arm_font_style_label <?php echo ( $form_settings['style']['form_title_font_italic'] == '1' ) ? 'arm_style_active' : ''; ?>" data-value="italic" data-field="arm_form_title_font_italic"><i class="armfa armfa-italic"></i></label>
																		<input type="hidden" name="arm_form_settings[style][form_title_font_italic]" id="arm_form_title_font_italic" class="arm_form_title_font_italic" value="<?php echo esc_attr($form_settings['style']['form_title_font_italic']); ?>" />
																		<!--/. Text Decoration Options ./-->
																		<label class="arm_font_style_label arm_decoration_label <?php echo ( $form_settings['style']['form_title_font_decoration'] == 'underline' ) ? 'arm_style_active' : ''; ?>" data-value="underline" data-field="arm_form_title_font_decoration"><i class="armfa armfa-underline"></i></label>
																		<label class="arm_font_style_label arm_decoration_label <?php echo ( $form_settings['style']['form_title_font_decoration'] == 'line-through' ) ? 'arm_style_active' : ''; ?>" data-value="line-through" data-field="arm_form_title_font_decoration"><i class="armfa armfa-strikethrough"></i></label>
																		<input type="hidden" name="arm_form_settings[style][form_title_font_decoration]" id="arm_form_title_font_decoration" class="arm_form_title_font_decoration" value="<?php echo esc_attr($form_settings['style']['form_title_font_decoration']); ?>" />
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Title Position', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_right">
																	<?php $form_settings['style']['form_title_position'] = ( ! empty( $form_settings['style']['form_title_position'] ) ) ? $form_settings['style']['form_title_position'] : 'left'; ?>
																	<div class="arm_switch arm_switch3 arm_form_title_position_switch">
																		<label data-value="left" class="arm_switch_label <?php echo ( $form_settings['style']['form_title_position'] == 'left' ) ? 'active' : ''; ?>"><?php esc_html_e( 'Left', 'armember-membership' ); ?></label>
																		<label data-value="center" class="arm_switch_label <?php echo ( $form_settings['style']['form_title_position'] == 'center' ) ? 'active' : ''; ?>"><?php esc_html_e( 'Center', 'armember-membership' ); ?></label>
																		<label data-value="right" class="arm_switch_label <?php echo ( $form_settings['style']['form_title_position'] == 'right' ) ? 'active' : ''; ?>"><?php esc_html_e( 'Right', 'armember-membership' ); ?></label>
																		<input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][form_title_position]" value="<?php echo esc_attr($form_settings['style']['form_title_position']); ?>">
																	</div>
																</div>
															</td>
														</tr>
														<tr class="arm_validation_message_type_container <?php echo ( $formLayout == 'writer' || $formLayout == 'writer_border' ) ? 'hidden_section' : ''; ?>">
															<td colspan="3"><?php esc_html_e( 'Validation Message Position', 'armember-membership' ); ?></td>
														</tr>
														<tr class="arm_validation_message_position_container <?php echo ( $formLayout == 'writer' || $formLayout == 'writer_border' ) ? 'hidden_section' : ''; ?>">
															<td colspan="3">
																<?php $form_settings['style']['validation_position'] = ( ! empty( $form_settings['style']['validation_position'] ) ) ? $form_settings['style']['validation_position'] : 'bottom'; ?>
																<div class="arm_switch arm_switch4 arm_validation_position_switch">
																	<label data-value="top" class="arm_switch_label <?php echo ( $form_settings['style']['validation_position'] == 'top' ) ? 'active' : ''; ?>"><?php esc_html_e( 'Top', 'armember-membership' ); ?></label>
																	<label data-value="bottom" class="arm_switch_label <?php echo ( $form_settings['style']['validation_position'] == 'bottom' ) ? 'active' : ''; ?>"><?php esc_html_e( 'Bottom', 'armember-membership' ); ?></label>
																	<label data-value="left" class="arm_switch_label <?php echo ( $form_settings['style']['validation_position'] == 'left' ) ? 'active' : ''; ?>"><?php esc_html_e( 'Left', 'armember-membership' ); ?></label>
																	<label data-value="right" class="arm_switch_label <?php echo ( $form_settings['style']['validation_position'] == 'right' ) ? 'active' : ''; ?>"><?php esc_html_e( 'Right', 'armember-membership' ); ?></label>
																	<input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][validation_position]" value="<?php echo esc_attr($form_settings['style']['validation_position']); ?>">
																</div>
															</td>
														</tr>

														
													</table>
												</div>
											</li>
												
											<li>
												<a href="javascript:void(0)" class="arm_accordion_header"><?php esc_html_e( 'Input Field Options', 'armember-membership' ); ?>:<i></i></a>
												<div id="three" class="arm_accordion">
													<table class="arm_form_settings_style_block arm_tbl_label_left_input_right">
														<tr>
															<td><?php esc_html_e( 'Field Width', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_right">
																	<input type="text" id="arm_field_width" name="arm_form_settings[style][field_width]" class="arm_field_width arm_form_setting_input arm_width_140" value="<?php echo ! empty( $form_settings['style']['field_width'] ) ? esc_attr($form_settings['style']['field_width']) : '100'; ?>" onkeydown="javascript:return checkNumber(event)"/><span>&nbsp;(%)&nbsp;</span>
																	<input type='hidden' id="arm_field_width_type" name="arm_form_settings[style][field_width_type]" class="arm_field_width_type" value="%" />
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Field Height', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_right">
																	<input type="text" id="arm_field_height" name="arm_form_settings[style][field_height]" class="arm_field_height arm_form_setting_input arm_width_140" value="<?php echo isset( $form_settings['style']['field_height'] ) ? esc_attr($form_settings['style']['field_height']) : '33'; ?>"  onkeydown="javascript:return checkNumber(event)"/><span>&nbsp;(px)</span>
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Field Spacing', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_right">
																	<input type="text" id="arm_field_spacing" name="arm_form_settings[style][field_spacing]" class="arm_field_spacing arm_form_setting_input arm_width_140" value="<?php echo isset( $form_settings['style']['field_spacing'] ) ? esc_attr($form_settings['style']['field_spacing']) : '10'; ?>" onkeydown="javascript:return checkNumber(event)"/><span>&nbsp;(px)</span>
																</div>
															</td>
														</tr>
														<tr>
															<td class="arm_vertical_align_top"><?php esc_html_e( 'Border', 'armember-membership' ); ?></td>
															<td>
																<div class="arm_right">
																	<input type='text' id="arm_field_border_width" name="arm_form_settings[style][field_border_width]" class="arm_field_border_width arm_form_setting_input arm_width_80" value="<?php echo isset( $form_settings['style']['field_border_width'] ) ? intval($form_settings['style']['field_border_width']) : '1'; ?>" onkeydown="javascript:return checkNumber(event)" />
																	<br />Width (px)
																</div>
															</td>
															<td>
																<div class="arm_right">
																	<input type='text' id="arm_field_border_radius" name="arm_form_settings[style][field_border_radius]" class="arm_field_border_radius arm_form_setting_input arm_width_80" value="<?php echo isset( $form_settings['style']['field_border_radius'] ) ? intval($form_settings['style']['field_border_radius']) : '3'; ?>" onkeydown="javascript:return checkNumber(event)" <?php echo ( $formLayout == 'writer_border' || $formLayout == 'writer' ) ? 'readonly="readonly"' : ''; ?> />

																	<br />Radius (px)
																</div>
															</td>
														</tr>
														<tr>
															<td></td>
															<td colspan="2">
																<div class="arm_right">
																	<input type='hidden' id="arm_field_border_style" name="arm_form_settings[style][field_border_style]" class="arm_field_border_style" value="<?php echo ! empty( $form_settings['style']['field_border_style'] ) ? esc_attr($form_settings['style']['field_border_style']) : 'solid'; ?>" />
																	<dl class="arm_selectbox column_level_dd arm_width_140">
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_field_border_style">
																				<li data-label="Solid" data-value="solid">Solid</li>
																				<li data-label="Dashed" data-value="dashed">Dashed</li>
																				<li data-label="Dotted" data-value="dotted">Dotted</li>
																			</ul>
																		</dd>
																	</dl>
																	<br />Style
																</div>
															</td>                                            
														</tr>
														<tr>
															<td><?php esc_html_e( 'Field Alignment', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_right">
																	<?php $form_settings['style']['field_position'] = ( ! empty( $form_settings['style']['field_position'] ) ) ? $form_settings['style']['field_position'] : 'left'; ?>
																	<div class="arm_switch arm_switch3 arm_field_position_switch">
																		<label data-value="left" class="arm_switch_label <?php echo ( $form_settings['style']['field_position'] == 'left' ) ? 'active' : ''; ?>"><?php esc_html_e( 'Left', 'armember-membership' ); ?></label>
																		<label data-value="center" class="arm_switch_label <?php echo ( $form_settings['style']['field_position'] == 'center' ) ? 'active' : ''; ?>"><?php esc_html_e( 'Center', 'armember-membership' ); ?></label>
																		<label data-value="right" class="arm_switch_label <?php echo ( $form_settings['style']['field_position'] == 'right' ) ? 'active' : ''; ?>"><?php esc_html_e( 'Right', 'armember-membership' ); ?></label>
																		<input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][field_position]" value="<?php echo esc_attr($form_settings['style']['form_position']); ?>">
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td class="font_settings_label"><?php esc_html_e( 'Font Settings', 'armember-membership' ); ?></td>
															<td colspan="2"></td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Font Family', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_right">
																	<input type='hidden' id="arm_field_font_family" name="arm_form_settings[style][field_font_family]" class="arm_field_font_family" value="<?php echo ! empty( $form_settings['style']['field_font_family'] ) ? esc_attr($form_settings['style']['field_font_family']) : 'Helvetica'; ?>" />
																	<dl class="arm_selectbox column_level_dd arm_width_150">
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_field_font_family">
																				<?php echo $arm_member_forms->arm_fonts_list(); //phpcs:ignore ?>
																			</ul>
																		</dd>
																	</dl>
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Font Size', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_right">
																	<input type='hidden' id="arm_field_font_size" name="arm_form_settings[style][field_font_size]" class="arm_field_font_size" value="<?php echo isset( $form_settings['style']['field_font_size'] ) ? intval($form_settings['style']['field_font_size']) : '14'; ?>" />
																	<dl class="arm_selectbox column_level_dd arm_width_120">
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_field_font_size">
																				<?php
																				for ( $i = 8; $i < 41; $i++ ) {
																					?>
																					<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																											   <?php
																				}
																				?>
																			</ul>
																		</dd>
																	</dl>
																	<span>(px)</span>
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Font Style', 'armember-membership' ); ?></td>
															<td colspan="2">
																<div class="arm_right">
																	<div class="arm_font_style_options">
																		<!--/. Font Bold Option ./-->
																		<label class="arm_font_style_label <?php echo ( $form_settings['style']['field_font_bold'] == '1' ) ? 'arm_style_active' : ''; ?>" data-value="bold" data-field="arm_field_font_bold"><i class="armfa armfa-bold"></i></label>
																		<input type="hidden" name="arm_form_settings[style][field_font_bold]" id="arm_field_font_bold" class="arm_field_font_bold" value="<?php echo esc_attr($form_settings['style']['field_font_bold']); ?>" />
																		<!--/. Font Italic Option ./-->
																		<label class="arm_font_style_label <?php echo ( $form_settings['style']['field_font_italic'] == '1' ) ? 'arm_style_active' : ''; ?>" data-value="italic" data-field="arm_field_font_italic"><i class="armfa armfa-italic"></i></label>
																		<input type="hidden" name="arm_form_settings[style][field_font_italic]" id="arm_field_font_italic" class="arm_field_font_italic" value="<?php echo esc_attr($form_settings['style']['field_font_italic']); ?>" />
																		<!--/. Text Decoration Options ./-->
																		<label class="arm_font_style_label arm_decoration_label <?php echo ( $form_settings['style']['field_font_decoration'] == 'underline' ) ? 'arm_style_active' : ''; ?>" data-value="underline" data-field="arm_field_font_decoration"><i class="armfa armfa-underline"></i></label>
																		<label class="arm_font_style_label arm_decoration_label <?php echo ( $form_settings['style']['field_font_decoration'] == 'line-through' ) ? 'arm_style_active' : ''; ?>" data-value="line-through" data-field="arm_field_font_decoration"><i class="armfa armfa-strikethrough"></i></label>
																		<input type="hidden" name="arm_form_settings[style][field_font_decoration]" id="arm_field_font_decoration" class="arm_field_font_decoration" value="<?php echo esc_attr($form_settings['style']['field_font_decoration']); ?>" />
																	</div>
																</div>
															</td>
														</tr>
														
														<?php if ( $isRegister ) { ?>
															<tr>
																<td class="font_settings_label"><?php esc_html_e( 'Calendar Style', 'armember-membership' ); ?></td>
																<td colspan="2"></td>
															</tr>
															<tr>
																<td><?php esc_html_e( 'Date Format', 'armember-membership' ); ?></td>
																<td colspan="2">
																	<div class="arm_right">
																		<?php
																		$dateFormatOpts = array( 'd/m/Y', 'm/d/Y', 'Y/m/d', 'M d, Y', 'F d, Y' );
																		$wp_format_date = get_option( 'date_format' );
																		if ( $wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y' ) {
																			$dateFormatOpts = array( 'm/d/Y', 'M d, Y', 'F d, Y' );
																			if ( in_array( $formDateFormat, array( 'm/d/Y', 'd/m/Y', 'Y/m/d' ) ) ) {
																				$formDateFormat = 'm/d/Y';
																			} elseif ( in_array( $formDateFormat, array( 'M d, Y', 'd M, Y', 'Y, M d' ) ) ) {
																				$formDateFormat = 'M d, Y';
																			} elseif ( in_array( $formDateFormat, array( 'F d, Y', 'd F, Y', 'Y, F d' ) ) ) {
																				$formDateFormat = 'F d, Y';
																			}
																		} elseif ( $wp_format_date == 'd/m/Y' ) {
																			$dateFormatOpts = array( 'd/m/Y', 'd M, Y', 'd F, Y' );
																			if ( in_array( $formDateFormat, array( 'm/d/Y', 'd/m/Y', 'Y/m/d' ) ) ) {
																				$formDateFormat = 'd/m/Y';
																			} elseif ( in_array( $formDateFormat, array( 'M d, Y', 'd M, Y', 'Y, M d' ) ) ) {
																				$formDateFormat = 'd M, Y';
																			} elseif ( in_array( $formDateFormat, array( 'F d, Y', 'd F, Y', 'Y, F d' ) ) ) {
																				$formDateFormat = 'd F, Y';
																			}
																		} elseif ( $wp_format_date == 'Y/m/d' ) {
																			$dateFormatOpts = array( 'Y/m/d', 'Y, M d', 'Y, F d' );
																			if ( in_array( $formDateFormat, array( 'm/d/Y', 'd/m/Y', 'Y/m/d' ) ) ) {
																				$formDateFormat = 'Y/m/d';
																			} elseif ( in_array( $formDateFormat, array( 'M d, Y', 'd M, Y', 'Y, M d' ) ) ) {
																				$formDateFormat = 'Y, M d';
																			} elseif ( in_array( $formDateFormat, array( 'F d, Y', 'd F, Y', 'Y, F d' ) ) ) {
																				$formDateFormat = 'Y, F d';
																			}
																		} else {
																			$dateFormatOpts = array( 'd/m/Y', 'm/d/Y', 'Y/m/d', 'M d, Y', 'F d, Y' );
																		}
																		$wp_default_dateFormatOpts = array( 'F d, Y', 'Y-m-d', 'm/d/Y', 'd/m/Y' );
																		$dateFormatOpts            = array_unique( array_merge( $dateFormatOpts, $wp_default_dateFormatOpts ) );
																		?>
																		<input type='hidden' id="arm_calendar_date_format" name="arm_form_settings[date_format]" class="arm_calendar_date_format armIgnore" value="<?php echo esc_html($formDateFormat); ?>" />
																		<dl class="arm_selectbox column_level_dd arm_width_150">
																			<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																			<dd>
																				<ul data-id="arm_calendar_date_format">
																				<?php
																				foreach ( $dateFormatOpts as $df ) {
																					echo '<li data-label="' . date( $df, current_time( 'timestamp' ) ) . '" data-value="' . $df . '">' . date( $df, current_time( 'timestamp' ) ) . '</li>'; //phpcs:ignore
																				}
																				?>
																					</ul>
																			</dd>
																		</dl>
																	</div>
																</td>
															</tr>
															<tr>
																<td><?php esc_html_e( 'Show Time', 'armember-membership' ); ?></td>
																<td colspan="2">
																	<div class="arm_right">
																		<div class="arm_switch arm_show_time_switch">
																			<label data-value="1" class="arm_switch_label <?php echo ( $showTimePicker == '1' ) ? 'active' : ''; ?>"><?php esc_html_e( 'Yes', 'armember-membership' ); ?></label>
																			<label data-value="0" class="arm_switch_label <?php echo ( $showTimePicker == '0' ) ? 'active' : ''; ?>"><?php esc_html_e( 'No', 'armember-membership' ); ?></label>
																			<input type="hidden" class="arm_switch_radio" name="arm_form_settings[show_time]" value="<?php echo intval($showTimePicker); ?>">
																		</div>
																	</div>
																</td>
															</tr>
															<tr>
																<td class="font_settings_label"><?php esc_html_e( 'Hidden Fields', 'armember-membership' ); ?></td>
																<td colspan="2"></td>
															</tr>
															<tr>
																<td colspan="3">
																	<label class="arm_form_opt_label" for="arm_enable_hidden_field"><?php esc_html_e( 'Enable Hidden Fields?', 'armember-membership' ); ?></label>
																	<div class="armswitch arm_global_setting_switch arm_vertical_align_middle">
																		<input type="checkbox" id="arm_enable_hidden_field" <?php checked( $form_settings['is_hidden_fields'], '1' ); ?> value="1" class="armswitch_input armIgnore" name="arm_form_settings[is_hidden_fields]"/>
																		<label for="arm_enable_hidden_field" class="armswitch_label"></label>
																	</div>
																</td>
															</tr>
															<tr class="arm_form_hidden_field_options <?php echo ( $form_settings['is_hidden_fields'] == 1 ) ? '' : 'hidden_section'; ?>">
																<td colspan="3">
																	<ol class="arm_form_hidden_field_wrapper">
																		<?php
																		$totalField = 1;
																		if ( ! isset( $form_settings['hidden_fields'] ) || empty( $form_settings['hidden_fields'] ) ) {
																			$form_settings['hidden_fields'][1] = array(
																				'title' => '',
																				'meta_key' => '', //phpcs:ignore
																				'value' => '',
																			);
																		}
																		if ( isset( $form_settings['hidden_fields'] ) && ! empty( $form_settings['hidden_fields'] ) ) {
																			foreach ( $form_settings['hidden_fields'] as $hkey => $hval ) {
																				?>
																				<li class="arm_form_hidden_field" id="arm_form_hidden_field<?php echo intval($totalField); ?>">
																					<a href="javascript:void(0)" class="arm_remove_hidden_field" data-index="<?php echo intval($totalField); ?>">x</a>
																					<input type="hidden" name="arm_form_settings[hidden_fields][<?php echo intval($totalField); ?>][meta_key]" class="armIgnore" value="<?php echo ( isset( $hval['meta_key'] ) ) ? esc_attr($hval['meta_key']) : ''; ?>">
																					<div class="armclear"></div>
																					<span><?php esc_html_e( 'Title', 'armember-membership' ); ?></span>
																					<input type="text" name="arm_form_settings[hidden_fields][<?php echo intval($totalField); ?>][title]" class="arm_form_setting_input armIgnore" value="<?php echo ( isset( $hval['title'] ) ) ? esc_attr($hval['title']) : ''; ?>">
																					<div class="armclear"></div>
																					<span><?php esc_html_e( 'Value', 'armember-membership' ); ?></span>
																					<input type="text" name="arm_form_settings[hidden_fields][<?php echo intval($totalField); ?>][value]" class="arm_form_setting_input armIgnore" value="<?php echo ( isset( $hval['value'] ) ) ? esc_attr($hval['value']) : ''; ?>">
																				</li>
																				<?php
																				$totalField++;
																			}
																		}
																		?>
																	</ol>
																	<div class="arm_add_form_hidden_field_wrapper">
																		<a class="arm_add_hidden_field_link" id="arm_add_hidden_field_link" href="javascript:void(0)" data-field_index="<?php echo intval($totalField); ?>">+ <?php esc_html_e( 'Add More', 'armember-membership' ); ?></a>
																	</div>
																</td>
															</tr>
														<?php } ?>
													</table>
												</div>
											</li>
											<li>
												<a href="javascript:void(0)" class="arm_accordion_header"><?php esc_html_e( 'Label Options', 'armember-membership' ); ?>:<i></i></a>
												<div id="four" class="arm_accordion">
													<table class="arm_form_settings_style_block arm_tbl_label_left_input_right">
														<tr>
															<td><?php esc_html_e( 'Label Width', 'armember-membership' ); ?></td>
															<td>
																<div class="arm_right">
																	<input type="text" id="arm_label_width" name="arm_form_settings[style][label_width]" class="arm_label_width arm_form_setting_input arm_width_140" value="<?php echo ! empty( $form_settings['style']['label_width'] ) ? intval($form_settings['style']['label_width']) : '150'; ?>" onkeydown="javascript:return checkNumber(event)"/>&nbsp;(px)
																	<input type='hidden' id="arm_label_width_type" name="arm_form_settings[style][label_width_type]" class="arm_label_width_type" value="px" />
																</div>
															</td>
														</tr>
														
														<tr class="arm_field_label_hide_container <?php echo ( $formLayout == 'writer' ) ? 'hidden_section' : ''; ?>">
															<td><?php esc_html_e( 'Hide Label', 'armember-membership' ); ?></td>
															<td>
																<div class="arm_right">
																	<?php $form_settings['style']['label_hide'] = ( ! empty( $form_settings['style']['label_hide'] ) ) ? $form_settings['style']['label_hide'] : '0'; ?>
																	<div class="arm_switch arm_label_hide_switch">
																		<label data-value="1" class="arm_switch_label <?php echo ( $form_settings['style']['label_hide'] == '1' ) ? 'active' : ''; ?>"><?php esc_html_e( 'Yes', 'armember-membership' ); ?></label>
																		<label data-value="0" class="arm_switch_label <?php echo ( $form_settings['style']['label_hide'] == '0' ) ? 'active' : ''; ?>"><?php esc_html_e( 'No', 'armember-membership' ); ?></label>
																		<input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][label_hide]" value="<?php echo esc_attr($form_settings['style']['label_hide']); ?>">
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td class="font_settings_label"><?php esc_html_e( 'Font Settings', 'armember-membership' ); ?></td>
															<td></td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Font Family', 'armember-membership' ); ?></td>
															<td>
																<div class="arm_right">
																	<input type='hidden' id="arm_label_font_family" name="arm_form_settings[style][label_font_family]" class="arm_label_font_family" value="<?php echo ! empty( $form_settings['style']['label_font_family'] ) ? esc_attr($form_settings['style']['label_font_family']) : 'Helvetica'; ?>" />
																	<dl class="arm_selectbox column_level_dd arm_width_150">
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_label_font_family">
																				<?php echo $arm_member_forms->arm_fonts_list(); //phpcs:ignore ?>
																			</ul>
																		</dd>
																	</dl>
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Font Size', 'armember-membership' ); ?></td>
															<td>
																<div class="arm_right">
																	<input type='hidden' id="arm_label_font_size" name="arm_form_settings[style][label_font_size]" class="arm_label_font_size" value="<?php echo ! empty( $form_settings['style']['label_font_size'] ) ? intval($form_settings['style']['label_font_size']) : '16'; ?>" />
																	<dl class="arm_selectbox column_level_dd arm_width_120">
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_label_font_size">
																				<?php
																				for ( $i = 8; $i < 41; $i++ ) {
																					?>
																					<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																											   <?php
																				}
																				?>
																			</ul>
																		</dd>
																	</dl>
																	<span>(px)</span>
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Desc. Font Size', 'armember-membership' ); ?></td>
															<td>
																<div class="arm_right">
																	<input type='hidden' id="arm_description_font_size" name="arm_form_settings[style][description_font_size]" class="arm_description_font_size" value="<?php echo ! empty( $form_settings['style']['description_font_size'] ) ? intval($form_settings['style']['description_font_size']) : '16'; ?>" />
																	<dl class="arm_selectbox column_level_dd arm_width_120">
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_description_font_size">
																				<?php
																				for ( $i = 8; $i < 41; $i++ ) {
																					?>
																					<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																											   <?php
																				}
																				?>
																			</ul>
																		</dd>
																	</dl>
																	<span>(px)</span>
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Font Style', 'armember-membership' ); ?></td>
															<td>
																<div class="arm_right">
																	<div class="arm_font_style_options">
																		<!--/. Font Bold Option ./-->
																		<label class="arm_font_style_label <?php echo ( $form_settings['style']['label_font_bold'] == '1' ) ? 'arm_style_active' : ''; ?>" data-value="bold" data-field="arm_label_font_bold"><i class="armfa armfa-bold"></i></label>
																		<input type="hidden" name="arm_form_settings[style][label_font_bold]" id="arm_label_font_bold" class="arm_label_font_bold" value="<?php echo esc_attr($form_settings['style']['label_font_bold']); ?>" />
																		<!--/. Font Italic Option ./-->
																		<label class="arm_font_style_label <?php echo ( $form_settings['style']['label_font_italic'] == '1' ) ? 'arm_style_active' : ''; ?>" data-value="italic" data-field="arm_label_font_italic"><i class="armfa armfa-italic"></i></label>
																		<input type="hidden" name="arm_form_settings[style][label_font_italic]" id="arm_label_font_italic" class="arm_label_font_italic" value="<?php echo esc_attr($form_settings['style']['label_font_italic']); ?>" />
																		<!--/. Text Decoration Options ./-->
																		<label class="arm_font_style_label arm_decoration_label <?php echo ( $form_settings['style']['label_font_decoration'] == 'underline' ) ? 'arm_style_active' : ''; ?>" data-value="underline" data-field="arm_label_font_decoration"><i class="armfa armfa-underline"></i></label>
																		<label class="arm_font_style_label arm_decoration_label <?php echo ( $form_settings['style']['label_font_decoration'] == 'line-through' ) ? 'arm_style_active' : ''; ?>" data-value="line-through" data-field="arm_label_font_decoration"><i class="armfa armfa-strikethrough"></i></label>
																		<input type="hidden" name="arm_form_settings[style][label_font_decoration]" id="arm_label_font_decoration" class="arm_label_font_decoration" value="<?php echo esc_attr($form_settings['style']['label_font_decoration']); ?>" />
																	</div>
																</div>
															</td>
														</tr>
													</table>
												</div>
											</li>
											<li>
												<a href="javascript:void(0)" class="arm_accordion_header"><?php esc_html_e( 'Submit Button Options', 'armember-membership' ); ?>:<i></i></a>
												<div id="five" class="arm_accordion">
													<table class="arm_form_settings_style_block arm_tbl_label_left_input_right">
														<tr>
															<td><?php esc_html_e( 'Width', 'armember-membership' ); ?></td>
															<td>
																<div class="arm_right">
																	<input type="text" id="arm_button_width" name="arm_form_settings[style][button_width]" class="arm_button_width arm_form_setting_input arm_width_140" value="<?php echo ! empty( $form_settings['style']['button_width'] ) ? intval($form_settings['style']['button_width']) : '150'; ?>" onkeydown="javascript:return checkNumber(event)"/><span>&nbsp;(px)</span>
																	<input type='hidden' id="arm_button_width_type" name="arm_form_settings[style][button_width_type]" class="arm_button_width_type" value="<?php echo ! empty( $form_settings['style']['button_width_type'] ) ? esc_attr($form_settings['style']['button_width_type']) : 'px'; ?>" />
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Height', 'armember-membership' ); ?></td>
															<td>
																<div class="arm_right">
																	<input type="text" id="arm_button_height" name="arm_form_settings[style][button_height]" class="arm_button_height arm_form_setting_input arm_width_140" value="<?php echo ! empty( $form_settings['style']['button_height'] ) ? intval($form_settings['style']['button_height']) : '35'; ?>" onkeydown="javascript:return checkNumber(event)"/><span>&nbsp;(px)</span>
																	<input type='hidden' id="arm_button_height_type" name="arm_form_settings[style][button_height_type]" class="arm_button_height_type" value="<?php echo ! empty( $form_settings['style']['button_height_type'] ) ? esc_attr($form_settings['style']['button_height_type']) : 'px'; ?>" />
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Border Radius', 'armember-membership' ); ?></td>
															<td>
																<div class="arm_right">
																	<input type="text" id="arm_button_border_radius" name="arm_form_settings[style][button_border_radius]" class="arm_button_border_radius arm_form_setting_input arm_width_140" value="<?php echo isset( $form_settings['style']['button_border_radius'] ) ? intval($form_settings['style']['button_border_radius']) : '4'; ?>" onkeydown="javascript:return checkNumber(event)"/><span>&nbsp;(px)</span>
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Button Style', 'armember-membership' ); ?></td>
															<td>
																<div class="arm_right">
																	<input type='hidden' id="arm_button_style" name="arm_form_settings[style][button_style]" class="arm_button_style" value="<?php echo ! empty( $form_settings['style']['button_style'] ) ? esc_attr($form_settings['style']['button_style']) : 'flat'; ?>" />
																	<dl class="arm_selectbox column_level_dd arm_width_150">
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_button_style">
																				<li data-value="flat" data-label="<?php esc_html_e( 'Flat', 'armember-membership' ); ?>"><?php esc_html_e( 'Flat', 'armember-membership' ); ?></li>
																				<li data-value="classic" data-label="<?php esc_html_e( 'Classic', 'armember-membership' ); ?>"><?php esc_html_e( 'Classic', 'armember-membership' ); ?></li>
																				<li data-value="border" data-label="<?php esc_html_e( 'Border', 'armember-membership' ); ?>"><?php esc_html_e( 'Border', 'armember-membership' ); ?></li>
																				<li data-value="reverse_border" data-label="<?php esc_html_e( 'Reverse Border', 'armember-membership' ); ?>"><?php esc_html_e( 'Reverse Border', 'armember-membership' ); ?></li>
																			</ul>
																		</dd>
																	</dl>
																</div>
															</td>
														</tr>
														<tr>
															<td class="font_settings_label"><?php esc_html_e( 'Font Settings', 'armember-membership' ); ?></td>
															<td></td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Font Family', 'armember-membership' ); ?></td>
															<td>
																<div class="arm_right">
																	<input type='hidden' id="arm_button_font_family" name="arm_form_settings[style][button_font_family]" class="arm_button_font_family" value="<?php echo ! empty( $form_settings['style']['button_font_family'] ) ? esc_attr($form_settings['style']['button_font_family']) : 'Helvetica'; ?>" />
																	<dl class="arm_selectbox column_level_dd arm_width_150">
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_button_font_family">
																				<?php echo $arm_member_forms->arm_fonts_list(); //phpcs:ignore ?>
																			</ul>
																		</dd>
																	</dl>
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Font Size', 'armember-membership' ); ?></td>
															<td>
																<div class="arm_right">
																	<input type='hidden' id="arm_button_font_size" name="arm_form_settings[style][button_font_size]" class="arm_button_font_size" value="<?php echo ! empty( $form_settings['style']['button_font_size'] ) ? intval($form_settings['style']['button_font_size']) : '16'; ?>" />
																	<dl class="arm_selectbox column_level_dd arm_width_130">
																		<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																		<dd>
																			<ul data-id="arm_button_font_size">
																				<?php
																				for ( $i = 8; $i < 41; $i++ ) {
																					?>
																					<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																											   <?php
																				}
																				?>
																			</ul>
																		</dd>
																	</dl>
																	<span>px</span>
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Font Style', 'armember-membership' ); ?></td>
															<td>
																<div class="arm_right">
																	<div class="arm_font_style_options">
																		<!--/. Font Bold Option ./-->
																		<label class="arm_font_style_label <?php echo ( $form_settings['style']['button_font_bold'] == '1' ) ? 'arm_style_active' : ''; ?>" data-value="bold" data-field="arm_button_font_bold"><i class="armfa armfa-bold"></i></label>
																		<input type="hidden" name="arm_form_settings[style][button_font_bold]" id="arm_button_font_bold" class="arm_button_font_bold" value="<?php echo esc_attr($form_settings['style']['button_font_bold']); ?>" />
																		<!--/. Font Italic Option ./-->
																		<label class="arm_font_style_label <?php echo ( $form_settings['style']['button_font_italic'] == '1' ) ? 'arm_style_active' : ''; ?>" data-value="italic" data-field="arm_button_font_italic"><i class="armfa armfa-italic"></i></label>
																		<input type="hidden" name="arm_form_settings[style][button_font_italic]" id="arm_button_font_italic" class="arm_button_font_italic" value="<?php echo esc_attr($form_settings['style']['button_font_italic']); ?>" />
																		<!--/. Text Decoration Options ./-->
																		<label class="arm_font_style_label arm_decoration_label <?php echo ( $form_settings['style']['button_font_decoration'] == 'underline' ) ? 'arm_style_active' : ''; ?>" data-value="underline" data-field="arm_button_font_decoration"><i class="armfa armfa-underline"></i></label>
																		<label class="arm_font_style_label arm_decoration_label <?php echo ( $form_settings['style']['button_font_decoration'] == 'line-through' ) ? 'arm_style_active' : ''; ?>" data-value="line-through" data-field="arm_button_font_decoration"><i class="armfa armfa-strikethrough"></i></label>
																		<input type="hidden" name="arm_form_settings[style][button_font_decoration]" id="arm_button_font_decoration" class="arm_button_font_decoration" value="<?php echo esc_attr($form_settings['style']['button_font_decoration']); ?>" />
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<?php esc_html_e( 'Margin', 'armember-membership' ); ?>
															</td>
															<td style="padding-right: 0;">
																<?php
																$form_settings['style']['button_margin_left']   = ( is_numeric( $form_settings['style']['button_margin_left'] ) ) ? $form_settings['style']['button_margin_left'] : 0;
																$form_settings['style']['button_margin_top']    = ( is_numeric( $form_settings['style']['button_margin_top'] ) ) ? $form_settings['style']['button_margin_top'] : 0;
																$form_settings['style']['button_margin_right']  = ( is_numeric( $form_settings['style']['button_margin_right'] ) ) ? $form_settings['style']['button_margin_right'] : 0;
																$form_settings['style']['button_margin_bottom'] = ( is_numeric( $form_settings['style']['button_margin_bottom'] ) ) ? $form_settings['style']['button_margin_bottom'] : 0;
																?>
																<div class="arm_button_margin_inputs_container">
																	<div class="arm_button_margin_inputs">
																		<input type="text" name="arm_form_settings[style][button_margin_left]" id="arm_button_margin_left" class="arm_button_margin_left" value="<?php echo esc_attr($form_settings['style']['button_margin_left']); ?>"/>
																		<br /><?php esc_html_e( 'Left', 'armember-membership' ); ?>
																	</div>
																	<div class="arm_button_margin_inputs">
																		<input type="text" name="arm_form_settings[style][button_margin_top]" id="arm_button_margin_top" class="arm_button_margin_top" value="<?php echo esc_attr($form_settings['style']['button_margin_top']); ?>"/>
																		<br /><?php esc_html_e( 'Top', 'armember-membership' ); ?>
																	</div>
																	<div class="arm_button_margin_inputs">
																		<input type="text" name="arm_form_settings[style][button_margin_right]" id="arm_button_margin_right" class="arm_button_margin_right" value="<?php echo esc_attr($form_settings['style']['button_margin_right']); ?>"/>
																		<br /><?php esc_html_e( 'Right', 'armember-membership' ); ?>
																	</div>
																	<div class="arm_button_margin_inputs">
																		<input type="text" name="arm_form_settings[style][button_margin_bottom]" id="arm_button_margin_bottom" class="arm_button_margin_bottom" value="<?php echo esc_attr($form_settings['style']['button_margin_bottom']); ?>"/>
																		<br /><?php esc_html_e( 'Bottom', 'armember-membership' ); ?>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td><?php esc_html_e( 'Button Position', 'armember-membership' ); ?></td>															<td>
																<div class="arm_right">
																	<?php $form_settings['style']['button_position'] = ( ! empty( $form_settings['style']['button_position'] ) ) ? $form_settings['style']['button_position'] : 'left'; ?>
																	<div class="arm_switch arm_switch3 arm_button_position_switch">
																		<label data-value="left" class="arm_switch_label <?php echo ( $form_settings['style']['button_position'] == 'left' ) ? 'active' : ''; ?>"><?php esc_html_e( 'Left', 'armember-membership' ); ?></label>
																		<label data-value="center" class="arm_switch_label <?php echo ( $form_settings['style']['button_position'] == 'center' ) ? 'active' : ''; ?>"><?php esc_html_e( 'Center', 'armember-membership' ); ?></label>
																		<label data-value="right" class="arm_switch_label <?php echo ( $form_settings['style']['button_position'] == 'right' ) ? 'active' : ''; ?>"><?php esc_html_e( 'Right', 'armember-membership' ); ?></label>
																		<input type="hidden" class="arm_switch_radio" name="arm_form_settings[style][button_position]" value="<?php echo esc_attr($form_settings['style']['button_position']); ?>">
																	</div>
																</div>
															</td>
														</tr>
													</table>
												</div>
											</li>
										  
										</ul>
									</div>
									<div class="armclear"></div>
								</div>
							</div>
							<?php }
							?>
						   
						</div>
					</div><!--./ END `.arm_editor_right_wrapper`-->
				</div><!--./ END `.arm_editor_right`-->
			</div>
            <?php 
            if(sanitize_text_field( $get_action) == 'edit_form'){
                $from_additional_content = '';
                if ($isRegister || $isEditProfile) {
                    echo apply_filters( 'arm_append_content_at_the_manage_forms_edit', $from_additional_content , $form_detail); //phpcs:ignore
                } else {
                    $otherForms = $arm_member_forms->arm_get_other_member_forms($setID);
                    
                    echo apply_filters( 'arm_append_content_at_the_manage_forms_edit', $from_additional_content,$otherForms); //phpcs:ignore
                }
            } ?>
		</form>
		<div class="armclear"></div>
	</div>
    <div class="arm_section_custom_css_detail_container"></div>
</div>
<div id="arm_fontawesome_modal" class="arm_manage_form_fa_icons_wrapper hidden_section">
	<div class="arm_manage_form_fa_icons_container arm_slider_box_container">
		<div class="arm_slider_box_arrow"></div>
		<div class="arm_slider_box_heading"><?php esc_html_e( 'Font Awesome Icons', 'armember-membership' ); ?></div>
		<div class="arm_slider_box_body">
			<?php
			if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_font_awesome.php' ) ) {
				include MEMBERSHIPLITE_VIEWS_DIR . '/arm_font_awesome.php';
			}
			?>
		</div>
	</div>
</div>
<?php
/**
 * Social Profile Fields Popup (Social Network List)
 */
$socialProfileFields = $arm_member_forms->arm_social_profile_field_types();
$activeSPF           = array( 'facebook', 'twitter', 'linkedin' );
if ( ! empty( $socialFieldsOptions ) ) {
	$activeSPF = isset( $socialFieldsOptions['arm_form_field_option']['options'] ) ? $socialFieldsOptions['arm_form_field_option']['options'] : array();
}
$activeSPF = ( ! empty( $activeSPF ) ) ? $activeSPF : array();
?>
<div class="popup_wrapper arm_social_profile_fields_popup_wrapper">
	<table cellspacing="0">
		<tr class="popup_wrapper_inner">	
			<td class="popup_close_btn arm_popup_close_btn arm_social_profile_fields_close_btn"></td>
			<td class="popup_header"><?php esc_html_e( 'Social Profile Fields', 'armember-membership' ); ?></td>
			<td class="popup_content_text">
				<div class="arm_social_profile_fields_list_wrapper">
					<?php if ( ! empty( $socialProfileFields ) ) { ?>
						<?php foreach ( $socialProfileFields as $spfKey => $spfLabel ) { ?>
							<div class="arm_social_profile_field_item">
								<input type="checkbox" class="arm_icheckbox arm_spf_active_checkbox" value="<?php echo esc_attr($spfKey); ?>" name="arm_social_fields[]" id="arm_spf_<?php echo esc_attr($spfKey); ?>_status" <?php echo ( in_array( $spfKey, $activeSPF ) ) ? 'checked="checked"' : ''; ?>>
								<label for="arm_spf_<?php echo esc_attr($spfKey); ?>_status"><?php echo esc_attr($spfLabel); ?></label>
							</div>
						<?php } ?>
					<?php } ?>
				</div>
			</td>
			<td class="popup_content_btn popup_footer">
				<div class="popup_content_btn_wrapper arm_social_profile_fields_btn_wrapper">
					<button class="arm_cancel_btn popup_close_btn arm_social_profile_fields_close_btn" type="button"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></button>
					<button class="arm_save_btn arm_add_edit_social_profile_fields" id="arm_add_edit_social_profile_fields" type="button"><?php esc_html_e( 'Add', 'armember-membership' ); ?></button>
				</div>
			</td>
		</tr>
	</table>
</div>
<div class="arm_section_custom_css_detail_container"></div>
<?php echo $form_styles; //phpcs:ignore ?>
<?php
/* Angular JS */
$ARMemberLite->enqueue_angular_script();
?>
<style type="text/css">#wpbody-content{padding:0;}html{background: #FFFFFF;}#adminmenuwrap{z-index: 9970;}</style>
<?php
$arm_form_css = $arm_member_forms->arm_ajax_generate_form_styles( $form_id, $form_settings, array(), $reference_template );
if ( isset( $arm_form_css['arm_link'] ) && ! empty( $arm_form_css['arm_link'] ) ) {
	echo $arm_form_css['arm_link']; //phpcs:ignore
} else {
	echo '<link id="google-font-' . intval($form_id) . '" rel="stylesheet" type="text/css" href="#" />'; //phpcs:ignore
}
/**
 * Add Social Network Popup
 */
if ((!$isRegister && !$isEditProfile)) {
    if ($ARMemberLite->is_arm_pro_active && $arm_social_feature->isSocialLoginFeature) {
        if (defined('MEMBERSHIP_VIEWS_DIR') && file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_form_editor_social_network_popup.php')) {
            include( MEMBERSHIP_VIEWS_DIR . '/arm_form_editor_social_network_popup.php');
        }
    }
}
?>
<style type="text/css" id="arm_form_runtime_style"><?php echo $arm_form_css['arm_css']; //phpcs:ignore ?></style>
<style type="text/css" id="arm_button_hover_color_style"></style>
<style type="text/css" id="arm_field_font_color_style"></style>
<style type="text/css" id="arm_field_focus_color_style"></style>
<style type="text/css" id="arm_field_border_color_style"></style>
<style type="text/css" id="arm_field_bg_color_style"></style>
<style type="text/css" id="arm_date_picker_color_style"></style>
<style type="text/css" id="arm_material_outline_label_bg_color_style"></style>
<script type="text/javascript">
	__ARM_TITLE = '<?php esc_html_e( 'Title', 'armember-membership' ); ?>';
	__ARM_METAKEY = '<?php esc_html_e( 'Meta Key', 'armember-membership' ); ?>';
	__ARM_VALUE = '<?php esc_html_e( 'Meta Value', 'armember-membership' ); ?>';
	function armColorSchemes() {
		var ColorSchemes = <?php echo wp_json_encode( $form_color_schemes ); ?>;
		return ColorSchemes;
	}
	function armButtonGradientScheme() {
		var GradientScheme = <?php echo wp_json_encode( $formButtonSchemes ); ?>;
		return GradientScheme;
	}
	jQuery(document).ready(function () {
		jQuery('.arm_loading_grid').fadeIn('slow');
	});
	jQuery(window).on("load", function () {
		setTimeout(function () {
			adjustEditor();
		}, 100);
		arm_disable_form_fields();
		setTimeout(function () {
			jQuery('.arm_editor_form_fileds_container').fadeIn();
			setTimeout(function () {
				jQuery('.arm_loading_grid').hide(0);
			}, 800);
		}, 800);
	});
	jQuery(window).resize(function () {
		adjustEditor();
	});
	jQuery(function ($) {
		adjustEditor();
		jQuery(document).on('click','.arm_slider_arrow_left', function (e) {
			var container = jQuery(this).attr('data-id');
			var arm_editor_left_width = jQuery('.arm_editor_left').width();
			if (isNaN(arm_editor_left_width) && arm_editor_left_width == undefined ) { arm_editor_left_width = 0; }
			jQuery('.arm_editor_center').css({'width': (jQuery('.arm_editor_wrapper').width() - arm_editor_left_width - jQuery('.arm_editor_right').width() - 50) + 'px'});
			jQuery('.' + container).toggle("slide");
			jQuery('.arm_slider_arrow_left').hide();
		});
		jQuery(document).on('click','.arm_slider_arrow_right', function (e) {
			var container = jQuery(this).attr('data-id');
			jQuery('.' + container).toggle("slide");
			jQuery('.arm_slider_arrow_left').show();
			var arm_editor_left_width = jQuery('.arm_editor_left').width();
			if (isNaN(arm_editor_left_width) && arm_editor_left_width == undefined ) { arm_editor_left_width = 0; }
			jQuery('.arm_editor_center').css({'width': (jQuery('.arm_editor_wrapper').width() - arm_editor_left_width - 50) + 'px'});
		});
<?php if ( ! empty( $form_detail ) ) { ?>
			jQuery(document).on('click', '#arm_reset_member_form', function () {
				location.reload();
			});
			jQuery(document).on('click', '#arm_save_member_form', function () {
				var form_data = '';
				var form_action_val = jQuery('#arm_manage_form_settings_form input.form_action_option_type:checked').val();
				if (form_action_val == 'page') {
					var form_action_page = jQuery('#arm_manage_form_settings_form .form_action_redirect_page').val();
					if (form_action_page == '' || form_action_page == 0) {
						armToast('<?php echo addslashes( esc_html__( 'Redirection page is required.', 'armember-membership' ) ); //phpcs:ignore ?>', 'error');
						jQuery('#arm_manage_form_settings_form .form_action_redirect_page').css('border-color', 'red');
						return false;
					}
				} else if (form_action_val == 'url') {
					var form_action_url = jQuery('#arm_manage_form_settings_form .form_action_redirect_url').val();

					if (form_action_url == '') {
						armToast('<?php echo addslashes( esc_html__( 'Redirection url is required.', 'armember-membership' ) ); //phpcs:ignore ?>', 'error');
						jQuery('#arm_manage_form_settings_form .form_action_redirect_url').css('border-color', 'red');
						return false;
					}
					if (form_action_url.match(/^\s+|\s+$/g)) {
						armToast('<?php echo addslashes( esc_html__( 'Please enter valid URL.', 'armember-membership' ) ); //phpcs:ignore ?>', 'error');
						return false;
					}
				}
				else if (form_action_val == 'conditional_redirect') {
					var form_action_url = jQuery('#arm_manage_form_settings_form .form_action_redirect_conditional_redirect').val();

					if (form_action_url == '') {
						armToast('<?php echo addslashes( esc_html__( 'Default Redirection url is required.', 'armember-membership' ) ); //phpcs:ignore ?>', 'error');
						jQuery('#arm_manage_form_settings_form .form_action_redirect_conditional_redirect').css('border-color', 'red');
						return false;
					}
					if (form_action_url.match(/^\s+|\s+$/g)) {
						armToast('<?php echo addslashes( esc_html__( 'Please enter valid URL.', 'armember-membership' ) ); //phpcs:ignore ?>', 'error');
						return false;
					}
				}
				else if (form_action_val == 'referral') {
					var form_action_url = jQuery('#arm_manage_form_settings_form .form_action_redirect_referral').val();

					if (form_action_url == '') {
						armToast('<?php echo addslashes( esc_html__( 'Default Redirection url is required.', 'armember-membership' ) ); //phpcs:ignore ?>', 'error');
						jQuery('#arm_manage_form_settings_form .form_action_redirect_referral').css('border-color', 'red');
						return false;
					}
					if (form_action_url.match(/^\s+|\s+$/g)) {
						armToast('<?php echo addslashes( esc_html__( 'Please enter valid URL.', 'armember-membership' ) ); //phpcs:ignore ?>', 'error');
						return false;
					}
				}
                else if( jQuery("#arm_opt_ins_cl_mode").prop("checked") && jQuery("#arm_opt_ins_cl_form_field").val() == '' ) {
                    armToast('<?php echo addslashes(esc_html__('Please select form field in Conditional Subscription.', 'armember-membership')); //phpcs:ignore?>', 'error');
                    return false;
                }

				jQuery('.arm_loading_grid').fadeIn('slow');
				form_data = jQuery('#arm_manage_form_settings_form').serialize();
				var arm_action = jQuery("#arm_action").val();
				jQuery(this).attr('disabled', 'disabled');
				nonce = jQuery('input[name="arm_wp_nonce"]').val();
                datastr = 'action=save_member_forms&' + form_data;
                languages_settings = "";
                if(jQuery('.arm_general_settings_multi_languages_tab').length>0){
                    languages_settings = jQuery(".arm_common_setting_multi_language_wrapper input,.arm_common_setting_multi_language_wrapper textarea").serialize();
                    datastr += '&' + languages_settings
                }
                datastr += '&_wpnonce='+nonce;
				jQuery.ajax({
					type: "POST",
					url: __ARMAJAXURL,
                    data: datastr,
					success: function (response)
					{
						response = arm_convert_json_from_pattern(response);
						response = jQuery.parseJSON(response);
						if (response.message == 'success') {
							armToast('<?php echo addslashes( esc_html__( 'Form Settings Saved Successfully.', 'armember-membership' ) ); //phpcs:ignore ?>', 'success');
							if (arm_action == 'new_form' || arm_action == 'duplicate_form') {
								if (window.history.pushState) {
									var pageurl = ArmRemoveVariableFromURL(document.URL, 'action');
									pageurl = ArmRemoveVariableFromURL(pageurl, 'form_id');
									pageurl = ArmRemoveVariableFromURL(pageurl, 'set_name');
									pageurl = ArmRemoveVariableFromURL(pageurl, 'arm_set_name');
									pageurl = ArmRemoveVariableFromURL(pageurl, 'form_meta_fields');
									pageurl = ArmRemoveVariableFromURL(pageurl, 'redirect_type');
									pageurl = ArmRemoveVariableFromURL(pageurl, 'pageid');
									pageurl = ArmRemoveVariableFromURL(pageurl, 'redirect_url');
									pageurl += '&action=edit_form&form_id=' + response.form_id;
									jQuery("#arm_action").val('edit_form');
									jQuery("#arm_form_id").val(response.form_id);
									window.history.pushState({path: pageurl}, '', pageurl);
								}
								if (response.form_type == 'registration') {
									jQuery('.arm_form_shortcode_container .arm_shortcode_text .armCopyText').attr("value","[arm_form id=\"" + response.form_id + "\"]");
									jQuery('.arm_form_shortcode_container .arm_shortcode_text .armCopyText').html("[arm_form id=\"" + response.form_id + "\"]");
									jQuery('.arm_form_shortcode_container .arm_shortcode_text .arm_click_to_copy_text').attr('data-code', "[arm_form id=\"" + response.form_id + "\"]");
									jQuery('.arm_form_shortcode_container').show();
								} else {
									var pageurl = ArmRemoveVariableFromURL(document.URL, 'form_id');
									var response_ids = response.form_ids;
									if(typeof response_ids != 'undefined')
									{
										var form_ids = response_ids.split(',');
										pageurl += '&form_id=' + form_ids[0];
										window.history.pushState({path: pageurl}, '', pageurl);
										jQuery("#arm_login_form_ids").val(response.form_ids);
										jQuery('#form_set_id').val(response.arm_form_set);
									}
								}
							}
						}
						else if(response.type=='error')
						{
							armToast(response.msg, 'error');
						}
						jQuery('.arm_loading_grid').fadeOut();
						jQuery(this).removeAttr('disabled');
						return false;
					}
				});
				jQuery('.arm_loading_grid').fadeOut();
				return false;
			});
<?php } ?>
<?php if ( $isRegister || $isEditProfile) { ?>
			jQuery(document).on('click', '.arm_field_type_list li:not(.arm_disabled)', function () {
				var field_type = jQuery(this).find('.arm_new_field a').attr('id');
				var form_id = '<?php echo intval($form_id); ?>';
				var check_old = 0;

				if (field_type == 'roles' || field_type == 'avatar') {
					check_old = jQuery('.arm-df__fields-wrapper_' + form_id + ' .arm-df__form-group_' + field_type).length;
				}
				var excludeKeys = ['first_name', 'last_name', 'user_login', 'user_email', 'user_pass', 'avatar', 'roles'];
				if (jQuery.inArray(field_type, excludeKeys) !== -1) {
					check_old = jQuery('.arm-df__fields-wrapper_' + form_id + ' .arm-df__form-group_' + field_type).length;
					if (check_old == 0) {
						check_old = jQuery('.arm-df__fields-wrapper_' + form_id + ' li[data-meta_key="' + field_type + '"]').length;
					}
				}
				if(jQuery('.arm-df__fields-wrapper_' + form_id + ' li[data-meta_key="user_pass"]').length && field_type == "password")
				{
					armToast('<?php echo addslashes( esc_html__( 'You have already added password field in you form.', 'armember-membership' ) ); //phpcs:ignore ?>', 'error');
					return false;
				}
				if (check_old > 0) {
					alert('<?php echo addslashes( esc_html__( 'Sorry, You can not add this field twice in form', 'armember-membership' ) ); //phpcs:ignore ?>');
				} else {
					var clone = jQuery(this).clone();
					jQuery('.arm_main_sortable.arm-df__fields-wrapper_' + form_id + '').append(clone);
					var $target = clone;
					armProcessFormFieldSorting(form_id, field_type, $target, '0');
					jQuery(window.opera ? 'html' : 'html, body').animate({scrollTop: jQuery('.arm-df__fields-wrapper_' + form_id + ' li:last').offset().top - 180}, 'slow');
				}
				return false;
			});
<?php } ?>
	});
	jQuery(document).on('change', '.form_action_option_type', function (e) {
		e.stopPropagation();
		var val = jQuery(this).val();
		jQuery('.arm_lable_shortcode_wrapper').addClass('hidden_section');
		jQuery('.arm_lable_shortcode_wrapper_' + val).removeClass('hidden_section');
	});
	jQuery(document).on('change', '.arm_form_email_tool_radio', function (e) {
		e.stopPropagation();
		var type = jQuery(this).attr('data-type');
		if (jQuery(this).is(':checked')) {
			jQuery(this).parents('.arm_etool_options_container').find('.arm_etool_list_container').removeClass('hidden_section');
		} else {
			jQuery(this).parents('.arm_etool_options_container').find('.arm_etool_list_container').addClass('hidden_section');
		}
	});
	jQuery(document).on('click', '.arm_color_scheme_nav_link', function (e) {
		e.stopPropagation();
		jQuery('a[href="#tabsetting-2"]').trigger('click');
		jQuery('a[href="#tabsetting-2"]').trigger('click');
		jQuery('#tabsetting-2 #arm_color_scheme_container').trigger('click');
	});
</script>
<?php
	// echo $ARMemberLite->arm_get_need_help_html_content('member-forms-editor');
?>
