<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Product;

/**
 * A subset of {@see \WC_Product} class focused on product pricing.
 *
 * @phpstan-import-type PricingChanges from Changeset
 */
interface ProductPricing {

	/**
	 * Original ID of \WC_Product instance.
	 */
	public function get_id(): int;

	public function get_regular_price(): string;

	public function get_sale_price(): string;

	public function get_date_on_sale_from(): ?\DateTimeInterface;

	public function get_date_on_sale_to(): ?\DateTimeInterface;

	public function get_currency(): string;

	/** @return PricingChanges */
	public function get_changes(): array;

	/** @return PricingChanges */
	public function get_data(): array;
}
