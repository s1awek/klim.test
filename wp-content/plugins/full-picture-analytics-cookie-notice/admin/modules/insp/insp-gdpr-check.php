<?php

$add_section = false;
$section_data = [
    'module_name' => $this->format == 'cdb' ? $module_info['name'] : $this->modules_names['insp'],
    'setup' => [],
    'tracked_extra_data' => [],
];

// Get loading status

$loading_status = $this->get_module_loading_status( 'insp', $module_info, $settings );

if ( $loading_status !== false ) {
    $add_section = true;
    $section_data['setup'][] = $loading_status;
}

if ( isset( $settings['ab_test_script'] ) ) {
    
    $add_section = true;

    if ( $this->format == 'cdb' ) {   
        $t_insp_1 = 'The script loads an additional script for A/B testing which requires consent to personalisation.';
    } else {
        $t_insp_1 = esc_html__('The script loads an additional script for A/B testing which requires consent to personalisation.', 'full-picture-analytics-cookie-notice');
    }

    $section_data['setup'][] = [ 'ok', $t_insp_1 ];
}

if ( isset( $settings['identif_users'] ) ) {
    
    $add_section = true;

    if ( $this->format == 'cdb' ) {   
        $t_insp_2 = 'Unique ID (user email email, user id or username).';
    } else {
        $t_insp_2 = esc_html__('Unique ID (user email email, user id or username).', 'full-picture-analytics-cookie-notice');
    }

    $section_data['tracked_extra_data'][] = $t_insp_2;
}

// Add section
if ( $add_section ) $this->data['insp'] = $section_data;