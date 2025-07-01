<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\PricePicker;

use WPDesk\Omnibus\Core\HistoricalPrice;

interface LowestPricePicker {

	public function get_price( \WC_Product $product ): HistoricalPrice;
}
