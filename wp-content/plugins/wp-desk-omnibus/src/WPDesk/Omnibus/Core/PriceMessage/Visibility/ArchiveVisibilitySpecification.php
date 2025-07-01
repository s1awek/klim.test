<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Visibility;

use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Settings;

/**
 * Do not display variable products in archive view, if we are in "split" display mode.
 */
class ArchiveVisibilitySpecification implements VisibilitySpecification {

	/** @var Settings */
	private $settings;

	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	public function should_show( HistoricalPrice $price, \WC_Product $product ): bool {
		if ( ! $product instanceof \WC_Product_Variable ) {
			return true;
		}

		if ( ! is_archive() ) {
			return true;
		}

		if ( $this->settings->get( 'variant_display_method' ) === 'cumulative' ) {
			return true;
		}

		return false;
	}
}
