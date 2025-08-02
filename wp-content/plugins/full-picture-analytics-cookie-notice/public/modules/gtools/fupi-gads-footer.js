FP.fns.gads_woo_events = () => {

	// TRACK IMPRESSIONS

	function track_woo_impress() {
		
		if ( ! fpdata.woo.lists.single ) return;
		if ( ! fp.woo.gads ) fp.woo.gads = { 'single' : [] };
		
		let items_a = [],
			value = 0,
			item_ids = fpdata.woo.lists.single.filter( id => ! fp.woo.gads.single.includes(id) ); // track only items that were not tracked before

		item_ids.forEach( id => {
			
			let prod = fpdata.woo.products[id],
				item = { 
				'id' : FP.fns.get_woo_prod_id(prod),
				'google_business_vertical' : fp.gads.business_type,
			};

			value += prod.price;
			items_a.push( item );
			
		});

		// prevent double tracking in case the next prods are added dynamically
		fp.woo.gads.single.push(...item_ids);

		if ( items_a.length > 0 ) {

			let payload_o = { 
				'items' : items_a, 
				'value' : value,
				'send_to' : fp.gads.id2 || fp.gads.id,
			};

			gtag('event', 'view_item', payload_o );
			if ( fp.main.debug ) console.log('[FP] GAds view_item event:', payload_o);
		}
	};

	if ( ! ( fp.woo.dont_track_views_after_refresh && fpdata.refreshed ) ){
		track_woo_impress();
		FP.addAction( ['woo_impress'], track_woo_impress );
	}

	// TRACK ADD TO CART

	function track_add_to_cart( data ){
		
		let products_data = data.products,
			event_value = data.value,
			items_a = [];
		
		products_data.forEach( prod_a => {

			let prod = prod_a[0],
				item = {
				'id' : FP.fns.get_woo_prod_id(prod),
				'google_business_vertical' : fp.gads.business_type,
			};

			items_a.push( item );
		} );

		if ( items_a.length == 0 ) return false;

		let payload_event = {
			'items' : items_a,
			'value' : event_value, 
			'send_to' : fp.gads.id2 || fp.gads.id,
		};

		gtag('event', 'add_to_cart', payload_event );
		if ( fp.main.debug ) console.log('[FP] GAds add_to_cart event:', payload_event);

		if ( ! fp.gads.woo_add_to_cart_conv_id ) return;

		let payload_conversion = {
			'items' : items_a, 
			'value' : event_value,
			'send_to' : ( fp.gads.id2 || fp.gads.id ) + '/' + fp.gads.woo_add_to_cart_conv_id,
			'currency' : fpdata.woo.currency,
		}

		gtag('event', 'conversion', payload_conversion );
		if ( fp.main.debug ) console.log('[FP] GAds add_to_cart conversion', payload_conversion);
	}

	FP.addAction( ['woo_add_to_cart'], data =>{
		track_add_to_cart( data );
	} );

	// TRACK CHECKOUT

	function track_checkout(){ // type can be either "checkout" or "order"

		if ( typeof gtag === 'undefined' ) return;

		let items_type = fp.woo.variable_tracking_method == 'track_parents' ? 'joined_items' : 'items',
			items_a = [],
			cart = fpdata.woo.cart;

		for ( const id in cart[items_type] ) {

			let prod = cart[items_type][id],
				item = {
				'id' : FP.fns.get_woo_prod_id(prod),
				'google_business_vertical' : fp.gads.business_type,
			};

			items_a.push( item );
		}

		if ( items_a.length == 0 ) return false;

		let payload_event = {
			'items' : items_a, 
			'value' : cart.value,
			'send_to' : fp.gads.id2 || fp.gads.id,
		};
		
		gtag( 'event', 'checkout', payload_event );
		if ( fp.main.debug ) console.log( '[FP] GAds checkout event: ', payload_event );

		if ( ! fp.gads.woo_checkout_conv_id ) return;

		let payload_conversion = {
			'items' : items_a, 
			'value' : cart.value,
			'send_to' : ( fp.gads.id2 || fp.gads.id ) + '/' + fp.gads.woo_checkout_conv_id,
			'currency' : fpdata.woo.currency,
		}

		gtag('event', 'conversion', payload_conversion );
		if ( fp.main.debug ) console.log('[FP] GAds checkout conversion', payload_conversion);
	};

	// track the start of checkout (except when the whole page or its part is refreshed)
	if ( ! fpdata.refreshed ) {
		if ( fp.woo.checkout_data_ready ) {
			fp.gads.woo_checkout_tracked = true;
			track_checkout();
		} else {
			document.addEventListener( 'fupi_woo_checkout_data_ready', ()=>{
				if ( ! fp.gads.woo_checkout_tracked ) {
					fp.gads.woo_checkout_tracked = true;
					track_checkout();
				}
			})
		}
	};

	// TRACK PURCHASE

	function track_purchase(){
		
		if ( typeof gtag === 'undefined' ) return;

		let items_type = fp.woo.variable_tracking_method == 'track_parents' ? 'joined_items' : 'items',
			items_a = [],
			cart = fpdata.woo.order;

		for ( const id in cart[items_type] ) {

			let prod = cart[items_type][id],
				item = {
				'id' : FP.fns.get_woo_prod_id(prod),
				'google_business_vertical' : fp.gads.business_type,
			};

			items_a.push( item );
		}

		if ( items_a.length == 0 ) return false;

		// Track event
		
		let payload_event = {
			'items' : items_a, 
			'value' : cart.value,
			'currency' : fpdata.woo.currency, 
			'send_to' : fp.gads.id2 || fp.gads.id,
		};

		gtag('event', 'purchase', payload_event );
		if ( fp.main.debug ) console.log('[FP] GAds purchase event', payload_event);

		// Track conversion

		if ( ! fp.gads.woo_conv_id ) return;

		let payload_conversion = {
			'items' : items_a, 
			'value' : cart.value,
			'currency' : fpdata.woo.currency, 
			'send_to' : ( fp.gads.id2 || fp.gads.id ) + '/' + fp.gads.woo_conv_id,
			'transaction_id': fpdata.woo.order.id,
		}

		gtag('event', 'conversion', payload_conversion );
		if ( fp.main.debug ) console.log('[FP] GAds purchase conversion', payload_conversion);
	};

	if ( fp.woo.order_data_ready ) track_purchase();
}

FP.fns.gads_standard_events = () => {

	// TRACK EMAIL LINKS

	if ( fp.gads.track_email ) {
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && fpdata.clicked.link.is_email ) {
				gtag( 'event', 'conversion', {'send_to': ( fp.gads.id2 || fp.gads.id ) + '/' + fp.gads.track_email } );
				if ( fp.main.debug ) console.log('[FP] GAds conversion event: email link click', ( fp.gads.id2 || fp.gads.id ) + '/' + fp.gads.track_email );
			}
		} );
	}

	// TRACK TEL LINKS

	if ( fp.gads.track_tel ) {
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && fpdata.clicked.link.is_tel ) {
				gtag( 'event', 'conversion', {'send_to': ( fp.gads.id2 || fp.gads.id ) + '/' + fp.gads.track_tel } );
				if ( fp.main.debug ) console.log('[FP] GAds conversion event: tel link click', ( fp.gads.id2 || fp.gads.id ) + '/' + fp.gads.track_tel );
			}
		} );
	}

	// TRACK VIEWS OF ELEMENTS
	// for performance: waits 250ms for dynamically generated content to finish

	FP.fns.gads_observe_inters = ( newly_added_els = false ) => {

		let send_el_view_evt = el => {

			if ( typeof gtag === 'undefined' ) return;

			gtag( 'event', 'conversion', {'send_to': ( fp.gads.id2 || fp.gads.id ) + '/' + el.dataset['gads_view'] } );
			
			if ( fp.main.debug ) console.log('[FP] GAds conversion event: element view', ( fp.gads.id2 || fp.gads.id ) + '/' + el.dataset['gads_view'] );
		};
		
		FP.intersectionObserver( newly_added_els, fp.gads.track_views, 'gads', send_el_view_evt, true);
	}
	
	if ( fp.gads.track_views ) setTimeout( ()=>{
		FP.fns.gads_observe_inters();
		FP.addAction( ['dom_modified'], FP.fns.gads_observe_inters );
	}, 250 ); 

	// TRACK AFFILIATE LINKS

	if ( fp.gads.track_affiliate ) {
		FP.addAction( ['click'], function(){
			var trackedAffLink_convID = FP.getTrackedAffiliateLink( fp.gads.track_affiliate );
			if ( trackedAffLink_convID ) {
				gtag( 'event', 'conversion', {'send_to' : ( fp.gads.id2 || fp.gads.id ) + '/' + trackedAffLink_convID });
				if ( fp.main.debug ) console.log('[FP] GAds conversion event: affiliate click', ( fp.gads.id2 || fp.gads.id ) + '/' + trackedAffLink_convID );
			}
		} );
	}

	// TRACK FORM SUBMITS

	if ( fp.gads.track_forms ) {
		FP.addAction( ['form_submit'], function(){
			var submittedForm_convID = FP.getSubmittedForm( fp.gads.track_forms );
			if ( submittedForm_convID ){
				gtag( 'event', 'conversion', {'send_to' : ( fp.gads.id2 || fp.gads.id ) + '/' + submittedForm_convID } );
				if ( fp.main.debug ) console.log('[FP] GAds conversion event: form submit', ( fp.gads.id2 || fp.gads.id ) + '/' + submittedForm_convID);
			}
		})
	}

	// TRACK CLICKS ON PAGE ELEMENTS

	if ( fp.gads.track_elems ) {
		FP.addAction( ['click'], function(){
			var trackedEl_convID  = FP.getClickTarget( fp.gads.track_elems );
			if ( trackedEl_convID ) {
				gtag( 'event', 'conversion', { 'send_to' : ( fp.gads.id2 || fp.gads.id ) + '/' + trackedEl_convID } );
				if ( fp.main.debug ) console.log('[FP] GAds conversion event: element click', ( fp.gads.id2 || fp.gads.id ) + '/' + trackedEl_convID );
			}
		})
	}
};

FP.fns.load_gads_footer = () => {
	FP.fns.gads_standard_events();
	if ( fp.loaded.includes('woo') ) FP.fns.gads_woo_events();
};

FP.enqueueFn( 'FP.fns.load_gads_footer' );
