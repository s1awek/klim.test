<?php

declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Frontend;

use WPDesk\Omnibus\Core\PriceMessage\MessageDisplayer;
use WPDesk\Omnibus\Core\Settings;
use WPDesk\Omnibus\Core\Utils\Hookable;

class ProductPriceFilterDisplay implements Hookable {

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
		if ( $this->settings->get_boolean( 'append_to_price' ) ) {
			add_filter(
				'woocommerce_get_price_html',
				function ( $price, $product ) {
					ob_start();
					echo '<p>';
					$this->displayer->output( $product );
					echo '</p>';
					$price .= ob_get_clean();
					return $price;
				},
				10,
				2
			);
		}
	}
}
