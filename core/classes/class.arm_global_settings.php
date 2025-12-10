<?php 
if ( ! class_exists( 'ARM_global_settings_Lite' ) ) {

	class ARM_global_settings_Lite {

		private $s;
		private $sub_folder;
		private $is_subdir_mu;
		private $blog_path;
		var $global_settings;
		var $block_settings;
		var $common_message;
		var $profile_url;

		function __construct() {
			global $wpdb, $ARMemberLite, $arm_slugs;
			/* ====================================/.Begin Set Global Settings For Class./==================================== */
			$this->global_settings = $this->arm_get_all_global_settings( true );
			//$this->block_settings  = $this->arm_get_parsed_block_settings();
			//$this->common_message  = $this->arm_get_all_common_message_settings();

			$sub_installation = trim( str_replace( ARMLITE_HOME_URL, '', site_url() ), ' /' );
			if ( $sub_installation && substr( $sub_installation, 0, 4 ) != 'http' ) {
				$this->sub_folder = $sub_installation . '/';
			}
			$this->is_subdir_mu = false;
			if ( is_multisite() ) {
				$this->is_subdir_mu = true;
				if ( ( defined( 'SUBDOMAIN_INSTALL' ) && SUBDOMAIN_INSTALL ) || ( defined( 'VHOST' ) && VHOST == 'yes' ) ) {
					$this->is_subdir_mu = false;
				}
			}
			if ( is_multisite() && ! $this->sub_folder && $this->is_subdir_mu ) {
				$this->sub_folder = ltrim( wp_parse_url( trim( get_blog_option( BLOG_ID_CURRENT_SITE, 'home' ), '/' ) . '/', PHP_URL_PATH ), '/' );
			}
			if ( is_multisite() && ! $this->blog_path && $this->is_subdir_mu ) {
				global $current_blog;
				$this->blog_path = str_replace( $this->sub_folder, '', $current_blog->path );
			}
			/* ====================================/.End Set Global Settings For Class./==================================== */
			add_action( 'wp_ajax_arm_send_test_mail', array( $this, 'arm_send_test_mail' ) );
			add_action( 'wp_ajax_arm_update_global_settings', array( $this, 'arm_update_all_settings' ) );
			add_action( 'wp_ajax_arm_update_block_settings', array( $this, 'arm_update_all_settings' ) );
			add_action( 'wp_ajax_arm_update_redirect_settings', array( $this, 'arm_update_all_settings' ) );
			add_action( 'wp_ajax_arm_page_settings', array( $this, 'arm_update_all_settings' ) );
			add_action( 'wp_ajax_arm_update_common_message_settings', array( $this, 'arm_update_all_settings' ) );

			add_action( 'wp_ajax_arm_update_access_restriction_settings', array( $this, 'arm_update_all_settings' ) );

			add_action( 'wp_ajax_arm_shortcode_exist_in_page', array( $this, 'arm_shortcode_exist_in_page' ) );
			add_action( 'wp_ajax_arm_update_feature_settings', array( $this, 'arm_update_feature_settings' ) );

			/* Apply Global Setting Action */
			add_action( 'init', array( $this, 'arm_apply_global_settings' ), 200 );

			add_action( 'login_head', array( $this, 'arm_login_enqueue_assets' ), 50 );
			add_filter( 'option_users_can_register', array( $this, 'arm_remove_registration_link' ) );
			/* Enable Shortcodes in Widgets */
			add_filter( 'widget_text', 'do_shortcode' );
			/* Filter Post Excerpt for plugin shortcodes */
			add_filter( 'the_excerpt', array( $this, 'arm_filter_the_excerpt' ) );
			add_filter( 'the_excerpt_rss', array( $this, 'arm_filter_the_excerpt' ) );

			/* Rewrite Rules */
			add_action( 'admin_notices', array( $this, 'arm_admin_notices' ) );

			add_filter( 'arm_display_admin_notices', array( $this, 'arm_global_settings_notices' ) );
			/* Filter `get_avatar` */
			add_filter( 'get_avatar', array( $this, 'arm_filter_get_avatar' ), 20, 5 );
			/* Filter `get_avatar_url` */
			add_filter( 'get_avatar_url', array( $this, 'arm_filter_get_avatar_url' ), 20, 3 );
			add_filter( 'arm_check_member_status_before_login', array( $this, 'arm_check_member_status' ), 10, 2 );
			/*
			 add_filter('arm_check_member_status_before_login', array($this, 'arm_check_block_settings'), 5, 2); */
			/* Delete Term Action Hook */
			add_action( 'delete_term', array( $this, 'arm_after_delete_term' ), 10, 4 );
			/* Added From Name And Form Email Hook */
			add_action( 'admin_enqueue_scripts', array( $this, 'arm_add_page_label_css' ), 20 );
			add_filter( 'display_post_states', array( $this, 'arm_add_set_page_label' ), 999, 2 );

			/* Set Global Profile URL */
			add_filter( 'query_vars', array( $this, 'arm_user_query_vars' ), 10, 1 );
			add_action( 'wp_ajax_arm_clear_form_fields', array( $this, 'arm_clear_form_fields' ) );
			add_action( 'wp_ajax_arm_failed_login_lockdown_clear', array( $this, 'arm_failed_login_lockdown_clear' ) );

			add_action( 'after_switch_theme', array( $this, 'arm_set_permalink_for_profile_page' ), 10 );
			add_action( 'permalink_structure_changed', array( $this, 'arm_set_session_for_permalink' ) );
			add_action( 'admin_footer', array( $this, 'arm_rewrite_rules_for_profile_page' ), 100 );

			add_filter( 'generate_rewrite_rules', array( $this, 'arm_generate_rewrite_rules' ), 10 );

			add_action( 'admin_init', array( $this, 'arm_plugin_add_suggested_privacy_content' ), 20 );
			add_action( 'login_init', array( $this, 'arm_add_jquery_for_login' ), 1 );
		}

		function arm_plugin_add_suggested_privacy_content() {
			if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
				$content = $this->arm_get_privacy_content();
				wp_add_privacy_policy_content( 'armember-membership', $content );
			}
		}

		function arm_add_jquery_for_login() {
			global $arm_global_settings;
			wp_enqueue_script( 'jquery' );
			add_filter( 'gettext', array( $arm_global_settings, 'remove_loginpage_label_text' ), 50 );
		}
		function arm_get_privacy_content() {
			$arm_gdpr_mode_cnt_default = '<h2>' . esc_html__( 'What personal data collected in ARMember', 'armember-membership' ) . '</h2>'
							. '<p>' . esc_html__( 'User\'s Signup Details such as Username, Password, First Name, Last Name and Custom Fields value( Address, Gender etc)', 'armember-membership' ) . '</p>'
							. '<p>' . esc_html__( 'User\'s IP Address Information', 'armember-membership' ) . '</p>'
							. '<p>' . esc_html__( 'User\'s Basic Details Sending to opt-ins such as (Email, First Name, Last Name)', 'armember-membership' ) . '</p>'
							. '<p>' . esc_html__( 'User\'s Logged in / Logout details', 'armember-membership' ) . '</p>'
							. '<p>' . esc_html__( 'User\'s Basic Payment Transaction Details (Not Storing any sensitive Payment Data such as Credit/Debit Card Details.)', 'armember-membership' ) . '</p>';

			return $arm_gdpr_mode_cnt_default;
		}

		function arm_set_permalink_for_profile_page() {
			$this->arm_user_rewrite_rules();
		}
		function arm_set_session_for_permalink() {
			global $ARMemberLite;
			$ARMemberLite->arm_session_start();
			$_SESSION['arm_site_permalink_is_changed'] = true;
		}
		function arm_rewrite_rules_for_profile_page() {
			global $wp_rewrite, $ARMemberLite;
			$ARMemberLite->arm_session_start();
			if ( isset( $_SESSION['arm_site_permalink_is_changed'] ) && $_SESSION['arm_site_permalink_is_changed'] == true ) {
				$this->arm_user_rewrite_rules();
				$wp_rewrite->flush_rules( false );
				unset( $_SESSION['arm_site_permalink_is_changed'] );
			}
		}

		function arm_failed_login_lockdown_clear() {
			global $wpdb, $ARMemberLite, $arm_capabilities_global;

			if ( isset( $_POST['reset_attempts_users'] ) && ! empty( $_POST['reset_attempts_users'] ) ) { //phpcs:ignore
				
				$arm_reset_attempts_users = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST['reset_attempts_users'] ); //phpcs:ignore

				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_block_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce

				if ( in_array( 'all', $arm_reset_attempts_users ) ) {

					$delete = $wpdb->query( $wpdb->prepare("DELETE FROM `$ARMemberLite->tbl_arm_fail_attempts`") );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_fail_attempts is table name. False Positive Alarm
					$delete = $wpdb->query( $wpdb->prepare( "DELETE FROM `$ARMemberLite->tbl_arm_lockdown`" ));//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_fail_attempts is table name. False Positive Alarm
				} else {
					
					foreach ( $arm_reset_attempts_users as $user_id ) {
						$wpdb->delete( $ARMemberLite->tbl_arm_fail_attempts, array( 'arm_user_id' => $user_id ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						$wpdb->delete( $ARMemberLite->tbl_arm_lockdown, array( 'arm_user_id' => $user_id ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					}
				}
			}
			die();
		}

		function arm_clear_form_fields() {
			global $wpdb, $ARMemberLite, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce

			$arm_posted_data = isset( $_POST['clear_fields'] ) ? array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST['clear_fields'] ) : array(); //phpcs:ignore

			$arm_deleted_fields = array();
			$presetFormFields   = get_option( 'arm_preset_form_fields', '' );
			$dbFormFields       = maybe_unserialize( $presetFormFields );

			if ( isset( $arm_posted_data ) && ! empty( $arm_posted_data ) ) {
				foreach ( $arm_posted_data as $key => $arm_field_key ) {
					$wpdb->query( $wpdb->prepare('DELETE FROM `' . $wpdb->usermeta . "` WHERE  `meta_key`=%s",$key ) );//phpcs:ignore --Reason: $wpdb->usermeta is a table name . False Positive Alarm
					unset( $dbFormFields['other'][ $key ] );
					array_push( $arm_deleted_fields, $key );
				}
			}
			update_option( 'arm_preset_form_fields', $dbFormFields );
			echo arm_pattern_json_encode( $arm_deleted_fields );
			die();
		}

		function arm_send_test_mail() {
			global $ARMemberLite, $arm_capabilities_global ,$arm_ajax_pattern_start,$arm_ajax_pattern_end;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce

			$reply_to      = ( isset( $_POST['reply_to'] ) && ! empty( $_POST['reply_to'] ) ) ? sanitize_email( $_POST['reply_to'] ) : ''; //phpcs:ignore
			$send_to       = ( isset( $_POST['send_to'] ) && ! empty( $_POST['send_to'] ) ) ? sanitize_email( $_POST['send_to'] ) : ''; //phpcs:ignore
			$subject       = ( isset( $_POST['subject'] ) && ! empty( $_POST['subject'] ) ) ? sanitize_text_field( $_POST['subject'] ) : esc_html__( 'SMTP Test E-Mail', 'armember-membership' ); //phpcs:ignore
			$message       = ( isset( $_POST['message'] ) && ! empty( $_POST['message'] ) ) ? sanitize_textarea_field( $_POST['message'] ) : ''; //phpcs:ignore
			$reply_to_name = ( isset( $_POST['reply_to_name'] ) && ! empty( $_POST['reply_to_name'] ) ) ? sanitize_text_field( $_POST['reply_to_name'] ) : ''; //phpcs:ignore

			$mail_authentication = ( isset( $_POST['mail_authentication'] ) ) ? intval( $_POST['mail_authentication'] ) : '1'; //phpcs:ignore
			$arm_mail_server     = ( isset( $_POST['mail_server'] ) && ! empty( $_POST['mail_server'] ) ) ? sanitize_text_field( $_POST['mail_server'] ) : ''; //phpcs:ignore
			$arm_mail_port       = ( isset( $_POST['mail_port'] ) && ! empty( $_POST['mail_port'] ) ) ? intval( $_POST['mail_port'] ) : ''; //phpcs:ignore
			$arm_mail_login_name = ( isset( $_POST['mail_login_name'] ) && ! empty( $_POST['mail_login_name'] ) ) ? sanitize_text_field( $_POST['mail_login_name'] ) : ''; //phpcs:ignore
			$arm_mail_password   = ( isset( $_POST['mail_password'] ) && ! empty( $_POST['mail_password'] ) ) ? $_POST['mail_password'] : ''; //phpcs:ignore
			$arm_mail_enc        = ( isset( $_POST['mail_enc'] ) && ! empty( $_POST['mail_enc'] ) ) ? sanitize_text_field( $_POST['mail_enc'] ) : ''; //phpcs:ignore

			if ( empty( $send_to ) || empty( $reply_to ) || empty( $message ) || empty( $subject ) ) {
				return;
			}
                	echo $arm_ajax_pattern_start;
			echo $this->arm_send_tedst_mail_func( $reply_to, $send_to, $subject, $message, array(), $reply_to_name, $arm_mail_server, $arm_mail_port, $arm_mail_login_name, $arm_mail_password, $arm_mail_enc, $mail_authentication ); //phpcs:ignore
                	echo $arm_ajax_pattern_end;
			die();
		}

		public function arm_send_tedst_mail_func( $from, $recipient, $subject, $message, $attachments = array(), $reply_to_name = '', $arm_mail_server = '', $arm_mail_port = '', $arm_mail_login_name = '', $arm_mail_password = '', $arm_mail_enc = '', $mail_authentication = '1' ) {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs, $arm_email_settings, $arm_plain_text, $wp_version;
			$return                 = false;
			$reply_to_name          = ( $reply_to_name == '' ) ? esc_attr( wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) ) : esc_attr( $reply_to_name );
			$use_only_smtp_settings = false;
			$emailSettings          = $arm_email_settings->arm_get_all_email_settings();
			$email_server           = 'smtp_server';
			$reply_to_name          = ( $reply_to_name == '' ) ? wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) : $reply_to_name;
			$reply_to               = ( $from == '' or $from == '[admin_email]' ) ? esc_attr( get_option( 'admin_email' ) ) : esc_attr( $from );
			$from_name              = ( ! empty( $emailSettings['arm_email_from_name'] ) ) ? esc_attr( $emailSettings['arm_email_from_name'] ) : wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			$from_email             = ( ! empty( $emailSettings['arm_email_from_email'] ) ) ? esc_attr( $emailSettings['arm_email_from_email'] ) : get_option( 'admin_email' );
			$content_type           = ( @$arm_plain_text ) ? 'text/plain' : 'text/html';
			$from_name              = $from_name;
			$reply_to               = ( ! empty( $from ) ) ? esc_attr( $from ) : esc_attr( $from_email );
			/* Set Email Headers */
			$headers   = array();
			$header[]  = 'From: "' . esc_attr( $reply_to_name ) . '" <' . esc_attr( $reply_to ) . '>';
			$header[]  = 'Reply-To: ' . esc_attr( $reply_to );
			$headers[] = 'Content-Type: ' . esc_attr( $content_type ) . '; charset="' . esc_attr( get_option( 'blog_charset' ) ) . '"';
			/* Filter Email Subject & Message */
			$subject = wp_specialchars_decode( wp_strip_all_tags( stripslashes( $subject ) ), ENT_QUOTES );
			$message = do_shortcode( $message );
			$message = wordwrap( stripslashes( $message ), 70, "\r\n" );
			if ( @$arm_plain_text ) {
				$message = wp_specialchars_decode( wp_strip_all_tags( $message ), ENT_QUOTES );
			}

			$subject   = apply_filters( 'arm_email_subject', $subject );
			$message   = apply_filters( 'arm_change_email_content', $message );
			$recipient = apply_filters( 'arm_email_recipients', $recipient );
			$headers   = apply_filters( 'arm_email_header', $headers, $recipient, $subject );
			remove_filter( 'wp_mail_from', 'bp_core_email_from_address_filter' );
			remove_filter( 'wp_mail_from_name', 'bp_core_email_from_name_filter' );
			if ( version_compare( $wp_version, '5.5', '<' ) ) {
				require_once ABSPATH . WPINC . '/class-phpmailer.php';
				require_once ABSPATH . WPINC . '/class-smtp.php';
				$armPMailer = new PHPMailer();
			} else {
				require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
				require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
				require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
				$armPMailer = new PHPMailer\PHPMailer\PHPMailer();
			}
			do_action( 'arm_before_send_email_notification', $from, $recipient, $subject, $message, $attachments );
			/* Character Set of the message. */
			$armPMailer->CharSet   = 'UTF-8';
			$armPMailer->SMTPDebug = 0;
			/* $armPMailer->Debugoutput = 'html'; */

			if ( $email_server == 'smtp_server' ) {
				$armPMailer->isSMTP();
				$armPMailer->Host     = isset( $arm_mail_server ) ? $arm_mail_server : '';
				$armPMailer->SMTPAuth = ( $mail_authentication == 1 ) ? true : false;
				$armPMailer->Username = isset( $arm_mail_login_name ) ? $arm_mail_login_name : '';
				$armPMailer->Password = isset( $arm_mail_password ) ? $arm_mail_password : '';
				if ( isset( $arm_mail_enc ) && ! empty( $arm_mail_enc ) && $arm_mail_enc != 'none' ) {
					$armPMailer->SMTPSecure = $arm_mail_enc;
				}
				if ( $arm_mail_enc == 'none' ) {
					$armPMailer->SMTPAutoTLS = false;
				}
				$armPMailer->Port = isset( $arm_mail_port ) ? $arm_mail_port : '';
			} else {
				$armPMailer->isMail();
			}

			$armPMailer->setFrom( $reply_to, $reply_to_name );
			$armPMailer->addReplyTo( $reply_to, $reply_to_name );
			$armPMailer->addAddress( $recipient );
			if ( isset( $attachments ) && ! empty( $attachments ) ) {
				foreach ( $attachments as $attachment ) {
					$armPMailer->addAttachment( $attachment );
				}
			}
			$armPMailer->isHTML( true );
			$armPMailer->Subject = $subject;
			$armPMailer->Body    = $message;
			if ( @$arm_plain_text ) {
				$armPMailer->AltBody = $message;
			}
			/* Send Email */
			if ( $email_server == 'smtp_server' || $email_server == 'phpmailer' ) {

				if ( ! $armPMailer->send() ) {

					echo wp_json_encode(
						array(
							'success' => 'false',
							'msg'     => $armPMailer->ErrorInfo,
						)
					);
				} else {
					echo wp_json_encode(
						array(
							'success' => 'true',
							'msg'     => '',
						)
					);
				}
			} else {
				if ( ! wp_mail( $recipient, $subject, $message, $header, $attachments ) ) {

					if ( ! $armPMailer->send() ) {

						return false;
					} else {

						return true;
					}
				} else {

					return true;
				}
			}
			do_action('arm_general_log_entry','email','send test email detail','armember', $message);
		}

		function arm_change_from_email( $from_email ) {
			global $arm_email_settings;
			$all_email_settings = $arm_email_settings->arm_get_all_email_settings();
			$from_email         = ( ! empty( $all_email_settings['arm_email_from_email'] ) ) ? $all_email_settings['arm_email_from_email'] : get_option( 'admin_email' );
			return $from_email;
		}

		function arm_change_from_name( $from_name ) {
			global $arm_email_settings;
			$all_email_settings = $arm_email_settings->arm_get_all_email_settings();
			$from_name          = ( ! empty( $all_email_settings['arm_email_from_name'] ) ) ? $all_email_settings['arm_email_from_name'] : get_option( 'blogname' );
			return $from_name;
		}





		function arm_admin_notices() {
			global $wp, $wpdb, $wp_rewrite, $arm_lite_errors, $arm_slugs, $ARMemberLite;
			/*             * ====================/.Begin Display Admin Notices./====================* */
			$current_cookie = str_replace( SITECOOKIEPATH, '', ADMIN_COOKIE_PATH );
			/* For non-sudomain and with paths mu: */
			if ( ! $current_cookie ) {
				$current_cookie = 'wp-admin';
			}

			if ( isset( $_GET['page'] ) ) { //phpcs:ignore
				 $_GET['page'] = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : ''; //phpcs:ignore
			}

			global $current_screen, $pagenow, $arm_access_rules;
			$default_rule_link = admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=access_rules_options' );

			if ( $current_screen->base == 'nav-menus' || $pagenow == 'nav-menus.php' ) {
				$default_access_rules = $arm_access_rules->arm_get_default_access_rules();
				$nav_rules            = ( isset( $default_access_rules['nav_menu'] ) ) ? $default_access_rules['nav_menu'] : '';
				if ( ! empty( $nav_rules ) ) {
					$warning_msg  = '<div class="error arm_admin_notices_container" style="color: #F00;"><p>';
					$warning_msg .= '<strong>' . esc_html__( 'ARMember Warning', 'armember-membership' ) . ':</strong> ';
					$warning_msg .= esc_html__( 'Please review', 'armember-membership' );
					$warning_msg .= ' <a href="' . $default_rule_link . '"><strong>' . esc_html__( 'Access Rules', 'armember-membership' ) . '</strong></a> ';
					$warning_msg .= esc_html__( 'after adding new menu items. Default access rule will be applied to new menu items.', 'armember-membership' );
					$warning_msg .= '</p></div>';
					echo $warning_msg; //phpcs:ignore
				}
			}
			if ( $current_screen->base == 'edit-tags' || $pagenow == 'edit-tags.php' ) { 
				if ( ! isset( $_REQUEST['tag_ID'] ) || empty( $_REQUEST['tag_ID'] ) ) { //phpcs:ignore
					$taxonomy             = $current_screen->taxonomy;
					$taxo_data            = get_taxonomy( $taxonomy );
					$default_access_rules = $arm_access_rules->arm_get_default_access_rules();
					if ( $taxo_data->name == 'category' ) {
						$taxo_rules       = ( isset( $default_access_rules['category'] ) ) ? $default_access_rules['category'] : '';
						$taxo_data->label = esc_html__( 'category(s)', 'armember-membership' );
					} else {
						$taxo_rules       = ( isset( $default_access_rules['taxonomy'] ) ) ? $default_access_rules['taxonomy'] : '';
						$taxo_data->label = esc_html__( 'custom taxonomy(s)', 'armember-membership' );
					}
					if ( ! empty( $taxo_rules ) ) {
						$warning_msg  = '<div class="error arm_admin_notices_container" style="color: #F00;"><p>';
						$warning_msg .= '<strong>' . esc_html__( 'ARMember Warning', 'armember-membership' ) . ':</strong> ';
						$warning_msg .= esc_html__( 'Please review', 'armember-membership' );
						$warning_msg .= ' <a href="' . $default_rule_link . '"><strong>' . esc_html__( 'Access Rules', 'armember-membership' ) . '</strong></a> ';
						$warning_msg .= esc_html__( 'after adding new', 'armember-membership' ) . ' ' . $taxo_data->label . '. ';
						$warning_msg .= esc_html__( 'Default access rule will be applied to new', 'armember-membership' ) . ' ' . $taxo_data->label . '. ';
						$warning_msg .= '</p></div>';
						echo $warning_msg; //phpcs:ignore
					}
				}
			}
			/*             * ====================/.End Display Admin Notices./====================* */
		}

		function is_permalink() {
			global $wp_rewrite;
			if ( ! isset( $wp_rewrite ) || ! is_object( $wp_rewrite ) || ! $wp_rewrite->using_permalinks() ) {
				return false;
			}
			return true;
		}

		function arm_mod_rewrite_rules( $rules ) {
			global $wp, $wpdb, $arm_lite_errors, $ARMemberLite;
			$home_root = wp_parse_url( ARMLITE_HOME_URL );
			if ( isset( $home_root['path'] ) ) {
				$home_root = trailingslashit( $home_root['path'] );
			} else {
				$home_root = '/';
			}
			$rules = str_replace( '(.*) ' . $home_root . '$1$2 ', '(.*) $1$2 ', $rules );
			return $rules;
		}




		function arm_apply_global_settings() {
			global $wp, $wpdb, $wp_rewrite, $arm_lite_errors, $current_user, $arm_slugs, $ARMemberLite, $arm_members_class, $arm_restriction, $arm_member_forms;
			$all_settings = $this->global_settings;

			$this->block_settings  = $this->arm_get_parsed_block_settings();
			$this->common_message  = $this->arm_get_all_common_message_settings();

			/* Hide admin bar for non-admin users. */
			$allow_access_admin_roles = array();
			$hide_admin_bar           = isset( $all_settings['hide_admin_bar'] ) ? $all_settings['hide_admin_bar'] : 0;
			if ( $hide_admin_bar == 1 ) {
				if ( isset( $all_settings['arm_exclude_role_for_hide_admin'] ) && is_array( $all_settings['arm_exclude_role_for_hide_admin'] ) ) {
					$allow_access_admin_roles = $all_settings['arm_exclude_role_for_hide_admin'];
				} else {

					$allow_access_admin_roles = ( isset( $all_settings['arm_exclude_role_for_hide_admin'] ) && ! empty( $all_settings['arm_exclude_role_for_hide_admin'] ) ) ? explode( ',', $all_settings['arm_exclude_role_for_hide_admin'] ) : array();
				}

				$user_match_role = array_intersect( $current_user->roles, $allow_access_admin_roles );
				if ( empty( $user_match_role ) ) {
					if ( ! is_admin() && ! current_user_can( 'administrator' ) ) {
						remove_all_filters('show_admin_bar');
						add_filter( 'show_admin_bar', '__return_false' );
					}
				}
			}/*
			End `($hide_admin_bar == 1)` */
			/* New User Verification */
			$user_register_verification = isset( $all_settings['user_register_verification'] ) ? sanitize_text_field($all_settings['user_register_verification']) : 'auto';
			if ( $user_register_verification != 'auto' ) {
				add_action( 'user_register', array( $arm_members_class, 'arm_add_member_activation_key' ) );
			}
			/* Verify Member Detail Before Login */
			if ( ! is_admin() ) {
				add_filter( 'authenticate', array( &$arm_members_class, 'arm_user_register_verification' ), 10, 3 );
			}
			/**
			 * Load Google Fonts for TinyMCE Editor
			 */
		}

		function arm_get_home_path() {
			$home    = get_option( 'home' );
			$siteurl = get_option( 'siteurl' );
			if ( ! empty( $home ) && 0 !== strcasecmp( $home, $siteurl ) ) {
				$wp_path_rel_to_home = str_ireplace( $home, '', $siteurl ); /* $siteurl - $home */
				$script_filename     = !empty( $_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';//phpcs:ignore
				$pos                 = strripos( str_replace( '\\', '/', sanitize_text_field( $script_filename ) ), trailingslashit( $wp_path_rel_to_home ) );
				$home_path           = substr( sanitize_text_field( $script_filename ), 0, $pos );
				$home_path           = trailingslashit( $home_path );
			} else {
				$home_path = ABSPATH;
			}
			return $home_path;
		}

		function arm_check_member_status( $return = true, $user_id = 0 ) {
			global $wp, $wpdb, $arm_lite_errors, $ARMemberLite, $arm_members_class, $arm_member_forms;
			if ( ! empty( $user_id ) && $user_id != 0 ) {
				if ( is_super_admin( $user_id ) ) {
					return true;
				}
				$primary_status   = arm_get_member_status( $user_id );
				$secondary_status = arm_get_member_status( $user_id, 'secondary' );
				switch ( $primary_status ) {
					case 'pending':
					case 3:
						$pending_msg = ( ! empty( $this->common_message['arm_account_pending'] ) ) ? $this->common_message['arm_account_pending'] : '<strong>' . esc_html__( 'Account Pending', 'armember-membership' ) . '</strong>: ' . esc_html__( 'Your account is currently not active. An administrator needs to activate your account before you can login.', 'armember-membership' );
						$return      = $arm_lite_errors;
						/* Remove other filters when there is an error */
						remove_all_filters( 'arm_check_member_status_before_login' );
						break;
					case 'inactive':
					case 2:
						if ( ( $primary_status == '2' && in_array( $secondary_status, array( 0, 1 ) ) ) || $primary_status == 4 ) {
							$err_msg = ( ! empty( $this->common_message['arm_account_inactive'] ) ) ? $this->common_message['arm_account_inactive'] : '<strong>' . esc_html__( 'Account Inactive', 'armember-membership' ) . '</strong>: ' . esc_html__( 'Your account is currently not active. Please contact the system administrator.', 'armember-membership' );
							$arm_lite_errors->add( 'access_denied', $err_msg );
						}
						$return = $arm_lite_errors;
							/* Remove other filters when there is an error */
							remove_all_filters( 'arm_check_member_status_before_login' );
						break;
					case 'active':
					case 1:
						$return = true;
						break;
					default:
						$return = true;
						break;
				}
			} else {
				$return = false;
			}
			return $return;
		}



		function arm_global_settings_notices( $notices = array() ) {
			global $wp, $wpdb, $arm_lite_errors, $ARMemberLite, $arm_slugs, $arm_social_feature;
			$default_global_settings = $this->arm_default_global_settings();
			$default_page_settings   = $default_global_settings['page_settings'];
			$page_settings           = $this->arm_get_single_global_settings( 'page_settings' );
			$final_page_settings     = shortcode_atts( $default_page_settings, $page_settings );
			if ( ! empty( $final_page_settings ) ) {
				$empty_pages = array();
				foreach ( $final_page_settings as $key => $page_id ) {
					if ( in_array( $key, array( 'logout_page_id', 'guest_page_id', 'thank_you_page_id', 'cancel_payment_page_id' ) ) ) {
						continue;
					}
					if ( $key == 'member_profile_page_id' && ! $arm_social_feature->isSocialFeature ) {
						continue;
					}
					if ( empty( $page_id ) || $page_id == 0 ) {
						$name          = str_replace( '_page_id', '', $key );
						$name          = str_replace( '_', ' ', $name );
						$name          = ucfirst( $name );
						$empty_pages[] = $name;
					}
				}
				if ( ! empty( $empty_pages ) ) {
					$empty_pages       = trim( implode( ', ', $empty_pages ), ', ' );
					$page_settings_url = admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=page_setup' );
					$notices[]         = array(
						'type'    => 'error',
						'message' => esc_html__( 'You need to set', 'armember-membership' ) . ' <b>\'' . esc_html($empty_pages) . '\'</b> ' . esc_html__( 'page(s) in', 'armember-membership' ) . ' <a href="' . esc_attr($page_settings_url) . '">' . esc_html__( 'page settings', 'armember-membership' ) . '</a>',
					);
				}
			}
			return $notices;
		}

		function arm_get_default_invoice_template() {

			$arm_default_invoice_template                                      = '<div id="arm_invoice_div" class="entry-content ms-invoice">';
			$arm_default_invoice_template                                     .= '<style>';
			$arm_default_invoice_template                                     .= '#arm_invoice_div table, th, td { margin: 0; font-size: 14px; }';
			$arm_default_invoice_template                                     .= '#arm_invoice_div table { padding: 0; border: 1px solid #DDD; width: 100%; background-color: #FFF; box-shadow: 0 1px 8px #F0F0F0; }';
			$arm_default_invoice_template                                     .= '#arm_invoice_div th, td { border: 0; padding: 8px; }';
			$arm_default_invoice_template                                     .= '#arm_invoice_div th { font-weight: bold; text-align: left; text-transform: none; font-size: 13px; }';
			$arm_default_invoice_template                                     .= '#arm_invoice_div tr.alt { background-color: #F9F9F9; }';
			$arm_default_invoice_template                                     .= '#arm_invoice_div tr.sep th, #arm_invoice_div tr.sep td { border-top: 1px solid #DDD; padding-top: 16px; }';
			$arm_default_invoice_template                                     .= '#arm_invoice_div tr.space th, #arm_invoice_div tr.space td { padding-bottom: 16px; }';
			$arm_default_invoice_template                                     .= '#arm_invoice_div tr.ms-inv-sep th,#arm_invoice_div tr.ms-inv-sep td { line-height: 1px; height: 1px; padding: 0; border-bottom: 1px solid #DDD; background-color: #F9F9F9; }';
			$arm_default_invoice_template                                     .= '#arm_invoice_div .ms-inv-total .ms-inv-price { font-weight: bold; font-size: 18px; text-align: right; }';
			$arm_default_invoice_template                                     .= '#arm_invoice_div h2 { text-align: right; padding: 0 10px 0 0; }';
			$arm_default_invoice_template                                     .= '#arm_invoice_div h2 a { color: #000; }';
			$arm_default_invoice_template                                     .= '</style>';
			$arm_default_invoice_template                                     .= '<div class="ms-invoice-details ms-status-paid">';
										$arm_default_invoice_template         .= '<table class="ms-purchase-table" cellspacing="0">';
											$arm_default_invoice_template     .= '<tbody>';
												$arm_default_invoice_template .= '<tr class="ms-inv-title">';
													$arm_default_invoice_template .= '<td colspan="2">';
													$arm_default_invoice_template .= '<h2>Invoice {ARM_INVOICE_INVOICEID}</h2>';
													$arm_default_invoice_template .= '<div style="text-align: right; padding: 0px 10px 10px 0px;">{ARM_INVOICE_PAYMENTDATE}</div>';
												$arm_default_invoice_template     .= '</td>';
												$arm_default_invoice_template     .= '</tr>';

												$arm_default_invoice_template     .= '<tr class="ms-inv-to alt space sep">';
													$arm_default_invoice_template .= '<th>Invoice to</th>';
													$arm_default_invoice_template .= '<td class="ms-inv-text">{ARM_INVOICE_USERFIRSTNAME} {ARM_INVOICE_USERLASTNAME} ( {ARM_INVOICE_PAYEREMAIL} )</td>';
												$arm_default_invoice_template     .= '</tr>';

												$arm_default_invoice_template     .= '<tr class="ms-inv-item-name space">';
													$arm_default_invoice_template .= '<th>Plan Name</th>';
													$arm_default_invoice_template .= '<td class="ms-inv-text">{ARM_INVOICE_SUBSCRIPTIONNAME}</td>';
												$arm_default_invoice_template     .= '</tr>';

												$arm_default_invoice_template     .= '<tr class="ms-inv-description alt space">';
													$arm_default_invoice_template .= '<th>Description</th>';
													$arm_default_invoice_template .= '<td class="ms-inv-text">{ARM_INVOICE_SUBSCRIPTIONDESCRIPTION}</td>';
												$arm_default_invoice_template     .= '</tr>';

												$arm_default_invoice_template     .= '<tr class="ms-inv-amount space">';
													$arm_default_invoice_template .= '<th>Plan Amount</th>';
													$arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_AMOUNT}</td>';
												$arm_default_invoice_template     .= '</tr>';

												$arm_default_invoice_template     .= '<tr class="ms-inv-amount alt space">';
													$arm_default_invoice_template .= '<th>transaction Id</th>';
													$arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_TRANSACTIONID}</td>';
												$arm_default_invoice_template     .= '</tr>';

												$arm_default_invoice_template     .= '<tr class="ms-inv-amount space">';
													$arm_default_invoice_template .= '<th>subscription id</th>';
													$arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_SUBSCRIPTIONID}</td>';
												$arm_default_invoice_template     .= '</tr>';

												$arm_default_invoice_template     .= '<tr class="ms-inv-amount space alt">';
													$arm_default_invoice_template .= '<th>payment gateway</th>';
													$arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_GATEWAY}</td>';
												$arm_default_invoice_template     .= '</tr>';

												$arm_default_invoice_template     .= '<tr class="ms-inv-amount space">';
													$arm_default_invoice_template .= '<th>trial amount</th>';
													$arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_TRIALAMOUNT}</td>';
												$arm_default_invoice_template     .= '</tr>';

												$arm_default_invoice_template     .= '<tr class="ms-inv-amount space alt">';
													$arm_default_invoice_template .= '<th>trial period</th>';
													$arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_TRIALPERIOD}</td>';
												$arm_default_invoice_template     .= '</tr>';

												$arm_default_invoice_template     .= '<tr class="ms-inv-amount space">';
													$arm_default_invoice_template .= '<th>coupon code</th>';
													$arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_COUPONCODE}</td>';
												$arm_default_invoice_template     .= '</tr>';

												$arm_default_invoice_template     .= '<tr class="ms-inv-amount alt space">';
													$arm_default_invoice_template .= '<th>coupon discount</th>';
													$arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_COUPONAMOUNT}</td>';
												$arm_default_invoice_template     .= '</tr>';
												$arm_default_invoice_template     .= '<tr class="ms-inv-amount alt space">';
													$arm_default_invoice_template .= '<th>Tax Percentage</th>';
													$arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_TAXPERCENTAGE}</td>';
												$arm_default_invoice_template     .= '</tr>';
												$arm_default_invoice_template     .= '<tr class="ms-inv-amount alt space">';
													$arm_default_invoice_template .= '<th>Tax Amount</th>';
													$arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_TAXAMOUNT}</td>';
												$arm_default_invoice_template     .= '</tr>';

												$arm_default_invoice_template .= '</tbody>';
											$arm_default_invoice_template     .= '</table>';
									   $arm_default_invoice_template          .= '</div>';
									$arm_default_invoice_template             .= '</div>';
									return $arm_default_invoice_template;
		}
		function arm_default_global_settings() {
			global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms;
			$default_global_settings = array();
			/* General Settings */
			$arm_default_invoice_template = $this->arm_get_default_invoice_template();

			$default_global_settings['general_settings'] = array(
				'hide_admin_bar'                      => 0,
				'arm_exclude_role_for_hide_admin'     => 0,
				'restrict_admin_panel'                => 0,
				'arm_exclude_role_for_restrict_admin' => 0,
				'hide_wp_login'                       => 0,
				'rename_wp_admin'                     => 0,
				'temp_wp_admin_path'                  => '',
				'new_wp_admin_path'                   => 'wp-admin',
				'hide_register_link'                  => 0,
				'user_register_verification'          => 'auto',
				'arm_new_signup_status'               => 1,
				'hide_feed'                           => 0,
				'disable_wp_login_style'              => 0,
				'restrict_site_access'                => 0,
				'arm_access_page_for_restrict_site'   => 0,
				'autolock_shared_account'             => 0,
				'paymentcurrency'                     => 'USD',
				'arm_specific_currency_position'      => 'suffix',
				'custom_currency'                     => array(
					'status'    => 0,
					'symbol'    => '',
					'shortname' => '',
					'place'     => 'prefix',
				),
				'enable_tax'                          => 0,
				'tax_amount'                          => 0,
				'file_upload_size_limit'              => '2',
				'enable_gravatar'                     => 1,
				'enable_crop'                         => 1,
				'spam_protection'                     => 1,
				'enqueue_all_js_css'                  => 0,
				'arm_anonymous_data'				  => 0,
				'global_custom_css'                   => '',
				'badge_width'                         => 30,
				'badge_height'                        => 30,
				'profile_permalink_base'              => 'user_login',
				'bbpress_profile_page'                => 0,
				'arm_email_schedular_time'            => 12,
				'arm_invoice_template'                => $arm_default_invoice_template,
				'front_settings'                      => array(
					'level_1_font' => array(
						'font_family'     => 'Open Sans',
						'font_size'       => '18',
						'font_color'      => '#32323a',
						'font_bold'       => 1,
						'font_italic'     => 0,
						'font_decoration' => '',
					),
					'level_2_font' => array(
						'font_family'     => 'Open Sans',
						'font_size'       => '16',
						'font_color'      => '#32323a',
						'font_bold'       => 1,
						'font_italic'     => 0,
						'font_decoration' => '',
					),
					'level_3_font' => array(
						'font_family'     => 'Open Sans',
						'font_size'       => '15',
						'font_color'      => '#727277',
						'font_bold'       => 0,
						'font_italic'     => 0,
						'font_decoration' => '',
					),
					'level_4_font' => array(
						'font_family'     => 'Open Sans',
						'font_size'       => '14',
						'font_color'      => '#727277',
						'font_bold'       => 0,
						'font_italic'     => 0,
						'font_decoration' => '',
					),
					'link_font'    => array(
						'font_family'     => 'Open Sans',
						'font_size'       => '14',
						'font_color'      => '#0c7cd5',
						'font_bold'       => 0,
						'font_italic'     => 0,
						'font_decoration' => '',
					),
					'button_font'  => array(
						'font_family'     => 'Open Sans',
						'font_size'       => '14',
						'font_color'      => '#FFFFFF',
						'font_bold'       => 0,
						'font_italic'     => 0,
						'font_decoration' => '',
					),
				),
			);
			/* Page Settings */
			$default_global_settings['page_settings'] = array(
				'register_page_id'        => 0,
				'login_page_id'           => 0,
				'forgot_password_page_id' => 0,
				'edit_profile_page_id'    => 0,
				'change_password_page_id' => 0,
				'member_profile_page_id'  => 0,
				'logout_page_id'          => 0,
				'guest_page_id'           => 0,
				'thank_you_page_id'       => 0,
				'cancel_payment_page_id'  => 0,
			);
			$default_global_settings                  = apply_filters( 'arm_default_global_settings', $default_global_settings );
			return $default_global_settings;
		}

		function arm_default_pages_content() {
			global $wpdb, $ARMemberLite, $arm_members_class, $arm_slugs, $arm_member_forms;
			$default_rf_id     = $arm_member_forms->arm_get_default_form_id( 'registration' );
			$default_lf_id     = $arm_member_forms->arm_get_default_form_id( 'login' );
			$default_ff_id     = $arm_member_forms->arm_get_default_form_id( 'forgot_password' );
			$default_cf_id     = $arm_member_forms->arm_get_default_form_id( 'change_password' );
			$logged_in_message = esc_html__( 'You are already logged in.', 'armember-membership' );
			$all_pages         = array(
				'register_page_id'        => array(
					'post_title'   => 'Register',
					'post_name'    => 'register',
					'post_content' => '[arm_form id="' . $default_rf_id . '" logged_in_message="' . $logged_in_message . '"]',
					'post_status'  => 'publish',
					'post_parent'  => 0,
					'post_author'  => 1,
					'post_type'    => 'page',
				),
				'login_page_id'           => array(
					'post_title'   => 'Login',
					'post_name'    => 'login',
					'post_content' => '[arm_form id="' . $default_lf_id . '" logged_in_message="' . $logged_in_message . '"]',
					'post_status'  => 'publish',
					'post_parent'  => 0,
					'post_author'  => 1,
					'post_type'    => 'page',
				),
				'forgot_password_page_id' => array(
					'post_title'   => 'Forgot Password',
					'post_name'    => 'forgot_password',
					'post_content' => '[arm_form id="' . $default_ff_id . '" logged_in_message="' . $logged_in_message . '"]',
					'post_status'  => 'publish',
					'post_parent'  => 0,
					'post_author'  => 1,
					'post_type'    => 'page',
				),
				'edit_profile_page_id'    => array(
					'post_title'   => 'Edit Profile',
					'post_name'    => 'edit_profile',
					'post_content' => '[arm_edit_profile title="Edit Your Profile" form_id="101" form_position="center" social_fields="facebook,twitter,linkedin,vk,instagram,pinterest,youtube,dribbble,delicious,tumblr,vine,"  submit_text="Update Profile" message="Your profile has been updated successfully."]',
					'post_status'  => 'publish',
					'post_parent'  => 0,
					'post_author'  => 1,
					'post_type'    => 'page',
				),
				'change_password_page_id' => array(
					'post_title'   => 'Change Password',
					'post_name'    => 'change_password',
					'post_content' => '[arm_form id="' . $default_cf_id . '"]',
					'post_status'  => 'publish',
					'post_parent'  => 0,
					'post_author'  => 1,
					'post_type'    => 'page',
				),
				'guest_page_id'           => array(
					'post_title'   => 'Guest',
					'post_name'    => 'guest',
					'post_content' => '<h3>' . esc_html__( 'Welcome Guest', 'armember-membership' ) . ',</h3>',
					'post_status'  => 'publish',
					'post_parent'  => 0,
					'post_author'  => 1,
					'post_type'    => 'page',
				),
				'thank_you_page_id'       => array(
					'post_title'   => 'Thank You',
					'post_name'    => 'thank_you',
					'post_content' => '<h3>' . esc_html__( 'Thank you for payment with us, We will reach you soon.', 'armember-membership' ) . '</h3>',
					'post_status'  => 'publish',
					'post_parent'  => 0,
					'post_author'  => 1,
					'post_type'    => 'page',
				),
				'cancel_payment_page_id'  => array(
					'post_title'   => 'Cancel Payment',
					'post_name'    => 'cancel_payment',
					'post_content' => esc_html__( 'Your purchase has not been completed.', 'armember-membership' ) . '<br/>' . esc_html__( 'Sorry something went wrong while processing your payment.', 'armember-membership' ),
					'post_status'  => 'publish',
					'post_parent'  => 0,
					'post_author'  => 1,
					'post_type'    => 'page',
				),
			);
			return $all_pages;
		}

		function arm_default_common_messages() {
			global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms;
			$common_messages = array(
				'arm_user_not_exist'                    => esc_html__( 'No such user exists in the system.', 'armember-membership' ),
				'arm_invalid_password_login'            => esc_html__( 'The password you entered is invalid.', 'armember-membership' ),
				'arm_attempts_login_failed'             => esc_html__( 'Remaining Login Attempts :', 'armember-membership' ) . '&nbsp;' . '[ATTEMPTS]',
				'arm_attempts_many_login_failed'        => esc_html__( 'Your Account is locked for', 'armember-membership' ) . ' [LOCKDURATION] ' . esc_html__( 'minutes.', 'armember-membership' ),
				'arm_permanent_locked_message'          => esc_html__( 'Your Account is locked for', 'armember-membership' ) . ' [LOCKDURATION] ' . esc_html__( 'hours.', 'armember-membership' ),
				'arm_not_authorized_login'              => esc_html__( 'Your account is inactive, you are not authorized to login.', 'armember-membership' ),
				'arm_spam_msg'                          => esc_html__( 'Spam detected.', 'armember-membership' ),
				'social_login_failed_msg'               => esc_html__( 'Login Failed, please try again.', 'armember-membership' ),
				'arm_no_registered_email'               => esc_html__( 'There is no user registered with that email address/Username.', 'armember-membership' ),
				'arm_reset_pass_not_allow'              => esc_html__( 'Password reset is not allowed for this user.', 'armember-membership' ),
				'arm_email_not_sent'                    => esc_html__( 'Email could not sent, please contact site admin.', 'armember-membership' ),
				'arm_password_reset'                    => esc_html__('Password Reset Successfully!', 'armember-membership') . ' [SUBTITLE]' . esc_html__('Your Password has been reset, Login now and get started', 'armember-membership') . ' [/SUBTITLE]',
				'arm_password_reset_loginlink'          => esc_html__('Login Now', 'armember-membership'),
				'arm_password_enter_new_pwd'            => esc_html__( 'Please enter new password', 'armember-membership' ),
				'arm_password_reset_pwd_link_expired'   => esc_html__( 'Reset Password Link is invalid.', 'armember-membership' ),
				'arm_form_title_close_account'          => esc_html__( 'Close Account', 'armember-membership' ),
				'arm_form_description_close_account'    => esc_html__( 'Are you sure you want to delete your account? This will erase all of your account data from the site. To delete your account enter your password below.', 'armember-membership' ),
				'arm_password_label_close_account'      => esc_html__( 'Your Password', 'armember-membership' ),
				'arm_submit_btn_close_account'          => esc_html__( 'Submit', 'armember-membership' ),
				'arm_blank_password_close_account'      => esc_html__( 'Password cannot be left Blank.', 'armember-membership' ),
				'arm_invalid_password_close_account'    => esc_html__( 'The password you entered is invalid.', 'armember-membership' ),
				'arm_user_not_created'                  => esc_html__( 'Error while creating user.', 'armember-membership' ),
				'arm_username_exist'                    => esc_html__( 'This username is already registered, please choose another one.', 'armember-membership' ),
				'arm_email_exist'                       => esc_html__( 'This email is already registered, please choose another one.', 'armember-membership' ),
				'arm_avtar_label'                       => esc_html__( 'Avatar', 'armember-membership' ),
				'arm_profile_cover_label'               => esc_html__( 'Profile Cover.', 'armember-membership' ),
				'arm_maxlength_invalid'                 => esc_html__( 'Maximum', 'armember-membership' ) . ' [MAXVALUE]' . esc_html__( ' characters allowed.', 'armember-membership' ),
				'arm_minlength_invalid'                 => esc_html__( 'Please enter at least', 'armember-membership' ) . ' [MINVALUE]' . esc_html__( ' characters.', 'armember-membership' ),
				'arm_expire_activation_link'            => esc_html__( 'Activation link is expired or invalid.', 'armember-membership' ),
				'arm_expire_reset_password_activation_link' => esc_html__( 'Reset Password Link is expired.', 'armember-membership' ),
				'arm_email_activation_manual_pending'   => esc_html__( 'Your account is not activated yet. Please contact site administrator.', 'armember-membership' ),
				'arm_already_active_account'            => esc_html__( 'Your account has been activated.', 'armember-membership' ),
				'arm_account_disabled'                  => esc_html__( 'Your account is disabled. Please contact system administrator.', 'armember-membership' ),
				'arm_account_inactive'                  => esc_html__( 'Your account is currently not active. Please contact the system administrator.', 'armember-membership' ),
				'arm_account_pending'                   => esc_html__( 'Your account is currently not active. An administrator needs to activate your account before you can login.', 'armember-membership' ),
				'arm_account_expired'                   => esc_html__( 'Your account has expired. Please contact system administrator.', 'armember-membership' ),

				'arm_invalid_credit_card'               => esc_html__( 'Please enter the correct card details.', 'armember-membership' ),
				'arm_unauthorized_credit_card'          => esc_html__( 'Card details could not be authorized, please use other card detail.', 'armember-membership' ),
				'arm_credit_card_declined'              => esc_html__( 'Your Card is declined.', 'armember-membership' ),
				'arm_blank_expire_month'                => esc_html__( 'Expiry month should not be blank.', 'armember-membership' ),
				'arm_blank_expire_year'                 => esc_html__( 'Expiry year should not be blank.', 'armember-membership' ),
				'arm_blank_cvc_number'                  => esc_html__( 'CVC Number should not be blank.', 'armember-membership' ),
				'arm_blank_credit_card_number'          => esc_html__( 'Card Number should not be blank.', 'armember-membership' ),
				'arm_invalid_plan_select'               => esc_html__( 'Selected plan is not valid.', 'armember-membership' ),
				'arm_no_select_payment_geteway'         => esc_html__( 'Your selected plan is paid, please select a payment method.', 'armember-membership' ),
				'arm_inactive_payment_gateway'          => esc_html__( 'Payment gateway is not active, please contact the site administrator.', 'armember-membership' ),
				'arm_general_msg'                       => esc_html__( 'Sorry, something went wrong. Please contact the site administrator.', 'armember-membership' ),
				'arm_search_result_found'               => esc_html__( 'No Search Result Found.', 'armember-membership' ),
				'arm_armif_invalid_argument'            => esc_html__( 'Invalid conditional argument(s).', 'armember-membership' ),
				'arm_armif_already_logged_in'           => esc_html__( 'You are already logged in.', 'armember-membership' ),

				'profile_directory_upload_cover_photo'  => esc_html__( 'Upload Cover Photo', 'armember-membership' ),
				'profile_directory_remove_cover_photo'  => esc_html__( 'Remove Cover Photo', 'armember-membership' ),
				'profile_template_upload_profile_photo' => esc_html__( 'Upload Profile Photo', 'armember-membership' ),
				'profile_template_remove_profile_photo' => esc_html__( 'Remove Profile Photo', 'armember-membership' ),
				'directory_sort_by_alphabatically'      => esc_html__( 'Alphabetically', 'armember-membership' ),
				'directory_sort_by_recently_joined'     => esc_html__( 'Recently Joined', 'armember-membership' ),
				'arm_profile_member_since'              => esc_html__( 'Member Since', 'armember-membership' ),
				'arm_profile_view_profile'              => esc_html__( 'View profile', 'armember-membership' ),
				'arm_disabled_submission'               => esc_html__( 'Sorry! Submit Button is disable to avoid any issues because you are logged in as an administrator.', 'armember-membership' ),
			);
			return $common_messages;
		}
		function get_section_wise_common_messages(){

			global $arm_social_feature;

            $common_settings = array(
                "Login Related Messages" => array(
									"arm_user_not_exist" => esc_html__("Incorrect Username/Email",'armember-membership'),
									"arm_invalid_password_login" => esc_html__("Incorrect Password",'armember-membership'),
									"arm_attempts_many_login_failed" => esc_html__("Too Many Failed Login Attempts(Temporary)",'armember-membership'),
									"arm_permanent_locked_message" => esc_html__("Too Many Failed Login Attempts(Permanent)",'armember-membership'),
									"arm_attempts_login_failed" => esc_html__("Remained Login Attempts Warning",'armember-membership'),
									"arm_armif_already_logged_in" => esc_html__("User Already LoggedIn Message",'armember-membership'),
									"arm_spam_msg" => esc_html__("System Detected Spam Robots",'armember-membership'),
								),
				"Forgot Password Messages" => array(
									"arm_no_registered_email" => esc_html__("Incorrect Username/Email",'armember-membership'),
									"arm_reset_pass_not_allow" => esc_html__("Password Reset Not Allowed",'armember-membership'),
									"arm_email_not_sent" => esc_html__("Email Not Sent",'armember-membership'),
								),
				"Change Password Messages" => array(
									"arm_password_reset" => esc_html__("Your password has been reset",'armember-membership'),
                                    'arm_password_reset_loginlink' => esc_html__("Login Now", 'armember-membership'),
									"arm_password_enter_new_pwd" => esc_html__("Please enter new password",'armember-membership'),
									"arm_password_reset_pwd_link_expired" => esc_html__("Reset Password Link is invalid",'armember-membership'),
								),
				"Close Account Messages" => array(
									"arm_form_title_close_account" => esc_html__("Form Title",'armember-membership'),
									"arm_form_description_close_account" => esc_html__("Form Description",'armember-membership'),
									"arm_password_label_close_account" => esc_html__("Password Field Label",'armember-membership'),
									"arm_submit_btn_close_account" => esc_html__("Submit Button Label",'armember-membership'),
									"arm_blank_password_close_account" => esc_html__("Empty Password Message",'armember-membership'),
									"arm_invalid_password_close_account" => esc_html__("Invalid Password Message",'armember-membership'),
								),
				"Registration / Edit Profile Labels" => array(
									"arm_user_not_created" => esc_html__("User Not Created",'armember-membership'),
									"arm_username_exist" => esc_html__("Username Already Exist",'armember-membership'),
									"arm_email_exist" => esc_html__("Email Already Exist",'armember-membership'),
									"arm_avtar_label" => esc_html__("Avatar Field Label( Edit Profile )",'armember-membership'),
									"arm_profile_cover_label" => esc_html__("Profile Cover Field Label( Edit Profile )",'armember-membership'),
									"arm_minlength_invalid" => esc_html__("Minlength",'armember-membership'),
									"arm_maxlength_invalid" => esc_html__("Maxlength",'armember-membership'),
								),
				"Account Related Messages" => array(
									"arm_expire_activation_link" => esc_html__("Expire Activation Link",'armember-membership'),
									"arm_already_active_account" => esc_html__("Account Activated",'armember-membership'),
									"arm_account_pending" => esc_html__("Account Pending",'armember-membership'),
									"arm_account_inactive" => esc_html__("Account Inactivated",'armember-membership'),
								),
				"Payment Related Messages" => array(
									"arm_invalid_plan_select" => esc_html__("Invalid Plan Selected",'armember-membership'),
									"arm_no_select_payment_geteway" => esc_html__("No Gateway Selected For Paid Plan",'armember-membership'),
									"arm_inactive_payment_gateway" => esc_html__("Payment Gateway Inactive",'armember-membership'),
								),
				"Profile/Directory Related Messages" => array(
									"profile_directory_upload_cover_photo" => esc_html__("Upload Cover Photo",'armember-membership'),
									"profile_directory_remove_cover_photo" => esc_html__("Remove Cover Photo",'armember-membership'),
									"profile_template_upload_profile_photo" => esc_html__("Upload Profile Photo",'armember-membership'),
									"profile_template_remove_profile_photo" => esc_html__("Remove Profile Photo",'armember-membership'),
									"directory_sort_by_alphabatically" => esc_html__("Alphabatically (Directory Filter)",'armember-membership'),
									"directory_sort_by_recently_joined" => esc_html__("Recently Joined (Directory Filter)",'armember-membership'),
									"arm_profile_member_since" => esc_html__("Member Since",'armember-membership'),
									"arm_profile_view_profile" => esc_html__("View profile",'armember-membership'),
								),
				"Miscellaneous Messages" => array(
									"arm_general_msg" => esc_html__("General Message",'armember-membership'),
									"arm_search_result_found" => esc_html__("No Search Result Found",'armember-membership'),
									"arm_armif_invalid_argument" => esc_html__("Invalid Arguments (ARM If Shortcode)",'armember-membership'),
								),
				
            );

			if ( $arm_social_feature->isSocialFeature ) {
				$common_settings['Login Related Messages']['arm_social_login_msg'] = esc_html__('Login Failed Message for Social Connect', 'armember-membership');
			}

            return $common_settings;
        }
        function get_common_messages_key_wise_notice(){
            $common_messages_notice = array(
                "arm_attempts_many_login_failed" => esc_html__("To display the duration of locked account, use",'armember-membership')." <b>[LOCKDURATION]</b> " . esc_html__("shortcode in a message.",'armember-membership'),
                "arm_permanent_locked_message" => esc_html__("To display the duration of locked account, use",'armember-membership')." <b>[LOCKDURATION]</b> " . esc_html__("shortcode in a message.",'armember-membership'),
                "arm_attempts_login_failed" => esc_html__("To display the number of remaining attempts use",'armember-membership')." <b>[ATTEMPTS]</b> " . esc_html__("shortcode in a message.",'armember-membership'),
                "arm_armif_already_logged_in" => esc_html__("User already loggedIn message for modal forms ( Navigation Popup )",'armember-membership'),
                "arm_password_reset" => esc_html__("To display password reset message use",'armember-membership'). " <b>[SUBTITLE]".esc_html__("Success message description",'armember-membership')."[/SUBTITLE]</b> " . esc_html__("shortcode in message.",'armember-membership')."<br>".esc_html__("(This message will be used only when password is changed from password reset link sent in mail)",'armember-membership'),
                "arm_password_reset_loginlink" => esc_html__("(This text will be displayed in reset password success message link after password reset successfully)",'armember-membership'),
                "arm_password_enter_new_pwd" => esc_html__("(This message will be displayed in reset password form where user comes by clicking on reset password link)",'armember-membership'),
                "arm_password_reset_pwd_link_expired" => esc_html__("(This message will be displayed on page where user comes by clicking expired reset password link)",'armember-membership'),
                "arm_minlength_invalid" => esc_html__("To display allowed minimum characters use",'armember-membership')." <b>[MINVALUE]</b> " . esc_html__("shortcode in message.",'armember-membership'),
                "arm_maxlength_invalid" => esc_html__("To display allowed maximum characters",'armember-membership')." <b>[MAXVALUE]</b> " . esc_html__("shortcode in message.",'armember-membership'),
            );
            
            return $common_messages_notice;
        }

        function get_common_settings_section_titles() {
            $common_settings_section = array(
                "Login Related Messages" => esc_html__("Login Related Messages",'armember-membership'),
                "Forgot Password Messages" => esc_html__("Forgot Password Messages",'armember-membership'),
                "Change Password Messages" => esc_html__("Change Password Messages",'armember-membership'),
                "Close Account Messages" => esc_html__("Close Account Messages",'armember-membership'),
                "Registration / Edit Profile Labels" => esc_html__("Registration / Edit Profile Labels",'armember-membership'),
                "Account Related Messages" => esc_html__("Account Related Messages",'armember-membership'),
                "Payment Related Messages" => esc_html__("Payment Related Messages",'armember-membership'),
                "Profile/Directory Related Messages" => esc_html__("Profile/Directory Related Messages",'armember-membership'),
                "Miscellaneous Messages" => esc_html__("Miscellaneous Messages",'armember-membership'),
            );
            return $common_settings_section;
        }
		function arm_registration_form_shortcode_exist_in_page( $shortcode_type = '', $page_id = 0 ) {

			global $wp, $wpdb, $ARMemberLite, $arm_member_forms;
				$is_exist = false;

				$page_detail = get_post( $page_id );

			if ( ! empty( $page_detail->ID ) && $page_detail->ID != 0 ) {
					$post_content   = $page_detail->post_content;
					$shortcode_text = array();
				switch ( $shortcode_type ) {
					case 'registration':
					case 'login':
						$is_shortcode    = $this->arm_find_match_shortcode_func( 'arm_form', $post_content );
						$is_cs_shortcode = $this->arm_find_match_shortcode_func( 'cs_armember_cs', $post_content );
						if ( $is_shortcode || $is_cs_shortcode ) {
								$forms = $arm_member_forms->arm_get_member_forms_by_type( $shortcode_type, false );
							if ( ! empty( $forms ) ) {
								foreach ( $forms as $form ) {
										$form_slug        = $form['arm_form_id'];
										$shortcode_text[] = "id='$form_slug'";
										$shortcode_text[] = "id=$form_slug";
										$shortcode_text[] = 'id="' . $form_slug . '"';
									if ( $shortcode_type == 'registration' ) {
										$shortcode_text[] = 'arm_form_registration="' . $form_slug . '"';
									} elseif ( $shortcode_type == 'login' ) {
												$shortcode_text[] = 'arm_form_login="' . $form_slug . '"';
									} elseif ( $shortcode_type == 'change_password' ) {
													$shortcode_text[] = 'arm_form_change_password="' . $form_slug . '"';
									} elseif ( $shortcode_type == 'forgot_password' ) {
										$shortcode_text[] = 'arm_form_forgot_password="' . $form_slug . '"';
									}
								}
								$is_exist = $this->arm_find_registration_match_func( $shortcode_text, $post_content );
							}
						}
						break;
					default:
						break;
				}
			}
				return $is_exist;
		}




		function arm_shortcode_exist_in_page( $shortcode_type = '', $page_id = 0 ) {
			global $wp, $wpdb, $ARMemberLite, $arm_member_forms, $arm_capabilities_global;

			$is_exist = false;
			$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST ); //phpcs:ignore
			if ( isset( $posted_data['action'] ) && $posted_data['action'] == 'arm_shortcode_exist_in_page' ) { //phpcs:ignore
				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce
				$shortcode_type = sanitize_text_field( $posted_data['shortcode_type'] ); //phpcs:ignore
				$page_id        = intval( $posted_data['page_id'] );
			}
			$page_detail = get_post( $page_id );
			if ( ! empty( $shortcode_type ) && ! empty( $page_detail->ID ) && $page_detail->ID != 0 ) {
				$post_content   = $page_detail->post_content;
				$shortcode_text = array();
				switch ( $shortcode_type ) {
					case 'registration':
					case 'login':
					case 'forgot_password':
					case 'change_password':
						$is_shortcode    = $this->arm_find_match_shortcode_func( 'arm_form', $post_content );
						$is_cs_shortcode = false;
						$is_cs_shortcode = apply_filters( 'armember_cs_check_shortcode_in_page', $is_cs_shortcode, 'cs_armember_cs', $post_content );
						if ( $is_shortcode || $is_cs_shortcode ) {
							$forms = $arm_member_forms->arm_get_member_forms_by_type( $shortcode_type, false );

							if ( ! empty( $forms ) ) {
								foreach ( $forms as $form ) {
									$form_slug        = $form['arm_form_id'];
									$shortcode_text[] = "id='$form_slug'";
									$shortcode_text[] = "id=$form_slug";
									$shortcode_text[] = 'id="' . $form_slug . '"';
									if ( $shortcode_type == 'registration' ) {
										$shortcode_text[] = 'arm_form_registration="' . $form_slug . '"';
									} elseif ( $shortcode_type == 'login' ) {
										$shortcode_text[] = 'arm_form_login="' . $form_slug . '"';
									} elseif ( $shortcode_type == 'change_password' ) {
										$shortcode_text[] = 'arm_form_change_password="' . $form_slug . '"';
									} elseif ( $shortcode_type == 'forgot_password' ) {
										$shortcode_text[] = 'arm_form_forgot_password="' . $form_slug . '"';
									}
								}
								$is_exist = $this->arm_find_match_func( $shortcode_text, $post_content );
							}
						}
						/* Check Membership Setup Wizard Shortcode */
						if ( $shortcode_type == 'registration' && ! $is_exist ) {
							$is_exist = $this->arm_find_match_shortcode_func( 'arm_setup', $post_content );
							if ( ! $is_exist ) {
								$is_exist = apply_filters( 'armember_cs_check_shortcode_in_page', $is_exist, 'cs_armember_cs', $post_content );
							}
						}
						break;
					case 'edit_profile':
						$is_exist = $this->arm_find_match_shortcode_func( 'arm_edit_profile', $post_content );
						if ( ! $is_exist ) {
							$is_exist = apply_filters( 'armember_cs_check_shortcode_in_page', $is_exist, 'cs_armember_cs', $post_content );
						}
						break;
					case 'members_directory':
						$is_exist = $this->arm_find_match_shortcode_func( 'arm_template', $post_content );
						break;
					default:
						break;
				}
			}
			if ( isset( $posted_data['action'] ) && $posted_data['action'] == 'arm_shortcode_exist_in_page' ) { //phpcs:ignore
				echo arm_pattern_json_encode( array( 'status' => $is_exist ) );
				exit;
			} else {
				return $is_exist;
			}
		}

		function arm_find_match_shortcode_func( $key = '', $string = '' ) {
			$matched = false;
			$pattern = '\[' . $key . '(.*?)\]';
			if ( ! empty( $key ) && ! empty( $string ) ) {
				if ( preg_match_all( '/' . $pattern . '/s', $string, $matches ) > 0 ) {
					$matched = true;
				}
			}
			return $matched;
		}

		function arm_find_match_func( $key = array(), $string = '' ) {
			if ( ! empty( $key ) && ! empty( $string ) ) {
				foreach ( $key as $val ) {
					if ( preg_match_all( '/' . $val . '/s', $string, $matches ) > 0 ) {
						return true;
					}
				}
			}
			return false;
		}

		function arm_find_registration_match_func( $key = array(), $string = '' ) {
			if ( ! empty( $key ) && ! empty( $string ) ) {
				foreach ( $key as $val ) {
					if ( preg_match_all( '/' . $val . '/s', $string, $matches ) > 0 ) {

						$val = preg_replace( '/[a-z=\'\"]/', '', $val );
						return $val;
					}
				}
			}
			return false;
		}

		/**
		 * Parse shortcodes in Feed Post Excerpt
		 */
		function arm_filter_the_excerpt( $content ) {
			$isARMShortcode = $this->arm_find_match_shortcode_func( 'arm_', $content );

			if ( $isARMShortcode ) {
				$content = do_shortcode( $content );
			}
			return $content;
		}

		function arm_get_all_roles() {
			$allRoles = array();
			if ( ! function_exists( 'get_editable_roles' ) && file_exists( ABSPATH . '/wp-admin/includes/user.php' ) ) {
				require_once ABSPATH . '/wp-admin/includes/user.php';
			}
			global $wp_roles;
			$roles = get_editable_roles();
			if ( ! empty( $roles ) ) {
				unset( $roles['administrator'] );
				foreach ( $roles as $key => $role ) {
					$allRoles[ $key ] = $role['name'];
				}
			}

			return $allRoles;
		}



		function arm_get_permalink( $slug = '', $id = 0 ) {
			global $wp, $wpdb, $ARMemberLite;
			$link = ARMLITE_HOME_URL;
			if ( ! empty( $slug ) && $slug != '' ) {
				$object = $wpdb->get_results( $wpdb->prepare('SELECT `ID` FROM ' . $wpdb->posts . " WHERE `post_name`=%s",$slug) );//phpcs:ignore --Reason: $wpdb->posts is a table name
				if ( ! empty( $object ) ) {
					$link = get_permalink( $object[0]->ID );
				}
			} elseif ( ! empty( $id ) && $id != 0 ) {
				$link = get_permalink( $id );
			}
			return $link;
		}

		function arm_get_user_profile_url( $userid = 0, $show_admin_users = 0 ) {
			global $wp, $wpdb, $ARMemberLite, $arm_social_feature;
			if ( $show_admin_users == 0 ) {
				if ( user_can( $userid, 'administrator' ) ) {
					return '#';
				}
			}
			$profileUrl = ARMLITE_HOME_URL;
			if ( $arm_social_feature->isSocialFeature ) {
				if ( isset( $this->profile_url ) && ! empty( $this->profile_url ) ) {
					$profileUrl = $this->profile_url;
				} else {
					$profile_page_id   = isset( $this->global_settings['member_profile_page_id'] ) ? $this->global_settings['member_profile_page_id'] : 0;
					$profile_page_url  = get_permalink( $profile_page_id );
					$profileUrl        = ( ! empty( $profile_page_url ) ) ? $profile_page_url : $profileUrl;
					$this->profile_url = $profileUrl;
				}
				if ( ! empty( $userid ) && $userid != 0 ) {
					$permalinkBase = isset( $this->global_settings['profile_permalink_base'] ) ? $this->global_settings['profile_permalink_base'] : 'user_login';
					$userBase      = $userid;
					if ( $permalinkBase == 'user_login' ) {
						$userInfo = get_userdata( $userid );
						$userBase = $userInfo->user_login;
					}
					if ( get_option( 'permalink_structure' ) ) {
						$profileUrl = trailingslashit( untrailingslashit( $profileUrl ) );
						$profileUrl = $profileUrl . $userBase . '/';
					} else {
						$profileUrl = $this->add_query_arg( 'arm_user', $userBase, $profileUrl );
					}
				}
			} else {
				if ( isset( $this->global_settings['edit_profile_page_id'] ) && $this->global_settings['edit_profile_page_id'] != 0 ) {
					$profileUrl = get_permalink( $this->global_settings['edit_profile_page_id'] );
				}
			}
			return $profileUrl;
		}

		function arm_user_query_vars( $public_query_vars ) {
			$public_query_vars[] = 'arm_user';
			return $public_query_vars;
		}

		function arm_user_rewrite_rules() {
			global $wp, $wpdb, $wp_rewrite, $ARMemberLite;
			$allGlobalSettings = $this->arm_get_all_global_settings( true );
			if ( isset( $allGlobalSettings['member_profile_page_id'] ) && $allGlobalSettings['member_profile_page_id'] != 0 ) {
				$profile_page_id = $allGlobalSettings['member_profile_page_id'];
				$profilePage     = get_post( $profile_page_id );
		                $is_parent_page = isset($profilePage->post_parent) && $profilePage->post_parent != 0 ? true : false ; 
		                $parent_page_id = isset($profilePage->post_parent) && !empty($profilePage->post_parent) ? $profilePage->post_parent : 0 ; 
		                $profileParentSlug = '';
		                while ( $is_parent_page ) {
		                    $parentPage = get_post($parent_page_id);
		                    $profileParentSlug = isset($parentPage->post_name) &&  !empty($parentPage->post_name) ? $parentPage->post_name.'/'.$profileParentSlug : '' ;                                        
		                    $parent_page_id = $parentPage->post_parent;                    
		                    if($parent_page_id != 0) {
		                        $is_parent_page = true;
		                    } else {
		                        $is_parent_page = false;
		                        break;
		                    }
		                }
				if ( isset( $profilePage->post_name ) ) {
					$profileSlug = $profilePage->post_name;
					add_rewrite_rule( $profileParentSlug.$profileSlug . '/([^/]+)/?$', 'index.php?page_id=' . $profile_page_id . '&arm_user=$matches[1]', 'top' );
				}
			}
		}

		function arm_generate_rewrite_rules( $wp_rewrite ) {
			global $wp, $wpdb, $wp_rewrite, $ARMemberLite;
			$allGlobalSettings = $this->arm_get_all_global_settings( true );
			if ( isset( $allGlobalSettings['member_profile_page_id'] ) && $allGlobalSettings['member_profile_page_id'] != 0 ) {
				$profile_page_id = $allGlobalSettings['member_profile_page_id'];
				$profilePage     = get_post( $profile_page_id );
				if ( isset( $profilePage->post_name ) ) {
					$profileSlug = $profilePage->post_name;
					// add_rewrite_rule($profileSlug . '/([^/]+)/?$', 'index.php?page_id=' . $profile_page_id . '&arm_user=$matches[1]', 'top');
					$feed_rules = array(
						$profileSlug . '/([^/]+)/?$' => 'index.php?page_id=' . $profile_page_id . '&arm_user=$matches[1]',
					);

					$wp_rewrite->rules = $feed_rules + $wp_rewrite->rules;
				}
			}
			return $wp_rewrite->rules;
		}

		/**
		 * Create Pagination Links
		 *
		 * @param Int $total Total Number Of Records
		 * @param Int $per_page Number Of Records Per Page
		 */
		function arm_get_paging_links( $current = 1, $total = 10, $per_page = 10, $type = '' ) {
			global $wp, $wp_rewrite;
			$return_links = '';
			$current      = ( ! empty( $current ) && $current != 0 ) ? $current : 1;
			$total_links  = ceil( $total / $per_page );
			/* Don't print empty markup if there's only one page. */
			if ( $total_links < 1 ) {
				return;
			}
			$end_size   = 1;
			$mid_size   = 1;
			$page_links = array();
			$dots       = false;
			if ( $current && 1 < $current ) {
				$prev = $current - 1;
				$page_links[] = '<a class="arm_prev arm_page_numbers" href="javascript:void(0)" data-page="' . esc_attr( $prev ) . '" data-per_page="' . $per_page . '"></a>';
			} else {
				$page_links[] = '<a class="arm_prev current arm_page_numbers" href="javascript:void(0)" data-per_page="' . esc_attr($per_page) . '"></a>';
			}
			for ( $n = 1; $n <= $total_links; $n++ ) {
				if ( $n == $current ) {
					$page_links[] = '<a class="current arm_page_numbers" href="javascript:void(0)" data-page="' . esc_attr($current) . '" data-per_page="' . esc_attr($per_page) . '">' . number_format_i18n( $n ) . '</a>';
					$dots         = true;
				} else {
					if ( $n <= $end_size || ( $current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total_links - $end_size ) {
						$page_links[] = '<a class="arm_page_numbers" href="javascript:void(0)" data-page="' . esc_attr($n) . '" data-per_page="' . esc_attr($per_page) . '">' . number_format_i18n( $n ) . '</a>';
						$dots         = true;
					} elseif ( $dots ) {
						$page_links[] = '<span class="arm_page_numbers dots">&hellip;</span>';
						$dots         = false;
					}
				}
			}
			if ( $current && ( $current < $total_links || -1 == $total_links ) ) {
				$next = $current + 1;
				$page_links[] = '<a class="arm_next arm_page_numbers" href="javascript:void(0)" data-page="' . esc_attr( $next ) . '" data-per_page="' . esc_attr($per_page) . '"></a>';
			} else {
				$page_links[] = '<a class="arm_next current arm_page_numbers" href="javascript:void(0)" data-per_page="' . esc_attr($per_page) . '"></a>';
			}
			if ( ! empty( $page_links ) ) {

				$startNum = ( ! empty( $current ) && $current > 1 ) ? ( ( $current - 1 ) * $per_page ) + 1 : 1;
				$endNum   = $current * $per_page;
				$endNum   = ( $endNum > $total ) ? $total : $endNum;
				/* Join Links */
				$links         = join( "\n", $page_links );
				$return_links  = '<div class="arm_paging_wrapper arm_paging_wrapper_' . $type . '">';
				$return_links .= '<div class="arm_paging_info">';
				switch ( $type ) {
					case 'activity':
						$return_links .= esc_html__( 'Showing', 'armember-membership' ) . ' ' . esc_html($startNum) . ' ' . esc_html__( 'to', 'armember-membership' ) . ' ' . esc_html($endNum) . ' ' . esc_html__( 'of', 'armember-membership' ) . ' ' . esc_html($total) . ' ' . esc_html__( 'total activities', 'armember-membership' );
						break;
					case 'membership_history':
						$return_links .= esc_html__( 'Showing', 'armember-membership' ) . ' ' . esc_html($startNum) . ' ' . esc_html__( 'to', 'armember-membership' ) . ' ' . esc_html($endNum) . ' ' . esc_html__( 'of', 'armember-membership' ) . ' ' . esc_html($total) . ' ' . esc_html__( 'total records', 'armember-membership' );
						break;
					case 'directory':
						$return_links .= esc_html__( 'Showing', 'armember-membership' ) . ' ' . esc_html($startNum) . ' - ' . esc_html($endNum) . ' ' . esc_html__( 'of', 'armember-membership' ) . ' ' . esc_html($total) . ' ' . esc_html__( 'members', 'armember-membership' );
						break;
					case 'transaction':
						$return_links .= esc_html__( 'Showing', 'armember-membership' ) . ' ' . esc_html($startNum) . ' - ' . esc_html($endNum) . ' ' . esc_html__( 'of', 'armember-membership' ) . ' ' . esc_html($total) . ' ' . esc_html__( 'transactions', 'armember-membership' );
						break;
					default:
						$return_links .= esc_html__( 'Showing', 'armember-membership' ) . ' ' . esc_html($startNum) . ' - ' . esc_html($endNum) . ' ' . esc_html__( 'of', 'armember-membership' ) . ' ' . esc_html($total) . ' ' . esc_html__( 'records', 'armember-membership' );
						break;
				}
				$return_links .= '</div>';
				$return_links .= '<div class="arm_paging_links">' . $links . '</div>';
				$return_links .= '</div>';
			}
			return $return_links;
		}

		function arm_filter_get_avatar( $avatar, $id_or_email, $size, $default, $alt = '' ) {
			global $pagenow;
			/* Do not filter if inside WordPress options page OR `enable_gravatar` set to '0' */
			if ( 'options-discussion.php' == $pagenow ) {
				return $avatar;
			}
			$user_avatar = $this->arm_get_user_avatar( $id_or_email, $size, $default, $alt );
			if ( ! empty( $user_avatar ) ) {
				$avatar = $user_avatar;
			} else {
				if ( empty($this->global_settings['enable_gravatar']) ) {
					$avatar = "<img src='" . MEMBERSHIPLITE_IMAGES_URL . "/avatar_placeholder.png' class='avatar arm_grid_avatar arm-avatar avatar-{$size}' width='{$size}' />"; // phpcs:ignore
				} else {
					$avatar = str_replace( 'avatar-' . $size, 'avatar arm_grid_avatar arm-avatar avatar-' . $size, $avatar );
				}
			}
			return apply_filters( 'arm_change_user_avatar', $avatar, $id_or_email, $size, $default, $alt );
		}

		function arm_filter_get_avatar_url( $url, $id_or_email, $args ) {
			if ( is_numeric( $id_or_email ) ) {
				$user_id = (int) $id_or_email;
			} elseif ( is_string( $id_or_email ) && ( $user = get_user_by( 'email', $id_or_email ) ) ) {
				$user_id = $user->ID;
			} elseif ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ) {
				$user_id = (int) $id_or_email->user_id;
			} else {
				$user_id = 0;
			}

			if ( ! empty( $user_id ) ) {
				$avatar_url = get_user_meta( $user_id, 'avatar', true );
				if ( ! empty( $avatar_url ) && file_exists( MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename( $avatar_url ) ) ) {
					return $avatar_url;
				}
			}
			return $url;
		}

		function arm_get_avatar( $id_or_email, $size = '96', $default = '', $alt = false ) {
			global $wp, $wpdb, $ARMemberLite;
			$user_avatar = $this->arm_get_user_avatar( $id_or_email, $size, $default, $alt );
			if ( $this->global_settings['enable_gravatar'] == '1' && ! empty( $user_avatar ) ) {
				$avatar = apply_filters( 'arm_change_user_avatar', $user_avatar, $id_or_email, $size, $default, $alt );
			} else {
				$avatar = get_avatar( $id_or_email, $size, $default, $alt );
			}
			return $avatar;
		}

		function arm_get_user_avatar( $id_or_email, $size = '96', $default = '', $alt = false ) {
			global $wp, $wpdb, $ARMemberLite;
			$safe_alt = ( false === $alt ) ? '' : esc_attr( $alt );
			if ( is_numeric( $id_or_email ) ) {
				$user_id = (int) $id_or_email;
			} elseif ( is_string( $id_or_email ) && ( $user = get_user_by( 'email', $id_or_email ) ) ) {
				$user_id = $user->ID;
			} elseif ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ) {
				$user_id = (int) $id_or_email->user_id;
			} else {
				$user_id = 0;
			}
			$user             = get_user_by( 'id', $user_id );
			$avatar_url       = get_user_meta( $user_id, 'avatar', true );
			$avatar_w_h_class = '';
			if ( ! empty( $avatar_url ) && file_exists( MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename( $avatar_url ) ) ) {
				$avatar_detail = @getimagesize( MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename( $avatar_url ) );
				if ( $size > $avatar_detail[0] ) {
					$avatar_w_h_class = ' arm_avatar_small_width';
				}
				if ( $size > $avatar_detail[1] ) {
					$avatar_w_h_class .= ' arm_avatar_small_height';
				}
			}
			$avatar_class = 'avatar arm_grid_avatar gravatar avatar arm-avatar photo avatar-' . $size . ' ' . $avatar_w_h_class;
			if ( empty( $safe_alt ) && $user ) {
				$safe_alt = esc_html__( 'Profile photo of', 'armember-membership' ) . $user->user_login;
			}
			if ( ! empty( $avatar_url ) && file_exists( MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename( $avatar_url ) ) ) {
				$avatar_filesize = @filesize( MEMBERSHIPLITE_UPLOAD_DIR . '/' . basename( $avatar_url ) );
				if ( $avatar_filesize > 0 ) {
					if ( file_exists( strstr( $avatar_url, '//' ) ) ) {
						$avatar_url = strstr( $avatar_url, '//' );
					} elseif ( file_exists( $avatar_url ) ) {
						$avatar_url = $avatar_url;
					} else {
						$avatar_url = $avatar_url;
					}
					$avatar = '<img src="' . esc_url($avatar_url) . '" class="' . esc_attr($avatar_class) . '" width="' . esc_attr($size) . '" height="' . esc_attr($size) . '" alt="' . esc_attr($safe_alt) . '" />'; //phpcs:ignore 
				} else {
					$avatar = '';
				}
			} else {
				$avatar = '';
			}
			return $avatar;
		}

		function arm_default_avatar_url( $default = '' ) {
			global $wp, $wpdb, $ARMemberLite;
			$avatar_default = get_option( 'avatar_default' );
			$default        = ( ! empty( $avatar_default ) ) ? $avatar_default : 'mystery';
			if ( is_ssl() ) {
				$host = 'https://secure.gravatar.com';
			} else {
				$host = 'http://0.gravatar.com';
			}
			if ( 'mystery' == $default ) {
				$default = "$host/avatar/ad516503a11cd5ca435acc9bb6523536?s={$size}";
			} elseif ( 'blank' == $default ) {
				$default = includes_url( 'images/blank.gif' );
			} elseif ( 'gravatar_default' == $default ) {
				$default = "$host/avatar/?s={$size}";
			} elseif ( strpos( $default, 'http://' ) === 0 ) {
				$default = add_query_arg( 's', $size, $default );
			}
			return esc_url( $default );
		}

		/**
		 * Get Single Global Setting by option name
		 */
		function arm_get_single_global_settings( $option_name, $default = '' ) {
			global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms;
			$all_settings = $this->global_settings;
			$option_value = $default;
			if ( ! empty( $option_name ) ) {
				if ( isset( $all_settings[ $option_name ] ) && ! empty( $all_settings[ $option_name ] ) ) {
					$option_value = $all_settings[ $option_name ];
				} elseif ( $option_name == 'page_settings' ) {
					$defaultGS    = $this->arm_default_global_settings();
					$option_value = shortcode_atts( $defaultGS['page_settings'], $all_settings );
				}
			}
			return $option_value;
		}

		function arm_get_all_global_settings( $merge = false ) {
			global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms;
			$default_global_settings = $this->arm_default_global_settings();
			$global_settings         = get_option( 'arm_global_settings', $default_global_settings );
			$all_global_settings     = maybe_unserialize( $global_settings );
			$all_global_settings     = apply_filters( 'arm_get_all_global_settings', $all_global_settings );
			if ( $merge ) {
				$all_global_settings['general_settings'] = isset( $all_global_settings['general_settings'] ) ? $all_global_settings['general_settings'] : $default_global_settings['general_settings'];
				$all_global_settings['page_settings']    = isset( $all_global_settings['page_settings'] ) ? $all_global_settings['page_settings'] : $default_global_settings['page_settings'];
				$arm_merge_global_settings               = array_merge( $all_global_settings['general_settings'], $all_global_settings['page_settings'] );
				return $arm_merge_global_settings;
			}
			return $all_global_settings;
		}

		function arm_get_all_block_settings() {
			global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms;
			$default_block_settings = array(
				'failed_login_lockdown'       => 1,
				'remained_login_attempts'     => 1,
				'track_login_history'         => 1,
				'max_login_retries'           => 5,
				'temporary_lockdown_duration' => 10,
				'permanent_login_retries'     => 15,
				'permanent_lockdown_duration' => 24,

				'arm_block_usernames'         => '',
				'arm_block_usernames_msg'     => esc_html__( 'Username should not contain bad words.', 'armember-membership' ),
				'arm_block_emails'            => '',
				'arm_block_emails_msg'        => esc_html__( 'Email Address should not contain bad words.', 'armember-membership' ),

			);
			$block_settings     = get_option( 'arm_block_settings', $default_block_settings );
			$all_block_settings = maybe_unserialize( $block_settings );
			if ( ! is_array( $all_block_settings ) ) {
				$all_block_settings = array();
			}
			$all_block_settings['arm_block_usernames_msg'] = ! empty( $all_block_settings['arm_block_usernames_msg'] ) ? stripslashes( $all_block_settings['arm_block_usernames_msg'] ) : '';
			$all_block_settings['arm_block_emails_msg']    = ! empty( $all_block_settings['arm_block_emails_msg'] ) ? stripslashes( $all_block_settings['arm_block_emails_msg'] ) : '';

			$all_block_settings = apply_filters( 'arm_get_all_block_settings', $all_block_settings );
			return $all_block_settings;
		}

		function arm_get_parsed_block_settings() {
			global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms;
			$parsed_block_settings = $this->arm_get_all_block_settings();
			if ( is_array( $parsed_block_settings ) ) {
				foreach ( $parsed_block_settings as $type => $val ) {
					if ( ! empty( $val ) && in_array( $type, array( 'arm_block_usernames', 'arm_block_emails' ) ) ) {

							$new_val = array_map( 'strtolower', array_map( 'trim', explode( "\n", $val ) ) );

						$parsed_block_settings[ $type ] = $new_val;
					}
				}
			}
			$parsed_block_settings = apply_filters( 'arm_get_parsed_block_settings', $parsed_block_settings );
			return $parsed_block_settings;
		}

		function arm_get_all_common_message_settings() {
			global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms;
			$arm_default_common_messages = $this->arm_default_common_messages();
			$common_message_settings     = get_option( 'arm_common_message_settings', $arm_default_common_messages );
			$all_common_message_settings = maybe_unserialize( $common_message_settings );
			$all_common_message_settings = ( ! empty( $all_common_message_settings ) ) ? $all_common_message_settings : array();
			if ( ! empty( $all_common_message_settings ) ) {
				foreach ( $all_common_message_settings as $key => $val ) {
					$all_common_message_settings[ $key ] = stripslashes( $val );
				}
			}

			$all_common_message_settings = apply_filters( 'arm_get_all_common_message_settings', $all_common_message_settings );
			return $all_common_message_settings;
		}

		function arm_update_all_settings() {
			global $wpdb, $wp_rewrite, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_email_settings, $arm_payment_gateways, $arm_access_rules, $arm_crons, $arm_capabilities_global, $ARMemberLiteAllowedHTMLTagsArray;

			$response                = array(
				'type' => 'error',
				'msg'  => esc_html__( 'There is a error while updating settings, please try again.', 'armember-membership' ),
			);
			$is_new_wp_admin_path    = false;
			$default_global_settings = $this->arm_default_global_settings();
			$old_global_settings     = $this->arm_get_all_global_settings();

			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_update_global_settings' ) { //phpcs:ignore

				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce

				//$save_all = isset( $_POST['save_all'] ) ? $_POST['save_all'] : '';

				$_POST['arm_general_settings']['hide_register_link'] = isset( $_POST['arm_general_settings']['hide_register_link'] ) ? intval( $_POST['arm_general_settings']['hide_register_link'] ) : 0; //phpcs:ignore
				$_POST['arm_general_settings']['enable_gravatar']    = isset( $_POST['arm_general_settings']['enable_gravatar'] ) ? intval( $_POST['arm_general_settings']['enable_gravatar'] ) : 0; //phpcs:ignore
				$_POST['arm_general_settings']['enable_crop']        = isset( $_POST['arm_general_settings']['enable_crop'] ) ? intval( $_POST['arm_general_settings']['enable_crop'] ) : 0; //phpcs:ignore
				$_POST['arm_general_settings']['spam_protection']    = isset( $_POST['arm_general_settings']['spam_protection'] ) ? intval( $_POST['arm_general_settings']['spam_protection'] ) : 0; //phpcs:ignore
				$_POST['arm_general_settings']['enable_tax']         = isset( $_POST['arm_general_settings']['enable_tax'] ) ? intval( $_POST['arm_general_settings']['enable_tax'] ) : 0; //phpcs:ignore
				$_POST['arm_general_settings']['arm_anonymous_data'] = isset($_POST['arm_general_settings']['arm_anonymous_data']) ? intval($_POST['arm_general_settings']['arm_anonymous_data']) : 0; //phpcs:ignore

				$arm_general_settings = isset( $_POST['arm_general_settings'] ) ? array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST['arm_general_settings'] ) : array(); //phpcs:ignore

				$new_global_settings['general_settings'] = shortcode_atts( $default_global_settings['general_settings'], $arm_general_settings );
				if ( $new_global_settings['general_settings']['user_register_verification'] != 'auto' ) {
					$new_global_settings['general_settings']['arm_new_signup_status'] = 3;
				}

				$flush_rewrite_rules = false;

				$all_saved_global_settings = maybe_unserialize( get_option( 'arm_global_settings' ) );

				$logout = true;

				$home_root = wp_parse_url( home_url() );
				if ( isset( $home_root['path'] ) ) {
					$home_root = trailingslashit( $home_root['path'] );
				} else {
					$home_root = '/';
				}

				if ( ! isset( $new_global_settings['general_settings']['custom_currency']['status'] ) ) {
					$new_global_settings['general_settings']['custom_currency'] = array(
						'status'    => 0,
						'symbol'    => '',
						'shortname' => '',
						'place'     => 'prefix',
					);
				}
				$new_global_settings['page_settings'] = $old_global_settings['page_settings'];

				$arm_exclude_role_for_hide_admin  = ( isset( $_POST['arm_general_settings']['arm_exclude_role_for_hide_admin'] ) && ! empty( $_POST['arm_general_settings']['arm_exclude_role_for_hide_admin'] ) ) ? implode( ',', array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST['arm_general_settings']['arm_exclude_role_for_hide_admin'] ))  : ''; //phpcs:ignore

				$new_general_settings['arm_exclude_role_for_hide_admin'] = sanitize_text_field( $arm_exclude_role_for_hide_admin );

				// set old global setting because its updated from other page

				$new_global_settings['general_settings']['arm_exclude_role_for_restrict_admin'] = isset( $old_global_settings['general_settings']['arm_exclude_role_for_restrict_admin'] ) ? sanitize_text_field($old_global_settings['general_settings']['arm_exclude_role_for_restrict_admin']) : '';
				$new_global_settings['general_settings']['restrict_admin_panel']                = isset( $old_global_settings['general_settings']['restrict_admin_panel'] ) ? sanitize_text_field($old_global_settings['general_settings']['restrict_admin_panel']) : 0;

				$new_global_settings['page_settings']['guest_page_id']                     = isset( $old_global_settings['page_settings']['guest_page_id'] ) ? sanitize_text_field($old_global_settings['page_settings']['guest_page_id']) : 0;
				$new_global_settings['page_settings']['arm_access_page_for_restrict_site'] = isset( $old_global_settings['page_settings']['arm_access_page_for_restrict_site'] ) ? sanitize_text_field($old_global_settings['page_settings']['arm_access_page_for_restrict_site']) : '';

				$new_global_settings = apply_filters( 'arm_before_update_global_settings', $new_global_settings, $_POST ); //phpcs:ignore

				/* -------- Update Email Schedular Start ------- */
				$arm_old_general_settings = $old_global_settings['general_settings'];
				$arm_old_email_schedular  = isset( $arm_old_general_settings['arm_email_schedular_time'] ) ? $arm_old_general_settings['arm_email_schedular_time'] : 0;

				if ( $arm_old_email_schedular != $new_global_settings['general_settings']['arm_email_schedular_time'] ) {
					$arm_all_crons = $arm_crons->arm_get_cron_hook_names();

					foreach ( $arm_all_crons as $arm_cron_hook_name ) {
						$arm_crons->arm_clear_cron( $arm_cron_hook_name );
					}
				}
				/* -------- Update Email Schedular End------- */

				update_option( 'arm_global_settings', $new_global_settings );

				$arm_email_settings->arm_update_email_settings();
				$arm_payment_gateways->arm_update_payment_gate_status();
				$response = array(
					'type' => 'success',
					'msg'  => esc_html__( 'Global Settings Saved Successfully.', 'armember-membership' ),
				);
				if ( isset( $redirect_to ) && $redirect_to != '' ) {
					if ( ! $logout ) {
						$response['url'] = $redirect_to;
					} else {
						wp_destroy_current_session();
						wp_clear_auth_cookie();
						$response['url'] = wp_login_url();
					}
				}
			}
			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_page_settings' ) { //phpcs:ignore

				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce

				$default_global_settings                 = $this->arm_default_global_settings();
				$arm_page_settings                       = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST['arm_page_settings'] ); //phpcs:ignore
				$old_page_settings                       = shortcode_atts( $default_global_settings['page_settings'], $old_global_settings['page_settings'] );
				$new_global_settings['page_settings']    = shortcode_atts( $old_page_settings, $arm_page_settings );
				$new_global_settings['general_settings'] = $old_global_settings['general_settings'];
				$new_global_settings                     = apply_filters( 'arm_before_update_page_settings', $new_global_settings, $_POST ); //phpcs:ignore
				update_option( 'arm_global_settings', $new_global_settings );
				$this->arm_user_rewrite_rules();
				$wp_rewrite->flush_rules( false );
				$response = array(
					'type' => 'success',
					'msg'  => esc_html__( 'Page Settings Saved Successfully.', 'armember-membership' ),
				);
			}
			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_update_block_settings' ) { //phpcs:ignore

				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_block_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce
				
				$post_block_settings                            = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST['arm_block_settings'] ); //phpcs:ignore

				$post_block_settings['arm_block_usernames'] = !empty( $_POST['arm_block_settings']['arm_block_usernames'] ) ? sanitize_textarea_field( $_POST['arm_block_settings']['arm_block_usernames'] ) : '';//phpcs:ignore
				$post_block_settings['arm_block_emails'] = !empty( $_POST['arm_block_settings']['arm_block_emails'] ) ? sanitize_textarea_field( $_POST['arm_block_settings']['arm_block_emails'] ) : '';//phpcs:ignore

				$post_block_settings['failed_login_lockdown']   = isset( $post_block_settings['failed_login_lockdown'] ) ? intval( $post_block_settings['failed_login_lockdown'] ) : 0;
				$post_block_settings['remained_login_attempts'] = isset( $post_block_settings['remained_login_attempts'] ) ? intval( $post_block_settings['remained_login_attempts'] ) : 0;
				$post_block_settings['track_login_history']     = isset( $post_block_settings['track_login_history'] ) ? intval( $post_block_settings['track_login_history'] ) : 0;

				$arm_block_usernames = implode( PHP_EOL, array_filter( array_map( 'trim', explode( PHP_EOL, $post_block_settings['arm_block_usernames'] ) ) ) );
				$arm_block_emails    = implode( PHP_EOL, array_filter( array_map( 'trim', explode( PHP_EOL, $post_block_settings['arm_block_emails'] ) ) ) );

				$is_update = true;
				if ( $is_update == true ) {

					$post_block_settings['arm_block_usernames'] = $arm_block_usernames;
					$post_block_settings['arm_block_emails']    = $arm_block_emails;

					$post_block_settings = apply_filters( 'arm_before_update_block_settings', $post_block_settings, $_POST ); //phpcs:ignore

					update_option( 'arm_block_settings', $post_block_settings );

					$response = array(
						'type' => 'success',
						'msg'  => esc_html__( 'Settings Saved Successfully.', 'armember-membership' ),
					);
				} else {
					$response = array(
						'type' => 'error',
						'msg'  => esc_html__( 'Some of users are having administrator previlegs. So those cant be block.', 'armember-membership' ),
					);
				}
			}
			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_update_redirect_settings' ) { //phpcs:ignore

				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce
				$post_redirection_settings = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $_POST['arm_redirection_settings'] ); //phpcs:ignore

				$default_redirection_url = $post_redirection_settings['login']['conditional_redirect']['default'];
				unset( $post_redirection_settings['login']['conditional_redirect']['default'] );

				$post_redirection_settings['login']['conditional_redirect']            = array_values( $post_redirection_settings['login']['conditional_redirect'] );
				$post_redirection_settings['login']['conditional_redirect']['default'] = $default_redirection_url;
				$is_update = true;
				if ( $is_update == true ) {
					$post_redirection_settings = apply_filters( 'arm_before_update_redirection_settings', $post_redirection_settings, $_POST ); //phpcs:ignore
					update_option( 'arm_redirection_settings', $post_redirection_settings );
					$response = array(
						'type' => 'success',
						'msg'  => esc_html__( 'Settings Saved Successfully.', 'armember-membership' ),
					);
				} else {
					$response = array(
						'type' => 'error',
						'msg'  => esc_html__( 'Some of users are having administrator previlegs. So those cant be block.', 'armember-membership' ),
					);
				}
			}

			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_update_common_message_settings' ) { //phpcs:ignore
				
				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1'); //phpcs:ignore --Reason:Verifying nonce
				$common_messages = !empty($_POST['arm_common_message_settings']) ? array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST['arm_common_message_settings'] ) : array(); //phpcs:ignore
				$common_messages = apply_filters( 'arm_before_update_common_message_settings', $common_messages, $_POST ); //phpcs:ignore 

				if(!empty($common_messages) && is_array($common_messages) )
				{
					foreach($common_messages as $common_message_key => $common_message_val)
					{
						$common_message_key = wp_kses($common_message_key, $ARMemberLiteAllowedHTMLTagsArray);
						$common_messages[$common_message_key] = wp_kses($common_message_val, $ARMemberLiteAllowedHTMLTagsArray);
					}
					update_option( 'arm_common_message_settings', $common_messages );
				}
				$response = array(
					'type' => 'success',
					'msg'  => esc_html__( 'Settings Saved Successfully.', 'armember-membership' ),
				);
			}

			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_update_access_restriction_settings' ) { //phpcs:ignore

				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce
				$posted_general_setting = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST ); //phpcs:ignore
				$default_global_settings             = $this->arm_default_global_settings();
				$restrict_admin_panel                = isset( $posted_general_setting['arm_general_settings']['restrict_admin_panel'] ) ? $posted_general_setting['arm_general_settings']['restrict_admin_panel'] : 0;
				$arm_exclude_role_for_restrict_admin = ( isset( $posted_general_setting['arm_general_settings']['arm_exclude_role_for_restrict_admin'] ) && ! empty( $posted_general_setting['arm_general_settings']['arm_exclude_role_for_restrict_admin'] ) ) ? implode( ',', $posted_general_setting['arm_general_settings']['arm_exclude_role_for_restrict_admin'] ) : '';

				$new_general_settings = shortcode_atts( $default_global_settings['general_settings'], $old_global_settings['general_settings'] );

				$new_global_settings['page_settings']                                      = $old_global_settings['page_settings'];
				$new_global_settings['page_settings']['guest_page_id']                     = isset( $posted_general_setting['arm_page_settings']['guest_page_id'] ) ? intval( $posted_general_setting['arm_page_settings']['guest_page_id'] ) : 0;
				$new_global_settings['page_settings']['arm_access_page_for_restrict_site'] = ( isset( $posted_general_setting['arm_general_settings']['arm_access_page_for_restrict_site'] ) && ! empty( $posted_general_setting['arm_general_settings']['arm_access_page_for_restrict_site'] ) ) ? implode( ',', $posted_general_setting['arm_general_settings']['arm_access_page_for_restrict_site'] ) : '';

				$new_global_settings['general_settings'] = $new_general_settings;
				$new_global_settings                     = apply_filters( 'arm_before_update_access_restriction_settings', $new_global_settings, $posted_general_setting );
				update_option( 'arm_global_settings', $new_global_settings );
				$arm_access_rules->arm_update_default_access_rules();
				$response = array(
					'type' => 'success',
					'msg'  => esc_html__( 'Global Settings Saved Successfully.', 'armember-membership' ),
				);
			}
			echo arm_pattern_json_encode( $response );
			die();
		}



		function remove_loginpage_label_text( $text ) {
			$remove_txts = array(
				'username',
				'username:',
				'username *',
				'username or email',
				'username or email address',
				'username or email address *',
				'password',
				'my password:',
				'password *',
				'e-mail',
				'email address *',
				'first name *',
				'last name *',
				'email',
			);
			if ( in_array( strtolower( $text ), $remove_txts ) ) {
				$text = '';
			}
			if ( $text == 'Remember Me' ) {
				$text = 'Remember';
			}
			return $text;
		}

		function arm_remove_registration_link( $value ) {
			global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $pagenow;
			$hideRegister = isset( $this->global_settings['hide_register_link'] ) ? $this->global_settings['hide_register_link'] : 0;
			if ( $hideRegister == 1 ) {
				$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : ''; //phpcs:ignore
				if ( $pagenow == 'wp-login.php' && $action != 'register' ) {
					$value = false;
				}
			}
			return $value;
		}

		function arm_login_enqueue_assets() {
			global $arm_global_settings, $ARMemberLite;
			$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
			$general_settings    = $all_global_settings['general_settings'];
			  wp_enqueue_style( 'arm_wp_login', MEMBERSHIPLITE_URL . '/css/arm_wp_login.css', array(), MEMBERSHIPLITE_VERSION );
			if ( version_compare( $GLOBALS['wp_version'], '3.8', '<' ) ) {
				wp_enqueue_style( 'arm_login_css_lt_3.8', MEMBERSHIPLITE_URL . '/css/arm_login_lt_3.8.css', array(), MEMBERSHIPLITE_VERSION );
			}
			?>
				<script data-cfasync="false" type="text/javascript">
					jQuery.fn.outerHTML = function (s) {
						return s ? this.before(s).remove() : jQuery("<p>").append(this.eq(0).clone()).html();
					};
					jQuery(function ($) {
						jQuery('input[type=text]').each(function (e) {
							var label = jQuery(this).parents('label').text().replace('*', '');
							jQuery(this).attr('placeholder', label);
						});
						jQuery('input#user_login').attr('placeholder', 'Username').attr('autocomplete', 'off');
						jQuery('input#user_email').attr('placeholder', 'E-mail').attr('autocomplete', 'off');
						jQuery('input#user_pass').attr('placeholder', 'Password').attr('autocomplete', 'off');
						jQuery('input[type=checkbox]').each(function () {
							var input_box = jQuery(this).outerHTML();
							jQuery(this).replaceWith('<span class="arm_input_checkbox">' + input_box + '</span>');
						});
						jQuery('input[type=checkbox]').on('change', function () {
							if (jQuery(this).is(':checked')) {
								jQuery(this).closest('.arm_input_checkbox').addClass('arm_input_checked');
							} else {
								jQuery(this).closest('.arm_input_checkbox').removeClass('arm_input_checked');
							}
						});
					});
				</script>
				<?php

		}

		public function add_query_arg() {
			$args = func_get_args();
			$request_uri = !empty( $_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';//phpcs:ignore
			if ( is_array( $args[0] ) ) {
				if ( count( $args ) < 2 || false === $args[1] ) {
					$uri = sanitize_text_field( $request_uri );
				} else {
					$uri = $args[1];
				}
			} else {
				if ( count( $args ) < 3 || false === $args[2] ) {
					$uri = sanitize_text_field( $request_uri );
				} else {
					$uri = $args[2];
				}
			}
			if ( $frag = strstr( $uri, '#' ) ) {
				$uri = substr( $uri, 0, -strlen( $frag ) );
			} else {
				$frag = '';
			}

			if ( 0 === stripos( $uri, 'http://' ) ) {
				$protocol = 'http://';
				$uri      = substr( $uri, 7 );
			} elseif ( 0 === stripos( $uri, 'https://' ) ) {
				$protocol = 'https://';
				$uri      = substr( $uri, 8 );
			} else {
				$protocol = '';
			}

			if ( strpos( $uri, '?' ) !== false ) {
				list( $base, $query ) = explode( '?', $uri, 2 );
				$base                .= '?';
			} elseif ( $protocol || strpos( $uri, '=' ) === false ) {
				$base  = $uri . '?';
				$query = '';
			} else {
				$base  = '';
				$query = $uri;
			}
			wp_parse_str( $query, $qs );
			$qs = urlencode_deep( $qs ); /* This re-URL-encodes things that were already in the query string */
			if ( is_array( $args[0] ) ) {
				$kayvees = $args[0];
				$qs      = array_merge( $qs, $kayvees );
			} else {
				$qs[ $args[0] ] = $args[1];
			}
			foreach ( $qs as $k => $v ) {
				if ( $v === false ) {
					unset( $qs[ $k ] );
				}
			}
			$ret = build_query( $qs );
			$ret = trim( $ret, '?' );
			$ret = preg_replace( '#=(&|$)#', '$1', $ret );
			$ret = $protocol . $base . $ret . $frag;
			$ret = rtrim( $ret, '?' );
			$ret = esc_url_raw( $ret );
			return $ret;
		}

		public function handle_return_messages( $errors = '', $message = '' ) {
			global $wpdb, $ARMemberLite, $arm_members_class;
			$type   = 'error';
			$return = '';
			if ( ! empty( $errors ) ) {
				if ( isset( $errors ) && is_array( $errors ) && count( $errors ) > 0 ) {
					foreach ( $errors as $error ) {
						$return .= '<div>' . stripslashes( $error ) . '</div>';
					}
				}
			} elseif ( isset( $message ) && $message != '' ) {
				$type   = 'success';
				$return = $message;
			} else {
				$return = false;
			}
			return array(
				'type' => $type,
				'msg'  => $return,
			);
		}

		public function get_param( $param, $default = '', $src = 'get' ) {
			global $ARMemberLite;
			if ( strpos( $param, '[' ) ) {
				$params = explode( '[', $param );
				$param  = $params[0];
			}
			$str = isset($_POST['form']) ? stripslashes_deep( $_POST['form'] ) : ''; //phpcs:ignore
			$str = json_decode( $str, true );
			$str = !empty($str) ? $str : array();
			$str = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $str ); //phpcs:ignore

			if ( $src == 'get' ) {
				if ( isset( $_POST[ $param ] ) ) { //phpcs:ignore
					$value = stripslashes_deep($_POST[$param]); //phpcs:ignore
				} elseif ( isset( $str[ $param ] ) ) {
					$value = stripslashes_deep( $str[ $param ] );
				} elseif ( isset( $_GET[ $param ] ) ) { //phpcs:ignore
					$value = stripslashes_deep( $_GET[ $param ] ); //phpcs:ignore
				} else {
					$value = $default;
				}

				if ( ( ! isset( $_POST[ $param ] ) or ! isset( $str[ $param ] ) ) and isset( $_GET[ $param ] ) and ! is_array( $value ) ) { //phpcs:ignore
					$value = urldecode( $value );
				}
			} else {
				if ( isset( $_POST[ $param ] ) ) { //phpcs:ignore
					$value = stripslashes_deep( $_POST[ $param ] ); //phpcs:ignore
				} elseif ( isset( $str[ $param ] ) ) {
					$value = stripslashes_deep( maybe_unserialize( $str[ $param ] ) );
				} else {
					$value = $default;
				}
			}
			
			if ( isset( $params ) and is_array( $value ) and ! empty( $value ) ) {
				foreach ( $params as $k => $p ) {
					if ( ! $k or ! is_array( $value ) ) {
						continue;
					}
					$p     = trim( $p, ']' );
					$value = ( isset( $value[ $p ] ) ) ? $value[ $p ] : $default;
				}
			}
			return $value;
		}

		public function get_unique_key( $name = '', $table_name = '', $column = '', $id = 0, $num_chars = 8 ) {
			global $wpdb;
			$key = '';
			if ( ! empty( $name ) ) {
				if ( function_exists( 'sanitize_key' ) ) {
					$key = sanitize_key( $name );
				} else {
					$key = sanitize_title_with_dashes( $name );
				}
			}
			if ( empty( $key ) ) {
				$max_slug_value = pow( 36, $num_chars );
				$min_slug_value = 37;
				$key            = base_convert( wp_rand( $min_slug_value, $max_slug_value ), 10, 36 );
			}

			if ( ! empty( $table_name ) ) {
				$key_check = $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM `$table_name` WHERE `$column` = '%s' LIMIT 1", $key ) );//phpcs:ignore --Reason: $table_name is a table name
				if ( $key_check or is_numeric( $key_check ) ) {
					$suffix = 2;
					do {
						$alt_post_name = substr( $key, 0, 200 - ( strlen( $suffix ) + 1 ) ) . "$suffix";
						$key_check     = $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM `$table_name` WHERE `$column` = '%s' LIMIT 1", $alt_post_name, $id ) );//phpcs:ignore --Reason: $table_name is a table name
						$suffix++;
					} while ( $key_check || is_numeric( $key_check ) );
					$key = $alt_post_name;
				}
			}
			return $key;
		}

		public function armStringMatchWithWildcard( $source, $pattern ) {
			$pattern = preg_quote( $pattern, '/' );
			$pattern = str_replace( '\*', '.*', $pattern );
			return preg_match( '/^' . $pattern . '$/i', $source );
		}

		public function arm_find_url_match( $check_url = '', $urls = array() ) {
			global $wp, $wpdb, $arm_lite_errors;
			if ( ! empty( $check_url ) && ! empty( $urls ) ) {
				if ( ! preg_match( '#^http(s)?://#', $check_url ) ) {
					$check_url = 'http://' . $check_url;
				}
				$parse_check_url          = wp_parse_url( $check_url );
				$parse_check_url['path']  = ( isset( $parse_check_url['path'] ) ) ? $parse_check_url['path'] : '';
				$parse_check_url['query'] = ( isset( $parse_check_url['query'] ) ) ? $parse_check_url['query'] : '';
				foreach ( $urls as $url ) {
					$check_wildcard = explode( '*', $url );
					$wildcard_count = substr_count( $url, '*' );
					if ( $wildcard_count > 0 ) {
						if ( $this->armStringMatchWithWildcard( $check_url, $url ) ) {
							return true;
						}
						if ( $this->armStringMatchWithWildcard( $check_url, $url . '/' ) ) {
							return true;
						}
					} else {
						if ( ! preg_match( '/^http(s)?:\/\//', $url ) ) {
							$url = 'http://' . $url;
						}
						$parse_url          = wp_parse_url( $url );
						$parse_url['path']  = ( isset( $parse_url['path'] ) ) ? $parse_url['path'] : '';
						$parse_url['query'] = ( isset( $parse_url['query'] ) ) ? $parse_url['query'] : '';
						/* Compare URL Details. */
						$diff = array_diff( $parse_check_url, $parse_url );
						if ( $parse_check_url['path'] == $parse_url['path'] ) {
							if ( isset( $parse_check_url['query'] ) || isset( $parse_url['query'] ) ) {
								if ( $parse_check_url['query'] == $parse_url['query'] ) {
									return true;
								} else {
									continue;
								}
							}
							return true;
						}
					}
				}
			}
			return false;
		}

		/**
		 * Set Email Content Type
		 */
		public function arm_mail_content_type() {
			return 'text/html';
		}

		public function arm_mailer( $temp_slug, $user_id, $admin_template_id = '', $follower_id = '' ) {
			global $wpdb, $ARMemberLite, $arm_slugs, $arm_email_settings;
			if ( ! empty( $user_id ) && $user_id != 0 ) {
				$user_info = get_user_by( 'id', $user_id );
				$to_user   = $user_info->user_email;
				$to_admin  = get_option( 'admin_email' );

				$all_email_settings = $arm_email_settings->arm_get_all_email_settings();
		
				$email_css = '<style>
				table, th, td {
					border: 1px solid grey;
					border-collapse: collapse;
				}
				table {
					table-layout: auto;
				}
				th, td {
					padding: 5px;
					text-align: left;
				}
				</style>';
		
				if ( ! empty( $temp_slug ) ) {
					$template = $arm_email_settings->arm_get_email_template( $temp_slug );
					if ( $template->arm_template_status == '1' ) {
						$message = $this->arm_filter_email_with_user_detail( $template->arm_template_content, $user_id, 0, $follower_id );
						$subject = $this->arm_filter_email_with_user_detail( $template->arm_template_subject, $user_id, 0, $follower_id );
						$message = $email_css . $message;
						$user_send_mail = $this->arm_wp_mail( '', $to_user, $subject, $message );
					}
				}
				if ( ! empty( $admin_template_id ) ) {
					$admin_template = $arm_email_settings->arm_get_single_email_template( $admin_template_id );
					if ( $admin_template->arm_template_status == '1' ) {
						$message_admin = $this->arm_filter_email_with_user_detail( $admin_template->arm_template_content, $user_id, 0, $follower_id );
						$subject_admin = $this->arm_filter_email_with_user_detail( $admin_template->arm_template_subject, $user_id, 0, $follower_id );
						$message_admin = $email_css . $message_admin;
						$admin_send_mail = $this->arm_send_message_to_armember_admin_users( $to_user, $subject_admin, $message_admin );
					}
				}
			}
		}

		public function arm_send_message_to_armember_admin_users( $from = '', $subject = '', $message = '' ) {
			global $arm_email_settings, $arm_global_settings;
			$all_email_settings = $arm_email_settings->arm_get_all_email_settings();
			$admin_email        = ( ! empty( $all_email_settings['arm_email_admin_email'] ) ) ? $all_email_settings['arm_email_admin_email'] : get_option( 'admin_email' );

			$exploded_admin_email = array();
			if ( strpos( $admin_email, ',' ) !== false ) {
				$exploded_admin_email = explode( ',', trim( $admin_email ) );
			}

			if ( isset( $exploded_admin_email ) && ! empty( $exploded_admin_email ) ) {
				foreach ( $exploded_admin_email as $admin_email_from_array ) {
					if ( $admin_email_from_array != '' ) {
						$admin_email_from_array = apply_filters( 'arm_admin_email', trim( $admin_email_from_array ) );

						$admin_send_mail = $arm_global_settings->arm_wp_mail( $from, $admin_email_from_array, $subject, $message );
					}
				}
			} else {
				if ( $admin_email ) {
					$admin_email     = apply_filters( 'arm_admin_email', $admin_email );
					$admin_send_mail = $arm_global_settings->arm_wp_mail( $from, $admin_email, $subject, $message );
				}
			}

			return $admin_send_mail;
		}

		public function arm_wp_mail( $from, $recipient, $subject, $message, $attachments = array() ) {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs, $arm_email_settings, $arm_plain_text, $wp_version;
			remove_all_actions( 'phpmailer_init' );
			$return                  = false;
			$emailSettings           = $arm_email_settings->arm_get_all_email_settings();
			$arm_mail_authentication = ( isset( $emailSettings['arm_mail_authentication'] ) ) ? $emailSettings['arm_mail_authentication'] : '1';
			$email_server            = ( ! empty( $emailSettings['arm_email_server'] ) ) ? $emailSettings['arm_email_server'] : 'wordpress_server';
			$from_name               = ( ! empty( $emailSettings['arm_email_from_name'] ) ) ? stripslashes_deep( $emailSettings['arm_email_from_name'] ) : wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			$from_email              = ( ! empty( $emailSettings['arm_email_from_email'] ) ) ? $emailSettings['arm_email_from_email'] : get_option( 'admin_email' );
			$content_type            = ( @$arm_plain_text ) ? 'text/plain' : 'text/html';
			$from_name               = $from_name;
			$reply_to                = ( ! empty( $from ) ) ? $from : $from_email;
			$message_html = "<html>
            <head>";
            $message_html .= '<style>
						table, th, td {
							border: 1px solid grey;
							border-collapse: collapse;
						}
						table {
							table-layout: auto;
						}
						th, td {
							padding: 5px;
							text-align: left;
						}
					</style>';
            
                $message_html .= "</head><body>";
            $message_html .= $message;           
            $message_html .= "</body></html>";
            $message = $message_html;		
			$headers = array();
			 //$headers[] = 'From: "' . $from_name . '" <' . $reply_to . '>'; //changes from v3.0
			 $headers[] = 'From: "' . $from_name . '" <' . $from_email . '>';
			 $headers[] = 'Reply-To: ' . $reply_to;
			 $headers[] = 'Content-Type: ' . $content_type . '; charset="' . get_option('blog_charset') . '"';
			 /* Filter Email Subject & Message */
			 $subject = wp_specialchars_decode(strip_tags(stripslashes($subject)), ENT_QUOTES);
			 $message = do_shortcode($message);
			 $message = $email_css . stripslashes($message);
			 $message = wordwrap(stripslashes($message), 70, "\r\n");
			 if (@$arm_plain_text) {
				 $message = wp_specialchars_decode(strip_tags($message), ENT_QUOTES);
			 }

			$subject   = apply_filters( 'arm_email_subject', $subject );
			$message   = apply_filters( 'arm_change_email_content', $message );
			$recipient = apply_filters( 'arm_email_recipients', $recipient );
			$headers   = apply_filters( 'arm_email_header', $headers, $recipient, $subject );
			remove_filter( 'wp_mail_from', 'bp_core_email_from_address_filter' );
			remove_filter( 'wp_mail_from_name', 'bp_core_email_from_name_filter' );
			if ( version_compare( $wp_version, '5.5', '<' ) ) {
				require_once ABSPATH . WPINC . '/class-phpmailer.php';
				require_once ABSPATH . WPINC . '/class-smtp.php';
				$armPMailer = new PHPMailer();
			} else {
				require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
				require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
				require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
				$armPMailer = new PHPMailer\PHPMailer\PHPMailer();
			}

			do_action( 'arm_before_send_email_notification', $from, $recipient, $subject, $message, $attachments );
			/* Character Set of the message. */
			$armPMailer->CharSet   = 'UTF-8';
			$armPMailer->SMTPDebug = 0;
			/* $armPMailer->Debugoutput = 'html'; */
			$email_server_name = "WordPress Server";
			if ( $email_server == 'smtp_server' )
			{
				$email_server_name == 'SMTP Server';
			}
			if($email_server == 'phpmailer')
			{
				$email_server_name == 'PHP Mailer';
			}

			if ( $email_server == 'smtp_server' ) {
				$armPMailer->isSMTP();
				$armPMailer->Host     = isset( $emailSettings['arm_mail_server'] ) ? $emailSettings['arm_mail_server'] : '';
				$armPMailer->SMTPAuth = ( $arm_mail_authentication == 1 ) ? true : false;
				$armPMailer->Username = isset( $emailSettings['arm_mail_login_name'] ) ? $emailSettings['arm_mail_login_name'] : '';
				$armPMailer->Password = isset( $emailSettings['arm_mail_password'] ) ? $emailSettings['arm_mail_password'] : '';
				if ( isset( $emailSettings['arm_smtp_enc'] ) && ! empty( $emailSettings['arm_smtp_enc'] ) && $emailSettings['arm_smtp_enc'] != 'none' ) {
					$armPMailer->SMTPSecure = $emailSettings['arm_smtp_enc'];
				}
				if ( $emailSettings['arm_smtp_enc'] == 'none' ) {
					$armPMailer->SMTPAutoTLS = false;
				}

				$armPMailer->Port = isset( $emailSettings['arm_mail_port'] ) ? $emailSettings['arm_mail_port'] : '';
			} else {
				$armPMailer->isMail();
			}
			$armPMailer->setFrom( $from_email, $from_name );
			$armPMailer->addReplyTo( $reply_to, $from_name );
			$armPMailer->addAddress( $recipient );
			if ( isset( $attachments ) && ! empty( $attachments ) && is_array($attachments) ) {
				foreach ( $attachments as $attachment ) {
					$armPMailer->addAttachment( $attachment );
				}
			}

			$armPMailer->isHTML( true );
			$armPMailer->Subject = $subject;
			$armPMailer->Body    = $message;
			if ( @$arm_plain_text ) {
				$armPMailer->AltBody = $message;
			}
			if ( MEMBERSHIPLITE_DEBUG_LOG == true ) {
				if ( MEMBERSHIPLITE_DEBUG_LOG_TYPE == 'ARM_ALL' || MEMBERSHIPLITE_DEBUG_LOG_TYPE == 'ARM_MAIL' ) {
					global $arm_case_types, $wpdb;
					$arm_case_types['mail']['protected'] = true;
					$arm_case_types['mail']['type']      = '';
					$arm_case_types['mail']['message']   = ' Email Server : ' . $email_server . ' <br/> Email Recipient : ' . $recipient . ' <br/> Message Content : ' . $message;
					$ARMemberLite->arm_debug_response_log( 'arm_wp_mail', $arm_case_types, array(), $wpdb->last_query, true );
				}
			}
			/* Send Email */
			if ( $email_server == 'smtp_server' || $email_server == 'phpmailer' ) {
				if ( $armPMailer->send() ) {
					$return = true;
				}
			} else {
				add_filter( 'wp_mail_content_type', array( $this, 'arm_mail_content_type' ) );
				if ( ! wp_mail( $recipient, $subject, $message, $headers, $attachments ) ) {
					if ( $armPMailer->send() ) {
						$return = true;
					}
				} else {
					$return = true;
				}
				remove_filter( 'wp_mail_content_type', array( $this, 'arm_mail_content_type' ) );
			}
            /* arm_email_log_entry */
            $is_mail_send = ($return == true ) ? 'Yes' : 'No';
			$arm_mail_message = is_object($message) ? $arm_gmail_message_content : $message ;
            $arm_email_content  = '';
            $arm_email_content .= 'Email Sent Successfully: '.$is_mail_send.', To Email: '.$recipient.', From Email: '.$from. ', Email Server:'.$email_server_name.'{ARMNL}';   
            $arm_email_content .= 'Subject: '.$subject.'{ARMNL}';
            $arm_email_content .= 'Content: {ARMNL}'.$arm_mail_message.'{ARMNL}';

            if(!empty($arm_attachment_urls))
            {
                $arm_attachment_urls = rtrim($arm_attachment_urls, ',');
                $arm_email_content .= '{ARMNL}Attachment URL(s): {ARMNL}'.$arm_attachment_urls.'{ARMNL}';
            }
            do_action('arm_general_log_entry','email','send email detail','armember', $arm_email_content);
			do_action( 'arm_after_send_email_notification', $from, $recipient, $subject, $message, $attachments );
			return $return;
		}

		public function arm_filter_email_with_user_detail( $content, $user_id = 0, $plan_id = 0, $follower_id = 0, $key = '' ) {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs, $arm_payment_gateways, $arm_email_settings, $arm_global_settings;
			$user_info          = get_user_by( 'id', $user_id );
			$f_displayname      = '';
			$u_plan_description = '';
			if ( $follower_id != 0 && ! empty( $follower_id ) ) {
				$follower_info = get_user_by( 'id', $follower_id );
				$follower_name = $follower_info->first_name . ' ' . $follower_info->last_name;
				if ( empty( $follower_info->first_name ) && empty( $follower_info->last_name ) ) {
					$follower_name = $follower_info->user_login;
				}
				$f_displayname = "<a href='" . $this->arm_get_user_profile_url( $follower_id ) . "'>" . $follower_name . '</a>';
			}
			if ( $user_id != 0 && ! empty( $user_info ) ) {
				$u_email             = $user_info->user_email;
				$u_displayname       = $user_info->display_name;
				$u_username          = $user_info->user_login;
				$u_fname             = $user_info->first_name;
				$u_lname             = $user_info->last_name;
				$u_grace_period_days = 0;
				$u_trial_amount      = 0;
				$u_plan_discount     = 0;
				$u_payable_amount    = 0;
				$now                 = current_time( 'timestamp' ); // or your date as well

				$arm_is_user_in_grace    = 0;
				$arm_user_grace_end_date = '';
				$plan_detail             = array();
				$user_plans              = get_user_meta( $user_id, 'arm_user_plan_ids', true );
				$user_plan               = 0;
				$using_gateway           = '';
				$payment_cycle           = 0;
				if ( ! empty( $plan_id ) ) {
					$user_plan = $plan_id;
					$planData  = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
					if ( ! empty( $planData ) ) {
						$arm_is_user_in_grace    = ( isset( $planData['arm_is_user_in_grace'] ) && ! empty( $planData['arm_is_user_in_grace'] ) ) ? $planData['arm_is_user_in_grace'] : 0;
						$arm_user_grace_end_date = $planData['arm_grace_period_end'];
						$plan_detail             = $planData['arm_current_plan_detail'];
						$using_gateway           = $planData['arm_user_gateway'];
						$payment_cycle           = $planData['arm_payment_cycle'];
						$expire_time             = $planData['arm_expire_plan'];
					}
				}

				if ( $arm_is_user_in_grace == 1 ) {
					$datediff            = $arm_user_grace_end_date - $now;
					$u_grace_period_days = floor( $datediff / ( 60 * 60 * 24 ) );
				}
				$activation_key = get_user_meta( $user_id, 'arm_user_activation_key', true );
				$login_page_id  = isset( $this->global_settings['login_page_id'] ) ? $this->global_settings['login_page_id'] : 0;
				if ( $login_page_id == 0 ) {
					$arm_login_page_url = wp_login_url();
				} else {

					$arm_login_page_url = $this->arm_get_permalink( '', $login_page_id );
				}

				 $arm_login_page_url = $arm_global_settings->add_query_arg( 'arm-key', urlencode( $activation_key ), $arm_login_page_url );
				 $arm_login_page_url = $arm_global_settings->add_query_arg( 'email', urlencode( $u_email ), $arm_login_page_url );

				$validate_url = $arm_login_page_url;
				$pending      = '';

				$login_url    = $this->arm_get_permalink( '', $login_page_id );
				$profile_link = $this->arm_get_user_profile_url( $user_info->ID );
				$blog_name    = get_bloginfo( 'name' );
				$blog_url     = ARMLITE_HOME_URL;
				$arm_currency = $arm_payment_gateways->arm_get_global_currency();

				$all_email_settings = $arm_email_settings->arm_get_all_email_settings();
				$admin_email        = ( ! empty( $all_email_settings['arm_email_admin_email'] ) ) ? $all_email_settings['arm_email_admin_email'] : get_option( 'admin_email' );

				$u_plan_name       = '-';
				$u_plan_amount     = '-';
				$u_plan_discount   = '-';
				$u_payment_type    = '-';
				$u_payment_gateway = '-';
				$u_transaction_id  = '-';
				$plan_expire       = '';

				if ( ! empty( $plan_detail ) ) {
					$plan_detail = maybe_unserialize( $plan_detail );
					if ( ! empty( $plan_detail ) ) {
						$planObj = new ARM_Plan_Lite( 0 );
						$planObj->init( (object) $plan_detail );
					} else {
						$planObj = new ARM_Plan_Lite( $user_plan );
					}
					$u_plan_name        = $planObj->name;
					$u_plan_description = $planObj->description;

					if ( $planObj->is_recurring() ) {
						$plan_data     = $planObj->prepare_recurring_data( $payment_cycle );
						$u_plan_amount = $plan_data['amount'];
						$u_plan_amount = $arm_payment_gateways->arm_amount_set_separator( $arm_currency, $u_plan_amount );
					} else {
						$u_plan_amount = $arm_payment_gateways->arm_amount_set_separator( $arm_currency, $planObj->amount );
					}

					$plan_expire = esc_html__( 'Never Expires', 'armember-membership' );

					if ( ! empty( $expire_time ) ) {
						$date_format = $this->arm_get_wp_date_format();
						$plan_expire = date_i18n( $date_format, $expire_time );
					}

					if ( ! empty( $using_gateway ) ) {
						$u_payment_gateway = $arm_payment_gateways->arm_gateway_name_by_key( $using_gateway );
					}
					// if ($planObj->has_trial_period()) {
					// $planTrialOpts = isset($planObj->options['trial']) ? $planObj->options['trial'] : array();
					// $u_plan_amount = $arm_payment_gateways->arm_amount_set_separator($arm_currency, $planTrialOpts['amount']);
					// }
					if ( $planObj->is_paid() ) {
						if ( $planObj->is_lifetime() ) {
							$u_payment_type = esc_html__( 'Life Time', 'armember-membership' );
						} else {
							if ( $planObj->is_recurring() ) {
								$u_payment_type = esc_html__( 'Subscription', 'armember-membership' );
							} else {
								$u_payment_type = esc_html__( 'One Time', 'armember-membership' );
							}
						}
					} else {
						$u_payment_type = esc_html__( 'Free', 'armember-membership' );
					}

					$selectColumns = '`arm_log_id`, `arm_user_id`, `arm_transaction_id`, `arm_is_trial`, `arm_amount`, `arm_extra_vars`';
					$where_bt      = '';
					if ( $using_gateway == 'bank_transfer' ) {
						/* Change Log Table For Bank Transfer Method */
						$where_bt = " AND arm_payment_gateway='bank_transfer'";
					}

					$armLogTable    = $ARMemberLite->tbl_arm_payment_log;
					$selectColumns .= ', `arm_token`';

					$log_detail = $wpdb->get_row( $wpdb->prepare("SELECT ".$selectColumns." FROM `".$armLogTable."` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d {$where_bt} ORDER BY `arm_log_id` DESC",$user_id,$user_plan) );//phpcs:ignore --Reason: $armLogTable is a table name. False Positive alarm
					if ( ! empty( $log_detail ) ) {
						$u_transaction_id = $log_detail->arm_transaction_id;

						$extravars = maybe_unserialize( $log_detail->arm_extra_vars );

						if ( $using_gateway == 'bank_transfer' ) {
							if ( isset( $extravars['coupon'] ) ) {
								$u_plan_discount = isset( $extravars['coupon']['amount'] ) ? $extravars['coupon']['amount'] : 0;
							} else {
								$u_plan_discount = $log_detail->arm_coupon_discount . $log_detail->arm_coupon_discount_type;
							}
						} else {
							$u_plan_discount = isset( $extravars['coupon']['amount'] ) ? $extravars['coupon']['amount'] : 0;
						}

						if ( ! empty( $log_detail->arm_is_trial ) && $log_detail->arm_is_trial == 1 ) {
							$u_trial_amount = isset( $extravars['trial']['amount'] ) ? $extravars['trial']['amount'] : 0;

						}
						$u_payable_amount = $log_detail->arm_amount;

					}
				}

				if ( empty( $user_plans ) ) {
					$arm_user_entry_id = get_user_meta( $user_id, 'arm_entry_id', true );
					if ( isset( $arm_user_entry_id ) && $arm_user_entry_id != '' ) {
						$armentryTable            = $ARMemberLite->tbl_arm_entries;
						$arm_user_entry_data_ser  = $wpdb->get_var( $wpdb->prepare("SELECT `arm_entry_value` FROM `".$armentryTable."` WHERE `arm_entry_id` =%d",$arm_user_entry_id) );//phpcs:ignore --Reason: $armentryTable is a table name. False Positive Alarm
						$arm_user_entry_data      = maybe_unserialize( $arm_user_entry_data_ser );
						$arm_user_payment_gateway = '';

						if ( isset( $arm_user_entry_data['arm_front_gateway_skin_type'] ) && $arm_user_entry_data['arm_front_gateway_skin_type'] == 'dropdown' ) {

							$arm_user_payment_gateway = $arm_user_entry_data['_payment_gateway'];
							$arm_plan_skin_type       = $arm_user_entry_data['arm_front_plan_skin_type'];

								$arm_subscription_plan = isset( $arm_user_entry_data['subscription_plan'] ) ? $arm_user_entry_data['subscription_plan'] : '';

						} elseif ( isset( $arm_user_entry_data['arm_front_gateway_skin_type'] ) && $arm_user_entry_data['arm_front_gateway_skin_type'] == 'radio' ) {

							$arm_user_payment_gateway = $arm_user_entry_data['payment_gateway'];
							$arm_plan_skin_type       = $arm_user_entry_data['arm_front_plan_skin_type'];

								$arm_subscription_plan = isset( $arm_user_entry_data['subscription_plan'] ) ? $arm_user_entry_data['subscription_plan'] : '';

						}

						if ( $arm_user_payment_gateway == 'bank_transfer' ) {

							$userplanObj        = new ARM_Plan_Lite( $arm_subscription_plan );
							$u_plan_name        = $userplanObj->name;
							$u_plan_description = $userplanObj->description;
							$u_payment_gateway  = $arm_payment_gateways->arm_gateway_name_by_key( 'bank_transfer' );
							$plan_expire        = '';
							$u_trial_amount     = 0;
							$u_plan_discount    = 0;
							$u_payable_amount   = 0;

							if ( $userplanObj->is_recurring() ) {
								$plan_data     = $userplanObj->prepare_recurring_data( $payment_cycle );
								$u_plan_amount = $plan_data['amount'];
								$u_plan_amount = $arm_payment_gateways->arm_amount_set_separator( $arm_currency, $u_plan_amount );
							} else {
								$u_plan_amount = $arm_payment_gateways->arm_amount_set_separator( $arm_currency, $userplanObj->amount );
							}

							if ( $userplanObj->has_trial_period() ) {
								$planTrialOpts = isset( $userplanObj->options['trial'] ) ? $userplanObj->options['trial'] : array();
								$u_plan_amount = $arm_payment_gateways->arm_amount_set_separator( $arm_currency, $planTrialOpts['amount'] );
							}

							if ( $userplanObj->is_paid() ) {
								if ( $userplanObj->is_lifetime() ) {
									$u_payment_type = esc_html__( 'Life Time', 'armember-membership' );
								} else {
									if ( $userplanObj->is_recurring() ) {
										$u_payment_type = esc_html__( 'Subscription', 'armember-membership' );
									} else {
										$u_payment_type = esc_html__( 'One Time', 'armember-membership' );
									}
								}
							}

							$selectColumns = '`arm_transaction_id`, `arm_extra_vars`, `arm_is_trial`, `arm_amount`';

							$armLogTable = $ARMemberLite->tbl_arm_payment_log;

							$log_detail = $wpdb->get_row( $wpdb->prepare("SELECT ".$selectColumns." FROM `".$armLogTable."` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND arm_payment_gateway=%s ORDER BY `arm_log_id` DESC",$user_id,$arm_subscription_plan,'bank_transfer') );//phpcs:ignore --Reason: $armLogTable is a table name. False positive alarm.
							if ( ! empty( $log_detail ) ) {
								$u_transaction_id = $log_detail->arm_transaction_id;
								$u_payable_amount = $log_detail->arm_amount;

								$extravars = maybe_unserialize( $log_detail->arm_extra_vars );

								if ( ! empty( $log_detail->arm_is_trial ) && $log_detail->arm_is_trial == 1 ) {
									$u_trial_amount = isset( $extravars['trial']['amount'] ) ? $extravars['trial']['amount'] : 0;

								}
							}
						}
					}
				}

				if ( $key != '' && ! empty( $key ) ) {

					$change_password_page_id = isset( $arm_global_settings->global_settings['change_password_page_id'] ) ? $arm_global_settings->global_settings['change_password_page_id'] : 0;
					if ( $change_password_page_id == 0 ) {
						$arm_reset_password_link = network_site_url( 'wp-login.php?action=rp&key=' . rawurlencode( $key ) . '&login=' . rawurlencode( $u_username ), 'login' );
					} else {
						$arm_change_password_page_url = $arm_global_settings->arm_get_permalink( '', $change_password_page_id );

						$arm_change_password_page_url = $arm_global_settings->add_query_arg( 'action', 'armrp', $arm_change_password_page_url );
						$arm_change_password_page_url = $arm_global_settings->add_query_arg( 'key', rawurlencode( $key ), $arm_change_password_page_url );
						$arm_change_password_page_url = $arm_global_settings->add_query_arg( 'login', rawurlencode( $u_username ), $arm_change_password_page_url );
						$arm_reset_password_link      = $arm_change_password_page_url;
					}

					$varification_key = get_user_meta( $user_id, 'arm_user_activation_key', true );
					$user_status      = arm_get_member_status( $user_id );
					if ( $user_status == 3 ) {
						$rp_link = $arm_global_settings->add_query_arg( 'varify_key', rawurlencode( $varification_key ), $arm_reset_password_link );
					}

					$content = str_replace( '{ARM_RESET_PASSWORD_LINK}', $arm_reset_password_link, $content );
				} else {

					$content = str_replace( '{ARM_RESET_PASSWORD_LINK}', '', $content );
				}

				$content = str_replace( '{ARM_USER_ID}', $user_id, $content );
				$content = str_replace( '{ARM_USERNAME}', $u_username, $content );
				$content = str_replace( '{ARM_FIRST_NAME}', $u_fname, $content );
				$content = str_replace( '{ARM_LAST_NAME}', $u_lname, $content );
				$content = str_replace( '{ARM_NAME}', $u_displayname, $content );
				$content = str_replace( '{ARM_EMAIL}', $u_email, $content );
				$content = str_replace( '{ARM_ADMIN_EMAIL}', $admin_email, $content );
				$content = str_replace( '{ARM_BLOGNAME}', $blog_name, $content );
				$content = str_replace( '{ARM_BLOG_URL}', $blog_url, $content );
				$content = str_replace( '{ARM_VALIDATE_URL}', $validate_url, $content );
				$content = str_replace( '{ARM_CHANGE_PASSWORD_CONFIRMATION_URL}', $pending, $content );
				$content = str_replace( '{ARM_PENDING_REQUESTS_URL}', $pending, $content );
				$content = str_replace( '{ARM_PROFILE_FIELDS}', $pending, $content );
				$content = str_replace( '{ARM_PROFILE_LINK}', $profile_link, $content );
				$content = str_replace( '{ARM_LOGIN_URL}', $login_url, $content );
				$content = str_replace( '{ARM_PLAN}', $u_plan_name, $content );
				$content = str_replace( '{ARM_PLAN_DESCRIPTION}', $u_plan_description, $content );
				$content = str_replace( '{ARM_PLAN_AMOUNT}', $u_plan_amount, $content );
				$content = str_replace( '{ARM_PLAN_DISCOUNT}', $u_plan_discount, $content );
				$content = str_replace( '{ARM_TRIAL_AMOUNT}', $u_trial_amount, $content );
				$content = str_replace( '{ARM_PAYABLE_AMOUNT}', $u_payable_amount, $content );
				$content = str_replace( '{ARM_PAYMENT_TYPE}', $u_payment_type, $content );
				$content = str_replace( '{ARM_PAYMENT_GATEWAY}', $u_payment_gateway, $content );
				$content = str_replace( '{ARM_TRANSACTION_ID}', $u_transaction_id, $content );
				$content = str_replace( '{ARM_GRACE_PERIOD_DAYS}', $u_grace_period_days, $content );
				$Content = str_replace( '{ARM_CURRENCY}', $arm_currency, $content );
				$Content = str_replace( '{ARM_PLAN_EXPIRE}', $plan_expire, $content );

				$networ_name = get_site_option( 'site_name' );
				$networ_url  = get_site_option( 'siteurl' );

				$Content = str_replace( '{ARM_MESSAGE_NETWORKNAME}', $networ_name, $content );

				$Content = str_replace( '{ARM_MESSAGE_NETWORKURL}', $networ_url, $content );

				/* Content replace for user meta */
				$matches = array();
				preg_match_all( "/\b(\w*ARM_USERMETA_\w*)\b/", $content, $matches, PREG_PATTERN_ORDER );
				$matches = $matches[0];
				if ( ! empty( $matches ) ) {
					foreach ( $matches as $mat_var ) {
						$key      = str_replace( 'ARM_USERMETA_', '', $mat_var );
						$meta_val = '';
						if ( ! empty( $key ) ) {
							$meta_val = do_shortcode('[arm_usermeta id='.$user_id.' meta="'.$key.'"]',true);
							/* $meta_val = get_user_meta( $user_id, $key, true );
							if ( is_array( $meta_val ) ) {
								$meta_val = implode( ',', $meta_val );
							} */
						}
						$content = str_replace( '{' . $mat_var . '}', $meta_val, $content );
					}
				}
			}

			// $content = nl2br( $content );
			$arm_is_html = $this->arm_is_html($content);
            if( !$arm_is_html )
            {
                $content = nl2br( $content );
            }
			$content = apply_filters( 'arm_change_email_content_with_user_detail', $content, $user_id );
			return $content;
		}

		function arm_is_html( $content )
        {
            return preg_match( "/<[^<]+>/", $content, $m ) != 0;
        }

		function arm_get_wp_pages( $args = '', $columns = array() ) {
			 $defaults      = array(
				 'depth'                 => 0,
				 'child_of'              => 0,
				 'selected'              => 0,
				 'echo'                  => 1,
				 'name'                  => 'page_id',
				 'id'                    => '',
				 'show_option_none'      => 'Select Page',
				 'show_option_no_change' => '',
				 'option_none_value'     => '',
				 'class'                 => '',
				 'required'              => false,
				 'required_msg'          => false,
			 );
			 $arm_r         = wp_parse_args( $args, $defaults );
			 $arm_pages     = get_pages( $arm_r );
			 $arm_new_pages = array();
			 if ( ! empty( $arm_pages ) ) {
				 if ( ! empty( $columns ) ) {
					 $n = 0;
					 foreach ( $arm_pages as $page ) {
						 foreach ( $columns as $column ) {
							 $arm_new_pages[ $n ][ $column ] = $page->$column;
						 }
						 $n++;
					 }
				 } else {
					 $arm_new_pages = $arm_pages;
				 }
			 }
			 return $arm_new_pages;
		}

		function arm_wp_dropdown_pages( $args = '', $dd_class = '' ) {
			$defaults = array(
				'depth'                 => 0,
				'child_of'              => 0,
				'selected'              => 0,
				'echo'                  => 1,
				'name'                  => 'page_id',
				'id'                    => '',
				'show_option_none'      => 'Select Page',
				'show_option_no_change' => '',
				'option_none_value'     => '',
				'class'                 => '',
				'required'              => false,
				'required_msg'          => false,
			);
			$r        = wp_parse_args( $args, $defaults );
			$pages    = get_pages( $r );
			$output   = '';
			if ( empty( $r['id'] ) ) {
				$r['id'] = $r['name'];
			}

			$pageIds = array();
			if ( ! empty( $pages ) ) {
				$pageIds = array();
				foreach ( $pages as $p ) {
					$pageIds[] = $p->ID;
				}
			}
			if ( ! in_array( $r['selected'], $pageIds ) ) {
				$r['selected'] = '';
			}

			$required     = ( $r['required'] ) ? 'required="required"' : '';
			$required_msg = ( $r['required_msg'] ) ? 'data-msg-required="' . esc_attr($r['required_msg']) . '"' : '';
			$output      .= "<input type='hidden'  name='" . esc_attr( $r['name'] ) . "' id='" . esc_attr( $r['id'] ) . "' class='" . esc_attr($r['class']) . "' value='" . esc_attr($r['selected']) . "' $required $required_msg/>";
			$output      .= "<dl class='arm_selectbox column_level_dd arm_width_100_pct arm_margin_top_12'>";
			$output      .= "<dt class='" . esc_attr($dd_class) . "'><span>" . ( ! empty( $r['selected'] ) ? esc_attr( get_the_title( $r['selected'] ) ) : 'Select Page' ) . "</span><input type='text' style='display:none;' value='" . ( ! empty( $r['selected'] ) ? esc_attr( get_the_title( $r['selected'] ) ) : 'Select Page' ) . "' class='arm_autocomplete'  /><i class='armfa armfa-caret-down armfa-lg'></i></dt>";
			$output      .= '<dd>';
			$output      .= "<ul data-id='" . esc_attr( $r['id'] ) . "'>";

			if ( $r['show_option_no_change'] ) {

				$output .= "<li data-label='" . esc_attr($r['show_option_no_change']) . "' data-value='-1'>" . $r['show_option_no_change'] . '</li>';
			}
			if ( $r['show_option_none'] ) {
				$output .= "<li data-label='" . esc_attr($r['show_option_none']) . "' data-value='" . esc_attr( $r['option_none_value'] ) . "'>" . esc_html($r['show_option_none']) . '</li>';
			}
			if ( ! empty( $pages ) ) {
				foreach ( $pages as $p ) {
					$is_protected = 0;
					$item_plans   = get_post_meta( $p->ID, 'arm_access_plan' );
					$item_plans   = ( ! empty( $item_plans ) ) ? $item_plans : array();

					if ( count( $item_plans ) == 0 ) {
						$is_protected = 0;
					} else {
						$is_protected = 1;
					}
					$output .= "<li data-label='" . esc_attr($p->post_title) . "' data-value='" . esc_attr( $p->ID ) . "' data-protected='" . esc_attr($is_protected) . "' >" . esc_html($p->post_title) . '</li>';
				}
			}
			$output .= '</ul>';
			$output .= '</dd>';
			$output .= '</dl>';

			$html = apply_filters( 'arm_wp_dropdown_pages', $output );

			if ( $r['echo'] ) {
				echo $html; //phpcs:ignore
			}
			return $html;
		}

		function arm_get_wp_date_format() {
			global $wp, $wpdb;
			if ( is_multisite() ) {
				$wp_format_date = get_option( 'date_format' );
			} else {
				$wp_format_date = get_site_option( 'date_format' );
			}
			if ( empty( $wp_format_date ) ) {
				$date_format = 'M d, Y';
			} else {
				$date_format = $wp_format_date;
			}
			return $date_format;
		}

		function arm_get_wp_date_time_format() {
			global $wp, $wpdb;

			if ( is_multisite() ) {
				$wp_date_time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
			} else {
				$wp_date_time_format = get_site_option( 'date_format' ) . ' ' . get_site_option( 'time_format' );
			}

			if ( empty( $wp_date_time_format ) ) {
				$date_time_format = 'M d, Y H:i:s';
			} else {
				$date_time_format = $wp_date_time_format;
			}
			return $date_time_format;
		}

		function arm_time_elapsed( $ptime ) {
			$etime = current_time( 'timestamp' ) - $ptime;
			if ( $etime < 1 ) {
				return esc_html__( 'now!', 'armember-membership' );
			}
			$a = array(
				12 * 30 * 24 * 60 * 60 => esc_html__( 'year', 'armember-membership' ),
				30 * 24 * 60 * 60      => esc_html__( 'month', 'armember-membership' ),
				24 * 60 * 60           => esc_html__( 'day', 'armember-membership' ),
				60 * 60                => esc_html__( 'hour', 'armember-membership' ),
				60                     => esc_html__( 'minute', 'armember-membership' ),
				1                      => esc_html__( 'second', 'armember-membership' ),
			);
			foreach ( $a as $secs => $str ) {
				$d = $etime / $secs;
				if ( $d >= 1 ) {
					$r = round( $d );
					return $r . ' ' . $str . ( $r > 1 ? 's' : '' ) . esc_html__( ' ago', 'armember-membership' );
				}
			}
			return '-';
		}

		function arm_time_remaining( $rtime ) {
			$etime = $rtime - current_time( 'timestamp' );
			if ( $etime < 1 ) {
				return esc_html__( 'now!', 'armember-membership' );
			}
			$a = array(
				12 * 30 * 24 * 60 * 60 => esc_html__( 'year', 'armember-membership' ),
				30 * 24 * 60 * 60      => esc_html__( 'month', 'armember-membership' ),
				24 * 60 * 60           => esc_html__( 'day', 'armember-membership' ),
				60 * 60                => esc_html__( 'hour', 'armember-membership' ),
				60                     => esc_html__( 'minute', 'armember-membership' ),
				1                      => esc_html__( 'second', 'armember-membership' ),
			);
			foreach ( $a as $secs => $str ) {
				$d = $etime / $secs;
				if ( $d >= 1 ) {
					$r = round( $d );
					return $r . ' ' . $str . ( $r > 1 ? 's' : '' );
				}
			}
			return '-';
		}

		function arm_get_remaining_occurrence( $start_date, $end_date, $interval ) {
			$dates = array();
			$now   = current_time( 'timestamp' );
			while ( $start_date <= $end_date ) {
				if ( $now < $start_date ) {
					$dates[] = date( 'Y-m-d H:i:s', $start_date ); //phpcs:ignore
				}
				$start_date = strtotime( $interval, $start_date );
			}
			return ( count( $dates ) - 1 );
		}

		function arm_get_confirm_box( $item_id = 0, $confirmText = '', $btnClass = '', $deleteType = '',$deleteText='',$cancelText='',$confirmTextTitle='') {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs;
			
			$item_id = $item_id;
			$confirmText = sanitize_text_field( $confirmText );
			$btnClass = esc_attr( $btnClass );
			$deleteType = esc_attr( $deleteType );

			$deleteText = !empty($deleteText) ? sanitize_text_field( $deleteText ) : esc_html__('Delete', 'armember-membership');
			$cancelText = !empty($cancelText) ? sanitize_text_field( $cancelText ) : esc_html__('Cancel', 'armember-membership');
			$confirmBox  = "<div class='arm_confirm_box arm_confirm_box_".esc_attr($item_id)."' id='arm_confirm_box_".esc_attr($item_id)."'>";
			$confirmBox .= "<div class='arm_confirm_box_body'>";
			$confirmBox .= "<div class='arm_confirm_box_arrow'></div>";
			$confirmBox .= "<div class='arm_confirm_box_text_title'>".$confirmTextTitle."</div>";
			$confirmBox .= "<div class='arm_confirm_box_text'>".esc_html($confirmText)."</div>";
			$confirmBox .= "<div class='arm_confirm_box_btn_container'>";
			$confirmBox .= "<button type='button' class='arm_confirm_box_btn armcancel' onclick='hideConfirmBoxCallback();'>" . esc_html($cancelText) . '</button>';
			$confirmBox .= "<button type='button' class='arm_confirm_box_btn armok ".esc_attr($btnClass).
			"' data-item_id='".esc_attr($item_id)."' data-type='".esc_attr($deleteType)."'>" . esc_html($deleteText) . '</button>';
			$confirmBox .= '</div>';
			$confirmBox .= '</div>';
			$confirmBox .= '</div>';
			return $confirmBox;
		}
		function arm_get_bpopup_html( $args ) {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs;
			$defaults = array(
				'id'              => '',
				'class'           => 'arm_bpopup_wrapper',
				'title'           => '',
				'content'         => '',
				'button_id'       => '',
				'button_onclick'  => '',
				'ok_btn_class'    => '',
				'ok_btn_text'     => esc_html__( 'Ok', 'armember-membership' ),
				'cancel_btn_text' => esc_html__( 'Cancel', 'armember-membership' ),
				'close_icon' => false
			);
			extract( shortcode_atts( $defaults, $args ) );
			/* Generate Popup HTML */
			$popup          = '<div id="' . esc_attr( $id ) . '" class="popup_wrapper ' . esc_attr( $class ) . '"><div class="popup_wrapper_inner">';
			$popup         .= '<div class="popup_header">';
			$popup .= '<span class="popup_header_text">' . $title; //phpcs:ignore
            if($close_icon)
            {
                $popup         .= '<span class="popup_close_btn arm_popup_close_btn"></span>'; //phpcs:ignore
            }
            $popup .= '</span>';
			$popup         .= '</div>';
			$popup         .= '<div class="popup_content_text">' . $content . '</div>'; //phpcs:ignore
			$popup         .= '<div class="armclear"></div>';
			$popup         .= '<div class="popup_footer">';
			$popup         .= '<div class="popup_content_btn_wrapper">';
			$ok_btn_onclick = ( ! empty( $button_onclick ) ) ? 'onclick="' . esc_attr($button_onclick) . '"' : '';
			$popup         .= '<button type="button" class="arm_submit_btn popup_ok_btn ' . esc_attr($ok_btn_class) . '" id="' . esc_attr($button_id) . '" ' . $ok_btn_onclick . '>' . esc_html($ok_btn_text) . '</button>';
			$popup         .= '</div>';
			$popup         .= '<div class="popup_content_btn_wrapper">';
			$popup         .= '<button class="arm_cancel_btn popup_close_btn" type="button">' . esc_html($cancel_btn_text) . '</button>';
			$popup         .= '</div>';
			$popup         .= '</div>';
			$popup         .= '<div class="armclear"></div>';
			$popup         .= '</div></div>';
			return $popup;
		}

		function arm_get_plugin_upgrade_popup(){
			$popup          = '<div id="arm_black_friday_bpopup" class="popup_wrapper arm_black_friday_bpopup"><div class="popup_wrapper_inner">';
			$popup         .= '<div class="popup_header">';
			$popup         .= '<span class="popup_close_btn arm_popup_close_btn"></span>';
			$popup         .= '</div>';
			$popup         .= '<div class="popup_content_text"></div>'; //phpcs:ignore
			$popup         .= '<div class="armclear"></div>';
			$popup         .= '</div></div>';
			return $popup;
		}

		function arm_get_bpopup_html_payment( $args ) {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs;
			$defaults = array(
				'id'              => '',
				'class'           => 'arm_bpopup_wrapper',
				'title'           => '',
				'content'         => '',
				'button_id'       => '',
				'button_onclick'  => '',
				'ok_btn_class'    => '',
				'ok_btn_text'     => esc_html__( 'Ok', 'armember-membership' ),
				'cancel_btn_text' => esc_html__( 'Cancel', 'armember-membership' ),
			);
			extract( shortcode_atts( $defaults, $args ) );
			/* Generate Popup HTML */
			$popup          = '<div id="' . esc_attr($id) . '" class="popup_wrapper ' . esc_attr($class) . '"><div class="popup_wrapper_inner">';
			$popup         .= '<div class="popup_header">';
			$popup         .= '<span class="popup_close_btn arm_popup_close_btn"></span>';
			$popup         .= '<span class="popup_header_text">' . esc_html($title) . '</span>';
			$popup         .= '</div>';
			$popup         .= '<div class="popup_content_text">' . $content . '</div>'; //phpcs:ignore
			$popup         .= '<div class="armclear"></div>';
			$popup         .= '<div class="popup_footer">';
			$popup         .= '<div class="popup_content_btn_wrapper">';
			$ok_btn_onclick = ( ! empty( $button_onclick ) ) ? 'onclick="' . esc_attr($button_onclick) . '"' : '';
			$popup         .= '<button type="button" class="arm_submit_btn popup_ok_btn ' . esc_attr($ok_btn_class) . '" id="' . esc_attr($button_id) . '" ' . $ok_btn_onclick . '>' . esc_html($ok_btn_text) . '</button>';
			$popup         .= '</div>';

			$popup .= '</div>';
			$popup .= '<div class="armclear"></div>';
			$popup .= '</div></div>';
			return $popup;
		}

		function arm_after_delete_term( $term, $tt_id, $taxonomy, $deleted_term ) {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs;
			delete_arm_term_meta( $term, 'arm_protection' );
			delete_arm_term_meta( $term, 'arm_access_plan' );
		}

		/**         * **************************************************************************************
		 * * String Utilities Functions
		 * * ************************************************************************************* */

		/**
		 * Trims deeply; alias of `trim_deep`.
		 *
		 * @param string|array $value Either a string, an array, or a multi-dimensional array, filled with integer and/or string values.
		 * @return string|array Either the input string, or the input array; after all data is trimmed up according to arguments passed in.
		 */
		public static function trim( $value = '', $chars = false, $extra_chars = false ) {
			return self::trim_deep( $value, $chars, $extra_chars );
		}

		/**
		 * Trims deeply; or use {@link s2Member\Utilities\self::trim()}.
		 *
		 * @param string|array $value Either a string, an array, or a multi-dimensional array, filled with integer and/or string values.
		 * @return string|array Either the input string, or the input array; after all data is trimmed up according to arguments passed in.
		 */
		public static function trim_deep( $value = '', $chars = false, $extra_chars = false ) {
			$chars = ( is_string( $chars ) ) ? $chars : " \t\n\r\0\x0B";
			$chars = ( is_string( $extra_chars ) ) ? $chars . $extra_chars : $chars;
			if ( is_array( $value ) ) {
				foreach ( $value as &$r ) {
					$r = self::trim_deep( $r, $chars );
				}
				return $value;
			}
			return trim( (string) $value, $chars );
		}

		/**
		 * Trims all single/double quote entity variations deeply.
		 * This is useful on Shortcode attributes mangled by a Visual Editor.
		 *
		 * @param string|array $value Either a string, an array, or a multi-dimensional array, filled with integer and/or string values.
		 * @return string|array Either the input string, or the input array; after all data is trimmed up.
		 */
		public static function trim_qts_deep( $value = '' ) {
			$quote_entities_variations = array(
				'&apos;'           => '&apos;',
				'&#0*39;'          => '&#39;',
				'&#[xX]0*27;'      => '&#x27;',
				'&lsquo;'          => '&lsquo;',
				'&#0*8216;'        => '&#8216;',
				'&#[xX]0*2018;'    => '&#x2018;',
				'&rsquo;'          => '&rsquo;',
				'&#0*8217;'        => '&#8217;',
				'&#[xX]0*2019;'    => '&#x2019;',
				'&quot;'           => '&quot;',
				'&#0*34;'          => '&#34;',
				'&#[xX]0*22;'      => '&#x22;',
				'&ldquo;'          => '&ldquo;',
				'&#0*8220;'        => '&#8220;',
				'&#[xX]0*201[cC];' => '&#x201C;',
				'&rdquo;'          => '&rdquo;',
				'&#0*8221;'        => '&#8221;',
				'&#[xX]0*201[dD];' => '&#x201D;',
			);
			$qts                       = implode( '|', array_keys( $quote_entities_variations ) );
			return is_array( $value ) ? array_map( 'self::trim_qts_deep', $value ) : preg_replace( '/^(?:' . $qts . ')+|(?:' . $qts . ')+$/', '', (string) $value );
		}

		/**
		 * Trims HTML whitespace.
		 * This is useful on Shortcode content.
		 *
		 * @param string $string Input string to trim.
		 * @return string Output string with all HTML whitespace trimmed away.
		 */
		public static function trim_html( $string = '' ) {
			$whitespace = '&nbsp;|\<br\>|\<br\s*\/\>|\<p\>(?:&nbsp;)*\<\/p\>';
			return preg_replace( '/^(?:' . $whitespace . ')+|(?:' . $whitespace . ')+$/', '', (string) $string );
		}

		public static function arm_set_ini_for_access_rules() {
			$memoryLimit = ini_get( 'memory_limit' );
			if ( preg_match( '/^(\d+)(.)$/', $memoryLimit, $matches ) ) {
				if ( $matches[2] == 'M' ) {
					$memoryLimit = $matches[1] * 1024 * 1024;
				} elseif ( $matches[2] == 'K' ) {
					$memoryLimit = $matches[1] * 1024;
				}
			}
			if ( $memoryLimit < ( 256 * 1024 * 1024 ) ) {
				/* @define('WP_MEMORY_LIMIT', '256M'); */
				@ini_set( 'memory_limit', '256M' );//phpcs:ignore
			}
			set_time_limit( 0 ); //phpcs:ignore
		}

		public static function arm_set_ini_for_importing_users() {
			$memoryLimit = ini_get( 'memory_limit' );
			if ( preg_match( '/^(\d+)(.)$/', $memoryLimit, $matches ) ) {
				if ( $matches[2] == 'M' ) {
					$memoryLimit = $matches[1] * 1024 * 1024;
				} elseif ( $matches[2] == 'K' ) {
					$memoryLimit = $matches[1] * 1024;
				}
			}
			if ( $memoryLimit < ( 512 * 1024 * 1024 ) ) {
				/* @define('WP_MEMORY_LIMIT', '256M'); */
				@ini_set( 'memory_limit', '512M' );//phpcs:ignore
			}
			set_time_limit( 0 ); //phpcs:ignore
		}

		function arm_add_page_label_css( $hook ) {
			if ( 'edit.php' != $hook ) {
				return;
			}
			$postLabelCss  = '<style type="text/css">';
			$postLabelCss .= '.arm_set_page_label, .arm_set_page_label_protected, .arm_set_page_label_drippred{display: inline-block;margin-right: 5px;padding: 3px 8px;font-size: 11px;line-height: normal;color: #fff;border-radius: 10px;-webkit-border-radius: 10px;-moz-border-radius: 10px;-o-border-radius: 10px;}';
			$postLabelCss .= ' .arm_set_page_label{background-color: #53ba73;}';
			$postLabelCss .= ' .arm_set_page_label_protected{background-color: #191111;}';
			$postLabelCss .= ' .arm_set_page_label_drippred{background-color: #e34581;}';
			$postLabelCss .= '</style>';
			echo $postLabelCss; //phpcs:ignore
		}

		function arm_add_set_page_label( $states, $post = null ) {
			global $wpdb, $ARMemberLite, $post;
			if ( isset( $post->ID ) ) {
				$str = '';
				if ( get_post_type( $post->ID ) == 'page' ) {
					$arm_page_settings = $this->arm_get_single_global_settings( 'page_settings' );
					if ( ! empty( $arm_page_settings ) ) {
						foreach ( $arm_page_settings as $key => $value ) {
							if ( $value == $post->ID ) {
								switch ( strtolower( $key ) ) {
									case 'register_page_id':
										$title_label = esc_html__( 'Registration page', 'armember-membership' );
										break;
									case 'login_page_id':
										$title_label = esc_html__( 'Login page', 'armember-membership' );
										break;
									case 'forgot_password_page_id':
										$title_label = esc_html__( 'Forgot Password page', 'armember-membership' );
										break;
									case 'edit_profile_page_id':
										$title_label = esc_html__( 'Edit Profile page', 'armember-membership' );
										break;
									case 'change_password_page_id':
										$title_label = esc_html__( 'Change Password page', 'armember-membership' );
										break;
									case 'member_profile_page_id':
										$title_label = esc_html__( 'Member Profile page', 'armember-membership' );
										break;
									case 'guest_page_id':
										$title_label = esc_html__( 'Guest page', 'armember-membership' );
										break;
								}
								if ( ! empty( $title_label ) ) {
									$str .= '<div class="arm_set_page_label">ARMember ' . esc_html($title_label) . '</div>';
								}
							}
						}
					}
				}

				$arm_protect = 0;
				$item_plans  = get_post_meta( $post->ID, 'arm_access_plan' );
				$item_plans  = ( ! empty( $item_plans ) ) ? $item_plans : array();

				if ( count( $item_plans ) == 0 ) {
					$arm_protect = 0;
				} else {
					$arm_protect = 1;
				}

				if ( ! empty( $arm_protect ) && $arm_protect == 1 ) {
					$str .= '<div class="arm_set_page_label_protected">' . esc_html__( 'ARMember Protected', 'armember-membership' ) . '</div>';
				}
				/**
				 * Check If Post Has Drip Rules
				 */

				if ( ! empty( $str ) ) {
					$states[] = $str;
				}
			}
			return $states;
		}

		function arm_update_feature_settings() {
			global $wp, $wpdb, $wp_rewrite, $ARMemberLite, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_feature_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce

			$response = array(
				'type' => 'error',
				'msg'  => esc_html__( 'Sorry, Something went wrong. Please try again.', 'armember-membership' ),
			);
			if ( ! empty( $_POST['arm_features_options'] ) ) { // phpcs:ignore
				$features_options    = sanitize_text_field($_POST['arm_features_options']); //phpcs:ignore
				$arm_features_status = ( ! empty( $_POST['arm_features_status'] ) ) ? intval( $_POST['arm_features_status'] ) : 0; //phpcs:ignore
				
				$arm_default_module_array = array(
                    'arm_is_social_feature',
                    'arm_is_gutenberg_block_restriction_feature',
                    'arm_is_beaver_builder_restriction_feature',
                    'arm_is_divi_builder_restriction_feature',
                    'arm_is_wpbakery_page_builder_restriction_feature',
                    'arm_is_fusion_builder_restriction_feature',
                    'arm_is_oxygen_builder_restriction_feature',
                    'arm_is_siteorigin_builder_restriction_feature',
                    'arm_is_bricks_builder_restriction_feature',
                );
                if(in_array($features_options, $arm_default_module_array))
                {
					if ( $arm_features_status == 1 ) {

						// do_action('arm_update_feature_settings', $_POST);

						if ( $features_options == 'arm_is_social_feature' ) {
							$isPageExist                = false;
							$old_member_profile_page_id = isset( $this->global_settings['member_profile_page_id'] ) ? $this->global_settings['member_profile_page_id'] : 0;
							if ( ! empty( $old_member_profile_page_id ) && $old_member_profile_page_id != 0 ) {
								$isPageExist = true;
								$pageData    = get_post( $old_member_profile_page_id );
								if ( ! isset( $pageData->ID ) || empty( $pageData->ID ) ) {
									$isPageExist = false;
								}
							}
							if ( ! $isPageExist ) {
								$profileTemplateID        = $wpdb->get_var( $wpdb->prepare('SELECT `arm_id` FROM `' . $ARMemberLite->tbl_arm_member_templates . "` WHERE `arm_type`=%s ORDER BY `arm_id` ASC LIMIT 1",'profile') );//phpcs:ignore --Reason: $ARMemberLite->tbl_arm_member_templates is a table name
								$profileTemplateShortcode = ( ! empty( $profileTemplateID ) ) ? '[arm_template type="profile" id="' . $profileTemplateID . '"]' : '';
								$profilePageData          = array(
									'post_title'   => 'Profile',
									'post_name'    => 'arm_member_profile',
									'post_content' => $profileTemplateShortcode,
									'post_status'  => 'publish',
									'post_parent'  => 0,
									'post_author'  => 1,
									'post_type'    => 'page',
								);
								$page_id                  = wp_insert_post( $profilePageData );
								$new_global_settings      = $this->arm_get_all_global_settings();
								$new_global_settings['page_settings']['member_profile_page_id'] = $page_id;
								update_option( 'arm_global_settings', $new_global_settings );
								$this->arm_user_rewrite_rules();
								$wp_rewrite->flush_rules( false );
							}
							$arm_features_status = ( ! empty( $_POST['arm_features_status'] ) ) ? intval( $_POST['arm_features_status'] ) : 0; //phpcs:ignore
							update_option( 'arm_is_social_feature', '1' );
							$response = array(
								'type' => 'success',
								'msg'  => esc_html__( 'Features Settings Updated Successfully.', 'armember-membership' ),
							);
							echo wp_json_encode( $response );
							die();
						} else if ($features_options == 'arm_is_beaver_builder_restriction_feature') {
                            if (file_exists( WP_PLUGIN_DIR . "/beaver-builder-lite-version/fl-builder.php") || file_exists( WP_PLUGIN_DIR . "/bb-plugin/fl-builder.php")) {
                                if (is_plugin_active('beaver-builder-lite-version/fl-builder.php') || is_plugin_active('bb-plugin/fl-builder.php')) {
                                    update_option($features_options, $arm_features_status);
                                    update_option('arm_is_beaver_builder_restriction_feature_old', $arm_features_status);
                                    $response = array('type' => 'success', 'msg' => esc_html__('Features Settings Updated Successfully.', 'armember-membership'));
                                    echo wp_json_encode($response);
                                    die();
                                } else {
                                    update_option($features_options, 0);
                                    $response = array('type' => 'beaver_builder_error', 'msg' => esc_html__('Please activate Beaver Builder and try to active this add-on.', 'armember-membership'));
                                    echo wp_json_encode($response);
                                    die();
                                }
                            } else {
                                update_option($features_options, 0);
                                $response = array('type' => 'beaver_builder_error', 'msg' => esc_html__('Please install Beaver Builder and try to active this add-on.', 'armember-membership'));
                                echo wp_json_encode($response);
                                die();
                            }
                        } else if ($features_options == 'arm_is_divi_builder_restriction_feature') {
                            if (file_exists( WP_PLUGIN_DIR . "/divi-builder/divi-builder.php") || wp_get_theme()->get('Name') == 'Divi') {
                                if (is_plugin_active('divi-builder/divi-builder.php') || wp_get_theme()->get('Name') == 'Divi') {
                                    update_option($features_options, $arm_features_status);
                                    update_option('arm_is_divi_builder_restriction_feature_old', $arm_features_status);
                                    $response = array('type' => 'success', 'msg' => esc_html__('Features Settings Updated Successfully.', 'armember-membership'));
                                    echo wp_json_encode($response);
                                    die();
                                } else {
                                    update_option($features_options, 0);
                                    $response = array('type' => 'divi_builder_error', 'msg' => esc_html__('Please activate Divi Builder or Divi Theme and try to active this add-on.', 'armember-membership'));
                                    echo wp_json_encode($response);
                                    die();
                                }
                            } else {
                                update_option($features_options, 0);
                                $response = array('type' => 'beaver_builder_error', 'msg' => esc_html__('Please install Divi Builder or Divi Theme and try to active this add-on.', 'armember-membership'));
                                echo wp_json_encode($response);
                                die();
                            }
                        } else if ($features_options == 'arm_is_wpbakery_page_builder_restriction_feature') {
                            if ( file_exists( WP_PLUGIN_DIR . "/js_composer/js_composer.php") ) {
                                if ( is_plugin_active('js_composer/js_composer.php') ) {
                                    update_option($features_options, $arm_features_status);
                                    update_option('arm_is_wpbakery_page_builder_restriction_feature_old', $arm_features_status);
                                    $response = array('type' => 'success', 'msg' => esc_html__('Features Settings Updated Successfully.', 'armember-membership'));
                                    echo wp_json_encode($response);
                                    die();
                                } else {
                                    update_option($features_options, 0);
                                    $response = array('type' => 'wpbakery_page_builder_error', 'msg' => esc_html__('Please activate WPBakery Page Builder and try to active this add-on.', 'armember-membership'));
                                    echo wp_json_encode($response);
                                    die();
                                }
                            } else {
                                update_option($features_options, 0);
                                $response = array('type' => 'wpbakery_page_builder_error', 'msg' => esc_html__('Please install WPBakery Page Builder and try to active this add-on.', 'armember-membership'));
                                echo wp_json_encode($response);
                                die();
                            }
                        } else if ($features_options == 'arm_is_fusion_builder_restriction_feature') {
                            if (file_exists( WP_PLUGIN_DIR . "/fusion-builder/fusion-builder.php") ) {
                                if (is_plugin_active('fusion-builder/fusion-builder.php') ) {
                                    update_option($features_options, $arm_features_status);
                                    update_option('arm_is_fusion_builder_restriction_feature_old', $arm_features_status);
                                    $response = array('type' => 'success', 'msg' => esc_html__('Features Settings Updated Successfully.', 'armember-membership'));
                                    echo wp_json_encode($response);
                                    die();
                                } else {
                                    update_option($features_options, 0);
                                    $response = array('type' => 'fusion_builder_error', 'msg' => esc_html__('Please activate Fusion Builder or Avada Theme and try to active this add-on.', 'armember-membership'));
                                    echo wp_json_encode($response);
                                    die();
                                }
                            } else {
                                update_option($features_options, 0);
                                $response = array('type' => 'fusion_builder_error', 'msg' => esc_html__('Please install Fusion Builder or Avada Theme and try to active this add-on.', 'armember-membership'));
                                echo wp_json_encode($response);
                                die();
                            }
                        } else if ($features_options == 'arm_is_oxygen_builder_restriction_feature') {
                            if (file_exists( WP_PLUGIN_DIR . "/oxygen/functions.php") ) {
                                if (is_plugin_active('oxygen/functions.php') ) {
                                    update_option($features_options, $arm_features_status);
                                    update_option('arm_is_oxygen_builder_restriction_feature_old', $arm_features_status);
                                    $response = array('type' => 'success', 'msg' => esc_html__('Features Settings Updated Successfully.', 'armember-membership'));
                                    echo wp_json_encode($response);
                                    die();
                                } else {
                                    update_option($features_options, 0);
                                    $response = array('type' => 'oxygen_builder_error', 'msg' => esc_html__('Please activate Oxygen Builder and try to active this add-on.', 'armember-membership'));
                                    echo wp_json_encode($response);
                                    die();
                                }
                            } else {
                                update_option($features_options, 0);
                                $response = array('type' => 'oxygen_builder_error', 'msg' => esc_html__('Please install Oxygen Builder and try to active this add-on.', 'armember-membership'));
                                echo wp_json_encode($response);
                                die();
                            }
                        } else if ($features_options == 'arm_is_siteorigin_builder_restriction_feature') {
                            if (file_exists( WP_PLUGIN_DIR . "/siteorigin-panels/siteorigin-panels.php") ) {
                                if (is_plugin_active('siteorigin-panels/siteorigin-panels.php') ) {
                                    update_option($features_options, $arm_features_status);
                                    update_option('arm_is_siteorigin_builder_restriction_feature_old', $arm_features_status);
                                    $response = array('type' => 'success', 'msg' => esc_html__('Features Settings Updated Successfully.', 'armember-membership'));
                                    echo wp_json_encode($response);
                                    die();
                                } else {
                                    update_option($features_options, 0);
                                    $response = array('type' => 'siteorigin_builder_error', 'msg' => esc_html__('Please activate SiteOrigin Builder and try to active this add-on.', 'armember-membership'));
                                    echo wp_json_encode($response);
                                    die();
                                }
                            } else {
                                update_option($features_options, 0);
                                $response = array('type' => 'siteorigin_builder_error', 'msg' => esc_html__('Please install SiteOrigin Builder and try to active this add-on.', 'armember-membership'));
                                echo wp_json_encode($response);
                                die();
                            }
                        } else if ($features_options == 'arm_is_bricks_builder_restriction_feature') {
                            if (wp_get_theme()->get('Name') == 'Bricks' || is_child_theme('Bricks')) {
                                if (wp_get_theme()->get('Name') == 'Bricks' || is_child_theme('Bricks')) {
                                    update_option($features_options, $arm_features_status);
                                    update_option('arm_is_bricks_builder_restriction_feature_old', $arm_features_status);
                                    $response = array('type' => 'success', 'msg' => esc_html__('Features Settings Updated Successfully.', 'armember-membership'));
                                    echo wp_json_encode($response);
                                    die();
                                } else {
                                    update_option($features_options, 0);
                                    $response = array('type' => 'bricks_builder_error', 'msg' => esc_html__('Please activate Bricks Builder and try to active this add-on.', 'armember-membership'));
                                    echo wp_json_encode($response);
                                    die();
                                }
                            } else {
                                update_option($features_options, 0);
                                $response = array('type' => 'bricks_builder_error', 'msg' => esc_html__('Please install Bricks Builder and try to active this add-on.', 'armember-membership'));
                                echo wp_json_encode($response);
                                die();
                            }
                        } else  {
							$arm_features_status = (!empty($_POST['arm_features_status'])) ? intval($_POST['arm_features_status']) : 0; //phpcs:ignore
							update_option($features_options, $arm_features_status);
							$response = array('type' => 'success', 'msg' => esc_html__('Features Settings Updated Successfully.', 'armember-membership'));
							echo wp_json_encode($response);
							die();
						}
					} else {

						// do_action('arm_deactivate_feature_settings', $_POST);

						update_option($features_options, 0);
						$response = array(
							'type' => 'success',
							'msg'  => esc_html__( 'Features Settings Updated Successfully.', 'armember-membership' ),
						);
						echo wp_json_encode( $response );
						die();
					}
				}
			} /* END `(!empty($_POST['arm_features_options']))` */
		}





		function arm_get_front_font_style() {
			global $wp, $wpdb, $arm_slugs, $current_user, $arm_lite_errors, $ARMemberLite, $arm_subscription_plans, $arm_member_forms;
			$frontfontstyle   = array();
			$frontFontFamilys = array();
			$frontfontOptions = array( 'level_1_font', 'level_2_font', 'level_3_font', 'level_4_font', 'link_font', 'button_font' );
			$frontOptions     = isset( $this->global_settings['front_settings'] ) ? $this->global_settings['front_settings'] : array();
			foreach ( $frontfontOptions as $key ) {
				$ffont_family       = ( isset( $frontOptions[ $key ]['font_family'] ) ) ? esc_attr( $frontOptions[ $key ]['font_family'] ) : 'Poppins';
				$ffont_family       = ( $ffont_family == 'inherit' ) ? '' : $ffont_family;
				$frontFontFamilys[] = $ffont_family;
				$ffont_size         = ( isset( $frontOptions[ $key ]['font_size'] ) ) ? esc_attr( $frontOptions[ $key ]['font_size'] ) : '';
				$ffont_color        = ( isset( $frontOptions[ $key ]['font_color'] ) ) ? esc_attr( $frontOptions[ $key ]['font_color'] ) : '';
				$ffont_bold         = ( isset( $frontOptions[ $key ]['font_bold'] ) && $frontOptions[ $key ]['font_bold'] == '1' ) ? 'font-weight: bold !important;' : 'font-weight: normal !important;';
				$ffont_italic       = ( isset( $frontOptions[ $key ]['font_italic'] ) && $frontOptions[ $key ]['font_italic'] == '1' ) ? 'font-style: italic !important;' : 'font-style: normal !important;';
				$ffont_decoration   = ( ! empty( $frontOptions[ $key ]['font_decoration'] ) ) ? 'text-decoration: ' . esc_attr( $frontOptions[ $key ]['font_decoration'] ) . ' !important;' : 'text-decoration: none !important;';

				$front_font_family            = ( ! empty( $ffont_family ) ) ? 'font-family: ' . esc_attr( $ffont_family ) . ", sans-serif, 'Trebuchet MS' !important;" : '';
				$frontOptions[ $key ]['font'] = "{$front_font_family} font-size: ". esc_attr( $ffont_size ) ."px !important;color: ". esc_attr( $ffont_color ). " !important;". esc_attr( $ffont_bold ) . esc_attr( $ffont_italic ) . esc_attr( $ffont_decoration );
			}
			$gFontUrl = $arm_member_forms->arm_get_google_fonts_url( $frontFontFamilys );
			if ( ! empty( $gFontUrl ) ) {
				$frontfontstyle['google_font_url'] = esc_url( $gFontUrl );
			}
			$frontfontstyle['frontOptions'] = $frontOptions;
			return $frontfontstyle;
		}

	}

}
					global $arm_global_settings;
					$arm_global_settings = new ARM_global_settings_Lite();
if ( ! function_exists( 'arm_generate_random_code' ) ) {

	function arm_generate_random_code( $length = 10 ) {
		$charLength = round( $length * 0.8 );
		$numLength  = round( $length * 0.2 );
		$keywords   = array(
			array(
				'count' => $charLength,
				'char'  => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
			),
			array(
				'count' => $numLength,
				'char'  => '0123456789',
			),
		);
		$temp_array = array();
		foreach ( $keywords as $char_set ) {
			for ( $i = 0; $i < $char_set['count']; $i++ ) {
				$temp_array[] = $char_set['char'][ wp_rand( 0, strlen( $char_set['char'] ) - 1 ) ];
			}
		}
		shuffle( $temp_array );
		return implode( '', $temp_array );
	}
}

if ( ! function_exists( 'arm_generate_captcha_code' ) ) {

	function arm_generate_captcha_code( $length = 8 ) {
		$possible_letters = '23456789bcdfghjkmnpqrstvwxyz';
		$random_dots      = 0;
		$random_lines     = 20;
		$code             = '';
		$i                = 0;
		while ( $i < $length ) {
			$code .= substr( $possible_letters, wp_rand( 0, strlen( $possible_letters ) - 1 ), 1 );
			$i++;
		}
		return $code;
	}
}

if ( ! function_exists( 'add_arm_term_meta' ) ) {

	/**
	 * Add meta data field to a term.
	 *
	 * @param int    $term_id Post ID.
	 * @param string $key Metadata name.
	 * @param mixed  $value Metadata value.
	 * @param bool   $unique Optional, default is false. Whether the same key should not be added.
	 * @return bool False for failure. True for success.
	 */
	function add_arm_term_meta( $term_id, $meta_key, $meta_value, $unique = false ) {
		return add_metadata( 'arm_term', $term_id, $meta_key, $meta_value, $unique );
	}
}
if ( ! function_exists( 'delete_arm_term_meta' ) ) {

	/**
	 * Remove metadata matching criteria from a term.
	 *
	 * You can match based on the key, or key and value. Removing based on key and
	 * value, will keep from removing duplicate metadata with the same key. It also
	 * allows removing all metadata matching key, if needed.
	 *
	 * @param int    $term_id term ID
	 * @param string $meta_key Metadata name.
	 * @param mixed  $meta_value Optional. Metadata value.
	 * @return bool False for failure. True for success.
	 */
	function delete_arm_term_meta( $term_id, $meta_key, $meta_value = '' ) {
		return delete_metadata( 'arm_term', $term_id, $meta_key, $meta_value );
	}
}
if ( ! function_exists( 'get_arm_term_meta' ) ) {

	/**
	 * Retrieve term meta field for a term.
	 *
	 * @param int    $term_id Term ID.
	 * @param string $key The meta key to retrieve.
	 * @param bool   $single Whether to return a single value.
	 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single
	 *  is true.
	 */
	function get_arm_term_meta( $term_id, $key, $single = false ) {
		return get_metadata( 'arm_term', $term_id, $key, $single );
	}
}
if ( ! function_exists( 'update_arm_term_meta' ) ) {

	/**
	 * Update term meta field based on term ID.
	 *
	 * Use the $prev_value parameter to differentiate between meta fields with the
	 * same key and term ID.
	 *
	 * If the meta field for the term does not exist, it will be added.
	 *
	 * @param int    $term_id Term ID.
	 * @param string $key Metadata key.
	 * @param mixed  $value Metadata value.
	 * @param mixed  $prev_value Optional. Previous value to check before removing.
	 * @return bool False on failure, true if success.
	 */
	function update_arm_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
		return update_metadata( 'arm_term', $term_id, $meta_key, $meta_value, $prev_value );
	}
}
if ( ! function_exists( 'armXML_to_Array' ) ) {

	/**
	 * Convert XML File Data Into Array
	 *
	 * @param type $content (xml file content)
	 */
	function armXML_to_Array( $contents, $get_attributes = 1, $priority = 'tag' ) {
		if ( ! $contents ) {
			return array();
		}
		if ( ! function_exists( 'xml_parser_create' ) ) {
			/* print "'xml_parser_create()' function not found!"; */
			return array();
		}
		/* Get the XML parser of PHP - PHP must have this module for the parser to work */
		$parser = xml_parser_create( '' );
		xml_parser_set_option( $parser, XML_OPTION_TARGET_ENCODING, 'UTF-8' );
		xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
		xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
		xml_parse_into_struct( $parser, trim( $contents ), $xml_values );
		xml_parser_free( $parser );
		if ( ! $xml_values ) {
			return;
		}
		/* Initializations */
		$xml_array   = array();
		$parents     = array();
		$opened_tags = array();
		$arr         = array();

		$current = &$xml_array; /* Refference */

		$repeated_tag_index = array(); /* Multiple tags with same name will be turned into an array */
		foreach ( $xml_values as $data ) {
			unset( $attributes, $value ); /* Remove existing values, or there will be trouble */
			/**
			 * This command will extract these variables into the foreach scope tag(string), type(string), level(int), attributes(array).
			 */
			extract( $data );
			$result          = array();
			$attributes_data = array();
			if ( isset( $value ) ) {
				if ( $priority == 'tag' ) {
					$result = $value;
				} else {
					$result['value'] = $value; /* Put the value in a assoc array if we are in the 'Attribute' mode */
				}
			}
			/* Set the attributes too. */
			if ( isset( $attributes ) and $get_attributes ) {
				foreach ( $attributes as $attr => $val ) {
					if ( $priority == 'tag' ) {
						$attributes_data[ $attr ] = $val;
					} else {
						$result['attr'][ $attr ] = $val; /* Set all the attributes in a array called 'attr' */
					}
				}
			}
			/* See tag status and do the needed. */
			if ( $type == 'open' ) {
				/* The starting of the tag '<tag>' */
				$parent[ $level - 1 ] = &$current;
				if ( ! is_array( $current ) or ( ! in_array( $tag, array_keys( $current ) ) ) ) {
					$current[ $tag ] = $result;
					if ( $attributes_data ) {
						$current[ $tag . '_attr' ] = $attributes_data;
					}
					$repeated_tag_index[ $tag . '_' . $level ] = 1;

					$current = &$current[ $tag ];
				} else {
					/* There was another element with the same tag name */
					if ( isset( $current[ $tag ][0] ) ) {
						/* If there is a 0th element it is already an array */
						$current[ $tag ][ $repeated_tag_index[ $tag . '_' . $level ] ] = $result;
						$repeated_tag_index[ $tag . '_' . $level ] ++;
					} else {
						/*
											 This section will make the value an array if multiple tags with the same name appear together */
						/* This will combine the existing item and the new item together to make an array */
						$current[ $tag ]                           = array( $current[ $tag ], $result );
						$repeated_tag_index[ $tag . '_' . $level ] = 2;

						if ( isset( $current[ $tag . '_attr' ] ) ) {
							/* The attribute of the last(0th) tag must be moved as well */
							$current[ $tag ]['0_attr'] = $current[ $tag . '_attr' ];
							unset( $current[ $tag . '_attr' ] );
						}
					}
					$last_item_index = $repeated_tag_index[ $tag . '_' . $level ] - 1;
					$current         = &$current[ $tag ][ $last_item_index ];
				}
			} elseif ( $type == 'complete' ) {
				/*
									 Tags that ends in 1 line '<tag />' */
				/* See if the key is already taken. */
				if ( ! isset( $current[ $tag ] ) ) {
					$current[ $tag ]                           = $result;
					$repeated_tag_index[ $tag . '_' . $level ] = 1;
					if ( $priority == 'tag' and $attributes_data ) {
						$current[ $tag . '_attr' ] = $attributes_data;
					}
				} else {
					/* If taken, put all things inside a list(array) */
					if ( isset( $current[ $tag ][0] ) and is_array( $current[ $tag ] ) ) {
						$current[ $tag ][ $repeated_tag_index[ $tag . '_' . $level ] ] = $result;
						if ( $priority == 'tag' and $get_attributes and $attributes_data ) {
							$current[ $tag ][ $repeated_tag_index[ $tag . '_' . $level ] . '_attr' ] = $attributes_data;
						}
						$repeated_tag_index[ $tag . '_' . $level ] ++;
					} else {
						$current[ $tag ]                           = array( $current[ $tag ], $result );
						$repeated_tag_index[ $tag . '_' . $level ] = 1;
						if ( $priority == 'tag' and $get_attributes ) {
							if ( isset( $current[ $tag . '_attr' ] ) ) {
								$current[ $tag ]['0_attr'] = $current[ $tag . '_attr' ];
								unset( $current[ $tag . '_attr' ] );
							}
							if ( $attributes_data ) {
								$current[ $tag ][ $repeated_tag_index[ $tag . '_' . $level ] . '_attr' ] = $attributes_data;
							}
						}
						$repeated_tag_index[ $tag . '_' . $level ] ++; /* 0 and 1 index is already taken */
					}
				}
			} elseif ( $type == 'close' ) {
				/* End of tag '</tag>' */
				$current = &$parent[ $level - 1 ];
			}
		}
		return $xml_array;
	}
}


if ( ! function_exists( 'arm_array_map' ) ) {

	function arm_array_map( $input = array() ) {
		if ( empty( $input ) ) {
			return $input;
		}

		return is_array( $input ) ? array_map( 'arm_array_map', $input ) : trim( $input );
	}
}

if ( ! function_exists( 'arm_wp_date_format_to_bootstrap_datepicker' ) ) {

	function arm_wp_date_format_to_bootstrap_datepicker( $date_format = '' ) {
		if ( $date_format == '' ) {
			$date_format = get_option( 'date_format' );
		}

		$SYMBOLS_MATCHING = array(
			'd' => 'DD',
			'D' => 'ddd',
			'j' => 'D',
			'l' => 'dddd',
			'N' => '',
			'S' => '',
			'w' => '',
			'z' => 'o',
			'W' => '',
			'F' => 'MMMM',
			'm' => 'MM',
			'M' => 'M',
			'n' => 'm',
			't' => '',
			'L' => '',
			'o' => '',
			'Y' => 'YYYY',
			'y' => 'y',
			'a' => '',
			'A' => '',
			'B' => '',
			'g' => '',
			'G' => '',
			'h' => '',
			'H' => '',
			'i' => '',
			's' => '',
			'u' => '',
		);
		$jqueryui_format  = '';
		$escaping         = false;
		for ( $i = 0; $i < strlen( $date_format ); $i++ ) {
			$char = $date_format[ $i ];
			if ( $char === '\\' ) { // PHP date format escaping character
				$i++;
				if ( $escaping ) {
					$jqueryui_format .= $date_format[ $i ];
				} else {
					$jqueryui_format .= '\'' . $date_format[ $i ];
				}
				$escaping = true;
			} else {
				if ( $escaping ) {
					$jqueryui_format .= "'";
					$escaping         = false;
				}
				if ( isset( $SYMBOLS_MATCHING[ $char ] ) ) {
					$jqueryui_format .= $SYMBOLS_MATCHING[ $char ];
				} else {
					$jqueryui_format .= $char;
				}
			}
		}

		return $jqueryui_format;
	}
}

if ( ! function_exists( 'arm_strtounicode' ) ) {

	function arm_strtounicode( $str = '' ) {
		if ( $str == '' ) {
			return $str;
		}

		return preg_replace_callback(
			"([\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF]{2}|[\xF0-\xF7][\x80-\xBF]{3}|[\xF8-\xFB][\x80-\xBF]{4}|[\xFC-\xFD][\x80-\xBF]{5})",
			function( $m ) {
				$c   = $m[0];
				$out = bindec( ltrim( decbin( ord( $c[0] ) ), '1' ) );
				$l   = strlen( $c );
				for ( $i = 1; $i < $l; $i++ ) {
					$out = ( $out << 6 ) | bindec( ltrim( decbin( ord( $c[ $i ] ) ), '1' ) );
				}
				if ( $out < 256 ) {
					return chr( $out );
				}
				return '&#' . $out . ';';
			},
			$str
		);
	}
}
if ( ! function_exists( 'arm_check_date_format' ) ) {

	function arm_check_date_format( $date_value, $key = 0 ) {
		$date_formats      = array(
			'd/m/Y',
			'm/d/Y',
			'Y/m/d',
			'M d, Y',
			'F d, Y',
			'd M, Y',
			'd F, Y',
			'Y, M d',
			'Y, F d',
		);
		$final_date_format = false;
		foreach ( $date_formats as $k => $format ) {
			if ( DateTime::createFromFormat( $format, $date_value ) ) {
				$final_date_format = DateTime::createFromFormat( $format, $date_value );
				break;
			}
		}
		if ( $final_date_format == '' || empty( $final_date_format ) ) {
			try {
				$final_date_format = new DateTime( $date_value );
			} catch ( Exception $e ) {
				$date_value        = str_replace( '/', '-', $date_value );
				$final_date_format = new DateTime( $date_value );
			}
		}
		return $final_date_format;
	}
}
