<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Reports what an AI-configured import actually did once it has run.
 *
 * Everything else the bridge sends describes configuring an import. Whether the
 * import then worked is knowable only here, inside WordPress, after a run that
 * often happens hours later on a schedule.
 *
 * Reported once per configuration, on the first run. A configuration that worked
 * was a success; whether a schedule still works months later is about the data or
 * the site rather than the configuration, and cannot be attributed back to it.
 *
 * Two consequences shape this class. The run has no logged-in user, so consent and
 * the session id are captured when the configuration is applied and read back at
 * run time. And a session token is long expired by then, so the report is signed
 * with the shared secret rather than carrying one.
 */
class WPAI_Bridge_Import_Outcome {

    /** Per-import record of an AI configuration, written while a user is present. */
    const MARKER_OPTION_PREFIX = 'wpai_bridge_ai_import_';

    const TIMEOUT = 5;

    private static $instance = null;

    public static function getInstance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Priority 99: after WP All Import has finished writing its own counters to
        // the import record, which is what this reads.
        add_action( 'pmxi_after_xml_import', array( $this, 'report' ), 99, 2 );
        // Otherwise a deleted import leaves its marker in the options table forever.
        add_action( 'pmxi_before_import_delete', array( $this, 'on_import_delete' ), 10, 2 );
    }

    /**
     * Remember that this import was configured by the AI flow.
     *
     * Consent is captured here, not at run time: `wpai_bridge_user_has_consented()`
     * reads the current user, and a scheduled run has none — checking it there would
     * silently drop every scheduled import instead of respecting a decision.
     */
    public static function mark_configured( $import_id, $log_session_id = '', $user_id = 0, $outcome_token = '' ) {
        $import_id = (int) $import_id;
        if ( $import_id <= 0 ) {
            return false;
        }

        $user_id = $user_id ? (int) $user_id : get_current_user_id();
        $consent = $user_id ? get_user_meta( $user_id, '_wpai_bridge_ai_consent', true ) : '';

        if ( empty( $consent ) ) {
            // No consent, no marker, so no report can be produced later.
            return false;
        }

        // Without a token the report cannot be authenticated, so it would be
        // refused. Marking anyway would leave a row that can never be redeemed.
        if ( '' === (string) $outcome_token ) {
            return false;
        }

        return update_option( self::MARKER_OPTION_PREFIX . $import_id, array(
            'log_session_id' => (string) $log_session_id,
            'outcome_token'  => (string) $outcome_token,
            'consented'      => true,
            'user_id'        => $user_id,
            'marked_at'      => time(),
        ), false );
    }

    /**
     * WP All Import passes the import RECORD here, not its id
     * (controllers/admin/manage.php), which is why the signature mirrors the
     * existing listener in the structure cache rather than taking an id.
     */
    public function on_import_delete( $import, $is_delete_posts = false ) {
        if ( is_object( $import ) && ! empty( $import->id ) ) {
            self::forget( $import->id );
        }
    }

    public static function forget( $import_id ) {
        return delete_option( self::MARKER_OPTION_PREFIX . (int) $import_id );
    }

    public function report( $import_id, $import = null ) {
        $marker = get_option( self::MARKER_OPTION_PREFIX . (int) $import_id );
        if ( ! is_array( $marker ) || empty( $marker['consented'] ) || empty( $marker['outcome_token'] ) ) {
            return;
        }

        // Re-checked every run, not trusted from the marker. Consent recorded once
        // and never re-read is consent that cannot be withdrawn: revoking deletes
        // the user meta and would otherwise leave a recurring import reporting
        // forever, which is precisely what the wordpress.org rule forbids. Covers a
        // deleted user for the same reason.
        $consenting_user = isset( $marker['user_id'] ) ? (int) $marker['user_id'] : 0;
        if ( ! $consenting_user || ! get_user_meta( $consenting_user, '_wpai_bridge_ai_consent', true ) ) {
            self::forget( $import_id );
            return;
        }

        if ( ! is_object( $import ) ) {
            return;
        }

        // pmxi_after_xml_import also fires for preview runs. Counting those would
        // inflate every success figure with imports that never really happened.
        if ( ! empty( $import->is_preview ) ) {
            return;
        }

        $url = function_exists( 'pmxi_get_llm_service_url' ) ? pmxi_get_llm_service_url() : '';
        if ( ! $url ) {
            return;
        }
        $endpoint = rtrim( $url, '/' ) . '/api/import-outcome';

        // No site or import id: the token FL issued is the authority for both, so
        // sending them would be asking to be taken at our word for something the
        // service already knows.
        $payload = array(
            'outcome_token'  => (string) $marker['outcome_token'],
            'log_session_id' => $marker['log_session_id'] ? (string) $marker['log_session_id'] : null,
            'bridge_version' => defined( 'WPAI_BRIDGE_VERSION' ) ? WPAI_BRIDGE_VERSION : null,
            'consented'      => true,
            'outcome'        => self::outcome_of( $import ),
        );

        $body = wp_json_encode( array_filter( $payload, static function ( $value ) {
            return null !== $value;
        } ) );

        // Reported once. The question is whether the AI configuration worked, and
        // the first run answers it; a schedule that breaks months later is about the
        // data or the site, not about the configuration, and cannot be attributed
        // back to it. Forgetting here also means the marker cannot outlive its
        // purpose in the options table or keep reporting after consent is withdrawn.
        self::forget( $import_id );

        $headers = array( 'Content-Type' => 'application/json' );
        if ( class_exists( 'WPAI_Bridge_FL_Signer' ) ) {
            $headers = array_merge( $headers, WPAI_Bridge_FL_Signer::headers_for_url( 'POST', $endpoint, $body ) );
        }

        // Non-blocking: an import that has just finished must not wait on, or fail
        // because of, a telemetry call.
        wp_remote_post( $endpoint, array(
            'blocking' => false,
            'timeout'  => self::TIMEOUT,
            'headers'  => $headers,
            'body'     => $body,
        ) );
    }

    /**
     * The counters WP All Import wrote on the import record.
     *
     * Read from the record rather than from the history table: history stores a
     * free-text summary, and only the record carries the numbers.
     */
    private static function outcome_of( $import ) {
        // No duration. WP All Import resets registered_on to the current time
        // immediately before firing this hook (models/import/record.php and
        // controllers/admin/import.php both do it), so last_activity is never later
        // than it and any elapsed time computed here is exactly zero. A field that
        // always reports 0 is worse than an absent one — it reads as a measurement.
        return array(
            'total_records'   => isset( $import->count ) ? (int) $import->count : 0,
            'created'         => isset( $import->created ) ? (int) $import->created : 0,
            'updated'         => isset( $import->updated ) ? (int) $import->updated : 0,
            'skipped'         => isset( $import->skipped ) ? (int) $import->skipped : 0,
            'deleted'         => isset( $import->deleted ) ? (int) $import->deleted : 0,
            'changed_missing' => isset( $import->changed_missing ) ? (int) $import->changed_missing : 0,
            'imported'        => isset( $import->imported ) ? (int) $import->imported : 0,
            'failed'          => ! empty( $import->failed ),
            'canceled'        => ! empty( $import->canceled ),
        );
    }
}
