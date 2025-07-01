<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Formatter;

use WPDesk\Omnibus\Core\Settings;
use WPDesk\Omnibus\Core\Utils\Hookable;

class TranslationRegistrator implements Hookable {

	private Settings $settings;

	private RichMessageFormatter $formatter;

	public function __construct( Settings $settings, RichMessageFormatter $formatter ) {
		$this->settings  = $settings;
		$this->formatter = $formatter;
	}

	public function hooks(): void {
		add_action( 'init', $this );
	}

	public function __invoke(): void {
		$lang = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : get_bloginfo( 'language' );

		\icl_register_string(
			'wpdesk-omnibus',
			'omnibus-price-message',
			$this->settings->get( 'price_message' ) ?: $this->formatter->get_default_message(),
			false,
			$lang
		);
	}
}
