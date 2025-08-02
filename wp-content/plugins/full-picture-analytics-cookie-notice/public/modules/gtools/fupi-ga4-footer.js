// WOO EVENTS

FP.fns.send_ga4_evt = ( nr, evt_name, data )=>{
	if ( ! evt_name ) return;
	// add fp_ prefix if the first char is a number
	if ( !isNaN( evt_name.charAt(0) ) ) evt_name = 'fp_' + evt_name;
	// trim the name to max 40 chars
	evt_name = evt_name.substring(0, 40);
	gtag( 'event', evt_name, data);
	if ( fp.main.debug ) console.log('[FP] GA #' + nr + ' event: ' + evt_name, data);
};

FP.fns.ga4_woo_events = ()=>{

	// Helpers

	function add_categories( item, prod ){
		if ( prod.categories && prod.categories.length > 0 ) {
			prod.categories.forEach( ( cat, i ) => {
				if ( i == 0) {
					item['item_category'] = cat;
				} else {
					item['item_category' + ( i + 1 )] = cat;
				}
			} );
		};

		return item;
	};

	function add_brand( item, prod ){
		if ( prod.brand && prod.brand.length > 0 ) item['item_brand'] = prod.brand[0];
		return item;
	};

	function add_list_name( item, prod ){
		if ( prod.list_name ) item['item_list_name'] = prod.list_name;
		return item;
	}

	function add_index( item, prod ){
		if ( prod.index ) item['index'] = prod.index;
		return item;
	}

	// TRACK IMPRESSIONS

	function track_woo_impress() {

		if ( typeof gtag === 'undefined' ) return;

		if ( ! fp.woo.ga4 ) fp.woo.ga4 = {};
		
		let teasers_arr = [],
			single_arr = [],
			value = 0;

		// for each product list
		for ( let [ list_name, item_ids ] of Object.entries( fpdata.woo.lists ) ) {

			let start_index = 1;
			
			// track only items that were not tracked before
			if ( fp.woo.ga4[list_name] ) {
				item_ids = item_ids.filter( id => ! fp.woo.ga4[list_name].includes(id) );
				start_index = fp.woo.ga4[list_name].length + 1;
			} else {
				fp.woo.ga4[list_name] = [];
			};

			item_ids.forEach( ( id, i ) => {
				
				let prod = fpdata.woo.products[id],
					item = { 
					'item_id' : FP.fns.get_woo_prod_id(prod),
					'item_name': FP.fns.get_woo_prod_name(prod),
					'item_list_name' : list_name,
					'index' : i + start_index,
					'price' : prod.price,
				};

				item = add_brand( item, prod );
				item = add_categories( item, prod );

				if ( list_name == 'single' ) {
					value += prod.price;
					single_arr.push( item );
				} else {
					teasers_arr.push( item );
				}
			});

			// prevent double tracking in case the next teasers are added dynamically
			fp.woo.ga4[list_name].push(...item_ids);
		};
		
		if ( teasers_arr.length > 0 ) {

			let payload_o = { 
				'items' : teasers_arr,
				'send_to' : 'fupi_ga4' 
			};
				
			gtag( 'event', 'view_item_list', payload_o );
			if ( fp.main.debug ) console.log( '[FP] GA event view_item_list:', payload_o );
		};

		if ( single_arr.length > 0 ) {

			let payload_o = { 
				'items' : single_arr, 
				'currency' : fpdata.woo.currency,
				'value' : value,
				'send_to' : 'fupi_ga4' 
			};

			gtag( 'event', 'view_item', payload_o );
			if ( fp.main.debug ) console.log( '[FP] GA event view_item:', payload_o );
		}
	};

	if ( ! ( fp.woo.dont_track_views_after_refresh && fpdata.refreshed ) ){
		track_woo_impress();
		FP.addAction( ['woo_impress'], track_woo_impress );
	}

	// TRACK DEFAULT VARIANT VIEW
	// TRACK VARIANT VIEWS

	function woo_variant_view( variant_id ){

		if (typeof gtag === 'undefined' || !fpdata.woo.products[variant_id]) return;

		let prod = fpdata.woo.products[variant_id],
			item = {
				'item_id': FP.fns.get_woo_prod_id(prod),
				'item_name': FP.fns.get_woo_prod_name(prod),
				'item_list_name' : 'single',
				'price': prod.price
			};

		item = add_brand(item, prod);
		item = add_categories(item, prod);

		let payload_o = {
			'items': [item],
			'currency': fpdata.woo.currency,
			'value': prod.price,
			'send_to': 'fupi_ga4'
		};

		gtag('event', 'view_item', payload_o);
		if (fp.main.debug) console.log('[FP] GA view_item event:', payload_o);
	}

	FP.addAction( ['woo_variant_view'], woo_variant_view );
	FP.addAction( ['woo_def_variant_view'], woo_variant_view );

	// TRACK TEASER CLICKS
	// TRACK ADD TO CART / REMOVE FROM CART
	// TRACK ADD TO WISHLIST

	function track_items( data, event_name ){
		
		let products_data = data.products,
			event_value = data.value,
			items_a = [];
		
		products_data.forEach( prod_a => {

			let prod = prod_a[0],
				qty = prod_a[1] || prod.qty || 1,
				item = {
				'item_id' : FP.fns.get_woo_prod_id( prod ),
				'item_name' : FP.fns.get_woo_prod_name( prod ),
				'price' : prod.price,
				'quantity' : qty,
			};

			item = add_index(item, prod);
			item = add_list_name(item, prod);
			item = add_brand(item, prod);
			item = add_categories(item, prod);

			items_a.push( item );
		} );

		if ( items_a.length == 0 ) return false;

		let payload_o = {
			'items' : items_a, 
			'value' : event_value, 
			'currency' : fpdata.woo.currency, 
			'send_to' : 'fupi_ga4' 
		};

		if ( typeof gtag !== 'undefined' ) gtag( 'event', event_name, payload_o );
		if ( fp.main.debug ) console.log( '[FP] GA ' + event_name + ' event: ', payload_o );
	}

	FP.addAction( ['woo_teaser_click'], data => {
		track_items( data, 'select_item' );
	} );

	FP.addAction( ['woo_add_to_cart'], data =>{
		track_items( data, 'add_to_cart' );
	} );

	FP.addAction( ['woo_add_to_wishlist'], data => {
		track_items( data, 'add_to_wishlist');
	} );

	FP.addAction( ['woo_remove_from_cart'], data => {
		track_items( data, 'remove_from_cart' );
	} );

	

	// TRACK CHECKOUT
	// TRACK ORDER

	function track_cart( type ){ // type can be either "checkout" or "order"

		if ( typeof gtag === 'undefined' ) return;

		let items_type = fp.woo.variable_tracking_method == 'track_parents' ? 'joined_items' : 'items',
			items_a = [],
			cart = type == 'checkout' ? fpdata.woo.cart : fpdata.woo.order,
			event_name = type == 'checkout' ? 'begin_checkout' : 'purchase';

		for ( const id in cart[items_type] ) {

			let prod = cart[items_type][id],
				item = {
				'item_id' : FP.fns.get_woo_prod_id(prod),
				'item_name' : FP.fns.get_woo_prod_name(prod),
				'price' : prod.price,
				'quantity' : prod.qty,
			};

			item = add_brand(item, prod);
			item = add_categories(item, prod);

			items_a.push( item );
		}

		if ( items_a.length == 0 ) return false;

		let payload_o = {
			'items' : items_a, 
			'value' : cart.value,
			'currency' : fpdata.woo.currency, 
			'send_to' : 'fupi_ga4',
		};

		if ( cart.coupons && cart.coupons.length > 0 ) payload_o['coupon'] = cart.coupons.join(', ');
		
		if ( type == 'order' ) {
			payload_o['transaction_id'] = fpdata.woo.order.id;
			payload_o['tax'] = fpdata.woo.order.tax || 0;
			payload_o['shipping'] = fpdata.woo.order.shipping || 0;	
		}
		
		gtag( 'event', event_name, payload_o );
		if ( fp.main.debug ) console.log( '[FP] GA ' + event_name + ' event: ', payload_o );
	}

	// track order
	if ( fp.woo.order_data_ready ) {
		if ( ! fp.main.is_pro || ! ( fp.ga41.server_side && fp.ga41.adv_orders ) ) {
			track_cart('order');
		} else {
			if ( fp.main.debug ) console.log( '[FP] GA purchase event is tracked by the server-side script' );
		}
	};

	// track the start of checkout (except when the whole page or its part is refreshed)
	if ( ! fpdata.refreshed ) {
		if ( fp.woo.checkout_data_ready ) {
			fp.ga41.woo_checkout_tracked = true;
			track_cart('checkout')
		} else {
			document.addEventListener( 'fupi_woo_checkout_data_ready', ()=>{
				if ( ! fp.ga41.woo_checkout_tracked ) {
					fp.ga41.woo_checkout_tracked = true;
					track_cart('checkout');
				}
			})
		};
	}
};

// STANDARD EVENTS

FP.fns.ga4_standard_events = nr => {

	var _ga = fp['ga4' + nr];

	// TRACK SCROLL

	if ( _ga.track_scroll_method && _ga.track_scroll ) {
		
		_ga.track_scroll = FP.formatScrollPoints( _ga.track_scroll );
		
		FP.addAction( ['scroll', 'active_time_tick'], function(){

			if ( typeof gtag === 'undefined' ) return;

			if (
				_ga.track_scroll.length > 0 &&
				fpdata.activity.total >= fp.track.track_scroll_time &&
				fpdata.scrolled.current_px >= fp.track.track_scroll_min
			){
				var reachedPoint = FP.isScrollTracked( _ga.track_scroll );
				
				if ( reachedPoint ) {
					// remove reached scroll points from array
					_ga.track_scroll = _ga.track_scroll.filter( function( point ){ return point > reachedPoint } );

					var evt_name = 'scrolled_' + reachedPoint,
						data = { 'send_to': fp['ga4' + nr].id };
					
					if ( _ga.track_scroll_method == 'params' ) {
						evt_name = 'scroll';
						data['percent_scrolled'] = reachedPoint;
					};

					FP.fns.send_ga4_evt( nr, evt_name, data );
				}
			}
		} );
	}

	// TRACK VIEWS OF ELEMENTS
	// for performance: waits 250ms for dynamically generated content to finish

	FP.fns['ga4' + nr + '_observe_inters'] = ( newly_added_els = false ) => {
		
		let send_el_view_evt = ( el, nr ) => {

			if ( typeof gtag === 'undefined' ) return;
			
			let evt_name = el.dataset['ga4' + nr + '_view'],
				data = { 'send_to': fp['ga4' + nr].id };
			
			if ( _ga.track_views_method == 'params' ) {
				data['viewed_element'] = evt_name;
				evt_name = 'element_view';
			};
			
			FP.fns.send_ga4_evt( nr, evt_name, data );
		};
		
		FP.intersectionObserver( newly_added_els, _ga.track_views, 'ga4' + nr, send_el_view_evt, true, nr );
	}
	
	if ( _ga.track_views_method && _ga.track_views ) setTimeout( ()=>{
		FP.fns['ga4' + nr + '_observe_inters']();
		FP.addAction( ['dom_modified'], FP.fns['ga4' + nr + '_observe_inters'] );
	}, 250 ); // wait for any dynamically generated content
	
	// TRACK AFFILIATE LINKS

	if ( _ga.track_affil_method && _ga.track_affiliate ) {

		FP.addAction( ['click'], function(){

			if ( typeof gtag === 'undefined' ) return;
			
			var trackedAffLink = FP.getTrackedAffiliateLink( _ga.track_affiliate );

			if ( ! trackedAffLink ) return;
			
			var evt_name = trackedAffLink,
				data = { 'send_to': fp['ga4' + nr].id };
			
			if ( _ga.track_affil_method == 'params' ) {
				evt_name = 'affiliate_link_click';
				data['affiliate_link_click'] = trackedAffLink;
			};
			
			FP.fns.send_ga4_evt( nr, evt_name, data );
		} );
	}

	// TRACK CLICKS ON EMAIL AND TEL LINKS

	if ( _ga.track_email_tel ) {
		FP.addAction( ['click'], function(){
			
			if ( typeof gtag === 'undefined' ) return;

			if ( fpdata.clicked.link && ( fpdata.clicked.link.is_email || fpdata.clicked.link.is_tel ) ) {
				
				var link_type = fpdata.clicked.link.is_email ? 'email' : 'tel',
					evt_name = link_type + '_click_' + ( fpdata.clicked.link.safe_email || fpdata.clicked.link.safe_tel ),
					data = { 'send_to': fp['ga4' + nr].id };

				if ( _ga.track_email_tel == 'params' ){
					evt_name = link_type + '_link_click'
					data['contact_click'] = fpdata.clicked.link.safe_email || fpdata.clicked.link.safe_tel;
				}
				
				FP.fns.send_ga4_evt( nr, evt_name, data );
			}
		} );
	}

	// TRACK FORM SUBMITS

	if ( _ga.track_forms_method && _ga.track_forms ) {

		FP.addAction( ['form_submit'], function(){
			
			if ( typeof gtag === 'undefined' ) return;

			var submittedForm = FP.getSubmittedForm( _ga.track_forms );

			if ( submittedForm ){

				var evt_name = submittedForm,
					data = { 'send_to': fp['ga4' + nr].id };

				if ( _ga.track_forms_method == 'params' ){
					evt_name = 'form_submit';
					data['submitted_form'] = submittedForm;
				}
				
				FP.fns.send_ga4_evt( nr, evt_name, data );
			}
		})
	}

	// TRACK CLICKS ON PAGE ELEMENTS

	if ( _ga.track_elems_method && _ga.track_elems ) {

		FP.addAction( ['click'], function(){

			if ( typeof gtag === 'undefined' ) return;

			var trackedElName  = FP.getClickTarget( _ga.track_elems );

			if ( trackedElName ) {

				var evt_name = trackedElName,
					data = { 'send_to': fp['ga4' + nr].id };

				if ( _ga.track_elems_method == 'params' ){
					evt_name = 'element_click',
					data['element_click'] = trackedElName;
				}
				
				FP.fns.send_ga4_evt( nr, evt_name, data );
			}
		})
	}

	
};

FP.fns.load_ga4_footer = nr => {
	
	// Fire for GA4 #1 & #2
	FP.fns.ga4_standard_events( nr );

	// Everything below fires only for #1 because it sends data to both GAs
	if ( nr == 1 && fp.loaded.includes('woo') ) FP.fns.ga4_woo_events();
};

FP.enqueueFn( 'FP.fns.load_ga4_footer' );