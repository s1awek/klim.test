<?php
/**
 * @var string $label
 * @var string $group
 */
?>
<a x-data href="#" @click.prevent="$store.table.toggleSort('<?php echo esc_attr( $group ); ?>')">
	<span>
		<?php echo esc_html( $label ); ?>
	</span>
	<span class="sorting-indicators" x-cloak>
		<span class="sorting-indicator asc" aria-hidden="true"></span>
		<span class="sorting-indicator desc" aria-hidden="true"></span>
	</span>
</a>
