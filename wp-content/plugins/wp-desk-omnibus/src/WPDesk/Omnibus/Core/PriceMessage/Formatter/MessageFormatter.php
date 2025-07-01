<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Formatter;

use WPDesk\Omnibus\Core\HistoricalPrice;

/**
 * Class provides an html markup to a Historical Price entity.
 */
interface MessageFormatter {

	/**
	 * @param HistoricalPrice $entity
	 *
	 * @return string
	 */
	public function format_price( HistoricalPrice $entity ): string;
}
