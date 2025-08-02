<?php

$add_section = false;
$section_data = [
    'module_name' => $this->format == 'cdb' ? $module_info['name'] : $this->modules_names['gads'],
    'setup' => [],
    'tracked_extra_data' => [],
];

// Get loading status

$loading_status = $this->get_module_loading_status( 'gads', $module_info, $settings );

if ( $loading_status !== false ) {
    $add_section = true;
    $section_data['setup'][] = $loading_status;
}

// Check Enh. Conv.

if ( isset( $settings['enh_conv'] ) ) {

    $add_section = true;

    if ( $this->format == 'cdb' ) {
        $t_gads_extra_1 = 'Real name, surname, phone number, email address and physical address of customers and logged-in users (enabled with Enhanced Conversions)';
    } else {
        $t_gads_extra_1 = esc_html__('Real name, surname, phone number, email address and physical address of customers and logged-in users (enabled with Enhanced Conversions)', 'full-picture-analytics-cookie-notice');
    };

    $section_data['tracked_extra_data'][] = $t_gads_extra_1;
}

// Check Woo

if ( ! empty( $this->tools['woo'] ) ) {

    $add_section = true;

    if ( $this->format == 'cdb' ) {   
        $t_order_id = 'Order ID';
    } else {
        $t_order_id = esc_html__('Order ID', 'full-picture-analytics-cookie-notice');
    }

    $section_data['tracked_extra_data'][] = $t_order_id;
}

// Check URL Passthrough

$passthr = $this->check_url_passthrough();

if ( $passthr !== false ) {
    $add_section = true;
    $section_data['setup'][] = $passthr;
}

// Add section
if ( $add_section ) $this->data['gads'] = $section_data;