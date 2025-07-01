<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage\Visibility;

use WPDesk\Omnibus\Core\HistoricalPrice;

class AndSpecification implements VisibilitySpecification {

	/** @var VisibilitySpecification[] */
	private $specifications;

	public function __construct(
		VisibilitySpecification ...$specifications
	) {
		$this->specifications = $specifications;
	}

	public function should_show( HistoricalPrice $price, \WC_Product $product ): bool {
		return array_reduce(
			$this->specifications,
			static function (
				bool $carry,
				VisibilitySpecification $specification
			) use (
				$price,
				$product
			): bool {
				return $carry && $specification->should_show( $price, $product );
			},
			true
		);
	}
}
