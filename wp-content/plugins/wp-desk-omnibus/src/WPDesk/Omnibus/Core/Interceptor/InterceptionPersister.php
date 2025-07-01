<?php

declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Interceptor;

use OmnibusProVendor\Psr\Log\LoggerInterface;
use WPDesk\Omnibus\Core\HistoricalPrice;
use WPDesk\Omnibus\Core\Multicurrency\AvailableCurrencies;
use WPDesk\Omnibus\Core\Multicurrency\RawDefaultCurrencies;
use WPDesk\Omnibus\Core\Repository\Repository;
use WPDesk\Omnibus\Core\Repository\PriceNotSaved;
use WPDesk\Omnibus\Core\Repository\HistoricalPricePersister;

/**
 * It's worth noting that interception persister in current form operates correctly in synchronous work, exercising multiple database calls, especially when updating entities.
 *
 * Firstly, inner interception persister usually calls database to fetch possible price entity. Then
 * such entity is persisted to database and the next interceptor will fetch a fresh instance (for
 * the same or different entity). This process would benefit from some simple unit of work, ensuring
 * one database save per entity, but at the current form it would introduce more burden to handle
 * potential issues.
 */
class InterceptionPersister {

	/** @var Repository */
	private $repository;

	/** @var Interception\PriceInterception[] */
	private $interceptors;

	/** @var HistoricalPricePersister */
	private $persister;

	/** @var LoggerInterface */
	private $logger;

	private AvailableCurrencies $currencies;

	/** @param Interception\PriceInterception[] $interceptors */
	public function __construct(
		$interceptors,
		Repository $repository,
		HistoricalPricePersister $persister,
		LoggerInterface $logger,
		AvailableCurrencies $currencies = null
	) {
		$this->interceptors = $interceptors;
		$this->repository   = $repository;
		$this->persister    = $persister;
		$this->logger       = $logger;
		$this->currencies   = $currencies ?? new RawDefaultCurrencies();
	}

	/**
	 * @param \WC_Product $product
	 *
	 * @return void
	 */
	public function intercept_product_prices( $product ): void {
		if ( function_exists( 'WCML\functions\getWooCommerceWpml' ) ) {
			$original_id = \WCML\functions\getWooCommerceWpml()->products->get_original_product_id( $product->get_id() );

			if ( $product->get_id() !== $original_id ) {
				return;
			}
		}

		if ( ! $this->should_intercept( $product ) ) {
			return;
		}

		if ( $product instanceof \WC_Product_Variable ) {
			/** @var \WC_Product_Variation $item */
			foreach ( $product->get_available_variations( 'objects' ) as $item ) {
				$this->intercept_product_prices( $item );
			}

			return;
		}

		foreach ( $this->currencies as $currency_code => $resolver ) {
			$product_pricing = $resolver( $product );
			$this->logger->debug(
				'Handling interception for product {id} in currency "{currency}"',
				[
					'id'                   => $product_pricing->get_id(),
					'currency'             => $currency_code,
				]
			);

			foreach ( $this->interceptors as $interceptor ) {
				$entity = $interceptor->intercept( $product_pricing );
				if ( $entity === null ) {
					continue;
				}

				if ( ! $this->needs_saving( $entity ) ) {
					$this->logger->info( 'Skipping {id}', [ 'id' => $entity->get_product_id() ] );
					continue;
				}

				$this->logger->info( 'Saving price value for product {id}', [ 'id' => $entity->get_product_id() ] );
				try {
					$this->persister->save( $entity );
					do_action( 'wpdesk/omnibus/historical_price/saved', $entity );
					$this->logger->info( 'Successfully saved price value for product {id}', [ 'id' => $entity->get_product_id() ] );
				} catch ( PriceNotSaved $e ) {
					$this->logger->error(
						'Could not save price value for product {id}. Reason: {message}',
						[
							'id'      => $entity->get_product_id(),
							'message' => $e->getMessage(),
						]
					);
				}
			}

			$this->logger->debug(
				'Finished interception for product {id} in currency "{currency}"',
				[
					'id'       => $product_pricing->get_id(),
					'currency' => $currency_code,
				]
			);
		}
	}

	/**
	 * Always require save if we are handling price from database. Check for similarity only for
	 * fresh instances.
	 */
	private function needs_saving( HistoricalPrice $price ): bool {
		if ( $price->get_id() !== null ) {
			return true;
		}

		$old_price = $this->repository->find_last_similar( $price );
		if ( $price->equals( $old_price ) ) {
			return false;
		}

		return true;
	}

	/**
	 * For any product variation, we check if parent is publicly
	 * available. Parent's status has higher priority.
	 *
	 * @param \WC_Product $product
	 *
	 * @return bool
	 */
	private function should_intercept( \WC_Product $product ): bool {
		if ( $product instanceof \WC_Product_Variation ) {
			$parent = wc_get_product( $product->get_parent_id() );
			if ( ! $parent instanceof \WC_Product ) {
				return false;
			}

			$product = $parent;
		}

		return $product->get_status() === 'publish';
	}
}
