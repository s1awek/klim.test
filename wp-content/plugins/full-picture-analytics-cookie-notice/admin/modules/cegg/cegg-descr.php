<?php

$ret_text = '';

switch( $section_id ){

	// INSTALLATION

	case 'fupi_cegg_install':

		$module_data = get_option('fupi_cegg');
		$script_src = ! empty ( $module_data ) && ! empty ( $module_data['script_src'] ) ? esc_attr( $module_data['script_src'] ) : '';

		$ret_text = '
		<div id="fupi_not_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/almost_ico.png" aria-hidden="true"> <p>' . sprintf( esc_html__( '%1$s is not installed', 'full-picture-analytics-cookie-notice' ), 'Crazy Egg' ) . '<br><span class="fupi_small">' . esc_html__( 'To install it, please fill in the required field below', 'full-picture-analytics-cookie-notice' ) . '</span>.</p>
		</div>
		<div id="fupi_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/success_ico.png" aria-hidden="true"> <p>' . esc_html__( 'Well done! Crazy Egg is installed', 'full-picture-analytics-cookie-notice' ) . '<br><span class="fupi_small">' . esc_html__( 'The data is collected by script ', 'full-picture-analytics-cookie-notice' ) . $script_src . '</span>.</p>
		</div>';
	break;

	// TAGS

	case 'fupi_cegg_loading':
		$ret_text = '<p>' . esc_html__( 'Here you can change when and where this tool loads. This is all optional.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// TAGS

	case 'fupi_cegg_tags':
		$ret_text = '<p>' . esc_html__( 'Here you can tag session recordings with user actions and extra information.', 'full-picture-analytics-cookie-notice' ) . '</p>';
	break;

	// USER IDENTIFICATION CONFIG

	case 'fupi_cegg_user':
		$ret_text = '<p>' . esc_html__( 'Identify users to associate them with session recordings and get more filtering options on Crazy Egg\'s confetti overlays.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// E-COMMERCE TRACKING

	case 'fupi_cegg_ecomm':
	
		if ( empty( $no_woo_descr_text ) ) {
			
			$ret_text = '<p>' . esc_html__( 'You are now tagging recordings with WooCommerce events:', 'full-picture-analytics-cookie-notice') . '</p>
				<ol class="fupi_checked_list">
					<li>' . esc_html__( 'purchase', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'checkout', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'product view', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'add to cart', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'remove from cart', 'full-picture-analytics-cookie-notice') . '</li>
					<li>' . esc_html__( 'add to wishlist (if you set it up on the WooCommerce Tracking page)', 'full-picture-analytics-cookie-notice') . '</li>
				</ol>
				<p>' . esc_html__( 'Tags help you quickly find the recordings you need in your Crazy Egg\'s dashboard.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p class="fupi_woo_reqs_info"><strong>' . esc_html__( 'Attention.', 'full-picture-analytics-cookie-notice' ) . '</strong> ' . sprintf( esc_html__( 'WP Full Picture can track stores that use %1$sstandard Woocommerce hooks and HTML%2$s. If your store uses plugins that don\'t use them, tracking may not work.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/woocommerce-tracking-requirements/">', '</a>' ) . ' <a href="https://wpfullpicture.com/support/documentation/debug-mode-features/">' . esc_html__( 'Learn how to test it', 'full-picture-analytics-cookie-notice' ) . '</a>.</p>';
		} else {
			$ret_text = $no_woo_descr_text;
		}

	break;
};

?>
