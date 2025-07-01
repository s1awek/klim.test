<?php

class Fupi_TRACK404_admin {

    private $settings;

    public function __construct(){
        $this->settings = get_option('fupi_track404');
        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters(){
        add_action( 'fupi_register_setting_track404', array( $this, 'register_module_settings' ) );
        add_filter( 'fupi_track404_add_fields_settings', array( $this, 'add_fields_settings' ), 10, 1 );
        add_filter( 'fupi_track404_get_faq_data', array( $this, 'get_faq_data' ), 10, 1 );
        add_filter( 'fupi_track404_get_page_descr', array( $this, 'get_page_descr' ), 10, 2 );
    }

    public function add_fields_settings( $sections ){
        include_once 'track404-fields.php';
        return $sections;
    }

    public function register_module_settings(){
        register_setting( 'fupi_track404', 'fupi_track404', array( 'sanitize_callback' => array( $this, 'sanitize_fields' ) ) );
    }

    public function sanitize_fields( $input ){
        include 'track404-sanitize.php';
		include FUPI_PATH . '/admin/common/fupi-clear-cache.php';
		return $clean_data; 
    }

    public function get_faq_data( $empty_arr ){
        include_once 'track404-faq.php';
        return [ 
            'q' => $questions, 
            'a' => $answers 
        ];
    }

    public function get_page_descr( $section_id, $no_woo_descr_text ){
        include 'track404-descr.php';
        return $ret_text;
    }
}