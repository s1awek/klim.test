<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Batch\Handlers;

use WPDesk\Omnibus\Core\Batch\Handler;
use WPDesk\Omnibus\Core\Migrations\Schema;

/**
 * @phpstan-type HistoryPriceResult object{id: numeric,product_id: numeric, created: string, price: numeric, reduced_price: string}
 * @implements Handler<HistoryPriceResult>
 */
class ChangedPriceFiller implements Handler {
	private const MIGRATION_FINISHED = 'omnibus_changed_price_fill';

	/** @var \wpdb */
	private $wpdb;

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	public function get_name(): string {
		return 'changed_price_fill';
	}

	public function should_enqueue(): bool {
		if ( filter_var( get_option( self::MIGRATION_FINISHED, false ), \FILTER_VALIDATE_BOOLEAN ) ) {
			return false;
		}

		$required = iterator_count( $this->chunk( 1 ) ) !== 0;

		if ( ! $required ) {
			add_option( self::MIGRATION_FINISHED, '1' );
		}

		return $required;
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
			} else {
				$this->wpdb->query(
					$this->wpdb->prepare(
						<<<SQL
						UPDATE $table
						SET `changed` = NULL
						WHERE `id` = %d;
						SQL,
						$result->id
					)
				);
			}
		}

		if ( ! $this->should_enqueue() ) {
			add_option( self::MIGRATION_FINISHED, '1' );
		}
	}

	public function chunk( int $size ): iterable {
		$table = Schema::price_logger_table_name();
		yield from $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$table} WHERE `changed` = '0000-00-00 00:00:00' LIMIT %d;",
				$size
			)
		);
	}

	public function get_batch_size(): int {
		return 7000;
	}
}
