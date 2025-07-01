<?php

declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\PriceMessage;

use WPDesk\Omnibus\Core\HistoricalPrice;

class VariationValue implements \JsonSerializable {

	/** @var HistoricalPrice */
	private $variation_price;

	public function __construct( HistoricalPrice $variation_price ) {
		$this->variation_price = $variation_price;
	}

	/**
	 * @return array{
	 *     price: string,
	 *     date: string
	 * }
	 */
	public function jsonSerialize(): array {
		$created = $this->variation_price->get_created();
		return [
			'price' => wc_price( $this->variation_price->get_price() ),
			'date'  => date_i18n( get_option( 'date_format' ), $created->getTimestamp() + $created->getOffset() ),
		];
	}
}
