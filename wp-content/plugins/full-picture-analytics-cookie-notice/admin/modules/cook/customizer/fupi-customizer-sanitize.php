<?php

$setting_id = $setting->id;
$field_type = $setting->manager->get_control( $setting_id )->type;

// error_log('setting choices: '. json_encode($setting->manager->get_control( $setting->id )->choices));

// Sanitize in bulk all fields of the same type
switch ( $field_type ) {
	case 'checkbox':
		return ( $val === true ) ? true : false;
	break;
	case 'multi_checkbox' :
		$multi_values = ! is_array( $val ) ? explode( ',', $val ) : $val;
		return !empty( $multi_values ) ? array_map( 'sanitize_text_field', $multi_values ) : array();
	break;
	case 'text':
		switch ($setting_id) {
			case 'fupi_cookie_notice[btn_class]':
			case 'fupi_cookie_notice[cta_class]':

				if ( ! empty ( $val ) ) {
					$classes = explode( ' ', $val );
					$clean_classes = array_map( 'sanitize_html_class', $classes );
					$classes_string = implode( ' ', $clean_classes );

					if ( ! is_numeric ( $classes_string ) ) {
						return $classes_string;
					} else {
						return '';
					}
				} else {
					return '';
				}
			break;
		}
	break;
}

// Sanitize specific fields
switch ( $setting_id ) {

	// Media file ID for use an an icon in the consent banner toggler
	case 'fupi_custom_toggler_img':
		if ( ! is_numeric( $val ) ) return false;
		return $val; 
	break;
}
