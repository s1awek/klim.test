<?php

/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.1
 */

defined('ABSPATH') || exit;

$meta = get_post_meta(get_the_ID());

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if (!function_exists('wc_get_gallery_image_html')) {
    return;
}

global $product;
if (empty($product)) {
    $product = wc_get_product(get_the_ID());
}

$pid = apply_filters(
    'svi_product_id',
    $product->get_id()
);
$svi_loaddata = $this->loadProduct($pid);
$is_ajax_request = wp_doing_ajax();
$initial_markup = $this->get_initial_gallery_markup($svi_loaddata, $pid);

if (!$is_ajax_request) {
    $this->prime_product_data($pid, $svi_loaddata);
}

$wcsvi_data = '';

if ($is_ajax_request) {
    $svi_load = wp_json_encode($svi_loaddata);
    $wcsvi_data = function_exists('wc_esc_json') ? wc_esc_json($svi_load) : _wp_specialchars($svi_load, ENT_QUOTES, 'UTF-8', true);
}
//wp_localize_script($this->plugin_name, 'wcsvi_' . $pid, json_encode($this->loadProduct($pid)));
//$columns           = apply_filters('woocommerce_product_thumbnails_columns', 4);
$post_thumbnail_id = $product->get_image_id();

$sviVariable = '';

if (array_key_exists('slugs', $svi_loaddata) && !empty($svi_loaddata['slugs'] && !$product->is_type('simple')))
    $sviVariable = 'svi-variable';

$wrapper_classes   = apply_filters(
    'woocommerce_single_product_image_gallery_classes',
    array(
        'gallery-svi',
        'woocommerce-product-gallery',
        'woocommerce-product-gallery--' . ($product->get_image_id() ? 'with-images' : 'without-images'),
        $sviVariable,
        'images',
    )
);

if (property_exists($this->options, 'sviforce_image') && $this->options->sviforce_image) {
    if (($key = array_search('images', $wrapper_classes)) !== false) {
        unset($wrapper_classes[$key]);
    }
}
// Remove 'images' class when using Divi Builder
if ($this->options->template === 'Divi' && $this->validate_runningDivi($product)) {
    if (($key = array_search('images', $wrapper_classes)) !== false) {
        unset($wrapper_classes[$key]);
    }
}
if (property_exists($this->options, 'custom_class') && !empty(trim($this->options->custom_class))) {
    $wrapper_classes[] = $this->options->custom_class;
}
$wrapper_classes = array_values($wrapper_classes);

$div_attributes = [
    'data-sviproduct_id="' . esc_attr($pid) . '"',
    'data-wcsvi-ref="' . esc_attr($pid) . '"',
    'class="' . esc_attr(implode(' ', array_map('sanitize_html_class', $wrapper_classes))) . '"',
];

$transition_style = 'transition: opacity .25s ease-in-out;';

if (!empty($initial_markup) || $this->options->preload_fimg) {
    $transition_style = 'opacity: 1; ' . $transition_style;
} else {
    $transition_style = 'opacity: 0; ' . $transition_style;
}

$div_attributes[] = 'style="' . esc_attr($transition_style) . '"';

if ($is_ajax_request) {
    $div_attributes[] = 'data-wcsvi="' . $wcsvi_data . '"';
}

?>
<div <?php echo implode(' ', $div_attributes); ?>>
    <?php do_action('svi_before_images'); ?>
    <?php if ($this->options->template == 'flatsome') { ?>
        <?php (!defined('DOING_AJAX') ? do_action('flatsome_sale_flash') : ''); ?>

        <div class="image-tools absolute top show-on-hover right z-3">
            <?php do_action('flatsome_product_image_tools_top'); ?>
        </div>
    <?php } ?>
    <div class="svi_wrapper">
        <?php if ($initial_markup) : ?>
            <div class="svi-initial-holder">
                <?php echo $initial_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        <?php elseif ($this->options->preload_fimg) : ?>
            <div class="svi-initial-holder">
                <?php echo get_the_post_thumbnail($pid, $this->options->main_imagesize); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        <?php endif; ?>
        <div class="svi-app-entry" data-svi-mount></div>
    </div>&nbsp;
</div>
