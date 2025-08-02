<?php

$clean_data = array();

if ( ! empty ( $input ) ) foreach( $input as $key => $value ) {

	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		switch ($clean_key) {
			case 'track_affiliate_2':

				$clean_val = [];

				if ( is_array( $value ) ){

					foreach( $value as $i => $section ){
						if ( empty( $section['sel'] ) ) continue;
						$clean_val[$i]['sel'] = trim( sanitize_text_field( $section['sel'] ) );
						if ( ! empty( $section['val'] ) ) $clean_val[$i]['val'] = trim( sanitize_text_field( $section['val'] ) );
					}
				};
				break;

			case 'track_elems_2':
			case 'track_forms_2':
			case 'track_views':
				
				$clean_val = [];

				if ( is_array( $value ) ){

					foreach( $value as $i => $section ){
						if ( empty( $section['sel'] ) || empty( $section['val'] ) ) continue;
						$clean_val[$i]['sel'] = trim( sanitize_text_field( $section['sel'] ) );
						$clean_val[$i]['val'] = trim( sanitize_text_field( $section['val'] ) );
					}
				};
				break;
			case 'selected_users':
				$clean_val = array_map( 'sanitize_key', $value );
			break;
			case 'domain':
			case 'custom_domain':
			case 'track_affiliate_goalname':
			case 'track_elems_goalname':
			case 'track_forms_goalname':
			case 'track_contact_links':
			case 'track_cookie_decline_2':
			case 'track_woo_purchases':
			case 'track_woo_purchased_items':
			case 'track_woo_checkouts':
			case 'track_woo_checkout_items':
			case 'track_woo_addtocart':
			case 'track_woo_addtowishlist':
				$clean_val = trim( sanitize_text_field( $value ) );
				break;
			case 'track_cf':

				$clean_val = [];

				if ( is_array($value) ){
					foreach( $value as $i => $field ){
						if ( ! empty( $field['id'] ) && ! empty( $field['param_name'] ) ) {
							$clean_val[$i]['id'] = trim( sanitize_text_field( $field['id'] ) );
							$clean_val[$i]['param_name'] = sanitize_key( $field['param_name'] );
						}
					}
				};

				break;
			case 'shared_link_url':
				$clean_val = esc_url_raw( $value );
				break;
			case 'stats_page_cap':
				$clean_val = sanitize_key( $value );
				break;
			case 'limit_country':

				$clean_val = [];

				if ( is_array( $value ) && ! empty( $value['method'] ) && ! empty( $value['countries'] ) ) {
					$clean_val['method'] = sanitize_key( $value['method'] );
					$clean_val['countries'] = trim( sanitize_text_field( $value['countries'] ) );
				};

				break;
			case 'custom_events':
				$clean_val = [];

				if ( is_array( $value ) ){

					$i = 0;
					$j = 0;
					$max = count( $value );

					for ( ; $i < $max; $i++) {
						$section = $value[$i];
						if ( empty( $section['atrig_id'] ) || empty( $section['evt_name'] ) ) continue;
						$clean_val[$j]['atrig_id'] = sanitize_key( $section['atrig_id'] );
						$clean_val[$j]['evt_name'] = sanitize_text_field( $section['evt_name'] );
						$clean_val[$j]['repeat'] = empty( $section['repeat'] ) ? 'no' :  sanitize_key( $section['repeat'] );
						$j++;
					}
				};
				break;
			default:
				$clean_val = is_bool ( $value ) || is_string ( $value ) ? strip_tags( stripslashes( $value ) ) : false;
		
				break;
		}

		// error_log('sanitized ' . $clean_key . ' value: ' . json_encode($clean_val) );

		if ( ! empty( $clean_val ) && ! empty ( $clean_key ) ) $clean_data[$clean_key] = $clean_val;
	}
}

// Make sure we are not overwriting the selected_users when a non-admin user saves settings on this page

if ( ! current_user_can('manage_options') && ! empty( $pla_opts ) && ! empty( $pla_opts['selected_users'] ) ) {
	$clean_data['selected_users'] = $pla_opts['selected_users'];
}
