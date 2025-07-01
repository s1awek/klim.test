<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Utils;

class NumberFormatter extends \NumberFormatter {

	public function __construct( $locale = null, $style = \NumberFormatter::DECIMAL, $pattern = null ) {
		parent::__construct( $locale ?: get_locale(), $style );
	}

	/**
	 * Kept for backward compatibility. This overwrite may be deleted in future versions.
	 */
	public static function create(
		$locale = null,
		$style = \NumberFormatter::DECIMAL,
		$pattern = null
	): ?\NumberFormatter {
		return new self( $locale, $style );
	}

	/**
	 * A little improvement over native method, which always returns float to avoid casting by
	 * client, even when it's not a numeric string (thus returning 0.0).
	 */
	public function parse( $numeric_string, $type = \NumberFormatter::TYPE_DOUBLE, &$offset = null ): float {
		return parent::parse( $numeric_string, $type, $offset ) ?: (float) $numeric_string;
	}
}
