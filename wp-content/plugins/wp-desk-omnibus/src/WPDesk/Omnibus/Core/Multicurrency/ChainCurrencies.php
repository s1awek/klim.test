<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Multicurrency;

use WPDesk\Omnibus\Core\Product\RegularPricing;

class ChainCurrencies implements AvailableCurrencies {

	/** @var AvailableCurrencies[] */
	private array $currencies;

	/** @param AvailableCurrencies[] $currencies */
	public function __construct( array $currencies ) {
		$this->currencies = $currencies;
	}

	public function getIterator(): \Traversable {
		foreach ( $this->currencies as $currency ) {
			yield from $currency->getIterator();
		}
	}

	public function codes(): \Traversable {
		foreach ( $this->currencies as $currency ) {
			yield from $currency->codes();
		}
	}
}
