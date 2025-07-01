<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Batch;

interface Scheduler {

	/** @param Handler<mixed> $batch */
	public function schedule( Handler $batch ): void;
}
