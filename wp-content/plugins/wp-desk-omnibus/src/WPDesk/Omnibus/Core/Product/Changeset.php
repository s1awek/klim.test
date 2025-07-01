<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Product;

/**
 * @phpstan-type PricingChanges array{
 *     regular_price?: string,
 *     sale_price?: string,
 *     date_on_sale_from?: ?\DateTimeInterface,
 *     date_on_sale_to?: ?\DateTimeInterface,
 * }&array<string, mixed>
 */
interface Changeset {

	/** @return PricingChanges */
	public function changes(): array;

	/** @return PricingChanges */
	public function original(): array;
}
