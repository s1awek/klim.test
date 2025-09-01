;(()=>{

	if ( ! fp.woo ) return;

	fp.loaded.push('woo');

	// Helpers

	FP.fns.get_woo_prod_id = prod => { // This must be equivalent to "get_woo_prod_id" in class-fupi-public-woo.php
		if ( prod.type == 'variation' && fp.woo.variable_tracking_method == 'track_parents' ) {
			return fp.woo.sku_is_id ? prod.parent_sku || prod.parent_id : prod.parent_id;
		} else {
			return fp.woo.sku_is_id ? prod.sku || prod.id : prod.id;
		};
	}

	FP.fns.get_woo_prod_name = prod => { // This must be equivalent to "get_woo_prod_name" in class-fupi-public-woo.php
		return prod.type == 'variation' && fp.woo.variable_tracking_method == 'track_parents' ? prod.parent_name || prod.name : prod.name;
	}

	function get_current_variant_id(){
		
		// Get fields with the main product ID and the currently selected variation ID
		let product_id_field = FP.findFirst( 'form.variations_form input[name="product_id"]' ),
			variant_id_field = FP.findFirst( 'form.variations_form input[name="variation_id"]' );

		if ( ! ( product_id_field && variant_id_field ) ) return false;

		let product_id = product_id_field.value,
			variant_id = variant_id_field.value;

		// Check if main prod and variant are the same
		if ( product_id == variant_id ) {

			// Mark this variant as viewed and return
			fpdata.woo.viewed_variants.push(variant_id);			
			return false;
		};

		// Check if this variant has already been tracked
		if ( fpdata.woo.viewed_variants && fpdata.woo.viewed_variants.includes( variant_id ) ) return false;

		// Mark this variant as viewed
		fpdata.woo.viewed_variants.push(variant_id);

		return variant_id;
	}

	

	// PREPARE GROUPED PRODUCTS

	function prepare_grouped_prods_for_addtocart(){
	
		FP.findAll( 'form.grouped_form' ).forEach( form_el => {
	
			let item_wrap = FP.findAll('.woocommerce-grouped-product-list-item', form_el),
				dataset_info = [],
				added_value = false;
	
			if ( item_wrap.length > 0 ){
	
				// save info on products with qty > 0 in a data property in the form element
				item_wrap.forEach( item => {
					
					let qty = FP.findFirst( 'input.qty', item ),
						id = item.id.replace('product-', '');
	
					if ( id && qty && qty.value && qty.value > 0 ) {
						dataset_info.push( [ id, qty.value ] );
						added_value = true;
					}
				});
			};
			
			form_el.dataset.fupi_products = added_value ? JSON.stringify(dataset_info) : '';
		} )
	};

	setTimeout( prepare_grouped_prods_for_addtocart, 500 );
	document.addEventListener( 'change', prepare_grouped_prods_for_addtocart );
	document.addEventListener( 'click', () => { setTimeout( prepare_grouped_prods_for_addtocart, 100 ); } ); // this makes sure the script will not slow down tracking

	// PREPARE SINGLE PRODUCTS AND TEASERS
	// TRACK IMPRESSIONS OF SINGLE PRODS AND TEASERS

	function get_teaser_list_name( teaser_wrap, script_el ){

		let list = teaser_wrap.closest("ul") || teaser_wrap.parentElement,
			list_name = script_el.dataset.list_name || list && list.dataset.fupi_list_name;

		if ( list && ! list_name ) {
				
			if ( list.parentElement.parentElement.classList.contains('wp-block-woocommerce-related-products') ) {
				list_name = 'woo related block';
			} else if ( list.parentElement.classList.contains('wp-block-handpicked-products') ) {
				list_name = 'woo handpicked block';
			} else if ( list.parentElement.classList.contains('wp-block-product-best-sellers') ) {
				list_name = 'woo bestsellers block';
			} else if ( list.parentElement.classList.contains('wp-block-product-new') ) {
				list_name = 'woo new products block';
			} else if ( list.parentElement.classList.contains('wp-block-product-on-sale') ) {
				list_name = 'woo on sale block';
			} else if ( list.parentElement.classList.contains('wp-block-product-top-rated') ) {
				list_name = 'woo top rated block';
			} else if ( list.parentElement.classList.contains('wc-block-products-by-attribute') ) {
				list_name = 'woo products by attribute block';
			} else {
				list_name = 'woo products'; // default
			};
		};

		list.classList.add('fupi_products_list');
		
		// save list name in the list's data attrib
		if ( list && list_name ) list.dataset.fupi_list_name = list_name;

		return list_name;
	}

	// Prepare teasers and single products for tracking and init tracking
	FP.fns.prepare_teaser_and_single = function(){
		
		// Adds classes to products and product teasers and gets their list names
		prepare_products_with_data_HTML();
		prepare_allprods_block_teasers();
		
		// STOP if page was refreshed
		if ( fp.woo.dont_track_views_after_refresh && fpdata.refreshed ) return;
		
		// Track impressions
		FP.doActions( "woo_impress" );

		// Track views of default variant
		if ( fp.woo.variable_tracking_method == 'track_def_var' ) { // 85
			
			let variant_id_field = FP.findFirst( 'form.variations_form input[name="variation_id"]' );

			if ( ! variant_id_field ) return;

			// keep checking the value of variant_id_field field until it is no longer '0' (string) or until 2 seconds pass
			let timer = 0;

			let	variant_check_interval = setInterval( () => {
					
				timer += 200;

				if ( variant_id_field.value != '0' ) {
					clearInterval( variant_check_interval );

					let variant_id = get_current_variant_id();
		
					if ( variant_id && fpdata.woo.products[variant_id] && FP.hasActions( 'woo_def_variant_view' ) ) {
						FP.doActions( "woo_def_variant_view", variant_id );
					}
				}

				if ( timer > 2000 ) {
					clearInterval( variant_check_interval );
					return;
				}
			}, 200 );			
		}
	};

	function prepare_products_with_data_HTML(){
		
		let prod_data_els = FP.findAll(".fupi_prod_data:not(.fupi_ready)");

		prod_data_els.forEach( script_el => {

			let list_name = 'single',
				id = script_el.dataset.id,
				type = script_el.dataset.type,
				prod_wrap = false;
	
			script_el.classList.add("fupi_ready");
	
			// mark teasers
			if ( type == 'teaser' ){
				
				let wrapper_selector = 'li';
				if ( fp.woo.teaser_wrapper_sel ) {
					wrapper_selector += ', ' + fp.woo.teaser_wrapper_sel;
				}

				prod_wrap = script_el.closest( wrapper_selector ) || script_el.parentElement;
				list_name = get_teaser_list_name( prod_wrap, script_el );
				prod_wrap.classList.add("fupi_woo_teaser", "fupi_woo_product");
				
				let add_to_cart_btns = FP.findAll('.add_to_cart_button, .ajax_add_to_cart', prod_wrap );
				
				add_to_cart_btns.forEach( btn => {
					let href = btn.getAttribute('href');
					// check if the product can be purchased from the list (the button redirects to product page when belongs to virtual products)
					if ( href && href.includes('add-to-cart=') ) btn.classList.add('fupi_add_to_cart_button');
					// if ( ( href && href.includes('add-to-cart=') ) || btn.classList.contains('wp-block-button__link') ) btn.classList.add('fupi_add_to_cart_button') // ! 9.1
				} );
				
			} else {
				
				let prod_form_el = script_el.closest("form.cart");

				if ( prod_form_el ) {
					prod_wrap = prod_form_el.parentElement.classList.contains("summary") ? prod_form_el.parentElement : prod_form_el.parentElement.parentElement;
					prod_wrap.classList.add('fupi_woo_single_product', 'fupi_woo_product');
				};
			};

			if ( prod_wrap ) {

				prod_wrap.dataset.fupi_woo_prod_id = script_el.dataset.id;
		
				// save product data in the fpdata.woo.lists
				fpdata.woo.lists[list_name] = fpdata.woo.lists[list_name] || [];
				if ( ! fpdata.woo.lists[list_name].includes(id) ) fpdata.woo.lists[list_name].push( id );
			}
	
		});
	}

	// PREPARE "ALL PRODUCTS" BLOCK TEASERS

	function prepare_allprods_block_teasers(){

		if ( ! fp.woo.products_from_all_products_block || fp.woo.products_from_all_products_block.length == 0 ) return;
		
		fp.woo.products_from_all_products_block.forEach( prod => {

			// get cats
			let price = parseInt( prod.prices.price ) / 100,
				categories = [];

			prod.categories.forEach( cat => categories.push( cat.name ) );

			// get all data
			let data = {
				'categories' : categories,
				'id' : prod.id,
				'name' : prod.name,
				'sku' : prod.sku,
				'price' : price,
				'type' : prod.type,
				// 'parent_id', parent_sku' and 'brand' are not available in the data obj
			}
			
			// update prod in the fpdata obj
			fpdata.woo.products[prod.id] = fpdata.woo.products[prod.id] ? { ...fpdata.woo.products[prod.id], ...data } : data;

			// mark prod in HTML

			let prod_link = FP.findFirst('div.wp-block-woocommerce-all-products li.wc-block-grid__product:not(.fupi_ready) a[href="' + prod.permalink + '"]');

			if ( prod_link ) {
				
				let prod_wrap = prod_link.closest("li.wc-block-grid__product"),
					list = prod_link.closest('ul');

				if ( ! prod_wrap ) return;
				
				list_name = 'woo all products block';
				list.dataset.fupi_list_name = list_name;

				prod_wrap.classList.add("fupi_woo_teaser", "fupi_woo_product");
				prod_wrap.dataset.fupi_woo_prod_id = prod.id;

				let add_to_cart_btns = FP.findAll('.add_to_cart_button, .ajax_add_to_cart', prod_wrap );

				add_to_cart_btns.forEach( btn => btn.classList.add('fupi_add_to_cart_button') );

				// save product data in the fpdata.woo.lists
				fpdata.woo.lists[list_name] = fpdata.woo.lists[list_name] || [];
				if ( ! fpdata.woo.lists[list_name].includes(prod.id) ) fpdata.woo.lists[list_name].push( prod.id );
			};
		});
	}

	// TRACKING HELPERS

	function get_teaser_data(){

		let teaser_el = fpdata.clicked.element.closest('.fupi_woo_teaser'),
			prod_id = teaser_el.dataset.fupi_woo_prod_id;
		
		if ( prod_id && fpdata.woo.products[prod_id] ) {

			let prod = { ...fpdata.woo.products[prod_id] },
				teasers_wrapper = teaser_el.closest('.fupi_products_list');

			if ( ! teasers_wrapper ) return false;

			let	list_name = teasers_wrapper.dataset.fupi_list_name || 'woo products',
				index = fpdata.woo.lists[list_name] && fpdata.woo.lists[list_name].includes(prod_id) ? fpdata.woo.lists[list_name].indexOf(prod_id) + 1 : 1;

			prod['list_name'] = list_name;
			prod['index'] = index;

			return prod;
		}

		return false;
	}

	// TRACK CLICKS IN TEASER

	FP.addAction( ['click'], () => {

		if ( ! FP.hasActions( 'woo_teaser_click' ) ) return;

		let selector = '.fupi_woo_teaser a:not(.add_to_cart_button)' + ( fp.woo.wishlist_btn_sel ? ':not(' + fp.woo.wishlist_btn_sel + ')' : '' );

		if ( FP.isClickTarget( selector ) ) {
			let teaser_data = get_teaser_data();
			if ( teaser_data ) FP.doActions( 'woo_teaser_click', { 'products' : [[teaser_data, 1]], 'value' : teaser_data.price } );
		}
	} );

	// TRACK ADD TO CART FROM A TEASER

	FP.addAction( ['click'], function(){
		
		if ( ! FP.hasActions( 'woo_add_to_cart' ) ) return;

		// prevent "add to cart" event from being fired twice - 1st time when the Add to cart button is clicked and the other one when cart is updated (tracked by a different script)
		// if ( document.body.classList.contains('woocommerce-cart') ) return;

		if ( FP.isClickTarget( '.fupi_add_to_cart_button, .fupi_add_to_cart_button *' ) ) {
			let teaser_data = get_teaser_data();
			if ( teaser_data ) FP.doActions( 'woo_add_to_cart', { 'products' : [[teaser_data, 1]], 'value' : teaser_data.price } );
		}
	} );

	// TRACK ADD TO CART FOR A SIMPLE, VARIABLE OR GROUPED PRODUCT

	FP.addAction( ['click'], function(){
		
		if ( ! FP.hasActions( 'woo_add_to_cart' ) ) return;

		if ( FP.isClickTarget( '.single_add_to_cart_button:not(.disabled), .single_add_to_cart_button:not(.disabled) *' ) ) {
			
			let form_el = fpdata.clicked.element.closest('form.cart');

			// track variable product
			if ( form_el.classList.contains('variations_form') ) {

				let prod_id = FP.findFirst( 'input.variation_id', form_el ).value;

				if ( fpdata.woo.products[prod_id] ) {

					let qty_el = FP.findFirst( 'input.qty', form_el ),
						qty = qty_el && qty_el.value && qty_el.value > 0 ? parseInt( qty_el.value ) : 1,
						prod = fpdata.woo.products[prod_id],
						value = Math.round( prod.price * qty * 100 ) / 100;

					FP.doActions( 'woo_add_to_cart', { 'products' : [[prod, qty]], 'value': value } );
				}

			// track grouped product
			} else if ( form_el.classList.contains('grouped_form') ) {
				
				let prods_data_arr = form_el.dataset.fupi_products ? JSON.parse( form_el.dataset.fupi_products ) : [],
					items_a = [],
					value = 0;

				if ( prods_data_arr.length > 0 ) {
					
					prods_data_arr.forEach( prod_a => {
						
						let prod_id = prod_a[0],
							qty = parseFloat( prod_a[1] );
	
						if ( fpdata.woo.products[prod_id] ) {
							
							let prod = fpdata.woo.products[prod_id];
							
							value += prod.price * qty;
							items_a.push( [prod, qty] );
						}
					} );
	
					value = Math.round( value * 100 ) / 100;
	
					if ( items_a.length > 0 ) FP.doActions( 'woo_add_to_cart', { 'products' : items_a, 'value' : value } );
				}
				

			// track simple product
			} else {

				let prod_id = FP.findFirst( '.single_add_to_cart_button', form_el ).value;

				if ( fpdata.woo.products[prod_id] ) {

					let qty_el = FP.findFirst( 'input.qty', form_el ),
						qty = qty_el && qty_el.value && qty_el.value > 0 ? parseInt( qty_el.value ) : 1,
						prod = fpdata.woo.products[prod_id],
						value = Math.round( prod.price * qty * 100 ) / 100;

					FP.doActions( 'woo_add_to_cart', { 'products' : [[prod, qty]], 'value': value } );
				};
			}
		};
	} );

	// TRACK REMOVE ITEMS FROM CLASSIC MINI-CART

	FP.addAction( ['click'], function(){

		if ( ! FP.hasActions( 'woo_remove_from_cart' ) ) return;

		if ( FP.isClickTarget( 'a.remove.remove_from_cart_button' ) ) {

			let product_wrap = fpdata.clicked.element.closest('li'),
				fupi_product_data_el = FP.findFirst('.fupi_cart_item_data', product_wrap),
				prod_id = fupi_product_data_el.dataset.product_id;
			
			if ( fpdata.woo.cart.items[prod_id] ) {

				let prod = { ...fpdata.woo.cart.items[prod_id] },
					value = Math.round( prod.price * prod.qty * 100 ) / 100;

				FP.doActions( 'woo_remove_from_cart', { 'products' : [[prod, false]], 'value' : value } );
			}
		}
	} );

	// TRACK ADD/REMOVE ITEMS IN THE CLASSIC CART

	// PREPARE CLASSIC CART FOR TRACKING QUANTITY CHANGES
	// makes sure that adding and removing products in cart is tracked

	function prepare_classic_cart( ajax_update ){

		let cart_data_el = FP.findFirst('span.fupi_cart_data:not(.fupi_ready)');

		if ( cart_data_el ) {

			let cart_data_dirty = cart_data_el.innerHTML,
				cart_data_clean = cart_data_dirty.replaceAll('“', '"').replaceAll('”', '"').replaceAll('″', '"').replaceAll('„', '"'), // for some reason WP reformats JSON and we need to fix quotes
				cart_data = JSON.parse(cart_data_clean);

			if ( ajax_update ) fpdata.woo.cart_old = {...fpdata.woo.cart};
			
			fpdata.woo.cart = cart_data;
		}
	};

	setTimeout( prepare_classic_cart, 300 ); // puts <span>cart_data</span> into fpdata.woo.cart. Must run on page load. We add timeout to wait for the cart contents to load

	function compare_old_and_new_carts(){
		
		let old_cart_items = fpdata.woo.cart_old.items,
			old_cart_item_keys = Object.keys( old_cart_items ),
			new_cart_items = fpdata.woo.cart.items,
			new_cart_item_keys = Object.keys( new_cart_items ),
			removed = [],
			removed_val = 0;
			added = [],
			added_val = 0;

		old_cart_item_keys.forEach( old_item_key => {

			let old_prod = fpdata.woo.cart_old.items[old_item_key];

			if ( new_cart_item_keys.includes( old_item_key ) ) {
				
				let old_qty = old_cart_items[old_item_key].qty,
					new_qty = new_cart_items[old_item_key].qty;

				if ( old_qty > new_qty ) {
					let qty_change = old_qty - new_qty;
					removed.push( [old_prod, qty_change ] );
					removed_val += old_prod.price * qty_change;
				} else if ( old_qty < new_qty ) {
					let qty_change = new_qty - old_qty;
					added.push( [old_prod, qty_change ] );
					added_val += old_prod.price * qty_change;
				};

			} else {
				removed.push([old_prod, false]);
				removed_val += old_prod.price * old_prod.qty;
			};
		} );

		new_cart_item_keys.forEach( new_item_key => {
			if ( ! old_cart_item_keys.includes( new_item_key ) ) {
				let new_prod = fpdata.woo.cart.items[new_item_key];
				added.push( [new_prod, false ] );
				added_val += new_prod.price * new_prod.qty;
			};
		} );

		if ( added.length > 0 ) {
			added_val = Math.round( added_val * 100 ) / 100;
			FP.doActions( 'woo_add_to_cart', { 'products' : added, 'value' : added_val } );
		}

		if ( removed.length > 0 ) {
			removed_val = Math.round( removed_val * 100 ) / 100;
			FP.doActions( 'woo_remove_from_cart', { 'products' : removed, 'value' : removed_val } );
		}
	};

	// when a big cart is updated
	// for some reason the jquery event won't get attached when the script loads, but we need to wait a bit
	setTimeout( ()=>{
		jQuery('body').on('updated_cart_totals', ()=>{
			prepare_classic_cart(true);
			compare_old_and_new_carts();
		});
	}, 100 );

	// TRACK ADD TO WISHLIST

	if ( fp.woo.wishlist_btn_sel ){

		FP.addAction( ['click'], function(){

			if ( ! FP.hasActions( 'woo_add_to_wishlist' ) ) return;

			if ( FP.isClickTarget( fp.woo.wishlist_btn_sel + ', ' + fp.woo.wishlist_btn_sel + ' *' ) ) {

				let product_el = fpdata.clicked.element.closest('.fupi_woo_product');
				
				if ( product_el ) {
					let prod_id = product_el.dataset.fupi_woo_prod_id,
						prod = fpdata.woo.products[prod_id];

					if ( prod ) FP.doActions( 'woo_add_to_wishlist', { 'products' : [[prod, 1]], 'value' : prod.price } );
				}
			};
		} );
	};

	// TRACK VIEWED VARIANTS AS PRODUCT VIEWS

	if ( fp.woo.variable_tracking_method != 'track_parents' && fp.woo.track_variant_views ) {

		jQuery(document).ready(function($) {
			
			// Listen for variation change events on the variations form
			$( 'form.variations_form' ).on( 'woocommerce_variation_has_changed' , function() { // !! do NOT change into an arrow function
				
				let variant_id = get_current_variant_id();

				if ( variant_id && fpdata.woo.products[variant_id] && FP.hasActions( 'woo_variant_view' ) ) {
					FP.doActions( 'woo_variant_view', variant_id );
				}
			} )
		} ) 
	}

	// BLOCK CART & MINI CART

	function add_block_hooks(){
		if ( typeof wp !== 'undefined' && wp.hooks && wp.hooks.addAction ){

			// change quantity
			wp.hooks.addAction(
				"experimental__woocommerce_blocks-cart-set-item-quantity",
				"fupi-tracking",
				( {product} ) => {

					if ( ! product ) return; 

					setTimeout( ()=> {

						let prod = fpdata.woo.cart.items[product.id];

						if ( ! prod ) return; // this can happen when a product was added from cart's cross-sells

						let cart_product_el = fpdata.clicked.element.closest('tr'),
							qty_el = FP.findFirst('.wc-block-components-quantity-selector__input', cart_product_el),
							new_qty = parseInt( qty_el.value );

						if ( new_qty > product.quantity ) {
						
							let qty_change = new_qty - product.quantity,
								value = Math.round( prod.price * qty_change * 100 ) / 100;

							FP.doActions( 'woo_add_to_cart', { 'products' : [[prod, qty_change]], 'value': value } );
						
						} else {
						
							let qty_change = product.quantity - new_qty,
								value = Math.round( prod.price * qty_change * 100 ) / 100;

							FP.doActions('woo_remove_from_cart', { 'products' : [[prod, qty_change]], 'value' : value } );
						};

					}, 100 ); // we need to wait for a tiny sec.
					
				}
			);
				
			// remove item
			wp.hooks.addAction(
				"experimental__woocommerce_blocks-cart-remove-item",
				"fupi-tracking",
				( {product} ) => {

					if ( !product ) return;

					let prod = { ...fpdata.woo.cart.items[product.id] },
						value = Math.round( prod.price * prod.qty * 100 ) / 100;

					FP.doActions( 'woo_remove_from_cart', { 'products' : [[prod, false]], 'value' : value } );
				}
			);
			
			// "All products" block
			wp.hooks.addAction(
				"experimental__woocommerce_blocks-product-list-render",
				"fupi-tracking",
				( {products, listName } ) => {

					if ( products.length > 0 && listName == 'woocommerce/all-products' ) {

						fp.woo.products_from_all_products_block = products;

						if ( fp.vars.wooImpressTimeout ) clearTimeout( fp.vars.wooImpressTimeout );
						fp.vars.wooImpressTimeout = setTimeout( () => FP.runFn( "FP.fns.prepare_teaser_and_single" ), 200 );
					}
				}
			);

			wp.hooks.addAction(
				"wc-blocks_product_list_rendered",
				"fupi-tracking",
				() => {
					console.log('List name ', arguments);
				}
			)
		}
	}

	add_block_hooks();

	FP.enqueueFn( 'FP.fns.prepare_teaser_and_single' );

})();
