// CREATE LINKS TO SECTIONS (IN SIDEBAR)

(() => {

	let fupi_settings_form 	= FP.findID('fupi_settings_form'),
		fupi_admin_page 	= FP.getUrlParamByName('page'),
		headlines 			= FP.findAll( '#fupi_settings_form h2' );
		sub_items_added		= false;

	function add_sub_items(){

		if ( headlines.length < 2 ) return;

		let current_page_el = FP.findFirst('.fupi_sidenav_item.fupi_current'),
			output = '<div id="fupi_sidenav_sub">',
			active_tab = fupi_settings_form.dataset.activetab;
		
		headlines.forEach( ( headline, i ) => {
			
			let h_txt = headline.innerText,
				current_class = i == 0  ? 'active' : '';
			
			output += '<button type="button" data-target="hook_' + active_tab + '_' + i + '" class="fupi_sidenav_sub_item ' + current_class + '"><span>' + h_txt + '</span></button>';
			headline.setAttribute( 'id', 'hook_' + active_tab + '_' + i );
			headline.classList.add( 'fupi_hook', 'fupi_el');
		});

		// current_page_el.insertAdjacentHTML('beforebegin', '<button id="fupi_toggle_hidden_menu_items"><span class="dashicons dashicons-menu-alt"></span><span class="fupi_srt">Menu</span></button>');
		current_page_el.insertAdjacentHTML('beforeend', output + '</div>');
		current_page_el.classList.add('fupi_has_subnav');

		sub_items_added = true;
	}
	/*
	function hide_not_active_menu_items(){

		// get all sidenav sections
		let sections = FP.findAll('.fupi_sidenav_section');

		// hide menu elements that do not have current (active) menu items
		sections.forEach( section => {
			
			let current_page_link = FP.findFirst( '.fupi_current', section );
			
			if ( ! current_page_link ) {
				section.style.display = 'none';
				section.classList.add('fupi_hideable_menu_element');
			} else {
				// hide all menu items within this section which are not current or are marked with alt style
				let $not_active_menu_items = jQuery('.fupi_sidenav_item:not(.fupi_current):not(.fupi_alt_style)');
				$not_active_menu_items.hide().addClass('fupi_hideable_menu_element');
			}
		});

		// add event to toggle button
		FP.findID( 'fupi_toggle_hidden_menu_items' ).addEventListener( 'click', () => {

			// jQuery - get all elements with class fupi_hideable_menu_element
			let $hidden_elements = jQuery('#fupi_nav_col .fupi_hideable_menu_element');
 
			// Use jQuery to animate showing all hidden sections
			$hidden_elements.slideToggle( 300 );

			// Use jQuery to slide hide element with id fupi_toggle_hidden_menu_items
			jQuery('#fupi_toggle_hidden_menu_items').slideToggle( 300 );
		});
	}*/

	function remove_highlight_from_active_menu_item(){
		let active_menu_link = FP.findFirst('#fupi_nav_col .fupi_sidenav_sub_item.active');
		if ( active_menu_link ) active_menu_link.classList.remove('active');
	}

	function enable_sections_toggle(){

		// headlines = FP.findAll('#fupi_settings_form h2');
		let fupi_nav_col_links = FP.findAll('#fupi_nav_col .fupi_sidenav_sub_item');

		// make 1st section visible & unhide form
		if ( headlines.length > 1 ) show_section( headlines[0].id ); // show first section

		// add events to links that add a target to url & show sections
		if ( fupi_nav_col_links.length > 1 ) {

			fupi_nav_col_links.forEach( link => {
				link.addEventListener( 'click', () => {
					if ( ! link.classList.contains('active') ) {
						let hook = link.dataset.target;
						show_section( hook );
						FP.setCookie('fp_viewed_section', JSON.stringify( [ hook, document.location ] ) ); // remember chosen section
					}
				} )
			} )
		}
	}

	function show_last_viewed_section(){

		let last_viewed_section = FP.readCookie('fp_viewed_section');

		if ( ! last_viewed_section ) return;

		last_viewed_section = JSON.parse( last_viewed_section );

		// do nothing if the page was reloaded or refreshed
		if ( last_viewed_section[1] == document.location ) return; 

		let section_menu_item = FP.findFirst( '.fupi_sidenav_sub_item[data-target="' + last_viewed_section[0] + '"]' );
		if ( ! section_menu_item ) {
			FP.deleteCookie('fp_viewed_section' );
		} else {
			show_section( last_viewed_section[0] );
		};
	}

	function show_section( hook ) {

		let section_elements 	= FP.findAll( '.fupi_el, .form-table' ),
			pseudo_link 		= FP.findFirst( '.fupi_sidenav_sub_item[data-target="' + hook + '"]' );

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

	if ( fupi_admin_page && fupi_settings_form) {
		
		if ( fupi_admin_page != 'full_picture_tools' ){
			add_sub_items();
			//if ( sub_items_added ) hide_not_active_menu_items();
			enable_sections_toggle();
		}

		show_last_viewed_section();
	};

})();

// TOGGLE MENU O MOBILES

(()=>{

	$toggle_menu_btn = FP.findID('fupi_mobile_nav_toggle_button');

	if ( ! $toggle_menu_btn ) return;

	$menu = FP.findID('fupi_side_menu');

	$toggle_menu_btn.addEventListener('click', () => {
		$menu.classList.toggle('fupi_show_mobile_menu');
	});
})();
