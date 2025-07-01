<?php
declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Interceptor\Interception;

use OmnibusProVendor\Psr\Clock\ClockInterface;
use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Product\ProductPricing;
use WPDesk\Omnibus\Core\Repository\Repository;

final class ChangedSaleExpirationDateInterception implements PriceInterception {

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

		if ( ! array_key_exists( 'date_on_sale_to', $product->get_changes() ) ) {
			return null;
		}

		[ 'date_on_sale_to' => $updated_expiration_date ] = $product->get_changes();
		[ 'date_on_sale_to' => $old_expiration_date ]     = $product->get_data();

		if ( $updated_expiration_date == $old_expiration_date ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			return null;
		}

		if (
			$old_expiration_date instanceof \DateTimeInterface &&
			$this->clock->now() > $old_expiration_date
		) {
			return null;
		}

		$previous_entry = $this->repository->find_price_awaiting_change(
			$product->get_id(),
			$this->number_formatter->parse( $product->get_sale_price() ),
			true,
			$old_expiration_date ?: null
		);

		if ( $previous_entry instanceof HistoricalPrice ) {
			if (
				$updated_expiration_date instanceof \DateTimeInterface &&
				$this->clock->now() > $updated_expiration_date
			) {
				$previous_entry->set_changed( $this->clock->now() );
			} else {
				$previous_entry->set_changed( $updated_expiration_date );
			}
			return $previous_entry;
		}

		return null;
	}
}
