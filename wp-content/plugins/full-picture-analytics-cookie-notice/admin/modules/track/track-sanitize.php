<?php

$clean_data = array();

if ( ! empty( $input ) ) foreach( $input as $key => $value ) {

	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		switch ($clean_key) {

			case 'intersections':
				$clean_val = trim( sanitize_text_field( $value ) );
				break;
			case 'auto_track_non_http':
			case 'tracked_taxonomies':
			case 'join_ref':
			case 'meta_preview':
				$clean_val = array_map( 'sanitize_key', $value );
				break;
			case 'redirect_404':
				$clean_val = esc_url_raw( $value );
				break;
			case 'notrack_dblclck':
			case 'formsubm_trackdelay':
			case 'track_scroll_time':
			case 'track_scroll_min':
				$clean_val = (int) $value;
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
			case 'custom_data_ids':

				$clean_val = [];

				if ( is_array($value) ){

					foreach( $value as $i => $section ){
						if ( empty( $section['id'] ) || empty( $section['type'] ) || empty( $section['meta'] ) ) continue;
						$clean_val[$i]['meta'] = sanitize_key( $section['meta'] );
						$clean_val[$i]['name'] = ! empty ( $section['name'] )  ? sanitize_text_field ( $section['name'] ) : sanitize_key( $section['id'] );
						$clean_val[$i]['id'] = sanitize_key( $section['id'] );
						$clean_val[$i]['type'] = sanitize_key( $section['type'] );
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

?>
