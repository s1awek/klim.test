(function (FP) {

    'use strict';

    var d = document,
        w = window;

    // We set some basic vars
    FP.v = {};
    fp.track_queue = [];
    fpdata.activity = { total : 0, last : 0, current : 0 };
    fpdata.refreshed = false;
    
    if ( document.referrer == document.location.origin + document.location.pathname ) {
        fpdata.refreshed = true;
    } else {
        if ( performance.navigation ? performance.navigation.type === performance.navigation.TYPE_RELOAD : performance.getEntriesByType('navigation')[0].type === 'reload' ) fpdata.refreshed = true; // deprecated method first, the new one later
    }

    // fires function every X ms with an option to fire them ADDITIONALLY on start ("leading") and after events ("trailing")
    FP.throttle = function (func, wait, options) {
        var context, args, result;
        var timeout = null;
        var previous = 0;
        if (!options) options = {};

        var later = function later() {
            previous = options.leading === false ? 0 : Date.now();
            timeout = null;
            result = func.apply(context, args);
            if (!timeout) context = args = null;
        };

        return function () {
            var now = Date.now();
            if (!previous && options.leading === false) previous = now;
            var remaining = wait - (now - previous);
            context = this;
            args = arguments;

            if (remaining <= 0 || remaining > wait) {
                if (timeout) {
                    clearTimeout(timeout);
                    timeout = null;
                }

                previous = now;
                result = func.apply(context, args);
                if (!timeout) context = args = null;
            } else if (!timeout && options.trailing !== false) {
                timeout = setTimeout(later, remaining);
            }

            return result;
        };
    };

    FP.isObjectEmpty = function (obj) {
        return obj.constructor === Object && Object.keys(obj).length === 0;
    };

    FP.isNumeric = function (n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    };

    FP.reduce = function( arr ){
        return arr.reduce( function(accumulator, val){ return val ? accumulator + val : accumulator } );
    };

    FP.remove = el => { if (el) el.parentNode.removeChild(el); };

    FP.findFirst = (e, c) => {
        if ( c === null ) return null;
        if ( !e ) return false;
        c = c || document;
        return c.querySelector(e);
    };

    FP.addAction = function( action_name, fn ){

        let arr = Array.isArray(action_name) ? action_name : [action_name]; // make sure that trigger is in an array

        arr.forEach(function ( name , i) { 
            if ( ! fp.actions[name] ) fp.actions[name] = [];
            fp.actions[name].push( fn );
        });
    }

    FP.doActions = ( action_name, args_o = null, cb ) => {
        if ( fp.actions[action_name] ) fp.actions[action_name].forEach( fn => {
            let return_val = fn( args_o );
            if ( typeof return_val !== 'undefined' ) args_o = return_val;
        } );
        if ( cb ) cb( args_o );
        // TODO: add in this place a function that sends the collected payload to server
    }

    FP.hasActions = action_name => {
        return fp.actions[action_name] && fp.actions[action_name].length > 0;
    };

    function shouldCustomScriptLoadHere( geo ){

        let geo_method = geo[0],
            geo_countries = geo[1];

        if ( fp.geo && geo_method && geo_countries ) { // we check fp.geo to prevent situations when user disabled geo but didn't save new tools settings
        
            if ( fp.ready ) {
                return geo_method == 'incl' ? geo_countries.includes( fpdata.country ) : ! geo_countries.includes( fpdata.country );
            }
            return false;
        }

        return true;
    }

    function shouldScriptLoadHere( id ){

        if ( fp.geo && fp[id].limit_country ) { // we check fp.geo to prevent situations when user disabled geo but didn't save new tools settings
            
            if ( fp.ready ) {

                if ( fp[id].limit_country.method == 'excl' ) {
                    return ! fp[id].limit_country.countries.includes( fpdata.country );
                } else {
                    return fp[id].limit_country.countries.includes( fpdata.country );
                }
            }

            return false;
        }

        return true;
    };

    FP.isAllowedToLoad_basic = function( id, force_load, permissions_a, geo_a ){
    
        // STOP if we don't have all the basic data yet
        if ( ! fp.ready ) return false;

        // STOP if the script has already loaded
        if ( fp.loaded.includes(id) ) return false;

        // START if the load is forced
        if ( force_load ) return true;

        // STOP if the tab is not in focus AND we are NOT on WooCommerce checkout page
        if ( fpdata.page_type !== 'Woo Order Received' ) {
            if ( document.hidden ) return false;
        }

        // STOP tracking an excluded visitor (MUST be after checking FORCE LOAD)
        if ( ! fp.main.track_current_user ) return false;

        // STOP if the script is set NOT to load in the current location
        if ( geo_a && ! shouldCustomScriptLoadHere( geo_a ) ) return false;

        // STOP if any permissions need to be given in this location...
        if ( fp.notice.enabled && fp.notice.mode == 'optin' && ! fpdata.cookies && permissions_a.length > 0 ) return false;

        // STOP if required permissions are not given
        if ( fpdata.cookies && permissions_a.length > 0 ) {
            if ( ! permissions_a.every( permission => fpdata.cookies[permission] ) ) return false;
        }

        return true;
    };

    FP.isAllowedToLoad = function( module_id, permissions_a, required_a, nr, tool_does_not_use_cookies = false ) {

        var id = nr ? module_id + nr : module_id;

        // STOP if we don't have all the basic data yet
        if ( ! fp.ready ) return false;

        // STOP if we have no settings for this tool
        if ( ! fp[id] ) return false;

        // STOP if the script has already loaded
        if ( fp.loaded.includes(id) ) return false;

        // STOP if the required data is missing
        if ( required_a.some( req => ! fp[id][req] ) ) return false;

        // START if the load is forced
        if ( fp[id].force_load ) return true;

        // STOP if the tab is not in focus AND we are NOT on WooCommerce checkout page
        if ( fpdata.page_type !== 'Woo Order Received' ) {
            if ( document.hidden ) return false;
        }

        // STOP tracking an excluded visitor (MUST be after checking FORCE LOAD)
        if ( ! fp.main.track_current_user ) return false;

        // STOP if the script is set NOT to load in the current location
        if ( ! shouldScriptLoadHere( id ) ) return false;

        // START 
        // if the tool doesn't use cookies
        // or if it is set to disregard cookie choices
        if ( tool_does_not_use_cookies || fp[id].disreg_cookies ) return true;

        // STOP if we wait for consent but some permissions need to be given
        if ( fp.notice.enabled && fp.notice.mode == 'optin' && ! fpdata.cookies && permissions_a.length > 0) return false;

        // STOP if user chose their cookie preferences but some required permissions are missing
        if ( fp.notice.enabled && fpdata.cookies && permissions_a.length > 0 ) {
            if ( ! permissions_a.every( permission => fpdata.cookies[permission] ) ) return false;
        }

        return true;
    };

    FP.updateScrollValues = function(){

        fpdata.scrolled = fpdata.scrolled || {};

        fpdata.scrolled.current         = Math.round( d.documentElement.scrollTop / ( d.documentElement.scrollHeight - d.documentElement.clientHeight ) * 100 ) || 0;
        fpdata.scrolled.current_px      = d.documentElement.scrollTop || 0;
        fpdata.scrolled.max             = fpdata.scrolled.max || fpdata.scrolled.current;
        fpdata.scrolled.max_px          = fpdata.scrolled.max_px || fpdata.scrolled.current_px;

        if ( fpdata.scrolled.current > fpdata.scrolled.max ) {
			fpdata.scrolled.max         = fpdata.scrolled.current;
			fpdata.scrolled.max_px      = fpdata.scrolled.current_px;
		}
	}

    FP.startActivityTimer = function () {

        fpdata.activity.last = 0;

        if (!FP.v.activityTimerRunning) {
            FP.v.activityTimerRunning = true;
            FP.v.activityTimer = setInterval(function () {

                fpdata.activity.total++;
                fpdata.activity.current++;
                fpdata.activity.last++;

                FP.doActions( 'active_time_tick' );

                if ( fpdata.activity.last >= 15 ) FP.stopActivityTimer(); // stop timer after 15 secs from last activity
            }, 1000);
        }
    };

    FP.stopActivityTimer = function () {
        if (FP.v.activityTimerRunning) {
            FP.v.activityTimerRunning = false;
            clearInterval(FP.v.activityTimer);
        }
    };

    FP.encode = function (string) {
        return window.btoa(string).split('=').join('')
    }

    FP.detectAddedElement = callback => {

		const observer = new MutationObserver( mutations => {
		  	mutations.forEach( mutation => {
				if ( mutation.type === 'childList' ) {
			  		callback( mutation.addedNodes );
				}
		  	} );
		} );
	  
		observer.observe( document.body, {
			childList: true,
			subtree: true
		} );
	}

    FP.initCondTracking = ( events_settings, cb ) => {
		
		if ( ! ( events_settings && fp.atrig ) ) return;
		if ( ! fp.atrig.actions ) fp.atrig.actions = {};

		events_settings.forEach( e => {
			
			if ( ! fp.atrig.actions[e.atrig_id] ) fp.atrig.actions[e.atrig_id] = [];
			
			fp.atrig.actions[e.atrig_id].push( {
				'func' : cb.bind( this, e ),
				'repeat' : e.repeat
			} );
		});
	}

    /* V8 mods - START */

    // Check if WPFP can use cookies

    function check_if_can_save_wpfp_cookies( first_check = false ) {

        // save previous state
        let prev_check = !! fp.vars.can_save_wpfp_cookies; // "!!" changes undefined into false
        
        // get current state
        fp.vars.can_save_wpfp_cookies = ! fp.notice.enabled || fp.notice.mode == 'notify' || fp.notice.mode == 'hide' || ( fp.notice.mode == 'optout' && ! fpdata.cookies ) || ( fp.notice.mode == 'optin' && fpdata.cookies && fpdata.cookies.stats );

        // check if the state has changed
        fp.vars.consents_changed = first_check ? false : prev_check != fp.vars.can_save_wpfp_cookies;        
    }

    check_if_can_save_wpfp_cookies( true );

    // Get session data from cookies

    function get_session_data_from_cookies(){

        let session_cookie = FP.readCookie('fp_current_session');
        if ( session_cookie ) fpdata.current_session = JSON.parse(session_cookie);

        
    }

    get_session_data_from_cookies();

    

    function create_new_single_session_data(){
        
        fpdata.current_session = {};

        

        FP.setCookie( 'fp_current_session', JSON.stringify(fpdata.current_session), 0, 30 ); // 30 mins
    }

    function update_single_session_data(){
        if ( ! fpdata.current_session ) return;
        
        FP.setCookie( 'fp_current_session', JSON.stringify(fpdata.current_session), 0, 30 ); // 30 mins
    }

    

    // Set or update session data when the page loads
    function set_session_data(){
        
        if ( fp.vars.can_save_wpfp_cookies ) {
            
            fpdata.new_session = ! fpdata.current_session;
            
            ! fpdata.current_session ? create_new_single_session_data() : update_single_session_data();
            

        } else {
            fpdata.new_session = ! fpdata.refreshed && ! window.sessionStorage['fp_old_session'] && ( window.history.length == 1 || ( window.history.length == 2 && ! document.referrer ) );
        }

        fpdata.new_tab = ! fpdata.new_session && ! window.sessionStorage['fp_old_session'];
        window.sessionStorage['fp_old_session'] = true; // removed on a new tab
    };

    set_session_data();

    // CREATE, UPDATE OR REMOVE SESSION DATA
    // triggered after users change cookie preferences or when a page gains focus

    FP.updateSessionData = () => {
        
        check_if_can_save_wpfp_cookies();

        if ( fp.vars.consents_changed ) {
            
            // if we can no longer use session cookies
            if ( ! fp.vars.can_save_wpfp_cookies ) {
                
                // delete everything
                FP.deleteCookie( 'fp_current_session' );
                delete fpdata.current_session;
                
            
            // if we can now use session cookies
            } else {

                // get latest sesion data (in case user agreed to cookies on a different tab)
                get_session_data_from_cookies();

                // set new data if it wasn\'t set before (when a user has just chosen sth in the banner)
                ! fpdata.current_session ? create_new_single_session_data() : update_single_session_data();
                
            };

        } else {
            // get latest sesion data (in case user visited a different tab before opening this one)
            get_session_data_from_cookies();
        }
    }

    /* V8 mods - END */

    FP.isClickTarget = function( selector ){
		return ( ! fpdata.clicked.link && ! fpdata.clicked.middle && fpdata.clicked.element.matches( selector ) ) || ( fpdata.clicked.link && fpdata.clicked.link.element.matches( selector ) );
	}
    
    // the difference between this Fn and isAnchorLinkToCurrentPage is that this one doesn't check if the link actually works
    function isLinkToCurrentPage (link) {
        let target_url_obj = new URL( link.href );
        return target_url_obj.origin + target_url_obj.pathname == location.origin + location.pathname;
    }
	
	// checks if the link is an anchor to an EXISTING element on the same page
	function isAnchorLinkToCurrentPage (link){
        
        let url = new URL( link.href ),
            el = false;
        
        try {
            el = FP.findFirst( url.hash );
        } catch (e) {
            el = false;
        }
        
        return !! el;
    };

	function isOutboundLink (el) {
        return el.href.indexOf(location.host) == -1 && el.href.indexOf('mailto:') != 0 && el.href.indexOf('tel:') != 0 && el.href.indexOf('http') == 0;
    };

	function trackClicks(e, is_middle_click) {

		let redirect_stopped = false;

		if ( e.target.classList.contains('fupi_click_stopped') ) return;
		
		// PREVENT MULTI CLICKS
	    // Do not run this function if a visitor clicks the element multiple times in a short time-span
	    var now = Date.now();
	    if ( ! ( ! FP.v.lastClick || now - FP.v.lastClick > fp.track.dblclck_time ) ) return;
	    FP.v.lastClick = now;
		
		// PREVENT REDIRECTS

		// prevent default if a visitor clicked a link that is to be opened on the current tab
		var link_el = e.target.closest('a[href]');

		if ( link_el && ! is_middle_click && link_el.target != '_blank' && fp.track.link_click_delay  ){
			e.preventDefault(); // prevents redirects
			e.stopPropagation(); // prevents other event listeners from firing
			e.target.classList.add('fupi_click_stopped');
			e.target.style.pointerEvents = 'none';
			redirect_stopped = true;
			if ( fp.main.debug ) console.log('[FP] link-click stopped');
		};

		// GET CLICK DATA

		fpdata.clicked = {
			'element' : e.target,
			'middle' : is_middle_click,
		};

		if ( link_el && link_el.href.length > 1 ) {

			fpdata.clicked.link 				= {};
			fpdata.clicked.link.element			= link_el;
			fpdata.clicked.link.href			= link_el.href;
			fpdata.clicked.link.is_to_current_page = isLinkToCurrentPage(link_el);
			fpdata.clicked.link.target_blank 	= link_el.target == '_blank';

			fpdata.clicked.link.is_outbound 	= isOutboundLink(link_el);
			fpdata.clicked.link.is_anchor 		= isAnchorLinkToCurrentPage(link_el);

            if ( link_el.href.includes('mailto:') ){
                fpdata.clicked.link.is_email = true;
                fpdata.clicked.link.safe_email = link_el.href.replace('mailto:', '').split('@')[0];
            }

            if ( link_el.href.includes('tel:') ){
                fpdata.clicked.link.is_tel = true;
                fpdata.clicked.link.safe_tel = link_el.href.replace('tel:','').slice(-5);
            }
		}

		// RUN QUEUED FNs

		FP.doActions( 
			'click', 
			false, // no slug
			() => { // cb after all functions are run
				if ( fpdata.clicked.element.classList.contains('fupi_click_stopped') && typeof fpdata.clicked.element.click == 'function' ) {
					fpdata.clicked.element.click();
					fpdata.clicked.element.classList.remove('fupi_click_stopped');
					e.target.style.pointerEvents = '';
					if ( fp.main.debug ) console.log('[FP] Initiated click event after all FP functions');
				}
			}
		);
    };

    function getAttributesFromStr(str){

        let clean_str = str.replaceAll( "\'", "'").replaceAll('\"', '"'),
            parts = clean_str.trim().split(' '), // spaces occur in unexpected places!
			ok_parts = parts.filter( v => v ), // remove empty values
			ret_val = {};

		for ( var i = 0; i < ok_parts.length; i++ ) {

			let part = ok_parts[i],
				eq_index = part.indexOf('='),
				last_char = part[part.length-1];

			// if we have an equal sign
			// (this means that the value before the equal sign is the key and the one after is the value)
			if ( eq_index != -1 ){

				// we check if the last element of the current part is an apostrophe. If it isn\'t then it means we split the value in two or more pieces and need to join it with the next string parts
				while ( ! ( last_char == '"' || last_char == "'" ) && i+1 <= ok_parts.length ) {
					part += ' ' + ok_parts[i+1];
					last_char = part[part.length-1];
					i++;
				}

				// if we have the key and the whole value
				if ( last_char == '"' || last_char == "'" ) {

					// split on the first occurance of "=" sign
					let key_val = part.split(/=(.*)/s); 
                    
                    // remove empties
                    key_val = key_val.filter( v => v );
                    
                    // remove apostrophes
                    if ( key_val[1].includes('"') ){
                        key_val[1] = key_val[1].replaceAll('"', '');
                    };

                    if ( key_val[1].includes("'") ){
                        key_val[1] = key_val[1].replaceAll("'", '');
                    } ; 

                    // save in an array
					ret_val[key_val[0]] = key_val[1];
				}

			// if we don't have an equal sign this is a single value
			} else {
				ret_val[part] = true;
			}
		};

		return ret_val;
    };

    // Add blocked scripts to HTML
    function addScriptsToDOM() {

        fp.blocked_scripts.forEach( scr_data => {

            let url = scr_data[0],
                attr_s = scr_data[1],
                scr_id = scr_data[2],
                force_load = scr_data[3],
                permissions_a = scr_data[4],
                geo_a = scr_data[5],
                type = scr_data[6] || false;

			if ( FP.isAllowedToLoad_basic( scr_id, force_load, permissions_a, geo_a ) ) {
			    if ( ! url && attr_s == 'empty' ){
                    FP.loadScript( scr_id );
                } else {
                    let attr_o = getAttributesFromStr(attr_s);
                    FP.getScript( url, ()=>{
                        fp.loaded.push( scr_id );
                    }, attr_o, type );        
                }
            }
		} )
	}

    // FIRE TAGS WHEN THE DOCUMENT GETS AND LOSES FOCUS

    function doPageShowActions(){

        if ( ! fp.vars.page_show_tracked ){

            fp.vars.page_show_tracked = true;
            fp.vars.page_hide_tracked = false;

            fpdata.doc_in_focus = true;
            
            FP.startActivityTimer();
            
            var cookies = FP.readCookie('fp_cookie');

            if ( cookies ) {
                if ( fpdata.cookies && cookies == JSON.stringify( fpdata.cookies ) ) return;
                cookies = JSON.parse(cookies);
                fpdata.cookies = cookies;
                FP.updateConsents();
            } else {
                fpdata.cookies = false;
            }
            
            FP.updateSessionData();

            FP.sendEvt( 'fp_load_scripts' );

            if ( fp.actions.page_in_focus ) FP.doActions('page_in_focus');
        }
    }

    function doPageHideActions(){
        if ( ! fp.vars.page_hide_tracked ) {
            fp.vars.page_hide_tracked = true;
            fp.vars.page_show_tracked = false;

            fpdata.doc_in_focus = false;
            FP.stopActivityTimer();
            if ( fp.actions.page_in_blur ) FP.doActions('page_in_blur');
            fpdata.activity.current = 0;
        }
    }

    // ON DYNAMIC URL CHANGES
    // URL is set every second with setInterval() and not "active_time_tick", to make the check even when the page is not in focus

	let currentUrl = location.href;
	  
	setInterval( () => { 
		if ( location.href !== currentUrl ) {
			currentUrl = location.href;
			FP.doActions( 'url_change' );
		}
	}, 1000);

    // pagehide/show fires on some events but is cross-browser
    // !! it fires BEFORE visibilitychanges
    // !! when the pageshow event fires the document obj doesn't have updated cookies or localStarage yet, thus we don't use it for "show"
    window.addEventListener( "pagehide", e => { doPageHideActions() }, false );
    // window.addEventListener( "pageshow", e => { doPageShowActions('pageshow') }, false ); visibilitychange is universal

    // beforeunload doesn't work with prev/next page buttons and has problems on mobiles
    window.addEventListener( "beforeunload", e => { doPageHideActions() }, false );

    // visibilitychanges fires on all events but has problems on mobiles and in safari
    // !! fires AFTER pagehide/show
    // !! when the visibilitychange event fires for "show" the document obj already has updated cookies and localStarage
    document.addEventListener( 'visibilitychange', function () {
        document.hidden ? doPageHideActions() :  doPageShowActions('visibilitychange');
        //if ( fp.page_visibility ) FP.doActions( 'page_visibility' );
    }, false);

    // load custom/extra scripts to DOM
    document.addEventListener( 'fp_load_scripts', addScriptsToDOM );

    function formSubmitEvents( {target} ){
        fpdata.submitted_form = { 'element' : target };
		FP.doActions( 'form_submit' );
    }

    // ON DOM LOADED
	document.addEventListener( 'DOMContentLoaded', ()=>{

        // fp.vars.dom_loaded_time = Date.now();
        addScriptsToDOM();

        // init actions waiting for DOM loaded
        setTimeout ( ()=>{ FP.doActions( ['dom_loaded'] ) }, 250 ); // we must add a small delay to make sure that all triggers are hooked

        // start listening to form submits
        setTimeout( ()=>{ document.addEventListener('submit', formSubmitEvents ) }, fp.track.formsubm_trackdelay ? fp.track.formsubm_trackdelay * 1000 : 1000 );
        
        // start listening to DOM modifications
		if ( fp.track.use_mutation_observer ) FP.detectAddedElement( addedNodes => {
            let els = FP.nl2Arr( addedNodes );
            if ( els.length > 0 ) FP.doActions( ['dom_modified'], els );
        } );
	});


    // ON MOUSE MOVE FOR USER ACTVITY TRACKER
    document.addEventListener( 'mousemove', FP.throttle( FP.startActivityTimer, 500 ) ); // This starts the user's activity timer

    if ( ! document.hidden) {
        FP.startActivityTimer();
        fpdata.doc_in_focus = true;
        fp.vars.page_show_tracked = true;
        fp.vars.page_hide_tracked = false;
    };

    // ON LEFT CLICK
	window.addEventListener( 'click', function (e) {
		if ( fp.actions.click ) trackClicks(e, false);
	}, true);

	// ON MIDDLE CLICK
	window.addEventListener( 'mouseup', function(e) {
		if ( fp.actions.click && e.button == 1) trackClicks(e, true);
	});

})(FP);
