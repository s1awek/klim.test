<?php
declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Interceptor\Interception;

use OmnibusProVendor\Psr\Clock\ClockInterface;
use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Repository\Repository;

/**
 * When we've got a pair of sale prices and one of them is set in future, we need to update price
 * changed date for the previous one. Additional conditions are that previous sale start date must
 * equal changed date from previous historical price and those dates has to be set in future.
 *
 * Considering current date is 2022-10-03 the following schema would trigger the update.
 *
 * price | created                   | changed
 * 90    | 2022-10-10 (future start) | 2022-10-30 (schedule end)
 * 90    | 2022-10-01                | 2022-10-10 (assume changed date for next schedule)
 */
final class PreviousSalePriceFromSaleStartChange implements PriceInterception {

	/** @var Repository */
	private $repository;

	/**
	 * @var ClockInterface
	 */
	private $clock;

	public function __construct(
		Repository $repository,
		ClockInterface $clock
	) {
		$this->repository = $repository;
		$this->clock      = $clock;
	}

	public function intercept( $product ): ?HistoricalPrice {
		if ( ! $product->get_sale_price() ) {
			return null;
		}

		if ( ! array_key_exists( 'date_on_sale_from', $product->get_changes() ) ) {
			return null;
		}

		[ 'date_on_sale_from' => $updated_start_date ] = $product->get_changes();
		[ 'date_on_sale_from' => $old_start_date ]     = $product->get_data();

		if ( $updated_start_date == $old_start_date ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			return null;
		}

		$historical_price = $this->repository->find_previous(
			$product->get_id(),
			true,
			$old_start_date
		);

		if ( $historical_price instanceof HistoricalPrice ) {

			if ( $historical_price->get_changed() < $this->clock->now() ) {
				return null;
			}

			if ( $historical_price->get_changed() !== $old_start_date ) {
				return null;
			}

			$historical_price->set_changed( $updated_start_date );
			return $historical_price;
		}

		return null;
	}
}
