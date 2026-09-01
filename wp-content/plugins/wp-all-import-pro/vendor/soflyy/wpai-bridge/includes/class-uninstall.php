<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Removes everything the bridge persisted. Callable directly because an embedded
 * SDK has no uninstall.php of its own — the host plugin runs this from its own
 * uninstall routine.
 */
class WPAI_Bridge_Uninstall {

    public static function run() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpai_bridge_file_cache';
        $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

        // Literals, not the constants: uninstall.php runs with plugins unloaded.
        wp_clear_scheduled_hook( 'wpai_bridge_poll_remote_status' );
        delete_option( 'wpai_bridge_remote_status' );

        $wpai_bridge_options = array(
            'wpai_bridge_file_cache_db_version',
            'wpai_bridge_corpus_capture_seen',
            'wpai_bridge_debug',
            'wpai_bridge_shared_secret',
        );

        foreach ( $wpai_bridge_options as $wpai_bridge_option ) {
            delete_option( $wpai_bridge_option );
        }

        // The _wpallimport_session_* and wpai_preview_session_* keys the bridge writes to
        // belong to WP All Import core's wizard-session and preview stores, which core
        // reads, writes and expires itself. They are deliberately left alone.
        $wpai_bridge_option_prefixes = array(
            'wpai_llm_original_template_',
            'wpai_llm_session_',
            'wpai_step1_session_',
            'wpai_bridge_ai_import_',
        );

        foreach ( $wpai_bridge_option_prefixes as $wpai_bridge_prefix ) {
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                    $wpdb->esc_like( $wpai_bridge_prefix ) . '%'
                )
            );
        }

        $wpai_bridge_transient_prefixes = array(
            'wpai_bridge_session_error_',
            'wpai_template_fields_',
        );

        foreach ( $wpai_bridge_transient_prefixes as $wpai_bridge_prefix ) {
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                    $wpdb->esc_like( '_transient_' . $wpai_bridge_prefix ) . '%',
                    $wpdb->esc_like( '_transient_timeout_' . $wpai_bridge_prefix ) . '%'
                )
            );
        }

        delete_metadata( 'user', 0, '_wpai_bridge_ai_consent', '', true );

        $wpai_bridge_post_meta_keys = array(
            '_wpai_llm_configured',
            '_wpai_llm_configuring',
            '_wpai_llm_config_token',
        );

        foreach ( $wpai_bridge_post_meta_keys as $wpai_bridge_meta_key ) {
            delete_metadata( 'post', 0, $wpai_bridge_meta_key, '', true );
        }
    }
}
