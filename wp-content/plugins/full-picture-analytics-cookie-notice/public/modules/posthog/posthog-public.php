<?php

class Fupi_POSTHOG_public {

    private $settings;
    private $main;

    public function __construct(){

        $this->settings = get_option('fupi_posthog');
        $this->main = get_option('fupi_main');

        if ( ! empty ( $this->settings ) ) {
            $this->add_actions_and_filters();
        }
    }

    private function add_actions_and_filters(){
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'fupi_modify_fp_object', array($this, 'add_data_to_fp_object'), 10, 1 );
    }

    public function add_data_to_fp_object( $fp ){
        $fp['posthog'] = $this->settings;
        $fp['tools'][] = 'posthog';
        return $fp;
    }

    public function enqueue_scripts(){
        /* ^ */ wp_enqueue_script( 'fupi-posthog-head-js', FUPI_URL . 'public/modules/posthog/fupi-posthog.js', array( 'fupi-helpers-js' ), FUPI_VERSION, [ 'in_footer' => false, 'strategy' => 'async' ] );
    }
}