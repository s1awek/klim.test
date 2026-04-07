<?php
/**
 * WP All Import Diagnostics Logger
 *
 * Loguje metryki systemowe podczas importu WP All Import
 * w celu diagnozowania przyczyn przerywania procesów.
 *
 * Włączanie: define('WPAI_DIAGNOSTICS', true); w wp-config.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WPAI_DIAGNOSTICS' ) || ! WPAI_DIAGNOSTICS ) {
	return;
}

class WPAI_Diagnostics {

	private static $start_time    = null;
	private static $batch_start   = null;
	private static $log_file      = null;
	private static $post_counter  = 0;
	private static $import_id     = null;
	private static $pid           = null;
	private static $iteration     = 0;
	private static $log_dir       = null;
	private static $is_import_req = false;
	private static $import_ended  = false;
	private static $batch_ended   = false;

	public static function init() {
		$upload_dir     = wp_upload_dir();
		self::$log_dir  = $upload_dir['basedir'] . '/wpallimport/logs';
		self::$log_file = self::$log_dir . '/diagnostics.log'; // fallback
		self::$pid      = getmypid();

		self::rotate_log();

		add_action( 'admin_init', array( __CLASS__, 'on_admin_init' ), 1 );
		add_action( 'pmxi_before_xml_import', array( __CLASS__, 'on_before_xml_import' ), 1, 1 );
		add_action( 'pmxi_before_post_import', array( __CLASS__, 'on_before_post_import' ), 1, 1 );
		add_action( 'pmxi_saved_post', array( __CLASS__, 'on_saved_post' ), 10, 3 );
		add_action( 'pmxi_after_post_import', array( __CLASS__, 'on_after_post_import' ), 10, 1 );
		add_action( 'pmxi_after_xml_import', array( __CLASS__, 'on_after_xml_import' ), 99, 1 );
	}

	/**
	 * Rejestruje shutdown function dla requestów importowych.
	 */
	public static function on_admin_init() {
		if ( ! isset( $_GET['page'], $_GET['action'] ) ) {
			return;
		}
		if ( $_GET['page'] !== 'pmxi-admin-import' || $_GET['action'] !== 'process' ) {
			return;
		}

		self::$is_import_req = true;
		self::$start_time    = microtime( true );

		// Log per import: diagnostics-import-{id}-{date}-{ip}.log
		$import_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		$ip_short  = str_replace( array( '.', ':' ), '-', $_SERVER['REMOTE_ADDR'] ?? 'unknown' );
		$date      = gmdate( 'Y-m-d' );
		self::$log_file = self::$log_dir . "/diagnostics-import-{$import_id}-{$date}-{$ip_short}.log";

		ignore_user_abort( true );
		register_shutdown_function( array( __CLASS__, 'on_shutdown' ) );

		// KRYTYCZNE: detekcja AJAX wg WP All Import
		$http_accept    = $_SERVER['HTTP_ACCEPT'] ?? '';
		$wpai_is_ajax   = strpos( $http_accept, 'json' ) !== false;

		self::log( 'request_start', array(
			'request_uri'        => $_SERVER['REQUEST_URI'] ?? '',
			'remote_addr'        => $_SERVER['REMOTE_ADDR'] ?? '',
			'import_id'          => $import_id,
			'failures'           => isset( $_GET['failures'] ) ? intval( $_GET['failures'] ) : 0,
			'http_accept'        => $http_accept,
			'http_user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? '',
			'http_x_requested_with' => $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '',
			'wpai_is_ajax'       => $wpai_is_ajax,
			'http_referer'       => $_SERVER['HTTP_REFERER'] ?? '',
		) );
	}

	/**
	 * Początek całego importu.
	 */
	public static function on_before_xml_import( $import_id ) {
		self::$import_id    = $import_id;
		self::$iteration    = 0;
		self::$post_counter = 0;

		if ( ! self::$start_time ) {
			self::$start_time = microtime( true );
		}

		self::log( 'import_start', array(
			'import_id'          => $import_id,
			'php_version'        => PHP_VERSION,
			'php_sapi'           => PHP_SAPI,
			'server_software'    => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
			'remote_addr'        => $_SERVER['REMOTE_ADDR'] ?? '',
			'memory_limit'       => ini_get( 'memory_limit' ),
			'max_execution_time' => ini_get( 'max_execution_time' ),
			'max_input_time'     => ini_get( 'max_input_time' ),
			'post_max_size'      => ini_get( 'post_max_size' ),
			'wp_memory_limit'    => defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : 'not set',
		) );
	}

	/**
	 * Początek batcha/iteracji.
	 */
	public static function on_before_post_import( $import_id ) {
		self::$iteration++;
		self::$batch_start  = microtime( true );
		self::$import_id    = $import_id;
		self::$post_counter = 0;

		self::log( 'batch_start', array(
			'import_id' => $import_id,
			'iteration' => self::$iteration,
		) );
	}

	/**
	 * Po zapisaniu każdego rekordu — loguje co N-ty lub przy anomalii.
	 */
	public static function on_saved_post( $post_id, $xml_node, $is_update ) {
		self::$post_counter++;

		$nth     = defined( 'WPAI_DIAGNOSTICS_NTH' ) ? (int) WPAI_DIAGNOSTICS_NTH : 50;
		$metrics = self::get_metrics();

		$warn_mem = defined( 'WPAI_DIAGNOSTICS_WARN_MEM' ) ? (int) WPAI_DIAGNOSTICS_WARN_MEM : 80;
		$is_warning = $metrics['memory_pct'] > $warn_mem
			|| $metrics['connection_status'] !== 0;

		if ( self::$post_counter === 1 || ( $nth > 0 && self::$post_counter % $nth === 0 ) || $is_warning ) {
			self::log( 'record', array(
				'import_id'    => self::$import_id,
				'post_id'      => $post_id,
				'is_update'    => $is_update,
				'record_in_batch' => self::$post_counter,
				'iteration'    => self::$iteration,
			) );
		}
	}

	/**
	 * Koniec batcha.
	 */
	public static function on_after_post_import( $import_id ) {
		self::$batch_ended = true;
		$batch_time = self::$batch_start ? microtime( true ) - self::$batch_start : null;

		self::log( 'batch_end', array(
			'import_id'      => $import_id,
			'iteration'      => self::$iteration,
			'records_in_batch' => self::$post_counter,
			'batch_time_s'   => $batch_time ? round( $batch_time, 3 ) : null,
		) );
	}

	/**
	 * Koniec całego importu.
	 */
	public static function on_after_xml_import( $import_id ) {
		self::$import_ended = true;

		self::log( 'import_end', array(
			'import_id'      => $import_id,
			'total_iterations' => self::$iteration,
		) );
	}

	/**
	 * Shutdown function — łapie fatal errors i nieoczekiwane zakończenia.
	 */
	public static function on_shutdown() {
		if ( ! self::$is_import_req ) {
			return;
		}

		$error = error_get_last();
		$is_fatal = $error && in_array( $error['type'], array( E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE ), true );

		// Sprawdź jakie nagłówki PHP wysłał (response headers)
		$headers_sent = function_exists( 'headers_list' ) ? headers_list() : array();
		$content_type = '';
		foreach ( $headers_sent as $h ) {
			if ( stripos( $h, 'content-type:' ) === 0 ) {
				$content_type = trim( substr( $h, 13 ) );
				break;
			}
		}

		$data = array(
			'import_id'          => self::$import_id,
			'import_ended'       => self::$import_ended,
			'connection_aborted' => connection_aborted(),
			'last_iteration'     => self::$iteration,
			'last_record_in_batch' => self::$post_counter,
			'response_content_type' => $content_type,
			'response_headers'   => $headers_sent,
		);

		if ( $is_fatal ) {
			$data['fatal_error'] = array(
				'type'    => $error['type'],
				'message' => $error['message'],
				'file'    => $error['file'],
				'line'    => $error['line'],
			);
		}

		// Diagnoza przyczyny
		if ( self::$import_ended ) {
			$data['diagnosis'] = 'normal_end';
		} elseif ( self::$batch_ended ) {
			$data['diagnosis'] = 'batch_ok';
		} elseif ( $is_fatal && stripos( $error['message'], 'memory' ) !== false ) {
			$data['diagnosis'] = 'memory_exhausted';
		} elseif ( $is_fatal && stripos( $error['message'], 'time' ) !== false ) {
			$data['diagnosis'] = 'execution_timeout';
		} elseif ( connection_aborted() ) {
			$data['diagnosis'] = 'connection_aborted';
		} elseif ( $is_fatal ) {
			$data['diagnosis'] = 'fatal_error';
		} else {
			$data['diagnosis'] = 'process_killed';
		}

		// Ustal typ zdarzenia
		if ( self::$import_ended ) {
			$event = 'shutdown_import_complete';
		} elseif ( self::$batch_ended ) {
			$event = 'shutdown_batch_ok';
		} else {
			$event = 'shutdown_abnormal';
		}

		self::log( $event, $data );
	}

	/**
	 * Zbiera metryki systemowe.
	 */
	private static function get_metrics() {
		$mem_usage = memory_get_usage( true );
		$mem_peak  = memory_get_peak_usage( true );
		$mem_limit = self::parse_memory_limit( ini_get( 'memory_limit' ) );
		$mem_pct   = $mem_limit > 0 ? round( ( $mem_usage / $mem_limit ) * 100, 1 ) : 0;

		$load = function_exists( 'sys_getloadavg' ) ? sys_getloadavg() : null;

		$rusage     = function_exists( 'getrusage' ) ? getrusage() : null;
		$utime      = null;
		$stime      = null;
		if ( $rusage ) {
			$utime = ( $rusage['ru_utime.tv_sec'] ?? 0 ) + ( $rusage['ru_utime.tv_usec'] ?? 0 ) / 1e6;
			$stime = ( $rusage['ru_stime.tv_sec'] ?? 0 ) + ( $rusage['ru_stime.tv_usec'] ?? 0 ) / 1e6;
		}

		// I/O stats z /proc/self/io (Linux)
		$io = self::get_proc_io();

		return array(
			'pid'               => getmypid(),
			'memory_mb'         => round( $mem_usage / 1048576, 1 ),
			'memory_peak_mb'    => round( $mem_peak / 1048576, 1 ),
			'memory_limit_mb'   => round( $mem_limit / 1048576, 1 ),
			'memory_pct'        => $mem_pct,
			'cpu_load'          => $load ? array_map( function( $v ) { return round( $v, 2 ); }, $load ) : null,
			'connection_status' => connection_status(),
			'elapsed_s'         => self::$start_time ? round( microtime( true ) - self::$start_time, 3 ) : null,
			'rusage_utime'      => $utime !== null ? round( $utime, 3 ) : null,
			'rusage_stime'      => $stime !== null ? round( $stime, 3 ) : null,
			'io_read_mb'        => $io ? round( $io['read_bytes'] / 1048576, 2 ) : null,
			'io_write_mb'       => $io ? round( $io['write_bytes'] / 1048576, 2 ) : null,
			'io_syscr'          => $io ? $io['syscr'] : null,
			'io_syscw'          => $io ? $io['syscw'] : null,
		);
	}

	/**
	 * Zapisuje linię logu.
	 */
	private static function log( $event, $extra = array() ) {
		$metrics = self::get_metrics();

		$warn_mem = defined( 'WPAI_DIAGNOSTICS_WARN_MEM' ) ? (int) WPAI_DIAGNOSTICS_WARN_MEM : 80;
		$warning = $metrics['memory_pct'] > $warn_mem
			|| $metrics['connection_status'] !== 0
			|| ( $metrics['cpu_load'] && $metrics['cpu_load'][0] > 4.0 );

		$entry = array_merge(
			array( 'event' => $event ),
			$metrics,
			$extra
		);

		if ( $warning ) {
			$entry['warning'] = true;
		}

		$timestamp = gmdate( 'Y-m-d H:i:s' );
		$line      = sprintf( "[%s] %s\n", $timestamp, json_encode( $entry, JSON_UNESCAPED_UNICODE ) );

		$dir = dirname( self::$log_file );
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		self::maybe_rotate();

		file_put_contents( self::$log_file, $line, FILE_APPEND | LOCK_EX );
	}

	/**
	 * Odczytuje statystyki I/O procesu z /proc/self/io (Linux).
	 */
	private static function get_proc_io() {
		$path = '/proc/self/io';
		if ( ! is_readable( $path ) ) {
			return null;
		}

		$content = @file_get_contents( $path );
		if ( $content === false ) {
			return null;
		}

		$io = array();
		foreach ( explode( "\n", trim( $content ) ) as $line ) {
			$parts = explode( ': ', $line, 2 );
			if ( count( $parts ) === 2 ) {
				$io[ trim( $parts[0] ) ] = (int) trim( $parts[1] );
			}
		}

		return $io ?: null;
	}

	/**
	 * Konwersja "512M", "1G" itp. na bajty.
	 */
	private static function parse_memory_limit( $val ) {
		$val  = trim( $val );
		$last = strtolower( substr( $val, -1 ) );
		$num  = (int) $val;

		switch ( $last ) {
			case 'g':
				$num *= 1024;
				// fall through
			case 'm':
				$num *= 1024;
				// fall through
			case 'k':
				$num *= 1024;
		}

		if ( $num <= 0 || $val === '-1' ) {
			return PHP_INT_MAX;
		}

		return $num;
	}

	/**
	 * Rotacja logu — jeśli > 10MB, przenosi do .old
	 */
	private static function rotate_log() {
		// Rotacja jest per-plik, wywoływana w log() gdy plik > 10MB
	}

	private static function maybe_rotate() {
		if ( self::$log_file && file_exists( self::$log_file ) && filesize( self::$log_file ) > 10 * 1048576 ) {
			@rename( self::$log_file, self::$log_file . '.old' );
		}
	}
}

add_action( 'init', array( 'WPAI_Diagnostics', 'init' ) );
