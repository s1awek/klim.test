<?php

// ONLY HTML OUTPUT

$this->data['cdb'] = [ 
    'module_name' => esc_html__('Records of consents', 'full-picture-analytics-cookie-notice'),
    'setup' => [],
];

$records_are_enabled = false;

if ( isset( $this->tools['proofrec'] ) && ! empty ( $this->priv_policy_url ) ) {

    if ( ! empty ( $this->proofrec['storage_location'] ) && $this->proofrec['storage_location'] == 'email' ) {
        
        $records_are_enabled = true;
        
        $proofrec_method_info = [
            'ok',
            esc_html__('Proofs are saved in an email account, along with copies of privacy policy and tracking settings.', 'full-picture-analytics-cookie-notice')
        ];
        
    } else if ( ! empty( $this->proofrec['cdb_key'] ) ) {
        
        $records_are_enabled = true;

        $proofrec_method_info = [
            'ok',
            esc_html__('Proofs are saved in a cloud database ConsentsDB, along with copies of privacy policy and tracking settings.', 'full-picture-analytics-cookie-notice')
        ];
    }
}

if ( $records_are_enabled ) {

    $this->data['cdb']['setup'][] = [ 
        'ok', 
        esc_html__('Saving proofs of visitor\'s tracking consents is enabled.', 'full-picture-analytics-cookie-notice') 
    ];

    $this->data['cdb']['setup'][] = $proofrec_method_info;

    $this->data['cdb']['pp comments'][] = [ 
        esc_html__('Add to your privacy policy information that WP Full Picture uses the following cookie:', 'full-picture-analytics-cookie-notice'), 
        [ 
            $t_cook_59 = esc_html__('cdb_id - a necessary cookie. It is saved after visitors make a choice in the consent banner. It stores an identifier of a proof of consent. It does not expire.', 'full-picture-analytics-cookie-notice')
        ] 
    ];

} else {
    
    $this->data['cdb']['setup'][] = [ 
        'alert', 
        esc_html__('Save proofs that your visitors consented to tracking. Enable the Records of Consents module. Keeping records is required by GDPR.', 'full-picture-analytics-cookie-notice')
    ];
}