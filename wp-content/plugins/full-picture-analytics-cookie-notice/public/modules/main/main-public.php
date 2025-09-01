<?php

class Fupi_MAIN_public {

    private $settings;
    private $tools;

    public function __construct(){

        $this->settings = get_option('fupi_main');
        if ( $this->settings === false ) return;

        $this->tools = get_option('fupi_tools');

        add_action( 'wp_head', array( $this, 'fupi_add_meta_tags' ), -5 );
        add_action( 'init', array( $this, 'add_fpinfo_shortcode' ) ); // check if it is safe to move it before the "return" above

        if ( ! empty( $this->settings['debug'] ) ) {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            add_action( 'wp_footer', array( $this, 'fupi_add_setup_console_html' ) );
        }
    }

    public function add_fpinfo_shortcode(){
        add_shortcode( 'fp_info', array( $this, 'fupi_info' ) );
    }

    public function fupi_info ( $atts, $content = null ) {

        $a = shortcode_atts( array(
            'display' => 'list',
        ), $atts );
        
        // include_once FUPI_PATH . '/includes/fupi_modules_data.php';
        include_once 'fpinfo_generator.php';
        
        $fupi_policy_generator = new Fupi_fpinfo_generator( $a['display'] );
        
        return $fupi_policy_generator->output();
	}

    public function fupi_add_meta_tags(){
        if ( ! empty( $this->settings['meta_tags'] ) ){
            foreach( $this->settings['meta_tags'] as $tag ){
                echo html_entity_decode( $tag['tag'], ENT_QUOTES, 'UTF-8') . "\n";
            }
        }
    }

    public function fupi_add_setup_console_html(){
        
        if ( current_user_can( 'manage_options' ) && ! is_customize_preview() ) {

            // START
            $output = '<div id="fupi_console_wrap" >';

                // STICKY SIDE BUTTON
                $output .= '<button type="button" id="fupi_console_fixed_btn" class="fupi_console_toggle_btn"><img src="' . FUPI_URL . 'admin/assets/img/fp-ico.svg"><span class="fupi_srt"> ' . esc_html__( 'Tracking tester', 'full-picture-analytics-cookie-notice' ) . '</span></button>

                <div id="fupi_console" class="fupi_hidden">';

                    // CLOSE BUTTON

                    $output .= '<button type="button" id="fupi_console_close_btn" class="fupi_console_toggle_btn"><span class="dashicons dashicons-no-alt"></span><span class="fupi_srt">' . esc_html__( 'Close Panel', 'full-picture-analytics-cookie-notice' ) . '</span></button>';

                    // HEADLINE AND INTRO

                    $output .= '<p style="margin-top: 0 !important; font-size: 16px;"><strong>' . esc_html__( 'Test your tracking tools', 'full-picture-analytics-cookie-notice' ) . '</strong> <button type="button" id="fupi_open_intro_btn" class="fupi_i_btn">i</button></p>

                    <div id="fupi_console_intro" class="fupi_hidden">
                        <p>' . sprintf( esc_html__( 'This panel is a part of WP Full Picture plugin. Only administrators can see it and only when the %1$ssetup mode%2$s is active (%3$sdisable it here%4$s)', 'full-picture-analytics-cookie-notice' ), '<strong>', '</strong>', '<a href="' . get_admin_url() . 'admin.php?page=full_picture_main">', '</a>' ) . '</p>
                    </div>';

                    // PANEL 1 - WHEN TESTING IS DISABLED

                    $output .= '<div id="fupi_console_step1">
                        <p>' . esc_html__( 'This panel lets you test configuration of WP Full Picture and its modules.', 'full-picture-analytics-cookie-notice' ) . ' <a href="https://wpfullpicture.com/support/documentation/debug-mode-features/">' . esc_html__( 'Learn how to use it', 'full-picture-analytics-cookie-notice' ) . ' <span class="dashicons dashicons-external"></span></a></p>
                    </div>';

                    // PANEL 2 - WHEN TESTING IS ENABLED

                    $output .= '<div id="fupi_console_step2">
                        <p>' . esc_html__( 'Testing is enabled and you are tracked like other visitors. It will deactivate automatically after 6 hours.', 'full-picture-analytics-cookie-notice' ) . '</p>
                        <p>' . esc_html__( 'Open browser console and start browsing your website. You will see there useful information about your tracking tools. Make sure to keep your ad blocker disabled during tests.', 'full-picture-analytics-cookie-notice' ) . ' <a href="https://wpfullpicture.com/support/documentation/debug-mode-features/">' . esc_html__( 'Learn more', 'full-picture-analytics-cookie-notice' ) . ' <span class="dashicons dashicons-external"></span></a></p>
                    </div>';

                    // BUTTONS

                    $output .= '<div id="fupi_console_buttons">
                        <button type="button" id="fupi_start_test" class="fupi_test_reset_btn fupi_primary_button">' . esc_html__( 'Start testing', 'full-picture-analytics-cookie-notice' ) . '</button>';
                
                        // BUTTONS SHOW ONLY WHEN CONSENT BANNER IS ENABLED

                        if ( ! empty( $this->tools['cook'] ) ) {
                            $output .= '<button type="button" id="fupi_simulate_first_visit" class="fupi_test_reset_btn fupi_secondary_button">' . esc_html__( 'Reset consents', 'full-picture-analytics-cookie-notice' ) . '</button>
                            
                            <button type="button" id="fupi_show_banner" class="fupi_secondary_button">' . esc_html__( 'Show consent banner', 'full-picture-analytics-cookie-notice' ) . '</button>
                            <span id="fupi_hidden_show_banner_btn" class="fp_show_cookie_notice fupi_hidden"></span>'; // this span is clicked by the script
                        }

                        $output .= '<button type="button" id="fupi_end_test" class="fupi_secondary_button">' . esc_html__( 'Finish testing', 'full-picture-analytics-cookie-notice' ) . '</button>';

                        // END

                        $output .= '
                    </div>
                </div>
            </div>';

            echo $output;
        }
    }

    public function enqueue_scripts(){
        if ( current_user_can( 'manage_options' ) && ! is_customize_preview() ) {
            /* ^ */ wp_enqueue_style( 'fupi-setup-console', FUPI_URL . 'public/modules/main/css/fupi-setup-console.css', array(), FUPI_VERSION, 'all' ); // also contains (little) CSS for the iframe manager	
            /* _ */ wp_enqueue_script( 'fupi-setup-console', FUPI_URL . 'public/modules/main/js/fupi-setup-console.js', array( 'fupi-helpers-js' ), FUPI_VERSION, true );
        }
    }
}