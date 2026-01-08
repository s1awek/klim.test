<?php

/**
 * Template loader used to replace WooCommerce product image template calls.
 *
 * Ensures Smart Variations Images can supply its gallery when builders invoke
 * `wc_get_template( 'single-product/product-image.php' )` directly (e.g. Elementor).
 *
 * @package    Smart_Variations_Images
 * @subpackage Smart_Variations_Images/public/partials
 * @since      5.2.20
 */

defined('ABSPATH') || exit;

$instance = Smart_Variations_Images_Public::get_current_instance();
$context = Smart_Variations_Images_Public::pop_template_context();

if (!$instance instanceof Smart_Variations_Images_Public) {
    Smart_Variations_Images_Public::clear_current_instance();
    if (is_array($context) && !empty($context['located']) && file_exists($context['located'])) {
        include $context['located'];
    }
    return;
}

$output = $instance->capture_frontend_template();

Smart_Variations_Images_Public::clear_current_instance();

if ('' === trim($output) && is_array($context) && !empty($context['located']) && file_exists($context['located'])) {
    include $context['located'];
    return;
}

echo $output;
