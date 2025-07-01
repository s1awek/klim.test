;(function(window){

	function can_enable_ga4() 	{ return FP.isAllowedToLoad( 'ga4', ['stats'], ['id'], 1, fp.notice && fp.notice.gtag_no_cookie_mode ); }
	function can_enable_gads() 	{ return FP.isAllowedToLoad( 'gads', ['stats','marketing'], ['id'], false, fp.notice && fp.notice.gtag_no_cookie_mode ); }

	load_gtag( can_enable_ga4(), can_enable_gads() );

	// Load on event
	document.addEventListener('fp_load_scripts', () => {
		load_gtag( can_enable_ga4(), can_enable_gads() );
	});

	function load_gtag( enable_ga4, enable_gads ) {

		if ( ! enable_ga4 && ! enable_gads ) return;

		if ( fp.gads && fp.gads.id && fp.gads.id.indexOf('AW-') != 0 && fp.gads.id.indexOf('GT-') != 0 ) fp.gads.id = 'AW-' + fp.gads.id;

		let script_id = enable_ga4 ? fp.ga41.id :
						enable_gads ? fp.gads.id :
						false;

		if ( ! script_id ) return;

		// ! Datalayer is already created in head-js.php

		if ( ! fp.loading.includes('gtg') ) {

			fp.loading.push('gtg');

			// set_consents();

			FP.getScript(
				'https://www.googletagmanager.com/gtag/js?id=' + script_id,
				() => { enable_tags( enable_ga4, enable_gads ) },
				{'async' : 'async'}
			);
			
		} else {
			enable_tags( enable_ga4, enable_gads );
		};
	}

	function enable_tags( enable_ga4, enable_gads ){

		if ( ! fp.loaded.includes('gtg') ) {
			window.gtag('js', new Date());
			fp.loaded.push('gtg');
		}

		let can_load_ga42 = false,
			enh_conv_active = false;

		

		if ( enable_ga4 ) {
			FP.fns.load_ga4(1, can_load_ga42);
			
		}

		if ( enable_gads ) {
			
			if ( enh_conv_active ) {
				window.gtag( 'config', fp.gads.id, { 'allow_enhanced_conversions': true } );
			} else {
				window.gtag( 'config', fp.gads.id );
			}

			FP.fns.load_gads();
		}

	}

})(window);
