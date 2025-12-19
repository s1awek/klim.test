<?php

$this->data['iframeblock'] = [ 
    'module_name' => esc_html__('Content from other websites', 'full-picture-analytics-cookie-notice'),
    'top comments' => [
        esc_attr__( 'Content from other sites, like YouTube videos or Google Maps, can track your visitors. Make sure it loads after visitor\'s consents.', 'full-picture-analytics-cookie-notice'),
    ],
    'setup' => [
        [ 
            'warning',
            sprintf( esc_html__('Go to the Consent Banner module > Control iframes and set up blocking content embedded from other sites. It will display an image placeholder until visitors agree to being tracked. %1$sSee how it works and learn more%2$s', 'full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/support/documentation/how-iframes-manager-works-and-how-to-set-it-up/">', '</a>' )
        ]
    ],
    'pp comments' => [ 
        esc_html__('Add information in your privacy policy that your website loads content from other sources and what happens with their data after they agree to tracking. You can link to their privacy policies.', 'full-picture-analytics-cookie-notice') 
    ]
];

// Check if there are any manual rules which do not require any consents
if ( ! empty( $this->cook['control_other_iframes'] ) && ! empty( $this->cook['iframe_manual_rules'] ) ) {

    foreach ( $this->cook['iframe_manual_rules'] as $rules ) {

        if ( ! empty ( $rules['stats'] ) || ! empty ( $rules['market'] ) || ! empty ( $rules['pers'] ) ) continue;

        $this->data['iframeblock']['setup'][] = [ 
            'warning', 
            'Content from ' . $rules['iframe_url'] . esc_html__(' is set to load without waiting for tracking consent. Are you sure it does not track your visitors? If not, set it to load after consent in the Consent Banner > Control iframes.', 'full-picture-analytics-cookie-notice') 
        ];
    };
}