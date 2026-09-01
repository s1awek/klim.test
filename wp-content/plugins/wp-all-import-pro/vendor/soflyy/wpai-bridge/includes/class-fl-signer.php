<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Signs the bridge's server-to-server requests to the frontend layer (FL) so FL
 * can verify the request came from a genuine WP All Import install before running
 * rate-limited / spend-capped work. This is a shared-secret speed-bump shipped in
 * the free plugin, NOT per-user auth — it is combined on FL with rate limiting and
 * per-site daily spend caps.
 *
 * Wire contract (must match the FL withBridgeProtection verifier exactly):
 *   canonical = "{timestamp}\n{METHOD}\n{path}\n{sha256hex(rawBody)}"
 *   signature = lowercase hex HMAC-SHA256(secret, canonical)
 *   headers   = X-WPAI-Timestamp: <unix seconds>, X-WPAI-Signature: v1=<hex>
 *
 * The shared secret resolves option → constant → shipped default, like the
 * formulas pubkey. When it is empty (unset), signing is skipped (no headers) so
 * local/dev works and FL likewise skips verification.
 */
class WPAI_Bridge_FL_Signer {

    const SECRET_OPT = 'wpai_bridge_shared_secret';

    // Shipped default shared secret. Intentionally EMPTY here — the production
    // value is injected at release build (matching the FL WPAI_BRIDGE_SHARED_SECRET
    // env). Never hardcode a real secret in source. Local/staging can override via
    // the wpai_bridge_shared_secret option or WPAI_BRIDGE_SHARED_SECRET constant.
    const DEFAULT_SHARED_SECRET = '';

    /** The shared secret: option → constant → shipped default. Empty ⇒ signing off. */
    public static function secret() {
        $secret = get_option( self::SECRET_OPT );
        if ( ! $secret && defined( 'WPAI_BRIDGE_SHARED_SECRET' ) ) {
            $secret = WPAI_BRIDGE_SHARED_SECRET;
        }
        if ( ! $secret ) {
            $secret = self::DEFAULT_SHARED_SECRET;
        }
        return (string) $secret;
    }

    /**
     * Compute the signing headers for a request. Returns an empty array when no
     * shared secret is configured (signing disabled).
     *
     * @param string $method HTTP method (e.g. 'POST').
     * @param string $path   FL route path (e.g. '/api/translate').
     * @param string $body   Exact request body bytes (JSON string); '' for none.
     * @return array<string,string>
     */
    public static function sign_headers( $method, $path, $body ) {
        $secret = self::secret();
        if ( '' === $secret ) {
            return array();
        }

        $timestamp = (string) time();
        $canonical = self::canonical( $timestamp, $method, $path, (string) $body );
        $signature = self::hmac_hex( $canonical, $secret );

        return array(
            'X-WPAI-Timestamp' => $timestamp,
            'X-WPAI-Signature' => 'v1=' . $signature,
        );
    }

    /**
     * Mint a short-lived, site-bound session token the browser can present on the
     * autoconfigure routes that FL's own /configure + /step1 pages fetch from the
     * browser (they can't carry the shared secret to sign each request). FL's
     * with-bridge-protection verifies this token with the SAME secret + HMAC, so a
     * valid session token is accepted in lieu of a request signature.
     *
     * Wire format (must match the FL verifySessionToken):
     *   payload = base64url(json{ site_url, iat })   // iat = unix seconds
     *   token   = payload . "." . lowercase-hex HMAC-SHA256(secret, payload)
     *
     * Returns '' when no shared secret is configured (verification is skipped on
     * FL too, so dev/local still works).
     *
     * @param string $site_url The site identity the token is bound to; must equal
     *                         the wp_api_url/site_url the browser sends in the body.
     * @return string
     */
    public static function mint_session_token( $site_url ) {
        $secret = self::secret();
        if ( '' === $secret ) {
            return '';
        }

        $payload = wp_json_encode(
            array(
                'site_url' => (string) $site_url,
                'iat'      => time(),
            )
        );
        $encoded   = self::base64url_encode( (string) $payload );
        $signature = self::hmac_hex( $encoded, $secret );

        return $encoded . '.' . $signature;
    }

    /** Lowercase hex HMAC-SHA256 — the single HMAC compute shared by all signing. */
    private static function hmac_hex( $data, $secret ) {
        return hash_hmac( 'sha256', (string) $data, (string) $secret );
    }

    /** URL-safe base64 without padding (matches the JS base64url decode on FL). */
    private static function base64url_encode( $data ) {
        return rtrim( strtr( base64_encode( (string) $data ), '+/', '-_' ), '=' );
    }

    /**
     * Convenience: derive the path from a full FL URL and return the signing
     * headers. Use this at call sites that already have the destination URL.
     *
     * @param string $method HTTP method.
     * @param string $url    Full FL URL.
     * @param string $body   Exact request body bytes.
     * @return array<string,string>
     */
    public static function headers_for_url( $method, $url, $body ) {
        $path = wp_parse_url( $url, PHP_URL_PATH );
        if ( ! is_string( $path ) || '' === $path ) {
            $path = '/';
        }
        return self::sign_headers( $method, $path, $body );
    }

    /** Build the canonical string that both sides HMAC over. */
    public static function canonical( $timestamp, $method, $path, $body ) {
        $body_hash = hash( 'sha256', (string) $body );
        return $timestamp . "\n" . strtoupper( $method ) . "\n" . $path . "\n" . $body_hash;
    }
}
