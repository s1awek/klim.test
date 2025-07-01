<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Multicurrency;

use WPDesk\Omnibus\Core\Product\ProductPricing;

/**
 * Which currencies are available store-wide.
 *
 * @extends \IteratorAggregate<string, callable(\WC_Product): ProductPricing>
 */
interface AvailableCurrencies extends \IteratorAggregate {

	/** @return \Traversable<string> */
	public function codes(): \Traversable;
}
