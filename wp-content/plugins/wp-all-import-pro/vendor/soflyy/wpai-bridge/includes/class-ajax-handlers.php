<?php
/**
 * AJAX Handlers for LLM Auto-Configuration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPAI_Bridge_AJAX_Handlers {

    /**
     * Character cap on instructions that travel into the setup prompt
     */
    const INSTRUCTIONS_MAX_LENGTH = 1000;

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
     * Constructor - register AJAX handlers
     */
    private function __construct() {
        add_action( 'wp_ajax_wpai_save_llm_template', array( $this, 'save_llm_template' ) );
        add_action( 'wp_ajax_wpai_get_import_options', array( $this, 'get_import_options' ) );
        add_action( 'wp_ajax_wpai_update_session_options', array( $this, 'update_session_options' ) );
        add_action( 'wp_ajax_wpai_record_ai_consent', array( $this, 'record_ai_consent' ) );
        add_action( 'wp_ajax_wpai_reset_template_to_defaults', array( $this, 'reset_template_to_defaults' ) );
    }

    /**
     * AJAX handler to record user consent for AI data processing
     */
    public function record_ai_consent() {
        // Security check
        if ( ! check_ajax_referer( 'wpai_bridge_consent', 'security', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'wpai-ai-bridge-plugin' ) ), 403 );
        }

        if ( ! current_user_can( PMXI_Plugin::$capabilities ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpai-ai-bridge-plugin' ) ), 403 );
        }

        $result = wpai_bridge_record_user_consent();

        if ( $result ) {
            wp_send_json_success( array(
                'message' => __( 'Consent recorded successfully', 'wpai-ai-bridge-plugin' ),
            ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to record consent', 'wpai-ai-bridge-plugin' ) ), 500 );
        }
    }
    
    /**
     * AJAX handler to save template data to import record for LLM auto-configuration
     * 
     * This uses the same logic as the preview feature to save all template form data
     * to the import record's options field as soon as the template page loads.
     * This ensures the import record has all the default values before the LLM processes it.
     */
    public function save_llm_template() {
        // Security check
        if ( ! check_ajax_referer( 'wp_all_import_secure', 'security', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'wpai-ai-bridge-plugin' ) ), 403 );
        }

        if ( ! current_user_can( PMXI_Plugin::$capabilities ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpai-ai-bridge-plugin' ) ), 403 );
        }

        // Check for PHP max_input_vars limit
        // When this limit is exceeded, PHP silently truncates $_POST data, causing failures
        $max_input_vars = (int) ini_get( 'max_input_vars' );
        $actual_post_vars = count( $_POST, COUNT_RECURSIVE );

        // If we're at 95% or more of the limit, data was likely truncated
        if ( $max_input_vars > 0 && $actual_post_vars >= ( $max_input_vars * 0.95 ) ) {
            $error_message = sprintf(
                /* translators: 1: current limit, 2: how many vars we received */
                __( 'PHP max_input_vars limit reached (%1$d). Your import template has too many fields (%2$d received). Please increase max_input_vars in your PHP configuration (php.ini or .htaccess) to at least %3$d.', 'wpai-ai-bridge-plugin' ),
                $max_input_vars,
                $actual_post_vars,
                max( 3000, $max_input_vars * 2 )
            );

            WPAI_Bridge_Logger::error( 'max_input_vars limit hit: ' . $max_input_vars . ' limit, received ' . $actual_post_vars . ' vars' );

            wp_send_json_error( array(
                'message'        => $error_message,
                'error_type'     => 'max_input_vars',
                'limit'          => $max_input_vars,
                'received'       => $actual_post_vars,
                'suggested_limit' => max( 3000, $max_input_vars * 2 ),
            ), 400 );
        }

        try {
            $input = new PMXI_Input();
            $import_id = isset( $_POST['import_id'] ) ? intval( $_POST['import_id'] ) : 0;

            if ( empty( $import_id ) ) {
                wp_send_json_error( array( 'message' => __( 'Import ID is required', 'wpai-ai-bridge-plugin' ) ), 400 );
            }

            // Load the import record
            $import = new PMXI_Import_Record();
            $import->getById( $import_id );

            if ( $import->isEmpty() ) {
                wp_send_json_error( array( 'message' => __( 'Import not found', 'wpai-ai-bridge-plugin' ) ), 404 );
            }

            // Get default options (same as preview feature)
            $default = PMXI_Plugin::get_default_import_options();
            
            // Add addon defaults
            foreach ( PMXI_Admin_Addons::get_active_addons() as $class ) {
                if ( class_exists( $class ) ) {
                    $default += call_user_func( array( $class, "get_default_import_options" ) );
                }
            }

            // Merge with existing import options
            $DefaultOptions = array_replace_recursive( $default, $import->options );

            // Get posted template data (same as preview feature)
            $post = $input->post( apply_filters( 'pmxi_options_options', $DefaultOptions, false ) );

            // CRITICAL: Ensure custom_type is preserved from session
            // The session's custom_type property (set at step 1) is the authoritative source
            // for the import type. The form's hidden field may have incorrect defaults.
            if ( isset( PMXI_Plugin::$session->custom_type ) && ! empty( PMXI_Plugin::$session->custom_type ) ) {
                $post['custom_type'] = PMXI_Plugin::$session->custom_type;
            }

            // Handle fields array if present
            if ( isset( $_POST['fields'] ) && is_array( $_POST['fields'] ) ) {
                $post['fields'] = $_POST['fields'];
            }

            // For LLM configuration, we skip validation because the template is intentionally empty
            // and will be filled in by the LLM. We just want to save the defaults.
            // We still apply the pmxi_save_options filter to let addons process their defaults.
            $post = apply_filters( 'pmxi_save_options', $post, $import );

            // Store the original pre-LLM template if not already stored
            // This allows re-analyze to restore to the exact same starting point as the original run
            $original_template_key = 'wpai_llm_original_template_' . $import_id;
            if ( ! get_option( $original_template_key ) ) {
                update_option( $original_template_key, $post, false ); // false = don't autoload
                WPAI_Bridge_Logger::debug('=== Stored original pre-LLM template for import ' . $import_id . ' ===');
            }

            // Debug: Log what's being saved
            WPAI_Bridge_Logger::debug('=== save_llm_template saving options ===');
            WPAI_Bridge_Logger::debug('title: ' . ($post['title'] ?? '(not set)'));
            WPAI_Bridge_Logger::debug('content: ' . substr($post['content'] ?? '(not set)', 0, 100));
            WPAI_Bridge_Logger::debug('unique_key: ' . ($post['unique_key'] ?? '(not set)'));
            WPAI_Bridge_Logger::debug('custom_type: ' . ($post['custom_type'] ?? '(not set)'));
            WPAI_Bridge_Logger::debug('custom_name count: ' . (isset($post['custom_name']) ? count($post['custom_name']) : 0));

            // Save the template data to the import record
            // This is the key step - we're saving all the template defaults to the database
            $import->set( array( 'options' => $post ) )->update();

            // Set update_previous in session so template form submission doesn't create a duplicate
            PMXI_Plugin::$session->set( 'update_previous', $import_id );

            // CRITICAL: Set session options to the import options
            // This ensures that when the LLM updates the import options via REST API,
            // and then the template form is submitted, the session will be reloaded
            // with the latest import options (including LLM-configured values like unique_key)
            PMXI_Plugin::$session->set( 'options', $post );
            PMXI_Plugin::$session->save_data();

            wp_send_json_success( array(
                'message' => __( 'Template data saved successfully', 'wpai-ai-bridge-plugin' ),
                'import_id' => $import_id
            ) );

        } catch ( Exception $e ) {
            wp_send_json_error( array( 'message' => $e->getMessage() ), 500 );
        }
    }

    /**
     * AJAX handler to get import options from database
     *
     * This is called after the LLM finishes configuring the import to fetch
     * the updated options from the database before updating the session.
     */
    public function get_import_options() {
        // Security check
        if ( ! check_ajax_referer( 'wp_all_import_secure', 'security', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'wpai-ai-bridge-plugin' ) ), 403 );
        }

        if ( ! current_user_can( PMXI_Plugin::$capabilities ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpai-ai-bridge-plugin' ) ), 403 );
        }

        try {
            $import_id = isset( $_POST['import_id'] ) ? intval( $_POST['import_id'] ) : 0;

            if ( empty( $import_id ) ) {
                wp_send_json_error( array( 'message' => __( 'Import ID is required', 'wpai-ai-bridge-plugin' ) ), 400 );
            }

            // Load the import record
            $import = new PMXI_Import_Record();
            $import->getById( $import_id );

            if ( $import->isEmpty() ) {
                wp_send_json_error( array( 'message' => __( 'Import not found', 'wpai-ai-bridge-plugin' ) ), 404 );
            }

            wp_send_json_success( array(
                'message' => __( 'Import options retrieved successfully', 'wpai-ai-bridge-plugin' ),
                'import_id' => $import_id,
                'options' => $import->options
            ) );

        } catch ( Exception $e ) {
            wp_send_json_error( array( 'message' => $e->getMessage() ), 500 );
        }
    }

    /**
     * AJAX handler to update session options with LLM-configured values
     *
     * This is called after the LLM finishes configuring the import and the template fields
     * are populated. It updates the session options to match the import options so that
     * when the template form is submitted, the session has the correct values.
     */
    public function update_session_options() {
        // Security check
        if ( ! check_ajax_referer( 'wp_all_import_secure', 'security', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'wpai-ai-bridge-plugin' ) ), 403 );
        }

        if ( ! current_user_can( PMXI_Plugin::$capabilities ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpai-ai-bridge-plugin' ) ), 403 );
        }

        try {
            $import_id = isset( $_POST['import_id'] ) ? intval( $_POST['import_id'] ) : 0;
            $options_json = isset( $_POST['options'] ) ? $_POST['options'] : '';

            if ( empty( $import_id ) ) {
                wp_send_json_error( array( 'message' => __( 'Import ID is required', 'wpai-ai-bridge-plugin' ) ), 400 );
            }

            if ( empty( $options_json ) ) {
                wp_send_json_error( array( 'message' => __( 'Options are required', 'wpai-ai-bridge-plugin' ) ), 400 );
            }

            // Decode options
            $options = json_decode( stripslashes( $options_json ), true );
            if ( json_last_error() !== JSON_ERROR_NONE ) {
                wp_send_json_error( array( 'message' => __( 'Invalid options JSON', 'wpai-ai-bridge-plugin' ) ), 400 );
            }

            // Filter out add-on fields from custom fields (they have their own sections)
            $options = $this->filter_addon_custom_fields( $options );

            // Use PMXI_Handler properly instead of manually manipulating options
            // Initialize session if not already done
            if ( empty( PMXI_Plugin::$session ) ) {
                PMXI_Plugin::$session = new PMXI_Handler();
            }

            // Preserve custom_type from existing session
            $existing_custom_type = PMXI_Plugin::$session->get( 'custom_type' );
            if ( ! empty( $existing_custom_type ) ) {
                $options['custom_type'] = $existing_custom_type;
            }

            // Update options in session
            PMXI_Plugin::$session->set( 'options', $options );
            PMXI_Plugin::$session->save_data();

            WPAI_Bridge_Logger::debug( 'AI Bridge: Updated session options using PMXI_Handler' );
            WPAI_Bridge_Logger::debug( 'AI Bridge: Options title: ' . ( isset( $options['title'] ) ? substr( $options['title'], 0, 50 ) : 'NOT SET' ) );
            WPAI_Bridge_Logger::debug( 'AI Bridge: Options content: ' . ( isset( $options['content'] ) ? substr( $options['content'], 0, 50 ) : 'NOT SET' ) );
            WPAI_Bridge_Logger::debug( 'AI Bridge: Options tax_assing: ' . ( isset( $options['tax_assing'] ) ? json_encode( $options['tax_assing'] ) : 'NOT SET' ) );
            WPAI_Bridge_Logger::debug( 'AI Bridge: Options tax_logic: ' . ( isset( $options['tax_logic'] ) ? json_encode( $options['tax_logic'] ) : 'NOT SET' ) );
            WPAI_Bridge_Logger::debug( 'AI Bridge: Options tax_single_xpath: ' . ( isset( $options['tax_single_xpath'] ) ? json_encode( $options['tax_single_xpath'] ) : 'NOT SET' ) );

            wp_send_json_success( array(
                'message' => __( 'Session options updated successfully', 'wpai-ai-bridge-plugin' ),
                'import_id' => $import_id
            ) );

        } catch ( Exception $e ) {
            wp_send_json_error( array( 'message' => $e->getMessage() ), 500 );
        }
    }

    /**
     * AJAX handler to reset template options to defaults
     *
     * This is called before re-analyzing with the LLM to ensure the template
     * starts from a clean state, not from the previous LLM's configuration.
     */
    public function reset_template_to_defaults() {
        // Security check
        if ( ! check_ajax_referer( 'wp_all_import_secure', 'security', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'wpai-ai-bridge-plugin' ) ), 403 );
        }

        if ( ! current_user_can( PMXI_Plugin::$capabilities ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpai-ai-bridge-plugin' ) ), 403 );
        }

        try {
            $import_id = isset( $_POST['import_id'] ) ? intval( $_POST['import_id'] ) : 0;

            if ( empty( $import_id ) ) {
                wp_send_json_error( array( 'message' => __( 'Import ID is required', 'wpai-ai-bridge-plugin' ) ), 400 );
            }

            // Load the import record
            $import = new PMXI_Import_Record();
            $import->getById( $import_id );

            if ( $import->isEmpty() ) {
                wp_send_json_error( array( 'message' => __( 'Import not found', 'wpai-ai-bridge-plugin' ) ), 404 );
            }

            // Try to restore from the original pre-LLM template that was stored on first run
            // This ensures re-analyze has the exact same starting data as the original run
            $original_template_key = 'wpai_llm_original_template_' . $import_id;
            $original_template = get_option( $original_template_key );

            // A submitted instructions parameter always wins, including when it is
            // empty: the panel shows the stored value, so clearing the box must
            // clear it. An absent parameter leaves the stored value alone, which
            // keeps callers that only reset the template working.
            if ( isset( $_POST['instructions'] ) ) {
                $preserved_user_instructions = $this->cap_instructions(
                    sanitize_textarea_field( wp_unslash( $_POST['instructions'] ) )
                );
            } else {
                $preserved_user_instructions = $import->options['llm_user_instructions'] ?? '';
            }

            if ( $original_template && is_array( $original_template ) ) {
                // Restore from the stored original template
                $new_options = $original_template;
                WPAI_Bridge_Logger::debug('=== reset_template_to_defaults - Restoring from stored original ===');
            } else {
                // Fallback: No stored original, use defaults (shouldn't normally happen)
                WPAI_Bridge_Logger::debug('=== reset_template_to_defaults - No stored original, using defaults ===');

                $default = PMXI_Plugin::get_default_import_options();

                // Add addon defaults
                foreach ( PMXI_Admin_Addons::get_active_addons() as $class ) {
                    if ( class_exists( $class ) ) {
                        $default += call_user_func( array( $class, "get_default_import_options" ) );
                    }
                }

                // Keep only essential non-template options from the existing import
                $preserve_keys = array(
                    'type',           // File type (xml, csv, etc.)
                    'custom_type',    // Post type
                    'taxonomy_type',  // Taxonomy type if importing taxonomies
                    'wizard_type',    // Import wizard type
                    'delimiter',      // CSV delimiter
                    'encoding',       // File encoding
                    'xpath',          // Root element XPath
                    'root_element',   // Root element name
                );

                $new_options = $default;

                if ( is_array( $import->options ) ) {
                    foreach ( $preserve_keys as $key ) {
                        if ( isset( $import->options[ $key ] ) ) {
                            $new_options[ $key ] = $import->options[ $key ];
                        }
                    }
                }

                $new_options = apply_filters( 'pmxi_save_options', $new_options, $import );
            }

            // CRITICAL: Ensure custom_type is preserved from session as fallback
            if ( empty( $new_options['custom_type'] ) && isset( PMXI_Plugin::$session->custom_type ) && ! empty( PMXI_Plugin::$session->custom_type ) ) {
                $new_options['custom_type'] = PMXI_Plugin::$session->custom_type;
            }

            // Written unconditionally so an emptied value clears rather than
            // falling back to whatever the restored template carried.
            $new_options['llm_user_instructions'] = $preserved_user_instructions;
            WPAI_Bridge_Logger::debug('llm_user_instructions: ' . ( $preserved_user_instructions !== '' ? $preserved_user_instructions : '(cleared)' ));

            // Debug: Log what's being reset to
            WPAI_Bridge_Logger::debug('Restoring to:');
            WPAI_Bridge_Logger::debug('  title: ' . ($new_options['title'] ?? '(not set)'));
            WPAI_Bridge_Logger::debug('  content: ' . substr($new_options['content'] ?? '(not set)', 0, 100));
            WPAI_Bridge_Logger::debug('  unique_key: ' . ($new_options['unique_key'] ?? '(not set)'));
            WPAI_Bridge_Logger::debug('  custom_type: ' . ($new_options['custom_type'] ?? '(not set)'));
            WPAI_Bridge_Logger::debug('  custom_name count: ' . (isset($new_options['custom_name']) ? count($new_options['custom_name']) : 0));

            // Save the restored options to both import record AND session
            $import->set( array( 'options' => $new_options ) )->update();
            PMXI_Plugin::$session->set( 'options', $new_options );
            PMXI_Plugin::$session->save_data();

            wp_send_json_success( array(
                'message' => __( 'Template reset to defaults successfully', 'wpai-ai-bridge-plugin' ),
                'import_id' => $import_id
            ) );

        } catch ( Exception $e ) {
            wp_send_json_error( array( 'message' => $e->getMessage() ), 500 );
        }
    }

    /**
     * Truncate instructions to the same cap the textarea advertises. The browser
     * maxlength is advisory only and this value is billed per token downstream.
     */
    private function cap_instructions( $instructions ) {
        $instructions = trim( (string) $instructions );

        if ( function_exists( 'mb_substr' ) ) {
            return mb_substr( $instructions, 0, self::INSTRUCTIONS_MAX_LENGTH );
        }

        return substr( $instructions, 0, self::INSTRUCTIONS_MAX_LENGTH );
    }

    /**
     * Filter add-on fields from custom fields arrays and move them to proper locations
     *
     * Add-on fields (like pmui[login], pmsci_customer[email]) use bracket notation
     * and should be in their own sections, not in Custom Fields. This method:
     * 1. Removes them from custom_name/custom_value arrays
     * 2. Parses them into nested array structure
     * 3. Merges them into the proper options keys
     *
     * @param array $options The options array to filter
     * @return array Filtered and restructured options
     */
    private function filter_addon_custom_fields( $options ) {
        // Fields that should not be in custom fields (non-bracketed add-on settings)
        $excluded_fields = array(
            'is_hashed_wordpress_password',
            'do_not_send_password_notification',
            'send_email',
            'pmgi_is_update_entry_fields',
        );

        if ( empty( $options['custom_name'] ) || ! is_array( $options['custom_name'] ) ) {
            return $options;
        }

        $filtered_names = array();
        $filtered_values = array();

        foreach ( $options['custom_name'] as $index => $name ) {
            $value = isset( $options['custom_value'][ $index ] ) ? $options['custom_value'][ $index ] : '';

            // Skip excluded fields
            if ( in_array( $name, $excluded_fields ) ) {
                continue;
            }

            // Skip empty names
            if ( empty( trim( $name ) ) ) {
                continue;
            }

            // Check if this field uses bracket notation (e.g., "pmwi_order[status]")
            // If so, it's likely an add-on field that should be in its own namespace
            if ( preg_match( '/^([a-zA-Z_][a-zA-Z0-9_]*)\[/', $name, $matches ) ) {
                $prefix = $matches[1];

                // Check if this prefix exists as an array in options (add-on namespace)
                // OR if it looks like an add-on prefix (starts with pm or has underscore)
                $is_addon_field = false;

                // If the prefix already exists as an array in options, it's definitely an add-on
                if ( isset( $options[ $prefix ] ) && is_array( $options[ $prefix ] ) ) {
                    $is_addon_field = true;
                }
                // Common add-on prefix patterns: pmXX, pmXX_something
                elseif ( preg_match( '/^pm[a-z]{2,}/', $prefix ) ) {
                    $is_addon_field = true;
                }
                // Also check for pmsci_ pattern (WooCommerce customer)
                elseif ( strpos( $prefix, 'pmsci_' ) === 0 ) {
                    $is_addon_field = true;
                }

                if ( $is_addon_field ) {
                    // Parse bracket notation and merge into options
                    $parsed = $this->parse_bracket_notation( $name, $value );
                    $options = $this->array_merge_recursive_distinct( $options, $parsed );
                    continue;
                }
            }

            // Keep this field in custom fields
            $filtered_names[] = $name;
            $filtered_values[] = $value;
        }

        $options['custom_name'] = $filtered_names;
        $options['custom_value'] = $filtered_values;

        return $options;
    }

    /**
     * Parse bracket notation string into nested array
     *
     * Converts "pmsci_customer[login]" with value "{userlogin[1]}"
     * into: ['pmsci_customer' => ['login' => '{userlogin[1]}']]
     *
     * @param string $key The bracket notation key
     * @param mixed $value The value to set
     * @return array Nested array structure
     */
    private function parse_bracket_notation( $key, $value ) {
        // Extract all bracket parts: "pmsci_customer[login]" => ["pmsci_customer", "login"]
        preg_match_all( '/([^\[\]]+)/', $key, $matches );
        $parts = $matches[1];

        if ( empty( $parts ) ) {
            return array();
        }

        // Build nested array from inside out
        $result = $value;
        for ( $i = count( $parts ) - 1; $i >= 0; $i-- ) {
            $result = array( $parts[ $i ] => $result );
        }

        return $result;
    }

    /**
     * Merge arrays recursively, with later values overwriting earlier ones
     *
     * @param array $array1 Base array
     * @param array $array2 Array to merge in
     * @return array Merged array
     */
    private function array_merge_recursive_distinct( array $array1, array $array2 ) {
        $merged = $array1;

        foreach ( $array2 as $key => $value ) {
            if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
                $merged[ $key ] = $this->array_merge_recursive_distinct( $merged[ $key ], $value );
            } else {
                $merged[ $key ] = $value;
            }
        }

        return $merged;
    }
}

