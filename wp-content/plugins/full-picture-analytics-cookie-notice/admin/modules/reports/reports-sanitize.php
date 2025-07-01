<?php

$clean_data = array();

if ( ! empty( $input ) ) foreach( $input as $key => $value ) {

	$clean_key = sanitize_key( $key );

	if( ! empty( $value ) ) {

		switch ($clean_key) {

			case 'selected_users':
				$clean_val = array_map( 'sanitize_key', $value );
			break;

			case 'dashboards':

				if ( is_array($value) ){

					$clean_val = [];
					$i = 0;

					foreach( $value as $section ){
						
						if ( ! ( empty( $section['id'] ) || empty( $section['iframe'] ) || empty( $section['title'] ) ) ) {
							$clean_val[$i]['id'] = trim( sanitize_key( $section['id'] ) );
							$clean_val[$i]['title'] = trim( sanitize_text_field( $section['title'] ) );
							$clean_val[$i]['iframe'] = htmlentities( $section['iframe'], ENT_QUOTES );

							$clean_val[$i]['width'] = $section['width'] ? (int) $section['width'] : '1200';
							$clean_val[$i]['height'] = $section['height'] ? (int) $section['height'] : '675';

							if ( ! empty ( $section['selected_users'] ) ) {	
								$clean_val[$i]['selected_users'] = array_map( 'sanitize_key', $section['selected_users'] );
							}
						}
						$i++;
					}
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

// Make sure we are not overwriting the selected_users when a non-admin user saves settings on this page

if ( ! current_user_can('manage_options') && ! empty( $this->reports ) ) {
			
	if ( ! empty( $this->reports['selected_users'] ) ) {
		$clean_data['selected_users'] = $this->reports['selected_users'];
	}

	if ( ! empty( $this->reports['dashboards'] ) && ! empty ( $clean_data['dashboards'] ) ) {
		foreach ( $clean_data['dashboards'] as $i => $new_dash ) {
			foreach ( $this->reports['dashboards'] as $old_dash ) {
				if ( $new_dash['id'] == $old_dash['id'] ) {
					$clean_data['dashboards'][$i]['selected_users'] = $old_dash['selected_users'];
				}
			}
		}
	}
}
?>
