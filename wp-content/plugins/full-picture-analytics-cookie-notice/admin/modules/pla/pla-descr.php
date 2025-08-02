<?php

$ret_text = '';

$links_to_popups = '<p> 
				<button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_differences_popup">' . esc_html__('Whare are the differences', 'full-picture-analytics-cookie-notice') . ' <span class="fupi_open_popup_i">i</span></button>
				<button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_setup_popup">' . esc_html__('How to register them in Plausible', 'full-picture-analytics-cookie-notice') . ' <span class="fupi_open_popup_i">i</span></button>
			</p>';

switch( $section_id ){

	// LOADING

	case 'fupi_pla_loading':
		$ret_text = '<p>' . esc_html__( 'Here you can change when and where this tool loads. This is all optional.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// SIMPLE EVENTS

	case 'fupi_pla_events_2':
		$ret_text = '<div>
			<p>' . esc_html__( 'Use functions on this page to track simple events, like clicking a button or submitting a form.' , 'full-picture-analytics-cookie-notice' ) . ' ' . esc_html__( 'There are two ways you can track them in Plausible - with or without properties.' , 'full-picture-analytics-cookie-notice' ) . '</p>' . $links_to_popups . '
		</div>';
	break;

	// COMPLEX EVENTS
	
	case 'fupi_pla_cond':
		$ret_text = '<div>
			<p>' . esc_html__( 'Use functions on this page to track complex events, with many conditions, for example, when a visitor from France visits 5 product pages in one session. You can set these conditions in the "Advanced triggers" module.' , 'full-picture-analytics-cookie-notice' ) . '</p>
			<p>' . esc_html__( 'Plausible lets you track these events in two ways - with or without properties.' , 'full-picture-analytics-cookie-notice' ) . '</p>' . $links_to_popups . '
		</div>';
	break;

	// EVENT PROPERTIES

	case 'fupi_pla_wpdata':
		$ret_text = '<div>
			<p>' . esc_html__( 'Get more information about pages that your users visit, for example, what was their language, type or author. To view properties in your reports, you need to register them in your Plausible\'s panel.' , 'full-picture-analytics-cookie-notice' ) . ' <button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_setup_popup">' . esc_html__('Learn how', 'full-picture-analytics-cookie-notice') . ' <span class="fupi_open_popup_i">i</span></button></p>
			<p class="fupi_warning_text">' . esc_html__('Only users of Plausible Business plan can view event properties in the reports.', 'full-picture-analytics-cookie-notice' ) . '</p>
		</div>';
	break;

	// E-COMMERCE TRACKING

	case 'fupi_pla_ecomm':

		if ( empty( $no_woo_descr_text ) ) {
			$ret_text = '<p>' . sprintf( esc_html__( 'To see WooCommere data in your Plausible reports you need to %1$sregister goal names and properties%2$s in your Plausible account.', 'full-picture-analytics-cookie-notice'), '<button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_setup_popup">', '</button>' ) . '</p>

				<p class="fupi_woo_reqs_info">
					<strong>' . esc_html__( 'Attention.', 'full-picture-analytics-cookie-notice' ) . '</strong> ' . sprintf( esc_html__( 'WP Full Picture can track stores that use %1$sstandard Woocommerce hooks and HTML%2$s. If your store uses plugins that don\'t use them, tracking may not work.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/woocommerce-tracking-requirements/">', '</a>' ) . ' <a href="https://wpfullpicture.com/support/documentation/debug-mode-features/">' . esc_html__( 'Learn how to test it', 'full-picture-analytics-cookie-notice' ) . '</a>.
				</p>';
		} else {
			$ret_text = $no_woo_descr_text;
		} 

	break;

	// STATS

	case 'fupi_pla_stats':
		$ret_text = '<p>' . esc_html__( 'To show Plausible statistics in your WP admin, please fill in the field below. You will see your statistics in the "Reports" menu item. If the stats don\'t show up, do NOT set a password while generating the link.' , 'full-picture-analytics-cookie-notice' ) . '</p>';
	break;
};

?>
