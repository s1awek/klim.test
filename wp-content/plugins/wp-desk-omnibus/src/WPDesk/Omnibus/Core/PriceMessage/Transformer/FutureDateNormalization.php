<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Transformer;

use OmnibusProVendor\Psr\Clock\ClockInterface;
use WPDesk\Omnibus\Core\HistoricalPrice;

/**
 * When prices are displayed relative to current date, we may end up with a product which
 * lowest price date is set in future (for sale prices with future schedule). This is correct
 * behavior, but may be confusing to end customer and potentially disclose some information about
 * the duration of the sale. In such cases, we are simply padding the future date to current time.
 */
final class FutureDateNormalization implements Transformer {

	/** @var ClockInterface */
	private $clock;

	public function __construct( ClockInterface $clock ) {
		$this->clock = $clock;
	}

	public function transform( HistoricalPrice $entity ): HistoricalPrice {
		if ( $entity->get_changed() > $this->clock->now() ) {
			$entity->set_changed( $this->clock->now() );
		}
		return $entity;
	}
}
