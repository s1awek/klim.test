<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Product;

use WPDesk\Omnibus\Core\Migrations\Schema;
use WPDesk\Omnibus\Core\Utils\Hookable;

class HistoricalPricesCleanup implements Hookable {

	private \wpdb $wpdb;

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	public function hooks(): void {
		add_action( 'after_delete_post', $this, 10, 2 );
	}

	/**
	 * @param int $id
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
	public function __invoke( $id, $post ) {
		if ( $post->post_type === 'product' || $post->post_type === 'product_variation' ) {
			$this->wpdb->query(
				$this->wpdb->prepare(
					'DELETE FROM %i WHERE product_id = %d',
					Schema::price_logger_table_name(),
					$id
				)
			);
		}
	}
}
