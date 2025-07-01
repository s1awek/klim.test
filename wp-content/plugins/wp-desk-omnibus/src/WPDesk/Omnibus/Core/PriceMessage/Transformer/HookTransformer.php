<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Transformer;

use OmnibusProVendor\Psr\Log\LoggerInterface;
use WPDesk\Omnibus\Core\HistoricalPrice;

/**
 * Hooks into WordPress system for external transformations.
 * Additionally wrapped in guard, to make sure no-one breaks it from outside.
 */
final class HookTransformer implements Transformer {

	/** @var LoggerInterface */
	private $logger;

	public function __construct( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	/**
	 * @param HistoricalPrice $entity
	 *
	 * @return HistoricalPrice
	 * @todo type compatibility
	 */
	public function transform( HistoricalPrice $entity ): HistoricalPrice {
		/**
		 * Allow to filter product's price object before display.
		 *
		 * @param HistoricalPrice $price
		 * @param \WC_Product|null $product
		 *
		 * @return HistoricalPrice
		 */
		$new_entity = apply_filters(
			'omnibus/core/current_price_value',
			$entity,
			$entity->get_product()
		);

		if ( $new_entity instanceof HistoricalPrice ) {
			return $new_entity;
		}

		$this->logger->warning(
			sprintf(
				'`$price_value` must be instance of %s. Make sure, your hook filter returns correct value.',
				HistoricalPrice::class
			)
		);
		return $entity;
	}
}
