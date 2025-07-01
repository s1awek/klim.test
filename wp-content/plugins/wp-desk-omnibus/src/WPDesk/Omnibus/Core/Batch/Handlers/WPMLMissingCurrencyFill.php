<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Batch\Handlers;

use WPDesk\Omnibus\Core\Batch\ResettableHandler;
use WPDesk\Omnibus\Core\Interceptor\InterceptionPersister;
use WPDesk\Omnibus\Core\Migrations\Schema;
use WPDesk\Omnibus\Core\Multicurrency\AvailableCurrencies;
use WPDesk\Omnibus\Core\Utils\ExternalPlugin;

/**
 * If we have WPML multicurrency enabled, check for all saved prices which doesn't have foreign
 * currency (provided by WPML) and fill it with current value, either managed or unmanaged.
 *
 * This is inherently handled by {@see PriceMigrator}, but this class is targeted for users
 * upgrading to 2.1.0, which previously had prices table filled. Both cases should not interfere, if
 * executed one after another (concurrent execution of both handlers may be unexpected).
 *
 * @since 2.1.0
 *
 * @implements ResettableHandler<\WC_Product>
 */
class WPMLMissingCurrencyFill implements ResettableHandler {
	private const WPML_CURRENCY_FILL = 'omnibus_wpml_currency_fill';

	private InterceptionPersister $persister;

	private \wpdb $wpdb;

	private AvailableCurrencies $currencies;

	private ExternalPlugin $wcml;

	public function __construct( InterceptionPersister $persister, \wpdb $wpdb, AvailableCurrencies $currencies, ExternalPlugin $wcml ) {
		$this->persister  = $persister;
		$this->wpdb       = $wpdb;
		$this->currencies = $currencies;
		$this->wcml       = $wcml;
	}

	public function get_name(): string {
		return 'wpml_currency_fill';
	}

	public function reset(): bool {
		return delete_option( self::WPML_CURRENCY_FILL );
	}

	public function process( iterable $batch ): void {
		foreach ( $batch as $product ) {
			$this->persister->intercept_product_prices( $product );
		}

		if ( ! $this->should_enqueue() ) {
			add_option( self::WPML_CURRENCY_FILL, '1' );
		}
	}

	public function should_enqueue(): bool {
		if ( filter_var( get_option( self::WPML_CURRENCY_FILL, false ), \FILTER_VALIDATE_BOOLEAN ) ) {
			return false;
		}

		if ( ! $this->wcml->is_active() ) {
			return false;
		}

		if ( iterator_count( $this->currencies->codes() ) === 0 ) {
			return false;
		}

		$required = iterator_count( $this->lazy_get_products( 1 ) ) !== 0;

		if ( ! $required ) {
			add_option( self::WPML_CURRENCY_FILL, '1' );
		}

		return $required;
	}

	/** @return \Generator<\WC_Product> */
	public function chunk( int $size ): iterable {
		foreach ( $this->lazy_get_products( $size ) as $product ) {
			$product = wc_get_product( $product->id );

			if ( $product instanceof \WC_Product_Variable ) {
				continue;
			}

			if ( $product instanceof \WC_Product ) {
				yield $product;
			}
		}
	}

	/** @return \Generator<object{id: string}> */
	private function lazy_get_products( int $size ): iterable {
		yield from $this->wpdb->get_results(
			$this->wpdb->prepare(
				"
				SELECT DISTINCT p.product_id as id
				FROM %i p
				WHERE NOT EXISTS (
					SELECT 1
					FROM %i
					WHERE
						product_id = p.product_id AND
						currency IN ( '" . implode( "','", array_map( 'esc_sql', iterator_to_array( $this->currencies->codes() ) ) ) . "' )
					GROUP BY product_id
				)
				LIMIT %d
				",
				Schema::price_logger_table_name(),
				Schema::price_logger_table_name(),
				$size,
			)
		);
	}

	/**
	 * As each batch processes multiple currencies sequentially in a one run, ensure only a fraction
	 * of nominal (stress-tested) size from {@see PriceMigrator} is used.
	 */
	public function get_batch_size(): int {
		$currencies_count = max( iterator_count( $this->currencies->codes() ), 1 );
		return (int) ceil( 5000 / $currencies_count );
	}
}
