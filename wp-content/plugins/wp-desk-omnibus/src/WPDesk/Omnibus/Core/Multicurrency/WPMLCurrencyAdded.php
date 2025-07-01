<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Multicurrency;

use WPDesk\Omnibus\Core\Batch\Handlers\WPMLMissingCurrencyFill;
use WPDesk\Omnibus\Core\Utils\Hookable;

class WPMLCurrencyAdded implements Hookable {

	private WPMLMissingCurrencyFill $job;

	public function __construct( WPMLMissingCurrencyFill $job ) {
		$this->job = $job;
	}

	public function hooks(): void {
		add_action( 'update_option__wcml_settings', $this, 10, 2 );
	}

	/**
	 * @param array{currency_options?: array<string, mixed>} $old_value
	 * @param array{currency_options?: array<string, mixed>} $new_value
	 */
	public function __invoke( $old_value, $new_value ): void {
		if (
			isset( $old_value['currency_options'], $new_value['currency_options'] ) &&
			count( $new_value['currency_options'] ) > count( $old_value['currency_options'] )
		) {
			$this->job->reset();
		}
	}
}
