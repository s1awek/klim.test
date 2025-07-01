<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Repository;

use OmnibusProVendor\Psr\Clock\ClockInterface;
use WPDesk\Omnibus\Core\HistoricalPrice;

class HistoricalPriceHydrator {

	/** @var ClockInterface */
	private $clock;

	public function __construct( ClockInterface $clock ) {
		$this->clock = $clock;
	}

	/**
	 * @param array{
	 *     id?: numeric,
	 *     product_id?: numeric,
	 *     price?: numeric,
	 *     created?: string,
	 *     reduced_price?: numeric-string,
	 *     changed?: string|null,
	 *     currency?: string,
	 * } $params
	 *
	 * @return HistoricalPrice
	 */
	public function hydrate( array $params ): HistoricalPrice {
		$changed = null;
		if ( isset( $params['changed'] ) && $params['changed'] !== '0000-00-00 00:00:00' ) {
			$changed = new \DateTimeImmutable( $params['changed'] );
		}

		return new HistoricalPrice(
			isset( $params['id'] ) ? (int) $params['id'] : null,
			(int) ( $params['product_id'] ?? 0 ),
			(float) ( $params['price'] ?? 0 ),
			isset( $params['created'] ) ? new \DateTimeImmutable( $params['created'] ) : $this->clock->now(),
			isset( $params['reduced_price'] ) && $params['reduced_price'] !== '0',
			$changed,
			isset( $params['currency'] ) ? $params['currency'] : get_woocommerce_currency()
		);
	}

	/**
	 * @param HistoricalPrice $entity
	 *
	 * @return array{
	 *     product_id: int,
	 *     price: float,
	 *     created: string,
	 *     reduced_price: 0|1,
	 *     currency: string,
	 * }
	 */
	public function dehydrate( HistoricalPrice $entity ): array {
		return [
			'id'            => $entity->get_id(),
			'product_id'    => $entity->get_product_id(),
			'price'         => $entity->get_price(),
			'created'       => $entity->get_created()->format( 'Y-m-d G:i:s' ),
			'reduced_price' => $entity->is_reduced_price() ? 1 : 0,
			'changed'       => $entity->get_changed() ? $entity->get_changed()->format( 'Y-m-d G:i:s' ) : null,
			'currency'      => $entity->get_currency() ?: get_woocommerce_currency(),
		];
	}
}
