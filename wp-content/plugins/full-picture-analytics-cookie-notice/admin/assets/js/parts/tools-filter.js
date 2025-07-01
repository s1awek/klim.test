// FILTER THE LIST OF TOOLS

(()=>{

	let filter_btns = FP.findAll('.fupi_filter_btn');

	filter_btns.forEach( filter_btn => {
		
		filter_btn.addEventListener('click', () => {
		
			filter_btn.classList.toggle('fupi_active');
	
			let active_filters = FP.findAll('.fupi_filter_btn.fupi_active'),
				active_filters_arr = [],
				tools = FP.findAll('#fupi_settings_form table:first-of-type tr');
				
			active_filters.forEach( f => {
				active_filters_arr.push( f.dataset.tag );
			});
	
			tools.forEach( tool => {
				
				let input = FP.findFirst( 'input', tool ),
					tool_tags = input.dataset.tags,
					tool_tags_arr = tool_tags.split(' ');
				
				// if tools has all the tags then show it, otherwise hide it
				if ( active_filters_arr.every( filter_tag => tool_tags_arr.includes( filter_tag ) ) ) {
					tool.style.display = "block";
				} else {
					tool.style.display = "none";
				}
			} );

			if ( active_filters.length == 0 ) {
				tools.forEach( tool => tool.style.display = "block" );
			}
		});
	} )

})();