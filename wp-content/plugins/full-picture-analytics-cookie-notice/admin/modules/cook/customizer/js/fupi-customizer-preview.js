( function( $ ) {

	if ( typeof fp == 'undefined' ) return;

    // Helpers

    // Binds a callback to a WP Customizer setting change
    const bind = ( id, cb ) => wp.customize( id, val => val.bind( cb ) );
    // Sets a CSS variable on the body element
    const setCss = ( prop, val ) => document.body.style.setProperty( prop, val );
    
	// Do not wait for FP helpers
    const findID = id => document.getElementById(id);
    const findfirst = sel => document.querySelector(sel);
    const findAll = sel => document.querySelectorAll(sel);
    
	/*
	*	MAIN MECHANICS
    *   Handles banner type switching (opt-in/out vs notify), visibility toggling, and initialization.
	*/
	let toggler = findID('fupi_notice_toggler');
    if(toggler) toggler.setAttribute("style","");

    // Shows the notification headline if it has content
	function show_notif_headline(){
		let h = findID('fupi_main_headline');
		if ( h && h.textContent.length > 0 ) h.classList.remove('fupi_hidden');
	};
		
    // Enables the correct notice type based on preview mode (notify or opt-in/out)
	function enable_correct_notice_type(){ 
		if ( window.fupi_init ) return;
		window.fupi_init = true;

		remember_state_of_hide_els();
		remember_state_of_show_els();
		
		window.fupi_preview_mode == 'notify' ? enable_notif_preview() : enable_optin_preview();
	}
	
    // Helper to get setting values, handling empty strings and comma-separated lists
    function get_states( latest, setting ) {
        if ( latest === '' ) latest = ' ';
        if ( latest ) return latest.split(',').map( v => v.trim() );
        return wp.customize.settings.values[setting];
    }

    // Stores the visibility state of elements that can be hidden
	function remember_state_of_hide_els( latest ){
		let states = get_states( latest, 'fupi_cookie_notice[hide]' );
		
		window.fupi_hide_toggle = [
            [ 'fupi_decline_cookies_btn', 'decline_btn' ],
            [ 'fupi_cookie_settings_btn', 'settings_btn' ],
            [ 'fupi_stats_section', 'stats' ],
            [ 'fupi_market_section', 'market' ],
            [ 'fupi_pers_section', 'pers' ]
        ].map( ([id, key]) => [ id, states.includes(key) ] );
	}

    // Stores the visibility state of elements that can be shown
	function remember_state_of_show_els( latest ){
		let states = get_states( latest, 'fupi_cookie_notice[show]' );
		
		window.fupi_show_toggles = [
            [ '.fupi_poweredBy', 'powered_by' ],
            [ '#fupi_stats_only_btn', 'stats_only_btn' ]
        ].map( ([sel, key]) => [ sel, states.includes(key) ] );
	}

    // Toggles visibility of elements based on stored states
	function toggle_els( toggles, is_show ){
		if ( toggles ) {
			toggles.forEach( ([sel, state]) => {
                // Determine display property: block if condition met, none otherwise
                let display = (is_show ? state : !state) ? 'block' : 'none';
                if(is_show) findAll( sel ).forEach( el => el.style.display = display );
                else { let el = findID( sel ); if(el) el.style.display = display; }
			});
		}
	}

    // Wrappers for toggling hidden and shown elements
    const toggle_hide_els = () => toggle_els( window.fupi_hide_toggle, false );
    const toggle_show_els = () => toggle_els( window.fupi_show_toggles, true );

	// Transforms the banner into a notification style (infobox)
	function enable_notif_preview(){

		findID('fupi_cookie_notice').classList.add('fupi_notice_infobox');
		findfirst('html').classList.add('fupi_infobox');
		document.body.style.overflowY = 'auto';

		let panel_welcome = findID('fupi_welcome_panel'),
			panel_settings = findID('fupi_settings_panel');

        // Hide settings panel, show welcome panel
		panel_settings.classList.remove( 'fupi_fadeInUp', 'fupi_animated' );
		panel_settings.classList.add( 'fupi_fadeOutDown' );

		panel_welcome.classList.remove( 'fupi_fadeOutDown', 'fupi_hidden' );
		panel_welcome.classList.add( 'fupi_animated', 'fupi_fadeInUp' );

        // Hide specific buttons and sections not needed for notification mode
		findID('fupi_decline_cookies_btn').style.display = "none";
		
		[ 'fupi_cookie_settings_btn', 'fupi_stats_section', 'fupi_market_section', 'fupi_pers_section', 'fupi_stats_only_btn' ]
            .forEach( el => findID(el).style.display="none" );

        // Show "Powered By" if enabled
		if ( window.fupi_show_toggles && window.fupi_show_toggles.some( arr => arr[0] == '.fupi_poweredBy' && arr[1] === true ) ) 
            findAll( '.fupi_poweredBy' ).forEach(el => el.style.display="block" );
	}

    // Reverts banner to standard opt-in/out preview
	function enable_optin_preview(){
		findID('fupi_cookie_notice').classList.remove('fupi_notice_infobox');
		findfirst('html').classList.remove('fupi_infobox');
		if ( findfirst('html').classList.contains('fupi_scroll_lock') ) document.body.style.overflowY = 'hidden';
		toggle_hide_els();
		toggle_show_els();
	}

    // Parses text for {{text|url}} pattern and converts to HTML links
	function fupi_add_link_to_notif(text){
		if ( !text.includes('{{') ) return text;
        return text.replace(/\{\{(.*?)\}\}/g, (match, inner) => {
            let [txt, url] = inner.split('|');
            return `<a href="${url || 'https://example.com'}">${txt}</a>`;
        });
	}

    // Updates element text content, optionally adding links
	function fupi_fill_with_text(el_id, text_id, new_text, add_link = false) {
		var $el = $( el_id );
		if ( $el.length ){
            let txt = new_text.length ? new_text : fupi_default_texts[text_id];
            if(add_link) txt = fupi_add_link_to_notif(txt);
            $el.html(txt);
		}
	}

    // Pre-selects consent switches based on settings
	function preselect_switches( preselected ){
        ['stats', 'market', 'pers'].forEach( type => {
            let checked = findfirst(`#fupi_${type === 'market' ? 'marketing' : type}_agree:checked`),
                enable = preselected.includes(type);
            // Click switch if current state doesn't match desired state
            if ( ( enable && ! checked ) || ( ! enable && checked ) ) findfirst(`#fupi_${type}_section .fupi_switch`).click();
        });
	}

	// SHOW / HIDE BANNER OR TOGGLER WHEN THE SETTINGS SECTION IS OPENED / CLOSED

	function show_notice_banner(){
		let $notice_els = $('#fupi_cookie_notice, #fupi_welcome_panel'),
			html_el = document.getElementsByTagName( 'html' )[0];
		
		findID('fupi_cookie_notice').setAttribute("style","");
		$notice_els.removeClass( 'fupi_fadeOutDown fupi_hidden' ).addClass( 'fupi_fadeInUp fupi_animated' );

        // Apply blur effect if enabled
		if ( html_el.classList.contains('fupi_blur_added') || ! html_el.classList.contains('fupi_blur_removed') && fp.notice.blur_page ) {
			html_el.classList.remove('fupi_blur_out');
			html_el.classList.add('fupi_blur');
		}

        // Apply scroll lock if enabled
		if ( html_el.classList.contains('fupi_scroll_removed') || ! html_el.classList.contains('fupi_scroll_removed') && fp.notice.scroll_lock ) {
			document.body.style.overflowY = 'hidden';
			html_el.classList.add('fupi_scroll_lock');
		}
	}

	function hide_notice_banner(){
		let $notice_els = $('#fupi_cookie_notice, .fupi_panel.fupi_animated'),
			html_el = document.getElementsByTagName( 'html' )[0];
		
		$notice_els.removeClass( 'fupi_fadeInUp' ).addClass( 'fupi_fadeOutDown' );

        // Remove blur effect
		if ( html_el.classList.contains('fupi_blur') ){
			html_el.classList.remove('fupi_blur');
			html_el.classList.add('fupi_blur_out');
		}

        // Remove scroll lock
		if ( html_el.classList.contains('fupi_scroll_lock') ) {
			html_el.classList.remove('fupi_scroll_lock');
			document.body.style.overflowY = 'auto';
		}
	}

    // Toggles visibility of the floating toggler button
    function toggle_toggler_vis( show ) {
        let toggler = findID('fupi_notice_toggler');
		if ( toggler && toggler.classList.contains('fupi_active') ) {
            toggler.classList.toggle('fupi_fadeInUp', show);
            toggler.classList.toggle('fupi_fadeOutDown', !show);
		}
    }

	// INIT When the preview finishes loading
    wp.customize.bind( 'preview-ready', () => {
    	
		// SHOW/HIDE THE BANNER IN THE PREVIEW SECTION
		wp.customize.preview.bind( 'fupi_open_notice', function( expand ) {
			window.fupi_preview_mode = window.fupi_preview_mode || wp.customize.settings.values['fupi_cookie_notice[active_preview]'];
			enable_correct_notice_type(); 
			show_notif_headline();
			preselect_switches( wp.customize.settings.values['fupi_cookie_notice[switches_on]'] );

            toggle_toggler_vis(!expand);
            expand ? show_notice_banner() : hide_notice_banner();
    	} );

		// SHOW/HIDE TOGGLER IN THE PREVIEW SECTION
		wp.customize.preview.bind( 'fupi_open_toggler', function( expand ) {
            toggle_toggler_vis(expand);
            expand ? hide_notice_banner() : show_notice_banner();
    	} );
    } );

	// TOGGLER SECTION
	bind( 'fupi_cookie_notice[enable_toggle_btn]', enabled => {
        let t = findID('fupi_notice_toggler');
        if(t) t.classList.toggle('fupi_active', enabled);
        if(t) t.classList.toggle('fupi_animated', enabled);
	} );

	/*
	*	LIVE BANNER MODIFICATIONS
    *   Real-time updates for banner appearance settings
	*/
	
	bind( 'fupi_cookie_notice[active_preview]', newval => {
        window.fupi_preview_mode = newval;
        newval == 'notify' ? enable_notif_preview() : enable_optin_preview();
	} );

	bind( 'fupi_cookie_notice[position]', v => $( '#fupi_cookie_notice' ).attr( 'data-position', v) );

	bind( 'fupi_cookie_notice[position_inform]', newval => {
        if ( newval == 'notify' )  findID('fupi_cookie_notice').classList.add('fupi_notice_infobox');
        $( '#fupi_cookie_notice' ).attr( 'data-position_inform', newval);
	} );

	bind( 'fupi_cookie_notice[overlay]', newval => $( '#fupi_cookie_notice' ).toggleClass('fupi_notice_gradient_overlay', newval) );

	bind( 'fupi_cookie_notice[scroll_lock]', newval => {
        let html = document.getElementsByTagName( 'html' )[0];
        document.body.style.overflowY = newval ? 'hidden' : 'auto';
        html.classList.toggle('fupi_scroll_removed', newval);
        html.classList.toggle('fupi_scroll_lock', newval);
	} );

	bind( 'fupi_cookie_notice[blur_page]', newval => {
        let html = document.getElementsByTagName( 'html' )[0];
        if ( newval ) {
            html.classList.remove('fupi_blur_removed', 'fupi_blur_out');
            html.classList.add('fupi_blur_added', 'fupi_blur');
        } else {
            setTimeout( () => html.classList.remove('fupi_blur_added', 'fupi_blur'), 300 );
            setTimeout( () => html.classList.add('fupi_blur_removed', 'fupi_blur_out'), 300 );
        }
	} );

	bind( 'fupi_cookie_notice[btn_config]', v => $( '#fupi_cookie_notice' ).attr( 'data-btn_config', v) );
	bind( 'fupi_cookie_notice[paddings]', v => $( '#fupi_cookie_notice' ).attr( 'data-paddings', v) );
	bind( 'fupi_cookie_notice_btns_gaps', v => $( '#fupi_cookie_notice' ).attr( 'data-btn_gaps', v) );

	bind( 'fupi_cookie_notice[hide]', v => { remember_state_of_hide_els(v); toggle_hide_els(); } );
	bind( 'fupi_cookie_notice[show]', v => { remember_state_of_show_els(v); toggle_show_els(); } );
	bind( 'fupi_cookie_notice[switches_on]', v => preselect_switches( v ) );

    // Helper to bind class updates to elements
    const bindClass = (id, sel) => bind(id, v => $(sel).each(function(){ $(this).attr('class', $(this).attr('data-classes') + ' ' + v ); }));
	bindClass( 'fupi_cookie_notice[btn_class]', '.fupi_button' );
	bindClass( 'fupi_cookie_notice[cta_class]', '.fupi_cta' );

	bind( 'fupi_cookie_notice_size', size => {
        $( '#fupi_cookie_notice' ).removeClass('fupi_notice_btn_default fupi_notice_btn_small fupi_notice_btn_medium fupi_notice_btn_large')
            .addClass( 'fupi_notice_btn_' + size );
	} );

    // Helper to bind CSS variable updates
    const bindCssVar = (id, name, def = 0, unit = 'px') => bind(id, v => setCss(name, (v || def) + unit));
	bindCssVar( 'fupi_notice_popup_width', '--fupi-notice-popup-panel-max-width', 0 );
	bindCssVar( 'fupi_notice_round_corners', '--fupi-notice-panel-round-corners', 16 );
	bindCssVar( 'fupi_notice_btn_round_corners', '--fupi-notice-btn-round-corners', 8 );

    // Helper to bind color updates to CSS variables
    const bindColor = (id, name) => bind(id, v => setCss(name, v));
	bindColor( 'fupi_notice_bg_color', '--fupi-notice-panel-bg-color' );
	bindColor( 'fupi_notice_h_color', '--fupi-notice-h-color' );
	bindColor( 'fupi_notice_text_color', '--fupi-notice-txt-color' );
	bindColor( 'fupi_notice_btn_color', '--fupi-notice-btn-bg-color' );
	bindColor( 'fupi_notice_btn_color_hover', '--fupi-notice-btn-bg-color-hover' );
	bindColor( 'fupi_notice_btn_txt_color', '--fupi-notice-btn-text-color' );
	bindColor( 'fupi_notice_btn_txt_color_hover', '--fupi-notice-btn-text-color-hover' );
	bindColor( 'fupi_notice_cta_color', '--fupi-notice-cta-bg-color' );
	bindColor( 'fupi_notice_cta_color_hover', '--fupi-notice-cta-bg-color-hover' );
	bindColor( 'fupi_notice_cta_txt_color', '--fupi-notice-cta-txt-color' );
	bindColor( 'fupi_notice_cta_txt_color_hover', '--fupi-notice-cta-txt-color-hover' );
	bindColor( 'fupi_notice_switch_color', '--fupi-notice-slider-color' );
	bindColor( 'fupi_notice_necessary_switch_color', '--fupi-notice-necessary-slider-color' );
	bindColor( 'fupi_notice_border_color', '--fupi-notice-panel-border-color' );
    bindColor( 'fupi_toggler_bg_color', '--fupi-notice-toggler-bg-color' );

	bind( 'fupi_cookie_notice_border', type => { 
        let shadow = 'none', width = '0px';
        if ( type == 'small_shadow' ) shadow = '2px 3px 7px rgba(0,0,0,.2)';
        else if ( type == 'large_shadow' ) shadow = '5px 7px 17px rgba(0,0,0,.2)';
        else if ( type == 'thin_border' ) width = '1px';
        else if ( type == 'wide_border' ) width = '4px';
        setCss('--fupi-notice-panel-box-shadow', shadow);
        setCss('--fupi-notice-panel-border-width', width);
    } );

	// TYPOGRAPHY PANEL
	bind( 'fupi_cookie_notice_heading_tag', tag => {
        findAll('.fupi_headline').forEach( old_h => {
            let new_h = document.createElement( tag );
            window.fupi_headline_tag = tag;
            new_h.id = old_h.id;
            new_h.className = 'fupi_headline' + (old_h.innerHTML ? '' : ' fupi_hidden');
            new_h.style.cssText = old_h.style.cssText;
            if ( old_h.innerHTML ) new_h.innerHTML = old_h.innerHTML;
            old_h.parentNode.replaceChild(new_h, old_h);
        });
    } );

    // Helper to bind font size updates
    const bindFont = (id, name, def) => bindCssVar(id, name, def);
	bindFont( 'fupi_cookie_notice_h_font_size', '--fupi-notice-h-size', 20 );
	bindFont( 'fupi_cookie_notice_h_font_size_mobile', '--fupi-notice-h-size-mobile', 17 );
	bindFont( 'fupi_cookie_notice_p_font_size', '--fupi-notice-p-size', 16 );
	bindFont( 'fupi_cookie_notice_p_font_size_mobile', '--fupi-notice-p-size-mobile', 14 );
	bindFont( 'fupi_cookie_notice_button_font_size', '--fupi-notice-btn-txt-size', 16 );
	bindFont( 'fupi_cookie_notice_button_font_size_mobile', '--fupi-notice-btn-txt-size-mobile', 14 );

	// CONTENT PANEL
	bind( 'fupi_cookie_notice[notif_headline_text]', new_text => {
        let el = findID('fupi_main_headline');
        if ( ! new_text ){
            if ( el ) el.classList.add('fupi_hidden');
        } else {
            if ( el ) el.classList.remove('fupi_hidden');
            else {
                let tag = window.fupi_headline_tag || findID('fupi_cookie_notice').dataset.headlinetag || 'p',
                    html = `<${tag} id="fupi_main_headline" class="fupi_headline">${new_text}</${tag}>`,
                    parent = findfirst( '#fupi_welcome_panel .fupi_content' );
                if ( parent ) parent.insertAdjacentHTML( 'afterbegin', html );
            }
        };
        fupi_fill_with_text('#fupi_main_headline', 'notif_h', new_text);
	} );

    // Helper to bind text updates to elements
    const bindTxt = (id, el, key, link) => bind(id, v => fupi_fill_with_text(el, key, v, link));
	bindTxt( 'fupi_cookie_notice[notif_text]', '#fupi_main_descr', 'notif_descr', true );
	bindTxt( 'fupi_cookie_notice[agree_text]', '#fupi_agree_text', 'agree' );
	bindTxt( 'fupi_cookie_notice[stats_only_text]', '#fupi_stats_only_btn', 'stats_only' );
	bindTxt( 'fupi_cookie_notice[ok_text]', '#fupi_ok_text', 'ok' );
	bindTxt( 'fupi_cookie_notice[agree_to_selected_text]', '#fupi_agree_to_selected_cookies_btn', 'agree_to_selected' );
	bindTxt( 'fupi_cookie_notice[return_text]', '#fupi_return_btn', 'return' );
	bindTxt( 'fupi_cookie_notice[decline_text]', '#fupi_decline_cookies_btn', 'decline' );
	bindTxt( 'fupi_cookie_notice[cookie_settings_text]', '#fupi_cookie_settings_btn', 'cookie_settings' );

	bind( 'fupi_cookie_notice[necess_headline_text]', new_text => {
        let h = findID('fupi_necess_headline'), s = findID('fupi_necess_switch'), sec = findID('fupi_necess_section');
        if ( ! new_text ){
            h.classList.add('fupi_hidden'); s.classList.add('fupi_hidden');
            if ( findID('fupi_necess_descr').classList.contains('fupi_hidden') ) sec.classList.add('fupi_hidden');
        } else {
            h.classList.remove('fupi_hidden'); s.classList.remove('fupi_hidden'); sec.classList.remove('fupi_hidden');
        };
        fupi_fill_with_text('#fupi_necess_headline', 'necess_h', new_text);
	} );

	bind( 'fupi_cookie_notice[necess_text]', new_text => {
        let d = findID('fupi_necess_descr'), sec = findID('fupi_necess_section');
        if ( ! new_text ){
            d.classList.add('fupi_hidden');
            if ( findID('fupi_necess_headline').classList.contains('fupi_hidden') ) sec.classList.add('fupi_hidden');
        } else {
            d.classList.remove('fupi_hidden'); sec.classList.remove('fupi_hidden');
        };
        fupi_fill_with_text('#fupi_necess_descr','necess_descr',  new_text, true);
	} );

	bindTxt( 'fupi_cookie_notice[stats_headline_text]', '#fupi_stats_headline', 'stats_h' );
	bindTxt( 'fupi_cookie_notice[stats_text]', '#fupi_stats_descr', 'stats_descr', true );
	bindTxt( 'fupi_cookie_notice[pers_headline_text]', '#fupi_pers_headline', 'pers_h' );
	bindTxt( 'fupi_cookie_notice[pers_text]', '#fupi_pers_descr', 'pers_descr', true );
	bindTxt( 'fupi_cookie_notice[marketing_headline_text]', '#fupi_market_headline', 'market_h' );
	bindTxt( 'fupi_cookie_notice[marketing_text]', '#fupi_market_descr', 'market_descr', true );

} )( jQuery );
