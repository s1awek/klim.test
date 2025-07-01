<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Visibility;

use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Settings;

class OnSaleVisibilitySpecification implements VisibilitySpecification {

	/** @var Settings */
	private $settings;

	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	public function should_show( HistoricalPrice $price, \WC_Product $product ): bool {
		if ( $product->is_on_sale() ) {
			return true;
		}

		if ( $this->settings->get_boolean( 'display_only_sale' ) ) {
			return false;
		}

		return true;
	}
}
