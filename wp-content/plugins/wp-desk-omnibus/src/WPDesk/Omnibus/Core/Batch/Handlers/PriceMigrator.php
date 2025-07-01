<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Batch\Handlers;

use WPDesk\Omnibus\Core\Batch\ResettableHandler;
use WPDesk\Omnibus\Core\Interceptor\InterceptionPersister;
use WPDesk\Omnibus\Core\Migrations\Schema;

/**
 * @implements ResettableHandler<\WC_Product>
 */
class PriceMigrator implements ResettableHandler {
	private const OMNIBUS_PRICES_MIGRATED = 'omnibus_prices_migrated';

	/** @var InterceptionPersister */
	private $persister;

	/** @var \wpdb */
	private $wpdb;

	public function __construct( InterceptionPersister $persister, \wpdb $wpdb ) {
		$this->persister = $persister;
		$this->wpdb      = $wpdb;
	}

	public function get_name(): string {
		return 'price_migrator';
	}

	public function reset(): bool {
		return delete_option( self::OMNIBUS_PRICES_MIGRATED );
	}

	public function process( iterable $batch ): void {
		foreach ( $batch as $product ) {
			$this->persister->intercept_product_prices( $product );
		}

		if ( ! $this->should_enqueue() ) {
			add_option( self::OMNIBUS_PRICES_MIGRATED, '1' );
		}
	}

	public function should_enqueue(): bool {
		if ( filter_var( get_option( self::OMNIBUS_PRICES_MIGRATED, false ), \FILTER_VALIDATE_BOOLEAN ) ) {
			return false;
		}

		$required = iterator_count( $this->lazy_get_products( 1 ) ) !== 0;

		if ( ! $required ) {
			add_option( self::OMNIBUS_PRICES_MIGRATED, '1' );
		}

		return $required;
	}

	/** @return \Generator<\WC_Product> */
	public function chunk( int $size ): iterable {
		foreach ( $this->lazy_get_products( $size ) as $product ) {
			$product = wc_get_product( $product->id );

			if ( $product instanceof \WC_Product_Variable ) {
				continue;
			}

			if ( $product instanceof \WC_Product ) {
				yield $product;
			}
		}
	}

	/** @return \Generator<object{id: string}> */
	private function lazy_get_products( int $size ): iterable {
		$posts_table = $this->wpdb->posts;
		$price_table = Schema::price_logger_table_name();
		yield from $this->wpdb->get_results(
			$this->wpdb->prepare(
				<<<SQL
				SELECT p.id
				FROM $posts_table p
				WHERE
					NOT EXISTS (
						SELECT 1
						FROM $price_table l
						WHERE p.ID = l.product_id
					) AND (
						post_type = 'product' OR
						post_type = 'product_variation'
					)
					AND post_status = 'publish'
				LIMIT %d;
				SQL,
				$size
			)
		);
	}

	public function get_batch_size(): int {
		return 5000;
	}
}
