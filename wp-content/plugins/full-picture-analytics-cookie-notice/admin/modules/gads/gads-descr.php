<?php

$ret_text = '';
$how_to_useit = '<p style="text-align: center;" class="fupi_warning_text"><button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_howtouseit_popup">' . esc_html__( 'How NOT to use Google Ads with WP Full Picture.', 'full-picture-analytics-cookie-notice' ) . ' <span class="fupi_open_popup_i">i</span></button></p>';

switch( $section_id ){

	// MAIN

	case 'fupi_gads_install':

		$conv_id = ! empty ( $this->settings ) && ! empty ( $this->settings['id'] ) ? esc_attr( $this->settings['id'] ) : '';

		$ret_text = '
		<div id="fupi_not_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/almost_ico.png" aria-hidden="true"> <p>' . sprintf( esc_html__( '%1$s is not installed', 'full-picture-analytics-cookie-notice' ), 'Google Ads' ) . '<br><span class="fupi_small">' . esc_html__( 'To install it, please fill in the required field below', 'full-picture-analytics-cookie-notice' ) . '</span>.</p>
		</div>

		<div id="fupi_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/success_ico.png" aria-hidden="true"> <p>' . esc_html__( 'Well done! Google Ads is installed', 'full-picture-analytics-cookie-notice' ) . '<br><span class="fupi_small">' . esc_html__( 'The data is sent to Google Tag ID', 'full-picture-analytics-cookie-notice' ) . ' ' . $conv_id . '</span>.</p>
		</div>' . $how_to_useit;
	break;

	// LOADING

	case 'fupi_gads_loading':
		$ret_text = '<p>' . esc_html__( 'Here you can change when and where this tool loads. This is all optional.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// PRIVACY

	case 'fupi_gads_basic':
		$ret_text = '<p>' . esc_html__( 'These settings impact the amount and precision of collected data.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;
		
	// SIMPLE EVENTS
	case 'fupi_gads_events':
		$ret_text = '<div>
			<p>' . esc_html__('Use functions on this page to track as conversions simple events, like clicking a button or submitting a form.', 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__('%1$sFollow this tutorial%2$s to get conversion labels for use on this page.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/support/documentation/how-to-get-google-ads-tag-id-conversion-id/">', '</a>' ) . '</p>
		</div>';
	break;

	// COMPLEX EVENTS
	
	case 'fupi_gads_atrig':
		$ret_text = '<div>
			<p>' . esc_html__( 'Use functions on this page to track complex events as conversions. Complex events can have many conditions, for example, when a visitor from France visits 5 product pages in one session. You can set these conditions in the "Advanced triggers" module.' , 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . sprintf( esc_html__('%1$sFollow this tutorial%2$s to get conversion labels for use on this page.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/support/documentation/how-to-get-google-ads-tag-id-conversion-id/">', '</a>' ) . '</p>
		</div>';
	break;

	// E-COMMERCE

	case 'fupi_gads_ecomm':

		if ( empty( $no_woo_descr_text ) ) {
			$ret_text = '<p>' . sprintf( esc_html__('Provide %1$sconversion labels%2$s in the fields below to track purchases, checkouts and additions to cart as conversions.', 'full-picture-analytics-cookie-notice' ), '<a target="_blank" href="https://wpfullpicture.com/support/documentation/how-to-get-google-ads-tag-id-conversion-id/">', '</a>' ) . '</p>
				<p>' . esc_html__( 'All these events are sent with product information.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p class="fupi_woo_reqs_info"><strong>' . esc_html__( 'Attention.', 'full-picture-analytics-cookie-notice' ) . '</strong> ' . sprintf( esc_html__( 'WP Full Picture can track stores that use %1$sstandard Woocommerce hooks and HTML%2$s. If your store uses plugins that don\'t use them, tracking may not work.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/woocommerce-tracking-requirements/">', '</a>' ) . ' <a href="https://wpfullpicture.com/support/documentation/debug-mode-features/">' . esc_html__( 'Learn how to test it', 'full-picture-analytics-cookie-notice' ) . '</a>.</p>';
		} else {
			$ret_text = $no_woo_descr_text;
		};

	break;
};

?>
