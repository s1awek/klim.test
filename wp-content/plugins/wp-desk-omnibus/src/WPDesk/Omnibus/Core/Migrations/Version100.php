<?php
declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Migrations;

use OmnibusProVendor\WPDesk\Migrations\AbstractMigration;

final class Version100 extends AbstractMigration {

	public function up(): bool {
		$charset_collate = $this->wpdb->get_charset_collate();
		$table           = Schema::price_logger_table_name();

		$sql = "CREATE TABLE IF NOT EXISTS {$table} (
			product_id bigint unsigned NOT NULL,
			created datetime NOT NULL,
			price decimal(26, 8) unsigned NOT NULL,
			PRIMARY KEY (product_id, created)
		) {$charset_collate};";

		return (bool) $this->wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}
