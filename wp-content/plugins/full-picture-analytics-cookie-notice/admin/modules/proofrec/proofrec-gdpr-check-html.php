<?php

// ONLY HTML OUTPUT

$this->data['cdb'] = [ 
    'module_name' => esc_html__('Records of consents', 'full-picture-analytics-cookie-notice'),
    'setup' => [],
];

$records_are_enabled = false;

if ( isset( $this->tools['proofrec'] ) && $this->pp_ok ) {

    if ( ! empty ( $this->proofrec['storage_location'] ) && $this->proofrec['storage_location'] == 'email' ) {
        
        $records_are_enabled = true;

        $this->data['cdb']['top comments'] = [ 
            esc_html__('Saving proofs of visitor\'s tracking consents is enabled. Proofs are saved in an email account, along with copies of privacy policy and tracking settings.', 'full-picture-analytics-cookie-notice')
        ];
        
    } else if ( ! empty( $this->proofrec['cdb_key'] ) ) {
        
        $records_are_enabled = true;

        $this->data['cdb']['top comments'] = [ 
            esc_html__('Saving proofs of visitor\'s tracking consents is enabled. Proofs are saved in a cloud database ConsentsDB, along with copies of privacy policy and tracking settings.', 'full-picture-analytics-cookie-notice') 
        ];

        $this->data['cdb']['pp comments'][] = [ 
            sprintf( esc_html__('Add to your privacy policy information about ConsentsDB. We prepared a sample text for you that you can adjust to your needs and legal requirements in your country. %1$sView the text%2$s', 'full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/support/documentation/texts-for-the-privacy-policy/">', '</a>' ),
        ];
    }
}

if ( ! $records_are_enabled ) {
    $this->data['cdb']['setup'][] = [ 
        'alert', 
        esc_html__('Save proofs that your visitors consented to tracking. Enable the Records of Consents module. Keeping records is required by GDPR.', 'full-picture-analytics-cookie-notice')
    ];
}