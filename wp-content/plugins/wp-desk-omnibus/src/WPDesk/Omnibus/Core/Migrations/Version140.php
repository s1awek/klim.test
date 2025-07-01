<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Migrations;

use OmnibusProVendor\WPDesk\Migrations\AbstractMigration;

/**
 * Add `currency` column after `created` column to benefit from sequential order in database index
 * as currency will be used for nearly all queries from now on.
 */
final class Version140 extends AbstractMigration {
	use ColumnExistsTrait;

	public function up(): bool {
		return (bool) $this->wpdb->query(
			$this->wpdb->prepare(
				<<<SQL
				ALTER TABLE %i
					ADD `currency` CHAR(3) NOT NULL DEFAULT %s
					AFTER `created`,
					DROP INDEX `product_data`,
					ADD UNIQUE INDEX `product_data` (`product_id`, `created`, `currency`, `price`);
				SQL,
				Schema::price_logger_table_name(),
				get_option( 'woocommerce_currency' ) // Use raw DB value, as filter may be polluted by other plugins.
			)
		);
	}

	public function is_needed(): bool {
		return $this->column_exists( 'currency' ) === false;
	}
}
