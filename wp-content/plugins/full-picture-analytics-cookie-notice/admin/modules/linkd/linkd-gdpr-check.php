<?php

$section_data = [
    'module_name' => $this->format == 'cdb' ? $module_info['name'] : $this->modules_names['linkd'],
    'setup' => [],
];

// get loading status
$loading_status = $this->get_module_loading_status( 'linkd', $module_info, $settings );

if ( $loading_status !== false ) {
    $section_data['setup'][] = $loading_status;
    $this->data['linkd'] = $section_data;
}