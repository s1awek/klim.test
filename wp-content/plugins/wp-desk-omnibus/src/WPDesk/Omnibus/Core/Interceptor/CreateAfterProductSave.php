<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Interceptor;

use WPDesk\Omnibus\Core\Interceptor\InterceptionPersister;
use WPDesk\Omnibus\Core\Utils\Hookable;

/**
 * Save product prices after WooCommerce flushed all changes to database. Here we only create new entries
 * in database.
 */
class CreateAfterProductSave implements Hookable {

	/** @var InterceptionPersister */
	private $persister;

	public function __construct( InterceptionPersister $persister ) {
		$this->persister = $persister;
	}

	public function hooks(): void {
		add_action( 'woocommerce_after_product_object_save', $this );
	}

	/**
	 * @param \WC_Product $product
	 */
	public function __invoke( $product ): void {
		$this->persister->intercept_product_prices( $product );
	}
}
