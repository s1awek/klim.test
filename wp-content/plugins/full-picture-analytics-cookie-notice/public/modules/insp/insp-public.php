<?php

class Fupi_INSP_public {

    private $settings;
    private $tools;
    private $main;

    public function __construct(){

        $this->settings = get_option('fupi_insp');
        
        if ( ! empty ( $this->settings ) ) {
            $this->tools = get_option('fupi_tools');
            $this->main = get_option('fupi_main');
            $this->add_actions_and_filters();
        }
    }

    private function add_actions_and_filters(){
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'fupi_modify_fp_object', array( $this, 'add_data_to_fp_object'), 10, 1 );
    }

    public function add_data_to_fp_object( $fp ){
        $fp['insp'] = $this->settings;
        $fp['tools'][] = 'insp';
        return $fp;
    }

    public function enqueue_scripts(){

        $head_args = [ 'in_footer' => false ];
        $footer_args = [ 'in_footer' => true ];

        if ( ! empty( $this->main ) && isset( $this->main['async_scripts'] ) ) {
            $head_args['strategy'] = 'defer';
            $footer_args['strategy'] = 'defer';
        }

        $reqs = ! empty( $this->tools['woo'] ) && function_exists('WC') ? array('fupi-helpers-js', 'fupi-helpers-footer-js', 'fupi-woo-js', 'fupi-insp-head-js') : array('fupi-helpers-js', 'fupi-helpers-footer-js', 'fupi-insp-head-js');

        /* ^ */ wp_enqueue_script( 'fupi-insp-head-js', FUPI_URL . 'public/modules/insp/fupi-insp.js', array( 'fupi-helpers-js' ), FUPI_VERSION, $head_args );
        /* _ */ wp_enqueue_script( 'fupi-insp-footer-js', FUPI_URL . 'public/modules/insp/fupi-insp-footer.js', $reqs, FUPI_VERSION, $footer_args );
    }
}