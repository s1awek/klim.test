<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Batch;

interface Processor {

	public function process(): void;
}
