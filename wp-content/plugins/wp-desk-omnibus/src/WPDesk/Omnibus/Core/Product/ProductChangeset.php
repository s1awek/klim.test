<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Product;

class ProductChangeset implements Changeset {

	/** @var array<string, mixed> */
	private array $before;

	/** @var array<string, mixed> */
	private array $after;

	/** @var array<string, mixed> */
	private array $defaults = [
		'regular_price'     => '',
		'sale_price'        => '',
		'date_on_sale_to'   => '',
		'date_on_sale_from' => '',
	];

	/**
	 * Parameters are expected to be raw values from get_post_meta with parameter $single = false,
	 * which returns an array of values for specific meta key.
	 *
	 * @param array<string, array<int|string>> $before
	 * @param array<string, array<int|string>> $after
	 */
	public function __construct( array $before, array $after ) {
		$this->before = $before;
		$this->after  = $after;
	}

	public function changes(): array {
		return array_replace(
			$this->defaults,
			array_map( 'current', $this->after )
		);
	}

	public function original(): array {
		return array_merge(
			$this->defaults,
			array_map( 'current', $this->before ),
		);
	}
}
