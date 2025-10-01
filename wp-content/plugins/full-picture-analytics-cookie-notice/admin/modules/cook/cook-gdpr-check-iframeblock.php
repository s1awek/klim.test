<?php

$this->data['iframeblock'] = [ 
    'module_name' => esc_html__('Content from other websites', 'full-picture-analytics-cookie-notice'),
    'setup' => [
        [ 
            'warning',
            esc_html__('Load YouTube videos, Google Maps and other embedded content after visitors agree to it. You can set it up in the Consent Banner module > Control iframes.', 'full-picture-analytics-cookie-notice')
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