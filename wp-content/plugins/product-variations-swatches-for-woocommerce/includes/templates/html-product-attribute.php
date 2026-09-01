<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$vi_wpvs_attribute_name     = $attribute->get_name();
$vi_wpvs_attribute_position = $attribute->get_position();
$vi_wpvs_is_taxonomy        = $attribute->is_taxonomy();

if ( $vi_wpvs_is_taxonomy ) {
    $vi_wpvs_global_settings_url = admin_url( 'admin.php?page=woocommerce-product-variations-swatches-global-attrs&viwvps_attr=' . $attribute->get_id() );
    $vi_attribute_profile        = $vi_attribute_settings['attribute_profile'][ $vi_wpvs_attribute_name ] ?? null;
    $vi_attribute_type           = $vi_attribute_settings['attribute_type'][ $vi_wpvs_attribute_name ] ?? null;
    $vi_attribute_colors         = $vi_attribute_settings['attribute_colors'][ $vi_wpvs_attribute_name ] ?? array();
    $vi_attribute_color_separator = $vi_attribute_settings['attribute_color_separator'][ $vi_wpvs_attribute_name ] ?? array();
    $vi_attribute_img_ids        = $vi_attribute_settings['attribute_img_ids'][ $vi_wpvs_attribute_name ] ?? array();
    $vi_attribute_display_type   = $vi_attribute_settings['attribute_display_type'][ $vi_wpvs_attribute_name ] ?? null;
} else {
    $vi_wpvs_global_settings_url = admin_url( 'admin.php?page=woocommerce-product-variations-swatches#custom_attrs' );
    $vi_wpvs_attribute_name_     = html_entity_decode( $vi_wpvs_attribute_name, ENT_NOQUOTES, 'UTF-8' );
    $vi_attribute_profile        = $vi_attribute_settings['attribute_profile'][ $vi_wpvs_attribute_name_ ] ?? null;
    $vi_attribute_type           = $vi_attribute_settings['attribute_type'][ $vi_wpvs_attribute_name_ ] ?? null;
    $vi_attribute_colors         = $vi_attribute_settings['attribute_colors'][ $vi_wpvs_attribute_name_ ] ?? array();
    $vi_attribute_color_separator = $vi_attribute_settings['attribute_color_separator'][ $vi_wpvs_attribute_name_ ] ?? array();
    $vi_attribute_img_ids        = $vi_attribute_settings['attribute_img_ids'][ $vi_wpvs_attribute_name_ ] ?? array();
    $vi_attribute_display_type   = $vi_attribute_settings['attribute_display_type'][ $vi_wpvs_attribute_name_ ] ?? null;
}
$vi_wpvs_need_edit = ( $vi_attribute_type || $vi_attribute_profile ) ? 1 : 0;
?>
    <tr>
        <td colspan="4">
            <p>
                <?php esc_html_e( 'You can customize the general display settings for ', 'product-variations-swatches-for-woocommerce' ); ?>
                <a href="<?php echo esc_url( $vi_wpvs_global_settings_url ); ?>" target="_blank"><?php esc_html_e( ' attribute ', 'product-variations-swatches-for-woocommerce' );
                    echo wp_kses_post( wc_attribute_label( $vi_wpvs_attribute_name ) ); ?></a>
                <?php esc_html_e( ' of all products or click ', 'product-variations-swatches-for-woocommerce' ); ?>
                <a href="#" class="vi-wpvs-attribute-info-custom-open"><?php esc_html_e( 'here', 'product-variations-swatches-for-woocommerce' ); ?></a>
                <?php esc_html_e( ' to configure settings specifically for this product.', 'product-variations-swatches-for-woocommerce' ); ?>
            </p>
            <div class="vi-wpvs-attribute-wrap<?php echo esc_attr( $vi_wpvs_need_edit ? '' : ' vi-wpvs-hidden' ); ?>">

                <?php wp_nonce_field( 'viwpvs-dokan-save-attributes', 'viwpvs_nonce' ); ?>

                <div class="vi-wpvs-attribute-info-wrap vi-wpvs-attribute-info-custom-wrap">
                    <div class="vi-wpvs-attribute-loop-enable">
                        <a class="button button-primary" disabled target="_blank"
                           href="https://villatheme.com/extensions/woocommerce-product-variations-swatches/">
                            <?php esc_html_e( 'Show in product list - Premium version only', 'product-variations-swatches-for-woocommerce' ); ?>
                        </a>
                    </div>
                    <div class="vi-wpvs-attribute-display-type">
                        <select name="vi_attribute_display_type[<?php echo esc_attr( $i ); ?>]"
                                title="<?php esc_attr_e( 'Choose display style', 'product-variations-swatches-for-woocommerce' ); ?>">
                            <option value="0" <?php selected( $vi_attribute_display_type, '0' ); ?>>
                                <?php esc_html_e( 'Global Style', 'product-variations-swatches-for-woocommerce' ); ?>
                            </option>
                            <option value="vertical" <?php selected( $vi_attribute_display_type, 'vertical' ); ?>>
                                <?php esc_html_e( 'Vertical', 'product-variations-swatches-for-woocommerce' ); ?>
                            </option>
                            <option value="horizontal" <?php selected( $vi_attribute_display_type, 'horizontal' ); ?>>
                                <?php esc_html_e( 'Horizontal', 'product-variations-swatches-for-woocommerce' ); ?>
                            </option>
                        </select>
                    </div>
                    <div class="vi-wpvs-attribute-type">
                        <select name="vi_attribute_type[<?php echo esc_attr( $i ); ?>]"
                                title="<?php esc_attr_e( 'Choose display type', 'product-variations-swatches-for-woocommerce' ); ?>">
                            <option value="0" <?php selected( $vi_attribute_type, '0' ); ?>>
                                <?php esc_html_e( 'Global Type', 'product-variations-swatches-for-woocommerce' ); ?>
                            </option>
                            <?php
                            foreach ( $attribute_types as $vi_wpvs_k => $vi_wpvs_v ) {
                                ?>
                                <option value="<?php echo esc_attr( $vi_wpvs_k ); ?>" <?php selected( $vi_attribute_type, $vi_wpvs_k ); ?>><?php echo esc_html( $vi_wpvs_v ); ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="vi-wpvs-attribute-profile">
                        <select name="vi_attribute_profile[<?php echo esc_attr( $i ); ?>]"
                                title="<?php esc_attr_e( 'Choose swatches profile', 'product-variations-swatches-for-woocommerce' ); ?>">
                            <option value="0" <?php selected( $vi_attribute_profile, '0' ); ?>>
                                <?php esc_html_e( 'Global Profile', 'product-variations-swatches-for-woocommerce' ); ?>
                            </option>
                            <?php
                            foreach ( $vi_wpvs_ids as $vi_wpvs_k => $vi_wpvs_id ) {
                                ?>
                                <option value="<?php echo esc_attr( $vi_wpvs_id ); ?>" <?php selected( $vi_attribute_profile, $vi_wpvs_id ); ?>><?php echo esc_html( $vi_wpvs_name[ $vi_wpvs_k ] ); ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="vi-wpvs-change-product-image"
                         title="<?php echo esc_html__( 'When selecting an attribute value, change product image according to attribute/variation image(Only use for Image/Variation image type)', 'product-variations-swatches-for-woocommerce' ); ?>">
                        <a class="button button-primary" disabled target="_blank"
                           href="https://villatheme.com/extensions/woocommerce-product-variations-swatches/">
                            <?php esc_html_e( 'Show in product list - Premium version only', 'product-variations-swatches-for-woocommerce' ); ?>
                        </a>
                    </div>
                </div>
                <div class="vi-wpvs-attribute-value-wrap">
                    <input type="hidden" class="viwpvs_save_attribute" name="viwpvs_save_attribute[<?php echo esc_attr( $i ); ?>]"
                           value="<?php echo esc_attr( $vi_wpvs_need_edit ); ?>">
                    <p><strong><?php esc_html_e( 'Please click \'Save attributes\' before setting the new attribute item', 'product-variations-swatches-for-woocommerce' ); ?></strong></p>
                    <?php
                    if ( $vi_wpvs_is_taxonomy ) {
                        foreach ( $attribute_options as $vi_wpvs_option ) {
                            $vi_wpvs_term = get_term( $vi_wpvs_option );
                            if ( ! $vi_wpvs_term ) {
                                continue;
                            }
                            $vi_wpvs_terms_settings                 = ! empty( get_term_meta( $vi_wpvs_option, 'vi_wpvs_terms_params', true ) ) ? get_term_meta( $vi_wpvs_option, 'vi_wpvs_terms_params', true ) : array();
                            $vi_wpvs_attr_item_name                 = $vi_wpvs_term->name;
                            $vi_wpvs_attr_item_color_separator       = $vi_attribute_color_separator[ $vi_wpvs_option ] ?? $vi_wpvs_terms_settings['color_separator'] ?? '1';
                            $vi_wpvs_attr_item_colors                = $vi_attribute_colors[ $vi_wpvs_option ] ?? $vi_wpvs_terms_settings['color'] ?? '';
                            $vi_wpvs_attr_item_img_id                = $vi_attribute_img_ids[ $vi_wpvs_option ] ?? $vi_wpvs_terms_settings['img_id'] ?? '';
                            $vi_wpvs_attr_item_color_separator_name = 'vi_attribute_color_separator[' . $i . '][' . $vi_wpvs_term->term_id . ']';
                            $vi_wpvs_attr_item_color_name            = 'vi_attribute_colors[' . $i . '][' . $vi_wpvs_term->term_id . '][]';
                            $vi_wpvs_attr_item_image_name            = 'vi_attribute_images[' . $i . '][' . $vi_wpvs_term->term_id . ']';
                            wc_get_template(
                                'html-product-attribute-item.php',
                                array(
                                    'vi_wpvs_class'                     => 'vi-wpvs-attribute-value vi-wpvs-attribute-taxonomy-value vi-wpvs-attribute-taxonomy-value-' . $vi_wpvs_term->term_id,
                                    'vi_wpvs_i'                         => $i,
                                    'vi_wpvs_attr_item'                 => $vi_wpvs_term,
                                    'vi_wpvs_term_id'                   => $vi_wpvs_term->term_id,
                                    'vi_wpvs_item_name'                 => $vi_wpvs_attr_item_name,
                                    'vi_wpvs_img_id'                    => $vi_wpvs_attr_item_img_id,
                                    'vi_wpvs_colors'                    => $vi_wpvs_attr_item_colors,
                                    'vi_wpvs_attr_item_color_separator' => $vi_wpvs_attr_item_color_separator,
                                    'vi_wpvs_vi_attribute_type'        => $vi_attribute_type,
                                    'vi_wpvs_vi_default_colors'        => $vi_default_colors,
                                    'vi_wpvs_attr_item_image_name'     => $vi_wpvs_attr_item_image_name,
                                    'vi_wpvs_attr_item_color_name'     => $vi_wpvs_attr_item_color_name,
                                    'vi_wpvs_attr_item_color_separator_name' => $vi_wpvs_attr_item_color_separator_name,
                                ),
                                '',
                                VI_WOO_PRODUCT_VARIATIONS_SWATCHES_TEMPLATES
                            );
                        }
                    } else {
                        if ( is_array( $attribute_options ) && ! empty( $attribute_options ) ) {
                            foreach ( $attribute_options as $vi_wpvs_k => $vi_wpvs_attribute_option ) {
                                $vi_wpvs_attr_item_name                 = $vi_wpvs_attribute_option;
                                $vi_wpvs_attr_item_color_separator       = $vi_attribute_color_separator[ $vi_wpvs_k ] ?? '1';
                                $vi_wpvs_attr_item_colors                = $vi_attribute_colors[ $vi_wpvs_k ] ?? array();
                                $vi_wpvs_attr_item_img_id                = $vi_attribute_img_ids[ $vi_wpvs_k ] ?? '';
                                $vi_wpvs_attr_item_colors_id             = current_time( 'timestamp' ) . '-' . $vi_wpvs_k;
                                $vi_wpvs_attr_item_color_separator_name = 'vi_attribute_color_separator[' . $i . '][]';
                                $vi_wpvs_attr_item_color_name            = 'vi_attribute_colors[' . $i . '][' . $vi_wpvs_attr_item_colors_id . '][]';
                                $vi_wpvs_attr_item_image_name            = 'vi_attribute_images[' . $i . '][]';
                                wc_get_template(
                                    'html-product-attribute-item.php',
                                    array(
                                        'vi_wpvs_class'                     => 'vi-wpvs-attribute-value',
                                        'vi_wpvs_i'                         => $i,
                                        'vi_wpvs_attr_item'                 => $vi_wpvs_attribute_option,
                                        'vi_wpvs_term_id'                   => 0,
                                        'vi_wpvs_item_name'                 => $vi_wpvs_attr_item_name,
                                        'vi_wpvs_attr_item_image_name'     => $vi_wpvs_attr_item_image_name,
                                        'vi_wpvs_attr_item_color_name'     => $vi_wpvs_attr_item_color_name,
                                        'vi_wpvs_attr_item_color_separator_name' => $vi_wpvs_attr_item_color_separator_name,
                                        'vi_wpvs_img_id'                    => $vi_wpvs_attr_item_img_id,
                                        'vi_wpvs_colors'                    => $vi_wpvs_attr_item_colors,
                                        'vi_wpvs_attr_item_color_separator' => $vi_wpvs_attr_item_color_separator,
                                        'vi_wpvs_vi_attribute_type'        => $vi_attribute_type,
                                        'vi_wpvs_vi_default_colors'        => $vi_default_colors,
                                    ),
                                    '',
                                    VI_WOO_PRODUCT_VARIATIONS_SWATCHES_TEMPLATES
                                );
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </td>
    </tr>
<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound