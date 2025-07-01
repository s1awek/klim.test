<?php
declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Interceptor\Interception;

use OmnibusProVendor\Psr\Clock\ClockInterface;
use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Product\ProductPricing;
use WPDesk\Omnibus\Core\Repository\Repository;

final class ChangedRegularPriceInterception implements PriceInterception {

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
		if ( ! array_key_exists( 'regular_price', $product->get_changes() ) ) {
			return null;
		}

		[ 'regular_price' => $updated_price ] = $product->get_changes();
		[ 'regular_price' => $old_price ]     = $product->get_data();

		if ( $updated_price === $old_price ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			return null;
		}

		$previous_entry = $this->repository->find_price_awaiting_change(
			$product->get_id(),
			$this->number_formatter->parse( $old_price ),
			false,
			null
		);

		if ( $previous_entry instanceof HistoricalPrice ) {
			$previous_entry->set_changed( $this->clock->now() );
			return $previous_entry;
		}

		return null;
	}
}
