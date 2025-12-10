<?php
global $arm_common_lite;
$arm_loader = $arm_common_lite->arm_loader_img_func();?>
<div class="arm_member_view_detail_container popup_wrapper">
    <div class="arm_member_view_detail_popup popup_wrapper arm_member_view_detail_popup_wrapper">
        <div class="popup_wrapper_inner" style="overflow: hidden;">
        <div class="popup_header arm_member_popup_header_wrapper">
            <div class="page_title">
                <span class="arm_view_manage_member_popup_title"><?php esc_html_e('Member Details','armember-membership' )?></span>
                <div class="arm_popup_close_btn arm_member_view_detail_close_btn"></div>
            </div>
        </div>
            <div class="arm_loading_grid arm_view_member_loading"><?php echo $arm_loader;?></div>
            <div class="popup_content_text arm_member_view_detail_popup_text arm_padding_0" id="arm_member_view_detail_popup_text" >
            </div>
        </div>
    </div>
</div>