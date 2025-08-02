<?php

$section_data = [
    'module_name' => $this->format == 'cdb' ? $module_info['name'] : $this->modules_names['tik'],
    'setup' => [],
];

// get loading status
$loading_status = $this->get_module_loading_status( 'tik', $module_info, $settings );

if ( $loading_status !== false ) {
    $section_data['setup'][] = $loading_status;
    $this->data['tik'] = $section_data;
}