<?php

class Fupi_SIMPL_public {
    private $settings;

    private $main;

    private $ver;

    public function __construct() {
        $this->settings = get_option( 'fupi_simpl' );
        $this->main = get_option( 'fupi_main' );
        $this->ver = get_option( 'fupi_versions' );
        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters() {
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );
        add_filter(
            'fupi_modify_fp_object',
            array($this, 'add_data_to_fp_object'),
            10,
            1
        );
    }

    public function add_data_to_fp_object( $fp ) {
        $fp['simpl'] = ( !empty( $this->settings ) ? $this->settings : array() );
        // plausible does not require any setup to run
        $fp['tools'][] = 'simpl';
        return $fp;
    }

    public function enqueue_scripts() {
        /* ^ */
        wp_enqueue_script(
            'fupi-simpl-head-js',
            FUPI_URL . 'public/modules/simpl/fupi-simpl.js',
            array('fupi-helpers-js'),
            FUPI_VERSION,
            [
                'in_footer' => false,
                'strategy'  => 'async',
            ]
        );
    }

}
