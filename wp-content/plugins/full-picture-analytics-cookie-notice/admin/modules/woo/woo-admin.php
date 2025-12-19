<?php

class Fupi_WOO_admin {
    private $settings;

    private $main;

    private $cook;

    private $tools;

    private $proofrec;

    private $user_cap = 'manage_options';

    public function __construct() {
        $this->settings = get_option( 'fupi_woo' );
        $this->main = get_option( 'fupi_main' );
        $this->cook = get_option( 'fupi_cook' );
        $this->tools = get_option( 'fupi_tools' );
        $this->proofrec = get_option( 'fupi_proofrec' );
        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters() {
        add_action( 'fupi_register_setting_woo', array($this, 'register_module_settings') );
        add_filter(
            'fupi_woo_add_fields_settings',
            array($this, 'add_fields_settings'),
            10,
            1
        );
        add_filter(
            'fupi_woo_get_faq_data',
            array($this, 'get_faq_data'),
            10,
            1
        );
        add_filter(
            'fupi_woo_get_page_descr',
            array($this, 'get_page_descr'),
            10,
            2
        );
    }

    private function pp_ok() {
        if ( !empty( $this->cook['pp_id'] ) ) {
            $pp_id = (int) $this->cook['pp_id'];
            return get_post_status( $pp_id ) == 'publish';
        }
        return false;
    }

    public function add_fields_settings( $sections ) {
        include_once 'woo-fields.php';
        return $sections;
    }

    public function register_module_settings() {
        register_setting( 'fupi_woo', 'fupi_woo', array(
            'sanitize_callback' => array($this, 'sanitize_fields'),
        ) );
    }

    public function sanitize_fields( $input ) {
        include 'woo-sanitize.php';
        if ( apply_filters( 'fupi_updating_many_options', false ) ) {
            return $clean_data;
        }
        if ( !empty( $this->tools['cook'] ) && !empty( $this->tools['proofrec'] ) && $this->pp_ok() ) {
            include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
            $gdpr_checker = new Fupi_compliance_status_checker('woo', $clean_data);
            $gdpr_checker->send_and_return_status();
        }
        include FUPI_PATH . '/admin/common/fupi-clear-cache.php';
        return $clean_data;
    }

    public function get_faq_data( $empty_arr ) {
        include_once 'woo-faq.php';
        return [
            'q' => $questions,
            'a' => $answers,
        ];
    }

    public function get_page_descr( $section_id, $no_woo_descr_text ) {
        include 'woo-descr.php';
        return $ret_text;
    }

    private function get_order_creation_method( $order, $created_via ) {
        $methods = array(
            'checkout'     => esc_html__( 'checkout page', 'full-picture-analytics-cookie-notice' ),
            'store-api'    => esc_html__( 'store-api (block checkout or integration with a different tool)', 'full-picture-analytics-cookie-notice' ),
            'admin'        => esc_html__( 'WP admin panel', 'full-picture-analytics-cookie-notice' ),
            'rest-api'     => esc_html__( 'REST API integration', 'full-picture-analytics-cookie-notice' ),
            'csv_import'   => esc_html__( 'CSV import', 'full-picture-analytics-cookie-notice' ),
            'subscription' => esc_html__( 'subscription renewal', 'full-picture-analytics-cookie-notice' ),
            'pos'          => esc_html__( 'Point of Sale system', 'full-picture-analytics-cookie-notice' ),
        );
        return ( isset( $methods[$created_via] ) ? $methods[$created_via] : null );
    }

}
