<?php

class Fupi_COOK_admin {

    private $settings;
    private $tools;

    public function __construct(){
        $this->settings = get_option('fupi_cook');
        $this->tools = get_option('fupi_tools');
        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters(){
        add_action( 'fupi_register_setting_cook', array( $this, 'register_module_settings' ) );
        add_filter( 'fupi_cook_add_fields_settings', array( $this, 'add_fields_settings' ), 10, 1 );
		add_filter( 'fupi_cook_get_faq_data', array( $this, 'get_faq_data' ), 10, 1 );
        add_filter( 'fupi_cook_get_page_descr', array( $this, 'get_page_descr' ), 10, 2 );

        // CDB - Privacy page updates listener
		add_action( 'publish_page', array( $this, 'fupi_listen_to_pp_page_updates' ), 10, 2 );

        // CUSTOMIZER
		
		// Enable customizer functions if we are NOT using OceanWP theme
		$theme = wp_get_theme();

		if ( $theme->get('Name') != 'OceanWP' ) {
			add_action( 'customize_register', array( $this, 'fupi_customize_register' ) );
			add_action( 'customize_save_after', array( $this, 'fupi_customize_save_after' ) );
			add_action( 'customize_preview_init', array( $this, 'fupi_customizer_preview_scripts' ) );
			add_action( 'customize_controls_enqueue_scripts', array( $this, 'fupi_enqueue_customizer_css_js') );
		}
    }

    // for CDB
	public function fupi_listen_to_pp_page_updates( $post_id, $post ){

		$pp_id = get_option( 'wp_page_for_privacy_policy' );
		
		if ( $post_id != $pp_id ) return;
			
		$page_status = get_post_status( $post_id );

		if ( $page_status == 'publish' ) {
			include_once 'cook-register-cdb.php'; // loads consent checker to send the config data to CDB
			$cdb = new Fupi_send_to_CDB();
			$clean_data = $cdb->send_privacy_policy();

		}
	}

    // CUSTOMIZER

	// Register customizer settings
	public function fupi_customize_register($wp_customize) {	
        if ( ! function_exists('fupi_disable_customizer') ) {
            include_once 'customizer/fupi-customizer-settings.php';
        }
	}

	// Sanitize customizer settings
	public function fupi_customizer_sanitize($val, $setting) {
		if ( ! function_exists('fupi_disable_customizer') ) {
			$sanitized = ( include ('customizer/fupi-customizer-sanitize.php') ); // a workaround to get the value returned from the included file
			return $sanitized;
		}
	}

	// Send customizer settings to CDB
	public function fupi_customize_save_after(){
		if ( ! empty ( $this->tools['cook'] ) && ! empty( $this->settings['cdb_key'] ) && ! empty ( get_privacy_policy_url() ) ) {
			include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
			new Fupi_compliance_status_checker( 'cdb', $this->settings, false );
		}
	}

	// Enqueue customizer preview scripts
	public function fupi_customizer_preview_scripts() {
		if ( ! function_exists('fupi_disable_customizer') ) {
			wp_enqueue_script( 'fupi-customizer-preview', plugin_dir_url( __FILE__ ) . 'customizer/js/fupi-customizer-preview.js', array( 'customize-preview', 'jquery' ), FUPI_VERSION, true );
		}
	}

	// Enqueue customizer controls scripts and styles
	public function fupi_enqueue_customizer_css_js(){	
        if ( ! function_exists('fupi_disable_customizer') ) {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker');
            wp_enqueue_script( 'fupi-customizer-controls', plugin_dir_url( __FILE__ ) . 'customizer/js/fupi-customizer-controls.js', array( 'jquery', 'customize-controls' ), FUPI_VERSION, true );
            wp_enqueue_style( 'fupi-customizer-css', plugin_dir_url( __FILE__ ) . 'customizer/css/fupi-customizer.css', array(), FUPI_VERSION, 'all' );
        }
	}

    // ADMIN PAGE

    public function add_fields_settings( $sections ){
        include_once 'cook-fields.php';
        return $sections;
    }

    public function register_module_settings(){
        register_setting( 'fupi_cook', 'fupi_cook', array( 'sanitize_callback' => array( $this, 'sanitize_fields' ) ) );
    }

    public function sanitize_fields( $input ){

        include 'cook-sanitize.php';

        // loads consent checker to send the config data to CDB
        // all checks are done in the class
        include_once 'cook-register-cdb.php'; 
        $cdb = new Fupi_send_to_CDB();
		$clean_data = $cdb->register_new_site( $clean_data );
		
		include FUPI_PATH . '/admin/common/fupi-clear-cache.php';
		return $clean_data; 
    }

	public function get_faq_data( $empty_arr ){
        include_once 'cook-faq.php';
        return [ 
            'q' => $questions, 
            'a' => $answers 
        ];
    }

    public function get_page_descr( $section_id, $no_woo_descr_text ){
        include 'cook-descr.php';
        return $ret_text;
    }

}