<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Visibility;

use WPDesk\Omnibus\Core\HistoricalPrice;

final class GroupedProductSpecification implements VisibilitySpecification {

	public function should_show( HistoricalPrice $price, \WC_Product $product ): bool {
		if ( $product instanceof \WC_Product_Grouped ) {
			return false;
		}

		return true;
	}
}
