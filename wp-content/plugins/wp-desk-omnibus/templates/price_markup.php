<?php
/**
 * @var string $price
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<span class='js-omnibus-price'>
	<?php echo wp_kses_post( $price ); ?>
</span>
