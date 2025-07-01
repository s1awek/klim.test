FP.fns.push_to_gtm_dl = ( evt_name, payload ) => {
	// (optional) Do not clear the "ecommerce" obj before each push
	if ( ! fp.gtm['clear_woo_data'] ) window[fp.gtm.datalayer].push({ ecommerce: null });

	window[fp.gtm.datalayer].push( payload );
	if ( fp.vars.debug ) console.log( '[FP] GTM event ' + evt_name + ':', payload );
};

FP.fns.gotm_woo_events = () => {
	
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

		if ( ! fp.woo.gtm ) fp.woo.gtm = {};
		
		let teasers_arr = [],
			single_arr = [],
			value = 0;

		// for each product list
		for ( let [ list_name, item_ids ] of Object.entries( fpdata.woo.lists ) ) {

			let start_index = 1;
			
			// track only items that were not tracked before
			if ( fp.woo.gtm[list_name] ) {
				item_ids = item_ids.filter( id => ! fp.woo.gtm[list_name].includes(id) );
				start_index = fp.woo.gtm[list_name].length + 1;
			} else {
				fp.woo.gtm[list_name] = [];
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
			fp.woo.gtm[list_name].push(...item_ids);
		};
		
		if ( teasers_arr.length > 0 ) {

			let teasers_payload_o = {
				'event' : 'view_item_list',
				'ecommerce' : {
					'currency' : fpdata.woo.currency,
					'items' : teasers_arr,
				}
				
			};
			
			FP.fns.push_to_gtm_dl( 'view_item_list', teasers_payload_o );
		};

		if ( single_arr.length > 0 ) {

			let single_payload_o = { 
				'event' : 'view_item',
				'ecommerce' : {
					'currency' : fpdata.woo.currency,
					'value' : value,
					'items' : single_arr,
				}
			};

			FP.fns.push_to_gtm_dl( 'view_item' , single_payload_o );
		}
	};

	if ( ! ( fp.woo.dont_track_views_after_refresh && fpdata.refreshed ) ){
		track_woo_impress();
		FP.addAction( ['woo_impress'], track_woo_impress );
	}
	
	// TRACK DEFAULT VARIANT VIEW
	// TRACK VARIANT VIEWS

	function woo_variant_view( variant_id ){

		let prod = fpdata.woo.products[variant_id],
			item ={ 
				'item_id' : FP.fns.get_woo_prod_id(prod),
				'item_name': FP.fns.get_woo_prod_name(prod),
				'item_list_name' : 'single',
				'price' : prod.price,
			};

			item = add_brand( item, prod );
			item = add_categories( item, prod );

		let payload_o = {
			'event' : 'view_item',
			'ecommerce' : {
				'currency' : fpdata.woo.currency,
				'value' : prod.price,
				'items' : [item],
			}
		};

		FP.fns.push_to_gtm_dl( 'view_item' , payload_o );
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
				'currency' : fpdata.woo.currency,
			};

			item = add_index(item, prod);
			item = add_list_name(item, prod);
			item = add_brand(item, prod);
			item = add_categories(item, prod);

			items_a.push( item );
		} );

		if ( items_a.length == 0 ) return false;

		let payload_o = {
			'event' : event_name,
			'ecommerce' : {
				'items' : items_a,
				'currency' : fpdata.woo.currency, 
				'value' : event_value, 
			},
		};

		FP.fns.push_to_gtm_dl( event_name , payload_o );
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
			'event' : event_name,
			'ecommerce' : {
				'currency' : fpdata.woo.currency, 
				'value' : cart.value, 
				'items' : items_a,
			},
		};

		if ( cart.coupons && cart.coupons.length > 0 ) payload_o['coupon'] = cart.coupons.join(', ');

		if ( type == 'order' ) {
			payload_o['transaction_id'] = fpdata.woo.order.id;
			payload_o['shipping'] = fpdata.woo.order.shipping;
		}
		
		FP.fns.push_to_gtm_dl( event_name, payload_o );
	}

	// track order
	if ( fp.woo.order_data_ready ) track_cart('order');

	// track the start of checkout (except when the whole page or its part is refreshed)
	if ( ! fpdata.refreshed ) {
		if ( fp.woo.checkout_data_ready ) {
			fp.gtm.woo_checkout_tracked = true;
			track_cart('checkout')
		} else {
			document.addEventListener( 'fupi_woo_checkout_data_ready', ()=>{
				if ( ! fp.gtm.woo_checkout_tracked ) {
					fp.gtm.woo_checkout_tracked = true;
					track_cart('checkout');
				}
			})
		};
	}
}

FP.fns.gotm_standard_events = function(){

	// TRACK OUTBOUND LINKS

	if ( fp.gtm.track_outbound ) {
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && fpdata.clicked.link.is_outbound ) {
				window[fp.gtm.datalayer].push( {
					'event' : 'fp_outboundLinkClick',
					'fp_clickedOutboundLink' : fpdata.clicked.link.href,
					'fp_visitorActivityTime_total' : fpdata.activity.total,
				} )
			}
		})
	}

	// TRACK AFFILIATE LINKS

	if ( fp.gtm.track_affiliate ) {
		FP.addAction( ['click'], function(){
			var name = FP.getTrackedAffiliateLink( fp.gtm.track_affiliate );
			if ( name ) {
				window[fp.gtm.datalayer].push( {
					'event' : 'fp_affiliateLinkClick',
					'fp_clickedAffiliateLink' : name,
					'fp_visitorActivityTime_total' : fpdata.activity.total,
				} );
			}
		} )
	}

	// TRACK CLICKS ON EMAIL & TEL LINKS

	if ( fp.gtm.track_email_tel ) {
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && ( fpdata.clicked.link.is_email || fpdata.clicked.link.is_tel ) ) {
				let contact_type = fpdata.clicked.link.is_email ? 'email' : 'tel';
				window[fp.gtm.datalayer].push( {
					'event' : 'fp_contactLinkClick',
					'fp_clickedContactLink' :  fpdata.clicked.link.href,
					'fp_clickedSafeContactLink' :  fpdata.clicked.link.safe_email || fpdata.clicked.link.safe_tel,
					'fp_clickedContactLinkType' : contact_type + ' click',
					'fp_visitorActivityTime_total' : fpdata.activity.total,
				} )
			}
		});
	}

	// TRACK FILE DOWNLOADS

	if ( fp.gtm.track_file_downl ) {
		FP.addAction( ['click'], function(){
			var filename = FP.getTrackedFilename( fp.gtm.track_file_downl );
			if ( filename ) {
				window[fp.gtm.datalayer].push( {
					'event' : 'fp_fileDownload',
					'fp_downloadedFile' : filename,
					'fp_visitorActivityTime_total' : fpdata.activity.total,
				} )
			}
		})
	}

	// TRACK CLICKS IN ANCHORS

	if ( fp.gtm.track_anchor_clicks ){
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && fpdata.clicked.link.is_anchor ){
				window[fp.gtm.datalayer].push( {
					'event' : 'fp_anchorClick',
					'fp_clickedAnchorLink' : fpdata.clicked.link.href,
					'fp_visitorActivityTime_total' : fpdata.activity.total,
				} )
			}
		})
	}

	// TRACK SCROLLS

	if ( fp.gtm.track_scroll ){
		fp.gtm.track_scroll = FP.formatScrollPoints( fp.gtm.track_scroll );
		FP.addAction( ['scroll', 'active_time_tick'], function(){
			// check if the window was scrolled
			if ( fp.gtm.track_scroll.length > 0 && fpdata.activity.total >= fp.vars.track_scroll_time && fpdata.scrolled.current_px >= fp.vars.track_scroll_min ) {
				var reachedPoint = FP.isScrollTracked( fp.gtm.track_scroll );
				if ( reachedPoint ) {
					// remove from array the scroll points that were already reached
					fp.gtm.track_scroll = fp.gtm.track_scroll.filter( function( point ){ return point > reachedPoint } );
					// track scroll
					window[fp.gtm.datalayer].push( {
						'fp_scrollDepth' : reachedPoint,
						'fp_visitorActivityTime_total' : fpdata.activity.total,
					} )
				}
			}
		} );
	}

	// TRACK VIEWS OF ELEMENTS
	// for performance: waits 250ms for dynamically generated content to finish

	FP.fns.gtm_observe_inters = ( newly_added_els = false ) => {

		let send_el_view_evt = el => {
			window[fp.gtm.datalayer].push( {
				'event' : 'fp_elementView',
				'fp_viewedElement' : el.dataset['gtm_view'],
				'fp_visitorActivityTime_total' : fpdata.activity.total,
			} )
		};
		
		FP.intersectionObserver( newly_added_els, fp.gtm.track_views, 'gtm', send_el_view_evt, true );
	}
	
	if ( fp.gtm.track_views ) setTimeout( ()=>{
		FP.fns.gtm_observe_inters();
		FP.addAction( ['dom_modified'], FP.fns.gtm_observe_inters );
	}, 250 );
	
	
	// TRACK DYNAMIC URL CHANGES (history.state != null)

	if ( fp.gtm.track_dynamic_urls ){
		FP.addAction( ['url_change'], function(){
			window[fp.gtm.datalayer].push( {
				'event' : 'fp_virtualPageview',
				'fp_virtualPageviewURL' : location.host + location.pathname,
				'fp_visitorActivityTime_total' : fpdata.activity.total,
			} )
		})
	}

	// TRACK FORMS

	if ( fp.gtm.track_forms ) {
		FP.addAction( ['form_submit'], function(){
			var name = FP.getSubmittedForm( fp.gtm.track_forms );
			if ( name ){
				window[fp.gtm.datalayer].push( {
					'event' : 'fp_formSubmit',
					'fp_submittedForm' : name,
					'fp_visitorActivityTime_total' : fpdata.activity.total,
				} );
			}
		})
	}

	// TRACK WHEN THE TAB IS IN FOCUS AND LOOSES FOCUS

	if ( fp.gtm.track_focus ) {
		FP.addAction( ['page_in_blur'], function(){
			window[fp.gtm.datalayer].push( {
				'event' : 'windowVisibilityChange',
				'fp_pageInFocus' : fpdata.doc_in_focus,
				'fp_visitorActivityTime_total' : fpdata.activity.total,
			} );
		})
	}
	

	// TRACK SPECIFIC ELEMENTS

	if ( fp.gtm.track_elems ) {
		FP.addAction( ['click'], function(){
			var name  = FP.getClickTarget( fp.gtm.track_elems );
			if ( name ) {
				window[fp.gtm.datalayer].push( {
					'event' : 'fp_elementClick',
					'fp_clickedElement' : name,
					'fp_visitorActivityTime_total' : fpdata.activity.total,
				} )
			}
		})
	}
}


FP.fns.load_gotm_footer = () => {
	FP.fns.gotm_standard_events();
	if ( fp.loaded.includes('woo') ) FP.fns.gotm_woo_events();
};

FP.enqueueFn( 'FP.fns.load_gotm_footer' );