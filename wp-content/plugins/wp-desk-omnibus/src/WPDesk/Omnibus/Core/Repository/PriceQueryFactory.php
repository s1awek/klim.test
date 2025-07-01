<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Repository;

use OmnibusProVendor\Psr\Clock\ClockInterface;
use WPDesk\Omnibus\Core\Multicurrency\Client\CurrencyResolverFactory;
use WPDesk\Omnibus\Core\Multicurrency\Client\FixedCurrency;
use WPDesk\Omnibus\Core\Settings;

/**
 * Decide which query instance to use, based on global application settings.
 */
final class PriceQueryFactory {

	/** @var \wpdb */
	private $wpdb;

	/** @var HistoricalPriceHydrator */
	private $hydrator;

	/** @var Settings */
	private $settings;

	/** @var ClockInterface */
	private $clock;

	private CurrencyResolverFactory $currency_resolver_factory;

	public function __construct(
		\wpdb $wpdb,
		HistoricalPriceHydrator $hydrator,
		Settings $settings,
		ClockInterface $clock,
		CurrencyResolverFactory $currency_resolver_factory
	) {
		$this->wpdb                      = $wpdb;
		$this->hydrator                  = $hydrator;
		$this->settings                  = $settings;
		$this->clock                     = $clock;
		$this->currency_resolver_factory = $currency_resolver_factory;
	}

	public function get_price_query(): PriceQuery {
		if ( $this->settings->get( 'date_cutoff_method' ) === 'sale_date' ) {
			return new PromotionRelativeHistoricalPriceRepository(
				$this->wpdb,
				$this->hydrator,
				$this->settings,
				$this->clock,
				$this->currency_resolver_factory->get_resolver()
			);
		}

		return new HistoricalPriceRepository(
			$this->wpdb,
			$this->hydrator,
			$this->settings,
			$this->clock,
			$this->currency_resolver_factory->get_resolver()
		);
	}

	public function with_currency( string $currency ): PriceQuery {
		if ( $this->settings->get( 'date_cutoff_method' ) === 'sale_date' ) {
			return new PromotionRelativeHistoricalPriceRepository(
				$this->wpdb,
				$this->hydrator,
				$this->settings,
				$this->clock,
				new FixedCurrency( $currency )
			);
		}

		return new HistoricalPriceRepository(
			$this->wpdb,
			$this->hydrator,
			$this->settings,
			$this->clock,
			new FixedCurrency( $currency )
		);
	}
}
