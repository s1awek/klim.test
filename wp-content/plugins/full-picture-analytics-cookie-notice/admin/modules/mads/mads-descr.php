<?php

$ret_text = '';

switch( $section_id ){

    case 'fupi_mads_install':

		$mads_id = ! empty ( $this->settings ) && ! empty ( $this->settings['id'] ) ? esc_attr( $this->settings['id'] ) : '';

        $ret_text = '
        <div id="fupi_not_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/almost_ico.png" aria-hidden="true"> <p>' . sprintf( esc_html__( '%1$s is not installed', 'full-picture-analytics-cookie-notice' ), 'Microsoft Advertising pixel (UET Tag)' ) . '<br><span class="fupi_small">' . esc_html__( 'To install it, please fill in the required field below', 'full-picture-analytics-cookie-notice' ) . '</span>.</p>
		</div>
        <div id="fupi_installed_info" class="fupi_installation_status fupi_hidden">
            <img src="' . FUPI_URL . 'admin/assets/img/success_ico.png" aria-hidden="true"> <p>' . esc_html__( 'Well done! Microsoft Ads pixel (UET tag) is installed', 'full-picture-analytics-cookie-notice' ) . '<br><span class="fupi_small">' . esc_html__( 'The data is sent to an account with UET tag ID ', 'full-picture-analytics-cookie-notice' ) . $mads_id . '</span>.</p>
        </div>';

    break;

    // LOADING

	case 'fupi_mads_loading':
		$ret_text = '<p>' . esc_html__( 'Change when this tool loads and starts tracking visitors.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

    case 'fupi_mads_events':
        $ret_text = '<p>' . sprintf( esc_html__( '%1$sRegister event actions in MS Ads%2$s and use them to track conversions and build custom audiences for remarketing purposes.', 'full-picture-analytics-cookie-notice'), '<a href="https://wpfullpicture.com/support/documentation/how-to-track-specific-user-actions-as-conversions-in-microsoft-advertising/" >','</a>') . '</p>';
    break;

	case 'fupi_mads_ecomm':

        if ( empty( $no_woo_descr_text ) ) {

            $ret_text = '<p>' . esc_html__( 'WP Full Picture automatically sends to Microsoft Advertising these WooCommerce events:', 'full-picture-analytics-cookie-notice') . '</p>
                <ol class="fupi_checked_list">
                    <li>' . esc_html__( 'purchase', 'full-picture-analytics-cookie-notice') . ' (as "woo purchase" event)</li>
                    <li>' . esc_html__( 'checkout', 'full-picture-analytics-cookie-notice') . ' (as "woo checkout" event)</li>
                    <li>' . esc_html__( 'add to cart', 'full-picture-analytics-cookie-notice') . ' (as "woo add to cart" event)</li>
                    <li>' . esc_html__( 'product view', 'full-picture-analytics-cookie-notice') . ' (as "woo product view" event)</li>
                    <li>' . esc_html__( 'list item view', 'full-picture-analytics-cookie-notice') . ' (as "woo list item view" event)</li>
                </ol>
                <p>' . esc_html__( 'All these events are sent with product information.', 'full-picture-analytics-cookie-notice' ) . '</p>
                <h3 style="color: #e47d00;">' . esc_html__( 'What you should do next', 'full-picture-analytics-cookie-notice' ) . '</h3>
                <p>' . esc_html__( 'To use the tracked data to track conversions or create custom audienes, you need to register these events in your MS Ads panel.', 'full-picture-analytics-cookie-notice' ) . '</p>
                <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 10px;">
                    <button type="button" class="button-secondary fupi_open_popup" data-popup="fupi_woo_track_conv_popup">' . esc_html__( 'How to register ecommerce events in MS Ads', 'full-picture-analytics-cookie-notice') . '</button>
                    <button type="button" class="button-secondary fupi_open_popup" data-popup="fupi_dynamic_remarket_popup">' . esc_html__( 'How to create remarketing campaigns', 'full-picture-analytics-cookie-notice') . '</button>
                </div>
                <p class="fupi_woo_reqs_info"><strong>' . esc_html__( 'Attention.', 'full-picture-analytics-cookie-notice' ) . '</strong> ' . sprintf( esc_html__( 'WP Full Picture can track stores that use %1$sstandard Woocommerce hooks and HTML%2$s. If your store uses plugins that don\'t use them, tracking may not work.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/woocommerce-tracking-requirements/">', '</a>' ) . ' <a href="https://wpfullpicture.com/support/documentation/debug-mode-features/">' . esc_html__( 'Learn how to test it', 'full-picture-analytics-cookie-notice' ) . '</a>.</p>';
        } else {
			$ret_text = $no_woo_descr_text;
		}

	break;
};

?>
