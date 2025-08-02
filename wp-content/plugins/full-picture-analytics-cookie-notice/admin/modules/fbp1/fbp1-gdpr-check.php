<?php

$add_section = false;
$section_data = [
    'module_name' => $this->format == 'cdb' ? $module_info['name'] : $this->modules_names['fbp1'],
    'setup' => [],
    'tracked_extra_data' => [],
];

// Get loading status

$loading_status = $this->get_module_loading_status( 'fbp1', $module_info, $settings );

if ( $loading_status !== false ) {
    $add_section = true;
    $section_data['setup'][] = $loading_status;
}

if ( isset( $settings['adv_match'] ) ) {

    $add_section = true;

    if ( $this->format == 'cdb' ) {
        $t_fbp_1 = 'Physical addresses, email addresses, phone numbers and user identifiers of your visitors and logged in users are sent to Meta in an encrypted form (if known)';
    } else {
        $t_fbp_1 = esc_html__('Advanced Match is enabled. Physical addresses, email addresses, phone numbers and user identifiers of your visitors and logged in users are sent to Meta in an encrypted form (if known).', 'full-picture-analytics-cookie-notice');
    }
    
    $section_data['tracked_extra_data'][] = $t_fbp_1;
}

if ( isset( $settings['limit_data_use'] ) ) {

    $add_section = true;

    if ( $this->format == 'cdb' ) {
        $t_fbp_2 = 'Limited Data Use option is enabled for visitors from the USA.';
    } else {
        $t_fbp_2 = esc_html__('Limited Data Use option is enabled for visitors from the USA.', 'full-picture-analytics-cookie-notice');
    }
    
    $section_data['tracked_extra_data'][] = $t_fbp_2;
}

// Get metadata
$usermeta_info = $this->get_tracked_usermeta( 'fbp1', $settings );

if ( $usermeta_info !== false ) {
    $add_section = true;
    $section_data['tracked_extra_data'][] = $usermeta_info;
}

// Add section
if ( $add_section ) $this->data['fbp1'] = $section_data;

?>