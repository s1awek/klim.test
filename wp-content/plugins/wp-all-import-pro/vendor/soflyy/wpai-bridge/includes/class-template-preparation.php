<?php
/**
 * Template Preparation API
 *
 * Provides server-side template rendering and schema extraction
 * to enable seamless Step 1 → LLM configuration without page reload.
 *
 * This class renders the Step 3 template page server-side, loads all
 * add-on field groups (ACF, Toolset, JetEngine, MetaBox, etc.), and
 * extracts the template schema - all without requiring a browser DOM.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPAI_Bridge_Template_Preparation {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * API namespace
     */
    const API_NAMESPACE = 'wp-all-import/v1';

    /**
     * Properties expected by WP All Import templates when they use $this->
     * These templates are designed to run in a controller context, so we provide
     * the expected properties here to allow dynamic template rendering.
     */
    public $isWizard = true;

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
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Prepare template schema for an import
        register_rest_route( self::API_NAMESPACE, '/prepare-template/(?P<import_id>\d+)', array(
            'methods' => 'POST',
            'callback' => array( $this, 'prepare_template' ),
            'permission_callback' => array( $this, 'check_permissions' ),
            'args' => array(
                'import_id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
            ),
        ) );
    }

    /**
     * Check permissions - requires valid session token or WordPress user auth
     */
    public function check_permissions( $request ) {
        // Check WordPress REST API nonce authentication first (same-origin admin requests)
        // The nonce is validated by WordPress core when X-WP-Nonce header is present
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            // Nonce is valid, check user capabilities
            if ( current_user_can( PMXI_Plugin::$capabilities ) ) {
                return true;
            }
        }

        // Fallback: check if user is logged in via cookies
        if ( is_user_logged_in() && current_user_can( PMXI_Plugin::$capabilities ) ) {
            return true;
        }

        // Get session token from header (for cross-origin requests from Vercel)
        $token = $request->get_header( 'X-WPAI-Session-Token' );

        if ( ! empty( $token ) ) {
            // Try step1 session token first (file processor stores wpai_step1_session_*)
            $file_processor = WPAI_Bridge_File_Processor::getInstance();
            if ( method_exists( $file_processor, 'validate_token' ) && $file_processor->validate_token( $token, false ) ) {
                return true;
            }

            // Try per-import token (wpai_llm_session_{import_id}) — allows concurrent
            // schema fetches after step1 completes, since each import has its own token.
            $import_id = $request->get_param( 'import_id' );
            if ( $import_id ) {
                $llm_config_api = WPAI_Bridge_LLM_Config_API::getInstance();
                if ( method_exists( $llm_config_api, 'validate_token_public' ) && $llm_config_api->validate_token_public( intval( $import_id ), $token ) ) {
                    return true;
                }
            }
        }

        return new WP_Error(
            'rest_forbidden',
            __( 'Authentication required.', 'wpai-ai-bridge-plugin' ),
            array( 'status' => 401 )
        );
    }

    /**
     * Main endpoint: Prepare template and extract schema
     */
    public function prepare_template( $request ) {
        $import_id = $request->get_param( 'import_id' );

        // DIAGNOSTIC: If 'test' param is set, return minimal JSON to verify endpoint works
        if ( $request->get_param( 'test' ) ) {
            return new WP_REST_Response( array( 'success' => true, 'test' => 'ok' ), 200 );
        }

        WPAI_Bridge_Logger::debug( "prepare_template called for import_id: $import_id" );

        try {
            // Load the import record
            $import = new PMXI_Import_Record();
            $import->getById( $import_id );

            if ( $import->isEmpty() ) {
                return new WP_Error(
                    'import_not_found',
                    __( 'Import not found.', 'wpai-ai-bridge-plugin' ),
                    array( 'status' => 404 )
                );
            }

            // Initialize session for this import
            self::initialize_session( $import );

            // Get import type and options
            $import_type = $import->options['custom_type'] ?? 'post';
            $options = $import->options;

            // Capture any stray output that might leak during schema extraction
            ob_start();
            $schema = $this->render_and_extract_schema( $import, $import_type, $options );
            $stray_output = ob_get_clean();

            if ( ! empty( $stray_output ) ) {
                WPAI_Bridge_Logger::warn( 'Stray output captured during schema extraction', array( 'length' => strlen( $stray_output ), 'preview' => substr( $stray_output, 0, 500 ) ) );
            }

            // Generate session token for the iframe
            $llm_api = WPAI_Bridge_LLM_Config_API::getInstance();
            $token_data = $llm_api->generate_token( $import_id );

            // Get import file URL
            $file_url = $this->get_import_file_url( $import );

            // Consent-gated, best-effort corpus capture (full file + schema) to the
            // AI service. Self-gates on consent; never disrupts this flow.
            WPAI_Bridge_Corpus_Capture::maybe_dispatch( $import, $import_type, $schema, $token_data['token'] );

            // Get any previously stored user instructions for re-analyze flow
            $user_instructions = isset( $options['llm_user_instructions'] ) ? $options['llm_user_instructions'] : '';

            return new WP_REST_Response( array(
                'success' => true,
                'data' => array(
                    'token' => $token_data['token'],
                    'expires' => $token_data['expires'],
                    'import_file_url' => $file_url,
                    'template_schema' => $schema,
                    'import_id' => $import_id,
                    'user_instructions' => $user_instructions,
                ),
            ), 200 );

        } catch ( Exception $e ) {
            WPAI_Bridge_Logger::error( 'prepare_template error: ' . $e->getMessage() );
            return new WP_Error(
                'preparation_failed',
                $e->getMessage(),
                array( 'status' => 500 )
            );
        } catch ( Throwable $t ) {
            // Catch PHP 7+ errors (TypeError, etc.)
            WPAI_Bridge_Logger::error( 'prepare_template throwable: ' . $t->getMessage() );
            return new WP_Error(
                'preparation_failed',
                $t->getMessage(),
                array( 'status' => 500 )
            );
        }
    }

    /**
     * Seed the wizard session for an import so schema extraction can render it.
     *
     * Always writes the session that PMXI_Handler keys as 'new'.
     */
    private static function initialize_session( $import ) {
        WPAI_Bridge_Logger::separator( '======================================================' );
        WPAI_Bridge_Logger::debug( 'AI Bridge PREPARE-TEMPLATE: initialize_session()' );
        WPAI_Bridge_Logger::separator( '======================================================' );
        WPAI_Bridge_Logger::debug( ' import->id = ' . $import->id );
        WPAI_Bridge_Logger::debug( ' import->path = ' . $import->path );
        WPAI_Bridge_Logger::debug( ' import->xpath = ' . $import->xpath );

        // Initialize session if not already done
        if ( empty( PMXI_Plugin::$session ) ) {
            PMXI_Plugin::$session = new PMXI_Handler();
        }

        // Get XML file path from file history (NOT import->path which may be URL)
        $xml_file_path = wp_all_import_get_absolute_path( $import->path ); // fallback
        $history_file = new PMXI_File_Record();
        $history_file->getBy( array( 'import_id' => $import->id ), 'id DESC' );
        if ( ! $history_file->isEmpty() ) {
            $xml_file_path = wp_all_import_get_absolute_path( $history_file->path );
            WPAI_Bridge_Logger::debug( ' Found file history - using XML path: ' . $xml_file_path );
        } else {
            WPAI_Bridge_Logger::debug( ' WARNING - No file history found, using import->path: ' . $import->path );
        }

        $options = is_array( $import->options ) ? $import->options : array();

        // Mirrors the source array WP All Import builds when it delegates the
        // template screen for an existing import (controllers/admin/import.php).
        $source = array(
            'name'         => $import->name,
            'type'         => $import->type,
            'path'         => wp_all_import_get_relative_path( $import->path ),
            'root_element' => $import->root_element,
        );

        // Set session data from import record
        PMXI_Plugin::$session->set( 'import_id', $import->id );
        PMXI_Plugin::$session->set( 'update_previous', $import->id );
        PMXI_Plugin::$session->set( 'custom_type', $options['custom_type'] ?? 'post' );
        PMXI_Plugin::$session->set( 'taxonomy_type', $options['taxonomy_type'] ?? '' );
        PMXI_Plugin::$session->set( 'options', $import->options );
        // Use XML file path for filePath (for processing), not import->path (which may be URL)
        PMXI_Plugin::$session->set( 'filePath', $xml_file_path );
        PMXI_Plugin::$session->set( 'xpath', $import->xpath );
        PMXI_Plugin::$session->set( 'root_element', $import->root_element );
        // Step 3 dereferences source['type'] and wizard_type unconditionally, so a
        // session missing them is a fatal, not a degraded screen.
        PMXI_Plugin::$session->set( 'source', $source );
        PMXI_Plugin::$session->set( 'wizard_type', $options['wizard_type'] ?? 'new' );
        PMXI_Plugin::$session->set( 'is_csv', $options['delimiter'] ?? '' );
        PMXI_Plugin::$session->set( 'encoding', $options['encoding'] ?? 'UTF-8' );
        PMXI_Plugin::$session->set( 'local_paths', array( $xml_file_path ) );
        PMXI_Plugin::$session->set( 'count', $import->count );
        PMXI_Plugin::$session->set( 'action', 'import' );
        PMXI_Plugin::$session->set( 'pointer', 1 );
        PMXI_Plugin::$session->set( 'chunk_number', 1 );
        PMXI_Plugin::$session->set( 'queue_chunk_number', 0 );
        PMXI_Plugin::$session->set( 'processing', 0 );
        PMXI_Plugin::$session->set( 'log', '' );
        PMXI_Plugin::$session->set( 'warnings', 0 );
        PMXI_Plugin::$session->set( 'errors', 0 );
        PMXI_Plugin::$session->set( 'start_time', 0 );
        PMXI_Plugin::$session->save_data();

        WPAI_Bridge_Logger::debug( ' Session saved with filePath = ' . $xml_file_path );
        WPAI_Bridge_Logger::separator( '======================================================' );
    }

    /**
     * Render template sections and extract schema DYNAMICALLY
     *
     * This method ACTUALLY renders the WP All Import template files to capture
     * the real form fields - NO hardcoded fields, pure dynamic extraction.
     */
    private function render_and_extract_schema( $import, $import_type, $options ) {
        WPAI_Bridge_Logger::debug( ' Starting TRUE DYNAMIC template rendering for ' . $import_type );

        // Render the actual WP All Import template files - this captures base fields
        $html = $this->render_template_page( $import, $import_type, $options );

        WPAI_Bridge_Logger::debug( ' Rendered template HTML length: ' . strlen( $html ) );

        // Parse HTML into sections with fields - mirrors Step 3 JS extractTemplateSchema()
        $schema = $this->parse_template_html_into_schema( $html, $import_type );

        // IMPORTANT: Addon field groups (ACF, Toolset, JetEngine, MetaBox) are loaded via AJAX
        // on Step 3 when the user checks a group checkbox. The HTML rendering only captures
        // accordion headers, not the actual fields. We must get fields directly from addon APIs.
        $schema = $this->add_acf_fields_from_api( $import_type, $options, $schema );
        $schema = $this->add_toolset_fields_from_api( $import_type, $options, $schema );
        $schema = $this->render_new_api_addon_groups( $import, $options, $schema );

        // Add taxonomies list
        $schema = $this->add_taxonomies( $import_type, $schema );

        // COMPREHENSIVE LOGGING - Log EVERYTHING being sent to LLM
        WPAI_Bridge_Logger::separator( '========================================' );
        WPAI_Bridge_Logger::debug( '=== AI Bridge COMPLETE Schema for LLM ===' );
        WPAI_Bridge_Logger::separator( '========================================' );
        WPAI_Bridge_Logger::debug( 'Import Type: ' . $import_type );
        WPAI_Bridge_Logger::debug( 'Total Sections: ' . count( $schema['sections'] ) );
        WPAI_Bridge_Logger::debug( 'Total Taxonomies: ' . count( $schema['taxonomies'] ?? array() ) );

        $total_fields = 0;
        foreach ( $schema['sections'] as $section_index => $section ) {
            $section_name = $section['name'] ?? 'Unknown';
            $fields = $section['fields'] ?? array();
            $field_count = count( $fields );
            $total_fields += $field_count;

            WPAI_Bridge_Logger::debug( '' );
            WPAI_Bridge_Logger::debug( "--- Section [$section_index]: \"$section_name\" ($field_count fields) ---" );

            // Log ALL fields in this section
            foreach ( $fields as $field_index => $field ) {
                $field_name = $field['name'] ?? 'unnamed';
                $field_label = $field['label'] ?? '';
                $field_type = $field['type'] ?? '';
                $input_type = $field['inputType'] ?? '';
                // Cast on the way in too: an older FL or an MCP client may still send a
                // numeric groupId, and this feeds group matching below.
                $field_group = (string) ( $field['groupName'] ?? ( isset( $field['groupId'] ) ? $field['groupId'] : '' ) );

                $details = "[$field_index] name=\"$field_name\"";
                if ( $field_label && $field_label !== $field_name ) {
                    $details .= " label=\"$field_label\"";
                }
                if ( $input_type ) {
                    $details .= " type=$input_type";
                }
                if ( $field_group ) {
                    $details .= " group=\"$field_group\"";
                }

                WPAI_Bridge_Logger::debug( "  $details" );
            }
        }

        WPAI_Bridge_Logger::debug( '' );
        $tax_count = count( $schema['taxonomies'] ?? array() );
        WPAI_Bridge_Logger::debug( "--- Taxonomies ($tax_count items) ---" );
        foreach ( $schema['taxonomies'] ?? array() as $tax ) {
            $tax_name = $tax['name'] ?? 'unknown';
            $tax_label = $tax['label'] ?? '';
            WPAI_Bridge_Logger::debug( "  - $tax_name" . ( $tax_label ? " ($tax_label)" : '' ) );
        }

        WPAI_Bridge_Logger::debug( '' );
        WPAI_Bridge_Logger::debug( "TOTAL: $total_fields fields across " . count( $schema['sections'] ) . " sections" );
        WPAI_Bridge_Logger::separator( '========================================' );
        WPAI_Bridge_Logger::debug( '=== End Complete Schema for LLM ===' );
        WPAI_Bridge_Logger::separator( '========================================' );

        return $schema;
    }

    /**
     * Render the actual Step 3 template page to HTML
     * This ACTUALLY includes and renders the WP All Import template files
     * to capture ALL form fields exactly as they appear on the real page.
     */
    private function render_template_page( $import, $import_type, $options ) {
        WPAI_Bridge_Logger::debug( ' render_template_page starting for type: ' . $import_type );

        // Load WordPress admin functions that templates may need (not loaded in REST API context)
        if ( ! function_exists( 'get_page_templates' ) ) {
            require_once ABSPATH . 'wp-admin/includes/theme.php';
        }
        if ( ! function_exists( 'page_template_dropdown' ) ) {
            require_once ABSPATH . 'wp-admin/includes/template.php';
        }
        if ( ! function_exists( 'wp_dropdown_pages' ) ) {
            require_once ABSPATH . 'wp-admin/includes/post.php';
        }

        // Get WP All Import root directory
        $pmxi_root = defined( 'PMXI_ROOT_DIR' ) ? PMXI_ROOT_DIR : ( class_exists( 'PMXI_Plugin' ) ? PMXI_Plugin::ROOT_DIR : WP_PLUGIN_DIR . '/wp-all-import-pro' );

        // ALWAYS start with full default import options to ensure all required keys exist.
        // This prevents "Undefined array key" warnings in PHP 8.x.
        // Merge core defaults, then every active add-on's defaults (WooCommerce, ACF,
        // etc.) — same pattern WP All Import uses in its own controllers.
        $default_options = PMXI_Plugin::get_default_import_options();
        foreach ( PMXI_Admin_Addons::get_active_addons() as $class ) {
            if ( class_exists( $class ) ) {
                $default_options += call_user_func( array( $class, 'get_default_import_options' ) );
            }
        }

        // Merge provided options over defaults (provided options take precedence)
        if ( ! empty( $options ) ) {
            $default_options = array_merge( $default_options, $options );
        }
        $default_options['custom_type'] = $import_type;

        // Set up all the variables that the template files expect
        $post = $default_options;
        $post_type = $import_type;
        $isWizard = true;

        // Get meta keys for custom fields template
        $meta_keys = $this->get_meta_keys_for_post_type( $import_type );

        // Suppress PHP warnings during template rendering (PHP 8.x throws warnings for missing array keys)
        $old_error_reporting = error_reporting();
        error_reporting( 0 ); // Suppress ALL errors during template rendering

        // Replace any custom error handler with a silent one to prevent HTML output
        set_error_handler( function( $errno, $errstr, $errfile, $errline ) {
            return true; // Suppress all errors
        }, E_ALL );

        // Record the current output buffer level so we can restore to it
        $original_ob_level = ob_get_level();

        // Start capturing HTML
        ob_start();
        $html = '';

        try {
            // Use the same pmxi_visible_template_sections filter that WP All Import's
            // template.php uses to determine which sections to render per post type.
            // This ensures we match the actual Step 3 UI exactly, including sections
            // added or removed by add-ons via this filter.
            $visible_sections = apply_filters(
                'pmxi_visible_template_sections',
                array( 'caption', 'main', 'taxonomies', 'cf', 'featured', 'other', 'nested' ),
                $post_type
            );

            // ========================================
            // SECTION: Caption (Title & Content)
            // ========================================
            if ( in_array( 'caption', $visible_sections, true ) ) {
                $this->render_caption_section_html( $post, $post_type );
            }

            // ========================================
            // SECTION: Main (comments/reviews template + addon hooks)
            // ========================================
            if ( in_array( 'main', $visible_sections, true ) ) {
                if ( in_array( $post_type, array( 'comments', 'woo_reviews' ), true ) ) {
                    $comments_template = $pmxi_root . '/views/admin/import/template/_comments_main_template.php';
                    if ( file_exists( $comments_template ) ) {
                        include $comments_template;
                    }
                }
                do_action( 'pmxi_extend_options_main', $post_type, $post );
            }

            // ========================================
            // SECTION: Featured (Images)
            // ========================================
            if ( in_array( 'featured', $visible_sections, true ) ) {
                $is_images_enabled = apply_filters( 'wp_all_import_is_images_section_enabled', true, $post_type );
                if ( $is_images_enabled ) {
                    $images_title = apply_filters( 'pmxi_section_title_featured', __( 'Images', 'wp-all-import-pro' ), $post_type );
                    if ( class_exists( 'PMXI_API' ) && method_exists( 'PMXI_API', 'add_additional_images_section' ) ) {
                        PMXI_API::add_additional_images_section( $images_title, '', $post, $post_type, true, true );
                    }
                }
                do_action( 'pmxi_extend_options_featured', $post_type, $post );
            }

            // ========================================
            // SECTION: Custom Fields / Term Meta / Comment Meta
            // ========================================
            if ( in_array( 'cf', $visible_sections, true ) ) {
                if ( $post_type === 'taxonomies' ) {
                    $term_meta_template = $pmxi_root . '/views/admin/import/template/_term_meta_template.php';
                    if ( file_exists( $term_meta_template ) ) {
                        include $term_meta_template;
                    }
                } elseif ( in_array( $post_type, array( 'comments', 'woo_reviews' ), true ) ) {
                    $comments_meta_template = $pmxi_root . '/views/admin/import/template/_comments_meta_template.php';
                    if ( file_exists( $comments_meta_template ) ) {
                        include $comments_meta_template;
                    }
                } else {
                    $cf_template = $pmxi_root . '/views/admin/import/template/_custom_fields_template.php';
                    if ( file_exists( $cf_template ) ) {
                        include $cf_template;
                    }
                }
                do_action( 'pmxi_extend_options_custom_fields', $post_type, $post );
            }

            // ========================================
            // SECTION: Taxonomies
            // ========================================
            if ( in_array( 'taxonomies', $visible_sections, true ) ) {
                $tax_template = $pmxi_root . '/views/admin/import/template/_taxonomies_template.php';
                if ( file_exists( $tax_template ) ) {
                    include $tax_template;
                }
                do_action( 'pmxi_extend_options_taxonomies', $post_type, $post );
            }

            // ========================================
            // SECTION: Other Options
            // ========================================
            if ( in_array( 'other', $visible_sections, true ) ) {
                if ( $post_type === 'taxonomies' ) {
                    $other_template = $pmxi_root . '/views/admin/import/template/_term_other_template.php';
                } else {
                    $other_template = $pmxi_root . '/views/admin/import/template/_other_template.php';
                }
                // WP All Import skips _other_template.php for comments/woo_reviews
                // even when 'other' is in visible_sections
                if ( ! in_array( $post_type, array( 'comments', 'woo_reviews' ), true ) && file_exists( $other_template ) ) {
                    include $other_template;
                }
                do_action( 'pmxi_extend_options_other', $post_type, $post );
            }

            // ========================================
            // SECTION: Nested (currently commented out in WP All Import,
            // but add-ons may hook onto pmxi_extend_options_nested)
            // ========================================
            if ( in_array( 'nested', $visible_sections, true ) ) {
                do_action( 'pmxi_extend_options_nested', $post_type );
            }

            // ========================================
            // ADDON SECTIONS: ACF, Toolset, JetEngine, etc.
            // ========================================
            $this->render_acf_field_groups_html( $import, $options );
            $this->render_toolset_field_groups_html( $import, $options );
            $this->render_new_api_addons_html( $import, $import_type, $options );

        } finally {
            // ALWAYS capture the buffer, even if an exception was thrown
            $html = ob_get_clean();

            // Clean any additional buffers that might have been started
            while ( ob_get_level() > $original_ob_level ) {
                ob_end_clean();
            }

            // Restore error handler and error reporting
            restore_error_handler();
            error_reporting( $old_error_reporting );
        }

        WPAI_Bridge_Logger::debug( ' Captured HTML length: ' . strlen( $html ) );
        return $html;
    }

    /**
     * Render the Title & Content (caption) section HTML
     * This mirrors the caption section in WP All Import's template.php
     */
    private function render_caption_section_html( $post, $post_type ) {
        // Determine section title based on post type
        if ( in_array( $post_type, array( 'comments', 'woo_reviews' ) ) ) {
            $section_title = __( 'Comment', 'wp-all-import-pro' );
        } elseif ( $post_type === 'taxonomies' ) {
            $section_title = __( 'Name & Description', 'wp-all-import-pro' );
        } elseif ( $post_type === 'product' ) {
            $section_title = __( 'Title & Description', 'wp-all-import-pro' );
        } else {
            $section_title = __( 'Title & Content', 'wp-all-import-pro' );
        }
        $section_title = apply_filters( 'pmxi_section_title_caption', $section_title, $post_type );
        ?>
        <div class="wpallimport-collapsed wpallimport-section">
            <div class="wpallimport-content-section">
                <div class="wpallimport-collapsed-header">
                    <h3><?php echo esc_html( $section_title ); ?></h3>
                </div>
                <div class="wpallimport-collapsed-content">
                    <?php if ( ! in_array( $post_type, array( 'comments', 'woo_reviews' ) ) ) : ?>
                    <div id="titlediv">
                        <div id="titlewrap">
                            <input id="wpallimport-title" class="widefat" type="text" name="title" value="<?php echo esc_attr( $post['title'] ?? '' ); ?>" />
                        </div>
                    </div>
                    <?php endif; ?>

                    <div id="poststuff">
                        <div class="postarea">
                            <textarea name="content" class="wpallimport-plugin-editor"><?php echo esc_textarea( $post['content'] ?? '' ); ?></textarea>
                        </div>
                    </div>

                    <?php if ( post_type_supports( $post_type, 'excerpt' ) || $post_type === 'attachment' ) : ?>
                    <div class="template_input">
                        <input type="text" name="post_excerpt" value="<?php echo esc_attr( $post['post_excerpt'] ?? '' ); ?>" />
                    </div>
                    <?php endif; ?>

                    <!-- Advanced Options -->
                    <div class="wpallimport-collapsed wpallimport-section">
                        <div class="wpallimport-content-section">
                            <div class="wpallimport-collapsed-header">
                                <h3><?php _e( 'Advanced Options', 'wp-all-import-pro' ); ?></h3>
                            </div>
                            <div class="wpallimport-collapsed-content">
                                <div class="input pmxi_option">
                                    <input type="hidden" name="is_keep_linebreaks" value="0" />
                                    <input type="checkbox" id="is_keep_linebreaks" name="is_keep_linebreaks" value="1" <?php checked( ! empty( $post['is_keep_linebreaks'] ) ); ?> />
                                    <label for="is_keep_linebreaks"><?php _e( 'Keep line breaks from file', 'wp-all-import-pro' ); ?></label>
                                </div>
                                <div class="input pmxi_option">
                                    <input type="hidden" name="is_leave_html" value="0" />
                                    <input type="checkbox" id="is_leave_html" name="is_leave_html" value="1" <?php checked( ! empty( $post['is_leave_html'] ) ); ?> />
                                    <label for="is_leave_html"><?php _e( 'Decode HTML entities with html_entity_decode', 'wp-all-import-pro' ); ?></label>
                                </div>
                                <div class="input pmxi_option">
                                    <input type="hidden" name="is_convert_to_blocks" value="0" />
                                    <input type="checkbox" id="is_convert_to_blocks" name="is_convert_to_blocks" value="1" <?php checked( ! empty( $post['is_convert_to_blocks'] ) ); ?> />
                                    <label for="is_convert_to_blocks"><?php _e( 'Convert content to Gutenberg blocks', 'wp-all-import-pro' ); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get meta keys for a post type (used by custom fields template)
     */
    private function get_meta_keys_for_post_type( $post_type ) {
        global $wpdb;

        $meta_keys = array();

        if ( $post_type === 'taxonomies' ) {
            // Get term meta keys
            $keys = $wpdb->get_col( "SELECT DISTINCT meta_key FROM {$wpdb->termmeta} WHERE meta_key NOT LIKE '\_%' ORDER BY meta_key" );
        } elseif ( $post_type === 'import_users' ) {
            // Get user meta keys
            $keys = $wpdb->get_col( "SELECT DISTINCT meta_key FROM {$wpdb->usermeta} WHERE meta_key NOT LIKE '\_%' ORDER BY meta_key" );
        } else {
            // Get post meta keys for the specific post type
            $keys = $wpdb->get_col( $wpdb->prepare(
                "SELECT DISTINCT pm.meta_key FROM {$wpdb->postmeta} pm
                 INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                 WHERE p.post_type = %s AND pm.meta_key NOT LIKE '\_%'
                 ORDER BY pm.meta_key",
                $post_type
            ) );
        }

        if ( ! empty( $keys ) ) {
            $meta_keys = array_values( array_unique( $keys ) );
        }

        return $meta_keys;
    }

    /**
     * Render ACF field groups HTML
     */
    private function render_acf_field_groups_html( $import, $options ) {
        if ( ! class_exists( 'PMAI_Plugin' ) || ! class_exists( 'pmai_acf_add_on\groups\GroupFactory' ) ) {
            return;
        }

        $acf_groups = PMXI_Plugin::$session ? PMXI_Plugin::$session->get( 'acf_groups' ) : array();
        if ( empty( $acf_groups ) ) {
            return;
        }

        foreach ( $acf_groups as $group ) {
            try {
                $group_obj = \pmai_acf_add_on\groups\GroupFactory::create( $group, $options );
                $group_obj->view();
            } catch ( Exception $e ) {
                // Continue on error
            }
        }
    }

    /**
     * Render Toolset field groups HTML
     */
    private function render_toolset_field_groups_html( $import, $options ) {
        if ( ! class_exists( 'PMTI_Plugin' ) || ! class_exists( 'wpai_toolset_types_add_on\groups\GroupFactory' ) ) {
            return;
        }

        $toolset_groups = PMXI_Plugin::$session ? PMXI_Plugin::$session->get( 'wpcs_groups' ) : array();
        if ( empty( $toolset_groups ) ) {
            return;
        }

        foreach ( $toolset_groups as $group ) {
            try {
                $group_obj = \wpai_toolset_types_add_on\groups\GroupFactory::create( $group, $options );
                $group_obj->view();
            } catch ( Exception $e ) {
                // Continue on error
            }
        }
    }

    /**
     * Render new API addon field groups HTML (JetEngine, MetaBox, etc.)
     */
    private function render_new_api_addons_html( $import, $import_type, $options ) {
        if ( ! class_exists( '\Wpai\AddonAPI\PMXI_Addon_Manager' ) ) {
            return;
        }

        $addons = \Wpai\AddonAPI\PMXI_Addon_Manager::get_addons();
        if ( empty( $addons ) ) {
            return;
        }

        foreach ( $addons as $addon ) {
            if ( ! $addon->isAvailableForType( $import_type, $options ) ) {
                continue;
            }

            try {
                // Render the addon's accordion/view
                if ( method_exists( $addon, 'getAccordion' ) ) {
                    $accordion = $addon->getAccordion();
                    if ( $accordion && method_exists( $accordion, 'render' ) ) {
                        $accordion->render( $import_type, $options );
                    }
                }
            } catch ( Exception $e ) {
                // Continue on error
            }
        }
    }

    /**
     * Parse rendered HTML into schema with sections and fields
     * This mirrors what Step 3 JS extractTemplateSchema() does with the DOM
     */
    private function parse_template_html_into_schema( $html, $import_type ) {
        $schema = array(
            'sections' => array(),
            'taxonomies' => array(),
        );

        if ( empty( $html ) ) {
            WPAI_Bridge_Logger::debug( ' No HTML to parse' );
            return $schema;
        }

        // Parse HTML with DOMDocument
        $dom = new DOMDocument();
        libxml_use_internal_errors( true );
        $dom->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
        libxml_clear_errors();

        $xpath = new DOMXPath( $dom );

        // Track seen fields to avoid duplicates
        $seen_fields = array();

        // Find all sections - both .wpallimport-section and .wpallimport-collapsed
        // This mirrors the Step 3 JS: $('.wpallimport-section, .wpallimport-collapsed')
        $sections = $xpath->query( "//*[contains(@class, 'wpallimport-section') or contains(@class, 'wpallimport-collapsed')]" );

        WPAI_Bridge_Logger::debug( ' Found ' . $sections->length . ' sections in HTML' );

        foreach ( $sections as $section_node ) {
            $class = $section_node->getAttribute( 'class' );

            // Get section title from h3
            $title_nodes = $xpath->query( ".//h3", $section_node );
            $section_title = '';
            if ( $title_nodes->length > 0 ) {
                $section_title = trim( $title_nodes->item(0)->textContent );
            }

            if ( empty( $section_title ) ) {
                continue;
            }

            // Check for special addon sections
            $is_acf = strpos( $class, 'pmai_options' ) !== false;
            $is_toolset = strpos( $class, 'pmti_options' ) !== false;
            $is_new_api_addon = strpos( $class, 'pmxi-addon' ) !== false;

            // Extract fields from this section
            $section_fields = $this->extract_fields_from_section( $section_node, $xpath, $seen_fields, $section_title );

            if ( ! empty( $section_fields ) ) {
                $schema['sections'][] = array(
                    'name' => $section_title,
                    'fields' => $section_fields,
                );
            }
        }

        // If no sections found, try to extract all fields as one section
        if ( empty( $schema['sections'] ) ) {
            WPAI_Bridge_Logger::debug( ' No sections found, extracting all fields' );
            $all_fields = $this->extract_all_fields_from_dom( $dom, $xpath, $seen_fields );
            if ( ! empty( $all_fields ) ) {
                $schema['sections'][] = array(
                    'name' => 'Template Fields',
                    'fields' => $all_fields,
                );
            }
        }

        return $schema;
    }

    /**
     * Extract fields from a section DOM node
     */
    private function extract_fields_from_section( $section_node, $xpath, &$seen_fields, $section_title ) {
        $fields = array();

        // Find all inputs, textareas, and selects in this section
        $inputs = $xpath->query( ".//input | .//textarea | .//select", $section_node );

        foreach ( $inputs as $input ) {
            $name = $input->getAttribute( 'name' );
            $type = $input->getAttribute( 'type' );

            // Skip if no name, already seen, or is a button/submit
            if ( empty( $name ) || isset( $seen_fields[ $name ] ) || in_array( $type, array( 'submit', 'button' ) ) ) {
                continue;
            }

            // Skip template placeholders
            $parent = $input->parentNode;
            while ( $parent ) {
                if ( $parent instanceof DOMElement && strpos( $parent->getAttribute( 'class' ), 'template' ) !== false ) {
                    continue 2;
                }
                $parent = $parent->parentNode;
            }

            $seen_fields[ $name ] = true;

            // Try to find label
            $label = $this->find_label_for_input( $input, $xpath );

            $field = array(
                'name' => $name,
                'label' => $label ?: $name,
                'type' => $input->nodeName,
                'inputType' => $type ?: 'text',
            );
            $opts = $this->extract_select_options( $input, $xpath );
            if ( null !== $opts ) {
                $field['options'] = $opts;
            }
            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * Capture a <select>'s <option> value/label pairs so select fields expose
     * their allowed choices in the normalized descriptors (get-typed-fields).
     * Returns null for non-select nodes or an option-less select.
     */
    private function extract_select_options( $input, $xpath ) {
        if ( 'select' !== strtolower( $input->nodeName ) ) {
            return null;
        }
        $options = array();
        foreach ( $xpath->query( './/option', $input ) as $option ) {
            $value = $option->getAttribute( 'value' );
            $text  = trim( (string) $option->textContent );
            // Skip a pure placeholder option (no value and no label).
            if ( '' === $value && '' === $text ) {
                continue;
            }
            $options[] = array( 'value' => $value, 'label' => '' !== $text ? $text : $value );
        }
        return empty( $options ) ? null : $options;
    }

    /**
     * Extract all fields from entire DOM (fallback)
     */
    private function extract_all_fields_from_dom( $dom, $xpath, &$seen_fields ) {
        $fields = array();

        $inputs = $xpath->query( "//input | //textarea | //select" );

        foreach ( $inputs as $input ) {
            $name = $input->getAttribute( 'name' );
            $type = $input->getAttribute( 'type' );

            if ( empty( $name ) || isset( $seen_fields[ $name ] ) || in_array( $type, array( 'submit', 'button' ) ) ) {
                continue;
            }

            $seen_fields[ $name ] = true;

            $label = $this->find_label_for_input( $input, $xpath );

            $field = array(
                'name' => $name,
                'label' => $label ?: $name,
                'type' => $input->nodeName,
                'inputType' => $type ?: 'text',
            );
            $opts = $this->extract_select_options( $input, $xpath );
            if ( null !== $opts ) {
                $field['options'] = $opts;
            }
            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * Add Title & Content section (caption section in template)
     * Matches actual Step 3 DOM fields exactly
     */
    private function add_caption_section( $import_type, $options, $schema ) {
        $section_name = 'Title & Content';

        // Adjust section name based on import type
        if ( $import_type === 'taxonomies' ) {
            $section_name = 'Name & Description';
        } elseif ( $import_type === 'product' ) {
            $section_name = 'Title & Description';
        } elseif ( in_array( $import_type, array( 'comments', 'woo_reviews' ) ) ) {
            $section_name = 'Comment';
        }

        $fields = array();

        // Title field (not for comments)
        if ( ! in_array( $import_type, array( 'comments', 'woo_reviews' ) ) ) {
            $title_label = 'Title';
            if ( $import_type === 'taxonomies' ) {
                $title_label = 'Name';
            }

            $fields[] = array(
                'name' => 'title',
                'label' => $title_label,
                'type' => 'textarea',
                'inputType' => 'text',
                'description' => 'The main title/name for each record. Supports XPath like {title[1]}',
            );
        }

        // Content field
        $content_label = 'Content';
        if ( $import_type === 'taxonomies' ) {
            $content_label = 'Description';
        } elseif ( in_array( $import_type, array( 'comments', 'woo_reviews' ) ) ) {
            $content_label = 'Comment Text';
        }

        $fields[] = array(
            'name' => 'content',
            'label' => $content_label,
            'type' => 'textarea',
            'inputType' => 'text',
            'description' => 'The main content/description text. Supports XPath like {content[1]}',
        );

        // Excerpt (for posts/pages/CPTs only)
        if ( ! in_array( $import_type, array( 'taxonomies', 'comments', 'woo_reviews', 'import_users' ) ) ) {
            $fields[] = array(
                'name' => 'post_excerpt',
                'label' => 'Excerpt',
                'type' => 'textarea',
                'inputType' => 'text',
                'description' => 'Short summary or excerpt of the content',
            );
        }

        // Content processing options - these appear in the actual Step 3 DOM
        $fields[] = array(
            'name' => 'is_keep_linebreaks',
            'label' => 'Keep line breaks from file',
            'type' => 'checkbox',
            'inputType' => 'checkbox',
            'description' => 'Preserve line breaks from the import file in the content',
        );

        $fields[] = array(
            'name' => 'is_leave_html',
            'label' => 'Decode HTML entities with html_entity_decode',
            'type' => 'checkbox',
            'inputType' => 'checkbox',
            'description' => 'Convert HTML entities like &amp; back to their original characters',
        );

        $fields[] = array(
            'name' => 'is_convert_to_blocks',
            'label' => 'Convert content to Gutenberg blocks',
            'type' => 'checkbox',
            'inputType' => 'checkbox',
            'description' => 'Automatically convert imported content to WordPress block format',
        );

        $schema['sections'][] = array(
            'name' => $section_name,
            'fields' => $fields,
        );

        return $schema;
    }

    /**
     * Add Images section (featured section in template)
     * Matches actual Step 3 DOM fields exactly - all 31 fields
     */
    private function add_images_section( $import_type, $options, $schema ) {
        // Skip for taxonomy and user imports
        if ( in_array( $import_type, array( 'taxonomies', 'import_users' ) ) ) {
            return $schema;
        }

        // Check if images section is enabled
        $is_images_enabled = apply_filters( 'wp_all_import_is_images_section_enabled', true, $import_type );
        if ( ! $is_images_enabled ) {
            return $schema;
        }

        $fields = array(
            // Download images option (main toggle)
            array(
                'name' => 'download_images',
                'label' => 'Download images hosted elsewhere',
                'type' => 'checkbox',
                'inputType' => 'checkbox',
                'description' => 'Download images from external URLs to the media library',
            ),
            // Download images - featured image fields
            array(
                'name' => 'download_featured_delim',
                'label' => 'Separator for downloaded featured images',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'Character to separate multiple featured image URLs when downloading',
            ),
            array(
                'name' => 'download_featured_image',
                'label' => 'Download featured image URL',
                'type' => 'textarea',
                'inputType' => 'text',
                'description' => 'URL(s) of featured images to download',
            ),
            // Gallery images for download mode
            array(
                'name' => 'gallery_featured_delim',
                'label' => 'Separator for downloaded gallery images',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'Character to separate multiple gallery image URLs when downloading',
            ),
            array(
                'name' => 'gallery_featured_image',
                'label' => 'Download gallery image URLs',
                'type' => 'textarea',
                'inputType' => 'text',
                'description' => 'URL(s) of gallery images to download',
            ),
            // Use images in Media Library mode
            array(
                'name' => 'featured_delim',
                'label' => 'Separator for media library images',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'Character to separate multiple image filenames',
            ),
            array(
                'name' => 'featured_image',
                'label' => 'Featured image filename or URL',
                'type' => 'textarea',
                'inputType' => 'text',
                'description' => 'Filename, URL, or path to the featured image',
            ),
            // Search existing images options
            array(
                'name' => 'search_existing_images',
                'label' => 'Search through the Media Library for existing images before importing new images',
                'type' => 'checkbox',
                'inputType' => 'checkbox',
                'description' => 'Look for matching images already in media library',
            ),
            array(
                'name' => 'search_existing_images_logic',
                'label' => 'Match image by URL',
                'type' => 'radio',
                'inputType' => 'radio',
                'description' => 'How to match existing images (by URL or filename)',
            ),
            // Image handling options
            array(
                'name' => 'do_not_remove_images',
                'label' => 'Keep images currently in Media Library',
                'type' => 'checkbox',
                'inputType' => 'checkbox',
                'description' => 'Do not delete existing images when updating posts',
            ),
            array(
                'name' => 'set_image_meta_title',
                'label' => 'Set image title',
                'type' => 'checkbox',
                'inputType' => 'checkbox',
                'description' => 'Set the title metadata for imported images',
            ),
            array(
                'name' => 'image_meta_title',
                'label' => 'Image title value',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'XPath or text for image title',
            ),
            array(
                'name' => 'set_image_meta_caption',
                'label' => 'Set image caption',
                'type' => 'checkbox',
                'inputType' => 'checkbox',
                'description' => 'Set the caption metadata for imported images',
            ),
            array(
                'name' => 'image_meta_caption',
                'label' => 'Image caption value',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'XPath or text for image caption',
            ),
            array(
                'name' => 'set_image_meta_alt',
                'label' => 'Set image alt text',
                'type' => 'checkbox',
                'inputType' => 'checkbox',
                'description' => 'Set the alt text for imported images',
            ),
            array(
                'name' => 'image_meta_alt',
                'label' => 'Image alt text value',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'XPath or text for image alt text',
            ),
            array(
                'name' => 'set_image_meta_description',
                'label' => 'Set image description',
                'type' => 'checkbox',
                'inputType' => 'checkbox',
                'description' => 'Set the description for imported images',
            ),
            array(
                'name' => 'image_meta_description',
                'label' => 'Image description value',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'XPath or text for image description',
            ),
            array(
                'name' => 'auto_set_extension',
                'label' => 'Auto-detect image extension',
                'type' => 'checkbox',
                'inputType' => 'checkbox',
                'description' => 'Automatically detect file extension from image content',
            ),
            array(
                'name' => 'auto_rename_images',
                'label' => 'Auto-rename images',
                'type' => 'checkbox',
                'inputType' => 'checkbox',
                'description' => 'Automatically rename images during import',
            ),
            array(
                'name' => 'auto_rename_images_suffix',
                'label' => 'Image rename suffix',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'Suffix to add when renaming images',
            ),
            array(
                'name' => 'auto_extensions_list',
                'label' => 'Allowed image extensions',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'Comma-separated list of allowed file extensions',
            ),
            array(
                'name' => 'disable_dynamic_featured_image',
                'label' => 'Disable dynamic featured image detection',
                'type' => 'checkbox',
                'inputType' => 'checkbox',
                'description' => 'Do not automatically set featured image from content',
            ),
            // SEO-related image options
            array(
                'name' => 'images_meta_data',
                'label' => 'Import image SEO meta data',
                'type' => 'checkbox',
                'inputType' => 'checkbox',
                'description' => 'Include SEO metadata when importing images',
            ),
            array(
                'name' => 'images_meta_data_fields',
                'label' => 'Image SEO meta data fields',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'Which SEO fields to import for images',
            ),
            // Webp and format handling
            array(
                'name' => 'import_webp_images',
                'label' => 'Import WebP images',
                'type' => 'checkbox',
                'inputType' => 'checkbox',
                'description' => 'Allow importing WebP format images',
            ),
            array(
                'name' => 'keep_original_images',
                'label' => 'Keep original images',
                'type' => 'checkbox',
                'inputType' => 'checkbox',
                'description' => 'Keep original image file alongside any conversions',
            ),
            // Image sizes
            array(
                'name' => 'create_image_sizes',
                'label' => 'Create thumbnail sizes',
                'type' => 'checkbox',
                'inputType' => 'checkbox',
                'description' => 'Generate WordPress thumbnail sizes for imported images',
            ),
        );

        $schema['sections'][] = array(
            'name' => 'Images',
            'fields' => $fields,
        );

        return $schema;
    }

    /**
     * Add Custom Fields section (cf section in template)
     */
    private function add_custom_fields_section( $import_type, $options, $schema ) {
        // Get existing meta keys for this post type
        $meta_keys = $this->get_meta_keys_for_type( $import_type );

        $fields = array();

        // Add the dynamic custom fields inputs
        $fields[] = array(
            'name' => 'custom_name[]',
            'label' => 'Custom Field Name',
            'type' => 'input',
            'inputType' => 'text',
            'description' => 'The meta key name for this custom field. Can add multiple.',
            'repeatable' => true,
        );

        $fields[] = array(
            'name' => 'custom_value[]',
            'label' => 'Custom Field Value',
            'type' => 'textarea',
            'inputType' => 'text',
            'description' => 'The value to import for this custom field. Use XPath expressions like {field_name[1]}.',
            'repeatable' => true,
        );

        // Add existing meta keys as suggestions
        if ( ! empty( $meta_keys ) ) {
            $fields[] = array(
                'name' => '_existing_meta_keys',
                'label' => 'Existing Custom Fields',
                'type' => 'info',
                'description' => 'These custom fields already exist for this post type: ' . implode( ', ', array_slice( $meta_keys, 0, 20 ) ),
                'suggestions' => $meta_keys,
            );
        }

        $schema['sections'][] = array(
            'name' => 'Custom Fields',
            'fields' => $fields,
        );

        return $schema;
    }

    /**
     * Get existing meta keys for a post type
     */
    private function get_meta_keys_for_type( $import_type ) {
        global $wpdb;

        $meta_keys = array();

        if ( $import_type === 'taxonomies' ) {
            // Get term meta keys
            $keys = $wpdb->get_col(
                "SELECT DISTINCT meta_key FROM {$wpdb->termmeta} WHERE meta_key NOT LIKE '\_%' ORDER BY meta_key LIMIT 100"
            );
        } elseif ( $import_type === 'import_users' ) {
            // Get user meta keys
            $keys = $wpdb->get_col(
                "SELECT DISTINCT meta_key FROM {$wpdb->usermeta} WHERE meta_key NOT LIKE '\_%' ORDER BY meta_key LIMIT 100"
            );
        } else {
            // Get post meta keys for this post type
            $keys = $wpdb->get_col( $wpdb->prepare(
                "SELECT DISTINCT pm.meta_key
                FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE p.post_type = %s
                AND pm.meta_key NOT LIKE '\_%'
                ORDER BY pm.meta_key
                LIMIT 100",
                $import_type
            ) );
        }

        return is_array( $keys ) ? $keys : array();
    }

    /**
     * Add Taxonomies section
     */
    private function add_taxonomies_section( $import_type, $options, $schema ) {
        // Skip for taxonomy and user imports
        if ( in_array( $import_type, array( 'taxonomies', 'import_users' ) ) ) {
            return $schema;
        }

        // Get taxonomies for this post type
        $exclude_taxonomies = apply_filters( 'pmxi_exclude_taxonomies',
            class_exists( 'PMWI_Plugin' )
                ? array( 'post_format', 'product_type', 'product_shipping_class' )
                : array( 'post_format' )
        );

        $post_taxonomies = array_diff_key(
            get_taxonomies_by_object_type( array( $import_type ), 'objects' ),
            array_flip( $exclude_taxonomies )
        );

        if ( empty( $post_taxonomies ) ) {
            return $schema;
        }

        $fields = array();

        foreach ( $post_taxonomies as $taxonomy ) {
            // Skip hidden taxonomies
            if ( empty( $taxonomy->labels->name ) ) {
                continue;
            }

            // Skip WooCommerce product attribute taxonomies
            if ( class_exists( 'PMWI_Plugin' ) && strpos( $taxonomy->name, 'pa_' ) === 0 && $import_type === 'product' ) {
                continue;
            }

            $tax_name = $taxonomy->name;
            $tax_label = $taxonomy->labels->name;

            // Enable/disable checkbox
            $fields[] = array(
                'name' => "tax_assing[{$tax_name}]",
                'label' => "Import to {$tax_label}",
                'type' => 'checkbox',
                'inputType' => 'checkbox',
                'description' => "Enable importing to the {$tax_label} taxonomy",
            );

            // Taxonomy logic - single vs multiple
            $fields[] = array(
                'name' => "tax_logic[{$tax_name}]",
                'label' => "{$tax_label} - Assignment Mode",
                'type' => 'radio',
                'inputType' => 'radio',
                'options' => array(
                    array( 'value' => 'single', 'label' => 'Each record has one ' . $taxonomy->labels->singular_name ),
                    array( 'value' => 'multiple', 'label' => 'Each record has multiple ' . $tax_label ),
                    $taxonomy->hierarchical ? array( 'value' => 'hierarchical', 'label' => 'Hierarchical (parent/child)' ) : null,
                ),
                'description' => "How {$tax_label} values are structured in your data",
            );

            // XPath for single taxonomy
            $fields[] = array(
                'name' => "tax_single_xpath[{$tax_name}]",
                'label' => "{$tax_label} - Single Value XPath",
                'type' => 'input',
                'inputType' => 'text',
                'description' => "XPath expression for single {$taxonomy->labels->singular_name} value",
            );

            // XPath for multiple taxonomies
            $fields[] = array(
                'name' => "tax_multiple_xpath[{$tax_name}]",
                'label' => "{$tax_label} - Multiple Values XPath",
                'type' => 'input',
                'inputType' => 'text',
                'description' => "XPath expression for multiple {$tax_label} values",
            );

            // Delimiter for multiple
            $fields[] = array(
                'name' => "tax_multiple_delim[{$tax_name}]",
                'label' => "{$tax_label} - Separator",
                'type' => 'input',
                'inputType' => 'text',
                'description' => "Character that separates multiple {$tax_label} (default: comma)",
            );
        }

        if ( ! empty( $fields ) ) {
            $schema['sections'][] = array(
                'name' => 'Taxonomies, Categories, Tags',
                'fields' => array_filter( $fields ),
            );
        }

        return $schema;
    }

    /**
     * Add Other Options section (other section in template)
     */
    private function add_other_options_section( $import_type, $options, $schema ) {
        // Skip for comments/reviews
        if ( in_array( $import_type, array( 'comments', 'woo_reviews' ) ) ) {
            return $schema;
        }

        $fields = array();
        $custom_type = get_post_type_object( $import_type );
        $type_label = $custom_type ? $custom_type->labels->singular_name : 'Post';

        // Different fields for different import types
        if ( $import_type === 'taxonomies' ) {
            // Term-specific fields
            $fields[] = array(
                'name' => 'taxonomy_slug',
                'label' => 'Term Slug',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'The URL-friendly slug for the term',
            );

            $fields[] = array(
                'name' => 'taxonomy_parent',
                'label' => 'Parent Term',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'Parent term name, slug, or ID for hierarchical taxonomies',
            );
        } elseif ( $import_type === 'import_users' ) {
            // User-specific fields
            $fields[] = array(
                'name' => 'user_login',
                'label' => 'Username',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'The user login/username',
            );

            $fields[] = array(
                'name' => 'user_email',
                'label' => 'Email',
                'type' => 'input',
                'inputType' => 'email',
                'description' => 'User email address',
            );

            $fields[] = array(
                'name' => 'user_pass',
                'label' => 'Password',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'User password (leave empty to auto-generate)',
            );

            $fields[] = array(
                'name' => 'first_name',
                'label' => 'First Name',
                'type' => 'input',
                'inputType' => 'text',
            );

            $fields[] = array(
                'name' => 'last_name',
                'label' => 'Last Name',
                'type' => 'input',
                'inputType' => 'text',
            );

            $fields[] = array(
                'name' => 'role',
                'label' => 'User Role',
                'type' => 'select',
                'inputType' => 'select',
                'options' => array_map( function( $role ) {
                    return array( 'value' => $role, 'label' => ucfirst( $role ) );
                }, array_keys( wp_roles()->roles ) ),
            );
        } else {
            // Standard post fields
            $fields[] = array(
                'name' => 'status',
                'label' => 'Post Status',
                'type' => 'select',
                'inputType' => 'select',
                'options' => array(
                    array( 'value' => 'publish', 'label' => 'Published' ),
                    array( 'value' => 'draft', 'label' => 'Draft' ),
                    array( 'value' => 'pending', 'label' => 'Pending Review' ),
                    array( 'value' => 'private', 'label' => 'Private' ),
                ),
                'description' => 'The publish status for imported posts',
            );

            $fields[] = array(
                'name' => 'status_xpath',
                'label' => 'Post Status (from data)',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'XPath to set status dynamically. Values: publish, draft, pending, private',
            );

            $fields[] = array(
                'name' => 'date',
                'label' => 'Post Date',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'Date for the post. Use any format PHP strtotime() understands, or {xpath[1]} for data value.',
            );

            $fields[] = array(
                'name' => 'post_slug',
                'label' => 'Post Slug',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'The URL-friendly post slug. Leave empty to auto-generate from title.',
            );

            $fields[] = array(
                'name' => 'author',
                'label' => 'Post Author',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'Author by user ID, username, or email address',
            );

            $fields[] = array(
                'name' => 'comment_status',
                'label' => 'Comments',
                'type' => 'select',
                'inputType' => 'select',
                'options' => array(
                    array( 'value' => 'open', 'label' => 'Open' ),
                    array( 'value' => 'closed', 'label' => 'Closed' ),
                ),
                'description' => 'Whether comments are allowed',
            );

            $fields[] = array(
                'name' => 'ping_status',
                'label' => 'Trackbacks/Pingbacks',
                'type' => 'select',
                'inputType' => 'select',
                'options' => array(
                    array( 'value' => 'open', 'label' => 'Open' ),
                    array( 'value' => 'closed', 'label' => 'Closed' ),
                ),
            );

            // Page-specific fields
            if ( $import_type === 'page' || ( $custom_type && $custom_type->hierarchical ) ) {
                $fields[] = array(
                    'name' => 'parent',
                    'label' => 'Page/Post Parent',
                    'type' => 'input',
                    'inputType' => 'text',
                    'description' => 'Parent page/post ID, title, or slug',
                );

                $fields[] = array(
                    'name' => 'page_template',
                    'label' => 'Page Template',
                    'type' => 'select',
                    'inputType' => 'select',
                    'description' => 'Page template to use',
                );
            }

            $fields[] = array(
                'name' => 'order',
                'label' => 'Menu Order',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'Menu order number for sorting',
            );

            // Attachments
            $fields[] = array(
                'name' => 'attachments',
                'label' => 'Download Attachments',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'URLs of files to download and attach to the post',
            );

            $fields[] = array(
                'name' => 'atch_delim',
                'label' => 'Attachment Separator',
                'type' => 'input',
                'inputType' => 'text',
                'description' => 'Character separating multiple attachment URLs',
            );
        }

        if ( ! empty( $fields ) ) {
            $section_name = "Other {$type_label} Options";
            if ( $import_type === 'taxonomies' ) {
                $section_name = 'Other Term Options';
            } elseif ( $import_type === 'import_users' ) {
                $section_name = 'User Details';
            }

            $schema['sections'][] = array(
                'name' => $section_name,
                'fields' => $fields,
            );
        }

        return $schema;
    }

    /**
     * Parse addon HTML to extract sections and fields
     */
    private function parse_addon_html( $html, $schema ) {
        if ( empty( $html ) ) {
            return $schema;
        }

        // Use DOMDocument to parse HTML
        $dom = new DOMDocument();
        @$dom->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
        $xpath = new DOMXPath( $dom );

        // Find all sections (wpallimport-collapsed or wpallimport-section)
        $sections = $xpath->query( "//*[contains(@class, 'wpallimport-collapsed') or contains(@class, 'wpallimport-section')]" );

        foreach ( $sections as $section ) {
            // Skip ACF and Toolset sections - we handle them specially
            $class = $section->getAttribute( 'class' );
            if ( strpos( $class, 'pmai_options' ) !== false || strpos( $class, 'pmti_options' ) !== false ) {
                continue;
            }

            // Skip new API addons (pmxi-addon class) - they're handled in render_new_api_addon_groups
            if ( strpos( $class, 'pmxi-addon' ) !== false ) {
                continue;
            }

            // Get section title
            $title_nodes = $xpath->query( ".//h3", $section );
            $section_title = '';
            if ( $title_nodes->length > 0 ) {
                $section_title = trim( $title_nodes->item(0)->textContent );
            }

            if ( empty( $section_title ) ) {
                continue;
            }

            // Extract fields from this section
            $fields = $this->extract_fields_from_node( $section, $xpath );

            if ( ! empty( $fields ) ) {
                $schema['sections'][] = array(
                    'name' => $section_title,
                    'fields' => $fields,
                );
            }
        }

        return $schema;
    }

    /**
     * Extract fields from a DOM node
     */
    private function extract_fields_from_node( $node, $xpath ) {
        $fields = array();
        $seen = array();

        // Find all inputs, textareas, and selects
        $inputs = $xpath->query( ".//input | .//textarea | .//select", $node );

        foreach ( $inputs as $input ) {
            $name = $input->getAttribute( 'name' );
            $type = $input->getAttribute( 'type' );

            // Skip if no name, already seen, or is a button/submit
            if ( empty( $name ) || isset( $seen[ $name ] ) || in_array( $type, array( 'submit', 'button' ) ) ) {
                continue;
            }

            $seen[ $name ] = true;

            // Try to find label
            $label = $this->find_label_for_input( $input, $xpath );

            $fields[] = array(
                'name' => $name,
                'label' => $label ?: $name,
                'type' => $input->nodeName,
                'inputType' => $type ?: 'text',
            );
        }

        return $fields;
    }

    /**
     * Find label for an input element
     */
    private function find_label_for_input( $input, $xpath ) {
        $id = $input->getAttribute( 'id' );

        // Try to find label by 'for' attribute
        if ( $id ) {
            $labels = $xpath->query( "//label[@for='$id']" );
            if ( $labels->length > 0 ) {
                return trim( $labels->item(0)->textContent );
            }
        }

        // Try to find parent label
        $parent = $input->parentNode;
        while ( $parent ) {
            if ( $parent->nodeName === 'label' ) {
                return trim( $parent->textContent );
            }
            $parent = $parent->parentNode;
        }

        return '';
    }

    /**
     * Render all ACF field groups and extract their fields
     */
    private function render_acf_groups( $import, $options, $schema ) {
        // Check if ACF add-on is active
        if ( ! class_exists( 'PMAI_Plugin' ) || ! class_exists( 'pmai_acf_add_on\groups\GroupFactory' ) ) {
            return $schema;
        }

        // Get ACF groups from session (populated by pmxi_extend_options_custom_fields hook)
        $acf_groups = PMXI_Plugin::$session->get( 'acf_groups' );

        if ( empty( $acf_groups ) ) {
            return $schema;
        }

        WPAI_Bridge_Logger::debug( ' Rendering ' . count( $acf_groups ) . ' ACF field groups' );

        $acf_section = array(
            'name' => 'Advanced Custom Fields Add-On',
            'fields' => array(),
        );

        // Add group toggle checkboxes
        foreach ( $acf_groups as $group ) {
            $group_id = (string) ( $group['ID'] ?? $group['id'] ?? '' );
            $group_title = $group['title'] ?? 'Field Group';

            $acf_section['fields'][] = array(
                'name' => "acf_groups[$group_id]",
                'label' => "Enable Field Group: $group_title",
                'type' => 'checkbox',
                'fieldType' => 'acf_group_toggle',
                'groupId' => $group_id,
                'description' => "Check this to enable importing fields from the \"$group_title\" field group",
            );
        }

        // Render each group's fields
        foreach ( $acf_groups as $group ) {
            $group_id = (string) ( $group['ID'] ?? $group['id'] ?? '' );
            $group_title = $group['title'] ?? 'Field Group';

            // Use the same rendering code as the AJAX handler
            ob_start();
            try {
                $group_obj = \pmai_acf_add_on\groups\GroupFactory::create( $group, $options );
                $group_obj->view();
            } catch ( Exception $e ) {
                WPAI_Bridge_Logger::debug( " Error rendering ACF group $group_id: " . $e->getMessage() );
            }
            $group_html = ob_get_clean();

            // Parse the group HTML to extract fields
            $group_fields = $this->parse_acf_group_html( $group_html, $group_id, $group_title );
            $acf_section['fields'] = array_merge( $acf_section['fields'], $group_fields );
        }

        if ( ! empty( $acf_section['fields'] ) ) {
            $schema['sections'][] = $acf_section;
        }

        return $schema;
    }

    /**
     * Parse ACF group HTML to extract fields
     */
    private function parse_acf_group_html( $html, $group_id, $group_title ) {
        $group_id = (string) $group_id;
        $fields = array();

        if ( empty( $html ) ) {
            return $fields;
        }

        $dom = new DOMDocument();
        @$dom->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
        $xpath = new DOMXPath( $dom );

        // Find all field containers
        $field_nodes = $xpath->query( "//*[contains(@class, 'field')]" );

        $seen = array();

        foreach ( $field_nodes as $field_node ) {
            $class = $field_node->getAttribute( 'class' );

            // Get field key from class
            preg_match( '/field_key-([^\s]+)/', $class, $key_match );
            $field_key = $key_match[1] ?? '';

            // Get field type from class
            preg_match( '/field_type-([^\s]+)/', $class, $type_match );
            $field_type = $type_match[1] ?? 'text';

            // Get field label
            $label_nodes = $xpath->query( ".//label", $field_node );
            $field_label = '';
            if ( $label_nodes->length > 0 ) {
                $field_label = trim( $label_nodes->item(0)->textContent );
            }

            // Extract all inputs from this field
            $inputs = $xpath->query( ".//input | .//textarea | .//select", $field_node );

            foreach ( $inputs as $input ) {
                $name = $input->getAttribute( 'name' );
                $type = $input->getAttribute( 'type' );

                if ( empty( $name ) || isset( $seen[ $name ] ) || in_array( $type, array( 'submit', 'button' ) ) ) {
                    continue;
                }

                $seen[ $name ] = true;

                // Build descriptive label
                $full_label = "[$group_title] $field_label";

                // Try to get more specific label for this input
                $input_label = $this->find_label_for_input( $input, $xpath );
                if ( $input_label && $input_label !== $field_label ) {
                    $full_label .= " - $input_label";
                }

                $fields[] = array(
                    'name' => $name,
                    'label' => $full_label,
                    'type' => $input->nodeName,
                    'fieldType' => 'acf_' . $field_type,
                    'fieldKey' => $field_key,
                    'groupId' => $group_id,
                    'groupName' => $group_title,
                    'inputType' => $type ?: 'text',
                );
            }
        }

        return $fields;
    }

    /**
     * Render all Toolset field groups and extract their fields
     */
    private function render_toolset_groups( $import, $options, $schema ) {
        // Check if Toolset add-on is active
        if ( ! class_exists( 'PMTI_Plugin' ) || ! class_exists( 'wpai_toolset_types_add_on\groups\GroupFactory' ) ) {
            return $schema;
        }

        // Get Toolset groups from session
        $toolset_groups = PMXI_Plugin::$session->get( 'wpcs_groups' );

        if ( empty( $toolset_groups ) ) {
            return $schema;
        }

        WPAI_Bridge_Logger::debug( ' Rendering ' . count( $toolset_groups ) . ' Toolset field groups' );

        $toolset_section = array(
            'name' => 'Toolset Types Add-On',
            'fields' => array(),
        );

        // Add group toggle checkboxes
        foreach ( $toolset_groups as $group ) {
            $group_id = (string) ( $group['id'] ?? '' );
            $group_name = $group['name'] ?? 'Field Group';

            $toolset_section['fields'][] = array(
                'name' => "wpcs_groups[$group_id]",
                'label' => "Enable Field Group: $group_name",
                'type' => 'checkbox',
                'fieldType' => 'toolset_group_toggle',
                'groupId' => $group_id,
                'description' => "Check this to enable importing fields from the \"$group_name\" field group",
            );
        }

        // Render each group's fields
        foreach ( $toolset_groups as $group ) {
            $group_id = (string) ( $group['id'] ?? '' );
            $group_name = $group['name'] ?? 'Field Group';

            ob_start();
            try {
                $group_obj = \wpai_toolset_types_add_on\groups\GroupFactory::create( $group, $options );
                $group_obj->view();
            } catch ( Exception $e ) {
                WPAI_Bridge_Logger::debug( " Error rendering Toolset group $group_id: " . $e->getMessage() );
            }
            $group_html = ob_get_clean();

            // Parse the group HTML to extract fields
            $group_fields = $this->parse_toolset_group_html( $group_html, $group_id, $group_name );
            $toolset_section['fields'] = array_merge( $toolset_section['fields'], $group_fields );
        }

        if ( ! empty( $toolset_section['fields'] ) ) {
            $schema['sections'][] = $toolset_section;
        }

        return $schema;
    }

    /**
     * Parse Toolset group HTML to extract fields
     */
    private function parse_toolset_group_html( $html, $group_id, $group_name ) {
        $group_id = (string) $group_id;
        $fields = array();

        if ( empty( $html ) ) {
            return $fields;
        }

        $dom = new DOMDocument();
        @$dom->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
        $xpath = new DOMXPath( $dom );

        // Find all inputs
        $inputs = $xpath->query( "//input | //textarea | //select" );

        $seen = array();

        foreach ( $inputs as $input ) {
            $name = $input->getAttribute( 'name' );
            $type = $input->getAttribute( 'type' );

            if ( empty( $name ) || isset( $seen[ $name ] ) || in_array( $type, array( 'submit', 'button' ) ) ) {
                continue;
            }

            $seen[ $name ] = true;

            // Get label
            $label = $this->find_label_for_input( $input, $xpath );
            $full_label = "[$group_name] " . ( $label ?: $name );

            $fields[] = array(
                'name' => $name,
                'label' => $full_label,
                'type' => $input->nodeName,
                'fieldType' => 'toolset',
                'groupId' => $group_id,
                'groupName' => $group_name,
                'inputType' => $type ?: 'text',
            );
        }

        return $fields;
    }

    /**
     * Add ACF fields directly from the ACF API (not from HTML parsing)
     * This is needed because ACF field groups are loaded via AJAX on Step 3
     *
     * IMPORTANT: Field names must match the format used by the ACF Add-On:
     * - Group toggle: acf[group-slug] (slug is sanitized group title)
     * - Child fields: fields[field_xxx] (where field_xxx is the ACF field key)
     */
    private function add_acf_fields_from_api( $import_type, $options, $schema ) {
        // Check if ACF and ACF Add-On are active
        if ( ! function_exists( 'acf_get_field_groups' ) || ! class_exists( 'PMAI_Plugin' ) ) {
            return $schema;
        }

        // Get ACF field groups based on import type
        // For taxonomy and user imports, the ACF Add-On shows ALL groups (not filtered by location)
        // because users may want to import fields regardless of where they're normally displayed
        if ( in_array( $import_type, array( 'taxonomies', 'import_users' ), true ) ) {
            // Get ALL ACF field groups - matches WP All Import ACF Add-On behavior
            $acf_groups = acf_get_field_groups();
            WPAI_Bridge_Logger::debug( 'Getting ALL ACF field groups for ' . $import_type . ' import: ' . count( $acf_groups ) . ' groups' );
        } else {
            // For post types, filter by post type location rule
            $acf_groups = acf_get_field_groups( array( 'post_type' => $import_type ) );
        }

        if ( empty( $acf_groups ) ) {
            WPAI_Bridge_Logger::debug( 'No ACF field groups found for import type: ' . $import_type );
            return $schema;
        }

        WPAI_Bridge_Logger::debug( ' Getting ACF fields from API for ' . count( $acf_groups ) . ' groups' );

        $acf_fields = array();

        foreach ( $acf_groups as $group ) {
            $group_id = (string) ( $group['ID'] ?? $group['key'] ?? '' );
            $group_title = $group['title'] ?? 'ACF Group';

            // The ACF Add-On resolves the enable toggle via pmai_get_acf_group_by_slug(),
            // which matches the field group's key/post_excerpt (e.g. "group_abc123") or its
            // numeric post ID -- NOT sanitize_title_with_dashes( $title ). Emit the same
            // identifier the add-on's own form uses (slug, else key, else ID) so the toggle
            // the analysis references maps onto the group the save/import actually activates.
            if ( ! empty( $group['slug'] ) ) {
                $group_toggle_key = $group['slug'];
            } elseif ( ! empty( $group['key'] ) ) {
                $group_toggle_key = $group['key'];
            } else {
                $group_toggle_key = (string) $group_id;
            }

            // Add group toggle checkbox - format: acf[group-key]
            $acf_fields[] = array(
                'name' => 'acf[' . $group_toggle_key . ']',
                'label' => 'Enable Field Group: ' . $group_title,
                'type' => 'checkbox',
                'fieldType' => 'acf_group_toggle',
                'groupId' => (string) $group_id,
                'description' => 'Check this to enable importing fields from the "' . $group_title . '" field group',
            );

            // Get fields in this group
            $fields = acf_get_fields( $group_id );

            if ( ! empty( $fields ) ) {
                $this->add_acf_fields_recursive( $acf_fields, $fields, $group_id, $group_title, '' );
            }
        }

        if ( ! empty( $acf_fields ) ) {
            // Remove any existing ACF section (from HTML parsing) and replace with API data
            $schema['sections'] = array_filter( $schema['sections'], function( $section ) {
                return strpos( $section['name'] ?? '', 'Advanced Custom Fields' ) === false;
            } );
            $schema['sections'] = array_values( $schema['sections'] );

            $schema['sections'][] = array(
                'name' => 'Advanced Custom Fields Add-On',
                'fields' => $acf_fields,
            );

            WPAI_Bridge_Logger::debug( ' Added ' . count( $acf_fields ) . ' ACF fields from API' );
        }

        return $schema;
    }

    /**
     * Recursively add ACF fields to the schema, handling sub-fields properly
     *
     * @param array  $acf_fields Array to add fields to (passed by reference)
     * @param array  $fields     ACF fields to process
     * @param string $group_id   ACF group ID
     * @param string $group_title ACF group title
     * @param string $field_name_prefix Prefix for nested field names
     */
    private function add_acf_fields_recursive( &$acf_fields, $fields, $group_id, $group_title, $field_name_prefix ) {
        foreach ( $fields as $field ) {
            $field_key = $field['key'] ?? '';
            $field_name = $field['name'] ?? '';
            $field_label = $field['label'] ?? $field_name;
            $field_type = $field['type'] ?? 'text';

            // Build the full field name path
            $full_field_name = $field_name_prefix ? $field_name_prefix . '[' . $field_key . ']' : '[' . $field_key . ']';

            // For repeater/flexible_content/group fields, add the container marker plus options.
            // The marker carries `acfFieldType` so the descriptor normalizer can tell the three
            // container types apart (repeater = N rows, group = single object, flexible_content =
            // N layout blocks) instead of collapsing them.
            if ( in_array( $field_type, array( 'repeater', 'flexible_content', 'group' ), true ) ) {
                // Container anchor. For a repeater this is the "Repeater Mode" selector; for a
                // group/flex it is only the container signal (a group does not repeat).
                $acf_fields[] = array(
                    'name' => 'fields' . $full_field_name . '[is_variable]',
                    'label' => 'repeater' === $field_type
                        ? "[{$group_title}] {$field_label} - Repeater Mode"
                        : "[{$group_title}] {$field_label}",
                    'type' => 'radio',
                    'fieldType' => 'acf_repeater_option',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => 'radio',
                    'acfFieldType' => $field_type,
                    'description' => 'ACF repeater mode: "no" for Fixed, "yes" for Variable XML, "csv" for Variable CSV',
                    'validValues' => array( 'no', 'yes', 'csv' ),
                );

                // Separator/foreach are repeater CSV/XML variable-mode controls only; a group
                // nests a single object and flexible_content maps per-layout, so they don't apply.
                if ( 'repeater' === $field_type ) {
                    // Add separator option for CSV variable mode
                    $acf_fields[] = array(
                        'name' => 'fields' . $full_field_name . '[separator]',
                        'label' => "[{$group_title}] {$field_label} - Separator Character",
                        'type' => 'input',
                        'fieldType' => 'acf_repeater_option',
                        'fieldKey' => $field_key,
                        'groupId' => (string) $group_id,
                        'groupName' => $group_title,
                        'inputType' => 'text',
                        'description' => 'ACF repeater separator for CSV mode (default: |)',
                    );

                    // Add foreach option for XML variable mode
                    $acf_fields[] = array(
                        'name' => 'fields' . $full_field_name . '[foreach]',
                        'label' => "[{$group_title}] {$field_label} - XML Foreach Path",
                        'type' => 'input',
                        'fieldType' => 'acf_repeater_option',
                        'fieldKey' => $field_key,
                        'groupId' => (string) $group_id,
                        'groupName' => $group_title,
                        'inputType' => 'text',
                        'description' => 'ACF repeater XPath for XML variable mode (e.g., "items/item")',
                    );
                }

                // Sub-field path shape differs per container:
                //  - repeater: fields[KEY][rows][ROWNUMBER][SUB]  (N rows)
                //  - group:    fields[KEY][SUB]                   (direct nest, no rows)
                //  - flexible_content: per layout, a marker at
                //    fields[KEY][layouts][ROWNUMBER][acf_fc_layout] carrying the layout name
                //    (the value the add-on matches, FieldFlexibleContent) plus each layout
                //    sub-field at fields[KEY][layouts][ROWNUMBER][SUBKEY].
                if ( 'group' === $field_type ) {
                    if ( ! empty( $field['sub_fields'] ) ) {
                        $this->add_acf_fields_recursive(
                            $acf_fields,
                            $field['sub_fields'],
                            $group_id,
                            $group_title,
                            $full_field_name
                        );
                    }
                } elseif ( 'repeater' === $field_type ) {
                    if ( ! empty( $field['sub_fields'] ) ) {
                        $subfield_prefix = $full_field_name . '[rows][ROWNUMBER]';
                        $this->add_acf_fields_recursive(
                            $acf_fields,
                            $field['sub_fields'],
                            $group_id,
                            $group_title,
                            $subfield_prefix
                        );
                    }
                } elseif ( 'flexible_content' === $field_type && ! empty( $field['layouts'] ) ) {
                    foreach ( (array) $field['layouts'] as $layout ) {
                        if ( ! is_array( $layout ) ) {
                            continue;
                        }
                        $layout_name   = isset( $layout['name'] ) ? (string) $layout['name'] : '';
                        $layout_label  = isset( $layout['label'] ) && '' !== (string) $layout['label'] ? (string) $layout['label'] : $layout_name;
                        $layout_prefix = $full_field_name . '[layouts][ROWNUMBER]';

                        // Layout marker: teaches the normalizer the layout name/label. The
                        // acf_fc_layout value MUST equal the ACF layout name at apply time.
                        $acf_fields[] = array(
                            'name'        => 'fields' . $layout_prefix . '[acf_fc_layout]',
                            'label'       => "[{$group_title}] {$field_label} - {$layout_label}",
                            'type'        => 'hidden',
                            'fieldType'   => 'acf_flex_layout',
                            'fieldKey'    => $field_key,
                            'groupId'     => (string) $group_id,
                            'groupName'   => $group_title,
                            'inputType'   => 'hidden',
                            'layoutName'  => $layout_name,
                            'layoutLabel' => $layout_label,
                        );

                        // One marker per layout sub-field: fields[KEY][layouts][ROWNUMBER][SUBKEY].
                        // Emitted flat (not via add_acf_single_field) so each layout member is one
                        // descriptor keyed by its field key -- matching the read-API adapter shape.
                        foreach ( (array) ( $layout['sub_fields'] ?? array() ) as $sub ) {
                            if ( ! is_array( $sub ) ) {
                                continue;
                            }
                            $sub_key   = isset( $sub['key'] ) ? (string) $sub['key'] : '';
                            $sub_label = isset( $sub['label'] ) && '' !== (string) $sub['label'] ? (string) $sub['label'] : $sub_key;
                            $sub_type  = isset( $sub['type'] ) ? (string) $sub['type'] : 'text';
                            if ( '' === $sub_key ) {
                                continue;
                            }
                            $acf_fields[] = array(
                                'name'       => 'fields' . $layout_prefix . '[' . $sub_key . ']',
                                'label'      => "[{$group_title}] {$sub_label}",
                                'type'       => $sub_type,
                                'fieldType'  => 'acf_flex_field',
                                'fieldKey'   => $sub_key,
                                'parentKey'  => $field_key,
                                'groupId'    => (string) $group_id,
                                'groupName'  => $group_title,
                                'inputType'  => $sub_type,
                                'layoutName' => $layout_name,
                            );
                        }
                    }
                }
            } else {
                // Regular field - format depends on field type
                $this->add_acf_single_field( $acf_fields, $field, $group_id, $group_title, $field_name_prefix );
            }
        }
    }

    /**
     * Add a single ACF field to the schema with proper formatting
     */
    private function add_acf_single_field( &$acf_fields, $field, $group_id, $group_title, $field_name_prefix ) {
        $field_key = $field['key'] ?? '';
        $field_name = $field['name'] ?? '';
        $field_label = $field['label'] ?? $field_name;
        $field_type = $field['type'] ?? 'text';

        // Build the base field name - always prepend 'fields'
        $base_name = 'fields' . ( $field_name_prefix ? $field_name_prefix . '[' . $field_key . ']' : '[' . $field_key . ']' );

        // Different field types need different sub-property formats
        switch ( $field_type ) {
            case 'image':
            case 'file':
                // Image/file fields have [url] and options
                $acf_fields[] = array(
                    'name' => $base_name . '[url]',
                    'label' => "[{$group_title}] {$field_label}",
                    'type' => 'input',
                    'fieldType' => 'acf_field',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => $field_type,
                );
                $acf_fields[] = array(
                    'name' => $base_name . '[search_in_media]',
                    'label' => "[{$group_title}] {$field_label} - Search in Media Library",
                    'type' => 'checkbox',
                    'fieldType' => 'acf_field_option',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => 'checkbox',
                );
                $acf_fields[] = array(
                    'name' => $base_name . '[search_in_files]',
                    'label' => "[{$group_title}] {$field_label} - Search in Files",
                    'type' => 'checkbox',
                    'fieldType' => 'acf_field_option',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => 'checkbox',
                );
                break;

            case 'gallery':
                // Gallery fields have [gallery] and delimiter
                $acf_fields[] = array(
                    'name' => $base_name . '[gallery]',
                    'label' => "[{$group_title}] {$field_label}",
                    'type' => 'textarea',
                    'fieldType' => 'acf_field',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => $field_type,
                );
                $acf_fields[] = array(
                    'name' => $base_name . '[delim]',
                    'label' => "[{$group_title}] {$field_label} - Delimiter",
                    'type' => 'input',
                    'fieldType' => 'acf_field_option',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => 'text',
                );
                $acf_fields[] = array(
                    'name' => $base_name . '[search_in_media]',
                    'label' => "[{$group_title}] {$field_label} - Search in Media Library",
                    'type' => 'checkbox',
                    'fieldType' => 'acf_field_option',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => 'checkbox',
                );
                break;

            case 'select':
            case 'radio':
            case 'checkbox':
            case 'button_group':
            case 'true_false':
                // Select/radio/checkbox/button_group/true_false can have XPath mode
                $acf_fields[] = array(
                    'name' => $base_name,
                    'label' => "[{$group_title}] {$field_label}",
                    'type' => 'input',
                    'fieldType' => 'acf_field',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => $field_type,
                );
                // Add is_multiple_field_value option for XPath mode
                // IMPORTANT: For repeater subfields, this needs the full path including [rows][N]
                $is_multiple_name = 'is_multiple_field_value' . ( $field_name_prefix ? $field_name_prefix . '[' . $field_key . ']' : '[' . $field_key . ']' );
                $acf_fields[] = array(
                    'name' => $is_multiple_name,
                    'label' => "[{$group_title}] {$field_label} - Value Mode",
                    'type' => 'hidden',
                    'fieldType' => 'acf_field_option',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => 'hidden',
                    'description' => 'Set to "no" to use XPath value, or "yes" for fixed choice',
                );
                break;

            case 'relationship':
            case 'post_object':
            case 'page_link':
                // Relationship/Post Object/Page Link fields have [value] and [delim]
                $acf_fields[] = array(
                    'name' => $base_name . '[value]',
                    'label' => "[{$group_title}] {$field_label}",
                    'type' => 'input',
                    'fieldType' => 'acf_field',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => $field_type,
                );
                $acf_fields[] = array(
                    'name' => $base_name . '[delim]',
                    'label' => "[{$group_title}] {$field_label} - Delimiter",
                    'type' => 'input',
                    'fieldType' => 'acf_field_option',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => 'text',
                );
                break;

            case 'user':
                // User fields use simple text input (user ID, username, or email)
                // NO [value] suffix - just fields[field_key] directly
                $acf_fields[] = array(
                    'name' => $base_name,
                    'label' => "[{$group_title}] {$field_label}",
                    'type' => 'input',
                    'fieldType' => 'acf_field',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => 'text',
                    'description' => 'Specify user ID, username, or email. Separate multiple values with commas.',
                );
                break;

            case 'taxonomy':
                // Taxonomy fields have [value] and [delim], plus is_multiple_field_value mode
                // The mode selector must be set to "no" to enable XPath mode
                $is_multiple_name = 'is_multiple_field_value' . ( $field_name_prefix ? $field_name_prefix . '[' . $field_key . ']' : '[' . $field_key . ']' );
                $acf_fields[] = array(
                    'name' => $is_multiple_name,
                    'label' => "[{$group_title}] {$field_label} - Value Mode",
                    'type' => 'hidden',
                    'fieldType' => 'acf_field_option',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => 'hidden',
                    'description' => 'Set to "no" to enable XPath mode for taxonomy field',
                );
                $acf_fields[] = array(
                    'name' => $base_name . '[value]',
                    'label' => "[{$group_title}] {$field_label}",
                    'type' => 'input',
                    'fieldType' => 'acf_field',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => $field_type,
                );
                $acf_fields[] = array(
                    'name' => $base_name . '[delim]',
                    'label' => "[{$group_title}] {$field_label} - Hierarchy Delimiter",
                    'type' => 'input',
                    'fieldType' => 'acf_field_option',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => 'text',
                );
                break;

            case 'link':
                // Link fields have [title], [url], [target]
                $acf_fields[] = array(
                    'name' => $base_name . '[title]',
                    'label' => "[{$group_title}] {$field_label} - Title",
                    'type' => 'input',
                    'fieldType' => 'acf_field',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => 'text',
                );
                $acf_fields[] = array(
                    'name' => $base_name . '[url]',
                    'label' => "[{$group_title}] {$field_label} - URL",
                    'type' => 'input',
                    'fieldType' => 'acf_field',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => 'url',
                );
                $acf_fields[] = array(
                    'name' => $base_name . '[target]',
                    'label' => "[{$group_title}] {$field_label} - Target",
                    'type' => 'input',
                    'fieldType' => 'acf_field',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => 'text',
                );
                break;

            case 'google_map':
                // Google map fields have multiple sub-properties
                $acf_fields[] = array(
                    'name' => $base_name . '[address]',
                    'label' => "[{$group_title}] {$field_label} - Address",
                    'type' => 'input',
                    'fieldType' => 'acf_field',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => 'text',
                );
                $acf_fields[] = array(
                    'name' => $base_name . '[lat]',
                    'label' => "[{$group_title}] {$field_label} - Latitude",
                    'type' => 'input',
                    'fieldType' => 'acf_field',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => 'text',
                );
                $acf_fields[] = array(
                    'name' => $base_name . '[lng]',
                    'label' => "[{$group_title}] {$field_label} - Longitude",
                    'type' => 'input',
                    'fieldType' => 'acf_field',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => 'text',
                );
                break;

            default:
                // Simple fields just use the base name directly
                $acf_fields[] = array(
                    'name' => $base_name,
                    'label' => "[{$group_title}] {$field_label}",
                    'type' => 'input',
                    'fieldType' => 'acf_field',
                    'fieldKey' => $field_key,
                    'groupId' => (string) $group_id,
                    'groupName' => $group_title,
                    'inputType' => $field_type,
                );
                break;
        }
    }

    /**
     * Add Toolset fields directly from the Toolset API (not from HTML parsing)
     * This is needed because Toolset field groups are loaded via AJAX on Step 3
     */
    private function add_toolset_fields_from_api( $import_type, $options, $schema ) {
        // Check if Toolset Types and Toolset Add-On are active
        if ( ! class_exists( 'PMTI_Plugin' ) ) {
            return $schema;
        }

        // Check if Toolset Types functions exist
        if ( ! function_exists( 'wpcf_admin_fields_get_groups' ) && ! function_exists( 'types_get_groups' ) ) {
            return $schema;
        }

        // Get Toolset field groups
        $toolset_groups = array();
        if ( function_exists( 'wpcf_admin_fields_get_groups' ) ) {
            $toolset_groups = wpcf_admin_fields_get_groups();
        } elseif ( function_exists( 'types_get_groups' ) ) {
            $toolset_groups = types_get_groups();
        }

        if ( empty( $toolset_groups ) ) {
            return $schema;
        }

        // Filter groups that apply to this post type
        $applicable_groups = array();
        foreach ( $toolset_groups as $group ) {
            $post_types = $group['_wp_types_group_post_types'] ?? '';
            if ( empty( $post_types ) || $post_types === 'all' || strpos( $post_types, $import_type ) !== false ) {
                $applicable_groups[] = $group;
            }
        }

        if ( empty( $applicable_groups ) ) {
            return $schema;
        }

        WPAI_Bridge_Logger::debug( ' Getting Toolset fields from API for ' . count( $applicable_groups ) . ' groups' );

        $toolset_fields = array();

        foreach ( $applicable_groups as $group ) {
            $group_id = (string) ( $group['id'] ?? $group['ID'] ?? '' );
            $group_title = $group['name'] ?? 'Toolset Group';

            // Add group toggle checkbox
            $toolset_fields[] = array(
                'name' => 'wpcs_groups[]',
                'label' => 'Enable Field Group: ' . $group_title,
                'type' => 'checkbox',
                'inputType' => 'checkbox',
                'fieldType' => 'toolset_group_toggle',
                'groupId' => $group_id,
                'value' => $group_id,
                'description' => 'Check this to enable importing fields from the "' . $group_title . '" field group',
            );

            // Get fields in this group
            $fields = isset( $group['fields'] ) ? $group['fields'] : array();
            if ( empty( $fields ) && function_exists( 'wpcf_admin_fields_get_fields_by_group' ) ) {
                $fields = wpcf_admin_fields_get_fields_by_group( $group_id );
            }

            if ( ! empty( $fields ) ) {
                foreach ( $fields as $field_slug => $field ) {
                    $field_name = $field['meta_key'] ?? ( 'wpcf-' . $field_slug );
                    $field_label = $field['name'] ?? $field_slug;
                    $field_type = $field['type'] ?? 'text';

                    $toolset_fields[] = array(
                        'name' => $field_name,
                        'label' => "[{$group_title}] {$field_label}",
                        'type' => 'input',
                        'inputType' => $field_type,
                        'fieldType' => 'toolset_field',
                        'groupId' => $group_id,
                        'groupName' => $group_title,
                    );
                }
            }
        }

        if ( ! empty( $toolset_fields ) ) {
            // Remove any existing Toolset section (from HTML parsing) and replace with API data
            $schema['sections'] = array_filter( $schema['sections'], function( $section ) {
                return strpos( $section['name'] ?? '', 'Toolset' ) === false;
            } );
            $schema['sections'] = array_values( $schema['sections'] );

            $schema['sections'][] = array(
                'name' => 'Toolset Types Add-On',
                'fields' => $toolset_fields,
            );

            WPAI_Bridge_Logger::debug( ' Added ' . count( $toolset_fields ) . ' Toolset fields from API' );
        }

        return $schema;
    }

    /**
     * Render new API addon field groups (JetEngine, MetaBox, etc.)
     */
    private function render_new_api_addon_groups( $import, $options, $schema ) {
        // Check if the new addon API exists
        if ( ! class_exists( '\Wpai\AddonAPI\PMXI_Addon_Manager' ) ) {
            return $schema;
        }

        $import_type = $options['custom_type'] ?? 'post';
        $subtype = $options['taxonomy_type'] ?? null;

        // Get registered addons using static method
        $addons = \Wpai\AddonAPI\PMXI_Addon_Manager::get_addons();

        if ( empty( $addons ) ) {
            return $schema;
        }

        foreach ( $addons as $addon_id => $addon ) {
            // Check if addon is available for this import type
            if ( ! $addon->isAvailableForType( $import_type, $options ) ) {
                continue;
            }

            WPAI_Bridge_Logger::debug( ' Rendering new API addon: ' . $addon->name() );

            // Get fields and groups directly from the addon's static methods
            try {
                $groups = $addon->groups( $import_type, $subtype );
                $all_fields = $addon->fields( $import_type, $subtype );

                if ( empty( $groups ) && empty( $all_fields ) ) {
                    continue;
                }

                $addon_fields = array();
                // Use the addon's actual slug property for consistency with accordion.php
                // which uses $addon->slug for field names like jetengine_groups[]
                $addon_slug = $addon->slug;

                // First, add group toggle checkboxes (for enabling field groups)
                foreach ( $groups as $group ) {
                    // A group id is an identifier in the schema, but add-ons disagree
                    // on its PHP type: an ACF group id is a post id (int), Meta Box
                    // casts, JetEngine uses a slug. Normalise once here so consumers
                    // do not each have to cope with whichever add-on is active.
                    $group_id = isset( $group['id'] ) ? (string) $group['id'] : '';
                    $group_label = $group['label'] ?? $group_id;

                    if ( '' === $group_id ) {
                        continue;
                    }

                    // Add group toggle checkbox - this is how the UI enables/disables groups
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

                // Now add the actual fields, organized by group
                foreach ( $all_fields as $field ) {
                    $field_key = $field['key'] ?? $field['name'] ?? '';
                    $field_label = $field['label'] ?? $field['title'] ?? $field_key;
                    $field_type = $field['type'] ?? 'text';
                    $field_group = isset( $field['group'] ) ? (string) $field['group'] : '';

                    // Find the group label for this field. Both sides are normalised
                    // before comparing: a strict compare of an int id against a
                    // numeric-string group silently missed, leaving the raw id shown
                    // as the label.
                    $group_label = $field_group;
                    foreach ( $groups as $group ) {
                        if ( isset( $group['id'] ) && (string) $group['id'] === $field_group ) {
                            $group_label = $group['label'] ?? $field_group;
                            break;
                        }
                    }

                    // Field name format: addon_slug[field_key] - e.g. jetengine[test_text_field]
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

                if ( ! empty( $addon_fields ) ) {
                    $addon_name = $addon->name();

                    // Remove any existing section for this addon (from HTML parsing) before adding API data
                    $schema['sections'] = array_filter( $schema['sections'], function( $section ) use ( $addon_name ) {
                        return ( $section['name'] ?? '' ) !== $addon_name;
                    } );
                    $schema['sections'] = array_values( $schema['sections'] );

                    // Use addon name directly - it already includes "Add-On" suffix
                    $schema['sections'][] = array(
                        'name' => $addon_name,
                        'fields' => $addon_fields,
                    );

                    WPAI_Bridge_Logger::debug( ' Added ' . count( $addon_fields ) . ' fields from ' . $addon_name );
                }
            } catch ( \Exception $e ) {
                WPAI_Bridge_Logger::debug( ' Error getting addon fields for ' . $addon->name() . ': ' . $e->getMessage() );
            }
        }

        return $schema;
    }

    /**
     * Parse new API addon HTML to extract fields
     */
    private function parse_new_api_addon_html( $html, $addon_name ) {
        $fields = array();

        if ( empty( $html ) ) {
            return $fields;
        }

        $dom = new DOMDocument();
        @$dom->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
        $xpath = new DOMXPath( $dom );

        // Find all inputs
        $inputs = $xpath->query( "//input | //textarea | //select" );

        $seen = array();

        foreach ( $inputs as $input ) {
            $name = $input->getAttribute( 'name' );
            $type = $input->getAttribute( 'type' );

            if ( empty( $name ) || isset( $seen[ $name ] ) || in_array( $type, array( 'submit', 'button' ) ) ) {
                continue;
            }

            $seen[ $name ] = true;

            // Get label
            $label = $this->find_label_for_input( $input, $xpath );
            $full_label = "[$addon_name] " . ( $label ?: $name );

            $fields[] = array(
                'name' => $name,
                'label' => $full_label,
                'type' => $input->nodeName,
                'fieldType' => 'addon_api',
                'addonName' => $addon_name,
                'inputType' => $type ?: 'text',
            );
        }

        return $fields;
    }


    /**
     * Add core WordPress fields (title, content, etc.)
     */
    private function add_core_fields( $import_type, $options, $schema ) {
        $core_fields = array();

        // Standard post fields
        if ( in_array( $import_type, array( 'post', 'page' ) ) || post_type_exists( $import_type ) ) {
            $core_fields = array(
                array(
                    'name' => 'title',
                    'label' => 'Title',
                    'type' => 'input',
                    'fieldType' => 'core',
                    'inputType' => 'text',
                ),
                array(
                    'name' => 'content',
                    'label' => 'Content',
                    'type' => 'textarea',
                    'fieldType' => 'core',
                    'inputType' => 'text',
                ),
                array(
                    'name' => 'excerpt',
                    'label' => 'Excerpt',
                    'type' => 'textarea',
                    'fieldType' => 'core',
                    'inputType' => 'text',
                ),
                array(
                    'name' => 'post_status',
                    'label' => 'Post Status',
                    'type' => 'select',
                    'fieldType' => 'core',
                    'inputType' => 'select',
                    'options' => array( 'publish', 'draft', 'pending', 'private' ),
                ),
                array(
                    'name' => 'post_date',
                    'label' => 'Post Date',
                    'type' => 'input',
                    'fieldType' => 'core',
                    'inputType' => 'text',
                ),
                array(
                    'name' => 'post_author',
                    'label' => 'Post Author',
                    'type' => 'input',
                    'fieldType' => 'core',
                    'inputType' => 'text',
                ),
                array(
                    'name' => 'post_slug',
                    'label' => 'Post Slug',
                    'type' => 'input',
                    'fieldType' => 'core',
                    'inputType' => 'text',
                ),
            );

            // Add featured image if supported
            if ( post_type_supports( $import_type, 'thumbnail' ) ) {
                $core_fields[] = array(
                    'name' => 'featured_image',
                    'label' => 'Featured Image',
                    'type' => 'input',
                    'fieldType' => 'core',
                    'inputType' => 'text',
                    'description' => 'URL to the featured image',
                );
            }
        }

        // Taxonomy import type
        if ( $import_type === 'taxonomies' ) {
            $core_fields = array(
                array(
                    'name' => 'term_name',
                    'label' => 'Term Name',
                    'type' => 'input',
                    'fieldType' => 'core',
                    'inputType' => 'text',
                ),
                array(
                    'name' => 'term_slug',
                    'label' => 'Term Slug',
                    'type' => 'input',
                    'fieldType' => 'core',
                    'inputType' => 'text',
                ),
                array(
                    'name' => 'term_description',
                    'label' => 'Term Description',
                    'type' => 'textarea',
                    'fieldType' => 'core',
                    'inputType' => 'text',
                ),
                array(
                    'name' => 'term_parent',
                    'label' => 'Parent Term',
                    'type' => 'input',
                    'fieldType' => 'core',
                    'inputType' => 'text',
                ),
            );
        }

        // User import type
        if ( $import_type === 'import_users' ) {
            $core_fields = array(
                array(
                    'name' => 'user_login',
                    'label' => 'Username',
                    'type' => 'input',
                    'fieldType' => 'core',
                    'inputType' => 'text',
                ),
                array(
                    'name' => 'user_email',
                    'label' => 'Email',
                    'type' => 'input',
                    'fieldType' => 'core',
                    'inputType' => 'email',
                ),
                array(
                    'name' => 'first_name',
                    'label' => 'First Name',
                    'type' => 'input',
                    'fieldType' => 'core',
                    'inputType' => 'text',
                ),
                array(
                    'name' => 'last_name',
                    'label' => 'Last Name',
                    'type' => 'input',
                    'fieldType' => 'core',
                    'inputType' => 'text',
                ),
                array(
                    'name' => 'user_pass',
                    'label' => 'Password',
                    'type' => 'input',
                    'fieldType' => 'core',
                    'inputType' => 'password',
                ),
                array(
                    'name' => 'role',
                    'label' => 'Role',
                    'type' => 'select',
                    'fieldType' => 'core',
                    'inputType' => 'select',
                ),
            );
        }

        if ( ! empty( $core_fields ) ) {
            // Add core fields as the first section
            array_unshift( $schema['sections'], array(
                'name' => 'Core Fields',
                'fields' => $core_fields,
            ) );
        }

        return $schema;
    }

    /**
     * Add taxonomies for the import type
     */
    private function add_taxonomies( $import_type, $schema ) {
        // Skip for non-post types
        if ( $import_type === 'taxonomies' || $import_type === 'import_users' ) {
            return $schema;
        }

        // Get taxonomies for this post type
        $taxonomies = get_object_taxonomies( $import_type, 'objects' );

        foreach ( $taxonomies as $taxonomy ) {
            // Skip internal taxonomies
            if ( ! $taxonomy->public && ! $taxonomy->publicly_queryable ) {
                continue;
            }

            $schema['taxonomies'][] = array(
                'name' => $taxonomy->name,
                'label' => $taxonomy->label,
                'hierarchical' => $taxonomy->hierarchical,
                'description' => $taxonomy->description,
            );
        }

        return $schema;
    }

    /**
     * Get the import file URL
     */
    private function get_import_file_url( $import ) {
        $file_path = $import->path;

        // Check if it's a URL
        if ( filter_var( $file_path, FILTER_VALIDATE_URL ) ) {
            return $file_path;
        }

        // Check if it's a local file
        if ( file_exists( $file_path ) ) {
            // Try to convert to URL
            $upload_dir = wp_upload_dir();
            if ( strpos( $file_path, $upload_dir['basedir'] ) === 0 ) {
                return str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $file_path );
            }
        }

        // Return the path as-is
        return $file_path;
    }
}
