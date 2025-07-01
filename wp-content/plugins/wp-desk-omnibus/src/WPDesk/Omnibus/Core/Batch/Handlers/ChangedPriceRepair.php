<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Batch\Handlers;

use WPDesk\Omnibus\Core\Batch\Handler;
use WPDesk\Omnibus\Core\Migrations\Schema;

/**
 * @phpstan-type HistoryPriceResult object{id: numeric,product_id: numeric, created: string}
 * @implements Handler<HistoryPriceResult>
 */
class ChangedPriceRepair implements Handler {
	public const IS_REQUESTED = 'omnibus_changed_price_repair_requested';

	/** @var \wpdb */
	private $wpdb;

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	public function get_name(): string {
		return 'changed_price_repair';
	}

	public function process( iterable $batch ): void {
		$table = Schema::price_logger_table_name();
		foreach ( $batch as $result ) {
			$next = $this->wpdb->get_row(
				$this->wpdb->prepare(
					<<<SQL
					SELECT created
					FROM $table
					WHERE
						`product_id` = %d AND
						`created` > %s
					ORDER BY `created` ASC LIMIT 1;
					SQL,
					$result->product_id,
					$result->created
				)
			);

			if ( $next ) {
				$this->wpdb->query(
					$this->wpdb->prepare(
						<<<SQL
						UPDATE $table
						SET `changed` = %s
						WHERE `id` = %d;
						SQL,
						$next->created,
						$result->id
					)
				);
			}
		}

		if ( ! $this->should_enqueue() ) {
			add_option( self::IS_REQUESTED, '0' );
		}
	}

	public function should_enqueue(): bool {
		if ( filter_var( get_option( self::IS_REQUESTED, false ), \FILTER_VALIDATE_BOOLEAN ) === false ) {
			return false;
		}

		$required = iterator_count( $this->chunk( 1 ) ) !== 0;

		if ( ! $required ) {
			update_option( self::IS_REQUESTED, '0' );
		}

		return $required;
	}

	public function chunk( int $size ): iterable {
		$table = Schema::price_logger_table_name();
		yield from $this->wpdb->get_results(
			$this->wpdb->prepare(
				<<<SQL
				SELECT
					l1.id, l1.product_id, l1.created
				FROM $table l1
				JOIN $table l2
					ON l1.product_id = l2.product_id
					AND l1.currency = l2.currency
					AND l1.reduced_price = l2.reduced_price
				WHERE l1.id != l2.id
					AND l1.changed IS NULL
					AND l2.changed IS NULL
				LIMIT %d;
				SQL,
				$size
			)
		);
	}

	public function get_batch_size(): int {
		return 7000;
	}
}
