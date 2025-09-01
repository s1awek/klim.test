// WOO EVENTS

FP.fns.fbp_woo_events = () => {

	// TRACK IMPRESSIONS

	function track_woo_impress() {
		
		if ( ! fpdata.woo.lists.single ) return;
		if ( ! fp.woo.fbp ) fp.woo.fbp = { 'single' : [] };
		
		let items_a = [],
			value = 0,
			item_ids = fpdata.woo.lists.single.filter( id => ! fp.woo.fbp.single.includes(id) ); // track only items that were not tracked before

		item_ids.forEach( id => {
			
			let prod = fpdata.woo.products[id],
				item = { 
				'id' : FP.fns.get_woo_prod_id(prod),
				'quantity' : 1,
			};

			value += prod.price;
			items_a.push( item );
		});

		// prevent double tracking in case the next prods are added dynamically
		fp.woo.fbp.single.push(...item_ids);

		if ( items_a.length > 0 ) {

			let payload_o = { 
				'contents' : items_a, 
				'currency' : fpdata.woo.currency, 
				'value' : value, 
				'content_type' : 'product'
			};

			FP.track_fbp_evt( false, 'ViewContent', false, payload_o );
		}
	};

	if ( ! ( fp.woo.dont_track_views_after_refresh && fpdata.refreshed ) ){
		track_woo_impress();
		FP.addAction( ['woo_impress'], track_woo_impress );
	}

	// TRACK DEFAULT VARIANT VIEW
	// TRACK VARIANT VIEWS

	function woo_variant_view( variant_id ){

		let prod = fpdata.woo.products[variant_id];

		let payload_o = {
			'contents' : [ {
				'id' : FP.fns.get_woo_prod_id(prod),
				'quantity' : 1,
			} ],
			'currency' : fpdata.woo.currency,
			'value' : prod.price,
			'content_type' : 'product'
		};

		FP.track_fbp_evt( false, 'ViewContent', false, payload_o );
	}

	FP.addAction( ['woo_variant_view'], woo_variant_view );
	FP.addAction( ['woo_def_variant_view'], woo_variant_view );

	// TRACK ADD TO CART
	// TRACK ADD TO WISHLIST

	function track_items( data, event_name ){
		
		let products_data = data.products,
			event_value = data.value,
			items_a = [];
		
		products_data.forEach( prod_a => {

			let prod = prod_a[0],
				qty = prod_a[1] || prod.qty || 1,
				item = {
				'id' : FP.fns.get_woo_prod_id( prod ),
				'item_price' : prod.price,
				'quantity' : parseInt(qty),
			};

			items_a.push( item );
		} );

		if ( items_a.length == 0 ) return false;

		let payload_o = {
			'contents' : items_a, 
			'value' : event_value, 
			'currency' : fpdata.woo.currency,
			'content_type' : 'product'
		};

		FP.track_fbp_evt( false, event_name, false, payload_o );
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
			event_name = type == 'checkout' ? 'InitiateCheckout' : 'Purchase';

		for ( const id in cart[items_type] ) {

			let prod = cart[items_type][id],
				item = {
				'id' : FP.fns.get_woo_prod_id(prod),
				'item_price' : prod.price,
				'quantity' : prod.qty,
			};

			items_a.push( item );
		}

		if ( items_a.length == 0 ) return false;

		let payload_o = {
			'contents' : items_a, 
			'value' : cart.value, 
			'num_items': items_a.length,
			'currency' : fpdata.woo.currency,
			'content_type' : 'product',
		};

		FP.track_fbp_evt( false, event_name, false, payload_o );
	}
	
	// track order
	// unless we already do it through server with advanced order tracking
	if ( fp.woo.order_data_ready ) {
		if ( ! fp.main.is_pro || ! ( fp.fbp.server_side && fp.fbp.adv_orders ) ) {
			track_cart('order');
		} else {
			if ( fp.main.debug ) console.log( '[FP] Meta Pixel purchase event is tracked by the server-side script' );
		}
	};

	// track the start of checkout (except when the whole page or its part is refreshed)
	if ( ! fpdata.refreshed ) {
		if ( fp.woo.checkout_data_ready ) {
			fp.fbp.woo_checkout_tracked = true;
			track_cart('checkout')
		} else {
			document.addEventListener( 'fupi_woo_checkout_data_ready', ()=>{
				if ( ! fp.fbp.woo_checkout_tracked ) {
					fp.fbp.woo_checkout_tracked = true;
					track_cart('checkout');
				}
			})
		};
	}
};

// STANDARD EVENTS

FP.fns.fbp_standard_events = () => {
		
	// TRACK OUTBOUND LINKS

	if ( fp.fbp.track_outbound ) {
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && fpdata.clicked.link.is_outbound ) {
				var data = {
					'url' : fpdata.clicked.link.href,
					'time on page' : fpdata.activity.total,
				};
				FP.track_fbp_evt( true, 'outbound', false, data, !! fp.fbp.track_outbound_capi );
			}
		} );
	}

	// TRACK CLICKS ON EMAIL & TEL LINKS

	if ( fp.fbp.track_email_tel ) {
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && ( fpdata.clicked.link.is_email || fpdata.clicked.link.is_tel ) ) {
				var data = {
					'target' : fpdata.clicked.link.safe_email || fpdata.clicked.link.safe_tel,
					'type' : fpdata.clicked.link.is_email ? 'email' : 'tel',
					'time on page' : fpdata.activity.total,
				};
				FP.track_fbp_evt( true, 'Contact', false, data, !! fp.fbp.track_email_tel_capi );
			}
		} );
	}

	// TRACK AFFILIATE LINKS

	if ( fp.fbp.track_affiliate ) {
		FP.addAction( ['click'], function(){
			var trackedAffLink = FP.getTrackedAffiliateLink( fp.fbp.track_affiliate );
			if ( trackedAffLink ) {
				var data = {
					'target' : trackedAffLink,
					'time on page' : fpdata.activity.total,
				};
				FP.track_fbp_evt( true, 'affiliate', false, data, !! fp.fbp.track_affiliate_capi );
			}
		} );
	}

	// TRACK FILE DOWNLOADS

	if ( fp.fbp.track_file_downl ) {
		FP.addAction( ['click'], function(){
			var filename = FP.getTrackedFilename( fp.fbp.track_file_downl );
			if ( filename ) {
				var data = {
					'file' : filename,
					'time on page' : fpdata.activity.total,
				};
				FP.track_fbp_evt( true, 'file download', false, data, !! fp.fbp.track_file_downl_capi );
			}
		} );
	};

	// TRACK FORMS

	if ( fp.fbp.track_forms ) {
		FP.addAction( ['form_submit'], function(){
			var submittedForm = FP.getSubmittedForm( fp.fbp.track_forms );
			if ( submittedForm ){
				var data = {
					'form' : submittedForm,
					'time on page' : fpdata.activity.total,
				};
				FP.track_fbp_evt( true, 'form submit', false, data, !! fp.fbp.track_forms_capi );
			}
		})
	}

	// TRACK SCROLLS

	if ( fp.fbp.track_scroll ){
		fp.fbp.track_scroll = FP.formatScrollPoints( fp.fbp.track_scroll );
		FP.addAction( ['scroll', 'active_time_tick'], function(){
			// check if the window was scrolled
			if ( fp.fbp.track_scroll.length > 0 && fpdata.activity.total >= fp.track.track_scroll_time && fpdata.scrolled.current_px >= fp.track.track_scroll_min ) {
				var reachedPoint = FP.isScrollTracked( fp.fbp.track_scroll );
				if ( reachedPoint ) {
					// remove from array the scroll points that were already reached
					fp.fbp.track_scroll = fp.fbp.track_scroll.filter( function( point ){ return point > reachedPoint } );
					// track scroll
					var data = {
						'scroll height' : reachedPoint,
						'time on page' : fpdata.activity.total,
					};
					FP.track_fbp_evt( true, 'scroll', false, data, !! fp.fbp.track_scroll_capi );
				}
			}
		} );
	}

	// TRACK VIEWS OF ELEMENTS
	// for performance: waits 250ms for dynamically generated content to finish

	FP.fns.fbp_observe_inters = ( newly_added_els = false ) => {

		let send_el_view_evt = el => {

			var data = {
				'element' : el.dataset['fbp_view'],
				'el' : el.dataset['fbp_view'], // for compat. with FP ver < 2.3
				'time on page' : fpdata.activity.total,
			};
			FP.track_fbp_evt( true, 'user viewed', false, data, !! fp.fbp.track_views_capi );
		};
		
		FP.intersectionObserver( newly_added_els, fp.fbp.track_views, 'fbp', send_el_view_evt, true);
	}
	
	if ( fp.fbp.track_views ) setTimeout( ()=>{
		FP.fns.fbp_observe_inters();
		FP.addAction( ['dom_modified'], FP.fns.fbp_observe_inters );
	}, 250 );

	

	// TRACK SPECIFIC ELEMENTS

	if ( fp.fbp.track_elems ) {
		FP.addAction( ['click'], function(){
			var trackedElName  = FP.getClickTarget( fp.fbp.track_elems );
			if ( trackedElName ) {
				var data = {
					'name' : trackedElName,
					'time on page' : fpdata.activity.total,
				};
				FP.track_fbp_evt( true, 'click on element', false, data, !! fp.fbp.track_elems_capi );
			}
		})
	}
};

FP.fns.load_fbp_footer = function() {
	FP.fns.fbp_standard_events();
	if ( fp.loaded.includes('woo') ) FP.fns.fbp_woo_events();
}

// INIT FOOTER SCRIPTS
FP.enqueueFn( 'FP.fns.load_fbp_footer' );
