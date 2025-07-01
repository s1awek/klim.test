<?php

$clean_data = array();

if ( ! empty( $input ) ) foreach( $input as $key => $value ) {

	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		switch ($clean_key) {
			case 'auto_rules':
				$clean_val = array_map( 'sanitize_key', $value );
			break;
			case 'blocked_scripts':

				$clean_val = [];

				if ( is_array($value) ){

					foreach( $value as $i => $section ){
						
						if ( empty( $section['url_part'] ) || empty( $section['id'] ) ) continue;
						
						$clean_val[$i]['url_part'] = trim( sanitize_text_field( $section['url_part'] ) );
						$clean_val[$i]['id'] = sanitize_key( $section['id'] );

						// Make title if not provided by the user
						
						$clean_val[$i]['title'] = empty( $section['title'] ) ? 'Script ' . $section['id'] : trim( sanitize_text_field( $section['title'] ) );
						// if ( ! empty( $section['title'] ) ) 	$clean_val[$i]['title'] = trim( sanitize_text_field( $section['title'] ) ); // previous

						// checkboxes
						$clean_val[$i]['stats'] 		= ! empty( $section['stats'] );
                        $clean_val[$i]['market'] 		= ! empty( $section['market'] );
                        $clean_val[$i]['pers'] 			= ! empty( $section['pers'] );
						$clean_val[$i]['force_load'] 	= ! empty( $section['force_load'] );

						if ( ! empty( $section['block_by'] ) ) 	$clean_val[$i]['block_by'] = sanitize_key( $section['block_by'] );
						if ( ! empty( $section['name'] ) ) 		$clean_val[$i]['name'] = trim( sanitize_text_field( $section['name'] ) );
						if ( ! empty( $section['pp_url'] ) ) 	$clean_val[$i]['pp_url'] = trim( sanitize_url( $section['pp_url'] ) );
						
						if ( ! empty( $section['method'] ) && ! empty( $section['countries'] ) ) {
							$clean_val[$i]['method'] = sanitize_key( $section['method'] );
							$clean_val[$i]['countries'] = trim( sanitize_text_field( $section['countries'] ) );
						};
					}
				};

			break;

			default:
				$clean_val = strip_tags( stripslashes( $value ) );
			break;
		}

		// error_log('sanitized ' . $clean_key . ' value: ' . json_encode($clean_val) );

		if ( ! empty( $clean_val ) && ! empty ( $clean_key ) ) $clean_data[$clean_key] = $clean_val;
	}
}
?>
