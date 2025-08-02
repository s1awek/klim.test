<?php

$section_data = [
    'module_name' => $this->format == 'cdb' ? $module_info['name'] : $this->modules_names['posthog'],
    'setup' => [],
];

// get loading status
$loading_status = $this->get_module_loading_status( 'posthog', $module_info, $settings );

if ( $loading_status !== false ) {
    $section_data['setup'][] = $loading_status;
}

if ( $this->format == 'cdb' ) {
    $t_posthog_ok = 'Visitor\'s data is being kept on servers in the EU';
    $t_posthog_alert = 'Visitor\'s data is not being kept on servers in the EU';
} else {
    $t_posthog_ok = esc_html__('Visitor\'s data is being kept on servers in the EU', 'full-picture-analytics-cookie-notice');
    $t_posthog_alert = esc_html__('Visitor\'s data is not being kept on servers in the EU', 'full-picture-analytics-cookie-notice');
}

if ( isset( $settings['data_in_eu'] ) ) {
    $section_data['setup'][] = [ 'ok', $t_posthog_ok ];
} else {
    $section_data['setup'][] = [ 'alert', $t_posthog_alert ];
}

$this->data['posthog'] = $section_data;