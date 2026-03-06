<?php

class Fupi_MAIN_public {
    private $settings;

    private $tools;

    private $ver;

    public function __construct() {
        $this->settings = get_option( 'fupi_main' );
        $this->tools = get_option( 'fupi_tools' );
        $this->ver = get_option( 'fupi_versions' );
        if ( !empty( $this->ver['debug'] ) ) {
            add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );
            add_action( 'wp_footer', array($this, 'fupi_add_setup_console_html') );
        }
        add_action( 'init', array($this, 'add_fpinfo_shortcode') );
        if ( $this->settings === false ) {
            return;
        }
        add_action( 'wp_head', array($this, 'fupi_add_meta_tags'), -5 );
    }

    public function add_fpinfo_shortcode() {
        add_shortcode( 'fp_info', array($this, 'fupi_info') );
    }

    public function fupi_info( $atts, $content = null ) {
        $a = shortcode_atts( array(
            'display' => 'list',
        ), $atts );
        // include_once FUPI_PATH . '/includes/fupi_modules_data.php';
        include_once 'fpinfo_generator.php';
        $fupi_policy_generator = new Fupi_fpinfo_generator($a['display']);
        return $fupi_policy_generator->output();
    }

    public function fupi_add_meta_tags() {
        if ( !empty( $this->settings['meta_tags'] ) ) {
            foreach ( $this->settings['meta_tags'] as $tag ) {
                echo html_entity_decode( $tag['tag'], ENT_QUOTES, 'UTF-8' ) . "\n";
            }
        }
    }

    public function fupi_add_setup_console_html() {
        // Check if we are not in the bricks editor
        $can_output_btn = !(function_exists( 'bricks_is_builder' ) && bricks_is_builder());
        if ( current_user_can( 'manage_options' ) && !is_customize_preview() && $can_output_btn ) {
            // START
            $output = '<div id="fupi_console_wrap" >';
            // STICKY SIDE BUTTON
            $output .= '<button type="button" id="fupi_console_fixed_btn" class="fupi_console_toggle_btn"><img src="' . FUPI_URL . 'admin/assets/img/fp-ico.svg"><span class="fupi_srt"> ' . esc_html__( 'Tracking tester', 'full-picture-analytics-cookie-notice' ) . '</span></button>

                <div id="fupi_console" class="fupi_hidden">';
            // CLOSE BUTTON
            $output .= '<button type="button" id="fupi_console_close_btn" class="fupi_console_toggle_btn"><span class="dashicons dashicons-no-alt"></span><span class="fupi_srt">' . esc_html__( 'Close Panel', 'full-picture-analytics-cookie-notice' ) . '</span></button>';
            // PANEL - WHEN TESTING IS DISABLED
            $output .= '<div id="fupi_console_step1">
                        <p style="margin-top: 0 !important; font-size: 16px;"><strong>' . esc_html__( 'Test your tracking setup', 'full-picture-analytics-cookie-notice' ) . '</strong></p>
                        <p>' . esc_html__( 'Make sure to keep your ad blocker disabled during tests.', 'full-picture-analytics-cookie-notice' ) . '</p>
                        <p>' . esc_html__( 'Testing information will be displayed in the browser console after you start testing.', 'full-picture-analytics-cookie-notice' ) . '</p>
                    </div>';
            // PANEL - WHEN TESTING IS ENABLED
            $output .= '<div id="fupi_console_step2">
                        <p style="margin-top: 0 !important; font-size: 16px;"><strong>' . esc_html__( 'Testing mode is active', 'full-picture-analytics-cookie-notice' ) . '</strong></p>
                        <p>' . esc_html__( 'All tracking tools behave as if you were a normal visitor.', 'full-picture-analytics-cookie-notice' ) . '</p>
                        <p>' . esc_html__( 'Extra information related to WP FP is now output to your browser console.', 'full-picture-analytics-cookie-notice' ) . '</p>
                    </div>';
            // BUTTONS
            $output .= '<div id="fupi_console_buttons">

                        <a class="fupi_secondary_button" href="https://wpfullpicture.com/support/documentation/debug-mode-features/" target="_blank">' . esc_html__( 'Learn how to test', 'full-picture-analytics-cookie-notice' ) . ' <span class="dashicons dashicons-external"></span></a>

                        <button type="button" id="fupi_start_test" class="fupi_test_reset_btn fupi_primary_button">' . esc_html__( 'Start testing', 'full-picture-analytics-cookie-notice' ) . '</button>';
            // BUTTONS SHOW ONLY WHEN CONSENT BANNER IS ENABLED
            if ( !empty( $this->tools['cook'] ) ) {
                $output .= '<button type="button" id="fupi_simulate_first_visit" class="fupi_test_reset_btn fupi_primary_button">' . esc_html__( 'Reset consents', 'full-picture-analytics-cookie-notice' ) . '</button>
                            
                            <button type="button" id="fupi_show_banner" class="fupi_primary_button">' . esc_html__( 'Show consent banner', 'full-picture-analytics-cookie-notice' ) . '</button>
                            <span id="fupi_hidden_show_banner_btn" class="fp_show_cookie_notice fupi_hidden"></span>';
                // this span is clicked by the script
            }
            $output .= '<button type="button" id="fupi_end_test" class="fupi_primary_button">' . esc_html__( 'Finish testing', 'full-picture-analytics-cookie-notice' ) . '</button>';
            // END
            $output .= '
                    </div>
                </div>
            </div>';
            echo $output;
        }
    }

    public function enqueue_scripts() {
        // Check if we are not in the bricks editor
        $can_output_btn = !(function_exists( 'bricks_is_builder' ) && bricks_is_builder());
        if ( current_user_can( 'manage_options' ) && !is_customize_preview() && $can_output_btn ) {
            /* ^ */
            wp_enqueue_style(
                'fupi-setup-console',
                FUPI_URL . 'public/modules/main/css/fupi-setup-console.css',
                array(),
                FUPI_VERSION,
                'all'
            );
            // also contains (little) CSS for the iframe manager
            /* _ */
            wp_enqueue_script(
                'fupi-setup-console',
                FUPI_URL . 'public/modules/main/js/fupi-setup-console.js',
                array('fupi-helpers-js'),
                FUPI_VERSION,
                [
                    'in_footer' => true,
                    'strategy'  => 'async',
                ]
            );
        }
    }

}
