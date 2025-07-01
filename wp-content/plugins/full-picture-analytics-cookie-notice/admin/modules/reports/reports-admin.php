<?php

class Fupi_REPORTS_admin {

    /*    
    This only adds a settings page where the reports can be configured. Functions which add Reports to admin are inside the class-fupi-admin.php, since reports can be aqdded also by plausible
    */

    private $settings;

    public function __construct(){
        $this->settings = get_option('fupi_reports');
        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters(){
        add_action( 'fupi_register_setting_reports', array( $this, 'register_module_settings' ) );
        add_filter( 'fupi_reports_add_fields_settings', array( $this, 'add_fields_settings' ), 10, 1 );
        add_filter( 'fupi_reports_get_faq_data', array( $this, 'get_faq_data' ), 10, 1 );
        add_filter( 'fupi_reports_get_page_descr', array( $this, 'get_page_descr' ), 10, 2 );
    }

    public function add_fields_settings( $sections ){
        include_once 'reports-fields.php';
        return $sections;
    }

    public function register_module_settings(){
        register_setting( 'fupi_reports', 'fupi_reports', array( 'sanitize_callback' => array( $this, 'sanitize_fields' ) ) );
    }

    public function sanitize_fields( $input ){
        include 'reports-sanitize.php';
		include FUPI_PATH . '/admin/common/fupi-clear-cache.php';
		return $clean_data; 
    }

    public function get_faq_data( $empty_arr ){
        include_once 'reports-faq.php';
        return [ 
            'q' => $questions, 
            'a' => $answers 
        ];
    }

    public function get_page_descr( $section_id, $no_woo_descr_text ){
        include 'reports-descr.php';
        return $ret_text;
    }
}