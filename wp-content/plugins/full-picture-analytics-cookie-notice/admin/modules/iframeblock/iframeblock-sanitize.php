<?php

$clean_data = array();

if ( ! empty( $input ) ) foreach( $input as $key => $value ) {

	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		switch ($clean_key) {
			case 'caption_txt':
			case 'btn_text':
				$clean_val = trim( sanitize_text_field( $value ) );
				break;
			case 'iframe_img':
			case 'privacy_url':
				$clean_val = trim( sanitize_url( $value ) );
				break;
			case 'auto_rules':
				$clean_val = array_map( 'sanitize_key', $value );
				break;
			case 'manual_rules':

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
			default:
				if ( ! is_array( $value ) ){
					$clean_val = strip_tags( stripslashes( $value ) );
				}
				break;
		}

		// error_log('sanitized ' . $clean_key . ' value: ' . json_encode($clean_val) );

		if ( ! empty( $clean_val ) && ! empty ( $clean_key ) ) $clean_data[$clean_key] = $clean_val;
	}
}
?>
