<?php

class Fupi_WOO_admin {

    private $settings;
    private $main;
    private $user_cap = 'manage_options';

    public function __construct(){
        
        $this->settings = get_option('fupi_woo');
        $this->main = get_option('fupi_main');

        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters(){
        add_action( 'fupi_register_setting_woo', array( $this, 'register_module_settings' ) );
        add_filter( 'fupi_woo_add_fields_settings', array( $this, 'add_fields_settings' ), 10, 1 );
        add_filter( 'fupi_woo_get_faq_data', array( $this, 'get_faq_data' ), 10, 1 );
        add_filter( 'fupi_woo_get_page_descr', array( $this, 'get_page_descr' ), 10, 2 );
    }

    public function add_fields_settings( $sections ){
        include_once 'woo-fields.php';
        return $sections;
    }

    public function register_module_settings(){
        register_setting( 'fupi_woo', 'fupi_woo', array( 'sanitize_callback' => array( $this, 'sanitize_fields' ) ) );
    }

    public function sanitize_fields( $input ){
        
        include 'woo-sanitize.php';
		include FUPI_PATH . '/admin/common/fupi-clear-cache.php';
		return $clean_data; 
    }

    public function get_faq_data( $empty_arr ){
        include_once 'woo-faq.php';
        return [ 
            'q' => $questions, 
            'a' => $answers 
        ];
    }

    public function get_page_descr( $section_id, $no_woo_descr_text ){
        include 'woo-descr.php';
        return $ret_text;
    }

}