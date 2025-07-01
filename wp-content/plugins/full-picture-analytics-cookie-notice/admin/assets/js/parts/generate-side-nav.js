// CREATE LINKS TO SECTIONS (IN SIDEBAR)

(() => {

	let fupi_settings_form 	= FP.findID('fupi_settings_form'),
		fupi_admin_page 	= FP.getUrlParamByName('page'),
		headlines 			= FP.findAll( '#fupi_settings_form h2' );

	function add_sub_items(){

		if ( headlines.length < 2 ) return;

		let current_page_el = FP.findID('fupi_current_page_sidenav'),
			output = '<div id="fupi_sidenav_sub">',
			active_tab = fupi_settings_form.dataset.activetab;
		
		headlines.forEach( ( headline, i ) => {
			
			let h_txt = headline.innerText,
				current_class = i == 0  ? 'active' : '';
			
			output += '<button type="button" data-target="hook_' + active_tab + '_' + i + '" class="fupi_nav_section_btn ' + current_class + '"><span>' + h_txt + '</span></button>';
			headline.setAttribute( 'id', 'hook_' + active_tab + '_' + i );
			headline.classList.add( 'fupi_hook', 'fupi_el');
		});

		current_page_el.insertAdjacentHTML('beforeend', output + '</div>');
	}

	function remove_highlight_from_active_menu_item(){
		let active_menu_link = FP.findFirst('#fupi_page_nav .fupi_nav_section_btn.active');
		if ( active_menu_link ) active_menu_link.classList.remove('active');
	}

	function enable_sections_toggle(){

		// headlines = FP.findAll('#fupi_settings_form h2');
		let fupi_page_nav_links = FP.findAll('#fupi_page_nav .fupi_nav_section_btn:not(.fupi_never_active)');

		// make 1st section visible & unhide form
		if ( headlines.length > 1 ) show_section( headlines[0].id ); // show first section
		// if ( fupi_settings_form ) fupi_settings_form.classList.remove('fupi_hidden');

		// add events to links that add a target to url & show sections
		if ( fupi_page_nav_links.length > 1 ) {

			fupi_page_nav_links.forEach( link => {
				link.addEventListener( 'click', () => {
					if ( ! link.classList.contains('active') ) {
						let hook = link.dataset.target;
						show_section( hook );
						FP.setCookie('fp_last_section', hook ); // remember chosen section
					}
				} )
			} )
		}
	}

	function show_last_viewed_section(){

		let last_viewed_section_hook = FP.readCookie('fp_last_section');

		if ( last_viewed_section_hook ) {
			let section_menu_item = FP.findFirst( '.fupi_nav_section_btn[data-target="' + last_viewed_section_hook + '"]' );
			if ( ! section_menu_item ) {
				FP.setCookie('fp_last_section', false );
			} else {
				show_section( last_viewed_section_hook );
			};
		}
	}

	function show_section( hook ) {

		let section_elements 	= FP.findAll( '.fupi_el, .form-table' ),
			pseudo_link 		= FP.findFirst( '.fupi_nav_section_btn[data-target="' + hook + '"]' );

		if ( pseudo_link ) {

			remove_highlight_from_active_menu_item();

			// hide all sections
			section_elements.forEach( element => element.classList.add('fupi_hidden'));
	
			// show chosen section
			FP.findAll('#' + hook + ', #' + hook + '+*, #' + hook + '+*+table').forEach( element => element.classList.remove('fupi_hidden'));
	
			// highlight current menu link
			pseudo_link.classList.add('active');
		}
	}

	if ( fupi_admin_page && fupi_admin_page != 'full_picture_tools' && fupi_settings_form) {
		add_sub_items();
		enable_sections_toggle();
		show_last_viewed_section();
	};

})();
