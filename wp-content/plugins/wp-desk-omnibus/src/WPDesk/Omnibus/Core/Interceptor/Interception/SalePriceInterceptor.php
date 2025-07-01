<?php

declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Interceptor\Interception;

use OmnibusProVendor\Psr\Clock\ClockInterface;
use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Product\ProductPricing;
use WPDesk\Omnibus\Core\Repository\PriceFactory;

/**
 * Intercept sale price with possible 'modifiers' as started and changed date if those dates are set
 * in future. Additional constraint is that sale price with both sale schedule start and end set for
 * past date should be ignored.
 */
final class SalePriceInterceptor implements PriceInterception {

	/** @var \NumberFormatter */
	private $number_formatter;

	/** @var PriceFactory */
	private $factory;

	/** @var ClockInterface */
	private $clock;

	public function __construct(
		\NumberFormatter $number_formatter,
		PriceFactory $factory,
		ClockInterface $clock
	) {
		$this->number_formatter = $number_formatter;
		$this->factory          = $factory;
		$this->clock            = $clock;
	}

	public function intercept( $product ): ?HistoricalPrice {
		$price = $product->get_sale_price( 'edit' );

		if ( empty( $price ) ) {
			return null;
		}

		$historical_price = $this->factory->with_price(
			$this->number_formatter->parse( $price ),
			$product,
			true,
			$product instanceof ProductPricing ? $product->get_currency() : null
		);

		$now = $this->clock->now();

		if ( $this->schedule_set_in_past( $product ) ) {
			return null;
		}

		if ( $product->get_date_on_sale_from() > $now ) {
			$historical_price->set_created( $product->get_date_on_sale_from() );
		}

		if ( $product->get_date_on_sale_to() > $now ) {
			$historical_price->set_changed( $product->get_date_on_sale_to() );
		}

		return $historical_price;
	}

	/**
	 * @param \WC_Product|ProductPricing $product
	 */
	private function schedule_set_in_past( $product ): bool {
		if ( ! $product->get_date_on_sale_from() && ! $product->get_date_on_sale_to() ) {
			return false;
		}

		$now = $this->clock->now();
		return $product->get_date_on_sale_from() < $now
			&& $product->get_date_on_sale_to() < $now;
	}
}
