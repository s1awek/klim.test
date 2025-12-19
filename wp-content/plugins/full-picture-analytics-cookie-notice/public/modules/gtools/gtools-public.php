<?php

class Fupi_GTOOLS_public {
    private $tools;

    private $main;

    private $ga41_enabled;

    private $ga42_enabled;

    private $gads_enabled;

    private $ga41_settings;

    private $ga42_settings;

    private $gads_settings;

    private $gtag_settings;

    public function __construct() {
        $this->tools = get_option( 'fupi_tools' );
        $this->main = get_option( 'fupi_main' );
        $this->ga41_enabled = isset( $this->tools['ga41'] );
        $this->ga42_enabled = isset( $this->tools['ga42'] );
        $this->gads_enabled = isset( $this->tools['gads'] );
        if ( $this->ga41_enabled ) {
            $this->ga41_settings = get_option( 'fupi_ga41' );
        }
        if ( $this->ga42_enabled ) {
            $this->ga42_settings = get_option( 'fupi_ga42' );
        }
        if ( $this->gads_enabled ) {
            $this->gads_settings = get_option( 'fupi_gads' );
        }
        if ( $this->ga41_enabled || $this->gads_enabled ) {
            $this->gtag_settings = get_option( 'fupi_gtag' );
        }
        if ( empty( $this->ga41_settings ) && empty( $this->gads_settings ) ) {
            return;
        }
        $this->tools = get_option( 'fupi_tools' );
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
        if ( $this->ga41_enabled ) {
            add_filter(
                'fupi_order_server_tracking',
                array($this, 'fupi_ga41_order_server_tracking__premium_only'),
                10,
                2
            );
        }
        if ( !empty( $this->gtag_settings['custom_gateway'] ) ) {
            add_action( 'wp_head', array($this, 'add_custom_gtag_gateway_async_script'), -1 );
        }
    }

    public function add_custom_gtag_gateway_async_script() {
        echo '<script async="" src="' . trailingslashit( esc_attr( $this->gtag_settings['custom_gateway'] ) ) . '"></script>';
    }

    public function enqueue_scripts() {
        $is_woo_enabled = $reqs = !empty( $this->tools['woo'] ) && function_exists( 'WC' );
        $reqs = ( $is_woo_enabled ? array('fupi-helpers-js', 'fupi-woo-js') : array('fupi-helpers-js') );
        $head_args = [
            'in_footer' => false,
        ];
        $footer_args = [
            'in_footer' => true,
        ];
        if ( !empty( $this->main ) && isset( $this->main['async_scripts'] ) ) {
            $head_args['strategy'] = 'defer';
            $footer_args['strategy'] = 'defer';
        }
        // GTG
        if ( $this->ga41_enabled || $this->gads_enabled ) {
            $footer_req_gtg = ( $is_woo_enabled ? array('fupi-helpers-js', 'fupi-helpers-footer-js', 'fupi-woo-js') : array('fupi-helpers-js', 'fupi-helpers-footer-js') );
            /* ^ */
            wp_enqueue_script(
                'fupi-gtg-head-js',
                FUPI_URL . 'public/modules/gtools/fupi-gtg.js',
                array('fupi-helpers-js'),
                FUPI_VERSION,
                $head_args
            );
            /* _ */
            wp_enqueue_script(
                'fupi-gtg-footer-js',
                FUPI_URL . 'public/modules/gtools/fupi-gtg-footer.js',
                $footer_req_gtg,
                FUPI_VERSION,
                $footer_args
            );
        }
    }

    public function add_data_to_fp_object( $fp ) {
        // GTAG
        if ( !empty( $this->gtag_settings ) ) {
            $fp['gtag'] = $this->gtag_settings;
        }
        // GA4
        if ( !empty( $this->ga41_settings ) ) {
            $fp['ga41'] = $this->ga41_settings;
            $fp['tools'][] = 'ga41';
            $fp['ga41']['server_side'] = false;
            if ( !empty( $fp['ga41']['mp_secret_key'] ) ) {
                unset($fp['ga41']['mp_secret_key']);
            }
        }
        // GADS
        if ( $this->gads_enabled && !empty( $this->gads_settings ) ) {
            $fp['gads'] = $this->gads_settings;
            $fp['tools'][] = 'gads';
        }
        return $fp;
    }

}
