<?php

declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Shortcode;

use WPDesk\Omnibus\Core\Utils\Hookable;

class ShortcodePriceMessage extends AbstractShortcode implements Hookable {

	private const SHORTCODE = 'omnibus_price_message';

	public function hooks(): void {
		add_shortcode( self::SHORTCODE, [ $this, 'render_shortcode' ] );
	}

	public function render_shortcode( $atts ): string {

		$product = $this->get_product_from_attributes( $atts );
		return $this->get_shortcode_string( $product );
	}
}
