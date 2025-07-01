<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Migrations;

use OmnibusProVendor\WPDesk\Migrations\AbstractMigration;

/**
 * Previously, Omnibus was limited to respecting dates only when they are set, what means we had no
 * viable way to tell if the price was still in use after some period of time, e.g. setting price at
 * the beginning of the year and keeping it to the mid-year promotion would exclude the price from
 * being count as the lowest (event if it might be) because we look 30 days (by default) back from
 * June 1st and no price change was made since May 1st.
 *
 * In some cases we rather want to know how long the price was is use, so we need the changed date.
 *
 * Adding column with invalid default value is only possible because WordPress sets required MySQL
 * modes for the session. Otherwise we should be aware that in some cases MySQL can return error
 * when inserting '0000-00-00 00:00:00' as a value.
 *
 * @see https://github.com/WordPress/wordpress-develop/blob/fdb6e13fedc46fc19852479bad2488c9eab1ed9f/src/wp-includes/class-wpdb.php#L638-L645
 */
final class Version131 extends AbstractMigration {
	use ColumnExistsTrait;

	public function up(): bool {
		$table = Schema::price_logger_table_name();
		return (bool) $this->wpdb->query(
			<<<SQL
			ALTER TABLE $table
				ADD `changed` datetime DEFAULT '0000-00-00 00:00:00',
				ADD INDEX (`changed`);
			SQL
		);
	}

	public function is_needed(): bool {
		return $this->column_exists( 'changed' ) === false;
	}
}
