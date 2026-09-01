<?php
/**
 * Helper functions for WP All Import LLM Bridge Plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// wpai_bridge_get_llm_service_url() lives in remote-status.php, which the gate
// loads before the SDK.
require_once __DIR__ . '/remote-status.php';

// Backward compatibility: keep the old function name as an alias
if ( ! function_exists('pmxi_get_llm_service_url') ) {
    function pmxi_get_llm_service_url() {
        return wpai_bridge_get_llm_service_url();
    }
}


/**
 * Send CORS headers for LLM service requests
 * 
 * Allows the LLM service (Vercel app) to make cross-origin requests to WordPress
 */
function wpai_bridge_send_cors_headers() {
    // Only send CORS headers for REST API requests
    if (!defined('REST_REQUEST') || !REST_REQUEST) {
        return;
    }

    // Get the origin from the request
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

    // Get allowed origin from .env file
    $allowed_url = wpai_bridge_get_llm_service_url();
    $allowed_origin = rtrim($allowed_url, '/');

    // Check if the request origin matches the configured LLM service URL
    if ($origin === $allowed_origin) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-WP-Nonce, Authorization, X-WPAI-Session-Token, X-Session-Token');
        header('Access-Control-Allow-Credentials: true');
    }

    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        status_header(200);
        exit;
    }
}
add_action('rest_api_init', 'wpai_bridge_send_cors_headers', 1);

/**
 * User consent functions for AI data processing
 *
 * Stores consent per-user in user meta to track whether each user has agreed
 * to send import file data to the external AI service for processing.
 */

/**
 * Check if the current user has consented to AI data processing
 *
 * @return bool True if user has consented, false otherwise
 */
function wpai_bridge_user_has_consented() {
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        return false;
    }

    $consent = get_user_meta( $user_id, '_wpai_bridge_ai_consent', true );
    return ! empty( $consent );
}

/**
 * Record user consent for AI data processing
 *
 * @return bool True on success, false on failure
 */
function wpai_bridge_record_user_consent() {
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        return false;
    }

    return update_user_meta( $user_id, '_wpai_bridge_ai_consent', array(
        'consented' => true,
        'timestamp' => current_time( 'timestamp' ),
        'version'   => WPAI_BRIDGE_VERSION,
    ) );
}

/**
 * Revoke user consent for AI data processing
 *
 * @return bool True on success, false on failure
 */
function wpai_bridge_revoke_user_consent() {
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        return false;
    }

    return delete_user_meta( $user_id, '_wpai_bridge_ai_consent' );
}

function wpai_bridge_is_spurious_log_line( $line ) {
	return (bool) preg_match( '/String expected as argument/i', (string) $line );
}
