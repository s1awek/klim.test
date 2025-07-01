<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Frontend;

use WPDesk\Omnibus\Core\PriceMessage\MessageDisplayer;
use WPDesk\Omnibus\Core\Settings;
use WPDesk\Omnibus\Core\Utils\Hookable;

class ArchiveMessageDisplay implements Hookable {

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
		if ( ! $this->settings->has( 'archive_display_hook' ) ) {
			return;
		}

		add_action(
			$this->settings->get( 'archive_display_hook' ),
			function ( $product = null ): void {
				if ( ! is_archive() ) {
					return;
				}
				$this->displayer->output( $product );
			}
		);
	}
}
