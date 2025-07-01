<?php

class Fupi_FBP1_admin {

    private $settings;
    private $tools;
    private $cook;

    public function __construct(){
        $this->settings = get_option('fupi_fbp1');
        $this->tools = get_option('fupi_tools');
        $this->cook = get_option('fupi_cook');	

        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters(){
        add_action( 'fupi_register_setting_fbp1', array( $this, 'register_module_settings' ) );
        add_filter( 'fupi_fbp1_add_fields_settings', array( $this, 'add_fields_settings' ), 10, 1 );
        add_filter( 'fupi_fbp1_get_faq_data', array( $this, 'get_faq_data' ), 10, 1 );
        add_filter( 'fupi_fbp1_get_page_descr', array( $this, 'get_page_descr' ), 10, 2 );
    }

    public function add_fields_settings( $sections ){
        include_once 'fbp1-fields.php';
        return $sections;
    }

    public function register_module_settings(){
        register_setting( 'fupi_fbp1', 'fupi_fbp1', array( 'sanitize_callback' => array( $this, 'sanitize_fields' ) ) );
    }

    public function sanitize_fields( $input ){
        
        include 'fbp1-sanitize.php';
		
		if ( ! empty ( $this->tools['cook'] ) && ! empty( $this->cook['cdb_key'] ) && ! empty ( get_privacy_policy_url() ) ) {
			include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
			new Fupi_compliance_status_checker( 'cdb', $this->cook, false );
		}
		
		include FUPI_PATH . '/admin/common/fupi-clear-cache.php';
		return $clean_data; 
    }
    
    public function get_faq_data( $empty_arr ){
        include_once 'fbp1-faq.php';
        return [ 
            'q' => $questions, 
            'a' => $answers 
        ];
    }

    public function get_page_descr( $section_id, $no_woo_descr_text ){
        include 'fbp1-descr.php';
        return $ret_text;
    }
}