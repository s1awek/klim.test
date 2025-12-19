<?php

$clean_data = array();

if ( ! empty( $input ) ) foreach( $input as $key => $value ) {

	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		switch ($clean_key) {
			case 'track_affiliate':

				$clean_val = [];

				if ( is_array( $value ) ){

					foreach( $value as $i => $section ){
						if ( empty( $section['sel'] ) ) continue;
						$clean_val[$i]['sel'] = trim( sanitize_text_field( $section['sel'] ) );
						if ( ! empty( $section['val'] ) ) $clean_val[$i]['val'] = trim( sanitize_text_field( $section['val'] ) );
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

						// Parameters

						// If parameters exist
						if ( ! empty ( $section['params'] ) && is_array( $section['params'] ) ) {
							
							$clean_params = [];
							$k = 0;
							
							foreach ( $section['params'] as $param ) {
								// Check if both name and value exist
								if ( ! empty( $param['name'] ) && isset( $param['val'] ) && isset( $param['type'] ) ) {
									$clean_params[$k]['name'] = sanitize_key( $param['name'] );
									$clean_params[$k]['type'] = sanitize_key( $param['type'] );
									
									switch ( $clean_params[$k]['type'] ) {
										case 'string':
										case 'path':
											$clean_params[$k]['val'] = sanitize_text_field( trim( $param['val'] ) );
										break;

										case 'number':
											$clean_params[$k]['val'] = (int) $param['val'];
										break;

										case 'bool':
											$clean_params[$k]['val'] = $param['val'] === 'true' || $param['val'] === '1' ? 1 : 0;
										break;
									}
									
									$k++;
								}
							}
							
							// Only save evt_params if there are valid parameters
							if ( ! empty( $clean_params ) ) {
								$clean_val[$j]['params'] = $clean_params;
							}
						}

						$j++;
					}
				};
				break;

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
				
			case 'track_file_downl':
			case 'track_scroll':
			case 'id':
				$clean_val = trim( sanitize_text_field( $value ) );
				break;
			case 'track_cf':

				$clean_val = [];

				if ( is_array($value) ){

					$i = 0;

					foreach( $value as $field ){
						if ( ! empty( $field['id'] ) ) {
							$clean_val[$i]['id'] = trim( sanitize_text_field( $field['id'] ) );
							$i++;
						}
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
