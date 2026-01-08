( function( $ ) {

	if ( typeof FP == 'undefined' ) return;

	/*
	*
	*
	*
	*	MAIN MECHANICS > Changing the banner type (optin/out & notify), toggling banner's and toggler's visibility
	*	
	*
	*
	*/
	let toggler = FP.findID('fupi_notice_toggler');
		toggler.setAttribute("style","");

	function show_notif_headline(){
		let h = FP.findID('fupi_main_headline');
		if ( h && h.textContent.length > 0 ){
			h.classList.remove('fupi_hidden');
		}
	};
		
	function enable_correct_notice_type(){ //optin/out or notify
		
		if ( window.fupi_init ) return;
		window.fupi_init = true;

		remember_state_of_hide_els();
		remember_state_of_show_els();
		
		if ( window.fupi_preview_mode == 'notify' ) {
			enable_notif_preview();
		} else {
			toggle_hide_els();
			toggle_show_els();
		}
	}
	
	function remember_state_of_hide_els( latest_states ){
		
		// get either the latest or original states
		if ( latest_states === '' ) latest_states = ' '; // when none of the checkboxes is checked an empty string is returned. It messes up the code afterwards so we need to change it to a string that will return true in the "if" below but will not be recognized by all the "includes" afterwards
		if ( latest_states ) latest_states = latest_states.split(',').map( v => v.trim());
		let states = latest_states || wp.customize.settings.values['fupi_cookie_notice[hide]'];
		
		// set empty arr
		window.fupi_hide_toggle = [];
		
		// save states in array
		window.fupi_hide_toggle.push( [ 'fupi_decline_cookies_btn', states.includes('decline_btn') ] );
		window.fupi_hide_toggle.push( [ 'fupi_cookie_settings_btn', states.includes('settings_btn') ] );
		window.fupi_hide_toggle.push( [ 'fupi_stats_section', states.includes('stats') ] );
		window.fupi_hide_toggle.push( [ 'fupi_market_section', states.includes('market') ] );
		window.fupi_hide_toggle.push( [ 'fupi_pers_section', states.includes('pers') ] );
	}

	function remember_state_of_show_els( latest_states ){
		
		// get either the latest or original states
		if ( latest_states === '' ) latest_states = ' '; // when none of the checkboxes is checked an empty string is returned. It messes up the code afterwards so we need to change it to a string that will return true in the "if" below but will not be recognized by all the "includes" afterwards
		if ( latest_states ) latest_states = latest_states.split(',').map( v => v.trim());
		let states = latest_states || wp.customize.settings.values['fupi_cookie_notice[show]'];
		
		// set empty arr
		window.fupi_show_toggles = [];
		
		// save states in array
		window.fupi_show_toggles.push( [ '.fupi_poweredBy', states.includes('powered_by') ] );
		window.fupi_show_toggles.push( [ '#fupi_stats_only_btn', states.includes('stats_only_btn') ] );
	}

	function toggle_hide_els(){

		if ( window.fupi_hide_toggle ) { // this if is super important and has to stay. Without it, the controls are unresponsive when a user saves preview as notify (when geo is enbaled), but then enables opt-in mode in the consent banner settings (without geo) and tries to style the notice
			window.fupi_hide_toggle.forEach( arr => {
				FP.findID( arr[0] ).style.display = arr[1] ? 'none' : 'block';
			});
		}
	}

	function toggle_show_els(){

		if ( window.fupi_show_toggles ) { // this if is super important and has to stay. Without it, the controls are unresponsive when a user saves preview as notify (when geo is enbaled), but then enables opt-in mode in the consent banner settings (without geo) and tries to style the notice
			window.fupi_show_toggles.forEach( arr => {
				FP.findAll( arr[0] ).forEach( el => el.style.display = arr[1] ? 'block' : 'none' );
			});
		}
	}

	// transform the banner into a notification
	function enable_notif_preview(){

		FP.findID('fupi_cookie_notice').classList.add('fupi_notice_infobox');
		
		FP.findFirst('html').classList.add('fupi_infobox');
		
		document.body.style.overflowY = 'auto';

		let panel_welcome = FP.findID('fupi_welcome_panel'),
			panel_settings = FP.findID('fupi_settings_panel');

		// hide settings panel
		panel_settings.classList.remove( 'fupi_fadeInUp', 'fupi_animated' );
		panel_settings.classList.add( 'fupi_fadeOutDown' );

		// show welcome panel
		panel_welcome.classList.remove( 'fupi_fadeOutDown', 'fupi_hidden' );
		panel_welcome.classList.add( 'fupi_animated', 'fupi_fadeInUp' );

		// hide the decline button
		FP.findID('fupi_decline_cookies_btn').style.display = "none";
		
		// hide all optin els except and "stats only" button
		[
			'fupi_cookie_settings_btn',
			'fupi_stats_section',
			'fupi_market_section',
			'fupi_pers_section',
			'fupi_stats_only_btn',
		].forEach( el => FP.findID(el).style.display="none" );

		// show powered_by if it was enabled in the settings
		if ( window.fupi_show_toggle ){
			if ( window.fupi_show_toggle.some( arr => arr[0] == 'fupi_poweredby' && arr[1] === true ) ) FP.findAll( '.fupi_poweredby' ).forEach(el => el.style.display="block" );
		}
	}

	function enable_optin_preview(){

		FP.findID('fupi_cookie_notice').classList.remove('fupi_notice_infobox');
		FP.findFirst('html').classList.remove('fupi_infobox');
		if ( FP.findFirst('html').classList.contains('fupi_scroll_lock') ) document.body.style.overflowY = 'hidden';

		toggle_hide_els();
		toggle_show_els();
	}

	function fupi_add_link_to_notif(text){

		if ( text.includes('{{') && text.includes('}}') ) {
			
			// get the content between {{ }}
			const regex = /\{\{(.*?)\}\}/g;
			let url = '';

			// Turn text into links
			return text.replace(regex, (match, innerText) => {

				// get URL and create a link
				if ( innerText.includes('|') ) {
					innerText_a = innerText.split('|');
					url = innerText_a[1] && innerText_a[1].length > 0 ? innerText_a[1] : 'https://example.com';
					innerText = innerText_a[0];
				} else {
					url = 'https://example.com';
				}

				return `<a href="${url}">${innerText}</a>`;
			});

		} else {
			return text;
		}
	}

	function fupi_fill_with_text(el_id, text_id, new_text, add_link = false) {
		var $el = $( el_id );
		if ( $el.length ){
			if ( new_text.length != 0 ) {
				if ( add_link ) new_text = fupi_add_link_to_notif( new_text );
				$el.html( new_text );
			} else {
				if ( add_link ) {
					default_text = fupi_add_link_to_notif( fupi_default_texts[text_id] );
					$el.html( default_text );
				} else {
					$el.html( fupi_default_texts[text_id] );
				}
			}
		}
	}

	function preselect_switches( preselected ){

		let stats_checked 	= FP.findFirst('#fupi_stats_agree:checked'),
			market_checked	= FP.findFirst('#fupi_marketing_agree:checked'),
			pers_checked	= FP.findFirst('#fupi_pers_agree:checked'),
			enable_stats	= preselected.includes('stats'),
			enable_market 	= preselected.includes('market'),
			enable_pers 	= preselected.includes('pers');

		if ( ( enable_stats && ! stats_checked ) || ( ! enable_stats && stats_checked ) ) FP.findFirst('#fupi_stats_section .fupi_switch').click();
		
		if ( ( enable_market && ! market_checked ) || ( ! enable_market && market_checked ) ) FP.findFirst('#fupi_market_section .fupi_switch').click();

		if ( ( enable_pers && ! pers_checked ) || ( ! enable_pers && pers_checked ) ) FP.findFirst('#fupi_pers_section .fupi_switch').click();
		
	}

	// SHOW / HIDE BANNER OR TOGGLER WHEN THE SETTINGS SECTION IS OPENED / CLOSED

	function show_notice_banner(){

		let $notice_els = $('#fupi_cookie_notice, #fupi_welcome_panel'),
			notice_wrapper = FP.findID('fupi_cookie_notice'),
			html_el = document.getElementsByTagName( 'html' )[0];
		
		// show notice
		notice_wrapper.setAttribute("style","");
		$notice_els.removeClass( 'fupi_fadeOutDown fupi_hidden' );
		$notice_els.addClass( 'fupi_fadeInUp fupi_animated' );

		// add blur
		if ( html_el.classList.contains('fupi_blur_added') || ! html_el.classList.contains('fupi_blur_removed') && fp.notice.blur_page ) {
			html_el.classList.remove('fupi_blur_out');
			html_el.classList.add('fupi_blur');
		}

		// remove scroll
		if ( html_el.classList.contains('fupi_scroll_removed') || ! html_el.classList.contains('fupi_scroll_removed') && fp.notice.scroll_lock ) {
			document.body.style.overflowY = 'hidden';
			html_el.classList.add('fupi_scroll_lock');
		}
	}

	function hide_notice_banner(){

		let $notice_els = $('#fupi_cookie_notice, .fupi_panel.fupi_animated'),
			html_el = document.getElementsByTagName( 'html' )[0];
		
		// hide all panels notice
		$notice_els.removeClass( 'fupi_fadeInUp' );
		$notice_els.addClass( 'fupi_fadeOutDown' );

		// remove blur
		if ( html_el.classList.contains('fupi_blur') ){
			html_el.classList.remove('fupi_blur');
			html_el.classList.add('fupi_blur_out');
		}

		// add scroll
		if ( html_el.classList.contains('fupi_scroll_lock') ) {
			html_el.classList.remove('fupi_scroll_lock');
			document.body.style.overflowY = 'auto';
		}
	}

	function show_toggler(){
		let toggler = FP.findID('fupi_notice_toggler');
		if ( toggler && toggler.classList.contains('fupi_active') ) {
			toggler.classList.add( 'fupi_fadeInUp' );
			toggler.classList.remove( 'fupi_fadeOutDown' );
		}
	};

	function hide_toggler(){
		let toggler = FP.findID('fupi_notice_toggler');
		if ( toggler && toggler.classList.contains('fupi_active') ) {
			toggler.classList.remove( 'fupi_fadeInUp');
			toggler.classList.add( 'fupi_fadeOutDown' );
		}
	};

	// INIT When the preview finishes loading
    wp.customize.bind( 'preview-ready', () => {
    	
		// SHOW/HIDE THE BANNER IN THE PREVIEW SECTION
		wp.customize.preview.bind( 'fupi_open_notice', function( expand ) {

			window.fupi_preview_mode = window.fupi_preview_mode || wp.customize.settings.values['fupi_cookie_notice[active_preview]'];

			enable_correct_notice_type(); // opt-in/out or notfication
			show_notif_headline();
			preselect_switches( wp.customize.settings.values['fupi_cookie_notice[switches_on]'] );

			if ( expand ) {
				hide_toggler();
				show_notice_banner();
			} else {
				show_toggler();
				hide_notice_banner();
			}
    	} );

		// SHOW/HIDE TOGGLER IN THE PREVIEW SECTION
		wp.customize.preview.bind( 'fupi_open_toggler', function( expand ) {

			// window.fupi_preview_mode = window.fupi_preview_mode || wp.customize.settings.values['fupi_cookie_notice[active_preview]'];

			if ( expand ) {
				show_toggler();
				hide_notice_banner();
			} else {
				hide_toggler();
				show_notice_banner();
			}
    	} );
    } );

	// TOGGLER SECTION

	wp.customize( 'fupi_cookie_notice[enable_toggle_btn]', value => {
		value.bind( function( enabled ) {
			let toggler = FP.findID('fupi_notice_toggler');
			if ( enabled ) {
				toggler.classList.add( 'fupi_active', 'fupi_animated' );
			} else {
				toggler.classList.remove( 'fupi_active', 'fupi_animated' );
			}
		} );
	} );

	/*
	*
	*
	*
	*	LIVE BANNER MODIFICATIONS
	*	
	*
	*
	*/
	
	// SWITCH PREVIEW VERSION

	wp.customize( 'fupi_cookie_notice[active_preview]', value => {
		value.bind( function( newval ) {
			window.fupi_preview_mode = newval;
			if ( newval == 'notify' ) {
				enable_notif_preview();
			} else {
				enable_optin_preview();
			}
		} );
	} );

	// POSITION

	wp.customize( 'fupi_cookie_notice[position]', value => {
		value.bind( function( newval ) {
			$( '#fupi_cookie_notice' ).attr( 'data-position', newval);
		} );
	} );

	// POSITION IN "INFORM ONLY" MODE

	wp.customize( 'fupi_cookie_notice[position_inform]', value  => {
		value.bind( function( newval ) {
			
			if ( newval == 'notify' )  FP.findID('fupi_cookie_notice').classList.add('fupi_notice_infobox');

			$( '#fupi_cookie_notice' ).attr( 'data-position_inform', newval);
		} );
	} );

	// OVERLAY
	// fupi_cookie_notice[btn_class]

	wp.customize( 'fupi_cookie_notice[overlay]', value => {
		value.bind( function( newval ) {
			if ( newval ) {
				$( '#fupi_cookie_notice' ).addClass('fupi_notice_gradient_overlay');
			} else {
				$( '#fupi_cookie_notice' ).removeClass('fupi_notice_gradient_overlay');
			}
		} );
	} );

	// LOCK SCROLL
	wp.customize( 'fupi_cookie_notice[scroll_lock]', value => {
		value.bind( function( newval ) {

			let html_el = document.getElementsByTagName( 'html' )[0];

			if ( newval ) {
				document.body.style.overflowY = 'hidden';
				html_el.classList.add('fupi_scroll_removed');
				html_el.classList.add('fupi_scroll_lock');
			} else {
				document.body.style.overflowY = 'auto';
				html_el.classList.remove('fupi_scroll_removed');
				html_el.classList.remove('fupi_scroll_lock');
			}
		} );
	} );

	// BLUR
	// fupi_cookie_notice[btn_class]

	wp.customize( 'fupi_cookie_notice[blur_page]', value => {
		value.bind( function( newval ) {

			let html_el = document.getElementsByTagName( 'html' )[0];

			if ( newval ) {
				html_el.classList.remove('fupi_blur_removed');
				html_el.classList.remove('fupi_blur_out');
				html_el.classList.add('fupi_blur_added');
				html_el.classList.add('fupi_blur');
			} else {
				setTimeout( () => {
					html_el.classList.remove('fupi_blur_added');
					html_el.classList.remove('fupi_blur');
					html_el.classList.add('fupi_blur_removed');
					html_el.classList.add('fupi_blur_out');
				}, 300 );
			}
		} );
	} );

	// BUTTON CONFIGURATION

	wp.customize( 'fupi_cookie_notice[btn_config]', value => {
		value.bind( function( newval ) {
			$( '#fupi_cookie_notice' ).attr( 'data-btn_config', newval);
		} );
	} );

	// PADDINGS

	wp.customize( 'fupi_cookie_notice[paddings]', value => {
		value.bind( function( newval ) {
			$( '#fupi_cookie_notice' ).attr( 'data-paddings', newval);
		} );
	} );

	// BUTTONS GAPS

	wp.customize( 'fupi_cookie_notice_btns_gaps', value => {
        value.bind( function( newval ) {
			$( '#fupi_cookie_notice' ).attr( 'data-btn_gaps', newval);
        } );
    } );

	// HIDE STUFF
	// fupi_cookie_notice[hide]

	wp.customize( 'fupi_cookie_notice[hide]', value => {
		value.bind( function( hidden_elements ) {
			remember_state_of_hide_els(hidden_elements); // everything except poweredby info
			toggle_hide_els();
		} );
	} );

	// SHOW STUFF
	// fupi_cookie_notice[show]

	wp.customize( 'fupi_cookie_notice[show]', value => {
		value.bind( function( show_elements ) {
			remember_state_of_show_els(show_elements); // everything except poweredby info
			toggle_show_els();
		} );
	} );

	// ENABLE SWITCHES BY DEFAULT
	// fupi_cookie_notice[switches_on]

	wp.customize( 'fupi_cookie_notice[switches_on]', value => {
		value.bind( function( preselected ) {
			preselect_switches( preselected );
		} );
	} );

	// BUTTON CLASS
	// fupi_cookie_notice[btn_class]

	wp.customize( 'fupi_cookie_notice[btn_class]', value => {
		value.bind( function( newval ) {
			$( '.fupi_button' ).each(function(){
				let btn = $(this);
				btn.attr('class', btn.attr('data-classes') + ' ' + newval );
			})
		} );
	} );

	// CTA CLASS
	// fupi_cookie_notice[cta_class]

	wp.customize( 'fupi_cookie_notice[cta_class]', value => {
		value.bind( function( newval ) {
			$( '.fupi_cta' ).each(function(){
				let btn = $(this);
				btn.attr('class', btn.attr('data-classes') + ' ' + newval );
			})
		} );
	} );

	wp.customize( 'fupi_cookie_notice_size', value => {
		value.bind( function( size ) {
			$( '#fupi_cookie_notice' ).removeClass('fupi_notice_btn_default fupi_notice_btn_small fupi_notice_btn_medium fupi_notice_btn_large');
			$( '#fupi_cookie_notice' ).addClass( 'fupi_notice_btn_' + size );

		} );
	} );

	// POPUP PANEL MAX WIDTH
	
	wp.customize( 'fupi_notice_popup_width', value => {
		value.bind( function( val ) {
			if ( ! val ) val = 0;
			document.body.style.setProperty('--fupi-notice-popup-panel-max-width', val + 'px');
		} );
	} );

	// NOTICE ROUNDED CORNERS

	wp.customize( 'fupi_notice_round_corners', value => {
		value.bind( function( val ) {
			if ( ! val ) val = 16;
			document.body.style.setProperty('--fupi-notice-panel-round-corners', val + 'px');
		} );
	} );

	// BTN ROUNDED CORNERS

	wp.customize( 'fupi_notice_btn_round_corners', value => {
		value.bind( function( val ) {
			if ( ! val ) val = 8;
			document.body.style.setProperty('--fupi-notice-btn-round-corners', val + 'px');
		} );
	} );

	// COLORS

	wp.customize( 'fupi_notice_bg_color', value => {
		value.bind( function( color ) {
			document.body.style.setProperty('--fupi-notice-panel-bg-color', color);
		} );
	} );

	wp.customize( 'fupi_notice_h_color', value => {
		value.bind( function( color ) {
			document.body.style.setProperty('--fupi-notice-h-color', color);
		} );
	} );

	wp.customize( 'fupi_notice_text_color', value => {
		value.bind( function( color ) {
			document.body.style.setProperty('--fupi-notice-txt-color', color);
		} );
	} );

	wp.customize( 'fupi_notice_btn_color', value => {
		value.bind( function( color ) {
			document.body.style.setProperty('--fupi-notice-btn-bg-color', color);
		} );
	} );

	wp.customize( 'fupi_notice_btn_color_hover', value => {
		value.bind( function( color ) {
			document.body.style.setProperty('--fupi-notice-btn-bg-color-hover', color);
		} );
	} );

	wp.customize( 'fupi_notice_btn_txt_color', value => {
		value.bind( function( color ) {
			document.body.style.setProperty('--fupi-notice-btn-text-color', color);
		} );
	} );

	wp.customize( 'fupi_notice_btn_txt_color_hover', value => {
        value.bind( function( color ) {
			document.body.style.setProperty('--fupi-notice-btn-text-color-hover', color);
        } );
    } );

	wp.customize( 'fupi_notice_cta_color', value => {
		value.bind( function( color ) {
			document.body.style.setProperty('--fupi-notice-cta-bg-color', color);
		} );
	} );

	wp.customize( 'fupi_notice_cta_color_hover', value => {
		value.bind( function( color ) {
			document.body.style.setProperty('--fupi-notice-cta-bg-color-hover', color);
		} );
	} );

	wp.customize( 'fupi_notice_cta_txt_color', value => {
		value.bind( function( color ) {
			document.body.style.setProperty('--fupi-notice-cta-txt-color', color);
		} );
	} );

	wp.customize( 'fupi_notice_cta_txt_color_hover', value => {
        value.bind( function( color ) {
			document.body.style.setProperty('--fupi-notice-cta-txt-color-hover', color);
        } );
    } );

	wp.customize( 'fupi_notice_switch_color', value => {
		value.bind( function( color ) {
			document.body.style.setProperty('--fupi-notice-slider-color', color);
		} );
	} );

	wp.customize( 'fupi_notice_necessary_switch_color', value => {
		value.bind( function( color ) {
			document.body.style.setProperty('--fupi-notice-necessary-slider-color', color);
		} );
	} );

	// border style 
	wp.customize( 'fupi_cookie_notice_border', value => { 
        value.bind( function( border_type ) {
			if ( border_type == 'small_shadow' ) {
				document.body.style.setProperty('--fupi-notice-panel-box-shadow', '2px 3px 7px rgba(0,0,0,.2)');
				document.body.style.setProperty('--fupi-notice-panel-border-width', '0px');
			} else if ( border_type == 'large_shadow' ) {
				document.body.style.setProperty('--fupi-notice-panel-box-shadow', '5px 7px 17px rgba(0,0,0,.2)');
				document.body.style.setProperty('--fupi-notice-panel-border-width', '0px');
			} else if ( border_type == 'thin_border' ) {
				document.body.style.setProperty('--fupi-notice-panel-box-shadow', 'none');
				document.body.style.setProperty('--fupi-notice-panel-border-width', '1px');
			} else if ( border_type == 'wide_border' ){
				document.body.style.setProperty('--fupi-notice-panel-box-shadow', 'none');
				document.body.style.setProperty('--fupi-notice-panel-border-width', '4px');
			} else {
				document.body.style.setProperty('--fupi-notice-panel-box-shadow', 'none');
				document.body.style.setProperty('--fupi-notice-panel-border-width', '0px');
			}
        } );
    } );

	// border color
	wp.customize( 'fupi_notice_border_color', value => {
        value.bind( function( color ) {
			document.body.style.setProperty('--fupi-notice-panel-border-color', color);
        } );
    } );

	//
	// TYPOGRAPHY PANEL
	//

	wp.customize( 'fupi_cookie_notice_heading_tag', value => {
        value.bind( tag => {

			FP.findAll('.fupi_headline').forEach( old_h => {

				let new_h = document.createElement( tag ); // just stay after the "if" above
				window.fupi_headline_tag = tag;

				new_h.id = old_h.id;
				new_h.classList.add('fupi_headline');
				new_h.style = old_h.getAttribute('style');

				if ( old_h.innerHTML ) {
					new_h.innerHTML = old_h.innerHTML;
				} else {
					new_h.classList.add( 'fupi_hidden' );
				}

				old_h.parentNode.replaceChild(new_h, old_h);

			});
        } );
    } );

	wp.customize( 'fupi_cookie_notice_h_font_size', value => {
        value.bind( function( size ) {
			let size_val = size && size > 0 ? size + 'px' : '20px';
			document.body.style.setProperty('--fupi-notice-h-size', size_val);
        } );
    } );

	wp.customize( 'fupi_cookie_notice_h_font_size_mobile', value => {
        value.bind( function( size ) {
			let size_val = size && size > 0 ? size + 'px' : '17px';
			document.body.style.setProperty('--fupi-notice-h-size-mobile', size_val);
        } );
    } );

	wp.customize( 'fupi_cookie_notice_p_font_size', value => {
        value.bind( function( size ) {
			let size_val = size && size > 0 ? size + 'px' : '16px';
			document.body.style.setProperty('--fupi-notice-p-size', size_val);
        } );
    } );

	wp.customize( 'fupi_cookie_notice_p_font_size_mobile', value => {
        value.bind( function( size ) {
			let size_val = size && size > 0 ? size + 'px' : '14px';
			document.body.style.setProperty('--fupi-notice-p-size-mobile', size_val);
        } );
    } );

	wp.customize( 'fupi_cookie_notice_button_font_size', value => {
        value.bind( function( size ) {
			let size_val = size && size > 0 ? size + 'px' : '16px';
			document.body.style.setProperty('--fupi-notice-btn-txt-size', size_val);
        } );
    } );

	wp.customize( 'fupi_cookie_notice_button_font_size_mobile', value => {
        value.bind( function( size ) {
			let size_val = size && size > 0 ? size + 'px' : '14px';
			document.body.style.setProperty('--fupi-notice-btn-txt-size-mobile', size_val);
        } );
    } );

	//
	// CONTENT PANEL
	//

	// TEXTS

	wp.customize( 'fupi_cookie_notice[notif_headline_text]', value => {
		value.bind( function( new_text ) {
			let headline_el = FP.findID('fupi_main_headline');
			if ( ! new_text ){
				if ( headline_el ) headline_el.classList.add('fupi_hidden')
			} else {
				if ( headline_el ) {
					if ( headline_el.classList.contains('fupi_hidden') ){
						headline_el.classList.remove('fupi_hidden')
					}
				} else {
					let notice_el = FP.findID('fupi_cookie_notice'),
						tag = window.fupi_headline_tag || notice_el.dataset.headlinetag || 'p',
						headline_html = '<' + tag + ' id="fupi_main_headline" class="fupi_headline">' + new_text + '</' + tag + '>',
						first_banner_text = FP.findFirst( '#fupi_welcome_panel .fupi_content' );
					
					if ( first_banner_text ) first_banner_text.insertAdjacentHTML( 'afterbegin', headline_html );
				}
			};
			fupi_fill_with_text('#fupi_main_headline', 'notif_h', new_text);
		} );
	} );

	wp.customize( 'fupi_cookie_notice[notif_text]', value => {
		value.bind( function( new_text ) {
			fupi_fill_with_text('#fupi_main_descr', 'notif_descr', new_text, true);
		} );
	} );

	wp.customize( 'fupi_cookie_notice[agree_text]', value => {
		value.bind( function( new_text ) {
			fupi_fill_with_text('#fupi_agree_text', 'agree', new_text);
		} );
	} );

	wp.customize( 'fupi_cookie_notice[stats_only_text]', value => {
		value.bind( function( new_text ) {
			fupi_fill_with_text('#fupi_stats_only_btn', 'stats_only', new_text);
		} );
	} );

	wp.customize( 'fupi_cookie_notice[ok_text]', value => {
		value.bind( function( new_text ) {
			fupi_fill_with_text('#fupi_ok_text', 'ok', new_text);
		} );
	} );

	wp.customize( 'fupi_cookie_notice[agree_to_selected_text]', value => {
		value.bind( function( new_text ) {
			fupi_fill_with_text('#fupi_agree_to_selected_cookies_btn', 'agree_to_selected', new_text);
		} );
	} );

	wp.customize( 'fupi_cookie_notice[return_text]', value => {
		value.bind( function( new_text ) {
			fupi_fill_with_text('#fupi_return_btn', 'return', new_text);
		} );
	} );

	wp.customize( 'fupi_cookie_notice[decline_text]', value => {
		value.bind( function( new_text ) {
			fupi_fill_with_text('#fupi_decline_cookies_btn', 'decline', new_text);
		} );
	} );

	wp.customize( 'fupi_cookie_notice[cookie_settings_text]', value => {
		value.bind( function( new_text ) {
			fupi_fill_with_text('#fupi_cookie_settings_btn', 'cookie_settings', new_text);
		} );
	} );

	wp.customize( 'fupi_cookie_notice[necess_headline_text]', value => {
		value.bind( function( new_text ) {
			
			let headline_el = FP.findID('fupi_necess_headline'),
				descr_el = FP.findID('fupi_necess_descr'),
				switch_el = FP.findID('fupi_necess_switch'),
				section_el = FP.findID('fupi_necess_section');

			if ( ! new_text ){
				headline_el.classList.add('fupi_hidden');
				switch_el.classList.add('fupi_hidden');
				if ( descr_el.classList.contains('fupi_hidden') ) section_el.classList.add('fupi_hidden');
			} else {
				if ( headline_el.classList.contains('fupi_hidden') ){
					headline_el.classList.remove('fupi_hidden');
					switch_el.classList.remove('fupi_hidden');
					section_el.classList.remove('fupi_hidden');
				}
			};

			fupi_fill_with_text('#fupi_necess_headline', 'necess_h', new_text);
		} );
	} );

	wp.customize( 'fupi_cookie_notice[necess_text]', value => {
		value.bind( function( new_text ) {
			
			let headline_el = FP.findID('fupi_necess_headline'),
				descr_el = FP.findID('fupi_necess_descr'),
				section_el = FP.findID('fupi_necess_section');

			if ( ! new_text ){
				descr_el.classList.add('fupi_hidden');
				if ( headline_el.classList.contains('fupi_hidden') ) section_el.classList.add('fupi_hidden');
			} else {
				if ( descr_el.classList.contains('fupi_hidden') ){
					descr_el.classList.remove('fupi_hidden');
					section_el.classList.remove('fupi_hidden');
				}
			};
			fupi_fill_with_text('#fupi_necess_descr','necess_descr',  new_text, true);
		} );
	} );

	wp.customize( 'fupi_cookie_notice[stats_headline_text]', value => {
		value.bind( function( new_text ) {
			fupi_fill_with_text('#fupi_stats_headline', 'stats_h', new_text);
		} );
	} );

	wp.customize( 'fupi_cookie_notice[stats_text]', value => {
		value.bind( function( new_text ) {
			fupi_fill_with_text('#fupi_stats_descr', 'stats_descr', new_text, true);
		} );
	} );

	wp.customize( 'fupi_cookie_notice[pers_headline_text]', value => {
		value.bind( function( new_text ) {
			fupi_fill_with_text('#fupi_pers_headline', 'pers_h', new_text);
		} );
	} );

	wp.customize( 'fupi_cookie_notice[pers_text]', value => {
		value.bind( function( new_text ) {
			fupi_fill_with_text('#fupi_pers_descr', 'pers_descr', new_text, true);
		} );
	} );

	wp.customize( 'fupi_cookie_notice[marketing_headline_text]', value => {
		value.bind( function( new_text ) {
			fupi_fill_with_text('#fupi_market_headline', 'market_h', new_text);
		} );
	} );

	wp.customize( 'fupi_cookie_notice[marketing_text]', value => {
		value.bind( function( new_text ) {
			fupi_fill_with_text('#fupi_market_descr', 'market_descr', new_text, true);
		} );
	} );

	// TOGGLER SECTION

	wp.customize( 'fupi_toggler_bg_color', value => {
		value.bind( function( color ) {
			document.body.style.setProperty('--fupi-notice-toggler-bg-color', color);
		} );
	} );

} )( jQuery );
