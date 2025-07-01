<?php
declare(strict_types=1);

namespace WPDesk\Omnibus\Core\PriceMessage\Transformer;

use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Settings;

final class VariableInvalidPriceTransformer implements Transformer {

	/** @var Settings */
	private $settings;

	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	public function transform( HistoricalPrice $entity ): HistoricalPrice {
		if ( $entity->is_valid() ) {
			return $entity;
		}

		$product = $entity->get_product();
		if ( ! $product instanceof \WC_Product_Variable ) {
			return $entity;
		}

		$price = array_reduce(
			$product->get_available_variations( 'objects' ),
			function ( $carry, \WC_Product_Variation $variation ) {
				if (
					$this->settings->get_boolean( 'use_sale_price' ) &&
					$variation->is_on_sale()
				) {
					$price = (float) $variation->get_sale_price( 'not-for-view' );
				} else {
					$price = (float) $variation->get_regular_price( 'not-for-view' );
				}

				if ( $carry === null ) {
					return $price;
				} elseif ( $carry < $price ) {
					return $carry;
				} else {
					return $price;
				}
			}
		);

		$entity->set_price( $price ?? 0 );
		return $entity;
	}
}
