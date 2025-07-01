<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Repository;

use OmnibusProVendor\Psr\Clock\ClockInterface;
use WPDesk\Omnibus\Core\Database\Query\Expression\CompositeExpression;
use WPDesk\Omnibus\Core\Database\Query\QueryBuilder;
use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Migrations\Schema;
use WPDesk\Omnibus\Core\Multicurrency\Client\CurrencyResolver;
use WPDesk\Omnibus\Core\Multicurrency\Client\RawDefaultCurrencyResolver;
use WPDesk\Omnibus\Core\Settings;

class PriceRepository implements Repository {

	/** @var \wpdb */
	protected $wpdb;

	/** @var HistoricalPriceHydrator */
	protected $hydrator;

	/** @var ClockInterface */
	protected $clock;

	/** @var Settings */
	protected $settings;

	/** This is used only to maintain backward compatibility. */
	private CurrencyResolver $currency_resolver;

	public function __construct(
		\wpdb $wpdb,
		HistoricalPriceHydrator $hydrator,
		Settings $settings,
		ClockInterface $clock
	) {
		$this->wpdb              = $wpdb;
		$this->hydrator          = $hydrator;
		$this->clock             = $clock;
		$this->settings          = $settings;
		$this->currency_resolver = new RawDefaultCurrencyResolver();
	}

	protected function create_query_builder( string $alias = null ): QueryBuilder {
		return ( new QueryBuilder( $this->wpdb ) )
			->select( $alias ?? '*' )
			->from( Schema::price_logger_table_name(), $alias );
	}

	public function find_price_awaiting_change( int $product_id, float $price, bool $reduced_price, ?\DateTimeInterface $changed = null, ?string $currency = null ): ?HistoricalPrice {
		if ( $currency === null ) {
			$currency = $this->currency_resolver->get_currency();
			@trigger_error(
				sprintf(
					'Not specifing target currency for price search is deprecated. Pass "$currency" argument to "%s()"',
					__METHOD__
				),
				\E_USER_DEPRECATED
			);
		}
		$qb = $this->create_query_builder();
		$qb->where(
			'product_id = %1$d',
			'currency = \'%2$s\'',
			'price = %3$f',
			'reduced_price = %4$d'
		)
			->orderBy( 'created', 'DESC' )
			->setMaxResults( 1 )
			->setParameter( '%1$d', $product_id )
			->setParameter( '%2$s', $currency )
			->setParameter( '%3$f', $price )
			->setParameter( '%4$d', $reduced_price ? 1 : 0 );

		if ( $changed ) {
			$qb->andWhere( 'changed = \'%5$s\'' )
				->setParameter( '%5$s', $changed->format( 'Y-m-d G:i:s' ) );
		} else {
			$qb->andWhere(
				CompositeExpression::or(
					'changed IS NULL',
					'changed >= \'%5$s\'',
				)
			)
				->setParameter( '%5$s', $this->clock->now()->format( 'Y-m-d G:i:s' ) );
		}

		$result = $qb->execute_query();

		if ( isset( $result[0] ) ) {
			return $this->hydrator->hydrate( $result[0] );
		}

		return null;
	}

	public function find_upcoming_sale_price( int $product_id, float $price, \DateTimeInterface $created, ?string $currency = null ): ?HistoricalPrice {
		if ( $currency === null ) {
			$currency = $this->currency_resolver->get_currency();
			@trigger_error(
				sprintf(
					'Not specifing target currency for price search is deprecated. Pass "$currency" argument to "%s()"',
					__METHOD__
				),
				\E_USER_DEPRECATED
			);
		}
		$qb = $this->create_query_builder();
		$qb->where(
			'product_id = %1$d',
			'currency = \'%2$s\'',
			'price = %3$f',
			'reduced_price = 1',
			'created = \'%4$s\''
		)
			->setMaxResults( 1 )
			->orderBy( 'created', 'DESC' )
			->setParameter( '%1$d', $product_id )
			->setParameter( '%2$s', $currency )
			->setParameter( '%3$f', $price )
			->setParameter( '%4$s', $created->format( 'Y-m-d G:i:s' ) );

		$result = $qb->execute_query();

		if ( isset( $result[0] ) ) {
			return $this->hydrator->hydrate( $result[0] );
		}

		return null;
	}

	public function find_last_similar( HistoricalPrice $price ): HistoricalPrice {
		$qb = $this->create_query_builder();
		$qb->where(
			'product_id = %1$d',
			'currency = \'%2$s\'',
			'reduced_price = %3$d'
		)
			->orderBy( 'created', 'DESC' )
			->setMaxResults( 1 )
			->setParameter( '%1$d', $price->get_product_id() )
			->setParameter( '%2$s', $price->get_currency() )
			->setParameter( '%3$d', $price->is_reduced_price() ? 1 : 0 );

		$result = $qb->execute_query();

		if ( isset( $result[0] ) ) {
			return $this->hydrator->hydrate( $result[0] );
		}

		return $this->hydrator->hydrate( [] );
	}

	public function find_by_products_id( array $products_id ): array {
		if ( empty( $products_id ) ) {
			return [];
		}

		$qb = $this->create_query_builder();
		$qb->where(
			'product_id IN (%1$s)'
		)
			->orderBy( 'created', 'DESC' )
			->setMaxResults( 20 )
			->setParameter( '%1$s', join( ',', $products_id ) );

		$result = [];
		foreach ( $qb->execute_query() as $row ) {
			$result[] = $this->hydrator->hydrate( $row );
		}

		return $result;
	}

	/**
	 * @param array<string, mixed> $where
	 * @param array<string, 'ASC'|'DESC'> $order_by
	 *
	 * @return HistoricalPrice[]
	 */
	public function find_by( array $where = [], array $order_by = [] ): array {
		$qb = $this->create_query_builder()
			->setMaxResults( 20 );

		$placeholder_count = 1;
		foreach ( $where as $key => $value ) {
			if ( is_array( $value ) ) {
				$qb->andWhere( $key . " IN ('" . implode( "','", array_map( 'esc_sql', $value ) ) . "')" );
			} else {
				$qb->andWhere( $key . ' = %' . $placeholder_count . '$s' );
				$qb->setParameter( '%' . $placeholder_count . '$s', $value );
				++$placeholder_count;
			}
		}

		if ( count( $order_by ) === 1 ) {
			[ $sort_column ] = array_keys( $order_by );
			[ $sort_order ]  = array_values( $order_by );

			$qb->orderBy( $sort_column, $sort_order );
		}

		$result = [];
		foreach ( $qb->execute_query() as $row ) {
			$result[] = $this->hydrator->hydrate( $row );
		}

		return $result;
	}
}
