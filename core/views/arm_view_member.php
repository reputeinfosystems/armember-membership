<?php
global $wp, $arm_access_rules, $arm_global_settings, $arm_crons, $wpdb, $wp_roles, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_member_forms, $arm_subscription_plans, $arm_payment_gateways, $arm_social_feature, $arm_transaction, $arm_members_badges, $arm_members_activity,$arm_pay_per_post_feature,$arm_common_lite;

$allRoles = $arm_global_settings->arm_get_all_roles();
$dbFormFields = $arm_member_forms->arm_get_db_form_fields(true);
$return_type = "return";
$edit_link = "javascript:void(0)";
$target="";
if ( !empty($_REQUEST['id']) && empty($user_id) ) {
	$user_id = abs(intval($_REQUEST['id'])); //phpcs:ignore
	$edit_link = admin_url('admin.php?page=' . $arm_slugs->manage_members . '&action=edit_member&id=' . $user_id);
	$target="target='_blank'";
}
//$view_type = 'page';
//if ( ! empty( $_REQUEST['view_type'] ) && 'popup' == $_REQUEST['view_type'] ) {
	$view_type = 'popup';
	$view_type_popup_class = " arm_view_member_popup";
//}

if(!empty($user_id))
{
	$user = get_user_by( 'id', $user_id );
	if ( empty( $user ) ) {
		wp_redirect( admin_url( 'admin.php?page=' . $arm_slugs->manage_members ) );
	}
	$user_roles = isset($user->roles) ? $user->roles : '';
	if (empty($user) || ((!empty($user_roles) && in_array('administrator',$user_roles,true)) || (is_multisite() && is_super_admin($user_id)))) {
		if($view_type == 'popup') {
			die;
		}
		else {
			wp_safe_redirect(admin_url('admin.php?page=' . $arm_slugs->manage_members));
		}
	}

	$user_metas = get_user_meta($user_id);
	//$edit_link = admin_url('admin.php?page=' . $arm_slugs->manage_members . '&action=edit_member&id=' . $user->ID);
	$userRegForm = array();
	$armform = '';
	$armform = apply_filters('arm_get_member_forms_filter',$armform);
	if(empty($armform))
	{
		$armform = new ARM_Form_Lite();
	}
	$user_arm_form_id = !empty($user->arm_form_id) ? $user->arm_form_id : 0;
	if(empty($user_arm_form_id))
	{
		$user_arm_form_entry_id = !empty($user->arm_entry_id) ? $user->arm_entry_id : 0;
		$user_arm_form_entry_id = (empty($user_arm_form_entry_id) && !empty($user->arm_wooc_gateway_entry_id)) ? $user->arm_wooc_gateway_entry_id : 0;
		if(!empty($user_arm_form_entry_id))
		{
			$user_arm_form_id = $wpdb->get_var( $wpdb->prepare("SELECT arm_form_id FROM ".$ARMemberLite->tbl_arm_entries." WHERE arm_entry_id = %d ",$user_arm_form_entry_id ) ); //phpcs:ignore --Reason $usermeta_table is a table name
		}
	}
	$user_arm_form_id = apply_filters('arm_modify_member_forms_id_external',$user_arm_form_id);
	if ( !empty($user_arm_form_id) ) {
		$userRegForm = $arm_member_forms->arm_get_single_member_forms($user_arm_form_id);
		$arm_exists_form = $armform->arm_is_form_exists($user_arm_form_id);
		if( $arm_exists_form ){
			$armform->init((object) $userRegForm);
		}
	}
	$date_format = $arm_global_settings->arm_get_wp_date_format();
	$global_currency = $arm_payment_gateways->arm_get_global_currency();
	$all_currencies = $arm_payment_gateways->arm_get_all_currencies();
	$global_currency_sym = $all_currencies[strtoupper($global_currency)];
	$backToListingIcon = MEMBERSHIPLITE_IMAGES_URL . '/back_to_listing_arrow.png';
	if (is_rtl()) {
		$backToListingIcon = MEMBERSHIPLITE_IMAGES_URL . '/back_to_listing_arrow_right.png';
	}
	$arm_hide_edit_profile_status = 0;
	if(!empty($_REQUEST['arm_hide_edit_action']) && empty($_REQUEST['arm_hide_edit_btn_action']))
	{
		$arm_hide_edit_profile_status = 1;
	}
	if(empty($_REQUEST['arm_hide_edit_action']) && !empty($_REQUEST['arm_hide_edit_btn_action']))
	{
		$arm_hide_edit_profile_status = 1;
	}
	$popup_content ='<div class="wrap arm_page arm_view_member_main_wrapper'. esc_attr($view_type_popup_class).'">
	<div class="content_wrapper" id="content_wrapper">
        <div class="arm_view_member_wrapper arm_member_detail_box">
			<div class="arm_belt_box arm_view_memeber_top_belt">
				<div class="arm_belt_block">
					<div class="page_title arm_view_member_title">
						<div class="arm_member_detail_avtar_wrapper">
							<div class="arm_member_detail_avtar">';
							$user_avatar = get_avatar($user_id,72); //phpcs:ignore
								$popup_content .= $user_avatar;
							$popup_content .= '</div>'. esc_html($user->first_name) . ' ' .esc_html($user->last_name).' ('. esc_html($user->user_login).')';
							$popup_content .= '</div>
							<div class="arm_member_detail_btn_wrapper">';
							$arm_admin_view_member_additional_btn_data = '';
							if(empty($arm_hide_edit_profile_status))
							{
								$popup_content .= apply_filters('arm_admin_right_box_panel_btn_section',$arm_admin_view_member_additional_btn_data,$user_id); //phpcs:ignore
								$popup_content .='<a href="javascript:void(0)" '.$target.' class="arm_open_edit_profile_popup_admin armemailaddbtn arm_edit_member_link" onclick="arm_open_edit_member_data('. $user_id.')" data-user_id="'. $user_id .'">
								<img align="absmiddle" class="arm_padding_right_10" src="'.MEMBERSHIPLITE_IMAGES_URL.'/arm_edit_profile_icon.png">
								'. esc_html__('Edit Profile', 'armember-membership').'</a>';
							}
					$popup_content .= '</div>
				</div>';
					if($view_type != 'popup') { 
					$arm_align_css = (is_rtl()) ? 'left' : 'right';
					$arm_align_margin = (is_rtl()) ? 'margin-left: 5px;' : 'margin-right: 5px;';
					$admin_member_page = esc_url( admin_url('admin.php?page=' . $arm_slugs->manage_members) );
					$popup_content .='<div class="arm_belt_block" align="'.$arm_align_margin.'">
					<a href="'. $admin_member_page.'" class="armemailaddbtn">'. esc_html__('Back to listing', 'armember-membership').'</a>
				</div>';
					}
					$arm_first_row_cls = '';
					if(!$ARMemberLite->is_arm_pro_active){
						$arm_first_row_cls = 'arm_margin_top_0';
					}
				$popup_content .='<div class="armclear"></div>
			</div>
			<div class="armclear"></div>
            <div class="arm_member_detail_wrapper_frm arm_admin_form arm_margin_0 arm_width_100_pct">
				<div class="armclear"></div>
				<div class="page_sub_content arm_member_details_container">
					<div class="arm_view_member_left_box '.$arm_first_row_cls.'">
						<div class="arm_view_member_sub_title">'.esc_html__('Personal Information', 'armember-membership').'</div>
						<table class="form-table">
							<tr class="form-field">
								<th class="arm-form-table-label">'. esc_html__('Username', 'armember-membership').'</th>
								<td class="arm-form-table-content">'. esc_html($user->user_login).'</td>
							</tr>';
							$arm_member_include_fields_keys=array('user_email');							
						    if(!empty($user_id)){
						    	$arm_default_form_id = 101;
							    $user = $arm_members_class->arm_get_member_detail($user_id);
							    $arm_form_id = isset($user_arm_form_id) ? $user_arm_form_id : 101;
							    if(empty($arm_form_id)){
							        $arm_form_id=$arm_default_form_id;
							    }
							    if($arm_form_id != 0  && $arm_form_id != '') {
							        $arm_member_form_fields = $arm_member_forms->arm_get_member_forms_fields($arm_form_id, 'all');
							        if(empty($arm_member_form_fields)){
							            $arm_form_id=$arm_default_form_id;
							            $arm_member_form_fields = $arm_member_forms->arm_get_member_forms_fields($arm_form_id, 'all');
							        }							        
							        if(!empty($arm_member_form_fields)){
							            foreach ($arm_member_form_fields as $fields_key => $fields_value) {
							                $arm_member_form_field_slug = $fields_value['arm_form_field_slug'];
							                if($arm_member_form_field_slug != ''){
							                    if(!in_array($fields_value['arm_form_field_option']['type'], array('section','html', 'hidden', 'submit','social_fields','repeat_pass','repeat_email','roles'))){
							                        $arm_member_include_fields_keys[$arm_member_form_field_slug]=$arm_member_form_field_slug;	         
							                        $dbFormFields[$arm_member_form_field_slug]['label'] = $fields_value['arm_form_field_option']['label'];
							                        if(isset($dbFormFields[$arm_member_form_field_slug]['options']) && isset($fields_value['arm_form_field_option']['options'])){
							                            $dbFormFields[$arm_member_form_field_slug]['options'] = $fields_value['arm_form_field_option']['options'];
							                            
							                        }
							                        $dbFormFields['display_member_fields'][$arm_member_form_field_slug]=$arm_member_form_field_slug;
							                    }    
							                }
							            }

							        }
							        if(isset($dbFormFields['display_member_fields']) && count($dbFormFields['display_member_fields'])){
							            $dbFormFields = array_merge(array_flip($dbFormFields['display_member_fields']), $dbFormFields);
							            unset($dbFormFields['display_member_fields']);
							        }
							    }    
							}    
							$exclude_keys = array(
                                'user_login', 'user_pass', 'repeat_pass','arm_user_plan', 'arm_last_login_ip', 'arm_last_login_date', 'roles', 'section', 
                                'repeat_pass', 'repeat_email', 'social_fields', 'avatar', 'profile_cover','arm_captcha'
                            );
                            if (!empty($dbFormFields)) {
                                foreach ($dbFormFields as $meta_key => $field) {
                                    $field_options = maybe_unserialize($field);
                                    $field_options = apply_filters('arm_change_field_options', $field_options);
                                    $meta_key = isset($field_options['meta_key']) ? $field_options['meta_key'] : $field_options['id'];
                                    $field_id = $meta_key . arm_generate_random_code();
                                    if (!in_array($meta_key, $exclude_keys) && in_array($meta_key,$arm_member_include_fields_keys) && !in_array($field_options['type'], array('section', 'roles', 'html', 'hidden', 'submit', 'repeat_pass', 'repeat_email'))) {
										$popup_content .='<tr class="form-field">
											<th class="arm-form-table-label">'. esc_html($field_options['label']).'</th>
											<td class="arm-form-table-content">';
											if (!empty($user->$meta_key)) {																			
												if($field_options['type'] == 'email') {
													$popup_content .='<a class="" href="mailto:'. esc_attr($user->user_email).'">'. esc_html($user->user_email).'</a>';
												} else if ($field_options['type'] == 'file') {
                                                    $file_name = basename($user->$meta_key);
													if ($user->$meta_key != '') {
														$files_urls = explode(',',$user->$meta_key);
														if($files_urls > 0)
														{
															foreach($files_urls as $file_url)
															{
																$exp_val = explode("/",$file_url);
																$filename = $exp_val[count($exp_val)-1];
																$file_extension = explode('.',$filename);
																$file_ext = $file_extension[count($file_extension) - 1];
																$thumbUrl = '';
																if (in_array($file_ext, array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'tif', 'tiff'))) {
																	$thumbUrl = $file_url;
																} else if (in_array($file_ext, array('pdf', 'exe'))) {
																	$thumbUrl = MEMBERSHIPLITE_IMAGES_URL."/document.png";
																} else if (in_array($file_ext, array('zip'))) {
																	$thumbUrl = MEMBERSHIPLITE_IMAGES_URL."/archive.png";
																} else {
																	$thumbUrl = MEMBERSHIPLITE_IMAGES_URL."/text.png";
																}
																$popup_content .='<a href="'. esc_url($file_url).'" target="__blank"> <img src="'. esc_url($thumbUrl).'" class="arm_max_width_100"style="height: auto;"></a>';
															}
														}
                                                    } 
                                                } else if (in_array($field_options['type'], array('radio', 'checkbox', 'select'))) {
                                                    $user_meta_detail = $user->$meta_key;
                                                    $main_array = array();
                                                    $options = $field_options['options'];
                                                    $value_array = array();
                                                    foreach ($options as $arm_key => $arm_val) {
                                                        if (strpos($arm_val, ":") != false) {
															$exp_val = explode(":", $arm_val);
															$exp_val1 = $exp_val[1];
															$value_array[$exp_val[0]] = $exp_val[1];
														} else {
															$value_array[$arm_val] = $arm_val;
														}
													}
                                                    $user_meta_detail = $ARMemberLite->arm_array_trim($user_meta_detail);
                                                    if (!empty($value_array)) {
                                                        if (is_array($user_meta_detail)) {
                                                            foreach ($user_meta_detail as $u) {
																foreach ($value_array as $arm_key => $arm_val) {
                                                                    if ($u == $arm_val) {
                                                                        array_push($main_array,$arm_key);
                                                                    }
                                                                }
                                                            }
                                                            $user_meta_detail = @implode(', ', $main_array);
                                                            $popup_content .= esc_html($user_meta_detail);
                                                        } else {
                                                            $exp_val = array();
                                                            /*if (strpos($user_meta_detail, ",") != false) {
																$exp_val = explode(",", $user_meta_detail);
                                                            }*/
                                                            if (!empty($exp_val)) {
                                                                foreach ($exp_val as $u) {
                                                                    if (in_array($u, $value_array)) {
                                                                        array_push($main_array,array_search($u,$value_array));
                                                                    }
                                                                }
                                                                $user_meta_detail = @implode(', ', $main_array);
                                                                $popup_content .= esc_html($user_meta_detail);
                                                            } else {
                                                                if (in_array($user_meta_detail, $value_array)) {
                                                                    $popup_content .= array_search($user_meta_detail,$value_array); //phpcs:ignore
                                                                } else {
                                                                    $popup_content .= esc_html($user_meta_detail);
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        if (is_array($user_meta_detail)) {
															$user_meta_detail = $ARMemberLite->arm_array_trim($user_meta_detail);
															$user_meta_detail = @implode(', ', $user_meta_detail);
															$popup_content .= esc_html($user_meta_detail);
														} else {
															$popup_content .= esc_html($user_meta_detail);
														}
													}
												} else {
													$user_meta_detail = $user->$meta_key;
													/*
													$pattern = '/^(date\_(.*))/';

                    								if(preg_match($pattern, $meta_key)){
                    										$user_meta_detail  =  date_i18n($date_format, strtotime($user_meta_detail));
                    								}
                    								*/
													if (is_array($user_meta_detail)) {
														$user_meta_detail = $ARMemberLite->arm_array_trim($user_meta_detail);
														$user_meta_detail = @implode(', ', $user_meta_detail);
														$popup_content .= esc_html($user_meta_detail);
													} else {
														$popup_content .= esc_html($user_meta_detail);
													}
												}
											} else {
												$popup_content .= "--";
											}
											$popup_content .='</td>
										</tr>';
                                    }
                                }
                            }                             
							$popup_content .='
                        </table>
                    </div>
					<div class="arm_view_member_left_box">
						<div class="arm_view_member_sub_title">'.esc_html__('Other Information', 'armember-membership').'</div>
					    <table class="form-table">      
							<tr class="form-field">
								<th class="arm-form-table-label">'. esc_html__('Role', 'armember-membership').'</th>
								<td class="arm-form-table-content">'.
								$u_roles = '';
								if (!empty($user->roles)) {
									foreach ($user->roles as $urole) {
										if (isset($allRoles[$urole])) {
                                            $u_roles .= $allRoles[$urole] . ', ';
										}
									}
									$u_roles = trim($u_roles, ', ');
                                }else{
                                    $u_roles = get_option('default_role');
                                }
                                $popup_content .= esc_html($u_roles);
								$popup_content .= '</td>
							</tr>
							<tr class="form-field">
								<th class="arm-form-table-label">'. esc_html__('Member Status', 'armember-membership').'</th>
								<td class="arm-form-table-content">'. $arm_members_class->armGetMemberStatusText($user_id).'</td>
							</tr>
							<tr class="form-field">
								<th class="arm-form-table-label">'. esc_html__('Member Since', 'armember-membership').'</th>
								<td class="arm-form-table-content">'.
									date_i18n($date_format, strtotime($user->user_registered)).'</td>
							</tr>
							<tr class="form-field">
								<th class="arm-form-table-label">'.esc_html__('Registered/Edited Profile From', 'armember-membership').'</th>
								<td class="arm-form-table-content">'; 
								if (!empty($user_arm_form_id) && $user_arm_form_id != 0) {
									if (!empty($userRegForm)) {
										$popup_content .= strip_tags(stripslashes($userRegForm['arm_form_label'])) . "<em> (Form ID: <b>$user_arm_form_id</b>)</em>"; //phpcs:ignore
									} else {
										$popup_content .= "--";
									}
								}
								else {
									$arm_is_user_import = get_user_meta($user->ID, 'arm_user_import');
									if($arm_is_user_import){
										$popup_content .= esc_html__('ARMember Admin (Import)', 'armember-membership'); 
									} else {
	                                    $usermeta_table = $wpdb->usermeta;
	                                    $result_arm_meta = $wpdb->get_results( $wpdb->prepare("SELECT count(*) as arm_meta FROM ".$usermeta_table." WHERE user_id = %d and meta_key like %s AND meta_key != %s AND meta_key != %s AND meta_key != %s", $user->ID, '%arm_%', '_arm_feed_key', 'arm_user_activation_key', 'arm_autolock_cookie'), ARRAY_A ); //phpcs:ignore --Reason $usermeta_table is a table name
	                                    if(isset($result_arm_meta[0]['arm_meta']) && $result_arm_meta[0]['arm_meta'] > 0)
	                                    {
	                                        $popup_content .= esc_html__('ARMember Admin', 'armember-membership');
	                                    } else {
											$popup_content .= esc_html__('Wordpress default', 'armember-membership');
	                                    }
	                                }
								}
								$popup_content .= '</td>
							</tr>';
							$arm_social_profiles_field_data = '';
							$popup_content .= apply_filters( 'arm_admin_view_member_get_social_profile_data', $arm_social_profiles_field_data, $user_id); //phpcs:ignore
						$popup_content .='</table>
					</div>
                    <div class="arm_view_member_left_box">
						<div class="form-field">
							<a class="arm_form_additional_btn arm_view_form_additional_btn" href="javascript:void(0);"><i></i><div class="arm_view_member_sub_title arm_padding_0">'.esc_html__('Additional Information', 'armember-membership').'</div></a>
						</div>
						<div class="arm_member_form_additional_content">
							<table class="form-table">';
								if (!empty($dbFormFields)) {
									foreach ($dbFormFields as $meta_key => $field) {
										$field_options = maybe_unserialize($field);
										$field_options = apply_filters('arm_change_field_options', $field_options);
										$meta_key = isset($field_options['meta_key']) ? $field_options['meta_key'] : $field_options['id'];
										$field_id = $meta_key . arm_generate_random_code();
										if (!in_array($meta_key, $exclude_keys) && !in_array($meta_key,$arm_member_include_fields_keys) && !in_array($field_options['type'], array('section', 'roles', 'html', 'hidden', 'submit', 'repeat_pass', 'repeat_email'))) {
											$popup_content .= '<tr class="form-field">
												<th class="arm-form-table-label">'. esc_html($field_options['label']).'</th>
												<td class="arm-form-table-content">';
												if (!empty($user->$meta_key)) {
													if ($field_options['type'] == 'file') {
														$file_name = basename($user->$meta_key);
														if ($user->$meta_key != '') {
															$exp_val = explode("/",$user->$meta_key);
															$filename = $exp_val[count($exp_val)-1];
															$file_extension = explode('.',$filename);
															$file_ext = $file_extension[count($file_extension) - 1];
															$thumbUrl = '';
															if (in_array($file_ext, array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'tif', 'tiff'))) {
																$thumbUrl = $user->$meta_key;
															} else if (in_array($file_ext, array('pdf', 'exe'))) {
																$thumbUrl = MEMBERSHIPLITE_IMAGES_URL."/document.png";
															} else if (in_array($file_ext, array('zip'))) {
																$thumbUrl = MEMBERSHIPLITE_IMAGES_URL."/archive.png";
															} else {
																$thumbUrl = MEMBERSHIPLITE_IMAGES_URL."/text.png";
															}
															$popup_content .= '<a href="'. esc_url($user->$meta_key).'" target="__blank"> <img src="'. esc_url($thumbUrl).'" class="arm_max_width_100"style="height: auto;"></a>';
														} 
													} else if (in_array($field_options['type'], array('radio', 'checkbox', 'select'))) {
														$user_meta_detail = $user->$meta_key;
														$main_array = array();
														$options = $field_options['options'];
														$value_array = array();
														foreach ($options as $arm_key => $arm_val) {
															if (strpos($arm_val, ":") != false) {
																$exp_val = explode(":", $arm_val);
																$exp_val1 = $exp_val[1];
																$value_array[$exp_val[0]] = $exp_val[1];
															} else {
																$value_array[$arm_val] = $arm_val;
															}
														}
														$user_meta_detail = $ARMemberLite->arm_array_trim($user_meta_detail);
														if (!empty($value_array)) {
															if (is_array($user_meta_detail)) {
																foreach ($user_meta_detail as $u) {
																	foreach ($value_array as $arm_key => $arm_val) {
																		if ($u == $arm_val) {
																			array_push($main_array,$arm_key);
																		}
																	}
																}
																$user_meta_detail = @implode(', ', $main_array);
																$popup_content .= esc_html($user_meta_detail); 
															} else {
																$exp_val = array();
																/*if (strpos($user_meta_detail, ",") != false) {
																	$exp_val = explode(",", $user_meta_detail);
																}*/
																if (!empty($exp_val)) {
																	foreach ($exp_val as $u) {
																		if (in_array($u, $value_array)) {
																			array_push($main_array,array_search($u,$value_array));
																		}
																	}
																	$user_meta_detail = @implode(', ', $main_array);
																	$popup_content .= esc_html($user_meta_detail);
																} else {
																	if (in_array($user_meta_detail, $value_array)) {
																		$popup_content .= array_search($user_meta_detail,$value_array); //phpcs:ignore
																	} else {
																		$popup_content .= esc_html($user_meta_detail);
																	}
																}
															}
														} else {
															if (is_array($user_meta_detail)) {
																$user_meta_detail = $ARMemberLite->arm_array_trim($user_meta_detail);
																$user_meta_detail = @implode(', ', $user_meta_detail);
																$popup_content .= esc_html($user_meta_detail);
															} else {
																$popup_content .= esc_html($user_meta_detail);
															}
														}
													} else {
														$user_meta_detail = $user->$meta_key;
														if (is_array($user_meta_detail)) {
															$user_meta_detail = $ARMemberLite->arm_array_trim($user_meta_detail);
															$user_meta_detail = @implode(', ', $user_meta_detail);
															$popup_content .= esc_html($user_meta_detail);
														} else {
															$popup_content .= esc_html($user_meta_detail);
														}
													}
												} else {
													$popup_content .= "--";
												}
												$popup_content .= '</td>
											</tr>';
										}
									}
								}
								$form_settings = (isset($armform->settings)) ? maybe_unserialize($armform->settings) : array();
								if ($armform->exists() && isset($form_settings['is_hidden_fields']) && $form_settings['is_hidden_fields'] == '1') {
									if (isset($form_settings['hidden_fields']) && !empty($form_settings['hidden_fields'])) {
										foreach ($form_settings['hidden_fields'] as $hiddenF) {
											$hiddenMetaKey = (isset($hiddenF['meta_key']) && !empty($hiddenF['meta_key'])) ? $hiddenF['meta_key'] : sanitize_title('arm_hidden_'.$hiddenF['title']);
											$hiddenValue = get_user_meta($user_id, $hiddenMetaKey, true);
											$popup_content .= '<tr class="form-field">
												<th class="arm-form-table-label">'. esc_html($hiddenF['title']).'</th>
												<td class="arm-form-table-content">'. esc_html($hiddenValue).'</td>
											</tr>';
										}
									}
								}  
							$popup_content .= '</table>
						</div>
                    </div>
                    
					<div class="armclear"></div>';
					$plan_id_name_array = $arm_subscription_plans->arm_get_plan_name_by_id_from_array();                                        
                    
					$membership_history = $arm_subscription_plans->arm_get_user_membership_history($user_id, 1, 5, $plan_id_name_array);
					if(!empty($membership_history)){
						$popup_content .= '
						<div class="arm_view_member_sub_title arm_padding_0 arm_padding_top_48">'.esc_html__('Membership History', 'armember-membership').'</div>
						<div class="arm_view_member_left_box arm_no_border">
							<div class="arm_view_member_sub_content arm_membership_history_list armPageContainer arm_padding_0">
								'. $membership_history.'
							</div>
						</div>
						<div class="armclear"></div>';
					}
					$user_logs = $arm_transaction->arm_get_user_transactions_with_pagging($user_id, 1, 5, $plan_id_name_array);
					if(!empty($user_logs)){
						$popup_content .= '
						<div class="arm_view_member_sub_title arm_padding_0 arm_padding_top_32">'.esc_html__('Payment History', 'armember-membership').'</div>
						<div class="arm_view_member_left_box arm_no_border">
							<div class="arm_view_member_sub_content arm_membership_history_list armPageContainer arm_padding_0">
							'. $user_logs.'
							</div>
						</div>
						<div class="armclear"></div>';
					}
					$popup_content .= '<div class="armclear"></div>';
					$arm_member_plans_details = '';
					
					$popup_content .= apply_filters('arm_view_members_memberships_details',$arm_member_plans_details,$user_id,$plan_id_name_array); //phpcs:ignore
					$popup_content .= '<div class="armclear"></div>';
					$arm_member_details = "";
					$arm_member_details = apply_filters('arm_view_member_details_outside', $arm_member_details, $user_id, $plan_id_name_array);
					$popup_content .= $arm_member_details; //phpcs:ignore				

					$wpnonce = wp_create_nonce( 'arm_wp_nonce' );
					$arm_mcard_template_preview = '';
					$arm_mcard_template_preview = apply_filters( 'arm_mcard_bpopup_html', $arm_mcard_template_preview);
					$popup_content .= '<input type="hidden" name="arm_wp_nonce" value="'. esc_attr($wpnonce).'"/>
				</div>
            </div>
        </div>
        <div class="armclear"></div>
		<div class="arm_members_activities_detail_container"></div>
		<div id="arm_profile_directory_template_preview" class="arm_profile_directory_template_preview">'.
			$arm_mcard_template_preview
		.'
		</div>
    </div>
</div>';
}