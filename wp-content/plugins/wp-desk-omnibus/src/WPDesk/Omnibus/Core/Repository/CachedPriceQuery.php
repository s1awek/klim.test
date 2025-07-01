<?php

declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Repository;

use WPDesk\Omnibus\Core\Cache\WpCachePool;
use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Multicurrency\Client\CurrencyResolver;

/**
 * Cache frontend query results.
 */
final class CachedPriceQuery implements PriceQuery {

	private PriceQuery $query;

	private WpCachePool $cache;

	private CurrencyResolver $currency;

	public function __construct( PriceQuery $query, WpCachePool $cache, CurrencyResolver $currency ) {
		$this->query    = $query;
		$this->cache    = $cache;
		$this->currency = $currency;
	}

	public function find_one_with_lowest_price( $product_id ): HistoricalPrice {
		return $this->cache->get(
			sprintf(
				'omnibus.query.%s.simple.%s',
				$this->currency->get_currency(),
				implode( ',', (array) $product_id )
			),
			fn () => $this->query->find_one_with_lowest_price( $product_id ),
			\DAY_IN_SECONDS
		) ?? $this->query->find_one_with_lowest_price( $product_id );
	}

	/**
	 * @param \WC_Product_Variable $variable_product
	 *
	 * @return HistoricalPrice[]
	 */
	public function find_cheapest_for_variations( $variable_product ): array {
		$children = [];
		if ( $variable_product instanceof \WC_Product_Variable ) {
			$children = $variable_product->get_children();
		} elseif ( is_array( $variable_product ) ) {
			// Backward compatibility for passing an array of children.
			$children = $variable_product;
		}

		$cache_id = $variable_product instanceof \WC_Product_Variable ? $variable_product->get_id() : implode( ',', $children );

		return $this->cache->get(
			sprintf(
				'omnibus.query.%s.variable.%s',
				$this->currency->get_currency(),
				$cache_id
			),
			fn () => $this->query->find_cheapest_for_variations( $variable_product ),
			\DAY_IN_SECONDS
		) ?? $this->query->find_cheapest_for_variations( $variable_product );
	}
}
