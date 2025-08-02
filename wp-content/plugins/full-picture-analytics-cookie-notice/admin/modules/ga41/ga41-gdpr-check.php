<?php

$add_section = false;
$section_data = [
    'module_name'        => ( $this->format == 'cdb' ? $module_info['name'] : $this->modules_names['ga41'] ),
    'setup'              => [],
    'tracked_extra_data' => [],
];
// Get loading status
$loading_status = $this->get_module_loading_status( 'ga41', $module_info, $settings );
if ( $loading_status !== false ) {
    $add_section = true;
    $section_data['setup'][] = $loading_status;
}
// Check User ID
if ( isset( $settings['set_user_id'] ) ) {
    if ( $this->format == 'cdb' ) {
        $t_ga4_cross = 'User ID - for cross-browser tracking';
    } else {
        $t_ga4_cross = esc_html__( 'User ID - for cross-browser tracking', 'full-picture-analytics-cookie-notice' );
    }
    $add_section = true;
    $section_data['tracked_extra_data'][] = $t_ga4_cross;
}
// Get metadata
$usermeta_info = $this->get_tracked_usermeta( 'ga41', $settings );
if ( $usermeta_info !== false ) {
    $add_section = true;
    $section_data['tracked_extra_data'][] = $usermeta_info;
}
// Get Woo
if ( $this->woo_enabled ) {
    $add_section = true;
    if ( $this->format == 'cdb' ) {
        $t_order_id = 'Order ID';
    } else {
        $t_order_id = esc_html__( 'Order ID', 'full-picture-analytics-cookie-notice' );
    }
    $section_data['tracked_extra_data'][] = $t_order_id;
}
// Add section
if ( $add_section ) {
    $this->data['ga41'] = $section_data;
}