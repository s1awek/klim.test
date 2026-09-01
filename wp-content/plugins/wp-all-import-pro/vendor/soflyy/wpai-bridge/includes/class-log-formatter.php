<?php
/**
 * Log Formatter - Abstraction layer for import log format conversion
 *
 * Handles conversion between HTML and JSON log formats, providing flexibility
 * for storage and retrieval in different contexts (admin UI vs API).
 *
 * @package WPAI_Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPAI_Bridge_Log_Formatter {

    /**
     * Log entry structure
     */
    const ENTRY_KEYS = array(
        'timestamp',
        'message',
        'type',      // info, warning, error, success
        'context',   // Optional additional data
    );

    /**
     * Parse HTML log content into structured JSON array
     *
     * @param string $html_content Raw HTML log content
     * @return array Array of structured log entries
     */
    public static function html_to_json( $html_content ) {
        if ( empty( $html_content ) ) {
            return array();
        }

        $entries = array();

        // Match progress-msg divs (standard WP All Import AJAX format)
        // Format: <div class='progress-msg'>[HH:MM:SS] message</div>
        if ( preg_match_all( '/<div[^>]*class=[\'"]progress-msg[\'"][^>]*>(.*?)<\/div>/is', $html_content, $matches ) ) {
            foreach ( $matches[1] as $message ) {
                $entry = self::parse_log_message( $message );
                if ( $entry ) {
                    $entries[] = $entry;
                }
            }
        }

        // Also match <p> tags (used by cron/scheduled imports)
        // Format: <p>[HH:MM:SS] message</p>
        if ( preg_match_all( '/<p[^>]*>(.*?)<\/p>/is', $html_content, $p_matches ) ) {
            foreach ( $p_matches[1] as $message ) {
                $entry = self::parse_log_message( $message );
                if ( $entry ) {
                    $entries[] = $entry;
                }
            }
        }

        // Fallback: try to match plain text lines if no structured entries found
        if ( empty( $entries ) ) {
            $lines = preg_split( '/\r?\n/', $html_content );
            foreach ( $lines as $line ) {
                $line = strip_tags( trim( $line ) );
                if ( ! empty( $line ) ) {
                    $entry = self::parse_log_message( $line );
                    if ( $entry ) {
                        $entries[] = $entry;
                    }
                }
            }
        }

        return $entries;
    }

    /**
     * Parse a single log message into structured format
     *
     * @param string $message Raw message (may contain HTML, timestamp prefix)
     * @return array|null Structured entry or null if empty
     */
    public static function parse_log_message( $message ) {
        // Strip HTML but preserve important formatting hints
        $has_bold = strpos( $message, '<b>' ) !== false || strpos( $message, '<strong>' ) !== false;
        $has_error = stripos( $message, 'error' ) !== false || stripos( $message, 'failed' ) !== false;
        $has_warning = stripos( $message, 'warning' ) !== false || stripos( $message, 'skipped' ) !== false;
        $has_success = stripos( $message, 'success' ) !== false || stripos( $message, 'complete' ) !== false;

        // Clean the message
        $clean_message = strip_tags( $message );
        $clean_message = html_entity_decode( $clean_message, ENT_QUOTES, 'UTF-8' );
        $clean_message = trim( $clean_message );

        if ( empty( $clean_message ) ) {
            return null;
        }

        // Extract timestamp if present [HH:MM:SS] or [YYYY-MM-DD HH:MM:SS]
        $timestamp = null;
        if ( preg_match( '/^\[(\d{2}:\d{2}:\d{2})\]\s*(.*)$/s', $clean_message, $time_match ) ) {
            $timestamp = $time_match[1];
            $clean_message = trim( $time_match[2] );
        } elseif ( preg_match( '/^\[(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\]\s*(.*)$/s', $clean_message, $datetime_match ) ) {
            $timestamp = $datetime_match[1];
            $clean_message = trim( $datetime_match[2] );
        }

        // Determine entry type
        $type = 'info';
        if ( $has_error ) {
            $type = 'error';
        } elseif ( $has_warning ) {
            $type = 'warning';
        } elseif ( $has_success ) {
            $type = 'success';
        } elseif ( $has_bold ) {
            $type = 'header';
        }

        // Extract context from message patterns
        $context = self::extract_context( $clean_message );

        return array(
            'timestamp' => $timestamp,
            'message'   => $clean_message,
            'type'      => $type,
            'context'   => $context,
        );
    }

    /**
     * Extract structured context from log message patterns
     *
     * @param string $message Clean message text
     * @return array Context data extracted from message
     */
    public static function extract_context( $message ) {
        $context = array();

        // Record number pattern: "Record #123" or "record 123"
        if ( preg_match( '/record\s*#?(\d+)/i', $message, $match ) ) {
            $context['record_number'] = (int) $match[1];
        }

        // Processing range: "Processing records 1 - 20"
        if ( preg_match( '/processing\s+records?\s+(\d+)\s*[-–]\s*(\d+)/i', $message, $match ) ) {
            $context['range_start'] = (int) $match[1];
            $context['range_end'] = (int) $match[2];
        }

        // Post ID pattern: "Post ID: 123" or "post #123" or "created post 123"
        if ( preg_match( '/post\s*(?:id[:\s]*)?#?(\d+)/i', $message, $match ) ) {
            $context['post_id'] = (int) $match[1];
        }

        // SKU pattern for WooCommerce
        if ( preg_match( '/sku[:\s]+[\'"]?([^\'">\s,]+)/i', $message, $match ) ) {
            $context['sku'] = $match[1];
        }

        // Image/attachment patterns
        if ( preg_match( '/image|attachment|media/i', $message ) ) {
            $context['category'] = 'media';
        }

        // Taxonomy patterns
        if ( preg_match( '/(?:category|tag|term|taxonomy)/i', $message ) ) {
            $context['category'] = 'taxonomy';
        }

        // Custom field patterns
        if ( preg_match( '/custom\s+field|meta|acf/i', $message ) ) {
            $context['category'] = 'custom_field';
        }

        return $context;
    }

    /**
     * Convert structured JSON log entries back to HTML format
     *
     * @param array $entries Array of structured log entries
     * @param array $options Formatting options
     * @return string HTML formatted log content
     */
    public static function json_to_html( $entries, $options = array() ) {
        $defaults = array(
            'wrapper_class' => 'progress-msg',
            'include_timestamp' => true,
            'colorize' => true,
        );
        $options = wp_parse_args( $options, $defaults );

        $html = '';

        foreach ( $entries as $entry ) {
            $message = isset( $entry['message'] ) ? esc_html( $entry['message'] ) : '';

            // Add timestamp prefix
            if ( $options['include_timestamp'] && ! empty( $entry['timestamp'] ) ) {
                $message = '[' . esc_html( $entry['timestamp'] ) . '] ' . $message;
            }

            // Apply type-based formatting
            $type = isset( $entry['type'] ) ? $entry['type'] : 'info';
            if ( $options['colorize'] ) {
                switch ( $type ) {
                    case 'header':
                        $message = '<b>' . $message . '</b>';
                        break;
                    case 'error':
                        $message = '<span style="color: #dc3545;">' . $message . '</span>';
                        break;
                    case 'warning':
                        $message = '<span style="color: #ffc107;">' . $message . '</span>';
                        break;
                    case 'success':
                        $message = '<span style="color: #28a745;">' . $message . '</span>';
                        break;
                }
            }

            $html .= sprintf(
                '<div class="%s">%s</div>' . "\n",
                esc_attr( $options['wrapper_class'] ),
                $message
            );
        }

        return $html;
    }

    /**
     * Create a structured log entry
     *
     * @param string $message Log message
     * @param string $type Entry type (info, warning, error, success, header)
     * @param array  $context Optional context data
     * @return array Structured log entry
     */
    public static function create_entry( $message, $type = 'info', $context = array() ) {
        return array(
            'timestamp' => date( 'H:i:s' ),
            'message'   => $message,
            'type'      => $type,
            'context'   => $context,
        );
    }

    /**
     * Read and parse a log file
     *
     * @param string $file_path Path to log file
     * @param string $format Output format ('json' or 'html')
     * @return array|string Parsed entries or raw HTML
     */
    public static function read_log_file( $file_path, $format = 'json' ) {
        if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
            return $format === 'json' ? array() : '';
        }

        $content = file_get_contents( $file_path );

        if ( $format === 'html' ) {
            return $content;
        }

        return self::html_to_json( $content );
    }

    /**
     * Get the log file path for a history record
     *
     * @param int $history_id History record ID
     * @return string|false File path or false if not found
     */
    public static function get_log_file_path( $history_id ) {
        $uploads = wp_upload_dir();

        // Try secure file path first
        if ( function_exists( 'wp_all_import_secure_file' ) ) {
            $secure_path = wp_all_import_secure_file(
                $uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::LOGS_DIRECTORY,
                $history_id,
                false,
                false
            );
            $file_path = $secure_path . DIRECTORY_SEPARATOR . $history_id . '.html';

            if ( file_exists( $file_path ) ) {
                return $file_path;
            }
        }

        // Fallback to standard path
        $file_path = $uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::LOGS_DIRECTORY . DIRECTORY_SEPARATOR . $history_id . DIRECTORY_SEPARATOR . $history_id . '.html';

        if ( file_exists( $file_path ) ) {
            return $file_path;
        }

        return false;
    }

    /**
     * Get import history records for an import
     *
     * @param int   $import_id Import ID
     * @param array $args Query arguments
     * @return array Array of history records with metadata
     */
    public static function get_history_records( $import_id, $args = array() ) {
        global $wpdb;

        $defaults = array(
            'limit'    => 20,
            'offset'   => 0,
            'order_by' => 'date',
            'order'    => 'DESC',
        );
        $args = wp_parse_args( $args, $defaults );

        $table = $wpdb->prefix . 'pmxi_history';

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT id, import_id, type, time_run, date, summary
             FROM {$table}
             WHERE import_id = %d
             ORDER BY {$args['order_by']} {$args['order']}
             LIMIT %d OFFSET %d",
            $import_id,
            $args['limit'],
            $args['offset']
        ), ARRAY_A );

        if ( ! $results ) {
            return array();
        }

        // Enrich with file availability info
        foreach ( $results as &$record ) {
            $file_path = self::get_log_file_path( $record['id'] );
            $record['has_log_file'] = $file_path !== false;
            $record['log_file_size'] = $file_path ? filesize( $file_path ) : 0;
        }

        return $results;
    }

    /**
     * Get the total count of history records for an import
     *
     * @param int $import_id Import ID
     * @return int Total count
     */
    public static function get_history_count( $import_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'pmxi_history';

        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE import_id = %d",
            $import_id
        ) );
    }

    /**
     * Filter entries by type
     *
     * @param array        $entries Log entries
     * @param string|array $types Type(s) to include
     * @return array Filtered entries
     */
    public static function filter_by_type( $entries, $types ) {
        if ( ! is_array( $types ) ) {
            $types = array( $types );
        }

        return array_filter( $entries, function( $entry ) use ( $types ) {
            return isset( $entry['type'] ) && in_array( $entry['type'], $types, true );
        } );
    }

    /**
     * Search entries by message content
     *
     * @param array  $entries Log entries
     * @param string $search Search term
     * @return array Matching entries
     */
    public static function search_entries( $entries, $search ) {
        $search = strtolower( $search );

        return array_filter( $entries, function( $entry ) use ( $search ) {
            return isset( $entry['message'] ) && strpos( strtolower( $entry['message'] ), $search ) !== false;
        } );
    }

    /**
     * Get summary statistics from log entries
     *
     * @param array $entries Log entries
     * @return array Summary statistics
     */
    public static function get_summary( $entries ) {
        $summary = array(
            'total'    => count( $entries ),
            'info'     => 0,
            'header'   => 0,
            'warning'  => 0,
            'error'    => 0,
            'success'  => 0,
            'records_mentioned' => array(),
        );

        foreach ( $entries as $entry ) {
            $type = isset( $entry['type'] ) ? $entry['type'] : 'info';
            if ( isset( $summary[ $type ] ) ) {
                $summary[ $type ]++;
            }

            // Track mentioned record numbers
            if ( ! empty( $entry['context']['record_number'] ) ) {
                $summary['records_mentioned'][] = $entry['context']['record_number'];
            }
        }

        $summary['records_mentioned'] = array_unique( $summary['records_mentioned'] );
        sort( $summary['records_mentioned'] );

        return $summary;
    }
}
