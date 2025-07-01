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
		$ret_text = '<p>' . esc_html__( 'Change when this tool loads and starts tracking visitors.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// TAGS

	case 'fupi_hotj_tags':
		$ret_text = '<p>' . esc_html__( 'Tracked data is used to tag recordings, to let you quickly find the ones that show what you need.', 'full-picture-analytics-cookie-notice') . '</p>
			<p style="color: #e47d00; font-weight: bold;">' . sprintf( esc_html__( 'Attention! %1$sRead this%2$s before you start tracking events.', 'full-picture-analytics-cookie-notice'), '<button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_warning_popup">', '</button>' ) . '</p>';
	break;

	// E-COMMERCE

	case 'fupi_hotj_ecomm':
		
		if ( empty( $no_woo_descr_text ) ) {
			$ret_text = '<p style="color: #e47d00; font-weight: bold;">' . sprintf( esc_html__( 'Attention! %1$sRead this%2$s before you start tracking events.', 'full-picture-analytics-cookie-notice'), '<button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_warning_popup">', '</button>' ) . '</p>';
		} else {
			$ret_text = $no_woo_descr_text;
		}

	break;
};

?>
