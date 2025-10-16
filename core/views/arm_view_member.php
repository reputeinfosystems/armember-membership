<?php
global $wp, $arm_access_rules, $arm_global_settings, $arm_crons, $wpdb, $wp_roles, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_member_forms, $arm_subscription_plans, $arm_payment_gateways, $arm_social_feature, $arm_transaction, $arm_members_badges, $arm_members_activity,$arm_pay_per_post_feature;

$allRoles = $arm_global_settings->arm_get_all_roles();
$dbFormFields = $arm_member_forms->arm_get_db_form_fields(true);
$user_id = 0;
if (!empty($_REQUEST['id'])) { //phpcs:ignore
	$user_id = abs(intval($_REQUEST['id'])); //phpcs:ignore
}
$view_type = "page";
$view_type_popup_class = "";
if(!empty($_REQUEST['view_type']) && 'popup' == $_REQUEST['view_type']) { //phpcs:ignore
	$view_type = "popup";
	$view_type_popup_class = " arm_view_member_popup";
}

$user = get_user_by('id', $user_id);
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
$edit_link = admin_url('admin.php?page=' . $arm_slugs->manage_members . '&action=edit_member&id=' . $user->ID);
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
?>

<div class="wrap arm_page arm_view_member_main_wrapper<?php echo esc_attr($view_type_popup_class);?>">
	<?php
    if($ARMemberLite->is_arm_pro_active)
	{
		$arm_license_notice = '';
		echo apply_filters('arm_admin_license_notice_html',$arm_license_notice); //phpcs:ignore
	}
	?>
	<div class="content_wrapper" id="content_wrapper">
        <div class="arm_view_member_wrapper arm_member_detail_box">
			<div class="arm_belt_box arm_view_memeber_top_belt">
				<div class="arm_belt_block">
					<div class="page_title"><?php echo esc_html($user->first_name) . ' ' .esc_html($user->last_name);?> (<?php echo esc_html($user->user_login);?>)</div>
				</div>
				<?php
					if($view_type != 'popup') { 
				?>
				<div class="arm_belt_block" align="<?php echo (is_rtl()) ? 'left' : 'right';?>">
					<a href="<?php echo esc_url( admin_url('admin.php?page=' . $arm_slugs->manage_members) ); //phpcs:ignore?>" class="armemailaddbtn"><img src="<?php echo esc_url($backToListingIcon);?>" style="<?php echo (is_rtl()) ? 'margin-left: 5px;' : 'margin-right: 5px;';?>"/><?php esc_html_e('Back to listing', 'armember-membership');?></a>
				</div>
				<?php
					}
				?>
				<div class="armclear"></div>
			</div>
			<div class="armclear"></div>
            <form class="arm_member_detail_wrapper_frm arm_admin_form">
				<div class="armclear"></div>
				<div class="page_sub_content arm_member_details_container">
					<div class="arm_view_member_left_box">
						<table class="form-table">
							<tr class="form-field">
								<th class="arm-form-table-label"><?php esc_html_e('Username', 'armember-membership');?>:</th>
								<td class="arm-form-table-content"><?php echo esc_html($user->user_login); ?></td>
							</tr>
							<?php							 
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
                                        ?>
										<tr class="form-field">
											<th class="arm-form-table-label"><?php echo esc_attr($field_options['label']);?>:</th>
											<td class="arm-form-table-content"><?php 
											if (!empty($user->$meta_key)) {																			
												if($field_options['type'] == 'email') {
													?>
													<a class="" href="mailto:<?php echo esc_attr($user->user_email); ?>"><?php echo esc_html($user->user_email); ?></a><?php 
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
																?><a href="<?php echo esc_url($file_url);?>" target="__blank"> <img src="<?php echo esc_url($thumbUrl); //phpcs:ignore ?>" class="arm_max_width_100"style="height: auto;"></a><?php
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
                                                            echo esc_html($user_meta_detail);
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
                                                                echo esc_html($user_meta_detail);
                                                            } else {
                                                                if (in_array($user_meta_detail, $value_array)) {
                                                                    echo array_search($user_meta_detail,$value_array); //phpcs:ignore
                                                                } else {
                                                                    echo esc_html($user_meta_detail);
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        if (is_array($user_meta_detail)) {
															$user_meta_detail = $ARMemberLite->arm_array_trim($user_meta_detail);
															$user_meta_detail = @implode(', ', $user_meta_detail);
															echo esc_html($user_meta_detail);
														} else {
															echo esc_html($user_meta_detail);
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
														echo esc_html($user_meta_detail);
													} else {
														echo esc_html($user_meta_detail);
													}
												}
											} else {
												echo "--";
											}
											?>
											</td>
										</tr>
										<?php
                                    }
                                }
                            }           
                            ?>                            
							<tr class="form-field"><th><a class="arm_form_additional_btn" href="javascript:void(0);"><i></i><span><?php esc_html_e('View Additional Fields', 'armember-membership');?></span></a></th>
                        	</tr>                        	
                        </table>
                    </div>

					<div class="arm_view_member_right_box">
						<div class="arm_member_detail_avtar_section">
							<div class="arm_member_detail_avtar">
								<?php echo $user_avatar = get_avatar($user_id, 150); //phpcs:ignore?>
							</div>							
						</div>
						<?php
						$arm_admin_view_member_additional_data = '';
						echo apply_filters('arm_admin_right_box_panel_section',$arm_admin_view_member_additional_data,$user_id); //phpcs:ignore
						$last_login_date = get_user_meta($user_id, 'arm_last_login_date', true);
						if(!empty($last_login_date)) {
							$last_login_ip = get_user_meta($user_id, 'arm_last_login_ip', true);
							?>
							<div class="arm_member_detail_login_section">
								<div class="arm_member_detail_login_date">
									<span><?php 
									echo esc_html__('Last loggedin on', 'armember-membership').' '.date_i18n($date_format, strtotime($last_login_date)).''; //phpcs:ignore
									if(!empty($last_login_ip)) {
										echo ' '.esc_html__('from IP', 'armember-membership').' '.$last_login_ip; //phpcs:ignore
									}
									?></span>
								</div>
							</div>
						<?php } ?>
						
						<a href="<?php echo esc_url($edit_link);?>" class="arm_open_edit_profile_popup_admin armemailaddbtn arm_edit_member_link"><?php esc_html_e('Edit Profile', 'armember-membership');?></a>		
						<?php
							$arm_admin_view_member_additional_btn_data = '';
							echo apply_filters('arm_admin_right_box_panel_btn_section',$arm_admin_view_member_additional_btn_data,$user_id); //phpcs:ignore
						?>
						
					</div>
                    <div class="arm_view_member_left_box arm_member_form_additional_content">
				      	<table class="form-table">
                        	<?php
                        	if (!empty($dbFormFields)) {
                                foreach ($dbFormFields as $meta_key => $field) {
                                    $field_options = maybe_unserialize($field);
                                    $field_options = apply_filters('arm_change_field_options', $field_options);
                                    $meta_key = isset($field_options['meta_key']) ? $field_options['meta_key'] : $field_options['id'];
                                    $field_id = $meta_key . arm_generate_random_code();
                                    if (!in_array($meta_key, $exclude_keys) && !in_array($meta_key,$arm_member_include_fields_keys) && !in_array($field_options['type'], array('section', 'roles', 'html', 'hidden', 'submit', 'repeat_pass', 'repeat_email'))) {
                                        ?>
										<tr class="form-field">
											<th class="arm-form-table-label"><?php echo esc_html($field_options['label']); //phpcs:ignore?>:</th>
											<td class="arm-form-table-content"><?php 
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
														?><a href="<?php echo esc_url($user->$meta_key);?>" target="__blank"> <img src="<?php echo esc_url($thumbUrl); //phpcs:ignore ?>" class="arm_max_width_100"style="height: auto;"></a><?php
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
                                                            echo esc_html($user_meta_detail); 
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
                                                                echo esc_html($user_meta_detail);
                                                            } else {
                                                                if (in_array($user_meta_detail, $value_array)) {
                                                                    echo array_search($user_meta_detail,$value_array); //phpcs:ignore
                                                                } else {
                                                                    echo esc_html($user_meta_detail);
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        if (is_array($user_meta_detail)) {
															$user_meta_detail = $ARMemberLite->arm_array_trim($user_meta_detail);
															$user_meta_detail = @implode(', ', $user_meta_detail);
															echo esc_html($user_meta_detail);
														} else {
															echo esc_html($user_meta_detail);
														}
													}
												} else {
													$user_meta_detail = $user->$meta_key;
													if (is_array($user_meta_detail)) {
														$user_meta_detail = $ARMemberLite->arm_array_trim($user_meta_detail);
														$user_meta_detail = @implode(', ', $user_meta_detail);
														echo esc_html($user_meta_detail);
													} else {
														echo esc_html($user_meta_detail);
													}
												}
											} else {
												echo "--";
											}
											?>
											</td>
										</tr>
										<?php
                                    }
                                }
                            }
                            $form_settings = (isset($armform->settings)) ? maybe_unserialize($armform->settings) : array();
                            if ($armform->exists() && isset($form_settings['is_hidden_fields']) && $form_settings['is_hidden_fields'] == '1') {
                                if (isset($form_settings['hidden_fields']) && !empty($form_settings['hidden_fields'])) {
                                    foreach ($form_settings['hidden_fields'] as $hiddenF) {
                                        $hiddenMetaKey = (isset($hiddenF['meta_key']) && !empty($hiddenF['meta_key'])) ? $hiddenF['meta_key'] : sanitize_title('arm_hidden_'.$hiddenF['title']);
                                        $hiddenValue = get_user_meta($user_id, $hiddenMetaKey, true);
                                        ?>
                                        <tr class="form-field">
                                            <th class="arm-form-table-label"><?php echo esc_html($hiddenF['title']);?>:</th>
                                            <td class="arm-form-table-content"><?php echo esc_html($hiddenValue);?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                            }  
                            ?>
                        </table>
                    </div>
                    <div class="arm_view_member_left_box">
					    <table class="form-table">      
							<tr class="form-field">
								<th class="arm-form-table-label"><?php esc_html_e('Role', 'armember-membership');?>:</th>
								<td class="arm-form-table-content"><?php 
                                $u_roles = '-';
								if (!empty($user->roles)) {
                                    $u_roles = '';
									foreach ($user->roles as $urole) {
										if (isset($allRoles[$urole])) {
                                            $u_roles .= $allRoles[$urole] . ', ';
										}
									}
									$u_roles = trim($u_roles, ', ');
                                }else{
                                    $u_roles = get_option('default_role');
                                }
                                echo esc_html($u_roles);
								?></td>
							</tr>
							<tr class="form-field">
								<th class="arm-form-table-label"><?php esc_html_e('Member Status', 'armember-membership');?>:</th>
								<td class="arm-form-table-content"><?php 
								echo $arm_members_class->armGetMemberStatusText($user_id); //phpcs:ignore
								?></td>
							</tr>
							<tr class="form-field">
								<th class="arm-form-table-label"><?php esc_html_e('Member Since', 'armember-membership');?>:</th>
								<td class="arm-form-table-content"><?php
									echo date_i18n($date_format, strtotime($user->user_registered)); //phpcs:ignore
								?></td>
							</tr>
							<tr class="form-field">
								<th class="arm-form-table-label"><?php esc_html_e('Registered/Edited Profile From', 'armember-membership');?>:</th>
								<td class="arm-form-table-content"><?php 
								if (!empty($user_arm_form_id) && $user_arm_form_id != 0) {
									if (!empty($userRegForm)) {
										echo strip_tags(stripslashes($userRegForm['arm_form_label'])) . "<em> (Form ID: <b>$user_arm_form_id</b>)</em>"; //phpcs:ignore
									} else {
										echo "--";
									}
								}
								else {
									$arm_is_user_import = get_user_meta($user->ID, 'arm_user_import');
									if($arm_is_user_import){
										esc_html_e('ARMember Admin (Import)', 'armember-membership'); 
									} else {
	                                    $usermeta_table = $wpdb->usermeta;
	                                    $result_arm_meta = $wpdb->get_results( $wpdb->prepare("SELECT count(*) as arm_meta FROM ".$usermeta_table." WHERE user_id = %d and meta_key like %s AND meta_key != %s AND meta_key != %s AND meta_key != %s", $user->ID, '%arm_%', '_arm_feed_key', 'arm_user_activation_key', 'arm_autolock_cookie'), ARRAY_A ); //phpcs:ignore --Reason $usermeta_table is a table name
	                                    if(isset($result_arm_meta[0]['arm_meta']) && $result_arm_meta[0]['arm_meta'] > 0)
	                                    {
	                                        esc_html_e('ARMember Admin', 'armember-membership');
	                                    } else {
											esc_html_e('Wordpress default', 'armember-membership');
	                                    }
	                                }
								}
								?></td>                        		
							</tr>
							<?php
							$arm_social_profiles_field_data = '';
							echo apply_filters( 'arm_admin_view_member_get_social_profile_data', $arm_social_profiles_field_data, $user_id); //phpcs:ignore
						?>	
						</table>
					</div>					
					<div class="armclear"></div>
					<?php                                
                                        
					$plan_id_name_array = $arm_subscription_plans->arm_get_plan_name_by_id_from_array();                                        
                    
					$membership_history = $arm_subscription_plans->arm_get_user_membership_history($user_id, 1, 5, $plan_id_name_array);
					?>
					<?php if(!empty($membership_history)): ?>
						<div class="arm_view_member_sub_title"><?php esc_html_e('Membership History','armember-membership'); ?></div>
						<div class="arm_view_member_sub_content arm_membership_history_list armPageContainer">
							<?php echo $membership_history; //phpcs:ignore?>
						</div>
						<div class="armclear"></div>
					<?php endif;?>
					<?php
					$user_logs = $arm_transaction->arm_get_user_transactions_with_pagging($user_id, 1, 5, $plan_id_name_array);
					?>
					<?php  if(!empty($user_logs)): ?>
						<div class="arm_view_member_sub_title"><?php esc_html_e('Payment History','armember-membership'); ?></div>
							<div class="arm_view_member_sub_content arm_membership_history_list armPageContainer">
							<?php echo $user_logs; //phpcs:ignore?>
						</div>
						<div class="armclear"></div>
					<?php endif;?>
					<div class="armclear"></div>
					<?php
					$arm_member_plans_details = '';
					echo apply_filters('arm_view_members_memberships_details',$arm_member_plans_details,$user_id,$plan_id_name_array); //phpcs:ignore
					?>
 					<?php
						$arm_member_details = "";
						$arm_member_details = apply_filters('arm_view_member_details_outside', $arm_member_details, $user_id, $plan_id_name_array);
						echo $arm_member_details; //phpcs:ignore
					?>	
					<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
					<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
				</div>
            </form>
        </div>
        <div class="armclear"></div>
		<div class="arm_members_activities_detail_container"></div>
		<div id="arm_profile_directory_template_preview" class="arm_profile_directory_template_preview"></div>
    </div>

</div>