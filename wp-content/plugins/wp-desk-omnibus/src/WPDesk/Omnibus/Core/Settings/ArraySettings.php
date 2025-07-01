<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Settings;

use WPDesk\Omnibus\Core\Settings;

class ArraySettings implements Settings {

	/** @var array<string, mixed> */
	private $settings;

	public function __construct( array $settings ) {
		$this->settings = $settings;
	}

	public function has( string $option ): bool {
		return ! empty( $this->settings[ $option ] );
	}

	public function get( string $option, $default = null ): string {
		return (string) ( $this->settings[ $option ] ?? $default );
	}

	public function get_boolean( string $option, bool $default = false ): bool {
		return filter_var( $this->get( $option, $default ), \FILTER_VALIDATE_BOOLEAN );
	}
}
