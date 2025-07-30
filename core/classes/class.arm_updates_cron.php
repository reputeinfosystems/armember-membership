<?php

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

                add_action('wp_ajax_arm_updates_cron_db_processing_notice',array($this,'arm_updates_cron_db_processing_notice'),10);
                add_action('wp_ajax_arm_updates_cron_db_completed_notice',array($this,'arm_updates_cron_db_completed_notice'),10);

                add_action('admin_init', array($this, 'arm_show_updates_cron_notice'), 1);

            }
            

        }

        function arm_show_updates_cron_notice() {
            global $arm_slugs;
            if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) { //phpcs:ignore
                if (!in_array($_REQUEST['page'], array($arm_slugs->manage_forms))) { //phpcs:ignore
                    add_action('admin_notices', array($this, 'arm_updates_cron_admin_notices'), 100);
                }
            }
        }

        function arm_updates_cron_admin_notices() {
            global $arm_slugs;

            $notice_html = '';
            $arm_allowed_slugs = (array) $arm_slugs;

            if(isset($_REQUEST['page']) && in_array($_REQUEST['page'], $arm_allowed_slugs)) //phpcs:ignore
            {
                $arm_updates_cron_db_notice = get_option('arm_updates_cron_db_notice');
                $arm_updates_cron_db_activity_notice = get_option('arm_updates_cron_db_activity_notice');

                if( empty($arm_updates_cron_db_notice) || empty($arm_updates_cron_db_activity_notice) )
                {
                    printf("<div class='notice notice-info arm_dismiss_update_db_notice' style='display:block;margin: 40px 40px 0px;border-left-color: var(--arm-pt-orange) !important;min-height:150px'><p style='font-size:calc(24px);'>". esc_html__('ARMember Database Update Required', 'armember-membership')."</p><p>ARMember has been updated! To keep things running smoothly, we have to update your database to the newest version. The database update process runs in the background and may take a little while, so please be patience.</p><button class='armemailaddbtn' style='margin-top:15px;margin-bottom:20px;'>". esc_html__('Update ARMember Database', 'armember-membership')."</button></div>");

                    printf("<div class='notice notice-info arm_dismiss_updated_db_notice' style='display:none;margin: 40px 40px 0px;border-left-color: var(--arm-pt-orange) !important;height:110px'><p style='font-size:calc(24px);'>". esc_html__('ARMember database Updation in Progress', 'armember-membership')."</p><p>ARMember has started a data updation in background. The database update process may take a little while, so please be patience.</p></div>");
                }
                else if($arm_updates_cron_db_notice=="1" || $arm_updates_cron_db_activity_notice=="1")
                {
                    printf("<div class='notice notice-info arm_dismiss_updated_db_notice' style='display:block;margin: 40px 40px 0px;border-left-color: var(--arm-pt-orange) !important;height:110px'><p style='font-size:calc(24px);'>". esc_html__('ARMember database Updation in Progress', 'armember-membership')."</p><p>ARMember has started a data updation in background. The database update process may take a little while, so please be patience.</p></div>");
                }
                else if($arm_updates_cron_db_notice=="2" || $arm_updates_cron_db_activity_notice=="2")
                {
                    printf("<div class='notice notice-info arm_dismiss_updated_data_notice' style='display:block;margin: 40px 40px 0px;border-left-color: var(--arm-pt-orange) !important;min-height:170px'><p style='font-size:calc(24px);'>". esc_html__('ARMember database updation process done', 'armember-membership')."</p><p>ARMember database update complete. Thank you for updating to the latest version!</p><button class='armemailaddbtn' style='margin-top:15px;margin-bottom:20px;'>". esc_html__('Thanks!', 'armember-membership')."</button></div>");
                }

                echo $notice_html; //phpcs:ignore
            }
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
                    $arm_user_plan_ids_value = get_user_meta($user_id,'arm_user_plan_ids',true);

                    $user_meta_value_array = array();
                    if(!empty($arm_user_plan_ids_value))
                    {
                        $user_meta_value_arr = maybe_unserialize($arm_user_plan_ids_value);
                        if(!empty($user_meta_value_arr) && is_array($user_meta_value_arr))
                        {
                            foreach($user_meta_value_arr as $arm_user_plan_id)
                            {
                                $user_meta_value_array[] = (int)$arm_user_plan_id;
                            }
                        }
                    }
                    $user_meta_value_array = maybe_serialize($user_meta_value_array);

                    $arm_user_suspended_plan_ids_value = get_user_meta($user_id,'arm_user_suspended_plan_ids',true);

                    $user_suspended_plan_meta_value_array = array();
                    if(!empty($arm_user_suspended_plan_ids_value))
                    {
                        $user_suspended_plan_meta_value_arr = maybe_unserialize($arm_user_suspended_plan_ids_value);
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
                    $arm_content_data = maybe_unserialize($arm_content['arm_content']);

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
                        $plan_usermeta = get_user_meta( $user_id, 'arm_user_plan_'.$plan_id, true );
                        $arm_plan_detail = maybe_unserialize($plan_usermeta);
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

        function arm_updates_cron_db_processing_notice()
        {
            global $ARMemberLite,$arm_capabilities_global;
            $ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce
            update_option('arm_updates_cron_db_notice',1);
            update_option('arm_updates_cron_db_activity_notice',1);
            die();
        }

        function arm_updates_cron_db_completed_notice()
        {
            global $ARMemberLite,$arm_capabilities_global;
            $ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce
            update_option('arm_updates_cron_db_notice',2);
            update_option('arm_updates_cron_db_activity_notice',2);
            update_option('arm_updates_cron_db_initialize',0);
            wp_clear_scheduled_hook("arm_handle_updates_db_migrate_data");
            wp_clear_scheduled_hook("arm_handle_updates_db_migrate_activity_data");
            die();
        }
    }
}
global $arm_updates_cron;
$arm_updates_cron = new ARM_updates_cron_Lite();