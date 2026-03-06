<?php

$clean_data = array();

if ( ! empty( $input ) ) foreach( $input as $key => $value ) {

	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		switch ($clean_key) {

			// MIXED SECTIONS

			case 'enable_scripts_after':
			case 'mode':
			case 'optin':
			case 'optout':
			case 'inform':
				$clean_val = sanitize_key( $value );
			break;
			
			case 'show_to_countries':
			case 'optin_countries':
			case 'optout_countries':
			case 'inform_countries':
			case 'toggle_selector':
			case 'iframe_caption_txt':
			case 'iframe_btn_text':
				$clean_val = trim( sanitize_text_field( $value ) );
			break;

			case 'priv_policy_page':
			case 'pp_id':
				$clean_val = (int) $value;
			break;

			case 'hide_on_pages':
			case 'iframe_auto_rules':
			case 'scrblk_auto_rules':
				$clean_val = array_map( 'sanitize_key', $value );
			break;

			// IFRAME BLOCKING ONLY

			case 'iframe_img':
			case 'privacy_url':
				$clean_val = trim( sanitize_url( $value ) );
			break;

			case 'iframe_manual_rules':

				$clean_val = [];

				if ( is_array($value) ){

					foreach( $value as $i => $section ){
						
						if ( empty( $section['name'] ) || empty( $section['iframe_url'] ) ) continue;
						
						$clean_val[$i]['name'] 				= trim( sanitize_text_field( $section['name'] ) );
						$clean_val[$i]['iframe_url'] 		= trim( sanitize_text_field( $section['iframe_url'] ) );
						$clean_val[$i]['privacy_url'] 		= trim( sanitize_url( $section['privacy_url'] ) );
						$clean_val[$i]['image_url'] 		= trim( sanitize_url( $section['image_url'] ) );
						$clean_val[$i]['stats'] 			= ! empty( $section['stats'] );
                        $clean_val[$i]['market'] 			= ! empty( $section['market'] );
                        $clean_val[$i]['pers'] 				= ! empty( $section['pers'] );
					}
				};

			break;

			// SCRIPT BLOCKING ONLY

			case 'scrblk_manual_rules':

				$clean_val = [];

				if ( is_array($value) ){

					foreach( $value as $i => $section ){
						
						if ( empty( $section['rules'] ) || empty( $section['id'] ) ) continue;
						
						$clean_val[$i]['id'] = sanitize_key( $section['id'] );

						// Make title if not provided by the user
						
						$clean_val[$i]['title'] = empty( $section['title'] ) ? 'Script ' . $section['id'] : trim( sanitize_text_field( $section['title'] ) );

						// RULES
                
						$j = 0;

						foreach ( $section['rules'] as $rule ) {

							// save only full rules
							if ( ! empty( $rule['block_by'] ) && ! empty( $rule['unique'] ) ) {
								$clean_val[$i]['rules'][$j]['block_by'] = sanitize_key( $rule['block_by'] );
								$clean_val[$i]['rules'][$j]['unique'] = trim( sanitize_text_field( $rule['unique'] ) );
							}

							$j++;
						}

						// REQUIRES
						$clean_val[$i]['stats'] 		= ! empty( $section['stats'] );
                        $clean_val[$i]['market'] 		= ! empty( $section['market'] );
                        $clean_val[$i]['pers'] 			= ! empty( $section['pers'] );
						$clean_val[$i]['always'] 		= ! empty( $section['always'] );
						$clean_val[$i]['force_load'] 	= ! empty( $section['force_load'] );

						if ( ! empty( $section['pp_url'] ) ) 	$clean_val[$i]['pp_url'] = trim( sanitize_url( $section['pp_url'] ) );
						
						if ( ! empty( $section['method'] ) && ! empty( $section['countries'] ) ) {
							$clean_val[$i]['method'] = sanitize_key( $section['method'] );
							$clean_val[$i]['countries'] = trim( sanitize_text_field( $section['countries'] ) );
						};
					}
				};

			break;

			// DEFAULT

			default:
				$clean_val = is_bool ( $value ) || is_string ( $value ) ? strip_tags( stripslashes( $value ) ) : false;
			break;
		}

		// error_log('sanitized ' . $clean_key . ' value: ' . json_encode($clean_val) );

		if ( ! empty( $clean_val ) && ! empty ( $clean_key ) ) $clean_data[$clean_key] = $clean_val;
	}
}
?>
