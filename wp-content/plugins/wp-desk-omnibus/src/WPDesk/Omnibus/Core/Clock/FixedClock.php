<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Clock;

use DateTimeImmutable;
use OmnibusProVendor\Psr\Clock\ClockInterface;

class FixedClock implements ClockInterface {

	/** @var DateTimeImmutable */
	private $date;


	/** @param string|DateTimeImmutable $date */
	public function __construct( $date ) {
		if ( is_string( $date ) ) {
			$date = new DateTimeImmutable( $date );
		}
		$this->date = $date;
	}

	public function now(): DateTimeImmutable {
		return $this->date;
	}
}
