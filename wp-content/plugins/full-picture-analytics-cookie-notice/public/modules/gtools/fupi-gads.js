FP.fns.load_gads = function(){    
    
    

    fp.loaded.push( 'gads' );
    if ( fp.main.debug ) console.log('[FP] Google Ads loaded');
    FP.runFn( 'FP.fns.load_gads_footer' );
}