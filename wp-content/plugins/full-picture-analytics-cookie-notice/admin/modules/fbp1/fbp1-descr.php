<?php

$ret_text = '';

switch( $section_id ){

	// INSTALLATION

	case 'fupi_fbp1_install':

		$pixel_id = ! empty ( $this->settings ) && ! empty ( $this->settings['pixel_id'] ) ? esc_attr( $this->settings['pixel_id'] ) : '';

		$ret_text = '
		<div id="fupi_not_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/almost_ico.png" aria-hidden="true"> <p>' . sprintf( esc_html__( '%1$s is not installed', 'full-picture-analytics-cookie-notice' ), 'Meta Pixel' ) . '<br><span class="fupi_small">' . sprintf( esc_html__( 'To install Meta Pixel fill in the required field below and add a domain verification meta tag in the %1$sGeneral Settings%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="' . admin_url('admin.php?page=full_picture_main') . '" target="_blank">', '</a>' ) . '</span>.</p>
		</div>
		<div id="fupi_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/success_ico.png" aria-hidden="true"> <p>' . esc_html__( 'Well done! Meta Pixel is installed', 'full-picture-analytics-cookie-notice' ) . '<br><span class="fupi_small">' . esc_html__( 'The data is sent to a dataset with an ID ', 'full-picture-analytics-cookie-notice' ) . $pixel_id . '</span>.</p>
		</div>';
	break;

	// LOADING

	case 'fupi_fbp1_loading':
		$ret_text = '<p>' . esc_html__( 'Here you can change when and where this tool loads. This is all optional.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// PRIVACY

	case 'fupi_fbp1_basic':
		$ret_text = '<p>' . esc_html__( 'These settings impact the amount and precision of collected data.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// SIMPLE EVENTS

	case 'fupi_fbp1_events':
		$ret_text = '<p>' . esc_html__('Use simple events to track clicks, form submissions and others events.', 'full-picture-analytics-cookie-notice' ) . '</p>';
	break;

	// COMPLEX EVENTS

	case 'fupi_fbp1_atrig':
		$ret_text = '<p>' . sprintf( 
			esc_html__( 'Here you can set up sending custom events of two types - %4$sstandard Meta events%2$s and %5$sfully custom events%2$s. Trigger them with %1$scustom triggers%2$s and add %3$soptional parameters%2$s.' , 'full-picture-analytics-cookie-notice' ), 
			'<a href="https://wpfullpicture.com/support/documentation/how-to-set-up-advanced-triggers/" target="_blank">', 
			'</a>',
			'<a href="https://wpfullpicture.com/support/documentation/how-to-set-up-custom-parameters/" target="_blank">',
			'<a href="https://developers.facebook.com/docs/meta-pixel/reference" target="_blank">',
			'<a href="https://www.facebook.com/business/help/964258670337005?id=1205376682832142" target="_blank">' 
		) . '</p>';
	break;

	// PARAMETERS TRACKING

	case 'fupi_fbp1_wpdata':
		$ret_text = '<p>' . esc_html__( 'These parameters will be sent with all events, including the initial pageview event and custom events.', 'full-picture-analytics-cookie-notice' ) . '</p>';
	break;

	// E-COMMERCE

	case 'fupi_fbp1_ecomm':

		if ( empty( $no_woo_descr_text ) ) {

			$ret_text = '<p>' . esc_html__( 'You are now automatically tracking WooCommerce events:', 'full-picture-analytics-cookie-notice') . '</p>
				<ol class="fupi_checked_list">
					<li>' . esc_html__( 'purchase', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'checkout', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'product view', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'add to cart', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'add to wishlist (if you set it up on the WooCommerce Tracking page)', 'full-picture-analytics-cookie-notice') . '</li>
				</ol>
				<p>' . esc_html__( 'All these events are sent with product information.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p>' . esc_html__( 'If you enable the Conversion API in the "Installation" section, then all these events will be also automatically sent via server-side tracking technology.', 'full-picture-analytics-cookie-notice') . '</p>
				<p class="fupi_woo_reqs_info"><strong>' . esc_html__( 'Attention.', 'full-picture-analytics-cookie-notice' ) . '</strong> ' . sprintf( esc_html__( 'WP Full Picture can track stores that use %1$sstandard Woocommerce hooks and HTML%2$s. If your store uses plugins that don\'t use them, tracking may not work.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/woocommerce-tracking-requirements/">', '</a>' ) . ' <a href="https://wpfullpicture.com/support/documentation/debug-mode-features/">' . esc_html__( 'Learn how to test it', 'full-picture-analytics-cookie-notice' ) . '</a>.</p>';
		} else {
			$ret_text = $no_woo_descr_text;
		}

	break;
};

?>
