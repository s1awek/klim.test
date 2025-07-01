<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Product;

/**
 * When possible, cast changeset values to \DateTimeInterface objects.
 */
class NormalizedChangeset implements Changeset {

	private Changeset $changeset;

	public function __construct( Changeset $changeset ) {
		$this->changeset = $changeset;
	}

	public function changes(): array {
		return $this->cast_values( $this->changeset->changes() );
	}

	public function original(): array {
		return $this->cast_values( $this->changeset->original() );
	}

	private function cast_values( array $array ): array {
		return array_combine(
			array_keys( $array ),
			array_map(
				function ( $k, $v ) {
					if ( str_contains( $k, 'date_on_sale' ) && is_numeric( $v ) ) {
						return new \DateTimeImmutable( '@' . $v );
					}

					return $v;
				},
				array_keys( $array ),
				array_values( $array )
			),
		);
	}
}
