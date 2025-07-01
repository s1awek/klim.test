<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Multicurrency\Client;

final class WPMLCurrencyResolver implements CurrencyResolver {

	private RawDefaultCurrencyResolver $default_resolver;

	public function __construct( RawDefaultCurrencyResolver $currency_resolver ) {
		$this->default_resolver = $currency_resolver;
	}

	public function get_currency(): string {
		if ( function_exists( '\WCML\functions\getClientCurrency' ) ) {
			return \WCML\functions\getClientCurrency();
		}

		return $this->default_resolver->get_currency();
	}
}
