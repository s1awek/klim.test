<?php

class Fupi_MADS_public {

    private $settings;
    private $tools;
    private $main;

    public function __construct(){

        $this->settings = get_option('fupi_mads');
        
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
        $fp['mads'] = $this->settings;
        $fp['tools'][] = 'mads';
        return $fp;
    }

    public function enqueue_scripts(){

        $reqs = ! empty( $this->tools['woo'] ) && function_exists('WC') ? array('fupi-helpers-js', 'fupi-helpers-footer-js', 'fupi-woo-js', 'fupi-mads-head-js') : array('fupi-helpers-js', 'fupi-helpers-footer-js', 'fupi-mads-head-js');

        /* ^ */ wp_enqueue_script( 'fupi-mads-head-js', FUPI_URL . 'public/modules/mads/fupi-mads.js', array( 'fupi-helpers-js' ), FUPI_VERSION, [ 'in_footer' => false, 'strategy' => 'async' ] );
        /* _ */ wp_enqueue_script( 'fupi-mads-footer-js', FUPI_URL . 'public/modules/mads/fupi-mads-footer.js', $reqs, FUPI_VERSION, [ 'in_footer' => true, 'strategy' => 'async' ] );
    }
}