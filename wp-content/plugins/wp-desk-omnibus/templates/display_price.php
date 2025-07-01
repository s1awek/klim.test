<?php
/**
 * @var \WC_Product $product
 * @var \WPDesk\Omnibus\Core\HistoricalPrice $price_value
 * @var string $encoded_variations
 * @var string $message
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<span id='omnibus-price-data'
		data-variations_data='<?php echo esc_attr( $encoded_variations ); ?>'>
<?php echo wp_kses_post( $message ); ?>
</span>
<?php
