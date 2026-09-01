<?php
/**
 * Uninstall handler.
 *
 * WP All Import's own data is deliberately left alone — imports, history and
 * settings are not disposable, and deleting the plugin is not a request to
 * discard them. Only bundled components that own transient state clean up here.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// The AI Bridge SDK ships inside this plugin and has no uninstall of its own:
// its cache table and options are rebuildable, so they go when the host does.
$wpai_bridge_uninstall = __DIR__ . '/vendor/soflyy/wpai-bridge/includes/class-uninstall.php';
if ( file_exists( $wpai_bridge_uninstall ) ) {
    require_once $wpai_bridge_uninstall;
    WPAI_Bridge_Uninstall::run();
}
