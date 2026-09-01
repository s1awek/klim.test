<?php
/**
 * Corpus capture (consent-gated).
 *
 * After the LLM config feature prepares an import, send the full import file +
 * the assembled template_schema to the AI service's /api/capture endpoint, which
 * proxies it server-to-server into the regression corpus. This pairs the real
 * feed file with the target schema it was configured against so the corpus can
 * reproduce the target.
 *
 * Properties:
 *  - Consent-gated on the existing AI-data-processing consent. One check, so
 *    swapping to a dedicated corpus consent later is a one-line change.
 *  - Single destination: the AI service domain the plugin already talks to — no
 *    new endpoint/domain for a customer to allow.
 *  - Best-effort + non-blocking (wp_remote_post blocking=false); never disrupts
 *    or slows the import config flow.
 *  - Bounded: files over the cap are skipped-with-log, never sent.
 *  - Idempotent: per-(import,file) dedup so re-analyze doesn't resend.
 */

defined( 'ABSPATH' ) || exit;

class WPAI_Bridge_Corpus_Capture {

	const MAX_BYTES    = 4194304; // 4 MB — under the AI service's serverless body limit.
	const SEEN_OPTION  = 'wpai_bridge_corpus_capture_seen';
	const MAX_SEEN     = 500;
	const TIMEOUT      = 15;

	public static function maybe_dispatch( $import, $import_type, $schema, $session_token ) {
		try {
			// Single consent gate (AI data processing). Change here to adopt a
			// dedicated corpus-contribution consent later.
			if ( ! function_exists( 'wpai_bridge_user_has_consented' ) || ! wpai_bridge_user_has_consented() ) {
				return;
			}

			$api       = WPAI_Bridge_LLM_Config_API::getInstance();
			$file_path = $api->get_import_file_path( $import );
			if ( is_wp_error( $file_path ) || empty( $file_path ) || ! is_file( $file_path ) || ! is_readable( $file_path ) ) {
				return;
			}

			$size = filesize( $file_path );
			if ( $size === false || $size > self::MAX_BYTES ) {
				WPAI_Bridge_Logger::debug( 'Corpus capture: skip (oversized/unreadable)', array(
					'bytes'     => $size,
					'import_id' => isset( $import->id ) ? (int) $import->id : 0,
				) );
				return;
			}

			$import_id = isset( $import->id ) ? (int) $import->id : 0;
			$hash      = sha1( $file_path . '|' . $size . '|' . $import_id );
			if ( self::already_sent( $hash ) ) {
				return;
			}

			$fields = array(
				'session_token'   => (string) $session_token,
				'wp_api_url'      => get_rest_url( null, 'wp-all-import/v1' ),
				'import_id'       => (string) $import_id,
				'import_type'     => (string) $import_type,
				'site_url'        => site_url(),
				'filename'        => basename( $file_path ),
				'template_schema' => wp_json_encode( $schema ),
			);

			// Observe/extend seam — fires in-process only; no data leaves through it.
			do_action( 'wpai_bridge_corpus_capture_dispatch', array_merge( $fields, array(
				'file_bytes' => (int) $size,
				'endpoint'   => self::endpoint(),
			) ) );

			self::send( $fields, $file_path );
			self::mark_sent( $hash );
		} catch ( \Throwable $e ) {
			WPAI_Bridge_Logger::warn( 'Corpus capture dispatch failed: ' . $e->getMessage() );
		}
	}

	private static function endpoint() {
		return trailingslashit( wpai_bridge_get_llm_service_url() ) . 'api/capture';
	}

	private static function send( array $fields, $file_path ) {
		$boundary = 'wpaicap' . wp_generate_password( 24, false );
		$eol      = "\r\n";
		$body     = '';
		foreach ( $fields as $name => $value ) {
			$body .= '--' . $boundary . $eol;
			$body .= 'Content-Disposition: form-data; name="' . $name . '"' . $eol . $eol;
			$body .= $value . $eol;
		}
		$body .= '--' . $boundary . $eol;
		$body .= 'Content-Disposition: form-data; name="file"; filename="' . $fields['filename'] . '"' . $eol;
		$body .= 'Content-Type: application/octet-stream' . $eol . $eol;
		$body .= file_get_contents( $file_path ) . $eol;
		$body .= '--' . $boundary . '--' . $eol;

		wp_remote_post( self::endpoint(), array(
			'blocking' => false,
			'timeout'  => self::TIMEOUT,
			'headers'  => array( 'Content-Type' => 'multipart/form-data; boundary=' . $boundary ),
			'body'     => $body,
		) );
	}

	private static function already_sent( $hash ) {
		$seen = get_option( self::SEEN_OPTION, array() );
		return is_array( $seen ) && isset( $seen[ $hash ] );
	}

	private static function mark_sent( $hash ) {
		$seen = get_option( self::SEEN_OPTION, array() );
		if ( ! is_array( $seen ) ) {
			$seen = array();
		}
		$seen[ $hash ] = time();
		if ( count( $seen ) > self::MAX_SEEN ) {
			asort( $seen );
			$seen = array_slice( $seen, -self::MAX_SEEN, null, true );
		}
		update_option( self::SEEN_OPTION, $seen, false );
	}
}
