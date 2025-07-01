<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Visibility;

use WPDesk\Omnibus\Core\HistoricalPrice;

class VariableNotModifiedSpecification extends NotModifiedVisibilitySpecification {

	public function should_show( HistoricalPrice $price, \WC_Product $product ): bool {
		if (
			$product instanceof \WC_Product_Variable &&
			$this->settings->get( 'variant_display_method' ) === 'cumulative'
		) {
			return array_reduce(
				$product->get_available_variations( 'objects' ),
				function ( bool $carry, \WC_Product_Variation $variation ) use ( $price ) {
					return $carry && parent::should_show( $price, $variation );
				},
				true
			);
		}

		return parent::should_show( $price, $product );
	}
}
