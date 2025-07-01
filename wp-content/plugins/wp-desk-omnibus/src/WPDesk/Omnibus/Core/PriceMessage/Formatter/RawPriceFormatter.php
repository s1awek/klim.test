<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Formatter;

use WPDesk\Omnibus\Core\HistoricalPrice;
use OmnibusProVendor\WPDesk\View\Renderer\Renderer;

class RawPriceFormatter implements MessageFormatter {

	/**
	 * @var Renderer
	 */
	protected $renderer;

	public function __construct( Renderer $renderer ) {
		$this->renderer = $renderer;
	}

	public function format_price( HistoricalPrice $entity ): string {

		return $this->renderer->render(
			'price_markup',
			[
				'price' => wc_price(
					$entity->get_price() ?: (float) $entity->get_product()->get_regular_price()
				),
			]
		);
	}
}
