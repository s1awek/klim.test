<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Clock;

use DateTimeImmutable;
use OmnibusProVendor\Psr\Clock\ClockInterface;

class RequestTimeClock implements ClockInterface {

	public function now(): DateTimeImmutable {
		return (
			new DateTimeImmutable( "@{$_SERVER['REQUEST_TIME']}" ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidatedNotSanitized
		)->setTimezone( wp_timezone() );
	}
}
