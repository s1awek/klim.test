<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Multicurrency\Client;

/**
 * Rely on raw database value, as calling {@see get_woocommerce_currency()} may be altered by filters. This way it also makes the class safe for both front-end and back-end requests (used during price presentation and price persistence).
 */
final class RawDefaultCurrencyResolver implements CurrencyResolver {

	public function get_currency(): string {
		return get_option( 'woocommerce_currency' );
	}
}
