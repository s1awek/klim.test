<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Repository;

use WPDesk\Omnibus\Core\Database\Query\Expression\CompositeExpression;
use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Migrations\Schema;
use WPDesk\Omnibus\Core\Utils\ProductsDateRangeBag;

/**
 * This price query is able to query products with regard to last intercepted promotion date.
 */
class PromotionRelativeHistoricalPriceRepository extends AbstractQuery {

	public function find_one_with_lowest_price( $product_id ): HistoricalPrice {
		if ( empty( $product_id ) ) {
			return $this->hydrator->hydrate( [] );
		}

		$qb                         = $this->create_query_builder();
		[ $bottom_date, $top_date ] = $this->get_cutoff_date( $product_id );
		$qb->where(
			'product_id IN (%1$s)',
			'currency = \'%2$s\'',
		)
			->andWhere(
				CompositeExpression::or(
					// Within referenced range.
					CompositeExpression::and(
						'changed > \'%3$s\'',
						'changed <= \'%4$s\''
					),
					// Current regular price. Reduced are excluded.
					CompositeExpression::and(
						'reduced_price = 0',
						'changed IS NULL',
						'created <= \'%5$s\''
					)
				)
			)
			->orderBy( 'price', 'ASC' )
			->setMaxResults( 1 )
			->setParameter( '%1$s', \implode( ',', (array) $product_id ) )
			->setParameter( '%2$s', $this->currency_resolver->get_currency() )
			->setParameter( '%3$s', $bottom_date )
			->setParameter( '%4$s', $top_date )
			->setParameter( '%5$s', $this->clock->now()->format( 'Y-m-d H:i:s' ) );

		if ( ! $this->settings->get_boolean( 'use_sale_price' ) ) {
			$qb->andWhere( 'reduced_price = 0' );
		}

		$result = $qb->execute_query();

		if ( isset( $result[0] ) ) {
			return $this->hydrator->hydrate( $result[0] );
		}

		$entity = $this->hydrator->hydrate( [] );
		if ( is_int( $product_id ) ) {
			$entity->set_product_id( $product_id );
		} elseif ( is_array( $product_id ) ) {
			$entity->set_product_id( $product_id[0] );
		}

		return $entity;
	}

	/**
	 * When regarding product variations, we may end up with a bottleneck, when user has
	 * a large amount of variations. Because of this, we try to group queries by date (Y-m-d) of
	 * the last price change.
	 *
	 * @param \WC_Product_Variable $variable_product
	 *
	 * @return HistoricalPrice[]
	 */
	public function find_cheapest_for_variations( $variable_product ): array {
		if ( $variable_product instanceof \WC_Product_Variable ) {
			$variable_product = $variable_product->get_children();
		}

		if ( empty( $variable_product ) ) {
			return [];
		}

		$dates = $this->get_cutoff_date_for_product( $variable_product );

		$result = [];
		foreach ( $dates as $products_date_range ) {
			$qb = $this->create_query_builder( 'p1' );
			$qb->select(
				'p1.id',
				'p1.product_id',
				'MIN(p1.created) AS created',
				'p1.price',
				// We ignore value of reduced_price, because our view already filters this value.
				// It would be the best to use ANY_VALUE, but this function is not supported in mariadb.
				'MIN(p1.reduced_price) as reduced_price',
				'MIN(p1.changed) AS changed',
			)
				->from( Schema::price_logger_table_name(), 'p2' )
				->where(
					'p1.product_id = p2.product_id',
					'p1.product_id IN (%1$s)'
				)
				->andWhere(
					'p1.currency = \'%2$s\'',
					'p2.currency = \'%3$s\'',
				)
				->andWhere(
					CompositeExpression::or(
						CompositeExpression::and(
							'p1.changed <= \'%4$s\'',
							'p1.changed > \'%5$s\'',
							'p2.changed <= \'%6$s\'',
							'p2.changed > \'%7$s\'',
						),
						CompositeExpression::and(
							'p1.reduced_price = 0',
							'p1.changed IS NULL',
							'p1.created <= \'%8$s\'',
							'p2.reduced_price = 0',
							'p2.changed IS NULL',
							'p2.created <= \'%9$s\''
						)
					)
				)
				->groupBy( 'p1.product_id', 'p1.price' )
				->having( 'p1.price = min(p2.price)' )
				->setParameter( '%1$s', implode( ',', $products_date_range->get_products() ) )
				->setParameter( '%2$s', $this->currency_resolver->get_currency() )
				->setParameter( '%3$s', $this->currency_resolver->get_currency() )
				->setParameter( '%4$s', $products_date_range->get_start() )
				->setParameter( '%5$s', $products_date_range->get_end() )
				->setParameter( '%6$s', $products_date_range->get_start() )
				->setParameter( '%7$s', $products_date_range->get_end() )
				->setParameter( '%8$s', $this->clock->now()->format( 'Y-m-d H:i:s' ) )
				->setParameter( '%9$s', $this->clock->now()->format( 'Y-m-d H:i:s' ) );

			if ( ! $this->settings->get_boolean( 'use_sale_price' ) ) {
				$qb->andWhere( 'p2.reduced_price = 0' );
			}

			foreach ( $qb->execute_query() as $row ) {
				if ( ! isset( $result[ (int) $row['product_id'] ] ) ) {
					$result[ (int) $row['product_id'] ] = $this->hydrator->hydrate( $row );
				}
			}
		}

		return $this->fill_missing_keys( $result, $variable_product );
	}

	/**
	 * @param int|int[] $product_id
	 *
	 * @return array<string, ProductsDateRangeBag>
	 */
	private function get_cutoff_date_for_product( $product_id ): array {
		if ( empty( $product_id ) ) {
			return [];
		}

		$qb = $this->create_query_builder();
		$qb->select(
			'product_id',
			'MAX(created) as created'
		)
			->where(
				'reduced_price = 1',
				'product_id IN (%1$s)',
				'created <= \'%2$s\'',
				'currency = \'%3$s\'',
			)
			->groupBy( 'product_id' )
			->setParameter( '%1$s', \implode( ',', (array) $product_id ) )
			->setParameter( '%2$s', $this->clock->now()->format( 'Y-m-d H:i:s' ) )
			->setParameter( '%3$s', $this->currency_resolver->get_currency() );

		$result   = [];
		$interval = $this->get_cutoff_interval();

		foreach ( $qb->execute_query() as $row ) {
			$start = new \DateTimeImmutable( $row['created'] );
			$end   = $start->modify( "-$interval days" );
			$key   = $end->format( 'Y-m-d' );
			if ( ! isset( $result[ $key ] ) ) {
				$result[ $key ] = new ProductsDateRangeBag( $start, $end );
			}

			if ( $result[ $key ]->get_start() < $start->format( 'Y-m-d H:i:s' ) ) {
				$result[ $key ] = $result[ $key ]->with_range( $start, $end );
			}

			$result[ $key ]->add_products( $row['product_id'] );
		}

		return $result;
	}

	/**
	 * @param int|int[] $product_id
	 *
	 * @return array{string, string}
	 * @throws \Exception
	 */
	private function get_cutoff_date( $product_id ): array {
		if ( ! empty( $product_id ) ) {
			$qb = $this->create_query_builder();
			$qb->select( 'created' )
				->where(
					'reduced_price = 1',
					'product_id IN (%1$s)',
					'created <= \'%2$s\'',
					'currency = \'%3$s\'',
				)
				->orderBy( 'created', 'DESC' )
				->setMaxResults( 1 )
				->setParameter( '%1$s', \implode( ',', (array) $product_id ) )
				->setParameter( '%2$s', $this->clock->now()->format( 'Y-m-d H:i:s' ) )
				->setParameter( '%3$s', $this->currency_resolver->get_currency() );
			$result = $qb->execute_query();
		}

		if ( isset( $result[0]['created'] ) ) {
			$date = new \DateTimeImmutable( $result[0]['created'] );
		} else {
			$date = $this->clock->now();
		}

		$interval = $this->get_cutoff_interval();

		return [
			$date
				->modify( "-{$interval} days" )
				->format( 'Y-m-d H:i:s' ),
			$date->format( 'Y-m-d H:i:s' ),
		];
	}
}
