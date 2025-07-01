<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core;

class HistoricalPrice {

	/** @var int|null */
	private $id;

	/** @var int */
	private $product_id;

	private string $currency;

	/** @var float */
	private $price;

	/** @var \DateTimeInterface */
	private $created;

	/** @var \DateTimeInterface|null */
	private $changed;

	/** @var bool */
	private $reduced_price;

	public function __construct(
		?int $id,
		int $product_id,
		float $price,
		\DateTimeInterface $created,
		bool $reduced_price = false,
		?\DateTimeInterface $changed = null,
		string $currency = null
	) {
		$this->id            = $id;
		$this->product_id    = $product_id;
		$this->price         = $price;
		$this->created       = $created;
		$this->reduced_price = $reduced_price;
		$this->changed       = $changed;
		// Currency should always be a string. Deliberately using raw db value as fallback (instead of CurrencyResolver object) because fallback should NEVER be actually required. Not using get_woocommerce_currency as it may be polluted by WPML.
		$this->currency = $currency ?? \get_option( 'woocommerce_currency' );
	}

	public function get_id(): ?int {
		return $this->id;
	}

	public function set_id( int $id ): void {
		$this->id = $id;
	}

	public function get_product(): \WC_Product {
		$product = wc_get_product( $this->product_id );

		if ( $product instanceof \WC_Product ) {
			return $product;
		}

		return new \WC_Product();
	}

	public function is_reduced_price(): bool {
		return $this->reduced_price;
	}

	public function set_reduced_price( bool $reduced_price ): void {
		$this->reduced_price = $reduced_price;
	}

	public function get_product_id(): int {
		return $this->product_id;
	}

	public function set_product_id( int $product_id ): void {
		$this->product_id = $product_id;
	}

	public function get_price(): float {
		return $this->price;
	}

	public function set_price( float $price ): void {
		$this->price = $price;
	}

	public function get_created(): \DateTimeInterface {
		return $this->created;
	}

	public function set_created( \DateTimeInterface $created ): void {
		$this->created = $created;
	}

	public function get_changed(): ?\DateTimeInterface {
		return $this->changed;
	}

	public function set_changed( ?\DateTimeInterface $changed ): void {
		$this->changed = $changed;
	}

	public function get_currency(): string {
		return $this->currency;
	}

	public function set_currency( string $currency ): void {
		$this->currency = $currency;
	}

	public function __set( $name, $value ) {
		if ( ! property_exists( $this, $name ) ) {
			throw new \BadMethodCallException(
				sprintf(
					'Property %s does not exists.',
					$name
				)
			);
		}

		// @phpstan-ignore property.dynamicName
		$this->$name = $value;
		@trigger_error(
			sprintf(
				'Setting property directly in %s is not allowed. Use appropiate set* method instead.',
				self::class
			),
			\E_USER_WARNING
		);
	}

	public function __get( $name ) {
		if ( property_exists( $this, $name ) ) {
			@trigger_error(
				sprintf(
					'Getting property directly in %s is not allowed. Use appropiate get* method instead.',
					self::class
				),
				\E_USER_WARNING
			);
			// @phpstan-ignore property.dynamicName
			return $this->$name;
		}

		throw new \BadMethodCallException(
			sprintf(
				'Property %s is not accessible in context of class %s',
				$name,
				self::class
			)
		);
	}

	/**
	 * We consider price as invalid, if it equals 0 or is missing
	 * product reference. In such case we may want to use current
	 * product price.
	 *
	 * @return bool
	 */
	public function is_valid(): bool {
		return $this->product_id !== 0 && $this->price !== 0.0;
	}

	/**
	 * When comparing two prices we consider those are equal if product reference, price, type are
	 * the same. Validate changed date, but with no regard to timezone, as it may differentiate
	 * between database load and fresh entity (we wouldn't like it, but better safe than sorry).
	 * Creation date value is not important when checking for equality.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function equals( $value ): bool {
		if ( ! is_object( $value ) || ! $value instanceof HistoricalPrice ) {
			return false;
		}

		$i = [
			$this->product_id,
			$this->currency,
			$this->price,
			$this->reduced_price,
			is_null( $this->changed ) ? null : $this->changed->format( 'Y-m-d H:i:s' ),
		] <=> [
			$value->product_id,
			$value->currency,
			$value->price,
			$value->reduced_price,
			is_null( $value->changed ) ? null : $value->changed->format( 'Y-m-d H:i:s' ),
		];

		return $i === 0;
	}
}
