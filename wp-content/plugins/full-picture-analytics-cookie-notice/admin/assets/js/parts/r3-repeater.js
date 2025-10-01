// SCRIPT REPEATER

(() => {

	let fupi_content = FP.findFirst('#fupi_content');

	if ( ! fupi_content ) return;

	let module_id = fupi_content.dataset.page;

	function remove_or_clear_section(target){
		let closest_section_wrap = target.closest('.fupi_r3_section'),
			siblingSections = FP.getSiblings( closest_section_wrap );

		siblingSections = siblingSections.filter(sect => sect.classList.contains('fupi_r3_section')); // get only r3 sections

		// check if this is the only section on this level
		if ( siblingSections.length > 0 ) {
			FP.remove(closest_section_wrap);
		} else {
			clear_section(closest_section_wrap);
		}
	}

	function add_section(target){

		let current_section = target.closest('.fupi_r3_section'),
			cloned_section = current_section.cloneNode(true);

		cloned_section = clear_section(cloned_section);
		cloned_section = remove_extra_repeaters(cloned_section);
		current_section.parentElement.insertBefore(cloned_section, current_section.nextSibling);

		if ( module_id == 'cscr' || module_id == 'cook' || module_id == 'reports' || module_id == 'atrig' ) {
			let id_field = FP.findFirst('.fupi_field_id_wrap input', cloned_section);
			if ( id_field ) id_field.value = generate_random_id();
		}
	}

	function destroy_all_select2s(){
		(($)=>{
			if ( $.isFunction($.fn.select2) ){
				$('.fupi_r3_repeater .fupi_select2_enabled.fupi_select2').each( function(){
					$select2 = $(this);
					$select2.select2('destroy');
					$select2.removeClass('fupi_select2_enabled');
					
				});
			}
		})(jQuery)
	}

	function enable_all_select2s(){
		jQuery(document).ready(function($) {

			if ( $.isFunction($.fn.select2) ){

				$('.fupi_r3_repeater .fupi_select2').each( function(){

					$select2 = $(this);

					if ( $select2.hasClass('fupi_user_search') ) {

						$select2.select2({
							ajax: {
								url: ajaxurl,
								dataType: 'json',
								delay: 250,
								data: function (params) {
									return {
										q: params.term,
										action: 'fupi_search_users',
									};
								},
								processResults: function(data) {
									return {
										results: data
									};
								},
								cache: true
							},
							width: '100%',
							minimumInputLength: 2,
							placeholder: $select2.data('placeholder_text')
						});

					} else {
						$select2.select2();
					}

					$select2.addClass('fupi_select2_enabled');
				})
			};
		});
	}

	function rename_fields_in_section( sections_wrap, parent_sect_nums ){ // section_wrap is a <td> or - when called from inside this function - fupi_r3_field

		if ( ! parent_sect_nums ) parent_sect_nums = [];

		// get all the sections that are direct descendants of the section wrap
		let sibling_sections = FP.nl2Arr( sections_wrap.children ),
			sections = sibling_sections ? sibling_sections.filter( siblig => siblig.classList.contains('fupi_r3_section') ) : []; // children of <td> ( fupi_r3_section )

		// console.log('same level sections', sections);

		sections.forEach( (section, sect_i) => {

			let current_sect_nums = [...parent_sect_nums, sect_i]; // starts as [0]

			// get all the field wrappers that are direct descendants of the section or are inside a fupi_r3_fields_group wrapper
			let section_children_arr = FP.nl2Arr( section.children ),
				field_wrappers = [];

			section_children_arr.forEach( child => {
				if ( child.classList.contains( 'fupi_r3_field' ) && ! child.classList.contains('fupi_field_type_label') ) {
					field_wrappers.push( child );
				} else if ( child.classList.contains( 'fupi_r3_fields_group' ) ) {
					let sub_section_children_arr = FP.nl2Arr( child.children );
					sub_section_children_arr.forEach( sub_child => {
						if ( sub_child.classList.contains( 'fupi_r3_field' ) && ! sub_child.classList.contains('fupi_field_type_label') ) {
							field_wrappers.push( sub_child );
						}
					})
				}
			});

			field_wrappers.forEach( ( field_wrap, field_i ) => {

				// if field wrapper contains a repeater run renaming function on its content
				if ( field_wrap.classList.contains('fupi_field_type_r3') ){

					rename_fields_in_section( field_wrap, current_sect_nums );

				// if the field wrapper contains form fields then modify their names and ids
				} else {

					let field = FP.findFirst('input, textarea, select', field_wrap);

					// make sure that wrapper has a form field
					if ( field ) {

						let name_parts = field.name.split(/\]\[\d*?\]\[/g) || []; // this gives us strings with square brackets, like '][3]['. We don't need anything more than that. We use it only to count how many occurances of digits appear
	
						if (name_parts.length - 1 <= current_sect_nums.length ){ // there should always be 1 more name part than numbers to fill in
	
							// here we create a name and id
							let new_name = name_parts.shift(); // we take the first name part (this also lowers the number of elements in the array)
							name_parts.forEach( ( part, i ) => { new_name += '][' + current_sect_nums[i] + '][' + part; } );
	
							// console.log('new field name for ' + field.name + ' is ' + new_name);
	
							field.name = new_name;
							field.id = new_name;
						} else {
							console.error('name can\'t be constructed for ', field, ' with: ', name_parts, current_sect_nums );
						}
					}

				};
			} );
		} );
	}

	function remove_extra_repeaters(section){
		let extra_section = FP.findFirst('.fupi_r3_repeater + .fupi_r3_repeater', section);
		if ( extra_section ) {
			FP.remove(extra_section);
			return remove_extra_repeaters(section);
		} else {
			return section;
		}
	}

	function clear_section(section){

		section.classList.add('fupi_highlight_req');

		// remove all indicators of filled and empty fields
		FP.findAll('.field_empty', section).forEach( field => field.classList.remove('field_empty') );
		FP.findAll( '.field_filled', section ).forEach( field => field.classList.remove('field_filled') );

		// clear values and mark with empties
		FP.findAll('textarea', section).forEach( area => {
			area.value = '';
			if ( area.classList.contains('fupi_req') ) area.parentElement.classList.add('fupi_empty');
		} );

		FP.findAll('input[type="text"], input[type="url"]', section).forEach( field => {
			field.value = '';
			if ( field.classList.contains('fupi_req') ) field.parentElement.classList.add('fupi_empty');
		} );
		
		FP.findAll('input[type="password"]', section).forEach( field => {
			field.value = '';
			if ( field.classList.contains('fupi_req') ) field.parentElement.classList.add('fupi_empty');
		} );
		
		FP.findAll('input[type="number"]', section).forEach( field => {
			field.value = 0;
			if ( field.classList.contains('fupi_req') ) field.parentElement.classList.add('fupi_empty');
		} );
		
		// these do not get empties
		FP.findAll('input[type="checkbox"]', section).forEach( box => box.checked = false );
		FP.findAll('input[type="hidden"]', section).forEach( field => field.value = '' );

		FP.findAll('select', section).forEach( sel => {
			let option_el = FP.findFirst('option', sel);
			if ( option_el && option_el.value ) sel.value = option_el.value;
			
			if ( sel.classList.contains('fupi_req') ) sel.parentElement.classList.add('fupi_empty');
		} );

		// unhide hidden elements
		FP.findAll('.fupi_r3_field.fupi_hidden', section).forEach( field => field.classList.remove('fupi_hidden') );
		FP.findAll('.fupi_r3_field.fupi_disabled', section).forEach( field => field.classList.remove('fupi_disabled') );

		return section;
	}

	function enable_section_buttons() {

		FP.findID('fupi_settings_form').addEventListener('click', e => {

			let btn = e.target;

			if ( btn.classList.contains('fupi_r3_btn') ){

				destroy_all_select2s();
				let section_wrap = btn.closest('td');

				if ( btn.classList.contains('fupi_btn_remove') ){
					remove_or_clear_section(btn);
					rename_fields_in_section(section_wrap);
				} else {
					add_section(btn);
					rename_fields_in_section(section_wrap);
					modify_specific_fields();
				}

				enable_all_select2s();
				hide_already_selected_atrig_selects();
			}
		})
	}

	// This is for the Custom Scripts module

	function get_attributes_from_str( str ){

		// return an object with key-value pairs
		// string example: 'data-param='abc def ghi' async src="https://www.googletagmanager.com/gtag/js?id=UA-11111111-1" data-param2="abc" id="anID" class="class1 class2"'

		let parts = str.trim().split(' '), // we break the string into parts by 'space' (spaces can occur in unexpected places! see example above)
			ok_parts = parts.filter( v => v ), // and remove empty values from array
			ret_val = {};

		for ( var i = 0; i < ok_parts.length; i++ ) {

			let part = ok_parts[i],
				eq_index = part.indexOf('='),
				last_char = part[part.length-1];

			// if we have an equal sign
			// (this means that the value before the equal sign is the key and the one after is the value)
			if ( eq_index != -1 ){

				// we check if the last element of the current part is an apostrophe. If it isn\'t then it means we split the value in two or more pieces and need to join it with the next string parts
				while ( ! ( last_char == '"' || last_char == "'" ) && i+1 <= ok_parts.length ) {
					part += ' ' + ok_parts[i+1];
					last_char = part[part.length-1];
					i++;
				}

				// if we have the key and the whole value
				if ( last_char == '"' || last_char == "'" ) {
					// we save the key and value pair
					let key_val = part.split(/=(.*)/s); // split on the first occurance of "=" sign. They may sometimes show up in values too
					ret_val[key_val[0]]= key_val[1];
				}

			// if we don't have an equal sign this is a single value
			} else {
				ret_val[part] = true;
			}
		}

		return ret_val;
	}

	function reformat_custom_scripts( script ){

		// remove all ending "</script>" tags
		script = script.replaceAll('</script>', '');

		// remove all HTML comments including everything between <!-- and -->
		script = script.replaceAll(/<!--/g, '//');
		script = script.replaceAll(/-->/g, '');

		// remove noscript tags with everything in between
		script = script.replaceAll(/<noscript>[\s\S]*?<\/noscript>/g, '');

		// find all <script[something or nothing]> tags
		let start_regex = /<script[\s\S]*?>/gi,
			matches = script.match(start_regex),
			replacements = [];

		// get their attributes
		if ( Array.isArray( matches ) && matches.length > 0 ) matches.forEach( txt => {

			// remove "<script" and final ">"
			txt = txt.replace('<script','');
			txt = txt.slice(0, -1);
			txt = txt.trim();

			if ( txt.length == 0 ) {

				replacements.push('');

			} else {

				// try to transform the "<script [attributes]>" into "getScript()"
				let attributes_obj = get_attributes_from_str(txt),
					src = attributes_obj.src ? attributes_obj.src : false,
					attrs_str = '';

				if ( ! src ) {
					// Remove the whole <script> tag
					replacements.push('');
				} else {
					// Build string with attributes
					for (const [key, val] of Object.entries(attributes_obj)) {
						if ( key != 'src' ) {
							if ( attrs_str.length > 1 ) attrs_str += ", ";
							attrs_str += '"' + key + '": ' + val;
						}
					}
	
					replacements.push([src, attrs_str]);
				}
			}
		} );

		// prepare elements to replace <string [something or nothing]> in the original script
		replacements.forEach( ( repl, i ) =>{
			if ( repl ) replacements[i] = 'FP.getScript(' + repl[0] + ', false, {' + repl[1] + '});'; // repl[0] & repl[1] are already properly formatted
		} )

		// replace elements in string one by one

		let i = 0;

		return script.replace(start_regex, ()=>{ return replacements[i++]; });

		// e.target.value = e.target.value.replaceAll(/<.*?script[\s\S]*?>/g, '');
	}

	function enable_focusout_checks(){
		document.addEventListener( 'focusout', e => {

			switch (e.target.tagName) {
				case 'INPUT':
				case 'TEXTAREA':
				case 'SELECT':

					let r3_field_wrap = e.target.closest('.fupi_r3_field');

					if ( r3_field_wrap ) {

						// mark as empty
						if ( e.target.value ) {
							r3_field_wrap.classList.remove('field_empty');
							r3_field_wrap.classList.add('field_filled');
						} else {
							r3_field_wrap.classList.remove('field_filled');
							r3_field_wrap.classList.add('field_empty');
						}
						
						// sanitize script
						if ( module_id == 'cscr' ) {
							if ( e.target.tagName == 'TEXTAREA' ) {
								let new_value = reformat_custom_scripts( e.target.value ); // e.target.value = e.target.value.replaceAll(/<.*?script[\s\S]*?>/g, '');
								if ( new_value ) e.target.value = new_value;
							}
						}
						
						// sanitize script id
						if ( module_id == 'cscr' || module_id == 'scrblock' || module_id == 'reports' ) {
							if ( e.target.name.includes('[id]') ) e.target.value = e.target.value.replace(/[^a-z0-9_]/g, "");
						}
					}

				break;
			}
		})
	}

	// R3 SPECIFIC FUNCTIONS

	// FOR ADDING CUSTOM SCRIPTS

	function generate_random_id(){
		return Math.random().toString(36).replace(/[^a-z1-9]+/g, '').substring(0, 5); // 5 random  characters
	}

	function fill_empty_id_fields_with_random_ids(){
		let script_id_fields = FP.findAll('.fupi_field_id_wrap input');
		script_id_fields.forEach( field => field.value = field.value || generate_random_id() );
	}

	// for the Reporst & Stats module
	function make_ids_from_titles(){
	
		let reports_fields = FP.findAll('.fupi_reports_fields');

		if ( reports_fields.length == 0 ) return;
	
		document.addEventListener( 'change', e => {
			if ( e.target.tagName == 'INPUT' && e.target.name.includes('[title]') ) {
				
				let id_field = FP.findFirst( '.fupi_field_id_wrap input', e.target.closest('.fupi_r3_section') );
				
				if ( id_field ){
					let new_val = e.target.value ? (e.target.value.toLowerCase()).replace(/[^a-z0-9]/g, "") : generate_random_id();
					id_field.value = new_val;
				}
			}
		});
	}
	
	function toggle_leadscore_repeat_field( select, show ){
		let closest_r3_section = select.closest('.fupi_r3_section'),
			action_repeat_select = FP.findFirst( '.fupi_field_repeat_wrap select', closest_r3_section );

		if ( show ) {
			action_repeat_select.disabled = false;
		} else {
			if ( select.value.includes('fp_leadscore') ) {
				action_repeat_select.value = 'no';
				action_repeat_select.disabled = true;
			} else {
				action_repeat_select.disabled = false;
			};
		}
		
	}

    function listen_to_select_events(){
        document.addEventListener('change', e=>{
            if( e.target.tagName =='SELECT' && e.target.getAttribute('name').includes('atrig_id') ) {
				toggle_leadscore_repeat_field( e.target );
				hide_already_selected_atrig_selects();
			}
        })
    }

	function hide_already_selected_atrig_selects( trigger_selects ){

		trigger_selects = trigger_selects || FP.findAll('.fupi_events_builder select[name*="atrig_id"]');

		// get fields that are already selected
		let selected_options = [];

		trigger_selects.forEach( select => {
			if ( select.value ) selected_options.push( select.value );
		} );

		// hide already selected fields
		trigger_selects.forEach( select => {

			let all_options = FP.nl2Arr( select.options );

			all_options.forEach( opt => {
				opt.style.display = selected_options.includes( opt.value ) ? 'none' : 'block';
			})
		});
	}

	function modify_specific_fields(){

		// FOR SELECTING ADVANCED TRIGGERS ON A MODULE'S PAGE
		let trigger_selects = FP.findAll('.fupi_events_builder select[name*="atrig_id"]');
		
		hide_already_selected_atrig_selects( trigger_selects );
		
		trigger_selects.forEach( select => {
			toggle_leadscore_repeat_field( select );
		});
	}

	// INIT

	// check if there are any r3s on page
	if ( FP.findFirst('.fupi_r3_repeater') ) {
		
		if ( module_id == 'cscr' || module_id == 'cook' || module_id == 'reports' || module_id == 'atrig' ) fill_empty_id_fields_with_random_ids();

		enable_all_select2s();
		enable_section_buttons();
		enable_focusout_checks();
		modify_specific_fields();
    	listen_to_select_events();

		if ( module_id == 'reports' ) make_ids_from_titles();
	}

})();
