;( function () {

	'use strict';

	let notice = FP.findID('fupi_cookie_notice'),
		panel = {},
		added_events_to_notice_btns = false;

	panel.welcome = FP.findID('fupi_welcome_panel');
	panel.settings = FP.findID('fupi_settings_panel');

	// INIT

	add_events_to_buttons_in_notice();
	show_notice_after_clicking_links_in_html();
	enable_switches();

	function enable_switches(){
		if ( notice.dataset.switches ){
			
			if ( notice.dataset.switches.includes('stats') ){
				FP.findFirst('#fupi_stats_section .fupi_switch').click();
			};

			if ( notice.dataset.switches.includes('market') ){
				FP.findFirst('#fupi_market_section .fupi_switch').click();
			};

			if ( notice.dataset.switches.includes('pers') ){
				FP.findFirst('#fupi_pers_section .fupi_switch').click();
			};
		}
	}

	function show_notice_after_clicking_links_in_html() {

		let show_notice_links = FP.findAll( '.fp-show-cookie-notice' );

		if ( show_notice_links.length ){
			show_notice_links.forEach( function ( link ) {
				link.addEventListener( 'click', function(e) {
					e.preventDefault();
					e.stopPropagation();
					show_notice();
					panel.settings ? show_panel( 'settings' ) : show_panel( 'welcome' );
				});
			})
		}
	}

	function add_events_to_buttons_in_notice() {

		let cookie_settings_btn 		= FP.findID( 'fupi_cookie_settings_btn' ),
			return_btn 					= FP.findID( 'fupi_return_btn' );

		if ( cookie_settings_btn ) {
			cookie_settings_btn.addEventListener( 'click', () => {
				hide_panel( 'welcome' );
				show_panel( 'settings' );
			})
		}

		if ( return_btn ) {
			return_btn.addEventListener( 'click', () => {
				hide_panel( 'settings' );
				show_panel( 'welcome' );
			})
		}
	}

	function show_panel( type ) {

		if ( panel[type] ) {

			panel[type].classList.remove( 'fupi_fadeOutDown', 'fupi_hidden' );
			panel[type].classList.add( 'fupi_animated', 'fupi_fadeInUp' );

			if ( ! added_events_to_notice_btns ) {
				add_events_to_buttons_in_notice();
				added_events_to_notice_btns = true;
			}
		}
	}

	function hide_panel( type ) {
		panel[type].classList.remove( 'fupi_fadeInUp' );
		panel[type].classList.add( 'fupi_animated', 'fupi_fadeOutDown' );
	}

	function show_notice() {
		notice.setAttribute("style","");
		notice.classList.remove( 'fupi_fadeOutDown', 'fupi_hidden' );
		notice.classList.add( 'fupi_fadeInUp', 'fupi_animated' );
	}

	// function hide_notice() {
	// 	notice.classList.remove( 'fupi_fadeInUp' );
	// 	notice.classList.add( 'fupi_fadeOutDown' );
	// }

})();
