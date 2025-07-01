<?php

class Fupi_PRIVEX_public {

    private $settings;

    public function __construct(){

        $this->settings = get_option('fupi_privex');
        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters(){
        add_action( 'init', array( $this, 'add_fpinfo_shortcode' ) );
    }

    public function add_fpinfo_shortcode(){
        add_shortcode( 'fp_info', array( $this, 'fupi_info' ) );
    }

	public function fupi_info ( $atts, $content = null ) {

        $a = shortcode_atts( array(
            'display' => 'list',
        ), $atts );
        
        // include_once FUPI_PATH . '/includes/fupi_modules_data.php';
        include_once FUPI_PATH . '/public/modules/privex/privacy_generator.php';
        
        $fupi_policy_generator = new Fupi_policy_generator( $a['display'] );
        
        return $fupi_policy_generator->output();
	}
}