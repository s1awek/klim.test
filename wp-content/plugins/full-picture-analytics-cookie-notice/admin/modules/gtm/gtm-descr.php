<?php

$ret_text = '';

switch( $section_id ){

	// MAIN

    case 'fupi_gtm_main':

		$gtm_id = ! empty ( $this->settings ) && ! empty ( $this->settings['id'] ) ? esc_attr( $this->settings['id'] ) : '';

        $ret_text = '
		<div id="fupi_not_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/almost_ico.png" aria-hidden="true"> <p>' . sprintf( esc_html__( '%1$s is not installed', 'full-picture-analytics-cookie-notice' ), 'Google Tag Manager' ) . '<br><span class="fupi_small">' . esc_html__( 'To install it, please fill in the required field below', 'full-picture-analytics-cookie-notice' ) . '</span>.</p>
		</div>
		<div id="fupi_installed_info" class="fupi_installation_status fupi_hidden">
            <img src="' . FUPI_URL . 'admin/assets/img/success_ico.png" aria-hidden="true"> <p>' . esc_html__( 'Well done! GTM is installed.', 'full-picture-analytics-cookie-notice' ) . '<br><span class="fupi_small">' . esc_html__( 'The data is sent to the container ', 'full-picture-analytics-cookie-notice' ) . $gtm_id . '</span>.</p>
        </div>';
    break;

	case 'fupi_gtm_events':
		$ret_text = '<p>' . esc_html__( 'Push to the dataLayer information on actions that visitors\'s take on your site.', 'full-picture-analytics-cookie-notice') . ' ' . sprintf( esc_html__( 'Follow %1$sthis tutorial%2$s to learn how to turn this data into variables that you can use in GTM.', 'full-picture-analytics-cookie-notice'), '<a href="https://www.analyticsmania.com/post/pull-data-from-data-layer-google-tag-manager-tutorial/" target="_blank">', '</a>' ) . '</p>';
	break;

	case 'fupi_gtm_users':
		$ret_text = '<p>' . esc_html__( 'Push to the dataLayer information about the visitors and logged in users of your website.', 'full-picture-analytics-cookie-notice') . ' ' . sprintf( esc_html__( 'Follow %1$sthis tutorial%2$s to learn how to turn this data into variables that you can use in GTM.', 'full-picture-analytics-cookie-notice'), '<a href="https://www.analyticsmania.com/post/pull-data-from-data-layer-google-tag-manager-tutorial/" target="_blank">', '</a>' ) . '</p>';
	break;

	case 'fupi_gtm_wpdata':
		$ret_text = '<p>' . esc_html__( 'Push to the dataLayer information about the visited pages on your web site.', 'full-picture-analytics-cookie-notice') . ' ' . sprintf( esc_html__( 'Follow %1$sthis tutorial%2$s to learn how to turn this data into variables that you can use in GTM.', 'full-picture-analytics-cookie-notice'), '<a href="https://www.analyticsmania.com/post/pull-data-from-data-layer-google-tag-manager-tutorial/" target="_blank">', '</a>' ) . '</p>';
	break;

	case 'fupi_gtm_atrig':
		$ret_text = '<p>' . esc_html__( 'Push to the dataLayer information when visitors take a series of actions and/or when they behave like potential clients.', 'full-picture-analytics-cookie-notice') . ' ' . sprintf( esc_html__( 'Follow %1$sthis tutorial%2$s to learn how to turn this data into variables that you can use in GTM.', 'full-picture-analytics-cookie-notice'), '<a href="https://www.analyticsmania.com/post/pull-data-from-data-layer-google-tag-manager-tutorial/" target="_blank">', '</a>' ) . '</p>';
	break;

    // E-COMMERCE

	case 'fupi_gtm_ecomm':

		if ( empty( $no_woo_descr_text ) ) {

			$ret_text = '<p>' . esc_html__( 'WP Full Picture is now automatically pushing to the dataLayer data about these WooCommerce events:', 'full-picture-analytics-cookie-notice') . '</p>
				<ol class="fupi_checked_list">
					<li>' . esc_html__( 'purchase', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'checkout', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'product view', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'list item view', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'list item click', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'add to cart', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'remove from cart', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'add to wishlist (if you set it up on the WooCommerce Tracking page)', 'full-picture-analytics-cookie-notice') . '</li>
				</ol>
				<p class="fupi_woo_reqs_info"><strong>' . esc_html__( 'Attention.', 'full-picture-analytics-cookie-notice' ) . '</strong> ' . sprintf( esc_html__( 'WP Full Picture can track stores that use %1$sstandard Woocommerce hooks and HTML%2$s. If your store uses plugins that don\'t use them, tracking may not work.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/woocommerce-tracking-requirements/">', '</a>' ) . ' <a href="https://wpfullpicture.com/support/documentation/debug-mode-features/">' . esc_html__( 'Learn how to test it', 'full-picture-analytics-cookie-notice' ) . '</a>.</p>';
		} else {
			$ret_text = $no_woo_descr_text;
		};

	break;
};

?>
