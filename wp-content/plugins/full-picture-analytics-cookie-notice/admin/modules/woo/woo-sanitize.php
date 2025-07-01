<?php

$clean_data = array();

if ( ! empty( $input ) ) foreach( $input as $key => $value ) {

	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		switch ($clean_key) {
			
			case 'wishlist_btn_sel':
			case 'teaser_wrapper_sel':
				$clean_val = trim( sanitize_text_field( $value ) );
				break;

			case 'brand_tax':
			case 'variable_tracking_method':
				$clean_val = sanitize_key( $value );
				break;

			case 'server_track_on_statuses':
			case 'server_cancel_on_statuses';
				$clean_val = array_map( 'sanitize_key', $value );
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
