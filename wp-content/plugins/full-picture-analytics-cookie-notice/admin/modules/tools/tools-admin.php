<?php

class Fupi_TOOLS_admin {

    private $settings;
    private $cook;
    private $main;
    private $tools;
    private $proofrec;

    public function __construct(){
        $this->settings = get_option('fupi_tools');
        $this->cook = get_option('fupi_cook');	
        $this->main = get_option('fupi_main');
        $this->tools = get_option('fupi_tools');
        $this->proofrec = get_option('fupi_proofrec');

        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters(){
        add_action( 'fupi_register_setting_tools', array( $this, 'register_module_settings' ) );
        add_filter( 'fupi_tools_add_fields_settings', array( $this, 'add_fields_settings' ), 10, 1 );
        add_filter( 'fupi_tools_get_page_descr', array( $this, 'get_page_descr' ), 10, 2 );
    }

    private function pp_ok(){
            
        if ( ! empty( $this->cook['pp_id'] ) ) {
            $pp_id = (int) $this->cook['pp_id'];
            return get_post_status( $pp_id ) == 'publish';
        }

        return false;
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
		if ( apply_filters( 'fupi_updating_many_options', false ) ) return $clean_data;

        // If the proofrec module has been disabled
        // - Turn off CRON for sending emails 
        // - Remove the setting for sending emails

        if ( ! empty ( $this->tools['proofrec'] ) && empty ( $clean_data['proofrec'] ) ) {

            // Turn off CRON for sending emails 
            $timestamp = wp_next_scheduled( 'fupi_consents_backup_cron_event' );
            
            while ( $timestamp ) {
                wp_unschedule_event( $timestamp, 'fupi_consents_backup_cron_event' );
                $timestamp = wp_next_scheduled( 'fupi_consents_backup_cron_event' );
            }

            // Remove the setting for sending emails - this needs to be manually switched on every time the module is enabled, to send initial config and PP to the email
            if ( ! empty( $this->proofrec['storage_location'] ) && $this->proofrec['storage_location'] == 'email' ) {
                unset( $this->proofrec['storage_location'] );
                update_option( 'fupi_proofrec', $this->proofrec );
            }
        }

         // UPDATE TRACKING INFO IN CDB / EMAIL

        if ( ! empty ( $clean_data['cook'] ) && ! empty ( $clean_data['proofrec'] ) && $this->pp_ok() ) {
            include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
            $gdpr_checker = new Fupi_compliance_status_checker( 'tools', $clean_data );
            $gdpr_checker->send_and_return_status();
        }
        
		include FUPI_PATH . '/admin/common/fupi-clear-cache.php';
		return $clean_data;
	}
}