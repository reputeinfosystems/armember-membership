<?php

global $ARMemberLite, $arm_global_settings, $arm_social_feature, $wpdb, $arm_lite_errors;

$globalSettings = $arm_global_settings->global_settings;

$register_page_id        = isset( $globalSettings['register_page_id'] ) ? $globalSettings['register_page_id'] : 0;
$forgot_password_page_id = isset( $globalSettings['forgot_password_page_id'] ) ? $globalSettings['forgot_password_page_id'] : 0;
$reg_redirect_id         = isset( $globalSettings['thank_you_page_id'] ) ? $globalSettings['thank_you_page_id'] : 0;
$login_redirect_id       = isset( $globalSettings['edit_profile_page_id'] ) ? $globalSettings['edit_profile_page_id'] : 0;

$wp_upload_dir       = wp_upload_dir();
$arm_lite_upload_dir = $wp_upload_dir['basedir'] . '/armember/';

/* Registration Template */

$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Template 1', 'armember-membership' );
$forms['arm_form_title']        = esc_html__( 'Please Signup', 'armember-membership' );
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-registration';
$forms['arm_set_name']          = esc_html__( 'Template 1', 'armember-membership' );
$forms['arm_is_default']        = 1;
$forms['arm_is_template']       = 1;
$forms['arm_ref_template']      = 1;
$forms['arm_set_id']            = 0;
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );

$form_settings = array(
	'redirect_type' => 'page',
	'redirect_page' => $reg_redirect_id,
	'redirect_url'  => '',
	'auto_login'    => '1',
	'style'         => array(
		'form_layout'                => 'writer',
		'form_width'                 => '550',
		'form_width_type'            => 'px',
		'form_border_width'          => '2',
		'form_border_radius'         => '12',
		'form_border_style'          => 'solid',
		'form_padding_left'          => '30',
		'form_padding_top'           => '40',
		'form_padding_bottom'        => '40',
		'form_padding_right'         => '30',
		'form_position'              => 'left',
		'form_bg'                    => '',
		'form_title_font_family'     => 'Poppins',
		'form_title_font_size'       => '24',
		'form_title_font_bold'       => '1',
		'form_title_font_italic'     => '0',
		'form_title_font_decoration' => '',
		'form_title_position'        => 'center',
		'validation_position'        => 'bottom',
		'color_scheme'               => 'blue',
		'lable_font_color'           => '#1A2538',
		'field_font_color'           => '#2F3F5C',
		'field_border_color'         => '#D3DEF0',
		'field_focus_color'          => '#637799',
		'button_back_color'          => '#005AEE',
		'button_font_color'          => '#FFFFFF',
		'button_hover_color'         => '#0D54C9',
		'button_hover_font_color'    => '#ffffff',
		'form_title_font_color'      => '#1A2538',
		'form_bg_color'              => '#FFFFFF',
		'form_border_color'          => '#CED4DE',
		'prefix_suffix_color'        => '#bababa',
		'error_font_color'           => '#FF3B3B',
		'error_field_border_color'   => '#FF3B3B',
		'error_field_bg_color'       => '#ffffff',
		'field_width'                => '100',
		'field_width_type'           => '%',
		'field_height'               => '44',
		'field_spacing'              => '18',
		'field_border_width'         => '1',
		'field_border_radius'        => '0',
		'field_border_style'         => 'solid',
		'field_font_family'          => 'Poppins',
		'field_font_size'            => '15',
		'field_font_bold'            => '0',
		'field_font_italic'          => '0',
		'field_font_decoration'      => '',
		'field_position'             => 'left',
		'rtl'                        => '0',
		'label_width'                => '250',
		'label_width_type'           => 'px',
		'label_position'             => 'block',
		'label_align'                => 'left',
		'label_hide'                 => '0',
		'label_font_family'          => 'Poppins',
		'label_font_size'            => '14',
		'description_font_size'      => '14',
		'label_font_bold'            => '0',
		'label_font_italic'          => '0',
		'label_font_decoration'      => '',
		'button_width'               => '360',
		'button_width_type'          => 'px',
		'button_height'              => '40',
		'button_height_type'         => 'px',
		'button_border_radius'       => '6',
		'button_style'               => 'border',
		'button_font_family'         => 'Poppins',
		'button_font_size'           => '15',
		'button_font_bold'           => '0',
		'button_font_italic'         => '0',
		'button_font_decoration'     => '',
		'button_margin_left'         => '0',
		'button_margin_top'          => '10',
		'button_margin_right'        => '0',
		'button_margin_bottom'       => '0',
		'button_position'            => 'center',
	),
);

$forms['arm_form_settings'] = maybe_serialize( $form_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id = $wpdb->insert_id;

$field_options = array(
	'id'              => 'user_login',
	'label'           => esc_html__( 'Username', 'armember-membership' ),
	'placeholder'     => '',
	'type'            => 'text',
	'meta_key'        => 'user_login', // phpcs:ignore
	'required'        => '1',
	'hide_username'   => '0',
	'blank_message'   => esc_html__( 'Username can not be left blank', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid username', 'armember-membership' ),
	'default_field'   => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 1,
	'arm_form_field_slug'         => 'user_login',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'             => 'first_name',
	'label'          => esc_html__( 'First Name', 'armember-membership' ),
	'placeholder'    => '',
	'type'           => 'text',
	'meta_key'       => 'first_name', // phpcs:ignore
	'required'       => '1',
	'hide_firstname' => '0',
	'blank_message'  => esc_html__( 'First Name can not be left blank.', 'armember-membership' ),
	'default_field'  => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => 'first_name',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'            => 'last_name',
	'label'         => esc_html__( 'Last Name', 'armember-membership' ),
	'placeholder'   => '',
	'type'          => 'text',
	'meta_key'      => 'last_name', // phpcs:ignore
	'required'      => '1',
	'hide_lastname' => '0',
	'blank_message' => esc_html__( 'Last Name can not be left blank.', 'armember-membership' ),
	'default_field' => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 3,
	'arm_form_field_slug'         => 'last_name',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'              => 'user_email',
	'label'           => esc_html__( 'Email Address', 'armember-membership' ),
	'placeholder'     => '',
	'type'            => 'email',
	'meta_key'        => 'user_email', // phpcs:ignore
	'required'        => '1',
	'blank_message'   => esc_html__( 'Email Address can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid email address.', 'armember-membership' ),
	'default_field'   => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 4,
	'arm_form_field_slug'         => 'user_email',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'              => 'user_pass',
	'label'           => esc_html__( 'Password', 'armember-membership' ),
	'placeholder'     => '',
	'type'            => 'password',
	'options'         => array(
		'strength_meter'  => '1',
		'strong_password' => '0',
		'minlength'       => '6',
		'maxlength'       => '',
		'special'         => '1',
		'numeric'         => '1',
		'uppercase'       => '1',
		'lowercase'       => '1',
	),
	'meta_key'        => 'user_pass', // phpcs:ignore
	'required'        => '1',
	'blank_message'   => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid password.', 'armember-membership' ),
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 5,
	'arm_form_field_slug'         => 'user_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'            => 'submit',
	'label'         => esc_html__( 'Submit', 'armember-membership' ),
	'type'          => 'submit',
	'default_field' => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 6,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );
unset( $form_id );
unset( $forms );

/* Registration Template */


/* Login Form Template Start */
$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Please Login', 'armember-membership' );
$forms['arm_form_title']        = esc_html__( 'Please Login', 'armember-membership' );
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-login';
$forms['arm_set_name']          = esc_html__( 'Template 1', 'armember-membership' );
$forms['arm_is_default']        = '1';
$forms['arm_is_template']       = '1';
$forms['arm_ref_template']      = 1;
$forms['arm_set_id']            = '-1';
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );

$form_template_settings                          = array();
$form_settings                                   = array();
$form_settings['display_direction']              = 'vertical';
$form_settings['redirect_type']                  = 'page';
$form_settings['redirect_page']                  = $login_redirect_id;
$form_settings['redirect_url']                   = '';
$form_settings['show_rememberme']                = '1';
$form_settings['show_registration_link']         = '1';
$form_settings['registration_link_label']        = '<center>Dont have account? [ARMLINK]SIGNUP[/ARMLINK]</center>';
$form_settings['registration_link_type']         = 'page';
$form_settings['registration_link_type_modal']   = '1';
$form_settings['registration_link_type_page']    = $register_page_id;
$form_settings['show_forgot_password_link']      = '1';
$form_settings['forgot_password_link_label']     = 'Lost Your Password';
$form_settings['forgot_password_link_type']      = 'modal';
$form_settings['forgot_password_link_type_page'] = $forgot_password_page_id;
$form_settings['forgot_password_link_margin']['bottom'] = '0';
$form_settings['forgot_password_link_margin']['top']    = '-132';
$form_settings['forgot_password_link_margin']['left']   = '315';
$form_settings['forgot_password_link_margin']['right']  = '0';
$form_settings['registration_link_margin']['top']       = 0;
$form_settings['registration_link_margin']['bottom']    = 0;
$form_settings['registration_link_margin']['left']      = 0;
$form_settings['registration_link_margin']['right']     = 0;

if ( $arm_social_feature->isSocialFeature && ! empty( $arm_social_feature->isSocialFeature ) ) {
	$social_networks = $arm_social_feature->social_settings['options'];
	$forms_networks  = array( 'facebook', 'twitter' );
	$networks        = '';
	$counter         = 0;
	$network_order   = '';
	foreach ( $social_networks as $key => $network ) {
		if ( in_array( $key, $forms_networks ) && $network['status'] == '1' ) {
			$networks .= $key . ',';
			$counter++;
		}
		$network_order .= $key . ',';
	}
	if ( $counter > 0 ) {
		$networks                                 = rtrim( $networks, ',' );
		$network_order                            = rtrim( $network_order, ',' );
		$form_settings['enable_social_login']     = '1';
		$form_settings['social_networks']         = $networks;
		$form_settings['social_networks_order']   = $network_order;
		$form_settings['social_network_settings'] = $social_networks;
	}
}

$form_style = array(
	'social_btn_position'         => 'bottom',
	'social_btn_type'             => 'horizontal',
	'social_btn_align'            => 'center',
	'enable_social_btn_separator' => '1',
	'social_btn_separator'        => '<center>OR</center>',
	'form_layout'                 => 'writer',
	'form_width'                  => '550',
	'form_width_type'             => 'px',
	'form_border_width'           => '2',
	'form_border_radius'          => '12',
	'form_border_style'           => 'solid',
	'form_padding_left'           => '30',
	'form_padding_top'            => '40',
	'form_padding_right'          => '30',
	'form_padding_bottom'         => '40',
	'form_position'               => 'left',
	'form_bg'                     => '',
	'form_title_font_family'      => 'Poppins',
	'form_title_font_size'        => '24',
	'form_title_font_bold'        => '1',
	'form_title_font_italic'      => '0',
	'form_title_font_decoration'  => '',
	'form_title_position'         => 'center',
	'validation_position'         => 'bottom',
	'color_scheme'                => 'blue',
	'lable_font_color'            => '#1A2538',
	'field_font_color'            => '#2F3F5C',
	'field_border_color'          => '#D3DEF0',
	'field_focus_color'           => '#637799',
	'button_back_color'           => '#005AEE',
	'button_font_color'           => '#FFFFFF',
	'button_hover_color'          => '#0D54C9',
	'button_hover_font_color'     => '#ffffff',
	'form_title_font_color'       => '#1A2538',
	'form_bg_color'               => '#FFFFFF',
	'form_border_color'           => '#CED4DE',
	'prefix_suffix_color'         => '#bababa',
	'error_font_color'            => '#FF3B3B',
	'error_field_border_color'    => '#FF3B3B',
	'error_field_bg_color'        => '#ffffff',
	'field_width'                 => '100',
	'field_width_type'            => '%',
	'field_height'                => '44',
	'field_spacing'               => '18',
	'field_border_width'          => '1',
	'field_border_radius'         => '0',
	'field_border_style'          => 'solid',
	'field_font_family'           => 'Poppins',
	'field_font_size'             => '15',
	'field_font_bold'             => '0',
	'field_font_italic'           => '0',
	'field_font_decoration'       => '',
	'field_position'              => 'left',
	'rtl'                         => '0',
	'label_width'                 => '250',
	'label_width_type'            => 'px',
	'label_position'              => 'block',
	'label_align'                 => 'left',
	'label_hide'                  => '0',
	'label_font_family'           => 'Poppins',
	'label_font_size'             => '14',
	'description_font_size'       => '14',
	'label_font_bold'             => '0',
	'label_font_italic'           => '0',
	'label_font_decoration'       => '',
	'button_width'                => '360',
	'button_width_type'           => 'px',
	'button_height'               => '40',
	'button_height_type'          => 'px',
	'button_border_radius'        => '6',
	'button_style'                => 'border',
	'button_font_family'          => 'Poppins',
	'button_font_size'            => '15',
	'button_font_bold'            => '0',
	'button_font_italic'          => '0',
	'button_font_decoration'      => '',
	'button_margin_left'          => '0',
	'button_margin_top'           => '10',
	'button_margin_right'         => '0',
	'button_margin_bottom'        => '0',
	'button_position'             => 'center',
);

$form_settings['style'] = $form_style;

$form_template_settings     = $form_settings;
$forms['arm_form_settings'] = maybe_serialize( $form_template_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id       = $wpdb->insert_id;
$field_options = array(
	'id'              => 'user_login',
	'type'            => 'text',
	'default_field'   => '1',
	'label'           => esc_html__( 'Username', 'armember-membership' ),
	'placeholder'     => '',
	'options'         => array(
		'minlength' => '',
		'maxlength' => '',
	),
	'required'        => '1',
	'meta_key'        => 'user_login', // phpcs:ignore
	'blank_message'   => esc_html__( 'Username can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid username.', 'armember-membership' ),
	'prefix'          => '',
	'suffix'          => '',
	'ref_field_id'    => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 1,
	'arm_form_field_slug'         => 'user_login',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'              => 'user_pass',
	'label'           => esc_html__( 'Password', 'armember-membership' ),
	'placeholder'     => '',
	'type'            => 'password',
	'default_field'   => '1',
	'options'         => array(
		'strength_meter'  => '0',
		'strong_password' => '0',
		'minlength'       => '1',
		'maxlength'       => '0',
		'special'         => '0',
		'numeric'         => '0',
		'uppercase'       => '0',
		'lowercase'       => '0',
	),
	'meta_key'        => 'user_pass', // phpcs:ignore
	'required'        => '1',
	'blank_message'   => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid password', 'armember-membership' ),
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => 'user_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'            => 'rememberme',
	'type'          => 'rememberme',
	'default_field' => '1',
	'default_val'   => 'forever',
	'label'         => esc_html__( 'Remember me', 'armember-membership' ),
	'meta_key'      => 'rememberme', // phpcs:ignore
	'ref_field_id'  => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 3,
	'arm_form_field_slug'         => 'rememberme',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'            => 'submit',
	'type'          => 'submit',
	'default_field' => '1',
	'label'         => 'LOGIN',
	'meta_key'      => '', //phpcs:ignore
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 4,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );
unset( $forms );
unset( $form_id );

/* Login Form Template End */

/* Forgot Password Form Start */
$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Forgot Password', 'armember-membership' );
$forms['arm_form_title']        = esc_html__( 'Forgot Password', 'armember-membership' );
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-forgot-password';
$forms['arm_set_name']          = esc_html__( 'Template 1', 'armember-membership' );
$forms['arm_is_default']        = '1';
$forms['arm_is_template']       = '1';
$forms['arm_ref_template']      = 1;
$forms['arm_set_id']            = '-1';
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );


$form_template_settings['redirect_type'] = 'message';

$form_template_settings['description'] = esc_html__( 'Please enter your email address or username below.', 'armember-membership' );

$forms['arm_form_settings'] = maybe_serialize( $form_template_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id = $wpdb->insert_id;

$field_options = array(
	'id'              => 'user_login',
	'type'            => 'text',
	'default_field'   => '1',
	'label'           => esc_html__( 'Username', 'armember-membership' ),
	'placeholder'     => '',
	'options'         => array(
		'minlength' => '',
		'maxlength' => '',
	),
	'required'        => '1',
	'meta_key'        => 'user_login', // phpcs:ignore
	'blank_message'   => esc_html__( 'Username can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid username.', 'armember-membership' ),
	'prefix'          => '',
	'suffix'          => '',
	'ref_field_id'    => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 1,
	'arm_form_field_slug'         => 'user_login',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'            => 'submit',
	'type'          => 'submit',
	'default_field' => '1',
	'label'         => 'Submit',
	'meta_key'      => '', // phpcs:ignore
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );
unset( $forms );
unset( $form_id );

/* Forgot Password Form End */

/* Change Password Form Start */
$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Change Password', 'armember-membership' );
$forms['arm_form_title']        = esc_html__( 'Change Password', 'armember-membership' );
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-change-password';
$forms['arm_set_name']          = esc_html__( 'Template 1', 'armember-membership' );
$forms['arm_is_default']        = '1';
$forms['arm_is_template']       = '1';
$forms['arm_ref_template']      = 1;
$forms['arm_set_id']            = '-1';
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );

$form_template_settings['redirect_type'] = 'message';
$form_template_settings['message']       = esc_html__( 'Your password changed successfully.', 'armember-membership' );


$forms['arm_form_settings'] = maybe_serialize( $form_template_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'current_user_pass',
    'type' => 'current_user_pass',
    'default_field' => '1',
    'label' => esc_html__('Current Password', 'armember-membership'),
    'placeholder' => esc_html__('Current Password', 'armember-membership'),
    'options' => array(
        'minlength' => '0',
        'maxlength' => '',
        'strength_meter' => '0',
        'special' => '0',
        'numeric' => '0',
        'uppercase' => '0',
        'lowercase' => '0'
    ),
    'required' => '1',
    'meta_key' => 'current_user_pass', // phpcs:ignore
    'blank_message' => esc_html__('Password can not be left blank.', 'armember-membership'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'current_user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => current_time( 'mysql' )
);

$wpdb->insert($ARMemberLite->tbl_arm_form_field, $form_field_data); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_field_id = $wpdb->insert_id;
unset($form_field_data);
unset($field_options);

$field_options = array(
	'id'            => 'user_pass',
	'type'          => 'password',
	'default_field' => '1',
	'label'         => esc_html__( 'New Password', 'armember-membership' ),
	'placeholder'   => '',
	'options'       => array(
		'minlength'      => '6',
		'maxlength'      => '',
		'strength_meter' => '1',
		'special'        => '1',
		'numeric'        => '1',
		'uppercase'      => '1',
		'lowercase'      => '1',
	),
	'required'      => '1',
	'meta_key'      => 'user_pass', // phpcs:ignore
	'blank_message' => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
	'prefix'        => '',
	'suffix'        => '',
	'ref_field_id'  => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => 'user_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_field_id = $wpdb->insert_id;
unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'              => 'repeat_pass',
	'type'            => 'repeat_pass',
	'default_field'   => '1',
	'label'           => esc_html__( 'Confirm Password', 'armember-membership' ),
	'required'        => '1',
	'meta_key'        => 'repeat_pass', // phpcs:ignore
	'blank_message'   => esc_html__( 'Confirm Password can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Passwords don\'t match.', 'armember-membership' ),
	'prefix'          => '',
	'suffix'          => '',
	'ref_field_id'    => $form_field_id,
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 3,
	'arm_form_field_slug'         => 'repeat_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);
$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_field_id = $wpdb->insert_id;
unset( $form_field_data );
unset( $field_options );
unset( $form_field_id );

$field_options = array(
	'id'            => 'submit',
	'type'          => 'submit',
	'default_field' => '1',
	'label'         => 'Submit',
	'meta_key'      => '', // phpcs:ignore
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 4,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );
unset( $forms );
unset( $form_id );
unset( $form_template_settings );

/* Change Password Form End */

/* First Set End */

/* Second Set Start */

$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Template 2', 'armember-membership' ) . "<hr style='border:2px solid #005aee;'/>";
$forms['arm_form_title']        = esc_html__( 'Please Signup', 'armember-membership' ) . "<hr style='border:2px solid #005aee;'/>";
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-registration-2';
$forms['arm_set_name']          = esc_html__( 'Template 2', 'armember-membership' );
$forms['arm_is_default']        = 1;
$forms['arm_is_template']       = 1;
$forms['arm_ref_template']      = 2;
$forms['arm_set_id']            = 0;
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );

$form_settings = array(
	'redirect_type' => 'page',
	'redirect_page' => $reg_redirect_id,
	'redirect_url'  => '',
	'auto_login'    => '1',
	'style'         => array(
		'form_layout'                => 'writer',
		'form_width'                 => '550',
		'form_width_type'            => 'px',
		'form_border_width'          => '2',
		'form_border_radius'         => '12',
		'form_border_style'          => 'solid',
		'form_padding_left'          => '30',
		'form_padding_top'           => '40',
		'form_padding_bottom'        => '40',
		'form_padding_right'         => '30',
		'form_position'              => 'left',
		'form_bg'                    => '',
		'form_title_font_family'     => 'Poppins',
		'form_title_font_size'       => '24',
		'form_title_font_bold'       => '1',
		'form_title_font_italic'     => '0',
		'form_title_font_decoration' => '',
		'form_title_position'        => 'center',
		'validation_position'        => 'bottom',
		'color_scheme'               => 'blue',
		'lable_font_color'           => '#1A2538',
		'field_font_color'           => '#2F3F5C',
		'field_border_color'         => '#D3DEF0',
		'field_focus_color'          => '#637799',
		'button_back_color'          => '#005AEE',
		'button_font_color'          => '#FFFFFF',
		'button_hover_color'         => '#0D54C9',
		'button_hover_font_color'    => '#ffffff',
		'login_link_font_color'      => '#005AEE',
		'register_link_font_color'   => '#005AEE',
		'form_title_font_color'      => '#1A2538',
		'form_bg_color'              => '#FFFFFF',
		'form_border_color'          => '#CED4DE',
		'prefix_suffix_color'        => '#bababa',
		'error_font_color'           => '#FF3B3B',
		'error_field_border_color'   => '#FF3B3B',
		'error_field_bg_color'       => '#ffffff',
		'field_width'                => '100',
		'field_width_type'           => '%',
		'field_height'               => '44',
		'field_spacing'              => '18',
		'field_border_width'         => '1',
		'field_border_radius'        => '0',
		'field_border_style'         => 'solid',
		'field_font_family'          => 'Poppins',
		'field_font_size'            => '15',
		'field_font_bold'            => '0',
		'field_font_italic'          => '0',
		'field_font_decoration'      => '',
		'field_position'             => 'left',
		'rtl'                        => '0',
		'label_width'                => '250',
		'label_width_type'           => 'px',
		'label_position'             => 'block',
		'label_align'                => 'left',
		'label_hide'                 => '0',
		'label_font_family'          => 'Poppins',
		'label_font_size'            => '14',
		'description_font_size'      => '14',
		'label_font_bold'            => '0',
		'label_font_italic'          => '0',
		'label_font_decoration'      => '',
		'button_width'               => '110',
		'button_width_type'          => 'px',
		'button_height'              => '100',
		'button_height_type'         => 'px',
		'button_border_radius'       => '90',
		'button_style'               => 'border',
		'button_font_family'         => 'Poppins',
		'button_font_size'           => '15',
		'button_font_bold'           => '0',
		'button_font_italic'         => '0',
		'button_font_decoration'     => '',
		'button_margin_left'         => '0',
		'button_margin_top'          => '10',
		'button_margin_right'        => '0',
		'button_margin_bottom'       => '0',
		'button_position'            => 'center',
	),
	'custom_css'    => '.arm-df__heading{padding-bottom:40px !important;}.arm_forgot_password_description{margin-top: -30px !important;margin-bottom: 40px !important;margin-left: 20px !important;}',
);

$forms['arm_form_settings'] = maybe_serialize( $form_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id = $wpdb->insert_id;

$field_options = array(
	'id'              => 'user_login',
	'label'           => esc_html__( 'Username', 'armember-membership' ),
	'placeholder'     => '',
	'type'            => 'text',
	'meta_key'        => 'user_login', // phpcs:ignore
	'required'        => '1',
	'hide_username'   => '0',
	'blank_message'   => esc_html__( 'Username can not be left blank', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid username', 'armember-membership' ),
	'default_field'   => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 1,
	'arm_form_field_slug'         => 'user_login',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'             => 'first_name',
	'label'          => esc_html__( 'First Name', 'armember-membership' ),
	'placeholder'    => '',
	'type'           => 'text',
	'meta_key'       => 'first_name', // phpcs:ignore
	'required'       => '1',
	'hide_firstname' => '0',
	'blank_message'  => esc_html__( 'First Name can not be left blank.', 'armember-membership' ),
	'default_field'  => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => 'first_name',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'            => 'last_name',
	'label'         => esc_html__( 'Last Name', 'armember-membership' ),
	'placeholder'   => '',
	'type'          => 'text',
	'meta_key'      => 'last_name', // phpcs:ignore
	'required'      => '1',
	'hide_lastname' => '0',
	'blank_message' => esc_html__( 'Last Name can not be left blank.', 'armember-membership' ),
	'default_field' => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 3,
	'arm_form_field_slug'         => 'last_name',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'              => 'user_email',
	'label'           => esc_html__( 'Email Address', 'armember-membership' ),
	'placeholder'     => '',
	'type'            => 'email',
	'meta_key'        => 'user_email', // phpcs:ignore
	'required'        => '1',
	'blank_message'   => esc_html__( 'Email Address can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid email address.', 'armember-membership' ),
	'default_field'   => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 4,
	'arm_form_field_slug'         => 'user_email',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'              => 'user_pass',
	'label'           => esc_html__( 'Password', 'armember-membership' ),
	'placeholder'     => '',
	'type'            => 'password',
	'options'         => array(
		'strength_meter'  => '1',
		'strong_password' => '0',
		'minlength'       => '6',
		'maxlength'       => '',
		'special'         => '1',
		'numeric'         => '1',
		'uppercase'       => '1',
		'lowercase'       => '1',
	),
	'meta_key'        => 'user_pass', // phpcs:ignore
	'required'        => '1',
	'blank_message'   => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid password.', 'armember-membership' ),
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 5,
	'arm_form_field_slug'         => 'user_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'            => 'submit',
	'label'         => esc_html__( 'Submit', 'armember-membership' ),
	'type'          => 'submit',
	'default_field' => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 6,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );
unset( $form_id );
unset( $forms );

/* Registration Template */

$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Please Login', 'armember-membership' ) . "<hr style='border:2px solid #005aee;' />";
$forms['arm_form_title']        = esc_html__( 'Please Login', 'armember-membership' ) . "<hr style='border:2px solid #005aee;' />";
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-login-2';
$forms['arm_set_name']          = esc_html__( 'Template 2', 'armember-membership' );
$forms['arm_is_default']        = '1';
$forms['arm_is_template']       = '1';
$forms['arm_ref_template']      = 2;
$forms['arm_set_id']            = '-2';
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );

$form_template_settings                          = array();
$form_settings                                   = array();
$form_settings['display_direction']              = 'vertical';
$form_settings['redirect_type']                  = 'page';
$form_settings['redirect_page']                  = $login_redirect_id;
$form_settings['redirect_url']                   = '';
$form_settings['show_rememberme']                = '1';
$form_settings['show_registration_link']         = '1';
$form_settings['registration_link_label']        = 'Dont have account? [ARMLINK]SIGNUP[/ARMLINK]';
$form_settings['registration_link_type']         = 'page';
$form_settings['registration_link_type_modal']   = '1';
$form_settings['registration_link_type_page']    = $register_page_id;
$form_settings['show_forgot_password_link']      = '1';
$form_settings['forgot_password_link_label']     = 'Forgot Password';
$form_settings['forgot_password_link_type']      = 'modal';
$form_settings['forgot_password_link_type_page'] = $forgot_password_page_id;
$form_settings['forgot_password_link_margin']['bottom'] = '0';
$form_settings['forgot_password_link_margin']['top']    = '-198';
$form_settings['forgot_password_link_margin']['left']   = '320';
$form_settings['forgot_password_link_margin']['right']  = '0';
$form_settings['registration_link_margin']['top']       = '5';
$form_settings['registration_link_margin']['bottom']    = '0';
$form_settings['registration_link_margin']['left']      = '110';
$form_settings['registration_link_margin']['right']     = '0';

$form_style = array(
	'form_layout'                => 'writer',
	'form_width'                 => '550',
	'form_width_type'            => 'px',
	'form_border_width'          => '2',
	'form_border_radius'         => '12',
	'form_border_style'          => 'solid',
	'form_padding_left'          => '30',
	'form_padding_top'           => '40',
	'form_padding_right'         => '30',
	'form_padding_bottom'        => '40',
	'form_position'              => 'left',
	'form_bg'                    => '',
	'form_title_font_family'     => 'Poppins',
	'form_title_font_size'       => '24',
	'form_title_font_bold'       => '1',
	'form_title_font_italic'     => '0',
	'form_title_font_decoration' => '',
	'form_title_position'        => 'center',
	'validation_position'        => 'bottom',
	'color_scheme'               => 'blue',
	'lable_font_color'           => '#1A2538',
	'field_font_color'           => '#2F3F5C',
	'field_border_color'         => '#D3DEF0',
	'field_focus_color'          => '#637799',
	'button_back_color'          => '#005AEE',
	'button_font_color'          => '#FFFFFF',
	'button_hover_color'         => '#0D54C9',
	'button_hover_font_color'    => '#ffffff',
	'login_link_font_color'      => '#005AEE',
	'register_link_font_color'   => '#005AEE',
	'form_title_font_color'      => '#1A2538',
	'form_bg_color'              => '#FFFFFF',
	'form_border_color'          => '#CED4DE',
	'prefix_suffix_color'        => '#bababa',
	'error_font_color'           => '#FF3B3B',
	'error_field_border_color'   => '#FF3B3B',
	'error_field_bg_color'       => '#ffffff',
	'field_width'                => '100',
	'field_width_type'           => '%',
	'field_height'               => '44',
	'field_spacing'              => '18',
	'field_border_width'         => '1',
	'field_border_radius'        => '0',
	'field_border_style'         => 'solid',
	'field_font_family'          => 'Poppins',
	'field_font_size'            => '15',
	'field_font_bold'            => '0',
	'field_font_italic'          => '0',
	'field_font_decoration'      => '',
	'field_position'             => 'left',
	'rtl'                        => '0',
	'label_width'                => '250',
	'label_width_type'           => 'px',
	'label_position'             => 'block',
	'label_align'                => 'left',
	'label_hide'                 => '0',
	'label_font_family'          => 'Poppins',
	'label_font_size'            => '14',
	'description_font_size'      => '14',
	'label_font_bold'            => '0',
	'label_font_italic'          => '0',
	'label_font_decoration'      => '',
	'button_width'               => '110',
	'button_width_type'          => 'px',
	'button_height'              => '110',
	'button_height_type'         => 'px',
	'button_border_radius'       => '90',
	'button_style'               => 'border',
	'button_font_family'         => 'Poppins',
	'button_font_size'           => '15',
	'button_font_bold'           => '0',
	'button_font_italic'         => '0',
	'button_font_decoration'     => '',
	'button_margin_left'         => '0',
	'button_margin_top'          => '5',
	'button_margin_right'        => '0',
	'button_margin_bottom'       => '0',
	'button_position'            => 'center',
);

$form_settings['style'] = $form_style;

$form_settings['custom_css'] = '.arm-df__heading{padding-bottom:40px !important;}.arm_forgot_password_description{margin-top: -30px !important;margin-bottom: 40px !important;margin-left: 20px !important;}';

$form_template_settings     = $form_settings;
$forms['arm_form_settings'] = maybe_serialize( $form_template_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id       = $wpdb->insert_id;
$field_options = array(
	'id'              => 'user_login',
	'type'            => 'text',
	'default_field'   => '1',
	'label'           => esc_html__( 'Username', 'armember-membership' ),
	'placeholder'     => '',
	'options'         => array(
		'minlength' => '',
		'maxlength' => '',
	),
	'required'        => '1',
	'hide_username'   => 0,
	'meta_key'        => 'user_login', // phpcs:ignore
	'blank_message'   => esc_html__( 'Username can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid username.', 'armember-membership' ),
	'prefix'          => '',
	'suffix'          => '',
	'ref_field_id'    => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 1,
	'arm_form_field_slug'         => 'user_login',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'              => 'user_pass',
	'label'           => esc_html__( 'Password', 'armember-membership' ),
	'placeholder'     => '',
	'type'            => 'password',
	'default_field'   => '1',
	'options'         => array(
		'strength_meter'  => '0',
		'strong_password' => '0',
		'minlength'       => '1',
		'maxlength'       => '0',
		'special'         => '0',
		'numeric'         => '0',
		'uppercase'       => '0',
		'lowercase'       => '0',
	),
	'meta_key'        => 'user_pass', // phpcs:ignore
	'required'        => '1',
	'blank_message'   => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid password', 'armember-membership' ),
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => 'user_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'            => 'rememberme',
	'type'          => 'rememberme',
	'default_field' => '1',
	'label'         => esc_html__( 'Remember me', 'armember-membership' ),
	'meta_key'      => 'rememberme', // phpcs:ignore
	'ref_field_id'  => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 3,
	'arm_form_field_slug'         => 'rememberme',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'            => 'submit',
	'type'          => 'submit',
	'default_field' => '1',
	'label'         => 'LOGIN',
	'meta_key'      => '', // phpcs:ignore
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 4,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );
unset( $forms );
unset( $form_id );

/* Login Form Template End */

/* Forgot Password Form Start */
$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Forgot Password', 'armember-membership' ) . "<hr style='border:2px solid #005aee;' />";
$forms['arm_form_title']        = esc_html__( 'Forgot Password', 'armember-membership' ) . "<hr style='border:2px solid #005aee;' />";
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-forgot-password-2';
$forms['arm_set_name']          = esc_html__( 'Template 2', 'armember-membership' );
$forms['arm_is_default']        = '1';
$forms['arm_is_template']       = '1';
$forms['arm_ref_template']      = 2;
$forms['arm_set_id']            = '-2';
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );


$form_template_settings['redirect_type'] = 'message';

$form_template_settings['description'] = esc_html__( 'Please enter your email address or username below.', 'armember-membership' );

$forms['arm_form_settings'] = maybe_serialize( $form_template_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id = $wpdb->insert_id;

$field_options = array(
	'id'              => 'user_login',
	'type'            => 'text',
	'default_field'   => '1',
	'label'           => esc_html__( 'Username', 'armember-membership' ),
	'placeholder'     => '',
	'options'         => array(
		'minlength' => '',
		'maxlength' => '',
	),
	'required'        => '1',
	'meta_key'        => 'user_login', // phpcs:ignore
	'hide_username'   => 0,
	'blank_message'   => esc_html__( 'Username can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid username.', 'armember-membership' ),
	'prefix'          => '',
	'suffix'          => '',
	'ref_field_id'    => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 1,
	'arm_form_field_slug'         => 'user_login',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'            => 'submit',
	'type'          => 'submit',
	'default_field' => '1',
	'label'         => 'Submit',
	'meta_key'      => '', // phpcs:ignore
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );
unset( $forms );
unset( $form_id );

/* Forgot Password Form End */

/* Change Password Form Start */
$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Change Password', 'armember-membership' ) . "<hr style='border:2px solid #005aee;' />";
$forms['arm_form_title']        = esc_html__( 'Change Password', 'armember-membership' ) . "<hr style='border:2px solid #005aee;' />";
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-change-password-2';
$forms['arm_set_name']          = esc_html__( 'Template 2', 'armember-membership' );
$forms['arm_is_default']        = '1';
$forms['arm_is_template']       = '1';
$forms['arm_ref_template']      = 2;
$forms['arm_set_id']            = '-2';
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );

$form_template_settings['redirect_type'] = 'message';
$form_template_settings['message']       = esc_html__( 'Your password changed successfully.', 'armember-membership' );


$forms['arm_form_settings'] = maybe_serialize( $form_template_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'current_user_pass',
    'type' => 'current_user_pass',
    'default_field' => '1',
    'label' => esc_html__('Current Password', 'armember-membership'),
    'placeholder' => esc_html__('Current Password', 'armember-membership'),
    'options' => array(
        'minlength' => '0',
        'maxlength' => '',
        'strength_meter' => '0',
        'special' => '0',
        'numeric' => '0',
        'uppercase' => '0',
        'lowercase' => '0'
    ),
    'required' => '1',
    'meta_key' => 'current_user_pass', // phpcs:ignore
    'blank_message' => esc_html__('Password can not be left blank.', 'armember-membership'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'current_user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => current_time( 'mysql' )
);

$wpdb->insert($ARMemberLite->tbl_arm_form_field, $form_field_data); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_field_id = $wpdb->insert_id;
unset($form_field_data);
unset($field_options);

$field_options = array(
	'id'            => 'user_pass',
	'type'          => 'password',
	'default_field' => '1',
	'label'         => esc_html__( 'New Password', 'armember-membership' ),
	'placeholder'   => '',
	'options'       => array(
		'minlength'      => '6',
		'maxlength'      => '',
		'strength_meter' => '1',
		'special'        => '1',
		'numeric'        => '1',
		'uppercase'      => '1',
		'lowercase'      => '1',
	),
	'required'      => '1',
	'meta_key'      => 'user_pass', // phpcs:ignore
	'blank_message' => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
	'prefix'        => '',
	'suffix'        => '',
	'ref_field_id'  => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => 'user_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_field_id = $wpdb->insert_id;
unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'              => 'repeat_pass',
	'type'            => 'repeat_pass',
	'default_field'   => '1',
	'label'           => esc_html__( 'Confirm Password', 'armember-membership' ),
	'required'        => '1',
	'meta_key'        => 'repeat_pass', // phpcs:ignore
	'blank_message'   => esc_html__( 'Confirm Password can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Passwords don\'t match.', 'armember-membership' ),
	'prefix'          => '',
	'suffix'          => '',
	'ref_field_id'    => $form_field_id,
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 3,
	'arm_form_field_slug'         => 'repeat_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);
$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_field_id = $wpdb->insert_id;
unset( $form_field_data );
unset( $field_options );
unset( $form_field_id );

$field_options = array(
	'id'            => 'submit',
	'type'          => 'submit',
	'default_field' => '1',
	'label'         => 'Submit',
	'meta_key'      => '', // phpcs:ignore
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 4,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );
unset( $forms );
unset( $form_id );
unset( $form_template_settings );

/* Second Set End */

/* Third Set Start */


/* Registration Template */

$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Template 3', 'armember-membership' );
$forms['arm_form_title']        = esc_html__( 'Please Signup', 'armember-membership' );
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-registration-3';
$forms['arm_set_name']          = esc_html__( 'Template 3', 'armember-membership' );
$forms['arm_is_default']        = 1;
$forms['arm_is_template']       = 1;
$forms['arm_ref_template']      = 3;
$forms['arm_set_id']            = 0;
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );

$form_settings = array(
	'redirect_type' => 'page',
	'redirect_page' => $reg_redirect_id,
	'redirect_url'  => '',
	'auto_login'    => '1',
	'style'         => array(
		'form_layout'                 => 'rounded',
		'form_width'                  => '550',
		'form_width_type'             => 'px',
		'form_border_width'           => '2',
		'form_border_radius'          => '12',
		'form_border_style'           => 'solid',
		'form_padding_left'           => '30',
		'form_padding_top'            => '40',
		'form_padding_bottom'         => '40',
		'form_padding_right'          => '30',
		'form_position'               => 'left',
		'form_bg'                     => '',
		'form_title_font_family'      => 'Poppins',
		'form_title_font_size'        => '24',
		'form_title_font_bold'        => '1',
		'form_title_font_italic'      => '0',
		'form_title_font_decoration'  => '',
		'form_title_position'         => 'center',
		'validation_position'         => 'bottom',
		'color_scheme'                => 'red',
		'lable_font_color'            => '#1a2538',
		'field_font_color'            => '#242424',
		'field_border_color'          => '#dbdbdb',
		'field_focus_color'           => '#a38ea3',
		'button_back_color'           => '#dd2476',
		'button_back_color_gradient'  => '#ff512f',
		'button_font_color'           => '#ffffff',
		'button_hover_color'          => '#dd2476',
		'button_hover_font_color'     => '#ffffff',
		'button_hover_color_gradient' => '#ff512f',
		'login_link_font_color'       => '#e65e80',
		'register_link_font_color'    => '#e65e80',
		'form_title_font_color'       => '#dd2476',
		'form_bg_color'               => '#ffffff',
		'form_border_color'           => '#e6e7f5',
		'prefix_suffix_color'         => '#997a88',
		'error_font_color'            => '#ffffff',
		'error_field_border_color'    => '#f05050',
		'error_field_bg_color'        => '#e6594d',
		'field_width'                 => '100',
		'field_width_type'            => '%',
		'field_height'                => '44',
		'field_spacing'               => '8',
		'field_border_width'          => '2',
		'field_border_radius'         => '40',
		'field_border_style'          => 'solid',
		'field_font_family'           => 'Poppins',
		'field_font_size'             => '15',
		'field_font_bold'             => '0',
		'field_font_italic'           => '0',
		'field_font_decoration'       => '',
		'field_position'              => 'left',
		'rtl'                         => '0',
		'label_width'                 => '250',
		'label_width_type'            => 'px',
		'label_position'              => 'block',
		'label_align'                 => 'left',
		'label_hide'                  => '1',
		'label_font_family'           => 'Poppins',
		'label_font_size'             => '14',
		'description_font_size'       => '14',
		'label_font_bold'             => '0',
		'label_font_italic'           => '0',
		'label_font_decoration'       => '',
		'button_width'                => '180',
		'button_width_type'           => 'px',
		'button_height'               => '48',
		'button_height_type'          => 'px',
		'button_border_radius'        => '50',
		'button_style'                => 'flat',
		'button_font_family'          => 'Poppins',
		'button_font_size'            => '15',
		'button_font_bold'            => '1',
		'button_font_italic'          => '0',
		'button_font_decoration'      => '',
		'button_margin_left'          => '0',
		'button_margin_top'           => '5',
		'button_margin_right'         => '0',
		'button_margin_bottom'        => '0',
		'button_position'             => 'center',
	),
	'custom_css'    => '.arm-df__form-field-wrap_rememberme{margin-top:-10px !important;margin-left:15px !important;}.arm-df__form-control-submit-btn:hover{border:0px; !important;}.arm-df__heading{margin-bottom:40px !important;}',
);

$forms['arm_form_settings'] = maybe_serialize( $form_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id = $wpdb->insert_id;

$field_options = array(
	'id'              => 'user_login',
	'label'           => esc_html__( 'Username', 'armember-membership' ),
	'placeholder'     => esc_html__( 'Username', 'armember-membership' ),
	'type'            => 'text',
	'meta_key'        => 'user_login', // phpcs:ignore
	'hide_username'   => '0',
	'required'        => '1',
	'blank_message'   => esc_html__( 'Username can not be left blank', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid username', 'armember-membership' ),
	'default_field'   => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 1,
	'arm_form_field_slug'         => 'user_login',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'             => 'first_name',
	'label'          => esc_html__( 'First Name', 'armember-membership' ),
	'placeholder'    => esc_html__( 'First Name', 'armember-membership' ),
	'type'           => 'text',
	'meta_key'       => 'first_name', //phpcs:ignore
	'required'       => '1',
	'hide_firstname' => '0',
	'blank_message'  => esc_html__( 'First Name can not be left blank.', 'armember-membership' ),
	'default_field'  => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => 'first_name',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'            => 'last_name',
	'label'         => esc_html__( 'Last Name', 'armember-membership' ),
	'placeholder'   => esc_html__( 'Last Name', 'armember-membership' ),
	'type'          => 'text',
	'meta_key'      => 'last_name', // phpcs:ignore
	'required'      => '1',
	'hide_lastname' => '0',
	'blank_message' => esc_html__( 'Last Name can not be left blank.', 'armember-membership' ),
	'default_field' => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 3,
	'arm_form_field_slug'         => 'last_name',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'              => 'user_email',
	'label'           => esc_html__( 'Email Address', 'armember-membership' ),
	'placeholder'     => esc_html__( 'Email Address', 'armember-membership' ),
	'type'            => 'email',
	'meta_key'        => 'user_email', // phpcs:ignore
	'required'        => '1',
	'blank_message'   => esc_html__( 'Email Address can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid email address.', 'armember-membership' ),
	'default_field'   => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 4,
	'arm_form_field_slug'         => 'user_email',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'              => 'user_pass',
	'label'           => esc_html__( 'Password', 'armember-membership' ),
	'placeholder'     => esc_html__( 'Password', 'armember-membership' ),
	'type'            => 'password',
	'options'         => array(
		'strength_meter'  => '1',
		'strong_password' => '0',
		'minlength'       => '6',
		'maxlength'       => '',
		'special'         => '1',
		'numeric'         => '1',
		'uppercase'       => '1',
		'lowercase'       => '1',
	),
	'meta_key'        => 'user_pass', // phpcs:ignore
	'required'        => '1',
	'blank_message'   => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid password.', 'armember-membership' ),
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 5,
	'arm_form_field_slug'         => 'user_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'            => 'submit',
	'label'         => esc_html__( 'Submit', 'armember-membership' ),
	'type'          => 'submit',
	'default_field' => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 6,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );
unset( $form_id );
unset( $forms );

/* Registration Template */

$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'PLEASE LOGIN', 'armember-membership' );
$forms['arm_form_title']        = esc_html__( 'PLEASE LOGIN', 'armember-membership' );
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-login-3';
$forms['arm_set_name']          = esc_html__( 'Template 3', 'armember-membership' );
$forms['arm_is_default']        = '1';
$forms['arm_is_template']       = '1';
$forms['arm_ref_template']      = 3;
$forms['arm_set_id']            = '-3';
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );

$form_template_settings                          = array();
$form_settings                                   = array();
$form_settings['display_direction']              = 'vertical';
$form_settings['redirect_type']                  = 'page';
$form_settings['redirect_page']                  = $login_redirect_id;
$form_settings['redirect_url']                   = '';
$form_settings['show_rememberme']                = '1';
$form_settings['show_registration_link']         = '1';
$form_settings['registration_link_label']        = 'CREATE ACCOUNT';
$form_settings['registration_link_type']         = 'page';
$form_settings['registration_link_type_modal']   = '1';
$form_settings['registration_link_type_page']    = $register_page_id;
$form_settings['show_forgot_password_link']      = '1';
$form_settings['forgot_password_link_label']     = 'Forgot Password';
$form_settings['forgot_password_link_type']      = 'modal';
$form_settings['forgot_password_link_type_page'] = $forgot_password_page_id;
$form_settings['forgot_password_link_margin']['bottom'] = '0';
$form_settings['forgot_password_link_margin']['top']    = '-132';
$form_settings['forgot_password_link_margin']['left']   = '320';
$form_settings['forgot_password_link_margin']['right']  = '0';
$form_settings['registration_link_margin']['top']       = '5';
$form_settings['registration_link_margin']['bottom']    = '0';
$form_settings['registration_link_margin']['left']      = '150';
$form_settings['registration_link_margin']['right']     = '0';

$form_style = array(
	'form_layout'                 => 'rounded',
	'form_width'                  => '550',
	'form_width_type'             => 'px',
	'form_border_width'           => '2',
	'form_border_radius'          => '12',
	'form_border_style'           => 'solid',
	'form_padding_left'           => '30',
	'form_padding_top'            => '40',
	'form_padding_right'          => '30',
	'form_padding_bottom'         => '40',
	'form_position'               => 'left',
	'form_bg'                     => '',
	'form_title_font_family'      => 'Poppins',
	'form_title_font_size'        => '24',
	'form_title_font_bold'        => '1',
	'form_title_font_italic'      => '0',
	'form_title_font_decoration'  => '',
	'form_title_position'         => 'center',
	'validation_position'         => 'bottom',
	'color_scheme'                => 'red',
	'lable_font_color'            => '#1a2538',
	'field_font_color'            => '#242424',
	'field_border_color'          => '#dbdbdb',
	'field_focus_color'           => '#a38ea3',
	'button_back_color'           => '#dd2476',
	'button_back_color_gradient'  => '#ff512f',
	'button_hover_color'          => '#dd2476',
	'button_hover_color_gradient' => '#ff512f',
	'button_font_color'           => '#ffffff',
	'button_hover_font_color'     => '#ffffff',
	'login_link_font_color'       => '#e65e80',
	'register_link_font_color'    => '#e65e80',
	'form_title_font_color'       => '#dd2476',
	'form_bg_color'               => '#ffffff',
	'form_border_color'           => '#e6e7f5',
	'prefix_suffix_color'         => '#997a88',
	'error_font_color'            => '#ffffff',
	'error_field_border_color'    => '#f05050',
	'error_field_bg_color'        => '#e6594d',
	'field_width'                 => '100',
	'field_width_type'            => '%',
	'field_height'                => '44',
	'field_spacing'               => '8',
	'field_border_width'          => '2',
	'field_border_radius'         => '40',
	'field_border_style'          => 'solid',
	'field_font_family'           => 'Poppins',
	'field_font_size'             => '15',
	'field_font_bold'             => '0',
	'field_font_italic'           => '0',
	'field_font_decoration'       => '',
	'field_position'              => 'left',
	'rtl'                         => '0',
	'label_width'                 => '250',
	'label_width_type'            => 'px',
	'label_position'              => 'block',
	'label_align'                 => 'left',
	'label_hide'                  => '1',
	'label_font_family'           => 'Poppins',
	'label_font_size'             => '14',
	'description_font_size'       => '14',
	'label_font_bold'             => '0',
	'label_font_italic'           => '0',
	'label_font_decoration'       => '',
	'button_width'                => '180',
	'button_width_type'           => 'px',
	'button_height'               => '48',
	'button_height_type'          => 'px',
	'button_border_radius'        => '50',
	'button_style'                => 'flat',
	'button_font_family'          => 'Poppins',
	'button_font_size'            => '15',
	'button_font_bold'            => '1',
	'button_font_italic'          => '0',
	'button_font_decoration'      => '',
	'button_margin_left'          => '0',
	'button_margin_top'           => '5',
	'button_margin_right'         => '0',
	'button_margin_bottom'        => '0',
	'button_position'             => 'center',
);

$form_settings['style'] = $form_style;

$form_settings['custom_css'] = '.arm-df__form-field-wrap_rememberme{margin-top:-10px !important;margin-left:15px !important;}.arm-df__form-control-submit-btn:hover{border:0px; !important;}.arm-df__heading{margin-bottom:40px !important;}';

$form_template_settings     = $form_settings;
$forms['arm_form_settings'] = maybe_serialize( $form_template_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id       = $wpdb->insert_id;
$field_options = array(
	'id'              => 'user_login',
	'type'            => 'text',
	'default_field'   => '1',
	'label'           => esc_html__( 'Username', 'armember-membership' ),
	'placeholder'     => esc_html__( 'Username', 'armember-membership' ),
	'options'         => array(
		'minlength' => '',
		'maxlength' => '',
	),
	'required'        => '1',
	'hide_username'   => 0,
	'meta_key'        => 'user_login', // phpcs:ignore
	'blank_message'   => esc_html__( 'Username can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid username.', 'armember-membership' ),
	'prefix'          => '',
	'suffix'          => '',
	'ref_field_id'    => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 1,
	'arm_form_field_slug'         => 'user_login',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'              => 'user_pass',
	'label'           => esc_html__( 'Password', 'armember-membership' ),
	'placeholder'     => esc_html__( 'Password', 'armember-membership' ),
	'type'            => 'password',
	'default_field'   => '1',
	'options'         => array(
		'strength_meter'  => '0',
		'strong_password' => '0',
		'minlength'       => '1',
		'maxlength'       => '0',
		'special'         => '0',
		'numeric'         => '0',
		'uppercase'       => '0',
		'lowercase'       => '0',
	),
	'meta_key'        => 'user_pass', // phpcs:ignore
	'required'        => '1',
	'blank_message'   => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid password', 'armember-membership' ),
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => 'user_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'            => 'rememberme',
	'type'          => 'rememberme',
	'default_field' => '1',
	'label'         => esc_html__( 'Remember me', 'armember-membership' ),
	'meta_key'      => 'rememberme', // phpcs:ignore
	'ref_field_id'  => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 3,
	'arm_form_field_slug'         => 'rememberme',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'            => 'submit',
	'type'          => 'submit',
	'default_field' => '1',
	'label'         => 'LOGIN',
	'meta_key'      => '', // phpcs:ignore
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 4,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );
unset( $forms );
unset( $form_id );

/* Login Form Template End */

/* Forgot Password Form Start */
$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Forgot Password', 'armember-membership' );
$forms['arm_form_title']        = esc_html__( 'Forgot Password', 'armember-membership' );
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-forgot-password-3';
$forms['arm_set_name']          = esc_html__( 'Template 3', 'armember-membership' );
$forms['arm_is_default']        = '1';
$forms['arm_is_template']       = '1';
$forms['arm_ref_template']      = 3;
$forms['arm_set_id']            = '-3';
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );


$form_template_settings['redirect_type'] = 'message';

$form_template_settings['description'] = esc_html__( 'Please enter your email address or username below.', 'armember-membership' );

$forms['arm_form_settings'] = maybe_serialize( $form_template_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id = $wpdb->insert_id;

$field_options = array(
	'id'              => 'user_login',
	'type'            => 'text',
	'default_field'   => '1',
	'label'           => esc_html__( 'Username', 'armember-membership' ),
	'placeholder'     => esc_html__( 'Username', 'armember-membership' ),
	'options'         => array(
		'minlength' => '',
		'maxlength' => '',
	),
	'required'        => '1',
	'hide_username'   => 0,
	'meta_key'        => 'user_login', // phpcs:ignore
	'blank_message'   => esc_html__( 'Username can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid username.', 'armember-membership' ),
	'prefix'          => '',
	'suffix'          => '',
	'ref_field_id'    => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 1,
	'arm_form_field_slug'         => 'user_login',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'            => 'submit',
	'type'          => 'submit',
	'default_field' => '1',
	'label'         => 'Submit',
	'meta_key'      => '', // phpcs:ignore
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );
unset( $forms );
unset( $form_id );

/* Forgot Password Form End */

/* Change Password Form Start */
$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Change Password', 'armember-membership' );
$forms['arm_form_title']        = esc_html__( 'Change Password', 'armember-membership' );
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-change-password-3';
$forms['arm_set_name']          = esc_html__( 'Template 3', 'armember-membership' );
$forms['arm_is_default']        = '1';
$forms['arm_is_template']       = '1';
$forms['arm_ref_template']      = 3;
$forms['arm_set_id']            = '-3';
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );

$form_template_settings['redirect_type'] = 'message';
$form_template_settings['message']       = esc_html__( 'Your password changed successfully.', 'armember-membership' );


$forms['arm_form_settings'] = maybe_serialize( $form_template_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'current_user_pass',
    'type' => 'current_user_pass',
    'default_field' => '1',
    'label' => esc_html__('Current Password', 'armember-membership'),
    'placeholder' => esc_html__('Current Password', 'armember-membership'),
    'options' => array(
        'minlength' => '0',
        'maxlength' => '',
        'strength_meter' => '0',
        'special' => '0',
        'numeric' => '0',
        'uppercase' => '0',
        'lowercase' => '0'
    ),
    'required' => '1',
    'meta_key' => 'current_user_pass', // phpcs:ignore
    'blank_message' => esc_html__('Password can not be left blank.', 'armember-membership'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'current_user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => current_time( 'mysql' )
);

$wpdb->insert($ARMemberLite->tbl_arm_form_field, $form_field_data); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_field_id = $wpdb->insert_id;
unset($form_field_data);
unset($field_options);

$field_options = array(
	'id'            => 'user_pass',
	'type'          => 'password',
	'default_field' => '1',
	'label'         => esc_html__( 'New Password', 'armember-membership' ),
	'placeholder'   => esc_html__( 'New Password', 'armember-membership' ),
	'options'       => array(
		'minlength'      => '6',
		'maxlength'      => '',
		'strength_meter' => '1',
		'special'        => '1',
		'numeric'        => '1',
		'uppercase'      => '1',
		'lowercase'      => '1',
	),
	'required'      => '1',
	'meta_key'      => 'user_pass', // phpcs:ignore
	'blank_message' => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
	'prefix'        => '',
	'suffix'        => '',
	'ref_field_id'  => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => 'user_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_field_id = $wpdb->insert_id;
unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'              => 'repeat_pass',
	'type'            => 'repeat_pass',
	'default_field'   => '1',
	'label'           => esc_html__( 'Confirm Password', 'armember-membership' ),
	'placeholder'     => esc_html__( 'Confirm Password', 'armember-membership' ),
	'required'        => '1',
	'meta_key'        => 'repeat_pass', // phpcs:ignore
	'blank_message'   => esc_html__( 'Confirm Password can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Passwords don\'t match.', 'armember-membership' ),
	'prefix'          => '',
	'suffix'          => '',
	'ref_field_id'    => $form_field_id,
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 3,
	'arm_form_field_slug'         => 'repeat_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);
$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_field_id = $wpdb->insert_id;
unset( $form_field_data );
unset( $field_options );
unset( $form_field_id );

$field_options = array(
	'id'            => 'submit',
	'type'          => 'submit',
	'default_field' => '1',
	'label'         => 'Submit',
	'meta_key'      => '', // phpcs:ignore
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 4,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );
unset( $forms );
unset( $form_id );
unset( $form_template_settings );

/* Third Set End */

/*Fourth set Start */

$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Template 4', 'armember-membership' );
$forms['arm_form_title']        = esc_html__( 'Please Signup', 'armember-membership' );
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-registration-4';
$forms['arm_set_name']          = esc_html__( 'Template 4', 'armember-membership' );
$forms['arm_is_default']        = 1;
$forms['arm_is_template']       = 1;
$forms['arm_ref_template']      = 4;
$forms['arm_set_id']            = 0;
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );

$form_settings = array(
	'redirect_type' => 'page',
	'redirect_page' => $reg_redirect_id,
	'redirect_url'  => '',
	'auto_login'    => '1',
	'style'         => array(
		'form_layout'                => 'iconic',
		'form_width'                 => '550',
		'form_width_type'            => 'px',
		'form_border_width'          => '2',
		'form_border_radius'         => '12',
		'form_border_style'          => 'solid',
		'form_padding_left'          => '30',
		'form_padding_top'           => '40',
		'form_padding_bottom'        => '40',
		'form_padding_right'         => '30',
		'form_position'              => 'left',
		'form_bg'                    => '',
		'form_title_font_family'     => 'Poppins',
		'form_title_font_size'       => '24',
		'form_title_font_bold'       => '1',
		'form_title_font_italic'     => '0',
		'form_title_font_decoration' => '',
		'form_title_position'        => 'center',
		'validation_position'        => 'bottom',
		'color_scheme'               => 'green',
		'lable_font_color'           => '#131a15',
		'field_font_color'           => '#242424',
		'field_border_color'         => '#e6e6e6',
		'field_focus_color'          => '#27c24c',
		'field_bg_color'             => '#f0f0f0',
		'button_back_color'          => '#27c24c',
		'button_font_color'          => '#fcfcfc',
		'button_hover_color'         => '#29cc50',
		'button_hover_font_color'    => '#ffffff',
		'form_title_font_color'      => '#131a15',
		'form_bg_color'              => '#ffffff',
		'form_border_color'          => '#e6e7f5',
		'prefix_suffix_color'        => '#997a88',
		'error_font_color'           => '#ffffff',
		'error_field_border_color'   => '#f05050',
		'error_field_bg_color'       => '#e6594d',
		'login_link_font_color'      => '#27c24c',
		'register_link_font_color'   => '#27c24c',
		'field_width'                => '100',
		'field_width_type'           => '%',
		'field_height'               => '44',
		'field_spacing'              => '8',
		'field_border_width'         => '1',
		'field_border_radius'        => '6',
		'field_border_style'         => 'solid',
		'field_font_family'          => 'Poppins',
		'field_font_size'            => '15',
		'field_font_bold'            => '0',
		'field_font_italic'          => '0',
		'field_font_decoration'      => '',
		'field_position'             => 'left',
		'rtl'                        => '0',
		'label_width'                => '250',
		'label_width_type'           => 'px',
		'label_position'             => 'block',
		'label_align'                => 'left',
		'label_hide'                 => '1',
		'label_font_family'          => 'Poppins',
		'label_font_size'            => '14',
		'description_font_size'      => '14',
		'label_font_bold'            => '0',
		'label_font_italic'          => '0',
		'label_font_decoration'      => '',
		'button_width'               => '360',
		'button_width_type'          => 'px',
		'button_height'              => '44',
		'button_height_type'         => 'px',
		'button_border_radius'       => '6',
		'button_style'               => 'reverse_border',
		'button_font_family'         => 'Poppins',
		'button_font_size'           => '15',
		'button_font_bold'           => '1',
		'button_font_italic'         => '0',
		'button_font_decoration'     => '',
		'button_margin_left'         => '0',
		'button_margin_top'          => '10',
		'button_margin_right'        => '0',
		'button_margin_bottom'       => '0',
		'button_position'            => 'center',
	),
);

$forms['arm_form_settings'] = maybe_serialize( $form_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id = $wpdb->insert_id;

$field_options = array(
	'id'              => 'user_login',
	'label'           => esc_html__( 'Username', 'armember-membership' ),
	'placeholder'     => esc_html__( 'Username', 'armember-membership' ),
	'type'            => 'text',
	'meta_key'        => 'user_login', // phpcs:ignore
	'required'        => '1',
	'hide_username'   => '0',
	'blank_message'   => esc_html__( 'Username can not be left blank', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid username', 'armember-membership' ),
	'default_field'   => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 1,
	'arm_form_field_slug'         => 'user_login',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'             => 'first_name',
	'label'          => esc_html__( 'First Name', 'armember-membership' ),
	'placeholder'    => esc_html__( 'First Name', 'armember-membership' ),
	'type'           => 'text',
	'meta_key'       => 'first_name', // phpcs:ignore
	'required'       => '1',
	'hide_firstname' => '0',
	'blank_message'  => esc_html__( 'First Name can not be left blank.', 'armember-membership' ),
	'default_field'  => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => 'first_name',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'            => 'last_name',
	'label'         => esc_html__( 'Last Name', 'armember-membership' ),
	'placeholder'   => esc_html__( 'Last Name', 'armember-membership' ),
	'type'          => 'text',
 	'meta_key'      => 'last_name', // phpcs:ignore
	'required'      => '1',
	'hide_lastname' => '0',
	'blank_message' => esc_html__( 'Last Name can not be left blank.', 'armember-membership' ),
	'default_field' => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 3,
	'arm_form_field_slug'         => 'last_name',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'              => 'user_email',
	'label'           => esc_html__( 'Email Address', 'armember-membership' ),
	'placeholder'     => esc_html__( 'Email Address', 'armember-membership' ),
	'type'            => 'email',
	'meta_key'        => 'user_email', // phpcs:ignore
	'required'        => '1',
	'blank_message'   => esc_html__( 'Email Address can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid email address.', 'armember-membership' ),
	'default_field'   => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 4,
	'arm_form_field_slug'         => 'user_email',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'              => 'user_pass',
	'label'           => esc_html__( 'Password', 'armember-membership' ),
	'placeholder'     => esc_html__( 'Password', 'armember-membership' ),
	'type'            => 'password',
	'options'         => array(
		'strength_meter'  => '1',
		'strong_password' => '0',
		'minlength'       => '6',
		'maxlength'       => '',
		'special'         => '1',
		'numeric'         => '1',
		'uppercase'       => '1',
		'lowercase'       => '1',
	),
	'meta_key'        => 'user_pass', // phpcs:ignore
	'required'        => '1',
	'blank_message'   => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid password.', 'armember-membership' ),
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 5,
	'arm_form_field_slug'         => 'user_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'            => 'submit',
	'label'         => esc_html__( 'Submit', 'armember-membership' ),
	'type'          => 'submit',
	'default_field' => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 6,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );
unset( $form_id );
unset( $forms );


/* Login Form Template Start */
$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Please Login', 'armember-membership' );
$forms['arm_form_title']        = esc_html__( 'Please Login', 'armember-membership' );
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-login-4';
$forms['arm_set_name']          = esc_html__( 'Template 4', 'armember-membership' );
$forms['arm_is_default']        = '1';
$forms['arm_is_template']       = '1';
$forms['arm_ref_template']      = 4;
$forms['arm_set_id']            = '-4';
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );

$form_template_settings                          = array();
$form_settings                                   = array();
$form_settings['display_direction']              = 'vertical';
$form_settings['redirect_type']                  = 'page';
$form_settings['redirect_page']                  = $login_redirect_id;
$form_settings['redirect_url']                   = '';
$form_settings['show_rememberme']                = '0';
$form_settings['show_registration_link']         = '1';
$form_settings['registration_link_label']        = 'Create User';
$form_settings['registration_link_type']         = 'page';
$form_settings['registration_link_type_modal']   = '1';
$form_settings['registration_link_type_page']    = $register_page_id;
$form_settings['show_forgot_password_link']      = '1';
$form_settings['forgot_password_link_label']     = 'Forgot Password';
$form_settings['forgot_password_link_type']      = 'modal';
$form_settings['forgot_password_link_type_page'] = $forgot_password_page_id;
$form_settings['forgot_password_link_margin']['bottom'] = '0';
$form_settings['forgot_password_link_margin']['top']    = '0';
$form_settings['forgot_password_link_margin']['left']   = '0';
$form_settings['forgot_password_link_margin']['right']  = '0';
$form_settings['registration_link_margin']['top']       = '-40';
$form_settings['registration_link_margin']['bottom']    = '0';
$form_settings['registration_link_margin']['left']      = '280';
$form_settings['registration_link_margin']['right']     = '0';

$form_style = array(
	'social_btn_position'         => 'bottom',
	'social_btn_type'             => 'horizontal',
	'social_btn_align'            => 'center',
	'enable_social_btn_separator' => '1',
	'social_btn_separator'        => '<center>OR</center>',
	'form_layout'                 => 'iconic',
	'form_width'                  => '550',
	'form_width_type'             => 'px',
	'form_border_width'           => '2',
	'form_border_radius'          => '12',
	'form_border_style'           => 'solid',
	'form_padding_left'           => '80',
	'form_padding_top'            => '40',
	'form_padding_right'          => '80',
	'form_padding_bottom'         => '40',
	'form_position'               => 'left',
	'form_bg'                     => '',
	'form_title_font_family'      => 'Poppins',
	'form_title_font_size'        => '24',
	'form_title_font_bold'        => '1',
	'form_title_font_italic'      => '0',
	'form_title_font_decoration'  => '',
	'form_title_position'         => 'center',
	'validation_position'         => 'bottom',
	'color_scheme'                => 'green',
	'lable_font_color'            => '#131a15',
	'field_font_color'            => '#242424',
	'field_border_color'          => '#e6e6e6',
	'field_focus_color'           => '#27c24c',
	'field_bg_color'              => '#f0f0f0',
	'button_back_color'           => '#27c24c',
	'button_font_color'           => '#fcfcfc',
	'button_hover_color'          => '#29cc50',
	'button_hover_font_color'     => '#ffffff',
	'form_title_font_color'       => '#131a15',
	'form_bg_color'               => '#ffffff',
	'form_border_color'           => '#e6e7f5',
	'prefix_suffix_color'         => '#997a88',
	'error_font_color'            => '#ffffff',
	'error_field_border_color'    => '#f05050',
	'error_field_bg_color'        => '#e6594d',
	'login_link_font_color'       => '#27c24c',
	'register_link_font_color'    => '#27c24c',
	'field_width'                 => '100',
	'field_width_type'            => '%',
	'field_height'                => '44',
	'field_spacing'               => '8',
	'field_border_width'          => '1',
	'field_border_radius'         => '6',
	'field_border_style'          => 'solid',
	'field_font_family'           => 'Poppins',
	'field_font_size'             => '15',
	'field_font_bold'             => '0',
	'field_font_italic'           => '0',
	'field_font_decoration'       => '',
	'field_position'              => 'center',
	'rtl'                         => '0',
	'label_width'                 => '250',
	'label_width_type'            => 'px',
	'label_position'              => 'block',
	'label_align'                 => 'left',
	'label_hide'                  => '1',
	'label_font_family'           => 'Poppins',
	'label_font_size'             => '14',
	'description_font_size'       => '14',
	'label_font_bold'             => '0',
	'label_font_italic'           => '0',
	'label_font_decoration'       => '',
	'button_width'                => '360',
	'button_width_type'           => 'px',
	'button_height'               => '44',
	'button_height_type'          => 'px',
	'button_border_radius'        => '6',
	'button_style'                => 'reverse_border',
	'button_font_family'          => 'Poppins',
	'button_font_size'            => '15',
	'button_font_bold'            => '1',
	'button_font_italic'          => '0',
	'button_font_decoration'      => '',
	'button_margin_left'          => '0',
	'button_margin_top'           => '10',
	'button_margin_right'         => '0',
	'button_margin_bottom'        => '0',
	'button_position'             => 'center',
);

$form_custom_style = '.arm_editor_form_fileds_wrapper .arm_login_links_wrapper, .arm_login_links_wrapper{ width:auto !important ; }';

$form_settings['style']      = $form_style;
$form_settings['custom_css'] = $form_custom_style;

$form_template_settings     = $form_settings;
$forms['arm_form_settings'] = maybe_serialize( $form_template_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id       = $wpdb->insert_id;
$field_options = array(
	'id'              => 'user_login',
	'type'            => 'text',
	'default_field'   => '1',
	'label'           => esc_html__( 'Username', 'armember-membership' ),
	'placeholder'     => esc_html__( 'Username', 'armember-membership' ),
	'options'         => array(
		'minlength' => '',
		'maxlength' => '',
	),
	'required'        => '1',
	'meta_key'        => 'user_login', // phpcs:ignore
	'hide_username'   => 0,
	'blank_message'   => esc_html__( 'Username can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid username.', 'armember-membership' ),
	'prefix'          => '',
	'suffix'          => '',
	'ref_field_id'    => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 1,
	'arm_form_field_slug'         => 'user_login',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'              => 'user_pass',
	'label'           => esc_html__( 'Password', 'armember-membership' ),
	'placeholder'     => esc_html__( 'Password', 'armember-membership' ),
	'type'            => 'password',
	'default_field'   => '1',
	'options'         => array(
		'strength_meter'  => '0',
		'strong_password' => '0',
		'minlength'       => '1',
		'maxlength'       => '0',
		'special'         => '0',
		'numeric'         => '0',
		'uppercase'       => '0',
		'lowercase'       => '0',
	),
	'meta_key'        => 'user_pass', // phpcs:ignore
	'required'        => '1',
	'blank_message'   => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid password', 'armember-membership' ),
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => 'user_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'            => 'rememberme',
	'type'          => 'rememberme',
	'default_field' => '1',
	'default_val'   => 'forever',
	'label'         => esc_html__( 'Remember me', 'armember-membership' ),
	'meta_key'      => 'rememberme', // phpcs:ignore
	'ref_field_id'  => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 3,
	'arm_form_field_slug'         => 'rememberme',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'            => 'submit',
	'type'          => 'submit',
	'default_field' => '1',
	'label'         => 'LOGIN',
	'meta_key'      => '', // phpcs:ignore
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 4,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );
unset( $forms );
unset( $form_id );

/* Login Form Template End */

/* Forgot Password Form Start */
$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Forgot Password', 'armember-membership' );
$forms['arm_form_title']        = esc_html__( 'Forgot Password', 'armember-membership' );
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-forgot-password-4';
$forms['arm_set_name']          = esc_html__( 'Template 4', 'armember-membership' );
$forms['arm_is_default']        = '1';
$forms['arm_is_template']       = '1';
$forms['arm_ref_template']      = 4;
$forms['arm_set_id']            = '-4';
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );


$form_template_settings['redirect_type'] = 'message';

$form_template_settings['description'] = '<center>'.esc_html__( 'Please enter your email address or username below', 'armember-membership' ).'</center>';

$forms['arm_form_settings'] = maybe_serialize( $form_template_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id = $wpdb->insert_id;

$field_options = array(
	'id'              => 'user_login',
	'type'            => 'text',
	'default_field'   => '1',
	'label'           => esc_html__( 'Username', 'armember-membership' ),
	'placeholder'     => esc_html__( 'Username', 'armember-membership' ),
	'options'         => array(
		'minlength' => '',
		'maxlength' => '',
	),
	'required'        => '1',
	'hide_username'   => 0,
	'meta_key'        => 'user_login', // phpcs:ignore
	'blank_message'   => esc_html__( 'Username can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid username.', 'armember-membership' ),
	'prefix'          => '',
	'suffix'          => '',
	'ref_field_id'    => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 1,
	'arm_form_field_slug'         => 'user_login',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'            => 'submit',
	'type'          => 'submit',
	'default_field' => '1',
	'label'         => 'Submit',
	'meta_key'      => '', // phpcs:ignore
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );
unset( $forms );
unset( $form_id );

/* Forgot Password Form End */

/* Change Password Form Start */
$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Change Password', 'armember-membership' );
$forms['arm_form_title']        = esc_html__( 'Change Password', 'armember-membership' );
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-change-password-4';
$forms['arm_set_name']          = esc_html__( 'Template 4', 'armember-membership' );
$forms['arm_is_default']        = '1';
$forms['arm_is_template']       = '1';
$forms['arm_ref_template']      = 4;
$forms['arm_set_id']            = '-4';
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );

$form_template_settings['redirect_type'] = 'message';
$form_template_settings['message']       = esc_html__( 'Your password changed successfully.', 'armember-membership' );


$forms['arm_form_settings'] = maybe_serialize( $form_template_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'current_user_pass',
    'type' => 'current_user_pass',
    'default_field' => '1',
    'label' => esc_html__('Current Password', 'armember-membership'),
    'placeholder' => esc_html__('Current Password', 'armember-membership'),
    'options' => array(
        'minlength' => '0',
        'maxlength' => '',
        'strength_meter' => '0',
        'special' => '0',
        'numeric' => '0',
        'uppercase' => '0',
        'lowercase' => '0'
    ),
    'required' => '1',
    'meta_key' => 'current_user_pass', // phpcs:ignore
    'blank_message' => esc_html__('Password can not be left blank.', 'armember-membership'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'current_user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => current_time( 'mysql' )
);

$wpdb->insert($ARMemberLite->tbl_arm_form_field, $form_field_data); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_field_id = $wpdb->insert_id;
unset($form_field_data);
unset($field_options);

$field_options = array(
	'id'            => 'user_pass',
	'type'          => 'password',
	'default_field' => '1',
	'label'         => esc_html__( 'New Password', 'armember-membership' ),
	'placeholder'   => esc_html__( 'New Password', 'armember-membership' ),
	'options'       => array(
		'minlength'      => '6',
		'maxlength'      => '',
		'strength_meter' => '1',
		'special'        => '1',
		'numeric'        => '1',
		'uppercase'      => '1',
		'lowercase'      => '1',
	),
	'required'      => '1',
	'meta_key'      => 'user_pass', // phpcs:ignore
	'blank_message' => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
	'prefix'        => '',
	'suffix'        => '',
	'ref_field_id'  => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => 'user_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_field_id = $wpdb->insert_id;
unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'              => 'repeat_pass',
	'type'            => 'repeat_pass',
	'default_field'   => '1',
	'label'           => esc_html__( 'Confirm Password', 'armember-membership' ),
	'placeholder'     => esc_html__( 'Confirm Password', 'armember-membership' ),
	'required'        => '1',
	'meta_key'        => 'repeat_pass', // phpcs:ignore
	'blank_message'   => esc_html__( 'Confirm Password can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Passwords don\'t match.', 'armember-membership' ),
	'prefix'          => '',
	'suffix'          => '',
	'ref_field_id'    => $form_field_id,
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 3,
	'arm_form_field_slug'         => 'repeat_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);
$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_field_id = $wpdb->insert_id;
unset( $form_field_data );
unset( $field_options );
unset( $form_field_id );

$field_options = array(
	'id'            => 'submit',
	'type'          => 'submit',
	'default_field' => '1',
	'label'         => 'Submit',
	'meta_key'      => '', // phpcs:ignore
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 4,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );
unset( $forms );
unset( $form_id );
unset( $form_template_settings );

/* Change Password Form End */



/*Fourth set End */

/* Fifth set Start */

$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Template 5', 'armember-membership' );
$forms['arm_form_title']        = esc_html__( 'Please Signup', 'armember-membership' );
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-registration-5';
$forms['arm_set_name']          = esc_html__( 'Template 5', 'armember-membership' );
$forms['arm_is_default']        = 1;
$forms['arm_is_template']       = 1;
$forms['arm_ref_template']      = 5;
$forms['arm_set_id']            = 0;
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );

$form_settings = array(
	'redirect_type' => 'page',
	'redirect_page' => $reg_redirect_id,
	'redirect_url'  => '',
	'auto_login'    => '1',
	'style'         => array(
		'form_layout'                => 'iconic',
		'form_width'                 => '550',
		'form_width_type'            => 'px',
		'form_border_width'          => '2',
		'form_border_radius'         => '12',
		'form_border_style'          => 'solid',
		'form_padding_left'          => '30',
		'form_padding_top'           => '40',
		'form_padding_bottom'        => '40',
		'form_padding_right'         => '30',
		'form_position'              => 'left',
		'form_bg'                    => '',
		'form_title_font_family'     => 'Poppins',
		'form_title_font_size'       => '24',
		'form_title_font_bold'       => '1',
		'form_title_font_italic'     => '0',
		'form_title_font_decoration' => '',
		'form_title_position'        => 'center',
		'validation_position'        => 'bottom',
		'color_scheme'               => 'purple',
		'lable_font_color'           => '#919191',
		'field_font_color'           => '#242424',
		'field_border_color'         => '#c7c7c7',
		'field_focus_color'          => '#6164c1',
		'field_bg_color'             => '#ffffff',
		'button_back_color'          => '#6164c1',
		'button_font_color'          => '#ffffff',
		'button_hover_color'         => '#8072cc',
		'button_hover_font_color'    => '#ffffff',
		'form_title_font_color'      => '#313131',
		'form_bg_color'              => '#ffffff',
		'form_border_color'          => '#CED4DE',
		'prefix_suffix_color'        => '#bababa',
		'error_font_color'           => '#ffffff',
		'error_field_border_color'   => '#f05050',
		'error_field_bg_color'       => '#e6594d',
		'login_link_font_color'      => '#27c24c',
		'register_link_font_color'   => '#27c24c',
		'field_width'                => '100',
		'field_width_type'           => '%',
		'field_height'               => '44',
		'field_spacing'              => '12',
		'field_border_width'         => '1',
		'field_border_radius'        => '6',
		'field_border_style'         => 'solid',
		'field_font_family'          => 'Poppins',
		'field_font_size'            => '15',
		'field_font_bold'            => '0',
		'field_font_italic'          => '0',
		'field_font_decoration'      => '',
		'field_position'             => 'left',
		'rtl'                        => '0',
		'label_width'                => '250',
		'label_width_type'           => 'px',
		'label_position'             => 'block',
		'label_align'                => 'left',
		'label_hide'                 => '0',
		'label_font_family'          => 'Poppins',
		'label_font_size'            => '14',
		'description_font_size'      => '14',
		'label_font_bold'            => '0',
		'label_font_italic'          => '0',
		'label_font_decoration'      => '',
		'button_width'               => '240',
		'button_width_type'          => 'px',
		'button_height'              => '44',
		'button_height_type'         => 'px',
		'button_border_radius'       => '6',
		'button_style'               => 'classic',
		'button_font_family'         => 'Poppins',
		'button_font_size'           => '15',
		'button_font_bold'           => '1',
		'button_font_italic'         => '0',
		'button_font_decoration'     => '',
		'button_margin_left'         => '0',
		'button_margin_top'          => '20',
		'button_margin_right'        => '0',
		'button_margin_bottom'       => '10',
		'button_position'            => 'center',
	),
);

$forms['arm_form_settings'] = maybe_serialize( $form_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id = $wpdb->insert_id;

$field_options = array(
	'id'              => 'user_login',
	'label'           => esc_html__( 'Username', 'armember-membership' ),
	'placeholder'     => '',
	'type'            => 'text',
	'meta_key'        => 'user_login', // phpcs:ignore
	'required'        => '1',
	'hide_username'   => '0',
	'blank_message'   => esc_html__( 'Username can not be left blank', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid username', 'armember-membership' ),
	'default_field'   => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 1,
	'arm_form_field_slug'         => 'user_login',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'             => 'first_name',
	'label'          => esc_html__( 'First Name', 'armember-membership' ),
	'placeholder'    => '',
	'type'           => 'text',
	'meta_key'       => 'first_name', // phpcs:ignore
	'required'       => '1',
	'hide_firstname' => '0',
	'blank_message'  => esc_html__( 'First Name can not be left blank.', 'armember-membership' ),
	'default_field'  => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => 'first_name',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'            => 'last_name',
	'label'         => esc_html__( 'Last Name', 'armember-membership' ),
	'placeholder'   => '',
	'type'          => 'text',
	'meta_key'      => 'last_name', // phpcs:ignore
	'required'      => '1',
	'hide_lastname' => '0',
	'blank_message' => esc_html__( 'Last Name can not be left blank.', 'armember-membership' ),
	'default_field' => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 3,
	'arm_form_field_slug'         => 'last_name',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'              => 'user_email',
	'label'           => esc_html__( 'Email Address', 'armember-membership' ),
	'placeholder'     => '',
	'type'            => 'email',
	'meta_key'        => 'user_email', // phpcs:ignore
	'required'        => '1',
	'blank_message'   => esc_html__( 'Email Address can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid email address.', 'armember-membership' ),
	'default_field'   => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 4,
	'arm_form_field_slug'         => 'user_email',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'              => 'user_pass',
	'label'           => esc_html__( 'Password', 'armember-membership' ),
	'placeholder'     => '',
	'type'            => 'password',
	'options'         => array(
		'strength_meter'  => '1',
		'strong_password' => '0',
		'minlength'       => '6',
		'maxlength'       => '',
		'special'         => '1',
		'numeric'         => '1',
		'uppercase'       => '1',
		'lowercase'       => '1',
	),
	'meta_key'        => 'user_pass', // phpcs:ignore
	'required'        => '1',
	'blank_message'   => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid password.', 'armember-membership' ),
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 5,
	'arm_form_field_slug'         => 'user_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );

$field_options = array(
	'id'            => 'submit',
	'label'         => esc_html__( 'Submit', 'armember-membership' ),
	'type'          => 'submit',
	'default_field' => '1',
);

$fields = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 6,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => '1',
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $fields ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $field_options );
unset( $fields );
unset( $form_id );
unset( $forms );


/* Login Form Template Start */
$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Please Login', 'armember-membership' );
$forms['arm_form_title']        = esc_html__( 'Please Login', 'armember-membership' );
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-login-5';
$forms['arm_set_name']          = esc_html__( 'Template 5', 'armember-membership' );
$forms['arm_is_default']        = '1';
$forms['arm_is_template']       = '1';
$forms['arm_ref_template']      = 5;
$forms['arm_set_id']            = '-5';
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );

$form_template_settings                          = array();
$form_settings                                   = array();
$form_settings['display_direction']              = 'vertical';
$form_settings['redirect_type']                  = 'page';
$form_settings['redirect_page']                  = $login_redirect_id;
$form_settings['redirect_url']                   = '';
$form_settings['show_rememberme']                = '0';
$form_settings['show_registration_link']         = '1';
$form_settings['registration_link_label']        = 'SIGNUP';
$form_settings['registration_link_type']         = 'page';
$form_settings['registration_link_type_modal']   = '0';
$form_settings['registration_link_type_page']    = $register_page_id;
$form_settings['show_forgot_password_link']      = '1';
$form_settings['forgot_password_link_label']     = 'Forgot Password';
$form_settings['forgot_password_link_type']      = 'modal';
$form_settings['forgot_password_link_type_page'] = $forgot_password_page_id;
$form_settings['forgot_password_link_margin']['bottom'] = '0';
$form_settings['forgot_password_link_margin']['top']    = '0';
$form_settings['forgot_password_link_margin']['left']   = '80';
$form_settings['forgot_password_link_margin']['right']  = '0';
$form_settings['registration_link_margin']['top']       = '-40';
$form_settings['registration_link_margin']['bottom']    = '0';
$form_settings['registration_link_margin']['left']      = '280';
$form_settings['registration_link_margin']['right']     = '0';

$form_style = array(
	'social_btn_position'         => 'bottom',
	'social_btn_type'             => 'horizontal',
	'social_btn_align'            => 'center',
	'enable_social_btn_separator' => '1',
	'social_btn_separator'        => '<center>OR</center>',
	'form_layout'                 => 'iconic',
	'form_width'                  => '550',
	'form_width_type'             => 'px',
	'form_border_width'           => '2',
	'form_border_radius'          => '12',
	'form_border_style'           => 'solid',
	'form_padding_left'           => '30',
	'form_padding_top'            => '40',
	'form_padding_right'          => '30',
	'form_padding_bottom'         => '40',
	'form_position'               => 'left',
	'form_bg'                     => '',
	'form_title_font_family'      => 'Poppins',
	'form_title_font_size'        => '24',
	'form_title_font_bold'        => '1',
	'form_title_font_italic'      => '0',
	'form_title_font_decoration'  => '',
	'form_title_position'         => 'center',
	'validation_position'         => 'bottom',
	'color_scheme'                => 'purple',
	'lable_font_color'            => '#919191',
	'field_font_color'            => '#242424',
	'field_border_color'          => '#c7c7c7',
	'field_focus_color'           => '#6164c1',
	'field_bg_color'              => '#ffffff',
	'button_back_color'           => '#6164c1',
	'button_font_color'           => '#ffffff',
	'button_hover_color'          => '#8072cc',
	'button_hover_font_color'     => '#ffffff',
	'form_title_font_color'       => '#313131',
	'form_bg_color'               => '#ffffff',
	'form_border_color'           => '#CED4DE',
	'prefix_suffix_color'         => '#bababa',
	'error_font_color'            => '#ffffff',
	'error_field_border_color'    => '#f05050',
	'error_field_bg_color'        => '#e6594d',
	'login_link_font_color'       => '#6164c1',
	'register_link_font_color'    => '#6164c1',
	'field_width'                 => '100',
	'field_width_type'            => '%',
	'field_height'                => '44',
	'field_spacing'               => '12',
	'field_border_width'          => '1',
	'field_border_radius'         => '6',
	'field_border_style'          => 'solid',
	'field_font_family'           => 'Poppins',
	'field_font_size'             => '15',
	'field_font_bold'             => '0',
	'field_font_italic'           => '0',
	'field_font_decoration'       => '',
	'field_position'              => 'left',
	'rtl'                         => '0',
	'label_width'                 => '250',
	'label_width_type'            => 'px',
	'label_position'              => 'block',
	'label_align'                 => 'left',
	'label_hide'                  => '0',
	'label_font_family'           => 'Poppins',
	'label_font_size'             => '14',
	'description_font_size'       => '14',
	'label_font_bold'             => '0',
	'label_font_italic'           => '0',
	'label_font_decoration'       => '',
	'button_width'                => '240',
	'button_width_type'           => 'px',
	'button_height'               => '44',
	'button_height_type'          => 'px',
	'button_border_radius'        => '6',
	'button_style'                => 'classic',
	'button_font_family'          => 'Poppins',
	'button_font_size'            => '15',
	'button_font_bold'            => '1',
	'button_font_italic'          => '0',
	'button_font_decoration'      => '',
	'button_margin_left'          => '0',
	'button_margin_top'           => '10',
	'button_margin_right'         => '0',
	'button_margin_bottom'        => '0',
	'button_position'             => 'center',
);


$form_settings['style'] = $form_style;


$form_template_settings     = $form_settings;
$forms['arm_form_settings'] = maybe_serialize( $form_template_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id       = $wpdb->insert_id;
$field_options = array(
	'id'              => 'user_login',
	'type'            => 'text',
	'default_field'   => '1',
	'label'           => esc_html__( 'Username', 'armember-membership' ),
	'placeholder'     => '',
	'options'         => array(
		'minlength' => '',
		'maxlength' => '',
	),
	'required'        => '1',
	'hide_username'   => 0,
	'meta_key'        => 'user_login', // phpcs:ignore
	'blank_message'   => esc_html__( 'Username can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid username.', 'armember-membership' ),
	'prefix'          => '',
	'suffix'          => '',
	'ref_field_id'    => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 1,
	'arm_form_field_slug'         => 'user_login',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'              => 'user_pass',
	'label'           => esc_html__( 'Password', 'armember-membership' ),
	'placeholder'     => '',
	'type'            => 'password',
	'default_field'   => '1',
	'options'         => array(
		'strength_meter'  => '0',
		'strong_password' => '0',
		'minlength'       => '1',
		'maxlength'       => '0',
		'special'         => '0',
		'numeric'         => '0',
		'uppercase'       => '0',
		'lowercase'       => '0',
	),
	'meta_key'        => 'user_pass', // phpcs:ignore
	'required'        => '1',
	'blank_message'   => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid password', 'armember-membership' ),
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => 'user_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'            => 'rememberme',
	'type'          => 'rememberme',
	'default_field' => '1',
	'default_val'   => 'forever',
	'label'         => esc_html__( 'Remember me', 'armember-membership' ),
	'meta_key'      => 'rememberme', // phpcs:ignore
	'ref_field_id'  => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 3,
	'arm_form_field_slug'         => 'rememberme',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'            => 'submit',
	'type'          => 'submit',
	'default_field' => '1',
	'label'         => 'LOGIN',
	'meta_key'      => '', // phpcs:ignore
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 4,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );
unset( $forms );
unset( $form_id );

/* Login Form Template End */

/* Forgot Password Form Start */
$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Forgot Password', 'armember-membership' );
$forms['arm_form_title']        = esc_html__( 'Forgot Password', 'armember-membership' );
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-forgot-password-5';
$forms['arm_set_name']          = esc_html__( 'Template 5', 'armember-membership' );
$forms['arm_is_default']        = '1';
$forms['arm_is_template']       = '1';
$forms['arm_ref_template']      = 5;
$forms['arm_set_id']            = '-5';
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );


$form_template_settings['redirect_type'] = 'message';

$form_template_settings['description'] = '<center>'.esc_html__( 'Please enter your email address or username below', 'armember-membership' ).'</center>';

$forms['arm_form_settings'] = maybe_serialize( $form_template_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id = $wpdb->insert_id;

$field_options = array(
	'id'              => 'user_login',
	'type'            => 'text',
	'default_field'   => '1',
	'label'           => esc_html__( 'Username', 'armember-membership' ),
	'placeholder'     => '',
	'options'         => array(
		'minlength' => '',
		'maxlength' => '',
	),
	'required'        => '1',
	'hide_username'   => 0,
	'meta_key'        => 'user_login', // phpcs:ignore
	'blank_message'   => esc_html__( 'Username can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Please enter valid username.', 'armember-membership' ),
	'prefix'          => '',
	'suffix'          => '',
	'ref_field_id'    => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 1,
	'arm_form_field_slug'         => 'user_login',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'            => 'submit',
	'type'          => 'submit',
	'default_field' => '1',
	'label'         => 'Submit',
	'meta_key'      => '', // phpcs:ignore
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );
unset( $forms );
unset( $form_id );

/* Forgot Password Form End */

/* Change Password Form Start */
$forms                          = array();
$forms['arm_form_label']        = esc_html__( 'Change Password', 'armember-membership' );
$forms['arm_form_title']        = esc_html__( 'Change Password', 'armember-membership' );
$forms['arm_form_type']         = 'template';
$forms['arm_form_slug']         = 'template-change-password-5';
$forms['arm_set_name']          = esc_html__( 'Template 5', 'armember-membership' );
$forms['arm_is_default']        = '1';
$forms['arm_is_template']       = '1';
$forms['arm_ref_template']      = 5;
$forms['arm_set_id']            = '-5';
$forms['arm_form_updated_date'] = current_time( 'mysql' );
$forms['arm_form_created_date'] = current_time( 'mysql' );

$form_template_settings['redirect_type'] = 'message';
$form_template_settings['message']       = esc_html__( 'Your password changed successfully.', 'armember-membership' );


$forms['arm_form_settings'] = maybe_serialize( $form_template_settings );

$wpdb->insert( $ARMemberLite->tbl_arm_forms, $forms ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_id = $wpdb->insert_id;

$field_options = array(
    'id' => 'current_user_pass',
    'type' => 'current_user_pass',
    'default_field' => '1',
    'label' => esc_html__('Current Password', 'armember-membership'),
    'placeholder' => esc_html__('Current Password', 'armember-membership'),
    'options' => array(
        'minlength' => '0',
        'maxlength' => '',
        'strength_meter' => '0',
        'special' => '0',
        'numeric' => '0',
        'uppercase' => '0',
        'lowercase' => '0'
    ),
    'required' => '1',
    'meta_key' => 'current_user_pass', // phpcs:ignore
    'blank_message' => esc_html__('Password can not be left blank.', 'armember-membership'),
    'prefix' => '',
    'suffix' => '',
    'ref_field_id' => '0'
);

$form_field_data = array(
    'arm_form_field_form_id' => $form_id,
    'arm_form_field_order' => 1,
    'arm_form_field_slug' => 'current_user_pass',
    'arm_form_field_option' => maybe_serialize($field_options),
    'arm_form_field_status' => 1,
    'arm_form_field_created_date' => current_time( 'mysql' )
);

$wpdb->insert($ARMemberLite->tbl_arm_form_field, $form_field_data); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_field_id = $wpdb->insert_id;
unset($form_field_data);
unset($field_options);

$field_options = array(
	'id'            => 'user_pass',
	'type'          => 'password',
	'default_field' => '1',
	'label'         => esc_html__( 'New Password', 'armember-membership' ),
	'placeholder'   => '',
	'options'       => array(
		'minlength'      => '6',
		'maxlength'      => '',
		'strength_meter' => '1',
		'special'        => '1',
		'numeric'        => '1',
		'uppercase'      => '1',
		'lowercase'      => '1',
	),
	'required'      => '1',
	'meta_key'      => 'user_pass', // phpcs:ignore
	'blank_message' => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
	'prefix'        => '',
	'suffix'        => '',
	'ref_field_id'  => '0',
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 2,
	'arm_form_field_slug'         => 'user_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_field_id = $wpdb->insert_id;
unset( $form_field_data );
unset( $field_options );

$field_options = array(
	'id'              => 'repeat_pass',
	'type'            => 'repeat_pass',
	'default_field'   => '1',
	'label'           => esc_html__( 'Confirm Password', 'armember-membership' ),
	'placeholder'     => '',
	'required'        => '1',
	'meta_key'        => 'repeat_pass', // phpcs:ignore
	'blank_message'   => esc_html__( 'Confirm Password can not be left blank.', 'armember-membership' ),
	'invalid_message' => esc_html__( 'Passwords don\'t match.', 'armember-membership' ),
	'prefix'          => '',
	'suffix'          => '',
	'ref_field_id'    => $form_field_id,
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 3,
	'arm_form_field_slug'         => 'repeat_pass',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);
$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$form_field_id = $wpdb->insert_id;
unset( $form_field_data );
unset( $field_options );
unset( $form_field_id );

$field_options = array(
	'id'            => 'submit',
	'type'          => 'submit',
	'default_field' => '1',
	'label'         => 'Submit',
	'meta_key'      => '', // phpcs:ignore
);

$form_field_data = array(
	'arm_form_field_form_id'      => $form_id,
	'arm_form_field_order'        => 4,
	'arm_form_field_slug'         => '',
	'arm_form_field_option'       => maybe_serialize( $field_options ),
	'arm_form_field_status'       => 1,
	'arm_form_field_created_date' => current_time( 'mysql' ),
);

$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery

unset( $form_field_data );
unset( $field_options );
unset( $forms );
unset( $form_id );
unset( $form_template_settings );

/* Change Password Form End */



/*Fourth set End */
