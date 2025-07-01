<?php
/**
 * @var string $label
 * @var string $group
 * @var array $inputs
 */
?>
<div
	x-data="filterDropdown('<?php echo esc_attr( $group ); ?>')"
	@keydown.escape.prevent.stop="close($refs.container)"
	@focusin.window="! $refs.panel.contains($event.target) && close()"
	x-id="['dropdown-button']"
	x-ref="container"
	class="filter-container"
	>
	<a href="#" @click.prevent="toggle" :aria-controls="$id('dropdown-button')" :aria-expanded="open">
		<?php echo esc_html( $label ); ?>
		<span class="filter-icon dashicons dashicons-filter" aria-hidden="true"></span>
	</a>
	<!-- Panel -->
	<div
		x-ref="panel"
		x-show="open"
		x-transition.origin.top.left
		@click.outside="close($refs.container)"
		:id="$id('dropdown-button')"
		style="display: none;"
		class="filter-dropdown"
		>
		<?php foreach ( $inputs as $input ) { ?>
			<label class="filter-value-label">
				<input type="<?php echo esc_attr( $input['type'] ); ?>" x-model="selectedValues" value="<?php echo esc_attr( $input['value'] ); ?>">
				<?php echo esc_html( wp_strip_all_tags( $input['label'] ) ); ?>
			</label>
		<?php } ?>
		<div class="action-buttons">
			<button class="button" @click.prevent="clearFilters">
				<?php echo esc_html_x( 'Clear', 'Clear filters in table view', 'wpdesk-omnibus' ); ?>
			</button>
			<button class="button button-primary" @click.prevent="applyFilters">
				<?php echo esc_html_x( 'Apply', 'Apply filters in table view', 'wpdesk-omnibus' ); ?>
			</button>
		</div>
	</div>
</div>
