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

	FP.fns.createWooPurchaseMD5 = () => {
		
		// MD5 encoder
		!function(n){"use strict";function d(n,t){var r=(65535&n)+(65535&t);return(n>>16)+(t>>16)+(r>>16)<<16|65535&r}function f(n,t,r,e,o,u){return d((u=d(d(t,n),d(e,u)))<<o|u>>>32-o,r)}function l(n,t,r,e,o,u,c){return f(t&r|~t&e,n,t,o,u,c)}function g(n,t,r,e,o,u,c){return f(t&e|r&~e,n,t,o,u,c)}function v(n,t,r,e,o,u,c){return f(t^r^e,n,t,o,u,c)}function m(n,t,r,e,o,u,c){return f(r^(t|~e),n,t,o,u,c)}function c(n,t){var r,e,o,u;n[t>>5]|=128<<t%32,n[14+(t+64>>>9<<4)]=t;for(var c=1732584193,f=-271733879,i=-1732584194,a=271733878,h=0;h<n.length;h+=16)c=l(r=c,e=f,o=i,u=a,n[h],7,-680876936),a=l(a,c,f,i,n[h+1],12,-389564586),i=l(i,a,c,f,n[h+2],17,606105819),f=l(f,i,a,c,n[h+3],22,-1044525330),c=l(c,f,i,a,n[h+4],7,-176418897),a=l(a,c,f,i,n[h+5],12,1200080426),i=l(i,a,c,f,n[h+6],17,-1473231341),f=l(f,i,a,c,n[h+7],22,-45705983),c=l(c,f,i,a,n[h+8],7,1770035416),a=l(a,c,f,i,n[h+9],12,-1958414417),i=l(i,a,c,f,n[h+10],17,-42063),f=l(f,i,a,c,n[h+11],22,-1990404162),c=l(c,f,i,a,n[h+12],7,1804603682),a=l(a,c,f,i,n[h+13],12,-40341101),i=l(i,a,c,f,n[h+14],17,-1502002290),c=g(c,f=l(f,i,a,c,n[h+15],22,1236535329),i,a,n[h+1],5,-165796510),a=g(a,c,f,i,n[h+6],9,-1069501632),i=g(i,a,c,f,n[h+11],14,643717713),f=g(f,i,a,c,n[h],20,-373897302),c=g(c,f,i,a,n[h+5],5,-701558691),a=g(a,c,f,i,n[h+10],9,38016083),i=g(i,a,c,f,n[h+15],14,-660478335),f=g(f,i,a,c,n[h+4],20,-405537848),c=g(c,f,i,a,n[h+9],5,568446438),a=g(a,c,f,i,n[h+14],9,-1019803690),i=g(i,a,c,f,n[h+3],14,-187363961),f=g(f,i,a,c,n[h+8],20,1163531501),c=g(c,f,i,a,n[h+13],5,-1444681467),a=g(a,c,f,i,n[h+2],9,-51403784),i=g(i,a,c,f,n[h+7],14,1735328473),c=v(c,f=g(f,i,a,c,n[h+12],20,-1926607734),i,a,n[h+5],4,-378558),a=v(a,c,f,i,n[h+8],11,-2022574463),i=v(i,a,c,f,n[h+11],16,1839030562),f=v(f,i,a,c,n[h+14],23,-35309556),c=v(c,f,i,a,n[h+1],4,-1530992060),a=v(a,c,f,i,n[h+4],11,1272893353),i=v(i,a,c,f,n[h+7],16,-155497632),f=v(f,i,a,c,n[h+10],23,-1094730640),c=v(c,f,i,a,n[h+13],4,681279174),a=v(a,c,f,i,n[h],11,-358537222),i=v(i,a,c,f,n[h+3],16,-722521979),f=v(f,i,a,c,n[h+6],23,76029189),c=v(c,f,i,a,n[h+9],4,-640364487),a=v(a,c,f,i,n[h+12],11,-421815835),i=v(i,a,c,f,n[h+15],16,530742520),c=m(c,f=v(f,i,a,c,n[h+2],23,-995338651),i,a,n[h],6,-198630844),a=m(a,c,f,i,n[h+7],10,1126891415),i=m(i,a,c,f,n[h+14],15,-1416354905),f=m(f,i,a,c,n[h+5],21,-57434055),c=m(c,f,i,a,n[h+12],6,1700485571),a=m(a,c,f,i,n[h+3],10,-1894986606),i=m(i,a,c,f,n[h+10],15,-1051523),f=m(f,i,a,c,n[h+1],21,-2054922799),c=m(c,f,i,a,n[h+8],6,1873313359),a=m(a,c,f,i,n[h+15],10,-30611744),i=m(i,a,c,f,n[h+6],15,-1560198380),f=m(f,i,a,c,n[h+13],21,1309151649),c=m(c,f,i,a,n[h+4],6,-145523070),a=m(a,c,f,i,n[h+11],10,-1120210379),i=m(i,a,c,f,n[h+2],15,718787259),f=m(f,i,a,c,n[h+9],21,-343485551),c=d(c,r),f=d(f,e),i=d(i,o),a=d(a,u);return[c,f,i,a]}function i(n){for(var t="",r=32*n.length,e=0;e<r;e+=8)t+=String.fromCharCode(n[e>>5]>>>e%32&255);return t}function a(n){var t=[];for(t[(n.length>>2)-1]=void 0,e=0;e<t.length;e+=1)t[e]=0;for(var r=8*n.length,e=0;e<r;e+=8)t[e>>5]|=(255&n.charCodeAt(e/8))<<e%32;return t}function e(n){for(var t,r="0123456789abcdef",e="",o=0;o<n.length;o+=1)t=n.charCodeAt(o),e+=r.charAt(t>>>4&15)+r.charAt(15&t);return e}function r(n){return unescape(encodeURIComponent(n))}function o(n){return i(c(a(n=r(n)),8*n.length))}function u(n,t){return function(n,t){var r,e=a(n),o=[],u=[];for(o[15]=u[15]=void 0,16<e.length&&(e=c(e,8*n.length)),r=0;r<16;r+=1)o[r]=909522486^e[r],u[r]=1549556828^e[r];return t=c(o.concat(a(t)),512+8*t.length),i(c(u.concat(t),640))}(r(n),r(t))}function t(n,t,r){return t?r?u(t,n):e(u(t,n)):r?o(n):e(o(n))}"function"==typeof define&&define.amd?define(function(){return t}):"object"==typeof module&&module.exports?module.exports=t:n.md5=t}(this);
		//# sourceMappingURL=md5.min.js.map

		// Encode email as MD5
		let email_el = FP.findID('email') || FP.findID('billing_email');
		
		if ( email_el && !! email_el.value ) {
			let hash = md5( email_el.value );
			FP.setCookie( 'fupi_order_md5', hash, 7 );
			return hash;
		}

		return false;

	};

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
					if ( href && href.includes('add-to-cart=') ) btn.classList.add('fupi_add_to_cart_button') 
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
		if ( wp && wp.hooks && wp.hooks.addAction ){

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
