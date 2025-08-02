(function (window){

	// SET BASE VALUES
	FP.updateScrollValues();

	FP.makeName = function ( str, el ) {
		if ( str ) {
			if ( str.indexOf('[name]') != -1  ) {
				var textContent = el.textContent;
				if ( textContent.length > 20 ) {
					textContent.substring(0, 20);
				};
				return str.replace('[name]', textContent);
			} else {
				return str;
			}
		} else {
			return false;
		}
	}

    FP.isFileUrl = function (url, file_formats) {
        
		var last_url_part = url.split('/').pop(),
        	files_arr = file_formats.split(',').map(function (file) {
            	return file.trim();
        	});

		return files_arr.some(function (file) {
			if ( ! file ) return false; // this will get rid of empty arr values. They show up if someone leaves a comma ',' at the begining or end of the list of formats
            return last_url_part.indexOf( '.' + file.toLowerCase() ) != -1;
        });
    };

	FP.intersectionObserver = ( newly_added_els, track_settings, slug, callback, unobserve = false, passed_val = false ) => {

		if ( ! ( 'IntersectionObserver' in window ) || ( newly_added_els && newly_added_els.length == 0 ) ) return;

		let els_a = [];

		const observer_name = 'i_' + slug + '_' + Date.now(); // "i" is for "Intersection"
		
		const processEl = ( el, val ) => {
			el.dataset[ slug + '_view' ] = val || 'name not provided';
			els_a.push(el);
		}

		// check if tracked elements are in HTML
		for (const key in track_settings) {

			let selector = track_settings[key]['sel'],
				value = track_settings[key]['val'];

			if ( newly_added_els ) {
				newly_added_els.forEach( el => {
					if ( el.matches && el.matches( selector ) ) processEl( el, value );
				});
			} else {
				FP.findAll( selector ).forEach( el => processEl( el, value ) );
			}
		}
		
		if ( els_a.length == 0 ) return;

		// create observer
		const observer = new IntersectionObserver( ( entries, observer ) => {
			entries.forEach( ( { isIntersecting, target } ) => {
				if ( isIntersecting ) {
					if ( fpdata.observed != target ) fpdata.observed = target;
					if ( unobserve ) observer.unobserve( target ); 
					callback.call( this, target, passed_val );
				}
			});
		}, { 
			rootMargin: fp.track.intersections 
		} );

		fp.observers[observer_name] = observer;

		// attach observer to found elements
		els_a.forEach( el => fp.observers[observer_name].observe(el) );
		
	};

    FP.isScrollTracked = function ( scroll_points ) {

        var reached_point = false,
			max = scroll_points.length;

		// check if the user reached any scroll point
		for (var i = 0; i < max; i++) {
			if ( fpdata.scrolled.max > scroll_points[i] ) {
				reached_point = scroll_points[i];
			}
		}

        return reached_point;
    };

	FP.getTrackedAffiliateLink = function( obj ){
	// returns link URL if left empty

		var name = false;

		if ( fpdata.clicked.link ){

			for ( const key in obj ) {

				var url_part = obj[key]['sel'],
					is_aff_link = fpdata.clicked.link.href.indexOf(url_part) != -1;

				if ( is_aff_link ) {
					name = FP.makeName( obj[key]['val'], fpdata.clicked.link.element ) || fpdata.clicked.link.href;
					break;
				}
			}
		}

		return name;
	}

	FP.getTrackedFilename = function( str ){
		if ( fpdata.clicked.link && FP.isFileUrl( fpdata.clicked.link.href, str) ) {
			try {
				return fpdata.clicked.link.href.split('/').pop().split('?')[0];
			} catch (e) {
				console.error(e);
				return false;
			}
		}
	}

	FP.getClickTarget = function( obj ){

		var name = false;

        for ( const key in obj ) {
	
			var selector = obj[key]['sel'],
				searched_el = false,
				target = fpdata.clicked.element;

			if ( fpdata.clicked.element.matches( selector ) ) {
				if ( fpdata.clicked.middle ) {
					if ( fpdata.clicked.link ) {
						searched_el = target;
					}
				} else {
					searched_el = target;
				}
			} else if ( fpdata.clicked.link && fpdata.clicked.link.element.matches( selector ) ){
				searched_el = fpdata.clicked.link.element;
			}

			if ( searched_el ) {
				name = FP.makeName( obj[key]['val'], searched_el ) || selector;
				break;
			}
        }

		return name;
	}

	FP.getSubmittedForm = function( obj ){

		var name = false;

		for ( const key in obj ) {
			if ( fpdata.submitted_form.element.matches( obj[key]['sel'] ) ) {
				name = obj[key]['val'] || location.host + location.pathname;
				break;
			}
		}

		return name;
	}

	FP.formatScrollPoints = function( str ){
		if ( typeof str == 'string' ) return str.split(",").map( function (val) { return parseInt( val.replace('%','') ) } )
		return str;
	}

	// ON FORM SUBMIT

	// var wait_for_forms = fp.track.formsubm_trackdelay ? fp.track.formsubm_trackdelay * 1000 : 1000; // we are waiting for all the forms to load before we start tracking submissions.

	// function listenToFormSubmits(e){
	// 	fpdata.submitted_form = { 'element' : e.target };
	// 	FP.doActions( 'form_submit' );
	// }

	// setTimeout( function(){
	// 	document.addEventListener('submit', listenToFormSubmits);
	// }, wait_for_forms );

	// ON SCROLL

    function on_scroll() {
        FP.startActivityTimer();
        FP.updateScrollValues();
		FP.doActions( 'scroll' );
    }

    document.addEventListener( 'scroll', FP.throttle( on_scroll, 50, { trailing: true } ) );

	// SHOW "USER NOT TRACKED" ICON AT THE BOTTOM-LEFT CORNER OF THE SCREEN

	if ( fpdata.cookies && fpdata.cookies.disabled ) {
		document.addEventListener('DOMContentLoaded', function() {
			document.body.insertAdjacentHTML('afterbegin', '<div id=\'fupi_disabled\' title="Tracking disabled"><svg style="width: 20px;" focusable="false" data-prefix="fas" data-icon="eye-slash" class="svg-inline--fa fa-eye-slash fa-w-20" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="currentColor" d="M320 400c-75.85 0-137.25-58.71-142.9-133.11L72.2 185.82c-13.79 17.3-26.48 35.59-36.72 55.59a32.35 32.35 0 0 0 0 29.19C89.71 376.41 197.07 448 320 448c26.91 0 52.87-4 77.89-10.46L346 397.39a144.13 144.13 0 0 1-26 2.61zm313.82 58.1l-110.55-85.44a331.25 331.25 0 0 0 81.25-102.07 32.35 32.35 0 0 0 0-29.19C550.29 135.59 442.93 64 320 64a308.15 308.15 0 0 0-147.32 37.7L45.46 3.37A16 16 0 0 0 23 6.18L3.37 31.45A16 16 0 0 0 6.18 53.9l588.36 454.73a16 16 0 0 0 22.46-2.81l19.64-25.27a16 16 0 0 0-2.82-22.45zm-183.72-142l-39.3-30.38A94.75 94.75 0 0 0 416 256a94.76 94.76 0 0 0-121.31-92.21A47.65 47.65 0 0 1 304 192a46.64 46.64 0 0 1-1.54 10l-73.61-56.89A142.31 142.31 0 0 1 320 112a143.92 143.92 0 0 1 144 144c0 21.63-5.29 41.79-13.9 60.11z"></path></svg></div><style>#fupi_disabled{position: fixed; left: 0; bottom: 0; color: #fff; background-color: rgba(0,0,0,.2); padding: 5px 5px 0; border-radius: 0 3px 0 0; z-index: 10000;cursor: pointer;}#fupi_disabled:hover{background-color: rgba(0,0,0,.8);}</style>');

			var disabled_ico = FP.findID('fupi_disabled');

			disabled_ico.addEventListener('click', function (e) {
                FP.deleteCookie('fp_cookie');
                fpdata.cookies = false;
                document.location = location.origin;
            })
		});
	}

})(window);
