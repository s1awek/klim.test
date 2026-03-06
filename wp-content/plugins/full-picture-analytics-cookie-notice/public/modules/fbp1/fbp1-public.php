<?php

class Fupi_FBP1_public {
    private $settings;

    private $tools;

    private $main;

    private $ver;

    public function __construct() {
        $this->settings = get_option( 'fupi_fbp1' );
        if ( !empty( $this->settings ) && !empty( $this->settings['pixel_id'] ) ) {
            $this->tools = get_option( 'fupi_tools' );
            $this->main = get_option( 'fupi_main' );
            $this->ver = get_option( 'fupi_versions' );
            $this->add_actions_and_filters();
        }
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
        $fbp_data = $this->settings;
        // ! we must make a copy to safely modify
        $fbp_data['server_side'] = false;
        $fbp_data['server_side_2'] = false;
        if ( !empty( $fbp_data['capi_token'] ) ) {
            unset($fbp_data['capi_token']);
        }
        if ( !empty( $fbp_data['capi_token_2'] ) ) {
            unset($fbp_data['capi_token_2']);
        }
        if ( !empty( $fbp_data['test_code'] ) ) {
            unset($fbp_data['test_code']);
        }
        if ( !empty( $fbp_data['test_code_2'] ) ) {
            unset($fbp_data['test_code_2']);
        }
        $fp['fbp'] = $fbp_data;
        $fp['tools'][] = 'fbp';
        return $fp;
    }

    public function enqueue_scripts() {
        $reqs = ( !empty( $this->tools['woo'] ) && function_exists( 'WC' ) ? array(
            'fupi-helpers-js',
            'fupi-helpers-footer-js',
            'fupi-woo-js',
            'fupi-fbp-head-js'
        ) : array('fupi-helpers-js', 'fupi-helpers-footer-js', 'fupi-fbp-head-js') );
        /* ^ */
        wp_enqueue_script(
            'fupi-fbp-head-js',
            FUPI_URL . 'public/modules/fbp1/fupi-fbp.js',
            array('fupi-helpers-js'),
            FUPI_VERSION,
            [
                'in_footer' => false,
                'strategy'  => 'async',
            ]
        );
        /* _ */
        wp_enqueue_script(
            'fupi-fbp-footer-js',
            FUPI_URL . 'public/modules/fbp1/fupi-fbp-footer.js',
            $reqs,
            FUPI_VERSION,
            [
                'in_footer' => true,
                'strategy'  => 'async',
            ]
        );
    }

}
