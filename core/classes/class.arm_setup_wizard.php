<?php
if (!class_exists('ARM_setup_Wizard_Lite')) {
    class ARM_setup_Wizard_Lite {
        function __construct() {
            add_action('wp_ajax_arm_complete_setup_data', array($this, 'arm_complete_setup_data'), 10, 2);
            add_action('wp_ajax_skip_setup_action',array($this,'skip_setup_action'),10,2);
        }
        function skip_setup_action()
        {
            global $wp,$wpdb,$ARMemberLite,$arm_slugs, $arm_capabilities_global;
	    
            $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1'); //phpcs:ignore --Reason:Verifying nonce

            update_option('arm_lite_is_wizard_complete',1);
            $redirect_url =  admin_url('admin.php?page=' . $arm_slugs->manage_members); 
            $response = array('type'=>'success','msg'=>esc_html__('ARMember setup wizard completed','armember-membership'),'redirect_url'=>$redirect_url);
            echo wp_json_encode($response);
            die;
        }
        /** WIZARD SETUPS STARTS*/

        function arm_complete_setup_data(){
            global $wp,$wpdb,$ARMemberLite,$arm_global_settings,$arm_payment_gateways,$arm_subscription_plans,$ARMember,$arm_access_rules,$arm_capabilities_global;
            $response = array('type'=>'error','msg'=>esc_html__('Something went wrong! please try again later','armember-membership'));
            $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1'); //phpcs:ignore --Reason:Verifying nonce

            $posted_data = $_POST; //phpcs:ignore
            $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
            $general_settings = $arm_global_settings->global_settings;
            // $all_general_settings = $all_global_settings['general_settings'];
            $default_rules = $arm_access_rules->arm_get_default_access_rules();
            if(empty($default_rules))
            {
                $default_rules = array();
            }
            $payment_gateways = get_option('arm_payment_gateway_settings');
            $general_settings['restrict_site_access'] = !empty($posted_data['arm_restrict_entire_website']) ? inval($posted_data['arm_restrict_entire_website']) : '';
            $default_rules['arm_allow_content_listing'] = !empty($posted_data['arm_post_page_listing']) ? $posted_data['arm_post_page_listing'] : '';
            $general_settings['user_register_verification'] = $posted_data['user_register_verification'];
            $general_settings['arm_new_signup_status'] = ($posted_data['user_register_verification'] != 'auto') ? 3 : 1;
            $general_settings['arm_anonymous_data'] = !empty($posted_data['arm_anonymous_data'])? intval($posted_data['arm_anonymous_data']) : 0;
            $general_settings['paymentcurrency'] = sanitize_text_field( $posted_data['paymentcurrency'] );
            
            $all_global_settings['general_settings'] = $general_settings;

            update_option('arm_global_settings', $all_global_settings);
            update_option('arm_default_rules', $default_rules);

            //payment gateways
            
            $paypal_merchant_email = !empty($posted_data['arm_paypal_merchant_email']) ? $posted_data['arm_paypal_merchant_email'] : '';
            $paypal_api_username = !empty($posted_data['arm_paypal_merchant_api_username']) ? $posted_data['arm_paypal_merchant_api_username'] : ''; 
            $paypal_api_password = !empty($posted_data['arm_paypal_merchant_api_password']) ? $posted_data['arm_paypal_merchant_api_password'] : ''; 
            $paypal_api_signature = !empty($posted_data['arm_paypal_merchant_api_signature']) ? $posted_data['arm_paypal_merchant_api_signature'] : ''; 
            $pay_gate_settings = $all_gateways = $payment_mode_arr =  array();
            $payment_gateway_data = $posted_data['arm_selected_payment_gateway'];
            foreach($payment_gateway_data as $payment_gateway => $payment_data)
            {
                $pg_setting = array();
                if($payment_gateway == 'paypal' && !empty($payment_data['status']))
                {                   
                    $pg_setting['status']  = !empty($payment_data['status'])? intval($payment_data['status']) : 0;
                    $pg_setting['paypal_payment_mode'] = $payment_data['payment_method'];
                    $pg_setting['paypal_merchant_email'] = $payment_data['merchant_email'];
                    $paypal_api_username = $payment_data['api_username'];
                    $paypal_merchant_email = $payment_data['api_password'];
                    $paypal_merchant_signature = $payment_data['api_signature'];
                    if($payment_data['payment_method'] == 'sandbox')
                    {                        
                        $pg_setting['sandbox_api_username'] = $paypal_api_username;
                        $pg_setting['sandbox_api_password'] = $paypal_merchant_email;
                        $pg_setting['sandbox_api_signature'] = $paypal_merchant_signature;
                    }
                    else
                    {
                        $pg_setting['paypal_payment_mode'] = 'live';
                        $pg_setting['live_api_username'] = $paypal_api_username;
                        $pg_setting['live_api_password'] = $paypal_merchant_email;
                        $pg_setting['live_api_signature'] = $paypal_merchant_signature;
                    }
                }
                if($payment_gateway == 'bank_transfer' && !empty($payment_data['status']))
                {
                    $pg_setting['status'] = !empty($payment_data['status']) ? intval($payment_data['status']) : 0;
                    $transaction_id = !empty($payment_data['transaction_id']) ? 1 : 0 ;
                    $bank_name = !empty($payment_data['bank_name']) ? 1 : 0 ;
                    $account_name = !empty($payment_data['account_name']) ? 1 : 0 ;
                    $additional_info = !empty($payment_data['additional_info']) ? 1 : 0 ;
                    $transfer_mode = !empty($payment_data['transaction_id']) ? 1 : 0 ;
                    $digital_transfer_label =!empty($payment_data['digital_transfer_label']) ? $payment_data['digital_transfer_label'] : '';
                    $cheque_label =!empty($payment_data['cheque_label']) ? $payment_data['cheque_label'] : '';
                    $cash_label =!empty($payment_data['cash_label']) ? $payment_data['cash_label'] : '';
                    $pg_setting['fields']= array(
                        'transaction_id' => $transaction_id,
                        'bank_name' => $bank_name,
                        'account_name' => $account_name,
                        'additional_info' => $additional_info,
                        'transfer_mode' => $transfer_mode,
                        'transfer_mode_option'=> $payment_data['transfer_mode_option'],
                        'transfer_mode_option_label' => array(
                            'bank_transfer'=>$digital_transfer_label,
                            'cheque'=>$cheque_label,
                            'cash'=>$cash_label
                        ),
                    );
                    
                }

                $pay_gate_settings[$payment_gateway] = $pg_setting;
                if(!empty($payment_data['status']) && $payment_data['status'] == 1)
                {
                    array_push($all_gateways,$payment_gateway);
                    $payment_mode = 'manual_subscription';
                    if($payment_gateway=='paypal')
                    {
                        $payment_mode = 'both';
                    }
                    $payment_mode_arr[$payment_gateway] = $payment_mode;
                }
                
                
            }
            
            update_option('arm_payment_gateway_settings',$pay_gate_settings);
            //membership plans
            $arm_membership_plan_name = $posted_data['arm_membership_plan_name'];
            $subscription_type = $posted_data['arm_subscription_plan_type'];
            $subscription_amount = !empty($posted_data['arm_membership_plan_amount'])? $posted_data['arm_membership_plan_amount'] : 0;
            $data = array('action'=>'add','plan_name'=>$arm_membership_plan_name,'plan_status'=>1,'arm_subscription_plan_type'=>$subscription_type,'arm_subscription_plan_amount'=>$subscription_amount);
            
            $plan_id = $this->create_subscription_plans($data);

            //setups
            $setup_name = $posted_data['arm_membership_setup_name'];
            $setup_modules = array(
                'modules'=>array(
                    'plans' => array($plan_id),
                    'forms' => 101, 
                    'gateways' => $all_gateways,
                    'payment_mode' => $payment_mode_arr,
                    'coupon'=>0,
                    'plans_order' => array($plan_id => 1),
                    'gateways_order' => array('paypal' => 1,'stripe' => 2,'authorize_net' => 3,'2checkout' => 4,'bank_transfer' => 5),
                ),
                'style'=>array(
                    'plan_skin' => 'skin1',
                    'plan_area_position' => 'before',
                    'gateway_skin' => 'radio',
                    'content_width' => 800,
                    'form_position' => 'center',
                    'font_family' => 'Poppins',
                    'title_font_size' => 20,
                    'title_font_bold' => 1,
                    'title_font_italic' => '',
                    'title_font_decoration' => '',
                    'description_font_size' => 15,
                    'description_font_bold' => 0,
                    'description_font_italic' =>'' ,
                    'description_font_decoration' => '',
                    'price_font_size' => 28,
                    'price_font_bold' => 0,
                    'price_font_italic' => '',
                    'price_font_decoration' => '',
                    'summary_font_size' => 16,
                    'summary_font_bold' => 0,
                    'summary_font_italic' => '',
                    'summary_font_decoration' => '',
                    'plan_title_font_color' => '#2C2D42',
                    'plan_desc_font_color' => '#555F70',
                    'price_font_color' => '#2C2D42',
                    'summary_font_color' => '#555F70',
                    'selected_plan_title_font_color' => '#005AEE',
                    'selected_plan_desc_font_color' => '#2C2D42',
                    'selected_price_font_color' => '#FFFFFF',
                    'bg_active_color' => '#005AEE',
                ),
                'plans_columns'=>3,
                'selected_plan'=>$plan_id,
                'cycle_columns'=>1,
                'gateways_columns'=>1,
                'custom_css'=>'',
            );
            $setup_labels = array('button_labels' => array('submit' => 'Submit','coupon_title' => 'Enter Coupon Code','coupon_button' => 'Apply','next' => 'Next','previous' => 'Previous'),
            'member_plan_field_title' => 'Select Membership Plan',
            'payment_cycle_section_title' => 'Select Your Payment Cycle',
            'payment_cycle_field_title' => 'Select Your Payment Cycle',
            'payment_section_title' => 'Select Your Payment Gateway',
            'payment_gateway_field_title' => 'Select Your Payment Gateway',
            'payment_gateway_labels' => array(
                    'paypal' => 'Paypal',
                    'stripe' => 'Stripe',
                    'authorize_net' => 'Authorize.net',
                    '2checkout' => '2Checkout',
                    'bank_transfer' => 'Bank Transfer',
            ),
        
            'payment_mode_selection' => 'How you want to pay?',
            'automatic_subscription' => 'Auto Debit Payment',
            'semi_automatic_subscription' => 'Manual Payment',
            'credit_card_logos' => '',
            'summary_text' => '<div>Payment Summary</div><br/><div>Your currently selected plan : <strong>[PLAN_NAME]</strong>,  Plan Amount : <strong>[PLAN_AMOUNT]</strong> </div><div>Coupon Discount Amount : <strong>[DISCOUNT_AMOUNT]</strong>, Final Payable Amount: <strong>[PAYABLE_AMOUNT]</strong> </div>'
        );
            $db_data = array(
                'arm_setup_name' => $setup_name,
                'arm_setup_modules' => maybe_serialize($setup_modules),
                'arm_setup_labels' => maybe_serialize($setup_labels),
                'arm_setup_type' => 0
            );
            $db_data['arm_status'] = 1;
            $db_data['arm_created_date'] = current_time( 'mysql' );
            /* Insert Form Fields. */
            $wpdb->insert($ARMemberLite->tbl_arm_membership_setup, $db_data); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $setup_id = $wpdb->insert_id;
            /* Action After Adding Setup Details */
            do_action('arm_saved_membership_setup', $setup_id, $db_data);

            $create_setup_page = array(
                    'post_title' => 'Setup',
                    'post_name' => 'setup',
                    'post_content' => '[arm_setup id="' . $setup_id . '" hide_title="1"]',
                    'post_status' => 'publish',
                    'post_parent' => 0,
                    'post_author' => 1,
                    'post_type' => 'page',
            );
            $page_id = wp_insert_post($create_setup_page);
            $setup_page_url = get_permalink($page_id);


            
            //access_rules IDS
            $arm_allowed_access_pages= !empty($posted_data['arm_access_rules_pages_ids']) ? $posted_data['arm_access_rules_pages_ids'] : '';
            if(!empty($arm_allowed_access_pages))
            {
                foreach($arm_allowed_access_pages as $page_id)
                {
                    update_post_meta($page_id,'arm_access_plan',$plan_id);
                }
            }
            update_option('arm_lite_is_wizard_complete',1);
            $response = array('type'=>'success','msg'=>esc_html__('ARMember setup wizard completed','armember-membership'),'setup_url'=>$setup_page_url);
            echo wp_json_encode($response);
            die;
        }

        function create_subscription_plans($posted_data=array())
        {
            global $wp,$wpdb,$ARMember,$arm_global_settings,$ARMemberLite;
            if (isset($posted_data) && !empty($posted_data) && in_array($posted_data['action'], array('add', 'update'))) {
                
                $plan_name = (!empty($posted_data['plan_name'])) ? sanitize_text_field($posted_data['plan_name']) : esc_html__('Untitled Plan', 'armember-membership');
                $plan_description = (!empty($posted_data['plan_description'])) ? $posted_data['plan_description'] : '';
                $plan_status = (!empty($posted_data['plan_status']) && $posted_data['plan_status'] != 0) ? 1 : 0;
                $plan_role = (!empty($posted_data['plan_role'])) ? sanitize_text_field($posted_data['plan_role']) : get_option('default_role');
                $plan_type = (!empty($posted_data['arm_subscription_plan_type'])) ? sanitize_text_field($posted_data['arm_subscription_plan_type']) : 'free';
                $payment_type = $plan_amount = $stripe_plan = '';
                $plan_options = $plan_payment_gateways = array();
                if ($plan_type != 'free') {
                    $plan_options = (!empty($posted_data['arm_subscription_plan_options'])) ? $posted_data['arm_subscription_plan_options'] : array();

                    $plan_options['access_type'] = (!empty($plan_options['access_type'])) ? $plan_options['access_type'] : 'lifetime';
                    $plan_options['payment_type'] = (!empty($plan_options['payment_type'])) ? $plan_options['payment_type'] : 'one_time';

                    if ($plan_type == 'paid_finite') {
                        $plan_options['access_type']='finite';
                        $plan_options['expiry_type'] = 'joined_date_expiry';
                        $plan_options['eopa'] =array(
                            'days' => 1,
                            'weeks' => 1,
                            'months' => 1,
                            'years' => 1,
                            'type' => 'M'
                        );
                    } else {
                        unset($plan_options['expiry_type']);
                        unset($plan_options["expiry_date"]);
                        unset($plan_options["eopa"]);
                    }

                    if ($plan_type == 'paid_infinite') {
                        unset($plan_options['upgrade_action']);
                        unset($plan_options['downgrade_action']);
                        unset($plan_options['enable_upgrade_downgrade_action']);
                        unset($plan_options['grace_period']);
                        unset($plan_options['eot']);
                        unset($plan_options['upgrade_plans']);
                        unset($plan_options['downgrade_plans']);
                    }

                    if ($plan_options['payment_type'] == "one_time") {
                        $plan_options['trial'] = array();
                    }

                    $plan_amount = (!empty($posted_data['arm_subscription_plan_amount'])) ? $posted_data['arm_subscription_plan_amount'] : 0;
                    
                    if ($plan_type == 'recurring') {
                        $plan_options['access_type']='finite';
                        $plan_options['payment_type'] = 'subscription';
                        
                        $manual_billing_start = (!empty($plan_options['recurring'])) ? $plan_options['recurring']['manual_billing_start'] : 'transaction_day';
                        $plan_options['payment_cycles'][0] = array(
                            'cycle_key'=>'arm0',
                            'cycle_label' => $plan_name,
                            'cycle_amount'=>$plan_amount,
                            'billing_cycle'=>1,
                            'billing_type'=>'M',
                            'recurring_time'=>'infinite',
                            'payment_cycle_order'=>1
                        );
                        $plan_options['recurring'] = array(
                            'days' => 1,
                            'months' => 1,
                            'years' => 1,
                            'type' => 'M',
                            'time' => 'infinite',
                            'manual_billing_start' => 'transaction_day',
                        );
                        $plan_options['cancel_action'] = 'block';
                        $plan_options['cancel_plan_action'] = 'on_expire';
                        $plan_options['eot'] = 'block';
                        $plan_options['grace_period'] = array(
                                'end_of_term' => 0,
                                'failed_payment' => 2
                        );
                    } else {
                        unset($plan_options['payment_cycles']);
                        unset($plan_options['recurring']);
                        unset($plan_options['trial']);
                        unset($plan_options['cancel_action']);
                        unset($plan_options['cancel_plan_action']);
                        unset($plan_options['payment_failed_action']);
                    }
                }
                $plan_options['pricetext'] = isset($posted_data['arm_subscription_plan_options']['pricetext']) ? $posted_data['arm_subscription_plan_options']['pricetext'] : esc_html__('Free Membership', 'armember-membership');
                $plan_options = apply_filters('arm_befor_save_field_membership_plan', $plan_options, $posted_data);
                $subscription_plans_data = array(
                    'arm_subscription_plan_name' => $plan_name,
                    'arm_subscription_plan_description' => $plan_description,
                    'arm_subscription_plan_status' => $plan_status,
                    'arm_subscription_plan_type' => $plan_type,
                    'arm_subscription_plan_options' => maybe_serialize($plan_options),
                    'arm_subscription_plan_amount' => $plan_amount,
                    'arm_subscription_plan_role' => $plan_role,
                );
                if ($posted_data['action'] == 'add') {
                    $subscription_plans_data['arm_subscription_plan_created_date'] = current_time( 'mysql' ); 
                    $wpdb->insert($ARMemberLite->tbl_arm_subscription_plans, $subscription_plans_data); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                    $plan_id = $wpdb->insert_id;
                    //Action After Adding Plan
                    do_action('arm_saved_subscription_plan', $plan_id, $subscription_plans_data);
                    $inherit_plan_id = isset($posted_data['arm_inherit_plan_rules']) ? intval($posted_data['arm_inherit_plan_rules']) : 0;
                    if (!empty($plan_id) && $plan_id != 0 && !empty($inherit_plan_id) && $inherit_plan_id != 0) {
                        $arm_access_rules->arm_inherit_plan_rules($plan_id, $inherit_plan_id);
                    }
                    return $plan_id;
                }
            }
        }
        /** WIZARD SETUP ENDS */
    }
}
global $arm_lite_wizard_class;
$arm_lite_wizard_class = new ARM_setup_Wizard_Lite();