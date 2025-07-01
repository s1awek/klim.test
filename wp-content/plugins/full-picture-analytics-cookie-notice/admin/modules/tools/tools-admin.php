<?php

class Fupi_TOOLS_admin {

    private $settings;
    private $cook;
    private $main;
    private $tools;

    public function __construct(){
        $this->settings = get_option('fupi_tools');
        $this->cook = get_option('fupi_cook');	
        $this->main = get_option('fupi_main');
        $this->tools = get_option('fupi_tools');

        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters(){
        add_action( 'fupi_register_setting_tools', array( $this, 'register_module_settings' ) );
        add_filter( 'fupi_tools_add_fields_settings', array( $this, 'add_fields_settings' ), 10, 1 );
        add_filter( 'fupi_tools_get_page_descr', array( $this, 'get_page_descr' ), 10, 2 );
    }

    public function register_module_settings(){
        register_setting( 'fupi_tools', 'fupi_tools', array( 'sanitize_callback' => array( $this, 'sanitize_fields' ) ) );
    }

    public function add_fields_settings( $sections ){
        include_once 'tools-fields.php';
        return $sections;
    }

    public function get_page_descr( $section_id, $no_woo_descr_text ){
        include 'tools-descr.php';
        return $ret_text;
    }

    // SANITIZATION

	public function sanitize_fields( $input ) {
		
        include 'tools-sanitize.php';

        // UPDATE CDB

        if ( ! empty ( $clean_data['cook'] ) && ! empty( $this->cook['cdb_key'] ) && ! empty ( get_privacy_policy_url() ) ) {
            include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
            new Fupi_compliance_status_checker( 'cdb', $this->cook, false, $clean_data );
        }		

        // GENERATE FILES

        $generate_head = false;
        $generate_cscr = false;

		// Generate HEAD js
		
		// if main file generation is enabled
		if ( ! empty( $this->main['save_settings_file' ] ) ) {

			// regenerate if the consent banner module was enabled or disabled
			$cook_was_enabled = isset( $this->tools['cook'] );
			$cook_is_enabled = isset( $clean_data['cook'] );

			if ( $cook_was_enabled !== $cook_is_enabled ) $generate_head = true;

			// regenerate if the geolocation module was enabled or disabled
			$geo_was_enabled = isset( $this->tools['geo'] );
			$geo_is_enabled = isset( $clean_data['geo'] );

			if ( $geo_was_enabled !== $geo_is_enabled ) $generate_head = true;
		}

		// Generate CSCR files
        
        // if CSCR module is enabled and file generation is enabled
        if ( ! empty( $this->main['save_cscr_file'] ) && ! empty ( $clean_data['cscr'] ) ) {
            
            // If CSCR module has not been enabled before
            if ( empty ( $this->tools['cscr'] ) ) $generate_cscr = true;

            // If the consent banner module was enabled or disabled
            if ( isset( $clean_data['cook'] ) !== isset( $this->tools['cook'] ) ) $generate_cscr = true;

            // We do not check GEO because the function that loads CSCR scripts makes all the geo checks            
        }

		// if the Custom Scripts module and file generation are enabled

        if ( $generate_head || $generate_cscr ) {

            include_once FUPI_PATH . '/admin/common/generate-files.php';
            $generator = new Fupi_Generate_Files();

            if ( $generate_head ) $generator->make_head_js_file( 'tools', $clean_data );
            if ( $generate_cscr ) $generator->make_cscr_js_files( false );
        }

		include FUPI_PATH . '/admin/common/fupi-clear-cache.php';
		return $clean_data;
	}
}