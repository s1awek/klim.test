<?php
/**
*/
?>
<table class="wc_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Omnibus Price Records">
				<h2><?php esc_html_e( 'Omnibus Price Records', 'wpdesk-omnibus' ); ?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Total records"><?php esc_html_e( 'Total price records', 'wpdesk-omnibus' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( __( 'The total number of historical price records in the database', 'wpdesk-omnibus' ) ); ?></td>
			<td><?php echo esc_html( $diagnostics['total_records'] ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Products with price records"><?php esc_html_e( 'Products with price records', 'wpdesk-omnibus' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( __( 'Number of unique products that have historical price records', 'wpdesk-omnibus' ) ); ?></td>
			<td><?php echo esc_html( $diagnostics['products_with_records'] ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Records without end date"><?php esc_html_e( 'Records without end date', 'wpdesk-omnibus' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( __( 'Number of price records without an end date (changed field)', 'wpdesk-omnibus' ) ); ?></td>
			<td>
			<?php
			if ( $diagnostics['records_without_end_date'] > 0 ) {
				echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html( $diagnostics['records_without_end_date'] ) . '</mark>';
			} else {
				echo '<mark class="yes"><span class="dashicons dashicons-yes"></span> ' . esc_html( $diagnostics['records_without_end_date'] ) . '</mark>';
			}
			?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Products with multiple active prices"><?php esc_html_e( 'Products with multiple active prices', 'wpdesk-omnibus' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( __( 'Number of products that have more than 1 active price record (may indicate saving issues)', 'wpdesk-omnibus' ) ); ?></td>
			<td>
			<?php
			if ( $diagnostics['products_with_multiple_prices'] > 0 ) {
				echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html( $diagnostics['products_with_multiple_prices'] ) . '</mark>';
			} else {
				echo '<mark class="yes"><span class="dashicons dashicons-yes"></span> ' . esc_html( $diagnostics['products_with_multiple_prices'] ) . '</mark>';
			}
			?>
			</td>
		</tr>
	</tbody>
</table>
