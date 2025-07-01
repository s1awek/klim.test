<?php

$ret_text = '';

switch( $section_id ){

	// DEFAULT

	default:
		$ret_text = '<p>' . sprintf( esc_html__('Control tracking tools installed with other plugins or added directly to HTML of your site. Controlled tools track visitors according to the settings of your Consent Banner module and %1$sgain additional benefits%2$s.','full-picture-analytics-cookie-notice'), '<button type="button" class="fupi_open_popup fupi_faux_link" data-popup="fupi_about_popup" >', '</button>' ) . '</p>
		<p style="color: red;">' . esc_html__( 'Before you start using the Tracking Tools Manager, please make sure that your caching tool / plugin / system does NOT combine or minify javascript files. If it does, then the TTM may break your website (client facing part). ', 'full-picture-analytics-cookie-notice' ) . '</p>';
	break;
};

?>
