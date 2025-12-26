<?php

if ( ! class_exists( 'ARMLoginWidget' ) ) {
	class ARMLoginWidget extends WP_Widget {

		function __construct() {
			parent::__construct(
				'arm_member_form_login_widget',
				esc_html__( 'ARMember Login Widget', 'armember-membership' ),
				array( 'description' => esc_html__( 'Display currently logged in Member profile', 'armember-membership' ) )
			);
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
		}

		public function widget( $args, $instance ) {
			global $wp, $wpdb, $current_user, $arm_lite_errors, $ARMemberLite, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_lite_members_activity,$is_globalcss_added,$arm_social_feature,$arm_members_directory;
			if ( ! is_user_logged_in() ) {
				return;
			}
			$user_id = get_current_user_id();
			if ( $user_id == '' || empty( $user_id ) || current_user_can( 'administrator' ) ) {
				return;
			}
			echo $args['before_widget']; //phpcs:ignore
			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', sanitize_text_field( $instance['title'] ) ) . $args['after_title']; //phpcs:ignore
			}
			$label1 = isset( $instance['custom_meta_1'] ) ?  sanitize_text_field( $instance['custom_meta_1'] ) : '';
			$value1 = isset( $instance['custom_meta_value_1'] ) ? sanitize_text_field( $instance['custom_meta_value_1'] ) : '';
			$value1 = $this->arm_login_widget_user_meta_value( $value1, $user_id );

			$label2 = isset( $instance['custom_meta_2'] ) ? sanitize_text_field( $instance['custom_meta_2'])  : '';
			$value2 = isset( $instance['custom_meta_value_2'] ) ? sanitize_text_field( $instance['custom_meta_value_2'] ) : '';
			$value2 = $this->arm_login_widget_user_meta_value( $value2, $user_id );

			$label3 = isset( $instance['custom_meta_3'] ) ? sanitize_text_field( $instance['custom_meta_3'] ) : '';
			$value3 = isset( $instance['custom_meta_value_3'] ) ? sanitize_text_field( $instance['custom_meta_value_3'] ) : '';
			$value3 = $this->arm_login_widget_user_meta_value( $value3, $user_id );

			$output               = '';
			$profile_template     = $arm_members_directory->arm_get_template_by_id( 1 );
			$profile_template_opt = $profile_template['arm_options'];
			$default_cover        = $profile_template_opt['default_cover'];
			$profile_cover        = get_user_meta( $user_id, 'profile_cover', true );
			if ( $profile_cover == '' || empty( $profile_cover ) ) {
				$profile_cover = $default_cover;
			}
			$profile_avatar    = get_avatar( $user_id, 95 );
					$rtl_class = '';
			if ( is_rtl() ) {
				$rtl_class = 'arm_rtl_widget';
			}
			$output             .= "<div class='arm_login_widget_wrapper " . esc_attr($rtl_class) . "'>";
				$output         .= "<div class='arm_login_widget_header'>";
					$output     .= "<div class='arm_login_widget_user_cover'>";
						$output .= "<img src='".esc_attr($profile_cover)."' style='width:100%;height:100%;border-radius:0;-webkit-border-radius:0;-o-border-radius:0;-moz-borde-radius:0;' />"; //phpcs:ignore
					$output     .= '</div>';
					$output     .= "<div class='arm_login_widget_avatar'>";
						$output .= $profile_avatar;
					$output     .= '</div>';
				$output         .= '</div>';
				$output         .= "<div class='arm_login_widget_content_wrapper'>";
				$profile_link    = $arm_global_settings->arm_get_user_profile_url( $user_id );
				$output         .= "<a href='".esc_attr($profile_link)."' class='arm_login_widget_profile_link'><span>" . get_user_meta( $user_id, 'first_name', true ) . ' ' . get_user_meta( $user_id, 'last_name', true ) . '</span></a>';
				$output         .= "<div class='arm_login_widget_user_info'>";
			if ( $label1 != '' ) {
				$output     .= "<div class='arm_login_widget_user_info_row'>";
					$output .= "<div class='arm_login_widget_user_info_row_left'>";
					$output .= $label1;
					$output .= '</div>';
					$output .= "<div class='arm_login_widget_user_info_row_right'>";
					$output .= $value1;
					$output .= '</div>';
				$output     .= '</div>';
			}

			if ( $label2 != '' ) {
				$output     .= "<div class='arm_login_widget_user_info_row'>";
					$output .= "<div class='arm_login_widget_user_info_row_left'>";
					$output .= $label2;
					$output .= '</div>';
					$output .= "<div class='arm_login_widget_user_info_row_right'>";
					$output .= $value2;
					$output .= '</div>';
				$output     .= '</div>';
			}

			if ( $label3 != '' ) {
				$output     .= "<div class='arm_login_widget_user_info_row'>";
					$output .= "<div class='arm_login_widget_user_info_row_left'>";
					$output .= $label3;
					$output .= '</div>';
					$output .= "<div class='arm_login_widget_user_info_row_right'>";
					$output .= $value3;
					$output .= '</div>';
				$output     .= '</div>';
			}
				$output .= '</div>';
				$output .= '</div>';
			$output     .= '</div>';
			echo $output; //phpcs:ignore
			echo $args['after_widget']; //phpcs:ignore
		}

		public function form( $instance ) {
			global $arm_member_forms;
			$title         = ! empty( $instance['title'] ) ? sanitize_text_field( $instance['title'] ) : '';
			$custom_meta_1 = ! empty( $instance['custom_meta_1'] ) ? sanitize_text_field( $instance['custom_meta_1'] ) : esc_html__( 'Joined Date', 'armember-membership' );
			$custom_meta_2 = ! empty( $instance['custom_meta_2'] ) ? sanitize_text_field( $instance['custom_meta_2'] ) : '';
			$custom_meta_3 = ! empty( $instance['custom_meta_3'] ) ? sanitize_text_field( $instance['custom_meta_3'] ) : '';
			$user_query    = new WP_User_Query(
				array(
					'fields' => 'all_with_meta',
					'number' => 1,
				)
			);
			?>
			<p style="margin-bottom:0;">
				<label for="<?php echo esc_attr($this->get_field_id( 'title' )); //phpcs:ignore ?>"><?php esc_html_e( 'Title', 'armember-membership' ); ?>: </label>
				<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); //phpcs:ignore ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); //phpcs:ignore ?>" type="text" value="<?php echo esc_attr( $title ); ?>">&nbsp;
			</p>
			<p>
				<label for="<?php echo esc_attr($this->get_field_id( 'custom_meta_1' )); //phpcs:ignore ?>" style="float:left;width:100%;"><?php esc_html_e( 'User Meta 1', 'armember-membership' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'custom_meta_1' )); //phpcs:ignore ?>" name="<?php echo esc_attr($this->get_field_name( 'custom_meta_1' )); //phpcs:ignore ?>" type="text" value="<?php echo esc_attr( $custom_meta_1 ); ?>"  style="float:left;width:120px;position: relative;top:1px;margin-right:5px;" />
				<?php
					$custom_field_1 = ! empty( $instance['custom_meta_value_1'] ) ? sanitize_text_field( $instance['custom_meta_value_1'] ) : 'joined_date';
					$custom_field_2 = ! empty( $instance['custom_meta_value_2'] ) ? sanitize_text_field( $instance['custom_meta_value_2'] ) : '';
					$custom_field_3 = ! empty( $instance['custom_meta_value_3'] ) ? sanitize_text_field( $instance['custom_meta_value_3'] ) : '';
				?>
				<select name='<?php echo $this->get_field_name( 'custom_meta_value_1' ); //phpcs:ignore ?>' style="width:140px;">
					<option value=""><?php esc_html_e( 'Select User Meta', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_1, 'user_name' ); ?> value="user_name"><?php esc_html_e( 'User Name', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_1, 'first_name' ); ?> value="first_name"><?php esc_html_e( 'First Name', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_1, 'last_name' ); ?> value="last_name"><?php esc_html_e( 'Last Name', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_1, 'display_name' ); ?> value="display_name"><?php esc_html_e( 'Display Name', 'armember-membership' ); ?></option>
					<option  <?php selected( $custom_field_1, 'joined_date' ); ?> value="joined_date"><?php esc_html_e( 'Joined Date', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_1, 'email' ); ?> value="email"><?php esc_html_e( 'Email Address', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_1, 'gender' ); ?> value="gender"><?php esc_html_e( 'Gender', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_1, 'url' ); ?> value="url"><?php esc_html_e( 'Website', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_1, 'country' ); ?> value="country"><?php esc_html_e( 'Country/Region', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_1, 'description' ); ?> value="description"><?php esc_html_e( 'Biography', 'armember-membership' ); ?></option>
				</select>
			</p>
			<p>
				<label for="<?php echo esc_attr($this->get_field_id( 'custom_meta_2' )); //phpcs:ignore ?>" style="float:left;width:100%;"><?php esc_html_e( 'User Meta 2', 'armember-membership' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'custom_meta_2' )); //phpcs:ignore ?>" name="<?php echo esc_attr($this->get_field_name( 'custom_meta_2' )); //phpcs:ignore ?>" type="text" value="<?php echo esc_attr( $custom_meta_2 ); ?>" style="float:left;width:120px;position: relative;top:1px;margin-right:5px;" />
				<select name='<?php echo esc_attr($this->get_field_name( 'custom_meta_value_2' )); //phpcs:ignore ?>' style="width:140px;">
					<option value=""><?php esc_html_e( 'Select User Meta', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_2, 'user_name' ); ?> value="user_name"><?php esc_html_e( 'User Name', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_2, 'first_name' ); ?> value="first_name"><?php esc_html_e( 'First Name', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_2, 'last_name' ); ?> value="last_name"><?php esc_html_e( 'Last Name', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_2, 'display_name' ); ?> value="display_name"><?php esc_html_e( 'Display Name', 'armember-membership' ); ?></option>
					<option  <?php selected( $custom_field_2, 'joined_date' ); ?> value="joined_date"><?php esc_html_e( 'Joined Date', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_2, 'email' ); ?> value="email"><?php esc_html_e( 'Email Address', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_2, 'gender' ); ?> value="gender"><?php esc_html_e( 'Gender', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_2, 'url' ); ?> value="url"><?php esc_html_e( 'Website', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_2, 'country' ); ?> value="country"><?php esc_html_e( 'Country/Region', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_2, 'description' ); ?> value="description"><?php esc_html_e( 'Biography', 'armember-membership' ); ?></option>
				</select>
			</p>
			<p>
				<label for="<?php echo esc_attr($this->get_field_id( 'custom_meta_3' )); //phpcs:ignore ?>" style="float:left;width:100%;"><?php esc_html_e( 'User Meta 3', 'armember-membership' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'custom_meta_3' )); //phpcs:ignore ?>" name="<?php echo esc_attr($this->get_field_name( 'custom_meta_3' )); //phpcs:ignore ?>" type="text" value="<?php echo esc_attr( $custom_meta_3 ); ?>" style="float:left;width:120px;position: relative;top:1px;margin-right:5px;" />
				<select name='<?php echo $this->get_field_name( 'custom_meta_value_3' ); //phpcs:ignore ?>' style="width:140px;">
					<option value=""><?php esc_html_e( 'Select User Meta', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_3, 'user_name' ); ?> value="user_name"><?php esc_html_e( 'User Name', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_3, 'first_name' ); ?> value="first_name"><?php esc_html_e( 'First Name', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_3, 'last_name' ); ?> value="last_name"><?php esc_html_e( 'Last Name', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_3, 'display_name' ); ?> value="display_name"><?php esc_html_e( 'Display Name', 'armember-membership' ); ?></option>
					<option  <?php selected( $custom_field_3, 'joined_date' ); ?> value="joined_date"><?php esc_html_e( 'Joined Date', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_3, 'email' ); ?> value="email"><?php esc_html_e( 'Email Address', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_3, 'gender' ); ?> value="gender"><?php esc_html_e( 'Gender', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_3, 'url' ); ?> value="url"><?php esc_html_e( 'Website', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_3, 'country' ); ?> value="country"><?php esc_html_e( 'Country/Region', 'armember-membership' ); ?></option>
					<option <?php selected( $custom_field_3, 'description' ); ?> value="description"><?php esc_html_e( 'Biography', 'armember-membership' ); ?></option>
				</select>
			</p>
			<?php
		}

		public function update( $new_instance, $old_instance ) {
			$instance                        = array();
			$instance['title']               = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( sanitize_text_field( $new_instance['title'] ) ) : '';
			$instance['custom_meta_1']       = ! empty( $new_instance['custom_meta_1'] ) ? sanitize_text_field( $new_instance['custom_meta_1'] ) : esc_html__( 'Joined Date', 'armember-membership' );
			$instance['custom_meta_value_1'] = ! empty( $new_instance['custom_meta_value_1'] ) ? sanitize_text_field( $new_instance['custom_meta_value_1'] ) : 'joined_date';

			$instance['custom_meta_2']       = ! empty( $new_instance['custom_meta_2'] ) ? sanitize_text_field( $new_instance['custom_meta_2'] ) : '';
			$instance['custom_meta_value_2'] = ! empty( $new_instance['custom_meta_value_2'] ) ? sanitize_text_field( $new_instance['custom_meta_value_2'] ) : '';

			$instance['custom_meta_3']       = ! empty( $new_instance['custom_meta_3'] ) ? sanitize_text_field( $new_instance['custom_meta_3'] ) : '';
			$instance['custom_meta_value_3'] = ! empty( $new_instance['custom_meta_value_3'] ) ? sanitize_text_field( $new_instance['custom_meta_value_3'] ) : '';
			return $instance;
		}

		public function scripts() {
			if ( is_active_widget( false, false, $this->id_base, true ) ) {
				wp_enqueue_style( 'arm_lite_front_css', MEMBERSHIPLITE_URL . '/css/arm_front.css', array(), MEMBERSHIPLITE_VERSION );
			}
		}

		public function arm_login_widget_user_meta_value( $value = '', $user_id = '' ) {
			global $arm_global_settings;
			if ( empty( $user_id ) ) {
				return '';
			}
			$user                    = new WP_User( $user_id );
						$date_format = $arm_global_settings->arm_get_wp_date_format();
			switch ( $value ) {
				case 'user_name':
					return $user->data->user_login;
					break;
				case 'first_name':
					return get_user_meta( $user_id, 'first_name', true );
					break;
				case 'last_name':
					return get_user_meta( $user_id, 'last_name', true );
					break;
				case 'display_name':
					return $user->data->display_name;
					break;
				case 'email':
					return $user->data->user_email;
					break;
				case 'gender':
					return get_user_meta( $user_id, 'gender', true );
					break;
				case 'joined_date':
					return date_i18n( $date_format, strtotime( $user->data->user_registered ) );
					break;
				case 'description':
					return get_user_meta( $user_id, 'description', true );
					break;
				case 'url':
					return $user->data->user_url;
					break;
				case 'country':
					return get_user_meta( $user_id, 'country', true );
					break;
				default:
					return date_i18n( $date_format, strtotime( $user->data->user_registered ) );
					break;
			}
		}
	}
	if ( class_exists( 'WP_Widget' ) ) {
		function arm_register_login_widgets() {
			 register_widget( 'ARMLoginWidget' );
		}
		add_action( 'widgets_init', 'arm_register_login_widgets' );
	}
}
