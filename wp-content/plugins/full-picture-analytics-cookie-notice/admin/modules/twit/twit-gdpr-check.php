<?php

$add_section = false;
$section_data = [
    'module_name' => $this->format == 'cdb' ? $module_info['name'] : $this->modules_names['twit'],
    'setup' => [],
    'tracked_extra_data' => [],
];

// get loading status
$loading_status = $this->get_module_loading_status( 'tik', $module_info, $settings );

if ( $loading_status !== false ) {
    $add_section = true;
    $section_data['setup'][] = $loading_status;
}

// get enhanced conversions
if ( isset( $settings['enhanced_conv'] ) ) {

    $add_section = true;

    if ( $this->format == 'cdb' ) {
        $t_twit_1 = 'Enhanced Conversions is enabled. Email addresses of clients and logged-in users are sent to Twitter.';
    } else {
        $t_twit_1 = esc_html__('Enhanced Conversions is enabled. Email addresses of clients and logged-in users are sent to Twitter.', 'full-picture-analytics-cookie-notice');
    }

    $section_data['tracked_extra_data'][] = $t_twit_1;
}

// Add section
if ( $add_section ) $this->data['twit'] = $section_data;