<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Batch;

use WPDesk\Omnibus\Core\Utils\Hookable;

class BatchProcess implements Hookable {

	/** @var HandlersList */
	private $batches;

	/** @var Processor */
	private $processor;

	/** @var Scheduler */
	private $scheduler;

	public function __construct(
		Processor $processor,
		Scheduler $scheduler,
		HandlersList $batches
	) {
		$this->processor = $processor;
		$this->scheduler = $scheduler;
		$this->batches   = $batches;
	}

	public function hooks(): void {
		add_action( 'admin_init', [ $this, 'initialize' ] );
	}

	public function initialize(): void {
		$this->processor->process();

		if ( did_action( 'action_scheduler_init' ) ) {
			$this->schedule();
		} else {
			add_action( 'action_scheduler_init', [ $this, 'schedule' ] );
		}
	}

	/** @internal */
	public function schedule(): void {
		foreach ( $this->batches as $batch ) {
			$this->scheduler->schedule( $batch );
		}
	}
}
