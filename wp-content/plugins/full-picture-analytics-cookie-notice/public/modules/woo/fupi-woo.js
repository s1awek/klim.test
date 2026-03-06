function fupi_woo(){

	if ( ! fp.woo ) return;

	fp.loaded.push('woo'); // checked in other functions

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

	function get_teaser_list_name( teaser_wrap, el ){

		let list = teaser_wrap.closest("ul") || teaser_wrap.parentElement,
			list_name = el.dataset.list_name || list && list.dataset.fupi_list_name;

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

	function process_new_html_prod_data(){
		
		let prod_data_els = FP.findAll(".fupi_prod_data:not(.fupi_ready)");

		if ( prod_data_els.length == 0 ) return false;

		prod_data_els.forEach( el => {

			let list_name = 'single',
				id = el.dataset.id,
				prod_data = JSON.parse ( el.dataset.data ),
				prod_type = el.dataset.type || prod_data.type,
				prod_wrap = false,
				closest_form_element = el.closest("form.cart");

			// add prod data to fprada.woo.products
			fpdata.woo.products[id] = fpdata.woo.products[id] ? { ...fpdata.woo.products[id], ...prod_data } : prod_data;
			
			// mark as processed
			el.classList.add("fupi_ready");

			// skip variants
			if ( prod_type == 'variant' ) return;

			// add classes to prod wrapper

			// if is product
			if ( closest_form_element ) {

				prod_wrap = closest_form_element.parentElement.classList.contains("summary") ? closest_form_element.parentElement : closest_form_element.parentElement.parentElement;
				prod_wrap.classList.add('fupi_woo_single_product', 'fupi_woo_product');

			// probably is teaser
			} else {

				let wrapper_selector = 'li';

				if ( fp.woo.teaser_wrapper_sel ) {
					wrapper_selector += ', ' + fp.woo.teaser_wrapper_sel;
				}

				prod_wrap = el.closest( wrapper_selector ) || el.parentElement;
				list_name = get_teaser_list_name( prod_wrap, el );
				prod_wrap.classList.add("fupi_woo_teaser", "fupi_woo_product");
				
				let btns_and_links = FP.findAll('a, button', prod_wrap );
				
				// add class to ATC buttons inside the wrapper
				btns_and_links.forEach( btn => {

					if ( btn.classList.contains('single_add_to_cart') ) return;
					if ( fp.woo.wishlist_btn_sel && btn.classList.contains( fp.woo.wishlist_btn_sel ) ) return;

					let href = btn.getAttribute('href');

					if ( btn.classList.contains('add_to_cart_button') && ( btn.tagName == 'BUTTON' || ( href && href.includes('add-to-cart=') ) ) ) {
						btn.classList.add('fupi_add_to_cart_button');
					}

				} );
			}

			// save product ID in the fpdata.woo.lists
			if ( prod_wrap ) {
				prod_wrap.dataset.fupi_woo_prod_id = id;
				fpdata.woo.lists[list_name] = fpdata.woo.lists[list_name] || [];
				if ( ! fpdata.woo.lists[list_name].includes(id) ) fpdata.woo.lists[list_name].push( id );
			}
		});

		return true;
	}

	function process_new_allprods_block_data(){

		if ( ! fp.woo.products_from_all_products_block || fp.woo.products_from_all_products_block.length == 0 ) return false;
		
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
				list.classList.add('fupi_products_list');

				prod_wrap.classList.add("fupi_woo_teaser", "fupi_woo_product");
				prod_wrap.dataset.fupi_woo_prod_id = prod.id;

				let add_to_cart_btns = FP.findAll('.add_to_cart_button, .ajax_add_to_cart', prod_wrap );

				add_to_cart_btns.forEach( btn => btn.classList.add('fupi_add_to_cart_button') );

				// save product data in the fpdata.woo.lists
				fpdata.woo.lists[list_name] = fpdata.woo.lists[list_name] || [];
				if ( ! fpdata.woo.lists[list_name].includes(prod.id) ) fpdata.woo.lists[list_name].push( prod.id );
			};
		});

		return true;
	}

	function prepare_prod_data(){
		
		let new_prods_added = process_new_html_prod_data();
		let new_allprods_blocks_added = process_new_allprods_block_data();
		
		if ( ! new_prods_added && ! new_allprods_blocks_added ) return;
		if ( fp.woo.dont_track_views_after_refresh && fpdata.refreshed ) return;
		
		FP.doActions( "woo_impress" );
	}

	function track_def_variant_view(){

		if ( fp.woo.variable_tracking_method != 'track_def_var' ) return;
		if ( fp.woo.dont_track_views_after_refresh && fpdata.refreshed ) return;
			
		let variant_id_field = FP.findFirst( 'form.variations_form input[name="variation_id"]' );

		if ( ! variant_id_field ) return;

		let timer = 1000;

		let	variant_check_interval = setInterval( () => {

			if ( variant_id_field.value != '0' ) {
				clearInterval( variant_check_interval );

				let variant_id = get_current_variant_id();
	
				if ( variant_id && fpdata.woo.products[variant_id] && FP.hasActions( 'woo_def_variant_view' ) ) {
					FP.doActions( "woo_def_variant_view", variant_id );
				}
			}

			if ( timer >= 4000 ) {
				clearInterval( variant_check_interval );
				return;
			}

			timer += 1000;

		}, 1000 );
	}

	// Prepare teasers and single products for tracking and init tracking
	if ( document.readyState === "complete" ) {
		prepare_prod_data();
		track_def_variant_view();
	} else {
		document.addEventListener('DOMContentLoaded', ()=>{
			prepare_prod_data();
			track_def_variant_view();
		} );
	}

	setInterval( ()=>{prepare_prod_data();}, 1000 );

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

		let selector = '.fupi_woo_teaser a:not(.fupi_add_to_cart_button)' + ( fp.woo.wishlist_btn_sel ? ':not(' + fp.woo.wishlist_btn_sel + ')' : '' );

		if ( FP.isClickTarget( selector ) ) {
			let teaser_data = get_teaser_data();
			if ( teaser_data ) FP.doActions( 'woo_teaser_click', { 'products' : [[teaser_data, 1]], 'value' : teaser_data.price } );
		}
	} );

	// TRACK ADD TO CART FROM A TEASER

	FP.addAction( ['click'], function(){
		
		// Skip if tracking should happen in cart instead
		if ( fp.woo.where_track_addtocart === 'in_cart' ) return;
		
		if ( ! FP.hasActions( 'woo_add_to_cart' ) || ( !! fp.woo.disable_woo_events && fp.woo.disable_woo_events.includes('add_to_cart_teaser') ) ) return;

		// prevent "add to cart" event from being fired twice - 1st time when the Add to cart button is clicked and the other one when cart is updated (tracked by a different script)
		// if ( document.body.classList.contains('woocommerce-cart') ) return;

		if ( FP.isClickTarget( '.fupi_add_to_cart_button, .fupi_add_to_cart_button *' ) ) {
			let teaser_data = get_teaser_data();
			if ( teaser_data ) FP.doActions( 'woo_add_to_cart', { 'products' : [[teaser_data, 1]], 'value' : teaser_data.price } );
		}
	} );

	// TRACK ADD TO CART FOR A SIMPLE, VARIABLE OR GROUPED PRODUCT

	FP.addAction( ['click'], function(){
		
		// Skip if tracking should happen in cart instead
		if ( fp.woo.where_track_addtocart === 'in_cart' ) return;
		
		if ( ! FP.hasActions( 'woo_add_to_cart' ) || ( !! fp.woo.disable_woo_events && fp.woo.disable_woo_events.includes('add_to_cart_full') ) ) return;

		if ( FP.isClickTarget( '.single_add_to_cart_button:not(.disabled), .single_add_to_cart_button:not(.disabled) *' ) ) {
			
			let form_el = fpdata.clicked.element.closest('form.cart');

			// if no form element
			if ( ! form_el ) {

				// this is a single button
				if ( fpdata.clicked.element?.href && fpdata.clicked.element.href.includes('add-to-cart=') ) {
					
					let prod_id = FP.getUrlParamByName( 'add-to-cart', fpdata.clicked.element.href );

					if ( fpdata.woo.products[prod_id] ) {
						
						let qty_from_url = FP.getUrlParamByName( 'quantity', fpdata.clicked.element.href ),
							qty = qty_from_url || 1,
							prod = fpdata.woo.products[prod_id],
							value = Math.round( prod.price * qty * 100 ) / 100;

						FP.doActions( 'woo_add_to_cart', { 'products' : [[prod, qty]], 'value': value } );
						FP.setCookie( 'fp_last_atc', prod_id, 0 );
					}
				}
			
			// track variable product
			} else if ( form_el.classList.contains('variations_form') ) {

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
			} else if ( form_el ) {

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

		// Skip if tracking should happen in cart instead
		if ( fp.woo.where_track_addtocart === 'in_cart' ) return;

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

		if ( cart_data_el && !! cart_data_el.innerHTML ) {

			let cart_data = JSON.parse( cart_data_el.innerHTML );

			if ( ajax_update ) fpdata.woo.cart_old = {...fpdata.woo.cart};
			
			fpdata.woo.cart = cart_data;
			cart_data_el.classList.add('fupi_ready'); // Mark as processed
		}
	};

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

	// puts <span>cart_data</span> into fpdata.woo.cart. Must run on page load. We add timeout to wait for the cart contents to load
	setTimeout( prepare_classic_cart, 300 );

	// Bind event handler immediately (no timeout)
	jQuery(document).ready(function($) {
		$('body').on('updated_cart_totals', function(){
			prepare_classic_cart(true);
			compare_old_and_new_carts();
			update_cart_cookie();
		});
	});

	function update_cart_cookie(){
		// update or create an "fp_woo_cart" cookie, create it based on the value of fpdata.woo.cart.items - it should contain an object of elements, where the keys are the IDs of products and the values are their quantities
		let cart_cookie_data = {};
		for ( let prod_id in fpdata.woo.cart.items ) {
			cart_cookie_data[prod_id] = fpdata.woo.cart.items[prod_id].qty;
		}
		FP.setCookie( 'fp_woo_cart', JSON.stringify(cart_cookie_data), 90 );
	}

	// This is used when add to cart events are to be sent only in cart
	// It creates a "fp.woo.cart_to_track" object with all products that should be added to cart > this is later processed by tracking tools when they load
	function do_addToCart_in_cart(){
		
		// check if there is an "fp_woo_cart" cookie with FP.readCookie() function
		let fp_woo_cart_cookie = FP.readCookie('fp_woo_cart');
		let added = [];
		let added_val = 0;
		
		// if there is a cookie
		if ( fp_woo_cart_cookie ) {

			let saved_cart = JSON.parse(fp_woo_cart_cookie);
			
			// compare ids and quantities of products currently in fpdata.woo.cart.items and those saved in a cookie
			// get an array of all product IDs and quantities of products which are now in cart but were not in the cookie
			for ( let prod_id in fpdata.woo.cart.items ) {
				
				let current_qty = fpdata.woo.cart.items[prod_id].qty;
				let saved_qty = saved_cart[prod_id] || 0;
				
				if ( current_qty > saved_qty ) {
					let qty_change = current_qty - saved_qty;
					let prod = fpdata.woo.cart.items[prod_id];
					added.push( [prod, qty_change] );
					added_val += prod.price * qty_change;
				}
			}
			
			// Track added products
			if ( added.length > 0 ) {
				added_val = Math.round( added_val * 100 ) / 100;
				fp.woo.cart_to_track = { 'products' : added, 'value' : added_val };
			}
		
		// if there is no cookie
		} else {
			
			// change object with cart items into an array
			for ( let prod_id in fpdata.woo.cart.items ) {
				let prod = fpdata.woo.cart.items[prod_id];
				added.push( [prod, prod.qty] );
			}
			
			fp.woo.cart_to_track = { 'products' : added, 'value' : fpdata.woo.cart.value };
		}
		
		update_cart_cookie();
	}

	if ( fpdata.page_type == "Woo Cart" && fpdata.woo.cart.items && fp.woo.where_track_addtocart === 'in_cart' ) do_addToCart_in_cart();

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

	function update_fpdata_cart_items( id, new_qty ){
		let prod = fpdata.woo.cart.items[id];
		if ( prod ) {
			if ( ! new_qty ) { // product removed
				// make sure we have a backup of this prod in "products"
				fpdata.woo.products[id] = structuredClone(prod);
				delete fpdata.woo.cart.items[id];
			} else if ( new_qty != prod.qty ) {
				prod.qty = new_qty;
			}
		}
	};

	// BLOCK CART & MINI CART

	function add_block_hooks(){

		// Stable replacement using wp.data to subscribe to cart changes
		if ( window.wp && window.wp.data && window.wp.data.select( 'wc/store/cart' ) ) {
			
			const { subscribe } = wp.data;
			const cartStore = wp.data.select( 'wc/store/cart' );
			
			// Initialize with current cart items
			// The selector is getCartData(), which returns an object containing 'items'
			let previousCartItems = cartStore.getCartData().items;

			subscribe( () => {
				const newCartItems = cartStore.getCartData().items;

				// If the array reference is the same, nothing changed
				if ( newCartItems === previousCartItems ) return;

				// Map items by key for easy comparison
				const prevMap = new Map( previousCartItems.map( i => [ i.key, i ] ) );
				const newMap = new Map( newCartItems.map( i => [ i.key, i ] ) );

				// 1. Check for REMOVED items
				for ( const [ key, prevItem ] of prevMap ) {
					if ( ! newMap.has( key ) ) {
						let prod = get_prod_from_store_item( prevItem );
						let value = Math.round( prod.price * prevItem.quantity * 100 ) / 100;
						// Trigger remove action
						FP.doActions( 'woo_remove_from_cart', { 'products' : [[prod, false]], 'value' : value } );
						if ( fpdata.page_type == 'Woo Cart' ) update_fpdata_cart_items(prod.id, false);
					}
				}

				// 2. Check for QUANTITY changes
				for ( const [ key, newItem ] of newMap ) {
					const prevItem = prevMap.get( key );
					
					if ( prevItem && newItem.quantity !== prevItem.quantity ) {
						
						let prod = get_prod_from_store_item( newItem );
						let qty_change = Math.abs( newItem.quantity - prevItem.quantity );
						let value = Math.round( prod.price * qty_change * 100 ) / 100;

						if ( newItem.quantity > prevItem.quantity ) {
							// Quantity Increased
							FP.doActions( 'woo_add_to_cart', { 'products' : [[prod, qty_change]], 'value': value } );
							if ( fpdata.page_type == 'Woo Cart' ) update_fpdata_cart_items(prod.id, newItem.quantity);
						} else {
							// Quantity Decreased
							FP.doActions( 'woo_remove_from_cart', { 'products' : [[prod, qty_change]], 'value' : value } );
							if ( fpdata.page_type == 'Woo Cart' ) update_fpdata_cart_items(prod.id, newItem.quantity);
						}
					}
				}

				update_cart_cookie();
				// Update previous items for next change
				previousCartItems = newCartItems;
			} );

			// "All products" block
			wp.hooks.addAction(
				"experimental__woocommerce_blocks-product-list-render",
				"fupi-tracking",
				( {products, listName } ) => {

					if ( products.length > 0 && listName == 'woocommerce/all-products' ) {
						fp.woo.products_from_all_products_block = products;
					}
				}
			);
		}

		// Helper function to normalize product data from the store item
		function get_prod_from_store_item( item ) {
			// Prefer data from fpdata if available (contains more info like categories)
			if ( fpdata.woo.cart.items && fpdata.woo.cart.items[item.id] ) {
				return { ...fpdata.woo.cart.items[item.id] };
			}
			
			// Fallback to data available in the store item
			return {
				id: item.id,
				name: item.name,
				sku: item.sku,
				price: parseInt( item.prices.price ) / 100, // Price is in minor units
				qty: item.quantity
			};
		}
	}

	add_block_hooks();
	
	FP.loaded('woo'); // fp.loaded.push('woo'); is at the top
};

FP.load('woo', 'fupi_woo', ['footer_helpers']);
