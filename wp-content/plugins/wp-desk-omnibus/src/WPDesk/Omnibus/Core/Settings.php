<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core;

interface Settings {

	/**
	 * @param string $option
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( string $option, $default = null );

	public function get_boolean( string $option, bool $default = false ): bool;

	public function has( string $option ): bool;
}
