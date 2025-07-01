<?php

$clean_data = array();

if ( ! empty( $input ) ) foreach( $input as $key => $value ) {

	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		switch ($clean_key) {
			case 'join_traffic':
				$clean_val = trim( sanitize_text_field( $value ) );
				break;
			case 'src':
				$clean_val = trim( sanitize_text_field( $value ) );
				if ( strpos( $clean_val, 'https://' ) !== 0 ) $clean_val = 'https://' . $clean_val;
				if ( substr( $clean_val, -1) !== '/' ) $clean_val .= '/';
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
