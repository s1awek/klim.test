<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Utils;

use OmnibusProVendor\Psr\Container\ContainerInterface;

class HookRegistrator {

	private bool $propagation_stopped = false;

	private $plugin_info;

	private ContainerInterface $container;

	public function __construct( $plugin_info, ContainerInterface $container ) {
		$this->plugin_info = $plugin_info;
		$this->container   = $container;
	}

	public function boot(): void {
		foreach ( $this->find_hooks() as $hook ) {
			add_action( $hook, fn() => $this->register( $hook ) );
		}
	}

	/** @return string[] */
	private function find_hooks(): array {
		return [
			'plugins_loaded',
			'wpml_st_loaded',
		];
	}

	private function register( string $hook ): void {
		if ( $this->propagation_stopped ) {
			return;
		}

		$providers = require $this->plugin_info->get_plugin_dir() . "/config/hook_providers/$hook.php";
		foreach ( $providers as $class ) {
			$provider = $this->container->get( $class );
			if ( ! $provider instanceof Hookable ) {
				continue;
			}

			$provider->hooks();
		}
	}
}
