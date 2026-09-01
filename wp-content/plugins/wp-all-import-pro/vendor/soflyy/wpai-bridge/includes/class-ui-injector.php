<?php
/**
 * UI Injector for LLM Auto-Configuration
 * 
 * Injects LLM-related UI elements into WP All Import pages using hooks
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPAI_Bridge_UI_Injector {

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
        // Global: Enqueue logger script on WP All Import pages
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_logger_script' ), 5 );

        // Step 1: Inject Automatic Setup card and modal
        add_action( 'pmxi_import_options_before', array( $this, 'inject_step1_button' ) );
        add_action( 'admin_footer', array( $this, 'inject_step1_modal' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_step1_scripts' ) );

        // Step 3: Inject LLM configuration UI
        add_action( 'pmxi_template_before_content', array( $this, 'inject_step3_ui' ), 10, 1 );
        add_filter( 'pmxi_template_form_class', array( $this, 'add_llm_form_class' ), 10, 2 );
        add_filter( 'pmxi_template_title', array( $this, 'filter_template_title' ), 10, 2 );
        add_filter( 'pmxi_template_content_wrapper_style', array( $this, 'filter_content_wrapper_style' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_step3_scripts' ) );

        // Re-entry points into Automatic Setup for existing imports
        add_action( 'pmxi_template_before_content', array( $this, 'inject_reentry_band' ), 20, 1 );

        // LLM Auto-Run: Auto-submit update confirmation form
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_auto_run_script' ) );

        // Display session error messages
        add_action( 'admin_notices', array( $this, 'display_session_error' ) );
    }

    /**
     * Enqueue the logger script and global config on WP All Import pages
     * This runs early (priority 5) so other scripts can depend on it
     */
    public function enqueue_logger_script() {
        // Only on WP All Import admin pages
        if ( ! isset( $_GET['page'] ) || strpos( $_GET['page'], 'pmxi' ) !== 0 ) {
            return;
        }

        wp_enqueue_script(
            'wpai-bridge-logger',
            WPAI_BRIDGE_URL . 'assets/js/logger.js',
            array(),
            WPAI_BRIDGE_VERSION,
            false // Load in header so it's available to other scripts
        );

        wp_localize_script(
            'wpai-bridge-logger',
            'wpai_bridge_config',
            array(
                'debug' => WPAI_Bridge_Logger::is_enabled(),
            )
        );
    }

    /**
     * Check if we're on Step 1 (New Import page)
     */
    private function is_step1_page() {
        if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'pmxi-admin-import' ) {
            return false;
        }
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'index';
        return $action === 'index';
    }

    /**
     * Check if we're on Step 3 (Template page) in LLM mode
     */
    private function is_step3_llm_page() {
        if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'pmxi-admin-import' ) {
            return false;
        }
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'index';
        if ( $action !== 'template' ) {
            return false;
        }
        return $this->is_llm_mode() || $this->is_llm_success();
    }

    /**
     * Check if we're on a screen where the re-entry band can render.
     *
     * `pmxi_template_before_content` fires for two screens outside LLM mode:
     * `pmxi-admin-import&action=template` and `pmxi-admin-manage&action=edit`
     * (WP All Import's own "Edit Template" link delegates to the same
     * controller). Both show the band, so both need the modal.
     */
    private function is_reentry_band_page() {
        if ( ! isset( $_GET['page'] ) ) {
            return false;
        }

        if ( $this->is_llm_mode() || $this->is_llm_success() ) {
            return false;
        }

        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'index';

        if ( $_GET['page'] === 'pmxi-admin-import' ) {
            return $action === 'template';
        }

        if ( $_GET['page'] === 'pmxi-admin-manage' ) {
            return $action === 'edit';
        }

        return false;
    }

    /**
     * Inject Automatic Setup modal on Step 1, Step 3 (for re-analyze) and the
     * re-entry band screens. The modal is hidden by default and opened via
     * JavaScript.
     */
    public function inject_step1_modal() {
        if ( ! $this->is_step1_page() && ! $this->is_step3_llm_page() && ! $this->is_reentry_band_page() ) {
            return;
        }

        // Include the modal view
        include WPAI_BRIDGE_DIR . 'views/step1-ai-import.php';

        // Enqueue the Step 1 handler script
        wp_enqueue_script(
            'wpai-bridge-step1-handler',
            WPAI_BRIDGE_URL . 'assets/js/step1-handler.js',
            array( 'jquery', 'wpai-bridge-logger' ),
            WPAI_BRIDGE_VERSION,
            true
        );
    }

    /**
     * Inject Automatic Setup card on Step 1
     */
    public function inject_step1_button() {
        include WPAI_BRIDGE_DIR . 'views/step1-button.php';
    }
    
    /**
     * Enqueue scripts for Step 1 and for the re-entry band
     */
    public function enqueue_step1_scripts( $hook ) {
        // Only load on WP All Import pages
        if ( ! $this->is_wpai_page() ) {
            return;
        }

        // The band's own handler. The Step 1 health check below is deliberately
        // not loaded here — it drives the Step 1 card and retries on a timer,
        // which the band has no use for. The band asks the same endpoint itself,
        // once on load and again on click, because it has something to lose.
        if ( $this->is_reentry_band_page() ) {
            $this->enqueue_reentry_band_script();
            return;
        }

        // Everything below is Step 1 (index action) only.
        if ( ! $this->is_step1_page() ) {
            return;
        }

        wp_enqueue_script(
            'wpai-bridge-step1-health-check',
            WPAI_BRIDGE_URL . 'assets/js/step1-health-check.js',
            array( 'jquery', 'wpai-bridge-logger' ),
            WPAI_BRIDGE_VERSION,
            true
        );
        
        wp_localize_script(
            'wpai-bridge-step1-health-check',
            'wpai_bridge_step1',
            array(
                'llm_service_url' => wpai_bridge_get_llm_service_url(),
                'wp_api_url' => rest_url( 'wp-all-import/v1' ),
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'consent_nonce' => wp_create_nonce( 'wpai_bridge_consent' ),
                'user_has_consented' => wpai_bridge_user_has_consented(),
                'table_exists' => WPAI_Bridge_File_Structure_Cache::table_exists(),
                'checking_text' => __( 'Checking availability…', 'wpai-ai-bridge-plugin' ),
                'ready_text' => __( 'Set up your import automatically', 'wpai-ai-bridge-plugin' ),
                'unavailable_text' => __( 'Automatic Setup is currently unavailable', 'wpai-ai-bridge-plugin' ),
                'unavailable_alert' => __( 'Automatic Setup is currently unavailable. Please try again later, or configure your import manually.', 'wpai-ai-bridge-plugin' ),
                'table_missing_text' => __( 'Automatic Setup requires a database table that could not be created. Check permissions and reactivate the plugin.', 'wpai-ai-bridge-plugin' ),
                // Kept apart from the unavailable wording: a pause is not a fault,
                // and the settings screen describes it as a pause too.
                'paused_text' => __( 'Automatic Setup is paused', 'wpai-ai-bridge-plugin' ),
                'paused_alert' => __( 'Automatic Setup is paused and will return shortly. In the meantime you can configure your import manually.', 'wpai-ai-bridge-plugin' ),
                'no_file_alert' => __( 'Please upload or select a file first.', 'wpai-ai-bridge-plugin' ),
                'consent_error' => __( 'Failed to record consent. Please try again.', 'wpai-ai-bridge-plugin' ),
            )
        );
    }
    
    /**
     * Check if we're in LLM mode
     */
    private function is_llm_mode() {
        return ! empty( $_GET['llm_mode'] );
    }

    /**
     * Check if LLM configuration was successful
     */
    private function is_llm_success() {
        return ! empty( $_GET['llm_success'] );
    }

    /**
     * Inject LLM configuration UI on Step 3
     */
    public function inject_step3_ui( $controller ) {
        $llm_mode = $this->is_llm_mode();
        $llm_success = $this->is_llm_success();

        if ( ! $llm_mode && ! $llm_success ) {
            return;
        }

        // Get import ID from multiple sources
        $import_id = 0;

        // First try: update_previous record
        if ( ! empty( $controller->data['update_previous'] ) && ! $controller->data['update_previous']->isEmpty() ) {
            $import_id = $controller->data['update_previous']->id;
        }
        // Second try: session
        elseif ( ! empty( PMXI_Plugin::$session->update_previous ) ) {
            $import_id = PMXI_Plugin::$session->update_previous;
        }
        elseif ( ! empty( PMXI_Plugin::$session->import_id ) ) {
            $import_id = PMXI_Plugin::$session->import_id;
        }
        // Last resort: a stray `id`-bearing URL reads an unrelated session key, so
        // trust the request parameter rather than rendering against nothing.
        elseif ( ! empty( $_GET['id'] ) ) {
            $import_id = absint( $_GET['id'] );
        }

        // If we're in LLM mode but couldn't find a valid import ID, the session was lost
        // Redirect to manage imports page to prevent white screen
        if ( $llm_mode && empty( $import_id ) ) {
            $this->redirect_to_manage_imports( __( 'Your session has expired. Please start a new import or edit an existing one.', 'wpai-ai-bridge-plugin' ) );
            return;
        }

        // Pass data to the view
        $data = array(
            'llm_mode' => $llm_mode,
            'llm_success' => $llm_success,
            'llm_service_url' => wpai_bridge_get_llm_service_url(),
            'import_id' => $import_id,
            'user_instructions' => $this->get_stored_user_instructions( $import_id ),
        );

        include WPAI_BRIDGE_DIR . 'views/step3-llm-ui.php';
    }

    /**
     * Stored setup instructions for an import, so the panel can show and clear them
     */
    private function get_stored_user_instructions( $import_id ) {
        if ( empty( $import_id ) ) {
            return '';
        }

        $import = new PMXI_Import_Record();
        $import->getById( $import_id );

        if ( $import->isEmpty() || ! is_array( $import->options ) ) {
            return '';
        }

        return (string) ( $import->options['llm_user_instructions'] ?? '' );
    }

    /**
     * Filter the template page title
     */
    public function filter_template_title( $title, $controller ) {
        if ( $this->is_llm_mode() || $this->is_llm_success() ) {
            return __( 'Automatic Setup', 'wpai-ai-bridge-plugin' );
        }
        return $title;
    }

    /**
     * Filter the content wrapper style to hide the manual template only while
     * the configuration iframe is on screen (llm_mode without llm_success)
     */
    public function filter_content_wrapper_style( $style, $controller ) {
        if ( $this->is_llm_mode() && ! $this->is_llm_success() ) {
            return 'display: none;';
        }
        return $style;
    }
    
    /**
     * Add wpai-llm-mode class to template form
     */
    public function add_llm_form_class( $classes, $controller ) {
        $llm_mode = ! empty( $_GET['llm_mode'] );
        
        if ( $llm_mode ) {
            $classes .= ' wpai-llm-mode';
        }
        
        return $classes;
    }
    
    /**
     * Confirmation text shared by both re-analyze entry points, so the
     * destructive warning has a single translation.
     */
    private function reanalyze_confirm_message() {
        return __( "Setting up again will replace this import's current field mapping. Continue?", 'wpai-ai-bridge-plugin' );
    }

    /**
     * Entry point into Automatic Setup from an import that was configured
     * manually or has already run. Only renders outside LLM mode, so it never
     * competes with the configuration UI. When wizard session state is
     * absent (e.g. a direct link to the template page), falls back to the
     * `id` request parameter, verifying the import actually exists first.
     */
    public function inject_reentry_band( $controller ) {
        if ( $this->is_llm_mode() || $this->is_llm_success() ) {
            return;
        }

        $import_id = 0;
        if ( ! empty( $controller->data['update_previous'] ) && ! $controller->data['update_previous']->isEmpty() ) {
            $import_id = $controller->data['update_previous']->id;
        } elseif ( ! empty( PMXI_Plugin::$session->update_previous ) ) {
            $import_id = PMXI_Plugin::$session->update_previous;
        } elseif ( ! empty( PMXI_Plugin::$session->import_id ) ) {
            $import_id = PMXI_Plugin::$session->import_id;
        } elseif ( ! empty( $_GET['id'] ) ) {
            $import_id = absint( $_GET['id'] );

            $record = new PMXI_Import_Record();
            $record->getById( $import_id );
            if ( $record->isEmpty() ) {
                return;
            }
        }

        if ( empty( $import_id ) ) {
            return;
        }

        ?>
        <div class="wpai-llm-config-container wpai-reentry-band">
            <div class="wpai-success-header">
                <div class="wpai-success-content">
                    <h3><?php esc_html_e( 'Set this import up automatically', 'wpai-ai-bridge-plugin' ); ?></h3>
                    <h4 class="wpai-success-subtitle"><?php esc_html_e( 'Map this import&#39;s fields automatically. This replaces the field mapping you have now', 'wpai-ai-bridge-plugin' ); ?></h4>
                </div>
                <div class="wpai-success-actions">
                    <button type="button" id="wpai-start-automatic-setup" class="button wpallimport-large-button wpallimport-continue-button" data-import-id="<?php echo esc_attr( $import_id ); ?>">
                        <?php esc_html_e( 'Set Up Automatically', 'wpai-ai-bridge-plugin' ); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue the re-entry band's click handler. The band opens the same modal
     * as Step 1, so it only needs the reset nonce and its own strings; the
     * modal's own config comes from views/step1-ai-import.php.
     */
    private function enqueue_reentry_band_script() {
        wp_enqueue_script(
            'wpai-bridge-reentry-band',
            WPAI_BRIDGE_URL . 'assets/js/reentry-band.js',
            array( 'jquery', 'wpai-bridge-logger' ),
            WPAI_BRIDGE_VERSION,
            true
        );

        wp_localize_script(
            'wpai-bridge-reentry-band',
            'wpai_bridge_reentry',
            array(
                'ajax_url'    => admin_url( 'admin-ajax.php' ),
                'reset_nonce' => wp_create_nonce( 'wp_all_import_secure' ),
                // The band asks the service whether it can finish before it
                // discards a working configuration, so it needs the same two
                // endpoints Step 1 uses.
                'llm_service_url' => wpai_bridge_get_llm_service_url(),
                'wp_api_url'      => rest_url( 'wp-all-import/v1' ),
                'i18n'        => array(
                    'confirm'      => $this->reanalyze_confirm_message(),
                    'checking'     => __( 'Checking availability...', 'wpai-ai-bridge-plugin' ),
                    'resetting'    => __( 'Resetting...', 'wpai-ai-bridge-plugin' ),
                    'reset_failed' => __( 'Could not restart setup. Please try again.', 'wpai-ai-bridge-plugin' ),
                    'paused'       => __( 'Automatic Setup is temporarily unavailable, so your current configuration has been left untouched. Please try again shortly.', 'wpai-ai-bridge-plugin' ),
                    'unavailable'  => __( 'Automatic Setup is not available, so your current configuration has been left untouched. Please refresh the page and try again.', 'wpai-ai-bridge-plugin' ),
                ),
            )
        );
    }

    /**
     * Enqueue styles/scripts for Step 3 and the Automatic Setup re-entry band.
     *
     * Two independent gates live here, deliberately not coupled:
     *
     * - The STYLESHEET gate is broad (any WP All Import admin screen). The
     *   re-entry band renders via `pmxi_template_before_content` on whatever
     *   page fires that hook for an existing import — which in practice is
     *   `page=pmxi-admin-manage&action=edit` (WP All Import's own "Edit
     *   Template" link), not just `page=pmxi-admin-import&action=template`.
     *   Every selector in llm-config.css is prefixed `wpai-` or scoped under
     *   `.wpallimport-plugin`, so loading it broadly cannot bleed into core
     *   WP or other plugins' UI.
     * - The SCRIPT gate stays narrow: the step-3 JS assumes LLM mode (it
     *   drives the iframe handshake) and must only run on
     *   `page=pmxi-admin-import&action=template` with `llm_mode` or
     *   `llm_success` set. Do not widen this to match the stylesheet gate.
     */
    public function enqueue_step3_scripts( $hook ) {
        // Only load on WP All Import pages
        if ( ! $this->is_wpai_page() ) {
            return;
        }

        wp_enqueue_style(
            'wpai-bridge-step3-llm-config',
            WPAI_BRIDGE_URL . 'assets/css/llm-config.css',
            array( 'pmxi-admin-style' ),
            WPAI_BRIDGE_VERSION
        );

        // Everything below is the script gate: Step 3 (template action) only.
        if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'pmxi-admin-import' ) {
            return;
        }

        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'index';
        if ( $action !== 'template' ) {
            return;
        }

        // The script assumes LLM mode, so keep it gated.
        $llm_mode = ! empty( $_GET['llm_mode'] );
        $llm_success = ! empty( $_GET['llm_success'] );

        if ( ! $llm_mode && ! $llm_success ) {
            return;
        }

        wp_enqueue_script(
            'wpai-bridge-step3-llm-config',
            WPAI_BRIDGE_URL . 'assets/js/step3-llm-config.js',
            array( 'jquery', 'wpai-bridge-logger' ),
            WPAI_BRIDGE_VERSION,
            true
        );
        
        // Get session data
        $import_id = 0;
        $file_path = '';
        $source = array();
        $custom_type = '';
        $is_csv = false;
        
        if ( class_exists( 'PMXI_Plugin' ) && isset( PMXI_Plugin::$session ) ) {
            if ( ! empty( PMXI_Plugin::$session->import_id ) ) {
                $import_id = PMXI_Plugin::$session->import_id;
            } elseif ( ! empty( PMXI_Plugin::$session->update_previous ) ) {
                $import_id = PMXI_Plugin::$session->update_previous;
            }
            
            $file_path = isset( PMXI_Plugin::$session->filePath ) ? PMXI_Plugin::$session->filePath : '';
            $source = isset( PMXI_Plugin::$session->source ) ? PMXI_Plugin::$session->source : array();
            $custom_type = isset( PMXI_Plugin::$session->custom_type ) ? PMXI_Plugin::$session->custom_type : '';
            $is_csv = isset( PMXI_Plugin::$session->is_csv ) ? PMXI_Plugin::$session->is_csv : false;
        }
        
        wp_localize_script(
            'wpai-bridge-step3-llm-config',
            'wpai_bridge_step3',
            array(
                'llm_service_url' => wpai_bridge_get_llm_service_url(),
                'wp_api_url' => rest_url( 'wp-all-import/v1' ),
                'bridge_auth_token' => WPAI_Bridge_FL_Signer::mint_session_token( rest_url( 'wp-all-import/v1' ) ),
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'admin_url' => admin_url( 'admin.php' ),
                'nonce' => wp_create_nonce( 'wp_all_import_secure' ),
                'rest_nonce' => wp_create_nonce( 'wp_rest' ),
                'import_id' => $import_id,
                'file_path' => $file_path,
                'source' => $source,
                'custom_type' => $custom_type,
                'is_csv' => $is_csv,
                'llm_mode' => $llm_mode ? '1' : '0',
                'llm_success' => $llm_success ? '1' : '0',
                'manage_imports_url' => admin_url( 'admin.php?page=pmxi-admin-manage' ),
                'user_has_consented' => wpai_bridge_user_has_consented(),
                // Translatable strings
                'i18n' => array(
                    'loading_acf_fields' => __( 'Loading ACF field groups...', 'wpai-ai-bridge-plugin' ),
                    'loading_addon_fields' => __( 'Loading add-on field groups...', 'wpai-ai-bridge-plugin' ),
                    'saving_template' => __( 'Saving template defaults to database...', 'wpai-ai-bridge-plugin' ),
                    'initializing_session' => __( 'Initializing secure session...', 'wpai-ai-bridge-plugin' ),
                    'import_id_not_found' => __( 'Import ID not found. Please go back and try again.', 'wpai-ai-bridge-plugin' ),
                    'session_failed' => __( 'Failed to initialize session', 'wpai-ai-bridge-plugin' ),
                    'analyzing_fields' => __( 'Analyzing available template fields...', 'wpai-ai-bridge-plugin' ),
                    'loading_interface' => __( 'Loading Automatic Setup…', 'wpai-ai-bridge-plugin' ),
                    'unexpected_error' => __( 'Something went wrong during setup.', 'wpai-ai-bridge-plugin' ),
                    'config_failed' => __( 'Setup failed', 'wpai-ai-bridge-plugin' ),
                    'provide_instructions' => __( 'Please add some instructions first.', 'wpai-ai-bridge-plugin' ),
                    'wait_for_completion_preview' => __( 'Please wait for setup to finish before previewing.', 'wpai-ai-bridge-plugin' ),
                    'wait_for_completion_run' => __( 'Please wait for setup to finish before running the import.', 'wpai-ai-bridge-plugin' ),
                    'resetting' => __( 'Resetting...', 'wpai-ai-bridge-plugin' ),
                    'reset_failed' => __( 'Failed to reset template. Please try again.', 'wpai-ai-bridge-plugin' ),
                    'checking'    => __( 'Checking availability...', 'wpai-ai-bridge-plugin' ),
                    'paused'      => __( 'Automatic Setup is temporarily unavailable, so your current configuration has been left untouched. Please try again shortly.', 'wpai-ai-bridge-plugin' ),
                    'unavailable' => __( 'Automatic Setup is not available, so your current configuration has been left untouched. Please refresh the page and try again.', 'wpai-ai-bridge-plugin' ),
                    'reanalyze_confirm' => $this->reanalyze_confirm_message(),
                ),
            )
        );
    }

    /**
     * Enqueue auto-run script for LLM imports
     * This script auto-submits the update confirmation form when llm_auto_run=1 parameter is present
     */
    public function enqueue_auto_run_script() {
        // Only enqueue on WP All Import manage pages with action=update
        if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'pmxi-admin-manage' ) {
            return;
        }

        $action = isset( $_GET['action'] ) ? $_GET['action'] : '';
        if ( $action !== 'update' ) {
            return;
        }

        wp_enqueue_script(
            'wpai-bridge-llm-auto-run',
            WPAI_BRIDGE_URL . 'assets/js/llm-auto-run.js',
            array( 'wpai-bridge-logger' ),
            WPAI_BRIDGE_VERSION,
            true
        );
    }

    /**
     * Display session error message from transient
     */
    public function display_session_error() {
        // Only show on WP All Import pages
        if ( ! $this->is_wpai_page() ) {
            return;
        }

        $user_id = get_current_user_id();
        $transient_key = 'wpai_bridge_session_error_' . $user_id;
        $message = get_transient( $transient_key );

        if ( ! empty( $message ) ) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
            // Delete transient after displaying
            delete_transient( $transient_key );
        }
    }

    /**
     * Redirect to manage imports page with an error message
     *
     * @param string $message Error message to display
     */
    private function redirect_to_manage_imports( $message = '' ) {
        // Store message in transient so it persists across redirect
        if ( ! empty( $message ) ) {
            set_transient( 'wpai_bridge_session_error_' . get_current_user_id(), $message, 30 );
        }

        // Redirect to manage imports page
        $redirect_url = admin_url( 'admin.php?page=pmxi-admin-manage' );
        wp_safe_redirect( $redirect_url );
        exit;
    }

    private function is_wpai_page() {
        if ( ! is_admin() ) {
            return false;
        }

        $screen = get_current_screen();
        if ( ! $screen ) {
            return false;
        }
        
        return strpos( $screen->id, 'pmxi-admin' ) !== false;
    }
}
