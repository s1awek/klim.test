<?php

use WPDesk\Omnibus\Core\Admin;
use WPDesk\Omnibus\Core\Batch;
use WPDesk\Omnibus\Core\Cache;
use WPDesk\Omnibus\Core\Interceptor;
use WPDesk\Omnibus\Core\Multicurrency;
use WPDesk\Omnibus\Core\PriceMessage;
use WPDesk\Omnibus\Core\Product;

return [
	Admin\PluginOptions::class,
	Admin\PriceTableMetabox::class,
	Admin\AjaxPriceTable::class,
	Admin\RepairTools::class,
	Admin\StatusPage::class,
	Batch\BatchProcess::class,
	Cache\InvalidateCache::class,
	Interceptor\CreateAfterProductSave::class,
	Interceptor\UpdateBeforeProductSave::class,
	PriceMessage\Frontend\ArchiveMessageDisplay::class,
	PriceMessage\Frontend\CartMessageDisplay::class,
	PriceMessage\Frontend\SingleProductMessageDisplay::class,
	PriceMessage\Shortcode\ShortcodePrice::class,
	PriceMessage\Shortcode\ShortcodePriceMessage::class,
	Product\HistoricalPricesCleanup::class,

	// WCML integration, which has to be safe to use, even when WCML not active.
	Interceptor\WPMLInterceptor::class,
	Product\ProductChangesRegistry::class,
	Multicurrency\WPMLCurrencyAdded::class,
];
