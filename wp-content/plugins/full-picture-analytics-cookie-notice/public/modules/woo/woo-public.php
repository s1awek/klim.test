<?php

class Fupi_WOO_public {
    private $settings;

    private $sku_is_id = false;

    private $incl_tax = false;

    private $variable_tracking_method = 'default';

    private $incl_shipping_in_total = false;

    private $is_woo_enabled = false;

    public function __construct() {
        $this->settings = get_option( 'fupi_woo' );
        $this->incl_shipping_in_total = !empty( $this->settings['incl_shipping_in_total'] );
        $this->sku_is_id = !empty( $this->settings['sku_is_id'] );
        $this->incl_tax = isset( $this->settings['incl_tax_in_price'] );
        $this->variable_tracking_method = ( !empty( $this->settings['variable_tracking_method'] ) ? esc_attr( $this->settings['variable_tracking_method'] ) : 'default' );
        $this->add_filters_and_actions();
    }

    private function add_filters_and_actions() {
        // GENERAL
        add_action( 'woocommerce_loaded', array($this, 'check_if_woocommerce_loaded') );
        // check if woo has loaded
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );
        add_filter(
            'fupi_modify_fp_object',
            array($this, 'add_data_to_fp_object'),
            10,
            1
        );
        add_filter(
            'fupi_modify_fpdata_object',
            array($this, 'add_data_to_fpdata_object'),
            10,
            1
        );
        // REGISTER BRAND
        // if ( isset ( $this->settings['add_brand_tax'] ) ) {
        // 	add_action( 'init', array( $this, 'register_woo_cpts' ) ); // ok
        // }
        //
        // CLASSIC WOO ONLY
        //
        // teasers in product archives - ok
        // teasers in "Related products" and "You may also like" sections on a single product page
        // >>>> EXCEPTION <<< teasers in "You may also like" section on a single product page when FSE is enabled ("related products on the same page use blocks, which have totally different HTML)
        add_action( 'woocommerce_before_shop_loop_item', array($this, 'fupi_woo_archive_teaser_data'), 50 );
        // teasers in widgets - ok
        add_action( 'woocommerce_widget_product_item_end', array($this, 'fupi_woo_widget_teaser_data'), 9999 );
        // mini cart - ok
        add_action(
            'woocommerce_after_mini_cart',
            array($this, 'fupi_classic_mini_cart_data'),
            10,
            3
        );
        // cart - ok
        add_action(
            'woocommerce_before_cart_contents',
            array($this, 'fupi_cart_data'),
            10,
            3
        );
        // cart & mini cart - ok
        add_filter(
            'woocommerce_cart_item_name',
            array($this, 'fupi_classic_cart_item_id'),
            10,
            3
        );
        //
        // BLOCKS ONLY
        //
        // teasers in woocommerce/handpicked-products
        // teasers in woocommerce/product-best-sellers
        // teasers in woocommerce/product-new << also used in the cart block
        // teasers in woocommerce/product-on-sale
        // teasers in woocommerce/product-top-rated
        add_filter(
            'woocommerce_blocks_product_grid_item_html',
            array($this, 'fupi_woo_block_teasers'),
            999999,
            3
        );
        // ok
        // teasers in Full Site Edit product archives - ok
        // teasers in Full Site Edit "related products" section on a single product page - ok
        // block woocommerce/mini-cart - ok
        // block woocommerce/cart (except the cross-sells !) - ok
        add_filter(
            'render_block',
            array($this, 'fupi_woo_block_render_block_mod'),
            50,
            2
        );
        //
        // CLASSIC & BLOCKS (Blocks in a classic)
        //
        // single product - ok
        add_action( 'woocommerce_after_add_to_cart_button', array($this, 'fupi_woo_prod_data'), 50 );
        // grouped products - ok
        add_filter(
            'woocommerce_grouped_product_list_column_label',
            array($this, 'fupi_woo_extra_group_prod_data'),
            50,
            2
        );
        // Any page - for adding products to cart with a URL parameter add-to-cart - ok
        add_action( 'wp_footer', array($this, 'fupi_woo_add_to_cart_from_url'), 999 );
        // add_action('woocommerce_add_to_cart', array( $this, 'fupi_woo_add_to_cart_action', 10, 6));
        // checkout - checkout page and order confirmation page - ok
        add_action( 'wp_head', array($this, 'fupi_woo_get_order_data'), 100 );
        // TO DO:
        // CROSS-SELL in cart. No hooks available right now (6.3.0)
        // FEATURED PRODUCT BLOCK. No hooks available right now (6.3.0)
    }

    public function check_if_woocommerce_loaded() {
        $this->is_woo_enabled = true;
    }

    // ENQUEUE SCRIPTS
    public function enqueue_scripts() {
        /* _ */
        wp_enqueue_script(
            'fupi-woo-js',
            FUPI_URL . 'public/modules/woo/fupi-woo.js',
            array('jquery', 'wp-hooks'),
            FUPI_VERSION,
            [
                'in_footer' => true,
                'strategy'  => 'async',
            ]
        );
    }

    // MODIFY FP & FPDATA OBJECTS
    public function add_data_to_fp_object( $fp ) {
        if ( !$this->is_woo_enabled ) {
            return $fp;
        }
        $fp['woo']['teaser_wrapper_sel'] = ( !empty( $this->settings['teaser_wrapper_sel'] ) ? esc_attr( $this->settings['teaser_wrapper_sel'] ) : false );
        if ( !empty( $this->settings['force_item_view_on_url'] ) ) {
            foreach ( $this->settings['force_item_view_on_url'] as $key => $section ) {
                $fp['woo']['force_item_view_on_url'][$key] = esc_attr( $section['url_part'] );
            }
        }
        if ( !empty( $this->settings['disable_woo_events'] ) ) {
            $fp['woo']['disable_woo_events'] = $this->settings['disable_woo_events'];
        }
        $fp['woo']['variable_tracking_method'] = $this->variable_tracking_method;
        $fp['woo']['track_variant_views'] = isset( $this->settings['track_variant_views'] );
        $fp['woo']['order_stats'] = isset( $this->settings['order_stats'] );
        $fp['woo']['where_track_addtocart'] = ( !empty( $this->settings['where_track_addtocart'] ) ? esc_attr( $this->settings['where_track_addtocart'] ) : 'default' );
        $fp['woo']['incl_tax_in_price'] = isset( $this->settings['incl_tax_in_price'] );
        $fp['woo']['incl_shipping_in_total'] = isset( $this->settings['incl_shipping_in_total'] );
        $fp['woo']['sku_is_id'] = isset( $this->settings['sku_is_id'] );
        $fp['woo']['dont_track_views_after_refresh'] = isset( $this->settings['refresh_no_track_views'] );
        if ( isset( $this->settings['wishlist_btn_sel'] ) ) {
            $fp['woo']['wishlist_btn_sel'] = esc_js( $this->settings['wishlist_btn_sel'] );
        }
        return $fp;
    }

    public function add_data_to_fpdata_object( $fpdata ) {
        if ( !$this->is_woo_enabled ) {
            return $fpdata;
        }
        $user_data_provided = false;
        $fpdata['woo'] = [
            'products'        => [],
            'lists'           => [],
            'cart'            => [],
            'order'           => [],
            'viewed_variants' => [],
        ];
        $fpdata['woo']['currency'] = get_woocommerce_currency();
        // product
        if ( is_product() ) {
            $fpdata['page_type'] = 'Woo Product';
            // product category
        } else {
            if ( is_product_category() ) {
                $fpdata['page_type'] = 'Woo Product Category';
                // product tag
            } else {
                if ( is_product_tag() ) {
                    $fpdata['page_type'] = 'Woo Product Tag';
                    // customer account
                } else {
                    if ( is_account_page() ) {
                        $fpdata['page_type'] = 'Woo Customer Account';
                        // main shop page and product search
                    } else {
                        if ( is_shop() ) {
                            if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'product' ) {
                                $fpdata['page_type'] = 'Woo Product Search';
                                $search_query = get_search_query();
                                if ( $search_query ) {
                                    $fpdata['search_query'] = $search_query;
                                }
                                global $wp_query;
                                $fpdata['search_results'] = $wp_query->found_posts;
                                $fpdata['page_title'] = 'Search results';
                            } else {
                                $fpdata['page_type'] = 'Woo Shop Page';
                            }
                            // cart page
                        } else {
                            if ( is_cart() ) {
                                $fpdata['page_type'] = 'Woo Cart';
                                // checkout page
                            } else {
                                if ( is_checkout() && !is_wc_endpoint_url( 'order-received' ) ) {
                                    $fpdata['page_type'] = 'Woo Checkout';
                                    // order confirmation page
                                } else {
                                    if ( is_wc_endpoint_url( 'order-received' ) ) {
                                        $fpdata['page_type'] = 'Woo Order Received';
                                        // customer data is available only if the order has not been viewed before
                                        if ( method_exists( $this, 'get_customer_data__premium_only' ) ) {
                                            global $wp;
                                            $order_id = ( isset( $wp->query_vars['order-received'] ) ? $wp->query_vars['order-received'] : false );
                                            if ( !empty( $order_id ) ) {
                                                $order = wc_get_order( $order_id );
                                                if ( !empty( $order ) ) {
                                                    $thank_you_viewed = $order->get_meta( 'fupi_thankyou_viewed' );
                                                    if ( !$thank_you_viewed ) {
                                                        $user_data_provided = true;
                                                        $customer_data = $this->get_customer_data__premium_only( $order );
                                                        if ( !empty( $fpdata['user'] ) && count( $customer_data ) > 0 ) {
                                                            $fpdata['user'] = array_merge( $fpdata['user'], $customer_data );
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        // user data (sent only when there is no customer data provided)
        if ( !$user_data_provided && is_user_logged_in() && method_exists( $this, 'get_user_data__premium_only' ) ) {
            $user_data_provided = true;
            $user_data = $this->get_user_data__premium_only();
            if ( !empty( $fpdata['user'] ) && count( $user_data ) > 0 ) {
                $fpdata['user'] = array_merge( $fpdata['user'], $user_data );
            }
        }
        return $fpdata;
    }

    private function get_brands( $postID ) {
        $brands_a = [];
        $brands = false;
        // if ( isset( $this->settings['add_brand_tax'] ) ) { // from WP FP
        // 	$brands = get_the_terms( $postID, 'fupi_woo_brand' );
        // } else
        // if ( isset( $this->settings['brand_tax'] ) ) { // Custom
        // 	$brands = get_the_terms( $postID, $this->settings['brand_tax'] );
        // } else {
        $brands = get_the_terms( $postID, 'product_brand' );
        // in WooCommerce core
        // }
        if ( $brands !== false && !is_wp_error( $brands ) && !empty( $brands ) ) {
            foreach ( $brands as $brand ) {
                $brands_a[] = $brand->name;
            }
        }
        return $brands_a;
    }

    private function get_prod_data(
        $product,
        $id,
        $parent_product = false,
        $parent_id = false
    ) {
        return array(
            'id'          => $id,
            'parent_id'   => ( !empty( $parent_id ) && $parent_id != 0 ? $parent_id : false ),
            'sku'         => $product->get_sku(),
            'parent_sku'  => ( !empty( $parent_product ) ? $parent_product->get_sku() : false ),
            'name'        => str_replace( ';', '', strip_tags( $product->get_title() ) ),
            'parent_name' => ( !empty( $parent_product ) ? str_replace( ';', '', strip_tags( $parent_product->get_title() ) ) : false ),
            'type'        => $product->get_type(),
            'price'       => ( $this->incl_tax ? round( wc_get_price_including_tax( $product ), 2 ) : round( wc_get_price_excluding_tax( $product ), 2 ) ),
            'categories'  => ( !empty( $parent_product ) ? $this->get_cats( $parent_product ) : $this->get_cats( $product ) ),
            'brand'       => ( !empty( $parent_id ) ? $this->get_brands( $parent_id ) : $this->get_brands( $id ) ),
        );
    }

    private function get_cats( $prod ) {
        $cat_ids_a = $prod->get_category_ids();
        $woo_cats = [];
        foreach ( $cat_ids_a as $id ) {
            $woo_term = get_term( $id );
            if ( !empty( $woo_term ) ) {
                $woo_cats[] = str_replace( ';', '', $woo_term->name );
            }
        }
        return $woo_cats;
    }

    private function get_cart_items_data( $cart_items, $are_order_items = false ) {
        $cart_data = array(
            'qty'          => 0,
            'subtotal_tax' => 0,
        );
        foreach ( $cart_items as $item ) {
            $product = ( $are_order_items ? $item->get_product() : $item['data'] );
            if ( empty( $product ) ) {
                continue;
            }
            $product_id = $product->get_id();
            $item_qty = (float) $item['quantity'];
            $item_price = ( $this->incl_tax ? round( wc_get_price_including_tax( $product ), 2 ) : round( wc_get_price_excluding_tax( $product ), 2 ) );
            $cart_data['qty'] += $item_qty;
            // we take the original data of the product
            $parent_id = $product->get_parent_id();
            $parent_product = ( !empty( $parent_id ) ? new WC_Product($parent_id) : false );
            $cart_data['items'][$product_id] = $this->get_prod_data(
                $product,
                $product_id,
                $parent_product,
                $parent_id
            );
            // parent data is necessary to get proper categories. Variants do not have them attached
            $cart_data['items'][$product_id]['qty'] = $item_qty;
            $cart_data['items'][$product_id]['parent_id'] = $parent_id;
            $cart_data['subtotal_tax'] += (wc_get_price_including_tax( $product ) - wc_get_price_excluding_tax( $product )) * $item_qty;
            // if we are dealing with a variant BUT we are tracking it as simple products
            // we need to take data of cart with merged variants too
            if ( $this->variable_tracking_method == 'track_parents' ) {
                // we join variable products
                if ( !empty( $parent_id ) ) {
                    // if we have product with this ID already in the cart
                    if ( !empty( $cart_data['joined_items'][$parent_id] ) ) {
                        // calc qty
                        $old_qty = $cart_data['joined_items'][$parent_id]['qty'];
                        $new_qty = $old_qty + $item_qty;
                        $cart_data['joined_items'][$parent_id]['qty'] = $new_qty;
                        // calc aver price per item
                        $old_aver_price = $cart_data['joined_items'][$parent_id]['price'];
                        $new_aver_price = ($old_aver_price * $old_qty + $item_price * $item_qty) / $new_qty;
                        $cart_data['joined_items'][$parent_id]['price'] = round( $new_aver_price, 2 );
                        // if this is a new product
                    } else {
                        // get all the data (most from the parent product)
                        $cart_data['joined_items'][$parent_id] = array(
                            'id'         => $parent_id,
                            'sku'        => $parent_product->get_sku(),
                            'name'       => str_replace( ';', '', strip_tags( $parent_product->get_title() ) ),
                            'type'       => $parent_product->get_type(),
                            'categories' => $this->get_cats( $parent_product ),
                            'brand'      => $this->get_brands( $parent_id ),
                            'price'      => $item_price,
                            'qty'        => $item_qty,
                        );
                    }
                    // and we just add all the simple ones
                } else {
                    $cart_data['joined_items'][$product_id] = $cart_data['items'][$product_id];
                    $cart_data['joined_items'][$product_id]['qty'] = $item_qty;
                }
            }
        }
        $cart_data['subtotal_tax'] = round( $cart_data['subtotal_tax'], 2 );
        return $cart_data;
    }

    // GET CART DATA (FOR CART & CHECKOUT)
    // Code reference: https://woocommerce.github.io/code-reference/classes/WC-Cart.html#method_get_shipping_total
    public function get_cart_data( $cart, $is_checkout = false ) {
        $cart_data = array(
            'fees'     => $cart->get_fees(),
            'subtotal' => ( $this->incl_tax ? round( (float) $cart->get_subtotal_tax() + (float) $cart->get_subtotal(), 2 ) : round( (float) $cart->get_subtotal(), 2 ) ),
            'discount' => ( $this->incl_tax ? round( (float) $cart->get_discount_total() + (float) $cart->get_cart_discount_tax_total(), 2 ) : round( (float) $cart->get_discount_total(), 2 ) ),
        );
        $cart_data['value'] = round( $cart_data['subtotal'] - $cart_data['discount'], 2 );
        if ( $is_checkout ) {
            $checkout_data = array(
                'coupons'         => $cart->get_applied_coupons(),
                'subtotal_no_tax' => round( (float) $cart->get_subtotal(), 2 ),
                'shipping_no_tax' => round( (float) $cart->get_shipping_total(), 2 ),
                'shipping_tax'    => round( (float) $cart->get_shipping_tax(), 2 ),
                'shipping'        => ( $this->incl_tax ? round( (float) $cart->get_shipping_tax() + (float) $cart->get_shipping_total(), 2 ) : round( (float) $cart->get_shipping_total(), 2 ) ),
                'discount_no_tax' => round( (float) $cart->get_discount_total(), 2 ),
                'discount_tax'    => round( (float) $cart->get_cart_discount_tax_total(), 2 ),
            );
            $cart_data = array_merge( $cart_data, $checkout_data );
            // add shipping to total if required
            if ( $this->incl_shipping_in_total ) {
                $cart_data['value'] += $checkout_data['shipping'];
            }
        }
        $cart_data['value'] = round( $cart_data['value'], 2 );
        $cart_items_data = $this->get_cart_items_data( $cart->get_cart() );
        $cart_data = array_merge( $cart_data, $cart_items_data );
        return $cart_data;
    }

    // GET AND OUTPUT ORDER DATA
    private function get_order_completed_data( $order_id = false, $order = false, $for_server_tracking = false ) {
        if ( empty( $order_id ) ) {
            global $wp;
            $order_id = ( isset( $wp->query_vars['order-received'] ) ? $wp->query_vars['order-received'] : false );
            if ( empty( $order_id ) ) {
                return;
            }
            $order = wc_get_order( $order_id );
        }
        if ( empty( $order ) ) {
            return;
        }
        $order_number = $order->get_order_number();
        // gives "0" if the user is not logged in or has no Woo's cookie confirming that it was them that made the order
        if ( empty( $order_number ) ) {
            return;
        }
        if ( $order->has_status( 'failed' ) ) {
            return;
        }
        if ( !$for_server_tracking ) {
            // Mark order as tracked by the browser
            $thank_you_viewed = $order->get_meta( 'fupi_thankyou_viewed' );
            if ( !(empty( $thank_you_viewed ) || isset( $_GET["trackit"] )) ) {
                return;
            }
            $order->update_meta_data( 'fupi_thankyou_viewed', '1' );
            $order->save();
        }
        // Get data
        $shipping_cost = ( $this->incl_tax ? (float) $order->get_total_shipping() + (float) $order->get_shipping_tax() : (float) $order->get_total_shipping() );
        $order_data = [
            'order_id'        => $order_id,
            'id'              => $order_number,
            'fees'            => $order->get_fees(),
            'coupons'         => $order->get_coupon_codes(),
            'currency'        => $order->get_currency(),
            'payment_method'  => $order->get_payment_method(),
            'discount_no_tax' => round( (float) $order->get_discount_total(), 2 ),
            'discount_tax'    => round( (float) $order->get_discount_tax(), 2 ),
            'discount'        => ( $this->incl_tax ? round( (float) $order->get_discount_total() + (float) $order->get_discount_tax(), 2 ) : round( (float) $order->get_discount_total(), 2 ) ),
            'shipping_no_tax' => round( (float) $order->get_total_shipping(), 2 ),
            'shipping_tax'    => round( (float) $order->get_shipping_tax(), 2 ),
            'shipping'        => round( $shipping_cost, 2 ),
            'subtotal_no_tax' => round( (float) $order->get_subtotal(), 2 ),
            'tax'             => round( (float) $order->get_total_tax(), 2 ),
            'tracked'         => isset( $thank_you_viewed ),
        ];
        // get items
        $cart_items = $order->get_items();
        $order_data = array_merge( $order_data, $this->get_cart_items_data( $cart_items, true ) );
        // calculate subtotal
        // ( it needs to go after the get_cart_items_data() above because it calculates the subtotal_tax value )
        $order_data['subtotal'] = ( $this->incl_tax ? round( $order_data['subtotal_no_tax'] + $order_data['subtotal_tax'], 2 ) : $order_data['subtotal_no_tax'] );
        // calculate total
        $order_data['value'] = $order_data['subtotal'] - $order_data['discount'];
        if ( $this->incl_shipping_in_total ) {
            $order_data['value'] += $order_data['shipping'];
        }
        $order_data['value'] = round( $order_data['value'], 2 );
        // OUTPUT OR RETURN DATA
        if ( $for_server_tracking ) {
            return $order_data;
        } else {
            // get user data and put it all together
            $json_order_data = json_encode( $order_data );
            $output = "fpdata['woo']['order']={$json_order_data};";
            // this is output in <head>
            echo '<!--noptimize--><script data-no-optimize="1" id="fupi_woo_purchase_data" class="fupi_no_defer" nowprocket>
			
			function fupi_woo_order_data_in_head(){
				// get session order cookie
				let order_cookie = FP.readCookie(\'fp_orders\') || "";
				
				if ( ! order_cookie || ! order_cookie.includes("' . $order_number . '") ) {
					order_cookie += "' . $order_number . ' ";
					FP.setCookie(\'fp_orders\', order_cookie ); // session cookie
					' . $output . ';
					fp.woo.order_data_ready = true;
				};

				FP.loaded("woo_order_data");
			};

			FP.load("woo_order_data", "fupi_woo_order_data_in_head", ["head_helpers"]);
			</script><!--/noptimize-->';
        }
    }

    public function fupi_woo_get_order_data() {
        if ( function_exists( 'is_wc_endpoint_url' ) && is_checkout() ) {
            if ( is_wc_endpoint_url( 'order-received' ) ) {
                $this->get_order_completed_data();
            } else {
                // CHECKOUT DATA
                $cart = WC()->cart;
                if ( !empty( $cart ) && !$cart->is_empty() ) {
                    $cart_data = json_encode( $this->get_cart_data( $cart ) );
                    // this is output in <head>
                    echo "<!--noptimize--><script data-no-optimize='1' id='fupi_woo_checkout_data' class='fupi_no_defer' nowprocket>\r\n\r\n\t\t\t\t\tfunction fupi_woo_checkout_data_in_head(){\r\n\t\t\t\t\t\tif ( fpdata.woo.cart.value ) fpdata.woo.cart_old = { ...fpdata.woo.cart };\r\n\t\t\t\t\t\tfpdata.woo.cart = {$cart_data};\r\n\t\t\t\t\t\tfp.woo.checkout_data_ready = true;\r\n\t\t\t\t\t\tFP.sendEvt( 'fupi_woo_checkout_data_ready' );\r\n\t\t\t\t\t\tFP.loaded(\"woo_checkout_data\");\r\n\t\t\t\t\t};\r\n\r\n\t\t\t\t\tFP.load(\"woo_checkout_data\", \"fupi_woo_checkout_data_in_head\", ['head_helpers', 'woo']);\r\n\t\t\t\t\t</script><!--/noptimize-->";
                }
            }
        }
    }

    // CLASSIC CART ( block cart is handled by fupi_woo_block_render_mod() )
    // (we can't use <script>, because the output is filtered and removed)
    public function fupi_cart_data() {
        // Action
        $cart = WC()->cart;
        if ( !empty( $cart ) && !$cart->is_empty() ) {
            $cart_data = esc_attr( wp_json_encode( $this->get_cart_data( $cart ) ) );
            echo "<span class='fupi_cart_data' style='display: none;' id='fupi_woo_cart_element'>{$cart_data}</span>";
        }
    }

    // CLASSIC MINI CART
    public function fupi_classic_mini_cart_data() {
        $cart = WC()->cart;
        if ( !empty( $cart ) && !$cart->is_empty() ) {
            $cart_data = json_encode( $this->get_cart_data( $cart ) );
            echo "<!--noptimize--><script data-no-optimize='1' id='fupi_mini_cart_data' class='fupi_no_defer' nowprocket>\r\n\t\t\t\tif ( fpdata.woo.cart.value ) fpdata.woo.cart_old = { ...fpdata.woo.cart };\r\n\t\t\t\tfpdata.woo.cart = {$cart_data};\r\n\t\t\t</script><!--/noptimize-->";
        }
    }

    // CLASSIC MINI-CART ITEM
    // ( also added to all cart tables, but we use it only for the mini cart )
    public function fupi_classic_cart_item_id( $item_name_html, $cart_item, $cart_item_key ) {
        $product_id = ( !empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'] );
        if ( !empty( $product_id ) ) {
            return $item_name_html . "<span class='fupi_cart_item_data' style='display: none !important' data-product_id='{$product_id}'></span>";
        }
        return $item_name_html;
    }

    // ARCHIVE TEASERS
    // action: woocommerce_before_shop_loop_item
    public function fupi_woo_archive_teaser_data() {
        global $product;
        if ( empty( $product ) ) {
            return;
        }
        $id = $product->get_id();
        $parent_id = $product->get_parent_id();
        $parent_product = ( !empty( $parent_id ) ? new WC_Product($parent_id) : false );
        $json_data = esc_attr( json_encode( $this->get_prod_data(
            $product,
            $id,
            $parent_product,
            $parent_id
        ) ) );
        // List position and name
        global $woocommerce_loop;
        $list_name = '';
        if ( !empty( $woocommerce_loop ) ) {
            // ATTENTION! Shortcode [products] also returns teasers that have loop name "products". Hence "woo product list" can list names of typical product teasers as well as products displayed by the said shortcode
            $list_name = ( empty( $woocommerce_loop['name'] ) ? ( empty( $woocommerce_loop['is_search'] ) ? 'woo products' : 'woo search' ) : 'woo ' . $woocommerce_loop['name'] );
        }
        echo "<i class='fupi_prod_data' style='display:none !important;' data-id='{$id}' data-list_name='{$list_name}' data-data='{$json_data}'></i>";
        // echo "<!--noptimize--><script data-no-optimize='1' nowprocket class='fupi_prod_data fupi_no_defer' data-id='{$id}' data-list_name='{$list_name}'>FP.prepareProduct( 'teaser', {$id}, {$json_data} );</script><!--/noptimize-->";
    }

    // WOO BLOCKS
    // filter: woocommerce_blocks_product_grid_item_html
    // teasers in woocommerce/handpicked-products
    // teasers in woocommerce/product-best-sellers
    // teasers in woocommerce/product-new << also used in the cart block
    // teasers in woocommerce/product-on-sale
    // teasers in woocommerce/product-top-rated
    // more info: https://docs.wpdebuglog.com/plugin/woocommerce/5.0.0/file/woocommerce--packages--woocommerce-blocks--src--BlockTypes--AbstractProductGrid.php/#
    // HTML for a product in a block is <li class=\"wc-block-grid__product\">...</li>
    public function fupi_woo_block_teasers( $html, $data, $product ) {
        // FILTER
        if ( is_admin() ) {
            return $html;
        }
        if ( substr( $html, -5 ) == '</li>' ) {
            $id = $product->get_id();
            $parent_id = $product->get_parent_id();
            $parent_product = ( !empty( $parent_id ) ? new WC_Product($parent_id) : false );
            $json_data = esc_attr( json_encode( $this->get_prod_data(
                $product,
                $id,
                $parent_product,
                $parent_id
            ) ) );
            $script = "<i class='fupi_prod_data fupi_woo_block_teasers' style='display:none !important;' data-id='{$id}' data-data='{$json_data}'></i>";
            // $script = "<!--noptimize--><script data-no-optimize='1' nowprocket class='fupi_prod_data fupi_no_defer fupi_woo_block_teasers' data-id='{$id}'>FP.prepareProduct( 'teaser', {$id}, {$json_data} );</script><!--/noptimize-->";
            return substr( $html, 0, -5 ) . $script . '</li>';
        }
        return $html;
    }

    // BLOCK CART/MINI-CART
    // single products !! 9.1.3
    // list blocks !! 9.1.3
    // some blocks
    // fse product archives
    // related products (on single)
    public function fupi_woo_block_render_block_mod( $block_content, $block_settings ) {
        if ( is_admin() || !$this->is_woo_enabled ) {
            return $block_content;
        }
        // FSE product archives and related products on single product page
        // !! 9.1.0 - can also be added to single products
        if ( $block_settings['blockName'] == 'woocommerce/product-button' ) {
            global $product;
            if ( !empty( $product ) ) {
                $id = $product->get_id();
                $parent_id = $product->get_parent_id();
                $parent_product = ( !empty( $parent_id ) ? new WC_Product($parent_id) : false );
                $prod_data = esc_attr( json_encode( $this->get_prod_data(
                    $product,
                    $id,
                    $parent_product,
                    $parent_id
                ) ) );
                return $block_content . "<i class='fupi_prod_data fupi_woo_fse_block_teaser' style='display:none !important;' data-id='{$id}' data-data='{$prod_data}'></i>";
                // return $block_content . "<script data-no-optimize='1' nowprocket class='fupi_prod_data fupi_no_defer fupi_woo_fse_block_teaser' data-id='{$id}'>FP.prepareProduct( 'teaser', {$id}, {$prod_data} );</script>"; // <!--noptimize--> comment removed in 7.5.1
            }
            // cart & mini cart blocks
            // data output on the cart page may double with data added by "fupi_classic_cart_data" fn above.
        } else {
            if ( $block_settings['blockName'] == 'woocommerce/mini-cart' || $block_settings['blockName'] == 'woocommerce/cart' ) {
                // get cart
                $cart = WC()->cart;
                // get products if not empty
                if ( !empty( $cart ) && !$cart->is_empty() ) {
                    $cart_data = json_encode( $this->get_cart_data( $cart ) );
                    return "<!--noptimize--><script data-no-optimize='1' id='fupi_woo_cart_block' class='fupi_no_defer' nowprocket>\r\n\t\t\t\t\tif ( fpdata.woo.cart.value ) fpdata.woo.cart_old = { ...fpdata.woo.cart };\r\n\t\t\t\t\tfpdata.woo.cart = {$cart_data};\r\n\t\t\t\t</script><!--/noptimize-->" . $block_content;
                }
            }
        }
        return $block_content;
    }

    // WIDGETS
    public function fupi_woo_widget_teaser_data( $args ) {
        $id = get_the_ID();
        $product = wc_get_product( $id );
        if ( empty( $product ) ) {
            return;
        }
        $parent_id = $product->get_parent_id();
        $parent_product = ( !empty( $parent_id ) ? new WC_Product($parent_id) : false );
        $json_data = esc_attr( json_encode( $this->get_prod_data(
            $product,
            $id,
            $parent_product,
            $parent_id
        ) ) );
        $list_name = ( isset( $args['widget_id'] ) ? $args['widget_id'] : '' );
        // $list_name = ! empty ( $args['widget_id'] ) ? $args['widget_id'] : 'woo custom widget'; // for some reason this is not working
        if ( str_contains( $list_name, 'recently_viewed_products' ) ) {
            $list_name = 'woo recently viewed widget';
        } else {
            if ( str_contains( $list_name, 'woocommerce_products-' ) ) {
                $list_name = 'woo products list widget';
            } else {
                if ( str_contains( $list_name, 'top_rated_products-' ) ) {
                    $list_name = 'woo top rated products widget';
                } else {
                    $list_name = 'woo custom widget';
                }
            }
        }
        echo "<i class='fupi_prod_data' style='display:none !important;' data-id='{$id}' data-list_name='{$list_name}' data-data='{$json_data}'></i>";
        // echo "<!--noptimize--><script data-no-optimize='1' nowprocket class='fupi_prod_data fupi_no_defer' data-id='{$id}' data-list_name='{$list_name}'>FP.prepareProduct( 'teaser', {$id}, {$json_data} );</script><!--/noptimize-->";
    }

    // SINGLE PRODUCTS - SIMPLE AND VARIABLE
    public function fupi_woo_prod_data() {
        global $product;
        if ( empty( $product ) ) {
            return;
        }
        // single or parent prod data
        $id = $product->get_id();
        $parent_id = $product->get_parent_id();
        $parent_product = ( !empty( $parent_id ) ? new WC_Product($parent_id) : false );
        $prod_data = esc_attr( json_encode( $this->get_prod_data(
            $product,
            $id,
            $parent_product,
            $parent_id
        ) ) );
        $output = "<i class='fupi_prod_data' style='display:none !important;' data-type='single' data-id='{$id}' data-data='{$prod_data}'></i>";
        // $output = "FP.prepareProduct( 'single', {$id}, {$prod_data} );";
        // variants
        $variation_ids = $product->get_children();
        foreach ( $variation_ids as $variation_id ) {
            $variant_prod = wc_get_product( $variation_id );
            if ( empty( $variant_prod ) ) {
                continue;
            }
            $variant_data = esc_attr( json_encode( $this->get_prod_data(
                $variant_prod,
                $variation_id,
                $product,
                $id
            ) ) );
            $output .= "<i class='fupi_prod_data' style='display:none !important;' data-type='variant' data-id='{$variation_id}' data-data='{$variant_data}'></i>";
            // $output .= "FP.prepareProduct( 'variant', {$variation_id}, {$variant_data} );";
        }
        echo $output;
        // echo "<!--noptimize--><script data-no-optimize='1' nowprocket class='fupi_prod_data fupi_no_defer' data-id='{$id}'>{$output}</script><!--/noptimize-->";
    }

    // SINGLE PRODUCTS - GROUPED
    // ! adds product data next to the name of each sub-product
    public function fupi_woo_extra_group_prod_data( $html, $product ) {
        // Filter
        if ( is_admin() ) {
            return $html;
        }
        // do not modify anything while editing
        $id = $product->get_id();
        $parent_id = $product->get_parent_id();
        $parent_product = ( !empty( $parent_id ) ? new WC_Product($parent_id) : false );
        $prod_data = esc_attr( json_encode( $this->get_prod_data(
            $product,
            $id,
            $parent_product,
            $parent_id
        ) ) );
        return "<i class='fupi_prod_data fupi_woo_group_item' style='display:none !important;' data-type='group_item' data-id='{$id}' data-data='{$prod_data}'></i>" . $html;
        // return "<!--noptimize--><script data-no-optimize='1' nowprocket class='fupi_woo_group_item fupi_no_defer'>FP.prepareProduct( 'group_item', {$id}, {$prod_data} );</script><!--/noptimize-->" . $html;
    }

    // public function fupi_woo_add_to_cart_action( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ){
    // }
    public function fupi_woo_add_to_cart_from_url() {
        if ( $this->is_woo_enabled && isset( $_GET['add-to-cart'] ) ) {
            $id = (int) $_GET['add-to-cart'];
            $product = wc_get_product( $id );
            if ( empty( $product ) ) {
                return;
            }
            // check if the product is published and can be purchased
            if ( !$product->is_purchasable() ) {
                return;
            }
            $prod_data = json_encode( $this->get_prod_data( $product, $id ) );
            $qty = ( isset( $_GET['quantity'] ) ? (int) $_GET['quantity'] : 1 );
            echo "<!--noptimize--><script data-no-optimize='1' nowprocket class='fupi_add_to_cart_prod_data fupi_no_defer'>\r\n\r\n\t\t\t\tfunction fupi_track_atc_from_url(){\r\n\t\t\t\t\r\n\t\t\t\t\tlet fupi_last_atc_cookie = FP.readCookie( 'fp_last_atc' );\r\n\r\n\t\t\t\t\tif ( ! fupi_last_atc_cookie || fupi_last_atc_cookie != {$id} ) {\r\n\r\n\t\t\t\t\t\tlet fupi_prod = {$prod_data},\r\n\t\t\t\t\t\t\tfupi_qty = {$qty},\r\n\t\t\t\t\t\t\tfupi_value = Math.round( fupi_prod.price * fupi_qty * 100 ) / 100;\r\n\t\t\r\n\t\t\t\t\t\t// Only track if default mode (not 'in_cart' mode)\r\n\t\t\t\t\t\tif ( fp.woo.where_track_addtocart === 'default' ) {\r\n\t\t\t\t\t\t\tFP.doActions(\r\n\t\t\t\t\t\t\t\t'woo_add_to_cart',\r\n\t\t\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\t\t\t'products' : [[fupi_prod, fupi_qty]],\r\n\t\t\t\t\t\t\t\t\t'value' : fupi_value\r\n\t\t\t\t\t\t\t\t}\r\n\t\t\t\t\t\t\t);\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t\t\t\r\n\t\t\t\t\t} else {\r\n\t\t\t\t\t\tFP.deleteCookie( 'fp_last_atc' );\r\n\t\t\t\t\t}\r\n\r\n\t\t\t\t\tFP.loaded('woo_atc_from_url');\r\n\t\t\t\t}\r\n\t\t\t\t\r\n\t\t\t\tFP.load('woo_atc_from_url','fupi_track_atc_from_url',['woo']);\r\n\t\t\t\t</script><!--/noptimize-->";
        }
    }

    // public function fupi_classic_checkout_custom_fields_admin_display__premium_only( $order ){
    // 	echo '<p><strong>' . esc_html__( 'WP Full Picture extra order data' ) . ':</strong> ' . esc_html( serialize( $order->get_meta( 'fupi_extra_order_data', true ) ) ) . '</p>';
    // }
}
