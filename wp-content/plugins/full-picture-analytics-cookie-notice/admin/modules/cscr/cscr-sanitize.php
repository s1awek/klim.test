<?php

$clean_data = array();

if ( ! empty( $input ) ) foreach( $input as $key => $value ) {

	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		$clean_val = [];

		switch ($clean_key) {
			default:
				if ( is_array($value) ){

					foreach( $value as $i => $section ){

						if ( empty( $section['scr'] ) || empty( $section['id'] ) ) continue;

						$clean_val[$i]['title'] = empty( $section['title'] ) ? 'Script ' . $section['id'] : trim( sanitize_text_field( $section['title'] ) );

						if ( ! empty( ( $section['name'] ) ) ) $clean_val[$i]['name'] = trim( sanitize_text_field( $section['name'] ) );
						
						if ( ! empty( $section['pp_url'] ) ) $clean_val[$i]['pp_url'] = trim( sanitize_url( $section['pp_url'] ) );

						$section['scr'] = preg_replace("/<.*?script.*?>/", '', $section['scr']);
						$section['scr'] = trim($section['scr']);
						$clean_val[$i]['scr'] = htmlentities( $section['scr'], ENT_QUOTES );
						
						if ( ! empty( $section['html'] ) ) $clean_val[$i]['html'] = htmlentities( $section['html'], ENT_QUOTES );

						$clean_val[$i]['id'] = sanitize_key( $section['id'] );
						if ( isset( $section['force_load'] ) ) $clean_val[$i]['force_load'] = '1';
						if ( isset( $section['disable'] ) ) $clean_val[$i]['disable'] = '1';

						if ( ! empty( $section['stats'] ) && $section['stats'] == '1' ) $clean_val[$i]['stats'] = '1';
						if ( ! empty( $section['pers'] ) && $section['pers'] == '1' ) $clean_val[$i]['pers'] = '1';
						if ( ! empty( $section['market'] ) && $section['market'] == '1' ) $clean_val[$i]['market'] = '1';

						if ( ! empty( $section['method'] ) && ! empty( $section['countries'] ) ) {
							$clean_val[$i]['method'] = sanitize_key( $section['method'] );
							$clean_val[$i]['countries'] = trim( sanitize_text_field( $section['countries'] ) );
						}

						if ( ! empty( $section['not_installer'] ) ) $clean_val[$i]['not_installer'] = '1';

						if ( ! empty( $section['adv_trigger'] ) && ! empty( $section['adv_trigger']['atrig_id'] ) ) {
							$clean_val[$i]['adv_trigger']['atrig_id'] = sanitize_key( $section['adv_trigger']['atrig_id'] );
							$clean_val[$i]['adv_trigger']['repeat'] = empty( $section['adv_trigger']['repeat'] ) ? 'no' :  sanitize_key( $section['adv_trigger']['repeat'] );
						};
					}
				};
			break;
		}

		// error_log('sanitized ' . $clean_key . ' value: ' . json_encode($clean_val) );

		if ( count( $clean_val ) > 0 && ! empty( $clean_key ) ) $clean_data[$clean_key] = $clean_val;
	}
}
?>
