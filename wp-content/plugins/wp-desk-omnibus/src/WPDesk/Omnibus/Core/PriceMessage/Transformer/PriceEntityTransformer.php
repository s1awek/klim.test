<?php

declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Transformer;

use WPDesk\Omnibus\Core\HistoricalPrice;

/**
 * Sort of chain of responsibility for transformators.
 * It's designed to enqueue multiple changes based on price attributes.
 * Better than hook system, which in this case (well-defined transforms)
 * allows to overwrite price before display.
 */
final class PriceEntityTransformer implements Transformer {

	/** @var Transformer[] */
	private $transformators;

	public function __construct( Transformer ...$transformators ) {
		$this->transformators = $transformators;
	}

	public function add_transformator( Transformer $transformator ): void {
		$this->transformators[] = $transformator;
	}

	public function transform( HistoricalPrice $entity ): HistoricalPrice {
		return array_reduce(
			$this->transformators,
			static function ( HistoricalPrice $entity, Transformer $transformator ) {
				return $transformator->transform( $entity );
			},
			$entity
		);
	}
}
