<?php

class Fupi_POSTHOG_admin {

    private $settings;
    private $tools;
    private $cook;

    public function __construct(){
        $this->settings = get_option('fupi_posthog');
        $this->tools = get_option('fupi_tools');
        $this->cook = get_option('fupi_cook');	
        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters(){
        add_action( 'fupi_register_setting_posthog', array( $this, 'register_module_settings' ) );
        add_filter( 'fupi_posthog_add_fields_settings', array( $this, 'add_fields_settings' ), 10, 1 );
        add_filter( 'fupi_posthog_get_page_descr', array( $this, 'get_page_descr' ), 10, 2 );
    }

    public function add_fields_settings( $sections ){
        include_once 'posthog-fields.php';
        return $sections;
    }

    public function register_module_settings(){
        register_setting( 'fupi_posthog', 'fupi_posthog', array( 'sanitize_callback' => array( $this, 'sanitize_fields' ) ) );
    }

    public function sanitize_fields( $input ){
        
        include 'posthog-sanitize.php';
		
		if ( ! empty ( $this->tools['cook'] ) && ! empty( $this->cook['cdb_key'] ) && ! empty ( get_privacy_policy_url() ) ) {
			include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
			new Fupi_compliance_status_checker( 'cdb', $this->cook, false );
		}
		
		include FUPI_PATH . '/admin/common/fupi-clear-cache.php';
		return $clean_data; 
    }

    public function get_page_descr( $section_id, $no_woo_descr_text ){
        include 'posthog-descr.php';
        return $ret_text;
    }
}