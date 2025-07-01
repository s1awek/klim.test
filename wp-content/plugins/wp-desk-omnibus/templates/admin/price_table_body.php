<?php
/**
 * @var \WPDesk\Omnibus\Core\HistoricalPrice[] $prices
 * @var \WPDesk\Omnibus\Core\HistoricalPrice[] $the_lowest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
foreach ( $prices as $price ) {
	$is_lowest = array_filter( $the_lowest, [ $price, 'equals' ] );
	?>
	<tr class="<?php echo esc_attr( $is_lowest ? 'is-lowest' : '' ); ?>">
		<td><?php echo wp_kses_post( $price->get_product()->get_formatted_name() ); ?></td>
		<td><?php echo wp_kses_post( wc_price( $price->get_price(), [ 'currency' => $price->get_currency() ] ) ); ?></td>
		<td><?php echo esc_html( $price->get_currency() ); ?></td>
		<td><?php echo esc_html( $price->get_created()->format( 'Y-m-d G:i:s' ) ); ?></td>
		<td><?php echo esc_html( $price->get_changed() ? $price->get_changed()->format( 'Y-m-d G:i:s' ) : '-' ); ?></td>
		<td>
			<?php
			$price->is_reduced_price() ?
			esc_html_e( 'Reduced', 'wpdesk-omnibus' ) :
			esc_html_e( 'Regular', 'wpdesk-omnibus' )
			?>
		</td>
	</tr>
<?php } ?>
