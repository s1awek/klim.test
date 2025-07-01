<?php

$output = '<div id="fupi_main_checklist_popup" class="fupi_popup_content">
<div class="fupi_checklist">';

// $folder_path = $is_premium ? $module_id . '__premium_only' : $module_id;

// if ( $current_module_data['is_addon'] == true ) {
    $file_data = apply_filters( 'fupi_' . $module_id . '_get_faq_data', [] );
// } else {
//     if ( $module_path == 'status' ) {
//         $file_path = FUPI_PATH . '/admin/common/pages/parts/fupi-page-part-status-guides.php';
//     } else {
//         $file_path = FUPI_PATH . '/admin/modules/' . $folder_path . '/checklist_' . $module_id . '.php';
//     };
// }


$popups_html = ''; // overwritten in the imported file

if ( ! empty( $file_data ) ) {

    $tasks = $file_data['q'];
    $popups_html = $file_data['a'];

    if ( ! empty( $tasks ) && is_array( $tasks ) && count( $tasks ) > 0 ) {
        
        $output .= '<h3 class="fupi_checklist_heading">' . esc_html__('Quick guides for this page', 'full-picture-analytics-cookie-notice') . '</h3>';
    
        foreach( $tasks as $task ) {
    
            $data_page_id = empty( $task['p_id'] ) ? '' : 'data-page_id="' . $task['p_id'] . '"';
            $classes = ! empty( $task['classes'] ) ? esc_attr( $task['classes'] ) : '';
    
            $output .= "<section class='fupi_task {$classes}' data-id='{$task["id"]}' {$data_page_id}>";
    
                if ( empty( $task['url'] ) ) {
                    $task_id = 'fupi_' . $task['id'] . '_popup';
                    $output .= "<button type='button' class='fupi_open_popup fupi_task_title' data-popup='{$task_id}'>{$task["title"]}</button>";
                } else {
                    $output .= "<a href='{$task['url']}' target='_blank' class='fupi_task_title'>{$task["title"]}<span class='dashicons dashicons-external'></span></a>";
                };
    
                $output .= "
            </section>";
        };
    }
};

$output .= '
        <h3 class="fupi_checklist_heading">' . esc_html__( 'Documentation & support', 'full-picture-analytics-cookie-notice') . '</h3>
        <section class="fupi_task">
            <a href="https://wpfullpicture.com/support/documentation/first-steps-in-full-picture/?utm_source=fp_admin&utm_medium=referral&utm_campaign=menu_link" class="fupi_task_title" target="_blank"><span class="dashicons dashicons-video-alt3"></span> ' . esc_html__('First steps - video tutorials', 'full-picture-analytics-cookie-notice') . ' <span class="dashicons dashicons-external" style="color: #777;"></span></a>
        </section>
        <section class="fupi_task">
            <a href="https://wpfullpicture.com/support/documentation/troubleshooting/?utm_source=fp_admin&utm_medium=referral&utm_campaign=menu_link" class="fupi_task_title" target="_blank"><span class="dashicons dashicons-sos"></span> ' . esc_html__( 'Solutions to common problems', 'full-picture-analytics-cookie-notice' ) . ' <span class="dashicons dashicons-external" style="color: #777;"></span></a>
        </section>
        <section class="fupi_task">
            <a href="https://wpfullpicture.com/support/documentation/?utm_source=fp_admin&utm_medium=referral&utm_campaign=menu_link" class="fupi_task_title" target="_blank"><span class="dashicons dashicons-admin-page"></span> ' . esc_html__('Documentation', 'full-picture-analytics-cookie-notice') . ' <span class="dashicons dashicons-external" style="color: #777;"></span></a>
        </section>
        <section class="fupi_task">
            <a href="';

            if ( fupi_fs()->is_not_paying() ) {
                $output .= 'https://wordpress.org/support/plugin/full-picture-analytics-cookie-notice/';
            } else {
                $output .= 'https://wpfullpicture.com/contact/?utm_source=fp_admin&utm_medium=referral&utm_campaign=menu_link';
            };
    
        $output .= '" class="fupi_task_title" target="_blank"> <span class="dashicons dashicons-admin-users"></span> ' . esc_html__('Contact support / Report a problem', 'full-picture-analytics-cookie-notice') . ' <span class="dashicons dashicons-external" style="color: #777;"></span></a></section>
        <section class="fupi_task">
            <a href="https://wpfullpicture.com/releases/?utm_source=fp_admin&utm_medium=referral&utm_campaign=menu_link" class="fupi_task_title" target="_blank"><span class="dashicons dashicons-format-aside"></span> ' . esc_html__( 'What\'s new', 'full-picture-analytics-cookie-notice' ) . ' <span class="dashicons dashicons-external" style="color: #777;"></span></a>
        </section>
    </div>
</div>';

echo $output . $popups_html;

?>