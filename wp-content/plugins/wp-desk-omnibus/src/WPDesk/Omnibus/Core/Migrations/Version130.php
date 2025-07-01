<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Migrations;

use OmnibusProVendor\WPDesk\Migrations\AbstractMigration;

/**
 * Saved prices were treated nearly as value objects because only read-write operations were
 * required, without any updates. This changed with the introduction of intercepting date of price
 * change, thus we need a viable way to discriminate prices and easily update them.
 */
final class Version130 extends AbstractMigration {

	use ColumnExistsTrait;

	public function up(): bool {
		$table = Schema::price_logger_table_name();
		return (bool) $this->wpdb->query(
			<<<SQL
			ALTER TABLE $table
				ADD `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT FIRST,
				DROP INDEX `PRIMARY`,
				ADD PRIMARY KEY (`id`),
				ADD UNIQUE INDEX `product_data` (`product_id`, `created`, `price`);
			SQL
		);
	}

	public function is_needed(): bool {
		return $this->column_exists( 'id' ) === false;
	}
}
