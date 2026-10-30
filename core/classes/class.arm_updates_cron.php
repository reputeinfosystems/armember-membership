<?php 
if(! defined('ABSPATH')) exit;

if (!class_exists('ARM_updates_cron_Lite')) {

    class ARM_updates_cron_Lite {

        function __construct() {

            global $wpdb, $ARMemberLite, $arm_slugs;
            $arm_updates_cron_db_initialize = get_option('arm_updates_cron_db_initialize');
            if(!empty($arm_updates_cron_db_initialize))
            {
                add_filter('cron_schedules', array($this, 'arm_updates_cron_schedules'));
                add_action('init', array($this, 'arm_add_updates_cron'), 10);

                add_action('arm_handle_updates_db_migrate_data',array($this,'arm_handle_updates_db_migrate_data_func'));

                add_action('arm_handle_updates_db_migrate_activity_data',array($this,'arm_handle_updates_db_migrate_activity_data_func'));

                add_action('arm_handle_updates_db_migrate_subscription_data',array($this,'arm_handle_updates_db_migrate_subscription_data_func'));

                add_action('wp_ajax_arm_updates_cron_db_processing_notice',array($this,'arm_updates_cron_db_processing_notice'),10);
                add_action('wp_ajax_arm_updates_cron_db_completed_notice',array($this,'arm_updates_cron_db_completed_notice'),10);

                add_action('admin_init', array($this, 'arm_show_updates_cron_notice'), 1);

            }
            

        }

        function arm_show_updates_cron_notice() {
            global $arm_slugs;
            if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) { //phpcs:ignore
                if (!in_array($_REQUEST['page'], array($arm_slugs->manage_forms))) { //phpcs:ignore
                    add_filter('arm_admin_notice', array($this, 'arm_updates_cron_admin_notices'), 5, 1);
                }
            }
        }

        function arm_updates_cron_admin_notices($arm_admin_notice = '') {
            global $arm_slugs;

            $notice_html = '';
            $arm_allowed_slugs = (array) $arm_slugs;

            if(isset($_REQUEST['page']) && in_array($_REQUEST['page'], $arm_allowed_slugs)) //phpcs:ignore
            {
                $arm_updates_cron_db_notice = get_option('arm_updates_cron_db_notice');
                $arm_updates_cron_db_activity_notice = get_option('arm_updates_cron_db_activity_notice');
                $arm_updates_cron_db_subscription_notice = get_option('arm_updates_cron_db_subscription_notice', 2);

                $arm_in_progress_text = esc_html__('ARMember is updating your database in the background. This may take a little while, you can keep working meanwhile.', 'armember-membership');
                $arm_subscription_totals = get_option('arm_updates_cron_db_subscription_totals', array());
                if( $arm_updates_cron_db_subscription_notice == 1 && !empty($arm_subscription_totals['users']) )
                {
                    /* translators: %s: number of members */
                    $arm_in_progress_text .= ' ' . sprintf( esc_html__('%s members updated so far.', 'armember-membership'), number_format_i18n( (int) $arm_subscription_totals['users'] ) );
                }

                if( empty($arm_updates_cron_db_notice) || empty($arm_updates_cron_db_activity_notice) || empty($arm_updates_cron_db_subscription_notice) )
                {
                    $notice_html .= '<div class="armember_notice_warning yellow arm_padding_20 arm_dismiss_update_db_notice"><div class="arm_update_db_required_content"><div class="arm_font_size_20 arm_font_weight_600 arm_margin_bottom_8">' . esc_html__('ARMember Database Update Required', 'armember-membership') . '</div><div>' . esc_html__('ARMember has been updated! To keep things running smoothly, your database needs to be updated to the newest version. The update runs in the background and may take a little while.', 'armember-membership') . '</div><button type="button" class="armemailaddbtn arm_updates_cron_db_start_link arm_margin_top_16">' . esc_html__('Update ARMember Database', 'armember-membership') . '</button></div><div class="arm_update_db_progress_content hidden_section"><div class="arm_font_size_20 arm_font_weight_600 arm_margin_bottom_8">' . esc_html__('ARMember Database Update in Progress', 'armember-membership') . '</div><div>' . $arm_in_progress_text . '</div></div></div>';
                }
                else if($arm_updates_cron_db_notice=="1" || $arm_updates_cron_db_activity_notice=="1" || $arm_updates_cron_db_subscription_notice=="1")
                {
                    $notice_html .= '<div class="armember_notice_warning blue arm_padding_20 arm_dismiss_updated_db_notice"><div class="arm_font_size_20 arm_font_weight_600 arm_margin_bottom_8">' . esc_html__('ARMember Database Update in Progress', 'armember-membership') . '</div><div>' . $arm_in_progress_text . '</div></div>';
                }
                else if($arm_updates_cron_db_notice=="2" || $arm_updates_cron_db_activity_notice=="2")
                {
                    $notice_html .= '<div class="armember_notice_warning blue arm_padding_20 arm_dismiss_updated_data_notice"><div class="arm_font_size_20 arm_font_weight_600 arm_margin_bottom_8">' . esc_html__('ARMember Database Update Completed', 'armember-membership') . '</div><div>' . esc_html__('Your ARMember database is up to date. Thank you for updating to the latest version!', 'armember-membership') . '</div><button type="button" class="armemailaddbtn arm_margin_top_16">' . esc_html__('Thanks!', 'armember-membership') . '</button></div>';
                }

                $arm_admin_notice = $notice_html . $arm_admin_notice;
            }
            return $arm_admin_notice;
        }

        function arm_updates_cron_schedules($schedules)
        {
            if (!is_array($schedules)) {
                $schedules = array();
            }
            $schedules['arm_every_minute']=array('interval' => 60,'display'=>esc_html__('One Minute', 'armember-membership'));
            return $schedules;
        }

        function arm_add_updates_cron() {
            global $wpdb, $ARMemberLite, $arm_slugs, $arm_cron_hooks_interval, $arm_global_settings;
            //wp_get_schedules();
            //$arm_newdbversion = get_option('arm_version');
            $arm_updates_cron_db_notice = get_option('arm_updates_cron_db_notice');
            if($arm_updates_cron_db_notice < 2)
            {
                $hook = "arm_handle_updates_db_migrate_data";
                if (!wp_next_scheduled($hook)) {
                    wp_schedule_event(time(), 'arm_every_minute', $hook);
                }
            }

            $arm_updates_cron_db_activity_notice = get_option('arm_updates_cron_db_activity_notice');
            if($arm_updates_cron_db_activity_notice < 2)
            {
                //cron for update activity table database
                $hook = "arm_handle_updates_db_migrate_activity_data";
                if (!wp_next_scheduled($hook)) {
                    wp_schedule_event(time(), 'arm_every_minute', $hook);
                }
            }

            $arm_updates_cron_db_subscription_notice = get_option('arm_updates_cron_db_subscription_notice', 2);
            if($arm_updates_cron_db_subscription_notice < 2)
            {
                //cron for member plans & subscriptions tables
                $hook = "arm_handle_updates_db_migrate_subscription_data";
                if (!wp_next_scheduled($hook)) {
                    wp_schedule_event(time(), 'arm_every_minute', $hook);
                }
            }

        }

        function arm_handle_updates_db_migrate_data_func()
        {
            $arm_updates_cron_db_notice = get_option('arm_updates_cron_db_notice');
            set_time_limit(0); //phpcs:ignore
            if($arm_updates_cron_db_notice < 1)
            {
                return;
            }
            global $wp, $wpdb, $ARMemberLite;
            
            $user_update_limit = 1000;
            $total_updated_users = get_option('arm_updates_cron_db_total_users_updated');
            $total_updated_users = empty($total_updated_users) ? 0 : $total_updated_users;
            
            $args = array(
                        'offset' => $total_updated_users,
                        'number' => $user_update_limit,
                        'order_by'=>'ASC'
                    );
            $users = get_users( $args );
            
            $total_users = count($users);
            if($total_users>0)
            {
                foreach($users as $user)
                {
                    $user_id = $user->data->ID;
                    //$user_id = $user['ID'];
                    $arm_user_plan_ids_value = arm_get_user_meta($user_id,'arm_user_plan_ids',true);

                    $user_meta_value_array = array();
                    if(!empty($arm_user_plan_ids_value))
                    {
                        $user_meta_value_arr = arm_maybe_unserialize($arm_user_plan_ids_value);
                        if(!empty($user_meta_value_arr) && is_array($user_meta_value_arr))
                        {
                            foreach($user_meta_value_arr as $arm_user_plan_id)
                            {
                                $user_meta_value_array[] = (int)$arm_user_plan_id;
                            }
                        }
                    }
                    $user_meta_value_array = maybe_serialize($user_meta_value_array);

                    $arm_user_suspended_plan_ids_value = arm_get_user_meta($user_id,'arm_user_suspended_plan_ids',true);

                    $user_suspended_plan_meta_value_array = array();
                    if(!empty($arm_user_suspended_plan_ids_value))
                    {
                        $user_suspended_plan_meta_value_arr = arm_maybe_unserialize($arm_user_suspended_plan_ids_value);
                        if(!empty($user_suspended_plan_meta_value_arr) && is_array($user_suspended_plan_meta_value_arr))
                        {
                            foreach($user_suspended_plan_meta_value_arr as $arm_user_plan_id)
                            {
                                $user_suspended_plan_meta_value_array[] = (int)$arm_user_plan_id;
                            }
                        }
                    }
                    $user_suspended_plan_meta_value_array = maybe_serialize($user_suspended_plan_meta_value_array);

                    $wpdb->update($ARMemberLite->tbl_arm_members, array('arm_user_plan_ids' => $user_meta_value_array,'arm_user_suspended_plan_ids' => $user_suspended_plan_meta_value_array),     array('arm_user_id' => $user_id)); //phpcs:ignore

                    // update total user migrated to database
                    $total_updated_users = (int)$total_updated_users + 1;
                    update_option('arm_updates_cron_db_total_users_updated',$total_updated_users);
                }
            }
            else 
            {
                update_option('arm_updates_cron_db_notice',2);
                wp_clear_scheduled_hook("arm_handle_updates_db_migrate_data");
            }
            
        }

        function arm_handle_updates_db_migrate_activity_data_func()
        {
            $arm_updates_cron_db_activity_notice = get_option('arm_updates_cron_db_activity_notice');
            set_time_limit(0); //phpcs:ignore
            if($arm_updates_cron_db_activity_notice < 1)
            {
                return;
            }
            global $wp, $wpdb, $ARMemberLite;
            
            $user_update_limit = 1000;
            $total_updated_activity = get_option('arm_updates_cron_db_total_activity_updated');
            $total_updated_activity = empty($total_updated_activity) ? 0 : $total_updated_activity;

            $arm_activity_table = $ARMemberLite->tbl_arm_activity;
            
            $get_all_activity = $wpdb->get_results("SELECT arm_activity_id,arm_user_id,arm_content,arm_item_id FROM ".$arm_activity_table." LIMIT ".$total_updated_activity.",".$user_update_limit,ARRAY_A); //phpcs:ignore
            
            $get_all_activity_count = count($get_all_activity);
            if($get_all_activity_count > 0)
            {
                foreach($get_all_activity as $arm_content)
                {
                    $arm_act_id = $arm_content['arm_activity_id'];
                    $arm_content_data = arm_maybe_unserialize($arm_content['arm_content']);

                    $user_id = $arm_content['arm_user_id'];
                    $plan_id = $arm_content['arm_item_id'];

                    $arm_plan_name = $arm_content_data['plan_name'];
                    $arm_plan_type = $arm_content_data['plan_type'];
                    $arm_plan_payment_gateway = $arm_content_data['gateway'];
                    $arm_plan_amount = $arm_content_data['plan_detail']['arm_subscription_plan_amount'];
                    $arm_plan_start_date = date('Y-m-d H:i:s',$arm_content_data['start']); //phpcs:ignore
                    $arm_plan_end_date = !empty($arm_content_data['expire']) ? date('Y-m-d H:i:s',(int)$arm_content_data['expire']) : ''; //phpcs:ignore
                    $arm_plan_next_cycle_date = !empty($arm_content_data['expire']) ? date('Y-m-d H:i:s',(int)$arm_content_data['expire']) : ''; //phpcs:ignore

                    if($arm_plan_type == 'free' || $arm_plan_type =="infinite")
                    {
                        $arm_plan_end_date = '';
                        $arm_plan_next_cycle_date = '';
                    }
                    if($arm_plan_type == 'paid_finite')
                    {
                        $arm_plan_next_cycle_date = '';
                    }
                    if($arm_plan_type == 'recurring')
                    {
                        $plan_usermeta = arm_get_user_meta( $user_id, 'arm_user_plan_'.$plan_id, true );
                        $arm_plan_detail = arm_maybe_unserialize($plan_usermeta);
                        $arm_plan_next_cycle_date = !empty($arm_plan_detail['arm_next_due_payment']) ? date('Y-m-d H:i:s',$arm_plan_detail['arm_next_due_payment']) : ''; //phpcs:ignore
                    }
                    $data = array(
                        'arm_activity_plan_name'=>$arm_plan_name,
                        'arm_activity_plan_type'=>$arm_plan_type,
                        'arm_activity_payment_gateway'=>$arm_plan_payment_gateway,
                        'arm_activity_plan_amount'=>$arm_plan_amount,
                        'arm_activity_plan_start_date'=>$arm_plan_start_date,
                        'arm_activity_plan_end_date'=>$arm_plan_end_date,
                        'arm_activity_plan_next_cycle_date'=>$arm_plan_next_cycle_date
                    );
                    $wpdb->update($arm_activity_table,$data,array('arm_activity_id'=>$arm_act_id)); //phpcs:ignore
                    $total_updated_activity = (int)$total_updated_activity + 1;
                    update_option('arm_updates_cron_db_total_activity_updated',$total_updated_activity);
                }
            }
            else 
            {
                update_option('arm_updates_cron_db_activity_notice',2);
                wp_clear_scheduled_hook("arm_handle_updates_db_migrate_activity_data");
            }
        }

        function arm_handle_updates_db_migrate_subscription_data_func()
        {
            global $wpdb, $arm_member_subscriptions;

            $arm_updates_cron_db_subscription_notice = get_option('arm_updates_cron_db_subscription_notice', 2);
            if( $arm_updates_cron_db_subscription_notice != 1 || !is_object($arm_member_subscriptions) || !method_exists($arm_member_subscriptions, 'arm_migrate_user') )
            {
                return;
            }
            if( !$arm_member_subscriptions->arm_is_tables_ready() )
            {
                return;
            }

            $arm_lock_key = 'arm_subscription_migration_lock';
            if( get_transient($arm_lock_key) )
            {
                return;
            }
            set_transient($arm_lock_key, time(), 5 * MINUTE_IN_SECONDS);
            set_time_limit(0); //phpcs:ignore --Reason: Set time limit to 0 for long processing in background

            $arm_batch_limit = (int) apply_filters('arm_subscription_migration_batch_limit', 200);
            $arm_time_limit  = (int) apply_filters('arm_subscription_migration_time_limit', 40);
            $arm_started     = time();

            $arm_last_user_id = (int) get_option('arm_updates_cron_db_subscription_last_user_id', 0);
            $arm_totals = get_option('arm_updates_cron_db_subscription_totals', array());
            $arm_totals = is_array($arm_totals) ? $arm_totals : array();
            $arm_totals = array_merge(array('users' => 0, 'created' => 0, 'skipped' => 0, 'history' => 0, 'logs' => 0, 'activities' => 0, 'mismatch' => 0, 'failed' => 0), $arm_totals);

            $arm_user_ids = $wpdb->get_col( $wpdb->prepare("SELECT ID FROM `{$wpdb->users}` WHERE ID > %d ORDER BY ID ASC LIMIT %d", $arm_last_user_id, $arm_batch_limit) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery

            if( empty($arm_user_ids) )
            {
                update_option('arm_updates_cron_db_subscription_notice', 2);
                update_option('arm_updates_cron_db_subscription_completed_date', current_time('mysql'));
                wp_clear_scheduled_hook("arm_handle_updates_db_migrate_subscription_data");
                delete_transient($arm_lock_key);
                do_action('arm_after_subscription_migration_completed', $arm_totals);
                return;
            }

            foreach( $arm_user_ids as $arm_user_id )
            {
                $arm_stats = $arm_member_subscriptions->arm_migrate_user( (int) $arm_user_id );
                if( false === $arm_stats )
                {
                    $arm_totals['failed']++;
                }
                else
                {
                    foreach( $arm_stats as $arm_key => $arm_count )
                    {
                        $arm_totals[$arm_key] = ( isset($arm_totals[$arm_key]) ? (int) $arm_totals[$arm_key] : 0 ) + (int) $arm_count;
                    }
                }
                $arm_totals['users']++;
                $arm_last_user_id = (int) $arm_user_id;

                if( ( time() - $arm_started ) >= $arm_time_limit )
                {
                    break;
                }
            }

            /* Progress saved once per batch. */
            update_option('arm_updates_cron_db_subscription_last_user_id', $arm_last_user_id);
            update_option('arm_updates_cron_db_subscription_totals', $arm_totals, false);
            delete_transient($arm_lock_key);
        }

        function arm_updates_cron_db_processing_notice()
        {
            global $ARMemberLite,$arm_capabilities_global;
            $ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce
            foreach( array('arm_updates_cron_db_notice','arm_updates_cron_db_activity_notice','arm_updates_cron_db_subscription_notice') as $arm_job_option )
            {
                $arm_job_status = get_option($arm_job_option, ('arm_updates_cron_db_subscription_notice' == $arm_job_option) ? 2 : 0);
                if( $arm_job_status != 2 )
                {
                    update_option($arm_job_option,1);
                }
            }
            die();
        }

        function arm_updates_cron_db_completed_notice()
        {
            global $ARMemberLite,$arm_capabilities_global;
            $ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce
            update_option('arm_updates_cron_db_notice',2);
            update_option('arm_updates_cron_db_activity_notice',2);
            $arm_updates_cron_db_subscription_notice = get_option('arm_updates_cron_db_subscription_notice', 2);
            if( $arm_updates_cron_db_subscription_notice == 2 )
            {
                update_option('arm_updates_cron_db_initialize',0);
            }
            wp_clear_scheduled_hook("arm_handle_updates_db_migrate_data");
            wp_clear_scheduled_hook("arm_handle_updates_db_migrate_activity_data");
            die();
        }
    }
}
global $arm_updates_cron;
$arm_updates_cron = new ARM_updates_cron_Lite();