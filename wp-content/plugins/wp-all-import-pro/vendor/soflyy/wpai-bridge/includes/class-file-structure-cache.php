<?php
/**
 * File Structure Cache - Hooks into WPAI upload process for early structure extraction
 *
 * Instead of parsing files on-demand via API calls, this class hooks into WP All
 * Import's upload/processing flow and extracts file structure metadata + preview
 * records in a single pass. The user is already waiting for upload processing,
 * so we piggyback on that latency rather than adding it to API requests later.
 *
 * Storage: Custom DB table (wpai_bridge_file_cache) — not post meta or transients.
 *
 * @package AI_Bridge_For_WP_All_Import
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPAI_Bridge_File_Structure_Cache {

    const TABLE_NAME = 'wpai_bridge_file_cache';
    const DB_VERSION = '1.1';
    const DB_VERSION_OPTION = 'wpai_bridge_file_cache_db_version';
    const PREVIEW_BLOCK_SIZE = 10;
    const PREVIEW_BLOCK_COUNT = 3;

    private static $instance = null;

    public static function getInstance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'pmxi_import_file_ready', array( $this, 'on_import_file_ready' ), 10, 3 );
        add_action( 'pmxi_before_import_delete', array( $this, 'on_import_delete' ), 10, 2 );
    }

    /**
     * Create or update the custom table. Called from plugin activation hook.
     *
     * @return bool True if table exists after creation attempt, false otherwise.
     */
    public static function create_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            import_id BIGINT UNSIGNED NOT NULL,
            total_records INT UNSIGNED NOT NULL DEFAULT 0,
            root_element VARCHAR(255) NOT NULL DEFAULT '',
            xpath VARCHAR(255) NOT NULL DEFAULT '',
            field_names TEXT,
            sample_values TEXT,
            first_record LONGTEXT,
            preview_records LONGTEXT,
            file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
            file_mtime INT UNSIGNED NOT NULL DEFAULT 0,
            root_candidates TEXT,
            file_signature VARCHAR(100) NOT NULL DEFAULT '',
            trigger_hook VARCHAR(100) NOT NULL DEFAULT '',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY import_id (import_id),
            KEY file_signature (file_signature)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // Verify table was actually created
        $table_exists = $wpdb->get_var(
            $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name )
        ) === $table_name;

        if ( $table_exists ) {
            update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
        } else {
            delete_option( self::DB_VERSION_OPTION );
        }

        return $table_exists;
    }

    /**
     * Check whether the custom table exists.
     *
     * @return bool
     */
    public static function table_exists() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        return $wpdb->get_var(
            $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name )
        ) === $table_name;
    }

    /**
     * Drop the custom table. Called from plugin uninstall.
     */
    public static function drop_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
        delete_option( self::DB_VERSION_OPTION );
    }

    /**
     * Get the full table name with prefix
     */
    private function table_name() {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_NAME;
    }

    /**
     * Pre-cache file structure when import is fully configured.
     * Fires from: WPAI wizard Step 3 save, our file processor, or template update.
     */
    public function on_import_file_ready( $import_id, $file_path, $import ) {
        $existing = $this->get_cached_structure( $import_id, $file_path );
        if ( $existing !== false ) {
            WPAI_Bridge_Logger::debug( '[FileStructureCache] Already cached, skipping', array(
                'import_id' => $import_id,
                'records'   => $existing['total_records'] ?? 'unknown',
            ));
            return;
        }

        $this->extract_and_cache( $import_id, 'pmxi_import_file_ready' );
    }

    /**
     * Clean up cache row when an import is deleted in WP All Import.
     */
    public function on_import_delete( $import, $is_delete_posts ) {
        if ( ! empty( $import->id ) ) {
            $this->clear_cached_structure( $import->id );
        }
    }

    /**
     * Core extraction: single pass through file captures structure + preview records.
     */
    private function extract_and_cache( $import_id, $trigger ) {
        WPAI_Bridge_Logger::perf_start( 'structure_extraction' );

        try {
            $import = new PMXI_Import_Record();
            $import->getById( $import_id );

            if ( $import->isEmpty() ) {
                WPAI_Bridge_Logger::warn( '[FileStructureCache] Import not found', array(
                    'import_id' => $import_id,
                    'trigger'   => $trigger,
                ));
                return;
            }

            $file_path = $this->get_absolute_file_path( $import );
            if ( ! $file_path || ! file_exists( $file_path ) ) {
                WPAI_Bridge_Logger::warn( '[FileStructureCache] File not found', array(
                    'import_id' => $import_id,
                    'path'      => $import->path,
                    'trigger'   => $trigger,
                ));
                return;
            }

            $file_size  = filesize( $file_path );
            $file_mtime = filemtime( $file_path );

            WPAI_Bridge_Logger::debug( '[FileStructureCache] Starting extraction', array(
                'import_id'    => $import_id,
                'trigger'      => $trigger,
                'file_size_kb' => round( $file_size / 1024, 2 ),
                'root_element' => $import->root_element,
            ));

            $structure = $this->parse_file_structure( $file_path, $import );

            $structure['file_size']      = $file_size;
            $structure['file_mtime']     = $file_mtime;
            $structure['file_signature'] = $file_size . '_' . $file_mtime;
            $structure['trigger']        = $trigger;

            // Attach root element candidates if the File Processor just ran detection.
            $processor = WPAI_Bridge_File_Processor::getInstance();
            $candidates = $processor->get_last_root_candidates();
            if ( ! empty( $candidates ) ) {
                $structure['root_candidates'] = $candidates;
            }

            $this->save_cached_structure( $import_id, $structure );

            $perf_data = WPAI_Bridge_Logger::perf_end( 'structure_extraction', array(
                'import_id'        => $import_id,
                'trigger'          => $trigger,
                'total_records'    => $structure['total_records'],
                'file_size_kb'     => round( $file_size / 1024, 2 ),
                'fields_extracted' => count( $structure['field_names'] ),
                'preview_blocks'   => count( $structure['preview_records'] ),
                'preview_records'  => array_sum( array_map( function( $b ) { return count( $b['records'] ); }, $structure['preview_records'] ) ),
            ));

            WPAI_Bridge_Logger::info( '[FileStructureCache] Extraction complete', array(
                'import_id'       => $import_id,
                'trigger'         => $trigger,
                'records'         => $structure['total_records'],
                'fields'          => count( $structure['field_names'] ),
                'preview_blocks'  => count( $structure['preview_records'] ),
                'preview_records' => array_sum( array_map( function( $b ) { return count( $b['records'] ); }, $structure['preview_records'] ) ),
                'elapsed_ms'      => $perf_data['elapsed_ms'] ?? 'unknown',
                'mem_delta_mb'    => $perf_data['mem_delta_mb'] ?? 'unknown',
                'overhead_verdict' => $this->assess_overhead( $perf_data, $file_size ),
            ));

        } catch ( \Throwable $e ) {
            WPAI_Bridge_Logger::error( '[FileStructureCache] Extraction failed', array(
                'import_id' => $import_id,
                'trigger'   => $trigger,
                'error'     => $e->getMessage(),
            ));
            WPAI_Bridge_Logger::perf_end( 'structure_extraction', array(
                'error' => $e->getMessage(),
            ));
        }
    }

    /**
     * Single-pass extraction: capture field names/samples from first record, plus
     * 3 blocks of 10 contiguous preview records from start, middle, and end.
     *
     * Uses $import->count (already computed by handle_create_import) instead of
     * doing a separate counting pass. Only DOM-parses records that need to be
     * captured — all others are skipped to keep memory usage constant regardless
     * of file size.
     *
     * For small files (<= 30 records), all records are captured in a single block.
     */
    private function parse_file_structure( $file_path, $import ) {
        $encoding = ! empty( $import->options['encoding'] ) ? $import->options['encoding'] : 'UTF-8';
        $bs       = self::PREVIEW_BLOCK_SIZE;

        // Use the import's already-computed record count (set by handle_create_import
        // or count_elements before this hook fires) to avoid a full counting pass.
        $total_records = (int) $import->count;

        if ( $total_records <= 0 ) {
            WPAI_Bridge_Logger::warn( '[FileStructureCache] Import count is 0, skipping structure extraction' );
            return array(
                'total_records'   => 0,
                'root_element'    => $import->root_element,
                'xpath'           => $import->xpath,
                'first_record'    => null,
                'field_names'     => array(),
                'sample_values'   => array(),
                'preview_records' => array(),
            );
        }

        // Compute 3 contiguous blocks: start, middle, end
        $max_capture = self::PREVIEW_BLOCK_SIZE * self::PREVIEW_BLOCK_COUNT;

        if ( $total_records <= $max_capture ) {
            $blocks = array(
                array( 'offset' => 0, 'end' => $total_records - 1 ),
            );
        } else {
            $mid_start = (int) floor( ( $total_records - $bs ) / 2 );
            $end_start = $total_records - $bs;
            $blocks = array(
                array( 'offset' => 0,          'end' => $bs - 1 ),
                array( 'offset' => $mid_start, 'end' => $mid_start + $bs - 1 ),
                array( 'offset' => $end_start, 'end' => $end_start + $bs - 1 ),
            );
        }

        // Build a fast lookup: record_index => block_index
        $capture_map = array();
        foreach ( $blocks as $bi => $block ) {
            for ( $pos = $block['offset']; $pos <= $block['end']; $pos++ ) {
                $capture_map[ $pos ] = $bi;
            }
        }

        // Single pass: capture field names + preview blocks.
        // Only DOM-parse records we actually need (first record + capture_map entries).
        // For all other records, just increment the counter without allocating DOM objects.
        // PMXI_Chunk returns one root-level element per read() call, so each call = 1 record.
        $chunk = new PMXI_Chunk( $file_path, array(
            'element'  => $import->root_element,
            'encoding' => $encoding,
        ));

        $record_index    = 0;
        $first_record    = null;
        $field_names     = array();
        $sample_values   = array();
        $preview_blocks  = array();
        foreach ( $blocks as $bi => $block ) {
            $preview_blocks[ $bi ] = array(
                'offset'  => $block['offset'],
                'records' => array(),
            );
        }
        $captured_count   = 0;
        $total_to_capture = count( $capture_map );

        while ( $xml_chunk = $chunk->read() ) {
            if ( empty( $xml_chunk ) ) {
                continue;
            }

            $need_parse = ( $first_record === null ) || isset( $capture_map[ $record_index ] );

            if ( $need_parse ) {
                $xml = "<?xml version=\"1.0\" encoding=\"" . $encoding . "\"?>\n" . $xml_chunk;
                $dom = new DOMDocument( '1.0', $encoding );
                $old = libxml_use_internal_errors( true );
                $dom->loadXML( $xml );
                libxml_clear_errors();
                libxml_use_internal_errors( $old );
                $xpath_obj = new DOMXPath( $dom );

                $elements = @$xpath_obj->query( $import->xpath );
                if ( $elements && $elements->length ) {
                    foreach ( $elements as $element ) {
                        if ( $first_record === null ) {
                            $first_record  = $dom->saveXML( $element );
                            $extracted     = $this->extract_fields_from_record( $element );
                            $field_names   = $extracted['names'];
                            $sample_values = $extracted['samples'];
                        }

                        if ( isset( $capture_map[ $record_index ] ) ) {
                            $bi = $capture_map[ $record_index ];
                            $preview_blocks[ $bi ]['records'][] = $dom->saveXML( $element );
                            $captured_count++;
                        }
                    }
                }

                // Explicitly free DOM objects to prevent memory accumulation
                unset( $elements, $xpath_obj, $dom );
            }

            $record_index++;

            // Early exit once all blocks are captured
            if ( $captured_count >= $total_to_capture && $first_record !== null ) {
                break;
            }
        }

        return array(
            'total_records'   => $total_records,
            'root_element'    => $import->root_element,
            'xpath'           => $import->xpath,
            'first_record'    => $first_record,
            'field_names'     => $field_names,
            'sample_values'   => $sample_values,
            'preview_records' => array_values( $preview_blocks ),
        );
    }

    /**
     * Extract field names and sample values from a DOM element.
     */
    private function extract_fields_from_record( $element, $prefix = '' ) {
        $names   = array();
        $samples = array();

        foreach ( $element->childNodes as $child ) {
            if ( $child->nodeType !== XML_ELEMENT_NODE ) {
                continue;
            }

            $field_name   = $prefix . $child->nodeName;
            $has_children = false;

            foreach ( $child->childNodes as $grandchild ) {
                if ( $grandchild->nodeType === XML_ELEMENT_NODE ) {
                    $has_children = true;
                    break;
                }
            }

            if ( $has_children ) {
                $nested  = $this->extract_fields_from_record( $child, $field_name . '/' );
                $names   = array_merge( $names, $nested['names'] );
                $samples = array_merge( $samples, $nested['samples'] );
            } else {
                $names[] = $field_name;
                $value   = trim( $child->textContent );
                $samples[ $field_name ] = strlen( $value ) > 100 ? substr( $value, 0, 100 ) . '...' : $value;
            }
        }

        return array(
            'names'   => $names,
            'samples' => $samples,
        );
    }

    /**
     * Get cached structure from custom table.
     * Returns false if not cached or cache is stale (file changed).
     *
     * @param int         $import_id The import ID
     * @param string|null $file_path Optional: validate against current file
     * @return array|false Cached structure or false
     */
    public function get_cached_structure( $import_id, $file_path = null ) {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name()} WHERE import_id = %d",
                $import_id
            ),
            ARRAY_A
        );

        if ( empty( $row ) ) {
            return false;
        }

        // Validate freshness if file path provided
        if ( $file_path && file_exists( $file_path ) ) {
            $current_signature = filesize( $file_path ) . '_' . filemtime( $file_path );
            if ( $row['file_signature'] !== $current_signature ) {
                WPAI_Bridge_Logger::debug( '[FileStructureCache] Cache stale, file changed', array(
                    'import_id'   => $import_id,
                    'cached_sig'  => $row['file_signature'],
                    'current_sig' => $current_signature,
                ));
                $this->clear_cached_structure( $import_id );
                return false;
            }
        }

        // Deserialize JSON columns
        $row['field_names']      = json_decode( $row['field_names'], true ) ?: array();
        $row['sample_values']    = json_decode( $row['sample_values'], true ) ?: array();
        $row['preview_records']  = json_decode( $row['preview_records'], true ) ?: array();
        $row['root_candidates']  = ! empty( $row['root_candidates'] ) ? ( json_decode( $row['root_candidates'], true ) ?: array() ) : array();
        $row['total_records']    = (int) $row['total_records'];
        $row['file_size']        = (int) $row['file_size'];
        $row['file_mtime']       = (int) $row['file_mtime'];
        $row['trigger']          = $row['trigger_hook'];

        return $row;
    }

    /**
     * Save structure to custom table (insert or update).
     */
    public function save_cached_structure( $import_id, $structure ) {
        global $wpdb;

        $data = array(
            'import_id'       => $import_id,
            'total_records'   => $structure['total_records'],
            'root_element'    => $structure['root_element'] ?? '',
            'xpath'           => $structure['xpath'] ?? '',
            'field_names'     => wp_json_encode( $structure['field_names'] ?? array() ),
            'sample_values'   => wp_json_encode( $structure['sample_values'] ?? array() ),
            'first_record'    => $structure['first_record'] ?? '',
            'preview_records'  => wp_json_encode( $structure['preview_records'] ?? array() ),
            'root_candidates'  => wp_json_encode( $structure['root_candidates'] ?? array() ),
            'file_size'        => $structure['file_size'] ?? 0,
            'file_mtime'      => $structure['file_mtime'] ?? 0,
            'file_signature'  => $structure['file_signature'] ?? '',
            'trigger_hook'    => $structure['trigger'] ?? '',
            'created_at'      => current_time( 'mysql', true ),
        );

        $formats = array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s' );

        // Try update first, then insert
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$this->table_name()} WHERE import_id = %d",
                $import_id
            )
        );

        if ( $existing ) {
            unset( $data['import_id'] );
            array_shift( $formats );
            return $wpdb->update( $this->table_name(), $data, array( 'import_id' => $import_id ), $formats, array( '%d' ) ) !== false;
        }

        return $wpdb->insert( $this->table_name(), $data, $formats ) !== false;
    }

    /**
     * Clear cached structure for an import.
     */
    public function clear_cached_structure( $import_id ) {
        global $wpdb;

        WPAI_Bridge_Logger::debug( '[FileStructureCache] Clearing cache', array(
            'import_id' => $import_id,
        ));

        return $wpdb->delete( $this->table_name(), array( 'import_id' => $import_id ), array( '%d' ) ) !== false;
    }

    /**
     * Get pre-cached preview records for a paginated request.
     *
     * Preview records are stored as contiguous blocks (start/middle/end).
     * Finds the block whose offset is closest to the requested offset
     * and returns its contiguous records.
     *
     * @param int $import_id The import ID
     * @param int $offset    Requested offset (0-based)
     * @param int $limit     Number of records to return
     * @return array|false Array with 'records', 'total', 'count' or false
     */
    public function get_preview_records( $import_id, $offset, $limit ) {
        $cached = $this->get_cached_structure( $import_id );
        if ( $cached === false || empty( $cached['preview_records'] ) ) {
            return false;
        }

        $total  = $cached['total_records'];
        $blocks = $cached['preview_records'];

        // Find the block whose offset is closest to the requested offset
        $best_block = null;
        $best_distance = PHP_INT_MAX;

        foreach ( $blocks as $block ) {
            $distance = abs( ( $block['offset'] ?? 0 ) - $offset );
            if ( $distance < $best_distance ) {
                $best_distance = $distance;
                $best_block    = $block;
            }
        }

        if ( $best_block === null || empty( $best_block['records'] ) ) {
            return false;
        }

        $records = array_slice( $best_block['records'], 0, $limit );

        return array(
            'records' => $records,
            'total'   => $total,
            'count'   => count( $records ),
        );
    }

    /**
     * Get pre-cached random preview records.
     * Pools all records from all blocks, shuffles, and returns up to $limit.
     *
     * @param int $import_id The import ID
     * @param int $limit     Number of records to return
     * @return array|false Array with 'records', 'total', 'count' or false
     */
    public function get_random_preview_records( $import_id, $limit ) {
        $cached = $this->get_cached_structure( $import_id );
        if ( $cached === false || empty( $cached['preview_records'] ) ) {
            return false;
        }

        // Pool all records from all blocks
        $all_records = array();
        foreach ( $cached['preview_records'] as $block ) {
            if ( ! empty( $block['records'] ) ) {
                $all_records = array_merge( $all_records, $block['records'] );
            }
        }

        shuffle( $all_records );

        $selected = array_slice( $all_records, 0, $limit );

        return array(
            'records' => $selected,
            'total'   => $cached['total_records'],
            'count'   => count( $selected ),
        );
    }

    /**
     * Get absolute file path from import record.
     */
    private function get_absolute_file_path( $import ) {
        if ( empty( $import->path ) ) {
            return false;
        }

        if ( function_exists( 'wp_all_import_get_absolute_path' ) ) {
            return wp_all_import_get_absolute_path( $import->path );
        }

        if ( file_exists( $import->path ) ) {
            return $import->path;
        }

        $upload_dir = wp_upload_dir();
        $possible_paths = array(
            $upload_dir['basedir'] . '/wpallimport/' . $import->path,
            $upload_dir['basedir'] . '/wpallimport/uploads/' . $import->path,
            ABSPATH . $import->path,
        );

        foreach ( $possible_paths as $path ) {
            if ( file_exists( $path ) ) {
                return $path;
            }
        }

        return false;
    }

    /**
     * Assess whether the extraction overhead is acceptable.
     */
    private function assess_overhead( $perf_data, $file_size ) {
        $elapsed_ms   = $perf_data['elapsed_ms'] ?? 0;
        $file_size_mb = $file_size / ( 1024 * 1024 );

        if ( $file_size_mb < 1 && $elapsed_ms < 100 ) {
            return 'minimal (<100ms for <1MB)';
        }
        if ( $file_size_mb < 10 && $elapsed_ms < 500 ) {
            return 'acceptable (<500ms for <10MB)';
        }
        if ( $file_size_mb < 100 && $elapsed_ms < 2000 ) {
            return 'acceptable (<2s for <100MB)';
        }
        if ( $elapsed_ms < 5000 ) {
            return 'elevated but acceptable (<5s)';
        }

        return 'HIGH - needs investigation (' . round( $elapsed_ms / 1000, 1 ) . 's)';
    }
}
