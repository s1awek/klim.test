<?php
/**
 * Import Flow Handler for LLM Auto-Configuration
 * 
 * Handles the import creation and flow modifications for LLM auto-configuration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPAI_Bridge_Import_Flow {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor - register hooks
     */
    private function __construct() {
        // Intercept import creation for LLM auto-configuration
        add_filter( 'pmxi_before_import_redirect', array( $this, 'handle_autoconfigure_redirect' ), 10, 3 );
        
        // Handle auto-run flow (skip options/confirm pages)
        add_filter( 'pmxi_template_redirect_url', array( $this, 'handle_template_redirect' ), 10, 2 );
        add_filter( 'pmxi_options_redirect_url', array( $this, 'handle_options_redirect' ), 10, 2 );
        add_filter( 'pmxi_confirm_auto_run', array( $this, 'handle_confirm_auto_run' ), 10, 1 );
    }
    
    /**
     * Handle autoconfigure redirect from Step 1
     */
    public function handle_autoconfigure_redirect( $redirect_url, $post, $action ) {
        // Check if autoconfigure flag is set
        if ( empty( $post['autoconfigure'] ) ) {
            return $redirect_url;
        }
        
        // Redirect to Step 3 with llm_mode=1
        return add_query_arg( array( 'action' => 'template', 'llm_mode' => '1' ), admin_url( 'admin.php?page=pmxi-admin-import' ) );
    }
    
    /**
     * Handle template form submission redirect
     */
    public function handle_template_redirect( $redirect_url, $auto_run ) {
        if ( $auto_run == 1 ) {
            // Skip options page and go straight to confirm with auto_run flag
            return add_query_arg( array( 'action' => 'confirm', 'auto_run' => 1 ), admin_url( 'admin.php?page=pmxi-admin-import' ) );
        }
        
        return $redirect_url;
    }
    
    /**
     * Handle options page redirect
     */
    public function handle_options_redirect( $redirect_url, $auto_run ) {
        if ( $auto_run == 1 ) {
            // Add auto_run flag to confirm page
            return add_query_arg( array( 'action' => 'confirm', 'auto_run' => 1 ), admin_url( 'admin.php?page=pmxi-admin-import' ) );
        }
        
        return $redirect_url;
    }
    
    /**
     * Handle auto-run on confirm page
     */
    public function handle_confirm_auto_run( $auto_run ) {
        return ! empty( $_GET['auto_run'] ) ? 1 : $auto_run;
    }
}

