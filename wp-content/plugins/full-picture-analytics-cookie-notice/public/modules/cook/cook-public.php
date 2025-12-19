<?php

class Fupi_COOK_public {
    private $settings;

    private $tools;

    private $main;

    private $pp_url = false;

    private $pp_update_date = false;

    public function __construct() {
        $this->settings = get_option( 'fupi_cook' );
        if ( $this->settings === false ) {
            $this->settings = [];
        }
        $this->tools = get_option( 'fupi_tools' );
        $this->main = get_option( 'fupi_main' );
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
        add_action(
            'wp_head',
            array($this, 'fupi_add_iframe_translations'),
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
        // HTML MODS
        add_action( 'init', array($this, 'register_iframes_shortcodes') );
    }

    public function output_notice_html() {
        include_once FUPI_PATH . '/public/modules/cook/fupi-display-cookie-notice.php';
    }

    public function fupi_add_iframe_translations() {
        // !! DO NOT DELETE
        // we get options again because otherwise the translation would not kick in
        $cook_opts_2 = get_option( 'fupi_cook' );
        $iframe_caption_txt = ( empty( $cook_opts_2['iframe_caption_txt'] ) ? esc_attr__( 'This content is hosted by [[an external source]]. By loading it, you accept its {{privacy terms}}.', 'full-picture-analytics-cookie-notice' ) : esc_attr( $cook_opts_2['iframe_caption_txt'] ) );
        $iframe_btn_text = ( empty( $cook_opts_2['iframe_btn_text'] ) ? esc_attr__( 'Load content', 'full-picture-analytics-cookie-notice' ) : esc_attr( $cook_opts_2['iframe_btn_text'] ) );
        $iframe_texts = [
            'iframe_caption_txt' => $iframe_caption_txt,
            'iframe_btn_text'    => $iframe_btn_text,
        ];
        echo '<script id="fupi_iframe_texts">let fupi_iframe_texts = ' . json_encode( $iframe_texts ) . ';</script>';
    }

    private function get_pp_data() {
        if ( !empty( $this->settings['pp_id'] ) ) {
            $pp_id = (int) $this->settings['pp_id'];
            $pp_post = get_post( $pp_id );
            if ( !empty( $pp_post ) && isset( $pp_post->post_status ) && $pp_post->post_status === 'publish' ) {
                $this->pp_update_date = get_post_modified_time(
                    'U',
                    false,
                    $pp_id,
                    false
                );
                $this->pp_url = get_permalink( $pp_post );
            }
        }
        return null;
    }

    public function add_data_to_fp_object( $fp ) {
        // GET DATA FROM THE CUSTOMIZER
        $notice_data = get_option( 'fupi_cookie_notice' );
        // from customizer
        $this->get_pp_data();
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
        $mod_settings = $this->settings;
        // MODIFY VALUES
        $mod_settings['display_notice'] = !$hide_on_this_page;
        $mod_settings['toggle_selector'] = ( !empty( $mod_settings['toggle_selector'] ) ? esc_attr( $mod_settings['toggle_selector'] ) . ' .fupi_show_cookie_notice, .fp_show_cookie_notice' : '.fupi_show_cookie_notice, .fp_show_cookie_notice' );
        // REMOVE VALUES
        unset($mod_settings['enable_scripts_after']);
        // set later
        unset($mod_settings['mode']);
        // set later
        unset($mod_settings['optin']);
        unset($mod_settings['optin_countries']);
        unset($mod_settings['optout']);
        unset($mod_settings['optout_countries']);
        unset($mod_settings['inform']);
        unset($mod_settings['inform_countries']);
        unset($mod_settings['hide_on_pages']);
        unset($mod_settings['customize_notice_btn']);
        unset($mod_settings['cdb_key']);
        // the key is not removed after DB update so "unset" needs to stay
        unset($mod_settings['save_all_consents']);
        unset($mod_settings['iframe_auto_rules']);
        unset($mod_settings['iframe_manual_rules']);
        unset($mod_settings['control_other_iframes']);
        unset($mod_settings['scrblk_auto_rules']);
        unset($mod_settings['control_other_tools']);
        unset($mod_settings['scrblk_manual_rules']);
        // ADD VALUES
        $new_settings = [
            'enabled'              => true,
            'display_notice'       => !$hide_on_this_page,
            'priv_policy_update'   => $this->pp_update_date,
            'blur_page'            => !empty( $notice_data ) && !empty( $notice_data['blur_page'] ),
            'scroll_lock'          => !empty( $notice_data ) && !empty( $notice_data['scroll_lock'] ),
            'hidden'               => ( isset( $notice_data['hide'] ) ? $notice_data['hide'] : [] ),
            'shown'                => ( isset( $notice_data['show'] ) ? $notice_data['show'] : [] ),
            'preselected_switches' => ( isset( $notice_data['switches_on'] ) ? $notice_data['switches_on'] : [] ),
            'optin_switches'       => !empty( $notice_data['optin_switches'] ),
            'privacy_url'          => $this->pp_url,
        ];
        $notice_settings = array_merge( $new_settings, $mod_settings );
        // ADD TO FP OBJECT
        $fp['notice'] = $notice_settings;
        /*
                $fp['notice'] = [
        
                    // NEW
                    'enabled' 						=> true,
                    'display_notice'				=> ! $hide_on_this_page,
                    'priv_policy_update'			=> $privacy_policy_update_date,
        
                    // NEW - FROM CUSTOMIZER
                    'blur_page'						=> ! empty( $notice_data ) && ! empty( $notice_data['blur_page'] ),
                    'scroll_lock' 					=> ! empty( $notice_data ) && ! empty( $notice_data['scroll_lock'] ),
                    'hidden'						=> isset( $notice_data['hide'] ) ? $notice_data['hide'] : [],
                    'shown'							=> isset( $notice_data['show'] ) ? $notice_data['show'] : [],
                    'preselected_switches' 			=> isset( $notice_data['switches_on'] ) ? $notice_data['switches_on'] : [],
                    'optin_switches'				=> ! empty( $notice_data['optin_switches'] ),
        
                    // FROM SETTINGS
                    'gtag_no_cookie_mode'			=> isset( $this->settings['gtag_no_cookie_mode'] ),
                    'consent_access'                => isset( $this->settings['consent_access'] ),
                    'url_passthrough'				=> isset( $this->settings['url_passthrough'] ),
                    'ask_for_consent_again'			=> isset( $this->settings['ask_for_consent_again'] ),
                    'save_all_consents'				=> isset( $this->settings['save_all_consents'] ),
        
                    // FROM SETTINGS - MODIFIED
                    'toggle_selector'				=> ! empty( $this->settings['toggle_selector'] ) ? esc_attr( $this->settings['toggle_selector'] ) . ' .fupi_show_cookie_notice, .fp_show_cookie_notice' : '.fupi_show_cookie_notice, .fp_show_cookie_notice',
                ];*/
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
        // Load JS only when we are NOT in the bricks builder editor
        if ( !(function_exists( 'bricks_is_builder' ) && bricks_is_builder()) ) {
            /* ^ */
            wp_enqueue_script(
                'fupi-iframes-js',
                FUPI_URL . 'public/modules/cook/js/fupi-iframes.js',
                array('fupi-helpers-js'),
                FUPI_VERSION,
                [
                    'in_footer' => false,
                ]
            );
        }
    }

    // SHORTCODE FOR BLOCKING IFRAMES
    public function register_iframes_shortcodes() {
        add_shortcode( 'fp_block', array($this, 'fupi_block') );
        add_shortcode( 'fp_block_iframe', array($this, 'fupi_block') );
    }

    public function fupi_block( $atts, $content = null ) {
        $a = shortcode_atts( array(
            'stats'   => '',
            'market'  => '',
            'pers'    => '',
            'name'    => '',
            'image'   => false,
            'privacy' => '',
        ), $atts );
        if ( empty( $content ) ) {
            return '';
        } else {
            // get the data
            $stats = ( !empty( $a['stats'] ) && $a['stats'] == '1' ? '1' : '0' );
            $market = ( !empty( $a['market'] ) && $a['market'] == '1' ? '1' : '0' );
            $pers = ( !empty( $a['pers'] ) && $a['pers'] == '1' ? '1' : '0' );
            $name = ( !empty( $a['name'] ) ? ' data-name="' . esc_attr( $a['name'] ) . '"' : '' );
            $placeholder = ( !empty( $a['image'] ) ? ' data-placeholder="' . esc_url( $a['image'] ) . '"' : '' );
            $privacy = ( !empty( $a['privacy'] ) ? ' data-privacy="' . esc_url( $a['privacy'] ) . '"' : '' );
            // replace iframe
            $new_content = str_replace( '<iframe', '<div class="fupi_blocked_iframe" data-stats="' . $stats . '" data-market="' . $market . '" data-pers="' . $pers . '" ' . $placeholder . $name . $privacy . '><div class="fupi_iframe_data"', $content );
            $output = str_replace( '/iframe>', '/div></div>', $new_content ) . '<!--noptimize--><script data-no-optimize="1" nowprocket>FP.manageIframes();</script><!--/noptimize-->';
            return $output;
        }
        return $content;
        // this returns only iframes - shortcodes are always invisible ( it saves user time removing them if the iframe blocking module was disabled )
    }

}

// class END