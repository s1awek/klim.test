<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Migrations;

trait ColumnExistsTrait {

	private function column_exists( string $column ): bool {
		$table  = Schema::price_logger_table_name();
		$result = (array) $this->wpdb->get_results( "SHOW COLUMNS FROM {$table} LIKE '{$column}'" );
		return count( $result ) > 0;
	}
}
