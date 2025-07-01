<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Batch;

use OmnibusProVendor\WPDesk\Mutex\Mutex;

class ActionSchedulerBatchProcessor implements Scheduler, Processor {
	private const QUEUE_HOOK = 'wpdesk/omnibus/current_prices_migration';

	/** @var \WC_Queue_Interface */
	private $queue;

	/** @var HandlersList */
	private $batches;

	/** @var Mutex */
	private $lock;

	public function __construct(
		\WC_Queue_Interface $queue,
		HandlersList $batches,
		Mutex $lock
	) {
		$this->queue   = $queue;
		$this->batches = $batches;
		$this->lock    = $lock;
	}

	/** @param Handler<mixed> $batch */
	public function schedule( Handler $batch ): void {
		if ( ! $batch->should_enqueue() ) {
			return;
		}

		if ( ! $this->lock->acquireLock() ) {
			return;
		}

		$action = $this->queue->add(
			self::QUEUE_HOOK,
			[ 'name' => $batch->get_name() ],
			'omnibus-migrations'
		);
	}

	public function process(): void {
		add_action( self::QUEUE_HOOK, [ $this, 'do_process' ] );
	}

	/** @internal */
	public function do_process( string $batch ): void {
		$this->lock->releaseLock();
		$handler = $this->batches->get( $batch );
		if ( ! $handler instanceof Handler ) {
			return;
		}
		$handler->process( $handler->chunk( $handler->get_batch_size() ) );
	}
}
