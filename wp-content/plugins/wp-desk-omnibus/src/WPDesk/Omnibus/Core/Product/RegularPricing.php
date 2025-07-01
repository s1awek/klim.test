<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Product;

use WPDesk\Omnibus\Core\Multicurrency\Client\CurrencyResolver;

class RegularPricing implements ProductPricing {

	private \WC_Product $product;

	private CurrencyResolver $currency_resolver;

	public function __construct( \WC_Product $product, CurrencyResolver $currency_resolver ) {
		$this->product           = $product;
		$this->currency_resolver = $currency_resolver;
	}

	public function get_id(): int {
		return $this->product->get_id();
	}

	public function get_regular_price(): string {
		return (string) $this->product->get_regular_price();
	}

	public function get_sale_price(): string {
		return (string) $this->product->get_sale_price();
	}

	public function get_date_on_sale_from(): ?\DateTimeInterface {
		return $this->product->get_date_on_sale_from();
	}

	public function get_date_on_sale_to(): ?\DateTimeInterface {
		return $this->product->get_date_on_sale_to();
	}

	public function get_currency(): string {
		return $this->currency_resolver->get_currency();
	}

	public function get_changes(): array {
		return $this->product->get_changes();
	}

	public function get_data(): array {
		return $this->product->get_data();
	}
}
