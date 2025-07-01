<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Batch;

/**
 * Reset job's state, allowing to re-enqueue.
 *
 * @template T
 * @extends Handler<T>
 */
interface ResettableHandler extends Handler {

	public function reset(): bool;
}
