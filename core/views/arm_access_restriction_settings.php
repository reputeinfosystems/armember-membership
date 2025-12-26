<?php
global $wpdb, $ARMemberLite, $arm_global_settings, $arm_access_rules, $arm_subscription_plans, $arm_restriction;
$all_global_settings = $arm_global_settings->arm_get_all_global_settings();

$general_settings = $all_global_settings['general_settings'];
$page_settings    = $all_global_settings['page_settings'];

$all_plans_data    = $arm_subscription_plans->arm_get_all_subscription_plans( 'arm_subscription_plan_id, arm_subscription_plan_name, arm_subscription_plan_type', ARRAY_A, true );
$defaultRulesTypes = $arm_access_rules->arm_get_access_rule_types();
$default_rules     = $arm_access_rules->arm_get_default_access_rules();
$all_roles         = $arm_global_settings->arm_get_all_roles();

?>

<div class="arm_global_settings_main_wrapper armPageContainer">
	<div class="page_sub_content">

		<div class="page_sub_title arm_margin_bottom_32">Default Restriction Rules </div>
		<form method="post" action="#" id="arm_access_restriction" class="arm_access_restriction arm_admin_form" onsubmit="return false;">
						<?php do_action( 'arm_before_access_restriction_settings_html', $general_settings ); ?>

			<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_restriction_settings_section', 'general_restriction') : ''; ?>
			
			<div class="page_sub_title arm_font_size_18 arm_font_weight_500" id="arm_global_default_access_rules">
							<?php esc_html_e( 'Default Access Rules for newly added Content', 'armember-membership' ); ?>
							<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php esc_html_e( 'Please configure default rules to restrict any newly added page, post, category, custom post, etc for which there is no rule defined at Access Rules.', 'armember-membership' ); ?>"></i>
						</div>
						<?php
						$ruleTypes = array(
							'page'     => esc_html__( 'New Pages', 'armember-membership' ),
							'post'     => esc_html__( 'New Posts', 'armember-membership' ),
							'category' => esc_html__( 'New Categories', 'armember-membership' ),

						);
			if ($ARMemberLite->is_arm_pro_active) {
							$ruleTypes['nav_menu'] = esc_html__('New Navigation Menus', 'armember-membership');
						}
			if (!empty($defaultRulesTypes['post_type'])) {
							foreach ( $defaultRulesTypes['post_type'] as $postType => $title ) {
								if ( ! in_array( $postType, $ruleTypes ) ) {
									$ruleTypes[ $postType ] = esc_html__( 'New', 'armember-membership' ) . ' ' . $title;
								}
							}
						}
			if (!empty($defaultRulesTypes['taxonomy'])) {
							foreach ( $defaultRulesTypes['taxonomy'] as $taxonomy => $title ) {
								if ( $taxonomy != 'category' ) {
									$ruleTypes[ $taxonomy ] = esc_html__( 'New', 'armember-membership' ) . ' ' . $title;
								}
							}
						}

			$arm_default_ar_cntr = 0;
			foreach ($ruleTypes as $rtype => $rtitle) :

				if ($arm_default_ar_cntr == 4) :
			?>
					<div class="arm_padding_0 arm_margin_top_24">
								<div class="page_sub_title arm_font_size_18 arm_font_weight_500">
									<?php esc_html_e('Custom Post type, Taxonomy, Tag', 'armember-membership'); ?>
					</div>
			<?php
				endif;

									$arm_default_restriction_option = '';
									if ( empty( $default_rules[ $rtype ] ) ) {
										$arm_default_restriction_option = '';
									} elseif ( is_array( $default_rules[ $rtype ] ) && in_array( '-2', $default_rules[ $rtype ] ) ) {
										$arm_default_restriction_option = '-2';
									} elseif ( ! empty( $default_rules[ $rtype ] ) ) {
										$arm_default_restriction_option = '1';
				}
			?>
				<div class="arm_setting_main_content arm_padding_0 arm_margin_top_24" id="arm_default_restriction_<?php echo esc_attr($rtype); ?>">
					<div class="arm_row_wrapper arm_row_wrapper_padding_before">
						<div class="left_content">
							<div class="arm_form_header_label arm-setting-hadding-label"><?php echo esc_html($rtitle); ?></div>
						</div>
					</div>
					<div class="arm_content_border"></div>

					<div class="arm_row_wrapper arm_row_wrapper_padding_after arm_restriction_rules">
						<label class="arm_min_width_100">
							<input type="radio" name="arm_default_restriction_option[<?php echo esc_attr($rtype); ?>]" value="0" class="arm_default_restriction_option arm_iradio" <?php checked($arm_default_restriction_option, ''); ?> data-cntr="<?php echo esc_attr($arm_default_ar_cntr); ?>" /><span><?php esc_html_e('Everyone', 'armember-membership'); ?></span>
						</label>
						<label class="arm_min_width_150">
							<input type="radio" name="arm_default_restriction_option[<?php echo esc_attr($rtype); ?>]" value="-2" class="arm_default_restriction_option arm_iradio" <?php checked($arm_default_restriction_option, '-2'); ?> data-cntr="<?php echo esc_attr($arm_default_ar_cntr); ?>" /><span><?php esc_html_e('Only logged in member (Everyone)', 'armember-membership'); ?></span>
						</label>
						<label class="arm_min_width_150" style="margin-bottom:0 !important;">
							<input type="radio" name="arm_default_restriction_option[<?php echo esc_attr($rtype); ?>]" value="1" class="arm_default_restriction_option arm_iradio" <?php checked($arm_default_restriction_option, '1'); ?> data-cntr="<?php echo esc_attr($arm_default_ar_cntr); ?>" /><span><?php esc_html_e('Selected Plan(s) Only', 'armember-membership'); ?></span>
						</label>
					</div>

					<?php $visible = ($arm_default_restriction_option === '1') ? '' : 'style="display:none;"'; ?>
					<div class="arm_row_wrapper arm_default_access_restrictions_row arm_default_restriction_option_<?php echo esc_attr($arm_default_ar_cntr); ?> arm_display_block" <?php echo $visible; ?>>
						<div class="left_content arm_padding_left_32 arm_padding_right_32 arm_padding_bottom_32 ">
						<div class="arm_margin_bottom_12">
						<label class="arm-form-table-label">Select Plan</label>
						<?php
							$da_tooltip = esc_html__('Please select plan(s) for members can access', 'armember-membership') . ' ' . $rtitle . ' ' . esc_html__('by default.', 'armember-membership');
						?>
						<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo esc_attr($da_tooltip); ?>"></i>
						</div>
							<select name="arm_default_rules[<?php echo esc_attr($rtype); ?>][]" class="arm_default_rule_select arm_chosen_selectbox " multiple data-placeholder="<?php esc_html_e('Select Plan', 'armember-membership'); ?>" tabindex="-1">
								<?php
								if (!empty($all_plans_data)) {
									$default_rules[$rtype] = !empty($default_rules[$rtype]) ? $default_rules[$rtype] : array();
									foreach ($all_plans_data as $plan) {
										if ($plan['arm_subscription_plan_id'] != '-2') {
											$selected = in_array($plan['arm_subscription_plan_id'], $default_rules[$rtype]) ? 'selected="selected"' : '';
											echo '<option value="' . esc_attr($plan['arm_subscription_plan_id']) . '" ' . $selected . '>' . esc_html(stripslashes($plan['arm_subscription_plan_name'])) . '</option>';
										}
									}
								}
								?>
							</select>
							
						</div>
					</div>
				</div>

			<?php $arm_default_ar_cntr++;
			endforeach; ?>

			<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters('arm_load_restriction_settings_section', 'drip_rules') : ''; ?>

			<?php do_action('arm_after_access_restriction_settings_html', $general_settings); ?>

			<div class="arm_submit_btn_container arm_apply_changes_btn_container">
				<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; ?>" class="arm_submit_btn_loader" id="arm_loader_img" style="display:none;" width="24" height="24" />
				<button id="arm_access_restriction_settings_btn" class="arm_save_btn" name="arm_access_restriction_settings_btn" type="submit">
					<?php esc_html_e('Apply Changes', 'armember-membership'); ?>
				</button>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr(wp_create_nonce('arm_wp_nonce')); ?>" />
			</div>
		</form>	
	</div>
</div>
