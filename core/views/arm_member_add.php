<?php
global $wpdb, $armPrimaryStatus, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_subscription_plans, $arm_social_feature,$arm_email_settings,$arm_lite_members_activity;
/**
 * Process Submited Form.
 */
$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend'), $_POST); //phpcs:ignore
$posted_data['user_pass'] = !empty($_POST['user_pass']) ? $_POST['user_pass'] : '';
$posted_data['repeat_pass'] = !empty($_POST['repeat_pass']) ? $_POST['repeat_pass'] : '';

$arm_common_date_format = 'm/d/Y';
if($ARMemberLite->is_arm_pro_active)
{
	$arm_common_date_format = $arm_global_settings->arm_check_common_date_format(get_option('date_format'));
}


if ( isset( $posted_data['action'] ) && in_array( $posted_data['action'], array( 'add_member', 'update_member' ) ) ) {
	do_action( 'arm_admin_save_member_details', $posted_data );
}
if(isset($_SESSION['arm_file_upload_arr'])){
    unset($_SESSION['arm_file_upload_arr']);
}
$arm_default_form_id  = 101;
$arm_member_form_id = 101;
if (isset($_GET['arm_form_id'])) { //phpcs:ignore
   $arm_member_form_id= intval($_GET['arm_form_id']); //phpcs:ignore
   if (!is_numeric($arm_member_form_id)) {
       $arm_member_form_id=$arm_default_form_id;
   } 
}
$arm_suffix_icon_pass = '<span class="arm_visible_password_admin arm_editor_suffix"><i class="armfa armfa-eye"></i></span>';

$user_roles             = $arm_global_settings->arm_get_all_roles();
$all_active_plans       = $arm_subscription_plans->arm_get_all_active_subscription_plans();
$dbFormFields           = $arm_member_forms->arm_get_db_form_fields( true );
$arm_default_FormFields = $arm_member_forms->arm_default_preset_user_fields();
if ( count( $arm_default_FormFields ) > 0 ) {
	foreach ( $arm_default_FormFields as $df_key => $df_field_value ) {
		if ( ! isset( $dbFormFields[ $df_key ] ) ) {
			$dbFormFields[ $df_key ] = $df_field_value;
		}
	}
	unset( $dbFormFields['social_fields'] );
}
$form_mode                      = esc_html__( 'Add New Member', 'armember-membership' );
$action                         = 'add_member';
$user_id                        = 0;
$arm_form_id                    = $arm_member_form_id;
$username                       = $useremail = $firstname = $last_name = $planID = '';
$u_roles                        = 'subscriber';
$primary_status                 = 1;
$secondary_status               = 0;
$user                           = '';
$cancel_url                     = admin_url( 'admin.php?page=' . $arm_slugs->manage_members );
$required_class                 = 0;
$planIDs                        = array();
$futurePlanIDs                  = array();
$plan_start_date                = date( $arm_common_date_format ); //phpcs:ignore
$arm_member_include_fields_keys = array('user_email', 'user_pass');

if ( isset( $posted_data['action'] ) && $posted_data['action'] == 'add_member' ) {
	$username  = ! empty( $posted_data['user_login'] ) ? $posted_data['user_login'] : '';
	$useremail = ! empty( $posted_data['user_email'] ) ? $posted_data['user_email'] : '';
	$firstname = ! empty( $posted_data['first_name'] ) ? $posted_data['first_name'] : '';
	$last_name = ! empty( $posted_data['last_name'] ) ? $posted_data['last_name'] : '';
	$u_roles   = ! empty( $posted_data['roles'] ) ? $posted_data['roles'] : 'subscriber';
	if ( ! empty( $posted_data['arm_primary_status'] ) && $posted_data['arm_primary_status'] == '1' ) {
		$primary_status = '1';
	} else {
		$primary_status = '2';
	}
	$planIDs = ! empty( $posted_data['arm_user_plan'] ) ? $posted_data['arm_user_plan'] : array();

	$planIDs = ! is_array( $planIDs ) ? array( $planIDs ) : $planIDs;
}
$planData = array();
if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit_member' && ! empty( $_GET['id'] ) ) { //phpcs:ignore
	$form_mode   = esc_html__( 'Update Member', 'armember-membership' );
	$action      = 'update_member';
	$user_id     = intval(abs( $_GET['id'] )); //phpcs:ignore
	$user        = $arm_members_class->arm_get_member_detail( $user_id );

	if( empty( $user ) ) 
	{
		wp_safe_redirect( $cancel_url );
		die();
	}

	$arm_user_roles = $user->roles;
    if((!empty($arm_user_roles) && in_array('administrator',$arm_user_roles,true)) || (is_multisite() && is_super_admin($user_id)))
    {
        wp_safe_redirect( $cancel_url );
    }
	$arm_form_id = isset( $user->arm_form_id ) ? $user->arm_form_id : 0;
	if ( empty( $arm_form_id ) ) {
		$arm_form_id = $arm_member_form_id;
	}
	$arm_form_id = apply_filters('arm_modify_member_forms_id_external',$arm_form_id);

	if ( $arm_form_id != 0 && $arm_form_id != '' ) {
		$arm_member_form_fields = $arm_member_forms->arm_get_member_forms_fields( $arm_form_id, 'all' );
        if(empty($arm_member_form_fields)){
            $arm_form_id = $arm_member_form_id;
            $arm_member_form_fields = $arm_member_forms->arm_get_member_forms_fields($arm_form_id, 'all');
        }
		
		if ( ! empty( $arm_member_form_fields ) ) {
			foreach ( $arm_member_form_fields as $fields_key => $fields_value ) {
				if(in_array($fields_value['arm_form_field_option']['type'], array('file','avatar','profile_cover'))){
                    $file_meta_key = !empty($fields_value['arm_form_field_option']['meta_key'])?$fields_value['arm_form_field_option']['meta_key']:"";
					if(!$ARMemberLite->is_arm_pro_active)
					{
						$fname = $ARMemberLite->arm_get_basename($user->$file_meta_key);
						$arm_lite_members_activity->session_for_file_handle($file_meta_key,$fname);
					}
					else{
						global $arm_members_activity;
						$file_name = explode(",",$user->$file_meta_key);
						foreach ($file_name as $fname) {
							$fname = $ARMember->arm_get_basename($fname);
							if($fields_value['arm_form_field_option']['type']=="file"){
							$arm_members_activity->session_for_file_handle($file_meta_key,$fname,1);
							}else{
								$arm_members_activity->session_for_file_handle($file_meta_key,$fname);
							}

						}
					}
                    
                }
				$arm_member_form_field_slug = $fields_value['arm_form_field_slug'];
				if ( $arm_member_form_field_slug != '' ) {
					if ( ! in_array( $fields_value['arm_form_field_option']['type'], array( 'section', 'html', 'hidden', 'submit' ,'social_fields') ) ) {
						$arm_member_include_fields_keys[ $arm_member_form_field_slug ] = $arm_member_form_field_slug;
						$dbFormFields[ $arm_member_form_field_slug ]['label']          = $fields_value['arm_form_field_option']['label'];
						if ( isset( $dbFormFields[ $arm_member_form_field_slug ]['options'] ) && isset( $fields_value['arm_form_field_option']['options'] ) ) {
							$dbFormFields[ $arm_member_form_field_slug ]['options'] = $fields_value['arm_form_field_option']['options'];
						}
                        if( !empty( isset($fields_value['arm_form_field_option']['default_val']) ) && !empty($fields_value['arm_form_field_option']['type']) && ($fields_value['arm_form_field_option']['type']=='radio' || $fields_value['arm_form_field_option']['type']=='checkbox'))
                        {
                            $dbFormFields[$arm_member_form_field_slug]['default_val'] = $fields_value['arm_form_field_option']['default_val'];
                        }
						$dbFormFields['display_member_fields'][ $arm_member_form_field_slug ] = $arm_member_form_field_slug;
					}
				}
			}
		}
		if ( isset( $dbFormFields['display_member_fields'] ) && count( $dbFormFields['display_member_fields'] ) ) {
			$dbFormFields = array_merge( array_flip( $dbFormFields['display_member_fields'] ), $dbFormFields );
			unset( $dbFormFields['display_member_fields'] );
		}
		if ( isset( $dbFormFields['user_pass'] ) && isset( $dbFormFields['user_pass']['required'] ) ) {
			$dbFormFields['user_pass']['required'] = 0;
		}
	}
	$required_class = 1;
	if ( ! empty( $user ) ) {

		$arm_all_user_status = arm_get_all_member_status( $user_id );
		$primary_status      = $arm_all_user_status['arm_primary_status'];
		$secondary_status    = $arm_all_user_status['arm_secondary_status'];
	}
	$planIDs  = get_user_meta( $user_id, 'arm_user_plan_ids', true );
	$planIDs  = ! empty( $planIDs ) ? $planIDs : array();
	$planID   = isset( $planIDs[0] ) ? $planIDs[0] : 0;
	$planIDs = apply_filters('arm_pro_modify_plan_ids_externally', $planIDs, $user_id);
	$planIDs = apply_filters('arm_modify_plan_ids_externally', $planIDs, $user_id);
	$planData = get_user_meta( $user_id, 'arm_user_plan_' . $planID, true );

	$plan_start_date = ( isset( $planData['arm_start_plan'] ) && ! empty( $planData['arm_start_plan'] ) ) ? date( $arm_common_date_format, $planData['arm_start_plan'] ) : date( $arm_common_date_format ); //phpcs:ignore

	$futurePlanIDs = get_user_meta( $user_id, 'arm_user_future_plan_ids', true );
	$futurePlanIDs = ! empty( $futurePlanIDs ) ? $futurePlanIDs : array();
	$futurePlanIDs = apply_filters('arm_pro_modify_future_plan_ids_externally', $futurePlanIDs, $user_id);
} else {
	$arm_member_form_fields = $arm_member_forms->arm_get_member_forms_fields( $arm_form_id, 'all' );
	if ( ! empty( $arm_member_form_fields ) ) {
		foreach ( $arm_member_form_fields as $fields_key => $fields_value ) {
			if(in_array($fields_value['arm_form_field_option']['type'], array('file','avatar','profile_cover'))){
				$file_meta_key = !empty($fields_value['arm_form_field_option']['meta_key'])?$fields_value['arm_form_field_option']['meta_key']:"";
				if($ARMemberLite->is_arm_pro_active)
				{
					global $arm_members_activity;
					$arm_members_activity->session_for_file_handle($file_meta_key,"");
				}
				else
				{
					$arm_lite_members_activity->session_for_file_handle($file_meta_key,"");
				}
			}
			$arm_member_form_field_slug = $fields_value['arm_form_field_slug'];
			if ( $arm_member_form_field_slug != '' ) {
				if ( ! in_array( $fields_value['arm_form_field_option']['type'], array( 'section', 'html', 'hidden', 'submit', 'social_fields' ) ) ) {
					$arm_member_include_fields_keys[ $arm_member_form_field_slug ]        = $arm_member_form_field_slug;
					$dbFormFields[ $arm_member_form_field_slug ]                          = $dbFormFields[ $arm_member_form_field_slug ];
					$dbFormFields[ $arm_member_form_field_slug ]['label']                 = $fields_value['arm_form_field_option']['label'];
					$dbFormFields[ $arm_member_form_field_slug ]['options']               = isset( $fields_value['arm_form_field_option']['options'] ) ? $fields_value['arm_form_field_option']['options'] : array();
					$dbFormFields['display_member_fields'][ $arm_member_form_field_slug ] = $arm_member_form_field_slug;
				}
			}
		}
	}

	if ( isset( $dbFormFields['display_member_fields'] ) && count( $dbFormFields['display_member_fields'] ) ) {
		$dbFormFields = array_merge( array_flip( $dbFormFields['display_member_fields'] ), $dbFormFields );
		unset( $dbFormFields['display_member_fields'] );
	}
}

$all_plan_ids = array();
if ( ! empty( $all_active_plans ) ) {
	foreach ( $all_active_plans as $p ) {
		$all_plan_ids[] = $p['arm_subscription_plan_id'];
	}
}

$plan_to_show = array_diff( $all_plan_ids, $planIDs );

$plan_to_show = array_diff( $plan_to_show, $futurePlanIDs );

$plansLists = '<li data-label="' . esc_html__( 'Select Plan', 'armember-membership' ) . '" data-value="">' . esc_html__( 'Select Plan', 'armember-membership' ) . '</li>';
if ( ! empty( $all_active_plans ) ) {
	foreach ( $all_active_plans as $p ) {
		$p_id = $p['arm_subscription_plan_id'];


			$plansLists .= '<li data-label="' . stripslashes( esc_attr( $p['arm_subscription_plan_name'] ) ) . '" data-value="' . esc_attr($p_id) . '">' . stripslashes( esc_attr( $p['arm_subscription_plan_name'] ) ) . '</li>';

	}
}
$formHiddenFields = '';
?>
<div class="wrap arm_page arm_add_member_page armPageContainer">
<?php
    if($ARMemberLite->is_arm_pro_active)
	{
		$arm_license_notice = '';
		echo apply_filters('arm_admin_license_notice_html',$arm_license_notice); //phpcs:ignore
	}
	?>
	<div class="content_wrapper" id="content_wrapper">
		<div class="page_title"><?php echo esc_html($form_mode); ?></div>
		<div class="armclear"></div>
		<?php
		global $arm_lite_errors;
		if($ARMemberLite->is_arm_pro_active){
			global $arm_errors;
			$errors = $arm_errors->get_error_messages();
		}
		else
		{
			$errors = $arm_lite_errors->get_error_messages();
		}
		if ( ! empty( $errors ) ) {
			foreach ( $errors as $err ) {
				echo '<div class="arm_message arm_error_message" style="display:block;">';
				echo '<div class="arm_message_text">' . esc_html($err) . '</div>';
				echo '</div>';
			}
		}
		?>
		<div class="armclear"></div>
		<div class="arm_add_edit_member_wrapper arm_member_detail_box">
			<form method="post" id="arm_add_edit_member_form" class="arm_add_edit_member_form arm_admin_form" enctype="multipart/form-data">
			<?php
				$arm_register_forms_fields = '';
				echo apply_filters('arm_register_form_fields_selection',$arm_register_forms_fields); //phpcs:ignore
			?>
				<input type="hidden" name="id" value="<?php echo intval($user_id); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr($action); ?>">
				<input type="hidden" name="form" value="<?php echo intval($arm_form_id); ?>">
				<?php if ( isset( $_GET['action'] ) && $_GET['action'] == 'new' && empty( $_GET['id'] ) ) { //phpcs:ignore?>
				<input type="hidden" name="arm_member_form_has_url" id="arm_member_form_has_url" value="<?php echo esc_url( admin_url( 'admin.php?page=arm_manage_members&action=new' ) ); //phpcs:ignore ?>">
				<?php } ?>
				<div class="arm_admin_form_content">
					<table class="form-table">
						<?php
						if($ARMemberLite->is_arm_pro_active)
						{
							$armform ='';
							$armform = apply_filters('arm_get_member_forms_filter',$armform);
						}
						else
						{
							$armform = new ARM_Form_Lite();
						}
						if ( ! empty( $arm_form_id ) && $arm_form_id != 0 ) {
							$userRegForm     = $arm_member_forms->arm_get_single_member_forms( $arm_form_id );
							$arm_exists_form = $armform->arm_is_form_exists( $arm_form_id );
							if ( $arm_exists_form ) {
								$armform->init( (object) $userRegForm );
							}
						}
						$arm_repeated_fields = array( 'repeat_email' => 'repeat_email' );
						if ( isset( $_GET['action'] ) && $_GET['action'] == 'new' && empty( $_GET['id'] ) ) { //phpcs:ignore
							if ( ! empty( $dbFormFields ) ) {
								foreach ( $dbFormFields as $meta_key => $field ) {
									$field_options = maybe_unserialize( $field );
									$field_options = apply_filters( 'arm_change_field_options', $field_options );
									$meta_key      = isset( $field_options['meta_key'] ) ? $field_options['meta_key'] : $field_options['id'];
									$field_id      = $meta_key . arm_generate_random_code();
									if ( in_array( $meta_key, $arm_member_include_fields_keys ) && ! in_array( $meta_key, array( 'section', 'roles', 'html', 'hidden', 'submit', 'repeat_email', 'social_fields' ) ) ) {
										?>
										<?php
										if ( $meta_key == 'user_pass' ) {
											$amr_confirm_pass_lbl               = '';
											$arm_repeated_fields['repeat_pass'] = 'repeat_pass';
											if ( isset( $dbFormFields['repeat_pass'] ) && isset( $dbFormFields['repeat_pass']['label'] ) ) {
												$amr_confirm_pass_lbl = $dbFormFields['repeat_pass']['label'];
											}
											$amr_user_pass_lbl = '';
											if ( isset( $dbFormFields['user_pass'] ) && isset( $dbFormFields['user_pass']['label'] ) ) {
												$amr_user_pass_lbl = $dbFormFields['user_pass']['label'];
											}
											?>
											<tr class="form-field">
												<th>
													<label for="arm_password"><?php ( ! empty( $amr_user_pass_lbl ) ) ? esc_html_e( $amr_user_pass_lbl ) : esc_html_e( 'Password', 'armember-membership' );//phpcs:ignore ?>
																					  <?php
																						if ( $required_class != 1 ) :
																							?>
														<span class="required_icon">*</span><?php endif; ?></label>
												</th>
												<td>
												<?php
													$arm_suffix_icon_pass_cls = '';
												if ( is_rtl() ) {
													$arm_suffix_icon_pass_cls = 'arm_visible_password_admin_rtl';
												}
												?>
													<input id="arm_password" autocomplete="off" class="arm_member_form_input <?php echo esc_html($arm_suffix_icon_pass_cls); ?>" name="user_pass" type="password" value="" data-msg-required="<?php esc_html_e( 'Password can not be left blank.', 'armember-membership' ); ?>" 
																																		<?php
																																		if ( $required_class != 1 ) :
																																			?>
														required<?php endif; ?>/>
													<?php echo $arm_suffix_icon_pass; //phpcs:ignore ?>
												</td>
											</tr>
											<tr class="form-field">
												<th>
													<label for="arm_repeat_pass"><?php ( ! empty( $amr_confirm_pass_lbl ) ) ? esc_html_e( $amr_confirm_pass_lbl ) : esc_html_e( 'Confirm Password', 'armember-membership' ); //phpcs:ignore ?>
																						 <?php
																							if ( $required_class != 1 ) :
																								?>
														<span class="required_icon">*</span><?php endif; ?></label>
												</th>
												<td>
													<input id="arm_repeat_pass" class="arm_member_form_input <?php echo esc_attr($arm_suffix_icon_pass_cls); ?>" name="repeat_pass" type="password" value="" data-msg-required="<?php esc_attr_e( 'Confirm Password can not be left blank.', 'armember-membership' ); ?>" 
																														<?php
																														if ( $required_class != 1 ) :
																															?>
														required<?php endif; ?>/>
													<?php echo $arm_suffix_icon_pass; //phpcs:ignore ?>
												</td>
											</tr>
										<?php } else { ?>
										<tr class="form-field">
											<th>
												<label for="<?php echo esc_attr($field_options['id']); ?>">
													<?php echo esc_html($field_options['label']); ?>
													<?php echo ( isset( $field_options['required'] ) && $field_options['required'] == 1 ) ? '<span class="required_icon">*</span>' : ''; ?>
												</label>
											</th>
											<td>
												<div class="arm_form_fields_wrapper">
													<?php
													if ( ! empty( $user ) ) {
														$field_options['value'] = $user->$meta_key;
													}
													echo $arm_member_forms->arm_member_form_get_fields_by_type( $field_options, $field_id, $arm_form_id, 'active', $armform ); //phpcs:ignore
													?>
													<div class="armclear"></div>
												</div>
											</td>
										</tr>
										<?php } ?>
										<?php
									}
								}
							}
						} else {
							?>
							<tr class="form-field form-required">
								<th>
									<label for="arm_username"><?php esc_html_e( 'Username', 'armember-membership' ); ?><span class="required_icon">*</span></label>

								</th>
								<td>
									<?php
									$disabled = '';
									if ( ! empty( $user ) ) {
										$username = $user->user_login;
										$disabled = 'disabled="disabled" ';
									}
									?>
									<input id="arm_username" class="arm_member_form_input" type="text" name="user_login" value="<?php echo esc_attr($username); ?>" <?php echo $disabled; //phpcs:ignore ?> data-msg-required="<?php esc_attr_e( 'Username can not be left blank.', 'armember-membership' ); ?>" required/>
								</td>
							</tr>
													<?php

													if ( ! empty( $dbFormFields ) ) {
														foreach ( $dbFormFields as $meta_key => $field ) {
															$field_options = maybe_unserialize( $field );
															$field_options = apply_filters( 'arm_change_field_options', $field_options );
															$meta_key      = isset( $field_options['meta_key'] ) ? $field_options['meta_key'] : $field_options['id'];
															$field_id      = $meta_key . arm_generate_random_code();
															if ( in_array( $meta_key, $arm_member_include_fields_keys ) && ! in_array( $meta_key, array( 'user_login', 'section', 'roles', 'html', 'hidden', 'submit', 'repeat_email', 'social_fields' ) ) ) {
																?>
																<?php
																if ( $meta_key == 'user_pass' ) {
																	$arm_repeated_fields['repeat_pass'] = 'repeat_pass';
																	$amr_confirm_pass_lbl               = '';
																	if ( isset( $dbFormFields['repeat_pass'] ) && isset( $dbFormFields['repeat_pass']['label'] ) ) {
																		$amr_confirm_pass_lbl = $dbFormFields['repeat_pass']['label'];
																	}
																	$amr_user_pass_lbl = '';
																	if ( isset( $dbFormFields['user_pass'] ) && isset( $dbFormFields['user_pass']['label'] ) ) {
																		$amr_user_pass_lbl = $dbFormFields['user_pass']['label'];
																	}
																	?>
											<tr class="form-field">
												<th>
													<label for="arm_password"><?php ( ! empty( $amr_user_pass_lbl ) ) ? esc_html_e( $amr_user_pass_lbl) : esc_html_e( 'Password', 'armember-membership' ); //phpcs:ignore ?>
																					  <?php
																						if ( $required_class != 1 ) :
																							?>
						<span class="required_icon">*</span><?php endif; ?></label>
												</th>
												<td>
																	<?php
																			$arm_suffix_icon_pass_cls = '';
																	if ( is_rtl() ) {
																		$arm_suffix_icon_pass_cls = 'arm_visible_password_admin_rtl';
																	}
																	?>
													<input id="arm_password" autocomplete="off" class="arm_member_form_input <?php echo esc_attr($arm_suffix_icon_pass_cls); ?>" name="user_pass" type="password" value="" data-msg-required="<?php esc_attr_e( 'Password can not be left blank.', 'armember-membership' ); ?>" 
																																		<?php
																																		if ( $required_class != 1 ) :
																																			?>
														required<?php endif; ?>/>
																	<?php echo $arm_suffix_icon_pass; //phpcs:ignore ?>
												</td>
											</tr>
											<tr class="form-field">
												<th>
													<label for="arm_repeat_pass"><?php ( ! empty( $amr_confirm_pass_lbl ) ) ? esc_html_e( $amr_confirm_pass_lbl ) : esc_html_e( 'Confirm Password', 'armember-membership' ); //phpcs:ignore?>
																						 <?php
																							if ( $required_class != 1 ) :
																								?>
														<span class="required_icon">*</span><?php endif; ?></label>
												</th>
												<td>
													<input id="arm_repeat_pass" class="arm_member_form_input <?php echo esc_attr($arm_suffix_icon_pass_cls); ?>" name="repeat_pass" type="password" value="" data-msg-required="<?php esc_attr_e( 'Confirm Password can not be left blank.', 'armember-membership' ); ?>" 
																														<?php
																														if ( $required_class != 1 ) :
																															?>
														required<?php endif; ?>/>
																	<?php echo $arm_suffix_icon_pass; //phpcs:ignore ?>
												</td>
											</tr>
										<?php } else { ?>
											<tr class="form-field">
												<th>
													<label for="<?php echo esc_attr($field_options['id']); ?>">
																	<?php echo esc_html($field_options['label']); ?>
																	<?php echo ( isset( $field_options['required'] ) && $field_options['required'] == 1 ) ? '<span class="required_icon">*</span>' : ''; ?>
													</label>
												</th>
												<td>
													<div class="arm_form_fields_wrapper">
																	<?php
																	if ( ! empty( $user ) && $meta_key != 'user_pass' ) {
																		$field_options['value'] = $user->$meta_key;
																	}
																	echo $arm_member_forms->arm_member_form_get_fields_by_type( $field_options, $field_id, $arm_form_id, 'active', $armform ); //phpcs:ignore
																	?>
														<div class="armclear"></div>
													</div>
												</td>
											</tr>

																	<?php
										}
															}
														}
													}

													?>
												<?php } ?>
						<tr class="form-field"><th></th><td><a class="arm_form_additional_btn" href="javascript:void(0);"><i></i><span><?php esc_html_e( 'Additional Fields', 'armember-membership' ); ?></span></a></td></tr>
					</table>
				</div>
			<div class="arm_admin_form_content arm_member_form_additional_content">
				<table class="form-table">         
							<?php

							$exclude_keys = array(
								'user_login',
								'user_email',
								'user_pass',
								'repeat_pass',
								'arm_user_plan',
								'arm_last_login_ip',
								'arm_last_login_date',
								'roles',
								'section',
								'repeat_pass',
								'repeat_email',
								'social_fields',
								'avatar',
								'profile_cover',
							);
							if ( count( $arm_member_include_fields_keys ) > 0 ) {
								$exclude_keys = array_merge( $exclude_keys, $arm_member_include_fields_keys );
							}
							if ( count( $arm_repeated_fields ) > 0 ) {
								foreach ( $arm_repeated_fields as $field_index => $rfield_key ) {
									unset( $dbFormFields[ $rfield_key ] );
								}
							}

							if ( ! empty( $dbFormFields ) ) {
								foreach ( $dbFormFields as $meta_key => $field ) {
									$field_options = maybe_unserialize( $field );

									$field_options = apply_filters( 'arm_change_field_options', $field_options );
									$meta_key      = isset( $field_options['meta_key'] ) ? $field_options['meta_key'] : $field_options['id'];
									$field_id      = $meta_key . arm_generate_random_code();
									if ( ! in_array( $meta_key, $exclude_keys ) && ! in_array( $field_options['type'], array( 'section', 'roles', 'html', 'hidden', 'submit', 'repeat_pass', 'repeat_email', 'social_fields' ) ) ) {
										?>
										<tr class="form-field">
											<th>
												<label for="<?php echo esc_html($field_options['id']); ?>">
													<?php echo esc_html($field_options['label']); ?>
													<?php echo ( isset( $field_options['required'] ) && $field_options['required'] == 1 ) ? '<span class="required_icon">*</span>' : ''; ?>
												</label>
											</th>
											<td>
												<div class="arm_form_fields_wrapper">
													<?php
													if ( ! empty( $user ) ) {
														$field_options['value'] = $user->$meta_key;
													}
													echo $arm_member_forms->arm_member_form_get_fields_by_type( $field_options, $field_id, $arm_form_id, 'active', $armform ); //phpcs:ignore
													?>
													<div class="armclear"></div>
												</div>
											</td>
										</tr>
										<?php
									}
								}
							}

							?>
							<?php
								/**
								 * Add Form Hidden Fields.
								 */
								/*
								$form_settings = (isset($armform->settings)) ? maybe_unserialize($armform->settings) : array();
								if ($armform->exists() && isset($form_settings['is_hidden_fields']) && $form_settings['is_hidden_fields'] == '1') {
									if (isset($form_settings['hidden_fields']) && !empty($form_settings['hidden_fields'])) {
										foreach ($form_settings['hidden_fields'] as $hiddenF) {
											$hiddenMetaKey = (isset($hiddenF['meta_key']) && !empty($hiddenF['meta_key'])) ? $hiddenF['meta_key'] : sanitize_title('arm_hidden_' . $hiddenF['title']);
											$hiddenValue = get_user_meta($user_id, $hiddenMetaKey, true);
											$hiddenValue = (!empty($hiddenValue)) ? $hiddenValue : $hiddenF['value'];
											$hiddentitle = (!empty($hiddenF['title'])) ? $hiddenF['title'] : '';

											echo '<tr class="form-field"><th>'.$hiddentitle.'</th><td><input type="text" name="' . $hiddenMetaKey . '" value="' . $hiddenValue . '"/></td></tr>';
										}
									}
								}*/

							?>
							<?php
							if ( ! isset( $arm_member_include_fields_keys['avatar'] ) && ! in_array( 'avatar', $arm_member_include_fields_keys ) ) {
								if(!isset($_SESSION['arm_file_upload_arr']['avatar'])){
									if($ARMemberLite->is_arm_pro_active)
									{
										global $arm_members_activity;
										$arm_members_activity->session_for_file_handle('avatar','');
									}
									else
									{
										$arm_lite_members_activity->session_for_file_handle('avatar','');
									}
									
								}
								$avatar_field_id   = 'avatar_' . arm_generate_random_code();
								$avatar_dbfield_id = !empty($dbFormFields['avatar']['db_field_id']) ? $dbFormFields['avatar']['db_field_id'] : '';
								$avatarOptions     = array(
									'id'              => 'avatar',
									'label'           => esc_html__( 'Avatar', 'armember-membership' ),
									'placeholder'     => esc_html__( 'Drop file here or click to select.', 'armember-membership' ),
									'type'            => 'avatar',
									'value'           => '',
									'allow_ext'       => '',
									'file_size_limit' => '2',
									'meta_key'        => 'avatar', //phpcs:ignore
									'required'        => 0,
									'blank_message'   => esc_html__( 'Please select avatar.', 'armember-membership' ),
									'invalid_message' => esc_html__( 'Invalid image selected.', 'armember-membership' ),
								);
								$avatarOptions     = apply_filters( 'arm_change_field_options', $avatarOptions );
								?>
								<tr class="form-field">
									<th>
										<label><?php esc_html_e( 'Avatar', 'armember-membership' ); ?></label>
									</th>
									<td>
										<div class="arm_form_fields_wrapper">
											<?php echo $arm_member_forms->arm_member_form_get_fields_by_type( $avatarOptions, $avatar_field_id, $arm_form_id, 'active', $armform ); //phpcs:ignore ?>
											<div class="armclear"></div>
										</div>
									</td>
								</tr>
								<?php
							}
							if ( ! isset( $arm_member_include_fields_keys['profile_cover'] ) && ! in_array( 'profile_cover', $arm_member_include_fields_keys ) ) {
								if(!isset($_SESSION['arm_file_upload_arr']['profile_cover'])){
									if($ARMemberLite->is_arm_pro_active)
									{
										global $arm_members_activity;
										$arm_members_activity->session_for_file_handle('profile_cover','');
									}
									else
									{
										$arm_lite_members_activity->session_for_file_handle('profile_cover','');
									}
								}
								$profile_cover_field_id = 'profile_cover_' . arm_generate_random_code();
								$profileCoverOptions    = array(
									'id'              => 'profile_cover',
									'label'           => esc_html__( 'Profile Cover', 'armember-membership' ),
									'placeholder'     => esc_html__( 'Drop file here or click to select.', 'armember-membership' ),
									'type'            => 'avatar',
									'value'           => '',
									'allow_ext'       => '',
									'file_size_limit' => '10',
									'meta_key'        => 'profile_cover', //phpcs:ignore
									'required'        => 0,
									'blank_message'   => esc_html__( 'Please select profile cover.', 'armember-membership' ),
									'invalid_message' => esc_html__( 'Invalid image selected.', 'armember-membership' ),
								);
								$profileCoverOptions    = apply_filters( 'arm_change_field_options', $profileCoverOptions );
								?>
								<tr class="form-field">
									<th>
										<label><?php esc_html_e( 'Profile Cover', 'armember-membership' ); ?></label>
									</th>
									<td>
										<div class="arm_form_fields_wrapper">
											<?php echo $arm_member_forms->arm_member_form_get_fields_by_type( $profileCoverOptions, $profile_cover_field_id, $arm_form_id, 'active', $armform ); //phpcs:ignore ?>
											<div class="armclear"></div>
										</div>
									</td>
								</tr>
							<?php } ?>
				</table>
			</div> 
			<div class="arm_admin_form_content">
				<table class="form-table">
						<tr class="form-field">
							<th>
								<label for="arm_role"><?php esc_html_e( 'Role', 'armember-membership' ); ?></label>
							</th>
							<td class="arm-form-table-content">

								<?php
								if ( ! empty( $user ) && ! empty( $user->roles ) ) {
									$u_roles = $user->roles;
								} else {
									$u_roles = array();
								}
								?>

								<select id="arm_role" class="arm_chosen_selectbox" data-msg-required="<?php esc_attr_e( 'Select Role.', 'armember-membership' ); ?>" name="roles[]" data-placeholder="<?php esc_attr_e( 'Select Role(s)..', 'armember-membership' ); ?>" multiple="multiple">
									<?php if ( ! empty( $user_roles ) ) { ?>
										<?php foreach ( $user_roles as $key => $val ) { ?>
											<option class="arm_message_selectbox_op" value="<?php echo esc_attr($key); ?>" 
																									   <?php
																										if ( in_array( $key, $u_roles ) ) {
																											echo "selected='selected'";
																										}
																										?>
											><?php echo esc_html($val); ?></option>
												<?php } ?>
											<?php } else { ?>
										<option value=""><?php esc_html_e( 'No Roles Available', 'armember-membership' ); ?></option>
									<?php } ?>
								</select>
							</td>
						</tr>
						<?php
						$planID = isset( $planIDs[0] ) ? $planIDs[0] : 0;

						if($ARMemberLite->is_arm_pro_active)
						{
							$planObj = new ARM_Plan( $planID );
						}
						else{
							$planObj = new ARM_Plan_Lite( $planID );
						}

						?>

						<tr class="form-field">
							<th>
								<label for="arm_primary_status"><?php esc_html_e( 'Member Status', 'armember-membership' ); ?></label>
							</th>
						<td class="arm_position_relative">
							<div class="armswitch arm_member_status_div">
									<input type="checkbox" id="arm_primary_status_check" <?php checked( $primary_status, '1' ); ?> value="1" class="armswitch_input" name="arm_primary_status"/>
									<label for="arm_primary_status_check" class="armswitch_label arm_primary_status_check_label"></label>
								</div>
								<?php if ( $primary_status == '1' ) { ?>
									<?php
									$arm_user_plans = get_user_meta( $user_id, 'arm_user_plan_ids', true );
									$arm_user_plans = ! empty( $arm_user_plans ) ? $arm_user_plans : array();
								}
								?>
								<input type="hidden" id="arm_status_switch_val" value="<?php echo esc_attr($primary_status); ?>"/>
								<div class="arm_current_status_text">
									<?php echo $arm_members_class->armGetMemberStatusText( $user_id, $primary_status ); //phpcs:ignore ?></div>
								<?php
								if ( $primary_status != 1 && $primary_status != 2 ) {
									$new_status = $primary_status;
								} else {
									$new_status = 2;
								}
								?>
								<div class="arm_inactive_status_text" style="display: none;"><?php echo $arm_members_class->armGetMemberStatusTextForAdmin( $user_id, $new_status, $secondary_status ); //phpcs:ignore ?></div>
								<div class="arm_active_status_text" style="display: none;"><?php echo $arm_members_class->armGetMemberStatusTextForAdmin( $user_id, 1, $secondary_status ); //phpcs:ignore ?></div>
							</td>
						</tr>
						<?php
						if ( isset( $_GET['action'] ) && $_GET['action'] == 'new' ) { //phpcs:ignore

							$arm_all_email_settings       = $arm_email_settings->arm_get_all_email_template();
							$email_without_payment_status = isset( $arm_all_email_settings[2]->arm_template_status ) ? $arm_all_email_settings[2]->arm_template_status : '';
							if ( $email_without_payment_status == '1' ) {
								?>
								<tr class="form-field">
									<th>
										<label for="arm_send_email"><?php esc_html_e( 'Send Signup Email Notification to User', 'armember-membership' ); ?></label>
									</th>
									<td>
										<div class="armswitch arm_send_email_to_user_div">
											<input type="checkbox" id="arm_send_email_check" <?php checked( $email_without_payment_status, '1' ); ?> value="1" class="armswitch_input" name="arm_send_email"/>
											<label for="arm_send_email_check" class="armswitch_label arm_send_email_check_label"></label>
										</div>
									</td>
								</tr>
								<?php
							}
						}
						?>

						<tr><td colspan="2"><div class="arm_solid_divider"></div><div class="page_sub_title"><?php esc_html_e( 'Membership Plan', 'armember-membership' ); ?></div></td></tr>
						<tr>
							<td colspan="2">
								<div class="arm-note-message --warning">
									<p><?php esc_html_e( 'Important Note:', 'armember-membership' ); ?></p>
									<span><?php esc_html_e( 'All the actions like add new plan, change plan status, renew cycle, extend days, delete plan will be applied only after save button is clicked at the bottom of this page.', 'armember-membership' ); ?></span>
								</div>
							</td>
						</tr>

						<tr class="form-field">
							<?php if(!$ARMemberLite->is_arm_pro_active) {?>
							<th>
								<label for="arm_user_plan">
								<?php

										esc_html_e( 'Membership Plan', 'armember-membership' );

								?>
									</label>
							</th>
							<td class="arm_position_relative">

							   

									<span class="arm_user_plan_text">
										<?php
										$plan_name = $arm_subscription_plans->arm_get_plan_name_by_id( $planID ); //phpcs:ignore
										echo ( ! empty( $plan_name ) ) ? $plan_name : '-'; //phpcs:ignore
										$plan_id = ( $planID > 0 ) ? $planID : ''; //phpcs:ignore
										?>
									</span>
									<a href="javascript:void(0)" class="arm_user_plan_change_action_btn" onclick="showUserPlanChangeBoxCallback('plan_change');"><?php esc_html_e( 'Change Plan', 'armember-membership' ); ?></a>
									<div class="arm_confirm_box arm_member_edit_confirm_box arm_confirm_box_plan_change arm_width_280" id="arm_confirm_box_plan_change" >
										<div class="arm_confirm_box_body">
											<div class="arm_confirm_box_arrow"></div>
											<div class="arm_confirm_box_text arm_text_align_left arm_padding_top_15">
												<input type='hidden' id="arm_user_plan" class="arm_user_plan_change_input arm_user_plan_change_input_get_cycle" name="arm_user_plan" data-old="<?php echo esc_attr($plan_id); ?>" value="<?php echo esc_attr($plan_id); ?>" data-manage-plan-grid="2"/>
												<span class="arm_add_plan_filter_label"><?php esc_html_e( 'Select New Plan', 'armember-membership' ); ?></span>
												<dl class="arm_selectbox column_level_dd arm_width_230">
													<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
													<dd><ul data-id="arm_user_plan"><?php echo $plansLists; //phpcs:ignore ?></ul></dd>
												</dl>
												<div style="display: inline-block; position: relative;" class="arm_plan_start_date_box arm_margin_top_10">
													<span class="arm_add_plan_filter_label"><?php esc_html_e( 'Plan Start Date', 'armember-membership' ); ?>  </span> 
													<input type="text" value="<?php echo esc_attr($plan_start_date); //phpcs:ignore ?>"  name="arm_subscription_start_date" class="arm_member_form_input arm_user_plan_date_picker arm_width_232 arm_min_width_232"/>
												</div>
											</div>
											<div class='arm_confirm_box_btn_container'>
												<button type="button" class="arm_confirm_box_btn armemailaddbtn arm_user_plan_change_btn arm_margin_right_5" ><?php esc_html_e( 'Ok', 'armember-membership' ); ?></button>
												<button type="button" class="arm_confirm_box_btn armcancel arm_user_plan_change_cancel_btn" onclick="hideUserPlanChangeBoxCallback();"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></button>
											</div>
										</div>
									</div> 
							
							</td>
							<?php }
							else{
								$arm_membership_plan_list_filter = '';
								echo apply_filters('arm_manage_members_plan_list_filters',$arm_membership_plan_list_filter,$planIDs,$plansLists,$planData); //phpcs:ignore
							}?>
						</tr>
						<?php
						if ( ! empty( $planIDs ) || ! empty( $futurePlanIDs ) ) { ?>
						<tr><td colspan="2">
								<div class="arm_add_member_plans_div">

									<table class="arm_user_plan_table">
										<tr class="odd">
											<th class="arm_user_plan_text_th arm_user_plan_no"><?php esc_html_e( 'No', 'armember-membership' ); ?></th>
											<th class="arm_user_plan_text_th arm_user_plan_name"><?php esc_html_e( 'Membership Plan', 'armember-membership' ); ?></th>
											<th class="arm_user_plan_text_th arm_user_plan_type"><?php esc_html_e( 'Plan Type', 'armember-membership' ); ?></th>
											<th class="arm_user_plan_text_th arm_user_plan_start"><?php esc_html_e( 'Starts On', 'armember-membership' ); ?></th>
											<th class="arm_user_plan_text_th arm_user_plan_end"><?php esc_html_e( 'Expires On', 'armember-membership' ); ?></th>
											<th class="arm_user_plan_text_th arm_user_plan_cycle_date"><?php esc_html_e( 'Cycle Date', 'armember-membership' ); ?></th>
											<th class="arm_user_plan_text_th arm_user_plan_action"><?php esc_html_e( 'Action', 'armember-membership' ); ?></th>
										</tr>
										<?php
											$date_format        = $arm_global_settings->arm_get_wp_date_format();
											$defaultPlanData    = $arm_subscription_plans->arm_default_plan_array();
											$suspended_plan_ids = get_user_meta( $user_id, 'arm_user_suspended_plan_ids', true );
											$suspended_plan_ids = ( isset( $suspended_plan_ids ) && ! empty( $suspended_plan_ids ) ) ? $suspended_plan_ids : array();
											$count_plans        = 0;
										if ( ! empty( $planIDs ) ) {
											foreach ( $planIDs as $pID ) {
												if ( ! empty( $pID ) ) {
													$planData = get_user_meta( $user_id, 'arm_user_plan_' . $pID, true );
													$planData = ! empty( $planData ) ? $planData : array();
													if ( ! empty( $planData ) ) {
														$arm_paid_condition = 1;
														if($ARMemberLite->is_arm_pro_active){
															$arm_paid_condition = apply_filters('arm_pro_paid_conditional',$arm_paid_condition,$planData);
														}
                                                        if ($arm_paid_condition) {
														$planDetail = $planData['arm_current_plan_detail'];
														if ( ! empty( $planDetail ) ) {
															if($ARMemberLite->is_arm_pro_active)
															{
																$planObj = new ARM_Plan( 0 );
															}
															else
															{
																$planObj = new ARM_Plan_Lite( 0 );
															}
															$planObj->init( (object) $planDetail );
														} else {
															if($ARMemberLite->is_arm_pro_active)
															{
																$planObj = new ARM_Plan( $pID );
															}
															else
															{
																$planObj = new ARM_Plan_Lite( $pID );
															}
														}

														$no            = $count_plans;
														$planName      = $planObj->name;
														$grace_message = '';
														$starts_date     = ! empty( $planData['arm_start_plan'] ) ?  $planData['arm_start_plan'] : '';
                                                            $started_date = !empty($planData['arm_started_plan_date']) ? $planData['arm_started_plan_date'] : '';

                                                            $starts_on = !empty($starts_date) ? date_i18n($date_format, $starts_date) : '-';

                                                            if($started_date != '' && $started_date <= $starts_date) {
                                                                $starts_on = date_i18n($date_format, $started_date);
                                                            }
														$expires_on    = ! empty( $planData['arm_expire_plan'] ) ? '<span id="arm_user_expiry_date_' . esc_attr($pID) . '" style="display: inline;"> ' . date_i18n( $date_format, $planData['arm_expire_plan'] ) . ' <img src="' . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/grid_edit_hover_trns.png" width="26" style="position: absolute; margin: -4px 0 0 5px; cursor: pointer;" title="' . esc_attr__( 'Change Expiry Date', 'armember-membership' ) . '" data-plan_id="' . esc_attr($pID) . '" class="arm_edit_user_expiry_date"></span><span id="arm_user_expiry_date_box_' . esc_attr($pID) . '" style="display: none; position: relative; width: 155px;"><input type="text" value="' . esc_attr( date( 'm/d/Y', $planData['arm_expire_plan'] ) ) . '"  name="arm_subscription_expiry_date_' . esc_attr($pID) . '" class="arm_member_form_input arm_user_plan_expiry_date_picker arm_width_120 arm_min_width_120" /><img src="' . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/cancel_date_icon.png" width="11" height="11" title="' . esc_attr__( 'Cancel', 'armember-membership' ) . '" data-plan_id="' . esc_attr($pID) . '" data-plan-expire-date="' . esc_attr( date( $arm_common_date_format, $planData['arm_expire_plan'] ) ) . '" class="arm_cancel_edit_user_expiry_date"></span>' : esc_html__( 'Never Expires', 'armember-membership' ); //phpcs:ignore

														$renewal_on        = ! empty( $planData['arm_next_due_payment'] ) ? date_i18n( $date_format, $planData['arm_next_due_payment'] ) : '-';
														$trial_starts      = ! empty( $planData['arm_trial_start'] ) ? $planData['arm_trial_start'] : '';
														$trial_ends        = ! empty( $planData['arm_trial_end'] ) ? $planData['arm_trial_end'] : '';
														$arm_payment_mode  = ( $planData['arm_payment_mode'] == 'auto_debit_subscription' ) ? '<br/>(' . esc_html__( 'Auto Debit', 'armember-membership' ) . ')' : '';
														$arm_payment_cycle = ! empty( $planData['arm_payment_cycle'] ) ? $planData['arm_payment_cycle'] : '';

														if ( $planObj->is_recurring() ) {
															$recurring_plan_options = $planObj->prepare_recurring_data( $arm_payment_cycle );
															$recurring_time         = $recurring_plan_options['rec_time'];
															$completed              = $planData['arm_completed_recurring'];
															if ( $recurring_time == 'infinite' || empty( $planData['arm_expire_plan'] ) ) {
																$remaining_occurence = esc_html__( 'Never Expires', 'armember-membership' );
															} else {
																$remaining_occurence = $recurring_time - $completed;
															}

															if ( ! empty( $planData['arm_expire_plan'] ) ) {
																if ( $remaining_occurence == 0 ) {
																	$renewal_on = esc_html__( 'No cycles due', 'armember-membership' );
																} else {
																	$renewal_on .= '<br/>( ' . $remaining_occurence . esc_html__( ' cycles due', 'armember-membership' ) . ' )';
																}
															}
                                                            }

															$arm_is_user_in_grace = $planData['arm_is_user_in_grace'];

															$arm_grace_period_end = $planData['arm_grace_period_end'];

															if ( $arm_is_user_in_grace == '1' || $arm_is_user_in_grace == 1 ) {
																$arm_grace_period_end = date_i18n( $date_format, $arm_grace_period_end );
																$grace_message       .= '<br/>( ' . esc_html__( 'grace period expires on', 'armember-membership' ) . ' ' . $arm_grace_period_end . ' )';
															}

														$arm_plan_is_suspended = '';

														if ( ! empty( $suspended_plan_ids ) ) {
															if ( in_array( $pID, $suspended_plan_ids ) ) {
																$arm_plan_is_suspended = '<div class="arm_user_plan_status_div arm_position_relative" ><span class="armhelptip tipso_style arm_color_red" id="arm_user_suspend_plan_' . esc_attr($pID) . '" style=" cursor:pointer;" onclick="arm_show_failed_payment_history(' . esc_attr($user_id) . ',' . esc_attr($pID) . ',\'' . esc_attr($planName) . '\',\'' . esc_attr($planData['arm_start_plan']) . '\')" title="' . esc_attr__( 'Click here to Show failed payment history', 'armember-membership' ) . '">(' . esc_attr__( 'Suspended', 'armember-membership' ) . ')</span><img src="' . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/grid_edit_hover_trns.png" width="26" style="position: absolute; margin: -4px 0 0 5px; cursor: pointer;" title="' . esc_attr__( 'Activate Plan', 'armember-membership' ) . '" data-plan_id="' . esc_attr($pID) . '" onclick="showConfirmBoxCallback(\'change_user_plan_' . esc_attr($pID) . '\');" class="arm_change_user_plan_img_' . esc_attr($pID) . '">'; //phpcs:ignore
 
																$arm_plan_is_suspended .='<div class="arm_confirm_box arm_member_edit_confirm_box" id="arm_confirm_box_change_user_plan_' . esc_attr($pID) . '" style="top:25px; right: -20px; ">
                                                                            <div class="arm_confirm_box_body">
                                                                                <div class="arm_confirm_box_arrow arm_float_right" ></div>
                                                                                <div class="arm_confirm_box_text arm_padding_top_15" ">' .
																		esc_html__( 'Are you sure you want to active this plan?', 'armember-membership' ) . '
                                                                                </div>
                                                                                <div class="arm_confirm_box_btn_container">
                                                                                    <button type="button" class="arm_confirm_box_btn armemailaddbtn arm_margin_right_5" id="arm_change_user_plan_status"  data-index="' . esc_attr($pID) . '" >' . esc_html__( 'Ok', 'armember-membership' ) . '</button>
                                                                                    <button type="button" class="arm_confirm_box_btn armcancel" onclick="hideConfirmBoxCallback();">' . esc_html__( 'Cancel', 'armember-membership' ) . '</button>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                            </div>';
															}
														}

														$trial_active = '';
														if ( ! empty( $trial_starts ) ) {
															if ( $planData['arm_is_trial_plan'] == 1 || $planData['arm_is_trial_plan'] == '1' ) {
																if ( $trial_starts < $planData['arm_start_plan'] ) {
																	$trial_active = "<div class='arm_user_plan_status_div'><span class='arm_current_membership_trial_active'>(" . esc_html__( 'trial active', 'armember-membership' ) . ')</span></div>';
																}
															}
														}
														?>
															<tr class="arm_user_plan_table_tr <?php echo ( $count_plans % 2 == 0 ) ? 'even' : 'odd'; ?>" id="arm_user_plan_div_<?php echo intval($count_plans); ?>">
																<td><?php echo intval($count_plans) + 1; ?></td>
                                                                <?php
                                                                    $plan_access = $planData['arm_current_plan_detail']['arm_subscription_plan_type'];
                                                                    if($plan_access == 'paid_finite')
                                                                    {
                                                                        $expires_on = $expires_on . $grace_message;
                                                                    }
                                                                    if($plan_access == 'recurring')
                                                                    {
                                                                        $renewal_on = $renewal_on . $grace_message;
                                                                    }

                                                                    $additional_plan_details = '';
                                                                    $additional_plan_details = apply_filters('arm_content_after_membership_plan_name_external', $additional_plan_details, $user_id, $pID, $planData); //phpcs:ignore
                                                                    
                                                                ?>
                                                                <td><?php echo $planName . $arm_plan_is_suspended . $additional_plan_details; //phpcs:ignore ?></td>
																<td><?php echo $planObj->new_user_plan_text( false, $arm_payment_cycle ); //phpcs:ignore ?></td>
																<td><?php echo $starts_on . $trial_active; //phpcs:ignore ?></td>
																<td><?php echo $expires_on; //phpcs:ignore ?></td>
                                                                <td><?php echo $renewal_on . $arm_payment_mode; //phpcs:ignore ?></td>

																<td>

																<?php
																if ( $planObj->is_recurring() && $planData['arm_payment_mode'] == 'manual_subscription' && ! in_array( $pID, $futurePlanIDs ) ) {

																	$recurringData = $planObj->prepare_recurring_data( $arm_payment_cycle );

																	$total_recurrence = $recurringData['rec_time'];
																	$completed_rec    = $planData['arm_completed_recurring'];
																	?>
																		<div class="arm_position_relative arm_float_left">
																		<?php
																		if ( ! in_array( $pID, $suspended_plan_ids ) && $total_recurrence != $completed_rec ) {
																			?>
																				<a href="javascript:void(0)" id="arm_extend_cycle_days" class="arm_user_extend_renewal_date_action_btn" onclick="showConfirmBoxCallback('extend_renewal_date_<?php echo intval($pID); ?>');"><?php esc_html_e( 'Extend Days', 'armember-membership' ); ?></a>
																				<div class="arm_confirm_box arm_member_edit_confirm_box arm_confirm_box_extend_renewal_date" id="arm_confirm_box_extend_renewal_date_<?php echo intval($pID); ?>">
																					<div class="arm_confirm_box_body">
																						<div class="arm_confirm_box_arrow"></div>
																						<div class="arm_confirm_box_text arm_padding_top_15">
																							<span class="arm_font_size_15 arm_margin_bottom_5"> <?php esc_html_e( 'Select how many days you want to extend in current cycle?', 'armember-membership' ); ?></span><div class="arm_margin_top_10">
																								<input type='hidden' id="arm_user_grace_plus_<?php echo intval($pID); ?>" name="arm_user_grace_plus_<?php echo intval($pID); ?>" value="0" class="arm_user_grace_plus"/>
																								<dl class="arm_selectbox column_level_dd arm_member_form_dropdown arm_width_83">
																									<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
																									<dd>
																										<ul data-id="arm_user_grace_plus_<?php echo intval($pID); ?>">
																										<?php
																										for ( $i = 0; $i <= 30; $i++ ) {
																											?>
																												<li data-label="<?php echo intval($i); ?>" data-value="<?php echo intval($i); ?>"><?php echo intval($i); ?></li>
																												<?php
																										}
																										?>
																										</ul>
																									</dd>
																								</dl>&nbsp;&nbsp;<?php esc_html_e( 'Days', 'armember-membership' ); ?></div>
																						</div>
																						<div class='arm_confirm_box_btn_container'>
																							<button type="button" class="arm_confirm_box_btn armemailaddbtn arm_margin_right_5" onclick="hideConfirmBoxCallback();"><?php esc_html_e( 'Ok', 'armember-membership' ); ?></button>
																							<button type="button" class="arm_confirm_box_btn armcancel arm_user_extend_renewal_date_cancel_btn" onclick="hideUserExtendRenewalDateBoxCallback(<?php echo intval($pID); ?>);"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></button>
																						</div>
																					</div>
																				</div>
																				<?php
																		}
																		?>
																		<?php
																		if ( $total_recurrence != $completed_rec ) {
																			?>
																				   
																				<a href="javascript:void(0)" class="arm_user_renew_next_cycle_action_btn" id="arm_skip_next_cycle" onclick="showConfirmBoxCallback('renew_next_cycle_<?php echo intval($pID); ?>');"><?php esc_html_e( 'Renew Cycle', 'armember-membership' ); ?></a>
																				<div class="arm_confirm_box arm_member_edit_confirm_box arm_confirm_box_renew_next_cycle arm_width_280" id="arm_confirm_box_renew_next_cycle_<?php echo intval($pID); ?>" style="top:25px; right:45px; ">
																					<div class="arm_confirm_box_body">
																						<div class="arm_confirm_box_arrow arm_float_right" ></div>
																						<div class="arm_confirm_box_text arm_padding_top_15" >
																							<input type='hidden' id="arm_skip_next_renewal_<?php echo intval($pID); ?>" name="arm_skip_next_renewal_<?php echo intval($pID); ?>" value="0" class="arm_skip_next_renewal"/>
																						<?php esc_html_e( 'Are you sure you want to renew next cycle?', 'armember-membership' ); ?>
																						</div>
																						<div class='arm_confirm_box_btn_container'>
																							<button type="button" class="arm_confirm_box_btn armemailaddbtn arm_margin_right_5" onclick="RenewNextCycleOkCallback(<?php echo intval($pID); ?>)" ><?php esc_html_e( 'Ok', 'armember-membership' ); ?></button>
																							<button type="button" class="arm_confirm_box_btn armcancel arm_user_renew_next_cycle_cancel_btn" onclick="hideUserRenewNextCycleBoxCallback(<?php echo intval($pID); ?>);"><?php esc_html_e( 'Cancel', 'armember-membership' ); ?></button>
																						</div>
																					</div>
																				</div>
																				<?php
																		}
																}
else if(isset($planData['arm_current_plan_detail']['arm_subscription_plan_type']) && $planData['arm_current_plan_detail']['arm_subscription_plan_type']=='paid_finite')
                                                                        {
										$arm_renewal_button_section = '';
										echo apply_filters('arm_pro_paid_finite_plan_renewal_section',$arm_renewal_button_section,$pID); //phpcs:ignore
									}

																if ( in_array( $pID, $suspended_plan_ids ) ) {
																	?>
																			<input type="hidden" name="arm_user_suspended_plan[]" value="<?php echo intval($pID); ?>" id="arm_user_suspended_plan_<?php echo intval($pID); ?>"/>
																		<?php
																}

                                                                    $additional_plan_action = "";
                                                                    $additional_plan_action = apply_filters('arm_add_edit_member_member_plan_additional_actions', $additional_plan_action, $user_id, $pID, $planData, $count_plans, $planObj); //phpcs:ignore

                                                                    echo $additional_plan_action; //phpcs:ignore

																	$arm_multiple_membership_plan_renewal = '';
																	echo apply_filters('arm_multiple_membership_plan_renewal', $arm_multiple_membership_plan_renewal, $pID, $planData, $count_plans); //phpcs:ignore
																?>

																</td>
															</tr>


															<?php
															$count_plans++;
													}
												}
											}
										}

						
						 }
						if ( ! empty( $futurePlanIDs ) ) {	
							foreach ( $futurePlanIDs as $pID ) {
								if ( ! empty( $pID ) ) {
									$userPlanDatameta = get_user_meta( $user_id, 'arm_user_plan_' . $pID, true );
									$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
									$planData         = shortcode_atts( $defaultPlanData, $userPlanDatameta );



									if ( ! empty( $planData ) ) {
										$planDetail = $planData['arm_current_plan_detail'];
										if ( ! empty( $planDetail ) ) {
											if($ARMemberLite->is_arm_pro_active)
											{
												$planObj = new ARM_Plan( 0 );
											}
											else
											{
												$planObj = new ARM_Plan_Lite( 0 );
											}
											$planObj->init( (object) $planDetail );
										} else {
											if($ARMemberLite->is_arm_pro_active)
											{
												$planObj = new ARM_Plan( $pID );	
											}
											else{
												$planObj = new ARM_Plan_Lite( $pID );
											}
										}
									}

									$no                = $count_plans;
									$planName          = $planObj->name;
									$grace_message     = '';
									$starts_date         = ! empty( $planData['arm_start_plan'] ) ? $planData['arm_start_plan'] : '';
																$started_date = !empty($planData['arm_started_plan_date']) ? $planData['arm_started_plan_date'] : '';
									$starts_on = !empty($starts_date) ? date_i18n($date_format, $starts_date) : '-';
									if($started_date != '' && $started_date <= $starts_date) {
										$starts_on = date_i18n($date_format, $started_date);
									}
									$expires_on        = ! empty( $planData['arm_expire_plan'] ) ? '<span id="arm_user_expiry_date_' . esc_attr($pID) . '" style="display: inline;">' . date_i18n( $date_format, $planData['arm_expire_plan'] ) . ' <img src="' . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/grid_edit_hover_trns.png" width="26" style="position: absolute; margin: -4px 0 0 5px; cursor: pointer;" title="' . esc_attr__( 'Change Expiry Date', 'armember-membership' ) . '" data-plan_id="' . esc_attr($pID) . '" class="arm_edit_user_expiry_date"></span><span id="arm_user_expiry_date_box_' . esc_attr($pID) . '" style="display: none; position: relative; width: 155px;"><input type="text" value="' . esc_attr(date( 'm/d/Y', $planData['arm_expire_plan'] )) . '"  name="arm_subscription_expiry_date_' . esc_attr($pID) . '" class="arm_member_form_input arm_user_plan_expiry_date_picker" style="width: 120px; min-width: 120px;"/><img src="' . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/cancel_date_icon.png" width="11" height="11" title="' . esc_attr__( 'Cancel', 'armember-membership' ) . '" data-plan_id="' . esc_attr($pID) . '" data-plan-expire-date="' . esc_attr(date( $arm_common_date_format, $planData['arm_expire_plan'] )) . '" class="arm_cancel_edit_user_expiry_date"></span>' : esc_html__( 'Never Expires', 'armember-membership' ); //phpcs:ignore
									$renewal_on        = ! empty( $planData['arm_next_due_payment'] ) ? date_i18n( $date_format, $planData['arm_next_due_payment'] ) : '-';
									$trial_starts      = ! empty( $planData['arm_trial_start'] ) ? $planData['arm_trial_start'] : '';
									$trial_ends        = ! empty( $planData['arm_trial_end'] ) ? $planData['arm_trial_end'] : '';
									$arm_payment_mode  = ( $planData['arm_payment_mode'] == 'auto_debit_subscription' ) ? '<br/>(' . esc_html__( 'Auto Debit', 'armember-membership' ) . ')' : '';
									$arm_payment_cycle = ! empty( $planData['arm_payment_cycle'] ) ? $planData['arm_payment_cycle'] : '';
									if ( $planObj->is_recurring() ) {
										$recurring_plan_options = $planObj->prepare_recurring_data( $arm_payment_cycle );
										$recurring_time         = $recurring_plan_options['rec_time'];
										$completed              = $planData['arm_completed_recurring'];
										if ( $recurring_time == 'infinite' || empty( $planData['arm_expire_plan'] ) ) {
											$remaining_occurence = esc_html__( 'Never Expires', 'armember-membership' );
										} else {
											$remaining_occurence = $recurring_time - $completed;
										}

										if ( ! empty( $planData['arm_expire_plan'] ) ) {
											if ( $remaining_occurence == 0 ) {
												$renewal_on = esc_html__( 'No cycles due', 'armember-membership' );
											} else {
												$renewal_on .= '<br/>( ' . $remaining_occurence . esc_html__( ' cycles due', 'armember-membership' ) . ' )';
											}
										}
										$arm_is_user_in_grace = $planData['arm_is_user_in_grace'];

										$arm_grace_period_end = $planData['arm_grace_period_end'];

										if ( $arm_is_user_in_grace == '1' ) {
											$arm_grace_period_end = date_i18n( $date_format, $arm_grace_period_end );
											$grace_message       .= '<br/>( ' . esc_html__( 'grace period expires on', 'armember-membership' ) . ' ' . $arm_grace_period_end . ' )';
										}
									}

									$arm_plan_is_suspended = '';

									$trial_active = '';
									?>
										<tr class="arm_user_plan_table_tr <?php echo ( $count_plans % 2 == 0 ) ? 'even' : 'odd'; ?>" id="arm_user_future_plan_div_<?php echo intval($count_plans); ?>">
											<td><?php echo intval($no) + 1; ?></td>

											<td><?php echo $planName . $arm_plan_is_suspended; //phpcs:ignore ?></td>
											<td><?php echo $planObj->new_user_plan_text( false, $arm_payment_cycle ); //phpcs:ignore ?></td>
											<td><?php echo $starts_on . $trial_active; //phpcs:ignore ?></td>
											<td><?php echo $expires_on; //phpcs:ignore ?></td>
											<td><?php echo $renewal_on . $grace_message . $arm_payment_mode; //phpcs:ignore ?></td>

											<td>
												<input type="hidden" name="arm_user_future_plan[]" value="<?php echo intval($pID); ?>" id="arm_user_future_plan_<?php echo intval($pID); ?>"/>
												<?php 
												$arm_multiple_membership_plan_renewal = '';
													echo apply_filters('arm_multiple_membership_plan_renewal', $arm_multiple_membership_plan_renewal, $pID, $planData, $count_plans); //phpcs:ignore
												?>
											</td>
										</tr>

										<?php
										$count_plans++;
								}
							}
						}
						?>
                                    </table>

                                </div>

                            </td></tr>
                        <?php
						} ?>
		<?php 
					if($ARMemberLite->is_arm_pro_active)
					{
						$arm_add_edit_member_additional_data = '';
						echo apply_filters('arm_add_edit_member_additional_data',$arm_add_edit_member_additional_data,$planID,$planIDs,$user_id); //phpcs:ignore
					}?>
		
		
		
		</table>
		
						<?php if ( $arm_social_feature->isSocialFeature ) : ?>
							<?php
							$socialProfileFields = $arm_member_forms->arm_social_profile_field_types();
							?>
							<table class="form-table">
								<tr><td colspan="2"><div class="arm_solid_divider"></div><div class="page_sub_title"><?php esc_html_e( 'Social Fields', 'armember-membership' ); ?></div></td></tr>
								<tr class="form-field">
									<th>
										<label><?php esc_html_e( 'Add Social Accounts', 'armember-membership' ); ?></label>
									</th> 
									<td class="arm-form-table-content">           
										<select id="arm_member_social_ac_selection" class="arm_chosen_selectbox arm_width_500" name="arm_member_social_ac_selection" data-placeholder="<?php esc_attr_e( 'Please Select..', 'armember-membership' ); ?>"  data-msg-required="<?php esc_attr_e( 'Please Select Social Account.', 'armember-membership' ); ?>" data-msg-already="<?php esc_attr_e( 'This social account already added.', 'armember-membership' ); ?>">
											<option value=""><?php esc_html_e( 'Please Select', 'armember-membership' ); ?></option>
											<?php
											foreach ( $socialProfileFields as $spfKey => $spfLabel ) {
												echo '<option value="' . esc_attr($spfKey) . '">' . strip_tags( stripslashes( $spfLabel ) ) . '</option>'; //phpcs:ignore
											}
											?>
										</select> <input type="button" class="armcommonbtn" id="arm_member_add_social_account_fields_btn" onclick="arm_member_add_social_account_fields();" value="<?php esc_attr_e( 'Add', 'armember-membership' ); ?>">   
										<div class="armclear"></div>
										<span id="arm_member_social_ac_selection-error" class="error arm_invalid"><?php esc_html_e( 'Please Select account', 'armember-membership' ); ?></span>
									</td>
								</tr>
							</table>
							<table class="form-table" id="arm_social_field_tbl">
							<?php
							if ( ! empty( $socialProfileFields ) ) {
								foreach ( $socialProfileFields as $spfKey => $spfLabel ) {
									$spfMetaKey   = 'arm_social_field_' . $spfKey;
									$spfMetaValue = get_user_meta( $user_id, $spfMetaKey, true );
									if ( ! empty( $spfMetaValue ) ) {
										?>
										<tr class="form-field">
											<th>
												<label><?php echo esc_html($spfLabel); ?></label>
											</th>
											<td>
												<input id="arm_social_<?php echo esc_attr($spfKey); ?>" class="arm_member_form_input" name="<?php echo esc_attr($spfMetaKey); ?>" type="text" value="<?php echo esc_attr($spfMetaValue); ?>"/>
											</td>
										</tr>
										<?php
									}
								}
							}
							?>
							</table>
						<?php endif; ?>
					
                        <?php 
                            $outside_field_content = "";
                            echo $outside_field_content = apply_filters('arm_add_fields_in_admin_before_save_button', $outside_field_content, $user_id); //phpcs:ignore
                        ?>
			
					<!--<div class="arm_divider"></div>-->
					<div class="arm_submit_btn_container">
						<button class="arm_save_btn" type="submit"><?php esc_html_e( 'Save', 'armember-membership' ); ?></button>
						<a class="arm_cancel_btn" href="<?php echo esc_url($cancel_url); ?>"><?php esc_html_e( 'Close', 'armember-membership' ); ?></a>
						<?php echo $formHiddenFields; //phpcs:ignore ?>
						<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
						<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
					</div>
					<div class="armclear"></div>
				</div>
			</form>
			<div class="armclear"></div>
		</div>
	</div>
</div>


<div class="arm_member_plan_failed_payment_popup popup_wrapper" >


	<div class="popup_header">
		<span class="popup_close_btn arm_popup_close_btn arm_member_plan_failed_payment_close_btn"></span>

		<span class="add_rule_content"><?php esc_html_e( 'Total Skipped Cycles Of', 'armember-membership' ); ?> <span class="arm_failed_payment_plan_name"></span></span>
	</div>
	<div class="popup_content_text arm_member_plan_failed_payment_popup_text arm_text_align_center" >

		<div class="arm_width_100_pct" style=" margin: 45px auto;"> <img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>"></div>

	</div>
	<div class="armclear"></div>


</div>
<?php $paidPlansLists = '';?>
<script>
	var PLANLIST = '<?php echo $plansLists; //phpcs:ignore ?>';
    var PLANLIST2 = '<?php echo apply_filters('arm_paid_post_plan_lists',$paidPlansLists,$planIDs); //phpcs:ignore?>';
	var SELECTPLANLABEL = '<?php esc_html_e( 'Select Plan', 'armember-membership' ); ?>';
	var PLANSTARTDATELABEL = '<?php echo esc_html__( 'Plan Start Date', 'armember-membership' ) . ' '; ?>';
	var CURRENTDATE = '<?php echo esc_html(date( $arm_common_date_format ));  //phpcs:ignore?>';
	var REMOVEPLAN = '<?php esc_html_e( 'Remove Plan', 'armember-membership' ); ?>';
	var ADDPLAN = '<?php esc_html_e( 'Add New Plan', 'armember-membership' ); ?>';
	var REMOVEPLANMESSAGE = '<?php esc_html_e( 'You cannot remove all plans.', 'armember-membership' ); ?>';
	var IMAGEURL = "<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore ?>";
	var ACTIVESTATUSLABEL = "<?php esc_html_e( 'Active', 'armember-membership' ); ?>";
    var SELECTPOSTLABEL = '<?php echo addslashes( esc_html__('Select Post', 'armember-membership')); //phpcs:ignore?>';
    var POSTSTARTDATELABEL = '<?php echo addslashes( esc_html__('Post Start Date', 'armember-membership')).' '; //phpcs:ignore?>';
    var ARMREMOVEPOST = '<?php echo addslashes( esc_html__('Remove Post', 'armember-membership')); //phpcs:ignore?>';
    var ARMADDPOST = '<?php echo addslashes( esc_html__('Add New Post', 'armember-membership')); //phpcs:ignore?>';
    var REMOVEPAIDPOSTMESSAGE = '<?php echo addslashes( esc_html__('You cannot remove all posts.', 'armember-membership')); //phpcs:ignore?>';
</script>

<?php
    echo $ARMemberLite->arm_get_need_help_html_content('manage-members-add'); //phpcs:ignore
?>
