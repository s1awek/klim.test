<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Clock;

use DateTimeImmutable;
use OmnibusProVendor\Psr\Clock\ClockInterface;

class WpClock implements ClockInterface {

	public function now(): DateTimeImmutable {
		return new DateTimeImmutable( 'now', wp_timezone() );
	}
}
