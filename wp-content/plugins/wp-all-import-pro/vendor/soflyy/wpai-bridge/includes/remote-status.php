<?php
/**
 * Whether the Automatic Setup service is currently offering the feature.
 *
 * A verdict is fetched on cron and stored with an expiry, so it decays on its own
 * and a site that cannot reach the service keeps working unchanged.
 *
 * Lives with the gate rather than the SDK: the answer is needed on installs where
 * the SDK never loads.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'WPAI_BRIDGE_STATUS_OPTION' ) ) {
    define( 'WPAI_BRIDGE_STATUS_OPTION', 'wpai_bridge_remote_status' );
}
if ( ! defined( 'WPAI_BRIDGE_STATUS_EVENT' ) ) {
    define( 'WPAI_BRIDGE_STATUS_EVENT', 'wpai_bridge_poll_remote_status' );
}

// Several poll intervals, so one missed window cannot expire a verdict.
if ( ! defined( 'WPAI_BRIDGE_STATUS_TTL' ) ) {
    define( 'WPAI_BRIDGE_STATUS_TTL', 3 * DAY_IN_SECONDS );
}

if ( ! defined( 'WPAI_BRIDGE_STATUS_RETRY_AFTER' ) ) {
    define( 'WPAI_BRIDGE_STATUS_RETRY_AFTER', HOUR_IN_SECONDS );
}

if ( ! defined( 'WPAI_BRIDGE_DEFAULT_SERVICE_URL' ) ) {
    define( 'WPAI_BRIDGE_DEFAULT_SERVICE_URL', 'https://wpai-frontend-layer.vercel.app' );
}

if ( ! function_exists( 'wpai_bridge_get_llm_service_url' ) ) {
    /**
     * Defined here rather than in the SDK so that it resolves the same way whether
     * or not the SDK is loaded — the status poll runs in both states.
     *
     * @return string
     */
    function wpai_bridge_get_llm_service_url() {
        $env_file = defined( 'WPAI_BRIDGE_DIR' ) ? WPAI_BRIDGE_DIR . '.env' : __DIR__ . '/../.env';

        if ( file_exists( $env_file ) ) {
            $env_contents = file_get_contents( $env_file );
            if ( false !== $env_contents ) {
                foreach ( explode( "\n", $env_contents ) as $line ) {
                    $line = trim( $line );
                    if ( '' === $line || 0 === strpos( $line, '#' ) ) {
                        continue;
                    }
                    if ( 0 === strpos( $line, 'WPAI_LLM_SERVICE_URL=' ) ) {
                        $url = trim( trim( substr( $line, strlen( 'WPAI_LLM_SERVICE_URL=' ) ) ), '"\'' );
                        if ( '' !== $url ) {
                            return $url;
                        }
                    }
                }
            }
        }

        return WPAI_BRIDGE_DEFAULT_SERVICE_URL;
    }
}

/**
 * @return string One of 'enabled', 'paused', 'disabled'.
 */
function wpai_bridge_remote_status() {
    $stored = get_option( WPAI_BRIDGE_STATUS_OPTION );

    if ( ! is_array( $stored ) || empty( $stored['expires'] ) || $stored['expires'] < time() ) {
        return 'enabled';
    }

    $status = isset( $stored['status'] ) ? $stored['status'] : '';

    return in_array( $status, array( 'enabled', 'paused', 'disabled' ), true ) ? $status : 'enabled';
}

/** Whether our service is currently offering the feature at all. */
function wpai_bridge_remote_status_is_serving() {
    return 'enabled' === wpai_bridge_remote_status();
}

/** Whether anything — a verdict or a recent failure — has been recorded yet. */
function wpai_bridge_remote_status_is_known() {
    $stored = get_option( WPAI_BRIDGE_STATUS_OPTION );

    return is_array( $stored ) && ! empty( $stored['expires'] ) && $stored['expires'] >= time();
}

// Autoloaded: the gate reads this on every request.
function wpai_bridge_store_remote_status( $status, $ttl ) {
    update_option(
        WPAI_BRIDGE_STATUS_OPTION,
        array(
            'status'  => $status,
            'expires' => time() + (int) $ttl,
        ),
        true
    );
}

/** Ask the service what it is doing and record the answer. Runs on cron. */
function wpai_bridge_poll_remote_status() {
    $url = trailingslashit( wpai_bridge_get_llm_service_url() ) . 'api/status';

    $response = wp_remote_get(
        $url,
        array(
            'timeout'     => 5,
            'redirection' => 0,
            'headers'     => array(
                'Accept'        => 'application/json',
                // An intermediate proxy must not answer with a stale verdict.
                'Cache-Control' => 'no-cache',
            ),
        )
    );

    $feature = null;

    if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( is_array( $body ) && isset( $body['feature'] )
            && in_array( $body['feature'], array( 'enabled', 'paused', 'disabled' ), true ) ) {
            $feature = $body['feature'];
        }
    }

    if ( null === $feature ) {
        // An unreadable answer leaves any existing verdict alone. Recording a
        // placeholder when there is none stops a site that cannot reach the
        // service from re-priming the poll on every admin request.
        if ( ! wpai_bridge_remote_status_is_known() ) {
            wpai_bridge_store_remote_status( 'enabled', WPAI_BRIDGE_STATUS_RETRY_AFTER );
        }
        return;
    }

    wpai_bridge_store_remote_status( $feature, WPAI_BRIDGE_STATUS_TTL );
}

/** Keep the poll scheduled. Admin only — it exists to drive admin UI. */
function wpai_bridge_schedule_status_poll() {
    if ( wp_next_scheduled( WPAI_BRIDGE_STATUS_EVENT ) ) {
        return;
    }

    // Spread across the hour so every install does not poll at once. abs()
    // because crc32 is signed on 32-bit PHP.
    $jitter = abs( crc32( (string) get_site_url() ) ) % HOUR_IN_SECONDS;

    wp_schedule_event( time() + $jitter, 'twicedaily', WPAI_BRIDGE_STATUS_EVENT );
}

/**
 * Bring the first poll forward on a site that has no verdict yet, rather than
 * waiting up to a full schedule for one.
 */
function wpai_bridge_prime_status_poll() {
    if ( wpai_bridge_remote_status_is_known() ) {
        return;
    }

    $next = wp_next_scheduled( WPAI_BRIDGE_STATUS_EVENT );

    // false when nothing is scheduled, which is the case that most needs priming.
    if ( ! $next || $next > ( time() + MINUTE_IN_SECONDS ) ) {
        wp_schedule_single_event( time(), WPAI_BRIDGE_STATUS_EVENT );
    }
}

add_action( WPAI_BRIDGE_STATUS_EVENT, 'wpai_bridge_poll_remote_status' );
