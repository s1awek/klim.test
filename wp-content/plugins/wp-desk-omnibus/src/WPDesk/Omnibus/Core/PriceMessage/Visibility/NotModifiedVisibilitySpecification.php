<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Visibility;

use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Repository\PriceFactory;
use WPDesk\Omnibus\Core\Settings;

class NotModifiedVisibilitySpecification implements VisibilitySpecification {

	/** @var PriceFactory */
	private $factory;

	/** @var Settings */
	protected $settings;

	public function __construct( PriceFactory $factory, Settings $settings ) {
		$this->factory  = $factory;
		$this->settings = $settings;
	}

	public function should_show( HistoricalPrice $price, \WC_Product $product ): bool {
		if ( $product instanceof \WC_Product_Variable ) {
			return true;
		}

		if ( $this->settings->get( 'equal_prices' ) === 'no_show' ) {
			$product_as_price = $this->factory->with_product( $product );
			if ( ! $price->is_valid() || $price->equals( $product_as_price ) ) {
				return false;
			}
		}

		return true;
	}
}
