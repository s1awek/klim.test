<?php

$add_section = false;
$section_data = [
    'module_name' => $this->format == 'cdb' ? $module_info['name'] : $this->modules_names['mads'],
    'setup' => [],
    'tracked_extra_data' => [],
];

// Get loading status

$loading_status = $this->get_module_loading_status( 'mads', $module_info, $settings );

if ( $loading_status !== false ) {
    $add_section = true;
    $section_data['setup'][] = $loading_status;
}

// Check enh. conversions

if ( isset( $settings['enhanced_conv'] ) ) {

    $add_section = true;

    if ( $this->format == 'cdb' ) {
        $t_mads_1 = 'Enhanced Conversions is enabled and sends to MS Advertising email addresses of clients or logged-in users (if known).';
    } else {
        $t_mads_1 = esc_html__('Enhanced Conversions is enabled and sends to MS Advertising email addresses of clients or logged-in users (if known).', 'full-picture-analytics-cookie-notice');
    }

    $section_data['tracked_extra_data'][] = $t_mads_1;
}

if ( $add_section ) $this->data['mads'] = $section_data;

?>