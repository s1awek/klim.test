<?php

$add_section = false;
$section_data = [
    'module_name' => $this->format == 'cdb' ? $module_info['name'] : $this->modules_names['pla'],
    'setup' => [],
    'tracked_extra_data' => [],
];

// Get metadata
$usermeta_info = $this->get_tracked_usermeta( 'pla', $settings );

if ( $usermeta_info !== false ) {

    $add_section = true;

    if ( $this->format != 'cdb' ) {
        $section_data['setup'][] = ['warning', 'You are tracking custom user metadata. Make sure it does not hold information that can identify your users. Sending it to Plausible is against its Terms of Service.'];
    }

    $section_data['tracked_extra_data'][] = $usermeta_info;
}

// Add section
if ( $add_section ) $this->data['pla'] = $section_data;