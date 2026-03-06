<?php

$ret_text = '';
$how_to_useit = '<p style="text-align: center;" class="fupi_warning_text"><button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_howtouseit_popup">' . esc_html__( 'How NOT to use GTM with WP Full Picture.', 'full-picture-analytics-cookie-notice' ) . ' <span class="fupi_open_popup_i">i</span></button></p>';

switch( $section_id ){

    case 'fupi_gtm_main':

		$gtm_id = ! empty ( $this->settings ) && ! empty ( $this->settings['id'] ) ? esc_attr( $this->settings['id'] ) : '';

        $ret_text = '
		<div id="fupi_not_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/almost_ico.png" aria-hidden="true"> <p>' . sprintf( esc_html__( '%1$s is not installed', 'full-picture-analytics-cookie-notice' ), 'Google Tag Manager' ) . '<br><span class="fupi_small">' . esc_html__( 'To install it, please fill in the required field below', 'full-picture-analytics-cookie-notice' ) . '</span>.</p>
		</div>
		<div id="fupi_installed_info" class="fupi_installation_status fupi_hidden">
            <img src="' . FUPI_URL . 'admin/assets/img/success_ico.png" aria-hidden="true"> <p>' . esc_html__( 'Well done! GTM is installed.', 'full-picture-analytics-cookie-notice' ) . '<br><span class="fupi_small">' . esc_html__( 'The data is sent to the container ', 'full-picture-analytics-cookie-notice' ) . $gtm_id . '</span>.</p>
        </div>' . $how_to_useit;
    break;

	case 'fupi_gtm_loading':

		$ret_text = '<p class="fupi_warning_text">' . esc_html__('Tracking tools installed with GTM, track all users - even those who did not agree to tracking or are excluded from tracking (in the General Settings page).', 'full-picture-analytics-cookie-notice' ) . '</p>
		<p class="fupi_warning_text">' . esc_html__('To comply with GDPR (and be able to decline tracking yourself), please, enable the Consent Management module and set every tag in GTM to require relevant consents.', 'full-picture-analytics-cookie-notice' ) . '</p>
		<ol>
			<li>' . esc_html__( 'Open the tag configuration.', 'full-picture-analytics-cookie-notice') . ',</li>
			<li>' . esc_html__( 'Click on "Advanced settings" and then "Consent settings"', 'full-picture-analytics-cookie-notice') . ',</li>
			<li>' . esc_html__( 'Check the box "Require additional consents for tag to fire"', 'full-picture-analytics-cookie-notice') . ',</li>
			<li>' . esc_html__( 'Choose relevant consents', 'full-picture-analytics-cookie-notice') . ',</li>
			<li>' . esc_html__( 'And save the changes', 'full-picture-analytics-cookie-notice') . '.</li>
		</ol>';
	break;

	case 'fupi_gtm_events':
		$ret_text = '<p>' . esc_html__('Use functions on this page to push to the dataLayer information about simple events, like clicking a button or submitting a form.', 'full-picture-analytics-cookie-notice' ) . '</p>';
	break;

	case 'fupi_gtm_atrig':
		$ret_text = '<p>' . sprintf( esc_html__( 'Here you can set up pushing to the dataLayer data of custom events. They can be triggered by %1$scustom triggers%2$s and include %3$soptional parameters%2$s.' , 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-to-set-up-advanced-triggers/" target="_blank">', '</a>', '<a href="https://wpfullpicture.com/support/documentation/how-to-set-up-custom-parameters/" target="_blank">' ) . '</p>';
	break;

	case 'fupi_gtm_users':
		$ret_text = '<p>' . esc_html__( 'Push to the dataLayer information about the visitors and logged in users of your website.', 'full-picture-analytics-cookie-notice') . ' ' . sprintf( esc_html__( 'Follow %1$sthis tutorial%2$s to learn how to turn this data into variables that you can use in GTM.', 'full-picture-analytics-cookie-notice'), '<a href="https://www.analyticsmania.com/post/pull-data-from-data-layer-google-tag-manager-tutorial/" target="_blank">', '</a>' ) . '</p>';
	break;

	case 'fupi_gtm_wpdata':
		$ret_text = '<p>' . esc_html__( 'Push to the dataLayer information about the visited pages on your web site.', 'full-picture-analytics-cookie-notice') . ' ' . sprintf( esc_html__( 'Follow %1$sthis tutorial%2$s to learn how to turn this data into variables that you can use in GTM.', 'full-picture-analytics-cookie-notice'), '<a href="https://www.analyticsmania.com/post/pull-data-from-data-layer-google-tag-manager-tutorial/" target="_blank">', '</a>' ) . '</p>';
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
