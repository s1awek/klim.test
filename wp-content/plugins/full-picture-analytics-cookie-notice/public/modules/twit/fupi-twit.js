;(function(window){

	if ( allow_loading_twit() ) { 
		load_twit();
	} else {
		document.addEventListener('fp_load_scripts', ()=>{ if ( allow_loading_twit() ) load_twit(); } );
	}

	// FUNCTIONS

	function allow_loading_twit(){
		return FP.isAllowedToLoad( 'twit', ['stats','marketing'], ['id'] ); // module id in fp.XX, required cookie permission, setting name with required data (like in fp.gtm.setting_name)
	}

	function load_twit() {

		// LOAD PIXEL

		!function(e,t,n,s,u,a){
			e.twq||(s=e.twq=function(){s.exe?s.exe.apply(s,arguments):s.queue.push(arguments);},
			s.version='1.1',
			s.queue=[],
			u=t.createElement(n),
			u.async=!0,
			u.src='https://static.ads-twitter.com/uwt.js',
			a=t.getElementsByTagName(n)[0],
			a.parentNode.insertBefore(u,a))
		}(window,document,'script');

		twq('config', fp.twit.id);

		fp.loaded.push('twit');
		FP.runFn( 'FP.fns.load_twit_footer' );
	}

})(window);
