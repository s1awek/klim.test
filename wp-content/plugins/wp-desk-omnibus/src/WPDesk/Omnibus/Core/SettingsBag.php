<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core;

class SettingsBag implements Settings {

	/**
	 * @var array<string, string|false>
	 */
	private static $cache = [];

	public function has( string $option ): bool {
		self::$cache[ $option ] = \get_option( 'omnibus_' . $option );
		return ! empty( self::$cache[ $option ] );
	}

	public function get( string $option, $default = null ): string {
		if ( ! empty( self::$cache[ $option ] ) ) {
			return self::$cache[ $option ];
		}
		return self::$cache[ $option ] = (string) \get_option( 'omnibus_' . $option, $default ); // phpcs:ignore
	}

	public function get_boolean( string $option, bool $default = false ): bool {
		return filter_var( $this->get( $option, $default ), \FILTER_VALIDATE_BOOLEAN );
	}
}
