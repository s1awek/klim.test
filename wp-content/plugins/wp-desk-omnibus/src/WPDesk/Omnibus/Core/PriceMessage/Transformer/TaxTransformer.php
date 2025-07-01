<?php

declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Transformer;

use WPDesk\Omnibus\Core\HistoricalPrice;

final class TaxTransformer implements Transformer {

	public function transform( HistoricalPrice $entity ): HistoricalPrice {
		if ( get_option( 'woocommerce_tax_display_shop' ) === 'incl' ) {
			$entity->set_price(
				(float) wc_get_price_including_tax(
					$entity->get_product(),
					[ 'price' => $entity->get_price() ]
				)
			);
		}

		return $entity;
	}
}
