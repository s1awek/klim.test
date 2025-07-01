<?php

$clean_data = array();

if ( ! empty( $input ) ) foreach( $input as $key => $value ) {

	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		switch ($clean_key) {
			case 'enable_scripts_after':
			case 'mode':
			case 'optin':
			case 'optout':
			case 'inform':
				$clean_val = sanitize_key( $value );
				break;
			case 'show_to_countries':
			case 'optin_countries':
			case 'optout_countries':
			case 'inform_countries':
			case 'toggle_selector':
				$clean_val = trim( sanitize_text_field( $value ) );
				break;
			case 'priv_policy_page':
				$clean_val = (int) $value;
			break;
			case 'hide_on_pages':
				$clean_val = array_map( 'sanitize_key', $value );
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
