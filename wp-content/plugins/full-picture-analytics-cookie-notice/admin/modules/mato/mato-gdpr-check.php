<?php

$add_section = false;
$section_data = [
    'module_name' => $this->format == 'cdb' ? $module_info['name'] : $this->modules_names['mato'],
    'setup' => [],
    'tracked_extra_data' => [],
];

// Get loading status

// when privacy mode is enabled, then script loading always follows GDPR
if ( isset( $settings['no_cookies'] ) ) {
    
    $add_section = true;

    if ( $this->format == 'cdb' ) {
        $t_mato_1 = 'Matomo works in privacy mode and does not require tracking consent. It does not track identifiable information before users agree to tracking and only loads safe, necessary cookies.';
    } else {
        $t_mato_1 = esc_html__('Matomo works in privacy mode and does not require tracking consent. It does not track identifiable information before users agree to tracking and only loads safe, necessary cookies.', 'full-picture-analytics-cookie-notice');
    }
    
    $section_data['setup'][] = [ 'ok', $t_mato_1 ];

} else {

    $loading_status = $this->get_module_loading_status( 'mato', $module_info, $settings );

    if ( $loading_status !== false ) {
        $add_section = true;
        $section_data['setup'][] = $loading_status;
    }
}

// Check usermeta

$usermeta_info = $this->get_tracked_usermeta( 'mato', $settings ); // id is either ga41 or ga42

if ( $usermeta_info !== false ) {
    $add_section = true;
    $section_data['tracked_extra_data'][] = $usermeta_info;
}

// Check User ID

if ( isset( $settings['set_user_id'] ) ) {

    if ( $this->format == 'cdb' ) {
        $t_mato_3 = 'User ID - for cross-browser tracking (used only after users agree to tracking).';
    } else {
        $t_mato_3 = esc_html__('User ID - for cross-browser tracking (used only after users agree to tracking).', 'full-picture-analytics-cookie-notice');
    }

    $add_section = true;
    $section_data['tracked_extra_data'][] = $t_mato_3;
}

if ( $this->woo_enabled ) {
    
    $add_section = true;

    if ( isset( $settings['no_cookies'] ) ) {

        if ( $this->format == 'cdb' ) {
            $t_mato_6 = 'Real order ID is tracked when visitors agree to tracking. Random order ID is used when they don\'t.';
        } else {
            $t_mato_6 = esc_html__('Real order ID is tracked when visitors agree to tracking. Random order ID is used when they don\'t.', 'full-picture-analytics-cookie-notice');
        }

        $section_data['tracked_extra_data'][] = $t_mato_6;
    } else {

        if ( $this->format == 'cdb' ) {   
            $t_order_id = 'Order ID';
        } else {
            $t_order_id = esc_html__('Order ID', 'full-picture-analytics-cookie-notice');
        }

        $section_data['tracked_extra_data'][] = $t_order_id;
    }
}

if ( $add_section ) $this->data['mato'] = $section_data;