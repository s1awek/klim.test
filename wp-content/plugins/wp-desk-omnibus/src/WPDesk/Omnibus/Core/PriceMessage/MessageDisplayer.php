<?php

declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage;

use OmnibusProVendor\WPDesk\View\Renderer\Renderer;
use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\PriceMessage\Formatter\MessageFormatter;
use WPDesk\Omnibus\Core\PriceMessage\PricePicker\PricePickerFactory;
use WPDesk\Omnibus\Core\PriceMessage\Transformer\Transformer;
use WPDesk\Omnibus\Core\PriceMessage\Visibility\VisibilitySpecification;
use WPDesk\Omnibus\Core\Repository\PriceQuery;
use WPDesk\Omnibus\Core\Settings;

class MessageDisplayer {

	/** @var PriceQuery */
	private $price_query;

	/** @var Renderer */
	private $renderer;

	/** @var Transformer */
	private $transformer;

	/** @var PricePickerFactory */
	private $factory;

	/** @var VisibilitySpecification */
	private $specification;

	/** @var MessageFormatter */
	private $formatter;

	/** @var Settings */
	private $settings;

	public function __construct(
		PricePickerFactory $factory,
		PriceQuery $price_query,
		Renderer $renderer,
		Transformer $transformer,
		VisibilitySpecification $specification,
		MessageFormatter $formatter,
		Settings $settings
	) {
		$this->factory       = $factory;
		$this->price_query   = $price_query;
		$this->renderer      = $renderer;
		$this->transformer   = $transformer;
		$this->specification = $specification;
		$this->formatter     = $formatter;
		$this->settings      = $settings;
	}

	private function get_product_from_globals(): ?\WC_Product {
		global $post;

		if ( ! $post instanceof \WP_Post ) {
			return null;
		}

		$product = wc_get_product( $post->ID );
		if ( ! $product instanceof \WC_Product ) {
			return null;
		}

		return $product;
	}

	/**
	 * @param mixed $product
	 *
	 * @return void
	 */
	public function output( $product = null ): void {
		if ( ! $product instanceof \WC_Product ) {
			$product = $this->get_product_from_globals() ?? new \WC_Product();
		}

		if ( function_exists( 'WCML\functions\getWooCommerceWpml' ) ) {
			$original_id = \WCML\functions\getWooCommerceWpml()->products->get_original_product_id( $product->get_id() );

			if ( $product->get_id() !== $original_id ) {
				$product = wc_get_product( $original_id );
			}
		}

		$price_picker     = $this->factory->with_product( $product );
		$historical_price = $price_picker->get_price( $product );

		if ( ! $this->specification->should_show( $historical_price, $product ) ) {
			return;
		}

		$price_value = $this->transformer->transform( $historical_price );

		$this->renderer->output_render(
			'display_price',
			[
				'message'            => $this->formatter->format_price( $price_value ),
				'encoded_variations' => json_encode( $this->get_variations_data( $product->get_id() ) ),
				'price_value'        => $price_value,
				'product'            => $product,
			]
		);
	}


	/**
	 * @param int $parent_id
	 *
	 * @return array<string|int,VariationValue|false>|false
	 */
	private function get_variations_data( int $parent_id ) {
		$product = wc_get_product( $parent_id );
		if ( ! $product instanceof \WC_Product_Variable ) {
			return false;
		}

		if ( $this->settings->get( 'variant_display_method' ) === 'cumulative' ) {
			return false;
		}

		$variations_data = $this->price_query->find_cheapest_for_variations( $product );

		if ( function_exists( 'WCML\functions\getSitePress' ) ) {
			// When WCML is in question, we have to ensure, correct product ID is used in frontend mapping.
			$sitepress       = \WCML\functions\getSitePress();
			$variations_data = array_reduce(
				$variations_data,
				function ( $a, HistoricalPrice $variation ) use ( $sitepress ) {
					$current_lang_id = apply_filters( 'translate_object_id', $variation->get_product_id(), 'product', true, $sitepress->get_current_language() );

					$a[ $current_lang_id ] = $variation;

					return $a;
				},
				[]
			);
		}

		return array_map(
			function ( HistoricalPrice $variation ) {
				if ( $this->specification->should_show( $variation, $variation->get_product() ) ) {
					return new VariationValue( $this->transformer->transform( $variation ) );
				}

				return false;
			},
			$variations_data
		);
	}
}
