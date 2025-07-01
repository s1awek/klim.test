<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Visibility;

use WPDesk\Omnibus\Core\HistoricalPrice;

interface VisibilitySpecification {

	public function should_show( HistoricalPrice $price, \WC_Product $product ): bool;
}
