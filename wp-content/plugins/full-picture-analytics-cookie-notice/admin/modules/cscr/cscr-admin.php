<?php

class Fupi_CSCR_admin {

    private $settings;
    private $tools;
    private $main;
    private $cook;

    public function __construct(){
        
        $this->settings = get_option('fupi_cscr');
        $this->tools = get_option('fupi_tools');
        $this->main = get_option('fupi_main');
        $this->cook = get_option('fupi_cook');

        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters(){
        add_action( 'fupi_register_setting_cscr', array( $this, 'register_module_settings' ) );
        add_filter( 'fupi_cscr_add_fields_settings', array( $this, 'add_fields_settings' ), 10, 1 );
        add_filter( 'fupi_cscr_get_faq_data', array( $this, 'get_faq_data' ), 10, 1 );
        add_filter( 'fupi_cscr_get_page_descr', array( $this, 'get_page_descr' ), 10, 2 );
    }

    // ADMIN PAGE

    public function add_fields_settings( $sections ){
        include_once 'cscr-fields.php';
        return $sections;
    }

    public function register_module_settings(){
        register_setting( 'fupi_cscr', 'fupi_cscr', array( 'sanitize_callback' => array( $this, 'sanitize_fields' ) ) );
    }

    public function sanitize_fields( $input ){

        include 'cscr-sanitize.php';
		
		if ( ! empty ( $this->tools['cook'] ) && ! empty( $this->cook['cdb_key'] ) && ! empty ( get_privacy_policy_url() ) ) {
			include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
			new Fupi_compliance_status_checker( 'cdb', $this->cook, false );
		}

        if ( ! empty( $this->settings ) && ! empty( $this->main['save_cscr_file'] ) ) {
            include_once 'cscr-generate-files.php';
		    new Fupi_generate_cscr_files( $clean_data );
		}
		
		include FUPI_PATH . '/admin/common/fupi-clear-cache.php';
		return $clean_data; 
    }

    public function get_faq_data( $empty_arr ){
        include_once 'cscr-faq.php';
        return [ 
            'q' => $questions, 
            'a' => $answers 
        ];
    }

    public function get_page_descr( $section_id, $no_woo_descr_text ){
        include 'cscr-descr.php';
        return $ret_text;
    }
}