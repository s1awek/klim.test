<?php
declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Interceptor\Interception;

use OmnibusProVendor\Psr\Clock\ClockInterface;
use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Product\ProductPricing;
use WPDesk\Omnibus\Core\Repository\Repository;

final class ChangedSalePriceInterception implements PriceInterception {

	/** @var \NumberFormatter */
	private $number_formatter;

	/** @var Repository */
	private $repository;

	/** @var ClockInterface */
	private $clock;

	public function __construct(
		\NumberFormatter $number_formatter,
		Repository $repository,
		ClockInterface $clock
	) {
		$this->number_formatter = $number_formatter;
		$this->repository       = $repository;
		$this->clock            = $clock;
	}

	public function intercept( $product ): ?HistoricalPrice {
		if ( ! array_key_exists( 'sale_price', $product->get_changes() ) ) {
			return null;
		}

		[ 'sale_price' => $updated_sale_price ] = $product->get_changes();
		[ 'sale_price' => $old_sale_price ]     = $product->get_data();

		// Skip new sale price.
		if ( ! is_numeric( $old_sale_price ) && is_numeric( $updated_sale_price ) ) {
			return null;
		}

		if ( $updated_sale_price == $old_sale_price ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			return null;
		}

		$previous_entry = $this->repository->find_price_awaiting_change(
			$product->get_id(),
			$this->number_formatter->parse( $old_sale_price ),
			true,
			null
		);

		if ( $previous_entry instanceof HistoricalPrice ) {
			$previous_entry->set_changed( $this->clock->now() );
			return $previous_entry;
		}

		return null;
	}
}
