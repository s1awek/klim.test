<?php

$clearedCacheOf = 'WP object cache';
wp_cache_flush();

// Sources:
// https://hotexamples.com/examples/-/WpeCommon/purge_varnish_cache/php-wpecommon-purge_varnish_cache-method-examples.html
// https://blog.letsgodev.com/tips/clear-all-cache/

// AUTOPTIMIZE
if( class_exists('autoptimizeCache') && method_exists( 'autoptimizeCache', 'clearall') ) {
	autoptimizeCache::clearall();
	$clearedCacheOf .= ',  Autoptimize';
  }

// ROCKET CACHE
if ( function_exists( 'rocket_clean_domain' ) ) {
	rocket_clean_domain();
	$clearedCacheOf .= ',  WP Rocket';
}

// WP ENGINE
elseif ( class_exists( 'WpeCommon' ) ) {
	if (method_exists('WpeCommon', 'purge_memcached')) { WpeCommon::purge_memcached(); }
	if (method_exists('WpeCommon', 'clear_maxcdn_cache')) { WpeCommon::clear_maxcdn_cache(); }
	if (method_exists('WpeCommon', 'purge_varnish_cache')) { WpeCommon::purge_varnish_cache(); }
	$clearedCacheOf .= ', WP Engine';
}

// W3 TOTAL CACHE
elseif ( function_exists( 'w3tc_pgcache_flush' ) ) {
	w3tc_pgcache_flush();
	$clearedCacheOf .= ', W3 Total Cache';
}

// WP-Optimize
elseif ( function_exists( 'WP_Optimize' ) ) {
	WP_Optimize()->get_page_cache()->purge();
	$clearedCacheOf .= ', WP-Optimzie';
}

// BREEZE
elseif ( class_exists( 'Breeze_Admin' ) ) {
	do_action('breeze_clear_all_cache');
	$clearedCacheOf .= ', Breeze';
}

// SITEGROUND
// via SG OPTIMIZER @ https://wordpress.org/plugins/sg-cachepress/
elseif ( function_exists( 'sg_cachepress_purge_cache' ) ) {
	sg_cachepress_purge_cache();
	$clearedCacheOf .= ', Siteground';
}

// WP SUPER CACHE
elseif ( function_exists( 'wp_cache_clear_cache' ) ) {
    wp_cache_clear_cache();
    $clearedCacheOf .= ', WP Super Cache';
}

// CACHE ENABLER
elseif( class_exists('Cache_Enabler') && method_exists('Cache_Enabler', 'clear_total_cache') ) {
	Cache_Enabler::clear_total_cache();
	$clearedCacheOf .= ', Cache Enabler';
}

// or an alternative method ( https://github.com/Automattic/wp-super-cache/issues/628 )
// elseif ( function_exists( 'wp_cache_clean_cache' ) ) { // WP Super Cache
// 	global $file_prefix;
// 	wp_cache_clean_cache( $file_prefix );
// }

// WP FASTEST CACHE
// https://www.wpfastestcache.com/tutorial/delete-the-cache-by-calling-the-function/
elseif( function_exists( 'wpfc_clear_all_cache;' ) ) {
	wpfc_clear_all_cache();
	$clearedCacheOf .= ', WP Fastest Cache';
}

// LITESPEED CACHE

elseif ( class_exists('\LiteSpeed\Purge') ) {
	\LiteSpeed\Purge::purge_all();
	$clearedCacheOf .= ', Litespeed Cache';
}

// COMET CACHE
elseif( class_exists("comet_cache") ) {
	comet_cache::clear();
	$clearedCacheOf .= ', Comet Cache';
}

// HUMMINGBIRD
elseif( class_exists('\Hummingbird\WP_Hummingbird') && method_exists('\Hummingbird\WP_Hummingbird', 'flush_cache') ) {
  \Hummingbird\WP_Hummingbird::flush_cache();
  $clearedCacheOf .= ', Hummingbird';
}

trigger_error('WP Full Picture cleared cache of ' . $clearedCacheOf ); // When this line is enabled it can occasionally cause errors that are visible on screen

?>
