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
		<form method="post" action="#" id="arm_access_restriction" class="arm_access_restriction arm_admin_form" onsubmit="return false;">
						<?php do_action( 'arm_before_access_restriction_settings_html', $general_settings ); ?>

						<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters  ('arm_load_restriction_settings_section','general_restriction') : ''; //phpcs:ignore;?>
			
		
						<div class="page_sub_title" id="arm_global_default_access_rules">
							<?php esc_html_e( 'Default Access Rules for newly added Content', 'armember-membership' ); ?>
							<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php esc_html_e( 'Please configure default rules to restrict any newly added page, post, category, custom post, etc for which there is no rule defined at Access Rules.', 'armember-membership' ); ?>"></i>
						</div>
						<?php
						$ruleTypes = array(
							'page'     => esc_html__( 'New Pages', 'armember-membership' ),
							'post'     => esc_html__( 'New Posts', 'armember-membership' ),
							'category' => esc_html__( 'New Categories', 'armember-membership' ),

						);
						if($ARMemberLite->is_arm_pro_active)
						{
							$ruleTypes['nav_menu'] = esc_html__('New Navigation Menus', 'armember-membership');
						}
						if ( isset( $defaultRulesTypes['post_type'] ) && ! empty( $defaultRulesTypes['post_type'] ) ) {
							foreach ( $defaultRulesTypes['post_type'] as $postType => $title ) {
								if ( ! in_array( $postType, $ruleTypes ) ) {
									$ruleTypes[ $postType ] = esc_html__( 'New', 'armember-membership' ) . ' ' . $title;
								}
							}
						}
						if ( isset( $defaultRulesTypes['taxonomy'] ) && ! empty( $defaultRulesTypes['taxonomy'] ) ) {
							foreach ( $defaultRulesTypes['taxonomy'] as $taxonomy => $title ) {
								if ( $taxonomy != 'category' ) {
									$ruleTypes[ $taxonomy ] = esc_html__( 'New', 'armember-membership' ) . ' ' . $title;
								}
							}
						}
						?>
						<table class="form-table">
							<?php if ( ! empty( $ruleTypes ) ) : ?>
								<?php
								$arm_default_ar_cntr = 0;
								foreach ( $ruleTypes as $rtype => $rtitle ) :
									if($arm_default_ar_cntr==4) 
                                        {
                                ?>
                                            <tr class="form-field">
                                                <td colspan="">
                                                    <span class="arm_failed_login_sub_title">
                                                        <strong>
                                                            <?php esc_html_e('Custom Post type, Taxonomy, Tag', 'armember-membership'); ?>
                                                        </strong>
                                                    </span>
                                                </td>
                                            </tr>
                                <?php
                                        }
									$arm_default_restriction_option = '';
									if ( empty( $default_rules[ $rtype ] ) ) {
										$arm_default_restriction_option = '';
									} elseif ( is_array( $default_rules[ $rtype ] ) && in_array( '-2', $default_rules[ $rtype ] ) ) {
										$arm_default_restriction_option = '-2';
									} elseif ( ! empty( $default_rules[ $rtype ] ) ) {
										$arm_default_restriction_option = '1';
									}
									?>
									<tr class="form-field">
										<th><?php echo esc_html($rtitle); ?></th>
										<td>
											<label  class="arm_min_width_100">
													<input type="radio" name="arm_default_restriction_option[<?php echo esc_attr($rtype); ?>]" value="0" class="arm_default_restriction_option arm_iradio" <?php checked( $arm_default_restriction_option, '' ); ?>  data-cntr="<?php echo esc_attr($arm_default_ar_cntr); ?>">
													<span><?php esc_html_e( 'Everyone', 'armember-membership' ); ?></span>
											</label>
											<label class="arm_min_width_150">
													<input type="radio" name="arm_default_restriction_option[<?php echo esc_attr($rtype); ?>]" value="-2" class="arm_default_restriction_option arm_iradio" <?php checked( $arm_default_restriction_option, '-2' ); ?>  data-cntr="<?php echo esc_attr($arm_default_ar_cntr); ?>">
												   <span><?php esc_html_e( 'Only logged in member (Everyone)', 'armember-membership' ); ?></span>
											</label>
											<label class="arm_min_width_150">
													<input type="radio" name="arm_default_restriction_option[<?php echo esc_attr($rtype); ?>]" value="1" class="arm_default_restriction_option arm_iradio" <?php checked( $arm_default_restriction_option, '1' ); ?> data-cntr="<?php echo esc_attr($arm_default_ar_cntr); ?>">
													<span><?php esc_html_e( 'Selected Plan(s) Only', 'armember-membership' ); ?></span><br>
											</label>
										</td>
									</tr>

									<tr class="form-field arm_default_access_restrictions_row arm_default_restriction_option_<?php echo esc_attr($arm_default_ar_cntr); ?>" style="
																																		<?php
																																		if ( $arm_default_restriction_option != 1 ) {
																																			?>
										 display: none; <?php } ?>">
										<th>&nbsp;</th>
										<td>
											<select name="arm_default_rules[<?php echo esc_attr($rtype); ?>][]" class="arm_default_rule_select arm_chosen_selectbox" multiple data-placeholder="<?php esc_html_e( 'Select Plan', 'armember-membership' ); ?>" tabindex="-1">
												<?php
												if ( ! empty( $all_plans_data ) ) {
													$default_rules[ $rtype ] = ( ! empty( $default_rules[ $rtype ] ) ) ? $default_rules[ $rtype ] : array();
													foreach ( $all_plans_data as $plan ) {
														if ( $plan['arm_subscription_plan_id'] != '-2' ) {
															?>
														<option value="<?php echo esc_html($plan['arm_subscription_plan_id']); ?>" <?php echo ( in_array( $plan['arm_subscription_plan_id'], $default_rules[ $rtype ] ) ) ? 'selected="selected"' : ''; ?>><?php echo stripslashes( $plan['arm_subscription_plan_name'] ); //phpcs:ignore ?></option>
															<?php
														}
													}
												}
												?>
											</select>
											<?php $da_tooltip = esc_html__( 'Please select plan(s) for members can access', 'armember-membership' ) . " {$rtitle} " . esc_html__( 'by default.', 'armember-membership' ); ?>
											<i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo esc_html($da_tooltip); ?>"></i>
										</td>
									</tr>
									<?php
									$arm_default_ar_cntr++;
							endforeach;
								?>
							<?php endif; ?>
						</table>
						<?php echo ($ARMemberLite->is_arm_pro_active) ? apply_filters  ('arm_load_restriction_settings_section','drip_rules') : ''; //phpcs:ignore;?>
						
			<?php do_action( 'arm_after_access_restriction_settings_html', $general_settings ); ?>
			<div class="arm_submit_btn_container">
				<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL) . '/arm_loader.gif'; //phpcs:ignore ?>" class="arm_submit_btn_loader" id="arm_loader_img" style="display:none;" width="24" height="24" />&nbsp;<button id="arm_access_restriction_settings_btn" class="arm_save_btn" name="arm_access_restriction_settings_btn" type="submit"><?php esc_html_e( 'Save', 'armember-membership' ); ?></button>
				<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>			 
			</div>
		</form>
	</div>
</div>
