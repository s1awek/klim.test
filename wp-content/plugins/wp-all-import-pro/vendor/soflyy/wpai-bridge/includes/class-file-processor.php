<?php
/**
 * File Processor for AI Bridge Step 1
 *
 * Handles file uploads, URL downloads, and import creation for the Automatic
 * Setup import flow via REST API. Uses session tokens for secure cross-origin requests.
 *
 * Session model (mirrors class-llm-config-api.php):
 * - Session tokens are created by authenticated WordPress users (via step1/init endpoint)
 * - Tokens are stored in options table keyed by user_id (since no import_id exists yet)
 * - Tokens expire after 1 hour
 * - For cross-origin requests (from Vercel iframe), we validate the token exists and hasn't expired
 * - The token itself acts as proof of authorization since only the authenticated user
 *   who initiated Automatic Setup could have obtained the token
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPAI_Bridge_File_Processor {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Top root element candidates from the most recent detect_root_element() call.
     * Each entry: array( 'name' => string, 'depth' => int, 'count' => int,
     *                     'child_count' => int, 'child_names' => string[] )
     */
    private $last_root_candidates = array();

    /**
     * REST API namespace (same as class-llm-config-api.php)
     */
    const API_NAMESPACE = 'wp-all-import/v1';

    /**
     * Step 1 session option prefix (keyed by user_id since no import_id exists yet)
     */
    const STEP1_SESSION_PREFIX = 'wpai_step1_session_';

    /**
     * Session expiration time (1 hour, same as class-llm-config-api.php)
     */
    const SESSION_EXPIRATION = 3600;

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
    }

    /**
     * Register REST API routes for Step 1
     */
    public function register_routes() {
        // Initialize Step 1 session (creates token) - requires WordPress authentication
        register_rest_route( self::API_NAMESPACE, '/step1/init', array(
            'methods' => 'POST',
            'callback' => array( $this, 'init_session' ),
            'permission_callback' => array( $this, 'check_admin_permissions' ),
        ) );

        // Validate Step 1 session (for iframe to check if session is still valid)
        register_rest_route( self::API_NAMESPACE, '/step1/validate', array(
            'methods' => 'POST',
            'callback' => array( $this, 'validate_session' ),
            'permission_callback' => '__return_true', // Token validation is done in the callback
        ) );

        // Upload file - requires Step 1 session token
        register_rest_route( self::API_NAMESPACE, '/step1/upload', array(
            'methods' => 'POST',
            'callback' => array( $this, 'handle_file_upload' ),
            'permission_callback' => array( $this, 'check_session_token' ),
        ) );

        // Download from URL - requires Step 1 session token
        register_rest_route( self::API_NAMESPACE, '/step1/download-url', array(
            'methods' => 'POST',
            'callback' => array( $this, 'handle_url_download' ),
            'permission_callback' => array( $this, 'check_session_token' ),
            'args' => array(
                'url' => array( 'required' => true, 'type' => 'string' ),
            ),
        ) );

        // Create import - requires Step 1 session token
        register_rest_route( self::API_NAMESPACE, '/step1/create-import', array(
            'methods' => 'POST',
            'callback' => array( $this, 'handle_create_import' ),
            'permission_callback' => array( $this, 'check_session_token' ),
            'args' => array(
                'file_path' => array( 'required' => true, 'type' => 'string' ),
                'file_name' => array( 'required' => false, 'type' => 'string', 'default' => '' ), // Original filename for display
                'import_type' => array( 'required' => true, 'type' => 'string' ),
                'custom_type' => array( 'required' => false, 'type' => 'string' ),
                'taxonomy' => array( 'required' => false, 'type' => 'string' ),
                'source_type' => array( 'required' => false, 'type' => 'string', 'default' => 'upload' ),
                'original_url' => array( 'required' => false, 'type' => 'string', 'default' => '' ), // Original URL for URL-type imports
                // FTP/SFTP credentials (only used when source_type is 'ftp')
                'ftp_host' => array( 'required' => false, 'type' => 'string', 'default' => '' ),
                'ftp_port' => array( 'required' => false, 'type' => 'string', 'default' => '' ),
                'ftp_username' => array( 'required' => false, 'type' => 'string', 'default' => '' ),
                'ftp_password' => array( 'required' => false, 'type' => 'string', 'default' => '' ),
                'ftp_private_key' => array( 'required' => false, 'type' => 'string', 'default' => '' ),
                'ftp_root' => array( 'required' => false, 'type' => 'string', 'default' => '' ),
                'ftp_file_path' => array( 'required' => false, 'type' => 'string', 'default' => '' ), // Full path on FTP server
            ),
        ) );

        // List server files - requires Step 1 session token
        register_rest_route( self::API_NAMESPACE, '/step1/list-server-files', array(
            'methods' => 'GET',
            'callback' => array( $this, 'handle_list_server_files' ),
            'permission_callback' => array( $this, 'check_session_token' ),
            'args' => array(
                'path' => array( 'required' => false, 'type' => 'string', 'default' => '' ),
            ),
        ) );

        // Use server file - requires Step 1 session token
        register_rest_route( self::API_NAMESPACE, '/step1/use-server-file', array(
            'methods' => 'POST',
            'callback' => array( $this, 'handle_use_server_file' ),
            'permission_callback' => array( $this, 'check_session_token' ),
            'args' => array(
                'file_path' => array( 'required' => true, 'type' => 'string' ),
            ),
        ) );

        // FTP/SFTP connect and list files - requires Step 1 session token
        // Uses WP All Import's native RemoteFilesystem class for FTP/SFTP support
        register_rest_route( self::API_NAMESPACE, '/step1/ftp-list', array(
            'methods' => 'POST',
            'callback' => array( $this, 'handle_ftp_list' ),
            'permission_callback' => array( $this, 'check_session_token' ),
            'args' => array(
                'host' => array( 'required' => true, 'type' => 'string' ),
                'port' => array( 'required' => false, 'type' => 'string', 'default' => '' ),
                'username' => array( 'required' => true, 'type' => 'string' ),
                'password' => array( 'required' => false, 'type' => 'string', 'default' => '' ),
                'private_key' => array( 'required' => false, 'type' => 'string', 'default' => '' ),
                'path' => array( 'required' => false, 'type' => 'string', 'default' => '/' ),
                'root' => array( 'required' => false, 'type' => 'string', 'default' => '/' ),
            ),
        ) );

        // FTP/SFTP download file - requires Step 1 session token
        // Uses WP All Import's native RemoteFilesystem/PMXI_FTPFetcher for downloads
        register_rest_route( self::API_NAMESPACE, '/step1/ftp-download', array(
            'methods' => 'POST',
            'callback' => array( $this, 'handle_ftp_download' ),
            'permission_callback' => array( $this, 'check_session_token' ),
            'args' => array(
                'host' => array( 'required' => true, 'type' => 'string' ),
                'port' => array( 'required' => false, 'type' => 'string', 'default' => '' ),
                'username' => array( 'required' => true, 'type' => 'string' ),
                'password' => array( 'required' => false, 'type' => 'string', 'default' => '' ),
                'private_key' => array( 'required' => false, 'type' => 'string', 'default' => '' ),
                'file_path' => array( 'required' => true, 'type' => 'string' ),
                'root' => array( 'required' => false, 'type' => 'string', 'default' => '/' ),
            ),
        ) );
    }

    /**
     * Check if user has admin permissions (for init endpoint)
     * Same pattern as class-llm-config-api.php
     */
    public function check_admin_permissions( $request ) {
        if ( ! current_user_can( PMXI_Plugin::$capabilities ) ) {
            return new WP_Error(
                'rest_forbidden',
                __( 'You do not have permission to access this endpoint.', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 403 )
            );
        }
        return true;
    }

    /**
     * Check session token validity (permission_callback for protected endpoints)
     *
     * Same security model as class-llm-config-api.php:
     * - Token is passed in X-WPAI-Session-Token header (same header as Step 3)
     * - For cross-origin requests, we validate token exists and hasn't expired
     * - Token itself proves authorization since only authenticated user could obtain it
     * - For same-origin requests with cookies, we also check user permissions
     *
     * Two valid auth paths:
     * 1. Same-origin: Cookies present → current_user_can() works → allow
     * 2. Cross-origin: No cookies, but valid token → token proves prior auth → allow
     */
    public function check_session_token( $request ) {
        $token = $request->get_header( 'X-WPAI-Session-Token' );
        $has_wp_auth = current_user_can( PMXI_Plugin::$capabilities );

        // Path 1: Same-origin with cookies - WordPress handles auth
        if ( $has_wp_auth ) {
            // Still require token for consistency
            if ( empty( $token ) ) {
                return new WP_Error(
                    'rest_forbidden',
                    __( 'Session token is required.', 'wpai-ai-bridge-plugin' ),
                    array( 'status' => 401 )
                );
            }
            return true;
        }

        // Path 2: Cross-origin without cookies - validate token
        // Token proves user was authenticated when they obtained it via init_session()
        if ( empty( $token ) ) {
            return new WP_Error(
                'rest_forbidden',
                __( 'Session token is required.', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 401 )
            );
        }

        // Validate token - this proves the user was authenticated when they got the token
        if ( ! $this->validate_token( $token, false ) ) {
            return new WP_Error(
                'rest_forbidden',
                __( 'Invalid or expired session token.', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 401 )
            );
        }

        return true;
    }

    /**
     * Initialize Step 1 session and create token
     * Same pattern as class-llm-config-api.php init_session()
     */
    public function init_session( $request ) {
        $user_id = get_current_user_id();

        // Generate secure token (same method as class-llm-config-api.php)
        $token = wp_generate_password( 64, false );
        $expires = time() + self::SESSION_EXPIRATION;

        // Store in options table with user_id as key (since no import_id exists yet)
        // This mirrors the pattern in class-llm-config-api.php but uses user_id instead of import_id
        $option_name = self::STEP1_SESSION_PREFIX . $user_id;
        update_option( $option_name, array(
            'token' => $token,
            'expires' => $expires,
            'created' => time(),
            'user_id' => $user_id,
        ), false ); // Don't autoload

        return new WP_REST_Response( array(
            'success' => true,
            'session' => array(
                'token' => $token,
                'expires' => $expires,
                'expires_in' => self::SESSION_EXPIRATION,
            ),
            'message' => __( 'Step 1 session initialized successfully.', 'wpai-ai-bridge-plugin' ),
        ), 200 );
    }

    /**
     * Validate session (REST endpoint for iframe to check session validity)
     * Same pattern as class-llm-config-api.php validate_session()
     */
    public function validate_session( $request ) {
        $token = $request->get_header( 'X-WPAI-Session-Token' );

        if ( $this->validate_token( $token, false ) ) {
            $stored_data = $this->get_session_data_by_token( $token );
            return new WP_REST_Response( array(
                'success' => true,
                'valid' => true,
                'expires' => $stored_data['expires'],
            ), 200 );
        }

        return new WP_REST_Response( array(
            'success' => false,
            'valid' => false,
            'message' => __( 'Session is invalid or expired.', 'wpai-ai-bridge-plugin' ),
        ), 401 );
    }

    /**
     * Validate session token
     * Same security model as class-llm-config-api.php validate_token()
     *
     * @param string $token The session token
     * @param bool $check_user Whether to verify token belongs to current user
     * @return bool
     */
    public function validate_token( $token, $check_user = true ) {
        $stored_data = $this->get_session_data_by_token( $token );

        if ( empty( $stored_data ) || ! is_array( $stored_data ) ) {
            return false;
        }

        // Check if token matches (constant-time comparison to prevent timing attacks)
        if ( ! hash_equals( $stored_data['token'], $token ) ) {
            return false;
        }

        // Check if token has expired
        if ( time() > $stored_data['expires'] ) {
            $this->cleanup_session( $stored_data['user_id'] );
            return false;
        }

        // Verify the token belongs to the current user (prevents session hijacking)
        // Skip this check for cross-origin requests where cookies aren't sent
        if ( $check_user ) {
            $current_user_id = get_current_user_id();
            if ( empty( $current_user_id ) || $stored_data['user_id'] != $current_user_id ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get session data by token
     * Since we store by user_id, we need to search through active sessions
     */
    private function get_session_data_by_token( $token ) {
        global $wpdb;

        // Search for the session with this token
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
                self::STEP1_SESSION_PREFIX . '%'
            )
        );

        foreach ( $results as $row ) {
            $data = maybe_unserialize( $row->option_value );
            if ( is_array( $data ) && isset( $data['token'] ) && hash_equals( $data['token'], $token ) ) {
                return $data;
            }
        }

        return null;
    }

    /**
     * Clean up session after import is created or on expiration
     */
    private function cleanup_session( $user_id ) {
        $option_name = self::STEP1_SESSION_PREFIX . $user_id;
        delete_option( $option_name );
    }

    /**
     * Get user_id from token (for cleanup after import creation)
     */
    private function get_user_id_from_token( $token ) {
        $stored_data = $this->get_session_data_by_token( $token );
        return $stored_data ? $stored_data['user_id'] : null;
    }

    /**
     * Handle file upload from Vercel iframe
     */
    public function handle_file_upload( $request ) {
        $files = $request->get_file_params();

        if ( empty( $files['file'] ) ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'No file uploaded', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 400 )
            );
        }

        $file = $files['file'];

        // Validate file type - matches WP All Import supported formats
        $allowed_extensions = array( 'xml', 'csv', 'tsv', 'json', 'txt', 'dat', 'psv', 'sql', 'gz', 'gzip', 'zip', 'xls', 'xlsx' );
        $ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

        if ( ! in_array( $ext, $allowed_extensions ) ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'Invalid file type. Allowed: XML, CSV, TSV, JSON, TXT, DAT, PSV, SQL, ZIP, GZ, XLS, XLSX', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 400 )
            );
        }

        // Create a unique pre-import temp directory for this upload.
        // We cannot use wp_all_import_secure_file() here because without an import ID
        // it derives the directory from time() and can destructively reuse the same
        // directory across concurrent uploads in the same second.
        $uploads = wp_upload_dir();
        $uploads_base = $uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::UPLOADS_DIRECTORY;
        $upload_dir = trailingslashit( $uploads_base ) . wp_generate_uuid4();

        if ( ! wp_mkdir_p( $upload_dir ) || ! is_dir( $upload_dir ) ) {
            return new WP_Error(
                'rest_error',
                __( 'Failed to create upload directory', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 500 )
            );
        }

        if ( ! file_exists( $upload_dir . DIRECTORY_SEPARATOR . 'index.php' ) ) {
            @touch( $upload_dir . DIRECTORY_SEPARATOR . 'index.php' );
        }

        // Generate unique filename
        $filename = wp_unique_filename( $upload_dir, sanitize_file_name( $file['name'] ) );
        $file_path = $upload_dir . DIRECTORY_SEPARATOR . $filename;

        // Move uploaded file
        if ( ! move_uploaded_file( $file['tmp_name'], $file_path ) ) {
            return new WP_Error(
                'rest_error',
                __( 'Failed to save uploaded file', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 500 )
            );
        }

        return new WP_REST_Response( array(
            'success' => true,
            'file_path' => $file_path,
            'file_name' => $filename,
        ), 200 );
    }

    /**
     * Handle URL download request from Vercel iframe
     */
    /**
     * Returns a WP_Error when the URL resolves to a loopback, link-local, or
     * RFC-1918/reserved address (SSRF defense), or null when it is safe to fetch.
     * The host is resolved (A + AAAA) and every resolved IP is checked, so a
     * public hostname pointing at an internal address is rejected too. A host
     * that cannot be resolved is refused (fail closed).
     */
    /**
     * Whether the SSRF private-network guard should be skipped for this fetch.
     * True when the caller passes allow_private (truthy) on the request, or the
     * wpai_bridge_allow_private_fetch filter returns true site-wide. Intended for
     * local/containerized WordPress that must ingest its own loopback/LAN feeds;
     * off by default so production stays protected.
     */
    private static function private_fetch_allowed( $request ) {
        $per_request = false;
        if ( $request instanceof WP_REST_Request ) {
            $per_request = filter_var( $request->get_param( 'allow_private' ), FILTER_VALIDATE_BOOLEAN );
        }
        /**
         * Filter: allow the bridge to fetch private/loopback/reserved addresses.
         *
         * @param bool             $allow       Whether private fetches are permitted.
         * @param WP_REST_Request  $request     The originating request.
         */
        return (bool) apply_filters( 'wpai_bridge_allow_private_fetch', $per_request, $request );
    }

    private static function reject_private_network_url( $url ) {
        $host = wp_parse_url( $url, PHP_URL_HOST );
        if ( empty( $host ) ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'Invalid URL: no host.', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 400 )
            );
        }
        $host = trim( $host, '[]' ); // strip IPv6 brackets

        $ips = array();
        if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
            $ips[] = $host;
        } else {
            $records = function_exists( 'dns_get_record' ) ? @dns_get_record( $host, DNS_A | DNS_AAAA ) : array();
            foreach ( (array) $records as $r ) {
                if ( ! empty( $r['ip'] ) )   { $ips[] = $r['ip']; }
                if ( ! empty( $r['ipv6'] ) ) { $ips[] = $r['ipv6']; }
            }
            if ( empty( $ips ) ) {
                $v4 = gethostbyname( $host );
                if ( $v4 && $v4 !== $host ) { $ips[] = $v4; }
            }
        }

        if ( empty( $ips ) ) {
            return new WP_Error(
                'rest_forbidden_url',
                __( 'Could not resolve the URL host, so it cannot be fetched safely. Upload the file directly with add-source type=upload.', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 400 )
            );
        }

        foreach ( $ips as $ip ) {
            // NO_PRIV_RANGE blocks RFC-1918 (10/8, 172.16/12, 192.168/16) + fc00::/7;
            // NO_RES_RANGE blocks loopback (127/8, ::1), link-local (169.254/16 incl.
            // the 169.254.169.254 metadata endpoint, fe80::/10), 0.0.0.0, and other
            // reserved ranges. A public address passes both and returns the IP.
            if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
                return new WP_Error(
                    'rest_forbidden_url',
                    __( 'Refusing to fetch a URL that resolves to a private, loopback, link-local, or reserved address. To fetch a local/private address (e.g. a WordPress on localhost), pass allow_private:true. Otherwise provide a publicly reachable URL, or upload the file directly with add-source type=upload.', 'wpai-ai-bridge-plugin' ),
                    array( 'status' => 400 )
                );
            }
        }

        return null;
    }

    public function handle_url_download( $request ) {
        $url = $request->get_param( 'url' );

        if ( empty( $url ) ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'URL is required', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 400 )
            );
        }

        // Preserve the raw input URL (may contain inline template expressions)
        // This is what WPAI stores for re-runs so expressions are re-evaluated each time
        $raw_url = trim( $url );

        // Process the URL exactly as WPAI's native upload_resource handler does:
        // 1. Sanitize (Dropbox/Google Drive URL conversion, space encoding)
        // 2. Apply feed URL filter
        // 3. Run through XmlImportParser to evaluate any inline template expressions
        $url = apply_filters( 'wp_all_import_feed_url', wp_all_import_sanitize_url( $raw_url ) );

        $filesXML = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<data><node></node></data>";
        $files = XmlImportParser::factory( $filesXML, '/data/node', $url, $tmp_file )->parse();
        if ( ! empty( $tmp_file ) ) {
            @unlink( $tmp_file );
        }

        $resolved_url = ( ! empty( $files ) && is_array( $files ) ) ? array_shift( $files ) : $url;

        // Validate: must start with http:// or https:// (same check as PMXI_Upload::url)
        if ( ! preg_match( '%^https?://%i', $resolved_url ) ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'Invalid URL. Please make sure the URL starts with http:// or https://.', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 400 )
            );
        }

        // SSRF guard: refuse to fetch a URL that targets the server's own network
        // (loopback, link-local incl. the cloud metadata endpoint, or RFC-1918
        // private ranges), matching the rigor of the traversal/scheme blocking on
        // the file paths. The host is resolved and every resolved IP is checked, so
        // a public hostname that resolves to a private address is caught too.
        // Opt-out for local/containerized development: the per-request allow_private
        // flag, or the site-wide wpai_bridge_allow_private_fetch filter, skips it so
        // a WP on localhost/a private LAN can still ingest its own feeds.
        $private_allowed = self::private_fetch_allowed( $request );
        if ( ! $private_allowed ) {
            $ssrf = self::reject_private_network_url( $resolved_url );
            if ( is_wp_error( $ssrf ) ) {
                return $ssrf;
            }
        }

        // When the caller opted into a private fetch, a download failure is most
        // often WordPress core's own external-host block (http_request_host_is_external
        // refuses non-site hosts, including loopback) rather than a bad URL — the
        // bridge cannot open that filter for you, so point at it in the error.
        $local_fetch_hint = $private_allowed
            ? __( ' If this is a local/private host, note that WordPress core also blocks non-site hosts: on a trusted dev site, allow it with the http_request_host_is_external filter (e.g. add_filter("http_request_host_is_external","__return_true") in a mu-plugin).', 'wpai-ai-bridge-plugin' )
            : '';

        // Extract original filename from URL BEFORE conversion
        // PMXI_Upload->url() converts CSV/Excel to XML, losing the original filename
        $url_path = wp_parse_url( $resolved_url, PHP_URL_PATH );
        $original_name_from_url = $url_path ? basename( $url_path ) : '';

        // Clean up query strings that might be part of the filename
        if ( strpos( $original_name_from_url, '?' ) !== false ) {
            $original_name_from_url = substr( $original_name_from_url, 0, strpos( $original_name_from_url, '?' ) );
        }

        // Detect feed type from resolved URL (same as native flow)
        $feed_type = apply_filters( 'wp_all_import_feed_type', '', wp_all_import_sanitize_url( $url ) );

        // Use WP All Import's upload class to handle URL download
        $errors = new WP_Error();
        $uploader = new PMXI_Upload( trim( $resolved_url ), $errors );
        $result = $uploader->url( $feed_type, $url, '' );

        if ( $result instanceof WP_Error ) {
            return new WP_Error(
                'rest_error',
                sprintf(
                    /* translators: %s: underlying download error */
                    __( 'Could not download the source URL: %s. Check the URL is reachable and returns a supported feed (XML/CSV), or upload the file directly with add-source type=upload.', 'wpai-ai-bridge-plugin' ),
                    $result->get_error_message()
                ) . $local_fetch_hint,
                array( 'status' => 400 )
            );
        }

        if ( $errors->get_error_codes() ) {
            return new WP_Error(
                'rest_error',
                sprintf(
                    /* translators: %s: underlying download error */
                    __( 'Could not download the source URL: %s. Check the URL is reachable and returns a supported feed (XML/CSV), or upload the file directly with add-source type=upload.', 'wpai-ai-bridge-plugin' ),
                    $errors->get_error_message()
                ) . $local_fetch_hint,
                array( 'status' => 400 )
            );
        }

        // Use the original filename from URL (before conversion to XML)
        // source['name'] already contains the converted XML name, not the original
        $original_name = ! empty( $original_name_from_url ) ? $original_name_from_url : basename( $result['filePath'] );

        return new WP_REST_Response( array(
            'success' => true,
            'file_path' => $result['filePath'],
            'file_name' => $original_name, // Use original name, not converted XML name
            'original_url' => $raw_url, // Raw input URL with inline expressions for re-runs
            'source' => $result['source'],
            'is_csv' => $result['is_csv'],
            'root_element' => isset( $result['root_element'] ) ? $result['root_element'] : '',
        ), 200 );
    }

    /**
     * Handle listing server files from WP All Import files directory
     */
    public function handle_list_server_files( $request ) {
        $subpath = $request->get_param( 'path' ) ?: '';

        // Get the WP All Import files directory (where users upload files for import)
        $uploads = wp_upload_dir();
        $base_dir = $uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::FILES_DIRECTORY;

        // Sanitize and validate the subpath to prevent directory traversal
        $subpath = ltrim( $subpath, '/' );
        $subpath = str_replace( '..', '', $subpath );

        $target_dir = $base_dir;
        if ( ! empty( $subpath ) ) {
            $target_dir = $base_dir . DIRECTORY_SEPARATOR . $subpath;
        }

        // Ensure the target directory is within the base directory
        $real_base = realpath( $base_dir );
        $real_target = realpath( $target_dir );

        if ( $real_target === false || strpos( $real_target, $real_base ) !== 0 ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'Invalid directory path', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 400 )
            );
        }

        if ( ! is_dir( $real_target ) ) {
            return new WP_Error(
                'rest_not_found',
                __( 'Directory not found', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 404 )
            );
        }

        $files = array();
        // Matches WP All Import supported formats
        $allowed_extensions = array( 'xml', 'csv', 'tsv', 'json', 'txt', 'dat', 'psv', 'sql', 'gz', 'gzip', 'zip', 'xls', 'xlsx' );

        $iterator = new DirectoryIterator( $real_target );
        foreach ( $iterator as $file ) {
            if ( $file->isDot() ) {
                continue;
            }

            $item = array(
                'name' => $file->getFilename(),
                'path' => ( ! empty( $subpath ) ? $subpath . '/' : '' ) . $file->getFilename(),
                'is_dir' => $file->isDir(),
                'size' => $file->isFile() ? $file->getSize() : 0,
                'modified' => date( 'Y-m-d H:i:s', $file->getMTime() ),
            );

            // Only include directories and allowed file types
            if ( $file->isDir() ) {
                $files[] = $item;
            } elseif ( $file->isFile() ) {
                $ext = strtolower( $file->getExtension() );
                if ( in_array( $ext, $allowed_extensions ) ) {
                    $files[] = $item;
                }
            }
        }

        // Sort: directories first, then files alphabetically
        usort( $files, function( $a, $b ) {
            if ( $a['is_dir'] && ! $b['is_dir'] ) return -1;
            if ( ! $a['is_dir'] && $b['is_dir'] ) return 1;
            return strcasecmp( $a['name'], $b['name'] );
        } );

        return new WP_REST_Response( array(
            'success' => true,
            'path' => $subpath,
            'files' => $files,
        ), 200 );
    }

    /**
     * Handle using an existing server file
     */
    public function handle_use_server_file( $request ) {
        $file_path = $request->get_param( 'file_path' );

        if ( empty( $file_path ) ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'File path is required', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 400 )
            );
        }

        // Get the WP All Import files directory
        $uploads = wp_upload_dir();
        $base_dir = $uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::FILES_DIRECTORY;

        // Sanitize path to prevent directory traversal
        $file_path = ltrim( $file_path, '/' );
        $file_path = str_replace( '..', '', $file_path );

        $full_path = $base_dir . DIRECTORY_SEPARATOR . $file_path;

        // Ensure the file is within the base directory
        $real_base = realpath( $base_dir );
        $real_file = realpath( $full_path );

        if ( $real_file === false || strpos( $real_file, $real_base ) !== 0 ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'Invalid file path', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 400 )
            );
        }

        if ( ! file_exists( $real_file ) || ! is_file( $real_file ) ) {
            return new WP_Error(
                'rest_not_found',
                __( 'File not found', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 404 )
            );
        }

        // Validate file type - matches WP All Import supported formats
        $allowed_extensions = array( 'xml', 'csv', 'tsv', 'json', 'txt', 'dat', 'psv', 'sql', 'gz', 'gzip', 'zip', 'xls', 'xlsx' );
        $ext = strtolower( pathinfo( $real_file, PATHINFO_EXTENSION ) );

        if ( ! in_array( $ext, $allowed_extensions ) ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'Invalid file type. Allowed: XML, CSV, TSV, JSON, TXT, DAT, PSV, SQL, ZIP, GZ, XLS, XLSX', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 400 )
            );
        }

        // Return both the full path and relative path
        // The relative path is needed for PMXI_Upload::file() method
        $relative_path = str_replace( $real_base . DIRECTORY_SEPARATOR, '', $real_file );

        return new WP_REST_Response( array(
            'success' => true,
            'file_path' => $relative_path, // Relative path for file() method
            'file_name' => basename( $real_file ),
        ), 200 );
    }

    /**
     * Handle FTP/SFTP connection and file listing
     *
     * This EXACTLY replicates WP All Import's FtpbrowserController::loadAction()
     * to ensure 100% compatibility with how WP All Import handles FTP paths.
     * The paths returned will be in the exact format WP All Import expects.
     *
     * @see wp-all-import-pro/src/App/UnsecuredController/FtpbrowserController.php
     */
    public function handle_ftp_list( $request ) {
        $host = $request->get_param( 'host' );
        $port = $request->get_param( 'port' ) ?: '';
        $username = $request->get_param( 'username' );
        $password = $request->get_param( 'password' ) ?: '';
        $private_key = $request->get_param( 'private_key' ) ?: '';
        // IMPORTANT: Use empty string for path, not '/' - matches WP All Import's ftp-browser.js
        // WP All Import's formatPath() returns '' for root, not '/'
        $path = $request->get_param( 'path' );
        if ( $path === null || $path === '/' ) {
            $path = '';
        }
        $root = $request->get_param( 'root' ) ?: '/';

        // Validate required parameters - matches FtpbrowserController validation
        if ( empty( $host ) || empty( $username ) ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'Missing Host Details.', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 400 )
            );
        }

        // Either password or private key is required - matches FtpbrowserController
        if ( empty( $password ) && empty( $private_key ) ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'A password or SFTP Private key is required.', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 400 )
            );
        }

        // Ensure RemoteFilesystem class is available
        if ( ! class_exists( 'RemoteFilesystem' ) ) {
            require_once( WP_ALL_IMPORT_ROOT_DIR . '/classes/filesystem/RemoteFilesystem.php' );
        }

        // Build options array EXACTLY matching FtpbrowserController::loadAction()
        // @see FtpbrowserController.php lines 46-54
        $options = array(
            'host' => stripslashes( $host ),
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'root' => empty( $root ) ? '/' : $root,
            'dir' => $path, // Use path directly, let frontend handle formatting
            'privateKey' => $private_key,
        );

        try {
            $ftp = new \RemoteFilesystem( $options );

            // Get contents - this is where RemoteFilesystem may adjust the root
            $contents = array();
            $contents['data'] = $ftp->listContents();

            if ( $ftp->getError() !== false ) {
                return new WP_Error(
                    'ftp_connection_failed',
                    $ftp->getError(),
                    array( 'status' => 400 )
                );
            }

            if ( empty( $contents['data'] ) ) {
                return new WP_Error(
                    'ftp_list_empty',
                    __( 'No data returned from the (S)FTP server. The root path is probably required. Enter it in the path field like \'/root/path/here\'. The filename is optional when selecting on the Data Source screen.', 'wpai-ai-bridge-plugin' ),
                    array( 'status' => 400 )
                );
            }

            // Check if port changed - EXACT match to FtpbrowserController lines 70-72
            if ( $port != $ftp->get_port() ) {
                $contents['port'] = $ftp->get_port();
            }

            // Check if host protocol needs to be noted - EXACT match to FtpbrowserController lines 75-77
            if ( ! ( strpos( strtolower( $host ), $ftp->get_protocol() . '://' ) !== false ) ) {
                $contents['host'] = $ftp->get_protocol() . '://' . $host;
            }

            // Check if root path changed - EXACT match to FtpbrowserController lines 80-82
            // This is the critical part for path handling
            if ( ! ( strpos( strtolower( $root ), $ftp->get_root() ) !== false ) ) {
                $contents['root'] = $ftp->get_root();
            }



            // Return contents in EXACT same format as FtpbrowserController
            // The frontend (FtpInput.tsx) expects this format
            return new WP_REST_Response( $contents, 200 );

        } catch ( \Exception $e ) {
            return new WP_Error(
                'ftp_error',
                $e->getMessage(),
                array( 'status' => 400 )
            );
        }
    }

    /**
     * Handle FTP/SFTP file download
     * Uses WP All Import's native PMXI_FTPFetcher class
     */
    public function handle_ftp_download( $request ) {
        $host = $request->get_param( 'host' );
        $port = $request->get_param( 'port' ) ?: '';
        $username = $request->get_param( 'username' );
        $password = $request->get_param( 'password' ) ?: '';
        $private_key = $request->get_param( 'private_key' ) ?: '';
        $file_path = $request->get_param( 'file_path' );
        $root = $request->get_param( 'root' ) ?: '/';

        // Validate required parameters
        if ( empty( $host ) || empty( $username ) || empty( $file_path ) ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'Host, username, and file path are required', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 400 )
            );
        }

        // Either password or private key is required
        if ( empty( $password ) && empty( $private_key ) ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'A password or SFTP private key is required', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 400 )
            );
        }

        // SSRF guard for the (S)FTP host — the same private/loopback/reserved-range
        // rejection applied to url fetches, so `ftp` cannot be used to reach the
        // server's own network (e.g. 127.0.0.1). Same opt-out (allow_private / the
        // wpai_bridge_allow_private_fetch filter) for local/containerized dev.
        if ( ! self::private_fetch_allowed( $request ) ) {
            $probe = ( false !== strpos( (string) $host, '://' ) ) ? (string) $host : 'ftp://' . $host;
            $ssrf  = self::reject_private_network_url( $probe );
            if ( is_wp_error( $ssrf ) ) {
                return $ssrf;
            }
        }

        // Validate file type
        $allowed_extensions = array( 'xml', 'csv', 'json', 'txt', 'gz', 'gzip', 'zip', 'xls', 'xlsx', 'tsv', 'dat', 'psv', 'sql' );
        $ext = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );

        if ( ! in_array( $ext, $allowed_extensions ) ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'Invalid file type. Allowed: XML, CSV, JSON, TXT, ZIP, GZ, XLS, XLSX, TSV, DAT, PSV, SQL', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 400 )
            );
        }

        // Ensure PMXI_FTPFetcher class is available
        if ( ! class_exists( 'PMXI_FTPFetcher' ) ) {
            require_once( WP_ALL_IMPORT_ROOT_DIR . '/classes/ftp/FTPFetcher.php' );
        }

        // Build options array for PMXI_FTPFetcher
        $options = array(
            'ftp_host' => stripslashes( $host ),
            'ftp_port' => $port,
            'ftp_username' => $username,
            'ftp_password' => $password,
            'ftp_path' => $file_path,
            'ftp_root' => $root,
            'ftp_private_key' => $private_key,
        );

        try {
            $files = \PMXI_FTPFetcher::fetch( $options );

            if ( empty( $files ) || ! is_array( $files ) ) {
                return new WP_Error(
                    'ftp_download_failed',
                    __( 'Could not download the file from the (S)FTP server. Check the host, port, credentials, and that the file path exists on the server.', 'wpai-ai-bridge-plugin' ),
                    array( 'status' => 400 )
                );
            }

            $local_path = $files[0];

            // Return the file_path as-is (relative to root) - this matches how
            // WP All Import's native FTP browser stores paths.
            // The ftp_root is stored separately, so ftp_path just needs to be the relative path.
            return new WP_REST_Response( array(
                'success' => true,
                'file_path' => $local_path,
                'file_name' => basename( $local_path ),
                'ftp_path' => ltrim( $file_path, '/' ), // Path relative to root, without leading slash
            ), 200 );

        } catch ( \Exception $e ) {
            // WP All Import's fetcher throws generic upload-flow messages (e.g.
            // "Uploaded file is empty" from PMXI_FTPFetcher when the connection
            // yields nothing) that misdescribe a download. Wrap it so the surfaced
            // error names the actual operation and likely causes, keeping the
            // underlying detail for diagnosis.
            return new WP_Error(
                'ftp_download_failed',
                sprintf(
                    /* translators: %s: underlying (S)FTP error detail */
                    __( 'Could not download the file from the (S)FTP server (%s). Check the host, port, credentials, and that the file path exists on the server.', 'wpai-ai-bridge-plugin' ),
                    $e->getMessage()
                ),
                array( 'status' => 400 )
            );
        }
    }

    /**
     * Directories a source file handed to create-import may live in.
     *
     * add-source only ever produces paths inside these two: uploads/URL/FTP fetches land
     * in WP All Import's uploads directory, existing server files in its files directory.
     */
    public static function source_base_dirs() {
        $uploads = wp_upload_dir();

        return array(
            $uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::UPLOADS_DIRECTORY,
            $uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::FILES_DIRECTORY,
        );
    }

    /**
     * Whether a resolved path sits inside one of the given base directories.
     *
     * @param string $real_path Path already run through realpath().
     * @param array  $base_dirs Base directories to test against.
     * @return bool
     */
    public static function path_within_base_dirs( $real_path, $base_dirs ) {
        if ( empty( $real_path ) || ! is_array( $base_dirs ) ) {
            return false;
        }

        foreach ( $base_dirs as $base_dir ) {
            $real_base = realpath( $base_dir );

            if ( $real_base === false ) {
                continue;
            }

            $real_base = rtrim( $real_base, DIRECTORY_SEPARATOR );

            if ( $real_path === $real_base || strpos( $real_path, $real_base . DIRECTORY_SEPARATOR ) === 0 ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle import creation and redirect to Step 3
     */
    public function handle_create_import( $request ) {

        $file_path = $request->get_param( 'file_path' );
        $file_name = $request->get_param( 'file_name' ) ?: ''; // Original filename passed from frontend
        $import_type = $request->get_param( 'import_type' );
        $custom_type = $request->get_param( 'custom_type' ) ?: '';
        $taxonomy = $request->get_param( 'taxonomy' ) ?: '';
        $source_type = $request->get_param( 'source_type' ) ?: 'upload';
        $original_url = $request->get_param( 'original_url' ) ?: ''; // Original URL for URL-type imports

        // FTP/SFTP credentials (only used when source_type is 'ftp')
        $ftp_host = $request->get_param( 'ftp_host' ) ?: '';
        $ftp_port = $request->get_param( 'ftp_port' ) ?: '';
        $ftp_username = $request->get_param( 'ftp_username' ) ?: '';
        $ftp_password = $request->get_param( 'ftp_password' ) ?: '';
        $ftp_private_key = $request->get_param( 'ftp_private_key' ) ?: '';
        $ftp_root = $request->get_param( 'ftp_root' ) ?: '';
        $ftp_file_path = $request->get_param( 'ftp_file_path' ) ?: ''; // Full path on FTP server


        if ( empty( $file_path ) ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'File path is required', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 400 )
            );
        }

        // For 'file' source type (existing server file), the file_path is relative to FILES_DIRECTORY
        // For other types, file_path is absolute
        if ( $source_type === 'file' ) {
            $wp_uploads = wp_upload_dir();
            $files_dir = $wp_uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::FILES_DIRECTORY . DIRECTORY_SEPARATOR;
            $full_file_path = $files_dir . ltrim( $file_path, '/' );
            $allowed_base_dirs = array( $files_dir );
        } else {
            $full_file_path = $file_path;
            $allowed_base_dirs = self::source_base_dirs();
        }

        // A path that resolves outside the WP All Import source directories is rejected;
        // one that does not resolve at all falls through to the not-found response below.
        $real_file_path = realpath( $full_file_path );

        if ( $real_file_path !== false ) {
            if ( ! self::path_within_base_dirs( $real_file_path, $allowed_base_dirs ) ) {
                return new WP_Error(
                    'rest_invalid_param',
                    __( 'Invalid file path', 'wpai-ai-bridge-plugin' ),
                    array( 'status' => 400 )
                );
            }

            $full_file_path = $real_file_path;
        }

        if ( ! file_exists( $full_file_path ) ) {
            // Don't echo the absolute server path back to the caller (info leak); reference
            // the value they passed instead and steer them to the source handoff.
            return new WP_Error(
                'rest_not_found',
                sprintf(
                    /* translators: %s: the file identifier the caller supplied */
                    __( 'Source file "%s" was not found. Call add-source first and pass the file_path it returns (with the matching source_type).', 'wpai-ai-bridge-plugin' ),
                    (string) $file_path
                ),
                array( 'status' => 404 )
            );
        }

        // Validate file type - matches WP All Import supported formats
        $allowed_extensions = array( 'xml', 'csv', 'tsv', 'json', 'txt', 'dat', 'psv', 'sql', 'gz', 'gzip', 'zip', 'xls', 'xlsx' );
        $ext = strtolower( pathinfo( $full_file_path, PATHINFO_EXTENSION ) );

        if ( ! in_array( $ext, $allowed_extensions ) ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'Invalid file type. Allowed: XML, CSV, TSV, JSON, TXT, DAT, PSV, SQL, ZIP, GZ, XLS, XLSX', 'wpai-ai-bridge-plugin' ),
                array( 'status' => 400 )
            );
        }

        // Determine the actual custom type
        $actual_custom_type = $import_type;
        if ( $import_type === 'taxonomies' ) {
            $actual_custom_type = 'taxonomies';
        } elseif ( ! empty( $custom_type ) ) {
            $actual_custom_type = $custom_type;
        }

        // Map source_type to WP All Import's import type
        $import_type_map = array(
            'upload' => 'upload',
            'url' => 'url',
            'file' => 'file',
            'ftp' => 'ftp',
        );
        $wpai_import_type = isset( $import_type_map[ $source_type ] ) ? $import_type_map[ $source_type ] : 'upload';

        // CRITICAL: Create import record FIRST to get an import ID
        // This is needed because PMXI_Upload uses the import ID to create a consistent
        // secure directory path via wp_all_import_secure_file()
        $import = new PMXI_Import_Record();
        $import_name = basename( $file_path );

        $import->set( array(
            'name' => $import_name,
            'friendly_name' => $import_name,
            'type' => $wpai_import_type,
            'path' => '', // Will be updated after file processing
            'root_element' => 'node', // Will be updated after file processing
            'xpath' => '/node', // Will be updated after file processing
            'options' => array(
                'custom_type' => $actual_custom_type,
                'taxonomy_type' => $taxonomy,
                'encoding' => 'UTF-8', // Required for file parsing by REST API endpoints
            ),
            'count' => 0,
            'registered_on' => date( 'Y-m-d H:i:s' ),
        ) )->save();

        // Create the secure upload directory manually using the import ID
        // We do this ourselves instead of relying on wp_all_import_secure_file() because
        // that function silently falls back to the parent directory if creation fails
        $wp_uploads = wp_upload_dir();
        $uploads_base = $wp_uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::UPLOADS_DIRECTORY;

        // Check if secure imports are enabled
        $is_secure_import = PMXI_Plugin::getInstance()->getOption('secure');

        if ( $is_secure_import ) {
            $nonce_salt = defined('NONCE_SALT') ? NONCE_SALT : wp_salt('nonce');
            $target_dir = $uploads_base . DIRECTORY_SEPARATOR . md5( $import->id . $nonce_salt );
        } else {
            $target_dir = $uploads_base;
        }

        // Create the directory if it doesn't exist
        if ( ! is_dir( $target_dir ) ) {
            $created = wp_mkdir_p( $target_dir );
            if ( ! $created ) {
                $import->delete();
                return new WP_Error(
                    'rest_error',
                    sprintf( __( 'Failed to create upload directory: %s', 'wp-all-import-ai-bridge' ), $target_dir ),
                    array( 'status' => 500 )
                );
            }
            // Create index.php for security
            @touch( $target_dir . DIRECTORY_SEPARATOR . 'index.php' );
        } else {
        }

        // Verify the directory is writable
        if ( ! is_writable( $target_dir ) ) {
            $import->delete();
            return new WP_Error(
                'rest_error',
                sprintf( __( 'Upload directory is not writable: %s', 'wp-all-import-ai-bridge' ), $target_dir ),
                array( 'status' => 500 )
            );
        }

        // Also set $_GET['id'] for any code that might still rely on it
        $_GET['id'] = $import->id;

        // Process the file using WP All Import's upload class
        // Use different methods based on source type (mirrors import.php index())
        $errors = new WP_Error();

        // Suppress PHP warnings/notices that might corrupt JSON output
        // WP All Import's libraries have deprecation warnings in PHP 8.x
        $previous_error_reporting = error_reporting( E_ERROR | E_PARSE );
        ob_start();

        if ( $source_type === 'file' ) {
            // For existing server files, check if it's a native XML file
            $ext = strtolower( pathinfo( $full_file_path, PATHINFO_EXTENSION ) );

            if ( $ext === 'xml' ) {
                // Native XML file - copy to /uploads/ but skip the full PMXI_Upload processing
                // This matches WP All Import's behavior: copy file, but no format conversion needed
                // Copy file to uploads directory (matching PMXI_Upload->file() behavior)
                $copy_target = $target_dir . DIRECTORY_SEPARATOR . basename( $full_file_path );
                if ( ! @copy( $full_file_path, $copy_target ) ) {
                    $errors->add( 'form-validation', __( 'Failed to copy XML file to uploads directory.', 'wp-all-import-pro' ) );
                }

                // Detect root element from XML file
                $detected_root = $this->detect_root_element( $full_file_path );

                // Build result array matching PMXI_Upload format
                // filePath points to the COPIED file in /uploads/
                // source['path'] points to the ORIGINAL file in /files/
                $result = array(
                    'filePath' => $copy_target, // Copied file in /uploads/
                    'is_csv' => false,
                    'csv_path' => '',
                    'source' => array(
                        'name' => basename( $full_file_path ),
                        'type' => 'file',
                        'path' => $full_file_path, // Original file in /files/
                    ),
                    'root_element' => $detected_root,
                );
            } else {
                // Non-XML file (CSV, Excel, etc.) - use PMXI_Upload for conversion
                // Pass the target directory so PMXI_Upload uses our pre-created secure directory
                $uploader = new PMXI_Upload( $file_path, $errors, $target_dir );
                $result = $uploader->file();
            }
        } elseif ( $source_type === 'url' || $source_type === 'upload' || $source_type === 'ftp' ) {
            // For URL downloads, uploads, and FTP, check if it's a native XML file
            $ext = strtolower( pathinfo( $full_file_path, PATHINFO_EXTENSION ) );

            if ( $ext === 'xml' ) {
                // Native XML file - copy to target directory, skip PMXI_Upload processing
                // This matches the handling for server XML files above
                // Copy file to target directory
                $copy_target = $target_dir . DIRECTORY_SEPARATOR . basename( $full_file_path );
                if ( ! @copy( $full_file_path, $copy_target ) ) {
                    $errors->add( 'form-validation', __( 'Failed to copy XML file to uploads directory.', 'wp-all-import-pro' ) );
                }

                // Detect root element from XML file
                $detected_root = $this->detect_root_element( $full_file_path );

                // Build result array matching PMXI_Upload format
                $result = array(
                    'filePath' => $copy_target,
                    'is_csv' => false,
                    'csv_path' => '',
                    'source' => array(
                        'name' => basename( $full_file_path ),
                        'type' => $source_type,
                        'path' => $full_file_path, // Original file location
                    ),
                    'root_element' => $detected_root,
                );
            } else {
                // Non-XML file (CSV, Excel, etc.) - use PMXI_Upload for conversion
                $uploader = new PMXI_Upload( $full_file_path, $errors, $target_dir );
                $result = $uploader->upload();
            }
        } else {
            // Fallback for any other source types
            $uploader = new PMXI_Upload( $full_file_path, $errors, $target_dir );
            $result = $uploader->upload();
        }

        // Restore error reporting and discard any output
        ob_end_clean();
        error_reporting( $previous_error_reporting );
		
        // Handle file processing errors - delete the import record if processing failed
        if ( $result instanceof WP_Error ) {
            $import->delete();
            return new WP_Error(
                'rest_error',
                $result->get_error_message(),
                array( 'status' => 400 )
            );
        }

        if ( $errors->get_error_codes() ) {
            $import->delete();
            return new WP_Error(
                'rest_error',
                $errors->get_error_message(),
                array( 'status' => 400 )
            );
        }

        // Initialize session if not already set (REST API context)
        // Use PMXI_Handler which is the concrete implementation of PMXI_Session
        // Suppress warnings from PMXI_Handler that might corrupt JSON output
        $previous_error_reporting = error_reporting( E_ERROR | E_PARSE );
        ob_start();
        if ( empty( PMXI_Plugin::$session ) ) {
            PMXI_Plugin::$session = new PMXI_Handler();
        }

        // Initialize session with file data
        PMXI_Plugin::$session->clean_session();
        ob_end_clean();
        error_reporting( $previous_error_reporting );

        // CRITICAL FIX: Build source array with correct values for the PROCESSED file
        // $result['source'] contains the SOURCE file info (xlsx, csv), but we need
        // the session's source to reflect the PROCESSED XML file
        // This source array is what gets saved to the import record when the form is submitted
        $source = array(
            'name' => basename( $result['filePath'] ),
            'type' => $wpai_import_type, // Use the import type we set, not 'file'
            'path' => wp_all_import_get_relative_path( $result['filePath'] ), // Processed XML path
            'root_element' => ! empty( $result['root_element'] ) ? $result['root_element'] : 'node',
            'first_import' => date( 'Y-m-d H:i:s' ),
        );

        // Get root element and xpath
        $root_element = ! empty( $result['root_element'] ) ? $result['root_element'] : 'node';
        $xpath = '/' . $root_element;

        // Count elements in file
        $count = $this->count_elements( $result['filePath'], $root_element, $xpath );

        // IMPORTANT: Determine the correct path to save based on source type
        // The path saved to import->path is used for DISPLAY purposes on manage imports page
        // The actual XML file path is stored in session->filePath for processing
        // - For 'url': Use original URL (like WP All Import does natively)
        // - For 'file' (existing server files): Use original path since /wpallimport/files/ is permanent
        // - For 'upload', 'ftp': Use processed XML path (original is in temp)
        if ( $source_type === 'url' && ! empty( $original_url ) ) {
            // URL source - use original URL for display (matches WP All Import behavior)
            $path_to_save = $original_url;
        } elseif ( $source_type === 'file' ) {
            // Server files - original is in permanent /files/ directory, use it
            if ( ! empty( $result['is_csv'] ) && ! empty( $result['csv_path'] ) ) {
                $path_to_save = $result['csv_path'];
            } elseif ( isset( $result['source']['path'] ) ) {
                $path_to_save = $result['source']['path'];
            } else {
                $path_to_save = $result['filePath'];
            }
        } elseif ( $source_type === 'ftp' ) {
            // FTP source - WP All Import stores LOCAL path in 'path' field, FTP details in options
            // The ftp_file_path contains the full URL (e.g., sftp://host:port/path/to/file.xml)
            // We need to use the local processed file for path, and extract just the path portion for ftp_path option
            $path_to_save = $result['filePath'];
        } else {
            // Upload or URL without original_url - use processed XML path
            $path_to_save = $result['filePath'];
        }

        // For URL paths, save the full URL; for all others (including FTP), convert to relative
        // FTP imports store FTP details in options, not in the path field
        if ( $source_type === 'url' && ! empty( $original_url ) ) {
            $import_path_to_save = $path_to_save;
        } else {
            $import_path_to_save = wp_all_import_get_relative_path( $path_to_save );
        }
        // Use file_name from frontend if provided (contains original name before XML conversion)
        $import_name_to_save = ! empty( $file_name ) ? $file_name : basename( $path_to_save );

        // CRITICAL FIX: Update $source array with correct values BEFORE setting on session
        // The $source created earlier (lines 1097-1103) uses XML values, but when
        // process() runs (import.php:2758-2773), it merges session->source into the import,
        // overwriting the import's path/name. So source must use the same values we're saving.
        $source = array(
            'name' => $import_name_to_save,
            'type' => $wpai_import_type,
            'path' => $import_path_to_save,
            'root_element' => $root_element,
            'first_import' => date( 'Y-m-d H:i:s' ),
        );

        // Update the import record with the correct file path, type, and metadata
        // IMPORTANT: options['encoding'] is required by get_file_preview and other API endpoints
        // Use ORIGINAL source file path for name/path (what user uploaded), not the processed XML
        // Build import options array
        $import_options = array(
            'custom_type' => $actual_custom_type,
            'taxonomy_type' => $taxonomy,
            'encoding' => 'UTF-8', // Required for file parsing by REST API endpoints
        );

        // Add FTP/SFTP credentials to options if this is an FTP import
        if ( $source_type === 'ftp' && ! empty( $ftp_host ) ) {
            $import_options['ftp_host'] = $ftp_host;
            $import_options['ftp_port'] = $ftp_port;
            $import_options['ftp_username'] = $ftp_username;
            $import_options['ftp_password'] = $ftp_password;
            $import_options['ftp_private_key'] = $ftp_private_key;
            $import_options['ftp_root'] = $ftp_root;

            // Store ftp_path directly - it's already the path relative to root
            // (matching how WP All Import's native FTP browser stores it)
            if ( ! empty( $ftp_file_path ) ) {
                $import_options['ftp_path'] = ltrim( $ftp_file_path, '/' );
            }
        }

        $import->set( array(
            'name' => $import_name_to_save,
            'friendly_name' => $import_name_to_save,
            'type' => $wpai_import_type, // Ensure type is set correctly (not 'file' from source)
            'path' => $import_path_to_save,
            'root_element' => $root_element,
            'xpath' => $xpath,
            'options' => $import_options,
            'count' => $count,
        ) )->save();

        // Check via ORM
        $saved_import = new PMXI_Import_Record();
        $saved_import->getById( $import->id );

        // Check raw database to rule out ORM caching issues
        global $wpdb;
        $table = $wpdb->prefix . 'pmxi_imports';
        $db_row = $wpdb->get_row( $wpdb->prepare( "SELECT id, name, path, type FROM {$table} WHERE id = %d", $import->id ) );
		
        // Create file history record to reliably store the XML file path
        // This is important for URL/CSV imports where import->path stores the original source for display,
        // but we need to find the processed XML file for LLM processing
        $file_history = new PMXI_File_Record();
        $file_history->set( array(
            'import_id' => $import->id,
            'name' => basename( $result['filePath'] ),
            'path' => wp_all_import_get_relative_path( $result['filePath'] ),
            'registered_on' => date( 'Y-m-d H:i:s' ),
        ) )->save();

        // Pre-cache file structure now that import is fully configured.
        // This eliminates the ~3s cold start when Step 3 loads.
        // The import record has root_element, xpath, and the XML file exists.
        do_action( 'pmxi_import_file_ready', $import->id, $result['filePath'], $import );

        // Copy the ORIGINAL file to /files/ directory for "Existing File" option
        // This matches WP All Import's behavior in settings.php (lines 886-893)
        // IMPORTANT: For CSV/Excel files, copy the ORIGINAL file, not the converted XML
        // SKIP for source_type='file' - the original is already in /files/ directory
        $copy_file_allowed = apply_filters( 'wp_all_import_copy_uploaded_file_into_files_folder', true );
        if ( $copy_file_allowed && $source_type !== 'file' ) {
	        $wp_uploads = wp_upload_dir();
	        $files_dir  = $wp_uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::FILES_DIRECTORY . DIRECTORY_SEPARATOR;

	        // Determine which file to copy:
	        // - For CSV/Excel: copy the original file (csv_path), NOT the converted XML
	        // - For native XML: copy the XML file (filePath)
	        if ( ! empty( $result['is_csv'] ) && ! empty( $result['csv_path'] ) && file_exists( $result['csv_path'] ) ) {
		        $source_file = $result['csv_path'];
	        } else {
		        $source_file = $result['filePath'];
	        }

	        $target_file = $files_dir . basename( $source_file );

	        // Check if source file is already in /files/ directory
	        $source_is_in_files_dir = ( strpos( realpath( $source_file ), realpath( $files_dir ) ) === 0 );

	        if ( ! $source_is_in_files_dir && ! file_exists( $target_file ) && file_exists( $source_file ) ) {
		        @copy( $source_file, $target_file );
	        }
        }

        // Set session data (mirrors what import.php index() does)
        PMXI_Plugin::$session->set( 'filePath', $result['filePath'] );
        PMXI_Plugin::$session->set( 'source', $source );
        PMXI_Plugin::$session->set( 'xpath', $xpath );
        PMXI_Plugin::$session->set( 'root_element', $root_element );
        PMXI_Plugin::$session->set( 'is_csv', $result['is_csv'] );
        PMXI_Plugin::$session->set( 'csv_path', isset( $result['csv_path'] ) ? $result['csv_path'] : '' );
        PMXI_Plugin::$session->set( 'custom_type', $actual_custom_type );
        PMXI_Plugin::$session->set( 'taxonomy_type', $taxonomy );
        PMXI_Plugin::$session->set( 'wizard_type', 'new' );
        PMXI_Plugin::$session->set( 'encoding', 'UTF-8' );
        PMXI_Plugin::$session->set( 'chunk_number', 1 );
        PMXI_Plugin::$session->set( 'log', '' );
        PMXI_Plugin::$session->set( 'processing', 0 );
        PMXI_Plugin::$session->set( 'queue_chunk_number', 0 );
        PMXI_Plugin::$session->set( 'warnings', 0 );
        PMXI_Plugin::$session->set( 'errors', 0 );
        PMXI_Plugin::$session->set( 'start_time', 0 );
        PMXI_Plugin::$session->set( 'local_paths', array( $result['filePath'] ) );
        PMXI_Plugin::$session->set( 'action', 'import' );
        PMXI_Plugin::$session->set( 'pointer', 1 );
        PMXI_Plugin::$session->set( 'count', $count );

        // Save import ID to session - following the EXACT same pattern as WP All Import's index() method
        // In the original flow, session is saved to 'new' key and redirect has NO id parameter
        PMXI_Plugin::$session->set( 'import_id', $import->id );
        PMXI_Plugin::$session->set( 'update_previous', $import->id );

        // For cross-origin REST API calls, save_data() won't work because has_session() returns false
        // (no cookies = no is_user_logged_in()). We need to manually save the session data.
        // Build the same data array that PMXI_Session::set() builds and save it in the same format
        // that PMXI_Handler::save_data() uses: base64_encode(serialize($data))
        $session_data = array(
            'filepath'           => maybe_serialize( $result['filePath'] ),
            'source'             => maybe_serialize( $source ),
            'xpath'              => maybe_serialize( $xpath ),
            'root_element'       => maybe_serialize( $root_element ),
            'is_csv'             => maybe_serialize( $result['is_csv'] ),
            'csv_path'           => maybe_serialize( isset( $result['csv_path'] ) ? $result['csv_path'] : '' ),
            'custom_type'        => maybe_serialize( $actual_custom_type ),
            'taxonomy_type'      => maybe_serialize( $taxonomy ),
            'wizard_type'        => maybe_serialize( 'new' ),
            'encoding'           => maybe_serialize( 'UTF-8' ),
            'chunk_number'       => maybe_serialize( 1 ),
            'log'                => maybe_serialize( '' ),
            'processing'         => maybe_serialize( 0 ),
            'queue_chunk_number' => maybe_serialize( 0 ),
            'warnings'           => maybe_serialize( 0 ),
            'errors'             => maybe_serialize( 0 ),
            'start_time'         => maybe_serialize( 0 ),
            'local_paths'        => maybe_serialize( array( $result['filePath'] ) ),
            'action'             => maybe_serialize( 'import' ),
            'pointer'            => maybe_serialize( 1 ),
            'count'              => maybe_serialize( $count ),
            'import_id'          => maybe_serialize( $import->id ),
            'update_previous'    => maybe_serialize( $import->id ),
        );

        // Save using the same format as PMXI_Handler::save_data()
        // IMPORTANT: Save to 'new' key, not the import ID, because the redirect URL has no 'id' parameter
        // When Step 3 loads, PMXI_Handler::generate_import_id() reads from $_GET['id'] and defaults to 'new'
        $session_option = '_wpallimport_session_new_';
        $session_expiry_option = '_wpallimport_session_expires_new_';

        // Clear caches
        wp_cache_delete( 'notoptions', 'options' );
        wp_cache_delete( $session_option, 'options' );
        wp_cache_delete( $session_expiry_option, 'options' );

        // Save session data in same format as PMXI_Handler
        $session_expiration = time() + ( 60 * 60 * 48 ); // 48 hours
        if ( false === get_option( $session_option ) ) {
            add_option( $session_option, base64_encode( serialize( $session_data ) ), '', 'no' );
            add_option( $session_expiry_option, $session_expiration, '', 'no' );
        } else {
            update_option( $session_option, base64_encode( serialize( $session_data ) ) );
        }

        // Build redirect URL to Step 3 with llm_mode - NO id parameter (matches line 540 in import.php)
        // The session's update_previous value will be used to identify the import
        $redirect_url = add_query_arg( array(
            'page' => 'pmxi-admin-import',
            'action' => 'template',
            'llm_mode' => '1',
        ), admin_url( 'admin.php' ) );

        // Clean up the Step 1 session since import is now created
        $token = $request->get_header( 'X-WPAI-Session-Token' );
        if ( $token ) {
            $user_id = $this->get_user_id_from_token( $token );
            if ( $user_id ) {
                $this->cleanup_session( $user_id );
            }
        }

        return new WP_REST_Response( array(
            'success' => true,
            'import_id' => $import->id,
            'redirect_url' => $redirect_url,
        ), 200 );
    }

    /**
     * Count elements in file matching xpath
     */
    private function count_elements( $file_path, $root_element, $xpath ) {
        $count = 0;

        if ( ! file_exists( $file_path ) ) {
            return $count;
        }

        $file = new PMXI_Chunk( $file_path, array( 'element' => $root_element, 'get_cloud' => false ) );

        while ( $xml = $file->read() ) {
            if ( ! empty( $xml ) ) {
                $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" . $xml;
                $dom = new DOMDocument( '1.0', 'UTF-8' );
                $old = libxml_use_internal_errors( true );
                $dom->loadXML( $xml );
                libxml_use_internal_errors( $old );
                $dxpath = new DOMXPath( $dom );

                if ( ( $elements = @$dxpath->query( $xpath ) ) && $elements->length ) {
                    $count += $elements->length;
                }

                unset( $dom, $dxpath, $elements );
            }
        }

        return $count;
    }

    /**
     * Get the top root element candidates from the most recent detection.
     *
     * @return array Each entry: array( name, depth, count, child_count, child_names )
     */
    public function get_last_root_candidates() {
        return $this->last_root_candidates;
    }

    /**
     * Detect root element from XML file
     *
     * Uses XMLReader directly to scan for the most frequently repeating element.
     * This mirrors WPAI's PMXI_Chunk detection logic but avoids its preprocessxml
     * stream filter which replaces colons with _colon_, breaking namespace URIs
     * and crashing PHP on SOAP/namespaced XML files.
     *
     * XMLReader's localName property strips namespace prefixes automatically,
     * so <soap:Body> is read as "Body" and <SimpleExportKatalogPolozka> stays as-is.
     *
     * @param string $file_path Path to XML file
     * @return string Root element name (defaults to 'node' if not detected)
     */
    private function detect_root_element( $file_path ) {
        if ( ! file_exists( $file_path ) ) {
            return 'node';
        }

        try {
            $reader = new XMLReader();

            // Open file directly (no preprocessxml filter)
            $old = libxml_use_internal_errors( true );
            if ( ! @$reader->open( $file_path ) ) {
                libxml_use_internal_errors( $old );
                return 'node';
            }

            // Build frequency cloud, track minimum depth, and record parent→child
            // relationships so we can score elements by child diversity.
            $cloud        = array();
            $min_depth    = array();
            $children     = array(); // parent_name => array( child_name => true, … )
            $parent_stack = array(); // depth => element name currently open at that depth
            $depth_limit  = 5;

            while ( @$reader->read() ) {
                if ( $reader->nodeType === XMLReader::ELEMENT && $reader->depth <= $depth_limit ) {
                    $name  = $reader->localName;
                    $depth = $reader->depth;
                    if ( ! empty( $name ) ) {
                        // Frequency
                        if ( isset( $cloud[ $name ] ) ) {
                            $cloud[ $name ]++;
                        } else {
                            $cloud[ $name ] = 1;
                        }
                        // Minimum depth
                        if ( ! isset( $min_depth[ $name ] ) || $depth < $min_depth[ $name ] ) {
                            $min_depth[ $name ] = $depth;
                        }
                        // Parent→child tracking
                        if ( $depth > 0 && isset( $parent_stack[ $depth - 1 ] ) ) {
                            $parent = $parent_stack[ $depth - 1 ];
                            if ( ! isset( $children[ $parent ] ) ) {
                                $children[ $parent ] = array();
                            }
                            $children[ $parent ][ $name ] = true;
                        }
                        $parent_stack[ $depth ] = $name;
                    }
                }
            }

            $reader->close();
            libxml_use_internal_errors( $old );

            if ( empty( $cloud ) ) {
                return 'node';
            }

            // Sort by frequency (highest first) - same as PMXI_Chunk
            arsort( $cloud );

            // WPAI's priority list - check if any high-frequency element matches
            $priority = array(
                'node', 'product', 'job', 'deal', 'entry', 'item',
                'property', 'listing', 'hotel', 'record', 'article',
                'post', 'book', 'item_0',
            );

            // First pass: pick highest-frequency element that matches priority list
            foreach ( $cloud as $element => $count ) {
                if ( in_array( strtolower( $element ), $priority, true ) ) {
                    WPAI_Bridge_Logger::debug( '[FileProcessor] Root element detected (priority match): ' . $element, array(
                        'cloud' => $cloud,
                    ));
                    return $element;
                }
            }

            // Second pass: composite scoring.
            // Rank candidates by: child diversity (strongest signal — the record
            // element has the most varied children), record count, and a mild
            // preference for shallower depth.  A single strict depth-first sort
            // fails when different record types live at different depths (e.g.
            // agents at depth 2 with 9 children vs properties at depth 3 with 40).
            //
            // Score = child_count * count / (depth + 1)
            //
            // Elements at depth <= 1 must appear at least 5 times to filter out
            // structural wrappers that repeat a handful of times.
            $candidates = array();
            foreach ( $cloud as $element => $count ) {
                $d         = isset( $min_depth[ $element ] ) ? $min_depth[ $element ] : 0;
                $min_count = ( $d <= 1 ) ? 5 : 2;
                if ( $count >= $min_count ) {
                    $cc = isset( $children[ $element ] ) ? count( $children[ $element ] ) : 0;
                    $candidates[ $element ] = array(
                        'name'        => $element,
                        'count'       => $count,
                        'depth'       => $d,
                        'child_count' => $cc,
                        'score'       => $cc * $count / ( $d + 1 ),
                    );
                }
            }

            if ( ! empty( $candidates ) ) {
                uasort( $candidates, function ( $a, $b ) {
                    // Primary: composite score (higher wins).
                    if ( $a['score'] != $b['score'] ) {
                        return $b['score'] <=> $a['score'];
                    }
                    // Tiebreaker: more children first.
                    return $b['child_count'] <=> $a['child_count'];
                });

                // Store top 5 candidates with metadata for the frontend to evaluate.
                $this->last_root_candidates = array();
                $i = 0;
                foreach ( $candidates as $name => $data ) {
                    if ( $i >= 5 ) {
                        break;
                    }
                    $this->last_root_candidates[] = array(
                        'name'        => $name,
                        'depth'       => $data['depth'],
                        'count'       => $data['count'],
                        'child_count' => $data['child_count'],
                        'child_names' => isset( $children[ $name ] ) ? array_keys( $children[ $name ] ) : array(),
                    );
                    $i++;
                }

                $element = array_key_first( $candidates );
                WPAI_Bridge_Logger::debug( '[FileProcessor] Root element detected (scored): ' . $element, array(
                    'score'       => $candidates[ $element ]['score'],
                    'depth'       => $candidates[ $element ]['depth'],
                    'count'       => $candidates[ $element ]['count'],
                    'child_count' => $candidates[ $element ]['child_count'],
                    'candidates'  => $this->last_root_candidates,
                ));
                return $element;
            }

            // Final fallback: just pick the most frequent (even if count=1)
            $element = array_key_first( $cloud );
            WPAI_Bridge_Logger::debug( '[FileProcessor] Root element detected (fallback): ' . $element, array(
                'cloud' => $cloud,
            ));
            return $element;

        } catch ( Exception $e ) {
            WPAI_Bridge_Logger::warn( '[FileProcessor] Root element detection failed', array(
                'error' => $e->getMessage(),
            ));
        }

        return 'node';
    }
}
