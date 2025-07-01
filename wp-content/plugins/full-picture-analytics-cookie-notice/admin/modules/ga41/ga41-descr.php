<?php

$ret_text = '';

switch( $section_id ){

	// INSTALLATION

	case 'fupi_ga41_install':

		$ga4_id = ! empty ( $this->settings ) && ! empty ( $this->settings['id'] ) ? esc_attr( $this->settings['id'] ) : '';

		$ret_text = '
		<div id="fupi_not_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/almost_ico.png" aria-hidden="true"> <p>' . sprintf( esc_html__( '%1$s is not installed', 'full-picture-analytics-cookie-notice' ), 'Google Analytics' ) . '<br><span class="fupi_small">' . esc_html__( 'To install it, please fill in the required field below', 'full-picture-analytics-cookie-notice' ) . '</span>.</p>
		</div>
		<div id="fupi_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/success_ico.png" aria-hidden="true"> <p>' . esc_html__( 'Well done! GA 4 is installed', 'full-picture-analytics-cookie-notice' ) . '<br><span class="fupi_small">' . esc_html__( 'The data is sent to an account with measurement ID ', 'full-picture-analytics-cookie-notice' ) . $ga4_id . '</span>.</p>
		</div>';
	break;

	// LOADING
	
	case 'fupi_ga41_loading':
		$ret_text = '<p>' . esc_html__( 'Change when this tool loads and starts tracking visitors.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// EVENTS TRACKING

	case 'fupi_ga41_events':
		$ret_text = '<p>' . esc_html__( 'Track what actions visitors take on your site. you can send them to GA as either different events with no parameters (easy) or single events with parameters (advanced). Click "i" icons next to labels below to learn more.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// CONDITIONAL TRACKING
	
	case 'fupi_ga41_atrig':
		$ret_text = '<p>' .sprintf( esc_html__( 'Track when visitors behave like potential clients. Learn %1$smore about lead scoring%2$s and %3$show to set it up%2$s', 'full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/blog/lead-scoring-in-web-analytics-what-is-it-and-how-to-use-it/">', '</a>', '<a href="https://wpfullpicture.com/support/documentation/how-to-use-lead-scoring/">' ) . '</p>';
	break;

	// WP DATA
	
	case 'fupi_ga41_wpdata':
		$ret_text = '<p>' . esc_html__( 'Event parameters give context to GA events.', 'full-picture-analytics-cookie-notice') . '</p>
		<p class="fupi_warning_text">' . sprintf( esc_html__( 'In order to see them in GA, you need to %1$sregistered them as custom dimensions%2$s.', 'full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/support/documentation/how-to-set-up-custom-definitions-in-google-analytics-4/">', '</a>' ) . '</p>';
	break;

	// E-COMMERCE

	case 'fupi_ga41_ecomm':

		if ( empty( $no_woo_descr_text ) ) {

			$ret_text = '<p>' . esc_html__( 'You are now automatically tracking WooCommerce events:', 'full-picture-analytics-cookie-notice') . '</p>
				<ol class="fupi_checked_list">
					<li>' . esc_html__( 'purchase', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'checkout', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'product view', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'product list view', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'list item click', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'add to cart', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'remove from cart', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'add to wishlist (if you set it up on the WooCommerce Tracking page)', 'full-picture-analytics-cookie-notice') . '</li>
				</ol>
				<p>' . esc_html__( 'All these events are sent with product information.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p class="fupi_woo_reqs_info"><strong>' . esc_html__( 'Attention.', 'full-picture-analytics-cookie-notice' ) . '</strong> ' . sprintf( esc_html__( 'WP Full Picture can track stores that use %1$sstandard Woocommerce hooks and HTML%2$s. If your store uses plugins that don\'t use them, tracking may not work.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/woocommerce-tracking-requirements/">', '</a>' ) . ' <a href="https://wpfullpicture.com/support/documentation/debug-mode-features/">' . esc_html__( 'Learn how to test it', 'full-picture-analytics-cookie-notice' ) . '</a>.</p>';
		} else {
			$ret_text = $no_woo_descr_text;
		}

	break;

	// DEPRECATED

	case 'fupi_ga41_deprecated':
		$ret_text = '<h3 class="fupi_title">' . esc_html__( 'These functions are scheduled for removal in future updates', 'full-picture-analytics-cookie-notice' ) . '</h3>';
	break;
};

?>
