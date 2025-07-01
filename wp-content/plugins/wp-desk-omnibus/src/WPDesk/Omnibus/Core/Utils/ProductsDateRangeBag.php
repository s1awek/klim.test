<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core\Utils;

class ProductsDateRangeBag {

	/** @var \DateTimeInterface */
	private $range_start;

	/** @var \DateTimeInterface */
	private $range_end;

	/** @var numeric[] */
	private $products = [];

	/**
	 * @param \DateTimeInterface $range_start
	 * @param \DateTimeInterface $range_end
	 */
	public function __construct(
		\DateTimeInterface $range_start,
		\DateTimeInterface $range_end
	) {
		$this->range_start = $range_start;
		$this->range_end   = $range_end;
	}

	public function with_range( \DateTimeInterface $range_start, \DateTimeInterface $range_end ): self {
		$self              = clone $this;
		$self->range_start = $range_start;
		$self->range_end   = $range_end;

		return $self;
	}

	/** @param numeric ...$product_id */
	public function add_products( ...$product_id ): void {
		array_push( $this->products, ...\array_values( $product_id ) );
	}

	public function get_start(): string {
		return $this->range_start->format( 'Y-m-d H:i:s' );
	}

	public function get_end(): string {
		return $this->range_end->format( 'Y-m-d H:i:s' );
	}

	/** @return numeric[] */
	public function get_products(): array {
		return $this->products;
	}
}
