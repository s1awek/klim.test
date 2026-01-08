<?php
$clean_data = array();

if ( ! empty( $input ) ) foreach( $input as $key => $value ) {
	
	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		switch ($clean_key) {
			case 'custom_menu_title':
			case 'deactiv_email':
				$clean_val = trim( sanitize_text_field( $value ) );
			break;

			case 'extra_users_2':
			case 'disable_for_roles':
				$clean_val = array_map( 'sanitize_key', $value );
			break;

			case 'magic_keyword':
				$clean_val = sanitize_title_with_dashes( $value );
			break;

			case 'server_method':
			case 'bot_list':
				$clean_val = sanitize_key( $value );
				break;

			case 'user_cap':

				if ( current_user_can('manage_options') ) {
					$clean_val = sanitize_key( $value );

				// if a non-admin user saves the setting...
				} else {
					// we disregard what is in the field and pass the value set by the admin
					$clean_val = $this->settings['user_cap'];
				}
			break;

			case 'extra_tools':

				$clean_val = [];

				if ( is_array($value) ){

					foreach( $value as $i => $section ){

						if ( empty( $section['name'] ) ) continue;

						$clean_val[$i]['name'] = trim( sanitize_text_field( $section['name'] ) );

						if ( ! empty( $section['url'] ) ) {
							$clean_val[$i]['url'] = trim( sanitize_url( $section['url'] ) );
						};
					}
				};

				break;

			case 'meta_tags':

				$clean_val = [];

				if ( is_array( $value ) ){

					foreach( $value as $i => $section ){
						
						if ( empty( $section['tag'] ) || empty( $section['name'] ) ) continue;
						
						$tag = $section['tag'];

						// Basic XSS prevention
						$tag = wp_kses($tag, array(
							'meta' => array(
								'name' => array(),
								'content' => array(),
								'property' => array(),
								'charset' => array(),
								'http-equiv' => array()
							)
						));
						
						// Ensure tag starts with <meta
						if (stripos($tag, '<meta') === 0) {
							// Additional sanitization
							$tag = str_replace(array('"', "'"), '"', $tag); // Standardize quotes
							$tag = preg_replace('/\s+/', ' ', $tag); // Normalize whitespace
							$tag = htmlspecialchars($tag, ENT_QUOTES, 'UTF-8', false);
							
							// save to array if everything is OK
							$clean_val[$i]['tag'] = $tag;
							$clean_val[$i]['name'] = trim( sanitize_text_field( $section['name'] ) );
						}
					}
				};
				
			break;

			// for the geolocation

			case 'ipdata_api_key':
			case 'geo':
				$clean_val = sanitize_key( $value );
			break;

			case 'cf_worker_url':
				$clean_val = esc_url_raw( $value );
			break;
			
			case 'remember_geo':
				$clean_val = (int) $value;
			break;
			
			// default

			default:
				$clean_val = is_bool ( $value ) || is_string ( $value ) ? strip_tags( stripslashes( $value ) ) : false;
			break;
		}

		// error_log('sanitized ' . $clean_key . ' value: ' . json_encode($clean_val) );

		if ( ! empty( $clean_val ) && ! empty ( $clean_key ) ) $clean_data[$clean_key] = $clean_val;
	}
}

// Make sure we are not overwriting the extra_users_2 when a non-admin user saves settings on this page

if ( ! current_user_can('manage_options') && ! empty( $this->settings ) && ! empty( $this->settings['extra_users_2'] ) ) {
	$clean_data['extra_users_2'] = $this->settings['extra_users_2'];
}
