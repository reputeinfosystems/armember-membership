<?php

if ( ! class_exists( 'ARM_payment_gateways_Lite' ) ) {

	class ARM_payment_gateways_Lite {

		var $currency;

		function __construct() {
			global $wpdb, $ARMemberLite, $arm_slugs;
			add_action( 'wp_ajax_arm_update_pay_gate_settings', array( $this, 'arm_update_pay_gate_settings' ) );
			add_filter( 'arm_change_user_meta_before_save', array( $this, 'arm_filter_form_posted_plan_data' ), 10, 3 );
			add_action( 'wp_ajax_arm_check_currency_status', array( $this, 'arm_check_currency_status' ) );
			$this->currency = array(
				'paypal'        => $this->arm_paypal_currency_symbol(),
				'bank_transfer' => $this->arm_bank_transfer_currency_symbol(),
			);
			add_action('wp_ajax_arm_view_payment_debug_log', array($this, 'arm_view_payment_debug_log'));
			add_action('wp_ajax_arm_view_general_debug_log', array($this, 'arm_view_general_debug_log'));

            add_action('wp_ajax_arm_download_payment_debug_log', array($this, 'arm_download_payment_debug_log'));
            add_action('wp_ajax_arm_download_general_debug_log', array($this, 'arm_download_general_debug_log'));

			add_action('admin_init', array( $this, 'arm_debug_log_download_file') );
		}

		function arm_debug_log_download_file(){
            if( !empty( $_REQUEST['arm_action'] ) && 'download_log' == $_REQUEST['arm_action'] ){ //phpcs:ignore
                global $wpdb, $ARMemberLite, $arm_global_settings, $arm_capabilities_global;
                
                $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');

                $filename = !empty($_REQUEST['file']) ? basename( sanitize_file_name( $_REQUEST['file']) ) : ''; //phpcs:ignore
                if(!empty($filename))
                {
                    $file_path = MEMBERSHIPLITE_UPLOAD_DIR . '/' . $filename;

                    $allowexts = array( "txt", "zip" );

                    $file_name_arm = substr($filename, 0,3);

                    $checkext = explode(".", $filename);
                    $ext = strtolower( $checkext[count($checkext) - 1] );

                    if(!empty($ext) && in_array($ext, $allowexts) && !empty($filename) && file_exists($file_path))
                    {
                        ignore_user_abort();
                        $now = date("D, d M Y H:i:s"); //phpcs:ignore
                        header("Expires: Tue, 03 Jul 2020 06:00:00 GMT");
                        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
                        header("Last-Modified: {$now} GMT");
                        header("Content-Type: application/force-download");
                        header("Content-Type: application/octet-stream");
                        header("Content-Type: application/download");
                        header("Content-Disposition: attachment;filename={$filename}");
                        header("Content-Transfer-Encoding: binary");

                        readfile($file_path); //phpcs:ignore

                        unlink( $file_path ); //phpcs:ignore

                        $arm_txt_file_name = str_replace('.zip', '.txt', $filename);
                        $arm_txt_file_path = MEMBERSHIPLITE_UPLOAD_DIR . '/' . $arm_txt_file_name;
                        if(file_exists($arm_txt_file_path))
                        {
                            unlink($arm_txt_file_path); //phpcs:ignore
                        }

                        die;
                    }
                }
            }
        }

		function arm_view_payment_debug_log()
        {
            global $wpdb, $ARMemberLite, $arm_global_settings, $arm_capabilities_global;
            $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');//phpcs:ignore --Reason:Verifying nonce
            $arm_payment_debug_log_html = '';
            if(!empty($_POST) && !empty($_POST['arm_debug_log_selector']))//phpcs:ignore
            {
                $posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST ); //phpcs:ignore
                $tbl_arm_debug_payment_log = $ARMemberLite->tbl_arm_debug_payment_log;

                $arm_payment_debug_log_selector = $posted_data['arm_debug_log_selector'];
                $date_time_format = $arm_global_settings->arm_get_wp_date_time_format();

                $current_page = !empty($posted_data['page']) ? $posted_data['page'] : 1;
                $perPage = !empty($posted_data['per_page']) ? intval($posted_data['per_page']) : 25;

                $offset = 0;
                if (!empty($current_page) && $current_page > 1) {
                    $offset = ($current_page - 1) * $perPage;
                }

                $totalRecord = $wpdb->get_var( $wpdb->prepare("SELECT COUNT('arm_payment_log_id') FROM `" . $tbl_arm_debug_payment_log . "` WHERE `arm_payment_log_gateway` = %s AND `arm_payment_log_status`= %d ORDER BY arm_payment_log_id DESC",$arm_payment_debug_log_selector,1) ); //phpcs:ignore --Reason $tbl_arm_debug_payment_log is a table name

                $armPaymentDebugLimit = (!empty($perPage)) ? " LIMIT $offset, $perPage " : "";

                $arm_payment_debug_log_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $tbl_arm_debug_payment_log . "` WHERE `arm_payment_log_gateway` = %s AND `arm_payment_log_status`=%d ORDER BY arm_payment_log_id DESC {$armPaymentDebugLimit}", $arm_payment_debug_log_selector, 1), ARRAY_A); //phpcs:ignore --Reason $tbl_arm_debug_payment_log is a table name
                    $arm_payment_debug_log_html .= '<div class="arm_payment_debug_log_container">';
                $arm_payment_debug_log_html .= '<table class="form-table arm_member_last_subscriptions_table">';
                $arm_payment_debug_log_html .= '<tr>';
                $arm_payment_debug_log_html .= '<td><b>'.esc_html__('Log ID', 'armember-membership').'</b></td>';
                $arm_payment_debug_log_html .= '<td><b>'.esc_html__('Log Name', 'armember-membership').'</b></td>';
                $arm_payment_debug_log_html .= '<td class="arm_debug_log_raw_data"><b>'.esc_html__('Log Data', 'armember-membership').'</b></td>';
                $arm_payment_debug_log_html .= '<td><b>'.esc_html__('Log Added Date', 'armember-membership').'</b></td>';
                $arm_payment_debug_log_html .= '</tr>';


                foreach($arm_payment_debug_log_data as $arm_payment_debug_log_key => $arm_payment_debug_log_val)
                {
                    $arm_payment_debug_log_html .= '<tr>';

                    $arm_payment_debug_log_html .= '<td>'.$arm_payment_debug_log_val['arm_payment_log_id'].'</td>';

                    $arm_payment_debug_log_html .= '<td>'.$arm_payment_debug_log_val['arm_payment_log_event'].'</td>';

                    //$arm_payment_debug_log_html .= '<td>'.$arm_payment_debug_log_val['arm_payment_log_gateway'].'</td>';

                    /*$arm_payment_debug_log_html .= '<td>'.$arm_payment_debug_log_val['arm_payment_log_event'].'</td>';

                    $arm_payment_debug_log_html .= '<td>'.$arm_payment_debug_log_val['arm_payment_log_event_from'].'</td>';*/

                    $arm_payment_debug_log_html .= '<td class="arm_debug_log_raw_data">'.htmlspecialchars(utf8_encode($arm_payment_debug_log_val['arm_payment_log_raw_data'])).'</td>';

                    $arm_created_date = date_i18n($date_time_format, strtotime($arm_payment_debug_log_val['arm_payment_log_added_date']));
                    $arm_payment_debug_log_html .= '<td>'.$arm_created_date.'</td>';

                    $arm_payment_debug_log_html .= '</tr>';
                }

                if($totalRecord <= 0)
                {
                    $total_column = 4;
                    $arm_payment_debug_log_html .= '<tr>';
                    $arm_payment_debug_log_html .= '<td colspan="'.esc_attr($total_column).'" class="arm_text_align_center" >' . esc_html__('No debug logs found.', 'armember-membership') . '</td>';
                    $arm_payment_debug_log_html .= '</tr>';
                }

                $arm_payment_debug_log_html .= '</table>';
                $arm_payment_debug_log_html .= '</div>';

                $arm_payment_debug_log_html .= '<div class="arm_membership_history_pagination_block">';
                $historyPaging = $arm_global_settings->arm_get_paging_links($current_page, $totalRecord, $perPage);
                $arm_payment_debug_log_html .= '<div class="arm_membership_history_paging_container">' . $historyPaging . '</div>';
                $arm_payment_debug_log_html .= '</div>';

            }
            echo $arm_payment_debug_log_html; //phpcs:ignore;
            exit();
        }

		function arm_view_general_debug_log()
        {
            global $wpdb, $ARMemberLite, $arm_global_settings, $arm_capabilities_global;
            $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');//phpcs:ignore --Reason:Verifying nonce
            $arm_general_debug_log_html = '';
            if(!empty($_POST) && !empty($_POST['arm_debug_log_selector']))//phpcs:ignore
            {
                $tbl_arm_debug_general_log = $ARMemberLite->tbl_arm_debug_general_log;
                $posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST ); //phpcs:ignore
                $arm_general_debug_log_selector = $posted_data['arm_debug_log_selector'];

                $arm_general_debug_log_selector_search = "";

                if($arm_general_debug_log_selector!='cron' && $arm_general_debug_log_selector != 'email') {
                    $arm_general_debug_log_selector_search = $wpdb->prepare(" `arm_general_log_event`!=%s AND `arm_general_log_event`!=%s ",'cron','email');
                }
                else {
                    $arm_general_debug_log_selector_search = $wpdb->prepare(" `arm_general_log_event` =%s ",$arm_general_debug_log_selector);
                }
                
                $date_time_format = $arm_global_settings->arm_get_wp_date_time_format();

                $current_page = !empty($posted_data['page']) ? $posted_data['page'] : 1;
                $perPage = !empty($posted_data['per_page']) ? intval($posted_data['per_page']) : 25;

                $offset = 0;
                if (!empty($current_page) && $current_page > 1) {
                    $offset = ($current_page - 1) * $perPage;
                }

                $totalRecord = $wpdb->get_var("SELECT COUNT('arm_general_log_id') FROM `" . $tbl_arm_debug_general_log . "` WHERE {$arm_general_debug_log_selector_search} ORDER BY arm_general_log_id DESC"); //phpcs:ignore --Reason $tbl_arm_debug_general_log is a table name

                $arm_general_debug_log_html .= '<div class="arm_general_debug_log_container">';
                $arm_general_debug_log_html .= '<table class="form-table arm_member_last_subscriptions_table" width="100%">';
                $arm_general_debug_log_html .= '<tr>';
                    $arm_general_debug_log_html .= '<td><b>'.esc_html__('Log ID', 'armember-membership').'</b></td>';
                    $arm_general_debug_log_html .= '<td><b>'.esc_html__('Log Name', 'armember-membership').'</b></td>';
                    $arm_general_debug_log_html .= '<td class="arm_debug_log_raw_data"><b>'.esc_html__('Log Data', 'armember-membership').'</b></td>';
                    $arm_general_debug_log_html .= '<td><b>'.esc_html__('Log Added Date', 'armember-membership').'</b></td>';
                $arm_general_debug_log_html .= '</tr>';
                
                if($totalRecord>0)
                {
                    $armgeneralDebugLimit = (!empty($perPage)) ? " LIMIT $offset, $perPage " : "";

                    $arm_general_debug_log_data = $wpdb->get_results("SELECT * FROM `" . $tbl_arm_debug_general_log . "` WHERE {$arm_general_debug_log_selector_search} ORDER BY arm_general_log_id DESC {$armgeneralDebugLimit}", ARRAY_A); //phpcs:ignore --Reason $tbl_arm_debug_general_log is a table name
                    
                    foreach($arm_general_debug_log_data as $arm_general_debug_log_key => $arm_general_debug_log_val)
                    {
                        $arm_general_debug_log_val_raw_data = $arm_general_debug_log_val['arm_general_log_raw_data'];
                        $arm_general_debug_log_val_raw_data = htmlspecialchars(utf8_encode($arm_general_debug_log_val_raw_data));
                        if($arm_general_debug_log_val['arm_general_log_event']=='email')
                        {
                            $arm_general_debug_log_val_raw_data = str_replace(array("{ARMNL}"), "<br />", $arm_general_debug_log_val_raw_data);
                        }

                        $arm_general_debug_log_html .= '<tr>';

                            $arm_general_debug_log_html .= '<td>'.$arm_general_debug_log_val['arm_general_log_id'].'</td>';
                            $arm_general_debug_log_html .= '<td>'.$arm_general_debug_log_val['arm_general_log_event_name'].'</td>';
                            $arm_general_debug_log_html .= '<td class="arm_debug_log_raw_data">'.$arm_general_debug_log_val_raw_data.'</td>';

                            $arm_created_date = date_i18n($date_time_format, strtotime($arm_general_debug_log_val['arm_general_log_added_date']));
                            $arm_general_debug_log_html .= '<td>'.$arm_created_date.'</td>';

                        $arm_general_debug_log_html .= '</tr>';
                    }

                }
                else 
                {
                    $total_column = 4;
                    $arm_general_debug_log_html .= '<tr>';
                        $arm_general_debug_log_html .= '<td colspan="'.esc_attr($total_column).'" class="arm_text_align_center" >' . esc_html__('No debug logs found.', 'armember-membership') . '</td>';
                    $arm_general_debug_log_html .= '</tr>';
                }

                $arm_general_debug_log_html .= '</table>';
                $arm_general_debug_log_html .= '</div>';

                $arm_general_debug_log_html .= '<div class="arm_membership_history_pagination_block">';
                    $historyPaging = $arm_global_settings->arm_get_paging_links($current_page, $totalRecord, $perPage);
                    $arm_general_debug_log_html .= '<div class="arm_membership_history_paging_container">' . $historyPaging . '</div>';
                $arm_general_debug_log_html .= '</div>';

            }
            echo $arm_general_debug_log_html; //phpcs:ignore
            exit();
        }

		function arm_download_general_debug_log(){
            global $wpdb, $ARMemberLite, $arm_global_settings, $arm_capabilities_global;
            $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');//phpcs:ignore --Reason:Verifying nonce
            $arm_download_url = '';
            if(!empty($_POST['arm_download_key']))//phpcs:ignore
            {
                $posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST ); //phpcs:ignore
                $tbl_arm_debug_general_log = $ARMemberLite->tbl_arm_debug_general_log;
                $arm_download_key = $posted_data['arm_download_key'];
                $arm_selected_download_duration = !empty($posted_data['selected_download_duration']) ? $posted_data['selected_download_duration'] : 'all';

                $arm_custom_duration_start_date = !empty($posted_data['arm_filter_pstart_date']) ? $posted_data['arm_filter_pstart_date'] : '';
                $arm_custom_duration_end_date = !empty($posted_data['arm_filter_pend_date']) ? $posted_data['arm_filter_pend_date'] : '';

                $arm_debug_general_log_where_cond = "";
                if(!empty($arm_custom_duration_start_date) && !empty($arm_custom_duration_end_date) && $arm_selected_download_duration == "custom")
                {
                    $arm_custom_duration_start_date = date('Y-m-d 00:00:00', strtotime($arm_custom_duration_start_date)); //phpcs:ignore

                    $arm_custom_duration_end_date = date('Y-m-d 23:59:59', strtotime($arm_custom_duration_end_date)); //phpcs:ignore

                    $arm_debug_general_log_where_cond = $wpdb->prepare(" AND (arm_general_log_added_date >= %s AND arm_general_log_added_date <= %s)",$arm_custom_duration_start_date,$arm_custom_duration_end_date);
                }
                else if(!empty($arm_selected_download_duration) && $arm_selected_download_duration != "custom")
                {
                    $arm_last_selected_days = date('Y-m-d', strtotime('-'.$arm_selected_download_duration.' days')); //phpcs:ignore

                    $arm_debug_general_log_where_cond = $wpdb->prepare(" AND (arm_general_log_added_date >= %s)",$arm_last_selected_days);
                }


                $arm_general_debug_log_selector_search = "";
                if($arm_download_key!='cron' &&  $arm_download_key!='email') 
                {
                    $arm_general_debug_log_selector_search = $wpdb->prepare(" `arm_general_log_event`!=%s AND `arm_general_log_event`!=%s ",'cron','email');
                }
                else 
                {
                    $arm_general_debug_log_selector_search = $wpdb->prepare(" `arm_general_log_event` =%s",$arm_download_key);
                }

                $total_log_data = $wpdb->get_var($wpdb->prepare("SELECT count(arm_general_log_id) as total_records FROM `" . $tbl_arm_debug_general_log . "` WHERE {$arm_general_debug_log_selector_search} ".$arm_debug_general_log_where_cond." ORDER BY arm_general_log_id DESC") ); //phpcs:ignore --Reason $tbl_arm_debug_general_log is a table name
                
                /*if($total_log_data>10000)
                {
                   $total_log_data = 10000; 
                }*/

                $arm_debug_log_data = $arm_debug_log_arr = array();
                $chunk_data_var = 1000;
                if(!empty($total_log_data))
                {
                    $totaloccurance = ceil($total_log_data/$chunk_data_var);
                    $startfrom = 0-$chunk_data_var;
                    $endto = 0;
                    for($cntr=0;$cntr<$totaloccurance;$cntr++)
                    {
                        $arm_debug_log_arr = array();
                        $startfrom += $chunk_data_var;
                        $endto += $chunk_data_var;

                        //echo '<br>==>'.$startfrom.', '.$endto.'|';

                        //echo "<pre><br>SELECT * FROM `" . $tbl_arm_debug_general_log . "` WHERE {$arm_general_debug_log_selector_search} ORDER BY arm_general_log_id DESC LIMIT ".$startfrom.", ".$endto;

                        $arm_debug_log_arr = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $tbl_arm_debug_general_log . "` WHERE {$arm_general_debug_log_selector_search} ORDER BY arm_general_log_id DESC LIMIT %d,%d", $startfrom, $endto ), ARRAY_A); //phpcs:ignore --Reason $tbl_arm_debug_general_log is a table name

                        $arm_debug_log_data = array_merge($arm_debug_log_data,$arm_debug_log_arr);
                    }
                }

                $arm_download_data = wp_json_encode($arm_debug_log_data);

                if( !function_exists('WP_Filesystem' ) )
                {
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                }
                WP_Filesystem();
                global $wp_filesystem;

                $arm_debug_log_file_name = "arm_debug_logs_" . $arm_download_key."_".$arm_selected_download_duration ;
                $result = $wp_filesystem->put_contents( MEMBERSHIPLITE_UPLOAD_DIR."/".$arm_debug_log_file_name.".txt", $arm_download_data, 0777 );

                $debug_log_file_name  = '';

                if(class_exists('ZipArchive'))
                {
                    $zip = new ZipArchive();
                    $zip->open(MEMBERSHIPLITE_UPLOAD_DIR."/".$arm_debug_log_file_name.".zip", ZipArchive::CREATE);
                    $zip->addFile(MEMBERSHIPLITE_UPLOAD_DIR."/".$arm_debug_log_file_name.".txt", $arm_debug_log_file_name.".txt");
                    $zip->close();

                    $arm_download_url = MEMBERSHIPLITE_UPLOAD_URL."/".$arm_debug_log_file_name.".zip";

                    $debug_log_file_name = $arm_debug_log_file_name . ".zip";
                } else {
                    $arm_download_url = MEMBERSHIPLITE_UPLOAD_URL."/".$arm_debug_log_file_name.".txt";
                    $debug_log_file_name = $arm_debug_log_file_name . ".txt";
                }
            }

            echo admin_url( 'admin.php?page=arm_general_settings&action=debug_logs&arm_action=download_log&file=' . $debug_log_file_name ); //phpcs:ignore
            exit();
        }

        function arm_download_payment_debug_log()
        {
            global $wpdb, $ARMemberLite, $arm_global_settings, $arm_capabilities_global;
            $ARMemberLite->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1');//phpcs:ignore --Reason:Verifying nonce
            $arm_download_url = '';
            if(!empty($_POST['arm_download_key']))//phpcs:ignore
            {
                $posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data'), $_POST ); //phpcs:ignore
                $tbl_arm_debug_payment_log = $ARMemberLite->tbl_arm_debug_payment_log;
                $arm_download_key = $posted_data['arm_download_key'];

                $arm_debug_payment_log_where_cond = "";

                $arm_selected_download_duration = !empty($posted_data['selected_download_duration']) ? $posted_data['selected_download_duration'] : 'all';

                $arm_custom_duration_start_date = !empty($posted_data['arm_filter_pstart_date']) ? $posted_data['arm_filter_pstart_date'] : '';
                $arm_custom_duration_end_date = !empty($posted_data['arm_filter_pend_date']) ? $posted_data['arm_filter_pend_date'] : '';
                if(!empty($arm_custom_duration_start_date) && !empty($arm_custom_duration_end_date) && $arm_selected_download_duration == "custom")
                {
                    $arm_custom_duration_start_date = date('Y-m-d 00:00:00', strtotime($arm_custom_duration_start_date)); //phpcs:ignore

                    $arm_custom_duration_end_date = date('Y-m-d 23:59:59', strtotime($arm_custom_duration_end_date)); //phpcs:ignore

                    $arm_debug_payment_log_where_cond = $wpdb->prepare(" AND (arm_payment_log_added_date >= %s AND arm_payment_log_added_date <= %s)",$arm_custom_duration_start_date,$arm_custom_duration_end_date);
                }
                else if(!empty($arm_selected_download_duration) && $arm_selected_download_duration != "custom")
                {
                    $arm_last_selected_days = date('Y-m-d', strtotime('-'.$arm_selected_download_duration.' days')); //phpcs:ignore
                    $arm_debug_payment_log_where_cond = $wpdb->prepare(" AND (arm_payment_log_added_date >= %s)",$arm_last_selected_days);
                }

                $arm_debug_payment_log_query = $wpdb->prepare("SELECT * FROM `" . $tbl_arm_debug_payment_log . "` WHERE `arm_payment_log_gateway` = %s AND `arm_payment_log_status` = %d ".$arm_debug_payment_log_where_cond." ORDER BY arm_payment_log_id DESC",$arm_download_key,1); //phpcs:ignore --Reason $tbl_arm_debug_payment_log is a table name


                $arm_payment_debug_log_data = $wpdb->get_results($arm_debug_payment_log_query, ARRAY_A);//phpcs:ignore --Reason $arm_debug_payment_log_query is a prepared query

                $arm_download_data = wp_json_encode($arm_payment_debug_log_data);

                if( !function_exists('WP_Filesystem' ) )
                {
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                }
                WP_Filesystem();
                global $wp_filesystem;

                $arm_debug_log_file_name = "arm_debug_logs_".$arm_download_key."_".$arm_selected_download_duration;
                $result = $wp_filesystem->put_contents( MEMBERSHIPLITE_UPLOAD_DIR."/".$arm_debug_log_file_name.".txt", $arm_download_data, 0777 );

                $debug_log_file_name  = '';

                if(class_exists('ZipArchive'))
                {
                    $zip = new ZipArchive();
                    $zip->open(MEMBERSHIPLITE_UPLOAD_DIR."/".$arm_debug_log_file_name.".zip", ZipArchive::CREATE);
                    $zip->addFile(MEMBERSHIPLITE_UPLOAD_DIR."/".$arm_debug_log_file_name.".txt", $arm_debug_log_file_name.".txt");
                    $zip->close();

                    $arm_download_url = MEMBERSHIPLITE_UPLOAD_URL."/".$arm_debug_log_file_name.".zip";

                    $debug_log_file_name = $arm_debug_log_file_name . ".zip";
                } else {
                    $arm_download_url = MEMBERSHIPLITE_UPLOAD_URL."/".$arm_debug_log_file_name.".txt";
                    $debug_log_file_name = $arm_debug_log_file_name . ".txt";
                }
            }

            echo admin_url( 'admin.php?page=arm_general_settings&action=debug_logs&arm_action=download_log&file=' . $debug_log_file_name ); //phpcs:ignore
            exit();
        }

		function arm_need_to_cancel_old_subscription_gateways() {
			return apply_filters( 'arm_need_to_cancel_old_subscription_gateways', array() );
		}

		function arm_get_all_payment_gateways() {
			global $wpdb, $ARMemberLite;
			$pay_get_settings_user = get_option( 'arm_payment_gateway_settings', array() );
			$pay_get_settings      = maybe_unserialize( $pay_get_settings_user );
			/* General Settings */
			$default_payment_gateway = array(
				'paypal'        => array( 'gateway_name' => $this->arm_gateway_name_by_key( 'paypal' ) ),

				'bank_transfer' => array( 'gateway_name' => $this->arm_gateway_name_by_key( 'bank_transfer' ) ),
			);
			$payment_gateways = apply_filters( 'arm_get_payment_gateways_in_filters', $default_payment_gateway );
			foreach ( $payment_gateways as $pgKey => $pgVal ) {
				if ( isset( $pay_get_settings[ $pgKey ] ) ) {
					$payment_gateways[ $pgKey ] = array_merge( $pgVal, $pay_get_settings[ $pgKey ] );
				}
			}
			return $payment_gateways;
		}

		function arm_get_all_payment_gateways_for_setup() {
			global $wpdb, $ARMemberLite;
			$pay_get_settings_unser = get_option( 'arm_payment_gateway_settings', array() );
			$pay_get_settings       = maybe_unserialize( $pay_get_settings_unser );
			/* General Settings */
			$default_payment_gateway = array(
				'paypal'        => array( 'gateway_name' => $this->arm_gateway_name_by_key( 'paypal' ) ),

				'bank_transfer' => array( 'gateway_name' => $this->arm_gateway_name_by_key( 'bank_transfer' ) ),
			);
			$payment_gateways = apply_filters( 'arm_get_payment_gateways', $default_payment_gateway );
			foreach ( $payment_gateways as $pgKey => $pgVal ) {
				if ( isset( $pay_get_settings[ $pgKey ] ) ) {
					$payment_gateways[ $pgKey ] = array_merge( $pgVal, $pay_get_settings[ $pgKey ] );
				}
			}
			return $payment_gateways;
		}

		function arm_gateway_name_by_key( $gateway_key = '' ) {
			$gatewayNames = array(
				'paypal'        => esc_html__( 'Paypal', 'armember-membership' ),

				'bank_transfer' => esc_html__( 'Bank Transfer', 'armember-membership' ),
				'manual'        => esc_html__( 'Manual', 'armember-membership' ),
			);
			$gatewayNames = apply_filters( 'arm_filter_gateway_names', $gatewayNames );
			$pgName       = ( isset( $gatewayNames[ $gateway_key ] ) ) ? $gatewayNames[ $gateway_key ] : '';
			return apply_filters( 'arm_gateway_name_by_key', $pgName, $gateway_key );
		}

		function arm_get_active_payment_gateways() {
			global $wpdb, $ARMemberLite;
			$payment_gateways       = array();
			$pay_get_settings_unser = $this->arm_get_all_payment_gateways();
			$pay_get_settings       = maybe_unserialize( $pay_get_settings_unser );
			if ( ! empty( $pay_get_settings ) ) {
				foreach ( $pay_get_settings as $key => $pg ) {
					if ( isset( $pg['status'] ) && $pg['status'] == 1 ) {
						$payment_gateways[ $key ] = $pg;
					}
				}
			}
			return $payment_gateways;
		}

		function arm_get_all_currencies() {
			global $wpdb, $ARMemberLite, $arm_global_settings;
			$paypal_cur        = $this->arm_paypal_currency_symbol();
			$bank_transfer_cur = $this->arm_bank_transfer_currency_symbol();
			$all_currencies    = array_merge( $paypal_cur, $bank_transfer_cur );
			/* Add Custom Currency */
			$global_settings = $arm_global_settings->arm_get_all_global_settings( true );
			$custom_currency = isset( $global_settings['custom_currency'] ) ? $global_settings['custom_currency'] : array();
			if ( isset( $custom_currency['status'] ) && $custom_currency['status'] == 1 ) {
				$all_currencies[ strtoupper( $custom_currency['shortname'] ) ] = $custom_currency['symbol'];
			}
			return apply_filters( 'arm_add_currency_in_default_list', $all_currencies );
		}

		function arm_get_global_currency() {
			global $wpdb, $ARMemberLite, $arm_global_settings;
			$global_settings = isset( $arm_global_settings->global_settings ) ? $arm_global_settings->global_settings : $arm_global_settings->arm_get_all_global_settings( true );
			$global_currency = $global_settings['paymentcurrency'];
			$custom_currency = isset( $global_settings['custom_currency'] ) ? $global_settings['custom_currency'] : array();
			if ( isset( $custom_currency['status'] ) && $custom_currency['status'] == 1 ) {
				$global_currency = $custom_currency['shortname'];
			}
			$global_currency = apply_filters( 'arm_set_global_currency_outside', $global_currency );
			return $global_currency;
		}

		/**
		 * Get Currency Symbol Position From Currency Code
		 */
		function arm_currency_symbol_position( $currency = '' ) {
			global $wpdb, $ARMemberLite, $arm_global_settings;
			$symbol_position = array(
				'prefix' => array( 'USD', 'AUD', 'BRL', 'CAD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'NZD', 'PHP', 'GBP', 'RUB', 'SGD', 'CHF', 'TWD', 'THB', 'TRY', 'INR', 'GHS', 'NGN', 'ZAR', 'HKD', 'BTC', 'BTN', 'CUC', 'CUP', 'GGP', 'IMP', 'JEP', 'KPW', 'RMB', 'SDG', 'SSP', 'VEF', 'VES' ),

				'suffix' => array( 'CZK', 'SEK', 'DKK', 'NOK', 'PLN', 'BHD', 'BYR', 'BYN', 'ERN', 'IQD', 'IRR', 'IRT', 'JOD', 'KWD', 'LYD', 'MRU', 'OMR', 'PRB', 'STN', 'TMT', 'TND' ),
			);

			$current_symbol_pos = 'suffix';
			// $global_settings = $arm_global_settings->arm_get_all_global_settings(true);
			$global_settings = isset( $arm_global_settings->global_settings ) ? $arm_global_settings->global_settings : $arm_global_settings->arm_get_all_global_settings( true );

			$custom_currency = isset( $global_settings['custom_currency'] ) ? $global_settings['custom_currency'] : array();

			if ( isset( $custom_currency['status'] ) && $custom_currency['status'] == 1 && $custom_currency['place'] ) {
				$current_symbol_pos = $custom_currency['place'];
			} elseif ( in_array( strtoupper( $currency ), $symbol_position['prefix'] ) ) {
				$current_symbol_pos = 'prefix';
			} elseif ( in_array( strtoupper( $currency ), $symbol_position['suffix'] ) ) {
				$current_symbol_pos = 'suffix';
			} elseif ( $currency == 'EUR' ) {
				$current_symbol_pos = isset( $global_settings['arm_specific_currency_position'] ) ? $global_settings['arm_specific_currency_position'] : 'suffix';
			}
			return $current_symbol_pos;
		}

		/**
		 * Get Currency Symbol Position With Amount From Currency Code And Amount
		 */
		function arm_prepare_amount( $currency = '', $amount = 0 ) {
			$new_amount = $amount;
			if ( ! empty( $currency ) && ! empty( $amount ) && $amount > 0 ) {
				$all_currencies = $this->arm_get_all_currencies();
				$symbol         = isset( $all_currencies[ strtoupper( $currency ) ] ) ? $all_currencies[ strtoupper( $currency ) ] : ' ';
				if ( $this->arm_currency_symbol_position( $currency ) == 'prefix' ) {
					$new_amount = $symbol . '' . $this->arm_amount_set_separator( $currency, $amount );
				} else {
					$new_amount = $this->arm_amount_set_separator( $currency, $amount ) . '' . $symbol;
				}
			} elseif ( ! empty( $currency ) ) {
				$all_currencies = $this->arm_get_all_currencies();
				$symbol         = isset( $all_currencies[ strtoupper( $currency ) ] ) ? $all_currencies[ strtoupper( $currency ) ] : ' ';
				if ( $this->arm_currency_symbol_position( $currency ) == 'prefix' ) {
					$new_amount = $symbol . '' . $this->arm_amount_set_separator( $currency, $amount );
				} else {
					$new_amount = $this->arm_amount_set_separator( $currency, $amount ) . '' . $symbol;
				}
			}
			return $new_amount;
		}

		function arm_update_pay_gate_settings() {
			global $wpdb, $ARMemberLite, $arm_members_class, $arm_member_forms, $arm_email_settings, $arm_global_settings, $arm_capabilities_global, $arm_subscription_plans;

			$pay_gate_settings = array();
			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_payment_gateways'], '1' );//phpcs:ignore --Reason:Verifying nonce
			
			$old_pay_gate_settings = get_option( 'arm_payment_gateway_settings' );
			
			$posted_data = array_map( array( $ARMemberLite, 'arm_recursive_sanitize_data_extend' ), $_POST ); //phpcs:ignore
			$arm_payment_gateway_settings = $posted_data['payment_gateway_settings']; //phpcs:ignore
			if ( is_array( $arm_payment_gateway_settings ) && ! empty( $arm_payment_gateway_settings ) ) {
				foreach ( $arm_payment_gateway_settings as $key => $pg_setting ) {
					$pay_gate_settings[ $key ]                       = isset( $arm_payment_gateway_settings[ $key ] ) ? $pg_setting : '';
					$pay_gate_settings[ $key ]['payment_debug_logs'] = !empty( $old_pay_gate_settings[ $key ]['payment_debug_logs'] ) ? $old_pay_gate_settings[ $key ]['payment_debug_logs'] : 0;
				}
			}

			$pay_gate_settings        = apply_filters( 'arm_save_payment_gateway_settings', $pay_gate_settings, $posted_data );
			$pay_gate_settings        = arm_array_map( $pay_gate_settings );
			$pay_gate_settings_result = $pay_gate_settings;
			update_option( 'arm_payment_gateway_settings', $pay_gate_settings_result );
			$this->arm_update_payment_gate_status();
			$response = array( 'message' => 'success' );
			echo wp_json_encode( $response );
			die();
		}

		function arm_update_payment_gate_status() {
			global $wpdb, $ARMemberLite, $arm_global_settings,$arm_capabilities_global;
			$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_payment_gateways'], '1' );//phpcs:ignore --Reason:Verifying nonce
			$global_currency   = $this->arm_get_global_currency();
			$not_allow_payment = $this->arm_check_currency_status_for_gateways( $global_currency );
			$not_allow_payment = apply_filters( 'arm_check_currency_status', $not_allow_payment, $global_currency );
			if ( ! empty( $not_allow_payment ) ) {
				$pg_settings     = get_option( 'arm_payment_gateway_settings', array() );
				$new_pg_settings = maybe_unserialize( $pg_settings );
				foreach ( $not_allow_payment as $payment ) {
					if ( isset( $new_pg_settings[ $payment ] ) ) {
						$new_pg_settings[ $payment ]['status'] = 0;
					}
				}
				update_option( 'arm_payment_gateway_settings', $new_pg_settings );
			}
			return;
		}

		function arm_filter_form_posted_plan_data( $posted_data, $user_ID,$admin_save_flag = 0 ) {
			global $wpdb, $ARMemberLite, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $payment_done, $arm_transaction, $arm_members_class,$arm_membership_setup;

			if ( ! empty( $posted_data ) && is_array( $posted_data ) && ( ! empty( $posted_data['subscription_plan'] ) || ! empty( $posted_data['_subscription_plan'] ) || ! empty( $posted_data['arm_user_plan'] ) ) ) {
				/* Set User Plan Values */

				if ( isset( $posted_data['arm_update_user_from_profile'] ) && $posted_data['arm_update_user_from_profile'] == 0 ) {
					$arm_update_user_from_profile = 0;
				} else {
					$arm_update_user_from_profile = 1;
				}

				if ( ! empty( $posted_data['arm_user_plan'] ) ) {
					$subscription_plan = $posted_data['arm_user_plan'];
				} else {
					$subscription_plan = ( ! empty( $posted_data['subscription_plan'] ) ) ? intval( $posted_data['subscription_plan'] ) : 0;
					if ( $subscription_plan == 0 ) {
						$subscription_plan = ( ! empty( $posted_data['_subscription_plan'] ) ) ? intval( $posted_data['_subscription_plan'] ) : 0;
					}
				}

				$setup_id = isset( $posted_data['setup_id'] ) ? intval( $posted_data['setup_id'] ) : 0;

				$pgateway = isset( $posted_data['payment_gateway'] ) ? sanitize_text_field( $posted_data['payment_gateway'] ) : '';
				if ( $pgateway === '' ) {
					$pgateway = isset( $posted_data['_payment_gateway'] ) ? sanitize_text_field( $posted_data['_payment_gateway'] ) : '';
				}

				$gateway = ( ! empty( $pgateway ) ) ? $pgateway : 'manual';

				$action = isset( $posted_data['action'] ) ? sanitize_text_field( $posted_data['action'] ) : '';

				$defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
				$all_active_pgs  = $this->arm_get_active_payment_gateways();
				foreach ( $all_active_pgs as $k => $data ) {
					if ( isset( $posted_data[ $k ] ) ) {
						unset( $posted_data[ $k ] );
					}
				}
				$posted_data['arm_user_plan'] = $subscription_plan;

				if ( ! empty( $subscription_plan ) ) {
					if ( is_array( $subscription_plan ) ) {

						foreach ( $subscription_plan as $pid ) {
							if ( ! empty( $pid ) ) {
								$userPlanDatameta = get_user_meta( $user_ID, 'arm_user_plan_' . $pid, true );
								$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
								$userPlanData     = shortcode_atts( $defaultPlanData, $userPlanDatameta );
								$update           = false;
								$plan             = new ARM_Plan_Lite( $pid );

								if ( $plan->is_free() ) {
									$pgateway = '';
								}

								$posted_data['pgateway'] = $pgateway;

								$payment_mode  = '';
								$payment_cycle = '';

								if ( $plan->is_recurring() ) {
										$payment_mode_ = ! empty( $posted_data['arm_selected_payment_mode'] ) ? sanitize_text_field( $posted_data['arm_selected_payment_mode'] ) : 'manual_subscription';
									if ( isset( $posted_data['arm_payment_mode'][ $gateway ] ) ) {
										$payment_mode_ = ! empty( $posted_data['arm_payment_mode'][ $gateway ] ) ? sanitize_text_field( $posted_data['arm_payment_mode'][ $gateway ] ) : 'manual_subscription';
									} else {
										$setup_data = $arm_membership_setup->arm_get_membership_setup( $setup_id );
										if ( ! empty( $setup_data ) && ! empty( $setup_data['setup_modules']['modules'] ) ) {
											$setup_modules = $setup_data['setup_modules'];
											$modules       = $setup_modules['modules'];
											$payment_mode_ = $modules['payment_mode'][ $gateway ];
										}
									}
									$payment_mode = 'manual_subscription';
									if ( $payment_mode_ == 'both' ) {
										$payment_mode = ! empty( $posted_data['arm_selected_payment_mode'] ) ? sanitize_text_field( $posted_data['arm_selected_payment_mode'] ) : 'manual_subscription';
									} else {
										$payment_mode = $payment_mode_;
									}

									$payment_cycle = isset( $posted_data['arm_selected_payment_cycle'] ) ? $posted_data['arm_selected_payment_cycle'] : 0;
								}

								/*
												 *  1) At import time if user with old date imported ( `arm_subscription_start_date`  set in csv ) than set that start date else assign current date . At import time don't allow trial period.
								* 2) if user add from admin or updated from admin than don't allow trial period.
								* */
								if ( isset( $posted_data['arm_subscription_start_date'] ) && $posted_data['arm_subscription_start_date'] != '' || ( isset( $posted_data['arm_user_import'] ) && $posted_data['arm_user_import'] == true || $posted_data['action'] == 'add_member' || $posted_data['action'] == 'update_member' ) ) {

									if ( isset( $posted_data['arm_subscription_start_date'] ) && $posted_data['arm_subscription_start_date'] != '' ) {
										$nowMysql = strtotime( $posted_data['arm_subscription_start_date'] );
									} else {
										if ( isset( $posted_data[ 'arm_subscription_start_' . $pid ] ) && ! empty( $posted_data[ 'arm_subscription_start_' . $pid ] ) ) {
											$nowMysql = strtotime( $posted_data[ 'arm_subscription_start_' . $pid ] );
											unset( $posted_data[ 'arm_subscription_start_' . $pid ] );
										} else {
											$nowMysql = strtotime( current_time( 'mysql' ) );
										}

										if ( $plan->is_recurring() && isset( $payment_cycle ) && ! empty( $payment_cycle ) && is_array( $payment_cycle ) ) {
											if ( isset( $payment_cycle[ 'arm_plan_cycle_' . $pid ] ) ) {
												$payment_cycle = $payment_cycle[ 'arm_plan_cycle_' . $pid ];
											}
										}
									}
									$start_time                = $nowMysql;
									$posted_data['start_time'] = $start_time;
								} else {
									$nowMysql                  = strtotime( current_time( 'mysql' ) );
									$posted_data['start_time'] = $nowMysql;
									if ( $pgateway != 'bank_transfer' ) {
										$trial_and_sub_start_date = $plan->arm_trial_and_plan_start_date( $nowMysql, $payment_mode, true, $payment_cycle );
										$start_time               = isset( $trial_and_sub_start_date['subscription_start_date'] ) ? $trial_and_sub_start_date['subscription_start_date'] : '';

										if ( isset( $trial_and_sub_start_date['arm_trial_start_date'] ) && $trial_and_sub_start_date['arm_trial_start_date'] != '' ) {

											$userPlanData['arm_trial_start'] = $trial_and_sub_start_date['arm_trial_start_date'];
											if ( isset( $trial_and_sub_start_date['arm_expire_plan_trial'] ) && $trial_and_sub_start_date['arm_expire_plan_trial'] != '' ) {
												$userPlanData['arm_is_trial_plan'] = '1';
												$userPlanData['arm_trial_end']     = $trial_and_sub_start_date['arm_expire_plan_trial'];
												$arm_is_trial                      = true;
											}
										}
									}
								}

								$old_plan_ids = get_user_meta( $user_ID, 'arm_user_plan_ids', true );
								$old_plan_ids = ! empty( $old_plan_ids ) ? $old_plan_ids : array();
								$expire_time  = false;
								if ( $pgateway == 'bank_transfer' ) {
									if ( $plan->is_recurring() ) {
										$payment_mode                      = 'manual_subscription';
										$userPlanData['arm_payment_mode']  = $payment_mode;
										$userPlanData['arm_payment_cycle'] = $payment_cycle;
										$update                            = false;
									} else {
										$userPlanData['arm_payment_mode']  = '';
										$userPlanData['arm_payment_cycle'] = '';
									}
								} else {
									$plan_options = $plan->options;
									$expire_time  = $plan->arm_plan_expire_time( $start_time, $payment_mode, $payment_cycle );

									if ( in_array( $plan->ID, $old_plan_ids ) ) {
											$expired = $userPlanData['arm_expire_plan'];
										if ( $arm_update_user_from_profile == 0 ) {
											$expire_time = $plan->arm_plan_expire_time_for_renew_action( $start_time );

											if ( $plan->is_recurring() ) {
												$completed_rec                           = $userPlanData['arm_completed_recurring'];
												$completed_rec                           = ! empty( $completed_rec ) ? $completed_rec : 0;
												$userPlanData['arm_completed_recurring'] = $completed_rec + 1;
												$userPlanData['arm_payment_mode']        = $payment_mode;
												$userPlanData['arm_payment_cycle']       = $payment_cycle;

											}
										} else {
											if ( $plan->is_recurring()/* && $old_payment_mode == 'manual_subscription' */ ) {
												$completed_rec = $userPlanData['arm_completed_recurring'];
												if ( $completed_rec === '' ) {
													$userPlanData['arm_completed_recurring'] = 1;
												}
											}
										}
										if ( ! empty( $expired ) && $start_time > $expired ) {
											$update = true;
										}
									} else {

										if ( $plan->is_recurring()/* && $payment_mode == 'manual_subscription'*/ ) {
											if ( ! $plan->has_trial_period() ) {
												$userPlanData['arm_completed_recurring'] = 1;
											} else {

												if ( $arm_update_user_from_profile == 0 ) {
													$userPlanData['arm_completed_recurring'] = 0;
												} else {
													$userPlanData['arm_completed_recurring'] = 1;
												}
											}

											$userPlanData['arm_payment_mode']  = $payment_mode;
											$userPlanData['arm_payment_cycle'] = $payment_cycle;
										}
										$update = true;
									}
								}

								if ( $update ) {
									$posted_data['roles'] = ( isset( $posted_data['roles'] ) && ! empty( $posted_data['roles'] ) ) ? $posted_data['roles'] : array();
									if ( ! empty( $plan->plan_role ) ) {
										array_push( $posted_data['roles'], $plan->plan_role );
									}

									$userPlanData['arm_start_plan'] = $start_time;
									if ( $expire_time != false ) {
										$userPlanData['arm_expire_plan'] = $expire_time;
									}
									$userPlanData['arm_user_gateway'] = ( ! empty( $pgateway ) ) ? $pgateway : 'manual';
									/* Set Current Plan Detail */
									$curPlanDetail                                    = (array) $plan->plan_detail;
									$curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;
									$userPlanData['arm_current_plan_detail']          = $curPlanDetail;

								}
								update_user_meta( $user_ID, 'arm_user_plan_' . $plan->ID, $userPlanData );
								if ( ! in_array( $plan->ID, $old_plan_ids ) ) {
									if ( $plan->is_recurring()/* && $payment_mode == 'manual_subscription'*/ ) {
										$allow_trial = true;

										if ( $action == 'add_member' || $action == 'update_member' ) {
											$allow_trial = false;
										}
										$arm_next_payment_due_date            = $arm_members_class->arm_get_next_due_date( $user_ID, $plan->ID, $allow_trial, $payment_cycle, $start_time );
										$userPlanData['arm_next_due_payment'] = $arm_next_payment_due_date;
										update_user_meta( $user_ID, 'arm_user_plan_' . $plan->ID, $userPlanData );
									}
								}

								/* Unset unsued details. */
								if ( ! empty( $payment_done ) && $payment_done['status'] === true ) {
									$log_id        = ( ! empty( $payment_done['log_id'] ) ) ? $payment_done['log_id'] : 0;
									$selectColumns = '`arm_log_id`, `arm_user_id`, `arm_transaction_id`';

									$armLogTable    = $ARMemberLite->tbl_arm_payment_log;
									$selectColumns .= ', `arm_token`, `arm_extra_vars`';

									$log_detail = $wpdb->get_row( $wpdb->prepare("SELECT {$selectColumns} FROM `{$armLogTable}` WHERE `arm_log_id`=%d",$log_id) ); //phpcs:ignore --Reason: $armLogTable is table name defined globally. False Positive alarm
									if ( ! empty( $log_detail ) ) {
										$arm_first_name = ( isset( $posted_data['first_name'] ) ) ? $posted_data['first_name'] : '';
										$arm_last_name  = ( isset( $posted_data['last_name'] ) ) ? $posted_data['last_name'] : '';
										if ( $user_ID ) {
											$user_detail = get_userdata( $user_ID );
											if ( empty( $arm_first_name ) ) {
												$arm_first_name = $user_detail->first_name;
											}
											if ( empty( $arm_last_name ) ) {
												$arm_last_name = $user_detail->last_name;
											}
										}
										$upData                   = array( 'arm_user_id' => $user_ID );
										$upData['arm_first_name'] = $arm_first_name;
										$upData['arm_last_name']  = $arm_last_name;
										if ( $pgateway != 'bank_transfer' ) {
											$extra_vars = maybe_unserialize( $log_detail->arm_extra_vars );
											if ( isset( $extra_vars['card_number'] ) && ! empty( $extra_vars['card_number'] ) ) {
												$extra_vars['card_number'] = $extra_vars['card_number'];
											} else {
												$extra_vars['card_number'] = isset( $posted_data[ $pgateway ]['card_number'] ) ? $posted_data[ $pgateway ]['card_number'] : '-';
											}
											$upData['arm_extra_vars'] = maybe_serialize( $extra_vars );
										}
										$wpdb->update( $armLogTable, $upData, array( 'arm_log_id' => $log_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

										$userPlanData = apply_filters( 'arm_membership_update_user_meta_from_outside', $userPlanData, $user_ID, $plan, $log_detail, $pgateway );
									}
									$posted_data['arm_entry_id'] = ( ! empty( $payment_done['entry_id'] ) ) ? $payment_done['entry_id'] : 0;

									update_user_meta( $user_ID, 'arm_user_plan_' . $plan->ID, $userPlanData );
								}
							}
						}
					} else {

						$userPlanDatameta = get_user_meta( $user_ID, 'arm_user_plan_' . $subscription_plan, true );
						$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
						$userPlanData     = shortcode_atts( $defaultPlanData, $userPlanDatameta );

						$update = false;

						$plan = new ARM_Plan_Lite( $subscription_plan );

						if ( $plan->is_free() ) {
							$pgateway = '';
						}

						$posted_data['pgateway'] = $pgateway;

						$payment_mode  = '';
						$payment_cycle = '';

						if ( $plan->is_recurring() ) {
							$payment_mode_ = isset( $posted_data['arm_payment_mode'][ $gateway ] ) ? sanitize_text_field( $posted_data['arm_payment_mode'][ $gateway ] ) : 'both';
							$payment_mode  = 'manual_subscription';
							if ( $payment_mode_ == 'both' ) {
								$payment_mode = ! empty( $posted_data['arm_selected_payment_mode'] ) ? sanitize_text_field( $posted_data['arm_selected_payment_mode'] ) : 'manual_subscription';
							} else {
								$payment_mode = $payment_mode_;
							}
							$payment_cycle = isset( $posted_data['arm_selected_payment_cycle'] ) ? intval( $posted_data['arm_selected_payment_cycle'] ) : 0;
						}
						$start_time = strtotime( current_time( 'mysql' ) );

						/*
										 *  1) At import time if user with old date imported ( `arm_subscription_start_date`  set in csv ) than set that start date else assign current date . At import time don't allow trial period.
						 * 2) if user add from admin or updated from admin than don't allow trial period.
						 * */
						if ( isset( $posted_data['arm_subscription_start_date'] ) && $posted_data['arm_subscription_start_date'] != '' || ( isset( $posted_data['arm_user_import'] ) && $posted_data['arm_user_import'] == true || $action == 'add_member' || $action == 'update_member' ) ) {

							if ( isset( $posted_data['arm_subscription_start_date'] ) && $posted_data['arm_subscription_start_date'] != '' ) {
								$nowMysql = strtotime( $posted_data['arm_subscription_start_date'] );

							} else {
								$nowMysql = strtotime( current_time( 'mysql' ) );
							}
							$start_time                = $nowMysql;
							$posted_data['start_time'] = $nowMysql;
						} else {
							$nowMysql                  = strtotime( current_time( 'mysql' ) );
							$posted_data['start_time'] = $nowMysql;
							if ( $pgateway != 'bank_transfer' ) {
								$trial_and_sub_start_date = $plan->arm_trial_and_plan_start_date( $nowMysql, $payment_mode, true, $payment_cycle );
								$start_time               = isset( $trial_and_sub_start_date['subscription_start_date'] ) ? $trial_and_sub_start_date['subscription_start_date'] : '';

								if ( isset( $trial_and_sub_start_date['arm_trial_start_date'] ) && $trial_and_sub_start_date['arm_trial_start_date'] != '' ) {

									$userPlanData['arm_trial_start'] = $trial_and_sub_start_date['arm_trial_start_date'];
									if ( isset( $trial_and_sub_start_date['arm_expire_plan_trial'] ) && $trial_and_sub_start_date['arm_expire_plan_trial'] != '' ) {

										$userPlanData['arm_is_trial_plan'] = '1';

										$userPlanData['arm_trial_end'] = $trial_and_sub_start_date['arm_expire_plan_trial'];
										$arm_is_trial                  = true;
									}
								}
							}
						}

						$old_plan_ids = get_user_meta( $user_ID, 'arm_user_plan_ids', true );
						$old_plan_ids = ! empty( $old_plan_ids ) ? $old_plan_ids : array();

						$expire_time = false;
						if ( $pgateway == 'bank_transfer' ) {

							if ( $plan->is_recurring() ) {
								$payment_mode                      = 'manual_subscription';
								$update                            = false;
								$userPlanData['arm_payment_mode']  = $payment_mode;
								$userPlanData['arm_payment_cycle'] = $payment_cycle;
							} else {
								$userPlanData['arm_payment_mode']  = '';
								$userPlanData['arm_payment_cycle'] = '';
							}
						} else {
							$plan_options = $plan->options;
							$expire_time  = $plan->arm_plan_expire_time( $start_time, $payment_mode, $payment_cycle );

							if ( in_array( $plan->ID, $old_plan_ids ) ) {

									$expired = $userPlanData['arm_expire_plan'];
								if ( $arm_update_user_from_profile == 0 ) {
									$expire_time   = $plan->arm_plan_expire_time_for_renew_action( $start_time );
									$completed_rec = $userPlanData['arm_completed_recurring'];
									$completed_rec = ! empty( $completed_rec ) ? $completed_rec : 0;
									if ( $plan->is_recurring() ) {
										$userPlanData['arm_completed_recurring'] = $completed_rec + 1;
										$userPlanData['arm_payment_mode']        = $payment_mode;
										$userPlanData['arm_payment_cycle']       = $payment_cycle;
									}
								} else {
									if ( $plan->is_recurring() /*&& $old_payment_mode == 'manual_subscription'*/ ) {
										$completed_rec = $userPlanData['arm_completed_recurring'];
										if ( $completed_rec === '' ) {
											$userPlanData['arm_completed_recurring'] = 1;
										}
									}
								}
								if ( ! empty( $expired ) && $start_time > $expired ) {
									$update = true;
								}
							} else {
								if ( $plan->is_recurring()/* && $payment_mode == 'manual_subscription'*/ ) {
									if ( ! $plan->has_trial_period() ) {
										$userPlanData['arm_completed_recurring'] = 1;
									} else {
										if ( $arm_update_user_from_profile == 0 ) {
											$userPlanData['arm_completed_recurring'] = 0;
										} else {
											$userPlanData['arm_completed_recurring'] = 1;
										}
									}
									$userPlanData['arm_payment_mode']  = $payment_mode;
									$userPlanData['arm_payment_cycle'] = $payment_cycle;
								}
								$update = true;
							}
						}
						if ( $update ) {

							/* Assign Membership Plan Role To User if member form has no role field */
							if ( empty( $posted_data['roles'] ) && ! empty( $plan->plan_role ) ) {
								$posted_data['roles'] = $plan->plan_role;
							}

							$posted_data['arm_user_plan']   = $subscription_plan;
							$userPlanData['arm_start_plan'] = $start_time;
							if ( $expire_time != false ) {
								$userPlanData['arm_expire_plan'] = $expire_time;
							}
							$userPlanData['arm_user_gateway'] = ( ! empty( $pgateway ) ) ? $pgateway : 'manual';
							/* Set Current Plan Detail */

							$curPlanDetail = (array) $plan->plan_detail;

							$curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;
							$userPlanData['arm_current_plan_detail']          = $curPlanDetail;

						}
						update_user_meta( $user_ID, 'arm_user_plan_' . $plan->ID, $userPlanData );
						if ( ! in_array( $plan->ID, $old_plan_ids ) ) {
							if ( $plan->is_recurring()/* && $payment_mode == 'manual_subscription'*/ ) {
								$allow_trial = true;

								if ( $action == 'add_member' || $action == 'update_member' ) {
									$allow_trial = false;
								}

								$arm_next_payment_due_date            = $arm_members_class->arm_get_next_due_date( $user_ID, $plan->ID, $allow_trial, $payment_cycle, $start_time );
								$userPlanData['arm_next_due_payment'] = $arm_next_payment_due_date;

								update_user_meta( $user_ID, 'arm_user_plan_' . $plan->ID, $userPlanData );
							}
						}

						/* Unset unsued details. */
						if ( ! empty( $payment_done ) && $payment_done['status'] === true ) {
							$log_id        = ( ! empty( $payment_done['log_id'] ) ) ? $payment_done['log_id'] : 0;
							$selectColumns = '`arm_log_id`, `arm_user_id`, `arm_transaction_id`';

							$armLogTable    = $ARMemberLite->tbl_arm_payment_log;
							$selectColumns .= ', `arm_token`, `arm_extra_vars`';

							$log_detail = $wpdb->get_row( $wpdb->prepare("SELECT {$selectColumns} FROM `{$armLogTable}` WHERE `arm_log_id`=%d",$log_id) );//phpcs:ignore --Reason: $armLogTable is Table name defined globally. False Positive alarm
							if ( ! empty( $log_detail ) ) {
								$arm_first_name = ( isset( $posted_data['first_name'] ) ) ? $posted_data['first_name'] : '';
								$arm_last_name  = ( isset( $posted_data['last_name'] ) ) ? $posted_data['last_name'] : '';
								if ( $user_ID ) {
									$user_detail = get_userdata( $user_ID );
									if ( empty( $arm_first_name ) ) {
										$arm_first_name = $user_detail->first_name;
									}
									if ( empty( $arm_last_name ) ) {
										$arm_last_name = $user_detail->last_name;
									}
								}
								$upData                   = array( 'arm_user_id' => $user_ID );
								$upData['arm_first_name'] = $arm_first_name;
								$upData['arm_last_name']  = $arm_last_name;
								if ( $pgateway != 'bank_transfer' ) {
									$extra_vars = ! empty( $log_detail->arm_extra_vars ) ? maybe_unserialize( $log_detail->arm_extra_vars ) : array();
									if ( isset( $extra_vars['card_number'] ) && ! empty( $extra_vars['card_number'] ) ) {
										$extra_vars['card_number'] = $extra_vars['card_number'];
									} else {
										$extra_vars['card_number'] = isset( $posted_data[ $pgateway ]['card_number'] ) ? $posted_data[ $pgateway ]['card_number'] : '-';
									}
									$upData['arm_extra_vars'] = maybe_serialize( $extra_vars );
								}
								$wpdb->update( $armLogTable, $upData, array( 'arm_log_id' => $log_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

								$userPlanData = apply_filters( 'arm_membership_update_user_meta_from_outside', $userPlanData, $user_ID, $plan, $log_detail, $pgateway );

							}
							$posted_data['arm_entry_id'] = ( ! empty( $payment_done['entry_id'] ) ) ? $payment_done['entry_id'] : 0;

							update_user_meta( $user_ID, 'arm_user_plan_' . $plan->ID, $userPlanData );
						}
					}
				}
				unset( $posted_data['payment_done'] );
				unset( $posted_data['subscription_plan'] );
				unset( $posted_data['_subscription_plan'] );
				unset( $posted_data['payment_gateway'] );
				unset( $posted_data['_payment_gateway'] );
			}

			if ( ! empty( $posted_data['arm_user_future_plan'] ) ) {
				$subscription_future_plan = $posted_data['arm_user_future_plan'];
			} else {
				$subscription_future_plan = array();
			}

			if ( ! empty( $posted_data['role'] ) ) {
				$meta_key    = 'role';
				$arm_form_id = isset( $posted_data['arm_form_id'] ) ? intval( $posted_data['arm_form_id'] ) : '';

				$user_form_id = ! empty( $arm_form_id ) ? $arm_form_id : get_user_meta( $user_ID, 'arm_form_id', true );

				$form = new ARM_Form_Lite( 'id', $user_form_id );

				if ( ! $form->exists() || $form->type != 'registration' ) {
					$user_form_id = $default_form_id = $arm_member_forms->arm_get_default_form_id( 'registration' );
					$form         = new ARM_Form_Lite( 'id', $default_form_id );
				}

				if ( $form->exists() && ! empty( $form->fields ) ) {
					$arm_role_field_options = $arm_member_forms->arm_get_field_option_by_meta( $meta_key, $user_form_id );
					$arm_role_field_option  = isset( $arm_role_field_options['options'] ) ? $arm_role_field_options['options'] : '';
					if ( ! empty( $arm_role_field_option ) ) {
						if ( is_array( $posted_data['role'] ) ) {
							$count = 0;
							foreach ( $posted_data['role'] as $posted_data_role ) {
								if ( ! array_key_exists( $posted_data_role, $arm_role_field_option ) ) {
									if ( ( $arm_role_key = array_search( $posted_data_role, $posted_data['role'] ) ) !== false ) {
										unset( $posted_data['role'][ $arm_role_key ] );
									}
								}
							}
						} else {
							if ( ! array_key_exists( $posted_data['role'], $arm_role_field_option ) ) {
								// unset($posted_data['role']);
								$posted_data['role'] = '';
							}
						}
					} else {
						// unset($posted_data['role']);
						if ( is_array( $posted_data['role'] ) ) {
							if ( in_array( 'administrator', $posted_data['role'] ) ) {
								if ( ( $arm_role_key = array_search( 'administrator', $posted_data['role'] ) ) !== false ) {
									unset( $posted_data['role'][ $arm_role_key ] );
								}
							}
						} else {
							if ( ! empty( $posted_data['role'] ) && $posted_data['role'] == 'administrator' ) {
								$posted_data['role'] = '';
							}
						}
					}
				} else {
					// unset($posted_data['role']);
					if ( is_array( $posted_data['role'] ) ) {
						if ( in_array( 'administrator', $posted_data['role'] ) ) {
							if ( ( $arm_role_key = array_search( 'administrator', $posted_data['role'] ) ) !== false ) {
								unset( $posted_data['role'][ $arm_role_key ] );
							}
						}
					} else {
						if ( ! empty( $posted_data['role'] ) && $posted_data['role'] == 'administrator' ) {
							$posted_data['role'] = '';
						}
					}
				}
			}

			if ( ! empty( $posted_data['roles'] ) && ( ( !empty( $posted_data['arm_user_plan'] ) ) || (empty( $posted_data['arm_user_plan'] ) && empty($admin_save_flag))) ) {
				$meta_key    = 'roles';
				$arm_form_id = isset( $posted_data['arm_form_id'] ) ? $posted_data['arm_form_id'] : '';

				$user_form_id = ! empty( $arm_form_id ) ? $arm_form_id : get_user_meta( $user_ID, 'arm_form_id', true );

				$form = new ARM_Form_Lite( 'id', $user_form_id );

				if ( ! $form->exists() || $form->type != 'registration' ) {
					$user_form_id = $default_form_id = $arm_member_forms->arm_get_default_form_id( 'registration' );
					$form         = new ARM_Form_Lite( 'id', $default_form_id );
				}

				if ( $form->exists() && ! empty( $form->fields ) ) {
					$arm_role_field_options = $arm_member_forms->arm_get_field_option_by_meta( $meta_key, $user_form_id );
					$arm_role_field_option  = isset( $arm_role_field_options['options'] ) ? $arm_role_field_options['options'] : '';
					if ( ! empty( $arm_role_field_option ) ) {
						if ( is_array( $posted_data['roles'] ) ) {
							$count = 0;
							foreach ( $posted_data['roles'] as $posted_data_role ) {
								if ( ! array_key_exists( $posted_data_role, $arm_role_field_option ) ) {
									if ( ( $arm_role_key = array_search( $posted_data_role, $posted_data['roles'] ) ) !== false ) {
										unset( $posted_data['roles'][ $arm_role_key ] );
									}
								}
							}
						} else {
							if ( ! array_key_exists( $posted_data['roles'], $arm_role_field_option ) ) {
								// unset($posted_data['roles']);
								$posted_data['roles'] = '';
							}
						}
					} else {
						// unset($posted_data['roles']);
						// $posted_data['roles'] = "";
						if ( is_array( $posted_data['roles'] ) ) {
							if ( in_array( 'administrator', $posted_data['roles'] ) ) {
								if ( ( $arm_role_key = array_search( 'administrator', $posted_data['roles'] ) ) !== false ) {
									unset( $posted_data['roles'][ $arm_role_key ] );
								}
							}
						} else {
							if ( ! empty( $posted_data['roles'] ) && $posted_data['roles'] == 'administrator' ) {
								$posted_data['roles'] = '';
							}
						}
					}
				} else {
					// unset($posted_data['roles']);
					if ( is_array( $posted_data['roles'] ) ) {
						if ( in_array( 'administrator', $posted_data['roles'] ) ) {
							if ( ( $arm_role_key = array_search( 'administrator', $posted_data['roles'] ) ) !== false ) {
								unset( $posted_data['roles'][ $arm_role_key ] );
							}
						}
					} else {
						if ( ! empty( $posted_data['roles'] ) && $posted_data['roles'] == 'administrator' ) {
							$posted_data['roles'] = '';
						}
					}
				}
			}

			$posted_data = array_merge( array( 'arm_user_future_plan' => $subscription_future_plan ), $posted_data );

			return $posted_data;
		}

		function arm_save_payment_log( $log_data = array() ) {
			global $wp, $wpdb, $ARMemberLite, $arm_subscription_plans,  $arm_transaction;
			$payment_log_id = $arm_transaction->arm_add_transaction( $log_data );
			return $payment_log_id;
		}

		function arm_bank_transfer_payment_gateway_action( $payment_gateway, $payment_gateway_options, $posted_data, $entry_id = 0 ) {
			global $wpdb, $ARMemberLite, $arm_global_settings, $payment_done, $arm_membership_setup,  $arm_subscription_plans,$arm_lite_debug_payment_log_id;
			if ( $payment_gateway == 'bank_transfer' ) {
				$arm_posted_data = $posted_data;
				if(!empty($arm_posted_data) && isset($arm_posted_data['user_pass'])){
					unset($arm_posted_data['user_pass']);
				}
				if(!empty($arm_posted_data) && isset($arm_posted_data['repeat_pass'])){
					unset($arm_posted_data['repeat_pass']);
				}
				do_action('arm_payment_log_entry', 'bank_transfer', 'Bank Transfer Posted Data', 'armember', $arm_posted_data, $arm_lite_debug_payment_log_id);
				$entry_data = $this->arm_get_entry_data_by_id( $entry_id );
				if ( ! empty( $entry_data ) ) {
					$posted_data = apply_filters( 'arm_handle_bank_transfer_before_payment_from_outside', $posted_data, $entry_data );
					$arm_entry_values  = maybe_unserialize( $entry_data['arm_entry_value'] );
					if(!empty($arm_entry_values) && isset($arm_entry_values['user_pass'])){
						unset($arm_entry_values['user_pass']);
					}
					if(!empty($arm_entry_values) && isset($arm_entry_values['repeat_pass'])){
						unset($arm_entry_values['repeat_pass']);
					}

					$user_id       = $entry_data['arm_user_id'];
					$entry_values  = maybe_unserialize( $entry_data['arm_entry_value'] );
					do_action('arm_payment_log_entry', 'bank_transfer', 'Bank Transfer Entry values', 'armember', $arm_entry_values, $arm_lite_debug_payment_log_id); //phpcs:ignore
					$payment_cycle = $entry_values['arm_selected_payment_cycle'];

					$setup_id = ( isset( $entry_values['setup_id'] ) && ! empty( $entry_values['setup_id'] ) ) ? $entry_values['setup_id'] : 0;
					$plan_id  = ( ! empty( $posted_data['subscription_plan'] ) ) ? intval( $posted_data['subscription_plan'] ) : 0;
					if ( $plan_id == 0 ) {
						$plan_id = ( ! empty( $posted_data['_subscription_plan'] ) ) ? intval( $posted_data['_subscription_plan'] ) : 0;
					}
					$plan = new ARM_Plan_Lite( $plan_id );

					$payment_mode = 'one_time';
					if ( $plan->is_recurring() ) {
						$payment_mode = 'manual_subscription';
					}

					$arm_user_old_plan = ( isset( $posted_data['old_plan_id'] ) && ! empty( $posted_data['old_plan_id'] ) ) ? explode( ',', $posted_data['old_plan_id'] ) : array();
					if ( ! empty( $arm_user_old_plan ) ) {
						if ( in_array( $plan_id, $arm_user_old_plan ) ) {
							$is_recurring_payment = $arm_subscription_plans->arm_is_recurring_payment_of_user( $user_id, $plan_id, $payment_mode );
							if ( $is_recurring_payment ) {
								$planData      = get_user_meta( $user_id, 'arm_user_plan_' . $plan_id, true );
								$oldPlanDetail = $planData['arm_current_plan_detail'];
								if ( ! empty( $oldPlanDetail ) ) {
									$plan = new ARM_Plan_Lite( 0 );
									$plan->init( (object) $oldPlanDetail );
								}
							}
						}
					}

					if ( $plan->is_recurring() ) {
						$recurring_data = $plan->prepare_recurring_data( $payment_cycle );
						$amount         = $recurring_data['amount'];
					} else {
						 $amount = $plan->amount;
					}
					$amount                        = str_replace( ',', '', $amount );
					$arm_extra_vars                = array();
					$arm_extra_vars['plan_amount'] = $amount;

					if ( ! $plan->is_recurring() || $payment_mode == 'manual_subscription' ) {

						$bank_info    = isset( $posted_data['bank_transfer'] ) ? $posted_data['bank_transfer'] : array();
						$arm_is_trial = '0';

						$arm_user_old_plan = ( ! empty( $arm_user_old_plan ) ) ? $arm_user_old_plan : array();
						if ( $plan->is_recurring() && $plan->has_trial_period() && empty( $arm_user_old_plan ) ) {

							$arm_is_trial                   = '1';
							$arm_extra_vars['trial']        = $recurring_data['trial'];
							$arm_extra_vars['arm_is_trial'] = $arm_is_trial;

							$amount = $plan->options['trial']['amount'];
						}

						$amount                        = number_format( (float) $amount, 2, '.', '' );
						$arm_extra_vars['paid_amount'] = $amount;
						$arm_first_name                = '';
						if ( isset( $posted_data['first_name'] ) && isset( $posted_data['last_name'] ) ) {
							$arm_first_name = $posted_data['first_name'];
							$arm_last_name  = $posted_data['last_name'];
						} elseif ( ! empty( $user_id ) ) {
							$user_detail    = get_userdata( $user_id );
							$arm_first_name = $user_detail->first_name;
							$arm_last_name  = $user_detail->last_name;
						}
						$payment_data = array(
							'arm_user_id'                  => $user_id,
							'arm_first_name'               => $arm_first_name,
							'arm_last_name'                => $arm_last_name,
							'arm_plan_id'                  => $plan->ID,
							'arm_old_plan_id'              => isset( $posted_data['old_plan_id'] ) ? $posted_data['old_plan_id'] : 0,
							'arm_payer_email'              => $entry_data['arm_entry_email'],
							'arm_transaction_id'           => ( isset( $bank_info['transaction_id'] ) && $amount > 0 ) ? $bank_info['transaction_id'] : '-',
							'arm_bank_name'                => ( isset( $bank_info['bank_name'] ) ) ? $bank_info['bank_name'] : '',
							'arm_account_name'             => ( isset( $bank_info['account_name'] ) ) ? $bank_info['account_name'] : '',
							'arm_additional_info'          => ( isset( $bank_info['additional_info'] ) ) ? $bank_info['additional_info'] : '',
							'arm_payment_transfer_mode'    => ( isset( $bank_info['transfer_mode'] ) ) ? $bank_info['transfer_mode'] : '',
							'arm_amount'                   => $amount,
							'arm_payment_gateway'          => 'bank_transfer',
							'arm_payment_type'             => ( $payment_mode == 'manual_subscription' ) ? 'subscription' : 'one_time',
							'arm_transaction_payment_type' => ( $payment_mode == 'manual_subscription' ) ? 'subscription' : 'one_time',
							'arm_payment_mode'             => $payment_mode,
							'arm_payment_cycle'            => $payment_cycle,
							'arm_currency'                 => $this->arm_get_global_currency(),
							'arm_extra_vars'               => maybe_serialize( $arm_extra_vars ),

							'arm_transaction_status'       => 0,
							'arm_is_trial'                 => $arm_is_trial,
							'arm_created_date'             => current_time( 'mysql' ),
							'arm_payment_date'             => current_time( 'mysql' ),
						);
						do_action( 'arm_after_bank_transfer_payment', $plan, $payment_mode, $amount, '', $arm_is_trial );
						do_action( 'arm_before_add_transaction', $payment_data );

						$arm_last_invoice_id = get_option( 'arm_last_invoice_id', 0 );
						$arm_last_invoice_id++;
						$payment_data['arm_invoice_id'] = $arm_last_invoice_id;
                        do_action('arm_payment_log_entry', 'bank_transfer', 'Save payment log data', 'armember', $payment_data, $arm_lite_debug_payment_log_id);

						$payment_log = $wpdb->insert( $ARMemberLite->tbl_arm_payment_log, $payment_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

						do_action( 'arm_after_add_transaction', $payment_data );
						$payment_log_id = $wpdb->insert_id;
						$payment_done   = array();
						if ( $payment_log_id ) {
							update_option( 'arm_last_invoice_id', $arm_last_invoice_id );
							/*
							if ($coupon_code != '') {

								$arm_manage_coupons->arm_update_coupon_used_count($coupon_code);
							}*/
							$payment_done = array(
								'status'   => true,
								'log_id'   => $payment_log_id,
								'entry_id' => $entry_id,
							);
						}
					} else {
						$err_msg      = esc_html__( 'Selected plan is not valid for bank transfer.', 'armember-membership' );
						$payment_done = array(
							'status' => false,
							'error'  => $err_msg,
						);
					}
				}
			}
		}

		function arm_get_entry_data_by_id( $entry_id = 0 ) {
			global $wpdb, $ARMemberLite, $arm_global_settings, $arm_subscription_plans;
			$entry_data = array();
			if ( ! empty( $entry_id ) && $entry_id != 0 ) {
				$entry_data = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$ARMemberLite->tbl_arm_entries."` WHERE `arm_entry_id` = %d LIMIT 1",$entry_id), ARRAY_A );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_entries is a table name
				if ( ! empty( $entry_data ) ) {
					$entry_data['arm_description'] = maybe_unserialize( $entry_data['arm_description'] );
					$entry_data['arm_entry_value'] = maybe_unserialize( $entry_data['arm_entry_value'] );
				}
			}
			return $entry_data;
		}

		function arm_get_credit_card_box( $type = 'stripe', $column_type = '1', $fieldPosition = 'left', $errPos = 'right' ) {
			global $wpdb, $ARMemberLite, $arm_global_settings, $arm_subscription_plans;
			$type          = ( ! empty( $type ) ) ? $type : 'no_gateway';
			$gateways      = $this->arm_get_all_payment_gateways();
			$gateways_opts = $gateways[ $type ];
			$cc_html       = '';
			$ccFieldsHtml  = '';
			$ccFieldsHtml .= apply_filters( 'arm_add_before_cc_field_filter', $ccFieldsHtml );

			$arm_card_holder_label_filter = apply_filters( 'arm_payment_card_holder_filter', $allowed_arr = array(), $type );

			$field_random_id = arm_generate_random_code();

			foreach ( array( 'card_holder_name', 'card_number', 'exp_month', 'exp_year', 'cvc' ) as $key ) {
				$fieldLabel = $fieldClass = $fieldAttr = $validation = $fieldDesc = $fieldid = '';
				switch ( $key ) {
					case 'card_number':
						$fieldLabel           = ! empty( $gateways_opts['cc_label'] ) ? stripslashes( $gateways_opts['cc_label'] ) : esc_html__( 'Credit Card Number', 'armember-membership' );
						$fieldDesc            = ! empty( $gateways_opts['cc_desc'] ) ? stripslashes( $gateways_opts['cc_desc'] ) : '';
						$field_min_length_msg = esc_html__( 'Please enter at least 13 digits.', 'armember-membership' );
						$field_mx_length_msg  = esc_html__( 'Maximum 19 digits allowed.', 'armember-membership' );
						$fieldAttr            = 'name="' . esc_attr($type) . '[' . esc_attr($key) . ']" minlength="13" data-validation-minlength-message="' . esc_attr($field_min_length_msg) . '" maxlength="19" data-validation-maxlength-message="' . esc_attr($field_mx_length_msg) . '" onkeydown="armvalidatenumber(event);"';
						$fieldAttr           .= ' data-paymentgateway="' . esc_attr($type) . '" ';

						$err_msg  = $arm_global_settings->common_message['arm_blank_credit_card_number'];
						$cc_error = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'This field can not be left blank', 'armember-membership' );

						$fieldAttr .= ' required data-validation-required-message="' . esc_attr($cc_error) . '"';
						$fieldClass = ' cardNumber';

						$err_msg  = $arm_global_settings->common_message['arm_invalid_credit_card'];
						$ec_error = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'Please enter correct card details.', 'armember-membership' );
						$fieldid  = 'arm_' . esc_attr($type) . '_card_number';
						break;
					case 'cvc':
						$fieldLabel          = ! empty( $gateways_opts['cvv_label'] ) ? stripslashes( $gateways_opts['cvv_label'] ) : esc_html__( 'CVV Code', 'armember-membership' );
						$fieldDesc           = ! empty( $gateways_opts['cvv_desc'] ) ? stripslashes( $gateways_opts['cvv_desc'] ) : '';
						$field_mx_length_msg = esc_html__( 'Maximum 4 digits allowed.', 'armember-membership' );
						$fieldAttr           = 'name="' . esc_attr($type) . '[' . esc_attr($key) . ']"  onkeydown="armvalidatenumber(event);" maxlength="4" data-validation-maxlength-message="' . esc_attr($field_mx_length_msg) . '"';
						$err_msg             = $arm_global_settings->common_message['arm_blank_cvc_number'];
						$cvc_error           = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'This field can not be left blank', 'armember-membership' );

						$fieldAttr .= ' required data-validation-required-message="' . esc_attr($cvc_error) . '"';
						$fieldClass = ' cardCVC';
						$fieldid    = 'arm_' . esc_attr($type) . '_cvc';
						break;
					case 'exp_month':
						$fieldLabel          = ! empty( $gateways_opts['em_label'] ) ? stripslashes( $gateways_opts['em_label'] ) : esc_html__( 'Expiration Month', 'armember-membership' );
						$fieldDesc           = ! empty( $gateways_opts['em_desc'] ) ? stripslashes( $gateways_opts['em_desc'] ) : '';
						$field_mx_length_msg = esc_html__( 'Maximum 2 digits allowed.', 'armember-membership' );
						$fieldAttr           = 'name="' . esc_attr($type) . '[' . esc_attr($key) . ']" onkeydown="armvalidatenumber(event);" maxlength="2" data-validation-maxlength-message="' . esc_attr($field_mx_length_msg) . '" size="2"';
						$err_msg             = $arm_global_settings->common_message['arm_blank_expire_month'];
						$em_error            = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'This field can not be left blank', 'armember-membership' );
						$fieldAttr          .= ' required data-validation-required-message="' . esc_attr($em_error) . '"';
						$fieldClass          = ' card-expiry-month';
						$fieldid             = 'arm_' . esc_attr($type) . '_exp_month';

						break;
					case 'exp_year':
						$fieldLabel          = ! empty( $gateways_opts['ey_label'] ) ? stripslashes( $gateways_opts['ey_label'] ) : esc_html__( 'Expiration Year', 'armember-membership' );
						$fieldDesc           = ! empty( $gateways_opts['ey_desc'] ) ? stripslashes( $gateways_opts['ey_desc'] ) : '';
						$field_mx_length_msg = esc_html__( 'Maximum 4 digits allowed.', 'armember-membership' );
						$fieldAttr           = 'name="' . esc_attr($type) . '[' . esc_attr($key) . ']" onkeydown="armvalidatenumber(event);" maxlength="4" data-validation-maxlength-message="' . esc_attr($field_mx_length_msg) . '" size="4"';
						$err_msg             = $arm_global_settings->common_message['arm_blank_expire_year'];
						$ey_error            = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'This field can not be left blank', 'armember-membership' );
						$fieldAttr          .= ' required data-validation-required-message="' . esc_attr($ey_error) . '"';
						$fieldClass          = ' card-expiry-year';
						$fieldid             = 'arm_' . esc_attr($type) . '_exp_year';
						break;
					default:
						break;
				}
				if ( $type == 'authorize_net' && $key == 'card_holder_name' ) {
				} else {
					$fieldid       = $fieldid . '_' . esc_attr($field_random_id);
					$ccFieldsHtml .= '<div class="arm-control-group arm_cc_field_wrapper arm-df__form-group arm-df__form-group_text">';
					if ( $form_style != 'writer_border' ) {
						$ccFieldsHtml     .= '<div class="arm_form_label_wrapper arm-df__field-label arm_form_member_field_text">';
							$ccFieldsHtml .= '<label class="arm_form_field_label_text">' . esc_html($fieldLabel) . '</label>';
						$ccFieldsHtml     .= '</div>';
					}
					// $ccFieldsHtml .= '<div class="arm_label_input_separator"></div>';
					$ccFieldsHtml .= '<div class="arm-df__form-field">';
					$ccFieldsHtml .= '<div class="arm-df__form-field-wrap arm-controls arm-df__form-field-wrap_' . $key . '">';
					if ( $form_style != 'writer_border' ) {
						$ccFieldsHtml .= '<label class="arm-df__label-text" for="' . esc_attr($fieldid) . '" >' . esc_html($fieldLabel) . '</label>';
					}
					$ccFieldsHtml .= '<input type="text" id="' . esc_attr($fieldid) . '" value="" class="field_' . esc_attr($type) . ' arm-df__form-control ' . esc_attr($fieldClass) . '" ' . $fieldAttr . '>';
					if ( $form_style == 'writer_border' ) {
						$ccFieldsHtml .= '<div class="arm-notched-outline">
                                        <div class="arm-notched-outline__leading"></div>
                                        <div class="arm-notched-outline__notch">
                                            <label class="arm-df__label-text" for="' . esc_attr($fieldid) . '">' . esc_html($fieldLabel) . '</label></div>
                                            <div class="arm-notched-outline__trailing"></div>
                                        </div>';
					}

					$ccFieldsHtml .= $validation;
					$ccFieldsHtml .= '</div>';
					if ( ! empty( $fieldDesc ) ) {
						$ccFieldsHtml .= '<span>' . $fieldDesc . '</span>';
					}
					$ccFieldsHtml .= '</div>';
					$ccFieldsHtml .= '</div>';
				}
			}
			$ccFieldsHtml = apply_filters( 'arm_add_cc_fields_outside', $ccFieldsHtml, $type, $gateways_opts );
			$cc_html                 .= '<div class="arm-df-wrapper arm_msg_pos_' . esc_attr($errPos) . '">';
			$cc_html                 .= '<div class="arm_cc_fields_container arm_' . esc_attr($type) . '_fields arm-df__fields-wrapper arm_field_position_' . esc_attr($fieldPosition) . '">';
			$cc_html                 .= '<span class="payment-errors"></span>';
			$cc_html                 .= $ccFieldsHtml;
			$cc_html                 .= '</div>';
			$cc_html                 .= '<div class="armclear"></div>';
			$cc_html                 .= '</div>';
			return $cc_html;
		}

		function arm_get_bank_transfer_form( $options = array(), $fieldPosition = 'left', $errPos = 'right', $form_id = 0, $form_settings = array() ) {
			$gateways      = $this->arm_get_all_payment_gateways();
			$gateways_opts = $gateways['bank_transfer'];
			$bt_fields     = isset( $options['fields'] ) ? $options['fields'] : array();
			$bt_form       = '<div class="arm-df-wrapper arm_msg_pos_' . $errPos . '">';
			$bt_form      .= '<div class="arm_bank_transfer_fields_container arm-df__fields-wrapper arm_field_position_' . $fieldPosition . '">';
			/* Transaction ID Input */
			$transaction_id_field_label = ! empty( $gateways_opts['transaction_id_label'] ) ? stripslashes( $gateways_opts['transaction_id_label'] ) : esc_html__( 'Transaction ID', 'armember-membership' );

			$field_random_id = arm_generate_random_code();

			$formStyles                = ( isset( $form_settings['style'] ) && ! empty( $form_settings['style'] ) ) ? $form_settings['style'] : array();
			$arm_allow_notched_outline = 0;
			if ( $formStyles['form_layout'] == 'writer_border' ) {
				$arm_allow_notched_outline = 1;
			}
			if ( isset( $bt_fields['transaction_id'] ) && $bt_fields['transaction_id'] == '1' ) {
				/*if there is material or not */
				$field_random_id_html = 'arm_bank_transfer_transaction_id_' . $form_id . '_' . $field_random_id;

				$arm_field_wrap_active_class = $ffield_label_html = $ffield_label = '';
				if ( ! empty( $arm_allow_notched_outline ) ) {
					$arm_field_wrap_active_class = ( ! empty( $field_val ) ) ? ' arm-df__form-material-field-wrap' : '';

					$ffield_label_html  = '<div class="arm-notched-outline">';
					$ffield_label_html .= '<div class="arm-notched-outline__leading"></div>';
					$ffield_label_html .= '<div class="arm-notched-outline__notch">';

					$ffield_label_html .= '<label class="arm-df__label-text arm_material_label" for=' . esc_attr($field_random_id_html) . '>' . $transaction_id_field_label . '</label>';

					$ffield_label_html .= '</div>';
					$ffield_label_html .= '<div class="arm-notched-outline__trailing"></div>';
					$ffield_label_html .= '</div>';

					$ffield_label = $ffield_label_html;
				} else {
					$class_label = '';
					if ( $formStyles['form_layout'] == 'writer' ) {
						$class_label  = 'arm-df__label-text';
						$ffield_label = '<label class="' . esc_attr($class_label) . '" for=' . esc_attr($field_random_id_html) . '>' . esc_html($transaction_id_field_label) . '</label>';
					}
				}

				$bt_form                    .= '<div class="arm-control-group arm_bt_field_wrapper arm-df__form-group arm-df__form-group_text">';
				$bt_form                    .= '<div class="arm_form_label_wrapper arm-df__field-label arm_form_member_field_text">';
				$bt_form                    .= '<label class="arm_form_field_label_text" for=' . esc_attr($field_random_id_html) . '>' . esc_html($transaction_id_field_label) . '</label>';
				$bt_form                    .= '</div>';
				$bt_form                    .= '<div class="arm_label_input_separator"></div>';
				$bt_form                    .= '<div class="arm-df__form-field">';
				$bt_form                    .= '<div class="arm-df__form-field-wrap arm-controls arm-df__form-field-wrap_transaction_id">';
				$bt_transaction_id_error_msg = esc_html__( 'Please enter', 'armember-membership' ) . ' ' . $transaction_id_field_label;
				$bt_form                    .= '<input type="text" id=' . esc_attr($field_random_id_html) . ' name="bank_transfer[transaction_id]" value="" class="field_bank_transfer arm_bank_transaction_id arm-df__form-control"  required data-validation-required-message="' . esc_attr				($bt_transaction_id_error_msg) . '" >';
				$bt_form                    .= $ffield_label;

				$bt_form .= '<span class="arm_bank_transaction_id_error front_error notify_msg" style="display:none;">' . esc_html__( 'Please enter', 'armember-membership' ) . ' ' . esc_html($transaction_id_field_label) . '.</span>';
				$bt_form .= '</div>';
				$bt_form .= '</div>';
				$bt_form .= '</div>';
			}

			if ( isset( $bt_fields['bank_name'] ) && $bt_fields['bank_name'] == '1' ) {

				$field_random_id_html = 'arm_bank_transfer_bank_name_' . $form_id . '_' . $field_random_id;

				$bank_name_field_label = ! empty( $gateways_opts['bank_name_label'] ) ? stripslashes( $gateways_opts['bank_name_label'] ) : esc_html__( 'Bank Name', 'armember-membership' );

				$arm_field_wrap_active_class = $ffield_label_html = $ffield_label = '';
				if ( ! empty( $arm_allow_notched_outline ) ) {
					$arm_field_wrap_active_class = ( ! empty( $field_val ) ) ? ' arm-df__form-material-field-wrap' : '';

					$ffield_label_html  = '<div class="arm-notched-outline">';
					$ffield_label_html .= '<div class="arm-notched-outline__leading"></div>';
					$ffield_label_html .= '<div class="arm-notched-outline__notch">';

					$ffield_label_html .= '<label class="arm-df__label-text arm_material_label" for=' . esc_attr($field_random_id_html) . '>' . esc_html($bank_name_field_label) . '</label>';

					$ffield_label_html .= '</div>';
					$ffield_label_html .= '<div class="arm-notched-outline__trailing"></div>';
					$ffield_label_html .= '</div>';

					$ffield_label = $ffield_label_html;
				} else {
					$class_label = '';
					if ( $formStyles['form_layout'] == 'writer' ) {
						$class_label  = 'arm-df__label-text';
						$ffield_label = '<label class="' .esc_attr($class_label) . '" for=' . esc_attr($field_random_id_html) . '>' . esc_html($bank_name_field_label) . '</label>';
					}
				}
				/* Bank Name Input */

				$bt_form               .= '<div class="arm-control-group arm_bt_field_wrapper arm-df__form-group arm-df__form-group_text">';
				$bt_form               .= '<div class="arm_form_label_wrapper arm-df__field-label arm_form_member_field_text">';
				$bt_form               .= '<label class="arm_form_field_label_text" for=' . esc_attr($field_random_id_html) . '>' . esc_html($bank_name_field_label) . '</label>';
				$bt_form               .= '</div>';
				$bt_form               .= '<div class="arm_label_input_separator"></div>';
				$bt_form               .= '<div class="arm-df__form-field">';
				$bt_form               .= '<div class="arm-df__form-field-wrap arm-controls arm-df__form-field-wrap_transaction_id">';
				$bt_bank_name_error_msg = esc_html__( 'Please enter', 'armember-membership' ) . ' ' . $bank_name_field_label;
				$bt_form               .= '<input type="text" id=' . esc_attr($field_random_id_html) . ' name="bank_transfer[bank_name]" value="" class="field_bank_transfer arm-df__form-control" required data-validation-required-message="' . esc_attr($bt_bank_name_error_msg) . '">';
				$bt_form               .= $ffield_label;

				$bt_form .= '<span class="arm_bank_transaction_id_error front_error notify_msg" style="display:none;">' . esc_html__( 'Please enter', 'armember-membership' ) . ' ' . esc_attr($bank_name_field_label) . '.</span>';
				$bt_form .= '</div>';
				$bt_form .= '</div>';
				$bt_form .= '</div>';
			}
			if ( isset( $bt_fields['account_name'] ) && $bt_fields['account_name'] == '1' ) {
				/* Account Name Input */
				$account_name_field_label    = ! empty( $gateways_opts['account_name_label'] ) ? stripslashes( $gateways_opts['account_name_label'] ) : esc_html__( 'Account Holder Name', 'armember-membership' );
				$arm_field_wrap_active_class = $ffield_label_html = $ffield_label = '';
				if ( ! empty( $arm_allow_notched_outline ) ) {
					$arm_field_wrap_active_class = ( ! empty( $field_val ) ) ? ' arm-df__form-material-field-wrap' : '';

					$ffield_label_html  = '<div class="arm-notched-outline">';
					$ffield_label_html .= '<div class="arm-notched-outline__leading"></div>';
					$ffield_label_html .= '<div class="arm-notched-outline__notch">';

					$ffield_label_html .= '<label class="arm-df__label-text arm_material_label" for=' . esc_attr($field_random_id_html) . '>' . esc_html($account_name_field_label) . '</label>';

					$ffield_label_html .= '</div>';
					$ffield_label_html .= '<div class="arm-notched-outline__trailing"></div>';
					$ffield_label_html .= '</div>';

					$ffield_label = $ffield_label_html;
				} else {
					$class_label = '';
					if ( $formStyles['form_layout'] == 'writer' ) {
						$class_label  = 'arm-df__label-text';
						$ffield_label = '<label class="' . esc_attr($class_label) . '" for=' . esc_attr($field_random_id_html) . '>' . esc_html($account_name_field_label) . '</label>';
					}
				}
				/* Account Name Input */
				$bt_form                  .= '<div class="arm-control-group arm_bt_field_wrapper arm-df__form-group arm-df__form-group_text">';
				$bt_form                  .= '<div class="arm_form_label_wrapper arm-df__field-label arm_form_member_field_text">';
				$bt_form                  .= '<label class="arm_form_field_label_text" for=' . esc_attr($field_random_id_html) . '>' . esc_html($account_name_field_label) . '</label>';
				$bt_form                  .= '</div>';
				$bt_form                  .= '<div class="arm_label_input_separator"></div>';
				$bt_form                  .= '<div class="arm-df__form-field">';
				$bt_form                  .= '<div class="arm-df__form-field-wrap arm-controls arm-df__form-field-wrap_transaction_id">';
				$bt_account_name_error_msg = esc_html__( 'Please enter', 'armember-membership' ) . ' ' . $account_name_field_label;
				$bt_form                  .= '<input type="text" id=' . esc_attr($field_random_id_html) . ' name="bank_transfer[account_name]" value="" class="field_bank_transfer arm-df__form-control" required data-validation-required-message="' . esc_attr($bt_account_name_error_msg) . '">';
				$bt_form                  .= $ffield_label;

				$bt_form .= '<span class="arm_bank_transaction_id_error front_error notify_msg" style="display:none;">' . esc_html__( 'Please enter', 'armember-membership' ) . ' ' . esc_attr($account_name_field_label) . '.</span>';
				$bt_form .= '</div>';
				$bt_form .= '</div>';
				$bt_form .= '</div>';
			}
			if ( isset( $bt_fields['additional_info'] ) && $bt_fields['additional_info'] == '1' ) {

				$additional_info_field_label = ! empty( $gateways_opts['additional_info_label'] ) ? stripslashes( $gateways_opts['additional_info_label'] ) : esc_html__( 'Additional Note', 'armember-membership' );
				$field_random_id_html        = 'arm_bank_transfer_additional_info_' . $form_id . '_' . $field_random_id;
				$arm_field_wrap_active_class = $ffield_label_html = $ffield_label = '';
				if ( ! empty( $arm_allow_notched_outline ) ) {
					$arm_field_wrap_active_class = ( ! empty( $field_val ) ) ? ' arm-df__form-material-field-wrap' : '';
					$ffield_label_html           = '<div class="arm-notched-outline">';
					$ffield_label_html          .= '<div class="arm-notched-outline__leading"></div>';
					$ffield_label_html          .= '<div class="arm-notched-outline__notch">';
					$ffield_label_html          .= '<label class="arm-df__label-text arm_material_label ' . esc_attr($arm_field_wrap_active_class) . '" for=' . esc_attr($field_random_id_html) . '>' . esc_html($additional_info_field_label) . '</label>';

					$ffield_label_html .= '</div>';
					$ffield_label_html .= '<div class="arm-notched-outline__trailing"></div>';
					$ffield_label_html .= '</div>';

					$ffield_label = $ffield_label_html;
				} else {

					if ( $formStyles['form_layout'] == 'writer' ) {

						$ffield_label = '<label class="arm-df__label-text ' . esc_attr($arm_field_wrap_active_class) . '" for=' . esc_attr($field_random_id_html) . '>' . esc_html($additional_info_field_label) . '</label>';
					}
				}
				/* Additional Note Input */

				$bt_form .= '<div class="arm-control-group arm_bt_field_wrapper arm-df__form-group arm-df__form-group_textarea">';
				$bt_form .= '<div class="arm_form_label_wrapper arm-df__field-label arm_form_member_field_text">';
				$bt_form .= '<label class="arm_form_field_label_text" for=' . esc_attr($field_random_id_html) . '>' . esc_html($additional_info_field_label) . '</label>';
				$bt_form .= '</div>';
				$bt_form .= '<div class="arm_label_input_separator"></div>';
				$bt_form .= '<div class="arm-df__form-field">';

				$bt_form                     .= '<div class="arm-df__form-field-wrap arm-df__form-field-wrap_textarea arm-controls arm-df__form-field-wrap_transaction_id">';
				$bt_additional_info_error_msg = esc_html__( 'Please enter', 'armember-membership' ) . ' ' . $additional_info_field_label;
				$bt_form                     .= '<textarea id=' . esc_attr($field_random_id_html) . ' class="arm_textarea arm-df__form-control field_bank_transfer" name="bank_transfer[additional_info]" required data-validation-required-message="' . esc_attr($bt_additional_info_error_msg) . '" rows="3" cols="10"></textarea>';
				$bt_form                     .= $ffield_label;

				$bt_form .= '<span class="arm_bank_transaction_id_error front_error notify_msg" style="display:none;">' . esc_html__( 'Please enter', 'armember-membership' ) . ' ' . esc_html($additional_info_field_label) . '.</span>';
				$bt_form .= '</div>';
				$bt_form .= '</div>';
				$bt_form .= '</div>';
			}
			if ( isset( $bt_fields['transfer_mode'] ) && $bt_fields['transfer_mode'] == '1' ) {

				/* Payment transfer mode Input */
				$field_random_id_html = 'arm_bank_transfer_transfer_mode_' . $form_id . '_' . $field_random_id;

				$transfer_mode = $this->arm_get_bank_transfer_mode_options();
				// $transfer_mode_li = '<option class="armMDOption armSelectOption arm_payment_transfer_mode_option_'.$form_id.'" role="option" aria-selected="false" value="">' . esc_html__('Select Payment Mode', 'armember-membership') . '</option>';

				$transfer_mode_option_val_label = $transfer_mode_option_val_selected = !empty($gateways_opts['transfer_mode_label']) ? stripslashes($gateways_opts['transfer_mode_label']) : esc_html__( 'Select Payment Mode', 'armember-membership' );

				$transfer_mode_li = '<li class="arm__dc--item arm_payment_transfer_mode_option_' . esc_attr($form_id) . '" data-label="' . esc_attr($transfer_mode_option_val_label) . '" data-value="">' . esc_html($transfer_mode_option_val_label) . '</li>';

				$transfer_mode_option_arr = ! empty( $gateways_opts['fields']['transfer_mode_option'] ) ? $gateways_opts['fields']['transfer_mode_option'] : array();
				$drpdown_cntr             = 0;
				$default_pg               = '';
				foreach ( $transfer_mode as $key => $value ) {
					if ( in_array( $key, $transfer_mode_option_arr ) ) {
						$transfer_mode_option_val = ! empty( $gateways_opts['fields']['transfer_mode_option_label'][ $key ] ) ? $gateways_opts['fields']['transfer_mode_option_label'][ $key ] : $value;
						if ( $drpdown_cntr == 0 ) {
							$default_pg = $transfer_mode_option_val;
						}
						// $transfer_mode_li .= '<option class="arm_payment_transfer_mode_option_'.$form_id.'" role="option" selected="false" value="' . esc_attr($key) . '">' . $transfer_mode_option_val . '</option>';
						$transfer_mode_li .= '<li class="arm__dc--item arm_payment_transfer_mode_option_' . esc_attr($form_id) . '" data-label="' . esc_html( $transfer_mode_option_val ) . '" data-value="' . esc_attr( $transfer_mode_option_val ) . '">' . esc_html( $transfer_mode_option_val ) . '</li>';
					}
				}

				$ng_change_func           = '';
				$name                     = 'bank_transfer[transfer_mode]';
				$payment_mode_field_label = ! empty( $gateways_opts['transfer_mode_label'] ) ? stripslashes( $gateways_opts['transfer_mode_label'] ) : esc_html__( 'Payment Mode', 'armember-membership' );
				$arm_bk_active_class      = '';
				if ( $formStyles['form_layout'] == 'writer' || $formStyles['form_layout'] == 'writer_border' ) {
					$arm_bk_active_class = 'active';
				}
				$bt_transfer_mode_error_msg = esc_html__( 'Please select', 'armember-membership' ) . ' ' . $payment_mode_field_label;
				$field_attr                 = 'name="' . esc_attr( $name ) . '" class="arm-selectpicker-input-control arm-df__form-control" ';
				$field_attr                .= ' aria-label="' . esc_attr($payment_mode_field_label) . '"';
				$required                   = ' required="" data-validation-required-message="' . $bt_transfer_mode_error_msg . '" ';
				$writter_class              = ( ! empty( $form ) && isset( $formStyles['rtl'] ) && $formStyles['rtl'] == '1' ) ? 'armSelectOptionRTL' : 'armSelectOptionLTR';

				if ( ! empty( $arm_allow_notched_outline ) ) {
					$arm_bk_active_class         = '';
					$arm_field_wrap_active_class = ( ! empty( $default_pg ) ) ? ' arm-df__form-material-field-wrap' : '';
					if ( $arm_field_wrap_active_class != '' ) {
						$arm_bk_active_class = 'active';
					}
					$ffield_label_html  = '<div class="arm-notched-outline">';
					$ffield_label_html .= '<div class="arm-notched-outline__leading"></div>';
					$ffield_label_html .= '<div class="arm-notched-outline__notch">';

					$ffield_label_html .= '<label class="arm-df__label-text ' . esc_attr($arm_bk_active_class) . '" for=' . esc_attr($field_random_id_html) . '>' . esc_html($payment_mode_field_label) . '</label>';

					$ffield_label_html .= '</div>';
					$ffield_label_html .= '<div class="arm-notched-outline__trailing"></div>';
					$ffield_label_html .= '</div>';

					$ffield_label = $ffield_label_html;
				} else {
					$class_label = '';
					if ( $formStyles['form_layout'] == 'writer' ) {
						if ( ! empty( $default_pg ) ) {
							$arm_bk_active_class = 'active';
						}
						$class_label  = 'arm-df__label-text ' . esc_attr($arm_bk_active_class);
						$ffield_label = '<label class="' . esc_attr($class_label) . '" for=' . esc_attr($field_random_id_html) . '>' . esc_attr($payment_mode_field_label) . '</label>';
					}
				}

				$bt_form .= '<div class="arm-control-group arm_bt_field_wrapper arm-df__form-group arm-df__form-group_select">';
				$bt_form .= '<div class="arm_form_label_wrapper arm-df__field-label arm_form_member_field_text">';
				$bt_form .= '<label class="arm_form_field_label_text" for=' . esc_attr($field_random_id_html) . '>' . esc_html($payment_mode_field_label) . '</label>';
				$bt_form .= '</div>';
				$bt_form .= '<div class="arm_label_input_separator"></div>';
				$bt_form .= '<div class="arm-df__form-field">';
				$bt_form .= '<div class="arm-df__form-field-wrap arm-controls arm-df__form-field-wrap_select ' . esc_attr($arm_field_wrap_active_class) . '">';

				$bt_form .= '<input type="text" id="' . esc_attr( $field_random_id_html ) . '" ' . $field_attr . esc_attr($ng_change_func) . ' value="' . esc_attr($default_pg) . '" ' . $required . '/>';
				$bt_form .= $ffield_label;
				$bt_form .= '<dl class="arm-df__dropdown-control column_level_dd arm_member_form_dropdown ">';
				$bt_form .= '<dt class="arm__dc--head"><span class="arm__dc--head__title">' . esc_html($default_pg) . '</span><input type="text" style="display:none;" value="" class="arm-df__dc--head__autocomplete arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
				$bt_form .= '<dd class="arm__dc--items-wrap"><ul class="arm__dc--items" data-id="' . esc_attr( $field_random_id_html ) . '" style="display:none;">';
				$bt_form .= $transfer_mode_li;
				$bt_form .= '';
				$bt_form .= '</ul></dd>';
				$bt_form .= '</dl>';

				// $output .= '<label class="arm-df__label-text active" for="arm-df__form-control_'.$field_id.'">'.$value['label'].'</label>';

				// $bt_form .= '<label class="arm-df__label-text active" for='.$field_random_id_html.'>' . esc_html__('Select Payment Mode', 'armember-membership') . '</label>';
				// $bt_form .= '<select id='.$field_random_id_html.' class="" ' . $field_attr . $ng_change_func .'>';
				// need to check empty class

				// $bt_form .= $transfer_mode_li;

				// $bt_form .= '</select>';

				// $bt_form .= '<span class="arm_bank_transaction_id_error front_error notify_msg" style="display:none;">' . esc_html__('Please select', 'armember-membership') . " " . $payment_mode_field_label . '.</span>';
				// $bt_form .= '<input type="hidden" name="' . esc_attr($name) . '" value="">';
				$bt_form .= '</div>';
				$bt_form .= '</div>';
				$bt_form .= '</div>';

			}

			$bt_form .= '</div>';
			$bt_form .= '<div class="armclear"></div>';
			$bt_form .= '</div>';
			return $bt_form;
		}

		/**
		 * Get Currency Name/Label From Currency Code For Paypal
		 */
		function arm_paypal_currency_symbol() {

			/* 26 currency */
			$currency_symbol = array(

				'AUD' => '$',
				'BRL' => 'R$',
				'CAD' => '$',
				'CZK' => '&#75;&#269;',
				'DKK' => '&nbsp;&#107;&#114;',
				'EUR' => '&#128;',
				'HKD' => '&#20803;',
				'HUF' => '&#70;&#116;',
				'ILS' => '&#8362;',
				'JPY' => '&#165;',
				'MYR' => '&#82;&#77;',
				'MXN' => '&#36;',
				'TWD' => '&#36;',
				'NZD' => '&#36;',
				'NOK' => '&nbsp;&#107;&#114;',
				'PHP' => '&#80;&#104;&#8369;',
				'PLN' => '&#122;&#322;',
				'GBP' => '&#163;',
				'RUB' => '&#1088;&#1091;',
				'SGD' => '&#36;',
				'SEK' => '&nbsp;&#107;&#114;',
				'CHF' => '&#67;&#72;&#70;',
				'THB' => '&#3647;',
				'USD' => '$',
				'TRY' => '&#89;&#84;&#76;',
				'INR' => '&#8377;',
			);
			return $currency_symbol;
		}
		/**
		 * Get Currency Name/Label From Currency Code For Bank Transfer - Total 168
		 */
		function arm_bank_transfer_currency_symbol() {
			$currency_symbol = array(
				'AED' => '&#x62f;&#x2e;&#x625;',
				'AFN' => '&#1547;',
				'ALL' => 'L',
				'AMD' => '&#1423;',
				'ANG' => '$',
				'AOA' => 'Kz',
				'ARS' => '&#36;',
				'AUD' => '$',
				'AWG' => '&#x192;',
				'AZN' => 'maH',
				'BAM' => '&#x4b;&#x4d',
				'BBD' => 'Bds&#36;',
				'BDT' => '&#2547;',
				'BGN' => '&#1083;&#1074;.',
				'BHD' => '.&#x62f;.&#x628;',
				'BIF' => 'FBu',
				'BMD' => 'BD$',
				'BND' => 'B$',
				'BTN' => 'Nu.',
				'BOB' => 'Bs.',
				'BRL' => 'R$',
				'BSD' => '&#x0024;',
				'BTC' => '&#3647;',
				'BWP' => 'P',
				'BYN' => 'Br',
				'BYR' => 'Br',
				'BZD' => 'BZ$',
				'CAD' => '$',
				'CDF' => 'FC',
				'CHF' => '&#67;&#72;&#70;',
				'CLP' => '&#36;',
				'CNY' => '&#165;',
				'COP' => '&#36;',
				'CRC' => '&#8353;',
				'CUC' => '&#36;',
				'CUP' => '&#36;',
				'CVE' => 'Esc',
				'CZK' => '&#75;&#269;',
				'DJF' => 'Fdj',
				'DKK' => '&nbsp;&#107;&#114;',
				'DOP' => 'RD$',
				'DZD' => 'DA',
				'EGP' => 'E&#163;',
				'ETB' => 'Br',
				'ERN' => 'Nfk',
				'EUR' => '&#128;',
				'FJD' => 'FJ$',
				'FKP' => '&#xa3;',
				'GBP' => '&#163;',
				'GEL' => '&#x20be;',
				'GGP' => '&pound;',
				'GHS' => '&#x20b5;',
				'GIP' => '&#xa3;',
				'GMD' => 'D',
				'GNF' => 'FG',
				'GTQ' => 'Q',
				'GYD' => '&#x24;',
				'HKD' => '&#36;',
				'HNL' => 'L',
				'HRK' => 'kn',
				'HTG' => 'G',
				'HUF' => '&#70;&#116;',
				'IDR' => 'Rp',
				'ILS' => '&#8362;',
				'IMP' => '&pound;',
				'INR' => '&#8377;',
				'IQD' => '&#x639;.&#x62f;',
				'IRR' => '&#xfdfc;',
				'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
				'ISK' => '&nbsp;&#x6b;&#x72;',
				'JEP' => '&pound;',
				'JMD' => 'J$',
				'JOD' => '&#x62f;.&#x627;',
				'JPY' => '&#165;',
				'KES' => 'KSh',
				'KGS' => '&#x43b;&#x432;',
				'KHR' => '&#x17db;',
				'KMF' => 'CF',
				'KPW' => '&#x20a9;',
				'KRW' => '&#8361;',
				'KWD' => '&#x62f;.&#x643;',
				'KYD' => '$',
				'KZT' => '&#8376;',
				'LAK' => '&#8365;',
				'LBP' => 'L&#163;',
				'LKR' => '&#8360;',
				'LRD' => 'L$',
				'LSL' => 'L',
				'LYD' => '&#x644;.&#x62f;',
				'MAD' => '&#x2e;&#x62f;&#x2e;&#x645;',
				'MDL' => 'MDL',
				'MGA' => 'Ar',
				'MKD' => '&#x434;&#x435;&#x43d;',
				'MMK' => 'K',
				'MNT' => '&#x20ae;',
				'MOP' => 'P',
				'MRO' => 'UM',
				'MRU' => 'UM',
				'MUR' => '&#8360;',
				'MVR' => 'Rf',
				'MWK' => 'MK',
				'MXN' => '&#36;',
				'MYR' => '&#82;&#77;',
				'MZN' => '&#x4d;&#x54;',
				'NAD' => '$',
				'NGN' => '&#x20a6;',
				'NIO' => 'C$',
				'NOK' => '&nbsp;&#107;&#114;',
				'NPR' => '&#8360;',
				'NZD' => '&#36;',
				'OMR' => '&#x631;.&#x639;.',
				'PAB' => 'B/.',
				'PEN' => 'S/.',
				'PGK' => 'K',
				'PHP' => '&#8369;',
				'PKR' => '&#8360;',
				'PLN' => '&#122;&#322;',
				'PRB' => '&#x440;.',
				'PYG' => 'Gs',
				'QAR' => '&#65020;',
				'RMB' => '&yen;',
				'RON' => 'L',
				'RSD' => '&#x414;&#x438;&#x43d;&#x2e;',
				'RUB' => '&#1088;&#1091;',
				'RWF' => 'RF',
				'SAR' => '&#65020;',
				'SBD' => 'SI$',
				'SCR' => '&#8360;',
				'SEK' => '&nbsp;&#107;&#114;',
				'SGD' => '&#36;',
				'SGF' => 'S$',
				'SHP' => '&#xa3;',
				'SYP' => 'S&#163;',
				'TJS' => 'TSh',
				'SDG' => '&pound;SD',
				'SLL' => 'Le',
				'SOS' => 'S',
				'SRD' => '$',
				'SSP' => '&pound;',
				'STN' => 'Db',
				'STD' => 'Db',
				'SVC' => '$',
				'SZL' => 'SZL',
				'THB' => '&#3647;',
				'TMT' => 'm',
				'TND' => '&#x62f;.&#x62a;',
				'TOP' => 'T$',
				'TRY' => '&#89;&#84;&#76;',
				'TTD' => 'TT$',
				'TWD' => '&#36;',
				'TZS' => 'x',
				'UAH' => '&#8372;',
				'UGX' => 'USh',
				'USD' => '$',
				'UYU' => '$U',
				'UZS' => '&#x43b;&#x432;',
				'VEF' => 'Bs F',
				'VES' => 'Bs.S',
				'VND' => '&#8363;',
				'VUV' => 'VT',
				'WST' => 'T',
				'XAF' => 'FCFA',
				'XCD' => 'EC$',
				'XOF' => 'CFA',
				'XPF' => 'F',
				'YER' => '&#65020;',
				'ZAR' => 'R',
				'ZMW' => 'ZK',
			);
			return $currency_symbol;
		}



		function arm_check_currency_status( $arm_currency = 'USD' ) {
			global $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_membership_setup, $arm_capabilities_global;
		
			$response = array(
				'type' => 'error',
				'msg'  => esc_html__( 'Sorry, Something went wrong. Please try again.', 'armember-membership' ),
			);
			$currency = ( isset( $_REQUEST['arm_currency'] ) && ! empty( $_REQUEST['arm_currency'] ) ) ? sanitize_text_field( $_REQUEST['arm_currency'] ) : $arm_currency; //phpcs:ignore
			$message  = '';
			$notAllow = $notAllowSetups = array();
			if ( ! empty( $currency ) ) {
				if ( ! array_key_exists( $currency, $this->currency['paypal'] ) ) {
					$notAllow[] = 'paypal';
				}

				$notAllow = apply_filters( 'arm_currency_support', $notAllow, $currency );
				if ( ! empty( $notAllow ) ) {
					$message  = esc_html__( 'This currency is not supported by', 'armember-membership' );
					$message .= ' ' . implode( ', ', $notAllow ) . ' ';
					$message .= esc_html__( 'payment gateway(s). If you will save this settings, than those payment gateway(s) will be disable.', 'armember-membership' );
					$setups   = $wpdb->get_results( 'SELECT `arm_setup_name`, `arm_setup_modules` FROM `' . $ARMemberLite->tbl_arm_membership_setup . '` ORDER BY `arm_setup_id` DESC', ARRAY_A );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_membership_setup is a table name. No need to Prepare bcz no WHERE Clause in Query
					if ( ! empty( $setups ) ) {
						foreach ( $setups as $setup ) {
							$setupModules = maybe_unserialize( $setup['arm_setup_modules'] );
							if ( isset( $setupModules['modules']['gateways'] ) && ! empty( $setupModules['modules']['gateways'] ) ) {
								$diffPG = array_diff( $setupModules['modules']['gateways'], $notAllow );
								if ( empty( $diffPG ) || count( $diffPG ) <= 1 ) {
									$notAllowSetups[] = $setup['arm_setup_name'];
								}
							}
						}
						$notAllowSetups = $ARMemberLite->arm_array_unique( $notAllowSetups );
					}
					if ( ! empty( $notAllowSetups ) ) {
						$message .= ' ' . esc_html__( 'As well as following setup(s) will not work properly', 'armember-membership' );
						$message .= ': ' . implode( ', ', $notAllowSetups ) . '.';
					}
				}
				$response = array(
					'type' => 'success',
					'msg'  => $message,
				);
			}
			if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'arm_check_currency_status' ) { //phpcs:ignore
				$ARMemberLite->arm_check_user_cap( $arm_capabilities_global['arm_manage_payment_gateways'], '1' );
				echo wp_json_encode( $response );
				exit;
			}
			return $message;
		}

		function arm_check_currency_status_for_gateways( $arm_currency = 'USD' ) {
			global $wpdb, $ARMemberLite, $arm_slugs, $arm_global_settings, $arm_membership_setup;
			$response = array(
				'type' => 'error',
				'msg'  => esc_html__( 'Sorry, Something went wrong. Please try again.', 'armember-membership' ),
			);
			$currency = ( isset( $_REQUEST['arm_currency'] ) && ! empty( $_REQUEST['arm_currency'] ) ) ? sanitize_text_field( $_REQUEST['arm_currency'] ) : $arm_currency; //phpcs:ignore
			$message  = '';
			$notAllow = $notAllowSetups = array();

			if ( ! empty( $currency ) ) {
				if ( ! array_key_exists( $currency, $this->currency['paypal'] ) ) {
					$notAllow[] = 'paypal';
				}

				$notAllow = apply_filters( 'arm_currency_support', $notAllow, $currency );
				if ( ! empty( $notAllow ) ) {
					$message  = esc_html__( 'This currency is not supported by', 'armember-membership' );
					$message .= ' ' . implode( ', ', $notAllow ) . ' ';
					$message .= esc_html__( 'payment gateway. If you will save this settings, than those payment gateway will be disable.', 'armember-membership' );
					$setups   = $wpdb->get_results( 'SELECT `arm_setup_name`, `arm_setup_modules` FROM `' . $ARMemberLite->tbl_arm_membership_setup . '` ORDER BY `arm_setup_id` DESC', ARRAY_A );//phpcs:ignore --Reason $ARMemberLite->tbl_arm_membership_setup is a table name. No need to Prepare bcz no WHERE Clause in Query
					if ( ! empty( $setups ) ) {
						foreach ( $setups as $setup ) {
							$setupModules = maybe_unserialize( $setup['arm_setup_modules'] );
							if ( isset( $setupModules['modules']['gateways'] ) && ! empty( $setupModules['modules']['gateways'] ) ) {
								$diffPG = array_diff( $setupModules['modules']['gateways'], $notAllow );
								if ( empty( $diffPG ) || count( $diffPG ) <= 1 ) {
									$notAllowSetups[] = $setup['arm_setup_name'];
								}
							}
						}
						$notAllowSetups = $ARMemberLite->arm_array_unique( $notAllowSetups );
					}
					if ( ! empty( $notAllowSetups ) ) {
						$message .= ' ' . esc_html__( 'As well as following setup(s) will not work properly', 'armember-membership' );
						$message .= ': ' . implode( ', ', $notAllowSetups ) . '.';
					}
				}
				$response = array(
					'type' => 'success',
					'msg'  => $message,
				);
			}
			if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'arm_check_currency_status' ) { //phpcs:ignore
				echo wp_json_encode( $response );
				exit;
			}
			return $notAllow;
		}

		function arm_paypal_language() {
			$currency_symbol = array(
				'ar_EG' => esc_html__( 'Arab/Egypt', 'armember-membership' ),
				'da_DK' => esc_html__( 'Danish/Denmark', 'armember-membership' ),
				'nl_NL' => esc_html__( 'Dutch/Netherlands', 'armember-membership' ),
				'zh_XC' => esc_html__( 'Chinese', 'armember-membership' ),
				'zh_CN' => esc_html__( 'Chinese/China', 'armember-membership' ),
				'zh_HK' => esc_html__( 'Chino/Hong Kong', 'armember-membership' ),
				'zh_TW' => esc_html__( 'Chinese/Taiwan', 'armember-membership' ),
				'en_AU' => esc_html__( 'English/Australia', 'armember-membership' ),
				'en_GB' => esc_html__( 'English/United Kingdom', 'armember-membership' ),
				'en_US' => esc_html__( 'English/United States', 'armember-membership' ),
				'es_XC' => esc_html__( 'Eskimo Aleut/Anguilla', 'armember-membership' ),
				'fr_CA' => esc_html__( 'Francais/Canada', 'armember-membership' ),
				'fr_XC' => esc_html__( 'French/Angola', 'armember-membership' ),
				'fr_FR' => esc_html__( 'French/France', 'armember-membership' ),
				'de_DE' => esc_html__( 'German/Germany', 'armember-membership' ),
				'he_IL' => esc_html__( 'Hebrew/Israel', 'armember-membership' ),
				'id_ID' => esc_html__( 'Indonesian/Indonesia', 'armember-membership' ),
				'it_IT' => esc_html__( 'Italian/Italy', 'armember-membership' ),
				'ja_JP' => esc_html__( 'Japanese/Japan', 'armember-membership' ),
				'ko_KR' => esc_html__( 'Korean/South Korea', 'armember-membership' ),
				'no_NO' => esc_html__( 'Norwegian/Norway', 'armember-membership' ),
				'pl_PL' => esc_html__( 'Polish/Poland', 'armember-membership' ),
				'pt_BR' => esc_html__( 'Portugues/Brasil', 'armember-membership' ),
				'pt_PT' => esc_html__( 'Portuguese/Portugal', 'armember-membership' ),
				'ru_RU' => esc_html__( 'Rusia/Rusia', 'armember-membership' ),
				'es_ES' => esc_html__( 'Spanish/Spain', 'armember-membership' ),
				'sv_SE' => esc_html__( 'Swedish/Sweden', 'armember-membership' ),
				'th_TH' => esc_html__( 'Thai/Thailand', 'armember-membership' ),

			);
			return $currency_symbol;
		}


		function arm_amount_set_separator( $arm_currency = 'USD', $arm_amount = '0', $is_coupon_amount = false, $get_currency_wise_seperator = false ) {

			$arm_currency = strtoupper( $arm_currency );

			$currency_separators = $this->get_other_seperator_currencies();

			$separator       = $this->get_currency_separators_standard();
			$arm_main_amount = (float) str_replace( ',', '', $arm_amount );

			if ( $get_currency_wise_seperator == true && ! empty( $currency_separators[ $arm_currency ] ) ) {
				$separator                  = array();
				$separator[ $arm_currency ] = $currency_separators[ $arm_currency ];
			}
			if ( isset( $separator[ $arm_currency ]['decimal'] ) && $separator[ $arm_currency ]['decimal'] != '' ) {
				return number_format( (float) $arm_main_amount, 2, $separator[ $arm_currency ]['decimal'], $separator[ $arm_currency ]['thousand'] );
			} elseif ( isset( $separator[ $arm_currency ]['decimal'] ) && $separator[ $arm_currency ]['decimal'] == '' ) {
				$floar_amount            = floor( $arm_main_amount );
				$after_decimal_point_val = $arm_main_amount - $floar_amount;
				if ( $is_coupon_amount && $after_decimal_point_val == '0.50' ) {
					return number_format( (float) floor( $arm_main_amount ) );
				} else {
					return number_format( (float) $arm_main_amount, 0, '', $separator[ $arm_currency ]['thousand'] );
				}
			} else {
				return number_format( (float) $arm_main_amount, 2, '.', ',' );
			}

		}

		function arm_get_bank_transfer_mode_options() {
			$transfer_mode = array(
				'bank_transfer' => esc_html__( 'Digital Transfer', 'armember-membership' ),
				'cheque'        => esc_html__( 'Cheque', 'armember-membership' ),
				'cash'          => esc_html__( 'Cash', 'armember-membership' ),
			);
			return $transfer_mode;
		}

		function get_currency_separators_standard() {
			$separator = array(
				'USD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'AUD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'BRL' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'CAD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'CZK' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'DKK' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'EUR' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'HKD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'ILS' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'ZMW' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'MYR' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'UZS' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'MXN' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'NOK' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'NZD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'PHP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'PLN' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'GBP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'RUB' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'SGD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'SEK' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'CHF' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'THB' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'UGX' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'TRY' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'AFN' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'ALL' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'ARS' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'AZN' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'DZD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'BSD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'BDT' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'BBD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'BZD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'BMD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'BOB' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'BWP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'BND' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'BGN' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'SHP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'CNY' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'COP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'CRC' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'HRK' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'DOP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'XCD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'EGP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'FJD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'GTQ' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'HNL' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'INR' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'IDR' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'JMD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'KZT' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'KES' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'LAK' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'MMK' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'LBP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'LRD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'MOP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'MVR' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'MRO' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'MUR' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'MAD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'NPR' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'NIO' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'PKR' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'PGK' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'PEN' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'QAR' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'RON' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'WST' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'SAR' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'SCR' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'SGF' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'SBD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'ZAR' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'MKD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'LKR' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'SYP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'TOP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'TTD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'UAH' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'AED' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'YER' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'ETB' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'AMD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'ANG' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'AOA' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'AWG' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'CDF' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'CVE' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'FKP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'GEL' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'GIP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'GMD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'GYD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'KYD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'HTG' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'ISK' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'KGS' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'KHR' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'LSL' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'MDL' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'MNT' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'MWK' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'MZN' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'NAD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'NGN' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'PAB' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'RSD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'UYU' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'TJS' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'SLL' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'SOS' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'SRD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'STD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'SVC' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'SZL' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'TZS' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'BAM' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'BIF' => array(
					'decimal'  => '',
					'thousand' => ',',
				),
				'KRW' => array(
					'decimal'  => '',
					'thousand' => ',',
				),
				'MGA' => array(
					'decimal'  => '',
					'thousand' => ',',
				),
				'RWF' => array(
					'decimal'  => '',
					'thousand' => ',',
				),
				'CLP' => array(
					'decimal'  => '',
					'thousand' => ',',
				),
				'XAF' => array(
					'decimal'  => '',
					'thousand' => ',',
				),
				'TWD' => array(
					'decimal'  => '',
					'thousand' => ',',
				),
				'XPF' => array(
					'decimal'  => '',
					'thousand' => ',',
				),
				'JPY' => array(
					'decimal'  => '',
					'thousand' => ',',
				),
				'HUF' => array(
					'decimal'  => '',
					'thousand' => ',',
				),
				'PYG' => array(
					'decimal'  => '',
					'thousand' => ',',
				),
				'VUV' => array(
					'decimal'  => '',
					'thousand' => ',',
				),
				'VND' => array(
					'decimal'  => '',
					'thousand' => ',',
				),
				'XOF' => array(
					'decimal'  => '',
					'thousand' => ',',
				),
				'DJF' => array(
					'decimal'  => '',
					'thousand' => ',',
				),
				'GNF' => array(
					'decimal'  => '',
					'thousand' => ',',
				),
				'KMF' => array(
					'decimal'  => '',
					'thousand' => ',',
				),
				'GHS' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'BHD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'BTC' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'BTN' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'BYR' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'BYN' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'CUC' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'CUP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'ERN' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'GGP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'IMP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'IQD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'IRR' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'IRT' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'JEP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'JOD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'KPW' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'KWD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'LYD' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'MRU' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'OMR' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'PRB' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'RMB' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'SDG' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'SSP' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'STN' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'TMT' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'TND' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'VEF' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
				'VES' => array(
					'decimal'  => '.',
					'thousand' => ',',
				),
			);
			return $separator;
		}


		function get_currency_wise_separator( $arm_currency ) {
			$separators          = array();
			$arm_currency        = strtoupper( $arm_currency );
			$currency_separators = $this->get_other_seperator_currencies();

			if ( ! empty( $arm_currency ) && ! empty( $currency_separators[ $arm_currency ] ) ) {
				$separators = $currency_separators[ $arm_currency ];
			}
			return $separators;
		}

		function get_other_seperator_currencies() {
			$currency_separators = array(
				'BRL' => array(
					'decimal'  => ',',
					'thousand' => '.',
				),
				'BYR' => array(
					'decimal'  => ',',
					'thousand' => '',
				),
				'BYN' => array(
					'decimal'  => ',',
					'thousand' => '',
				),
				'CUC' => array(
					'decimal'  => ',',
					'thousand' => '.',
				),
				'CUP' => array(
					'decimal'  => ',',
					'thousand' => '.',
				),
				'TMT' => array(
					'decimal'  => ',',
					'thousand' => '',
				),
				'TND' => array(
					'decimal'  => ',',
					'thousand' => '.',
				),
				'VEF' => array(
					'decimal'  => ',',
					'thousand' => '.',
				),
				'VES' => array(
					'decimal'  => ',',
					'thousand' => '.',
				),
			);
			return $currency_separators;
		}
	}

}
global $arm_payment_gateways;
$arm_payment_gateways = new ARM_payment_gateways_Lite();
