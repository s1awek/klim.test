<?php

$add_section = false;
$section_data = [
    'module_name' => $this->format == 'cdb' ? $module_info['name'] : $this->modules_names['pin'],
    'setup' => [],
    'tracked_extra_data' => [],
];

// Get loading status

$loading_status = $this->get_module_loading_status( 'pin', $module_info, $settings );

if ( $loading_status !== false ) {
    $add_section = true;
    $section_data['setup'][] = $loading_status;
}

// Check tracking user emails

if ( isset( $settings['track_user_emails'] ) ) {
    
    if ( $this->format == 'cdb' ) {
        $t_pin_1 = 'Enhanced Match is enabled. Email addresses of clients and logged-in users are sent to Pinterest.';
    } else {
        $t_pin_1 = esc_html__('Enhanced Match is enabled. Email addresses of clients and logged-in users are sent to Pinterest.', 'full-picture-analytics-cookie-notice');
    }
    
    $add_section = true;
    $section_data['tracked_extra_data'][] = $t_pin_1;
}

// Add section
if ( $add_section ) $this->data['pin'] = $section_data;