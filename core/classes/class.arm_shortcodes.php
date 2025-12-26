<?php
if ( ! class_exists( 'ARM_shortcodes_Lite' ) ) {

	class ARM_shortcodes_Lite {

		function __construct() {
			global $wpdb, $ARMemberLite, $arm_slugs;
			/* Build Shortcodes For `armif` */

			/* Build Shortcodes For Subscription Plans */
			add_shortcode( 'arm_plan', array( $this, 'arm_plan_shortcode_func' ) );
			add_shortcode( 'arm_plan_not', array( $this, 'arm_plan_not_shortcode_func' ) );

			add_shortcode( 'arm_restrict_content', array( $this, 'arm_restrict_content_shortcode_func' ) );
			add_shortcode( 'arm_content', array( $this, 'arm_content_shortcode_func' ) );
			add_shortcode( 'arm_not_login_content', array( $this, 'arm_not_login_content_shortcode_func' ) );
			add_shortcode( 'arm_template', array( $this, 'arm_template_shortcode_func' ) );
			add_shortcode( 'arm_account_detail', array( $this, 'arm_account_detail_shortcode_func' ) );
			add_shortcode( 'arm_view_profile', array( $this, 'arm_view_profile_shortcode_func' ) );
			add_shortcode( 'arm_member_transaction', array( $this, 'arm_member_transaction_func' ) );
			add_shortcode( 'arm_close_account', array( $this, 'arm_close_account_shortcode_func' ) );
			add_shortcode( 'arm_membership', array( $this, 'arm_membership_detail_shortcode_func' ) );

			add_shortcode( 'arm_username', array( $this, 'arm_username_func' ) );
			add_shortcode( 'arm_userid', array( $this, 'arm_userid_func' ) );
			add_shortcode( 'arm_displayname', array( $this, 'arm_displayname_func' ) );
			add_shortcode( 'arm_avatar', array( $this, 'arm_avatar_func' ) );
			add_shortcode( 'arm_if_user_in_trial', array( $this, 'arm_if_user_in_trial_func' ) );
			add_shortcode( 'arm_not_if_user_in_trial', array( $this, 'arm_not_if_user_in_trial_func' ) );
			add_shortcode( 'arm_firstname_lastname', array( $this, 'arm_firstname_lastname_func' ) );
			add_shortcode( 'arm_user_plan', array( $this, 'arm_user_plan_func' ) );
			add_shortcode( 'arm_usermeta', array( $this, 'arm_usermeta_func' ) );

			add_shortcode( 'arm_user_planinfo', array( $this, 'arm_user_planinfo_func' ) );

			add_action( 'wp_ajax_arm_directory_paging_action', array( $this, 'arm_directory_paging_action' ) );
			add_action( 'wp_ajax_nopriv_arm_directory_paging_action', array( $this, 'arm_directory_paging_action' ) );
			add_action( 'wp_ajax_arm_transaction_paging_action', array( $this, 'arm_transaction_paging_action' ) );
			add_action( 'wp_ajax_arm_close_account_form_submit_action', array( $this, 'arm_close_account_form_action' ) );

			/* Add Buttons Into WordPress(TinyMCE) Editor */
			add_action( 'admin_footer', array( $this, 'arm_insert_shortcode_popup' ) );
			add_action( 'media_buttons', array( $this, 'arm_insert_shortcode_button' ), 20 );
			add_action( 'admin_init', array( $this, 'arm_add_tinymce_styles' ) );
			add_action( 'pre_get_posts', array( $this, 'arm_add_tinymce_styles' ) );
			/* Add Font Support Into WordPress(TinyMCE) Editor */
			add_filter( 'mce_buttons', array( $this, 'arm_editor_mce_buttons' ) );
			add_filter( 'mce_buttons_2', array( $this, 'arm_editor_mce_buttons_2' ) );
			add_filter( 'tiny_mce_before_init', array( $this, 'arm_editor_font_sizes' ) );
			/* Shortcode for Display Current User Login History */
		}



		function arm_plan_shortcode_func( $atts, $content, $tag ) {
			if ( current_user_can( 'administrator' ) ) {
				return do_shortcode( $content );
			}
			global $wp, $wpdb, $current_user, $arm_lite_errors, $ARMemberLite, $arm_global_settings, $arm_subscription_plans;
			/* ---------------------/.Begin Set Shortcode Attributes--------------------- */
			$defaults = array(
				'id'      => 0,
				'message' => '',
			);
			/* Extract Shortcode Attributes */
			$opts = shortcode_atts( $defaults, $atts, $tag );
			$opts = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $opts ); //phpcs:ignore
			extract( $opts );
			/* ---------------------/.End Set Shortcode Attributes--------------------- */
			if ( ! empty( $id ) && $id != 0 ) {
				$user_id = get_current_user_id();
				if ( ! empty( $user_id ) && $user_id != 0 ) {
					$user_plans = get_user_meta( $user_id, 'arm_user_plan_ids', true );
					$user_plans = ! empty( $user_plans ) ? $user_plans : array();
					if ( in_array( $id, $user_plans ) ) {
						return do_shortcode( $content );
					}
				}
			}

			return $message;
		}

		function arm_plan_not_shortcode_func( $atts, $content, $tag ) {
			if ( current_user_can( 'administrator' ) ) {
				return do_shortcode( $content );
			}
			global $wp, $wpdb, $current_user, $arm_lite_errors, $ARMemberLite, $arm_global_settings, $arm_subscription_plans;
			/* ---------------------/.Begin Set Shortcode Attributes--------------------- */
			$defaults = array(
				'id'      => 0,
				'message' => '',
			);
			/* Extract Shortcode Attributes */
			$opts = shortcode_atts( $defaults, $atts, $tag );
			$opts = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $opts ); //phpcs:ignore
			extract( $opts );
			/* ---------------------/.End Set Shortcode Attributes--------------------- */
			if ( ! empty( $id ) && $id != 0 ) {
				$user_id = get_current_user_id();
				if ( ! empty( $user_id ) && $user_id != 0 ) {
					$user_plans = get_user_meta( $user_id, 'arm_user_plan_ids', true );
					$user_plans = ! empty( $user_plans ) ? $user_plans : array();
					if ( ! in_array( $id, $user_plans ) ) {
						return do_shortcode( $content );
					}
				}
			}
			return $message;
		}

		function arm_restrict_content_shortcode_func( $atts, $content, $tag ) {
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}
			/* ---------------------/.Begin Set Shortcode Attributes--------------------- */
			$defaults = array(
				'type' => 'hide', /* Shortcode behaviour type */
				'plan' => '', /* Plan Id or comma separated plan ids. */
			);
			/* Extract Shortcode Attributes */
			$opts = shortcode_atts( $defaults, $atts, $tag );
			$opts = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $opts ); //phpcs:ignore
			extract( $opts );
			/* ---------------------/.End Set Shortcode Attributes--------------------- */
			global $wp, $wpdb, $current_user, $arm_lite_errors, $ARMemberLite, $arm_global_settings, $arm_subscription_plans;
			$main_content = $else_content = null;
			$else_tag     = '[armelse]';
			if ( strpos( $content, $else_tag ) !== false ) {
				list($main_content, $else_content) = explode( $else_tag, $content, 2 );
			} else {
				$main_content = $content;
			}
			/* Always Display Content For Admins */
			if ( current_user_can( 'administrator' ) ) {
				return do_shortcode( $main_content );
			}
			$hasaccess       = false;
			$isLoggedIn      = is_user_logged_in();
			$current_user_id = get_current_user_id();
			$arm_user_plan   = get_user_meta( $current_user_id, 'arm_user_plan_ids', true );
			$arm_user_plan   = ! empty( $arm_user_plan ) ? $arm_user_plan : array();
			if ( ! empty( $arm_user_plan ) ) {
				$suspended_plan_ids = get_user_meta( $current_user_id, 'arm_user_suspended_plan_ids', true );
				if ( ! empty( $suspended_plan_ids ) ) {
					foreach ( $suspended_plan_ids as $suspended_plan_id ) {
						if ( in_array( $suspended_plan_id, $arm_user_plan ) ) {
							unset( $arm_user_plan[ array_search( $suspended_plan_id, $arm_user_plan ) ] );
						}
					}
				}
			}
			if ( ! empty( $plan ) ) {
				/* Plans Section */
				if ( strpos( $plan, ',' ) ) {
					$plans = explode( ',', $plan );
				} else {
					$plans = array( $plan );
				}
				$plans      = array_filter( $plans );
				$registered = false;
				if ( in_array( 'registered', $plans ) ) {
					$registered = true;
					$rkey       = array_search( 'registered', $plans );
					unset( $plans[ $rkey ] );
				}
				$unregistered = false;
				if ( in_array( 'unregistered', $plans ) ) {
					$unregistered = true;
					$ukey         = array_search( 'unregistered', $plans );
					unset( $plans[ $ukey ] );
				}
				$return_array = array_intersect( $arm_user_plan, $plans );
				if ( $type == 'show' ) {
					if ( $isLoggedIn ) {
						if ( $registered ) {
							$hasaccess = true;
						}

						if ( ! empty( $plans ) && ! empty( $return_array ) ) {
							$hasaccess = true;
						}
						if ( ! empty( $arm_user_plan ) && in_array( 'any_plan', $plans ) ) {
							$hasaccess = true;
						}
					} else {
						/* Show Content To Non LoggedIn Members */
						if ( $unregistered ) {
							$hasaccess = true;
						}
					}
				} else {
					if ( $isLoggedIn ) {
						/* Need to check this condition and confirm */
						if ( $unregistered ) {
							$hasaccess = true;
						}
						/* Need to check this condition and confirm */

						if ( ! empty( $plans ) && empty( $return_array ) ) {
							$hasaccess = true;
						}
						if ( ! empty( $arm_user_plan ) && in_array( 'any_plan', $plans ) ) {
							$hasaccess = false;
						}
					} else {
						/* Hide Content From Non LoggedIn Members */
						if ( ! $unregistered ) {
							$hasaccess = true;
						}
					}
				}
			} else {
				if ( $type == 'show' ) {
					$hasaccess = true;
				}
			}
			$hasaccess = apply_filters( 'arm_restrict_content_shortcode_hasaccess', $hasaccess, $opts );
			if ( $hasaccess ) {
				return do_shortcode( $main_content );
			} else {
				return do_shortcode( $else_content );
			}
		}

		function arm_if_user_in_trial_func( $atts, $content, $tag ) {
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}
			$main_content = $content;
			$else_content = null;
			/* Always Display Content For Admins */
			if ( current_user_can( 'administrator' ) ) {
				return do_shortcode( $main_content );
			}

			$hasaccess = false;
			if ( is_user_logged_in() ) {
				$current_user_id = get_current_user_id();
				$arm_user_plans  = get_user_meta( $current_user_id, 'arm_user_plan_ids', true );

				$hasaccess = false;
				if ( ! empty( $arm_user_plans ) && is_array( $arm_user_plans ) ) {

					foreach ( $arm_user_plans as $arm_user_plan ) {
						/* Plans Section */
						$planData = get_user_meta( $current_user_id, 'arm_user_plan_' . $arm_user_plan, true );
						if ( ! empty( $planData ) ) {
							$planDetail = $planData['arm_current_plan_detail'];
							if ( ! empty( $planDetail ) ) {
								$plan_info = new ARM_Plan_Lite( 0 );
								$plan_info->init( (object) $planDetail );
							} else {
								$plan_info = new ARM_Plan_Lite( $arm_user_plan );
							}
							if ( $plan_info->is_recurring() ) {
								$arm_is_trial = $planData['arm_is_trial_plan'];
								if ( $arm_is_trial == 1 ) {
									$arm_plan_trial_expiry_date = $planData['arm_trial_end'];
									if ( $arm_plan_trial_expiry_date != '' ) {
										$now = current_time( 'timestamp' );
										if ( $now <= $arm_plan_trial_expiry_date ) {
											$hasaccess = true;
											break;
										}
									}
								}
							}
						}
					}
				}
			}
			$main_content = apply_filters( 'arm_is_user_in_trial_shortcode_content', $main_content );
			$else_content = apply_filters( 'arm_is_user_in_trial_shortcode_else_content', $else_content );
			$hasaccess    = apply_filters( 'arm_is_user_in_trial_shortcode_hasaccess', $hasaccess );
			if ( $hasaccess ) {
				return do_shortcode( $main_content );
			} else {
				return do_shortcode( $else_content );
			}
		}

		function arm_not_if_user_in_trial_func( $atts, $content, $tag ) {
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}

			$main_content = $content;
			$else_content = null;
			/* Always Display Content For Admins */
			if ( current_user_can( 'administrator' ) ) {
				return do_shortcode( $main_content );
			}
			$hasaccess = false;
			if ( is_user_logged_in() ) {
				$current_user_id = get_current_user_id();
				$arm_user_plans  = get_user_meta( $current_user_id, 'arm_user_plan_ids', true );

				if ( ! empty( $arm_user_plans ) && is_array( $arm_user_plans ) ) {
					foreach ( $arm_user_plans as $arm_user_plan ) {
						$hasaccess = false;
						/* Plans Section */
						$planData   = get_user_meta( $current_user_id, 'arm_user_plan_' . $arm_user_plan, true );
						$planDetail = $planData['arm_current_plan_detail'];
						if ( ! empty( $planDetail ) ) {
							$plan_info = new ARM_Plan_Lite( 0 );
							$plan_info->init( (object) $planDetail );
						} else {
							$plan_info = new ARM_Plan_Lite( $arm_user_plan );
						}
						if ( $plan_info->is_recurring() ) {
							$arm_is_trial = $planData['arm_is_trial_plan'];
							if ( $arm_is_trial == 1 ) {
								$arm_plan_trial_expiry_date = $planData['arm_trial_end'];
								if ( $arm_plan_trial_expiry_date != '' ) {
									$now = current_time( 'timestamp' );
									if ( $now > $arm_plan_trial_expiry_date ) {
										$hasaccess = true;
									}
								}
							} else {
								$hasaccess = true;
							}
						} else {
							$hasaccess = true;
						}

						if ( $hasaccess == false ) {
							break;
						}
					}
				} else {
					$hasaccess = true;
				}
			}

			$main_content = apply_filters( 'arm_not_is_user_in_trial_shortcode_content', $main_content );
			$else_content = apply_filters( 'arm_not_is_user_in_trial_shortcode_else_content', $else_content );
			$hasaccess    = apply_filters( 'arm_not_is_user_in_trial_shortcode_hasaccess', $hasaccess );
			if ( $hasaccess ) {
				return do_shortcode( $main_content );
			} else {
				return do_shortcode( $else_content );
			}
		}

		function arm_content_shortcode_func( $atts, $content, $tag ) {
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}
			/* Always Display Content For Admins */
			if ( current_user_can( 'administrator' ) ) {
				return do_shortcode( $content );
			}
			/* ---------------------/.Begin Set Shortcode Attributes--------------------- */
			$defaults = array(
				'plan'    => 'all', /* Plan Id or comma separated plan ids. */
				'message' => '', /* Message for restricted area. */
			);
			/* Extract Shortcode Attributes */
			$opts = shortcode_atts( $defaults, $atts, $tag );
			$opts = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $opts ); //phpcs:ignore
			extract( $opts );
			/* ---------------------/.End Set Shortcode Attributes--------------------- */
			global $wp, $wpdb, $current_user, $arm_lite_errors, $ARMemberLite, $arm_global_settings, $arm_subscription_plans;
			$hasaccess = true;
			/* Check if User is logged in */
			if ( is_user_logged_in() ) {
				$user_id       = $current_user->ID;
				$arm_user_plan = get_user_meta( $user_id, 'arm_user_plan_ids', true );
				$arm_user_plan = ! empty( $arm_user_plan ) ? $arm_user_plan : array();
				/* Plans Section */
				if ( strpos( $plan, ',' ) ) {
					$plans = explode( ',', $plan );
				} else {
					$plans = array( $plan );
				}
				$return_array = array_intersect( $arm_user_plan, $plans );
				if ( $plan != 'all' && ( ! empty( $plans ) && empty( $return_array ) ) ) {
					$hasaccess = false;
				}
			} else {
				$hasaccess = false;
			}
			$hasaccess = apply_filters( 'arm_content_shortcode_hasaccess', $hasaccess );
			if ( $hasaccess ) {
				return do_shortcode( $content );
			} else {
				return $message;
			}
		}

		function arm_not_login_content_shortcode_func( $atts, $content, $tag ) {
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}
			/*
			 ---------------------/.Begin Set Shortcode Attributes--------------------- */
			/* Extract Shortcode Attributes */
			$opts = shortcode_atts( array( 'message' => '' ), $atts, $tag );
			$opts = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $opts ); //phpcs:ignore
			extract( $opts );
			/* ---------------------/.End Set Shortcode Attributes--------------------- */
			if ( ! is_user_logged_in() ) {
				$content = do_shortcode( $content );
			} else {
				$content = $message;
			}
			return $content;
		}

		/**
		 * Directory Template AJAX Pagination Content
		 */
		function arm_directory_paging_action() {
			global $wpdb, $ARMemberLite, $arm_global_settings, $arm_members_directory, $arm_members_class;
			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_directory_paging_action' ) { //phpcs:ignore

				$ARMemberLite->arm_check_user_cap( '', '1' ); //phpcs:ignore --Reason:Verifying nonce
				
				unset( $_POST['action'] ); //phpcs:ignore
				$content = '';
				if ( ! empty( $_POST ) ) { //phpcs:ignore
					$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data' ), $_POST ); //phpcs:ignore
						$opts = $posted_data; //phpcs:ignore
						$temp_data = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$ARMemberLite->tbl_arm_member_templates."` WHERE `arm_id`=%d AND `arm_type`=%s",$opts['id'],$opts['type']) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_member_templates is a table name
						if ( ! empty( $temp_data ) ) {
							$temp_data->arm_options   = isset( $temp_data->arm_options ) ? maybe_unserialize( $temp_data->arm_options ) : array();
							$opts['template_options'] = $temp_data->arm_options;
							$opts['current_page']     = ( isset( $opts['current_page'] ) ) ? $opts['current_page'] : 1;
							$opts['pagination']       = ( isset( $opts['template_options']['pagination'] ) ) ? $opts['template_options']['pagination'] : 'numeric';

							$opts['show_joining']     = ( isset( $opts['template_options']['show_joining'] ) && $opts['template_options']['show_joining'] == '1' ) ? true : false;
							$opts['show_admin_users'] = ( isset( $opts['template_options']['show_admin_users'] ) && $opts['template_options']['show_admin_users'] == '1' ) ? true : false;
							$content                  = $arm_members_directory->arm_get_directory_members( $temp_data, $opts );
						}
					echo do_shortcode( $content );
					exit;
				}
			}
		}

		function arm_template_shortcode_func( $atts, $content, $tag, $template_data = '' ) {

			global $wpdb, $ARMemberLite, $arm_global_settings, $arm_members_directory, $arm_members_class, $arm_social_feature;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}
			if ( ! $arm_social_feature->isSocialFeature ) {
				return do_shortcode( $content );
			}
			$common_messages       = $arm_global_settings->arm_get_all_common_message_settings();
			$alphabaticalSortByTxt = ( ! empty( $common_messages['directory_sort_by_alphabatically'] ) ) ? $common_messages['directory_sort_by_alphabatically'] : esc_html__( 'Alphabetically', 'armember-membership' );
			$recentlyJoinedTxt     = ( ! empty( $common_messages['directory_sort_by_recently_joined'] ) ) ? $common_messages['directory_sort_by_recently_joined'] : esc_html__( 'Recently Joined', 'armember-membership' );
			/*
			 ---------------------/.Begin Set Shortcode Attributes./--------------------- */
			/* Extract Shortcode Attributes */
			$opts = shortcode_atts(
				array(
					'id'           => '',
					'type'         => '',
					'user_id'      => 0,
					'role'         => 'all',
					'listof'       => 'all',
					'search'       => '',
					'orderby'      => 'display_name',
					'order'        => 'ASC',
					'current_page' => 1,
					'per_page'     => 10,
					'pagination'   => 'numeric',
					'sample'       => false,
					'is_preview'   => 0,
				),
				$atts,
				$tag
			);
			$opts = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $opts ); //phpcs:ignore
			extract( $opts );

			$current_page = intval( $current_page );
			$per_page = intval( $per_page );
			$per_page = ($per_page > 0) ? $per_page : 10;

			$opts['listof']     = ( ! empty( $opts['listof'] ) ) ? $opts['listof'] : 'all';
			$opts['sample']     = ( $opts['sample'] === 'true' || $opts['sample'] === '1' ) ? true : false;
			$opts['is_preview'] = ( $opts['is_preview'] === 'true' || $opts['is_preview'] === '1' ) ? 1 : 0;
			/* ---------------------/.End Set Shortcode Attributes./--------------------- */
			$date_format  = $arm_global_settings->arm_get_wp_date_format();
			$pd_templates = array();
			if ( ! empty( $id ) && ! empty( $type ) ) {
				$user_id = 0;
				if ( $type == 'profile' ) {
					$current_user_info = false;
						global $wp_query;
						$reqUser = rawurldecode( $wp_query->get( 'arm_user' ) ); //phpcs:ignore

					if ( empty( $reqUser ) ) {
						$reqUser = ( isset( $_REQUEST['arm_user'] ) && ! empty( $_REQUEST['arm_user'] ) ) ? sanitize_text_field( $_REQUEST['arm_user'] ) : ''; //phpcs:ignore
					}
					$reqUser = sanitize_text_field( $reqUser );
					if ( ! empty( $reqUser ) ) {
						$permalinkBase = isset( $arm_global_settings->global_settings['profile_permalink_base'] ) ? $arm_global_settings->global_settings['profile_permalink_base'] : 'user_login';
						if ( $permalinkBase == 'user_login' ) {
							$current_user_info = get_user_by( 'login', urldecode( $reqUser ) );
						} else {
							$current_user_info = get_user_by( 'id', $reqUser );
						}
						if ( $current_user_info !== false ) {
							$user_id = $current_user_info->ID;
						} else {
							return do_shortcode( $content );
						}
					} else {
						if ( is_user_logged_in() ) {
							$user_id           = get_current_user_id();
							$current_user_info = get_user_by( 'id', $user_id );
						} else {
							return do_shortcode( $content );
						}
					}
					if ( $current_user_info != false ) {
						$arm_member_statuses = $wpdb->get_row( $wpdb->prepare("SELECT `arm_primary_status`, `arm_secondary_status` FROM `".$ARMemberLite->tbl_arm_members."` WHERE `arm_user_id`=%d",$user_id) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_members is a table name						
						$arm_member_status   = '';
						if ( $arm_member_statuses != null ) {
							$arm_member_status           = $arm_member_statuses->arm_primary_status;
							$arm_member_secondary_status = $arm_member_statuses->arm_secondary_status;

							if ( ( $arm_member_status == '2' && in_array( $arm_member_secondary_status, array( 0, 1 ) ) ) || $arm_member_status == 4 ) {
								$current_user_info = false;
							}
						}
					}
				}

				$is_admin_user = $display_admin_user = 0;
				if ( user_can( $user_id, 'administrator' ) ) {
					$is_admin_user = 1;
				}

				$id = intval($id);
				$content = '';
				if ( $type == 'profile' ) {
					$user_plans    = get_user_meta( $user_id, 'arm_user_plan_ids', true );
					$temp_id_admin = $wpdb->get_row( $wpdb->prepare('SELECT `arm_id` FROM `'.$ARMemberLite->tbl_arm_member_templates.'` WHERE `arm_enable_admin_profile` = %d ORDER BY `arm_id` ASC LIMIT 1',1) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_member_templates is a table name

					$admin_template_data = array();
					if ( empty( $user_plans ) || $is_admin_user ) {
						if ( $is_admin_user && isset( $temp_id_admin->arm_id ) && $temp_id_admin->arm_id > 0 && $temp_id_admin->arm_id != '' ) {
							$temp_data          = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".$ARMemberLite->tbl_arm_member_templates."` WHERE `arm_id`=%d AND `arm_type`=%s",$temp_id_admin->arm_id,$type) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_member_templates is a table name
							$display_admin_user = 1;

						} else {
							$temp_data = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$ARMemberLite->tbl_arm_member_templates."` WHERE `arm_id`=%d AND `arm_type`=%s",$id,$type) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_member_templates is a table name
							
						}
					} else {						
						foreach ( $user_plans as $user_plan ) {
							$temp_count = $wpdb->get_var( $wpdb->prepare("SELECT count(*) FROM `".$ARMemberLite->tbl_arm_member_templates."` WHERE FIND_IN_SET(".$user_plan.", `arm_subscription_plan`) AND `arm_type`=%s",$type) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_member_templates is a table name
							if ( $temp_count > 0 ) {
								$temp_data = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$ARMemberLite->tbl_arm_member_templates."` WHERE FIND_IN_SET(".$user_plan.", `arm_subscription_plan`) AND `arm_type`=%s LIMIT 0,1",$type) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_member_templates is a table name
								break;
							}
						}
						if ( $temp_count == 0 ) {
							$temp_data = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$ARMemberLite->tbl_arm_member_templates."` WHERE `arm_id`=%d AND `arm_type`=%s",$id,$type) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_member_templates is a table name
						}
					}	
					
				} else {
					$temp_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".$ARMemberLite->tbl_arm_member_templates."` WHERE `arm_id`=%d AND `arm_type`=%s",$id,$type) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_member_templates is a table name
				}

				if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/templates/' . $temp_data->arm_slug . '.css' ) ) {
					if(!is_admin())
					{
						wp_enqueue_style( 'arm_template_style_' . $temp_data->arm_slug, MEMBERSHIPLITE_VIEWS_URL . '/templates/' . $temp_data->arm_slug . '.css', array(), MEMBERSHIPLITE_VERSION );
					}
					else
					{	
						$content = '<link id="arm_template_style_' . $temp_data->arm_slug.'" rel="stylesheet" type="text/css" href="' . MEMBERSHIPLITE_VIEWS_URL . '/templates/' . $temp_data->arm_slug . '.css" />'; //phpcs:ignore
					}
				}
				if ( ! empty( $temp_data ) ) {

					$temp_data->arm_options   = isset( $temp_data->arm_options ) ? maybe_unserialize( $temp_data->arm_options ) : array();
					$opts['template_options'] = $temp_data->arm_options;
					$opts['pagination']       = ( isset( $opts['template_options']['pagination'] ) ) ? $opts['template_options']['pagination'] : 'numeric';
					$opts['per_page']         = ( isset( $opts['template_options']['per_page_users'] ) ) ? $opts['template_options']['per_page_users'] : 10;
					$opts['show_admin_users'] = isset( $display_admin_user ) && $display_admin_user == 1 ? true : false;

					$opts['show_joining'] = ( isset( $opts['template_options']['show_joining'] ) && $opts['template_options']['show_joining'] == '1' ) ? true : false;
					$_data                = array();
					$content              = apply_filters( 'arm_change_content_before_display_profile_and_directory', $content, $opts );
					$randomTempID         = $id . '_' . arm_generate_random_code();

					$ARMemberLite->arm_session_start(true);
					$captcha_code = arm_generate_captcha_code();
					if ( ! isset( $_SESSION['ARM_FILTER_INPUT'] ) ) {
						$_SESSION['ARM_FILTER_INPUT'] = array();
					}
					if ( isset( $_SESSION['ARM_FILTER_INPUT'][ $randomTempID ] ) ) {
						unset( $_SESSION['ARM_FILTER_INPUT'][ $randomTempID ] );
					}
					$_SESSION['ARM_FILTER_INPUT'][ $randomTempID ] = $captcha_code;
					
					$content             .= '<div class="arm_template_wrapper arm_template_wrapper_' . $id . ' arm_template_wrapper_' . $temp_data->arm_slug . '" data-random-id="' . esc_attr($randomTempID) . '">';
					$all_global_settings  = $arm_global_settings->arm_get_all_global_settings();
					$general_settings     = $all_global_settings['general_settings'];
					$enable_crop          = isset( $general_settings['enable_crop'] ) ? $general_settings['enable_crop'] : 1;
					$nonce = wp_create_nonce( 'arm_wp_nonce' );
					$content .= '<input type="hidden" name="arm_wp_nonce" value="'.esc_attr($nonce).'"/>';
					$content .='<input type="hidden" name="arm_wp_nonce_check" value="1">';
					global $arm_is_enable_crop,$arm_member_forms;
					if ( $enable_crop && empty( $arm_is_enable_crop ) ) {
						$arm_is_enable_crop = 1;
						$content           .= '<div id="arm_crop_div_wrapper" class="arm_crop_div_wrapper" style="display:none;">';
						$content           .= '<div id="arm_crop_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
						$content           .= '<div id="arm_crop_div"><img id="arm_crop_image" alt="" src="" style="max-width:100%;" data-rotate="0" /></div>'; //phpcs:ignore 
						$content           .= '<div class="arm_skip_avtr_crop_button_wrapper_admn arm_inht_front_usr_avtr">';
						$content           .= '<button class="arm_crop_button arm_img_setting armhelptip tipso_style" title="' . esc_html__( 'Crop', 'armember-membership' ) . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
						$content           .= '<button class="arm_clear_button arm_img_setting armhelptip tipso_style" title="' . esc_html__( 'Clear', 'armember-membership' ) . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
						$content           .= '<button class="arm_zoom_button arm_zoom_plus arm_img_setting armhelptip tipso_style" data-method="zoom" data-option="0.1" title="' . esc_html__( 'Zoom In', 'armember-membership' ) . '"><span class="armfa armfa-search-plus"></span></button>';
						$content           .= '<button class="arm_zoom_button arm_zoom_minus arm_img_setting armhelptip tipso_style" data-method="zoom" data-option="-0.1" title="' . esc_html__( 'Zoom Out', 'armember-membership' ) . '"><span class="armfa armfa-search-minus"></span></button>';
						$content           .= '<button class="arm_rotate_button arm_img_setting armhelptip tipso_style" data-method="rotate" data-option="90" title="' . esc_html__( 'Rotate', 'armember-membership' ) . '"><span class="armfa armfa-rotate-right"></span></button>';
						$content           .= '<button class="arm_reset_button arm_img_setting armhelptip tipso_style" title="' . esc_html__( 'Reset', 'armember-membership' ) . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
						$content           .= '<button id="arm_skip_avtr_crop_nav_front" class="arm_avtr_done_front">' . esc_html__( 'Done', 'armember-membership' ) . '</button>';
						$content           .= '</div>';
						$content           .= '<p class="arm_discription">(' . sprintf( addslashes( esc_html__( 'Use Cropper to set image and %1$s use mouse scroller for zoom image.', 'armember-membership' ) ), '<br/>') . ')</p>'; //phpcs:ignore
						$content           .= '</div>';

						$content .= '<div id="arm_crop_cover_div_wrapper" class="arm_crop_cover_div_wrapper" style="display:none;">';
						$content .= '<div id="arm_crop_cover_div_wrapper_close" class="arm_clear_field_close_btn arm_popup_close_btn"></div>';
						$content .= '<div id="arm_crop_cover_div"><img id="arm_crop_cover_image" alt="" src="" style="max-width:100%;" data-rotate="0" /></div>'; //phpcs:ignore 
						$content .= '<div class="arm_skip_cvr_crop_button_wrapper_admn arm_inht_front_usr_cvr">';
						$content .= '<button class="arm_crop_cover_button arm_img_cover_setting armhelptip tipso_style" title="' . esc_html__( 'Crop', 'armember-membership' ) . '" data-method="crop"><span class="armfa armfa-crop"></span></button>';
						$content .= '<button class="arm_clear_cover_button arm_img_cover_setting armhelptip tipso_style" title="' . esc_html__( 'Clear', 'armember-membership' ) . '" data-method="clear" style="display:none;"><span class="armfa armfa-times"></span></button>';
						$content .= '<button class="arm_zoom_cover_button arm_zoom_plus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="0.1" title="' . esc_html__( 'Zoom In', 'armember-membership' ) . '"><span class="armfa armfa-search-plus"></span></button>';
						$content .= '<button class="arm_zoom_cover_button arm_zoom_minus arm_img_cover_setting armhelptip tipso_style" data-method="zoom" data-option="-0.1" title="' . esc_html__( 'Zoom Out', 'armember-membership' ) . '"><span class="armfa armfa-search-minus"></span></button>';
						$content .= '<button class="arm_rotate_cover_button arm_img_cover_setting armhelptip tipso_style" data-method="rotate" data-option="90" title="' . esc_html__( 'Rotate', 'armember-membership' ) . '"><span class="armfa armfa-rotate-right"></span></button>';
						$content .= '<button class="arm_reset_cover_button arm_img_cover_setting armhelptip tipso_style" title="' . esc_html__( 'Reset', 'armember-membership' ) . '" data-method="reset"><span class="armfa armfa-refresh"></span></button>';
						$content .= '<button id="arm_skip_cvr_crop_nav_front" class="arm_cvr_done_front">' . esc_html__( 'Done', 'armember-membership' ) . '</button>';
						$content .= '</div>';
						$content .= '<p class="arm_discription">' . esc_html__( '(Use Cropper to set image and use mouse scroller for zoom image.)', 'armember-membership' ) . '</p>';
						$content .= '</div>';
					}
					if(is_admin())
					{
						$content .= $arm_members_directory->arm_template_style( $id, $opts['template_options'] );
					}
					else{
						wp_register_style('arm_profile_dir_card_shortcode_css_'.$id,false,array(),MEMBERSHIPLITE_VERSION);
						wp_enqueue_style('arm_profile_dir_card_shortcode_css_'.$id);
						$arm_template_content_css = $arm_members_directory->arm_template_style( $id, $opts['template_options'],1);
						wp_add_inline_style( 'arm_profile_dir_card_shortcode_css_'.$id, $arm_template_content_css );
					}
					$arm_profile_form_rtl = $arm_directory_form_rtl = '';
					if ( is_rtl() ) {
						$arm_profile_form_rtl   = 'arm_profile_form_rtl';
						$arm_directory_form_rtl = 'arm_directory_form_rtl';
					}
					if ( $type == 'profile' ) {
						$content .= '<div class="arm_template_container arm_profile_container ' . esc_attr($arm_profile_form_rtl) . '"  id="arm_template_container_' . esc_attr($randomTempID) . '">';
						if ( ! empty( $current_user_info ) ) {
							$_data = array( $current_user_info );
							$_data = $arm_members_directory->arm_prepare_users_detail_for_template( $_data, $opts );
							$_data = apply_filters( 'arm_change_user_detail_before_display_in_profile_and_directory', $_data, $opts );

													   $content .= $arm_members_directory->arm_profile_template_blocks( (array) $temp_data, $_data, $opts );
						}
						$content .= '</div>';
					} elseif ( $type == 'directory' ) {
						$content .= '<form method="POST" class="arm_directory_form_container ' . esc_attr($arm_directory_form_rtl) . '" data-temp="' . esc_attr($id) . '" onsubmit="return false;" action="#">';
						$content .= '<div class="arm_template_loading" style="display: none;"><img src="' . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/loader_template.gif" alt="Loading.."></div>'; //phpcs:ignore 
						/* For Filter User List */
						$sortbox   = ( isset( $opts['template_options']['sortbox'] ) && $opts['template_options']['sortbox'] == '1' ) ? true : false;
						$searchbox = ( isset( $opts['template_options']['searchbox'] ) && $opts['template_options']['searchbox'] == '1' ) ? true : false;
						if ( $sortbox || $searchbox || ( is_user_logged_in() ) ) {
							$content .= '<div class="arm_directory_filters_wrapper">';
							if ( $searchbox ) {
								$content .= '<div class="arm_directory_search_wrapper">';
								$content .= '<input type="text" name="search" value="' . esc_attr( $search ) . '" class="arm_directory_search_box">';
								$content .= '<a class="arm_directory_search_btn"><i class="armfa armfa-search"></i></a><img id="arm_loader_img" width="24" height="24" style="position: relative; top: 3px; display: none; float: left; margin-left: 5px; " src="' . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif" alt="Loading..">'; //phpcs:ignore 
								$content .= '</div>';
							} else {
								$content .= '<input type="hidden" name="search" value="">';
							}
							$content .= '<input type="hidden" name="listof" value="all">';
							if ( $sortbox ) {
								$content .= '<div class="arm_directory_list_by_filters">';
								$content .= '<select name="orderby" class="arm_directory_listby_select">';
								$content .= '<option value="login" ' . selected( $orderby, 'login', false ) . '>' . esc_html__( 'Sort By', 'armember-membership' ) . '</option>';
								$content .= '<option value="display_name" ' . selected( $orderby, 'display_name', false ) . '>' . esc_html($alphabaticalSortByTxt) . '</option>';
								$content .= '<option value="user_registered" ' . selected( $orderby, 'user_registered', false ) . '>' . esc_html($recentlyJoinedTxt) . '</option>';
								$content .= '</select>';
								$content .= '</div>';
							} else {
								$content .= '<input type="hidden" name="orderby" value="login">';
							}
							$content .= '<div class="armclear"></div>';
							$content .= '</div>';
							$content .= '<div class="armclear"></div>';
						}
						$content .= '<div class="arm_template_container arm_directory_container" id="arm_template_container_' . esc_attr($randomTempID) . '">';
						$content .= $arm_members_directory->arm_get_directory_members( $temp_data, $opts );
						/* Template Arguments Inputs */
						foreach ( array( 'id', 'type', 'user_id', 'role', 'order', 'per_page', 'pagination', 'sample', 'is_preview' ) as $k ) {
							$content .= '<input type="hidden" class="arm_temp_field_' . esc_attr($k) . '" name="' . esc_attr($k) . '" value="' . esc_attr( $opts[ $k ] ) . '">';
						}
						$content .= '</div>';
						$nonce = wp_create_nonce('arm_wp_nonce');
						$content .='<input type="hidden" name="arm_wp_nonce" value='.esc_attr( $nonce ).'>';
						$content .='<input type="hidden" name="arm_wp_nonce_check" value="1">';
						$content .= '</form>';
					}
					$content .= '<div class="armclear"></div>';
					$content .= '</div>';
					$content  = apply_filters( 'arm_change_content_after_display_profile_and_directory', $content, $opts );
				}
			}
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

			$content = do_shortcode($content);
			$content = $this->arm_com_escape_all_shortcodes($content.$hiddenvalue);
			$content = $this->arm_com_descaped_all_shortcodes($content);
			return $content;
		}

		/**
		 * Transaction AJAX Pagination Content
		 */
		function arm_transaction_paging_action() {
			global $wpdb, $ARMemberLite, $arm_global_settings, $arm_members_directory, $arm_members_class;
			
			$ARMemberLite->arm_check_user_cap('',1); //phpcs:ignore --Reason:Verifying nonce
			$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data' ), $_POST ); //phpcs:ignore
			
			if ( isset( $posted_data['action'] ) && $posted_data['action'] == 'arm_transaction_paging_action' ) {
				unset( $posted_data['action'] );
				if ( ! empty( $posted_data ) ) {
					$shortcode_param = '';
					foreach ( $posted_data as $k => $v ) {
						$shortcode_param .= htmlentities( sanitize_text_field( $k ) ) . '="' . htmlentities( sanitize_text_field( $v ) ) . '" ';
					}
					
					$shortcode_param = str_replace( '[' , '&#10635', $shortcode_param);
					$shortcode_param = str_replace(']' , '&#10636', $shortcode_param);
					echo do_shortcode( "[arm_member_transaction $shortcode_param]" );
					exit;
				}
			}
		}

		function arm_member_transaction_func( $atts, $content, $tag ) {
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}

			/* ====================/.Begin Set Shortcode Attributes./==================== */
			$default_transaction_fields = esc_html__( 'Transaction ID', 'armember-membership' ) . ',' . esc_html__( 'Plan', 'armember-membership' ) . ',' . esc_html__( 'Payment Gateway', 'armember-membership' ) . ',' . esc_html__( 'Payment Type', 'armember-membership' ) . ',' . esc_html__( 'Transaction Status', 'armember-membership' ) . ',' . esc_html__( 'Amount', 'armember-membership' ) . ',' . esc_html__( 'Payment Date', 'armember-membership' );
			$defaults                   = array(
				'user_id'           => '',
				'title'             => esc_html__( 'Transactions', 'armember-membership' ),
				'current_page'      => 0,
				'per_page'          => 5,
				'message_no_record' => esc_html__( 'There is no any Transactions found', 'armember-membership' ),
				'label'             => 'transaction_id,plan,payment_gateway,payment_type,transaction_status,amount,payment_date',
				'value'             => $default_transaction_fields,
			);
			/* Extract Shortcode Attributes */
			$args = shortcode_atts( $defaults, $atts, $tag );

			$args = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $args ); //phpcs:ignore

			extract( $args );

			$ARMemberLite->arm_session_start();

			$current_page = intval( $current_page );
			$per_page = intval( $per_page );
			$per_page = ($per_page > 0) ? $per_page : 5;

			/* ====================/.End Set Shortcode Attributes./==================== */
			global $wp, $wpdb, $current_user, $current_site, $arm_lite_errors, $ARMemberLite, $arm_transaction, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways, $arm_lite_bpopup_loaded;
			$arm_lite_bpopup_loaded = 1;
			$date_format            = $arm_global_settings->arm_get_wp_date_format();
			$date_time_format       = $arm_global_settings->arm_get_wp_date_time_format();
			$labels                 = explode( ',', rtrim( $args['label'], ',' ) );
			$values                 = explode( ',', rtrim( $args['value'], ',' ) );

			if ( is_user_logged_in() ) {
				if ( current_user_can( 'arm_manage_members' ) ) {
					$user_id = $args['user_id'];
				}
				if ( empty( $user_id ) || $user_id == 0 || $user_id == 'current' ) {
					$user_id = get_current_user_id();
				}
				wp_enqueue_style( 'arm_form_style_css' );
				$offset = ( ! empty( $current_page ) && $current_page > 1 ) ? ( ( $current_page - 1 ) * $per_page ) : 0;

				$trans_count  = $arm_transaction->arm_get_total_transaction( $user_id );
				$transactions = $arm_transaction->arm_get_all_transaction( $user_id, $offset, $per_page );

				$content        = apply_filters( 'arm_before_member_transaction_shortcode_content', $content, $args );
				$content       .= "<div class='arm_transactions_container' id='arm_tm_container'>";
				$frontfontstyle = $arm_global_settings->arm_get_front_font_style();
				// $content .=!empty($frontfontstyle['google_font_url']) ? '<link id="google-font" rel="stylesheet" type="text/css" href="' . $frontfontstyle['google_font_url'] . '" />' : '';
				$content                 .= ! empty( $frontfontstyle['google_font_url'] ) ? wp_enqueue_style( 'google-font', $frontfontstyle['google_font_url'], array(), MEMBERSHIPLITE_VERSION ) : '';
				$content                 .= '<style type="text/css">';
				$transactionsWrapperClass = '.arm_transactions_container';

				$content .= "
                        $transactionsWrapperClass .arm_transactions_heading_main{
                            ". esc_attr( $frontfontstyle['frontOptions']['level_1_font']['font'] ) ."
                        }
                        $transactionsWrapperClass .arm_transaction_list_header th{
                            ". esc_attr( $frontfontstyle['frontOptions']['level_2_font']['font'] ) ."
                        }
                        $transactionsWrapperClass .arm_transaction_list_item td{
                            ". esc_attr( $frontfontstyle['frontOptions']['level_3_font']['font'] ) ."
                        }
                        .arm_transactions_container .arm_paging_wrapper .arm_paging_info,
                        .arm_transactions_container .arm_paging_wrapper .arm_paging_links a{
                            ". esc_attr( $frontfontstyle['frontOptions']['level_4_font']['font'] ) ."
                        }";
				$content .= '</style>';
				if ( ! empty( $title ) ) {
					$content .= '<div class="arm_transactions_heading_main" id="arm_tm_heading_main">' . esc_attr( $title ) . '</div>';
					$content .= '<div class="armclear"></div>';
				}

				$formRandomID                  = '1_' . arm_generate_random_code();

				$captcha_code = arm_generate_captcha_code();
				if ( ! isset( $_SESSION['ARM_FILTER_INPUT'] ) ) {
					$_SESSION['ARM_FILTER_INPUT'] = array();
				}
				if ( isset( $_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] ) ) {
					unset( $_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] );
				}
				$_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] = $captcha_code;

				$content .= '<form method="POST" action="#" class="arm_transaction_form_container" data-random-id="' . esc_attr($formRandomID) . '">';
				$content .= '<div class="arm_template_loading" style="display: none;"><img src="' . MEMBERSHIPLITE_IMAGES_URL . '/loader.gif" alt="Loading.."></div>'; //phpcs:ignore 
				$content .= "<div class='arm_transactions_wrapper' id='arm_tm_wrapper'>";
				if ( ! empty( $transactions ) ) {
					$global_currency     = $arm_payment_gateways->arm_get_global_currency();
					$all_currencies      = $arm_payment_gateways->arm_get_all_currencies();
					$global_currency_sym = isset( $all_currencies ) ? $all_currencies[ strtoupper( $global_currency ) ] : '';
					if ( is_rtl() ) {
						$is_transaction_class_rtl = 'is_transaction_class_rtl';
					} else {
						$is_transaction_class_rtl = '';
					}
					$content               .= "<div class='arm_transaction_content  " . $is_transaction_class_rtl . "' id='arm_tm_content' style='overflow-x: auto;'>";
					$content               .= "<table class='arm_user_transaction_list_table arm_front_grid' id='arm_tm_table' cellpadding='0' cellspacing='0' border='0'>";
					$content               .= '<thead>';
					$content               .= "<tr class='arm_transaction_list_header' id='arm_tm_list_header'>";
					$has_transaction_id     = true;
					$has_plan               = true;
					$has_payment_gateway    = true;
					$has_payment_type       = true;
					$has_transaction_status = true;
					$has_amount             = true;

					$has_payment_date = true;
					$has_action       = false;

					if ( in_array( 'transaction_id', $labels ) ) {
						$label_key = array_search( 'transaction_id', $labels );
						$l_transID = ! empty( $values[ $label_key ] ) ? $values[ $label_key ] : esc_html__( 'Transaction ID', 'armember-membership' );
					} else {
						$has_transaction_id = false;
					}

					if ( in_array( 'plan', $labels ) ) {
						$label_key = array_search( 'plan', $labels );
						$l_plan    = ! empty( $values[ $label_key ] ) ? $values[ $label_key ] : esc_html__( 'Plan', 'armember-membership' );
					} else {
						$has_plan = false;
					}
					if ( in_array( 'payment_gateway', $labels ) ) {
						$label_key = array_search( 'payment_gateway', $labels );
						$l_pg      = ! empty( $values[ $label_key ] ) ? $values[ $label_key ] : esc_html__( 'Payment Gateway', 'armember-membership' );
					} else {
						$has_payment_gateway = false;
					}

					if ( in_array( 'payment_type', $labels ) ) {
						$label_key = array_search( 'payment_type', $labels );
						$l_pType   = ! empty( $values[ $label_key ] ) ? $values[ $label_key ] : esc_html__( 'Payment Type', 'armember-membership' );
					} else {
						$has_payment_type = false;
					}
					if ( in_array( 'transaction_status', $labels ) ) {
						$label_key     = array_search( 'transaction_status', $labels );
						$l_transStatus = ! empty( $values[ $label_key ] ) ? $values[ $label_key ] : esc_html__( 'Transaction Status', 'armember-membership' );
					} else {
						$has_transaction_status = false;
					}
					if ( in_array( 'amount', $labels ) ) {
						$label_key = array_search( 'amount', $labels );
						$l_amount  = ! empty( $values[ $label_key ] ) ? $values[ $label_key ] : esc_html__( 'Amount', 'armember-membership' );
					} else {
						$has_amount = false;
					}

					if ( in_array( 'payment_date', $labels ) ) {
						$label_key = array_search( 'payment_date', $labels );
						$l_pDate   = ! empty( $values[ $label_key ] ) ? $values[ $label_key ] : esc_html__( 'Payment Date', 'armember-membership' );
					} else {
						$has_payment_date = false;
					}
					if ( $has_transaction_id ) :
						$content .= "<th class='arm_transaction_th' id='arm_tm_transid'>{$l_transID}</th>";
					endif;

					if ( $has_plan ) :
						$content .= "<th class='arm_transaction_th' id='arm_tm_plan'>{$l_plan}</th>";
					endif;
					if ( $has_payment_gateway ) :
						$content .= "<th class='arm_transaction_th' id='arm_tm_payment_gateway'>{$l_pg}</th>";
					endif;
					if ( $has_payment_type ) :
						$content .= "<th class='arm_transaction_th' id='arm_tm_payment_type'>{$l_pType}</th>";
					endif;
					if ( $has_transaction_status ) :
						$content .= "<th class='arm_transaction_th' id='arm_tm_status'>{$l_transStatus}</th>";
					endif;
					if ( $has_amount ) :
						$content .= "<th class='arm_transaction_th' id='arm_tm_amount'>{$l_amount}</th>";
					endif;

					if ( $has_payment_date ) :
						$content .= "<th class='arm_transaction_th' id='arm_tm_payment_date'>{$l_pDate}</th>";
					endif;
					if ( $has_action ) :
						$content .= "<th class='arm_transaction_th' id='arm_tm_payment_action'></th>";
					endif;
					$content .= '</tr>';
					$content .= '</thead>';
					foreach ( $transactions as $r ) {

						$r = (object) $r;
						$r->arm_transaction_id = !empty($r->arm_transaction_id) ? stripslashes($r->arm_transaction_id) : '';
						$currency = ( ! empty( $r->arm_currency ) && isset( $all_currencies[ strtoupper( $r->arm_currency ) ] ) ) ? $all_currencies[ strtoupper( $r->arm_currency ) ] : $global_currency_sym;
						$content .= "<tr class='arm_transaction_list_item' id='arm_transaction_list_item_" . $r->arm_transaction_id . "'>";
						if ( $has_transaction_id ) {
							$content .= "<td data-label='".esc_attr($l_transID)."'>";
							if ( ! empty( $r->arm_transaction_id ) ) {
								$content .= $r->arm_transaction_id;
							} else {
								$content .= esc_html__( 'Manual', 'armember-membership' );
							}
							$content .= '</td>';
						}

						if ( $has_plan ) {
							$content .= "<td data-label='".esc_attr($l_plan)."' id='arm_transaction_list_item_td_" . esc_attr($r->arm_transaction_id) . "'>" . $arm_subscription_plans->arm_get_plan_name_by_id( $r->arm_plan_id ) . '</td>';
							$nonce = wp_create_nonce('arm_wp_nonce');
							$content .='<input type="hidden" name="arm_wp_nonce" value="'.esc_attr( $nonce ).'">';
							$content .='<input type="hidden" name="arm_wp_nonce_check" value="1">';
						}
						if ( $has_payment_gateway ) {
							$content .= "<td data-label='".esc_attr($l_pg)."' id='arm_transaction_list_item_td_" . esc_attr($r->arm_transaction_id) . "'>" . $arm_payment_gateways->arm_gateway_name_by_key( $r->arm_payment_gateway ) . '</td>';
						}
						if ( $has_payment_type ) {
							$payment_type = ( isset( $r->arm_payment_type ) && $r->arm_payment_type == 'subscription' ) ? esc_html__( 'Subscription', 'armember-membership' ) : esc_html__( 'One Time', 'armember-membership' );
							$arm_is_trial = ( isset( $r->arm_is_trial ) && $r->arm_is_trial == 1 ) ? ' ' . esc_html__( '(Trial Transaction)', 'armember-membership' ) : '';
							$content     .= "<td data-label='".esc_attr($l_pType)."' id='arm_transaction_list_item_td_" . esc_attr($r->arm_transaction_id) . "'>" . $payment_type . $arm_is_trial . '</td>';
						}
						if ( $has_transaction_status ) {
							$arm_txn_status         = '';
							$arm_transaction_status = $r->arm_transaction_status;
							switch ( $arm_transaction_status ) {
								case '0':
									$arm_txn_status = 'pending';
									break;
								case '1':
									$arm_txn_status = 'success';
									break;
								case '2':
									$arm_txn_status = 'canceled';
									break;
								default:
									$arm_txn_status = $r->arm_transaction_status;
									break;
							}
							$arm_transaction_status = $arm_transaction->arm_get_transaction_status_text( $arm_txn_status );
							$content               .= "<td data-label='".esc_attr($l_transStatus)."' id='arm_transaction_list_item_td_".esc_attr($arm_txn_status)."'>" . $arm_transaction_status . '</td>';
						}
						if ( $has_amount ) {
							$content  .= "<td data-label='".esc_attr($l_amount)."' id='arm_transaction_list_item_td_" . esc_attr($r->arm_transaction_id) . "'>";
							$extraVars = ( ! empty( $r->arm_extra_vars ) ) ? maybe_unserialize( $r->arm_extra_vars ) : array();
							if ( ! empty( $extraVars ) && ! empty( $extraVars['plan_amount'] ) && $extraVars['plan_amount'] != 0 && $extraVars['plan_amount'] != $r->arm_amount ) {
								$content .= '<span class="arm_transaction_list_plan_amount">' . $arm_payment_gateways->arm_prepare_amount( $r->arm_currency, $extraVars['plan_amount'] ) . '</span>';
							}
							$content .= '<span class="arm_transaction_list_paid_amount">';
							if ( ! empty( $r->arm_amount ) && $r->arm_amount > 0 ) {
								$content .= $arm_payment_gateways->arm_prepare_amount( $r->arm_currency, $r->arm_amount );
								if ( $global_currency_sym == $currency && strtoupper( $global_currency ) != strtoupper( $r->arm_currency ) ) {
									$content .= ' (' . strtoupper( $r->arm_currency ) . ')';
								}
							} else {
								$content .= $arm_payment_gateways->arm_prepare_amount( $r->arm_currency, $r->arm_amount );
							}
							$content .= '</span>';
							if ( ! empty( $extraVars ) && isset( $extraVars['trial'] ) ) {
								$trialInterval = $extraVars['trial']['interval'];
								$content      .= '<span class="arm_transaction_list_trial_text">';
								$content      .= esc_html__( 'Trial Period', 'armember-membership' ) . ": {$trialInterval} ";
								if ( $extraVars['trial']['period'] == 'Y' ) {
									$content .= ( $trialInterval > 1 ) ? esc_html__( 'Years', 'armember-membership' ) : esc_html__( 'Year', 'armember-membership' );
								} elseif ( $extraVars['trial']['period'] == 'M' ) {
									$content .= ( $trialInterval > 1 ) ? esc_html__( 'Months', 'armember-membership' ) : esc_html__( 'Month', 'armember-membership' );
								} elseif ( $extraVars['trial']['period'] == 'W' ) {
									$content .= ( $trialInterval > 1 ) ? esc_html__( 'Weeks', 'armember-membership' ) : esc_html__( 'Week', 'armember-membership' );
								} elseif ( $extraVars['trial']['period'] == 'D' ) {
									$content .= ( $trialInterval > 1 ) ? esc_html__( 'Days', 'armember-membership' ) : esc_html__( 'Day', 'armember-membership' );
								}
								$content .= '</span>';
							}
							$content .= '</td>';
						}

						if ( $has_payment_date ) {
							$content .= "<td data-label='".esc_attr($l_pDate)."' id='arm_transaction_list_item_td_" . esc_attr($r->arm_transaction_id) . "'>" . date_i18n( $date_time_format, strtotime( $r->arm_created_date ) ) . '</td>';
						}
						if ( $has_action ) {
							$content         .= "<td data-label='" . esc_html__( 'Payment Action', 'armember-membership' ) . "' id='arm_transaction_list_item_td_" . esc_attr($r->arm_transaction_id) . "'>";
									$log_type = ( $r->arm_payment_gateway == 'bank_transfer' ) ? 'bt_log' : 'other';

							$content .= '</td>';

						}
						$content .= '</tr>';
					}
					$content .= '</table>';
					$nonce = wp_create_nonce('arm_wp_nonce');
					$content .= "<input type='hidden' name='arm_wp_nonce' value='".esc_attr( $nonce )."'>";
					$content .='<input type="hidden" name="arm_wp_nonce_check" value="1">';
					$content    .= '</div>';
					$transPaging = $arm_global_settings->arm_get_paging_links( $current_page, $trans_count, $per_page, 'transaction' );
					$content    .= "<div class='arm_transaction_paging_container " . esc_attr($is_transaction_class_rtl) . "'>" . $transPaging . '</div>';
				} else {
					if ( is_rtl() ) {
						$is_transaction_class_rtl = 'is_transaction_class_rtl';
					} else {
						$is_transaction_class_rtl = '';
					}
					$content           .= "<div class='arm_transaction_content  " . esc_attr($is_transaction_class_rtl) . "' style='overflow-x: auto;' >";
					$content           .= "<table class='arm_user_transaction_list_table arm_front_grid' cellpadding='0' cellspacing='0' border='0' style='border-collapse:unset;'>";
					$content           .= '<thead>';
					$content           .= "<tr class='arm_transaction_list_header'>";
					$has_transaction_id = true;

					$has_plan               = true;
					$has_payment_gateway    = true;
					$has_payment_type       = true;
					$has_transaction_status = true;
					$has_amount             = true;

					$has_payment_date = true;

					if ( in_array( 'transaction_id', $labels ) ) {
						$label_key = array_search( 'transaction_id', $labels );
						$l_transID = $values[ $label_key ];
					} else {
						$has_transaction_id = false;
					}

					if ( in_array( 'plan', $labels ) ) {
						$label_key = array_search( 'plan', $labels );
						$l_plan    = $values[ $label_key ];
					} else {
						$has_plan = false;
					}
					if ( in_array( 'payment_gateway', $labels ) ) {
						$label_key = array_search( 'payment_gateway', $labels );
						$l_pg      = $values[ $label_key ];
					} else {
						$has_payment_gateway = false;
					}
					if ( in_array( 'payment_type', $labels ) ) {
						$label_key = array_search( 'payment_type', $labels );
						$l_pType   = $values[ $label_key ];
					} else {
						$has_payment_type = false;
					}
					if ( in_array( 'transaction_status', $labels ) ) {
						$label_key     = array_search( 'transaction_status', $labels );
						$l_transStatus = $values[ $label_key ];
					} else {
						$has_transaction_status = false;
					}
					if ( in_array( 'amount', $labels ) ) {
						$label_key = array_search( 'amount', $labels );
						$l_amount  = $values[ $label_key ];
					} else {
						$has_amount = false;
					}

					if ( in_array( 'payment_date', $labels ) ) {
						$label_key = array_search( 'payment_date', $labels );
						$l_pDate   = $values[ $label_key ];
					} else {
						$has_payment_date = false;
					}
					$i = 0;
					if ( $has_transaction_id ) :
						$i++;
						$content .= "<th class='arm_sortable_th'>{$l_transID}</th>";
					endif;

					if ( $has_plan ) :
						$i++;
						$content .= "<th class='arm_sortable_th'>{$l_plan}</th>";
					endif;
					if ( $has_payment_gateway ) :
						$i++;
						$content .= "<th class='arm_sortable_th'>{$l_pg}</th>";
					endif;
					if ( $has_payment_type ) :
						$i++;
						$content .= "<th class='arm_sortable_th'>{$l_pType}</th>";
					endif;
					if ( $has_transaction_status ) :
						$i++;
						$content .= "<th class='arm_sortable_th'>{$l_transStatus}</th>";
					endif;
					if ( $has_amount ) :
						$i++;
						$content .= "<th class='arm_sortable_th'>{$l_amount}</th>";
					endif;

					if ( $has_payment_date ) :
						$i++;
						$content .= "<th class='arm_sortable_th'>{$l_pDate}</th>";
					endif;
					$content .= '</tr>';
					$content .= '</thead>';
					$content .= "<tr class='arm_transaction_list_item'>";
					$content .= "<td colspan='" . esc_attr($i) . "' class='arm_no_transaction'>$message_no_record</td>";
					$content .= '</tr>';
					$content .= '</table>';
					$content .= '</div>';
				}
				$content .= '</div>';
				$content .= "<div class='armclear'></div>";
				/* Template Arguments Inputs */
				foreach ( array( 'user_id', 'title', 'per_page', 'message_no_record', 'label', 'value' ) as $k ) {
					$content .= '<input type="hidden" class="arm_trans_field_' . esc_attr($k) . '" name="' . esc_attr($k) . '" value="' . esc_attr($args[ $k ]) . '">';
				}
				$arm_wp_nonce = wp_create_nonce( 'arm_wp_nonce' );
				$content     .= '<input type="hidden" name="arm_wp_nonce" value="' . esc_attr($arm_wp_nonce) . '"/>';
				$content .='<input type="hidden" name="arm_wp_nonce_check" value="1">';
				$content .= '</form>';
				$content .= '<script data-cfasync="false" type="text/javascript">jQuery(document).ready(function ($) { if (typeof arm_transaction_init == "function") { arm_transaction_init(); } });</script>';
				$content .= '</div>';
				$content  = apply_filters( 'arm_after_member_transaction_shortcode_content', $content, $args );
			}
			$content = $this->arm_com_escape_all_shortcodes($content);
			$content = $this->arm_com_descaped_all_shortcodes($content);
			return do_shortcode( $content );
		}

		function arm_account_detail_shortcode_func( $atts, $content, $tag ) {
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}
			/* ====================/.Begin Set Shortcode Attributes./==================== */
			$atts = shortcode_atts(
				array(
					'section'                  => 'profile', /* Values:-> `profile,membership,transactions,close_account,logout` */
					'show_change_subscription' => false,
					'change_subscription_url'  => '',
					'fields'                   => '',
					'social_fields'            => '',
					'label'                    => 'first_name,last_name,user_login,user_email',
					'value'                    => 'First Name,Last Name,Username,Email',
				),
				$atts,
				$tag
			);
			$atts = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $atts ); //phpcs:ignore
			/* ====================/.End Set Shortcode Attributes./==================== */
			global $wp, $wpdb, $current_user, $current_site, $ARMemberLite, $arm_member_forms, $arm_global_settings, $arm_social_feature, $arm_lite_members_activity;
			$common_messages    = $arm_global_settings->arm_get_all_common_message_settings();
			$profileTabTxt      = esc_html__( 'Profile', 'armember-membership' );
			$membershipTabTxt   = esc_html__( 'Membership', 'armember-membership' );
			$transactionTabTxt  = esc_html__( 'Transactions', 'armember-membership' );
			$closeaccountTabTxt = esc_html__( 'Close Account', 'armember-membership' );
			if ( is_user_logged_in() ) {
				$user_id            = get_current_user_id();
				$defaultTabSettings = array(
					'profile'       => $profileTabTxt,
					'membership'    => $membershipTabTxt,
					'transactions'  => $transactionTabTxt,
					'close_account' => $closeaccountTabTxt,
				);
				$atts['section']    = strtolower( str_replace( ' ', '', $atts['section'] ) );
				$show_subscription  = ( $atts['show_change_subscription'] === 'true' ) ? true : false;
				$sections           = ( ! empty( $atts['section'] ) ) ? explode( ',', $atts['section'] ) : array( 'profile' );
				$sections           = $ARMemberLite->arm_array_trim( $sections );
				$sections           = $ARMemberLite->arm_array_unique( $sections );
				$displaySections    = array();
				if ( ! empty( $sections ) ) {
					foreach ( $defaultTabSettings as $tab => $title ) {
						if ( in_array( $tab, $sections ) ) {
							$displaySections[] = $tab;
						}
					}
				} else {
					$displaySections[] = 'profile';
				}
				$content        = apply_filters( 'arm_change_account_details_before_display', $content, $atts );
				$frontfontstyle = $arm_global_settings->arm_get_front_font_style();
				// $content .=!empty($frontfontstyle['google_font_url']) ? '<link id="google-font" rel="stylesheet" type="text/css" href="' . $frontfontstyle['google_font_url'] . '" />' : '';
				$content .= ! empty( $frontfontstyle['google_font_url'] ) ? wp_enqueue_style( 'google-font', $frontfontstyle['google_font_url'], array(), MEMBERSHIPLITE_VERSION ) : '';

				$content            .= '<style type="text/css">';
				$accountWrapperClass = '.arm_account_detail_wrapper';
				$content            .= "
                    $accountWrapperClass .arm_account_detail_tab_heading{
                        ". esc_attr( $frontfontstyle['frontOptions']['level_1_font']['font'] ) ."
                    }
                    $accountWrapperClass .arm-form-table-label,
                    $accountWrapperClass .arm_account_link_tab a,
                    $accountWrapperClass .arm_account_btn_tab a,
                    $accountWrapperClass .arm_transaction_list_header th,
                    $accountWrapperClass .arm_transactions_container table td:before,
                    $accountWrapperClass .arm_form_field_label_text{
                        ". esc_attr( $frontfontstyle['frontOptions']['level_2_font']['font'] ) ."
                    }
                    $accountWrapperClass .arm-form-table-content,
                    $accountWrapperClass .arm_transaction_list_item td,
                    $accountWrapperClass .arm_close_account_message,
                    $accountWrapperClass .arm_form_input_box{
                        ". esc_attr( $frontfontstyle['frontOptions']['level_3_font']['font'] ) ."
                    }
                    $accountWrapperClass .arm_details_activity,
                    $accountWrapperClass .arm_time_section,
                    $accountWrapperClass .arm_paging_wrapper,
                    $accountWrapperClass .arm_empty_box_warning,
                    $accountWrapperClass .arm_count_txt{
                        ". esc_attr( $frontfontstyle['frontOptions']['level_4_font']['font'] ) ."
                    }
                    $accountWrapperClass .arm_member_detail_action_links a,
                    $accountWrapperClass .arm_activity_display_name a,
                    $accountWrapperClass .arm_activity_other_links, 
                    $accountWrapperClass .arm_activity_other_links a,
                    $accountWrapperClass .arm_member_info_right a{
                        ". esc_attr( $frontfontstyle['frontOptions']['link_font']['font'] ) ."
                    }
                    $accountWrapperClass .arm_paging_wrapper .arm_paging_links a{
                        ". esc_attr( $frontfontstyle['frontOptions']['link_font']['font'] ) ."
                    }
                    
                ";
				$content            .= '</style>';
				if ( is_rtl() ) {
					$is_account_detail_class_rtl = 'is_account_detail_class_rtl';
				} else {
					$is_account_detail_class_rtl = '';
				}
				$content .= '<div class="arm_account_detail_wrapper ' . esc_attr( $is_account_detail_class_rtl ) . '">';
				if ( count( $displaySections ) == 1 ) {
					$content .= "<div class='arm_account_detail_tab_content_wrapper' style='border:1px solid #dee3e9;'>";
					$content .= '<div class="arm_account_detail_tab arm_account_detail_tab_content arm_account_content_active" data-tab="' . esc_attr($displaySections[0]) . '">';
					if ( $tab == 'membership' ) {
						$content .= $this->arm_account_detail_tab_content( $displaySections[0], $user_id, $show_subscription );
					} else {

						$content .= $this->arm_account_detail_tab_content( $displaySections[0], $user_id, false, $atts['fields'], $atts['social_fields'], array(), array(), $atts );
					}
					$content .= '</div>';
					$content .= '</div>';
				} else {
					$tabLinks = $tabContent = $tabContentActiveClass = '';
					$i        = 0;
					foreach ( $displaySections as $tab ) {
						$tabLinkClass          = 'arm_account_link_tab';
						$tabBtnClass           = 'arm_account_btn_tab';
						$tabContentActiveClass = 'arm_account_content_right';
						if ( $i == 0 ) {
							$tabLinkClass         .= ( $i == 0 ) ? ' arm_account_link_tab_active' : '';
							$tabBtnClass          .= ( $i == 0 ) ? ' arm_account_btn_tab_active' : '';
							$tabContentActiveClass = 'arm_account_content_active';
						}
						$tabLinks   .= '<li class="' . esc_attr($tabLinkClass) . '" data-tab="' . esc_attr($tab) . '">';
						$tabLinks   .= '<a href="javascript:void(0)">' . $defaultTabSettings[ $tab ] . '</a>';
						$tabLinks   .= '</li>';
						$tabContent .= '<div class="' . esc_attr($tabBtnClass) . '" data-tab="' . esc_attr($tab) . '"><a href="javascript:void(0)">' . $defaultTabSettings[ $tab ] . '</a></div>';
						$tabContent .= '<div class="arm_account_detail_tab arm_account_detail_tab_content ' . esc_attr($tabContentActiveClass) . '" data-tab="' . esc_attr($tab) . '">';
						if ( $tab == 'membership' ) {
							$tabContent .= $this->arm_account_detail_tab_content( $tab, $user_id, $show_subscription );
						} else {
							$tabContent .= $this->arm_account_detail_tab_content( $tab, $user_id );
						}
						$tabContent .= '</div>';
						$i++;
					}
					$tabLinks .= '<li class="arm_account_slider"></li>';
					$content  .= '<div class="arm_account_tabs_wrapper">';
					$content  .= '<div class="arm_account_detail_tab_links"><ul>' . $tabLinks . '</ul></div>';
					$content  .= '<div class="arm_account_detail_tab_content_wrapper">' . $tabContent . '</div>';
					$content  .= '</div>';
				}
				$content .= '</div>';
				$content  = apply_filters( 'arm_change_account_details_after_display', $content, $atts );
			} else {
				$default_login_form_id = $arm_member_forms->arm_get_default_form_id( 'login' );

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
				if ( preg_match_all( '/arm_redirect/', $login_page_url, $match ) < 2 ) {
					wp_redirect( $login_page_url );
				}
			}
			return $content;
		}

		function arm_account_detail_tab_content( $tab, $user_id = 0, $show_subscription = false, $fields = '', $social_fields = '', $renew_subscription_options = array(), $cancel_subscription_options = array(), $atts = array() ) {
			global $wp, $wpdb, $current_user, $current_site, $ARMemberLite, $arm_member_forms, $arm_global_settings;
			if ( empty( $renew_subscription_options ) ) {
				$renew_subscription_options['display_renew_btn'] = 'true';
				$renew_subscription_options['renew_text']        = esc_html__( 'Renew', 'armember-membership' );
				$renew_subscription_options['renew_url']         = '';
				$renew_subscription_options['renew_css']         = '';
				$renew_subscription_options['renew_hover_css']   = '';
			}

			if ( empty( $cancel_subscription_options ) ) {
				$cancel_subscription_options['display_cancel_btn'] = 'true';
				$cancel_subscription_options['cancel_text']        = esc_html__( 'Cancel', 'armember-membership' );

				$cancel_subscription_options['cancel_css']       = '';
				$cancel_subscription_options['cancel_hover_css'] = '';
			}

			$content         = $tabTitle = $tabTitleLinks = $tabContent = '';
			$global_settings = $arm_global_settings->global_settings;
			switch ( $tab ) {
				case 'profile':
					$tabTitle   = esc_html__( 'Profile Detail', 'armember-membership' );
					$tabContent = do_shortcode( "[arm_view_profile fields='{$fields}' label='{$atts["label"]}' value='{$atts["value"]}' social_fields='{$social_fields}']" );
					if ( isset( $global_settings['edit_profile_page_id'] ) && $global_settings['edit_profile_page_id'] != 0 ) {
						$editProfilePage = $arm_global_settings->arm_get_permalink( '', $global_settings['edit_profile_page_id'] );
						$tabTitleLinks  .= '<a href="' . esc_url($editProfilePage) . '" class="arm_front_edit_member_link">' . esc_html__( 'Edit Profile', 'armember-membership' ) . '</a>';
					}
					/* $tabTitleLinks .= do_shortcode('[arm_logout label="Logout" type="link" user_info="false" redirect_to="' . ARMLITE_HOME_URL . '"]'); */
					break;
				case 'membership':
					$tabTitle = ( isset( $atts['title'] ) && ! empty( $atts['title'] ) ) ? $atts['title'] : esc_html__( 'Current Membership', 'armember-membership' );
					$label    = "label=''";
					$value    = "value=''";

					if ( isset( $atts ) && ! empty( $atts ) ) {
						$label = "label='" . $atts['membership_label'] . "'";
						$value = "value='" . $atts['membership_value'] . "'";
					}

					$display_renew_btn = "display_renew_button='" . $renew_subscription_options['display_renew_btn'] . "'";
					$renew_text        = "renew_text='" . $renew_subscription_options['renew_text'] . "'";
					$renew_url         = "renew_url='" . $renew_subscription_options['renew_url'] . "'";
					$renew_css         = "renew_css='" . $renew_subscription_options['renew_css'] . "'";
					$renew_hover_css   = "renew_hover_css='" . $renew_subscription_options['renew_hover_css'] . "'";

					$display_cancel_btn = "display_cancel_button='" . $cancel_subscription_options['display_cancel_btn'] . "'";
					$cancel_text        = "cancel_text='" . $cancel_subscription_options['cancel_text'] . "'";

					$cancel_css       = "cancel_css='" . $cancel_subscription_options['cancel_css'] . "'";
					$cancel_hover_css = "cancel_hover_css='" . $cancel_subscription_options['cancel_hover_css'] . "'";

					$shortcode  = '[arm_subscription_detail ' . $label . ' ' . $value . ' ' . $display_renew_btn . ' ' . $renew_text . ' ' . $renew_url . ' ' . $renew_css . ' ' . $renew_hover_css . ' ' . $display_cancel_btn . ' ' . $cancel_text . ' ' . $cancel_css . ' ' . $cancel_hover_css . ']';
					$tabContent = do_shortcode( $shortcode );

					if ( $show_subscription ) {
						$tabTitleLinks = '<a href="' . $change_subscription_url . '" class="arm_front_edit_subscriptions_link">' . esc_html__( 'Change Subscription', 'armember-membership' ) . '</a>';
					}

					break;
				case 'transactions':
					$tabTitle     = esc_html__( 'Transaction History', 'armember-membership' );
					$noRecordText = esc_html__( 'There is no any Transactions found', 'armember-membership' );
					$tabContent   = do_shortcode( '[arm_member_transaction user_id="' . $user_id . '" title="" message_no_record="' . $noRecordText . '"]' );
					break;
				case 'close_account':
					$tabTitle   = esc_html__( 'Close Account', 'armember-membership' );
					$tabContent = do_shortcode( '[arm_close_account]' );
					break;
				case 'logout':
					$tabContent = do_shortcode( '[arm_logout label="Logout" type="link" user_info="false" redirect_to="' . ARMLITE_HOME_URL . '"]' );
					break;
				default:
					break;
			}
			if ( ! empty( $tabTitle ) ) {
				$content .= '<div class="arm_account_detail_tab_heading">' . $tabTitle . '</div>';
			}
			if ( ! empty( $tabTitleLinks ) ) {
				$content .= '<div class="arm_account_detail_tab_link_belt arm_member_detail_action_links">' . $tabTitleLinks . '</div>';
			}
			$content .= '<div class="arm_account_detail_tab_body arm_account_detail_tab_' . $tab . '">' . $tabContent . '</div>';

			return $content;
		}

		function arm_view_profile_shortcode_func( $atts, $content, $tag ) {
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}
			global $arm_global_settings;
			/* ====================/.Begin Set Shortcode Attributes./==================== */
			$atts = shortcode_atts(
				array(
					'title'         => '',
					'label'         => 'first_name,last_name,user_login,user_email',
					'fields'        => '',
					'value'         => 'First Name,Last Name,Username,Email',
					'social_fields' => '',
				),
				$atts,
				$tag
			);
			$atts = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $atts ); //phpcs:ignore
			/* ====================/.End Set Shortcode Attributes./==================== */

			if ( ! empty( $atts['fields'] ) ) {

				$display_fields       = explode( ',', rtrim( $atts['fields'], ',' ) );
				$display_fields_value = array();
			} else {
				$display_fields       = explode( ',', rtrim( $atts['label'], ',' ) );
				$display_fields_value = explode( ',', rtrim( $atts['value'], ',' ) );
			}
			$date_time_format = $arm_global_settings->arm_get_wp_date_format();

			$social_fields = explode( ',', rtrim( $atts['social_fields'], ',' ) );
			global $wp, $wpdb, $wp_roles, $current_user, $current_site, $arm_lite_errors, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_subscription_plans, $arm_social_feature, $arm_members_directory;
			if ( is_user_logged_in() ) {
				$dbFormFields = $arm_member_forms->arm_get_db_form_fields( true );
				$user_id      = get_current_user_id();
				$user         = get_user_by( 'id', $user_id );
				$user_metas   = get_user_meta( $user_id );
				$role_names   = $wp_roles->get_names();
				$content      = '';
				$content     .= '<div class="arm_view_profile_wrapper arm_account_detail_block">';
				$content     .= '<table class="form-table">';

				if ( ! empty( $display_fields ) && ! empty( $dbFormFields ) ) {

					foreach ( $dbFormFields as $fieldMeta_key => $fieldOpt ) {
						if ( in_array( $fieldMeta_key, $display_fields ) ) {

							$key = array_search( $fieldMeta_key, $display_fields );

							$fieldMeta_value = ( isset( $user->$fieldMeta_key ) ? $user->$fieldMeta_key : '' );
							$pattern         = '/^(date\_(.*))/';

							if ( preg_match( $pattern, $fieldMeta_key ) ) {
								$fieldMeta_value = date_i18n( $date_time_format, strtotime( $fieldMeta_value ) );
							}

							if ( is_array( $fieldMeta_value ) ) {
								$fieldMeta_value = $ARMemberLite->arm_array_trim( $fieldMeta_value );
								$fieldMeta_value = implode( ', ', $fieldMeta_value );
							}
							$content    .= '<tr class="form-field">';
							$field_label = ( isset( $display_fields_value[ $key ] ) && ! empty( $display_fields_value[ $key ] ) ) ? stripslashes($display_fields_value[ $key ]) : stripslashes($fieldOpt['label']);
							$content    .= '<th class="arm-form-table-label">' . $field_label . ' :</th>';

							if ( $fieldOpt['type'] == 'file' || $fieldOpt['type'] == 'avatar' ) {
								if ( $fieldMeta_value != '' ) {
									$exp_val        = explode( '/', $fieldMeta_value );
									$filename       = $exp_val[ count( $exp_val ) - 1 ];
									$file_extension = explode( '.', $filename );
									$file_ext       = $file_extension[ count( $file_extension ) - 1 ];
									if ( in_array( $file_ext, array( 'jpg', 'jpeg', 'jpe', 'png', 'bmp', 'tif', 'tiff', 'JPG', 'JPEG', 'JPE', 'PNG', 'BMP', 'TIF', 'TIFF' ) ) ) {
										$fileUrl = $fieldMeta_value;
									} else {
										$fileUrl = MEMBERSHIPLITE_IMAGES_URL . '/file_icon.svg';
									}
								} else {
									$fileUrl = '';
								}
								if ( $fileUrl != '' ) {
									$content .= '<td class="arm-form-table-content"><a target="__blank" href="' . $fieldMeta_value . '"><img style="max-width: 100px;height: auto;" src="' . $fileUrl . '"></a></td>'; //phpcs:ignore 
								} else {
									$content .= '<td class="arm-form-table-content">' . $fieldMeta_value . '</td>';
								}
							} else {
								$content .= '<td class="arm-form-table-content">' . $fieldMeta_value . '</td>';
							}
							$content .= '</tr>';
						}
					}
				}
				$socialProfileFields = $arm_member_forms->arm_social_profile_field_types();
				if ( ! empty( $social_fields ) && ! empty( $socialProfileFields ) && $arm_social_feature->isSocialFeature ) {
					foreach ( $social_fields as $sfield ) {
						if ( isset( $socialProfileFields[ $sfield ] ) ) {
							$spfMetaKey = 'arm_social_field_' . $sfield;
							$sfValue    = get_user_meta( $user_id, $spfMetaKey, true );
							$content   .= '<tr class="form-field">';
							$content   .= '<th class="arm-form-table-label">' . $socialProfileFields[ $sfield ] . ' :</th>';
							$content   .= '<td class="arm-form-table-content">' . $sfValue . '</td>';
							$content   .= '</tr>';
						}
					}
				}
				$content .= '</table>';
				$content .= '</div>';
			} else {
				$default_login_form_id = $arm_member_forms->arm_get_default_form_id( 'login' );
				return do_shortcode( "[arm_form id='$default_login_form_id' is_referer='1']" );
			}
			return $content;
		}

		function arm_close_account_shortcode_func( $atts, $content, $tag ) {
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}
			/* ====================/.Begin Set Shortcode Attributes./==================== */
			$atts = shortcode_atts(
				array(
					'title'  => '',
					'set_id' => '',
					'css'    => '',
				),
				$atts,
				$tag
			);

			$atts = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $atts ); //phpcs:ignore

			/* ====================/.End Set Shortcode Attributes./==================== */
			global $wp, $wpdb, $wp_roles, $current_user, $arm_lite_errors, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings;

			$common_messages    = $arm_global_settings->arm_get_all_common_message_settings();
			$caFormTitle        = isset( $arm_global_settings->common_message['arm_form_title_close_account'] ) ? $arm_global_settings->common_message['arm_form_title_close_account'] : '';
			$caFormDesc         = isset( $arm_global_settings->common_message['arm_form_description_close_account'] ) ? $arm_global_settings->common_message['arm_form_description_close_account'] : '';
			$passwordFieldLabel = isset( $arm_global_settings->common_message['arm_password_label_close_account'] ) ? $arm_global_settings->common_message['arm_password_label_close_account'] : esc_html__( 'Your Password', 'armember-membership' );
			$submitBtnTxt       = isset( $arm_global_settings->common_message['arm_submit_btn_close_account'] ) ? $arm_global_settings->common_message['arm_submit_btn_close_account'] : esc_html__( 'Submit', 'armember-membership' );
			$caBlankPassMsg     = isset( $arm_global_settings->common_message['arm_blank_password_close_account'] ) ? $arm_global_settings->common_message['arm_blank_password_close_account'] : esc_html__( 'Password cannot be left Blank.', 'armember-membership' );
			if ( is_user_logged_in() ) {
				do_action( 'arm_before_render_close_account_form', $atts );
				$user_id        = get_current_user_id();
				$formRandomID   = arm_generate_random_code();
				$content        = apply_filters( 'arm_before_close_account_shortcode_content', $content, $atts );
				$validation_pos = 'bottom';
				$field_position = 'left';
				$form_style     = array(
					'form_title_position' => 'left',
				);
				if ( ! isset( $atts['set_id'] ) || $atts['set_id'] == '' ) {
					$setform_settings = $wpdb->get_row( $wpdb->prepare("SELECT `arm_form_id`, `arm_form_type`, `arm_form_settings`, `arm_set_name` FROM `".$ARMemberLite->tbl_arm_forms."` WHERE `arm_form_type`=%s AND `arm_is_default`=%d ORDER BY arm_form_id DESC LIMIT 1",'login',1 ));//phpcs:ignore --Reason $ARMemberLite->tbl_arm_forms is a table name
				} else {
					$setform_settings = $wpdb->get_row( $wpdb->prepare("SELECT `arm_form_id`, `arm_form_type`, `arm_form_settings`, `arm_set_name` FROM `".$ARMemberLite->tbl_arm_forms."` WHERE `arm_form_id` = %d AND `arm_form_type`=%s ORDER BY arm_form_id DESC LIMIT 1",$atts['set_id'],'login') );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_forms is a table name
					if ( empty( $setform_settings ) ) {
						$setform_settings = $wpdb->get_row( $wpdb->prepare("SELECT `arm_form_id`, `arm_form_type`, `arm_form_settings`, `arm_set_name` FROM `".$ARMemberLite->tbl_arm_forms."` WHERE `arm_form_type`=%s AND `arm_is_default`='1' ORDER BY arm_form_id DESC LIMIT 1" ,'login'));//phpcs:ignore --Reason $ARMemberLite->tbl_arm_forms is a table name
					}
				}
				$set_style_option  = maybe_unserialize( $setform_settings->arm_form_settings );
				$form_style        = $set_style_option['style'];
				$form_style_class  = ' arm_form_close_account';
				$form_style_class .= ' arm_form_layout_' . $form_style['form_layout'];

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
					$form_style_class .= ' arm_form_rtl';
					$form_style_class .= ' arm_rtl_site';
				} else {
					$form_style_class .= ' arm_form_ltr';
				}
				$validation_pos = ! empty( $form_style['validation_position'] ) ? $form_style['validation_position'] : 'bottom';
				$field_position = ! empty( $form_style['field_position'] ) ? $form_style['field_position'] : 'left';
				$content       .= $this->arm_close_account_form_style( $setform_settings->arm_form_id, $formRandomID );
				if ( isset( $atts['css'] ) && $atts['css'] != '' ) {
					$content .= '<style>' . $this->arm_br2nl( $atts['css'] ) . '</style>';
				}

				$form_attr        = '';
				$form_attr       .= ' data-random-id="' . $formRandomID . '" ';
				$general_settings = isset( $arm_global_settings->global_settings ) ? $arm_global_settings->global_settings : array();
				$spam_protection  = isset( $general_settings['spam_protection'] ) ? $general_settings['spam_protection'] : '';
				$ARMemberLite->arm_session_start();
				$captcha_code = arm_generate_captcha_code();
				if ( ! isset( $_SESSION['ARM_FILTER_INPUT'] ) ) {
					$_SESSION['ARM_FILTER_INPUT'] = array();
				}
				if ( isset( $_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] ) ) {
					unset( $_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] );
				}
				$_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] = $captcha_code;
				if ( ! empty( $spam_protection ) ) {
					$_SESSION['ARM_VALIDATE_SCRIPT']               = true;
					$form_attr                                    .= ' data-submission-key="' . esc_attr($captcha_code) . '" ';
				}
				$content .= '<div class="arm_close_account_container arm_account_detail_block">';
				$content .= '<div class="arm_close_account_form_container arm-form-container">';

				$content .= '<div class="arm_form_message_container">';
				$content .= '<div class="arm_error_msg arm-df__fc--validation__wrap" id="arm_message_text" style="display:none;"></div>';
				$content .= '<div class="arm_success_msg" id="arm_message_text" style="display:none;"></div>';
				$content .= '</div>';
				$content .= '<form method="post" name="arm_form_ca" id="arm_form' . esc_attr($formRandomID) . '" class="arm_form arm_materialize_form ' . esc_attr($form_style_class) . '" enctype="multipart/form-data" novalidate ' . $form_attr . '>';
				$content .= '<div class="arm-df-wrapper arm_msg_pos_' . esc_attr($validation_pos) . '">';
				$content .= '<div class="arm-df__fields-wrapper arm-df__fields-wrapper_close_account arm_field_position_' . esc_attr($field_position) . ' arm_front_side_form">';
				if ( ! empty( $caFormTitle ) ) {
					$form_title_position = ( ! empty( $form_style['form_title_position'] ) ) ? $form_style['form_title_position'] : 'left';
					$content            .= '<div class="arm-df__heading armalign' . esc_attr($form_title_position) . '">';
					$content            .= '<span class="arm-df__heading-text">' . $caFormTitle . '</span>';
					$content            .= '</div>';
				}
				if ( ! empty( $caFormDesc ) ) {
					$content .= '<div class="arm_close_account_message">' . $caFormDesc . '</div>';
				}
				$content .= '<div class="armclear"></div>';
				$content .= '<div class="arm-control-group arm-df__form-group arm-df__form-group_password" id="arm-df__form-group_password_ca">';
				$content .= '<div class="arm_form_label_wrapper arm-df__field-label arm_form_member_field_password">';
				// $content .= '<div class="arm_member_form_field_label">';
				$content .= '<div class="arm-df__label-asterisk">*</div>';
				$content .= '<label class="arm_form_field_label_text">' . $passwordFieldLabel . '</label>';
				// $content .= '</div>';
				$content .= '</div>';
				// $content .= '<div class="arm_label_input_separator"></div>';
				$content .= '<div class="arm-df__form-field">';
				$content .= '<div class="arm-df__form-field-wrap_password arm-controls arm-df__form-field-wrap">';
				// $content .= '<label class="arm-df__label-text" for="arm_close_account_pass_' . $formRandomID . '">' . $passwordFieldLabel . '</label>';
				$content .= '<input name="pass" id="arm_close_account_pass_' . esc_attr($formRandomID) . '" type="password" autocomplete="off" value="" class="arm-df__form-control" required="required" data-validation-required-message="' . ( esc_html__( 'Password can not be left blank', 'armember-membership' ) ) . '" data-msg-invalid="' . ( esc_html__( 'Please enter valid data', 'armember-membership' ) ) . '">';
				if ( $form_style['form_layout'] == 'writer_border' ) {
					$content .= '<div class="arm-notched-outline">';
					$content .= '<div class="arm-notched-outline__leading"></div>';
					$content .= '<div class="arm-notched-outline__notch">';
				}
				$content .= '<label class="arm-df__label-text" for="arm_close_account_pass_' . esc_attr($formRandomID) . '">' . $passwordFieldLabel . '</label>';
				if ( $form_style['form_layout'] == 'writer_border' ) {
					$content .= '</div>';
					$content .= '<div class="arm-notched-outline__trailing"></div>';
					$content .= '</div>';
				}
				$suffix_eye_icon_cls = 'arm_visible_password';
				if ( isset( $form_style['form_layout'] ) && ( $form_style['form_layout'] == 'writer' || $form_style['form_layout'] == 'writer_border' ) ) {

					$suffix_eye_icon_cls = ' arm_visible_password_material ';
				}
				$content .= '<button type="button" class="arm-df__fc-icon --arm-suffix-icon ' . esc_attr($suffix_eye_icon_cls) . ' "><i class="armfa armfa-eye"></i></button>';
				// $content .= '<div class="arm-df__fc--validation">';
				// $content .= '<div data-ng-message="required" class="arm_error_msg arm-df__fc--validation__wrap"><div class="arm_error_box_arrow"></div>' . $caBlankPassMsg . '</div>';
				// $content .= '<div data-ng-message="invalid" class="arm_error_msg arm-df__fc--validation__wrap"><div class="arm_error_box_arrow"></div>' . esc_html__('Please enter valid password', 'armember-membership') . '</div>';
				// $content .= '</div>';
				$content .= '</div>';
				$content .= '</div>';
				$content .= '</div>';
				/* ---------------------------------------------------------- */
				$content .= '<div class="arm-df__form-group arm-df__form-group_submit arm_admin_form_field_container">';
				// $content .= '<div class="arm_form_label_wrapper arm-df__field-label arm_form_member_field_submit"></div>';

				if(file_exists(ABSPATH . 'wp-admin/includes/file.php')){
					require_once(ABSPATH . 'wp-admin/includes/file.php');
				}

				WP_Filesystem();
				global $wp_filesystem;
				$arm_loader_url = MEMBERSHIPLITE_IMAGES_URL . "/loader.svg";
				$arm_loader_img = $wp_filesystem->get_contents($arm_loader_url);

				$content     .= '<div class="arm-df__form-field">';
				$content     .= '<div class="arm-df__form-field-wrap_submit arm-df__form-field-wrap">';
				$btnAttr      = ( current_user_can( 'administrator' ) ) ? 'disabled="disabled"' : '';
				$content     .= '<button class="arm-df__form-control-submit-btn arm-df__form-group_button arm_close_account_btn" type="submit" ' . $btnAttr . '><span class="arm_spinner">' . $arm_loader_img . '</span>' . $submitBtnTxt . '</button>';
				$content     .= '</div>';
				$content     .= '</div>';
				$content     .= '</div>';
				$content     .= '</div>';
				$content     .= '<div class="armclear"></div>';
				$content     .= '<input type="hidden" name="arm_action" value="close_account"/>';
				$content     .= '<input type="hidden" name="id" value="' . esc_attr($user_id) . '"/>';
				$arm_wp_nonce = wp_create_nonce( 'arm_wp_nonce' );
				$content     .= '<input type="hidden" name="arm_wp_nonce" value="' . esc_attr($arm_wp_nonce) . '"/>';
				$content .= do_shortcode( '[armember_spam_filters]' );
				$content     .= '</div>';
				$content     .= '</form>';
				$content     .= '</div>';
				$content     .= '</div>';
				$content      = apply_filters( 'arm_after_close_account_shortcode_content', $content, $atts );
			} else {
				$default_login_form_id = $arm_member_forms->arm_get_default_form_id( 'login' );

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
				if ( preg_match_all( '/arm_redirect/', $login_page_url, $match ) < 2 ) {
					wp_redirect( $login_page_url );
				}
			}
			$ARMemberLite->enqueue_angular_script();

			$isEnqueueAll = $arm_global_settings->arm_get_single_global_settings( 'enqueue_all_js_css', 0 );
			if ( $isEnqueueAll == '1' ) {
				$content .= '<script type="text/javascript" data-cfasync="false">
                                    jQuery(document).ready(function (){
                                        arm_do_bootstrap_angular();
                                    });';
				$content .= '</script>';
			}

			return $content;
		}

		function arm_membership_detail_shortcode_func( $atts, $content, $tag ) {
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}
			/* ====================/.Begin Set Shortcode Attributes./==================== */

			$default_membership_fields = esc_html__( 'No.', 'armember-membership' ) . ',' . esc_html__( 'Membership Plan', 'armember-membership' ) . ',' . esc_html__( 'Plan Type', 'armember-membership' ) . ',' . esc_html__( 'Starts On', 'armember-membership' ) . ',' . esc_html__( 'Expires On', 'armember-membership' ) . ',' . esc_html__( 'Cycle Date', 'armember-membership' ) . ',' . esc_html__( 'Action', 'armember-membership' );
			$atts                      = shortcode_atts(
				array(
					'title'                      => esc_html__( 'Current Membership', 'armember-membership' ),
					'membership_label'           => 'current_membership_no,current_membership_is,current_membership_started_on,current_membership_expired_on,current_membership_next_billing_date,action_button',
					'membership_value'           => $default_membership_fields,
					'display_renew_button'       => 'true',
					'renew_css'                  => '',
					'renew_hover_css'            => '',
					'renew_text'                 => esc_html__( 'Renew', 'armember-membership' ),
					'make_payment_text'          => esc_html__( 'Make Payment', 'armember-membership' ),
					'display_cancel_button'      => 'true',
					'cancel_css'                 => '',
					'cancel_hover_css'           => '',
					'cancel_text'                => esc_html__( 'Cancel', 'armember-membership' ),
					'display_update_card_button' => 'true',
					'update_card_css'            => '',
					'update_card_hover_css'      => '',
					'update_card_text'           => esc_html__( 'Update Card', 'armember-membership' ),
					'setup_id'                   => '',
					'trial_active'               => esc_html__( 'trial active', 'armember-membership' ),
					'cancel_message'             => esc_html__( 'Your Subscription has been cancelled.', 'armember-membership' ),
					'message_no_record'          => esc_html__( 'There is no membership found.', 'armember-membership' ),
				),
				$atts,
				$tag
			);

			$atts = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend_only_kses'), $atts ); //phpcs:ignore
			extract( $atts );

			/* ====================/.End Set Shortcode Attributes./==================== */
			global $wp, $wpdb, $current_user, $current_site, $arm_lite_errors, $arm_member_forms, $ARMemberLite, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways, $arm_membership_setup;
			$date_format = $arm_global_settings->arm_get_wp_date_format();
			$labels      = explode( ',', rtrim( $atts['membership_label'], ',' ) );
			$values      = explode( ',', rtrim( $atts['membership_value'], ',' ) );
			$ARMemberLite->enqueue_angular_script(true);
			wp_enqueue_style('arm_form_style_css');
			if ( is_user_logged_in() ) {

				$ARMemberLite->arm_session_start();

				$setup_plans = array();
				if ( isset( $setup_id ) && $setup_id > 0 ) {
					$setup_data  = $arm_membership_setup->arm_get_membership_setup( $setup_id );
					$setup_plans = isset( $setup_data['arm_setup_modules']['modules']['plans'] ) ? $setup_data['arm_setup_modules']['modules']['plans'] : array();
				} else {
					$setup_data = $wpdb->get_row( 'SELECT * FROM `'.$ARMemberLite->tbl_arm_membership_setup.'` ORDER BY `arm_setup_id`', ARRAY_A );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_membership_setup is a table name
					if ( ! empty( $setup_data ) ) {
						$setup_id                        = isset( $setup_data['arm_setup_id'] ) ? $setup_data['arm_setup_id'] : 0;
						$setup_data['arm_setup_modules'] = maybe_unserialize( $setup_data['arm_setup_modules'] );
						$setup_plans                     = isset( $setup_data['arm_setup_modules']['modules']['plans'] ) ? $setup_data['arm_setup_modules']['modules']['plans'] : array();
					} else {
						$setup_plans = $arm_subscription_plans->arm_get_all_active_subscription_plans();
					}
				}
				$user_id    = get_current_user_id();
				$user_plans = get_user_meta( $user_id, 'arm_user_plan_ids', true );
				$user_plans = ! empty( $user_plans ) ? $user_plans : array();

				$user_future_plans = get_user_meta( $user_id, 'arm_user_future_plan_ids', true );
				$user_future_plans = ! empty( $user_future_plans ) ? $user_future_plans : array();
				$content           = apply_filters( 'arm_before_current_membership_shortcode_content', $content, $atts );
				$content          .= "<div class='arm_current_membership_container_loader_img'>";
				$content          .= '</div>';
				$content          .= "<div class='arm_current_membership_container'>";
				$frontfontstyle    = $arm_global_settings->arm_get_front_font_style();
				// $content .=!empty($frontfontstyle['google_font_url']) ? '<link id="google-font" rel="stylesheet" type="text/css" href="' . $frontfontstyle['google_font_url'] . '" />' : '';
				$content .= ! empty( $frontfontstyle['google_font_url'] ) ? wp_enqueue_style( 'google-font', $frontfontstyle['google_font_url'], array(), MEMBERSHIPLITE_VERSION ) : '';

				$content                      .= '<style type="text/css">';
				$currentMembershipWrapperClass = '.arm_current_membership_container';
				if ( empty( $renew_css ) ) {
					$content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_renew_subscription_button{ text-transform: none; " . $frontfontstyle['frontOptions']['button_font']['font'] . '}';
				} else {
					$content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_renew_subscription_button{" . $this->arm_br2nl( $renew_css ) . '}';
				}

				if ( empty( $renew_hover_css ) ) {
					$content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_renew_subscription_button:hover{" . $frontfontstyle['frontOptions']['button_font']['font'] . '}';
				} else {
					$content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_renew_subscription_button:hover{" . $this->arm_br2nl( $renew_hover_css ) . '}';
				}
				if ( empty( $cancel_css ) ) {
					$content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_cancel_subscription_button{text-transform: none; " . $frontfontstyle['frontOptions']['button_font']['font'] . '}';
				} else {
					$content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_cancel_subscription_button{" . $this->arm_br2nl( $cancel_css ) . '}';
				}
				if ( empty( $cancel_hover_css ) ) {
					$content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_cancel_subscription_button:hover{" . $frontfontstyle['frontOptions']['button_font']['font'] . '}';
				} else {
					$content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_cancel_subscription_button:hover{" . $this->arm_br2nl( $cancel_hover_css ) . '}';
				}

				if ( empty( $update_card_css ) ) {
					$content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_update_card_button_style{text-transform: none; " . $frontfontstyle['frontOptions']['button_font']['font'] . '}';
				} else {
					$content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_update_card_button_style{" . $this->arm_br2nl( $update_card_css ) . '}';
				}

				if ( empty( $update_card_hover_css ) ) {
					$content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_update_card_button_style:hover{" . $frontfontstyle['frontOptions']['button_font']['font'] . '}';
				} else {
					$content .= " $currentMembershipWrapperClass .arm_current_membership_list_item .arm_update_card_button_style:hover{" . $this->arm_br2nl( $update_card_hover_css ) . '}';
				}

				$content .= "
                    $currentMembershipWrapperClass .arm_current_membership_heading_main{
						". esc_attr( $frontfontstyle['frontOptions']['level_1_font']['font']) ."
                    }
                    $currentMembershipWrapperClass .arm_current_membership_list_header th{
						". esc_attr( $frontfontstyle['frontOptions']['level_2_font']['font']) ."
                    }
                    $currentMembershipWrapperClass .arm_current_membership_list_item td{
						". esc_attr( $frontfontstyle['frontOptions']['level_3_font']['font']) ."
                    }";
				$content .= '</style>';
				if ( ! empty( $title ) ) {
					$content .= '<div class="arm_current_membership_heading_main">' . esc_attr( $title ) . '</div>';
					$content .= '<div class="armclear"></div>';
				}

				$formRandomID                  = $setup_id . '_' . arm_generate_random_code();

				$captcha_code = arm_generate_captcha_code();
				if ( ! isset( $_SESSION['ARM_FILTER_INPUT'] ) ) {
					$_SESSION['ARM_FILTER_INPUT'] = array();
				}
				if ( isset( $_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] ) ) {
					unset( $_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] );
				}
				$_SESSION['ARM_FILTER_INPUT'][ $formRandomID ] = $captcha_code;
				
				$content             .= '<form method="POST" class="arm_current_membership_form_container" data-random-id="' . esc_attr($formRandomID) . '">';
				$content             .= '<div class="arm_template_loading" style="display: none;"><img src="' . MEMBERSHIPLITE_IMAGES_URL . '/loader.gif" alt="Loading.."></div>'; //phpcs:ignore 
				$content             .= "<div class='arm_current_membership_wrapper'>";
				$total_columns        = 0;
					$has_no           = true;
					$has_plan         = true;
					$has_start_date   = true;
					$has_end_date     = true;
					$has_trial_period = true;

					$has_renew_date          = true;
					$has_remaining_occurence = true;
					$has_recurring_profile   = true;
					$has_action_btn          = true;

				if ( in_array( 'current_membership_no', $labels ) ) {
					$label_key = array_search( 'current_membership_no', $labels );
					$l_has_no  = ! empty( $values[ $label_key ] ) ? $values[ $label_key ] : esc_html__( 'No.', 'armember-membership' );
				} else {
					$has_no = false;
				}

				if ( in_array( 'current_membership_is', $labels ) ) {
					$label_key  = array_search( 'current_membership_is', $labels );
					$l_has_plan = ! empty( $values[ $label_key ] ) ? $values[ $label_key ] : esc_html__( 'Membership Plan', 'armember-membership' );
				} else {
					$has_plan = false;
				}

				if ( in_array( 'current_membership_started_on', $labels ) ) {
					$label_key    = array_search( 'current_membership_started_on', $labels );
					$l_start_date = ! empty( $values[ $label_key ] ) ? $values[ $label_key ] : esc_html__( 'Start Date', 'armember-membership' );
				} else {
					$has_start_date = false;
				}

				if ( in_array( 'current_membership_expired_on', $labels ) ) {
					$label_key  = array_search( 'current_membership_expired_on', $labels );
					$l_end_date = ! empty( $values[ $label_key ] ) ? $values[ $label_key ] : esc_html__( 'End Date', 'armember-membership' );
				} else {
					$has_end_date = false;
				}

				if ( in_array( 'current_membership_recurring_profile', $labels ) ) {
					$label_key           = array_search( 'current_membership_recurring_profile', $labels );
					$l_recurring_profile = ! empty( $values[ $label_key ] ) ? $values[ $label_key ] : esc_html__( 'Recurring Profile', 'armember-membership' );
				} else {
					$has_recurring_profile = false;
				}

				if ( in_array( 'current_membership_remaining_occurence', $labels ) ) {
					$label_key             = array_search( 'current_membership_remaining_occurence', $labels );
					$l_remaining_occurence = ! empty( $values[ $label_key ] ) ? $values[ $label_key ] : esc_html__( 'Remaining Occurence', 'armember-membership' );
				} else {
					$has_remaining_occurence = false;
				}

				if ( in_array( 'current_membership_next_billing_date', $labels ) ) {
					$label_key    = array_search( 'current_membership_next_billing_date', $labels );
					$l_renew_date = ! empty( $values[ $label_key ] ) ? $values[ $label_key ] : esc_html__( 'Renewal On', 'armember-membership' );
				} else {
					$has_renew_date = false;
				}

				if ( in_array( 'trial_period', $labels ) ) {
					$label_key      = array_search( 'trial_period', $labels );
					$l_trial_period = ! empty( $values[ $label_key ] ) ? $values[ $label_key ] : esc_html__( 'Trial Period', 'armember-membership' );
				} else {
					$has_trial_period = false;
				}

				if ( in_array( 'action_button', $labels ) ) {
					$label_key    = array_search( 'action_button', $labels );
					$l_action_btn = ! empty( $values[ $label_key ] ) ? $values[ $label_key ] : esc_html__( 'Action', 'armember-membership' );
				} else {
					$has_action_btn = false;
				}
				if ( is_rtl() ) {
					$is_current_membership_class_rtl = 'is_current_membership_class_rtl';
				} else {
					$is_current_membership_class_rtl = '';
				}
					$content .= "<div class='arm_current_membership_content " . esc_attr($is_current_membership_class_rtl) . "'>";
					$content .= "<table class='arm_user_current_membership_list_table arm_front_grid' cellpadding='0' cellspacing='0' border='0'>";
					$content .= '<thead>';
					$content .= "<tr class='arm_current_membership_list_header' id='arm_current_membership_list_header'>";

				if ( $has_no ) :
					$content .= "<th class='arm_cm_sr_no' id='arm_cm_sr_no'>{$l_has_no}</th>";
					$total_columns++;
					endif;
				if ( $has_plan ) :
					$content .= "<th class='arm_cm_plan_name' id='arm_cm_plan_name'>{$l_has_plan}</th>";
					$total_columns++;
					endif;
				if ( $has_recurring_profile ) :
					$content .= "<th class='arm_cm_plan_profile' id='arm_cm_plan_profile'>{$l_recurring_profile}</th>";
					$total_columns++;
					endif;
				if ( $has_start_date ) :
					$content .= "<th class='arm_cm_plan_start_date' id='arm_cm_plan_start_date'>{$l_start_date}</th>";
					$total_columns++;
					endif;
				if ( $has_end_date ) :
					$content .= "<th class='arm_cm_plan_end_date' id='arm_cm_plan_end_date'>{$l_end_date}</th>";
					$total_columns++;
					endif;
				if ( $has_trial_period ) :
					$content .= "<th class='arm_cm_plan_trial_period' id='arm_cm_plan_trial_period'>{$l_trial_period}</th>";
					$total_columns++;
					endif;

				if ( $has_remaining_occurence ) :
					$content .= "<th class='arm_cm_plan_remaining_occurence' id='arm_cm_plan_remaining_occurence'>{$l_remaining_occurence}</th>";
					$total_columns++;
					endif;
				if ( $has_renew_date ) :
					$content .= "<th class='arm_cm_plan_renew_date' id='arm_cm_plan_renew_date'>{$l_renew_date}</th>";
					$total_columns++;
					endif;

				if ( $has_action_btn ) :

					if ( $display_cancel_button == 'true' || $display_renew_button == 'true' || $display_update_card_button == 'true' ) {

						$content .= "<th class='arm_cm_plan_action_btn' id='arm_cm_plan_action_btn'>{$l_action_btn}</th>";
						$total_columns++;
					}
					endif;

					$content .= '</tr>';
					$content .= '</thead>';

				if ( ! empty( $user_future_plans ) ) {

					$user_all_plans = array_merge( $user_plans, $user_future_plans );
				} else {
					$user_all_plans = $user_plans;
				}

				if ( ! empty( $user_all_plans ) ) {

					$sr_no                = 0;
					$change_plan_to_array = array();
					foreach ( $user_all_plans as $user_plan ) {
						$planData      = get_user_meta( $user_id, 'arm_user_plan_' . $user_plan, true );
						$curPlanDetail = $planData['arm_current_plan_detail'];
						$start_plan    = $planData['arm_start_plan'];
						if ( ! empty( $planData['arm_started_plan_date'] ) && $planData['arm_started_plan_date'] <= $start_plan ) {
							$start_plan = $planData['arm_started_plan_date'];
						}
						$expire_plan    = $planData['arm_expire_plan'];
						$change_plan    = $planData['arm_change_plan_to'];
						$effective_from = $planData['arm_subscr_effective'];

						if ( $change_plan != '' && $effective_from != '' && ! empty( $effective_from ) && ! empty( $change_plan ) ) {
							$change_plan_to_array[ $change_plan ] = $effective_from;

						}

						$payment_mode      = '';
						$payment_cycle     = '';
						$is_plan_cancelled = '';
						$completed         = '';
						$recurring_time    = '';
						$recurring_profile = '';
						$next_due_date     = '-';
						$user_payment_mode = '';
						if ( ! empty( $curPlanDetail ) ) {
							$plan_info = new ARM_Plan_Lite( 0 );
							$plan_info->init( (object) $curPlanDetail );
						} else {
							$plan_info = new ARM_Plan_Lite( $user_plan );
						}

						$arm_plan_is_suspended = '';
						$suspended_plan_ids    = get_user_meta( $user_id, 'arm_user_suspended_plan_ids', true );
						$suspended_plan_ids    = ( isset( $suspended_plan_ids ) && ! empty( $suspended_plan_ids ) ) ? $suspended_plan_ids : array();
						if ( ! empty( $suspended_plan_ids ) ) {
							if ( in_array( $user_plan, $suspended_plan_ids ) ) {
								$arm_plan_is_suspended = '<br/><span style="color: red;">(' . esc_html__( 'Suspended', 'armember-membership' ) . ')</span>';
							}
						}

						if ( $plan_info->exists() ) {
							$sr_no++;
							$plan_options = $plan_info->options;

							if ( $plan_info->is_recurring() ) {
								$completed              = $planData['arm_completed_recurring'];
								$is_plan_cancelled      = $planData['arm_cencelled_plan'];
								$payment_mode           = $planData['arm_payment_mode'];
								$payment_cycle          = $planData['arm_payment_cycle'];
								$recurring_plan_options = $plan_info->prepare_recurring_data( $payment_cycle );
								$recurring_time         = $recurring_plan_options['rec_time'];
								$next_due_date          = $planData['arm_next_due_payment'];

								if ( $payment_mode == 'auto_debit_subscription' ) {
									$user_payment_mode = '<br/>( ' . esc_html__( 'Auto Debit', 'armember-membership' ) . ' )';
								} else {
									$user_payment_mode = '';
								}
								$arm_trial_start_date = $planData['arm_trial_start'];
								$arm_is_user_in_trial = $planData['arm_is_trial_plan'];

								if ( $recurring_time == 'infinite' || empty( $expire_plan ) ) {
									$remaining_occurence = esc_html__( 'Never Expires', 'armember-membership' );
								} else {
									$remaining_occurence = $recurring_time - $completed;
								}

								if ( $remaining_occurence > 0 || $recurring_time == 'infinite' ) {
									if ( ! empty( $next_due_date ) ) {
										$next_due_date = date_i18n( $date_format, $next_due_date );
									}
								} else {
									$next_due_date = '';
								}

								$arm_is_user_in_grace = $planData['arm_is_user_in_grace'];

								$arm_grace_period_end = $planData['arm_grace_period_end'];
							} else {
								$recurring_profile    = '-';
								$arm_trial_start_date = '';
								$remaining_occurence  = '-';
								$arm_is_user_in_grace = 0;
								$arm_grace_period_end = '';
								$arm_is_user_in_trial = 0;

							}

							$recurring_profile = $plan_info->new_user_plan_text( false, $payment_cycle );

							$content .= "<tr class='arm_current_membership_list_item arm_current_membership_tr_" . $user_plan . "' id='arm_current_membership_tr_" . $user_plan . "'>";

							if ( $has_no ) :
								$content .= "<td data-label='".esc_attr($l_has_no)."' class='arm_current_membership_list_item_plan_sr' id='arm_current_membership_list_item_plan_sr_" . esc_attr($user_plan) . "'>" . $sr_no . '</td>';
							endif;

							if ( $has_plan ) :
								$content .= "<td data-label='".esc_attr($l_has_plan)."' class='arm_current_membership_list_item_plan_name' id='arm_current_membership_list_item_plan_name_" . esc_attr($user_plan) . "' aria-label='".esc_html($plan_info->name)."'>" . stripslashes( $plan_info->name ) . ' ' . $arm_plan_is_suspended . '</td>';
							endif;
							if ( $has_recurring_profile ) :
								$content .= "<td data-label='".esc_attr($l_recurring_profile)."' class='arm_current_membership_list_item_plan_profile' id='arm_current_membership_list_item_plan_profile_" . esc_attr($user_plan) . "'>";
								/*
								 if ($plan_info->is_recurring()) {
								  $content .= $plan_info->user_plan_text(false, $payment_cycle);
								  } else {
								  $content .="--";
								  }
								  if ($plan_info->is_recurring()) {
								  if ($payment_mode == 'auto_debit_subscription') {
								  $content .= ' ( ' . esc_html__('Auto Debit Subscription', 'armember-membership') . ' )';
								  } else {
								  $content .= ' ( ' . esc_html__('Manual Subscription', 'armember-membership') . ' )';
								  }
								  } */

								$content .= $recurring_profile;

								$content .= '</td>';
							endif;
							if ( $has_start_date ) :
								$arm_plan_start_date = date_i18n( $date_format, $planData['arm_start_plan'] );							
								$content .= "<td data-label='".esc_attr($l_start_date)."' class='arm_current_membership_list_item_plan_start' id='arm_current_membership_list_item_plan_start_" . esc_attr($user_plan) . "' aria-label='".esc_html($arm_plan_start_date)."'>";
								if ( ! empty( $start_plan ) ) {
									$content .= $arm_plan_start_date;
								}

								if ( ! empty( $arm_trial_start_date ) ) {
									if ( $arm_is_user_in_trial == 1 || $arm_is_user_in_trial == '1' ) {

										if ( $arm_trial_start_date < $start_plan && !empty($atts['trial_active']) ) {
											$content .= "<br/><span class='arm_current_membership_trial_active'>(" . $atts['trial_active'] . ')</span>';
										}
									}
								}
								 $content .= '</td>';

							endif;
							if ( $has_end_date ) :
								$arm_plan_end_date = date_i18n( $date_format, $planData['arm_expire_plan'] );		
								$content .= "<td data-label='".esc_attr($l_end_date)."' class='arm_current_membership_list_item_plan_end' id='arm_current_membership_list_item_plan_end_" . esc_attr($user_plan) . "' aria-label='".esc_html($arm_plan_end_date)."'>";

								if ( $plan_info->is_free() || $plan_info->is_lifetime() || ( $plan_info->is_recurring() && $recurring_time == 'infinite' ) ) {
									$content .= esc_html__( 'Never Expires', 'armember-membership' );
								} else {

									if ( isset( $plan_options['access_type'] ) && ! in_array( $plan_options['access_type'], array( 'infinite', 'lifetime' ) ) ) {

										if ( ! empty( $arm_plan_end_date ) ) {

											$membership_expire_content = $arm_plan_end_date;

											$content .= $membership_expire_content;
										} else {
											$content .= '-';
										}
									} else {

										$content .= '-';
									}
								}
								$content .= '</td>';
							endif;
							if ( $has_trial_period ) :
								$content .= "<td data-label='".esc_attr($l_trial_period)."' class='arm_current_membership_list_item_plan_trial_period' id='arm_current_membership_list_item_plan_trial_period_" . esc_attr($user_plan) . "'>";
								if ( ! empty( $arm_trial_start_date ) ) {
									$content .= date_i18n( $date_format, $arm_trial_start_date );
									$content .= ' ' . esc_html__( 'To', 'armember-membership' );
									$content .= ' ' . date_i18n( $date_format, strtotime( '-1 day', $start_plan ) );
								} else {
									$content .= '-';
								}

								$content .= '</td>';
							endif;

							if ( $has_remaining_occurence ) :
								$content .= "<td data-label='".esc_attr($l_renew_date)."' class='arm_current_membership_list_item_remaining_occurence' id='arm_current_membership_list_item_remaining_occurence_" . esc_attr($user_plan) . "' aria-label='".esc_html($user_plan['remaining_occurence'])."'>";

								/*
								 if ($plan_info->is_recurring()) {

								  if ($recurring_time == 'infinite') {
								  $content .= '--';
								  } else {
								  if (!empty($expire_plan)) {
								  if ($recurring_time == 'infinite') {
								  $content .= '--';
								  } else {

								  if ($plan_info->has_trial_period() && $completed == 0) {
								  $remaining = $recurring_time;
								  $content .= $recurring_time;
								  } else {
								  $total_rec = $recurring_time;
								  $remaining = $total_rec - $completed;
								  $content .= $remaining;
								  }
								  }
								  } else {
								  $content .= '--';
								  }
								  }
								  } else {
								  $content .="--";
								  } */

								$content .= $remaining_occurence;
								$content .= '</td>';
							endif;
							if ( $has_renew_date ) :							
								$content .= "<td data-label='".esc_attr($l_renew_date)."' class='arm_current_membership_list_item_renew_date' id='arm_current_membership_list_item_renew_date_" . esc_attr($user_plan) . "' aria-label='".esc_html($next_due_date)."'>";

								$content      .= $next_due_date;
								$grace_message = '';

								$next_cycle_due = '';
								if ( $plan_info->is_recurring() ) {

									if ( ! empty( $expire_plan ) ) {
										if ( $remaining_occurence == 0 ) {
											$next_cycle_due = esc_html__( 'No cycles due', 'armember-membership' );
										} else {
											$next_cycle_due = '<br/>(' . $remaining_occurence . ' ' . esc_html__( 'cycles due', 'armember-membership' ) . ')';
										}
									}

									if ( $arm_is_user_in_grace == '1' || $arm_is_user_in_grace == 1 ) {
										$arm_grace_period_end = date_i18n( $date_format, $arm_grace_period_end );
										$grace_message       .= '<br/>( ' . esc_html__( 'grace period expires on', 'armember-membership' ) . $arm_grace_period_end . ' )';

									}
								}

								$content .= $next_cycle_due . $grace_message . $user_payment_mode . '</td>';
							endif;
							if ( $has_action_btn ) :
								$arm_disable_button = '';
								if ( $setup_id == '' || $setup_id == '0' ) {
									$arm_disable_button = 'disabled';
								} else {
									$setup_data = $arm_membership_setup->arm_get_membership_setup( $setup_id );
									if ( empty( $setup_data ) ) {
										$arm_disable_button = 'disabled';
									}
								}

								if ( $display_cancel_button == 'true' || $display_renew_button == 'true' || $display_update_card_button == 'true' ) {
										$content .= "<td id='arm_cm_plan_action_btn' data-label='". esc_attr($l_action_btn) ."' class='arm_current_membership_list_item_action_btn_" . esc_attr($user_plan) . "'><div class='arm_current_membership_action_div'>";

									if ( ! in_array( $user_plan, $user_future_plans ) ) {
										if ( $display_renew_button == 'true' && ! $plan_info->is_lifetime() && ! $plan_info->is_free() && $is_plan_cancelled != 'yes' ) {
													$make_payment_content = '<div class="arm_cm_renew_btn_div"><button type="button" class= "arm_renew_subscription_button" data-plan_id="' . esc_attr($user_plan) . '" ' . $arm_disable_button . '>' . $make_payment_text . '</button></div>';
											if ( $change_plan == '' || $effective_from == '' || empty( $effective_from ) || empty( $change_plan ) ) {
												$renew_content = '<div class="arm_cm_renew_btn_div"><button type="button" class= "arm_renew_subscription_button" data-plan_id="' . esc_attr($user_plan) . '" ' . $arm_disable_button . '>' . $renew_text . '</button></div>';
											} else {
												$renew_content = '';
											}
											if ( $is_plan_cancelled == 'yes' ) {
												$renew_content = '';
											}
											if ( $plan_info->is_recurring() ) {

												if ( $payment_mode == 'manual_subscription' ) {
													if ( $recurring_time == 'infinite' ) {
														$content .= $make_payment_content;
													} else {
														if ( $remaining_occurence > 0 ) {
															$content .= $make_payment_content;
														} else {

															$now = current_time( 'mysql' );

															$arm_last_payment_status = $wpdb->get_var( $wpdb->prepare( 'SELECT `arm_transaction_status` FROM `'.$ARMemberLite->tbl_arm_payment_log.'` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1', $user_id, $user_plan, $now ) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name

															if ( $arm_last_payment_status == 'failed' ) {

																if ( ! empty( $expire_plan ) ) {

																	if ( strtotime( $now ) < $expire_plan ) {
																			$content .= $make_payment_content;
																	} else {
																		$content .= $renew_content;
																	}
																} else {
																					$content .= $make_payment_content;
																}
															} else {
																 $content .= $renew_content;
															}
														}
													}
												} else {
													if ( $recurring_time != 'infinite' ) {
														if ( $remaining_occurence == 0 ) {

															$now = current_time( 'mysql' );

															$arm_last_payment_status = $wpdb->get_var( $wpdb->prepare( 'SELECT `arm_transaction_status` FROM `'.$ARMemberLite->tbl_arm_payment_log.'` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_created_date`<=%s ORDER BY `arm_log_id` DESC LIMIT 0,1', $user_id, $user_plan, $now ) );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_payment_log is a table name

															if ( $arm_last_payment_status == 'failed' ) {
																if ( ! empty( $expire_plan ) ) {
																	if ( strtotime( $now ) < $expire_plan ) {

																		$content .= $make_payment_content;
																	} else {
																		$content .= $renew_content;
																	}
																} else {
																	$content .= $make_payment_content;
																}
															} else {
																	  $content .= $renew_content;
															}
														}
													}
												}
											} else {
												$content .= $renew_content;
											}
											if ( ( isset( $display_cancel_button ) && $display_cancel_button == 'true' ) && ( isset( $is_plan_cancelled ) && $is_plan_cancelled != 'yes' ) && ! $plan_info->is_recurring() ) {
												$content .= '<div class="arm_cm_cancel_btn_div" id="arm_cm_cancel_btn_div_' . esc_attr($user_plan) . '"><button type="button" id="arm_cancel_subscription_link_' . esc_attr($user_plan) . '" class= "arm_cancel_subscription_button arm_cancel_membership_link" data-plan_id = "' . esc_attr($user_plan) . '">' . $cancel_text . '</button><img src="' . MEMBERSHIPLITE_IMAGES_URL . '/arm_loader.gif" id="arm_field_loader_img_' . esc_attr($user_plan) . '" style="display: none;"/></div>'; //phpcs:ignore 
											}
										}

										if ( $plan_info->is_lifetime() || $plan_info->is_free() ) {
											if ( ( isset( $display_cancel_button ) && $display_cancel_button == 'true' ) && ( isset( $is_plan_cancelled ) && $is_plan_cancelled != 'yes' ) ) {
													$content .= '<div class="arm_cm_cancel_btn_div" id="arm_cm_cancel_btn_div_' . esc_attr($user_plan) . '"><button type="button" id="arm_cancel_subscription_link_' . esc_attr($user_plan) . '" class= "arm_cancel_subscription_button arm_cancel_membership_link" data-plan_id = "' . esc_attr($user_plan) . '">' . $cancel_text . '</button><img src="' . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif" id="arm_field_loader_img_' . esc_attr($user_plan) . '" style="display: none;"/></div>'; //phpcs:ignore 
											}
										}

										if ( $plan_info->is_recurring() ) {
											if ( $display_update_card_button == 'true' && $payment_mode == 'auto_debit_subscription' && $is_plan_cancelled != 'yes' ) {
												if ( $planData['arm_user_gateway'] == 'paypal' ) {
													$active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

													$pg_options = $active_gateways[ $planData['arm_user_gateway'] ];
													$sandbox    = ( isset( $pg_options['paypal_payment_mode'] ) && $pg_options['paypal_payment_mode'] == 'sandbox' ) ? true : false;
													if ( $sandbox ) {
														$paypal_url = 'https://www.sandbox.paypal.com/myaccount/wallet';
													} else {
														$paypal_url = 'https://www.paypal.com/myaccount/wallet';
													}
													$content .= '<div class="arm_cm_update_btn_div"><a href="' . $paypal_url . '" target="_blank"><button type="button" class= "arm_update_card_button_style">' . $update_card_text . '</button></a></div>';
												}
												$arm_card_btn_default = '';
												$content             .= apply_filters( 'arm_get_gateways_update_card_detail_btn', $arm_card_btn_default, $planData, $user_plan, $update_card_text );
											}
											if ( $display_cancel_button == 'true' ) {

												if ( $change_plan == '' || $effective_from == '' || empty( $effective_from ) || empty( $change_plan ) ) {

													if ( isset( $is_plan_cancelled ) && $is_plan_cancelled == 'yes' ) {

														$content .= '<div class="arm_cm_cancel_btn_div" id="arm_cm_cancel_btn_div_' . esc_attr($user_plan) . '"><button type="button" id="arm_cancel_subscription_link_' . esc_attr($user_plan) . '" class= "arm_cancel_subscription_button" data-plan_id = "' . esc_attr($user_plan) . '" style="cursor: default;" disabled="disabled">' . esc_html__( 'Cancelled', 'armember-membership' ) . '</button></div>';
													} else {

														$content .= '<div class="arm_cm_cancel_btn_div" id="arm_cm_cancel_btn_div_' . esc_attr($user_plan) . '"><button type="button" id="arm_cancel_subscription_link_' . esc_attr($user_plan) . '" class= "arm_cancel_subscription_button arm_cancel_membership_link" data-plan_id = "' . esc_attr($user_plan) . '">' . $cancel_text . '</button><img src="' . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif" id="arm_field_loader_img_' . esc_attr($user_plan) . '" style="display: none;"/></div>'; //phpcs:ignore 
													}
												}
											}
										}
									}
										$content .= '</div></td>';
								}

							endif;
							$content .= '</tr>';
						}
					}

					if ( ! empty( $change_plan_to_array ) ) {
						foreach ( $change_plan_to_array as $change_user_plan => $effective_from_date ) {

							if ( ! empty( $change_user_plan ) && ! empty( $effective_from_date ) ) {

								$change_plan_info = new ARM_Plan_Lite( $change_user_plan );

								if ( $change_plan_info->exists() ) {
									$sr_no++;

									$content .= "<tr class='arm_current_membership_list_item' id='arm_current_membership_tr_" . esc_attr($change_user_plan) . "'>";

									if ( $has_no ) :
										$content .= "<td data-label='".esc_attr($l_has_no)."' class='arm_current_membership_list_item_plan_sr' id='arm_current_membership_list_item_plan_sr_" . esc_attr($change_user_plan) . "'>" . $sr_no . '</td>';
								endif;

									if ( $has_plan ) :
										$content .= "<td data-label='".esc_attr($l_has_plan)."' class='arm_current_membership_list_item_plan_name' id='arm_current_membership_list_item_plan_name_" . esc_attr($change_user_plan) . "'>" . stripslashes( $change_plan_info->name ) . '</td>';
								endif;
									if ( $has_recurring_profile ) :
										$content          .= "<td data-label='".esc_attr($l_recurring_profile)."' class='arm_current_membership_list_item_plan_profile' id='arm_current_membership_list_item_plan_profile_" . esc_attr($change_user_plan) . "'>";
										$recurring_profile = $change_plan_info->new_user_plan_text( false, '' );

										$content .= $recurring_profile . '</td>';
								endif;
									if ( $has_start_date ) :

										$content .= "<td data-label='".esc_attr($l_start_date)."' class='arm_current_membership_list_item_plan_start' id='arm_current_membership_list_item_plan_start_" . esc_attr($change_user_plan) . "'>";
										if ( ! empty( $effective_from_date ) ) {
											$content .= date_i18n( $date_format, $effective_from_date );
										}

										 $content .= '</td>';

								endif;
									if ( $has_end_date ) :
										$content .= "<td data-label='".esc_attr($l_end_date)."' class='arm_current_membership_list_item_plan_end' id='arm_current_membership_list_item_plan_end_" . esc_attr($change_user_plan) . "'>";

										$content .= '</td>';
								endif;
									if ( $has_trial_period ) :
										$content .= "<td data-label='".esc_attr($l_trial_period)."' class='arm_current_membership_list_item_plan_trial_period' id='arm_current_membership_list_item_plan_trial_period_" . esc_attr($change_user_plan) . "'>";

										$content .= '</td>';
								endif;

									if ( $has_remaining_occurence ) :
										$content .= "<td data-label='".esc_attr($l_renew_date)."' class='arm_current_membership_list_item_remaining_occurence' id='arm_current_membership_list_item_remaining_occurence_" . esc_attr($change_user_plan) . "'>";

										$content .= '</td>';
								endif;
									if ( $has_renew_date ) :
										$content .= "<td data-label='".esc_attr($l_renew_date)."' class='arm_current_membership_list_item_renew_date' id='arm_current_membership_list_item_renew_date_" . esc_attr($change_user_plan) . "'>";

										$content .= '</td>';
								endif;
									if ( $has_action_btn ) :

										if ( $display_cancel_button == 'true' || $display_renew_button == 'true' || $display_update_card_button == 'true' ) {
											$content .= "<td id='arm_cm_plan_action_btn' data-label='".esc_attr($l_action_btn)."' class='arm_current_membership_list_item_action_btn_" . esc_attr($change_user_plan) . "'>";
											$content .= '</td>';
										}

								endif;
									$content .= '</tr>';
								}
							}
						}
					}
				} else {
					 $content .= "<tr class='arm_current_membership_list_item' id='arm_current_membership_list_item_no_plan'>";
					 $next_column = $total_columns + 1;
					$content  .= "<td colspan='" . esc_attr( $next_column ) . "' class='arm_no_plan'>" . $message_no_record . '</td>';
					$content  .= '</tr>';
				}

				$content     .= '</table>';
					$content .= '</div>';
				$content     .= '</div>';
				$content     .= "<input type='hidden' id='setup_id' name='setup_id' value='" . esc_attr($setup_id) . "'/>";
				$content     .= "<input type='hidden' id='loader_img' name='loader_img' value='" . esc_attr(MEMBERSHIPLITE_IMAGES_URL) . "/arm_loader.gif'/>";
				$content     .= "<input type='hidden' id='arm_form_style_css' name='arm_form_style_css' value='" . esc_attr(MEMBERSHIPLITE_URL) . "/css/arm_form_style.css'/>";
				$content     .= "<input type='hidden' id='angular_js' name='angular_js' value='" . esc_attr(MEMBERSHIPLITE_URL) . "/materialize/arm_materialize.js'/>";
				$content     .= "<input type='hidden' id='arm_font_awsome' name='arm_font_awsome' value='" . esc_attr(MEMBERSHIPLITE_URL) . "/css/arm-font-awesome.css'/>";
				$next_column = $total_columns + 1 ;
				$content     .= "<input type='hidden' id='arm_total_current_membership_columns' name='arm_total_current_membership_columns' value='" . esc_attr( $next_column ) . "'/>";
				$content     .= "<input type='hidden' id='arm_cancel_subscription_message' name='arm_cancel_subscription_message' value='" . esc_attr($cancel_message) . "'/>";
				$arm_wp_nonce = wp_create_nonce( 'arm_wp_nonce' );
				$content     .= '<input type="hidden" name="arm_wp_nonce" value="' . esc_attr($arm_wp_nonce) . '"/>';
				$content .='<input type="hidden" name="arm_wp_nonce_check" value="1">';
				$content     .= '</form></div>';
				$content     .= "<div class='armclear'></div>";
				$content      = apply_filters( 'arm_after_current_membership_shortcode_content', $content, $atts );
			} else {
				$default_login_form_id = $arm_member_forms->arm_get_default_form_id( 'login' );
				return do_shortcode( "[arm_form id='$default_login_form_id' is_referer='1']" );
			}

			$ARMemberLite->set_front_css( true );
			$ARMemberLite->enqueue_angular_script( true );
			wp_enqueue_style( 'arm_form_style_css' );
			return do_shortcode( $content );
		}

		function arm_close_account_form_style( $set_id = '', $formRandomID = 0 ) {
			global $wp, $wpdb, $wp_roles, $current_user, $arm_lite_errors, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings;

			$frontfontstyle      = $arm_global_settings->arm_get_front_font_style();
			$labelFontFamily     = isset( $frontfontstyle['frontOptions']['level_3_font']['font_family'] ) ? $frontfontstyle['frontOptions']['level_3_font']['font_family'] : 'Helvetica';
			$labelFontSize       = isset( $frontfontstyle['frontOptions']['level_3_font']['font_size'] ) ? $frontfontstyle['frontOptions']['level_3_font']['font_size'] : '14';
			$labelFontColor      = ( isset( $frontfontstyle['frontOptions']['level_3_font']['font_color'] ) ) ? $frontfontstyle['frontOptions']['level_3_font']['font_color'] : '';
			$labelFontBold       = ( isset( $frontfontstyle['frontOptions']['level_3_font']['font_bold'] ) && $frontfontstyle['frontOptions']['level_3_font']['font_bold'] == '1' ) ? 1 : 0;
			$labelFontItalic     = ( isset( $frontfontstyle['frontOptions']['level_3_font']['font_italic'] ) && $frontfontstyle['frontOptions']['level_3_font']['font_italic'] == '1' ) ? 1 : 0;
			$labelFontDecoration = ( ! empty( $frontfontstyle['frontOptions']['level_3_font']['font_decoration'] ) ) ? $frontfontstyle['frontOptions']['level_3_font']['font_decoration'] : '';

			$buttonFontFamily     = isset( $frontfontstyle['frontOptions']['button_font']['font_family'] ) ? $frontfontstyle['frontOptions']['button_font']['font_family'] : 'Helvetica';
			$buttonFontSize       = isset( $frontfontstyle['frontOptions']['button_font']['font_size'] ) ? $frontfontstyle['frontOptions']['button_font']['font_size'] : '14';
			$buttonFontColor      = ( isset( $frontfontstyle['frontOptions']['button_font']['font_color'] ) ) ? $frontfontstyle['frontOptions']['button_font']['font_color'] : '';
			$buttonFontBold       = ( isset( $frontfontstyle['frontOptions']['button_font']['font_bold'] ) && $frontfontstyle['frontOptions']['button_font']['font_bold'] == '1' ) ? 1 : 0;
			$buttonFontItalic     = ( isset( $frontfontstyle['frontOptions']['button_font']['font_italic'] ) && $frontfontstyle['frontOptions']['button_font']['font_italic'] == '1' ) ? 1 : 0;
			$buttonFontDecoration = ( ! empty( $frontfontstyle['frontOptions']['button_font']['font_decoration'] ) ) ? $frontfontstyle['frontOptions']['button_font']['font_decoration'] : '';

			$form_settings = array();
			if ( isset( $set_id ) && $set_id != '' ) {
				$setform_settings = $wpdb->get_row( $wpdb->prepare("SELECT `arm_form_id`, `arm_form_type`, `arm_form_settings` FROM `".$ARMemberLite->tbl_arm_forms."` WHERE `arm_form_id` = %d AND `arm_form_type`=%s ORDER BY arm_form_id DESC LIMIT 1",$set_id,'login' ));//phpcs:ignore --Reason $ARMemberLite->tbl_arm_forms is a table name
				$set_style_option = maybe_unserialize( $setform_settings->arm_form_settings );
				if ( isset( $set_style_option['style'] ) ) {
					$form_settings['style'] = $set_style_option['style'];
				}
				if ( isset( $set_style_option['custom_css'] ) ) {
					$form_settings['custom_css'] = $set_style_option['custom_css'];
				}
				$form_css = $arm_member_forms->arm_ajax_generate_form_styles( 'close_account', $form_settings );
			} else {
				// Get Default style
				$form_settings['style'] = $arm_member_forms->arm_default_form_style_login();
				$form_css               = $arm_member_forms->arm_ajax_generate_form_styles( 'close_account', $form_settings );
			}
			$caFormStyle = '';
			if ( ! empty( $frontfontstyle['google_font_url'] ) ) {
				// $caFormStyle .= '<link id="google-font" rel="stylesheet" type="text/css" href="' . $frontfontstyle['google_font_url'] . '" />';
				$caFormStyle .= wp_enqueue_style( 'google-font', $frontfontstyle['google_font_url'], array(), MEMBERSHIPLITE_VERSION );
			}
			$closeAccountcontainer = '.arm_form_close_account';
			$caFormStyle          .= "<style type='text/css'>
                /*$closeAccountcontainer .arm_close_account_message,
				$closeAccountcontainer .arm-df__form-control,
				$closeAccountcontainer .arm-df__form-field-wrap,
				$closeAccountcontainer .arm-df__form-field-wrap input{
                    {$frontfontstyle['frontOptions']['level_3_font']['font']}
                }
                $closeAccountcontainer .arm_close_account_btn{
                    {$frontfontstyle['frontOptions']['button_font']['font']}
                }*/
                {$form_css['arm_css']}
            </style>";
			return $caFormStyle; //phpcs:ignore
		}

		function arm_close_account_form_action() {
			global $wp, $wpdb, $current_user, $current_site, $arm_lite_errors, $ARMemberLite, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_lite_members_activity, $arm_subscription_plans;
			$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data' ), $_POST ); //phpcs:ignore		
			$ARMemberLite->arm_check_user_cap( '' ,1 ); //phpcs:ignore --Reason:Verifying nonce
			$user = wp_get_current_user();
			if ( isset( $posted_data['arm_action'] ) ) {
				do_action( 'arm_before_close_account_form_action', $posted_data, $user );
				if ( isset( $_POST['pass'] ) ) { //phpcs:ignore
					if ( $user && wp_check_password( $_POST['pass'], $user->data->user_pass, $user->ID ) ) { //phpcs:ignore
						arm_set_member_status( $user->ID, 2, 1 );
						$plan_ids             = get_user_meta( $user->ID, 'arm_user_plan_ids', true );
						$stop_future_plan_ids = get_user_meta( $user->ID, 'arm_user_future_plan_ids', true );
						$defaultPlanData      = $arm_subscription_plans->arm_default_plan_array();

						if ( ! empty( $stop_future_plan_ids ) && is_array( $stop_future_plan_ids ) ) {
							foreach ( $stop_future_plan_ids as $stop_future_plan_id ) {
								$arm_subscription_plans->arm_add_membership_history( $user->ID, $stop_future_plan_id, 'cancel_subscription', array(), 'terminate' );
								delete_user_meta( $user->ID, 'arm_user_plan_' . $stop_future_plan_id );
							}
							delete_user_meta( $user->ID, 'arm_user_future_plan_ids' );
						}

						if ( ! empty( $plan_ids ) && is_array( $plan_ids ) ) {

							foreach ( $plan_ids as $plan_id ) {
								$planData                       = get_user_meta( $user->ID, 'arm_user_plan_' . $plan_id, true );
								$userPlanDatameta               = ! empty( $planData ) ? $planData : array();
								$planData                       = shortcode_atts( $defaultPlanData, $userPlanDatameta );
								$plan_detail                    = $planData['arm_current_plan_detail'];
								$planData['arm_cencelled_plan'] = 'yes';
								update_user_meta( $user->ID, 'arm_user_plan_' . $plan_id, $planData );
								if ( ! empty( $plan_detail ) ) {
									$planObj = new ARM_Plan_Lite( 0 );
									$planObj->init( (object) $plan_detail );
								} else {
									$planObj = new ARM_Plan_Lite( $plan_id );
								}
								if ( $planObj->exists() && $planObj->is_recurring() ) {
									do_action( 'arm_cancel_subscription_gateway_action', $user->ID, $planObj->ID );
								}
								$arm_subscription_plans->arm_add_membership_history( $user->ID, $planObj->ID, 'cancel_subscription', array(), 'close_account' );
								do_action( 'arm_cancel_subscription', $user->ID, $planObj->ID );
								$arm_subscription_plans->arm_clear_user_plan_detail( $user->ID, $planObj->ID );
							}
						}
						do_action( 'arm_after_close_account', $user->ID, $user );
						wp_cache_delete( $user->ID, 'users' );
						wp_cache_delete( $user->user_login, 'userlogins' );

						$res_var = wp_delete_user( $user->ID, 1 );

						wp_logout();
						$home_url = ARMLITE_HOME_URL;
						$response = array(
							'type' => 'success',
							'msg'  => esc_html__( 'Your account is closed successfully.', 'armember-membership' ),
							'url'  => $home_url,
						);
					} else {
						$err_msg    = $arm_global_settings->common_message['arm_invalid_password_close_account'];
						$all_errors = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'Your current password is invalid.', 'armember-membership' );
						$response   = array(
							'type' => 'error',
							'msg'  => esc_html( $all_errors ),
						);
					}
				}
				do_action( 'arm_after_close_account_form_action', $posted_data, $user );
			}
			echo wp_json_encode( $response );
			die();
		}

		/**
		 * Add Shortcode Button in TinyMCE Editor.
		 */
		function arm_insert_shortcode_button( $content ) {

			$server_php_self = !empty( $_SERVER['PHP_SELF'] ) ? sanitize_text_field( $_SERVER['PHP_SELF'] ) : ''; //phpcs:ignore
			/*
			 if (!in_array(basename( $server_php_self ), array('post.php', 'page.php', 'post-new.php', 'page-new.php'))) {
			  return;
			  } */
			if ( ! in_array( basename( $server_php_self ), array( 'post.php', 'post-new.php' ) ) ) {
				return;
			}
			if ( basename( $server_php_self ) == 'post.php' ) {
				$post_id   = isset($_REQUEST['post']) ? intval($_REQUEST['post']) : 0; //phpcs:ignore
				$post_type = get_post_type( $post_id );
			}
			if ( basename( $server_php_self ) == 'post-new.php' ) {
				if ( isset( $_REQUEST['post_type'] ) ) { //phpcs:ignore
					$post_type = isset($_REQUEST['post_type']) ? sanitize_text_field($_REQUEST['post_type']) : 'post'; //phpcs:ignore
				} else {
					$post_type = 'post';
				}
			}
			if ( ! in_array( $post_type, array( 'post', 'page' ) ) ) {
				return;
			}
			if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'elementor' ) { //phpcs:ignore
				if ( ! wp_script_is( 'jquery', 'enqueued' ) ) {
					wp_enqueue_script( 'jquery' );
				}
				if ( ! wp_script_is( 'arm_tinymce', 'enqueued' ) ) {
					wp_enqueue_script( 'arm_tinymce', MEMBERSHIPLITE_URL . '/js/arm_tinymce_member.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION,false );
				}
				if ( ! wp_script_is( 'arm_bpopup', 'enqueued' ) ) {
					wp_enqueue_script( 'arm_bpopup', MEMBERSHIPLITE_URL . '/js/jquery.bpopup.min.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION,false );
				}
				if ( ! wp_script_is( 'arm_t_chosen_jq_min', 'enqueued' ) ) {
					wp_enqueue_script( 'arm_t_chosen_jq_min', MEMBERSHIPLITE_URL . '/js/chosen.jquery.min.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION,false );
				}
				if ( ! wp_script_is( 'arm_colpick-js', 'enqueued' ) ) {
					wp_enqueue_script( 'arm_colpick-js', MEMBERSHIPLITE_URL . '/js/colpick.min.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION,false );
				}
				if ( ! wp_script_is( 'arm_icheck-js', 'enqueued' ) ) {
					wp_enqueue_script( 'arm_icheck-js', MEMBERSHIPLITE_URL . '/js/icheck.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION,false );
				}
				if ( ! wp_style_is( 'arm_tinymce', 'enqueued' ) ) {
					wp_enqueue_style( 'arm_tinymce', MEMBERSHIPLITE_URL . '/css/arm_tinymce.css', array(), MEMBERSHIPLITE_VERSION );
				}
				if ( ! wp_style_is( 'arm_chosen_selectbox', 'enqueued' ) ) {
					wp_enqueue_style( 'arm_chosen_selectbox', MEMBERSHIPLITE_URL . '/css/chosen.css', array(), MEMBERSHIPLITE_VERSION );
				}
				if ( ! wp_style_is( 'arm_colpick-css', 'enqueued' ) ) {
					wp_enqueue_style( 'arm_colpick-css', MEMBERSHIPLITE_URL . '/css/colpick.css', array(), MEMBERSHIPLITE_VERSION );
				}
				if ( ! wp_style_is( 'arm-font-awesome', 'enqueued' ) ) {
					wp_enqueue_style( 'arm-font-awesome', MEMBERSHIPLITE_URL . '/css/arm-font-awesome.css', array(), MEMBERSHIPLITE_VERSION );
				}

				$internal_style_for_elementor = "
                    .arm_shortcode_options_popup_wrapper .arm_shortcode_options_container .arm_selectbox dt {
                        box-sizing: content-box;
                    }
                    .arm_shortcode_options_popup_wrapper.arm_normal_wrapper input:not([type='button']), .arm_shortcode_options_popup_wrapper input:not([type='button']), .arm_shortcode_options_popup_wrapper.arm_normal_wrapper select, .arm_shortcode_options_popup_wrapper select{
                        box-sizing: content-box;
                        width: 280px;
                    }
                    .arm_member_transaction_fields .arm_member_transaction_field_list input[type='text'],
                    .arm_member_current_membership_fields .arm_member_current_membership_field_list input[type='text'] {
                        box-sizing: border-box;
                    }
                    .arm_shortcode_popup_btn_wrapper {
                        margin: 0 0 5px 0;
                    }
                ";
				wp_add_inline_style( 'arm_tinymce', $internal_style_for_elementor );
				add_action( 'wp_footer', array( $this, 'arm_insert_shortcode_popup' ) );
			}
			?>
			<div class="arm_shortcode_popup_btn_wrapper">
				<span class="arm_logo_btn"></span>
				<span class="arm_spacer"></span>
				<a class="arm_shortcode_popup_link arm_form_shortcode_popup_link" onclick="arm_open_form_shortcode_popup();" href="javascript:void(0)"><?php esc_html_e( 'MEMBERSHIP SHORTCODES', 'armember-membership' ); ?></a>
				<span class="arm_spacer"></span>
				<a class="arm_shortcode_popup_link arm_restriction_shortcode_popup_link" onclick="arm_open_restriction_shortcode_popup();" href="javascript:void(0)"><?php esc_html_e( 'RESTRICT CONTENT', 'armember-membership' ); ?></a>
			</div>
			<?php
		}

		/**
		 * TinyMCE Editor Popup Window Content
		 */
		function arm_insert_shortcode_popup() {
			$_SERVER['PHP_SELF'] = !empty( $_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : ''; //phpcs:ignore
			if ( ! in_array( basename( $_SERVER['PHP_SELF'] ), array( 'post.php', 'page.php', 'post-new.php', 'page-new.php' ) ) ) {
				return;
			}
			if ( file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_tinymce_options_shortcodes.php' ) ) {
				require MEMBERSHIPLITE_VIEWS_DIR . '/arm_tinymce_options_shortcodes.php';
			}
		}

		/**
		 * Add Button in TinyMCE Editor.
		 */
		function arm_add_tinymce_styles() {
			$_SERVER['PHP_SELF'] = !empty( $_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : ''; //phpcs:ignore
			if ( ! in_array( basename( $_SERVER['PHP_SELF'] ), array( 'post.php', 'page.php', 'post-new.php', 'page-new.php' ) ) ) {
				return;
			}
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'arm_bpopup', MEMBERSHIPLITE_URL . '/js/jquery.bpopup.min.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION,false );
			wp_enqueue_script( 'arm_icheck-js', MEMBERSHIPLITE_URL . '/js/icheck.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION,false );
			wp_enqueue_script( 'arm_tinymce', MEMBERSHIPLITE_URL . '/js/arm_tinymce_member.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION,false );
			wp_enqueue_script( 'arm_colpick-js', MEMBERSHIPLITE_URL . '/js/colpick.min.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION,false );
			wp_enqueue_script( 'arm_t_chosen_jq_min', MEMBERSHIPLITE_URL . '/js/chosen.jquery.min.js', array( 'jquery' ), MEMBERSHIPLITE_VERSION,false );

			wp_enqueue_style( 'arm-font-awesome', MEMBERSHIPLITE_URL . '/css/arm-font-awesome.css', array(), MEMBERSHIPLITE_VERSION );
			wp_enqueue_style( 'arm_tinymce', MEMBERSHIPLITE_URL . '/css/arm_tinymce.css', array(), MEMBERSHIPLITE_VERSION );
			wp_enqueue_style( 'arm_colpick-css', MEMBERSHIPLITE_URL . '/css/colpick.css', array(), MEMBERSHIPLITE_VERSION );
			wp_enqueue_style( 'arm_chosen_selectbox', MEMBERSHIPLITE_URL . '/css/chosen.css', array(), MEMBERSHIPLITE_VERSION );
		}

		function arm_editor_mce_buttons( $buttons ) {
			global $wp, $wpdb, $ARMemberLite, $pagenow, $arm_slugs;
			if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], (array) $arm_slugs ) ) { //phpcs:ignore
				$buttons   = ( ! empty( $buttons ) ) ? $buttons : array();
				$boldKey   = array_search( 'bold', $buttons );
				$italicKey = array_search( 'italic', $buttons );
				unset( $buttons[ $boldKey ] );
				unset( $buttons[ $italicKey ] );
				$armMceButtons = array(
					'fontselect',
					'fontsizeselect',
					'forecolor',
					'bold',
					'italic',
					'underline',
				);
				$buttons       = array_merge( $armMceButtons, $buttons );
			}
			return $buttons;
		}

		function arm_editor_mce_buttons_2( $buttons ) {
			global $wp, $wpdb, $ARMemberLite, $pagenow, $arm_slugs;
			if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], (array) $arm_slugs ) ) { //phpcs:ignore
				$forecolorKey = array_search( 'forecolor', $buttons );
				$underlineKey = array_search( 'underline', $buttons );
				unset( $buttons[ $forecolorKey ] );
				unset( $buttons[ $underlineKey ] );
			}
			return $buttons;
		}

		function arm_editor_font_sizes( $initArray ) {
			global $wp, $wpdb, $ARMemberLite, $pagenow, $arm_slugs, $arm_member_forms;
			if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], (array) $arm_slugs ) ) { //phpcs:ignore
				$armFontFamily = $armFontSizes = '';
				for ( $i = 8; $i <= 40; $i++ ) {
					$armFontSizes .= "{$i}px ";
				}
				$initArray['fontsize_formats'] = trim( $armFontSizes, ' ' );
				/**
				 * Font-Family List
				 */
				$allFonts = array( 'Arial', 'Helvetica', 'sans-serif', 'Lucida Grande', 'Lucida Sans Unicode', 'Tahoma', 'Times New Roman', 'Courier New', 'Verdana', 'Geneva', 'Courier', 'Monospace', 'Times', 'Open Sans Semibold', 'Open Sans Bold' );
				/*
				 $g_fonts = $arm_member_forms->arm_google_fonts_list();
				  $allFonts = array_merge($allFonts, $g_fonts); */
				foreach ( $allFonts as $font ) {
					$armFontFamily .= $font . '=' . $font . ';';
				}
				$initArray['font_formats'] = trim( $armFontFamily, ' ' );
			}
			return $initArray;
		}



		function arm_username_func() {
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}
			$return_content = '';
			if ( is_user_logged_in() ) {
				$user_id        = get_current_user_id();
				$user_data      = wp_get_current_user( $user_id );
				$return_content = $user_data->data->user_login;
			}

			return $return_content;
		}

		function arm_userid_func() {
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}
			$return_content = '';
			if ( is_user_logged_in() ) {
				$user_id        = get_current_user_id();
				$return_content = $user_id;
			}

			return $return_content;
		}
		function arm_displayname_func() {
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}

			$return_content = '';

			if ( is_user_logged_in() ) {
				$user_id        = get_current_user_id();
				$user_data      = wp_get_current_user( $user_id );
				$return_content = $user_data->data->display_name;
			}
			return $return_content;
		}

		function arm_avatar_func() {
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}

			$avatar = '';

			if ( is_user_logged_in() ) {
				$user_id    = get_current_user_id();
				$user_data  = wp_get_current_user( $user_id );
				$user_email = $user_data->data->user_email;

				$avatar = get_avatar( $user_email );
			}
			return $avatar;
		}

		function arm_firstname_lastname_func() {
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}

			$return_content = '';
			if ( is_user_logged_in() ) {
				$user_id        = get_current_user_id();
				$return_content = get_user_meta( $user_id, 'first_name', true ) . ' ' . get_user_meta( $user_id, 'last_name', true );
			}
			return $return_content;
		}
		function arm_user_plan_func() {
			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}
			$user_current_plan     = '';
			$user_current_plan_arr = array();
			if ( is_user_logged_in() ) {
				$user_id       = get_current_user_id();
				$all_plans_ids = get_user_meta( $user_id, 'arm_user_plan_ids', true );
				if ( ! empty( $all_plans_ids ) ) {
					foreach ( $all_plans_ids as $single_plans_id ) {
						$single_plan_details = get_user_meta( $user_id, 'arm_user_plan_' . $single_plans_id, true );
						if ( ! empty( $single_plan_details ) && isset( $single_plan_details['arm_current_plan_detail']['arm_subscription_plan_name'] ) && $single_plan_details['arm_current_plan_detail']['arm_subscription_plan_name'] != '' ) {
							$plan_name               = $single_plan_details['arm_current_plan_detail']['arm_subscription_plan_name'];
							$user_current_plan_arr[] = "<span class='arm_plan_" . strtolower( str_replace( ' ', '_', $plan_name ) ) . "' >" . $plan_name . '</span>';
						}
					}
				}
			}
			if ( ! empty( $user_current_plan_arr ) ) {
				$user_current_plan = implode( "<span class='arm_plan_divider'>, </span>", $user_current_plan_arr );
			}
			return $user_current_plan;
		}

		function arm_usermeta_func( $atts, $content, $tag ) {
			global $ARMemberLite, $arm_member_forms;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}
			global $ARMemberLite, $arm_global_settings;
			$return_content = '';

			if ( isset( $atts['id'] ) && $atts['id'] != '' && $atts['id'] > 0 ) {
				$user_id = intval( $atts['id'] );
			} elseif ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
			}

			if ( isset( $atts['meta'] ) && $atts['meta'] != '' ) {
				$user_object = get_user_by( 'ID', $user_id );
				$meta_name   = $atts['meta'];
				switch ( $meta_name ) {
					case 'user_login':
					case 'user_email':
					case 'display_name':
					case 'user_nicename':
					case 'user_url':
						if ( 'user_url' == $meta_name ) {
							if( !empty( $user_object->data->$meta_name ) ) {
								$return_content = "<a class='arm_user_url' href='" . esc_url($user_object->data->$meta_name) . "' target='_blank'>" . esc_html($user_object->data->$meta_name) . '</a>';
							}
						} else {
							$return_content = $user_object->data->$meta_name;
						}
						break;
					case 'avatar':
						$return_content = get_avatar( $user_object->user_email );
						break;
					default:
						$return_content    = get_user_meta( $user_id, $meta_name, true );
						$arm_filed_options = $arm_member_forms->arm_get_field_option_by_meta( $meta_name );

						$arm_field_type = ( isset( $arm_filed_options['type'] ) && ! empty( $arm_filed_options['type'] ) ) ? $arm_filed_options['type'] : '';
						if ( $arm_field_type == 'file' ) {
							if ( $return_content != '' ) {
								$exp_val        = explode( '/', $return_content );
								$filename       = $exp_val[ count( $exp_val ) - 1 ];
								$file_extension = explode( '.', $filename );
								$file_ext       = $file_extension[ count( $file_extension ) - 1 ];
								if ( in_array( $file_ext, array( 'jpg', 'jpeg', 'jpe', 'png', 'bmp', 'tif', 'tiff', 'JPG', 'JPEG', 'JPE', 'PNG', 'BMP', 'TIF', 'TIFF' ) ) ) {
									$fileUrl = $return_content;
								} else {
									$fileUrl = MEMBERSHIPLITE_IMAGES_URL . '/file_icon.svg';
								}
								if ( preg_match( '@^http@', $return_content ) ) {
									$temp_data      = explode( '://', $return_content );
									$return_content = '//' . $temp_data[1];
								}
								if ( file_exists( strstr( $fileUrl, '//' ) ) ) {
									$fileUrl = strstr( $fileUrl, '//' );
								}
								$return_content = '<div class="arm_old_uploaded_file"><a href="' . esc_url($return_content) . '" target="__blank"><img alt="" src="' . esc_url( $fileUrl ) . '" width="100px"/></a></div>'; //phpcs:ignore 
							}
						} elseif ( $arm_field_type == 'select' || $arm_field_type == 'radio' || ( $arm_field_type == 'checkbox' && ! is_array( $return_content ) ) ) {
							if ( ! empty( $return_content ) ) {
								$arm_tmp_select_val = ! empty( $arm_filed_options['options'] ) ? $arm_filed_options['options'] : '';
								foreach ( $arm_tmp_select_val as $arm_tmp_select_key => $arm_tmp_val ) {
									$arm_tmp_select_val_arr      = explode( ':', $arm_tmp_val );
									$arm_tmp_selected_option_val = end( $arm_tmp_select_val_arr );
									if ( $arm_tmp_selected_option_val == $return_content ) {
										$return_content = str_replace( ':' . $arm_tmp_selected_option_val, '', $arm_tmp_val );
										break;
									}
								}
							}
						} else {
							$return_content = is_string( $return_content ) ? nl2br( $return_content ) : $return_content;
						}
						break;
				}
				if ( is_array( $return_content ) ) {
					$return_content = $ARMemberLite->arm_array_trim( $return_content );
					$return_content = implode( ', ', $return_content );
				}
			}
			$return_content = stripslashes_deep( $return_content );
			return $return_content;
		}



		function arm_user_planinfo_func( $atts, $content, $tag ) {
			if ( current_user_can( 'administrator' ) ) {
				return;
			}

			global $ARMemberLite;
			$arm_check_is_gutenberg_page = $ARMemberLite->arm_check_is_gutenberg_page();
			if ( $arm_check_is_gutenberg_page ) {
				return;
			}
			global $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways;

			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
				if ( isset( $atts['plan_id'] ) && ! empty( $atts['plan_id'] ) ) {
					$plan_id = $atts['plan_id'];

					$user_plan_ids = get_user_meta( $user_id, 'arm_user_plan_ids', true );
					$user_plan_ids = ! empty( $user_plan_ids ) ? $user_plan_ids : array();
					$date_format   = $arm_global_settings->arm_get_wp_date_format();
					if ( in_array( $plan_id, $user_plan_ids ) ) {

						if ( isset( $atts['plan_info'] ) && ! empty( $atts['plan_info'] ) ) {
							$plan_info        = trim( $atts['plan_info'] );
							$defaultPlanData  = $arm_subscription_plans->arm_default_plan_array();
							$planData         = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
							$userPlanDatameta = ! empty( $planData ) ? $planData : array();
							$planData         = shortcode_atts( $defaultPlanData, $userPlanDatameta );

							switch ( $plan_info ) {
								case 'arm_start_plan':
									if ( ! empty( $planData['arm_start_plan'] ) ) {

										$content .= date_i18n( $date_format, $planData['arm_start_plan'] );
									}
									break;
								case 'arm_expire_plan':
									if ( ! empty( $planData['arm_expire_plan'] ) ) {
										$content .= date_i18n( $date_format, $planData['arm_expire_plan'] );
									}
									break;
								case 'arm_trial_start':
									if ( ! empty( $planData['arm_trial_start'] ) ) {
										$content .= date_i18n( $date_format, $planData['arm_trial_start'] );
									}
									break;
								case 'arm_trial_end':
									if ( ! empty( $planData['arm_trial_end'] ) ) {
										$content .= date_i18n( $date_format, $planData['arm_trial_end'] );
									}
									break;
								case 'arm_grace_period_end':
									if ( ! empty( $planData['arm_grace_period_end'] ) ) {
										$content .= date_i18n( $date_format, $planData['arm_grace_period_end'] );
									}
									break;
								case 'arm_user_gateway':
									if ( ! empty( $planData['arm_user_gateway'] ) ) {
										$content .= $arm_payment_gateways->arm_gateway_name_by_key( $planData['arm_user_gateway'] );
									}
									break;
								case 'arm_completed_recurring':
										$content .= $planData['arm_completed_recurring'];
									break;
								case 'arm_next_due_payment':
									if ( ! empty( $planData['arm_next_due_payment'] ) ) {
										$content .= date_i18n( $date_format, $planData['arm_next_due_payment'] );
									}
									break;
								case 'arm_payment_mode':
									if ( ! empty( $planData['arm_payment_mode'] ) ) {
										if ( $planData['arm_payment_mode'] == 'auto_debit_subscription' ) {
											$content .= esc_html__( 'Automatic Subscription', 'armember-membership' );
										} elseif ( $planData['arm_payment_mode'] == 'manual_subscription' ) {
											$content .= esc_html__( 'Semi Automatic Subscription', 'armember-membership' );
										}
									}
									break;
								case 'arm_payment_cycle':
									if ( $planData['arm_payment_cycle'] != '' ) {
										$user_selected_payment_cycle = $planData['arm_payment_cycle'];
										$plan_detail                 = $planData['arm_current_plan_detail'];
										$plan_options                = maybe_unserialize( $plan_detail['arm_subscription_plan_options'] );
										$payment_cycle_data          = $plan_options['payment_cycles'];

										if ( ! empty( $payment_cycle_data ) ) {
											if ( isset( $payment_cycle_data[ $user_selected_payment_cycle ] ) && ! empty( $payment_cycle_data[ $user_selected_payment_cycle ] ) ) {
												$content .= $payment_cycle_data[ $user_selected_payment_cycle ]['cycle_label'];
											}
										}
									}
									break;
								case 'default':
									break;
							}
						}
					}
				}
			}
			return $content;
		}




		function arm_br2nl( $arm_string ) {
			return preg_replace( '/\<br(\s*)?\/?\>/i', "\n", $arm_string );
		}
		
		// Escape ALL shortcodes so they show literally instead of executing
		function arm_com_escape_all_shortcodes($content) {
            
			// Replace [ and ] with ARM HTML entities
			if(!empty($content)){
				$content= str_replace(['[', ']'], ['&#ARM91;', '&#ARM93;'], $content);
			}    

			return $content;
		}

		// Escaped ALL shortcodes return to [ and ]
		function arm_com_descaped_all_shortcodes($content) {
            
			// Replace [ and ] with HTML entities
			if(!empty($content)){
				$content= str_replace(['&#ARM91;', '&#ARM93;'], ['&#91;', '&#93;'], $content);
			}

			return $content;
		}

	}

}
global $arm_shortcodes;
$arm_shortcodes = new ARM_shortcodes_Lite();
