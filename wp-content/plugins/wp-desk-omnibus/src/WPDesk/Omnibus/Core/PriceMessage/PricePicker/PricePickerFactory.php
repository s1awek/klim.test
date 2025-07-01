<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\PricePicker;

use WPDesk\Omnibus\Core\PriceMessage\Visibility\OnSaleVisibilitySpecification;
use WPDesk\Omnibus\Core\Repository\PriceFactory;
use WPDesk\Omnibus\Core\Repository\PriceQuery;
use WPDesk\Omnibus\Core\Settings;

class PricePickerFactory {

	/** @var PriceQuery */
	private $price_query;

	/** @var Settings */
	private $settings;

	/** @var OnSaleVisibilitySpecification */
	private $visibility;

	/** @var PriceFactory */
	private $factory;

	public function __construct(
		PriceQuery $price_query,
		OnSaleVisibilitySpecification $visibility,
		Settings $settings,
		PriceFactory $factory
	) {
		$this->price_query = $price_query;
		$this->settings    = $settings;
		$this->visibility  = $visibility;
		$this->factory     = $factory;
	}

	public function with_product(
		\WC_Product $product
	): LowestPricePicker {
		if ( ! $this->visibility->should_show( $this->factory->with_product( $product ), $product ) ) {
			return new NullPricePicker();
		}

		if ( $product instanceof \WC_Product_Variable ) {
			if ( $this->settings->get( 'variant_display_method' ) === 'cumulative' ) {
				return new VariableProductPricePicker( $this->price_query );
			}

			return new NullPricePicker();
		}

		return new SingleProductPricePicker( $this->price_query );
	}
}
