<?php

class Fupi_COOK_public {
    private $settings;

    private $tools;

    public function __construct() {
        $this->settings = get_option( 'fupi_cook' );
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
        // output after body open
        if ( function_exists( 'wp_body_opens' ) ) {
            add_action( 'wp_body_opens', array($this, 'output_notice_html') );
        } else {
            // output before body close
            add_action( 'wp_footer', array($this, 'output_notice_html') );
        }
    }

    public function output_notice_html() {
        include_once FUPI_PATH . '/public/modules/cook/fupi-display-cookie-notice.php';
    }

    public function add_data_to_fp_object( $fp ) {
        // GET DATA FROM THE CUSTOMIZER
        $notice_data = get_option( 'fupi_cookie_notice' );
        $priv_policy_url = get_privacy_policy_url();
        // returns empty string if page is not published
        $priv_policy_id = get_option( 'wp_page_for_privacy_policy' );
        // gives ID event when the page is not published
        $privacy_policy_update_date = ( !empty( $priv_policy_url ) ? get_post_modified_time(
            'U',
            false,
            $priv_policy_id,
            false
        ) : null );
        // check if the banner should be hidden on the current page
        $hide_on_this_page = is_privacy_policy();
        if ( !$hide_on_this_page ) {
            if ( isset( $this->settings['hide_on_pages'] ) && is_array( $this->settings['hide_on_pages'] ) ) {
                $current_id = get_the_ID();
                if ( !empty( $current_id ) && in_array( $current_id, $this->settings['hide_on_pages'] ) ) {
                    $hide_on_this_page = true;
                }
            }
        }
        // BUILD BASIC OBJECT
        $fp['notice'] = [
            'enabled'               => true,
            'display_notice'        => !$hide_on_this_page,
            'gtag_no_cookie_mode'   => isset( $this->settings['gtag_no_cookie_mode'] ),
            'consent_access'        => isset( $this->settings['consent_access'] ),
            'url_passthrough'       => isset( $this->settings['url_passthrough'] ),
            'ask_for_consent_again' => isset( $this->settings['ask_for_consent_again'] ),
            'save_in_cdb'           => !empty( $this->settings['cdb_key'] ),
            'save_all_consents'     => isset( $this->settings['save_all_consents'] ),
            'priv_policy_update'    => $privacy_policy_update_date,
            'blur_page'             => !empty( $notice_data ) && !empty( $notice_data['blur_page'] ),
            'scroll_lock'           => !empty( $notice_data ) && !empty( $notice_data['scroll_lock'] ),
            'hidden'                => ( isset( $notice_data['hide'] ) ? $notice_data['hide'] : [] ),
            'shown'                 => ( isset( $notice_data['show'] ) ? $notice_data['show'] : [] ),
            'preselected_switches'  => ( isset( $notice_data['switches_on'] ) ? $notice_data['switches_on'] : [] ),
            'optin_switches'        => !empty( $notice_data['optin_switches'] ),
            'toggle_selector'       => ( !empty( $this->settings['toggle_selector'] ) ? esc_attr( $this->settings['toggle_selector'] ) . ' .fupi_show_cookie_notice, .fp_show_cookie_notice' : '.fupi_show_cookie_notice, .fp_show_cookie_notice' ),
        ];
        // UPDATE OBJECT
        $is_premium = false;
        if ( !$is_premium ) {
            $fp['notice']['mode'] = ( !empty( $this->settings['enable_scripts_after'] ) ? esc_attr( $this->settings['enable_scripts_after'] ) : 'optin' );
        }
        // default
        return $fp;
    }

    public function enqueue_scripts() {
        /* ^ */
        wp_enqueue_style(
            'fupi-consb',
            FUPI_URL . 'public/modules/cook/css/fupi-consb.min.css',
            array(),
            FUPI_VERSION,
            'all'
        );
        // also contains (little) CSS for the iframe manager
        if ( is_customize_preview() ) {
            /* _ */
            wp_enqueue_script(
                'fupi-customizer-consb-js',
                FUPI_URL . 'public/modules/cook/js/fupi-customizer-consb.js',
                array('fupi-helpers-js', 'jquery'),
                FUPI_VERSION,
                true
            );
        } else {
            /*if ( $this->track_current_user )*/
            /* _ */
            wp_enqueue_script(
                'fupi-consb-js',
                FUPI_URL . 'public/modules/cook/js/fupi-consb.js',
                array('fupi-helpers-js', 'fupi-helpers-footer-js'),
                FUPI_VERSION,
                true
            );
        }
    }

}
