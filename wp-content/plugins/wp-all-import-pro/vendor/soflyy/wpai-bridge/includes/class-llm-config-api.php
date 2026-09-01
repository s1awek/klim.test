<?php
/**
 * REST API Controller for LLM-based Automatic Import Configuration
 * 
 * This controller provides endpoints for an LLM to programmatically configure
 * WP All Import imports. It includes session-based authentication, file preview,
 * configuration management, and import execution capabilities.
 *
 * @author WP All Import
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class WPAI_Bridge_LLM_Config_API {
    
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
     * Constructor - register REST API routes
     */
    private function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );

        // Ensure core WP All Import options are available in REST requests
        add_filter( 'wp_all_import_config_options', array( $this, 'ensure_config_options' ) );

        // Debug: Log when pmxi hooks fire to diagnose sync issues
        add_action( 'pmxi_before_xml_import', array( $this, 'debug_before_xml_import' ), 1, 1 );
        add_action( 'pmxi_after_xml_import', array( $this, 'debug_after_xml_import' ), 1, 2 );
    }

    /**
     * Debug logging for pmxi_before_xml_import action
     */
    public function debug_before_xml_import( $import_id ) {
        WPAI_Bridge_Logger::debug( "[WPAI Bridge] pmxi_before_xml_import FIRED for import #$import_id" );

        // Check if WooCommerce add-on's hook is registered
        global $wp_filter;
        if ( isset( $wp_filter['pmxi_before_xml_import'] ) ) {
            $callbacks = array();
            foreach ( $wp_filter['pmxi_before_xml_import']->callbacks as $priority => $hooks ) {
                foreach ( $hooks as $hook ) {
                    $callback_name = is_array( $hook['function'] )
                        ? ( is_object( $hook['function'][0] ) ? get_class( $hook['function'][0] ) . '::' : $hook['function'][0] . '::' ) . $hook['function'][1]
                        : ( is_string( $hook['function'] ) ? $hook['function'] : 'closure' );
                    $callbacks[] = "Priority $priority: $callback_name";
                }
            }
            WPAI_Bridge_Logger::debug( "[WPAI Bridge] pmxi_before_xml_import registered callbacks: " . implode( ', ', $callbacks ) );
        }
    }

    /**
     * Debug logging for pmxi_after_xml_import action
     * This helps diagnose if the WooCommerce sync actions are firing
     */
    public function debug_after_xml_import( $import_id, $import ) {
        WPAI_Bridge_Logger::debug( "[WPAI Bridge] pmxi_after_xml_import FIRED for import #$import_id" );
        WPAI_Bridge_Logger::debug( "[WPAI Bridge] Import custom_type: " . ( isset( $import->options['custom_type'] ) ? $import->options['custom_type'] : 'not set' ) );

        // Check if WooCommerce add-on's hook is registered
        global $wp_filter;
        if ( isset( $wp_filter['pmxi_after_xml_import'] ) ) {
            $callbacks = array();
            foreach ( $wp_filter['pmxi_after_xml_import']->callbacks as $priority => $hooks ) {
                foreach ( $hooks as $hook ) {
                    $callback_name = is_array( $hook['function'] )
                        ? ( is_object( $hook['function'][0] ) ? get_class( $hook['function'][0] ) . '::' : $hook['function'][0] . '::' ) . $hook['function'][1]
                        : ( is_string( $hook['function'] ) ? $hook['function'] : 'closure' );
                    $callbacks[] = "Priority $priority: $callback_name";
                }
            }
            WPAI_Bridge_Logger::debug( "[WPAI Bridge] pmxi_after_xml_import registered callbacks: " . implode( ', ', $callbacks ) );
        }

        // Check WooCommerce-related options that should be processed
        $product_stack = get_option( 'wp_all_import_product_stack_' . $import_id, array() );
        $not_linked = get_option( 'wp_all_import_not_linked_products_' . $import_id, array() );
        WPAI_Bridge_Logger::debug( "[WPAI Bridge] wp_all_import_product_stack_$import_id has " . count( $product_stack ) . " items" );
        WPAI_Bridge_Logger::debug( "[WPAI Bridge] wp_all_import_not_linked_products_$import_id has " . count( $not_linked ) . " items" );
    }
    
    /**
     * Ensure core WP All Import options are available when running via REST.
     *
     * The main plugin only initialises its $options property on admin/cron
     * requests. REST requests skip that initialisation, so calls to getOption('chunk_size')
     * inside PMXI_Chunk would otherwise throw and break XML parsing.
     */
    public function ensure_config_options( $options ) {
        // If options are empty or missing critical keys, merge in defaults
        if ( empty( $options ) || ! isset( $options['chunk_size'] ) ) {
            if ( ! class_exists( '\\PMXI_Config' ) && ! class_exists( 'PMXI_Config' ) ) {
                require_once WP_ALL_IMPORT_ROOT_DIR . '/classes/config.php';
            }

            $defaults = \PMXI_Config::createFromFile( WP_ALL_IMPORT_ROOT_DIR . '/config/options.php' )->toArray();

            // Only fill in missing keys, never override existing values
            $options = $options + $defaults;
        }

        return $options;
    }

    /**
     * REST API namespace
     */
    const API_NAMESPACE = 'wp-all-import/v1';

    /**
     * Session token meta key
     */
    const SESSION_TOKEN_META = '_wpai_llm_config_token';

    /**
     * Session expiration time (1 hour)
     */
    const SESSION_EXPIRATION = 3600;

    /**
     * Register REST API routes
     */
    public function register_routes() {

        // Session Management
        $result1 = register_rest_route(self::API_NAMESPACE, '/session/init', array(
            'methods' => 'POST',
            'callback' => array($this, 'init_session'),
            'permission_callback' => array($this, 'check_admin_permissions'),
            'args' => array(
                'import_id' => array('required' => true, 'type' => 'integer'),
            ),
        ));

        $result2 = register_rest_route(self::API_NAMESPACE, '/session/validate', array(
            'methods' => 'POST',
            'callback' => array($this, 'validate_session'),
            'permission_callback' => '__return_true', // Token validation is done in the callback
        ));

        // File Preview & Analysis
        register_rest_route(self::API_NAMESPACE, '/file/preview', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_file_preview'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'import_id' => array('required' => true, 'type' => 'integer'),
                'offset' => array('default' => 0, 'type' => 'integer'),
                'limit' => array('default' => 10, 'type' => 'integer'),
                'random' => array('default' => false, 'type' => 'boolean'),
            ),
        ));

        register_rest_route(self::API_NAMESPACE, '/file/structure', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_file_structure'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'import_id' => array('required' => true, 'type' => 'integer'),
            ),
        ));

        register_rest_route(self::API_NAMESPACE, '/file/xpath-elements', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_xpath_elements'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'import_id' => array('required' => true, 'type' => 'integer'),
                'xpath' => array('default' => '', 'type' => 'string'),
            ),
        ));

        register_rest_route(self::API_NAMESPACE, '/file/content', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_file_content'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'import_id' => array('required' => true, 'type' => 'integer'),
            ),
        ));

        // Import Configuration
        // Note: Import creation is now handled by the normal Step 1 form submission
        // which redirects to the llm-configure action. The REST API is only used
        // for retrieving and updating existing imports.

        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_import'),
            'permission_callback' => array($this, 'check_session_token'),
        ));

        // Alias route for plural form (for compatibility with external apps)
        register_rest_route(self::API_NAMESPACE, '/imports/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_import'),
            'permission_callback' => array($this, 'check_session_token'),
        ));

        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_import'),
            'permission_callback' => array($this, 'check_session_token'),
        ));

        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/options', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_import_options'),
            'permission_callback' => array($this, 'check_session_token'),
        ));

        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/options', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_import_options'),
            'permission_callback' => array($this, 'check_session_token'),
        ));

        // Template & Field Mapping
        register_rest_route(self::API_NAMESPACE, '/template/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_template'),
            'permission_callback' => array($this, 'check_session_token'),
        ));

        register_rest_route(self::API_NAMESPACE, '/template/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_template'),
            'permission_callback' => array($this, 'check_session_token'),
        ));

        register_rest_route(self::API_NAMESPACE, '/xpath/evaluate', array(
            'methods' => 'POST',
            'callback' => array($this, 'evaluate_xpath'),
            'permission_callback' => array($this, 'check_session_token'),
        ));

        // Metadata & Helpers
        register_rest_route(self::API_NAMESPACE, '/meta/post-types', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_post_types'),
            'permission_callback' => array($this, 'check_session_token'),
        ));

        register_rest_route(self::API_NAMESPACE, '/meta/taxonomies', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_taxonomies'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'post_type' => array('default' => '', 'type' => 'string'),
            ),
        ));

        register_rest_route(self::API_NAMESPACE, '/meta/default-options', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_default_options'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'import_type' => array('default' => 'post', 'type' => 'string'),
            ),
        ));

        register_rest_route(self::API_NAMESPACE, '/meta/template-fields', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_template_fields'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'import_type' => array('required' => true, 'type' => 'string'),
                'force_refresh' => array('default' => false, 'type' => 'boolean'),
            ),
        ));

        // Import Execution
        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/execute', array(
            'methods' => 'POST',
            'callback' => array($this, 'execute_import'),
            'permission_callback' => array($this, 'check_session_token'),
        ));

        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/finalize', array(
            'methods' => 'POST',
            'callback' => array($this, 'finalize_import'),
            'permission_callback' => array($this, 'check_session_token'),
        ));

        // Preview - Run WP All Import's native preview to create test records
        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/preview', array(
            'methods' => 'POST',
            'callback' => array($this, 'run_preview'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'preview_mode' => array('default' => 'first', 'type' => 'string'), // first, specific, range, multiple
                'specific_record' => array('default' => '1', 'type' => 'string'),
                'range_start' => array('default' => 1, 'type' => 'integer'),
                'range_end' => array('default' => 1, 'type' => 'integer'),
                'multiple_records' => array('default' => '1', 'type' => 'string'),
            ),
        ));

        // Get import cron URLs for running via trigger/processing endpoints
        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/cron-urls', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_cron_urls'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer'),
            ),
        ));

        // Get import status (for polling during execution)
        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_import_status'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer'),
            ),
        ));

        // Trigger import execution (proxy for wp-load.php?action=trigger)
        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/trigger', array(
            'methods' => 'POST',
            'callback' => array($this, 'trigger_import'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer'),
            ),
        ));

        // Process import chunk (proxy for wp-load.php?action=processing)
        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/process', array(
            'methods' => 'POST',
            'callback' => array($this, 'process_import'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer'),
            ),
        ));

        // Cancel running import
        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/cancel', array(
            'methods' => 'POST',
            'callback' => array($this, 'cancel_import'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer'),
            ),
        ));

        // Refresh session token for a running import (same-origin only, requires WP admin auth)
        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/refresh-session', array(
            'methods' => 'POST',
            'callback' => array($this, 'refresh_import_session'),
            'permission_callback' => array($this, 'check_admin_permissions'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer'),
            ),
        ));

        // Adjust import settings (e.g., records per iteration, processing time limit)
        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/adjust-settings', array(
            'methods' => 'POST',
            'callback' => array($this, 'adjust_import_settings'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer'),
                'records_per_iteration' => array('required' => false, 'type' => 'integer'),
                'cron_processing_time_limit' => array('required' => false, 'type' => 'integer'),
            ),
        ));

        // Get processing limits info (current settings and PHP limits)
        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/processing-limits', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_processing_limits'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer'),
            ),
        ));

        // Clear stale processing lock (when repeated already_processing with no progress)
        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/clear-processing-lock', array(
            'methods' => 'POST',
            'callback' => array($this, 'clear_processing_lock'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer'),
            ),
        ));

        // Get import history records (list of past runs)
        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/history', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_import_history'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer'),
                'limit' => array('default' => 20, 'type' => 'integer'),
                'offset' => array('default' => 0, 'type' => 'integer'),
            ),
        ));

        // Get log content for a specific history record
        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/history/(?P<history_id>\d+)/log', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_history_log'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer'),
                'history_id' => array('required' => true, 'type' => 'integer'),
                'format' => array('default' => 'json', 'type' => 'string', 'enum' => array('json', 'html')),
                'filter_type' => array('default' => '', 'type' => 'string'),
                'search' => array('default' => '', 'type' => 'string'),
            ),
        ));

        // Get current/active log during import processing
        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/current-log', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_current_log'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer'),
                'format' => array('default' => 'json', 'type' => 'string', 'enum' => array('json', 'html')),
                'since_line' => array('default' => 0, 'type' => 'integer'),
            ),
        ));

        // Record filters — compile a structured rule list into an xpath predicate
        // appended to the import's record-selector (see WPAI_Bridge_Filter_Compiler).
        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/filters', array(
            'methods' => 'POST',
            'callback' => array($this, 'set_filters'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer'),
            ),
        ));

        register_rest_route(self::API_NAMESPACE, '/import/(?P<id>\d+)/filter-preview', array(
            'methods' => 'POST',
            'callback' => array($this, 'preview_filters'),
            'permission_callback' => array($this, 'check_session_token'),
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer'),
            ),
        ));
    }

    /**
     * Check if user has admin permissions
     */
    public function check_admin_permissions($request) {
        if (!current_user_can(PMXI_Plugin::$capabilities)) {
            return new WP_Error(
                'rest_forbidden',
                __('You do not have permission to access this endpoint.', 'wp_all_import_plugin'),
                array('status' => 403)
            );
        }
        return true;
    }

    /**
     * Check session token validity
     *
     * This is used as a permission_callback for REST API endpoints.
     *
     * Security model:
     * 1. Session tokens are created by authenticated WordPress users (via session/init endpoint)
     * 2. Tokens are tied to the user who created them (stored in options table)
     * 3. Tokens expire after 1 hour
     * 4. For cross-origin requests (from Vercel app), we validate the token exists and hasn't expired
     *    but don't check user_id since WordPress cookies aren't sent cross-origin
     * 5. The token itself acts as proof of authorization since only the authenticated user
     *    who created the import could have obtained the token
     */
    public function check_session_token($request) {
        $route = $request->get_route();
        $method = $request->get_method();
        WPAI_Bridge_Logger::debug("[LLM Config API] check_session_token called: $method $route");

        $token = $request->get_header('X-WPAI-Session-Token');

        if (empty($token)) {
            WPAI_Bridge_Logger::debug("[LLM Config API] Session token missing for $route");
            return new WP_Error(
                'rest_forbidden',
                __('Session token is required.', 'wp_all_import_plugin'),
                array('status' => 401)
            );
        }

        $import_id = $this->get_import_id_from_request($request);
        if (!$import_id) {
            WPAI_Bridge_Logger::debug("[LLM Config API] Import ID missing from request for $route");
            return new WP_Error(
                'rest_invalid_param',
                __('Import ID is required.', 'wp_all_import_plugin'),
                array('status' => 400)
            );
        }

        // Validate token without checking user_id for cross-origin requests
        // The token itself proves authorization since only the user who created
        // the import session could have obtained it
        if (!$this->validate_token($import_id, $token, false)) {
            WPAI_Bridge_Logger::debug("[LLM Config API] Token validation failed for import $import_id on route $route");
            return new WP_Error(
                'rest_forbidden',
                __('Invalid or expired session token.', 'wp_all_import_plugin'),
                array('status' => 401)
            );
        }

        WPAI_Bridge_Logger::debug("[LLM Config API] Token validated OK for import $import_id on route $route");
        return true;
    }

    /**
     * Get import ID from request
     */
    private function get_import_id_from_request($request) {
        // Try URL parameter first
        $import_id = $request->get_param('id');
        
        // Try query parameter
        if (!$import_id) {
            $import_id = $request->get_param('import_id');
        }

        return $import_id ? intval($import_id) : null;
    }

    /**
     * Validate session token
     *
     * Security: This method validates that:
     * 1. The token exists and matches (constant-time comparison)
     * 2. The token hasn't expired
     * 3. The token was created by the current user (prevents token hijacking)
     */
    /**
     * Public wrapper for validate_token — used by class-template-preparation.php
     * to accept per-import tokens for concurrent schema fetches.
     */
    public function validate_token_public($import_id, $token) {
        return $this->validate_token($import_id, $token, false);
    }

    private function validate_token($import_id, $token, $check_user = true) {
        // Import records are stored in custom table, not as posts
        // Store session tokens in WordPress options table with import_id as key
        $option_name = 'wpai_llm_session_' . $import_id;
        $stored_data = get_option($option_name);
		
        if (empty($stored_data) || !is_array($stored_data)) {
            return false;
        }

        // Check if token matches (constant-time comparison to prevent timing attacks)
        if (!hash_equals($stored_data['token'], $token)) {
            return false;
        }

        // Check if token has expired
        if (time() > $stored_data['expires']) {
            delete_option($option_name);
            return false;
        }

        // Verify the token belongs to the current user (prevents session hijacking)
        // This is critical for security - tokens can only be used by the user who created them
        if ($check_user) {
            $current_user_id = get_current_user_id();
            if (empty($current_user_id) || $stored_data['user_id'] != $current_user_id) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate session token
     */
    public function generate_token($import_id) {
        $token = wp_generate_password(64, false);
        $expires = time() + self::SESSION_EXPIRATION;
        $user_id = get_current_user_id();

        // Store in options table with import_id as key
        // Include user_id to ensure the token can only be used by the user who created it
        $option_name = 'wpai_llm_session_' . $import_id;
        update_option($option_name, array(
            'token' => $token,
            'expires' => $expires,
            'created' => time(),
            'user_id' => $user_id, // Tie session to the user who created it
        ), false); // Don't autoload

        return array(
            'token' => $token,
            'expires' => $expires,
            'expires_in' => self::SESSION_EXPIRATION,
        );
    }

    /**
     * Refresh session token for a running import.
     *
     * Called same-origin from WordPress admin (step1-handler.js) when the iframe
     * needs a fresh token. Uses WP admin auth (cookies) instead of the session token.
     */
    public function refresh_import_session($request) {
        $import_id = intval($request->get_param('id'));

        // Verify the import exists
        $import = new \PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new \WP_Error(
                'import_not_found',
                sprintf(__('Import #%s not found.', 'wpai-bridge'), $import_id),
                array('status' => 404)
            );
        }

        // Generate a new token for this import
        $token_data = $this->generate_token($import_id);

        WPAI_Bridge_Logger::debug("[LLM Config API] Session refreshed for import $import_id");

        return new \WP_REST_Response(array(
            'success' => true,
            'token' => $token_data['token'],
            'expires' => $token_data['expires'],
            'expires_in' => $token_data['expires_in'],
        ), 200);
    }

    /**
     * Ensure WP All Import Pro classes are loaded
     */
    private function ensure_wpai_loaded() {
        // Check if WP All Import Pro is available
        if (!class_exists('PMXI_Plugin')) {
            return false;
        }

        // WP All Import Pro's autoloader should already be registered
        // Just trigger it by checking if the class exists
        if (!class_exists('PMXI_Import_Record')) {
            return false;
        }

        return true;
    }

    /**
     * Get cached file metadata from custom table.
     *
     * @param int $import_id Import ID
     * @param string $file_path Absolute path to the import file
     * @return array|false Cached metadata or false if not found
     */
    private function get_cached_file_meta($import_id, $file_path) {
        $structure_cache = WPAI_Bridge_File_Structure_Cache::getInstance();
        $cached = $structure_cache->get_cached_structure($import_id, $file_path);

        if ($cached !== false) {
            WPAI_Bridge_Logger::debug('[FileMeta] Cache hit', array(
                'import_id' => $import_id,
                'records' => $cached['total_records'] ?? 'unknown',
            ));
            return $cached;
        }

        WPAI_Bridge_Logger::debug('[FileMeta] Cache miss', array(
            'import_id' => $import_id,
        ));

        return false;
    }

    /**
     * Cache file metadata to custom table.
     *
     * @param int $import_id Import ID
     * @param string $file_path Absolute path to the import file
     * @param array $meta Metadata to cache
     * @return bool Success
     */
    private function cache_file_meta($import_id, $file_path, $meta) {
        $structure_cache = WPAI_Bridge_File_Structure_Cache::getInstance();
        return $structure_cache->save_cached_structure($import_id, $meta);
    }

    /**
     * Fetch paginated records with early termination
     * Stops reading once we have enough records, avoiding full file scan
     *
     * @param string $file_path Absolute path to the import file
     * @param object $import Import record with xpath, root_element, options
     * @param int $offset Number of records to skip
     * @param int $limit Number of records to return
     * @param int|null $cached_total Optional cached total count (avoids full scan)
     * @return array Array with 'records', 'total', and 'count'
     */
    private function fetch_paginated_records($file_path, $import, $offset, $limit, $cached_total = null) {
        $encoding = !empty($import->options['encoding']) ? $import->options['encoding'] : 'UTF-8';

        $chunk = new PMXI_Chunk($file_path, array(
            'element' => $import->root_element,
            'encoding' => $encoding
        ));

        $records = array();
        $skipped = 0;
        $collected = 0;
        $total_records = 0;
        $need_full_count = ($cached_total === null);

        while ($xml_chunk = $chunk->read()) {
            if (!empty($xml_chunk)) {
                $xml = "<?xml version=\"1.0\" encoding=\"" . $encoding . "\"?>\n" . $xml_chunk;
                $dom = new DOMDocument('1.0', $encoding);
                $old = libxml_use_internal_errors(true);
                $dom->loadXML($xml);
                libxml_use_internal_errors($old);
                $xpath_obj = new DOMXPath($dom);

                if (($elements = @$xpath_obj->query($import->xpath)) && $elements->length) {
                    foreach ($elements as $element) {
                        $total_records++;

                        // Skip until we reach offset
                        if ($skipped < $offset) {
                            $skipped++;
                            continue;
                        }

                        // Collect until we have enough
                        if ($collected < $limit) {
                            $records[] = $dom->saveXML($element);
                            $collected++;
                        }

                        // If we have cached total and enough records, we can stop
                        if (!$need_full_count && $collected >= $limit) {
                            break 2; // Exit both loops
                        }
                    }
                }
            }
        }

        return array(
            'records' => $records,
            'total' => $need_full_count ? $total_records : $cached_total,
            'count' => count($records),
        );
    }

    /**
     * Fetch random records using reservoir sampling
     * Single-pass algorithm with O(k) memory where k is the sample size
     * Mathematically proven to give uniform random distribution
     *
     * @param string $file_path Absolute path to the import file
     * @param object $import Import record with xpath, root_element, options
     * @param int $limit Number of random records to return
     * @return array Array with 'records', 'total', and 'count'
     */
    private function fetch_random_records($file_path, $import, $limit) {
        $encoding = !empty($import->options['encoding']) ? $import->options['encoding'] : 'UTF-8';

        $chunk = new PMXI_Chunk($file_path, array(
            'element' => $import->root_element,
            'encoding' => $encoding
        ));

        // Reservoir sampling: maintain exactly $limit items
        $reservoir = array();
        $total_records = 0;

        while ($xml_chunk = $chunk->read()) {
            if (!empty($xml_chunk)) {
                $xml = "<?xml version=\"1.0\" encoding=\"" . $encoding . "\"?>\n" . $xml_chunk;
                $dom = new DOMDocument('1.0', $encoding);
                $old = libxml_use_internal_errors(true);
                $dom->loadXML($xml);
                libxml_use_internal_errors($old);
                $xpath_obj = new DOMXPath($dom);

                if (($elements = @$xpath_obj->query($import->xpath)) && $elements->length) {
                    foreach ($elements as $element) {
                        $total_records++;
                        $record_xml = $dom->saveXML($element);

                        if (count($reservoir) < $limit) {
                            // Fill reservoir until we have $limit items
                            $reservoir[] = $record_xml;
                        } else {
                            // Reservoir sampling: replace with probability limit/total_records
                            // This is Algorithm R from Vitter's paper
                            $j = mt_rand(0, $total_records - 1);
                            if ($j < $limit) {
                                $reservoir[$j] = $record_xml;
                            }
                        }
                    }
                }
            }
        }

        // Shuffle to randomize order of selected items
        shuffle($reservoir);

        return array(
            'records' => $reservoir,
            'total' => $total_records,
            'count' => count($reservoir),
        );
    }

    /**
     * Parse file to get structure metadata (total count, first record)
     * Used when cache is cold
     *
     * @param string $file_path Absolute path to the import file
     * @param object $import Import record with xpath, root_element, options
     * @return array Metadata array
     */
    private function parse_file_metadata($file_path, $import) {
        $encoding = !empty($import->options['encoding']) ? $import->options['encoding'] : 'UTF-8';

        $chunk = new PMXI_Chunk($file_path, array(
            'element' => $import->root_element,
            'encoding' => $encoding
        ));

        $total_records = 0;
        $first_record = null;

        while ($xml_chunk = $chunk->read()) {
            if (!empty($xml_chunk)) {
                $xml = "<?xml version=\"1.0\" encoding=\"" . $encoding . "\"?>\n" . $xml_chunk;
                $dom = new DOMDocument('1.0', $encoding);
                $old = libxml_use_internal_errors(true);
                $dom->loadXML($xml);
                libxml_use_internal_errors($old);
                $xpath_obj = new DOMXPath($dom);

                if (($elements = @$xpath_obj->query($import->xpath)) && $elements->length) {
                    $total_records += $elements->length;

                    // Get first record if we don't have one yet
                    if ($first_record === null && $elements->length > 0) {
                        $first_record = $dom->saveXML($elements->item(0));
                    }
                }
            }
        }

        $file_size = filesize($file_path);
        $file_mtime = filemtime($file_path);

        return array(
            'total_records' => $total_records,
            'root_element' => $import->root_element,
            'xpath' => $import->xpath,
            'first_record' => $first_record,
            'file_size' => $file_size,
            'file_mtime' => $file_mtime,
            'file_signature' => $file_size . '_' . $file_mtime,
        );
    }

    /**
     * Initialize LLM configuration session
     */
    public function init_session($request) {
        $import_id = $request->get_param('import_id');

        if (!$import_id) {
            return new WP_Error(
                'rest_invalid_param',
                __('Import ID is required.', 'wp_all_import_plugin'),
                array('status' => 400)
            );
        }

        // Ensure WP All Import Pro classes are loaded
        if (!$this->ensure_wpai_loaded()) {
            return new WP_Error(
                'rest_dependency_missing',
                __('WP All Import Pro is not available.', 'wp_all_import_plugin'),
                array('status' => 500)
            );
        }

        // Verify import exists
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error(
                'rest_not_found',
                __('Import not found.', 'wp_all_import_plugin'),
                array('status' => 404)
            );
        }

        // Generate session token
        $token_data = $this->generate_token($import_id);

        // File metadata reported to the client alongside the session.
        $file_path = $this->get_import_file_path($import);
        $file_info = array(
            'available' => false,
            'size' => 0,
            'filename' => '',
            'mime_type' => '',
        );

        if (!is_wp_error($file_path) && file_exists($file_path)) {
            $file_info = array(
                'available' => true,
                'size' => filesize($file_path),
                'filename' => basename($file_path),
                'mime_type' => $this->get_file_mime_type($file_path),
            );
        }

        return new WP_REST_Response(array(
            'success' => true,
            'import_id' => $import_id,
            'session' => $token_data,
            'file' => $file_info,
            'message' => __('Session initialized successfully.', 'wp_all_import_plugin'),
        ), 200);
    }

    /**
     * Get MIME type for import file
     */
    private function get_file_mime_type($file_path) {
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        $mime_types = array(
            'xml' => 'application/xml',
            'csv' => 'text/csv',
            'json' => 'application/json',
            'txt' => 'text/plain',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'gz' => 'application/gzip',
            'zip' => 'application/zip',
        );

        return isset($mime_types[$extension]) ? $mime_types[$extension] : 'application/octet-stream';
    }

    /**
     * Validate session
     */
    /**
     * Which WP All Import this is running inside.
     *
     * PMXI_EDITION is the host's own constant and the same one the version floor
     * already branches on, so this introduces no second notion of edition. Unknown
     * rather than a guess when the constant is absent: a wrong attribution is worse
     * than an admitted gap, because it silently moves spend between the free and
     * paid columns.
     */
    public static function edition() {
        if ( ! defined( 'PMXI_EDITION' ) ) {
            return 'unknown';
        }

        $edition = (string) PMXI_EDITION;

        return in_array( $edition, array( 'free', 'paid' ), true ) ? $edition : 'unknown';
    }

    public function validate_session($request) {
        $import_id = $request->get_param('import_id');
        $token = $request->get_header('X-WPAI-Session-Token');

        // Validate token without checking user_id for cross-origin requests
        if ($this->validate_token($import_id, $token, false)) {
            $option_name = 'wpai_llm_session_' . $import_id;
            $stored_data = get_option($option_name);
            return new WP_REST_Response(array(
                'success' => true,
                'valid' => true,
                'expires' => $stored_data['expires'],
                'time_remaining' => $stored_data['expires'] - time(),
                // Answered here because this is the one exchange where the site
                // speaks for itself: the caller reached this point by proving it
                // holds a session this site issued. Anything the browser or a signed
                // request asserts about the edition is only a claim — the shared
                // secret ships extractable in the free plugin, so it authenticates
                // nothing about WHICH install is calling.
                //
                // Deliberately no licence key and no derived "licensed" flag: Pro
                // stores keys rather than a status, so answering that would mean
                // reading key material in order to report something that says only
                // that a key is present, not that it is valid.
                'edition' => self::edition(),
            ), 200);
        }

        return new WP_REST_Response(array(
            'success' => false,
            'valid' => false,
            'message' => __('Session is invalid or expired.', 'wp_all_import_plugin'),
        ), 401);
    }

    /**
     * Get file preview records
     * Optimized to avoid loading entire file into memory:
     * - For pagination: Uses early termination (stops after collecting enough records)
     * - For random sampling: Uses reservoir sampling algorithm (O(k) memory)
     * - Uses cached total count when available
     */
    public function get_file_preview($request) {
        WPAI_Bridge_Logger::perf_start('get_file_preview');

        $import_id = $request->get_param('import_id');
        $offset = $request->get_param('offset');
        $limit = $request->get_param('limit');
        $random = $request->get_param('random');

        // Check pre-cached preview records first (populated at upload time)
        $structure_cache = WPAI_Bridge_File_Structure_Cache::getInstance();

        if ($random) {
            $cached_preview = $structure_cache->get_random_preview_records($import_id, $limit);
        } else {
            $cached_preview = $structure_cache->get_preview_records($import_id, $offset, $limit);
        }

        if ($cached_preview !== false && $cached_preview['count'] > 0) {
            WPAI_Bridge_Logger::perf_end('get_file_preview', array(
                'import_id' => $import_id,
                'mode' => $random ? 'random' : 'paginated',
                'total_records' => $cached_preview['total'],
                'returned' => $cached_preview['count'],
                'cache_hit' => true,
                'source' => 'pre_cached',
            ));

            return new WP_REST_Response(array(
                'success' => true,
                'total' => $cached_preview['total'],
                'offset' => $offset,
                'limit' => $limit,
                'count' => $cached_preview['count'],
                'records' => $cached_preview['records'],
            ), 200);
        }

        // Fallback: read from file (should be rare if hook-based extraction is working)
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error(
                'rest_not_found',
                __('Import not found.', 'wp_all_import_plugin'),
                array('status' => 404)
            );
        }

        $file_path = $this->get_import_file_path($import);
        if (is_wp_error($file_path)) {
            return $file_path;
        }

        try {
            $cached_meta = $this->get_cached_file_meta($import_id, $file_path);
            $cached_total = $cached_meta ? $cached_meta['total_records'] : null;

            if ($random) {
                $result = $this->fetch_random_records($file_path, $import, $limit);
            } else {
                $result = $this->fetch_paginated_records($file_path, $import, $offset, $limit, $cached_total);
            }

            WPAI_Bridge_Logger::perf_end('get_file_preview', array(
                'import_id' => $import_id,
                'mode' => $random ? 'random' : 'paginated',
                'total_records' => $result['total'],
                'returned' => $result['count'],
                'cache_hit' => false,
                'source' => 'file_read',
            ));

            return new WP_REST_Response(array(
                'success' => true,
                'total' => $result['total'],
                'offset' => $offset,
                'limit' => $limit,
                'count' => $result['count'],
                'records' => $result['records'],
            ), 200);

        } catch (Exception $e) {
            WPAI_Bridge_Logger::perf_end('get_file_preview', array(
                'import_id' => $import_id,
                'error' => $e->getMessage(),
            ));

            return new WP_Error(
                'rest_parse_error',
                sprintf(__('Error parsing file: %s', 'wp_all_import_plugin'), $e->getMessage()),
                array('status' => 500)
            );
        }
    }

    /**
     * Return the raw import file content so the Vercel layer can log it
     * without making its own outbound HTTP requests.
     */
    public function get_file_content( $request ) {
        $import_id = $request->get_param( 'import_id' );

        $import = new PMXI_Import_Record();
        $import->getById( $import_id );

        if ( $import->isEmpty() ) {
            return new WP_Error(
                'rest_not_found',
                __( 'Import not found.', 'wp_all_import_plugin' ),
                array( 'status' => 404 )
            );
        }

        $file_path = $this->get_import_file_path( $import );
        if ( is_wp_error( $file_path ) ) {
            return $file_path;
        }

        $content = file_get_contents( $file_path );
        if ( $content === false ) {
            return new WP_Error(
                'rest_file_read_error',
                __( 'Failed to read import file.', 'wp_all_import_plugin' ),
                array( 'status' => 500 )
            );
        }

        return new WP_REST_Response( array(
            'success'   => true,
            'file_name' => basename( $file_path ),
            'content'   => $content,
        ), 200 );
    }

    /**
     * Get file structure information
     * Uses caching to avoid re-parsing on subsequent requests
     */
    public function get_file_structure($request) {
        WPAI_Bridge_Logger::perf_start('get_file_structure');

        $import_id = $request->get_param('import_id');

        // Load import
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error(
                'rest_not_found',
                __('Import not found.', 'wp_all_import_plugin'),
                array('status' => 404)
            );
        }

        // Get file path (handles preview imports and file history)
        $file_path = $this->get_import_file_path($import);
        if (is_wp_error($file_path)) {
            return $file_path;
        }

        try {
            $cached_meta = $this->get_cached_file_meta($import_id, $file_path);

            if ($cached_meta !== false) {
                $cache_source = isset($cached_meta['trigger']) ? 'hook_' . $cached_meta['trigger'] : 'on_demand';

                // Return cached data
                WPAI_Bridge_Logger::perf_end('get_file_structure', array(
                    'import_id' => $import_id,
                    'cache_hit' => true,
                    'cache_source' => $cache_source,
                    'total_records' => $cached_meta['total_records'],
                ));

                return new WP_REST_Response(array(
                    'success' => true,
                    'file_path' => basename($import->path),
                    'root_element' => $cached_meta['root_element'],
                    'xpath' => $cached_meta['xpath'],
                    'total_records' => $cached_meta['total_records'],
                    'sample_record' => $cached_meta['first_record'],
                    'field_names' => $cached_meta['field_names'] ?? null,
                    'root_candidates' => $cached_meta['root_candidates'] ?? array(),
                    'cached' => true,
                    'cache_source' => $cache_source,
                ), 200);
            }

            // Cache miss - parse file and cache the result
            // This should be rare if hook-based extraction is working
            WPAI_Bridge_Logger::info('[FileMeta] Cache miss - parsing on demand (hook may not have fired)', array(
                'import_id' => $import_id,
            ));

            $meta = $this->parse_file_metadata($file_path, $import);
            $meta['trigger'] = 'on_demand_api';

            // Cache for future requests
            $this->cache_file_meta($import_id, $file_path, $meta);

            WPAI_Bridge_Logger::perf_end('get_file_structure', array(
                'import_id' => $import_id,
                'cache_hit' => false,
                'cache_source' => 'on_demand',
                'total_records' => $meta['total_records'],
            ));

            return new WP_REST_Response(array(
                'success' => true,
                'file_path' => basename($import->path),
                'root_element' => $meta['root_element'],
                'xpath' => $meta['xpath'],
                'total_records' => $meta['total_records'],
                'sample_record' => $meta['first_record'],
                'root_candidates' => $meta['root_candidates'] ?? array(),
                'cached' => false,
                'cache_source' => 'on_demand',
            ), 200);

        } catch (Exception $e) {
            WPAI_Bridge_Logger::perf_end('get_file_structure', array(
                'import_id' => $import_id,
                'error' => $e->getMessage(),
            ));

            return new WP_Error(
                'rest_parse_error',
                sprintf(__('Error analyzing file: %s', 'wp_all_import_plugin'), $e->getMessage()),
                array('status' => 500)
            );
        }
    }

    /**
     * Get available XPath elements from file
     */
    public function get_xpath_elements($request) {
        $import_id = $request->get_param('import_id');
        $xpath = $request->get_param('xpath');

        // Load import
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error(
                'rest_not_found',
                __('Import not found.', 'wp_all_import_plugin'),
                array('status' => 404)
            );
        }

        // Get file path (handles preview imports and file history)
        $file_path = $this->get_import_file_path($import);
        if (is_wp_error($file_path)) {
            return $file_path;
        }

        try {
            // If no xpath provided, use import's xpath
            if (empty($xpath)) {
                $xpath = $import->xpath;
            }

            // Get encoding with safety check
            $encoding = ! empty( $import->options['encoding'] ) ? $import->options['encoding'] : 'UTF-8';

            // Use PMXI_Chunk to parse the file without template
            $chunk = new PMXI_Chunk($file_path, array(
                'element' => $import->root_element,
                'encoding' => $encoding
            ));

            $first_record = null;

            // Get first record
            while ($xml_chunk = $chunk->read()) {
                if (!empty($xml_chunk)) {
                    $xml = "<?xml version=\"1.0\" encoding=\"" . $encoding . "\"?>\n" . $xml_chunk;
                    $dom = new DOMDocument('1.0', $encoding);
                    $old = libxml_use_internal_errors(true);
                    $dom->loadXML($xml);
                    libxml_use_internal_errors($old);
                    $xpath_obj = new DOMXPath($dom);

                    // Get first record using the import's xpath
                    if (($elements = @$xpath_obj->query($import->xpath)) and $elements->length > 0) {
                        $first_record = $dom->saveXML($elements->item(0));
                        break;
                    }
                }
            }

            if (empty($first_record)) {
                // Use safe response to prevent any PHP warnings from corrupting JSON
                return $this->create_safe_rest_response(array(
                    'success' => true,
                    'elements' => array(),
                    'message' => __('No records found in file.', 'wp_all_import_plugin'),
                ), 200);
            }

            // Get first record and extract all element names
            $elements = $this->extract_xml_elements($first_record);

            // Use safe response to prevent any PHP warnings from corrupting JSON
            return $this->create_safe_rest_response(array(
                'success' => true,
                'xpath' => $xpath,
                'elements' => $elements,
                'count' => count($elements),
            ), 200);

        } catch (Exception $e) {
            return new WP_Error(
                'rest_parse_error',
                sprintf(__('Error extracting elements: %s', 'wp_all_import_plugin'), $e->getMessage()),
                array('status' => 500)
            );
        }
    }

    /**
     * Extract XML element names from a record
     *
     * @param mixed $xml The XML to parse (string or SimpleXMLElement)
     * @param string $prefix The current path prefix (used for nested elements)
     * @param bool $skip_root Whether to skip the root element (true for first call)
     */
    private function extract_xml_elements($xml, $prefix = '', $skip_root = true) {
        $elements = array();

        // If it's a string, parse it first
        if (is_string($xml)) {
            if (empty($xml)) {
                return $elements;
            }

            // Remove XML declaration if present
            $xml = preg_replace('/<\?xml[^?]*\?>\s*/i', '', $xml);

            // Parse XML - wrap in root to handle fragments
            $xml = simplexml_load_string('<root>' . $xml . '</root>');

            if ($xml === false) {
                return $elements;
            }

            // If skip_root is true, we want to skip the actual record root element
            // and start from its children. The record root is the first child of our wrapper.
            if ($skip_root && count($xml->children()) > 0) {
                // Get the first child (the actual record root like <node>)
                $record_root = $xml->children()[0];
                // Extract elements from the record root's children, not including the root itself
                return $this->extract_xml_elements($record_root, '', false);
            }
        }

        // Recursively extract element names
        foreach ($xml->children() as $child) {
            $name = $child->getName();
            $full_name = $prefix ? $prefix . '/' . $name : $name;

            $elements[] = array(
                'name' => $name,
                'xpath' => '{' . $full_name . '[1]}',
                'full_path' => $full_name,
                'has_children' => count($child->children()) > 0,
            );

            // Recursively get child elements - pass the SimpleXMLElement directly
            if (count($child->children()) > 0) {
                $child_elements = $this->extract_xml_elements($child, $full_name, false);
                $elements = array_merge($elements, $child_elements);
            }
        }

        return $elements;
    }

    /**
     * DEPRECATED: Create a new import
     *
     * This method is no longer used. Import creation is now handled by the normal
     * Step 1 form submission flow, which redirects to the llm-configure action.
     *
     * Keeping this method for reference but it should not be called.
     */
    /*
    public function create_import($request) {
        // This endpoint is deprecated and should not be used
        return new WP_Error(
            'rest_deprecated',
            __('This endpoint is deprecated. Use the normal Step 1 form submission instead.', 'wp_all_import_plugin'),
            array('status' => 410) // 410 Gone
        );
    }
    */

    /**
     * Get import configuration
     */
    public function get_import($request) {
        $import_id = $request->get_param('id');

        // Load import
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error(
                'rest_not_found',
                __('Import not found.', 'wp_all_import_plugin'),
                array('status' => 404)
            );
        }

        // Prepare import data
        $import_data = array(
            'id' => $import->id,
            'name' => $import->friendly_name,
            'type' => $import->type,
            'path' => $import->path,
            'root_element' => $import->root_element,
            'xpath' => $import->xpath,
            'feed_type' => $import->feed_type,
            'imported' => $import->imported,
            'created' => $import->created,
            'updated' => $import->updated,
            'skipped' => $import->skipped,
            'deleted' => $import->deleted,
            'last_activity' => $import->last_activity,
            'options' => $import->options,
        );

        return new WP_REST_Response(array(
            'success' => true,
            'import' => $import_data,
        ), 200);
    }

    /**
     * Update import configuration
     */
    public function update_import($request) {
        $import_id = $request->get_param('id');
        $params = $request->get_json_params();

        // Load import
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error(
                'rest_not_found',
                __('Import not found.', 'wp_all_import_plugin'),
                array('status' => 404)
            );
        }

        try {
            // Update allowed fields
            $update_data = array();

            if (isset($params['name'])) {
                $update_data['friendly_name'] = $params['name'];
            }
            if (isset($params['root_element'])) {
                $update_data['root_element'] = $params['root_element'];
            }
            if (isset($params['xpath'])) {
                $update_data['xpath'] = $params['xpath'];
            }
            if (isset($params['feed_type'])) {
                $update_data['feed_type'] = $params['feed_type'];
            }

            if (!empty($update_data)) {
                $import->set($update_data)->update();

                // When root_element or xpath changes the cached file structure
                // (records, field names, counts) is stale — clear and rebuild it.
                if (isset($params['root_element']) || isset($params['xpath'])) {
                    $structure_cache = WPAI_Bridge_File_Structure_Cache::getInstance();
                    $structure_cache->clear_cached_structure($import_id);

                    // Reload import to pick up the new values, then re-parse & cache.
                    $import->getById($import_id);
                    $file_path = $this->get_import_file_path($import);
                    if (!is_wp_error($file_path) && file_exists($file_path)) {
                        $meta = $this->parse_file_metadata($file_path, $import);
                        $meta['trigger'] = 'update_import_reparse';
                        $this->cache_file_meta($import_id, $file_path, $meta);
                        $import->set(array('count' => $meta['total_records']))->update();
                    }

                    WPAI_Bridge_Logger::info('[LLMConfigAPI] Cache rebuilt after root_element/xpath change', array(
                        'import_id' => $import_id,
                        'root_element' => $import->root_element,
                        'xpath' => $import->xpath,
                    ));
                }
            }

            return new WP_REST_Response(array(
                'success' => true,
                'message' => __('Import updated successfully.', 'wp_all_import_plugin'),
            ), 200);

        } catch (Exception $e) {
            return new WP_Error(
                'rest_update_error',
                sprintf(__('Error updating import: %s', 'wp_all_import_plugin'), $e->getMessage()),
                array('status' => 500)
            );
        }
    }

    /**
     * Get import options
     */
    /**
     * Remove WP All Import plugin secrets from an options blob before it is
     * returned to the model. These are credentials / signing material that the
     * agent never needs to configure an import and must not be exfiltrated
     * (cron_job_key, Google Drive OAuth id/signature, license keys, etc.).
     *
     * The `_key` suffix is deliberately NOT a blanket denylist: `unique_key` is a
     * legitimate, non-sensitive import setting the agent must be able to read.
     */
    public static function strip_sensitive_options($options) {
        if (!is_array($options)) {
            return $options;
        }

        $explicit_deny = array(
            'cron_job_key',
            'google_client_id',
            'google_signature',
            'licenses',
            'scheduling_license',
            'scheduling_license_status',
        );

        foreach (array_keys($options) as $key) {
            $lower = strtolower((string) $key);

            $deny = in_array($lower, $explicit_deny, true)
                || 0 === strpos($lower, 'google_')
                || false !== strpos($lower, 'license')
                || (bool) preg_match('/(_secret|_signature|_token|_password)$/', $lower)
                // *_key only when it clearly names a credential (never unique_key).
                || (bool) preg_match('/(cron|api|access|private|client|secret|auth)[a-z0-9]*_key$/', $lower);

            if ($deny) {
                unset($options[$key]);
            }
        }

        return $options;
    }

    public function get_import_options($request) {
        $import_id = $request->get_param('id');
        $full      = (bool) $request->get_param('full');

        // Load import
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error(
                'rest_not_found',
                __('Import not found.', 'wp_all_import_plugin'),
                array('status' => 404)
            );
        }

        // A fresh import has only a handful of persisted keys (custom_type,
        // taxonomy_type, encoding) — none of the dedup settings an agent must
        // verify — because WP All Import only materializes the full option template
        // when the import is first configured. Overlay core's own defaults so the
        // effective config (what WOULD apply) is visible even before configuring.
        $defaults  = class_exists('PMXI_Plugin') && is_callable(array('PMXI_Plugin', 'get_default_import_options'))
            ? (array) PMXI_Plugin::get_default_import_options()
            : array();
        $effective = array_merge($defaults, (array) $import->options);

        // Mirror core's is_update_previous onto the documented MCP alias so the
        // dedup view names the same flag set-options accepts.
        if (array_key_exists('is_update_previous', $effective) && !array_key_exists('update_existing_records', $effective)) {
            $effective['update_existing_records'] = $effective['is_update_previous'];
        }

        $effective = self::strip_sensitive_options($effective);

        // The four settings that decide whether records dedupe/update or collapse —
        // always surfaced up front so they can be verified without paging options.
        $dedup = array(
            'unique_key'              => isset($effective['unique_key']) ? $effective['unique_key'] : '',
            'create_new_records'      => isset($effective['create_new_records']) ? $effective['create_new_records'] : '',
            'update_existing_records' => isset($effective['update_existing_records']) ? $effective['update_existing_records'] : '',
            'is_update_previous'      => isset($effective['is_update_previous']) ? $effective['is_update_previous'] : '',
        );

        if ($full) {
            $options = $effective;
            $view    = 'full';
        } else {
            $options = self::curate_import_options($effective);
            $view    = 'summary';
        }

        return new WP_REST_Response(array(
            'success' => true,
            'view'    => $view,
            'dedup'   => $dedup,
            'options' => $options,
            'note'    => 'summary' === $view
                ? __('Showing the settings that matter for configuration (dedup, duplicate handling, record selection, update behavior, status/encoding). Call with full:true for every effective option including add-on settings.', 'wp_all_import_plugin')
                : __('Showing every effective option (core defaults overlaid with this import\'s saved options), add-on settings included.', 'wp_all_import_plugin'),
        ), 200);
    }

    /**
     * The per-import options that actually matter when configuring/verifying an
     * import, distilled from the ~900-key effective set (which, after configure,
     * is dominated by add-on order/subscription noise). A curated whitelist keeps
     * the default get-options view small and stable regardless of which add-ons
     * bloat the option template; full:true still returns everything. Any internal
     * bridge key (_wpai_mcp_*) is always kept so get-filters keeps working.
     */
    private static function curate_import_options($effective) {
        $keep = array(
            // dedup / duplicate handling
            'unique_key', 'create_new_records', 'update_existing_records', 'is_update_previous',
            'duplicate_matching', 'duplicate_indicator', 'custom_duplicate_name', 'custom_duplicate_value',
            // missing-record handling
            'is_delete_missing', 'delete_missing_logic', 'delete_missing_action',
            'is_send_removed_to_trash', 'set_missing_to_draft', 'status_of_removed',
            // what an update touches
            'update_all_data', 'is_keep_former_posts', 'is_update_status', 'is_update_content',
            'is_update_title', 'is_update_excerpt', 'is_update_categories', 'is_update_custom_fields',
            'is_update_images', 'is_update_dates', 'is_update_author',
            // record selection / range
            'is_import_specified', 'import_specified',
            // identity / type / status
            'type', 'custom_type', 'taxonomy_type', 'status',
            // source parsing
            'encoding', 'delimiter', 'feed_type',
        );
        $out = array();
        foreach ($keep as $key) {
            if (array_key_exists($key, $effective)) {
                $out[$key] = $effective[$key];
            }
        }
        // Preserve bridge-internal state (active filter rules/base) that other
        // tools read back through get-options.
        foreach ($effective as $key => $value) {
            if (0 === strpos((string) $key, '_wpai_mcp_')) {
                $out[$key] = $value;
            }
        }
        return $out;
    }

    /**
     * Update import options
     */
    public function update_import_options($request) {
        $import_id = $request->get_param('id');
        $params = $request->get_json_params();

        // Load import
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error(
                'rest_not_found',
                __('Import not found.', 'wp_all_import_plugin'),
                array('status' => 404)
            );
        }

        try {
            // Merge new options with existing options
            $current_options = $import->options;
            $new_options = !empty($params['options']) ? $params['options'] : array();

            // Type-check the known critical keys (coerce sane inputs, reject clearly-wrong
            // ones). Unknown keys are NOT rejected -- WP All Import is add-on-extensible, so a
            // full whitelist is impractical -- but we surface them as informational warnings.
            list($new_options, $errors, $warnings) = $this->validate_critical_options($new_options, $current_options);

            if (!empty($errors)) {
                return new WP_Error(
                    'rest_invalid_option',
                    implode(' ', $errors),
                    array('status' => 400, 'errors' => $errors)
                );
            }

            // Sanitize new options
            $sanitized_options = $this->sanitize_import_options($new_options);

            // Deep merge options
            $merged_options = array_replace_recursive($current_options, $sanitized_options);

            // Update import
            $import->set(array('options' => $merged_options))->update();

            return new WP_REST_Response(array(
                'success' => true,
                'message' => __('Import options updated successfully.', 'wp_all_import_plugin'),
                'warnings' => $warnings,
            ), 200);

        } catch (Exception $e) {
            return new WP_Error(
                'rest_update_error',
                sprintf(__('Error updating import options: %s', 'wp_all_import_plugin'), $e->getMessage()),
                array('status' => 500)
            );
        }
    }

    /**
     * Get import template
     */
    public function get_template($request) {
        $import_id = $request->get_param('id');

        // Load import
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error(
                'rest_not_found',
                __('Import not found.', 'wp_all_import_plugin'),
                array('status' => 404)
            );
        }

        // Build ACF field metadata for display purposes (labels for field keys)
        $acf_metadata = $this->build_acf_field_metadata_map();

        // Return all template options so the LLM can use them as a starting point.
        // Strip credential-bearing keys (ftp_password, ftp_private_key, *_secret,
        // license keys, …) with the same denylist get-options uses — the template
        // blob otherwise leaks the very FTP secrets get-options is careful to hide.
        // Use safe response to prevent any PHP warnings from corrupting JSON
        return $this->create_safe_rest_response(array(
            'success' => true,
            'template' => self::strip_sensitive_options((array) $import->options),
            'acf_metadata' => $acf_metadata, // Map of field keys to labels
        ), 200);
    }

    /**
     * Update import template
     */
    public function update_template($request) {
        $import_id = $request->get_param('id');
        $params = $request->get_json_params();

        // Load import
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error(
                'rest_not_found',
                __('Import not found.', 'wp_all_import_plugin'),
                array('status' => 404)
            );
        }

        try {
            $current_options = $import->options;
            $template = !empty($params['template']) ? $params['template'] : array();

            // Process ACF fields separately - they need special handling
            // to convert flat bracket notation to nested array structure
            $acf_fields = $this->extract_acf_fields($template);

            // Process add-on fields (pmui, pmsci_customer, pmgi, pmwi_order, etc.)
            // These also use bracket notation and need conversion to nested arrays
            // Pass current_options to enable dynamic detection of add-on namespaces
            $addon_fields = $this->extract_addon_fields($template, $current_options);

            // Merge add-on fields into current options
            foreach ($addon_fields as $prefix => $fields) {
                if (!isset($current_options[$prefix]) || !is_array($current_options[$prefix])) {
                    $current_options[$prefix] = array();
                }
                $current_options[$prefix] = $this->array_merge_recursive_distinct(
                    $current_options[$prefix],
                    $fields
                );
            }

            // Merge ACF fields into current options using proper nested structure
            if (!empty($acf_fields['acf'])) {
                if (!isset($current_options['acf']) || !is_array($current_options['acf'])) {
                    $current_options['acf'] = array();
                }
                // Use + operator to preserve numeric keys (group IDs)
                // array_merge() re-indexes numeric keys which breaks ACF group detection
                $current_options['acf'] = $acf_fields['acf'] + $current_options['acf'];
            }

            if (!empty($acf_fields['fields'])) {
                if (!isset($current_options['fields']) || !is_array($current_options['fields'])) {
                    $current_options['fields'] = array();
                }
                $current_options['fields'] = $this->array_merge_recursive_distinct(
                    $current_options['fields'],
                    $acf_fields['fields']
                );
            }

            if (!empty($acf_fields['is_multiple_field_value'])) {
                // Filter out orphaned mode selectors (those without corresponding value fields)
                $filtered_mode_selectors = $this->filter_orphaned_mode_selectors(
                    $acf_fields['is_multiple_field_value'],
                    $acf_fields['fields'] ?? array()
                );

                if (!empty($filtered_mode_selectors)) {
                    if (!isset($current_options['is_multiple_field_value']) || !is_array($current_options['is_multiple_field_value'])) {
                        $current_options['is_multiple_field_value'] = array();
                    }
                    $current_options['is_multiple_field_value'] = $this->array_merge_recursive_distinct(
                        $current_options['is_multiple_field_value'],
                        $filtered_mode_selectors
                    );
                }
            }

            if (!empty($acf_fields['multiple_value'])) {
                if (!isset($current_options['multiple_value']) || !is_array($current_options['multiple_value'])) {
                    $current_options['multiple_value'] = array();
                }
                $current_options['multiple_value'] = $this->array_merge_recursive_distinct(
                    $current_options['multiple_value'],
                    $acf_fields['multiple_value']
                );
            }

            // Validate the raw (non-ACF/non-addon) template keys against the keys WP
            // All Import core + active add-ons recognize, and warn on unknown ones —
            // mirroring set-options. This does NOT reject (the template is add-on
            // extensible) but stops a malformed write (e.g. a bogus "not_a_real_section"
            // section) from being merged in silently and living on invisibly.
            $template_warnings = $this->unknown_template_key_warnings($template, $current_options);

            // Merge remaining (non-ACF) template fields into current options
            foreach ($template as $key => $value) {
                // Strip [] suffix from array field names (e.g., attribute_name[] -> attribute_name)
                // The [] is just HTML form convention, not part of the actual option key
                $is_array_field = (substr($key, -2) === '[]');
                $clean_key = preg_replace('/\[\]$/', '', $key);

                // A null value is an explicit unset: remove the key from the stored
                // template rather than merging. This is the escape hatch for undoing a
                // bad write — set-mapping otherwise only ever merges, so an injected
                // key could not be removed by re-sending the template.
                if (null === $value) {
                    unset($current_options[$clean_key]);
                    continue;
                }

                // If the key ends with [], ensure the value is stored as an array
                // This is important for addon group selections (e.g., jetengine_groups[])
                if ($is_array_field && !is_array($value)) {
                    $value = !empty($value) ? array($value) : array();
                }

                $current_options[$clean_key] = $value;
            }

            // Ensure wizard_type is set (required by User Import Add-On and other add-ons)
            // Default to 'new' for creating new records
            if (!isset($current_options['wizard_type'])) {
                $current_options['wizard_type'] = 'new';
                WPAI_Bridge_Logger::debug('AI Bridge: Setting wizard_type = new (required for add-ons)');
            }

            // Auto-configure "Manage stock?" based on Stock Qty field
            // If Stock Qty is set and Manage Stock isn't already set to "xpath", set it to "yes"
            if (!empty($current_options['single_product_stock_qty']) &&
                $current_options['single_product_stock_qty'] !== '') {
                // Check if is_product_manage_stock is not already set to 'xpath'
                if (empty($current_options['is_product_manage_stock']) ||
                    $current_options['is_product_manage_stock'] !== 'xpath') {
                    $current_options['is_product_manage_stock'] = 'yes';
                    WPAI_Bridge_Logger::debug('AI Bridge: Auto-set is_product_manage_stock to "yes" because single_product_stock_qty is populated');
                }
            }

            // Fix JetEngine field names and enable groups if JetEngine is available
            // Use the addon API to check if JetEngine is available and get its field structure
            $jetengine_schema = $this->get_jetengine_schema_from_api($import);

            if ($jetengine_schema !== null) {
                WPAI_Bridge_Logger::debug('AI Bridge: JetEngine available via API, checking for fields to fix');
                $current_options = $this->fix_jetengine_field_names($current_options, $jetengine_schema);
            }

            // Fix update_addons type coercion for all addons
            // This ensures WP All Import receives the expected string types instead of booleans/arrays
            $current_options = $this->fix_update_addons_types($current_options);

            // Clean up custom fields to remove WooCommerce and system fields
            $current_options = $this->filter_custom_fields($current_options);

            // Ensure all add-on defaults are present before saving
            // This is critical for WooCommerce imports - the add-on expects many options to be set
            $current_options = $this->ensure_addon_defaults($current_options);

            // Update import
            $import->set(array('options' => $current_options))->update();

            // Remember that this import was configured by the AI flow, so the run
            // can be reported later. Recorded here because consent is a property of
            // the user doing the configuring, and a scheduled run has no user to
            // ask — checking it there would drop every scheduled import instead.
            if ( class_exists( 'WPAI_Bridge_Import_Outcome' ) ) {
                $session = get_option( 'wpai_llm_session_' . (int) $import_id );
                WPAI_Bridge_Import_Outcome::mark_configured(
                    $import_id,
                    isset( $params['log_session_id'] ) ? (string) $params['log_session_id'] : '',
                    is_array( $session ) && isset( $session['user_id'] ) ? (int) $session['user_id'] : 0,
                    // Minted by the service during this call, which is the only
                    // exchange it can authenticate. The run happens later with
                    // nothing left to prove who it is.
                    isset( $params['outcome_token'] ) ? (string) $params['outcome_token'] : ''
                );
            }

            // Use safe response to prevent any PHP warnings from corrupting JSON
            return $this->create_safe_rest_response(array(
                'success' => true,
                'message' => __('Template updated successfully.', 'wp_all_import_plugin'),
                'warnings' => $template_warnings,
            ), 200);

        } catch (Exception $e) {
            return new WP_Error(
                'rest_update_error',
                sprintf(__('Error updating template: %s', 'wp_all_import_plugin'), $e->getMessage()),
                array('status' => 500)
            );
        }
    }

    /**
     * Informational warnings for template keys set-mapping does not recognize.
     * A key is recognized when it is a WP All Import core/add-on option key
     * (get_core_option_keys), already present in the import's stored template,
     * bracket/nested notation (foo[bar], attr[]), or one of the structural
     * sections update_template handles specially (acf, fields, …). Everything
     * else is surfaced (not rejected) so a typo or bogus section is visible.
     *
     * @return string[]
     */
    private function unknown_template_key_warnings($template, $current_options) {
        if (!is_array($template) || empty($template)) {
            return array();
        }

        $recognized = $this->get_core_option_keys();
        $structural = array('acf', 'fields', 'is_multiple_field_value', 'multiple_value', 'wizard_type');
        $existing   = is_array($current_options) ? array_keys($current_options) : array();
        $known      = array_flip(array_merge($recognized, $structural, $existing));

        $warnings = array();
        foreach (array_keys($template) as $key) {
            $clean = preg_replace('/\[\]$/', '', (string) $key);
            // Bracket/nested notation targets a known section (e.g. acf[123][name]);
            // the base before the first '[' is what must be recognized.
            $base = (false !== strpos($clean, '[')) ? substr($clean, 0, strpos($clean, '[')) : $clean;
            if (isset($known[$clean]) || isset($known[$base]) || '' === $base) {
                continue;
            }
            $warnings[] = sprintf(
                /* translators: %s: template key */
                __('Template key "%s" is not a recognized WP All Import setting or field (it may belong to an add-on, or be a typo). It was saved as-is; send it as null to remove it.', 'wpai-ai-bridge-plugin'),
                $key
            );
        }
        return $warnings;
    }

    /**
     * Get JetEngine schema directly from addon API
     * Returns a schema structure with sections containing JetEngine fields
     * or null if JetEngine is not available
     */
    private function get_jetengine_schema_from_api($import) {
        // Check if addon API exists
        if (!class_exists('\Wpai\AddonAPI\PMXI_Addon_Manager')) {
            return null;
        }

        // Get registered addons
        $addons = \Wpai\AddonAPI\PMXI_Addon_Manager::get_addons();
        if (empty($addons)) {
            return null;
        }

        $import_type = $import->options['custom_type'] ?? 'post';
        $subtype = $import->options['taxonomy_type'] ?? null;
        $options = $import->options;

        // Find JetEngine addon
        foreach ($addons as $addon_id => $addon) {
            if (stripos($addon->name(), 'JetEngine') === false) {
                continue;
            }

            // Check if available for this import type
            if (!$addon->isAvailableForType($import_type, $options)) {
                continue;
            }

            WPAI_Bridge_Logger::debug('AI Bridge: Found JetEngine addon via API');

            try {
                $groups = $addon->groups($import_type, $subtype);
                $all_fields = $addon->fields($import_type, $subtype);

                if (empty($groups) && empty($all_fields)) {
                    return null;
                }

                $addon_slug = $addon->slug;
                $addon_fields = array();

                // Add group toggle checkboxes
                foreach ($groups as $group) {
                    // An ACF field group id is a post id, so that add-on hands us an
                    // int while Meta Box casts and JetEngine uses slugs. The schema
                    // field is an identifier, so normalise here rather than leaving
                    // every consumer to cope with whichever add-on is active.
                    $group_id = isset($group['id']) ? (string) $group['id'] : '';
                    $group_label = $group['label'] ?? $group_id;

                    if ('' === $group_id) {
                        continue;
                    }

                    $addon_fields[] = array(
                        'name' => $addon_slug . '_groups[]',
                        'label' => 'Enable Field Group: ' . $group_label,
                        'type' => 'checkbox',
                        'inputType' => 'checkbox',
                        'fieldType' => $addon_slug . '_group_toggle',
                        'groupId' => $group_id,
                        'value' => $group_id,
                        'description' => 'Check this to enable importing fields from the "' . $group_label . '" field group',
                    );
                }

                // Add actual fields
                foreach ($all_fields as $field) {
                    $field_key = $field['key'] ?? $field['name'] ?? '';
                    $field_label = $field['label'] ?? $field['title'] ?? $field_key;
                    $field_type = $field['type'] ?? 'text';
                    $field_group = isset($field['group']) ? (string) $field['group'] : '';

                    // Find group label. Both sides are normalised before comparing:
                    // a strict compare of an int id against a numeric-string group
                    // silently missed, leaving the raw id as the label.
                    $group_label = $field_group;
                    foreach ($groups as $group) {
                        if (isset($group['id']) && (string) $group['id'] === $field_group) {
                            $group_label = $group['label'] ?? $field_group;
                            break;
                        }
                    }

                    $addon_field = array(
                        'name' => $addon_slug . '[' . $field_key . ']',
                        'label' => $group_label ? "[{$group_label}] {$field_label}" : $field_label,
                        'type' => 'input',
                        'inputType' => $field_type,
                        'fieldType' => $addon_slug . '_field',
                        'groupName' => $group_label,
                    );

                    // groupId is typed as a string, so a field with no group omits the
                    // key rather than sending null — absent and null are not the same.
                    if ( '' !== $field_group ) {
                        $addon_field['groupId'] = $field_group;
                    }

                    $addon_fields[] = $addon_field;
                }

                // Return schema structure
                return array(
                    'sections' => array(
                        array(
                            'name' => $addon->name(),
                            'fields' => $addon_fields,
                        ),
                    ),
                );

            } catch (\Exception $e) {
                WPAI_Bridge_Logger::debug('AI Bridge: Error getting JetEngine fields from API: ' . $e->getMessage());
                return null;
            }
        }

        return null;
    }

    /**
     * Fix JetEngine field names - wrap fields in jetengine[] if they should be JetEngine fields
     * This handles cases where the LLM maps fields like "sku" instead of "jetengine[sku]"
     * Also ensures the appropriate JetEngine groups are enabled
     */
    private function fix_jetengine_field_names($options, $template) {
        // Find JetEngine section in template
        $jetengine_section = null;
        foreach ($template['sections'] as $section) {
            if (stripos($section['name'], 'JetEngine') !== false) {
                $jetengine_section = $section;
                break;
            }
        }

        if (!$jetengine_section) {
            WPAI_Bridge_Logger::debug('AI Bridge: JetEngine section not found in template');
            return $options;
        }

        // Build maps from template
        $field_to_group = array();  // field_key => groupId
        $label_to_id = array();      // groupName => groupId (for fixing labels that LLM might use)

        foreach ($jetengine_section['fields'] as $field) {
            // Extract field key from name like "jetengine[_sku]"
            if (preg_match('/jetengine\[([^\]]+)\]/', $field['name'], $matches)) {
                $field_key = $matches[1];
                if (!empty($field['groupId'])) {
                    $field_to_group[$field_key] = $field['groupId'];

                    // Also map label to ID so we can fix incorrect values
                    if (!empty($field['groupName'])) {
                        $label_to_id[$field['groupName']] = $field['groupId'];
                    }
                }
            }
            // Also check for group toggle checkboxes to build label_to_id map
            else if (isset($field['fieldType']) && $field['fieldType'] === 'jetengine_group_toggle') {
                if (!empty($field['value']) && !empty($field['label'])) {
                    // Extract group name from label like "Enable Field Group: Product (Post Type)"
                    if (preg_match('/Enable Field Group:\s*(.+)/', $field['label'], $label_matches)) {
                        $label_to_id[$label_matches[1]] = $field['value'];
                        WPAI_Bridge_Logger::debug("AI Bridge: Mapped JetEngine group label '{$label_matches[1]}' to ID '{$field['value']}'");
                    }
                }
            }
        }

        // Initialize jetengine_groups if not present
        if (!isset($options['jetengine_groups'])) {
            $options['jetengine_groups'] = array();
        }

        // Initialize jetengine array if not present
        if (!isset($options['jetengine'])) {
            $options['jetengine'] = array();
        }

        // CRITICAL FIX: Convert bracket-notation keys to nested arrays
        // The LLM might create 'jetengine[_sku]' as a top-level key (string with brackets)
        // But WP All Import expects $options['jetengine']['_sku'] (nested array)
        $keys_to_remove = array();
        foreach ($options as $key => $value) {
            // Check if this is a bracket-notation field like 'jetengine[_sku]'
            if (preg_match('/^jetengine\[([^\]]+)\]$/', $key, $matches)) {
                $field_key = $matches[1];
                WPAI_Bridge_Logger::debug("AI Bridge: Converting bracket-notation key '$key' to nested array jetengine['$field_key']");
                $options['jetengine'][$field_key] = $value;
                $keys_to_remove[] = $key;
            }
        }
        // Remove the bracket-notation keys
        foreach ($keys_to_remove as $key) {
            unset($options[$key]);
        }

        // Collect group IDs for fields we're about to enable
        $groups_to_enable = array();

        // Map of common field names to their JetEngine equivalents
        // The LLM might create "sku" but JetEngine expects "_sku"
        $potential_jetengine_fields = array(
            'sku' => '_sku',
            'regular_price' => '_regular_price',
            'sale_price' => '_sale_price',
            'price' => '_regular_price',
            'stock' => '_stock',
            'stock_qty' => '_stock',
            'weight' => '_weight',
            'length' => '_length',
            'width' => '_width',
            'height' => '_height',
            'description' => '_description',
            'short_description' => '_short_description',
        );

        // Check for top-level fields that should be moved to jetengine
        foreach ($potential_jetengine_fields as $top_level_name => $jetengine_field_name) {
            if (isset($options[$top_level_name]) && !empty($options[$top_level_name])) {
                // Move to jetengine array if not already there
                if (!isset($options['jetengine'][$jetengine_field_name])) {
                    WPAI_Bridge_Logger::debug("AI Bridge: Moving '$top_level_name' to 'jetengine[$jetengine_field_name]'");
                    $options['jetengine'][$jetengine_field_name] = $options[$top_level_name];
                    unset($options[$top_level_name]);

                    // Find which group this field belongs to
                    if (isset($field_to_group[$jetengine_field_name])) {
                        $groups_to_enable[] = $field_to_group[$jetengine_field_name];
                        WPAI_Bridge_Logger::debug("AI Bridge: Field '$jetengine_field_name' belongs to group '{$field_to_group[$jetengine_field_name]}'");
                    }
                }
            }
        }

        // Also check for fields already in jetengine array
        // They might have incorrect naming (without underscore) or just need their groups enabled
        $jetengine_fields_to_rename = array();
        foreach ($options['jetengine'] as $field_key => $field_value) {
            if (empty($field_value)) {
                continue;
            }

            // Check if this field exists in the template with this exact name
            if (isset($field_to_group[$field_key])) {
                $groups_to_enable[] = $field_to_group[$field_key];
                WPAI_Bridge_Logger::debug("AI Bridge: Existing field 'jetengine[$field_key]' belongs to group '{$field_to_group[$field_key]}'");
            }
            // Check if this field needs to be renamed (e.g., 'sku' -> '_sku')
            else if (isset($potential_jetengine_fields[$field_key])) {
                $correct_name = $potential_jetengine_fields[$field_key];
                if (isset($field_to_group[$correct_name])) {
                    WPAI_Bridge_Logger::debug("AI Bridge: Field 'jetengine[$field_key]' should be renamed to 'jetengine[$correct_name]'");
                    $jetengine_fields_to_rename[$field_key] = $correct_name;
                    $groups_to_enable[] = $field_to_group[$correct_name];
                }
            }
        }

        // Apply renames to jetengine fields
        foreach ($jetengine_fields_to_rename as $old_name => $new_name) {
            if (!isset($options['jetengine'][$new_name])) {
                $options['jetengine'][$new_name] = $options['jetengine'][$old_name];
            }
            unset($options['jetengine'][$old_name]);
        }

        // Build list of valid group IDs from the template
        $valid_group_ids = array();
        foreach ($jetengine_section['fields'] as $field) {
            if (isset($field['fieldType']) && $field['fieldType'] === 'jetengine_group_toggle') {
                if (!empty($field['value'])) {
                    $valid_group_ids[] = $field['value'];
                }
            }
        }
        WPAI_Bridge_Logger::debug("AI Bridge: Valid JetEngine group IDs: " . implode(', ', $valid_group_ids));

        // Fix any label values in jetengine_groups (convert labels to IDs and remove invalid ones)
        if (!empty($options['jetengine_groups']) && is_array($options['jetengine_groups'])) {
            $fixed_groups = array();
            foreach ($options['jetengine_groups'] as $group_value) {
                // Check if this is a label that needs to be converted to ID
                if (isset($label_to_id[$group_value])) {
                    $correct_id = $label_to_id[$group_value];
                    WPAI_Bridge_Logger::debug("AI Bridge: Converting JetEngine group label '$group_value' to ID '$correct_id'");
                    $fixed_groups[] = $correct_id;
                } elseif (in_array($group_value, $valid_group_ids)) {
                    // Valid group ID, keep it
                    $fixed_groups[] = $group_value;
                } else {
                    // Invalid group ID or unrecognized label, remove it
                    WPAI_Bridge_Logger::debug("AI Bridge: Removing invalid JetEngine group value '$group_value' (not a valid group ID)");
                }
            }
            $options['jetengine_groups'] = array_unique($fixed_groups);
        }

        // Add groups for any populated fields that aren't already enabled
        $groups_to_enable = array_unique($groups_to_enable);
        foreach ($groups_to_enable as $group_id) {
            if (!in_array($group_id, $options['jetengine_groups'])) {
                WPAI_Bridge_Logger::debug("AI Bridge: Enabling JetEngine group '$group_id'");
                $options['jetengine_groups'][] = $group_id;
            }
        }

        // Initialize update_addons structure for JetEngine if we have populated fields
        if (!empty($options['jetengine']) && count(array_filter($options['jetengine'])) > 0) {
            if (!isset($options['update_addons'])) {
                $options['update_addons'] = array();
            }
            if (!isset($options['update_addons']['jetengine'])) {
                WPAI_Bridge_Logger::debug("AI Bridge: Initializing update_addons structure for JetEngine");
                $options['update_addons']['jetengine'] = array(
                    'fields_list' => '0',
                    'is_update' => '1',
                    'update_logic' => 'full_update',
                    'fields_only_list' => '',
                    'fields_except_list' => '',
                );
            }
        }

        return $options;
    }

    /**
     * Fix update_addons type coercion for addon API addons
     * WP All Import expects strings for these fields, not booleans/arrays
     * This prevents UI rendering bugs caused by type mismatches
     * Only applies to addons that use the new addon API (JetEngine, MetaBox, etc.)
     * Does NOT apply to older addons like ACF which use different structures
     */
    private function fix_update_addons_types($options) {
        if (!isset($options['update_addons']) || !is_array($options['update_addons'])) {
            return $options;
        }

        // Get list of addon API addons dynamically if available
        $addon_api_slugs = array();
        if (class_exists('\Wpai\AddonAPI\PMXI_Addon_Manager')) {
            $addons = \Wpai\AddonAPI\PMXI_Addon_Manager::get_addons();
            foreach ($addons as $addon) {
                $addon_api_slugs[] = $addon->slug;
            }
            WPAI_Bridge_Logger::debug('AI Bridge: Found addon API addons: ' . implode(', ', $addon_api_slugs));
        }

        // If no addons found via API, use known addon API slugs as fallback
        if (empty($addon_api_slugs)) {
            $addon_api_slugs = array('jetengine', 'metabox', 'metaboxio', 'pods');
            WPAI_Bridge_Logger::debug('AI Bridge: Using fallback addon API slugs: ' . implode(', ', $addon_api_slugs));
        }

        foreach ($options['update_addons'] as $addon_slug => &$addon_update) {
            if (!is_array($addon_update)) {
                continue;
            }

            // Only apply fix to addon API addons, not older addons like ACF
            if (!in_array($addon_slug, $addon_api_slugs)) {
                WPAI_Bridge_Logger::debug("AI Bridge: Skipping update_addons type fix for '{$addon_slug}' (not an addon API addon)");
                continue;
            }

            // Convert is_update from boolean to string
            if (isset($addon_update['is_update'])) {
                if (is_bool($addon_update['is_update'])) {
                    $original = $addon_update['is_update'] ? 'true' : 'false';
                    $addon_update['is_update'] = $addon_update['is_update'] ? '1' : '0';
                    WPAI_Bridge_Logger::debug("AI Bridge: Converted update_addons.{$addon_slug}.is_update from boolean {$original} to string '{$addon_update['is_update']}'");
                }
            }

            // Convert fields_list from array to string
            if (isset($addon_update['fields_list'])) {
                if (is_array($addon_update['fields_list'])) {
                    $addon_update['fields_list'] = empty($addon_update['fields_list']) ? '0' : '1';
                    WPAI_Bridge_Logger::debug("AI Bridge: Converted update_addons.{$addon_slug}.fields_list from array to string '{$addon_update['fields_list']}'");
                }
            }

            // Convert fields_only_list from array to string
            if (isset($addon_update['fields_only_list'])) {
                if (is_array($addon_update['fields_only_list'])) {
                    $addon_update['fields_only_list'] = empty($addon_update['fields_only_list']) ? '' : implode(',', $addon_update['fields_only_list']);
                    WPAI_Bridge_Logger::debug("AI Bridge: Converted update_addons.{$addon_slug}.fields_only_list from array to string");
                }
            }

            // Convert fields_except_list from array to string
            if (isset($addon_update['fields_except_list'])) {
                if (is_array($addon_update['fields_except_list'])) {
                    $addon_update['fields_except_list'] = empty($addon_update['fields_except_list']) ? '' : implode(',', $addon_update['fields_except_list']);
                    WPAI_Bridge_Logger::debug("AI Bridge: Converted update_addons.{$addon_slug}.fields_except_list from array to string");
                }
            }
        }

        return $options;
    }

    /**
     * Evaluate XPath expression
     */
    public function evaluate_xpath($request) {
        $params = $request->get_json_params();

        // Name only the parameter that is actually missing, instead of blaming both
        // (an agent that supplied a valid import_id but an empty xpath was told the
        // "Import ID ... is required" too, and chased the wrong problem).
        $missing_id    = empty($params['import_id']);
        $missing_xpath = ! isset($params['xpath']) || '' === trim((string) $params['xpath']);
        if ($missing_id || $missing_xpath) {
            if ($missing_id && $missing_xpath) {
                $message = __('Import ID and XPath expression are required.', 'wp_all_import_plugin');
            } elseif ($missing_id) {
                $message = __('Import ID is required.', 'wp_all_import_plugin');
            } else {
                $message = __('XPath expression is required.', 'wp_all_import_plugin');
            }
            return new WP_Error('rest_invalid_param', $message, array('status' => 400));
        }

        $import_id = $params['import_id'];
        $xpath_expr = $params['xpath'];

        // This evaluates a WP All Import *template expression*, not raw XPath: a real
        // reference must be wrapped in {…} (e.g. "{price[1]}"). A bare path with no {}
        // is treated as a constant and echoed back literally, which looks like a match
        // but isn't one -- flag that so the caller isn't misled.
        $has_placeholder = ( false !== strpos( (string) $xpath_expr, '{' ) && false !== strpos( (string) $xpath_expr, '}' ) );

        // An unbalanced brace (e.g. "{sku[1]") otherwise reaches the XPath engine and
        // surfaces as a raw parser error ("Unexpected end of XPath expression 'sku[1]'"),
        // which sends the agent chasing an xpath-syntax problem rather than the real
        // one: the {…} placeholder was never closed. Name that directly.
        if ( substr_count( (string) $xpath_expr, '{' ) !== substr_count( (string) $xpath_expr, '}' ) ) {
            return new WP_Error(
                'rest_invalid_param',
                sprintf(
                    /* translators: %s: the expression as given */
                    __('The expression "%s" has an unbalanced brace — every {…} field placeholder must be closed. Example: "{sku[1]}".', 'wp_all_import_plugin'),
                    (string) $xpath_expr
                ),
                array('status' => 400)
            );
        }

        // Load import
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error(
                'rest_not_found',
                __('Import not found.', 'wp_all_import_plugin'),
                array('status' => 404)
            );
        }

        // Get file path (handles preview imports and file history)
        $file_path = $this->get_import_file_path($import);
        if (is_wp_error($file_path)) {
            return $file_path;
        }

        try {
            // Get encoding with safety check
            $encoding = ! empty( $import->options['encoding'] ) ? $import->options['encoding'] : 'UTF-8';

            // Use PMXI_Chunk to read records from the file
            $chunk = new PMXI_Chunk($file_path, array(
                'element' => $import->root_element,
                'encoding' => $encoding,
            ));

            $limit = !empty($params['limit']) ? intval($params['limit']) : 5;
            $results = array();
            $record_index = 0;

            // Read records from chunks
            while ($xml_chunk = $chunk->read()) {
                if (!empty($xml_chunk)) {
                    $xml = "<?xml version=\"1.0\" encoding=\"" . $encoding . "\"?>\n" . $xml_chunk;
                    $dom = new DOMDocument('1.0', $encoding);
                    $old = libxml_use_internal_errors(true);
                    $dom->loadXML($xml);
                    libxml_use_internal_errors($old);
                    $xpath_obj = new DOMXPath($dom);

                    // Get records using the import's xpath
                    if (($elements = @$xpath_obj->query($import->xpath)) and $elements->length) {
                        foreach ($elements as $element) {
                            if ($record_index >= $limit) {
                                break 2; // Break out of both loops
                            }

                            // Create a parser for this record with the XPath expression as template
                            $record_xml = $dom->saveXML($element);
                            $file = '';
                            $parser = XmlImportParser::factory(
                                $record_xml,
                                '/*', // Root element is the record itself
                                $xpath_expr, // Use the XPath expression as the template
                                $file
                            );

                            // Parse to evaluate the XPath
                            $parsed = $parser->parse();
                            $value = !empty($parsed[0]) ? $parsed[0] : '';

                            // For a bracket-less literal (no {…}), echo the input faithfully.
                            // The template parser normalizes a bare "sku[1]" to "sku1" (it strips
                            // the [1] index it only honors inside a placeholder), so the echoed
                            // "value" wasn't the constant that would actually be written. A literal
                            // is a constant: the faithful echo is the input string itself.
                            if ( ! $has_placeholder ) {
                                $value = (string) $xpath_expr;
                            }

                            $results[] = array(
                                'record_index' => $record_index,
                                'value' => $value,
                                'is_literal' => ! $has_placeholder,
                            );

                            $record_index++;
                        }
                    }
                }
            }

            if (empty($results)) {
                return new WP_REST_Response(array(
                    'success' => true,
                    'results' => array(),
                    'message' => __('No records found in file.', 'wp_all_import_plugin'),
                ), 200);
            }

            $response = array(
                'success' => true,
                'xpath' => $xpath_expr,
                'results' => $results,
                'count' => count($results),
            );
            if ( ! $has_placeholder ) {
                $response['note'] = __( 'The expression has no {…} placeholder, so it was evaluated as a literal constant and echoed back — it is not a match against your data. Wrap the field in braces (e.g. "{column[1]}") to read a value.', 'wp_all_import_plugin' );
            }

            return new WP_REST_Response($response, 200);

        } catch (\Throwable $e) {
            return new WP_Error(
                'rest_evaluation_error',
                sprintf(__('Could not evaluate the expression "%1$s": %2$s', 'wp_all_import_plugin'), (string) $xpath_expr, $e->getMessage()),
                array('status' => 500)
            );
        }
    }

    /**
     * Sidecar option key holding the UNFILTERED base xpath captured the first
     * time filters are set on an import. Re-setting filters always composes
     * `base + predicate` from this stored value so predicates never stack.
     */
    const FILTER_BASE_OPTION_KEY = '_wpai_mcp_filter_base';

    /** Sidecar holding the last rule list passed to set_filters(), for get_filters() round-trip. */
    const FILTER_RULES_OPTION_KEY = '_wpai_mcp_filter_rules';

    /**
     * Compile a structured filter-rule list (WPAI_Bridge_Filter_Compiler's
     * rule model) into an xpath predicate and write it onto the import's
     * `xpath` column, composed against the captured unfiltered base so
     * re-setting filters is idempotent (never stacks predicates).
     */
    public function set_filters($request) {
        $import_id = $request->get_param('id');
        $params = $request->get_json_params();
        $rules = isset($params['rules']) && is_array($params['rules']) ? $params['rules'] : array();

        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error(
                'rest_not_found',
                __('Import not found.', 'wp_all_import_plugin'),
                array('status' => 404)
            );
        }

        if (!class_exists('WPAI_Bridge_Filter_Compiler')) {
            return new WP_Error(
                'rest_dependency_missing',
                __('Filter compiler is not available.', 'wp_all_import_plugin'),
                array('status' => 500)
            );
        }

        try {
            $options = (array) $import->options;

            // Capture the UNFILTERED base xpath the first time filters are set,
            // so subsequent set_filters() calls compose against it instead of
            // stacking predicates onto whatever xpath currently happens to be set.
            if (!isset($options[self::FILTER_BASE_OPTION_KEY]) || '' === $options[self::FILTER_BASE_OPTION_KEY]) {
                $options[self::FILTER_BASE_OPTION_KEY] = $import->xpath;
            }
            $base = $options[self::FILTER_BASE_OPTION_KEY];

            $compiled = WPAI_Bridge_Filter_Compiler::compile($rules);
            if (isset($compiled['error'])) {
                return new WP_Error(
                    'rest_invalid_filter',
                    $compiled['error'],
                    array('status' => 400, 'invalid_operators' => $compiled['invalid_operators'] ?? array())
                );
            }
            $predicate = $compiled['predicate'];
            $final_xpath = ('' !== $predicate) ? ($base . '[' . $predicate . ']') : $base;

            $options['filters_output'] = $compiled['filters_output'];
            $options[self::FILTER_RULES_OPTION_KEY] = $rules;

            $import->set(array(
                'xpath' => $final_xpath,
                'options' => $options,
            ))->update();

            // xpath changed — rebuild the cached file structure the same way
            // update_import() does on xpath change, so downstream previews reflect
            // the (possibly now-filtered) record set.
            $structure_cache = WPAI_Bridge_File_Structure_Cache::getInstance();
            $structure_cache->clear_cached_structure($import_id);

            $import->getById($import_id);
            $file_path = $this->get_import_file_path($import);
            if (!is_wp_error($file_path) && file_exists($file_path)) {
                $meta = $this->parse_file_metadata($file_path, $import);
                $meta['trigger'] = 'set_filters_reparse';
                $this->cache_file_meta($import_id, $file_path, $meta);
                $import->set(array('count' => $meta['total_records']))->update();
            }

            return new WP_REST_Response(array(
                'success' => true,
                'xpath' => $final_xpath,
                'filters_output_set' => true,
            ), 200);

        } catch (Exception $e) {
            return new WP_Error(
                'rest_update_error',
                sprintf(__('Error setting filters: %s', 'wp_all_import_plugin'), $e->getMessage()),
                array('status' => 500)
            );
        }
    }

    /**
     * Compile a structured filter-rule list and count how many records in the
     * import's source file the resulting xpath (base + predicate) would match,
     * WITHOUT persisting anything — used by the MCP layer to preview a filter's
     * effect before committing to it.
     */
    public function preview_filters($request) {
        $import_id = $request->get_param('id');
        $params = $request->get_json_params();
        $rules = isset($params['rules']) && is_array($params['rules']) ? $params['rules'] : array();

        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error(
                'rest_not_found',
                __('Import not found.', 'wp_all_import_plugin'),
                array('status' => 404)
            );
        }

        if (!class_exists('WPAI_Bridge_Filter_Compiler')) {
            return new WP_Error(
                'rest_dependency_missing',
                __('Filter compiler is not available.', 'wp_all_import_plugin'),
                array('status' => 500)
            );
        }

        $file_path = $this->get_import_file_path($import);
        if (is_wp_error($file_path)) {
            return $file_path;
        }

        $options = (array) $import->options;
        $base = isset($options[self::FILTER_BASE_OPTION_KEY]) && '' !== $options[self::FILTER_BASE_OPTION_KEY]
            ? $options[self::FILTER_BASE_OPTION_KEY]
            : $import->xpath;

        $compiled = WPAI_Bridge_Filter_Compiler::compile($rules);
        if (isset($compiled['error'])) {
            return new WP_Error(
                'rest_invalid_filter',
                $compiled['error'],
                array('status' => 400, 'invalid_operators' => $compiled['invalid_operators'] ?? array())
            );
        }
        $predicate = $compiled['predicate'];
        $final_xpath = ('' !== $predicate) ? ($base . '[' . $predicate . ']') : $base;

        try {
            $encoding = !empty($import->options['encoding']) ? $import->options['encoding'] : 'UTF-8';

            $chunk = new PMXI_Chunk($file_path, array(
                'element' => $import->root_element,
                'encoding' => $encoding,
            ));

            $record_cap = 5000;
            $matched = 0;
            $scanned = 0;
            $capped = false;

            while ($xml_chunk = $chunk->read()) {
                if ($scanned >= $record_cap) {
                    $capped = true;
                    break;
                }

                if (!empty($xml_chunk)) {
                    $xml = "<?xml version=\"1.0\" encoding=\"" . $encoding . "\"?>\n" . $xml_chunk;
                    $dom = new DOMDocument('1.0', $encoding);
                    $old = libxml_use_internal_errors(true);
                    $dom->loadXML($xml);
                    libxml_use_internal_errors($old);
                    $xpath_obj = new DOMXPath($dom);

                    $scanned++;

                    if (($elements = @$xpath_obj->query($final_xpath)) && $elements->length) {
                        $matched++;
                    }
                }
            }

            return new WP_REST_Response(array(
                'success' => true,
                'matched' => $matched,
                'scanned' => $scanned,
                'capped' => $capped,
            ), 200);

        } catch (Exception $e) {
            return new WP_Error(
                'rest_evaluation_error',
                sprintf(__('Error previewing filters: %s', 'wp_all_import_plugin'), $e->getMessage()),
                array('status' => 500)
            );
        }
    }

    /**
     * Get available post types
     */
    public function get_post_types($request) {
        // Get all public post types
        $post_types = get_post_types(array('public' => true), 'objects');

        $formatted_types = array();
        foreach ($post_types as $type_name => $type_obj) {
            // Get supported features as an array of feature names
            $supports = get_all_post_type_supports($type_name);
            $support_features = is_array($supports) ? array_keys($supports) : array();

            $formatted_types[] = array(
                'name' => $type_name,
                'label' => $type_obj->label,
                'singular_label' => $type_obj->labels->singular_name,
                'hierarchical' => $type_obj->hierarchical,
                'supports' => $support_features,
            );
        }

        return new WP_REST_Response(array(
            'success' => true,
            'post_types' => $formatted_types,
        ), 200);
    }

    /**
     * Get taxonomies for a post type
     */
    public function get_taxonomies($request) {
        $post_type = $request->get_param('post_type');

        if (empty($post_type)) {
            // Get all taxonomies
            $taxonomies = get_taxonomies(array('public' => true), 'objects');
        } else {
            // Get taxonomies for specific post type
            $taxonomies = get_object_taxonomies($post_type, 'objects');
        }

        $formatted_taxonomies = array();
        foreach ($taxonomies as $tax_name => $tax_obj) {
            $formatted_taxonomies[] = array(
                'name' => $tax_name,
                'label' => $tax_obj->label,
                'singular_label' => $tax_obj->labels->singular_name,
                'hierarchical' => $tax_obj->hierarchical,
                'post_types' => $tax_obj->object_type,
            );
        }

        return new WP_REST_Response(array(
            'success' => true,
            'taxonomies' => $formatted_taxonomies,
        ), 200);
    }

    /**
     * Get default import options
     */
    public function get_default_options($request) {
        $import_type = $request->get_param('import_type');

        // Get default options from config
        $default_options = PMXI_Config::createFromFile( PMXI_Plugin::ROOT_DIR . '/config/options.php' )->toArray();

        // Add import type specific defaults
        $type_specific = array(
            'import_type' => $import_type,
            'custom_type' => $import_type === 'post' ? 'post' : $import_type,
        );

        $options = array_merge($default_options, $type_specific);

        return new WP_REST_Response(array(
            'success' => true,
            'options' => self::strip_sensitive_options($options),
        ), 200);
    }

    /**
     * Get available template fields for an import type
     *
     * This endpoint renders the Step 3 template page and parses all form fields
     * to provide a complete list of available fields for the LLM to map to.
     *
     * Results are cached based on import type, active plugins, and active theme.
     */
    public function get_template_fields($request) {
        $import_type = $request->get_param('import_type');
        $force_refresh = $request->get_param('force_refresh');

        // Generate cache key based on import type, plugins, and theme
        $cache_key = $this->get_template_fields_cache_key($import_type);

        // Check cache unless force refresh
        if (!$force_refresh) {
            $cached = get_transient($cache_key);
            if ($cached !== false) {
                return new WP_REST_Response(array(
                    'success' => true,
                    'fields' => $cached,
                    'cached' => true,
                    'cache_key' => $cache_key,
                ), 200);
            }
        }

        try {
            // Render template and extract fields
            $fields = $this->render_and_extract_template_fields($import_type);

            // Cache for 24 hours
            set_transient($cache_key, $fields, DAY_IN_SECONDS);

            return new WP_REST_Response(array(
                'success' => true,
                'fields' => $fields,
                'cached' => false,
                'cache_key' => $cache_key,
            ), 200);

        } catch (\Throwable $e) {
            // The Step-3 template scrape requires WP All Import's admin controller as its
            // render context (it calls $this->error()/warning()/tag() and includes partials),
            // which isn't available here. Fail cleanly and point callers at the structured
            // discovery path rather than surfacing a raw PHP error.
            return new WP_Error(
                'rest_template_fields_error',
                __( 'Target fields could not be extracted in this context. Create the import first, then call get-typed-fields (or get-template-schema) for structured field discovery.', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 500, 'detail' => $e->getMessage() )
            );
        }
    }

    /**
     * Generate cache key for template fields based on import type, plugins, and theme
     */
    private function get_template_fields_cache_key($import_type) {
        // Get active plugins
        $active_plugins = get_option('active_plugins', array());
        sort($active_plugins); // Ensure consistent order
        $plugins_hash = md5(serialize($active_plugins));

        // Get active theme
        $theme = wp_get_theme();
        $theme_hash = md5($theme->get_stylesheet() . $theme->get('Version'));

        // Combine into cache key
        return 'wpai_template_fields_' . $import_type . '_' . $plugins_hash . '_' . $theme_hash;
    }

    /**
     * Render template page and extract all form fields
     */
    private function render_and_extract_template_fields($import_type) {
        // Prepare post data with defaults
        $default_options = PMXI_Config::createFromFile( PMXI_Plugin::ROOT_DIR . '/config/options.php' )->toArray();

        // Add WooCommerce defaults if applicable
        if (in_array($import_type, array('product', 'shop_order')) && class_exists('PMWI_Plugin')) {
            $woo_defaults = PMWI_Plugin::get_default_import_options();
            $default_options = array_merge($default_options, $woo_defaults);
        }

        $post = $default_options;
        $post['custom_type'] = $import_type;

        // Start output buffering. The finally guarantees the buffer is always reclaimed
        // even if a rendered template/controller throws — otherwise the partial HTML would
        // auto-flush at shutdown and corrupt the JSON response (e.g. the MCP transport).
        ob_start();
        try {
            // Render Step 3 template sections
            $this->render_template_sections($import_type, $post);
            $html = ob_get_clean();
        } catch ( \Throwable $e ) {
            ob_end_clean();
            throw $e;
        }

        // Parse HTML to extract form fields
        $fields = $this->parse_form_fields_from_html($html, $import_type);

        return $fields;
    }

    /**
     * Render all template sections for an import type
     */
    private function render_template_sections($import_type, $post) {
        // Render core template page
        $pmxi_root = defined( 'PMXI_ROOT_DIR' ) ? PMXI_ROOT_DIR : ( class_exists( 'PMXI_Plugin' ) ? PMXI_Plugin::ROOT_DIR : WP_PLUGIN_DIR . '/wp-all-import-pro' );
        $template_path = $pmxi_root . '/views/admin/import/template.php';
        if (file_exists($template_path)) {
            // Set up variables that template expects
            $isWizard = true;
            $this_obj = new stdClass();
            $this_obj->isWizard = true;
            $this_obj->isTemplateEdit = false;
            $this_obj->errors = new WP_Error();
            $this_obj->warnings = new WP_Error();

            // Include template
            include($template_path);
        }

        // Render add-on specific sections
        if ($import_type === 'product' && class_exists('PMWI_Admin_Import')) {
            $controller = new PMWI_Admin_Import();
            $controller->index($post);
        } elseif ($import_type === 'shop_order' && class_exists('PMWI_Admin_Import')) {
            $controller = new PMWI_Admin_Import();
            $controller->index($post);
        }

        // Trigger WordPress actions that add-ons hook into
        do_action('pmxi_template_header', true, $post);
        do_action('pmxi_extend_options_main', $import_type, $post);
        do_action('pmxi_extend_options_featured', $import_type, $post);

        // Render Step 4 options sections (contains many important fields)
        if ($import_type === 'product' && class_exists('PMWI_Admin_Import')) {
            $controller = new PMWI_Admin_Import();
            $controller->options(true, $post);
        } elseif ($import_type === 'shop_order' && class_exists('PMWI_Admin_Import')) {
            $controller = new PMWI_Admin_Import();
            $controller->options(true, $post);
        }
    }

    /**
     * Parse HTML to extract all form field names and metadata
     */
    private function parse_form_fields_from_html($html, $import_type) {
        $fields = array();

        // Use DOMDocument to parse HTML
        $dom = new DOMDocument();
        // Suppress warnings from malformed HTML
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        // Extract input fields
        $inputs = $dom->getElementsByTagName('input');
        foreach ($inputs as $input) {
            $name = $input->getAttribute('name');
            $type = $input->getAttribute('type');

            if (empty($name) || $type === 'submit' || $type === 'button') {
                continue;
            }

            $field_info = array(
                'name' => $name,
                'type' => $this->normalize_field_type($type),
                'default' => $input->getAttribute('value'),
                'category' => $this->categorize_field_name($name, $import_type),
                'label' => $this->extract_field_label($input, $dom),
            );

            $fields[$name] = $field_info;
        }

        // Extract textarea fields
        $textareas = $dom->getElementsByTagName('textarea');
        foreach ($textareas as $textarea) {
            $name = $textarea->getAttribute('name');

            if (empty($name)) {
                continue;
            }

            $field_info = array(
                'name' => $name,
                'type' => 'textarea',
                'default' => $textarea->nodeValue,
                'category' => $this->categorize_field_name($name, $import_type),
                'label' => $this->extract_field_label($textarea, $dom),
            );

            $fields[$name] = $field_info;
        }

        // Extract select fields
        $selects = $dom->getElementsByTagName('select');
        foreach ($selects as $select) {
            $name = $select->getAttribute('name');

            if (empty($name)) {
                continue;
            }

            // Get options
            $options = array();
            $option_elements = $select->getElementsByTagName('option');
            foreach ($option_elements as $option) {
                $options[] = array(
                    'value' => $option->getAttribute('value'),
                    'label' => $option->nodeValue,
                );
            }

            $field_info = array(
                'name' => $name,
                'type' => 'select',
                'options' => $options,
                'category' => $this->categorize_field_name($name, $import_type),
                'label' => $this->extract_field_label($select, $dom),
            );

            $fields[$name] = $field_info;
        }

        // Add metadata about field importance
        foreach ($fields as $name => &$field) {
            $field['required'] = $this->is_required_field($name, $import_type);
            $field['description'] = $this->get_field_description($name, $import_type);
        }

        // Enrich ACF fields with labels and metadata from ACF itself
        $fields = $this->enrich_acf_field_metadata($fields);

        return array_values($fields); // Return as indexed array
    }

    /**
     * Normalize field type
     */
    private function normalize_field_type($type) {
        $type_map = array(
            'text' => 'text',
            'hidden' => 'hidden',
            'checkbox' => 'boolean',
            'radio' => 'choice',
            'number' => 'number',
            'email' => 'email',
            'url' => 'url',
            'date' => 'date',
        );

        return isset($type_map[$type]) ? $type_map[$type] : 'text';
    }

    /**
     * Categorize field by name pattern
     */
    private function categorize_field_name($name, $import_type) {
        // Core WordPress fields
        if (in_array($name, array('title', 'content', 'post_excerpt', 'excerpt'))) {
            return 'core';
        }

        if (in_array($name, array('slug', 'author', 'date', 'status', 'comment_status'))) {
            return 'core';
        }

        // WooCommerce Product fields
        if ($import_type === 'product') {
            if (strpos($name, 'single_product_sku') !== false || strpos($name, '_sku') !== false) {
                return 'inventory';
            }
            if (strpos($name, 'price') !== false) {
                return 'pricing';
            }
            if (strpos($name, 'stock') !== false || strpos($name, 'manage_stock') !== false) {
                return 'inventory';
            }
            if (strpos($name, 'weight') !== false || strpos($name, 'length') !== false ||
                strpos($name, 'width') !== false || strpos($name, 'height') !== false) {
                return 'shipping';
            }
            if (strpos($name, 'attribute') !== false) {
                return 'attributes';
            }
            if (strpos($name, 'variation') !== false) {
                return 'variations';
            }
            if (strpos($name, 'product_type') !== false || strpos($name, 'is_multiple_product_type') !== false ||
                strpos($name, 'multiple_product_type') !== false) {
                return 'general';
            }
            if (strpos($name, 'matching_parent') !== false || strpos($name, 'parent') !== false) {
                return 'general';
            }
        }

        // WooCommerce Order fields
        if ($import_type === 'shop_order') {
            if (strpos($name, 'billing') !== false) {
                return 'billing';
            }
            if (strpos($name, 'shipping') !== false) {
                return 'shipping';
            }
            if (strpos($name, 'order_') !== false) {
                return 'order';
            }
        }

        // Update settings
        if (strpos($name, 'is_update_') !== false) {
            return 'update_settings';
        }

        // Matching/unique identifier
        if (strpos($name, 'unique') !== false || strpos($name, 'duplicate') !== false) {
            return 'matching';
        }

        // Custom fields
        if (strpos($name, 'custom_field') !== false || strpos($name, 'serialized_custom_fields') !== false) {
            return 'custom_fields';
        }

        // Taxonomies
        if (strpos($name, 'taxonom') !== false || strpos($name, 'categor') !== false || strpos($name, 'tag') !== false) {
            return 'taxonomies';
        }

        // Images
        if (strpos($name, 'image') !== false || strpos($name, 'featured') !== false || strpos($name, 'attachment') !== false) {
            return 'media';
        }

        return 'other';
    }

    /**
     * Check if field is required
     */
    private function is_required_field($name, $import_type) {
        // Core required fields
        $required_core = array('title', 'custom_type');

        // Product required fields
        $required_product = array('title', 'custom_type', 'is_multiple_product_type', 'multiple_product_type');

        // Order required fields
        $required_order = array('custom_type');

        if ($import_type === 'product') {
            return in_array($name, $required_product);
        } elseif ($import_type === 'shop_order') {
            return in_array($name, $required_order);
        }

        return in_array($name, $required_core);
    }

    /**
     * Get field description
     */
    private function get_field_description($name, $import_type) {
        $descriptions = array(
            // Core fields
            'title' => 'The title/name of the imported item',
            'content' => 'The main content/description',
            'post_excerpt' => 'Short excerpt or summary',
            'excerpt' => 'Short excerpt or summary',
            'slug' => 'URL-friendly permalink slug',
            'author' => 'Author ID or username',
            'date' => 'Publication date',
            'status' => 'Publication status (publish, draft, pending)',

            // Product fields
            'single_product_sku' => 'Product SKU (Stock Keeping Unit) - unique identifier',
            'single_product_regular_price' => 'Regular price of the product',
            'single_product_sale_price' => 'Sale price of the product',
            'single_product_stock_qty' => 'Stock quantity',
            'single_product_weight' => 'Product weight',
            'single_product_length' => 'Product length dimension',
            'single_product_width' => 'Product width dimension',
            'single_product_height' => 'Product height dimension',
            'is_product_manage_stock' => 'Whether to enable stock management',
            'product_stock_status' => 'Stock status (instock, outofstock, onbackorder)',
            'multiple_product_type' => 'Product type (simple, variable, grouped, external)',

            // Matching fields
            'unique_key' => 'Unique identifier for matching existing records',
            'duplicate_indicator' => 'How to identify duplicate records',
            'is_update_previous' => 'Whether to update existing records',
            'create_new_records' => 'Whether to create new records',
        );

        return isset($descriptions[$name]) ? $descriptions[$name] : '';
    }

    /**
     * Extract label for a form field from surrounding HTML
     */
    private function extract_field_label($element, $dom) {
        // Try to find associated label
        $id = $element->getAttribute('id');
        if (!empty($id)) {
            $labels = $dom->getElementsByTagName('label');
            foreach ($labels as $label) {
                if ($label->getAttribute('for') === $id) {
                    return trim($label->nodeValue);
                }
            }
        }

        // Try to find parent label
        $parent = $element->parentNode;
        while ($parent && $parent->nodeName !== 'body') {
            if ($parent->nodeName === 'label') {
                return trim($parent->nodeValue);
            }
            $parent = $parent->parentNode;
        }

        return '';
    }

    /**
     * Enrich ACF fields with labels and metadata from ACF itself
     */
    private function enrich_acf_field_metadata($fields) {
        // Check if ACF is available
        if (!class_exists('ACF') || !function_exists('acf_get_field_groups')) {
            return $fields;
        }

        // Build a map of field keys to ACF field objects
        $acf_field_map = array();

        // Get all ACF field groups
        $field_groups = acf_get_field_groups();

        if (empty($field_groups)) {
            return $fields;
        }

        foreach ($field_groups as $group) {
            // Get fields for this group
            $group_fields = acf_get_fields($group);

            if (empty($group_fields)) {
                continue;
            }

            // Recursively collect all fields including sub-fields
            $this->collect_acf_fields_recursive($group_fields, $acf_field_map);
        }

        // Now enrich the fields array with ACF metadata
        foreach ($fields as $field_name => &$field_info) {
            // Check if this is an ACF field (fields[field_key])
            if (preg_match('/^fields\[([^\]]+)\]$/', $field_name, $matches)) {
                $field_key = $matches[1];

                if (isset($acf_field_map[$field_key])) {
                    $acf_field = $acf_field_map[$field_key];

                    // Add ACF metadata
                    $field_info['acf_label'] = $acf_field['label'];
                    $field_info['acf_name'] = $acf_field['name'];
                    $field_info['acf_type'] = $acf_field['type'];
                    $field_info['acf_key'] = $field_key;

                    // Update the generic label if it wasn't found via HTML parsing
                    if (empty($field_info['label'])) {
                        $field_info['label'] = $acf_field['label'];
                    }

                    // Update category to indicate this is an ACF field
                    $field_info['category'] = 'acf';

                    // Add instructions if available
                    if (!empty($acf_field['instructions'])) {
                        $field_info['description'] = $acf_field['instructions'];
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * Recursively collect ACF fields including sub-fields from repeater, group, flexible content
     * Tracks the group title for each field
     */
    private function collect_acf_fields_recursive($fields, &$field_map, $group_title = '') {
        foreach ($fields as $field) {
            // Add this field to the map with group info
            if (!empty($field['key'])) {
                $field['_group_title'] = $group_title;
                $field_map[$field['key']] = $field;
            }

            // Check for sub-fields (repeater, group, flexible content, clone)
            if (isset($field['sub_fields']) && !empty($field['sub_fields'])) {
                $this->collect_acf_fields_recursive($field['sub_fields'], $field_map, $group_title);
            }

            // Flexible content has layouts with sub-fields
            if (isset($field['layouts']) && !empty($field['layouts'])) {
                foreach ($field['layouts'] as $layout) {
                    if (isset($layout['sub_fields']) && !empty($layout['sub_fields'])) {
                        $this->collect_acf_fields_recursive($layout['sub_fields'], $field_map, $group_title);
                    }
                }
            }
        }
    }

    /**
     * Build a simple map of ACF field keys to their labels for display
     * Uses proper ACF API functions instead of direct database queries
     * Includes group title for each field to enable grouping in UI
     */
    private function build_acf_field_metadata_map() {
        $metadata = array();

        // Check if ACF is available with required functions
        if (!class_exists('ACF') || !function_exists('acf_get_raw_field_groups') || !function_exists('acf_get_fields')) {
            return $metadata;
        }

        // Use acf_get_raw_field_groups() to get ALL field groups without location filtering
        $field_groups = acf_get_raw_field_groups();

        if (empty($field_groups)) {
            return $metadata;
        }

        // Build a map of all fields from all groups
        $acf_field_map = array();
        foreach ($field_groups as $group) {
            // Get fields for this group using ACF's API
            $group_fields = acf_get_fields($group);

            if (empty($group_fields)) {
                continue;
            }

            // Get group title for display
            $group_title = isset($group['title']) ? $group['title'] : '';

            // Recursively collect all fields including sub-fields, passing group title
            $this->collect_acf_fields_recursive($group_fields, $acf_field_map, $group_title);
        }

        // Build key => metadata map including group info
        foreach ($acf_field_map as $key => $field) {
            $metadata[$key] = array(
                'label' => isset($field['label']) ? $field['label'] : '',
                'name' => isset($field['name']) ? $field['name'] : '',
                'type' => isset($field['type']) ? $field['type'] : 'unknown',
                'group' => isset($field['_group_title']) ? $field['_group_title'] : '',
            );
        }

        return $metadata;
    }

    /**
     * Create a REST response with error suppression to prevent warnings from corrupting JSON
     *
     * When returning large data structures via REST API, PHP warnings or notices
     * can corrupt the JSON response. This method temporarily suppresses errors
     * during WP_REST_Response creation to ensure clean JSON output.
     *
     * @param array $data Response data array
     * @param int $status HTTP status code (default 200)
     * @return WP_REST_Response
     */
    private function create_safe_rest_response($data, $status = 200) {
        // Suppress errors during response creation to prevent JSON corruption
        set_error_handler(function() {});

        $response = new WP_REST_Response($data, $status);

        // Restore previous error handler
        restore_error_handler();

        return $response;
    }

    /**
     * Execute import
     */
    public function execute_import($request) {
        $import_id = $request->get_param('id');

        // Load import
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error(
                'rest_not_found',
                __('Import not found.', 'wp_all_import_plugin'),
                array('status' => 404)
            );
        }

        try {
            // Trigger import execution
            // This will be handled by the existing import processing system
            $import->set(array(
                'processing' => 1,
                'triggered' => 1,
            ))->update();

            // Get the import URL for the user to be redirected to
            $import_url = add_query_arg(
                array(
                    'page' => 'pmxi-admin-manage',
                    'id' => $import->id,
                    'action' => 'process',
                ),
                admin_url('admin.php')
            );

            return new WP_REST_Response(array(
                'success' => true,
                'import_id' => $import->id,
                'redirect_url' => $import_url,
                'message' => __('Import execution started.', 'wp_all_import_plugin'),
            ), 200);

        } catch (Exception $e) {
            return new WP_Error(
                'rest_execution_error',
                sprintf(__('Error executing import: %s', 'wp_all_import_plugin'), $e->getMessage()),
                array('status' => 500)
            );
        }
    }

    /**
     * Finalize import configuration and redirect to execution
     */
    public function finalize_import($request) {
        $import_id = $request->get_param('id');

        WPAI_Bridge_Logger::separator( '======================================================' );
        WPAI_Bridge_Logger::debug( 'AI Bridge FINALIZE-IMPORT: Starting for import ' . $import_id );
        WPAI_Bridge_Logger::separator( '======================================================' );

        // Load import
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            WPAI_Bridge_Logger::debug( 'AI Bridge FINALIZE-IMPORT: Import not found!' );
            return new WP_Error(
                'rest_not_found',
                __('Import not found.', 'wp_all_import_plugin'),
                array('status' => 404)
            );
        }

        // Log current import state
        WPAI_Bridge_Logger::debug( 'AI Bridge FINALIZE-IMPORT: Current import state:' );
        WPAI_Bridge_Logger::debug( 'AI Bridge:   import->id = ' . $import->id );
        WPAI_Bridge_Logger::debug( 'AI Bridge:   import->path = ' . $import->path );
        WPAI_Bridge_Logger::debug( 'AI Bridge:   import->type = ' . $import->type );
        WPAI_Bridge_Logger::debug( 'AI Bridge:   import->xpath = ' . $import->xpath );

        // Check raw database
        global $wpdb;
        $table = $wpdb->prefix . 'pmxi_imports';
        $db_row = $wpdb->get_row( $wpdb->prepare( "SELECT id, path, type FROM {$table} WHERE id = %d", $import_id ) );
        WPAI_Bridge_Logger::debug( 'AI Bridge FINALIZE-IMPORT: RAW DB check:' );
        WPAI_Bridge_Logger::debug( 'AI Bridge:   db_path = ' . ( $db_row ? $db_row->path : 'NULL' ) );

        try {
            // Validate import configuration before finalizing
            $validation_errors = $this->validate_import_configuration($import);
			
            if (!empty($validation_errors)) {
                return new WP_Error(
                    'rest_validation_error',
                    __('Import configuration is incomplete or invalid.', 'wp_all_import_plugin'),
                    array(
                        'status' => 400,
                        'errors' => $validation_errors,
                    )
                );
            }

            // Count records AND pre-cache file structure in a single pass
            // This eliminates the cold start when Step 3 loads (previously ~3s for 1472 records)
            $file_path = $this->get_import_file_path($import);
            $record_count = 0;

            if (!is_wp_error($file_path) && file_exists($file_path)) {
                $structure_cache = WPAI_Bridge_File_Structure_Cache::getInstance();
                $existing = $structure_cache->get_cached_structure($import_id, $file_path);

                if ($existing !== false) {
                    // Structure already cached - just use the count
                    $record_count = $existing['total_records'];
                    WPAI_Bridge_Logger::debug('AI Bridge finalize_import: Using pre-cached structure (records: ' . $record_count . ')');
                } else {
                    // No cache - extract structure (which also counts records) and cache it
                    // This replaces the separate count-only loop that was here before
                    WPAI_Bridge_Logger::perf_start('finalize_structure_extraction');

                    $meta = $this->parse_file_metadata($file_path, $import);
                    $record_count = $meta['total_records'];
                    $meta['trigger'] = 'finalize_import';

                    $structure_cache->save_cached_structure($import_id, $meta);

                    WPAI_Bridge_Logger::perf_end('finalize_structure_extraction', array(
                        'import_id' => $import_id,
                        'total_records' => $record_count,
                        'trigger' => 'finalize_import',
                    ));
                }

                // Update import with record count
                $import->set(array('count' => $record_count))->update();
            }

            // Create file history record (required for file preview to work)
            // Check if history record already exists - use array syntax for proper lookup
            $history_file = new PMXI_File_Record();
            $history_file->getBy(array('import_id' => $import->id), 'id DESC');

            if ($history_file->isEmpty() || empty($history_file->path)) {
                // Read file contents
                $file_contents = file_exists($file_path) ? file_get_contents($file_path) : '';
                $relative_path = wp_all_import_get_relative_path($file_path);

                if ($history_file->isEmpty()) {
                    // Create new history record
                    // IMPORTANT: Use $file_path (processed XML) not $import->path (original URL/source)
                    // The file history stores the path to the actual XML file for processing
                    $history_file->set(array(
                        'name' => $import->name,
                        'import_id' => $import->id,
                        'path' => $relative_path,
                        'contents' => $file_contents,
                        'registered_on' => date('Y-m-d H:i:s'),
                    ))->save();
                    WPAI_Bridge_Logger::debug('AI Bridge finalize_import: Created file history with XML path: ' . $relative_path);
                } else {
                    // Update existing record with correct path (can happen during re-analysis)
                    $history_file->set(array(
                        'path' => $relative_path,
                        'contents' => $file_contents,
                    ))->update();
                    WPAI_Bridge_Logger::debug('AI Bridge finalize_import: Updated file history path to: ' . $relative_path);
                }
            } else {
                WPAI_Bridge_Logger::debug('AI Bridge finalize_import: File history already exists with path: ' . $history_file->path);
            }

            // Mark import as configured by LLM
            update_post_meta($import->id, '_wpai_llm_configured', 1);

            // Remove "in configuration" flag - import is now ready to be shown
            delete_post_meta($import->id, '_wpai_llm_configuring');

            // Clean up session token
            delete_post_meta($import->id, self::SESSION_TOKEN_META);

            // CRITICAL: Create/update the session for the import ID
            // WP All Import's wizard mode uses sessions keyed by import ID.
            // The validation in import.php checks:
            // 1. PMXI_Plugin::$session->xpath must be set (otherwise redirects to Step 2)
            // 2. PMXI_Plugin::$session->options must not be empty (otherwise redirects to Step 3)
            // Session key format: _wpallimport_session_{import_id}_
            $session_option = '_wpallimport_session_' . $import->id . '_';
            $existing_session = get_option($session_option);

            // Build session data from the import record
            $session_data = array();

            if ($existing_session && !empty($existing_session)) {
                // Decode existing session data
                $decoded = @unserialize(base64_decode($existing_session));
                if (is_array($decoded)) {
                    $session_data = $decoded;
                }
            }

            // Set required fields for Step 4 (options page) validation
            // xpath is required - without it, redirects to Step 2 (element selection)
            $session_data['xpath'] = $import->xpath;

            // options is required - without it, redirects to Step 3 (template)
            // Store as array (not serialized) - session code accesses it directly as $session->options['key']
            $current_options = is_array($import->options) ? $import->options : maybe_unserialize($import->options);

            // Merge with WP All Import's default options (from wp-all-import-pro.php:1458)
            // This replicates what happens when the template form is submitted with all its hidden defaults
            // The new Vercel flow bypasses form submission, so we need to ensure defaults are present
            $default_options = PMXI_Plugin::get_default_import_options();
            $session_data['options'] = array_merge($default_options, $current_options);

            // Ensure friendly_name is set (used by import.php:2753)
            if (empty($session_data['options']['friendly_name'])) {
                $session_data['options']['friendly_name'] = !empty($import->friendly_name) ? $import->friendly_name : $import->name;
            }

            // Element is required for chunk reading (chunk.php:296)
            if (empty($session_data['options']['element'])) {
                $session_data['options']['element'] = $import->root_element;
            }

            // Additional fields needed for proper wizard state and DOM parsing
            $session_data['count'] = $import->count;
            $session_data['is_update_previous'] = 1; // Always treat as update since import exists
            $session_data['update_previous'] = $import->id;
            $session_data['custom_type'] = $import->options['custom_type'] ?? 'post';
            $session_data['wizard_type'] = 'edit'; // Edit mode since import exists
            $session_data['root_element'] = $import->root_element;

            // encoding is needed for DOMDocument creation
            $session_data['encoding'] = $import->options['encoding'] ?? 'UTF-8';

            // IMPORTANT: filePath and local_paths must point to the PROCESSED XML file
            // These are used by WP All Import's get_xml() and _step_ready() for DOM parsing
            // Use $file_path (XML) NOT $import->path (which stores original URL/source for display)
            // CRITICAL: Use lowercase 'filepath' because WP All Import's session uses sanitize_key()
            // which lowercases all keys. If we use camelCase 'filePath', it won't be found!
            $session_data['filepath'] = $file_path; // lowercase! sanitize_key('filePath') = 'filepath'
            $session_data['local_paths'] = array($file_path);
            WPAI_Bridge_Logger::debug('AI Bridge finalize_import: Session filepath set to XML: ' . $file_path);

            // source stores original import info - path here is for reference/display, not processing
            // Keep using $import->path here since source is about the original file
            // IMPORTANT: When process() runs (import.php:2758-2773), it merges session->source into import
            // So source must use the same display values (URL for URL imports, original path for others)
            $session_data['source'] = array(
                'root_element' => $import->root_element,
                'name' => $import->name,
                'type' => $import->type,
                'path' => wp_all_import_get_relative_path($import->path),
            );
            WPAI_Bridge_Logger::debug('AI Bridge finalize_import: Session source values:');
            WPAI_Bridge_Logger::debug('AI Bridge:   source[name] = ' . $session_data['source']['name']);
            WPAI_Bridge_Logger::debug('AI Bridge:   source[type] = ' . $session_data['source']['type']);
            WPAI_Bridge_Logger::debug('AI Bridge:   source[path] = ' . $session_data['source']['path']);

            // Additional session fields expected by options page (lines 2091-2101 of import.php)
            $session_data['taxonomy_type'] = $import->options['taxonomy_type'] ?? '';
            $session_data['is_csv'] = $import->options['delimiter'] ?? '';
            $session_data['feed_type'] = $import->feed_type ?? '';
            $session_data['parent_import_id'] = $import->parent_import_id ?? 0;
            $session_data['ftp_host'] = $import->options['ftp_host'] ?? '';
            $session_data['ftp_path'] = $import->options['ftp_path'] ?? '';
            $session_data['ftp_root'] = $import->options['ftp_root'] ?? '/';
            $session_data['ftp_port'] = $import->options['ftp_port'] ?? '21';
            $session_data['ftp_username'] = $import->options['ftp_username'] ?? '';
            $session_data['ftp_password'] = $import->options['ftp_password'] ?? '';
            $session_data['ftp_private_key'] = $import->options['ftp_private_key'] ?? '';

            // Update the import record's options to include all the session defaults
            // This ensures the import has all required fields even outside the session context
            $import->set(array('options' => $session_data['options']))->update();

            // Encode and save session
            $encoded_data = base64_encode(serialize($session_data));
            $session_saved = update_option($session_option, $encoded_data);

            // Also set the session expiry
            $session_expiry_option = '_wpallimport_session_expires_' . $import->id . '_';
            $expiry_saved = update_option($session_expiry_option, time() + (60 * 60 * 48)); // 48 hours

            WPAI_Bridge_Logger::debug('AI Bridge: Session save result: ' . ($session_saved ? 'SUCCESS' : 'FAILED'));
            WPAI_Bridge_Logger::debug('AI Bridge: Session expiry save result: ' . ($expiry_saved ? 'SUCCESS' : 'FAILED'));

            // Immediately verify the session was saved
            $verify_session = get_option($session_option);
            WPAI_Bridge_Logger::debug('AI Bridge: Session option exists after save: ' . ($verify_session ? 'YES' : 'NO'));
            if ($verify_session) {
                $decoded_verify = @unserialize(base64_decode($verify_session));
                WPAI_Bridge_Logger::debug('AI Bridge: Verified session has filepath: ' . (isset($decoded_verify['filepath']) ? $decoded_verify['filepath'] : 'MISSING'));
            }

            WPAI_Bridge_Logger::debug('AI Bridge: Created session for import ' . $import->id . ' with xpath: ' . $import->xpath);

            // FINAL VERIFICATION: Check that import path hasn't changed
            WPAI_Bridge_Logger::separator( '======================================================' );
            WPAI_Bridge_Logger::debug( 'AI Bridge FINALIZE-IMPORT: FINAL VERIFICATION' );
            WPAI_Bridge_Logger::separator( '======================================================' );
            $final_import = new PMXI_Import_Record();
            $final_import->getById($import->id);
            WPAI_Bridge_Logger::debug( 'AI Bridge:   final import->path (ORM) = ' . $final_import->path );

            // Raw database check
            $final_db_row = $wpdb->get_row( $wpdb->prepare( "SELECT path FROM {$table} WHERE id = %d", $import->id ) );
            WPAI_Bridge_Logger::debug( 'AI Bridge:   final db_path (RAW) = ' . ( $final_db_row ? $final_db_row->path : 'NULL' ) );

            // Check file history
            $final_history = new PMXI_File_Record();
            $final_history->getBy( array( 'import_id' => $import->id ), 'id DESC' );
            WPAI_Bridge_Logger::debug( 'AI Bridge:   file history path = ' . ( $final_history->isEmpty() ? 'NONE' : $final_history->path ) );
            WPAI_Bridge_Logger::separator( '======================================================' );

            // Get the Step 3 template URL where user can review/edit the LLM-configured mapping
            // Use llm_success=1 to show completed state (NOT llm_mode=1 which would re-trigger LLM)
            $preview_url = add_query_arg(
                array(
                    'page' => 'pmxi-admin-import',
                    'action' => 'template',
                    'id' => $import->id,
                    'llm_success' => '1',
                ),
                admin_url('admin.php')
            );

            // Use safe response to prevent any PHP warnings from corrupting JSON
            return $this->create_safe_rest_response(array(
                'success' => true,
                'import_id' => $import->id,
                'redirect_url' => $preview_url,
                'record_count' => $record_count,
                'message' => __('Import configuration finalized. Ready for preview.', 'wp_all_import_plugin'),
            ), 200);

        } catch (Exception $e) {
            return new WP_Error(
                'rest_finalize_error',
                sprintf(__('Error finalizing import: %s', 'wp_all_import_plugin'), $e->getMessage()),
                array('status' => 500)
            );
        }
    }

    /**
     * Run WP All Import's native preview to create test records
     *
     * This endpoint exposes WP All Import's existing preview functionality via REST API.
     * It creates real records (posts, products, users, etc.) to test the import configuration,
     * then returns information about the created records including edit/view URLs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function run_preview( $request ) {
        // Start output buffering immediately to capture any output from WP All Import
        ob_start();

        $import_id = (int) $request->get_param( 'id' );
        // The raw progress HTML doubles the response size and duplicates the clean
        // `log` array; only include it when the caller explicitly opts in.
        $include_html = filter_var( $request->get_param( 'include_html' ), FILTER_VALIDATE_BOOLEAN );

        // Load import record
        $import = new PMXI_Import_Record();
        $import->getById( $import_id );

        if ( $import->isEmpty() ) {
            ob_end_clean();
            return new WP_Error(
                'rest_not_found',
                __( 'Import not found.', 'wp_all_import_plugin' ),
                array( 'status' => 404 )
            );
        }

        // Get the file path for this import
        $file_path = $this->get_import_file_path( $import );
        if ( is_wp_error( $file_path ) ) {
            ob_end_clean();
            return $file_path;
        }

        // Ensure preview functions are loaded
        if ( ! function_exists( 'wpai_execute_unified_preview' ) ) {
            $preview_file = WP_ALL_IMPORT_ROOT_DIR . '/actions/wp_ajax_wpai_run_preview_with_progress.php';
            if ( file_exists( $preview_file ) ) {
                require_once $preview_file;
            } else {
                ob_end_clean();
                return new WP_Error(
                    'rest_preview_unavailable',
                    __( 'Preview functionality is not available in this version of WP All Import.', 'wp_all_import_plugin' ),
                    array( 'status' => 501 )
                );
            }
        }

        // Ensure helper functions are loaded
        if ( ! function_exists( 'wpai_ensure_preview_column_exists' ) ) {
            $helpers_file = WP_ALL_IMPORT_ROOT_DIR . '/helpers/wpai_preview_cleanup.php';
            if ( file_exists( $helpers_file ) ) {
                require_once $helpers_file;
            }
        }

        // Ensure preview column exists
        if ( function_exists( 'wpai_ensure_preview_column_exists' ) ) {
            wpai_ensure_preview_column_exists();
        }

        // Generate preview session ID
        $preview_session_id = wp_generate_password( 32, false );
        if ( function_exists( 'wpai_register_preview_session' ) ) {
            wpai_register_preview_session( $preview_session_id );
        }

        try {
            // Get preview parameters from request
            $preview_mode = $request->get_param( 'preview_mode' ) ?: 'first';
            $specific_record = $request->get_param( 'specific_record' ) ?: '1';
            $range_start = (int) $request->get_param( 'range_start' ) ?: 1;
            $range_end = (int) $request->get_param( 'range_end' ) ?: 1;
            $multiple_records = $request->get_param( 'multiple_records' ) ?: '1';

            // Calculate which records to preview (matching WP All Import's logic)
            $records_to_import = array();
            switch ( $preview_mode ) {
                case 'first':
                    $records_to_import = array( 1 );
                    break;
                case 'specific':
                    foreach ( preg_split( '% *, *%', $specific_record, -1, PREG_SPLIT_NO_EMPTY ) as $chunk ) {
                        if ( preg_match( '%^(\d+)\s*-\s*(\d+)$%', $chunk, $m ) ) {
                            $start = intval( $m[1] );
                            $end = intval( $m[2] );
                            if ( $start > 0 && $end >= $start ) {
                                $records_to_import = array_merge( $records_to_import, range( $start, $end ) );
                            }
                        } else {
                            $n = intval( $chunk );
                            if ( $n > 0 ) {
                                $records_to_import[] = $n;
                            }
                        }
                    }
                    $records_to_import = array_values( array_unique( $records_to_import ) );
                    sort( $records_to_import );
                    break;
                case 'range':
                    if ( $range_start > 0 && $range_end >= $range_start ) {
                        $records_to_import = range( $range_start, $range_end );
                    }
                    break;
                case 'multiple':
                    foreach ( preg_split( '% *, *%', $multiple_records, -1, PREG_SPLIT_NO_EMPTY ) as $chunk ) {
                        if ( preg_match( '%^(\d+)\s*-\s*(\d+)$%', $chunk, $m ) ) {
                            $start = intval( $m[1] );
                            $end = intval( $m[2] );
                            if ( $start > 0 && $end >= $start ) {
                                $records_to_import = array_merge( $records_to_import, range( $start, $end ) );
                            }
                        } else {
                            $n = intval( $chunk );
                            if ( $n > 0 ) {
                                $records_to_import[] = $n;
                            }
                        }
                    }
                    $records_to_import = array_values( array_unique( $records_to_import ) );
                    sort( $records_to_import );
                    break;
                default:
                    $records_to_import = array( 1 );
            }

            if ( empty( $records_to_import ) ) {
                $records_to_import = array( 1 );
            }

            // Build preview data (matches what WP All Import's preview expects)
            $import_options = is_array( $import->options ) ? $import->options : maybe_unserialize( $import->options );
            $import_type = $import_options['custom_type'] ?? 'post';

            // Determine import category for preview system
            $import_category = 'posts';
            if ( $import_type === 'taxonomies' ) {
                $import_category = 'taxonomies';
            } elseif ( in_array( $import_type, array( 'import_users', 'shop_customer' ), true ) ) {
                $import_category = 'users';
            } elseif ( in_array( $import_type, array( 'comments', 'woo_reviews' ), true ) ) {
                $import_category = 'comments';
            } elseif ( $import_type === 'gf_entries' ) {
                $import_category = 'gravity_forms';
            }

            // Apply preview-specific modifications (e.g., append -preview to slugs)
            if ( function_exists( 'wpai_apply_preview_modifications' ) ) {
                $import_options = wpai_apply_preview_modifications( $import_options, $import_type );
            }

            $preview_data = array(
                'xpath'        => $import->xpath,
                'root_element' => $import->root_element,
                'options'      => $import_options,
            );

            // Set up logger - collect messages AND echo as HTML for display
            $log_messages = array();
            $import_log_html_parts = array();
            $logger = function( $m ) use ( &$log_messages, &$import_log_html_parts ) {
                // The context-free "String expected as argument." type-guard from
                // XmlImportStringReader fires before record #1 on runs that import
                // fine and names no field/record — non-actionable noise the agent
                // would chase. It is already kept out of log_errors; drop it from the
                // raw preview log/HTML too. (WP All Import's own on-disk log is
                // written separately and is untouched.)
                if ( wpai_bridge_is_spurious_log_line( $m ) ) {
                    return;
                }
                $timestamp = '[' . date( 'H:i:s' ) . '] ';
                $log_messages[] = $timestamp . $m;
                // Build HTML log for display
                $import_log_html_parts[] = '<div class="progress-msg">' . esc_html( $timestamp . $m ) . '</div>';
            };

            // Execute the preview using WP All Import's native system.
            // Note: wpai_execute_unified_preview manages its own output buffering internally.
            //
            // Add-on preview hooks (e.g. the WooCommerce add-on's setImport()) resolve the
            // "current import" through PMXI_Input, which reads $_GET/$_REQUEST. The admin
            // AJAX preview carries the id in those superglobals; an MCP/REST request carries
            // it only as a REST param, so without this the add-on loads an EMPTY import
            // record and fatals on $import->options — aborting the preview before it can roll
            // back its sample record. Expose the id here and restore the superglobals after.
            $prev_get_id      = array_key_exists( 'id', $_GET ) ? $_GET['id'] : null;
            $prev_req_id      = array_key_exists( 'id', $_REQUEST ) ? $_REQUEST['id'] : null;
            $prev_get_iid     = array_key_exists( 'import_id', $_GET ) ? $_GET['import_id'] : null;
            $prev_req_iid     = array_key_exists( 'import_id', $_REQUEST ) ? $_REQUEST['import_id'] : null;
            $_GET['id']       = $_REQUEST['id']        = $import_id;
            $_GET['import_id'] = $_REQUEST['import_id'] = $import_id;

            // Preview must be a REAL run (so every theme/plugin/add-on hook fires and the
            // result is accurate), which the engine drives through PMXI_Plugin::$session —
            // populated in wp-admin by adminInit(), absent in a REST request. Without it,
            // session-scoped state (chunk_number) fatals. Establish a session for the run,
            // then restore it, and finally sweep the sandbox preview records the same way
            // the admin_init cleanup hook does in wp-admin (that hook never fires here).
            $prev_session = PMXI_Plugin::$session;
            if ( ! ( PMXI_Plugin::$session instanceof PMXI_Session ) ) {
                PMXI_Plugin::$session = new PMXI_Handler();
            }
            PMXI_Plugin::$session->import_id    = $import_id;
            PMXI_Plugin::$session->chunk_number = 0;

            // A preview sideloads real images just like a real run; deleting the sandbox
            // preview import removes its posts but not these attachments. Snapshot existing
            // attachment IDs so we can delete any created during the preview afterward
            // (diff-based, so it works regardless of how WPAI inserts the attachment).
            $att_ids_before = get_posts( array(
                'post_type'      => 'attachment',
                'post_status'    => 'any',
                'fields'         => 'ids',
                'numberposts'    => -1,
                'suppress_filters' => true,
            ) );

            try {
                $result = wpai_execute_unified_preview(
                    $preview_data,
                    $file_path,
                    $records_to_import,
                    $logger,
                    $import_category,
                    $import_type,
                    $preview_session_id
                );
            } finally {
                // The admin sweep protects "active" preview sessions (tracked by a
                // transient kept alive via heartbeat). A one-shot REST preview has no
                // heartbeat, so end the session transient first — otherwise the sandbox
                // record is treated as in-use and never swept — then run the same cleanup
                // wp-admin runs on admin_init, plus drop the now-empty preview import shell.
                // The preview creates a sandbox "Session Preview Import" (is_preview=1,
                // parent_import_id=0) plus its sample post/attachments. wp-admin sweeps
                // these on admin_init, which never fires for a REST request. End our session
                // first (so the sweep won't treat it as active), then fully delete every
                // INACTIVE preview import — its posts, attachments, and the import row — via
                // WPAI's own deleter. Active previews (live admin sessions, kept alive by
                // heartbeat) are protected. parent_import_id can't be used here (it's 0), so
                // we target is_preview rows directly.
                $att_ids_after = get_posts( array(
                    'post_type'      => 'attachment',
                    'post_status'    => 'any',
                    'fields'         => 'ids',
                    'numberposts'    => -1,
                    'suppress_filters' => true,
                ) );
                foreach ( array_diff( $att_ids_after, $att_ids_before ) as $att_id ) {
                    wp_delete_attachment( (int) $att_id, true );
                }
                if ( ! empty( $preview_session_id ) ) {
                    delete_transient( 'wpai_preview_session_' . $preview_session_id );
                }
                if ( class_exists( 'PMXI_Plugin' ) && function_exists( 'wpai_delete_preview_import' ) ) {
                    global $wpdb;
                    $prefix = PMXI_Plugin::getInstance()->getTablePrefix();
                    $active = function_exists( 'wpai_get_active_preview_session_import_ids' )
                        ? array_map( 'intval', (array) wpai_get_active_preview_session_import_ids() )
                        : array();
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- internal WPAI table name from the plugin prefix, no user input.
                    $preview_ids = $wpdb->get_col( "SELECT id FROM {$prefix}imports WHERE is_preview = 1" );
                    foreach ( (array) $preview_ids as $pid ) {
                        if ( ! in_array( (int) $pid, $active, true ) ) {
                            wpai_delete_preview_import( $pid );
                        }
                    }
                } elseif ( function_exists( 'wpai_cleanup_all_preview_records' ) ) {
                    wpai_cleanup_all_preview_records();
                }
                PMXI_Plugin::$session = $prev_session;
                if ( null === $prev_get_id ) { unset( $_GET['id'] ); } else { $_GET['id'] = $prev_get_id; }
                if ( null === $prev_req_id ) { unset( $_REQUEST['id'] ); } else { $_REQUEST['id'] = $prev_req_id; }
                if ( null === $prev_get_iid ) { unset( $_GET['import_id'] ); } else { $_GET['import_id'] = $prev_get_iid; }
                if ( null === $prev_req_iid ) { unset( $_REQUEST['import_id'] ); } else { $_REQUEST['import_id'] = $prev_req_iid; }
            }

            // Capture any stray output that might have escaped
            $stray_output = ob_get_clean();

            // Build import log HTML from logger calls
            $import_log_html = implode( "\n", $import_log_html_parts );

            // If there was stray output, append it
            if ( ! empty( $stray_output ) ) {
                $import_log_html .= "\n" . '<div class="stray-output">' . esc_html( $stray_output ) . '</div>';
            }

            // Surface composing errors/warnings WP All Import logged but that don't fail the
            // preview outright. Otherwise they're invisible behind success:true — buried in
            // the raw log array with no structured signal (e.g. "ERROR: String expected as
            // argument." from a mapping expression evaluated against an empty value).
            $log_errors = array_values( array_filter( (array) $log_messages, function ( $line ) {
                if ( wpai_bridge_is_spurious_log_line( $line ) ) {
                    return false;
                }
                return (bool) preg_match( '/\b(ERROR|WARNING)\b/i', (string) $line );
            } ) );

            if ( ! $result['success'] ) {
                return new WP_Error(
                    'rest_preview_failed',
                    $result['message'] ?? __( 'Preview failed', 'wp_all_import_plugin' ),
                    array( 'status' => 500, 'log' => $log_messages )
                );
            }

            // Build response
            $response = array(
                'success'              => true,
                'post_id'              => $result['post_id'] ?? null,
                'post_ids'             => $result['post_ids'] ?? array(),
                'post_count'           => $result['post_count'] ?? 0,
                'post_title'           => $result['post_title'] ?? '',
                'post_status'          => $result['post_status'] ?? '',
                'edit_url'             => $result['edit_url'] ?? '',
                'view_url'             => $result['view_url'] ?? '',
                'was_skipped'          => $result['was_skipped'] ?? false,
                'skip_reason'          => $result['skip_reason'] ?? '',
                'is_unexpected_multiple' => $result['is_unexpected_multiple'] ?? false,
                'warning_context'      => array_values( array_merge( (array) ( $result['warning_context'] ?? array() ), $log_errors ) ),
                'log_errors'           => $log_errors,
                'total_available_records' => $result['total_available_records'] ?? 0,
                'preview_session_id'   => $preview_session_id,
                'log'                  => $log_messages,
            );

            if ( $include_html ) {
                $response['import_log_html'] = $import_log_html;
            }

            return new WP_REST_Response( $response, 200 );

        } catch ( \Throwable $e ) {
            // Catch Error as well as Exception so a fatal in the preview engine can never
            // leave the output buffer open (it would auto-flush partial HTML into the JSON
            // response and corrupt the MCP transport).
            if ( ob_get_level() > 0 ) {
                ob_end_clean();
            }
            return new WP_Error(
                'rest_preview_error',
                sprintf( __( 'Preview error: %s', 'wp_all_import_plugin' ), $e->getMessage() ),
                array( 'status' => 500 )
            );
        }
    }

    /**
     * Validate import configuration
     */
    private function validate_import_configuration($import) {
        $errors = array();

        // Check if file exists (handles preview imports and file history)
        $file_path = $this->get_import_file_path($import);
        if (is_wp_error($file_path)) {
            $errors[] = __('Import file does not exist.', 'wp_all_import_plugin');
        }

        // Check if root element is set
        if (empty($import->root_element)) {
            $errors[] = __('Root element is not configured.', 'wp_all_import_plugin');
        }

        // Check if post type is set
        if (empty($import->options['custom_type'])) {
            $errors[] = __('Post type is not configured.', 'wp_all_import_plugin');
        }

        // Check if unique identifier is set for matching imports
        if (!empty($import->options['is_update_previous']) && empty($import->options['unique_key'])) {
            $errors[] = __('Unique identifier is required for updating existing items.', 'wp_all_import_plugin');
        }

        return $errors;
    }

    /**
     * Ensure all add-on defaults are present in the import options
     *
     * This is critical for WooCommerce imports - the add-on expects many options
     * to be set with default values. Without these defaults, the parser will throw
     * "Undefined array key" warnings and may not process products correctly.
     *
     * Uses the + operator to add defaults without overwriting existing values.
     */
    private function ensure_addon_defaults($options) {
        $custom_type = isset($options['custom_type']) ? $options['custom_type'] : '';

        // Add WP All Import core defaults (existing values in $options take precedence)
        $options = $options + PMXI_Plugin::get_default_import_options();

        // Add WooCommerce add-on defaults for product/order imports
        if (in_array($custom_type, array('product', 'product_variation', 'shop_order', 'shop_order_refund'))
            && class_exists('PMWI_Plugin')) {
            $woo_defaults = PMWI_Plugin::get_default_import_options();
            // Use + operator: existing values in $options take precedence over defaults
            $options = $options + $woo_defaults;
            WPAI_Bridge_Logger::debug("[WPAI Bridge] Applied WooCommerce add-on defaults for $custom_type import");
        }

        // Add User add-on defaults
        if ($custom_type === 'import_users' && class_exists('PMUI_Plugin')
            && method_exists('PMUI_Plugin', 'get_default_import_options')) {
            $user_defaults = PMUI_Plugin::get_default_import_options();
            $options = $options + $user_defaults;
            WPAI_Bridge_Logger::debug("[WPAI Bridge] Applied User add-on defaults");
        }

        // Add ACF add-on defaults
        if (class_exists('PMAI_Plugin') && method_exists('PMAI_Plugin', 'get_default_import_options')) {
            $acf_defaults = PMAI_Plugin::get_default_import_options();
            $options = $options + $acf_defaults;
        }

        // Add Gravity Forms add-on defaults
        if ($custom_type === 'gravityformsentries' && class_exists('PMGI_Plugin')
            && method_exists('PMGI_Plugin', 'get_default_import_options')) {
            $gf_defaults = PMGI_Plugin::get_default_import_options();
            $options = $options + $gf_defaults;
        }

        // Add Toolset add-on defaults
        if (class_exists('PMTI_Plugin') && method_exists('PMTI_Plugin', 'get_default_import_options')) {
            $toolset_defaults = PMTI_Plugin::get_default_import_options();
            $options = $options + $toolset_defaults;
        }

        // Add defaults for "new API" addons (JetEngine, MetaBox, Pods, etc.)
        // These addons use PMXI_Addon_API and require _switchers, _multiple, _groups arrays
        $new_api_addons = array('jetengine', 'metabox', 'pods', 'mb', 'jet-engine');
        foreach ($new_api_addons as $addon_slug) {
            // Initialize the addon arrays if not already set
            if (!isset($options[$addon_slug])) {
                $options[$addon_slug] = array();
            }
            if (!isset($options[$addon_slug . '_switchers'])) {
                $options[$addon_slug . '_switchers'] = array();
            }
            if (!isset($options[$addon_slug . '_multiple'])) {
                $options[$addon_slug . '_multiple'] = array();
            }
            if (!isset($options[$addon_slug . '_groups'])) {
                $options[$addon_slug . '_groups'] = array();
            }
        }

        return $options;
    }

    /**
     * Filter custom fields to remove WooCommerce, add-on, and system fields
     *
     * This prevents WooCommerce-specific fields, add-on fields, and system fields
     * from appearing in the Custom Fields section when they should be handled by
     * their respective sections in the import template.
     */
    private function filter_custom_fields($options) {
        // List of field names that should NOT be in custom fields
        // These are WooCommerce product fields and system fields
        $excluded_fields = array(
            // WooCommerce product type fields
            'is_multiple_product_type',
            'multiple_product_type',

            // WooCommerce matching/parent fields
            'matching_parent',
            'parent',

            // WooCommerce product fields (these have their own sections)
            'single_product_sku',
            'single_product_regular_price',
            'single_product_sale_price',
            'single_product_stock_qty',
            'single_product_weight',
            'single_product_length',
            'single_product_width',
            'single_product_height',
            'is_product_manage_stock',
            'product_stock_status',

            // Core WordPress fields
            'title',
            'content',
            'post_excerpt',
            'excerpt',
            'slug',
            'author',
            'date',
            'status',
            'comment_status',
            'custom_type',

            // System fields
            'unique_key',
            'duplicate_indicator',

            // User/Customer Import Add-On settings (non-bracketed)
            'is_hashed_wordpress_password',
            'do_not_send_password_notification',
            'send_email',
            'create_new_records',

            // Gravity Forms Add-On settings
            'pmgi_is_update_entry_fields',
        );

        // Build list of attribute names (lowercase) to exclude from custom fields
        // This prevents attributes from being duplicated in the custom fields section
        $attribute_names_lower = array();
        if (!empty($options['attribute_name']) && is_array($options['attribute_name'])) {
            foreach ($options['attribute_name'] as $attr_name) {
                if (!empty($attr_name)) {
                    $attribute_names_lower[] = strtolower(trim($attr_name));
                }
            }
        }

        // Collect all taxonomy XPaths to exclude from custom fields
        // This prevents taxonomy fields from being duplicated in the custom fields section
        $taxonomy_xpaths = array();
        // Single taxonomy XPaths
        if (!empty($options['tax_single_xpath']) && is_array($options['tax_single_xpath'])) {
            foreach ($options['tax_single_xpath'] as $xpath) {
                if (!empty($xpath)) {
                    $taxonomy_xpaths[$xpath] = true;
                }
            }
        }
        // Multiple taxonomy XPaths
        if (!empty($options['tax_multiple_xpath']) && is_array($options['tax_multiple_xpath'])) {
            foreach ($options['tax_multiple_xpath'] as $xpath) {
                if (!empty($xpath)) {
                    $taxonomy_xpaths[$xpath] = true;
                }
            }
        }
        // Hierarchical taxonomy XPaths (arrays of concatenated XPaths like "{a[1]} > {b[1]} > {c[1]}")
        // Need to extract individual XPaths from the concatenated strings
        if (!empty($options['tax_hierarchical_xpath']) && is_array($options['tax_hierarchical_xpath'])) {
            foreach ($options['tax_hierarchical_xpath'] as $xpaths) {
                if (is_array($xpaths)) {
                    foreach ($xpaths as $concat_xpath) {
                        if (!empty($concat_xpath)) {
                            // Extract individual XPaths from concatenated string using regex
                            // Matches patterns like {field[1]} or {field_name[1]}
                            if (preg_match_all('/\{[^}]+\}/', $concat_xpath, $matches)) {
                                foreach ($matches[0] as $xpath) {
                                    $taxonomy_xpaths[$xpath] = true;
                                }
                            }
                            // Also add the full concatenated string in case it's used as-is
                            $taxonomy_xpaths[$concat_xpath] = true;
                        }
                    }
                }
            }
        }

        // Filter custom_name and custom_value arrays
        if (!empty($options['custom_name']) && is_array($options['custom_name'])) {
            $filtered_names = array();
            $filtered_values = array();

            foreach ($options['custom_name'] as $index => $name) {
                // Skip if this is an excluded field
                if (in_array($name, $excluded_fields)) {
                    continue;
                }

                // Skip if the name is empty (blank custom field)
                if (empty(trim($name))) {
                    continue;
                }

                // Skip if this field is already defined as a product attribute
                // (case-insensitive comparison)
                if (in_array(strtolower(trim($name)), $attribute_names_lower)) {
                    continue;
                }

                // Get the corresponding value
                $value = isset($options['custom_value'][$index]) ? $options['custom_value'][$index] : '';

                // Skip if the value is empty (no XPath mapping)
                if (empty(trim($value))) {
                    continue;
                }

                // Skip if this value is already used in a taxonomy mapping
                if (isset($taxonomy_xpaths[$value])) {
                    continue;
                }

                // Check if this field uses bracket notation (e.g., "pmwi_order[status]")
                // If so, it's likely an add-on field that should be in its own namespace
                if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\[/', $name, $matches)) {
                    $prefix = $matches[1];

                    // Check if this prefix exists as an array in options (add-on namespace)
                    // OR if it looks like an add-on prefix (starts with pm)
                    $is_addon_field = false;

                    // If the prefix already exists as an array in options, it's definitely an add-on
                    if (isset($options[$prefix]) && is_array($options[$prefix])) {
                        $is_addon_field = true;
                    }
                    // Common add-on prefix patterns: pmXX, pmXX_something
                    elseif (preg_match('/^pm[a-z]{2,}/', $prefix)) {
                        $is_addon_field = true;
                    }
                    // Also check for pmsci_ pattern (WooCommerce customer)
                    elseif (strpos($prefix, 'pmsci_') === 0) {
                        $is_addon_field = true;
                    }

                    if ($is_addon_field) {
                        continue;
                    }
                }

                // Skip if both name and value are empty (completely blank custom field)
                if (empty(trim($name)) && empty(trim($value))) {
                    continue;
                }

                // Keep this custom field
                $filtered_names[] = $name;
                $filtered_values[] = $value;
            }

            // Update the options with filtered arrays
            $options['custom_name'] = $filtered_names;
            $options['custom_value'] = $filtered_values;
        }

        return $options;
    }

    /**
     * Sanitize import options
     *
     * This function passes through all options provided, applying type-specific
     * sanitization where appropriate. It does NOT act as a whitelist - any field
     * can be updated, which allows the LLM to set WooCommerce Add-On fields
     * and other plugin-specific options.
     */
    private function sanitize_import_options($options) {
        $sanitized = array();

        // Fields that need HTML sanitization (but preserve XPath syntax)
        $html_fields = array('title', 'content', 'post_excerpt', 'slug', 'author', 'date', 'custom_type', 'status');

        // Fields that should be cast to boolean. update_existing_records is the
        // MCP-documented alias of is_update_previous; casting it here (alongside
        // create_new_records and is_update_previous) keeps all three critical
        // duplicate-handling flags a consistent boolean type in get-options.
        $boolean_fields = array('is_update_previous', 'update_existing_records', 'create_new_records', 'is_delete_missing', 'is_keep_former_posts');

        // Fields that should be cast to integer
        $integer_fields = array('records_per_request', 'chunk_size');

        // Fields that must be valid taxonomy slugs (alphanumeric, hyphens, underscores only).
        // Defense-in-depth: these values are interpolated into SQL in WP All Import core.
        $taxonomy_slug_fields = array('taxonomy_type');

        // Fields that must be valid post status strings
        $status_fields = array('status_of_removed');

        // Process all provided options
        foreach ($options as $key => $value) {
            if (in_array($key, $html_fields)) {
                // Sanitize HTML but preserve XPath syntax like {sku[1]}
                $sanitized[$key] = wp_kses_post($value);
            } elseif (in_array($key, $boolean_fields)) {
                // Cast to boolean
                $sanitized[$key] = (bool) $value;
            } elseif (in_array($key, $integer_fields)) {
                // Cast to integer
                $sanitized[$key] = intval($value);
            } elseif (in_array($key, $taxonomy_slug_fields)) {
                // Only allow valid taxonomy slug characters (a-z, 0-9, hyphens, underscores)
                $sanitized[$key] = preg_replace('/[^a-zA-Z0-9_\-]/', '', $value);
            } elseif (in_array($key, $status_fields)) {
                // Only allow valid WordPress post status characters
                $sanitized[$key] = preg_replace('/[^a-zA-Z0-9_\-]/', '', $value);
            } else {
                // Pass through all other fields unchanged
                // This includes WooCommerce Add-On fields, XPath expressions, arrays, etc.
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Type-check the known critical import options and flag keys core doesn't
     * recognize. Boolean-ish flags are coerced to '1'/'0'; a scalar that can't be
     * coerced (an array where a scalar is required, an unrecognized boolean token)
     * is an error. Unknown keys are never rejected (add-ons register their own),
     * only reported as warnings.
     *
     * @return array{0: array, 1: string[], 2: string[]} [ options, errors, warnings ]
     */
    private function validate_critical_options($options, $current_options = array()) {
        $errors   = array();
        $warnings = array();
        if (!is_array($current_options)) {
            $current_options = array();
        }

        $boolean_keys = array('create_new_records', 'update_existing_records', 'is_update_previous');
        $scalar_keys  = array('unique_key', 'status');

        foreach ($boolean_keys as $key) {
            if (!array_key_exists($key, $options)) {
                continue;
            }
            $value = $options[$key];
            if (is_bool($value)) {
                $options[$key] = $value ? '1' : '0';
            } elseif (is_int($value) || (is_string($value) && preg_match('/^\s*[01]\s*$/', $value))) {
                $options[$key] = ((int) $value) ? '1' : '0';
            } elseif (is_string($value) && in_array(strtolower(trim($value)), array('yes', 'true', 'on'), true)) {
                $options[$key] = '1';
            } elseif (is_string($value) && in_array(strtolower(trim($value)), array('no', 'false', 'off', ''), true)) {
                $options[$key] = '0';
            } else {
                $errors[] = sprintf(
                    /* translators: %s: option key */
                    __('Option "%s" expects a boolean (1/0, true/false, yes/no).', 'wpai-ai-bridge-plugin'),
                    $key
                );
            }
        }

        foreach ($scalar_keys as $key) {
            if (!array_key_exists($key, $options)) {
                continue;
            }
            if (is_array($options[$key])) {
                $errors[] = sprintf(
                    /* translators: %s: option key */
                    __('Option "%s" expects a string value, array given.', 'wpai-ai-bridge-plugin'),
                    $key
                );
            } else {
                $options[$key] = (string) $options[$key];
            }
        }

        // A unique_key with no {…} placeholder is a constant: every record gets the
        // SAME key, so WP All Import treats them all as one duplicate and collapses
        // the import into a single record (an empty unique_key does the same). This
        // is exactly the failure the tool description warns about, so surface it
        // instead of a silent success. Reuse evaluate-xpath's is-literal rule
        // (a value with no {…} is a literal).
        if (array_key_exists('unique_key', $options) && is_string($options['unique_key'])) {
            $uk = trim($options['unique_key']);
            $has_placeholder = (false !== strpos($uk, '{') && false !== strpos($uk, '}'));
            if ('' === $uk) {
                $warnings[] = __('unique_key is empty, so every record shares the same (blank) identity and the import collapses to a single record. Set a per-record expression, e.g. "{sku[1]}".', 'wpai-ai-bridge-plugin');
            } elseif (!$has_placeholder) {
                $warnings[] = sprintf(
                    /* translators: %s: the literal unique_key value */
                    __('unique_key "%s" has no {…} placeholder, so it is a constant — every record gets the same key and the import collapses to a single record. Wrap a source field in braces, e.g. "{sku[1]}".', 'wpai-ai-bridge-plugin'),
                    $uk
                );
            }
        }

        // 'status' beyond the scalar check:
        //  - The literal "xpath" is WP All Import's two-field dynamic-status marker:
        //    the real per-record value comes from the paired status_xpath. It is
        //    canonical WPAI (models/import/record.php keys dynamic status off
        //    status === 'xpath'), NOT a literal post_status, so accept it. Warn when
        //    the pair is incomplete, because core then falls back to publish and
        //    silently force-publishes every record.
        //  - Any other plain literal must be a real registered post status
        //    (get_post_stati includes add-on/custom ones, e.g. WooCommerce wc-*).
        //  - A {template} expression is left untouched (unchanged from before).
        if (array_key_exists('status', $options) && is_string($options['status'])) {
            $status = trim($options['status']);
            if ('xpath' === strtolower($status)) {
                // status_xpath may arrive in this same payload, or it may have been
                // persisted earlier by update_template (the configure flow sends the
                // template — which carries status_xpath — before the options). Consider
                // both so the warning fires only when the pair is genuinely incomplete,
                // not on every dynamic-status import (where status_xpath rode the
                // template payload and is already in $current_options).
                $status_xpath = isset($options['status_xpath'])
                    ? trim((string) $options['status_xpath'])
                    : (isset($current_options['status_xpath']) ? trim((string) $current_options['status_xpath']) : '');
                if ('' === $status_xpath) {
                    $warnings[] = __('Option "status" is "xpath" (dynamic mode) but "status_xpath" is empty, so WP All Import will publish every record. Set status_xpath to a per-record expression, or set a literal status instead.', 'wpai-ai-bridge-plugin');
                }
            } elseif ('' !== $status && false === strpos($status, '{')) {
                $allowed = $this->allowed_post_statuses();
                if (!in_array(strtolower($status), $allowed, true)) {
                    $errors[] = sprintf(
                        /* translators: 1: given status, 2: allowed statuses */
                        __('Option "status" must be a valid post status (one of: %2$s), or "xpath" for a status_xpath-driven dynamic status. Got "%1$s".', 'wpai-ai-bridge-plugin'),
                        $status,
                        implode(', ', $allowed)
                    );
                }
            }
        }

        // Reconcile the two "update existing" flags so the documented MCP key
        // (update_existing_records) and the core key (is_update_previous) never
        // disagree: whichever the caller set wins and is mirrored onto the other.
        // The documented key is authoritative when both are present. Both are
        // already coerced to '1'/'0' above when present.
        $has_uer = array_key_exists('update_existing_records', $options);
        $has_iup = array_key_exists('is_update_previous', $options);
        if ($has_uer || $has_iup) {
            $canonical = $has_uer ? $options['update_existing_records'] : $options['is_update_previous'];
            $options['update_existing_records'] = $canonical;
            $options['is_update_previous']      = $canonical;
        }

        // The critical keys are always recognized (update_existing_records is an
        // MCP-level alias not present in core defaults).
        $core_keys = array_merge($this->get_core_option_keys(), $boolean_keys, $scalar_keys);
        if (!empty($core_keys)) {
            foreach (array_keys($options) as $key) {
                if (!in_array($key, $core_keys, true)) {
                    $warnings[] = sprintf(
                        /* translators: %s: option key */
                        __('Option "%s" is not a recognized WP All Import core setting (it may belong to an add-on, or be a typo). It was saved as-is.', 'wpai-ai-bridge-plugin'),
                        $key
                    );
                }
            }
        }

        return array($options, $errors, $warnings);
    }

    /**
     * Post statuses a plain-literal `status` option may take. Registered statuses
     * (get_post_stati) cover core + add-on/custom ones (e.g. WooCommerce wc-*),
     * plus the values WP All Import itself accepts for the status template field.
     *
     * @return string[] lowercased status slugs
     */
    private function allowed_post_statuses() {
        $stati = function_exists('get_post_stati') ? array_keys(get_post_stati()) : array();
        $stati = array_merge($stati, array('publish', 'draft', 'pending', 'private', 'future', 'trash', 'inherit', 'auto-draft'));
        return array_values(array_unique(array_map('strtolower', array_map('strval', $stati))));
    }

    /**
     * Keys WP All Import core (plus any active add-ons) recognizes as import
     * options. Used only to flag unknown keys as informational warnings.
     */
    private function get_core_option_keys() {
        static $keys = null;
        if (null !== $keys) {
            return $keys;
        }

        $keys = array();
        if (class_exists('PMXI_Plugin') && method_exists('PMXI_Plugin', 'get_default_import_options')) {
            $keys = array_keys((array) PMXI_Plugin::get_default_import_options());
        }

        if (class_exists('PMXI_Admin_Addons') && method_exists('PMXI_Admin_Addons', 'get_active_addons')) {
            foreach (PMXI_Admin_Addons::get_active_addons() as $class) {
                if (class_exists($class) && method_exists($class, 'get_default_import_options')) {
                    $keys = array_merge($keys, array_keys((array) call_user_func(array($class, 'get_default_import_options'))));
                }
            }
        }

        $keys = array_values(array_unique($keys));
        return $keys;
    }

    /**
     * Get the correct file path for an import, handling preview imports.
     *
     * When a preview import runs, it may overwrite the session's import_id with the
     * preview import's ID. Preview imports have their files in a temporary directory
     * that gets cleaned up. This method ensures we always get the correct file path
     * by checking if the import is a preview import and falling back to the parent
     * import's file path or the session's file path.
     *
     * @param PMXI_Import_Record $import The import record
     * @return string|WP_Error The absolute file path or WP_Error if not found
     */
    public function get_import_file_path($import) {
        WPAI_Bridge_Logger::separator( '======================================================' );
        WPAI_Bridge_Logger::debug( 'AI Bridge GET_IMPORT_FILE_PATH: Called for import ' . $import->id );
        WPAI_Bridge_Logger::separator( '======================================================' );
        WPAI_Bridge_Logger::debug( 'AI Bridge:   import->path = ' . $import->path );
        WPAI_Bridge_Logger::debug( 'AI Bridge:   import->type = ' . $import->type );
        WPAI_Bridge_Logger::debug( 'AI Bridge:   import->is_preview = ' . ( $import->is_preview ? 'YES' : 'NO' ) );

        // Check if this is a preview import
        if (!empty($import->is_preview)) {
            // For preview imports with a parent, get the parent's file path
            if (!empty($import->parent_import_id)) {
                $parent_import = new PMXI_Import_Record();
                $parent_import->getById($import->parent_import_id);
                if (!$parent_import->isEmpty()) {
                    return $this->get_import_file_path($parent_import);
                }
            }

            // For session-based preview imports (no parent), use session filePath
            if (!empty(PMXI_Plugin::$session) && !empty(PMXI_Plugin::$session->filePath)) {
                $file_path = wp_all_import_get_absolute_path(PMXI_Plugin::$session->filePath);
                if (file_exists($file_path)) {
                    return $file_path;
                }
            }

            // Preview import with no valid source - return error
            return new WP_Error(
                'rest_not_found',
                __('Preview import file not found. Please start a new import.', 'wp_all_import_plugin'),
                array('status' => 404)
            );
        }

        // For regular imports, first try the import's path
        $file_path = wp_all_import_get_absolute_path($import->path);

        // All file types that WP All Import converts to XML (from PMXI_Upload class)
        // These need special handling to find the processed XML file
        // Pattern matches: csv, tsv, txt, dat, psv, json, xls, xlsx, sql, gz, gzip
        $non_xml_extensions_pattern = '%\.(csv|tsv|txt|dat|psv|json|xls|xlsx|sql|gz|gzip|zip)$%i';

        // Determine if we need to search for the XML file
        // This is needed when import->path stores:
        // 1. A URL (for URL-type imports, we store the original URL for display)
        // 2. A non-XML file path (for server file imports, we store the original file for display)
        $is_url = preg_match('%^https?://%i', $import->path);
        $is_non_xml_file = preg_match($non_xml_extensions_pattern, $import->path);

        if ($is_url || $is_non_xml_file) {
            $source_type = $is_url ? 'URL' : strtoupper(pathinfo($import->path, PATHINFO_EXTENSION));
            WPAI_Bridge_Logger::debug("AI Bridge get_import_file_path: {$source_type} source detected, looking for processed XML file");

            // Get the base filename for matching (works for both URLs and file paths)
            // Handle compressed files (e.g., file.csv.gz -> file, not file.csv)
            if ($is_url) {
                $source_basename = pathinfo(parse_url($import->path, PHP_URL_PATH), PATHINFO_FILENAME);
            } else {
                $source_basename = pathinfo($import->path, PATHINFO_FILENAME);
            }
            // Strip additional extension if it's a compressed file (file.csv.gz -> file.csv -> file)
            // Also handles double extensions like .tar.gz
            if (preg_match('%\.(gz|gzip|zip)$%i', $import->path)) {
                $source_basename = pathinfo($source_basename, PATHINFO_FILENAME);
            }

            // Strategy 1: Try file history first (most reliable for existing imports)
            // File history records store the path to the processed XML file
            WPAI_Bridge_Logger::debug( 'AI Bridge get_import_file_path: Strategy 1 - Checking file history...' );
            $history_file = new PMXI_File_Record();
            $history_file->getBy(array('import_id' => $import->id), 'id DESC');
            WPAI_Bridge_Logger::debug( 'AI Bridge:   history_file->isEmpty() = ' . ( $history_file->isEmpty() ? 'TRUE' : 'FALSE' ) );
            if (!$history_file->isEmpty()) {
                WPAI_Bridge_Logger::debug( 'AI Bridge:   history_file->path = ' . $history_file->path );
                $history_path = wp_all_import_get_absolute_path($history_file->path);
                WPAI_Bridge_Logger::debug( 'AI Bridge:   absolute path = ' . $history_path );
                WPAI_Bridge_Logger::debug( 'AI Bridge:   file_exists = ' . ( file_exists($history_path) ? 'TRUE' : 'FALSE' ) );
                if (file_exists($history_path)) {
                    // IMPORTANT: Ensure the file is in the correct secure directory for THIS import
                    // When reusing files from file picker, the XML might be in another import's directory
                    // which can get cleaned up. Copy it to the current import's directory.
                    $wp_uploads = wp_upload_dir();
                    $uploads_base = $wp_uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::UPLOADS_DIRECTORY;
                    $is_secure = PMXI_Plugin::getInstance()->getOption('secure');

                    if ($is_secure) {
                        $nonce_salt = defined('NONCE_SALT') ? NONCE_SALT : wp_salt('nonce');
                        $expected_dir = $uploads_base . DIRECTORY_SEPARATOR . md5($import->id . $nonce_salt);
                    } else {
                        $expected_dir = $uploads_base;
                    }

                    // ALWAYS ensure the directory exists (it might have been cleaned up)
                    if (!is_dir($expected_dir)) {
                        WPAI_Bridge_Logger::debug("AI Bridge get_import_file_path: Expected directory doesn't exist, creating: {$expected_dir}");
                        wp_mkdir_p($expected_dir);
                        @touch($expected_dir . DIRECTORY_SEPARATOR . 'index.php');
                    }

                    // Calculate the expected file path
                    $expected_path = $expected_dir . DIRECTORY_SEPARATOR . basename($history_path);

                    // If the current path is already correct AND file exists there, use it
                    if ($history_path === $expected_path && file_exists($expected_path)) {
                        WPAI_Bridge_Logger::debug("AI Bridge get_import_file_path: SUCCESS - File is in correct location: {$expected_path}");
                        return $expected_path;
                    }

                    // Otherwise, copy the file to the expected location to ensure it persists
                    if ($history_path !== $expected_path) {
                        WPAI_Bridge_Logger::debug("AI Bridge get_import_file_path: File path mismatch, copying");
                        WPAI_Bridge_Logger::debug("AI Bridge:   history_path = {$history_path}");
                        WPAI_Bridge_Logger::debug("AI Bridge:   expected_path = {$expected_path}");
                    } else {
                        WPAI_Bridge_Logger::debug("AI Bridge get_import_file_path: File doesn't exist at expected path, copying from history");
                    }

                    if (copy($history_path, $expected_path)) {
                        WPAI_Bridge_Logger::debug("AI Bridge get_import_file_path: SUCCESS - Copied to {$expected_path}");
                        return $expected_path;
                    } else {
                        WPAI_Bridge_Logger::debug("AI Bridge get_import_file_path: ERROR - Failed to copy, using history path");
                        return $history_path;
                    }
                } else {
                    WPAI_Bridge_Logger::debug("AI Bridge get_import_file_path: FAIL - File history path doesn't exist on disk");
                }

                // For Excel files, history might point to .xlsx but XML is in same directory
                if (preg_match('%\.(xls|xlsx)$%i', $history_path)) {
                    $history_dir = dirname($history_path);
                    $history_basename = pathinfo($history_path, PATHINFO_FILENAME);
                    $xml_file = $history_dir . DIRECTORY_SEPARATOR . $history_basename . '.xml';
                    if (file_exists($xml_file)) {
                        WPAI_Bridge_Logger::debug("AI Bridge get_import_file_path: Found XML for Excel file: {$xml_file}");
                        return $xml_file;
                    }
                    // Try any XML in that directory
                    $xml_files = glob($history_dir . '/*.xml');
                    if (!empty($xml_files)) {
                        WPAI_Bridge_Logger::debug("AI Bridge get_import_file_path: Using XML in Excel directory: {$xml_files[0]}");
                        return $xml_files[0];
                    }
                }
            }

            // Strategy 2: Try session filePath (for fresh imports not yet processed)
            if (!empty(PMXI_Plugin::$session) && !empty(PMXI_Plugin::$session->filePath)) {
                $session_path = wp_all_import_get_absolute_path(PMXI_Plugin::$session->filePath);
                if (file_exists($session_path)) {
                    WPAI_Bridge_Logger::debug("AI Bridge get_import_file_path: Found XML via session: {$session_path}");
                    return $session_path;
                }
            }

            // Strategy 3: Search wpallimport/uploads directories for matching XML
            $uploads = wp_upload_dir();
            $uploads_base = $uploads['basedir'] . DIRECTORY_SEPARATOR . WP_ALL_IMPORT_UPLOADS_BASE_DIRECTORY;
            if (is_dir($uploads_base)) {
                $subdirs = glob($uploads_base . '/*', GLOB_ONLYDIR);
                $candidate_files = array();

                foreach ($subdirs as $subdir) {
                    // First try exact basename match
                    $xml_file = $subdir . DIRECTORY_SEPARATOR . $source_basename . '.xml';
                    if (file_exists($xml_file)) {
                        WPAI_Bridge_Logger::debug("AI Bridge get_import_file_path: Found XML by basename match: {$xml_file}");
                        return $xml_file;
                    }

                    // Collect all XML files as potential candidates
                    $xml_files = glob($subdir . '/*.xml');
                    foreach ($xml_files as $xml) {
                        $candidate_files[] = array(
                            'path' => $xml,
                            'mtime' => filemtime($xml),
                            'dir' => $subdir,
                        );
                    }
                }

                // If we have candidates, return the most recently modified one
                if (!empty($candidate_files)) {
                    usort($candidate_files, function($a, $b) {
                        return $b['mtime'] - $a['mtime'];
                    });
                    WPAI_Bridge_Logger::debug("AI Bridge get_import_file_path: Using most recent XML file: {$candidate_files[0]['path']}");
                    return $candidate_files[0]['path'];
                }
            }

            // No XML file found - return error
            return new WP_Error(
                'rest_not_found',
                sprintf(
                    __('Processed XML file not found for %s import. The original file may need to be re-imported.', 'wp_all_import_plugin'),
                    $source_type
                ),
                array('status' => 404)
            );
        }

        // For XML files or other paths, try to use directly
        if (file_exists($file_path)) {
            return $file_path;
        }

        // If import path doesn't exist, try file history (like WP All Import does for existing imports)
        $history_file = new PMXI_File_Record();
        $history_file->getBy(array('import_id' => $import->id), 'id DESC');

        if (!$history_file->isEmpty()) {
            $file_path = wp_all_import_get_absolute_path($history_file->path);

            // Handle Excel files that get converted to XML
            if (preg_match('%\W(xlsx|xls)$%i', $file_path)) {
                $dir = dirname($file_path);
                $basename = pathinfo($file_path, PATHINFO_FILENAME);

                $xml_file = $dir . '/' . $basename . '.xml';
                if (file_exists($xml_file)) {
                    return $xml_file;
                }

                $xml_files = glob($dir . '/*.xml');
                if (!empty($xml_files)) {
                    return $xml_files[0];
                }
            }

            if (file_exists($file_path)) {
                return $file_path;
            }
        }

        // Last resort: try session filePath
        if (!empty(PMXI_Plugin::$session) && !empty(PMXI_Plugin::$session->filePath)) {
            $file_path = wp_all_import_get_absolute_path(PMXI_Plugin::$session->filePath);
            if (file_exists($file_path)) {
                return $file_path;
            }
        }

        return new WP_Error(
            'rest_not_found',
            sprintf(
                __('Import file not found. Path: %s (resolved from: %s)', 'wp_all_import_plugin'),
                wp_all_import_get_absolute_path($import->path),
                $import->path
            ),
            array('status' => 404)
        );
    }

    /**
     * Get known add-on field prefixes that use bracket notation
     *
     * These prefixes identify fields that belong to WP All Import add-ons
     * and should be parsed into nested arrays rather than treated as custom fields.
     *
     * @return array Array of prefix patterns (strings or regex patterns)
     */
    private function get_addon_field_prefixes() {
        $prefixes = array(
            // User Import Add-On (import_users)
            'pmui',
            // WooCommerce Customer Import (shop_customer)
            'pmsci_customer',
            'pmsci_',  // Other pmsci_ prefixed fields
            // Gravity Forms Import Add-On (gf_entries)
            'pmgi',
            // MetaBox Import Add-On
            'pmmi',
            // JetEngine Import Add-On
            'pmji',
            // WooCommerce Orders Add-On (shop_order)
            'pmwi_order',
            // WooCommerce Products Add-On (product)
            'pmwi',
            // Comments Import
            'pmci',
        );

        /**
         * Filter the list of add-on field prefixes
         *
         * @param array $prefixes Array of prefix strings
         */
        return apply_filters('wpai_llm_bridge_addon_prefixes', $prefixes);
    }

    /**
     * Extract and parse add-on fields from flat template array
     *
     * Converts flat bracket notation keys like:
     *   "pmui[login]" => "{username[1]}"
     *   "pmsci_customer[email]" => "{email[1]}"
     *   "pmgi[status]" => "active"
     *   "pmwi_order[status]" => "wc-completed"
     *
     * Into nested array structure that add-ons expect:
     *   pmui => ['login' => '{username[1]}']
     *   pmsci_customer => ['email' => '{email[1]}']
     *   pmgi => ['status' => 'active']
     *   pmwi_order => ['status' => 'wc-completed']
     *
     * Uses dynamic detection instead of explicit prefix lists:
     * - Checks if prefix already exists as array in current options (add-on namespace)
     * - Checks if prefix matches common add-on patterns (pm[a-z]{2,})
     * - Checks for pmsci_ pattern (WooCommerce customer)
     *
     * @param array $template Reference to template array (add-on keys will be removed)
     * @param array $current_options Current import options for checking existing namespaces
     * @return array Parsed add-on fields in nested structure
     */
    private function extract_addon_fields(&$template, $current_options = array()) {
        $addon_data = array();
        $keys_to_remove = array();

        // New addon-API slugs (MetaBox, Pods, JetEngine, etc.). Their field values arrive as
        // <slug>[field_key] but the slug is not "pm*"-prefixed and, in the MCP configure-import
        // path, the import record has no pre-seeded <slug> array to key off of -- so without this
        // list those keys would fall through to the generic loop and be stored as the literal
        // bogus top-level key "metabox[field]" instead of $options['metabox']['field'], and never
        // import. (JetEngine only escaped this via its own bespoke fix_jetengine_field_names pass.)
        $new_api_slugs = array();
        if ( class_exists( '\Wpai\AddonAPI\PMXI_Addon_Manager' ) ) {
            foreach ( \Wpai\AddonAPI\PMXI_Addon_Manager::get_addons() as $addon ) {
                if ( ! empty( $addon->slug ) ) {
                    $new_api_slugs[] = $addon->slug;
                }
            }
        }

        foreach ($template as $key => $value) {
            // Check if this key uses bracket notation (e.g., pmui[login], pmwi_order[status])
            if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\[/', $key, $matches)) {
                $prefix = $matches[1];
                $is_addon_field = false;

                // Dynamic detection of add-on fields:
                // 1. If the prefix already exists as an array in options, it's definitely an add-on
                if (isset($current_options[$prefix]) && is_array($current_options[$prefix])) {
                    $is_addon_field = true;
                }
                // 2. Common add-on prefix patterns: pmXX, pmXX_something (pmui, pmwi, pmwi_order, pmgi, etc.)
                elseif (preg_match('/^pm[a-z]{2,}/', $prefix)) {
                    $is_addon_field = true;
                }
                // 3. Also check for pmsci_ pattern (WooCommerce Customers Add-On)
                elseif (strpos($prefix, 'pmsci_') === 0) {
                    $is_addon_field = true;
                }
                // 4. Registered new addon-API slugs (metabox, pods, jetengine, ...)
                elseif (in_array($prefix, $new_api_slugs, true)) {
                    $is_addon_field = true;
                }

                if ($is_addon_field) {
                    // Parse the bracket notation into nested array
                    $parsed = $this->parse_bracket_notation($key, $value);

                    // Merge into addon_data
                    if (!empty($parsed[$prefix])) {
                        if (!isset($addon_data[$prefix])) {
                            $addon_data[$prefix] = array();
                        }
                        // Only use recursive merge if both are arrays
                        // For simple values like jetengine_groups[] = "Test MB", just assign directly
                        if (is_array($parsed[$prefix]) && is_array($addon_data[$prefix])) {
                            $addon_data[$prefix] = $this->array_merge_recursive_distinct(
                                $addon_data[$prefix],
                                $parsed[$prefix]
                            );
                        } else {
                            // Handle array fields (like jetengine_groups[]) that can have multiple values
                            if (substr($key, -2) === '[]') {
                                // It's an array field - append to array
                                if (!is_array($addon_data[$prefix])) {
                                    $addon_data[$prefix] = array();
                                }
                                $addon_data[$prefix][] = $parsed[$prefix];
                            } else {
                                // Simple value assignment
                                $addon_data[$prefix] = $parsed[$prefix];
                            }
                        }
                    } elseif (!empty($parsed)) {
                        // Handle nested prefixes like pmsci_customer
                        if (is_array($parsed)) {
                            $addon_data = $this->array_merge_recursive_distinct($addon_data, $parsed);
                        }
                    }

                    $keys_to_remove[] = $key;
                }
            }
        }

        // Remove processed add-on keys from template
        foreach ($keys_to_remove as $key) {
            unset($template[$key]);
        }

        return $addon_data;
    }

    /**
     * Extract and parse ACF fields from flat template array
     *
     * Converts flat bracket notation keys like:
     *   "acf[example-group]" => "1"
     *   "fields[field_xxx][url]" => "{image[1]}"
     *   "is_multiple_field_value[field_xxx]" => "no"
     *   "multiple_value[field_xxx]" => "option_value"
     *
     * Into nested array structure that WP All Import ACF Add-on expects:
     *   acf => ['example-group' => '1']
     *   fields => ['field_xxx' => ['url' => '{image[1]}']]
     *   is_multiple_field_value => ['field_xxx' => 'no']
     *   multiple_value => ['field_xxx' => 'option_value']
     *
     * @param array $template Reference to template array (ACF keys will be removed)
     * @return array Parsed ACF fields in nested structure
     */
    private function extract_acf_fields(&$template) {
        $acf_data = array(
            'acf' => array(),
            'fields' => array(),
            'is_multiple_field_value' => array(),
            'multiple_value' => array(),
        );

        $keys_to_remove = array();

        foreach ($template as $key => $value) {
            // Field group toggles: acf[group-slug]
            if (preg_match('/^acf\[([^\]]+)\]$/', $key, $matches)) {
                $acf_data['acf'][$matches[1]] = $value;
                $keys_to_remove[] = $key;
                continue;
            }

            // ACF field values: fields[field_xxx] or fields[field_xxx][subkey]...
            if (preg_match('/^fields\[/', $key)) {
                $parsed = $this->parse_bracket_notation($key, $value);
                if (isset($parsed['fields'])) {
                    $acf_data['fields'] = $this->array_merge_recursive_distinct(
                        $acf_data['fields'],
                        $parsed['fields']
                    );
                }
                $keys_to_remove[] = $key;
                continue;
            }

            // Mode selectors: is_multiple_field_value[field_xxx] or nested
            if (preg_match('/^is_multiple_field_value\[/', $key)) {
                $parsed = $this->parse_bracket_notation($key, $value);
                if (isset($parsed['is_multiple_field_value'])) {
                    $acf_data['is_multiple_field_value'] = $this->array_merge_recursive_distinct(
                        $acf_data['is_multiple_field_value'],
                        $parsed['is_multiple_field_value']
                    );
                }
                $keys_to_remove[] = $key;
                continue;
            }

            // Fixed values: multiple_value[field_xxx] or nested
            if (preg_match('/^multiple_value\[/', $key)) {
                $parsed = $this->parse_bracket_notation($key, $value);
                if (isset($parsed['multiple_value'])) {
                    $acf_data['multiple_value'] = $this->array_merge_recursive_distinct(
                        $acf_data['multiple_value'],
                        $parsed['multiple_value']
                    );
                }
                $keys_to_remove[] = $key;
                continue;
            }
        }

        // Remove processed ACF keys from template
        foreach ($keys_to_remove as $key) {
            unset($template[$key]);
        }

        // ALSO extract ACF fields from custom_name[]/custom_value[] arrays
        // This handles repeater row fields and other nested ACF data that the LLM
        // may return in the custom fields format
        // Note: The key might be 'custom_name' OR 'custom_name[]' depending on how it was sent
        $custom_names = null;
        $custom_values = null;
        $custom_name_key = null;
        $custom_value_key = null;

        // Check for both key formats
        if (!empty($template['custom_name'])) {
            $custom_names = $template['custom_name'];
            $custom_name_key = 'custom_name';
        } elseif (!empty($template['custom_name[]'])) {
            $custom_names = $template['custom_name[]'];
            $custom_name_key = 'custom_name[]';
        }

        if (!empty($template['custom_value'])) {
            $custom_values = $template['custom_value'];
            $custom_value_key = 'custom_value';
        } elseif (!empty($template['custom_value[]'])) {
            $custom_values = $template['custom_value[]'];
            $custom_value_key = 'custom_value[]';
        }

        // JSON-decode if values are strings (LLM might send JSON-encoded arrays)
        if (is_string($custom_names)) {
            $decoded = json_decode($custom_names, true);
            if (is_array($decoded)) {
                $custom_names = $decoded;
                WPAI_Bridge_Logger::debug('AI Bridge: JSON-decoded custom_name array with ' . count($decoded) . ' items');
            }
        }
        if (is_string($custom_values)) {
            $decoded = json_decode($custom_values, true);
            if (is_array($decoded)) {
                $custom_values = $decoded;
                WPAI_Bridge_Logger::debug('AI Bridge: JSON-decoded custom_value array with ' . count($decoded) . ' items');
            }
        }

        if (!empty($custom_names) && is_array($custom_names) &&
            !empty($custom_values) && is_array($custom_values)) {

            $indices_to_remove = array();

            foreach ($custom_names as $index => $name) {
                if (!is_string($name)) {
                    continue;
                }

                $value = isset($custom_values[$index]) ? $custom_values[$index] : '';

                // ACF field values: fields[field_xxx] or fields[field_xxx][rows][N][subfield]...
                if (preg_match('/^fields\[/', $name)) {
                    $parsed = $this->parse_bracket_notation($name, $value);
                    if (isset($parsed['fields'])) {
                        $acf_data['fields'] = $this->array_merge_recursive_distinct(
                            $acf_data['fields'],
                            $parsed['fields']
                        );
                    }
                    $indices_to_remove[] = $index;
                    continue;
                }

                // Mode selectors: is_multiple_field_value[field_xxx]
                if (preg_match('/^is_multiple_field_value\[/', $name)) {
                    $parsed = $this->parse_bracket_notation($name, $value);
                    if (isset($parsed['is_multiple_field_value'])) {
                        $acf_data['is_multiple_field_value'] = $this->array_merge_recursive_distinct(
                            $acf_data['is_multiple_field_value'],
                            $parsed['is_multiple_field_value']
                        );
                    }
                    $indices_to_remove[] = $index;
                    continue;
                }

                // Fixed values: multiple_value[field_xxx]
                if (preg_match('/^multiple_value\[/', $name)) {
                    $parsed = $this->parse_bracket_notation($name, $value);
                    if (isset($parsed['multiple_value'])) {
                        $acf_data['multiple_value'] = $this->array_merge_recursive_distinct(
                            $acf_data['multiple_value'],
                            $parsed['multiple_value']
                        );
                    }
                    $indices_to_remove[] = $index;
                    continue;
                }

                // ACF group toggles: acf[group-slug]
                if (preg_match('/^acf\[([^\]]+)\]$/', $name, $matches)) {
                    $acf_data['acf'][$matches[1]] = $value;
                    $indices_to_remove[] = $index;
                    continue;
                }
            }

            // Remove processed ACF entries from custom_name/custom_value
            if (!empty($indices_to_remove)) {
                foreach ($indices_to_remove as $idx) {
                    unset($custom_names[$idx]);
                    unset($custom_values[$idx]);
                }
                // Re-index arrays to maintain sequential keys
                $custom_names = array_values($custom_names);
                $custom_values = array_values($custom_values);

                // Update the template with filtered arrays
                if ($custom_name_key) {
                    $template[$custom_name_key] = $custom_names;
                }
                if ($custom_value_key) {
                    $template[$custom_value_key] = $custom_values;
                }

                WPAI_Bridge_Logger::debug('AI Bridge: Extracted ' . count($indices_to_remove) . ' ACF fields from custom_name/custom_value arrays');
            }
        }

        // Auto-generate is_multiple_field_value for select/checkbox fields with XPath values
        // ACF select/checkbox/radio fields need is_multiple_field_value[field_key] = "no" to enable XPath mode
        // Without this, the ACF add-on defaults to "fixed value" mode and ignores the XPath
        // We detect this by looking for simple field values (not nested like [url] or [rows])
        // that contain XPath syntax ({ and })
        if (!empty($acf_data['fields'])) {
            foreach ($acf_data['fields'] as $field_key => $field_value) {
                // Only process if:
                // 1. Value is a string (not a nested array like image fields with [url])
                // 2. Contains XPath syntax
                // 3. Doesn't already have a mode selector set
                if (is_string($field_value) &&
                    strpos($field_value, '{') !== false &&
                    strpos($field_value, '}') !== false &&
                    !isset($acf_data['is_multiple_field_value'][$field_key])) {

                    $acf_data['is_multiple_field_value'][$field_key] = 'no';
                    WPAI_Bridge_Logger::debug("AI Bridge: Auto-generated is_multiple_field_value[$field_key] = 'no' for XPath value");
                }
            }
        }

        return $acf_data;
    }

    /**
     * Parse bracket notation string into nested array
     *
     * Converts "fields[field_xxx][rows][ROWNUMBER][field_yyy]" with value "test"
     * into: ['fields' => ['field_xxx' => ['rows' => ['ROWNUMBER' => ['field_yyy' => 'test']]]]]
     *
     * @param string $key The bracket notation key
     * @param mixed $value The value to set
     * @return array Nested array structure
     */
    private function parse_bracket_notation($key, $value) {
        // Extract all bracket parts: "fields[a][b][c]" => ["fields", "a", "b", "c"]
        preg_match_all('/([^\[\]]+)/', $key, $matches);
        $parts = $matches[1];

        if (empty($parts)) {
            return array();
        }

        // Build nested array from inside out
        $result = $value;
        for ($i = count($parts) - 1; $i >= 0; $i--) {
            $result = array($parts[$i] => $result);
        }

        return $result;
    }

    /**
     * Filter orphaned mode selectors that don't have corresponding value fields
     *
     * ACF taxonomy/select fields need both:
     * - is_multiple_field_value[field_key] = "no" (to enable XPath mode)
     * - fields[field_key][value] = "{xpath}" (the actual value)
     *
     * If a mode selector exists without a corresponding meaningful value field,
     * it results in the UI showing "Set with XPath" mode with an empty value box.
     * This function removes such orphaned mode selectors.
     *
     * @param array $mode_selectors Nested array of is_multiple_field_value settings
     * @param array $fields Nested array of fields settings
     * @return array Filtered mode selectors
     */
    private function filter_orphaned_mode_selectors( array $mode_selectors, array $fields ) {
        $filtered = array();

        foreach ( $mode_selectors as $key => $value ) {
            if ( is_array( $value ) ) {
                // Recurse into nested arrays (for repeater fields)
                $filtered_nested = $this->filter_orphaned_mode_selectors( $value, $fields[ $key ] ?? array() );
                if ( ! empty( $filtered_nested ) ) {
                    $filtered[ $key ] = $filtered_nested;
                }
            } else {
                // Leaf node - check if there's a corresponding value field with meaningful content
                $has_meaningful_value = false;

                if ( isset( $fields[ $key ] ) ) {
                    // Handle string values directly (select, checkbox, radio fields)
                    if ( is_string( $fields[ $key ] ) ) {
                        $field_value = $fields[ $key ];
                        // Check if it's a meaningful XPath (contains { and })
                        if ( ! empty( $field_value ) && strpos( $field_value, '{' ) !== false && strpos( $field_value, '}' ) !== false ) {
                            $has_meaningful_value = true;
                        }
                    }
                    // Handle array values (taxonomy fields with [value] subkey, etc.)
                    elseif ( is_array( $fields[ $key ] ) ) {
                        // Check for [value] subkey (taxonomy fields)
                        if ( isset( $fields[ $key ]['value'] ) ) {
                            $field_value = $fields[ $key ]['value'];
                            if ( ! empty( $field_value ) && strpos( $field_value, '{' ) !== false && strpos( $field_value, '}' ) !== false ) {
                                $has_meaningful_value = true;
                            }
                        }
                    }
                }

                if ( $has_meaningful_value ) {
                    $filtered[ $key ] = $value;
                } else {
                    WPAI_Bridge_Logger::debug( "AI Bridge: Removing orphaned mode selector for field '{$key}' (no corresponding value)" );
                }
            }
        }

        return $filtered;
    }

    /**
     * Recursively merge arrays, with later values overwriting earlier ones
     * (unlike array_merge_recursive which creates arrays of values)
     *
     * @param array $array1 Base array
     * @param array $array2 Array to merge in
     * @return array Merged array
     */
    private function array_merge_recursive_distinct(array $array1, array $array2) {
        $merged = $array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->array_merge_recursive_distinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Get cron URLs for running an import
     * Returns the trigger and processing URLs with the import key
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_cron_urls($request) {
        $import_id = $request['id'];

        // Get import record
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error(
                'import_not_found',
                'Import not found.',
                array('status' => 404)
            );
        }

        // Get the cron job key from WP All Import options
        $cron_job_key = PMXI_Plugin::getInstance()->getOption('cron_job_key');

        // Build URLs with cache buster to prevent caching layers from serving stale responses
        $base_url = home_url('/wp-load.php');
        $cache_buster = time() . '-' . wp_rand(1000, 9999);

        $trigger_url = add_query_arg(array(
            'import_key' => $cron_job_key,
            'import_id' => $import_id,
            'action' => 'trigger',
            '_' => $cache_buster
        ), $base_url);

        $processing_url = add_query_arg(array(
            'import_key' => $cron_job_key,
            'import_id' => $import_id,
            'action' => 'processing',
            '_' => $cache_buster
        ), $base_url);

        return new WP_REST_Response(array(
            'success' => true,
            'import_id' => $import_id,
            'import_name' => $import->name,
            'trigger_url' => $trigger_url,
            'processing_url' => $processing_url,
            'cron_key' => $cron_job_key,
        ), 200);
    }

    /**
     * Get import status
     * Returns current progress and state of an import
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_import_status($request) {
        $import_id = $request['id'];

        // Get import record
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error(
                'import_not_found',
                'Import not found.',
                array('status' => 404)
            );
        }

        // Determine import state
        $is_processing = (int)$import->processing === 1;
        $is_triggered = (int)$import->triggered === 1;
        $is_complete = (int)$import->imported >= (int)$import->count && !$is_processing;
        $is_failed = (int)$import->failed === 1;
        $is_canceled = (int)$import->canceled === 1;

        // Calculate progress percentage based on all processed records (imported + skipped)
        $progress = 0;
        $total_processed = (int)$import->imported + (int)$import->skipped;
        if ($import->count > 0) {
            $progress = round(($total_processed / $import->count) * 100, 2);
        }

        // Use safe response to prevent any PHP warnings from corrupting JSON
        return $this->create_safe_rest_response(array(
            'success' => true,
            'import_id' => $import_id,
            'import_name' => $import->name,
            'status' => array(
                'is_processing' => $is_processing,
                'is_triggered' => $is_triggered,
                'is_complete' => $is_complete,
                'is_failed' => $is_failed,
                'is_canceled' => $is_canceled,
            ),
            'progress' => array(
                'total' => (int)$import->count,
                'imported' => (int)$import->imported,
                'created' => (int)$import->created,
                'updated' => (int)$import->updated,
                'skipped' => (int)$import->skipped,
                'percentage' => $progress,
            ),
            'last_activity' => $import->last_activity,
        ), 200);
    }

    /**
     * Trigger import execution (proxy for wp-load.php?action=trigger)
     *
     * This endpoint proxies the cron trigger URL to avoid CORS issues when calling from Vercel UI.
     * It internally calls the wp-load.php?action=trigger URL server-side.
     */
    public function trigger_import($request) {
        // Start output buffering to catch any output that might leak
        ob_start();

        try {
            $import_id = $request['id'];

            // Get import to verify it exists
            $import = new PMXI_Import_Record();
            $import->getById($import_id);

            if ($import->isEmpty()) {
                ob_end_clean();
                return new WP_Error('import_not_found', 'Import not found.', array('status' => 404));
            }

            WPAI_Bridge_Logger::debug("[LLM Config API] Triggering import $import_id (type: {$import->type})");

            // Note: We don't validate import type here because unlike wp_loaded_99.php (which is for
            // recurring scheduled imports), we're using the cron mechanism for one-time chunked execution.
            // The "upload" type restriction in wp_loaded_99.php is for recurring cron jobs that need to
            // re-download files, but our use case is just running an already-uploaded file in chunks.

            // Check if already executing (active manual process — do not interrupt)
            if ((int)$import->executing) {
                ob_end_clean();
                return new WP_Error(
                    'import_executing',
                    sprintf(__('Import #%s is currently in manually process. Request skipped.', 'wpai-bridge'), $import_id),
                    array('status' => 403)
                );
            }

            // Reset any previous run state before triggering.
            // The trigger endpoint is ONLY called after (re-)configuration, so the import
            // should always start fresh. A previous run may have left triggered=1, processing=1,
            // or canceled=1 — clear all of these so the new trigger starts from the beginning.
            $needs_reset = (int)$import->triggered || (int)$import->processing || (int)$import->canceled;
            if ($needs_reset) {
                WPAI_Bridge_Logger::debug("[LLM Config API] Resetting import $import_id state before trigger (triggered={$import->triggered}, processing={$import->processing}, canceled={$import->canceled})");
                $import->set(array(
                    'triggered'   => 0,
                    'processing'  => 0,
                    'canceled'    => 0,
                    'canceled_on' => '',
                ))->update();
            }

            // Trigger using WP All Import's scheduler class
            $scheduledImport = new \Wpai\Scheduling\Import();
            $history_log = $scheduledImport->trigger($import);

            WPAI_Bridge_Logger::debug("[LLM Config API] Import $import_id triggered successfully");

            // Clean output buffer before creating response
            ob_end_clean();

            // Use safe response to prevent any PHP warnings from corrupting JSON
            $response = $this->create_safe_rest_response(array(
                'success' => true,
                'import_id' => $import_id,
                'message' => sprintf(__('Import #%s triggered successfully', 'wpai-bridge'), $import_id),
            ), 200);

            // Prevent caching of this response
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->header('Pragma', 'no-cache');

            return $response;
        } catch (Exception $e) {
            WPAI_Bridge_Logger::debug("[LLM Config API] Trigger failed with exception: " . $e->getMessage());
            ob_end_clean();
            return new WP_Error('trigger_failed', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Process import chunk (proxy for wp-load.php?action=processing)
     *
     * This endpoint proxies the cron processing URL to avoid CORS issues when calling from Vercel UI.
     * It internally calls the wp-load.php?action=processing URL server-side.
     */
    public function process_import($request) {
        $import_id = $request['id'];

        WPAI_Bridge_Logger::debug("[LLM Config API] process_import called for import $import_id");

        // Get import to verify it exists
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error('import_not_found', 'Import not found.', array('status' => 404));
        }

        // Check if import was canceled - fail early
        if ((int)$import->canceled === 1) {
            WPAI_Bridge_Logger::debug("[LLM Config API] Import $import_id was canceled");
            return $this->create_safe_rest_response(array(
                'success' => true,
                'should_continue' => false,
                'status' => array(
                    'is_processing' => false,
                    'is_triggered' => false,
                    'is_complete' => false,
                    'is_failed' => false,
                    'is_canceled' => true,
                ),
                'progress' => array(
                    'total' => (int)$import->count,
                    'imported' => (int)$import->imported,
                    'created' => (int)$import->created,
                    'updated' => (int)$import->updated,
                    'skipped' => (int)$import->skipped,
                    'percentage' => $import->count > 0 ? round((((int)$import->imported + (int)$import->skipped) / $import->count) * 100, 2) : 0,
                ),
                'message' => 'Import was canceled',
            ), 200);
        }

        // Check if import is complete first (before checking triggered flag)
        // WP All Import resets triggered=0 when complete, so we need to check completion before erroring on triggered
        $is_complete = (int)$import->queue_chunk_number === 0 && (int)$import->imported > 0;

        if ($is_complete) {
            WPAI_Bridge_Logger::debug("[LLM Config API] Import $import_id is already complete (imported={$import->imported}, queue_chunk=0)");

            // Calculate progress based on all processed records (imported + skipped)
            $progress = 0;
            $total_processed = (int)$import->imported + (int)$import->skipped;
            if ($import->count > 0) {
                $progress = round(($total_processed / $import->count) * 100, 2);
            }

            // Return completion response - use safe response to prevent any PHP warnings from corrupting JSON
            $response = $this->create_safe_rest_response(array(
                'success' => true,
                'should_continue' => false,
                'status' => array(
                    'is_processing' => false,
                    'is_triggered' => false,
                    'is_complete' => true,
                    'is_failed' => (int)$import->failed === 1,
                    'is_canceled' => (int)$import->canceled === 1,
                ),
                'progress' => array(
                    'total' => (int)$import->count,
                    'imported' => (int)$import->imported,
                    'created' => (int)$import->created,
                    'updated' => (int)$import->updated,
                    'skipped' => (int)$import->skipped,
                    'percentage' => $progress,
                ),
            ), 200);

            // Prevent caching of this response
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->header('Pragma', 'no-cache');

            return $response;
        }

        // Check if not triggered yet
        if (!(int)$import->triggered) {
            WPAI_Bridge_Logger::debug("[LLM Config API] Import $import_id not triggered (triggered={$import->triggered})");
            return new WP_Error(
                'not_triggered',
                sprintf(__('Import #%s is not triggered. Request skipped.', 'wpai-bridge'), $import_id),
                array('status' => 403)
            );
        }

        // Check if already executing manually
        if ((int)$import->executing) {
            WPAI_Bridge_Logger::debug("[LLM Config API] Import $import_id already executing (executing={$import->executing})");
            return new WP_Error(
                'import_executing',
                sprintf(__('Import #%s is currently in manually process. Request skipped.', 'wpai-bridge'), $import_id),
                array('status' => 403)
            );
        }

        // Check if already processing (another cron is running)
        if ((int)$import->processing) {
            WPAI_Bridge_Logger::debug("[LLM Config API] Import $import_id has processing=1 (triggered={$import->triggered}, queue_chunk={$import->queue_chunk_number})");

            // Check for stale processing - if processing flag is set but import isn't actually running
            // This can happen when a previous request completed processing but crashed during response generation
            // We detect this by checking if the processing_started timestamp is too old (more than 60 seconds)
            $processing_timeout = 60; // seconds
            $last_activity = strtotime($import->registered_on);
            $time_since_activity = time() - $last_activity;

            // Also check if this might be a stuck state by looking at the transient lock
            $lock_key = 'pmxi_import_lock_' . $import_id;
            $is_locked = get_transient($lock_key);

            // If no lock exists OR we've been "processing" for too long, assume it's stale
            if (!$is_locked || $time_since_activity > $processing_timeout) {
                WPAI_Bridge_Logger::debug("[LLM Config API] Detected stale processing flag for import $import_id (locked=" . ($is_locked ? 'yes' : 'no') . ", time_since_activity={$time_since_activity}s). Resetting processing flag.");

                // Reset the processing flag so we can continue
                global $wpdb;
                $wpdb->update(
                    $wpdb->prefix . 'pmxi_imports',
                    array('processing' => 0),
                    array('id' => $import_id),
                    array('%d'),
                    array('%d')
                );

                // Refresh the import object
                $import->getById($import_id);
            } else {
                // Genuinely still processing
                WPAI_Bridge_Logger::debug("[LLM Config API] Import $import_id genuinely still processing (locked=yes, time_since_activity={$time_since_activity}s)");
                return new WP_Error(
                    'already_processing',
                    sprintf(__('Import #%s already processing. Request skipped.', 'wpai-bridge'), $import_id),
                    array('status' => 403)
                );
            }
        }

        WPAI_Bridge_Logger::debug("[LLM Config API] Starting processing for import $import_id");

        // Set a transient lock to indicate we're actually processing
        // This helps detect stale processing flags (where processing=1 but no actual processing is happening)
        $lock_key = 'pmxi_import_lock_' . $import_id;
        set_transient($lock_key, time(), 120); // 2 minute expiry

        // Start output buffering at the beginning to catch ANY output that might leak
        ob_start();

        // Suppress notices/warnings from going to stdout (they can corrupt JSON response)
        // Save current settings and restore after processing
        $old_display_errors = ini_get('display_errors');
        $old_error_reporting = error_reporting();
        ini_set('display_errors', '0');
        error_reporting(E_ERROR | E_PARSE); // Only report fatal errors during processing

        try {
            // Create a logger that both captures output AND echoes HTML for log file capture
            // The echo is critical - WP All Import's Scheduling/Import::process() uses ob_start()
            // to capture echoed output and write it to the log file
            // Limit to 100 messages to prevent memory issues during JSON serialization
            $log_messages = array();
            $max_log_messages = 100;
            $logger = function($m) use (&$log_messages, $max_log_messages) {
                // Echo in WP All Import's standard HTML format so it gets captured for log file
                // This matches the format from controllers/admin/import.php
                echo "<div class='progress-msg'>[" . date("H:i:s") . "] " . wp_all_import_filter_html_kses($m) . "</div>\n";

                // The echo above feeds WP All Import's own log-file capture (left
                // intact); but keep the JSON `log` array free of the context-free
                // "String expected as argument." type-guard noise the agent would
                // otherwise chase (already excluded from log_errors).
                if (wpai_bridge_is_spurious_log_line($m)) {
                    return;
                }

                // Also store in array for JSON response (limited to prevent memory issues)
                if (count($log_messages) < $max_log_messages) {
                    $log_messages[] = $m;
                } elseif (count($log_messages) === $max_log_messages) {
                    $log_messages[] = '[... additional log messages truncated ...]';
                }
            };
            $logger = apply_filters('wp_all_import_logger', $logger);

            // Initialize PMXI session with import_id before processing
            // This is required for addons (like WooCommerce) that look up the import from session
            // when their hooks are triggered during import processing
            if (empty(PMXI_Plugin::$session)) {
                PMXI_Plugin::$session = new PMXI_Handler();
            }
            PMXI_Plugin::$session->set('import_id', $import_id);
            PMXI_Plugin::$session->save_data();

            // Process using WP All Import's scheduler class
            $scheduledImport = new \Wpai\Scheduling\Import();
            $response = $scheduledImport->process($import, $logger);

            // Restore error settings
            ini_set('display_errors', $old_display_errors);
            error_reporting($old_error_reporting);

            // Delete the transient lock now that processing is complete
            delete_transient($lock_key);

            WPAI_Bridge_Logger::debug("[LLM Config API] Processing completed for import $import_id");

            // Refresh import record to get updated status
            $import->getById($import_id);

            // If process() returned a response, handle it
            if (!empty($response) && is_array($response)) {
                // This means there was an error or special condition
                if (isset($response['status']) && $response['status'] !== 200) {
                    // Clean output buffer before returning error
                    ob_end_clean();
                    return new WP_Error('processing_error', $response['message'], array('status' => $response['status']));
                }
            }

            // Calculate current state
            $is_processing = (int)$import->processing === 1;
            // Import is complete when queue_chunk_number is 0 (all chunks processed)
            // Note: WP All Import resets triggered flag to 0 when complete, so we don't check it
            $is_complete = (int)$import->queue_chunk_number === 0;
            $is_failed = (int)$import->failed === 1;
            $is_canceled = (int)$import->canceled === 1;

            // Calculate progress percentage based on all processed records (imported + skipped)
            $progress = 0;
            $total_processed = (int)$import->imported + (int)$import->skipped;
            if ($import->count > 0) {
                $progress = round(($total_processed / $import->count) * 100, 2);
            }

            $should_continue = !$is_complete && !$is_failed && !$is_canceled;

            WPAI_Bridge_Logger::debug("[LLM Config API] Import status: imported={$import->imported}/{$import->count}, queue_chunk={$import->queue_chunk_number}, triggered={$import->triggered}, is_complete=" . ($is_complete ? 'true' : 'false') . ", should_continue=" . ($should_continue ? 'true' : 'false'));

            // Clean output buffer before returning response
            ob_end_clean();

            // Use safe response to prevent any PHP warnings from corrupting JSON
            $response = $this->create_safe_rest_response(array(
                'success' => true,
                'import_id' => $import_id,
                'message' => $is_complete
                    ? sprintf(__('Import #%s complete', 'wpai-bridge'), $import_id)
                    : sprintf(__('Records Processed %s. Records Count %s.', 'wpai-bridge'), (int)$import->queue_chunk_number, (int)$import->count),
                'should_continue' => $should_continue,
                'status' => array(
                    'is_processing' => $is_processing,
                    'is_complete' => $is_complete,
                    'is_failed' => $is_failed,
                    'is_canceled' => $is_canceled,
                ),
                'progress' => array(
                    'total' => (int)$import->count,
                    'imported' => (int)$import->imported,
                    'created' => (int)$import->created,
                    'updated' => (int)$import->updated,
                    'skipped' => (int)$import->skipped,
                    'percentage' => $progress,
                    'queue_chunk_number' => (int)$import->queue_chunk_number,
                ),
                'log' => $log_messages,
            ), 200);

            // Prevent caching of this response
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->header('Pragma', 'no-cache');

            return $response;
        } catch (Exception $e) {
            // Restore error settings
            ini_set('display_errors', $old_display_errors);
            error_reporting($old_error_reporting);

            // Delete the transient lock on exception too
            delete_transient($lock_key);

            WPAI_Bridge_Logger::debug("[LLM Config API] Processing failed with exception: " . $e->getMessage());
            // Clean output buffer before returning error
            ob_end_clean();
            return new WP_Error('processing_failed', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Cancel a running import
     * Stops the import execution and marks it as canceled
     */
    public function cancel_import($request) {
        // Start output buffering to catch any output that might leak
        ob_start();

        try {
            $import_id = $request['id'];

            WPAI_Bridge_Logger::debug("[LLM Config API] cancel_import called for import $import_id");

            // Get import to verify it exists
            $import = new PMXI_Import_Record();
            $import->getById($import_id);

            if ($import->isEmpty()) {
                $captured = ob_get_clean();
                if (!empty($captured)) {
                    WPAI_Bridge_Logger::debug("[LLM Config API] Discarded output during cancel (import_not_found): " . substr($captured, 0, 200));
                }
                return new WP_Error('import_not_found', 'Import not found.', array('status' => 404));
            }

            // Run-state guard: only an import that is actually queued/running can be
            // canceled. Canceling one that never ran (or already finished) previously
            // faked success AND stranded canceled=1 permanently on a fresh import,
            // which then reads as is_canceled forever with no un-cancel path. When
            // nothing is running, no-op: report it clearly and leave state untouched.
            $is_running = ((int) $import->triggered === 1)
                || ((int) $import->processing === 1)
                || ((int) $import->executing === 1);
            if (!$is_running) {
                $already_canceled = ((int) $import->canceled === 1);
                $captured = ob_get_clean();
                if (!empty($captured)) {
                    WPAI_Bridge_Logger::debug("[LLM Config API] Discarded output during cancel (not_running): " . substr($captured, 0, 200));
                }
                return $this->create_safe_rest_response(array(
                    'success'   => true,
                    'import_id' => $import_id,
                    'canceled'  => false,
                    'message'   => $already_canceled
                        ? sprintf(__('Import #%s is not running; it was already marked canceled. No change made.', 'wpai-bridge'), $import_id)
                        : sprintf(__('Import #%s is not running, so there is nothing to cancel. Its state was left unchanged.', 'wpai-bridge'), $import_id),
                ), 200);
            }

            // Clean session data (this may output warnings)
            // PMXI_Plugin::$session may be null in REST API context
            if (!empty(PMXI_Plugin::$session) && method_exists(PMXI_Plugin::$session, 'clean_session')) {
                PMXI_Plugin::$session->clean_session($import_id);
            }

            // Mark import as canceled
            $import->set(array(
                'triggered' => 0,
                'processing' => 0,
                'executing' => 0,
                'canceled' => 1,
                'canceled_on' => date('Y-m-d H:i:s')
            ))->update();

            WPAI_Bridge_Logger::debug("[LLM Config API] Import $import_id canceled successfully");

            // Capture and log any output that was generated
            $captured = ob_get_clean();
            if (!empty($captured)) {
                WPAI_Bridge_Logger::debug("[LLM Config API] Discarded output during cancel: " . substr($captured, 0, 500));
            }

            // Use safe response to prevent any PHP warnings from corrupting JSON
            $response = $this->create_safe_rest_response(array(
                'success' => true,
                'import_id' => $import_id,
                'canceled' => true,
                'message' => sprintf(__('Import #%s canceled', 'wpai-bridge'), $import_id),
            ), 200);

            // Prevent caching of this response
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->header('Pragma', 'no-cache');

            return $response;
        } catch (Exception $e) {
            WPAI_Bridge_Logger::debug("[LLM Config API] Cancel failed with exception: " . $e->getMessage());
            $captured = ob_get_clean();
            if (!empty($captured)) {
                WPAI_Bridge_Logger::debug("[LLM Config API] Discarded output during cancel exception: " . substr($captured, 0, 200));
            }
            return new WP_Error('cancel_failed', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Clear stale processing lock
     *
     * This endpoint is called when the frontend detects repeated "already_processing"
     * responses with no actual progress being made. This indicates the transient lock
     * is stale (from a crashed process) and should be cleared.
     *
     * Safety: Only clears the lock if processing=1 but no progress is being made.
     * The frontend is responsible for tracking progress and only calling this
     * when it's confident the lock is stale.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function clear_processing_lock($request) {
        ob_start();

        try {
            $import_id = $request['id'];

            WPAI_Bridge_Logger::debug("[LLM Config API] clear_processing_lock called for import $import_id");

            // Get import to verify it exists
            $import = new PMXI_Import_Record();
            $import->getById($import_id);

            if ($import->isEmpty()) {
                $captured = ob_get_clean();
                if (!empty($captured)) {
                    WPAI_Bridge_Logger::debug("[LLM Config API] Discarded output during clear_processing_lock (import_not_found): " . substr($captured, 0, 200));
                }
                return new WP_Error('import_not_found', 'Import not found.', array('status' => 404));
            }

            // Delete the transient lock
            $lock_key = 'pmxi_import_lock_' . $import_id;
            $lock_existed = get_transient($lock_key) !== false;
            delete_transient($lock_key);

            // Also reset the processing flag to allow new processing requests
            $was_processing = (int)$import->processing === 1;
            if ($was_processing) {
                global $wpdb;
                $wpdb->update(
                    $wpdb->prefix . 'pmxi_imports',
                    array('processing' => 0),
                    array('id' => $import_id),
                    array('%d'),
                    array('%d')
                );
            }

            WPAI_Bridge_Logger::debug("[LLM Config API] Cleared processing lock for import $import_id (lock_existed: " . ($lock_existed ? 'yes' : 'no') . ", was_processing: " . ($was_processing ? 'yes' : 'no') . ")");

            $captured = ob_get_clean();
            if (!empty($captured)) {
                WPAI_Bridge_Logger::debug("[LLM Config API] Discarded output during clear_processing_lock: " . substr($captured, 0, 200));
            }

            return $this->create_safe_rest_response(array(
                'success' => true,
                'import_id' => $import_id,
                'lock_existed' => $lock_existed,
                'was_processing' => $was_processing,
                'message' => 'Processing lock cleared',
            ), 200);
        } catch (Exception $e) {
            WPAI_Bridge_Logger::debug("[LLM Config API] clear_processing_lock failed with exception: " . $e->getMessage());
            $captured = ob_get_clean();
            if (!empty($captured)) {
                WPAI_Bridge_Logger::debug("[LLM Config API] Discarded output during clear_processing_lock exception: " . substr($captured, 0, 200));
            }
            return new WP_Error('clear_lock_failed', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Get import history records (list of past runs)
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_import_history($request) {
        $import_id = $request['id'];
        $limit = $request->get_param('limit');
        $offset = $request->get_param('offset');

        WPAI_Bridge_Logger::debug("[LLM Config API] get_import_history called for import $import_id (limit: $limit, offset: $offset)");

        // Get import to verify it exists
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error('import_not_found', 'Import not found.', array('status' => 404));
        }

        // Get history records using LogFormatter
        $records = WPAI_Bridge_Log_Formatter::get_history_records($import_id, array(
            'limit' => $limit,
            'offset' => $offset,
        ));

        $total = WPAI_Bridge_Log_Formatter::get_history_count($import_id);

        return new WP_REST_Response(array(
            'success' => true,
            'import_id' => $import_id,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'records' => $records,
        ), 200);
    }

    /**
     * Get log content for a specific history record
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_history_log($request) {
        $import_id = $request['id'];
        $history_id = $request['history_id'];
        $format = $request->get_param('format');
        $filter_type = $request->get_param('filter_type');
        $search = $request->get_param('search');

        WPAI_Bridge_Logger::debug("[LLM Config API] get_history_log called for import $import_id, history $history_id (format: $format)");

        // Get import to verify it exists
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error('import_not_found', 'Import not found.', array('status' => 404));
        }

        // Verify history record belongs to this import
        global $wpdb;
        $table = $wpdb->prefix . 'pmxi_history';
        $history = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d AND import_id = %d",
            $history_id,
            $import_id
        ), ARRAY_A);

        if (!$history) {
            return new WP_Error('history_not_found', 'History record not found', array('status' => 404));
        }

        // Get log file path
        $file_path = WPAI_Bridge_Log_Formatter::get_log_file_path($history_id);

        if (!$file_path) {
            return new WP_REST_Response(array(
                'success' => true,
                'import_id' => $import_id,
                'history_id' => $history_id,
                'history' => $history,
                'format' => $format,
                'entries' => array(),
                'message' => 'Log file not found',
            ), 200);
        }

        // Read and parse log
        if ($format === 'html') {
            $content = WPAI_Bridge_Log_Formatter::read_log_file($file_path, 'html');
            return new WP_REST_Response(array(
                'success' => true,
                'import_id' => $import_id,
                'history_id' => $history_id,
                'history' => $history,
                'format' => 'html',
                'content' => $content,
            ), 200);
        }

        // JSON format
        $entries = WPAI_Bridge_Log_Formatter::read_log_file($file_path, 'json');

        // Apply filters if specified
        if (!empty($filter_type)) {
            $types = explode(',', $filter_type);
            $entries = WPAI_Bridge_Log_Formatter::filter_by_type($entries, $types);
        }

        if (!empty($search)) {
            $entries = WPAI_Bridge_Log_Formatter::search_entries($entries, $search);
        }

        // Re-index array after filtering
        $entries = array_values($entries);

        // Get summary
        $summary = WPAI_Bridge_Log_Formatter::get_summary($entries);

        return new WP_REST_Response(array(
            'success' => true,
            'import_id' => $import_id,
            'history_id' => $history_id,
            'history' => $history,
            'format' => 'json',
            'entries' => $entries,
            'summary' => $summary,
        ), 200);
    }

    /**
     * Get current/active log during import processing
     * Returns the most recent history record's log, with support for incremental fetching
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_current_log($request) {
        $import_id = $request['id'];
        $format = $request->get_param('format');
        $since_line = $request->get_param('since_line');

        WPAI_Bridge_Logger::debug("[LLM Config API] get_current_log called for import $import_id (format: $format, since_line: $since_line)");

        // Get import to verify it exists
        $import = new PMXI_Import_Record();
        $import->getById($import_id);

        if ($import->isEmpty()) {
            return new WP_Error('import_not_found', 'Import not found.', array('status' => 404));
        }

        // Get recent history records for this import. The single newest row is often a
        // cron-trigger stub with no log file yet (e.g. right after rerun-import dispatches
        // via cron), which is why fetching only LIMIT 1 returned "Log file not yet created".
        // Scan the recent rows and use the most recent one that actually has a log file.
        global $wpdb;
        $table = $wpdb->prefix . 'pmxi_history';
        $histories = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE import_id = %d ORDER BY date DESC LIMIT 10",
            $import_id
        ), ARRAY_A);

        if (!$histories) {
            return new WP_REST_Response(array(
                'success' => true,
                'import_id' => $import_id,
                'history_id' => null,
                'format' => $format,
                'entries' => array(),
                'total_lines' => 0,
                'message' => 'No history records found',
            ), 200);
        }

        $history = null;
        $file_path = null;
        foreach ($histories as $h) {
            $fp = WPAI_Bridge_Log_Formatter::get_log_file_path($h['id']);
            if ($fp && file_exists($fp)) {
                $history = $h;
                $file_path = $fp;
                break;
            }
        }
        // Nothing has a log file yet — report against the newest row.
        if (!$history) {
            $history = $histories[0];
        }

        if (!$file_path) {
            return new WP_REST_Response(array(
                'success' => true,
                'import_id' => $import_id,
                'history_id' => (int)$history['id'],
                'history' => $history,
                'format' => $format,
                'entries' => array(),
                'total_lines' => 0,
                'message' => 'Log file not yet created',
            ), 200);
        }

        // Read and parse log
        $all_entries = WPAI_Bridge_Log_Formatter::read_log_file($file_path, 'json');
        $total_lines = count($all_entries);

        // Return only new entries since_line
        $new_entries = array_slice($all_entries, $since_line);

        if ($format === 'html') {
            $content = WPAI_Bridge_Log_Formatter::json_to_html($new_entries);
            return new WP_REST_Response(array(
                'success' => true,
                'import_id' => $import_id,
                'history_id' => (int)$history['id'],
                'history' => $history,
                'format' => 'html',
                'content' => $content,
                'total_lines' => $total_lines,
                'returned_from' => $since_line,
            ), 200);
        }

        // Get summary of new entries
        $summary = WPAI_Bridge_Log_Formatter::get_summary($new_entries);

        return new WP_REST_Response(array(
            'success' => true,
            'import_id' => $import_id,
            'history_id' => (int)$history['id'],
            'history' => $history,
            'format' => 'json',
            'entries' => $new_entries,
            'total_lines' => $total_lines,
            'returned_from' => $since_line,
            'summary' => $summary,
        ), 200);
    }

    /**
     * Adjust import settings (e.g., records per iteration) and reset stuck state
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function adjust_import_settings($request) {
        ob_start();

        try {
            $import_id = $request['id'];
            $records_per_iteration = $request->get_param('records_per_iteration');
            $cron_processing_time_limit = $request->get_param('cron_processing_time_limit');

            WPAI_Bridge_Logger::debug("[LLM Config API] adjust_import_settings called for import $import_id with records_per_iteration: $records_per_iteration, cron_processing_time_limit: $cron_processing_time_limit");

            // Get import to verify it exists
            $import = new PMXI_Import_Record();
            $import->getById($import_id);

            if ($import->isEmpty()) {
                $captured = ob_get_clean();
                if (!empty($captured)) {
                    WPAI_Bridge_Logger::debug("[LLM Config API] Discarded output during adjust_settings (import_not_found): " . substr($captured, 0, 200));
                }
                return new WP_Error('import_not_found', 'Import not found.', array('status' => 404));
            }

            $updates = array();
            $options = $import->options;
            $global_settings_updated = false;

            // Update records per iteration if provided
            // Note: WP All Import uses 'records_per_request' not 'records_per_iteration'
            if ($records_per_iteration !== null && is_numeric($records_per_iteration)) {
                $records_per_iteration = max(1, min(1000, (int)$records_per_iteration)); // Clamp between 1 and 1000
                $options['records_per_request'] = $records_per_iteration;
                WPAI_Bridge_Logger::debug("[LLM Config API] Setting records_per_request to: $records_per_iteration");
            }

            // Update global cron_processing_time_limit if provided
            // This is a global WP All Import setting, not per-import
            if ($cron_processing_time_limit !== null && is_numeric($cron_processing_time_limit)) {
                $cron_processing_time_limit = max(5, min(600, (int)$cron_processing_time_limit)); // Clamp between 5 and 600 seconds
                PMXI_Plugin::getInstance()->updateOption('cron_processing_time_limit', $cron_processing_time_limit);
                $global_settings_updated = true;
                WPAI_Bridge_Logger::debug("[LLM Config API] Setting global cron_processing_time_limit to: $cron_processing_time_limit");
            }

            // Reset stuck processing state
            $updates['options'] = $options;
            $updates['processing'] = 0;
            $updates['executing'] = 0;

            // Don't reset triggered flag - keep it so import can continue

            // Update import
            $import->set($updates)->update();

            WPAI_Bridge_Logger::debug("[LLM Config API] Import $import_id settings adjusted successfully");

            $captured = ob_get_clean();
            if (!empty($captured)) {
                WPAI_Bridge_Logger::debug("[LLM Config API] Discarded output during adjust_settings: " . substr($captured, 0, 500));
            }

            $response = $this->create_safe_rest_response(array(
                'success' => true,
                'import_id' => $import_id,
                'records_per_iteration' => isset($options['records_per_request']) ? $options['records_per_request'] : null,
                'cron_processing_time_limit' => PMXI_Plugin::getInstance()->getOption('cron_processing_time_limit'),
                'global_settings_updated' => $global_settings_updated,
                'message' => 'Import settings adjusted successfully',
            ), 200);

            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->header('Pragma', 'no-cache');

            return $response;
        } catch (Exception $e) {
            WPAI_Bridge_Logger::debug("[LLM Config API] Adjust settings failed with exception: " . $e->getMessage());
            $captured = ob_get_clean();
            if (!empty($captured)) {
                WPAI_Bridge_Logger::debug("[LLM Config API] Discarded output during adjust_settings exception: " . substr($captured, 0, 200));
            }
            return new WP_Error('adjust_settings_failed', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Get processing limits information
     *
     * Returns current cron_processing_time_limit, PHP max_execution_time,
     * and current records_per_iteration for the import.
     */
    public function get_processing_limits($request) {
        ob_start();

        try {
            $import_id = $request['id'];

            // Get import to verify it exists and get its settings
            $import = new PMXI_Import_Record();
            $import->getById($import_id);

            if ($import->isEmpty()) {
                $captured = ob_get_clean();
                if (!empty($captured)) {
                    WPAI_Bridge_Logger::debug("[LLM Config API] Discarded output during get_processing_limits (import_not_found): " . substr($captured, 0, 200));
                }
                return new WP_Error('import_not_found', 'Import not found.', array('status' => 404));
            }

            // Get PHP max_execution_time
            $php_max_execution_time = (int) ini_get('max_execution_time');
            // If 0, PHP has no limit (CLI mode or set to unlimited)
            if ($php_max_execution_time === 0) {
                $php_max_execution_time = 300; // Default to 5 minutes if unlimited
            }

            // Get current WP All Import cron_processing_time_limit
            $current_cron_limit = PMXI_Plugin::getInstance()->getOption('cron_processing_time_limit');
            if (empty($current_cron_limit)) {
                $current_cron_limit = 59; // Default value
            }

            // Get current records_per_iteration for this import
            $records_per_iteration = isset($import->options['records_per_request'])
                ? (int) $import->options['records_per_request']
                : 20; // Default

            $captured = ob_get_clean();
            if (!empty($captured)) {
                WPAI_Bridge_Logger::debug("[LLM Config API] Discarded output during get_processing_limits: " . substr($captured, 0, 200));
            }

            $response = $this->create_safe_rest_response(array(
                'success' => true,
                'import_id' => $import_id,
                'php_max_execution_time' => $php_max_execution_time,
                'cron_processing_time_limit' => (int) $current_cron_limit,
                'records_per_iteration' => $records_per_iteration,
                // Suggest a safe processing time limit (slightly less than PHP limit)
                'suggested_time_limit' => max(10, min($php_max_execution_time - 10, 59)),
            ), 200);

            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->header('Pragma', 'no-cache');

            return $response;
        } catch (Exception $e) {
            WPAI_Bridge_Logger::debug("[LLM Config API] get_processing_limits failed with exception: " . $e->getMessage());
            $captured = ob_get_clean();
            if (!empty($captured)) {
                WPAI_Bridge_Logger::debug("[LLM Config API] Discarded output during get_processing_limits exception: " . substr($captured, 0, 200));
            }
            return new WP_Error('get_limits_failed', $e->getMessage(), array('status' => 500));
        }
    }
}

