<?php
declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Interceptor\Interception;

use OmnibusProVendor\Psr\Clock\ClockInterface;
use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Product\ProductPricing;
use WPDesk\Omnibus\Core\Repository\Repository;

final class ChangedSaleStartDateInterception implements PriceInterception {

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

		if (
			$old_start_date instanceof \DateTimeInterface &&
			$this->clock->now() > $old_start_date
		) {
			return null;
		}

		$historical_price = $this->repository->find_upcoming_sale_price(
			$product->get_id(),
			$this->number_formatter->parse( $product->get_sale_price() ),
			$old_start_date ?: $this->clock->now()
		);

		if ( $historical_price instanceof HistoricalPrice ) {
			if ( $this->clock->now() > $updated_start_date ) {
				$historical_price->set_created( $this->clock->now() );
			} else {
				$historical_price->set_created( $updated_start_date );
			}
			return $historical_price;
		}

		return null;
	}
}
