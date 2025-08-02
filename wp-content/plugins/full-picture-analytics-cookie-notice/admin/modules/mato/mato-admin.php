<?php

class Fupi_MATO_admin {

    private $settings;
    private $tools;
    private $cook;
    private $proofrec;

    public function __construct(){
        $this->settings = get_option('fupi_mato');
        $this->tools = get_option('fupi_tools');
        $this->cook = get_option('fupi_cook');
        $this->proofrec = get_option('fupi_proofrec');
        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters(){
        add_action( 'fupi_register_setting_mato', array( $this, 'register_module_settings' ) );
        add_filter( 'fupi_mato_add_fields_settings', array( $this, 'add_fields_settings' ), 10, 1 );
        add_filter( 'fupi_mato_get_faq_data', array( $this, 'get_faq_data' ), 10, 1 );
        add_filter( 'fupi_mato_get_page_descr', array( $this, 'get_page_descr' ), 10, 2 );
    }

    public function add_fields_settings( $sections ){
        include_once 'mato-fields.php';
        return $sections;
    }

    public function register_module_settings(){
        register_setting( 'fupi_mato', 'fupi_mato', array( 'sanitize_callback' => array( $this, 'sanitize_fields' ) ) );
    }

    public function sanitize_fields( $input ){
        
        include 'mato-sanitize.php';

        if ( apply_filters( 'fupi_updating_many_options', false ) ) return $clean_data;
		
		if ( ! empty ( $this->tools['cook'] ) && ! empty ( $this->tools['proofrec'] ) && ! empty ( get_privacy_policy_url() ) ) {

			include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
			$gdpr_checker = new Fupi_compliance_status_checker( 'mato', $clean_data );
            $gdpr_checker->send_and_return_status();
		}
		
		include FUPI_PATH . '/admin/common/fupi-clear-cache.php';
		return $clean_data; 
    }

    public function get_faq_data( $empty_arr ){
        include_once 'mato-faq.php';
        return [ 
            'q' => $questions, 
            'a' => $answers 
        ];
    }

    public function get_page_descr( $section_id, $no_woo_descr_text ){
        include 'mato-descr.php';
        return $ret_text;
    }
}