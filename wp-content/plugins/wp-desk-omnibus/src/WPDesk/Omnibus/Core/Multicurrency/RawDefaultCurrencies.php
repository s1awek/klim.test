<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Multicurrency;

use WPDesk\Omnibus\Core\Multicurrency\Client\FixedCurrency;
use WPDesk\Omnibus\Core\Product\RegularPricing;

class RawDefaultCurrencies implements AvailableCurrencies {

	public function getIterator(): \Traversable {
		yield get_option( 'woocommerce_currency' ) => static fn ( \WC_Product $product ) => new RegularPricing(
			$product,
			new FixedCurrency(
				get_option( 'woocommerce_currency' )
			)
		);
	}

	public function codes(): \Traversable {
		yield get_option( 'woocommerce_currency' );
	}
}
