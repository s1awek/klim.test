<?php

declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Admin;

use OmnibusProVendor\WPDesk\View\Renderer\Renderer;
use WPDesk\Omnibus\Core\Multicurrency\AvailableCurrencies;
use WPDesk\Omnibus\Core\Repository\PriceQueryFactory;
use WPDesk\Omnibus\Core\Repository\Repository;
use WPDesk\Omnibus\Core\Utils\Hookable;

class AjaxPriceTable implements Hookable {

	private Renderer $renderer;

	private Repository $repository;

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
		add_action( 'wp_ajax_omnibus_get_price_table', $this );
	}

	public function __invoke(): void {
		if ( ! isset( $_GET['product'] ) ) {
			wp_send_json_error();
		}

		$product_id = absint( wp_unslash( $_GET['product'] ) );
		check_ajax_referer( 'omnibus_get_price_table_' . $product_id );

		if ( current_user_can( 'edit_product', $product_id ) === false ) {
			wp_send_json_error();
		}

		$products = $this->get_products_ids( get_post( $product_id ) );

		$filters = [ 'product_id' => $products ];
		if ( isset( $_GET['filters'] ) && is_array( $_GET['filters'] ) ) {
			$filters = $this->parse_filters_query( $product_id, wp_unslash( $_GET['filters'] ), [ 'product_id' => $products ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized inside parse_filters_query
		}

		$sort = [];
		if ( isset( $_GET['sort'] ) && is_array( $_GET['sort'] ) ) {
			$sort = $this->parse_sort_query( wp_unslash( $_GET['sort'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized inside parse_sort_query
		}

		$the_lowest = [];
		foreach ( $this->currencies->codes() as $currency ) {
			$query = $this->query_factory->with_currency( $currency );
			foreach ( $filters['product_id'] as $product ) {
				$the_lowest[] = $query->find_one_with_lowest_price( $product );
			}
		}

		wp_send_json_success(
			$this->renderer->render(
				'price_table_body',
				[
					'prices'     => $this->repository->find_by(
						$filters,
						$sort
					),
					'the_lowest' => $the_lowest,
				]
			)
		);
	}

	/**
	 * @param \WP_Post $post
	 *
	 * @return int[]
	 */
	private function get_products_ids( \WP_Post $post ): array {
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

	private function parse_filters_query( int $product_id, array $filters_query, array $defaults ): array {
		$result = $defaults;

		if ( isset( $filters_query['currency'] ) ) {
			$available_currencies = iterator_to_array( $this->currencies->codes(), false );
			$wanted_currencies    = array_filter(
				array_map(
					'sanitize_text_field',
					wp_unslash( $filters_query['currency'] )
				),
				fn ( $c ) => in_array( $c, $available_currencies, true )
			);
			$result['currency']   = $wanted_currencies;
		}

		if ( isset( $filters_query['reduced_price'] ) ) {
			$result['reduced_price'] = absint( wp_unslash( $filters_query['reduced_price'] ) );
		}

		if ( isset( $filters_query['product'] ) && is_array( $filters_query['product'] ) ) {
			$product = wc_get_product( $product_id );
			if ( $product instanceof \WC_Product ) {
				$products_filter      = array_filter(
					array_map( 'absint', wp_unslash( $filters_query['product'] ) ),
					fn ( $p ) => in_array( $p, $product->get_children(), true )
				);
				$result['product_id'] = $products_filter;
			}
		}

		return $result;
	}

	private function parse_sort_query( array $sort_query ): array {
		[ $sort_column ] = array_keys( wp_unslash( $sort_query ) );
		[ $sort_order ]  = array_values( wp_unslash( $sort_query ) );

		if ( in_array( $sort_order, [ 'asc', 'desc' ], true ) ) {
			return [
				sanitize_key( $sort_column ) => strtoupper( $sort_order ) === 'ASC' ? 'ASC' : 'DESC',
			];
		}

		return [];
	}
}
