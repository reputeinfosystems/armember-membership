<?php
if ( ! class_exists( 'armLiteAdminDashboardWidgets' ) ) {
	class armLiteAdminDashboardWidgets {

		function __construct() {
			add_action( 'admin_head', array( $this, 'armAdminDashboardWidgetsStyle' ) );
			add_action( 'wp_dashboard_setup', array( $this, 'armAdminDashboardWidgets_init' ) );
			add_action( 'wp_ajax_arm_load_summary', array( $this, 'armAdminDashboardSummary' ) );
		}
		function armAdminDashboardWidgetsStyle() {
			global $pagenow, $arm_lite_ajaxurl,$ARMemberLite;
			if ( ( current_user_can( 'administrator' ) || current_user_can( 'arm_admin_dashboard_widgets' ) ) && $pagenow == 'index.php' ) {
				if($ARMemberLite->is_arm_pro_active)
				{
					$arm_load_arfhighchart_script = '';
					apply_filters('arm_load_arfhighchart_script',$arm_load_arfhighchart_script);
				}
				wp_register_style( 'arm-admin-dashboard-widget-styles', MEMBERSHIPLITE_URL . '/css/arm_admin_dashboard.css', array(), MEMBERSHIPLITE_VERSION );
				wp_enqueue_style( 'arm-admin-dashboard-widget-styles' );
				?>
				<script type="text/javascript">
					jQuery(document).ready(function(e){
						var arm_wp_nonce = jQuery('#ARMemberSummary input[name="arm_wp_nonce"]').val();
						jQuery.ajax({
							url: __ARMAJAXURL,
							type: 'POST',
							data: 'action=arm_load_summary&_wpnonce='+arm_wp_nonce,
							success : function( data ){
								jQuery(document).find('.arm_loader_img_dashboard').hide();
								jQuery('#ARMemberSummary').find('.inside').append(data);
								jQuery('#ARMemberSummary').find('.arm_plugin_logo img').css("margin-bottom","0");
							}
						});
					});
				</script>
				<?php
			}
		}
		function armAdminDashboardWidgets_init() {
			if ( current_user_can( 'administrator' ) || current_user_can( 'arm_admin_dashboard_widgets' ) ) {
				/* Register Admin Widgets */
				$armemberstatistics_txt = esc_html__( 'ARMember Statistics', 'armember-membership' );
				$recentmemeber_txt      = esc_html__( 'ARMember Recent Members', 'armember-membership' );
				$recentpayments_txt     = esc_html__( 'ARMember Recent Payments', 'armember-membership' );
				wp_add_dashboard_widget( 'ARMemberSummary', $armemberstatistics_txt, array( $this, 'ARMemberSummary_display' ) );
				wp_add_dashboard_widget( 'ARMRegisteredMembers', $recentmemeber_txt, array( $this, 'ARMRegisteredMembers_display' ) );
				wp_add_dashboard_widget( 'ARMUserTransactions', $recentpayments_txt, array( $this, 'ARMUserTransactions_display' ) );
				// wp_add_dashboard_widget( 'armember-add-ons', esc_html__( 'ARMember Pro Add-Ons', 'armember-membership' ), array( $this, 'armember_dashboard_widgets_add_ons_list' ) );

				global $wp_meta_boxes;
				$normal_widgets       = $wp_meta_boxes['dashboard']['normal']['core'];
				$side_widgets         = $wp_meta_boxes['dashboard']['side']['core'];
				$widget_backup_normal = array( 'ARMemberSummary' => $normal_widgets['ARMemberSummary'] );
				$widget_backup_side   = array(
					'ARMRegisteredMembers' => $normal_widgets['ARMRegisteredMembers'],
					'ARMUserTransactions'  => $normal_widgets['ARMUserTransactions'],
				);
				/* Unset Widgets From Main Array */
				unset( $normal_widgets['ARMemberSummary'] );
				unset( $normal_widgets['ARMRegisteredMembers'] );
				unset( $normal_widgets['ARMUserTransactions'] );
				/* Sort & Save Right Side Widgets */
				$sorted_normal                                = array_merge( $widget_backup_normal, $normal_widgets );
				$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_normal;
				/* Sort & Save Left Side Widgets */
				$sorted_side                                = array_merge( $widget_backup_side, $side_widgets );
				$wp_meta_boxes['dashboard']['side']['core'] = $sorted_side;
			}
		}
		function armember_dashboard_widgets_add_ons_list() {
			global $arm_lite_version;
			$armember_addons_page = get_transient( 'arm_dashboard_listing_data_page' );
			if ( false === $armember_addons_page ) {
				$arm_dashboard_add_ons_list_url  = 'https://www.armemberplugin.com/armember_addons/addon_whatsnew_list.php?arm_version=' . $arm_lite_version;
				$arm_dashboard_add_ons_list_args = array();
				$arm_dashboard_add_ons_list      = wp_remote_get( $arm_dashboard_add_ons_list_url, $arm_dashboard_add_ons_list_args );

				if ( is_wp_error( $arm_dashboard_add_ons_list ) ) {
					printf( esc_html__( '%1$sThere is something error to retrieve the %2$s add-ons list. Please try again later.%3$s', 'armember-membership' ), "<div class='arm_add_ons_msg'>", 'armember-membership', '</div>' ); //phpcs:ignore
				} else {
					$arm_dashboard_add_ons_list = json_decode( $arm_dashboard_add_ons_list['body'] );
					$arm_all_addons_list        = apply_filters( 'arm_dashboard_add_more_add_ons', $arm_dashboard_add_ons_list );

					set_transient( 'arm_dashboard_listing_data_page', $arm_all_addons_list, WEEK_IN_SECONDS );
				}
			} else {
				$arm_all_addons_list = $armember_addons_page;
			}

			if ( ! empty( $arm_all_addons_list ) ) {
				?>
				<div class="armember_dashbord_wrapper_addons" id="armember_dashbord_wrapper_addons">
					<table cellspacing="0" cellpadding="0" border="0" id="armember_dashbord_tbl_addons">
						<tbody>
						<?php

						if ( ! empty( $arm_all_addons_list ) ) {

							$arm_addons_list_tr_class     = 'even';
							$arm_addons_list_tr_class_ext = '';

							$arm_total_addons = count( $arm_all_addons_list );

							$arm_addons_last_tr_counter = ceil( $arm_total_addons / 4 );

							echo '<tr class="' . esc_attr($arm_addons_list_tr_class) . ' arm_dashboard_frist_addons_icon" >'; //phpcs:ignore

							$arm_addons_list_counter = 1;
							$arm_addons_row_counter  = 1;

							foreach ( $arm_all_addons_list as $key => $arm_add_ons_list ) {
								$arm_add_ons_list_link = $arm_add_ons_list->addon_url;
								$arm_add_ons_list_img  = $arm_add_ons_list->addon_icon_url;
								$arm_add_ons_list_name = $arm_add_ons_list->addon_name;
								if ( ! empty( $arm_add_ons_list->addon_display_size ) && $arm_add_ons_list->addon_display_size == 'full' ) {
									$addon_display_anchor_styling = ! empty( $arm_add_ons_list->addon_display_anchor_styling ) ? ' style="' . $arm_add_ons_list->addon_display_anchor_styling . '"' : '';
									?>
											<td colspan="4" align="center" class="arm_dashboard_add_ons_icon arm_dashboard_addon_list_no_border arm_dashboard_icon_full">
												<a target="_blank" class="arm_dashboard_add_ons_icon_image" href="<?php echo esc_url( $arm_add_ons_list_link ); ?>" title='<?php echo esc_attr( $arm_add_ons_list_name ); ?>'<?php echo $addon_display_anchor_styling; //phpcs:ignore ?>>
													<img src="<?php echo esc_url( $arm_add_ons_list_img ); //phpcs:ignore ?>"  alt='<?php echo esc_attr( $arm_add_ons_list_name ); ?>'/>
												</a>			                            
											</td>   			                              			                                
									<?php
									$arm_addons_list_counter = $arm_addons_list_counter + 3;
									$arm_total_addons        = $arm_total_addons + 3;
									$arm_addons_last_tr_counter++;
								} else {
									$arm_add_on_td_class = '';
									if ( $arm_addons_list_counter % 4 == 0 ) {
										$arm_add_on_td_class = ' arm_dashboard_addon_list_no_border';
									}
									?>
										<td class="arm_dashboard_add_ons_icon <?php echo esc_attr( $arm_add_on_td_class ); ?>">
											<a target="_blank" class="arm_dashboard_add_ons_icon_image" href="<?php echo esc_url( $arm_add_ons_list_link ); ?>" title='<?php echo esc_attr( $arm_add_ons_list_name ); ?>'>
												<img src="<?php echo esc_url( $arm_add_ons_list_img ); //phpcs:ignore ?>"  alt='<?php echo esc_attr( $arm_add_ons_list_name ); ?>'/>
											</a>
										</td>			                        
									<?php
								}
								if ( $arm_addons_list_counter % 4 == 0 && $arm_addons_list_counter < $arm_total_addons ) {
									echo '</tr>';
									$arm_addons_list_tr_class = ( $arm_addons_list_tr_class == 'even' ) ? 'odd' : 'even';
									$arm_addons_row_counter++;
									if ( $arm_addons_row_counter == $arm_addons_last_tr_counter ) {
										$arm_addons_list_tr_class_ext = 'arm_dashboard_addons_last_row';
									}
									echo '<tr class="' . esc_attr( $arm_addons_list_tr_class ) . ' ' . esc_attr( $arm_addons_list_tr_class_ext ) . '">';
								}

								if ( $arm_addons_list_counter == $arm_total_addons ) {
									echo '</tr>';
								}
									$arm_addons_list_counter++;
							}
						}

						?>

						</tbody>
					</table>
				</div>

				<?php
			}
		}
		function ARMRegisteredMembers_display() {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_members_class, $arm_subscription_plans;
			$members     = array();
			$user_arg    = array(
				'orderby'      => 'ID',
				'order'        => 'DESC',
				'number'       => '6',
				'role__not_in' => 'administrator',
			);
			$members     = get_users( $user_arg );
			$date_format = $arm_global_settings->arm_get_wp_date_format();
			if ( ! empty( $members ) ) {
				?>
				<div class="ARMRegisteredMembers_container armAdminDashboardWidgetContent">
					<?php 
					if($ARMemberLite->is_arm_pro_active)
					{
						$arm_chartRecentMembers = '';
						echo apply_filters('arm_load_chart_container',$arm_chartRecentMembers,'recent_members'); //phpcs:ignore
					}?>
					<table cellpadding="0" cellspacing="0" border="0" id="ARMRegisteredMembers_table" class="display">
						<thead>
							<tr>
								<th align="left"><?php esc_html_e( 'Username', 'armember-membership' ); ?></th>
								<th align="left" width="40%"><?php esc_html_e( 'Email', 'armember-membership' ); ?></th>
								<th align="left"><?php esc_html_e( 'Membership', 'armember-membership' ); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ( $members as $m ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $arm_slugs->manage_members . '&action=view_member&id=' . $m->ID ) ); //phpcs:ignore ?>"><?php echo esc_html($m->user_login);  ?></a></td>
								<td><?php echo esc_html($m->user_email); ?></td>
								<td>
								<?php
								$plan_ids  = get_user_meta( $m->ID, 'arm_user_plan_ids', true );
								$plan_name = $arm_subscription_plans->arm_get_comma_plan_names_by_ids( $plan_ids );
								echo ( ! empty( $plan_name ) ) ? $plan_name : '<span class="arm_empty">--</span>'; //phpcs:ignore
								?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<div class="armclear"></div>
					<div class="arm_view_all_link">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $arm_slugs->manage_members ) ); //phpcs:ignore ?>"><?php esc_html_e( 'View All Members', 'armember-membership' ); ?></a>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="arm_dashboard_error_box"><?php esc_html_e( 'There is no any recent members found.', 'armember-membership' ); ?></div>
				<?php
			}
		}
		function ARMUserTransactions_display() {
			global $wp, $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_members_class, $arm_subscription_plans, $arm_payment_gateways, $arm_transaction;
			$date_format  = $arm_global_settings->arm_get_wp_date_format();
			$pay_log = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `".$ARMemberLite->tbl_arm_payment_log."` WHERE `arm_is_post_payment`=%d AND `arm_paid_post_id`=%d AND arm_is_gift_payment = %d ORDER BY `arm_created_date` DESC LIMIT 0,6",0,0,0), ARRAY_A); //phpcs:ignore --Reason $ARMember->tbl_arm_payment_log is a table name
			$bt_logs      = $arm_transaction->arm_get_bank_transfer_logs( 0, 0, 0, 6 );
			$payment_log  = array_merge( $pay_log, $bt_logs );
			$transactions = array();
			if ( ! empty( $payment_log ) ) {
				$i = 0;
				foreach ( $payment_log as $log ) {
					$date = strtotime( $log['arm_created_date'] );
					if ( isset( $newLog[ $date ] ) && ! empty( $newLog[ $date ] ) ) {
						$date                 += $i;
						$transactions[ $date ] = $log;
					} else {
						$transactions[ $date ] = $log;
					}
					$i++;
				}
				krsort( $transactions );

			}
			if ( ! empty( $transactions ) ) {
				$global_currency     = $arm_payment_gateways->arm_get_global_currency();
				$all_currencies      = $arm_payment_gateways->arm_get_all_currencies();
				$global_currency_sym = $all_currencies[ strtoupper( $global_currency ) ];
				?>
				<div class="ARMUserTransactions_content armAdminDashboardWidgetContent">
					<?php
					if($ARMemberLite->is_arm_pro_active)
					{ 
						$arm_chartRecentMembers = '';
						echo apply_filters('arm_load_chart_container',$arm_chartRecentMembers,'transactions'); //phpcs:ignore
					}?>
					<table cellpadding="0" cellspacing="0" border="0" id="ARMUserTransactions_table" class="display">
						<thead>
							<tr>
								<th align="left"><?php esc_html_e( 'Username', 'armember-membership' ); ?></th>
								<th align="left"><?php esc_html_e( 'Membership', 'armember-membership' ); ?></th>
								<th align="center"><?php esc_html_e( 'Amount', 'armember-membership' ); ?></th>
								<th align="center"><?php esc_html_e( 'Status', 'armember-membership' ); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php
						$j = 0;
						foreach ( $transactions as $t ) :
							$t = (object) $t;
							?>
								<?php
								if ( $j > 5 ) {
									continue;
								}
								$j++;
								?>
							<tr>
								<td><a href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->manage_members . '&action=view_member&id=' . $t->arm_user_id ) ); //phpcs:ignore ?>">
									<?php
									$data = get_userdata( $t->arm_user_id );
									if ( ! empty( $data ) ) {
										echo esc_html($data->user_login); 
									}
									?>
								</a></td>
								<td>
									<?php
									$plan_name = $arm_subscription_plans->arm_get_plan_name_by_id( $t->arm_plan_id );
									echo ( ! empty( $plan_name ) ) ? $plan_name : '<span class="arm_empty">--</span>'; //phpcs:ignore
									?>
								</td>
								<td class="arm_center arm_table_amount">
									<?php
									if ( ! empty( $t->arm_amount ) && $t->arm_amount > 0 ) {
										$t_currency = isset( $t->arm_currency ) ? strtoupper( $t->arm_currency ) : strtoupper( $global_currency );
										$currency   = ( isset( $all_currencies[ $t_currency ] ) ) ? $all_currencies[ $t_currency ] : $global_currency_sym;
										echo $arm_payment_gateways->arm_prepare_amount( $t->arm_currency, $t->arm_amount ); //phpcs:ignore
										if ( $global_currency_sym == $currency && strtoupper( $global_currency ) != $t_currency ) {
												echo ' (' . $t_currency . ')'; //phpcs:ignore
										}
									} else {
										echo $arm_payment_gateways->arm_prepare_amount( $t->arm_currency, $t->arm_amount ); //phpcs:ignore
									}
									?>
								</td>
								<td class="arm_center arm_table_status"><?php echo $arm_transaction->arm_get_transaction_status_text($t->arm_transaction_status); //phpcs:ignore?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<div class="armclear"></div>
					<div class="arm_view_all_link">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $arm_slugs->transactions ) ); //phpcs:ignore ?>"><?php esc_html_e( 'View All Transactions', 'armember-membership' ); ?></a>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="arm_dashboard_error_box"><?php esc_html_e( 'There is no any recent transactions found.', 'armember-membership' ); ?></div>
				<?php
			}
		}
		function ARMemberSummary_display() {
			?>
			<div class="arm_plugin_logo">
			<a href="https://www.armemberplugin.com" target="_blank" style="box-shadow:none;"><img alt="" src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/arm_logo.png"/></a><br/>

				<img src="<?php echo esc_attr(MEMBERSHIPLITE_IMAGES_URL); //phpcs:ignore ?>/arm_loader.gif" class="arm_loader_img_dashboard" width="24" height="24" />
				<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr( $wpnonce ); ?>"/>
			</div>

			<?php
		}
		function armAdminDashboardSummary() {
			if ( current_user_can( 'administrator' ) || current_user_can( 'arm_admin_dashboard_widgets' ) ) {
				global $wp, $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_members_class, $arm_subscription_plans;
				$ARMemberLite->arm_check_user_cap('',1); //phpcs:ignore -nonce is already checked
				$all_members          = $arm_members_class->arm_get_all_members_without_administrator( 0, 1 );
				$active_members       = $arm_members_class->arm_get_all_members_without_administrator( 1, 1 );
				$total_members        = ( ! empty( $all_members ) ) ? $all_members : 0;
				$total_active_members = ( ! empty( $active_members ) ) ? $active_members : 0;

				$inactive_type          = array( 2 );
				$total_inactive_members = $arm_members_class->arm_get_all_members_without_administrator( 0, 1, $inactive_type );
				if($ARMemberLite->is_arm_pro_active)
				{
					$total_inactive_members = apply_filters('arm_get_all_members_without_administrator_dash',$total_inactive_members,$inactive_type);

					$total_inactive_members = !empty($total_inactive_members) ? $total_inactive_members : 0;
				}
				$pending_type          = array( 3 );
				$total_pending_members = $arm_members_class->arm_get_all_members_without_administrator( 0, 1, $pending_type );
				if($ARMemberLite->is_arm_pro_active)
				{
					$total_pending_members = apply_filters('arm_get_all_members_without_administrator_dash',$total_pending_members,$pending_type);

					$total_pending_members = !empty($total_pending_members) ? $total_pending_members : 0;	
				}
				$terminated_type          = array( 4 );
				$total_terminated_members = $arm_members_class->arm_get_all_members_without_administrator( 0, 1, $terminated_type );
				if($ARMemberLite->is_arm_pro_active)
				{
					$total_terminated_members = apply_filters('arm_get_all_members_without_administrator_dash',$total_terminated_members,$terminated_type);
					$total_terminated_members = !empty($total_terminated_members) ? $total_terminated_members : 0;
				}
				$all_plans   = $arm_subscription_plans->arm_get_all_subscription_plans();
				$total_plans = ( ! empty( $all_plans ) ) ? count( $all_plans ) : 0;

				$arm_manage_member_page_url = admin_url( 'admin.php?page=' . $arm_slugs->main );
				?>
				<div class="arm_dashboard_member_summary">
					<a href="<?php echo esc_attr($arm_manage_member_page_url); //phpcs:ignore ?>" class="welcome-icon">
						<div class="arm_total_members arm_member_summary arm_member_summary_mr4i">
                            <div class="arm_member_summary_count_wrapper">
                                <div class="arm_member_summary_count"><?php echo esc_html($total_members);?></div>
                                <div class="arm_member_summery_icon_wrapper">
                                    <img class="arm_member_summery_icon" src="<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore?>/arm_total_members.png" alt="<?php esc_html_e('Total Members', 'armember-membership');?>">
                                </div>
                            </div>
                            <div class="arm_member_summary_label_wrapper">
                                <div class="arm_member_summary_label"><?php esc_html_e('Total Members', 'armember-membership');?></div>
                            </div>
						</div>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $arm_slugs->manage_plans ) ); //phpcs:ignore ?>" class="welcome-icon">
						<div class="arm_membership_plans arm_member_summary">
                            <div class="arm_member_summary_count_wrapper">
                                <div class="arm_member_summary_count"><?php echo esc_html($total_plans);?></div>
                                <div class="arm_member_summery_icon_wrapper">
                                    <img class="arm_member_summery_icon" src="<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore?>/arm_membership_plans.png" alt="<?php esc_html_e('Membership Plans', 'armember-membership');?>">
                                </div>
                            </div>
                            <div class="arm_member_summary_label_wrapper">
                                <div class="arm_member_summary_label"><?php esc_html_e('Membership Plans', 'armember-membership');?></div>
                            </div>
						</div>
					</a>
					<?php $arm_admin_dashboard_manage_member_page_url = apply_filters('arm_admin_dashboard_manage_member_page_url',$arm_manage_member_page_url,'inactive_member'); //phpcs:ignore?>
					<a href="<?php echo esc_url($arm_admin_dashboard_manage_member_page_url); //phpcs:ignore ?>" class="welcome-icon">
						<div class="arm_inactive_members arm_member_summary arm_member_summary_mr4i">
                            <div class="arm_member_summary_count_wrapper">
                            <div class="arm_member_summary_count"><?php echo esc_html($total_inactive_members);?></div>
                                <div class="arm_member_summery_icon_wrapper">
                                <img class="arm_member_summery_icon" src="<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore?>/arm_inactive_members.png" alt="<?php esc_html_e('Inactive Members', 'armember-membership');?>">
                                </div>
                            </div>
                            <div class="arm_member_summary_label_wrapper">
                                <div class="arm_member_summary_label"><?php esc_html_e('Inactive Members', 'armember-membership');?></div>
                            </div>
						</div>
					</a>
					<?php $arm_admin_dashboard_manage_member_page_url = apply_filters('arm_admin_dashboard_manage_member_page_url',$arm_manage_member_page_url,'active_member'); //phpcs:ignore?>
					<a href="<?php echo esc_url($arm_admin_dashboard_manage_member_page_url); //phpcs:ignore ?>" class="welcome-icon">
						<div class="arm_active_members arm_member_summary">
                            <div class="arm_member_summary_count_wrapper">
                                <div class="arm_member_summary_count"><?php echo esc_html($total_active_members);?></div>
                                <div class="arm_member_summery_icon_wrapper">
                                <img class="arm_member_summery_icon" src="<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore?>/arm_active_members.png" alt="<?php esc_html_e('Active Members', 'armember-membership');?>">
                                </div>
                            </div>
                            <div class="arm_member_summary_label_wrapper">
                                <div class="arm_member_summary_label"><?php esc_html_e('Active Members', 'armember-membership');?></div>
                            </div>
						</div>
					</a>
					<?php $arm_admin_dashboard_manage_member_page_url = apply_filters('arm_admin_dashboard_manage_member_page_url',$arm_manage_member_page_url,'pending_member'); //phpcs:ignore?>
					<a href="<?php echo esc_url($arm_admin_dashboard_manage_member_page_url); //phpcs:ignore ?>" class="welcome-icon">
						<div class="arm_pending_members arm_member_summary arm_member_summary_mr4i">
                            <div class="arm_member_summary_count_wrapper">
                                <div class="arm_member_summary_count"><?php echo esc_html($total_pending_members);?></div>
                                <div class="arm_member_summery_icon_wrapper">
                                <img class="arm_member_summery_icon" src="<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore?>/arm_pending_members.png" alt="<?php esc_html_e('Pending Members', 'armember-membership');?>">
                                </div>
                            </div>
                            <div class="arm_member_summary_label_wrapper">
                                <div class="arm_member_summary_label"><?php esc_html_e('Pending Members', 'armember-membership');?></div>
                            </div>
						</div>
					</a>
					<?php $arm_admin_dashboard_manage_member_page_url = apply_filters('arm_admin_dashboard_manage_member_page_url',$arm_manage_member_page_url,'terminated_member'); //phpcs:ignore?>
					<a href="<?php echo esc_url($arm_admin_dashboard_manage_member_page_url); //phpcs:ignore ?>" class="welcome-icon">
						<div class="arm_terminate_members arm_member_summary">
                            <div class="arm_member_summary_count_wrapper">
                                <div class="arm_member_summary_count"><?php echo esc_html($total_terminated_members);?></div>
                                    <div class="arm_member_summery_icon_wrapper">
                                    <img class="arm_member_summery_icon" src="<?php echo MEMBERSHIPLITE_IMAGES_URL; //phpcs:ignore?>/arm_terminate_members.png" alt="">
                                </div>
                            </div>
                            <div class="arm_member_summary_label_wrapper">
                                <div class="arm_member_summary_label"><?php esc_html_e('Terminate Members', 'armember-membership');?></div>
                            </div>
						</div>
					</a>
				</div>
				<div class="armclear"></div>
				<?php $this->armember_dashboard_widgets_add_ons_list(); ?>
				<?php /* <div class="arm_members_chart_container arm_min_width_255" >
					<?php //$arm_members_class->arm_chartRecentMembers()?>
					<div class="armclear"></div>
					<?php //$arm_members_class->arm_chartPlanMembers($all_plans)?>
				</div> */ ?>
				<?php
				die();
			}
		}
	}
	global $armLiteAdminDashboardWidgets;
	$armLiteAdminDashboardWidgets = new armLiteAdminDashboardWidgets();
}
