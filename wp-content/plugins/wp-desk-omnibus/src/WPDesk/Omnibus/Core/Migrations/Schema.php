<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Migrations;

class Schema {

	public static function price_logger_table_name(): string {
		global $wpdb;
		return $wpdb->prefix . 'omnibus_price_logger';
	}
}
