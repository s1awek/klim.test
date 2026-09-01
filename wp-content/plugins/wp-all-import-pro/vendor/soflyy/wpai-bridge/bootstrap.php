<?php
/**
 * AI Bridge SDK entry point.
 *
 * Hosts require gate.php, not this file: the SDK is opt-in and gate.php owns the
 * setting that decides whether this runs at all. Requiring this directly loads
 * the SDK unconditionally, which is only correct in a test bootstrap.
 *
 * Loaded at file scope by the host, before PMXI_Plugin is instantiated — see
 * README.md for why that ordering is load-bearing.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// First copy wins. Only one copy should exist in a normal install; this makes a
// second one inert rather than fatal (duplicate class declarations). Define
// WPAI_BRIDGE_SDK_DIR before the first require to pin a specific copy instead.
if ( defined( 'WPAI_BRIDGE_SDK_LOADED' ) ) {
    return;
}
define( 'WPAI_BRIDGE_SDK_LOADED', true );

$wpai_bridge_sdk_dir = defined( 'WPAI_BRIDGE_SDK_DIR' )
    ? trailingslashit( WPAI_BRIDGE_SDK_DIR )
    : trailingslashit( __DIR__ );

/**
 * Registered here, at load time, rather than from the loader: WP All Import
 * decides inside PMXI_Plugin's constructor whether to load its libraries, and the
 * bridge's REST namespace needs them. A filter added later never runs.
 *
 * The mcp/mcp-adapter-default-server marker is for the MCP surface, whose tools
 * call PMXI_* classes in-process on that endpoint.
 */
add_filter( 'pmxi_is_admin_dashboard_or_cron_import', function ( $is_admin_or_cron ) {
    if ( $is_admin_or_cron ) {
        return $is_admin_or_cron;
    }

    $request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
    $rest_route   = isset( $_GET['rest_route'] ) ? (string) $_GET['rest_route'] : '';
    $rest_markers = apply_filters(
        'wpai_bridge_force_wpai_load_uri_markers',
        array( 'wp-all-import/v1', 'mcp/mcp-adapter-default-server' )
    );

    foreach ( (array) $rest_markers as $marker ) {
        if ( '' !== $marker && ( false !== strpos( $request_uri, $marker ) || false !== strpos( $rest_route, $marker ) ) ) {
            return true;
        }
    }

    return $is_admin_or_cron;
}, 10, 1 );

// Lets the LLM auto-configuration flow drive step 3 directly.
add_filter( 'pmxi_step_ready_bypass', function ( $bypass, $action, $update_previous ) {
    if ( 'template' === $action && ( ! empty( $_GET['llm_mode'] ) || ! empty( $_GET['llm_success'] ) ) ) {
        return true;
    }
    return $bypass;
}, 10, 3 );

require_once $wpai_bridge_sdk_dir . 'includes/class-loader.php';
WPAI_Bridge_Loader::register( null, $wpai_bridge_sdk_dir );
unset( $wpai_bridge_sdk_dir );
