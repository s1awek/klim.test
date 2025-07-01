<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Transformer;

use WPDesk\Omnibus\Core\HistoricalPrice;

/**
 * Allows to transform price based on different attributes.
 */
interface Transformer {

	public function transform( HistoricalPrice $entity ): HistoricalPrice;
}
