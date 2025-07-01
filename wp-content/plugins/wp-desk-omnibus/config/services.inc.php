<?php

use OmnibusProVendor\DI\Container;
use OmnibusProVendor\Psr\Clock\ClockInterface;
use OmnibusProVendor\Psr\Log\LoggerInterface;
use OmnibusProVendor\WPDesk\Logger\Settings as LoggerSettings;
use OmnibusProVendor\WPDesk\Logger\SimpleLoggerFactory;
use OmnibusProVendor\WPDesk\Migrations\Migrator;
use OmnibusProVendor\WPDesk\Migrations\WpdbMigrator;
use OmnibusProVendor\WPDesk\Mutex\Mutex;
use OmnibusProVendor\WPDesk\Mutex\WordpressPostMutex;
use OmnibusProVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use OmnibusProVendor\WPDesk\View\Resolver\ChainResolver;
use OmnibusProVendor\WPDesk\View\Resolver\DirResolver;
use OmnibusProVendor\WPDesk\View\Resolver\WPThemeResolver;
use OmnibusProVendor\WPDesk_Plugin_Info;
use WPDesk\Omnibus\Core\Admin\AjaxPriceTable;
use WPDesk\Omnibus\Core\Admin\PluginOptions;
use WPDesk\Omnibus\Core\Admin\PriceTableMetabox;
use WPDesk\Omnibus\Core\Admin\StatusPage;
use WPDesk\Omnibus\Core\Batch;
use WPDesk\Omnibus\Core\Cache\WpCachePool;
use WPDesk\Omnibus\Core\Clock\RequestTimeClock;
use WPDesk\Omnibus\Core\Interceptor;
use WPDesk\Omnibus\Core\Interceptor\InterceptionPersister;
use WPDesk\Omnibus\Core\Multicurrency\ChainCurrencies;
use WPDesk\Omnibus\Core\Multicurrency\Client\CurrencyResolver;
use WPDesk\Omnibus\Core\Multicurrency\Client\CurrencyResolverFactory;
use WPDesk\Omnibus\Core\Multicurrency\Client\RawDefaultCurrencyResolver;
use WPDesk\Omnibus\Core\Multicurrency\RawDefaultCurrencies;
use WPDesk\Omnibus\Core\Multicurrency\WPMLCurrencies;
use WPDesk\Omnibus\Core\PriceMessage\Formatter\RawPriceFormatter;
use WPDesk\Omnibus\Core\PriceMessage\Formatter\RichMessageFormatter;
use WPDesk\Omnibus\Core\PriceMessage\MessageDisplayer;
use WPDesk\Omnibus\Core\PriceMessage\Shortcode\ShortcodePrice;
use WPDesk\Omnibus\Core\PriceMessage\Shortcode\ShortcodePriceMessage;
use WPDesk\Omnibus\Core\PriceMessage\Transformer;
use WPDesk\Omnibus\Core\PriceMessage\Visibility;
use WPDesk\Omnibus\Core\Repository\CachedPriceQuery;
use WPDesk\Omnibus\Core\Repository\PriceQuery;
use WPDesk\Omnibus\Core\Repository\PriceQueryFactory;
use WPDesk\Omnibus\Core\Repository\PriceRepository;
use WPDesk\Omnibus\Core\Repository\Repository;
use WPDesk\Omnibus\Core\SettingsBag;
use WPDesk\Omnibus\Core\Utils\ExternalPlugin;
use WPDesk\Omnibus\Core\Utils\NumberFormatter;
use function OmnibusProVendor\DI\autowire;
use function OmnibusProVendor\DI\create;
use function OmnibusProVendor\DI\get;

return [
	'plugin.wcml'                                         => create( ExternalPlugin::class )->constructor( 'woocommerce-multilingual/wpml-woocommerce.php' ),
	'interception.create'                                 => autowire( InterceptionPersister::class )->constructorParameter(
		'interceptors',
		[
			autowire( Interceptor\Interception\RegularPriceInterceptor::class ),
			autowire( Interceptor\Interception\SalePriceInterceptor::class ),
		]
	),
	'interception.update'                                 => autowire( InterceptionPersister::class )->constructorParameter(
		'interceptors',
		[
			autowire( Interceptor\Interception\ChangedRegularPriceInterception::class ),
			autowire( Interceptor\Interception\ChangedSalePriceInterception::class ),
			autowire( Interceptor\Interception\ChangedSaleExpirationDateInterception::class ),
			autowire( Interceptor\Interception\ChangedSaleStartDateInterception::class ),
		]
	),

	Interceptor\CreateAfterProductSave::class             => autowire()->constructorParameter( 'persister', get( 'interception.create' ) ),
	Interceptor\UpdateBeforeProductSave::class            => autowire()->constructorParameter( 'persister', get( 'interception.update' ) ),
	Interceptor\WPMLInterceptor::class                    => autowire()->constructorParameter(
		'persister',
		autowire( InterceptionPersister::class )->constructorParameter(
			'interceptors',
			[
				autowire( Interceptor\Interception\ChangedRegularPriceInterception::class ),
				autowire( Interceptor\Interception\ChangedSalePriceInterception::class ),
				autowire( Interceptor\Interception\ChangedSaleExpirationDateInterception::class ),
				autowire( Interceptor\Interception\ChangedSaleStartDateInterception::class ),
				autowire( Interceptor\Interception\RegularPriceInterceptor::class ),
				autowire( Interceptor\Interception\SalePriceInterceptor::class ),
			]
		)->constructorParameter(
			'currencies',
			get( WPMLCurrencies::class )
		)
	),

	'renderer.front'                                      => static function ( WPDesk_Plugin_Info $p ) {
		return new SimplePhpRenderer(
			new ChainResolver(
				new WPThemeResolver( 'wpdesk-omnibus' ),
				new DirResolver( $p->get_plugin_dir() . '/templates/' )
			)
		);
	},
	'renderer.admin'                                      => static function ( WPDesk_Plugin_Info $p ) {
		return new SimplePhpRenderer(
			new DirResolver( $p->get_plugin_dir() . '/templates/admin/' )
		);
	},

	LoggerInterface::class                                => static function () {
		return ( new SimpleLoggerFactory( 'wpdesk-omnibus' ) )->getLogger();
	},

	\wpdb::class                                          => static function () {
		global $wpdb;
		return $wpdb;
	},

	Visibility\VisibilitySpecification::class             => get( Visibility\AndSpecification::class ),

	Visibility\AndSpecification::class                    => static function ( Container $c ) {
		return new Visibility\AndSpecification(
			$c->get( Visibility\GroupedProductSpecification::class ),
			$c->get( Visibility\OnSaleVisibilitySpecification::class ),
			$c->get( Visibility\VariableNotModifiedSpecification::class ),
			$c->get( Visibility\NotModifiedVisibilitySpecification::class ),
			$c->get( Visibility\ArchiveVisibilitySpecification::class )
		);
	},

	Repository::class                                     => autowire( PriceRepository::class ),

	PriceQuery::class                                     => static function ( PriceQueryFactory $f, CurrencyResolver $c ) {
		return new CachedPriceQuery( $f->get_price_query(), new WpCachePool(), $c );
	},

	'WPDesk\Omnibus\Core\PriceMessage\Formatter\*Formatter' => autowire()->constructorParameter( 'renderer', get( 'renderer.front' ) ),

	'displayer.rich'                                      => autowire( MessageDisplayer::class )
		->constructorParameter( 'formatter', get( RichMessageFormatter::class ) )
		->constructorParameter( 'renderer', get( 'renderer.front' ) ),

	'displayer.raw'                                       => autowire( MessageDisplayer::class )
		->constructorParameter( 'formatter', get( RawPriceFormatter::class ) )
		->constructorParameter( 'renderer', get( 'renderer.front' ) ),

	'WPDesk\Omnibus\Core\PriceMessage\Frontend\*Display'  => autowire()
		->constructorParameter( 'displayer', get( 'displayer.rich' ) ),

	ShortcodePriceMessage::class                          => autowire()->constructor( get( 'displayer.rich' ) ),
	ShortcodePrice::class                                 => autowire()->constructor( get( 'displayer.raw' ) ),

	Transformer\Transformer::class                        => get( Transformer\PriceEntityTransformer::class ),

	Transformer\PriceEntityTransformer::class             => static function ( Container $c ) {
		return new Transformer\PriceEntityTransformer(
			$c->get( Transformer\InvalidPriceTransformer::class ),
			$c->get( Transformer\VariableInvalidPriceTransformer::class ),
			$c->get( Transformer\FutureDateNormalization::class ),
			$c->get( Transformer\TaxTransformer::class ),
			$c->get( Transformer\HookTransformer::class )
		);
	},

	\WPDesk\Omnibus\Core\Settings::class                  => autowire( SettingsBag::class ),

	PriceTableMetabox::class                              => autowire()->constructorParameter( 'renderer', get( 'renderer.admin' ) )->constructorParameter( 'currencies', get( ChainCurrencies::class ) ),
	AjaxPriceTable::class                                 => autowire()->constructorParameter( 'renderer', get( 'renderer.admin' ) )->constructorParameter( 'currencies', get( ChainCurrencies::class ) ),
	PluginOptions::class                                  => autowire()->constructorParameter( 'renderer', get( 'renderer.admin' ) ),
	StatusPage::class                                     => autowire()->constructorParameter( 'renderer', get( 'renderer.admin' ) ),

	Batch\Processor::class                                => get( Batch\ActionSchedulerBatchProcessor::class ),
	Batch\Scheduler::class                                => get( Batch\ActionSchedulerBatchProcessor::class ),

	Batch\ActionSchedulerBatchProcessor::class            => autowire(),

	// We want to release the lock only after action scheduler executed the action, which
	// depends on server settings and can be triggered relatively late, thus timeout is
	// generous.
	// Additionally, we use wp_postmeta mutex (even though schedule is not in postmeta)
	// because currently its the only implementation which allows us to acquire the lock
	// between sessions.
	Mutex::class                                          => static fn () => new WordpressPostMutex( 1, '_omnibus_batch', \DAY_IN_SECONDS ),

	\WC_Queue_Interface::class                            => static function () {
		return \WC_Queue::instance();
	},

	Batch\HandlersList::class                             => create()->constructor(
		[
			get( Batch\Handlers\PriceMigrator::class ),
			get( Batch\Handlers\ChangedPriceFiller::class ),
			get( Batch\Handlers\ChangedPriceRepair::class ),
			get( Batch\Handlers\WPMLMissingCurrencyFill::class ),
			get( Batch\Handlers\WPMLAutomaticExchange::class ),
		]
	),

	Batch\Handlers\PriceMigrator::class                   => autowire()
		->constructorParameter(
			'persister',
			autowire( InterceptionPersister::class )
				->constructorParameter(
					'interceptors',
					[
						autowire( Interceptor\Interception\RegularPriceInterceptor::class ),
						autowire( Interceptor\Interception\SalePriceInterceptor::class ),
					]
				)
				->constructorParameter( 'currencies', get( ChainCurrencies::class ) )
		),

	Batch\Handlers\WPMLMissingCurrencyFill::class         => autowire()
		->constructorParameter(
			'persister',
			autowire( InterceptionPersister::class )
				->constructorParameter(
					'interceptors',
					[
						autowire( Interceptor\Interception\RegularPriceInterceptor::class ),
						autowire( Interceptor\Interception\SalePriceInterceptor::class ),
					]
				)
				->constructorParameter(
					'currencies',
					get( WPMLCurrencies::class )
				)
		)
		->constructorParameter( 'wcml', get( 'plugin.wcml' ) )
		->constructorParameter( 'currencies', get( WPMLCurrencies::class ) ),
	Batch\Handlers\WPMLAutomaticExchange::class           => autowire()
		->constructorParameter( 'wcml', get( 'plugin.wcml' ) )
		->constructorParameter( 'currencies', get( WPMLCurrencies::class ) ),

	\NumberFormatter::class                               => create( NumberFormatter::class ),

	Migrator::class                                       => static function ( WPDesk_Plugin_Info $p ) {
		return WpdbMigrator::from_directories(
			[ $p->get_plugin_dir() . '/src/WPDesk/Omnibus/Core/Migrations/' ],
			'wpdesk_omnibus'
		);
	},

	ClockInterface::class                                 => create( RequestTimeClock::class ),

	ChainCurrencies::class                                => static function ( Container $c ) {
		return new ChainCurrencies(
			[
				$c->get( RawDefaultCurrencies::class ),
				$c->get( WPMLCurrencies::class ),
			]
		);
	},

	WPMLCurrencies::class                                 => autowire()
	->constructorParameter( 'default_resolver', get( RawDefaultCurrencyResolver::class ) ),

	CurrencyResolverFactory::class                        => create()->constructor( get( 'plugin.wcml' ) ),

	CurrencyResolver::class                               => static function ( CurrencyResolverFactory $f ) {
		return $f->get_resolver();
	},
];
