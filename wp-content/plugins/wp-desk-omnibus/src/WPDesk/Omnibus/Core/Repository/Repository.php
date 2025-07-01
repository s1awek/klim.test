<?php

namespace WPDesk\Omnibus\Core\Repository;

use WPDesk\Omnibus\Core\HistoricalPrice;

interface Repository {
	/**
	 * Search for a modifiable price entity, which either hasn't been changed or has change date set
	 * in future.
	 */
	public function find_price_awaiting_change( int $product_id, float $price, bool $is_sale, ?\DateTimeInterface $changed = null /*, string $currency */ ): ?HistoricalPrice;

	/**
	 * Search for a modifiable reduced price entity, which start date is set in the future.
	 */
	public function find_upcoming_sale_price( int $product_id, float $price, \DateTimeInterface $created /*, string $currency */ ): ?HistoricalPrice;

	public function find_last_similar( HistoricalPrice $price ): HistoricalPrice;

	/**
	 * @param int[] $products_id
	 *
	 * @return HistoricalPrice[]
	 */
	public function find_by_products_id( array $products_id ): array;

	/* public function find_by( array $where, array $order_by ): array; */
}
