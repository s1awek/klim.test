<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Cache;

/**
 * Simple wrapper around wp_cache_*.
 */
class WpCachePool {

	/**
	 * Fetch a value from cache or compute if not found.
	 *
	 * @template T
	 *
	 * @param callable(): T $callback
	 *
	 * @return T|null
	 */
	public function get( string $key, callable $callback, int $expiration = 0 ) {
		$found = false;

		$result = wp_cache_get( $key, 'wpdesk-omnibus', false, $found );

		if ( $found === true ) {
			return $result;
		}

		$set_result = wp_cache_set( $key, $callback(), 'wpdesk-omnibus', $expiration );

		if ( $set_result === false ) {
			throw new CacheWriteFailure( 'Failed to write item to cache.' );
		}

		$result = wp_cache_get( $key, 'wpdesk-omnibus', false, $found );

		if ( $found === false ) {
			return null;
		}

		return $result;
	}

	public function delete( string $key ): bool {
		return (bool) wp_cache_delete( $key, 'wpdesk-omnibus' );
	}

	public function prune(): bool {
		return wp_cache_supports( 'flush_group' ) && wp_cache_flush_group( 'wpdesk-omnibus' );
	}
}
