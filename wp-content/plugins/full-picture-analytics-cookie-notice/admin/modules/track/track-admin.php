<?php

class Fupi_TRACK_admin {

    private $settings;
    private $tools;
    private $proofrec;

    public function __construct(){
        $this->settings = get_option('fupi_track');
        $this->tools = get_option('fupi_tools');
        $this->proofrec = get_option('fupi_proofrec');
        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters(){
        add_action( 'fupi_register_setting_track', array( $this, 'register_module_settings' ) );
        add_filter( 'fupi_track_add_fields_settings', array( $this, 'add_fields_settings' ), 10, 1 );
        add_filter( 'fupi_track_get_page_descr', array( $this, 'get_page_descr' ), 10, 2 );
    }

    public function add_fields_settings( $sections ){
        include_once 'track-fields.php';
        return $sections;
    }

    public function register_module_settings(){
        register_setting( 'fupi_track', 'fupi_track', array( 'sanitize_callback' => array( $this, 'sanitize_fields' ) ) );
    }

    public function sanitize_fields( $input ){
        
        include 'track-sanitize.php';
        
        if ( apply_filters( 'fupi_updating_many_options', false ) ) return $clean_data;

        if ( ! empty ( $this->tools['cook'] ) && ! empty ( $this->tools['proofrec'] ) && ! empty ( get_privacy_policy_url() ) ) {
			include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
			$gdpr_checker = new Fupi_compliance_status_checker( 'track', $clean_data );
            $gdpr_checker->send_and_return_status();
		}

		include FUPI_PATH . '/admin/common/fupi-clear-cache.php';
		return $clean_data; 
    }

    public function get_page_descr( $section_id, $no_woo_descr_text ){
        include 'track-descr.php';
        return $ret_text;
    }
}