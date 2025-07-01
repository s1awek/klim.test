<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Cache;

use OmnibusProVendor\Psr\Log\LoggerInterface;
use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Utils\Hookable;

final class InvalidateCache implements Hookable {

	private WpCachePool $cache;

	private LoggerInterface $logger;

	public function __construct( WpCachePool $cache, LoggerInterface $logger ) {
		$this->cache  = $cache;
		$this->logger = $logger;
	}

	public function hooks(): void {
		add_action(
			'woocommerce_settings_save_products',
			[ $this, 'invalidate_multiple' ],
			100
		);

		add_action( 'wpdesk/omnibus/historical_price/saved', [ $this, 'invalidate_single' ] );
	}

	/**
	 * @param HistoricalPrice $entity
	 */
	public function invalidate_single( $entity ): void {
		$product = $entity->get_product();
		$this->cache->delete(
			sprintf(
				'omnibus.query.%s.simple.%s',
				$entity->get_currency(),
				$product->get_id()
			)
		);

		if ( $product instanceof \WC_Product_Variable ) {
			$this->cache->delete(
				sprintf(
					'omnibus.query.%s.variable.%s',
					$entity->get_currency(),
					$product->get_id()
				)
			);

			$ids = implode( ',', array_merge( [ $product->get_id() ], $product->get_children() ) );
			$this->cache->delete(
				sprintf(
					'omnibus.query.%s.simple.%s',
					$entity->get_currency(),
					$ids
				)
			);
		}

		$this->logger->info(
			'Omnibus lowest price cache invalidated for product {id} in currency "{currency}".',
			[
				'id'       => $product->get_id(),
				'currency' => $entity->get_currency(),
			]
		);
	}

	public function invalidate_multiple(): void {
		$pruned = $this->cache->prune();

		if ( $pruned === true ) {
			$this->logger->info( 'Omnibus lowest price completely cache invalidated after settings change.' );
		} else {
			$this->logger->notice( 'Omnibus cache could not be invalidated after settings change.' );
		}
	}
}
