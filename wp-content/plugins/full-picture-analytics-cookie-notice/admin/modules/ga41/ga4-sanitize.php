<?php

$clean_data = array();

if ( ! empty ( $input ) ) foreach( $input as $key => $value ) {

	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		switch ($clean_key) {
			case 'page_type':
			case 'page_id':
			case 'page_number':
			case 'post_date':
			case 'user_role':
			case 'page_lang':
			case 'tax_terms':
			case 'seo_title':
			case 'post_author':
			case 'search_results_nr':
			case 'author_id':
			case 'track_scroll_method':
			case 'track_views_method':
			case 'track_affil_method':
			case 'track_elems_method':
			case 'track_forms_method':
			case 'track_email_tel':
				$clean_val = sanitize_key( $value );
				break;
			case 'track_affiliate':

				$clean_val = [];

				if ( is_array( $value ) ){

					foreach( $value as $i => $section ){
						
						if ( empty( $section['sel'] ) ) continue;
						
						$clean_val[$i]['sel'] = trim( sanitize_text_field( $section['sel'] ) );
						
						if ( $input['track_views_method'] == 'evt' ){
							
							if ( empty( $section['val'] ) ) continue;
							
							$clean_val[$i]['val'] = sanitize_key( $section['val'] );

						} else {
							if ( ! empty( $section['val'] ) ) $clean_val[$i]['val'] = trim( sanitize_text_field( $section['val'] ) );
						}
					}
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
						$clean_val[$j]['evt_val'] = (int) $section['evt_val'];
						$j++;
					}
				};
				break;

			case 'track_elems':

				$clean_val = [];

				if ( is_array( $value ) ){

					foreach( $value as $i => $section ){
						if ( empty( $section['sel'] ) || empty( $section['val'] ) ) continue;
						$clean_val[$i]['sel'] = trim( sanitize_text_field( $section['sel'] ) );
						if ( $input['track_elems_method'] == 'evt' ){
							$clean_val[$i]['val'] = sanitize_key( $section['val'] );
						} else {
							$clean_val[$i]['val'] = trim( sanitize_text_field( $section['val'] ) );
						}
					}
				};
				break;

			case 'track_forms':

				$clean_val = [];

				if ( is_array( $value ) ){

					foreach( $value as $i => $section ){
						if ( empty( $section['sel'] ) || empty( $section['val'] ) ) continue;
						$clean_val[$i]['sel'] = trim( sanitize_text_field( $section['sel'] ) );
						if ( $input['track_forms_method'] == 'evt' ){
							$clean_val[$i]['val'] = sanitize_key( $section['val'] );
						} else {
							$clean_val[$i]['val'] = trim( sanitize_text_field( $section['val'] ) );
						}
					}
				};
				break;
				
			case 'track_views':
				
				$clean_val = [];

				if ( is_array( $value ) ){

					foreach( $value as $i => $section ){
						
						if ( empty( $section['sel'] ) || empty( $section['val'] ) ) continue;

						$clean_val[$i]['sel'] = trim( sanitize_text_field( $section['sel'] ) );

						if ( $input['track_views_method'] == 'evt' ){
							$clean_val[$i]['val'] = sanitize_key( $section['val'] );
						} else {
							$clean_val[$i]['val'] = trim( sanitize_text_field( $section['val'] ) );
						}
					}
				};
				break;

			case 'track_scroll':
				$clean_val = trim( sanitize_text_field( $value ) );
				break;

			case 'track_cf':

				$clean_val = [];

				if ( is_array($value) ){

					$i = 0;
					$j = 0;
					$max = count( $value );

					for ( ; $i < $max; $i++) { 
						$section = $value[$i];
						if ( empty( $section['id'] ) || empty( $section['dimname'] ) ) continue;
						$clean_val[$j]['id'] = trim( sanitize_text_field( $section['id'] ) );
						$clean_val[$j]['dimname'] = sanitize_key( $section['dimname'] );
						$j++;
					}
				};

				break;

			case 'limit_country':

				$clean_val = [];

				if ( is_array( $value ) && ! empty( $value['method'] ) && ! empty( $value['countries'] ) ) {
					$clean_val['method'] = sanitize_key( $value['method'] );
					$clean_val['countries'] = trim( sanitize_text_field( $value['countries'] ) );
				};

				break;

			case 'mp_secret_key':
			case 'id':
				
				$clean_val = trim( sanitize_text_field( $value ) );
				break;
				
			default:
				$clean_val = is_bool ( $value ) || is_string ( $value ) ? strip_tags( stripslashes( $value ) ) : false;
			break;
		}

		// error_log('sanitized ' . $clean_key . ' value: ' . json_encode($clean_val) );

		if ( ! empty( $clean_val ) && ! empty ( $clean_key ) ) $clean_data[$clean_key] = $clean_val;
	}
}
