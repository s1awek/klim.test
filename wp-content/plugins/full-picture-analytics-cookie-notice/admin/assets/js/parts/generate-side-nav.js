// CREATE LINKS TO SECTIONS (IN SIDEBAR)

(() => {

	let fupi_settings_form 	= FP.findID('fupi_settings_form'),
		fupi_admin_page 	= FP.getUrlParamByName('page'),
		headlines 			= FP.findAll( '#fupi_settings_form h2' ),
		hidden_h2			= [],
		visible_h2			= [],
		sub_items_added		= false,
		is_easy_mode		= !! FP.findFirst('#fupi_content.adv_mode_off');

	function mark_all_h2_as_fupi_el(){
		headlines.forEach( h2 => h2.classList.add('fupi_el'));
	}

	function get_next_table_el( h2 ){

		let nextEl = h2.nextElementSibling;

		if ( nextEl ) {

			if ( nextEl.tagName == 'TABLE' ) {
				return nextEl;
			} else {
				let nextNextEl = nextEl.nextElementSibling;
				if ( nextNextEl && nextNextEl.tagName == 'TABLE' ) return nextNextEl;
			}
		}

		return false;
	}

	function check_if_show( h2 ){

		if ( ! is_easy_mode ) return true;

		let table_el = get_next_table_el(h2);

		if ( table_el ) {
			if ( table_el.querySelectorAll('tr:not(.fupi_adv)').length > 0 ) return true;
		} else {
			return true;
		}

		return false;
	}

	function add_submenu_items(){
		
		let current_page_el = FP.findFirst('.fupi_sidenav_item.fupi_current'),
			output = '<div id="fupi_sidenav_sub">';
		
		headlines.forEach( ( h2, i ) => {
			
			let show = check_if_show( h2 );

			if ( ! show ) {
				h2.style.display = 'none';
				hidden_h2.push(h2);
				return;
			};

			visible_h2.push(h2);

			let h_txt = h2.innerText,
				current_class = i == 0  ? 'active' : '',
				active_tab = fupi_settings_form.dataset.activetab;
			
			output += '<button type="button" data-target="hook_' + active_tab + '_' + i + '" class="fupi_sidenav_sub_item ' + current_class + '"><span>' + h_txt + '</span></button>';

			// add hook to section
			h2.setAttribute( 'id', 'hook_' + active_tab + '_' + i );
		});

		// Add submenu items only if there is more than 1 section
		if ( visible_h2.length <= 1 ) return;

		// current_page_el.insertAdjacentHTML('beforebegin', '<button id="fupi_toggle_hidden_menu_items"><span class="dashicons dashicons-menu-alt"></span><span class="fupi_srt">Menu</span></button>');
		current_page_el.insertAdjacentHTML('beforeend', output + '</div>');
		current_page_el.classList.add('fupi_has_subnav');

		sub_items_added = true;
	}

	function remove_highlight_from_active_menu_item(){
		let active_menu_link = FP.findFirst('#fupi_nav_col .fupi_sidenav_sub_item.active');
		if ( active_menu_link ) active_menu_link.classList.remove('active');
	}

	function enable_sections_toggle(){

		let fupi_nav_col_links = FP.findAll('#fupi_nav_col .fupi_sidenav_sub_item');

		// make 1st section visible & unhide form
		if ( visible_h2.length > 1 ) show_section( headlines[0].id ); // show first section

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

	function hide_all_section_elements(){
		FP.findAll( '.fupi_el, .form-table' ).forEach( element => element.classList.add('fupi_hidden'));
	}

	function show_the_only_visible_section(){
		
		let h2 = FP.findFirst('h2[id*="hook_track_"]'),
			nextEl = h2.nextElementSibling;

		h2.classList.remove('fupi_hidden');

		if ( nextEl && nextEl.classList.contains('fupi_section_descr') ) {
			
			nextEl.classList.remove('fupi_hidden');

			let nextNextEl = nextEl.nextElementSibling;
			if ( nextNextEl && nextNextEl.tagName == 'TABLE' ) {
				nextNextEl.classList.remove('fupi_hidden');
			}
		}
	}

	if ( headlines.length > 1 && fupi_admin_page && fupi_settings_form ) {
		
		if ( fupi_admin_page != 'full_picture_tools' ){

			mark_all_h2_as_fupi_el();
			hide_all_section_elements();
			add_submenu_items();

			if ( visible_h2.length > 1 ) {
				enable_sections_toggle();
			} else {
				show_the_only_visible_section();
			}
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
