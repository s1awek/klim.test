<?php
/**
 * Logger for AI Bridge for WP All Import
 *
 * Provides environment-gated logging. Logs are OFF by default.
 *
 * Enable logging by adding one of:
 * - define('WPAI_BRIDGE_DEBUG', true); in wp-config.php
 * - WPAI_BRIDGE_DEBUG=true in .env
 * - Set 'wpai_bridge_debug' option to '1' in database
 *
 * @package AI_Bridge_For_WP_All_Import
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WPAI_Bridge_Logger
 *
 * Centralized logging with environment-based gating.
 */
class WPAI_Bridge_Logger {

    /**
     * Whether logging is enabled (cached after first check).
     *
     * @var bool|null
     */
    private static $enabled = null;

    /**
     * Active performance timers.
     *
     * @var array
     */
    private static $timers = array();

    /**
     * Check if logging is enabled.
     *
     * @return bool
     */
    public static function is_enabled() {
        if ( self::$enabled !== null ) {
            return self::$enabled;
        }

        // Check constant first (wp-config.php)
        if ( defined( 'WPAI_BRIDGE_DEBUG' ) && WPAI_BRIDGE_DEBUG ) {
            self::$enabled = true;
            return true;
        }

        // Check server environment variable
        $env_debug = getenv( 'WPAI_BRIDGE_DEBUG' );
        if ( $env_debug === 'true' || $env_debug === '1' ) {
            self::$enabled = true;
            return true;
        }

        // Check .env file in plugin directory
        $env_file_value = self::get_env_file_value( 'WPAI_BRIDGE_DEBUG' );
        if ( $env_file_value === 'true' || $env_file_value === '1' ) {
            self::$enabled = true;
            return true;
        }

        // Check WordPress option (for runtime toggling via admin)
        $option_debug = get_option( 'wpai_bridge_debug', false );
        if ( $option_debug === '1' || $option_debug === true ) {
            self::$enabled = true;
            return true;
        }

        // Default: logging disabled
        self::$enabled = false;
        return false;
    }

    /**
     * Read a value from the plugin's .env file.
     *
     * @param string $key The environment variable name to look for.
     * @return string|null The value if found, null otherwise.
     */
    private static function get_env_file_value( $key ) {
        static $env_cache = null;

        // Parse .env file once and cache
        if ( $env_cache === null ) {
            $env_cache = array();

            // Check plugin directory first, then WordPress root
            $env_paths = array(
                WPAI_BRIDGE_DIR . '.env',
                ABSPATH . '.env',
            );

            foreach ( $env_paths as $env_path ) {
                if ( file_exists( $env_path ) && is_readable( $env_path ) ) {
                    $lines = file( $env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
                    if ( $lines === false ) {
                        continue;
                    }

                    foreach ( $lines as $line ) {
                        // Skip comments
                        $line = trim( $line );
                        if ( empty( $line ) || strpos( $line, '#' ) === 0 ) {
                            continue;
                        }

                        // Parse KEY=value
                        if ( strpos( $line, '=' ) !== false ) {
                            list( $name, $value ) = explode( '=', $line, 2 );
                            $name  = trim( $name );
                            $value = trim( $value );

                            // Remove quotes if present
                            $value = trim( $value, '"' );
                            $value = trim( $value, "'" );

                            $env_cache[ $name ] = $value;
                        }
                    }
                    break; // Use first .env file found
                }
            }
        }

        return isset( $env_cache[ $key ] ) ? $env_cache[ $key ] : null;
    }

    /**
     * Reset the enabled cache (useful for testing or after option changes).
     */
    public static function reset_cache() {
        self::$enabled = null;
    }

    /**
     * Log a debug message.
     *
     * @param string $message The message to log.
     * @param array  $context Optional context data.
     */
    public static function debug( $message, $context = array() ) {
        self::log( 'DEBUG', $message, $context );
    }

    /**
     * Log an info message.
     *
     * @param string $message The message to log.
     * @param array  $context Optional context data.
     */
    public static function info( $message, $context = array() ) {
        self::log( 'INFO', $message, $context );
    }

    /**
     * Log a warning message.
     *
     * @param string $message The message to log.
     * @param array  $context Optional context data.
     */
    public static function warn( $message, $context = array() ) {
        self::log( 'WARN', $message, $context );
    }

    /**
     * Log an error message.
     *
     * @param string $message The message to log.
     * @param array  $context Optional context data.
     */
    public static function error( $message, $context = array() ) {
        self::log( 'ERROR', $message, $context );
    }

    /**
     * Internal log method.
     *
     * @param string $level   Log level.
     * @param string $message The message to log.
     * @param array  $context Optional context data.
     */
    private static function log( $level, $message, $context = array() ) {
        if ( ! self::is_enabled() ) {
            return;
        }

        $prefix = "[AI Bridge][$level]";

        // Format context if provided
        $context_str = '';
        if ( ! empty( $context ) ) {
            $context_str = ' ' . wp_json_encode( $context, JSON_UNESCAPED_SLASHES );
        }

        // Use WordPress error_log which respects WP_DEBUG_LOG
        error_log( $prefix . ' ' . $message . $context_str );
    }

    /**
     * Log a separator line (for visual grouping in logs).
     *
     * @param string $title Optional title for the separator.
     */
    public static function separator( $title = '' ) {
        if ( ! self::is_enabled() ) {
            return;
        }

        $line = str_repeat( '=', 50 );
        if ( $title ) {
            error_log( $line );
            error_log( "[AI Bridge] $title" );
            error_log( $line );
        } else {
            error_log( $line );
        }
    }

    /**
     * Start a performance timer.
     *
     * @param string $name Unique name for this timer.
     */
    public static function perf_start( $name ) {
        if ( ! self::is_enabled() ) {
            return;
        }

        self::$timers[ $name ] = array(
            'start_time' => microtime( true ),
            'start_mem'  => memory_get_usage( true ),
        );
    }

    /**
     * End a performance timer and log the results.
     *
     * @param string $name    Timer name (must match perf_start).
     * @param array  $context Optional additional context to log.
     * @return array|null Performance data array or null if timer not found/logging disabled.
     */
    public static function perf_end( $name, $context = array() ) {
        if ( ! self::is_enabled() ) {
            return null;
        }

        if ( ! isset( self::$timers[ $name ] ) ) {
            self::warn( "Performance timer '$name' was never started." );
            return null;
        }

        $timer = self::$timers[ $name ];
        unset( self::$timers[ $name ] );

        $elapsed_ms = round( ( microtime( true ) - $timer['start_time'] ) * 1000, 2 );
        $peak_mem   = memory_get_peak_usage( true );
        $mem_used   = round( ( $peak_mem - $timer['start_mem'] ) / 1024 / 1024, 2 );
        $peak_mb    = round( $peak_mem / 1024 / 1024, 2 );

        $perf_data = array(
            'elapsed_ms' => $elapsed_ms,
            'mem_delta_mb' => $mem_used,
            'peak_mem_mb' => $peak_mb,
        );

        $message = sprintf(
            '[PERF] %s: %sms | Memory: +%sMB (peak: %sMB)',
            $name,
            $elapsed_ms,
            $mem_used,
            $peak_mb
        );

        if ( ! empty( $context ) ) {
            $message .= ' | ' . wp_json_encode( $context, JSON_UNESCAPED_SLASHES );
        }

        error_log( "[AI Bridge] $message" );

        return $perf_data;
    }

    /**
     * Log a performance measurement inline (without start/end).
     *
     * @param string $name     Description of what was measured.
     * @param float  $start    Start time from microtime(true).
     * @param int    $start_mem Start memory from memory_get_usage(true).
     * @param array  $context  Optional additional context.
     * @return array Performance data array.
     */
    public static function perf_log( $name, $start, $start_mem = 0, $context = array() ) {
        $elapsed_ms = round( ( microtime( true ) - $start ) * 1000, 2 );
        $peak_mem   = memory_get_peak_usage( true );
        $mem_used   = $start_mem > 0 ? round( ( $peak_mem - $start_mem ) / 1024 / 1024, 2 ) : 0;
        $peak_mb    = round( $peak_mem / 1024 / 1024, 2 );

        $perf_data = array(
            'elapsed_ms' => $elapsed_ms,
            'mem_delta_mb' => $mem_used,
            'peak_mem_mb' => $peak_mb,
        );

        if ( ! self::is_enabled() ) {
            return $perf_data;
        }

        $message = sprintf(
            '[PERF] %s: %sms | Memory: +%sMB (peak: %sMB)',
            $name,
            $elapsed_ms,
            $mem_used,
            $peak_mb
        );

        if ( ! empty( $context ) ) {
            $message .= ' | ' . wp_json_encode( $context, JSON_UNESCAPED_SLASHES );
        }

        error_log( "[AI Bridge] $message" );

        return $perf_data;
    }
}
