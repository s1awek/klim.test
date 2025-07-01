<?php

namespace WPDesk\Omnibus\Core\Repository;

use WPDesk\Omnibus\Core\HistoricalPrice;

/**
 * Query the lowest price from product's history.
 */
interface PriceQuery {
	/**
	 * Search for the lowest historical price across passed product IDs.
	 * We can search across one, selected product or all it's children (for variable products).
	 *
	 * In case, we don't find the actual lowest price (e.g. product was updates earlier than our
	 * search criteria), by convention we return empty (nulled) historical price with product ID set
	 * to first passed value (in case it's array, we use first element)
	 *
	 * @todo: Think about better solution to this fragile convention
	 *
	 * @param int[]|int $product_id
	 *
	 * @return HistoricalPrice
	 */
	public function find_one_with_lowest_price( $product_id ): HistoricalPrice;

	/**
	 * @param \WC_Product_Variable $variable_product
	 *
	 * @return HistoricalPrice[]
	 *
	 * @since 2.2.5 passing array of variation IDs is deprecated.
	 */
	public function find_cheapest_for_variations( /* \WC_Product_Variable */ $variable_product ): array;
}
