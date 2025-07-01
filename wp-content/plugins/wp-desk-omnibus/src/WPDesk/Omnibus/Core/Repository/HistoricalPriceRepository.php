<?php

declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Repository;

use WPDesk\Omnibus\Core\Database\Query\Expression\CompositeExpression;
use WPDesk\Omnibus\Core\Migrations\Schema;
use WPDesk\Omnibus\Core\HistoricalPrice;

/**
 * Query the lowest price relative from current date.
 */
class HistoricalPriceRepository extends AbstractQuery {

	public function find_one_with_lowest_price( $product_id ): HistoricalPrice {
		if ( empty( $product_id ) ) {
			return $this->hydrator->hydrate( [] );
		}

		[ $bottom_date, $top_date ] = $this->get_cutoff_date();
		$qb                         = $this->create_query_builder();
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
					// Current price.
					CompositeExpression::and(
						'created <= \'%5$s\'',
						CompositeExpression::or(
							'changed IS NULL',
							'changed > \'%6$s\'',
						)
					)
				)
			)
			->orderBy( 'price', 'ASC' )
			->setMaxResults( 1 )
			->setParameter( '%1$s', \implode( ',', (array) $product_id ) )
			->setParameter( '%2$s', $this->currency_resolver->get_currency() )
			->setParameter( '%3$s', $bottom_date )
			->setParameter( '%4$s', $top_date )
			->setParameter( '%5$s', $this->clock->now()->format( 'Y-m-d H:i:s' ) )
			->setParameter( '%6$s', $this->clock->now()->format( 'Y-m-d H:i:s' ) );

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

		[ $bottom_date, $top_date ] = $this->get_cutoff_date();
		$qb                         = $this->create_query_builder( 'p1' );
		$qb->select(
			'MIN(p1.id) AS id',
			'p1.product_id',
			'MIN(p1.created) AS created',
			'p1.price',
			'MIN(p1.reduced_price) as reduced_price',
			'MIN(p1.changed) AS changed'
		)
			->from( Schema::price_logger_table_name(), 'p2' )
			->where(
				'p1.product_id = p2.product_id',
				'p1.product_id IN (%1$s)'
			)
			->andWhere(
				'p1.currency = \'%2$s\'',
				'p2.currency = \'%3$s\''
			)
			->andWhere(
				CompositeExpression::or(
					CompositeExpression::and(
						'p1.changed > \'%4$s\'',
						'p1.changed <= \'%5$s\'',
						'p2.changed > \'%6$s\'',
						'p2.changed <= \'%7$s\'',
					),
					CompositeExpression::and(
						'p1.created <= \'%8$s\'',
						'p2.created <= \'%9$s\'',
						CompositeExpression::or(
							CompositeExpression::and(
								'p1.changed IS NULL',
								'p2.changed IS NULL',
							),
							CompositeExpression::and(
								'p1.changed > \'%10$s\'',
								'p2.changed > \'%11$s\'',
							)
						)
					)
				)
			)
			->groupBy( 'p1.product_id', 'p1.price' )
			->having( 'p1.price = min(p2.price)' )
			->setParameter( '%1$s', \implode( ',', $variable_product ) )
			->setParameter( '%2$s', $this->currency_resolver->get_currency() )
			->setParameter( '%3$s', $this->currency_resolver->get_currency() )
			->setParameter( '%4$s', $bottom_date )
			->setParameter( '%5$s', $top_date )
			->setParameter( '%6$s', $bottom_date )
			->setParameter( '%7$s', $top_date )
			->setParameter( '%8$s', $this->clock->now()->format( 'Y-m-d H:i:s' ) )
			->setParameter( '%9$s', $this->clock->now()->format( 'Y-m-d H:i:s' ) )
			->setParameter( '%10$s', $this->clock->now()->format( 'Y-m-d H:i:s' ) )
			->setParameter( '%11$s', $this->clock->now()->format( 'Y-m-d H:i:s' ) );

		if ( ! $this->settings->get_boolean( 'use_sale_price' ) ) {
			$qb->andWhere( 'p2.reduced_price = 0' );
		}

		$result = [];
		foreach ( $qb->execute_query() as $row ) {
			if ( ! isset( $result[ (int) $row['product_id'] ] ) ) {
				$result[ (int) $row['product_id'] ] = $this->hydrator->hydrate( $row );
			}
		}

		return $this->fill_missing_keys( $result, $variable_product );
	}

	private function get_cutoff_date(): array {
		$interval = $this->get_cutoff_interval();
		$now      = $this->clock->now();

		return [
			$now
				->modify( "-$interval days" )
				->format( 'Y-m-d H:i:s' ),
			$now
				->format( 'Y-m-d H:i:s' ),
		];
	}
}
