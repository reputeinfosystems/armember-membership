<?php
    global $arm_member_forms,$arm_common_lite,$ARMemberLite,$arm_global_settings,$wpdb,$wp,$ARMemberLiteAllowedHTMLTagsArray,$arm_pay_per_post_feature,$arm_lite_members_activity;

    $arm_lite_allowed_tab_id = ['arm_member_subscription','arm_transaction','arm_edit_profile','arm_close_account','arm_change_password'];

    //get front_end_appearance settings
    $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
    $general_settings   = $all_global_settings['general_settings'];
    $all_default_global_setting = $arm_global_settings->arm_default_global_settings();
    $arm_default_front_settings  = $all_default_global_setting['general_settings']['front_settings'];
    $arm_all_member_panel_settings = $arm_global_settings->arm_get_member_panel_settings();
    $arm_member_panel_settings = is_array($arm_all_member_panel_settings) ? $arm_all_member_panel_settings : array();
    $arm_member_panel_settings = isset($arm_all_member_panel_settings['tab_settings']) ? $arm_all_member_panel_settings['tab_settings'] : array();
    
    $arm_appearance_panel_settings = isset($arm_all_member_panel_settings['appearance_settings']) ? $arm_all_member_panel_settings['appearance_settings'] : array();
    $arm_mpt_icons = $arm_global_settings->arm_get_memper_panel_tab_icon_array();
    
    $arm_default_front_settings['color'] = isset($arm_default_front_settings['color']) ? $arm_default_front_settings['color'] : array();
    $arm_appearance_panel_settings['color'] = isset($arm_appearance_panel_settings['color']) ? $arm_appearance_panel_settings['color'] : array();
    
    $frontOptions   = isset($arm_appearance_panel_settings['color']) ? $arm_appearance_panel_settings['color'] : $arm_default_front_settings['color'];
    $frontFontVal   = isset($arm_appearance_panel_settings['font']['font_family']) ? sanitize_text_field($arm_appearance_panel_settings['font']['font_family']) : 'Poppins';

    $gFontUrl  = $arm_member_forms->arm_get_google_fonts_url( array($frontFontVal) );
    if ( ! empty( $gFontUrl ) ) {
        wp_enqueue_style( 'google-font-member-panel', $gFontUrl, array(), MEMBERSHIPLITE_VERSION );
    }

    $arm_primary_color = isset($frontOptions['primary_color'])  ? sanitize_text_field($frontOptions['primary_color'])  : (isset($arm_default_front_settings['color']['primary_color']) ? sanitize_text_field($arm_default_front_settings['color']['primary_color']) : '');

    $panel_sidebar_color = isset($frontOptions['panel_sidebar_color'])  ? sanitize_text_field($frontOptions['panel_sidebar_color'])  : (isset($arm_default_front_settings['color']['panel_sidebar_color']) ? sanitize_text_field($arm_default_front_settings['color']['panel_sidebar_color']) : '');

    $panel_background_color = isset($frontOptions['panel_background_color'])  ? sanitize_text_field($frontOptions['panel_background_color'])  : (isset($arm_default_front_settings['color']['panel_background_color']) ? sanitize_text_field($arm_default_front_settings['color']['panel_background_color']) : '');

    $border_color = isset($frontOptions['border_color'])  ? sanitize_text_field($frontOptions['border_color'])  : (isset($arm_default_front_settings['color']['border_color']) ? sanitize_text_field($arm_default_front_settings['color']['border_color']) : '');

    $title_text_color = isset($frontOptions['title_text_color'])  ? sanitize_text_field($frontOptions['title_text_color'])  : (isset($arm_default_front_settings['color']['title_text_color']) ? sanitize_text_field($arm_default_front_settings['color']['title_text_color']) : '');

    $content_color = isset($frontOptions['content_color'])  ? sanitize_text_field($frontOptions['content_color'])  : (isset($arm_default_front_settings['color']['content_color']) ? sanitize_text_field($arm_default_front_settings['color']['content_color']) : '');

    $content_overlayed_color = $content_color ? $arm_common_lite->hexToRgba($content_color,0.05) : '';
    $ARMemberLite->arm_session_start();
    if(!isset($_SESSION['arm_file_upload_arr']['avatar'])){
        if($ARMemberLite->is_arm_pro_active)
        {
            global $arm_members_activity;
            $arm_members_activity->session_for_file_handle('avatar',"");
        }
        else
        {
            $arm_lite_members_activity->session_for_file_handle('avatar',"");
        }
    }
    if(!isset($_SESSION['arm_file_upload_arr']['profile_cover'])){
        if($ARMemberLite->is_arm_pro_active)
        {
            global $arm_members_activity;
            $arm_members_activity->session_for_file_handle('profile_cover',"");
        }
        else
        {
            $arm_lite_members_activity->session_for_file_handle('profile_cover',"");
        }
    }

    if(is_user_logged_in()){
        $user_id = get_current_user_id();
        $user_info = get_userdata($user_id);
        $arm_user_full_name = $user_info->first_name.' '.$user_info->last_name;
        $arm_username = $user_info->user_login;
        $user_avatar = get_avatar_url($user_id,array('size'=>80));
        if($ARMemberLite->is_arm_pro_active){
            global $ARMember;
            $ARMember->set_front_css(2);
            $ARMember->set_front_js(true);
        }
        else{
            $ARMemberLite->set_front_css(2);
            $ARMemberLite->set_front_js(true);
        }
        $arm_wpnonce = wp_create_nonce( 'arm_wpnonce' );
        $logout_url    = add_query_arg(
            array(
                'arm_action'  => 'logout',
                'redirect_to' => ARMLITE_HOME_URL,
                'arm_wpnonce' => $arm_wpnonce,
            ),
            ARMLITE_HOME_URL
        );

    $arm_member_tabs_html = '';
    $arm_member_content_html = '';

    $first_content_tab_found = false;
    $is_paid_post_allowed = ( $ARMemberLite->is_arm_pro_active &&  $arm_pay_per_post_feature->isPayPerPostFeature == '1' );
    $arm_edit_profile_tab_id = '';
    $arm_edit_profile_tab_title = '';
    foreach ($arm_member_panel_settings as $key => $tab ) {

        $is_allowed_tab = false;
        $tab_id = $tab['id'];

        if (in_array($tab_id, $arm_lite_allowed_tab_id)) {
            $is_allowed_tab = true;
        } else if($ARMemberLite->is_arm_pro_active){
            $is_allowed_tab = apply_filters('arm_is_member_panel_tab_allowed',false,$tab_id,);
        }

        if ($is_allowed_tab) {

            if (!isset($tab['is_enable']) || $tab['is_enable'] != 1) continue;


            $mpt_tab_id = isset($tab['id']) && !empty($tab['id'])  ? sanitize_key($tab['id']) . '_' . $key  : 'custom_' . $key;
            if ($mpt_tab_id === 'Paid Post' && !$is_paid_post_allowed) {
                continue;
            }

            if($tab_id === 'arm_edit_profile'){
                $arm_edit_profile_tab_id = $mpt_tab_id;
                $arm_edit_profile_tab_title = $tab['title'];
            }

            $mpt_container_id = isset($tab['title']) ? strtolower(esc_html($tab['title'])) : '';
            $mpt_container_id = preg_replace('/[^a-z0-9]+/', '_', $mpt_container_id);
            $mpt_container_id = trim($mpt_container_id, '_');

            $map_class = preg_replace('/^arm_/', '', $mpt_tab_id);

            $map_class = str_replace(
                ['member_subscription','transaction','edit_profile'],
                ['member_subscriptions','member_transactions','edit_members'],
                $map_class
            );

            $active_class = '';
            if (!$first_content_tab_found && $tab['tab_type'] !== 'url') {
                $active_class = 'arm-panel-menu-item-active ';
                $first_content_tab_found = true;
            }

            if ($tab['tab_type'] === 'url') {
                $target = !empty($tab['url_in_new_tab']) ? ' target="_blank"' : '';
                    $arm_member_tabs_html .= '<a href="' . esc_url($tab['url_content']) . '" class="' . $active_class . 'arm-menu-item" id="' . esc_attr($mpt_tab_id) . '"' . $target . '>'
                    . '<div class="arm-panel-menu-item-icon">' . (isset($arm_mpt_icons[$tab['icon']]) ? $arm_mpt_icons[$tab['icon']] : '') . '</div>'
                    . '<div class="arm-menu-item-txt" aria-label="' . esc_attr($tab['title']) . '">' . esc_html($tab['title']) . '</div>'
                    . '</a>';
            } else {
                    $arm_member_tabs_html .= '<a href="javascript:void(0);" class="' . $active_class . 'arm-menu-item" id="' . esc_attr($mpt_tab_id) . '" onClick="arm_panel_change_tab(\'' . esc_attr($mpt_tab_id) . '\',\'' . esc_attr($tab['title']) . '\');" data-arm_menu_title="' . esc_attr($tab['title']) . '">'
                    . '<div class="arm-panel-menu-item-icon">' . (isset($arm_mpt_icons[$tab['icon']]) ? $arm_mpt_icons[$tab['icon']] : '') . '</div>'
                    . '<div class="arm-menu-item-txt" aria-label="' . esc_attr($tab['title']) . '">' . esc_html($tab['title']) . '</div>'
                    . '</a>';
            }
            if ($tab['tab_type'] === 'content') {
                $style = $active_class ? '' : 'style="display: none;"';
                $raw_content = !empty($tab['text_content']) ? $tab['text_content'] : '';

                $updated_content = preg_replace_callback(
                    '/\[(arm_[a-zA-Z0-9_]+)([^\]]*)\]/', 
                    function ($matches) {
                        $tag   = $matches[1];      
                        $attrs = trim($matches[2]);  
                        $param_to_add = 'arm_member_panel="1" title =""';

                        $attrs .= ($attrs ? ' ' : '') . $param_to_add;

                        return '[' . $tag . ' ' . trim($attrs) . ']';
                    },
                    $raw_content
                );
                $updated_content = apply_filters($mpt_tab_id.'_panel_content_external', $updated_content);

                $content = do_shortcode(wp_kses($updated_content, $ARMemberLiteAllowedHTMLTagsArray));

                    $arm_member_content_html .= '<div class="arm-panel-detail-' . esc_attr($mpt_tab_id) . ' arm-panel-detail arm-panel-detail-' . esc_attr($map_class) . '" ' . $style . '>
                    <div class="arm-panel-content-header arm-panel-padding">
                        <div class="arm-panel-tab-heading">' . esc_html($tab['title']) . '</div>
                    </div>
                    <div class="arm-panel-data-container arm-panel-table">' . $content . '</div>
                </div>';
                }
            }
        }
    ?>
    <style id="arm_member_panel">
    .arm-panel-content,
    .arm-panel-container .arm-panel-sidebar:not(.arm_panel_menu_items .arm-panel-sidebar){
    background-color: <?php echo $panel_sidebar_color;?>; 
    border: 1px solid <?php echo $border_color;?>;
    }
    .arm_front_dashborad_rtl .arm-panel-content{
        border-left: 1px solid <?php echo $border_color;?> !important;
    }

    .arm-tablet .arm-panel-container .arm_panel_menu_mobile,.arm-tablet .arm_profile_dropdown ul{
    background-color: <?php echo $panel_sidebar_color;?>; 
    }
    .arm-panel-content{
        border-left: 0 !important;
    }

    .arm-tablet .arm-panel-container .arm-panel-content{
        border: 1px solid <?php echo $border_color;?> !important;
    }

    .arm-menu-item svg path ,
    .arm_responsive_mobile_menu_icon  svg path, 
    .arm-droup-down-arrow svg, 
    .arm_paging_wrapper .arm_paging_info,
    .arm_paging_wrapper .arm_paging_links .arm_prev,
    .arm_paging_wrapper .arm_paging_links .arm_next,
    .arm-panel-sidebar-profile-action.arm_dashboard_logout_link svg path{
        stroke: <?php echo $content_color;?> !important;
    }
    .arm-panel-sidebar a.arm-panel-menu-item-active svg path{
        stroke: <?php echo $panel_sidebar_color;?> !important;
    }

    .arm-panel-container.arm_display_inherit{
        background-color: <?php echo $panel_background_color;?>;
    }

    .arm-panel-container_main .arm-panel-sidebar a.arm-menu-item:not(.arm-panel-menu-item-active):hover, .arm-panel-container_main .arm-panel-sidebar a.arm-menu-item:not(.arm-panel-menu-item-active):focus{
        background-color: <?php echo $arm_primary_color;?>0F;   
    }

    .arm-panel-sidebar:not(.arm_panel_menu_items .arm-panel-sidebar) {
    border-right: 1px solid <?php echo $border_color?>;
    background-color: <?php $panel_sidebar_color;?>;
    }

    .arm-panel-sidebar a.arm-menu-item, .arm-panel-sidebar a.arm-menu-item:not(.arm-panel-menu-item-active):focus {
    color: <?php echo $content_color;?> !important;
    font-family: <?php echo $frontFontVal;?>;
    }

    .arm-panel-sidebar a.arm-panel-menu-item-active,
    .arm-panel-sidebar a.arm-panel-menu-item-active:hover{
        background: <?php echo $arm_primary_color?> !important;
        color: <?php echo $panel_sidebar_color;?> !important;
    }

    .arm-panel-front-menu-seperator{
        border: 0px;
    }

    .arm-panel-sidebar-profile-section{
        color: <?php echo $content_color;?>;
    }

    .arm-panel-sidebar-profile-detais{
        font-family: <?php echo $frontFontVal; ?>;
    }

    .arm-panel-content{
        background-color: <?php echo $panel_background_color;?>;
    }

    .arm-panel-content-header, .arm-panel-profile_info-heading{
        color: <?php echo $title_text_color;?>;
    }
    .arm-panel-data-container table.arm_user_current_membership_list_table td,
    .arm-panel-container_main:not(.arm-tablet) .arm-panel-data-container table.arm_user_transaction_list_table tr:not(.arm_expanded) td:not(.arm_no_plan){
        border: 0;   
        border-bottom: 1px solid <?php echo $border_color;?> !important;
    }
    .arm-panel-sidebar-profile-action:hover svg path{
        stroke: #FFF !important;
    }

    .arm-panel-sidebar-profile-action:hover .arm-menu-item-txt{
        color:var(--arm-cl-white) !important;
    }

    .arm-panel-data-container .arm_shortcode_grid_container .arm_no_plan{
        text-align: center;
        border: 0 !important;   
        color: <?php echo $content_color;?> !important;
        font-weight: 400 !important;
        font-size: 14px !important;
        line-height: 20px;
        letter-spacing: 2%;
        padding: 22px 0px 20px 20px !important;
        cursor: default !important;
    }

    .arm-panel-data-container .arm_current_membership_list_header th,
    .arm-panel-data-container .arm_transactions_container .arm_transaction_list_header th{
        border: 0;
        border-bottom: 1px solid <?php echo $border_color;?> !important;
        background: <?php echo $panel_background_color;?>;
        color: <?php echo $content_color;?> !important;
        font-family: <?php echo $frontFontVal?> !important;
    }
    .arm-panel-data-container .arm_user_transaction_list_table,
    .arm-panel-data-container .arm_shortcode_grid_container .arm_shortcode_grid_table_header th{
        border-bottom: 1px solid <?php echo $border_color;?> !important;
        background: <?php echo $panel_background_color;?>;
    }
    .arm-panel-data-container .arm_user_current_membership_list_table{
        background: <?php echo $panel_background_color;?>;
    }
    .arm_expand_grid_section .arm_expand_grid_col_label,
    .arm-panel-data-container .arm_shortcode_grid_container .arm_shortcode_grid_table_header th , .arm-panel-data-container{
        color: <?php echo $content_color;?> !important;
        font-family: <?php echo $frontFontVal?> !important;
    }
    .arm-panel-data-container .arm_shortcode_grid_container .arm_paging_wrapper .arm_paging_info{
    color: <?php echo $content_color;?> !important;
    font-family: <?php echo $frontFontVal?> !important;
    }
    .arm_expand_grid_section .arm_expand_grid_col_val,
    .arm-panel-data-container .arm_current_membership_container .arm_membership_expand_data td,
    .arm-panel-data-container .arm_transactions_container .arm_transaction_list_item td,
    .arm-panel-data-container .arm_shortcode_grid_list_item td,
    .arm_user_current_membership_list_table td,
    .arm-panel-data-container .arm_shortcode_grid_list_item td a{
        color: <?php echo $title_text_color?> !important;
        font-family: <?php echo $frontFontVal?> !important;
    }
    .arm-panel-container_main:not(.arm-tablet) .arm_shortcode_grid_list_item:hover td:not(.arm_no_plan,.arm_current_membership_cancelled_row),
    .arm-panel-container_main:not(.arm-tablet) .arm_expanded_row td,
    .arm-panel-container_main.arm-tablet .arm_expanded_row{
        background: <?php echo $content_overlayed_color;?> !important;
    }
    .arm_shortcode_grid_list_item td,
    .arm-panel-data-container .arm_current_membership_container .arm_membership_expand_data td{
        border-bottom: 1px solid <?php echo $border_color;?> !important;
    }

    .arm_shortcode_grid_list_item td:not(.arm_no_plan){
        cursor: pointer !important;
    }

    .arm-panel-tab-heading , .arm-panel-profile_info-heading{   
        font-family: <?php echo $frontFontVal;?>;
    }
    .arm-tablet .arm-panel-container .arm-panel-data-container .arm_user_current_membership_list_table .arm_shortcode_grid_list_item,
    .arm-tablet .arm-panel-container .arm_expand_grid_section{
        border-color: <?php echo $border_color;?> !important;
    }
    .arm-tablet .arm-panel-container .arm_shortcode_grid_container table.arm_user_transaction_list_table tr:not(.arm_membership_expand_data),
    .arm-tablet .arm-panel-container .arm-panel-data-container .arm_user_current_membership_list_table .arm_membership_expand_data td,
    .arm-tablet .arm-panel-container .arm-panel-data-container .arm_membership_expand_data td{
        border: 1px solid <?php echo $border_color;?> !important;
    }

    .arm-panel-sidebar-profile-action .arm-menu-item-txt{ 
        color: <?php echo $content_color?> !important;   
    }
    </style>
    <?php 
    $arm_is_rtl_container = "";
    if(is_rtl())
    {
        $arm_is_rtl_container = " arm_front_dashborad_rtl";
    }
    ?>
    <div class="arm-panel-container_main<?php echo $arm_is_rtl_container; ?>" id="arm-panel-container">
        <div class="arm-panel-container">
            <div class="arm-panel-sidebar" style="display:none">
                <div class="arm_responsive_mobile_menu_icon arm_hide">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40" fill="none"><rect x="0.5" y="0.5" width="39" height="39" rx="9.5" /><rect x="0.5" y="0.5" width="39" height="39" rx="9.5" stroke="#CFD6E5"/><path d="M14 15H26" stroke="#535D71" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 20H26" stroke="#535D71" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 25H26" stroke="#535D71" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div class="arm_panel_menu_items arm_panel_menu_desktop">
                <?php
                echo $arm_member_tabs_html;
                    $content = '';
                    echo apply_filters( 'arm_front_end_additional_panel_menu', $content );?>
                    <div class="arm_panel_logout_link_data arm_hide">
                        <a href='<?php echo $logout_url;?>' class="arm-panel-sidebar-profile-action arm_dashboard_logout_link">
                            <div class="arm-panel-menu-item-icon"><svg width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg"><rect class="arm-panel-svg-border-color" x="0.5" y="0.5" width="37" height="37" rx="7.5" stroke="#C9CFDB"></rect><path class="arm-panel-svg-content-color" d="M22.0996 14.5602C21.7896 10.9602 19.9396 9.49023 15.8896 9.49023H15.7596C11.2896 9.49023 9.4996 11.2802 9.4996 15.7502V22.2702C9.4996 26.7402 11.2896 28.5302 15.7596 28.5302H15.8896C19.9096 28.5302 21.7596 27.0802 22.0896 23.5402" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path class="arm-panel-svg-content-color" d="M16.0009 19H27.3809"  stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path class="arm-panel-svg-content-color" d="M25.15 15.6504L28.5 19.0004L25.15 22.3504"  stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                            </div>
                            <div class="arm-menu-item-txt" aria-label="<?php esc_html_e('Logout','armember-membership');?>"><?php esc_html_e('Logout','armember-membership');?></div>
                        </a>
                    </div>
                </div>
                <div class="arm-panel-front-menu-seperator"></div>

                <div class="arm-panel-sidebar-profile-section">
                    <div class="arm-profile-trigger">
                    <img src="<?php echo $user_avatar;?>" alt="User Avatar">
                    <span class="arm-droup-down-arrow" tabindex="0">
                        <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 1.5L6 6.5L11 1.5"  stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>                                
                    </span>   
                    </div>
                    <div class="arm-panel-sidebar-profile-detais">
                        <div class="arm-panel-sidebar-username"><?php echo $arm_user_full_name;?></div>
                        <div class="arm-panel-sidebar-useremail"><?php echo $arm_username;?></div>
                    </div>
                    <dl class="arm_profile_dropdown">
                        <dd>
                            <ul>
                                <?php if(!empty($arm_edit_profile_tab_title )){ ?>
                                <li>
                                    <a href="javascript:void(0);" class="arm-menu-item" id="edit_members" onClick="arm_panel_change_tab('<?php echo $arm_edit_profile_tab_id;?>','<?php esc_html_e('Edit Profile','armember-membership');?>');" data-arm_menu_title="<?php esc_html_e('Edit Profile','armember-membership');?>">
                                        <div class="arm-panel-menu-item-icon"><svg width="16" height="16" viewBox="0 0 19 23" fill="none" xmlns="http://www.w3.org/2000/svg"><path class="ap-front-menu-fill" d="M13.4316 5.82353C13.4316 6.32238 13.3334 6.81635 13.1425 7.27724C12.9516 7.73812 12.6718 8.15689 12.319 8.50964C11.9663 8.86238 11.5475 9.1422 11.0866 9.3331C10.6257 9.524 10.1318 9.62226 9.63291 9.62226C9.13406 9.62226 8.64008 9.524 8.1792 9.3331C7.71832 9.1422 7.29955 8.86238 6.9468 8.50964C6.59406 8.15689 6.31424 7.73812 6.12334 7.27724C5.93243 6.81635 5.83418 6.32238 5.83418 5.82353C5.83418 4.81604 6.2344 3.84982 6.9468 3.13742C7.6592 2.42501 8.62543 2.02479 9.63291 2.02479C10.6404 2.02479 11.6066 2.42502 12.319 3.13742C13.0314 3.84982 13.4316 4.81604 13.4316 5.82353ZM13.6419 9.83255C14.7052 8.76929 15.3025 7.3272 15.3025 5.82353C15.3025 4.31985 14.7052 2.87776 13.6419 1.8145C12.5787 0.751239 11.1366 0.153906 9.63291 0.153906C8.12924 0.153906 6.68714 0.751239 5.62388 1.8145C4.56062 2.87776 3.96329 4.31985 3.96329 5.82353C3.96329 7.3272 4.56062 8.76929 5.62388 9.83255C6.68714 10.8958 8.12924 11.4931 9.63291 11.4931C11.1366 11.4931 12.5787 10.8958 13.6419 9.83255ZM9.63291 12.6856C7.3168 12.6856 5.09555 13.6056 3.45781 15.2434C1.82007 16.8811 0.9 19.1024 0.9 21.4185C0.9 21.6666 0.998555 21.9045 1.17398 22.0799C1.34941 22.2554 1.58735 22.3539 1.83544 22.3539C2.08354 22.3539 2.32147 22.2554 2.4969 22.0799C2.67233 21.9045 2.77089 21.6666 2.77089 21.4185C2.77089 19.5985 3.49385 17.8532 4.78073 16.5663C6.06761 15.2794 7.81299 14.5564 9.63291 14.5564C11.4528 14.5564 13.1982 15.2794 14.4851 16.5663C15.772 17.8532 16.4949 19.5985 16.4949 21.4185C16.4949 21.6666 16.5935 21.9045 16.7689 22.0799C16.9444 22.2554 17.1823 22.3539 17.4304 22.3539C17.6785 22.3539 17.9164 22.2554 18.0918 22.0799C18.2673 21.9045 18.3658 21.6666 18.3658 21.4185C18.3658 19.1024 17.4458 16.8811 15.808 15.2434C14.1703 13.6056 11.949 12.6856 9.63291 12.6856Z" stroke="#2E3645" stroke-width="1.5"></path></svg></div>
                                        <div class="arm-menu-item-txt" aria-label="<?php echo $arm_edit_profile_tab_title; ?>"><?php echo $arm_edit_profile_tab_title; ?></div>
                                    </a>
                                </li>
                                <?php } ?>
                                <li>
                                    <a href="<?php echo $logout_url;?>" class="arm-menu-item">
                                        <div class="arm-panel-menu-item-icon"><svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M11.6041 5.66532C11.3596 2.82604 9.90057 2.5 6.70638 2.5H6.60385C3.07841 2.5 1.66665 3.91175 1.66665 7.4372V12.5795C1.66665 16.1049 3.07841 17.5167 6.60385 17.5167H6.70638C9.87691 17.5167 11.336 17.2064 11.5962 14.4144" stroke="#2E3645" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> <path d="M8.45989 10.0005H17.4352" stroke="#2E3645" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> <path d="M15.6764 7.3584L18.3185 10.0005L15.6764 12.6426" stroke="#2E3645" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg></div>
                                        <div class="arm-menu-item-txt" aria-label="<?php esc_html_e('Logout','armember-membership');?>"> <?php esc_html_e('Logout','armember-membership');?> </div>
                                    </a>
                                </li>
                            </ul>
                        </dd>
                    </dl>
                    <a href='<?php echo $logout_url;?>' class="arm-panel-sidebar-profile-action arm_dashboard_logout_link">
                        <div tabindex="0" class="arm-panel-logout-icon">
                            <svg width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg"><path class="arm-panel-svg-content-color" d="M22.0996 14.5602C21.7896 10.9602 19.9396 9.49023 15.8896 9.49023H15.7596C11.2896 9.49023 9.4996 11.2802 9.4996 15.7502V22.2702C9.4996 26.7402 11.2896 28.5302 15.7596 28.5302H15.8896C19.9096 28.5302 21.7596 27.0802 22.0896 23.5402" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path class="arm-panel-svg-content-color" d="M16.0009 19H27.3809"  stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path class="arm-panel-svg-content-color" d="M25.15 15.6504L28.5 19.0004L25.15 22.3504"  stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>                   
                            <div class="arm-menu-item-txt" aria-label="<?php esc_html_e('Logout','armember-membership');?>"><?php esc_html_e('Log out','armember-membership');?></div>
                        </div>
                    </a>
                </div>
                <div class="arm_panel_menu_items arm_panel_menu_mobile_wrapper" style="display:none;">
                    <div class="arm_panel_menu_items arm_panel_menu_mobile">
                        <div class="arm-panel-menu-close"><svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.02071 20.6552L7.02073 20.6552L13.0004 14.6734L18.9812 20.6541L18.9812 20.6541L18.9825 20.6554C19.2056 20.8709 19.5045 20.9902 19.8148 20.9875C20.1251 20.9848 20.4219 20.8603 20.6413 20.6409C20.8607 20.4215 20.9851 20.1247 20.9878 19.8145C20.9905 19.5042 20.8712 19.2053 20.6557 18.9821L20.6557 18.9821L20.6545 18.9809L14.6737 13.0001L20.6545 7.0193L20.6545 7.01928C20.8764 6.79724 21.001 6.49615 21.0008 6.18226C21.0007 5.86836 20.8759 5.56736 20.6539 5.34548C20.4319 5.1236 20.1308 4.99901 19.8169 4.99912C19.503 4.99923 19.202 5.12403 18.9801 5.34607L13.0004 11.3268L7.01963 5.34605L7.01964 5.34604L7.01839 5.34483C6.79521 5.12927 6.4963 5.01 6.18603 5.0127C5.87577 5.01539 5.57897 5.13984 5.35957 5.35924C5.14017 5.57864 5.01572 5.87544 5.01303 6.1857C5.01033 6.49597 5.1296 6.79488 5.34516 7.01806L5.34515 7.01807L5.34638 7.0193L11.3272 13.0001L5.34638 18.9809C5.12434 19.2029 4.99961 19.5041 4.99961 19.8181C4.99961 20.132 5.12435 20.4332 5.34638 20.6552C5.56841 20.8772 5.86955 21.002 6.18355 21.002C6.49754 21.002 6.79868 20.8772 7.02071 20.6552Z" fill="#656E81" stroke="#656E81" stroke-width="0.2"></path></svg></div>
                <div class="arm-panel-sidebar">
                <?php
                echo $arm_member_tabs_html;

                        $arm_addon_panel_menu = '';
                        echo apply_filters('arm_front_end_additional_panel_menu',$arm_addon_panel_menu);
                        ?>
                        
                        </div>

                    </div>
                </div>
            </div>
            <div class="arm-panel-content" style="display:none"><!---->
                <?php $arm_loader = $arm_common_lite->arm_loader_img_func();
                echo $arm_loader; //phpcs:ignore ?>
        <?php
            echo $arm_member_content_html ;
                    $arm_addon_panel_content = '';
                    
                    echo apply_filters('arm_front_end_additional_panel_content', $arm_addon_panel_content);
                ?>
            </div>
        </div>
    </div>
<?php }
    else{
        $default_login_form_id = $arm_member_forms->arm_get_default_form_id('login');
        echo do_shortcode("[arm_form id='$default_login_form_id' is_referer='1']");
    }
?>
<input type="hidden" id="arm_panel_nonce" name="arm_panel_nonce" value="<?php echo wp_create_nonce('arm_wp_nonce');?>">