FP.fns.track_tik_evt = ( event_name, payload = false ) => {
	ttq.track( event_name, payload );
	if ( fp.main.debug ) console.log('[FP] TikTok "' + event_name + '" event: ', payload );
};

FP.fns.tik_woo_events = () => {
	
	function get_item( prod, qty ){

		let category = prod.categories && prod.categories.length > 0 ? prod.categories[0] : 'All products';
		
		return { 
			'content_id' : FP.fns.get_woo_prod_id( prod ),
			'content_name': FP.fns.get_woo_prod_name( prod ),
			'content_category' : category,
			'content_type' : 'product',
			'quantity' : parseInt(qty),
			'price' : prod.price,
		}
	}
	
	// TRACK IMPRESSIONS
	
	function track_woo_impress() {
		
		if ( ! fpdata.woo.lists.single ) return;
		if ( ! fp.woo.tik ) fp.woo.tik = { 'single' : [] };
		
		let items_a = [],
			value = 0,
			item_ids = fpdata.woo.lists.single.filter( id => ! fp.woo.tik.single.includes(id) ); // track only items that were not tracked before
		
		if ( item_ids.length == 0 ) return;

		item_ids.forEach( id => {
			let prod = fpdata.woo.products[id];
			items_a.push( get_item( prod, 1 ) );
			value += prod.price;
		});

		// prevent double tracking in case the next prods are added dynamically
		fp.woo.tik.single.push(...item_ids);

		let payload_o = { 
			'contents' : items_a, 
			'currency' : fpdata.woo.currency, 
			'value' : value,
			'content_type' : 'product',
		};

		FP.fns.track_tik_evt( 'ViewContent', payload_o );
	};

	if ( ! ( fp.woo.dont_track_views_after_refresh && fpdata.refreshed ) ){
		track_woo_impress();
		FP.addAction( ['woo_impress'], track_woo_impress );
	}

	// TRACK DEFAULT VARIANT VIEW
	// TRACK VARIANT VIEWS

	function woo_variant_view( variant_id ){

		let prod = fpdata.woo.products[variant_id],
			item = get_item(prod, 1),
			payload_o = {
				'contents': [item],
				'currency': fpdata.woo.currency,
				'value': prod.price,
				'content_type': 'product'
			};

		FP.fns.track_tik_evt('ViewContent', payload_o);		
	}

	FP.addAction( ['woo_variant_view'], woo_variant_view );
	FP.addAction( ['woo_def_variant_view'], woo_variant_view );

	// TRACK ADD TO CART
	// TRACK ADD TO WISHLIST

	function track_items( data, event_name ){
		
		let products_data = data.products,
			items_a = [];

		if ( products_data.length == 0 ) return;
		
		products_data.forEach( prod_a => {
			let prod = prod_a[0],
				qty = prod_a[1] || prod.qty || 1;

			items_a.push( get_item( prod, qty ) );
		} );

		if ( items_a.length == 0 ) return false;

		let payload_o = {
			'contents' : items_a, 
			'value' : data.value, 
			'currency' : fpdata.woo.currency,
			'content_type' : 'product',
		};

		FP.fns.track_tik_evt( event_name, payload_o );
	};

	FP.addAction( ['woo_add_to_cart'], data =>{
		track_items( data, 'AddToCart' );
	} );

	FP.addAction( ['woo_add_to_wishlist'], data => {
		track_items( data, 'AddToWishlist');
	} );

	// TRACK CHECKOUT
	// TRACK ORDER

	function track_cart( type ){ // type can be either "checkout" or "order"

		let items_type = fp.woo.variable_tracking_method == 'track_parents' ? 'joined_items' : 'items',
			items_a = [],
			cart = type == 'checkout' ? fpdata.woo.cart : fpdata.woo.order,
			event_name = type == 'checkout' ? 'InitiateCheckout' : 'PlaceAnOrder';

		for ( const id in cart[items_type] ) {
			let prod = cart[items_type][id];
			items_a.push( get_item( prod, prod.qty ) );
		}

		if ( items_a.length == 0 ) return false;

		let payload_o = {
			'contents' : items_a, 
			'value' : cart.value, 
			'currency' : fpdata.woo.currency,
			'content_type' : 'product',
		};
		
		FP.fns.track_tik_evt( event_name, payload_o );
	}

	// track order
	if ( fp.woo.order_data_ready ) track_cart('order');

	// track the start of checkout (except when the whole page or its part is refreshed)
	if ( ! fpdata.refreshed ) {
		if ( fp.woo.checkout_data_ready ) {
			fp.tik.woo_checkout_tracked = true;
			track_cart('checkout')
		} else {
			document.addEventListener( 'fupi_woo_checkout_data_ready', ()=>{
				if ( ! fp.tik.woo_checkout_tracked ) {
					fp.tik.woo_checkout_tracked = true;
					track_cart('checkout');
				}
			})
		};
	}
}

FP.fns.tik_standard_events = function() {

	// CLICKS ON EMAIL & TEL LINKS

	if ( fp.tik.track_email_tel ) {
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && ( fpdata.clicked.link.is_email || fpdata.clicked.link.is_tel ) ) {
				let contact =  fpdata.clicked.link.safe_email || fpdata.clicked.link.safe_tel;
				FP.fns.track_tik_evt('Contact', { 'description' : contact } );
			}
		});
	}

	// FILE DOWNLOADS

	if ( fp.tik.track_file_downl ) {
		FP.addAction( ['click'], function(){
			var filename = FP.getTrackedFilename( fp.tik.track_file_downl );
			if ( filename ) {
				FP.fns.track_tik_evt('Download', { 'description' : filename } );		
			}
		})
	}

	// FORM SUBMITS

	if ( fp.tik.track_forms ) {
		FP.addAction( ['form_submit'], function(){
			var name = FP.getSubmittedForm( fp.tik.track_forms );
			if ( name ){
				if( name[0] == '!' ) {
					FP.fns.track_tik_evt( 'custom_' + name.replace('!', '') );
				} else {
					FP.fns.track_tik_evt('SubmitForm', { 'description' : name } );
				};
			}
		})
	}

	// CLICKS ON SPECIFIC ELEMENTS

	if ( fp.tik.track_elems ) {
		FP.addAction( ['click'], function(){
			var name  = FP.getClickTarget( fp.tik.track_elems );
			if ( name ) {
				if( name[0] == '!' ) {
					FP.fns.track_tik_evt( 'custom_' + name.replace('!', '') );
				} else {
					FP.fns.track_tik_evt('ClickButton', { 'description' : name } )
				};
			}
		})
	}
}

FP.fns.load_tik_footer = () => {
	FP.fns.tik_standard_events();
	if ( fp.loaded.includes('woo') ) FP.fns.tik_woo_events();
}

// INIT FOOTER SCRIPTS
FP.enqueueFn( 'FP.fns.load_tik_footer' );
