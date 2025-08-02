;(function(window){

	if ( allow_loading_mads() ) { 
		load_mads();
	} else {
		document.addEventListener('fp_load_scripts', ()=>{ if ( allow_loading_mads() ) load_mads(); } );
	};

	// FUNCTIONS

	function allow_loading_mads(){
		return FP.isAllowedToLoad( 'mads', ['stats', 'marketing'], ['id'] ); // module id in fp.XX, required cookie permission, setting name with required data (like in fp.gtm.setting_name)
	}

	function load_mads() {

		// LOAD MICROSOFT'S EVENT TRACKING TAG
		(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:fp.mads.id};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");

		// window.uetq = window.uetq || []; // this is now set in head-js
		
		
		// LOAD FOOTER SCRIPTS
		fp.loaded.push('mads');
		FP.runFn( 'FP.fns.load_mads_footer' );
	}

})(window);
