<?php
/**
 * Plugin Name: WooCommerce Przelewy24 Payment Gateway
 * Plugin URI: http://www.przelewy24.pl/pobierz
 * Description: Przelewy24 Payment gateway for woocommerce.
 * Version: 1.0.0
 * Author: DialCom24 Sp. z o.o.
 * Author URI: http://www.przelewy24.pl/
 */

define('PRZELEWY24_URI', plugin_dir_url(__FILE__));

add_action('plugins_loaded', 'woocommerce_p24_init', 0);
function woocommerce_p24_init()
{
    if (!class_exists('WC_Payment_Gateway') || !extension_loaded('soap') || !extension_loaded('curl')) return;

    load_plugin_textdomain('przelewy24', false, dirname(plugin_basename(__FILE__)) . '/lang/');

    require_once 'includes/shared-libraries/autoloader.php';
    require_once 'includes/class_przelewy24.php';
    require_once 'includes/WC_Gateway_Przelewy24.php';
    require_once 'includes/shared-libraries/classes/Przelewy24Product.php';

    add_filter('woocommerce_payment_gateways', 'woocommerce_p24_add_gateway');
    add_action('woocommerce_checkout_update_order_meta', 'p24_regulation_accept_update_order_meta');
}

/**
 * add_action.
 */
function woocommerce_p24_add_gateway($methods)
{
    $methods[] = 'WC_Gateway_Przelewy24';
    return $methods;
}

function p24_regulation_accept_update_order_meta($order_id)
{
    if ($_POST['p24_regulation_accept']) update_post_meta($order_id, 'p24_regulation_accept', esc_attr($_POST['p24_regulation_accept']));
}

