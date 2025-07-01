<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Repository;

use OmnibusProVendor\Psr\Clock\ClockInterface;
use WPDesk\Omnibus\Core\Database\Query\QueryBuilder;
use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Migrations\Schema;
use WPDesk\Omnibus\Core\Multicurrency\Client\CurrencyResolver;
use WPDesk\Omnibus\Core\Multicurrency\Client\RawDefaultCurrencyResolver;
use WPDesk\Omnibus\Core\Settings;

abstract class AbstractQuery implements PriceQuery {
	private const REFERENCE_INTERVAL = 30;

	/** @var \wpdb */
	protected $wpdb;

	/** @var HistoricalPriceHydrator */
	protected $hydrator;

	/** @var ClockInterface */
	protected $clock;

	/** @var Settings */
	protected $settings;

	protected CurrencyResolver $currency_resolver;

	public function __construct(
		\wpdb $wpdb,
		HistoricalPriceHydrator $hydrator,
		Settings $settings,
		ClockInterface $clock,
		CurrencyResolver $currency_resolver = null
	) {
		$this->wpdb              = $wpdb;
		$this->hydrator          = $hydrator;
		$this->clock             = $clock;
		$this->settings          = $settings;
		$this->currency_resolver = $currency_resolver ?? new RawDefaultCurrencyResolver();
	}

	protected function create_query_builder( string $alias = null ): QueryBuilder {
		return ( new QueryBuilder( $this->wpdb ) )
			->select( $alias ?? '*' )
			->from( Schema::price_logger_table_name(), $alias );
	}

	/**
	 * @param HistoricalPrice[] $array
	 * @param int[] $keys
	 *
	 * @return HistoricalPrice[]
	 */
	protected function fill_missing_keys( array $array, array $keys ): array {
		foreach ( $keys as $id ) {
			if ( ! isset( $array[ $id ] ) ) {
				$array[ $id ] = $this->hydrator->hydrate( [ 'product_id' => $id ] );
			}
		}

		return $array;
	}

	/** @return numeric */
	protected function get_cutoff_interval() {
		return $this->settings->get( 'date_interval', self::REFERENCE_INTERVAL );
	}
}
