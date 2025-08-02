<?php
if ( ! empty( $this->tools['cscr'] ) ) {
    
    $module_info = $this->get_module_info( 'cscr' );
    $this->set_basic_module_info( 'cscr', $module_info );

    $settings = $this->clean_val_id == 'cscr' && ! empty( $this->clean_val ) ? $this->clean_val : get_option('fupi_cscr');
    $script_placement = ['fupi_head_scripts', 'fupi_footer_scripts'];

    foreach ( $script_placement as $placement ) {
        
        if ( ! empty ( $settings[$placement] ) ) foreach ( $settings[$placement] as $script_settings ) {

            if ( ! empty( $script_settings['disable'] ) ) continue;
            if ( isset( $script_settings['not_installer'] ) ) continue; // this checks if the script is set as non-tracking
            if ( empty ( $script_settings['stats'] ) && empty ( $script_settings['market'] ) && empty ( $script_settings['pers'] ) ) continue;

            // Get title
            $title = ! empty( $script_settings['title'] ) ? esc_attr( $script_settings['title'] ) : 'Script ' . $script_settings['id'];
            
            // Start description text
            $descr = 'WP Full Picture loads a script:  "' . $title . '". ';

            $req_consents = [];

            if ( ! empty ( $script_settings['stats'] ) ) $req_consents[] = 'statistics';
            if ( ! empty ( $script_settings['market'] ) ) $req_consents[] = 'marketing';
            if ( ! empty ( $script_settings['pers'] ) ) $req_consents[] = 'personalisation';

            $delimiter = count( $req_consents ) == 2 ? ' and ' : ', ';
            $descr .= 'It is set to require consents for ' . join(  $delimiter, $req_consents );
            
            if ( isset( $script_settings['force_load'] ) ) {
                $descr .= ' but it is force-loaded before visitors can make their choices.';
            }
            
            
            $this->data['cscr']['setup'][] = [ 'ok', $descr ];
        }
    }
}