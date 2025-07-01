<?php
/**
 * @var array<string, mixed> $value
 * @var array|string $option_value
 * @var array<string, string> $custom_attributes
 * @var string $tooltip_html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // phpcs:ignore ?></label>
	</th>
	<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
		<select
			name="<?php echo esc_attr( $value['id'] ); ?><?php echo ( 'multiselect' === $value['type'] ) ? '[]' : ''; ?>"
			id="<?php echo esc_attr( $value['id'] ); ?>"
			style="<?php echo esc_attr( $value['css'] ); ?>"
			class="<?php echo esc_attr( $value['class'] ); ?>"
			<?php echo implode( ' ', $custom_attributes ); // phpcs:ignore ?>
			<?php echo 'multiselect' === $value['type'] ? 'multiple="multiple"' : ''; ?>
			>
			<?php
			foreach ( $value['options'] as $key => $val ) {
				?>
			<option value="<?php echo esc_attr( $key ); ?>"
				<?php

				if ( is_array( $option_value ) ) {
					selected( in_array( (string) $key, $option_value, true ), true );
				} else {
					selected( $option_value, (string) $key );
				}

				?>
				><?php echo esc_html( $val ); ?></option>
				<?php
			}
			?>
		</select>
		<p>
			<?php
			if ( isset( $value['description'] ) ) {
			echo $value['description']; // phpcs:ignore
			}
			?>
		</p>
	</td>
</tr>

