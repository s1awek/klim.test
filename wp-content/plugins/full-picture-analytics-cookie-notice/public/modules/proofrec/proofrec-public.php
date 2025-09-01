<?php

class Fupi_PROOFREC_public {

    private $settings;
    private $tools;

    public function __construct(){
        $this->settings = get_option('fupi_proofrec');
        
        if ( empty ( $this->settings ) ) $this->settings = [];
        
        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters(){
        add_filter( 'fupi_modify_fp_object', array($this, 'add_data_to_fp_object'), 10, 1 );       
    }

    public function add_data_to_fp_object( $fp ){

        $send_to_email      = ! empty ( $this->settings['storage_location'] ) && $this->settings['storage_location'] == 'email';
        $send_to_cdb        = ! $send_to_email && ! empty ( $this->settings['cdb_key'] );
        $modif_settings     = $this->settings;

        if ( ! $send_to_email && ! $send_to_cdb ) {
            $modif_settings['save_consent'] = false;
        } else {
            unset( $modif_settings['cdb_key'] );
            $modif_settings['save_consent'] = true;
            $modif_settings['storage_location'] = $send_to_email ? 'email' : ( $send_to_cdb ? 'cdb' : false );
        }

        $fp['proofrec'] = $modif_settings;
        return $fp;
    }
}