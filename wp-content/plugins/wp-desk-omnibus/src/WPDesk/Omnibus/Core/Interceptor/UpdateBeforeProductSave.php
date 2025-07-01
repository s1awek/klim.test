<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Interceptor;

use WPDesk\Omnibus\Core\Interceptor\InterceptionPersister;
use WPDesk\Omnibus\Core\Utils\Hookable;

/**
 * Before any changes are persisted for product, hook to intercept changed values and possibly
 * update our existing price entities. Interceptors included there should only change and never
 * create new entities.
 *
 * When to record a change for previous price entity?
 * 1. Whenever a regular price is changed. (@see ChangedRegularPriceInterception)
 * 2. When a sale price is previously set and it is unset or changed. (@see ChangedSalePriceInterception)
 * 3. When both price types are set and both are changed. (1. & 2.)
 * 4. When a sale price expiration date is set, removed or changed. (@see ChangedSaleExpirationDateInterception)
 * 5. When a sale price start date is set, removed or changed and is further from current time. (Don't
 * update past dates).
 *
 * Case no 4. & 5. are the tricky ones, as we end up with setting changed/created date in future,
 * but that neccessary, as WooCommerce will not notify us about schedule expiration otherwise.
 */
class UpdateBeforeProductSave implements Hookable {

	/** @var InterceptionPersister */
	private $persister;

	public function __construct( InterceptionPersister $persister ) {
		$this->persister = $persister;
	}

	public function hooks(): void {
		add_action( 'woocommerce_before_product_object_save', $this );
	}

	/**
	 * @param \WC_Product $product
	 */
	public function __invoke( $product ): void {
		$this->persister->intercept_product_prices( $product );
	}
}
