<?php

$add_section = false;
$section_data = [
    'module_name' => $this->format == 'cdb' ? $module_info['name'] : $this->modules_names['gtm'],
    'setup' => [],
    'tracked_extra_data' => [],
];

// Add information on required setup for GDPR compliance

if ( $this->format == 'html' ) {
    
    $add_section = true;

    $section_data['setup'][] = [ 'warning', esc_html__('Make sure to trigger tags in GTM after visitors agree to tracking. You must set every tag in GTM to require additional consents. Find out more in the GTM module settings.', 'full-picture-analytics-cookie-notice') ];
}

// Get list of tracked personal data

$tracked_priv_info = [];

if ( $this->format == 'cdb' ) {
    $t_gtm_1 = 'User ID';
    $t_gtm_2 = 'Name and surname of a user or a client';
    $t_gtm_3 = 'User\'s email address and/or an email address of a client (even when not logged in, collected at the time of purchase)';
    $t_gtm_4 = 'User\'s phone number and/or phone number of a client (even when not logged in, collected at the time of purchase)';
    $t_gtm_5 = 'User\'s physical address and/or address of a client (even when not logged in, collected at the time of purchase)';
} else {
    $t_gtm_1 = esc_html__('User ID', 'full-picture-analytics-cookie-notice');
    $t_gtm_2 = esc_html__('Name and surname of a user or a client', 'full-picture-analytics-cookie-notice');
    $t_gtm_3 = esc_html__('User\'s email address and/or an email address of a client (even when not logged in, collected at the time of purchase)', 'full-picture-analytics-cookie-notice');
    $t_gtm_4 = esc_html__('User\'s phone number and/or phone number of a client (even when not logged in, collected at the time of purchase)', 'full-picture-analytics-cookie-notice');
    $t_gtm_5 = esc_html__('User\'s physical address and/or address of a client (even when not logged in, collected at the time of purchase)', 'full-picture-analytics-cookie-notice');
}

if ( isset( $settings['user_id'] ) ) $tracked_priv_info[] = $t_gtm_1;
if ( isset( $settings['user_realname'] ) ) $tracked_priv_info[] = $t_gtm_2;
if ( isset( $settings['user_email'] ) ) $tracked_priv_info[] = $t_gtm_3;
if ( isset( $settings['user_phone'] ) ) $tracked_priv_info[] = $t_gtm_4;
if ( isset( $settings['user_address'] ) ) $tracked_priv_info[] = $t_gtm_5;

if ( $this->format == 'cdb' ) {
    $t_gtm_6 = 'Order ID (in WooCommerce)';
}  else {
    $t_gtm_6 = esc_html__('Order ID (in WooCommerce)', 'full-picture-analytics-cookie-notice');
}

$tracked_priv_info[] = $t_gtm_6;

if ( isset( $settings['track_cf'] ) && is_array( $settings['track_cf'] ) ) {
    foreach ( $settings['track_cf'] as $tracked_meta ) {
        if ( substr( $tracked_meta['id'], 0, 5 ) == 'user|' ) {

            if ( $this->format == 'cdb' ) {
                $t_gtm_7 = 'User metadata with ID';
            }  else {
                $t_gtm_7 = esc_html__('User metadata with ID', 'full-picture-analytics-cookie-notice');
            }

            $tracked_priv_info[] = $t_gtm_7 . ' ' . substr( $tracked_meta['id'], 5 );
        }
    }
}

if ( count( $tracked_priv_info ) > 0 ) {
    $add_section = true;
    foreach ( $tracked_priv_info as $str ) {
        $section_data['tracked_extra_data'][] = $str;
    };
};

if ( $this->format != 'cdb' ) {

    // Add PP Comments (always come last in the section)
    $add_section = true;
    $section_data['pp comments'][] = esc_html__('The privacy policy must include information about tracking tools that are loaded with GTM, what data is tracked, what it is used and whether it is resold (to whom and to what purpose).', 'full-picture-analytics-cookie-notice');
}

// Add section
if ( $add_section ) $this->data['gtm'] = $section_data;