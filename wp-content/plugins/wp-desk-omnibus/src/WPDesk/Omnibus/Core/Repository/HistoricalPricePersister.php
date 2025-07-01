<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Repository;

use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Migrations\Schema;

class HistoricalPricePersister {

	/** @var \wpdb */
	protected $wpdb;

	/** @var HistoricalPriceHydrator */
	protected $hydrator;

	public function __construct(
		\wpdb $wpdb,
		HistoricalPriceHydrator $hydrator
	) {
		$this->wpdb     = $wpdb;
		$this->hydrator = $hydrator;
	}

	public function save( HistoricalPrice $entity ): void {
		if ( $entity->get_id() !== null ) {
			$status = $this->update( $entity );
		} elseif ( $this->actually_exists( $entity ) ) {
			// We don't have to update, it's identical.
			throw new PriceNotSaved( 'Price already exists in database.' );
		} else {
			$status = $this->create( $entity );
		}

		if ( $status === false ) {
			throw new PriceNotSaved( $this->wpdb->last_error );
		}
	}

	private function create( HistoricalPrice $entity ) {
		return $this->wpdb->insert(
			Schema::price_logger_table_name(),
			$this->hydrator->dehydrate( $entity )
		);
	}

	private function update( HistoricalPrice $entity ) {
		return $this->wpdb->update(
			Schema::price_logger_table_name(),
			array_diff_key( $this->hydrator->dehydrate( $entity ), [ 'id' => null ] ),
			[ 'id' => $entity->get_id() ]
		);
	}

	public function delete( HistoricalPrice $entity ): void {
		$this->wpdb->delete(
			Schema::price_logger_table_name(),
			[ 'id' => $entity->get_id() ]
		);
	}

	/**
	 * Check for price existence based on unique key constraint. It has to be done manually, as
	 * WordPress is very verbose about database errors and doesn't allow to intercept and handle
	 * such cases.
	 */
	private function actually_exists( HistoricalPrice $entity ): bool {
		[
		'product_id' => $product_id,
		'price' => $price,
		'created' => $created,
		'currency' => $currency,
		] = $this->hydrator->dehydrate( $entity );

		$res = $this->wpdb->query(
			$this->wpdb->prepare(
				<<<SQL
				SELECT 1
				FROM %i
				WHERE product_id = %d
				AND price = %d
				AND created = %s
				AND currency = %s
				SQL,
				Schema::price_logger_table_name(),
				$product_id,
				$price,
				$created,
				$currency
			)
		);

		return $res === 1;
	}
}
