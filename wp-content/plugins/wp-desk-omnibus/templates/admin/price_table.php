<?php
/**
 * @var \WPDesk\Omnibus\Core\HistoricalPrice[] $prices
 * @var string $nonce
 * @var \WC_Product $product
 * @var string[] $currencies
 * @var \WPDesk\Omnibus\Core\HistoricalPrice[] $the_lowest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style>
.filter-container:focus {
	outline: 1px solid transparent;
	box-shadow: 0 0 0 1px #4f94d4, 0 0 2px 1px rgba(79, 148, 212, 0.8);
}

.filter-icon {
	cursor: pointer;
}

.filter-dropdown {
	position: absolute;
	z-index: 1;
	display: flex;
	flex-direction: column;
	gap: 8px;
	background-color: #fff;
	box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
	border: 1px solid #ddd;
	padding: 8px;
	max-height: 150px;
	overflow-y: auto;
}

.filter-dropdown .action-buttons {
	display: flex;
	gap: 8px;
}

.filter-value-label {
	display: block;
}

[x-cloak] {
	display: none !important;
}

.is-lowest {
	font-weight: bold;
}

.omnibus-table-caption {
	caption-side: bottom;
	text-align: left;
}
</style>
<table id="omnibus-price-table" class="widefat fixed striped">
	<caption class="omnibus-table-caption"><?php esc_html_e( "Prices table is limited to 20 entries. The lowest price – according to current plugin's configuration – is emphasized.", 'wpdesk-omnibus' ); ?></caption>
	<thead>
		<tr>
			<th>
				<?php if ( $product->is_type( 'variable' ) ) { ?>
					<?php
					$this->output_render(
						'_filter',
						[
							'group'  => 'product',
							'label'  => esc_html__( 'Product', 'wpdesk-omnibus' ),
							'inputs' =>
								array_map(
									fn ( $p ) => [
										'value' => $p->get_id(),
										'label' => $p->get_formatted_name(),
										'type'  => 'checkbox',
									],
									array_map(
										'wc_get_product',
										$product->get_children()
									),
								),
						]
					);
					?>
				<?php } else { ?>
					<?php esc_html_e( 'Product', 'wpdesk-omnibus' ); ?>
				<?php } ?>
			</th>
			<th data-column="price" class="sortable">
				<?php
				$this->output_render(
					'_sort',
					[
						'group' => 'price',
						'label' => esc_html__( 'Price', 'wpdesk-omnibus' ),
					]
				);
				?>
	</th>
			<th>
				<?php
				$this->output_render(
					'_filter',
					[
						'group'  => 'currency',
						'label'  => esc_html__( 'Currency', 'wpdesk-omnibus' ),
						'inputs' => array_map(
							fn ( $c ) => [
								'value' => $c,
								'label' => $c,
								'type'  => 'checkbox',
							],
							$currencies
						),
					]
				);
				?>
			</th>
			<th data-column="created" class="sortable">
				<?php
				$this->output_render(
					'_sort',
					[
						'label' => esc_html__( 'Valid from', 'wpdesk-omnibus' ),
						'group' => 'created',
					]
				);
				?>
			</th>
			<th data-column="changed" class="sortable">
				<?php
				$this->output_render(
					'_sort',
					[
						'label' => esc_html__( 'Valid to', 'wpdesk-omnibus' ),
						'group' => 'changed',
					]
				);
				?>
			</th>
			<th>
				<?php
				$this->output_render(
					'_filter',
					[
						'group'  => 'reduced_price',
						'label'  => esc_html__( 'Price Type', 'wpdesk-omnibus' ),
						'inputs' => [
							[
								'value' => '0',
								'label' => esc_html__( 'Regular', 'wpdesk-omnibus' ),
								'type'  => 'radio',
							],
							[
								'value' => '1',
								'label' => esc_html__( 'Reduced', 'wpdesk-omnibus' ),
								'type'  => 'radio',
							],
						],
					]
				);
				?>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$this->output_render(
			'price_table_body',
			[
				'prices'     => $prices,
				'the_lowest' => $the_lowest,
			]
		);
		?>
	</tbody>
</table>
<script type="module">
import Alpine from "https://esm.sh/alpinejs@3.13.3";

Alpine.data('filterDropdown', (group) => ({
	open: false,
	selectedValues: [],
	toggle() {
		if (this.open) {
			return this.close()
		}

		this.$refs.panel.focus()

		this.open = true
	},
	close(focusAfter) {
		if (! this.open) return

		this.open = false

		focusAfter && focusAfter.focus();
	},

	clearFilters() {
		this.selectedValues = [];
		this.flush();
	},
	applyFilters() {
		this.flush();
	},
	flush() {
		this.close();
		Alpine.store('table').filterBy(group, this.selectedValues);
	}
}))

Alpine.store('table', {
	filters: {
		product: null,
		priceType: null,
		currency: null
	},
	sort: null,

	filterBy(name, value) {
		this.filters[name] = value
		this.sendAjaxRequest()
	},

	toggleSort(column) {
		this.sort = (this.sort && this.sort.column === column)
			? { ...this.sort, direction: (this.sort.direction === 'asc') ? 'desc' : (this.sort.direction === 'desc') ? null : 'asc' }
			: { column, direction: 'asc' };
		this.updateSortIndicators()
		this.sendAjaxRequest()
	},

	updateSortIndicators() {
		const sortableHeaders = document.querySelectorAll('#omnibus-price-table th.sortable');
		sortableHeaders.forEach(header => {
			const isColumn = header.dataset.column === this.sort?.column;
			header.classList.toggle('sorted', isColumn);
			header.classList.toggle('asc', isColumn && this.sort?.direction === 'asc');
			header.classList.toggle('desc', isColumn && this.sort?.direction === 'desc');
		});
	},

	sendAjaxRequest() {
		const params = new URLSearchParams();

		for (const key in this.filters) {
			if (this.filters[key] === null) {
				continue;
			}
			if (Array.isArray(this.filters[key])) {
				this.filters[key].forEach(value => {
					params.append(`filters[${key}][]`, value);
				});
			} else {
				params.append(`filters[${key}]`, this.filters[key]);
			}
		}

		if (this.sort && this.sort.directrion !== null) {
			params.append(`sort[${this.sort.column}]`, this.sort.direction);
		}

		params.append('_ajax_nonce', '<?php echo esc_attr( $nonce ); ?>');
		params.append('action', 'omnibus_get_price_table');
		params.append('product', '<?php echo esc_attr( $product->get_id() ); ?>');

		fetch(
			`${window.ajaxurl}?${params.toString()}`,
			)
			.then(response => response.json())
			.then(({success, data}) => {
				if (success) {
					document.querySelector('#omnibus-price-table tbody').innerHTML = data;
				}
			})
			.catch(console.error);
	},
})

Alpine.start()
</script>
