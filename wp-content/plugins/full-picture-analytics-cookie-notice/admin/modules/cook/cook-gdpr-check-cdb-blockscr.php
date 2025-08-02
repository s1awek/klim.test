<?php

$has_auto_rules = ! empty( $this->cook['scrblk_auto_rules'] );
$has_manual_rules = ! empty( $this->cook['control_other_tools'] ) && ! empty ( $this->cook['scrblk_manual_rules'] );

if ( $has_auto_rules || $has_manual_rules ) {

    $this->data['blockscr'] = [ 
        'module_name' => 'Tracking tools installed outside WP Full Picture',
        'setup' => [],
    ];

    // Auto rules

    if ( $has_auto_rules && is_array( $this->cook['scrblk_auto_rules'] ) ) {

        $auto_rules_tools = [];

        if ( ! empty ( $this->cook['scrblk_auto_rules'] ) && in_array( 'jetpack', $this->cook['scrblk_auto_rules'] ) ) {
            $auto_rules_tools[] = 'Jetpack Statistics';
        }

        if ( count ( $auto_rules_tools ) > 0 ) $this->data['blockscr']['setup'][] = [ 'ok', 'These tracking tools are automatically loaded according to the consent banner settings and visitors tracking preferenecs: ' . join( ', ', $auto_rules_tools ) ];
    }

    // Manual rules

    if ( $has_manual_rules && is_array( $this->cook['scrblk_manual_rules'] ) ) {

        foreach ( $this->cook['scrblk_manual_rules'] as $rules ) {

            // Check if is force loaded
            $force_loaded = ! empty ( $rules['force_load'] );
            
            // Get script title
            if ( ! empty ( $rules['title'] ) ) {
                $title = esc_attr( $rules['title'] );
            } else if ( ! empty ( $rules['name'] ) ) {
                $title = esc_attr( $rules['name'] );
            } else {
                $title = ! empty ( $rules['id'] ) ? esc_attr( $rules['id'] ) : 'No name provided';
            }

            // Get consents
            $req_consents = [];
            
            if ( ! empty ( $rules['stats'] ) ) $req_consents[] = 'statistics';
            if ( ! empty ( $rules['market'] ) ) $req_consents[] = 'marketing';
            if ( ! empty ( $rules['pers'] ) ) $req_consents[] = 'personalisation';
            
            $delimiter = count( $req_consents ) == 2 ? ' and ' : ', ';
            
            // Build descr
            $descr = 'Tracking tool with ' . $rules['block_by'] . '="' . $rules['url_part'] . '" and title/ID "' . $title . '"';
            
            // Don\'t forget to add "force_load" check

            if ( count( $req_consents ) > 0 && ! $force_loaded ){
                $descr .= ' is set to load after visitors agree to ' . join(  $delimiter, $req_consents ) . '.';
            } else {
                $descr .= ' is set to load without waiting for consents.';
            }

            $this->data['blockscr']['setup'][] = [ 'warning', $descr ];
        };
    }
}