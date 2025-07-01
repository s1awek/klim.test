<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Formatter;

use WPDesk\Omnibus\Core\SettingsBag;
use WPDesk\Omnibus\Core\HistoricalPrice;
use OmnibusProVendor\WPDesk\View\Renderer\Renderer;

class RichMessageFormatter implements MessageFormatter {

	/**
	 * @var Renderer
	 */
	protected $renderer;

	/**
	 * @var SettingsBag
	 */
	private $settings;

	public function __construct(
		Renderer $renderer,
		SettingsBag $settings
	) {
		$this->renderer = $renderer;
		$this->settings = $settings;
	}

	public function format_price( HistoricalPrice $entity ): string {
		$raw_message = apply_filters(
			'wpml_translate_single_string',
			$this->settings->get( 'price_message' ) ?: $this->get_default_message(),
			'wpdesk-omnibus',
			'omnibus-price-message'
		);

		$message = str_ireplace(
			[ '{date}', '{price}', '{days}' ],
			[
				$this->renderer->render(
					'date_markup',
					[
						'date'        => $entity->get_changed() ? $entity->get_changed()->format( 'Y-m-d' ) : $entity->get_created()->format( 'Y-m-d' ),
						'date_object' => $entity->get_changed() ? $entity->get_changed() : $entity->get_created(),
					]
				),
				$this->renderer->render(
					'price_markup',
					[
						'price' => wc_price(
							$entity->get_price() ?: (float) $entity->get_product()->get_regular_price()
						),
					]
				),
				$this->settings->get( 'date_interval', 30 ),
			],
			$raw_message
		);

		return $message;
	}

	public function get_default_message(): string {
		return __( 'The lowest price ({date}): {price}', 'wpdesk-omnibus' );
	}
}
