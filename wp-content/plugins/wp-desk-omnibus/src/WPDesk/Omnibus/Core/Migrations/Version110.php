<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Migrations;

use OmnibusProVendor\WPDesk\Migrations\AbstractMigration;

final class Version110 extends AbstractMigration {
	use ColumnExistsTrait;

	public function up(): bool {
		$table = Schema::price_logger_table_name();
		$sql   = "ALTER TABLE {$table}
		ADD `reduced_price` tinyint(1) NOT NULL DEFAULT 0
		COMMENT 'Boolean whether price was regular (0) or reduced (1)'";
		return (bool) $this->wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	public function is_needed(): bool {
		return $this->column_exists( 'reduced_price' ) === false;
	}
}
