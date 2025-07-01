<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Migrations;

use OmnibusProVendor\WPDesk\Migrations\AbstractMigration;

class Version120 extends AbstractMigration {

	public function up(): bool {
		$table = Schema::price_logger_table_name();
		$sql   = "ALTER TABLE {$table}
		ADD PRIMARY KEY `product_data` (`product_id`, `created`, `price`),
        DROP INDEX `PRIMARY`;";
		return (bool) $this->wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}
