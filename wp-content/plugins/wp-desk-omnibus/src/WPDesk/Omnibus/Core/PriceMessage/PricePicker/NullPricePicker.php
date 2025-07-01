<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\PricePicker;

use WPDesk\Omnibus\Core\HistoricalPrice;

/**
 * Sometimes we may just want to use invalid price and skip database
 * call, because actual value is ignored.
 *
 * For example, this is true for variable products, which are only
 * important because of their children, and itself doesn't hold
 * price value.
 */
class NullPricePicker implements LowestPricePicker {

	public function get_price( \WC_Product $product ): HistoricalPrice {
		return new HistoricalPrice( null, $product->get_id(), 0.0, new \DateTime() );
	}
}
