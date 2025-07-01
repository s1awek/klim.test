<?php

class Fupi_SIMPL_admin {

    private $settings;

    public function __construct(){
        $this->settings = get_option('fupi_simpl');
        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters(){
        add_action( 'fupi_register_setting_simpl', array( $this, 'register_module_settings' ) );
        add_filter( 'fupi_simpl_add_fields_settings', array( $this, 'add_fields_settings' ), 10, 1 );
        add_filter( 'fupi_simpl_get_page_descr', array( $this, 'get_page_descr' ), 10, 2 );
    }

    public function add_fields_settings( $sections ){
        include_once 'simpl-fields.php';
        return $sections;
    }

    public function register_module_settings(){
        register_setting( 'fupi_simpl', 'fupi_simpl', array( 'sanitize_callback' => array( $this, 'sanitize_fields' ) ) );
    }

    public function sanitize_fields( $input ){
        include 'simpl-sanitize.php';
		include FUPI_PATH . '/admin/common/fupi-clear-cache.php';
		return $clean_data; 
    }
    public function get_page_descr( $section_id, $no_woo_descr_text ){
        include 'simpl-descr.php';
        return $ret_text;
    }
}