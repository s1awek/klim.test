<?php

$option_arr_id = 'fupi_iframeblock';
$priv_policy_url = get_privacy_policy_url();
$priv_policy_url_text = empty( $priv_policy_url ) ? '<p style="color: red;">' . esc_html__( 'Privacy policy page is not set! Please set it in Settings > Privacy.', 'full-picture-analytics-cookie-notice' ) . '</p>' : '';

$sections = array(

	// IFRAME BLOCKING

	array(
		'section_id' => 'fupi_iframeblock_main',
		'section_title' => esc_html__( 'Iframes manager', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
				array(
					'type'	 			=> 'multi checkbox',
					'label' 			=> esc_html__( 'Automatically manage iframes', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'auto_rules',
					'option_arr_id'		=> $option_arr_id,
					'options' 			=> array(
						'youtube'			=> esc_html__( 'YouTube', 'full-picture-analytics-cookie-notice' ),
						'vimeo'				=> esc_html__( 'Vimeo', 'full-picture-analytics-cookie-notice' ),
					),
					'popup'				=> '<p>' . esc_html__( 'This will automatically make all iframes on your website load, when visitors agree to cookies (or privacy policies of platforms that host the external content).', 'full-picture-analytics-cookie-notice' ) . '</p>
						<h3>' . esc_html__( 'Good to know', 'full-picture-analytics-cookie-notice' ) . '</h3>
						<p>' . esc_html__( 'This setting does not work on iframes loaded dynamically to the page (after the page loads).', 'full-picture-analytics-cookie-notice' ) . '</p>
						<p>' . sprintf( esc_html__( 'If you have such iframes, please use the %1$sshortcode method%2$s to manage them', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-iframes-manager-works-and-how-to-set-it-up/#how-to-manage-single-iframes-using-shortcodes">', '</a>' ) . '</p>
						<p>' . sprintf( esc_html__( 'If you do NOT want this module to manage specific iframes, simply wrap them with these HTML comments %1$s<!-- fp_no_mod_start --> your iframe <!-- fp_no_mod_end -->%2$s', 'full-picture-analytics-cookie-notice' ), '<code>', '</code>' ) . '</p>',
				),
				array(
					'type'	 			=> 'r3',
					'label' 			=> esc_html__( 'Manage other iframes', 'full-picture-analytics-cookie-notice' ),
					'field_id' 			=> 'manual_rules',
					'option_arr_id'		=> $option_arr_id,
					'is_repeater'		=> true,
					'class'				=> 'fupi_fullwidth_tr',
					'btns_class'		=> 'fupi_push_right',
					'popup'				=> '<p>' . esc_html__( 'Fill-in this form to manage iframes from other platforms then above. These settings will work on all iframes on this website, except iframes loaded dynamically.', 'full-picture-analytics-cookie-notice' ) . '</p>
						<p>' . esc_html__( 'If you have such iframes, please use the shortcode method to manage them', 'full-picture-analytics-cookie-notice' ) . '</p>
						<p>' . sprintf( esc_html__( '%1$sRead this article%2$s for more information.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-iframes-manager-works-and-how-to-set-it-up/" target="_blank">', '</a>' ) . '</p>',
					'fields'			=> array(
						array(
							'type'				=> 'text',
							'placeholder'		=> esc_html__('Domain name, e.g. youtube.com', 'full-picture-analytics-cookie-notice' ),
							'field_id'			=> 'name',
							'el_class'			=> 'fupi_internal_title',
							'class'		=> 'fupi_col_100',
							'required'			=> true,
						),
						array(
							'type'				=> 'text',
							'label'				=> esc_html__( 'Source domain URL*', 'full-picture-analytics-cookie-notice' ),
							'field_id'			=> 'iframe_url',
							'placeholder'		=> esc_html__( 'e.g. youtube.com', 'full-picture-analytics-cookie-notice' ),
							'class'		=> 'fupi_col_20',
							'required'			=> true,
						),
						array(
							'type'				=> 'url',
							'label'				=> esc_html__( 'Privacy policy URL of the iframe\'s source', 'full-picture-analytics-cookie-notice' ),
							'field_id'			=> 'privacy_url',
							'class'		=> 'fupi_col_40',
						),
						array(
							'type'				=> 'url',
							'label'				=> esc_html__( 'Placeholder image URL', 'full-picture-analytics-cookie-notice' ),
							'field_id'			=> 'image_url',
							'class'		=> 'fupi_col_40',
						),
						array(
							'type'				=> 'label',
							'label'				=> esc_html__('What is visitor\'s data used for?', 'full-picture-analytics-cookie-notice' ),
							'field_id'			=> 'types_label',
							'start_sub_section' =>  true,
							'class'		=> 'fupi_col_40',
						),
						array(
							'type'				=> 'checkbox',
							'label'             => esc_html__('Statistics', 'full-picture-analytics-cookie-notice' ),
							'field_id'			=> 'stats',
							'class'		=> 'fupi_col_20',
						),
						array(
							'type'				=> 'checkbox',
							'label'             => esc_html__('Marketing', 'full-picture-analytics-cookie-notice' ),
							'field_id'			=> 'market',
							'class'		=> 'fupi_col_20',
						),
						array(
							'type'				=> 'checkbox',
							'label'             => esc_html__('Personalisation', 'full-picture-analytics-cookie-notice' ),
							'field_id'			=> 'pers',
							'class'		=> 'fupi_col_20',
							'end_sub_section' 	=>  true,
						),
					)
				),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Default image placeholder', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'iframe_img',
				'placeholder'		=> 'https://...',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[iframe_img]',
				'popup'				=> '<p>' . esc_html__( 'This placeholder will be shown instead of the iframe if no other placeholder is available. You can enter a link to a png, jpeg or a gif file here.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Text over the placeholder', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'caption_txt',
				'default'			=> esc_html__( 'This content is hosted by [[an external source]]. By loading it, you accept its {{privacy terms}}.', 'full-picture-analytics-cookie-notice' ),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[caption_txt]',
				'under field'		=> '<p>' . esc_html__( 'The default text is "This content is hosted by [[an external source]]. By loading it, you accept its {{privacy terms}}."', 'full-picture-analytics-cookie-notice' ) . '</p>' . $priv_policy_url_text,
				'popup'				=> '<p>' . esc_html__( '[[an external source]] will be replaced by the iframe\'s domain URL', 'full-picture-analytics-cookie-notice' ) . '</p>
					<p>' . esc_html__( 'Words wrapped with double curly brackets {{ ... }} will turn into a link to the privacy policy of the iframe\'s source or, if it is hasn\'t been provided, to the privacy policy of your website.', 'full-picture-analytics-cookie-notice' ) . '</p>'  . $priv_policy_url_text,
			),
			array(
				'type'	 			=> 'text',
				'label' 			=> esc_html__( 'Text of the button which loads the iframe', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'btn_text',
				'default'			=> esc_html__( 'Load content', 'full-picture-analytics-cookie-notice' ),
				'under field'		=> esc_html__( 'The default text is "Load content".', 'full-picture-analytics-cookie-notice' ),
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[btn_text]',
			),
			array(
				'type'	 			=> 'toggle',
				'label' 			=> esc_html__( 'Lazy load all managed iframes', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'iframe_lazy',
				'option_arr_id'		=> $option_arr_id,
				'after field'		=> esc_html__( 'Recommended for improved page-load times', 'full-picture-analytics-cookie-notice' ),
			),
		)
	),
);

?>
