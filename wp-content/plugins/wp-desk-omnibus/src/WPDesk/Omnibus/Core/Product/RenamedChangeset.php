<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Product;

/**
 * Rename key names for changeset. This may be required to remain compatible with accepted changeset keys.
 */
class RenamedChangeset implements Changeset {

	private Changeset $changeset;

	/**
	 * @var callable( string ): string
	 */
	private $rename;

	public function __construct( Changeset $changeset, callable $rename ) {
		$this->changeset = $changeset;
		$this->rename    = $rename;
	}

	public function changes(): array {
		return $this->rename_keys( $this->changeset->changes() );
	}

	public function original(): array {
		return $this->rename_keys( $this->changeset->original() );
	}

	private function rename_keys( array $array ): array {
		return array_combine(
			array_map( $this->rename, array_keys( $array ) ),
			array_values( $array )
		);
	}
}
