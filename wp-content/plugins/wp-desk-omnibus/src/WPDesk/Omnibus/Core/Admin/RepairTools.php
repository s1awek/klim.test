<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Admin;

use WPDesk\Omnibus\Core\Batch\Handlers\ChangedPriceRepair;
use WPDesk\Omnibus\Core\Utils\Hookable;

class RepairTools implements Hookable {

	public function hooks(): void {
		add_filter( 'woocommerce_debug_tools', $this, 999 );
	}

	/**
	 * @param array $tools
	 *
	 * @return array
	 */
	public function __invoke( $tools ) {
		$tools['omnibus_repair'] = [
			'name'     => __( 'Historical prices validation', 'wpdesk-omnibus' ),
			'desc'     => __( 'This tool will scan your saved prices and automatically update any records that may need correction.', 'wpdesk-omnibus' ),
			'callback' => [ $this, 'run' ],
			'button'   => __( 'Validate prices', 'wpdesk-omnibus' ),
		];
		return $tools;
	}

	public function run(): string {
		update_option( ChangedPriceRepair::IS_REQUESTED, '1' );
		return __( 'Validating prices in background...', 'wpdesk-omnibus' );
	}
}
