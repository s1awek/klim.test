<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Multicurrency;

use WPDesk\Omnibus\Core\Multicurrency\Client\CurrencyResolver;
use WPDesk\Omnibus\Core\Product\NormalizedChangeset;
use WPDesk\Omnibus\Core\Product\RenamedChangeset;
use WPDesk\Omnibus\Core\Product\ProductChangesRegistry;
use WPDesk\Omnibus\Core\Product\RegularPricing;
use WPDesk\Omnibus\Core\Product\WPMLCurrencyAwarePricing;

class WPMLCurrencies implements AvailableCurrencies {

	private ProductChangesRegistry $changes;

	private CurrencyResolver $default_resolver;

	public function __construct( ProductChangesRegistry $changes, CurrencyResolver $default_resolver ) {
		$this->changes          = $changes;
		$this->default_resolver = $default_resolver;
	}

	public function getIterator(): \Traversable {
		foreach ( $this->codes() as $code ) {
			yield $code => fn ( \WC_Product $product ) => new WPMLCurrencyAwarePricing(
				new RegularPricing( $product, $this->default_resolver ),
				$code,
				new NormalizedChangeset(
					new RenamedChangeset(
						$this->changes->get_changeset( $product->get_id() ),
						function ( string $key ) use ( $code ) {
								$renames_map = [
									'_regular_price_' . $code => 'regular_price',
									'_sale_price_' . $code => 'sale_price',
									'_sale_price_dates_from_' . $code => 'date_on_sale_from',
									'_sale_price_dates_to_' . $code => 'date_on_sale_to',
								];

								return $renames_map[ $key ] ?? $key;
						}
					)
				)
			);
		}
	}

	public function codes(): \Traversable {
		if ( ! function_exists( '\WCML\functions\getWooCommerceWpml' ) ) {
			return new \ArrayIterator( [] );
		}

		$wpml = \WCML\functions\getWooCommerceWpml();

		if ( $wpml === null ) {
			return new \ArrayIterator( [] );
		}

		return new \ArrayIterator(
			array_keys(
				$wpml->get_multi_currency()->get_currencies()
			)
		);
	}
}
