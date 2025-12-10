<?php
if ( ! class_exists( 'ARM_email_settings_Lite' ) ) {
	class ARM_email_settings_Lite {

		var $templates;

		function __construct() {
			global $wpdb, $ARMemberLite, $arm_slugs;

			add_action( 'wp_ajax_arm_submit_email_template', array( $this, 'arm_submit_email_template' ) );
			add_action( 'wp_ajax_arm_edit_template_data', array( $this, 'arm_edit_template_data' ) );
			add_action( 'wp_ajax_arm_update_email_template_status', array( $this, 'arm_update_email_template_status' ) );
			add_action('wp_ajax_arm_send_test_email', array( $this,'arm_send_test_email_callback'));
			add_action('wp_ajax_arm_reset_email_template_by_id',array($this, 'arm_reset_email_template_by_id_func'));


			$this->templates                               = new stdClass();
			$this->templates->new_reg_user_admin           = 'new-reg-user-admin';
			$this->templates->new_reg_user_with_payment    = 'new-reg-user-with-payment';
			$this->templates->new_reg_user_without_payment = 'new-reg-user-without-payment';
			$this->templates->email_verify_user            = 'email-verify-user';
			$this->templates->account_verified_user        = 'account-verified-user';
			$this->templates->change_password_user         = 'change-password-user';
			$this->templates->forgot_passowrd_user         = 'forgot-passowrd-user';
			$this->templates->profile_updated_user         = 'profile-updated-user';
			$this->templates->profile_updated_notification_to_admin = 'profile-updated-notification-admin';
			$this->templates->grace_failed_payment         = 'grace-failed-payment';
			$this->templates->grace_eot                    = 'grace-eot';
			$this->templates->failed_payment_admin         = 'failed-payment-admin';
			$this->templates->on_menual_activation = 'on-menual-activation';
		}
		function arm_get_email_template( $temp_slug ) {
			 global $wpdb,$ARMemberLite;
			$res = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$ARMemberLite->tbl_arm_email_templates."` WHERE `arm_template_slug`=%s",$temp_slug) ); //phpcs:ignore --Reason $ARMemberLite->tbl_arm_email_templates is a table name
			if ( ! empty( $res ) ) {
				$res->arm_template_subject = isset( $res->arm_template_subject ) ? stripslashes_deep( $res->arm_template_subject ) : '';
				$res->arm_template_content = isset( $res->arm_template_content ) ? stripslashes_deep( $res->arm_template_content ) : '';
				return $res;
			}
			return false;
		}
		function arm_update_email_settings() {
			$arm_email_from_name     = isset( $_POST['arm_email_from_name'] ) ? sanitize_text_field( $_POST['arm_email_from_name'] ) : ''; //phpcs:ignore
			$arm_email_from_email    = isset( $_POST['arm_email_from_email'] ) ? sanitize_email( $_POST['arm_email_from_email'] ) : ''; //phpcs:ignore
			$arm_email_admin_email   = isset( $_POST['arm_email_admin_email'] ) ? sanitize_text_field( $_POST['arm_email_admin_email'] ) : ''; //phpcs:ignore
			$server                  = isset( $_POST['arm_email_server'] ) ? sanitize_text_field( $_POST['arm_email_server'] ) : ''; //phpcs:ignore
			$arm_mail_authentication = isset( $_POST['arm_mail_authentication'] ) ? intval( $_POST['arm_mail_authentication']) : '0'; //phpcs:ignore
			$smtp_mail_server        = isset( $_POST['arm_mail_server'] ) ? sanitize_text_field( $_POST['arm_mail_server'] ) : ''; //phpcs:ignore
			$smtp_mail_port          = isset( $_POST['arm_mail_port'] ) ? sanitize_text_field( $_POST['arm_mail_port'] ) : ''; //phpcs:ignore
			$smtp_mail_login_name    = isset( $_POST['arm_mail_login_name'] ) ? sanitize_text_field( $_POST['arm_mail_login_name'] ) : ''; //phpcs:ignore
			$smtp_mail_password      = isset( $_POST['arm_mail_password'] ) ? $_POST['arm_mail_password'] : ''; //phpcs:ignore
			$smtp_mail_enc           = isset( $_POST['arm_smtp_enc'] ) ? sanitize_text_field( $_POST['arm_smtp_enc'] ) : 'none'; //phpcs:ignore
			$old_settings            = $this->arm_get_all_email_settings();
			$email_tools             = ( isset( $old_settings['arm_email_tools'] ) ) ? $old_settings['arm_email_tools'] : array();
			$email_settings          = array(
				'arm_email_from_name'     => $arm_email_from_name,
				'arm_email_from_email'    => $arm_email_from_email,
				'arm_email_admin_email'   => $arm_email_admin_email,
				'arm_email_server'        => $server,
				'arm_mail_server'         => $smtp_mail_server,
				'arm_mail_port'           => $smtp_mail_port,
				'arm_mail_login_name'     => $smtp_mail_login_name,
				'arm_mail_password'       => $smtp_mail_password,
				'arm_smtp_enc'            => $smtp_mail_enc,
				'arm_email_tools'         => $email_tools,
				'arm_mail_authentication' => $arm_mail_authentication,
			);
			update_option( 'arm_email_settings', $email_settings );
		}


		function arm_get_all_email_settings() {
			 global $wpdb;
			$email_settings_unser = get_option( 'arm_email_settings' );
			$all_email_settings   = maybe_unserialize( $email_settings_unser );
			$all_email_settings   = apply_filters( 'arm_get_all_email_settings', $all_email_settings );
			return $all_email_settings;
		}
		function arm_get_single_email_template( $template_id, $fields = array() ) {
			 global $wpdb, $ARMemberLite;
			if ( $template_id == '' ) {
				return false;
			}
			$select_fields = '*';
			if ( is_array( $fields ) && ! empty( $fields ) ) {
				$select_fields = implode( ',', $fields );
			}
				$res = $wpdb->get_row( $wpdb->prepare("SELECT $select_fields FROM `".$ARMemberLite->tbl_arm_email_templates."` WHERE  `arm_template_id`=%d",$template_id) ); //phpcs:ignore --Reason $tbl_arm_email_templates is a table name
			if ( ! empty( $res ) ) {
				if ( ! empty( $res->arm_template_subject ) ) {
					$res->arm_template_subject = stripslashes_deep( $res->arm_template_subject );
				}
				if ( ! empty( $res->arm_template_content ) ) {
					$res->arm_template_content = stripslashes_deep( $res->arm_template_content );
				}
				return $res;
			}
			return false;
		}
		function arm_get_all_email_template( $field = array() ) {
			global $wpdb, $ARMemberLite;
			if ( is_array( $field ) && ! empty( $field ) ) {
				$field_name = implode( ',', $field );
				$sql        = 'SELECT ' . $field_name . ' FROM `' . $ARMemberLite->tbl_arm_email_templates . '` ORDER BY `arm_template_id` ASC ';
			} else {
				$sql = 'SELECT * FROM `' . $ARMemberLite->tbl_arm_email_templates . '` ORDER BY `arm_template_id` ASC ';
			}
			$results = $wpdb->get_results( $sql );//phpcs:ignore --Reason Query is a select without where so no need to prepare
			if ( ! empty( $results->arm_template_subject ) ) {
				$results->arm_template_subject = stripslashes_deep( $results->arm_template_subject );
			}
			if ( ! empty( $results->arm_template_content ) ) {
				$results->arm_template_content = stripslashes_deep( $results->arm_template_content );
			}
			return $results;
		}

		function arm_reset_email_template_by_id_func() {
			global $arm_email_settings, $ARMemberLite,$arm_capabilities_global;
		
			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_email_notifications'], '1' ); //phpcs:ignore --Reason:Verifying nonce
		
			$template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
			
			if (!$template_id) {
				$response = array('status'=>'error','message' => esc_html__('Template ID missing.','armember-membership'));
			}
		
			if (empty($arm_email_settings) || !method_exists($arm_email_settings, 'arm_get_single_email_template')) {
				$response = array('status'=>'error','message' => esc_html__('Email settings unavailable.','armember-membership'));
			}
		
			$template = $arm_email_settings->arm_get_single_email_template($template_id);
			if (!$template || empty($template->arm_template_slug)) {
				$response = array('status'=>'error','message' => esc_html__('Template not found.','armember-membership'));
			}
		
			$slug = $template->arm_template_slug;
		

			$defaults = $this->arm_default_email_templates();
		
			if (!isset($defaults[$slug])) {
				$response = array('status'=>'error','message' => esc_html__('Default template content not available for this slug.','armember-membership'));
			}
			else
			{
				$response = array(
					'status' => 'success',
					'message' => esc_html__('Template reseted to default.','armember-membership'),
					'template_subject' => $defaults[$slug]['arm_template_subject'],
					'template_content' => $defaults[$slug]['arm_template_content'],
				);
			}
			echo arm_pattern_json_encode($response);
			die;
		}
		
		function arm_edit_template_data() {
			 global $wpdb, $ARMemberLite, $arm_slugs, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_communication, $arm_capabilities_global;
			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_email_notifications'], '1' ); //phpcs:ignore --Reason:Verifying nonce
			$return = array( 'status' => 'error' );
			if ( isset( $_REQUEST['action'] ) && isset( $_REQUEST['temp_id'] ) && $_REQUEST['temp_id'] != '' ) { //phpcs:ignore
				$template_id = intval( $_REQUEST['temp_id'] ); //phpcs:ignore
				$temp_detail = $arm_email_settings->arm_get_single_email_template( $template_id );
				if ( ! empty( $temp_detail ) ) {
					$return = array(
						'status'               => 'success',
						'id'                   => $template_id,
						'popup_heading'        => esc_html( stripslashes( $temp_detail->arm_template_name ) ),
						'arm_template_slug'    => $temp_detail->arm_template_slug,
						'arm_template_subject' => esc_html( stripslashes( $temp_detail->arm_template_subject ) ),
						'arm_template_content' => stripslashes_deep( $temp_detail->arm_template_content ),
						'arm_template_status'  => $temp_detail->arm_template_status,
					);
				}
			}
			echo arm_pattern_json_encode( $return );
			exit;
		}
		function arm_submit_email_template() {
			global $wpdb, $ARMemberLite, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_email_notifications'], '1' ); //phpcs:ignore --Reason:Verifying nonce

			$response = array(
				'type' => 'error',
				'msg'  => esc_html__( 'Sorry, Something went wrong. Please try again.', 'armember-membership' ),
			);
			if ( ! empty( $_POST['arm_template_id'] ) && intval($_POST['arm_template_id']) != 0 ) { //phpcs:ignore
				$template_id = intval( $_POST['arm_template_id'] ); //phpcs:ignore
				if ( ! $template_id ) {
					$template_id = '';
				}
				if ( ! empty( $template_id ) ) {
					$arm_email_template_subject = ( ! empty( $_POST['arm_template_subject'] ) ) ? sanitize_text_field( $_POST['arm_template_subject'] ) : ''; //phpcs:ignore
					$arm_email_template_content = ( ! empty( $_POST['arm_template_content'] ) ) ? wp_kses_post( $_POST['arm_template_content'] ) : ''; //phpcs:ignore
					$arm_email_template_status  = ( ! empty( $_POST['arm_template_status'] ) ) ? intval( $_POST['arm_template_status'] ) : 0; //phpcs:ignore
					$temp_data                  = array(
						'arm_template_subject' => $arm_email_template_subject,
						'arm_template_content' => $arm_email_template_content,
						'arm_template_status'  => $arm_email_template_status,
					);
					$update_temp                = $wpdb->update( $ARMemberLite->tbl_arm_email_templates, $temp_data, array( 'arm_template_id' => $template_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$response                   = array(
						'type' => 'success',
						'msg'  => esc_html__( 'Email Template Updated Successfully.', 'armember-membership' ),
					);
				}
			}
			echo arm_pattern_json_encode( $response );
			exit;
		}
		function arm_update_email_template_status( $posted_data = array() ) {
			global $wpdb, $ARMemberLite, $arm_capabilities_global;

			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_email_notifications'], '1' ); //phpcs:ignore --Reason:Verifying nonce

			$response = array(
				'type' => 'error',
				'msg'  => esc_html__( 'Sorry, Something went wrong. Please try again.', 'armember-membership' ),
			);
			if ( ! empty( $_POST['arm_template_id'] ) && $_POST['arm_template_id'] != 0 && intval( $_POST['arm_template_id'] ) ) { //phpcs:ignore
				$template_id = intval( $_POST['arm_template_id'] ); //phpcs:ignore
				if ( ! empty( $template_id ) ) {
					$arm_email_template_status = ( ! empty( $_POST['arm_template_status'] ) ) ? intval( $_POST['arm_template_status'] ) : 0; //phpcs:ignore
					$temp_data                 = array(
						'arm_template_status' => $arm_email_template_status,
					);
					$update_temp               = $wpdb->update( $ARMemberLite->tbl_arm_email_templates, $temp_data, array( 'arm_template_id' => $template_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$response                  = array(
						'type' => 'success',
						'msg'  => esc_html__( 'Email Template Updated Successfully.', 'armember-membership' ),
					);
				}
			}
			echo arm_pattern_json_encode( $response );
			exit;
		}
		function arm_insert_default_email_templates() {
			 global $wpdb, $ARMemberLite;
			$default_email_template = $this->arm_default_email_templates();
			if ( ! empty( $default_email_template ) ) {
				foreach ( $default_email_template as $slug => $email_template ) {
					$oldTemp = $this->arm_get_email_template( $slug );
					if ( ! empty( $oldTemp ) ) {
						continue;
					} else {
						$email_template['arm_template_slug']   = $slug;
						$email_template['arm_template_status'] = '1';
						$ins                                   = $wpdb->insert( $ARMemberLite->tbl_arm_email_templates, $email_template ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					}
				}
			}
		}
		function arm_default_email_templates() {
			global $wp, $wpdb, $ARMemberLite;
			$temp_slugs      = $this->templates;

	$email_templates = array(
		$temp_slugs->new_reg_user_admin => array(
			'arm_template_name' => esc_html__('Signup Completed Notification To Admin', 'armember-membership'),
			'arm_template_subject' => sprintf('New User Registration on %s- Account Details', '{ARM_BLOGNAME}'),
			'arm_template_content' => sprintf( '%sHello Administrator,%s This message informs you that a new user has successfully registered on %s. Below are the details of the newly registered user:%s Full Name%s Email Address%s First Name%s Last Name%s Username%s To view more detailed information about this user or to take any necessary actions, please click the link below:%s Thank you for being a part of %s',
'<p>', '</p><p>', '<strong>{ARM_BLOGNAME}</strong>', '</p>
<table>
	<tr>
		<th>','</th>
		<td>{ARM_NAME}</td>
	</tr>
	<tr>
		<th>','</th>
		<td>{ARM_EMAIL}</td>
	</tr>
	<tr>
		<th>','</th>
		<td>{ARM_FIRST_NAME}</td>
	</tr>
	<tr>
		<th>','</th>
		<td>{ARM_LAST_NAME}</td>
	</tr>
	<tr>
		<th>','</th>
		<td>{ARM_USERNAME}</td>
	</tr>
</table>
<p>',
'</p><p>{ARM_PROFILE_LINK}</p> 
<p>', '<strong>{ARM_BLOGNAME}</strong>. </p>' )
),
		
		
			$temp_slugs->new_reg_user_with_payment => array(
				'arm_template_name'    => esc_html__('Signup Completed (With Payment) Notification To User', 'armember-membership'),
				'arm_template_subject' => sprintf( 'Your Subscription to %s is Complete  Payment Confirmation', '{ARM_BLOGNAME}' ),
				'arm_template_content' => sprintf( '%sHello %s Thank you for subscribing to %s. We are pleased to welcome you aboard as a member and appreciate your choice to join us under the %s plan.%s To manage or update your membership information, please visit the following link:%s Below is a summary of your most recent payment details for your reference:%s Date of Payment%s Paid With%s Plan Name%s Payment Mode%s Amount%s Transaction ID%s If you have any questions or require further assistance, please do not hesitate to contact us. We look forward to providing you with an excellent experience at %s Best regards,%s The %s Team%s', 
'<p>',
'{ARM_NAME},</p><p>',
'{ARM_BLOGNAME}',
'{ARM_PLAN}',
'</p><p> {ARM_PROFILE_LINK}',
'</p><p>',
'</p>
<table>
	<tr>
		<th align="right"><strong>',
'</strong></th><td>{ARM_PAYMENT_DATE}</td></tr>
	<tr><th align="right"><strong>',
'</strong></th><td>{ARM_PAYMENT_GATEWAY}</td></tr>
	<tr><th align="right"><strong>',
'</strong></th><td>{ARM_PLAN}</td></tr>
	<tr><th align="right"><strong>',
'</strong></th><td>{ARM_PAYMENT_TYPE}</td></tr>
	<tr><th align="right"><strong>',
'</strong></th><td>{ARM_PLAN_AMOUNT}</td></tr>
	<tr><th align="right"><strong>',
'</strong></th><td>{ARM_TRANSACTION_ID}</td></tr>
</table><p>',
'{ARM_BLOGNAME}. </p><p>',
'</p><p>',
'{ARM_BLOGNAME}',
'</p>')
),
				
		$temp_slugs->new_reg_user_without_payment => array(
			'arm_template_name'    => esc_html__('Signup Completed (Without Payment) Notification To User', 'armember-membership'),
			'arm_template_subject' => sprintf( 'Welcome to %s  - Membership Confirmation', '{ARM_BLOGNAME}'),
			'arm_template_content' => sprintf( '%s Hello %s We are happy to welcome you as you are our valued member! Thank you for registering at %s You can review and manage your membership details. Please click the provided link:%s If you have any queries, you can contact us! We look forward to serving you.%s Best Regards,%s The %s Team %s',
'<p>',
'{ARM_NAME},</p><p>',
'{ARM_BLOGNAME}.</p><p>',
'</p><p>{ARM_PROFILE_LINK}</p>',
'</p><p>',
'</p><p>',
'{ARM_BLOGNAME}',
'</p>')
),
				
			
$temp_slugs->email_verify_user => array(
		'arm_template_name'    => esc_html__('Email Verification', 'armember-membership'),
		'arm_template_subject' => sprintf('Email Confirmation Required to Activate Your Account'),
		'arm_template_content' => sprintf(
			"%sDear %s Thank you for registering with %s. To complete the sign-up process and gain access to your account, we kindly request that you confirm your email address.%s Please click on the link below to validate and activate your account:%s If you did not sign up for this account, please disregard this email.%s If you need any assistance or encounter issues, feel free to reach out to us. We'll be happy to help!%s Best regards,%s The %s Team%s",
'<p>',
'{ARM_NAME},</p><p>',
'{ARM_BLOGNAME}',
'</p><p>',
'</p><p>{ARM_VALIDATE_URL}</p><p>',
'</p><p>',
'</p><p>',
'<br>',
'{ARM_BLOGNAME}',
'</p>')
),
					
		$temp_slugs->account_verified_user => array(
			'arm_template_name'    => esc_html__('Email Verified', 'armember-membership'),
			'arm_template_subject' => sprintf('Your Account is Now Verified!'),
			'arm_template_content' => sprintf(
				"%sHi %s We're excited to let you know that your account has been successfully verified at %s You can log in to the site with the:%s Page: %s Username: %sPassword: (Set while signing up at the site)%sIf you have any questions or need assistance, feel free to reach out.%sThanks for being part of our community, and have a wonderful day!%sBest regards,%sThe %s Team%s",
'<p>',
'{ARM_NAME},</p><p>',
'{ARM_BLOGNAME}!</p><p>',
'</p>','{ARM_LOGIN_URL}', '<p>',
'{ARM_USERNAME}<br>',
'</p><p>',
'</p><p>',
'</p><p>',
'</p><p>',
'<br>',
'{ARM_BLOGNAME}',
'</p>')
),

		$temp_slugs->change_password_user => array(
			'arm_template_name'    => esc_html__('Change Password', 'armember-membership'),
			'arm_template_subject' => sprintf('Your Password Has Been Successfully Changed'),
		'arm_template_content' => sprintf(
		"%sHi %s To your request for a password reset through your account, your password has been successfully updated. To access your account, please log in using the following link:%s With your username: %s Password: %s(Newly set password)%s If you did not request this change or believe this was made in error, please contact us immediately for assistance.%sThank you, and have a great day!%sBest regards,%sThe %s Team%s",
'<p>','<strong>{ARM_NAME}</strong>,</p>
<p>','</p>
<p>{ARM_LOGIN_URL}</p>
<p>','<strong>{ARM_USERNAME}</strong></p>
<p>','<strong>','</strong></p>
<p>','</p>
<p>','</p>
<p>','</p>
<p>','</p>
<p>','{ARM_BLOGNAME}','</p>')
),
		$temp_slugs->forgot_passowrd_user => array(
			'arm_template_name'    => esc_html__('Forgot Password', 'armember-membership'),
			'arm_template_subject' => sprintf('Password Reset Request for %s','{ARM_BLOGNAME}'),
			'arm_template_content' => sprintf("%sHi %sWe received a request to reset the password for your account:%sUsername: %sEmail: %sIf you did not make this request, you can safely ignore this email, and no changes will be made to your account.%s To proceed with resetting your password, please click the link below:%sIf you encounter any issues or have questions, feel free to reach out to us at %s Thanks,%sThe %s Team%s",
'<p>',
'<strong>{ARM_NAME}</strong>,</p><p>',
'</p><p>',
'<strong>{ARM_USERNAME}</strong><br>',
'<strong>{ARM_EMAIL}</strong></p>
<p>','</p><p>',
'</p> <p>{ARM_RESET_PASSWORD_LINK}</p>
<p>','{ARM_ADMIN_EMAIL}.</p> 
<p>','</p><p>', '{ARM_BLOGNAME}','</p>')
),
						
		$temp_slugs->profile_updated_user => array(
			'arm_template_name'    => esc_html__('Profile Updated', 'armember-membership'),
			'arm_template_subject' => sprintf('Your Account Details Have Been Updated Successfully!'),
			'arm_template_content' => sprintf(
				"%sHello %sWe're happy to inform you that your account details have been successfully updated! To review and manage your profile, click the link below:%sIf you have any further queries on the updated information, kindly let us know through %sThank you for being a valued part of our community. Have a fantastic day ahead!%sWarm regards,%sThe %s Team%s",
'<p>',
'<strong>{ARM_NAME}</strong>,</p>
<p>','</p><p>{ARM_PROFILE_LINK}</p>
<p>','{ARM_ADMIN_EMAIL}.</p>
<p>','</p>
<p>','</p>
<p>','{ARM_BLOGNAME}','</p>')
),
						
		 
		$temp_slugs->profile_updated_notification_to_admin => array(
			'arm_template_name' => esc_html__('Profile Updated Notification To Admin', 'armember-membership'),
			'arm_template_subject' => sprintf('%s\'s Account Details Updated on %s','{ARM_USERNAME}', '{ARM_BLOGNAME}'),
			'arm_template_content' => sprintf(
					'%sDear Administrator,%s We would like to inform you that the account of %s has been successfully updated on %s. Below are a few basic details of the member. Kindly review their account and check out the updated details:%s Username%s Email%s First Name%s Last Name%s Thank you for your attention to this matter.',
		'<p>', '</p><p>', '<strong>{ARM_USERNAME}</strong>', '<strong>{ARM_BLOGNAME}</strong>', '</p>
<table>
	<tr>
		<th>', '</th>
		<td>{ARM_USERNAME}</td>
	</tr>
	<tr>
		<th>', '</th>
		<td>{ARM_EMAIL}</td>
	</tr>
	<tr>
		<th>', '</th>
		<td>{ARM_FIRST_NAME}</td>
	</tr>
	<tr>
		<th>', '</th>
		<td>{ARM_LAST_NAME}</td>
	</tr>
</table>
<p>'),
),
		
						
		$temp_slugs->grace_failed_payment => array(
			'arm_template_name' => esc_html__('Grace Period For Failed Payment', 'armember-membership'),
			'arm_template_subject' => sprintf('Action Required for your Failed Payment at %s','{ARM_BLOGNAME}'),
			'arm_template_content' => sprintf(
					"%sDear %s We wanted to inform you that there was an issue processing your recurring payment for your %s subscription at %s. Here are the details of the payment attempt:%s Payment Method%s Amount%s Transaction ID%s We recommend reaching out to your payment service provider to resolve the issue. Please be aware that if no action is taken within the next %s days, your current membership may lapse.%s If you have any questions or need assistance, don't hesitate to contact us at %s. We're happy to help!%s Thank you, and have a great day!%s Best regards,%s The %s Team",
'<p>', '{ARM_NAME},</p><p>',
'{ARM_PLAN}', '{ARM_BLOGNAME}', '</p>
<table>
	<tr>
		<th>', '</th>
		<td>{ARM_PAYMENT_GATEWAY}</td>
	</tr>
	<tr>
		<th>', '</th>
		<td>{ARM_PLAN_AMOUNT}</td>
	</tr>
	<tr>
		<th>', '</th>
		<td>{ARM_TRANSACTION_ID}</td>
	</tr>
</table>
<p>',
		'{ARM_GRACE_PERIOD_DAYS}', '</p><p>',
		'{ARM_BLOGNAME}', '</p><p>',
		'</p><p>', '</p><p>', '{ARM_BLOGNAME}')
),
		
		$temp_slugs->grace_eot => array(
			'arm_template_name' => esc_html__('User Enters Grace Period Notification', 'armember-membership'),
			'arm_template_subject' => sprintf('Reminder for membership expiration at %s','{ARM_BLOGNAME}'),
			'arm_template_content' => sprintf(
				"%sHi %s We wanted to let you know that your %s membership has just expired at However, you can still access our website without any issues for %s days.%s If you'd like to renew or update your membership plan, please click the link below:%s Note: %s If you do not renew or change your membership within %s days, the system will automatically take the necessary action.%s Have a great day!%s Best regards,%s The %s Team",
'<p>', '{ARM_NAME},</p>
<p>','{ARM_PLAN}', '{ARM_BLOGNAME}.</p>
<p>','{ARM_GRACE_PERIOD_DAYS}', '</p>
<p>','</p> 
<p>{ARM_BLOG_URL}</p> 
<p><strong>','</strong> ','{ARM_GRACE_PERIOD_DAYS}', '</p>
<p>','</p>
<p>', '</p>
<p>', '{ARM_BLOGNAME}')
		),
		$temp_slugs->failed_payment_admin => array(
			'arm_template_name' => esc_html__('Failed Payment Notification To Admin', 'armember-membership'),
			'arm_template_subject' => sprintf('Failed Payment for %s Membership - Action Required','{ARM_PLAN}'),
			'arm_template_content' => sprintf(
					'%sHello Administrator,%s This is a reminder that the recurring payment for %s membership for the following member named as %s has failed at %s Here are the Member Details:%s Username%s Email%s Payment Method%s Amount%s Please check whether the necessary actions have been taken in the system.%s Best regards,%s Team%s',
'<p>', '</p>
<p>','{ARM_PLAN}', '{ARM_NAME}', '{ARM_BLOGNAME}:</p>
<p>','</p>
<table>
	<tr>
		<th><strong>', '</strong></th>
		<td>{ARM_USERNAME}</td>
	</tr>
	<tr>
		<th><strong>','</strong></th>
		<td>{ARM_EMAIL}</td>
	</tr>
	<tr>
		<th><strong>', '</strong></th>
		<td>{ARM_PAYMENT_GATEWAY}</td>
	</tr> 
	<tr>
		<th><strong>','</strong></th>
		<td>{ARM_PLAN_AMOUNT}</td>
	</tr>
</table>
<p>','</p> 
<p>', '<br>{ARM_BLOGNAME}','</p>')
		),
		
		$temp_slugs->on_menual_activation => array(
			'arm_template_name' => esc_html__('Manual User Activation', 'armember-membership'),
			'arm_template_subject' => sprintf( 'Your account has been activated at %s', '{ARM_BLOGNAME}' ),
			'arm_template_content' =>sprintf('%sHello %s We are pleased to inform you that your account has been successfully activated. Welcome aboard! To get started, please click on the following link: %s Review and take a tour at %s, we hope you have smooth experience with us!%s Have a great day!%s Best regards,%s Team %s',
'<p>', '{ARM_NAME},</p> 
<p>','<a href="{ARM_LOGIN_URL}">{ARM_LOGIN_URL}</a>.</p>
<p>','{ARM_BLOGNAME}', '</p>
<p>','</p>
<p>','</p>
<p>','<br> {ARM_BLOGNAME}','</p>')
		),		
	);
			$email_templates = apply_filters( 'arm_default_email_templates', $email_templates );
			return $email_templates;
		}

		function arm_send_test_email_callback() {
			global $arm_email_settings, $arm_global_settings, $arm_capabilities_global, $ARMemberLite,$all_email_settings;
		
			$ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_email_notifications'], '1');
		
			$template_id = isset($_POST['temp_id']) ? intval($_POST['temp_id']) : 0;
			if ($template_id <= 0) {
				wp_send_json_error(['msg' => 'Invalid template ID.']);
			}
		
			$temp_detail = $arm_email_settings->arm_get_single_email_template($template_id);
			if (empty($temp_detail)) {
				wp_send_json_error(['msg' => 'Email template not found.']);
			}
		
			$subject = str_replace('{ARM_BLOGNAME}', get_bloginfo('name'), stripslashes($temp_detail->arm_template_subject));
			$content = str_replace('{ARM_BLOGNAME}', get_bloginfo('name'), stripslashes($temp_detail->arm_template_content));
		
			$all_email_settings = $arm_email_settings->arm_get_all_email_settings();
			$admin_email        = ( ! empty( $all_email_settings['arm_email_admin_email'] ) ) ? $all_email_settings['arm_email_admin_email'] : get_option( 'admin_email' );
		
			if (empty($admin_email)) {
				wp_send_json_error(['msg' => 'Admin email address is missing.']);
			}
			$email_addresses = [];
			$raw_emails = strpos($admin_email, ',') !== false ? explode(',', $admin_email) : [$admin_email];
		
			foreach ($raw_emails as $email) {
				$email = trim($email);
				if (is_email($email)) {
					$email_addresses[] = $email;
				}
			}
		
			if (empty($email_addresses)) {
				wp_send_json_error(['msg' => 'No valid email addresses found.']);
			}
		
			if (!isset($arm_global_settings) || !method_exists($arm_global_settings, 'arm_send_message_to_armember_admin_users')) {
				wp_send_json_error(['msg' => 'Mail function not available.']);
			}
		
			$all_sent = true;
		
			$sent = $arm_global_settings->arm_send_message_to_armember_admin_users($email_user, $subject, $content);
			if (!$sent) {
				$all_sent = false;
			}
	
			$response = [
				'status' => $all_sent ? 'success' : 'error',
				'msg' => $all_sent ? 'Test email sent to' . esc_html(implode(', ', $email_addresses)) : 'Failed to send test email.',
				'id' => $template_id,
				'popup_heading' => esc_html(stripslashes($temp_detail->arm_template_name ?? $temp_detail->arm_message_type ?? '')),
				'arm_template_slug' => $temp_detail->arm_template_slug ?? $temp_detail->arm_message_type ?? '',
				'arm_template_subject' => esc_html($subject),
				'arm_template_content' => $content,
				'arm_template_status' => $temp_detail->arm_template_status ?? $temp_detail->arm_message_status ?? '',
			];
		
			$response = apply_filters('arm_email_attachment_file_outside', $response);
		
			if ($all_sent) {
				do_action('arm_debug_log', 'Test email sent via arm_wp_mail to:' . implode(', ', $email_addresses));
				wp_send_json_success($response);
			} else {
				do_action('arm_debug_log', 'Failed to send test email via arm_wp_mail to: ' . implode(', ', $email_addresses));
				wp_send_json_error($response);
			}
		}
		
	}
}
global $arm_email_settings;
$arm_email_settings = new ARM_email_settings_Lite();
