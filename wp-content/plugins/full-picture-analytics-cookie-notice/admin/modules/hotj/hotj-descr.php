<?php

$ret_text = '';

switch( $section_id ){

	// INSTALLATION

	case 'fupi_hotj_install':

		$hotj_id = ! empty ( $this->settings ) && ! empty ( $this->settings['id'] ) ? esc_attr( $this->settings['id'] ) : '';

		$ret_text = '
		<div id="fupi_not_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/almost_ico.png" aria-hidden="true"> <p>' . sprintf( esc_html__( '%1$s is not installed', 'full-picture-analytics-cookie-notice' ), 'Hotjar' ) . '<br><span class="fupi_small">' . esc_html__( 'To install it, please fill in the required field below', 'full-picture-analytics-cookie-notice' ) . '</span>.</p>
		</div>
		<div id="fupi_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/success_ico.png" aria-hidden="true"> <p>' . esc_html__( 'Well done! Hotjar is installed', 'full-picture-analytics-cookie-notice' ) . '<br><span class="fupi_small">' . esc_html__( 'The data is sent to an account with site ID ', 'full-picture-analytics-cookie-notice' ) . $hotj_id . '</span>.</p>
		</div>';
	break;

	// LOADING
	
	case 'fupi_hotj_loading':
		$ret_text = '<p>' . esc_html__( 'Here you can change when and where this tool loads. This is all optional.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// DATA COLLECTION

	case 'fupi_hotj_basic':
		$ret_text = '<p>' . esc_html__( 'These settings impact the amount and precision of collected data.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// TAGS

	case 'fupi_hotj_tags':
		$ret_text = '<div>
			<p>' . esc_html__( 'Here you can tag session recordings with user actions and extra information.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p class="fupi_warning_text">' . sprintf( esc_html__( '%1$sRead this%2$s before you start.', 'full-picture-analytics-cookie-notice'), '<button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_warning_popup">', ' <span class="fupi_open_popup_i">i</span></button>' ) . '</p>
		</div>';
	break;

	// E-COMMERCE

	case 'fupi_hotj_ecomm':
		
		if ( empty( $no_woo_descr_text ) ) {
			$ret_text = '<p class="fupi_warning_text">' . sprintf( esc_html__( '%1$sRead this%2$s before you start.', 'full-picture-analytics-cookie-notice'), '<button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_warning_popup">', ' <span class="fupi_open_popup_i">i</span></button>' ) . '</p>';
		} else {
			$ret_text = $no_woo_descr_text;
		}

	break;
};

?>
