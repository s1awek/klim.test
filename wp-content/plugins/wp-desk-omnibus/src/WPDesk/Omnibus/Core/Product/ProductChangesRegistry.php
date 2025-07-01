<?php

namespace WPDesk\Omnibus\Core\Product;

use WPDesk\Omnibus\Core\Utils\Hookable;

/**
 * This is a hacky class which is both listener, keeping track of changes made to product during one
 * request and is able to provide the changeset for specific product.
 */
class ProductChangesRegistry implements Hookable {

	/** @var array<int,array<string,string>> */
	private array $before_changes = [];

	/** @var array<int,array<string,string>> */
	private array $after_changes = [];

	public function hooks(): void {
		add_action( 'save_post_product', [ $this, '_before' ], 0 );
		add_action( 'save_post_product_variation', [ $this, '_before' ], 0 );
		// We could hook into save_post with some later priority, but WPML already hooks with
		// \PHP_MAX_INT, absolutely blocking us from attaching anything afterwards. Following WP
		// implementation, we hook into next hook which should be available right after.
		add_action( 'wp_insert_post', [ $this, '_after' ] );
	}

	/** @param int $post_id */
	public function _before( $post_id ): void {
		if ( ! isset( $this->before_changes[ $post_id ] ) ) {
			$this->before_changes[ $post_id ] = get_post_meta( $post_id );
		}
	}

	/** @param int $post_id */
	public function _after( $post_id ): void {
		if ( ! isset( $this->after_changes[ $post_id ] ) ) {
			$this->after_changes[ $post_id ] = get_post_meta( $post_id );
		}
	}

	public function get_changeset( int $id ): ProductChangeset {
		return new ProductChangeset(
			$this->before_changes[ $id ] ?? [],
			$this->after_changes[ $id ] ?? []
		);
	}
}
