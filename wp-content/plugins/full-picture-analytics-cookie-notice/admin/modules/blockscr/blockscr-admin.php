<?php

class Fupi_BLOCKSCR_admin {

    private $settings;
    private $tools;
    private $cook;

    public function __construct(){
        $this->settings = get_option( 'fupi_blockscr' );
        $this->tools = get_option('fupi_tools');
        $this->cook = get_option('fupi_cook');	

        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters(){
        add_action( 'fupi_register_setting_blockscr', array( $this, 'register_module_settings' ) );
        add_filter( 'fupi_blockscr_add_fields_settings', array( $this, 'add_fields_settings' ), 10, 1 );
        add_filter( 'fupi_blockscr_get_faq_data', array( $this, 'get_faq_data' ), 10, 1 );
        add_filter( 'fupi_blockscr_get_page_descr', array( $this, 'get_page_descr' ), 10, 2 );
    }

    public function register_module_settings(){
        register_setting( 'fupi_blockscr', 'fupi_blockscr', array( 'sanitize_callback' => array( $this, 'sanitize_fields' ) ) );
    }

    public function add_fields_settings( $sections ){
        include_once 'blockscr-fields.php';
        return $sections;
    }

    public function sanitize_fields( $input ){
        
        include 'blockscr-sanitize.php';
        
        if ( ! empty ( $this->tools['cook'] ) && ! empty( $this->cook['cdb_key'] ) && ! empty ( get_privacy_policy_url() ) ) {
            include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
            new Fupi_compliance_status_checker( 'cdb', $this->cook, false );
        }

		include FUPI_PATH . '/admin/common/fupi-clear-cache.php';
		return $clean_data;
    }

    public function get_faq_data( $empty_arr ){
        include_once 'blockscr-faq.php';
        return [ 
            'q' => $questions, 
            'a' => $answers 
        ];
    }

    public function get_page_descr( $section_id, $no_woo_descr_text ){
        include 'blockscr-descr.php';
        return $ret_text;
    }

}