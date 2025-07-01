<?php

$clean_data = array();

if ( ! empty( $input ) ) foreach( $input as $key => $value ) {

	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		switch ($clean_key) {
			case 'show_to_countries':
			case 'custom_agree_cookies_class':
			case 'custom_decline_cookies_class':
			case 'custom_cookies_settings_class':
			case 'custom_menu_title':
			case 'deactiv_email':
			case 'intersections':
				$clean_val = trim( sanitize_text_field( $value ) );
				break;
			case 'auto_track_non_http':
			case 'disable_for_roles':
			case 'tracked_taxonomies':
				$clean_val = array_map( 'sanitize_key', $value );
				break;
			case 'enable_scripts_after':
			case 'geo':
			case 'server_method':
			case 'bot_list':
				$clean_val = sanitize_key( $value );
				break;
			case 'redirect_404':
			case 'cf_worker_url':
				$clean_val = esc_url_raw( $value );
				break;
			case 'magic_keyword':
				$clean_val = sanitize_title_with_dashes( $value );
				break;
			case 'notrack_dblclck':
			case 'formsubm_trackdelay':
			case 'track_scroll_time':
			case 'track_scroll_min':
				$clean_val = (int) $value;
				break;
			case 'join_ref':
			case 'self_host':
			case 'extra_users_2':
				$clean_val = array_map( 'sanitize_key', $value );
			break;
			case 'track_non_http':
				$clean_val = [];

				if ( is_array( $value ) ){

					foreach( $value as $i => $section ){
						if ( empty( $section['search'] ) || empty( $section['replace'] ) ) continue;
						$clean_val[$i]['compare'] = isset( $section['compare'] ) ? sanitize_key( $section['compare'] ) : 'eq';
						$clean_val[$i]['search'] = trim( sanitize_text_field( $section['search'] ) );
						$clean_val[$i]['replace'] = trim( sanitize_text_field( $section['replace'] ) );
					}
				};
				break;
			case 'switch_ref':
				$clean_val = [];

				if ( is_array( $value ) ){

					foreach( $value as $i => $section ){
						if ( empty( $section['search'] ) || empty( $section['replace'] ) ) continue;
						$clean_val[$i]['search'] = trim( sanitize_text_field( $section['search'] ) );
						$clean_val[$i]['replace'] = trim( sanitize_text_field( $section['replace'] ) );
					}
				};
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
			default:
				$clean_val = strip_tags( stripslashes( $value ) );
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
