<?php

namespace WPDesk\Omnibus\Core\PriceMessage\Shortcode;

use WPDesk\Omnibus\Core\PriceMessage\MessageDisplayer;

abstract class AbstractShortcode {

	/** @var MessageDisplayer */
	protected $displayer;

	public function __construct( MessageDisplayer $displayer ) {
		$this->displayer = $displayer;
	}

	protected function get_product_from_attributes( $atts ): ?\WC_Product {

		if ( ! isset( $atts['id'] ) ) {
			return null;
		}

		$product = wc_get_product( (int) $atts['id'] );

		if ( ! $product instanceof \WC_Product ) {
			return null;
		}

		return $product;
	}

	protected function get_shortcode_string( ?\WC_Product $product ): string {
		\ob_start();
		$this->displayer->output( $product );
		return \ob_get_clean();
	}
}
