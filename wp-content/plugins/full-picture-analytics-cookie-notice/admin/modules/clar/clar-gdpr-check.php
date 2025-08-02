<?php


$add_section = false;
$section_data = [
    'module_name' => $this->format == 'cdb' ? $module_info['name'] : $this->modules_names['clar'],
    'setup' => [],
    'tracked_extra_data' => [],
];

// Check "no cookie" mode

// if it is enabled, then the script always loads according to GDPR
if ( isset( $settings['no_cookie'] ) ) {    
    if ( $this->format == 'cdb' ) {
        $add_section = true;
        $section_data['setup'][0] = [ 'ok', 'MS Clarity works in the consent mode. It loads irrespective of user tracking consents but does not track identifiable information before users agree to tracking in the consent banner. Only necessary cookies are loaded.' ];
    }

// if it is disabled, then we have to check script loading status
} else {

    $loading_status = $this->get_module_loading_status( 'clar', $module_info, $settings );

    if ( $loading_status !== false ) {
        $add_section = true;
        $section_data['setup'][0] = $loading_status;
    }
}

// Check metadata
$usermeta_info = $this->get_tracked_usermeta( 'clar', $settings ); // id is either ga41 or ga42

if ( $usermeta_info !== false ) {
    $add_section = true;
    $section_data['tracked_extra_data'][] = $usermeta_info;
}

// Add section
if ( $add_section ) $this->data['clar'] = $section_data;