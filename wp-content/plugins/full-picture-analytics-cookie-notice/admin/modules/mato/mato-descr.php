<?php

$ret_text = '';

switch( $section_id ){

	// INSTALLATION

	case 'fupi_mato_install':

		$mato_id = ! empty ( $this->settings ) && ! empty ( $this->settings['id'] ) ? esc_attr( $this->settings['id'] ) : '';
		$mato_url = ! empty ( $this->settings ) && ! empty ( $this->settings['url'] ) ? esc_attr( $this->settings['url'] ) : '';
		$mato_src = ! empty ( $this->settings ) && ! empty ( $this->settings['src'] ) ? esc_attr( $this->settings['src'] ) : '';
		$install_details_text = '';

		if ( ! empty( $mato_id ) && ! empty( $mato_url ) ){
			if ( ! empty( $mato_src ) ) { 
				$install_details_text = esc_html__( 'The data is sent to Matomo Cloud.', 'full-picture-analytics-cookie-notice' );
			} else {
				$install_details_text = esc_html__( 'The data is sent to your on-premise installation of Matomo. ', 'full-picture-analytics-cookie-notice' );
			}
		}

		$ret_text = '
		<div id="fupi_not_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/almost_ico.png" aria-hidden="true"> <p>' . sprintf( esc_html__( '%1$s is not installed', 'full-picture-analytics-cookie-notice' ), 'Matomo' ) . '<br><span class="fupi_small">' . esc_html__( 'To install it, please fill in the required field below', 'full-picture-analytics-cookie-notice' ) . '</span>.</p>
		</div>
		<div id="fupi_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/success_ico.png" aria-hidden="true"> <p>' . esc_html__( 'Well done! Matomo is installed', 'full-picture-analytics-cookie-notice' ) . '<br><span class="fupi_small">' . $install_details_text . '</span></p>
		</div>';

	break;

	// LOADING
	
	case 'fupi_mato_loading':
		$ret_text = '<p>' . esc_html__( 'Here you can change when and where this tool loads. This is all optional.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// DATA COLLECTION

	case 'fupi_mato_basic':
		$ret_text = '<p>' . esc_html__( 'These settings impact the amount and precision of collected data.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;
	
	// SIMPLE EVENTS
	
	case 'fupi_mato_events':
		$ret_text = '<p>' . esc_html__('Use simple events to track clicks, form submissions and others events.', 'full-picture-analytics-cookie-notice' ) . '</p>';
	break;
		
	// CUSTOM EVENTS
	
	case 'fupi_mato_atrig':
		$ret_text = '<p>' . sprintf( esc_html__( 'Here you can set up custom events. Trigger them with %1$scustom triggers%2$s' , 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-to-set-up-advanced-triggers/" target="_blank">', '</a>' ) . '</p>';
	break;
	
	// WP DATA TRACKING

	case 'fupi_mato_wpdata':
		$ret_text = '<p>' . sprintf( esc_html__( 'Event parameters are sent with all events, including the initial pageview event and custom events. Provide parameter ID in the fields below to use them. %1$sLearn how to register parameters%2$s.', 'full-picture-analytics-cookie-notice'), '<a target="_blank" href="https://wpfullpicture.com/support/documentation/how-to-set-up-custom-dimensions-in-matomo/">', '</a>' ) . '</p>';
	break;
		
	// WOOCOMMERCE

	case 'fupi_mato_ecomm':

		if ( empty( $no_woo_descr_text ) ) {

			$ret_text = '<p>' . esc_html__( 'To automatically track WooCommerce events, you need to enable e-commerce functions in Matomo.', 'full-picture-analytics-cookie-notice') . ' <button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_enable_ecomm_popup">' . esc_html__( 'Learn how to do it', 'full-picture-analytics-cookie-notice') . '</button></p>
				<p>' . esc_html__( 'After you do this, WP FP will track and send to Matomo these events:', 'full-picture-analytics-cookie-notice') . '</p>
				<ol class="fupi_checked_list">
					<li>' . esc_html__( 'purchase (as an internal and separate event)', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . sprintf( esc_html__( 'product view %1$s(only as an internal Matomo event!)%2$s', 'full-picture-analytics-cookie-notice'), '<strong class="fupi_warning_text">', '</strong>' ) . '</li>
					<li>' . esc_html__( 'add to cart (as an internal and separate event)', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'remove from cart (as an internal and separate event)', 'full-picture-analytics-cookie-notice') . '</li>
				</ol>
				<p>' . sprintf( esc_html__( '%1$sInternal events%2$s are used by Matomo to create default e-commerce reports and are sent with product information, e.g. what products were purchased or added to cart. These events cannot be used for creating goals or in custom reports.', 'full-picture-analytics-cookie-notice' ), '<strong>', '</strong>' ) . '</p>
				<p>' . sprintf( esc_html__( '%1$sSeparate events%2$s do not come with extra data but can be used as goals and in custom reports.', 'full-picture-analytics-cookie-notice' ), '<strong>', '</strong>' ) . '</p>
				<p class="fupi_woo_reqs_info"><strong>' . esc_html__( 'Attention.', 'full-picture-analytics-cookie-notice' ) . '</strong> ' . sprintf( esc_html__( 'WP Full Picture can track stores that use %1$sstandard Woocommerce hooks and HTML%2$s. If your store uses plugins that don\'t use them, tracking may not work.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/woocommerce-tracking-requirements/">', '</a>' ) . ' <a href="https://wpfullpicture.com/support/documentation/debug-mode-features/">' . esc_html__( 'Learn how to test it', 'full-picture-analytics-cookie-notice' ) . '</a>.</p>';

		} else {
			$ret_text = $no_woo_descr_text;
		};
		
	break;
};

?>
