FP.fns.track_twit_evt = ( event_name, event_id, payload = false ) => {
	
	

	twq( 'event', event_id, payload);
	if ( fp.vars.debug ) console.log('[FP] X / Twitter "' + event_name + '" event (event id: ' + event_id + '): ', payload);
};

FP.fns.twit_woo_events = () => {

	// HELPERS

	function get_item( prod, qty ){

		let category = prod.categories && prod.categories.length > 0 ? prod.categories[0] : 'All products';
		
		return { 
			'content_id' : FP.fns.get_woo_prod_id( prod ),
			'content_name': FP.fns.get_woo_prod_name( prod ),
			'content_type' : category,
			'content_price' : prod.price,
			'num_items' : parseInt(qty),
		}
	}
	
	// TRACK IMPRESSIONS
	
	function track_woo_impress() {
		
		if ( ! fpdata.woo.lists.single ) return;
		if ( ! fp.woo.twit ) fp.woo.twit = { 'single' : [] };
		
		let items_a = [],
			value = 0,
			item_ids = fpdata.woo.lists.single.filter( id => ! fp.woo.twit.single.includes(id) ); // track only items that were not tracked before
		
		if ( item_ids.length == 0 ) return;

		item_ids.forEach( id => {
			let prod = fpdata.woo.products[id];
			items_a.push( get_item( prod, 1 ) );
			value += prod.price;
		});

		// prevent double tracking in case the next prods are added dynamically
		fp.woo.twit.single.push(...item_ids);

		let payload_o = { 
			'contents' : items_a, 
			'currency' : fpdata.woo.currency, 
			'value' : value,
		};

		FP.fns.track_twit_evt( 'product view', fp.twit.track_woo_prodview, payload_o );
	};

	if ( ! ( fp.woo.dont_track_views_after_refresh && fpdata.refreshed ) && fp.twit.track_woo_prodview ){
		track_woo_impress();
		FP.addAction( ['woo_impress'], track_woo_impress );
	}

	// TRACK DEFAULT VARIANT VIEW
	// TRACK VARIANT VIEWS

	function woo_variant_view( variant_id ){

		let prod = fpdata.woo.products[variant_id],
			item = get_item(prod, 1);

		let payload_o = {
			'contents': [item],
			'currency': fpdata.woo.currency,
			'value': prod.price
		};

		FP.fns.track_twit_evt('product view', fp.twit.track_woo_prodview, payload_o);
	}

	FP.addAction( ['woo_variant_view'], woo_variant_view );
	FP.addAction( ['woo_def_variant_view'], woo_variant_view );

	// TRACK ADD TO CART
	// TRACK ADD TO WISHLIST

	function track_items( data, event_name, event_id ){
		
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
			'currency' : fpdata.woo.currency
		};

		FP.fns.track_twit_evt( event_name, event_id, payload_o );
	};

	if ( fp.twit.track_woo_addtocart ) {
		FP.addAction( ['woo_add_to_cart'], data =>{
			track_items( data, 'add to cart', fp.twit.track_woo_addtocart );
		} );
	}

	if ( fp.twit.track_woo_addtowishlist ) {
		FP.addAction( ['woo_add_to_wishlist'], data => {
			track_items( data, 'add to wishlist', fp.twit.track_woo_addtowishlist );
		} );
	}

	// TRACK CHECKOUT
	// TRACK ORDER

	function track_cart( type, event_id ){ // type can be either "checkout" or "order"

		let items_type = fp.woo.variable_tracking_method == 'track_parents' ? 'joined_items' : 'items',
			items_a = [],
			cart = type == 'checkout' ? fpdata.woo.cart : fpdata.woo.order;

		for ( const id in cart[items_type] ) {
			let prod = cart[items_type][id];
			items_a.push( get_item( prod, prod.qty ) );
		}

		if ( items_a.length == 0 ) return false;

		let payload_o = {
			'contents' : items_a, 
			'value' : cart.value,
			'currency' : fpdata.woo.currency,
		};

		if ( type == "order" ){
			payload_o['conversion_id'] = cart.id;
		}
		
		FP.fns.track_twit_evt( type, event_id, payload_o );
	}

	// track order
	if ( fp.woo.order_data_ready && fp.twit.track_woo_purchase ) track_cart('order', fp.twit.track_woo_purchase );

	// track the start of checkout (except when the whole page or its part is refreshed)
	if ( fp.twit.track_woo_checkout && ! fpdata.refreshed ) {
		if ( fp.woo.checkout_data_ready ) {
			fp.twit.woo_checkout_tracked = true;
			track_cart( 'checkout', fp.twit.track_woo_checkout )
		} else {
			document.addEventListener( 'fupi_woo_checkout_data_ready', ()=>{
				if ( ! fp.twit.woo_checkout_tracked ) {
					fp.twit.woo_checkout_tracked = true;
					track_cart( 'checkout', fp.twit.track_woo_checkout );
				}
			})
		};
	}
}

FP.fns.twit_standard_events = () => {

	// TRACK VIEWS OF ELEMENTS
	// for performance: waits 250ms for dynamically generated content to finish

	FP.fns.twit_observe_inters = ( newly_added_els = false ) => {

		let send_el_view_evt = el => {
			FP.fns.track_twit_evt( 'page element visible', el.dataset.twit_view, {} );
		};
		
		FP.intersectionObserver( newly_added_els, fp.twit.track_views, 'twit', send_el_view_evt, true );
	}
	
	if ( fp.twit.track_views ) setTimeout( ()=>{
		FP.fns.twit_observe_inters();
		FP.addAction( ['dom_modified'], FP.fns.twit_observe_inters );
	}, 250 );

	// TRACK AFFILIATE LINKS

	if ( fp.twit.track_affiliate ) {
		FP.addAction( ['click'], function(){
			var evt_id = FP.getTrackedAffiliateLink( fp.twit.track_affiliate );
			if ( evt_id ) FP.fns.track_twit_evt( 'affiliate link click', evt_id, {} );
		})
	}
	
	// TRACK FORM SUBMITS
	
	if ( fp.twit.track_forms ) {
		FP.addAction( ['form_submit'], function(){
			var evt_id = FP.getSubmittedForm( fp.twit.track_forms );
			if ( evt_id ) FP.fns.track_twit_evt( 'form submit', evt_id, {} );
		})
	}

	// TRACK CLICKS IN SPECIFIC ELEMENTS

	if ( fp.twit.track_elems ) {
		FP.addAction( ['click'], function(){
			var evt_id  = FP.getClickTarget( fp.twit.track_elems );
			if ( evt_id ) FP.fns.track_twit_evt( 'page element click', evt_id, {} );
		})
	}
}

FP.fns.load_twit_footer = function() {
	FP.fns.twit_standard_events();
	if ( fp.loaded.includes('woo') ) FP.fns.twit_woo_events();
}

// INIT FOOTER SCRIPTS
FP.enqueueFn( 'FP.fns.load_twit_footer' );
