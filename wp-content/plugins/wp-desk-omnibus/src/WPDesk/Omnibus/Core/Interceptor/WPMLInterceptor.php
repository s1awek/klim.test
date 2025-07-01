<?php

namespace WPDesk\Omnibus\Core\Interceptor;

use WPDesk\Omnibus\Core\Interceptor\InterceptionPersister;
use WPDesk\Omnibus\Core\Utils\Hookable;

class WPMLInterceptor implements Hookable {

	private InterceptionPersister $persister;

	public function __construct( InterceptionPersister $persister ) {
		$this->persister = $persister;
	}

	/**
	 * Hook after changeset is calculated {@see ProductChangesetRegistry}.
	 */
	public function hooks(): void {
		add_action( 'wp_insert_post', $this, 11 );
	}

	/**
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function __invoke( $post_id ) {
		$product = wc_get_product( $post_id );

		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		$this->persister->intercept_product_prices( $product );
	}
}
