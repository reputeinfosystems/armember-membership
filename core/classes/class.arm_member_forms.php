<?php 
if ( ! class_exists( 'ARM_member_forms_Lite' ) ) {

	class ARM_member_forms_Lite {

		function __construct() {
			global $wpdb, $ARMemberLite, $arm_slugs;
			add_action( 'wp_ajax_save_member_forms', array( $this, 'save_member_forms' ) );

			add_action( 'wp_ajax_arm_delete_form_field', array( $this, 'arm_delete_form_field' ) );
			add_action( 'wp_ajax_arm_create_new_field', array( $this, 'arm_create_new_field' ) );
			add_action( 'wp_ajax_arm_get_updated_social_profile_fields_html', array( $this, 'arm_get_updated_social_profile_fields_html' ) );
			add_action( 'wp_ajax_arm_get_updated_field_html', array( $this, 'arm_get_updated_field_html' ) );
			add_action( 'wp_ajax_arm_roles_field_options', array( $this, 'arm_roles_field_options' ) );
			add_action( 'wp_ajax_arm_prefix_suffix_field_html', array( $this, 'arm_prefix_suffix_field_html' ) );
			add_action( 'wp_ajax_arm_ajax_generate_form_styles', array( $this, 'arm_ajax_generate_form_styles' ) );
			/* Member Forms Shortcode Ajax Action */
			add_action( 'wp_ajax_arm_shortcode_form_ajax_action', array( $this, 'arm_shortcode_form_ajax_action' ) );
			add_action( 'wp_ajax_nopriv_arm_shortcode_form_ajax_action', array( $this, 'arm_shortcode_form_ajax_action' ) );
			/* Check Already Exist Field Value */
			add_action( 'wp_ajax_arm_check_exist_field', array( $this, 'arm_check_exist_field' ) );
			add_action( 'wp_ajax_nopriv_arm_check_exist_field', array( $this, 'arm_check_exist_field' ) );
			/* Remove Uploaded File */
			add_action( 'wp_ajax_arm_remove_uploaded_file', array( $this, 'arm_remove_uploaded_file' ) );
			add_action( 'wp_ajax_nopriv_arm_remove_uploaded_file', array( $this, 'arm_remove_uploaded_file' ) );

			add_action( 'wp_ajax_arm_get_all_preset_fields', array( $this, 'arm_get_all_preset_fields' ) );

			/* Shortcode For Member Forms */
			add_shortcode( 'arm_form', array( $this, 'arm_form_shortcode_func' ) );
			add_shortcode( 'arm_edit_profile', array( $this, 'arm_edit_profile_shortcode_func' ) );
			add_shortcode( 'arm_logout', array( $this, 'arm_logout_shortcode_func' ) );

			add_filter( 'arm_change_field_options', array( $this, 'arm_filter_form_field_options' ) );
			add_action( 'arm_before_render_form', array( $this, 'arm_check_form_include_js_css' ), 10, 2 );

			add_action( 'arm_member_update_meta', array( $this, 'arm_member_update_meta_details' ), 10, 3 );
			add_action( 'arm_admin_save_member_details', array( $this, 'arm_admin_save_member_details' ) );

			add_action( 'wp_ajax_arm_get_spf_in_tinymce', array( $this, 'arm_get_spf_in_tinymce' ) );
			/*
			 Insert Login History When user logged in */
			// add_action('set_logged_in_cookie', array($this, 'arm_add_login_history_for_set_logged_in_cookie'), 10, 5);
			/*
			 Update Logout Entery  */
			// add_action('clear_auth_cookie', array($this, 'arm_update_login_history'), 10);
			add_filter( 'registration_errors', array( $this, 'armforceError' ), 10, 1 );
			/* Reinitialize session for spam filter if any error occured while submit the form. for e.g. wrong password */
			add_action( 'wp_ajax_arm_reinit_session', array( $this, 'arm_reinit_session_filter_var' ) );
			add_action( 'wp_ajax_nopriv_arm_reinit_session', array( $this, 'arm_reinit_session_filter_var' ) );

			add_action( 'wp_ajax_arm_reinit_session_multiple_form', array( $this, 'arm_reinit_session_filter_var_multiple_form' ) );
			add_action( 'wp_ajax_nopriv_arm_reinit_session_multiple_form', array( $this, 'arm_reinit_session_filter_var_multiple_form' ) );

			add_filter( 'arm_change_popup_form_content', array( $this, 'arm_change_content_after_display_form_function' ), 10, 4 );
			add_action( 'arm_remove_third_party_error', array( $this, 'arm_remove_bot_error' ), 10, 1 );
			add_action('init', array($this, 'arm_auto_lock_shared_account'));
			add_filter( 'send_password_change_email', array( $this, 'arm_send_change_password_default_email' ), 10, 3 );
			add_filter( 'send_email_change_email', array( $this, 'arm_send_change_password_default_email' ), 10, 3 );
			add_filter( 'the_content', array( $this, 'arm_the_filtered_content' ) );

			add_action( 'wp_ajax_arm_update_preset_form_fields', array( $this, 'arm_update_preset_form_fields' ) );
			add_filter( 'get_user_metadata', array( $this, 'armember_update_user_data' ), 10, 4 );
		}
		function arm_get_field_option_by_meta( $meta_key = '', $form_id = 0 ) {
			global $wpdb, $ARMemberLite;
			$meta = ( isset( $_GET['meta'] ) ) ? sanitize_text_field( $_GET['meta'] ) : $meta_key; //phpcs:ignore
			$where = $wpdb->prepare('WHERE `arm_form_field_slug` = %s ',$meta);
			if ( $form_id != 0 ) {
				$where .= $wpdb->prepare(' AND arm_form_field_form_id = %d' , $form_id);
			}

			$opts_arr = $wpdb->get_results( 'SELECT `arm_form_field_option` FROM `' . $ARMemberLite->tbl_arm_form_field . "` ".$where, ARRAY_A );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_form_fieldis a table name
			$opts     = array();
			if ( ! empty( $opts_arr ) ) {
				$opts = array_column( $opts_arr, 'arm_form_field_option' );
				$opts = maybe_unserialize( $opts[0] );
			}
			if ( isset( $_GET['meta'] ) ) { //phpcs:ignore
				$opts = $opts['options'];
				echo wp_json_encode( $opts );
				exit;
			} else {
				return $opts;
			}
		}
		function arm_update_preset_form_fields() {
			global $arm_member_forms, $ARMemberLite, $arm_capabilities_global;

			$response = array( 'type' => 'error' );

			if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'arm_update_preset_form_fields' ) { //phpcs:ignore

				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce

				$arm_posted_data = isset( $_POST['preset_fields'] )? $_POST['preset_fields'] : array(); //phpcs:ignore
				$arm_posted_data  = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend'), $arm_posted_data ); //phpcs:ignore
				$presetFormFields = get_option( 'arm_preset_form_fields', '' );
				$dbFormFields     = maybe_unserialize( $presetFormFields );
				$new_options      = array();

				if ( isset( $arm_posted_data ) && ! empty( $arm_posted_data ) ) {
					foreach ( $arm_posted_data as $key => $arm_field_key ) {
						$label   = isset( $arm_field_key['label'] ) ? sanitize_text_field( $arm_field_key['label'] ) : ''; //phpcs:ignore
						$options = isset( $arm_field_key['options'] ) ? $arm_field_key['options'] : '';

						if ( ! empty( $options ) ) {
							$options     = array_map( 'trim', explode( "\n", $options ) );
							$new_options = $options;
							if ( is_array( $options ) ) {
								$new_options = array();
								foreach ( $options as $data ) {
									if ( $data != '' ) {
										$new_options[] = stripslashes( $data );
									}
								}
							}
						}

						if ( isset( $dbFormFields['default'][ $key ] ) ) {
							if ( isset( $dbFormFields['default'][ $key ]['label'] ) ) {
								$dbFormFields['default'][ $key ]['label'] = $label;
							}
							if ( in_array( $dbFormFields['default'][ $key ]['type'], array( 'radio', 'checkbox', 'select' ) ) ) {
								if ( isset( $dbFormFields['default'][ $key ]['options'] ) ) {
									$dbFormFields['default'][ $key ]['options'] = $new_options;
								}
							}
						} elseif ( isset( $dbFormFields['other'][ $key ] ) ) {
							if ( isset( $dbFormFields['other'][ $key ]['label'] ) ) {
								$dbFormFields['other'][ $key ]['label'] = $label;
							}
							if ( in_array( $dbFormFields['other'][ $key ]['type'], array( 'radio', 'checkbox', 'select' ) ) ) {
								if ( isset( $dbFormFields['other'][ $key ]['options'] ) ) {
									$dbFormFields['other'][ $key ]['options'] = $new_options;
								}
							}
						}
					}
				}

					update_option( 'arm_preset_form_fields', $dbFormFields );
					$response = array( 'type' => 'success' );
			}
			echo wp_json_encode( $response );
			die();
		}

		function arm_get_all_preset_fields() {
			global $arm_member_forms, $ARMemberLite, $arm_capabilities_global;

			if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'arm_get_all_preset_fields' ) { //phpcs:ignore
				$content      = '';
				$dbFormFields = $arm_member_forms->arm_get_db_form_fields( true );
				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce
				if ( ! empty( $dbFormFields ) ) {
					$content                                 .= '<table class="arm_preset_field_table" cellpadding="0" cellspacing="0">';
					$content                                 .= '<tr>';
											$content         .= '<th style="width: 250px;">';
													$content .= esc_html__( 'Field Key', 'armember-membership' );
											$content         .= '</th>';

											$content         .= '<th style="width: 290px;">';
													$content .= esc_html__( 'Field Label', 'armember-membership' );
											$content         .= '</th>';

											$content     .= '<th>';
												$content .= esc_html__( 'Field Options', 'armember-membership' );
											$content     .= '</th>';
									$content             .= '</tr>';
					foreach ( $dbFormFields as $meta_key => $field ) {
						$field_options = maybe_unserialize( $field );
						$field_options = apply_filters( 'arm_change_field_options', $field_options );
						$exclude_keys  = array(
							'repeat_pass',
							'arm_user_plan',
							'arm_last_login_ip',
							'arm_last_login_date',
							'roles',
							'section',
							'current_user_pass',
							'repeat_pass',
							'repeat_email',
							'social_fields',
							'avatar',
							'profile_cover',
						);
						$meta_key      = isset( $field_options['meta_key'] ) ? $field_options['meta_key'] : $field_options['id'];
						$label         = isset( $field_options['label'] ) ? $field_options['label'] : '';
						$options       = isset( $field_options['options'] ) ? $field_options['options'] : array();
						$type          = isset( $field_options['type'] ) ? $field_options['type'] : array();
						$old_options   = '';
						if ( ! empty( $options ) ) {
							foreach ( $options as $key => $opt ) {
								$opt          = stripslashes( $opt );
								$old_options .= "$opt\n";
							}
						}

						if ( ! in_array( $meta_key, $exclude_keys ) && ! in_array( $type, array( 'section', 'roles', 'html', 'hidden', 'submit', 'repeat_pass', 'repeat_email' ) ) ) {

									$content                 .= '<tr>';
											$content         .= '<td>';
													$content .= $meta_key;
											$content         .= '</td>';

											$content         .= '<td>';
													$content .= '<input type="text" name="preset_fields[' . esc_attr(trim( $meta_key )) . '][label]" value="' . esc_attr($label) . '" class="arm_preset_form_field">';
											$content         .= '</td>';

											$content .= '<td>';
							if ( in_array( $type, array( 'radio', 'checkbox', 'select' ) ) ) {
								$content   .= '<textarea class="arm_preset_form_field" name="preset_fields[' . esc_attr(trim( $meta_key )) . '][options]">' . $old_options . '</textarea>
                                                        <p class="description">';
								$content   .= esc_html__( 'You should place each option on a new line.', 'armember-membership' );
								  $content .= '<br/>';
								$content   .= esc_html__( 'Separate values format should be label:value.', 'armember-membership' );
								if ( trim( $meta_key ) == 'country' ) {
														$content .= '<br/>';
														$content .= '<font color="red">' . esc_html__( 'Please don\'t change value of country ID. For example: :1,:2,:3', 'armember-membership' ) . '</font>';
								}
								 $content .= '</p>';
								   // $content .= '<textarea name="">fdsfdsf</textarea>';
							}
											$content .= '</td>';
									$content         .= '</tr>';

						}
					}
					 $content .= '<table>';
				}
				echo $content; //phpcs:ignore
				die();
			}
		}

		function arm_the_filtered_content( $content ) {

			if ( isset( $_GET['arm-key'] ) && ! empty( $_GET['arm-key'] ) ) { //phpcs:ignore

				$chk_key    = stripslashes_deep( sanitize_text_field( $_GET['arm-key'] ) ); //phpcs:ignore
				$user_email = !empty( $_GET['email'] ) ? stripslashes_deep( sanitize_email( $_GET['email'] ) ) : ''; //phpcs:ignore

				$arm_message = $this->arm_verify_user_activation_for_front( $user_email, $chk_key );
				$message     = '';
				if ( $arm_message['status'] == 'error' ) {
					$message .= '<div class="arm_form_message_container1 arm_editor_form_fileds_container arm_editor_form_fileds_wrapper arm_account_verify_error_container"><div class="arm-df__fc--validation__wrap"><ul><li>';
					$message .= $arm_message['message'];
					$message .= '</li></ul></div></div>';
				} else {
					$message .= '<div class="arm_form_message_container1 arm_editor_form_fileds_container arm_editor_form_fileds_wrapper arm_account_verify_success_container"><div class="arm_success_msg"><ul><li>';
					$message .= $arm_message['message'];
					$message .= '</li></ul></div></div>';
				}
				$content = $message . $content;
			}
			return $content;
		}






		function arm_send_change_password_default_email( $return, $user, $userdata ) {
			$return = false;
			return $return;
		}

		function arm_remove_bot_error( $arm_lite_errors ) {
			if ( isset( $arm_lite_errors->errors['bot_error'] ) ) {
				unset( $arm_lite_errors->errors['bot_error'] );
			}
			return $arm_lite_errors;
		}

		function arm_change_content_after_display_form_function( $content, $form, $atts, $formRandomID = '' ) {

			global $arm_global_settings;
			if ( isset( $form ) && ! empty( $form ) ) {
				if ( is_user_logged_in() ) {

					$already_logged_in_msg = isset( $arm_global_settings->common_message['arm_armif_already_logged_in'] ) ? $arm_global_settings->common_message['arm_armif_already_logged_in'] : '';
					if ( in_array( $form->type, array( 'login', 'signin', 'logout', 'log-out', 'signout', 'sign-out' ) ) ) {
						$already_logged_in_message            = ( isset( $atts['logged_in_message'] ) && ! empty( $atts['logged_in_message'] ) ) ? $atts['logged_in_message'] : $already_logged_in_msg;
						return $already_logged_in_message_div = '<div class="arm_already_logged_in_message_popup" id="arm_already_logged_in_message_popup">' . esc_html($already_logged_in_message) . '</div>';
					}
					if ( ! is_admin() && in_array( $form->type, array( 'registration', 'forgot_password', 'lostpassword', 'retrievepassword' ) ) ) {

						$already_logged_in_message            = ( isset( $atts['logged_in_message'] ) && ! empty( $atts['logged_in_message'] ) ) ? $atts['logged_in_message'] : $already_logged_in_msg;
						return $already_logged_in_message_div = '<div class="arm_already_logged_in_message_popup" id="arm_already_logged_in_message_popup">' . esc_html($already_logged_in_message) . '</div>';
					}
				}
			}
			return $content;
		}

		function armforceError( $errors ) {
			if ( ! empty( $errors->errors ) ) {

				if ( count( $errors->errors ) == 1 && isset( $errors->errors['dm_ec_force_error'] ) ) {

					unset( $errors->errors['dm_ec_force_error'] );
				}
			}
			return $errors;
		}

		function arm_remove_uploaded_file() {
			global $wpdb, $ARMemberLite, $arm_slugs;
			
			$ARMemberLite->arm_check_user_cap( '', '1' ); //phpcs:ignore --Reason:Verifying nonce
			$denyExts = array( 'php', 'php3', 'php4', 'php5', 'pl', 'py', 'jsp', 'asp', 'exe', 'cgi', 'css', 'js', 'html', 'htm' );
			$ARMemberLite->arm_session_start(true);

			if ( ! empty( $_POST['file_name'] ) ) { //phpcs:ignore
				$file_name     = $ARMemberLite->arm_get_basename( sanitize_text_field( $_POST['file_name'] ) ); //phpcs:ignore
				$meta_field_name = !empty($_POST['meta_name']) ? sanitize_text_field( $_POST['meta_name'] ) : ''; //phpcs:ignore
				$meta_field_type = !empty($_POST['type']) ? sanitize_text_field( $_POST['type'] ) : ''; //phpcs:ignore
                if( $meta_field_type == 'profile_pic' || $meta_field_type == 'profile_cover' ) { //phpcs:ignore
                    switch ($meta_field_type) { //phpcs:ignore
                        case 'profile_pic':
                            $meta_field_name = "avatar";
                            break;
                        case 'profile_cover':
                            $meta_field_name = "profile_cover";
                            break;
                    }
                }
				$file_name_arm = substr( $file_name, 0, 3 );

				$checkext = explode( '.', $file_name );
				$ext      = strtolower( $checkext[ count( $checkext ) - 1 ] );

				if ( ! empty( $ext ) && ! in_array( $ext, $denyExts ) && !empty( $file_name ) && $file_name_arm == 'arm' ) {
					$file_path = MEMBERSHIPLITE_UPLOAD_DIR . '/' . $file_name;
					if ( file_exists($file_path) && !empty($meta_field_name) && !empty($_SESSION['arm_file_upload_arr'][$meta_field_name]) && ( $_SESSION['arm_file_upload_arr'][$meta_field_name]==$file_name ) ) { //phpcs:ignore
						@unlink( $file_path ); //phpcs:ignore

						if( is_user_logged_in() ) {
							if( isset( $_POST['type'] ) && $_POST['type'] == 'profile_cover' ) { //phpcs:ignore
								delete_user_meta( get_current_user_id(), 'profile_cover' );
								do_action( 'arm_remove_bp_profile_cover', get_current_user_id() );
								exit;
							}

							if( isset( $_POST['type'] ) && $_POST['type'] == 'profile_pic' ) { //phpcs:ignore
								do_action( 'arm_remove_bp_avatar', get_current_user_id() );
								delete_user_meta( get_current_user_id(), 'avatar' );

								$avatar = get_avatar( wp_get_current_user()->user_email, '200' );
								preg_match_all( "/src='([^']+)/", $avatar, $images );

								$avatar_url = isset( $images[1][0] ) ? $images[1][0] : '';
								echo esc_url($avatar_url);
								exit;
							}
						}
					}
					if (!empty($meta_field_name) && isset($_SESSION['arm_file_upload_arr'][$meta_field_name])){
                        unset($_SESSION['arm_file_upload_arr'][$meta_field_name]);
                    }
					else if (!empty($meta_field_name)){
						$_SESSION['arm_file_upload_arr'][$meta_field_name] = "-";
					}
				}
			}

			if ( ! empty( $_POST['file_url'] ) ) { //phpcs:ignore
				$file_name = $ARMemberLite->arm_get_basename(sanitize_text_field($_POST['file_url'])); //phpcs:ignore
                $file_name_arm = substr($file_name, 0,3);
		        $meta_field_name = !empty($_POST['meta_name']) ? sanitize_text_field( $_POST['meta_name'] ) : ''; //phpcs:ignore

				$checkext = explode( '.', $file_name );
				$ext      = strtolower( $checkext[ count( $checkext ) - 1 ] );

				if( ! empty( $ext ) && ! in_array( $ext, $denyExts ) && !empty( $file_name ) && $file_name_arm == 'arm' ) {
					$file_path = MEMBERSHIPLITE_UPLOAD_DIR . '/' . $ARMemberLite->arm_get_basename( sanitize_text_field( $file_name ) );
					
					if(file_exists( $file_path ) && !empty( $meta_field_name ) && !empty( $_SESSION['arm_file_upload_arr'][$meta_field_name] ) && ( $_SESSION['arm_file_upload_arr'][$meta_field_name]==$file_name ) ) { //phpcs:ignore
						unlink( $file_path ); //phpcs:ignore
					}
					if (!empty($meta_field_name) && isset($_SESSION['arm_file_upload_arr'][$meta_field_name])){
                        $_SESSION['arm_file_upload_arr'][$meta_field_name]="";
                    }
					echo '1';
					exit;
				}
			}
		}

		/**
		 * `[arm_logout]` shortcode function
		 */
		function arm_logout_shortcode_func( $atts, $content, $tag ) {
			/* ====================/.Begin Set Shortcode Attributes./==================== */
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}

			$atts              = shortcode_atts(
				array(
					'label'                  => esc_html__( 'Logout', 'armember-membership' ),
					'type'                   => 'link',
					'user_info'              => true,
					'redirect_to'            => '',
					'link_css'               => '',
					'link_hover_css'         => '',
					'logged_in_as_text'      => esc_html__( 'Logged in as', 'armember-membership' ),
					'logout_of_account_text' => esc_html__( 'Log out of this account?', 'armember-membership' ),
				),
				$atts,
				$tag
			);
			$atts['user_info'] = ( $atts['user_info'] === 'false' ) ? false : true;

			$atts = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $atts ); //phpcs:ignore

			/* ====================/.End Set Shortcode Attributes./==================== */
			global $wp, $wpdb, $current_user, $arm_slugs, $ARMemberLite, $arm_global_settings;
			$redirect_to = ( ! empty( $atts['redirect_to'] ) && $atts['redirect_to'] != '' ) ? $atts['redirect_to'] : ARMLITE_HOME_URL;
			if ( is_user_logged_in() ) {
				$user          = wp_get_current_user();
				$user_identity = '';
				if ( $user->exists() ) {
					$user_identity = $user->first_name . ' ' . $user->last_name;
					if ( empty( $user->first_name ) && empty( $user->last_name ) ) {
						$user_identity = $user->user_login;
					}
				}
				// $logout_url = wp_logout_url($redirect_to);
				$arm_wpnonce = wp_create_nonce( 'arm_wpnonce' );
				$logout_url    = add_query_arg(
					array(
						'arm_action'  => 'logout',
						'redirect_to' => $redirect_to,
						'arm_wpnonce' => $arm_wpnonce,
					),
					ARMLITE_HOME_URL
				);
				//$logout_url    = wp_nonce_url( $logout_url );
				$logoutWrapper = arm_generate_random_code();
				$content       = apply_filters( 'arm_before_logout_shortcode_content', $content, $atts );
				$content      .= '<div class="arm_logout_form_container" id="arm_logout_' . esc_attr($logoutWrapper) . '">';
				$btnStyle      = '';
				if ( ! empty( $atts['link_css'] ) ) {
					$btnStyle .= '#arm_logout_' . $logoutWrapper . ' .arm_logout_btn{' .  $atts['link_css'] . '}';
				}
				if ( ! empty( $atts['link_hover_css'] ) ) {
					$btnStyle .= '#arm_logout_' . $logoutWrapper . ' .arm_logout_btn:hover{' . $atts['link_hover_css']  . '}';
				}
				if ( ! empty( $btnStyle ) ) {
					$content .= '<style type="text/css">' . $btnStyle . '</style>';
				}
				if ( $atts['user_info'] ) {
					$content      .= '<span class="arm-logged-in-as">' . esc_html($atts['logged_in_as_text']) . ' <a href="' . esc_url(get_edit_user_link()) . '">' . esc_html($user_identity) . '</a>.</span>';
					$atts['label'] = $atts['label'] . '?';
				}
				if ( $atts['type'] == 'button' ) {
					$content .= '<form method="post" class="arm_logout" name="arm_logout" action="' . esc_attr($logout_url) . '" enctype="multipart/form-data">';
					$content .= '<button type="submit" class="arm_logout_btn arm_logout_button" title="' . esc_attr($atts['logout_of_account_text']) . '">' . esc_html($atts['label']) . '</button>';
					$content .= '</form>';
				} else {
					$content .= '<a href="' . esc_url($logout_url) . '" title="' . esc_attr($atts['logout_of_account_text']) . '" class="arm_logout_btn arm_logout_link">' . esc_html($atts['label']) . '</a>';
				}
				$content .= '</div>';
				$content  = apply_filters( 'arm_after_logout_shortcode_content', $content, $atts );
			}
			$ARMemberLite->arm_check_font_awesome_icons( $content );
			return do_shortcode( $content );
		}

		/**
		 * `[arm_edit_profile]` shortcode function
		 * Default: `[arm_edit_profile title="" message="Your profile has been updated successfully."]`
		 */
		function arm_edit_profile_shortcode_func( $atts, $content, $tag ) {
			/* ====================/.Begin Set Shortcode Attributes./==================== */
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}
			$atts                      = shortcode_atts(
				array(
					'title'                     => '',
					'form_id'                   => '',
					'submit_text'               => esc_html__( 'Update Profile', 'armember-membership' ),
					'message'                   => '',
					'class'                     => '',
					'form_position'             => 'center',
					'social_fields'             => '',
					'avatar_field'              => 'yes',
					'profile_cover_field'       => 'yes',
					'view_profile'              => false,
					'view_profile_link'         => esc_html__( 'View Profile', 'armember-membership' ),
					'profile_cover_title'       => '',
					'profile_cover_placeholder' => esc_html__( 'Drop file here or click to select', 'armember-membership' ),
				),
				$atts,
				$tag
			);

			$atts = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $atts ); //phpcs:ignore

			$atts['view_profile']      = ( $atts['view_profile'] === 'true' || $atts['view_profile'] == '1' ) ? true : false;
			$atts['view_profile_link'] = ( ! empty( $atts['view_profile_link'] ) ) ? $atts['view_profile_link'] : esc_html__( 'View Profile', 'armember-membership' );
			$atts['message']           = ( ! empty( $atts['message'] ) ) ? $atts['message'] : esc_html__( 'Your profile has been updated successfully.', 'armember-membership' );
			$atts['type']              = 'edit_profile';
			/* ====================/.End Set Shortcode Attributes./==================== */
			global $wp, $wpdb, $current_user, $ARMemberLite, $arm_global_settings;
			$content      = '';
			$formRandomID = '';
			if ( is_user_logged_in() ) {
				$default_form_id = $this->arm_get_default_form_id( 'registration' );
				$user_id         = get_current_user_id();
				if ( isset( $atts['form_id'] ) && ! empty( $atts['form_id'] ) ) {
					$user_form_id = $atts['form_id'];
				} else {
					$user_form_id = get_user_meta( $user_id, 'arm_form_id', true );
				}
				$form = new ARM_Form_Lite( 'id', $user_form_id );
				if ( ! $form->exists() || $form->type != 'registration' ) {
					$form = new ARM_Form_Lite( 'id', $default_form_id );
				}
				$form = apply_filters( 'arm_form_data_before_edit_profile_shortcode', $form, $atts );
				do_action( 'arm_before_render_edit_profile_form', $form, $atts );
				do_action( 'arm_before_render_form', $form, $atts );
				if ( $form->exists() && ! empty( $form->fields ) ) {
					$form_id           = $form->ID;
					$form_settings     = $form->settings;
					$ref_template      = $form->form_detail['arm_ref_template'];
					$form_style        = $form_settings['style'];
					$form_color_scheme = ! empty( $form_style['color_scheme'] ) ? $form_style['color_scheme'] : 'default';
					/* Form Classes */
					$form_style['button_position'] = ( ! empty( $form_style['button_position'] ) ) ? $form_style['button_position'] : 'left';
					$formRandomID                  = $form_id . '_' . arm_generate_random_code();
					$form_style_class              = ' arm_form_' . $form_id;
					$form_style_class             .= ' arm_form_layout_' . $form_style['form_layout'] . ' arm-default-form';

					if ( $form_style['form_layout'] == 'writer' ) {
						$form_style_class .= ' arm-material-style arm_materialize_form ';
					} elseif ( $form_style['form_layout'] == 'rounded' ) {
						$form_style_class .= ' arm-rounded-style ';
					} elseif ( $form_style['form_layout'] == 'writer_border' ) {
						$form_style_class .= ' arm--material-outline-style arm_materialize_form ';
					}
					$form_style_class .= ( $form_style['label_hide'] == '1' ) ? ' armf_label_placeholder' : '';
					$form_style_class .= ' armf_alignment_' . $form_style['label_align'];
					$form_style_class .= ' armf_layout_' . $form_style['label_position'];
					$form_style_class .= ' armf_button_position_' . $form_style['button_position'];
					$form_style_class .= ( $form_style['rtl'] == '1' ) ? ' arm_form_rtl' : ' arm_form_ltr';
					if ( is_rtl() ) {
						$form_style_class .= ' arm_rtl_site';
					}
					$form_style_class .= ' ' . $atts['class'];
					$form_attr         = ' name="arm_form" id="arm_form' . esc_attr($formRandomID) . '"';
					$ARMemberLite->arm_session_start();
					$captcha_code = arm_generate_captcha_code();
					if ( ! isset( $_SESSION['ARM_FILTER_INPUT'] ) ) {
						$_SESSION['ARM_FILTER_INPUT'] = array();
					}
					if ( isset( $_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] ) ) {
						unset( $_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] );
					}
					$_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] = $captcha_code;
					$_SESSION['ARM_VALIDATE_SCRIPT']               = true;

					$form_attr .= ' data-random-id="' . esc_attr($formRandomID) . '" ';

					$general_settings = isset( $arm_global_settings->global_settings ) ? $arm_global_settings->global_settings : array();
					$spam_protection  = isset( $general_settings['spam_protection'] ) ? $general_settings['spam_protection'] : '';
					if ( ! empty( $spam_protection ) ) {
						$form_attr .= ' data-submission-key="' . esc_attr($captcha_code) . '" ';
					}

					/* Add Form Style on front page. */
					if ( ! empty( $form_style['form_layout'] ) && $form_style['form_layout'] != '' ) {
						$form_style_class .= ' arm_form_style_' . esc_attr($form_color_scheme);
					}
					$form_css = $this->arm_ajax_generate_form_styles( $form_id, $form_settings, $atts, $ref_template );
					/* Form Inner Content */
					$field_position = ! empty( $form_style['field_position'] ) ? $form_style['field_position'] : 'left';
					$validation_pos = ! empty( $form_style['validation_position'] ) ? $form_style['validation_position'] : 'bottom';
					$content        = apply_filters( 'arm_change_content_before_display_form', $content, 0, $atts );
					$content       .= $form_css['arm_link'];
					$content       .= '<style type="text/css" id="arm_form_style_' . esc_attr($form_id) . '">' . $form_css['arm_css'] . '</style>';
					$content       .= '<div class="arm-form-container">';
					$content       .= '<div class="arm_form_message_container arm_editor_form_fileds_container arm_editor_form_fileds_wrapper arm_form_' . esc_attr($form_id) . '"></div>';
					$content       .= '<div class="armclear"></div>';
					$content       .= '<form method="post" class="arm_form arm_form_edit_profile ' . esc_attr($form_style_class) . '" enctype="multipart/form-data" novalidate ' . $form_attr . '>';
					$content       .= '<div class="arm-df-wrapper arm_msg_pos_' . esc_attr($validation_pos) . '">';
					/* 20aug2016 */
					$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
					$general_settings    = $all_global_settings['general_settings'];
					$enable_crop         = isset( $general_settings['enable_crop'] ) ? $general_settings['enable_crop'] : 0;

					global $arm_is_enable_crop;
					if ( $enable_crop && empty( $arm_is_enable_crop ) ) {
						$arm_is_enable_crop = 1;
						$content           .= '<div id="arm_crop_div_wrapper" class="arm_crop_div_wrapper"  style="display:none;" data_id="' . esc_attr($formRandomID) . '">';
						$content           .= '<div id="arm_crop_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
						$content           .= '<div id="arm_crop_div" class="arm_crop_div" data_id="' . esc_attr($formRandomID) . '"><img id="arm_crop_image" class="arm_crop_image" src="" style="max-width:100%;" data_id="' . esc_attr($formRandomID) . '"/></div>'; //phpcs:ignore 
						$content           .= '<div class="arm_skip_avtr_crop_button_wrapper_admn arm_inht_front_usr_avtr">';
						$content           .= '<button class="arm_crop_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Crop', 'armember-membership' ) . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
						$content           .= '<button class="arm_clear_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Clear', 'armember-membership' ) . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
						$content           .= '<button class="arm_zoom_button arm_zoom_plus arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" data-method="zoom" data-option="0.1" title="' . esc_html__( 'Zoom In', 'armember-membership' ) . '"><span class="armfa armfa-search-plus"></span></button>';
						$content           .= '<button class="arm_zoom_button arm_zoom_minus arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" data-method="zoom" data-option="-0.1" title="' . esc_html__( 'Zoom Out', 'armember-membership' ) . '"><span class="armfa armfa-search-minus"></span></button>';
						$content           .= '<button class="arm_rotate_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" data-method="rotate" data-option="90" title="' . esc_html__( 'Rotate', 'armember-membership' ) . '"><span class="armfa armfa-rotate-right"></span></button>';
						$content           .= '<button class="arm_reset_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Reset', 'armember-membership' ) . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
						$content           .= '<button id="arm_skip_avtr_crop_nav_front" class="arm_avtr_done_front" data_id="' . esc_attr($formRandomID) . '">' . esc_html__( 'Done', 'armember-membership' ) . '</button>';
						$content           .= '</div>';
						$content           .= '<p class="arm_discription">' . sprintf( addslashes(esc_html__('(Use Cropper to set image and %suse mouse scroller for zoom image.)', 'armember-membership') ),'<br/>' ) . '</p>'; //phpcs:ignore
						$content           .= '</div>';

						$content .= '<div id="arm_crop_cover_div_wrapper" class="arm_crop_cover_div_wrapper" style="display:none;" data_id="' . esc_attr($formRandomID) . '">';
						$content .= '<div id="arm_crop_cover_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
						$content .= '<div id="arm_crop_cover_div" class="arm_crop_cover_div" data_id="' . esc_attr($formRandomID) . '"><img id="arm_crop_cover_image" class="arm_crop_cover_image" src=""  style="max-width:100%;max-height:100%;" data_id="' . esc_attr($formRandomID) . '" /></div>'; //phpcs:ignore 
						$content .= '<div class="arm_skip_cvr_crop_button_wrapper_admn arm_inht_front_usr_cvr arm_inht_front_usr_profile_cvr">';
						$content .= '<button class="arm_crop_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Crop', 'armember-membership' ) . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
						$content .= '<button class="arm_clear_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Clear', 'armember-membership' ) . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
						$content .= '<button class="arm_zoom_cover_button arm_zoom_plus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="0.1" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Zoom In', 'armember-membership' ) . '"><span class="armfa armfa-search-plus"></span></button>';
						$content .= '<button class="arm_zoom_cover_button arm_zoom_minus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="-0.1" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Zoom Out', 'armember-membership' ) . '"><span class="armfa armfa-search-minus"></span></button>';
						$content .= '<button class="arm_rotate_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" data-method="rotate" data-option="90" title="' . esc_html__( 'Rotate', 'armember-membership' ) . '"><span class="armfa armfa-rotate-right"></span></button>';
						$content .= '<button class="arm_reset_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Reset', 'armember-membership' ) . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
						$content .= '<button data_id="' . esc_attr($formRandomID) . '" id="arm_skip_cvr_crop_nav_front" class="arm_cvr_done_front">' . esc_html__( 'Done', 'armember-membership' ) . '</button>';
						$content .= '</div>';
						$content .= '<p class="arm_discription">' . esc_html__( '(Use Cropper to set image and use mouse scroller for zoom image.)', 'armember-membership' ) . '</p>';
						$content .= '</div>';
					}
					$nonce= wp_create_nonce( 'arm_wp_nonce' );
					$content .='<input type="hidden" name="arm_wp_nonce" value='. esc_attr( $nonce ) .'>';
					$content .='<input type="hidden" name="arm_wp_nonce_check" value="1">';

					$content .= '<div class="arm-df__fields-wrapper arm-df__fields-wrapper_edit_profile arm_field_position_' . esc_attr( $field_position ) . '" data-form_id="edit_profile">';
					if ( $atts['view_profile'] ) {
						$profile_link = $arm_global_settings->arm_get_user_profile_url( $user_id, '1' );
						$content     .= '<div class="arm_view_profile_link_container">';
						$content     .= '<a href="' . esc_url($profile_link) . '" class="arm_view_profile_link">' . esc_html($atts['view_profile_link']) . '</a>';
						$content     .= '</div>';
					}
					if ( ! empty( $atts['title'] ) ) {
						$form_title_position = ( ! empty( $form_style['form_title_position'] ) ) ? $form_style['form_title_position'] : 'left';
						$content            .= '<div class="arm-df__heading armalign' . esc_attr($form_title_position) . '">';
						$content            .= '<span class="arm-df__heading-text">' . esc_html($atts['title']) . '</span>';
						$content            .= '</div>';
					}
					$content .= $this->arm_member_form_get_single_form_fields( $form, $atts, $formRandomID );
					$content .= '<div class="armclear"></div>';
					if ( isset( $form_settings['is_hidden_fields'] ) && $form_settings['is_hidden_fields'] == '1' ) {
						if ( isset( $form_settings['hidden_fields'] ) && ! empty( $form_settings['hidden_fields'] ) ) {
							foreach ( $form_settings['hidden_fields'] as $hiddenF ) {
								$hiddenMetaKey = ( isset( $hiddenF['meta_key'] ) && ! empty( $hiddenF['meta_key'] ) ) ? $hiddenF['meta_key'] : sanitize_title( $hiddenF['title'] );
								$hiddenValue   = get_user_meta( $user_id, $hiddenMetaKey, true );
								$hiddenValue   = ( ! empty( $hiddenValue ) ) ? $hiddenValue : $hiddenF['value'];
								$content      .= '<input type="hidden" name="' . esc_attr( $hiddenMetaKey ) . '" value="' . esc_attr( $hiddenValue ) . '"/>';
							}
						}
					}
					$content .= '</div>';
					$content .= '<div class="armclear"></div>';
					$content .= '<input type="hidden" name="arm_action" value="edit_profile"/>';
					$content .= '<input type="hidden" name="isAdmin" value="' . ( ( is_admin() ) ? '1' : '0' ) . '"/>';
					$content .= '<input type="hidden" name="arm_parent_form_id" value="' . esc_attr( $form_id ) . '"/>';
					$content .= '<input type="hidden" name="arm_success_message" value="' . esc_attr( $atts['message'] ) . '"/>';

					$content .= '<input type="hidden" name="id" value="' . esc_attr( $user_id ) . '"/>';
					$content .= do_shortcode( '[armember_spam_filters]' );
					$content .= '</div>';
					$content .= '</form>';
					$content .= '<div class="armclear"></div>';

					global $arm_lite_members_activity, $arm_lite_version;
					$arm_request_version = get_bloginfo( 'version' );

					$content .= '</div>';
					$content  = apply_filters( 'arm_change_content_after_display_form', $content, 0, $atts );
				}
			} else {
				$default_login_form_id = $this->arm_get_default_form_id( 'login' );

				$arm_all_global_settings = $arm_global_settings->arm_get_all_global_settings();

				$page_settings    = $arm_all_global_settings['page_settings'];
				$general_settings = $arm_all_global_settings['general_settings'];

				$login_page_id = ( isset( $page_settings['login_page_id'] ) && $page_settings['login_page_id'] != '' && $page_settings['login_page_id'] != 404 ) ? $page_settings['login_page_id'] : 0;
				if ( $login_page_id == 0 ) {

					if ( $general_settings['hide_wp_login'] == 1 ) {
						$login_page_url = ARMLITE_HOME_URL;
					} else {
						$referral_url   = wp_get_current_page_url();
						$referral_url   = ( ! empty( $referral_url ) && $referral_url != '' ) ? $referral_url : wp_get_current_page_url();
						$login_page_url = wp_login_url( $referral_url );
					}
				} else {
					$login_page_url = get_permalink( $login_page_id ) . '?arm_redirect=' . urlencode( wp_get_current_page_url() );
				}
				if ( is_home() ) {
					return '';
				} else {
					if ( preg_match_all( '/arm_redirect/', $login_page_url, $matche ) < 2 ) {
						wp_redirect( $login_page_url );
					}
				}
			}
			$ARMemberLite->enqueue_angular_script();
			$ARMemberLite->arm_check_font_awesome_icons( $content );

			$isEnqueueAll = $arm_global_settings->arm_get_single_global_settings( 'enqueue_all_js_css', 0 );
			if ( $isEnqueueAll == '1' ) {
				if ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) {
					$content .= '<script type="text/javascript" data-cfasync="false">
									jQuery(document).ready(function (){
										arm_do_bootstrap_angular();
                                        ARMFormInitValidation("arm_form' . $formRandomID . '");
									});';
					$content .= '</script>';
				}
			}

			$inbuild     = '';
			$hiddenvalue = '';
			global $arm_lite_members_activity, $arm_lite_version;
			$arm_request_version = get_bloginfo( 'version' );
			$setact              = 0;
			global $check_version;
			$setact = $arm_lite_members_activity->$check_version();

			if ( $setact != 1 ) {
				$inbuild = ' (U)';
			}

			$hiddenvalue = '  
            <!--Plugin Name: ARMember    
                Plugin Version: ' . get_option( 'armlite_version' ) . ' ' . $inbuild . '
                Developed By: Repute Infosystems
                Developer URL: http://www.reputeinfosystems.com/
            -->';

			return $content . $hiddenvalue;
		}

		function arm_profile_detail_shortcode_func( $atts, $content, $tag ) {
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}

			$atts = shortcode_atts(
				array(
					'id'            => '',
					'form_id'       => '',
					'form_position' => 'center',
				),
				$atts,
				$tag
			);

			if ( '' == $atts['id'] && '' == $atts['form_id'] ) {
				return;
			}

			global $wp, $wpdb, $current_user, $ARMemberLite, $arm_global_settings;

			$content      = '';
			$formRandomID = '';

			if ( is_user_logged_in() ) {
				$default_form_id = $atts['id'];
				$user_id         = get_current_user_id();
				if ( ! empty( $atts['id'] ) ) {
					$user_form_id = $atts['id'];
				} elseif ( ! empty( $atts['form_id'] ) ) {
					$user_form_id = $atts['form_id'];

				}

				$form = new ARM_Form_Lite( 'id', $user_form_id );

				if ( ! $form->exists() || $form->type != 'edit_profile' ) {
					return;
				}

				$success_message = isset( $form->settings['edit_success_message'] ) ? $form->settings['edit_success_message'] : esc_html__( 'Your profile updated successfully', 'armember-membership' );

				$form = apply_filters( 'arm_form_data_before_edit_profile_shortcode', $form, $atts );
				do_action( 'arm_before_render_edit_profile_form', $form, $atts );
				do_action( 'arm_before_render_form', $form, $atts );
				if ( $form->exists() && ! empty( $form->fields ) ) {
					$form_id           = $form->ID;
					$form_settings     = $form->settings;
					$ref_template      = $form->form_detail['arm_ref_template'];
					$form_style        = $form_settings['style'];
					$form_color_scheme = ! empty( $form_style['color_scheme'] ) ? $form_style['color_scheme'] : 'default';
					/* Form Classes */
					$form_style['button_position'] = ( ! empty( $form_style['button_position'] ) ) ? $form_style['button_position'] : 'left';
					$formRandomID                  = $form_id . '_' . arm_generate_random_code();
					$form_style_class              = ' arm_form_' . $form_id;
					$form_style_class             .= ' arm_form_layout_' . $form_style['form_layout'];

					if ( $form_style['form_layout'] == 'writer' ) {
						$form_style_class .= ' arm-default-form arm-material-style arm_materialize_form ';
					} elseif ( $form_style['form_layout'] == 'rounded' ) {
						$form_style_class .= ' arm-default-form arm-rounded-style ';
					} elseif ( $form_style['form_layout'] == 'writer_border' ) {
						$form_style_class .= ' arm-default-form arm--material-outline-style arm_materialize_form ';
					} else {
						$form_style_class .= ' arm-default-form ';
					}

					$form_style_class .= ( $form_style['label_hide'] == '1' ) ? ' armf_label_placeholder' : '';
					$form_style_class .= ' armf_alignment_' . $form_style['label_align'];
					$form_style_class .= ' armf_layout_' . $form_style['label_position'];
					$form_style_class .= ' armf_button_position_' . $form_style['button_position'];
					$form_style_class .= ( $form_style['rtl'] == '1' ) ? ' arm_form_rtl' : ' arm_form_ltr';
					if ( is_rtl() ) {
						$form_style_class .= ' arm_rtl_site';
					}
					// $form_style_class .= ' ' . $atts['class'];
					$form_attr = ' name="arm_form" id="arm_form' . esc_attr($formRandomID) . '"';

					$captcha_code = arm_generate_captcha_code();
					if ( ! isset( $_SESSION['ARM_FILTER_INPUT'] ) ) {
						$_SESSION['ARM_FILTER_INPUT'] = array();
					}
					if ( isset( $_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] ) ) {
						unset( $_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] );
					}
					$_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] = $captcha_code;
					$_SESSION['ARM_VALIDATE_SCRIPT']               = true;

					$form_attr .= ' data-random-id="' . esc_attr($formRandomID) . '" ';

					$general_settings = isset( $arm_global_settings->global_settings ) ? $arm_global_settings->global_settings : array();
					$spam_protection  = isset( $general_settings['spam_protection'] ) ? $general_settings['spam_protection'] : '';
					if ( ! empty( $spam_protection ) ) {
						$form_attr .= ' data-submission-key="' . esc_attr($captcha_code) . '" ';
					}

					/* Add Form Style on front page. */
					if ( ! empty( $form_style['form_layout'] ) && $form_style['form_layout'] != '' ) {
						$form_style_class .= ' arm_form_style_' . $form_color_scheme;
					}
					if ( ! empty( $form_style['validation_type'] ) && $form_style['validation_type'] == 'standard' ) {
						$form_style_class .= ' arm_standard_validation_type ';
						$validation_pos    = 'bottom';
					}
					$form_css = $this->arm_ajax_generate_form_styles( $form_id, $form_settings, $atts, $ref_template );
					/* Form Inner Content */
					$field_position  = ! empty( $form_style['field_position'] ) ? $form_style['field_position'] : 'left';
					$validation_type = ! empty( $form_style['validation_type'] ) ? $form_style['validation_type'] : 'modern';
					$validation_pos  = ! empty( $form_style['validation_position'] && $validation_type != 'standard' ) ? $form_style['validation_position'] : 'bottom';
					$content         = apply_filters( 'arm_change_content_before_display_form', $content, 0, $atts );
					$content        .= $form_css['arm_link'];
					$content        .= '<style type="text/css" id="arm_form_style_' . esc_attr($form_id) . '">' . $form_css['arm_css'] . '</style>';
					$content        .= '<div class="arm-form-container">';
					$content        .= '<div class="arm_form_message_container arm_editor_form_fileds_container arm_editor_form_fileds_wrapper arm_form_' . esc_attr($form_id) . '"></div>';
					$content        .= '<div class="armclear"></div>';
					$content        .= '<form method="post" class="arm_form arm_form_edit_profile ' . $form_style_class . '" enctype="multipart/form-data" novalidate ' . $form_attr . '>';
					$content        .= '<div class="arm-df-wrapper arm_msg_pos_' . esc_attr($validation_pos) . '">';
					/* 20aug2016 */

					$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
					$general_settings    = $all_global_settings['general_settings'];
					$enable_crop         = isset( $general_settings['enable_crop'] ) ? $general_settings['enable_crop'] : 0;

					global $arm_is_enable_crop;
					$nonce = wp_create_nonce( 'arm_wp_nonce' );
					$content           .= '<input type="hidden" name="arm_wp_nonce" value="'. esc_attr( $nonce ) .'"/>';
					if ( $enable_crop && empty( $arm_is_enable_crop ) ) {
						$arm_is_enable_crop = 1;
						$content           .= '<div id="arm_crop_div_wrapper" class="arm_crop_div_wrapper"  style="display:none;" data_id="' . esc_attr($formRandomID) . '">';
						$content           .= '<div id="arm_crop_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
						$content           .= '<div id="arm_crop_div" class="arm_crop_div" data_id="' . esc_attr($formRandomID) . '"><img id="arm_crop_image" class="arm_crop_image" src="" style="max-width:100%;" data_id="' . esc_attr($formRandomID) . '"/></div>'; //phpcs:ignore 
						$content           .= '<div class="arm_skip_avtr_crop_button_wrapper_admn arm_inht_front_usr_avtr">';
						$content           .= '<button class="arm_crop_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Crop', 'armember-membership' ) . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
						$content           .= '<button class="arm_clear_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Clear', 'armember-membership' ) . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
						$content           .= '<button class="arm_zoom_button arm_zoom_plus arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" data-method="zoom" data-option="0.1" title="' . esc_html__( 'Zoom In', 'armember-membership' ) . '"><span class="armfa armfa-search-plus"></span></button>';
						$content           .= '<button class="arm_zoom_button arm_zoom_minus arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" data-method="zoom" data-option="-0.1" title="' . esc_html__( 'Zoom Out', 'armember-membership' ) . '"><span class="armfa armfa-search-minus"></span></button>';
						$content           .= '<button class="arm_rotate_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" data-method="rotate" data-option="90" title="' . esc_html__( 'Rotate', 'armember-membership' ) . '"><span class="armfa armfa-rotate-right"></span></button>';
						$content           .= '<button class="arm_reset_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Reset', 'armember-membership' ) . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
						$content           .= '<button id="arm_skip_avtr_crop_nav_front" class="arm_avtr_done_front" data_id="' . esc_attr($formRandomID) . '">' . esc_html__( 'Done', 'armember-membership' ) . '</button>';
						$content           .= '</div>';
						$content           .= '<p class="arm_discription">(' . sprintf( addslashes( esc_html__( 'Use Cropper to set image and %1$s use mouse scroller for zoom image.', 'armember-membership' ) ), '<br/>') . ')</p>'; //phpcs:ignore
						$content           .= '</div>';

						$content .= '<div id="arm_crop_cover_div_wrapper" class="arm_crop_cover_div_wrapper" style="display:none;" data_id="' . esc_attr($formRandomID) . '">';
						$content .= '<div id="arm_crop_cover_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
						$content .= '<div id="arm_crop_cover_div" class="arm_crop_cover_div" data_id="' . esc_attr($formRandomID) . '"><img id="arm_crop_cover_image" class="arm_crop_cover_image" src="" style="max-width:100%;max-height:100%;" data_id="' . esc_attr($formRandomID) . '" /></div>'; //phpcs:ignore 
						$content .= '<div class="arm_skip_cvr_crop_button_wrapper_admn arm_inht_front_usr_cvr arm_inht_front_usr_profile_cvr">';
						$content .= '<button class="arm_crop_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Crop', 'armember-membership' ) . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
						$content .= '<button class="arm_clear_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Clear', 'armember-membership' ) . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
						$content .= '<button class="arm_zoom_cover_button arm_zoom_plus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="0.1" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Zoom In', 'armember-membership' ) . '"><span class="armfa armfa-search-plus"></span></button>';
						$content .= '<button class="arm_zoom_cover_button arm_zoom_minus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="-0.1" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Zoom Out', 'armember-membership' ) . '"><span class="armfa armfa-search-minus"></span></button>';
						$content .= '<button class="arm_rotate_cover_button arm_img_cover_setting armhelptip tipso_style" data-method="rotate" data-option="90" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Rotate', 'armember-membership' ) . '"><span class="armfa armfa-rotate-right"></span></button>';
						$content .= '<button class="arm_reset_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Reset', 'armember-membership' ) . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
						$content .= '<button data_id="' . esc_attr($formRandomID) . '" id="arm_skip_cvr_crop_nav_front" class="arm_cvr_done_front">' . esc_html__( 'Done', 'armember-membership' ) . '</button>';
						$content .= '</div>';
						$content .= '<p class="arm_discription">' . esc_html__( '(Use Cropper to set image and use mouse scroller for zoom image.)', 'armember-membership' ) . '</p>';
						$content .= '</div>';
					}

					$content .= '<div class="arm-df__fields-wrapper arm-df__fields-wrapper_edit_profile arm_field_position_' . esc_attr($field_position) . '" data-form_id="edit_profile">';

					if ( isset( $form_settings['view_profile_link'] ) && '1' == $form_settings['view_profile_link'] ) {
						$profile_link = $arm_global_settings->arm_get_user_profile_url( $user_id, '1' );
						$content     .= '<div class="arm_view_profile_link_container">';
						$content     .= '<a href="' . esc_url($profile_link) . '" class="arm_view_profile_link">' . $form_settings['arm_view_profile_link_label'] . '</a>';
						$content     .= '</div>';
					}

					if ( ! empty( $form->name ) && empty( $form->settings['hide_title'] ) ) {
						$form_title_position = ( ! empty( $form_style['form_title_position'] ) ) ? $form_style['form_title_position'] : 'left';
						$content            .= '<div class="arm-df__heading armalign' . esc_attr($form_title_position) . '">';
						$content            .= '<span class="arm-df__heading-text">' . esc_html($form->name) . '</span>';
						$content            .= '</div>';
					}
					$content .= $this->arm_member_form_get_single_form_fields( $form, $atts, $formRandomID );
					$content .= '<div class="armclear"></div>';
					if ( isset( $form_settings['is_hidden_fields'] ) && $form_settings['is_hidden_fields'] == '1' ) {
						if ( isset( $form_settings['hidden_fields'] ) && ! empty( $form_settings['hidden_fields'] ) ) {
							foreach ( $form_settings['hidden_fields'] as $hiddenF ) {
								$hiddenMetaKey = ( isset( $hiddenF['meta_key'] ) && ! empty( $hiddenF['meta_key'] ) ) ? $hiddenF['meta_key'] : sanitize_title( $hiddenF['title'] );
								$hiddenValue   = get_user_meta( $user_id, $hiddenMetaKey, true );
								$hiddenValue   = ( ! empty( $hiddenValue ) ) ? $hiddenValue : $hiddenF['value'];
								$content      .= '<input type="hidden" name="' . esc_attr( $hiddenMetaKey ) . '" value="' . esc_attr( $hiddenValue ) . '"/>';
							}
						}
					}
					$content .= '</div>';
					$content .= '<div class="armclear"></div>';
					$content .= '<input type="hidden" name="arm_action" value="edit_profile"/>';
					$content .= '<input type="hidden" name="isAdmin" value="' . ( ( is_admin() ) ? '1' : '0' ) . '"/>';
					$content .= '<input type="hidden" name="arm_parent_form_id" value="' . esc_attr( $form_id ) . '"/>';
					$content .= '<input type="hidden" name="arm_success_message" value="' . esc_attr( $success_message ) . '"/>';

					$content .= '<input type="hidden" name="id" value="' . esc_attr( $user_id ) . '"/>';
					$content .= do_shortcode( '[armember_spam_filters]' );
					$content .= '</div>';
					$content .= '</form>';
					$content .= '<div class="armclear"></div>';

					global $arm_lite_members_activity, $arm_lite_version;
					$arm_request_version = get_bloginfo( 'version' );
					$setact              = 0;
					global $check_version;
					$setact = $arm_lite_members_activity->$check_version();

					if ( $setact != 1 ) {
						$content .= "<div><span style='color:#FF0000; margin-top:10px; font-size:12px !important; text-align:center; display:block !important;'>Powered by <a href='https://www.armemberplugin.com/redirect.php?rdt=t2&arm_version=$arm_lite_version&arm_request_version=$arm_request_version' target='_blank'>ARMember</a></span></div>";
						$content .= "<div><span style='color:#FF0000; font-size:12px !important; text-align:center; display:block !important;'>&nbsp;&nbsp;(Unlicensed)</span></div>";
					}

					$content .= '</div>';
					$content  = apply_filters( 'arm_change_content_after_display_form', $content, $form, $atts, $formRandomID );
				}
			} else {
				$default_login_form_id = $this->arm_get_default_form_id( 'login' );

				$arm_all_global_settings = $arm_global_settings->arm_get_all_global_settings();

				$page_settings    = $arm_all_global_settings['page_settings'];
				$general_settings = $arm_all_global_settings['general_settings'];

				$login_page_id = ( isset( $page_settings['login_page_id'] ) && $page_settings['login_page_id'] != '' && $page_settings['login_page_id'] != 404 ) ? $page_settings['login_page_id'] : 0;
				if ( $login_page_id == 0 ) {

					if ( $general_settings['hide_wp_login'] == 1 ) {
						$login_page_url = ARM_HOME_URL;
					} else {
						$referral_url   = wp_get_current_page_url();
						$referral_url   = ( ! empty( $referral_url ) && $referral_url != '' ) ? $referral_url : wp_get_current_page_url();
						$login_page_url = wp_login_url( $referral_url );
					}
				} else {
					$login_page_url = get_permalink( $login_page_id ) . '?arm_redirect=' . urlencode( wp_get_current_page_url() );
				}
				if ( is_home() ) {
					return '';
				} else {
					if ( preg_match_all( '/arm_redirect/', $login_page_url, $matche ) < 2 ) {
						wp_redirect( $login_page_url );
					}
				}
			}
			$ARMemberLite->enqueue_angular_script();
			$ARMemberLite->arm_check_font_awesome_icons( $content );

			$inbuild     = '';
			$hiddenvalue = '';
			global $arm_lite_members_activity, $arm_lite_version;
			$arm_request_version = get_bloginfo( 'version' );

			$hiddenvalue = '  
            <!--Plugin Name: ARMember    
                Plugin Version: ' . get_option( 'armlite_version' ) . ' ' . $inbuild . '
                Developed By: Repute Infosystems
                Developer URL: http://www.reputeinfosystems.com/
            -->';

			return $content . $hiddenvalue;
		}

		function arm_verify_user_activation_for_front( $user_email, $key ) {
			global $wp, $wpdb, $arm_lite_errors, $ARMemberLite, $arm_global_settings;
			$arm_message = array();
			if ( ! isset( $user_email ) || empty( $user_email ) ) {
				$err_msg     = isset( $arm_global_settings->common_message['arm_user_not_exist'] ) ? $arm_global_settings->common_message['arm_user_not_exist'] : '';
				$err_msg     = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'User does not exist.', 'armember-membership' );
				$arm_message = array(
					'status'  => 'error',
					'message' => $err_msg,
				);
			}

			// Get user data.
			$user_data      = get_user_by( 'email', $user_email );
			$activation_key = '';
			if ( isset( $user_data ) && ! empty( $user_data ) ) {
				$activation_key = get_user_meta( $user_data->ID, 'arm_user_activation_key', true );
			}

			if ( ! empty( $user_data ) && ( empty( $activation_key ) || $activation_key == '' ) ) {

				$err_msg     = isset( $arm_global_settings->common_message['arm_already_active_account'] ) ? $arm_global_settings->common_message['arm_already_active_account'] : '';
				$err_msg     = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'Your account has been activated.', 'armember-membership' );
				$arm_message = array(
					'status'  => 'success',
					'message' => $err_msg,
				);
			} elseif ( $activation_key == $key ) {

				/* Update Activation Status */
				arm_set_member_status( $user_data->ID, 1 );

				$total_user_plans = get_user_meta( $user_data->ID, 'arm_user_plan_ids', true );
				$total_user_plans = ( isset( $total_user_plans ) && ! empty( $total_user_plans ) ) ? $total_user_plans : array();

				if ( ! empty( $total_user_plans ) ) {
					$total_user_suspended_plans = get_user_meta( $user_data->ID, 'arm_user_suspended_plan_ids', true );
					$total_user_suspended_plans = ( isset( $total_user_suspended_plans ) && ! empty( $total_user_suspended_plans ) ) ? $total_user_suspended_plans : array();
					foreach ( $total_user_plans as $tp ) {
						if ( in_array( $tp, $total_user_suspended_plans ) ) {
							unset( $total_user_suspended_plans[ array_search( $tp, $total_user_suspended_plans ) ] );
						}
					}
					update_user_meta( $user_data->ID, 'arm_user_suspended_plan_ids', $total_user_suspended_plans );
				}

				/* Send New User Notification Mail */
				armMemberSignUpCompleteMail( $user_data );
				/* Send Account Verify Notification Mail */
				armMemberAccountVerifyMail( $user_data );
				/* Activation Success Message */
				 $err_msg    = ( ! empty( $arm_global_settings->common_message['arm_already_active_account'] ) ) ? $arm_global_settings->common_message['arm_already_active_account'] : esc_html__( 'Your account has been activated, please login to view your profile.', 'armember-membership' );
				$arm_message = array(
					'status'  => 'success',
					'message' => $err_msg,
				);
			} else {

				$err_msg     = ( ! empty( $arm_global_settings->common_message['arm_expire_activation_link'] ) ) ? $arm_global_settings->common_message['arm_expire_activation_link'] : esc_html__( 'Activation link is expired or invalid.', 'armember-membership' );
				$arm_message = array(
					'status'  => 'error',
					'message' => $err_msg,
				);
			}

			return $arm_message;
		}

		function arm_verify_reset_password_link( $user_email, $key ) {
			global $arm_global_settings;
			$arm_message = array();

			if ( ! isset( $user_email ) || empty( $user_email ) ) {
				$err_msg     = isset( $arm_global_settings->common_message['arm_user_not_exist'] ) ? $arm_global_settings->common_message['arm_user_not_exist'] : '';
				$err_msg     = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'User does not exist.', 'armember-membership' );
				$arm_message = array(
					'status'  => 'error',
					'message' => $err_msg,
				);
			}

			$user = check_password_reset_key( $key, $user_email );

			if ( ! $user || is_wp_error( $user ) ) {

				if ( $user && $user->get_error_code() === 'expired_key' ) {
					$err_msg     = ( ! empty( $arm_global_settings->common_message['arm_password_reset_pwd_link_expired'] ) ) ? $arm_global_settings->common_message['arm_password_reset_pwd_link_expired'] : esc_html__( 'Reset Password Link is expired.', 'armember-membership' );
					$arm_message = array(
						'status'  => 'error',
						'message' => $err_msg,
					);
				} else {
					$err_msg     = ( ! empty( $arm_global_settings->common_message['arm_password_reset_pwd_link_expired'] ) ) ? $arm_global_settings->common_message['arm_password_reset_pwd_link_expired'] : esc_html__( 'Reset Password Link is invalid.', 'armember-membership' );
					$arm_message = array(
						'status'  => 'error',
						'message' => $err_msg,
					);
				}
			} else {
				$err_msg     = ( ! empty( $arm_global_settings->common_message['arm_password_enter_new_pwd'] ) ) ? $arm_global_settings->common_message['arm_password_enter_new_pwd'] : esc_html__( 'Please enter new password.', 'armember-membership' );
				$arm_message = array(
					'status'  => 'success',
					'message' => $err_msg,
				);
			}

			return $arm_message;
		}

		/**
		 * `[arm_form]` shortcode function
		 */
		function arm_form_shortcode_func( $atts, $content, $tag ) {
			global $arm_lite_bpopup_loaded, $arm_members_class, $arm_global_settings, $ARMSPAMFILEURL, $arm_lite_inner_form_modal, $arm_subscription_plans, $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}
			$ARMemberLite->arm_session_start(true);
			/* ====================/.Begin Set Shortcode Attributes./==================== */

			$short_atts = array(
				'id'                  => 0,
				'class'               => '',
				'popup'               => false, /* Form will be open in popup box when options is true */
				'link_type'           => 'link',
				'link_class'          => '', /* Possible Options:- `link`, `button` */
				'link_title'          => esc_html__( 'Click here to open form', 'armember-membership' ), /* Default to form name */
				'popup_height'        => '',
				'popup_width'         => '',
				'overlay'             => '0.6',
				'modal_bgcolor'       => '#000000',
				'redirect_to'         => '',
				'setup'               => false,
				'widget'              => false,
				'link_css'            => '',
				'link_hover_css'      => '',
				'is_referer'          => '0',
				'preview'             => false,
				'nav_menu'            => 0,
				'form_position'       => 'center',
				'assign_default_plan' => 0,
				'logged_in_message'   => '',
				'setup_form_id'       => '',
			);

			$short_atts = apply_filters( 'arm_add_register_dynamic_atts', $short_atts, $tag );

			$atts = shortcode_atts( $short_atts, $atts, $tag );

			$atts = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $atts ); //phpcs:ignore

			$atts['popup'] = ( $atts['popup'] === 'true' || $atts['popup'] == '1' ) ? true : false;
			$atts['setup'] = ( $atts['setup'] === 'true' || $atts['setup'] == '1' ) ? true : false;
			$atts['id']	= ( isset( $atts['id'] ) && ! empty( $atts['id'] ) ) ? intval( $atts['id'] ) : 0;

			if ( $atts['popup'] && ! $atts['setup'] ) {
				$atts['form_position']  = 'center';
				$arm_lite_bpopup_loaded = 1;
			}
			$atts['widget'] = ( $atts['widget'] === 'true' || $atts['widget'] == '1' ) ? true : false;
			$isPreview      = ( $atts['preview'] === 'true' || $atts['preview'] == '1' ) ? true : false;
			$is_nav_menu    = ( $atts['nav_menu'] === '1' || $atts['nav_menu'] == 1 ) ? 1 : 0;
			/* For Social Form Check */
			$social_form = ( isset( $_GET['social_form'] ) && ! empty( $_GET['social_form'] ) ) ? intval( $_GET['social_form'] ) : 0; //phpcs:ignore
			/* ====================/.End Set Shortcode Attributes./==================== */
			global $wp, $wpdb, $current_user, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_social_feature;
			if ( empty( $atts['id'] ) || $atts['id'] == 0 || ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'wpseo_filter_shortcodes' ) ) { //phpcs:ignore
				return '';
			} else {
				if ( is_admin() ) {
					$get_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : ''; //phpcs:ignore
					$current_url      = admin_url( 'admin.php?page=' . $get_page );
					$redirect_to      = admin_url( 'admin.php?page=' . $arm_slugs->manage_members );
				} else {
					$redirect_to = ! empty( $atts['redirect_to'] ) ? $atts['redirect_to'] : ARMLITE_HOME_URL;
				}
				$form      = new ARM_Form_Lite( 'id', $atts['id'] );
				$form_slug = $form->slug;
				if ( $form->type == 'registration' && $isPreview ) {

				} else {
					if ( is_user_logged_in() ) {
						/* Check for login form shortcodes */

						if ( $atts['popup'] === false ) {
							if ( in_array( $form->type, array( 'login', 'signin', 'logout', 'log-out', 'signout', 'sign-out' ) ) ) {

								if ( ! isset( $_GET['arm-key'] ) && empty( $_GET['arm-key'] ) ) { //phpcs:ignore
									$already_logged_in_message     = ( isset( $atts['logged_in_message'] ) && ! empty( $atts['logged_in_message'] ) ) ? $atts['logged_in_message'] : '';
									$already_logged_in_message_div = '<div class="arm_already_logged_in_message" id="arm_already_logged_in_message">' . $already_logged_in_message . '</div>';
									return $already_logged_in_message_div;
								} else {
									return '';
								}
							}
							if ( ! is_admin() && in_array( $form->type, array( 'registration', 'forgot_password', 'lostpassword', 'retrievepassword' ) ) ) {
								$already_logged_in_message     = ( isset( $atts['logged_in_message'] ) && ! empty( $atts['logged_in_message'] ) ) ? $atts['logged_in_message'] : '';
								$already_logged_in_message_div = '<div class="arm_already_logged_in_message" id="arm_already_logged_in_message">' . $already_logged_in_message . '</div>';
								return $already_logged_in_message_div;
								if ( $atts['widget'] == false ) {
									wp_redirect( $redirect_to );
									exit;
								} else {
									$already_logged_in_message     = ( isset( $atts['logged_in_message'] ) && ! empty( $atts['logged_in_message'] ) ) ? $atts['logged_in_message'] : '';
									$already_logged_in_message_div = '<div class="arm_already_logged_in_message" id="arm_already_logged_in_message">' . $already_logged_in_message . '</div>';
									return $already_logged_in_message_div;
								}
							}
						}
					} else {
						if ( ! is_admin() && in_array( $form->type, array( 'edit_profile', 'update_profile', 'change_password' ) ) ) {
							if ( $form->type == 'change_password' && isset( $_GET['key'] ) && isset( $_GET['action'] ) && $_GET['action'] == 'armrp' && isset( $_GET['login'] ) && ! empty( $_GET['login'] ) ) { //phpcs:ignore
 
								$chk_key      = rawurldecode( sanitize_text_field( $_GET['key'] ) ); //phpcs:ignore
								$user_email   = rawurldecode( sanitize_text_field( $_GET['login'] ) ); //phpcs:ignore
								$arm_message1 = array();

								if ( isset( $_GET['varify_key'] ) && ! empty( $_GET['varify_key'] ) ) { //phpcs:ignore

									 $user_data_array = get_user_by( 'login', $user_email );

									$this->arm_verify_user_activation_for_front( $user_data_array->user_email, rawurldecode( sanitize_text_field( $_GET['varify_key'] ) ) ); //phpcs:ignore
								}

								$arm_message1 = $this->arm_verify_reset_password_link( $user_email, $chk_key );

								if ( $arm_message1['status'] == 'error' ) {
									$default_forgot_password_form_id = $this->arm_get_default_form_id( 'forgot_password' );
									return do_shortcode( "[arm_form id='$default_forgot_password_form_id']" );
								}
								else
								{
									$atts['skip_current_password']=1;
								}
							} else {
								$default_login_form_id   = $this->arm_get_default_form_id( 'login' );
								$arm_all_global_settings = $arm_global_settings->arm_get_all_global_settings();

								$page_settings    = $arm_all_global_settings['page_settings'];
								$general_settings = $arm_all_global_settings['general_settings'];

								$login_page_id  = ( isset( $page_settings['login_page_id'] ) && $page_settings['login_page_id'] != '' && $page_settings['login_page_id'] != 404 ) ? $page_settings['login_page_id'] : 0;
								$armCurPage_url = wp_get_current_page_url();
								if ( $login_page_id == 0 ) {
									if ( $general_settings['hide_wp_login'] == 1 ) {
										$login_page_url = ARMLITE_HOME_URL;
									} else {
										$armCurPage_url = wp_get_current_page_url();
										$login_page_url = wp_login_url( $armCurPage_url );
									}
								} else {
									$login_page_url = get_permalink( $login_page_id ) . '?arm_redirect=' . urlencode( wp_get_current_page_url() );
								}
								if ( $is_nav_menu == 1 ) {
									return do_shortcode( "[arm_form id='$default_login_form_id' is_referer='1' nav_menu='1']" );
								} else {
									if ( $atts['widget'] == false ) {
										if ( preg_match_all( '/arm_redirect/', $login_page_url, $matche ) < 2 ) {
											wp_redirect( $login_page_url );
											return '';
										}
									} else {
										return '';
									}
								}
							}
						}
					}
				}
				$form_settings = array(
					'style'      => $this->arm_default_form_style(),
					'custom_css' => '',
				);
				$form          = apply_filters( 'arm_form_data_before_form_shortcode', $form, $atts );
				do_action( 'arm_before_render_form', $form, $atts );
				if ( $form->exists() && ! empty( $form->fields ) ) {
					$form_id            = $form->ID;
					$form_settings      = $form->settings;
					$ref_template       = $form->form_detail['arm_ref_template'];
					$atts['hide_title'] = ( isset( $form_settings['hide_title'] ) && $form_settings['hide_title'] == '1' ) ? true : false;
					$form_style         = $form_settings['style'];
					$form_color_scheme  = ! empty( $form_style['color_scheme'] ) ? $form_style['color_scheme'] : 'default';
					if ( isset( $form_settings['redirect_type'] ) && $form_settings['redirect_type'] != 'message' ) {
						if ( $form_settings['redirect_type'] == 'page' ) {
							$form_redirect_id  = ( ! empty( $form_settings['redirect_page'] ) ) ? $form_settings['redirect_page'] : '0';
							$redirect_to       = $arm_global_settings->arm_get_permalink( '', $form_redirect_id );
							$arm_redirect_type = '';
						} elseif ( $form_settings['redirect_type'] == 'referral' ) {
							$redirect_to       = wp_get_referer();
							$default_redirect  = ( ! empty( $form_settings['referral_url'] ) ) ? $form_settings['referral_url'] : wp_get_current_page_url();
							$arm_redirect_type = '';
						} else {
							$redirect_to       = ( ! empty( $form_settings['redirect_url'] ) ) ? $form_settings['redirect_url'] : $redirect_to;
							$arm_redirect_type = '';
						}
					}
					/* Form Classes */
					$form_style['button_position'] = ( ! empty( $form_style['button_position'] ) ) ? $form_style['button_position'] : 'left';
					$form_style_class              = ' arm_form_' . $form_id;
					$form_style_class             .= ' arm_form_layout_' . $form_style['form_layout'];

					if ( $form_style['form_layout'] == 'writer' ) {
						$form_style_class .= ' arm-default-form arm-material-style ';
					} elseif ( $form_style['form_layout'] == 'rounded' ) {
						$form_style_class .= ' arm-default-form arm-rounded-style ';
					} elseif ( $form_style['form_layout'] == 'writer_border' ) {
						$form_style_class .= ' arm-default-form arm--material-outline-style ';
					} else {
						$form_style_class .= ' arm-default-form ';
					}

					$form_style_class .= ( $form_style['label_hide'] == '1' ) ? ' armf_label_placeholder' : '';
					$form_style_class .= ' armf_alignment_' . $form_style['label_align'];
					$form_style_class .= ' armf_layout_' . $form_style['label_position'];
					$form_style_class .= ' armf_button_position_' . $form_style['button_position'];
					$form_style_class .= ( $form_style['rtl'] == '1' ) ? ' arm_form_rtl' : ' arm_form_ltr';
					if ( is_rtl() ) {
						$form_style_class .= ' arm_rtl_site';
					}
					$form_style_class .= ' ' . $atts['class'];
					if ( empty( $atts['setup_form_id'] ) ) {
						$formRandomID = $form_id . '_' . arm_generate_random_code();
					} else {
						$formRandomID = $atts['setup_form_id'];
					}
					$loginFormLinks      = $modalForms = $socialBtns = $socialBtnSeparator = '';
					$enable_social_login = ( isset( $form_settings['enable_social_login'] ) ) ? $form_settings['enable_social_login'] : 0;
					$social_btn_position = ( isset( $form_style['social_btn_position'] ) ) ? $form_style['social_btn_position'] : 'bottom';
					if ( $form->type == 'login' ) {
						$reg_link_label = ( isset( $form_settings['registration_link_label'] ) ) ? stripslashes( $form_settings['registration_link_label'] ) : esc_html__( 'Register', 'armember-membership' );
						$fp_link_label  = ( isset( $form_settings['forgot_password_link_label'] ) ) ? stripslashes( $form_settings['forgot_password_link_label'] ) : esc_html__( 'Forgot Password', 'armember-membership' );
						$show_fp_link   = ( isset( $form_settings['show_forgot_password_link'] ) ) ? $form_settings['show_forgot_password_link'] : 0;
						if ( $show_fp_link == '1' ) {
							if ( isset( $form_settings['forgot_password_link_type'] ) && $form_settings['forgot_password_link_type'] == 'page' ) {
								$fpLinkPageID  = ( isset( $form_settings['forgot_password_link_type_page'] ) ) ? $form_settings['forgot_password_link_type_page'] : $arm_global_settings->arm_get_single_global_settings( 'forgot_password_page_id', 0 );
								$fpLinkHref    = $arm_global_settings->arm_get_permalink( '', $fpLinkPageID );
								$fp_link_label = $this->arm_parse_login_links( $fp_link_label, $fpLinkHref );

								$loginFormLinks .= '<div class="arm-df__form-group arm-df__form-group_forgot_link arm_forgot_password_above_link arm_forgotpassword_link">';
								$loginFormLinks .= $fp_link_label; //phpcs:ignore
								$loginFormLinks .= '</div>';
							} else {
								$fp_id           = $wpdb->get_var( $wpdb->prepare('SELECT `arm_form_id` FROM `' . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_form_type`=%s AND `arm_set_id`=%d",'forgot_password',$form->set_id) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_forms is a table name
								$fp_id           = ( ! empty( $fp_id ) && $fp_id != 0 ) ? $fp_id : $this->arm_get_default_form_id( 'forgot_password' );
								$fpIdClass       = 'arm_login_form_fp_link_' . $form_id . '_' . $fp_id . '_' . $formRandomID;
								$modalForms     .= do_shortcode( "[arm_form id='$fp_id' popup='true' link_title=' ' link_class='arm_login_form_other_links $fpIdClass']" );
								$fp_link_label   = $this->arm_parse_login_links( $fp_link_label, 'javascript:void(0)', 'arm_login_popup_form_links arm_form_popup_ahref', 'data-form_id="' . esc_attr($fpIdClass) . '" data-toggle="armmodal" data-modal_bg="' . esc_attr($atts['modal_bgcolor']) . '" data-overlay="' . esc_attr($atts['overlay']) . '"' );
								$loginFormLinks .= '<div class="arm-df__form-group arm-df__form-group_forgot_link arm_forgot_password_below_link arm_forgotpassword_link">';
								$loginFormLinks .= $fp_link_label; //phpcs:ignore
								$loginFormLinks .= '</div>';
							}
						}
						$isSeparator = ( isset( $form_style['enable_social_btn_separator'] ) ) ? $form_style['enable_social_btn_separator'] : 0;

						$loginFormLinks .= '<div class="arm_login_links_wrapper arm_login_options arm_socialicons_bottom">';
						$loginFormLinks .= ( $social_btn_position == 'bottom' ) ? $socialBtnSeparator : '';
						$loginFormLinks .= ( $social_btn_position == 'bottom' ) ? $socialBtns : '';
						$show_reg_link   = ( isset( $form_settings['show_registration_link'] ) ) ? $form_settings['show_registration_link'] : 0;
						if ( $show_reg_link == '1' ) {
							if ( isset( $form_settings['registration_link_type'] ) && $form_settings['registration_link_type'] == 'modal' ) {
								$default_rf_id = $this->arm_get_default_form_id( 'registration' );
								$rf_id         = ( isset( $form_settings['registration_link_type_modal'] ) ) ? $form_settings['registration_link_type_modal'] : $default_rf_id;
								$regIdClass    = 'arm_login_form_reg_link_' . $formRandomID;
								$rf_type       = ( isset( $form_settings['registration_link_type_modal_form_type'] ) ) ? $form_settings['registration_link_type_modal_form_type'] : 'arm_form';
								if ( $rf_type == 'arm_setup' ) {
									$modalForms .= do_shortcode( "[arm_setup id='$rf_id' popup='true'  link_title=' ' popup_width='800' link_type='link' link_class='" . $regIdClass . "']" );
								} else {
									$modalForms .= do_shortcode( "[arm_form id='$rf_id' popup='true' link_title=' ' link_class='arm_login_form_other_links $regIdClass'] " );
								}
								$reg_link_label  = $this->arm_parse_login_links( $reg_link_label, 'javascript:void(0)', 'arm_login_popup_form_links arm_form_popup_ahref', 'data-form_id="' . esc_attr($regIdClass) . '" data-toggle="armmodal" data-modal_bg="' . esc_attr($atts['modal_bgcolor']) . '" data-overlay="' . esc_attr($atts['overlay']) . '"' );
								$loginFormLinks .= '<span class="arm_registration_link arm_reg_login_links">' . $reg_link_label . '</span>';
							} else {
								$regLinkPageID   = ( isset( $form_settings['registration_link_type_page'] ) ) ? $form_settings['registration_link_type_page'] : $arm_global_settings->arm_get_single_global_settings( 'register_page_id', 0 );
								$regLinkHref     = $arm_global_settings->arm_get_permalink( '', $regLinkPageID );
								$reg_link_label  = $this->arm_parse_login_links( $reg_link_label, $regLinkHref );
								$loginFormLinks .= '<span class="arm_registration_link arm_reg_login_links">' . $reg_link_label . '</span>';
							}
						}
						$loginFormLinks .= '<div class="armclear"></div>';
						$loginFormLinks .= '</div>';
						$loginFormLinks .= '<div class="armclear"></div>';
					}

					$form_attr = ' name="arm_form" id="arm_form' . esc_attr( $formRandomID ) . '"';
					// $form_attr .= ' onsubmit=" return armFormSubmit(\'arm_form' . $formRandomID . '\', event);"';
					if ( ! empty( $form_style['form_layout'] ) && $form_style['form_layout'] != '' ) {
						$form_style_class .= ' arm_form_style_' . $form_color_scheme;
						if ( $form_style['form_layout'] == 'writer' || $form_style['form_layout'] == 'writer_border' ) {
							$form_style_class .= ' arm_materialize_form';
						}
					}
					$form_css      = $this->arm_ajax_generate_form_styles( $form_id, $form_settings, $atts, $ref_template );
					$form_content  = $form_css['arm_link'];
					$form_content .= '<style type="text/css" id="arm_form_style_' . esc_attr($form_id) . '">' . $form_css['arm_css'] . '</style>';
					/* Form Inner Content */
					$field_position = ! empty( $form_style['field_position'] ) ? $form_style['field_position'] : 'left';
					$validation_pos = ! empty( $form_style['validation_position'] ) ? $form_style['validation_position'] : 'bottom';
					if ( isset( $atts['popup'] ) && $atts['popup'] !== false ) {
						$validation_pos           = 'bottom';
						$form_style['form_width'] = ( ! empty( $form_style['form_width'] ) ) ? $form_style['form_width'] : '600';
						if ( isset( $atts['popup_width'] ) && $atts['popup_width'] < $form_style['form_width'] ) {
							$form_attr .= ' style="width: 100%;"';
						}
					}
					$form_content .= '<div class="arm-df-wrapper arm_msg_pos_' . esc_attr($validation_pos) . '">';
					$form_content .= '<div class="arm-df__fields-wrapper arm-df__fields-wrapper_' . esc_attr($form_id) . ' arm_field_position_' . esc_attr($field_position) . ' arm_front_side_form"  data-form_id="' . esc_attr($form_id) . '">';
					if ( $form->type == 'login' && $social_btn_position == 'top' ) {
						$form_content .= '<div class="arm_login_links_wrapper arm_socialicons_top">';
						$form_content .= $socialBtns . $socialBtnSeparator;
						$form_content .= '</div>';
					}
					if ( $atts['hide_title'] == false && $atts['popup'] === false ) {
						$form_title_position = ( ! empty( $form_style['form_title_position'] ) ) ? $form_style['form_title_position'] : 'left';
						$form_content       .= '<div class="arm-df__heading armalign' . esc_attr($form_title_position) . '">';
						$form_content       .= '<span class="arm-df__heading-text">' . esc_html($form->name) . '</span>';
						$form_content       .= '</div>';
					}
					if ( $form->type == 'forgot_password' ) {
						if ( isset( $form_settings['description'] ) ) {
							$form_content .= '<div class="arm_forgot_password_description">';
							$form_content .= stripslashes( $form_settings['description'] );
							$form_content .= '</div>';
						}
					}
					$form_content .= $this->arm_member_form_get_single_form_fields( $form, $atts, $formRandomID );
					$form_content .= '<div class="armclear"></div>';
					if ( isset( $form_settings['is_hidden_fields'] ) && $form_settings['is_hidden_fields'] == '1' ) {
						if ( isset( $form_settings['hidden_fields'] ) && ! empty( $form_settings['hidden_fields'] ) ) {
							foreach ( $form_settings['hidden_fields'] as $hiddenF ) {
								$hiddenMetaKey = ( isset( $hiddenF['meta_key'] ) && ! empty( $hiddenF['meta_key'] ) ) ? $hiddenF['meta_key'] : sanitize_title( $hiddenF['title'] );
								$form_content .= '<input type="hidden" name="' . esc_attr( $hiddenMetaKey ) . '" value="' . esc_attr( $hiddenF['value'] ) . '"/>';
							}
						}
					}
					$form_content .= '</div>';
					$form_content .= '<input type="hidden" name="arm_action" value="' . esc_attr( $form_slug ) . '"/>';

					$form_content .= '<input type="hidden" name="redirect_to" value="' . esc_attr( $redirect_to ) . '"/>';
					$form_content .= '<input type="hidden" name="isAdmin" value="' . ( ( is_admin() ) ? '1' : '0' ) . '"/>';

					$arm_default_redirection_settings = get_option( 'arm_redirection_settings' );
					$arm_default_redirection_settings = maybe_unserialize( $arm_default_redirection_settings );
					$login_redirection_rules_options  = $arm_default_redirection_settings['login'];
					$signup_redirection_rules_options = $arm_default_redirection_settings['signup'];

					if ( $atts['is_referer'] == '1' || ( isset( $login_redirection_rules_options['type'] ) && $login_redirection_rules_options['type'] == 'referral' ) || ( isset( $signup_redirection_rules_options['type'] ) && $signup_redirection_rules_options['type'] == 'referral' ) ) {

						if ( isset( $_REQUEST['redirect'] ) && $_REQUEST['redirect'] != '' ) { //phpcs:ignore

							$referral_url1 = urldecode( sanitize_text_field( $_REQUEST['redirect'] ) ); //phpcs:ignore
						} elseif ( isset( $_REQUEST['arm_redirect'] ) && $_REQUEST['arm_redirect'] != '' ) { //phpcs:ignore
							$referral_url1 = urldecode( sanitize_text_field( $_REQUEST['arm_redirect'] ) ); //phpcs:ignore
						} else {

							if ( $atts['popup'] !== false ) {
								global $arm_restriction;
								$referral_url1 = $arm_restriction->curPageURL();
							} else {
								$referral_url1 = wp_get_referer();
							}
						}
						if ( isset( $_SESSION['arm_restricted_page_url'] ) && ! empty( $_SESSION['arm_restricted_page_url'] ) ) {
							/* if referrel page is restricted, then below is used */
							global $arm_restriction;
							$check_curr_page_url = $arm_restriction->curPageURL();
							$arm_url_components  = wp_parse_url( $check_curr_page_url );

							// $arm_url_components = parse_url($referral_url1);
							if ( isset( $arm_url_components['query'] ) ) {
								parse_str( $arm_url_components['query'], $arm_chk_res_params );
								if ( isset( $arm_chk_res_params['restricted'] ) ) {
									$referral_url1 = $_SESSION['arm_restricted_page_url']; //phpcs:ignore
								}
							}
						}
					}

					$default_redirect = ( ! empty( $login_redirection_rules_options['refferel'] ) ) ? $login_redirection_rules_options['refferel'] : wp_get_current_page_url();
					if ( preg_match( '/signup|registration/', $form_slug ) > 0 ) {
						$default_redirect = ( ! empty( $signup_redirection_rules_options['refferel'] ) ) ? $signup_redirection_rules_options['refferel'] : wp_get_current_page_url();
					}
					$referral_url  = ! empty( $referral_url1 ) ? $referral_url1 : $default_redirect;
					$form_content .= '<input type="hidden" name="referral_url" value="' . esc_attr( $referral_url ) . '"/>';
					if ( is_admin() && isset( $_REQUEST['id'] ) ) { //phpcs:ignore
						$form_content .= '<input type="hidden" name="id" value="' . intval( $_REQUEST['id'] ) . '"/>'; //phpcs:ignore
					}
					if ( $form->type == 'registration' ) {

						/* For User Avatar Cropper */
						$general_settings = isset( $arm_global_settings->global_settings ) ? $arm_global_settings->global_settings : array();
						$enable_crop      = isset( $general_settings['enable_crop'] ) ? $general_settings['enable_crop'] : 0;

						global $arm_is_enable_crop;
						if ( $enable_crop && empty( $arm_is_enable_crop ) ) {
							$arm_is_enable_crop = 1;
							$form_content      .= '<div id="arm_crop_div_wrapper" class="arm_crop_div_wrapper"  style="display:none;" data_id="' . esc_attr($formRandomID) . '">';
							$form_content      .= '<div id="arm_crop_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
							$form_content      .= '<div id="arm_crop_div" class="arm_crop_div" data_id="' . esc_attr($formRandomID) . '"><img id="arm_crop_image" class="arm_crop_image" src="" style="max-width:100%;" data_id="' . esc_attr($formRandomID) . '"/></div>'; //phpcs:ignore 
							$form_content      .= '<div class="arm_skip_avtr_crop_button_wrapper_admn arm_inht_front_usr_avtr">';
							$form_content      .= '<button class="arm_crop_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Crop', 'armember-membership' ) . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
							$form_content      .= '<button class="arm_clear_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Clear', 'armember-membership' ) . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
							$form_content      .= '<button class="arm_zoom_button arm_zoom_plus arm_img_setting armhelptip tipso_style" data-method="zoom" data-option="0.1" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Zoom In', 'armember-membership' ) . '"><span class="armfa armfa-search-plus"></span></button>';
							$form_content      .= '<button class="arm_zoom_button arm_zoom_minus arm_img_setting armhelptip tipso_style" data-method="zoom" data-option="-0.1" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Zoom Out', 'armember-membership' ) . '"><span class="armfa armfa-search-minus"></span></button>';
							$form_content      .= '<button class="arm_rotate_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" data-method="rotate" data-option="90" title="' . esc_html__( 'Rotate', 'armember-membership' ) . '"><span class="armfa armfa-rotate-right"></span></button>';
							$form_content      .= '<button class="arm_reset_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Reset', 'armember-membership' ) . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
							$form_content      .= '<button id="arm_skip_avtr_crop_nav_front" class="arm_avtr_done_front" data_id="' . esc_attr($formRandomID) . '">' . esc_html__( 'Done', 'armember-membership' ) . '</button>';
							$form_content      .= '</div>';
							$form_content           .= '<p class="arm_discription">' .sprintf( addslashes( esc_html__('(Use Cropper to set image and %suse mouse scroller for zoom image.)', 'armember-membership') ),'<br/>' ) . '</p>';//phpcs:ignore
							$form_content      .= '</div>';
						}
						/* For User Avatar Cropper */
						$form_content .= '<input type="hidden" name="arm_form_id" value="' . esc_attr( $form_id ) . '"/>';
						if ( isset( $atts['assign_default_plan'] ) && $arm_subscription_plans->isFreePlanExist( $atts['assign_default_plan'] ) ) {
							$form_content .= '<input type="hidden" name="subscription_plan" id="arm_assign_default_plan" value="' . esc_attr( $atts['assign_default_plan'] ) . '"/>';
						}
						if ( ! $atts['setup'] ) {
							/*Add login link in signup form*/
							$login_link_label = ( isset( $form_settings['login_link_label'] ) ) ? stripslashes( $form_settings['login_link_label'] ) : esc_html__( 'Login', 'armember-membership' );

							$form_content   .= '<div class="arm_reg_links_wrapper arm_reg_options arm_reg_login_links">';
							$show_login_link = ( isset( $form_settings['show_login_link'] ) ) ? $form_settings['show_login_link'] : 0;

							if ( $show_login_link == '1' && ! is_user_logged_in() ) {
								global $arm_login_form_popup_ids_arr;
								if ( isset( $form_settings['login_link_type'] ) && $form_settings['login_link_type'] == 'modal' ) {
									$default_lf_id = $this->arm_get_default_form_id( 'login' );
									$lf_id         = ( isset( $form_settings['login_link_type_modal'] ) ) ? $form_settings['login_link_type_modal'] : $default_lf_id;

									if ( array_key_exists( $lf_id, $arm_login_form_popup_ids_arr ) ) {
										$formRandomID = $arm_login_form_popup_ids_arr[ $lf_id ];
									}

									$loginIdClass  = 'arm_reg_form_login_link_' . $formRandomID;
									$form_content .= '<input type="hidden" name="arm_signup_login_form" value="' . esc_attr( $loginIdClass ) . '">';

									if ( ! array_key_exists( $lf_id, $arm_login_form_popup_ids_arr ) ) {
										$arm_login_form_popup_ids_arr[ $lf_id ] = $formRandomID;
										$modalForms                            .= do_shortcode( "[arm_form id='" . $lf_id . "' popup='true' link_title=' ' link_class='arm_reg_form_other_links " . $loginIdClass . "']" );
									} else {
										$modalForms .= "[arm_form id='" . $lf_id . "' popup='true' link_title=' ' link_class='arm_reg_form_other_links " . $loginIdClass . "']";
									}

									$login_link_label = $this->arm_parse_login_links( $login_link_label, 'javascript:void(0)', 'arm_reg_popup_form_links arm_form_popup_ahref', 'data-form_id="' . esc_attr($loginIdClass) . '" data-toggle="armmodal" data-modal_bg="' . esc_attr($atts['modal_bgcolor']) . '" data-overlay="' . esc_attr($atts['overlay']) . '"' );
									$form_content    .= '<span class="arm_login_link">' . $login_link_label . '</span>';
								} else {
									$loginLinkPageID  = ( isset( $form_settings['login_link_type_page'] ) ) ? $form_settings['login_link_type_page'] : $arm_global_settings->arm_get_single_global_settings( 'login_page_id', 0 );
									$loginLinkHref    = $arm_global_settings->arm_get_permalink( '', $loginLinkPageID );
									$login_link_label = $this->arm_parse_login_links( $login_link_label, $loginLinkHref );
									$form_content    .= '<span class="arm_login_link">' . $login_link_label . '</span>'; //phpcs:ignore
								}
							}
							$form_content .= '<div class="armclear"></div>';
							$form_content .= '</div>';
							$form_content .= '<div class="armclear"></div>';
						}
					} elseif ( $form->type == 'change_password' && ! empty( $_GET['action'] ) && $_GET['action'] == 'armrp' ) { //phpcs:ignore
						$armrpkey      = ! empty( $_GET['key'] ) ? sanitize_text_field($_GET['key']) : ''; //phpcs:ignore
						$armrplogin    = ! empty( $_GET['login'] ) ? sanitize_text_field($_GET['login']) : ''; //phpcs:ignore
						$form_content .= '<input type="hidden" name="armrpkey" value="' . esc_attr( $armrpkey ) . '"/>';
						$form_content .= '<input type="hidden" name="armrplogin" value="' . esc_attr( $armrplogin ) . '"/>';
					}
					$form_content .= '<div class="armclear"></div>';
					$form_content .= do_shortcode( '[armember_spam_filters]' );
					$form_content .= $loginFormLinks;
					$form_content .= '</div>';
					/* Prepare Form HTML */
					$content = apply_filters( 'arm_change_content_before_display_form', $content, $form, $atts );
					if ( $atts['setup'] ) {
						$content .= '<div class="arm_form ' . esc_attr($form_style_class) . '">';
						$content .= $form_content;
						$content .= '</div>';
						$content .= '<div class="armclear"></div>';
					} else {
						$content .= '<div class="arm-form-container">';
						if ( $atts['popup'] !== false ) {
							$content .= '<div class="arm_form_message_container"></div>';
						} else {
							if ( isset( $_GET['key'] ) && ! empty( $_GET['key'] ) && isset( $_GET['action'] ) && $_GET['action'] == 'armrp' && isset( $_GET['login'] ) && ! empty( $_GET['login'] ) && ! is_user_logged_in() ) { //phpcs:ignore

								if ( $form->type == 'change_password' ) {
									$chk_key      = rawurldecode( sanitize_text_field($_GET['key']) ); //phpcs:ignore
									$user_email   = rawurldecode( sanitize_text_field($_GET['login']) ); //phpcs:ignore
									$arm_message1 = $this->arm_verify_reset_password_link( $user_email, $chk_key );

									if ( $arm_message1['status'] == 'error' ) {
										$content .= '<div class="arm_form_message_container1 arm_editor_form_fileds_container arm_editor_form_fileds_wrapper"><div class="arm-df__fc--validation__wrap"><ul><li>';
										$content .= esc_html($arm_message1['message']);
										$content .= '</li></ul></div></div>';
									} else {
										$content .= '<div class="arm_form_message_container1 arm_editor_form_fileds_container arm_editor_form_fileds_wrapper"><div class="arm_success_msg1"><ul><li>';
										$content .= esc_html($arm_message1['message']);
										$content .= '</li></ul></div></div>';
									}
								}

								if ( $form->type == 'forgot_password' ) {
									$chk_key     = rawurldecode( sanitize_text_field($_GET['key']) );  //phpcs:ignore
									$user_email  = rawurldecode( sanitize_text_field($_GET['login']) ); //phpcs:ignore
									$arm_message = $this->arm_verify_reset_password_link( $user_email, $chk_key );

									if ( $arm_message['status'] == 'error' ) {
										$content .= '<div class="arm_form_message_container1 arm_editor_form_fileds_container arm_editor_form_fileds_wrapper"><div class="arm-df__fc--validation__wrap"><ul><li>';
										$content .= esc_attr($arm_message['message']);
										$content .= '</li></ul></div></div>';
									}
								}
							}
							$content .= '<div class="arm_form_message_container arm_editor_form_fileds_container arm_editor_form_fileds_wrapper arm_form_' . esc_attr($form_id) . '"></div>';
						}
						$content .= '<div class="armclear"></div>';

						$captcha_code = arm_generate_captcha_code();
						if ( ! isset( $_SESSION['ARM_FILTER_INPUT'] ) ) {
							$_SESSION['ARM_FILTER_INPUT'] = array();
						}
						if ( isset( $_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] ) ) {
							unset( $_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] );
						}
						$_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] = $captcha_code;
						$_SESSION['ARM_VALIDATE_SCRIPT']               = true;

						$form_attr .= ' data-random-id="' . esc_attr( $formRandomID ) . '" ';

						$general_settings = isset( $arm_global_settings->global_settings ) ? $arm_global_settings->global_settings : array();
						$spam_protection  = isset( $general_settings['spam_protection'] ) ? $general_settings['spam_protection'] : '';
						if ( ! empty( $spam_protection ) ) {
							$form_attr .= ' data-submission-key="' . esc_attr( $captcha_code ) . '" ';
						}

						$content           .= '<form method="post" class="arm_form ' . esc_attr( $form_style_class ) . ' arm_cl_' . esc_attr( $formRandomID ) . '" enctype="multipart/form-data" novalidate ' . $form_attr . '>';
						$content           .= "<input type='text' name='arm_filter_input' arm_register='true' data-random-key='". esc_attr( $formRandomID) ."' value='' style='opacity:0 !important;display:none !important;visibility:hidden !important;' />";
						$nonce = wp_create_nonce( 'arm_wp_nonce' );
						$content           .= "<input type='text' name='arm_wp_nonce' arm_register='true' data-random-key='". esc_attr( $formRandomID ). "' value='".esc_attr($nonce)."' style='opacity:0 !important;display:none !important;visibility:hidden !important;' />";
						$content .='<input type="hidden" name="arm_wp_nonce_check" value="1">';
							$arm_random_key = wp_rand();

						$content .= $form_content;
						$content .= '</form>';

						$content .= '<div class="armclear">&nbsp;</div>';
						$content .= $modalForms;

						if ( $atts['popup'] !== false ) {
							$content       .= '</div>';
							$popup_content  = '<div class="arm_form_popup_container">';
							$link_title     = ( ! empty( $atts['link_title'] ) ) ? $atts['link_title'] : $form->name;
							$link_style     = $link_hover_style = '';
							$popup_content .= '<style type="text/css">';
							$pformRandomID  = $form->ID . '_' . arm_generate_random_code();
							if ( ! empty( $atts['link_css'] ) ) {
								$link_style     = esc_html( $atts['link_css'] );
								$popup_content .= '.arm_form_popup_link_' . $pformRandomID . '{' . $link_style . '}';
							}
							if ( ! empty( $atts['link_hover_css'] ) ) {
								$link_hover_style = esc_html( $atts['link_hover_css'] );
								$popup_content   .= '.arm_form_popup_link_' . $pformRandomID . ':hover{' . $link_hover_style . '}';
							}
							$popup_content .= '</style>';
							$popupLinkID    = 'arm_form_popup_link_' . esc_attr( $form->ID );
							// Condition for check that if already same form_id popup exist then not creating duplicate popup of that.
							global $arm_form_popup_ids_arr;
							if ( array_key_exists( $form->ID, $arm_form_popup_ids_arr ) ) {
								$pformRandomID = $arm_form_popup_ids_arr[ $form->ID ];
							}
							$popupLinkClass = 'arm_form_popup_link arm_form_popup_link_' . $form->ID . ' arm_form_popup_link_' . $pformRandomID;
							if ( ! empty( $atts['link_class'] ) ) {
								$popupLinkClass .= ' ' . esc_html( $atts['link_class'] );
							}
							$popupLinkAttr = 'data-form_id="' . esc_attr($pformRandomID) . '" data-toggle="armmodal"  data-modal_bg="' . esc_attr($atts['modal_bgcolor']) . '" data-overlay="' . esc_attr($atts['overlay']) . '"';
							if ( ! empty( $atts['link_type'] ) && strtolower( $atts['link_type'] ) == 'button' ) {
								$popup_content .= '<button type="button" id="' . esc_attr($popupLinkID) . '" class="' . esc_attr($popupLinkClass) . ' arm_form_popup_button" ' . esc_attr($popupLinkAttr) . '>' . esc_html($link_title) . '</button>';
							} else {
								$popup_content .= '<a href="javascript:void(0)" id="' . esc_attr($popupLinkID) . '" class="' . esc_attr($popupLinkClass) . ' arm_form_popup_ahref" ' . esc_attr($popupLinkAttr) . '>' . esc_attr($link_title) . '</a>';
							}
							$popup_style = $popup_content_height = '';
							$popupHeight = 'auto';
							$popupWidth  = '500';
							if ( ! empty( $atts['popup_height'] ) ) {
								if ( $atts['popup_height'] == 'auto' ) {
									$popup_style .= 'height: auto;';
								} else {
									$popup_style         .= 'overflow: hidden;height: ' . $atts['popup_height'] . 'px;';
									$popupHeight          = ( $atts['popup_height'] - 70 ) . 'px';
									$popup_content_height = 'overflow-x: hidden;overflow-y: auto;height: ' . ( $atts['popup_height'] - 70 ) . 'px;';
								}
							}
							if ( ! empty( $atts['popup_width'] ) ) {
								if ( $atts['popup_width'] == 'auto' ) {
									$popup_style .= '';
								} else {
									$popupWidth   = $atts['popup_width'];
									$popup_style .= 'width: ' . $atts['popup_width'] . 'px;';
								}
							}
							$popup_modal_content  = '';
							$popup_modal_content .= '<div class="popup_wrapper arm_popup_wrapper arm_popup_member_form arm_popup_member_form_' . esc_attr($form->ID) . ' arm_popup_member_form_' . esc_attr($pformRandomID) . '" style="' . esc_attr($popup_style) . '" data-width="' . esc_attr($popupWidth) . '"><div class="popup_wrapper_inner">';
							$popup_modal_content .= '<div class="popup_header">';
							$popup_modal_content .= '<span class="popup_close_btn arm_popup_close_btn"></span>';
							$popup_modal_content .= '<div class="popup_header_text arm-df__heading">';
							if ( $atts['hide_title'] == false ) {
								$popup_modal_content .= '<span class="arm-df__heading-text">' .esc_html($form->name) . '</span>';
							}
							$popup_modal_content .= '</div>';
							$popup_modal_content .= '</div>';
							$popup_modal_content .= '<div class="popup_content_text" style="' . esc_attr($popup_content_height) . 'min-height: 100px;" data-height="' . esc_attr($popupHeight) . '">';
							$popup_modal_content .= apply_filters( 'arm_change_popup_form_content', $content, $form, $atts, $pformRandomID );
							$popup_modal_content .= '</div>';
							$popup_modal_content .= '<div class="armclear"></div>';
							$popup_modal_content .= '</div></div>';
							$content              = $popup_content;
							// Condition for check that if already same form_id popup exist then not creating duplicate popup of that.
							if ( ! array_key_exists( $form->ID, $arm_form_popup_ids_arr ) ) {
								$arm_form_popup_ids_arr[ $form->ID ] = $pformRandomID;
								array_push( $arm_lite_inner_form_modal, $popup_modal_content );
							}
							if ( $social_form == $form->ID ) {
								$content .= '<script data-cfasync="false" type="text/javascript">jQuery(window).on("load", function(){jQuery(".arm_form_popup_link_' . $form->ID . '").trigger("click")});</script>';
							}
							$content .= '<div class="armclear">&nbsp;</div>';
						}

						if ( $form->type == 'registration' || $form->type == 'forgot_password' || $form->type == 'change_password' ) {
							global $arm_lite_members_activity, $arm_lite_version;
							$arm_request_version = get_bloginfo( 'version' );

						}

						$content .= '</div>';
					}

					$content = apply_filters( 'arm_change_content_after_display_form', $content, $form, $atts, $formRandomID );
					$ARMemberLite->enqueue_angular_script();
				}
			}
			$ARMemberLite->arm_check_font_awesome_icons( $content );
			if ( ! empty( $form_content ) ) {
				$ARMemberLite->arm_check_font_awesome_icons( $form_content );
			}

			$isEnqueueAll = $arm_global_settings->arm_get_single_global_settings( 'enqueue_all_js_css', 0 );
			if ( $isEnqueueAll == '1' ) {
				if ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) {
					$content .= '<script type="text/javascript" data-cfasync="false">
                                        jQuery(document).ready(function (){
                                            arm_do_bootstrap_angular();
                                            ARMFormInitValidation("arm_form' . $formRandomID . '");
                                        });';
					$content .= '</script>';
				}
			}
			$inbuild     = '';
			$hiddenvalue = '';

			global $arm_lite_members_activity, $arm_lite_version;
			$arm_request_version = get_bloginfo( 'version' );

			$hiddenvalue = '  
            <!--Plugin Name: ARMember    
                Plugin Version: ' . get_option( 'armlite_version' ) . ' ' . $inbuild . '
                Developed By: Repute Infosystems
                Developer URL: http://www.reputeinfosystems.com/
            -->';
			return do_shortcode( $content . $hiddenvalue );
		}

		function arm_parse_login_links( $linkLabel, $url = '#', $class = '', $attrs = '' ) {
			if ( strpos( strtoupper( $linkLabel ), 'ARMLINK' ) ) {
				if ( strpos( strtoupper( $linkLabel ), '[ARMLINK]' ) && strpos( strtoupper( $linkLabel ), '[/ARMLINK]' ) === false ) {
					$linkLabel = str_replace( '[ARMLINK]', '<a href="' . esc_url($url) . '" class="' . esc_attr($class) . '" ' . $attrs . '>', $linkLabel );
					$linkLabel = str_replace( '[armlink]', '<a href="' . esc_url($url) . '" class="' . esc_attr($class) . '" ' . $attrs . '>', $linkLabel );
					$linkLabel = $linkLabel . '</a>';
				} elseif ( strpos( strtoupper( $linkLabel ), '[/ARMLINK]' ) && strpos( strtoupper( $linkLabel ), '[ARMLINK]' ) === false ) {
					$linkLabel = '<a href="' . esc_url($url) . '" class="' . esc_attr($class) . '" ' . $attrs . '>' . $linkLabel;
					$linkLabel = str_replace( '[/ARMLINK]', '</a>', $linkLabel );
					$linkLabel = str_replace( '[/armlink]', '</a>', $linkLabel );
				} else {
					$linkLabel = str_replace( '[ARMLINK]', '<a href="' . esc_url($url) . '" class="' . esc_attr($class) . '" ' . $attrs . '>', $linkLabel );
					$linkLabel = str_replace( '[/ARMLINK]', '</a>', $linkLabel );
					$linkLabel = str_replace( '[armlink]', '<a href="' . esc_url($url) . '" class="' . esc_attr($class) . '" ' . $attrs . '>', $linkLabel );
					$linkLabel = str_replace( '[/armlink]', '</a>', $linkLabel );
				}
			} else {
				$linkLabel = '<a href="' . esc_url($url) . '" class="' . esc_attr($class) . '" ' . $attrs . '>' . $linkLabel . '</a>';
			}
			return $linkLabel;
		}

		function arm_member_form_get_single_form_fields( $form, $atts = array(), $formRandomID = '' ) {
			global $wp, $wpdb, $current_user, $ARMemberLite, $arm_global_settings, $arm_social_feature,$arm_lite_members_activity,$arm_reset_file_upload_data_flag;
			$form_id       = $form->ID;
			$field_content = $submit_field = '';

			if(isset($_SESSION['arm_file_upload_arr']) && empty($arm_reset_file_upload_data_flag)){
                unset($_SESSION['arm_file_upload_arr']);
				$arm_reset_file_upload_data_flag=1;
            }

			if ( ! empty( $form ) ) {
				if ( ! empty( $form->fields ) ) {
					$isAvatarField    = false;
					$isSocialField    = false;
					$field_content    = apply_filters( 'arm_change_content_before_field', $field_content, $form );
					$is_hide_username = 0;
					foreach ( $form->fields as $field ) {
						if ( isset( $field['arm_form_field_slug'] ) && (
							( $field['arm_form_field_slug'] == 'user_login' && ! empty( $field['arm_form_field_option']['hide_username'] ) ) ||
							( $field['arm_form_field_slug'] == 'first_name' && ! empty( $field['arm_form_field_option']['hide_firstname'] ) ) ||
							( $field['arm_form_field_slug'] == 'last_name' && ! empty( $field['arm_form_field_option']['hide_lastname'] ) ) )
						) {
							continue;
						}

						$field_options = maybe_unserialize( $field['arm_form_field_option'] );
						if ( ! in_array( $field_options['type'], array( 'html', 'hidden' ) ) ) {
							$field_options = apply_filters( 'arm_change_field_options', $field_options );
						}
						if ( isset( $field_options['meta_key'] ) ) {
							if ( $field_options['meta_key'] == 'user_login' ) {
								if ( isset( $field_options['hide_username'] ) && $field_options['hide_username'] == 1 ) {
									$is_hide_username = 1;
								}
							}
							if ( $field_options['meta_key'] == 'first_name' ) {
								if ( isset( $field_options['hide_firstname'] ) && $field_options['hide_firstname'] == 1 ) {
									$is_hide_firstname = 1;
								}
							}
							if ( $field_options['meta_key'] == 'last_name' ) {
								if ( isset( $field_options['hide_lastname'] ) && $field_options['hide_lastname'] == 1 ) {
									$is_hide_lastname = 1;
								}
							}
						}
					}

					foreach ( $form->fields as $field ) {
						if ( isset( $field['arm_form_field_slug'] ) && ( ( $field['arm_form_field_slug'] == 'user_login' && ! empty( $field['arm_form_field_option']['hide_username'] ) ) ||
							( $field['arm_form_field_slug'] == 'first_name' && ! empty( $field['arm_form_field_option']['hide_firstname'] ) ) ||
							( $field['arm_form_field_slug'] == 'last_name' && ! empty( $field['arm_form_field_option']['hide_lastname'] ) ) ) ) {
							continue;
						}

						$field_options = maybe_unserialize( $field['arm_form_field_option'] );
						if ( ! in_array( $field_options['type'], array( 'html', 'hidden' ) ) ) {
							$field_options = apply_filters( 'arm_change_field_options', $field_options );
						}
						$field         = apply_filters( 'arm_change_field_setting_external', $field, $field_options );
						$form_field_id = $field['arm_form_field_id'];

						if ( ( isset( $atts['type'] ) && $atts['type'] == 'edit_profile' ) || 'edit_profile' == $form->type ) {

							if ( $field_options['type'] == 'password' ) {
								$field_options['required'] = '';
							} elseif ( $field_options['type'] == 'repeat_pass' ) {
								$field_options['required'] = '';
							} elseif ( $field_options['meta_key'] == 'user_login' ) {
								$field_options['disabled'] = '1';
							}
						}
						if ( function_exists( 'extract' ) ) {
							extract( $field_options );
						} else {
							$id                  = $field_options['id'];
							$label               = $field_options['label'];
							$placeholder         = $field_options['placeholder'];
							$type                = $field_options['type'];
							$value               = $field_options['value'];
							$options             = $field_options['options'];
							$bg_color            = $field_options['bg_color'];
							$class               = $field_options['class'];
							$padding             = $field_options['padding'];
							$margin              = $field_options['margin'];
							$allow_ext           = $field_options['allow_ext'];
							$file_size_limit     = $field_options['file_size_limit'];
							$max_date            = $field_options['max_date'];
							$required            = $field_options['required'];
							$hide_username       = $field_options['hide_username'];
							$hide_firstname      = $field_options['hide_firstname'];
							$hide_lastname       = $field_options['hide_lastname'];
							$blank_message       = $field_options['blank_message'];
							$invalid_message     = $field_options['invalid_message'];
							$invalid_username    = $field_options['invalid_username'];
							$invalid_firstname   = $field_options['invalid_firstname'];
							$invalid_lastname    = $field_options['invalid_lastname'];
							$validation_type     = $field_options['validation_type'];
							$regular_expression  = $field_options['regular_expression'];
							$default_field       = $field_options['default_field'];
							$mapfield            = $field_options['mapfield'];
							$ref_field_id        = $field_options['ref_field_id'];
							$enable_repeat_field = $field_options['enable_repeat_field'];
						}
						if(in_array($type, array('file','avatar','profile_cover'))){
                            $file_meta_key = !empty($field_options['meta_key'])?sanitize_text_field($field_options['meta_key']):"";
                            $allow_multiple = !empty($field_options['allow_multiple'])?sanitize_text_field($field_options['allow_multiple']):"";
                            $arm_lite_members_activity->session_for_file_handle($file_meta_key,$ARMemberLite->arm_get_basename($value));
                        }
						$class = ! empty( $class ) ? ' ' . $class : '';

						$prefix_name = 'arm_field[' . $form_id . ']';
						if ( $type == 'avatar' || $field_options['meta_key'] == 'avatar' ) {
							$isAvatarField = true;
						}
						if ( $type == 'submit' ) {
							if ( isset( $atts['type'] ) && $atts['type'] == 'edit_profile' ) {
								$field_options['label'] = ( isset( $atts['submit_text'] ) && ! empty( $atts['submit_text'] ) ) ? $atts['submit_text'] : esc_html__( 'Update Profile', 'armember-membership' );
								if ( $arm_social_feature->isSocialFeature ) {
									/*
																		 * *
									 * Social Fields
									 *
									 * * */
									if ( ! $isSocialField ) {
										$field_content .= '<div class="arm-df__form-group arm-df__form-group_social_fields" id="arm-df__form-group_' . esc_attr($form_field_id) . '" data-field_id="' . esc_attr($form_field_id) . '">';
										if ( ! empty( $atts['social_fields'] ) && isset( $atts['social_fields'] ) ) {
											$extraFields = explode( ',', rtrim( $atts['social_fields'], ',' ) );
										} else {
											$extraFields = array();
										}
										/**
										 * `$extraFields` -- This variable need to get from `Edit Profile` Shortcode argument.
										 * e.g. $extraFields = array('youtube', 'pinterest');
										 */
										$field_content .= $this->arm_social_profile_field_options_html( $form_id, $form_field_id, $field_options, 'active', $form, $extraFields );
										$field_content .= '</div>';
									}

									$common_messages = $arm_global_settings->arm_get_all_common_message_settings();

									if ( isset( $atts['avatar_field'] ) && $atts['avatar_field'] == 'yes' ) {
										if(!isset($_SESSION['arm_file_upload_arr']['avatar'])){
											$arm_lite_members_activity->session_for_file_handle('avatar','');
										}
										if(is_user_logged_in())
										{
											$ARMemberLite->arm_session_start();
											if(empty($_SESSION['arm_additional_form_fields']))
											{
												$_SESSION['arm_additional_form_fields'] = array();
											}
											$_SESSION['arm_additional_form_fields'][$form_id][] = 'avatar';
										}
										$arm_avtar_label         = ( isset( $arm_global_settings->common_message['arm_avtar_label'] ) && $arm_global_settings->common_message['arm_avtar_label'] != '' ) ? $arm_global_settings->common_message['arm_avtar_label'] : esc_html__( 'Avatar', 'armember-membership' );
										$arm_profile_cover_label = ( isset( $arm_global_settings->common_message['arm_profile_cover_label'] ) && $arm_global_settings->common_message['arm_profile_cover_label'] != '' ) ? $arm_global_settings->common_message['arm_profile_cover_label'] : esc_html__( 'Profile Cover', 'armember-membership' );
										if ( ! $isAvatarField ) {
											/**
											 * User Avatar Field
											 */
											$avatar_field_id = 'avatar_' . arm_generate_random_code();
											$avatarOptions   = array(
												'id'       => 'avatar',
												'label'    => $arm_avtar_label,
												'placeholder' => esc_html__( 'Drop file here or click to select.', 'armember-membership' ),
												'type'     => 'avatar',
												'value'    => '',
												'allow_ext' => '',
												'file_size_limit' => '2',
												'meta_key' => 'avatar', // phpcs:ignore
												'required' => 0,
												'blank_message' => esc_html__( 'Please select avatar.', 'armember-membership' ),
												'invalid_message' => esc_html__( 'Invalid image selected.', 'armember-membership' ),
											);
											$avatarOptions   = apply_filters( 'arm_change_field_options', $avatarOptions );
											$submit_field   .= '<div class="arm-df__form-group' . esc_attr($class) . ' arm-df__form-group_avatar" id="arm-df__form-group_' . esc_attr($avatar_field_id) . '" data-field_id="' . esc_attr($avatar_field_id) . '">';
											$submit_field   .= '<div class="arm_form_label_wrapper arm-df__field-label arm_form_member_field_avatar">';
											// $submit_field .= '<div class="arm_member_form_field_label">';
											if ( $required == 1 ) {
												$submit_field .= '<span class="arm-df__label-asterisk arm-df__label-asterisk_' . esc_attr($avatar_field_id) . '">* </span>';
											}
											$submit_field .= '<label class="arm_form_field_label_text" aria-label="' . esc_html__( 'Avatar', 'armember-membership' ) . '">' . esc_html__( 'Avatar', 'armember-membership' ) . '</label>';
											// $submit_field .= '</div>';
											$submit_field .= '</div>';
											$submit_field .= '<div class="arm_label_input_separator"></div>';
											$submit_field .= '<div class="arm-df__form-field">';
											$submit_field .= $this->arm_member_form_get_fields_by_type( $avatarOptions, $avatar_field_id, $form_id, 'active', $form );
											$submit_field .= '</div>';
											$submit_field .= '</div>';
										}
									}
									/**
									 * Profile Cover Field
									 */
									if ( isset( $atts['profile_cover_field'] ) && $atts['profile_cover_field'] == 'yes' ) {
										if(!isset($_SESSION['arm_file_upload_arr']['profile_cover'])){
											$arm_lite_members_activity->session_for_file_handle('profile_cover','');
										}
										if(is_user_logged_in())
										{
											$ARMemberLite->arm_session_start();
											if(empty($_SESSION['arm_additional_form_fields']))
											{
												$_SESSION['arm_additional_form_fields'] = array();
											}
											$_SESSION['arm_additional_form_fields'][$form_id][] = 'profile_cover';
										}
										$profile_cover_field_id  = 'profile_cover_' . arm_generate_random_code();
										$arm_profile_cover_label = ( isset( $arm_global_settings->common_message['arm_profile_cover_label'] ) && $arm_global_settings->common_message['arm_profile_cover_label'] != '' ) ? $arm_global_settings->common_message['arm_profile_cover_label'] : esc_html__( 'Profile Cover', 'armember-membership' );
										$profileCoverOptions     = array(
											'id'          => 'profile_cover',
											'label'       => ( isset( $atts['profile_cover_title'] ) && ! empty( $atts['profile_cover_title'] ) ) ? $atts['profile_cover_title'] : $arm_profile_cover_label,
											'placeholder' => isset( $atts['profile_cover_placeholder'] ) ? $atts['profile_cover_placeholder'] : esc_html__( 'Drop file here or click to select.', 'armember-membership' ),
											'type'        => 'avatar',
											'value'       => '',
											'allow_ext'   => '',
											'file_size_limit' => '10',
											'meta_key'    => 'profile_cover', // phpcs:ignore
											'required'    => 0,
											'blank_message' => esc_html__( 'Please select profile cover.', 'armember-membership' ),
											'invalid_message' => esc_html__( 'Invalid image selected.', 'armember-membership' ),
										);
										$profileCoverOptions     = apply_filters( 'arm_change_field_options', $profileCoverOptions );
										$submit_field           .= '<div class="arm-df__form-group' . esc_attr($class) . ' arm-df__form-group_profile_cover" id="arm-df__form-group_' . esc_attr($profile_cover_field_id) . '" data-field_id="' . esc_attr($profile_cover_field_id) . '">';
										$submit_field           .= '<div class="arm_form_label_wrapper arm-df__field-label arm_form_member_field_profile_cover">';
										// $submit_field .= '<div class="arm_member_form_field_label">';
										if ( $required == 1 ) {
											$submit_field .= '<span class="arm-df__label-asterisk arm-df__label-asterisk_' . esc_attr($profile_cover_field_id) . '">* </span>';
										}
										$submit_field .= '<label class="arm_form_field_label_text" aria-label="' . esc_attr($profileCoverOptions['label']) . '">' . esc_html($profileCoverOptions['label']) . '</label>';
										// $submit_field .= '</div>';
										$submit_field .= '</div>';
										$submit_field .= '<div class="arm_label_input_separator"></div>';
										$submit_field .= '<div class="arm-df__form-field">';
										$submit_field .= $this->arm_member_form_get_fields_by_type( $profileCoverOptions, $profile_cover_field_id, $form_id, 'active', $form );
										$submit_field .= '</div>';
										$submit_field .= '</div>';
									}
								}
							}
							if ( empty( $atts['setup'] ) ) {
								$submit_field .= '<div class="arm-df__form-group' . esc_attr($class) . ' arm-df__form-group_' . esc_attr($type) . '" id="arm-df__form-group_' . esc_attr($form_field_id) . '" data-field_id="' . esc_attr($form_field_id) . '">';
								// $submit_field .= '<div class="arm_form_label_wrapper arm-df__field-label arm_form_member_field_' . $type . '"></div>';
								$submit_field .= '<div class="arm_label_input_separator"></div>';
								$submit_field .= '<div class="arm-df__form-field">';
								$submit_field .= $this->arm_member_form_get_fields_by_type( $field_options, $form_field_id, $form_id, 'active', $form );
								$submit_field .= '</div>';
								$submit_field .= '</div>';
							}
						} elseif ( $type == 'social_fields' ) {
							$isSocialField = true;
							if ( $arm_social_feature->isSocialFeature ) {
								$field_content .= '<div class="arm-df__form-group' . esc_attr($class) . ' arm-df__form-group_social_fields" id="arm-df__form-group_' . esc_attr($form_field_id) . '" data-field_id="' . esc_attr($form_field_id) . '">';
								if ( ! empty( $atts['social_fields'] ) && isset( $atts['social_fields'] ) ) {
									$extraFields = explode( ',', rtrim( $atts['social_fields'], ',' ) );
								} else {
									$extraFields = array();
								}
								/**
								 * `$extraFields` -- This variable need to get from `Edit Profile` Shortcode argument.
								 * e.g. $extraFields = array('youtube', 'pinterest');
								 */
								$field_content .= $this->arm_social_profile_field_options_html( $form_id, $form_field_id, $field_options, 'active', $form, $extraFields );
								$field_content .= '</div>';
							}
						} elseif ( $type == 'hidden' ) {
							$field_content .= '<div class="arm-df__form-group' . esc_attr($class) . ' hidden_field_hide arm-df__form-group_' . esc_attr($type) . '" id="arm-df__form-group_' . esc_attr($form_field_id) . '" data-field_id="' . esc_attr($form_field_id) . '">';
							$field_content .= $this->arm_member_form_get_fields_by_type( $field_options, $form_field_id, $form_id, 'active', $form );
							$field_content .= '</div>';
						} else {
							$fieldBoxStyle   = '';
							$show_rememberme = ( isset( $form->settings['show_rememberme'] ) ) ? $form->settings['show_rememberme'] : 0;
							if ( $type == 'rememberme' && $show_rememberme != 1 ) {
								$fieldBoxStyle = 'display:none;';
							}
                            if($type == 'current_user_pass')
                            {
                                $field_options['required'] = "1";
                                if(!empty($atts['skip_current_password']))
                                {
                                    $field_options['required'] = "";
                                    $fieldBoxStyle = 'display:none;';
                                }
                            }
							$fieldContClass = '';
							if ( $type == 'section' ) {
								$fieldContClass   = ' arm_section_fields_wrapper';
								$margin           = ! empty( $margin ) ? $margin : array();
								$margin['top']    = ( isset( $margin['top'] ) && is_numeric( $margin['top'] ) ) ? $margin['top'] : 20;
								$margin['bottom'] = ( isset( $margin['bottom'] ) && is_numeric( $margin['bottom'] ) ) ? $margin['bottom'] : 20;
								$fieldBoxStyle   .= 'margin-top:' . $margin['top'] . 'px !important;';
								$fieldBoxStyle   .= 'margin-bottom:' . $margin['bottom'] . 'px !important;';
							}

							if ( $type == 'text' ) {
								$arm_form_type_check = ( isset( $atts['type'] ) ) ? $atts['type'] : '';
								if ( $field_options['meta_key'] == 'first_name' && $hide_firstname == 1 && $arm_form_type_check != 'edit_profile' ) {
									$fieldBoxStyle .= 'display: none;';
								} elseif ( $field_options['meta_key'] == 'last_name' && $hide_lastname == 1 && $arm_form_type_check != 'edit_profile' ) {
									$fieldBoxStyle .= 'display: none;';
								} elseif ( $field_options['meta_key'] == 'user_login' && $hide_username == 1 ) {
									$fieldBoxStyle .= 'display: none;';
								}
							}
							$sub_type = ( $field_options['type'] == 'roles' && isset( $field_options['sub_type'] ) ) ? $field_options['sub_type'] : '';

							$field_content .= '<div class="arm-control-group arm-df__form-group' . esc_attr($class) . ' arm-df__form-group_' . esc_attr($type) . esc_attr($fieldContClass) . '" id="arm-df__form-group_' . esc_attr($form_field_id) . '" data-field_id="' . esc_attr($form_field_id) . '" style="' . esc_attr($fieldBoxStyle) . '">';
							if ( ! in_array( $field_options['type'], array( 'html', 'section' ) ) && ! empty( $label ) ) {
								if ( ( $form->settings['style']['form_layout'] != 'writer' && $form->settings['style']['form_layout'] != 'writer_border' ) || $field_options['type'] == 'checkbox' || $field_options['type'] == 'radio' || $field_options['type'] == 'select' || $field_options['type'] == 'file' || $field_options['type'] == 'profile_cover' || $field_options['type'] == 'avatar' || ( $field_options['type'] == 'roles' && $sub_type == 'radio' ) || is_admin() ) {
									$field_content .= '<div class="arm_form_label_wrapper arm-df__field-label arm_form_member_field_' . esc_attr($type) . '">';
									if ( ! in_array( $type, array( 'submit', 'hidden', 'rememberme' ) ) ) {
										if ( $required == 1 ) {
											$field_content .= '<span class="arm-df__label-asterisk arm-df__label-asterisk_' . esc_attr($form_field_id) . '">* </span>';
										}
										$field_content .= '<label class="arm_form_field_label_text" for="arm-df__form-control_' . esc_attr($form_field_id) . '_'. esc_attr($formRandomID) .'" aria-label="' . esc_attr( stripslashes( $label ) ) . '">';
										$field_content .= html_entity_decode( stripslashes( $label ) );
										$field_content .= '</label>';
									}
									$field_content .= '</div>';
								}

								$field_content .= '<div class="arm_label_input_separator"></div>';
							}
							$field_content .= '<div class="arm-df__form-field">';
							$field          = $this->arm_member_form_get_fields_by_type( $field_options, $form_field_id, $form_id, 'active', $form, $formRandomID );
							if ( ! ( $field_options['type'] == 'html' && preg_match( "/\bid=\"" . $form_id . '"/', $field, $match ) && preg_match( "/\[arm_form\b/", $field, $match ) ) ) {
								$field_content .= $field;
							}
							$field_content .= '</div>';
							$field_content  = apply_filters( 'arm_add_content_after_field', $field_content, $field_options, $form, $formRandomID );
							$field_content .= '</div>';
						}
					}
					$field_content  = apply_filters( 'arm_change_content_after_field', $field_content, $form );
					$field_content .= $submit_field;
				}
			}
			return do_shortcode( $field_content );
		}

		function arm_member_form_get_field_html( $form_id = 0, $form_field_id = 0, $field_options = array(), $form_type = 'inactive', $form = '' ) {
			global $wp, $wpdb, $current_user, $arm_slugs, $ARMemberLite, $arm_subscription_plans, $arm_global_settings;
			$field_options = maybe_unserialize( $field_options );
			$field_options = apply_filters( 'arm_change_field_options', $field_options );
			if ( function_exists( 'extract' ) ) {
				extract( $field_options );
			} else {
				$id                  = $field_options['id'];
				$label               = $field_options['label'];
				$placeholder         = $field_options['placeholder'];
				$type                = $field_options['type'];
				$meta_key            = strtolower( $field_options['meta_key'] );
				$sub_type            = $field_options['sub_type'];
				$value               = $field_options['value'];
				$bg_color            = $field_options['bg_color'];
				$padding             = $field_options['padding'];
				$margin              = $field_options['margin'];
				$options             = $field_options['options'];
				$allow_ext           = $field_options['allow_ext'];
				$file_size_limit     = $field_options['file_size_limit'];
				$max_date            = $field_options['max_date'];
				$required            = $field_options['required'];
				$blank_message       = $field_options['blank_message'];
				$invalid_username    = $field_options['invalid_username'];
				$invalid_firstname   = $field_options['invalid_firstname'];
				$invalid_lastname    = $field_options['invalid_lastname'];
				$validation_type     = $field_options['validation_type'];
				$regular_expression  = $field_options['regular_expression'];
				$invalid_message     = $field_options['invalid_message'];
				$default_field       = $field_options['default_field'];
				$mapfield            = $field_options['mapfield'];
				$ref_field_id        = $field_options['ref_field_id'];
				$enable_repeat_field = $field_options['enable_repeat_field'];
			}
			$prefix_name    = 'arm_forms[' . $form_id . ']';
			$material_class = '';
			if ( $type == 'social_fields' ) {
				echo $this->arm_social_profile_field_options_html( $form_id, $form_field_id, $field_options, $form_type, $form ); //phpcs:ignore
			} else {
				if ( isset( $form->settings['style'] ) && ( $form->settings['style']['form_layout'] == 'writer' || $form->settings['style']['form_layout'] == 'writer_border' ) && ! in_array( $type, array( 'radio', 'checkbox', 'rememberme', 'file', 'avatar', 'profile_cover' ) ) ) {
					$material_class = 'layout-gt-sm="row"';
				}

				if ( ( $form->settings['style']['form_layout'] != 'writer' && $form->settings['style']['form_layout'] != 'writer_border' ) || is_admin() ) {
					$class_form_label_wrapper = '';
					if ( $type == 'roles' ) {
						$class_form_label_wrapper = ' arm-df__field-label--roles-' . $sub_type;
					}
					?>
					<div class="arm_form_label_wrapper arm-df__field-label arm_form_member_field_<?php echo esc_attr($type) . esc_attr($class_form_label_wrapper); ?>">
					<?php if ( ! in_array( $type, array( 'submit', 'hidden', 'html', 'section', 'rememberme' ) ) ) { ?>
							<div class="arm-df__label-asterisk arm-df__label-asterisk_<?php echo intval($form_field_id); ?>">
						<?php
						if ( $required == 1 ) {
							echo '* ';
						}
						?>
								</div>
								<label class="arm-df__field-label_text arm_form_field_label_text" aria-label="<?php echo esc_attr( stripslashes( $label ) );?>"><?php echo html_entity_decode( stripslashes( $label ) ); //phpcs:ignore ?></label>
					<?php } ?>
					<?php if ( $form_type == 'inactive' ) : ?>
							<input type="hidden" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][id]" value="<?php echo esc_attr($id); ?>"/>
							<input type="hidden" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][type]" value="<?php echo esc_attr($type); ?>"/>
							<input type="hidden" class="arm_is_default_field" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][default_field]" value="<?php echo esc_attr($default_field); ?>"/>
					<?php endif; ?>
					</div>
					<?php
				}
				?>
				<div class="arm_label_input_separator"></div>
				<div class="arm-df__form-field" <?php echo $material_class; //phpcs:ignore ?>> <?php echo $this->arm_member_form_get_fields_by_type( $field_options, $form_field_id, $form_id, $form_type, $form ); //phpcs:ignore ?> </div>
				<?php if ( $form_type == 'inactive' ) { ?>
					<div class="arm_form_settings_icon">
					<?php if ( $type != 'submit' ) { ?>
							<a href="javascript:void(0)" class="arm_form_member_settings_icon armhelptip" title="<?php esc_attr_e( 'Edit Field Options', 'armember-membership' ); ?>" data-field_id="<?php echo esc_attr($form_field_id); ?>" data-field_type="<?php echo esc_attr($type); ?>"> <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL; ?>/fe_setting.png" onmouseover="this.src = '<?php echo MEMBERSHIPLITE_IMAGES_URL; ?>/fe_setting_hover.png';" onmouseout="this.src = '<?php echo MEMBERSHIPLITE_IMAGES_URL; ?>/fe_setting.png';" style='cursor:pointer'/> </a> <?php //phpcs:ignore ?>
						<?php if ( $default_field != 1 && ! in_array( $type, array( 'repeat_email', 'repeat_pass' ) ) ) { ?>
								<a href="javascript:void(0)" class="arm_form_member_delete_icon armhelptip" data-field_id="<?php echo esc_attr($form_field_id); ?>" data-field_type="<?php echo esc_attr($type); ?>" title="<?php esc_html_e( 'Delete Field', 'armember-membership' ); ?>" onclick="showConfirmBoxCallback(<?php echo esc_attr($form_field_id); ?>);"> <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL; ?>/fe_delete.png" onmouseover="this.src = '<?php echo MEMBERSHIPLITE_IMAGES_URL; ?>/fe_delete_hover.png';" onmouseout="this.src = '<?php echo MEMBERSHIPLITE_IMAGES_URL; ?>/fe_delete.png';" style='cursor:pointer'/> </a> <?php //phpcs:ignore ?>
						<?php } ?>
							<a href="javascript:void(0)" class="arm_form_member_sortable_icon armhelptip" title="<?php esc_html_e( 'Sort Field Order', 'armember-membership' ); ?>"> <img src="<?php echo MEMBERSHIPLITE_IMAGES_URL; ?>/fe_drag.png" onmouseover="this.src = '<?php echo MEMBERSHIPLITE_IMAGES_URL; ?>/fe_drag_hover.png';" onmouseout="this.src = '<?php echo MEMBERSHIPLITE_IMAGES_URL; ?>/fe_drag.png';" style='cursor:pointer'/> </a> <?php //phpcs:ignore ?>
					<?php } ?>
					</div>
					<?php
					if ( $default_field != 1 ) {
						echo $gridAction = $arm_global_settings->arm_get_confirm_box( $form_field_id, esc_html__( 'Are you sure you want to delete this field?', 'armember-membership' ), 'arm_field_delete_ok_btn', $type ); //phpcs:ignore
					}
					?>
							<div class="arm_form_field_settings_menu_wrapper arm_slider_box arm_form_field_settings_menu_wrapper_<?php echo esc_attr($form_field_id); ?>" data-field_id="<?php echo esc_attr($form_field_id); ?>" data-ftype="<?php echo esc_attr($type); ?>">
								<div class="arm_form_field_settings_menu arm_slider_box_container">
									<div class="arm_form_field_settings_menu_arrow arm_slider_box_arrow"></div>
										<div class="arm_slider_box_heading">
											<?php esc_html_e( 'Custom Setting', 'armember-membership' ); ?>
										</div>
										<div class="arm_slider_box_body">
											<?php
											if ( ! in_array( $type, array( 'hidden', 'html', 'section', 'rememberme' ) ) ) :
												?>
													<div class="arm_form_field_settings_menu_inner">
														<div class="arm_form_field_settings_field_label">
															<?php esc_html_e( 'Field Label', 'armember-membership' ); ?>
														</div>
														<div class="arm_form_field_settings_field_val">
															<input type="text" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][label]" class="arm-df__field-label_value field_label_text" value="<?php echo esc_attr( $label ); ?>"/>
														</div>
													</div>
												<?php
												endif;
											?>
											<div class="arm_form_field_settings_menu_inner">
												<div class="arm_form_field_settings_field_label">
													<?php esc_html_e( 'Description', 'armember-membership' ); ?>
												</div>
												<div class="arm_form_field_settings_field_val">
													<input type="text" class="arm_form_field_description_wrapper_value arm_form_field_settings_field_val_input field_description_text" data-ftype="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][description]" value="<?php echo stripslashes_deep( esc_attr($description) ); //phpcs:ignore ?>" />
												</div>
											</div>
							<?php
							$enable_repeat_field = ( isset( $field_options['enable_repeat_field'] ) ) ? $field_options['enable_repeat_field'] : 0;
							switch ( $type ) {
								case 'checkbox':
								case 'radio':
								case 'select':
									$old_options = '';
									if ( ! empty( $options ) ) {
										foreach ( $options as $key => $opt ) {
											$opt          = stripslashes( $opt );
											$old_options .= "$opt\n";
										}
									}
									?>
												<div class="arm_form_field_settings_menu_inner">
													<div class="arm_form_field_settings_field_label">
									<?php esc_html_e( 'Options', 'armember-membership' ); ?>
													</div>
													<div class="arm_form_field_settings_field_val">
														<textarea class="arm_form_field_settings_field_val_input field_options_text" data-ftype="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][options]"><?php echo esc_attr($old_options); ?></textarea>
														<p class="description">
												<?php esc_html_e( 'You should place each option on a new line.', 'armember-membership' ); ?>
															<br/>
												<?php esc_html_e( 'Separate values format should be label:value.', 'armember-membership' ); ?>
																<?php
																if ( trim( $meta_key ) == 'country' ) {
																	?>
																		<br/>
																	<?php
																	echo '<font color="red">' . esc_html__( 'Please don\'t change value of country ID. For example: :1,:2,:3', 'armember-membership' ) . '</font>';

																}
																?>
														</p>
													</div>
												</div>
									<?php
									break;
								case 'role':
								case 'roles':
									?>
												<div class="arm_form_field_settings_menu_inner arm_roles_field_options_type">
													<div class="arm_form_field_settings_field_label">
												<?php esc_html_e( 'Field Type', 'armember-membership' ); ?>
													</div>
													<div class="arm_form_field_settings_field_val">
														<input type="radio" id="role_sub_type_select" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][sub_type]" class="arm_form_field_settings_field_input field_options_text arm_iradio" data-ftype="<?php echo esc_attr($type); ?>" value="select" <?php checked( $sub_type, 'select' ); ?>><label for="role_sub_type_select" aria-label="<?php esc_html_e( 'Select', 'armember-membership' );?>">
												<?php esc_html_e( 'Select', 'armember-membership' ); ?>
														</label>
														<input type="radio" id="role_sub_type_radio" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][sub_type]" class="arm_form_field_settings_field_input field_options_text arm_iradio" data-ftype="<?php echo esc_attr($type); ?>" value="radio" <?php checked( $sub_type, 'radio' ); ?>><label for="role_sub_type_radio" aria-label="<?php esc_html_e( 'Radio', 'armember-membership' );?>">
												<?php esc_html_e( 'Radio', 'armember-membership' ); ?>
														</label>
													</div>
												</div>
												<div class="arm_form_field_settings_menu_inner arm_roles_field_options_type">
													<div class="arm_form_field_settings_field_label">
									<?php esc_html_e( 'Select roles to display at front-end.', 'armember-membership' ); ?>
													</div>
													<div class="arm_form_field_settings_field_val arm_role_field_options">
															<?php
															$allRoles = $arm_global_settings->arm_get_all_roles();
															if ( ! empty( $allRoles ) ) {
																foreach ( $allRoles as $roleK => $roleN ) {
																	$options[ $roleK ] = isset( $options[ $roleK ] ) ? $options[ $roleK ] : '';
																	?>
																<label>
																	<input type="checkbox" value="<?php echo esc_attr($roleN); ?>" <?php checked( $options[ $roleK ], $roleN ); ?> class="arm_icheckbox" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][options][<?php echo esc_attr($roleK); ?>]" />
																	<span class="arm_form_field_settings_notice"><?php echo esc_attr($roleN); ?></span></label>
																	<?php
																}
															}
															?>
													</div>
												</div>
									<?php
									break;
								case 'file':
								case 'profile_cover':
								case 'avatar':
									$placeholder = ( ! empty( $placeholder ) ) ? $placeholder : esc_html__( 'Drop file here or click to select.', 'armember-membership' );
									?>
									<div class="arm_form_field_settings_menu_inner">
										<div class="arm_form_field_settings_field_label">
												<?php esc_html_e( 'Placeholder', 'armember-membership' ); ?>
										</div>
										<div class="arm_form_field_settings_field_val">
											<input type="text" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][placeholder]" class="file_placeholder_text" value="<?php echo esc_attr($placeholder); ?>"/>
										</div>
									</div>
									<?php
									if ( $type != 'avatar' && $type != 'profile_cover' ) {
										?>
											<div class="arm_form_field_settings_menu_inner">
												<div class="arm_form_field_settings_field_label">
												<?php esc_html_e( 'Allowed File Extension', 'armember-membership' ); ?>
												</div>
												<div class="arm_form_field_settings_field_val">
													<input type="text" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][allow_ext]" class="allow_ext arm_form_field_settings_field_input" value="<?php echo esc_attr($allow_ext); ?>"/>
													<p class="description">
													<?php esc_html_e( 'You should place comma separated list of file extensions.', 'armember-membership' ); ?>
														<br/>
													<?php esc_html_e( 'Leave blank for allow all file types.', 'armember-membership' ); ?>
													</p>
												</div>
											</div>
										<?php
									}
									?>
												<div class="arm_form_field_settings_menu_inner">
													<div class="arm_form_field_settings_field_label">
												<?php esc_html_e( 'File Size Limit', 'armember-membership' ); ?>
													</div>
													<div class="arm_form_field_settings_field_val">
														<input type="text" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][file_size_limit]" class="file_size_limit arm_form_field_settings_field_input" value="<?php echo esc_attr($file_size_limit); ?>" style="width: 60px;text-align: center;"/>
														<span>MB</span>
														<p class="description" style="color: #F00">
														<?php
														$max_upload   = (int) ( ini_get( 'upload_max_filesize' ) );
														$max_post     = (int) ( ini_get( 'post_max_size' ) );
														$memory_limit = (int) ( ini_get( 'memory_limit' ) );
														$upload_mb    = min( $max_upload, $max_post, $memory_limit );
														esc_html_e( 'PHP Maximum Upload Size: ' . $upload_mb . 'MB', 'armember-membership' ); //phpcs:ignore
														?>
														</p>
													</div>
												</div>
									<?php
									break;
								case 'hidden':
									?>
												<div class="arm_form_field_settings_menu_inner">
													<div class="arm_form_field_settings_field_label">
															<?php esc_html_e( 'Hidden Value', 'armember-membership' ); ?>
													</div>
													<div class="arm_form_field_settings_field_val">
														<textarea class="arm_form_field_settings_field_val_input field_options_text" data-ftype="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][value]"><?php echo esc_attr($value); ?></textarea>
													</div>
												</div>
												<?php
									break;
								case 'html':
									?>
												<div class="arm_form_field_settings_menu_inner">
													<div class="arm_form_field_settings_field_label arm_html_field_options">
									<?php esc_html_e( 'Html Text', 'armember-membership' ); ?>
													</div>
													<div class="arm_form_field_settings_field_val arm_html_field_options">
														<textarea class="arm_form_field_settings_field_val_input field_options_text" data-ftype="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][value]"><?php echo stripcslashes( esc_attr($value) ); //phpcs:ignore ?></textarea>
													</div>
												</div>
												<?php
									break;
								case 'section':
									?>
												<div class="arm_form_field_settings_menu_inner">
													<div class="arm_form_field_settings_field_label">
									<?php esc_html_e( 'Section Heading', 'armember-membership' ); ?>
													</div>
													<div class="arm_form_field_settings_field_val">
														<textarea class="arm_form_field_settings_field_val_input field_options_text" data-ftype="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][value]"><?php echo stripcslashes( esc_attr($value) ); //phpcs:ignore ?></textarea>
													</div>
												</div>
												<div class="arm_form_field_settings_menu_inner">
													<div class="arm_form_field_settings_field_label">
														<?php esc_html_e( 'Section Margin', 'armember-membership' ); ?>
													</div>
													<div class="arm_form_field_settings_field_val">
														<div class="arm_button_margin_inputs_container">
									<?php
									$margin           = ! empty( $margin ) ? $margin : array();
									$margin['top']    = ( isset( $margin['top'] ) && is_numeric( $margin['top'] ) ) ? $margin['top'] : 20;
									$margin['bottom'] = ( isset( $margin['bottom'] ) && is_numeric( $margin['bottom'] ) ) ? $margin['bottom'] : 20;
									?>
															<div class="arm_button_margin_inputs">
																<input type="text" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][margin][top]" data-type="<?php echo esc_attr($type); ?>" class="arm_section_margin_opt arm_section_margin_top" value="<?php echo esc_attr($margin['top']); ?>" onkeydown="javascript:return checkNumber(event)" min="0" maxlength="3"/>
																<br />
									<?php esc_html_e( 'Top', 'armember-membership' ); ?>
															</div>
															<div class="arm_button_margin_inputs">
																<input type="text" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][margin][bottom]" data-type="<?php echo esc_attr($type); ?>" class="arm_section_margin_opt arm_section_margin_bottom" value="<?php echo esc_attr($margin['bottom']); ?>" onkeydown="javascript:return checkNumber(event)" min="0" maxlength="3"/>
																<br />
									<?php esc_html_e( 'Bottom', 'armember-membership' ); ?>
															</div>
														</div>
													</div>
												</div>
									<?php
									break;
								case 'date':
									?>
												<div class="arm_form_field_settings_menu_inner arm_placeholder_text_container">
													<div class="arm_form_field_settings_field_label">
									<?php esc_html_e( 'Placeholder', 'armember-membership' ); ?>
													</div>
													<div class="arm_form_field_settings_field_val">
														<input type="text" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][placeholder]" class="placeholder_text" value="<?php echo esc_attr($placeholder); ?>"/>
													</div>
												</div>
									<?php
									break;
								case 'rememberme':
									?>
												<div class="arm_form_field_settings_menu_inner">
													<div class="arm_form_field_settings_field_label">
															<?php esc_html_e( 'Label', 'armember-membership' ); ?>
													</div>
													<div class="arm_form_field_settings_field_val">
														<input type="text" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][label]" class="field_options_text" data-ftype="rememberme" value="<?php echo esc_attr( $label ); ?>"/>
													</div>
												</div>
									<?php
									break;
								case 'password':
									$options['minlength']       = ( isset( $options['minlength'] ) ) ? $options['minlength'] : '';
									$options['maxlength']       = ( isset( $options['maxlength'] ) ) ? $options['maxlength'] : '';
									$options['strength_meter']  = ( isset( $options['strength_meter'] ) ) ? $options['strength_meter'] : 0;
									$options['strong_password'] = ( isset( $options['strong_password'] ) ) ? $options['strong_password'] : 0;
									$options['special']         = ( isset( $options['special'] ) ) ? $options['special'] : 0;
									$options['numeric']         = ( isset( $options['numeric'] ) ) ? $options['numeric'] : 0;
									$options['uppercase']       = ( isset( $options['uppercase'] ) ) ? $options['uppercase'] : 0;
									$options['lowercase']       = ( isset( $options['lowercase'] ) ) ? $options['lowercase'] : 0;
									$options['veryweaktext']    = ( ! empty( $options['veryweaktext'] ) ) ? stripslashes( $options['veryweaktext'] ) : esc_html__( 'Strength: Very Weak', 'armember-membership' );
									$options['weaktext']        = ( ! empty( $options['weaktext'] ) ) ? stripslashes( $options['weaktext'] ) : esc_html__( 'Strength: Weak', 'armember-membership' );
									$options['goodtext']        = ( ! empty( $options['goodtext'] ) ) ? stripslashes( $options['goodtext'] ) : esc_html__( 'Strength: Good', 'armember-membership' );
									$options['strongtext']      = ( ! empty( $options['strongtext'] ) ) ? stripslashes( $options['strongtext'] ) : esc_html__( 'Strength: Strong', 'armember-membership' );
									?>
												<div class="arm_form_field_settings_menu_inner arm_placeholder_text_container">
													<div class="arm_form_field_settings_field_label">
														<?php esc_html_e( 'Placeholder', 'armember-membership' ); ?>
													</div>
													<div class="arm_form_field_settings_field_val">
														<input type="text" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][placeholder]" class="placeholder_text" value="<?php echo esc_attr($placeholder); ?>"/>
													</div>
												</div>
												<?php if ($meta_key == 'repeat_pass' || $id == 'repeat_pass' ) : ?>
												<?php else : ?>
													<div class="arm_form_field_settings_menu_inner">
														<div class="arm_form_field_settings_field_label">
													<?php esc_html_e( 'Min Length', 'armember-membership' ); ?>
														</div>
														<div class="arm_form_field_settings_field_val">
															<input type="number" value="<?php echo esc_attr($options['minlength']); ?>" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][options][minlength]" min="0" onkeydown="javascript:return checkNumber(event)"/>
														</div>
													</div>
													<div class="arm_form_field_settings_menu_inner">
														<div class="arm_form_field_settings_field_label">
													<?php esc_html_e( 'Max Length', 'armember-membership' ); ?>
														</div>
														<div class="arm_form_field_settings_field_val">
															<input type="number" value="<?php echo esc_attr($options['maxlength']); ?>" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][options][maxlength]" min="0" onkeydown="javascript:return checkNumber(event)"/>
														</div>
													</div>
													<?php if ( $form->type != 'login' ) : ?>
														<div class="arm_form_field_settings_menu_inner">
															<div class="arm_form_field_settings_field_label" style="padding-top: 0;margin-top: -3px;">
														<?php esc_html_e( 'Display Strength Meter?', 'armember-membership' ); ?>
															</div>
															<div class="arm_form_field_settings_field_val">
														<?php $is_strength_meter = isset( $options['strength_meter'] ) ? $options['strength_meter'] : 0; ?>
																<label style="margin-left: -4px;">
																	<input type="checkbox" value="1" <?php checked( $is_strength_meter, 1 ); ?> class="arm_icheckbox arm_form_field_settings_field_input arm_form_field_settings_strength_meter_field" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][options][strength_meter]" />
																</label>
																<p class="description">
														<?php esc_html_e( 'It will not visible in editor / preview. Please check at front-end.', 'armember-membership' ); ?>
																</p>
															</div>
														</div>
														<div class="arm_form_field_settings_menu_inner arm_form_field_strength_text <?php echo ( $is_strength_meter != 1 ) ? 'hidden_section' : ''; ?>">                         
															<div class="arm_form_field_settings_field_label">
														<?php esc_html_e( 'Very Weak Text', 'armember-membership' ); ?>
															</div>
															<div class="arm_form_field_settings_field_val">
															<input type="textbox" value="<?php echo esc_attr($options['veryweaktext']); ?>" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][options][veryweaktext]"  />
															</div>

															<div class="arm_form_field_settings_field_label">
														<?php esc_html_e( 'Weak Text', 'armember-membership' ); ?>
															</div>
															<div class="arm_form_field_settings_field_val">
																<input type="textbox" value="<?php echo esc_attr($options['weaktext']); ?>" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][options][weaktext]" />
															</div>

															<div class="arm_form_field_settings_field_label">
														<?php esc_html_e( 'Good Text', 'armember-membership' ); ?>
															</div>
															<div class="arm_form_field_settings_field_val">
																<input type="textbox" value="<?php echo esc_attr($options['goodtext']); ?>" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][options][goodtext]"  />
															</div>

															<div class="arm_form_field_settings_field_label">
														<?php esc_html_e( 'Strong Text', 'armember-membership' ); ?>
															</div>
															<div class="arm_form_field_settings_field_val">
																<input type="textbox" value="<?php echo esc_attr($options['strongtext']); ?>" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][options][strongtext]" />
															</div>
														</div> 
														<div class="arm_form_field_settings_menu_inner">
															<div class="arm_form_field_settings_field_label">
																<?php esc_html_e( 'Strong Password?', 'armember-membership' ); ?>
															</div>
															<div class="arm_form_field_settings_field_val">
														<?php $is_strong_password = isset( $options['strong_password'] ) ? $options['strong_password'] : 0; ?>
																<label style="margin-left: -4px;">
																	<input type="checkbox" value="1" <?php checked( $is_strong_password, 1 ); ?> class="arm_icheckbox arm_form_field_settings_field_input arm_form_field_settings_strong_password_field" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][options][strong_password]"/>
																	<span class="arm_form_field_settings_notice">
														<?php esc_html_e( 'Enable Strong Password?', 'armember-membership' ); ?>
																	</span></label>
																<div class="arm_strong_password_options <?php echo ( $is_strong_password != 1 ) ? 'hidden_section' : ''; ?>">
																	<label>
																		<input type="checkbox" value="1" <?php checked( $options['special'], 1 ); ?> class="arm_icheckbox" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][options][special]" />
																		<span class="arm_form_field_settings_notice">
														<?php esc_html_e( 'Require Special Charecter?', 'armember-membership' ); ?>
																		</span></label>
																	<label>
																		<input type="checkbox" value="1" <?php checked( $options['numeric'], 1 ); ?> class="arm_icheckbox" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][options][numeric]" />
																		<span class="arm_form_field_settings_notice">
																<?php esc_html_e( 'Require Numeric Value?', 'armember-membership' ); ?>
																		</span></label>
																	<label>
																		<input type="checkbox" value="1" <?php checked( $options['uppercase'], 1 ); ?> class="arm_icheckbox" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][options][uppercase]" />
																		<span class="arm_form_field_settings_notice">
														<?php esc_html_e( 'Require Uppercase Character?', 'armember-membership' ); ?>
																		</span></label>
																	<label>
																		<input type="checkbox" value="1" <?php checked( $options['lowercase'], 1 ); ?> class="arm_icheckbox" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][options][lowercase]" />
																		<span class="arm_form_field_settings_notice">
														<?php esc_html_e( 'Require Lowercase Character?', 'armember-membership' ); ?>
																		</span></label>
																</div>
															</div>
														</div>
										<?php endif; ?>
															<?php if ( $form->type == 'registration' ) : ?>
														<div class="arm_form_field_settings_menu_inner">
															<div class="arm_form_field_settings_field_label" style="padding-top: 0;margin-top: -3px;">
																<?php esc_html_e( 'Enable Confirm Password?', 'armember-membership' ); ?>
															</div>
															<div class="arm_form_field_settings_field_val">
																<label style="margin-left: -4px;">
																	<input type="checkbox" value="1" <?php checked( $enable_repeat_field, 1 ); ?> class="arm_icheckbox arm_form_field_settings_field_input arm_enable_repeat_field" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][enable_repeat_field]" data-field_id="<?php echo esc_attr($form_field_id); ?>" data-field_type="repeat_pass"/>
																</label>
															</div>
														</div>
																		<?php endif; ?>
									<?php endif; ?>
									<?php
									break;
								case 'email':
									?>
												<div class="arm_form_field_settings_menu_inner arm_placeholder_text_container">
													<div class="arm_form_field_settings_field_label">
									<?php esc_html_e( 'Placeholder', 'armember-membership' ); ?>
													</div>
													<div class="arm_form_field_settings_field_val">
														<input type="text" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][placeholder]" class="placeholder_text" value="<?php echo esc_attr($placeholder); ?>"/>
													</div>
												</div>
												<div class="arm_form_field_settings_menu_inner">
													<div class="arm_form_field_settings_field_label" style="padding-top: 0;margin-top: -3px;">
									<?php esc_html_e( 'Enable Confirm Email Address?', 'armember-membership' ); ?>
													</div>
													<div class="arm_form_field_settings_field_val">
														<label style="margin-left: -4px;">
															<input type="checkbox" value="1" <?php checked( $enable_repeat_field, 1 ); ?> class="arm_icheckbox arm_form_field_settings_field_input arm_enable_repeat_field" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][enable_repeat_field]" data-field_id="<?php echo esc_attr($form_field_id); ?>" data-field_type="repeat_email"/>
														</label>
													</div>
												</div>
														<?php
									break;
								case 'current_user_pass':
								case 'repeat_pass':
								case 'repeat_email':
									?>
												<input type="hidden" value="<?php echo esc_attr($required); ?>" class="arm_icheckbox arm_form_field_settings_field_input arm_form_field_settings_required_field" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][required]"/>
												<div class="arm_form_field_settings_menu_inner arm_placeholder_text_container">
													<div class="arm_form_field_settings_field_label">
												<?php esc_html_e( 'Placeholder', 'armember-membership' ); ?>
													</div>
													<div class="arm_form_field_settings_field_val">
														<input type="text" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][placeholder]" class="placeholder_text" value="<?php echo esc_attr($placeholder); ?>"/>
													</div>
												</div>
									<?php
									break;
								case 'submit':
									break;
								default:
									$options['minlength'] = ( isset( $options['minlength'] ) ) ? $options['minlength'] : '';
									$options['maxlength'] = ( isset( $options['maxlength'] ) ) ? $options['maxlength'] : '';
									?>
														<?php /* -------------------- Form Field Settings  ---------------------------------------------------------------- */ ?>  

									<?php /* ------------------------- Placeholder ----------------------------- */ ?>             
												<div class="arm_form_field_settings_menu_inner arm_placeholder_text_container">
													<div class="arm_form_field_settings_field_label">
									<?php esc_html_e( 'Placeholder', 'armember-membership' ); ?>
													</div>
													<div class="arm_form_field_settings_field_val">
														<input type="text" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][placeholder]" class="placeholder_text" value="<?php echo esc_attr($placeholder); ?>"/>
													</div>
												</div>

												<?php /* ------------------------- MinLength & Maxlength  ----------------------------- */ ?>                     
												<?php if ( $type != 'email' ) { ?>
													<div class="arm_form_field_settings_menu_inner">
														<div class="arm_form_field_settings_field_label">
															<?php esc_html_e( 'Min Length', 'armember-membership' ); ?>
														</div>
														<div class="arm_form_field_settings_field_val">
															<input type="number" value="<?php echo esc_attr($options['minlength']); ?>" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][options][minlength]" min="0" onkeydown="javascript:return checkNumber(event)"/>
														</div>
													</div>
													<div class="arm_form_field_settings_menu_inner">
														<div class="arm_form_field_settings_field_label">
													<?php esc_html_e( 'Max Length', 'armember-membership' ); ?>
														</div>
														<div class="arm_form_field_settings_field_val">
															<input type="number" value="<?php echo esc_attr($options['maxlength']); ?>" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][options][maxlength]" min="0" onkeydown="javascript:return checkNumber(event)"/>
														</div>
													</div>
													<?php
												}
									break;
							}
							?>

							<?php /* ------------ Hide Username, Firts Name & LastName -------------------------------- */ ?>        
							<?php
							if ( ! in_array( $type, array( 'submit' ) ) ) {

								if ( $default_field == 1 && in_array( $meta_key, array( 'user_login' ) ) && $form->type == 'registration' ) {
									?>
								<div class="arm_form_field_settings_menu_inner">
									<div class="arm_form_field_settings_field_label">
									<?php esc_html_e( 'Hide username field and assign username with email', 'armember-membership' ); ?>
									</div>
									<div class="arm_form_field_settings_field_val">
										<label style="margin-left: -4px;">
											<input type="checkbox" value="1" <?php checked( $hide_username, 1 ); ?> class="arm_icheckbox arm_form_field_settings_field_input arm_form_field_settings_required_field" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][hide_username]" />
										</label>
									</div>
								</div>
									<?php
								}

								if ( $default_field == 1 && in_array( $meta_key, array( 'first_name' ) ) && $form->type == 'registration' ) {
									?>
								<div class="arm_form_field_settings_menu_inner">
									<div class="arm_form_field_settings_field_label">
									<?php esc_html_e( 'Hide First Name field', 'armember-membership' ); ?>
									</div>
									<div class="arm_form_field_settings_field_val">
										<label style="margin-left: -4px;">
											<input type="checkbox" value="1" <?php checked( $hide_firstname, 1 ); ?> class="arm_icheckbox arm_form_field_settings_field_input arm_form_field_settings_required_field" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][hide_firstname]" />
										</label>
									</div>
								</div>
									<?php
								}

								if ( in_array( $meta_key, array( 'last_name' ) ) && $form->type == 'registration' ) {
									?>
								<div class="arm_form_field_settings_menu_inner">
									<div class="arm_form_field_settings_field_label">
										<?php esc_html_e( 'Hide Last Name field', 'armember-membership' ); ?>
									</div>
									<div class="arm_form_field_settings_field_val">
										<label style="margin-left: -4px;">
											<input type="checkbox" value="1" <?php checked( $hide_lastname, 1 ); ?> class="arm_icheckbox arm_form_field_settings_field_input arm_form_field_settings_required_field" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][hide_lastname]" />
										</label>
									</div>
								</div>
									<?php
								}

								/* --------------------------- Required Checkbox ---------------------------------- */

								if ( in_array( $form->type, array( 'login', 'change_password' ) ) && in_array( $meta_key, array( 'user_login', 'user_email', 'user_pass','current_user_pass' ) ) ) {
									?>
								<input type="checkbox" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][required]" value="1" checked="checked" class="arm_form_field_settings_required_field" style="display: none;"/>
										<?php

								} else {
									if ( $default_field == 1 && in_array( $meta_key, array( 'first_name', 'last_name', 'user_login', 'user_email','current_user_pass' ) ) ) {
										?>
								<input type="checkbox" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][required]" value="1" checked="checked" class="arm_form_field_settings_required_field" style="display: none;"/>
										<?php
									} else {
										if ( ! in_array( $type, array( 'hidden', 'html', 'section', 'rememberme', 'repeat_pass', 'repeat_email' ) ) ) {
											?>
									<div class="arm_form_field_settings_menu_inner">
										<div class="arm_form_field_settings_field_label">
											<?php esc_html_e( 'Required', 'armember-membership' ); ?>
										</div>
										<div class="arm_form_field_settings_field_val">
											<label style="margin-left: -4px;">
												<input type="checkbox" value="1" <?php checked( $required, 1 ); ?> class="arm_icheckbox arm_form_field_settings_field_input arm_form_field_settings_required_field" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][required]" />
											</label>
										</div>
									</div>
											<?php
										}
									}
								}
								?>


								<?php
								/* -------------------------- Validation ----------------------------------------- */

								if ( $type == 'text' && ! in_array( $meta_key, array( 'user_login', 'user_email', 'user_pass' ) ) ) {
									?>
								<div class="arm_form_field_settings_menu_inner">
									<div class="arm_form_field_settings_field_label">
												<?php esc_html_e( 'Validation', 'armember-membership' ); ?>
									</div>
									<div class="arm_form_field_settings_field_val">
									<?php $validation_type = isset( $validation_type ) ? $validation_type : 'custom_validation_none'; ?>
										<input id="arm_form_field_settings_validation_type_<?php echo esc_attr($form_field_id); ?>" field_id = "<?php echo esc_attr($form_field_id); ?>" type='hidden' name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][validation_type]" class="arm_form_field_settings_field_input arm_form_field_settings_validation_type" value="<?php echo esc_attr($validation_type); ?>" />
										<dl class="arm_selectbox column_level_dd arm_width_210">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_form_field_settings_validation_type_<?php echo esc_attr($form_field_id); ?>">
													<li data-value="custom_validation_none" data-label="<?php esc_attr_e( 'None', 'armember-membership' ); ?>"><?php esc_html_e( 'None', 'armember-membership' ); ?></li>
													<li  data-value="customvalidationalpha" data-label="<?php esc_attr_e( 'Only Alphabets', 'armember-membership' ); ?>"><?php esc_html_e( 'Only Alphabets', 'armember-membership' ); ?></li>
													<li data-value="customvalidationnumber" data-label="<?php esc_attr_e( 'Only Numbers', 'armember-membership' ); ?>"><?php esc_html_e( 'Only Numbers', 'armember-membership' ); ?></li>
													<li data-value="customvalidationalphanumber" data-label="<?php esc_attr_e( 'Only Alphabets & Numbers', 'armember-membership' ); ?>"><?php esc_html_e( 'Only Alphabets & Numbers', 'armember-membership' ); ?></li>
													<li data-value="customvalidationregex" data-label="<?php esc_attr_e( 'Regular Expression', 'armember-membership' ); ?>"><?php esc_html_e( 'Regular Expression', 'armember-membership' ); ?></li>
												</ul>
											</dd>
										</dl>
									</div>
								</div>
									<?php
												$disabled_validation_msg     = '';
												$disabled_regular_expression = '';
									if ( $validation_type == 'custom_validation_none' ) {
										$disabled_validation_msg     = 'disabled="disabled"';
										$disabled_regular_expression = 'disabled="disabled"';
									}
									if ( $validation_type != 'customvalidationregex' ) {
										$disabled_regular_expression = 'disabled="disabled"';
									}
									?>
								<div class="arm_form_field_settings_menu_inner">
									<div class="arm_form_field_settings_field_label">
									<?php esc_html_e( 'Validation message', 'armember-membership' ); ?>
									</div>
									<div class="arm_form_field_settings_field_val">
										<input type="text" class="arm_form_field_settings_field_input arm_form_field_settings_validation_msg" <?php echo esc_attr($disabled_validation_msg); //phpcs:ignore ?> name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][invalid_message]" value="<?php echo stripcslashes( esc_attr($invalid_message) ); ?>" id="arm_form_field_settings_validation_msg_<?php echo esc_attr($form_field_id); ?>"/>

									</div>
								</div>
								<div class="arm_form_field_settings_menu_inner">
									<div class="arm_form_field_settings_field_label">
									<?php esc_html_e( 'Regular Expression', 'armember-membership' ); ?>
									</div>
									<div class="arm_form_field_settings_field_val">
										<input type="text" value="<?php echo esc_attr($regular_expression); //phpcs:ignore ?>" <?php echo esc_attr($disabled_regular_expression); //phpcs:ignore ?> class="arm_form_field_settings_field_input arm_form_field_settings_regular_expression" id="arm_form_field_settings_regular_expression_<?php echo esc_attr($form_field_id); ?>" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][regular_expression]"/>
										<br/>
										<span><?php esc_html_e( 'e.g.', 'armember-membership' ); ?>  <b>/^.+@.+\..+$/</b></span>
									</div>
								</div> 
									<?php
								}
								?>


								<?php
								/* --------------------------------- Metakey ------------------------------------- */

								if ( $default_field != 1 && ! in_array( $type, array( 'avatar', 'profile_cover', 'roles', 'html', 'section', 'rememberme', 'repeat_pass', 'repeat_email', 'password' ) ) ) {
									?>
								<div class="arm_form_field_settings_menu_inner">
									<div class="arm_form_field_settings_field_label">
									<?php
									esc_html_e( 'Meta Key', 'armember-membership' );
									echo $type; //phpcs:ignore
									?>
									</div>
									<div class="arm_form_field_settings_field_val">
										<input type="text" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][meta_key]" value="<?php echo strtolower( esc_attr($meta_key) ); //phpcs:ignore ?>" class="arm_form_field_settings_field_input arm_form_field_settings_meta_key"/>
									</div>
								</div>
									<?php
								} else {
									?>
								<input type="hidden" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][meta_key]" value="<?php echo strtolower( esc_attr($meta_key) ); //phpcs:ignore ?>" class="arm_form_field_settings_field_input arm_form_field_settings_meta_key"/>
							<?php } ?>


								<?php
								/* ---------------------------------- Blank Field Message & Invalid Field Message  ----------------------------- */

								if ( ! in_array( $type, array( 'hidden', 'html', 'section', 'rememberme' ) ) ) {
									?>
								<div class="arm_form_field_settings_menu_inner">
									<div class="arm_form_field_settings_field_label">
												<?php esc_html_e( 'Blank field message', 'armember-membership' ); ?>
									</div>
									<div class="arm_form_field_settings_field_val">
										<input type="text" value="<?php echo stripcslashes( esc_attr($blank_message) ); //phpcs:ignore ?>" class="arm_form_field_settings_field_input arm_form_field_settings_blank_msg" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][blank_message]"/>
									</div>
								</div>
									<?php if ( ! in_array( $type, array( 'text', 'password', 'select', 'checkbox', 'radio', 'textarea' ) ) ) { ?>
									<div class="arm_form_field_settings_menu_inner">
										<div class="arm_form_field_settings_field_label">
											<?php esc_html_e( 'Invalid field message', 'armember-membership' ); ?>
										</div>
										<div class="arm_form_field_settings_field_val">
											<input type="text" class="arm_form_field_settings_field_input arm_form_field_settings_invalid_msg" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][invalid_message]" value="<?php echo stripcslashes( esc_attr($invalid_message) ); //phpcs:ignore ?>"/>
										</div>
									</div>
										<?php
									}
								}

								if ( $default_field == 1 && in_array( $meta_key, array( 'user_login' ) ) && $form->type == 'registration' ) {
									?>
								<div class="arm_form_field_settings_menu_inner">
									<div class="arm_form_field_settings_field_label">
									<?php esc_html_e( 'Invalid field message', 'armember-membership' ); ?>
									</div>
									<div class="arm_form_field_settings_field_val">
										<input type="text" class="arm_form_field_settings_field_input arm_form_field_settings_invalid_username" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][invalid_username]" value="<?php echo stripcslashes( esc_attr($invalid_username) ); //phpcs:ignore ?>"/>
									</div>
								</div>
										<?php
								}

								if ( $default_field == 1 && in_array( $meta_key, array( 'first_name' ) ) && $form->type == 'registration' ) {
									?>
								<div class="arm_form_field_settings_menu_inner">
									<div class="arm_form_field_settings_field_label">
									<?php esc_html_e( 'Invalid field message', 'armember-membership' ); ?>
									</div>
									<div class="arm_form_field_settings_field_val">
										<input type="text" class="arm_form_field_settings_field_input arm_form_field_settings_invalid_firstname" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][invalid_firstname]" value="<?php echo stripcslashes( esc_attr($invalid_firstname) ); //phpcs:ignore ?>"/>
									</div>
								</div>
									<?php
								}

								if ( in_array( $meta_key, array( 'last_name' ) ) && $form->type == 'registration' ) {
									?>
								<div class="arm_form_field_settings_menu_inner">
									<div class="arm_form_field_settings_field_label">
												<?php esc_html_e( 'Invalid field message', 'armember-membership' ); ?>
									</div>
									<div class="arm_form_field_settings_field_val">
										<input type="text" class="arm_form_field_settings_field_input arm_form_field_settings_invalid_lastname" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][invalid_lastname]" value="<?php echo stripcslashes( esc_attr($invalid_lastname) ); //phpcs:ignore ?>"/>
									</div>
								</div>
									<?php
								}
								?>

								<?php
								/* =============================./Begin Iconic Support Options/.============================= */
								if ( in_array( $type, array( 'text', 'email', 'repeat_email', 'password', 'repeat_pass', 'url', 'date' ) ) ) {
									?>
								<div class="arm_member_form_iconic_options">
									<div class="arm_form_field_settings_menu_inner">
										<div class="arm_form_field_settings_field_label">
									<?php esc_html_e( 'Add Icon', 'armember-membership' ); ?>
										</div>
										<div class="arm_form_field_settings_field_val">
											<div class="arm_field_prefix_suffix_wrapper" id="arm_field_prefix_suffix_wrapper_<?php echo esc_attr($form_field_id); ?>">
												<div class="arm_prefix_wrapper arm_ps_icons_opt_wraper" style="width: 60px;">
													<div class="arm_prefix_suffix_container_wrapper" data-type="prefix" data-field_id="<?php echo esc_attr($form_field_id); ?>" id="arm_edit_prefix_<?php echo esc_attr($form_field_id); ?>" data-toggle="armmodal">
														<div class="arm_prefix_container" id="arm_select_prefix_<?php echo esc_attr($form_field_id); ?>">
									<?php
									if ( ! empty( $field_options['prefix'] ) ) {
										if ( strpos( $field_options['prefix'], ' ' ) === false ) {
											$field_options['prefix'] = 'armfa ' . $field_options['prefix'];
										}
										echo '<i class="' . esc_attr($field_options['prefix']) . '"></i>';
									} else {
										esc_html_e( 'No Icon', 'armember-membership' );
									}
									?>
														</div>
														<input type="hidden" id="arm_prefix_<?php echo esc_attr($form_field_id); ?>" value="<?php echo esc_attr($field_options['prefix']); ?>">
														<div class="arm_prefix_suffix_action_container">
															<div class="arm_prefix_suffix_action" title="Change Icon"><i class="armfa armfa-caret-down armfa-lg"></i></div>
														</div>
													</div>
													<div class="arm_prefix_suffix_icons_container arm_slider_box"></div>
													<div class="armclear"></div>
													<div class="howto">
									<?php esc_html_e( 'Prefix', 'armember-membership' ); ?>
													</div>
													<input type="hidden" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][prefix]" value="<?php echo esc_attr($field_options['prefix']); ?>" id="arm_field_prefix_<?php echo esc_attr($form_field_id); ?>"/>
												</div>
												<div class="arm_suffix_wrapper arm_ps_icons_opt_wraper" style="width: 60px;<?php echo ( is_rtl() ) ? 'margin-right: 15px;' : 'margin-left: 15px;'; ?>">
													<div class="arm_prefix_suffix_container_wrapper" data-type="suffix" data-field_id="<?php echo esc_attr($form_field_id); ?>" id="arm_edit_suffix_<?php echo esc_attr($form_field_id); ?>" data-toggle="armmodal">
														<div class="arm_suffix_container" id="arm_select_suffix_<?php echo esc_attr($form_field_id); ?>">
									<?php
									if ( ! empty( $field_options['suffix'] ) ) {
										if ( strpos( $field_options['suffix'], ' ' ) === false ) {
											$field_options['suffix'] = 'armfa ' . $field_options['suffix'];
										}
										echo '<i class="' . esc_attr($field_options['suffix']) . '"></i>';
									} else {
										esc_html_e( 'No Icon', 'armember-membership' );
									}
									?>
														</div>
														<input type="hidden" id="arm_suffix_<?php echo esc_attr($form_field_id); ?>" value="<?php echo esc_attr($field_options['suffix']); ?>">
														<div class="arm_prefix_suffix_action_container">
															<div class="arm_prefix_suffix_action" title="Change Icon"><i class="armfa armfa-caret-down armfa-lg"></i></div>
														</div>
													</div>
													<div class="arm_prefix_suffix_icons_container arm_slider_box"></div>
													<div class="armclear"></div>
													<div class="howto">
									<?php esc_html_e( 'Suffix', 'armember-membership' ); ?>
													</div>
													<input type="hidden" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][suffix]" value="<?php echo esc_attr($field_options['suffix']); ?>" id="arm_field_suffix_<?php echo esc_attr($form_field_id); ?>"/>
												</div>
											</div>
										</div>
									</div>
								</div>
					<?php } ?>

								   
		


								<?php
								if ( in_array( $type, array( 'date' ) ) ) {
									$cal_locales = array(
										''      => esc_html__( 'English/Western', 'armember-membership' ),
										'af'    => esc_html__( 'Afrikaans', 'armember-membership' ),
										'sq'    => esc_html__( 'Albanian', 'armember-membership' ),
										'ar'    => esc_html__( 'Arabic', 'armember-membership' ),
										'hy-am' => esc_html__( 'Armenian', 'armember-membership' ),
										'az'    => esc_html__( 'Azerbaijani', 'armember-membership' ),
										'eu'    => esc_html__( 'Basque', 'armember-membership' ),
										'bs'    => esc_html__( 'Bosnian', 'armember-membership' ),
										'bg'    => esc_html__( 'Bulgarian', 'armember-membership' ),
										'ca'    => esc_html__( 'Catalan', 'armember-membership' ),
										'zh-CN' => esc_html__( 'Chinese Simplified', 'armember-membership' ),
										'zh-TW' => esc_html__( 'Chinese Traditional', 'armember-membership' ),
										'hr'    => esc_html__( 'Croatian', 'armember-membership' ),
										'cs'    => esc_html__( 'Czech', 'armember-membership' ),
										'da'    => esc_html__( 'Danish', 'armember-membership' ),
										'nl'    => esc_html__( 'Dutch', 'armember-membership' ),
										'en-GB' => esc_html__( 'English/UK', 'armember-membership' ),
										'eo'    => esc_html__( 'Esperanto', 'armember-membership' ),
										'et'    => esc_html__( 'Estonian', 'armember-membership' ),
										'fo'    => esc_html__( 'Faroese', 'armember-membership' ),
										'fa'    => esc_html__( 'Farsi/Persian', 'armember-membership' ),
										'fi'    => esc_html__( 'Finnish', 'armember-membership' ),
										'fr'    => esc_html__( 'French', 'armember-membership' ),
										'fr-CH' => esc_html__( 'French/Swiss', 'armember-membership' ),
										'de'    => esc_html__( 'German', 'armember-membership' ),
										'el'    => esc_html__( 'Greek', 'armember-membership' ),
										'he'    => esc_html__( 'Hebrew', 'armember-membership' ),
										'hu'    => esc_html__( 'Hungarian', 'armember-membership' ),
										'is'    => esc_html__( 'Icelandic', 'armember-membership' ),
										'it'    => esc_html__( 'Italian', 'armember-membership' ),
										'ja'    => esc_html__( 'Japanese', 'armember-membership' ),
										'ko'    => esc_html__( 'Korean', 'armember-membership' ),
										'lv'    => esc_html__( 'Latvian', 'armember-membership' ),
										'lt'    => esc_html__( 'Lithuanian', 'armember-membership' ),
										'nb'    => esc_html__( 'Norwegian', 'armember-membership' ),
										'pl'    => esc_html__( 'Polish', 'armember-membership' ),
										'pt-BR' => esc_html__( 'Portuguese/Brazilian', 'armember-membership' ),
										'ro'    => esc_html__( 'Romanian', 'armember-membership' ),
										'ru'    => esc_html__( 'Russian', 'armember-membership' ),
										'sr'    => esc_html__( 'Serbian', 'armember-membership' ),
										'sr-SR' => esc_html__( 'Serbian', 'armember-membership' ),
										'sk'    => esc_html__( 'Slovak', 'armember-membership' ),
										'sl'    => esc_html__( 'Slovenian', 'armember-membership' ),
										'es'    => esc_html__( 'Spanish', 'armember-membership' ),
										'sv'    => esc_html__( 'Swedish', 'armember-membership' ),
										'ta'    => esc_html__( 'Tamil', 'armember-membership' ),
										'th'    => esc_html__( 'Thai', 'armember-membership' ),
										'tr'    => esc_html__( 'Turkish', 'armember-membership' ),
										'uk'    => esc_html__( 'Ukrainian', 'armember-membership' ),
										'vi'    => esc_html__( 'Vietnamese', 'armember-membership' ),
									);
									?>
		<div class="arm_form_field_settings_menu_inner">
		<div class="arm_form_field_settings_field_label">
									<?php esc_html_e( 'Calendar Localization', 'armember-membership' ); ?>
		</div>
		<div class="arm_form_field_settings_field_val">
		<input type="hidden" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][cal_localization]" class="arm_form_field_cal_localization_wrapper_value field_cal_localization_text" id="arm_cal_localization" value="<?php echo esc_attr( $cal_localization ); ?>"/>
		<dl class="arm_selectbox column_level_dd" >
			<dt style="border: 1px solid #dbe1e8; width: 197px; height: 22px; border-radius: 5px; padding: 3px 5px;"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
			<dd>
				<ul data-id="arm_cal_localization" class="arm_conditional_plans_li" style="height:88px;">
													<?php
													if ( ! empty( $cal_locales ) ) {
														foreach ( $cal_locales as $lan_key => $lan_val ) {
															?>
															<li data-label="<?php echo esc_attr($lan_val); ?>" data-value="<?php echo esc_attr($lan_key); ?>"><?php echo esc_html($lan_val); ?></li>
																					   <?php
														}
													}
													?>
																	</ul>
																</dd>
															</dl>
														</div>
													</div>
																<?php } ?>


												<div class="arm_form_field_settings_menu_inner">
													<div class="arm_form_field_settings_field_label"></div>
													<div class="arm_form_field_settings_field_val">
														<input type="hidden" name="<?php echo esc_attr($prefix_name); ?>[<?php echo esc_attr($form_field_id); ?>][ref_field_id]" value="<?php echo esc_attr($ref_field_id); ?>" class="arm_form_field_ref_field_id arm_form_field_ref_field_<?php echo esc_attr($ref_field_id); ?>"/>
														<button class="arm_save_btn arm_form_field_settings_field_val_ok_btn" type="button" name="arm_settings_form_addnew_form_btn" field_id='<?php echo esc_attr($form_field_id); ?>'>
														<?php esc_html_e( 'Ok', 'armember-membership' ); ?>
														</button>
														<img src="<?php echo MEMBERSHIPLITE_IMAGES_URL . '/arm_loader.gif'; //phpcs:ignore ?>" class="arm_field_loader_img" style="display:none;" width="24" height="24" /> </div>
												</div>
									<?php
							}
							?>
							</div>
						</div>
					</div>
				<?php } ?>
				<div class="armclear"></div>
				<?php
			}
		}

		function arm_social_profile_field_options_html( $form_id = 0, $form_field_id = 0, $field_options = array(), $form_type = 'inactive', $form = '', $extraFields = array() ) {
			global $wp, $wpdb, $arm_slugs, $ARMemberLite, $arm_global_settings;
			$socialProfileFields = $this->arm_social_profile_field_types();
			$activeSocialFields  = isset( $field_options['options'] ) && ! empty( $field_options['options'] ) ? $field_options['options'] : array();
			if ( ! empty( $extraFields ) ) {
				foreach ( $extraFields as $sftype ) {
					if ( ! in_array( $sftype, $activeSocialFields ) ) {
						$activeSocialFields[] = $sftype;
					}
				}
			}
			$field_class      = ! empty( $field_options['class'] ) ? ' ' . $field_options['class'] : '';
			$prefix_name      = 'arm_forms[' . $form_id . ']';
			$socialFieldsHtml = '<div class="arm_form_social_profile_fields_wrapper">';
			$ffield_label     = $selectedSPFOpt = '';
			$selectedSPFOpt  .= '<input type="hidden" name="' . esc_attr( $prefix_name ) . '[' . esc_attr( $form_field_id ) . '][id]" value="' . esc_attr( $field_options['id'] ) . '"/><input type="hidden" name="' . esc_attr( $prefix_name ) . '[' . esc_attr( $form_field_id ) . '][type]" value="social_fields"/><input type="hidden" name="' . esc_attr( $prefix_name ) . '[' . esc_attr( $form_field_id ) . '][label]" value="' . esc_attr( $field_options['label'] ) . '"/><input type="hidden" name="' . esc_attr( $prefix_name ) . '[' . esc_attr( $form_field_id ) . '][meta_key]" value="' . esc_attr( $field_options['meta_key'] ) . '"/><input type="hidden" class="arm_is_default_field" name="' . esc_attr( $prefix_name ) . '[' . esc_attr( $form_field_id ) . '][default_field]" value="0"/>';
			if ( ! empty( $activeSocialFields ) ) {
				$class        = apply_filters( 'arm_form_field_class', '' );
				$class       .= ' arm-df__form-control arm-df__form-control_' . esc_attr( $form_field_id ) . ' ';
				$formSettings = ( ! empty( $form ) && ! empty( $form->settings ) ) ? $form->settings : array();
				$formStyles   = ( ! empty( $form ) && isset( $formSettings['style'] ) && ! empty( $formSettings['style'] ) ) ? $formSettings['style'] : array();
				if ( isset( $formStyles['form_layout'] ) && ( $formStyles['form_layout'] == 'writer' || $formStyles['form_layout'] == 'writer_border' ) ) {
					$class .= ' arm_material_input';
				}
				$user_id = 0;
				if(is_user_logged_in())
				{
					$ARMemberLite->arm_session_start();
					if(isset($_SESSION['arm_additional_form_fields']))
					{
						unset($_SESSION['arm_additional_form_fields']);
					}
					$user_id      = get_current_user_id();
				}
				foreach ( $socialProfileFields as $spfKey => $spfLabel ) {
					if ( in_array( $spfKey, $activeSocialFields ) ) {
						$spfMetaKey   = 'arm_social_field_' . $spfKey;
                        //$spfMetaKeyRandomID = $spfMetaKey."".$formrando;
						$spfMetaValue = '';
						if ( isset( $formStyles['form_layout'] ) && ( $formStyles['form_layout'] != 'writer' || $formStyles['form_layout'] != 'writer_border' ) ) {
							$inputPlaceholder  = ' data-placeholder="' . esc_attr($spfLabel) . '"';
							$inputPlaceholder .= ' placeholder="' . esc_attr($spfLabel) . '"';
						} else {
							$inputPlaceholder = '';
						}
						if ( !empty($user_id) ) {
							/**
							 * In case of admin edit member page -- `$user_id` will be replaced with requested user id from url.
							 */
							if(empty($_SESSION['arm_additional_form_fields']))
							{
								$_SESSION['arm_additional_form_fields'] = array();
							}
							$_SESSION['arm_additional_form_fields'][$form_id][] = $spfMetaKey;
							$spfMetaValue = get_user_meta( $user_id, $spfMetaKey, true );
						}

						$arm_allow_notched_outline = 0;
						if ( is_admin() || $formStyles['form_layout'] == 'writer_border' ) {
							$arm_allow_notched_outline = 1;
							$inputPlaceholder          = '';
						}
						$arm_social_wrap_active_class = '';
						if ( ! empty( $arm_allow_notched_outline ) ) {
							$arm_social_wrap_active_class = ( ! empty( $spfMetaValue ) ) ? ' arm-df__form-material-field-wrap' : '';
							$ffield_label_html            = '<div class="arm-notched-outline">';
							$ffield_label_html           .= '<div class="arm-notched-outline__leading"></div>';
							$ffield_label_html           .= '<div class="arm-notched-outline__notch">';
						}

						if ( ! empty( $spfKey ) ) {
							$arm_social_label_active = '';
							if ( $arm_social_wrap_active_class != '' || ! empty( $spfMetaValue ) ) {
								$arm_social_label_active = 'active';
							}

							if ( $formStyles['form_layout'] == 'writer_border' || $formStyles['form_layout'] == 'writer' ) {
								$inputPlaceholder = '';
								$ffield_label     = '<label class="arm-df__label-text ' . esc_attr($arm_social_label_active) . '" for="arm_'. esc_attr( $spfKey ) .'" aria-label="' . esc_attr( stripslashes( $spfKey ) ) . '">' . html_entity_decode( stripslashes( $spfKey ) ) . '</label>';
							} else {
								$inputPlaceholder .= ' placeholder="' . $spfLabel . '"';
							}
							if ( is_admin() ) {
								$ffield_label = '<label class="arm-df__label-text arm_form_field_label_text ' . esc_attr( $arm_social_label_active ) . '" for="arm_'.esc_attr($spfKey).'" aria-label="' . esc_attr( stripslashes( $spfKey ) ) . '">' . html_entity_decode( stripslashes( $spfKey ) ) . '</label>';
							}
						} else {
							$ffield_label = '';
						}

						if ( ! empty( $arm_allow_notched_outline ) ) {
							$ffield_label_html .= $ffield_label;

								$ffield_label_html .= '</div>';
							$ffield_label_html     .= '<div class="arm-notched-outline__trailing"></div>';
							$ffield_label_html     .= '</div>';

							$ffield_label = $ffield_label_html;
						}

						$selectedSPFOpt   .= '<input type="hidden" class="arm_selected_social_profile_fields" name="' . esc_attr($prefix_name) . '[' . esc_attr($form_field_id) . '][options][]" value="' . esc_attr( $spfKey ) . '"/>';
						$socialFieldsHtml .= '<div class="arm-df__form-group' . esc_attr($field_class) . ' arm-df__form-group_text" id="arm-df__form-group_' . esc_attr($form_field_id) . '_' . esc_attr( $spfKey ) . '" data-field_id="' . esc_attr($form_field_id) . '">';
						$socialFieldsHtml .= '<div class="arm_form_label_wrapper arm-df__field-label arm_form_member_field_social_fields">';
						$socialFieldsHtml .= '<label class="arm_form_field_label_text" >' . html_entity_decode( stripslashes( $spfKey ) ) . '</label>';
						$socialFieldsHtml .= '</div>';
						$socialFieldsHtml .= '<div class="arm_label_input_separator"></div>';
						$socialFieldsHtml .= '<div class="arm-df__form-field">';
						$socialFieldsHtml .= '<div class="arm-df__form-field-wrap_social_fields arm-df__form-field-wrap arm-controls ' . esc_attr($arm_social_wrap_active_class) . '" id="arm-df__form-field-wrap_' . esc_attr( $form_field_id ) . '">';
						$socialFieldsHtml .= '<input name="' . esc_attr( $spfMetaKey ) . '" type="text" value="' . esc_attr( $spfMetaValue ) . '" class="' . esc_attr( $class ) . ' arm-df__form-control_' . esc_attr( $spfKey ) . ' arm_social_field_input" ' . esc_attr( $inputPlaceholder ) . ' id="arm_'.esc_attr( $spfKey ).'">';
						$socialFieldsHtml .= $ffield_label;
						$socialFieldsHtml .= '</div>';
						$socialFieldsHtml .= '</div>';
						$socialFieldsHtml .= '<div class="armclear"></div>';
						$socialFieldsHtml .= '</div>';
					}
				}
			}
			$socialFieldsHtml .= '</div>';
			if ( $form_type == 'inactive' ) {
				$socialFieldsHtml .= $selectedSPFOpt;
				$socialFieldsHtml .= '<div class="arm_form_settings_icon">';
				$socialFieldsHtml .= '<a href="javascript:void(0)" class="arm_form_member_settings_icon armhelptip" title="' . esc_html__( 'Edit Field Options', 'armember-membership' ) . '" data-field_id="' . $form_field_id . '" data-field_type="social_fields">';
				$socialFieldsHtml .= '<img src="' . MEMBERSHIPLITE_IMAGES_URL . '/fe_setting.png" onmouseover="this.src=\'' . MEMBERSHIPLITE_IMAGES_URL . '/fe_setting_hover.png\';" onmouseout="this.src=\'' . MEMBERSHIPLITE_IMAGES_URL . '/fe_setting.png\';" style="cursor:pointer;"/>'; //phpcs:ignore 
				$socialFieldsHtml .= '</a>';
				$socialFieldsHtml .= '<a href="javascript:void(0)" class="arm_form_member_delete_icon armhelptip" title="' . esc_html__( 'Delete Field', 'armember-membership' ) . '" data-field_id="' . esc_attr($form_field_id) . '" data-field_type="social_fields" onclick="showConfirmBoxCallback(' . esc_attr($form_field_id) . ');">';
				$socialFieldsHtml .= '<img src="' . MEMBERSHIPLITE_IMAGES_URL . '/fe_delete.png" onmouseover="this.src=\'' . MEMBERSHIPLITE_IMAGES_URL . '/fe_delete_hover.png\';" onmouseout="this.src=\'' . MEMBERSHIPLITE_IMAGES_URL . '/fe_delete.png\';" style="cursor:pointer;"/>'; //phpcs:ignore 
				$socialFieldsHtml .= '</a>';
				$socialFieldsHtml .= '</div>';
				$socialFieldsHtml .= $arm_global_settings->arm_get_confirm_box( $form_field_id, esc_html__( 'Are you sure you want to delete this field?', 'armember-membership' ), 'arm_field_delete_ok_btn', 'social_fields' );
			}
			return $socialFieldsHtml;
		}

		function arm_generate_field_fa_icon( $field_id = 0, $id = '', $type = '', $color = '' ) {
			if ( empty( $id ) || $id == 'undefined' ) {
				return '';
			}
			$icon      = '';
			$iconStyle = '';
			if ( ! empty( $color ) ) {
				$iconStyle = 'color:' . $color;
			}
			if ( strpos( $id, ' ' ) === false ) {
				$id = 'armfa ' . $id;
			}
			if ( $type == 'prefix' ) {
				$icon .= '<span class="arm-df__fc-icon --arm-prefix-icon" id="arm_editor_prefix_' . esc_attr($field_id) . '" ><i class="' . esc_attr($id) . '" style="' . esc_attr($iconStyle) . '"></i></span>';
			} elseif ( $type == 'suffix' ) {
				$icon .= '<span class="arm-df__fc-icon --arm-suffix-icon" id="arm_editor_suffix_' . esc_attr($field_id) . '"><i class="' . esc_attr($id) . '" style="' . esc_attr($iconStyle) . '"></i></span>';
			}
			return $icon;
		}

		function arm_member_form_get_fields_by_type( $field_options, $field_id = 0, $form_id = 0, $form_type = 'inactive', $form = '', $formRandomID = '' ) {
			global $wp, $wpdb, $arm_slugs, $current_user, $ARMemberLite, $arm_global_settings, $arm_subscription_plans,$arm_lite_members_activity;

			$value           = $field_options;
			$meta_key        = $value['meta_key'];
			$ffield_type     = $value['type'];
			$name            = 'no_field';
			$common_messages = $arm_global_settings->arm_get_all_common_message_settings();
			if ( $form_type == 'active' ) {
				$name = ( ! empty( $meta_key ) ) ? $meta_key : $value['id'];
				if ( ! empty( $meta_key ) && isset( $_REQUEST[ $meta_key ] ) && ! empty( $_REQUEST[ $meta_key ] ) ) { //phpcs:ignore
					$value['value'] = esc_html( sanitize_text_field( $_REQUEST[ $meta_key ] ) ); //phpcs:ignore
				}
			}

			$formRandomID = esc_attr( $formRandomID );

			$ng_model    = '';
			$value['id'] = 'arm_' . $value['id'] . '_' . $form_id;
			$class       = apply_filters( 'arm_form_field_class', '' );
			$class      .= ' arm-df__form-control_' . $field_id . ' ';

			$arm_form_control_class = '';
			if ( $ffield_type != 'radio' && $ffield_type != 'checkbox' && $ffield_type != 'submit' && $ffield_type != 'rememberme' && $ffield_type != 'roles' ) {
				$arm_form_control_class = 'arm-df__form-control ';
			}
			$class               .= $arm_form_control_class . 'arm_cl_' . esc_attr( $meta_key ) . '_' . esc_attr( $formRandomID ) . ' ';
			$value['label']       = ! empty( $value['label'] ) ? stripslashes( $value['label'] ) : '';
			$value['placeholder'] = ! empty( $value['placeholder'] ) ? stripslashes( $value['placeholder'] ) : '';
			$ffield_label         = ( ! empty( $value['placeholder'] ) ) ? $value['placeholder'] : '';
			$placeholder          = isset( $value['placeholder'] ) ? ' placeholder="' . esc_attr( $value['placeholder'] ) . '"' : '';
			$formSettings         = ( ! empty( $form ) && ! empty( $form->settings ) ) ? $form->settings : array();
			$formStyles           = ( ! empty( $form ) && isset( $formSettings['style'] ) && ! empty( $formSettings['style'] ) ) ? $formSettings['style'] : array();
			$suffix_eye_icon_cls  = 'arm_visible_password';
			if ( isset( $formStyles['form_layout'] ) && ( $formStyles['form_layout'] == 'writer' || $formStyles['form_layout'] == 'writer_border' ) ) {
				$placeholder         = '';
				$ffield_label        = $value['label'];
				$class               = $arm_form_control_class . 'arm-df__form-control_' . $field_id . ' arm_material_input arm_cl_' . $meta_key . '_' . $formRandomID;
				$suffix_eye_icon_cls = ' arm_visible_password_material ';
			}
			$validate_msgs = '';

			$required_star = ( ! empty( $value['required'] ) ) ? ' required  data-validation-required-message="' . htmlentities( addslashes( $value['blank_message'] ) ) . '" ' : '';
			if ( in_array( $ffield_type, array( 'repeat_email','current_user_pass' ) ) ) {
				$required_star = ' required data-validation-required-message="' . htmlentities( addslashes( $value['blank_message'] ) ) . '" ';
			}

			$required = ( ! empty( $value['required'] ) ) ? ' required data-validation-required-message="' . htmlentities( addslashes( $value['blank_message'] ) ) . '" ' : '';
			if ( ! empty( $value['hide_username'] ) && $value['hide_username'] == 1 ) {
				$required = '';
			}
			if ( ! empty( $value['hide_firstname'] ) && $value['hide_firstname'] == 1 ) {
				$required = '';
			}
			if ( ! empty( $value['hide_lastname'] ) && $value['hide_lastname'] == 1 ) {
				$required = '';
			}

			$disabled           = ( ! empty( $value['disabled'] ) ) ? ' disabled="disabled"" ' : '';
			$blank_message      = ( ! empty( $value['blank_message'] ) && ! empty( $value['required'] ) ) ? ' data-validation-required-message="' . htmlentities( stripcslashes( $value['blank_message'] ) ) . '" ' : '';
			$invalid_username   = ( ! empty( $value['invalid_username'] ) ) ? $value['invalid_username'] : '';
			$invalid_firstname  = ( ! empty( $value['invalid_firstname'] ) ) ? $value['invalid_firstname'] : '';
			$invalid_lastname   = ( ! empty( $value['invalid_lastname'] ) ) ? $value['invalid_lastname'] : '';
			$validation_type    = ( ! empty( $value['validation_type'] ) ) ? $value['validation_type'] : 'custom_validation_none';
			$regular_expression = ( ! empty( $value['regular_expression'] ) ) ? $value['regular_expression'] : '';
			$invalid_message    = ( ! empty( $value['invalid_message'] ) ) ? ' data-msg-invalid="' . stripcslashes( $value['invalid_message'] ) . '" ' : '';
			$validation_data    = $required . $blank_message . $invalid_message;
			$validation_data   .= ( ! empty( $value['options']['minlength'] ) ) ? ' minlength="' . intval( $value['options']['minlength'] ) . '"' : '';
			$validation_data   .= ( ! empty( $value['options']['maxlength'] ) ) ? ' maxlength="' . intval( $value['options']['maxlength'] ) . '"' : '';
			if ( $form_type != 'active' ) {
				$validation_data = $validate_msgs = $required = '';
			}
			$onchange = ( ! empty( $value['onchange'] ) ) ? 'onchange="' . esc_attr($value['onchange']) . '"' : '';
			/* Set Value Variable */
			$field_desc             = ( isset( $value['description'] ) ) ? $value['description'] : '';
			$field_val              = ( isset( $value['value'] ) ) ? $value['value'] : '';
			$field_val_active_class = ! empty( $field_val ) ? ' active' : '';
			$prefix_icon            = $suffix_icon = '';
			if ( isset( $formStyles['form_layout'] ) && $formStyles['form_layout'] != 'writer' ) {
				$prefix_icon = ( ! empty( $value['prefix'] ) && ! is_array( $value['prefix'] ) && $value['prefix'] != 'Array' ) ? $this->arm_generate_field_fa_icon( $field_id, $value['prefix'], 'prefix' ) : '';
				$suffix_icon = ( ! empty( $value['suffix'] ) && ! is_array( $value['suffix'] ) && $value['suffix'] != 'Array' ) ? $this->arm_generate_field_fa_icon( $field_id, $value['suffix'], 'suffix' ) : '';
			}

			// $visible_pass_cls = ($suffix_icon != "") ? " arm_right_space_visible_pass " : "";
			$visible_pass_cls = ( $suffix_icon != '' ) ? ' ' : '';

			$suffix_eye_icon = '';
			if ( $ffield_type == 'password' || $ffield_type == 'repeat_pass' || $ffield_type=='current_user_pass' ) {
				$suffix_eye_icon = '<button type="button" class="arm-df__fc-icon --arm-suffix-icon ' . $suffix_eye_icon_cls . $visible_pass_cls . '"><i class="armfa armfa-eye"></i></button>';
			}

			$class .= ( ! empty( $prefix_icon ) || ! empty( $suffix_icon ) || ! empty( $suffix_eye_icon ) ) ? ' --arm-has-prefix-sufix-icon' : '';
			$class .= ( ! empty( $suffix_icon ) || ! empty( $suffix_eye_icon ) ) ? ' --arm-has-suffix-icon' : '';
			$class .= ( ! empty( $prefix_icon ) ) ? ' --arm-has-prefix-icon' : '';

			$return_html = $output = $psm = '';
			$field_attr  = $ng_model . ' ' . $placeholder . ' ' . $required . ' ' . $disabled;

			$all_fields = !empty($form->fields) ? $form->fields : array();
			foreach ( $all_fields as $fields ) {
				$form_field_option = $fields['arm_form_field_option'];
				if ( ! empty( $form_field_option['meta_key'] ) && $form_field_option['meta_key'] == $name ) {
					if ( ! empty( $form_field_option['options']['minlength'] ) ) {
						$field_attr               .= ' minlength="' . ( (int) $form_field_option['options']['minlength'] ) . '"';
						$minlength_invalid_message = ( isset( $common_messages['arm_minlength_invalid'] ) && $common_messages['arm_minlength_invalid'] != '' ) ? str_replace( '[MINVALUE]', ( (int) $form_field_option['options']['minlength'] ), $common_messages['arm_minlength_invalid'] ) : esc_html__( 'Please enter at least', 'armember-membership' ) . ' ' . esc_html( (int) $form_field_option['options']['minlength'] ) . ' ' . esc_html__( 'characters.', 'armember-membership' );
						$field_attr               .= ' data-validation-minlength-message="' . htmlentities( $minlength_invalid_message ) . '"';
					}
					if ( ! empty( $form_field_option['options']['maxlength'] ) ) {
						$field_attr               .= ' maxlength="' . ( (int) $form_field_option['options']['maxlength'] ) . '"';
						$maxlength_invalid_message = ( isset( $common_messages['arm_maxlength_invalid'] ) && $common_messages['arm_maxlength_invalid'] != '' ) ? str_replace( '[MAXVALUE]', ( (int) $form_field_option['options']['maxlength'] ), $common_messages['arm_maxlength_invalid'] ) : esc_html__( 'Maximum', 'armember-membership' ) . ' ' . esc_html( (int) $form_field_option['options']['minlength'] ) . ' ' . esc_html__( 'characters allowed.', 'armember-membership' );
						$field_attr               .= ' data-validation-maxlength-message="' . htmlentities( $maxlength_invalid_message ) . '"';
					}
				}
			}
			// $validate_msgs .= '<div class="arm-df__fc--validation__wrap"><div class="arm_error_box_arrow"></div></div>';

			$arm_allow_notched_outline = 0;
			if ( ( is_admin() || $formStyles['form_layout'] == 'writer_border' ) && ( $ffield_type != 'avatar' && $ffield_type != 'file' && $ffield_type != 'profile_cover' && $ffield_type != 'radio' && $ffield_type != 'checkbox' ) ) {
				$arm_allow_notched_outline = 1;
			}

			$arm_field_wrap_active_class = '';
			if ( ! empty( $arm_allow_notched_outline ) ) {
				if ( $ffield_type == 'roles' ) {
					$arm_field_wrap_active_class = ( ! empty( $field_val ) ) ? ' arm-df__form-material-field-wrap' : '';
				} else {
					$arm_field_wrap_active_class = ( ! empty( $field_val ) ) ? ' arm-df__form-material-field-wrap' : '';
				}
				$ffield_label_html      = '<div class="arm-notched-outline">';
				$ffield_label_html     .= '<div class="arm-notched-outline__leading"></div>';
					$ffield_label_html .= '<div class="arm-notched-outline__notch">';
			}

            if(!is_admin())
            {                
                $field_attr .= ' aria-label="'.$value['label'].'"';
            }
			if ( ! empty( $ffield_label ) && ! is_admin() ) {
				$field_val_active_class = '';
				if ( $arm_field_wrap_active_class != '' || ! empty( $field_val ) || ( $ffield_type == 'roles' && ( ! empty( $field_val ) ) ) ) {
					$field_val_active_class = 'active';
				}
				if ( $required_star != '' ) {
					$ffield_label = '<label class="arm-df__label-text ' . esc_attr($field_val_active_class) . '" for="arm-df__form-control_' . esc_attr($field_id) . '_' . esc_attr($formRandomID) . '" aria-label="' . esc_attr( stripslashes( $ffield_label ) ) . '"> * ' . html_entity_decode( stripslashes( $ffield_label ) ) . '</label>';
				} else {
					if ( $ffield_type != 'avatar' && $ffield_type != 'file' && $ffield_type != 'profile_cover' ) {
						$ffield_label = '<label class="arm-df__label-text ' . esc_attr($field_val_active_class) . '" for="arm-df__form-control_' . esc_attr($field_id) . '_' . esc_attr($formRandomID) . '" aria-label="' . esc_attr( stripslashes( $ffield_label ) ) . '"> ' . html_entity_decode( stripslashes( $ffield_label ) ) . '</label>';
					} else {
						$ffield_label = '';
					}
				}
			} else {
					$field_val_active_class = '';
				if ( ( $ffield_type == 'roles' && ( ! empty( $field_val ) ) ) || $arm_field_wrap_active_class != '' ) {
					$field_val_active_class = 'active';
				}
				if ( $required_star != '' ) {
					$ffield_label = '<label class="arm-df__label-text ' . esc_attr($field_val_active_class) . '" for="arm-df__form-control_' . esc_attr($field_id) . '_' . esc_attr($formRandomID) . '" aria-label="' . esc_attr( stripslashes( $ffield_label ) ) . '"> * ' . html_entity_decode( stripslashes( $ffield_label ) ) . '</label>';
				} else {
					if ( $ffield_type != 'avatar' && $ffield_type != 'file' && $ffield_type != 'profile_cover' ) {
						$ffield_label = '<label class="arm-df__label-text ' . esc_attr($field_val_active_class) . '" for="arm-df__form-control_' . esc_attr($field_id) . '_' . esc_attr($formRandomID) . '" aria-label="' . esc_attr( stripslashes( $ffield_label ) ) . '"> ' . html_entity_decode( stripslashes( $ffield_label ) ) . '</label>';
					} else {
						$ffield_label = '';
					}
				}
			}

			if ( ! empty( $arm_allow_notched_outline ) ) {
				$ffield_label_html .= $ffield_label;

					$ffield_label_html .= '</div>';
				$ffield_label_html     .= '<div class="arm-notched-outline__trailing"></div>';
				$ffield_label_html     .= '</div>';

				$ffield_label = $ffield_label_html;
			}

			$field_class = isset( $field_options['class'] ) ? $field_options['class'] : '';
			switch ( $ffield_type ) {
				/* Text Field */
				case 'text':
				case 'repeat_email':
				case 'email':
				case 'url':
					if ( $ffield_type == 'text' && $validation_type != 'custom_validation_none' && $validation_type != 'customvalidationregex' ) {

						$field_attr .= ' data-validation-' . $validation_type . '-callback="arm_' . esc_attr($validation_type) . '_function" data-validation-' . $validation_type . '-message="' . htmlentities( stripcslashes( $value['invalid_message'] ) ) . '"';

					}
					if ( $ffield_type == 'text' && $validation_type == 'customvalidationregex' && ! empty( $regular_expression ) ) {
						$regular_expression = ltrim( $regular_expression, '/' );
						$regular_expression = rtrim( $regular_expression, '/' );
						$field_attr        .= ' data-validation-regex-regex="' . esc_attr($regular_expression) . '" data-validation-regex-message="' . htmlentities( stripcslashes( $value['invalid_message'] ) ) . '"';

					}
					if ( $ffield_type == 'email' || $ffield_type == 'repeat_email' ) {

						$field_attr .= ' data-validation-regex-regex="^.+@.+\..+$" data-validation-regex-message="' . htmlentities( stripcslashes( $value['invalid_message'] ) ) . '"';

						if ( $ffield_type == 'repeat_email' ) {
							// $ffield_type = 'email';
							$refFieldID = ( isset( $value['ref_field_id'] ) && $value['ref_field_id'] != 0 ) ? $value['ref_field_id'] : 0;
							if ( isset( $value['ref_field_id'] ) && $value['ref_field_id'] != 0 ) {
								$psm              = '';
								$class           .= ' armRepeatEmailInput ';
								 $invalid_message = ( ! empty( $value['invalid_message'] ) ) ? stripcslashes( $value['invalid_message'] ) : esc_html__( 'Please enter email address again.', 'armember-membership' );
								$field_attr      .= ' data-validation-matches-match="arm-df__form-control_' . $refFieldID . '_' . $formRandomID . '" data-validation-matches-message="' . htmlentities( $invalid_message ) . '"';

							} else {
								// $ffield_type = 'email';
							}
						}
						$ffield_type = 'text';

					}
					if ( $ffield_type == 'url' ) {
						$field_attr .= 'data-validation-regex-regex="^(((https?|ftp)\:\/\/)?((\[?(\d{1,3}\.){3}\d{1,3}\]?)|(([-a-zA-Z0-9]+\.)+[a-zA-Z]{2,4}))(\:\d+)?(\/[-a-zA-Z0-9._]+)*?)$" data-validation-regex-message="' . htmlentities( stripcslashes( $value['invalid_message'] ) ) . '"';
					}
					if ( $form_type == 'active' && ! empty( $form ) && $form->type == 'registration' ) {
						if ( $name == 'user_login' ) {
							$class .= ' usernamecheck existcheck';
							if ( empty( $regular_expression ) ) {
								$field_attr .= ' data-validation-usernamecheck-callback="arm_usernamecheck_function" data-validation-usernamecheck-message="' . htmlentities( stripcslashes( $invalid_username ) ) . '"';
							}

							$exist_msg = isset( $arm_global_settings->common_message['arm_username_exist'] ) ? $arm_global_settings->common_message['arm_username_exist'] : '';
							$exist_msg = ( ! empty( $exist_msg ) ) ? $exist_msg : esc_html__( 'This username is already registered, please choose another one.', 'armember-membership' );
							if ( is_multisite() ) {
								$class .= ' arm_multisite_validate ';
							}
							$field_attr .= ' data-validation-callback-callback="arm_existcheck_function" data-validation-callback-message="' . htmlentities( stripcslashes( $exist_msg ) ) . '"';

						}
						if ( $name == 'user_email' ) {
							$class      .= ' existcheck';
							$exist_msg   = isset( $arm_global_settings->common_message['arm_email_exist'] ) ? $arm_global_settings->common_message['arm_email_exist'] : '';
							$exist_msg   = ( ! empty( $exist_msg ) ) ? $exist_msg : esc_html__( 'This email is already registered, please choose another one.', 'armember-membership' );
							$field_attr .= ' data-validation-callback-callback="arm_existcheck_function" data-validation-callback-message="' . htmlentities( stripcslashes( $exist_msg ) ) . '"';

						}
					}

					$output .= '<input name="' . esc_attr( $name ) . '" type="' . esc_attr( $ffield_type ) . '" id="arm-df__form-control_' . esc_attr( $field_id ) . '_' . esc_attr( $formRandomID ) . '" value="' . esc_attr( $field_val ) . '" class="' . esc_attr( $class ) . '" ' . $field_attr . ' ' . $onchange . '>';
					$output .= $prefix_icon;
					$output .= $ffield_label;
					$output .= $suffix_icon;

					break;
				/* Password */
				case 'current_user_pass':
				case 'repeat_pass':
				case 'password':
					$pass_attr = '';
					$options   = $value['options'];
					if ( ! empty( $options ) && $form_type == 'active' ) {
						if ( isset( $options['strong_password'] ) && $options['strong_password'] == '1' ) {
							$pass_attr    .= ' armstrongpassword="1"';
							$validate_char = array(
								'lowercase' => esc_html__( 'lowercase', 'armember-membership' ),
								'uppercase' => esc_html__( 'uppercase', 'armember-membership' ),
								'numeric'   => esc_html__( 'numeric', 'armember-membership' ),
								'special'   => esc_html__( 'special', 'armember-membership' ),
							);
							foreach ( $validate_char as $v => $v_lbl ) {
								if ( isset( $options[ $v ] ) && $options[ $v ] == '1' ) {
									$pass_attr    .= ' data-validation-arm' . $v . '-callback="arm' . $v . '_function"';
									$pass_attr_msg = esc_html__( 'Please use atleast one', 'armember-membership' ) . ' ' . $v_lbl . ' ' . esc_html__( 'character.', 'armember-membership' );
									$pass_attr    .= ' data-validation-arm' . $v . '-message="' . htmlentities( $pass_attr_msg ) . '"';

								}
							}
						}
						if ( ! is_admin() ) {
							if ( isset( $options['strength_meter'] ) && $options['strength_meter'] == '1' ) {

								$veryweaktext = ( ! empty( $options['veryweaktext'] ) ) ? esc_html( stripslashes( $options['veryweaktext'] ) ) : esc_html__( 'Strength: Very Weak', 'armember-membership' );
								$weaktext     = ( ! empty( $options['weaktext'] ) ) ? esc_html( stripslashes( $options['weaktext'] ) ) : esc_html__( 'Strength: Weak', 'armember-membership' );
								$goodtext     = ( ! empty( $options['goodtext'] ) ) ? esc_html( stripslashes( $options['goodtext'] ) ) : esc_html__( 'Strength: Good', 'armember-membership' );
								$strongtext   = ( ! empty( $options['strongtext'] ) ) ? esc_html( stripslashes( $options['strongtext'] ) ) : esc_html__( 'Strength: Strong', 'armember-membership' );

								$class .= ' arm_strength_meter_input';
								$psm   .= '<div class="arm_pass_strength_meter">';
								$psm   .= '<ul class="arm_strength_meter_block_container" check-strength="arm-df__form-control_' . esc_attr( $field_id ) . '_' . esc_attr( $formRandomID ) . '" field-name="' . esc_attr( $name ) . '" data-field-veryweak = "' . esc_attr( $veryweaktext ) . '" data-field-weak = "' . esc_attr( $weaktext ) . '" data-field-good = "' . esc_attr( $goodtext ) . '" data-field-strong = "' . esc_attr( $strongtext ) . '" ></ul>';
								$psm   .= '<span class="arm_strength_meter_label">' . esc_html( $veryweaktext ) . '</span>';
								$psm   .= '<div class="armclear"></div>';
								$psm   .= '</div>';
							}
						}
					}
					if ( $ffield_type == 'repeat_pass' && $form_type == 'active' ) {
						$refFieldID = ( isset( $value['ref_field_id'] ) && $value['ref_field_id'] != 0 ) ? $value['ref_field_id'] : 0;
						if ( isset( $value['ref_field_id'] ) && $value['ref_field_id'] != 0 ) {
							$psm             = '';
							$class          .= ' armRepeatPasswordInput ';
							$invalid_message = ( ! empty( $value['invalid_message'] ) ) ? stripcslashes( $value['invalid_message'] ) : esc_html__( 'Passwords don\'t match.', 'armember-membership' );
							$pass_attr       = ' data-validation-matches-match="arm-df__form-control_' . esc_attr($refFieldID) . '_' . esc_attr($formRandomID) . '" data-validation-matches-message="' . htmlentities( $invalid_message ) . '"';

						}
					} else {

					}

					$output .= '<input name="' . esc_attr($name) . '" type="password" id="arm-df__form-control_' . esc_attr( $field_id ) . '_' . esc_attr( $formRandomID ) . '" autocomplete="off" value="' . esc_attr( $field_val ) . '" class=" ' . esc_attr( $class ) . '" ' . $field_attr . ' ' . $pass_attr . '>';
					$output .= $prefix_icon;
					$output .= $ffield_label;

					$output .= $suffix_eye_icon;
					// $output .= $suffix_icon;

					break;
				/* Date Field */
				case 'date':
					$formDateFormat  = 'd/m/Y';
					$dateFormatTypes = array(
						'm/d/Y'  => 'MM/DD/YYYY',
						'd/m/Y'  => 'DD/MM/YYYY',
						'Y/m/d'  => 'YYYY/MM/DD',
						'M d, Y' => 'MMM DD, YYYY',
						'd M, Y' => 'DD MMM, YYYY',
						'Y, M d' => 'YYYY, MMM DD',
						'F d, Y' => 'MMMM DD, YYYY',
						'd F, Y' => 'DD MMMM, YYYY',
						'Y, F d' => 'YYYY, MMMM DD',
						'Y-m-d'  => 'YYYY-MM-DD',
					);
					$showTimePicker  = '0';
					if ( ! empty( $form ) && ! empty( $formSettings['date_format'] ) ) {
						$formDateFormat = $formSettings['date_format'];
					}
					$dateFormat = $dateFormatTypes[ $formDateFormat ];
					if ( ! empty( $form ) && ! empty( $formSettings['show_time'] ) ) {
						$showTimePicker = $formSettings['show_time'];
					}

					$calLocalization = '';
					if ( ! empty( $form ) && isset( $value['cal_localization'] ) ) {
						$calLocalization = $value['cal_localization'];
					}
					// $output .= '<div class="arm-df__form-field-date-container arm_date_field_' . $form_id . '">';
					if ( $form_type == 'active' ) {
						$class .= ' arm_datepicker arm_datepicker_front ';
					}
					$output .= '<input name="' . esc_attr($name) . '" type="text" id="arm-df__form-control_' . esc_attr( $field_id ) . '_' . esc_attr( $formRandomID ) . '" autocomplete="off" value="' . esc_attr( $field_val ) . '" class="' . esc_attr( $class ) . '" ' . $field_attr . ' data-dateformat="' . $dateFormat . '" data-date_field="arm_date_field_' . esc_attr( $form_id ) . '" data-show_timepicker="' . esc_attr( $showTimePicker ) . '" data-cal_localization="' . esc_attr( $calLocalization ) . '" ' . $onchange . '>';
					$output .= $prefix_icon;
					$output .= $ffield_label;
					$output .= $suffix_icon;

					// $output .= '</div>';
					global $arm_lite_datepicker_loaded;
					$arm_lite_datepicker_loaded = 1;

					break;
				/* File Upload Field */
				case 'file':
				case 'avatar':
				case 'profile_cover':
					global $arm_lite_file_upload_field;
					$arm_lite_file_upload_field = 1;
					$accept                     = ( ! empty( $value['allow_ext'] ) ) ? 'accept="' . $value['allow_ext'] . '"' : '';
					if ( $ffield_type == 'avatar' ) {
						$accept = 'accept=".jpg,.jpeg,.png,.bmp,.ico"';
					}
					$file_size_limit = ( ! empty( $value['file_size_limit'] ) ) ? (int) $value['file_size_limit'] : 2;
					/** FETCH A FILE UPLOAD SIZE FROM FORM SETTING INSTEAD OF PRESET FIELDS */
					$all_fields = $form->fields;
					foreach ( $all_fields as $fields ) {
						if ( isset($fields['arm_form_field_option']['meta_key']) && $fields['arm_form_field_option']['meta_key'] == $name ) {
							$file_size_limit = ( ! empty( $fields['arm_form_field_option']['file_size_limit'] ) ) ? (int) $fields['arm_form_field_option']['file_size_limit'] : 2;
						}
					}
					$display_file = ! empty( $field_val ) && file_exists( MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename( $field_val ) ) ? true : false;
					$file_name    = $fileUrl = '';
					if ( $display_file ) {
						$file_name = basename( $field_val );
						if ( $field_val != '' ) {
							$exp_val        = explode( '/', $field_val );
							$filename       = $exp_val[ count( $exp_val ) - 1 ];
							$file_extension = explode( '.', $filename );
							$file_ext       = $file_extension[ count( $file_extension ) - 1 ];
							if ( in_array( $file_ext, array( 'jpg', 'jpeg', 'jpe', 'png', 'bmp', 'tif', 'tiff', 'JPG', 'JPEG', 'JPE', 'PNG', 'BMP', 'TIF', 'TIFF' ) ) ) {
								$fileUrl = $field_val;
							} else {
								$fileUrl = MEMBERSHIPLITE_IMAGES_URL . '/file_icon.png';
							}
						}
					} else {
						$field_val = '';
					}

					if(isset($_SESSION['arm_file_upload_arr'][$name])){
						$arm_lite_members_activity->session_for_file_handle($name,$ARMemberLite->arm_get_basename($field_val));
					}
					$uploaderRandomID = esc_attr( $field_id . $form_id . arm_generate_random_code() );
					$file_placeholder = ( isset( $value['placeholder'] ) && ! empty( $value['placeholder'] ) ) ? $value['placeholder'] : esc_html__( 'Drop file here or click to select.', 'armember-membership' );
					// $output = $ffield_label;

					$output      .= '<div class="armFileUploadWrapper file-field input-field" data-iframe="' . esc_attr( $value['id'] ) . esc_attr($uploaderRandomID) . '">';
					$http_user_agent = !empty( $_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';//phpcs:ignore
					$browser_info = $ARMemberLite->getBrowser( sanitize_text_field($http_user_agent) );
					$inputType    = 'type="file"';

					$browser_check = 1;
					$isIE          = false;
					if ( isset( $browser_info ) and $browser_info != '' ) {
						if ( $browser_info['name'] == 'Internet Explorer' || $browser_info['name'] == 'Apple Safari' ) {
							if ( $browser_info['name'] == 'Apple Safari' ) {
								$class        .= ' armSafariFileUpload';
								$browser_check = 0;
							} elseif ( $browser_info['name'] == 'Internet Explorer' && $browser_info['version'] <= '9' ) {
								$isIE          = true;
								$browser_check = 0;
								$inputType     = 'type="text" data-iframe="' . esc_attr( $value['id'] ) . $uploaderRandomID . '"';
								$class        .= ' armIEFileUpload';
								$output       .= '<div id="' . esc_attr( $value['id'] ) . esc_attr($uploaderRandomID) . '_iframe_div" class="arm_iframe_wrapper" style="display:none;"><iframe id="' . esc_attr( $value['id'] ) . esc_attr($uploaderRandomID) . '_iframe" src="' . MEMBERSHIPLITE_VIEWS_URL . '/iframeupload.php"></iframe></div>';
							}
						}
					}
					if ( is_admin() && isset( $_GET['page'] ) && in_array( $_GET['page'], array( $arm_slugs->manage_members ) ) ) { //phpcs:ignores

						$arm_avatar_type     = '';
						$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
						$general_settings    = $all_global_settings['general_settings'];
						$enable_crop         = isset( $general_settings['enable_crop'] ) ? $general_settings['enable_crop'] : 0;
						if ( $enable_crop ) {
							if ( $value['meta_key'] == 'profile_cover' ) {
								$arm_avatar_type = ' data-avatar-type="cover"  data-update-meta="no"  ';
								$output .= '<div id="arm_crop_cover_div_wrapper" class="arm_crop_cover_div_wrapper" style="display:none;" data_id="' . esc_attr($formRandomID) . '">';
								$output .= '<div id="arm_crop_cover_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
								$output .= '<div id="arm_crop_cover_div" class="arm_crop_cover_div" data_id="' . esc_attr( $formRandomID ) . '"><img id="arm_crop_cover_image" class="arm_crop_cover_image" src="" style="max-width:100%;max-height:100%;" data_id="' . esc_attr($formRandomID) . '"  data-rotate="0" /></div>'; //phpcs:ignore 
								$output .= '<div class="arm_skip_cvr_crop_button_wrapper_admn">';
								$output .= '<button class="arm_crop_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Crop', 'armember-membership' ) . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
								$output .= '<button class="arm_clear_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Clear', 'armember-membership' ) . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
								$output .= '<button class="arm_zoom_cover_button arm_zoom_plus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="0.1" title="' . esc_html__( 'Zoom In', 'armember-membership' ) . '"><span class="armfa armfa-search-plus"></span></button>';
								$output .= '<button class="arm_zoom_cover_button arm_zoom_minus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="-0.1" title="' . esc_html__( 'Zoom Out', 'armember-membership' ) . '"><span class="armfa armfa-search-minus"></span></button>';
								$output .= '<button class="arm_rotate_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" data-method="rotate" data-option="90" title="' . esc_html__( 'Rotate', 'armember-membership' ) . '"><span class="armfa armfa-rotate-right"></span></button>';
								$output .= '<button class="arm_reset_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Reset', 'armember-membership' ) . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
								$output .= '<button id="arm_skip_cvr_crop_nav_admn" data_id="' . esc_attr($formRandomID) . '" class="arm_cvr_done_front">' . esc_html__( 'Done', 'armember-membership' ) . '</button>';
								$output .= '</div>';

								$output .= '<p class="arm_discription">' . esc_html__( '(Use Cropper to set image and use mouse scroller for zoom image.)', 'armember-membership' ) . '</p>';
								$output .= '</div>';
							} elseif ( $value['meta_key'] == 'avatar' ) {
								$arm_avatar_type = ' data-avatar-type="profile"  data-update-meta="no"  ';
								$output .= '<div id="arm_crop_div_wrapper" class="arm_crop_div_wrapper"  style="display:none;" data_id="' . esc_attr($formRandomID) . '">';
								$output .= '<div id="arm_crop_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
								$output .= '<div id="arm_crop_div" class="arm_crop_div" data_id="' . esc_attr($formRandomID) . '"><img id="arm_crop_image" class="arm_crop_image" src="" style="max-width:100%;" data_id="' . esc_attr($formRandomID) . '"  data-rotate="0" /></div>'; //phpcs:ignore 
								$output .= '<div class="arm_skip_avtr_crop_button_wrapper_admn">';
								$output .= '<button class="arm_crop_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Crop', 'armember-membership' ) . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
								$output .= '<button class="arm_clear_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Clear', 'armember-membership' ) . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
								$output .= '<button class="arm_zoom_button arm_zoom_plus arm_img_setting armhelptip tipso_style" data-method="zoom" data-option="0.1" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Zoom In', 'armember-membership' ) . '"><span class="armfa armfa-search-plus"></span></button>';
								$output .= '<button class="arm_zoom_button arm_zoom_minus arm_img_setting armhelptip tipso_style" data-method="zoom" data-option="-0.1" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Zoom Out', 'armember-membership' ) . '"><span class="armfa armfa-search-minus"></span></button>';
								$output .= '<button class="arm_rotate_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" data-method="rotate" data-option="90" title="' . esc_html__( 'Rotate', 'armember-membership' ) . '"><span class="armfa armfa-rotate-right"></span></button>';
								$output .= '<button class="arm_reset_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Reset', 'armember-membership' ) . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
								$output .= '<button id="arm_skip_avtr_crop_nav_admn" class="arm_avtr_done_front" data_id="' . esc_attr($formRandomID) . '">' . esc_html__( 'Done', 'armember-membership' ) . '</button>';
								$output .= '</div>';
								$output           .= '<p class="arm_discription">' . sprintf( addslashes( esc_html__('(Use Cropper to set image and %suse mouse scroller for zoom image.)', 'armember-membership') ),'<br/>' ) . '</p>'; //phpcs:ignore
								$output .= '</div>';
							}
						}
						/**
						 * For Admin Side Only
						 */
						$output .= '<div class="armFileUploadContainer" style="' . ( ( $display_file ) ? 'display:none;' : '' ) . '">';
						$output .= '<div class="armFileUpload-icon"></div>' . esc_html__( 'Upload', 'armember-membership' );
						$output .= '<input armfileuploader id="' . esc_attr( $value['id'] ) . esc_attr($uploaderRandomID) . '" ' . $accept . ' class="arm-df__file-upload-control ' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" ' . $inputType . ' ' . $onchange . ' value="' . esc_attr($field_val) . '" data-file_size="' . esc_attr( $file_size_limit ) . '"  ' . $arm_avatar_type . '/>';
						$output .= '</div>';
						if ( $display_file ) {
							if ( preg_match( '@^http@', $field_val ) ) {
								$temp_data = explode( '://', $field_val );
								$field_val = '//' . $temp_data[1];
							}

							if ( file_exists( strstr( $fileUrl, '//' ) ) ) {
								$fileUrl = strstr( $fileUrl, '//' );
							} elseif ( file_exists( $fileUrl ) ) {
								$fileUrl = $fileUrl;
							} else {
								$fileUrl = $fileUrl;
							}

							$output .= '<div class="arm_old_uploaded_file arm_admin_file"><a href="' . esc_url( $field_val ) . '" target="__blank">' . esc_html__( 'View File', 'armember-membership' ) . '</a></div>';
						}

						$output .= '<div class="armFileRemoveContainer" style="' . ( ( $display_file ) ? 'display: inline-block;' : '' ) . '"><div class="armFileRemove-icon"></div>' . esc_html__( 'Remove', 'armember-membership' ) . '</div>';
						$output .= '<div class="armFileUploadProgressBar" style="display: none;"><div class="armbar" style="width:0%;"></div></div>';
						$output .= '<div class="armFileUploadProgressInfo"></div>';
						$output .= '<div class="armFileMessages" id="armFileUploadMsg_' . esc_attr( $value['id'] ) . $uploaderRandomID . '"></div>';
						$output .= '<input class="arm_file_url" type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr($field_val) . '" ' . $validation_data . ' ' . $arm_avatar_type . '>';
					} else {
						$output .= '<div class="armNormalFileUpload">';
						if ( $browser_check != 1 ) {
							$arm_avatar_type = '';
							if ( $value['meta_key'] == 'profile_cover' ) {
								$arm_avatar_type = ' data-avatar-type="cover"  data-update-meta="no" ';
							} elseif ( $value['meta_key'] == 'avatar' ) {
								$arm_avatar_type = ' data-avatar-type="profile"  data-update-meta="no" ';
							}
							$output .= '<div class="armFileUploadContainer" for="' . esc_attr( $value['id'] ) . esc_attr($uploaderRandomID) . '" style="' . ( ( $display_file ) ? 'display:none;' : '' ) . '">';
							$output .= '<div class="armFileUpload-icon"></div>' . esc_html__( 'Upload', 'armember-membership' );
							$output .= '<input armfileuploader id="' . esc_attr( $value['id'] ) . esc_attr($uploaderRandomID) . '" ' . $accept . ' class="arm-df__file-upload-control ' . esc_attr( $class ) . '" ' . $inputType . ' name="' . esc_attr( $name ) . '" ' . $validation_data . ' ' . $onchange . ' value="' . esc_attr($field_val) . '" data-file_size="' . intval($file_size_limit) . '" aria-label="' . esc_attr($value['label']) . '" ' . $arm_avatar_type . '/>';
							$output .= '</div>';
							$output .= '<div class="armFileRemoveContainer" style="' . ( ( $display_file ) ? 'display: inline-block; ' : '' ) . '"><div class="armFileRemove-icon"></div></div>';
							if ( $display_file ) {
								$output .= '<div class="arm_old_file"><img alt="" src="' . esc_attr($fileUrl) . '" width="100px"/></div>'; //phpcs:ignore 
							}
							$output .= '<div class="armFileUploadProgressBar" style="display: none;"><div class="armbar" style="width:0%;"></div></div>';
							$output .= '<div class="armFileUploadProgressInfo"></div>';
							$output .= '<div class="armclear"></div>';
						} else {
							$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
							$general_settings    = $all_global_settings['general_settings'];
							$enable_crop         = isset( $general_settings['enable_crop'] ) ? $general_settings['enable_crop'] : 0;

							global $arm_is_enable_crop;
							if ( $enable_crop && empty( $arm_is_enable_crop ) && ! is_admin() ) {
								$arm_is_enable_crop = 1;
								$output            .= '<div id="arm_crop_div_wrapper" class="arm_crop_div_wrapper"  style="display:none;" data_id="' . esc_attr($formRandomID) . '">';
								$output            .= '<div id="arm_crop_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
								$output            .= '<div id="arm_crop_div" class="arm_crop_div" data_id="' . $formRandomID . '"><img id="arm_crop_image" class="arm_crop_image" src="" style="max-width:100%;" data_id="' . esc_attr($formRandomID) . '"/></div>'; //phpcs:ignore 
								$output            .= '<div class="arm_skip_avtr_crop_button_wrapper_admn arm_inht_front_usr_avtr">';
								$output            .= '<button class="arm_crop_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Crop', 'armember-membership' ) . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
								$output            .= '<button class="arm_clear_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Clear', 'armember-membership' ) . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
								$output            .= '<button class="arm_zoom_button arm_zoom_plus arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" data-method="zoom" data-option="0.1" title="' . esc_html__( 'Zoom In', 'armember-membership' ) . '"><span class="armfa armfa-search-plus"></span></button>';
								$output            .= '<button class="arm_zoom_button arm_zoom_minus arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" data-method="zoom" data-option="-0.1" title="' . esc_html__( 'Zoom Out', 'armember-membership' ) . '"><span class="armfa armfa-search-minus"></span></button>';
								$output            .= '<button class="arm_rotate_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" data-method="rotate" data-option="90" title="' . esc_html__( 'Rotate', 'armember-membership' ) . '"><span class="armfa armfa-rotate-right"></span></button>';
								$output            .= '<button class="arm_reset_button arm_img_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Reset', 'armember-membership' ) . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
								$output            .= '<button id="arm_skip_avtr_crop_nav_front" class="arm_avtr_done_front" data_id="' . esc_attr($formRandomID) . '">' . esc_html__( 'Done', 'armember-membership' ) . '</button>';
								$output            .= '</div>';
								$output           .= '<p class="arm_discription">(' . sprintf( addslashes( esc_html__( 'Use Cropper to set image and %1$s use mouse scroller for zoom image.', 'armember-membership' ) ), '<br/>') . ')</p>'; //phpcs:ignore
								$output            .= '</div>';

								$output .= '<div id="arm_crop_cover_div_wrapper" class="arm_crop_cover_div_wrapper" style="display:none;" data_id="' . esc_attr($formRandomID) . '">';
								$output .= '<div id="arm_crop_cover_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
								$output .= '<div id="arm_crop_cover_div" class="arm_crop_cover_div" data_id="' . $formRandomID . '"><img id="arm_crop_cover_image" class="arm_crop_cover_image" src="" style="max-width:100%;max-height:100%;" data_id="' . esc_attr($formRandomID) . '" /></div>'; //phpcs:ignore 
								$output .= '<div class="arm_skip_cvr_crop_button_wrapper_admn arm_inht_front_usr_cvr arm_inht_front_usr_profile_cvr">';
								$output .= '<button class="arm_crop_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Crop', 'armember-membership' ) . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
								$output .= '<button class="arm_clear_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Clear', 'armember-membership' ) . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
								$output .= '<button class="arm_zoom_cover_button arm_zoom_plus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="0.1" data_id="' . $formRandomID . '" title="' . esc_html__( 'Zoom In', 'armember-membership' ) . '"><span class="armfa armfa-search-plus"></span></button>';
								$output .= '<button class="arm_zoom_cover_button arm_zoom_minus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="-0.1" data_id="' . $formRandomID . '" title="' . esc_html__( 'Zoom Out', 'armember-membership' ) . '"><span class="armfa armfa-search-minus"></span></button>';
								$output .= '<button class="arm_rotate_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" data-method="rotate" data-option="90" title="' . esc_html__( 'Rotate', 'armember-membership' ) . '"><span class="armfa armfa-rotate-right"></span></button>';
								$output .= '<button class="arm_reset_cover_button arm_img_cover_setting armhelptip tipso_style" data_id="' . esc_attr($formRandomID) . '" title="' . esc_html__( 'Reset', 'armember-membership' ) . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
								$output .= '<button data_id="' . esc_attr($formRandomID) . '" id="arm_skip_cvr_crop_nav_front" class="arm_cvr_done_front">' . esc_html__( 'Done', 'armember-membership' ) . '</button>';
								$output .= '</div>';
								$output .= '<p class="arm_discription">' . esc_html__( '(Use Cropper to set image and use mouse scroller for zoom image.)', 'armember-membership' ) . '</p>';
								$output .= '</div>';
							}
							$arm_avatar_type = '';
							if ( $value['meta_key'] == 'profile_cover' ) {
								$arm_avatar_type = ' data-avatar-type="cover" data-update-meta="no" ';
								global $arm_lite_avatar_loaded, $arm_lite_bpopup_loaded;
								$arm_lite_avatar_loaded = 1;
								$arm_lite_bpopup_loaded = 1;
							} elseif ( $value['meta_key'] == 'avatar' ) {
								global $arm_lite_avatar_loaded, $arm_lite_bpopup_loaded;
								$arm_lite_avatar_loaded = 1;
								$arm_lite_bpopup_loaded = 1;
								$arm_avatar_type        = ' data-avatar-type="profile"  data-update-meta="no" ';
							}
							$output .= '<div class="arm-ffw__file-upload-box">';
							$output .= '<div class="arm_old_file arm_field_file_display">';
							if ( $display_file ) {
								$output .= '<div class="arm_uploaded_file_info"><img alt="" src="' . esc_attr($fileUrl) . '"/></div>'; //phpcs:ignore 
								$output .= '<div class="armFileRemoveContainer">x</div>';
							}
							$output .= '</div>';
							$output .= '<div class="armbar" style="width:0%;"></div>';
							$output .= '<label class="armFileDragAreaText" for="' . esc_attr( $value['id'] ) . esc_attr($uploaderRandomID) . '" style="' . ( ( $display_file ) ? 'display:none;' : '' ) . '">';
							$output .= '<div class="armFileUploaderWrapper armFileUploaderPlaceholder" id="armFileUploaderWrapper' . esc_attr($uploaderRandomID) . '" data-id="' . esc_attr( $value['id'] ) . esc_attr($uploaderRandomID) . '">' . esc_html($file_placeholder) . '</div>';
							$output .= '</label>';
							$output .= '<input armfileuploader id="' . esc_attr( $value['id'] ) . esc_attr($uploaderRandomID) . '" ' . $accept . ' class="arm-df__file-upload-control ' . esc_attr( $class ) . '" ' . $inputType . ' name="' . esc_attr( $name ) . '" ' . $validation_data . ' ' . $onchange . ' value="' . esc_attr($field_val) . '" data-file_size="' . intval($file_size_limit) . '" aria-label="' . esc_attr($value['label']) . '"  ' . $arm_avatar_type . ' data-form-id="' . esc_attr($formRandomID) . '"/>';
							$output .= '</div>';
							$output .= '<div class="armclear"></div>';
						}

						$output .= '</div>';
						$output .= '<input class="arm_file_url" type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr($field_val) . '" tabindex="-1">';
					}
					if ( $form_type == 'active' ) {
						$output .= '<div class="armFileMessages" id="armFileUploadMsg_' . esc_attr( $value['id'] ) . esc_attr($uploaderRandomID) . '"></div>';
					}
					$output .= '</div>';
					break;
				/* Textarea */
				case 'textarea':
					$rows = '5';
					$cols = '40';
					if ( isset( $value['settings']['rows'] ) ) {
						$custom_rows = $value['settings']['rows'];
						if ( is_numeric( $custom_rows ) ) {
							$rows = $custom_rows;
						}
					}
					$output .= '<textarea id="arm-df__form-control_' . esc_attr( $field_id ) . '_' . esc_attr( $formRandomID ) . '" class="arm_textarea ' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" rows="' . esc_attr( $rows ) . '" cols="' . esc_attr( $cols ) . '" ' . $field_attr . ' ' . $onchange . '>' . sanitize_textarea_field( stripslashes( $field_val ) ) . '</textarea>';
					$output .= $ffield_label;

					break;
				/* Select Box */
				case 'select':
					if ( empty( $field_val ) && ! empty( $value['default_val'] ) ) {
						$field_val = $value['default_val'];
					}
					if ( is_admin() && isset( $_GET['page'] ) && in_array( $_GET['page'], array( $arm_slugs->manage_members ) ) ) { //phpcs:ignore
						/**
						 * For Admin Side Only
						 */
						$field_options = '';
						if ( ! empty( $value['options'] ) ) {
							foreach ( $value['options'] as $data ) {
								$data_default = $data     = stripslashes( $data );
								$new_data = explode( ':', $data );
								$option   = $key = isset( $new_data[0] ) ? $new_data[0] : $data;
								$value_data='';
								if(count($new_data)>1)
								{
									$value_data = end($new_data);
									$labeldata = str_replace(':'.$value_data, '', $data_default);
									$option = $labeldata;
								}
								else {
									$option = $data_default;
								}
								if ( isset( $value_data ) && $value_data != '' ) {
									$key = $value_data;
								}
								if ( $value['meta_key'] == 'country' && isset( $value_data ) && ! empty( $field_val ) && ! is_numeric( $field_val ) ) {
									if ( $field_val == $new_data[0] ) {
										$field_val = $value_data;
									}
								}
								$field_options .= '<li data-label="' . esc_html( $option ) . '" data-value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</li>';
							}
						} else {
							$field_options .= '<li data-label="' . esc_html__( 'Choose your option', 'armember-membership' ) . '" data-value="">' . esc_html__( 'Choose your option', 'armember-membership' ) . '</li>';
						}
						$output .= '<input class="arm-selectpicker-input-control" type="text" id="' . esc_attr( $value['id'] ) . '" name="' . esc_attr( $value['meta_key'] ) . '" value="' . esc_attr( $field_val ) . '" ' . $validation_data . ' ' . $onchange . '/>';
						$output .= '<dl class="arm_selectbox column_level_dd arm_member_form_dropdown">';
						$output .= '<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
						$output .= '<dd><ul data-id="' . esc_attr( $value['id'] ) . '" style="display:none;">';
						$output .= $field_options;
						$output .= '';
						$output .= '</ul></dd>';
						$output .= '</dl>';
					} else {
						$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
						$general_settings    = $all_global_settings['general_settings'];
						$ng_change_func      = '';

						if ( isset( $general_settings['enable_tax'] ) && isset( $general_settings['country_tax_field'] ) && $general_settings['enable_tax'] == 1 && $name == $general_settings['country_tax_field'] ) {
							$ng_change_func = "onchange=\"armSetTax('arm_setup_form" . esc_attr($formRandomID) . "');\"";
						}
						/*
						if($required_star != '' && ($formStyles['form_layout'] == 'writer' || $formStyles['form_layout'] == 'writer_border')){
							$output .= '<label class="arm-df__label-text"> * ' . $value['label'] . '</label>';
						} else {
							$output .= '<label class="arm-df__label-text">' . $value['label'] . '</label>';
						}*/
						$ngModelSelect = esc_attr( $name );
						if ( $form_type != 'active' ) {
							// $ngModelSelect = 'default_val_' . $field_id;
							$ngModelSelect = 'arm_forms[' . $form_id . '][' . $field_id . '][default_val]';
						}
						$field_attr  = 'name="' . esc_attr($ngModelSelect) . '" ' . $placeholder . ' ' . $required . ' ' . $disabled;
						$field_attr .= ' aria-label="' . esc_attr($value['label']) . '"';

						if ( isset( $field_options['readonly'] ) && 1 == $field_options['readonly'] ) {
							$field_attr .= ' readonly="readonly" ';
						}
						$arm_select_field_options = '';

						$writter_class      = ( ! empty( $form ) && isset( $formStyles['rtl'] ) && $formStyles['rtl'] == '1' ) ? 'armSelectOptionRTL' : 'armSelectOptionLTR';
						$default_option_val = $default_option_label = '';
						if ( ! empty( $value['options'] ) ) {
							$allOptions   = array();
							$drpdown_cntr = 0;
							foreach ( $value['options'] as $data ) {
								$data_default = $data     = stripslashes( $data );
								$new_data = explode( ':', $data );
								$option   = $key = isset( $new_data[0] ) ? $new_data[0] : $data;
                                $value_data='';
                                if(count($new_data)>1)
                                {
                                    $value_data = end($new_data);
                                    $labeldata = str_replace(':'.$value_data, '', $data_default);
                                    $option = $labeldata;
                                }
                                else {
                                    $option = $data_default;
                                }
								if ( isset( $value_data ) && $value_data != '' ) {
									$key = $value_data;
								}
								if ( $drpdown_cntr == 0 ) {
									$default_option_val   = $key;
									$default_option_label = $option;
								}
								if ( $value['meta_key'] == 'country' && isset( $value_data ) && ! empty( $field_val ) && ! is_numeric( $field_val ) ) {
									if ( $field_val == $new_data[0] ) {
										$field_val = $value_data;
									}
								}
								$selected_val = ( strtolower( $field_val ) == strtolower( $key ) ) ? 'selected' : '';
								if ( array_key_exists( $key, $allOptions ) ) {
									continue;
								}
								if ( ( strtolower( $field_val ) == strtolower( $key ) ) ) {
									$field_attr .= ' value="' . esc_attr( $key ) . '"';
								}

								$allOptions[ $key ] = $option;

								$arm_select_field_options .= '<li class="arm__dc--item" data-label="' . esc_html( $option ) . '" data-value="' . esc_attr( $key ) . '" aria-label="'.esc_attr($option).'">' . esc_html( $option ) . '</li>';
								$drpdown_cntr++;
							}
						} else {
							$arm_select_field_options .= '<li class="arm__dc--item" data-label="' . esc_html__( 'Choose your option', 'armember-membership' ) . '" data-value="" aria-label="' . esc_attr__('Choose your option', 'armember-membership') . '">' . esc_html__( 'Choose your option', 'armember-membership' ) . '</li>';
						}
						$default_option = '';
						if ( ( $formStyles['form_layout'] == 'writer' || $formStyles['form_layout'] == 'writer_border' ) ) {
							$default_option = '';
						} else {
							$default_option = $default_option_label;
						}
						$field_options = '';

						if ( empty( $field_val ) ) {
							$field_attr .= ' value="' . esc_attr( $default_option_val ) . '"';
						} else {
							$field_attr .= ' value="' . esc_attr( $field_val ) . '"';
						}
						$output .= '<input type="text" id="' . esc_attr( $value['id'] ) . '_' . esc_attr( $formRandomID ) . '" class="arm-selectpicker-input-control ' . esc_attr( $class ) . '" ' . $field_attr . $ng_change_func . ' ' . $onchange . ' />';
						$output .= '<dl class="arm-df__dropdown-control column_level_dd arm_member_form_dropdown ' . esc_attr( $writter_class ) . '">';
						$output .= '<dt class="arm__dc--head"><span class="arm__dc--head__title" id="arm-df__form-control_' . esc_attr($field_id) . '_'. esc_attr($formRandomID) .'">' . esc_html($default_option) . '</span><input type="text" style="display:none;" value="" class="arm-df__dc--head__autocomplete arm_autocomplete" autocomplete="' . esc_attr( $value['id'] ) . '_' . esc_attr( $formRandomID ) . '" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
						$output .= '<dd class="arm__dc--items-wrap"><ul class="arm__dc--items" data-id="' . esc_attr( $value['id'] ) . '_' . esc_attr( $formRandomID ) . '" style="display:none;">';
						$output .= $arm_select_field_options;
						$output .= '';
						$output .= '</ul></dd>';
						$output .= '</dl>';
						$output .= $ffield_label;
						// $output .= '<label class="arm-df__label-text active" for="arm-df__form-control_'.$field_id.'_'.$formRandomID.'" aria-label="' . esc_attr( $value['label'] ) . '">'.$value['label'].'</label>';

					}
					break;
				/* Radio Box */
				case 'radio':
					global $arm_slugs;
					if ( $field_val == '' && $value['default_val'] ) {
						$field_val = $value['default_val'];
					}
					if ( ! empty( $value['options'] ) ) {
						if ( is_admin() && isset( $_GET['page'] ) && in_array( $_GET['page'], array( $arm_slugs->manage_members ) ) ) { //phpcs:ignore
							/**
							 * For Admin Side Only
							 */
							foreach ( $value['options'] as $data ) {
								$data_default = $data     = stripslashes( $data );
								$new_data = explode( ':', $data );
								$value_data = '';
								$option   = $key = isset( $new_data[0] ) ? $new_data[0] : $data;
								if(count($new_data)>1)
                                {
                                    $value_data = end($new_data);
                                    $labeldata = str_replace(':'.$value_data, '', $data_default);
                                    $option = $labeldata;
                                }
                                else {
                                    $option = $data_default;
                                }
								if ( isset( $value_data ) && $value_data != '' ) {
									$key = $value_data;
								}

								if( is_array( $field_val ) )
                                {
                                    $field_val = !empty( $field_val[0] ) ? $field_val[0] : '';
                                }

								// $output .= '<div class=" arm-align-items-center' . esc_attr($class) . '">';
								$output .= '<input class="arm_iradio ' . esc_attr( $class ) . '" type="radio" name="' . esc_attr( $name ) . '" id="' . esc_attr( $value['id'] ) . '_' . esc_attr( $key ) . '_' . esc_attr( $form_id ) . '" value="' . esc_attr( $key ) . '" ' . checked( strtolower( $field_val ), strtolower( $key ), false ) . ' ' . $validation_data . '/>';
								$output .= '<label class="arm_radio_label" for="' . esc_attr( $value['id'] ) . '_' . esc_attr( $key ) . '_' . esc_attr( $form_id ) . '" aria-label="' . esc_attr( $option ) . '">' . esc_html( $option ) . '</label>';
								// $output .= '</div>';
								$validation_data = '';
							}
						} else {
							$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
							$general_settings    = $all_global_settings['general_settings'];
							$ng_change_func      = '';
							if ( isset( $general_settings['enable_tax'] ) && isset( $general_settings['country_tax_field'] ) && $general_settings['enable_tax'] == 1 && $name == $general_settings['country_tax_field'] ) {
								$ng_change_func = "onchange = \"armSetTax('arm_setup_form" . esc_attr( $formRandomID ) . "');\"";
							}
							if ( $form_type != 'active' ) {
								$field_name = 'arm_forms[' . esc_attr( $form_id ) . '][' . esc_attr( $field_id ) . '][default_val]';
							} else {
								$field_name = $name;
							}
							$field_attr = $disabled . $required;

							$default_radio_temp_value = false;
							$radio_controls           = '';

							foreach ( $value['options'] as $data ) {
								$data_default = $data     = stripslashes( $data );
								$new_data = explode( ':', $data );
								$option   = $key = isset( $new_data[0] ) ? $new_data[0] : $data;
								$value_data='';
                                if(count($new_data)>1)
                                {
                                    $value_data = end($new_data);
                                    $labeldata = str_replace(':'.$value_data, '', $data_default);
                                    $option = $labeldata;
                                }
                                else {
                                    $option = $data_default;
                                }
								if ( isset( $value_data ) && $value_data != '' ) {
									$key = $value_data;
								}
								$radio_controls .= '<div class="arm-df__radio arm-d-flex arm-align-items-center ' . esc_attr( $class ) . '">';
								$radio_controls .= '<input class="arm_iradio arm-df__form-control--is-radio ' . esc_attr( $class ) . '" type="radio" name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $value['id'] ) . '_' . esc_attr( $key ) . '_' . $formRandomID . '" value="' . esc_attr($key) . '" ' . checked( strtolower( $field_val ), strtolower( $key ), false ) . ' ' . $validation_data . $ng_change_func . '/>';
								$radio_controls .= '<label class="arm_radio_label arm-df__fc-radio--label" for="' . esc_attr( $value['id'] ) . '_' . esc_attr( $key ) . '_' . esc_attr( $formRandomID ) . '" aria-label="' . esc_attr( $option ) . '">' . esc_html( $option ) . '</label>';
								$radio_controls .= '</div>';

							}
							if ( is_admin() ) {

								$radio_controls .= '<span  class="arm_cancel_btn arm_form_reset_btn arm_reset_radio_field" onclick="armresetradiofield(\'arm-df__form-control_' . $field_id . '\')"><i class="armfa armfa-rotate-right"></i></span>';
							}

							// $output .= '<div class="arm-df__radio arm-d-flex arm-align-items-center ' . esc_attr( $class ) . '">';

								$output .= $radio_controls;

							// $output .= '</div>';

							// $output .= '<input type="hidden" name="' . esc_attr($field_name) . '" id="'.esc_attr($name).'_default_val" value="'. esc_attr( $field_val ) .'">';
						}
					}
					break;
				/* Checkbox */
				case 'checkbox':
					$fname  = $name;
					$fhname = $name . '_arm_hidden';
					if ( ! empty( $value['options'] ) && count( $value['options'] ) > 1 ) {
						$fname  = $name . '[]';
						$fhname = $name . '_arm_hidden[]';
					}
					if ( $field_val == '' && $value['default_val'] != '' ) {
						$field_val = $value['default_val'];
					}
					global $arm_slugs;

					if ( ! empty( $value['options'] ) ) {
						if ( is_admin() && isset( $_GET['page'] ) && in_array( $_GET['page'], array( $arm_slugs->manage_members ) ) ) { //phpcs:ignore
							/**
							 * For Admin Side Only
							 */
							$arm_form_chkbox_counter = 1;
							foreach ( $value['options'] as $data ) {
								$data         = stripslashes( $data );
								$data_default = $data;
								// $new_data = explode(':', $data);
								$new_data = explode( ':', wp_strip_all_tags( $data ) );
								$option   = $key = isset( $new_data[0] ) ? $new_data[0] : $data;
								$value_data='';
								if ( count( $new_data ) > 1 ) {
									$value_data = end( $new_data );
									$labeldata  = str_replace( ':' . $value_data, '', $data_default );
									$option     = $labeldata;
								} else {
									$option = $data_default;
								}

								if ( isset( $value_data ) && $value_data != '' ) {
									$key = $value_data;
								}
								if ( is_array( $field_val ) ) {
									$chked      = ( in_array( $key, $field_val ) ) ? 'checked="checked"' : '';
									$hidden_key = ( in_array( $key, $field_val ) ) ? esc_attr( $key ) : '';
								} else {
									$chked      = ( strtolower( $field_val ) == strtolower( $key ) ) ? 'checked="checked"' : '';
									$hidden_key = ( strtolower( $field_val ) == strtolower( $key ) ) ? esc_attr( $key ) : '';
								}
								$output .= '<input type="hidden" name="' . esc_attr( $fhname ) . '" id="' . esc_attr( $value['id'] ) . '_' . esc_attr($arm_form_chkbox_counter) . '_' . esc_attr( $form_id ) . '_arm_hidden" value="' . esc_attr($hidden_key) . '">';

								$output .= '<input class="arm_icheckbox arm_hidden_checkbox ' . esc_attr( $class ) . '" type="checkbox" name="' . esc_attr( $fname ) . '" id="' . esc_attr( $value['id'] ) . '_' . esc_attr($arm_form_chkbox_counter) . '_' . esc_attr( $form_id ) . '" value="' . esc_attr( $key ) . '"  data-id="' . esc_attr( $value['id'] ) . '_' . esc_attr($arm_form_chkbox_counter) . '_' . esc_attr( $form_id ) . '_arm_hidden" ' . $chked . ' ' . $validation_data . '/>';
								$output .= '<label class="arm_checkbox_label" for="' . esc_attr( $value['id'] ) . '_' . esc_attr($arm_form_chkbox_counter) . '_' . esc_attr($form_id) . '" aria-label="' . esc_attr( $option ) . '">' . esc_html($option) . '</label>';
								$arm_form_chkbox_counter++;
							}
						} else {
							$chkInputs                      = '';
							$arm_field_chkbox_checkName     = 'arm_form__' . esc_attr( $name ) . '_' . esc_attr($field_id);
							$arm_field_checkboxes_arr       = $value['options'];
							$arm_field_checkboxes_arr_count = count( $value['options'] );
							$arm_field_chkbox_ng_required   = '';
							$arm_field_chkbox_counter       = 1;

							foreach ( $arm_field_checkboxes_arr as $arm_field_checkboxe ) {
								if ( $arm_field_chkbox_counter == 1 ) {
									$arm_field_chkbox_ng_required = '!(' . $arm_field_chkbox_checkName . '__' . $arm_field_chkbox_counter;
								} else {
									$arm_field_chkbox_ng_required .= ' || ' . $arm_field_chkbox_checkName . '__' . $arm_field_chkbox_counter;
								}

								if ( $arm_field_checkboxes_arr_count == $arm_field_chkbox_counter ) {
									$arm_field_chkbox_ng_required .= ')';
								}

								$arm_field_chkbox_counter++;
							}

							$arm_form_chkbox_counter = 1;
							foreach ( $value['options'] as $data ) {
								$data         = stripslashes( $data );
								$data_default = $data;
								$new_data     = explode( ':', wp_strip_all_tags( $data ) );
								$value_data='';
								if ( count( $new_data ) > 1 ) {
									$value_data = end( $new_data );
									$labeldata  = str_replace( ':' . $value_data, '', $data_default );
									$option     = $labeldata;
								} else {
									$option = $data_default;
								}

								$key = isset( $new_data[0] ) ? $new_data[0] : $data;
								if ( isset( $value_data ) && $value_data != '' ) {
									$key = $value_data;
								}
								$checkName = esc_attr( $name ) . '_' . esc_attr($field_id);

								$ngModelCheck = 'arm_form__' . esc_attr( $name ) . '_' . esc_attr($field_id) . '__' . esc_attr($arm_form_chkbox_counter) . '_' . esc_attr($formRandomID);

								$field_val_arr = stripslashes_deep( $field_val );

								$field_attr = ' name="' . esc_attr( $name ) . '" ' . $disabled;
								if ( ! empty( $required ) ) {
									$arm_field_chkbox_msg_required = ( ! empty( $value['blank_message'] ) ) ? $value['blank_message'] : esc_html__( 'Please check atleast one option', 'armember-membership' );
									$field_attr                   .= ' data-validation-minchecked-minchecked="1" data-validation-minchecked-message="' . htmlentities( $arm_field_chkbox_msg_required ) . '"';
								}
								$checked_field_attr = $hidden_key = '';
								if ( $field_val == esc_attr( $key ) ) {
									$checked_field_attr = 'checked="checked"';
									$hidden_key         = ( strtolower( $field_val ) == strtolower( $key ) ) ? esc_attr( $key ) : '';
								}
								if ( is_array( $field_val ) && in_array( esc_attr( $key ), $field_val ) ) {
									$checked_field_attr = 'checked="checked"';
									$hidden_key         = ( in_array( $key, $field_val ) ) ? esc_attr( $key ) : '';
								}
								// $checked_field_attr='checked="checked"';

								if ( $form_type != 'active' ) {
									$field_name = 'arm_forms[' . esc_attr( $form_id ) . '][' . esc_attr( $field_id ) . '][default_val][]';
								} else {
									$field_name = $fname;
								}
								$default_field_val = ( isset( $field_val[ $arm_form_chkbox_counter - 1 ] ) ) ? $field_val[ $arm_form_chkbox_counter - 1 ] : $field_val;

								$output .= '<div class="arm-df__checkbox arm-d-flex arm-align-items-center">';
								$output .= '<input name="' . esc_attr( $field_name ) . '" aria-label="' . esc_html( $key ) . '" value="' . esc_attr( $key ) . '" class="arm-df__form-control--is-checkbox arm_hidden_checkbox ' . esc_attr( $class ) . '" ' . $field_attr . '  id="' . $ngModelCheck . '" ' . $onchange . ' data-id="' . esc_attr( $value['id'] ) . '_' . esc_attr($arm_form_chkbox_counter) . '_' . esc_attr($form_id) . '_arm_hidden" type="checkbox" ' . $checked_field_attr . '>';

								$output .= '<label class="arm-df__fc-checkbox--label" for="' . $ngModelCheck . '" aria-label="' . esc_attr( $option ) . '">' . $option . '</label>';

								$output .= '<input type="hidden" name="' . esc_attr( $fhname ) . '" id="' . esc_attr( $value['id'] ) . '_' . esc_attr($arm_form_chkbox_counter) . '_' . esc_attr( $form_id ) . '_arm_hidden" value="' . $hidden_key . '">';

								$output .= '</div>';

								$arm_form_chkbox_counter++;
							}

							$output .= $chkInputs;
						}
					}
					break;
				/* Remember Me */
				case 'rememberme':
					$inputName = ( $form_type == 'active' ) ? 'rememberme' : 'arm_forms[' . esc_attr( $form_id ) . '][' . esc_attr( $field_id ) . '][default_val]';
					if ( empty( $field_val ) && ! empty( $value['default_val'] ) && $value['default_val'] == 'forever' ) {
						$field_val = 'forever';
					}
					$chked      = ( strtolower( $field_val ) == 'forever' ) ? 'checked=\'checked\'"' : '';
					$field_attr = ' ' . $required . ' ' . $disabled;
					$output    .= '<div class="arm-df__checkbox arm-d-flex arm-align-items-center">';
					$output    .= '<input ' . $chked . ' name="' . esc_attr($inputName) . '" value="forever" class="arm-df__form-control--is-checkbox ' . esc_attr( $class ) . '" ' . $field_attr . ' type="checkbox" id="arm-df__form-control_' . esc_attr($field_id) . '_' . esc_attr($formRandomID) . '">';
					$output    .= '<label class="arm-df__fc-checkbox--label" for="arm-df__form-control_' . esc_attr( $field_id ) . '_' . esc_attr( $formRandomID ) . '" aria-label="' . esc_attr( $value['label'] ) . '">' . esc_html($value['label']) . '</label>';
					$output    .= '</div>';

					break;
				/*
				 Hidden Text Field */
				/* Roles Box */
				case 'roles':
					$sub_type   = $value['sub_type'];
					$fieldRoles = ( isset( $value['options'] ) && ! empty( $value['options'] ) ) ? $value['options'] : array();
					if ( $field_val == '' && $value['default_val'] != '' ) {
						$field_val = $value['default_val'];
					}
					if ( is_array( $field_val ) && count( $field_val ) == 0 ) {
						$field_val = '';
					}
					if ( $form_type != 'active' ) {
						$field_name = 'arm_forms[' . $form_id . '][' . $field_id . '][default_val]';
					} else {
						$field_name = $name;
					}
					$default = '';
					if ( $sub_type == 'radio' && is_admin() ) {
						if ( $required_star != '' && ( $formStyles['form_layout'] == 'writer' || $formStyles['form_layout'] == 'writer_border' ) ) {
							$output .= '<label class="arm-df__label-text active"  aria-label="' . esc_attr( $value['label'] ) . '"> * ' . esc_html($value['label']) . '</label>';
						} else {
							$output .= '<label class="arm-df__label-text active" aria-label="' . esc_attr( $value['label'] ) . '">' . esc_html($value['label']) . '</label>';
						}
					}
					if ( is_array( $field_val ) ) {
						$field_val = isset( $field_val[0] ) ? $field_val[0] : '';
					}
					if ( $sub_type == 'radio' ) {
						foreach ( $fieldRoles as $key => $option ) {
							$output .= '<div class="arm-df__radio arm-d-flex arm-align-items-center ' . esc_attr( $class ) . '">';
							$output .= '<input type="radio" name="' . esc_attr( $field_name ) . '" value="' . esc_attr( $key ) . '" class="arm_iradio arm-df__form-control--is-radio ' . esc_attr( $class ) . '" ' . $field_attr . checked( strtolower( $field_val ), strtolower( $key ), false ) . ' id="' . esc_attr( $value['id'] ) . '_' . esc_attr( $key ) . '_' . esc_attr( $formRandomID ) . '">';
							$output .= '<label class="arm_radio_label arm_roles_label arm-df__fc-radio--label" for="' . esc_attr( $value['id'] ) . '_' . esc_attr( $key ) . '_' .esc_attr( $formRandomID ) . '" aria-label="' . esc_attr( $option ) . '">' . esc_html( $option ) . '</label>';
							$output .= '</div>';
						}
					} else {
						$field_attr   .= ' aria-label="' . $value['label'] . '"';
						$writter_class = ( ! empty( $form ) && isset( $formStyles['rtl'] ) && $formStyles['rtl'] == '1' ) ? 'armSelectOptionRTL' : 'armSelectOptionLTR';

						$field_options = '';
						$drpdown_cntr  = 0;
						if ( $fieldRoles ) {
							$default_option_label = '';
							foreach ( $fieldRoles as $key => $option ) {
								$option = stripslashes( $option );
								if ( $drpdown_cntr == 0 ) {
									$default_option_label = $option;
								}
								$field_options .= '<li class="arm__dc--item" data-label="' . esc_html( $option ) . '" data-value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</li>';
								$drpdown_cntr++;
							}
						}
						$default_selected = '';
						if ( ! empty( $formStyles['form_layout'] ) && ( $formStyles['form_layout'] == 'writer' || $formStyles['form_layout'] == 'writer_border' ) ) {
							$default_selected = '';
						} else {
							$default_selected = $default_option_label;
						}

						$output .= '<input type="text" id="' . esc_attr( $value['id'] ) . '_' . esc_attr( $formRandomID ) . '" class="arm-selectpicker-input-control arm-df__form-control ' . esc_attr( $class ) . '" name="' . esc_attr( $field_name ) . '" value="' . esc_attr($field_val) . '"/>';
						$output .= '<dl class="arm-df__dropdown-control column_level_dd arm_member_form_dropdown ' . $writter_class . '">';
						$output .= '<dt class="arm__dc--head"><span class="arm__dc--head__title">' . esc_html($default_selected) . '</span><input type="text" style="display:none;" value="" class="arm-df__dc--head__autocomplete arm_autocomplete" autocomplete="' . esc_attr( $value['id'] ) . '_' . esc_attr( $formRandomID ) . '"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
						$output .= '<dd class="arm__dc--items-wrap"><ul class="arm__dc--items" data-id="' . esc_attr( $value['id'] ) . '_' . esc_attr( $formRandomID ) . '" style="display:none;">';
						$output .= $field_options;
						$output .= '';
						$output .= '</ul></dd>';
						$output .= '</dl>';
						$output .= $ffield_label;
					}
					// $output .= '<input type="hidden" name="' . esc_attr( $field_name ) . '" value="{{ arm_form.' . esc_attr( $name ) . '_' . esc_attr( $field_id ) . ' }}">';
					break;
				case 'hidden':
					if ( $form_type != 'active' ) {
						$output .= esc_html__( 'Hidden Field Area', 'armember-membership' );
					}
					$output .= '<input name="' . esc_attr( $name ) . '" type="hidden" class="' . esc_attr( $class ) . '" value="' . esc_attr( $field_val ) . '" ' . $field_attr . '/>';
					break;
				/* Info Block */
				case 'info':
					$id = '';
					if ( isset( $value['id'] ) ) {
						$id = 'id="' . esc_attr( $value['id'] ) . '" ';
					}
					if ( isset( $value['type'] ) ) {
						$class .= ' section-' . $value['type'];
					}
					if ( isset( $value['class'] ) ) {
						$class .= ' ' . $value['class'];
					}
					$output .= '<div ' . $id . 'class="' . esc_attr( $class ) . '">' . "\n";
					if ( isset( $name ) ) {
						$output .= '<h4 class="heading">' . esc_html( $value['name'] ) . '</h4>' . "\n";
					}
					if ( isset( $value['description'] ) ) {
						$output .= $value['description'] . "\n";
					}
					$output .= '</div>' . "\n";
					break;
				/* Submit */
				case 'submit':
					$buttonStyle  = ( isset( $formStyles['button_style'] ) && ! empty( $formStyles['button_style'] ) ) ? $formStyles['button_style'] : 'flat';
					$submit_attr  = '';
					$submit_class = '--arm-is-' . $buttonStyle . '-style ';
					if ( ( $formStyles['form_layout'] == 'writer' || $formStyles['form_layout'] == 'writer_border' ) && ! is_admin() ) {
						$submit_class = ' arm-waves-effect arm-waves-light';
					}
					$submit_class .= esc_attr( $class );
					if ( $form_type == 'active' ) {

						if(file_exists(ABSPATH . 'wp-admin/includes/file.php')){
							require_once(ABSPATH . 'wp-admin/includes/file.php');
						}

						WP_Filesystem();
						global $wp_filesystem;
						$arm_loader_url = MEMBERSHIPLITE_IMAGES_URL . "/loader.svg";
						$arm_loader_img = $wp_filesystem->get_contents($arm_loader_url);
						
						$submit_class .= ' arm-df__form-control_' . esc_attr($field_id);
						$submit_attr  .= ' type="submit"';
						$output       .= '<button class="arm-df__form-control-submit-btn arm-df__form-group_button ' . $submit_class . '" ' . $submit_attr . ' name="armFormSubmitBtn"><span class="arm_spinner">' . $arm_loader_img . '</span>' . html_entity_decode( stripslashes( $value['label'] ) ) . '</button>';
					} else {
						$submit_class .= ' arm-df__form-control_' . esc_attr( $field_id );
						$submit_attr  .= ' type="button" id="' . esc_attr( $value['id'] ) . '" name="arm_forms[' . $form_id . '][' . $field_id . '][submit]" ';
						$output       .= '<div class="arm-df__form-control-submit-btn arm-df__form-group_button arm_editable_input_button ' . $submit_class . '" ' . $submit_attr . '><div class="arm-df__field-label_text arm_editable_input_button_inner">' . html_entity_decode( stripslashes( $value['label'] ) ) . '</div><a href="javascript:void(0)" class="arm_form_btn_editable_link">&nbsp;</a></div>';

						$arm_show_login_link = ! empty( $form->settings['show_login_link'] ) ? 1 : 0;

						if ( $form->type == 'registration' ) {
							$arm_default_link_label  = '<span>';
							$arm_default_link_label .= esc_html__( 'Already have an account?', 'armember-membership' );
							$arm_default_link_label .= ' ';
							$arm_default_link_label .= '<a href="#">';
							$arm_default_link_label .= esc_html__( 'LOGIN', 'armember-membership' );
							$arm_default_link_label .= '</a>';
							$arm_default_link_label .= '';

							$arm_login_link_label = ! empty( $form->settings['login_link_label'] ) ? $form->settings['login_link_label'] : $arm_default_link_label;
							$arm_login_link_label = str_replace( '[ARMLINK]', '<a href="#">', $arm_login_link_label );
							$arm_login_link_label = str_replace( '[/ARMLINK]', '</a>', $arm_login_link_label );

							$arm_css_attr = ( ! $arm_show_login_link ) ? 'display: none;' : '';
							$output      .= '<div class="arm_reg_login_links" style="' . esc_attr($arm_css_attr) . '">' . $arm_login_link_label . '</div>';
						}
					}
					break;
				/* Html Area */
				case 'html':
					if ( $value['value'] != '' ) {
						$output .= stripcslashes( $value['value'] );
					}
					$output .= "\n";
					break;
				case 'section':
					if ( $value['value'] != '' ) {
						$output .= stripcslashes( $value['value'] );
					}
					$output .= "\n";
					break;
				case 'social_fields':
					break;
				default:
					break;
			}
			$helper_description_text = stripslashes_deep( nl2br( $field_desc ) );
			$helper_description      = '';
			if ( ! empty( $helper_description_text ) || is_admin() ) {
				$arm_has_helper_description_text_class = ( ! empty( $helper_description_text ) ) ? ' arm_df__helper-has-description' : '';

				$helper_description  = '<div class="arm_df__helper-description' . esc_attr($arm_has_helper_description_text_class) . '">';
				$helper_description .= '<div class="arm_df__helper-description-text">';
				$helper_description .= $helper_description_text . '</div></div>';
			}

			$field_sub_type = ( isset( $sub_type ) && $ffield_type == 'roles' ) ? $sub_type : '';
			if ( ! empty( $output ) ) {
				$ffield_type_class = $arm_field_wrap_active_class;
				if ( $ffield_type == 'checkbox' || $ffield_type == 'radio' || $field_sub_type == 'radio' ) {
					$ffield_type_class .= ' arm-d-flex';
					if ( $field_sub_type == 'radio' ) {
						$ffield_type_class .= ' arm-df__form-field-wrap--roles-radio';
					}
				} elseif ( $ffield_type == 'date' ) {
					$ffield_type_class .= ' arm_date_field_' . esc_attr($form_id);
				}
				$field_arm_controls_class = 'arm-controls';
				$field_arm_controls_class = apply_filters( 'arm_field_validation_class', $field_arm_controls_class, $field_options );

				$return_html  = '<div class="arm-df__form-field-wrap_' . esc_attr($value['type']) . ' arm-df__form-field-wrap ' . esc_attr($field_arm_controls_class) . ' ' . esc_attr($ffield_type_class) . '" id="arm-df__form-field-wrap_' . esc_attr($field_id) . '">' . $output . '</div>' . $helper_description;
				$return_html .= $psm;
			}
			return $return_html;
		}

		function arm_admin_save_member_details( $member_data = array() ) {
			global $wp, $wpdb, $current_user, $arm_slugs, $arm_lite_errors, $ARMemberLite, $arm_members_class, $arm_global_settings, $arm_subscription_plans, $arm_manage_communication, $arm_capabilities_global;
			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_members'], '1' );
			$redirect_to = admin_url( 'admin.php?page=' . $arm_slugs->manage_members );
			if ( ! empty( $member_data['action'] ) && in_array( $member_data['action'], array( 'add_member', 'update_member' ) ) ) {
				if ( preg_match( '/\s/', $member_data['user_pass'] ) ) {
					unset( $member_data );
					$message = esc_html__( 'Space not allowed in password field', 'armember-membership' );
					$arm_lite_errors->add( 'arm_reg_error', $message );
					return $arm_lite_errors;
				}
				
				$arm_file_upload_arr= isset( $_SESSION['arm_file_upload_arr'] ) ? $_SESSION['arm_file_upload_arr'] : array(); //phpcs:ignore

				// sesion handling
				if(!empty($arm_file_upload_arr)){
                    foreach ($arm_file_upload_arr as $upload_key => $upload_img) {
                        $base_name = $ARMemberLite->arm_get_basename($member_data[$upload_key]);
                        if(!(isset($member_data[$upload_key]) && $upload_img==$base_name)){
                            unset($member_data[$upload_key]);
                        }
                    }
					unset($_SESSION['arm_file_upload_arr']);
                }
				if ( $member_data['action'] == 'add_member' ) {
					$user_login = $member_data['user_login'];
					$user_email = sanitize_email( $member_data['user_email'] );
					$user_pass  = $member_data['user_pass'];

					$sanitized_user_login = sanitize_user( $user_login );
					$chk_user_login       = $arm_members_class->arm_validate_username( $user_login );
					/* Check the username */
					if ( ! empty( $chk_user_login ) ) {
						$arm_lite_errors->add( 'arm_reg_error', $chk_user_login );
						$sanitized_user_login = '';
					}
					/* Check the e-mail address */
					$user_email     = apply_filters( 'user_registration_email', $user_email );
					$chk_user_email = $arm_members_class->arm_validate_email( $user_email );
					if ( ! empty( $chk_user_email ) ) {
						$arm_lite_errors->add( 'arm_reg_error', $chk_user_email );
						$user_email = '';
					}
					/* Check Member password */
					if ( empty( $user_pass ) ) {
						$user_pass = apply_filters( 'arm_member_registration_pass', wp_generate_password( 12, false ) );
					}

					do_action( 'register_post', $sanitized_user_login, $user_email, $arm_lite_errors );

					remove_all_filters( 'registration_errors' );
					$arm_lite_errors = apply_filters( 'registration_errors', $arm_lite_errors, $sanitized_user_login, $user_email );

					do_action( 'arm_remove_third_party_error', $arm_lite_errors );
					if ( ! empty( $arm_lite_errors ) ) {
						if ( $arm_lite_errors->get_error_code() ) {
							return $arm_lite_errors;
						}
					}
					$user_ID = wp_create_user( $sanitized_user_login, $user_pass, $user_email );
					if ( ! $user_ID ) {
						$link_tag = '<a href="mailto:' . esc_attr( get_option( 'admin_email' ) ) . '">' . esc_html__( 'webmaster', 'armember-membership' ) . '</a>';
						$err_msg  = isset( $arm_global_settings->common_message['arm_user_not_created'] ) ? $arm_global_settings->common_message['arm_user_not_created'] : '';
						$err_msg  = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( "Couldn't register you... please contact the", 'armember-membership' ) . ' ' . $link_tag; //phpcs:ignore
						$arm_lite_errors->add( 'arm_reg_error', $err_msg );
						return $arm_lite_errors;
					}
					$update_data['ID']         = $user_ID;
					$update_data['user_email'] = $user_email;
					if ( isset( $member_data['user_nicename'] ) ) {
						$update_data['user_nicename'] = sanitize_text_field( $member_data['user_nicename'] );
					}
					if ( isset( $member_data['user_url'] ) ) {
						$update_data['user_url'] = sanitize_text_field( $member_data['user_url'] );
					}
					$display_name              = isset( $member_data['display_name'] ) ? sanitize_text_field( $member_data['display_name'] ) : '';
					$member_data['first_name'] = isset( $member_data['first_name'] ) ? trim( sanitize_text_field( $member_data['first_name'] ) ) : '';
					$member_data['last_name']  = isset( $member_data['last_name'] ) ? trim( sanitize_text_field( $member_data['last_name'] ) ) : '';
					if ( empty( $display_name ) ) {
						if ( $member_data['first_name'] && $member_data['last_name'] ) {
							/* translators: 1: first name, 2: last name */
							$display_name = $member_data['first_name'] . ' ' . $member_data['last_name'];
						} elseif ( $member_data['first_name'] ) {
							$display_name = $member_data['first_name'];
						} elseif ( $member_data['last_name'] ) {
							$display_name = $member_data['last_name'];
						} else {
							$display_name = $user_login;
						}
					}
					$update_data['display_name'] = $display_name;

					$user_ID = wp_update_user( $update_data );
					if ( ! empty( $member_data['form'] ) ) {
						update_user_meta( $user_ID, 'arm_form_id', $member_data['form'] );
					}
					$success_message = esc_html__( 'New member has been added successfully.', 'armember-membership' );
					$ARMemberLite->arm_set_message( 'success', $success_message );
					$redirect_to = $arm_global_settings->add_query_arg( 'action', 'edit_member', $redirect_to );
					$redirect_to = $arm_global_settings->add_query_arg( 'id', $user_ID, $redirect_to );
				} elseif ( $member_data['action'] == 'update_member' && ! empty( $member_data['id'] ) && $member_data['id'] != 0 ) {
					$member_id   = intval( $member_data['id'] );
					$up_user     = get_userdata( $member_id );
					$user_roles = $up_user->roles;
					if((!empty($user_roles) && in_array('administrator',$user_roles,true)) || (is_multisite() && is_super_admin($member_id)))
					{
						wp_safe_redirect($redirect_to);
						exit;
					}
					$user_email  = apply_filters( 'user_registration_email', $member_data['user_email'] );
					$update_data = array(
						'ID'         => $member_id,
						'user_email' => $user_email,
					);
					/* Check the e-mail address */
					if ( strtolower( $user_email ) != strtolower( $up_user->user_email ) ) {
						$chk_user_email = $arm_members_class->arm_validate_email( $user_email );
						if ( ! empty( $chk_user_email ) ) {
							$arm_lite_errors->add( 'arm_profile_error', $chk_user_email );
							unset( $update_data['user_email'] );
						}
					}
					if ( $arm_lite_errors->get_error_code() ) {
						return $arm_lite_errors;
					}
					if ( isset( $member_data['user_url'] ) ) {
						$update_data['user_url'] = sanitize_text_field( $member_data['user_url'] );
					}
					$display_name              = isset( $member_data['display_name'] ) ? sanitize_text_field( $member_data['display_name'] ) : '';
					$member_data['first_name'] = isset( $member_data['first_name'] ) ? trim( sanitize_text_field( $member_data['first_name'] ) ) : '';
					$member_data['last_name']  = isset( $member_data['last_name'] ) ? trim( sanitize_text_field( $member_data['last_name'] ) ) : '';
					if ( empty( $display_name ) ) {
						if ( $member_data['first_name'] && $member_data['last_name'] ) {
							/* translators: 1: first name, 2: last name */
							$display_name = $member_data['first_name'] . ' ' . $member_data['last_name'];
						} elseif ( $member_data['first_name'] ) {
							$display_name = $member_data['first_name'];
						} elseif ( $member_data['last_name'] ) {
							$display_name = $member_data['last_name'];
						} else {
							$display_name = $up_user->user_login;
						}
					}
					$update_data['display_name'] = $display_name;
					if ( ! empty( $member_data['user_pass'] ) ) {
						$update_data['user_pass'] = $member_data['user_pass'];
					}
					$user_ID = wp_update_user( $update_data );

					if ( is_wp_error( $user_ID ) ) {
						/* There was an error, probably that user doesn't exist. */
						$usernotexist = esc_html__( "User doesn't exist.", 'armember-membership' );
						$arm_lite_errors->add( 'arm_profile_error', $usernotexist );
						return $arm_lite_errors;
					}
					$ARMemberLite->arm_set_message( 'success', esc_html__( 'Member detail has been updated successfully.', 'armember-membership' ) );
					$redirect_to = $arm_global_settings->add_query_arg( 'action', 'edit_member', $redirect_to );
					$redirect_to = $arm_global_settings->add_query_arg( 'id', $user_ID, $redirect_to );
				}
				if ( ! empty( $user_ID ) ) {
					$old_primary_status   = arm_get_member_status( $user_ID );
					$old_secondary_status = arm_get_member_status( $user_ID, 'secondary' );
					$is_status_change     = false;
					if ( $old_primary_status != 3 ) {
						if ( isset( $member_data['arm_primary_status'] ) && $member_data['arm_primary_status'] == '1' ) {
							$member_data['arm_primary_status']   = '1';
							$member_data['arm_secondary_status'] = '0';
						} else {
							$member_data['arm_primary_status'] = '2';
							if ( $old_secondary_status != 1 ) {
								$secondary_status                    = 0;
								$member_data['arm_secondary_status'] = $secondary_status;

								$old_plan_ids = get_user_meta( $user_ID, 'arm_user_plan_ids', true );
								if ( ! empty( $old_plan_ids ) && is_array( $old_plan_ids ) ) {
									foreach ( $old_plan_ids as $old_plan_id ) {
										$planData = get_user_meta( $user_ID, 'arm_user_plan_' . $old_plan_id, true );
										if ( ! empty( $planData ) ) {
											$plan_detail = $planData['arm_current_plan_detail'];
											if ( ! empty( $plan_detail ) ) {
												$old_plan = new ARM_Plan_Lite( 0 );
												$old_plan->init( (object) $plan_detail );
											} else {
												$old_plan = new ARM_Plan_Lite( $old_plan_id );
											}
											if ( $old_plan->is_paid() && ! $old_plan->is_lifetime() && $old_plan->is_recurring() ) {

												if ( isset( $member_data['arm_user_stop_user_plan'] ) && $member_data['arm_user_stop_user_plan'] == '1' ) {
													$secondary_status = 6;
													do_action( 'arm_before_update_user_subscription', $user_ID, '0' );

													do_action( 'arm_cancel_subscription', $user_ID, $old_plan_id );

													$arm_subscription_plans->arm_add_membership_history( $user_ID, $old_plan_id, 'cancel_subscription' );
													$arm_subscription_plans->arm_clear_user_plan_detail( $user_ID, $old_plan_id );
												}
											}
										}
									}
									if ( isset( $member_data['arm_user_stop_user_plan'] ) && $member_data['arm_user_stop_user_plan'] == '1' ) {
										unset( $member_data['arm_user_plan'] );
										$member_data['arm_secondary_status'] = $secondary_status;
									}
								}
							}
						}
					} else {
						if ( isset( $member_data['arm_primary_status'] ) && $member_data['arm_primary_status'] == '1' ) {
							$is_status_change                    = true;
							$member_data['arm_primary_status']   = '1';
							$member_data['arm_secondary_status'] = '0';
						}
					}
					unset( $member_data['arm_user_stop_user_plan'] );
					$old_plan_id   = 0;
					$old_plan_data = array();
					$old_plan_ids  = get_user_meta( $user_ID, 'arm_user_plan_ids', true );
					$old_plan_ids  = ! empty( $old_plan_ids ) ? $old_plan_ids : array();
					if ( ! isset( $member_data['arm_user_plan'] ) ) {
						$member_data['arm_user_plan'] = 0;
					} else {
						if ( is_array( $member_data['arm_user_plan'] ) ) {
							foreach ( $member_data['arm_user_plan'] as $key => $mpid ) {
								if ( empty( $mpid ) ) {
									unset( $member_data['arm_user_plan'][ $key ] );
								} else {
									$member_data[ 'arm_subscription_start_' . $mpid ] = isset( $member_data['arm_subscription_start_date'][ $key ] ) ? $member_data['arm_subscription_start_date'][ $key ] : '';
								}
							}
							unset( $member_data['arm_subscription_start_date'] );
							$member_data['arm_user_plan'] = array_values( $member_data['arm_user_plan'] );
							$member_data['arm_user_plan'] = array_unique( $member_data['arm_user_plan'] );
						}
					}

					if ( ! isset( $member_data['roles'] ) ) {
						$member_data['roles'] = '';
					}

					$arm_user_suspended_plan_ids = isset( $member_data['arm_user_suspended_plan'] ) ? $member_data['arm_user_suspended_plan'] : array();
					update_user_meta( $user_ID, 'arm_user_suspended_plan_ids', $arm_user_suspended_plan_ids );

					unset( $member_data['arm_user_suspended_plan'] );
					
					$admin_save_flag = 1;
					do_action( 'arm_member_update_meta', $user_ID, $member_data, $admin_save_flag );

					if ( ! empty( $member_data['arm_user_plan'] ) ) {
						$arm_changed_expiry_date_plan = get_user_meta( $user_ID, 'arm_changed_expiry_date_plans', true );
						$arm_changed_expiry_date_plan = ! empty( $arm_changed_expiry_date_plan ) ? $arm_changed_expiry_date_plan : array();
						if ( is_array( $member_data['arm_user_plan'] ) ) {
							foreach ( $member_data['arm_user_plan'] as $key => $mpid ) {

								if ( isset( $member_data[ 'arm_subscription_expiry_date_' . $mpid ] ) && ! empty( $member_data[ 'arm_subscription_expiry_date_' . $mpid ] ) ) {
									$user_plan_data = get_user_meta( $user_ID, 'arm_user_plan_' . $mpid, true );

									if ( $user_plan_data['arm_expire_plan'] != strtotime( $member_data[ 'arm_subscription_expiry_date_' . $mpid ] ) ) {
										if ( ! in_array( $mpid, $arm_changed_expiry_date_plan ) ) {
											$arm_changed_expiry_date_plan[] = $mpid;
										}
									}

									$user_plan_data['arm_expire_plan'] = strtotime( $member_data[ 'arm_subscription_expiry_date_' . $mpid ] );
									update_user_meta( $user_ID, 'arm_user_plan_' . $mpid, $user_plan_data );
									update_user_meta( $user_ID, 'arm_changed_expiry_date_plans', $arm_changed_expiry_date_plan );
								}
							}
						} else {
							if ( isset( $member_data[ 'arm_subscription_expiry_date_' . $member_data['arm_user_plan'] ] ) && ! empty( $member_data[ 'arm_subscription_expiry_date_' . $member_data['arm_user_plan'] ] ) ) {
								$user_plan_data = get_user_meta( $user_ID, 'arm_user_plan_' . $member_data['arm_user_plan'], true );

								if ( $user_plan_data['arm_expire_plan'] != strtotime( $member_data[ 'arm_subscription_expiry_date_' . $member_data['arm_user_plan'] ] ) ) {
									if ( ! in_array( $member_data['arm_user_plan'], $arm_changed_expiry_date_plan ) ) {
										$arm_changed_expiry_date_plan[] = $member_data['arm_user_plan'];
									}
								}
								update_user_meta( $user_ID, 'arm_changed_expiry_date_plans', $arm_changed_expiry_date_plan );
								$user_plan_data['arm_expire_plan'] = strtotime( $member_data[ 'arm_subscription_expiry_date_' . $member_data['arm_user_plan'] ] );
								update_user_meta( $user_ID, 'arm_user_plan_' . $member_data['arm_user_plan'], $user_plan_data );
							}
						}
					}

					if ( ! empty( $member_data['arm_user_future_plan'] ) ) {
						$arm_changed_expiry_date_plan = get_user_meta( $user_ID, 'arm_changed_expiry_date_plans', true );
						$arm_changed_expiry_date_plan = ! empty( $arm_changed_expiry_date_plan ) ? $arm_changed_expiry_date_plan : array();
						if ( is_array( $member_data['arm_user_future_plan'] ) ) {
							foreach ( $member_data['arm_user_future_plan'] as $fkey => $fmpid ) {
								if ( isset( $member_data[ 'arm_subscription_expiry_date_' . $fmpid ] ) && ! empty( $member_data[ 'arm_subscription_expiry_date_' . $fmpid ] ) ) {
									$user_plan_data = get_user_meta( $user_ID, 'arm_user_plan_' . $fmpid, true );

									if ( $user_plan_data['arm_expire_plan'] != strtotime( $member_data[ 'arm_subscription_expiry_date_' . $fmpid ] ) ) {
										if ( ! in_array( $fmpid, $arm_changed_expiry_date_plan ) ) {
											$arm_changed_expiry_date_plan[] = $fmpid;
										}
									}

									$user_plan_data['arm_expire_plan'] = strtotime( $member_data[ 'arm_subscription_expiry_date_' . $fmpid ] );
									update_user_meta( $user_ID, 'arm_user_plan_' . $fmpid, $user_plan_data );
									update_user_meta( $user_ID, 'arm_changed_expiry_date_plans', $arm_changed_expiry_date_plan );
								}
							}
						}
					}

					$wpdb->query( $wpdb->prepare( 'DELETE FROM `' . $wpdb->usermeta . "` WHERE  `meta_key` LIKE  %s",'arm_subscription_expiry_date\_%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

					if ( ! empty( $old_plan_ids ) && is_array( $old_plan_ids ) ) {

						$old_plan_id   = isset( $old_plan_ids[0] ) ? $old_plan_ids[0] : 0;
						$old_plan_data = get_user_meta( $user_ID, 'arm_user_plan_' . $old_plan_id, true );

						$extend_renewal_date_plan_ids = array();
						$count                        = 0;
						foreach ( $old_plan_ids as $old_pid ) {
							$old_plan_data = get_user_meta( $user_ID, 'arm_user_plan_' . $old_pid, true );
							if ( ! empty( $old_plan_data ) ) {
								$oldPlanDetail = $old_plan_data['arm_current_plan_detail'];
								if ( ! empty( $oldPlanDetail ) ) {
									$planObj = new ARM_Plan_Lite( 0 );
									$planObj->init( (object) $oldPlanDetail );
								} else {
									$planObj = new ARM_Plan_Lite( $old_pid );
								}

								$arm_selected_payment_mode = $old_plan_data['arm_payment_mode'];

								if ( $planObj->is_recurring() && $arm_selected_payment_mode == 'manual_subscription' ) {
									$count++;
									$extend_renewal_date_plan_ids[] = $old_pid;
								}
							}
						}
						if ( ! empty( $extend_renewal_date_plan_ids ) && is_array( $extend_renewal_date_plan_ids ) ) {
							$user_suspended_plans_ids_array = get_user_meta( $user_ID, 'arm_user_suspended_plan_ids', true );
							$removed_suspended_plans        = 0;
							foreach ( $extend_renewal_date_plan_ids as $extend_renewal_date_plan_id ) {

								$old_plan_data = get_user_meta( $user_ID, 'arm_user_plan_' . $extend_renewal_date_plan_id, true );

								if ( isset( $member_data[ 'arm_user_grace_plus_' . $extend_renewal_date_plan_id ] ) && $member_data[ 'arm_user_grace_plus_' . $extend_renewal_date_plan_id ] !== 0 ) {
									$arm_old_next_payment_due_date = $old_plan_data['arm_next_due_payment'];
									$payment_cycle                 = $old_plan_data['arm_payment_cycle'];
									$grace_period                  = $member_data[ 'arm_user_grace_plus_' . $extend_renewal_date_plan_id ];
									/* if next due date meta is not there than calculate it */

									$arm_plan_expire = $old_plan_data['arm_expire_plan'];
									if ( isset( $arm_old_next_payment_due_date ) && $arm_old_next_payment_due_date === '' ) {
										$arm_old_next_payment_due_date = $arm_members_class->arm_get_next_due_date( $user_ID, $extend_renewal_date_plan_id, false, $payment_cycle );
									}

									$arm_next_payment_due_date = strtotime( date( 'Y-m-d', strtotime( "+$grace_period days", $arm_old_next_payment_due_date ) ) ); //phpcs:ignore

									$old_plan_data['arm_next_due_payment'] = $arm_next_payment_due_date;

									$oldPlanDetail = $old_plan_data['arm_current_plan_detail'];
									if ( ! empty( $oldPlanDetail ) ) {
										$planObj = new ARM_Plan_Lite( 0 );
										$planObj->init( (object) $oldPlanDetail );
									} else {
										$planObj = new ARM_Plan_Lite( $extend_renewal_date_plan_id );
									}

									$recurringData    = $planObj->prepare_recurring_data( $payment_cycle );
									$total_recurrence = $recurringData['rec_time'];
									$completed_rec    = $old_plan_data['arm_completed_recurring'];

									if ( $total_recurrence == $completed_rec ) {

										$old_plan_data['arm_expire_plan'] = strtotime( date( 'Y-m-d', strtotime( "+$grace_period days", $arm_plan_expire ) ) ); //phpcs:ignore
									}

									update_user_meta( $user_ID, 'arm_user_plan_' . $extend_renewal_date_plan_id, $old_plan_data );
								}

								$wpdb->query( $wpdb->prepare( 'DELETE FROM `' . $wpdb->usermeta . "` WHERE  `meta_key` LIKE  %s",'arm_user_grace_plus\_%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
								if ( isset( $member_data[ 'arm_skip_next_renewal_' . $extend_renewal_date_plan_id ] ) && $member_data[ 'arm_skip_next_renewal_' . $extend_renewal_date_plan_id ] == 1 ) {
									$complete_recuring = $old_plan_data['arm_completed_recurring'];
									$payment_cycle     = $old_plan_data['arm_payment_cycle'];
									$old_next_due_date = $old_plan_data['arm_next_due_payment'];

									$now = current_time( 'mysql' );

									 $arm_last_payment_status = $wpdb->get_var( $wpdb->prepare( 'SELECT `arm_transaction_status` FROM `' . $ARMemberLite->tbl_arm_payment_log . '` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1', $user_ID, $extend_renewal_date_plan_id, $now ) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name

									if ( strtotime( $now ) < $old_next_due_date ) {

										if ( $arm_last_payment_status != 'failed' ) {
											if ( $complete_recuring !== '' ) {
												$old_plan_data['arm_completed_recurring'] = ++$complete_recuring;
											} else {
												$old_plan_data['arm_completed_recurring'] = 1;
											}
											update_user_meta( $user_ID, 'arm_user_plan_' . $extend_renewal_date_plan_id, $old_plan_data );

											$arm_next_payment_due_date             = $arm_members_class->arm_get_next_due_date( $user_ID, $extend_renewal_date_plan_id, false, $payment_cycle );
											$old_plan_data['arm_next_due_payment'] = $arm_next_payment_due_date;
											$old_plan_data['arm_user_gateway']     = 'manual';

											$old_plan_data['arm_is_user_in_grace']    = 0;
											$old_plan_data['arm_grace_period_end']    = '';
											$old_plan_data['arm_grace_period_action'] = '';

											update_user_meta( $user_ID, 'arm_user_plan_' . $extend_renewal_date_plan_id, $old_plan_data );
										}
									} else {

										if ( $complete_recuring !== '' ) {
											$old_plan_data['arm_completed_recurring'] = ++$complete_recuring;
										} else {
											$old_plan_data['arm_completed_recurring'] = 1;
										}
										update_user_meta( $user_ID, 'arm_user_plan_' . $extend_renewal_date_plan_id, $old_plan_data );

										$arm_next_payment_due_date             = $arm_members_class->arm_get_next_due_date( $user_ID, $extend_renewal_date_plan_id, false, $payment_cycle );
										$old_plan_data['arm_next_due_payment'] = $arm_next_payment_due_date;
										$old_plan_data['arm_user_gateway']     = 'manual';

										$old_plan_data['arm_is_user_in_grace']    = 0;
										$old_plan_data['arm_grace_period_end']    = '';
										$old_plan_data['arm_grace_period_action'] = '';

										update_user_meta( $user_ID, 'arm_user_plan_' . $extend_renewal_date_plan_id, $old_plan_data );
									}

									if ( ! empty( $user_suspended_plans_ids_array ) ) {
										if ( in_array( $extend_renewal_date_plan_id, $user_suspended_plans_ids_array ) ) {
											unset( $user_suspended_plans_ids_array[ array_search( $extend_renewal_date_plan_id, $user_suspended_plans_ids_array ) ] );
											$removed_suspended_plans = 1;
										}
									}

									$arm_members_class->arm_add_manual_user_payment( $user_ID, $extend_renewal_date_plan_id );
								}
								$wpdb->query( $wpdb->prepare( 'DELETE FROM `' . $wpdb->usermeta . "` WHERE  `meta_key` LIKE  %s",'arm_skip_next_renewal\_%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
							}
							if ( $removed_suspended_plans == 1 ) {
								update_user_meta( $user_ID, 'arm_user_suspended_plan_ids', array_values( $user_suspended_plans_ids_array ) );
							}
						}
					}

					if ( $member_data['action'] == 'add_member' ) {
						$wpdb->update( $ARMemberLite->tbl_arm_members, array( 'arm_user_type' => 1 ), array( 'arm_user_id' => $user_ID ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						arm_new_user_notification( $user_ID, $user_pass );
						do_action( 'arm_after_add_new_user', $user_ID, $member_data );

						if ( isset( $member_data['arm_user_plan'] ) && ! empty( $member_data['arm_user_plan'] ) ) {

								do_action( 'arm_after_user_plan_change_by_admin', $user_ID, $member_data['arm_user_plan'] );

						}
					} elseif ( $member_data['action'] == 'update_member' ) {

						// do not forget to change in arm_user_plan_action()

						if ( $is_status_change ) {
							$user_data = get_user_by( 'id', $user_ID );
							/* Send Account Verify Notification Mail */
							armMemberAccountVerifyMail( $user_data );
						}

						if ( isset( $member_data['arm_user_plan'] ) && ! empty( $member_data['arm_user_plan'] ) ) {

								// $old_plan_ids = array_intersect($member_data['arm_user_plan'], $old_plan_ids);
								// foreach ($member_data['arm_user_plan'] as $plan_id) {
							if ( ! in_array( $member_data['arm_user_plan'], $old_plan_ids ) ) {

								do_action( 'arm_after_user_plan_change_by_admin', $user_ID, $member_data['arm_user_plan'] );
							}
							   // }

						}
						do_action( 'arm_after_update_user_profile', $user_ID, $member_data );

						// do not forget to change in arm_user_plan_action()
					}
					if ( ! empty( $redirect_to ) ) {
						wp_redirect( $redirect_to );
						exit;
					}
				}
			}
		}

		function arm_shortcode_form_ajax_action() {
			global $wp, $wpdb, $current_user, $arm_lite_errors, $ARMemberLite, $arm_global_settings, $arm_email_settings;
			$all_errors  = array();
			$ARMemberLite->arm_session_start();
			$err_msg     = isset( $arm_global_settings->common_message['arm_general_msg'] ) ? $arm_global_settings->common_message['arm_general_msg'] : '';
			$err_msg     = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'Sorry, Something went wrong. Please try again.', 'armember-membership' );
			$return      = array(
				'status'  => 'error',
				'type'    => 'message',
				'message' => $err_msg,
			);
			$current_url = $arm_global_settings->add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
			$redirect_to = ! empty( $_REQUEST['redirect_to'] ) ? esc_html( sanitize_text_field( $_REQUEST['redirect_to'] ) ) : ARMLITE_HOME_URL; //phpcs:ignore
			if ( isset( $_POST ) && ! empty( $_POST['arm_action'] ) ) { //phpcs:ignore
				/* Process submitted data. */
				$http_post   = ( !empty( $_SERVER['REQUEST_METHOD'] ) && 'POST' == $_SERVER['REQUEST_METHOD'] );
				$posted_data = ( ! empty( $_POST ) ) ? $_POST : array(); //phpcs:ignore
				if(!empty($posted_data['bank_transfer']['additional_info']))
				{
					$posted_data['bank_transfer']['additional_info'] = isset($_POST['bank_transfer']['additional_info'])  ? sanitize_textarea_field( $_POST['bank_transfer']['additional_info'] ) : ''; //phpcs:ignore
				}
				if(!empty($_POST['user_pass'])) //phpcs:ignore
				{
					$posted_data['user_pass'] = $_POST['user_pass']; //phpcs:ignore
				}
				if(!empty($_POST['repeat_pass'])) //phpcs:ignore
				{
					$posted_data['repeat_pass'] = $_POST['repeat_pass']; //phpcs:ignore
				}

				if ( isset( $posted_data['arm_user_plan'] ) ) {
					unset( $posted_data['arm_user_plan'] );
				}

				if ( isset( $posted_data['arm_primary_status'] ) ) {
					unset( $posted_data['arm_primary_status'] );
				}

				if ( isset( $posted_data['arm_user_future_plan'] ) ) {
					unset( $posted_data['arm_user_future_plan'] );
				}
				// sesion handling
				$posted_data = $this->verify_fileupload_form_fields($posted_data);
				
				$form        = isset($posted_data['arm_action']) ? $posted_data['arm_action'] : '';
				$armform     = "";
				$form_type   = "";
				$form_settings= array();
				if ( is_user_logged_in() ) {
					if ( $form == 'edit_profile' ) {
						$form_type       = 'edit_profile';
						$form_id         = intval( $posted_data['arm_parent_form_id'] );
						$success_message = ! empty( $posted_data['arm_success_message'] ) ? sanitize_text_field( $posted_data['arm_success_message'] ) : '';
						unset( $posted_data['arm_parent_form_id'] );
						unset( $posted_data['arm_success_message'] );
						$armform       = new ARM_Form_Lite( 'id', $form_id );
						$armform->type = 'edit_profile';
					}
					else if($form=='change-password')
					{
						$form_id       = ( isset( $posted_data['arm_form_id'] ) ) ? intval( $posted_data['arm_form_id'] ) : '';
						$armform       = new ARM_Form_Lite( 'slug', $form );
						$form_type     = $armform->type;
						$form_settings = $armform->settings;
					}
				} else if ( !is_user_logged_in() ) {
					$form_id       = ( isset( $posted_data['arm_form_id'] ) ) ? intval( $posted_data['arm_form_id'] ) : '';
					$armform       = new ARM_Form_Lite( 'slug', $form );
					$form_type     = $armform->type;
					$form_settings = $armform->settings;
				}

				if( !empty( $posted_data['subscription_plan'] ) && $form_type != 'register' && $form_type != 'registration' )
				{
					unset($posted_data['subscription_plan']);
				} 
				else if ( !empty( $posted_data['subscription_plan'] ) ) 
				{
					$arm_check_subscription_plan = intval($posted_data['subscription_plan']);
					$planObj      = new ARM_Plan_Lite( $arm_check_subscription_plan );
					if( !empty($planObj->type) && $planObj->type!='free' )
					{
						unset($posted_data['subscription_plan']);
					}
				}
				
				$arm_form_fields  = !empty($armform->fields) ? $armform->fields : array();
				$field_options    = array();
				$is_hide_username = 0;
                $current_password_invalid_field='';
				foreach ( $arm_form_fields as $fields ) {
					if ( $fields['arm_form_field_slug'] == 'user_login' ) {
						$field_options = $fields['arm_form_field_option'];
						if ( isset( $field_options['hide_username'] ) && $field_options['hide_username'] == 1 ) {
							$posted_data['user_login'] = sanitize_email( $posted_data['user_email'] );
							$is_hide_username          = 1;
						}
                    }
                    if ($fields['arm_form_field_slug'] == 'current_user_pass') {
                        $field_options = $fields['arm_form_field_option'];
                        $current_password_invalid_field = !empty($field_options['invalid_message']) ? $field_options['invalid_message'] : esc_html__('Please enter valid current password.','armember-membership');
					}
				}
				$posted_data['form_type'] = $form_type;
				do_action( 'arm_before_form_submit_action', $armform, $posted_data );
				$all_errors = $this->arm_member_validate_meta_details( $armform, $posted_data );
				if ( $all_errors === true ) {
					do_action( 'arm_after_form_validate_action', $armform, $posted_data );
					switch ( $form_type ) {
						case 'registration':
						case 'register':
							$posted_data['form'] = $form;
							$user_id             = $this->arm_register_new_member( $posted_data, $armform );

							global $arm_login_from_registration;
							if ( is_numeric( $user_id ) && ! is_array( $user_id ) ) {

								$arm_default_redirection_settings = get_option( 'arm_redirection_settings' );
								$arm_default_redirection_settings = maybe_unserialize( $arm_default_redirection_settings );
								$login_redirection_rules_options  = $arm_default_redirection_settings['signup'];
								$referral_url                     = isset( $posted_data['referral_url'] ) ? esc_url_raw( $posted_data['referral_url'] ) : '';

								if ( $login_redirection_rules_options['type'] == 'page' ) {
									$form_redirect_id = ( ! empty( $login_redirection_rules_options['page_id'] ) ) ? $login_redirection_rules_options['page_id'] : '0';
									$redirect_to      = $arm_global_settings->arm_get_permalink( '', $form_redirect_id );
								} elseif ( $login_redirection_rules_options['type'] == 'referral' ) {
									$default_redirect = ( ! empty( $login_redirection_rules_options['refferel'] ) ) ? $login_redirection_rules_options['refferel'] : ARM_HOME_URL;
									$redirect_to      = ( ! empty( $referral_url ) ) ? $referral_url : $default_redirect;

								} else {
									$redirect_to = ( ! empty( $login_redirection_rules_options['url'] ) ) ? $login_redirection_rules_options['url'] : ARMLITE_HOME_URL;
									$user_info   = get_userdata( $user_id );
									$username    = $user_info->user_login;
									$redirect_to = str_replace( '{ARMCURRENTUSERNAME}', $username, $redirect_to );
									$redirect_to = str_replace( '{ARMCURRENTUSERID}', $user_id, $redirect_to );
								}

								$register_message  = $redirect_to;
								$arm_return_script = '';
								$return['script']  = apply_filters( 'arm_after_register_submit_sucess_outside', $arm_return_script );
								$return['status']  = 'success';
								$return['type']    = 'redirect';
								$return['message'] = $register_message;
							} else {
								$all_errors = $arm_lite_errors->get_error_messages( 'arm_reg_error' );
							}
							break;

						case 'edit_profile':
						case 'update_profile':
							if ( $is_hide_username == 1 ) {
								$posted_data['hide_username'] = 1;
							} else {
								$posted_data['hide_username'] = 0;
							}

							$user_id = $this->arm_update_member_profile( $posted_data );

							if ( is_numeric( $user_id ) && ! is_array( $user_id ) ) {

								update_user_meta( $user_id, 'arm_form_id', $form_id );

								$return['status']  = 'success';
								$return['message'] = $success_message;
							} else {
								$all_errors = $arm_lite_errors->get_error_messages( 'arm_profile_error' );
							}
							break;
						case 'login':
						case 'signin':
							if ( ! is_user_logged_in() ) {

								$login_data['user_login']    = isset( $posted_data['user_login'] ) ? sanitize_text_field( $posted_data['user_login'] ) : '';
								$login_data['user_password'] = isset( $posted_data['user_pass'] ) ? $posted_data['user_pass'] : '';
								$login_data['remember']      = isset( $posted_data['rememberme'] ) ? sanitize_text_field( $posted_data['rememberme'] ) : '';
								$referral_url                = isset( $posted_data['referral_url'] ) ? esc_url_raw( $posted_data['referral_url'] ) : '';
								if ( is_multisite() ) {
									$user       = get_user_by( 'login', $login_data['user_login'] );
									$is_deleted = get_user_meta( $user->ID, 'arm_site_' . $GLOBALS['blog_id'] . '_deleted', true );
									if ( $is_deleted != '' && $is_deleted == 1 ) {
										$all_errors        = array( esc_html__( 'User is deleted from current site. Please Contact Administrator.', 'armember-membership' ) );
										$return['status']  = 'error';
										$return['type']    = 'message';
										$return['message'] = esc_html__( 'User is deleted from current site. Please Contact Administrator.', 'armember-membership' );
										break;
									}
								}
								global $browser_session_id;
								$browser_session_id = session_id();

								$user = wp_signon( $login_data, false );

								if ( is_wp_error( $user ) ) {

									$login_error = $user->get_error_message();
									$all_errors  = array( $login_error );
								}
								if ( is_a( $user, 'WP_User' ) ) {
									wp_set_current_user( $user->ID, $user->user_login );
									$remember = ( isset( $posted_data['rememberme'] ) && $posted_data['rememberme'] != '' ) ? true : false;
									wp_set_auth_cookie( $user->ID, $remember );

									if ( is_user_logged_in() ) {
										if ( in_array( 'administrator', $user->roles ) ) {
											$redirect_to = get_admin_url();
										} else {
											$arm_default_redirection_settings = get_option( 'arm_redirection_settings' );
											$arm_default_redirection_settings = maybe_unserialize( $arm_default_redirection_settings );
											$login_redirection_rules_options  = $arm_default_redirection_settings['login'];

											if ( isset( $login_redirection_rules_options['main_type'] ) && $login_redirection_rules_options['main_type'] == 'fixed' ) {
												if ( $login_redirection_rules_options['type'] == 'page' ) {
													$form_redirect_id = ( ! empty( $login_redirection_rules_options['page_id'] ) ) ? $login_redirection_rules_options['page_id'] : '0';
													$redirect_to      = $arm_global_settings->arm_get_permalink( '', $form_redirect_id );
												} elseif ( $login_redirection_rules_options['type'] == 'referral' ) {
													$default_redirect = ( ! empty( $login_redirection_rules_options['refferel'] ) ) ? $login_redirection_rules_options['refferel'] : ARMLITE_HOME_URL;
													$redirect_to      = ( ! empty( $referral_url ) ) ? $referral_url : $default_redirect;
												} else {
													$redirect_to = ( ! empty( $login_redirection_rules_options['url'] ) ) ? $login_redirection_rules_options['url'] : ARMLITE_HOME_URL;
												}
											} else {
												$redirect_to = ( ! empty( $login_redirection_rules_options['url'] ) ) ? $login_redirection_rules_options['url'] : ARMLITE_HOME_URL;
											}
											$redirect_to = str_replace( '{ARMCURRENTUSERNAME}', $user->data->user_login, $redirect_to );
											$redirect_to = str_replace( '{ARMCURRENTUSERID}', $user->data->ID, $redirect_to );
										}

										update_user_meta( $user->ID, 'arm_firsttime_login', 1 );
										$login_message     = $redirect_to;
										$return['status']  = 'success';
										$return['type']    = 'redirect';
										$return['message'] = $login_message;
										unset( $_SESSION['arm_restricted_page_url'] );
									}
								}
							}
							break;

						case 'lostpassword':
						case 'retrievepassword':
						case 'forgot_password':
							if ( $http_post ) {
								$fp = $this->arm_retrieve_password();

								if ( $fp && empty( $arm_lite_errors->errors ) ) {
									$rp_success_msg    = ! empty( $form_settings['message'] ) ? $form_settings['message'] : esc_html__( 'We have sent you a password reset link, Please check your mail.', 'armember-membership' );
									$return['status']  = 'success';
									$return['message'] = $rp_success_msg;
								} else {
									$all_errors = $arm_lite_errors->get_error_messages();
								}
							}
							break;

						case 'change_password':
							$currentPass = isset($_POST['current_user_pass']) ? $_POST['current_user_pass'] : '';//phpcs:ignore
							$newPass    = isset( $_POST['user_pass'] ) ? $_POST['user_pass'] : ''; //phpcs:ignore
							$repeatPass = isset( $_POST['repeat_pass'] ) ? $_POST['repeat_pass'] : '';  //phpcs:ignore
							if ( ! empty( $newPass ) && ! empty( $repeatPass ) ) {
								if ( $newPass != $repeatPass ) {
									$err_msg    = esc_html__( 'The passwords do not match.', 'armember-membership' );
									$all_errors = array( $err_msg );
								} else {
									if ( is_user_logged_in() && !empty($currentPass) ) {
										$user = wp_get_current_user();
										if(wp_check_password( $currentPass, $user->user_pass, $user->ID ))
                                        {
											$this->arm_reset_password( $user, $newPass );
											/* Reset Auth Cookies */
											wp_cache_delete( $user->ID, 'users' );
											wp_cache_delete( $user->user_login, 'userlogins' );
											global $arm_is_change_password_form_for_logout, $arm_is_change_password_form_for_login;

											$arm_is_change_password_form_for_login  = 1;
											$arm_is_change_password_form_for_logout = 1;

											wp_logout();
											wp_signon(
												array(
													'user_login' => $user->user_login,
													'user_password' => $newPass,
												),
												false
											);
											$arm_global_settings->arm_mailer( $arm_email_settings->templates->change_password_user, $user->ID );
											$cp_success_msg      = ! empty( $form_settings['message'] ) ? $form_settings['message'] : esc_html__( 'Your password has been changed.', 'armember-membership' );
											$return['status']    = 'success';
											$return['message']   = $cp_success_msg;
											$return['is_action'] = '';
										}
										else
										{

										$all_errors = array( $current_password_invalid_field );

										}
									} elseif ( isset( $_POST['key2'] ) && isset( $_POST['action2'] ) && $_POST['action2'] == 'armrp' && isset( $_POST['login2'] ) && ! empty( $_POST['login2'] ) && ! empty( $_REQUEST['armrpkey'] ) && ! empty( $_REQUEST['armrplogin'] ) ) { //phpcs:ignore

										$armrpkey   = esc_html( sanitize_text_field( $_REQUEST['armrpkey'] ) ); //phpcs:ignore
										$armrplogin = sanitize_user( $_REQUEST['armrplogin'] ); //phpcs:ignore

										$arm_check_user_reset_obj = check_password_reset_key( $armrpkey, $armrplogin );
										if ( ! $arm_check_user_reset_obj || is_wp_error( $arm_check_user_reset_obj ) ) {

											if ( $arm_check_user_reset_obj && $arm_check_user_reset_obj->get_error_code() === 'expired_key' ) {
												$err_msg    = ( ! empty( $arm_global_settings->common_message['arm_password_reset_pwd_link_expired'] ) ) ? $arm_global_settings->common_message['arm_password_reset_pwd_link_expired'] : esc_html__( 'Reset Password Link is expired.', 'armember-membership' );
												$all_errors = array( $err_msg );
											} else {
												$err_msg    = ( ! empty( $arm_global_settings->common_message['arm_password_reset_pwd_link_expired'] ) ) ? $arm_global_settings->common_message['arm_password_reset_pwd_link_expired'] : esc_html__( 'Reset Password Link is invalid.', 'armember-membership' );
												$all_errors = array( $err_msg );
											}
										} else {
											// $user = get_user_by('login', sanitize_text_field($_POST['login2']));
											if ( isset( $arm_check_user_reset_obj ) && ! empty( $arm_check_user_reset_obj ) && ! empty( $arm_check_user_reset_obj->ID ) ) {
												$this->arm_reset_password( $arm_check_user_reset_obj, $newPass );
												update_user_meta( $arm_check_user_reset_obj->ID, 'arm_reset_password_key', '' );

												$login_page_id = isset( $arm_global_settings->global_settings['login_page_id'] ) ? $arm_global_settings->global_settings['login_page_id'] : 0;
												if ( $login_page_id == 0 ) {
													$rp_link = wp_login_url();
												} else {

													$arm_login_page_url = $arm_global_settings->arm_get_permalink( '', $login_page_id );
													$rp_link            = $arm_login_page_url;
												}

												$err_msg = isset($arm_global_settings->common_message['arm_password_reset']) ? $arm_global_settings->common_message['arm_password_reset'] : '';
                                                $loginlink = "<a href='" . $rp_link . "'>";
                                                $login_link_taxt = isset($arm_global_settings->common_message['arm_password_reset_loginlink']) ? $arm_global_settings->common_message['arm_password_reset_loginlink'] : esc_html__('Login Now', 'armember-membership');

                                                $err_msg = (!empty($err_msg)) ? $err_msg : esc_html__('Password Reset Successfully!', 'armember-membership') . ' [SUBTITLE]' . esc_html__('Your Password has been reset, Login now and get started', 'armember-membership') . ' [/SUBTITLE]';
                                                $err_msg = str_replace("[LOGINLINK]", $loginlink, $err_msg);
                                                $err_msg = str_replace("[/LOGINLINK]", "</a>", $err_msg);
                                                $err_msg = explode('[SUBTITLE]', $err_msg);

                                                $title = !empty($err_msg[0]) ? $err_msg[0] : '';
                                                $subtitle = !empty($err_msg[1]) ? str_replace("[/SUBTITLE]", "", $err_msg[1]) : '';
                                                $err_msg = array(
                                                    'title' => $title,
                                                    'sub_title' => $subtitle,
                                                    'login_link' => $login_link_taxt
                                                );
												$cp_success_msg      = esc_html__( 'Your password has been reset.', 'armember-membership' );
												$return['status']    = 'success';
												$return['message']   = $err_msg;
												$return['is_action'] = 'armrp';
											} else {
												$err_msg    = esc_html__( 'User does not exists.', 'armember-membership' );
												$all_errors = array( $err_msg );
											}
										}
									}
								}
							}
							break;
						default:
							break;
					}
				}
				if ( ! empty( $all_errors ) && $all_errors !== true ) {
					$return['status']  = 'error';
					$return['type']    = 'message';
					$return['message'] = '<div class="arm-df__fc--validation__wrap"><ul>';
					foreach ( $all_errors as $err ) {
						$return['message'] .= '<li>' . $err . '<i class="armfa armfa-times"></i></li>';
					}
					$return['message'] .= '</ul></div>';
				} else {
					$return['status'] = 'success';
					if ( isset( $return['type'] ) && $return['type'] == 'redirect' ) {

						$return['message'] = $return['message'];
					} else {
						if (isset($return['is_action']) && $return['is_action'] == 'armrp') {
							$form_id = $armform->ID;
							$form_style_setting = $form_settings['style'];
							$button_font_color = !empty($form_style_setting['button_font_color']) ? $form_style_setting['button_font_color'] : '#ffffff' ;
							$button_hover_font_color = !empty($form_style_setting['button_hover_font_color']) ? $form_style_setting['button_hover_font_color'] : '#ffffff' ;
							$arm_reset_pass_msg_style = '';
							$arm_reset_pass_msg_style .= ".arm_form_".$form_id." .arm_reset_password_success_msg_container .arm_reset_password_title {font-family:".$form_style_setting['form_title_font_family'].";font-size:".$form_style_setting['form_title_font_size']."px;color:".$form_style_setting['form_title_font_color'].";} .arm_form_".$form_id." .arm_reset_password_success_msg_container .arm_reset_passowrd_description {font-family:".$form_style_setting['label_font_family'].";font-size:".$form_style_setting['description_font_size']."px;text-align:center;color: ".$form_style_setting['lable_font_color'].";} .arm_form_".$form_id." .arm_reset_password_success_msg_container .arm_reset_password_login_btn {display: inline-block;color:".$button_font_color."} .arm_form_".$form_id." .arm_reset_password_success_msg_container .arm_reset_password_login_btn:hover{color:".$button_hover_font_color."} .arm_form_".$form_id." .arm_reset_password_success_msg_container a{color:".$form_style_setting['login_link_font_color'].";}";
							$arm_reset_password_title = (!empty($return['message']['title'])) ? $return['message']['title'] : '';
							$arm_reset_password_subtitle = (!empty($return['message']['sub_title'])) ? $return['message']['sub_title'] : '';
							$arm_reset_password_login_link = (!empty($return['message']['login_link'])) ? $return['message']['login_link'] : '';
							$arm_reset_password_msg = '';
							
							$arm_reset_password_img = MEMBERSHIPLITE_IMAGES_URL . "/arm_reset_password_img.png";

							$arm_reset_password_msg .= '<style id="arm_reset_password_success_msg_style">'.$arm_reset_pass_msg_style.'</style>';
							$arm_reset_password_msg .= '<div class="arm_reset_password_success_msg_container">';
							$arm_reset_password_msg .= '<div class="arm_reset_password_title">'.$arm_reset_password_title;
							$arm_reset_password_msg .= '<div class="arm_reset_passowrd_description">'.$arm_reset_password_subtitle.'</div>';
							$arm_reset_password_msg .= '</div>';
							$arm_reset_password_msg .= '<img class="arm_reset_password_img" src="' . esc_url( $arm_reset_password_img ) . '" alt="'. esc_html__('Reset Password', 'armember-membership') .'"><a class="arm_reset_password_login_btn" href="'. $rp_link .'">'.$arm_reset_password_login_link.'</a>'; //phpcs:ignore
							$arm_reset_password_msg .= '</div>';
							$return['message'] = $arm_reset_password_msg;
						} else {
							$return['type']      = 'message';
							$return['message']   = '<div class="arm_success_msg"><ul><li>' . $return['message'] . '</li></ul></div>';
							$return['is_action'] = isset( $return['is_action'] ) ? $return['is_action'] : '';
						}
					}
				}
				do_action( 'arm_after_form_submit_action', $armform, $posted_data );
			}

			echo wp_json_encode( $return );
			exit;
		}

		function verify_fileupload_form_fields($posted_data)
        {
            global $ARMemberLite;
            $ARMemberLite->arm_session_start();
			
			$arm_file_upload_arr = !empty($_SESSION['arm_file_upload_arr']) ? $_SESSION['arm_file_upload_arr'] : array(); //phpcs:ignore

			if(!empty($arm_file_upload_arr)){
				foreach ($arm_file_upload_arr as $upload_key => $upload_img) {
					if( isset( $posted_data[$upload_key] ) )
					{
						$base_name = $ARMemberLite->arm_get_basename($posted_data[$upload_key]);
						if($arm_file_upload_arr[$upload_key]!=$base_name){
							unset($posted_data[$upload_key]);
						}
					}
				}
			}
            else {
                $presetFormFields = get_option('arm_preset_form_fields', '');
                $dbFormFields = maybe_unserialize($presetFormFields);
                $armember_form_custom_fields = isset($dbFormFields['other']) ? $dbFormFields['other'] : array();
                if(!empty($armember_form_custom_fields))
                {
                    foreach($posted_data as $posted_field_key => $posted_field_val)
                    {
                        if( !empty( $armember_form_custom_fields[$posted_field_key] ) && $armember_form_custom_fields[$posted_field_key]['type']=='file' )
                        {
                            unset( $posted_data[$posted_field_key] );
                        }
                    }
                }
            }
            return $posted_data;
        }

		function arm_member_validate_meta_details( $armform, $posted_data = array(), $is_validate_form_field = 1 ) {
			global $wp, $wpdb, $current_user, $ARMemberLite, $arm_members_class, $arm_global_settings, $arm_case_types;
			$return = true;
			if ( ! empty( $posted_data ) && is_object( $armform ) && ! empty( $armform->ID ) ) {

				$common_messages = $arm_global_settings->arm_get_all_common_message_settings();
				/* Check Spam Filters */
				$formRandomKey = isset( $posted_data['form_random_key'] ) ? sanitize_text_field( $posted_data['form_random_key'] ) : '';
				$validate      = true;
				$is_check_spam = true;

				
				$validate = apply_filters( 'armember_validate_spam_filter_fields', $validate, $formRandomKey );
				
				if ( ! $validate ) {
					$return         = array();
					$err_msg        = isset( $arm_global_settings->common_message['arm_spam_msg'] ) ? $arm_global_settings->common_message['arm_spam_msg'] : '';
					$return['spam'] = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'Spam detected', 'armember-membership' );
				} else {
					$block_list = $arm_global_settings->block_settings;
					$form_type  = $armform->type;

					$is_hide_username  = 0;
					$is_hide_firstname = 0;
					$is_hide_lastname  = 0;
					$invalid_username  = '';
					$invalid_email     = '';
					if ( ! empty( $armform->fields ) && !empty( $is_validate_form_field ) ) {
						foreach ( $armform->fields as $field ) {
							$form_field_option = $field['arm_form_field_option'];
							$field_name        = ( ! empty( $form_field_option['meta_key'] ) ) ? $form_field_option['meta_key'] : $form_field_option['id'];

							if ( $field_name == 'user_login' ) {
								if ( isset( $form_field_option['hide_username'] ) ) {
									$is_hide_username = $form_field_option['hide_username'];
								}
								if ( isset( $form_field_option['invalid_username'] ) ) {
									$invalid_username = $form_field_option['invalid_username'];
								}
							} elseif ( $field_name == 'first_name' ) {
								if ( isset( $form_field_option['hide_firstname'] ) ) {
									$is_hide_firstname = $form_field_option['hide_firstname'];
								}
							} elseif ( $field_name == 'last_name' ) {
								if ( isset( $form_field_option['hide_lastname'] ) ) {
									$is_hide_lastname = $form_field_option['hide_lastname'];
								}
							} elseif ( $field_name == 'user_email' ) {
								if ( isset( $form_field_option['invalid_message'] ) ) {
									$invalid_email = $form_field_option['invalid_message'];
								}
							}
							if ( isset( $posted_data[ $field_name ] ) && isset( $form_field_option['required'] ) && $form_field_option['required'] == 1 ) {
								if ( empty( $posted_data[ $field_name ] ) && $posted_data[ $field_name ] == '' ) {
									if ( ( $field_name == 'user_pass' || $field_name == 'repeat_pass' ) && $posted_data['form_type'] == 'edit_profile' ) {
										continue;
									} elseif ( $field_name == 'first_name' && $is_hide_firstname == 1 ) {
										continue;
									} elseif ( $field_name == 'last_name' && $is_hide_lastname == 1 ) {
										continue;
									}
									if ( $field_name == 'current_user_pass' && $posted_data['form_type'] == 'change_password' ) {
										continue;
									}

									$blank_message         = ( ! empty( $form_field_option['blank_message'] ) ) ? $form_field_option['blank_message'] : $form_field_option['label'] . ' can not be left blank';
									$errors[ $field_name ] = $blank_message;
								} elseif ( $form_field_option['type'] == 'email' && ( $form_field_option['required'] != 0 ) ) {
									/* Input Type Email Validation */
									if ( ! is_email( $posted_data[ $field_name ] ) ) {
										$invalid_message       = ( ! empty( $form_field_option['invalid_message'] ) ) ? $form_field_option['invalid_message'] : $form_field_option['label'] . ' is not valid';
										$errors[ $field_name ] = $invalid_message;
									}
								}
							}
							if ( in_array( $form_type, array( 'registration' ) ) && !is_user_logged_in() ) {
								if ( $field_name == 'user_login' && $is_hide_username == 0 ) {
									$sanitized_user_login = sanitize_user( $posted_data['user_login'] );
									/* Check Abusive Words In Username */
									$bad_usernames = ( isset( $block_list['arm_block_usernames'] ) ) ? $block_list['arm_block_usernames'] : array();
									if ( !empty( $bad_usernames ) && preg_match_all( '/(' . implode( '|', $bad_usernames ) . ')/i', $sanitized_user_login, $matches ) > 0 ) {
										$bad_username_msg      = ! empty( $block_list['arm_block_usernames_msg'] ) ? $block_list['arm_block_usernames_msg'] : esc_html__( 'Username should not contain bad words.', 'armember-membership' );
										$errors[ $field_name ] = $bad_username_msg;
									} else {
										$chk_user_login = $arm_members_class->arm_validate_username( $sanitized_user_login, $invalid_username );
										/* Check the username */
										if ( ! empty( $chk_user_login ) ) {
											$errors[ $field_name ] = $chk_user_login;
										}
									}
								}
								if ( $field_name == 'user_email' ) {
									$user_email = apply_filters( 'user_registration_email', $posted_data['user_email'] );
									/* Check Abusive Words In Email Address */
									$bad_emails = ( isset( $block_list['arm_block_emails'] ) ) ? $block_list['arm_block_emails'] : array();
									if ( ! empty( $bad_emails ) && preg_match_all( '/(' . implode( '|', $bad_emails ) . ')/i', $user_email, $matches ) > 0 ) {
										$bad_email_msg         = ! empty( $block_list['arm_block_emails_msg'] ) ? $block_list['arm_block_emails_msg'] : esc_html__( 'Email should not contain bad words.', 'armember-membership' );
										$errors[ $field_name ] = $bad_email_msg;
									} else {
										$chk_user_email = $arm_members_class->arm_validate_email( $user_email, $invalid_email );
										if ( ! empty( $chk_user_email ) ) {
											$errors[ $field_name ] = $chk_user_email;
										}
									}
								}
							} elseif ( in_array( $form_type, array( 'edit_profile', 'update_profile' ) ) ) {
								$member_id    = get_current_user_id();
								$current_user = get_userdata( $member_id );
								if ( $field_name == 'user_email' ) {
									$user_email = apply_filters( 'user_registration_email', $posted_data['user_email'] );
									if ( strtolower( $user_email ) != strtolower( $current_user->user_email ) ) {
										$bad_emails = ( isset( $block_list['arm_block_emails'] ) ) ? $block_list['arm_block_emails'] : array();
										if ( ! empty( $bad_emails ) && preg_match_all( '/(' . implode( '|', $bad_emails ) . ')/i', $user_email, $matches ) > 0 ) {
											$bad_email_msg         = ! empty( $block_list['arm_block_emails_msg'] ) ? $block_list['arm_block_emails_msg'] : esc_html__( 'Email should not contain bad words.', 'armember-membership' );
											$errors[ $field_name ] = $bad_email_msg;
										} else {
											$chk_user_email = $arm_members_class->arm_validate_email( $user_email, $invalid_email );
											if ( ! empty( $chk_user_email ) ) {
												$errors[ $field_name ] = $chk_user_email;
											}
										}
									}
								}
							} elseif ( in_array( $form_type, array( 'login' ) ) ) {
							}
							/* Check if there is file upload */
							if ( $form_field_option['type'] == 'file' || $form_field_option['type'] == 'avatar' || 'profile_cover' == $form_field_option['type'] ) {
								$phpFileUploadErrors = array(
									0 => esc_html__( 'There is no error, the file uploaded with success.', 'armember-membership' ),
									1 => esc_html__( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'armember-membership' ),
									2 => esc_html__( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'armember-membership' ),
									3 => esc_html__( 'The uploaded file was only partially uploaded.', 'armember-membership' ),
									4 => esc_html__( 'No file was uploaded.', 'armember-membership' ),
									6 => esc_html__( 'Missing a temporary folder.', 'armember-membership' ),
									7 => esc_html__( 'Failed to write file to disk.', 'armember-membership' ),
									8 => esc_html__( 'A PHP extension stopped the file upload.', 'armember-membership' ),
								);
								
								if ( isset( $_FILES[ $field_name ] ) && ( !empty($_FILES[ $field_name ]['error']) && $_FILES[ $field_name ]['error'] === UPLOAD_ERR_OK ) ) { //phpcs:ignore
									$uploads = wp_upload_dir();
									if ( false !== $uploads['error'] ) {
										$errors['uploads_error'] = $uploads['error'];
									}
									/* Valid File. */
									if ( $form_field_option['type'] == 'avatar' ) {
										$allow_ext = '.jpg,.jpeg,.png,.bmp';
									} else {
										$allow_ext = $form_field_option['allow_ext'];
									}
									if ( ! empty( $allow_ext ) ) {
										$allowed_ext    = explode( ',', $allow_ext );
										$file_extension = explode( '.', sanitize_file_name($_FILES[ $field_name ]['name']) ); //phpcs:ignore
										$extension      = $file_extension[ count( $file_extension ) - 1 ];
										if ( ! in_array( $extension, $allowed_ext ) ) {
											$errors[ $field_name ] = esc_html__( 'File type is not allowed.', 'armember-membership' );
										}
									}
								} else {
									if ( ! empty( $form_field_option['required'] ) && $form_field_option['required'] == 1 ) {
										if ( empty( $posted_data[ $field_name ] ) && $posted_data[ $field_name ] == '' ) {
											$blank_message         = ( ! empty( $form_field_option['blank_message'] ) ) ? $form_field_option['blank_message'] : esc_html__( 'Please upload file.', 'armember-membership' );
											$errors[ $field_name ] = $blank_message;
										}
									}
								}
							}
						}
					}
					if ( ! empty( $errors ) ) {
						$return = array();
						$return = $errors;
					}
				}
			}
			$return = apply_filters( 'arm_validate_field_value_before_form_submission', $return, $armform, $posted_data );
			return $return;
		}

		/**
		 * Register New User.
		 */
		function arm_register_new_member( $posted_data = array(), $armform = null, $social_signup = '' ) {
			global $wp, $wpdb, $current_user, $arm_lite_errors, $ARMemberLite, $arm_members_class, $arm_global_settings, $arm_subscription_plans, $payment_done, $arm_email_settings, $arm_login_from_registration, $arm_manage_communication;
			$arm_lite_errors = new WP_Error();

			$posted_data = apply_filters( 'arm_before_member_register', $posted_data );
			$user_login  = ( isset( $posted_data['user_login'] ) && ! empty( $posted_data['user_login'] ) ) ? $posted_data['user_login'] : $posted_data['user_email'];
			$user_email  = ( isset( $posted_data['user_email'] ) ) ? $posted_data['user_email'] : '';

			if ( $social_signup == 'social_signup' ) {
				$user_pass = wp_generate_password();
			} else {
				$user_pass = ( isset( $posted_data['user_pass'] ) ) ? $posted_data['user_pass'] : '';
			}

			/* Check the e-mail address */
			$user_email     = apply_filters( 'user_registration_email', $user_email );
			$chk_user_email = $arm_members_class->arm_validate_email( $user_email );
			if ( ! empty( $chk_user_email ) ) {
				$arm_lite_errors->add( 'arm_reg_error', $chk_user_email );
				$user_email = '';
			}

			$sanitized_user_login = sanitize_user( $user_login );
			$chk_user_login       = $arm_members_class->arm_validate_username( $user_login );
			/* Check the username */
			if ( ! empty( $chk_user_login ) ) {
				$arm_lite_errors->add( 'arm_reg_error', $chk_user_login );
				$sanitized_user_login = '';
			}
			/* Check Member password */
			if ( empty( $user_pass ) ) {
				$user_pass = apply_filters( 'arm_member_registration_pass', wp_generate_password( 12, false ) );
			}

			do_action( 'register_post', $sanitized_user_login, $user_email, $arm_lite_errors );
			remove_all_filters( 'registration_errors' );
			$arm_lite_errors = apply_filters( 'registration_errors', $arm_lite_errors, $sanitized_user_login, $user_email );

			do_action( 'arm_remove_third_party_error', $arm_lite_errors );

			if ( ! empty( $arm_lite_errors ) ) {
				if ( $arm_lite_errors->get_error_code() ) {
					return $arm_lite_errors;
				}
			}

			$user_id = wp_create_user( $sanitized_user_login, $user_pass, $user_email );
			if ( ! $user_id ) {
				$link_tag = '<a href="mailto:' . get_option( 'admin_email' ) . '">' . esc_html__( 'webmaster', 'armember-membership' ) . '</a>';
				$err_msg  = isset( $arm_global_settings->common_message['arm_user_not_created'] ) ? $arm_global_settings->common_message['arm_user_not_created'] : '';
				$err_msg  = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( "Couldn't register you... please contact the", 'armember-membership' ) . ' ' . $link_tag;
				$arm_lite_errors->add( 'arm_reg_error', $err_msg );
				return $arm_lite_errors;
			}
			$update_data['ID']         = $user_id;
			$update_data['user_email'] = $user_email;
			if ( ! empty( $posted_data['user_nicename'] ) ) {
				$update_data['user_nicename'] = sanitize_text_field( $posted_data['user_nicename'] );
			}
			if ( ! empty( $posted_data['user_url'] ) ) {
				$update_data['user_url'] = sanitize_text_field( $posted_data['user_url'] );
			}
			$display_name              = isset( $posted_data['display_name'] ) ? sanitize_text_field( $posted_data['display_name'] ) : '';
			$posted_data['first_name'] = isset( $posted_data['first_name'] ) ? trim( sanitize_text_field( $posted_data['first_name'] ) ) : '';
			$posted_data['last_name']  = isset( $posted_data['last_name'] ) ? trim( sanitize_text_field( $posted_data['last_name'] ) ) : '';
			if ( empty( $display_name ) ) {
				if ( $posted_data['first_name'] && $posted_data['last_name'] ) {
					/* translators: 1: first name, 2: last name */
					$display_name = $posted_data['first_name'] . ' ' . $posted_data['last_name'];
				} elseif ( $posted_data['first_name'] ) {
					$display_name = $posted_data['first_name'];
				} elseif ( $posted_data['last_name'] ) {
					$display_name = $posted_data['last_name'];
				} else {
					$display_name = $user_login;
				}
			}
			$update_data['display_name'] = $display_name;
			$pgateway                    = isset( $posted_data['payment_gateway'] ) ? sanitize_text_field( $posted_data['payment_gateway'] ) : '';

			if ( $pgateway == '' ) {
				$pgateway = isset( $posted_data['_payment_gateway'] ) ? sanitize_text_field( $posted_data['_payment_gateway'] ) : '';
			}

			$user_id = wp_update_user( $update_data );
			/* Set Member Status */
			$new_member_status = $arm_global_settings->arm_get_single_global_settings( 'arm_new_signup_status', 1 );
			arm_set_member_status( $user_id, $new_member_status );
			/* Store User Meta Data */
			$admin_save_flag = 0;
			do_action( 'arm_member_update_meta', $user_id, $posted_data, $admin_save_flag );

			$wpdb->update( $ARMemberLite->tbl_arm_members, array( 'arm_user_type' => 1 ), array( 'arm_user_id' => $user_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$userData = array(
				'firstname' => $posted_data['first_name'],
				'lastname'  => $posted_data['last_name'],
				'email'     => $user_email,
			);

			/**
			 * Add Registration Activity Log.
			 */
			$plan_ID = isset( $posted_data['subscription_plan'] ) ? intval( $posted_data['subscription_plan'] ) : 0;
			if ( $plan_ID == 0 ) {
				$plan_ID = isset( $posted_data['_subscription_plan'] ) ? intval( $posted_data['_subscription_plan'] ) : 0;
			}

			$register_activity = array(
				'user_id' => $user_id,
				'type'    => 'register',
				'item_id' => $plan_ID,
			);
			do_action( 'arm_record_activity', $register_activity );
			/* Send User Notification */
			arm_new_user_notification( $user_id, $user_pass );
			if ( $pgateway != 'bank_transfer' && $plan_ID > 0 ) {
				/**
				 * Send Email Notification for Successful Payment
				 */
				$arm_manage_communication->arm_user_plan_status_action_mail(
					array(
						'plan_id' => $plan_ID,
						'user_id' => $user_id,
						'action'  => 'new_subscription',
					)
				);
			}
			/* Login new user if form option is enable */
			if ( $armform != null ) {
				$form_settings = $armform->settings;
				$member_status = arm_get_member_status( $user_id );
				$is_free_plan  = $arm_subscription_plans->isFreePlanExist( $plan_ID );
				// $user_pending_pgway = array('bank_transfer', 'paypal');
				$user_pending_pgway = array( 'paypal' );
				$user_pending_pgway = apply_filters( 'arm_change_pending_gateway_outside', $user_pending_pgway, $plan_ID, $user_id );
				if ( ( isset( $form_settings['auto_login'] ) && $form_settings['auto_login'] == '1' ) && $member_status == '1' && ( ! in_array( $pgateway, $user_pending_pgway ) || $is_free_plan ) ) {
					wp_set_auth_cookie( $user_id );
					wp_set_current_user( $user_id, $user_login );
					update_user_meta( $user_id, 'arm_last_login_date', current_time( 'mysql' ) );
					$ip_address = $ARMemberLite->arm_get_ip_address();
					update_user_meta( $user_id, 'arm_last_login_ip', $ip_address );
					$user_to_pass                = wp_get_current_user();
					$arm_login_from_registration = 1;
					do_action( 'wp_login', $user_id, $user_to_pass );
				}
			}

			/* move this action to default in switch case above */

			/* For affiliateWP insert referral */
			$posted_data['arform_object'] = $armform;
			$posted_data['user_data']     = $userData;
			do_action( 'arm_after_add_new_user', $user_id, $posted_data );

			return $user_id;
		}

		/**
		 * Update Member Details.
		 */
		function arm_update_member_profile( $posted_data = array() ) {

			global $wp, $wpdb, $current_user, $arm_lite_errors, $ARMemberLite, $arm_members_class, $arm_global_settings, $arm_email_settings;
			$arm_lite_errors = new WP_Error();

			$user_ID = get_current_user_id();
			if ( is_user_logged_in() ) {
				$current_user = get_userdata( $user_ID );
				$user_login   = isset( $posted_data['user_login'] ) ? sanitize_text_field( $posted_data['user_login'] ) : '';
				unset( $posted_data['user_login'] );
				$user_email  = sanitize_email( $posted_data['user_email'] );
				$user_email  = apply_filters( 'user_registration_email', $posted_data['user_email'] );
				$update_data = array(
					'ID' => $user_ID,
					// 'user_email' => $user_email
				);

				if ( ! empty( $user_email ) ) {
					$update_data['user_email'] = $user_email;
				}

				if ( isset( $posted_data['user_pass'] ) && ! empty( $posted_data['user_pass'] ) ) {
					$update_data['user_pass'] = $posted_data['user_pass'];
				}

				/* Check the e-mail address */
				if ( ! empty( $user_email ) && strtolower( $user_email ) != strtolower( $current_user->user_email ) ) {

					$chk_user_email = $arm_members_class->arm_validate_email( $user_email );
					if ( ! empty( $chk_user_email ) ) {
						$arm_lite_errors->add( 'arm_profile_error', $chk_user_email );
						unset( $update_data['user_email'] );
					}
				}

				if ( $arm_lite_errors->get_error_code() ) {
					return $arm_lite_errors;
				}

				if ( isset( $posted_data['user_url'] ) ) {
					$update_data['user_url'] = sanitize_text_field( $posted_data['user_url'] );
				}
				$display_name = isset( $posted_data['display_name'] ) ? sanitize_text_field( $posted_data['display_name'] ) : '';
				if ( isset( $posted_data['first_name'] ) ) {
					$posted_data['first_name'] = trim( sanitize_text_field( $posted_data['first_name'] ) );
				}
				if ( isset( $posted_data['last_name'] ) ) {
					$posted_data['last_name'] = trim( sanitize_text_field( $posted_data['last_name'] ) );
				}

				if ( empty( $display_name ) ) {
					if ( $posted_data['first_name'] && $posted_data['last_name'] ) {
						/* translators: 1: first name, 2: last name */
						$display_name = $posted_data['first_name'] . ' ' . $posted_data['last_name'];
					} elseif ( $posted_data['first_name'] ) {
						$display_name = $posted_data['first_name'];
					} elseif ( $posted_data['last_name'] ) {
						$display_name = $posted_data['last_name'];
					} else {
						$display_name = $user_login;
					}
				}
				$update_data['display_name'] = $display_name;
				global $arm_is_update_password_form_edit_profile_login, $arm_is_update_password_form_edit_profile_logout;

				$arm_is_update_password_form_edit_profile_logout = 1;
				$arm_is_update_password_form_edit_profile_login  = 1;

				$user_ID = wp_update_user( $update_data );
				/* For updating username */
				if ( is_wp_error( $user_ID ) ) {
					/* There was an error, probably that user doesn't exist. */
					$err_msg = isset( $arm_global_settings->common_message['arm_user_not_exist'] ) ? $arm_global_settings->common_message['arm_user_not_exist'] : '';
					$err_msg = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( "User doesn't exist.", 'armember-membership' );
					$arm_lite_errors->add( 'arm_profile_error', $err_msg );
					return $arm_lite_errors;
				}
				$admin_save_flag = 0;
				do_action( 'arm_member_update_meta', $user_ID, $posted_data, $admin_save_flag );
							  /**
				 * Add Update Profile Activity Log.
				 */
				$edit_profile_activity = array(
					'user_id' => $user_ID,
					'type'    => 'update_profile',
				);
				do_action( 'arm_record_activity', $edit_profile_activity );
				/* Send User Notification */
				wp_update_user_notification( $user_ID, $posted_data );
				if ( isset( $posted_data['user_pass'] ) && ! empty( $posted_data['user_pass'] ) ) {
					if ( ! wp_check_password( $posted_data['user_pass'], $current_user->user_pass, $user_ID ) ) {
						$arm_global_settings->arm_mailer( $arm_email_settings->templates->change_password_user, $user_ID );
					}
				}
			} else {
				$user_ID = 0;
			}
			return $user_ID;
		}

		function arm_member_update_meta_details( $user_ID, $posted_data = array(), $admin_save_flag = 0 ) {
			global $wp, $wpdb, $current_user, $arm_lite_errors, $ARMemberLite, $arm_subscription_plans, $payment_done, $arm_members_class;
			$arm_lite_errors = new WP_Error();

			$posted_data     = apply_filters( 'arm_change_user_meta_before_save', $posted_data, $user_ID,$admin_save_flag );
			$payment_gateway = isset( $posted_data['pgateway'] ) ? sanitize_text_field( $posted_data['pgateway'] ) : '';
			$start_time      = isset( $posted_data['start_time'] ) ? sanitize_text_field( $posted_data['start_time'] ) : '';
			$plan_cycle      = isset( $posted_data['arm_selected_payment_cycle'] ) ? sanitize_text_field( $posted_data['arm_selected_payment_cycle'] ) : 0;
			 /* Unset default member fields. */
			$action      = isset( $posted_data['action'] ) ? sanitize_text_field( $posted_data['action'] ) : '';
			$unser_array = array(
				'id',
				'form',
				'user_login',
				'user_email',
				'repeat_email',
				'user_pass',
				'password',
				'repeat_pass',
				'user_url',
				'display_name',
				'isAdmin',
				'action',
				'redirect_to',
				'arm_action',
				'page_id',
				'form_filter_kp',
				'form_filter_st',
				'nonce_check',
				'arm_plan_type',
				'armFormSubmitBtn',
				'arm_subscription_start_date',
				'arm_update_user_from_profile',
				'arm_total_payable_amount',
				'arm_front_gateway_skin_type',
				'arm_front_plan_skin_type',
				'arm_user_selected_payment_mode',
				'start_time',
				'arm_user_old_plan',
				'arm_is_user_logged_in_flag',
				'pgateway',
				'arm_user_payment_mode',
				'arm_payment_mode',
				'arm_selected_payment_mode',
				'arm_selected_payment_cycle',
			);

			foreach ( $unser_array as $key ) {
				if ( isset( $posted_data[ $key ] ) ) {
					unset( $posted_data[ $key ] );
				}
			}
			if(empty($admin_save_flag))
			{
				$form_id = 0;
				$form_random_key = !empty($posted_data['form_random_key']) ? sanitize_text_field($posted_data['form_random_key']) : '';
				$arm_setup_id = !empty($posted_data['setup_id']) ? intval($posted_data['setup_id']) : 0;
				$arm_setup_action = !empty($_POST['setup_action']) ? sanitize_text_field($_POST['setup_action']) : 'membership_setup'; //phpcs:ignore
				
				if(!empty($form_random_key))
				{
					$form_id_arr = explode('_', $form_random_key);
					$form_id = !empty($form_id_arr[0]) ? intval($form_id_arr[0]) : 0;
				}
				if (!empty($arm_setup_id) && "membership_setup"== $arm_setup_action)
				{
					$get_arm_setup_modules = $wpdb->get_var( $wpdb->prepare( "SELECT `arm_setup_modules` FROM `" . $ARMemberLite->tbl_arm_membership_setup . "` WHERE `arm_setup_id` = %d", $arm_setup_id ) ); //phpcs:ignore --Reason: $ARMemberLite->tbl_arm_membership_setup is a table name
					if(!empty($get_arm_setup_modules))
					{
						$get_arm_setup_modules = maybe_unserialize($get_arm_setup_modules);
						$form_id = isset($get_arm_setup_modules['modules']['forms']) ? $get_arm_setup_modules['modules']['forms'] : 0;
					}
				}
				
				if(empty($form_id))
				{
					return;
				}

				if(is_user_logged_in())
				{
					$ARMemberLite->arm_session_start();
				}
				
				$arm_form_fields_arr = $wpdb->get_results( $wpdb->prepare('SELECT `arm_form_field_option`,`arm_form_field_slug` FROM `' . $ARMemberLite->tbl_arm_form_field . "` WHERE arm_form_field_form_id = %d ", $form_id), ARRAY_A ); //phpcs:ignore --Reason: $ARMemberLite->tbl_arm_form_fieldis a table name

				$arm_form_field_slug_array = $arm_form_field_option_array = array();
				if(!empty($arm_form_fields_arr) && is_array($arm_form_fields_arr))
				{
					foreach($arm_form_fields_arr as $arm_form_fields_check_key => $arm_form_fields_check_val)
					{
						$arm_form_fields_check_val_option = !empty($arm_form_fields_check_val['arm_form_field_option']) ? maybe_unserialize($arm_form_fields_check_val['arm_form_field_option']) : array();
						$arm_form_field_slug = !empty($arm_form_fields_check_val['arm_form_field_slug']) ? $arm_form_fields_check_val['arm_form_field_slug'] : '';
						if(!empty($arm_form_field_slug) && $arm_form_field_slug == 'social_fields')
						{
							$arm_form_fields_check_val_option_field_options = !empty($arm_form_fields_check_val_option['options']) ? $arm_form_fields_check_val_option['options'] : array();
							foreach($arm_form_fields_check_val_option_field_options as $arm_form_fields_check_val_option_field_option)
							{
								$arm_form_field_slug_array[] = "arm_social_field_".$arm_form_fields_check_val_option_field_option;
								$arm_form_field_option_array["arm_social_field_".$arm_form_fields_check_val_option_field_option] = $arm_form_fields_check_val;
							}
						}
						else if(!empty($arm_form_field_slug))
						{
							$arm_form_field_slug_array[] = $arm_form_field_slug;
							$arm_form_field_option_array[$arm_form_field_slug] = $arm_form_fields_check_val;
                            if( $arm_form_fields_check_val_option['type'] == 'checkbox' )
                            {
                                $arm_form_field_slug_array[] = $arm_form_field_slug.'_arm_hidden';
                            }
						}
					}
					if(isset($_SESSION['arm_additional_form_fields']) && is_array($_SESSION['arm_additional_form_fields']) && isset($_SESSION['arm_additional_form_fields'][$form_id]) && is_array($_SESSION['arm_additional_form_fields'][$form_id]))
					{
						
						$arm_additional_form_fields= $_SESSION['arm_additional_form_fields']; //phpcs:ignore

						foreach($arm_additional_form_fields[$form_id] as $arm_social_profile_field)
						{
							if(!in_array($arm_social_profile_field,$arm_form_field_slug_array))
							{
								$arm_form_field_slug_array[] = $arm_social_profile_field;
							}
						}
					}
				}

				foreach( $posted_data as $posted_data_key => $posted_data_val ) {
					if ( !in_array( $posted_data_key, $arm_form_field_slug_array ) && ($posted_data_key!='arm_user_plan' && $posted_data_key!='arm_primary_status' && $posted_data_key!='arm_user_future_plan' && $posted_data_key!='arm_form_id' && $posted_data_key !='arm_entry_id' ) ) {
						unset( $posted_data[ $posted_data_key ] ); //phpcs:ignore
                        continue;
					}
					else 
					{
						$arm_form_field_option_array_field_option = !empty($arm_form_field_option_array[$posted_data_key]['arm_form_field_option']) ? maybe_unserialize($arm_form_field_option_array[$posted_data_key]['arm_form_field_option']) : array();

						if(!empty($arm_form_field_option_array_field_option))
						{
							$arm_form_field_option_array_field_option_options = !empty($arm_form_field_option_array_field_option['options']) ? $arm_form_field_option_array_field_option['options'] : array();
							$arm_form_field_option_array_field_option_type = !empty($arm_form_field_option_array_field_option['type']) ? $arm_form_field_option_array_field_option['type'] : '';
							if($posted_data_key=='roles')
							{
								if( !array_key_exists( $posted_data_val, $arm_form_field_option_array_field_option_options ) ) 
								{
									unset( $posted_data[ $posted_data_key ] );
                                    continue;
								}
							}
							else if($arm_form_field_option_array_field_option_type=='text' || $arm_form_field_option_array_field_option_type=='social_fields')
							{
								$posted_data[ $posted_data_key ] = sanitize_text_field( $posted_data[ $posted_data_key ] ); //phpcs:ignore
							}
							else if($arm_form_field_option_array_field_option_type=='textarea')
							{
								$posted_data[ $posted_data_key ] = sanitize_textarea_field( $posted_data[ $posted_data_key ] ); //phpcs:ignore
							}
							else if($arm_form_field_option_array_field_option_type=='radio' || $arm_form_field_option_array_field_option_type=='select')
							{
								$arm_val_data_with_field = 0;
								foreach($arm_form_field_option_array_field_option_options as $arm_form_field_option_array_field_option_option)
								{
									$new_arm_form_field_option_array_field_option_option = explode( ':', wp_strip_all_tags( $arm_form_field_option_array_field_option_option ) );

									if ( count( $new_arm_form_field_option_array_field_option_option ) > 1 ) {
										$arm_option_val_check = end( $new_arm_form_field_option_array_field_option_option );
									} else {
										$arm_option_val_check = $arm_form_field_option_array_field_option_option;
									}

									if(isset($posted_data[$posted_data_key]) && $arm_option_val_check == $posted_data[$posted_data_key] )  //phpcs:ignore
									{
										$posted_data[ $posted_data_key ] = $arm_option_val_check;
										$arm_val_data_with_field = 1;
										break;
									}
								}

								if(empty($arm_val_data_with_field))
								{
									unset( $posted_data[ $posted_data_key ] );
                                    continue;
								}
							}
							else if($arm_form_field_option_array_field_option_type=='checkbox')
							{
								$arm_val_data_with_field = 0;
								$posted_data_val_count = (!empty($posted_data_val) && is_array($posted_data_val)) ? count($posted_data_val) : 0;
								if(empty($posted_data_val_count) && !is_array($posted_data_val))
								{
									$posted_data_val_count = 1;
								}
								foreach($arm_form_field_option_array_field_option_options as $arm_form_field_option_array_field_option_option)
								{
									$new_arm_form_field_option_array_field_option_option = explode( ':', wp_strip_all_tags( $arm_form_field_option_array_field_option_option ) );

									if ( count( $new_arm_form_field_option_array_field_option_option ) > 1 ) {
										$arm_option_val_check = end( $new_arm_form_field_option_array_field_option_option );
									} else {
										$arm_option_val_check = $arm_form_field_option_array_field_option_option;
									}
									if( isset($posted_data[$posted_data_key]) && is_array($posted_data[$posted_data_key]) ) //phpcs:ignore
									{
										$original_posted_data_key_check_array = $posted_data[$posted_data_key]; //phpcs:ignore
										foreach($original_posted_data_key_check_array as $original_posted_data_key_check_array_key => $original_posted_data_key_check_array_val)
										{
											if($arm_option_val_check == $original_posted_data_key_check_array_val)
											{
												$posted_data[ $posted_data_key ][$original_posted_data_key_check_array_key] = $arm_option_val_check;
												$arm_val_data_with_field += 1;
												break;
											}
										}
									}
									else if(isset($posted_data[$posted_data_key]) && !is_array($posted_data[$posted_data_key]) && $arm_option_val_check == $posted_data[$posted_data_key]) //phpcs:ignore
									{
										$arm_val_data_with_field += 1;
										break;
									}
								}
								if($arm_val_data_with_field!=$posted_data_val_count)
								{
									unset( $posted_data[ $posted_data_key ] );
                                    continue;
								}
							}
						}
						else if( false !== strpos( $posted_data_key, 'arm_social_field_' ) )
						{
							$posted_data[ $posted_data_key ] = sanitize_text_field( $posted_data[ $posted_data_key ] ); //phpcs:ignore
						}
					}
				}
			}

			if ( ! empty( $user_ID ) && ! empty( $posted_data ) ) {
				$user         = new WP_User( $user_ID );
				$old_plan_ids = get_user_meta( $user_ID, 'arm_user_plan_ids', true );
				$old_plan_ids = ! empty( $old_plan_ids ) ? $old_plan_ids : array();
				$old_plan     = isset( $old_plan_ids[0] ) ? $old_plan_ids[0] : 0;
				$new_plan     = $old_plan;
				$planObj      = new ARM_Plan_Lite( $new_plan );

				foreach ( $posted_data as $key => $val ) {
					if ( $key == 'first_name' || $key == 'last_name' ) {
						$val = trim( sanitize_text_field( $val ) );
					} elseif ( $key == 'role' || $key == 'roles' ) {
						$all_plan_roles = $arm_subscription_plans->arm_get_plan_role_by_id($old_plan_ids);
						if (!empty($all_plan_roles) && is_array($all_plan_roles)) {
							foreach ($all_plan_roles as $key => $value) {
								$plan_role = $value['arm_subscription_plan_role'];
								if (!empty($plan_role)) {
									$user->remove_role($plan_role);
								}
							}
						}
                        if (isset($val) && is_array($val) && !empty($val)) {
                            $count = 0;
                            foreach ($val as $v) {
                                if ($count == 0) {
                                    $user->set_role($v);
                                } else {
                                    $user->add_role($v);
                                }
                                $count++;
                            }
                        } else {
                            $user->set_role($val);
                        }
					} elseif ( $key == 'arm_user_plan' ) {
						$primary_status = arm_get_member_status( $user_ID );

						$new_plan = sanitize_text_field( $val );
						if ( ! empty( $new_plan ) ) {
							$planObj = new ARM_Plan_Lite( $new_plan );
							if ( ! in_array( $new_plan, $old_plan_ids ) ) {

								/* Update Last Subscriptions Log Detail */
								$user->add_cap( 'armember_access_plan_' . $new_plan );

									do_action( 'arm_before_update_user_subscription', $user_ID, $new_plan );
									$user->remove_cap( 'armember_access_plan_' . $old_plan );
									delete_user_meta( $user_ID, 'arm_user_plan_' . $old_plan );
								if ( $payment_gateway != 'bank_transfer' ) {
									update_user_meta( $user_ID, 'arm_user_plan_ids', array( $new_plan ) );
									update_user_meta( $user_ID, 'arm_user_last_plan', $new_plan );
									if ( $start_time <= strtotime( current_time( 'mysql' ) ) ) {

										if ( ! empty( $planObj->plan_role ) ) {
											$user->set_role( $planObj->plan_role );
										}
									}
								}

								if ( $payment_gateway != 'bank_transfer' ) {
									$arm_subscription_plans->arm_add_membership_history( $user_ID, $new_plan, 'new_subscription' );
								}
								if ( $action == 'update_member' || $action == 'add_member' ) {
									$arm_members_class->arm_manual_update_user_data( $user_ID, $new_plan, $posted_data, $plan_cycle );
								}
							} else {
								if ( $payment_gateway != 'bank_transfer' ) {
									update_user_meta( $user_ID, 'arm_user_plan_ids', array_values( $old_plan_ids ) );

								}
							}
						} else {
							if ( ! empty( $old_plan_ids ) ) {
								foreach ( $old_plan_ids as $opid ) {
									if ( $user->has_cap( "armember_access_plan_{$opid}" ) ) {
										$user->remove_cap( "armember_access_plan_{$opid}" );
									}
									delete_user_meta( $user_ID, 'arm_user_plan_' . $opid );
								}
								$plan_id_role_array = $arm_subscription_plans->arm_get_plan_role_by_id( $old_plan_ids );
								if ( ! empty( $plan_id_role_array ) && is_array( $plan_id_role_array ) ) {
									foreach ( $plan_id_role_array as $key => $value ) {
										$plan_role = $value['arm_subscription_plan_role'];
										if ( ! empty( $plan_role ) ) {
											$user->remove_role( $plan_role );
											$arm_default_wordpress_role = get_option( 'default_role', 'subscriber' );
											$user->set_role( $arm_default_wordpress_role );
										}
									}
								}
							}
							delete_user_meta( $user_ID, 'arm_user_plan_ids' );
							delete_user_meta( $user_ID, 'arm_user_last_plan' );
						}

						if ( ! empty( $val ) ) {

							$current_user_plan_ids = get_user_meta( $user_ID, 'arm_user_plan_ids', true );
							$current_user_plan_ids = ! empty( $current_user_plan_ids ) ? $current_user_plan_ids : array();

								$user_future_plan_arrays = array();

							if ( ! empty( $start_time ) ) {
								if ( $start_time > strtotime( current_time( 'mysql' ) ) ) {
									if ( in_array( $val, $current_user_plan_ids ) ) {

										$current_user_plan_data = get_user_meta( $user_ID, 'arm_user_plan_' . $val, true );
										$is_user_in_trial       = isset( $current_user_plan_data['arm_is_trial_plan'] ) ? $current_user_plan_data['arm_is_trial_plan'] : 0;
										if ( $is_user_in_trial != 1 ) {
											unset( $current_user_plan_ids[ array_search( $val, $current_user_plan_ids ) ] );
											$user_future_plan_arrays[] = $val;
										}
									}
								}
							}
							update_user_meta( $user_ID, 'arm_user_future_plan_ids', array_values( $user_future_plan_arrays ) );
							update_user_meta( $user_ID, 'arm_user_plan_ids', array_values( $current_user_plan_ids ) );

						}

						continue;
					} elseif ( $key == 'arm_primary_status' ) {
						if ( $val == 1 ) {
							$secondary_status = 0;
						} else {
							$secondary_status = arm_get_member_status( $user_ID, 'secondary' );
						}
						arm_set_member_status( $user_ID, $val, $secondary_status );
					} elseif ( $key == 'arm_user_future_plan' ) {
						if ( ! empty( $val ) ) {
							$future_user_plan_ids = get_user_meta( $user_ID, 'arm_user_future_plan_ids', true );
							$future_user_plan_ids = ! empty( $future_user_plan_ids ) ? $future_user_plan_ids : array();
							if ( ! empty( $future_user_plan_ids ) ) {
								$common_future_plans = array_intersect( $future_user_plan_ids, $val );
								$common_future_plans = ! empty( $common_future_plans ) ? $common_future_plans : array();
								update_user_meta( $user_ID, 'arm_user_future_plan_ids', array_values( $common_future_plans ) );
							}
							$diff_future_plans = array_diff( $future_user_plan_ids, $val );
							if ( ! empty( $diff_future_plans ) ) {
								foreach ( $diff_future_plans as $diff_fp ) {
									delete_user_meta( $user_ID, 'arm_user_plan_' . $diff_fp );
								}
							}
						}
						continue;
					} else if( $key == 'arm_form_id' || $key == 'arm_entry_id' )
					{
						$val = intval($val);
					}
                    $pattern1 = '/^((.*)\_arm\_hidden)/';
                    if (preg_match($pattern1, $key)) {
                        $key = str_replace('_arm_hidden', '', $key);
                        unset($posted_data[$key]);
                    }
					update_user_meta( $user_ID, $key, $val );
				}
			}
		}

		function arm_retrieve_password() {
			global $wp, $wpdb, $wp_hasher, $current_user, $current_site, $arm_lite_errors, $ARMemberLite, $arm_email_settings, $arm_global_settings;
			$arm_lite_errors    = new WP_Error();
			$is_user_data_exist = 0;
			if ( empty( $_POST['user_login'] ) ) { //phpcs:ignore
				$err_msg = esc_html__( 'Enter a username or e-mail address.', 'armember-membership' );
				$arm_lite_errors->add( 'empty_username', $err_msg );
			} elseif ( strpos( $_POST['user_login'], '@' ) ) { //phpcs:ignore
				$user_data          = get_user_by( 'email', trim( sanitize_email( $_POST['user_login'] ) ) ); //phpcs:ignore
				$is_user_data_exist = 1;
				if ( empty( $user_data ) ) {
					$err_msg = isset( $arm_global_settings->common_message['arm_no_registered_email'] ) ? $arm_global_settings->common_message['arm_no_registered_email'] : '';
					$err_msg = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'There is no user registered with that email address.', 'armember-membership' );
					$arm_lite_errors->add( 'invalid_email', $err_msg );
				}
			} else {
				$login     = trim( sanitize_text_field( $_POST['user_login'] ) ); //phpcs:ignore
				$user_data = get_user_by( 'login', $login );
				if ( empty( $user_data ) ) {
					$err_msg = isset( $arm_global_settings->common_message['arm_no_registered_email'] ) ? $arm_global_settings->common_message['arm_no_registered_email'] : '';
					$err_msg = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'There is no user registered with that username.', 'armember-membership' );
					$arm_lite_errors->add( 'invalid_username', $err_msg );
				}
				$is_user_data_exist = 1;
			}

			if ( ! empty( $user_data ) && empty( $arm_lite_errors ) ) {
				$user_data_param = ( $is_user_data_exist == 1 ) ? $user_data : false;
				do_action( 'lostpassword_post', $arm_lite_errors, $user_data_param );
			}

			if ( $arm_lite_errors->get_error_code() ) {
				return $arm_lite_errors;
			}

			if ( ! $user_data ) {
				$err_msg = isset( $arm_global_settings->common_message['arm_no_registered_email'] ) ? $arm_global_settings->common_message['arm_no_registered_email'] : '';
				$err_msg = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'Invalid username or e-mail.', 'armember-membership' );
				$arm_lite_errors->add( 'invalidcombo', $err_msg );
				return $arm_lite_errors;
			}

			/* redefining user_login ensures we return the right case in the email */
			$user_id    = $user_data->ID;
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;
			/**
			 * Add patch for WordPress 4.4+
			 */
			if ( function_exists( 'get_password_reset_key' ) ) {
				$key = get_password_reset_key( $user_data );
				if ( is_wp_error( $key ) ) {

					$arm_lite_errors = new WP_Error();
					 $err_msg        = isset( $arm_global_settings->common_message['arm_reset_pass_not_allow'] ) ? $arm_global_settings->common_message['arm_reset_pass_not_allow'] : '';
					$err_msg         = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'Password reset is not allowed for this user.', 'armember-membership' );
					$arm_lite_errors->add( 'no_password_reset', $err_msg );
					return $key;
				}
			} else {

				do_action( 'retreive_password', $user_login );  /* Misspelled and deprecated */
				do_action( 'retrieve_password', $user_login );

				$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

				if ( ! $allow ) {
					$err_msg = isset( $arm_global_settings->common_message['arm_reset_pass_not_allow'] ) ? $arm_global_settings->common_message['arm_reset_pass_not_allow'] : '';
					$err_msg = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'Password reset is not allowed for this user.', 'armember-membership' );
					return new WP_Error( 'no_password_reset', $err_msg );
				} elseif ( is_wp_error( $allow ) ) {
					return $allow;
				}
				/* Generate something random for a key... */
				$key = wp_generate_password( 20, false );
				do_action( 'retrieve_password_key', $user_login, $key );
				/* Now insert the new md5 key into the db */
				if ( empty( $wp_hasher ) ) {
					require_once ABSPATH . WPINC . '/class-phpass.php';
					$wp_hasher = new PasswordHash( 8, true );
				}
				$hashed    = $wp_hasher->HashPassword( $key );
				$key_saved = $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				if ( false === $key_saved ) {
					return new WP_Error( 'no_password_key_update', esc_html__( 'Could not save password reset key to database.', 'armember-membership' ) );
				}
			}
			update_user_meta( $user_id, 'arm_reset_password_key', $key );
			$change_password_page_id = isset( $arm_global_settings->global_settings['change_password_page_id'] ) ? $arm_global_settings->global_settings['change_password_page_id'] : 0;
			if ( $change_password_page_id == 0 ) {
				$rp_link = network_site_url( 'wp-login.php?action=rp&key=' . rawurlencode( $key ) . '&login=' . rawurlencode( $user_login ), 'login' );
			} else {

				$arm_change_password_page_url = $arm_global_settings->arm_get_permalink( '', $change_password_page_id );

				$arm_change_password_page_url = $arm_global_settings->add_query_arg( 'action', 'armrp', $arm_change_password_page_url );
				$arm_change_password_page_url = $arm_global_settings->add_query_arg( 'key', rawurlencode( $key ), $arm_change_password_page_url );
				$arm_change_password_page_url = $arm_global_settings->add_query_arg( 'login', rawurlencode( $user_login ), $arm_change_password_page_url );

				$rp_link = $arm_change_password_page_url;
			}

			$varification_key = get_user_meta( $user_id, 'arm_user_activation_key', true );
			$user_status      = arm_get_member_status( $user_id );
			if ( $user_status == 3 ) {
				$rp_link = $arm_global_settings->add_query_arg( 'varify_key', rawurlencode( $varification_key ), $rp_link );
			}

			/* Now Create Password Reset Link */

			if ( is_multisite() ) {
				$blogname = $current_site->site_name;
			} else {
				/*
				 The blogname option is escaped with esc_html on the way into the database in sanitize_option */
				/* we want to reverse this for the plain text arena of emails. */
				$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			}
			$temp_detail = $arm_email_settings->arm_get_email_template( $arm_email_settings->templates->forgot_passowrd_user );
			if ( $temp_detail->arm_template_status == '1' ) {
				$title = $arm_global_settings->arm_filter_email_with_user_detail( $temp_detail->arm_template_subject, $user_id, 0 );

				$message = $arm_global_settings->arm_filter_email_with_user_detail( $temp_detail->arm_template_content, $user_id, 0, 0, $key );

				$message = str_replace( '{ARM_RESET_PASSWORD_LINK}', '<a href="' . esc_url($rp_link) . '">' . esc_html($rp_link) . '</a>', $message );
				$message = str_replace( '{VAR1}', '<a href="' . esc_url($rp_link) . '">' . esc_html($rp_link) . '</a>', $message );
			} else {
				$title    = $blogname . ' ' . esc_html__( 'Password Reset', 'armember-membership' );
				$message  = esc_html__( 'Someone requested that the password be reset for the following account:', 'armember-membership' ) . "\r\n\r\n";
				$message .= network_home_url( '/' ) . "\r\n\r\n";
				$message .= esc_html__( 'Username', 'armember-membership' ) . ': ' . $user_login . "\r\n\r\n";
				$message .= esc_html__( 'If this was a mistake, just ignore this email and nothing will happen.', 'armember-membership' ) . "\r\n\r\n";
				$message .= esc_html__( 'To reset your password, visit the following address:', 'armember-membership' ) . ' ' . $rp_link . "\r\n\r\n";
			}

			remove_all_filters( 'retrieve_password_message' );
			remove_all_filters( 'retrieve_password_title' );
			$title     = apply_filters( 'retrieve_password_title', $title, $user_data->ID );
			$message   = apply_filters( 'retrieve_password_message', $message, $key, $user_data->user_login, $user_data );
			$send_mail = $arm_global_settings->arm_wp_mail( '', $user_email, $title, $message );

			if ( $message && ! $send_mail ) {
				$err_msg = isset( $arm_global_settings->common_message['arm_email_not_sent'] ) ? $arm_global_settings->common_message['arm_email_not_sent'] : '';
				$err_msg = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'The e-mail could not be sent.', 'armember-membership' ) . "<br />\n" . esc_html__( 'Possible reason: your host may have disabled the mail() function...', 'armember-membership' );
				return new WP_Error( 'no_password_reset', $err_msg );
			}
			return true;
		}

		function arm_reset_password( $user, $new_pass ) {
			global $wp, $wpdb, $current_user, $ARMemberLite;

			do_action( 'password_reset', $user, $new_pass );

			wp_set_password( $new_pass, $user->ID );

			do_action_ref_array( 'arm_user_password_changed', array( &$user ) );

			do_action( 'arm_change_password_external', $user->ID, $new_pass );
		}

		function arm_check_exist_field() {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings;
			if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'arm_check_exist_field' ) { //phpcs:ignore
				$ARMemberLite->arm_check_user_cap( '', '1' ); //phpcs:ignore --Reason:Verifying nonce
				$return = array(
					'status' => 'success',
					'check'  => 1,
				);
				$requested_field = !empty( $_REQUEST['field'] ) ? sanitize_text_field($_REQUEST['field']) : '';//phpcs:ignore
				$requested_value = !empty( $_REQUEST['value'] ) ? sanitize_text_field($_REQUEST['value']) : '';//phpcs:ignore
				switch ( $requested_field ) {
					case 'user_login':
						if ( username_exists( sanitize_user( $requested_value ) ) ) {
							$return = array(
								'status' => 'error',
								'check'  => 0,
							);
						} else {
							$return = array(
								'status' => 'success',
								'check'  => 1,
							);
						}
						break;
					case 'user_email':
						if ( is_user_logged_in() ) {
							$current_user = wp_get_current_user();
							if ( strtolower( $current_user->user_email ) == strtolower( sanitize_email( $requested_value ) ) ) {
								$return = array(
									'status' => 'success',
									'check'  => 1,
								);
								echo wp_json_encode( $return );
								exit;
							}
						}
						if ( email_exists( sanitize_email( $requested_value ) ) ) {
							$return = array(
								'status' => 'error',
								'check'  => 0,
							);
						} else {
							$return = array(
								'status' => 'success',
								'check'  => 1,
							);
						}
						break;
					default:
						break;
				}
				echo wp_json_encode( $return );
				exit;
			}
		}

		function arm_filter_form_field_options( $field_options = array() ) {
			global $wp, $wpdb, $current_user, $ARMemberLite;
			if ( ! empty( $field_options['type'] ) ) {
				$type = $field_options['type'];
			} else {
				$type = 'text';
			}
			$field_id = isset( $field_options['meta_key'] ) ? $field_options['meta_key'] : '';
			if ( empty( $field_id ) ) {
				$field_id = $type . '_' . wp_generate_password( 5, false, false );
			}
			if ( $type == 'password' ) {
				$field_id = 'user_pass';
			}
			$default_options                  = array(
				'id'                  => $field_id,
				'label'               => '',
				'placeholder'         => '',
				'type'                => $type,
				'sub_type'            => '',
				'value'               => '',
				'bg_color'            => '',
				'padding'             => array(),
				'margin'              => array(),
				'options'             => array(),
				'allow_ext'           => '',
				'file_size_limit'     => 2,
				'meta_key'            => $field_id, // phpcs:ignore
				'required'            => 0,
				'hide_username'       => 0,
				'hide_firstname'      => 0,
				'hide_lastname'       => 0,
				'blank_message'       => esc_html__( 'This field can not be left blank.', 'armember-membership' ),
				'validation_type'     => 'custom_validation_none',
				'regular_expression'  => '',
				'invalid_message'     => esc_html__( 'Please enter valid data.', 'armember-membership' ),
				'invalid_username'    => esc_html__( 'This username is invalid. Please enter a valid username.', 'armember-membership' ),
				'invalid_firstname'   => esc_html__( 'This first name is invalid. Please enter a valid first name.', 'armember-membership' ),
				'invalid_lastname'    => esc_html__( 'This last name is invalid. Please enter a valid last name.', 'armember-membership' ),
				'default_field'       => 0,
				'cal_localization'    => '',
				'description'         => '',
				'prefix'              => '',
				'suffix'              => '',
				'_builtin'            => 0,
				'default_val'         => array(),
				'mapfield'            => 0,
				'ref_field_id'        => 0,
				'enable_repeat_field' => 0,
			);
			$field_options                    = shortcode_atts( $default_options, $field_options );
			$field_options['label']           = isset( $field_options['label'] ) ? stripslashes( $field_options['label'] ) : '';
			$field_options['placeholder']     = isset( $field_options['placeholder'] ) ? stripslashes( $field_options['placeholder'] ) : '';
			$field_options['blank_message']   = isset( $field_options['blank_message'] ) ? stripslashes( $field_options['blank_message'] ) : '';
			$field_options['invalid_message'] = isset( $field_options['invalid_message'] ) ? stripslashes( $field_options['invalid_message'] ) : '';
			if ( in_array( $field_options['type'], array( 'radio' ) ) && empty( $field_options['default_val'] ) && ! empty( $field_options['options'] ) ) {
				$fieldOptValues = array_values( $field_options['options'] );
				$firstVal       = array_shift( $fieldOptValues );
				reset( $field_options['options'] );
				$firstVal = stripslashes( $firstVal );
				$new_data = explode( ':', $firstVal );
				$key      = isset( $new_data[0] ) ? $new_data[0] : $firstVal;
				if ( isset( $new_data[1] ) && $new_data[1] != '' ) {
					$key = $new_data[1];
				}
				$field_options['default_val'] = $key;
			}
			if ( empty( $field_options['meta_key'] ) ) {
				$field_options['meta_key'] = $field_options['id']; // phpcs:ignore
			}
			/* Set Field Values. */
			$cur_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : ''; //phpcs:ignore
			if ( isset( $_REQUEST['arm_setup_preview'] ) ) { //phpcs:ignore
				return $field_options;
			}
			if ( is_user_logged_in() && ! in_array( $cur_page, array( 'arm_form_settings', 'arm_manage_forms' ) ) ) {
				if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit_member' && is_admin() ) { //phpcs:ignore
					$requested_id = !empty( $_REQUEST['id'] ) ? intval($_REQUEST['id']) : '';//phpcs:ignore
					$user_info = get_userdata( intval( $requested_id ) );
				}
				if ( ! is_admin() ) {
					$user_info = wp_get_current_user();
				}
				if ( ! empty( $user_info ) && ! in_array( $field_options['type'], array( 'submit', 'password', 'section', 'html' ) ) ) {
					switch ( $field_options['meta_key'] ) {
						case 'user_login':
						case 'username':
							$field_options['value'] = $user_info->user_login;
							break;
						case 'user_email':
						case 'email':
							$field_options['value'] = $user_info->user_email;
							break;
						case 'first_name':
						case 'firstname':
						case 'fname':
						case 'user_firstname':
							$field_options['value'] = $user_info->first_name;
							break;
						case 'lastname':
						case 'last_name':
						case 'lname':
						case 'user_lastname':
							$field_options['value'] = $user_info->last_name;
							break;
						case 'display_name':
						case 'full_name':
							$field_options['value'] = $user_info->display_name;
							break;
						case 'user_url':
						case 'website':
							$field_options['value'] = $user_info->user_url;
							break;
						case 'arm_primary_status':
							$field_options['value'] = arm_get_member_status( $user_info->ID );
							break;
						case 'arm_secondary_status':
							$field_options['value'] = arm_get_member_status( $user_info->ID, 'secondary' );
							break;
						case 'html':
							break;
						default:
							$field_options['value'] = get_user_meta( $user_info->ID, $field_options['meta_key'], true );
							break;
					}
				}
			}
			return $field_options;
		}

		function arm_default_field_options() {
			global $wp, $wpdb, $ARMemberLite, $arm_global_settings;
			$role_options = $arm_global_settings->arm_get_all_roles();
			$fields       = array(
				'text'         => array(
					'label'         => esc_html__( 'Textbox', 'armember-membership' ),
					'placeholder'   => '',
					'type'          => 'text',
					'required'      => 0,
					'blank_message' => esc_html__( 'Text field can not be left blank.', 'armember-membership' ),
				),
				'password'     => array(
					'label'           => esc_html__( 'Password', 'armember-membership' ),
					'placeholder'     => '',
					'type'            => 'password',
					'options'         => array(
						'strength_meter'  => 1,
						'strong_password' => 0,
						'minlength'       => 6,
						'special'         => 1,
						'numeric'         => 1,
						'uppercase'       => 1,
						'lowercase'       => 1,
					),
					'required'        => 0,
					'blank_message'   => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
					'invalid_message' => esc_html__( 'Please enter valid password.', 'armember-membership' ),
				),
				'textarea'     => array(
					'label'         => esc_html__( 'Textarea', 'armember-membership' ),
					'placeholder'   => '',
					'type'          => 'textarea',
					'required'      => 0,
					'blank_message' => esc_html__( 'This Field can not be left blank.', 'armember-membership' ),
				),
				'checkbox'     => array(
					'label'         => esc_html__( 'Checkbox', 'armember-membership' ),
					'placeholder'   => '',
					'type'          => 'checkbox',
					'required'      => 0,
					'options'       => array(
						'checkbox1' => 'Checkbox1',
						'checkbox2' => 'Checkbox2',
					),
					'blank_message' => esc_html__( 'Please check atleast one option.', 'armember-membership' ),
				),
				'radio'        => array(
					'label'         => esc_html__( 'Radio Button', 'armember-membership' ),
					'placeholder'   => '',
					'type'          => 'radio',
					'required'      => 0,
					'options'       => array(
						'radio1' => 'Radio1',
						'radio2' => 'Radio2',
					),
					'blank_message' => esc_html__( 'Please select one option.', 'armember-membership' ),
				),
				'select'       => array(
					'label'         => esc_html__( 'Dropdown', 'armember-membership' ),
					'placeholder'   => '',
					'type'          => 'select',
					'required'      => 0,
					'options'       => array(
						''        => 'Select Option',
						'option1' => 'Option1',
					),
					'blank_message' => esc_html__( 'Please select atleast one option.', 'armember-membership' ),
				),
				'date'         => array(
					'label'            => esc_html__( 'Date', 'armember-membership' ),
					'placeholder'      => '',
					'type'             => 'date',
					'required'         => 0,
					'value'            => '',
					'blank_message'    => esc_html__( 'Please select date.', 'armember-membership' ),
					'invalid_message'  => esc_html__( 'Invalid Date.', 'armember-membership' ),
					'cal_localization' => '',
				),
				'file'         => array(
					'label'           => esc_html__( 'File Upload', 'armember-membership' ),
					'placeholder'     => esc_html__( 'Drop file here or click to select.', 'armember-membership' ),
					'type'            => 'file',
					'required'        => 0,
					'value'           => '',
					'allow_ext'       => '',
					'file_size_limit' => '2',
					'blank_message'   => esc_html__( 'Please select file.', 'armember-membership' ),
					'invalid_message' => esc_html__( 'Invalid file selected.', 'armember-membership' ),
				),
				'avatar'       => array(
					'label'           => esc_html__( 'Avatar', 'armember-membership' ),
					'placeholder'     => esc_html__( 'Drop file here or click to select.', 'armember-membership' ),
					'type'            => 'avatar',
					'required'        => 0,
					'value'           => '',
					'meta_key'        => 'avatar', // phpcs:ignore
					'allow_ext'       => '',
					'file_size_limit' => '2',
					'blank_message'   => esc_html__( 'Please select file.', 'armember-membership' ),
					'invalid_message' => esc_html__( 'Invalid file selected.', 'armember-membership' ),
				),
				'roles'        => array(
					'label'         => esc_html__( 'Roles', 'armember-membership' ),
					'placeholder'   => '',
					'type'          => 'roles',
					'options'       => $role_options,
					'sub_type'      => 'select',
					'meta_key'      => 'roles', // phpcs:ignore
					'required'      => 0,
					'blank_message' => esc_html__( 'Please select atleast one role.', 'armember-membership' ),
				),
				'hidden'       => array(
					'label'         => esc_html__( 'Hidden Field', 'armember-membership' ),
					'placeholder'   => '',
					'type'          => 'hidden',
					'required'      => 0,
					'blank_message' => '',
				),
				'html'         => array(
					'label'         => esc_html__( 'Html Area', 'armember-membership' ),
					'value'         => esc_html__( 'Html Text', 'armember-membership' ),
					'placeholder'   => '',
					'type'          => 'html',
					'required'      => 0,
					'blank_message' => '',
				),
				'section'      => array(
					'label'         => esc_html__( 'Divider', 'armember-membership' ),
					'value'         => esc_html__( 'Section', 'armember-membership' ) . '<hr/>',
					'bg_color'      => '#F9F9F9',
					'padding'       => array(),
					'margin'        => array(),
					'placeholder'   => '',
					'type'          => 'section',
					'options'       => array(),
					'required'      => 0,
					'blank_message' => '',
				),
				'rememberme'   => array(
					'id'          => 'rememberme',
					'label'       => esc_html__( 'Remember me', 'armember-membership' ),
					'placeholder' => '',
					'type'        => 'rememberme',
					'meta_key'    => 'rememberme', // phpcs:ignore
					'required'    => 0,
				),
				'repeat_pass'  => array(
					'_builtin'        => 1,
					'id'              => 'repeat_pass',
					'label'           => esc_html__( 'Confirm Password', 'armember-membership' ),
					'placeholder'     => '',
					'type'            => 'repeat_pass',
					'options'         => array(
						'strength_meter'  => 0,
						'strong_password' => 0,
						'minlength'       => 0,
						'maxlength'       => '',
						'special'         => 0,
						'numeric'         => 0,
						'uppercase'       => 0,
						'lowercase'       => 0,
					),
					'meta_key'        => 'repeat_pass', // phpcs:ignore
					'required'        => 1,
					'blank_message'   => esc_html__( 'Confirm Password can not be left blank.', 'armember-membership' ),
					'invalid_message' => esc_html__( 'Passwords don\'t match.', 'armember-membership' ),
				),
				'repeat_email' => array(
					'_builtin'        => 1,
					'id'              => 'repeat_email',
					'label'           => esc_html__( 'Confirm Email Address', 'armember-membership' ),
					'placeholder'     => '',
					'type'            => 'repeat_email',
					'meta_key'        => 'repeat_email', // phpcs:ignore
					'required'        => 1,
					'blank_message'   => esc_html__( 'Confirm Email Address can not be left blank.', 'armember-membership' ),
					'invalid_message' => esc_html__( 'Please enter email address again.', 'armember-membership' ),
				),
			);

			$preset_fields = $this->arm_get_db_form_fields( true );
			if ( ! empty( $preset_fields ) ) {
				$fields = array_merge( $preset_fields, $fields );
			}
			return $fields;
		}

		function arm_social_profile_field_types() {
			$socialProfileFields = array(
				'facebook'  => esc_html__( 'Facebook', 'armember-membership' ),
				'twitter'   => esc_html__( 'Twitter', 'armember-membership' ),
				'linkedin'  => esc_html__( 'LinkedIn', 'armember-membership' ),
				'vk'        => esc_html__( 'VK', 'armember-membership' ),
				'instagram' => esc_html__( 'Instagram', 'armember-membership' ),
				'pinterest' => esc_html__( 'Pinterest', 'armember-membership' ),
				'youtube'   => esc_html__( 'Youtube', 'armember-membership' ),
				'dribbble'  => esc_html__( 'Dribbble', 'armember-membership' ),
				'delicious' => esc_html__( 'Delicious', 'armember-membership' ),
				'tumblr'    => esc_html__( 'Tumblr', 'armember-membership' ),
				'vine'      => esc_html__( 'Vine', 'armember-membership' ),
				'skype'     => esc_html__( 'Skype', 'armember-membership' ),
				'whatsapp'  => esc_html__( 'WhatsApp', 'armember-membership' ),
				'tiktok'    => esc_html__( 'Tiktok', 'armember-membership' ),
			);
			return $socialProfileFields;
		}

		function arm_default_preset_user_fields() {
			global $wp, $wpdb, $current_user, $ARMemberLite, $arm_social_feature;
			$countries = $this->arm_get_countries();
			$countries = array_merge( array( '0' => 'Country/Region' ), $countries );
			foreach ( $countries as $key => $country ) {
				$countries[ $key ] = "{$country}:{$key}";
			}
			$defaultPresetFields = array(
				'first_name'    => array(
					'_builtin'          => 1,
					'id'                => 'first_name',
					'label'             => esc_html__( 'First Name', 'armember-membership' ),
					'placeholder'       => '',
					'type'              => 'text',
					'meta_key'          => 'first_name', // phpcs:ignore
					'required'          => 0,
					'hide_firstname'    => 0,
					'invalid_firstname' => esc_html__( 'This first name is invalid. Please enter a valid first name.', 'armember-membership' ),
				),
				'last_name'     => array(
					'_builtin'         => 1,
					'id'               => 'last_name',
					'label'            => esc_html__( 'Last Name', 'armember-membership' ),
					'placeholder'      => '',
					'type'             => 'text',
					'meta_key'         => 'last_name', // phpcs:ignore
					'required'         => 0,
					'hide_lastname'    => 0,
					'invalid_lastname' => esc_html__( 'This last name is invalid. Please enter a valid last name.', 'armember-membership' ),
				),
				'display_name'  => array(
					'_builtin'    => 1,
					'id'          => 'display_name',
					'type'        => 'text',
					'label'       => esc_html__( 'Profile Display Name', 'armember-membership' ),
					'placeholder' => '',
					'meta_key'    => 'display_name', // phpcs:ignore
					'required'    => 0,
				),
				'user_login'    => array(
					'_builtin'        => 1,
					'id'              => 'user_login',
					'label'           => esc_html__( 'Username', 'armember-membership' ),
					'placeholder'     => '',
					'type'            => 'text',
					'meta_key'        => 'user_login', // phpcs:ignore
					'required'        => 1,
					'hide_username'   => 0,
					'blank_message'   => esc_html__( 'Username can not be left blank.', 'armember-membership' ),
					'invalid_message' => esc_html__( 'Please enter valid username.', 'armember-membership' ),
				),
				'user_email'    => array(
					'_builtin'        => 1,
					'id'              => 'user_email',
					'label'           => esc_html__( 'Email Address', 'armember-membership' ),
					'placeholder'     => '',
					'type'            => 'email',
					'options'         => array( 'is_confirm_email' => 0 ),
					'meta_key'        => 'user_email', // phpcs:ignore
					'required'        => 1,
					'blank_message'   => esc_html__( 'Email Address can not be left blank.', 'armember-membership' ),
					'invalid_message' => esc_html__( 'Please enter valid email address.', 'armember-membership' ),
				),
				'user_pass'     => array(
					'_builtin'        => 1,
					'id'              => 'user_pass',
					'label'           => esc_html__( 'Password', 'armember-membership' ),
					'placeholder'     => '',
					'type'            => 'password',
					'options'         => array(
						'strength_meter'  => 1,
						'strong_password' => 0,
						'minlength'       => 6,
						'maxlength'       => '',
						'special'         => 1,
						'numeric'         => 1,
						'uppercase'       => 1,
						'lowercase'       => 1,
						'is_confirm_pass' => 0,
					),
					'meta_key'        => 'user_pass', // phpcs:ignore
					'required'        => 1,
					'blank_message'   => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
					'invalid_message' => esc_html__( 'Please enter valid password.', 'armember-membership' ),
				),
				'gender'        => array(
					'_builtin'      => 1,
					'id'            => 'gender',
					'type'          => 'radio',
					'label'         => esc_html__( 'Gender', 'armember-membership' ),
					'placeholder'   => '',
					'meta_key'      => 'gender', // phpcs:ignore
					'required'      => 0,
					'options'       => array(
						'male'   => 'Male',
						'female' => 'Female',
					),
					'blank_message' => esc_html__( 'Please select one.', 'armember-membership' ),
				),
				'user_url'      => array(
					'_builtin'        => 1,
					'id'              => 'user_url',
					'type'            => 'url',
					'label'           => esc_html__( 'Website (URL)', 'armember-membership' ),
					'placeholder'     => '',
					'meta_key'        => 'user_url', // phpcs:ignore
					'required'        => 0,
					'blank_message'   => esc_html__( 'Website (URL) can not be left blank.', 'armember-membership' ),
					'invalid_message' => esc_html__( 'Invalid URL', 'armember-membership' ),
				),
				'country'       => array(
					'_builtin'      => 1,
					'id'            => 'country',
					'type'          => 'select',
					'label'         => esc_html__( 'Country/Region', 'armember-membership' ),
					'placeholder'   => '',
					'meta_key'      => 'country', // phpcs:ignore
					'required'      => 0,
					'options'       => $countries, /* array('' => 'Country/Region', 'option1' => 'Option1'), */
					'blank_message' => esc_html__( 'Please select atleast one option.', 'armember-membership' ),
				),
				'description'   => array(
					'_builtin'      => 1,
					'id'            => 'description',
					'type'          => 'textarea',
					'label'         => esc_html__( 'Biography', 'armember-membership' ),
					'placeholder'   => '',
					'meta_key'      => 'description', // phpcs:ignore
					'required'      => 0,
					'blank_message' => esc_html__( 'Biography can not be left blank.', 'armember-membership' ),
				),
				'social_fields' => array(
					'_builtin'      => 1,
					'id'            => 'social_fields',
					'type'          => 'social_fields',
					'label'         => esc_html__( 'Social Profile Fields', 'armember-membership' ),
					'placeholder'   => '',
					'meta_key'      => '', // phpcs:ignore
					'required'      => 0,
					'options'       => array( 'facebook', 'twitter', 'linkedin' ),
					'blank_message' => '',
				),
			);
			return $defaultPresetFields;
		}

		function arm_get_db_form_fields( $merge = false ) {
			global $wp, $wpdb, $current_user, $ARMemberLite;
			$presetFormFields = get_option( 'arm_preset_form_fields', '' );
			$dbFormFields     = maybe_unserialize( $presetFormFields );
			if ( ! is_array( $dbFormFields ) ) {
				$dbFormFields = array();
			}
			if ( $merge ) {
				$dbFormFields['default'] = isset( $dbFormFields['default'] ) ? $dbFormFields['default'] : array();
				$dbFormFields['other']   = isset( $dbFormFields['other'] ) ? $dbFormFields['other'] : array();
				$dbFormFields            = array_merge( $dbFormFields['default'], $dbFormFields['other'] );
			}
			return $dbFormFields;
		}

		function arm_db_add_preset_form_field( $field = array(), $field_id = 0 ) {
			$field['meta_key'] = ( isset( $field['meta_key'] ) && ! empty( $field['meta_key'] ) ) ? $field['meta_key'] : str_replace( ' ', '_', $field_id ); // phpcs:ignore
			$field['label']    = ( isset( $field['label'] ) && ! empty( $field['label'] ) ) ? $field['label'] : $field_id;
			$field['type']     = ( isset( $field['type'] ) && ! empty( $field['type'] ) ) ? $field['type'] : 'text';
			$this->arm_db_add_form_field( $field );
		}

		function arm_db_add_form_field( $field = array(), $field_id = 0, $form_id = 0 ) {
			global $wp, $wpdb, $current_user, $ARMemberLite;
			$defaultPresetFields = $this->arm_default_preset_user_fields();
			$oldFormFields       = $this->arm_get_db_form_fields();
			$fieldMetaKey        = ( isset( $field['meta_key'] ) && ! empty( $field['meta_key'] ) ) ? $field['meta_key'] : '';
			$fieldType           = ( isset( $field['type'] ) && ! empty( $field['type'] ) ) ? $field['type'] : '';
			$fieldMap            = ( isset( $field['mapfield'] ) && ! empty( $field['mapfield'] ) ) ? $field['mapfield'] : '';
			if ( ! empty( $fieldMetaKey ) && ! in_array( $fieldMetaKey, array_keys( $defaultPresetFields ) ) ) {
				if ( ! isset( $oldFormFields['other'][ $fieldMetaKey ] ) && ! in_array( $fieldType, array( 'hidden', 'html', 'section', 'info', 'rememberme', 'repeat_pass', 'repeat_email', 'social_fields' ) ) ) {
					$core_options                            = array(
						'db_field_id'       => $field_id,
						'db_form_id'        => $form_id,
						'id'                => $fieldMetaKey,
						'label'             => '',
						'placeholder'       => '',
						'type'              => $fieldType,
						'sub_type'          => '',
						'value'             => '',
						'options'           => array(),
						'allow_ext'         => '',
						'file_size_limit'   => 2,
						'meta_key'          => $fieldMetaKey, // phpcs:ignore
						'blank_message'     => esc_html__( 'This field can not be left blank.', 'armember-membership' ),
						'invalid_username'  => esc_html__( 'TThis username is invalid. Please enter a valid username.', 'armember-membership' ),
						'invalid_firstname' => esc_html__( 'This first name is invalid. Please enter a valid first name.', 'armember-membership' ),
						'invalid_lastname'  => esc_html__( 'This last name is invalid. Please enter a valid last name.', 'armember-membership' ),
						'invalid_message'   => esc_html__( 'Please enter valid detail.', 'armember-membership' ),
						'prefix'            => '',
						'suffix'            => '',
						'default_val'       => array(),
						'mapfield'          => $fieldMap,
					);
					$field_options                           = shortcode_atts( $core_options, $field );
					$field_options['default_field']          = $field_options['_builtin'] = $field_options['required'] = 0;
					$oldFormFields['other'][ $fieldMetaKey ] = $field_options;
					update_option( 'arm_preset_form_fields', $oldFormFields );
				}/* End `(!isset($oldFormFields['other'][$fieldMetaKey]))` */
			}/* End `(!empty($fieldMetaKey) && !in_array($fieldMetaKey, array_keys($defaultPresetFields)))` */
			return;
		}

		function arm_create_add_new_field( $form_id, $field_options ) {
			global $wp, $wpdb, $current_user, $ARMemberLite;
			$form_field_data = array(
				'arm_form_field_form_id'      => $form_id,
				'arm_form_field_slug'         => $field_options['meta_key'],
				'arm_form_field_created_date' => current_time( 'mysql' ),
				'arm_form_field_option'       => maybe_serialize( $field_options ),
				'arm_form_field_status'       => '2',
			);
			/* Insert Form Fields. */
			$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $form_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$form_field_id = $wpdb->insert_id;
			return $form_field_id;
		}

		function arm_get_updated_field_html() {
			global $wp, $wpdb, $current_user, $ARMemberLite, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_forms'], '1' ); //phpcs:ignore --Reason:Verifying nonce

			$form_id       = intval( $_POST['form_id'] ); //phpcs:ignore
			$form          = new ARM_Form_Lite( 'id', $form_id );
			$form_field_id = intval( $_POST['field_id'] ); //phpcs:ignore
			$field_options = isset( $_POST['arm_forms'][ $form_id ][ $form_field_id ] ) ? $_POST['arm_forms'][ $form_id ][ $form_field_id ] : array();  //phpcs:ignore
			$options       = array_map( 'trim', explode( "\n", $field_options['options'] ) );
			$new_options   = $options;
			if ( is_array( $options ) ) {
				$new_options = array();
				foreach ( $options as $data ) {
					if ( $data != '' ) {
						$new_options[] = stripslashes( $data );
					}
				}
			}
			$field_options['options'] = $new_options;

			$arm_form_type = $wpdb->get_row( $wpdb->prepare( 'SELECT arm_form_type FROM `' . $ARMemberLite->tbl_arm_forms . '` WHERE arm_form_id = %d', $form_id ) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_forms is a table name
			$isEditProfile = ( $arm_form_type->arm_form_type == 'edit_profile' ) ? true : false;
			/* Filter Form Field Options. */
			$field_options = apply_filters( 'arm_change_field_options', $field_options );
			$liStyle       = $sortable_class = '';
			$ref_field_id  = ( isset( $field_options['ref_field_id'] ) && $field_options['ref_field_id'] != 0 ) ? $field_options['ref_field_id'] : 0;
			if ( $field_options['type'] == 'section' ) {
				$sortable_class  .= ' arm_section_fields_wrapper';
				$margin           = isset( $field_options['margin'] ) ? $field_options['margin'] : array();
				$margin['top']    = ( isset( $margin['top'] ) && is_numeric( $margin['top'] ) ) ? $margin['top'] : 20;
				$margin['bottom'] = ( isset( $margin['bottom'] ) && is_numeric( $margin['bottom'] ) ) ? $margin['bottom'] : 20;
				$liStyle         .= 'margin-top:' . $margin['top'] . 'px !important;';
				$liStyle         .= 'margin-bottom:' . $margin['bottom'] . 'px !important;';
			}
			/* Generate Field HTML */
			?>
			<li class="arm-df__form-group arm_form_field_sortable arm-df__form-group_<?php echo esc_attr($field_options['type']); ?> <?php echo esc_attr($sortable_class); ?>" id="arm-df__form-group_<?php echo esc_attr($form_field_id); ?>" data-field_id="<?php echo esc_attr($form_field_id); ?>" data-type="<?php echo esc_attr($field_options['type']); ?>" data-meta_key="<?php echo strtolower( esc_attr($field_options['meta_key']) ); //phpcs:ignore ?>" data-ref_field="<?php echo esc_attr($ref_field_id); ?>" style="<?php echo $liStyle; //phpcs:ignore ?>">
			<?php
			$this->arm_member_form_get_field_html( $form_id, $form_field_id, $field_options, 'inactive', $form, $isEditProfile );
			?>
			</li>
			<?php
			exit;
		}

		function arm_create_new_field( $form_id = 0, $type = '', $refFieldID = 0 ) {
			global $wp, $wpdb, $current_user, $ARMemberLite, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_forms'], '1' ); //phpcs:ignore --Reason:Verifying nonce

			$field_type_options            = $this->arm_default_field_options();
			$field_type_options            = maybe_unserialize( $field_type_options );
			$form_id                       = ( ! empty( $form_id ) && $form_id != 0 ) ? $form_id : intval( $_POST['form_id'] ); //phpcs:ignore
			$type                          = ( ! empty( $type ) ) ? $type : sanitize_text_field( $_POST['type'] ); //phpcs:ignore
			$refFieldID                    = ( ! empty( $refFieldID ) ) ? $refFieldID : ( isset( $_POST['ref_field_id'] ) ? intval( $_POST['ref_field_id'] ) : 0 ); //phpcs:ignore
			$form                          = new ARM_Form_Lite( 'id', $form_id );
			$field_options                 = $field_type_options[ $type ];
			$ref_field_id                  = ( ! empty( $refFieldID ) && $refFieldID != 0 ) ? $refFieldID : 0;
			$field_options['ref_field_id'] = $ref_field_id;
			$total_fields                  = isset( $_POST['current_total_fields'] ) ? intval( $_POST['current_total_fields'] ) : rand( 99, 999 ); //phpcs:ignore
			/* Filter Form Field Options. */
			$field_options = apply_filters( 'arm_change_field_options', $field_options );
			$temp_form_id  = ( $form_id * 100 );
			$arm_form_type = $wpdb->get_row( $wpdb->prepare( 'SELECT arm_form_type FROM `' . $ARMemberLite->tbl_arm_forms . '` WHERE arm_form_id = %d', $form_id ) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_forms is a table name
			$isEditProfile = ( $arm_form_type->arm_form_type == 'edit_profile' ) ? true : false;
			$form_field_id = ( (int) $temp_form_id + (int) $total_fields );
			/* Generate Field HTML */
			$liStyle = $sortable_class = '';
			if ( $field_options['type'] == 'section' ) {
				$sortable_class  .= ' arm_section_fields_wrapper';
				$margin           = isset( $field_options['margin'] ) ? $field_options['margin'] : array();
				$margin['top']    = ( isset( $margin['top'] ) && is_numeric( $margin['top'] ) ) ? $margin['top'] : 20;
				$margin['bottom'] = ( isset( $margin['bottom'] ) && is_numeric( $margin['bottom'] ) ) ? $margin['bottom'] : 20;
				$liStyle         .= 'margin-top:' . $margin['top'] . 'px !important;';
				$liStyle         .= 'margin-bottom:' . $margin['bottom'] . 'px !important;';
			}
			?>
			<li class="arm-df__form-group arm_form_field_sortable arm-df__form-group_<?php echo esc_html($field_options['type']); ?> <?php echo esc_html($sortable_class); ?>" id="arm-df__form-group_<?php echo esc_attr($form_field_id); ?>" data-field_id="<?php echo esc_attr($form_field_id); ?>" data-type="<?php echo esc_attr($field_options['type']); ?>" data-meta_key="<?php echo strtolower( esc_attr($field_options['meta_key']) ); //phpcs:ignore ?>" data-ref_field="<?php echo esc_attr($ref_field_id); ?>" style="<?php echo esc_attr($liStyle); ?>">
			<?php
			$this->arm_member_form_get_field_html( $form_id, $form_field_id, $field_options, 'inactive', $form, $isEditProfile );
			?>
			</li>
			<?php
			exit;
		}

		function arm_get_updated_social_profile_fields_html() {
			global $wp, $wpdb, $current_user, $ARMemberLite, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_forms'], '1' ); //phpcs:ignore --Reason:Verifying nonce

			$field_type_options = $this->arm_default_preset_user_fields();
			$field_type_options = maybe_unserialize( $field_type_options );
			$field_options      = $field_type_options['social_fields'];
			$form_id            = intval( $_POST['form_id'] ); //phpcs:ignore
			$form               = new ARM_Form_Lite( 'id', $form_id );
			if ( isset( $_POST['field_id'] ) && $_POST['field_id'] != 0 ) { //phpcs:ignore
				$form_field_id = intval( $_POST['field_id'] ); //phpcs:ignore
			} else {
				$total_fields  = isset( $_POST['current_total_fields'] ) ? intval( $_POST['current_total_fields'] ) : rand( 99, 999 ); //phpcs:ignore
				$temp_form_id  = ( $form_id * 100 );
				$form_field_id = ( (int) $temp_form_id + (int) $total_fields );
			}
			$arm_form_type            = $wpdb->get_row( $wpdb->prepare( 'SELECT arm_form_type FROM `' . $ARMemberLite->tbl_arm_forms . '` WHERE arm_form_id = %d', $form_id ) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_forms is a table name
			$isEditProfile            = ( $arm_form_type->arm_form_type == 'edit_profile' ) ? true : false;
			$field_options['options'] = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST['arm_social_fields'] ); //phpcs:ignore
			/* Filter Form Field Options. */
			$field_options = apply_filters( 'arm_change_field_options', $field_options );
			?>
			<li class="arm-df__form-group arm-df__form-group_social_fields" id="arm-df__form-group_<?php echo esc_attr($form_field_id); ?>" data-type="social_fields" data-field_id="<?php echo esc_attr($form_field_id); ?>">
			<?php
			$this->arm_member_form_get_field_html( $form_id, $form_field_id, $field_options, 'inactive', $form, $isEditProfile );
			?>
			</li>
			<?php
			exit;
		}

		function arm_prefix_suffix_field_html() {
			global $wp, $wpdb, $ARMemberLite, $arm_capabilities_global;
			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_forms'], '1' ); //phpcs:ignore --Reason:Verifying nonce

			$iconColor = isset( $_POST['color'] ) ? sanitize_text_field( $_POST['color'] ) : ''; //phpcs:ignore
			$icon_key  = isset( $_POST['iconkey'] ) ? sanitize_text_field( $_POST['iconkey'] ) : ''; //phpcs:ignore
			$icon = isset( $_POST['icon'] ) ? sanitize_text_field( $_POST['icon'] ) : ''; //phpcs:ignore
			$field_id = intval( $_POST['field_id'] ); //phpcs:ignore
			$type = sanitize_text_field( $_POST['type'] ); //phpcs:ignore
			$icon      = $icon_key . ' ' . $icon;
			if ( ! empty( $icon ) ) {
				echo $this->arm_generate_field_fa_icon( $field_id, $icon, $type, $iconColor ); //phpcs:ignore
			} else {
				echo '';
			}
			exit;
		}

		function arm_roles_field_options() {
			global $wp, $wpdb, $ARMemberLite, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_forms'], '1' ); //phpcs:ignore --Reason:Verifying nonce	

			$field_type_options      = $this->arm_default_field_options();
			$field_type_options      = maybe_unserialize( $field_type_options );
			$form_id                 = intval( $_POST['form_id'] ); //phpcs:ignore
			$field_id                = intval( $_POST['field_id'] ); //phpcs:ignore
			$field_options           = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST['arm_forms'][ $form_id ][ $field_id ] ); //phpcs:ignore
			$roles_field             = $field_type_options['roles'];
			$roles_field['sub_type'] = isset( $field_options['sub_type'] ) ? $field_options['sub_type'] : 'select';
			$roles_field['options']  = isset( $field_options['options'] ) ? $field_options['options'] : array();
			/* Filter Form Field Options. */
			$roles_field = apply_filters( 'arm_change_field_options', $roles_field );
			echo $this->arm_member_form_get_fields_by_type( $roles_field, $field_id, $form_id ); //phpcs:ignore
			exit;
		}

		function armGetFormFieldKeysForDelete( $form_id = 0 ) {
			global $wp, $wpdb, $ARMemberLite;
			$otherFormFieldKeys = array();
			$field_result       = $wpdb->get_results( $wpdb->prepare('SELECT `arm_form_field_slug` FROM `' . $ARMemberLite->tbl_arm_form_field . "` WHERE `arm_form_field_form_id`!=%d AND `arm_form_field_status` != %d ORDER BY `arm_form_field_order` ASC",$form_id,2 ), ARRAY_A );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_form_field is a table name
			if ( ! empty( $field_result ) ) {
				foreach ( $field_result as $val ) {
					if ( ! empty( $val['arm_form_field_slug'] ) ) {
						$otherFormFieldKeys[ $val['arm_form_field_slug'] ] = $val['arm_form_field_slug'];
					}
				}
			}
			return $otherFormFieldKeys;
		}

		function arm_delete_form_field() {
			global $wp, $wpdb, $ARMemberLite, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_forms'], '1' ); //phpcs:ignore --Reason:Verifying nonce

			$field_id   = intval( $_POST['field_id'] ); //phpcs:ignore
			$field_type = sanitize_text_field( $_POST['field_type'] ); //phpcs:ignore
			$response   = array(
				'type' => 'error',
				'msg'  => 'There is a error while deleting field, please try again.',
			);
			if ( ! empty( $field_id ) ) {
				$old_field        = $wpdb->get_row( $wpdb->prepare('SELECT `arm_form_field_slug`, `arm_form_field_status`, `arm_form_field_option` FROM `' . $ARMemberLite->tbl_arm_form_field . "` WHERE `arm_form_field_id`=%d LIMIT 1",$field_id), ARRAY_A );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_form_field is a table name
				if(!empty($old_field)) {
					$old_field_status = $old_field['arm_form_field_status'];
					
					$field_options    = maybe_unserialize( $old_field['arm_form_field_option'] );
					if ( $old_field_status == 2 ) {
						$field_status_update = $wpdb->delete( $ARMemberLite->tbl_arm_form_field, array( 'arm_form_field_id' => $field_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					} else {
						$field_status_update = $wpdb->update( $ARMemberLite->tbl_arm_form_field, array( 'arm_form_field_status' => 0 ), array( 'arm_form_field_id' => $field_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					}
				}
				$response = array(
					'type' => 'success',
					'msg'  => 'Field deleted Successfully.',
				);
			}
			echo wp_json_encode( $response );
			die();
		}

		/*
		 * Default forms & their fields.
		 */

		function arm_default_member_forms_data() {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings;
			$first_name                 = array(
				'id'                => 'first_name',
				'label'             => esc_html__( 'First Name', 'armember-membership' ),
				'placeholder'       => '',
				'type'              => 'text',
				'meta_key'          => 'first_name', // phpcs:ignore
				'required'          => 1,
				'hide_firstname'    => 0,
				'blank_message'     => esc_html__( 'First Name can not be left blank.', 'armember-membership' ),
				'invalid_firstname' => esc_html__( 'This first name is invalid. Please enter a valid first name.', 'armember-membership' ),
				'default_field'     => 1,
			);
			$last_name                  = array(
				'id'               => 'last_name',
				'label'            => esc_html__( 'Last Name', 'armember-membership' ),
				'placeholder'      => '',
				'type'             => 'text',
				'meta_key'         => 'last_name', // phpcs:ignore
				'required'         => 1,
				'hide_lastname'    => 0,
				'blank_message'    => esc_html__( 'Last Name can not be left blank.', 'armember-membership' ),
				'invalid_Lastname' => esc_html__( 'This last name is invalid. Please enter a valid last name.', 'armember-membership' ),
				'default_field'    => 1,
			);
			$user_login                 = array(
				'id'               => 'user_login',
				'label'            => esc_html__( 'Username', 'armember-membership' ),
				'placeholder'      => '',
				'type'             => 'text',
				'meta_key'         => 'user_login', // phpcs:ignore
				'required'         => 1,
				'hide_username'    => 0,
				'blank_message'    => esc_html__( 'Username can not be left blank.', 'armember-membership' ),
				'invalid_message'  => esc_html__( 'Please enter valid username.', 'armember-membership' ),
				'invalid_username' => esc_html__( 'This username is invalid. Please enter a valid username.', 'armember-membership' ),
				'default_field'    => 1,
			);
			$user_login_forgot_password = array(
				'id'              => 'user_login',
				'label'           => esc_html__( 'Username OR Email Address', 'armember-membership' ),
				'placeholder'     => '',
				'type'            => 'text',
				'meta_key'        => 'user_login', // phpcs:ignore
				'required'        => 1,
				'blank_message'   => esc_html__( 'Username can not be left blank.', 'armember-membership' ),
				'invalid_message' => esc_html__( 'Please enter valid username.', 'armember-membership' ),
				'default_field'   => 1,
			);
			$user_email                 = array(
				'id'                  => 'user_email',
				'label'               => esc_html__( 'Email Address', 'armember-membership' ),
				'placeholder'         => '',
				'type'                => 'email',
				'meta_key'            => 'user_email', // phpcs:ignore
				'required'            => 1,
				'blank_message'       => esc_html__( 'Email Address can not be left blank.', 'armember-membership' ),
				'invalid_message'     => esc_html__( 'Please enter valid email address.', 'armember-membership' ),
				'default_field'       => 1,
				'ref_field_id'        => 0,
				'enable_repeat_field' => 0,
			);
			$user_pass_reg              = array(
				'id'              => 'user_pass',
				'label'           => esc_html__( 'Password', 'armember-membership' ),
				'placeholder'     => '',
				'type'            => 'password',
				'options'         => array(
					'strength_meter'  => 1,
					'strong_password' => 0,
					'minlength'       => 6,
					'maxlength'       => '',
					'special'         => 1,
					'numeric'         => 1,
					'uppercase'       => 1,
					'lowercase'       => 1,
				),
				'meta_key'        => 'user_pass', // phpcs:ignore
				'required'        => 1,
				'blank_message'   => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
				'invalid_message' => esc_html__( 'Please enter valid password.', 'armember-membership' ),
			);
			$user_pass_login            = array(
				'id'              => 'user_pass',
				'label'           => esc_html__( 'Password', 'armember-membership' ),
				'placeholder'     => '',
				'type'            => 'password',
				'options'         => array(
					'strength_meter'  => 0,
					'strong_password' => 0,
					'minlength'       => 1,
					'maxlength'       => '',
					'special'         => 0,
					'numeric'         => 0,
					'uppercase'       => 0,
					'lowercase'       => 0,
				),
				'meta_key'        => 'user_pass', // phpcs:ignore
				'required'        => 1,
				'blank_message'   => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
				'invalid_message' => esc_html__( 'Please enter valid password.', 'armember-membership' ),
				'default_field'   => 1,
			);
			$current_user_pass = array(
                'id' => 'current_user_pass',
                'label' => esc_html__('Current Password', 'armember-membership'),
                'placeholder' =>  esc_html__('Current Password', 'armember-membership'),
                'type' => 'current_user_pass',
                'options' => array('strength_meter' => 0, 'strong_password' => 0, 'minlength' => 0, 'maxlength' => '', 'special' => 1, 'numeric' => 1, 'uppercase' => 1, 'lowercase' => 1),
                'meta_key' => 'current_user_pass', // phpcs:ignore
                'required' => 1,
                'blank_message' => esc_html__('Current password can not be left blank.', 'armember-membership'),
                'invalid_message' => esc_html__('Please enter valid current password.', 'armember-membership'),
                'default_field' => 1,
                'ref_field_id' => 0,
                'enable_repeat_field' => 0,
            );
			$new_user_pass              = array(
				'id'                  => 'user_pass',
				'label'               => esc_html__( 'New Password', 'armember-membership' ),
				'placeholder'         => '',
				'type'                => 'password',
				'options'             => array(
					'strength_meter'  => 1,
					'strong_password' => 0,
					'minlength'       => 6,
					'maxlength'       => '',
					'special'         => 1,
					'numeric'         => 1,
					'uppercase'       => 1,
					'lowercase'       => 1,
				),
				'meta_key'            => 'user_pass', // phpcs:ignore
				'required'            => 1,
				'blank_message'       => esc_html__( 'Password can not be left blank.', 'armember-membership' ),
				'invalid_message'     => esc_html__( 'Please enter valid password.', 'armember-membership' ),
				'default_field'       => 1,
				'ref_field_id'        => 0,
				'enable_repeat_field' => 0,
			);
			$repeat_pass                = array(
				'id'                  => 'repeat_pass',
				'label'               => esc_html__( 'Confirm Password', 'armember-membership' ),
				'placeholder'         => '',
				'type'                => 'repeat_pass',
				'options'             => array(
					'strength_meter'  => 0,
					'strong_password' => 0,
					'minlength'       => 0,
					'maxlength'       => '',
					'special'         => 0,
					'numeric'         => 0,
					'uppercase'       => 0,
					'lowercase'       => 0,
				),
				'meta_key'            => 'repeat_pass', // phpcs:ignore
				'required'            => 1,
				'blank_message'       => esc_html__( 'Confirm Password can not be left blank.', 'armember-membership' ),
				'invalid_message'     => esc_html__( 'Passwords don\'t match.', 'armember-membership' ),
				'default_field'       => 1,
				'ref_field_id'        => 0,
				'enable_repeat_field' => 0,
			);
			$remember_me                = array(
				'id'            => 'rememberme',
				'type'          => 'rememberme',
				'label'         => esc_html__( 'Remember me', 'armember-membership' ),
				'meta_key'      => 'rememberme', // phpcs:ignore
				'required'      => 0,
				'default_field' => 1,
			);
			$submit                     = array(
				'id'            => 'submit',
				'label'         => esc_html__( 'Submit', 'armember-membership' ),
				'type'          => 'submit',
				'default_field' => 1,
			);
			$loginSubmit                = array(
				'id'            => 'submit',
				'label'         => esc_html__( 'LOGIN', 'armember-membership' ),
				'type'          => 'submit',
				'default_field' => 1,
			);
			$default_form_style         = $this->arm_default_form_style();
			/* Set Form Details. */
			$globalSettings           = $arm_global_settings->global_settings;
			$register_page_id         = isset( $globalSettings['register_page_id'] ) ? $globalSettings['register_page_id'] : 0;
			$forgot_password_page_id  = isset( $globalSettings['forgot_password_page_id'] ) ? $globalSettings['forgot_password_page_id'] : 0;
			$reg_redirect_id          = isset( $globalSettings['thank_you_page_id'] ) ? $globalSettings['thank_you_page_id'] : 0;
			$login_redirect_id        = isset( $globalSettings['edit_profile_page_id'] ) ? $globalSettings['edit_profile_page_id'] : 0;
			$forms['registration']    = array(
				'name'     => esc_html__( 'Please Signup', 'armember-membership' ),
				'form_slug'=>'please-signup',
				'settings' => array(
					'style'         => $default_form_style,
					'redirect_type' => 'page',
					'redirect_page' => $reg_redirect_id,
					'auto_login'    => '1',
				),
				'fields'   => array( $user_login, $first_name, $last_name, $user_email, $user_pass_reg, $submit ),
			);
			$default_login_form_style = $this->arm_default_form_style_login();
			$loginSettings            = array(
				'registration_link_type'         => 'page',
				'registration_link_type_page'    => $register_page_id,
				'forgot_password_link_type'      => 'page',
				'forgot_password_link_type_page' => $forgot_password_page_id,
				'redirect_type'                  => 'page',
				'redirect_page'                  => $login_redirect_id,
				'style'                          => $default_login_form_style,
				'show_rememberme'                => '1',
				'show_registration_link'         => '1',
				'registration_link_label'        => '<center>Dont have account? [ARMLINK]SIGNUP[/ARMLINK]</center>',
				'show_forgot_password_link'      => '1',
				'forgot_password_link_label'     => esc_html__( 'Lost Your Password', 'armember-membership' ),
				'forgot_password_link_margin'    => array(
					'bottom' => '0',
					'top'    => '-132',
					'left'   => '315',
					'right'  => '0',
				),
				'registration_link_margin'       => array(
					'top'    => '0',
					'left'   => '0',
					'right'  => '0',
					'bottom' => '0',
				),
				'custom_css'                     => '',
			);
			$forms['login']           = array(
				'name'     => esc_html__( 'Please Login', 'armember-membership' ),
				'form_slug'=>'please-login',
				'settings' => $loginSettings,
				'fields'   => array( $user_login, $user_pass_login, $remember_me, $loginSubmit ),
			);
			$forms['forgot_password'] = array(
				'name'     => esc_html__( 'Forgot Password', 'armember-membership' ),
				'form_slug'=>'forgot-password',
				'settings' => array(
					'style'         => $default_login_form_style,
					'redirect_type' => 'message',
					'message'       => esc_html__( 'We have sent you a password reset link, Please check your mail.', 'armember-membership' ),
					'description'   => esc_html__( 'Please enter your email address or username below.', 'armember-membership' ),
				),
				'fields'   => array( $user_login_forgot_password, $submit ),
			);
			$forms['change_password'] = array(
				'name'     => esc_html__( 'Change Password', 'armember-membership' ),
				'form_slug'=>'change-password',
				'settings' => array(
					'style'         => $default_login_form_style,
					'redirect_type' => 'message',
					'message'       => esc_html__( 'Your password changed successfully.', 'armember-membership' ),
				),
				'fields'   => array($current_user_pass, $new_user_pass, $repeat_pass, $submit ),
			);
			$profile_first_name = array(
                'id' => 'first_name',
                'label' => esc_html__('First Name', 'armember-membership'),
                'placeholder' => '',
                'type' => 'text',
                'meta_key' => 'first_name', // phpcs:ignore
                'required' => 1,
                'hide_firstname' => 0,
                'blank_message' => esc_html__('First Name can not be left blank.', 'armember-membership'),
                'invalid_firstname' => esc_html__('This first name is invalid. Please enter a valid first name.', 'armember-membership'),
                'default_field' => 0,
            );
            $profile_last_name = array(
                'id' => 'last_name',
                'label' => esc_html__('Last Name', 'armember-membership'),
                'placeholder' => '',
                'type' => 'text',
                'meta_key' => 'last_name', // phpcs:ignore
                'required' => 1,
                'hide_lastname' => 0,
                'blank_message' => esc_html__('Last Name can not be left blank.', 'armember-membership'),
                'invalid_Lastname' => esc_html__('This last name is invalid. Please enter a valid last name.', 'armember-membership'),
                'default_field' => 0,
            );
            $profile_user_login = array(
                'id' => 'user_login',
                'label' => esc_html__('Username', 'armember-membership'),
                'placeholder' => '',
                'type' => 'text',
                'meta_key' => 'user_login', // phpcs:ignore
                'required' => 1,
                'hide_username' => 0,
                'blank_message' => esc_html__('Username can not be left blank.', 'armember-membership'),
                'invalid_message' => esc_html__('Please enter valid username.', 'armember-membership'),
                'invalid_username' => esc_html__('This username is invalid. Please enter a valid username.', 'armember-membership'),
                'default_field' => 0
            );

            $profile_user_email = array(
                'id' => 'user_email',
                'label' => esc_html__('Email Address', 'armember-membership'),
                'placeholder' => '',
                'type' => 'email',
                'meta_key' => 'user_email', // phpcs:ignore
                'required' => 1,
                'blank_message' => esc_html__('Email Address can not be left blank.', 'armember-membership'),
                'invalid_message' => esc_html__('Please enter valid email address.', 'armember-membership'),
                'default_field' => 0,
                'ref_field_id' => 0,
                'enable_repeat_field' => 0,
            );
            $profile_user_pass = array(
                'id' => 'user_pass',
                'label' => esc_html__('Password', 'armember-membership'),
                'placeholder' => '',
                'type' => 'password',
                'options' => array('strength_meter' => 1, 'strong_password' => 0, 'minlength' => 6, 'maxlength' => '', 'special' => 1, 'numeric' => 1, 'uppercase' => 1, 'lowercase' => 1),
                'meta_key' => 'user_pass', // phpcs:ignore
                'required' => 1,
                'blank_message' => esc_html__('Password can not be left blank.', 'armember-membership'),
                'invalid_message' => esc_html__('Please enter valid password.', 'armember-membership'),
            );
            $profile_submit = array(
                'id' => 'submit',
                'label' => esc_html__('Update Profile', 'armember-membership'),
                'type' => 'submit',
                'default_field' => 1
            );
			$forms['edit_profile'] = array(
                'name' => esc_html__('Edit Profile', 'armember-membership'),
				'form_slug'=>'edit-profile',
                'settings' => array(
                    'style' => $default_form_style,
                ),
                'fields' => array( $profile_user_login, $profile_first_name, $profile_last_name, $profile_user_email, $profile_user_pass, $profile_submit )
            );
			return $forms;
		}

		function arm_check_unique_set_name() {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs,$arm_capabilities_global;
			$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST ); //phpcs:ignore
			/* Check For unique set name starts */
			if ( isset( $posted_data['arm_set_name'] ) ) { //phpcs:ignore
				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_forms'], '1' );
				$setform_name = $wpdb->get_row( 'SELECT * FROM `' . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_set_name` LIKE '" . sanitize_text_field( $posted_data['arm_set_name'] ) . "' GROUP BY arm_set_id ORDER BY arm_form_id DESC Limit 0,1", ARRAY_A ); //phpcs:ignore
				if ( ! empty( $setform_name ) && count( $setform_name ) > 0 ) {
					echo 'false';
				} else {
					echo 'true';
				}
			}
			/*
			 Check For unique set name ends */
			/* Check For unique Signup form name starts */
			if ( isset( $posted_data['arm_form_name'] ) ) { //phpcs:ignore
				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_forms'], '1' );
				$setform_name = $wpdb->get_row( 'SELECT * FROM `' . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_form_label` LIKE '" . sanitize_text_field( $posted_data['arm_form_name'] ) . "' and `arm_form_type` = 'registration' GROUP BY arm_form_id ORDER BY arm_form_id DESC Limit 0,1", ARRAY_A ); //phpcs:ignore
				if ( ! empty( $setform_name ) && count( $setform_name ) > 0 ) {
					echo 'false';
				} else {
					echo 'true';
				}
			}
			/* Check For unique Signup form name ends */
			die;
		}


		/*
		 * Get all form data with form fields.
		 */

		function arm_get_default_form_id( $type = '' ) {
			global $wp, $wpdb, $ARMemberLite;
			$default_form_id = 0;
			if ( ! empty( $type ) ) {
				/* Query Monitor Change */
				if ( isset( $GLOBALS['arm_form_default_id'] ) && isset( $GLOBALS['arm_form_default_id'][ $type ] ) ) {
					$arm_form_id = $GLOBALS['arm_form_default_id'][ $type ];
				} else {
					$arm_form_id = $wpdb->get_var( $wpdb->prepare('SELECT `arm_form_id` FROM `' . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_form_type`=%s AND `arm_is_default`=%d",$type,1 ));//phpcs:ignore --Reason $ARMemberLite->tbl_arm_forms is a table name
					if ( ! isset( $GLOBALS['arm_form_default_id'] ) ) {
						$GLOBALS['arm_form_default_id'] = array();
					}
					$GLOBALS['arm_form_default_id'][ $type ] = $arm_form_id;
				}
				/* Query Monitor Change */
				$default_form_id = ( ! empty( $arm_form_id ) && $arm_form_id != 0 ) ? $arm_form_id : 0;
			}
			return $default_form_id;
		}

		function arm_get_default_form_id_by_label( $type = '', $label = '' ) {
			global $wp, $wpdb, $ARMemberLite;
			$default_form_id = 0;

			if ( ! empty( $type ) && ! empty( $label ) ) {
				$arm_form_id     = $wpdb->get_var( $wpdb->prepare('SELECT `arm_form_id` FROM `' . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_form_type`=%s AND BINARY `arm_form_label`=%s",$type,$label) );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_forms is a table name. False Positive alarm
				$default_form_id = ( ! empty( $arm_form_id ) && $arm_form_id != 0 ) ? $arm_form_id : 0;
			}
			return $default_form_id;
		}

		function arm_get_default_form_label( $type = '' ) {
			global $wp, $wpdb, $ARMemberLite;
			$default_form_label = '';
			if ( ! empty( $type ) ) {
				$arm_form_label     = $wpdb->get_var( $wpdb->prepare('SELECT `arm_form_label` FROM `' . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_form_type`=%s AND `arm_is_default`=%d",$type,1) );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_forms is a table name. False Positive alarm
				$default_form_label = ( ! empty( $arm_form_label ) && $arm_form_label != '' ) ? $arm_form_label : '';
			}
			return $default_form_label;
		}

		function arm_get_single_member_forms( $form_id = 0, $fields = 'all', $isFormFields = true ) {
			global $wp, $wpdb, $current_user, $ARMemberLite;
			$forms_data   = array();
			$selectFields = '*';
			if ( ! empty( $fields ) ) {
				if ( $fields != 'all' && $fields != '*' ) {
					$selectFields = $fields;
				}
			}
			if ( ! empty( $form_id ) && $form_id != 0 ) {
				$forms_data = $wpdb->get_row( $wpdb->prepare("SELECT {$selectFields}, `arm_form_id` FROM `" . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_form_id`=%d ORDER BY `arm_form_id` ASC LIMIT 1",$form_id), ARRAY_A );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_formsis a table name. False Positive alarm
				if ( ! empty( $forms_data ) ) {
					$forms_data['arm_form_label']    = ( ! empty( $forms_data['arm_form_label'] ) ) ? stripslashes( $forms_data['arm_form_label'] ) : '';
					$forms_data['arm_form_settings'] = ( ! empty( $forms_data['arm_form_settings'] ) ) ? maybe_unserialize( $forms_data['arm_form_settings'] ) : array();
					if ( $isFormFields ) {
						/* Get Form Fields */
						$forms_data['fields'] = $this->arm_get_member_forms_fields( $forms_data['arm_form_id'] );
					}
				}
			}
			return $forms_data;
		}

		function arm_get_other_member_forms( $set_id = 0 ) {
			global $wp, $wpdb, $current_user, $ARMemberLite;
			$forms_data = array();
			if ( ! empty( $set_id ) && $set_id != 0 ) {
				$form_result = $wpdb->get_results( $wpdb->prepare('SELECT * FROM `' . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_set_id`=%d ORDER BY `arm_form_id` ASC",$set_id), ARRAY_A );// phpcs:ignore --Reason $ARMemberLite->tbl_arm_forms is a table name. False Positive alarm	
				if ( ! empty( $form_result ) ) {
					foreach ( $form_result as $form ) {
						$id = $form['arm_form_id'];
						/* Get Form Fields */
						$form['arm_form_label']    = ( ! empty( $form['arm_form_label'] ) ) ? stripslashes( $form['arm_form_label'] ) : '';
						$form['arm_form_settings'] = ( ! empty( $form['arm_form_settings'] ) ) ? maybe_unserialize( $form['arm_form_settings'] ) : array();
						$login_regex               = '/template-login(.*?)/';
						$register_regex            = '/template-registration(.*?)/';
						$forgot_regex              = '/template-forgot-password(.*?)/';
						$changepass_regex          = '/template-change-password(.*?)/';
						preg_match( $login_regex, $form['arm_form_slug'], $match_login );
						preg_match( $register_regex, $form['arm_form_slug'], $match_register );
						preg_match( $forgot_regex, $form['arm_form_slug'], $match_forgot );
						preg_match( $changepass_regex, $form['arm_form_slug'], $match_changepass );

						if ( isset( $match_login[0] ) && ! empty( $match_login[0] ) ) {
							$form['arm_form_type'] = 'login';
						} elseif ( isset( $match_register[0] ) && ! empty( $match_register[0] ) ) {
							$form['arm_form_type'] = 'registration';
						} elseif ( isset( $match_forgot[0] ) && ! empty( $match_forgot[0] ) ) {
							$form['arm_form_type'] = 'forgot_password';
						} elseif ( isset( $match_changepass[0] ) && ! empty( $match_changepass[0] ) ) {
							$form['arm_form_type'] = 'change_password';
						}

						$form['fields']    = $this->arm_get_member_forms_fields( $id );
						$forms_data[ $id ] = $form;
					}
				}
			}
			return $forms_data;
		}

		function arm_get_member_form_sets() {
			global $wp, $wpdb, $current_user, $ARMemberLite;
			$set_data    = array();
			$form_result = $wpdb->get_results( $wpdb->prepare('SELECT * FROM `' . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_set_id`!=%d AND `arm_is_template` = %d ORDER BY `arm_set_id` ASC LIMIT 0,3",0,0), ARRAY_A );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_forms is a table name. False Positive Alarm
			if ( ! empty( $form_result ) ) {
				foreach ( $form_result as $form ) {
					$id     = $form['arm_form_id'];
					$set_id = $form['arm_set_id'];
					/* Get Form Fields */
					$form['arm_form_label']     = ( ! empty( $form['arm_form_label'] ) ) ? stripslashes( $form['arm_form_label'] ) : '';
					$form['arm_form_settings']  = ( ! empty( $form['arm_form_settings'] ) ) ? maybe_unserialize( $form['arm_form_settings'] ) : array();
					$set_data[ $set_id ][ $id ] = $form;
				}
			}
			return $set_data;
		}

		function arm_get_member_forms_by_type( $type = '', $isFormFields = true ) {
			global $wp, $wpdb, $current_user, $ARMemberLite;
			$forms_data = array();
			if ( ! empty( $type ) && $type != '' ) {
				$form_result = $wpdb->get_results( $wpdb->prepare('SELECT * FROM `' . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_form_type`=%s ORDER BY `arm_form_id` ASC LIMIT 0,1",$type), ARRAY_A );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_forms is a table name. False Positive Alarm
				if ( ! empty( $form_result ) ) {
					foreach ( $form_result as $form ) {
						$id = $form['arm_form_id'];
						/* Get Form Fields */
						$form['arm_form_label']    = ( ! empty( $form['arm_form_label'] ) ) ? stripslashes( $form['arm_form_label'] ) : '';
						$form['arm_form_settings'] = ( ! empty( $form['arm_form_settings'] ) ) ? maybe_unserialize( $form['arm_form_settings'] ) : array();
						if ( $isFormFields ) {
							$form['fields'] = $this->arm_get_member_forms_fields( $id );
						}
						$forms_data[ $id ] = $form;
					}
				}
			}
			return $forms_data;
		}

		function arm_get_member_forms_and_fields_by_type( $type = '', $fields = 'all', $isFormFields = true ) {
			global $wp, $wpdb, $current_user, $ARMemberLite;
			$forms_data   = array();
			$selectFields = '*';
			if ( ! empty( $fields ) ) {
				if ( $fields != 'all' && $fields != '*' ) {
					$selectFields = $fields;
				}
			}
			if ( ! empty( $type ) && $type != '' ) {
				$form_result = $wpdb->get_results( $wpdb->prepare("SELECT {$selectFields} FROM `" . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_form_type`=%s ORDER BY `arm_form_id` DESC",$type), ARRAY_A );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_forms is a table name. False Positive alarm
				if ( ! empty( $form_result ) ) {
					foreach ( $form_result as $form ) {
						$id = $form['arm_form_id'];
						/* Get Form Fields */
						$form['arm_form_label']    = ( ! empty( $form['arm_form_label'] ) ) ? stripslashes( $form['arm_form_label'] ) : '';
						$form['arm_form_settings'] = ( ! empty( $form['arm_form_settings'] ) ) ? maybe_unserialize( $form['arm_form_settings'] ) : array();
						if ( $isFormFields ) {
							$form['fields'] = $this->arm_get_member_forms_fields( $id );
						}
						$forms_data[ $id ] = $form;
					}
				}
			}
			return $forms_data;
		}

		function arm_get_all_member_forms( $fields = 'all', $isFormFields = false ) {
			global $wp, $wpdb, $current_user, $ARMemberLite;
			$forms_data   = array();
			$selectFields = '*';
			if ( ! empty( $fields ) ) {
				if ( $fields != 'all' && $fields != '*' ) {
					$selectFields = $fields;
				}
			}
			$form_result = $wpdb->get_results( $wpdb->prepare("SELECT {$selectFields}, `arm_form_id` FROM `" . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_form_type` NOT LIKE %s AND arm_form_id in(101,102,103,104) ORDER BY `arm_form_id` DESC",'template'), ARRAY_A );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_forms is a tale name. False Positive Alarm.
			if ( ! empty( $form_result ) ) {
				foreach ( $form_result as $form ) {
					$id                        = $form['arm_form_id'];
					$form['arm_form_label']    = ( ! empty( $form['arm_form_label'] ) ) ? stripslashes( $form['arm_form_label'] ) : '';
					$form['arm_form_settings'] = ( ! empty( $form['arm_form_settings'] ) ) ? maybe_unserialize( $form['arm_form_settings'] ) : array();
					if ( $isFormFields ) {
						/* Get Form Fields */
						$form['fields'] = $this->arm_get_member_forms_fields( $id );
					}
					$forms_data[ $id ] = $form;
				}
			}
			return $forms_data;
		}

		/*
		 * Get Form Fields by form id.
		 */

		function arm_get_member_forms_fields( $form_id = '', $columns = 'all' ) {
			global $wp, $wpdb, $current_user, $ARMemberLite;
			$fields        = array();
			$selectColumns = '*';
			if ( ! empty( $columns ) ) {
				if ( $columns != 'all' && $columns != '*' ) {
					$selectColumns = $columns;
				}
			}
			if ( ! empty( $form_id ) && $form_id != 0 ) {
				$field_result = $wpdb->get_results( $wpdb->prepare("SELECT {$selectColumns}, `arm_form_field_id`, `arm_form_field_form_id` FROM `" . $ARMemberLite->tbl_arm_form_field . "` WHERE `arm_form_field_form_id`=%d AND `arm_form_field_status` != %d ORDER BY `arm_form_field_order` ASC",$form_id,2), ARRAY_A );//phpcs:ignore --Reason:  $ARMemberLite->tbl_arm_form_field is a table name false Positive Alarm
				foreach ( $field_result as $field ) {
					$field['arm_form_field_option'] = ( isset( $field['arm_form_field_option'] ) ) ? maybe_unserialize( $field['arm_form_field_option'] ) : array();
					$fields[]                       = $field;
				}
			}
			return $fields;
		}

		function save_member_forms() {
			global $wp, $wpdb, $current_user, $ARMemberLite, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_forms'], '1' );  //phpcs:ignore --Reason:Verifying nonce

			unset( $_POST['no_field'] ); //phpcs:ignore
			$posted_data  = $_POST; //phpcs:ignore
			$posted_data  = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend'), $posted_data ); //phpcs:ignore
			
			$arm_action   = sanitize_text_field( $posted_data['arm_action'] );
			$arm_form_ids = ( isset( $posted_data['arm_login_form_ids'] ) && $posted_data['arm_login_form_ids'] !== '' ) ? explode( ',', $posted_data['arm_login_form_ids'] ) : '';
			$arm_ref_form = isset( $posted_data['arm_ref_template'] ) ? intval( $posted_data['arm_ref_template'] ) : 0;
			unset( $posted_data['arm_ignore'] );
			$i = 0;
			foreach ( $posted_data['arm_forms'] as $tmp_form_id => $tmp_form ) {
				if ( $arm_action == 'edit_form' && ( $tmp_form['arm_form_type'] == 'registration' || $tmp_form['arm_form_type'] == 'edit_profile' ) ) {
					$new_form_id = $posted_data['arm_form_id'];
					$wpdb->query( $wpdb->prepare('DELETE FROM ' . $ARMemberLite->tbl_arm_form_field . ' WHERE `arm_form_field_form_id` = %d', $new_form_id ));//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_form_field is a table name. False Positive Alarm
				}
				if ( $arm_action == 'edit_form' && ( $tmp_form['arm_form_type'] != 'registration' && $tmp_form['arm_form_type'] != 'edit_profile' ) ) {
					$wpdb->query( $wpdb->prepare('DELETE FROM ' . $ARMemberLite->tbl_arm_form_field . ' WHERE `arm_form_field_form_id` = %d' , $arm_form_ids[ $i ]) );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_form_field is a table name. False Positive Alarm
				}
				$i++;
			}
			unset( $i );

			/* Save form & field settings option. */
			if ( ! empty( $posted_data['arm_forms'] ) ) {
				$arm_form_settings = array();
				if ( ! empty( $posted_data['arm_form_settings'] ) && ! empty( $posted_data['arm_form_settings'] ) ) {
					$arm_form_settings = $posted_data['arm_form_settings'];
					unset( $arm_form_settings['change_password'] );
					unset( $arm_form_settings['forgot_password'] );
				}
				if ( $arm_action == 'new_form' || $arm_action == 'duplicate_form' ) {
					$max_set_id = $wpdb->get_row( $wpdb->prepare('SELECT MAX(arm_set_id) as arm_set_id FROM ' . $ARMemberLite->tbl_arm_forms) );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_forms is a table name. False Positive alarm
					$set_id     = ( (int) $max_set_id->arm_set_id + 1 );
				} else {
					$set_id = isset( $posted_data['form_set_id'] ) ? intval( $posted_data['form_set_id'] ) : 0;
				}
				$x              = 0;
				$login_form_ids = array();

				foreach ( $posted_data['arm_forms'] as $form_id => $form_data ) {
					$formType = $form_data['arm_form_type'];
					if ( ! in_array( $formType, array( 'registration', 'edit_profile', 'login' ) ) ) {
						if ( $formType == 'change_password' ) {
							unset( $arm_form_settings['forgot_password'] );
							$arm_form_settings['redirect_type'] = 'message';
							$arm_form_settings['message']       = isset( $posted_data['arm_form_settings']['change_password']['message'] ) ? sanitize_text_field( $posted_data['arm_form_settings']['change_password']['message'] ) : '';
						}
						if ( $formType == 'forgot_password' ) {
							unset( $arm_form_settings['change_password'] );
							$arm_form_settings['redirect_type'] = 'message';
							$arm_form_settings['message']       = isset( $posted_data['arm_form_settings']['forgot_password']['message'] ) ? sanitize_text_field( $posted_data['arm_form_settings']['forgot_password']['message'] ) : '';
							$arm_form_settings['description']   = isset( $posted_data['arm_form_settings']['forgot_password']['description'] ) ? $posted_data['arm_form_settings']['forgot_password']['description'] : '';
						}
					}
					if ( isset( $arm_form_settings['hidden_fields'] ) && ! empty( $arm_form_settings['hidden_fields'] ) ) {
						foreach ( $arm_form_settings['hidden_fields'] as $hkey => $hiddenField ) {
							$hiddenField['meta_key']                     = ( isset( $hiddenField['meta_key'] ) && ! empty( $hiddenField['meta_key'] ) ) ? sanitize_text_field( $hiddenField['meta_key'] ) : sanitize_title( 'arm_hidden_' . $hiddenField['title'] ); // phpcs:ignore
							$arm_form_settings['hidden_fields'][ $hkey ] = $hiddenField;
							if ( empty( $hiddenField['title'] ) && empty( $hiddenField['value'] ) ) {
								unset( $arm_form_settings['hidden_fields'][ $hkey ] );
							}
						}
					}
					if ( $arm_form_settings['style']['form_layout'] == 'writer_border' ) {
						$arm_form_settings['style']['field_height'] = '52';
					} else {
						$arm_form_settings['style']['field_height'] = '44';
					}
					$update_form_data = array(
						'arm_form_label'        => $form_data['arm_form_label'],
						'arm_form_title'        => $form_data['arm_form_title'],
						'arm_form_type'         => $formType,
						'arm_ref_template'      => $arm_ref_form,
						'arm_set_id'            => $set_id,
						'arm_form_settings'     => maybe_serialize( $arm_form_settings ),
						'arm_form_updated_date' => current_time( 'mysql' ),
					);
					/* Insert Form Data */
					if ( $arm_action == 'edit_form' ) {
						if ( $formType == 'registration' || 'edit_profile' == $formType ) {
							$form_update = $wpdb->update( $ARMemberLite->tbl_arm_forms, $update_form_data, array( 'arm_form_id' => $new_form_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						} else {
							$frm_id      = $arm_form_ids[ $x ];
							$form_update = $wpdb->update( $ARMemberLite->tbl_arm_forms, $update_form_data, array( 'arm_form_id' => $frm_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
							array_push( $login_form_ids, $frm_id );
						}
					} else {
						$new_form_slug                     = sanitize_title( $form_data['arm_form_title'] );
						$check_form                        = new ARM_Form_Lite( 'slug', $new_form_slug );
						$new_form_slug                     = $new_form_slug . '-' . arm_generate_random_code( 3 );
						$update_form_data['arm_form_slug'] = $new_form_slug;
						$update_form_data['arm_set_name']  = sanitize_text_field( $posted_data['arm_new_set_name'] );
						if ( $formType == 'registration' || $formType == 'edit_profile' ) {
							$update_form_data['arm_set_id']     = 0;
							$update_form_data['arm_form_label'] = sanitize_text_field( $posted_data['arm_new_set_name'] );
						}
						$form_update = $wpdb->insert( $ARMemberLite->tbl_arm_forms, $update_form_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						$form_id     = $wpdb->insert_id;
						array_push( $login_form_ids, $form_id );
					}

					/* Unset Form Detail after update. */
					unset( $form_data['arm_form_label'] );
					unset( $form_data['arm_form_title'] );
					unset( $form_data['arm_form_type'] );
					unset( $form_data['arm_form_slug'] );
					unset( $form_data['arm_form_settings'] );
					if ( false === $form_update ) {
						/* Error in saving details. */
					} else {
						$i = 1;
						/* Delete Fields which is remove from editor */
						$deleted_fields = $wpdb->delete( $ARMemberLite->tbl_arm_form_field, array( 'arm_form_field_status' => 0 ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						foreach ( $form_data as $field_id => $field_data ) {
							if ( isset( $field_data['type'] ) && in_array( $field_data['type'], array( 'checkbox', 'radio', 'select' ) ) ) {
								$options     = array_map( 'trim', explode( "\n", $field_data['options'] ) );
								$new_options = array();
								foreach ( $options as $data ) {
									if ( $data != '' ) {
										$new_options[] = $data;
									}
								}
								$field_data['options'] = $new_options;
							}
							/* Make Lowercase meta key */
							$field_data['label']              = isset( $field_data['label'] ) ? esc_attr( $field_data['label'] ) : '';
							$field_data['meta_key']           = isset( $field_data['meta_key'] ) ? sanitize_title( strtolower( $field_data['meta_key'] ) ) : ''; // phpcs:ignore
							$field_data['readonly']           = isset( $field_data['readonly'] ) ? sanitize_title( strtolower( $field_data['readonly'] ) ) : '';
							$field_data['regular_expression'] = isset( $field_data['regular_expression'] ) ? stripslashes_deep( $field_data['regular_expression'] ) : '';

							if ( $field_data['type'] == 'radio' && empty( $field_data['default_val'] ) ) {
								$field_data['default_val'] = 'false';
							}
							if ( $field_data['type'] == 'password' && in_array( $formType, array( 'registration', 'edit_profile', 'change_password' ) ) ) {
								$field_data['options']['veryweaktext'] = isset( $field_data['options']['veryweaktext'] ) ? esc_attr( $field_data['options']['veryweaktext'] ) : '';
								$field_data['options']['weaktext']     = isset( $field_data['options']['weaktext'] ) ? esc_attr( $field_data['options']['weaktext'] ) : '';
								$field_data['options']['goodtext']     = isset( $field_data['options']['goodtext'] ) ? esc_attr( $field_data['options']['goodtext'] ) : '';
								$field_data['options']['strongtext']   = isset( $field_data['options']['strongtext'] ) ? esc_attr( $field_data['options']['strongtext'] ) : '';
							}
							$save_field_data = array(
								'arm_form_field_order'  => $i,
								'arm_form_field_slug'   => $field_data['meta_key'],
								'arm_form_field_option' => maybe_serialize( $field_data ),
								'arm_form_field_bp_field_id' => ( isset( $field_data['mapfield'] ) && $field_data['mapfield'] != '' ) ? $field_data['mapfield'] : 0,
								'arm_form_field_status' => 1,
								'arm_form_field_created_date' => current_time( 'mysql' ),
							);
							if ( $formType == 'registration' || $formType == 'edit_profile' ) {
								$save_field_data['arm_form_field_form_id'] = ( $arm_action == 'edit_form' ) ? intval( $posted_data['arm_form_id'] ) : $form_id;
							} else {
								$save_field_data['arm_form_field_form_id'] = $login_form_ids[ $x ];
							}

							$wpdb->insert( $ARMemberLite->tbl_arm_form_field, $save_field_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
							$field_id = $wpdb->insert_id;
							if ( $formType == 'registration' || $formType == 'edit_profile' ) {
								if ( $arm_action == 'edit_form' ) {
									$this->arm_db_add_form_field( $field_data, $field_id, $new_form_id );
								} else {
									$this->arm_db_add_form_field( $field_data, $field_id, $form_id );
								}
							}
							$i++;
						}
					}
					$x++;
				}
			}
			if ( $formType == 'registration' || $formType == 'edit_profile' || $formType == 'change_password' ) {
				$form_fields_stored = $this->arm_get_member_forms_fields( $form_id );
				if ( count( $form_fields_stored ) > 0 ) {
					global $password_field_id, $email_field_id;
					foreach ( $form_fields_stored as $key => $field_data ) {
						$enable_repeat_field = isset( $field_data['arm_form_field_option']['enable_repeat_field'] ) ? $field_data['arm_form_field_option']['enable_repeat_field'] : '0';
						if ( $field_data['arm_form_field_option']['type'] == 'email' && $enable_repeat_field == '1' ) {
							$email_field_id[ $field_data['arm_form_field_order'] ] = $field_data['arm_form_field_id'];
						}
						if ( $field_data['arm_form_field_option']['type'] == 'password' && ( $enable_repeat_field == '1' || $formType == 'change_password' ) ) {
							$password_field_id[ $field_data['arm_form_field_order'] ] = $field_data['arm_form_field_id'];
						}
					}
					foreach ( $form_fields_stored as $key => $field_data ) {
                        if ($field_data['arm_form_field_option']['type'] == 'current_user_pass') {
                            $field_id = $field_data['arm_form_field_id'];
                            $field_order = $field_data['arm_form_field_order'];
                            $field_data['arm_form_field_option']['ref_field_id'] = isset($password_field_id[$field_order - 1]) ? $password_field_id[$field_order - 1] : 0;
                            $field_data['arm_form_field_option']['required']=1;
                            $field_options = maybe_serialize($field_data['arm_form_field_option']);
                            $wpdb->update($ARMemberLite->tbl_arm_form_field, array('arm_form_field_option' => $field_options), array('arm_form_field_id' => $field_id)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                        }
						if ( $field_data['arm_form_field_option']['type'] == 'repeat_pass' ) {
							$field_id    = $field_data['arm_form_field_id'];
							$field_order = $field_data['arm_form_field_order'];
							$field_data['arm_form_field_option']['ref_field_id'] = isset( $password_field_id[ $field_order - 1 ] ) ? $password_field_id[ $field_order - 1 ] : 0;
							$field_options                                       = maybe_serialize( $field_data['arm_form_field_option'] );
							$wpdb->update( $ARMemberLite->tbl_arm_form_field, array( 'arm_form_field_option' => $field_options ), array( 'arm_form_field_id' => $field_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						}
						if ( $field_data['arm_form_field_option']['type'] == 'repeat_email' ) {
							$field_id    = $field_data['arm_form_field_id'];
							$field_order = $field_data['arm_form_field_order'];
							$field_data['arm_form_field_option']['ref_field_id'] = isset( $email_field_id[ $field_order - 1 ] ) ? $email_field_id[ $field_order - 1 ] : 0;
							$field_options                                       = maybe_serialize( $field_data['arm_form_field_option'] );
							$wpdb->update( $ARMemberLite->tbl_arm_form_field, array( 'arm_form_field_option' => $field_options ), array( 'arm_form_field_id' => $field_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						}
					}
				}
			}

			$final_response                 = array(
				'message'   => 'success',
				'form_id'   => $form_id,
				'form_type' => $formType,
			);
			$final_response['arm_form_set'] = $set_id;
			if ( $formType != 'registration' && $formType != 'edit_profile' ) {
				$final_response['form_ids'] = implode( ',', $login_form_ids );
			}
			echo wp_json_encode( $final_response );
			die();
		}

		/**
		 * Default Form Style
		 */
		function arm_default_form_style() {
			return array(
				'form_bg'                     => '',
				'form_width'                  => '550',
				'form_width_type'             => 'px',
				'form_border_width'           => '2',
				'form_border_radius'          => '12',
				'form_border_style'           => 'solid',
				'form_layout'                 => 'writer_border',
				'form_opacity'                => '1',
				'form_padding_top'            => '40',
				'form_padding_right'          => '30',
				'form_padding_bottom'         => '40',
				'form_padding_left'           => '30',
				'form_title_font_family'      => 'Poppins',
				'form_title_font_size'        => '24',
				'form_title_font_bold'        => '1',
				'form_title_font_italic'      => '0',
				'form_title_font_decoration'  => '',
				'form_title_position'         => 'center',
				'form_position'               => 'center',
				'validation_position'         => 'bottom',
				'rtl'                         => 0,
				'color_scheme'                => 'blue',
				'main_color'                  => '#005AEE',
				'form_title_font_color'       => '#1A2538',
				'lable_font_color'            => '#1A2538',
				'field_font_color'            => '#2F3F5C',
				'field_border_color'          => '#D3DEF0',
				'field_focus_color'           => '#637799',
				'field_bg_color'              => '#ffffff',
				'button_back_color'           => '#005AEE',
				'button_back_color_gradient'  => '#363795',
				'button_font_color'           => '#FFFFFF',
				'button_hover_color'          => '#0D54C9',
				'button_hover_color_gradient' => '#363795',
				'button_hover_font_color'     => '#ffffff',
				'login_link_font_color'       => '#005AEE',
				'register_link_font_color'    => '#005AEE',
				'form_bg_color'               => '#FFFFFF',
				'form_border_color'           => '#E6E7F5',
				'prefix_suffix_color'         => '#bababa',
				'error_font_color'            => '#FF3B3B',
				'error_field_border_color'    => '#FF3B3B',
				'error_field_bg_color'        => '#ffffff',
				'field_width'                 => '100',
				'field_width_type'            => '%',
				'field_height'                => '52',
				'field_spacing'               => '18',
				'field_border_width'          => '1',
				'field_border_radius'         => '0',
				'field_border_style'          => 'solid',
				'field_position'              => 'left',
				'field_font_family'           => 'Poppins',
				'field_font_size'             => '15',
				'field_font_bold'             => '0',
				'field_font_italic'           => '0',
				'field_font_decoration'       => '',
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
				'button_style'                => 'flat',
				'button_font_family'          => 'Poppins',
				'button_font_size'            => '15',
				'button_font_bold'            => '0',
				'button_font_italic'          => '0',
				'button_font_decoration'      => '',
				'button_margin_top'           => '10',
				'button_margin_right'         => '0',
				'button_margin_bottom'        => '0',
				'button_margin_left'          => '0',
				'button_position'             => 'center',
				'enable_social_btn_separator' => '',
				'social_btn_separator'        => '<center>' . esc_html__( 'OR', 'armember-membership' ) . '</center>',
				'social_btn_position'         => 'bottom',
				'social_btn_type'             => 'horizontal',
				'social_btn_align'            => 'center',
			);
		}

		function arm_default_form_style_login() {
			$defaultLoginFormStyle                      = $this->arm_default_form_style();
			$defaultLoginFormStyle['form_width']        = '550';
			$defaultLoginFormStyle['form_width_type']   = 'px';
			$defaultLoginFormStyle['form_border_width'] = '2';
			return $defaultLoginFormStyle;
		}

		function arm_form_color_schemes() {
			$mainColors = array(
				'blue'        => array(
					'main_color'                  => '#005AEE',
					'form_title_font_color'       => '#1A2538',
					'lable_font_color'            => '#1A2538',
					'field_font_color'            => '#2F3F5C',
					'field_border_color'          => '#D3DEF0',
					'field_focus_color'           => '#637799',
					'field_bg_color'              => '#ffffff',
					'button_back_color'           => '#005AEE',
					'button_back_color_gradient'  => '#363795',
					'button_font_color'           => '#FFFFFF',
					'button_hover_color'          => '#0D54C9',
					'button_hover_color_gradient' => '#363795',
					'button_hover_font_color'     => '#ffffff',
					'login_link_font_color'       => '#005AEE',
					'register_link_font_color'    => '#005AEE',
					'form_bg_color'               => '#FFFFFF',
					'prefix_suffix_color'         => '#bababa',
					'error_font_color'            => '#ffffff',
					'error_field_border_color'    => '#FF3B3B',
					'error_field_bg_color'        => '#FF3B3B',
				),
				'bright_cyan' => array(
					'main_color'                  => '#23b7e5',
					'form_title_font_color'       => '#555555',
					'lable_font_color'            => '#919191',
					'field_font_color'            => '#242424',
					'field_border_color'          => '#c7c7c7',
					'field_focus_color'           => '#23b7e5',
					'field_bg_color'              => '#ffffff',
					'button_back_color'           => '#23b7e5',
					'button_back_color_gradient'  => '#5691c8',
					'button_font_color'           => '#ffffff',
					'button_hover_color'          => '#25c0f0',
					'button_hover_font_color'     => '#ffffff',
					'button_hover_color_gradient' => '#5691c8',
					'login_link_font_color'       => '#23b7e5',
					'register_link_font_color'    => '#23b7e5',
					'form_bg_color'               => '#ffffff',
					'prefix_suffix_color'         => '#bababa',
					'error_font_color'            => '#ffffff',
					'error_field_border_color'    => '#f05050',
					'error_field_bg_color'        => '#e6594d',
				),
				'green'       => array(
					'main_color'                  => '#27c24c',
					'form_title_font_color'       => '#313131',
					'lable_font_color'            => '#919191',
					'field_font_color'            => '#242424',
					'field_border_color'          => '#c7c7c7',
					'field_focus_color'           => '#27c24c',
					'field_bg_color'              => '#ffffff',
					'button_back_color'           => '#27c24c',
					'button_back_color_gradient'  => '#8DC26F',
					'button_font_color'           => '#ffffff',
					'button_hover_color'          => '#29cc50',
					'button_hover_color_gradient' => '#8DC26F',
					'button_hover_font_color'     => '#ffffff',
					'login_link_font_color'       => '#27c24c',
					'register_link_font_color'    => '#27c24c',
					'form_bg_color'               => '#ffffff',
					'prefix_suffix_color'         => '#bababa',
					'error_font_color'            => '#ffffff',
					'error_field_border_color'    => '#f05050',
					'error_field_bg_color'        => '#e6594d',
				),
				'red'         => array(
					'main_color'                  => '#fd4343',
					'form_title_font_color'       => '#313131',
					'lable_font_color'            => '#919191',
					'field_font_color'            => '#242424',
					'field_border_color'          => '#c7c7c7',
					'field_focus_color'           => '#fd4343',
					'field_bg_color'              => '#ffffff',
					'button_back_color'           => '#fd4343',
					'button_back_color_gradient'  => '#FF512F',
					'button_font_color'           => '#ffffff',
					'button_hover_color'          => '#fc3535',
					'button_hover_color_gradient' => '#FF512F',
					'button_hover_font_color'     => '#ffffff',
					'login_link_font_color'       => '#fd4343',
					'register_link_font_color'    => '#fd4343',
					'form_bg_color'               => '#ffffff',
					'prefix_suffix_color'         => '#bababa',
					'error_font_color'            => '#ffffff',
					'error_field_border_color'    => '#f05050',
					'error_field_bg_color'        => '#e6594d',
				),
				'purple'      => array(
					'main_color'                  => '#6164c1',
					'form_title_font_color'       => '#313131',
					'lable_font_color'            => '#919191',
					'field_font_color'            => '#242424',
					'field_border_color'          => '#c7c7c7',
					'field_focus_color'           => '#6164c1',
					'field_bg_color'              => '#ffffff',
					'button_back_color'           => '#6164c1',
					'button_back_color_gradient'  => '#348AC7',
					'button_font_color'           => '#ffffff',
					'button_hover_color'          => '#8072cc',
					'button_hover_color_gradient' => '#348AC7',
					'button_hover_font_color'     => '#ffffff',
					'login_link_font_color'       => '#6164c1',
					'register_link_font_color'    => '#6164c1',
					'form_bg_color'               => '#ffffff',
					'prefix_suffix_color'         => '#bababa',
					'error_font_color'            => '#ffffff',
					'error_field_border_color'    => '#f05050',
					'error_field_bg_color'        => '#e6594d',
				),
				'orange'      => array(
					'main_color'                  => '#ff8400',
					'form_title_font_color'       => '#313131',
					'lable_font_color'            => '#919191',
					'field_font_color'            => '#242424',
					'field_border_color'          => '#c7c7c7',
					'field_focus_color'           => '#ff8400',
					'field_bg_color'              => '#ffffff',
					'button_back_color'           => '#ff8400',
					'button_back_color_gradient'  => '#ffc500',
					'button_font_color'           => '#ffffff',
					'button_hover_color'          => '#fd901c',
					'button_hover_color_gradient' => '#ffc500',
					'button_hover_font_color'     => '#ffffff',
					'login_link_font_color'       => '#ff8400',
					'register_link_font_color'    => '#ff8400',
					'form_bg_color'               => '#ffffff',
					'prefix_suffix_color'         => '#bababa',
					'error_font_color'            => '#ffffff',
					'error_field_border_color'    => '#f05050',
					'error_field_bg_color'        => '#e6594d',
				),
				'yellow'      => array(
					'main_color'                  => '#ffce3a',
					'form_title_font_color'       => '#313131',
					'lable_font_color'            => '#919191',
					'field_font_color'            => '#242424',
					'field_border_color'          => '#c7c7c7',
					'field_focus_color'           => '#ffb400',
					'field_bg_color'              => '#ffffff',
					'button_back_color'           => '#ffb400',
					'button_back_color_gradient'  => '#EDDE5D',
					'button_font_color'           => '#ffffff',
					'button_hover_color'          => '#fdbc20',
					'button_hover_color_gradient' => '#EDDE5D',
					'button_hover_font_color'     => '#ffffff',
					'login_link_font_color'       => '#ffb400',
					'register_link_font_color'    => '#ffb400',
					'form_bg_color'               => '#ffffff',
					'prefix_suffix_color'         => '#bababa',
					'error_font_color'            => '#ffffff',
					'error_field_border_color'    => '#f05050',
					'error_field_bg_color'        => '#e6594d',
				),
				'pink'        => array(
					'main_color'                  => '#eb3573',
					'form_title_font_color'       => '#313131',
					'lable_font_color'            => '#919191',
					'field_font_color'            => '#242424',
					'field_border_color'          => '#c7c7c7',
					'field_focus_color'           => '#eb3573',
					'field_bg_color'              => '#ffffff',
					'button_back_color'           => '#eb3573',
					'button_back_color_gradient'  => '#ff5858',
					'button_font_color'           => '#ffffff',
					'button_hover_color'          => '#f8387a',
					'button_hover_color_gradient' => '#ff5858',
					'button_hover_font_color'     => '#ffffff',
					'login_link_font_color'       => '#eb3573',
					'register_link_font_color'    => '#eb3573',
					'form_bg_color'               => '#ffffff',
					'prefix_suffix_color'         => '#bababa',
					'error_font_color'            => '#ffffff',
					'error_field_border_color'    => '#f05050',
					'error_field_bg_color'        => '#e6594d',
				),
				'strong_cyan' => array(
					'main_color'                  => '#00c9b6',
					'form_title_font_color'       => '#313131',
					'lable_font_color'            => '#919191',
					'field_font_color'            => '#242424',
					'field_border_color'          => '#c7c7c7',
					'field_focus_color'           => '#00c9b6',
					'field_bg_color'              => '#ffffff',
					'button_back_color'           => '#00c9b6',
					'button_back_color_gradient'  => '#185a9d',
					'button_font_color'           => '#ffffff',
					'button_hover_color'          => '#01d7c3',
					'button_hover_color_gradient' => '#185a9d',
					'button_hover_font_color'     => '#ffffff',
					'login_link_font_color'       => '#00c9b6',
					'register_link_font_color'    => '#00c9b6',
					'form_bg_color'               => '#ffffff',
					'prefix_suffix_color'         => '#bababa',
					'error_font_color'            => '#ffffff',
					'error_field_border_color'    => '#f05050',
					'error_field_bg_color'        => '#e6594d',
				),
				'gray'        => array(
					'main_color'                  => '#858585',
					'form_title_font_color'       => '#313131',
					'lable_font_color'            => '#919191',
					'field_font_color'            => '#242424',
					'field_border_color'          => '#c7c7c7',
					'field_focus_color'           => '#858585',
					'field_bg_color'              => '#ffffff',
					'button_back_color'           => '#858585',
					'button_back_color_gradient'  => '#859398',
					'button_font_color'           => '#ffffff',
					'button_hover_color'          => '#919191',
					'button_hover_color_gradient' => '#859398',
					'button_hover_font_color'     => '#ffffff',
					'login_link_font_color'       => '#858585',
					'register_link_font_color'    => '#858585',
					'form_bg_color'               => '#ffffff',
					'prefix_suffix_color'         => '#bababa',
					'error_font_color'            => '#ffffff',
					'error_field_border_color'    => '#f05050',
					'error_field_bg_color'        => '#e6594d',
				),
				'dark_purple' => array(
					'main_color'                  => '#5a5779',
					'form_title_font_color'       => '#313131',
					'lable_font_color'            => '#919191',
					'field_font_color'            => '#242424',
					'field_border_color'          => '#c7c7c7',
					'field_focus_color'           => '#5a5779',
					'field_bg_color'              => '#ffffff',
					'button_back_color'           => '#5a5779',
					'button_back_color_gradient'  => '#F8CDDA',
					'button_font_color'           => '#ffffff',
					'login_link_font_color'       => '#5a5779',
					'register_link_font_color'    => '#5a5779',
					'button_hover_color'          => '#636086',
					'button_hover_color_gradient' => '#F8CDDA',
					'button_hover_font_color'     => '#ffffff',
					'form_bg_color'               => '#ffffff',
					'prefix_suffix_color'         => '#bababa',
					'error_font_color'            => '#ffffff',
					'error_field_border_color'    => '#f05050',
					'error_field_bg_color'        => '#e6594d',
				),
				'black'       => array(
					'main_color'                  => '#1a1a1a',
					'form_title_font_color'       => '#313131',
					'lable_font_color'            => '#919191',
					'field_font_color'            => '#242424',
					'field_border_color'          => '#404040',
					'field_focus_color'           => '#000000',
					'field_bg_color'              => '#ffffff',
					'button_back_color'           => '#000000',
					'button_back_color_gradient'  => '#414345',
					'button_font_color'           => '#ffffff',
					'button_hover_color'          => '#2c2c2c',
					'button_hover_color_gradient' => '#414345',
					'button_hover_font_color'     => '#ffffff',
					'login_link_font_color'       => '#000000',
					'register_link_font_color'    => '#000000',
					'form_bg_color'               => '#ffffff',
					'prefix_suffix_color'         => '#bababa',
					'error_font_color'            => '#ffffff',
					'error_field_border_color'    => '#f05050',
					'error_field_bg_color'        => '#e6594d',
				),
			);
			return $mainColors;
		}

		function arm_ajax_generate_form_styles( $form_id = 0, $form_settings = array(), $atts = array(), $ref_form_id = 0 ) {
			global $ARMemberLite, $wpdb, $arm_capabilities_global;

			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_ajax_generate_form_styles' ) { //phpcs:ignore
				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_forms'], '1' ); //phpcs:ignore --Reason:Verifying nonce
			}

			$form_id                  = ( isset( $_POST['form_id'] ) ) ? intval( $_POST['form_id'] ) : $form_id; //phpcs:ignore
			$form_set_id              = ( isset( $_POST['form_set_id'] ) ) ? intval( $_POST['form_set_id'] ) : 0; //phpcs:ignore
			$ref_form_id              = ( isset( $_POST['arm_ref_template'] ) ) ? intval( $_POST['arm_ref_template'] ) : $ref_form_id; //phpcs:ignore
			$container                = '.arm_form_' . $form_id;
			$popup_container          = '.arm_popup_member_form_' . $form_id;
			$form_settings            = isset( $_POST['arm_form_settings'] ) ? array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST['arm_form_settings'] ) : $form_settings; //phpcs:ignore
			$isViewProfileLink        = ( isset( $atts['view_profile'] ) && $atts['view_profile'] == true ) ? true : false;
			$new_style_css            = '';
			$arm_default_fields_array = array();
			$arm_form_id_array        = array();
			if ( $form_set_id != 0 ) {
				$arm_form_ids = $wpdb->get_results( $wpdb->prepare('SELECT * FROM `' . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_set_id`=%d",$form_set_id), ARRAY_A );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_forms is a table name. False Positive Alarm
				foreach ( $arm_form_ids as $arm_form_id ) {
					$arm_form_id_array[] = $arm_form_id['arm_form_id'];
				}
				$arm_new_form_ids = implode( ',', $arm_form_id_array );
				$admin_placeholders = ' arm_form_field_form_id NOT IN (';
				$admin_placeholders .= rtrim( str_repeat( '%s,', count( $arm_form_id_array ) ), ',' );
				$admin_placeholders .= ')';
				array_unshift( $arm_form_id_array, $admin_placeholders );
				$where_cl = call_user_func_array(array( $wpdb, 'prepare' ), $arm_form_id_array );//phpcs:ignore 
			} else {
				$arm_new_form_ids = $form_id;
				$where_cl = $wpdb->prepare(" arm_form_field_form_id = %d",$form_id);//phpcs:ignore 
			}
			if ( ! empty( $form_id ) && $form_id == 'close_account' ) {
				$arm_default_fields_array = array();
			} else {

				/* Query Monitor Change */
				if ( isset( $GLOBALS['arm_form_style'] ) && isset( $GLOBALS['arm_form_style'][ $arm_new_form_ids ] ) ) {
					$arm_form_field_results = $GLOBALS['arm_form_style'][ $arm_new_form_ids ];
				} else {
					$where = ' WHERE';
					$where .= $where_cl;
					$arm_form_field_results = $wpdb->get_results( 'SELECT * FROM `' . $ARMemberLite->tbl_arm_form_field . "` ".$where, ARRAY_A );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_form_field is a table name. False Positive Alarm
					$GLOBALS['arm_form_style']                      = array();
					$GLOBALS['arm_form_style'][ $arm_new_form_ids ] = $arm_form_field_results;
				}
				if ( ! empty( $arm_form_field_results ) ) {
					foreach ( $arm_form_field_results as $arm_field_result ) {
						$fieldID        = $arm_field_result['arm_form_field_id'];
						$fieldSlug      = $arm_field_result['arm_form_field_slug'];
						$fieldIdOptions = maybe_unserialize( $arm_field_result['arm_form_field_option'] );
						$fieldPrefix    = $fieldPrefix_type = $fieldSuffix_type = $fieldSuffix = '';
						if ( isset( $fieldIdOptions['prefix'] ) && $fieldIdOptions['prefix'] != '' ) {
							$fieldPrefix      = $fieldIdOptions['prefix'];
							$fieldPrefix_type = 'prefix';
						}
						if ( isset( $fieldIdOptions['suffix'] ) && $fieldIdOptions['suffix'] != '' ) {
							$fieldSuffix      = $fieldIdOptions['suffix'];
							$fieldSuffix_type = 'suffix';
						}
						if ( $fieldSlug != '' ) {
							$arm_default_fields_array[] = array(
								'id'            => $fieldID,
								'type'          => $fieldSlug,
								'prefix_type'   => $fieldPrefix_type,
								'prefix_icon'   => $fieldPrefix,
								'no_icon_label' => esc_html__( 'No Icon', 'armember-membership' ),
								'suffix_icon'   => $fieldSuffix,
								'suffix_type'   => $fieldSuffix_type,
							);
						}
					}
				}
			}

			if ( ! empty( $form_settings['style'] ) ) {
				$default_form_style        = $this->arm_default_form_style();
				$new_style                 = $form_settings['style'];
				$form_layout               = $form_layout_style = $new_style['form_layout'];
				$form_style_container      = '';
				$new_style['field_height'] = '44';
				if ( $form_layout_style == 'writer' ) {
					$form_style_container = '.arm-material-style.arm_materialize_form';
				} elseif ( $form_layout_style == 'rounded' ) {
					$form_style_container = '.arm-rounded-style';
				} elseif ( $form_layout_style == 'writer_border' ) {
					$form_style_container      = '.arm--material-outline-style.arm_materialize_form';
					$new_style['field_height'] = '52';
				}
				$form_settings['custom_css'] = isset( $form_settings['custom_css'] ) ? stripslashes( $form_settings['custom_css'] ) : '';
				$fp_link_margin              = ( isset( $form_settings['forgot_password_link_margin'] ) ) ? $form_settings['forgot_password_link_margin'] : array();
				$fp_link_margin['left']      = ( isset( $fp_link_margin['left'] ) && is_numeric( $fp_link_margin['left'] ) ) ? $fp_link_margin['left'] : 0;
				$fp_link_margin['top']       = ( isset( $fp_link_margin['top'] ) && is_numeric( $fp_link_margin['top'] ) ) ? $fp_link_margin['top'] : 0;
				$fp_link_margin['right']     = ( isset( $fp_link_margin['right'] ) && is_numeric( $fp_link_margin['right'] ) ) ? $fp_link_margin['right'] : 0;
				$fp_link_margin['bottom']    = ( isset( $fp_link_margin['bottom'] ) && is_numeric( $fp_link_margin['bottom'] ) ) ? $fp_link_margin['bottom'] : 0;
				$reg_link_margin             = ( isset( $form_settings['registration_link_margin'] ) ) ? $form_settings['registration_link_margin'] : array();
				$reg_link_margin['left']     = ( isset( $reg_link_margin['left'] ) && is_numeric( $reg_link_margin['left'] ) ) ? $reg_link_margin['left'] : 0;
				$reg_link_margin['top']      = ( isset( $reg_link_margin['top'] ) && is_numeric( $reg_link_margin['top'] ) ) ? $reg_link_margin['top'] : 0;
				$reg_link_margin['right']    = ( isset( $reg_link_margin['right'] ) && is_numeric( $reg_link_margin['right'] ) ) ? $reg_link_margin['right'] : 0;
				$reg_link_margin['bottom']   = ( isset( $reg_link_margin['bottom'] ) && is_numeric( $reg_link_margin['bottom'] ) ) ? $reg_link_margin['bottom'] : 0;
				$new_style                   = shortcode_atts( $default_form_style, $new_style );
				$formBGImage                 = '';
				if ( isset( $new_style['form_bg'] ) && ! empty( $new_style['form_bg'] ) ) {
					if ( file_exists( MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename( $new_style['form_bg'] ) ) ) {
						$formBGImage = "url({$new_style['form_bg']})";
					}
				}
				$formBGColor = $new_style['form_bg_color'];
				if ( isset( $new_style['form_opacity'] ) && $new_style['form_opacity'] < 1 ) {
					$FrmBgOpacity   = isset( $new_style['form_opacity'] ) ? $new_style['form_opacity'] : 1;
					$FrmBgRgba      = $this->armHexToRGB( $formBGColor );
					$FrmBgRgbaRed   = ( ! empty( $FrmBgRgba['r'] ) ) ? $FrmBgRgba['r'] : 0;
					$FrmBgRgbaBlue  = ( ! empty( $FrmBgRgba['b'] ) ) ? $FrmBgRgba['b'] : 0;
					$FrmBgRgbaGreen = ( ! empty( $FrmBgRgba['g'] ) ) ? $FrmBgRgba['g'] : 0;
					$formBGColor    = "rgba({$FrmBgRgbaRed},{$FrmBgRgbaGreen},{$FrmBgRgbaBlue},{$FrmBgOpacity})";
				}
				$date_picker_color        = $new_style['field_focus_color'];
				$date_picker_color_scheme = $new_style['color_scheme'];
				if ( $new_style['field_focus_color'] == '' ) {
					$date_picker_color        = '#0c7cd5';
					$date_picker_color_scheme = 'blue';
				}
				$new_style['form_title_font_bold']       = ( $new_style['form_title_font_bold'] == '1' ) ? 'font-weight: bold;' : 'font-weight: normal;';
				$new_style['form_title_font_italic']     = ( $new_style['form_title_font_italic'] == '1' ) ? 'font-style: italic;' : 'font-style: normal;';
				$new_style['form_title_font_decoration'] = ( ! empty( $new_style['form_title_font_decoration'] ) ) ? 'text-decoration: ' . $new_style['form_title_font_decoration'] . ';' : 'text-decoration: none;';
				$new_style['field_font_bold']            = ( $new_style['field_font_bold'] == '1' ) ? 'font-weight: bold;' : 'font-weight: normal;';
				$new_style['field_font_italic']          = ( $new_style['field_font_italic'] == '1' ) ? 'font-style: italic;' : 'font-style: normal;';
				$new_style['field_font_decoration']      = ( ! empty( $new_style['field_font_decoration'] ) ) ? 'text-decoration: ' . $new_style['field_font_decoration'] . ';' : 'text-decoration: none;';
				$new_style['label_font_bold']            = ( $new_style['label_font_bold'] == '1' ) ? 'font-weight: bold;' : 'font-weight: normal;';
				$new_style['label_font_italic']          = ( $new_style['label_font_italic'] == '1' ) ? 'font-style: italic;' : 'font-style: normal;';
				$new_style['label_font_decoration']      = ( ! empty( $new_style['label_font_decoration'] ) ) ? 'text-decoration: ' . $new_style['label_font_decoration'] . ';' : 'text-decoration: none;';
				$new_style['button_font_bold']           = ( $new_style['button_font_bold'] == '1' ) ? 'font-weight: bold;' : 'font-weight: normal;';
				$new_style['button_font_italic']         = ( $new_style['button_font_italic'] == '1' ) ? 'font-style: italic;' : 'font-style: normal;';
				$new_style['button_font_decoration']     = ( ! empty( $new_style['button_font_decoration'] ) ) ? 'text-decoration: ' . $new_style['button_font_decoration'] . ';' : 'text-decoration: none;';
				$new_style['button_margin_top']          = ( is_numeric( $new_style['button_margin_top'] ) ) ? intval( $new_style['button_margin_top'] ) : 5;
				$new_style['button_margin_right']        = ( is_numeric( $new_style['button_margin_right'] ) ) ? intval( $new_style['button_margin_right'] ) : 0;
				$new_style['button_margin_bottom']       = ( is_numeric( $new_style['button_margin_bottom'] ) ) ? intval( $new_style['button_margin_bottom'] ) : 0;
				$new_style['button_margin_left']         = ( is_numeric( $new_style['button_margin_left'] ) ) ? intval( $new_style['button_margin_left'] ) : 0;

				$new_style['form_padding_top']    = ( is_numeric( $new_style['form_padding_top'] ) ) ? intval( $new_style['form_padding_top'] ) : 20;
				$new_style['form_padding_right']  = ( is_numeric( $new_style['form_padding_right'] ) ) ? intval( $new_style['form_padding_right'] ) : 20;
				$new_style['form_padding_bottom'] = ( is_numeric( $new_style['form_padding_bottom'] ) ) ? intval( $new_style['form_padding_bottom'] ) : 20;
				$new_style['form_padding_left']   = ( is_numeric( $new_style['form_padding_left'] ) ) ? intval( $new_style['form_padding_left'] ) : 20;
				$new_style['form_border_width']   = ( is_numeric( $new_style['form_border_width'] ) ) ? intval( $new_style['form_border_width'] ) : 2;
				if ( ! empty( $atts ) && isset( $atts['form_position'] ) && $atts['form_position'] !== '' ) {
					$new_style['form_position'] = $atts['form_position'];
				} else {
					$new_style['form_position'] = ( isset( $new_style['form_position'] ) ) ? $new_style['form_position'] : 'center';
				}

				$borderRGB      = $this->armHexToRGB( $new_style['field_border_color'] );
				$borderRGB['r'] = ( ! empty( $borderRGB['r'] ) ) ? $borderRGB['r'] : 0;
				$borderRGB['g'] = ( ! empty( $borderRGB['g'] ) ) ? $borderRGB['g'] : 0;
				$borderRGB['b'] = ( ! empty( $borderRGB['b'] ) ) ? $borderRGB['b'] : 0;

				$borderFocusRGB      = $this->armHexToRGB( $new_style['field_focus_color'] );
				$borderFocusRGB['r'] = ( ! empty( $borderFocusRGB['r'] ) ) ? $borderFocusRGB['r'] : 0;
				$borderFocusRGB['g'] = ( ! empty( $borderFocusRGB['g'] ) ) ? $borderFocusRGB['g'] : 0;
				$borderFocusRGB['b'] = ( ! empty( $borderFocusRGB['b'] ) ) ? $borderFocusRGB['b'] : 0;

				$new_style['form_width']    = ( ! empty( $new_style['form_width'] ) ) ? intval( $new_style['form_width'] ) : '600';
				$new_style['button_width']  = ( ! empty( $new_style['button_width'] ) ) ? intval( $new_style['button_width'] ) : '150';
				$new_style['button_height'] = ( ! empty( $new_style['button_height'] ) ) ? intval( $new_style['button_height'] ) : '35';
				$new_style['button_style']  = ( ! empty( $new_style['button_style'] ) ) ? $new_style['button_style'] : 'flat';
				$armSpinnerStyle            = '';
				$armSpinnerHoverStyle       = '';
				if ( $ref_form_id > 0 && in_array( $ref_form_id, array( 3 ) ) ) {
					$button_back_color           = $new_style['button_back_color'];
					$button_back_color_gradient  = $new_style['button_back_color_gradient'];
					$button_hover_color          = $new_style['button_hover_color'];
					$button_hover_color_gradient = $new_style['button_hover_color_gradient'];

					$buttonStyle  = 'background:' . $button_back_color . ';';
					$buttonStyle .= 'background-color:' . $button_back_color_gradient . ';';
					$buttonStyle .= 'background-image:-moz-linear-gradient(left,' . $button_back_color . ',' . $button_back_color_gradient . ');';
					$buttonStyle .= 'background-image:-webkit-gradient(left,' . $button_back_color . ',' . $button_back_color_gradient . ');';
					$buttonStyle .= 'background-image:-webkit-linear-gradient(left,' . $button_back_color . ',' . $button_back_color_gradient . ');';
					$buttonStyle .= 'background-image:-o-linear-gradient(left,' . $button_back_color . ',' . $button_back_color_gradient . ');';
					$buttonStyle .= 'background-image:linear-gradient(to left,' . $button_back_color . ',' . $button_back_color_gradient . ');';
					$buttonStyle .= "filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $button_back_color . "',endColorstr='" . $button_back_color_gradient . "',GradientType=0);";
					$buttonStyle .= "-ms-filter:filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $button_back_color . "',endColorstr='" . $button_back_color_gradient . "',GradeintType=0);";

					$buttonHoverStyle  = 'background:' . $button_hover_color . ' !important;';
					$buttonHoverStyle .= 'background-color:' . $button_hover_color_gradient . ' !important;';
					$buttonHoverStyle .= 'background-image:-moz-linear-gradient(left,' . $button_hover_color . ',' . $button_hover_color_gradient . ') !important;';
					$buttonHoverStyle .= 'background-image:-webkit-gradient(left,' . $button_hover_color . ',' . $button_hover_color_gradient . ') !important;';
					$buttonHoverStyle .= 'background-image:-webkit-linear-gradient(left,' . $button_hover_color . ',' . $button_hover_color_gradient . ') !important;';
					$buttonHoverStyle .= 'background-image:-o-linear-gradient(left,' . $button_hover_color . ',' . $button_hover_color_gradient . ') !important;';
					$buttonHoverStyle .= 'background-image:linear-gradient(to left,' . $button_hover_color . ',' . $button_hover_color_gradient . ') !important;';
					$buttonHoverStyle .= "filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $button_hover_color . "',endColorstr='" . $button_hover_color_gradient . "',GradientType=0) !important;";
					$buttonHoverStyle .= "-ms-filter:filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $button_hover_color . "',endColorstr='" . $button_hover_color_gradient . "',GradeintType=0) !important;";
				} else {
					$buttonStyle          = 'background: ' . $new_style['button_back_color'] . ';border: 1px solid ' . $new_style['button_back_color'] . ';color: ' . $new_style['button_font_color'] . ' !important;';
					$armSpinnerStyle      = 'fill:' . $new_style['button_font_color'];
					$buttonHoverStyle     = 'background-color: ' . $new_style['button_hover_color'] . ' !important;border: 1px solid ' . $new_style['button_hover_color'] . ' !important;color: ' . $new_style['button_hover_font_color'] . ' !important;';
					$armSpinnerStyle      = 'fill:' . $new_style['button_font_color'] . ';';
					$armSpinnerHoverStyle = 'fill:' . $new_style['button_hover_font_color'] . ';';
				}

				if ( $new_style['button_style'] == 'border' ) {

					$buttonStyle     = 'background-color: transparent;border: 2px solid ' . $new_style['button_back_color'] . ';color: ' . $new_style['button_back_color'] . ';';
					$armSpinnerStyle = 'fill:' . $new_style['button_back_color'] . ';';
					if ( $ref_form_id > 0 && in_array( $ref_form_id, array( 3 ) ) ) {
						$buttonHoverStyle  = 'background:' . $button_hover_color . ' !important;';
						$buttonHoverStyle .= 'background-color:' . $button_hover_color_gradient . ' !important;';
						$buttonHoverStyle .= 'background-image:-moz-linear-gradient(left,' . $button_hover_color . ',' . $button_hover_color_gradient . ') !important;';
						$buttonHoverStyle .= 'background-image:-webkit-gradient(left,' . $button_hover_color . ',' . $button_hover_color_gradient . ') !important;';
						$buttonHoverStyle .= 'background-image:-webkit-linear-gradient(left,' . $button_hover_color . ',' . $button_hover_color_gradient . ') !important;';
						$buttonHoverStyle .= 'background-image:-o-linear-gradient(left,' . $button_hover_color . ',' . $button_hover_color_gradient . ') !important;';
						$buttonHoverStyle .= 'background-image:linear-gradient(to left,' . $button_hover_color . ',' . $button_hover_color_gradient . ') !important;';
						$buttonHoverStyle .= "filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $button_hover_color . "',endColorstr='" . $button_hover_color_gradient . "',GradientType=0) !important;";
						$buttonHoverStyle .= "-ms-filter:filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $button_hover_color . "',endColorstr='" . $button_hover_color_gradient . "',GradeintType=0) !important;";
						$buttonHoverStyle .= 'color: ' . $new_style['button_hover_font_color'] . ' !important;';
						$buttonHoverStyle .= 'border: none !important;';
					} else {
						$buttonHoverStyle = 'background-color: ' . $new_style['button_hover_color'] . ' !important;border: 2px solid ' . $new_style['button_hover_color'] . ' !important;color: ' . $new_style['button_hover_font_color'] . ' !important;';
					}
					$armSpinnerHoverStyle = 'fill:' . $new_style['button_hover_font_color'] . ' !important;';
				} elseif ( $new_style['button_style'] == 'reverse_border' ) {

					if ( $ref_form_id > 0 && in_array( $ref_form_id, array( 3 ) ) ) {
						$buttonStyle  = 'background:' . $button_back_color . ' !important;';
						$buttonStyle .= 'background-color:' . $button_back_color_gradient . ';';
						$buttonStyle .= 'background-image:-moz-linear-gradient(left,' . $button_back_color . ',' . $button_back_color_gradient . ');';
						$buttonStyle .= 'background-image:-webkit-gradient(left,' . $button_back_color . ',' . $button_back_color_gradient . ');';
						$buttonStyle .= 'background-image:-webkit-linear-gradient(left,' . $button_back_color . ',' . $button_back_color_gradient . ');';
						$buttonStyle .= 'background-image:-o-linear-gradient(left,' . $button_back_color . ',' . $button_back_color_gradient . ');';
						$buttonStyle .= 'background-image:linear-gradient(to left,' . $button_back_color . ',' . $button_back_color_gradient . ');';
						$buttonStyle .= "filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $button_back_color . "',endColorstr='" . $button_back_color_gradient . "',GradientType=0);";
						$buttonStyle .= "-ms-filter:filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $button_back_color . "',endColorstr='" . $button_back_color_gradient . "',GradeintType=0);";
						$buttonStyle .= 'border:none !important;color:' . $new_style['button_font_color'] . ' !important;';
					} else {
						$buttonStyle = 'background: ' . $new_style['button_back_color'] . ' !important;border: 2px solid ' . $new_style['button_back_color'] . ';color: ' . $new_style['button_font_color'] . ' !important;';
					}
					$armSpinnerStyle = 'fill:' . $new_style['button_font_color'] . ';';

					if ( $ref_form_id > 0 && in_array( $ref_form_id, array( 3 ) ) ) {
						$buttonHoverStyle = 'background-color: transparent !important;background:transparent !important;background-image:transparent !important;';
					} else {
						$buttonHoverStyle = 'background-color: transparent !important;';
					}
					$buttonHoverStyle    .= 'border: 2px solid ' . $new_style['button_hover_color'] . ' !important;color: ' . $new_style['button_hover_color'] . ' !important;';
					$armSpinnerHoverStyle = 'fill:' . $new_style['button_hover_color'];
				} else {
					$armSpinnerStyle = 'fill:' . $new_style['button_font_color'] . ';';
				}

				$form_title_font_family = ( $new_style['form_title_font_family'] != 'inherit' ) ? 'font-family: ' . $new_style['form_title_font_family'] . ", sans-serif, 'Trebuchet MS';" : '';
				$label_font_family      = ( $new_style['label_font_family'] != 'inherit' ) ? 'font-family: ' . $new_style['label_font_family'] . ", sans-serif, 'Trebuchet MS';" : '';
				$field_font_family      = ( $new_style['field_font_family'] != 'inherit' ) ? 'font-family: ' . $new_style['field_font_family'] . ", sans-serif, 'Trebuchet MS';" : '';
				$button_font_family     = ( $new_style['button_font_family'] != 'inherit' ) ? 'font-family: ' . $new_style['button_font_family'] . ", sans-serif, 'Trebuchet MS';" : '';

				$formFonts = array( $new_style['field_font_family'], $new_style['form_title_font_family'], $new_style['label_font_family'], $new_style['button_font_family'] );
				$gFontUrl  = $this->arm_get_google_fonts_url( $formFonts );
				if ( ! empty( $gFontUrl ) ) {
					if ( is_admin() ) {
						$new_style_css1 = '<link id="google-font-' . $form_id . '" rel="stylesheet" type="text/css" href="' . esc_url($gFontUrl) . '" />'; //phpcs:ignore
					} else {
							wp_enqueue_style( 'google-font-' . $form_id, $gFontUrl, array(), MEMBERSHIPLITE_VERSION );
					}
				}
				$new_style_css = "
						$container .arm_editor_form_fileds_wrapper{
						   padding-top: " . $new_style['form_padding_top'] . 'px !important;
						   padding-bottom: ' . $new_style['form_padding_bottom'] . 'px !important;
						   padding-right: ' . $new_style['form_padding_right'] . 'px !important;
						   padding-left: ' . $new_style['form_padding_left'] . 'px !important;
						}
                                                

                                                .arm_popup_member_form_' . $form_id . ' .arm_form_message_container{
                                                    max-width: 100%;
                                                    width: ' . $new_style['form_width'] . $new_style['form_width_type'] . '; 
                                                    margin: 0 auto;
                                                }
                                                    
						.arm_popup_member_form_' . $form_id . " .arm-df__heading .arm-df__heading-text,
                        $container .arm_update_card_form_heading_container .arm-df__heading-text,
	                    $container .arm-df__heading:not(.popup_header_text) .arm-df__heading-text{
							color: " . $new_style['form_title_font_color'] . ';
							' . $form_title_font_family . '
							font-size: ' . $new_style['form_title_font_size'] . 'px;
							' . $new_style['form_title_font_bold'] . $new_style['form_title_font_italic'] . $new_style['form_title_font_decoration'] . "
						}
						$container .arm_registration_link,
						$container .arm_forgotpassword_link{
							color: " . $new_style['lable_font_color'] . ';
							' . $label_font_family . '
							font-size: ' . $new_style['label_font_size'] . 'px;
							' . $new_style['label_font_bold'] . $new_style['label_font_italic'] . $new_style['label_font_decoration'] . "
						}
	                    $container .arm_pass_strength_meter{
	                        color: " . $new_style['lable_font_color'] . ';
							' . $label_font_family . "
	                    }
                        $container .arm_reg_login_links a{
                            color: " . $new_style['register_link_font_color'] . " !important;
                        }
	                    $container .arm_registration_link a,
						$container .arm_forgotpassword_link a{
							color: " . $new_style['login_link_font_color'] . " !important;
	                    }
	                    $container .arm-df__form-group .arm_registration_link,
	                    $container .arm-df__form-group.arm_registration_link,
	                    $container .arm_registration_link{
	                        margin: " . $reg_link_margin['top'] . 'px ' . $reg_link_margin['right'] . 'px ' . $reg_link_margin['bottom'] . 'px ' . $reg_link_margin['left'] . "px !important;
	                    }
	                    $container .arm-df__form-group .arm_forgotpassword_link,
	                    $container .arm-df__form-group.arm_forgotpassword_link,
	                    $container .arm_forgotpassword_link{
	                        margin: " . $fp_link_margin['top'] . 'px ' . $fp_link_margin['right'] . 'px ' . $fp_link_margin['bottom'] . 'px ' . $fp_link_margin['left'] . 'px !important;                     
	                    }';
				if ( ! is_admin() ) {
					$new_style_css .= "$container .arm-df__form-group .arm_forgotpassword_link,
	                    $container .arm-df__form-group.arm_forgotpassword_link,
	                    $container .arm_forgotpassword_link{
	                        z-index:2;
	                    }";
				}
				$arm_background_form_field_style = '';
				if ( $form_layout != 'writer' ) {
					$arm_background_form_field_style = 'background-color: ' . $new_style['field_bg_color'] . ' !important;';
				}
				$new_style_css     .= "
	                    $container .arm_close_account_message,
						$container .arm_forgot_password_description {
							color: " . $new_style['lable_font_color'] . ';
							' . $label_font_family . '
							font-size: ' . ( $new_style['label_font_size'] + 1 ) . "px;
						}
						$container .arm-df__form-group{
							margin-bottom: " . $new_style['field_spacing'] . "px !important;
						}
						$container .arm-df__form-field,
                        $container.arm_membership_setup_form .arm_module_gateways_container .arm_module_gateway_fields .arm-df__form-field{
							max-width: 100%;
							width: 62%;
							width: " . $new_style['field_width'] . $new_style['field_width_type'] . ";
						}
	                    .arm_form_message_container.arm_editor_form_fileds_container.arm_editor_form_fileds_wrapper,
                            .arm_form_message_container1.arm_editor_form_fileds_container.arm_editor_form_fileds_wrapper {
	                        border: none !important;
	                    } 
						.arm_module_forms_container $container,
						.arm-form-container $container, 
                        .arm_update_card_form_container $container, .arm_editor_form_fileds_container,.arm_editor_form_fileds_container $container,
                        .arm-form-container $container.arm-default-form:not(.arm_admin_member_form){
							max-width: 100%;
							width: " . $new_style['form_width'] . $new_style['form_width_type'] . ';
							margin: 0 auto;
						}
                                                
                                                .popup_wrapper.arm_popup_wrapper.arm_popup_member_form' . $popup_container . '{
                                                        background: ' . $formBGImage . ' ' . $formBGColor . "!important;
							background-repeat: no-repeat;
							background-position: top left;
							
                                                }
                                                
                                                
                                                
						.arm_module_forms_container $container,
						.arm-form-container $container.arm-default-form:not(.arm_admin_member_form),
                        .arm_update_card_form_container $container, .arm_admin_member_form .arm_editor_form_fileds_wrapper {
							background: " . $formBGImage . ' ' . $formBGColor . ';
							background-repeat: no-repeat;
							background-position: top left;
							border: ' . $new_style['form_border_width'] . 'px ' . $new_style['form_border_style'] . ' ' . $new_style['form_border_color'] . ';
							border-radius: ' . $new_style['form_border_radius'] . 'px;
							-webkit-border-radius: ' . $new_style['form_border_radius'] . 'px;
							-moz-border-radius: ' . $new_style['form_border_radius'] . 'px;
							-o-border-radius: ' . $new_style['form_border_radius'] . 'px;
                            padding-top: ' . $new_style['form_padding_top'] . 'px !important;
                            padding-bottom: ' . $new_style['form_padding_bottom'] . 'px !important;
                            padding-right: ' . $new_style['form_padding_right'] . 'px !important;
                            padding-left: ' . $new_style['form_padding_left'] . 'px !important;
							float: ' . $new_style['form_position'] . ';
						}
                        .popup_wrapper.arm_popup_wrapper.arm_popup_member_form' . $popup_container . " .arm_module_forms_container $container,
						.popup_wrapper.arm_popup_wrapper.arm_popup_member_form" . $popup_container . " .arm-form-container $container{
                                background: none !important;
						}
	                    .arm_form_msg.arm-form-container, .arm_form_msg .arm_form_message_container,
                            .arm_form_msg.arm-form-container, .arm_form_msg .arm_form_message_container1{
	                        float: " . $new_style['form_position'] . ';
	                        width: ' . $new_style['form_width'] . $new_style['form_width_type'] . ";    
	                    }
						$container .arm_form_label_wrapper{
							max-width: 100%;
							width: 30%;
							width: " . $new_style['label_width'] . $new_style['label_width_type'] . ";
						}
						$container .arm_form_field_label_text,
						$container .arm_member_form_field_label .arm_form_field_label_text,
                        $container .arm_df__helper-description .arm_df__helper-description-text,
						$container .arm_form_label_wrapper .arm-df__label-asterisk,
						$container .arm-df__form-field-wrap label.arm_form_field_label_text {
							margin: 0px !important;
						}
                        $container" . $form_style_container . " .arm_form_field_label_text,
                        $container" . $form_style_container . " .arm_member_form_field_label .arm_form_field_label_text,
                        $container" . $form_style_container . " .arm_df__helper-description .arm_df__helper-description-text,
                        $container" . $form_style_container . " .arm_form_label_wrapper .arm-df__label-asterisk,
                        $container" . $form_style_container . " .arm-df__form-field-wrap label,
                        $container" . $form_style_container . " .arm-df__form-field .arm-df__radio .arm-df__fc-radio--label,
                        $container" . $form_style_container . ' .arm-df__form-field .arm-df__checkbox .arm-df__fc-checkbox--label {
                            color: ' . $new_style['lable_font_color'] . ';
                            ' . $label_font_family . '
                            font-size: ' . $new_style['label_font_size'] . 'px;
                            cursor: pointer;
                            ' . $new_style['label_font_bold'] . $new_style['label_font_italic'] . $new_style['label_font_decoration'] . '
                            line-height: ' . ( $new_style['label_font_size'] + 5 ) . "px;
                        }
                        $container" . $form_style_container . ' .arm-df__form-field-wrap .arm-notched-outline__notch label {
                            line-height: ' . ( $new_style['label_font_size'] * 2 ) . "px;
                        }
                        $container.arm-default-form .arm-df__checkbox input[type=\"checkbox\"]:checked + label:before {
                            border-right: 2px solid " . $formBGColor . ';
                            border-bottom: 2px solid ' . $formBGColor . ";
                        }
                        $container.arm-default-form .arm-df__dropdown-control .arm__dc--items-wrap .arm__dc--items {
                            background: " . $formBGColor . ";
                        }
                        $container .arm_reg_links_wrapper .arm_login_link, .arm_reg_login_links {
                            color: " . $new_style['lable_font_color'] . ';
                            ' . $label_font_family . '
                            font-size: ' . $new_style['label_font_size'] . 'px;
                            ' . $new_style['label_font_bold'] . $new_style['label_font_italic'] . $new_style['label_font_decoration'] . "
                        }
                        $container .arm-df__form-field-wrap .arm-df__dropdown-control .arm__dc--head .arm__dc--head__title,
                        $container .arm-df__form-field-wrap .arm-df__dropdown-control dt.arm__dc--head .arm-df__dc--head__autocomplete {
                            " . $field_font_family . '
                            color:' . $new_style['field_font_color'] . ';
                            font-size: ' . $new_style['field_font_size'] . 'px;
                            ' . $new_style['field_font_bold'] . "
                        }
                        $container" . $form_style_container . ' .arm_df__helper-description .arm_df__helper-description-text
                        { 
                            font-size: ' . $new_style['description_font_size'] . 'px; 
                            line-height: ' . $new_style['description_font_size'] . "px; 
                        }
	                    $container .arm-df__dropdown-control .arm__dc--items-wrap .arm__dc--items .arm__dc--item {
							" . $field_font_family . '
							font-size: ' . $new_style['label_font_size'] . 'px;
                            color:' . $new_style['field_font_color'] . ';
							' . $new_style['field_font_bold'] . $new_style['field_font_italic'] . $new_style['field_font_decoration'] . "
						}
                        $container .arm-df__dropdown-control .arm__dc--items-wrap .arm__dc--items .arm__dc--item:not([disabled]):focus, 
                        $container .arm-df__dropdown-control .arm__dc--items-wrap .arm__dc--items .arm__dc--item:not([disabled]):hover,
                        $container .arm-df__dropdown-control .arm__dc--items-wrap .arm__dc--items .arm__dc--item:not([disabled]).hovered
                        {
                            background-color : " . $new_style['field_focus_color'] . " ;
                            color : #ffffff;
                        }
						$container .arm-df__form-field-wrap.arm-df__form-field-wrap_section{
							color: " . $new_style['lable_font_color'] . ';
	                        ' . $label_font_family . "
	                    }
						$container .arm-df__radio, $container .arm-df__checkbox{
							color:" . $new_style['lable_font_color'] . ';
							' . $label_font_family . '
							font-size: ' . $new_style['label_font_size'] . 'px;
							cursor: pointer;
							' . $new_style['label_font_bold'] . $new_style['label_font_italic'] . $new_style['label_font_decoration'] . "
						}
						$container .arm-df__dropdown-control .arm__dc--items-wrap .arm__dc--items .arm__dc--item[selected] {
							font-weight: bold;
							color:" . $new_style['field_font_color'] . ";
						}
	                    $container .arm-df__form-field-wrap input:not([type='checkbox'],[type='radio'],.arm-df__dc--head__autocomplete){
	                        height: " . $new_style['field_height'] . "px;
	                    }
                        /*
                        $container .arm-df__form-field-wrap .arm-df__form-control[type='checkbox']{
                            width: " . $new_style['field_height'] . "px !important;
                        }
                        */

	                    $container .arm_apply_coupon_container .arm_coupon_submit_wrapper .arm_apply_coupon_btn{
	                        min-height: " . ( $new_style['field_height'] + 2 ) . "px;
	                        margin: 0;
	                    }
                        $container .arm-df__form-control::placeholder, 
                        $container input.arm-df__form-control:not(.arm-df__dc--head__autocomplete)::placeholder, 
                        $container textarea.arm-df__form-control::placeholder{
                            color:" . $new_style['field_font_color'] . ";
                        }
						$container .arm-df__form-field-wrap input:not([type='checkbox'],[type='radio'],.arm-df__dc--head__autocomplete),
						$container .arm-df__form-field-wrap textarea.arm-df__form-control,
						$container .arm-df__form-field-wrap select,
						$container .arm-df__form-field-wrap .arm-df__dropdown-control dt.arm__dc--head{
	                        " . $arm_background_form_field_style . '
							border: ' . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_border_color'] . ';
							border-color: ' . $new_style['field_border_color'] . ';
							border-radius: ' . $new_style['field_border_radius'] . 'px !important;
							-webkit-border-radius: ' . $new_style['field_border_radius'] . 'px !important;
							-moz-border-radius: ' . $new_style['field_border_radius'] . 'px !important;
							-o-border-radius: ' . $new_style['field_border_radius'] . 'px !important;
							color:' . $new_style['field_font_color'] . ';
							' . $field_font_family . '
							font-size: ' . $new_style['field_font_size'] . 'px;
							' . $new_style['field_font_bold'] . $new_style['field_font_italic'] . $new_style['field_font_decoration'] . '
							height: ' . $new_style['field_height'] . 'px;
                            line-height: ' . ( $new_style['field_height'] - 16 ) . "px;
                            background-image:none;
                            margin-bottom:0px !important;
						}
                        $container:not(.arm-material-style, .arm--material-outline-style) .arm-df__form-field-wrap input:not(.arm-df__dc--head__autocomplete) {
                            border-color: " . $new_style['field_border_color'] . ";
                        }

                        $container.arm-material-style .arm-df__form-field-wrap input:not([type='checkbox'],[type='radio'],.arm-df__dc--head__autocomplete),
                        $container.arm-material-style .arm-df__form-field-wrap textarea.arm-df__form-control,
                        $container.arm-material-style .arm-df__form-field-wrap select,
                        $container.arm-material-style .arm-df__form-field-wrap .arm-df__dropdown-control dt.arm__dc--head,
                        $container.arm--material-outline-style .arm-df__form-field-wrap input:not([type='checkbox'],[type='radio'],.arm-df__dc--head__autocomplete),
                        $container.arm--material-outline-style .arm-df__form-field-wrap textarea.arm-df__form-control,
                        $container.arm--material-outline-style .arm-df__form-field-wrap select,
                        $container.arm--material-outline-style .arm-df__form-field-wrap .arm-df__dropdown-control dt.arm__dc--head {
                            background-color: transparent !important;
                        }

                        $container.arm_form_rtl.arm-material-style .arm-df__form-field-wrap input:not([type='checkbox'],[type='radio'],.arm-df__dc--head__autocomplete),
                        $container.arm_rtl_site.arm-material-style .arm-df__form-field-wrap input:not([type='checkbox'],[type='radio'],.arm-df__dc--head__autocomplete)
                        {
                            text-align:right;
                        }

                        $container.arm--material-outline-style .arm-notched-outline__leading {
                            border-top: " . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_border_color'] . ';
                            border-left: ' . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_border_color'] . ';
                            border-bottom: ' . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_border_color'] . ";
                        }
                        $container.arm--material-outline-style .arm-notched-outline__notch {
                            border-top: " . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_border_color'] . ';
                            border-bottom: ' . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_border_color'] . ";
                        }
                        $container.arm--material-outline-style .arm-notched-outline__trailing {
                            border-top: " . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_border_color'] . ';
                            border-right: ' . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_border_color'] . ';
                            border-bottom: ' . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_border_color'] . ";
                        }
                        $container.arm_rtl_site.arm--material-outline-style .arm-df__form-group:not(.arm-df__form-group_select) .arm-notched-outline__leading,
                        $container.arm_rtl_site.arm--material-outline-style .arm-df__form-group_social_fields .arm-df__form-group_text .arm-notched-outline__leading,
                        $container.arm_form_rtl.arm--material-outline-style .arm-df__form-group .arm-notched-outline__leading,
                        $container.arm_form_rtl.arm--material-outline-style .arm-df__form-group_social_fields .arm-df__form-group_text .arm-notched-outline__leading,
                        $container.is_form_class_rtl.arm--material-outline-style .arm-df__form-group .arm-notched-outline__leading,
                        $container.is_form_class_rtl.arm--material-outline-style .arm-df__form-group_social_fields .arm-df__form-group_text .arm-notched-outline__leading {
                            border-top: " . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_border_color'] . ';
                            border-right: ' . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_border_color'] . ';
                            border-bottom: ' . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_border_color'] . ";
                            border-left: none;
                            border-radius:0;
                        }
                        $container.arm_rtl_site.arm--material-outline-style .arm-df__form-group:not(.arm-df__form-group_select) .arm-notched-outline__trailing,
                        $container.arm_rtl_site.arm--material-outline-style .arm-df__form-group_social_fields .arm-notched-outline__trailing,
                        $container.arm_form_rtl.arm_form_rtl.arm--material-outline-style .arm-df__form-group .arm-notched-outline__trailing,
                        $container.arm_form_rtl.arm--material-outline-style .arm-df__form-group_social_fields .arm-notched-outline__trailing,
                        $container.is_form_class_rtl.arm--material-outline-style .arm-df__form-group .arm-notched-outline__trailing,
                        $container.is_form_class_rtl.arm--material-outline-style .arm-df__form-group_social_fields .arm-notched-outline__trailing {
                            border-top: " . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_border_color'] . ';
                            border-left: ' . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_border_color'] . ';
                            border-bottom: ' . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_border_color'] . ";
                            border-right: none;
                            border-radius:0;
                        }

                        $container.arm--material-outline-style .arm-df__form-field-wrap input:not([type='checkbox'],[type='radio'],.arm-df__dc--head__autocomplete):focus + .arm-notched-outline .arm-notched-outline__leading,
                        
                        $container.arm--material-outline-style .arm-df__form-field-wrap input:not([type='checkbox'],[type='radio'],.arm-df__dc--head__autocomplete):focus + .arm-notched-outline .arm-notched-outline__notch,
                        $container.arm--material-outline-style .arm-df__form-field-wrap input:not([type='checkbox'],[type='radio'],.arm-df__dc--head__autocomplete):focus + .arm-notched-outline .arm-notched-outline__trailing,

                        $container.arm--material-outline-style .arm-df__form-field-wrap input.arm-df__form-control:focus + .arm-df__fc-icon+ .arm-notched-outline .arm-notched-outline__leading,
                        $container.arm--material-outline-style .arm-df__form-field-wrap input.arm-df__form-control:focus + .arm-df__fc-icon+ .arm-notched-outline .arm-notched-outline__notch,
                        $container.arm--material-outline-style .arm-df__form-field-wrap input.arm-df__form-control:focus + .arm-df__fc-icon+ .arm-notched-outline .arm-notched-outline__trailing,
                        
                        $container.arm--material-outline-style .arm-df__form-field-wrap textarea:focus + .arm-notched-outline .arm-notched-outline__leading,
                        $container.arm--material-outline-style .arm-df__form-field-wrap textarea:focus + .arm-notched-outline .arm-notched-outline__notch,
                        $container.arm--material-outline-style .arm-df__form-field-wrap textarea:focus + .arm-notched-outline .arm-notched-outline__trailing,
                        
                        $container.arm--material-outline-style .arm-df__form-field-wrap .arm-is-active + .arm-notched-outline .arm-notched-outline__leading,
                        $container.arm--material-outline-style .arm-df__form-field-wrap .arm-is-active + .arm-notched-outline .arm-notched-outline__notch,
                        $container.arm--material-outline-style .arm-df__form-field-wrap .arm-is-active + .arm-notched-outline .arm-notched-outline__trailing,
                        
                        $container.arm--material-outline-style .arm-df__form-field-wrap input.arm-df__form-control:focus + .bootstrap-datetimepicker-widget + .arm-notched-outline .arm-notched-outline__leading,
                        $container.arm--material-outline-style .arm-df__form-field-wrap input.arm-df__form-control:focus + .bootstrap-datetimepicker-widget + .arm-notched-outline .arm-notched-outline__notch,
                        $container.arm--material-outline-style .arm-df__form-field-wrap input.arm-df__form-control:focus + .bootstrap-datetimepicker-widget + .arm-notched-outline .arm-notched-outline__trailing {
                            border-color: " . $new_style['field_focus_color'] . " !important;
                        }

                        $container.arm--material-outline-style .arm-df__form-field .arm-df__form-control.arm_invalid + .arm-notched-outline .arm-notched-outline__leading,
                        $container.arm--material-outline-style .arm-df__form-field .arm-df__form-control.arm_invalid + .arm-notched-outline .arm-notched-outline__notch,
                        $container.arm--material-outline-style .arm-df__form-field .arm-df__form-control.arm_invalid + .arm-notched-outline .arm-notched-outline__trailing,

                        $container.arm--material-outline-style .arm-df__form-field .arm-df__form-control.arm_invalid + span + .arm-notched-outline .arm-notched-outline__leading,
                        $container.arm--material-outline-style .arm-df__form-field .arm-df__form-control.arm_invalid + span  + .arm-notched-outline .arm-notched-outline__notch,
                        $container.arm--material-outline-style .arm-df__form-field .arm-df__form-control.arm_invalid + span  + .arm-notched-outline .arm-notched-outline__trailing,
                        
                        $container.arm--material-outline-style .arm-df__form-field .arm-df__form-control.arm_invalid:focus + .arm-notched-outline .arm-notched-outline__leading,
                        $container.arm--material-outline-style .arm-df__form-field .arm-df__form-control.arm_invalid:focus + .arm-notched-outline .arm-notched-outline__notch,
                        $container.arm--material-outline-style .arm-df__form-field .arm-df__form-control.arm_invalid:focus + .arm-notched-outline .arm-notched-outline__trailing,

                        $container.arm--material-outline-style .arm-df__form-field .arm-df__form-control.arm_invalid:focus + span + .arm-notched-outline .arm-notched-outline__leading,
                        $container.arm--material-outline-style .arm-df__form-field .arm-df__form-control.arm_invalid:focus + span + .arm-notched-outline .arm-notched-outline__notch,
                        $container.arm--material-outline-style .arm-df__form-field .arm-df__form-control.arm_invalid:focus + span + .arm-notched-outline .arm-notched-outline__trailing,
                        
                        $container.arm--material-outline-style .arm-df__form-group_select.error .arm-notched-outline .arm-notched-outline__leading,
                        $container.arm--material-outline-style .arm-df__form-group_select.error .arm-notched-outline .arm-notched-outline__notch,
                        $container.arm--material-outline-style .arm-df__form-group_select.error .arm-notched-outline .arm-notched-outline__trailing
                        {
                            border-color: " . $new_style['error_field_border_color'] . " !important;
                        }

                        $container.arm--material-outline-style .arm-df__form-field-wrap .arm-df__label-text.active:before {
                            background: " . $formBGImage . ' ' . $formBGColor . " !important;
                        }

                        $container .arm-df__form-field-wrap .arm-df__dropdown-control dt.arm__dc--head .arm-df__dc--head__autocomplete {
                            line-height: " . ( $new_style['field_height'] - 16 ) . "px;
                        }
                        $container .arm-df__form-field-wrap .arm-df__dropdown-control dt.arm__dc--head i.armfa.armfa-caret-down{
                            color: " . $new_style['prefix_suffix_color'] . ";
                        }
                        $container .arm-df__form-field-wrap .arm-df__dropdown-control .arm__dc--items-wrap .arm__dc--items { border: " . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_border_color'] . "; }
                        $container.arm-rounded-style .arm-df__form-field-wrap .arm-df__dropdown-control.arm-is-active .arm__dc--head{
                        -webkit-border-radius: 25px 25px 0 0 !important;
                        -moz-border-radius: 25px 25px 0 0 !important;
                        -o-border-radius: 25px 25px 0 0 !important;
                        border-radius: 25px 25px 0 0 !important;
                        }
                        
						$container .armFileUploadWrapper .arm-ffw__file-upload-box{
							border-color: " . $new_style['field_border_color'] . ";
						}
						$container .armFileUploadWrapper .arm-ffw__file-upload-box.arm_dragover{
							border-color: " . $new_style['field_focus_color'] . ";
						}
						$container .arm-df__checkbox{
							color: rgba(" . $borderRGB['r'] . ', ' . $borderRGB['g'] . ', ' . $borderRGB['b'] . ", 0.87);
						}
						$container.arm_materialize_form .arm-df__checkbox input[type='checkbox'] + label:after,
                        $container.arm_materialize_form .arm-df__radio input[type='radio'] + label:before
                        {
							border-color: " . $new_style['field_border_color'] . ";
						}
						$container input[type=checkbox].arm-df__form-control--is-checkbox:checked,
                        $container input[type=radio].arm-df__form-control--is-radio:checked,
                        $container.arm_materialize_form .arm-df__checkbox input[type='checkbox']:checked + label:after,
                        $container.arm_materialize_form .arm-df__checkbox input[type='checkbox']:checked:focus + label:after,
                        $container.arm_materialize_form .arm-df__radio input[type='radio']:checked + label:after,
                        $container.arm_materialize_form .arm-df__radio input[type='radio']:checked:focus + label:after{
							background-color: " . $new_style['field_focus_color'] . ';
                            border-color: ' . $new_style['field_focus_color'] . ";
						}
                        $container.arm_materialize_form .arm-df__radio input[type='radio']:checked + label:before {
                            border-color: " . $new_style['field_focus_color'] . ";
                        }
						$container .arm-df__checkbox:before,
                        $container .arm-df__radio:before{
							background-color: rgba(" . $borderFocusRGB['r'] . ', ' . $borderFocusRGB['g'] . ', ' . $borderFocusRGB['b'] . ", 0.26) !important;
						}
						$container.arm_form_layout_writer .arm-df__fields-wrapper .select-wrapper input.select-dropdown,
						$container.arm_form_layout_writer .arm-df__fields-wrapper .file-field input.file-path{
							border-color: " . $new_style['field_border_color'] . ';
							border-width: 0 0 ' . $new_style['field_border_width'] . "px 0 !important;
						}
						$container.arm_form_layout_writer .arm-df__form-control.select-wrapper{border:0 !important;}
						$container .arm-df__form-field-wrap input.arm-df__form-control:focus,
						$container .arm-df__form-field-wrap textarea.arm-df__form-control:focus,
						$container .arm-df__form-field-wrap select:focus,
                        $container .arm-df__form-field-wrap .arm-df__dropdown-control.arm-is-active dt.arm__dc--head,
                        $container .arm-df__form-field-wrap .arm-df__dropdown-control.arm-is-active .arm__dc--items-wrap .arm__dc--items{
                            color: " . $new_style['field_font_color'] . ';
							border: ' . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_focus_color'] . ';
							border-color: ' . $new_style['field_focus_color'] . " !important;
                            background-image:none;
						}
						$container .arm_uploaded_file_info .armbar{
							background-color: " . $new_style['field_focus_color'] . ";
						}
						$container .arm-df__form-control.arm-df__fc--validation__wrap,
						$container .arm-df__form-control.arm_invalid, 
                        $container .arm-df__form-group_select.error .arm__dc--head {
							border: " . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['error_field_border_color'] . ';
							border-color: ' . $new_style['error_field_border_color'] . " !important;
						}
                        $container.arm_materialize_form:not(.arm--material-outline-style) .arm-df__form-field .arm-df__form-control.arm_invalid,
                        $container.arm_materialize_form:not(.arm--material-outline-style) .arm-df__form-group_select.error .arm__dc--head{
                            border-bottom: " . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['error_field_border_color'] . " !important;
                        }    
						$container .arm_form_message_container .arm_success_msg,
						$container .arm_form_message_container .arm-df__fc--validation__wrap,
                                                $container .arm_form_message_container1 .arm_success_msg,
                                                $container .arm_form_message_container1 .arm_success_msg1,
						$container .arm_form_message_container1 .arm-df__fc--validation__wrap,
                                                    $container .arm_form_message_container .arm_success_msg a{
							" . $label_font_family . "
	                        text-decoration: none !important;
						}
                        $container .arm_coupon_field_wrapper .success.notify_msg{
                            " . $label_font_family . "
                            text-decoration: none !important;
                        }
	                    $container.arm_form_layout_writer .arm-df__form-field-wrap textarea.arm-df__form-control{
	                        -webkit-transition: all 0.3s cubic-bezier(0.64, 0.09, 0.08, 1);
	                        -moz-transition: all 0.3s cubic-bezier(0.64, 0.09, 0.08, 1);
							transition: all 0.3s cubic-bezier(0.64, 0.09, 0.08, 1);
							background: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 99.1%, " . $new_style['field_border_color'] . ' 4%);
							background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 99.1%, ' . $new_style['field_border_color'] . " 4%);
							background-repeat: no-repeat;
							background-position: 0 0;
							background-size: 0 100%;
							max-height:150px;
	                                        }
						$container.arm_materialize_form .arm-df__form-field-wrap input,
						$container.arm_materialize_form .arm-df__form-field-wrap select{
							-webkit-transition: all 0.3s cubic-bezier(0.64, 0.09, 0.08, 1);
							transition: all 0.3s cubic-bezier(0.64, 0.09, 0.08, 1);
							background: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 96%, " . $new_style['field_border_color'] . ' 4%);
							background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 96%, ' . $new_style['field_border_color'] . " 4%);
							background-repeat: no-repeat;
							background-position: 0 0;
							background-size: 0 100%;
						}
						$container.arm_form_layout_writer .arm-df__form-field-wrap input.arm-df__form-control:focus,
						$container.arm_form_layout_writer .arm-df__form-field-wrap select:focus,
                        $container.arm_form_layout_writer .arm-df__form-field-wrap .arm-df__dropdown-control.arm-is-active dt.arm__dc--head{
							background: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 96%, " . $new_style['field_focus_color'] . ' 4%);
							background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 96%, ' . $new_style['field_focus_color'] . " 4%);
							background-repeat: no-repeat;
							background-position: 0 0;
							background-size: 100% 100%;
						}
	                    
	                    $container .arm_editor_form_fileds_container .arm-df__form-field-wrap input.arm-df__form-control:focus,
						$container .arm_editor_form_fileds_container .arm-df__form-field-wrap textarea:focus,
						$container .arm_editor_form_fileds_container .arm-df__form-field-wrap select:focus,
                        $container .arm_editor_form_fileds_container .arm-df__form-field-wrap .arm-df__dropdown-control.arm-is-active dt.arm__dc--head,
                        $container .arm_editor_form_fileds_container .arm-df__form-field-wrap .arm-df__dropdown-control.arm-is-active .arm__dc--items-wrap .arm__dc--items{
							border: " . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_focus_color'] . ';
							border-color: ' . $new_style['field_focus_color'] . " !important;
						}
	                    $container.arm_form_layout_writer .arm_editor_form_fileds_container .arm-df__form-control.arm-df__fc--validation__wrap:focus,
						$container.arm_form_layout_writer .arm_editor_form_fileds_container .arm-df__form-control.arm_invalid:focus,
                        $container.arm_form_layout_writer.arm_materialize_form .arm_editor_form_fileds_container .arm-df__form-control.arm_invalid:focus,
	                    $container.arm_form_layout_writer .arm_editor_form_fileds_container .arm-df__form-field-wrap input.arm-df__form-control:focus,
						$container.arm_form_layout_writer .arm_editor_form_fileds_container .arm-df__form-field-wrap select:focus,
                        $container.arm_form_layout_writer .arm_editor_form_fileds_container .arm-df__form-field-wrap .arm-df__dropdown-control.arm-is-active dt.arm__dc--head,
                        $container.arm_form_layout_writer .arm_editor_form_fileds_container .arm-df__form-field-wrap .arm-df__dropdown-control.arm-is-active .arm__dc--items-wrap .arm__dc--items{
	                        background-repeat: no-repeat;
							background-position: 0 0;
							background-size: 100% 100%;
	                        border-color: " . $new_style['field_focus_color'] . " !important;
	                    }
	                    $container.arm_form_layout_writer .arm-df__form-field-wrap textarea:focus{
	                        background: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 99.1%, " . $new_style['field_focus_color'] . ' 4%);
							background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 99.1%, ' . $new_style['field_focus_color'] . " 4%);
							background-repeat: no-repeat;
							background-position: 0 0;
							background-size: 100% 100%;
	                    }
	                    $container.arm_form_layout_writer textarea.arm-df__form-control.arm-df__fc--validation__wrap:focus,
	                    $container.arm_form_layout_writer textarea.arm-df__form-control.arm_invalid:focus,
                        $container.arm_form_layout_writer.arm_materialize_form textarea.arm-df__form-control.arm_invalid:focus{
	                        background: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 99.1%, " . $new_style['error_field_border_color'] . ' 4%);
	                        background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 99.1%, ' . $new_style['error_field_border_color'] . " 4%);
	                        background-repeat: no-repeat;
	                        background-position: 0 0;
	                        background-size: 100% 100%;
	                    }
						$container.arm_form_layout_writer .arm-df__form-control.arm-df__fc--validation__wrap:focus,
						$container.arm_form_layout_writer .arm-df__form-control.arm_invalid:focus,
                        $container.arm_form_layout_writer.arm_materialize_form .arm-df__form-control.arm_invalid:focus{
							background: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 96%, " . $new_style['error_field_border_color'] . ' 4%);
							background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 96%, ' . $new_style['error_field_border_color'] . " 4%);
							background-repeat: no-repeat;
							background-position: 0 0;
							background-size: 100% 100%;
						}

                        $container .armFileMessages.arm-df__fc--validation
                        {
                            display:block ;
                        }
                        $container:not(.arm_form_layout_iconic,.arm_form_layout_rounded,.arm_standard_validation_type) .arm-df__fc--validation .arm-df__fc--validation__wrap
                        {
                            color: " . $new_style['error_font_color'] . ';
	                        ' . $label_font_family . '
							font-size: 14px;
	                        font-size: ' . $new_style['label_font_size'] . "px;
	                        text-decoration: none !important;
                        }

$container.arm_form_layout_rounded .arm-df__fc-icon.--arm-suffix-icon.arm_visible_password_material:focus,
						$container.arm_form_layout_rounded .arm-df__fc-icon.--arm-suffix-icon.arm_visible_password:focus
						{
							border: " . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_focus_color'] . ' !important;
							border-color: ' . $new_style['field_focus_color'] . " !important;
							border-top-right-radius: 50% !important;
							border-bottom-right-radius: 50% !important;
						}

                        $container .arm-ffw__file-upload-box:focus-within{
                            border-color: " . $new_style['field_focus_color'] . " !important;
                        }

                        $container .arm-ffw__file-upload-box button.armFileRemoveContainer:focus{
                            color: " . $new_style['field_focus_color'] . " !important;
                        }

						$container.arm_form_layout_writer .arm-df__fc-icon.--arm-suffix-icon.arm_visible_password_material:focus,
						$container.arm_form_layout_writer .arm-df__fc-icon.--arm-suffix-icon.arm_visible_password:focus
						{
                            border: 0px !important;
                            background: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 96%, " . $new_style['field_focus_color'] . " 4%) !important;
                            background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 96%, " . $new_style['field_focus_color'] . " 4%) !important;
                            background-repeat: no-repeat !important ;
                            background-position: 0 0 !important;
                            background-size: 100% 100% !important;
						}

						$container:not(.arm_form_layout_writer,.arm_form_layout_rounded) .arm-df__fc-icon.--arm-suffix-icon.arm_visible_password_material:focus,
						$container:not(.arm_form_layout_writer,.arm_form_layout_rounded) .arm-df__fc-icon.--arm-suffix-icon.arm_visible_password:focus
						{
							border: " . $new_style['field_border_width'] . 'px ' . $new_style['field_border_style'] . ' ' . $new_style['field_focus_color'] . " !important;
						}

                        $container.arm_form_layout_writer_border .arm-df__fc-icon.--arm-suffix-icon.arm_visible_password_material:focus,
						$container.arm_form_layout_writer_border .arm-df__fc-icon.--arm-suffix-icon.arm_visible_password:focus
						{
							border-top-right-radius: " . $new_style['field_border_radius'] . 'px !important;
							border-bottom-right-radius: ' . $new_style['field_border_radius'] . "px !important;
						}

						$container.arm_form_layout_iconic:not(.arm_standard_validation_type) .arm-df__fc--validation .arm-df__fc--validation__wrap,
						$container.arm_form_layout_rounded:not(.arm_standard_validation_type) .arm-df__fc--validation .arm-df__fc--validation__wrap,
						$container:not(.arm_standard_validation_type) .arm-df__fc--validation .arm-df__fc--validation__wrap,
                        $container.arm_form_layout_iconic:not(.arm_standard_validation_type) .armFileMessages .arm-df__fc--validation__wrap,
                        $container.arm_form_layout_rounded:not(.arm_standard_validation_type) .armFileMessages .arm-df__fc--validation__wrap,
                        $container:not(.arm_standard_validation_type) .armFileMessages .arm-df__fc--validation__wrap
                        {
							color: " . $new_style['error_font_color'] . ';
							background: ' . $new_style['error_field_bg_color'] . ' !important;
	                        ' . $label_font_family . '
							font-size: 14px;
	                        font-size: ' . $new_style['label_font_size'] . "px;
							padding-left: 5px;
							padding-right: 5px;
	                        text-decoration: none !important;
						}
                        $container.arm_standard_validation_type .arm-df__fc--validation .arm-df__fc--validation__wrap, $container.arm_standard_validation_type .armFileMessages .arm-df__fc--validation__wrap{
                            color: " . $new_style['error_font_color'] . ';
                            ' . $label_font_family . '
                            font-size: ' . $new_style['label_font_size'] . "px;
                        }
						$container .arm_msg_pos_right .arm-df__fc--validation .arm_error_box_arrow:after, $container .arm_msg_pos_right .armFileMessages .arm_error_box_arrow:after{border-right-color: " . $new_style['error_field_bg_color'] . " !important;} 
						$container .arm_msg_pos_left .arm-df__fc--validation .arm_error_box_arrow:after, $container .arm_msg_pos_left .armFileMessages .arm_error_box_arrow:after{border-left-color: " . $new_style['error_field_bg_color'] . " !important;}
						$container .arm_msg_pos_top .arm-df__fc--validation .arm_error_box_arrow:after, $container .arm_msg_pos_top .armFileMessages .arm_error_box_arrow:after{border-top-color: " . $new_style['error_field_bg_color'] . " !important;}
						$container .arm_msg_pos_bottom .arm-df__fc--validation .arm_error_box_arrow:after, $container .arm_msg_pos_bottom .armFileMessages .arm_error_box_arrow:after{border-bottom-color: " . $new_style['error_field_bg_color'] . " !important;}
						$container .arm_writer_error_msg_box{
							color: " . $new_style['error_font_color'] . ';
							font-size: ' . $new_style['field_font_size'] . "px;
							font-size: 14px;
						}
						$container .arm-df__form-field-wrap_submit .arm-df__form-control-submit-btn,
						$container .arm-df__form-field-wrap_submit button.arm-df__form-control-submit-btn,
                        			.arm_form_message_container$container .arm_reset_password_login_btn{
							border-radius: " . $new_style['button_border_radius'] . 'px;
							-webkit-border-radius: ' . $new_style['button_border_radius'] . 'px;
							-moz-border-radius: ' . $new_style['button_border_radius'] . 'px;
							-o-border-radius: ' . $new_style['button_border_radius'] . 'px;
							width: auto;
							max-width: 100%;
							width: ' . $new_style['button_width'] . $new_style['button_width_type'] . ';
							min-height: 35px;
							min-height: ' . $new_style['button_height'] . $new_style['button_height_type'] . ';
							line-height: ' . $new_style['button_height'] . $new_style['button_height_type'] . ';
							padding: 0 10px;
							' . $button_font_family . '
							font-size: ' . $new_style['button_font_size'] . 'px;
							margin: ' . $new_style['button_margin_top'] . 'px ' . $new_style['button_margin_right'] . 'px ' . $new_style['button_margin_bottom'] . 'px ' . $new_style['button_margin_left'] . 'px;
							' . $new_style['button_font_bold'] . $new_style['button_font_italic'] . $new_style['button_font_decoration'] . '
							text-transform: none;
	                        ' . $buttonStyle . "
						}
	                    $container .arm-df__form-field-wrap_submit .arm-df__form-control-submit-btn.arm-df__form-group_button.arm_editable_input_button,
			    $container .arm-df__form-field-wrap_submit button.arm-df__form-control-submit-btn.arm-df__form-group_button.arm_editable_input_button{
	                        height: " . $new_style['button_height'] . $new_style['button_height_type'] . ";
	                    }
	                    $container .arm_setup_submit_btn_wrapper .arm-df__form-field-wrap_submit .arm-df__form-control-submit-btn,
                        $container .arm_setup_submit_btn_wrapper .arm-df__form-field-wrap_submit button.arm-df__form-control-submit-btn,
                        .arm_form_message_container$container .arm_reset_password_login_btn{
	                        " . $buttonStyle . "
	                    }
                        $container .arm-df__form-field-wrap_submit button.arm-df__form-control-submit-btn #arm_form_loader{
                            " . $armSpinnerStyle . "
                            }
						/*$container button:hover,*/
						$container .arm-df__form-field-wrap_submit .arm-df__form-control-submit-btn:hover,
						$container .arm-df__form-field-wrap_submit .arm-df__form-control-submit-btn:not([disabled]):hover,
						$container.arm_form_layout_writer .arm-df__fields-wrapper .arm-df__form-field-wrap_submit .arm-df__form-control-submit-btn.btn:hover,
						$container.arm_form_layout_writer .arm-df__fields-wrapper .arm-df__form-field-wrap_submit .arm-df__form-control-submit-btn.btn-large:hover,
						$container .arm-df__form-field-wrap_submit button.arm-df__form-control-submit-btn:hover,
						$container .arm-df__form-field-wrap_submit button.arm-df__form-control-submit-btn:not([disabled]):hover,
						$container.arm_form_layout_writer .arm-df__fields-wrapper .arm-df__form-field-wrap_submit button.arm-df__form-control-submit-btn.btn:hover,
						$container.arm_form_layout_writer .arm-df__fields-wrapper .arm-df__form-field-wrap_submit button.arm-df__form-control-submit-btn.btn-large:hover,
                        			.arm_form_message_container$container .arm_reset_password_login_btn:hover{
							" . $buttonHoverStyle . "
						}

                        $container .arm-df__form-field-wrap_submit .arm-df__form-control-submit-btn:focus,
						$container .arm-df__form-field-wrap_submit .arm-df__form-control-submit-btn:not([disabled]):focus,
						$container.arm_form_layout_writer .arm-df__fields-wrapper .arm-df__form-field-wrap_submit .arm-df__form-control-submit-btn.btn:focus,
						$container.arm_form_layout_writer .arm-df__fields-wrapper .arm-df__form-field-wrap_submit .arm-df__form-control-submit-btn.btn-large:focus,
						$container .arm-df__form-field-wrap_submit button.arm-df__form-control-submit-btn:focus,
						$container .arm-df__form-field-wrap_submit button.arm-df__form-control-submit-btn:not([disabled]):focus,
						$container.arm_form_layout_writer .arm-df__fields-wrapper .arm-df__form-field-wrap_submit button.arm-df__form-control-submit-btn.btn:focus,
						$container.arm_form_layout_writer .arm-df__fields-wrapper .arm-df__form-field-wrap_submit button.arm-df__form-control-submit-btn.btn-large:focus,
                        .arm_form_message_container$container .arm_reset_password_login_btn:focus{
							" . $buttonHoverStyle . "
						}

                        $container .arm-df__form-field-wrap_submit button.arm-df__form-control-submit-btn:hover #arm_form_loader,
						$container .arm-df__form-field-wrap_submit button.arm-df__form-control-submit-btn:not([disabled]):hover #arm_form_loader,
						$container.arm_form_layout_writer .arm-df__fields-wrapper .arm-df__form-field-wrap_submit button.arm-df__form-control-submit-btn.btn:hover #arm_form_loader,
						$container.arm_form_layout_writer .arm-df__fields-wrapper .arm-df__form-field-wrap_submit button.arm-df__form-control-submit-btn.btn-large:hover #arm_form_loader{
                            " . $armSpinnerHoverStyle . "
                        }
	                    $container .arm-df__fields-wrapper .armFileUploadWrapper .armFileBtn,
						$container .arm-df__fields-wrapper .armFileUploadContainer{
							border: 1px solid " . $new_style['button_back_color'] . ';
							background-color: ' . $new_style['button_back_color'] . ';
							color: ' . $new_style['button_font_color'] . ";
						}
						$container .arm-df__fields-wrapper .armFileUploadWrapper .armFileBtn:hover,
						$container .arm-df__fields-wrapper .armFileUploadContainer:hover{
	                        background-color: " . $new_style['button_hover_color'] . ' !important;
							border-color: ' . $new_style['button_hover_color'] . ' !important;
							color: ' . $new_style['button_hover_font_color'] . " !important;
	                    }
						$container .arm-df__fc-icon i{color: " . $new_style['prefix_suffix_color'] . ";}
						.arm_date_field_$form_id .bootstrap-datetimepicker-widget table td.today:before{border: 3px solid " . $date_picker_color . ";}
						.arm_date_field_$form_id .bootstrap-datetimepicker-widget table td.active,
						.arm_date_field_$form_id .bootstrap-datetimepicker-widget table td.active:hover{
							color: " . $date_picker_color . ' !important;
							background: url(' . MEMBERSHIPLITE_IMAGES_URL . '/bootstrap_datepicker_' . $date_picker_color_scheme . ".png) no-repeat !important;
						}
						.arm_date_field_$form_id .bootstrap-datetimepicker-widget table td span:hover{border-color: " . $date_picker_color . ";}
						.arm_date_field_$form_id .bootstrap-datetimepicker-widget table td span.active{background-color: " . $date_picker_color . ";}
						.arm_date_field_$form_id .arm_cal_header{background-color: " . $date_picker_color . " !important;}
						.arm_date_field_$form_id .arm_cal_month{
							background-color: " . $date_picker_color . ' !important;
							border-bottom: 1px solid ' . $date_picker_color . ";
						}
						.arm_date_field_$form_id .bootstrap-datetimepicker-widget table td.day:hover {
							background: url(" . MEMBERSHIPLITE_IMAGES_URL . "/bootstrap_datepicker_hover.png) no-repeat;
						}
						.arm_date_field_$form_id .arm_cal_hour:hover, .arm_date_field_$form_id .arm_cal_minute:hover{border-color: " . $date_picker_color . ";}
						.arm_date_field_$form_id .timepicker-picker .btn-primary{
							background-color: " . $date_picker_color . ';
							border-color: ' . $date_picker_color . ";
						}
						.arm_date_field_$form_id .armglyphicon-time:before,
						.arm_date_field_$form_id .armglyphicon-calendar:before,
						.arm_date_field_$form_id .armglyphicon-chevron-up:before,
						.arm_date_field_$form_id .armglyphicon-chevron-down:before{color: " . $date_picker_color . ';}
						' . stripslashes_deep( $form_settings['custom_css'] ) . '
					';
					$new_style_css .= $container . ' stop.arm_social_connect_svg { stop-color:' . $new_style['button_back_color'] . '; } ';
				if ( $isViewProfileLink ) {
					global $arm_global_settings;
					$frontfontstyle = $arm_global_settings->arm_get_front_font_style();
					$linkFonts      = isset( $frontfontstyle['frontOptions']['link_font'] ) ? $frontfontstyle['frontOptions']['link_font'] : '';
					$new_style_css .= "
	                        .arm-default-form .arm_view_profile_link_container a,
	                        .arm-default-form .arm_view_profile_link_container a.arm_view_profile_link{
	                            {$linkFonts['font']}
	                        }
	                    ";
					if ( isset( $frontfontstyle['google_font_url'] ) && ! empty( $frontfontstyle['google_font_url'] ) ) {
						if ( is_admin() ) {
							$new_style_css1 .= '<link id="google-font-' . esc_attr($form_id) . '" rel="stylesheet" type="text/css" href="' . esc_url($frontfontstyle['google_font_url']) . '" />'; //phpcs:ignore
						} else {
							wp_enqueue_style( 'google-font-' . $form_id, $frontfontstyle['google_font_url'], array(), MEMBERSHIPLITE_VERSION );
						}
					}
				}
			}
			if ( empty( $new_style_css1 ) ) {
				$new_style_css1 = '';
			}
			$arm_response = array(
				'arm_link'    => $new_style_css1,
				'arm_css'     => $new_style_css,
				'field_array' => $arm_default_fields_array,
			);
			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_ajax_generate_form_styles' ) { //phpcs:ignore
				echo wp_json_encode( $arm_response );
				exit;
			}
			return $arm_response;
		}

		function armHexToRGB( $hex = '#000000' ) {
			$rgb = array();
			if ( ! empty( $hex ) ) {
				list($r, $g, $b) = sscanf( $hex, '#%02x%02x%02x' );
				$rgb             = array(
					'r' => $r,
					'g' => $g,
					'b' => $b,
				);
			}
			return $rgb;
		}

		function arm_fonts_list() {
			global $wp, $wpdb, $ARMemberLite;
			$default_fonts = array( 'inherit', 'Arial', 'Helvetica', 'sans-serif', 'Lucida Grande', 'Lucida Sans Unicode', 'Tahoma', 'Times New Roman', 'Courier New', 'Verdana', 'Geneva', 'Courier', 'Monospace', 'Times', 'Open Sans Semibold', 'Open Sans Bold' );
			/* Default Fonts */
			$fonts_li = '<ol class="arm_selectbox_heading"> Default Fonts</ol>';
			foreach ( $default_fonts as $font ) {
				if ( $font == 'inherit' ) {
					$fonts_li .= '<li data-value="' . esc_attr($font) . '" data-label="' . esc_html__( 'Inherit Fonts', 'armember-membership' ) . '">' . esc_html__( 'Inherit Fonts', 'armember-membership' ) . '</li>';
				} else {
					$fonts_li .= '<li data-value="' . esc_attr($font) . '" data-label="' . esc_attr($font) . '">' . esc_html($font) . '</li>';
				}
			}
			/* Google Fonts */
			$g_fonts   = $this->arm_google_fonts_list();
			$fonts_li .= '<ol class="arm_selectbox_heading"> ' . esc_html__( 'Google Fonts', 'armember-membership' ) . '</ol>';
			foreach ( $g_fonts as $font ) {
				$fonts_li .= '<li data-value="' . esc_attr($font) . '" data-label="' . esc_attr($font) . '">' . esc_html($font) . '</li>';
			}
			return $fonts_li;
		}

		function arm_google_fonts_list() {
			global $wp, $wpdb, $ARMemberLite;
			$google_fonts = array( 'ABeeZee', 'Abel', 'Abhaya Libre', 'Abril Fatface', 'Aclonica', 'Acme', 'Actor', 'Adamina', 'Advent Pro', 'Aguafina Script', 'Akronim', 'Aladin', 'Aldrich', 'Alef', 'Alegreya', 'Alegreya SC', 'Alegreya Sans', 'Alegreya Sans SC', 'Alex Brush', 'Alfa Slab One', 'Alice', 'Alike', 'Alike Angular', 'Allan', 'Allerta', 'Allerta Stencil', 'Allura', 'Almendra', 'Almendra Display', 'Almendra SC', 'Amarante', 'Amaranth', 'Amatic SC', 'Amethysta', 'Amiko', 'Amiri', 'Amita', 'Anaheim', 'Andada', 'Andika', 'Angkor', 'Annie Use Your Telescope', 'Anonymous Pro', 'Antic', 'Antic Didone', 'Antic Slab', 'Anton', 'Arapey', 'Arbutus', 'Arbutus Slab', 'Architects Daughter', 'Archivo', 'Archivo Black', 'Archivo Narrow', 'Aref Ruqaa', 'Arima Madurai', 'Arimo', 'Arizonia', 'Armata', 'Arsenal', 'Artifika', 'Arvo', 'Arya', 'Asap', 'Asap Condensed', 'Asar', 'Asset', 'Assistant', 'Astloch', 'Asul', 'Athiti', 'Atma', 'Atomic Age', 'Aubrey', 'Audiowide', 'Autour One', 'Average', 'Average Sans', 'Averia Gruesa Libre', 'Averia Libre', 'Averia Sans Libre', 'Averia Serif Libre', 'Bad Script', 'Bahiana', 'Bai Jamjuree', 'Baloo', 'Baloo Bhai', 'Baloo Bhaijaan', 'Baloo Bhaina', 'Baloo Chettan', 'Baloo Da', 'Baloo Paaji', 'Baloo Tamma', 'Baloo Tammudu', 'Baloo Thambi', 'Balthazar', 'Bangers', 'Barlow', 'Barlow Condensed', 'Barlow Semi Condensed', 'Barrio', 'Basic', 'Battambang', 'Baumans', 'Bayon', 'Belgrano', 'Bellefair', 'Belleza', 'BenchNine', 'Bentham', 'Berkshire Swash', 'Bevan', 'Bigelow Rules', 'Bigshot One', 'Bilbo', 'Bilbo Swash Caps', 'BioRhyme', 'BioRhyme Expanded', 'Biryani', 'Bitter', 'Black And White Picture', 'Black Han Sans', 'Black Ops One', 'Bokor', 'Bonbon', 'Boogaloo', 'Bowlby One', 'Bowlby One SC', 'Brawler', 'Bree Serif', 'Bubblegum Sans', 'Bubbler One', 'Buda', 'Buenard', 'Bungee', 'Bungee Hairline', 'Bungee Inline', 'Bungee Outline', 'Bungee Shade', 'Butcherman', 'Butterfly Kids', 'Cabin', 'Cabin Condensed', 'Cabin Sketch', 'Caesar Dressing', 'Cagliostro', 'Cairo', 'Calligraffitti', 'Cambay', 'Cambo', 'Candal', 'Cantarell', 'Cantata One', 'Cantora One', 'Capriola', 'Cardo', 'Carme', 'Carrois Gothic', 'Carrois Gothic SC', 'Carter One', 'Catamaran', 'Caudex', 'Caveat', 'Caveat Brush', 'Cedarville Cursive', 'Ceviche One', 'Chakra Petch', 'Changa', 'Changa One', 'Chango', 'Charmonman', 'Chathura', 'Chau Philomene One', 'Chela One', 'Chelsea Market', 'Chenla', 'Cherry Cream Soda', 'Cherry Swash', 'Chewy', 'Chicle', 'Chivo', 'Chonburi', 'Cinzel', 'Cinzel Decorative', 'Clicker Script', 'Coda', 'Coda Caption', 'Codystar', 'Coiny', 'Combo', 'Comfortaa', 'Coming Soon', 'Concert One', 'Condiment', 'Content', 'Contrail One', 'Convergence', 'Cookie', 'Copse', 'Corben', 'Cormorant', 'Cormorant Garamond', 'Cormorant Infant', 'Cormorant SC', 'Cormorant Unicase', 'Cormorant Upright', 'Courgette', 'Cousine', 'Coustard', 'Covered By Your Grace', 'Crafty Girls', 'Creepster', 'Crete Round', 'Crimson Text', 'Croissant One', 'Crushed', 'Cuprum', 'Cute Font', 'Cutive', 'Cutive Mono', 'Damion', 'Dancing Script', 'Dangrek', 'David Libre', 'Dawning of a New Day', 'Days One', 'Dekko', 'Delius', 'Delius Swash Caps', 'Delius Unicase', 'Della Respira', 'Denk One', 'Devonshire', 'Dhurjati', 'Didact Gothic', 'Diplomata', 'Diplomata SC', 'Do Hyeon', 'Dokdo', 'Domine', 'Donegal One', 'Doppio One', 'Dorsa', 'Dosis', 'Dr Sugiyama', 'Duru Sans', 'Dynalight', 'EB Garamond', 'Eagle Lake', 'East Sea Dokdo', 'Eater', 'Economica', 'Eczar', 'El Messiri', 'Electrolize', 'Elsie', 'Elsie Swash Caps', 'Emblema One', 'Emilys Candy', 'Encode Sans', 'Encode Sans Condensed', 'Encode Sans Expanded', 'Encode Sans Semi Condensed', 'Encode Sans Semi Expanded', 'Engagement', 'Englebert', 'Enriqueta', 'Erica One', 'Esteban', 'Euphoria Script', 'Ewert', 'Exo', 'Exo 2', 'Expletus Sans', 'Fahkwang', 'Fanwood Text', 'Farsan', 'Fascinate', 'Fascinate Inline', 'Faster One', 'Fasthand', 'Fauna One', 'Faustina', 'Federant', 'Federo', 'Felipa', 'Fenix', 'Finger Paint', 'Fira Mono', 'Fira Sans', 'Fira Sans Condensed', 'Fira Sans Extra Condensed', 'Fjalla One', 'Fjord One', 'Flamenco', 'Flavors', 'Fondamento', 'Fontdiner Swanky', 'Forum', 'Francois One', 'Frank Ruhl Libre', 'Freckle Face', 'Fredericka the Great', 'Fredoka One', 'Freehand', 'Fresca', 'Frijole', 'Fruktur', 'Fugaz One', 'GFS Didot', 'GFS Neohellenic', 'Gabriela', 'Gaegu', 'Gafata', 'Galada', 'Galdeano', 'Galindo', 'Gamja Flower', 'Gentium Basic', 'Gentium Book Basic', 'Geo', 'Geostar', 'Geostar Fill', 'Germania One', 'Gidugu', 'Gilda Display', 'Give You Glory', 'Glass Antiqua', 'Glegoo', 'Gloria Hallelujah', 'Goblin One', 'Gochi Hand', 'Gorditas', 'Gothic A1', 'Goudy Bookletter 1911', 'Graduate', 'Grand Hotel', 'Gravitas One', 'Great Vibes', 'Griffy', 'Gruppo', 'Gudea', 'Gugi', 'Gurajada', 'Habibi', 'Halant', 'Hammersmith One', 'Hanalei', 'Hanalei Fill', 'Handlee', 'Hanuman', 'Happy Monkey', 'Harmattan', 'Headland One', 'Heebo', 'Henny Penny', 'Herr Von Muellerhoff', 'Hi Melody', 'Hind', 'Hind Guntur', 'Hind Madurai', 'Hind Siliguri', 'Hind Vadodara', 'Holtwood One SC', 'Homemade Apple', 'Homenaje', 'IBM Plex Mono', 'IBM Plex Sans', 'IBM Plex Sans Condensed', 'IBM Plex Serif', 'IM Fell DW Pica', 'IM Fell DW Pica SC', 'IM Fell Double Pica', 'IM Fell Double Pica SC', 'IM Fell English', 'IM Fell English SC', 'IM Fell French Canon', 'IM Fell French Canon SC', 'IM Fell Great Primer', 'IM Fell Great Primer SC', 'Iceberg', 'Iceland', 'Imprima', 'Inconsolata', 'Inder', 'Indie Flower', 'Inika', 'Inknut Antiqua', 'Irish Grover', 'Istok Web', 'Italiana', 'Italianno', 'Itim', 'Jacques Francois', 'Jacques Francois Shadow', 'Jaldi', 'Jim Nightshade', 'Jockey One', 'Jolly Lodger', 'Jomhuria', 'Josefin Sans', 'Josefin Slab', 'Joti One', 'Jua', 'Judson', 'Julee', 'Julius Sans One', 'Junge', 'Jura', 'Just Another Hand', 'Just Me Again Down Here', 'K2D', 'Kadwa', 'Kalam', 'Kameron', 'Kanit', 'Kantumruy', 'Karla', 'Karma', 'Katibeh', 'Kaushan Script', 'Kavivanar', 'Kavoon', 'Kdam Thmor', 'Keania One', 'Kelly Slab', 'Kenia', 'Khand', 'Khmer', 'Khula', 'Kirang Haerang', 'Kite One', 'Knewave', 'KoHo', 'Kodchasan', 'Kosugi', 'Kosugi Maru', 'Kotta One', 'Koulen', 'Kranky', 'Kreon', 'Kristi', 'Krona One', 'Krub', 'Kumar One', 'Kumar One Outline', 'Kurale', 'La Belle Aurore', 'Laila', 'Lakki Reddy', 'Lalezar', 'Lancelot', 'Lateef', 'Lato', 'League Script', 'Leckerli One', 'Ledger', 'Lekton', 'Lemon', 'Lemonada', 'Libre Barcode 128', 'Libre Barcode 128 Text', 'Libre Barcode 39', 'Libre Barcode 39 Extended', 'Libre Barcode 39 Extended Text', 'Libre Barcode 39 Text', 'Libre Baskerville', 'Libre Franklin', 'Life Savers', 'Lilita One', 'Lily Script One', 'Limelight', 'Linden Hill', 'Lobster', 'Lobster Two', 'Londrina Outline', 'Londrina Shadow', 'Londrina Sketch', 'Londrina Solid', 'Lora', 'Love Ya Like A Sister', 'Loved by the King', 'Lovers Quarrel', 'Luckiest Guy', 'Lusitana', 'Lustria', 'M PLUS 1p', 'M PLUS Rounded 1c', 'Macondo', 'Macondo Swash Caps', 'Mada', 'Magra', 'Maiden Orange', 'Maitree', 'Mako', 'Mali', 'Mallanna', 'Mandali', 'Manuale', 'Marcellus', 'Marcellus SC', 'Marck Script', 'Margarine', 'Markazi Text', 'Marko One', 'Marmelad', 'Martel', 'Martel Sans', 'Marvel', 'Mate', 'Mate SC', 'Maven Pro', 'McLaren', 'Meddon', 'MedievalSharp', 'Medula One', 'Meera Inimai', 'Megrim', 'Meie Script', 'Merienda', 'Merienda One', 'Merriweather', 'Merriweather Sans', 'Metal', 'Metal Mania', 'Metamorphous', 'Metrophobic', 'Michroma', 'Milonga', 'Miltonian', 'Miltonian Tattoo', 'Mina', 'Miniver', 'Miriam Libre', 'Mirza', 'Miss Fajardose', 'Mitr', 'Modak', 'Modern Antiqua', 'Mogra', 'Molengo', 'Molle', 'Monda', 'Monofett', 'Monoton', 'Monsieur La Doulaise', 'Montaga', 'Montez', 'Montserrat', 'Montserrat Alternates', 'Montserrat Subrayada', 'Moul', 'Moulpali', 'Mountains of Christmas', 'Mouse Memoirs', 'Mr Bedfort', 'Mr Dafoe', 'Mr De Haviland', 'Mrs Saint Delafield', 'Mrs Sheppards', 'Mukta', 'Mukta Mahee', 'Mukta Malar', 'Mukta Vaani', 'Muli', 'Mystery Quest', 'NTR', 'Nanum Brush Script', 'Nanum Gothic', 'Nanum Gothic Coding', 'Nanum Myeongjo', 'Nanum Pen Script', 'Neucha', 'Neuton', 'New Rocker', 'News Cycle', 'Niconne', 'Niramit', 'Nixie One', 'Nobile', 'Nokora', 'Norican', 'Nosifer', 'Notable', 'Nothing You Could Do', 'Noticia Text', 'Noto Sans', 'Noto Sans JP', 'Noto Sans KR', 'Noto Serif', 'Noto Serif JP', 'Noto Serif KR', 'Nova Cut', 'Nova Flat', 'Nova Mono', 'Nova Oval', 'Nova Round', 'Nova Script', 'Nova Slim', 'Nova Square', 'Numans', 'Nunito', 'Nunito Sans', 'Odor Mean Chey', 'Offside', 'Old Standard TT', 'Oldenburg', 'Oleo Script', 'Oleo Script Swash Caps', 'Open Sans', 'Open Sans Condensed', 'Oranienbaum', 'Orbitron', 'Oregano', 'Orienta', 'Original Surfer', 'Oswald', 'Over the Rainbow', 'Overlock', 'Overlock SC', 'Overpass', 'Overpass Mono', 'Ovo', 'Oxygen', 'Oxygen Mono', 'PT Mono', 'PT Sans', 'PT Sans Caption', 'PT Sans Narrow', 'PT Serif', 'PT Serif Caption', 'Pacifico', 'Padauk', 'Palanquin', 'Palanquin Dark', 'Pangolin', 'Paprika', 'Parisienne', 'Passero One', 'Passion One', 'Pathway Gothic One', 'Patrick Hand', 'Patrick Hand SC', 'Pattaya', 'Patua One', 'Pavanam', 'Paytone One', 'Peddana', 'Peralta', 'Permanent Marker', 'Petit Formal Script', 'Petrona', 'Philosopher', 'Piedra', 'Pinyon Script', 'Pirata One', 'Plaster', 'Play', 'Playball', 'Playfair Display', 'Playfair Display SC', 'Podkova', 'Poiret One', 'Poller One', 'Poly', 'Pompiere', 'Pontano Sans', 'Poor Story', 'Poppins', 'Port Lligat Sans', 'Port Lligat Slab', 'Pragati Narrow', 'Prata', 'Preahvihear', 'Press Start 2P', 'Pridi', 'Princess Sofia', 'Prociono', 'Prompt', 'Prosto One', 'Proza Libre', 'Puritan', 'Purple Purse', 'Quando', 'Quantico', 'Quattrocento', 'Quattrocento Sans', 'Questrial', 'Quicksand', 'Quintessential', 'Qwigley', 'Racing Sans One', 'Radley', 'Rajdhani', 'Rakkas', 'Raleway', 'Raleway Dots', 'Ramabhadra', 'Ramaraja', 'Rambla', 'Rammetto One', 'Ranchers', 'Rancho', 'Ranga', 'Rasa', 'Rationale', 'Ravi Prakash', 'Redressed', 'Reem Kufi', 'Reenie Beanie', 'Revalia', 'Rhodium Libre', 'Ribeye', 'Ribeye Marrow', 'Righteous', 'Risque', 'Roboto', 'Roboto Condensed', 'Roboto Mono', 'Roboto Slab', 'Rochester', 'Rock Salt', 'Rokkitt', 'Romanesco', 'Ropa Sans', 'Rosario', 'Rosarivo', 'Rouge Script', 'Rozha One', 'Rubik', 'Rubik Mono One', 'Ruda', 'Rufina', 'Ruge Boogie', 'Ruluko', 'Rum Raisin', 'Ruslan Display', 'Russo One', 'Ruthie', 'Rye', 'Sacramento', 'Sahitya', 'Sail', 'Saira', 'Saira Condensed', 'Saira Extra Condensed', 'Saira Semi Condensed', 'Salsa', 'Sanchez', 'Sancreek', 'Sansita', 'Sarala', 'Sarina', 'Sarpanch', 'Satisfy', 'Sawarabi Gothic', 'Sawarabi Mincho', 'Scada', 'Scheherazade', 'Schoolbell', 'Scope One', 'Seaweed Script', 'Secular One', 'Sedgwick Ave', 'Sedgwick Ave Display', 'Sevillana', 'Seymour One', 'Shadows Into Light', 'Shadows Into Light Two', 'Shanti', 'Share', 'Share Tech', 'Share Tech Mono', 'Shojumaru', 'Short Stack', 'Shrikhand', 'Siemreap', 'Sigmar One', 'Signika', 'Signika Negative', 'Simonetta', 'Sintony', 'Sirin Stencil', 'Six Caps', 'Skranji', 'Slabo 13px', 'Slabo 27px', 'Slackey', 'Smokum', 'Smythe', 'Sniglet', 'Snippet', 'Snowburst One', 'Sofadi One', 'Sofia', 'Song Myung', 'Sonsie One', 'Sorts Mill Goudy', 'Source Code Pro', 'Source Sans Pro', 'Source Serif Pro', 'Space Mono', 'Special Elite', 'Spectral', 'Spectral SC', 'Spicy Rice', 'Spinnaker', 'Spirax', 'Squada One', 'Sree Krushnadevaraya', 'Sriracha', 'Srisakdi', 'Stalemate', 'Stalinist One', 'Stardos Stencil', 'Stint Ultra Condensed', 'Stint Ultra Expanded', 'Stoke', 'Strait', 'Stylish', 'Sue Ellen Francisco', 'Suez One', 'Sumana', 'Sunflower', 'Sunshiney', 'Supermercado One', 'Sura', 'Suranna', 'Suravaram', 'Suwannaphum', 'Swanky and Moo Moo', 'Syncopate', 'Tajawal', 'Tangerine', 'Taprom', 'Tauri', 'Taviraj', 'Teko', 'Telex', 'Tenali Ramakrishna', 'Tenor Sans', 'Text Me One', 'The Girl Next Door', 'Tienne', 'Tillana', 'Timmana', 'Tinos', 'Titan One', 'Titillium Web', 'Trade Winds', 'Trirong', 'Trocchi', 'Trochut', 'Trykker', 'Tulpen One', 'Ubuntu', 'Ubuntu Condensed', 'Ubuntu Mono', 'Ultra', 'Uncial Antiqua', 'Underdog', 'Unica One', 'UnifrakturCook', 'UnifrakturMaguntia', 'Unkempt', 'Unlock', 'Unna', 'VT323', 'Vampiro One', 'Varela', 'Varela Round', 'Vast Shadow', 'Vesper Libre', 'Vibur', 'Vidaloka', 'Viga', 'Voces', 'Volkhov', 'Vollkorn', 'Vollkorn SC', 'Voltaire', 'Waiting for the Sunrise', 'Wallpoet', 'Walter Turncoat', 'Warnes', 'Wellfleet', 'Wendy One', 'Wire One', 'Work Sans', 'Yanone Kaffeesatz', 'Yantramanav', 'Yatra One', 'Yellowtail', 'Yeon Sung', 'Yeseva One', 'Yesteryear', 'Yrsa', 'Zeyada', 'Zilla Slab', 'Zilla Slab Highlight' );
			return $google_fonts;
		}

		function arm_load_google_fonts( $type = 'wp' ) {
			global $wp, $wpdb, $ARMemberLite;
			/* Google Font Lists */
			$g_fonts          = $this->arm_google_fonts_list();
			$diff             = count( $g_fonts ) / 2;
			$google_fonts_one = $g_fonts;
			$google_fonts_two = $g_fonts;
			array_splice( $google_fonts_one, $diff );
			array_splice( $google_fonts_two, 0, -$diff );
			$google_fonts_string_one = implode( '|', $google_fonts_one );
			$google_fonts_string_two = implode( '|', $google_fonts_two );
			$google_font_url_one     = $google_font_url_two = '';
			if ( is_ssl() ) {
				$google_font_url_one = 'https://fonts.googleapis.com/css?family=' . $google_fonts_string_one;
				$google_font_url_two = 'https://fonts.googleapis.com/css?family=' . $google_fonts_string_two;
			} else {
				$google_font_url_one = 'http://fonts.googleapis.com/css?family=' . $google_fonts_string_one;
				$google_font_url_two = 'http://fonts.googleapis.com/css?family=' . $google_fonts_string_two;
			}
			if ( $type == 'editor' ) {
				add_editor_style( $google_font_url_one );
				add_editor_style( $google_font_url_two );
			} else {
				wp_register_style( 'arm_googlefonts1', $google_font_url_one, array(), MEMBERSHIPLITE_VERSION );
				wp_register_style( 'arm_googlefonts2', $google_font_url_two, array(), MEMBERSHIPLITE_VERSION );
				wp_enqueue_style( 'arm_googlefonts1' );
				wp_enqueue_style( 'arm_googlefonts2' );
			}
		}

		function arm_get_google_fonts_url( $fontString = array() ) {
			global $wp, $wpdb, $arm_slugs, $ARMemberLite;
			$google_font_url = '';
			if ( ! empty( $fontString ) ) {
				$googleFonts = array();
				$fontString  = $ARMemberLite->arm_array_unique( $fontString );
				$g_fonts     = $this->arm_google_fonts_list();
				foreach ( $g_fonts as $font ) {
					if ( in_array( $font, $fontString ) ) {
						$googleFonts[] = $font;
					}
				}
				if ( ! empty( $googleFonts ) ) {
					$google_fonts_string = implode( '|', $googleFonts );
					if ( is_ssl() ) {
						$google_font_url = 'https://fonts.googleapis.com/css?family=' . $google_fonts_string;
					} else {
						$google_font_url = 'http://fonts.googleapis.com/css?family=' . $google_fonts_string;
					}
				}
			}
			return $google_font_url;
		}

		function arm_get_countries() {
			return apply_filters(
				'arm_countries',
				array(
					1   => esc_html__( 'Afghanistan', 'armember-membership' ),
					2   => esc_html__( 'Albania', 'armember-membership' ),
					3   => esc_html__( 'Algeria', 'armember-membership' ),
					4   => esc_html__( 'American Samoa', 'armember-membership' ),
					5   => esc_html__( 'Andorra', 'armember-membership' ),
					6   => esc_html__( 'Angola', 'armember-membership' ),
					7   => esc_html__( 'Anguilla', 'armember-membership' ),
					8   => esc_html__( 'Antarctica', 'armember-membership' ),
					9   => esc_html__( 'Antigua and Barbuda', 'armember-membership' ),
					10  => esc_html__( 'Argentina', 'armember-membership' ),
					11  => esc_html__( 'Armenia', 'armember-membership' ),
					12  => esc_html__( 'Aruba', 'armember-membership' ),
					13  => esc_html__( 'Australia', 'armember-membership' ),
					14  => esc_html__( 'Austria', 'armember-membership' ),
					15  => esc_html__( 'Azerbaijan', 'armember-membership' ),
					16  => esc_html__( 'Bahamas', 'armember-membership' ),
					17  => esc_html__( 'Bahrain', 'armember-membership' ),
					18  => esc_html__( 'Bangladesh', 'armember-membership' ),
					19  => esc_html__( 'Barbados', 'armember-membership' ),
					20  => esc_html__( 'Belarus', 'armember-membership' ),
					21  => esc_html__( 'Belgium', 'armember-membership' ),
					22  => esc_html__( 'Belize', 'armember-membership' ),
					23  => esc_html__( 'Benin', 'armember-membership' ),
					24  => esc_html__( 'Bermuda', 'armember-membership' ),
					25  => esc_html__( 'Bhutan', 'armember-membership' ),
					26  => esc_html__( 'Bolivia', 'armember-membership' ),
					27  => esc_html__( 'Bosnia and Herzegovina', 'armember-membership' ),
					28  => esc_html__( 'Botswana', 'armember-membership' ),
					29  => esc_html__( 'Brazil', 'armember-membership' ),
					30  => esc_html__( 'Brunei', 'armember-membership' ),
					31  => esc_html__( 'Bulgaria', 'armember-membership' ),
					32  => esc_html__( 'Burkina Faso', 'armember-membership' ),
					33  => esc_html__( 'Burundi', 'armember-membership' ),
					34  => esc_html__( 'Cambodia', 'armember-membership' ),
					35  => esc_html__( 'Cameroon', 'armember-membership' ),
					36  => esc_html__( 'Canada', 'armember-membership' ),
					37  => esc_html__( 'Cape Verde', 'armember-membership' ),
					38  => esc_html__( 'Cayman Islands', 'armember-membership' ),
					39  => esc_html__( 'Central African Republic', 'armember-membership' ),
					40  => esc_html__( 'Chad', 'armember-membership' ),
					41  => esc_html__( 'Chile', 'armember-membership' ),
					42  => esc_html__( 'China', 'armember-membership' ),
					43  => esc_html__( 'Colombia', 'armember-membership' ),
					44  => esc_html__( 'Comoros', 'armember-membership' ),
					45  => esc_html__( 'Congo', 'armember-membership' ),
					46  => esc_html__( 'Costa Rica', 'armember-membership' ),
					47  => esc_html__( 'Croatia', 'armember-membership' ),
					48  => esc_html__( 'Cuba', 'armember-membership' ),
					49  => esc_html__( 'Cyprus', 'armember-membership' ),
					50  => esc_html__( 'Czech Republic', 'armember-membership' ),
					51  => esc_html__( 'Denmark', 'armember-membership' ),
					52  => esc_html__( 'Djibouti', 'armember-membership' ),
					53  => esc_html__( 'Dominica', 'armember-membership' ),
					54  => esc_html__( 'Dominican Republic', 'armember-membership' ),
					55  => esc_html__( 'East Timor', 'armember-membership' ),
					56  => esc_html__( 'Ecuador', 'armember-membership' ),
					57  => esc_html__( 'Egypt', 'armember-membership' ),
					58  => esc_html__( 'El Salvador', 'armember-membership' ),
					59  => esc_html__( 'Equatorial Guinea', 'armember-membership' ),
					60  => esc_html__( 'Eritrea', 'armember-membership' ),
					61  => esc_html__( 'Estonia', 'armember-membership' ),
					62  => esc_html__( 'Ethiopia', 'armember-membership' ),
					63  => esc_html__( 'Fiji', 'armember-membership' ),
					64  => esc_html__( 'Finland', 'armember-membership' ),
					65  => esc_html__( 'France', 'armember-membership' ),
					66  => esc_html__( 'French Guiana', 'armember-membership' ),
					67  => esc_html__( 'French Polynesia', 'armember-membership' ),
					68  => esc_html__( 'Gabon', 'armember-membership' ),
					69  => esc_html__( 'Gambia', 'armember-membership' ),
					70  => esc_html__( 'Georgia', 'armember-membership' ),
					71  => esc_html__( 'Germany', 'armember-membership' ),
					72  => esc_html__( 'Ghana', 'armember-membership' ),
					73  => esc_html__( 'Gibraltar', 'armember-membership' ),
					74  => esc_html__( 'Greece', 'armember-membership' ),
					75  => esc_html__( 'Greenland', 'armember-membership' ),
					76  => esc_html__( 'Grenada', 'armember-membership' ),
					77  => esc_html__( 'Guam', 'armember-membership' ),
					78  => esc_html__( 'Guatemala', 'armember-membership' ),
					79  => esc_html__( 'Guinea', 'armember-membership' ),
					80  => esc_html__( 'Guinea-Bissau', 'armember-membership' ),
					81  => esc_html__( 'Guyana', 'armember-membership' ),
					82  => esc_html__( 'Haiti', 'armember-membership' ),
					83  => esc_html__( 'Honduras', 'armember-membership' ),
					84  => esc_html__( 'Hong Kong', 'armember-membership' ),
					85  => esc_html__( 'Hungary', 'armember-membership' ),
					86  => esc_html__( 'Iceland', 'armember-membership' ),
					87  => esc_html__( 'India', 'armember-membership' ),
					88  => esc_html__( 'Indonesia', 'armember-membership' ),
					89  => esc_html__( 'Iran', 'armember-membership' ),
					90  => esc_html__( 'Iraq', 'armember-membership' ),
					91  => esc_html__( 'Ireland', 'armember-membership' ),
					92  => esc_html__( 'Israel', 'armember-membership' ),
					93  => esc_html__( 'Italy', 'armember-membership' ),
					94  => esc_html__( 'Jamaica', 'armember-membership' ),
					95  => esc_html__( 'Japan', 'armember-membership' ),
					96  => esc_html__( 'Jordan', 'armember-membership' ),
					97  => esc_html__( 'Kazakhstan', 'armember-membership' ),
					98  => esc_html__( 'Kenya', 'armember-membership' ),
					99  => esc_html__( 'Kiribati', 'armember-membership' ),
					100 => esc_html__( 'North Korea', 'armember-membership' ),
					101 => esc_html__( 'South Korea', 'armember-membership' ),
					102 => esc_html__( 'Kuwait', 'armember-membership' ),
					103 => esc_html__( 'Kyrgyzstan', 'armember-membership' ),
					104 => esc_html__( 'Laos', 'armember-membership' ),
					105 => esc_html__( 'Latvia', 'armember-membership' ),
					106 => esc_html__( 'Lebanon', 'armember-membership' ),
					107 => esc_html__( 'Lesotho', 'armember-membership' ),
					108 => esc_html__( 'Liberia', 'armember-membership' ),
					109 => esc_html__( 'Libya', 'armember-membership' ),
					110 => esc_html__( 'Liechtenstein', 'armember-membership' ),
					111 => esc_html__( 'Lithuania', 'armember-membership' ),
					112 => esc_html__( 'Luxembourg', 'armember-membership' ),
					113 => esc_html__( 'Macedonia', 'armember-membership' ),
					114 => esc_html__( 'Madagascar', 'armember-membership' ),
					115 => esc_html__( 'Malawi', 'armember-membership' ),
					116 => esc_html__( 'Malaysia', 'armember-membership' ),
					117 => esc_html__( 'Maldives', 'armember-membership' ),
					118 => esc_html__( 'Mali', 'armember-membership' ),
					119 => esc_html__( 'Malta', 'armember-membership' ),
					120 => esc_html__( 'Marshall Islands', 'armember-membership' ),
					121 => esc_html__( 'Mauritania', 'armember-membership' ),
					122 => esc_html__( 'Mauritius', 'armember-membership' ),
					123 => esc_html__( 'Mexico', 'armember-membership' ),
					124 => esc_html__( 'Micronesia', 'armember-membership' ),
					125 => esc_html__( 'Moldova', 'armember-membership' ),
					126 => esc_html__( 'Monaco', 'armember-membership' ),
					127 => esc_html__( 'Mongolia', 'armember-membership' ),
					128 => esc_html__( 'Montenegro', 'armember-membership' ),
					129 => esc_html__( 'Montserrat', 'armember-membership' ),
					130 => esc_html__( 'Morocco', 'armember-membership' ),
					131 => esc_html__( 'Mozambique', 'armember-membership' ),
					132 => esc_html__( 'Myanmar', 'armember-membership' ),
					133 => esc_html__( 'Namibia', 'armember-membership' ),
					134 => esc_html__( 'Nauru', 'armember-membership' ),
					135 => esc_html__( 'Nepal', 'armember-membership' ),
					136 => esc_html__( 'Netherlands', 'armember-membership' ),
					137 => esc_html__( 'New Zealand', 'armember-membership' ),
					138 => esc_html__( 'Nicaragua', 'armember-membership' ),
					139 => esc_html__( 'Niger', 'armember-membership' ),
					140 => esc_html__( 'Nigeria', 'armember-membership' ),
					141 => esc_html__( 'Norway', 'armember-membership' ),
					142 => esc_html__( 'Northern Mariana Islands', 'armember-membership' ),
					143 => esc_html__( 'Oman', 'armember-membership' ),
					144 => esc_html__( 'Pakistan', 'armember-membership' ),
					145 => esc_html__( 'Palau', 'armember-membership' ),
					146 => esc_html__( 'Palestine', 'armember-membership' ),
					147 => esc_html__( 'Panama', 'armember-membership' ),
					148 => esc_html__( 'Papua New Guinea', 'armember-membership' ),
					149 => esc_html__( 'Paraguay', 'armember-membership' ),
					150 => esc_html__( 'Peru', 'armember-membership' ),
					151 => esc_html__( 'Philippines', 'armember-membership' ),
					152 => esc_html__( 'Poland', 'armember-membership' ),
					153 => esc_html__( 'Portugal', 'armember-membership' ),
					154 => esc_html__( 'Puerto Rico', 'armember-membership' ),
					155 => esc_html__( 'Qatar', 'armember-membership' ),
					211 => esc_html__( 'Reunion Island', 'armember-membership' ),
					156 => esc_html__( 'Romania', 'armember-membership' ),
					157 => esc_html__( 'Russia', 'armember-membership' ),
					158 => esc_html__( 'Rwanda', 'armember-membership' ),
					159 => esc_html__( 'Saint Kitts and Nevis', 'armember-membership' ),
					160 => esc_html__( 'Saint Lucia', 'armember-membership' ),
					161 => esc_html__( 'Saint Vincent and the Grenadines', 'armember-membership' ),
					162 => esc_html__( 'Samoa', 'armember-membership' ),
					163 => esc_html__( 'San Marino', 'armember-membership' ),
					164 => esc_html__( 'Sao Tome and Principe', 'armember-membership' ),
					165 => esc_html__( 'Saudi Arabia', 'armember-membership' ),
					166 => esc_html__( 'Senegal', 'armember-membership' ),
					167 => esc_html__( 'Serbia and Montenegro', 'armember-membership' ),
					168 => esc_html__( 'Seychelles', 'armember-membership' ),
					169 => esc_html__( 'Sierra Leone', 'armember-membership' ),
					170 => esc_html__( 'Singapore', 'armember-membership' ),
					171 => esc_html__( 'Slovakia', 'armember-membership' ),
					172 => esc_html__( 'Slovenia', 'armember-membership' ),
					173 => esc_html__( 'Solomon Islands', 'armember-membership' ),
					174 => esc_html__( 'Somalia', 'armember-membership' ),
					175 => esc_html__( 'South Africa', 'armember-membership' ),
					176 => esc_html__( 'Spain', 'armember-membership' ),
					177 => esc_html__( 'Sri Lanka', 'armember-membership' ),
					178 => esc_html__( 'Sudan', 'armember-membership' ),
					179 => esc_html__( 'Suriname', 'armember-membership' ),
					180 => esc_html__( 'Swaziland', 'armember-membership' ),
					181 => esc_html__( 'Sweden', 'armember-membership' ),
					182 => esc_html__( 'Switzerland', 'armember-membership' ),
					183 => esc_html__( 'Syria', 'armember-membership' ),
					184 => esc_html__( 'Taiwan', 'armember-membership' ),
					185 => esc_html__( 'Tajikistan', 'armember-membership' ),
					186 => esc_html__( 'Tanzania', 'armember-membership' ),
					187 => esc_html__( 'Thailand', 'armember-membership' ),
					188 => esc_html__( 'Togo', 'armember-membership' ),
					189 => esc_html__( 'Tonga', 'armember-membership' ),
					190 => esc_html__( 'Trinidad and Tobago', 'armember-membership' ),
					191 => esc_html__( 'Tunisia', 'armember-membership' ),
					192 => esc_html__( 'Turkey', 'armember-membership' ),
					193 => esc_html__( 'Turkmenistan', 'armember-membership' ),
					194 => esc_html__( 'Tuvalu', 'armember-membership' ),
					195 => esc_html__( 'Uganda', 'armember-membership' ),
					196 => esc_html__( 'Ukraine', 'armember-membership' ),
					197 => esc_html__( 'United Arab Emirates', 'armember-membership' ),
					198 => esc_html__( 'United Kingdom', 'armember-membership' ),
					199 => esc_html__( 'United States', 'armember-membership' ),
					200 => esc_html__( 'Uruguay', 'armember-membership' ),
					201 => esc_html__( 'Uzbekistan', 'armember-membership' ),
					202 => esc_html__( 'Vanuatu', 'armember-membership' ),
					203 => esc_html__( 'Vatican City', 'armember-membership' ),
					204 => esc_html__( 'Venezuela', 'armember-membership' ),
					205 => esc_html__( 'Vietnam', 'armember-membership' ),
					206 => esc_html__( 'Virgin Islands, British', 'armember-membership' ),
					207 => esc_html__( 'Virgin Islands, U.S.', 'armember-membership' ),
					208 => esc_html__( 'Yemen', 'armember-membership' ),
					209 => esc_html__( 'Zambia', 'armember-membership' ),
					210 => esc_html__( 'Zimbabwe', 'armember-membership' ),
				)
			);
		}

		function arm_check_form_include_js_css( $form, $atts ) {
			global $ARMemberLite;
			$form_style = '';
			if ( ! empty( $form ) ) {
				$form_style = isset( $form->settings['style']['form_layout'] ) ? $form->settings['style']['form_layout'] : '';
			}
			$ARMemberLite->set_front_css( false, $form_style );
			$ARMemberLite->set_front_js( true );
		}

		function arm_get_spf_in_tinymce() {
			global $wpdb, $ARMemberLite, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_members'], '1' );

			$form_name = isset( $_REQUEST['form_name'] ) ? esc_html( sanitize_text_field($_REQUEST['form_name']) ) : ''; //phpcs:ignore
			$is_vc     = isset( $_REQUEST['is_vc'] ) ? esc_html( sanitize_text_field($_REQUEST['is_vc']) ) : false; //phpcs:ignore
			if ( $form_name === '' ) {
				echo wp_json_encode( array( 'error' => true ) );
				die();
			} else {
				$content = '';
				if ( $is_vc != false ) {
					$content .= "<input type='hidden' name='social_fields' class='wpb_vc_param_value' id='social_fields_hidden' value='' />";
				}
				$all_spfields       = $this->arm_social_profile_field_types();
				$form_id            = $form_name;
				$form_social_fields = $wpdb->get_row( $wpdb->prepare( "SELECT arm_form_field_option FROM `".$ARMemberLite->tbl_arm_form_field."`  WHERE arm_form_field_form_id = %d AND arm_form_field_slug = %s ", $form_id, 'social_fields' ) );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_form_field is a table name.
				$active_spf         = array();
				if ( ! empty( $form_social_fields ) ) {
					$field_options = maybe_unserialize( $form_social_fields->arm_form_field_option );
					$active_spf    = $field_options['options'];
					$content      .= "<div class='arm_social_field_popup_wrapper'>";
					foreach ( $all_spfields as $SPFKey => $SPFLabel ) {
						$checked = '';
						if ( is_array( $active_spf ) && in_array( $SPFKey, $active_spf ) ) {
							$checked = 'checked="checked" disabled="disabled"';
						}
						if ( $is_vc != true ) {
							$content .= "<div class='arm_social_profile_field_item'>";
							$content .= "<input type='checkbox' class='arm_icheckbox arm_spf_active_checkbox arm_shortcode_form_popup_opt' value='". esc_attr( $SPFKey ) ."' name='arm_social_fields[]' id='arm_spf_". esc_attr( $SPFKey ) ."_status' {$checked} />";
							$content .= "<label for='arm_spf_". esc_attr( $SPFKey ) ."_status' aria-label='" . esc_attr( $SPFLabel ) . "'>". esc_html( $SPFLabel )."</label>";
							$content .= '</div>';
						} else {
							$content .= "<label class='arm_social_profile_field_item' for='arm_spf_{$SPFKey}_status' aria-label='" . esc_attr( $SPFLabel ) . "'>";
							$content .= "<input type='checkbox' class='arm_icheckbox arm_spf_active_checkbox arm_shortcode_form_popup_opt arm_spf_active_checkbox_input' value='". esc_attr( $SPFKey ) ."' onchange='arm_select_social_fields()' name='arm_social_fields[]' id='arm_spf_{$SPFKey}_status' {$checked} />";
							$content .= "<span>". esc_html( $SPFLabel )."</span>";
							$content .= '</label>';
						}
					}
					$content .= '</div>';
				} else {
					$content .= "<div class='arm_social_field_popup_wrapper'>";
					foreach ( $all_spfields as $SPFKey => $SPFLabel ) {
						$checked = '';
						if ( is_array( $active_spf ) && in_array( $SPFKey, $active_spf ) ) {
							$checked = 'checked="checked" disabled="disabled"';
						}
						if ( $is_vc != true ) {
							$content .= "<div class='arm_social_profile_field_item'>";
							$content .= "<input type='checkbox' class='arm_icheckbox arm_spf_active_checkbox arm_shortcode_form_popup_opt' value='". esc_attr( $SPFKey ) ."' name='arm_social_fields[]' id='arm_spf_". esc_attr( $SPFKey ) ."_status' {$checked} />";
							$content .= "<label for='arm_spf_". esc_attr( $SPFKey ) ."_status' aria-label='" . esc_attr( $SPFLabel ) . "'>". esc_html( $SPFLabel ) ."</label>";
							$content .= '</div>';
						} else {
							$content .= "<label class='arm_social_profile_field_item' for='arm_spf_". esc_attr( $SPFKey ) ."_status' aria-label='" . esc_attr( $SPFLabel ) . "'>";
							$content .= "<input type='checkbox' class='arm_icheckbox arm_spf_active_checkbox arm_shortcode_form_popup_opt arm_spf_active_checkbox_input' value='". esc_attr( $SPFKey ) ."' onchange='arm_select_social_fields()' name='arm_social_fields[]' id='arm_spf_". esc_attr( $SPFKey ) ."_status' {$checked} />";
							$content .= "<span>". esc_html( $SPFLabel )."</span>";
							$content .= '</label>';
						}
					}
					$content .= '</div>';
				}
			}
			echo wp_json_encode(
				array(
					'error'   => false,
					'content' => stripslashes_deep( $content ),
				)
			);
			die();
		}

		function arm_default_button_gradient_color() {
			$arm_button_gradient_color                = array();
			$arm_button_gradient_color['blue']        = array(
				'button_back_color'           => '#005C97',
				'button_back_color_gradient'  => '#363795',
				'button_hover_color'          => '#005C97',
				'button_hover_color_gradient' => '#363795',
			);
			$arm_button_gradient_color['bright_cyan'] = array(
				'button_back_color'           => '#00d2ff',
				'button_back_color_gradient'  => '#3afbd5',
				'button_hover_color'          => '#00d2ff',
				'button_hover_color_gradient' => '#3afbd5',
			);
			$arm_button_gradient_color['green']       = array(
				'button_back_color'           => '#3ca55c',
				'button_back_color_gradient'  => '#b5ac49',
				'button_hover_color'          => '#3ca55c',
				'button_hover_color_gradient' => '#b5ac49',
			);
			$arm_button_gradient_color['red']         = array(
				'button_back_color'           => '#dd2476',
				'button_back_color_gradient'  => '#ff512f',
				'button_hover_color'          => '#dd2476',
				'button_hover_color_gradient' => '#ff512f',
			);
			$arm_button_gradient_color['purple']      = array(
				'button_back_color'           => '#7474BF',
				'button_back_color_gradient'  => '#348AC7',
				'button_hover_color'          => '#7474BF',
				'button_hover_color_gradient' => '#348AC7',
			);
			$arm_button_gradient_color['orange']      = array(
				'button_back_color'           => '#c21500',
				'button_back_color_gradient'  => '#ffc500',
				'button_hover_color'          => '#c21500',
				'button_hover_color_gradient' => '#ffc500',
			);
			$arm_button_gradient_color['yellow']      = array(
				'button_back_color'           => '#F09819',
				'button_back_color_gradient'  => '#EDDE5D',
				'button_hover_color'          => '#F09819',
				'button_hover_color_gradient' => '#EDDE5D',
			);
			$arm_button_gradient_color['pink']        = array(
				'button_back_color'           => '#f857a6',
				'button_back_color_gradient'  => '#ff5858',
				'button_hover_color'          => '#f857a6',
				'button_hover_color_gradient' => '#ff5858',
			);
			$arm_button_gradient_color['strong_cyan'] = array(
				'button_back_color'           => '#43cea2',
				'button_back_color_gradient'  => '#185a9d',
				'button_hover_color'          => '#43cea2',
				'button_hover_color_gradient' => '#185a9d',
			);
			$arm_button_gradient_color['gray']        = array(
				'button_back_color'           => '#283048',
				'button_back_color_gradient'  => '#859398',
				'button_hover_color'          => '#283048',
				'button_hover_color_gradient' => '#859398',
			);
			$arm_button_gradient_color['dark_purple'] = array(
				'button_back_color'           => '#1D2B64',
				'button_back_color_gradient'  => '#F8CDDA',
				'button_hover_color'          => '#1D2B64',
				'button_hover_color_gradient' => '#F8CDDA',
			);
			$arm_button_gradient_color['black']       = array(
				'button_back_color'           => '#232526',
				'button_back_color_gradient'  => '#646668',
				'button_hover_color'          => '#232526',
				'button_hover_color_gradient' => '#646668',
			);

			return apply_filters( 'arm_button_gradient_color', $arm_button_gradient_color );
		}

		function arm_auto_lock_shared_account() {

			if ( is_user_logged_in() && ! is_admin() ) {
				$user_id = get_current_user_id();

				if ( user_can( $user_id, 'administrator' ) ) {
					return;
				}
				global $arm_global_settings, $ARMemberLite, $wpdb;

				$arm_all_general_settings = isset( $arm_global_settings->global_settings ) ? $arm_global_settings->global_settings : array();

				$autolock_shared_account = ( isset( $arm_all_general_settings['autolock_shared_account'] ) ) ? $arm_all_general_settings['autolock_shared_account'] : 0;

				if ( $autolock_shared_account == 1 ) {

					if ( isset( $_COOKIE[ 'arm_autolock_cookie_' . $user_id ] ) && ! empty( $_COOKIE[ 'arm_autolock_cookie_' . $user_id ] ) ) {

						$arm_autolock_cookie = sanitize_text_field($_COOKIE[ 'arm_autolock_cookie_' . $user_id ]); //phpcs:ignore
						$stored_cookie       = $arm_autolock_cookie;
						$inserted_id         = explode( '||', $stored_cookie );
						$arm_session_id      = $inserted_id[0];
						$arm_history_id      = $inserted_id[1];
						$logged_out_time     = current_time( 'mysql' );
						$login_history_table = $ARMemberLite->tbl_arm_login_history;

						$update_query = $wpdb->prepare( "UPDATE `{$login_history_table}` SET `arm_logout_date` = %s, `arm_user_current_status` = %d WHERE `arm_history_id` != %d AND `arm_history_session` != %s AND `arm_user_id` = %d AND `arm_user_current_status` != %d", $logged_out_time, 0, $arm_history_id, $arm_session_id, $user_id, 0 );//phpcs:ignore --Reason: $login_history_table is a table name. False Positive Alarm.
						$wpdb->query( $update_query );//phpcs:ignore --Reason: $update_query is a prepared Query. False Positive Alarm.
						unset( $_COOKIE[ 'arm_autolock_cookie_' . $user_id ] );
						setcookie( 'arm_autolock_cookie_' . $user_id, '', time() - 3600, '/' );
					}

					wp_destroy_other_sessions();
				}
			}
		}

		function arm_add_login_history_for_set_logged_in_cookie( $auth_cookie, $expire, $expiration, $user_id, $scheme ) {

			global $wpdb, $ARMemberLite, $arm_global_settings, $arm_is_change_password_form_for_login, $arm_login_from_registration, $arm_is_update_password_form_edit_profile_login, $browser_session_id;

			if ( ! ( extension_loaded( 'geoip' ) ) ) {
				@include MEMBERSHIPLITE_INC_DIR . '/geoip.inc';
			}

			$arm_all_block_settings = isset( $arm_global_settings->block_settings ) ? $arm_global_settings->block_settings : array();
			$tbl_login_history      = $ARMemberLite->tbl_arm_login_history;
			$general_settings       = isset( $arm_global_settings->general_settings ) ? $arm_global_settings->general_settings : '';

			if ( isset( $arm_all_block_settings['track_login_history'] ) && $arm_all_block_settings['track_login_history'] != 1 ) {
				return;
			}

			if ( empty( $user_id ) || user_can( $user_id, 'administrator' ) ) {
				return;
			}

			if ( $arm_is_change_password_form_for_login == 1 ) {
				$arm_is_change_password_form_for_login = 0;
				return;
			}

			if ( $arm_is_update_password_form_edit_profile_login == 1 ) {
				$arm_is_update_password_form_edit_profile_login = 0;
				return;
			}

			$logged_in_ip = $ARMemberLite->arm_get_ip_address();

			$current_time        = current_time( 'timestamp' );
			$logged_in_time      = date( 'Y-m-d H:i:s', $current_time ); //phpcs:ignore
			$httpuseragent     = !empty( $_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';//phpcs:ignore	
			$browser_info        = $ARMemberLite->getBrowser( sanitize_text_field($httpuseragent) );
			$browser_detail      = $browser_info['name'] . ' (' . $browser_info['version'] . ')';
			$user_current_status = 1;

			if ( empty( $browser_session_id ) ) {
				$ARMemberLite->arm_session_start();
				$browser_session_id = session_id();
			}


			$select_result = $wpdb->get_var( $wpdb->prepare("SELECT count(*) FROM `{$tbl_login_history}` WHERE `arm_history_session` = %s AND `arm_user_current_status` = %d",$browser_session_id,1) );//phpcs:ignore --Reason: $tbl_login_history is a table name. False Positive Alarm.
			if ( $select_result > 0 ) {
				return;
			}

			$update_result = $wpdb->query( $wpdb->prepare( "UPDATE `{$tbl_login_history}` SET `arm_user_current_status` = %d  WHERE `arm_user_current_status` != %d AND `arm_user_id` = %d AND `arm_history_browser` = %s AND `arm_logged_in_ip` = %s", 0, 0, $user_id, $browser_detail, $logged_in_ip ) );//phpcs:ignore --Reason: $tbl_login_history is a table name. False positive Alarm

			$insert_result = $wpdb->query( $wpdb->prepare( "INSERT INTO `{$tbl_login_history}` (`arm_user_id`,`arm_logged_in_ip`,`arm_logged_in_date`,`arm_history_browser`,`arm_history_session`,`arm_login_country`,`arm_user_current_status`,`arm_logout_date`) VALUES (%d,%s,%s,%s,%s,%s,%d,%s)", $user_id, $logged_in_ip, $logged_in_time, $browser_detail, $browser_session_id, $country, 1, $logged_in_time ) );//phpcs:ignore --Reason: $tbl_login_history is a table name. False positive Alarm

			if ( $arm_login_from_registration == 1 ) {
				$arm_login_from_registration = 0;
				return;
			}

			if ( ! empty( $general_settings ) && ! empty( $general_settings->autolock_shared_account ) ) {
				$cookie_name          = 'arm_cookie_' . $user_id;
				$autolock_cookie_name = 'arm_autolock_cookie_' . $user_id;
				$cookie_value         = $browser_session_id . '||' . $wpdb->insert_id;
				$cookie_exp_time      = time() + 60 * 60 * 24 * 30;
				setcookie( $cookie_name, $cookie_value, $cookie_exp_time, '/' );
				setcookie( $autolock_cookie_name, $cookie_value, $cookie_exp_time, '/' );
			}
		}

		function arm_update_login_history() {

			global $wpdb, $ARMemberLite, $arm_global_settings, $arm_is_change_password_form_for_logout, $arm_is_update_password_form_edit_profile_logout;

			$arm_all_block_settings = $arm_global_settings->block_settings;
			$login_history_table    = $ARMemberLite->tbl_arm_login_history;
			$user_id                = get_current_user_id();
			if ( isset( $arm_all_block_settings['track_login_history'] ) && $arm_all_block_settings['track_login_history'] != 1 ) {
					return;
			}

			if ( user_can( $user_id, 'administrator' ) ) {
				return;
			}

			/* Check for registered COOKIE When current user is logged in */
			if ( isset( $_COOKIE[ 'arm_cookie_' . $user_id ] ) and ! empty( $_COOKIE[ 'arm_cookie_' . $user_id ] ) ) {
				$stored_cookie = sanitize_text_field($_COOKIE[ 'arm_cookie_' . $user_id ]); //phpcs:ignore
				$inserted_id   = explode( '||', $stored_cookie );
				$session_id    = $inserted_id[0];
				$wp_insert_id  = $inserted_id[1];
				$logged_out_time = current_time( 'mysql' );

				if ( $arm_is_change_password_form_for_logout == 1 ) {

					$arm_is_change_password_form_for_logout = 0;
					$update_result                          = $wpdb->query( $wpdb->prepare( "UPDATE `{$login_history_table}` SET `arm_logout_date` = %s, `arm_user_current_status` = %d WHERE `arm_history_id` != %d AND  `arm_user_id` = %d AND `arm_user_current_status` = %d", $logged_out_time, 0, $wp_insert_id, $user_id, 1 ) );//phpcs:ignore --Reason: $login_history_table is a table name. False Positive Alarm

					return;
				}

				if ( $arm_is_update_password_form_edit_profile_logout == 1 ) {
					$arm_is_update_password_form_edit_profile_logout = 0;
					$update_result                                   = $wpdb->query( $wpdb->prepare( "UPDATE `{$login_history_table}` SET `arm_logout_date` = %s, `arm_user_current_status` = %d WHERE `arm_history_id` != %d AND  `arm_user_id` = %d AND `arm_user_current_status` = %d", $logged_out_time, 0, $wp_insert_id, $user_id, 1 ) );//phpcs:ignore --Reason: $login_history_table is a table name. False Positive Alarm
					return;
				}

				$get_login_time = $wpdb->get_row( $wpdb->prepare( "SELECT `arm_logged_in_date` FROM `{$login_history_table}` WHERE `arm_history_id` = %d AND `arm_user_id` = %d AND `arm_history_session` = %s ", $wp_insert_id, $user_id, $session_id ) );//phpcs:ignore --Reason: $login_history_table is a table name. False Positive Alarm
				if ( ! empty( $get_login_time ) ) {
					$arm_login_time     = $get_login_time->arm_logged_in_date;
					$login_duration     = strtotime( $logged_out_time ) - strtotime( $arm_login_time );
					$arm_login_duration = date( 'H:i:s', $login_duration ); //phpcs:ignore
					
					$wpdb->query( $wpdb->prepare( "UPDATE `{$login_history_table}` SET `arm_logout_date` = %s, `arm_login_duration` = %s, `arm_user_current_status` = %d WHERE `arm_history_id` = %d AND `arm_history_session` = %s AND `arm_user_id` = %d", $logged_out_time, $arm_login_duration, 0, $wp_insert_id, $session_id, $user_id ) );//phpcs:ignore --Reason: $login_history_table is a table name. False Positive Alarm
				}
				unset( $_COOKIE[ 'arm_cookie_' . $user_id ] );
				update_user_meta( $user_id, 'arm_autolock_cookie', '' );

			}
		}

		function arm_reinit_session_initialization( $form_key ) {
			global $ARMemberLite;
			$ARMemberLite->arm_session_start();
			$possible_letters = '23456789bcdfghjkmnpqrstvwxyz';
			$random_dots      = 0;
			$random_lines     = 20;

			$session_var = '';
			$i           = 0;
			while ( $i < 8 ) {
				$session_var .= substr( $possible_letters, wp_rand( 0, strlen( $possible_letters ) - 1 ), 1 );
				$i++;
			}
			return $session_var;
		}

		function arm_reinit_session_filter_var() {
			global $ARMemberLite;

			$ARMemberLite->arm_session_start();

			$nonce = wp_create_nonce( 'arm_wp_nonce' );
			// $ARMemberLite->arm_check_user_cap('',1);//phpcs:ignore --Reason:Verifying nonce
			$form_key    = sanitize_text_field( $_POST['form_key'] ); //phpcs:ignore
			if ( ! empty( $form_key ) ) {
				$allowed = false;
				$allowed = apply_filters('arm_modify_file_validation_external', $allowed);

				if( ( !empty( $_SESSION['ARM_FILTER_INPUT'] ) && !empty( $_SESSION['ARM_FILTER_INPUT'][ $form_key ] ) ) || $allowed )
				{
					$session_var = $this->arm_reinit_session_initialization( $form_key );
					$_SESSION['ARM_FILTER_INPUT'][ $form_key ] = $session_var;
					echo wp_json_encode( array( 'new_var' => $session_var, 'nonce'=>$nonce ) );
				}
			}
			die();
		}

		function arm_reinit_session_filter_var_multiple_form() {
			global $ARMemberLite;
			
			$ARMemberLite->arm_session_start();
			
			$session_arr  = array();
			$nonce = wp_create_nonce( 'arm_wp_nonce' );
			// $ARMemberLite->arm_check_user_cap('',1);//phpcs:ignore --Reason:Verifying nonce
			$form_key_arr = sanitize_text_field($_POST['form_key_arr']); //phpcs:ignore
			$session_arm_filter_input_form_key_flag = 0;
			if ( ! empty( $form_key_arr ) ) {
				$form_key_arr_exp = explode( ',', $form_key_arr );

				$allowed = false;
				$allowed = apply_filters('arm_modify_file_validation_external', $allowed);
				foreach ( $form_key_arr_exp as $form_key ) {
					$form_key                 = sanitize_text_field( $form_key );
					if( ( !empty( $_SESSION['ARM_FILTER_INPUT'] ) && !empty( $_SESSION['ARM_FILTER_INPUT'][ $form_key ] ) ) || $allowed )
					{
						$session_arr[ $form_key ] = $this->arm_reinit_session_initialization( $form_key );
						$_SESSION['ARM_FILTER_INPUT'][ $form_key ] = $session_arr[ $form_key ];
						$session_arm_filter_input_form_key_flag = 1;
					}
				}
			}
			if( !empty($session_arm_filter_input_form_key_flag) )
			{
				$session_arr['nonce'] = $nonce;
				echo wp_json_encode( $session_arr );
			}
			die();
		}

		function arm_get_avatar_opt() {
			$avatarOptions = array(
				'id'              => 'avatar',
				'label'           => esc_html__( 'Avatar', 'armember-membership' ),
				'placeholder'     => esc_html__( 'Drop file here or click to select.', 'armember-membership' ),
				'type'            => 'avatar',
				'value'           => '',
				'allow_ext'       => '',
				'file_size_limit' => '2',
				'meta_key'        => 'avatar', // phpcs:ignore
				'required'        => 0,
				'blank_message'   => esc_html__( 'Please select avatar.', 'armember-membership' ),
				'invalid_message' => esc_html__( 'Invalid image selected.', 'armember-membership' ),
			);
			$avatarOptions = apply_filters( 'arm_change_field_options', $avatarOptions );
			return $avatarOptions;
		}

		function arm_get_profile_cover_opt() {
			$profileCoverOptions = array(
				'id'              => 'profile_cover',
				'label'           => esc_html__( 'Profile Cover', 'armember-membership' ),
				'placeholder'     => esc_html__( 'Drop file here or click to select.', 'armember-membership' ),
				'type'            => 'avatar',
				'value'           => '',
				'allow_ext'       => '',
				'file_size_limit' => '10',
				'meta_key'        => 'profile_cover', // phpcs:ignore
				'required'        => 0,
				'blank_message'   => esc_html__( 'Please select profile cover.', 'armember-membership' ),
				'invalid_message' => esc_html__( 'Invalid image selected.', 'armember-membership' ),
			);
			$profileCoverOptions = apply_filters( 'arm_change_field_options', $profileCoverOptions );
			return $profileCoverOptions;
		}

		function arm_get_all_form_fields() {
			global $arm_member_forms;
			$arm_form_fields                  = array();
			$arm_form_fields                  = $arm_member_forms->arm_get_db_form_fields( true );
			$arm_form_fields['avatar']        = $this->arm_get_avatar_opt();
			$arm_form_fields['profile_cover'] = $this->arm_get_profile_cover_opt();
			$arm_form_fields['social_fields'] = $arm_member_forms->arm_social_profile_field_types();
			return $arm_form_fields;
		}

		function armember_update_user_data( $null, $user_id, $meta_key, $single ) {
			if ( 'country' == $meta_key ) {
				$country_meta_value = '';
				global $wpdb;
				$meta_value = $wpdb->get_var( $wpdb->prepare("SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = %s",$user_id,'country') );//phpcs:ignore --Reason $wpdb->usermeta is a table name. False Positive alarm
				if ( empty( $meta_value ) ) {
					return null;
				}
				$old_meta_value = $meta_value;

				$presetFormFields = get_option( 'arm_preset_form_fields', '' );
				$dbFormFields     = maybe_unserialize( $presetFormFields );
				if ( ! empty( $dbFormFields ) && isset( $dbFormFields['default']['country'] ) ) {
					$preset_country = $dbFormFields['default']['country']['options'];
					if ( ! empty( $preset_country ) ) {
						foreach ( $preset_country as $preset_country_index => $preset_country_value ) {
							$country     = explode( ':', $preset_country_value );
							$country_val = isset( $country[1] ) ? $country[1] : $country[0];
							if ( $old_meta_value == $country_val && ! empty( $country_val ) ) {

								$country_meta_value = $country[0];
								break;
							}
						}
					}
				}
				if ( empty( $country_meta_value ) ) {
					$countries = $this->arm_get_countries();
					if ( isset( $countries[ $meta_value ] ) && ! empty( $countries[ $meta_value ] ) ) {
						$country_meta_value = $countries[ $meta_value ];
					}
				}
				return ! empty( $country_meta_value ) ? array( $country_meta_value ) : array( $meta_value );
			}
			return null;
		}

	}

}
global $arm_member_forms, $arm_form_popup_ids_arr, $arm_login_form_popup_ids_arr;
$arm_member_forms             = new ARM_member_forms_Lite();
$arm_form_popup_ids_arr       = array();
$arm_login_form_popup_ids_arr = array();

if ( ! class_exists( 'ARM_Form_Lite' ) ) {

	class ARM_Form_Lite {

		var $ID;
		var $name;
		var $slug;
		var $type;
		var $default;
		var $set_id;
		var $updated;
		var $created;
		var $settings;
		var $fields;
		var $form_detail;
		var $ref_form_id;
		var $template;

		public function __construct( $field = '', $value = '' ) {
			global $wp, $wpdb, $ARMemberLite;
			$form_info = array();
			switch ( $field ) {
				case 'id':
				case 'form_id':
				case 'arm_form_id':
					$key = 'arm_form_id';
					break;
				case 'slug':
				case 'arm_form_slug':
					$key = 'arm_form_slug';
					break;
				case 'type':
				case 'arm_form_type':
					$key = 'arm_form_type';
					break;
				default:
					$key = '';
					break;
			}
			if ( ! empty( $key ) && $value != '' ) {
				$form_info = $this->get_form_by( $key, $value );
				if ( ! empty( $form_info ) ) {
					$this->init( $form_info );
				}
			}
		}

		public function init( $data ) {
			$this->ID          = $data->arm_form_id;
			$this->name        = stripslashes( $data->arm_form_title );
			$this->slug        = $data->arm_form_slug;
			$this->type        = $data->arm_form_type;
			$this->ref_form_id = $data->arm_ref_template;
			$login_regex       = '/template-login(.*?)/';
			$register_regex    = '/template-registration(.*?)/';
			$forgot_regex      = '/template-forgot-password(.*?)/';
			$changepass_regex  = '/template-change-password(.*?)/';
			preg_match( $login_regex, $this->slug, $match_login );
			preg_match( $register_regex, $this->slug, $match_register );
			preg_match( $forgot_regex, $this->slug, $match_forgot );
			preg_match( $changepass_regex, $this->slug, $match_changepass );
			if ( isset( $match_login[0] ) && ! empty( $match_login[0] ) ) {
				$this->type = 'login';
			} elseif ( isset( $match_register[0] ) && is_array( $match_register[0] ) && ! empty( $match_register[0] ) ) {
				$this->type = 'registration';
			} elseif ( isset( $match_forgot[0] ) && is_array( $match_forgot[0] ) && ! empty( $match_forgot[0] ) ) {
				$this->type = 'forgot_password';
			} elseif ( isset( $match_changepass[0] ) && is_array( $match_changepass[0] ) && ! empty( $match_changepass[0] ) ) {
				$this->type = 'change_password';
			}
			$this->default     = ( $data->arm_is_default == '1' ) ? true : false;
			$this->set_id      = $data->arm_set_id;
			$this->updated     = $data->arm_form_updated_date;
			$this->created     = $data->arm_form_created_date;
			$this->settings    = maybe_unserialize( $data->arm_form_settings );
			$this->fields      = $data->fields;
			$this->form_detail = (array) $data;
			$this->template    = ( $data->arm_is_template == '1' ) ? true : false;
		}

		public function get_form_by( $field, $value ) {
			global $wp, $wpdb, $ARMemberLite;

			/* Query Monitor Change */
			if ( isset( $GLOBALS['arm_forms'] ) && isset( $GLOBALS['arm_forms'][ $value ] ) ) {
				$form_data = $GLOBALS['arm_forms'][ $value ];
			} else {
				$form_data = $wpdb->get_row( $wpdb->prepare('SELECT * FROM `' . $ARMemberLite->tbl_arm_forms . "` WHERE `$field`=%s LIMIT 1",$value) );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_forms is a table name. False Positive Alarm
				$GLOBALS['arm_forms']           = array();
				$GLOBALS['arm_forms'][ $value ] = $form_data;
			}
			if ( ! empty( $form_data ) ) {
				$form_data->arm_form_settings = ( ! empty( $form_data->arm_form_settings ) ) ? maybe_unserialize( $form_data->arm_form_settings ) : array();
				/* Get Form Fields */
				$form_data->fields = self::get_form_fields( $form_data->arm_form_id );
			}
			return $form_data;
		}

		function get_form_fields( $form_id = 0 ) {
			global $wp, $wpdb, $ARMemberLite;
			$fields = array();
			if ( ! empty( $form_id ) && $form_id != 0 ) {

				/* Query Monitor Change */
				if ( isset( $GLOBALS['arm_form_fields'] ) && isset( $GLOBALS['arm_form_fields'][ $form_id ] ) ) {
					$field_result = $GLOBALS['arm_form_fields'][ $form_id ];
				} else {
					$field_result = $wpdb->get_results( $wpdb->prepare('SELECT * FROM `' . $ARMemberLite->tbl_arm_form_field . "` WHERE `arm_form_field_form_id`=%d AND `arm_form_field_status` != %d ORDER BY `arm_form_field_order` ASC",$form_id,2), ARRAY_A );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_form_field is a table name. False Positive alarm
					$GLOBALS['arm_form_fields']             = array();
					$GLOBALS['arm_form_fields'][ $form_id ] = $field_result;
				}
				$i = 1;
				foreach ( $field_result as $field ) {
					$field['arm_form_field_option'] = maybe_unserialize( $field['arm_form_field_option'] );
					$fields[ $i ]                   = $field;
					$i++;
				}
			}
			return $fields;
		}

		public function exists() {
			return ! empty( $this->ID );
		}

		public function arm_is_form_exists( $form_id ) {
			global $wpdb, $ARMemberLite;
			$table = $ARMemberLite->tbl_arm_forms;
			if ( $form_id == '' || $form_id == 0 ) {
				return false;
			}
			$result = $wpdb->get_results( $wpdb->prepare( 'SELECT COUNT(*) as total FROM `' . $table . '` WHERE arm_form_id = %d', $form_id ) );//phpcs:ignore --Reason: $table is a table name. False Positive Alarm.
			if ( $result[0]->total > 0 ) {
				return true;
			} else {
				return false;
			}
		}

	}

}
