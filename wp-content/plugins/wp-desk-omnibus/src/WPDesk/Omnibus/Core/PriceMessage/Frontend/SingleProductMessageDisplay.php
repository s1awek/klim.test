<?php

declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Frontend;

use WPDesk\Omnibus\Core\PriceMessage\MessageDisplayer;
use WPDesk\Omnibus\Core\Settings;
use WPDesk\Omnibus\Core\Utils\Hookable;

class SingleProductMessageDisplay implements Hookable {

	/** @var MessageDisplayer */
	private $displayer;

	/** @var Settings */
	private $settings;

	public function __construct(
		MessageDisplayer $displayer,
		Settings $settings
	) {
		$this->displayer = $displayer;
		$this->settings  = $settings;
	}

	public function hooks(): void {
		add_action(
			$this->settings->get( 'display_hook', 'woocommerce_product_meta_start' ),
			function ( $product = null ): void {
				$this->displayer->output( $product );
			}
		);
	}
}
