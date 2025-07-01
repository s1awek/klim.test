<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Batch\Handlers;

use WPDesk\Omnibus\Core\Batch\ResettableHandler;
use WPDesk\Omnibus\Core\Migrations\Schema;
use WPDesk\Omnibus\Core\Multicurrency\AvailableCurrencies;
use WPDesk\Omnibus\Core\Repository\HistoricalPriceHydrator;
use WPDesk\Omnibus\Core\Repository\HistoricalPricePersister;
use WPDesk\Omnibus\Core\Repository\PriceFactory;
use WPDesk\Omnibus\Core\Repository\PriceNotSaved;
use WPDesk\Omnibus\Core\Utils\ExternalPlugin;
use WPDesk\Omnibus\Core\HistoricalPrice;

/**
 * @since 2.1.0
 *
 * @implements ResettableHandler<HistoricalPrice>
 */
class WPMLAutomaticExchange implements ResettableHandler {
	private const WPML_CURRENCY_FILL = 'omnibus_wpml_automatic_exchange';

	private \wpdb $wpdb;

	private AvailableCurrencies $currencies;

	private HistoricalPriceHydrator $hydrator;

	private PriceFactory $factory;

	private HistoricalPricePersister $persister;

	private ExternalPlugin $wcml;

	public function __construct( \wpdb $wpdb, AvailableCurrencies $currencies, PriceFactory $factory, HistoricalPriceHydrator $hydrator, HistoricalPricePersister $persister, ExternalPlugin $wcml ) {
		$this->wpdb       = $wpdb;
		$this->currencies = $currencies;
		$this->hydrator   = $hydrator;
		$this->factory    = $factory;
		$this->persister  = $persister;
		$this->wcml       = $wcml;
	}

	public function get_name(): string {
		return 'wpml_automatic_exchange';
	}

	public function reset(): bool {
		return delete_option( self::WPML_CURRENCY_FILL );
	}

	public function process( iterable $batch ): void {
		foreach ( $batch as $price ) {
			foreach ( $this->currencies->codes() as $currency ) {
				if ( ! $this->uses_custom_prices( $price->get_product(), $price->get_currency() ) ) {
					$new_price = $this->factory->refresh( $price );
					$new_price->set_currency( $currency );
					$new_price->set_price( (float) apply_filters( 'wcml_raw_price_amount', $price->get_price(), $currency ) );

					try {
						$this->persister->save( $new_price );
					} catch ( PriceNotSaved $e ) {
					}
				}
			}
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

		$required = iterator_count( $this->chunk( 1 ) ) !== 0;

		if ( ! $required ) {
			add_option( self::WPML_CURRENCY_FILL, '1' );
		}

		return $required;
	}

	/** @return \Generator<HistoricalPrice> */
	public function chunk( int $size ): iterable {
		foreach ( $this->lazy_get_prices( $size ) as $price ) {
			yield $this->hydrator->hydrate( $price );
		}
	}

	/** @return \Generator<array<string, string>> */
	private function lazy_get_prices( int $size ): iterable {
		yield from $this->wpdb->get_results(
			$this->wpdb->prepare(
				"
				SELECT *
				FROM %i
				WHERE product_id NOT IN (
					SELECT product_id
					FROM %i
					WHERE currency IN ( '" . implode( "','", array_map( 'esc_sql', iterator_to_array( $this->currencies->codes(), false ) ) ) . "' )
				)
				LIMIT %d
				",
				Schema::price_logger_table_name(),
				Schema::price_logger_table_name(),
				$size,
			),
			\ARRAY_A
		);
	}

	public function get_batch_size(): int {
		return 5000;
	}

	private function uses_custom_prices( \WC_Product $product, string $currency ): bool {
		return // Use the same check as in WCML (non strict equal).
			get_post_meta( $product->get_id(), '_wcml_custom_prices_status', true ) == 1 && // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			is_numeric( get_post_meta( $product->get_id(), '_regular_price_' . $currency ) ) &&
			( (int) get_post_meta( $product->get_id(), '_regular_price_' . $currency ) ) !== 0;
	}
}
