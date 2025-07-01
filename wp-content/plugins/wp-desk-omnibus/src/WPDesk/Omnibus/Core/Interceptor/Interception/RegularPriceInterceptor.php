<?php
declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Interceptor\Interception;

use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Product\ProductPricing;
use WPDesk\Omnibus\Core\Repository\PriceFactory;

final class RegularPriceInterceptor implements PriceInterception {

	/** @var \NumberFormatter */
	private $number_formatter;

	/** @var PriceFactory */
	private $factory;

	public function __construct(
		\NumberFormatter $number_formatter,
		PriceFactory $factory
	) {
		$this->number_formatter = $number_formatter;
		$this->factory          = $factory;
	}

	public function intercept( $product ): ?HistoricalPrice {
		$price = $product->get_regular_price( 'edit' );

		if ( empty( $price ) ) {
			return null;
		}

		return $this->factory->with_price(
			$this->number_formatter->parse( $price ),
			$product,
			false,
			$product instanceof ProductPricing ? $product->get_currency() : null
		);
	}
}
