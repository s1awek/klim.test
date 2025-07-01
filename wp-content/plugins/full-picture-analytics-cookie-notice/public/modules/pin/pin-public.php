<?php

class Fupi_PIN_public {

    private $settings;
    private $tools;
    private $main;

    public function __construct(){

        $this->settings = get_option('fupi_pin');
        
        if ( ! empty ( $this->settings ) ) {
            $this->tools = get_option('fupi_tools');
            $this->main = get_option('fupi_main');
            $this->add_actions_and_filters();
        }
    }

    private function add_actions_and_filters(){
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'fupi_modify_fp_object', array($this, 'add_data_to_fp_object'), 10, 1 );
    }

    public function add_data_to_fp_object( $fp ){
        $fp['pin'] = $this->settings;
        $fp['tools'][] = 'pin';
        return $fp;
    }

    public function enqueue_scripts(){

        $head_args = [ 'in_footer' => false ];
        $footer_args = [ 'in_footer' => true ];

        if ( ! empty( $this->main ) && isset( $this->main['async_scripts'] ) ) {
            $head_args['strategy'] = 'defer';
            $footer_args['strategy'] = 'defer';
        }

        $woo_is_enabled = ! empty( $this->tools['woo'] ) && function_exists('WC');
        $reqs = $woo_is_enabled ? array('fupi-helpers-js', 'fupi-helpers-footer-js', 'fupi-woo-js', 'fupi-pin-head-js') : array('fupi-helpers-js', 'fupi-helpers-footer-js', 'fupi-pin-head-js');

        /* ^ */ wp_enqueue_script( 'fupi-pin-head-js', FUPI_URL . 'public/modules/pin/fupi-pin.js', array( 'fupi-helpers-js' ), FUPI_VERSION, $head_args );
        /* _ */ if ( $woo_is_enabled ) wp_enqueue_script( 'fupi-pin-footer-js', FUPI_URL . 'public/modules/pin/fupi-pin-footer.js', $reqs, FUPI_VERSION, $footer_args );
    }
}