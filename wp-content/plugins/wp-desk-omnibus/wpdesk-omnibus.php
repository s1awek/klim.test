<?php
/**
	Plugin Name: Omnibus for WooCommerce
	Plugin URI: https://www.wpdesk.pl/sklep/omnibus-woocommerce/
	Description: 100% compliance with the EU Omnibus directive. Automatically displays the lowest product price from the last 30 days.
	Product: WP Desk Omnibus
	Version: 2.2.13
	Author: WP Desk
	Author URI: https://www.wpdesk.pl/
	Text Domain: wpdesk-omnibus
	Domain Path: /lang/
	Requires at least: 6.4
	Tested up to: 6.8
	WC requires at least: 9.4
	WC tested up to: 9.8
	Requires PHP: 7.4
	Requires Plugins: woocommerce

	@package \WPDesk\Omnibus

	Copyright 2023 WP Desk Ltd.

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$plugin_version = '2.2.13';
$plugin_name        = 'WP Desk Omnibus';
$plugin_class_name  = '\WPDesk\Omnibus\Core\Plugin';
$plugin_text_domain = 'wpdesk-omnibus';
$product_id         = 'WP Desk Omnibus';
$plugin_file        = __FILE__;
$plugin_dir         = __DIR__;

$requirements = [
	'php'     => '7.4',
	'wp'      => '6.3',
	'plugins' => [
		[
			'name'      => 'woocommerce/woocommerce.php',
			'nice_name' => 'WooCommerce',
			'version'   => '8.8',
		],
	],
	'modules' => [
		[
			'name'      => 'intl',
			'nice_name' => 'intl',
		],
	],
];

require __DIR__ . '/vendor_prefixed/php-di/php-di/src/functions.php';
require __DIR__ . '/vendor_prefixed/wpdesk/wp-plugin-flow-common/src/plugin-init-php52.php';
