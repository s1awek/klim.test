<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Migrations;

use OmnibusProVendor\WPDesk\Migrations\AbstractMigration;

final class Version141 extends AbstractMigration {

	public function up(): bool {
		$has_index = $this->wpdb->query(
			$this->wpdb->prepare(
				<<<'SQL'
				SHOW INDEX FROM %i
				WHERE Key_name = 'covering';
				SQL,
				Schema::price_logger_table_name()
			)
		);

		if ( $has_index ) {
			return true;
		}

		return (bool) $this->wpdb->query(
			$this->wpdb->prepare(
				<<<'SQL'
				ALTER TABLE %i
					ADD INDEX `covering` (`product_id`, `currency`, `price`, `changed`, `created`);
				SQL,
				Schema::price_logger_table_name()
			)
		);
	}
}
