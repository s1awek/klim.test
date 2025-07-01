<?php

$clean_data = array();

if ( ! empty ( $input ) ) foreach( $input as $key => $value ) {

	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		switch ($clean_key) {
			
			case 'track_affiliate':
			case 'track_elems':
			case 'track_forms':
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
			
			case 'custom_events':
				$clean_val = [];

				if ( is_array( $value ) ){

					$i = 0;
					$j = 0;
					$max = count( $value );

					for ( ; $i < $max; $i++) {
						$section = $value[$i];
						if ( empty( $section['atrig_id'] ) || empty( $section['conv_label'] ) ) continue;
						$clean_val[$j]['atrig_id'] = sanitize_key( $section['atrig_id'] );
						$clean_val[$j]['conv_label'] = sanitize_text_field( $section['conv_label'] );
						$clean_val[$j]['repeat'] = sanitize_key( $section['repeat'] );
						$j++;
					}
				};
				break;

			case 'id':
			case 'id2':
			case 'business_type':
			case 'woo_conv_id':
			case 'track_tel':
			case 'track_email':
				$clean_val = trim( sanitize_text_field( $value ) );
				break;
			case 'limit_country':

				$clean_val = [];

				if ( is_array( $value ) && ! empty( $value['method'] ) && ! empty( $value['countries'] ) ) {
					$clean_val['method'] = sanitize_key( $value['method'] );
					$clean_val['countries'] = trim( sanitize_text_field( $value['countries'] ) );
				};

				break;
				
			case 'self_host_file_name':
				$clean_val = sanitize_key( $value );
				break;

			default:
				$clean_val = strip_tags( stripslashes( $value ) );
				break;
		}

		// error_log('sanitized ' . $clean_key . ' value: ' . json_encode($clean_val) );

		if ( ! empty( $clean_val ) && ! empty ( $clean_key ) ) $clean_data[$clean_key] = $clean_val;
	}
}
