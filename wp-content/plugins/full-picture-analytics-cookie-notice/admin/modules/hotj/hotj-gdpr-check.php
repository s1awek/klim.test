<?php

$add_section = false;
$section_data = [
    'module_name' => $this->format == 'cdb' ? $module_info['name'] : $this->modules_names['hotj'],
    'setup' => [],
    'tracked_extra_data' => [],
];

// Get info on tracked data only if user consent is required

if ( empty( $settings['data_suppression'] ) ) {

    $loading_status = $this->get_module_loading_status( 'hotj', $module_info, $settings );
    
    if ( $loading_status !== false ) {
        $add_section = true;
        $section_data['setup'][] = $loading_status;
    }

    if ( isset( $settings['identif_users'] ) ) {

        $add_section = true;

        if ( $this->format == 'cdb' ) {   
            $t_hotj = 'Unique ID (user email email and/or a user id)';
        } else {
            $t_hotj = esc_html__('Unique ID (user email email and/or a user id)', 'full-picture-analytics-cookie-notice');
        }

        $section_data['tracked_extra_data'][] = $t_hotj;

    }

    if ( $this->woo_enabled && isset( $settings['tag_woo_purchases_data'] ) && in_array( 'id', $settings['tag_woo_purchases_data'] ) ) {

        $add_section = true;

        if ( $this->format == 'cdb' ) {
            $t_order_id = 'Order ID';
        } else {
            $t_order_id = esc_html__('Order ID', 'full-picture-analytics-cookie-notice');
        }

        $section_data['tracked_extra_data'][] = $t_order_id;
    }

// If user consent is required, just display info about it
} else {
    
    $add_section = true;

    if ( $this->format == 'cdb' ) {
        $t_pii_status = 'Hotjar works in data supression mode. It loads without asking for consent but does not track identifiable information.';
    } else {
        $t_pii_status = esc_html__('Hotjar works in data supression mode. It loads without asking for consent but does not track identifiable information.', 'full-picture-analytics-cookie-notice');
    }

    $section_data['setup'][] = [ 'ok', $t_pii_status ];
}



// Add section
if ( $add_section ) $this->data['hotj'] = $section_data;