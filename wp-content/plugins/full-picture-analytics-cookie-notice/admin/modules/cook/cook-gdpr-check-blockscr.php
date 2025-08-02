<?php

$this->data['blockscr'] = [ 
    'module_name' => esc_html__('Tracking tools installed with other plugins', 'full-picture-analytics-cookie-notice'),
    'setup' => [
        [ 
            'warning',
            esc_html__('Load tracking tools that you installed without WP Full Picture after visitors agree to tracking. You can set it up in the Consent Banner module > Control other tracking tools.', 'full-picture-analytics-cookie-notice'),
        ],
    ],
    'pp comments' => [ 
        esc_html__('Add information in your privacy policy about additional tracking tools that you use. Link to their privacy policies and write what data they collect, how it is used and whether or not it is shared with 3rd parties.', 'full-picture-analytics-cookie-notice') 
    ]
];

if ( ! empty( $this->cook['control_other_tools'] ) && ! empty( $this->cook['scrblk_manual_rules'] ) && is_array( $this->cook['scrblk_manual_rules'] ) ) {

    foreach ( $this->cook['scrblk_manual_rules'] as $rules ) {
        
        $force_loaded = false;

        // if some consents are required...
        if ( ! empty ( $rules['stats'] ) || ! empty ( $rules['market'] ) || ! empty ( $rules['pers'] ) ) {

            // ...but the script is set to diregard them
            if ( ! empty ( $rules['force_load'] ) ) {
                $force_loaded = true;
            } else {
                continue;
            }
        }

        if ( ! empty ( $rules['title'] ) ) {
            $title = esc_attr( $rules['title'] ) ;
        } else if ( ! empty ( $rules['name'] ) ) {
            $title = esc_attr( $rules['name'] );
        } else {
            $title = ! empty ( $rules['id'] ) ? esc_attr( $rules['id'] ) : esc_html__('No name provided', 'full-picture-analytics-cookie-notice');
        }

        if ( $force_loaded  ) {
            $this->data['blockscr']['setup'][] = [
                'alert', 
                sprintf( esc_html__('Tracking tool with %1$s="%2$s" and title/ID "%3$s" requires tracking consents but is set to load without waiting for tracking consent.', 'full-picture-analytics-cookie-notice'), $rules['block_by'],  $rules['url_part'], $title )
            ];
        } else {
            $this->data['blockscr']['setup'][] = [
                'warning', 
                sprintf( esc_html__('Tracking tool with %1$s="%2$s" and title/ID "%3$s" is set to load without waiting for tracking consent. Are you sure it does not track your visitors? If not, set it to load after consent in the Consent Banner > Control other tracking tools.', 'full-picture-analytics-cookie-notice'), $rules['block_by'],  $rules['url_part'], $title )
            ];
        }

    };
};