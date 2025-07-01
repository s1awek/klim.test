<?php

class Fupi_GOTM_public {

    private $settings;
    private $tools;
    private $main;

    public function __construct(){

        $this->settings = get_option('fupi_gtm');
        
        if ( ! empty ( $this->settings ) ) {
            $this->tools = get_option('fupi_tools');
            $this->main = get_option('fupi_main');
            $this->add_actions_and_filters();
        }
    }

    private function add_actions_and_filters(){
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'fupi_modify_fp_object', array($this, 'add_data_to_fp_object'), 10, 1 );
        add_action( 'wp_body_open', array( $this, 'add_gtm_noscript_fallback') );
    }

    public function add_data_to_fp_object( $fp ){
        $fp['gtm'] = $this->settings;
        $fp['tools'][] = 'gtm';
        return $fp;
    }

    public function enqueue_scripts(){

        $head_args = [ 'in_footer' => false ];
        $footer_args = [ 'in_footer' => true ];

        if ( ! empty( $this->main ) && isset( $this->main['async_scripts'] ) ) {
            $head_args['strategy'] = 'defer';
            $footer_args['strategy'] = 'defer';
        }

        $reqs = ! empty( $this->tools['woo'] ) && function_exists('WC') ? array('fupi-helpers-js', 'fupi-helpers-footer-js', 'fupi-woo-js', 'fupi-gotm-head-js') : array('fupi-helpers-js', 'fupi-helpers-footer-js', 'fupi-gotm-head-js');

        /* ^ */ wp_enqueue_script( 'fupi-gotm-head-js', FUPI_URL . 'public/modules/gotm/fupi-gotm.js', array( 'fupi-helpers-js' ), FUPI_VERSION, $head_args );
        /* _ */ wp_enqueue_script( 'fupi-gotm-footer-js', FUPI_URL . 'public/modules/gotm/fupi-gotm-footer.js', $reqs, FUPI_VERSION, $footer_args );
    }

    public function add_gtm_noscript_fallback(){
        if ( ! empty( $this->settings ) && ! empty( $this->settings['id'] ) ) {
            echo '<!-- START Google Tag Manager (noscript) -->
                <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . esc_attr( $this->settings['id'] ) . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <!-- END Google Tag Manager (noscript) -->';
        }
    }
}