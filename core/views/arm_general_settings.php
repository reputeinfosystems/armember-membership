<?php
global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings,  $arm_slugs, $arm_social_feature;
$active = 'arm_general_settings_tab_active';

$g_action = isset( $_REQUEST['action'] ) ? sanitize_text_field($_REQUEST['action']) : 'general_settings'; //phpcs:ignore

$g_sub_action = isset($_GET['sub_action']) ? sanitize_text_field($_GET['sub_action']) : '';
?>
<div class="wrap arm_page arm_general_settings_main_wrapper">
<div class="content_wrapper arm_global_settings_content" id="content_wrapper">
    <div class="armclear"></div>
    <div class="armember_general_settings_wrapper">
        <div class="arm_general_settings_tab_wrapper">
            <a class="arm_general_settings_tab arm_setting_tabs <?php echo ($g_action == 'general_settings') ? esc_attr($active) : ""; //phpcs:ignore?>" href="<?php echo esc_url(admin_url('admin.php?page=' . $arm_slugs->general_settings)); ?>"><?php esc_html_e('General Settings', 'armember-membership'); ?></a>
            <?php if ( $g_action === 'general_settings' ) : ?>
                <div class="arm_submenu_tab_indent">
                    <a class="arm_general_settings_tab arm_setting_tabs <?php echo ( $g_sub_action == 'email' ? esc_html($active) : '' ); ?> arm_padding_0 arm_margin_top_5 " href="javascript:void(0)" data-target="#email_setting_sec"><?php esc_html_e( 'Email', 'armember-membership' ); ?></a>
                    <a class="arm_general_settings_tab arm_setting_tabs <?php echo ( $g_sub_action == 'preset_fields' ? esc_html($active) : '' ); ?> arm_padding_top_15 arm_padding_left_0 arm_padding_bottom_0" href="javascript:void(0)" data-target="#preset_fields_sec"><?php esc_html_e( 'Manage Preset Form Fields', 'armember-membership' ); ?></a>
                    <a class="arm_general_settings_tab arm_setting_tabs <?php echo ( $g_sub_action == 'email_scheduler' ? esc_html($active) : '' ); ?> arm_padding_top_15 arm_padding_left_0 arm_padding_bottom_0 arm_padding_right_0" href="javascript:void(0)" data-target="#email_scheduler_sec"><?php esc_html_e( 'Email Notification Scheduler', 'armember-membership' ); ?></a>
                    <a class="arm_general_settings_tab arm_setting_tabs <?php echo ( $g_sub_action == 'front_end_font' ? esc_html($active) : '' ); ?> arm_padding_top_15 arm_padding_left_0 arm_padding_bottom_0 arm_padding_right_0" href="javascript:void(0)" data-target="#front_end_font_sec"><?php esc_html_e( 'Front-end Font', 'armember-membership' ); ?></a>
                    <div class="armclear"></div>    
                </div>
            <?php endif; ?>    
            <a class="arm_general_settings_tab arm_setting_tabs <?php echo ( $g_action == 'payment_options' ? esc_html($active) : '' ); ?>" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=payment_options' ) ); ?>"><?php esc_html_e( 'Payment Gateways', 'armember-membership' ); ?></a>
            <a class="arm_general_settings_tab arm_setting_tabs <?php echo ( $g_action == 'page_setup' ? esc_html($active) : '' ); ?>" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=page_setup' ) ); ?>"><?php esc_html_e( 'Page Setup', 'armember-membership' ); ?></a>
            <a class="arm_general_settings_tab arm_setting_tabs <?php echo ( $g_action == 'access_restriction' ? esc_html($active) : '' ); ?>" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=access_restriction' )); ?>"><?php esc_html_e( 'Default Restriction Rules', 'armember-membership' ); ?></a>
            <a class="arm_general_settings_tab arm_setting_tabs <?php echo ( $g_action == 'block_options' ? esc_html($active) : '' ); ?>" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=block_options' )); ?>"><?php esc_html_e( 'Security Options', 'armember-membership' ); ?></a>
            <a class="arm_general_settings_tab arm_setting_tabs <?php echo ( $g_action == 'import_export' ? esc_html($active) : '' ); ?>" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=import_export' )); ?>"><?php esc_html_e( 'Import / Export', 'armember-membership' ); ?></a>
            <a class="arm_general_settings_tab arm_setting_tabs <?php echo ( $g_action == 'redirection_options' ? esc_html($active) : '' ); ?>" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=redirection_options' )); ?>"><?php esc_html_e( 'Redirection Rules', 'armember-membership' ); ?></a>
            <a class="arm_general_settings_tab arm_setting_tabs <?php echo ( $g_action == 'common_messages' ? esc_html($active) : '' ); ?>" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=common_messages' )); ?>"><?php esc_html_e( 'Common Messages', 'armember-membership' ); ?></a>
            <a class="arm_general_settings_tab arm_setting_tabs <?php echo ( $g_action == 'debug_logs' ? esc_html($active) : '' ); ?>" href="<?php echo esc_url(admin_url( 'admin.php?page=' . $arm_slugs->general_settings . '&action=debug_logs' )); ?>"><?php esc_html_e( 'Debug Log Settings', 'armember-membership' ); ?></a>
            <div class="armclear"></div>
        </div>

        <div class="arm_settings_container">
            <?php
            $arm_admin_notice = '';
            $arm_admin_notice = apply_filters('arm_admin_notice',$arm_admin_notice);  //phpcs:ignore
            if(!empty($arm_admin_notice)){
                echo '<div class="arm_global_settings_main_wrapper"><div class="page_sub_content">'.$arm_admin_notice.'</div></div>'; //phpcs:ignore
            }
            $arm_setting_title   = esc_html__( 'General Settings', 'armember-membership' );
            $arm_setting_tooltip = '';
            $file_path           = MEMBERSHIPLITE_VIEWS_DIR . '/arm_global_settings.php';

            if ( $g_action === 'general_settings' ) {
                switch ( $g_sub_action ) {
                    case 'email':
                        $file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_general_email_settings.php';
                        $arm_setting_title = esc_html__( 'Email Settings', 'armember-membership' );
                        break;
                    case 'preset_fields':
                        $file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_general_preset_fields.php';
                        $arm_setting_title = esc_html__( 'Preset Form Fields', 'armember-membership' );
                        break;
                    case 'email_scheduler':
                        $file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_general_email_scheduler.php';
                        $arm_setting_title = esc_html__( 'Email Notification Scheduler', 'armember-membership' );
                        break;
                    default:
                        $file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_global_settings.php';
                        // $arm_setting_title = esc_html__( 'General Settings', 'armember-membership' );
                        break;
                }
            } elseif ( $g_action === 'payment_options' ) {
                switch ( $g_sub_action ) {
                    case 'paypal':
                        $file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_payment_paypal.php';
                        $arm_setting_title = esc_html__( 'PayPal Settings', 'armember-membership' );
                        break;
                    case 'stripe':
                        $file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_payment_stripe.php';
                        $arm_setting_title = esc_html__( 'Stripe Settings', 'armember-membership' );
                        break;
                    case 'offline':
                        $file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_payment_offline.php';
                        $arm_setting_title = esc_html__( 'Offline Payment Settings', 'armember-membership' );
                        break;
                    default:
                        $file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_manage_payment_gateways.php';
                        $arm_setting_title = esc_html__( 'Payment Gateways', 'armember-membership' );
                        break;
                }
            } else {
                switch ( $g_action ) {
                    case 'page_setup':
                        $file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_page_setup.php';
                        $arm_setting_title = esc_html__( 'Page Setup', 'armember-membership' );
                        break;
                    case 'block_options':
                        $file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_block_settings.php';
                        $arm_setting_title = esc_html__( 'Security Options', 'armember-membership' );
                        break;
                    case 'import_export':
                        $file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_import_export.php';
                        $arm_setting_title = esc_html__( 'Import / Export', 'armember-membership' );
                        break;
                    case 'redirection_options':
                        $file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_redirection_settings.php';
                        $arm_setting_title = esc_html__( 'Page/Post Redirection Rules', 'armember-membership' );
                        break;
                    case 'common_messages':
                        $file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_common_messages_settings.php';
                        $arm_setting_title = esc_html__( 'Common Messages', 'armember-membership' );
                        break;
                    case 'access_restriction':
                        $file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_access_restriction_settings.php';
                        $arm_setting_title = esc_html__( 'Default Restriction Rules', 'armember-membership' );
                        break;
                    case 'debug_logs':
                        $file_path         = MEMBERSHIPLITE_VIEWS_DIR . '/arm_debug_logs.php';
                        $arm_setting_title = esc_html__( 'Debug Log Settings', 'armember-membership' );
                        break;
                }
            }

            if ( file_exists( $file_path ) ) {
                ?>
                <!-- <div class="arm_settings_title_wrapper">
                    <div class="arm_setting_title"><?php echo esc_html($arm_setting_title) . ' ' . esc_html($arm_setting_tooltip); ?></div>
                </div> -->
                <?php include $file_path; ?>
            <?php } ?>
        </div>
    </div>
    <div class="armclear"></div>
</div>
</div>
<?php
    echo $ARMemberLite->arm_get_need_help_html_content('arm_'.$g_action); //phpcs:ignore
?>