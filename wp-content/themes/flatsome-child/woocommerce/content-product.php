<?php

/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see              https://docs.woocommerce.com/document/template-structure/
 * @package          WooCommerce/Templates
 * @version          3.6.0
 * @flatsome-version 3.16.0
 */

defined('ABSPATH') || exit;

global $post, $product, $wc_cpdf;

$badge_style = get_theme_mod('bubble_style', 'style1');

if ($badge_style == 'style1') $badge_style = 'circle';
if ($badge_style == 'style2') $badge_style = 'square';
if ($badge_style == 'style3') $badge_style = 'frame';
?>


<?php
// Ensure visibility.
if (fl_woocommerce_version_check('4.4.0')) {
	if (empty($product) || false === wc_get_loop_product_visibility($product->get_id()) || !$product->is_visible()) {
		return;
	}
} else {
	if (empty($product) || !$product->is_visible()) {
		return;
	}
}

// Check stock status.
$out_of_stock = !$product->is_in_stock();

// Extra post classes.
$classes = array();
$classes[] = 'product-small';
$classes[] = 'col';
$classes[] = 'has-hover';

$product_type = $product->get_type();
$has_new_color = false;
if ($product_type === 'variable') {
	$variations = $product->get_available_variations();
	foreach ($variations as $key => $variation) {
		$new_color = get_post_meta($variation['variation_id'], '_mycheckbox', true);
		if ($new_color === 'yes') {
			$has_new_color = true;
		}
	}
}

if ($out_of_stock) $classes[] = 'out-of-stock';

?>
<div <?php wc_product_class($classes, $product); ?>>
	<div class="col-inner">
		<?php do_action('woocommerce_before_shop_loop_item'); ?>
		<div class="product-small box <?php echo flatsome_product_box_class(); ?>">
			<div class="box-image">
				<div class="<?php echo flatsome_product_box_image_class(); ?>">
					<a href="<?php echo get_the_permalink(); ?>" aria-label="<?php echo esc_attr($product->get_title()); ?>">
						<?php
						/**
						 *
						 * @hooked woocommerce_get_alt_product_thumbnail - 11
						 * @hooked woocommerce_template_loop_product_thumbnail - 10
						 */
						do_action('flatsome_woocommerce_shop_loop_images');
						?>
					</a>
				</div>
				<div class="image-tools is-small top right show-on-hover">
					<?php do_action('flatsome_product_box_tools_top'); ?>
				</div>
				<div class="image-tools is-small hide-for-small bottom left show-on-hover">
					<?php do_action('flatsome_product_box_tools_bottom'); ?>
				</div>
				<div class="image-tools <?php echo flatsome_product_box_actions_class(); ?>">
					<?php do_action('flatsome_product_box_actions'); ?>
				</div>
				<?php if ($out_of_stock) { ?><div class="out-of-stock-label"><?php _e('Out of stock', 'woocommerce'); ?></div><?php } ?>
			</div>

			<div class="box-text <?php echo flatsome_product_box_text_class(); ?>">
				<?php
				do_action('woocommerce_before_shop_loop_item_title');

				echo '<div class="title-wrapper">';
				do_action('woocommerce_shop_loop_item_title');
				echo '</div>';


				echo '<div class="price-wrapper">';
				do_action('woocommerce_after_shop_loop_item_title');
				echo '</div>';
				?>
				<div class="badge-container">
					<?php if ($product->is_on_sale()) : ?>
						<?php
						$custom_text = get_theme_mod('sale_bubble_text');
						$text        = $custom_text ? $custom_text : __('Sale!', 'woocommerce');

						if (get_theme_mod('sale_bubble_percentage')) {
							$text = flatsome_presentage_bubble($product, $text);
						}
						?>
						<?php echo apply_filters('woocommerce_sale_flash', '<div class="callout badge badge-' . $badge_style . '"><div class="badge-inner secondary on-sale"><span class="onsale">' .  $text . '</span></div></div>', $post, $product); ?>

					<?php endif; ?>
					<?php echo apply_filters('flatsome_product_labels', '', $post, $product, $badge_style); ?>
					<?php if ($has_new_color) : ?>
						<div class="badge callout badge-square">
							<div class="badge-inner callout-new-bg is-small new-bubble">NOWY KOLOR!</div>
						</div>
					<?php endif; ?>
				</div>
				<?php
				do_action('flatsome_product_box_after');

				?>
			</div>
		</div>
		<?php do_action('woocommerce_after_shop_loop_item'); ?>
	</div>
</div><?php /* empty PHP to avoid whitespace */ ?>