<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Multicurrency\Client;

/**
 * Determine, which currency should be used e.g. for queries based on current site settings (and request). This only applies for front-end requests.
 */
interface CurrencyResolver {

	/**
	 * @return string ISO 4217 currency code.
	 */
	public function get_currency(): string;
}
