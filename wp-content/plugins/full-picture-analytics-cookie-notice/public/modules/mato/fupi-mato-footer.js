FP.fns.mato_remmeber_cart_changes = ( data, should_remove, return_val = true ) => {

	// get old cart data
	var cart = FP.readCookie('fp_matomo_cart__tmp'),
		removed = [];

	cart = cart ? JSON.parse( cart ) : {};

	// update old cart data with data of newly added products
	data.products.forEach( prod_a => {

		let prod = prod_a[0],
			prod_id = FP.fns.get_woo_prod_id(prod);
			qty = prod_a[1] || prod.qty;

		// if this product is already in cart
		if ( cart[prod_id] ) {

			// fix qty count (can sometimes be negative)
			if ( cart[prod_id]['qty'] < 0 ) cart[prod_id]['qty'] = 0;

			if ( ! should_remove ) {
				// increase quantity
				cart[prod_id]['qty'] += qty || 1;
			} else {
				// remove the whole product if the quantity does not exist
				if ( ! qty || cart[prod_id]['qty'] == 0 ) { 
					removed.push( prod_id );
					delete cart[prod_id]
				// otherwise decrease quantity
				} else {
					// delete product if we try to remove more then there is in cart
					if ( cart[prod_id]['qty'] <= qty ) {
						removed.push( prod_id );
						delete cart[prod_id]
					} else {
						cart[prod_id]['qty'] -= qty;
					}
				}
			}

		// if it is a new product
		} else {
			
			let prod_cat = prod.categories && prod.categories.length > 0 ? prod.categories.slice(0,5) : [];
			
			cart[prod_id] = { 
				'name' : FP.fns.get_woo_prod_name(prod),
				'cat' : prod_cat,
				'price' : prod.price,
				'qty' : qty
			};
		}
	} );

	// save updated cart
	FP.setCookie('fp_matomo_cart__tmp', JSON.stringify(cart) );

	if ( return_val ) return [cart, removed];

	// Remove cart cookie when an order is made

	if ( fp.woo.order_data_ready ) FP.deleteCookie('fp_matomo_cart__tmp');
}

FP.fns.mato_woo_update_cart = () => {
	
	FP.addAction( ['woo_add_to_cart'], data =>{
		if ( ! fp.loaded.includes('mato') ) FP.fns.mato_remmeber_cart_changes( data, false, false );
	} );

	FP.addAction( ['woo_remove_from_cart'], data =>{
		if ( ! fp.loaded.includes('mato') ) FP.fns.mato_remmeber_cart_changes( data, true, false );
	} );
}

FP.fns.mato_woo_events = () => {

	// HELPERS

	function add_products_to_cart( cart ){
		
		let cart_value = 0;

		for (const id in cart) {
			if (Object.hasOwnProperty.call(cart, id)) {
				cart_value += cart[id]['qty'] * cart[id]['price'];
				_paq.push( [ 'addEcommerceItem', id, cart[id]['name'], cart[id]['cat'], cart[id]['price'], cart[id]['qty'] ] );
				if ( fp.main.debug ) console.log( '[FP] Matomo "addEcommerceItem" event: ', [ id, cart[id]['name'], cart[id]['cat'], cart[id]['price'], cart[id]['qty'] ] );
			}
		};

		return Math.round( cart_value * 100 ) / 100;
	}

	function remove_products_from_cart( removed ) {
		removed.forEach( id => {
			_paq.push( [ 'removeEcommerceItem', id ] );
			if ( fp.main.debug ) console.log( '[FP] Matomo "removeEcommerceItem" by id:', id );
		});
	};

	function send_cart_update( cart_value ){
		_paq.push(['trackEcommerceCartUpdate', cart_value]); 
		if ( fp.main.debug ) console.log( '[FP] Matomo "trackEcommerceCartUpdate" event: ', cart_value );
	}
	
	// TRACK IMPRESSIONS

	function track_woo_impress() {
		
		if ( ! fpdata.woo.lists.single ) return;
		if ( ! fp.woo.mato ) fp.woo.mato = { 'single' : [] };
		
		let item_ids = fpdata.woo.lists.single.filter( id => ! fp.woo.mato.single.includes(id) ); // track only items that were not tracked before

		if ( item_ids.length == 0 ) return;
		
		let prod_id = item_ids[0],
			prod = fpdata.woo.products[prod_id],
			prod_tracked_id = FP.fns.get_woo_prod_id(prod),
			prod_name = FP.fns.get_woo_prod_name(prod),
			prod_cat = prod.categories && prod.categories.length > 0 ? prod.categories.slice(0,5) : [];
			prod_price = prod.price;

		_paq.push( ['setEcommerceView',
			prod_tracked_id,
			prod_name,
			prod_cat,
			prod_price
		] );

		_paq.push(['trackPageView']);
		if ( fp.main.debug ) console.log( '[FP] Matomo "setEcommerceView" event: ', [ prod_tracked_id, prod_name, prod_cat, prod_price] );
	};

	if ( ! ( fp.woo.dont_track_views_after_refresh && fpdata.refreshed ) ){
		track_woo_impress();
		FP.addAction( ['woo_impress'], track_woo_impress );
	}

	// TRACK DEFAULT VARIANT VIEW
	// TRACK VARIANT VIEWS

	function woo_variant_view( variant_id ){
		
		let prod = fpdata.woo.products[variant_id],
		 	prod_id = FP.fns.get_woo_prod_id(prod),
			prod_name = FP.fns.get_woo_prod_name(prod),
			prod_cat = prod.categories && prod.categories.length > 0 ? prod.categories.slice(0,5) : [];

		_paq.push(['setEcommerceView',
			prod_id,
			prod_name, 
			prod_cat,
			prod.price
		]);
		
		if ( fp.main.debug ) console.log('[FP] Matomo "setEcommerceView" event: ', [prod_id, prod_name, prod_cat, prod.price]);
	}

	FP.addAction( ['woo_variant_view'], woo_variant_view );
	FP.addAction( ['woo_def_variant_view'], woo_variant_view );

	// TRACK ADD TO CART

	function track_cart_changes( data, should_remove ){

		let [cart, removed] = FP.fns.mato_remmeber_cart_changes( data, should_remove ),
			cart_value = add_products_to_cart( cart );
		
		if ( removed.length > 0 ) remove_products_from_cart( removed );

		send_cart_update( cart_value );
	};

	FP.addAction( ['woo_add_to_cart'], data =>{
		_paq.push(['trackEvent', 'Ecommerce', 'Add to cart', 'Add to cart']);
		track_cart_changes( data, false );
	} );

	FP.addAction( ['woo_remove_from_cart'], data =>{
		_paq.push(['trackEvent', 'Ecommerce', 'Remove from cart', 'Remove from cart']);
		track_cart_changes( data, true );
	} );

	// TRACK CART CONTENTS IF USER ADDED SOMETHING TO CART BEFORE AGREEING TO COOKIES

	function add_old_products(){
		let cart = FP.readCookie('fp_matomo_cart__tmp');
		if ( ! cart ) return;		
		cart = JSON.parse( cart );
		let cart_value = add_products_to_cart( cart );
		send_cart_update( cart_value );
	};

	if ( fp.mato.user_consent_granted ) add_old_products();

	// TRACK PURCHASE

	function track_purchase(){

		let items_type = fp.woo.variable_tracking_method == 'track_parents' ? 'joined_items' : 'items',
			order = fpdata.woo.order;

		for ( const id in order[items_type] ) {

			let prod = order[items_type][id],			
				prod_tracked_id = FP.fns.get_woo_prod_id(prod),
				prod_name = FP.fns.get_woo_prod_name(prod),
				prod_cat = prod.categories && prod.categories.length > 0 ? prod.categories.slice(0,5) : [];
				prod_price = prod.price;

			_paq.push( [ 'addEcommerceItem',
				prod_tracked_id, 
				prod_name,
				prod_cat,	
				prod_price,
				prod.qty
			] );

			if ( fp.main.debug ) console.log( '[FP] Matomo "addEcommerceItem" event: ', [ prod_tracked_id, prod_name, prod_cat, prod_price, prod.qty ] );
		}

		let track_real_order_id = false;

		if ( ! fp.notice.enabled ) {
			if ( ! fp.mato.no_cookies ) {
				track_real_order_id = true;
			}
		} else {
			if ( ! ( fp.notice.mode == 'optin' || fp.notice.mode == 'optout' ) ) {
				track_real_order_id = true;
			} else {
				if ( fpdata.cookies.stats ) track_real_order_id = true;
			}
		}

		let order_id = track_real_order_id ? order.id : FP.getRandomStr(),
			order_payload = [ 'trackEcommerceOrder', 
				order_id,
				order.value,
				order.subtotal,
				fp.woo.incl_tax_in_price ? order.tax : 0,
				fp.woo.incl_shipping_in_total ? order.shipping : 0,
				order.discount
			];
		
		_paq.push( order_payload );

		_paq.push(['trackEvent', 'Ecommerce', 'Purchase', 'Purchase']);
		
		if ( fp.main.debug ) console.log( '[FP] Matomo "trackEcommerceOrder" event: ', order_payload );

		FP.deleteCookie('fp_matomo_cart__tmp');
	}

	// track order
	if ( fp.woo.order_data_ready ) track_purchase();
}

FP.fns.mato_standard_events = () => {

	// TRACK SCROLL

	if ( fp.mato.track_scroll ) {
		fp.mato.track_scroll = FP.formatScrollPoints( fp.mato.track_scroll );
		FP.addAction( ['scroll', 'active_time_tick'], function(){
			if (
				fp.mato.track_scroll.length > 0 &&
				fpdata.activity.total >= fp.track.track_scroll_time &&
				fpdata.scrolled.current_px >= fp.track.track_scroll_min
			) {
				var reachedPoint = FP.isScrollTracked( fp.mato.track_scroll );
				if ( reachedPoint ) {
					// remove from array the scroll points that were already reached
					fp.mato.track_scroll = fp.mato.track_scroll.filter( function( point ){ return point > reachedPoint } );
					_paq.push(['trackEvent', 'Page Scroll Depth', 'Scroll', 'Scrolled to ' + reachedPoint, reachedPoint]);
					if ( fp.main.debug ) console.log('[FP] Matomo event: page scrolled to ', reachedPoint );
				}
			}
		} );
	}

	// TRACK VIEWS OF ELEMENTS
	// for performance: waits 250ms for dynamically generated content to finish

	FP.fns.mato_observe_inters = ( newly_added_els = false ) => {

		let send_el_view_evt = el => {

			if ( ! el.dataset.mato_view ) return;

			_paq.push(['trackEvent', 'Page Element View', 'View', el.dataset.mato_view]);
			if ( fp.main.debug ) console.log('[FP] Matomo event: viewed page element ', el.dataset.mato_view );
		};
		
		FP.intersectionObserver( newly_added_els, fp.mato.track_views, 'mato', send_el_view_evt, true);
	}
	
	if ( fp.mato.track_views ) setTimeout( ()=>{
		FP.addAction( ['dom_modified', 'dom_loaded'], FP.fns.mato_observe_inters );
	}, 250 );

	// TRACK AFFILIATE LINKS

	if ( fp.mato.track_affiliate ) {
		FP.addAction( ['click'], function(){
			var trackedAffLink = FP.getTrackedAffiliateLink( fp.mato.track_affiliate );
			if ( trackedAffLink ) {
				_paq.push(['trackEvent', 'Affiliate Link Click', 'Click', trackedAffLink]);
				if ( fp.main.debug ) console.log('[FP] Matomo event: clicked affiliate link ', trackedAffLink );
			}
		} );
	}

	// TRACK CLICKS ON EMAIL AND TEL LINKS

	if ( fp.mato.track_email_tel ) {
		FP.addAction( ['click'], function(){
			if ( fpdata.clicked.link && ( fpdata.clicked.link.is_email || fpdata.clicked.link.is_tel ) ) {
				
				var action = fpdata.clicked.link.is_email ? 'Email Link Click' : 'Tel Link Click',
					contact =  fpdata.clicked.link.safe_email || fpdata.clicked.link.safe_tel;
					
				_paq.push(['trackEvent', 'Contact Link Click', action, contact ]);
				if ( fp.main.debug ) console.log('[FP] Matomo event: ' +  action + ': ' + contact );
			}
		} );
	}

	// TRACK FORM SUBMITS

	if ( fp.mato.track_forms ) {
		FP.addAction( ['form_submit'], function(){
			var submittedForm = FP.getSubmittedForm( fp.mato.track_forms );
			if ( submittedForm ){
				_paq.push(['trackEvent', 'Form Submission', 'Form Submission', submittedForm]);
				if ( fp.main.debug ) console.log('[FP] Matomo event: Submitted form ' + submittedForm );
			}
		})
	}

	// TRACK CLICKS ON PAGE ELEMENTS

	if ( fp.mato.track_elems ) {
		FP.addAction( ['click'], function(){
			var trackedElName  = FP.getClickTarget( fp.mato.track_elems );
			if ( trackedElName ) {
				_paq.push(['trackEvent', 'Page Click', 'Click', trackedElName]);
				if ( fp.main.debug ) console.log('[FP] Matomo event: clicked ' + trackedElName );
			}
		} )
	}
}

// this saves cart before user agrees to cookies
if ( fp.loaded.includes('woo') ) FP.fns.mato_woo_update_cart();

FP.fns.load_mato_footer = function() {
	FP.fns.mato_standard_events();
	if ( fp.loaded.includes('woo') ) FP.fns.mato_woo_events();
}

// INIT FOOTER SCRIPTS
FP.enqueueFn( 'FP.fns.load_mato_footer' );

