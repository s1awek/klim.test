<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Batch;

/**
 * @implements \IteratorAggregate<string, Handler<mixed>>
 */
class HandlersList implements \IteratorAggregate {

	/** @var Handler<mixed>[] */
	private $handlers;

	/** @param Handler<mixed>[] $handlers */
	public function __construct( array $handlers ) {
		foreach ( $handlers as $handler ) {
			$this->add( $handler );
		}
	}

	/**
	 * @param Handler<mixed> $handler
	 */
	public function add( Handler $handler ): void {
		$this->handlers[ $handler->get_name() ] = $handler;
	}

	/** @return \ArrayIterator<string, Handler<mixed>> */
	public function getIterator(): \ArrayIterator {
		return new \ArrayIterator( $this->handlers );
	}

	/** @return Handler<mixed>|null */
	public function get( string $name ): ?Handler {
		return $this->handlers[ $name ] ?? null;
	}
}
