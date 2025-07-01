<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Interceptor\Interception;

use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Product\ProductPricing;

interface PriceInterception {

	/**
	 * @param \WC_Product|ProductPricing $product
	 *
	 * @since 2.1.0 Allow {@see ProductPricing} as $product argument. Deprecate usage of \WC_Product.
	 */
	public function intercept( $product ): ?HistoricalPrice;
}
