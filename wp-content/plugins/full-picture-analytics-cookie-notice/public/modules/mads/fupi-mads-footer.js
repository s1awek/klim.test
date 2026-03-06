
FP.fns.mads_woo_get_pagetype = ()=>{
	let page_type = 'other';
			
	switch ( fpdata.page_type ) {
		case 'Woo Product Category':
		case 'Woo Product Tag':
			page_type = 'category';
		break;
		case 'Front Page':
			page_type = 'home';
		break;
		case 'Woo Product':
			page_type = 'product';
		break;
		case 'Woo Checkout':
			page_type = 'cart'; // << there is no "checkout" in MS Ads
		break;
		case 'Woo Order Received':
			page_type = 'purchase';
		break;
	};

	return page_type;
}

function fupi_mads_woo_footer(){

	// TRACK IMPRESSIONS

	function track_woo_impress() {

		if ( ! fp.woo.mads ) fp.woo.mads = {};
		
		let teasers_ids_a = [],
			single_ids_a = [],
			teasers_value = 0,
			single_value = 0,
			page_type = FP.fns.mads_woo_get_pagetype();

		// for each product list
		for ( let [ list_name, item_ids ] of Object.entries( fpdata.woo.lists ) ) {
			
			// track only items that were not tracked before
			if ( fp.woo.mads[list_name] ) {
				item_ids = item_ids.filter( id => ! fp.woo.mads[list_name].includes(id) );
			} else {
				fp.woo.mads[list_name] = [];
			}

			item_ids.forEach( ( id, i ) => {
				
				let prod = fpdata.woo.products[id];
				
				if ( list_name == 'single' ) {
					single_ids_a.push( FP.fns.get_woo_prod_id(prod) );
					single_value += prod.price;
				} else {
					teasers_ids_a.push( FP.fns.get_woo_prod_id(prod) );
					teasers_value += prod.price;
				}
			});

			// prevent double tracking in case the next teasers are added dynamically
			fp.woo.mads[list_name].push(...item_ids);
		};
		
		if ( teasers_ids_a.length > 0 ) {

			let payload_o = { 
				'currency' : fpdata.woo.currency, 
				'event_value' : Math.round( teasers_value * 100, 2 ) / 100, // fix for incorrect rounding
				'ecomm_prodid' : teasers_ids_a,
				'ecomm_pagetype' : page_type,
			};
				
			window.uetq.push( 'event', 'woo list item view', payload_o );
			if ( fp.main.debug ) console.log('[FP] MS Ads "woo list item view" event action', payload_o);
		};

		if ( single_ids_a.length > 0 ) {

			let impress_evt_name = 'woo product view';
			if ( fp.woo?.force_item_view_on_url && fp.woo?.force_item_view_on_url.some( url_part => document.location.href.includes(url_part) ) ) {
				impress_evt_name = 'woo list item view';
			}

			let payload_o = { 
				'currency' : fpdata.woo.currency, 
				'event_value' : single_value,
				'ecomm_prodid' : single_ids_a,
				'ecomm_pagetype' : page_type,
			};

			if ( fpdata.content_id ) payload_o['ecomm_category'] = fpdata.content_id;
			
			window.uetq.push( 'event', impress_evt_name, payload_o );
			if ( fp.main.debug ) console.log('[FP] MS Ads "' + impress_evt_name + '" event action', payload_o);

		}
	};

	if ( ! ( fp.woo.dont_track_views_after_refresh && fpdata.refreshed ) ){
		track_woo_impress();
		FP.addAction( ['woo_impress'], track_woo_impress );
	}

	// TRACK DEFAULT VARIANT VIEW
	// TRACK VARIANT VIEWS

	function woo_variant_view( variant_id ){

		let page_type = FP.fns.mads_woo_get_pagetype(),
			prod = fpdata.woo.products[variant_id];
		
		let payload_o = {
			'currency': fpdata.woo.currency,
			'event_value': prod.price,
			'ecomm_prodid': [FP.fns.get_woo_prod_id(prod)],
			'ecomm_pagetype': page_type
		};

		if ( fpdata.content_id ) payload_o['ecomm_category'] = fpdata.content_id;

		window.uetq.push( 'event', 'woo product view', payload_o );
		if ( fp.main.debug ) console.log('[FP] MS Ads "woo product view" event action', payload_o);
	}

	FP.addAction( ['woo_variant_view'], woo_variant_view );
	FP.addAction( ['woo_def_variant_view'], woo_variant_view );

	// TRACK ADD TO CART

	function track_items( data ){
		
		let products_data = data.products,
			event_value = data.value,
			items_ids_a = [],
			items_a = [],
			page_type = FP.fns.mads_woo_get_pagetype();
		
		products_data.forEach( prod_a => {

			let prod = prod_a[0],
				prod_id = FP.fns.get_woo_prod_id( prod_a[0] ),
				qty = prod_a[1] || prod.qty || 1;
			
			items_a.push(
				{
					'id' : prod_id,
					'quantity' : qty,
					'price' : prod.price,
				}
			)
			items_ids_a.push( prod_id );
		} );

		if ( items_ids_a.length == 0 ) return false;

		let payload_o = {
			'currency' : fpdata.woo.currency, 
			'ecomm_prodid' : items_ids_a,
			'ecomm_pagetype' : page_type,
			'ecomm_totalvalue' : event_value,
			'event_value' : event_value,
			'revenue_value' : event_value,
			'items' : items_a, 
		};

		window.uetq.push( 'event', 'woo add to cart', payload_o );
		if ( fp.main.debug ) console.log('[FP] MS Ads "woo add to cart" event action', payload_o);
	}

	FP.addAction( ['woo_add_to_cart'], data =>{
		track_items( data );
	} );

	if ( fp.woo.cart_to_track ) track_items( fp.woo.cart_to_track ); // when ATC is tracked in cart

	// TRACK CHECKOUT
	// TRACK ORDER

	function track_cart( event_name ){ // type can be either "checkout" or "order"

		let items_type = fp.woo.variable_tracking_method == 'track_parents' ? 'joined_items' : 'items',
			items_a = [],
			items_ids_a = [],
			cart = event_name == 'checkout' ? fpdata.woo.cart : fpdata.woo.order;

		for ( const id in cart[items_type] ) {

			let prod = cart[items_type][id],
				prod_id = FP.fns.get_woo_prod_id(prod),
				item = {
				'id' : prod_id,
				'price' : prod.price,
				'quantity' : prod.qty,
			};

			items_ids_a.push( prod_id );
			items_a.push( item );
		}

		if ( items_a.length == 0 ) return false;

		let cart_value = Math.round( cart.value * 100, 2 ) / 100; // fix for incorrect rounding

		let payload_o = {
			'items' : items_a, 
			'ecomm_prodid' : items_ids_a,
			'ecomm_pagetype' : FP.fns.mads_woo_get_pagetype(),
			'ecomm_totalvalue' : cart_value,
			'revenue_value' : cart_value,
			'currency' : fpdata.woo.currency, 
		};

		if ( event_name == 'purchase' ) {
			payload_o['transaction_id'] = fpdata.woo.order.id;
		}
		
		window.uetq.push( 'event', 'woo ' + event_name, payload_o );
		if ( fp.main.debug ) console.log('[FP] MS Ads "woo ' + event_name + '" event action', payload_o);
	}

	// track order
	if ( fp.woo.order_data_ready ) track_cart('purchase');

	// track the start of checkout (except when the whole page or its part is refreshed)
	if ( ! fpdata.refreshed ) {
		if ( fp.woo.checkout_data_ready ) {
			fp.mads.woo_checkout_tracked = true;
			track_cart('checkout')
		} else {
			document.addEventListener( 'fupi_woo_checkout_data_ready', ()=>{
				if ( ! fp.mads.woo_checkout_tracked ) {
					fp.mads.woo_checkout_tracked = true;
					track_cart('checkout');
				}
			})
		};
	}

	FP.loaded('mads_footer_woo');
}

function fupi_mads_footer(){

	// TRACK VIEWS OF ELEMENTS
	// for performance: waits 250ms for dynamically generated content to finish

	FP.fns.mads_observe_inters = ( newly_added_els = false ) => {

		let send_el_view_evt = el => {
			
			if ( ! el.dataset.mads_view ) return;

			window.uetq.push( 'event', el.dataset.mads_view, {} );
			if ( fp.main.debug ) console.log( '[FP] MS Ads event action: ' + el.dataset.mads_view );
		};
		
		FP.intersectionObserver( newly_added_els, fp.mads.track_views, 'mads', send_el_view_evt, true);
	}
	
	if ( fp.mads.track_views ) setTimeout( ()=>{
		FP.fns.mads_observe_inters();
		FP.addAction( ['dom_modified'], FP.fns.mads_observe_inters );
	}, 250 );

	// TRACK AFFILIATE LINKS

	if ( fp.mads.track_affiliate ) {
		FP.addAction( ['click'], function(){
			var evt_action = FP.getTrackedAffiliateLink( fp.mads.track_affiliate );
			if ( evt_action ) {
				window.uetq.push( 'event', evt_action, {} );
				if ( fp.main.debug ) console.log( '[FP] MS Ads event action: ' + evt_action );
			}
		})
	}

	// TRACK FILE DOWNLOADS

	if ( fp.mads.track_file_downl ) {
		FP.addAction( ['click'], function(){
			var is_tracked_file = FP.getTrackedFilename( fp.mads.track_file_downl.formats );
			if ( is_tracked_file ) {
				window.uetq.push( 'event', fp.mads.track_file_downl.val, {} );
				if ( fp.main.debug ) console.log( '[FP] MS Ads event action: ' + fp.mads.track_file_downl.val );
			}
		})
	}

	// TRACK FORMS

	if ( fp.mads.track_forms ) {
		FP.addAction( ['form_submit'], function(){
			var evt_action = FP.getSubmittedForm( fp.mads.track_forms );
			if ( evt_action ){
				window.uetq.push( 'event', evt_action, {} );
				if ( fp.main.debug ) console.log( '[FP] MS Ads event action: ' + evt_action );
			}
		})
	}

	// TRACK SPECIFIC ELEMENTS

	if ( fp.mads.track_elems ) {
		FP.addAction( ['click'], function(){
			var evt_action  = FP.getClickTarget( fp.mads.track_elems );
			if ( evt_action ) {
				window.uetq.push( 'event', evt_action, {} );
				if ( fp.main.debug ) console.log( '[FP] MS Ads event action: ' + evt_action );
			}
		})
	}

	FP.loaded('mads_footer');
}

FP.load('mads_footer','fupi_mads_footer',['mads','footer_helpers']);
FP.load('mads_footer_woo','fupi_mads_woo_footer',['mads','footer_helpers','woo']);