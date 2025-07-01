<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Repository;

use OmnibusProVendor\Psr\Clock\ClockInterface;
use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Multicurrency\Client\RawDefaultCurrencyResolver;
use WPDesk\Omnibus\Core\Product\ProductPricing;
use WPDesk\Omnibus\Core\Settings;

class PriceFactory {

	/** @var Settings */
	private $settings;

	/** @var ClockInterface */
	private $clock;

	/** Only to maintain backward compatibility */
	private RawDefaultCurrencyResolver $currency_resolver;

	public function __construct( Settings $settings, ClockInterface $clock ) {
		$this->settings          = $settings;
		$this->clock             = $clock;
		$this->currency_resolver = new RawDefaultCurrencyResolver();
	}

	/**
	 * @since 2.1.0 Parameter $currency added. Parameter $product accepts now ProductPricing, usage of \WC_Product is deprecated.
	 *
	 * @param \WC_Product|ProductPricing $product
	 * @param bool $reduced Flag determining whether we deal with reduced or regular price
	 *
	 * @return HistoricalPrice
	 */
	public function with_price(
		float $price,
		$product,
		bool $reduced,
		?string $currency = null
	): HistoricalPrice {
		return new HistoricalPrice(
			null,
			$product->get_id(),
			$price,
			$this->clock->now(),
			$reduced,
			null,
			$currency ?? $this->currency_resolver->get_currency()
		);
	}

	/**
	 * Create a new entity from existing one.
	 */
	public function refresh( HistoricalPrice $historical_price ): HistoricalPrice {
		return new HistoricalPrice(
			null,
			$historical_price->get_product_id(),
			$historical_price->get_price(),
			$historical_price->get_created(),
			$historical_price->is_reduced_price(),
			$historical_price->get_changed(),
			$historical_price->get_currency()
		);
	}

	/**
	 * TODO: This method is a bit misleading, as it is used for front-end requests, which depend on
	 * current currency. Possibly, it would be the best to move it to own class.
	 */
	public function with_product( \WC_Product $product ): HistoricalPrice {
		if ( $this->settings->get_boolean( 'use_sale_price' ) && $product->is_on_sale() ) {
			$price   = (float) $product->get_sale_price( 'not-for-view' );
			$reduced = true;
		} else {
			$price   = (float) $product->get_regular_price( 'not-for-view' );
			$reduced = false;
		}

		return new HistoricalPrice(
			null,
			$product->get_id(),
			$price,
			$this->clock->now(),
			$reduced,
			null,
			$this->currency_resolver->get_currency()
		);
	}
}
