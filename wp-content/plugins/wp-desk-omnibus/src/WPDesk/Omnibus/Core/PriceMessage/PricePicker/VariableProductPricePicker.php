<?php

declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\PricePicker;

use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Repository\PriceQuery;

class VariableProductPricePicker implements LowestPricePicker {

	/** @var PriceQuery */
	private $price_query;

	public function __construct( PriceQuery $price_query ) {
		$this->price_query = $price_query;
	}

	public function get_price( \WC_Product $product ): HistoricalPrice {
		return $this->price_query->find_one_with_lowest_price(
			array_merge(
				[ $product->get_id() ],
				$product->get_children()
			)
		);
	}
}
