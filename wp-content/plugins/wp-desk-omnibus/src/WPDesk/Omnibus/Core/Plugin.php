<?php
declare( strict_types=1 );

namespace WPDesk\Omnibus\Core;

use OmnibusProVendor\DI\ContainerBuilder;
use OmnibusProVendor\WPDesk\Migrations\Migrator;
use OmnibusProVendor\WPDesk\Mutex\Mutex;
use OmnibusProVendor\WPDesk\PluginBuilder\Plugin\Activateable;
use OmnibusProVendor\WPDesk\PluginBuilder\Plugin\Deactivateable;
use OmnibusProVendor\WPDesk_Plugin_Info;
use WPDesk\Omnibus\Core\Batch\HandlersList;
use WPDesk\Omnibus\Core\Batch\ResettableHandler;
use WPDesk\Omnibus\Core\Utils\HookRegistrator;
use OmnibusProVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;

class Plugin extends AbstractPlugin implements Activateable, Deactivateable {

	/** @var \OmnibusProVendor\DI\Container */
	private $container;

	public function __construct( $plugin_info ) {
		parent::__construct( $plugin_info );
		$this->plugin_namespace = $this->plugin_info->get_text_domain();
		$this->settings_url     = admin_url( 'admin.php?page=wc-settings&tab=products' );
		$this->docs_url         = 'https://www.wpdesk.net/docs/docs-wp-desk-omnibus/?utm_campaign=omnibus-pro&utm_medium=quick-link&utm_source=user-site';
	}

	public function admin_enqueue_scripts(): void {
		wp_enqueue_script( 'admin', $this->plugin_info->get_plugin_url() . '/assets/js/admin.js', [ 'select2' ], $this->plugin_info->get_version(), true );
	}

	private function build_container(): void {
		$builder = new ContainerBuilder();
		$builder->addDefinitions(
			$this->plugin_info->get_plugin_dir() . '/config/services.inc.php',
			[
				WPDesk_Plugin_Info::class => $this->plugin_info,
			]
		);

		$this->container = $builder->build();

		$this->container->set( \WPDesk_Plugin_Info::class, $this->plugin_info );
	}

	public function init(): void {
		if ( $this->container === null ) {
			$this->build_container();
		}
		$this->container->get( Migrator::class )->migrate();

		parent::init();
	}

	public function hooks(): void {
		parent::hooks();

		( new HookRegistrator( $this->plugin_info, $this->container ) )->boot();
	}

	public function wp_enqueue_scripts(): void {
		parent::wp_enqueue_scripts();

		if ( ! is_product() ) {
			return;
		}
		wp_enqueue_script(
			'omnibus',
			$this->plugin_info->get_plugin_url() . '/assets/index.js',
			[ 'jquery' ],
			$this->plugin_info->get_version(),
			true
		);
	}

	public function activate(): void {
		if ( $this->container === null ) {
			$this->build_container();
		}
		$this->container->get( Migrator::class )->migrate();
	}

	public function deactivate(): void {
		if ( $this->container === null ) {
			$this->build_container();
		}

		$this->container->get( Mutex::class )->releaseLock();

		foreach ( $this->container->get( HandlersList::class ) as $handler ) {
			if ( $handler instanceof ResettableHandler ) {
				$handler->reset();
			}
		}
	}
}
