<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Product;

class WPMLCurrencyAwarePricing implements ProductPricing {

	private ProductPricing $product;

	private string $currency;

	private Changeset $changeset;

	public function __construct( ProductPricing $product, string $currency, Changeset $changeset ) {
		$this->product   = $product;
		$this->currency  = $currency;
		$this->changeset = $changeset;
	}

	public function get_id(): int {
		return $this->product->get_id();
	}

	public function get_regular_price(): string {
		if ( $this->uses_custom_prices() ) {
			return $this->get_meta( '_regular_price' );
		}

		return (string) apply_filters( 'wcml_raw_price_amount', $this->product->get_regular_price(), $this->currency );
	}

	public function get_sale_price(): string {
		if ( $this->uses_custom_prices() ) {
			return $this->get_meta( '_sale_price' );
		}

		return (string) apply_filters( 'wcml_raw_price_amount', $this->product->get_sale_price(), $this->currency );
	}

	public function get_date_on_sale_from(): ?\DateTimeInterface {
		if ( $this->uses_custom_schedule() ) {
			try {
				return new \WC_DateTime( '@' . $this->get_meta( '_sale_price_dates_from' ) );
			} catch ( \Exception $e ) {
				return null;
			}
		}

		return $this->product->get_date_on_sale_from();
	}

	public function get_date_on_sale_to(): ?\DateTimeInterface {
		if ( $this->uses_custom_schedule() ) {
			try {
				return new \WC_DateTime( '@' . $this->get_meta( '_sale_price_dates_to' ) );
			} catch ( \Exception $e ) {
				return null;
			}
		}

		return $this->product->get_date_on_sale_to();
	}

	public function get_currency(): string {
		return $this->currency;
	}

	public function get_changes(): array {
		$changes = $this->changeset->changes();
		if ( ! $this->uses_custom_prices() ) {
			if ( isset( $changes['_regular_price'] ) ) {
				$changes['regular_price'] = (string) apply_filters( 'wcml_raw_price_amount', $changes['_regular_price'], $this->currency );
			}
			if ( isset( $changes['_sale_price'] ) ) {
				$changes['sale_price'] = (string) apply_filters( 'wcml_raw_price_amount', $changes['_sale_price'], $this->currency );
			}
		}

		if ( ! $this->uses_custom_schedule() ) {
			if ( isset( $changes['_sale_price_dates_from'] ) ) {
				$changes['date_on_sale_from'] = is_numeric( $changes['_sale_price_dates_from'] ) ? new \DateTimeImmutable( '@' . $changes['_sale_price_dates_from'] ) : null;
			}
			if ( isset( $changes['_sale_price_dates_to'] ) ) {
				$changes['date_on_sale_to'] = is_numeric( $changes['_sale_price_dates_to'] ) ? new \DateTimeImmutable( '@' . $changes['_sale_price_dates_to'] ) : null;
			}
		}

		return $changes;
	}

	public function get_data(): array {
		$data = $this->changeset->original();
		if ( ! $this->uses_custom_prices() ) {
			if ( isset( $data['_regular_price'] ) ) {
				$data['regular_price'] = (string) apply_filters( 'wcml_raw_price_amount', $data['_regular_price'], $this->currency );
			}
			if ( isset( $data['_sale_price'] ) ) {
				$data['sale_price'] = (string) apply_filters( 'wcml_raw_price_amount', $data['_sale_price'], $this->currency );
			}
		}

		if ( ! $this->uses_custom_schedule() ) {
			if ( isset( $data['_sale_price_dates_from'] ) && is_numeric( $data['_sale_price_dates_from'] ) ) {
				$data['date_on_sale_from'] = new \DateTimeImmutable( '@' . $data['_sale_price_dates_from'] );
			}
			if ( isset( $data['_sale_price_dates_to'] ) && is_numeric( $data['_sale_price_dates_to'] ) ) {
				$data['date_on_sale_to'] = new \DateTimeImmutable( '@' . $data['_sale_price_dates_to'] );
			}
		}

		return $data;
	}

	private function uses_custom_schedule(): bool {
		// Use the same check as in WCML (weak truthy value).
		return (bool) $this->get_meta( '_wcml_schedule' );
	}

	private function uses_custom_prices(): bool {
		return // Use the same check as in WCML (non strict equal).
			get_post_meta( $this->product->get_id(), '_wcml_custom_prices_status', true ) == 1 && // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			is_numeric( $this->get_meta( '_regular_price' ) ) &&
			( (int) $this->get_meta( '_regular_price' ) ) !== 0;
	}

	/** @return string|false */
	private function get_meta( string $key ) {
		return get_post_meta( $this->product->get_id(), $key . '_' . $this->currency, true );
	}
}
