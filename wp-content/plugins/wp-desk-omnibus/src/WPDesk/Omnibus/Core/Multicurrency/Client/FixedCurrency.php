<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Multicurrency\Client;

final class FixedCurrency implements CurrencyResolver {

	private string $currency;

	public function __construct( string $currency ) {
		$this->currency = $currency;
	}

	public function get_currency(): string {
		return $this->currency;
	}
}
