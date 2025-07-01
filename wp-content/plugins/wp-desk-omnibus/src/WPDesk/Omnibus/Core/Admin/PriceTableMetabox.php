<?php

declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Admin;

use OmnibusProVendor\WPDesk\View\Renderer\Renderer;
use WPDesk\Omnibus\Core\Multicurrency\AvailableCurrencies;
use WPDesk\Omnibus\Core\Repository\PriceQuery;
use WPDesk\Omnibus\Core\Repository\PriceQueryFactory;
use WPDesk\Omnibus\Core\Repository\Repository;
use WPDesk\Omnibus\Core\Utils\Hookable;

class PriceTableMetabox implements Hookable {

	/** @var Renderer */
	private $renderer;

	/** @var Repository */
	private $repository;

	private AvailableCurrencies $currencies;

	private PriceQueryFactory $query_factory;

	public function __construct(
		Repository $repository,
		Renderer $renderer,
		AvailableCurrencies $currencies,
		PriceQueryFactory $query_factory
	) {
		$this->repository    = $repository;
		$this->renderer      = $renderer;
		$this->currencies    = $currencies;
		$this->query_factory = $query_factory;
	}

	public function hooks() {
		if ( ! is_admin() ) {
			return;
		}

		add_action(
			'add_meta_boxes_product',
			function ( $post ) {
				if ( ! $post instanceof \WP_Post ) {
					return;
				}

				$product = wc_get_product( $post->ID );
				if ( $product instanceof \WC_Product_Grouped ) {
					return;
				}

				$this->register_metabox();
			}
		);
	}

	private function register_metabox(): void {
		add_meta_box(
			'omnibus-price-table',
			esc_html__( 'Omnibus Price History', 'wpdesk-omnibus' ),
			function ( $post ) {
				$this->output( $post );
			}
		);
	}

	/**
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
	private function output( $post ): void {
		if ( function_exists( 'WCML\functions\getWooCommerceWpml' ) ) {
			$original_id = \WCML\functions\getWooCommerceWpml()->products->get_original_product_id( $post->ID );

			if ( $post->ID !== $original_id ) {
				$post = get_post( $original_id );
			}
		}

		$products = $this->get_products_ids( $post );

		$the_lowest = [];
		foreach ( $this->currencies->codes() as $currency ) {
			$query = $this->query_factory->with_currency( $currency );
			foreach ( $products as $product ) {
				$the_lowest[] = $query->find_one_with_lowest_price( $product );
			}
		}

		$this->renderer->output_render(
			'price_table',
			[
				'prices'     => $this->repository->find_by_products_id( $products ),
				'nonce'      => wp_create_nonce( 'omnibus_get_price_table_' . $post->ID ),
				'product'    => wc_get_product( $post->ID ),
				'currencies' => iterator_to_array( $this->currencies->codes(), false ),
				'the_lowest' => $the_lowest,
			]
		);
	}

	/**
	 * @param \WP_Post $post
	 *
	 * @return int[]
	 */
	public function get_products_ids( \WP_Post $post ): array {
		$product = wc_get_product( $post->ID );
		if ( ! $product instanceof \WC_Product ) {
			$products = [];
		} elseif ( $product instanceof \WC_Product_Variable ) {
			$products = $product->get_children();
		} else {
			$products = [ $post->ID ];
		}

		return $products;
	}
}
