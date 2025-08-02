<?php

// $folder_path = $is_premium ? $module_id . '__premium_only' : $module_id;
$file_data = apply_filters( 'fupi_' . $module_id . '_get_faq_data', [] );
$popups_html = empty( $file_data['a'] ) ? '' : $popups_html = $file_data['a'];

echo '<div id="fupi_main_checklist_popup" class="fupi_popup_content"><div class="fupi_checklist"></div></div>' 
. $popups_html . 
// INFO ABOUT DEBUG MODE
'<div id="fupi_debug_info_popup" class="fupi_popup_content">
    <p style="text-align: center; background: #efefef; padding: 10px; border: 1px solid #ddd">' . sprintf( esc_html__('You can enable setup mode in the %1$sGeneral Settings%2$s page.', 'full-picture-analytics-cookie-notice' ), '<a href="' . get_admin_url() . 'admin.php?page=full_picture_main" target="_blank">', '</a>' ) . '</p>
    <p>' . esc_html__( 'Use setup mode to test configuration of WP Full Picture and its modules. After you enable it, you will see:', 'full-picture-analytics-cookie-notice' ) . '</p>
    <ol>
        <li>' . esc_html__( 'a "testing panel" in the front-end of your site (visible only to logged-in administrators),', 'full-picture-analytics-cookie-notice' ) . '</li>
        <li>' . esc_html__( 'notifications about the state of your setup (in the browser console and WordPress debug.log file on the server),', 'full-picture-analytics-cookie-notice' ) . '</li>
        <li>' . esc_html__( 'user meta, post meta and term meta information (in the browser console - accessible via fp_usermeta, fp_postmeta and fp_termmeta variables).', 'full-picture-analytics-cookie-notice' ) . '</li>
    </ol>
    <p style="text-align: center;">' . sprintf( esc_html__('%1$sLearn more%2$s.', 'full-picture-analytics-cookie-notice'), ' <a href="https://wpfullpicture.com/support/documentation/debug-mode-features/?utm_source=fp_admin&utm_medium=fp_link" target="_blank">', ' <span class="dashicons dashicons-external"></span></a>') . '</p>
</div>';

?>