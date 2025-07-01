<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Transformer;

use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Settings;

/**
 * If price is invalid, set product's regular price instead.
 */
final class InvalidPriceTransformer implements Transformer {

	/** @var Settings */
	private $settings;

	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	public function transform( HistoricalPrice $entity ): HistoricalPrice {
		if ( ! $entity->is_valid() ) {
			if (
				$this->settings->get_boolean( 'use_sale_price' ) &&
				$entity->get_product()->is_on_sale()
			) {
				$price = $entity->get_product()->get_sale_price( 'not-for-view' );
			} else {
				$price = $entity->get_product()->get_regular_price( 'not-for-view' );
			}

			$entity->set_price( (float) $price );
		}
		return $entity;
	}
}
