<?php

$ret_text = '';

switch( $section_id ){

	// INTEGRATIONS
	// <a href="" data-srcid="9EIODjx3jy0" class="fupi_vid_thumb fupi_vid"><img src="'.FUPI_URL.'\admin\settings\img\fp-first-steps-vid_min.jpg"><span class="fupi_vid_thumb_play_btn dashicons dashicons-controls-play"></span></a>
	case 'fupi_tools_integrations':

		$ret_text = '<div class="fupi_descr_buttons_wrappers">
			<button type="button" id="fupi_toggle_filters_section" class="button-secondary">' . esc_html__('Filter by features','full-picture-analytics-cookie-notice') . '</button>
		</div>
		<div id="fupi_tools_filters">
				<p id="fupi_filters_title">' . esc_html__('Filter by features','full-picture-analytics-cookie-notice') . ':</p>
				<div id="fupi_tools_filters_wrap">

					<button class="fupi_filter_btn button fupi_tooltip" type="button" data-tag="stats">' . esc_html__('Traffic statistics','full-picture-analytics-cookie-notice') . ' <span class="fupi_tooltiptext">' . esc_html__('Info on traffic sources, visitor numbers, page popularity, etc.','full-picture-analytics-cookie-notice') . '</span></button>

					<button class="fupi_filter_btn button fupi_tooltip" type="button" data-tag="ads">' . esc_html__('Marketing','full-picture-analytics-cookie-notice') . ' <span class="fupi_tooltiptext">' . esc_html__('Conversion tracking, building visitors lists for ad campaigns','full-picture-analytics-cookie-notice') . '</span></button>

					<button class="fupi_filter_btn button fupi_tooltip" type="button" data-tag="woo">' . esc_html__('WooCommerce tracking','full-picture-analytics-cookie-notice') . ' <span class="fupi_tooltiptext">' . esc_html__('Tracking store events, like purchases, product views, etc.','full-picture-analytics-cookie-notice') . '</span></button>

					<button class="fupi_filter_btn button fupi_tooltip" type="button" data-tag="proxy">' . esc_html__('Domain proxing','full-picture-analytics-cookie-notice') . ' <span class="fupi_tooltiptext">' . esc_html__('More accurate tracking that bypasses ad blockers','full-picture-analytics-cookie-notice') . '</span></button>

					<button class="fupi_filter_btn button fupi_tooltip" type="button" data-tag="server">' . esc_html__('Server-side tracking','full-picture-analytics-cookie-notice') . ' <span class="fupi_tooltiptext">' . esc_html__('Much more accurate tracking that bypasses ad blockers (Pro only)','full-picture-analytics-cookie-notice') . '</span></button>

					<button class="fupi_filter_btn button fupi_tooltip" type="button" data-tag="nocook">' . esc_html__('Do not need a consent banner','full-picture-analytics-cookie-notice') . ' <span class="fupi_tooltiptext">' . esc_html__('In some cases extra setup may be required','full-picture-analytics-cookie-notice') . '</span></button>

					<button class="fupi_filter_btn button fupi_tooltip" type="button" data-tag="heat">' . esc_html__('Heatmaps & session recordings','full-picture-analytics-cookie-notice') . ' <span class="fupi_tooltiptext">' . esc_html__('See how your website is used and where visitors have issues','full-picture-analytics-cookie-notice') . '</span></button>

					<button class="fupi_filter_btn button fupi_tooltip" type="button" data-tag="tests">' . esc_html__('A/B testing','full-picture-analytics-cookie-notice') . ' <span class="fupi_tooltiptext">' . esc_html__('Test multiple versions of your content or design','full-picture-analytics-cookie-notice') . '</span></button>
					
					<button class="fupi_filter_btn button fupi_tooltip" type="button" data-tag="surveys">' . esc_html__('Surveys','full-picture-analytics-cookie-notice') . ' <span class="fupi_tooltiptext">' . esc_html__('Get feedback straight from your visitors','full-picture-analytics-cookie-notice') . '</span></button>
				
				</div>
			</div>';

	break;

	// TAG MANAGERS

	case 'fupi_tools_tagmanagers':
		$ret_text = '<p>' . esc_html__( 'Install tools not listed above.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// PRIVACY

	case 'fupi_tools_privacy':
		$ret_text = '<p>' . esc_html__( 'Track visitors in compliance with GDPR, PiPEDA, CCPA and other privacy laws.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// EXTENSIONS

	case 'fupi_tools_ext':
		$ret_text = '<p>' . esc_html__( 'Add extra features to other modules.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;
};

?>
