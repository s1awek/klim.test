<?php

$module_info = $this->get_module_info( 'cscr' );
$this->set_basic_module_info( 'cscr', $module_info );

$found_issues = false;

if ( ! empty( $this->tools['cscr'] ) ) {
    
    $settings = $this->clean_val_id == 'cscr' && ! empty( $this->clean_val ) ? $this->clean_val : get_option('fupi_cscr');
    $enabled_scripts = [];

    // get head and footer scripts
    $script_placement = ['fupi_head_scripts', 'fupi_footer_scripts'];

    foreach ( $script_placement as $placement ) {
        
        if ( ! empty ( $settings[$placement] ) ) foreach ( $settings[$placement] as $script_settings ) {

            if ( ! empty( $script_settings['disable'] ) ) continue;
            if ( isset( $script_settings['not_installer'] ) ) continue; // this checks if the script is set as non-tracking
            
            // Get title
            $title = ! empty( $script_settings['title'] ) ? esc_attr( $script_settings['title'] ) : 'Script ' . $script_settings['id'];
            
            // Start description text
            $descr = esc_html__('WP Full Picture loads a script', 'full-picture-analytics-cookie-notice') . ': "' . $title . '". ';
            
            $req_consents = [];
            
            if ( ! empty ( $script_settings['stats'] ) ) $req_consents[] = esc_html__('statistics', 'full-picture-analytics-cookie-notice');
            if ( ! empty ( $script_settings['market'] ) ) $req_consents[] = esc_html__('marketing', 'full-picture-analytics-cookie-notice');
            if ( ! empty ( $script_settings['pers'] ) ) $req_consents[] = esc_html__('personalisation', 'full-picture-analytics-cookie-notice');
            
            if ( count( $req_consents ) > 0 ){
                
                $enabled_scripts[] = $title;

                if ( isset( $script_settings['force_load'] ) ) {
                    
                    $found_issues = true;
                    $delimiter = count( $req_consents ) == 2 ? esc_html__( ' and ', 'full-picture-analytics-cookie-notice' ) : ', ';
                    
                    $descr .= esc_html__('It is set to require consents for', 'full-picture-analytics-cookie-notice') . ' ' . join(  $delimiter, $req_consents ) . ' ' . esc_html__('but it is force-loaded before visitors can make their choices.', 'full-picture-analytics-cookie-notice');

                    $this->data['cscr']['setup'][] = [ 'alert', $descr ];
                }

            } else {

                $found_issues = true;
                $script_status = 'warning';
                $descr .= esc_html__('The script is set to load without waiting for tracking consents. Make sure that it does not require any.', 'full-picture-analytics-cookie-notice');

                $this->data['cscr']['setup'][] = [ 'warning', $descr ];
            }
        }
    }

    if ( count( $enabled_scripts ) > 0 ) {
        $this->data['cscr']['pp comments'] = [ 
            [
                sprintf( esc_html__('Add information in your privacy policy about %1$s. Link to their privacy policies and write what data they collect, how it is used and whether or not it is shared with 3rd parties.', 'full-picture-analytics-cookie-notice'), join(', ', $enabled_scripts ) )
            ]
        ];
    }
}

if ( ! $found_issues ) {
    $this->data['cscr']['pre-setup'][] = [ 
        esc_html__('Did you use JavaScript snippets to install any tools that track your visitors? Move these snippets to the "Custom scripts" module. This way, the consent banner will be able to load them according to provided consents.', 'full-picture-analytics-cookie-notice')
    ];
}