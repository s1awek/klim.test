<?php

$iframeblock_enabled = ! empty ( $this->cook['iframe_auto_rules'] ) || ( ! empty( $this->cook['control_other_iframes'] ) && ! empty ( $this->cook['iframe_manual_rules'] ) );

if ( $iframeblock_enabled ) {

    $this->data['iframeblock'] = [ 
        'module_name' => 'Blocked iframes',
        'setup' => [],
    ];

    // Automatic iframe rules

    if ( ! empty( $this->cook['iframe_auto_rules'] ) && is_array( $this->cook['iframe_auto_rules'] ) ) {
        
        $delimiter = count( $this->cook['iframe_auto_rules'] ) == 2 ? ' and ' : ', ';
        $rules_str = join( $delimiter, $this->cook['iframe_auto_rules'] );
        $t_iframe_1 = 'Content from ' . $rules_str . ' is set to require consent to statistics and marketing.';

        $this->data['iframeblock']['setup'][] = [ 'ok', $t_iframe_1 ];
    }

    // Manual rules

    if ( ! empty( $this->cook['control_other_iframes'] ) && ! empty( $this->cook['iframe_manual_rules'] ) ) {

        foreach ( $this->cook['iframe_manual_rules'] as $rules ) {
            
            $descr_start = 'Content from ' . $rules['iframe_url'];
            $req_consents = [];

            if ( ! empty ( $rules['stats'] ) ) $req_consents[] = 'statistics';
            if ( ! empty ( $rules['market'] ) ) $req_consents[] = 'marketing';
            if ( ! empty ( $rules['pers'] ) ) $req_consents[] = 'personalisation';

            if ( count( $req_consents ) > 0 ){
                $this->data['iframeblock']['setup'][] = [ 'ok', $descr_start . ' requires consent to: ' . join( ', ', $req_consents ) . '.' ];
            } else {
                $this->data['iframeblock']['setup'][] = [ 'warning', $descr_start . ' is set to load without waiting for consents.' ];
            }
        };
    }
}