<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Admin;

use OmnibusProVendor\WPDesk\View\Renderer\Renderer;
use WPDesk\Omnibus\Core\Migrations\Schema;
use WPDesk\Omnibus\Core\Utils\Hookable;

/**
 * Adds Omnibus status information to the WooCommerce Status page.
 */
class StatusPage implements Hookable {

	private \wpdb $wpdb;

	private Renderer $renderer;

	public function __construct( \wpdb $wpdb, Renderer $renderer ) {
		$this->wpdb     = $wpdb;
		$this->renderer = $renderer;
	}

	public function hooks(): void {
		add_action( 'woocommerce_system_status_report', $this );
	}

	public function __invoke(): void {
		$this->renderer->output_render(
			'status_page',
			[
				'diagnostics' => $this->get_diagnostics_data(),
			]
		);
	}

	/**
	 * Get diagnostic data about price records
	 *
	 * @return array
	 */
	private function get_diagnostics_data(): array {
		$table = Schema::price_logger_table_name();

		[ $total, $distinct_products ] = $this->wpdb->get_row( $this->wpdb->prepare( 'SELECT COUNT(*), COUNT(DISTINCT product_id) FROM %i', $table ), \ARRAY_N );
		$records_without_end_date      = (int) $this->wpdb->get_var( $this->wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE changed IS NULL', $table ) );

		// Products with multiple active prices (more than 1 per currency and type)
		$subquery = $this->wpdb->prepare( 'SELECT product_id, COUNT(*) as price_count FROM %i WHERE changed IS NULL GROUP BY product_id, currency, reduced_price HAVING COUNT(*) > 1', $table );

		// use %1$s to ensure not wrapped in quotes
		$products_with_multiple_prices = (int) $this->wpdb->get_var( $this->wpdb->prepare( 'SELECT COUNT(DISTINCT product_id) FROM (%1$s) as t', $subquery ) );

		return [
			'total_records'                 => $total,
			'products_with_records'         => $distinct_products,
			'records_without_end_date'      => $records_without_end_date,
			'products_with_multiple_prices' => $products_with_multiple_prices,
		];
	}
}
