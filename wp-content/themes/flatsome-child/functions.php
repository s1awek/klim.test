<?php
// Add custom Theme Functions here
function wpdocs_dequeue_script()
{
    wp_dequeue_style('cwginstock_bootstrap');
}
add_action('wp_enqueue_scripts', 'wpdocs_dequeue_script', 9999);

function klim_scripts_method()
{
    $child_js_path = get_stylesheet_directory() . '/assets/js/scripts.js'; //Abs path to file, needed by filemtime()
    $themecsspath = get_stylesheet_directory() . '/css/style.css';
    wp_enqueue_style(
        'child-theme-styles',
        get_stylesheet_directory_uri() . '/css/style.css',
        array(),
        filemtime($themecsspath)
    );
    wp_enqueue_script('custom-script', get_stylesheet_directory_uri() . '/assets/js/scripts.js', array('jquery'), filemtime($child_js_path));
}
add_action('wp_enqueue_scripts', 'klim_scripts_method', 999);

function patricks_custom_variation_price($price, $product)
{
    $target_product_types = array(
        'variable'
    );
    if (in_array($product->product_type, $target_product_types)) {
        // if variable product return and empty string
        return '';
    }
    // return normal price
    return $price;
}
// add_filter('woocommerce_get_price_html', 'patricks_custom_variation_price', 10, 2);

add_filter('woocommerce_product_tabs', 'klim_remove_product_tabs', 98);

function klim_remove_product_tabs($tabs)
{
    unset($tabs['additional_information']);
    return $tabs;
}

//add_filter('woocommerce_show_variation_price',      function () {
//return TRUE;
//});

// removing the price of variable products
//remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);

// Change location of
//add_action('woocommerce_single_product_summary', 'custom_wc_template_single_price', 10);
function custom_wc_template_single_price()
{
    global $product;

    // Variable product only
    if ($product->is_type('variable')) :

        // Main Price
        $prices = array($product->get_variation_price('min', true), $product->get_variation_price('max', true));
        $price = $prices[0] !== $prices[1] ? sprintf(__('From: %1$s', 'woocommerce'), wc_price($prices[0])) : wc_price($prices[0]);

        // Sale Price
        $prices = array($product->get_variation_regular_price('min', true), $product->get_variation_regular_price('max', true));
        sort($prices);
        $saleprice = $prices[0] !== $prices[1] ? sprintf(__('From: %1$s', 'woocommerce'), wc_price($prices[0])) : wc_price($prices[0]);

        if ($price !== $saleprice && $product->is_on_sale()) {
            $price = '<del>' . $saleprice . $product->get_price_suffix() . '</del> <ins>' . $price . $product->get_price_suffix() . '</ins>';
        }

?>
        <!-- <script>
            jQuery(document).ready(function($) {
                // When variable price is selected by default
                setTimeout(function() {
                    if (0 < $('input.variation_id').val() && null != $('input.variation_id').val()) {
                        if ($('p.availability'))
                            $('p.availability').remove();

                        $('p.price').html($('div.woocommerce-variation-price > span.price').html()).append('<p class="availability">' + $('div.woocommerce-variation-availability').html() + '</p>');
                        console.log($('div.woocommerce-variation-availability').html());
                    }
                }, 300);

                // On live variation selection
                $('select').blur(function() {
                    if (0 < $('input.variation_id').val() && null != $('input.variation_id').val()) {
                        if ($('.price p.availability') || $('.price p.stock'))
                            $('p.price p').each(function() {
                                $(this).remove();
                            });

                        $('p.price').html($('div.woocommerce-variation-price > span.price').html()).append('<p class="availability">' + $('div.woocommerce-variation-availability').html() + '</p>');
                        console.log($('input.variation_id').val());
                    } else {
                        $('p.price').html($('div.hidden-variable-price').html());
                        if ($('p.availability'))
                            $('p.availability').remove();
                        console.log('NULL');
                    }
                });
            });
        </script> -->
    <?php
    //echo '<p class="price">' . $product->get_price_html();
    else :
    //echo '<p class="price">' . $product->get_price_html() . '</p>';

    endif;
}
add_filter('woocommerce_product_tabs', 'woo_reorder_tabs', 98);
function woo_reorder_tabs($tabs)
{

    $tabs['description']['priority'] = 1;            // Description second
    $tabs['ux_custom_tab']['priority'] = 2;    // Additional information third
    $tabs['ux_video_tab']['priority'] = 3;            // Description second
    $tabs['reviews']['priority'] = 4;            // Reviews first
    $tabs['additional_information']['priority'] = 5;    // Additional information third

    return $tabs;
}


/**
 * @snippet       Add extra tick box at checkout
 * @how-to        Watch tutorial @ https://businessbloomer.com/?p=19055
 * @sourcecode    https://businessbloomer.com/?p=19854
 * @author        Rodolfo Melogli
 * @testedwith    WooCommerce 3.3.4
 */

//add_action('woocommerce_review_order_before_submit', 'klim_add_checkout_tickbox', 9);

function klim_add_checkout_tickbox()
{

    ?>

    <p class="form-row terms">
        <input type="checkbox" class="input-checkbox" name="deliverycheck" id="deliverycheck">
        <label for="deliverycheck" class="checkbox">Zapoznałem się z regulaminem sklepu</label>
    </p>

    <?php

}

// Show notice if customer does not tick

//add_action('woocommerce_checkout_process', 'klim_not_approved_delivery');

function klim_not_approved_delivery()
{
    if (!(int) isset($_POST['deliverycheck'])) {
        wc_add_notice(__('Proszę potwierdzić zapoznanie się z regulaminem.'), 'error');
    }
}

add_action('wp_footer', 'cart_update_qty_script');
function cart_update_qty_script()
{
    if (is_cart()) :
    ?>
        <script>
            jQuery('body').on('click', 'div.woocommerce .quantity .button', function() {
                jQuery("[name='update_cart']").trigger("click");
            });
        </script>
    <?php
    endif;
}


/**
 * Get the user's roles
 * @since 1.0.0
 */
function wellmadeonline_get_current_user_roles()
{
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = (array) $user->roles;
        return $roles; // This returns an array
        // Use this to return a single value
        // return $roles[0];
    } else {
        return array();
    }
}


function custom_file_download($url, $type = 'csv')
{

    // Set our default cURL options.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

    /* Optional: Set headers if needed.
    *    $headers = array();
    *    $headers[] = "Accept-Language: de";
    *    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    */

    // Retrieve file from $url.
    $result = curl_exec($ch);

    // Return error if cURL fails.
    if (curl_errno($ch)) {
        exit('Error:' . curl_error($ch));
    }
    curl_close($ch);

    // Identify the upload directory path.
    $uploads  = wp_upload_dir();

    // Generate full file path and set extension to $type.
    $filename = $uploads['basedir'] . '/' . strtok(basename($url), "?") . '.' . $type;

    // If the file exists locally, mark it for deletion.
    if (file_exists($filename)) {
        @unlink($filename);
    }

    // Save the new file retrieved from FTP.
    file_put_contents($filename, $result);

    // Return the URL to the newly created file.
    return str_replace($uploads['basedir'], $uploads['baseurl'], $filename);
}
// [custom_file_download("ftp://europedealer:KTRGeuropeFTP2%40@files.klim.com/Europe Inventory.csv","csv")]

add_action('pre_get_posts', 'alter_query', 9999);

function alter_query($query)
{

    // avoid infinite loop
    remove_action('pre_get_posts', __FUNCTION__);

    $is_wholesale = false;
    $posts_arr = [];
    if (isset(wellmadeonline_get_current_user_roles()[0]) && wellmadeonline_get_current_user_roles()[0] === 'wholesale_customer') {
        $is_wholesale = true;
    }

    if ($query->is_search && !$is_wholesale && !is_admin() && !current_user_can('administrator')) {
        $posts = get_posts(array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status'       => 'publish',
            'meta_query' => array(
                array(
                    'key'   => 'wholesale_product',
                    'value' => '1',
                )
            )
        ));
        $posts_arr = [];
        if ($posts) {
            foreach ($posts as $post) {
                $posts_arr[] = $post->ID;
            }
        }

        $query->set('post__not_in', $posts_arr);
    }


    return $query;
}


//Remove click event from chosen posts
function wellmadeonline_hide_posts()
{

    $style = '';
    $posts = get_posts(array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'post_status'       => 'publish',
        'meta_query' => array(
            array(
                'key'   => 'wholesale_product',
                'value' => '1',
            )
        )
    ));
    if ($posts) {
        foreach ($posts as $post) {
            $style .= '[data-post-id="' . $post->ID . '"], .post-' . $post->ID . ' a,';
        }
    }
    if (!is_admin()) {
        //return if wellmadeonline_get_current_user_roles() is empty
        if (!empty(wellmadeonline_get_current_user_roles())) {
            if (wellmadeonline_get_current_user_roles()[0] === 'wholesale_customer') {
                $style = substr($style, 0, -1);
            }
        }
    }
    echo '<style>' . $style . '{pointer-events: none;}' . '</style>';
}

add_action('wp_head', 'wellmadeonline_hide_posts', 100);

//Remove search
function search_fixer()
{
    if (isset($_GET['dgwt_wcas']) && isset($_GET['s'])) {
        unset($_GET['dgwt_wcas']);
    }
}

add_action('init', 'search_fixer');


// display an 'Out of Stock' label on archive pages
// add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_stock', 10);
// function woocommerce_template_loop_stock()
// {
//     global $product;
//     if (!$product->managing_stock() && !$product->is_in_stock()) {
//         return;
//     }

// }


/**
 * Ensure variation combinations are working properly - standard limit is 30
 *
 */

function woo_custom_ajax_variation_threshold($qty, $product)
{
    return 150;
}
add_filter('woocommerce_ajax_variation_threshold', 'woo_custom_ajax_variation_threshold', 10, 2);



add_filter('woocommerce_get_availability_text', 'set_custom_availability_text', 10, 2);
function set_custom_availability_text($availability, $product)
{
    // if (!isset(wellmadeonline_get_current_user_roles()[0]) && wellmadeonline_get_current_user_roles()[0] !== 'wholesale_customer' && wellmadeonline_get_current_user_roles()[0] !== 'administrator')
    if (true) {
        if ($product->is_in_stock()) {
            $availability = __('Produkt dostępny', 'woocommerce');
        } elseif (!$product->is_in_stock()) {
            $availability = __('Brak w magazynie', 'woocommerce');
        }
    }
    // else {
    //     $stock = $product->get_stock_quantity();
    //     if ($stock > 0 && $stock <= 15) {
    //         $availability = $stock . 'szt w magazynie';
    //     } elseif ($stock > 15) {
    //         $availability = '15+ szt w magazynie';
    //     }
    // }

    return $availability;
}

add_filter('the_title', 'xcsn_single_product_page_title', 0, 2);
function xcsn_single_product_page_title($title, $id)
{

    if ((is_product())) {

        $title = str_replace('-%SALE%', ' - ' . __('Sale!', 'woocommerce'), $title);
        //Logic for changing the WooCommerce Product Title on a Single Product page goes here
        return $title;
    }
    //Return the normal Title if conditions aren't met
    return $title;
}

function woocommerce_template_loop_product_title()
{
    $title = get_the_title();
    $title = str_replace('-%SALE%', ' - ' . __('Sale!', 'woocommerce'), $title);
    $title = str_replace('%SALE%', __('Sale!', 'woocommerce'), $title);
    echo '<p class="name product-title"><a href="' . get_the_permalink() . '">' . $title . '</a></p>';
}

function flatsome_child_setup()
{
    $path = get_stylesheet_directory() . '/languages';
    load_child_theme_textdomain('flatsome-child', $path);
}
add_action('after_setup_theme', 'flatsome_child_setup');

add_filter('woocommerce_email_attachments', 'webroom_attach_to_wc_emails', 10, 3);
function webroom_attach_to_wc_emails($attachments, $email_id, $order)
{

    // Avoiding errors and problems
    if (!is_a($order, 'WC_Order') || !isset($email_id)) {
        return $attachments;
    }
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['basedir'] . '/2021/07/formularz_zwrotu_towaru_klim.pdf'; // directory of the current theme


    // if a child theme is being used, then use this line to get the directory
    // $file_path = get_stylesheet_directory() . '/file.pdf';

    if ($email_id == 'customer_processing_order') {
        $attachments[] = $file_path;
        return $attachments;
    } else {
        return $attachments;
    }
}

add_action('woocommerce_review_order_before_submit', 'bt_add_checkout_checkbox', 10);
/**
 * Add WooCommerce additional Checkbox checkout field
 */
function bt_add_checkout_checkbox()
{

    woocommerce_form_field('checkout_checkbox', array( // CSS ID
        'type'          => 'checkbox',
        'class'         => array('form-row mycheckbox'), // CSS Class
        'label_class'   => array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),
        'input_class'   => array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
        'required'      => true, // Mandatory or Optional
        'label'         => __('I want to receive a proof of purchase electronically (e-mail)', 'flatsome-child'), // Label and Link
    ));
}

add_action('woocommerce_checkout_process', 'bt_add_checkout_checkbox_warning');
/**
 * Alert if checkbox not checked
 */
function bt_add_checkout_checkbox_warning()
{
    if (!(int) isset($_POST['checkout_checkbox'])) {
        wc_add_notice(__('Please acknowledge the Checkbox', 'flatsome-child'), 'error');
    }
}

add_action('woocommerce_checkout_update_order_meta', 'bt_checkout_field_order_meta_db');
/**
 * Add custom field as order meta with field value to database
 */
function bt_checkout_field_order_meta_db($order_id)
{
    if (!empty($_POST['checkout_checkbox'])) {
        update_post_meta($order_id, 'checkout_checkbox', sanitize_text_field($_POST['checkout_checkbox']));
    }
}


add_action('woocommerce_before_add_to_cart_form', 'selected_variation_price_replace_variable_price_range');
function selected_variation_price_replace_variable_price_range()
{
    global $product;

    if ($product->is_type('variable')) :
    ?><style>
            .woocommerce-variation-price {
                display: none;
            }
        </style>
        <script>
            jQuery(function($) {
                var p = 'p.price'
                q = $(p).html();

                $('form.cart').on('show_variation', function(event, data) {
                    if (data.price_html) {
                        $(p).html(data.price_html);
                    }
                }).on('hide_variation', function(event) {
                    $(p).html(q);
                });
            });
        </script>
    <?php
    endif;
}


function klim_polska_header_metadata()
{

    ?>
    <meta name="google-site-verification" content="usDq6V70ghQYTidUq1PWY-d4hhdpfQaG1-mAOJJk1VU" />
    <meta name="google-site-verification" content="5h0tB6gDgJgqmMPwExj7wTSAD0Yi3L3nqulstGIjnYE" />
    <meta name="google-site-verification" content="jzPrO2Qicq8cRZPHSx1yJe3rGrIbWOFArUdSOAml01E" />
    <meta name="google-site-verification" content="ZCNtIz2kyPtSIN5EKIx71WUpxFuMUydnKcIWVXLp4KQ" />

    <?php

}
add_action('wp_head', 'klim_polska_header_metadata');


//To override Flatsome Theme untranslated strings in woocommerce domian
add_filter('gettext', 'wpdocs_translate_text', 10, 3);
function wpdocs_translate_text($translated_text, $untranslated_text, $domain)
{

    if ('woocommerce' !== $domain) {
        return $translated_text;
    }

    if ($translated_text === 'A password will be sent to your email address.') {
        $translated_text = str_ireplace(
            'A password will be sent to your email address.',
            'Hasło zostanie wysłane na Twój adres e-mail',
            $translated_text
        );
    }
    if ($translated_text === 'Stworzyć konto?') {
        $translated_text = str_ireplace(
            'Stworzyć konto?',
            'Stwórz konto',
            $translated_text
        );
    }
    if ($translated_text === 'Wysłać na inny adres?') {
        $translated_text = str_ireplace(
            'Wysłać na inny adres?',
            'Wyślij na inny adres',
            $translated_text
        );
    }
    if ($translated_text === 'Cofnij?') {
        $translated_text = str_ireplace(
            'Cofnij?',
            'Cofnij',
            $translated_text
        );
    }
    if ($translated_text === 'W taki sposób twoja nazwa zostanie wyświetlona w sekcji Moje konto i w twoich opiniach') {
        $translated_text = str_ireplace(
            'W taki sposób twoja nazwa zostanie wyświetlona w sekcji Moje konto i w twoich opiniach',
            'W taki sposób Twoja nazwa zostanie wyświetlona w sekcji Moje konto i w Twoich opiniach',
            $translated_text
        );
    }
    if ($translated_text === 'Produkt dostępny na zamówienie') {
        $translated_text = str_ireplace(
            'Produkt dostępny na zamówienie',
            'Produkt dostępny na backorder',
            $translated_text
        );
    }


    return $translated_text;
}

//add_filter('woocommerce_product_query_meta_query', 'shop_only_instock_products', 99999, 2);
function shop_only_instock_products($meta_query, $query)
{
    //var_dump(is_allowed_user_role(), wp_get_current_user()->roles);
    // Only on shop archive pages
    if (is_admin() || is_search() || (!is_shop() && !is_product_category()) || is_allowed_user_role()) return $meta_query;

    $meta_query[] = array(
        'relation' => 'AND',
        array(
            'key'     => '_stock_status',
            'value'   => 'outofstock',
            'compare' => '!='
        ),
        array(
            'key'     => '_stock_status',
            'value'   => 'onbackorder',
            'compare' => '!='
        ),
    );
    return $meta_query;
}

//Custom star rating
add_filter('woocommerce_get_star_rating_html', 'change_rating_output', 10, 3);
function change_rating_output($html, $rating, $count)
{
    global $product;
    $html = '<div class="stars-outer"><span class="stars" style="width:' . (($rating / 5) * 100) . '%"></span>';
    $new_count = $product->get_rating_count();

    $html .= '<div class="rating-wrap"><p class="rating">' . esc_html($rating) . '</p><p class="count">(' . esc_html($new_count) . ')</p></div>';


    $html .= '</div>';
    return $html;
}

add_filter('automatic_updates_is_vcs_checkout', '__return_false', 1);
// Custom conditional function targeting specific user roles
function is_allowed_user_role()
{
    $targeted_roles = array('administrator', 'shop_manager', 'wholesale_customer'); // Here define your targeted user roles
    return (bool) array_intersect(wp_get_current_user()->roles, $targeted_roles);
}

//Backorders for wholesale customers
add_filter('woocommerce_product_get_stock_status', 'filter_product_stock_status');
add_filter('woocommerce_product_variation_get_stock_status', 'filter_product_stock_status');
function filter_product_stock_status($stock_status)
{
    //if (!is_allowed_user_role() && 'onbackorder' === $stock_status) {
    if ('onbackorder' === $stock_status) {
        $stock_status = 'outofstock';
    }
    return $stock_status;
}

add_filter('woocommerce_product_get_backorders', 'filter_product_get_backorders');
add_filter('woocommerce_product_variation_get_backorders', 'filter_product_get_backorders');
function filter_product_get_backorders($backorders)
{
    return is_allowed_user_role() ? $backorders : 'no';
}

add_filter('woocommerce_product_backorders_allowed', 'filter_product_backorders_allowed', 10, 3);
function filter_product_backorders_allowed($allowed, $product_id, $product)
{
    return is_allowed_user_role() ? $allowed : false;
}

// Pokaż przelew jako metodę płatności tylko dla adminów i wholesale_customer
add_filter('woocommerce_available_payment_gateways', 'klim_filter_payment_method');
function klim_filter_payment_method($available_gateways)
{
    if (isset($available_gateways['bacs']) && !is_allowed_user_role()) {
        unset($available_gateways['bacs']);
    }
    return $available_gateways;
}


if (IS_LOCAL) {
    add_filter('auto_update_plugin', '__return_false');
    add_filter('auto_update_theme', '__return_false');
}

add_filter('updraftplus_exclude_directory', 'my_updraftplus_exclude_directory', 10, 2);
function my_updraftplus_exclude_directory($filter, $dir)
{
    return (basename($dir) == '.git') ? true : $filter;
}

// add_filter('woocommerce_taxonomy_args_pa_rozmiar', 'testing');
// function testing($data)
// {

//     $data['hierarchical'] = true;
//     return $data;
// }
// add_filter('woocommerce_taxonomy_args_pa_dopasowanie', 'testing2');
// function testing2($data)
// {

//     $data['hierarchical'] = true;
//     return $data;
// }

// WooCommerce - Add order notes column to orders list
// Code goes in functions.php for your child theme
// not tested in a PHP snippet plugin

// Add column "Order Notes" on the orders page
add_filter('manage_edit-shop_order_columns', 'add_order_notes_column');
function add_order_notes_column($columns)
{
    $new_columns = (is_array($columns)) ? $columns : array();
    $new_columns['order_notes'] = 'Notatki';
    return $new_columns;
}

add_action('admin_print_styles', 'add_order_notes_column_style');
function add_order_notes_column_style()
{
    $css = '.post-type-shop_order table.widefat.fixed { table-layout: auto; width: 100%; }';
    $css .= 'table.wp-list-table .column-order_notes { min-width: 280px; text-align: left; }';
    $css .= '.column-order_notes ul { margin: 0 0 0 18px; list-style-type: disc; }';
    $css .= 'li .font-bold { font-weight: bold; }';
    //$css .= '.order_customer_note { color: #ee0000; }'; // red
    //$css .= '.order_private_note { color: #0000ee; }'; // blue
    wp_add_inline_style('woocommerce_admin_styles', $css);
}

// Add order notes to the "Order Notes" column
add_action('manage_shop_order_posts_custom_column', 'add_order_notes_content');
function add_order_notes_content($column)
{
    if ($column != 'order_notes') return;
    global $post, $the_order;
    if (empty($the_order) || $the_order->get_id() != $post->ID) {
        $the_order = wc_get_order($post->ID);
    }
    $args = array();
    $args['order_id'] = $the_order->get_id();
    $args['order_by'] = 'date_created';
    $args['order'] = 'ASC';
    $notes = wc_get_order_notes($args);
    if ($notes) {
        print '<ul>';
        foreach ($notes as $note) {
            if ($note->customer_note) {
                print '<li class="order_customer_note description">';
            } else {
                print '<li class="order_private_note description">';
            }
            $date = date('d/m/y H:i', strtotime($note->date_created));
            echo $note->added_by !== 'system' ? '<span class="font-bold">' : '';
            print $date . ' przez ' . $note->added_by . '<br>' . $note->content . '</li>';
            echo $note->added_by !== 'system' ? '</span>' : '';
        }
        print '</ul>';
    }
} // end function

function klim_stock_account_item()
{
    $id = get_option('woocommerce_shop_page_id');
    $url = get_field('stock_link', $id) ?? '#';
    if (wellmadeonline_get_current_user_roles()[0] === 'wholesale_customer' || wellmadeonline_get_current_user_roles()[0] === 'administrator') :
    ?>
        <li class="<?php echo 'custom-link'; ?>">
            <a target="_blank" rel="nofollow noopener" href="<?php echo esc_url($url); ?>">Aktualne stany</a>
        </li>
    <?php
    endif;
}
add_action('flatsome_account_links', 'klim_stock_account_item', 999);



// 1. Add custom field input @ Product Data > Variations > Single Variation
add_action('woocommerce_variation_options_pricing', 'klim_add_custom_field_to_variations', 10, 3);
function klim_add_custom_field_to_variations($loop, $variation_data, $variation)
{
    woocommerce_wp_text_input(array(
        'id' => 'local_stock[' . $loop . ']',
        'class' => 'stock-info short',
        'label' => __('Polski magazyn', 'woocommerce'),
        'value' => get_post_meta($variation->ID, 'local_stock', true),
        'wrapper_class' => 'form-row form-row-first'
    ));
    woocommerce_wp_text_input(array(
        'id' => 'remote_stock[' . $loop . ']',
        'class' => 'stock-info short',
        'label' => __('Holenderski magazyn', 'woocommerce'),
        'value' => get_post_meta($variation->ID, 'remote_stock', true),
        'wrapper_class' => 'form-row form-row-last'
    ));
}

// -----------------------------------------
// 2. Save custom field on product variation save
add_action('woocommerce_save_product_variation', 'klim_save_custom_field_variations', 10, 2);
function klim_save_custom_field_variations($variation_id, $i)
{
    $local_stock = $_POST['local_stock'][$i];
    $remote_stock = $_POST['remote_stock'][$i];
    if (isset($local_stock)) update_post_meta($variation_id, 'local_stock', sanitize_text_field($local_stock));
    if (isset($remote_stock)) update_post_meta($variation_id, 'remote_stock', sanitize_text_field($remote_stock));
}

// -----------------------------------------
// 3. Store custom field value into variation data
add_filter('woocommerce_available_variation', 'klim_add_custom_field_variation_data');
function klim_add_custom_field_variation_data($variations)
{

    if (!empty(get_post_meta($variations['variation_id'], 'local_stock', true))) {
        $variations['local_stock'] = '<p><small>Wysyłka w ciągu: <strong>1 dzień roboczy</strong></small></p>';
    } elseif (!empty(get_post_meta($variations['variation_id'], 'remote_stock', true))) {
        $variations['remote_stock'] = '<p><small>Wysyłka w ciągu: <strong>3 dni robocze</strong></small></p>';
    } else {
        $variations['remote_stock'] = '';
        $variations['local_stock'] = '';
    }

    return $variations;
}

add_action('template_redirect', 'klim_logout_confirmation');
function klim_logout_confirmation()
{
    global $wp;
    if (isset($wp->query_vars['customer-logout'])) {
        wp_redirect(str_replace('&amp;', '&', wp_logout_url(wc_get_page_permalink('myaccount'))));
        exit;
    }
}
// Add checkbox
function action_woocommerce_variation_options($loop, $variation_data, $variation)
{
    $is_checked = get_post_meta($variation->ID, '_mycheckbox', true);

    if ($is_checked == 'yes') {
        $is_checked = 'checked';
    } else {
        $is_checked = '';
    }

    ?>
    <p>
        <label class="tips" data-tip="<?php esc_attr_e('Zaznacz jeśli chcesz aby na zdjęciu produktu wyświetlała sie informacja o nowym kolorze', 'woocommerce'); ?>">
            <?php esc_html_e('Nowy kolor?', 'woocommerce'); ?>
            <input type="checkbox" class="checkbox variable_checkbox" name="_mycheckbox[<?php echo esc_attr($loop); ?>]" <?php echo $is_checked; ?> />
        </label>
    </p>
<?php
}
add_action('woocommerce_variation_options', 'action_woocommerce_variation_options', 10, 3);

// Save checkbox
function action_woocommerce_save_product_variation($variation_id, $i)
{
    if (!empty($_POST['_mycheckbox']) && !empty($_POST['_mycheckbox'][$i])) {
        update_post_meta($variation_id, '_mycheckbox', 'yes');
    } else {
        update_post_meta($variation_id, '_mycheckbox', 'no');
    }
}
add_action('woocommerce_save_product_variation', 'action_woocommerce_save_product_variation', 10, 2);

//Add custom checkbox to variation data
add_filter('woocommerce_available_variation', 'klim_add_custom_field_variation_checkbox');

function klim_add_custom_field_variation_checkbox($variations)
{
    $variations['_mycheckbox'] = get_post_meta($variations['variation_id'], '_mycheckbox', true);
    return $variations;
}

/**
 * Sorting out of stock WooCommerce products - Order product collections by stock status, in-stock products first.
 */
class iWC_Orderby_Stock_Status
{
    public function __construct()
    {
        // Check if WooCommerce is active
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            add_filter('posts_clauses', array($this, 'order_by_stock_status'), 2000);
        }
    }
    public function order_by_stock_status($posts_clauses)
    {
        global $wpdb;
        // only change query on WooCommerce loops
        if (is_woocommerce() && (is_shop() || is_product_category() || is_product_tag())) {
            $posts_clauses['join'] .= " INNER JOIN $wpdb->postmeta istockstatus ON ($wpdb->posts.ID = istockstatus.post_id) ";
            $posts_clauses['orderby'] = " istockstatus.meta_value ASC, " . $posts_clauses['orderby'];
            $posts_clauses['where'] = " AND istockstatus.meta_key = '_stock_status' AND istockstatus.meta_value <> '' " . $posts_clauses['where'];
        }
        return $posts_clauses;
    }
}
new iWC_Orderby_Stock_Status;
/**
 * END - Order product collections by stock status, instock products first.
 */


//Add new fields to users' profile

/**
 * Add sortable columns to the users admin table
 */
function klim_make_user_columns_sortable($columns)
{
    $columns['role'] = 'role';
    $columns['user_registered'] = 'user_registered';
    return $columns;
}
add_filter('manage_users_sortable_columns', 'klim_make_user_columns_sortable');

/**
 * Add registration date column to users admin table
 */
function klim_add_user_columns($columns)
{
    $columns['user_registered'] = 'Data rejestracji';
    return $columns;
}
add_filter('manage_users_columns', 'klim_add_user_columns');

/**
 * Display registration date in the custom column
 */
function klim_show_user_columns_content($value, $column_name, $user_id)
{
    if ('user_registered' === $column_name) {
        $user = get_userdata($user_id);
        return date_i18n(get_option('date_format'), strtotime($user->user_registered));
    }
    return $value;
}
add_filter('manage_users_custom_column', 'klim_show_user_columns_content', 10, 3);

/**
 * Handle custom sorting for user role and registration date
 */
function klim_sort_users_by_custom_columns($query)
{
    global $wpdb;

    if (!is_admin()) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || 'users' !== $screen->id) {
        return;
    }

    $orderby = $query->get('orderby');

    if ('role' === $orderby) {
        $query->set('meta_key', $wpdb->prefix . 'capabilities');
        $query->set('orderby', 'meta_value');
    }

    // Registration date sorting is natively supported, no need for custom handling
}
add_action('pre_get_users', 'klim_sort_users_by_custom_columns');
