<?php

$clean_data = array();

if ( ! empty( $input ) ) foreach( $input as $key => $value ) {

	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		switch ($clean_key) {
			
			case 'tag_affiliate':

				$clean_val = [];

				if ( is_array( $value ) ){

					foreach( $value as $i => $section ){
						if ( empty( $section['sel'] ) ) continue;
						$clean_val[$i]['sel'] = trim( sanitize_text_field( $section['sel'] ) );
						if ( ! empty( $section['val'] ) ) $clean_val[$i]['val'] = trim( sanitize_text_field( $section['val'] ) );
					}
				};
				break;

			case 'tag_elems':
			case 'tag_forms':
			case 'tag_views':

				$clean_val = [];

				if ( is_array( $value ) ){

					foreach( $value as $i => $section ){
						if ( empty( $section['sel'] ) || empty( $section['val'] ) ) continue;
						$clean_val[$i]['sel'] = trim( sanitize_text_field( $section['sel'] ) );
						$clean_val[$i]['val'] = trim( sanitize_text_field( $section['val'] ) );
					}
				};
				break;
			case 'id':
			case 'tag_file_downl':
				$clean_val = trim( sanitize_text_field( $value ) );
				break;
			case 'tag_cf':

				$clean_val = [];

				if ( is_array($value) ){

					foreach( $value as $i => $section ){
						if ( empty( $section['id'] ) || empty( $section['name'] ) ) continue;
						$clean_val[$i]['id'] = trim( sanitize_text_field( $section['id'] ) );
						$clean_val[$i]['name'] = trim( sanitize_text_field( $section['name'] ) );
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
			default:
				$clean_val = strip_tags( stripslashes( $value ) );
				break;
		}

		// error_log('sanitized ' . $clean_key . ' value: ' . json_encode($clean_val) );

		if ( ! empty( $clean_val ) && ! empty ( $clean_key ) ) $clean_data[$clean_key] = $clean_val;
	}
}
?>
