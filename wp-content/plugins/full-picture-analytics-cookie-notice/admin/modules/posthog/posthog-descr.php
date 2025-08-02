<?php

$ret_text = '';

switch( $section_id ){

	// INSTALLATION

	case 'fupi_posthog_install':

		$posthog_api_key = ! empty ( $this->settings ) && ! empty ( $this->settings['api_key'] ) ? esc_attr( $this->settings['api_key'] ) : '';

		$ret_text = '
		<div id="fupi_not_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/almost_ico.png" aria-hidden="true"> <p>' . sprintf( esc_html__( '%1$s is not installed', 'full-picture-analytics-cookie-notice' ), 'PostHog' ) . '<br><span class="fupi_small">' . esc_html__( 'To install it, please fill in the required field below', 'full-picture-analytics-cookie-notice' ) . '</span>.</p>
		</div>
		<div id="fupi_installed_info" class="fupi_installation_status fupi_hidden">
			<img src="' . FUPI_URL . 'admin/assets/img/success_ico.png" aria-hidden="true"> <p>' . esc_html__( 'Well done! PostHog is installed', 'full-picture-analytics-cookie-notice' ) . '<br><span class="fupi_small">' . esc_html__( 'The data is sent to a project with API key ', 'full-picture-analytics-cookie-notice' ) . $posthog_api_key . '</span>.</p>
		</div>';
	break;

	// LOADING

	case 'fupi_posthog_loading':
		$ret_text = '<p>' . esc_html__( 'Here you can change when and where this tool loads. This is all optional.', 'full-picture-analytics-cookie-notice') . '</p>';
	break;
};

?>
