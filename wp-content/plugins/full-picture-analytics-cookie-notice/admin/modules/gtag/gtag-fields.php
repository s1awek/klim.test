<?php

$option_arr_id = 'fupi_gtag';

$sections = array(

	// INSTALLATION

	array(
		'section_id' => 'fupi_gtag_general',
		'section_title' => esc_html__( 'Google tag settings', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
            array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Google Tag Gateway measurement path', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'custom_gateway',
                'must_have'         => 'pro',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[custom_gateway]',
				'placeholder'		=> esc_html__( '/unique_address', 'full-picture-analytics-cookie-notice'),
				'popup2'	        => '<p>' . esc_html__( 'Google Tag Gateway masks Google\'s tracking scripts to look as if they were loaded from your own domain. As a result, you get:', 'full-picture-analytics-cookie-notice') . '</p>
                <ol>
                    <li>' . sprintf( esc_html__( '%1$sMore data:%2$s This should increase the number of tracked users by up to 5 percent, since scripts loaded this way are less likely to be blocked by ad blockers.', 'full-picture-analytics-cookie-notice'), '<strong>', '</strong>' ) . '</li>
                    <li>' . sprintf( esc_html__( '%1$sBetter ad performance:%2$s you are tracking more conversions, leading to better campaign results.', 'full-picture-analytics-cookie-notice'), '<strong>', '</strong>' ) . '</li>
                </ol>
                <p class="fupi_warning_text">' . esc_html__( 'At the moment Google Tag Gateway can only be used with websites proxied by Cloudflare.', 'full-picture-analytics-cookie-notice') . ' <a href="https://wpfullpicture.com/support/documentation/how-to-enable-google-tag-gateway/" target="_blank">' . esc_html__( 'Learn how to set it up', 'full-picture-analytics-cookie-notice') . '</a></p>',
			),
            array(
                'type'	 			=> 'toggle',
                'label' 			=> esc_html__( 'Use link decoration to improve conversion tracking', 'full-picture-analytics-cookie-notice' ),
                'field_id' 			=> 'url_passthrough',
                'option_arr_id'		=> $option_arr_id,
                'popup3'			=> '<p>'.esc_html__('This will enable Google\'s "url_passthrough" feature for link decoration. It will add a Google\'s advertising identifier to all links on your website which will improve conversion tracking.','full-picture-analytics-cookie-notice').'</p>
                <p style="color: #d50000">'.esc_html__('Attention! Using link decoration is a legal grey area and may be illegal in countries where consent before tracking is necessary (opt-in). Use at your own risk.','full-picture-analytics-cookie-notice').'</p>
                <p style="color: #d50000">'.esc_html__('Attention! Link decoration may, in very rare cases, cause problems on a website. To test it, visit your website from a Google advertisement and finish the whole conversion path.','full-picture-analytics-cookie-notice').'</p>
                <p style="color: #d50000">'.esc_html__('Attention! Link decoration will only be used in countries where consent banner lets users decline tracking.','full-picture-analytics-cookie-notice').'</p>',
            ),
		),
	),
);

?>
