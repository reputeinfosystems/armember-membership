<?php
if ( ! class_exists( 'ARMwidgetForm' ) ) {

	class ARMwidgetForm extends WP_Widget {

		function __construct() {
			parent::__construct(
				'arm_member_form_widget',
				esc_html__( 'ARMember Forms', 'armember-membership' ),
				array( 'description' => esc_html__( 'Display Member Form', 'armember-membership' ) )
			);
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
		}

		public function widget( $args, $instance ) {
			global $arm_member_forms, $ARMemberLite, $is_globalcss_added,$wpdb;
			echo $args['before_widget']; //phpcs:ignore
			if($ARMemberLite->is_arm_pro_active)
			{
				$arm_global_css_section = '';
				echo apply_filters('arm_widgets_global_css_section',$arm_global_css_section); //phpcs:ignore
			}
			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', sanitize_text_field( $instance['title'] ) ) . $args['after_title']; //phpcs:ignore
			}
			$form_id               = isset( $instance['form_id'] ) ? intval( $instance['form_id'] ) : 0;
			$arm_logged_in_message = isset( $instance['logged_in_message'] ) ? sanitize_text_field( $instance['logged_in_message'] ) : esc_html__( 'You are already logged in.', 'armember-membership' );
			if ( ! empty( $form_id ) && $form_id != 0 ) {
				$form_type     = $wpdb->get_results( $wpdb->prepare('SELECT `arm_form_type` FROM `' . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_form_id` = %d",$form_id) );//phpcs:ignore --Reason $tbl_arm_forms is a table name. False Positive Alarm
				$form_type     = $form_type[0]->arm_form_type;
				$logged_in_msg = '';
				if ( $form_type != 'change_password' ) {
					$logged_in_msg = 'logged_in_message="' . $arm_logged_in_message . '"';
				}
				echo do_shortcode( '[arm_form id="' . $form_id . '" widget="true" ' . $logged_in_msg . ']' );
			} else {
				esc_html_e( 'There is no any form found.', 'armember-membership' );
			}
			echo $args['after_widget']; //phpcs:ignore
		}

		public function form( $instance ) {
			global $wp, $wpdb, $ARMemberLite, $arm_member_forms;
			$title         = ! empty( $instance['title'] ) ? sanitize_text_field( $instance['title'] ) : '';
			$form_id       = ! empty( $instance['form_id'] ) ? intval( $instance['form_id'] ) : 0;
			$logged_in_msg = isset( $instance['logged_in_message'] ) ? sanitize_text_field( $instance['logged_in_message'] ) : esc_html__( 'You are already logged in', 'armember-membership' );
			$arm_where = 'AND `arm_form_id` in (101,102,103,104) ';
			if($ARMemberLite->is_arm_pro_active)
			{
				$arm_where = '';
			}
            $arm_forms = $wpdb->get_results( $wpdb->prepare("SELECT `arm_form_id`, `arm_form_label`, `arm_form_type` FROM `" . $ARMemberLite->tbl_arm_forms . "` WHERE `arm_form_type` NOT LIKE %s ".$arm_where." ORDER BY `arm_form_id` DESC",'template'), ARRAY_A); //phpcs:ignore --Reason $ARMember->tbl_arm_forms is a table name
			$form_type = '';
			if ( $form_id > 0 ) {
				$arm_selected_form_type = $wpdb->get_results( $wpdb->prepare('SELECT `arm_form_type` FROM `' . $ARMemberLite->tbl_arm_forms . '` WHERE `arm_form_id` = %d' , $form_id) );//phpcs:ignore --Reason: $tbl_arm_forms is a table name. False Positive Alarm
				$form_type              = $arm_selected_form_type[0]->arm_form_type;
			}
			?>
			<script type="text/javascript">
				function arm_update_widget_form_type(object){
					var $this = jQuery(object);
					var value = $this.val();
					if( value == '' ){
						return false;
					}
					var form_type = $this.find('option[value='+value+']').attr('data-type');
					if( form_type == 'change_password' ){
						jQuery('p#arm_logged_in_message').hide();
					} else {
						jQuery('p#arm_logged_in_message').show();
					}
				}
			</script>
			<p>
				<label for="<?php echo esc_attr($this->get_field_id( 'title' )); //phpcs:ignore ?>"><?php esc_html_e( 'Title', 'armember-membership' ); ?>: </label>
				<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); //phpcs:ignore ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); //phpcs:ignore ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'form_id' ) ); //phpcs:ignore ?>"><?php esc_html_e( 'Select Member Form', 'armember-membership' ); ?>:</label>
			<?php if ( ! empty( $arm_forms ) ) : ?>
					<select name="<?php echo esc_attr( $this->get_field_name( 'form_id' ) ); //phpcs:ignore ?>" id="" class="" style="width:100%;" onChange="arm_update_widget_form_type(this);">
						<option value=""><?php esc_html_e( 'Select Form', 'armember-membership' ); ?></option>
					<?php
					foreach ( $arm_forms as $form ) {
						?>
							<option value="<?php echo intval($form['arm_form_id']); ?>" <?php selected( $form_id, $form['arm_form_id'] ); ?> data-type="<?php echo esc_attr($form['arm_form_type']); ?>"><?php echo strip_tags( stripslashes( $form['arm_form_label'] ) ) . ' &nbsp;(ID: ' . $form['arm_form_id'] . ')'; //phpcs:ignore ?></option>
							<?php
					}
					?>
					</select>
					<?php endif; ?>
			</p>
			<p id="arm_logged_in_message" style="<?php echo ( $form_type == 'change_password' ) ? 'display:none;' : ''; ?>">
				<label for="<?php echo esc_attr($this->get_field_id( 'logged_in_message' )); //phpcs:ignore ?>"><?php esc_html_e( 'Logged in Message', 'armember-membership' ); ?></label>
				<input type="text" name="<?php echo esc_attr($this->get_field_name( 'logged_in_message' )); //phpcs:ignore ?>" id="" class="" style="width:100%;" value="<?php echo esc_attr($logged_in_msg); //phpcs:ignore ?>" />
			</p>
			<?php
		}

		public function update( $new_instance, $old_instance ) {
			$instance                      = array();
			$instance['title']             = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( sanitize_text_field( $new_instance['title'] ) ) : '';
			$instance['form_id']           = ! empty( $new_instance['form_id'] ) ? intval( $new_instance['form_id'] ) : 0;
			$instance['logged_in_message'] = isset( $new_instance['logged_in_message'] ) ? sanitize_text_field( $new_instance['logged_in_message'] ) : esc_html__( 'You are already logged in.', 'armember-membership' );
			return $instance;
		}

		function scripts() {
			global $wp, $wpdb, $ARMemberLite, $arm_lite_ajaxurl, $arm_slugs;
			if ( is_active_widget( false, false, $this->id_base, true ) ) {
				$ARMemberLite->set_front_css( true );
				$ARMemberLite->set_front_js( true );
			}
		}

	}

	if ( class_exists( 'WP_Widget' ) ) {

		function arm_register_forms_widgets() {
			register_widget( 'ARMwidgetForm' );
		}

		add_action( 'widgets_init', 'arm_register_forms_widgets' );
	}
}
