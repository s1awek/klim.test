<?php

$ret_text = '';

switch( $section_id ){

    // MAIN

	case 'fupi_linkd_install':

		$linkd_id = ! empty ( $this->settings ) && ! empty ( $this->settings['id'] ) ? esc_attr( $this->settings['id'] ) : '';

		$ret_text = '
		<div id="fupi_not_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/almost_ico.png" aria-hidden="true"> <p>' . sprintf( esc_html__( '%1$s is not installed', 'full-picture-analytics-cookie-notice' ), 'LinkedIn Insight Tag' ) . '<br><span class="fupi_small">' . esc_html__( 'To install it, please fill in the required field below', 'full-picture-analytics-cookie-notice' ) . '</span>.</p>
		</div>
		<div id="fupi_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/success_ico.png" aria-hidden="true"> <p>' . esc_html__( 'Well done! LinkedIn Insight Tag is installed', 'full-picture-analytics-cookie-notice' ) . '<br><span class="fupi_small">' . esc_html__( 'The data is sent to an account with partner ID ', 'full-picture-analytics-cookie-notice' ) . $linkd_id . '</span>.</p>
		</div>';

	break;

	// LOADING
	
	case 'fupi_linkd_loading':
		$ret_text = '<p>' . esc_html__( 'Change when this tool loads and starts tracking visitors.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

    // EVENT CONVERSIONS

	case 'fupi_linkd_events':
		$ret_text = '<p>' . esc_html__( 'To track conversions you need to register them in LinkedIn Campaign Manager and paste their IDs in the form below.', 'full-picture-analytics-cookie-notice') . '<button type="button" class="button-secondary fupi_open_popup" data-popup="fupi_track_convert_popup">' . esc_html__('How to do it','full-picture-analytics-cookie-notice') . '</button></p>';
	break;

	case 'fupi_linkd_ecomm':

		if ( empty( $no_woo_descr_text ) ) {
			$ret_text = '<p>' . esc_html__( 'To track conversions you need to register them in LinkedIn Campaign Manager and paste their IDs in the form below.', 'full-picture-analytics-cookie-notice') . ' <button type="button" class="button-secondary fupi_open_popup" data-popup="fupi_track_convert_popup">' . esc_html__('Learn how to do it','full-picture-analytics-cookie-notice') . '</button></p>';
		} else {
			$ret_text = $no_woo_descr_text;
		};

	break;
};

?>
