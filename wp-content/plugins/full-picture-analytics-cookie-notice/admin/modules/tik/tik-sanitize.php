<?php

$clean_data = array();

if ( ! empty( $input ) ) foreach( $input as $key => $value ) {

	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		switch ($clean_key) {
			case 'track_elems':
			case 'track_forms':
				
				$clean_val = [];

				if ( is_array( $value ) ){

					foreach( $value as $i => $section ){
						if ( empty( $section['sel'] ) || empty( $section['val'] ) ) continue;
						$clean_val[$i]['sel'] = trim( sanitize_text_field( $section['sel'] ) );
						$clean_val[$i]['val'] = trim( sanitize_text_field( $section['val'] ) );
						$i++;
					}
				};
				break;
				
			case 'id':
			case 'track_file_downl':
				$clean_val = trim( sanitize_text_field( $value ) );
				break;
			case 'limit_country':

				$clean_val = [];

				if ( is_array( $value ) && ! empty( $value['method'] ) && ! empty( $value['countries'] ) ) {
					$clean_val['method'] = sanitize_key( $value['method'] );
					$clean_val['countries'] = trim( sanitize_text_field( $value['countries'] ) );
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
