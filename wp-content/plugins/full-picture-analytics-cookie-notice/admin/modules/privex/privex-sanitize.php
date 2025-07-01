<?php

$clean_data = array();

if ( ! empty( $input ) ) foreach( $input as $key => $value ) {

	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		switch ($clean_key) {
			case 'extra_tools':

				$clean_val = [];

				if ( is_array($value) ){

					foreach( $value as $i => $section ){

						if ( empty( $section['name'] ) ) continue;

						$clean_val[$i]['name'] = trim( sanitize_text_field( $section['name'] ) );

						if ( ! empty( $section['url'] ) ) {
							$clean_val[$i]['url'] = trim( sanitize_url( $section['url'] ) );
						};
					}
				};

				break;
			default:
				//$ret_val = apply_filters('fupi_extra_ga4_sanit', $clean_key, $value );
				$clean_val = empty( $ret_val ) ? strip_tags( stripslashes( $value ) ) : $ret_val;
				break;
		}

		// error_log('sanitized ' . $clean_key . ' value: ' . json_encode($clean_val) );

		if ( ! empty( $clean_val ) && ! empty ( $clean_key ) ) $clean_data[$clean_key] = $clean_val;
	}
}
?>
