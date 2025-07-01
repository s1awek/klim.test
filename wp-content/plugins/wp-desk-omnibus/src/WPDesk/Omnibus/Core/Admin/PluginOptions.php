<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Admin;

use OmnibusProVendor\WPDesk\View\Renderer\Renderer;
use WPDesk\Omnibus\Core\Settings;
use WPDesk\Omnibus\Core\Utils\Hookable;

class PluginOptions implements Hookable {

	/** @var Renderer */
	private $renderer;

	/** @var Settings */
	private $settings;

	public function __construct( Renderer $renderer, Settings $settings ) {
		$this->renderer = $renderer;
		$this->settings = $settings;
	}

	public function hooks(): void {
		if ( ! is_admin() ) {
			return;
		}

		add_filter(
			'woocommerce_products_general_settings',
			[ $this, 'add_fields' ]
		);
		add_action(
			'woocommerce_admin_field_single_select_hook',
			[ $this, 'display_hook_select' ]
		);
	}

	/**
	 * @internal
	 *
	 * @param array<int, array<string, mixed>> $settings
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function add_fields( $settings ) {
		$additional_settings = [
			[
				'type'  => 'title',
				'title' => __( 'General Omnibus settings', 'wpdesk-omnibus' ),
				'id'    => 'wpdesk_omnibus_options',
			],
			[
				'title'             => __( 'Date interval', 'wpdesk-omnibus' ),
				'desc'              => __( 'Decide how many days to look back, when searching for lowest price', 'wpdesk-omnibus' ),
				'id'                => 'omnibus_date_interval',
				'type'              => 'number',
				'default'           => '30',
				'custom_attributes' => [
					'min' => '0',
				],
				'autoload'          => true,
			],
			[
				'title'           => __( 'Method of finding the lowest price', 'wpdesk-omnibus' ),
				'desc'            => __( "Choose how to seek for product's lowest price.", 'wpdesk-omnibus' ),
				'id'              => 'omnibus_date_cutoff_method',
				'default'         => 'today',
				'options'         => [
					'today'     => __( 'Find the lowest price for product since current date', 'wpdesk-omnibus' ),
					'sale_date' => __( "Find the lowest price since product's last promotion date", 'wpdesk-omnibus' ),
				],
				'type'            => 'radio',
				'autoload'        => true,
			],
			[
				'title'           => __( 'Current price is the lowest', 'wpdesk-omnibus' ),
				'desc'            => __( "Choose how to behave, when current product's price is the lowest, e.g. you didn't change price longer than lookup period or you just start to use Omnibus.", 'wpdesk-omnibus' ),
				'id'              => 'omnibus_equal_prices',
				'default'         => 'show',
				'options'         => [
					'show'    => __( 'Display message as usual', 'wpdesk-omnibus' ),
					'no_show' => __( "Don't display any message", 'wpdesk-omnibus' ),
				],
				'type'            => 'radio',
				'autoload'        => true,
			],
			[
				'title'           => __( 'Display the lowest price only for promotional products', 'wpdesk-omnibus' ),
				'desc'            => __( 'When displaying a message about the lowest price for a product, show it only for a product which is currently on promotion.', 'wpdesk-omnibus' ),
				'id'              => 'omnibus_display_only_sale',
				'default'         => 'no',
				'type'            => 'checkbox',
				'autoload'        => true,
			],
			[
				'title'           => __( 'Method of displaying message for product variations', 'wpdesk-omnibus' ),
				'desc'            => __( "Decide whether you want to show a cumulative value for your products' variants or each of them should be treated separately.", 'wpdesk-omnibus' ),
				'id'              => 'omnibus_variant_display_method',
				'default'         => 'split',
				'options'         => [
					'split'      => __( 'Display each product variant separately', 'wpdesk-omnibus' ),
					'cumulative' => __( "Show one, the lowest price from across all product's variants", 'wpdesk-omnibus' ),
				],
				'type'            => 'radio',
				'autoload'        => true,
			],
			[
				'title'           => __( 'Use sale prices', 'wpdesk-omnibus' ),
				'desc'            => __( 'Include sale prices when counting the lowest price.', 'wpdesk-omnibus' ),
				'id'              => 'omnibus_use_sale_price',
				'default'         => 'no',
				'type'            => 'checkbox',
				'autoload'        => true,
			],
			[
				'title'       => __( 'The lowest price message', 'wpdesk-omnibus' ),
				'id'          => 'omnibus_price_message',
				'type'        => 'text',
				'default'     => '',
				'class'       => '',
				'css'         => '',
				'placeholder' => __( 'The lowest price ({date}): {price}', 'wpdesk-omnibus' ),
				'desc'        => sprintf(
					__( 'Available tags:</br><code>{date}</code>: the lowest price date formatted as <em>%s</em></br><code>{price}</code>: the lowest price value</br><code>{days}</code>: the value of date interval option', 'wpdesk-omnibus' ),
					date_i18n( get_option( 'date_format' ), strtotime( '1999-01-01' ) ),
				),
				'desc_tip'    => __( 'Customize the message displaying the lowest price.', 'wpdesk-omnibus' ),
				'autoload'    => true,
			],
			[
				'type' => 'sectionend',
				'id'   => 'wpdesk_omnibus_options',
			],
			[
				'type'  => 'title',
				'title' => __( 'Omnibus message placement settings', 'wpdesk-omnibus' ),
				'desc'  => __( 'Choose, where to place the message. You can pick one of standard WooCommerce hooks or add here your own custom hook which will be used to display message.</br>Use the shortcodes <code>[omnibus_price]</code> and <code>[omnibus_price_message]</code> to display the price thru text editors.</br>If you\'d like to display the price of a variable product by the shortcode add the variant ID to it. Example: <code>[omnibus_price ID="111"]</code>', 'wpdesk-omnibus' ),
				'id'    => 'wpdesk_omnibus_placement_options',
			],
			[
				'title'             => __( 'Display message on product page', 'wpdesk-omnibus' ),
				'id'                => 'omnibus_display_hook',
				'type'              => 'single_select_hook',
				'options'           => $this->get_hook_select_options(),
				'default'           => 'woocommerce_product_meta_start',
				'class'             => 'omnibus-enhanced-select',
				'custom_attributes' => [
					'data-tags' => 'true',
				],
				'autoload'          => true,
			],
			[
				'title'             => __( 'Display message on archive page (shop page, category, tag, attributes)', 'wpdesk-omnibus' ),
				'id'                => 'omnibus_archive_display_hook',
				'type'              => 'single_select_hook',
				'options'           => $this->get_archive_hook_select_options(),
				'class'             => 'omnibus-enhanced-select',
				'custom_attributes' => [
					'data-tags' => 'true',
				],
				'autoload'          => true,
			],
			[
				'title'             => __( 'Display message on cart page', 'wpdesk-omnibus' ),
				'id'                => 'omnibus_cart_display_hook',
				'type'              => 'single_select_hook',
				'options'           => $this->get_cart_hook_select_options(),
				'class'             => 'omnibus-enhanced-select',
				'custom_attributes' => [
					'data-tags' => 'true',
				],
				'autoload'          => true,
			],
			[
				'type' => 'sectionend',
				'id'   => 'wpdesk_omnibus_placement_options',
			],
		];
		array_push( $settings, ...$additional_settings );

		return $settings;
	}

	/**
	 * Copy of WooCommerce select field code available for use with our hooks options.
	 *
	 * @param array $value
	 *
	 * @internal
	 */
	public function display_hook_select( $value ): void {
		// Custom attribute handling.
		$custom_attributes = [];

		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		$option_value      = $value['value'];
		$field_description = \WC_Admin_Settings::get_field_description( $value );
		$tooltip_html      = $field_description['tooltip_html'];

		$this->renderer->output_render(
			'hook_select',
			[
				'value'             => $value,
				'option_value'      => $option_value,
				'custom_attributes' => $custom_attributes,
				'tooltip_html'      => $tooltip_html,
			]
		);
	}

	/** @return array<string, string> */
	public function get_hook_select_options(): array {
		$options = [
			'woocommerce_single_product_summary'        => esc_html__( 'Before short description', 'wpdesk-omnibus' ) . ' (woocommerce_single_product_summary)',
			'woocommerce_before_add_to_cart_form'       => esc_html__( 'After short description', 'wpdesk-omnibus' ) . ' (woocommerce_before_add_to_cart_form)',
			'woocommerce_before_add_to_cart_quantity'   => esc_html__( 'Before add to cart quantity', 'wpdesk-omnibus' ) . ' (woocommerce_before_add_to_cart_quantity)',
			'woocommerce_after_add_to_cart_quantity'    => esc_html__( 'After add to cart quantity', 'wpdesk-omnibus' ) . ' (woocommerce_after_add_to_cart_quantity)',
			'woocommerce_before_add_to_cart_button'     => esc_html__( 'Before add to cart button', 'wpdesk-omnibus' ) . ' (woocommerce_before_add_to_cart_button)',
			'woocommerce_after_add_to_cart_button'      => esc_html__( 'After add to cart button', 'wpdesk-omnibus' ) . ' (woocommerce_after_add_to_cart_button)',
			'woocommerce_product_meta_start'            => esc_html__( "Before product's meta data", 'wpdesk-omnibus' ) . ' (woocommerce_product_meta_start)',
			'woocommerce_product_meta_end'              => esc_html__( "After product's meta data", 'wpdesk-omnibus' ) . ' (woocommerce_product_meta_end)',
			'woocommerce_before_single_product_summary' => esc_html__( 'Before product container', 'wpdesk-omnibus' ) . ' (woocommerce_before_single_product_summary)',
			'woocommerce_after_single_product_summary'  => esc_html__( 'After product container', 'wpdesk-omnibus' ) . ' (woocommerce_after_single_product_summary)',
		];
		if ( $this->is_custom_option_selected_for( 'display_hook', $options ) ) {
			$display_hook             = $this->settings->get( 'display_hook' );
			$options[ $display_hook ] = $display_hook;
		}
		return $options;
	}

	/** @return array<string, string> */
	public function get_archive_hook_select_options(): array {
		$options = [
			''                                        => esc_html__( 'Do not display', 'wpdesk-omnibus' ),
			'woocommerce_before_shop_loop_item'       => esc_html__( 'Before product', 'wpdesk-omnibus' ) . ' (woocommerce_before_shop_loop_item)',
			'woocommerce_before_shop_loop_item_title' => esc_html__( 'Before product title', 'wpdesk-omnibus' ) . ' (woocommerce_before_shop_loop_item_title)',
			'woocommerce_shop_loop_item_title'        => esc_html__( 'Product title', 'wpdesk-omnibus' ) . ' (woocommerce_shop_loop_item_title)',
			'woocommerce_after_shop_loop_item_title'  => esc_html__( 'After product title', 'wpdesk-omnibus' ) . ' (woocommerce_after_shop_loop_item_title)',
			'woocommerce_after_shop_loop_item'        => esc_html__( 'After product', 'wpdesk-omnibus' ) . ' (woocommerce_after_shop_loop_item)',
		];
		if ( $this->is_custom_option_selected_for( 'archive_display_hook', $options ) ) {
			$display_hook             = $this->settings->get( 'archive_display_hook' );
			$options[ $display_hook ] = $display_hook;
		}
		return $options;
	}

	/** @return array<string, string> */
	private function get_cart_hook_select_options(): array {
		$options = [
			''                                 => esc_html__( 'Do not display', 'wpdesk-omnibus' ),
			'woocommerce_after_cart_item_name' => esc_html__( 'After product name', 'wpdesk-omnibus' ) . ' (woocommerce_after_cart_item_name)',
		];
		if ( $this->is_custom_option_selected_for( 'cart_display_hook', $options ) ) {
			$display_hook             = $this->settings->get( 'cart_display_hook' );
			$options[ $display_hook ] = $display_hook;
		}
		return $options;
	}

	/**
	 * @param string $hook
	 * @param array<string, string>  $options
	 *
	 * @return bool
	 */
	private function is_custom_option_selected_for( string $hook, array $options ): bool {
		if ( $this->settings->has( $hook ) && ! array_key_exists( $this->settings->get( $hook ), $options ) ) {
			return true;
		}

		return false;
	}
}
