<?php

$add_section = false;
$section_data = [
    'module_name' => $this->format == 'cdb' ? $module_info['name'] : $this->modules_names['cegg'],
    'setup' => [],
    'tracked_extra_data' => [],
];

// get loading status
$loading_status = $this->get_module_loading_status( 'cegg', $module_info, $settings );

if ( $loading_status !== false ) {
    $add_section = true;
    $section_data['setup'][] = $loading_status;
}

// get tracking status
if ( isset( $settings['identif_users'] ) ) {
    
    $add_section = true;

    if ( $this->format == 'cdb' ) {
        $t_cegg = 'ID of a logged in user';
    } else {
        $t_cegg = esc_html__('ID of a logged in user', 'full-picture-analytics-cookie-notice');
    }
    
    $section_data['tracked_extra_data'][] = $t_cegg;
}

if ( $add_section ) {
    $this->data['cegg'] = $section_data;
}