<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Batch;

/**
 * @template T
 */
interface Handler {

	/**
	 * Friendly name of batch handler, used for serialization and restoring the class.
	 */
	public function get_name(): string;

	/**
	 * Determine if batch processor should actually be enqueued by scheduler.
	 */
	public function should_enqueue(): bool;

	/**
	 * @param iterable<T> $batch
	 */
	public function process( iterable $batch ): void;

	/**
	 * Split collection into a chunk usually of batch size for further processing.
	 *
	 * @return iterable<T>
	 */
	public function chunk( int $size ): iterable;

	/**
	 * Set own default batch size.
	 */
	public function get_batch_size(): int;
}
