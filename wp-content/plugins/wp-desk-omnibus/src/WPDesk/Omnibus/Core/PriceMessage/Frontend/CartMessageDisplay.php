<?php

declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Frontend;

use WPDesk\Omnibus\Core\PriceMessage\MessageDisplayer;
use WPDesk\Omnibus\Core\Settings;
use WPDesk\Omnibus\Core\Utils\Hookable;

class CartMessageDisplay implements Hookable {

	/** @var MessageDisplayer */
	private $displayer;

	/**
	 * @var Settings
	 */
	private $settings;


	public function __construct(
		MessageDisplayer $displayer,
		Settings $settings
	) {
		$this->displayer = $displayer;
		$this->settings  = $settings;
	}

	public function hooks(): void {
		if ( ! $this->settings->has( 'cart_display_hook' ) ) {
			return;
		}

		add_action(
			$this->settings->get( 'cart_display_hook' ),
			function ( $product = null ): void {
				if ( ! is_cart() ) {
					return;
				}

				if ( is_array( $product ) ) {
					if ( isset( $product['variation_id'] ) && ! empty( $product['variation_id'] ) ) {
						$product = wc_get_product( $product['variation_id'] );
					} elseif ( isset( $product['product_id'] ) ) {
						$product = wc_get_product( $product['product_id'] );
					}
				}
				$this->displayer->output( $product );
			}
		);
	}
}
