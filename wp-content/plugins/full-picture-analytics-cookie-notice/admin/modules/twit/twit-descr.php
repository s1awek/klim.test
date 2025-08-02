<?php

$ret_text = '';

switch( $section_id ){

	case 'fupi_twit_install':

		$twit_id = ! empty ( $this->settings ) && ! empty ( $this->settings['id'] ) ? esc_attr( $this->settings['id'] ) : '';

		$ret_text = '
		<div id="fupi_not_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/almost_ico.png" aria-hidden="true"> <p>' . sprintf( esc_html__( '%1$s is not installed', 'full-picture-analytics-cookie-notice' ), 'X Ads pixel' ) . '<br><span class="fupi_small">' . esc_html__( 'To install it, please fill in the required field below', 'full-picture-analytics-cookie-notice' ) . '</span>.</p>
		</div>
		<div id="fupi_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/success_ico.png" aria-hidden="true"> <p>' . esc_html__( 'Well done! X Ads tracking pixel is installed', 'full-picture-analytics-cookie-notice' ) . '<br><span class="fupi_small">' . esc_html__( 'The data is tracked by a tag with an ID ', 'full-picture-analytics-cookie-notice' ) . $twit_id . '</span>.</p>
		</div>';

	break;

	// LOADING

	case 'fupi_twit_loading':
		$ret_text = '<p>' . esc_html__( 'Here you can change when and where this tool loads. This is all optional.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// DATA COLLECTION

	case 'fupi_twit_basic':
		$ret_text = '<p>' . esc_html__( 'These settings impact the amount and precision of collected data.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// EVENTS

	case 'fupi_twit_single':
		$ret_text = '<p>' . esc_html__( 'Use functions on this page to track simple events, like clicking a button or submitting a form.' , 'full-picture-analytics-cookie-notice' ) . ' <button type="button" class="fupi_open_popup fupi_faux_link" data-popup="fupi_track_actions_popup">' . esc_html__( 'How to fill in the fields below', 'full-picture-analytics-cookie-notice') .  ' </button></p>';

	break;

	// E-COMMERCE TRACKING

	case 'fupi_twit_ecomm':

		if ( empty( $no_woo_descr_text ) ) {
			
			$ret_text = '<p>' . esc_html__( 'WP Full Picture automatically tracks WooCommerce events:', 'full-picture-analytics-cookie-notice') . '</p>
				<ol class="fupi_checked_list">
					<li>' . esc_html__( 'purchase', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'checkout', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'product view', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'add to cart', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'add to wishlist (if you set it up on the WooCommerce Tracking page)', 'full-picture-analytics-cookie-notice') . '</li>
				</ol>
				<p>' . esc_html__( 'Fill in the fields below to choose which of these events will be sent to X / Twitter.', 'full-picture-analytics-cookie-notice') . '<p>
				<p><button type="button" class="button-secondary fupi_open_popup" data-popup="fupi_woo_popup">' . esc_html__( 'How to fill in the fields below', 'full-picture-analytics-cookie-notice' ) . ' <span class="fupi_open_popup_i">i</span></button></p>
				<p>' . esc_html__( 'All these events are sent with product information.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p class="fupi_woo_reqs_info"><strong>' . esc_html__( 'Attention.', 'full-picture-analytics-cookie-notice' ) . '</strong> ' . sprintf( esc_html__( 'WP Full Picture can track stores that use %1$sstandard Woocommerce hooks and HTML%2$s. If your store uses plugins that don\'t use them, tracking may not work.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/woocommerce-tracking-requirements/">', '</a>' ) . ' <a href="https://wpfullpicture.com/support/documentation/debug-mode-features/">' . esc_html__( 'Learn how to test it', 'full-picture-analytics-cookie-notice' ) . '</a>.</p>';
		} else {
			$ret_text = $no_woo_descr_text;
		}

	break;
};

?>
